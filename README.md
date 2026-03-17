# Advanced Membership System

A comprehensive WordPress plugin for managing membership registrations with WooCommerce integration.

## Features

### 🎯 Core Features
- **Custom Fields Management** - Create unlimited custom fields via admin UI
- **Auto Membership ID Generation** - Configurable format (e.g., PAU00101)
- **WooCommerce Integration** - Checkout fields, order meta, user meta sync
- **Member Verification** - Public verification system with shortcode
- **My Account Tab** - Members can view/edit their information
- **Admin Tools** - Full member management from WordPress admin

### 🔌 Integrations
- WooCommerce (required)
- WooCommerce Memberships (optional)
- WooCommerce Subscriptions (optional)

## Installation

1. Upload to `/wp-content/plugins/advanced-membership-system/`
2. Activate the plugin
3. Go to **Membership > Settings** to configure
4. Add custom fields in **Membership > Custom Fields**
5. Select membership products in **Membership > Products**

## Configuration

### 1. General Settings
Navigate to **Membership > Settings > General**
- Select which products trigger membership ID generation
- Enable/disable auto-complete for membership orders

### 2. Membership ID Settings
Navigate to **Membership > Settings > Membership ID**
- **Prefix**: Set your ID prefix (e.g., PAU, MEM, MEMBER)
- **Padding**: Number of digits (5 = 00001, 00002, etc.)
- **Reset Counter**: Reset to a specific number for testing

### 3. Custom Fields
Navigate to **Membership > Custom Fields**
- Add new fields with ID, label, type, and group
- Reorder fields by dragging
- Enable/disable fields
- Mark fields as required

### 4. Email Settings
Navigate to **Membership > Settings > Email**
- Customize email subject and message
- Available placeholders: `{user_name}`, `{membership_id}`, `{account_url}`, `{site_name}`

## Shortcodes

### Display Membership ID
```
[membership_id]
[membership_id default="Not registered yet"]
```

### Member Count
```
[membership_count]
[membership_count offset="100"]
```

### Membership Information
```
[membership_info]
[membership_info fields="diploma_title,degree_title" template="table"]
[membership_info show_email="yes" show_id="yes"]
```

### Specific Field Value
```
[membership_field field="diploma_title"]
[membership_field field="degree_title" default="N/A"]
```

### Membership Status (WooCommerce Memberships)
```
[membership_status]
[membership_status show_expiry="yes"]
```

### Verification Form
```
[ams_verification_form]
[ams_verification_form title="Verify Your Membership" button_text="Check Now"]
[ams_verification_form show_all_fields="yes"]
```

## Usage Examples

### Example 1: Basic Setup
1. Create a WooCommerce product called "Annual Membership"
2. Go to **Membership > Products** and select this product
3. Add custom fields like "Phone Number", "Organization", etc.
4. When customers purchase, they'll fill out the fields and receive a membership ID

### Example 2: Verification Page
Create a new page and add:
```
[ams_verification_form title="Verify Membership Status" button_text="Verify"]
```

### Example 3: Member Dashboard
Create a custom page template showing:
```
<h2>Welcome, [membership_field field="first_name"]!</h2>
<p>Your Membership ID: [membership_id]</p>
[membership_info template="table"]
[membership_status]
```

## Workflow

### Customer Journey
1. Customer adds membership product to cart
2. Proceeds to checkout
3. Fills out personal info + custom fields
4. Completes payment
5. Order auto-completes (if enabled)
6. Membership ID auto-generated
7. Email sent with membership ID
8. Data saved to user meta
9. Customer can view/edit in My Account

### Admin Management
1. View all members in **Users** list with Membership ID column
2. Edit user profiles to update membership data
3. Manually assign/edit membership IDs
4. Export member data (via WordPress user export)

## Developer Hooks

### Actions
```php
// After membership ID is generated
do_action('ams_membership_id_generated', $user_id, $membership_id, $order_id);

// After user data is saved from My Account
do_action('ams_user_data_saved', $user_id);

// After checkout data synced to user meta
do_action('ams_synced_to_user_meta', $user_id, $order_id);

// When subscription status changes
do_action('ams_subscription_status_changed', $user_id, $subscription, $new_status, $old_status);
```

### Filters
```php
// Modify verification data
add_filter('ams_verify_membership_data', function($data, $user_id) {
    // Add custom data
    return $data;
}, 10, 2);
```

## Troubleshooting

### Membership ID not generating?
- Check that the product is selected in **Membership > Products**
- Ensure order status is "Completed"
- Verify user is logged in during checkout

### Fields not showing in checkout?
- Go to **Membership > Settings > Display**
- Enable "Show in Checkout"
- Clear cache if using caching plugin

### Reset membership counter for testing?
- Go to **Membership > Settings > Membership ID**
- Use "Reset Counter" section
- Set to desired starting number

## Requirements

- WordPress 5.8+
- PHP 7.4+
- WooCommerce 5.0+
- WooCommerce Memberships (optional)
- WooCommerce Subscriptions (optional)

## Support

For issues or feature requests, please contact support or visit the plugin documentation.

## License

GPLv2 or later
