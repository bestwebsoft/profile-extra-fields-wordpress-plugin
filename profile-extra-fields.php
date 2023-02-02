<?php
/**
Plugin Name: Profile Extra Fields by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/profile-extra-fields/
Description: Add extra fields to default WordPress user profile. The easiest way to create and manage additional custom values.
Author: BestWebSoft
Text Domain: profile-extra-fields
Domain Path: /languages
Version: 1.2.7
Author URI: https://bestwebsoft.com/
License: GPLv3 or later
 */

/*
  @ Copyright 2021  BestWebSoft  ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Add WordPress page 'bws_panel' and sub-page of this plugin to admin-panel.
 *
 * @return void
 */
/*add settings page in bws menu*/
if ( ! function_exists( 'prflxtrflds_admin_menu' ) ) {
	function prflxtrflds_admin_menu() {
		global $submenu, $prflxtrflds_plugin_info, $wp_version;

		$settings = add_menu_page(
			__( 'All Fields', 'profile-extra-fields' ),
			'Profile Extra Fields',
			'manage_options',
			'profile-extra-fields.php',
			'prflxtrflds_fields'
		);
		add_submenu_page(
			'profile-extra-fields.php',
			__( 'All Fields', 'profile-extra-fields' ),
			__( 'All Fields', 'profile-extra-fields' ),
			'manage_options',
			'profile-extra-fields.php',
			'prflxtrflds_fields'
		);
		add_submenu_page(
			'profile-extra-fields.php',
			__( 'Add New Fields', 'profile-extra-fields' ),
			__( 'Add New', 'profile-extra-fields' ),
			'manage_options',
			'profile-extra-field-add-new.php',
			'prflxtrflds_edit_field'
		);
		add_submenu_page(
			'profile-extra-fields.php',
			__( 'Profile Extra Fields Settings', 'profile-extra-fields' ),
			__( 'Settings', 'profile-extra-fields' ),
			'manage_options',
			'profile-extra-fields-settings.php',
			'prflxtrflds_settings_page'
		);
		add_submenu_page(
			'profile-extra-fields.php',
			'BWS Panel',
			'BWS Panel',
			'manage_options',
			'prflxtrflds-bws-panel',
			'bws_add_menu_render'
		);
		if ( isset( $submenu['profile-extra-fields.php'] ) ) {
			$submenu['profile-extra-fields.php'][] = array(
				'<span style="color:#d86463"> ' . __( 'Upgrade to Pro', 'profile-extra-fields' ) . '</span>',
				'manage_options',
				'https://bestwebsoft.com/products/wordpress/plugins/profile-extra-fields/?k=c37eed44c2fe607f3400914345cbdc8a&pn=300&v=' . $prflxtrflds_plugin_info['Version'] . '&wp_v=' . $wp_version,
			);
		}

		add_action( 'load-' . $settings, 'prflxtrflds_screen_options' );
	}
}

/**
 * Internationalization
 */
if ( ! function_exists( 'prflxtrflds_plugins_loaded' ) ) {
	function prflxtrflds_plugins_loaded() {
		load_plugin_textdomain( 'profile-extra-fields', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/**
 * Plugin init
 */
if ( ! function_exists( 'prflxtrflds_init' ) ) {
	function prflxtrflds_init() {
		global $prflxtrflds_plugin_info;

		$plugins_data = apply_filters( 'bws_bkng_prflxtrflds_get_data', $plugins_data = array() );
		foreach ( (array) $plugins_data as $plugin ) {
			if ( isset( $plugin['actions'] ) ) {
				foreach ( $plugin['actions'] as $action ) {
					add_action( $action, 'prflxtrflds_fields_table' );
				}
			}
		}

		/** Add bws menu. use in prflxtrflds_admin_menu*/
		require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
		bws_include_init( plugin_basename( __FILE__ ) );
		/** Get plugin data */
		if ( empty( $prflxtrflds_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$prflxtrflds_plugin_info = get_plugin_data( __FILE__ );
		}
		/** Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $prflxtrflds_plugin_info, '4.5' );

		/** Call register settings function */
		if ( ! is_admin() || ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'profile-extra-fields.php', 'profile-extra-fields-settings.php' ) ) ) ) {
			prflxtrflds_settings();
		}
	}
}

/** Admin init */
if ( ! function_exists( 'prflxtrflds_admin_init' ) ) {
	function prflxtrflds_admin_init() {
		global $bws_plugin_info, $prflxtrflds_plugin_info, $bws_shortcode_list, $pagenow, $prflxtrflds_options;
		/** Add variable for bws_menu */
		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array(
				'id'      => '300',
				'version' => $prflxtrflds_plugin_info['Version'],
			);
		}

		/** Add gallery to global $bws_shortcode_list */
		$bws_shortcode_list['prflxtrflds'] = array(
			'name'        => 'Profile Extra Fields',
			'js_function' => 'prflxtrflds_shortcode_init',
		);

		if ( 'plugins.php' === $pagenow ) {
			/** Install the option defaults */
			if ( function_exists( 'bws_plugin_banner_go_pro' ) ) {
				prflxtrflds_settings();
				bws_plugin_banner_go_pro( $prflxtrflds_options, $prflxtrflds_plugin_info, 'prflxtrflds', 'profile-extra-fields', 'c37eed44c2fe607f3400914345cbdc8a', '300', 'profile-extra-fields' );
			}
		}

		if ( isset( $_POST['prflxtrflds_export_submit'] ) ) {
			$format_export = sanitize_text_field( wp_unslash( $_POST['prflxtrflds_format_export'] ) );
			$format        = in_array( $format_export, array( 'columns', 'rows' ) ) ? $format_export : 'columns';
			$nonce         = wp_create_nonce( 'prflxtrflds_export_action' );
			prflxtrflds_export_file( $format, $nonce );
		}
	}
}

if ( ! function_exists( 'prflxtrflds_export_file' ) ) {
	function prflxtrflds_export_file( $format, $nonce ) {
		if ( wp_verify_nonce( $nonce, 'prflxtrflds_export_action' ) ) {
			global $wp_filesystem;
			WP_Filesystem();
			$param     = array(
				'display' => $format,
				'export'  => true,
			);
			$export    = prflxtrflds_show_data( $param );
			$upload_dir = wp_upload_dir();
			$file_name = wp_tempnam( 'tmp', $upload_dir['path'] . '/' );
			if ( ! $file_name ) {
				return false;
			}
			$export_str = '';
			if ( is_array( $export ) ) {
				foreach( $export as $export_array ) {
					$export_str .= '"' . implode( '";"', $export_array ) . '";' . PHP_EOL;
				}
			}
			$result = $wp_filesystem->put_contents( $file_name, $export_str );
			if ( ! $result ) {
				return false;
			}
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="prflxtrflds_' . $format . '_export.csv"' );
			echo $wp_filesystem->get_contents( $file_name );
			unlink( $file_name );
			exit();
		}
	}
}

/** Update new users and roles */
if ( ! function_exists( 'prflxtrflds_update_users' ) ) {
	function prflxtrflds_update_users() {
		global $wpdb;
		$cache_key          = 'prflxtrflds_user_data';
		$users_data_from_db = wp_cache_get( $cache_key );
		if ( false === $users_data_from_db ) {
			$users_data_from_db = $wpdb->get_results( 'SELECT `id`, `role` FROM ' . $wpdb->base_prefix . 'prflxtrflds_user_data', ARRAY_A );
			wp_cache_set( $cache_key, $users_data_from_db );
		}

		if ( $users_data_from_db ) {
			$all_user_in_db = array();
			foreach ( $users_data_from_db as $user ) {
				/** convert to 2D-array */
				$all_user_in_db[ $user['id'] ] = $user['role'];
			}
		}
		/** Get actual WordPress data */
		$users = get_users();
		if ( $users ) {
			foreach ( $users as $user ) {
				/** Write user id and role*/
				if ( ! isset( $all_user_in_db ) || ! array_key_exists( $user->ID, $all_user_in_db ) ) {
					/** $all_user_in_db not exist if database empty */
					$wpdb->insert(
						$wpdb->base_prefix . 'prflxtrflds_user_data',
						array(
							'userid' => $user->ID,
							'role'   => implode(
								', ',
								$user->roles
							),
						)
					);
				}
			}
			$users_data_from_db = $wpdb->get_results( 'SELECT `id`, `role` FROM ' . $wpdb->base_prefix . 'prflxtrflds_user_data', ARRAY_A );
			wp_cache_set( $cache_key, $users_data_from_db );
		}
	}
}

/** This is settings functions */
if ( ! function_exists( 'prflxtrflds_settings' ) ) {
	function prflxtrflds_settings() {
		global $prflxtrflds_options, $prflxtrflds_plugin_info, $wpdb;
		/** Db version in plugin */
		$db_version = '1.6';

		/** Install the option defaults */
		if ( ! get_option( 'prflxtrflds_options' ) ) {
			$option_defaults = prflxtrflds_get_options_default();
			add_option( 'prflxtrflds_options', $option_defaults );
		}

		/** Get options from database */
		$prflxtrflds_options = get_option( 'prflxtrflds_options' );
		/** Update options if other option version */
		if ( ! isset( $prflxtrflds_options['plugin_option_version'] ) || $prflxtrflds_options['plugin_option_version'] !== $prflxtrflds_plugin_info['Version'] ) {
			if ( isset( $prflxtrflds_options['plugin_option_version'] ) ) {
				$prflxtrflds_prev_version = str_replace( 'pro-', '', $prflxtrflds_options['plugin_option_version'] );
				if ( version_compare( $prflxtrflds_prev_version, '1.1.4', '<=' ) ) {
					/*In version 1.1.5, the field type "textarea" has been added and we need to rewrite the db*/
					$wpdb->query( 'UPDATE `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`  SET `field_type_id` = IF ( `field_type_id` > 1 ,`field_type_id` + 1 , `field_type_id`);' );
				}
			}
			if ( ! empty( $prflxtrflds_options['available_values'] ) ) {
				foreach ( $prflxtrflds_options['available_values'] as $key => $value ) {
					if ( '-1' === $value ) {
						unset( $prflxtrflds_options['available_values'][ $key ] );
					}
				}
			}
			$option_defaults                              = prflxtrflds_get_options_default();
			$option_defaults['display_settings_notice']   = 0;
			$prflxtrflds_options                          = array_merge( $option_defaults, $prflxtrflds_options );
			$prflxtrflds_options['plugin_option_version'] = $prflxtrflds_plugin_info['Version'];

			/** Show pro features */
			$prflxtrflds_options['hide_premium_options'] = array();

			$update_option = true;
			prflxtrflds_activation();
		}

		/** Update database */
		if ( ! isset( $prflxtrflds_options['plugin_db_version'] ) ||
			$prflxtrflds_options['plugin_db_version'] !== $db_version
		) {
			prflxtrflds_create_table();

			$column_exists = $wpdb->query( 'SHOW COLUMNS FROM `' . $wpdb->base_prefix . "prflxtrflds_roles_and_fields` LIKE 'editable'" );
			if ( 0 === $column_exists ) {
				$wpdb->query(
					'ALTER TABLE `' . $wpdb->base_prefix . "prflxtrflds_roles_and_fields`
					ADD `editable` tinyint(1) NOT NULL DEFAULT '1',
					ADD `visible` tinyint(1) NOT NULL DEFAULT '1'"
				);
			}

			$required_type = $wpdb->get_var(
				"SELECT DATA_TYPE
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE
					TABLE_NAME = '" . $wpdb->base_prefix . "prflxtrflds_fields_id' AND
					COLUMN_NAME = 'required'"
			);

			if ( 'varchar' !== $required_type ) {
				$wpdb->query(
					'ALTER TABLE `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`
					MODIFY COLUMN `required` VARCHAR(255) COLLATE utf8_general_ci'
				);

				$wpdb->update(
					$wpdb->base_prefix . 'prflxtrflds_fields_id',
					array( 'required' => '' ),
					array( 'required' => '0' )
				);
				$wpdb->update(
					$wpdb->base_prefix . 'prflxtrflds_fields_id',
					array( 'required' => '*' ),
					array( 'required' => '1' )
				);
			}

			$fields = $wpdb->get_results( 'SELECT * FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`' );

			if ( ! empty( $fields ) && isset( $fields[0]->show_in_register_form ) ) {
				foreach ( $fields as $field ) {
					if ( 1 === $field->show_in_register_form ) {
						$data = array(
							'field_id' => $field->field_id,
							'show_in'  => 'register_form',
							'value'    => $field->show_in_register_form,
						);
						$wpdb->insert( $wpdb->base_prefix . 'prflxtrflds_fields_meta', $data );
					}
				}
				$wpdb->query( 'ALTER TABLE `' . $wpdb->base_prefix . 'prflxtrflds_fields_id` DROP COLUMN `show_in_register_form`' );
			}

			if ( ! empty( $fields ) && isset( $fields[0]->show_in_woocomerce_form ) ) {
				foreach ( $fields as $field ) {
					if ( 1 === $field->show_in_woocomerce_form ) {
						$value = array(
							'checkout' => $field->show_in_woocomerce_checkout,
							'register' => $field->show_in_woocomerce_registration,
						);
						$data  = array(
							'field_id' => $field->field_id,
							'show_in'  => 'woocommerce',
							'value'    => maybe_serialize( $value ),
						);
						$wpdb->insert( $wpdb->base_prefix . 'prflxtrflds_fields_meta', $data );
					}
				}
				$wpdb->query(
					'ALTER TABLE `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`
					DROP `show_in_woocomerce_form`,
					DROP `show_in_woocomerce_checkout`,
					DROP `show_in_woocomerce_registration`'
				);
			}

			$prflxtrflds_options['plugin_db_version'] = $db_version;
			$update_option                            = true;
		}

		/** If option was updated */
		if ( isset( $update_option ) ) {
			update_option( 'prflxtrflds_options', $prflxtrflds_options );
		}
	}
}

if ( ! function_exists( 'prflxtrflds_get_options_default' ) ) {
	function prflxtrflds_get_options_default() {
		global $prflxtrflds_plugin_info;
		/** Create array with default options */
		return array(
			'plugin_option_version'   => $prflxtrflds_plugin_info['Version'],
			'display_settings_notice' => 1,
			'suggest_feature_banner'  => 1,
			'sort_sequence'           => 'ASC',
			'available_fields'        => array(),
			'available_values'        => array(),
			'show_empty_columns'      => 0,
			'show_id'                 => 1,
			'header_table'            => 'columns', /*rows */
			'empty_value'             => __( 'The field is empty', 'profile-extra-fields' ),
			'not_available_message'   => __( 'N/A', 'profile-extra-fields' ),
			'shortcode_debug'         => 1,
			'display_user_name'       => 'username',
		);
	}
}

if ( ! function_exists( 'prflxtrflds_get_field_type_id' ) ) {
	function prflxtrflds_get_field_type_id() {
		/** Conformity between field type id and field type name */
		return array(
			'1'  => __( 'Text field', 'profile-extra-fields' ),
			'2'  => __( 'Textarea', 'profile-extra-fields' ),
			'3'  => __( 'Checkbox', 'profile-extra-fields' ),
			'4'  => __( 'Radio button', 'profile-extra-fields' ),
			'5'  => __( 'Drop down list', 'profile-extra-fields' ),
			'6'  => __( 'Date', 'profile-extra-fields' ),
			'7'  => __( 'Time', 'profile-extra-fields' ),
			'8'  => __( 'Datetime', 'profile-extra-fields' ),
			'9'  => __( 'Number', 'profile-extra-fields' ),
			'10' => __( 'Phone number', 'profile-extra-fields' ),
			'11' => __( 'URL link', 'profile-extra-fields' ),
			'12' => __( 'Attachment', 'profile-extra-fields' ),
		);
	}
}

if ( ! function_exists( 'prflxtrflds_create_table' ) ) {
	function prflxtrflds_create_table() {
		global $wpdb;

		/** Require db Delta */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		/** Create table for roles types */
		$table_name = $wpdb->base_prefix . 'prflxtrflds_roles_id';
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
		if ( $wpdb->get_var( $query ) !== $table_name ) {
			/* create table for roles types */
			$sql = 'CREATE TABLE `' . $table_name . '` (
				`role_id` bigint(20) NOT NULL AUTO_INCREMENT,
				`role` VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
				`role_name` VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
				UNIQUE KEY (role_id)
			);';
			/** Call dbDelta */
			dbDelta( $sql );
		}

		$table_name = $wpdb->base_prefix . 'prflxtrflds_user_roles';
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( $wpdb->get_var( $query ) !== $table_name ) {
			/* create table for conformity user_id and user role id */
			$sql = 'CREATE TABLE `' . $table_name . '` (
				`user_id` bigint(20) NOT NULL,
				`role_id` bigint(20) NOT NULL,
				UNIQUE KEY (user_id)
			);';
			/** Call dbDelta */
			dbDelta( $sql );
		}
		/** Create roles id */
		if ( function_exists( 'prflxtrflds_update_user_roles' ) ) {
			prflxtrflds_update_user_roles();
		}

		$table_name = $wpdb->base_prefix . 'prflxtrflds_fields_id';
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( $wpdb->get_var( $query ) !== $table_name ) {
			$sql = 'CREATE TABLE `' . $table_name . "` (
				`field_id` bigint(20) NOT NULL AUTO_INCREMENT,
				`field_name` text NOT NULL COLLATE utf8_general_ci,
				`required` VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
				`show_default` int(1) NOT NULL DEFAULT '0',
				`show_always` int(1) NOT NULL DEFAULT '0',
				`description` text NOT NULL COLLATE utf8_general_ci,
				`field_type_id` bigint(20) NOT NULL DEFAULT '0',
				UNIQUE KEY (field_id)
				);";
			/** Call dbDelta */
			dbDelta( $sql );
		}

		$table_name = $wpdb->base_prefix . 'prflxtrflds_fields_meta';
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( $wpdb->get_var( $query ) !== $table_name ) {
			$sql = 'CREATE TABLE `' . $table_name . '` (
				`meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
				`field_id` bigint(20) NOT NULL,
				`show_in` VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
				`value` TEXT NOT NULL COLLATE utf8_general_ci,
				PRIMARY KEY (meta_id),
				CONSTRAINT prflxtrflds_unique_pair UNIQUE (field_id, show_in)
				);';
			/** Call dbDelta */
			dbDelta( $sql );
		}

		$table_name = $wpdb->base_prefix . 'prflxtrflds_roles_and_fields';
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( $wpdb->get_var( $query ) !== $table_name ) {
			/* create table conformity roles id with fields id */
				$sql = 'CREATE TABLE `' . $table_name . "` (
				`role_id` bigint(20) NOT NULL DEFAULT '0',
				`field_id` bigint(20) NOT NULL DEFAULT '0',
				`field_order` bigint(20) NOT NULL DEFAULT '0',
				`editable` tinyint(1) NOT NULL DEFAULT '1',
				`visible` tinyint(1) NOT NULL DEFAULT '1',
				UNIQUE KEY (role_id, field_id)
			);";
			/** Call dbDelta */
			dbDelta( $sql );
		}

		$table_name = $wpdb->base_prefix . 'prflxtrflds_field_values';
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( $wpdb->get_var( $query ) !== $table_name ) {
			/* create table conformity field id with available value */
			$sql = 'CREATE TABLE `' . $table_name . "` (
				`value_id` bigint(20) NOT NULL AUTO_INCREMENT,
				`field_id` bigint(20) NOT NULL DEFAULT '0',
				`value_name` VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
				`order` bigint(20) NOT NULL DEFAULT '0',
				UNIQUE KEY (value_id)
			);";
			/** Call dbDelta */
			dbDelta( $sql );
		}

		$table_name = $wpdb->base_prefix . 'prflxtrflds_user_field_data';
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( $wpdb->get_var( $query ) !== $table_name ) {
			/* create table conformity field id with available value */
			$sql = 'CREATE TABLE `' . $table_name . '` (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`field_id` bigint(20) NOT NULL,
				`user_id` bigint(20) NOT NULL,
				`user_value` TEXT NOT NULL COLLATE utf8_general_ci,
				UNIQUE KEY (id)
			);';
			/** Call dbDelta */
			dbDelta( $sql );
		}
	}
}

if ( ! function_exists( 'prflxtrflds_activation' ) ) {
	function prflxtrflds_activation() {
		/** Uninstall plugin */
		if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'prflxtrflds_uninstall' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'prflxtrflds_uninstall' );
		}
	}
}

/** Create conformity between roles and role_id */
if ( ! function_exists( 'prflxtrflds_update_roles_id' ) ) {
	function prflxtrflds_update_roles_id() {
		global $wpdb, $wp_roles;
		/** Get all available role */
		$all_roles = $wp_roles->roles;
		if ( ! empty( $all_roles ) ) {
			/** Get role name from array */
			foreach ( $all_roles as $role_key => $role ) {
				/** Check role for existing in plugin table */
				if ( ! $wpdb->get_var( $wpdb->prepare( 'SELECT `role_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_id` WHERE `role` = %s LIMIT 1', $role_key ) ) ) {
					/** Create field if not exist */
					$wpdb->insert(
						$wpdb->base_prefix . 'prflxtrflds_roles_id',
						array(
							'role'      => $role_key,
							'role_name' => $role['name'],
						),
						array( '%s', '%s' )
					);
				}
			}
		}
	}
}

/** Create conformity between user_id and role_id */
if ( ! function_exists( 'prflxtrflds_update_user_roles' ) ) {
	function prflxtrflds_update_user_roles( $user_id = null, $role = null ) {
		global $wpdb;
		/** First, update roles id */
		if ( function_exists( 'prflxtrflds_update_roles_id' ) ) {
			prflxtrflds_update_roles_id();
		}
		if ( null !== $user_id && ( null === $role || ! is_string( $role ) ) ) {
			/** Get role if not allowed */
			require_once ABSPATH . 'wp-includes/pluggable.php';
			$user_data = get_userdata( $user_id );
			/** Get user role by id */
			if ( isset( $user_data ) ) {
				$role = implode( ', ', $user_data->roles );
			}
		}

		if ( ! isset( $role ) ) {
			$users = get_users();
			if ( $users ) {
				/** If no selected roles, update roles for all users */
				foreach ( $users as $user ) {
					foreach ( $user->roles as $role_key ) {
						/** Role stored in array, get */
						$role_id = $wpdb->get_var( $wpdb->prepare( 'SELECT `role_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_id` WHERE `role`= %s LIMIT 1', $role_key ) );
						if ( $role_id ) {
							/** Insert value */
							$wpdb->replace(
								$wpdb->base_prefix . 'prflxtrflds_user_roles',
								array(
									'user_id' => $user->ID,
									'role_id' => $role_id,
								),
								array( '%d', '%d' )
							);
						}
					}
				}
			}
		} else {
			/** If role select, update role only for this user */
			$role_id = $wpdb->get_var( $wpdb->prepare( 'SELECT `role_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_id` WHERE `role`=%s LIMIT 1', $role ) );
			if ( $role_id ) {
				$wpdb->replace(
					$wpdb->base_prefix . 'prflxtrflds_user_roles',
					array(
						'user_id' => $user_id,
						'role_id' => $role_id,
					),
					array( '%d', '%d' )
				);
			}
		}
	}
}

/** Edit or create new field */
if ( ! function_exists( 'prflxtrflds_edit_field' ) ) {
	function prflxtrflds_edit_field() {
		global $wpdb, $prflxtrflds_options, $wp_version, $prflxtrflds_plugin_info;
		$prflxtrflds_field_type_id = prflxtrflds_get_field_type_id();
		$field_name = $description = $field_maxlength = $field_rows = $field_cols = $field_required = '';
		$error                     = '';
		$field_order               = $field_show_default = $field_show_always = 0;
		$field_pattern             = '***-**-**';
		$available_values          = $show_in = array();
		$field_type_id             = '1';
		$field_date_format         = get_option( 'date_format' );
		$field_time_format         = get_option( 'time_format' );

		/** Get field id with post or get */
		$field_id = isset( $_REQUEST['prflxtrflds_field_id'] ) && '' !== $_REQUEST['prflxtrflds_field_id'] ? absint( $_REQUEST['prflxtrflds_field_id'] ) : null;
		
		/** If field id is NULL - create new entry */
		if ( is_null( $field_id ) ) {
			$field_id = $wpdb->get_var( 'SELECT MAX(`field_id`) FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`' );
			if ( ! $field_id ) {
				/** If table is empty */
				$field_id = 1;
			} else {
				/** Generate new id */
				$field_id++;
			}
		}

		/** If is save settings page, call save field function */
		if ( isset( $_POST['prflxtrflds_save_field'] ) && check_admin_referer( 'prflxtrflds_nonce_name' ) ) {
			$field_name           = isset( $_POST['prflxtrflds_field_name'] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_field_name'] ) ) : '';
			$field_type_id        = isset( $_POST['prflxtrflds_type'] ) ? absint( $_POST['prflxtrflds_type'] ) : 1;
			$error                = 12 === $field_type_id ? __( 'Unable to create field of this type.', 'profile-extra-fields' ) : '';
			$description          = isset( $_POST['prflxtrflds_description'] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_description'] ) ) : '';
			$checked_roles_data   = isset( $_POST['prflxtrflds_roles'] ) ? array_filter( array_map( 'absint', (array) $_POST['prflxtrflds_roles'] ) ) : array(); /** Is array */
			$checked_editables    = isset( $_POST['prflxtrflds_editable'] ) ? array_filter( array_map( 'absint', (array) $_POST['prflxtrflds_editable'] ) ) : array(); /** Is array */
			$checked_visibilities = isset( $_POST['prflxtrflds_visibility'] ) ? array_filter( array_map( 'absint', (array) $_POST['prflxtrflds_visibility'] ) ) : array(); /** Is array */
			$checked_roles        = array();

			foreach ( $checked_roles_data as $role_id ) {
				$editable                  = in_array( $role_id, $checked_editables ) ? 1 : 0;
				$visible                   = in_array( $role_id, $checked_visibilities ) ? 1 : 0;
				$checked_roles[ $role_id ] = array(
					'editable' => $editable,
					'visible'  => $visible,
				);
			}

			$field_maxlength = isset( $_POST['prflxtrflds_maxlength'] ) && is_numeric( $_POST['prflxtrflds_maxlength'] ) ? absint( $_POST['prflxtrflds_maxlength'] ) : 255;

			$field_rows = isset( $_POST['prflxtrflds_rows'] ) && is_numeric( $_POST['prflxtrflds_rows'] ) ? absint( $_POST['prflxtrflds_rows'] ) : 2;
			$field_cols = isset( $_POST['prflxtrflds_cols'] ) && is_numeric( $_POST['prflxtrflds_cols'] ) ? absint( $_POST['prflxtrflds_cols'] ) : 50;

			$field_pattern = isset( $_POST['prflxtrflds_pattern'] ) ? preg_replace( '/[^\*\-\(\)\+]/', '', sanitize_text_field( wp_unslash( $_POST['prflxtrflds_pattern'] ) ) ) : '***-**-**';

			if ( isset( $_POST['prflxtrflds_time_format'] ) ) {
				$field_time_format = ( 'custom' === sanitize_text_field( wp_unslash( $_POST['prflxtrflds_time_format'] ) ) && isset( $_POST['prflxtrflds_time_format_custom'] ) ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_time_format_custom'] ) ) : sanitize_text_field( wp_unslash( $_POST['prflxtrflds_time_format'] ) );
			}
			if ( isset( $_POST['prflxtrflds_date_format'] ) ) {
				$field_date_format = ( 'custom' === sanitize_text_field( wp_unslash( $_POST['prflxtrflds_date_format'] ) ) && isset( $_POST['prflxtrflds_time_format_custom'] ) ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_date_format_custom'] ) ) : sanitize_text_field( wp_unslash( $_POST['prflxtrflds_date_format'] ) );
			}

			$field_order = isset( $_POST['prflxtrflds_order'] ) && is_numeric( $_POST['prflxtrflds_order'] ) ? absint( $_POST['prflxtrflds_order'] ) : 0;

			$field_required     = isset( $_POST['prflxtrflds_required'], $_POST['prflxtrflds_required_symbol'] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_required_symbol'] ) ) : '';
			$field_show_default = isset( $_POST['prflxtrflds_show_default'] ) ? 1 : 0;
			$field_show_always  = isset( $_POST['prflxtrflds_show_always'] ) ? 1 : 0;
			$show_in            = isset( $_POST['prflxtrflds_show_in'] ) ? array_map( 'wp_unslash', $_POST['prflxtrflds_show_in'] ) : array();

			if ( isset( $_POST['prflxtrflds-value-delete'] ) ) {
				$field_value_to_delete = array_map( 'intval', $_POST['prflxtrflds-value-delete'] );
			}

			$i = 1;
			if ( isset( $_POST['prflxtrflds_available_values'] ) && is_array( $_POST['prflxtrflds_available_values'] ) ) {
				$nonsort_available_values = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST['prflxtrflds_available_values'] ) );
				$value_ids                = isset( $_POST['prflxtrflds_value_id'] ) ? array_map( 'intval', $_POST['prflxtrflds_value_id'] ) : array();
				/** Is array */
				foreach ( $nonsort_available_values as $key => $value ) {
					if ( '' !== $value ) {
						$available_values[] = array(
							'value_name'  => $value,
							'value_id'    => ( isset( $value_ids[ $key ] ) ) ? $value_ids[ $key ] : '',
							'value_order' => $i,
						);
						$i++;
					} elseif ( ! empty( $value_ids[ $key ] ) ) {
						/** If field empty - delete entry */
						$field_value_to_delete[] = $value_ids[ $key ];
					}
				}
			}

			/** Delete fields if necessary */
			if ( ! empty( $field_value_to_delete ) && is_array( $field_value_to_delete ) ) {
				foreach ( $field_value_to_delete as $deleting_value_id ) {
					if ( '' !== $deleting_value_id ) {
						/** Remove field */
						$wpdb->delete(
							$wpdb->base_prefix . 'prflxtrflds_field_values',
							array(
								'value_id' => intval( $deleting_value_id ),
							)
						);
						/** Remove user data */
						$wpdb->delete(
							$wpdb->base_prefix . 'prflxtrflds_user_field_data',
							array(
								'field_id'   => $field_id,
								'user_value' => intval( $deleting_value_id ),
							)
						);
					}
				}
			}
			/** Name of page if error */
			$name_of_page = __( 'Edit Field', 'profile-extra-fields' );

			if ( empty( $_POST['prflxtrflds_field_name'] ) ) {
				$error .= __( 'Field name is empty.', 'profile-extra-fields' );
			}

			/** If roles not selected */
			if ( empty( $_POST['prflxtrflds_roles'] ) || 1 === count( $_POST['prflxtrflds_roles'] ) ) {
				$error .= __( 'Select at least one user role.', 'profile-extra-fields' );
			}

			if ( 10 === $field_type_id ) {
				if ( empty( $_POST['prflxtrflds_pattern'] ) ) {
					$error .= sprintf( __( 'Please specify a mask which will be used for the phone validation, where * is a number. Use only the following symbols %1$s', 'profile-extra-fields' ), '* - ( ) +' );
				}
			} elseif ( in_array( $field_type_id, array( '3', '4', '5' ) ) &&
				! empty( $_POST['prflxtrflds_available_values'] )
			) {
				/** If not choisen values */
				if ( is_array( $_POST['prflxtrflds_available_values'] ) ) {
					$_POST['prflxtrflds_available_values'] = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) $_POST['prflxtrflds_available_values'] ) );
					$filled                                = 0;
					foreach ( $_POST['prflxtrflds_available_values'] as $one_value ) {
						if ( ! empty( $one_value ) ) {
							$filled++;
						}
					}
					/** If all values is empty */
					if ( 0 === $filled ) {
						$error .= __( 'Select at least one available value.', 'profile-extra-fields' );
					} elseif ( 2 > $filled && ( 4 === $field_type_id || 5 === $field_type_id ) ) {
						/** If is radiobutton or select, select more if two available values */
						$error .= __( 'Select at least two available values.', 'profile-extra-fields' );
					}
				} else {
					$error .= __( 'Select at least one available value.', 'profile-extra-fields' );
				}
			}
			/** End check error */
			if ( empty( $error ) ) {
				/** Check for exist field id */
				if ( 1 === $wpdb->query( $wpdb->prepare( 'SELECT `field_id` FROM ' . $wpdb->base_prefix . 'prflxtrflds_fields_id WHERE `field_id`=%d', $field_id ) ) ) {
					$message = __( 'The field has been updated.', 'profile-extra-fields' );
				} else {
					$message = __( 'The field has been created.', 'profile-extra-fields' );
				}

				/** Update data */
				$wpdb->replace(
					$wpdb->base_prefix . 'prflxtrflds_fields_id',
					array(
						'field_id'      => $field_id,
						'field_name'    => $field_name,
						'required'      => $field_required,
						'description'   => $description,
						'show_default'  => $field_show_default,
						'show_always'   => $field_show_always,
						'field_type_id' => $field_type_id,
					)
				);

				foreach ( $show_in as $show => $value ) {
					if ( '0' !== $value ) {
						$result = $wpdb->replace(
							$wpdb->base_prefix . 'prflxtrflds_fields_meta',
							array(
								'field_id' => $field_id,
								'show_in'  => $show,
								'value'    => maybe_serialize( $value ),
							)
						);
					} else {
						$wpdb->delete(
							$wpdb->base_prefix . 'prflxtrflds_fields_meta',
							array(
								'field_id' => $field_id,
								'show_in'  => $show,
							)
						);
					}
				}

				/** Get all available roles id */
				$all_roles_in_db = $wpdb->get_col( $wpdb->prepare( 'SELECT `role_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields` WHERE `field_id` = %d', $field_id ) );
				if ( ! empty( $all_roles_in_db ) ) {
					foreach ( $all_roles_in_db as $role_id ) {
						if ( ! array_key_exists( $role_id, $checked_roles ) ) {
							/** Delete unchecked role */
							$wpdb->delete(
								$wpdb->base_prefix . 'prflxtrflds_roles_and_fields',
								array(
									'field_id' => $field_id,
									'role_id'  => $role_id,
								)
							);
						}
					}
				}
				/** Update data */
				if ( ! empty( $checked_roles ) ) {
					/** If field order change, apply it for all roles */
					$default_order = $wpdb->get_var( $wpdb->prepare( 'SELECT `field_order` FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields` WHERE `field_id`= %d AND `role_id`=0', $field_id ) );
					if ( $field_order !== $default_order ) {
						foreach ( $checked_roles as $role_id => $role_value ) {
							$wpdb->replace(
								$wpdb->base_prefix . 'prflxtrflds_roles_and_fields',
								array(
									'field_id'    => $field_id,
									'role_id'     => $role_id,
									'field_order' => $field_order,
									'editable'    => $role_value['editable'],
									'visible'     => $role_value['visible'],
								),
								array( '%d', '%d', '%d', '%d', '%d' )
							);
						}
					} else {
						/** If field order not change, not apply it. Hold old data */
						foreach ( $checked_roles as $role_id => $role_value ) {
							$old_order = $wpdb->get_var( $wpdb->prepare( 'SELECT `field_order` FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields` WHERE `field_id`=%d AND `role_id`=%s', $field_id, $role_id ) );
							/** If old order not exists, set default order */
							if ( ( ! isset( $old_order ) ) && ( isset( $field_order ) ) ) {
								/** For new roles */
								$old_order = $field_order;
							} elseif ( ( ! isset( $old_order ) ) && ( ! isset( $field_order ) ) ) {
								/** Default order if not exist */
								$old_order = 0;
							}

							$wpdb->replace(
								$wpdb->base_prefix . 'prflxtrflds_roles_and_fields',
								array(
									'field_id'    => $field_id,
									'role_id'     => $role_id,
									'field_order' => $old_order,
									'editable'    => $role_value['editable'],
									'visible'     => $role_value['visible'],
								),
								array( '%d', '%d', '%d', '%d', '%d' )
							);
						}
					}
				} else {
					/** If no available roles, create entry with role_id = 0 */
					$wpdb->replace(
						$wpdb->base_prefix . 'prflxtrflds_roles_and_fields',
						array(
							'field_id'    => $field_id,
							'role_id'     => 0,
							'field_order' => $field_order,
						),
						array( '%d', '%d', '%d' )
					);
				}
				/** Prflxtrflds_field_values update */
				if ( 1 === $field_type_id ||
					2 === $field_type_id ||
					6 === $field_type_id ||
					7 === $field_type_id ||
					8 === $field_type_id ||
					9 === $field_type_id ||
					10 === $field_type_id ||
					11 === $field_type_id
				) {
					switch ( $field_type_id ) {
						case '1':
							$value_name = $field_maxlength;
							break;
						case '2':
							$value_name = serialize(
								array(
									'rows'       => $field_rows,
									'cols'       => $field_cols,
									'max_length' => $field_maxlength,
								)
							);
							break;
						case '9':
							$value_name = $field_maxlength;
							break;
						case '10':
							$value_name = $field_pattern;
							break;
						case '11':
							$value_name = $field_maxlength;
							break;
						case '6':
							$value_name = $field_date_format;
							break;
						case '7':
							$value_name = $field_time_format;
							break;
						case '8':
							$value_name = serialize(
								array(
									'date' => $field_date_format,
									'time' => $field_time_format,
								)
							);
							break;
					}
					/** If entry with current id not exist, create new entry */
					if ( $wpdb->get_var( $wpdb->prepare( 'SELECT `field_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values` WHERE `field_id`= %d', $field_id ) ) ) {
						if ( '' !== $value_name ) {
							$wpdb->update(
								$wpdb->base_prefix . 'prflxtrflds_field_values',
								array( 'value_name' => $value_name ),
								array( 'field_id' => $field_id )
							);
						} else {
							$wpdb->delete(
								$wpdb->base_prefix . 'prflxtrflds_field_values',
								array(
									'field_id' => $field_id,
								)
							);
						}
					} elseif ( '' !== $value_name ) {
						$wpdb->insert(
							$wpdb->base_prefix . 'prflxtrflds_field_values',
							array(
								'value_name' => $value_name,
								'field_id'   => $field_id,
							)
						);
					}
				} elseif ( ! empty( $available_values ) ) {
					foreach ( $available_values as $i => $value ) {
						/** If entry with current id exists, update it */
						if ( ! empty( $value['value_id'] ) ) {
							/** Update entry if not empty field (rename entry) */
							$wpdb->update(
								$wpdb->base_prefix . 'prflxtrflds_field_values',
								array(
									'value_name' => $value['value_name'],
									'order'      => $value['value_order'],
								),
								array( 'value_id' => $value['value_id'] )
							);
						} else {
							/** If entry with current id not exist, create new entry */
							$wpdb->insert(
								$wpdb->base_prefix . 'prflxtrflds_field_values',
								array(
									'value_name' => $value['value_name'],
									'field_id'   => $field_id,
									'order'      => $value['value_order'],
								)
							);
							$result_id                          = $wpdb->insert_id;
							$available_values[ $i ]['value_id'] = $result_id;
						}
					}
				}
			}
		}

				if ( ! is_null( $field_id ) ) {
			/** Name of page if field exist */
			$name_of_page = __( 'Edit Field', 'profile-extra-fields' );
			/** If get $field_id - edit field */
			$field_options   = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT *
						FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`
						WHERE `field_id` = %d',
					$field_id
				),
				ARRAY_A
			);
			$show_in_results = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT `show_in`, `value`
						FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_meta`
						WHERE `field_id` = %d',
					$field_id
				),
				ARRAY_A
			);

			if ( ! empty( $show_in_results ) ) {
				foreach ( $show_in_results as $show ) {
					$show_in[ $show['show_in'] ] = maybe_unserialize( $show['value'] );
				}
			}
			if ( ! $field_options ) {
				/** If entry not exist - create new entry */
				$field_id = null;
			} else {
				$field_name         = $field_options['field_name'];
				$field_required     = $field_options['required'];
				$description        = $field_options['description'];
				$field_show_default = $field_options['show_default'];
				$field_show_always  = $field_options['show_always'];
				$field_type_id      = $field_options['field_type_id'];
				/** Get avaliable roles */
				$checked_roles_data = $wpdb->get_results( $wpdb->prepare( 'SELECT `role_id`, `editable`, `visible` FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields` WHERE `field_id`=%d', $field_id ), ARRAY_A );
				foreach ( $checked_roles_data as $value ) {
					$checked_roles[ $value['role_id'] ] = array(
						'editable' => $value['editable'],
						'visible'  => $value['visible'],
					);
				}

				$field_order = $wpdb->get_var( $wpdb->prepare( 'SELECT `field_order` FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields` WHERE `field_id`=%d LIMIT 1', $field_id ) );
				/** Get available values to checkbox, radiobutton, select, etc */
				if ( '10' === $field_type_id ) {
					$field_pattern = $wpdb->get_var( $wpdb->prepare( 'SELECT `value_name` FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values` WHERE `field_id`=%d', $field_id ) );
				} elseif ( '6' === $field_type_id ) {
					$field_date_format = $wpdb->get_var( $wpdb->prepare( 'SELECT `value_name` FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values` WHERE `field_id`=%d', $field_id ) );
				} elseif ( '7' === $field_type_id ) {
					$field_time_format = $wpdb->get_var( $wpdb->prepare( 'SELECT `value_name` FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values` WHERE `field_id`=%d', $field_id ) );
				} elseif ( '8' === $field_type_id ) {
					$date_and_time = unserialize( $wpdb->get_var( $wpdb->prepare( 'SELECT `value_name` FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values` WHERE `field_id`=%d', $field_id ) ) );
					if ( isset( $date_and_time['date'] ) ) {
						$field_date_format = $date_and_time['date'];
					}
					if ( isset( $date_and_time['time'] ) ) {
						$field_time_format = $date_and_time['time'];
					}
				} elseif ( '1' === $field_type_id || '9' === $field_type_id ) {
					$field_maxlength = $wpdb->get_var( $wpdb->prepare( 'SELECT `value_name` FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values` WHERE `field_id`=%d', $field_id ) );
				} elseif ( '2' === $field_type_id ) {
					$unser_textarea  = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( 'SELECT `value_name` FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values` WHERE `field_id`=%d', $field_id ) ) );
					$field_rows      = $unser_textarea['rows'];
					$field_cols      = $unser_textarea['cols'];
					$field_maxlength = $unser_textarea['max_length'];
				} else {
					$available_values = $wpdb->get_results( $wpdb->prepare( 'SELECT `value_id`, `value_name`, `order` FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values` WHERE `field_id`=%d ORDER BY `order`', $field_id ), ARRAY_A );
				}
			}
		} else {
			$name_of_page = __( 'Add New Field', 'profile-extra-fields' );
		}

		prflxtrflds_settings();

		$bws_hide_premium_options_check = bws_hide_premium_options_check( $prflxtrflds_options );
		/** Update roles id */
		prflxtrflds_update_roles_id();
		/** Get all avaliable roles */
		$all_roles = $wpdb->get_results( 'SELECT * FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_id`' ); ?>
		<div class="wrap">
		<?php $tab_action_true = ( isset( $_GET['tab-action'] ) ) ? '&tab-action=' . sanitize_text_field( wp_unslash( $_GET['tab-action'] ) ) : ''; ?>
			<h1 class="wp-heading-inline"><?php echo esc_html( $name_of_page ); ?></h1>
		<?php if ( ! empty( $error ) ) { ?>
			<div class="error below-h2"><p><strong><?php echo esc_html( $error ); ?></strong></p></div>
		<?php } elseif ( ! empty( $message ) ) { ?>
			<div class="updated fade below-h2"><p><?php echo esc_html( $message ); ?></p></div>
			<?php
		}
		if ( empty( $field_id ) ) {
			$action = '?page=profile-extra-field-add-new.php&amp;edit=1';
		} else {
			$action = sprintf( '?page=profile-extra-field-add-new.php&amp;edit=1&amp;prflxtrflds_field_id=%d', $field_id );
		}
		$action_url = $action . $tab_action_true;
		?>
		<form class="bws_form" method="post" action="<?php echo esc_url( $action_url ); ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( 'Name', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="text" name="prflxtrflds_field_name" value="<?php echo esc_attr( $field_name ); ?>" />
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Type', 'profile-extra-fields' ); ?></th>
						<td>
							<select id="prflxtrflds-select-type" name="prflxtrflds_type">
								<?php foreach ( $prflxtrflds_field_type_id as $id => $field_name ) { /** Create select with field types */ ?>
									<option value="<?php echo esc_attr( $id ); ?>" style="<?php echo 12 === $id ? 'background-color: #dcd6b8;' : ''; ?>" <?php echo 12 === $id ? 'disabled="disabled"' : ''; ?> <?php selected( $field_type_id, $id ); ?>><?php echo esc_html( $field_name ); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr class="prflxtrflds-maxlength">
						<th><?php esc_html_e( 'Max Length', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="number" min="1" name="prflxtrflds_maxlength" value="<?php echo ! empty( $field_maxlength ) ? intval( $field_maxlength ) : 255; ?>" />
							<div class="bws_info"><?php esc_html_e( 'Specify field max length (for text field and textarea type) or max number (for number field type).', 'profile-extra-fields' ); ?></div>
						</td>
					</tr>
					<tr class="prflxtrflds-cols">
						<th><?php esc_html_e( 'Field width in characters', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="number" min="1" name="prflxtrflds_cols" value="<?php echo ! empty( $field_cols ) ? intval( $field_cols ) : 50; ?>" />
						</td>
					</tr>
					<tr class="prflxtrflds-rows">
						<th><?php esc_html_e( 'The height of the field in the text lines', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="number" min="1" name="prflxtrflds_rows" value="<?php echo ! empty( $field_rows ) ? intval( $field_rows ) : 2; ?>" />
						</td>
					</tr>
					<tr class="prflxtrflds-date-format">
						<th scope="row"><?php esc_html_e( 'Date Format', 'profile-extra-fields' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php esc_html_e( 'Date Format', 'profile-extra-fields' ); ?></span></legend>
								<?php
								$date_formats = array_unique( apply_filters( 'date_formats', array( 'F j, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y' ) ) );
								$custom       = true;
								foreach ( $date_formats as $format ) {
									echo "\t<label title='" . esc_attr( $format ) . "'><input type='radio' name='prflxtrflds_date_format' value='" . esc_attr( $format ) . "'";
									if ( $field_date_format === $format ) {
										echo " checked='checked'";
										$custom = false;
									}
									echo ' /> ' . esc_html( date_i18n( $format ) ) . "</label><br />\n";
								}
								echo '	<label><input type="radio" name="prflxtrflds_date_format" id="prflxtrflds_date_format_custom_radio" value="custom"';
								checked( $custom );
								echo '/> ' . esc_html__( 'Custom:', 'profile-extra-fields' ) . '<span class="screen-reader-text"> ' . esc_html__( 'enter a custom date format in the following field', 'profile-extra-fields' ) . "</span></label>\n";
								echo '<label for="prflxtrflds_date_format_custom" class="screen-reader-text">' . esc_html__( 'Custom date format:', 'profile-extra-fields' ) . '</label><input type="text" name="prflxtrflds_date_format_custom" id="prflxtrflds_date_format_custom" value="' . esc_attr( $field_date_format ) . '" class="small-text" />
								<span class="screen-reader-text">' . esc_html__( 'example:', 'profile-extra-fields' ) . ' </span><span class="example"> ' . esc_html( date_i18n( $field_date_format ) ) . "</span> <span class='spinner'></span>\n";
								?>
								<p><a target="_blank" href="https://codex.wordpress.org/Formatting_Date_and_Time"><?php esc_html_e( 'Documentation on date and time formatting.', 'profile-extra-fields' ); ?></a></p>
							</fieldset>
						</td>
					</tr>
					<tr class="prflxtrflds-time-format">
						<th scope="row"><?php esc_html_e( 'Time Format', 'profile-extra-fields' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php esc_html_e( 'Time Format', 'profile-extra-fields' ); ?></span></legend>
								<?php
								$time_formats = array_unique( apply_filters( 'time_formats', array( 'g:i a', 'g:i A', 'H:i' ) ) );
								$custom       = true;
								foreach ( $time_formats as $format ) {
									echo "\t<label title='" . esc_attr( $format ) . "'><input type='radio' name='prflxtrflds_time_format' value='" . esc_attr( $format ) . "'";
									if ( $field_time_format === $format ) {
										echo " checked='checked'";
										$custom = false;
									}
									echo ' /> ' . esc_html( date_i18n( $format ) ) . "</label><br />\n";
								}
								echo '	<label><input type="radio" name="prflxtrflds_time_format" id="prflxtrflds_time_format_custom_radio" value="custom"';
								checked( $custom );
								echo '/> ' . esc_html__( 'Custom:', 'profile-extra-fields' ) . '<span class="screen-reader-text"> ' . esc_html__( 'enter a custom time format in the following field', 'profile-extra-fields' ) . "</span></label>\n";
								echo '<label for="prflxtrflds_time_format_custom" class="screen-reader-text">' . esc_html__( 'Custom time format:', 'profile-extra-fields' ) . '</label><input type="text" name="prflxtrflds_time_format_custom" id="prflxtrflds_time_format_custom" value="' . esc_attr( $field_time_format ) . '" class="small-text" /> <span class="screen-reader-text">' . esc_html__( 'example:', 'profile-extra-fields' ) . ' </span><span class="example"> ' . esc_html( date_i18n( $field_time_format ) ) . "</span> <span class='spinner'></span>\n";
								?>
								<p><a target="_blank" href="https://codex.wordpress.org/Formatting_Date_and_Time"><?php esc_html_e( 'Documentation on date and time formatting.', 'profile-extra-fields' ); ?></a></p>
							</fieldset>
						</td>
					</tr>
					<tr class="prflxtrflds-pattern">
						<th><?php esc_html_e( 'Pattern', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="text" name="prflxtrflds_pattern" value="<?php echo esc_attr( $field_pattern ); ?>" />
							<div class="bws_info"><?php printf( esc_html__( 'Please specify a mask which will be used for the phone validation, where * is a number. Use only the following symbols %1$s', 'profile-extra-fields' ), '* - ( ) +' ); ?></div>
						</td>
					</tr>
					<tr class="prflxtrflds-fields-container">
						<th><?php esc_html_e( 'Available Values', 'profile-extra-fields' ); ?></th>
						<td>
							<fieldset>
								<label><input type="radio" class="bws_option_affect" name="mode_edit_values" value="manually" data-affect-hide=".prflxtrflds-import-values" data-affect-show=".prflxtrflds-write-in-values" checked>&nbsp<?php esc_html_e( 'Edit values manually', 'profile-extra-fields' ); ?></label><br/>
								<div class="prflxtrflds-write-in-values">
									<div class="bws_info hide-if-js">
										<div class="prflxtrflds-value-name">
											<?php esc_html_e( 'Name of value', 'profile-extra-fields' ); ?>
										</div>
										<div class="prflxtrflds-delete">
											<?php esc_html_e( 'Delete', 'profile-extra-fields' ); ?>
										</div>
									</div><!--.prflxtrflds-values-info-->
									<div class="prflxtrflds-drag-values-container">
										<?php for ( $i = 0; $i < count( $available_values ); $i++ ) { ?>
											<div class="prflxtrflds-drag-values">
												<input type="hidden" name="prflxtrflds_value_id[]" value="
												<?php
												if ( ! empty( $available_values[ $i ]['value_id'] ) ) {
													echo esc_attr( $available_values[ $i ]['value_id'] );}
												?>
												" />
												<img class="prflxtrflds-drag-field hide-if-no-js prflxtrflds-hide-if-is-mobile" title="" src="<?php echo esc_url( plugins_url( 'images/dragging-arrow.png', __FILE__ ) ); ?>" alt="drag-arrow" />
												<input placeholder="<?php esc_html_e( 'Name of value', 'profile-extra-fields' ); ?>" class="prflxtrflds-add-options-input" type="text" name="prflxtrflds_available_values[]" value="<?php echo esc_attr( $available_values[ $i ]['value_name'] ); ?>" />
												<span class="prflxtrflds-value-delete"><input type="checkbox" name="prflxtrflds-value-delete[]" value="
												<?php
												if ( ! empty( $available_values[ $i ]['value_id'] ) ) {
													echo esc_attr( $available_values[ $i ]['value_id'] );}
												?>
												" /><label></label></span>
											</div><!--.prflxtrflds-drag-values-->
										<?php } ?>
										<div class="prflxtrflds-drag-values 
										<?php
										if ( ! empty( $available_values ) ) {
											echo 'hide-if-js';}
										?>
										">
											<input type="hidden" name="prflxtrflds_value_id[]" value="" />
											<img class="prflxtrflds-drag-field hide-if-no-js prflxtrflds-hide-if-is-mobile" title="" src="<?php echo esc_url( plugins_url( 'images/dragging-arrow.png', __FILE__ ) ); ?>" alt="drag-arrow" />
											<input placeholder="<?php esc_html_e( 'Name of value', 'profile-extra-fields' ); ?>" class="prflxtrflds-add-options-input" type="text" name="prflxtrflds_available_values[]" value="" />
											<span class="prflxtrflds-value-delete"><input type="checkbox" name="prflxtrflds-value-delete[]" value="" /><label></label></span>
										</div><!--.prflxtrflds-drag-values-->
									</div><!--.prflxtrflds-drag-values-container-->
									<div class="prflxtrflds-add-button-container">
										<input type="button" class="button-small button prflxtrflds-small-button hide-if-no-js" id="prflxtrflds-add-field" name="prflxtrflds-add-field" value="<?php esc_html_e( 'Add', 'profile-extra-fields' ); ?>" />
										<p class="hide-if-js"><?php esc_html_e( 'Click save button to add more values', 'profile-extra-fields' ); ?></p>
									</div>
								</div><br/>
							</fieldset>
							<?php if ( ! $bws_hide_premium_options_check ) { ?>
								<div class="bws_pro_version_bloc">
									<div class="bws_pro_version_table_bloc">
										<div class="bws_table_bg"></div>
										<table class="form-table bws_pro_version">
											<tbody>
												<tr>
													<td>
														<label><input type="radio" name="mode_edit_values" value="import" data-affect-hide=".prflxtrflds-write-in-values" disabled="disabled">&nbsp<?php esc_html_e( 'Import values', 'profile-extra-fields' ); ?><?php echo ( $bws_hide_premium_options_check ? 'disabled' : '' ); ?></label><br/>
														<div>
															<input type="file" name="prflxtrflds-import-file" accept=".xlsx"  disabled="disabled" />	
															<div class="bws_info"><?php esc_html_e( 'Upload XLSX file that includes the values to overwrite the standard values. Example: location1,location2,location3', 'profile-extra-fields' ); ?></div>		
														</div>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
									<div class="bws_pro_version_tooltip">
										<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/profile-extra-fields/?k=c37eed44c2fe607f3400914345cbdca&pn=300&v=<?php echo esc_attr( $prflxtrflds_plugin_info['Version'] ); ?>&wp_v=<?php echo esc_attr( $wp_version ); ?>" target="_blank" title="Profile Extra Fields Pro"><?php esc_html_e( 'Upgrade to Pro', 'profile-extra-fields' ); ?></a>
										<div class="clear"></div>
									</div>
								</div>
							<?php } ?>
						</td>
					</tr>
				</tbody>
			</table>
			<?php if ( ! $bws_hide_premium_options_check ) { ?>
				<div class="bws_pro_version_bloc prflxtrflds-selected-extensions">
					<div class="bws_pro_version_table_bloc">
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tbody>
								<tr class="prflxtrflds-selected-extensions">
									<th><?php esc_html_e( 'Available Extensions', 'profile-extra-fields' ); ?></th>
									<td>
										<select class="select" id="prflxtrflds_available_extensions" disabled="disabled" multiple>
											<?php
											$all_available_extensions = array( '.jpg', '.jpeg', '.png', '.gif', '.ico', '.pdf', '.doc', '.docx', '.ppt', '.pptx', '.pps', '.ppsx', '.odt', '.xls', '.xlsx', '.psd', '.mp3', '.m4a', '.ogg', '.wav', '.mp4', '.m4v', '.mov', '.wmv', '.avi', '.mpg', '.ogv', '.3gp', '.3g2' );
											foreach ( $all_available_extensions as $value ) {
												echo '<option value="' . esc_attr( $value ) . '">' . esc_attr( $value ) . '</option>';
											}
											?>
										</select>
									</td>
								</tr>
							</tbody>
						</table>	
					</div>
					<div class="bws_pro_version_tooltip">
						<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/profile-extra-fields/?k=c37eed44c2fe607f3400914345cbdc8a&pn=300&v=<?php echo esc_attr( $prflxtrflds_plugin_info['Version'] ); ?>&wp_v=<?php echo esc_attr( $wp_version ); ?>" target="_blank" title="Profile Extra Fields Pro"><?php esc_html_e( 'Upgrade to Pro', 'profile-extra-fields' ); ?></a>
						<div class="clear"></div>
					</div>
				</div>
			<?php } ?>
			<table class="form-table prflxtrflds-fields-edit-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( 'Description', 'profile-extra-fields' ); ?></th>
						<td>
							<textarea class="prflxtrflds-description" name="prflxtrflds_description"><?php echo esc_textarea( $description ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th class="prflxtrflds-hide-on-mobile"><?php esc_html_e( 'Field Properties', 'profile-extra-fields' ); ?></th>
						<td>
							<table>
								<tr id="prflxtrflds-caption-checkboxes">
									<th class="prflxtrflds-hide-on-mobile"><?php esc_html_e( 'Available', 'profile-extra-fields' ); ?></th>
									<th class="prflxtrflds-hide-on-mobile"><?php esc_html_e( 'Editable', 'profile-extra-fields' ); ?></th>
									<th class="prflxtrflds-hide-on-mobile"><?php esc_html_e( 'Visible', 'profile-extra-fields' ); ?></th>
								</tr>
								<tr id="prflxtrflds-select-checkboxes">
									<th class="prflxtrflds-labels-for-mobiles"><?php esc_html_e( 'Available', 'profile-extra-fields' ); ?></th>
									<td class="prflxtrflds-checkboxes-for-roles">
										<div id="prflxtrflds-select-roles">
											<div class="prflxtrflds-div-select-all">
												<input class="prflxtrflds-checkboxes-select-all-in-roles prflxtrflds-checkboxes-available" type="checkbox" id="prflxtrflds-select-all"/>
												<span class="prflxtrflds-labels-for-mobiles"><b><?php esc_html_e( 'Select all', 'profile-extra-fields' ); ?></b></span>
											</div>
											<?php
											$args = array();
											foreach ( $all_roles as $role ) {
												$args[ $role->role_id ] = isset( $checked_roles ) ? array_key_exists( $role->role_id, $checked_roles ) : false;
												?>
												<input type="checkbox" class="prflxtrflds-checkboxes-in-roles prflxtrflds-checkboxes-available" name="prflxtrflds_roles[]" value="<?php echo esc_attr( $role->role_id ); ?>"
												<?php
												if ( isset( $checked_roles ) ) {
													checked( array_key_exists( $role->role_id, $checked_roles ), true );
												}
												?>
												data-prflxtrflds-role-id="<?php echo esc_attr( $role->role_id ); ?>"/>
												<span class="prflxtrflds-labels-for-mobiles"><?php echo esc_attr( translate_user_role( $role->role_name ) ); ?></span>
												<br />
											<?php } ?>
										</div>
									</td>
									<th class="prflxtrflds-labels-for-mobiles"><?php esc_html_e( 'Editable', 'profile-extra-fields' ); ?></th>
									<td class="prflxtrflds-checkboxes-for-roles">
										<div id="prflxtrflds-select-roles-editable">
											<div class="prflxtrflds-div-select-all-editable">
												<?php
												foreach ( $all_roles as $role ) {
													$attr = $args[ $role->role_id ] ? ( isset( $checked_roles[ $role->role_id ] ) ? '' : 'disabled="disabled"' ) : 'disabled="disabled"';
												}
												?>
												<input class="prflxtrflds-checkboxes-select-all-in-roles prflxtrflds-checkboxes-editable" type="checkbox" name="prflxtrflds-select-all-editable" id="prflxtrflds-select-all-editable" data-prflxtrflds-role-id="all" <?php echo wp_kses_data( $attr ); ?> />
												<span class="prflxtrflds-labels-for-mobiles"><b><?php esc_html_e( 'Select all', 'profile-extra-fields' ); ?></b></span>
											</div>
											<?php
											foreach ( $all_roles as $role ) {
												$attr = $args[ $role->role_id ] ? ( isset( $checked_roles[ $role->role_id ] ) ? checked( $checked_roles[ $role->role_id ]['editable'], 1, false ) : '' ) : 'disabled="disabled"';
												?>
												<input class="prflxtrflds-checkboxes-in-roles prflxtrflds-checkboxes-editable" type="checkbox" name="prflxtrflds_editable[]" value="<?php echo esc_attr( $role->role_id ); ?>" <?php echo wp_kses_data( $attr ); ?> data-prflxtrflds-role-id="<?php echo esc_attr( $role->role_id ); ?>"/>
												<span class="prflxtrflds-labels-for-mobiles"><?php echo esc_attr( translate_user_role( $role->role_name ) ); ?></span>
												<br />
											<?php } ?>
										</div>
									</td>
									<th class="prflxtrflds-labels-for-mobiles"><?php esc_html_e( 'Visible', 'profile-extra-fields' ); ?></th>
									<td class="prflxtrflds-checkboxes-for-roles">
										<div id="prflxtrflds-select-roles-visibility">
											<div class="prflxtrflds-div-select-all-visibility">
												<?php
												foreach ( $all_roles as $role ) {
													$attr = ( $args[ $role->role_id ] ) ? ( isset( $checked_roles[ $role->role_id ] ) ? '' : 'disabled="disabled"' ) : 'disabled="disabled"';
												}
												?>
												<input class="prflxtrflds-checkboxes-select-all-in-roles prflxtrflds-checkboxes-visible" type="checkbox" name="prflxtrflds-select-all-visibility" id="prflxtrflds-select-all-visibility" data-prflxtrflds-role-id="all" <?php echo wp_kses_data( $attr ); ?> />
												<span class="prflxtrflds-labels-for-mobiles"><b><?php esc_html_e( 'Select all', 'profile-extra-fields' ); ?></b></span>
											</div>
											<?php
											foreach ( $all_roles as $role ) {
												$attr = $args[ $role->role_id ] ? ( isset( $checked_roles[ $role->role_id ] ) ? checked( $checked_roles[ $role->role_id ]['visible'], 1, false ) : '' ) : 'disabled="disabled"';
												?>
												<input class="prflxtrflds-checkboxes-in-roles prflxtrflds-checkboxes-visible" type="checkbox" name="prflxtrflds_visibility[]" value="<?php echo esc_attr( $role->role_id ); ?>"<?php echo wp_kses_data( $attr ); ?> data-prflxtrflds-role-id="<?php echo esc_attr( $role->role_id ); ?>" />
												<span class="prflxtrflds-labels-for-mobiles"><?php echo esc_attr( translate_user_role( $role->role_name ) ); ?></span>
												<br />
											<?php } ?>
										</div>
									</td>
									<td class="prflxtrflds-checkboxes-for-roles prflxtrflds-hide-on-mobile">
										<fieldset id="prflxtrflds-select-roles"><!--#prflxtrflds-select-roles-->
											<?php if ( $all_roles ) { ?>
												<div id="prflxtrflds-div-select-all">
													<label for="prflxtrflds-select-all"><b><?php esc_html_e( 'Select all', 'profile-extra-fields' ); ?></b></label>
												</div><!--#prflxtrflds-div-select-all-->
												<input type="hidden" name="prflxtrflds_roles[]" id="0" value="0" />
												<?php foreach ( $all_roles as $role ) { ?>
													<label for="<?php echo esc_attr( $role->role_id ); ?>"><?php echo esc_attr( translate_user_role( $role->role_name ) ); ?></label>
													<br />
													<?php
												}
											}
											?>
										</fieldset><!--#prflxtrflds-select-roles-->
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Required', 'profile-extra-fields' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="prflxtrflds-show-in-required" name="prflxtrflds_required" value="1" 
								<?php
								if ( ! empty( $field_required ) ) {
									echo 'checked="checked"';}
								?>
								/>
								<span class="bws_info"><?php esc_html_e( 'Enable to make this field required.', 'profile-extra-fields' ); ?></span>
							</label>
						</td>
					</tr>
					<tr class="prflxtrflds-show-in-required">
						<th><?php esc_html_e( 'Required Symbol', 'profile-extra-fields' ); ?></th>
						<td>
							<label>
								<input type="text" id="prflxtrflds-show-in-required-symbol" name="prflxtrflds_required_symbol" value="<?php echo ! empty( $field_required ) ? esc_attr( $field_required ) : '*'; ?>" maxlength="100" />
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Show by Default in User Data', 'profile-extra-fields' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="prflxtrflds-show-default" name="prflxtrflds_show_default" value="1" 
								<?php
								if ( isset( $field_show_default ) ) {
									checked( $field_show_default, '1' );}
								?>
								/>
								<span class="bws_info"><?php esc_html_e( 'Show this field by default in User Data. You can change it using Screen Options tab.', 'profile-extra-fields' ); ?></span>
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Always Show in User Data', 'profile-extra-fields' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="prflxtrflds-show-always" name="prflxtrflds_show_always" value="1" 
								<?php
								if ( isset( $field_show_always ) ) {
									checked( $field_show_always, '1' );}
								?>
								/>
								<span class="bws_info"><?php esc_html_e( 'Show this field in User Data on any display. You can change it using Screen Options tab.', 'profile-extra-fields' ); ?></span>
							</label>
						</td>
					</tr>
					<?php if ( ! is_multisite() ) { ?>
						<tr>
							<th><?php esc_html_e( 'Always Show in User Registration Form', 'profile-extra-fields' ); ?></th>
							<td>
								<label>
									<input type="checkbox" id="prflxtrflds-show-in-register-form" name="prflxtrflds_show_in[register_form]" value="1" 
									<?php
									if ( isset( $show_in['register_form'] ) ) {
										checked( $show_in['register_form'], '1' );}
									?>
									/>
									<span class="bws_info"><?php esc_html_e( 'Show this field in user registration form.', 'profile-extra-fields' ); ?></span>
								</label>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php if ( ! $bws_hide_premium_options_check ) { ?>
				<div class="bws_pro_version_bloc prflxtrflds-fields-edit-table">
					<div class="bws_pro_version_table_bloc">
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr>
								<th scope="row"><label for="prflxtrflds_show_woocommerce"><?php esc_html_e( 'WooСommerce', 'profile-extra-fields' ); ?></label></th>
								<td>
									<label>
										<input disabled="disabled" type="checkbox" id="prflxtrflds-show-woocommerce" name="prflxtrflds_show_woocommerce" value="1" />
										<span class="bws_info"><?php esc_html_e( 'Enable to display this field for WooCommerce.', 'profile-extra-fields' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th ><label for="prflxtrflds_checkout_woocommerce"><?php esc_html_e( 'WooСommerce Сheckout Form', 'profile-extra-fields' ); ?></label></th>
								<td>
									<label>
										<input disabled="disabled" type="checkbox" id="prflxtrflds-checkout-woocommerce" name="prflxtrflds_checkout_woocommerce" value="1" />
										<span class="bws_info"><?php esc_html_e( 'Enable to display this field for WooCommerce Сheckout Form.', 'profile-extra-fields' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="prflxtrflds_registration_woocommerce"><?php esc_html_e( 'WooСommerce Registration Form', 'profile-extra-fields' ); ?></label></th>
								<td>
									<label>
										<input disabled="disabled" type="checkbox" id="prflxtrflds-registration-woocommerce" name="prflxtrflds_registration_woocommerce" value="1" />
										<span class="bws_info"><?php esc_html_e( 'Enable to display this field for WooCommerce Registration Form.', 'profile-extra-fields' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="prflxtrflds_show_subscriber">Subscriber</label></th>
								<td>
									<label>
										<input disabled="disabled" type="checkbox" id="prflxtrflds-show-subscriber" name="prflxtrflds_show_subscriber" value="1" />
										<span class="bws_info"><?php esc_html_e( 'Enable to display this field for Subscriber form.', 'profile-extra-fields' ); ?></span>
									</label>
								</td>
							</tr>
						</table>
					</div>
					<div class="bws_pro_version_tooltip">
						<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/profile-extra-fields/?k=c37eed44c2fe607f3400914345cbdc8a&pn=300&v=<?php echo esc_attr( $prflxtrflds_plugin_info['Version'] ); ?>&wp_v=<?php echo esc_attr( $wp_version ); ?>" target="_blank" title="Profile Extra Fields Pro"><?php esc_html_e( 'Upgrade to Pro', 'profile-extra-fields' ); ?></a>
						<div class="clear"></div>
					</div>
				</div>
				<?php
			}
			$plugins_data = apply_filters( 'bws_bkng_prflxtrflds_get_data', $plugins_data = array() );
			?>
			<table class="form-table prflxtrflds-fields-edit-table">
				<?php foreach ( $plugins_data as $plugin ) { ?>
					<tr>
						<th><?php echo esc_html( $plugin['name'] ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="prflxtrflds-show-in-<?php echo esc_html( $plugin['slug'] ); ?>" name="prflxtrflds_show_in[<?php echo esc_html( $plugin['slug'] ); ?>]" value="1" 
								<?php
								if ( isset( $show_in[ $plugin['slug'] ] ) && ( 1 === intval( $show_in[ $plugin['slug'] ] ) || is_array( $show_in[ $plugin['slug'] ] ) ) ) {
									echo ' checked="checked"';}
								?>
								/>
								<span class="bws_info"><?php printf( esc_html__( 'Enable to display this field for %1$s.', 'profile-extra-fields' ), esc_html( $plugin['name'] ) ); ?></span>
							</label>
						</td>
					</tr>
					<?php if ( isset( $plugin['show_in'] ) ) { ?>
						<?php foreach ( $plugin['show_in'] as $name => $slug ) { ?>
							<tr class="prflxtrflds-show-in-<?php echo esc_html( $plugin['slug'] ); ?>">
								<th><?php echo esc_html( $name ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="prflxtrflds_show_in[<?php echo esc_html( $plugin['slug'] ); ?>][<?php echo esc_html( $slug ); ?>]" value="1" 
											<?php
											if ( isset( $show_in[ $plugin['slug'] ][ $slug ] ) ) {
												checked( $show_in[ $plugin['slug'] ][ $slug ], '1' );}
											?>
										/>
										<span class="bws_info"><?php printf( esc_html__( 'Enable to display this field for %1$s.', 'profile-extra-fields' ), esc_attr( $name ) ); ?></span>
									</label>
								</td>
							</tr>
						<?php } ?>
					<?php } ?>
					<?php
				}
				$all_plugins = get_plugins();
				$bws_plugins = array(
					'bws-car-rental-pro/bws-car-rental-pro.php' => array(
						'name' => 'Car Rental V2',
						'slug' => 'bws-car-rental-pro',
						'link' => 'https://bestwebsoft.com/products/wordpress/plugins/car-rental-v2/?k=9c60bc19fcec6712a46bdc0afb464784&pn=300&v=' . $prflxtrflds_plugin_info['Version'] . '&wp_v=' . $wp_version,
					),
				);

				if ( ! empty( $plugins_data ) ) {
					foreach ( $bws_plugins as $key => $plugin ) {
						if ( preg_grep( '/.*' . $plugin['slug'] . '.*$/', array_keys( $plugins_data ) ) ) {
							unset( $bws_plugins[ $key ] );
						}
					}
				}

				if ( ! empty( $bws_plugins ) ) {
					foreach ( $bws_plugins as $path => $plugin ) {
						if ( array_key_exists( $path, $all_plugins ) ) {
							$button_link = self_admin_url( 'plugins.php' );
							$button_text = __( 'Activate', 'profile-extra-fields' );
						} else {
							$button_link = $plugin['link'];
							$button_text = __( 'Download', 'profile-extra-fields' );
						}
						?>

						<tr>
							<th><?php echo esc_attr( $plugin['name'] ); ?></th>
							<td>
								<label>
									<input disabled="disabled" type="checkbox" value="1" />
									<span class="bws_info"><?php printf( esc_html__( 'Enable to display this field for %1$s.', 'profile-extra-fields' ), esc_html( $plugin['name'] ) ); ?>
										<?php printf( '<a href="%1$s" target="_blank">%2$s %3$s</a>', esc_url( $button_link ), esc_html( $button_text ), esc_html( $plugin['name'] ) ); ?>
									</span>
								</label>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</table>
			<table class="form-table prflxtrflds-fields-edit-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( 'Field Order', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="number" min="0" max="999" name="prflxtrflds_order" value="<?php echo ( isset( $field_order ) ) ? esc_attr( $field_order ) : '0'; ?>" />
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit prflxtrflds-fields-edit-table">
				<input type="hidden" name="prflxtrflds_save_field" value="true" />
				<input type="hidden" name="prflxtrflds_field_id" value="<?php echo esc_attr( $field_id ); ?>" />
				<input id="bws-submit-button" type="submit" class="button-primary" name="prflxtrflds_save_settings" value="<?php esc_html_e( 'Save Changes', 'profile-extra-fields' ); ?>" />
				<?php wp_nonce_field( 'prflxtrflds_nonce_name' ); ?>
			</p>
		</form>
		</div>
		<?php
	}
}

/** Screen option. Settings for display where items per page show in wp list table */
if ( ! function_exists( 'prflxtrflds_screen_options' ) ) {
	function prflxtrflds_screen_options() {
		$screen = get_current_screen();
		$args   = array(
			'id'      => 'prflxtrflds',
			'section' => '201146449',
		);
		bws_help_tab( $screen, $args );

		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Fields per page', 'profile-extra-fields' ),
			'default' => 20,
			'option'  => 'fields_per_page',
		);
		add_screen_option( $option, $args );

		if ( isset( $_GET['tab-action'] ) && 'userdata' === sanitize_text_field( wp_unslash( $_GET['tab-action'] ) ) ) {
			global $prflxtrflds_userdatalist_table;
			if ( ! isset( $prflxtrflds_userdatalist_table ) ) {
				$prflxtrflds_userdatalist_table = new Srrlxtrflds_Userdata_List();
			}
		} elseif ( isset( $_GET['tab-action'] ) && 'shortcode' === sanitize_text_field( wp_unslash( $_GET['tab-action'] ) ) ) {
			global $prflxtrflds_shortcodelist_table;
			if ( ! isset( $prflxtrflds_shortcodelist_table ) ) {
				$prflxtrflds_shortcodelist_table = new Srrlxtrflds_Shortcode_List();
			}
		} else {
			global $prflxtrflds_fields_list_table;
			if ( ! isset( $prflxtrflds_fields_list_table ) ) {
				$prflxtrflds_fields_list_table = new Srrlxtrflds_Fields_List();
			}
		}
	}
}

if ( ! function_exists( 'prflxtrflds_set_screen_options' ) ) {
	function prflxtrflds_set_screen_options( $status, $option, $value ) {
		if ( ! empty( $option ) && 'fields_per_page' === $option ) {
			return $value;
		}
		return $value;
	}
}

if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
	/** Create new class to displaying fields */
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	}

	if ( ! class_exists( 'Srrlxtrflds_Fields_List' ) ) {
		class Srrlxtrflds_Fields_List extends WP_List_Table {

			public function display( $display_nav = true ) {
				$singular = $this->_args['singular'];

				if ( $display_nav ) {
					$this->display_tablenav( 'top' );
				}

				$this->screen->render_screen_reader_content( 'heading_list' );
				?>
				<table class="wp-list-table <?php echo esc_html( implode( ' ', $this->get_table_classes() ) ); ?>">
					<thead>
					<tr>
						<?php $this->print_column_headers(); ?>
					</tr>
					</thead>

					<tbody id="the-list"
						<?php
						if ( $singular ) {
							echo " data-wp-lists='list:" . esc_attr( $singular ) . "'";
						}
						?>
						>
						<?php $this->display_rows_or_placeholder(); ?>
					</tbody>

					<tfoot>
					<tr>
						<?php $this->print_column_headers( false ); ?>
					</tr>
					</tfoot>

				</table>
				<?php
				if ( $display_nav ) {
					$this->display_tablenav( 'bottom' );
				}
			}

			public function get_columns() {
				$columns = array(
					'cb'           => '<input type="checkbox" />',
					'field_name'   => __( 'Name', 'profile-extra-fields' ),
					'description'  => __( 'Description', 'profile-extra-fields' ),
					'field_type'   => __( 'Type', 'profile-extra-fields' ),
					'required'     => __( 'Required', 'profile-extra-fields' ),
					'show_default' => __( 'Show by Default', 'profile-extra-fields' ),
					'show_always'  => __( 'Show Always', 'profile-extra-fields' ),
					'roles'        => __( 'Roles', 'profile-extra-fields' ),
					'field_order'  => __( 'Field Order', 'profile-extra-fields' ),
				);
				return $columns;
			}

			public function get_sortable_columns() {
				/** Seting sortable collumns */
				$sortable_columns = array(
					'field_name'  => array( 'field_name', true ),
					'field_order' => array( 'field_order', true ),
					'field_type'  => array( 'field_type_id', true ),
					'required'    => array( 'required', true ),
				);
				return $sortable_columns;
			}

			public function get_bulk_actions() {
				/** Adding bulk action */
				$actions = array(
					'delete_fields' => __( 'Delete Permanently', 'profile-extra-fields' ),
				);
				return $actions;
			}

			/** Override this function to delete nonce from options */
			public function display_tablenav( $which ) {
				?>
				<div class="tablenav <?php echo esc_attr( $which ); ?>">
					<div class="alignleft actions bulkactions">
						<?php $this->bulk_actions( $which ); ?>
					</div>
					<?php
					$this->extra_tablenav( $which );
					$this->pagination( $which );
					?>
					<br class="clear" />
				</div>
				<?php
			}

			/** Bulk actions handler */
			public function process_bulk_action() {
				/** Get action */
				$action = $this->current_action();
				/** Action = delete fields */
				switch ( $action ) {
					case 'delete_fields':
						/** Security check */
						if ( isset( $_GET['prflxtrflds_field_id'] ) &&
							isset( $_GET['prflxtrflds_nonce_name'] ) &&
							! empty( $_GET['prflxtrflds_nonce_name'] )
						) {
							$nonce = filter_input( INPUT_GET, 'prflxtrflds_nonce_name', FILTER_SANITIZE_STRING );
							if ( wp_verify_nonce( $nonce, 'prflxtrflds_nonce_name' ) ) {
								if ( isset( $_GET['prflxtrflds_field_id'] ) ) {
									foreach ( $_GET['prflxtrflds_field_id'] as $id ) {
										/** Delete all checked fields */
										prflxtrflds_remove_field( intval( $id ) );
									}
								}
							}
						}
						break;
					default:
						/** Do nothing */
						break;
				}
			}

			public function get_views() {
				/** Show links at the columns of table */
				global $wpdb;
				$views   = array();
				$current = ( ! empty( $_GET['role_id'] ) ) ? intval( $_GET['role_id'] ) : 'all';

				/** All link */
				$all_url      = esc_html( add_query_arg( 'role_id', 'all' ) );
				$class        = ( 'all' === $current ) ? 'class="current"' : '';
				$views['all'] = "<a href='" . $all_url . "' " . $class . ' >' . __( 'All', 'profile-extra-fields' ) . '</a>';

				/** Get actual users data */
				$roles = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->base_prefix . 'prflxtrflds_roles_id' );
				if ( $roles ) {
					foreach ( $roles as $role ) {
						/** Create link */
						$role_url                = esc_html( add_query_arg( 'role_id', $role->role_id ) );
						$class                   = ( $role->role_id === $current ) ? ' class="current"' : '';
						$views[ $role->role_id ] = "<a href='" . esc_url( $role_url ) . "'" . $class . '>' . esc_attr( translate_user_role( $role->role_name ) ) . '</a>';
					}
				}
				return $views;
			}

			public function extra_tablenav( $which ) {
				if ( 'columns' === $which ) {
					global $wpdb;
					$current = ( ! empty( $_GET['prflxtrflds_role_id'] ) ) ? intval( $_GET['prflxtrflds_role_id'] ) : 'all';
					/** Get actual users data */
					$roles = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->base_prefix . 'prflxtrflds_roles_id' );
					?>
					<div class="alignleft prflxtrflds-filter actions bulkactions">
						<label for="prflxtrflds-role-id">
							<select name="prflxtrflds_role_id" id="prflxtrflds-role-id">
								<option value="all" <?php selected( $current, 'all' ); ?>><?php esc_html_e( 'All roles', 'profile-extra-fields' ); ?></option>
								<?php
								if ( ! empty( $roles ) ) {
									/** Create select with field types */
									foreach ( $roles as $role ) {
										?>
										<option value="<?php echo intval( $role->role_id ); ?>"<?php selected( $current, $role->role_id ); ?>><?php echo esc_attr( translate_user_role( $role->role_name ) ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</label>
						<input type="submit" class="button" name="prflxtrflds_apply_filter" value="<?php esc_html_e( 'Filter', 'profile-extra-fields' ); ?>" />
					</div><!--.alignleft prflxtrflds-filter-->
					<?php
				}
			}

			public function column_cb( $item ) {
				/** Customize displaying cb collumn */
				return sprintf(
					'<input type="checkbox" name="prflxtrflds_field_id[]" value="%1$s" />',
					$item['field_id']
				);
			}

			public function column_field_name( $item ) {
				/** Adding action to 'name' collumn */
				$actions = array(
					'edit_fields'   => '<span><a href="' . wp_nonce_url( sprintf( '?page=profile-extra-field-add-new.php&amp;edit=1&amp;prflxtrflds_field_id=%1$s', $item['field_id'] ), 'prflxtrflds_nonce_name' ) . '">' . __( 'Edit Field', 'profile-extra-fields' ) . '</a></span>',
					'delete_fields' => '<span class="trash"><a href="' . wp_nonce_url( sprintf( '?page=profile-extra-fields.php&amp;remove=1&amp;prflxtrflds_field_id=%1$s', $item['field_id'] ), 'prflxtrflds_nonce_name' ) . '">' . __( 'Delete Permanently', 'profile-extra-fields' ) . '</a></span>',
				);
				if ( isset( $_GET['tab-action'] ) ) {
					$actions = array(
						'edit_fields'   => '<span><a href="' . wp_nonce_url( sprintf( '?page=profile-extra-field-add-new.php&tab-action=' . sanitize_text_field( wp_unslash( $_GET['tab-action'] ) ) . '&amp;edit=1&amp;prflxtrflds_field_id=%1$s', $item['field_id'] ), 'prflxtrflds_nonce_name' ) . '">' . __( 'Edit Field', 'profile-extra-fields' ) . '</a></span>',
						'delete_fields' => '<span class="trash"><a href="' . wp_nonce_url( sprintf( '?page=profile-extra-fields.php&amp;remove=1&amp;prflxtrflds_field_id=%1$s', $item['field_id'] ), 'prflxtrflds_nonce_name' ) . '">' . __( 'Delete Permanently', 'profile-extra-fields' ) . '</a></span>',
					);
				}
				return sprintf( '%1$s %2$s', $item['field_name'], $this->row_actions( $actions ) );
			}

			public function column_field_type( $item ) {
				$prflxtrflds_field_type_id = prflxtrflds_get_field_type_id();
				return sprintf(
					'%1$s',
					$prflxtrflds_field_type_id[ $item['field_type_id'] ]
				);
			}

			public function column_required( $item ) {
				return empty( $item['required'] ) ? __( 'No', 'profile-extra-fields' ) : __( 'Yes', 'profile-extra-fields' );
			}
			public function column_show_default( $item ) {
				$is_default = array(
					1 => __( 'Yes', 'profile-extra-fields' ),
					0 => __( 'No', 'profile-extra-fields' ),
				);
				return sprintf(
					'%1$s',
					$is_default[ $item['show_default'] ]
				);
			}
			public function column_show_always( $item ) {
				$is_always = array(
					1 => __( 'Yes', 'profile-extra-fields' ),
					0 => __( 'No', 'profile-extra-fields' ),
				);
				return sprintf(
					'%1$s',
					$is_always[ $item['show_always'] ]
				);
			}

			public function column_roles( $item ) {
				/** Delete last comma */
				return sprintf( '%1$s', chop( $item['roles'], ', ' ) );
			}

			public function prepare_items( $where = '' ) {
				/** Bulk action handler. Before query */
				global $wpdb;
				$this->process_bulk_action();
				$table_roles_meta       = $wpdb->base_prefix . 'prflxtrflds_fields_meta';
				$table_fields_id        = $wpdb->base_prefix . 'prflxtrflds_fields_id';
				$table_roles_id         = $wpdb->base_prefix . 'prflxtrflds_roles_id';
				$table_roles_and_fields = $wpdb->base_prefix . 'prflxtrflds_roles_and_fields';
				/** Order by field id by default. It need for generate fields to display without sorting */
				$rolerequest = 'ORDER BY ' . $table_fields_id . '.`field_id` ASC';
				/** Query if role selected */
				if ( isset( $_GET['prflxtrflds_role_id'] ) &&
					'all' !== $_GET['prflxtrflds_role_id']
				) {
					$selected_role = filter_input( INPUT_GET, 'prflxtrflds_role_id', FILTER_SANITIZE_NUMBER_INT );
					$rolerequest   = 'AND ' . $table_roles_and_fields . ".`role_id`='" . $selected_role . "' ORDER BY " . $table_roles_and_fields . '.`field_order` ASC';
				}
				/** Default WHERE query */
				$searchrequest = '1=1';
				/** Search handler */
				if ( isset( $_GET['s'] ) && '' !== trim( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) ) {
					/** Sanitize search query */
					$searchrequest = filter_input( INPUT_GET, 's', FILTER_SANITIZE_ENCODED );
					$searchrequest = $table_fields_id . ".`field_name` LIKE '%" . $searchrequest . "%'";
				}
				/** Fields for plugins where clause */

				$not = '';
				if ( '' === $where ) {
					$plugins_data = apply_filters( 'bws_bkng_prflxtrflds_get_data', $plugins_data = array() );
					$slugs        = array_column( $plugins_data, 'slug' );
					$where        = implode( "', '", $slugs );
					$not          = 'NOT';
				}

				$where = 'AND ' . $table_fields_id . '.`field_id` ' . $not . ' IN (
					SELECT ' . $table_roles_meta . '.`field_id`
					FROM ' . $table_roles_meta . '
					WHERE ' . $table_roles_meta . '.`show_in` IN ( "' . $where . '" ) AND ' . $table_roles_meta . '.`value` != ""
				)';

				$query = 'SELECT ' . $table_roles_and_fields . '.`field_order`, ' .
						$table_fields_id . '.`field_id`, ' .
						$table_fields_id . '.`field_name`, ' .
						$table_fields_id . '.`description`, ' .
						$table_fields_id . '.`required`, ' .
						$table_fields_id . '.`show_default`, ' .
						$table_fields_id . '.`show_always`, ' .
						$table_roles_id . '.`role_name`, ' .
						$table_roles_and_fields . '.`role_id`, ' .
						$table_fields_id . '.`field_type_id` ' .
						' FROM ' . $table_fields_id .
						' LEFT JOIN ' . $table_roles_and_fields .
						' ON ' . $table_roles_and_fields . '.`field_id`=' . $table_fields_id . '.`field_id`' .
						' LEFT JOIN ' . $table_roles_id .
						' ON ' . $table_roles_id . '.`role_id`=' . $table_roles_and_fields . '.`role_id`' .
						' WHERE ' . $searchrequest . ' ' . $where . ' ' .
						$rolerequest;

				/** Get result from database with repeat id with other role */
				$fields_query_result = $wpdb->get_results( $query, ARRAY_A );
				$i                   = 0;
				$fields_to_display   = array();
				$prev_id             = -1;
				foreach ( $fields_query_result as $one_field ) {
					$id = $one_field['field_id'];
					if ( $prev_id !== $id ) {
						$i++;
						/** If is new id, copy all fields */
						$fields_to_display[ $i ]['field_id']      = $one_field['field_id'];
						$fields_to_display[ $i ]['field_name']    = $one_field['field_name'];
						$fields_to_display[ $i ]['required']      = $one_field['required'];
						$fields_to_display[ $i ]['show_default']  = $one_field['show_default'];
						$fields_to_display[ $i ]['show_always']   = $one_field['show_always'];
						$fields_to_display[ $i ]['description']   = $one_field['description'];
						$fields_to_display[ $i ]['field_type_id'] = $one_field['field_type_id'];
						$fields_to_display[ $i ]['roles']         = esc_attr( translate_user_role( $one_field['role_name'] ) );
						$fields_to_display[ $i ]['field_order']   = $one_field['field_order'];
						$prev_id                                  = $id;
					} else {
						/** If is old id ( new role ), add new role */
						if ( isset( $fields_to_display[ $i ]['roles'] ) ) {
							$fields_to_display[ $i ]['roles'] .= ', ' . esc_attr( translate_user_role( $one_field['role_name'] ) );
						} else {
							$fields_to_display[ $i ]['roles'] = esc_attr( translate_user_role( $one_field['role_name'] ) );
						}
						$prev_id = $id;
					}
				}
				/** Sort function */
				if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
					/** Check permitted names of field */
					switch ( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ) {
						case 'field_name':
						case 'field_type_id':
						case 'required':
						case 'field_order':
							if ( 'desc' === $_GET['order'] ) {
								usort(
									$fields_to_display,
									function ( $first, $second ) {
										return strcmp( $first[ sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ], $second[ sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ] ) * -1;    /** ASC */
									}
								);
							} else {
								/** Sort result array. This use in usort. ASC by default */
								usort(
									$fields_to_display,
									function ( $first, $second ) {
										return strcmp( $first[ sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ], $second[ sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ] ); /** ASC */
									}
								);
							}
							break;
						default:
							break;
					}
				} else {
					/** Default sort by field order */
					usort(
						$fields_to_display,
						function( $first, $second ) {
							/** Permitted names of sort check in switch */
							return strcmp( $first['field_order'], $second['field_order'] ); /** ASC */
						}
					);
				}
				/** Pagination settings */
				/** Get the total fields */
				$totalitems = count( $fields_to_display );
				/** Get the value of number of field on one page */
				$perpage = $this->get_items_per_page( 'fields_per_page', 20 );
				/** The total number of pages */
				$totalpages = ceil( $totalitems / $perpage );
				/** Get current page */
				$current_page = $this->get_pagenum();
				/** Set pagination arguments */
				$this->set_pagination_args(
					array(
						'total_items' => $totalitems,
						'per_page'    => $perpage,
					)
				);
				/** Settings data to output */
				$this->_column_headers = $this->get_column_info();
				/** Slice array */
				$this->items = array_slice( $fields_to_display, ( ( $current_page - 1 ) * $perpage ), $perpage );
			}

			public function column_default( $item, $column_name ) {
				/** Setting default view for column items */
				switch ( $column_name ) {
					case 'field_id':
					case 'field_name':
					case 'description':
					case 'show_default':
					case 'show_always':
					case 'roles':
					case 'field_order':
						return $item[ $column_name ];
					default:
						/** Show array */
						return print_r( $item, true );
				}
			}
		}
	}

	if ( ! class_exists( 'Srrlxtrflds_Userdata_List' ) ) {
		class Srrlxtrflds_Userdata_List extends WP_List_Table {

			public function __construct( $args = array() ) {
				$args = wp_parse_args(
					$args,
					array(
						'plural'   => '',
						'singular' => '',
						'ajax'     => false,
						'screen'   => null,
					)
				);

				$this->screen = convert_to_screen( $args['screen'] );
				/** Change screen id */
				$this->screen->id = $this->screen->id . 'userdata';
				add_filter( "manage_{$this->screen->id}_columns", array( $this, 'get_columns' ), 0 );

				if ( ! $args['plural'] ) {
					$args['plural'] = $this->screen->base;
				}

				$args['plural']   = sanitize_key( $args['plural'] );
				$args['singular'] = sanitize_key( $args['singular'] );

				$this->_args = $args;
				if ( $args['ajax'] ) {
					add_action( 'admin_footer', array( $this, '_js_vars' ) );
				}
			}

			public function get_columns() {
				global $wpdb;
				/** Setup column */
				$columns = array(
					'user_id'   => __( 'User ID', 'profile-extra-fields' ),
					'name'      => __( 'Username', 'profile-extra-fields' ),
					'role'      => __( 'User role', 'profile-extra-fields' ),
					'disp_name' => __( 'Name', 'profile-extra-fields' ),
					'email'     => __( 'Email', 'profile-extra-fields' ),
					'posts'     => __( 'Posts', 'profile-extra-fields' ),
				);

				/** Get all fields from database and set as column */
				$all_fields_array = $wpdb->get_results( 'SELECT `field_id`, `field_name` FROM ' . $wpdb->base_prefix . 'prflxtrflds_fields_id', ARRAY_A );
				$db_columns       = array();
				foreach ( $all_fields_array as $one_field ) {
					/** Convert to 2D array for merge with $columns */
					$db_columns[ (string) $one_field['field_id'] ] = $one_field['field_name'];
				}
				/** Add columns from database to default columns */
				$columns = $columns + $db_columns;

				return $columns;
			}

			/** Override this function to delete nonce from options */
			public function display_tablenav( $which ) {
				?>
				<div class="tablenav <?php echo esc_attr( $which ); ?>">
					<?php
					$this->extra_tablenav( $which );
					$this->pagination( $which );
					?>
					<br class="clear" />
				</div>
				<?php
			}

			public function column_role( $item ) {
				/** Translate user role */
				return sprintf( '%1$s', esc_attr( translate_user_role( ucfirst( $item['role'] ) ) ) );
			}

			public function column_name( $item ) {
				$actions = array(
					'edit_user' => '<span><a href="' . sprintf( 'user-edit.php?user_id=%1$s&amp;wp_http_referer=%2$s', $item['user_id'], rawurlencode( admin_url( 'admin.php?page=profile-extra-fields.php&tab-action=userdata' ) ) ) . '">' . __( 'Edit user', 'profile-extra-fields' ) . '</a></span>',
				);
				return sprintf( '%1$s %2$s', $item['name'] . '<div class="user_id">' . __( 'User ID', 'profile-extra-fields' ) . ': ' . $item['user_id'] . '</div>', $this->row_actions( $actions ) );
			}

			public function get_sortable_columns() {
				/** Seting sortable collumns */
				$sortable_columns = array(
					'name'      => array( 'username', true ),
					'role'      => array( 'role', true ),
					'user_id'   => array( 'ID', true ),
					'disp_name' => array( 'name', true ),
					'email'     => array( 'email', true ),
				);
				return $sortable_columns;
			}

			public function extra_tablenav( $which ) {
				global $wp_version;
				/** Extra tablenav. Create filter. */
				if ( 'top' === $which ) {
					$roles = get_editable_roles();
					?>
					<div class="alignleft prflxtrflds-filter actions bulkactions">
						<label for="prflxtrflds-role">
							<?php if ( $wp_version >= '4.4' ) { ?>
								<select id="prflxtrflds-role" name="prflxtrflds_role[]" multiple="multiple">
									<?php
									if ( isset( $roles ) ) {
										foreach ( $roles as $key => $role ) {
											?>
											<option value="<?php echo esc_attr( $key ); ?>" 
											<?php
											if ( empty( $_GET['prflxtrflds_role'] ) || in_array( $key, $_GET['prflxtrflds_role'] ) ) {
												echo 'selected';
											}
											?>
											><?php echo esc_attr( translate_user_role( $role['name'] ) ); ?></option>
											<?php
										}
									}
									?>
								</select>
								<?php
							} else {
								$current_role = ( ! empty( $_GET['prflxtrflds_role'] ) ) ? sanitize_text_field( wp_unslash( $_GET['prflxtrflds_role'] ) ) : 'all';
								?>
								<select id="prflxtrflds-role" name="prflxtrflds_role">
									<option value="all" <?php selected( $current_role, 'all' ); ?>><?php esc_html_e( 'All roles', 'profile-extra-fields' ); ?></option>
									<?php
									if ( isset( $roles ) ) {
										foreach ( $roles as $key => $role ) {
											?>
											<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $current_role, $key ); ?>><?php echo esc_attr( translate_user_role( $role['name'] ) ); ?></option>
											<?php
										}
									}
									?>
								</select>
							<?php } ?>
						</label>
						<?php wp_nonce_field( 'prflxtrflds_apply_filter_action', 'prflxtrflds_apply_filters' ); ?>
						<input type="submit" class="button" name="prflxtrflds_apply_filter" value="<?php esc_html_e( 'Filter', 'profile-extra-fields' ); ?>" />
					</div><!--.alignleft prflxtrflds-filter-->
					<?php
				}
			}

			public function prepare_items() {
				global $wpdb, $wp_version;
				$userdata = array();
				$i        = 0;
				$search   = isset( $_REQUEST['s'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : '';

				$users_per_page = $this->get_items_per_page( 'fields_per_page', 20 );
				$paged          = $this->get_pagenum();
				$totalitems     = count( get_users() );

				$args = array(
					'number' => $totalitems,
					'fields' => 'all_with_meta',
				);
				if ( isset( $_REQUEST['prflxtrflds_apply_filters'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['prflxtrflds_apply_filters'] ) ), 'prflxtrflds_apply_filter_action' ) ) {
					if ( $wp_version >= '4.4' ) {
						if ( isset( $_REQUEST['prflxtrflds_role'] ) ) {
							$args['role__in'] = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_REQUEST['prflxtrflds_role'] ) );
						}
					} elseif ( isset( $_REQUEST['prflxtrflds_role'] ) && ! empty( $_REQUEST['prflxtrflds_role'] ) && 'all' !== $_REQUEST['prflxtrflds_role'] ) {
						$args['role'] = sanitize_text_field( wp_unslash( $_REQUEST['prflxtrflds_role'] ) );
					}

					if ( isset( $_REQUEST['orderby'] ) ) {
						$args['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
					}

					if ( isset( $_REQUEST['order'] ) ) {
						$args['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
					}
				}

				/** Query the user IDs for this page */
				$wp_user_search = new WP_User_Query( $args );
				$all_users      = $wp_user_search->get_results();
				/** Users post by id */
				$post_counts = count_many_users_posts( array_keys( $all_users ) );

				$table_field_values    = $wpdb->base_prefix . 'prflxtrflds_field_values';
				$table_user_field_data = $wpdb->base_prefix . 'prflxtrflds_user_field_data';
				$table_fields_id       = $wpdb->base_prefix . 'prflxtrflds_fields_id';

				foreach ( $all_users as $user ) {
					$userdata[ $i ]['name']      = $user->user_nicename;
					$userdata[ $i ]['role']      = implode( ', ', $user->roles );
					$userdata[ $i ]['user_id']   = $user->ID;
					$userdata[ $i ]['disp_name'] = $user->first_name . ' ' . $user->last_name;
					$userdata[ $i ]['email']     = $user->user_email;
					$userdata[ $i ]['posts']     = $post_counts[ $user->ID ];

					/** Get fields for current user */
					$filled_fields = $wpdb->get_results(
						'SELECT `' . $table_field_values . '`.`field_id`, `value_name` AS `user_value`
						FROM ' . $table_user_field_data . ', ' . $table_fields_id . ', `' . $table_field_values . '`
							WHERE `' . $table_user_field_data . '`.`user_value` = `' . $table_field_values . "`.`value_id`
								AND `user_id` = '" . $user->ID . "'
								AND `" . $table_field_values . '`.`field_id`= `' . $table_fields_id . '`.`field_id`
								AND `' . $table_user_field_data . '`.`field_id`= `' . $table_fields_id . '`.`field_id`
								AND `' . $table_fields_id . "`.`field_type_id` IN ( '3', '4', '5' )
						UNION
						SELECT `" . $table_user_field_data . '`.`field_id`, `user_value`
							FROM ' . $table_user_field_data . ', ' . $table_fields_id .
							" WHERE `user_id` = '" . $user->ID . "'
								AND `" . $table_user_field_data . '`.`field_id`= `' . $table_fields_id . '`.`field_id`
								AND `' . $table_fields_id . "`.`field_type_id` NOT IN ( '3', '4', '5' )
						",
						ARRAY_A
					);

					if ( ! empty( $filled_fields ) ) {
						foreach ( $filled_fields as $field ) {
							if ( isset( $userdata[ $i ][ $field['field_id'] ] ) ) {
								/** Add value name */
								$userdata[ $i ][ $field['field_id'] ] .= ', ' . wp_unslash( $field['user_value'] );
							} else {
								/** First write value name */
								$userdata[ $i ][ $field['field_id'] ] = wp_unslash( $field['user_value'] );
							}
						}
					}
					$i++;
				}
				/** Array search. If search by user not work */
				if ( ! empty( $search ) && isset( $userdata ) ) {
					$not_empty_keys = array();
					/** Get all columns */
					$hidden_columns = get_user_option( 'manage' . 'bws-panel_page_profile-extra-fieldsuserdata' . 'columnshidden' );
					if ( empty( $hidden_columns ) ) {
						$hidden_columns = array();
					}
					foreach ( $userdata as $key => $oneuserdata ) {
						/** Data for one user */
						foreach ( $oneuserdata as $key_col_id => $one_value ) {
							/** Skip if current column is hidden */
							if ( in_array( $key_col_id, $hidden_columns ) ) {
								continue;
							}
							/** If value in array, save key */
							if ( false !== stristr( $one_value, $search ) ) {
								$not_empty_keys[] = $key;
								break;
							}
						}
					}
					if ( isset( $not_empty_keys ) ) {
						$all_keys = array_keys( $userdata );
						/** Get empty entrys */
						$to_delete = array_diff( $all_keys, $not_empty_keys );
						if ( ! empty( $to_delete ) ) {
							foreach ( $to_delete as $key ) {
								/** Unset empty entrys */
								unset( $userdata[ $key ] );
							}
						}
					}
				}
				/** Order by firstname - lastname */
				if ( isset( $_GET['orderby'] ) && 'name' === $_GET['orderby'] ) {
					if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
						usort(
							$userdata,
							function ( $first, $second ) {
								return strcmp( $first['disp_name'], $second['disp_name'] ) * -1;    /** ASC */
							}
						);
					} else {
						/** Sort result array. This use in usort. ASC by default */
						usort(
							$userdata,
							function ( $first, $second ) {
								return strcmp( $first['disp_name'], $second['disp_name'] ); /** ASC */
							}
						);
					}
				}

				/**
					* Pagination settings
					* Get the total fields
					* The total number of pages
					*/
				$totalpages = ceil( $totalitems / $users_per_page );
				/** Get current page */
				$current_page = $this->get_pagenum();
				/** Set pagination arguments */

				$this->set_pagination_args(
					array(
						'total_items' => $totalitems,
						'total_pages' => $totalpages,
						'per_page'    => $users_per_page,
					)
				);

				/** Get info from screen options */
				$columns               = $this->get_columns();
				$hidden                = get_user_option( 'manage' . 'bws-panel_page_profile-extra-fieldsuserdata' . 'columnshidden' );
				$sortable              = $this->get_sortable_columns();
				$primary               = 'name';
				$this->_column_headers = $this->get_column_info();

				$this->items = array_slice( $userdata, ( ( $current_page - 1 ) * $users_per_page ), $users_per_page );
			}

			public function column_default( $item, $column_name ) {
				/** Setting default view for column items */
				switch ( $column_name ) {
					case 'name':
					case 'role':
					case 'user_id':
					case 'disp_name':
					case 'email':
					case 'posts':
						return $item[ $column_name ];
					default:
						/** Show array */
						if ( isset( $item[ $column_name ] ) ) {
							return $item[ $column_name ];
						} else {
							/** Default message */
							return '';
						}
				}
			}
		}
	}

	if ( ! class_exists( 'Srrlxtrflds_Shortcode_List' ) ) {
		class Srrlxtrflds_Shortcode_List extends WP_List_Table {

			public function __construct( $args = array() ) {
				$args = wp_parse_args(
					$args,
					array(
						'plural'   => '',
						'singular' => '',
						'ajax'     => false,
						'screen'   => null,
					)
				);

				$this->screen = convert_to_screen( $args['screen'] );
				/** Change screen id */
				$this->screen->id = $this->screen->id . 'shortcode';
				add_filter( "manage_{$this->screen->id}_columns", array( $this, 'get_columns' ), 0 );

				if ( ! $args['plural'] ) {
					$args['plural'] = $this->screen->base;
				}

				$args['plural']   = sanitize_key( $args['plural'] );
				$args['singular'] = sanitize_key( $args['singular'] );

				$this->_args = $args;
				if ( $args['ajax'] ) {
					add_action( 'admin_footer', array( $this, '_js_vars' ) );
				}
			}

			public function get_columns() {
				/** Setup column */
				return array(
					'field_name'  => __( 'Field Name', 'profile-extra-fields' ),
					'description' => __( 'Description', 'profile-extra-fields' ),
					'show'        => __( 'Show This Field', 'profile-extra-fields' ),
					'selected'    => __( 'Show Only If the Next Value is Selected', 'profile-extra-fields' ),
				);
			}

			public function column_show( $item ) {
				global $prflxtrflds_options;

				if ( is_array( $prflxtrflds_options['available_fields'] ) ) {
					$prflxtrflds_checked = checked( in_array( $item['field_id'], $prflxtrflds_options['available_fields'] ), 1, false );
				} else {
					$prflxtrflds_checked = '';
				}
				return sprintf( '<input type="checkbox" class="prflxtrflds-available-fields" name="prflxtrflds_options_available_fields[%1$d]" value="%1$d" %2$s /><input class="hidden" name="prflxtrflds_options_available_fields_hidden[%1$d]">', $item['field_id'], $prflxtrflds_checked );
			}

			public function column_selected( $item ) {
				global $prflxtrflds_options;
				/** If field have more 1 values, print select */
				if ( ! empty( $item['available_values'] ) ) {
					$prflxtrflds_option_list = '';
					foreach ( $item['available_values'] as $value ) {
						if ( is_array( $prflxtrflds_options['available_values'] ) ) {
							$value_selected = selected( in_array( $value['value_id'], $prflxtrflds_options['available_values'] ), 1, false );
						} else {
							$value_selected = '';
						}
						$prflxtrflds_option_list .= "<option value='" . $value['value_id'] . "' " . $value_selected . '>' . $value['value_name'] . '</option>';

					}
					return sprintf(
						'<select class="prflxtrflds-wplist-select" name="prflxtrflds_options_available_values[%1$s]">
					<option value="">%2$s</option>
					%3$s
					</select>',
						$item['field_id'],
						__( 'Show despite the value', 'profile-extra-fields' ),
						$prflxtrflds_option_list
					);
				} else {
					return '';
				}
			}

			/** Override this function to set nonce from options */
			public function display_tablenav( $which ) {
				if ( 'top' === $which ) {
					wp_nonce_field( 'update-options' );
				}
				?>
				<div class="tablenav <?php echo esc_attr( $which ); ?>">
					<div class="alignleft actions bulkactions"><?php $this->bulk_actions( $which ); ?></div>
					<?php
					$this->extra_tablenav( $which );
					$this->pagination( $which );
					?>
					<br class="clear" />
				</div>
				<?php
			}

			public function prepare_items() {
				global $wpdb;

				$get_fields_list_sql = 'SELECT `field_name`, `field_id`, `description`, `field_type_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`';
				/** Get the total number of items */
				$totalitems = $wpdb->query( $get_fields_list_sql );
				/** Get the value of number of items on one page */
				$perpage = $this->get_items_per_page( 'fields_per_page', 20 );
				/** The total number of pages */
				$totalpages   = ceil( $totalitems / $perpage );
				$current_page = $this->get_pagenum();
				/** Set pagination arguments */
				$this->set_pagination_args(
					array(
						'total_items'     => $totalitems,
						'total_pages'     => $totalpages,
						'fields_per_page' => $perpage,
					)
				);

				$available_fields = $wpdb->get_results( $get_fields_list_sql, ARRAY_A );
				if ( 0 < sizeof( $available_fields ) ) {
					/** Add available values to array with available fields */
					foreach ( $available_fields as &$field ) {
						if ( '3' === $field['field_type_id'] ||
							'4' === $field['field_type_id'] ||
							'5' === $field['field_type_id']
						) {
							$field['available_values'] = $wpdb->get_results( $wpdb->prepare( 'SELECT `value_id`, `value_name` FROM ' . $wpdb->base_prefix . 'prflxtrflds_field_values WHERE `field_id`=%d', $field['field_id'] ), ARRAY_A );
						}
					}
					unset( $field );
				}

				$columns                 = $this->get_columns();
				$prflxtrflds_user_option = get_user_option( 'manage' . 'bws-panel_page_profile-extra-fieldsshortcode' . 'columnshidden' );
				$hidden                  = ! empty( $prflxtrflds_user_option ) ? $prflxtrflds_user_option : array();
				$sortable                = array();
				$primary                 = $this->get_primary_column_name();
				$this->_column_headers   = array( $columns, $hidden, $sortable, $primary );
				$this->items             = array_slice( $available_fields, ( ( $current_page - 1 ) * $perpage ), $perpage );
			}

			public function column_default( $item, $column_name ) {
				/** Setting default view for column items */
				switch ( $column_name ) {
					case 'field_name':
					case 'description':
					case 'show':
					case 'selected':
						return $item[ $column_name ];
					default:
						/** Show array */
						return print_r( $item, true );
				}
			}
		}
	}
}

/** Remove info about field from database */
if ( ! function_exists( 'prflxtrflds_remove_field' ) ) {
	function prflxtrflds_remove_field( $field_id ) {
		global $wpdb;
		$wpdb->delete(
			$wpdb->base_prefix . 'prflxtrflds_fields_id',
			array(
				'field_id' => $field_id,
			)
		);
		$wpdb->delete(
			$wpdb->base_prefix . 'prflxtrflds_fields_meta',
			array(
				'field_id' => $field_id,
			)
		);
		$wpdb->delete(
			$wpdb->base_prefix . 'prflxtrflds_roles_and_fields',
			array(
				'field_id' => $field_id,
			)
		);
		$wpdb->delete(
			$wpdb->base_prefix . 'prflxtrflds_field_values',
			array(
				'field_id' => $field_id,
			)
		);
		$wpdb->delete(
			$wpdb->base_prefix . 'prflxtrflds_user_field_data',
			array(
				'field_id' => $field_id,
			)
		);
	}
}

/** Settings page */
if ( ! function_exists( 'prflxtrflds_settings_page' ) ) {
	function prflxtrflds_settings_page() {
		if ( ! class_exists( 'Bws_Settings_Tabs' ) ) {
			require_once dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php';
		}
		require_once dirname( __FILE__ ) . '/includes/class-prflxtrflds-settings.php';
		$page = new Prflxtrflds_Settings_Tabs( plugin_basename( __FILE__ ) );
		if ( method_exists( $page, 'add_request_feature' ) ) {
			$page->add_request_feature();
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Profile Extra Fields Settings', 'profile-extra-fields' ); ?></h1>
			<?php $page->display_content(); ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'prflxtrflds_fields' ) ) {
	function prflxtrflds_fields() {
		global $wpdb, $prflxtrflds_options, $prflxtrflds_plugin_info, $wp_version;
		$message         = $error = $notice = '';
		$plugin_basename = plugin_basename( __FILE__ );
		/** Remove slug */
		if ( isset( $_GET['remove'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'prflxtrflds_nonce_name' ) ) {
			if ( isset( $_GET['prflxtrflds_field_id'] ) ) {
				$field_id = filter_input( INPUT_GET, 'prflxtrflds_field_id', FILTER_SANITIZE_STRING );
				prflxtrflds_remove_field( $field_id );
			}
		}

		/** Get all available fields and print it */
		$available_fields = $wpdb->get_results( 'SELECT `field_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_id` LIMIT 1;', ARRAY_A );

		if ( isset( $_GET['tab-action'] ) && 'shortcode' === $_GET['tab-action'] ) {
			if ( 0 < count( $available_fields ) ) {
				if ( isset( $_REQUEST['prflxtrflds_form_submit'] ) &&
					check_admin_referer( $plugin_basename, 'prflxtrflds_nonce_name' )
				) {
					$prflxtrflds_options['empty_value']           = isset( $_POST['prflxtrflds_empty_value'] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_empty_value'] ) ) : '';
					$prflxtrflds_options['not_available_message'] = isset( $_POST['prflxtrflds_not_available_message'] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_not_available_message'] ) ) : '';
					$prflxtrflds_options['sort_sequence']         = isset( $_POST['prflxtrflds_sort_sequence'] ) && in_array( $_POST['prflxtrflds_sort_sequence'], array( 'ASC', 'DESC' ) ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_sort_sequence'] ) ) : 'ASC';
					$prflxtrflds_options['show_empty_columns']    = isset( $_POST['prflxtrflds_show_empty_columns'] ) ? 1 : 0;
					$prflxtrflds_options['show_id']               = isset( $_POST['prflxtrflds_show_id'] ) ? 1 : 0;
					$prflxtrflds_options['header_table']          = isset( $_POST['prflxtrflds_header_table'] ) && in_array( $_POST['prflxtrflds_header_table'], array( 'columns', 'rows' ) ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_header_table'] ) ) : 'columns';
					$prflxtrflds_options['available_values']      = ! empty( $_POST['prflxtrflds_options_available_values'] ) ? array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST['prflxtrflds_options_available_values'] ) ) : array();
					$prflxtrflds_options['shortcode_debug']       = isset( $_POST['prflxtrflds_shortcode_debug'] ) ? 1 : 0;
					$prflxtrflds_options['display_user_name']     = isset( $_POST['prflxtrflds_display_user_name'] ) && in_array( $_POST['prflxtrflds_display_user_name'], array( 'username', 'publicly_name' ) ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_display_user_name'] ) ) : 'username';

					$prflxtrflds_options['available_fields'] = ! empty( $_POST['prflxtrflds_options_available_fields'] ) ? array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST['prflxtrflds_options_available_fields'] ) ) : array();

					update_option( 'prflxtrflds_options', $prflxtrflds_options );
					$message = __( 'Settings saved', 'profile-extra-fields' );
				}

				if ( isset( $_REQUEST['bws_restore_confirm'] ) &&
					check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' )
				) {
					$prflxtrflds_options = prflxtrflds_get_options_default();
					update_option( 'prflxtrflds_options', $prflxtrflds_options );
					$message = __( 'All plugin settings were restored.', 'profile-extra-fields' );
				}
			}
		}
		$bws_hide_premium_options_check = bws_hide_premium_options_check( $prflxtrflds_options );
		/** GO PRO */
		if ( isset( $_GET['tab-action'] ) && 'go_pro' === $_GET['tab-action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename, 'prflxtrflds_options' );
			if ( ! empty( $go_pro_result['error'] ) ) {
				$error = $go_pro_result['error'];
			} elseif ( ! empty( $go_pro_result['message'] ) ) {
				$message = $go_pro_result['message'];
			}
		}
		?>
		<div class="wrap">
			<h1>
				Profile Extra Fields
				<a href="admin.php?page=profile-extra-field-add-new.php" class="page-title-action add-new-h2" ><?php esc_html_e( 'Add New', 'profile-extra-fields' ); ?></a>
			</h1>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab
				<?php
				if ( ! isset( $_GET['tab-action'] ) ) {
					echo esc_html( ' nav-tab-active' );
				}
				?>
				" href="admin.php?page=profile-extra-fields.php"><?php esc_html_e( 'Extra Fields', 'profile-extra-fields' ); ?></a>
				<?php if ( ! $bws_hide_premium_options_check ) { ?>
					<a id="prflxtrflds-pro-options" class="nav-tab 
					<?php
					if ( isset( $_GET['tab-action'] ) && 'woocommerce' === $_GET['tab-action'] ) {
						echo esc_html( ' nav-tab-active' );
					}
					?>
					" href="admin.php?page=profile-extra-fields.php&amp;tab-action=woocommerce"><?php esc_html_e( 'WooСommerce', 'profile-extra-fields' ); ?></a>
					<a id="prflxtrflds-pro-options" class="nav-tab 
					<?php
					if ( isset( $_GET['tab-action'] ) && 'subscriber' === $_GET['tab-action'] ) {
						echo esc_html( ' nav-tab-active' );
					}
					?>
					" href="admin.php?page=profile-extra-fields.php&amp;tab-action=subscriber">Subscriber</a>
				<?php } ?>
				<a class="nav-tab 
				<?php
				if ( isset( $_GET['tab-action'] ) && 'booking' === $_GET['tab-action'] ) {
					echo esc_html( ' nav-tab-active' );
				}
				?>
				" href="admin.php?page=profile-extra-fields.php&amp;tab-action=booking"><?php esc_html_e( 'Booking', 'profile-extra-fields' ); ?></a>
				<a class="nav-tab 
				<?php
				if ( isset( $_GET['tab-action'] ) && 'userdata' === $_GET['tab-action'] ) {
					echo esc_html( ' nav-tab-active' );
				}
				?>
				" href="admin.php?page=profile-extra-fields.php&amp;tab-action=userdata"><?php esc_html_e( 'User Data', 'profile-extra-fields' ); ?></a>
				<?php if ( 0 < count( $available_fields ) ) { ?>
					<a class="nav-tab 
					<?php
					if ( isset( $_GET['tab-action'] ) && 'shortcode' === $_GET['tab-action'] ) {
						echo esc_html( ' nav-tab-active' );
					}
					?>
					" href="admin.php?page=profile-extra-fields.php&amp;tab-action=shortcode"><?php esc_html_e( 'Shortcode Settings', 'profile-extra-fields' ); ?></a>
				<?php } ?>
			</h2>
			<?php if ( ! isset( $_GET['tab-action'] ) ) { ?>
				<div class="prflxtrflds-wplisttable-fullwidth-sort-container">
					<?php
					$prflxtrflds_fields_list_table = new Srrlxtrflds_Fields_List(); /** Wp list table to show all fields */
					$prflxtrflds_fields_list_table->prepare_items();
					if ( isset( $prflxtrflds_fields_list_table->items ) && ( 1 < count( $prflxtrflds_fields_list_table->items ) ) ) { /** Show drag-n-drop message if items > 2 */
						?>
						<p class="hide-if-no-js prflxtrflds-hide-if-is-mobile">
							<?php esc_html_e( 'Drag each item into the order you would like to display it on the user page', 'profile-extra-fields' ); ?>
						</p>
					<?php } ?>
					<form class="prflxtrflds-wplisttable-searchform" method="get" action="<?php get_admin_url(); ?>?page=profile-extra-fields.php">
						<input type="hidden" name="page" value="profile-extra-fields.php" />
						<?php
						wp_nonce_field( 'prflxtrflds_nonce_name', 'prflxtrflds_nonce_name', false );
						$prflxtrflds_fields_list_table->search_box( __( 'Search', 'profile-extra-fields' ), 'search_id' );
						?>
						<?php $prflxtrflds_fields_list_table->display(); ?>
					</form>
				</div><!-- .prflxtrflds-wplisttable-container -->
				<?php
			} elseif ( isset( $_GET['tab-action'] ) && ( 'woocommerce' === $_GET['tab-action'] || 'subscriber' === $_GET['tab-action'] ) ) {
				if ( ! $bws_hide_premium_options_check ) {
					?>
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<div class="prflxtrflds-wplisttable-fullwidth-sort-container">
									<?php
									$prflxtrflds_fields_list_table = new Srrlxtrflds_Fields_List(); /** Wp list table to show all fields */
									?>
									<form class="prflxtrflds-wplisttable-searchform" method="get" action="<?php get_admin_url(); ?>?page=profile-extra-fields.php&tab-action=woocommerce">
										<?php
										wp_nonce_field( 'prflxtrflds_nonce_name', 'prflxtrflds_nonce_name', false );
										$prflxtrflds_fields_list_table->search_box( __( 'Search', 'profile-extra-fields' ), 'search_id' );
										?>
										<?php $prflxtrflds_fields_list_table->display(); ?>
									</form>
								</div><!-- .prflxtrflds-wplisttable-container -->
							</table>
						</div>
						<div class="bws_pro_version_tooltip">
							<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/profile-extra-fields/?k=23e9c49f512f7a6d0900c5a1503ded4f&pn=91&v=<?php echo esc_attr( $prflxtrflds_plugin_info['Version'] ); ?>&wp_v=<?php echo esc_attr( $wp_version ); ?>" target="_blank" title="Profile Extra Fields Pro"><?php esc_html_e( 'Upgrade to Pro', 'profile-extra-fields' ); ?></a>
							<div class="clear"></div>
						</div>
					</div>
				<?php } ?>
				<?php
			} elseif ( isset( $_GET['tab-action'] ) && 'booking' === $_GET['tab-action'] ) {
				$plugins_data = apply_filters( 'bws_bkng_prflxtrflds_get_data', $plugins_data = array() );
				if ( ! empty( $plugins_data ) ) {
					?>
					<div class="prflxtrflds-wplisttable-fullwidth-sort-container">
						<?php
						$prflxtrflds_fields_list_table = new Srrlxtrflds_Fields_List(); /** Wp list table to show all fields */

						/** Show drag-n-drop message if items > 2 */
						if ( isset( $prflxtrflds_fields_list_table->items ) && ( 1 < count( $prflxtrflds_fields_list_table->items ) ) ) {
							?>
							<p class="hide-if-no-js prflxtrflds-hide-if-is-mobile">
								<?php esc_html_e( 'Drag each item into the order you would like to display it on the user page', 'profile-extra-fields' ); ?>
							</p>
						<?php } ?>
						<form class="prflxtrflds-wplisttable-searchform" method="get" action="<?php get_admin_url(); ?>?page=profile-extra-fields.php&tab-action=booking">
							<input type="hidden" name="page" value="profile-extra-fields.php" />
							<?php
							wp_nonce_field( 'prflxtrflds_nonce_name', 'prflxtrflds_nonce_name', false );
							$prflxtrflds_fields_list_table->search_box( __( 'Search', 'profile-extra-fields' ), 'search_id' );
							$prflxtrflds_fields_list_table->display_tablenav( 'top' );
							$table_displayed = false;
							foreach ( $plugins_data as $plugin ) {
								$prflxtrflds_fields_list_table->prepare_items( $plugin['slug'] );
								if ( ! empty( $prflxtrflds_fields_list_table->items ) ) {
									$table_displayed = true;
									?>
									<h2 class="hide-if-js"><?php echo esc_html( $plugin['name'] ); ?></h2> 
									<?php
									$prflxtrflds_fields_list_table->display( false );
									?>
									<input type="hidden" class="prflxtrflds-tables-name" value="<?php echo esc_attr( $plugin['name'] ); ?>">
									<?php
								}
							}
							if ( ! $table_displayed ) {
								$prflxtrflds_fields_list_table->display( false );
							}
							$prflxtrflds_fields_list_table->display_tablenav( 'bottom' );
							?>
						</form>
					</div><!-- .prflxtrflds-wplisttable-container -->
					<?php
				}
				$all_plugins = get_plugins();
				$bws_plugins = array(
					'bws-car-rental-pro/bws-car-rental-pro.php' => array(
						'name' => 'Car Rental V2',
						'slug' => 'bws-car-rental-pro',
						'link' => 'https://bestwebsoft.com/products/wordpress/plugins/car-rental-v2/?k=9c60bc19fcec6712a46bdc0afb464784&pn=300&v=' . $prflxtrflds_plugin_info['Version'] . '&wp_v=' . $wp_version,
					),
				);

				if ( ! empty( $plugins_data ) ) {
					foreach ( $bws_plugins as $key => $plugin ) {
						if ( preg_grep( '/.*' . $plugin['slug'] . '.*$/', array_keys( $plugins_data ) ) ) {
							unset( $bws_plugins[ $key ] );
						}
					}
				}

				if ( ! empty( $bws_plugins ) ) {
					foreach ( $bws_plugins as $path => $plugin ) {
						if ( array_key_exists( $path, $all_plugins ) ) {
							$message     = sprintf( __( 'Activate %1$s to display fields for %2$s.', 'profile-extra-fields' ), $plugin['name'], $plugin['name'] );
							$button_link = self_admin_url( 'plugins.php' );
							$button_text = __( 'Activate', 'profile-extra-fields' );
						} else {
							$message     = sprintf( __( 'Install %1$s to display fields for %2$s.', 'profile-extra-fields' ), $plugin['name'], $plugin['name'] );
							$button_link = $plugin['link'];
							$button_text = __( 'Download', 'profile-extra-fields' );
						}
						?>

						<tr>
							<td>
								<br>
								<span class="bws_info"><?php echo esc_attr( $message ); ?>
									<?php printf( '<a href="%1$s" target="_blank">%2$s %3$s</a>', esc_url( $button_link ), esc_html( $button_text ), esc_html( $plugin['name'] ) ); ?>
								</span>
								<br>
							</td>
						</tr>
					<?php } ?>
					<br>
				<?php } ?>
				<?php
			} elseif ( isset( $_GET['tab-action'] ) && 'userdata' === $_GET['tab-action'] ) {
				global $prflxtrflds_userdatalist_table, $prflxtrflds_plugin_info, $wp_version;
				if ( ! isset( $prflxtrflds_userdatalist_table ) ) {
					$prflxtrflds_userdatalist_table = new Srrlxtrflds_Userdata_List();
				}
				$bws_hide_premium_options_check = bws_hide_premium_options_check( $prflxtrflds_options );
				$prflxtrflds_userdatalist_table->prepare_items();
				?>
				<div class="prflxtrflds-wplisttable-fullwidth-container">
					<form class="bws_form" method="post" action="" enctype="multipart/form-data">
						<?php if ( ! $bws_hide_premium_options_check ) : ?>
							<div class="bws_pro_version_bloc">
								<div class="bws_pro_version_table_bloc">
									<button type="submit" name="bws_hide_premium_options"
										class="notice-dismiss bws_hide_premium_options"
										title="<?php esc_html_e( 'Close', 'profile-extra-fields' ); ?>"></button>
									<div class="bws_table_bg" style="top: 0;"></div>
									<table class="form-table prflxtrflds-export-table">
										<tr>
											<th><?php esc_html_e( 'Import Data', 'profile-extra-fields-pro' ); ?></th>
											<td>
												<label>
													<input type="file" name="prflxtrflds-import-file" accept=".csv">
												</label><br/>
												<input type="submit" name="prflxtrflds_import_submit" class="button" value="<?php esc_html_e( 'Import', 'profile-extra-fields' ); ?>" />
											</td>
										</tr>
									</table>
								</div>
								<div class="bws_pro_version_tooltip">
									<a class="bws_button" href="https://bestwebsoft.com/products/wordpress/plugins/profile-extra-fields/?k=c37eed44c2fe607f3400914345cbdc8a&pn=300&v=<?php echo esc_attr( $prflxtrflds_plugin_info['Version'] ); ?>&amp;wp_v=<?php echo esc_attr( $wp_version ); ?>" target="_blank" title="Profile Extra Fields Pro"><?php esc_html_e( 'Upgrade to Pro', 'profile-extra-fields' ); ?></a>
									<div class="clear"></div>
								</div>
							</div>
						<?php endif; ?>
						<table class="form-table prflxtrflds-export-table">
							<tr>
								<th><?php esc_html_e( 'Export Data', 'profile-extra-fields' ); ?></th>
								<td>
									<?php esc_html_e( 'Data layout', 'profile-extra-fields' ); ?>:
									<select name="prflxtrflds_format_export" >
										<option value="columns"<?php selected( $prflxtrflds_options['header_table'], 'columns' ); ?>><?php esc_html_e( 'Columns', 'profile-extra-fields' ); ?></option>
										<option value="rows"<?php selected( $prflxtrflds_options['header_table'], 'rows' ); ?>><?php esc_html_e( 'Rows', 'profile-extra-fields' ); ?></option>
									</select><br/>
									<input type="submit" name="prflxtrflds_export_submit" class="button" value="<?php esc_html_e( 'Export', 'profile-extra-fields' ); ?>" />
									<div class="bws_info"><?php esc_html_e( 'Data export does not depend on the selected filters for displaying data in the table below', 'profile-extra-fields' ); ?></div>
								</td>
							</tr>
						</table>
					</form>
					<form method="get" class="prflxtrflds-wplisttable-form">
						<input type="hidden" name="page" value="profile-extra-fields.php" />
						<input type="hidden" name="tab-action" value="userdata" />
						<?php if ( ! empty( $_GET['role'] ) ) { ?>
							<input type="hidden" name="role" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['role'] ) ) ); ?>" />
							<?php
						}
						$prflxtrflds_userdatalist_table->search_box( __( 'Search', 'profile-extra-fields' ), 'search_id' );
						$prflxtrflds_userdatalist_table->display();
						?>
					</form>
				</div>
				<?php
			} elseif ( isset( $_GET['tab-action'] ) && 'shortcode' === $_GET['tab-action'] && 0 < count( $available_fields ) ) {
				bws_show_settings_notice();
				if ( ! empty( $message ) ) {
					?>
					<div class="updated fade below-h2"><p><?php echo esc_html( $message ); ?></p></div>
				<?php } ?>
				<br/>
				<form class="bws_form" method="post" action="">
					<table class="form-table">
						<tbody>
							<tr>
								<th><?php esc_html_e( 'Message for Empty Field', 'profile-extra-fields' ); ?></th>
								<td>
									<input type="text" name="prflxtrflds_empty_value" value="<?php echo esc_textarea( $prflxtrflds_options['empty_value'] ); ?>" />
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Message for the Field Unavaliable for the User', 'profile-extra-fields' ); ?></th>
								<td>
									<input type="text" name="prflxtrflds_not_available_message" value="<?php echo esc_textarea( $prflxtrflds_options['not_available_message'] ); ?>" />
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Sort by User Name', 'profile-extra-fields' ); ?></th>
								<td>
									<select name="prflxtrflds_sort_sequence" >
										<option value="ASC" <?php selected( $prflxtrflds_options['sort_sequence'], 'ASC' ); ?>><?php esc_html_e( 'ASC', 'profile-extra-fields' ); ?></option>
										<option value="DESC" <?php selected( $prflxtrflds_options['sort_sequence'], 'DESC' ); ?>><?php esc_html_e( 'DESC', 'profile-extra-fields' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Display Name', 'profile-extra-fields' ); ?></th>
								<td>
									<fieldset>
										<label>
											<input type="radio" name="prflxtrflds_display_user_name" value="username" <?php checked( 'username' === $prflxtrflds_options['display_user_name'] ? 1 : 0 ); ?> /><?php esc_html_e( 'Username', 'profile-extra-fields' ); ?>
										</label><br>
										<label>
											<input type="radio" name="prflxtrflds_display_user_name" value="publicly_name" <?php checked( 'publicly_name' === $prflxtrflds_options['display_user_name'] ? 1 : 0 ); ?> ><?php esc_html_e( 'Public Name', 'profile-extra-fields' ); ?>
										</label>
									</fieldset>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Show Empty Fields', 'profile-extra-fields' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="prflxtrflds_show_empty_columns" value="1" <?php checked( $prflxtrflds_options['show_empty_columns'] ); ?> />
										<span class="bws_info"><?php esc_html_e( 'Enable to show the field if the value is not filled by a user.', 'profile-extra-fields' ); ?></span>
									</label>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Show User ID', 'profile-extra-fields' ); ?></th>
								<td>
									<input type="checkbox" name="prflxtrflds_show_id" value="1" <?php checked( $prflxtrflds_options['show_id'] ); ?> />
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Data Rotation', 'profile-extra-fields' ); ?></th>
								<td>
									<select name="prflxtrflds_header_table" >
										<option value="columns"<?php selected( $prflxtrflds_options['header_table'], 'columns' ); ?>><?php esc_html_e( 'Columns', 'profile-extra-fields' ); ?></option>
										<option value="rows"<?php selected( $prflxtrflds_options['header_table'], 'rows' ); ?>><?php esc_html_e( 'Rows', 'profile-extra-fields' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Debug Mode', 'profile-extra-fields' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="prflxtrflds_shortcode_debug" value="1" <?php checked( $prflxtrflds_options['shortcode_debug'] ); ?> />
										<span class="bws_info"><?php esc_html_e( 'Enable to display error messages when shortcode formation is failed (i.e. there are no users for the selected roles).', 'profile-extra-fields' ); ?></span>
									</label>
								</td>
							</tr>
						</tbody>
					</table><!--.form-table-->
					<div class="prflxtrflds-wplisttable-container">
						<?php
						$prflxtrflds_shortcodelist_table = new Srrlxtrflds_Shortcode_List();
						/** Wp lis table for shortcode settings */
						$prflxtrflds_shortcodelist_table->prepare_items();
						$prflxtrflds_shortcodelist_table->views();
						$prflxtrflds_shortcodelist_table->display();
						?>
					</div><!--.prflxtrflds-wplisttable-container-->
					<p class="submit">
						<input type="hidden" name="prflxtrflds_form_submit" value="submit" />
						<?php wp_nonce_field( $plugin_basename, 'prflxtrflds_nonce_name' ); ?>
						<input id="bws-submit-button" type="submit" class="button-primary" name="prflxtrflds_save_changes" value="<?php esc_html_e( 'Save Changes', 'profile-extra-fields' ); ?>" />
					</p>
				</form>
				<?php } ?>
		</div><!--.wrap-->
		<?php
	}
}

/** Print shortcode */
if ( ! function_exists( 'prflxtrflds_show_data' ) ) {
	function prflxtrflds_show_data( $param ) {
		global $wpdb, $prflxtrflds_options;
		$error_message = '';
		$user_ids      = $field_id = array();
		$export_action = ( isset( $param['export'] ) && true === $param['export'] ) ? true : false;

		if ( ! isset( $prflxtrflds_options ) ) {
			prflxtrflds_settings();
		}

		extract(
			shortcode_atts(
				array(
					'user_id'   => '',
					'user_role' => '',
					'display'   => '',
					'field_id'  => '',
				),
				$param
			)
		);

		/** Get user id param */
		if ( ! empty( $param['user_id'] ) ) {
			if ( 'get_current_user' === $param['user_id'] ) {
				$user_ids = array( get_current_user_id() );
			} else {
				$user_ids = explode( ',', $param['user_id'] );
				if ( is_array( $user_ids ) ) {
					/** If lot user ids */
					foreach ( $user_ids as $user_id ) {
						/** Check for existing user */
						if ( ! is_numeric( $user_id ) || ! get_user_by( 'id', intval( $user_id ) ) ) {
							/** Show error if user id not exist, or data is uncorrect */
							$error_message = sprintf( __( 'User with entered id(id=%1$s) does not exist!', 'profile-extra-fields' ), esc_attr( $user_id ) );
						}
					}
				}
			}
		}
		/** Get user role param */
		if ( ! empty( $param['user_role'] ) ) {
			$user_roles = explode( ',', $param['user_role'] );
			if ( is_array( $user_roles ) ) {
				foreach ( $user_roles as $role ) {
					/** Check for exist user role */
					$role_id = $wpdb->get_var( $wpdb->prepare( 'SELECT `role_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_id` WHERE `role` = %s', $role ) );
					if ( ! empty( $role_id ) ) {
						/** Get user ids by role */
						$ids_for_role = $wpdb->get_col( $wpdb->prepare( 'SELECT `user_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_user_roles` WHERE `role_id`=%d', $role_id ) );
						if ( ! empty( $ids_for_role ) ) {
							$user_ids = array_merge( $user_ids, $ids_for_role );
						}
					}
				}
				/** If not exist users for choisen role. User ids is empty and select all users */
				if ( empty( $user_ids ) ) {
					$error_message = sprintf( __( 'There are no users for the selected roles ( %1$s )', 'profile-extra-fields' ), esc_attr( $param['user_role'] ) );
				}
			}
		}
		/** Get display options */
		if ( ! empty( $param['display'] ) ) {
			/** If this values is not supported */
			if ( ! in_array( $param['display'], array( 'left', 'top', 'right', 'side', 'columns', 'rows' ) ) ) {
				$error_message .= sprintf( __( 'Unsupported shortcode option(display=%1$s)', 'profile-extra-fields' ), esc_attr( $param['display'] ) );
			} else {
				$display = $param['display'];
			}
		} else {
			/** If value not in shortcode, get from options. Top by default */
			$display = isset( $prflxtrflds_options['header_table'] ) ? $prflxtrflds_options['header_table'] : 'columns';
		}
		if ( ! empty( $error_message ) ) {
			if ( ! empty( $prflxtrflds_options['shortcode_debug'] ) ) {
				return sprintf( '<p>%1$s. %2$s</p>', __( 'Shortcode output error', 'profile-extra-fields' ), $error_message );
			} else {
				return '';
			}
		} else {
			$wp_users               = $wpdb->base_prefix . 'users';
			$wp_usermeta            = $wpdb->base_prefix . 'usermeta';
			$table_fields_id        = $wpdb->base_prefix . 'prflxtrflds_fields_id';
			$table_user_field_data  = $wpdb->base_prefix . 'prflxtrflds_user_field_data';
			$table_field_values     = $wpdb->base_prefix . 'prflxtrflds_field_values';
			$table_roles_id         = $wpdb->base_prefix . 'prflxtrflds_roles_id';
			$table_roles_and_fields = $wpdb->base_prefix . 'prflxtrflds_roles_and_fields';
			$table_user_roles       = $wpdb->base_prefix . 'prflxtrflds_user_roles';

			/** Collate all users ids */
			$get_for_selected_users = '';
			if ( ! empty( $user_ids ) ) {
				$get_for_selected_users = ' AND `' . $table_user_roles . "`.`user_id` IN ( '" . implode( "', '", $user_ids ) . "' )";
			}

			/** Get options - Which fields must be displayed */
			$get_for_available_fields = '';
			if ( $export_action ) {
				$fields_sql               = 'SELECT `field_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`';
				$totalitems               = $wpdb->get_col( $fields_sql );
				$field_ids                = implode( "', '", $totalitems );
				$get_for_available_fields = ' AND `' . $table_fields_id . "`.`field_id` IN ('" . $field_ids . "')";
			} else {
				if ( ! empty( $prflxtrflds_options['available_fields'] ) ) {
					$field_ids                = implode( "', '", $prflxtrflds_options['available_fields'] );
					$get_for_available_fields = ' AND `' . $table_fields_id . "`.`field_id` IN ('" . $field_ids . "')";
				}
			}

			$get_for_available_field_value = '';
			if ( ! empty( $prflxtrflds_options['available_values'] ) ) {
				$i              = 0;
				$extended_value = '';
				foreach ( $prflxtrflds_options['available_values'] as $value => $key ) {
					if ( '' !== $key ) {
						if ( 0 !== $i ) {
							$extended_value .= ' OR ';
						}

						$extended_value .= "(`user_value`='" . $key . "' AND `field_id`='" . $value . "')";
						$i++;
					}
				}
				if ( '' !== $extended_value ) {
					$get_for_available_field_value = ' AND `' . $table_user_roles . '`.`user_id` IN
						(SELECT `user_id`
							FROM `' . $table_user_field_data . '`
							WHERE ' . $extended_value . ')';
				}
			}

			$get_users_data_sql = 'SELECT ' . $wp_users . '.`user_nicename` , `display_name` , ' .
				$table_user_roles . '.`user_id`, ' .
				$table_fields_id . '.`field_name`, ' .
				$table_fields_id . '.`field_id`, ' .
				$table_fields_id . '.`field_type_id` ' .
			'FROM ' . $wp_users .
				' INNER JOIN ' . $table_user_roles .
				' ON ' . $table_user_roles . '.`user_id`=' . $wp_users . '.`ID` ' . $get_for_selected_users . $get_for_available_field_value .
					' LEFT JOIN ' . $table_roles_and_fields .
						' ON ' . $table_roles_and_fields . '.`role_id`=' . $table_user_roles . '.`role_id` ' .
					' LEFT JOIN ' . $table_roles_id .
						' ON ' . $table_roles_id . '.`role_id`=' . $table_user_roles . '.`role_id` ' .
					' LEFT JOIN ' . $table_fields_id .
						' ON ' . $table_fields_id . '.`field_id`=' . $table_roles_and_fields . '.`field_id` ' . $get_for_available_fields;

			if ( is_multisite() ) {
				$get_users_data_sql .= ' WHERE ' . $wp_users . '.`ID` IN ( ' . implode( ',', get_users( 'blog_id=' . get_current_blog_id() . '&fields=ID') ) . ')';
			}

			/** Group all and Add sorting order */
			$get_users_data_sql .= ' GROUP BY `' . $wp_users . '`.`ID`, `' . $table_fields_id . '`.`field_id` ORDER BY `' . $wp_users . '`.`user_nicename` ' . $prflxtrflds_options['sort_sequence'];
			/** Begin collate data to print shortcode */
			ob_start();

			$printed_table = $wpdb->get_results( $get_users_data_sql, ARRAY_A );

			if ( ! empty( $printed_table ) ) {
				foreach ( $printed_table as $key => $column ) {
					if ( ! empty( $column['field_id'] ) ) {
						if ( in_array( $column['field_type_id'], array( '3', '4', '5' ) ) ) {
							$user_value = $wpdb->get_col(
								$wpdb->prepare(
									'SELECT `value_name`
								FROM ' . $table_field_values .
									' WHERE `value_id` IN ( SELECT `user_value` FROM ' . $table_user_field_data . ' WHERE `user_id`=%d AND `field_id`=%d )',
									$column['user_id'],
									$column['field_id']
								)
							);

							$printed_table[ $key ]['value'] = implode( ', ', $user_value );
						} else {
							$printed_table[ $key ]['value'] = $wpdb->get_var(
								$wpdb->prepare(
									'SELECT `user_value`
								FROM `' . $table_user_field_data . '` WHERE `user_id`= %d
									AND `field_id`=%d LIMIT 1;',
									$column['user_id'],
									$column['field_id']
								)
							);
						}
					}
				}

				/** Get all field names */
				/** By default show all fields */
				$all_fields_sql = 'SELECT DISTINCT `field_id`, `field_name` FROM ' . $table_fields_id;

				$all_fields = $wpdb->get_results( $all_fields_sql, ARRAY_A );

				/** If need not show empty collumns */
				if ( ! $export_action && 0 === $prflxtrflds_options['show_empty_columns'] ) {
					/** Delete not filled columns */
					foreach ( $all_fields as $key => $one_field ) {
						$is_empty = 1;
						foreach ( $printed_table as $printed_line ) {
							/** If field not empty */
							if ( $printed_line['field_id'] === $one_field['field_id'] ) {
								if ( ! empty( $printed_line['value'] ) ) {
									$is_empty = 0;
									break;
								}
							}
						}
						if ( 1 === $is_empty ) {
							/** Delete if empty from all fields */
							unset( $all_fields[ $key ] );
						}
					}
				}
				if ( 'columns' === $display ) {
					if ( $export_action ) {
						$return_output_export = array();
						$output_export[]      = __( 'User ID', 'profile-extra-fields' );
						$output_export[]      = __( 'Username', 'profile-extra-fields' );
						$output_export[]      = __( 'User role', 'profile-extra-fields' );
						$output_export[]      = __( 'Name', 'profile-extra-fields' );
						$output_export[]      = __( 'Email', 'profile-extra-fields' );
						$output_export[]      = __( 'Posts', 'profile-extra-fields' );

						foreach ( $all_fields as $one_field ) {
								$output_export[] = $one_field['field_name'];
						}
						$return_output_export[] = $output_export;
						unset( $output_export );
						foreach ( $printed_table as $column_key => $column ) {
							/** If is new username */
							if ( ! isset( $printed_table[ $column_key - 1 ] ) ||
								( isset( $printed_table[ $column_key - 1 ] ) &&
									$printed_table[ $column_key - 1 ]['user_nicename'] !== $column['user_nicename'] )
							) {

								$user            = get_user_by( 'ID', $column['user_id'] );
								$output_export[] = $column['user_id'];
								$output_export[] = esc_attr( $column['user_nicename'] );
								$output_export[] = implode( ', ', $user->roles );
								$output_export[] = $user->first_name . ' ' . $user->last_name;
								$output_export[] = $user->user_email;
								$output_export[] = count_user_posts( $user->ID );

								$user_fields_temp = $all_fields;
							}

							foreach ( $user_fields_temp as $key => $one_field ) {
								if ( $column['field_id'] === $one_field['field_id'] ) {
									$user_fields_temp[ $key ]['user_value'] = esc_attr( $column['value'] );
									break;
								}
							}
							if ( ! isset( $printed_table[ $column_key + 1 ] ) ||
								( isset( $printed_table[ $column_key + 1 ] ) &&
									$printed_table[ $column_key + 1 ]['user_nicename'] !== $column['user_nicename'] )
							) {
								if ( ! empty( $user_fields_temp ) ) {
									foreach ( $user_fields_temp as $key => $value ) {
										$output_export[] = $value['user_value'];
									}
								}
								$return_output_export[] = $output_export;
								unset( $output_export );
							}
						}
						return $return_output_export;
					}
					?>
					<div style ="max-width: 100%; overflow-x: auto;margin-bottom: 15px;">
						<table>
							<thead>
								<tr>
									<?php if ( 1 === $prflxtrflds_options['show_id'] ) { ?>
										<th><?php esc_html_e( 'User ID', 'profile-extra-fields' ); ?></th>
									<?php } ?>
									<th><?php esc_html_e( 'Username', 'profile-extra-fields' ); ?></th>
									<?php foreach ( $all_fields as $one_field ) { ?>
										<th><?php echo esc_html( $one_field['field_name'] ); ?></th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ( $printed_table as $column_key => $column ) {
									/** If is new username */
									if ( ! isset( $printed_table[ $column_key - 1 ] ) ||
										( isset( $printed_table[ $column_key - 1 ] ) &&
											$printed_table[ $column_key - 1 ]['user_nicename'] !== $column['user_nicename'] )
									) {
										?>
										<tr>
											<?php if ( 1 === $prflxtrflds_options['show_id'] ) { ?>
												<td><?php echo esc_attr( $column['user_id'] ); ?></td>
											<?php } ?>
											<td><?php echo esc_attr( $column[ 'username' === $prflxtrflds_options['display_user_name'] ? 'user_nicename' : 'display_name' ] ); ?></td>
										<?php
										$user_fields_temp = $all_fields;
									}
									foreach ( $user_fields_temp as $key => $one_field ) {
										if ( $column['field_id'] === $one_field['field_id'] ) {
											if ( ! empty( $column['value'] ) ) {
												if ( 11 === intval( $column['field_type_id'] ) ) {
													$user_fields_temp[ $key ]['user_value'] = '<a href="' . esc_url( $column['value'] ) . '" title="">' . esc_attr( $column['value'] ) . '</a>';
												} else {
													$user_fields_temp[ $key ]['user_value'] = wp_unslash( wp_kses_post( str_replace( PHP_EOL, '<br />', wp_unslash( $column['value'] ) ) ) );
												}
												break;
											} else {
												$user_fields_temp[ $key ]['user_value'] = $prflxtrflds_options['empty_value'];
											}
										}
									}
									if ( ! isset( $printed_table[ $column_key + 1 ] ) ||
										( isset( $printed_table[ $column_key + 1 ] ) &&
											$printed_table[ $column_key + 1 ]['user_nicename'] !== $column['user_nicename'] )
									) {
										if ( ! empty( $user_fields_temp ) ) {
											foreach ( $user_fields_temp as $key => $value ) {
												if ( isset( $value['user_value'] ) ) {
													echo '<td>' . wp_unslash( wp_kses_post( $value['user_value'] ) ) . '</td>';
												} else {
													echo '<td>' . wp_kses_post( $prflxtrflds_options['not_available_message'] ) . '</td>';
												}
											}
										}
										?>
										</tr>
										<?php
									}
								}
								?>
							</tbody>
						</table>
					</div>
					<?php
				} else {
					if ( $export_action ) {
						$distinct_users = $return_output_export = $output_export = array();
						foreach ( $printed_table as $one_row ) {
							/** Create array of distinct users */
							if ( 0 < $one_row['user_id'] && ! isset( $distinct_users[ $one_row['user_id'] ] ) ) {
								$distinct_users[ $one_row['user_id'] ] = $one_row[ 'username' === $prflxtrflds_options['display_user_name'] ? 'user_nicename' : 'display_name' ];
							}
						}
						$output_export[] = __( 'User ID', 'profile-extra-fields' );
						foreach ( array_keys( $distinct_users ) as $user_id ) {
							$output_export[] = esc_attr( $user_id );
						}
						$return_output_export[] = $output_export;
						unset( $output_export );

						$output_export[] = __( 'Username', 'profile-extra-fields' );
						foreach ( $distinct_users as $user_name ) {
							$output_export[] = esc_attr( $user_name );
						}
						$return_output_export[] = $output_export;
						unset( $output_export );

						$output_export[] = __( 'User role', 'profile-extra-fields' );
						foreach ( array_keys( $distinct_users ) as $user_id ) {
							$user            = get_user_by( 'ID', $user_id );
							$output_export[] = implode( ', ', $user->roles );
						}
						$return_output_export[] = $output_export;
						unset( $output_export );

						$output_export[] = __( 'Name', 'profile-extra-fields' );
						foreach ( array_keys( $distinct_users ) as $user_id ) {
							$user            = get_user_by( 'ID', $user_id );
							$output_export[] = $user->first_name . ' ' . $user->last_name;
						}
						$return_output_export[] = $output_export;
						unset( $output_export );

						$output_export[] = __( 'Email', 'profile-extra-fields' );
						foreach ( array_keys( $distinct_users ) as $user_id ) {
							$user            = get_user_by( 'ID', $user_id );
							$output_export[] = $user->user_email;
						}
						$return_output_export[] = $output_export;
						unset( $output_export );

						$output_export[] = __( 'Posts', 'profile-extra-fields' );
						foreach ( array_keys( $distinct_users ) as $user_id ) {
							$user            = get_user_by( 'ID', $user_id );
							$output_export[] = count_user_posts( $user->ID );
						}
						$return_output_export[] = $output_export;
						unset( $output_export );

						foreach ( $all_fields as $one_field ) { /** Create new row for every field */
							$output_export[] = esc_attr( $one_field['field_name'] );
							/** Create column for every user */
							foreach ( array_keys( $distinct_users ) as $one_user_id ) {
								/** Get data for current field id and user */
								foreach ( $printed_table as $one_row ) {
									/** Skip if data not for current user */
									if ( intval( $one_row['user_id'] ) !== $one_user_id ) {
										continue;
									}
									if ( $one_field['field_id'] === $one_row['field_id'] ) {
										/** If no key exist, no set $user_field_data */
										if ( key_exists( 'value', $one_row ) ) {
											if ( empty( $one_row['value'] ) ) {
												/** Empty data for empty user value */
												$user_field_data = '';
											} else {
												/** Save user value */
												$user_field_data = $one_row['value'];
											}
										}
									}
								}
								$output_export[] = esc_attr( $user_field_data );
								unset( $user_field_data );
							}
							$return_output_export[] = $output_export;
							unset( $output_export );
						}
						return $return_output_export;
					}

					$distinct_users = array();
					foreach ( $printed_table as $one_row ) {
						/** Create array of distinct users */
						if ( 0 < $one_row['user_id'] && ! isset( $distinct_users[ $one_row['user_id'] ] ) ) {
							$distinct_users[ $one_row['user_id'] ] = $one_row[ 'username' === $prflxtrflds_options['display_user_name'] ? 'user_nicename' : 'display_name' ];
						}
					}
					?>
					<div style ="max-width: 100%; overflow-x: auto;margin-bottom: 15px;">
						<table>
							<?php if ( 1 === $prflxtrflds_options['show_id'] ) { ?>
							<tr>
								<th><?php esc_html_e( 'User ID', 'profile-extra-fields' ); ?></th>
								<?php foreach ( array_keys( $distinct_users ) as $user_id ) { ?>
									<td><?php echo esc_attr( $user_id ); ?></td>
								<?php } ?>
							</tr>
							<?php } /** Show user name */ ?>
							<tr>
								<th><?php esc_html_e( 'Username', 'profile-extra-fields' ); ?></th>
								<?php foreach ( $distinct_users as $user_name ) { ?>
									<td><?php echo esc_attr( $user_name ); ?></td>
								<?php } ?>
							</tr>
							<?php foreach ( $all_fields as $one_field ) { /** Create new row for every field */ ?>
								<tr>
									<th><?php echo esc_attr( $one_field['field_name'] ); ?></th>
									<?php
									foreach ( array_keys( $distinct_users ) as $one_user_id ) { /** Create column for every user */
										foreach ( $printed_table as $one_row ) {
											/** Get data for current field id and user */
											/** Skip if data not for current user */
											if ( intval( $one_row['user_id'] ) !== $one_user_id ) {
												continue;
											}
											if ( $one_field['field_id'] === $one_row['field_id'] ) {
												/** If no key exist, no set $user_field_data */
												if ( key_exists( 'value', $one_row ) ) {
													if ( empty( $one_row['value'] ) ) {
														/** Empty data for empty user value */
														$user_field_data = '';
													} else {
														/** Save user value */
														if ( 11 === intval( $one_row['field_type_id'] ) ) {
															$user_field_data = '<a href="' . esc_url( $one_row['value'] ) . '" title="" >' . esc_attr( $one_row['value'] ) . '</a>';
														} else {
															$user_field_data = wp_unslash( wp_kses_post( str_replace( PHP_EOL, '<br />', $one_row['value'] ) ) );
														}
													}
												}
											}
										}
										?>
										<td>
											<?php
											if ( ! isset( $user_field_data ) ) {
												/** Current field not avaialible for current user */
												echo esc_html( $prflxtrflds_options['not_available_message'] );
											} elseif ( empty( $user_field_data ) ) {
												/** This value is empty. Unset user data for next user */
												echo esc_html( $prflxtrflds_options['empty_value'] );
												unset( $user_field_data );
											} else {
												/** Print user data. Unset for next user */
												echo wp_unslash( wp_kses_post( $user_field_data ) );
												unset( $user_field_data );
											}
											?>
										</td>
									<?php } ?>
								</tr>
							<?php } ?>
						</table>
					</div>
					<?php
				}
				/** If printed table is empty */
			} else {
				?>
				<p><?php esc_html_e( 'No data for current shortcode settings', 'profile-extra-fields' ); ?></p>
				<?php
			}
			$prflxtrflds_shortcode_output = ob_get_contents();
			ob_end_clean();

			if ( ! empty( $prflxtrflds_shortcode_output ) ) {
				return $prflxtrflds_shortcode_output;
			}
		}
	}
}

if ( ! function_exists( 'prflxtrflds_show_field' ) ) {
	function prflxtrflds_show_field( $param ) {
		global $wpdb, $prflxtrflds_options;
		$error_message = '';

		if ( ! isset( $prflxtrflds_options ) ) {
			prflxtrflds_settings();
		}

		extract(
			shortcode_atts(
				array(
					'field_id' => '',
					'user_id'  => '',
				),
				$param
			)
		);

		if ( empty( $param['user_id'] ) ) {
			$user_id = get_current_user_id();
		} else {
			$user_id = $param['user_id'];
			if ( ! is_numeric( $user_id ) || ! get_user_by( 'id', intval( $user_id ) ) ) {
				/** Show error if user id not exist, or data is uncorrect */
				$error_message = sprintf( __( 'User with entered id(id=%1$s) does not exist!', 'profile-extra-fields' ), esc_attr( $user_id ) );
			}
		}

		$field_ids = $wpdb->get_col( 'SELECT `field_id` FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`' );

		if ( ! in_array( $param['field_id'], $field_ids ) ) {
			$error_message = sprintf( __( 'Field with entered id(id=%1$s) does not exist!', 'profile-extra-fields' ), esc_attr( $param['field_id'] ) );
		}

		$field_type = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT `field_type_id`
				FROM `' . $wpdb->prefix . 'prflxtrflds_fields_id` 
				WHERE `field_id` =%d',
				$param['field_id']
			)
		);

		if ( in_array( $field_type, array( '3', '4', '5' ) ) ) {
			/** Query if type of field is checkbox, radio or drop list*/
			$query = $wpdb->prepare(
				'SELECT `value_name`
				FROM `' . $wpdb->prefix . 'prflxtrflds_field_values` 
				WHERE `value_id` 
				IN ( SELECT `user_value` FROM `' . $wpdb->prefix . 'prflxtrflds_user_field_data` WHERE `user_id`=%d AND `field_id`=%d )',
				$user_id,
				$param['field_id']
			);
			$field = implode( ', ', $wpdb->get_col( $query ) );
		} else {
			$query = $wpdb->prepare(
				'SELECT `user_value`
				FROM `' . $wpdb->prefix . 'prflxtrflds_user_field_data`
				WHERE `field_id` = %d AND `user_id` = %d',
				$param['field_id'],
				$user_id
			);
			$field = $wpdb->get_var( $query );
		}

		if ( ! empty( $error_message ) ) {
			if ( ! empty( $prflxtrflds_options['shortcode_debug'] ) ) {
				return sprintf( '<p>%1$s. %2$s</p>', esc_html__( 'Shortcode output error', 'profile-extra-fields' ), $error_message );
			} else {
				return '';
			}
		} else {
			return $field;
		}
	}
}

if ( ! function_exists( 'prflxtrflds_show_edit_form' ) ) {
	function prflxtrflds_show_edit_form() {
		global $wpdb, $prflxtrflds_options, $hook_suffix, $prflxtrflds_front_shortcode;
		$message = $error = '';
		$errors = new WP_Error();

		if ( wp_is_json_request() ) {
			return;
		}

		if ( ! isset( $prflxtrflds_options ) ) {
			prflxtrflds_settings();
		}
		
		$user_id = get_current_user_id();
		if ( ! empty( $user_id ) ) {
			$user      = wp_get_current_user();
			$user_info = get_userdata( $user_id );
			$user_role = isset( $user_info->roles ) ? implode( "', '", $user_info->roles ) : get_option( 'default_role' );

			if ( isset( $_POST['prflxtrflds_front_info'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_front_info'] ) ), 'prflxtrflds_save_front_info' ) ) {
					$error = __( 'Sorry, your nonce did not verify.', 'profile-extra-fields' );
				} else {
					do_action_ref_array( 'user_profile_update_errors', array( &$errors, true, &$user ) );
					if ( ! empty( $errors->errors ) ) {
						$error = $errors->get_error_message();
					} else {
						if ( ! empty( $_POST['prflxtrflds_not_editable'] ) ) {
							/** Execute not_editable fields */
							$prflxtrflds_not_editable = array_map( 'intval', $_POST['prflxtrflds_not_editable'] );
							$not_editable_ids         = "'" . implode( "','", $prflxtrflds_not_editable ) . "'";
							$wpdb->query(
								$wpdb->prepare(
									'DELETE FROM `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`
								WHERE `user_id` = %d
									AND `field_id` NOT IN (' . $not_editable_ids . ')',
									$user_id
								)
							);
						} else {
							$wpdb->delete(
								$wpdb->base_prefix . 'prflxtrflds_user_field_data',
								array( 'user_id' => $user_id )
							);
						}
						foreach ( $_POST['prflxtrflds_user_field_value'] as $id => $val ) {
							if ( ! empty( $val ) ) {
								if ( is_array( $val ) ) {
									/** For checkboxes */
									foreach ( $val as $user_value ) {
										/** Insert or update value */
										$wpdb->replace(
											$wpdb->base_prefix . 'prflxtrflds_user_field_data',
											array(
												'user_id'    => $user_id,
												'field_id'   => intval( $id ),
												'user_value' => wp_filter_post_kses( wp_unslash( $user_value ) ),
											)
										);
									}
									$message = __( 'Profile updated', 'profile-extra-fields' );
								} else {
									$user_value = wp_filter_post_kses( wp_unslash( $val ) );
									/** Insert or update value */
									$wpdb->replace(
										$wpdb->base_prefix . 'prflxtrflds_user_field_data',
										array(
											'user_id'    => $user_id,
											'field_id'   => intval( $id ),
											'user_value' => $user_value,
										)
									);
									$message = __( 'Profile updated', 'profile-extra-fields' );
								}
							}
						}
					}
				}
			}

			$plugins_data = apply_filters( 'bws_bkng_prflxtrflds_get_data', $plugins_data = array() );
			$enabled_plugins = array_column( $plugins_data, 'slug' );
			array_unshift(
				$plugins_data,
				array(
					'name'    => 'Profile',
					'slug'    => $enabled_plugins,
					'exclude' => true
				)
			);

			$hidden_nonvisible_field = '';

			ob_start(); ?>
			<div class="">
				<?php
				if ( ! empty( $message ) ) {
					echo '<div class="updated notice prflxtrflds-success"><p>' . $message . '</p></div>';
				} 
				if ( ! empty( $error ) ) {
					echo '<div class="error prflxtrflds-error"><p>' . $error . '</p></div>';
				} ?>
				<form action="<?php the_permalink(); ?>" method="post">
					<?php foreach ( $plugins_data as $plugin ) {
						$args         = array(
							'roles'   => array( $user_role ),
							'show'    => $plugin['slug'],
							'exclude' => isset( $plugin['exclude'] ) ? $plugin['exclude'] : false,
						);
						$all_entry    = prflxtrflds_get_fields( $args );
						$custom_class = 'prflxtrflds_extra_fields_' . sanitize_title( $plugin['name'] );
						if ( empty( $all_entry ) ) {
							continue;
						}
						echo '<h3>' . $plugin['name'] . ' ' . __( 'Extra Fields', 'profile-extra-fields' ) . '</h3>';
						echo '<table>';
						foreach ( $all_entry as $one_entry ) {
							/** Add field values */
							$one_entry['available_fields'] = $wpdb->get_results(
								$wpdb->prepare(
									'SELECT `value_id`, `value_name`
									FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values`
									WHERE `field_id` = %d
									ORDER BY `order`',
									$one_entry['field_id']
								),
								ARRAY_A
							);
									
							if ( ! empty( $_POST['prflxtrflds_user_field_value'] ) && isset( $_POST['prflxtrflds_user_fields'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_fields'] ) ), 'prflxtrflds_user_field_action' ) ) {
								if ( isset( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] ) &&
									is_array( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] )
								) {
									/** For checkboxes */
									foreach ( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] as $user_value ) {
										$one_entry['user_value'][] = sanitize_text_field( wp_unslash( $user_value ) );
									}
								} else {
									$one_entry['user_value'] = isset( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] ) ) : '';
								}
							} else {
								/** Add selected values */
								if ( '3' === $one_entry['field_type_id'] ) {
									$user_value = $wpdb->get_results(
										$wpdb->prepare(
											'SELECT `user_value` FROM `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`
											WHERE `user_id`= %d AND `field_id` = %d',
											$user_id,
											$one_entry['field_id']
										),
										ARRAY_A
									);

									if ( ! empty( $user_value ) ) {
										foreach ( $user_value as $key_value => $value_single ) {
											$one_entry['user_value'][] = $value_single['user_value'];
										}
									} else {
										$one_entry['user_value'] = array();
									}
								} else {
									$one_entry['user_value'] = $wpdb->get_var(
										$wpdb->prepare(
											'SELECT `user_value` FROM `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`
											WHERE `user_id`= %s AND `field_id` = %s',
											$user_id,
											$one_entry['field_id']
										)
									);
								}
							}
							
							/** Change `editable` and `visible` data for non-current user editing */
							$editable_visible = $wpdb->get_row(
								$wpdb->prepare(
									'SELECT
									`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`editable`,
									`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`visible`
								FROM
									`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`,
									`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`,
									`' . $wpdb->base_prefix . 'prflxtrflds_roles_id`
								WHERE
									`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`role_id`= `' . $wpdb->base_prefix . 'prflxtrflds_roles_id`.`role_id`
									AND `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`field_id`=`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_id`
									AND `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`field_id`= %d
									AND `' . $wpdb->base_prefix . "prflxtrflds_roles_id`.`role` IN ( '" . $user_role . "' )",
									$one_entry['field_id']
								),
								ARRAY_A
							);

							$one_entry['editable'] = $editable_visible['editable'];
							$one_entry['visible']  = $editable_visible['visible'];
							if ( '0' === $one_entry['editable'] ) {
								$editable_attr            = ' readonly="readonly" disabled="disabled"';
								$hidden_noneditable_field = '<input type="hidden" name="prflxtrflds_not_editable[]" value="' . $one_entry['field_id'] . '" />';
							} else {
								$editable_attr = $hidden_noneditable_field = '';
							}

							$required_attr = ( ! empty( $one_entry['required'] ) && ! empty( $one_entry['editable'] ) ) ? ' required="required"' : '';
							if ( '1' === $one_entry['visible'] ) {
								?>
									<tr>
										<th>
											<?php
											echo esc_html( $one_entry['field_name'] );
											if ( ! empty( $one_entry['required'] ) ) {
												?>
												<span class="description"><?php echo esc_attr( $one_entry['required'] ); ?></span>
												<?php if ( '1' === $one_entry['editable'] ) { ?>
													<input type="hidden"
														name="prflxtrflds_required[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
														value="true"/>
													<?php
												}
											}
											?>
											<input type="hidden"
												name="prflxtrflds_field_name[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												value="<?php echo esc_attr( $one_entry['field_name'] ); ?>">
										</th>
										<td>
											<?php
											switch ( $one_entry['field_type_id'] ) {
												case '1':
													?>
													<input type="text"
														id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
														name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
														value="<?php
														if ( isset( $one_entry['user_value'] ) ) {
															echo esc_attr( wp_unslash( $one_entry['user_value'] ) );
														}
														?>"
														<?php
														if ( isset( $one_entry['available_fields'][0]['value_name'] ) ) {
															echo 'maxlength="' . esc_attr( $one_entry['available_fields'][0]['value_name'] ) . '"';
														}
														echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr );
														?>
													/>
													<?php
													break;
												case '2':
													$unser_textarea = maybe_unserialize( $one_entry['available_fields'][0]['value_name'] );
													?>
														<textarea
															id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															rows="<?php echo esc_attr( $unser_textarea['rows'] ); ?>" cols="<?php echo esc_attr( $unser_textarea['cols'] ); ?>" maxlength="<?php echo esc_attr( $unser_textarea['max_length'] ); ?>"
														<?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?>><?php echo esc_attr( wp_unslash( $one_entry['user_value'] ) ); ?></textarea>
													<?php
													break;
												case '3':
													foreach ( $one_entry['available_fields'] as $one_sub_entry ) {
														$checked = ( ! empty( $one_entry['user_value'] ) && in_array( $one_sub_entry['value_id'], $one_entry['user_value'] ) );
														?>
														<label 
														<?php
														if ( $checked ) {
															echo wp_kses_data( 'class="checked"' );
														}
														?>
														>
															<input type="checkbox" class="prflxtrflds_input_checkbox"
																name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>][]"
																value="<?php echo esc_attr( $one_sub_entry['value_id'] ); ?>"
																<?php
																if ( $checked ) {
																	echo esc_attr( ' checked' );
																}
																echo wp_kses_data( $editable_attr );
																?>
															/>
															<?php echo esc_attr( $one_sub_entry['value_name'] ); ?>
														</label>
														<br/>
														<?php
													}
													break;
												case '4':
													foreach ( $one_entry['available_fields'] as $one_sub_entry ) {
														?>
														<label>
															<input type="radio" class="prflxtrflds_input_radio"
																name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
																value="<?php echo esc_attr( $one_sub_entry['value_id'] ); ?>"
																<?php
																if ( isset( $one_entry['user_value'] ) && $one_sub_entry['value_id'] === $one_entry['user_value'] ) {
																	echo esc_attr( ' checked' );
																}
																echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr );
																?>
															/>
															<?php echo esc_attr( $one_sub_entry['value_name'] ); ?>
														</label>
														<br/>
														<?php
													}
													break;
												case '5':
													?>
													<select
														id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
														name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]" <?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?>>
														<option></option>
														<?php foreach ( $one_entry['available_fields'] as $one_sub_entry ) { ?>
															<option
																value="<?php echo esc_attr( $one_sub_entry['value_id'] ); ?>"
																<?php
																if ( isset( $one_entry['user_value'] ) && $one_sub_entry['value_id'] === $one_entry['user_value'] ) {
																	echo ' selected';}
																?>
																><?php echo esc_attr( $one_sub_entry['value_name'] ); ?></option>
														<?php } ?>
													</select>
													<?php
													break;
												case '6':
													?>
													<input class="prflxtrflds_datetimepicker" type="text"
															id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															value="<?php
															if ( isset( $one_entry['user_value'] ) ) {
																echo esc_attr( $one_entry['user_value'] );}
															?>" 
															<?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?>>
													<?php
													if ( isset( $one_entry['available_fields'][0] ) && strripos( $one_entry['available_fields'][0]['value_name'], 'T' ) ) {
														echo esc_attr( date_i18n( 'T' ) );
													}
													?>
													<input type="hidden" name="prflxtrflds_date_format"
															value="<?php echo esc_attr( trim( str_replace( 'T', '', $one_entry['available_fields'][0]['value_name'] ) ) ); ?>">
													<input type="hidden"
															name="prflxtrflds_user_field_datetime[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															value="<?php echo esc_attr( $one_entry['available_fields'][0]['value_name'] ); ?>">
													<?php
													break;
												case '7':
													?>
													<input class="prflxtrflds_datetimepicker" type="text"
															id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															value="<?php
															if ( isset( $one_entry['user_value'] ) ) {
																echo esc_attr( $one_entry['user_value'] );
															}
															?>" 
															<?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?>>
													<?php
													if ( isset( $one_entry['available_fields'][0] ) && strripos( $one_entry['available_fields'][0]['value_name'], 'T' ) ) {
														echo esc_attr( date_i18n( 'T' ) );
													}
													?>
													<input type="hidden" name="prflxtrflds_time_format"
															value="<?php echo esc_attr( trim( str_replace( 'T', '', $one_entry['available_fields'][0]['value_name'] ) ) ); ?>">
													<input type="hidden"
															name="prflxtrflds_user_field_datetime[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															value="<?php echo esc_attr( $one_entry['available_fields'][0]['value_name'] ); ?>">
													<?php
													break;
												case '8':
													$date_and_time = unserialize( $one_entry['available_fields'][0]['value_name'] );
													?>
													<input class="prflxtrflds_datetimepicker" type="text"
															id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															value="<?php
															if ( isset( $one_entry['user_value'] ) ) {
																echo esc_attr( $one_entry['user_value'] );
															}
															?>" 
															<?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?>>
													<?php
													if ( strripos( $date_and_time['time'], 'T' ) || strripos( $date_and_time['date'], 'T' ) ) {
														echo esc_attr( date_i18n( 'T' ) );
													}
													?>
													<input type="hidden" name="prflxtrflds_time_format"
															value="<?php echo esc_attr( trim( str_replace( 'T', '', $date_and_time['time'] ) ) ); ?>">
													<input type="hidden" name="prflxtrflds_date_format"
															value="<?php echo esc_attr( trim( str_replace( 'T', '', $date_and_time['date'] ) ) ); ?>">
													<input type="hidden"
															name="prflxtrflds_user_field_datetime[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															value="<?php echo esc_attr( $date_and_time['date'] ) . ' ' . esc_attr( $date_and_time['time'] ); ?>">
													<?php
													break;
												case '9':
													?>
													<input type="number" class="prflxtrflds_number"
															id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															value="<?php
															if ( isset( $one_entry['user_value'] ) ) {
																echo esc_attr( $one_entry['user_value'] );
															}
															?>" 
															<?php
															if ( isset( $one_entry['available_fields'][0]['value_name'] ) ) {
																echo 'max="' . esc_attr( $one_entry['available_fields'][0]['value_name'] ) . '"';}
															echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr );
															?>
													/>
													<?php if ( isset( $one_entry['available_fields'][0]['value_name'] ) ) { ?>
														<input type="hidden"
																name="prflxtrflds_user_field_max_number[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
																value="<?php echo esc_attr( $one_entry['available_fields'][0]['value_name'] ); ?>">
														<?php
													}
													break;
												case '10':
													?>
													<input type="text" class="prflxtrflds_phone"
														id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
														name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
														value="<?php
														if ( isset( $one_entry['user_value'] ) ) {
															echo esc_attr( wp_unslash( $one_entry['user_value'] ) );
														}
														?>" <?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?> >
													<input type="hidden"
														name="prflxtrflds_user_field_pattern[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
														value="<?php echo esc_attr( $one_entry['available_fields'][0]['value_name'] ); ?>">
													<?php
													break;

												case '11':
													?>
													<input type="url" class="prflxtrflds_url"
															id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
															value="<?php
															if ( isset( $one_entry['user_value'] ) ) {
																echo esc_attr( wp_unslash( $one_entry['user_value'] ) );
															}
															?>"
															<?php
															if ( isset( $one_entry['available_fields'][0]['value_name'] ) ) {
																echo 'maxlength="' . esc_attr( $one_entry['available_fields'][0]['value_name'] ) . '"';}
															echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr );
															?>
													 />
													<?php
													break;
											}
											echo wp_kses_data( $hidden_noneditable_field );
											if ( isset( $one_entry['description'] ) ) {
												?>
												<p class="description"><?php echo esc_html( $one_entry['description'] ); ?></p>
											<?php } ?>
										</td>
									</tr>
								<?php
							} else {
								$hidden_nonvisible_field .= $hidden_noneditable_field;
							}
						}
						echo '</table>';	
						echo wp_kses_data( $hidden_nonvisible_field );
						$prflxtrflds_front_shortcode = true;

					} ?>
					<p>
						<input type="submit" value="<?php esc_html_e( 'Save Info', 'profile-extra-fields' ); ?>" />
						<?php wp_nonce_field( 'prflxtrflds_save_front_info', 'prflxtrflds_front_info' ); ?>
					</p>
				</form>
			</div>
			<?php
			$prflxtrflds_shortcode_output = ob_get_contents();
			ob_end_clean();
			if ( ! empty( $prflxtrflds_shortcode_output ) ) {
				return $prflxtrflds_shortcode_output;
			}
		}
	}
}

/** Show info in user profile page */
if ( ! function_exists( 'prflxtrflds_fields_table' ) ) {
	function prflxtrflds_fields_table( $profileuser = false ) {
		global $wpdb, $hook_suffix, $pagenow;
		if ( 'user-new.php' === $pagenow || 'user-edit.php' === $pagenow ) {
			$user_id   = get_current_user_id();
			$user_info = get_userdata( $user_id );
			$user_role = isset( $user_info->roles ) ? implode( "', '", $user_info->roles ) : get_option( 'default_role' );
			if ( ! empty( $profileuser ) && is_object( $profileuser ) && isset( $profileuser->ID ) && $profileuser->ID !== $user_id ) {
				$user_id = $profileuser->ID;
			}
		} else {
			$user_id   = isset( $profileuser->ID ) ? $profileuser->ID : get_current_user_id();
			$user_info = get_userdata( $user_id );
			$user_role = isset( $user_info->roles ) ? implode( "', '", $user_info->roles ) : get_option( 'default_role' );
		}

		$plugins_data = apply_filters( 'bws_bkng_prflxtrflds_get_data', $plugins_data = array() );

		preg_match( '/bws_bkng_(.*?)_(.*?)$/', current_filter(), $matches );

		if ( empty( $matches ) ) {
			$enabled_plugins = array_column( $plugins_data, 'slug' );
			array_unshift(
				$plugins_data,
				array(
					'name'    => 'Profile',
					'slug'    => $enabled_plugins,
					'exclude' => true,
				)
			);
		} elseif ( ! empty( $matches[1] ) ) {
			$key          = array_search( $matches[2], array_column( $plugins_data, 'slug' ) );
			$plugins_data = array_values( $plugins_data );
			$plugins_data = array( $plugins_data[ $key ] );
			$show_in      = $matches[2];
			$certain_page = $matches[1];
		}
		prflxtrflds_enqueue_fields_styles();

		$hidden_nonvisible_field = '';

		foreach ( $plugins_data as $plugin ) {
			$args         = array(
				'roles'   => array( $user_role ),
				'show'    => $plugin['slug'],
				'exclude' => isset( $plugin['exclude'] ) ? $plugin['exclude'] : false,
			);
			$all_entry    = prflxtrflds_get_fields( $args );
			$custom_class = 'prflxtrflds_extra_fields_' . sanitize_title( $plugin['name'] );

			if ( ! empty( $certain_page ) ) {
				foreach ( $all_entry as $key => $one_entry ) {
					$one_entry['certain_pages'] = maybe_unserialize(
						$wpdb->get_var(
							$wpdb->prepare(
								'SELECT `value`
								FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_meta`
								WHERE `field_id` = %d AND `show_in` = %s',
								$one_entry['field_id'],
								$show_in
							)
						)
					);
					if ( ! is_array( $one_entry['certain_pages'] ) || ! array_key_exists( $certain_page, $one_entry['certain_pages'] ) ) {
						unset( $all_entry[ $key ] );
					}
				}
			}

			if ( empty( $all_entry ) ) {
				continue;
			}

			?>
			<h2 class="<?php echo esc_attr( $custom_class ); ?>"><?php printf( esc_html__( '%1$s Extra Fields', 'profile-extra-fields' ), esc_html( $plugin['name'] ) ); ?></h2>
			<table class="form-table <?php echo esc_attr( $custom_class ); ?>">
				<?php wp_nonce_field( 'prflxtrflds_user_field_action', 'prflxtrflds_user_fields' ); ?>
				<?php
				/** Group result array by field_id */
				foreach ( $all_entry as $one_entry ) {
					/** Add field values */
					$one_entry['available_fields'] = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT `value_id`, `value_name`
							FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values`
							WHERE `field_id` = %d
							ORDER BY `order`',
							$one_entry['field_id']
						),
						ARRAY_A
					);

					if ( ! empty( $_POST['prflxtrflds_user_field_value'] ) && isset( $_POST['prflxtrflds_user_fields'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_fields'] ) ), 'prflxtrflds_user_field_action' ) ) {
						if ( isset( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] ) &&
							is_array( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] )
						) {
							/** For checkboxes */
							foreach ( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] as $user_value ) {
								$one_entry['user_value'][] = sanitize_text_field( wp_unslash( $user_value ) );
							}
						} else {
							$one_entry['user_value'] = isset( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] ) ) : '';
						}
					} else {
						/** Add selected values */
						if ( 'user-new.php' === $pagenow ) {
							if ( '3' === $one_entry['field_type_id'] ) {
								$one_entry['user_value'] = array();
							} else {
								$one_entry['user_value'] = '';
							}
						} else {
							if ( '3' === $one_entry['field_type_id'] ) {
								$user_value = $wpdb->get_results(
									$wpdb->prepare(
										'SELECT `user_value` FROM `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`
										WHERE `user_id`= %d AND `field_id` = %d',
										$user_id,
										$one_entry['field_id']
									),
									ARRAY_A
								);

								if ( ! empty( $user_value ) ) {
									foreach ( $user_value as $key_value => $value_single ) {
										$one_entry['user_value'][] = $value_single['user_value'];
									}
								} else {
									$one_entry['user_value'] = array();
								}
							} else {
								$one_entry['user_value'] = $wpdb->get_var(
									$wpdb->prepare(
										'SELECT `user_value` FROM `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`
										WHERE `user_id`= %s AND `field_id` = %s',
										$user_id,
										$one_entry['field_id']
									)
								);
							}
						}
					}
					if ( 'profile.php' !== $hook_suffix ) {
						/** Change `editable` and `visible` data for non-current user editing */
						$editable_visible = $wpdb->get_row(
							$wpdb->prepare(
								'SELECT
								`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`editable`,
								`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`visible`
							FROM
								`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`,
								`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`,
								`' . $wpdb->base_prefix . 'prflxtrflds_roles_id`
							WHERE
								`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`role_id`= `' . $wpdb->base_prefix . 'prflxtrflds_roles_id`.`role_id`
								AND `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`field_id`=`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_id`
								AND `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`field_id`= %d
								AND `' . $wpdb->base_prefix . "prflxtrflds_roles_id`.`role` IN ( '" . $user_role . "' )",
								$one_entry['field_id']
							),
							ARRAY_A
						);

						$one_entry['editable'] = $editable_visible['editable'];
						$one_entry['visible']  = $editable_visible['visible'];
					}
					if ( '0' === $one_entry['editable'] && ( 'profile.php' !== $hook_suffix || ! current_user_can( 'edit_users' ) ) ) {
						$editable_attr            = ' readonly="readonly" disabled="disabled"';
						$hidden_noneditable_field = '<input type="hidden" name="prflxtrflds_not_editable[]" value="' . $one_entry['field_id'] . '" />';
					} else {
						$editable_attr = $hidden_noneditable_field = '';
					}

					$required_attr = ( ! empty( $one_entry['required'] ) && ! empty( $one_entry['editable'] ) ) ? ' required="required"' : '';

					if ( '1' === $one_entry['visible'] && ( 'profile.php' === $hook_suffix || current_user_can( 'edit_users' ) ) ) {
						?>
						<tr>
							<th>
								<?php
								echo esc_html( $one_entry['field_name'] );
								if ( ! empty( $one_entry['required'] ) ) {
									?>
									<span class="description"><?php echo esc_attr( $one_entry['required'] ); ?></span>
									<?php if ( '1' === $one_entry['editable'] ) { ?>
										<input type="hidden"
											name="prflxtrflds_required[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
											value="true"/>
										<?php
									}
								}
								?>
								<input type="hidden"
									name="prflxtrflds_field_name[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
									value="<?php echo esc_attr( $one_entry['field_name'] ); ?>">
							</th>
							<td>
								<?php
								switch ( $one_entry['field_type_id'] ) {
									case '1':
										?>
										<input type="text"
											id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
											name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
											value="<?php
											if ( isset( $one_entry['user_value'] ) ) {
												echo esc_attr( wp_unslash( $one_entry['user_value'] ) );
											}
											?>"
											<?php
											if ( isset( $one_entry['available_fields'][0]['value_name'] ) ) {
												echo 'maxlength="' . esc_attr( $one_entry['available_fields'][0]['value_name'] ) . '"';
											}
											echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr );
											?>
										/>
										<?php
										break;
									case '2':
										$unser_textarea = maybe_unserialize( $one_entry['available_fields'][0]['value_name'] );
										?>
											<textarea
												id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												rows="<?php echo esc_attr( $unser_textarea['rows'] ); ?>" cols="<?php echo esc_attr( $unser_textarea['cols'] ); ?>" maxlength="<?php echo esc_attr( $unser_textarea['max_length'] ); ?>"
											<?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?>><?php echo esc_attr( wp_unslash( $one_entry['user_value'] ) ); ?></textarea>
										<?php
										break;
									case '3':
										foreach ( $one_entry['available_fields'] as $one_sub_entry ) {
											$checked = ( ! empty( $one_entry['user_value'] ) && in_array( $one_sub_entry['value_id'], $one_entry['user_value'] ) );
											?>
											<label 
											<?php
											if ( $checked ) {
												echo wp_kses_data( 'class="checked"' );
											}
											?>
											>
												<input type="checkbox" class="prflxtrflds_input_checkbox"
													name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>][]"
													value="<?php echo esc_attr( $one_sub_entry['value_id'] ); ?>"
													<?php
													if ( $checked ) {
														echo esc_attr( ' checked' );
													}
													echo wp_kses_data( $editable_attr );
													?>
												/>
												<?php echo esc_attr( $one_sub_entry['value_name'] ); ?>
											</label>
											<br/>
											<?php
										}
										break;
									case '4':
										foreach ( $one_entry['available_fields'] as $one_sub_entry ) {
											?>
											<label>
												<input type="radio" class="prflxtrflds_input_radio"
													name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
													value="<?php echo esc_attr( $one_sub_entry['value_id'] ); ?>"
													<?php
													if ( isset( $one_entry['user_value'] ) && $one_sub_entry['value_id'] === $one_entry['user_value'] ) {
														echo esc_attr( ' checked' );
													}
													echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr );
													?>
												/>
												<?php echo esc_attr( $one_sub_entry['value_name'] ); ?>
											</label>
											<br/>
											<?php
										}
										break;
									case '5':
										?>
										<select
											id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
											name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]" <?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?>>
											<option></option>
											<?php foreach ( $one_entry['available_fields'] as $one_sub_entry ) { ?>
												<option
													value="<?php echo esc_attr( $one_sub_entry['value_id'] ); ?>"
													<?php
													if ( isset( $one_entry['user_value'] ) && $one_sub_entry['value_id'] === $one_entry['user_value'] ) {
														echo ' selected';}
													?>
													><?php echo esc_attr( $one_sub_entry['value_name'] ); ?></option>
											<?php } ?>
										</select>
										<?php
										break;
									case '6':
										?>
										<input class="prflxtrflds_datetimepicker" type="text"
												id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												value="<?php
												if ( isset( $one_entry['user_value'] ) ) {
													echo esc_attr( $one_entry['user_value'] );
												}
												?>" 
												<?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?>>
										<?php
										if ( isset( $one_entry['available_fields'][0] ) && strripos( $one_entry['available_fields'][0]['value_name'], 'T' ) ) {
											echo esc_attr( date_i18n( 'T' ) );
										}
										?>
										<input type="hidden" name="prflxtrflds_date_format"
												value="<?php echo esc_attr( trim( str_replace( 'T', '', $one_entry['available_fields'][0]['value_name'] ) ) ); ?>">
										<input type="hidden"
												name="prflxtrflds_user_field_datetime[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												value="<?php echo esc_attr( $one_entry['available_fields'][0]['value_name'] ); ?>">
										<?php
										break;
									case '7':
										?>
										<input class="prflxtrflds_datetimepicker" type="text"
												id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												value="<?php
												if ( isset( $one_entry['user_value'] ) ) {
													echo esc_attr( $one_entry['user_value'] );
												}
												?>" 
												<?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?>>
										<?php
										if ( isset( $one_entry['available_fields'][0] ) && strripos( $one_entry['available_fields'][0]['value_name'], 'T' ) ) {
											echo esc_attr( date_i18n( 'T' ) );
										}
										?>
										<input type="hidden" name="prflxtrflds_time_format"
												value="<?php echo esc_attr( trim( str_replace( 'T', '', $one_entry['available_fields'][0]['value_name'] ) ) ); ?>">
										<input type="hidden"
												name="prflxtrflds_user_field_datetime[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												value="<?php echo esc_attr( $one_entry['available_fields'][0]['value_name'] ); ?>">
										<?php
										break;
									case '8':
										$date_and_time = unserialize( $one_entry['available_fields'][0]['value_name'] );
										?>
										<input class="prflxtrflds_datetimepicker" type="text"
												id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												value="<?php
												if ( isset( $one_entry['user_value'] ) ) {
													echo esc_attr( $one_entry['user_value'] );}
												?>" 
												<?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?>>
										<?php
										if ( strripos( $date_and_time['time'], 'T' ) || strripos( $date_and_time['date'], 'T' ) ) {
											echo esc_attr( date_i18n( 'T' ) );
										}
										?>
										<input type="hidden" name="prflxtrflds_time_format"
												value="<?php echo esc_attr( trim( str_replace( 'T', '', $date_and_time['time'] ) ) ); ?>">
										<input type="hidden" name="prflxtrflds_date_format"
												value="<?php echo esc_attr( trim( str_replace( 'T', '', $date_and_time['date'] ) ) ); ?>">
										<input type="hidden"
												name="prflxtrflds_user_field_datetime[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												value="<?php echo esc_attr( $date_and_time['date'] ) . ' ' . esc_attr( $date_and_time['time'] ); ?>">
										<?php
										break;
									case '9':
										?>
										<input type="number" class="prflxtrflds_number"
												id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												value="<?php
												if ( isset( $one_entry['user_value'] ) ) {
													echo esc_attr( $one_entry['user_value'] );}
												?>" 
												<?php
												if ( isset( $one_entry['available_fields'][0]['value_name'] ) ) {
													echo 'max="' . esc_attr( $one_entry['available_fields'][0]['value_name'] ) . '"';}
												echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr );
												?>
										/>
										<?php if ( isset( $one_entry['available_fields'][0]['value_name'] ) ) { ?>
											<input type="hidden"
													name="prflxtrflds_user_field_max_number[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
													value="<?php echo esc_attr( $one_entry['available_fields'][0]['value_name'] ); ?>">
											<?php
										}
										break;
									case '10':
										?>
										<input type="text" class="prflxtrflds_phone"
											id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
											name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
											value="<?php
											if ( isset( $one_entry['user_value'] ) ) {
												echo esc_attr( wp_unslash( $one_entry['user_value'] ) );
											}
											?>" <?php echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr ); ?> >
										<input type="hidden"
											name="prflxtrflds_user_field_pattern[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
											value="<?php echo esc_attr( $one_entry['available_fields'][0]['value_name'] ); ?>">
										<?php
										break;

									case '11':
										?>
										<input type="url" class="prflxtrflds_url"
												id="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												name="prflxtrflds_user_field_value[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
												value="<?php
												if ( isset( $one_entry['user_value'] ) ) {
													echo esc_attr( wp_unslash( $one_entry['user_value'] ) );
												}
												?>"
												<?php
												if ( isset( $one_entry['available_fields'][0]['value_name'] ) ) {
													echo 'maxlength="' . esc_attr( $one_entry['available_fields'][0]['value_name'] ) . '"';}
												echo wp_kses_data( $editable_attr ) . wp_kses_data( $required_attr );
												?>
										 />
										<?php
										break;
								}
								echo wp_kses_data( $hidden_noneditable_field );
								if ( isset( $one_entry['description'] ) ) {
									?>
									<p class="description"><?php echo esc_html( $one_entry['description'] ); ?></p>
								<?php } ?>
							</td>
						</tr>
						<?php
					} else {
						$hidden_nonvisible_field .= $hidden_noneditable_field;
					}
				}
				?>
			</table><!--.form-table-->
			<?php
		}
		echo wp_kses_data( $hidden_nonvisible_field );
	}
}

/** Send errors to edit user page */
if ( ! function_exists( 'prflxtrflds_create_user_error' ) ) {
	function prflxtrflds_create_user_error( $errors, $update = null, $user = null ) {
		$required_array = array();

		if ( ( isset( $_POST['prflxtrflds_user_register_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_register_field'] ) ), 'prflxtrflds_user_register_action' )  ) 
			|| ( isset( $_POST['prflxtrflds_user_fields'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_fields'] ) ), 'prflxtrflds_user_field_action' ) ) 
			|| ( isset( $_POST['prflxtrflds_front_info'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_front_info'] ) ), 'prflxtrflds_save_front_info' ) ) 
		){
			if ( ! empty( $_POST['prflxtrflds_required'] ) ) {
				/** Get all reqired ids */
				foreach ( $_POST['prflxtrflds_required'] as $required_id => $required_value ) {
					if ( empty( $_POST['prflxtrflds_user_field_value'][ $required_id ] ) ) {
						/** Error for non-textfield */
						$name = isset( $_POST['prflxtrflds_field_name'][ $required_id ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_field_name'][ $required_id ] ) ) : '';
						$errors->add( 'prflxtrflds_required_error', sprintf( __( 'Required field %1$s is not filled. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $name . '</strong>' ) );
						$required_array[] = $required_id;
					}
				}
			}
			if ( ! empty( $_POST['prflxtrflds_user_field_pattern'] ) ) {
				foreach ( $_POST['prflxtrflds_user_field_pattern'] as $field_id => $pattern ) {
					if ( ! empty( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) &&
						! in_array( $field_id, $required_array )
					) {
						if ( ! preg_match( '/^' . str_replace( '\*', '[0-9]', preg_quote( $pattern ) ) . '$/', sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) ) ) ) {
							$name = isset( $_POST['prflxtrflds_field_name'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_field_name'][ $field_id ] ) ) : '';
							$errors->add( 'prflxtrflds_match_error', sprintf( __( 'Field %1$s does not match %2$s. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $name . '</strong>', '<strong>' . $pattern . '</strong>' ) );
						}
					}
				}
			}

			if ( ! empty( $_POST['prflxtrflds_user_field_max_number'] ) ) {
				foreach ( $_POST['prflxtrflds_user_field_max_number'] as $field_id => $max_number ) {
					if ( ! empty( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) &&
						! in_array( $field_id, $required_array )
					) {
						$max_number = intval( $max_number );
						if ( $max_number > 0 &&
							intval( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) > $max_number
						) {
							$_POST['prflxtrflds_user_field_value'][ $field_id ] = $max_number;
						}
					}
				}
			}

			if ( ! empty( $_POST['prflxtrflds_user_field_datetime'] ) ) {
				foreach ( $_POST['prflxtrflds_user_field_datetime'] as $field_id => $pattern ) {
					if ( ! empty( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) &&
						! in_array( $field_id, $required_array )
					) {
						$pattern = trim( str_replace( 'T', '', sanitize_text_field( wp_unslash( $pattern ) ) ) );
						if ( function_exists( 'date_create_from_format' ) ) {
							$d = date_create_from_format( $pattern, $_POST['prflxtrflds_user_field_value'][ $field_id ] );
							if ( ! $d || $d->format( $pattern ) !== $_POST['prflxtrflds_user_field_value'][ $field_id ] ) {
								$name = isset( $_POST['prflxtrflds_field_name'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_field_name'][ $field_id ] ) ) : '';
								$errors->add( 'prflxtrflds_match_error', sprintf( __( 'Field %1$s does not match %2$s. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $name . '</strong>', '<strong>' . $pattern . '</strong>' ) );
							}
						} elseif ( ! strtotime( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) ) {
							$name = isset( $_POST['prflxtrflds_field_name'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_field_name'][ $field_id ] ) ) : '';
							$errors->add( 'prflxtrflds_match_error', sprintf( __( 'Field %1$s does not match %2$s. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $name . '</strong>', '<strong>' . $pattern . '</strong>' ) );
						}
					}
				}
			}
		}
	}
}

/** Save user data from Edit user page */
if ( ! function_exists( 'prflxtrflds_save_user_data' ) ) {
	function prflxtrflds_save_user_data() {
		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : get_current_user_id();
		/** Get errors */
		$errors = edit_user( $user_id );
		if ( ! is_wp_error( $errors ) && ! empty( $_POST['prflxtrflds_user_field_value'] ) && isset( $_POST['prflxtrflds_user_fields'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_fields'] ) ), 'prflxtrflds_user_field_action' ) ) {
			global $wpdb;

			/** If array exists ( exist available fields for current user ), remove old data */
			if ( ! empty( $_POST['prflxtrflds_not_editable'] ) ) {
				/** Execute not_editable fields */
				$prflxtrflds_not_editable = array_map( 'intval', $_POST['prflxtrflds_not_editable'] );
				$not_editable_ids         = "'" . implode( "','", $prflxtrflds_not_editable ) . "'";
				$wpdb->query(
					$wpdb->prepare(
						'DELETE FROM `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`
					WHERE `user_id` = %d
						AND `field_id` NOT IN (' . $not_editable_ids . ')',
						$user_id
					)
				);
			} else {
				$wpdb->delete(
					$wpdb->base_prefix . 'prflxtrflds_user_field_data',
					array( 'user_id' => $user_id )
				);
			}

			/** Create array with user values */
			foreach ( $_POST['prflxtrflds_user_field_value'] as $id => $val ) {
				if ( ! empty( $val ) ) {
					if ( is_array( $val ) ) {
						/** For checkboxes */
						foreach ( $val as $user_value ) {
							/** Insert or update value */
							$wpdb->replace(
								$wpdb->base_prefix . 'prflxtrflds_user_field_data',
								array(
									'user_id'    => $user_id,
									'field_id'   => intval( $id ),
									'user_value' => wp_filter_post_kses( wp_unslash( $user_value ) ),
								)
							);
						}
					} else {
						$user_value = wp_filter_post_kses( wp_unslash( $val ) );
						/** Insert or update value */
						$wpdb->replace(
							$wpdb->base_prefix . 'prflxtrflds_user_field_data',
							array(
								'user_id'    => $user_id,
								'field_id'   => intval( $id ),
								'user_value' => $user_value,
							)
						);
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'prflxtrflds_save_booking_fields' ) ) {
	function prflxtrflds_save_booking_fields( $data ) {
		global $wpdb;

		if ( ! isset( $_POST['prflxtrflds_user_field_value'] ) ) {
			return $data;
		}

		if ( isset( $_POST['prflxtrflds_required'] ) ) {
			foreach ( $_POST['prflxtrflds_required'] as $key => $value ) {
				if ( empty( $_POST['prflxtrflds_user_field_value'][ $key ] ) ) {
					add_filter( 'bws_bkng_required_bilings_fields', 'prflxtrflds_add_required_fields' );
					return $data;
				}
			}
		}
		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : get_current_user_id();

		/** If array exists ( exist available fields for current user ), remove old data */
		if ( ! empty( $_POST['prflxtrflds_not_editable'] ) ) {
			/** Execute not_editable fields */
			$prflxtrflds_not_editable = array_map( 'intval', $_POST['prflxtrflds_not_editable'] );
			$not_editable_ids         = "'" . implode( "','", $prflxtrflds_not_editable ) . "'";

			$wpdb->query(
				$wpdb->prepare(
					'DELETE FROM `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`
				WHERE `user_id` = %d
					AND `field_id` NOT IN (' . $not_editable_ids . ')',
					$user_id
				)
			);
		} else {
			$wpdb->delete(
				$wpdb->base_prefix . 'prflxtrflds_user_field_data',
				array( 'user_id' => $user_id )
			);
		}

		foreach ( $_POST['prflxtrflds_user_field_value'] as $id => $val ) {
			if ( ! empty( $val ) ) {
				if ( is_array( $val ) ) {
					/** For checkboxes */
					foreach ( $val as $user_value ) {
						/** Insert or update value */
						$wpdb->replace(
							$wpdb->base_prefix . 'prflxtrflds_user_field_data',
							array(
								'user_id'    => $user_id,
								'field_id'   => intval( $id ),
								'user_value' => esc_sql( sanitize_text_field( wp_unslash( $user_value ) ) ),
							)
						);
					}
				} else {
					$user_value = esc_sql( sanitize_text_field( wp_unslash( $val ) ) );
					/** Insert or update value */
					$wpdb->replace(
						$wpdb->base_prefix . 'prflxtrflds_user_field_data',
						array(
							'user_id'    => $user_id,
							'field_id'   => intval( $id ),
							'user_value' => $user_value,
						)
					);
				}
			}
		}

		return $data;
	}
}

/** Add field order */
if ( ! function_exists( 'prflxtrflds_add_required_fields' ) ) {
	function prflxtrflds_add_required_fields( $fields ) {
		return array_merge( $fields, array( 'prflxtrflds_user_field_value' ) );
	}
}

/** Add error message */
if ( ! function_exists( 'prflxtrflds_add_error_message' ) ) {
	function prflxtrflds_add_error_message( $message, $code ) {
		return $message . ( 'prflxtrflds_user_field_value' === $code ) ? esc_html__( 'Please fill all required fields.', 'profile-extra-fields' ) : '';
	}
}

/** Save field order from wp list table */
if ( ! function_exists( 'prflxtrflds_table_order' ) ) {
	function prflxtrflds_table_order() {
		/** Check ajax. Function fie if error */
		check_ajax_referer( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' );
		/** If check ok, edit order */
		if ( isset( $_POST['table_order'] ) ) {
			/** Into string is values with coma separate */
			$sort_parametrs = filter_input( INPUT_POST, 'table_order', FILTER_SANITIZE_STRING );

			/** Role id = 0 for 'all' users */
			$role_id = ( isset( $_POST['field_id'] ) && 'all' !== $_POST['field_id'] ) ? intval( $_POST['field_id'] ) : 0;

			if ( '0' !== $sort_parametrs ) {
				global $wpdb;
				$table_roles_and_fields = $wpdb->base_prefix . 'prflxtrflds_roles_and_fields';
				/** Create array */
				$sort_parametrs = explode( ', ', $sort_parametrs );
				if ( is_array( $sort_parametrs ) ) {
					$i = 0;
					foreach ( $sort_parametrs as $field_id ) {
						$field_id = intval( $field_id );
						if ( 0 !== $role_id ) {
							$wpdb->update(
								$table_roles_and_fields,
								array(
									'field_order' => $i,
								),
								array(
									'field_id' => $field_id,
									'role_id'  => $role_id,
								),
								array( '%d' ),
								array( '%d', '%d' )
							);
						} else {
							/** If role id === 0, change sort settings for all roles */
							$wpdb->update(
								$table_roles_and_fields,
								array(
									'field_order' => $i,
								),
								array(
									'field_id' => $field_id,
								),
								array( '%d' ),
								array( '%d' )
							);
						}
						$i++;
					}
				}
			}
		}
		wp_die();
	}
}

if ( ! function_exists( 'prflxtrflds_get_users' ) ) {
	function prflxtrflds_get_users() {
		/** Check ajax. Function fie if error */
		check_ajax_referer( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' );
		$users = get_users();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				?>
				<option value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_attr( $user->display_name ); ?></option>
				<?php
			}
		}
		wp_die();
	}
}

if ( ! function_exists( 'prflxtrflds_get_roles' ) ) {
	function prflxtrflds_get_roles() {
		global $wpdb;
		/** Check ajax. Function fie if error */
		check_ajax_referer( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' );
		$roles = $wpdb->get_results( 'SELECT `role`, `role_name` FROM `' . $wpdb->base_prefix . 'prflxtrflds_roles_id`', ARRAY_A );
		if ( ! empty( $roles ) ) {
			foreach ( $roles as $role ) {
				?>
				<option value="<?php echo esc_attr( $role['role'] ); ?>"><?php echo esc_attr( $role['role_name'] ); ?></option>
				<?php
			}
		}
		wp_die();
	}
}

if ( ! function_exists( 'prflxtrflds_get_fields_name' ) ) {
	function prflxtrflds_get_fields_name() {
		global $wpdb;
		/** Check ajax. Function fie if error */
		check_ajax_referer( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' );
		$fields = $wpdb->get_results( 'SELECT `field_id`, `field_name` FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`', ARRAY_A );
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				?>
				<option value="<?php echo esc_attr( $field['field_id'] ); ?>"><?php echo esc_attr( $field['field_name'] ); ?></option>
				<?php
			}
		}
		wp_die();
	}
}

/** Add shortcode content */
if ( ! function_exists( 'prflxtrflds_shortcode_button_content' ) ) {
	function prflxtrflds_shortcode_button_content( $content ) {
		global $prflxtrflds_options;

		if ( ! isset( $prflxtrflds_options ) ) {
			prflxtrflds_settings();
		}
		$display_users = array(
			'all'           => __( 'Display all users data', 'profile-extra-fields' ),
			'current_user'  => __( 'Display logged in user data', 'profile-extra-fields' ),
			'specify_roles' => __( 'Specify a user role', 'profile-extra-fields' ),
			'specify_users' => __( 'Specify a user', 'profile-extra-fields' ),
		);
		?>
		<div id="prflxtrflds" style="display:none;">
			<p>
				<span style="vertical-align: middle;"><?php esc_html_e( 'Select Shortcode', 'profile-extra-fields' ); ?>:</span>&emsp;
				<select name="prflxtrflds_shortcode">
					<option value="table" selected="selected"><?php esc_html_e( 'Table', 'profile-extra-fields' ); ?></option>
					<option value="field"><?php esc_html_e( 'Field Value', 'profile-extra-fields' ); ?></option>
				</select>
			</p>
			<div class="prflxtrflds_table_block">
				<p>
					<span style="vertical-align: middle;"><?php esc_html_e( 'Data Rotation', 'profile-extra-fields' ); ?>:</span>&emsp;
					<select class='prflxtrflds_header_table' name="prflxtrflds_header_table">
						<option value="columns"<?php selected( $prflxtrflds_options['header_table'], 'columns' ); ?>><?php esc_html_e( 'Columns', 'profile-extra-fields' ); ?></option>
						<option value="rows"<?php selected( $prflxtrflds_options['header_table'], 'rows' ); ?>><?php esc_html_e( 'Rows', 'profile-extra-fields' ); ?></option>
					</select>
				</p>
				<p>
					<span style="vertical-align: middle;"><?php esc_html_e( 'Users', 'profile-extra-fields' ); ?>:</span>&emsp;
					<select class='prflxtrflds_specify' name="prflxtrflds_specify">
						<?php foreach ( $display_users as $users => $label ) { ?>
							<option value="<?php echo esc_attr( $users ); ?>" <?php selected( $users, 'all' ); ?> >
								<?php echo esc_html( $label ); ?>
							</option>
						<?php } ?>
					</select>
				</p>
				<img class="prflxtrflds_table_loader hidden" src="<?php echo esc_url( plugins_url( 'images/loader.gif', __FILE__ ) ); ?>" alt="Loading" />
				<p>
					<select class='prflxtrflds_user_roles hidden' name="prflxtrflds_user_roles" multiple="multiple" style="max-height: 70px; width: 355px;"></select>
					<select class='prflxtrflds_users hidden' name="prflxtrflds_users" multiple="multiple" style="max-height: 55px; width: 355px;"></select>
				</p>
			</div>
			
			<input class="bws_default_shortcode" type="hidden" name="default" value="[prflxtrflds_user_data]" />
			<?php
			$script = "function prflxtrflds_get_shortcode() {
					( function( $ ) {
						var shortcodeType = $( '.mce-reset select[name=\"prflxtrflds_shortcode\"]' ).val();
						if ( 'table' === shortcodeType ) {
							var header = $( '.mce-reset .prflxtrflds_header_table option:selected' ).val();

							var specify = $( '.mce-reset .prflxtrflds_specify option:selected' ).val();

							if ( 'specify_roles' === specify ) {
								var user_role = $( '.mce-reset .prflxtrflds_user_roles option:selected' ).map( function() { return this.value.replace( / /gi, '_' ); } ).get().join( \",\" );
							} else if ( 'specify_users' === specify ) {
								var user = $( '.mce-reset .prflxtrflds_users option:selected' ).map( function(){ return this.value } ).get().join( \",\" );
							} else if ( 'current_user' === specify ) {
								var user = 'get_current_user';
							}

							var shortcode = '[prflxtrflds_user_data';
							if ( user_role ) {
								shortcode = shortcode + ' user_role=' + user_role;
							}
							if ( user ) {
								shortcode = shortcode + ' user_id=' + user;
							}
							if ( header ) {
								shortcode = shortcode + ' display=' + header;
							}
							shortcode = shortcode + ']';
						} else {
							var user = $( '.mce-reset .prflxtrflds_user option:selected' ).val();

							if ( 'specify_user' === user ) {
								var user_id = $( '.mce-reset .prflxtrflds_specify_user option:selected' ).val();
							}

							shortcode = '[prflxtrflds_field';

							if ( user_id ) {
								shortcode = shortcode + ' user_id=' + user_id;
							}
							shortcode = shortcode + ']';
						}

						$( '.mce-reset #bws_shortcode_display' ).text( shortcode );
					} )( jQuery );
				}
				function prflxtrflds_shortcode_init() {
					( function( $ ) {
						$( '.mce-reset select[name=\"prflxtrflds_shortcode\"]' ).on( 'change', function() {
							var shortcodeType = $( this ).val();
							if ( 'table' === shortcodeType ) {
								$( '.mce-reset .prflxtrflds_table_block' ).show();
								$( '.mce-reset .prflxtrflds_field_block' ).hide();

							} else {
								$( '.mce-reset .prflxtrflds_table_block' ).hide();
								$( '.mce-reset .prflxtrflds_field_block' ).show();
								$.ajax( {
									url: ajaxurl,
									type: \"POST\",
									data: 'action=prflxtrflds_get_fields_name&prflxtrflds_ajax_nonce_field=" . wp_create_nonce( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' ) . "',
									success: function( result ) {
										$( '.mce-reset .prflxtrflds_field' ).html( result );
										$( '.mce-reset .prflxtrflds_field :first' ).attr( 'selected' );
										$( '.mce-reset .prflxtrflds_field' ).show();
										prflxtrflds_get_shortcode();
									},
									error: function( request, status, error ) {
										console.log( error + request.status );
									}
								} );
							}
						} ).trigger( 'change' );

						$( '.mce-reset .prflxtrflds_specify' ).on( 'change', function() {
							var specify = $( this ).val();
							if( 'all' === specify || 'gcurrent_user' === specify ) {
								$( '.mce-reset .prflxtrflds_user_roles' ).hide();
								$( '.mce-reset .prflxtrflds_users' ).hide();
							} else if( 'specify_roles' === specify ) {
								$( '.mce-reset .prflxtrflds_users' ).hide();
								if ( $( '.mce-reset .prflxtrflds_table_loader' ).length > 0 ) {
									$( '.mce-reset .prflxtrflds_table_loader' ).show();
									$.ajax( {
										url: ajaxurl,
										type: \"POST\",
										data: 'action=prflxtrflds_get_roles&prflxtrflds_ajax_nonce_field=" . wp_create_nonce( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' ) . "',
										success: function( result ) {
											$( '.mce-reset .prflxtrflds_user_roles' ).html( result );
											$( '.mce-reset .prflxtrflds_user_roles' ).show();
											$( '.mce-reset .prflxtrflds_table_loader' ).hide();
										},
										error: function( request, status, error ) {
											console.log( error + request.status );
										}
									} );
								} else {
									$( '.mce-reset .prflxtrflds_user_roles' ).show();
								}
							} else if ( 'specify_users' === specify ) {
								$( '.mce-reset .prflxtrflds_user_roles' ).hide();
								if ( $( '.mce-reset .prflxtrflds_table_loader' ).length > 0 ) {
									$( '.mce-reset .prflxtrflds_table_loader' ).show();
									$.ajax( {
										url: ajaxurl,
										type: \"POST\",
										data: 'action=prflxtrflds_get_users&prflxtrflds_ajax_nonce_field=" . wp_create_nonce( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' ) . "',
										success: function( result ) {
											$( '.mce-reset .prflxtrflds_users' ).html( result );
											$( '.mce-reset .prflxtrflds_users' ).show();
											$( '.mce-reset .prflxtrflds_table_loader' ).hide();
										},
										error: function( request, status, error ) {
											console.log( error + request.status );
										}
									} );
								} else {
									$( '.mce-reset .prflxtrflds_users' ).show();
								}
							}
						} );

						$( '.mce-reset .prflxtrflds_user' ).on( 'change', function() {
							var user = $( this ).val();
							if ( 'specify_user' === user ){
								$( '.mce-reset .prflxtrflds_specify_user' ).show();
							} else {
								$( '.mce-reset .prflxtrflds_specify_user' ).hide();
							}
						} );

						$( '.mce-reset #prflxtrflds input, .mce-reset #prflxtrflds select' ).on( 'change', function() {
							prflxtrflds_get_shortcode();
						} );
						/** Add specific css for shortcode window */
						$( '.mce-window-body' ).css( 'padding-bottom', '80px' );
						$( '.mce-window' ).css( 'height', '520px' );
					} )( jQuery );
				}";

			wp_register_script( 'prflxtrflds_get_shortcode', '' );
			wp_enqueue_script( 'prflxtrflds_get_shortcode' );
			wp_add_inline_script( 'prflxtrflds_get_shortcode', sprintf( $script ) );
			?>
			<div class="clear"></div>
		</div>
		<?php
	}
}

/** This links under plugin name */
if ( ! function_exists( 'prflxtrflds_plugin_action_links' ) ) {
	function prflxtrflds_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/** Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin ) {
				$this_plugin = plugin_basename( __FILE__ );
			}
			if ( $file === $this_plugin ) {
				$settings_link = '<a href="admin.php?page=profile-extra-fields.php">' . __( 'Settings', 'profile-extra-fields' ) . '</a>';
				array_unshift( $links, $settings_link ); /** Add settings link to begin of array */
			}
		}
		return $links;
	}
}

/** This links in plugin description */
if ( ! function_exists( 'prflxtrflds_register_plugin_links' ) ) {
	function prflxtrflds_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file === $base ) {
			if ( ! is_network_admin() ) {
				$links[] = '<a href="admin.php?page=profile-extra-fields.php">' . __( 'Settings', 'profile-extra-fields' ) . '</a>';
			}
			$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/201146449/">' . __( 'FAQ', 'profile-extra-fields' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'profile-extra-fields' ) . '</a>';
		}
		return $links;
	}
}

/** Add admin notices */
if ( ! function_exists( 'prflxtrflds_admin_notices' ) ) {
	function prflxtrflds_admin_notices() {
		global $hook_suffix, $prflxtrflds_plugin_info;
		if ( 'plugins.php' === $hook_suffix && ! is_network_admin() ) {
			bws_plugin_banner_to_settings( $prflxtrflds_plugin_info, 'prflxtrflds_options', 'profile-extra-fields', 'admin.php?page=profile-extra-fields.php' );
		}
		if ( isset( $_GET['page'] ) && 'profile-extra-fields.php' === $_GET['page'] ) {
			bws_plugin_suggest_feature_banner( $prflxtrflds_plugin_info, 'prflxtrflds_options', 'profile-extra-fields' );
		}
	}
}

/** Register scripts */
if ( ! function_exists( 'prflxtrflds_load_script' ) ) {
	function prflxtrflds_load_script() {
		global $hook_suffix;

		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'profile-extra-fields.php', 'profile-extra-field-add-new.php', 'profile-extra-fields-settings.php' ) ) ) {

			if ( wp_is_mobile() ) {
				wp_enqueue_script( 'jquery-touch-punch' );
			}

			wp_enqueue_script( 'prflxtrflds_script', plugins_url( '/js/script.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ), '1.2.4' );
			$script_vars = array(
				'prflxtrflds_ajax_url' => admin_url( 'admin-ajax.php' ),
				'prflxtrflds_nonce'    => wp_create_nonce( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' ),
			);
			wp_localize_script( 'prflxtrflds_script', 'prflxtrflds_ajax', $script_vars );

			bws_enqueue_settings_scripts();
			bws_plugins_include_codemirror();
		}

		if ( 'user-edit.php' === $hook_suffix || 'profile.php' === $hook_suffix ) {
			prflxtrflds_enqueue_fields_styles();
		}
	}
}

/** Uninstall plugin */
if ( ! function_exists( 'prflxtrflds_uninstall' ) ) {
	function prflxtrflds_uninstall() {
		global $wpdb;
		$all_plugins = get_plugins();
		/** Drop all plugin tables */
		if ( ! array_key_exists( 'profile-extra-fields-pro/profile-extra-fields-pro.php', $all_plugins ) ) {
			$table_names = array(
				'`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`',
				'`' . $wpdb->base_prefix . 'prflxtrflds_field_types`',
				'`' . $wpdb->base_prefix . 'prflxtrflds_field_values`',
				'`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`',
				'`' . $wpdb->base_prefix . 'prflxtrflds_roles_id`',
				'`' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`',
				'`' . $wpdb->base_prefix . 'prflxtrflds_user_roles`',
			);
			$wpdb->query( 'DROP TABLE IF EXISTS ' . implode( ', ', $table_names ) );
			/** Delete options */
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				$old_blog = $wpdb->blogid;
				/** Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					delete_option( 'prflxtrflds_options' );
				}
				switch_to_blog( $old_blog );
			} else {
				delete_option( 'prflxtrflds_options' );
			}

			require_once dirname( __FILE__ ) . '/bws_menu/bws_include.php';
			bws_include_init( plugin_basename( __FILE__ ) );
			bws_delete_plugin( plugin_basename( __FILE__ ) );
		}
	}
}

if ( ! function_exists( 'prflxtrflds_get_fields' ) ) {
	function prflxtrflds_get_fields( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'roles'    => array(),
			'visible'  => false,
			'editable' => false,
			'required' => false,
			'show'     => false,
			'exclude'  => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$where = '';

		if ( ! empty( $args['roles'] ) && is_array( $args['roles'] ) ) {
			$roles  = implode( '", "', $args['roles'] );
			$where .= 'AND `' . $wpdb->base_prefix . "prflxtrflds_roles_id`.`role` IN ( '" . $roles . "' ) ";
		}

		if ( false !== $args['visible'] ) {
			$where .= 'AND `' . $wpdb->base_prefix . "prflxtrflds_roles_and_fields`.`visible`='" . absint( $args['visible'] ) . "' ";
		}

		if ( false !== $args['editable'] ) {
			$where .= 'AND `' . $wpdb->base_prefix . "prflxtrflds_roles_and_fields`.`editable`='" . absint( $args['editable'] ) . "' ";
		}

		if ( false !== $args['required'] ) {
			$where .= 'AND `' . $wpdb->base_prefix . "prflxtrflds_fields_id`.`required` != '' ";
		}

		if ( false !== $args['show'] ) {
			$exclude = $args['exclude'] ? 'NOT' : '';

			if ( is_array( $args['show'] ) ) {
				$fields        = implode( "', '", $args['show'] );
				$where_show_in = "IN ( '" . $fields . "' )";
			} else {
				$where_show_in = "= '" . $args['show'] . "'";
			}

			$where .= 'AND `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_id` ' . $exclude . ' IN (
				SELECT `' . $wpdb->base_prefix . 'prflxtrflds_fields_meta`.`field_id`
				FROM `' . $wpdb->base_prefix . 'prflxtrflds_fields_meta`
				WHERE `' . $wpdb->base_prefix . 'prflxtrflds_fields_meta`.`show_in` ' . $where_show_in . ' AND `' . $wpdb->base_prefix . 'prflxtrflds_fields_meta`.`value` != ""
			)';
		}

		$sql_query = 'SELECT
			`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_id`,
			`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_name`,
			`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`required`,
			`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`description`,
			`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_type_id`,
			`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`field_order`,
			`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`editable`,
			`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`visible`,
			`' . $wpdb->base_prefix . 'prflxtrflds_field_values`.`value_name`
		FROM
			`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`,
			`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`,
			`' . $wpdb->base_prefix . 'prflxtrflds_roles_id`,
			`' . $wpdb->base_prefix . 'prflxtrflds_field_values`
		WHERE
			`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`role_id`= `' . $wpdb->base_prefix . 'prflxtrflds_roles_id`.`role_id`
			AND `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`field_id`=`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_id`
			AND `' . $wpdb->base_prefix . 'prflxtrflds_field_values`.`field_id`=`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_id`
			' . $where . '
		GROUP BY `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`field_id`
		ORDER BY `' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`field_order` ASC,
				`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`.`field_id` ASC';
		$entries = $wpdb->get_results( $sql_query, ARRAY_A );

		if ( ! $entries ) {
			/* If data for current role not exists, update table and try again */
			prflxtrflds_update_roles_id();
			$entries = $wpdb->get_results( $sql_query, ARRAY_A );
		}

		return $entries;
	}
}

/*this function show fields in registration form*/
if ( ! function_exists( 'prflxtrflds_get_field_html' ) ) {
	function prflxtrflds_get_field_html( $field_data = array(), $name = 'prflxtrflds_user_field_value', $atts = array(), $echo = false ) {
		$field_types = array(
			'1'  => 'text',
			'2'  => 'textarea',
			'3'  => 'checkbox',
			'4'  => 'radio',
			'5'  => 'select',
			'6'  => 'date',
			'7'  => 'time',
			'8'  => 'datetime',
			'9'  => 'number',
			'10' => 'phone',
			'11' => 'url',
		);
		$html        = $rows = $cols = $max_length_textarea = '';

		$value = ( isset( $field_data['user_value'] ) ) ? $field_data['user_value'] : '';
		if ( '' === $value && isset( $_POST[ $name ][ $field_data['field_id'] ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST[ $name ][ $field_data['field_id'] ] ) );
		}
		$max_length = ( isset( $field_data['available_fields'][0]['value_name'] ) ) ? 'maxlength="' . $field_data['available_fields'][0]['value_name'] . '"' : '';

		if ( 2 === intval( $field_data['field_type_id'] ) ) {
			$unser_textarea      = maybe_unserialize( $field_data['available_fields'][0]['value_name'] );
			$rows                = $unser_textarea['rows'];
			$cols                = $unser_textarea['cols'];
			$max_length_textarea = $unser_textarea['max_length'];
		}

		if ( 9 === intval( $field_data['field_type_id'] ) ) {
			$max_length_number = ( isset( $field_data['available_fields'][0]['value_name'] ) ) ? 'max="' . $field_data['available_fields'][0]['value_name'] . '"' : '';
		}

		$editable_attr  = implode( ' ', $atts );
		$editable_attr .= disabled( empty( $field_data['editable'] ), true, false );

		$required_attr = ( ! empty( $field_data['required'] ) && ! empty( $field_data['editable'] ) && ! isset( $field_data['gravity_form'] ) ) ? ' required="required"' : '';

		if ( 'text' === $field_types[ $field_data['field_type_id'] ] ) {
			$html = sprintf(
				'<input type="text" name="%1$s[%2$s]" value="%3$s" %4$s %5$s %6$s>',
				$name,
				$field_data['field_id'],
				$value,
				$max_length,
				$editable_attr,
				$required_attr
			);
		}

		if ( 'textarea' === $field_types[ $field_data['field_type_id'] ] ) {
			$html = sprintf(
				'<textarea name="%1$s[%2$s]" rows="%3$s" cols="%4$s" maxlength="%5$s" %6$s %7$s>%8$s</textarea>',
				$name,
				$field_data['field_id'],
				$rows,
				$cols,
				$max_length_textarea,
				$editable_attr,
				$required_attr,
				$value
			);
		}

		if ( 'checkbox' === $field_types[ $field_data['field_type_id'] ] ) {
			if ( is_array( $field_data['available_fields'] ) ) {
				foreach ( $field_data['available_fields'] as $key => $checkbox_data ) {
					$html .= sprintf(
						'<label><input class="prflxtrflds_input_checkbox" type="checkbox" name="%1$s[%2$s][' . $key . ']" value="%3$s" %4$s %5$s %6$s />%7$s</label><br />',
						$name,
						$field_data['field_id'],
						$checkbox_data['value_id'],
						$editable_attr,
						$required_attr,
						checked( ! empty( $value ) && is_array( $value ) && in_array( $checkbox_data['value_id'], $value ), true, false ),
						$checkbox_data['value_name']
					);
				}
			}
		}

		if ( 'radio' === $field_types[ $field_data['field_type_id'] ] ) {
			if ( is_array( $field_data['available_fields'] ) ) {
				foreach ( $field_data['available_fields'] as $key => $radio_data ) {
					$html .= sprintf(
						'<label><input class="prflxtrflds_input_radio" type="radio" name="%1$s[%2$s]" value="%3$s" %4$s %5$s %6$s >%7$s</label><br />',
						$name,
						$field_data['field_id'],
						$radio_data['value_id'],
						$editable_attr,
						$required_attr,
						checked( ! empty( $value ) && $value === $radio_data['value_id'], true, false ),
						$radio_data['value_name']
					);
				}
			}
		}

		if ( 'select' === $field_types[ $field_data['field_type_id'] ] ) {
			if ( is_array( $field_data['available_fields'] ) ) {
				$html = sprintf(
					'<select name="%1$s[%2$s]" %3$s><option></option>',
					$name,
					$field_data['field_id'],
					$editable_attr
				);
				foreach ( $field_data['available_fields'] as $key => $option_data ) {
					$html .= sprintf(
						'<option value="%1$s" %2$s>%3$s</option><br />',
						$option_data['value_id'],
						selected( ! empty( $value ) && $value === $option_data['value_id'], true, false ),
						$option_data['value_name']
					);
				}
				$html .= '</select>';
			}
		}

		if ( 'date' === $field_types[ $field_data['field_type_id'] ] ) {
			foreach ( $field_data['available_fields'] as $key => $date_data ) {
				$html = sprintf(
					'<input class="prflxtrflds_datetimepicker" type="text" name="%1$s[%2$s]" %3$s %4$s value="%6$s">
					 <input type="hidden" name="prflxtrflds_date_format" value="%5$s">
					 <input type="hidden" name="prflxtrflds_user_field_datetime[%2$s]" value="%5$s">',
					$name,
					$field_data['field_id'],
					$editable_attr,
					$required_attr,
					$date_data['value_name'],
					$value
				);
			}
		}

		if ( 'time' === $field_types[ $field_data['field_type_id'] ] ) {
			$time_data = array();
			foreach ( $field_data['available_fields'] as $key => $time_data ) {
				$time_value[] = $time_data;
				$html         = sprintf(
					'<input class="prflxtrflds_datetimepicker" type="text" name="%1$s[%2$s]" %3$s %4$s value="%6$s">
					 <input type="hidden" name="prflxtrflds_time_format" value="%5$s">
					 <input type="hidden" name="prflxtrflds_user_field_datetime[%2$s]" value="%5$s">',
					$name,
					$field_data['field_id'],
					$editable_attr,
					$required_attr,
					$time_value[ $key ]['value_name'],
					$value
				);
			}
		}

		if ( 'datetime' === $field_types[ $field_data['field_type_id'] ] ) {
			foreach ( $field_data['available_fields'] as $key => $time_data ) {
				$datetime_format = maybe_unserialize( $time_data['value_name'] );
				$html            = sprintf(
					'<input class="prflxtrflds_datetimepicker" type="text" name="%1$s[%2$s]" %3$s %4$s value="%7$s">
					 <input type="hidden" name="prflxtrflds_time_format" value="%6$s">
					 <input type="hidden" name="prflxtrflds_date_format" value="%5$s">
					 <input type="hidden" name="prflxtrflds_user_field_datetime[%2$s]" value="%5$s %6$s">',
					$name,
					$field_data['field_id'],
					$editable_attr,
					$required_attr,
					$datetime_format['date'],
					$datetime_format['time'],
					$value
				);
			}
		}

		if ( 'number' === $field_types[ $field_data['field_type_id'] ] ) {
			foreach ( $field_data['available_fields'] as $key => $number_data ) {
				$html = sprintf(
					'<input type="number" class="prflxtrflds_number" name="%1$s[%2$s]" %3$s %4$s %5$s value="%6$s"/>
					<input type="hidden" name="prflxtrflds_user_field_max_number[%2$s]" value="%7$s">',
					$name,
					$field_data['field_id'],
					$max_length_number,
					$editable_attr,
					$required_attr,
					$value,
					$number_data['value_name']
				);
			}
		}

		if ( 'phone' === $field_types[ $field_data['field_type_id'] ] ) {
			foreach ( $field_data['available_fields'] as $key => $phone_data ) {
				$html = sprintf(
					'<input type="text" class="prflxtrflds_phone" name="%1$s[%2$s]" %4$s %5$s value="%6$s"/>
					<input type="hidden" name="prflxtrflds_user_field_pattern[%2$s]" value="%3$s">',
					$name,
					$field_data['field_id'],
					$phone_data['value_name'],
					$editable_attr,
					$required_attr,
					$value
				);
			}
		}

		if ( 'url' === $field_types[ $field_data['field_type_id'] ] ) {
			$html = sprintf(
				'<input type="text" class="medium" name="%1$s[%2$s]" ',
				$name,
				$field_data['field_id'],
				$value,
				$max_length,
				$editable_attr,
				$required_attr
			);
		}

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}
}

/*this function show fields in registration form*/
if ( ! function_exists( 'prflxtrflds_user_profile_fields_in_register_form' ) ) {
	function prflxtrflds_user_profile_fields_in_register_form() {

		if ( ! is_multisite() ) {

				global $wpdb, $hook_suffix;

				$role = get_option( 'default_role' );

				$args      = array(
					'roles' => array( $role ),
					'show'  => 'register_form',
				);
				$all_entry = prflxtrflds_get_fields( $args );

				/** Group result array by field_id */
				foreach ( $all_entry as $key => $one_entry ) {
					/** Add field values */
					$all_entry[ $key ]['available_fields'] = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT `value_id`, `value_name`
						FROM `' . $wpdb->base_prefix . 'prflxtrflds_field_values`
						WHERE `field_id` = %d ORDER BY `order`',
							$one_entry['field_id']
						),
						ARRAY_A
					);
				}
				?>
				
				<!-- Begin code from user role extra field -->
				<table class="form-table">
					<?php wp_nonce_field( 'prflxtrflds_user_register_action', 'prflxtrflds_user_register_field' ); ?>
					<?php
					foreach ( $all_entry as $one_entry ) {
						if (
							empty( $one_entry['editable'] ) ||
							empty( $one_entry['visible'] )
						) {
							echo '<input type="hidden" name="prflxtrflds_not_editable[]" value="' . esc_attr( $one_entry['field_id'] ) . '" />';
						} else {
							?>
							<tr>
								<input type="hidden"
										name="prflxtrflds_field_name[<?php echo esc_attr( $one_entry['field_id'] ); ?>]"
										value="<?php echo esc_attr( $one_entry['field_name'] ); ?>">
								<p>
										<label>
											<?php
											esc_html_e( $one_entry['field_name'], 'profile-extra-fields' );
											if ( ! empty( $one_entry['required'] ) ) {
												echo esc_attr( $one_entry['required'] );
											}
											?>
										</label>
										<br/>
									<?php prflxtrflds_get_field_html( $one_entry, 'prflxtrflds_user_field_value', array(), true ); ?>
								</p>
								<br />
							</tr>
							<?php
						}
					}
					?>
				</table><!--.form-table-->

			<?php
		}

	}
}

/** Connecting CSS styles to the registration form. */
if ( ! function_exists( 'prflxtrflds_login_enqueue_scripts' ) ) {
	function prflxtrflds_login_enqueue_scripts() {
		if ( isset( $_GET['action'] ) && 'register' === $_GET['action'] ) {
			wp_enqueue_style( 'prflxtrflds_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), '1.2.4' );

			prflxtrflds_enqueue_fields_styles();
		}
	}
}
if ( ! function_exists( 'prflxtrflds_enqueue_scripts' ) ) {
	function prflxtrflds_enqueue_scripts() {
		wp_enqueue_style( 'prflxtrflds_front_stylesheet', plugins_url( 'css/front_style.css', __FILE__ ), array(), '1.2.4' );
	}
}
if ( ! function_exists( 'prflxtrflds_enqueue_fields_styles' ) ) {
	function prflxtrflds_enqueue_fields_styles() {
		wp_enqueue_style( 'jquery.datetimepicker.css', plugins_url( 'css/jquery.datetimepicker.css', __FILE__ ), array(), '1.2.4' );

		wp_enqueue_script( 'jquery.datetimepicker.full.min.js', plugins_url( '/js/jquery.datetimepicker.full.min.js', __FILE__ ), array( 'jquery' ), '1.2.4', true );

		wp_enqueue_script( 'inputmask.js', plugins_url( '/js/inputmask.js', __FILE__ ), array(), '1.2.4', true );
		wp_enqueue_script( 'jquery.inputmask.js', plugins_url( '/js/jquery.inputmask.js', __FILE__ ), array(), '1.2.4', true );

		wp_enqueue_script( 'prflxtrflds_profile_script', plugins_url( '/js/profile_script.js', __FILE__ ), array(), '1.2.4', true );
		$script_vars = array(
			'prflxtrflds_nonce' => wp_create_nonce( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' ),
		);
		wp_localize_script( 'prflxtrflds_profile_script', 'prflxtrflds_vars', $script_vars );
	}
}

/** Save user data from register form */
if ( ! function_exists( 'prflxtrflds_save_data_from_registration_form' ) ) {
	function prflxtrflds_save_data_from_registration_form( $user_id ) {
		if ( 0 !== $user_id ) {

			global $wpdb;

			if ( ( isset( $_POST['prflxtrflds_user_register_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_register_field'] ) ), 'prflxtrflds_user_register_action' ) ) || ( isset( $_POST['prflxtrflds_user_fields'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_fields'] ) ), 'prflxtrflds_user_field_action' ) ) ) {

				/** If array exists ( exist available fields for current user ), remove old data */
				if ( ! empty( $_POST['prflxtrflds_not_editable'] ) ) {
					/** Execute not_editable fields */
					$prflxtrflds_not_editable = array_map( 'intval', $_POST['prflxtrflds_not_editable'] );
					$not_editable_ids         = "'" . implode( "','", $prflxtrflds_not_editable ) . "'";

					$wpdb->query(
						$wpdb->prepare(
							'DELETE FROM `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`
						WHERE `user_id` = %d
							AND `field_id` NOT IN (' . $not_editable_ids . ')',
							$user_id
						)
					);
				} else {
					$wpdb->delete(
						$wpdb->base_prefix . 'prflxtrflds_user_field_data',
						array( 'user_id' => $user_id )
					);
				}

				/** Create array with user values */
				if ( isset( $_POST['prflxtrflds_user_field_value'] ) ) {
					foreach ( $_POST['prflxtrflds_user_field_value'] as $id => $val ) {
						if ( ! empty( $val ) ) {
							if ( is_array( $val ) ) {
								/** For checkboxes */
								foreach ( $val as $user_value ) {
									/** Insert or update value */
									$wpdb->replace(
										$wpdb->base_prefix . 'prflxtrflds_user_field_data',
										array(
											'user_id'    => $user_id,
											'field_id'   => intval( $id ),
											'user_value' => stripslashes( sanitize_text_field( wp_unslash( $user_value ) ) ),
										)
									);
								}
							} else {
								$user_value = stripslashes( sanitize_text_field( wp_unslash( $val ) ) );
								/** Insert or update value */
								$wpdb->replace(
									$wpdb->base_prefix . 'prflxtrflds_user_field_data',
									array(
										'user_id'    => $user_id,
										'field_id'   => $id,
										'user_value' => $user_value,
									)
								);
							}
						}
					}
				}
			}
		}
	}
}

/** Form validation */
if ( ! function_exists( 'prflxtrflds_register_check' ) ) {
	function prflxtrflds_register_check( $allow ) {
		global $wpdb;

		$role = get_option( 'default_role' );

		$args             = array(
			'roles'    => array( $role ),
			'visible'  => 1,
			'editable' => 1,
			'required' => true,
			'show'     => 'register_form',
		);
		$required_entries = prflxtrflds_get_fields( $args );
		$error_fields     = array();

		if ( ! empty( $required_entries ) ) {
			foreach ( $required_entries as $entry ) {
				if ( empty( $_POST['prflxtrflds_user_field_value'][ $entry['field_id'] ] ) ) {
					$error_fields[] = $entry['field_name'];
				}
			}
		}

		if ( ! empty( $error_fields ) ) {
			$message = sprintf(
				'%1$s %2$s: %3$s',
				__( 'Please fill all required fields.', 'profile-extra-fields' ),
				__( 'The following fields are required', 'profile-extra-fields' ),
				implode( ', ', $error_fields )
			);
			$allow   = new WP_Error( 'prflxtrflds_error', $message );
		}

		return $allow;
	}
}

/** Add css styles to the admin panel */
function prflxtrflds_admin_style() {
	wp_enqueue_style( 'prflxtrflds_style_admin', plugins_url( 'css/style.css', __FILE__ ), array(), '1.2.4' );
}

/** Send errors to registration user form */
if ( ! function_exists( 'prflxtrflds_register_error' ) ) {
	function prflxtrflds_register_error( $errors, $update = null, $user = null ) {
		$required_array = array();

		if ( isset( $_POST['prflxtrflds_user_register_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_register_field'] ) ), 'prflxtrflds_user_register_action' ) ) {

			if ( ! empty( $_POST['prflxtrflds_required'] ) ) {
				/** Get all reqired ids */
				foreach ( $_POST['prflxtrflds_required'] as $required_id => $required_value ) {
					if ( empty( $_POST['prflxtrflds_user_field_value'][ $required_id ] ) ) {
						/** Error for non-textfield */
						$name = isset( $_POST['prflxtrflds_field_name'][ $required_id ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_field_name'][ $required_id ] ) ) : '';
						$errors->add( 'prflxtrflds_required_error', sprintf( __( 'Required field %1$s is not filled. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $name . '</strong>' ) );
						$required_array[] = $required_id;
					}
				}
			}
			if ( ! empty( $_POST['prflxtrflds_user_field_pattern'] ) ) {
				foreach ( $_POST['prflxtrflds_user_field_pattern'] as $field_id => $pattern ) {
					if ( ! empty( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) && ! in_array( $field_id, $required_array ) ) {
						if ( ! preg_match( '/^' . str_replace( '\*', '[0-9]', preg_quote( $pattern ) ) . '$/', sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) ) ) ) {
							$name = isset( $_POST['prflxtrflds_field_name'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_field_name'][ $field_id ] ) ) : '';
							$errors->add( 'prflxtrflds_match_error', sprintf( __( 'Field %1$s does not match %2$s. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $name . '</strong>', '<strong>' . $pattern . '</strong>' ) );
						}
					}
				}
			}

			if ( ! empty( $_POST['prflxtrflds_user_field_max_number'] ) ) {
				foreach ( $_POST['prflxtrflds_user_field_max_number'] as $field_id => $max_number ) {
					if ( ! empty( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) &&
						! in_array( $field_id, $required_array )
					) {
						$max_number = intval( $max_number );
						if ( $max_number > 0 &&
							intval( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) > $max_number
						) {
							$_POST['prflxtrflds_user_field_value'][ $field_id ] = $max_number;
						}
					}
				}
			}

			if ( ! empty( $_POST['prflxtrflds_user_field_datetime'] ) ) {
				foreach ( $_POST['prflxtrflds_user_field_datetime'] as $field_id => $pattern ) {
					if ( ! empty( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) &&
						! in_array( $field_id, $required_array )
					) {
						$pattern = trim( str_replace( 'T', '', $pattern ) );
						if ( function_exists( 'date_create_from_format' ) ) {
							$d = date_create_from_format( $pattern, sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) ) );
							if ( ! $d || ! $d->format( $pattern ) === $_POST['prflxtrflds_user_field_value'][ $field_id ] ) {
								$name = isset( $_POST['prflxtrflds_field_name'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_field_name'][ $field_id ] ) ) : '';
								$errors->add( 'prflxtrflds_match_error', sprintf( __( 'Field %1$s does not match %2$s. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $name . '</strong>', '<strong>' . $pattern . '</strong>' ) );
							}
						} elseif ( ! strtotime( sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) ) ) ) {
							$name = isset( $_POST['prflxtrflds_field_name'][ $field_id ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_field_name'][ $field_id ] ) ) : '';
							$errors->add( 'prflxtrflds_match_error', sprintf( __( 'Field %1$s does not match %2$s. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $name . '</strong>', '<strong>' . $pattern . '</strong>' ) );
						}
					}
				}
			}
		}

		return $errors;
	}
}

if ( ! function_exists( 'prflxtrflds_display_front_script' ) ) {
	function prflxtrflds_display_front_script(){
		global $prflxtrflds_front_shortcode;
		if ( true === $prflxtrflds_front_shortcode ) {
			wp_enqueue_style( 'jquery.datetimepicker.css', plugins_url( 'css/jquery.datetimepicker.css', __FILE__ ), array(), '1.2.4' );

			wp_enqueue_script( 'jquery.datetimepicker.full.min.js', plugins_url( '/js/jquery.datetimepicker.full.min.js', __FILE__ ), array( 'jquery' ), '1.2.4', true );

			wp_enqueue_script( 'inputmask.js', plugins_url( '/js/inputmask.js', __FILE__ ), array(), '1.2.4', true );
			wp_enqueue_script( 'jquery.inputmask.js', plugins_url( '/js/jquery.inputmask.js', __FILE__ ), array(), '1.2.4', true );

			wp_enqueue_script( 'prflxtrflds_profile_script', plugins_url( '/js/profile_script.js', __FILE__ ), array(), '1.2.4', true );
		}
	}
}

if ( ! function_exists( 'prflxtrflds_wp_new_user_notification_email_admin' ) ) {
	function prflxtrflds_wp_new_user_notification_email_admin( $wp_new_user_notification_email_admin ) {
		if ( isset( $_POST['prflxtrflds_field_name'], $_POST['prflxtrflds_user_field_value'] ) ) {
			$wp_new_user_notification_email_admin['message'] .= "\r\n";
			$prflxtrflds_field_name                           = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST['prflxtrflds_field_name'] ) );
			foreach ( $prflxtrflds_field_name as $key => $name ) {
				$wp_new_user_notification_email_admin['message'] .= $name . ': ' . ( isset( $_POST['prflxtrflds_user_field_value'][ $key ] ) ? sanitize_text_field( wp_unslash( $_POST['prflxtrflds_user_field_value'][ $key ] ) ) : '' ) . "\r\n\r\n";
			}
		}

		return $wp_new_user_notification_email_admin;
	}
}

register_activation_hook( __FILE__, 'prflxtrflds_activation' );
/** Add css styles to the admin panel */
add_action( 'admin_head', 'prflxtrflds_admin_style' );
/** Bws menu */
add_action( 'admin_menu', 'prflxtrflds_admin_menu' );
/** Plugin init */
add_action( 'init', 'prflxtrflds_init' );
add_action( 'admin_init', 'prflxtrflds_admin_init' );
add_action( 'plugins_loaded', 'prflxtrflds_plugins_loaded' );
add_filter( 'set-screen-option', 'prflxtrflds_set_screen_options', 10, 3 );
/** This links under plugin name */
add_filter( 'plugin_action_links', 'prflxtrflds_plugin_action_links', 10, 2 );
/** This links in plugin description */
add_filter( 'plugin_row_meta', 'prflxtrflds_register_plugin_links', 10, 2 );
/** Add admin notices */
add_action( 'admin_notices', 'prflxtrflds_admin_notices' );
/** Add basic shortcode*/
add_shortcode( 'prflxtrflds_user_data', 'prflxtrflds_show_data' );
add_shortcode( 'prflxtrflds_field', 'prflxtrflds_show_field' );
add_shortcode( 'prflxtrflds_user_data_edit_form', 'prflxtrflds_show_edit_form' );
add_filter( 'widget_text', 'do_shortcode' );
/** Update table if user create */
add_action( 'user_register', 'prflxtrflds_update_user_roles', 10, 2 );
/** Update table on edit user profile */
add_action( 'profile_update', 'prflxtrflds_update_user_roles', 10, 2 );
/** Update on set user role */
add_action( 'set_user_role', 'prflxtrflds_update_user_roles', 10, 2 );
/** Show info in user profile page*/
add_action( 'show_user_profile', 'prflxtrflds_fields_table' );
add_action( 'edit_user_profile', 'prflxtrflds_fields_table' );
/** Add custom fields to the user registration form */
add_action( 'user_new_form', 'prflxtrflds_fields_table' );
/** Save user information where Save button is pressed */
add_action( 'edit_user_profile_update', 'prflxtrflds_save_user_data' );
add_action( 'personal_options_update', 'prflxtrflds_save_user_data' );
add_filter( 'bws_bkng_billing_data', 'prflxtrflds_save_booking_fields' );
add_filter( 'bws_bkng_order_errors', 'prflxtrflds_add_error_message', 10, 2 );
/** Load scripts */
add_action( 'admin_enqueue_scripts', 'prflxtrflds_load_script' );
/** Check fields from user settings page */
add_filter( 'user_profile_update_errors', 'prflxtrflds_create_user_error' );
/** Adding fields to the admin email after registering a new user */
add_filter( 'wp_new_user_notification_email_admin', 'prflxtrflds_wp_new_user_notification_email_admin' );
/** Save order through ajax */
add_action( 'wp_ajax_prflxtrflds_table_order', 'prflxtrflds_table_order' );
add_action( 'wp_ajax_prflxtrflds_get_users', 'prflxtrflds_get_users' );
add_action( 'wp_ajax_prflxtrflds_get_roles', 'prflxtrflds_get_roles' );
add_action( 'wp_ajax_prflxtrflds_get_fields_name', 'prflxtrflds_get_fields_name' );
/** Custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'prflxtrflds_shortcode_button_content' );
/** Add fields to the user registration form */
add_action( 'register_form', 'prflxtrflds_user_profile_fields_in_register_form' );
/** Connecting CSS styles to the registration form. */
add_action( 'login_enqueue_scripts', 'prflxtrflds_login_enqueue_scripts', 1 );
add_action( 'wp_enqueue_scripts', 'prflxtrflds_enqueue_scripts' );
/** Save user data from register form */
add_action( 'user_register', 'prflxtrflds_save_data_from_registration_form' );
/** Form validation */
add_filter( 'registration_errors', 'prflxtrflds_register_check', 10, 1 );
add_filter( 'registration_errors', 'prflxtrflds_register_error' );
/** Hook for display script in footer */
add_action( 'wp_footer', 'prflxtrflds_display_front_script' );
