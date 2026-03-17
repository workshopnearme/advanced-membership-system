<?php
if (!defined('ABSPATH')) {
    exit;
}

class AMS_Fields {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('wp_ajax_ams_save_fields', array($this, 'ajax_save_fields'));
        add_action('wp_ajax_ams_add_field', array($this, 'ajax_add_field'));
        add_action('wp_ajax_ams_delete_field', array($this, 'ajax_delete_field'));
    }
    
    public static function get_fields($enabled_only = false) {
        $fields = get_option('ams_custom_fields', array());
        
        if ($enabled_only) {
            $fields = array_filter($fields, function($field) {
                return !empty($field['enabled']);
            });
        }
        
        return $fields;
    }
    
    public static function get_field_ids($enabled_only = false) {
        $fields = self::get_fields($enabled_only);
        return array_column($fields, 'id');
    }
    
    public static function get_fields_by_group($enabled_only = false) {
        $fields = self::get_fields($enabled_only);
        $grouped = array();
        
        foreach ($fields as $field) {
            $group = !empty($field['group']) ? $field['group'] : 'Other';
            if (!isset($grouped[$group])) {
                $grouped[$group] = array();
            }
            $grouped[$group][] = $field;
        }
        
        return $grouped;
    }
    
    public static function get_field_label($field_id) {
        $fields = self::get_fields();
        foreach ($fields as $field) {
            if ($field['id'] === $field_id) {
                return $field['label'];
            }
        }
        return ucwords(str_replace('_', ' ', $field_id));
    }
    
    public static function render_field($field, $value = '', $context = 'checkout') {
        $field_id = esc_attr($field['id']);
        $field_label = esc_html($field['label']);
        $field_type = !empty($field['type']) ? $field['type'] : 'text';
        $field_required = !empty($field['required']);
        $field_placeholder = !empty($field['placeholder']) ? esc_attr($field['placeholder']) : '';
        $field_class = !empty($field['class']) ? esc_attr($field['class']) : '';
        
        $value = esc_attr($value);
        
        switch ($context) {
            case 'checkout':
                woocommerce_form_field($field_id, array(
                    'type' => $field_type,
                    'label' => $field_label,
                    'placeholder' => $field_placeholder,
                    'required' => $field_required,
                    'class' => array('form-row-wide', $field_class),
                    'clear' => true
                ), $value);
                break;
                
            case 'myaccount':
                ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="<?php echo $field_id; ?>">
                        <?php echo $field_label; ?>
                        <?php if ($field_required) : ?><span class="required">*</span><?php endif; ?>
                    </label>
                    <?php if ($field_type === 'textarea') : ?>
                        <textarea name="<?php echo $field_id; ?>" id="<?php echo $field_id; ?>" class="woocommerce-Input woocommerce-Input--textarea input-text <?php echo $field_class; ?>" placeholder="<?php echo $field_placeholder; ?>" <?php echo $field_required ? 'required' : ''; ?>><?php echo $value; ?></textarea>
                    <?php elseif ($field_type === 'select' && !empty($field['options'])) : ?>
                        <select name="<?php echo $field_id; ?>" id="<?php echo $field_id; ?>" class="woocommerce-Input woocommerce-Input--select input-text <?php echo $field_class; ?>" <?php echo $field_required ? 'required' : ''; ?>>
                            <option value=""><?php _e('Select...', 'ams'); ?></option>
                            <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                                <option value="<?php echo esc_attr($option_value); ?>" <?php selected($value, $option_value); ?>><?php echo esc_html($option_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <input type="<?php echo esc_attr($field_type); ?>" name="<?php echo $field_id; ?>" id="<?php echo $field_id; ?>" value="<?php echo $value; ?>" class="woocommerce-Input woocommerce-Input--text input-text <?php echo $field_class; ?>" placeholder="<?php echo $field_placeholder; ?>" <?php echo $field_required ? 'required' : ''; ?>>
                    <?php endif; ?>
                </p>
                <?php
                break;
                
            case 'admin':
                ?>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $field_id; ?>"><?php echo $field_label; ?></label>
                    </th>
                    <td>
                        <?php if ($field_type === 'textarea') : ?>
                            <textarea name="<?php echo $field_id; ?>" id="<?php echo $field_id; ?>" class="regular-text <?php echo $field_class; ?>" rows="5"><?php echo $value; ?></textarea>
                        <?php elseif ($field_type === 'select' && !empty($field['options'])) : ?>
                            <select name="<?php echo $field_id; ?>" id="<?php echo $field_id; ?>" class="regular-text <?php echo $field_class; ?>">
                                <option value=""><?php _e('Select...', 'ams'); ?></option>
                                <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                                    <option value="<?php echo esc_attr($option_value); ?>" <?php selected($value, $option_value); ?>><?php echo esc_html($option_label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else : ?>
                            <input type="<?php echo esc_attr($field_type); ?>" name="<?php echo $field_id; ?>" id="<?php echo $field_id; ?>" value="<?php echo $value; ?>" class="regular-text <?php echo $field_class; ?>">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
                break;
        }
    }
    
    public function ajax_save_fields() {
        check_ajax_referer('ams_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $fields = isset($_POST['fields']) ? json_decode(stripslashes($_POST['fields']), true) : array();
        
        update_option('ams_custom_fields', $fields);
        
        wp_send_json_success('Fields saved successfully');
    }
    
    public function ajax_add_field() {
        check_ajax_referer('ams_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $field = array(
            'id' => sanitize_key($_POST['id']),
            'label' => sanitize_text_field($_POST['label']),
            'type' => sanitize_text_field($_POST['type']),
            'group' => sanitize_text_field($_POST['group']),
            'enabled' => true
        );
        
        $fields = get_option('ams_custom_fields', array());
        $fields[] = $field;
        update_option('ams_custom_fields', $fields);
        
        wp_send_json_success($field);
    }
    
    public function ajax_delete_field() {
        check_ajax_referer('ams_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $field_id = sanitize_key($_POST['field_id']);
        $fields = get_option('ams_custom_fields', array());
        
        $fields = array_filter($fields, function($field) use ($field_id) {
            return $field['id'] !== $field_id;
        });
        
        update_option('ams_custom_fields', array_values($fields));
        
        wp_send_json_success('Field deleted');
    }
}
