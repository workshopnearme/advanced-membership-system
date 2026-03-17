<?php
if (!defined('ABSPATH')) {
    exit;
}

class AMS_Verification {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('wp_ajax_ams_verify_membership', array($this, 'ajax_verify'));
        add_action('wp_ajax_nopriv_ams_verify_membership', array($this, 'ajax_verify'));
        add_shortcode('ams_verification_form', array($this, 'verification_form_shortcode'));
    }
    
    public function verification_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => __('Verify Membership', 'ams'),
            'placeholder' => __('Enter Membership ID', 'ams'),
            'button_text' => __('Verify', 'ams'),
            'show_all_fields' => 'no'
        ), $atts);
        
        ob_start();
        ?>
        <div class="ams-verification-wrapper">
            <div class="ams-verification-form">
                <?php if (!empty($atts['title'])) : ?>
                    <h3><?php echo esc_html($atts['title']); ?></h3>
                <?php endif; ?>
                
                <form id="ams-verify-form" class="ams-verify-form">
                    <div class="form-group">
                        <input type="text" 
                               id="ams-membership-id-input" 
                               name="membership_id" 
                               placeholder="<?php echo esc_attr($atts['placeholder']); ?>" 
                               required
                               class="ams-input">
                        <button type="submit" class="ams-button">
                            <?php echo esc_html($atts['button_text']); ?>
                        </button>
                    </div>
                </form>
                
                <div id="ams-verification-result" class="ams-verification-result" style="display:none;"></div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#ams-verify-form').on('submit', function(e) {
                e.preventDefault();
                
                var membershipId = $('#ams-membership-id-input').val().trim();
                var $result = $('#ams-verification-result');
                var $button = $(this).find('button[type="submit"]');
                
                if (!membershipId) {
                    return;
                }
                
                $button.prop('disabled', true).text('<?php _e('Verifying...', 'ams'); ?>');
                $result.hide().html('');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'ams_verify_membership',
                        membership_id: membershipId,
                        show_all: '<?php echo esc_js($atts['show_all_fields']); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html(response.data.html).addClass('success').removeClass('error').fadeIn();
                        } else {
                            $result.html('<p class="error-message">' + response.data.message + '</p>')
                                   .addClass('error').removeClass('success').fadeIn();
                        }
                    },
                    error: function() {
                        $result.html('<p class="error-message"><?php _e('An error occurred. Please try again.', 'ams'); ?></p>')
                               .addClass('error').removeClass('success').fadeIn();
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('<?php echo esc_js($atts['button_text']); ?>');
                    }
                });
            });
        });
        </script>
        
        <style>
        .ams-verification-wrapper {
            max-width: 600px;
            margin: 20px auto;
        }
        .ams-verification-form {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .ams-verification-form h3 {
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
        }
        .ams-verify-form .form-group {
            display: flex;
            gap: 10px;
        }
        .ams-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .ams-button {
            padding: 12px 30px;
            background: #0073aa;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .ams-button:hover {
            background: #005177;
        }
        .ams-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .ams-verification-result {
            margin-top: 20px;
            padding: 20px;
            border-radius: 4px;
            background: white;
        }
        .ams-verification-result.success {
            border-left: 4px solid #46b450;
        }
        .ams-verification-result.error {
            border-left: 4px solid #dc3232;
        }
        .ams-member-info {
            line-height: 1.8;
        }
        .ams-member-info h4 {
            margin-top: 20px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 5px;
        }
        .ams-member-info p {
            margin: 8px 0;
        }
        .ams-member-info strong {
            display: inline-block;
            min-width: 150px;
            color: #555;
        }
        .error-message {
            color: #dc3232;
            font-weight: bold;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_verify() {
        $membership_id = isset($_POST['membership_id']) ? sanitize_text_field($_POST['membership_id']) : '';
        $show_all = isset($_POST['show_all']) && $_POST['show_all'] === 'yes';
        
        if (empty($membership_id)) {
            wp_send_json_error(array(
                'message' => __('Please enter a membership ID.', 'ams')
            ));
        }
        
        $member_data = AMS_Membership_ID::verify_membership($membership_id);
        
        if (!$member_data) {
            wp_send_json_error(array(
                'message' => __('Membership ID not found. Please check and try again.', 'ams')
            ));
        }
        
        $html = $this->render_member_info($member_data, $show_all);
        
        wp_send_json_success(array(
            'html' => $html,
            'data' => $member_data
        ));
    }
    
    private function render_member_info($data, $show_all = false) {
        ob_start();
        ?>
        <div class="ams-member-info">
            <h4><?php _e('Membership Information', 'ams'); ?></h4>
            <p><strong><?php _e('Membership ID:', 'ams'); ?></strong> <?php echo esc_html($data['membership_id']); ?></p>
            <p><strong><?php _e('Name:', 'ams'); ?></strong> <?php echo esc_html($data['name']); ?></p>
            <?php if ($show_all) : ?>
                <p><strong><?php _e('Email:', 'ams'); ?></strong> <?php echo esc_html($data['email']); ?></p>
            <?php endif; ?>
            <p><strong><?php _e('Member Since:', 'ams'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($data['registered'])); ?></p>
            
            <?php if (!empty($data['fields'])) : ?>
                <?php
                $fields_by_group = array();
                foreach ($data['fields'] as $field_id => $field_data) {
                    $fields = AMS_Fields::get_fields(true);
                    $group = 'Other';
                    foreach ($fields as $f) {
                        if ($f['id'] === $field_id && !empty($f['group'])) {
                            $group = $f['group'];
                            break;
                        }
                    }
                    if (!isset($fields_by_group[$group])) {
                        $fields_by_group[$group] = array();
                    }
                    $fields_by_group[$group][$field_id] = $field_data;
                }
                ?>
                
                <?php foreach ($fields_by_group as $group_name => $fields) : ?>
                    <h4><?php echo esc_html($group_name); ?></h4>
                    <?php foreach ($fields as $field_data) : ?>
                        <p><strong><?php echo esc_html($field_data['label']); ?>:</strong> <?php echo esc_html($field_data['value']); ?></p>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php
            $membership_status = $this->get_membership_status($data['user_id']);
            if ($membership_status) :
            ?>
                <h4><?php _e('Membership Status', 'ams'); ?></h4>
                <?php echo $membership_status; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function get_membership_status($user_id) {
        if (!function_exists('wc_memberships_get_user_memberships')) {
            return '';
        }
        
        $memberships = wc_memberships_get_user_memberships($user_id);
        
        if (empty($memberships)) {
            return '';
        }
        
        ob_start();
        foreach ($memberships as $membership) {
            $plan = $membership->get_plan();
            $status = $membership->get_status();
            $status_label = wc_memberships_get_user_membership_status_name($status);
            
            echo '<p><strong>' . esc_html($plan->get_name()) . ':</strong> ';
            echo '<span class="membership-status status-' . esc_attr($status) . '">' . esc_html($status_label) . '</span>';
            
            if ($membership->get_end_date()) {
                echo ' - ' . sprintf(__('Expires: %s', 'ams'), date_i18n(get_option('date_format'), strtotime($membership->get_end_date())));
            }
            
            echo '</p>';
        }
        
        return ob_get_clean();
    }
}
