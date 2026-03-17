<?php
if (!defined('ABSPATH')) {
    exit;
}

$fields = AMS_Fields::get_fields();
?>

<div class="wrap">
    <h1><?php _e('Custom Fields Management', 'ams'); ?></h1>
    
    <p><?php _e('Manage custom fields that will be collected during checkout and displayed in user profiles.', 'ams'); ?></p>
    
    <div class="ams-fields-manager">
        <div class="ams-add-field-form">
            <h2><?php _e('Add New Field', 'ams'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="new_field_id"><?php _e('Field ID', 'ams'); ?></label></th>
                    <td><input type="text" id="new_field_id" class="regular-text" placeholder="e.g., diploma_title"></td>
                </tr>
                <tr>
                    <th><label for="new_field_label"><?php _e('Label', 'ams'); ?></label></th>
                    <td><input type="text" id="new_field_label" class="regular-text" placeholder="e.g., Tajuk Diploma"></td>
                </tr>
                <tr>
                    <th><label for="new_field_type"><?php _e('Type', 'ams'); ?></label></th>
                    <td>
                        <select id="new_field_type">
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="tel">Phone</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="textarea">Textarea</option>
                            <option value="select">Select</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="new_field_group"><?php _e('Group', 'ams'); ?></label></th>
                    <td><input type="text" id="new_field_group" class="regular-text" placeholder="e.g., Diploma, Ijazah, Sarjana"></td>
                </tr>
            </table>
            <button type="button" id="ams-add-field-btn" class="button button-primary"><?php _e('Add Field', 'ams'); ?></button>
        </div>
        
        <hr>
        
        <h2><?php _e('Existing Fields', 'ams'); ?></h2>
        <p class="description"><?php _e('Drag to reorder fields. Changes are saved automatically.', 'ams'); ?></p>
        
        <table class="wp-list-table widefat fixed striped" id="ams-fields-table">
            <thead>
                <tr>
                    <th width="30"><?php _e('Order', 'ams'); ?></th>
                    <th><?php _e('Field ID', 'ams'); ?></th>
                    <th><?php _e('Label', 'ams'); ?></th>
                    <th><?php _e('Type', 'ams'); ?></th>
                    <th><?php _e('Group', 'ams'); ?></th>
                    <th width="80"><?php _e('Enabled', 'ams'); ?></th>
                    <th width="80"><?php _e('Required', 'ams'); ?></th>
                    <th width="100"><?php _e('Actions', 'ams'); ?></th>
                </tr>
            </thead>
            <tbody id="ams-fields-list">
                <?php foreach ($fields as $index => $field) : ?>
                <tr data-field-id="<?php echo esc_attr($field['id']); ?>">
                    <td class="handle" style="cursor: move;">☰</td>
                    <td><code><?php echo esc_html($field['id']); ?></code></td>
                    <td>
                        <input type="text" 
                               class="field-label regular-text" 
                               value="<?php echo esc_attr($field['label']); ?>" 
                               data-field="label">
                    </td>
                    <td>
                        <select class="field-type" data-field="type">
                            <option value="text" <?php selected($field['type'] ?? 'text', 'text'); ?>>Text</option>
                            <option value="email" <?php selected($field['type'] ?? 'text', 'email'); ?>>Email</option>
                            <option value="tel" <?php selected($field['type'] ?? 'text', 'tel'); ?>>Phone</option>
                            <option value="number" <?php selected($field['type'] ?? 'text', 'number'); ?>>Number</option>
                            <option value="date" <?php selected($field['type'] ?? 'text', 'date'); ?>>Date</option>
                            <option value="textarea" <?php selected($field['type'] ?? 'text', 'textarea'); ?>>Textarea</option>
                            <option value="select" <?php selected($field['type'] ?? 'text', 'select'); ?>>Select</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" 
                               class="field-group" 
                               value="<?php echo esc_attr($field['group'] ?? ''); ?>" 
                               data-field="group">
                    </td>
                    <td>
                        <input type="checkbox" 
                               class="field-enabled" 
                               <?php checked(!empty($field['enabled'])); ?> 
                               data-field="enabled">
                    </td>
                    <td>
                        <input type="checkbox" 
                               class="field-required" 
                               <?php checked(!empty($field['required'])); ?> 
                               data-field="required">
                    </td>
                    <td>
                        <button type="button" class="button button-small delete-field" data-field-id="<?php echo esc_attr($field['id']); ?>">
                            <?php _e('Delete', 'ams'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p class="submit">
            <button type="button" id="ams-save-fields-btn" class="button button-primary button-large">
                <?php _e('Save All Changes', 'ams'); ?>
            </button>
            <span class="spinner" style="float: none; margin: 0 10px;"></span>
            <span id="ams-save-status"></span>
        </p>
    </div>
</div>


<style>
.ams-fields-manager {
    max-width: 1200px;
}
.ams-add-field-form {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    margin: 20px 0;
}
#ams-fields-table input[type="text"] {
    width: 100%;
}
#ams-fields-table select {
    width: 100%;
}
.ui-state-highlight {
    height: 50px;
    background: #ffffcc;
}
.handle {
    color: #999;
    font-size: 18px;
}
</style>
