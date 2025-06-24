=== Profile Extra Fields by BestWebSoft ===
Contributors: bestwebsoft
Donate link: https://bestwebsoft.com/donate/
Tags: add fields WordPress, add extra fields, custom fields, woocommerce extra fields, woocommerce additional fields
Requires at least: 5.6
Tested up to: 6.8
Stable tag: 1.3.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add custom fields to WordPress user profiles and WooCommerce forms. Easily collect and display extra user information using a simple interface.

== Description ==

A user-friendly plugin for adding and managing custom fields on WordPress user profiles and WooCommerce registration and checkout pages. Create custom fields such as checkboxes, radio buttons, date pickers, phone numbers, and more — with no coding required.

Perfect for membership sites, user directories, WooCommerce stores, and any website that needs extra user information.

Easily display additional user data with shortcodes anywhere on your site.

[View Demo](https://bestwebsoft.com/demo-profile-extra-fields-plugin-for-wordpress/?ref=readme)  
[Watch Video](https://www.youtube.com/watch?v=O424Kpnffmo)

= Free Features =

* Add unlimited custom fields to WordPress user profiles
* Display extra user data with a shortcode:
	* All users
	* Logged-in user
	* Specific user role
	* Specific user ID
	* Current user
* Display selected fields via shortcode
* Show fields in the registration form
* Export all user data to a CSV file
* Customize shortcode output:
	* Table layout: rows or columns
	* Sort by username: ASC / DESC
	* Show empty fields
	* Show user ID
	* Display field shortcodes with values
* Customize validation messages:
	* For required fields
	* For unavailable fields
* Enable debug mode for troubleshooting
* Choose from various field types:
	* Text, Textarea, Checkbox, Radiobutton
	* Dropdown, Date, Time, Date & Time
	* Number, Phone, URL
* Field customization:
	* Field name and description
	* Max length for text/number
	* Required symbol and setting
	* Set field order (drag & drop)
	* Choose date/time/phone format
	* Limit field visibility to specific roles
* Front-end form to edit user data
* Compatible with the latest WordPress version
* Lightweight, fast, and easy to set up
* Translation-ready and RTL support
* Compatible with [Car Rental V2](https://bestwebsoft.com/products/wordpress/plugins/car-rental-v2/?k=a8f05dd9a324c003f22923d43eb75eea)

> **Pro Features**
>
> Includes all features from the free version, plus:
>
> * Import user data from a CSV file
> * New field type: Attachment
> * WooCommerce compatibility:
>   * Registration form
>   * Checkout billing fields
>   * Order confirmation emails
> * Integration with Gravity Forms
> * Integration with [Subscriber plugin](https://bestwebsoft.com/products/wordpress/plugins/subscriber/?k=fb814b406c52fdf3d8c48b9a342aaa69)
> * Add custom code from plugin settings
> * Bulk import values for:
>   * Checkbox
>   * Dropdown
>   * Radiobutton
> * Priority support – get answers within 1 business day ([Support Policy](https://bestwebsoft.com/support-policy/))
>
> [Upgrade to Pro Now](https://bestwebsoft.com/products/wordpress/plugins/profile-extra-fields/?k=b21e006d6bce19b9c1ac7667c721fe1d)

Have a feature idea? [Suggest a Feature](https://support.bestwebsoft.com/hc/en-us/requests/new)

= Documentation & Videos =

* [[Doc] User Guide](https://bestwebsoft.com/documentation/profile-extra-fields/profile-extra-fields-user-guide/)
* [[Doc] Installation](https://bestwebsoft.com/documentation/how-to-install-a-wordpress-product/how-to-install-a-wordpress-plugin/)
* [[Doc] Purchase Guide](https://bestwebsoft.com/documentation/how-to-purchase-a-wordpress-plugin/how-to-purchase-wordpress-plugin-from-bestwebsoft/)

= Help & Support =

Need help? Visit our Help Center — our Support Team is ready to assist you.  
<https://support.bestwebsoft.com/>

= Affiliate Program =

Earn 20% commission by promoting BestWebSoft premium WordPress plugins and themes.  
[Join our affiliate program](https://bestwebsoft.com/affiliate/?utm_source=plugin&utm_medium=readme&utm_campaign=affiliate_program)

= Translation =

Available languages:
* German (de_DE) — Thanks to [Matthias Siebler](mailto:matthias.siebler@gmail.com)
* Russian (ru_RU)
* Ukrainian (uk)
* French (fr_FR)
* Italian (it_IT)
* Japanese (ja)
* Portuguese (Brazil) (pt_BR)
* Spanish (es_ES)

Some translations may be incomplete. Help us improve! Send your updated `.po` and `.mo` files via [support](https://support.bestwebsoft.com/hc/en-us/requests/new).  
Download [Poedit](https://www.poedit.net/download.php) to edit translation files.

= Recommended Plugins =

* [Updater](https://bestwebsoft.com/products/wordpress/plugins/updater/?k=c70444d5332ad964766fa7f80de398dd) – Keep WordPress, plugins, and themes up to date.
* [User Role](https://bestwebsoft.com/products/wordpress/plugins/user-role/?k=350d112a7272eeed8aac838bbe2dc8c8) – Manage user roles with full control.
* [Subscriber](https://bestwebsoft.com/products/wordpress/plugins/subscriber/?k=fb814b406c52fdf3d8c48b9a342aaa69) – Add a newsletter sign-up form to your website.

== Installation ==

1. Upload the `profile-extra-fields` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin via the “Plugins” menu in WordPress admin.
3. Go to **Profile Extra Fields** in your WordPress dashboard to configure the plugin.
4. Use `[prflxtrflds_user_data]` shortcode to display user data on pages or posts.

[View Step-by-Step Installation Guide](https://bestwebsoft.com/documentation/how-to-install-a-wordpress-product/how-to-install-a-wordpress-plugin/)

== Frequently Asked Questions ==

= How to add an extra field to the user profile? =

Go to “Profile Extra Fields” settings in your dashboard and click "Add a new field".

= Can I reorder the fields? =

Yes. With JavaScript enabled, simply drag and drop fields in the "Extra Fields" tab.  
You can also define different orders for different user roles.

= How to view submitted user data? =

Navigate to the **User Data** tab under the plugin settings to view submitted information.

= How to display submitted user data on the site? =

Use the shortcode `[prflxtrflds_user_data]`.  
You can also filter by:
- `user_id`: `[prflxtrflds_user_data user_id=3,1]`
- `user_role`: `[prflxtrflds_user_data user_role=administrator,subscriber]`
- `display`: `[prflxtrflds_user_data display=top]`  
To show the current user's data: `[prflxtrflds_user_data user_id=get_current_user]`

= I'm having issues with the plugin. What should I include in a support request? =

Please provide:
1. The URL of the page with the issue
2. Plugin name and version (and order number if Pro)
3. Your WordPress version
4. System status info (see [how to get it here](https://bestwebsoft.com/documentation/admin-panel-issues/system-status/))


== Screenshots ==

1. Viewing user data via shortcode.
2. Field Edit Page.
3. Format setting for the date, time or datetime type fields.
4. Available values setting for the Radio button, Drop down list or Checkbox type fields.
5. Pattern setting for the Phone number type field.
6. Plugin Homepage with the list of created Extra Fields.
7. Viewing user information on the plugin page.
8. Additional fields on the user's profile page.
9. Datetimepicker displaying for the date, time or datetime type fields.
10. Shortcode settings page.
11. Adding Profile Extra Fields shortcode to your page or post.
12. Optional fields.
13. Car Rental fields.
14. Car Rental plugin with additional fields.
15. Additional Car Rental fields on the user profile page.

== Changelog ==

= V1.3.2 - 24.06.2025 =
* Update : Compatibility with BWS Login Form has been added.

= V1.3.0 - 15.07.2024 =
* Bugfix : Non-editable fields saving issue has been fixed.

= V1.2.9 - 03.07.2024 =
* Update : BWS Panel section minor changes.
* Update : All functionality was updated for WordPress 6.5.4.
* Bugfix : Fields were fixed for WooCommerce and Gravity forms.

= V1.2.8 - 18.12.2023 =
* NEW : Added a new field type: Country.
* Pro : Attachment field dispay has been fixed.
* Bugfix : Fields were fixed when creating the user.
* Bugfix : Shortcode settings has been fixed.
* Bugfix : Unauthorized access vulnerability has been fixed.
* Update : All functionality was updated for WordPress 6.3.

= V1.2.7 - 02.02.2023 =
* NEW: My Account page for frontend
* NEW: WooCommerce Order custom fields to email
* Update : BWS Panel section minor changes.
* Pro : Gravity Forms compatibility bugfix.
* Bugfix : "Time" field custom format fix.
* Bugfix : User registration form bug for admin has been fixed.

= V1.2.6 - 02.06.2022 =
* Update : BWS Panel section minor changes.
* Bugfix : Bug with URL link in front end has been fixed.
* Bugfix : Bug with Textarea has been fixed.
* Bugfix : Bug with Non editable fields has been fixed.
* Bugfix : Required field bug has been fixed.
* Bugfix : Rows and Columns export bug has been fixed.
* Pro : Gravity Forms compatibility bugfix.
* Bugfix : "Time" field custom format fix.
* Bugfix : User registration form bug has been fixed.
* Bugfix : CSV export has been fixed.
* Bugfix : Roles filter option has been fixed.
* Pro : WooCommerce registration form fields has been fixed.

= V1.2.5 - 30.12.2021 =
* Bugfix: The issue with show fields on the user edit page has been fixed.
* Bugfix: The issue with edit fields on the user edit page for administrator has been fixed.
* Update : BWS Panel section was updated.

= V1.2.4 - 24.11.2021 =
* Bugfix: The issue with get fields has been fixed.
* Update: Escape the user input in front-end has been updated
* Update : BWS Panel section was updated.
* Update : All functionality was updated for WordPress 5.8.2.
* Pro : Ability to import all user data to a CSV file has been added.

= V1.2.3 - 25.08.2021 =
* Bugfix : The issue with adding values for: Checkbox, Drop down list, Radio button has been fixed.
* Bugfix : The issue with screen options has been fixed.
* Pro : Ability to import available values for: Checkbox, Drop down list, Radio button in csv format has been added.
* Pro : Compatibility with Subscriber by BestWebSoft plugin has been added.

= V1.2.3 - 25.08.2021 =
* Bugfix : The issue with adding values for: Checkbox, Drop down list, Radio button has been fixed.
* Bugfix : The issue with screen options has been fixed.
* Pro : Ability to import available values for: Checkbox, Drop down list, Radio button in csv format has been added.
* Pro : Compatibility with Subscriber by BestWebSoft plugin has been added.

= V1.2.2 - 22.07.2021 =
* Bugfix : The issue with saving registration form field values has been fixed.
* Update : BWS Panel section was updated.
* Update : All functionality was updated for WordPress 5.8.
* Pro : Ability to import available values for: Checkbox, Drop down list, Radio button has been added.
* Pro : Attachment type field has been added.

= V1.2.1 - 19.11.2020 =
* NEW : Extra Fields have been added to the User Registration Email.
* NEW : Meta box with shortcodes has been added.
* Update : BWS Panel section was updated.
* Pro : Fixed a bug with required fields in Woocommerce.
* Pro : Fixed a bug with displaying visible fields when working with Gravity Forms.

= V1.2.0 - 21.09.2020 =
* NEW : 'URL link' field type was added.
* NEW : Ability to display profile extra fields on the user registration page.
* Update : BWS Panel section was updated.
* Update : The plugin settings page was changed.

= V1.1.9 - 09.04.2020 =
* Update : The plugin settings page was changed.
* Update : The compatibility with Car Rental V2 plugin has been improved.
* Update : BWS menu has been updated.
* Pro : Compatibility with Gravity Forms plugin has been added.

= V1.1.8 - 04.09.2019 =
* Update: The deactivation feedback has been changed. Misleading buttons have been removed.

= V1.1.7 - 06.08.2019 =
* NEW : Compatibility with Car Rental V2 plugin has been added.

= V1.1.6 - 22.04.2019 =
* Bugfix : The bug with the incorrect output of the field types has been fixed.
* Bugfix : The bug with the displaying of the visible fields has been fixed.

= V1.1.5 - 26.03.2019 =
* New : Textarea field type has been added.
* Update : All functionality was updated for WordPress 5.1.1

= V1.1.4 - 28.02.2019 =
* Update : All functionality was updated for WordPress 5.1.

= V1.1.3 - 21.02.2019 =
* PRO: Ability to display certain fields in Woocommerce has been added.

= V1.1.2 - 18.10.2018 =
* NEW : Ability to display Username or Public name has been added.
* NEW : Ability to display certain fields via the shortcode has been added.

= V1.1.1 - 30.05.2018 =
* NEW : Ability to export all user data to a CSV file has been added.

= V1.1.0 - 28.02.2018 =
* NEW : Ability to display the shortcode with the field value has been added.
* NEW : Ability to enable or disable debug mode has been added.

= V1.0.9 - 08.02.2018 =
* NEW : Display profile extra fields in user registration form.

= V1.0.8 - 25.05.2017 =
* NEW : The ability to set readonly parameter to the field or make it invisible has been added.

= V1.0.7 - 16.03.2017 =
* Update : BWS plugins section is updated.

= V1.0.6 - 04.10.2016 =
* NEW : Ability to specify field max length (for text field type) or max number (for number type).

= V1.0.5 - 29.08.2016 =
* NEW : Ability to display profile extra fields current logged in user by the shortcode.

= V1.0.4 - 20.07.2016 =
* NEW : The mask for the phone number field has been added on the profile page.
* Update : Select for role selection is made multiple on User data page (for WP since v. 4.4).

= V1.0.3 - 18.04.2016 =
* NEW : Ability to add custom styles.

= V1.0.2 - 09.12.2015 =
* Bugfix : The bug with sorting on mobile devices was fixed.
* Bugfix : The bug with plugin menu duplicating was fixed.

= V1.0.1 - 03.11.2015 =
* NEW : German language file is added to the plugin.
* NEW : We added new field types ( Date, Time, Datetime, Phone number, Number ).
* NEW : We added ability to restore settings to defaults.
* Bugfix : A bug with the sorting was fixed.

= V1.0.0 - 18.08.2015 =
* NEW : Screenshots are added.

== Upgrade Notice ==

= V1.3.2 =
* New features added.

= V1.3.0 =
* Bugs fixed.

= V1.2.9 =
* The compatibility with new WordPress version updated.
* Plugin optimization completed.
* Bugs fixed.

= V1.2.8 =
* Bugs fixed.

= V1.2.7 =
* New features added.
* Usability improved.
* Bugs fixed.

= V1.2.6 =
* Bugs fixed.

= V1.2.5 =
* Bugs fixed.
* Plugin optimization completed.

= V1.2.4 =
* New features added.
* The compatibility with new WordPress version updated.
* Usability improved.
* Bugs fixed.

= V1.2.3 =
* Bugs fixed.
* New features added.

= V1.2.2 =
* New features added.
* The compatibility with new WordPress version updated.
* Usability improved.
* Bugs fixed.

= V1.2.1 =
* Bugs fixed.

= V1.2.0 =
* Functionality has been expanded.

= V1.1.9 =
* Usability improved.

= V1.1.8 =
* Usability improved.

= V1.1.7 =
* New features added.

= V1.1.6 =
* Bugs fixed.

= V1.1.5 =
* New features added.
* The compatibility with new WordPress version updated.

= V1.1.4 =
* The compatibility with new WordPress version updated.

= V1.1.3 =
* New features added.

= V1.1.2 =
* New features added.

= V1.1.1 =
* New features added.

= V1.1.0 =
* New feature added.

= V1.0.9 =
* New feature added.

= V1.0.8 =
* New feature added.

= V1.0.7 =
* Functionality improved.

= V1.0.6 =
* Functionality expanded.

= V1.0.5 =
* Functionality expanded.

= V1.0.4 =
Functionality has been expanded.

= V1.0.3 =
Ability to add custom styles.

= V1.0.2 =
The bug with sorting on mobile devices was fixed. The bug with plugin menu duplicating was fixed.

= V1.0.1 =
German language file is added to the plugin. We added ability to restore settings to defaults. A bug with the sorting was fixed.

= V1.0.0 =
Screenshots are added
