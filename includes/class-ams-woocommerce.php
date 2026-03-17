<?php
if (!defined('ABSPATH')) {
    exit;
}

class AMS_WooCommerce {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        if (get_option('ams_show_in_checkout', 1)) {
            add_action('woocommerce_after_checkout_billing_form', array($this, 'add_checkout_fields'));
            add_action('woocommerce_checkout_process', array($this, 'validate_checkout_fields'));
            add_action('woocommerce_checkout_update_order_meta', array($this, 'save_checkout_fields'));
        }
        
        add_action('woocommerce_order_status_completed', array($this, 'sync_to_user_meta'), 20, 1);
        
        if (get_option('ams_auto_complete', 0)) {
            add_action('woocommerce_order_status_processing', array($this, 'auto_complete_order'));
            add_action('woocommerce_order_status_on-hold', array($this, 'auto_complete_order'));
        }
        
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_order_meta'));
    }
    
    public function add_checkout_fields($checkout) {
        $fields_by_group = AMS_Fields::get_fields_by_group(true);
        
        if (empty($fields_by_group)) {
            return;
        }
        
        echo '<div class="ams-checkout-fields">';
        echo '<h3>' . __('Maklumat Tambahan', 'ams') . '</h3>';
        
        foreach ($fields_by_group as $group_name => $fields) {
            echo '<div class="ams-field-group">';
            if ($group_name !== 'Other') {
                echo '<h4>' . esc_html($group_name) . '</h4>';
            }
            
            foreach ($fields as $field) {
                $value = $checkout->get_value($field['id']);
                AMS_Fields::render_field($field, $value, 'checkout');
            }
            
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    public function validate_checkout_fields() {
        $fields = AMS_Fields::get_fields(true);
        
        foreach ($fields as $field) {
            if (!empty($field['required']) && empty($_POST[$field['id']])) {
                wc_add_notice(sprintf(__('%s is a required field.', 'ams'), $field['label']), 'error');
            }
        }
    }
    
    public function save_checkout_fields($order_id) {
        $fields = AMS_Fields::get_fields(true);
        
        foreach ($fields as $field) {
            if (isset($_POST[$field['id']])) {
                $value = sanitize_text_field($_POST[$field['id']]);
                update_post_meta($order_id, $field['id'], $value);
            }
        }
    }
    
    public function sync_to_user_meta($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $user_id = $order->get_user_id();
        if (!$user_id) {
            return;
        }
        
        $fields = AMS_Fields::get_field_ids(true);
        
        foreach ($fields as $field_id) {
            $value = $order->get_meta($field_id, true);
            if (!empty($value)) {
                update_user_meta($user_id, $field_id, sanitize_text_field($value));
            }
        }
        
        do_action('ams_synced_to_user_meta', $user_id, $order_id);
    }
    
    public function auto_complete_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $membership_products = get_option('ams_membership_products', array());
        if (empty($membership_products)) {
            return;
        }
        
        foreach ($order->get_items() as $item) {
            if (in_array($item->get_product_id(), (array)$membership_products)) {
                $order->update_status('completed');
                $order->add_order_note(__('Order auto-completed by Advanced Membership System', 'ams'));
                break;
            }
        }
    }
    
    public function display_order_meta($order) {
        $fields = AMS_Fields::get_fields(true);
        
        if (empty($fields)) {
            return;
        }
        
        echo '<div class="ams-order-meta">';
        echo '<h3>' . __('Maklumat Keahlian', 'ams') . '</h3>';
        
        $has_data = false;
        foreach ($fields as $field) {
            $value = $order->get_meta($field['id'], true);
            if (!empty($value)) {
                $has_data = true;
                echo '<p><strong>' . esc_html($field['label']) . ':</strong> ' . esc_html($value) . '</p>';
            }
        }
        
        if (!$has_data) {
            echo '<p><em>' . __('No membership data available', 'ams') . '</em></p>';
        }
        
        echo '</div>';
    }
}
