<?php
if (!defined('ABSPATH')) {
    exit;
}

class AMS_Membership_ID {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('woocommerce_order_status_completed', array($this, 'generate_membership_id'), 10, 1);
    }
    
    public static function generate_id() {
        $prefix = get_option('ams_membership_prefix', 'PAU');
        $padding = intval(get_option('ams_membership_padding', 5));
        $last_id = intval(get_option('ams_last_id', 100));
        
        $new_id = $last_id + 1;
        $formatted = $prefix . str_pad($new_id, $padding, '0', STR_PAD_LEFT);
        
        update_option('ams_last_id', $new_id);
        
        return $formatted;
    }
    
    public static function get_user_membership_id($user_id) {
        return get_user_meta($user_id, '_membership_id', true);
    }
    
    public static function set_user_membership_id($user_id, $membership_id) {
        return update_user_meta($user_id, '_membership_id', sanitize_text_field($membership_id));
    }
    
    public function generate_membership_id($order_id) {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_status() !== 'completed') {
            return;
        }
        
        $user_id = $order->get_user_id();
        if (!$user_id) {
            return;
        }
        
        $membership_products = get_option('ams_membership_products', array());
        if (empty($membership_products)) {
            return;
        }
        
        $has_membership_product = false;
        foreach ($order->get_items() as $item) {
            if (in_array($item->get_product_id(), (array)$membership_products)) {
                $has_membership_product = true;
                break;
            }
        }
        
        if (!$has_membership_product) {
            return;
        }
        
        $existing_id = self::get_user_membership_id($user_id);
        if (!empty($existing_id)) {
            return;
        }
        
        $membership_id = self::generate_id();
        self::set_user_membership_id($user_id, $membership_id);
        
        $note = trim($order->get_customer_note() . "\nMembership ID: $membership_id");
        $order->set_customer_note($note);
        $order->add_order_note(sprintf(__('Membership ID generated: %s', 'ams'), $membership_id));
        $order->save();
        
        if (get_option('ams_send_email', 1)) {
            $this->send_membership_email($user_id, $membership_id);
        }
        
        do_action('ams_membership_id_generated', $user_id, $membership_id, $order_id);
    }
    
    private function send_membership_email($user_id, $membership_id) {
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return;
        }
        
        $subject = get_option('ams_email_subject', 'Membership ID Anda');
        $message = get_option('ams_email_message', 'Hi {user_name},\n\nMembership ID anda: {membership_id}\n\nAnda boleh log masuk di {account_url} untuk melihat maklumat keahlian anda.\n\nTerima kasih.');
        
        $placeholders = array(
            '{user_name}' => $user->display_name,
            '{membership_id}' => $membership_id,
            '{account_url}' => wc_get_page_permalink('myaccount'),
            '{site_name}' => get_bloginfo('name')
        );
        
        $subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject);
        $message = str_replace(array_keys($placeholders), array_values($placeholders), $message);
        $message = wpautop($message);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($user->user_email, $subject, $message, $headers);
    }
    
    public static function verify_membership($membership_id) {
        global $wpdb;
        
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_membership_id' AND meta_value = %s LIMIT 1",
            $membership_id
        ));
        
        if (!$user_id) {
            return false;
        }
        
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return false;
        }
        
        $fields = AMS_Fields::get_fields(true);
        $user_data = array(
            'membership_id' => $membership_id,
            'user_id' => $user_id,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'registered' => $user->user_registered,
            'fields' => array()
        );
        
        foreach ($fields as $field) {
            $value = get_user_meta($user_id, $field['id'], true);
            if (!empty($value)) {
                $user_data['fields'][$field['id']] = array(
                    'label' => $field['label'],
                    'value' => $value
                );
            }
        }
        
        $user_data = apply_filters('ams_verify_membership_data', $user_data, $user_id);
        
        return $user_data;
    }
}
