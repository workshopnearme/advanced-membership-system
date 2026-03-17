# Advanced Membership System - Usage Guide

## Quick Start Guide

### Step 1: Activate Plugin
1. Go to **Plugins** in WordPress admin
2. Find "Advanced Membership System"
3. Click **Activate**

### Step 2: Configure Settings
1. Go to **Membership > Settings**
2. Configure your membership ID format:
   - Prefix: `PAU` (or your choice)
   - Padding: `5` (for 5-digit numbers)
3. Save settings

### Step 3: Add Custom Fields
1. Go to **Membership > Custom Fields**
2. Click **Add New Field**
3. Example fields:
   - Field ID: `phone_number`
   - Label: `Nombor Telefon`
   - Type: `tel`
   - Group: `Contact Info`
4. Drag to reorder fields
5. Click **Save All Changes**

### Step 4: Select Membership Products
1. Go to **Membership > Products**
2. Check the products that represent memberships
3. Click **Save Product Selection**

### Step 5: Test the System
1. Log out and create a test order
2. Add a membership product to cart
3. Go to checkout - you'll see custom fields
4. Complete the order
5. Check email for membership ID
6. Log in and go to **My Account** to see the new tab

## Common Use Cases

### Use Case 1: Academic Institution Membership

**Fields to Create:**
- `diploma_title` - Tajuk Diploma
- `diploma_year` - Tahun Tamat Diploma
- `degree_title` - Tajuk Ijazah
- `degree_year` - Tahun Tamat Ijazah
- `institution` - Institusi Pengajian

**Settings:**
- Prefix: `ALUMNI`
- Products: "Alumni Membership - Annual"

**Verification Page:**
Create a page called "Verify Alumni" with:
```
[ams_verification_form title="Semak Status Alumni" button_text="Semak"]
```

### Use Case 2: Professional Association

**Fields to Create:**
- `license_number` - Nombor Lesen
- `specialization` - Bidang Kepakaran
- `years_experience` - Tahun Pengalaman
- `company` - Syarikat

**Settings:**
- Prefix: `PRO`
- Enable auto-complete orders
- Send email notifications

**Member Dashboard:**
Create a custom page with:
```
<h2>Selamat Datang!</h2>
<p>ID Keahlian: <strong>[membership_id]</strong></p>

[membership_info template="table" fields="license_number,specialization,company"]

[membership_status show_expiry="yes"]
```

### Use Case 3: Subscription-Based Membership

**Setup:**
1. Install WooCommerce Subscriptions
2. Create subscription products
3. Link to membership plans
4. Enable in **Membership > Products**

**Benefits:**
- Auto-renewal of memberships
- Membership ID persists across renewals
- Status updates automatically

## Advanced Configurations

### Custom Email Template

Go to **Membership > Settings > Email** and customize:

**Subject:**
```
Selamat Datang ke [site_name]!
```

**Message:**
```
Hi {user_name},

Terima kasih kerana mendaftar sebagai ahli kami!

ID Keahlian Anda: <strong>{membership_id}</strong>

Anda boleh log masuk di {account_url} untuk:
- Melihat maklumat keahlian
- Mengemas kini profil
- Melihat status keahlian

Jika ada sebarang pertanyaan, sila hubungi kami.

Terima kasih,
Pasukan {site_name}
```

### Conditional Field Display

If you want certain fields to only show for specific products, you can use this code snippet:

```php
add_filter('ams_checkout_fields', function($fields, $cart) {
    // Only show PhD fields if buying premium membership
    $has_premium = false;
    foreach ($cart->get_cart() as $item) {
        if ($item['product_id'] == 123) { // Premium product ID
            $has_premium = true;
            break;
        }
    }
    
    if (!$has_premium) {
        // Remove PhD fields
        $fields = array_filter($fields, function($field) {
            return strpos($field['id'], 'phd_') !== 0;
        });
    }
    
    return $fields;
}, 10, 2);
```

### Custom Verification Display

Customize how verification results appear:

```php
add_filter('ams_verify_membership_data', function($data, $user_id) {
    // Add custom data
    $data['custom_field'] = get_user_meta($user_id, 'custom_meta', true);
    
    // Add membership tier
    if (function_exists('wc_memberships_get_user_memberships')) {
        $memberships = wc_memberships_get_user_memberships($user_id);
        if (!empty($memberships)) {
            $data['tier'] = $memberships[0]->get_plan()->get_name();
        }
    }
    
    return $data;
}, 10, 2);
```

## Troubleshooting

### Issue: Fields not saving in checkout

**Solution:**
1. Check if fields are enabled in **Custom Fields**
2. Verify "Show in Checkout" is enabled in **Settings > Display**
3. Clear browser cache and try again
4. Check for JavaScript errors in browser console

### Issue: Membership ID not generating

**Solution:**
1. Ensure product is selected in **Membership > Products**
2. Check order status is "Completed"
3. Verify user was logged in during checkout
4. Check if user already has a membership ID (won't generate duplicate)

### Issue: Email not sending

**Solution:**
1. Check "Send Email" is enabled in **Settings > Email**
2. Test WordPress email with a plugin like "WP Mail SMTP"
3. Check spam folder
4. Verify email placeholders are correct

### Issue: My Account tab not showing

**Solution:**
1. Enable "Show in My Account" in **Settings > Display**
2. Go to **Settings > Permalinks** and click "Save Changes" to flush rewrite rules
3. Clear cache if using caching plugin
4. Check if WooCommerce My Account page is set correctly

### Issue: Verification form not working

**Solution:**
1. Check if jQuery is loaded on the page
2. Look for JavaScript errors in browser console
3. Ensure membership ID exists in database
4. Try different browser or clear cache

## Best Practices

### 1. Field Organization
- Group related fields together (e.g., all diploma fields in "Diploma" group)
- Use clear, descriptive labels in Malay or English
- Keep field IDs lowercase with underscores
- Don't use too many required fields (reduces conversion)

### 2. Membership ID Format
- Keep prefix short (2-4 characters)
- Use 5-digit padding for up to 99,999 members
- Don't reset counter unless absolutely necessary
- Document your ID format for reference

### 3. Product Setup
- Set membership products as "Virtual" for auto-completion
- Use clear product names
- Add product descriptions explaining what's included
- Consider using WooCommerce Subscriptions for recurring memberships

### 4. Email Communication
- Keep emails concise and clear
- Include direct link to My Account page
- Mention membership ID prominently
- Add contact information for support

### 5. Verification System
- Place verification form on a public page
- Consider adding it to footer or header menu
- Use clear instructions
- Limit displayed information for privacy

## Integration Tips

### With WooCommerce Memberships

The plugin automatically integrates. Benefits:
- Membership ID linked to membership plans
- Status tracking (active, expired, cancelled)
- Access control to content
- Drip content scheduling

### With WooCommerce Subscriptions

Automatic integration provides:
- Recurring billing
- Automatic renewal reminders
- Subscription status in verification
- Persistent membership ID across renewals

### With Email Marketing (Mailchimp, etc.)

Export user data including membership IDs:
1. Go to **Users** in WordPress
2. Use export plugin or custom code
3. Include membership ID and custom fields
4. Import to your email platform

## Security Considerations

1. **Membership ID Privacy**: Don't display full membership IDs publicly unless necessary
2. **Verification Access**: Consider limiting what information is shown in verification
3. **User Data**: Custom fields may contain sensitive data - ensure GDPR compliance
4. **Admin Access**: Only give admin access to trusted users
5. **Backups**: Regularly backup your database to prevent data loss

## Performance Optimization

1. **Caching**: If using caching, exclude My Account and checkout pages
2. **Database**: Plugin uses efficient queries with proper indexing
3. **Large Member Base**: Plugin tested with 10,000+ members
4. **Field Count**: Limit to 20-30 fields for best performance

## Getting Help

If you need assistance:
1. Check this guide first
2. Review the README.md file
3. Check WordPress debug log for errors
4. Contact plugin support with:
   - WordPress version
   - WooCommerce version
   - PHP version
   - Description of issue
   - Steps to reproduce
