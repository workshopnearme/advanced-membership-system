<?php
if (!defined('ABSPATH')) {
    exit;
}

class AMS_Admin {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('show_user_profile', array($this, 'user_profile_fields'));
        add_action('edit_user_profile', array($this, 'user_profile_fields'));
        add_action('personal_options_update', array($this, 'save_user_profile_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_profile_fields'));
        
        add_filter('manage_users_columns', array($this, 'add_user_columns'));
        add_filter('manage_users_custom_column', array($this, 'user_column_content'), 10, 3);
        add_filter('manage_users_sortable_columns', array($this, 'sortable_columns'));
        add_action('pre_get_users', array($this, 'sort_users_by_membership_id'));
    }
    
    public function user_profile_fields($user) {
        if (!current_user_can('edit_users')) {
            return;
        }
        
        $fields = AMS_Fields::get_fields(true);
        $membership_id = AMS_Membership_ID::get_user_membership_id($user->ID);
        
        ?>
        <h3><?php _e('Maklumat Keahlian', 'ams'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="_membership_id"><?php _e('Membership ID', 'ams'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="_membership_id" 
                           id="_membership_id" 
                           value="<?php echo esc_attr($membership_id); ?>" 
                           class="regular-text">
                    <p class="description"><?php _e('Leave empty to auto-generate on next membership purchase.', 'ams'); ?></p>
                </td>
            </tr>
            
            <?php
            $fields_by_group = AMS_Fields::get_fields_by_group(true);
            foreach ($fields_by_group as $group_name => $group_fields) :
                if ($group_name !== 'Other') :
            ?>
                <tr>
                    <td colspan="2"><h4 style="margin: 20px 0 10px 0; border-bottom: 1px solid #ddd; padding-bottom: 5px;"><?php echo esc_html($group_name); ?></h4></td>
                </tr>
            <?php
                endif;
                
                foreach ($group_fields as $field) :
                    $value = get_user_meta($user->ID, $field['id'], true);
                    AMS_Fields::render_field($field, $value, 'admin');
                endforeach;
            endforeach;
            ?>
        </table>
        <?php
    }
    
    public function save_user_profile_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }
        
        if (isset($_POST['_membership_id'])) {
            $membership_id = sanitize_text_field($_POST['_membership_id']);
            update_user_meta($user_id, '_membership_id', $membership_id);
        }
        
        $fields = AMS_Fields::get_field_ids(true);
        foreach ($fields as $field_id) {
            if (isset($_POST[$field_id])) {
                update_user_meta($user_id, $field_id, sanitize_text_field($_POST[$field_id]));
            }
        }
    }
    
    public function add_user_columns($columns) {
        $columns['membership_id'] = __('Membership ID', 'ams');
        $columns['membership_status'] = __('Membership Status', 'ams');
        return $columns;
    }
    
    public function user_column_content($value, $column_name, $user_id) {
        switch ($column_name) {
            case 'membership_id':
                $membership_id = AMS_Membership_ID::get_user_membership_id($user_id);
                return $membership_id ? esc_html($membership_id) : '—';
                
            case 'membership_status':
                if (function_exists('wc_memberships_get_user_memberships')) {
                    $memberships = wc_memberships_get_user_memberships($user_id);
                    if (!empty($memberships)) {
                        $statuses = array();
                        foreach ($memberships as $membership) {
                            $status = $membership->get_status();
                            $statuses[] = '<span class="membership-status-' . esc_attr($status) . '">' . 
                                         esc_html(wc_memberships_get_user_membership_status_name($status)) . 
                                         '</span>';
                        }
                        return implode(', ', $statuses);
                    }
                }
                return '—';
        }
        
        return $value;
    }
    
    public function sortable_columns($columns) {
        $columns['membership_id'] = 'membership_id';
        return $columns;
    }
    
    public function sort_users_by_membership_id($query) {
        if (!is_admin()) {
            return;
        }
        
        $orderby = $query->get('orderby');
        
        if ('membership_id' === $orderby) {
            $query->set('meta_key', '_membership_id');
            $query->set('orderby', 'meta_value');
        }
    }
}
