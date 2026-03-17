# Installation & Activation Guide

## Requirements

Before installing, ensure your server meets these requirements:

- **WordPress**: 5.8 or higher
- **PHP**: 7.4 or higher
- **WooCommerce**: 5.0 or higher (required)
- **MySQL**: 5.6 or higher
- **Memory**: 128MB minimum (256MB recommended)

### Optional Plugins
- WooCommerce Memberships (for membership plans)
- WooCommerce Subscriptions (for recurring memberships)

## Installation Methods

### Method 1: Manual Installation (Current)

Since you already have the plugin files in your plugins directory:

1. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Advanced Membership System"
   - Click "Activate"

2. **Verify Activation**
   - You should see a new "Membership" menu item in the admin sidebar
   - No errors should appear

### Method 2: Upload ZIP File

1. **Create ZIP File**
   - Compress the `advanced-membership-system` folder into a ZIP file

2. **Upload**
   - Go to WordPress Admin → Plugins → Add New
   - Click "Upload Plugin"
   - Choose your ZIP file
   - Click "Install Now"
   - Click "Activate Plugin"

### Method 3: FTP Upload

1. **Upload Files**
   - Connect to your server via FTP
   - Navigate to `/wp-content/plugins/`
   - Upload the `advanced-membership-system` folder

2. **Set Permissions**
   ```
   Folders: 755
   Files: 644
   ```

3. **Activate**
   - Go to WordPress Admin → Plugins
   - Find and activate the plugin

## Initial Configuration

### Step 1: Basic Settings

1. Go to **Membership → Settings**
2. Configure **General** tab:
   - Select membership products (or create them first)
   - Enable auto-complete if needed

3. Configure **Membership ID** tab:
   - Set prefix (e.g., `PAU`, `MEM`, `MEMBER`)
   - Set padding (recommended: 5)
   - Note the current counter value

4. Configure **Email** tab:
   - Enable email notifications
   - Customize subject and message
   - Test email delivery

5. Configure **Display** tab:
   - Enable "Show in Checkout"
   - Enable "Show in My Account"
   - Customize tab title and slug

6. Click **Save Changes**

### Step 2: Create Custom Fields

1. Go to **Membership → Custom Fields**
2. Add your first field:
   - Field ID: `phone_number` (lowercase, underscores only)
   - Label: `Nombor Telefon`
   - Type: `tel`
   - Group: `Contact Info`
3. Click **Add Field**
4. Repeat for all needed fields
5. Drag to reorder
6. Enable/disable as needed
7. Click **Save All Changes**

### Step 3: Configure Products

1. Go to **Membership → Products**
2. Check all products that should trigger membership ID generation
3. Click **Save Product Selection**

**Tip**: For digital memberships, also set products as "Virtual" in WooCommerce product settings for automatic order completion.

### Step 4: Flush Rewrite Rules

Important for My Account tab to work:

1. Go to **Settings → Permalinks**
2. Don't change anything
3. Just click **Save Changes**
4. This flushes rewrite rules

### Step 5: Test the System

1. **Test Checkout**
   - Log out
   - Add a membership product to cart
   - Go to checkout
   - Verify custom fields appear
   - Complete test order

2. **Verify Membership ID**
   - Check order notes for membership ID
   - Check email for notification
   - Check user profile in admin

3. **Test My Account**
   - Log in as the test user
   - Go to My Account
   - Verify new tab appears
   - Test editing fields

4. **Test Verification**
   - Create a page with `[ams_verification_form]`
   - Test verifying the membership ID

## Post-Installation Checklist

- [ ] Plugin activated successfully
- [ ] Membership menu appears in admin
- [ ] Settings configured
- [ ] Custom fields created
- [ ] Membership products selected
- [ ] Permalinks flushed
- [ ] Test order completed
- [ ] Membership ID generated
- [ ] Email received
- [ ] My Account tab visible
- [ ] Verification form working

## Common Installation Issues

### Issue: Plugin won't activate

**Possible Causes:**
- PHP version too old
- WooCommerce not installed/activated
- File permissions incorrect
- Syntax error in PHP

**Solutions:**
1. Check PHP version: `<?php phpinfo(); ?>`
2. Install/activate WooCommerce first
3. Set correct file permissions (644 for files, 755 for folders)
4. Check WordPress debug log

### Issue: "Headers already sent" error

**Solution:**
- Check for whitespace before `<?php` in plugin files
- Check for UTF-8 BOM encoding
- Disable other plugins to find conflict

### Issue: Membership menu not appearing

**Solution:**
- Clear browser cache
- Check user role has `manage_options` capability
- Deactivate and reactivate plugin
- Check for JavaScript errors

### Issue: Database errors on activation

**Solution:**
- Check database user has CREATE and ALTER permissions
- Verify database connection in wp-config.php
- Check database server is running

## Updating the Plugin

### Manual Update

1. **Backup First**
   - Backup database
   - Backup plugin files
   - Export settings if possible

2. **Deactivate** (don't delete)
   - Go to Plugins
   - Deactivate Advanced Membership System

3. **Replace Files**
   - Delete old plugin folder
   - Upload new plugin folder

4. **Reactivate**
   - Activate the plugin
   - Check settings are preserved

### Automatic Update (Future)

When available in WordPress repository:
- Go to Dashboard → Updates
- Check for plugin updates
- Click "Update Now"

## Uninstallation

### Temporary Deactivation

To disable without losing data:
1. Go to Plugins
2. Click "Deactivate" under Advanced Membership System
3. Data remains in database

### Complete Removal

To remove all data:

1. **Export Data First** (if needed)
   - Go to Tools → Export
   - Select "Users" to export member data

2. **Deactivate Plugin**
   - Go to Plugins
   - Deactivate Advanced Membership System

3. **Delete Plugin**
   - Click "Delete" under the plugin
   - Confirm deletion

4. **Clean Database** (optional)
   ```sql
   DELETE FROM wp_options WHERE option_name LIKE 'ams_%';
   DELETE FROM wp_usermeta WHERE meta_key LIKE 'diploma_%';
   DELETE FROM wp_usermeta WHERE meta_key LIKE 'degree_%';
   DELETE FROM wp_usermeta WHERE meta_key LIKE 'master_%';
   DELETE FROM wp_usermeta WHERE meta_key LIKE 'phd_%';
   DELETE FROM wp_usermeta WHERE meta_key = '_membership_id';
   ```

## Migration from Code Snippets

If you're currently using code snippets:

### Step 1: Export Existing Data

Before switching, note down:
- Current membership ID counter
- List of all custom fields
- Product IDs being used

### Step 2: Install Plugin

Follow installation steps above

### Step 3: Configure to Match

1. Set membership ID prefix and counter to match current system
2. Create same custom fields
3. Select same products

### Step 4: Disable Old Snippets

1. Go to Code Snippets plugin
2. Deactivate all membership-related snippets
3. Don't delete yet (keep as backup)

### Step 5: Test Thoroughly

1. Create test order
2. Verify membership ID continues from correct number
3. Check all fields work
4. Test My Account tab
5. Test verification

### Step 6: Clean Up

Once confirmed working:
1. Delete old code snippets
2. Remove any custom code from theme functions.php
3. Clear all caches

## Server Configuration

### Recommended PHP Settings

```ini
memory_limit = 256M
max_execution_time = 300
post_max_size = 64M
upload_max_filesize = 64M
```

### WordPress Debug Mode

For troubleshooting, enable debug mode in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check logs at: `/wp-content/debug.log`

## Security Recommendations

1. **File Permissions**
   - Folders: 755
   - Files: 644
   - wp-config.php: 600

2. **Database**
   - Use strong database password
   - Limit database user permissions
   - Regular backups

3. **Updates**
   - Keep WordPress updated
   - Keep WooCommerce updated
   - Keep PHP updated

4. **Backups**
   - Daily database backups
   - Weekly file backups
   - Test restore process

## Getting Support

If you encounter issues:

1. **Check Documentation**
   - README.md
   - USAGE-GUIDE.md
   - This file

2. **Enable Debug Mode**
   - Check debug.log for errors
   - Note exact error messages

3. **Gather Information**
   - WordPress version
   - WooCommerce version
   - PHP version
   - Active plugins list
   - Active theme

4. **Contact Support**
   - Provide all gathered information
   - Describe steps to reproduce
   - Include screenshots if relevant

## Next Steps

After successful installation:

1. Read the [Usage Guide](USAGE-GUIDE.md)
2. Review available [Shortcodes](README.md#shortcodes)
3. Set up verification page
4. Configure email templates
5. Train your team on admin features
6. Create member onboarding documentation
