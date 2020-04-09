<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! class_exists( 'Prflxtrflds_Settings_Tabs' ) ) {
	class Prflxtrflds_Settings_Tabs extends Bws_Settings_Tabs {
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $prflxtrflds_options, $prflxtrflds_plugin_info;
			$tabs = array(
				'misc'					=> array( 'label' => __( 'Misc', 'profile-extra-fields' ) ),
				'custom_code'			=> array( 'label' => __( 'Custom Code', 'profile-extra-fields' ) ),
				'license'				=> array( 'label' => __( 'License Key', 'profile-extra-fields' ) ),
			);

			parent::__construct( array(
				'plugin_basename'			=> $plugin_basename,
				'plugins_info'				=> $prflxtrflds_plugin_info,
				'prefix'					=> 'prflxtrflds',
				'default_options'			=> prflxtrflds_get_options_default(),
				'options'					=> $prflxtrflds_options,
				'is_network_options'		=> is_network_admin(),
				'tabs'						=> $tabs,
				'wp_slug'					=> 'profile-extra-fields',
				'pro_page'					=> 'admin.php?page=profile-extra-fields-pro-settings.php',
				'bws_license_plugin'		=> 'profile-extra-fields-pro/profile-extra-fields-pro.php',
				'link_key'					=> 'c37eed44c2fe607f3400914345cbdc8a',
				'link_pn'					=> '300'
			) );
		}
		public function save_options() {}
	}
}