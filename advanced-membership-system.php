<?php
/**
 * Plugin Name: Advanced Membership System
 * Plugin URI: https://cloudswired.com.my
 * Description: Complete membership registration system with custom fields, auto ID generation, verification, and WooCommerce integration
 * Version: 1.0.0
 * Author: Shukry Radzi
 * Author URI: https://cloudswired.com.my
 * Text Domain: ams
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AMS_VERSION', '1.0.0');
define('AMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AMS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Main plugin class
class Advanced_Membership_System {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    private function includes() {
        // Check if files exist before loading
        $files = array(
            'includes/class-ams-settings.php',
            'includes/class-ams-fields.php',
            'includes/class-ams-membership-id.php',
            'includes/class-ams-woocommerce.php',
            'includes/class-ams-my-account.php',
            'includes/class-ams-verification.php',
            'includes/class-ams-admin.php',
            'includes/class-ams-shortcodes.php',
            'includes/class-ams-integrations.php'
        );
        
        foreach ($files as $file) {
            $filepath = AMS_PLUGIN_DIR . $file;
            if (file_exists($filepath)) {
                require_once $filepath;
            } else {
                add_action('admin_notices', function() use ($file) {
                    echo '<div class="notice notice-error"><p>AMS Error: Missing file ' . esc_html($file) . '</p></div>';
                });
            }
        }
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize immediately instead of waiting for plugins_loaded
        add_action('init', array($this, 'init'), 0);
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('ams', false, dirname(AMS_PLUGIN_BASENAME) . '/languages');
        
        // Initialize classes only if they exist
        if (class_exists('AMS_Settings')) {
            AMS_Settings::instance();
        }
        if (class_exists('AMS_Fields')) {
            AMS_Fields::instance();
        }
        if (class_exists('AMS_Membership_ID')) {
            AMS_Membership_ID::instance();
        }
        if (class_exists('AMS_WooCommerce')) {
            AMS_WooCommerce::instance();
        }
        if (class_exists('AMS_My_Account')) {
            AMS_My_Account::instance();
        }
        if (class_exists('AMS_Verification')) {
            AMS_Verification::instance();
        }
        if (class_exists('AMS_Admin')) {
            AMS_Admin::instance();
        }
        if (class_exists('AMS_Shortcodes')) {
            AMS_Shortcodes::instance();
        }
        if (class_exists('AMS_Integrations')) {
            AMS_Integrations::instance();
        }
    }
    
    public function activate() {
        // Set default options
        if (!get_option('ams_membership_prefix')) {
            update_option('ams_membership_prefix', 'PAU');
        }
        if (!get_option('ams_membership_start_number')) {
            update_option('ams_membership_start_number', 100);
        }
        if (!get_option('ams_membership_padding')) {
            update_option('ams_membership_padding', 5);
        }
        if (!get_option('ams_last_id')) {
            update_option('ams_last_id', 100);
        }
        
        // Set default fields
        if (!get_option('ams_custom_fields')) {
            $default_fields = array(
                array(
                    'id' => 'diploma_title',
                    'label' => 'Tajuk Diploma',
                    'type' => 'text',
                    'group' => 'Diploma',
                    'enabled' => true
                ),
                array(
                    'id' => 'diploma_enroll_year',
                    'label' => 'Tahun Mula Diploma',
                    'type' => 'text',
                    'group' => 'Diploma',
                    'enabled' => true
                ),
                array(
                    'id' => 'diploma_graduate_year',
                    'label' => 'Tahun Tamat Diploma',
                    'type' => 'text',
                    'group' => 'Diploma',
                    'enabled' => true
                ),
                array(
                    'id' => 'degree_title',
                    'label' => 'Tajuk Ijazah',
                    'type' => 'text',
                    'group' => 'Ijazah',
                    'enabled' => true
                ),
                array(
                    'id' => 'degree_enroll_year',
                    'label' => 'Tahun Mula Ijazah',
                    'type' => 'text',
                    'group' => 'Ijazah',
                    'enabled' => true
                ),
                array(
                    'id' => 'degree_graduate_year',
                    'label' => 'Tahun Tamat Ijazah',
                    'type' => 'text',
                    'group' => 'Ijazah',
                    'enabled' => true
                ),
                array(
                    'id' => 'master_title',
                    'label' => 'Tajuk Sarjana',
                    'type' => 'text',
                    'group' => 'Sarjana',
                    'enabled' => true
                ),
                array(
                    'id' => 'master_enroll_year',
                    'label' => 'Tahun Mula Sarjana',
                    'type' => 'text',
                    'group' => 'Sarjana',
                    'enabled' => true
                ),
                array(
                    'id' => 'master_graduate_year',
                    'label' => 'Tahun Tamat Sarjana',
                    'type' => 'text',
                    'group' => 'Sarjana',
                    'enabled' => true
                ),
                array(
                    'id' => 'phd_title',
                    'label' => 'Tajuk PhD',
                    'type' => 'text',
                    'group' => 'PhD',
                    'enabled' => true
                ),
                array(
                    'id' => 'phd_enroll_year',
                    'label' => 'Tahun Mula PhD',
                    'type' => 'text',
                    'group' => 'PhD',
                    'enabled' => true
                ),
                array(
                    'id' => 'phd_graduate_year',
                    'label' => 'Tahun Tamat PhD',
                    'type' => 'text',
                    'group' => 'PhD',
                    'enabled' => true
                )
            );
            update_option('ams_custom_fields', $default_fields);
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function AMS() {
    return Advanced_Membership_System::instance();
}

// Start plugin
add_action('plugins_loaded', 'AMS', 1);

// Register admin menu DIRECTLY - bypass class chain to ensure menu always appears
add_action('admin_menu', 'ams_register_admin_menu');
function ams_register_admin_menu() {
    add_menu_page(
        'Membership System',
        'Membership',
        'manage_options',
        'ams-settings',
        'ams_render_settings_page',
        'dashicons-id-alt',
        56
    );
    add_submenu_page('ams-settings', 'Settings', 'Settings', 'manage_options', 'ams-settings', 'ams_render_settings_page');
    add_submenu_page('ams-settings', 'Custom Fields', 'Custom Fields', 'manage_options', 'ams-fields', 'ams_render_fields_page');
    add_submenu_page('ams-settings', 'Products', 'Products', 'manage_options', 'ams-products', 'ams_render_products_page');
}

function ams_render_settings_page() {
    if (class_exists('AMS_Settings') && method_exists(AMS_Settings::instance(), 'settings_page')) {
        AMS_Settings::instance()->settings_page();
    } else {
        echo '<div class="wrap"><h1>Membership System</h1><p>Loading settings...</p></div>';
    }
}

function ams_render_fields_page() {
    if (class_exists('AMS_Fields')) {
        require_once AMS_PLUGIN_DIR . 'includes/admin/fields-page.php';
    }
}

function ams_render_products_page() {
    require_once AMS_PLUGIN_DIR . 'includes/admin/products-page.php';
}
