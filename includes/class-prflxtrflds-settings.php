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
				'link_key'					=> 'c37eed44c2fe607f3400914345cbdc8a',
				'link_pn'					=> '300',
				'doc_link' 					=> 'https://bestwebsoft.com/documentation/profile-extra-fields/profile-extra-fields-user-guide/'
			) );

            add_action( get_parent_class( $this ) . '_display_metabox', array( $this, 'display_metabox' ) );
            add_action( get_parent_class( $this ) . '_additional_misc_options_affected', array( $this, 'additional_misc_options_affected' ) );
        }

        public function display_metabox() { ?>
            <div class="postbox">
                <h3 class="hndle">
                    <?php _e( 'Profile Extra Fields Pro Shortcode', 'profile-extra-fields' ); ?>
                </h3>
                <div class="inside">
                    <?php _e( "Add user data for current user using the following shortcode:", 'profile-extra-fields' ) ?>
                    <?php bws_shortcode_output( "[prflxtrflds_user_data user_id=get_current_user]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add user data for specific users using the following shortcode (where * is user ids, separated by commas):", 'profile-extra-fields' ) ?>
                    <?php bws_shortcode_output( "[prflxtrflds_user_data user_id=*]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add user data for specific user roles using the following shortcode (where * is user roles, separated by commas):", 'profile-extra-fields' ) ?>
                    <?php bws_shortcode_output( "[prflxtrflds_user_data user_role=*]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add user data specifying the data position (columns or rows) using the following shortcode (where * is top, left or right):", 'profile-extra-fields' ) ?>
                    <?php bws_shortcode_output( "[prflxtrflds_user_data display=*]" ); ?>
                </div>
            </div>
        <?php }
        public function save_options() {}

        public function additional_misc_options_affected() {
			if ( ! $this->hide_pro_tabs ) { ?>
				</table>
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">
							<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'profile-extra-fields' ); ?>"></button>
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<tr valign="top">
						                <th scope="row"><?php _e( 'Gravity Forms', 'profile-extra-fields' ); ?></th>
						                <td>
						                    <label>
						                        <input type="checkbox" disabled="disabled" /> <span class="bws_info"><?php _e( 'Ability to add Profile Extra Fields to the Gravity Forms.', 'profile-extra-fields' ); ?></span>
						                    </label>
						                </td>
						            </tr>
							</table>
						</div>
						<?php $this->bws_pro_block_links(); ?>
					</div>
				<table class="form-table">
			<?php } 
		}
    }
}
