<?php
if (!defined('ABSPATH')) {
    exit;
}

class AMS_Shortcodes {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_shortcode('membership_id', array($this, 'membership_id_shortcode'));
        add_shortcode('membership_count', array($this, 'membership_count_shortcode'));
        add_shortcode('membership_info', array($this, 'membership_info_shortcode'));
        add_shortcode('membership_field', array($this, 'membership_field_shortcode'));
        add_shortcode('membership_status', array($this, 'membership_status_shortcode'));
    }
    
    public function membership_id_shortcode($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'default' => __('Belum didaftarkan', 'ams')
        ), $atts);
        
        $user_id = intval($atts['user_id']);
        
        if (!$user_id) {
            return __('Sila log masuk', 'ams');
        }
        
        $membership_id = AMS_Membership_ID::get_user_membership_id($user_id);
        
        return $membership_id ? esc_html($membership_id) : esc_html($atts['default']);
    }
    
    public function membership_count_shortcode($atts) {
        $atts = shortcode_atts(array(
            'offset' => 100
        ), $atts);
        
        $last_id = intval(get_option('ams_last_id', 100));
        $offset = intval($atts['offset']);
        
        return max(0, $last_id - $offset);
    }
    
    public function membership_info_shortcode($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'fields' => '',
            'show_id' => 'yes',
            'show_name' => 'yes',
            'show_email' => 'no',
            'template' => 'list'
        ), $atts);
        
        $user_id = intval($atts['user_id']);
        
        if (!$user_id) {
            return '<p>' . __('Sila log masuk untuk melihat maklumat keahlian.', 'ams') . '</p>';
        }
        
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return '';
        }
        
        $membership_id = AMS_Membership_ID::get_user_membership_id($user_id);
        
        ob_start();
        
        if ($atts['template'] === 'table') {
            echo '<table class="ams-membership-info-table">';
            
            if ($atts['show_id'] === 'yes' && $membership_id) {
                echo '<tr><th>' . __('Membership ID', 'ams') . '</th><td>' . esc_html($membership_id) . '</td></tr>';
            }
            
            if ($atts['show_name'] === 'yes') {
                echo '<tr><th>' . __('Name', 'ams') . '</th><td>' . esc_html($user->display_name) . '</td></tr>';
            }
            
            if ($atts['show_email'] === 'yes') {
                echo '<tr><th>' . __('Email', 'ams') . '</th><td>' . esc_html($user->user_email) . '</td></tr>';
            }
            
            if (!empty($atts['fields'])) {
                $field_ids = array_map('trim', explode(',', $atts['fields']));
                foreach ($field_ids as $field_id) {
                    $value = get_user_meta($user_id, $field_id, true);
                    if (!empty($value)) {
                        $label = AMS_Fields::get_field_label($field_id);
                        echo '<tr><th>' . esc_html($label) . '</th><td>' . esc_html($value) . '</td></tr>';
                    }
                }
            }
            
            echo '</table>';
        } else {
            echo '<div class="ams-membership-info-list">';
            
            if ($atts['show_id'] === 'yes' && $membership_id) {
                echo '<p><strong>' . __('Membership ID:', 'ams') . '</strong> ' . esc_html($membership_id) . '</p>';
            }
            
            if ($atts['show_name'] === 'yes') {
                echo '<p><strong>' . __('Name:', 'ams') . '</strong> ' . esc_html($user->display_name) . '</p>';
            }
            
            if ($atts['show_email'] === 'yes') {
                echo '<p><strong>' . __('Email:', 'ams') . '</strong> ' . esc_html($user->user_email) . '</p>';
            }
            
            if (!empty($atts['fields'])) {
                $field_ids = array_map('trim', explode(',', $atts['fields']));
                foreach ($field_ids as $field_id) {
                    $value = get_user_meta($user_id, $field_id, true);
                    if (!empty($value)) {
                        $label = AMS_Fields::get_field_label($field_id);
                        echo '<p><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</p>';
                    }
                }
            }
            
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    public function membership_field_shortcode($atts) {
        $atts = shortcode_atts(array(
            'field' => '',
            'user_id' => get_current_user_id(),
            'default' => ''
        ), $atts);
        
        if (empty($atts['field'])) {
            return '';
        }
        
        $user_id = intval($atts['user_id']);
        if (!$user_id) {
            return esc_html($atts['default']);
        }
        
        $value = get_user_meta($user_id, $atts['field'], true);
        
        return $value ? esc_html($value) : esc_html($atts['default']);
    }
    
    public function membership_status_shortcode($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'show_expiry' => 'yes'
        ), $atts);
        
        $user_id = intval($atts['user_id']);
        
        if (!$user_id) {
            return '<p>' . __('Sila log masuk.', 'ams') . '</p>';
        }
        
        if (!function_exists('wc_memberships_get_user_memberships')) {
            return '<p>' . __('WooCommerce Memberships plugin not active.', 'ams') . '</p>';
        }
        
        $memberships = wc_memberships_get_user_memberships($user_id);
        
        if (empty($memberships)) {
            return '<p>' . __('No active memberships.', 'ams') . '</p>';
        }
        
        ob_start();
        echo '<div class="ams-membership-status">';
        
        foreach ($memberships as $membership) {
            $plan = $membership->get_plan();
            $status = $membership->get_status();
            $status_label = wc_memberships_get_user_membership_status_name($status);
            
            echo '<div class="membership-item status-' . esc_attr($status) . '">';
            echo '<h4>' . esc_html($plan->get_name()) . '</h4>';
            echo '<p><strong>' . __('Status:', 'ams') . '</strong> ' . esc_html($status_label) . '</p>';
            
            if ($atts['show_expiry'] === 'yes' && $membership->get_end_date()) {
                echo '<p><strong>' . __('Expires:', 'ams') . '</strong> ' . 
                     date_i18n(get_option('date_format'), strtotime($membership->get_end_date())) . '</p>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
        
        return ob_get_clean();
    }
}
