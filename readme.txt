=== Profile Extra Fields by BestWebSoft ===
Contributors: bestwebsoft
Donate link: http://bestwebsoft.com/donate/
Tags: add fields, add extra fields, add additional fields, add custom fields, adding fields plugin, profile extra fields, profile extra fields plugin, profile user data, profile information, extra user data, extra fields, additional fields
Requires at least: 3.8
Tested up to: 4.6.1
Stable tag: 1.0.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add extra fields to default WordPress user profile. The easiest way to create and manage additional custom values.

== Description ==

The plugin adds additional fields on the standard user's profile page. That can be checkboxes, radio buttons, text fields. The information entered by user can be viewed at the plugin settings page. Also, the information can be displayed using the shortcode. Plugin has flexible settings which allow to display information as you wish.

http://www.youtube.com/watch?v=O424Kpnffmo

= Features = 

* Add different fields on the user's profile page
* Make the fields required or not
* Change the fields order by using the drag-n-drop option
* Make the field available only for one specific user role
* Display information via shortcode

If you have a feature, suggestion or idea you'd like to see in the plugin, we'd love to hear about it! <a href="http://support.bestwebsoft.com/hc/en-us/requests/new" target="_blank">Suggest a Feature</a>

= Recommended Plugins = 

The author of the Profile Extra Fields also recommends the following plugins:

* <a href="http://wordpress.org/plugins/updater/">Updater</a> - This plugin updates WordPress core and the plugins to the recent versions. You can also use the auto mode or manual mode for updating and set email notifications.
There is also a premium version of the plugin <a href="http://bestwebsoft.com/products/wordpress/plugins/updater/">Updater Pro</a> with more useful features available. It can make backup of all your files and database before updating. Also it can forbid some plugins or WordPress Core update.

= Translation =

* German (de_DE) (thanks to <a href="mailto:matthias.siebler@gmail.com">Matthias Siebler</a>)
* Russian (ru_RU)
* Ukrainian (uk)

Some of these translations are not complete. We are constantly adding new features which should be translated. If you would like to create your own language pack or update the existing one, you can send <a href="http://codex.wordpress.org/Translating_WordPress" target="_blank">the text of PO and MO files</a> to <a href="http://support.bestwebsoft.com/hc/en-us/requests/new" target="_blank">BestWebSoft</a>, and we'll add it to the plugin. You can download the latest version of the program for working with PO and MO files <a href="http://www.poedit.net/download.php" target="_blank">Poedit</a>.

= Technical support =

Dear users, our plugins are available for free download. If you have any questions or recommendations regarding the functionality of our plugins (existing options, new options, current issues), please feel free to contact us. Please note that we accept requests in English only. All messages in other languages won't be accepted.

If you notice any bugs in the plugin's work, you can notify us about them and we'll investigate and fix the issue then. Your request should contain website URL, issues description and WordPress admin panel credentials.
Moreover, we can customize the plugin according to your requirements. It's a paid service (as a rule it costs $40, but the price can vary depending on the amount of the necessary changes and their complexity). Please note that we could also include this or that feature (developed for you) in the next release and share with the other users then.
We can fix some things for free for the users who provide a translation of our plugin into their native language (this should be a new translation of a certain plugin, you can check available translations on the official plugin page).

== Installation == 

1. Upload the `profile-extra-fields` folder to `/wp-content/plugins/` directory.
2. Activate the plugin using the 'Plugins' menu in your WordPress admin panel.
3. You can adjust the necessary settings using your WordPress admin panel in "BWS Panel" > "Profile Extra Fields".
4. Create a page or a post and insert the shortcode [prflxtrflds_user_data] into the text.

<a href="https://docs.google.com/document/d/1YSxf-rycHXQ_Tl38dUguuWav03PI2z0ZdKMDbRA1UV0/edit" target="_blank">View a Step-by-step Instruction on Error Log Viewer Installation</a>.

== Frequently Asked Questions ==

= How to add an additional field for the user? =

Please go to the plugin settings page ( "BWS Panel" > "Profile extra fields" ) and press the button 'Add a new field'

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

Please make sure that the problem hasn't been discussed on our forum yet (<a href="http://support.bestwebsoft.com" target="_blank">http://support.bestwebsoft.com</a>). If not, please provide the following data along with your problem's description:

1. the link to the page, on which the problem occurs
2. the plugin's name and version. If you are using a pro version - your order number.
3. the version of your WordPress installation
4. copy and paste your system status report into the message. Please read more here: <a href="https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/edit" target="_blank">Instuction on System Status</a>

== Screenshots ==

1. Viewing user data via shortcode.
2. Field Edit Page.
3. Format setting for the date, time or datetime type fields.
4. Available values setting for the Radiobutton, Drop down list or Checkbox type fields.
5. Pattern setting for the phone type field.
6. Plugin Homepage with the list of created Extra Fields.
7. Viewing user information on the plugin page.
8. Additional fields on the user's profile page.
9. Datetimepicker displaying for the date, time or datetime type fields.
10. Shortcode settings page.
11. Adding Profile Extra Fields shortcode to your page or post.

== Changelog ==

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
