<?php
if (!defined('ABSPATH')) {
    exit;
}

class AMS_Integrations {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init_integrations'));
    }
    
    public function init_integrations() {
        // WooCommerce Memberships integration
        if (function_exists('wc_memberships')) {
            add_action('wc_memberships_user_membership_saved', array($this, 'sync_membership_data'), 10, 2);
            add_filter('ams_verify_membership_data', array($this, 'add_wc_membership_data'), 10, 2);
        }
        
        // WooCommerce Subscriptions integration
        if (class_exists('WC_Subscriptions')) {
            add_action('woocommerce_subscription_status_updated', array($this, 'handle_subscription_status'), 10, 3);
            add_filter('ams_verify_membership_data', array($this, 'add_subscription_data'), 10, 2);
        }
    }
    
    public function sync_membership_data($membership_plan, $args) {
        if (empty($args['user_id'])) {
            return;
        }
        
        $user_id = $args['user_id'];
        $membership_id = AMS_Membership_ID::get_user_membership_id($user_id);
        
        if (empty($membership_id)) {
            return;
        }
        
        $user_membership = wc_memberships_get_user_membership($user_id, $membership_plan);
        
        if ($user_membership) {
            update_post_meta($user_membership->get_id(), '_ams_membership_id', $membership_id);
        }
    }
    
    public function add_wc_membership_data($data, $user_id) {
        if (!function_exists('wc_memberships_get_user_memberships')) {
            return $data;
        }
        
        $memberships = wc_memberships_get_user_memberships($user_id);
        
        if (empty($memberships)) {
            return $data;
        }
        
        $data['wc_memberships'] = array();
        
        foreach ($memberships as $membership) {
            $plan = $membership->get_plan();
            $membership_data = array(
                'plan_name' => $plan->get_name(),
                'status' => $membership->get_status(),
                'status_label' => wc_memberships_get_user_membership_status_name($membership->get_status()),
                'start_date' => $membership->get_start_date('Y-m-d H:i:s'),
                'end_date' => $membership->get_end_date('Y-m-d H:i:s'),
            );
            
            $data['wc_memberships'][] = $membership_data;
        }
        
        return $data;
    }
    
    public function handle_subscription_status($subscription, $new_status, $old_status) {
        $user_id = $subscription->get_user_id();
        
        if (!$user_id) {
            return;
        }
        
        do_action('ams_subscription_status_changed', $user_id, $subscription, $new_status, $old_status);
    }
    
    public function add_subscription_data($data, $user_id) {
        if (!class_exists('WC_Subscriptions')) {
            return $data;
        }
        
        $subscriptions = wcs_get_users_subscriptions($user_id);
        
        if (empty($subscriptions)) {
            return $data;
        }
        
        $data['subscriptions'] = array();
        
        foreach ($subscriptions as $subscription) {
            $subscription_data = array(
                'id' => $subscription->get_id(),
                'status' => $subscription->get_status(),
                'status_label' => wcs_get_subscription_status_name($subscription->get_status()),
                'start_date' => $subscription->get_date('start'),
                'next_payment' => $subscription->get_date('next_payment'),
                'end_date' => $subscription->get_date('end'),
                'total' => $subscription->get_total(),
            );
            
            $items = array();
            foreach ($subscription->get_items() as $item) {
                $items[] = $item->get_name();
            }
            $subscription_data['items'] = $items;
            
            $data['subscriptions'][] = $subscription_data;
        }
        
        return $data;
    }
    
    public static function get_user_active_memberships($user_id) {
        if (!function_exists('wc_memberships_get_user_active_memberships')) {
            return array();
        }
        
        return wc_memberships_get_user_active_memberships($user_id);
    }
    
    public static function get_user_subscriptions($user_id) {
        if (!function_exists('wcs_get_users_subscriptions')) {
            return array();
        }
        
        return wcs_get_users_subscriptions($user_id);
    }
    
    public static function has_active_membership($user_id, $plan_id = null) {
        if (!function_exists('wc_memberships_is_user_active_member')) {
            return false;
        }
        
        if ($plan_id) {
            return wc_memberships_is_user_active_member($user_id, $plan_id);
        }
        
        $memberships = self::get_user_active_memberships($user_id);
        return !empty($memberships);
    }
}
