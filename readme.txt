=== Profile Extra Fields by BestWebSoft ===
Contributors: bestwebsoft
Donate link: https://bestwebsoft.com/donate/
Tags: add fields, add extra fields, add additional fields, add custom fields, adding fields plugin, profile extra fields, profile extra fields plugin, profile user data, profile information, extra user data, extra fields, additional fields
Requires at least: 3.9
Tested up to: 4.9.6
Stable tag: 1.1.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add extra fields to default WordPress user profile. The easiest way to create and manage additional custom values.

== Description ==

Simple plugin which helps to add additional fields to the WordPress website user profile page. Checkboxes, radio buttons, text, date, time, and phone number fields.

Easily add and display extra information about WordPress users!

https://www.youtube.com/watch?v=O424Kpnffmo

= Features =

* Add unlimited number of extra fields
* Use shortcode to display user data on your page or post:
	* All users data
	* Logged in user data
	* Certain user role data
	* Certain user data
	* Current user data
* Display profile extra fields in user registration form
* Export all user data to a CSV file [NEW]
* User data shortcode settings:
	* Choose user data rotation on page or post
		* Rows
		* Columns
	* Sort user data by user name in the table:
		* ASC (ascending order from lowest to highest values)
		* DESC (descending order from highest to lowest values)
	* Show empty fields if user missed them
	* Show user ID in the table
	* Display the shortcode with the field value
	* Customize validation message for:
		* Empty fields
		* Unavailable fields
	* Enable or disable debug mode
* Choose extra field type:
	* Text
	* Checkbox
	* Radiobutton
	* Dropdown list
	* Date
	* Time
	* Date and time
	* Number
	* Phone number
* Customize extra fields:
	* Name
	* Description
* Set the max length for text or number in the appropriate fields
* Make any field required
* Set the fields order
* Choose date and time formats for the corresponding field types
* Set the phone number format for the corresponding field type
* Drag and drop fields to change their order in the list
* Make extra fields available for certain user roles
* Add custom code via plugin settings page
* Compatible with latest WordPress version
* Incredibly simple settings for fast setup without modifying code
* Detailed step-by-step documentation and videos
* Multilingual and RTL ready

If you have a feature suggestion or idea you'd like to see in the plugin, we'd love to hear about it! [Suggest a Feature](https://support.bestwebsoft.com/hc/en-us/requests/new)

= Documentation & Videos =

* [[Doc] Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ)

= Help & Support =

Visit our Help Center if you have any questions, our friendly Support Team is happy to help - <https://support.bestwebsoft.com/>

= Translation =

* German (de_DE) (thanks to [Matthias Siebler](mailto:matthias.siebler@gmail.com))
* Russian (ru_RU)
* Ukrainian (uk)

Some of these translations are not complete. We are constantly adding new features which should be translated. If you would like to create your own language pack or update the existing one, you can send [the text of PO and MO files](https://codex.wordpress.org/Translating_WordPress) to [BestWebSoft](https://support.bestwebsoft.com/hc/en-us/requests/new) and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO [files Poedit](https://www.poedit.net/download.php).

= Recommended Plugins =

* [Updater](https://bestwebsoft.com/products/wordpress/plugins/updater/?k=c70444d5332ad964766fa7f80de398dd) - Automatically check and update WordPress website core with all installed plugins and themes to the latest versions.
* [User Role](https://bestwebsoft.com/products/wordpress/plugins/user-role/?k=350d112a7272eeed8aac838bbe2dc8c8) - Powerful user role management plugin for WordPress website. Create, edit, copy, and delete user roles.

== Installation ==

1. Upload the `profile-extra-fields` folder to `/wp-content/plugins/` directory.
2. Activate the plugin using the 'Plugins' menu in your WordPress admin panel.
3. You can adjust the necessary settings using your WordPress admin panel in "BWS Panel" > "Profile Extra Fields".
4. Create a page or a post and insert the shortcode [prflxtrflds_user_data] into the text.

[View a Step-by-step Instruction on Profile Extra Fields Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/)

== Frequently Asked Questions ==

= How to add an additional field for the user? =

Please go to the plugin settings page ( "BWS Panel" > "Profile Extra Fields" ) and press the button 'Add a new field'.

= Can I change the order of displaying fields? =

Yes. If you have javascript enabled, you can simply drag the field on the settings page ( "BWS Panel" > "Profile Extra Fields" ) - Extra Fields tab, as you need.
If you select some of the roles in the filter settings, the order will apply to a particular user role.
You can also customize the order in the appropriate option while editing field.

= How to view the data filled by users? =

You can view the data filled by users on the plugin settings page ( "BWS Panel" > "Profile Extra Fields" ), in the 'User data' tab

= How to display the data which users submitted on my site? =

To display the user data on the site, please use the shortcode [prflxtrflds_user_data].
Also, it is possible to display data only for specific users. To do this, please enter the relevant user id to the shortcode. For example: [prflxtrflds_user_data user_id=3,1].
You can specify a user role, separated by commas without spaces. Example: [prflxtrflds_user_data user_role=administrator,contributor]
You can specify a header position manually (top, left or right). Example: [prflxtrflds_user_data display=top]

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<https://support.bestwebsoft.com>). If no, please provide the following data along with your problem's description:

1. The link to the page where the problem occurs.
2. The name of the plugin and its version. If you are using a pro version - your order number.
3. The version of your WordPress installation.
4. Copy and paste into the message your system status report. Please read more here: [Instruction on System Status](https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/).

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

== Changelog ==

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
