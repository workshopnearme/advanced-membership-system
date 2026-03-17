<?php
if (!defined('ABSPATH')) {
    exit;
}

class AMS_Settings {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function add_menu() {
        add_menu_page(
            __('Membership System', 'ams'),
            __('Membership', 'ams'),
            'manage_options',
            'ams-settings',
            array($this, 'settings_page'),
            'dashicons-id-alt',
            56
        );
        
        add_submenu_page(
            'ams-settings',
            __('Settings', 'ams'),
            __('Settings', 'ams'),
            'manage_options',
            'ams-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'ams-settings',
            __('Custom Fields', 'ams'),
            __('Custom Fields', 'ams'),
            'manage_options',
            'ams-fields',
            array($this, 'fields_page')
        );
        
        add_submenu_page(
            'ams-settings',
            __('Membership Products', 'ams'),
            __('Products', 'ams'),
            'manage_options',
            'ams-products',
            array($this, 'products_page')
        );
    }
    
    public function register_settings() {
        register_setting('ams_settings', 'ams_membership_prefix');
        register_setting('ams_settings', 'ams_membership_start_number');
        register_setting('ams_settings', 'ams_membership_padding');
        register_setting('ams_settings', 'ams_last_id');
        register_setting('ams_settings', 'ams_membership_products');
        register_setting('ams_settings', 'ams_auto_complete');
        register_setting('ams_settings', 'ams_send_email');
        register_setting('ams_settings', 'ams_email_subject');
        register_setting('ams_settings', 'ams_email_message');
        register_setting('ams_settings', 'ams_show_in_checkout');
        register_setting('ams_settings', 'ams_show_in_myaccount');
        register_setting('ams_settings', 'ams_myaccount_tab_title');
        register_setting('ams_settings', 'ams_myaccount_tab_slug');
        register_setting('ams_settings', 'ams_custom_fields');
    }
    
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'ams-') === false) {
            return;
        }
        
        wp_enqueue_style('ams-admin', AMS_PLUGIN_URL . 'assets/css/admin.css', array(), AMS_VERSION);
        wp_enqueue_script('ams-admin', AMS_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), AMS_VERSION, true);
        
        wp_localize_script('ams-admin', 'amsAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ams_admin_nonce')
        ));
    }
    
    public function settings_page() {
        if (isset($_POST['ams_reset_counter']) && check_admin_referer('ams_reset_counter')) {
            $new_start = intval($_POST['reset_to_number']);
            update_option('ams_last_id', $new_start);
            echo '<div class="notice notice-success"><p>' . __('Membership counter has been reset!', 'ams') . '</p></div>';
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Membership System Settings', 'ams'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('ams_settings'); ?>
                
                <h2 class="nav-tab-wrapper">
                    <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'ams'); ?></a>
                    <a href="#membership-id" class="nav-tab"><?php _e('Membership ID', 'ams'); ?></a>
                    <a href="#email" class="nav-tab"><?php _e('Email', 'ams'); ?></a>
                    <a href="#display" class="nav-tab"><?php _e('Display', 'ams'); ?></a>
                </h2>
                
                <div id="general" class="tab-content">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ams_membership_products"><?php _e('Membership Products', 'ams'); ?></label>
                            </th>
                            <td>
                                <select name="ams_membership_products[]" id="ams_membership_products" multiple style="width: 400px; height: 150px;">
                                    <?php
                                    $selected_products = get_option('ams_membership_products', array());
                                    $args = array(
                                        'post_type' => 'product',
                                        'posts_per_page' => -1,
                                        'orderby' => 'title',
                                        'order' => 'ASC'
                                    );
                                    $products = get_posts($args);
                                    foreach ($products as $product) {
                                        $selected = in_array($product->ID, (array)$selected_products) ? 'selected' : '';
                                        echo '<option value="' . $product->ID . '" ' . $selected . '>' . $product->post_title . ' (ID: ' . $product->ID . ')</option>';
                                    }
                                    ?>
                                </select>
                                <p class="description"><?php _e('Select products that will trigger membership ID generation. Hold Ctrl/Cmd to select multiple.', 'ams'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ams_auto_complete"><?php _e('Auto Complete Orders', 'ams'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ams_auto_complete" id="ams_auto_complete" value="1" <?php checked(get_option('ams_auto_complete'), 1); ?>>
                                    <?php _e('Automatically complete orders containing membership products', 'ams'); ?>
                                </label>
                                <p class="description"><?php _e('Note: You can also set products as "Virtual" in WooCommerce for auto-completion.', 'ams'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="membership-id" class="tab-content" style="display:none;">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ams_membership_prefix"><?php _e('ID Prefix', 'ams'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="ams_membership_prefix" id="ams_membership_prefix" value="<?php echo esc_attr(get_option('ams_membership_prefix', 'PAU')); ?>" class="regular-text">
                                <p class="description"><?php _e('Prefix for membership IDs (e.g., PAU, MEM, etc.)', 'ams'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ams_membership_padding"><?php _e('Number Padding', 'ams'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="ams_membership_padding" id="ams_membership_padding" value="<?php echo esc_attr(get_option('ams_membership_padding', 5)); ?>" min="1" max="10" class="small-text">
                                <p class="description"><?php _e('Number of digits (e.g., 5 = 00001, 00002, etc.)', 'ams'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label><?php _e('Current Counter', 'ams'); ?></label>
                            </th>
                            <td>
                                <strong><?php echo esc_html(get_option('ams_last_id', 100)); ?></strong>
                                <p class="description">
                                    <?php 
                                    $prefix = get_option('ams_membership_prefix', 'PAU');
                                    $padding = get_option('ams_membership_padding', 5);
                                    $next_id = intval(get_option('ams_last_id', 100)) + 1;
                                    $preview = $prefix . str_pad($next_id, $padding, '0', STR_PAD_LEFT);
                                    printf(__('Next ID will be: <strong>%s</strong>', 'ams'), $preview);
                                    ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <h3><?php _e('Reset Counter', 'ams'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="reset_to_number"><?php _e('Reset To', 'ams'); ?></label>
                            </th>
                            <td>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field('ams_reset_counter'); ?>
                                    <input type="number" name="reset_to_number" id="reset_to_number" value="<?php echo esc_attr(get_option('ams_membership_start_number', 100)); ?>" class="small-text">
                                    <input type="submit" name="ams_reset_counter" class="button button-secondary" value="<?php _e('Reset Counter', 'ams'); ?>" onclick="return confirm('<?php _e('Are you sure? This will affect the next membership ID generated.', 'ams'); ?>');">
                                    <p class="description"><?php _e('Use this for testing or to set a specific starting number.', 'ams'); ?></p>
                                </form>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="email" class="tab-content" style="display:none;">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ams_send_email"><?php _e('Send Email', 'ams'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ams_send_email" id="ams_send_email" value="1" <?php checked(get_option('ams_send_email', 1), 1); ?>>
                                    <?php _e('Send membership ID email to users', 'ams'); ?>
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ams_email_subject"><?php _e('Email Subject', 'ams'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="ams_email_subject" id="ams_email_subject" value="<?php echo esc_attr(get_option('ams_email_subject', 'Membership ID Anda')); ?>" class="large-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ams_email_message"><?php _e('Email Message', 'ams'); ?></label>
                            </th>
                            <td>
                                <?php
                                $default_message = "Hi {user_name},\n\nMembership ID anda: {membership_id}\n\nAnda boleh log masuk di {account_url} untuk melihat maklumat keahlian anda.\n\nTerima kasih.";
                                wp_editor(
                                    get_option('ams_email_message', $default_message),
                                    'ams_email_message',
                                    array(
                                        'textarea_name' => 'ams_email_message',
                                        'textarea_rows' => 10,
                                        'media_buttons' => false
                                    )
                                );
                                ?>
                                <p class="description">
                                    <?php _e('Available placeholders:', 'ams'); ?>
                                    <code>{user_name}</code>, <code>{membership_id}</code>, <code>{account_url}</code>, <code>{site_name}</code>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="display" class="tab-content" style="display:none;">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ams_show_in_checkout"><?php _e('Show in Checkout', 'ams'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ams_show_in_checkout" id="ams_show_in_checkout" value="1" <?php checked(get_option('ams_show_in_checkout', 1), 1); ?>>
                                    <?php _e('Display custom fields in WooCommerce checkout', 'ams'); ?>
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ams_show_in_myaccount"><?php _e('Show in My Account', 'ams'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ams_show_in_myaccount" id="ams_show_in_myaccount" value="1" <?php checked(get_option('ams_show_in_myaccount', 1), 1); ?>>
                                    <?php _e('Add custom tab in My Account page', 'ams'); ?>
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ams_myaccount_tab_title"><?php _e('My Account Tab Title', 'ams'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="ams_myaccount_tab_title" id="ams_myaccount_tab_title" value="<?php echo esc_attr(get_option('ams_myaccount_tab_title', 'Maklumat Akademik')); ?>" class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ams_myaccount_tab_slug"><?php _e('My Account Tab Slug', 'ams'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="ams_myaccount_tab_slug" id="ams_myaccount_tab_slug" value="<?php echo esc_attr(get_option('ams_myaccount_tab_slug', 'maklumat-akademik')); ?>" class="regular-text">
                                <p class="description"><?php _e('URL-friendly slug (no spaces, lowercase)', 'ams'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-content').hide();
                $($(this).attr('href')).show();
            });
        });
        </script>
        <?php
    }
    
    public function fields_page() {
        require_once AMS_PLUGIN_DIR . 'includes/admin/fields-page.php';
    }
    
    public function products_page() {
        require_once AMS_PLUGIN_DIR . 'includes/admin/products-page.php';
    }
}
