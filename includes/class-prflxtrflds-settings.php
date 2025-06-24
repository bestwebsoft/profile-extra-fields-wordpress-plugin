<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Prflxtrflds_Settings_Tabs' ) ) {
	class Prflxtrflds_Settings_Tabs extends Bws_Settings_Tabs {
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename Plugin basename.
		 */
		public function __construct( $plugin_basename ) {
			global $prflxtrflds_options, $prflxtrflds_plugin_info;
			$tabs = array(
				'settings'    => array( 'label' => __( 'Settings', 'profile-extra-fields' ) ),
				'misc'        => array( 'label' => __( 'Misc', 'profile-extra-fields' ) ),
				'custom_code' => array( 'label' => __( 'Custom Code', 'profile-extra-fields' ) ),
				'license'     => array( 'label' => __( 'License Key', 'profile-extra-fields' ) ),
			);

			parent::__construct(
				array(
					'plugin_basename'    => $plugin_basename,
					'plugins_info'       => $prflxtrflds_plugin_info,
					'prefix'             => 'prflxtrflds',
					'default_options'    => prflxtrflds_get_options_default(),
					'options'            => $prflxtrflds_options,
					'is_network_options' => is_network_admin(),
					'tabs'               => $tabs,
					'wp_slug'            => 'profile-extra-fields',
					'link_key'           => 'c37eed44c2fe607f3400914345cbdc8a',
					'link_pn'            => '300',
					'doc_link'           => 'https://bestwebsoft.com/documentation/profile-extra-fields/profile-extra-fields-user-guide/',
				)
			);

			add_action( get_parent_class( $this ) . '_display_metabox', array( $this, 'display_metabox' ) );
		}

		/**
		 * Save options. Empty function.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::save_options() for more information on default arguments.
		 */
		public function save_options() {
			$message = '';
			$notice  = '';
			$error   = '';
			if ( isset( $_POST['prflxtrflds_settings_nonce_field'] )
				&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_settings_nonce_field'] ) ), 'prflxtrflds_settings_action' )
			) {
				/* Settings Tab */
				$this->options['user_section_profile_title'] = isset( $_POST['prflxtrflds_user_section_profile_title'] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_section_profile_title'] ) ) : $this->options['user_section_profile_title'];
				$this->options['user_section_car_title']     = isset( $_POST['prflxtrflds_user_section_car_title'] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_section_car_title'] ) ) : $this->options['user_section_car_title'];

				update_option( 'prflxtrflds_options', $this->options );
				$message = __( 'Settings saved.', 'profile-extra-fields-pro' );
			}
			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Settings tab
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::tab_settings() for more information on default arguments.
		 */
		public function tab_settings() {
			?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'Profile Extra Fields Settings', 'profile-extra-fields' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table ">
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Title for Profile Extra Fields User Profile block', 'profile-extra-fields' ); ?> </th>
					<td>
						<label>
							<input class="regular-text" type="text" name="prflxtrflds_user_section_profile_title" value="<?php echo esc_html( $this->options['user_section_profile_title'] ); ?>" />
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Title for Car Rental V2 Extra Fields User Profile block', 'profile-extra-fields' ); ?> </th>
					<td>
						<label>
							<input class="regular-text" type="text" name="prflxtrflds_user_section_car_title" value="<?php echo esc_html( $this->options['user_section_car_title'] ); ?>" />
						</label>
					</td>
				</tr>							
			</table>
			<?php wp_nonce_field( 'prflxtrflds_settings_action', 'prflxtrflds_settings_nonce_field' ); ?>
			<?php
			if ( ! $this->hide_pro_tabs ) {
				?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php esc_html_e( 'Close', 'profile-extra-fields' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Gravity Forms', 'profile-extra-fields' ); ?></th>
								<td>
									<label>
										<input type="checkbox" disabled="disabled" /> <span class="bws_info"><?php esc_html_e( 'Ability to add Profile Extra Fields to the Gravity Forms.', 'profile-extra-fields' ); ?></span>
									</label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Title for Subscriber Extra Fields User Profile block', 'profile-extra-fields' ); ?> </th>
								<td>
									<label>
										<input class="regular-text" type="text" disabled="disabled" value="" />
									</label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Title for WooCommerce Extra Fields Checkout Form', 'profile-extra-fields' ); ?></th>
								<td>
									<label>
										<input class="regular-text" type="text" disabled="disabled" value="" /><br />
										<span class="bws_info"><?php esc_html_e( 'This title will be displayed on Checkout Page and WooCommerce order emails', 'profile-extra-fields' ); ?></span>
									</label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Title for WooCommerce Extra Fields Registration Form', 'profile-extra-fields' ); ?></th>
								<td>
									<label>
										<input class="regular-text" type="text" disabled="disabled" value="" /><br />
										<span class="bws_info"><?php esc_html_e( 'This title will be displayed only on the WooCommerce Registration For', 'profile-extra-fields' ); ?></span>
									</label>
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
				<?php
			}
		}

		/**
		 * Display metabox
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::display_metabox() for more information on default arguments.
		 */
		public function display_metabox() { ?>
			<div class="postbox">
				<h3 class="hndle">
					<?php esc_html_e( 'Profile Extra Fields Pro Shortcode', 'profile-extra-fields' ); ?>
				</h3>
				<div class="inside">
					<?php esc_html_e( 'Add user data for current user using the following shortcode:', 'profile-extra-fields' ); ?>
					<?php bws_shortcode_output( '[prflxtrflds_user_data user_id=get_current_user]' ); ?>
				</div>
				<div class="inside">
					<?php esc_html_e( 'Add user data for specific users using the following shortcode (where * is user ids, separated by commas):', 'profile-extra-fields' ); ?>
					<?php bws_shortcode_output( '[prflxtrflds_user_data user_id=*]' ); ?>
				</div>
				<div class="inside">
					<?php esc_html_e( 'Add user data for specific user roles using the following shortcode (where * is user roles, separated by commas):', 'profile-extra-fields' ); ?>
					<?php bws_shortcode_output( '[prflxtrflds_user_data user_role=*]' ); ?>
				</div>
				<div class="inside">
					<?php esc_html_e( 'Add user data specifying the data position (columns or rows) using the following shortcode (where * is top, left or right):', 'profile-extra-fields' ); ?>
					<?php bws_shortcode_output( '[prflxtrflds_user_data display=*]' ); ?>
				</div>
				<div class="inside">
					<?php esc_html_e( 'Display user data edit form on the front page:', 'profile-extra-fields' ); ?>
					<?php bws_shortcode_output( '[prflxtrflds_user_data_edit_form]' ); ?>
				</div>
			</div>
			<?php
		}
	}
}
