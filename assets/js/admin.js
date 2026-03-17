/* Advanced Membership System - Admin JavaScript */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Tab navigation
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.tab-content').hide();
            $($(this).attr('href')).show();
        });
        
        // Make fields sortable
        if ($('#ams-fields-list').length) {
            $('#ams-fields-list').sortable({
                handle: '.handle',
                placeholder: 'ui-state-highlight',
                update: function() {
                    $('#ams-save-status').html('<span style="color: orange;">⚠ Unsaved changes</span>');
                }
            });
        }
        
        // Add new field
        $('#ams-add-field-btn').on('click', function() {
            var fieldId = $('#new_field_id').val().trim();
            var fieldLabel = $('#new_field_label').val().trim();
            var fieldType = $('#new_field_type').val();
            var fieldGroup = $('#new_field_group').val().trim();
            
            if (!fieldId || !fieldLabel) {
                alert('Please fill in Field ID and Label');
                return;
            }
            
            // Validate field ID format
            if (!/^[a-z0-9_]+$/.test(fieldId)) {
                alert('Field ID can only contain lowercase letters, numbers, and underscores');
                return;
            }
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('Adding...');
            
            $.post(ajaxurl, {
                action: 'ams_add_field',
                nonce: amsAdmin.nonce,
                id: fieldId,
                label: fieldLabel,
                type: fieldType,
                group: fieldGroup
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || 'Error adding field');
                    $btn.prop('disabled', false).text('Add Field');
                }
            });
        });
        
        // Delete field
        $(document).on('click', '.delete-field', function() {
            if (!confirm('Are you sure you want to delete this field? This action cannot be undone.')) {
                return;
            }
            
            var fieldId = $(this).data('field-id');
            var $row = $(this).closest('tr');
            
            $.post(ajaxurl, {
                action: 'ams_delete_field',
                nonce: amsAdmin.nonce,
                field_id: fieldId
            }, function(response) {
                if (response.success) {
                    $row.fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert('Error deleting field');
                }
            });
        });
        
        // Track changes
        $(document).on('change', '.field-label, .field-type, .field-group, .field-enabled, .field-required', function() {
            $('#ams-save-status').html('<span style="color: orange;">⚠ Unsaved changes</span>');
        });
        
        // Save all fields
        $('#ams-save-fields-btn').on('click', function() {
            var fields = [];
            
            $('#ams-fields-list tr').each(function() {
                var $row = $(this);
                var field = {
                    id: $row.data('field-id'),
                    label: $row.find('.field-label').val(),
                    type: $row.find('.field-type').val(),
                    group: $row.find('.field-group').val(),
                    enabled: $row.find('.field-enabled').is(':checked'),
                    required: $row.find('.field-required').is(':checked')
                };
                fields.push(field);
            });
            
            $('.spinner').addClass('is-active');
            $('#ams-save-status').html('');
            
            $.post(ajaxurl, {
                action: 'ams_save_fields',
                nonce: amsAdmin.nonce,
                fields: JSON.stringify(fields)
            }, function(response) {
                $('.spinner').removeClass('is-active');
                if (response.success) {
                    $('#ams-save-status').html('<span style="color: green;">✓ Saved successfully!</span>');
                    setTimeout(function() {
                        $('#ams-save-status').fadeOut();
                    }, 3000);
                } else {
                    $('#ams-save-status').html('<span style="color: red;">✗ Error saving</span>');
                }
            });
        });
        
        // Preview membership ID format
        function updateMembershipPreview() {
            var prefix = $('#ams_membership_prefix').val() || 'PAU';
            var padding = parseInt($('#ams_membership_padding').val()) || 5;
            var lastId = parseInt($('#ams_last_id').text()) || 100;
            var nextId = lastId + 1;
            var preview = prefix + String(nextId).padStart(padding, '0');
            
            $('.membership-id-preview').text(preview);
        }
        
        $('#ams_membership_prefix, #ams_membership_padding').on('input', updateMembershipPreview);
        
        // Confirm before resetting counter
        $('input[name="ams_reset_counter"]').on('click', function(e) {
            if (!confirm('Are you sure? This will affect the next membership ID generated.')) {
                e.preventDefault();
            }
        });
        
    });
    
})(jQuery);
