<?php
if (!defined('ABSPATH')) {
    exit;
}

class AMS_My_Account {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        if (get_option('ams_show_in_myaccount', 1)) {
            add_filter('woocommerce_account_menu_items', array($this, 'add_menu_item'));
            add_action('init', array($this, 'add_endpoint'));
            add_action('woocommerce_account_' . $this->get_endpoint_slug() . '_endpoint', array($this, 'endpoint_content'));
        }
    }
    
    private function get_endpoint_slug() {
        return get_option('ams_myaccount_tab_slug', 'maklumat-akademik');
    }
    
    private function get_endpoint_title() {
        return get_option('ams_myaccount_tab_title', 'Maklumat Akademik');
    }
    
    public function add_menu_item($items) {
        $slug = $this->get_endpoint_slug();
        $title = $this->get_endpoint_title();
        
        $items = array_slice($items, 0, 1, true) +
            array($slug => $title) +
            array_slice($items, 1, null, true);
        
        return $items;
    }
    
    public function add_endpoint() {
        add_rewrite_endpoint($this->get_endpoint_slug(), EP_PAGES);
    }
    
    public function endpoint_content() {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ams_form_submit'])) {
            $this->save_user_data($user_id);
        }
        
        $this->render_form($user_id);
    }
    
    private function save_user_data($user_id) {
        if (!wp_verify_nonce($_POST['ams_nonce'], 'ams_save_user_data')) {
            wc_add_notice(__('Security check failed.', 'ams'), 'error');
            return;
        }
        
        $fields = AMS_Fields::get_fields(true);
        
        foreach ($fields as $field) {
            if (isset($_POST[$field['id']])) {
                $value = sanitize_text_field($_POST[$field['id']]);
                update_user_meta($user_id, $field['id'], $value);
            }
        }
        
        wc_add_notice(__('Maklumat berjaya disimpan.', 'ams'), 'success');
        
        do_action('ams_user_data_saved', $user_id);
    }
    
    private function render_form($user_id) {
        $fields_by_group = AMS_Fields::get_fields_by_group(true);
        $membership_id = AMS_Membership_ID::get_user_membership_id($user_id);
        
        ?>
        <div class="ams-my-account-form">
            <?php if ($membership_id) : ?>
                <div class="ams-membership-id-display">
                    <h3><?php _e('Membership ID', 'ams'); ?></h3>
                    <p class="membership-id-value"><strong><?php echo esc_html($membership_id); ?></strong></p>
                </div>
            <?php endif; ?>
            
            <form method="post" class="woocommerce-EditAccountForm edit-account">
                <?php wp_nonce_field('ams_save_user_data', 'ams_nonce'); ?>
                
                <?php foreach ($fields_by_group as $group_name => $fields) : ?>
                    <div class="ams-field-group">
                        <?php if ($group_name !== 'Other') : ?>
                            <h3><?php echo esc_html($group_name); ?></h3>
                        <?php endif; ?>
                        
                        <?php foreach ($fields as $field) : ?>
                            <?php
                            $value = get_user_meta($user_id, $field['id'], true);
                            AMS_Fields::render_field($field, $value, 'myaccount');
                            ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                
                <p>
                    <button type="submit" class="woocommerce-Button button" name="ams_form_submit" value="1">
                        <?php _e('Simpan Perubahan', 'ams'); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }
}
