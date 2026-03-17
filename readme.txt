=== Advanced Membership System ===
Contributors: yourname
Tags: membership, woocommerce, registration, user management, verification
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Complete membership registration system with custom fields, auto ID generation, verification, and WooCommerce integration.

== Description ==

Advanced Membership System is a comprehensive WordPress plugin that transforms your WooCommerce store into a powerful membership registration platform. Perfect for organizations, associations, and membership-based businesses.

**Key Features:**

* **Flexible Custom Fields** - Create unlimited custom fields with an easy-to-use admin interface
* **Auto Membership ID Generation** - Automatically generate unique membership IDs (e.g., PAU00101, PAU00102)
* **WooCommerce Integration** - Seamlessly integrates with WooCommerce checkout
* **Member Verification System** - Allow anyone to verify membership status using shortcodes
* **My Account Integration** - Members can view and edit their information
* **Admin Management** - Full control over member data from WordPress admin
* **Email Notifications** - Automatic email with membership ID upon registration
* **WooCommerce Memberships & Subscriptions Compatible** - Works with popular membership plugins

**Perfect For:**

* Professional associations
* Educational institutions
* Alumni networks
* Membership organizations
* Subscription-based services

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/advanced-membership-system/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Membership > Settings to configure
4. Add your custom fields in Membership > Custom Fields
5. Select membership products in Membership > Products

== Frequently Asked Questions ==

= How do I add custom fields? =

Go to Membership > Custom Fields in your WordPress admin. You can add, edit, reorder, and delete fields with a simple interface.

= Can I change the membership ID format? =

Yes! Go to Membership > Settings > Membership ID tab. You can customize the prefix (e.g., PAU, MEM), number padding, and reset the counter.

= How do I display membership information on my site? =

Use these shortcodes:
* `[membership_id]` - Display current user's membership ID
* `[membership_count]` - Show total member count
* `[membership_info]` - Display full membership information
* `[ams_verification_form]` - Add a verification form

= Does it work with WooCommerce Memberships? =

Yes! The plugin automatically integrates with WooCommerce Memberships and Subscriptions if installed.

= Can I collect fields during checkout? =

Yes! Enable "Show in Checkout" in settings, and your custom fields will appear in the WooCommerce checkout process.

== Screenshots ==

1. Settings page with membership ID configuration
2. Custom fields management interface
3. Membership verification form
4. My Account tab with member information
5. Admin user profile with membership data

== Changelog ==

= 1.0.0 =
* Initial release
* Custom fields management
* Auto membership ID generation
* WooCommerce integration
* Verification system
* My Account integration
* Admin management tools
* Email notifications
* WooCommerce Memberships & Subscriptions integration

== Upgrade Notice ==

= 1.0.0 =
Initial release of Advanced Membership System.

== Shortcodes ==

**[membership_id]**
Display the current user's membership ID.
Attributes:
* user_id - Specific user ID (default: current user)
* default - Text to show if no ID (default: "Belum didaftarkan")

**[membership_count]**
Show total number of members.
Attributes:
* offset - Starting number offset (default: 100)

**[membership_info]**
Display full membership information.
Attributes:
* user_id - Specific user ID (default: current user)
* fields - Comma-separated field IDs to display
* show_id - Show membership ID (yes/no)
* show_name - Show member name (yes/no)
* show_email - Show email (yes/no)
* template - Display format (list/table)

**[membership_field]**
Display a specific field value.
Attributes:
* field - Field ID to display (required)
* user_id - Specific user ID (default: current user)
* default - Default value if empty

**[membership_status]**
Show WooCommerce Memberships status.
Attributes:
* user_id - Specific user ID (default: current user)
* show_expiry - Show expiration date (yes/no)

**[ams_verification_form]**
Display membership verification form.
Attributes:
* title - Form title
* placeholder - Input placeholder text
* button_text - Submit button text
* show_all_fields - Show all fields (yes/no)

== Support ==

For support, please visit the plugin support forum or contact us directly.
