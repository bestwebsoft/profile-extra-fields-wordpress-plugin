<?php
/*
Plugin Name: Profile Extra Fields by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: Plugin Profile Extra Fields add extra data to user profile page.
Author: BestWebSoft
Text Domain: profile-extra-fields
Domain Path: /languages
Version: 1.0.2
Author URI: http://bestwebsoft.com/
License: GPLv3 or later
*/

/*  @ Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

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
* Add Wordpress page 'bws_plugins' and sub-page of this plugin to admin-panel.
* @return void
*/
/*add settings page in bws menu*/
if ( ! function_exists( 'prflxtrflds_admin_menu' ) ) {
	function prflxtrflds_admin_menu() {
		bws_general_menu();
		$hook = add_submenu_page( 'bws_plugins', __( 'Profile Extra Fields Settings', 'profile-extra-fields' ), 'Profile Extra Fields', 'manage_options', 'profile-extra-fields.php', 'prflxtrflds_settings_page' );
		add_action( "load-$hook", 'prflxtrflds_screen_options' );
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
 * plugin init
 */
if ( ! function_exists ( 'prflxtrflds_init' ) ) {
	function prflxtrflds_init() {
		global $prflxtrflds_plugin_info;		
		/*add bws menu. use in prflxtrflds_admin_menu*/
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		/* Get plugin data */
		if ( empty( $prflxtrflds_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$prflxtrflds_plugin_info = get_plugin_data( __FILE__ );
		}
		/* Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $prflxtrflds_plugin_info, '3.8', '3.8' );

		/* Call register settings function */
		if ( ! is_admin() || ( isset( $_GET['page'] ) && 'profile-extra-fields.php' == $_GET['page'] ) )
			prflxtrflds_settings();
	}
}

/* admin init */
if ( ! function_exists ( 'prflxtrflds_admin_init' ) ) {
	function prflxtrflds_admin_init() {
		global $bws_plugin_info, $prflxtrflds_plugin_info, $bws_shortcode_list;
		/* Add variable for bws_menu */
		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '300', 'version' => $prflxtrflds_plugin_info["Version"] );

		/* add gallery to global $bws_shortcode_list  */
		$bws_shortcode_list['prflxtrflds'] = array( 'name' => 'Profile Extra Fields', 'js_function' => 'prflxtrflds_shortcode_init' );
	}
}

/* update new users and roles */
if ( ! function_exists( 'prflxtrflds_update_users' ) ) {
	function prflxtrflds_update_users() {
		global $wpdb;
		$users_data_from_db = $wpdb->get_results( "SELECT `id`, `role` FROM " . $wpdb->base_prefix . "prflxtrflds_user_data", ARRAY_A );
		if ( $users_data_from_db ) {
			$all_user_in_db = array();
			foreach ( $users_data_from_db as $user ) {
				/* convert to 2D-array */
				$all_user_in_db[ $user['id'] ] = $user['role'];
			}
		}
		/* get actual wordpress data */
		$users = get_users();
		if ( $users ) {
			foreach ( $users as $user ) {
				/*write user id and role*/
				if ( ! isset( $all_user_in_db ) || ! array_key_exists( $user->ID, $all_user_in_db ) ) {
					/* $all_user_in_db not exist if database empty */
					$wpdb->insert( $wpdb->base_prefix . "prflxtrflds_user_data", array( 'userid' => $user->ID, 'role' => implode( ', ', $user->roles ) ) );
				}
			}
		}
	}
}

/* this is settings functions */
if ( ! function_exists( 'prflxtrflds_settings' ) ) {
	function prflxtrflds_settings() {
		global $prflxtrflds_field_type_id, $prflxtrflds_options, $prflxtrflds_plugin_info, $prflxtrflds_option_defaults;
		/* Conformity between field type id and field type name */
		if ( empty( $prflxtrflds_field_type_id ) ) {
			$prflxtrflds_field_type_id = array(
				'1' => __( 'Textfield', 'profile-extra-fields' ),
				'2' => __( 'Checkbox', 'profile-extra-fields' ),
				'3' => __( 'Radiobutton', 'profile-extra-fields' ),
				'4' => __( 'Drop down list', 'profile-extra-fields' ),
				'5' => __( 'Date', 'profile-extra-fields' ),
				'6' => __( 'Time', 'profile-extra-fields' ),
				'7' => __( 'Datetime', 'profile-extra-fields' ),
				'8' => __( 'Number', 'profile-extra-fields' ),
				'9' => __( 'Phone number', 'profile-extra-fields' )
			);
		}
		/* Db version in plugin */
		$prflxtrflds_db_version = '1.1';
		/* Create array with default options */
		$prflxtrflds_option_defaults = array(
			'sort_sequence'				=> 'ASC',
			'available_fields'			=> array(),
			'available_values'			=> array(),
			'show_empty_columns'		=> 0,
			'show_id'	            	=> 1,
			'header_table'          	=> 'top',
			'empty_value'				=> __( 'Field not filled', 'profile-extra-fields' ),
			'not_available_message'		=> __( 'N/A', 'profile-extra-fields' ),
			'plugin_db_version'			=> $prflxtrflds_db_version,
			'plugin_option_version'		=> $prflxtrflds_plugin_info["Version"],
			'display_settings_notice'	=>	1
		);
		/* In prflxtrflds_settings_page add hidden field to save values after option update (!) */
		if ( ! get_option( 'prflxtrflds_options' ) ) {
			/* Set default options */
			add_option( 'prflxtrflds_options', $prflxtrflds_option_defaults );
		}
		/* Get options from database */
		$prflxtrflds_options = get_option( 'prflxtrflds_options' );
		/* Update options if other option version */
		if ( ! isset( $prflxtrflds_options['plugin_option_version'] ) || $prflxtrflds_options['plugin_option_version'] != $prflxtrflds_plugin_info["Version"] ) {
			/* update to 1.0.1 */
			if ( 'asc' == $prflxtrflds_options['sort_sequence'] )
				$prflxtrflds_options['sort_sequence'] = 'ASC';
			elseif ( 'desc' == $prflxtrflds_options['sort_sequence'] )
				$prflxtrflds_options['sort_sequence'] = 'DESC';
			if ( $prflxtrflds_options['header_table'] == 'left' || $prflxtrflds_options['header_table'] == 'right' )
				$prflxtrflds_options['header_table'] = 'side';
			foreach ( $prflxtrflds_options['available_values'] as $key => $value ) {
			 	if ( '-1' == $value )
			 		unset( $prflxtrflds_options['available_values'][ $key ] );
			 } 

			$prflxtrflds_option_defaults['display_settings_notice'] = 0;
			$prflxtrflds_options = array_merge( $prflxtrflds_option_defaults, $prflxtrflds_options );
			$prflxtrflds_options['plugin_option_version'] = $prflxtrflds_plugin_info["Version"];
			$update_option = true;
		}
		/* Update database */
		if ( ! isset( $prflxtrflds_options['plugin_db_version'] ) || $prflxtrflds_options['plugin_db_version'] != $prflxtrflds_db_version ) {
			prflxtrflds_update_table();
			$prflxtrflds_options['plugin_db_version'] = $prflxtrflds_db_version;
			$update_option = true;
		}
		/* If option was updated */
		if ( isset( $update_option ) )
			update_option( 'prflxtrflds_options', $prflxtrflds_options );
	}
}

if ( ! function_exists( 'prflxtrflds_create_table' ) ) {
	function prflxtrflds_create_table() {
		global $wpdb;

		/* require db Delta */
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		/* create table for roles types */
		$sql = "CREATE TABLE " . $wpdb->base_prefix . "prflxtrflds_roles_id (
				role_id bigint(20) NOT NULL AUTO_INCREMENT,
				role text NOT NULL COLLATE utf8_general_ci,
				UNIQUE KEY  (role_id)
				);";
		/* call dbDelta */
		dbDelta( $sql );

		/* Create roles id */
		if ( function_exists( 'prflxtrflds_update_roles_id' ) ) {
			prflxtrflds_update_roles_id();
		}

		/* create table for conformity user_id and user role id */
		$sql = "CREATE TABLE " . $wpdb->base_prefix . "prflxtrflds_user_roles (
			user_id bigint(20) NOT NULL,
			role_id bigint(20) NOT NULL,
			UNIQUE KEY (user_id)
			);";
		/* call dbDelta */
		dbDelta( $sql );
		/* Create roles id */
		if ( function_exists( 'prflxtrflds_update_user_roles' ) ) {
			prflxtrflds_update_user_roles();
		}

		$sql = "CREATE TABLE " . $wpdb->base_prefix . "prflxtrflds_fields_id (
			`field_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`field_name` text NOT NULL COLLATE utf8_general_ci,
			`required` int(1) NOT NULL DEFAULT '0',
			`show_default` int(1) NOT NULL DEFAULT '0',
			`show_always` int(1) NOT NULL DEFAULT '0',
			`description` text NOT NULL COLLATE utf8_general_ci,
			`field_type_id` bigint(20) NOT NULL DEFAULT '0',
			UNIQUE KEY (field_id)
			);";
		/* call dbDelta */
		dbDelta( $sql );

		/* create table conformity roles id with fields id */
		$sql = "CREATE TABLE `" . $wpdb->base_prefix . "prflxtrflds_roles_and_fields` (
			`role_id` bigint(20) NOT NULL DEFAULT '0',
			`field_id` bigint(20) NOT NULL DEFAULT '0',
			`field_order` bigint(20) NOT NULL DEFAULT '0',
			UNIQUE KEY (role_id, field_id)
			);";
		/* call dbDelta */
		dbDelta( $sql );

		/* create table conformity field id with available value */
		$sql = "CREATE TABLE `" . $wpdb->base_prefix . "prflxtrflds_field_values` (
			`value_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`field_id` bigint(20) NOT NULL DEFAULT '0',
			`value_name` text NOT NULL COLLATE utf8_general_ci,
			`order` bigint(20) NOT NULL DEFAULT '0',
			UNIQUE KEY (value_id)
			);";
		/* call dbDelta */
		dbDelta( $sql );

		/* create table conformity field id with available value */
		$sql = "CREATE TABLE " . $wpdb->base_prefix . "prflxtrflds_user_field_data (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			field_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			user_value text NOT NULL COLLATE utf8_general_ci,
			UNIQUE KEY (id)
			);";
		/* call dbDelta */
		dbDelta( $sql );	
	}
}

if ( ! function_exists( 'prflxtrflds_update_table' ) ) {
	function prflxtrflds_update_table() {
		global $wpdb;
		/* v1.0.1 - delete 'prflxtrflds_textfield' */		
		$column_exists = $wpdb->query( "SHOW COLUMNS FROM `" . $wpdb->base_prefix . "prflxtrflds_fields_id` LIKE 'field_type_id'" );
		if ( 0 == $column_exists ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->base_prefix . "prflxtrflds_fields_id` ADD `field_type_id` bigint(20);" );

			$wpdb->query( "
				UPDATE `" . $wpdb->base_prefix . "prflxtrflds_field_types`, `" . $wpdb->base_prefix . "prflxtrflds_fields_id`
				SET    `" . $wpdb->base_prefix . "prflxtrflds_fields_id`.`field_type_id` = `" . $wpdb->base_prefix . "prflxtrflds_field_types`.`field_type_id`
				WHERE  `" . $wpdb->base_prefix . "prflxtrflds_fields_id`.field_id = `" . $wpdb->base_prefix . "prflxtrflds_field_types`.field_id" );

			$wpdb->query( "DROP TABLE " . $wpdb->base_prefix . "prflxtrflds_field_types" );
		}

		$column_exists = $wpdb->query( "SHOW COLUMNS FROM `" . $wpdb->base_prefix . "prflxtrflds_user_field_data` LIKE 'field_id'" );
		if ( 0 == $column_exists ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->base_prefix . "prflxtrflds_user_field_data` ADD `field_id` bigint(20);" );

			$wpdb->query( "
				UPDATE `" . $wpdb->base_prefix . "prflxtrflds_user_field_data`, `" . $wpdb->base_prefix . "prflxtrflds_field_values` 
				SET `" . $wpdb->base_prefix . "prflxtrflds_user_field_data`.`field_id` = `" . $wpdb->base_prefix . "prflxtrflds_field_values`.`field_id`					
				WHERE  `" . $wpdb->base_prefix . "prflxtrflds_user_field_data`.`value_id` = `" . $wpdb->base_prefix . "prflxtrflds_field_values`.`value_id`" );

			$wpdb->delete(
				$wpdb->base_prefix . "prflxtrflds_field_values",
				array(
					'value_name'	=> 'prflxtrflds_textfield'
				)
			);

			$wpdb->query( "
				UPDATE `" . $wpdb->base_prefix . "prflxtrflds_user_field_data`, `" . $wpdb->base_prefix . "prflxtrflds_field_values` 
				SET `" . $wpdb->base_prefix . "prflxtrflds_user_field_data`.`user_value` = `" . $wpdb->base_prefix . "prflxtrflds_user_field_data`.`value_id`				
				WHERE  `" . $wpdb->base_prefix . "prflxtrflds_user_field_data`.`value_id` = `" . $wpdb->base_prefix . "prflxtrflds_field_values`.`value_id`" );			

			$wpdb->query( "ALTER TABLE `" . $wpdb->base_prefix . "prflxtrflds_user_field_data` DROP `value_id`;" );
			
		} 

		$column_exists = $wpdb->query( "SHOW COLUMNS FROM `" . $wpdb->base_prefix . "prflxtrflds_field_values` LIKE 'order'" );
		if ( 0 == $column_exists ) {
			$wpdb->query( "ALTER TABLE `" . $wpdb->base_prefix . "prflxtrflds_field_values` ADD `order` bigint(20) DEFAULT '0';" );
		}
		/* v1.0.1 - end */	
	}
}

/* Create conformity between roles and role_id */
if ( ! function_exists( 'prflxtrflds_update_roles_id' ) ) {
	function prflxtrflds_update_roles_id() {
		global $wpdb, $wp_roles;
		/* Get all available role */
		$all_roles = $wp_roles->roles;
		if ( ! empty( $all_roles ) ) {			
			/* Get role name from array */
			foreach ( $all_roles as $role ) {
				/* Check role for existing in plugin table */
				if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT `role_id` FROM `" . $wpdb->base_prefix . "prflxtrflds_roles_id` WHERE `role` = %s LIMIT 1", $role['name'] ) ) ) {
					/* Create field if not exist */
					$wpdb->insert(
						$wpdb->base_prefix . "prflxtrflds_roles_id",
						array(
							'role' => $role['name']
						)
					);
				}
			}
		}
	}
}

/* Create conformity between user_id and role_id */
if ( ! function_exists( 'prflxtrflds_update_user_roles' ) ) {
	function prflxtrflds_update_user_roles( $user_id = NULL, $role = NULL ) {
		global $wpdb;
		/* First, update roles id */
		if ( function_exists( 'prflxtrflds_update_roles_id' ) ) {
			prflxtrflds_update_roles_id();
		}
		$table_user_roles 	= $wpdb->base_prefix . "prflxtrflds_user_roles";
		$table_role_id 		= $wpdb->base_prefix . "prflxtrflds_roles_id";
		/* Get role id by name */
		$get_role_id_sql = "SELECT `role_id` FROM " . $table_role_id . " WHERE `role`=%s LIMIT 1";
		if ( NULL != $user_id && ( NULL == $role || ! is_string( $role ) ) ) {
			/* Get role if not allowed */
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
			$user_data = get_userdata( $user_id );
			/* Get user role by id */
			if ( isset( $user_data ) ) {
				$role = implode( ', ', $user_data->roles ) ;
			}
		}
		if ( ! isset( $role ) ) {
			if ( $users = get_users() ) {
				/* If no selected roles, update roles for all users */
				foreach ( $users as $user ) {
					/* Role stored in array, get */
					$role = implode( ',', $user->roles );
					$role_id = $wpdb->get_var( $wpdb->prepare( $get_role_id_sql, $role ) );
					/* insert or update value  */
					$wpdb->replace(
						$table_user_roles,
						array(
							'user_id' => $user->ID,
							'role_id' => $role_id,
						)
					);
				}
			}
		} else {
			/* If role select, update role only for this user */
			$role_id = $wpdb->get_var( $wpdb->prepare( $get_role_id_sql, $role ) );
			$wpdb->replace(
				$table_user_roles,
				array(
					'user_id' => $user_id,
					'role_id' => $role_id
				)
			);
		}
	}
}

/* Edit or create new field */
if ( ! function_exists( 'prflxtrflds_edit_field' ) ) {
	function prflxtrflds_edit_field() {
		global $wpdb, $prflxtrflds_field_type_id;
		$error = '';

		$field_name = $description = '';
		$field_order = $field_required = $field_show_default = $field_show_always = 0;
		$field_pattern = '***-**-**';
		$available_values = array();
		$field_type_id = '1';
		$field_date_format = get_option( 'date_format' );
		$field_time_format = get_option( 'time_format' );

		/* Get field id with post or get */
		$field_id = isset( $_REQUEST['prflxtrflds_field_id'] ) ? intval( $_REQUEST['prflxtrflds_field_id'] ) : NULL;

		if ( ! empty( $_POST ) ) {
			$field_name 	= isset( $_POST['prflxtrflds_field_name'] ) ? stripslashes( esc_html( sanitize_text_field( $_POST['prflxtrflds_field_name'] ) ) ) : '';
			$field_type_id  = isset( $_POST['prflxtrflds_type'] ) ? $_POST['prflxtrflds_type'] : 1;
			$field_pattern 	= isset( $_POST['prflxtrflds_pattern'] ) ? preg_replace( '/[^\*\-\(\)\+]/', '', esc_html( $_POST['prflxtrflds_pattern'] ) ) : '***-**-**';
			$description 	= isset( $_POST['prflxtrflds_description'] ) ? stripslashes( esc_html( sanitize_text_field( $_POST['prflxtrflds_description'] ) ) ) : '';
			$checked_roles  = isset( $_POST['prflxtrflds_roles'] ) ? $_POST['prflxtrflds_roles'] : array(); /* is array */

			if ( isset( $_POST['prflxtrflds_time_format'] ) )
				$field_time_format = ( 'custom' == $_POST['prflxtrflds_time_format'] ) ? esc_html( $_POST['prflxtrflds_time_format_custom'] ) : $_POST['prflxtrflds_time_format'];
			if ( isset( $_POST['prflxtrflds_date_format'] ) )
				$field_date_format = ( 'custom' == $_POST['prflxtrflds_date_format'] ) ? esc_html( $_POST['prflxtrflds_date_format_custom'] ) : $_POST['prflxtrflds_date_format'];

			$field_order 	= isset( $_POST['prflxtrflds_order'] ) ? intval( $_POST['prflxtrflds_order'] ) : 0;
			if ( ! is_numeric( $field_order ) )
				$field_order = 0;
			
			$field_required 	= isset( $_POST['prflxtrflds_required'] ) ? 1 : 0;
			$field_show_default = isset( $_POST['prflxtrflds_show_default'] ) ? 1 : 0;
			$field_show_always	= isset( $_POST['prflxtrflds_show_always'] ) ? 1 : 0;

			if ( isset( $_POST['prflxtrflds-value-delete'] ) )
				$field_value_to_delete = $_POST['prflxtrflds-value-delete'];

			$i = 1;
			if ( isset( $_POST['prflxtrflds_available_values'] ) && is_array( $_POST['prflxtrflds_available_values'] ) ) {
				$nonsort_available_values 	= array_map( 'stripslashes_deep', $_POST['prflxtrflds_available_values'] );
				$value_ids					= isset( $_POST['prflxtrflds_value_id'] ) ? $_POST['prflxtrflds_value_id'] : ''; /* is array */
				foreach ( $nonsort_available_values as $key => $value ) {
					if ( ! empty( $value_ids[ $key ] ) && ! empty( $field_value_to_delete ) && in_array( $value_ids[ $key ], $field_value_to_delete ) ) {
						/* will be deleted */
					} else {
						if ( '' != $value ) {
							$available_values[] = array(
								'value_name' 	=> esc_html( $value ),
								'value_id' 		=> ( isset( $value_ids[ $key ] ) ) ? $value_ids[ $key ] : '',
								'value_order'	=> $i
							);
							$i++;
						} elseif ( ! empty( $value_ids[ $key ] ) ) {
							/* If field empty - delete entry */
							$field_value_to_delete[] = $value_ids[ $key ];
						}
					}
				}
			}	

			/* Delete fields if necessary */
			if ( ! empty( $field_value_to_delete ) && is_array( $field_value_to_delete ) ) {
				foreach ( $field_value_to_delete as $deleting_value_id ) {
					if ( '' != $deleting_value_id ) {
						/* remove field */
						$wpdb->delete(
							$wpdb->base_prefix . "prflxtrflds_field_values",
							array(
								'value_id' => $deleting_value_id,
							)
						);
						/* remove user data */
						$wpdb->delete(
							$wpdb->base_prefix . "prflxtrflds_user_field_data",
							array(
								'field_id' 		=> $field_id,
								'user_value' 	=> $deleting_value_id
							)
						);
					}
				}
			}
			/* Name of page if error */
			$name_of_page = __( 'Edit field', 'profile-extra-fields' );
		} elseif ( ! is_null( $field_id ) ) {
			/* Name of page if field exist */
			$name_of_page = __( 'Edit field', 'profile-extra-fields' );
			/* If get $field_id - edit field */
			$field_options = $wpdb->get_row( $wpdb->prepare( "SELECT `field_name`, `required`, `description`, `show_default`, `show_always`, `field_type_id` FROM `" . $wpdb->base_prefix . "prflxtrflds_fields_id` WHERE `field_id`=%d", $field_id ), ARRAY_A );
			if ( ! $field_options ) {
				/* If entry not exist - create new entry */
				$field_id 		    = NULL;
			} else {
				$field_name 	    = $field_options['field_name'];
				$field_required     = $field_options['required'];
				$description 	    = $field_options['description'];
				$field_show_default = $field_options['show_default'];
				$field_show_always  = $field_options['show_always'];
				$field_type_id 		= $field_options['field_type_id'];
				/* Get avaliable roles */
				$checked_roles  = $wpdb->get_col( $wpdb->prepare( "SELECT `role_id` FROM `" . $wpdb->base_prefix . "prflxtrflds_roles_and_fields` WHERE `field_id`=%d", $field_id ) );
				$field_order 	= $wpdb->get_var( $wpdb->prepare( "SELECT `field_order` FROM `" . $wpdb->base_prefix . "prflxtrflds_roles_and_fields` WHERE `field_id`=%d LIMIT 1", $field_id ) );
				/* Get available values to checkbox, radiobutton, select, etc */
				if ( '9' == $field_type_id )
					$field_pattern = $wpdb->get_var( $wpdb->prepare( "SELECT `value_name` FROM `" . $wpdb->base_prefix . "prflxtrflds_field_values` WHERE `field_id`=%d", $field_id ) );
				elseif ( '5' == $field_type_id )
					$field_date_format = $wpdb->get_var( $wpdb->prepare( "SELECT `value_name` FROM `" . $wpdb->base_prefix . "prflxtrflds_field_values` WHERE `field_id`=%d", $field_id ) );
				elseif ( '6' == $field_type_id )
					$field_time_format = $wpdb->get_var( $wpdb->prepare( "SELECT `value_name` FROM `" . $wpdb->base_prefix . "prflxtrflds_field_values` WHERE `field_id`=%d", $field_id ) );
				elseif ( '7' == $field_type_id ) {
					$date_and_time = unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT `value_name` FROM `" . $wpdb->base_prefix . "prflxtrflds_field_values` WHERE `field_id`=%d", $field_id ) ) );
					if ( isset( $date_and_time['date'] ) )
						$field_date_format = $date_and_time['date'];
					if ( isset( $date_and_time['time'] ) )
						$field_time_format = $date_and_time['time'];
				} else
					$available_values = $wpdb->get_results( $wpdb->prepare( "SELECT `value_id`, `value_name`, `order` FROM `" . $wpdb->base_prefix . "prflxtrflds_field_values` WHERE `field_id`=%d ORDER BY `order`", $field_id ), ARRAY_A );
			}
		} else {
			$name_of_page = __( 'Add new field', 'profile-extra-fields' );						
		}

		/* If field id is NULL - create new entry */
		if ( is_null( $field_id ) ) {
			if ( ! $field_id = $wpdb->get_var( "SELECT MAX(`field_id`) FROM `" . $wpdb->base_prefix . "prflxtrflds_fields_id`" ) ) {
				/* If table is empty */
				$field_id = 1;
			} else {
				/* Generate new id */
				$field_id++;
			}
		}

		/* if is save settings page, call save field function */
		if ( isset( $_POST['prflxtrflds_save_field'] ) && check_admin_referer( 'prflxtrflds_nonce_name' ) ) {

			if ( empty( $_POST['prflxtrflds_field_name'] ) )
				$error .= sprintf( '<p><strong>%s</strong></p>', __( 'Field name is empty', 'profile-extra-fields' ) );

			/* If roles not selected */
			if ( empty( $_POST['prflxtrflds_roles'] ) || 1 == sizeof( $_POST['prflxtrflds_roles'] ) )
				$error .= sprintf( '<p><strong>%s</strong></p>', __( 'Select at least one role', 'profile-extra-fields' ) );

			if ( '9' == $field_type_id ) {
				if ( empty( $_POST['prflxtrflds_pattern'] ) )
					$error .= '<p><strong>' . sprintf( __( 'Please specify a mask by which the phone will be validated, where the * is a number. Use these symbols only: %s', 'profile-extra-fields' ), '* - ( ) +' ) . '</strong></p>';

			} elseif ( in_array( $field_type_id, array( '2', '3', '4' ) ) && ! empty( $_POST['prflxtrflds_available_values'] ) ) {
				/* If not choisen values */
				if ( is_array( $_POST['prflxtrflds_available_values'] ) ) {
					$filled = 0;
					foreach ( $_POST['prflxtrflds_available_values'] as $one_value ) {
						if ( ! empty( $one_value ) ) {
							$filled++;
						}
					}
					/* if all values is empty */
					if ( 0 == $filled ) {
						$error .= sprintf( '<p><strong>%s</strong></p>', __( 'Select at least one available value', 'profile-extra-fields' ) );
					} elseif ( 2 > $filled && ( 3 == $field_type_id || 4 == $field_type_id ) ) {
						/* If is radiobutton or select, select more if two available values */
						$error .= sprintf( '<p><strong>%s</strong></p>', __( 'Select at least two available values', 'profile-extra-fields' ) );
					}
				} else {
					$error .= sprintf( '<p><strong>%s</strong></p>', __( 'Select at least one available value', 'profile-extra-fields' ) );
				}
			}
			/* End check error */
			if ( empty( $error ) ) {				

				/* Check for exist field id */
				if ( 1 == $wpdb->query( $wpdb->prepare( "SELECT `field_id` FROM " . $wpdb->base_prefix . "prflxtrflds_fields_id WHERE `field_id`=%d", $field_id ) ) )
					$message = __( 'Field updated', 'profile-extra-fields' );
				else
					$message = __( 'Field created', 'profile-extra-fields' );

				/* Update data */
				$wpdb->replace(
					$wpdb->base_prefix . "prflxtrflds_fields_id",
					array(
						'field_id' 	 	 => $field_id,
						'field_name' 	 => $field_name,
						'required'	 	 => $field_required,
						'description'	 => $description,
						'show_default'   => $field_show_default,
						'show_always'    => $field_show_always,
						'field_type_id'  => $field_type_id
					)
				);

				/* prflxtrflds_roles_and_fields update */
				/* Get all available roles id */
				$all_roles_in_db = $wpdb->get_col( "SELECT `role_id` FROM `" . $wpdb->base_prefix . "prflxtrflds_roles_and_fields` WHERE `field_id`='" . $field_id . "'" );
				/* Delete role if need */
				if ( ! empty( $checked_roles ) ) {
					$roles_to_delete = array_diff( $all_roles_in_db, $checked_roles );
				} else {
					/* If no checked roles, delete ell roles in db */
					$roles_to_delete = $all_roles_in_db;
				}
				if ( ! empty( $roles_to_delete ) ) {
					foreach ( $roles_to_delete as $role_id ) {
						/* Delete unchecked role */
						$wpdb->delete(
							$wpdb->base_prefix . "prflxtrflds_roles_and_fields",
							array(
								'field_id'  => $field_id,
								'role_id'	=> $role_id,
							)
						);
					}
				}
				/* update data */
				if ( ! empty( $checked_roles ) ) {
					/* If field order change, apply it for all roles */
					$default_order = $wpdb->get_var( "SELECT `field_order` FROM `" . $wpdb->base_prefix . "prflxtrflds_roles_and_fields` WHERE `field_id`=" . $field_id . " AND `role_id`=0" );
					if ( $field_order != $default_order ) {
						foreach ( $checked_roles as $role_id ) {
							$wpdb->replace(
								$wpdb->base_prefix . "prflxtrflds_roles_and_fields",
								array(
									'field_id' 		=> $field_id,
									'role_id' 		=> $role_id,
									'field_order' 	=> $field_order,
								)
							);
						}
					} else {
						/* If field order not change, not apply it. Hold old data */
						foreach ( $checked_roles as $role_id ) {
							$old_order = $wpdb->get_var( $wpdb->prepare( "SELECT `field_order` FROM `" . $wpdb->base_prefix . "prflxtrflds_roles_and_fields` WHERE `field_id`=" . $field_id . " AND `role_id`=%s", $role_id ) );
							/* If old order not exists, set default order */
							if ( ( ! isset( $old_order ) ) && ( isset( $field_order ) ) ) {
								/* For new roles */
								$old_order = $field_order;
							} elseif( ( ! isset( $old_order ) ) && ( ! isset( $field_order ) ) ) {
								/* Default order if not exist */
								$old_order = 0;
							}
							$wpdb->replace(
								$wpdb->base_prefix . "prflxtrflds_roles_and_fields",
								array(
									'field_id' 		=> $field_id,
									'role_id' 		=> $role_id,
									'field_order' 	=> $old_order,
								)
							);
						}
					}
				} else {
					/* If no available roles, create entry with role_id = 0 */
					$wpdb->replace(
						$wpdb->base_prefix . "prflxtrflds_roles_and_fields",
						array(
							'field_id' 		=> $field_id,
							'role_id' 		=> 0,
							'field_order'	=> $field_order,
						)
					);
				}

				/* prflxtrflds_field_values update */
				if ( '9' == $field_type_id || '5' == $field_type_id || '6' == $field_type_id || '7' == $field_type_id ) {
					if ( '9' == $field_type_id )
						$value_name = $field_pattern;
					elseif ( '5' == $field_type_id )
						$value_name = $field_date_format;
					elseif ( '6' == $field_type_id )
						$value_name = $field_time_format;
					elseif ( '7' == $field_type_id )
						$value_name = serialize( array( 'date' => $field_date_format, 'time' => $field_time_format ) );
					/* If entry with current id not exist, create new entry */
					if ( $wpdb->get_var( "SELECT `field_id` FROM `" . $wpdb->base_prefix . "prflxtrflds_field_values` WHERE `field_id`=" . $field_id ) ) {
						$wpdb->update(
							$wpdb->base_prefix . "prflxtrflds_field_values",
							array( 'value_name' => $value_name ),
							array( 'field_id' => $field_id )
						);
					} else {
						$wpdb->insert( 
							$wpdb->base_prefix . "prflxtrflds_field_values",
							array(
								'value_name'  => $value_name,
								'field_id'	  => $field_id,
							)
						);
					}					
				} elseif ( ! empty( $available_values ) ) {
					foreach ( $available_values as $i => $value ) {
						/* If entry with current id exists, update it */
						if ( ! empty( $value['value_id'] ) ) {
							/* Update entry if not empty field (rename entry) */
							$wpdb->update(
								$wpdb->base_prefix . "prflxtrflds_field_values",
								array(
									'value_name' 	=> $value['value_name'],
									'order'			=> $value['value_order']
								),
								array( 'value_id' => $value['value_id'] )
							);
						} else {
							/* If entry with current id not exist, create new entry */
							$result_id = $wpdb->insert(
								$wpdb->base_prefix . "prflxtrflds_field_values",
								array(
									'value_name'  	=> $value['value_name'],
									'field_id'	  	=> $field_id,
									'order'			=> $value['value_order']
								)
							);
							$available_values[ $i ]['value_id'] = $result_id;
						}
					}
				} 

				if ( 1 == $field_type_id || 8 == $field_type_id ) {
					/* If field type is changed from other type to textfield/number */						
					$wpdb->delete(
						$wpdb->base_prefix . "prflxtrflds_field_values",
						array(
							'field_id'	=> $field_id,
						)
					);
					$available_values = array();
				}				
			}
		}

		/* Update roles id */
		prflxtrflds_update_roles_id();
		/* Get all avaliable roles */
		$all_roles = $wpdb->get_results( "SELECT `role_id`, `role` FROM `" . $wpdb->base_prefix . "prflxtrflds_roles_id`" ); ?>
		<h2><?php echo $name_of_page; ?></h2>
		<?php if ( ! empty( $error ) ) { ?>
			<div class="error below-h2"><?php echo $error; ?></div>
		<?php } elseif ( ! empty( $message ) ) { ?>
			<div class="updated fade below-h2"><p><?php echo $message; ?></p></div>
		<?php }
		bws_show_settings_notice(); ?>
		<form class="bws_form" method="post" action="<?php get_admin_url(); ?>?page=profile-extra-fields.php&amp;edit=1">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php _e( 'Name', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="text" name="prflxtrflds_field_name" value="<?php echo $field_name; ?>" />
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Type', 'profile-extra-fields' ); ?></th>
						<td>
							<select id="prflxtrflds-select-type" name="prflxtrflds_type">
								<?php foreach ( $prflxtrflds_field_type_id as $id => $field_name ) { /* Create select with field types */ ?>
									<option value="<?php echo $id; ?>"<?php selected( $field_type_id, $id ); ?>><?php echo $field_name; ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr class="prflxtrflds-date-format">
						<th scope="row"><?php _e( 'Date Format', 'profile-extra-fields' ) ?></th>
						<td>
							<fieldset><legend class="screen-reader-text"><span><?php _e( 'Date Format', 'profile-extra-fields' ) ?></span></legend>
							<?php $date_formats = array_unique( apply_filters( 'date_formats', array( 'F j, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y' ) ) );
							$custom = true;
							foreach ( $date_formats as $format ) {
								echo "\t<label title='" . esc_attr( $format ) . "'><input type='radio' name='prflxtrflds_date_format' value='" . esc_attr( $format ) . "'";
								if ( $field_date_format == $format ) {
									echo " checked='checked'";
									$custom = false;
								}
								echo ' /> ' . date_i18n( $format ) . "</label><br />\n";
							}
							echo '	<label><input type="radio" name="prflxtrflds_date_format" id="prflxtrflds_date_format_custom_radio" value="custom"';
							checked( $custom );
							echo '/> ' . __( 'Custom:', 'profile-extra-fields' ) . '<span class="screen-reader-text"> ' . __( 'enter a custom date format in the following field', 'profile-extra-fields' ) . "</span></label>\n";
							echo '<label for="prflxtrflds_date_format_custom" class="screen-reader-text">' . __( 'Custom date format:', 'profile-extra-fields' ) . '</label><input type="text" name="prflxtrflds_date_format_custom" id="prflxtrflds_date_format_custom" value="' . esc_attr( $field_date_format ) . '" class="small-text" /> 
							<span class="screen-reader-text">' . __( 'example:', 'profile-extra-fields' ) . ' </span><span class="example"> ' . date_i18n( $field_date_format ) . "</span> <span class='spinner'></span>\n"; ?>
							<p><a target="_blank" href="https://codex.wordpress.org/Formatting_Date_and_Time"><?php _e( 'Documentation on date and time formatting', 'profile-extra-fields' ); ?></a>.</p>
							</fieldset>
						</td>
					</tr>
					<tr class="prflxtrflds-time-format">
						<th scope="row"><?php _e( 'Time Format', 'profile-extra-fields' ); ?></th>
						<td>
							<fieldset><legend class="screen-reader-text"><span><?php _e( 'Time Format', 'profile-extra-fields' ); ?></span></legend>
							<?php $time_formats = array_unique( apply_filters( 'time_formats', array( 'g:i a', 'g:i A', 'H:i' ) ) );
							$custom = true;
							foreach ( $time_formats as $format ) {
								echo "\t<label title='" . esc_attr( $format ) . "'><input type='radio' name='prflxtrflds_time_format' value='" . esc_attr( $format ) . "'";
								if ( $field_time_format == $format ) {
									echo " checked='checked'";
									$custom = false;
								}
								echo ' /> ' . date_i18n( $format ) . "</label><br />\n";
							}
							echo '	<label><input type="radio" name="prflxtrflds_time_format" id="prflxtrflds_time_format_custom_radio" value="custom"';
							checked( $custom );
							echo '/> ' . __( 'Custom:', 'profile-extra-fields' ) . '<span class="screen-reader-text"> ' . __( 'enter a custom time format in the following field', 'profile-extra-fields' ) . "</span></label>\n";
							echo '<label for="prflxtrflds_time_format_custom" class="screen-reader-text">' . __( 'Custom time format:', 'profile-extra-fields' ) . '</label><input type="text" name="prflxtrflds_time_format_custom" id="prflxtrflds_time_format_custom" value="' . esc_attr( $field_time_format ) . '" class="small-text" /> <span class="screen-reader-text">' . __( 'example:', 'profile-extra-fields' ) . ' </span><span class="example"> ' . date_i18n( $field_time_format ) . "</span> <span class='spinner'></span>\n"; ?>
							<p><a target="_blank" href="https://codex.wordpress.org/Formatting_Date_and_Time"><?php _e( 'Documentation on date and time formatting', 'profile-extra-fields' ); ?></a>.</p>
							</fieldset>
						</td>
					</tr>
					<tr class="prflxtrflds-pattern">
						<th><?php _e( 'Pattern', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="text" name="prflxtrflds_pattern" value="<?php echo $field_pattern; ?>" />
							<div class="bws_info"><?php printf( __( 'Please specify a mask by which the phone will be validated, where the * is a number. Use these symbols only: %s', 'profile-extra-fields' ), '* - ( ) +' ); ?></div>
						</td>
					</tr>					
					<tr class="prflxtrflds-fields-container">
						<th><?php _e( 'Available values', 'profile-extra-fields' ); ?></th>
						<td>
							<div class="bws_info hide-if-js">
								<div class="prflxtrflds-value-name">
									<?php _e( 'Name of value', 'profile-extra-fields' ); ?>
								</div>
								<div class="prflxtrflds-delete">
									<?php _e( 'Delete', 'profile-extra-fields' ); ?>
								</div>
							</div><!--.prflxtrflds-values-info-->
							<div class="prflxtrflds-drag-values-container">
								<?php for ( $i = 0; $i < sizeof( $available_values ); $i++ ) { ?>
									<div class="prflxtrflds-drag-values">
										<input type="hidden" name="prflxtrflds_value_id[]" value="<?php if ( ! empty( $available_values[ $i ]['value_id'] ) ) echo $available_values[ $i ]['value_id']; ?>" />
										<img class="prflxtrflds-drag-field hide-if-no-js prflxtrflds-hide-if-is-mobile" title="" src="<?php echo plugins_url( 'images/dragging-arrow.png', __FILE__ ); ?>" alt="drag-arrow" />
										<input placeholder="<?php _e( 'Name of value', 'profile-extra-fields' ); ?>" class="prflxtrflds-add-options-input" type="text" name="prflxtrflds_available_values[]" value="<?php echo $available_values[ $i ]['value_name']; ?>" />
										<span class="prflxtrflds-value-delete"><input type="checkbox" name="prflxtrflds-value-delete[]" value="<?php if ( ! empty( $available_values[ $i ]['value_id'] ) ) echo $available_values[ $i ]['value_id']; ?>" /><label></label></span>
									</div><!--.prflxtrflds-drag-values-->
								<?php } ?>
								<div class="prflxtrflds-drag-values <?php if ( ! empty( $available_values ) ) echo 'hide-if-js'; ?>">
									<input type="hidden" name="prflxtrflds_value_id[]" value="" />
									<img class="prflxtrflds-drag-field hide-if-no-js prflxtrflds-hide-if-is-mobile" title="" src="<?php echo plugins_url( 'images/dragging-arrow.png', __FILE__ ); ?>" alt="drag-arrow" />
									<input placeholder="<?php _e( 'Name of value', 'profile-extra-fields' ); ?>" class="prflxtrflds-add-options-input" type="text" name="prflxtrflds_available_values[]" value="" />
									<span class="prflxtrflds-value-delete"><input type="checkbox" name="prflxtrflds-value-delete[]" value="" /><label></label></span>
								</div><!--.prflxtrflds-drag-values-->
							</div><!--.prflxtrflds-drag-values-container-->
							<div class="prflxtrflds-add-button-container">
								<input type="button" class="button-small button prflxtrflds-small-button hide-if-no-js" id="prflxtrflds-add-field" name="prflxtrflds-add-field" value="<?php _e( 'Add', 'profile-extra-fields' ); ?>" />
								<p class="hide-if-js"><?php _e( 'To add more values, click save button', 'profile-extra-fields' ); ?></p>
							</div>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Description', 'profile-extra-fields' ); ?></th>
						<td>
							<textarea class="prflxtrflds-description" name="prflxtrflds_description"><?php echo esc_textarea( $description ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Available for roles', 'profile-extra-fields' ); ?></th>
						<td><fieldset>
							<div id="prflxtrflds-select-roles">
								<?php if ( $all_roles ){ ?>
									<div id="prflxtrflds-div-select-all">
										<input type="checkbox" name="prflxtrflds-select-all" id="prflxtrflds-select-all" />
										<label for="prflxtrflds-select-all"><b><?php _e( 'Select all', 'profile-extra-fields' ); ?></b></label>
										<br />
									</div><!--#prflxtrflds-div-select-all-->
									<input type="hidden" name="prflxtrflds_roles[]" id="0" value="0" />
									<?php foreach ( $all_roles as $role ) { ?>
										<input type="checkbox" name="prflxtrflds_roles[]" id="<?php echo $role->role_id; ?>" value="<?php echo $role->role_id; ?>"<?php if ( isset( $checked_roles ) ) checked( in_array( $role->role_id, $checked_roles ), true ); ?> />
										<label for="<?php echo $role->role_id; ?>"><?php echo translate_user_role( $role->role ); ?></label>
										<br />
									<?php }
								} ?>
							</div><!--#prflxtrflds-select-roles-->
						</fieldset></td>
					</tr>
					<tr>
						<th><?php _e( 'Required', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="checkbox" id="prflxtrflds-required" name="prflxtrflds_required" value="1" <?php if ( isset( $field_required ) ) { checked( $field_required, "1" ); } ?> />
							<label for="prflxtrflds-required"><?php _e( 'Mark this field as required', 'profile-extra-fields' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Show by default in User data', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="checkbox" id="prflxtrflds-show-default" name="prflxtrflds_show_default" value="1" <?php if ( isset( $field_show_default ) ) { checked( $field_show_default, "1" ); } ?> />
							<label for="prflxtrflds-show-default"><?php _e( 'Show this field by default in User data. You can change it using screen options', 'profile-extra-fields' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Show always in User data', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="checkbox" id="prflxtrflds-show-always" name="prflxtrflds_show_always" value="1" <?php if ( isset( $field_show_always ) ) { checked( $field_show_always, "1" ); } ?> />
							<label for="prflxtrflds-show-always"><?php _e( 'Show this field in User data on any display. You can change it using screen options', 'profile-extra-fields' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Field order', 'profile-extra-fields' ); ?></th>
						<td>
							<input type="number" min="0" max="999" name="prflxtrflds_order" value="<?php echo ( isset( $field_order ) ) ? $field_order : '0'; ?>" />
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="hidden" name="prflxtrflds_save_field" value="true" />
				<input type="hidden" name="prflxtrflds_field_id" value="<?php echo $field_id; ?>" />
				<input  id="bws-submit-button" type="submit" class="button-primary" name="prflxtrflds_save_settings" value="<?php _e( 'Save settings', 'profile-extra-fields' ); ?>" />
				<?php wp_nonce_field( 'prflxtrflds_nonce_name' ); ?>
			</p>
		</form>
	<?php }
}

/* Screen option. Settings for display where items per page show in wp list table */
if ( ! function_exists( 'prflxtrflds_screen_options' ) ) {
	function prflxtrflds_screen_options() {
		$screen = get_current_screen();
		$args = array(
			'id' 			=> 'prflxtrflds',
			'section' 		=> '201146449'
		);
		bws_help_tab( $screen, $args );

		$option = 'per_page';
		$args = array(
			'label'   => __( 'Fields per page', 'profile-extra-fields' ),
			'default' => 20,
			'option'  => 'fields_per_page',
		);
		add_screen_option( $option, $args );

		if ( isset( $_GET['tab-action'] ) && 'userdata' == $_GET['tab-action'] ) {
			global $prflxtrflds_userdatalist_table;
			if ( ! isset( $prflxtrflds_userdatalist_table ) )
				$prflxtrflds_userdatalist_table = new Srrlxtrflds_Userdata_List;
		} elseif ( isset( $_GET['tab-action'] ) && 'shortcode' == $_GET['tab-action']) {
			global $prflxtrflds_shortcodelist_table;
			if ( ! isset( $prflxtrflds_shortcodelist_table ) )
				$prflxtrflds_shortcodelist_table = new Srrlxtrflds_Shortcode_List();
		} else {
			global $prflxtrflds_fields_list_table;
			if ( ! isset( $prflxtrflds_fields_list_table ) )
				$prflxtrflds_fields_list_table  = new Srrlxtrflds_Fields_List;
		}
	}
}

if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
	/* Create new class to displaying fields */
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	if ( ! class_exists( 'Srrlxtrflds_Fields_List' ) ) {
		class Srrlxtrflds_Fields_List extends WP_List_Table {

			function get_columns() {
				$columns = array(
					'cb'			=> '<input type="checkbox" />',
					'field_name'	=> __( 'Name', 'profile-extra-fields' ),
					'description'	=> __( 'Description', 'profile-extra-fields' ),
					'field_type'	=> __( 'Type', 'profile-extra-fields' ),
					'required'		=> __( 'Required', 'profile-extra-fields' ),
					'show_default'	=> __( 'Show default', 'profile-extra-fields' ),
					'show_always'	=> __( 'Show always', 'profile-extra-fields' ),
					'roles'			=> __( 'Roles', 'profile-extra-fields' ),
					'field_order'	=> __( 'Field order', 'profile-extra-fields' ),
				);
				return $columns;
			}

			function get_sortable_columns() {
				/* seting sortable collumns */
				$sortable_columns = array(
					'field_name'	=> array( 'field_name', true ),
					'field_order'	=> array( 'field_order', true ),
					'field_type'	=> array( 'field_type_id', true ),
					'required'		=> array( 'required', true ),
				);
				return $sortable_columns;
			}

			function get_bulk_actions() {
				/* adding bulk action */
				$actions = array(
					'delete_fields'	=> __( 'Delete Permanently', 'profile-extra-fields' ),
				);
				return $actions;
			}

			/* Override this function to delete nonce from options */
			function display_tablenav( $which ) { ?>
				<div class="tablenav <?php echo esc_attr( $which ); ?>">
					<div class="alignleft actions bulkactions">
						<?php $this->bulk_actions( $which ); ?>
					</div>
					<?php $this->extra_tablenav( $which );
					$this->pagination( $which ); ?>
					<br class="clear" />
				</div>
			<?php }

			/* Bulk actions handler */
			function process_bulk_action() {
				/* Get action */
				$action = $this->current_action();
				/* Action = delete fields */
				switch ( $action ) {
					case 'delete_fields':
						/* Security check */
						if ( isset( $_GET['prflxtrflds_field_id'] ) && isset( $_GET['prflxtrflds_nonce_name'] ) && ! empty( $_GET['prflxtrflds_nonce_name'] ) ) {
							$nonce  = filter_input( INPUT_GET, 'prflxtrflds_nonce_name', FILTER_SANITIZE_STRING );
							if ( wp_verify_nonce( $nonce, 'prflxtrflds_nonce_name' ) ) {
								if ( isset( $_GET['prflxtrflds_field_id'] ) ) {
									foreach ( $_GET['prflxtrflds_field_id'] as $id ) {
										/* Delete all checked fields */
										prflxtrflds_remove_field( $id );
									}
								}
							}
						}
						break;
					default:
						/* Do nothing */
						break;
				}
				return ;
			}

			function get_views() {
				/* Show links at the top of table */
				global $wpdb;
				$views = array();
				$current = ( ! empty( $_GET['role_id'] ) ) ? $_GET['role_id'] : 'all';

				/* All link */
				$all_url = htmlspecialchars( add_query_arg( 'role_id', 'all' ) );
				$class = ( 'all' == $current ) ? 'class="current"' : '';
				$views['all'] = "<a href='" . $all_url . "' " . $class . " >" . __( 'All', 'profile-extra-fields' ) . "</a>";

				/* Get actual users data */
				$roles  = $wpdb->get_results( "SELECT * FROM " . $wpdb->base_prefix . "prflxtrflds_roles_id" );
				if ( $roles ) {
					foreach ( $roles as $role ) {
						/* Create link */
						$role_url = htmlspecialchars( add_query_arg( 'role_id', $role->role_id ) );
						if ( $role->role_id == $current ) {
							$class = 'class="current"';
						} else {
							$class = '';
						}
						$views[ $role->role_id ] = "<a href='" . $role_url . "' " . $class . " >" . $role->role . "</a>";
					}
				}
				return $views;
			}

			function extra_tablenav( $which ) {
				if ( "top" == $which ) {
					global $wpdb;
					if ( ! empty( $_GET['prflxtrflds_role_id'] ) ) {
						$current = $_GET['prflxtrflds_role_id'];
					} else {
						$current = 'all';
					}
					/* Get actual users data */
					$roles = $wpdb->get_results( "SELECT * FROM " . $wpdb->base_prefix . "prflxtrflds_roles_id" ); ?>
					<div class="alignleft prflxtrflds-filter actions bulkactions">
						<label for="prflxtrflds-role-id">
							<select name="prflxtrflds_role_id" id="prflxtrflds-role-id">
								<option value="all" <?php selected( $current, "all" ); ?>><?php _e( 'All roles', 'profile-extra-fields' ); ?></option>
								<?php if ( ! empty( $roles ) ) {
									/* Create select with field types */
									foreach ( $roles as $role ) { ?>
										<option value="<?php echo $role->role_id; ?>"<?php selected( $current, $role->role_id ); ?>><?php echo translate_user_role( $role->role ); ?></option>
									<?php }
								} ?>
							</select>
						</label>
						<input type="submit" class="button" name="prflxtrflds_apply_filter" value="<?php _e( 'Filter', 'profile-extra-fields' ); ?>" />
					</div><!--.alignleft prflxtrflds-filter-->
				<?php }
			}

			function column_cb( $item ) {
				/* customize displaying cb collumn */
				return sprintf(
					'<input type="checkbox" name="prflxtrflds_field_id[]" value="%s" />', $item['field_id']
				);
			}

			function column_field_name( $item ) {
				/* adding action to 'name' collumn */
				$actions = array(
					'edit_fields'	=> '<span><a href="' . wp_nonce_url( sprintf( '?page=profile-extra-fields.php&amp;edit=1&amp;prflxtrflds_field_id=%s', $item['field_id'] ), 'prflxtrflds_nonce_name' ) . '">' . __( 'Edit field', 'profile-extra-fields' ) . '</a></span>',
					'delete_fields'	=> '<span class="trash"><a href="' . wp_nonce_url( sprintf( '?page=profile-extra-fields.php&amp;remove=1&amp;prflxtrflds_field_id=%s', $item['field_id'] ), 'prflxtrflds_nonce_name' ) . '">' . __( 'Delete Permanently', 'profile-extra-fields' ) . '</a></span>',
				);
				return sprintf( '%1$s %2$s', $item['field_name'], $this->row_actions( $actions ) );
			}

			function column_field_type( $item ) {
				global $prflxtrflds_field_type_id;
				return sprintf(
					'%s', $prflxtrflds_field_type_id[ $item['field_type_id'] ]
				);
			}

			function column_required( $item ) {
				$is_required = array(
					1 => __( 'Yes', 'profile-extra-fields' ),
					0 => __( 'No', 'profile-extra-fields' ),
				);
				return sprintf(
					'%s', $is_required[ $item['required'] ]
				);
			}
			function column_show_default( $item ) {
				$is_default = array(
					1 => __( 'Yes', 'profile-extra-fields' ),
					0 => __( 'No', 'profile-extra-fields' ),
				);
				return sprintf(
					'%s', $is_default[ $item['show_default'] ]
				);
			}
			function column_show_always( $item ) {
				$is_always = array(
					1 => __( 'Yes', 'profile-extra-fields' ),
					0 => __( 'No', 'profile-extra-fields' ),
				);
				return sprintf(
					'%s', $is_always[ $item['show_always'] ]
				);
			}

			function column_roles( $item ) {
				/* Delete last comma */
				return sprintf( '%s', chop( $item['roles'], ', ' ) );
			}

			function prepare_items() {
				/* Bulk action handler. Before query */
				global $wpdb;
				$this->process_bulk_action();
				$table_fields_id		= $wpdb->base_prefix . 'prflxtrflds_fields_id';
				$table_roles_id 		= $wpdb->base_prefix . 'prflxtrflds_roles_id';
				$table_roles_and_fields = $wpdb->base_prefix . 'prflxtrflds_roles_and_fields';
				/* Order by field id by default. It need for generate fields to display without sorting */
				$rolerequest = "ORDER BY " . $table_fields_id . ".`field_id` ASC";
				/* Query if role selected */
				if ( isset( $_GET['prflxtrflds_role_id'] ) && 'all' != $_GET['prflxtrflds_role_id'] ) {
					$selected_role  = filter_input( INPUT_GET, 'prflxtrflds_role_id', FILTER_SANITIZE_NUMBER_INT );
					$rolerequest 	= "AND " . $table_roles_and_fields . ".`role_id`='" . $selected_role . "' ORDER BY " . $table_roles_and_fields . ".`field_order` ASC";
				}
				/* Default WHERE query */
				$searchrequest = '1=1';
				/* Search handler */
				if ( isset( $_GET['s'] ) && '' != trim( $_GET['s'] ) ) {
					/* Sanitize search query */
					$searchrequest = filter_input( INPUT_GET, 's', FILTER_SANITIZE_ENCODED );
					$searchrequest = $table_fields_id . ".`field_name` LIKE '%" . $searchrequest . "%'";
				}
				/* Default query */
				$query = "SELECT " . $table_roles_and_fields . ".`field_order`, " .
						$table_fields_id . ".`field_id`, " .
						$table_fields_id . ".`field_name`, " .
						$table_fields_id . ".`description`, " .
						$table_fields_id . ".`required`, " .
						$table_fields_id . ".`show_default`, " .
						$table_fields_id . ".`show_always`, " .
						$table_roles_id . ".`role`, " .
						$table_roles_and_fields . ".`role_id`, " .
						$table_fields_id . ".`field_type_id` " .
						" FROM " . $table_fields_id .
						" LEFT JOIN " .  $table_roles_and_fields .
						" ON " . $table_roles_and_fields . ".`field_id`=" . $table_fields_id . ".`field_id`" .
						" LEFT JOIN " . $table_roles_id .
						" ON " . $table_roles_id . ".`role_id`=" . $table_roles_and_fields . ".`role_id`" .
						" WHERE " . $searchrequest . " " .
						$rolerequest;
				/* Get result from database with repeat id with other role */
				$fields_query_result = $wpdb->get_results( $query, ARRAY_A );
				$i 					 = 0;
				$fields_to_display   = array();
				$prev_id 			 = -1;
				foreach ( $fields_query_result as $one_field ) {
					$id = $one_field['field_id'];
					if ( $prev_id != $id ) {
						$i++;
						/* If is new id, copy all fields */
						$fields_to_display[ $i ]['field_id'] 		= $one_field['field_id'];
						$fields_to_display[ $i ]['field_name']		= $one_field['field_name'];
						$fields_to_display[ $i ]['required'] 		= $one_field['required'];
						$fields_to_display[ $i ]['show_default']    = $one_field['show_default'];
						$fields_to_display[ $i ]['show_always']     = $one_field['show_always'];
						$fields_to_display[ $i ]['description'] 	= $one_field['description'];
						$fields_to_display[ $i ]['field_type_id'] 	= $one_field['field_type_id'];
						$fields_to_display[ $i ]['roles'] 			= translate_user_role( $one_field['role'] );
						$fields_to_display[ $i ]['field_order'] 	= $one_field['field_order'];
						$prev_id = $id;
					} else {
						/* If is old id ( new role ), add new role */
						if ( isset( $fields_to_display[ $i ]['roles'] ) ) {
							$fields_to_display[ $i ]['roles'] .= ', ' . translate_user_role( $one_field['role'] );
						} else {
							$fields_to_display[ $i ]['roles'] = translate_user_role( $one_field['role'] );
						}
						$prev_id = $id;
					}
				}
				/* Sort function */
				if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
					/* Check permitted names of field */
					switch ( $_GET['orderby'] ) {
						case 'field_name':
						case 'field_type_id':
						case 'required':
						case 'field_order':
							if (  'desc' == $_GET['order'] ) {
								function prflxtrflds_cmp( $first, $second ) {
									/* Permitted names of sort check in switch */
									return strcmp( $first[ $_GET['orderby'] ], $second[ $_GET['orderby'] ] ) * -1;	/* DESC */
								}
							} else {
								/* Sort result array. This use in usort. ASC by default */
								function prflxtrflds_cmp( $first, $second ) {
									return strcmp( $first[ $_GET['orderby'] ], $second[ $_GET['orderby'] ] );	/* ASC */
								}
							}
							usort( $fields_to_display, "prflxtrflds_cmp" );
							break;
						default:
							break;
					}
				} else {
					/* Default sort by field order */
					function prflxtrflds_cmp( $first, $second ) {
						/* Permitted names of sort check in switch */
						return strcmp( $first['field_order'], $second['field_order'] );	/* ASC */
					}
					usort( $fields_to_display, "prflxtrflds_cmp" );
				}
				/* Pagination settings */
				/* Get the total fields */
				$totalitems = count( $fields_to_display );
				/* Get the value of number of field on one page */
				$perpage = $this->get_items_per_page( 'fields_per_page', 20 );
				/* The total number of pages */
				$totalpages = ceil( $totalitems / $perpage );
				/* Get current page */
				$current_page = $this->get_pagenum();
				/* Set pagination arguments */
				$this->set_pagination_args( array(
					"total_items" 	=> $totalitems,
					"total_pages" 	=> $totalpages,
					"per_page" 		=> $perpage,
				) );
				/* Settings data to output */
				$this->_column_headers = $this->get_column_info();
				/* Slice array */
				$this->items 		   = array_slice( $fields_to_display, ( ( $current_page - 1 ) * $perpage ), $perpage );
			}

			function column_default( $item, $column_name ) {
				/* setting default view for column items */
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
						/* Show array */
						return print_r( $item, true );
				}
			}
		}
	}

	if ( ! class_exists( 'Srrlxtrflds_Userdata_List' ) ) {
		class Srrlxtrflds_Userdata_List extends WP_List_Table {

			public function __construct( $args = array() ) {
				$args = wp_parse_args( $args, array(
					'plural' => '',
					'singular' => '',
					'ajax' => false,
					'screen' => null,
				) );

				$this->screen = convert_to_screen( $args['screen'] );
				/* Change screen id */
				$this->screen->id = $this->screen->id . 'userdata';
				add_filter( "manage_{$this->screen->id}_columns", array( $this, 'get_columns' ), 0 );

				if ( ! $args['plural'] )
					$args['plural'] = $this->screen->base;

				$args['plural'] = sanitize_key( $args['plural'] );
				$args['singular'] = sanitize_key( $args['singular'] );

				$this->_args = $args;
				if ( $args['ajax'] ) {
					add_action( 'admin_footer', array( $this, '_js_vars' ) );
				}
			}

			function get_columns() {
				global $wpdb;
				/* Setup column */
				$columns = array(
					'user_id'     => __( 'User ID', 'profile-extra-fields' ),
					'name'		  => __( 'Username', 'profile-extra-fields' ),
					'role'		  => __( 'User role', 'profile-extra-fields' ),
					'disp_name'   => __( 'Name', 'profile-extra-fields' ),
					'email'       => __( 'Email', 'profile-extra-fields' ),
					'posts'       => __( 'Posts', 'profile-extra-fields' ),
				);

				/* Get all fields from database and set as column */
				$all_fields_array =  $wpdb->get_results( "SELECT `field_id`, `field_name` FROM " . $wpdb->base_prefix . 'prflxtrflds_fields_id', ARRAY_A );
				$db_columns = array();
				foreach ( $all_fields_array as $one_field ) {
					/* Convert to 2D array for merge with $columns */
					$db_columns[ (string) $one_field['field_id'] ] = $one_field['field_name'];
				}
				/* Add columns from database to default columns */
				$columns = $columns + $db_columns;
				/* Get hidden columns from option */
				$hidden_columns = get_user_option( 'manage' . 'bws-plugins_page_profile-extra-fieldsuserdata' . 'columnshidden' );
				if ( isset( $hidden_columns ) && is_array( $hidden_columns ) ) {
					/* If hidden columns exist, user has setting for hidden column */
					$all_columns = get_user_option( 'manage' . 'bws-plugins_page_profile-extra-fieldsuserdata' . 'allcolumns' );
					/* Get all colums ( for last user visit ) */
					if ( isset( $all_columns ) && is_array( $all_columns ) ) {
						/* create list of new columns */
						$new_columns = array_diff( $columns, $all_columns );
						/* Create list for delete columns ( not exist ) */
						$del_columns = array_diff( $all_columns, $columns );
						update_user_option( get_current_user_id() , 'manage' . $this->screen->id . 'allcolumns', $columns, true );
					} else {
						/* Else create all columns for current visit */
						update_user_option( get_current_user_id() , 'manage' . $this->screen->id . 'allcolumns', $columns, true );
					}
					/* Add to hidden columns new columns without show_default option*/
					$show_default = $wpdb->get_col( "SELECT `field_id` FROM " . $wpdb->base_prefix . "prflxtrflds_fields_id WHERE `show_default`='1'" );
					if ( isset( $new_columns ) && is_array( $new_columns ) ) {
						foreach( $new_columns as $key=>$column ) {
							if ( in_array( $key, $show_default ) )
								continue;
							/* Add new fields to hidden, if no set option show_default */
							$hidden_columns[] = $key;
						}
					}
					$show_always = $wpdb->get_col( "SELECT `field_id` FROM " . $wpdb->base_prefix . "prflxtrflds_fields_id WHERE `show_always`='1'" );
					if ( ! isset( $show_always ) )
						/* Create empty array if no array */
						$show_always = array();
					/* If exist delete columns, remove it from $hidden_columns array */
					if ( isset( $del_columns ) && is_array( $del_columns ) )
						$show_always = array_merge( $show_always, array_keys( $del_columns ) );
					if ( isset( $show_always ) && is_array( $show_always ) ) {
						foreach ( $show_always as $col ) {
							/* Get key of array for current value */
							$key = array_search( $col, $hidden_columns );
							/* If key exist, delete from hidden columns */
							if ( false !== $key ) {
								if ( isset( $hidden_columns[ $key ] ) )
									unset( $hidden_columns[ $key ] );
							}
						}
					}
					/* Delete void values */
					$hidden_columns = array_filter( $hidden_columns );
					/* Update hidden columns */
					update_user_option( get_current_user_id() , 'manage' . $this->screen->id . 'columnshidden', $hidden_columns, true );
				} else {
					/* If not exist hidden columns option */
					$hidden_columns = array(
						'role',
						'disp_name',
						'email',
						'posts',
					);
					/* Add to hidden columns not show default columns from database */
					$not_show_default = $wpdb->get_col( "SELECT `field_id` FROM " . $wpdb->base_prefix . "prflxtrflds_fields_id WHERE `show_default`='0'" );
					if ( isset( $not_show_default ) && is_array( $not_show_default ) )
						$hidden_columns = array_merge( $hidden_columns, $not_show_default );
					/* Update hidden columns */
					update_user_option( get_current_user_id() , 'manage' . $this->screen->id . 'columnshidden', $hidden_columns, true );
					/* Add allcolumns option */
					if ( isset( $columns ) )
						update_user_option( get_current_user_id() , 'manage' . $this->screen->id . 'allcolumns', $columns, true );

				}
				return $columns;
			}

			/* Override this function to delete nonce from options */
			function display_tablenav( $which ) { ?>
				<div class="tablenav <?php echo esc_attr( $which ); ?>">
					<?php $this->extra_tablenav( $which );
					$this->pagination( $which ); ?>
					<br class="clear" />
				</div>
			<?php }

			function column_role( $item ) {
				/* Translate user role */
				return sprintf( '%s', translate_user_role( ucfirst( $item['role'] ) ) );
			}

			function column_name( $item ) {
				$actions = array(
					'edit_user'	=> '<span><a href="' . sprintf( 'user-edit.php?user_id=%s&amp;wp_http_referer=%s', $item['user_id'], urlencode( admin_url( 'admin.php?page=profile-extra-fields.php&tab-action=userdata' ) ) ) . '">' . __( 'Edit user', 'profile-extra-fields' ) . '</a></span>',
				);
				return sprintf( '%s %s', $item['name'] , $this->row_actions( $actions ) );
			}

			function get_sortable_columns() {
				/* seting sortable collumns */
				$sortable_columns = array(
					'name'	    => array( 'username', true ),
					'role'	    => array( 'role', true ),
					'user_id'	=> array( 'ID', true ),
					'disp_name'	=> array( 'name', true ),
					'email'	    => array( 'email', true )
				);
				return $sortable_columns;
			}

			function extra_tablenav( $which ) {
				/* Extra tablenav. Create filter. */
				$current_role = ( ! empty( $_GET['prflxtrflds_role'] ) ) ? $_GET['prflxtrflds_role'] : 'all';
				if ( "top" == $which ) {
					$roles = get_editable_roles(); ?>
					<div class="alignleft prflxtrflds-filter actions bulkactions">
						<label for="prflxtrflds-role">
							<select id="prflxtrflds-role" name="prflxtrflds_role">
								<option value="all" <?php selected( $current_role, "all" ); ?>><?php _e( 'All roles', 'profile-extra-fields' ); ?></option>
								<?php if ( isset( $roles ) ) {
									foreach ( $roles as $role ) { /* Create select with field types */ ?>
										<option value="<?php echo $role['name']; ?>"<?php selected( $current_role, $role['name'] ); ?>><?php echo translate_user_role( $role['name'] ); ?></option>
									<?php }
								} ?>
							</select>
						</label>
						<input type="submit" class="button" name="prflxtrflds_apply_filter" value="<?php _e( 'Filter', 'profile-extra-fields' ); ?>" />
					</div><!--.alignleft prflxtrflds-filter-->
				<?php }
			}

			function prepare_items() {
				global $wpdb, $role;
				$userdata = array();
				$i = 0;
				$search = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
				$role = ( isset( $_REQUEST['prflxtrflds_role'] ) && '' != $_GET['prflxtrflds_role'] && 'all' != $_GET['prflxtrflds_role'] ) ? $_GET['prflxtrflds_role'] : '';

				$users_per_page = $this->get_items_per_page( 'fields_per_page', 20 );
				$paged = $this->get_pagenum();

				$args = array(
					'number' => $users_per_page,
					'offset' => ( $paged - 1 ) * $users_per_page,
					'role'   => $role,
					'fields' => 'all_with_meta'
				);

				if ( isset( $_REQUEST['orderby'] ) )
					$args['orderby'] = $_REQUEST['orderby'];

				if ( isset( $_REQUEST['order'] ) )
					$args['order'] = $_REQUEST['order'];

				/* Query the user IDs for this page */
				$wp_user_search = new WP_User_Query( $args );
				$all_users = $wp_user_search->get_results();
				/* Users post by id */
				$post_counts = count_many_users_posts( array_keys( $all_users ) );
				
				$this->set_pagination_args( array(
					'total_items' => $wp_user_search->get_total(),
					'per_page' => $users_per_page,
				) );

				$table_field_values		= $wpdb->base_prefix . 'prflxtrflds_field_values';
				$table_user_field_data  = $wpdb->base_prefix . 'prflxtrflds_user_field_data';
				$table_fields_id 		= $wpdb->base_prefix . 'prflxtrflds_fields_id';

				foreach ( $all_users as $user ) {
					$userdata[ $i ]['name'] = $user->user_nicename;
					$userdata[ $i ]['role'] = implode( ', ', $user->roles );
					$userdata[ $i ]['user_id'] = $user->ID;
					$userdata[ $i ]['disp_name'] = $user->first_name . ' ' . $user->last_name;
					$userdata[ $i ]['email'] = $user->user_email;
					$userdata[ $i ]['posts'] = $post_counts[ $user->ID ];
					

					/* Get fields for current user */
					$filled_fields = $wpdb->get_results(
						"SELECT `" . $table_field_values . "`.`field_id`, `value_name` AS `user_value`
						FROM " . $table_user_field_data . ", " . $table_fields_id . ", `" . $table_field_values . "`
							WHERE `" . $table_user_field_data . "`.`user_value` = `" . $table_field_values . "`.`value_id`
								AND `user_id` = '" . $user->ID . "'
								AND `" . $table_field_values . "`.`field_id`= `" . $table_fields_id . "`.`field_id`
								AND `" . $table_user_field_data . "`.`field_id`= `" . $table_fields_id . "`.`field_id`
								AND `" . $table_fields_id . "`.`field_type_id` IN ( '2', '3', '4' )
						UNION
						SELECT `" . $table_user_field_data . "`.`field_id`, `user_value`
							FROM " . $table_user_field_data . ", " . $table_fields_id .
							" WHERE `user_id` = '" . $user->ID . "' 
								AND `" . $table_user_field_data . "`.`field_id`= `" . $table_fields_id . "`.`field_id`
								AND `" . $table_fields_id . "`.`field_type_id` NOT IN ( '2', '3', '4' )
						", ARRAY_A );

					if ( ! empty( $filled_fields ) ) {
						foreach ( $filled_fields as $field ) {
							if ( isset( $userdata[ $i ][ $field['field_id'] ] ) ) {
								/* Add value name */
								$userdata[ $i ][ $field['field_id'] ] .= ", " .  $field['user_value'];
							} else {
								/* First write value name */
								$userdata[ $i ][ $field['field_id'] ] = $field['user_value'];
							}
						}
					}
					$i++;
				}
				/* Array search. If search by user not work */
				if ( ! empty( $search ) && isset( $userdata ) ) {
					$not_empty_keys = array();
					/* Get all columns */
					$hidden_columns = get_user_option( 'manage' . 'bws-plugins_page_profile-extra-fieldsuserdata' . 'columnshidden' );
					if ( empty( $hidden_columns ) ) {
						$hidden_columns = array();
					}
					foreach ( $userdata as $key => $oneuserdata ) {
						/* Data for one user */
						foreach ( $oneuserdata as $key_col_id=>$one_value ) {
							/* Skip if current column is hidden */
							if ( in_array( $key_col_id, $hidden_columns ) )
								continue;
							/* If value in array, save key */
							if ( false != stristr( $one_value, $search ) ) {
								$not_empty_keys[] = $key;
								break;
							}
						}
					}
					if ( isset( $not_empty_keys ) ) {
						$all_keys = array_keys( $userdata );
						/* Get empty entrys */
						$to_delete = array_diff( $all_keys, $not_empty_keys );
						if ( ! empty( $to_delete ) ) {
							foreach ( $to_delete as $key ) {
								/* Unset empty entrys */
								unset( $userdata[ $key ] );
							}
						}
					}
				}
				/* Order by firstname - lastname */
				if ( isset( $_GET['orderby'] ) && 'name' == $_GET['orderby'] ) {
					if (  'desc' == $_GET['order'] ) {
						function prflxtrflds_cmp( $first, $second ) {
							/* Permitted names of sort check in switch */
							return strcmp( $first['disp_name'], $second['disp_name'] ) * -1;	/* DESC */
						}
					} else {
						/* Sort result array. This use in usort. ASC by default */
						function prflxtrflds_cmp( $first, $second ) {
							return strcmp( $first['disp_name'], $second['disp_name'] );	/* ASC */
						}
					}
					usort( $userdata, "prflxtrflds_cmp" );
				}

				/* Get info from screen options */
				$this->_column_headers = $this->get_column_info();
				$this->items = $userdata;
			}

			function column_default( $item, $column_name ) {
				/* setting default view for column items */
				switch ( $column_name ) {
					case 'name':
					case 'role':
					case 'user_id':
					case 'disp_name':
					case 'email':
					case 'posts':
						return $item[ $column_name ];
					default:
						/* Show array */
						/*return print_r( $item, true ); For debug */
						if ( isset( $item[ $column_name ] ) ) {
							return $item[ $column_name ];
						} else {
							/* Default message */
							return '';
						}

				}
			}
		}
	}

	if ( ! class_exists( 'Srrlxtrflds_Shortcode_List' ) ) {
		class Srrlxtrflds_Shortcode_List extends WP_List_Table {

			public function __construct( $args = array() ) {
				$args = wp_parse_args( $args, array(
					'plural' => '',
					'singular' => '',
					'ajax' => false,
					'screen' => null,
				) );

				$this->screen = convert_to_screen( $args['screen'] );
				/* Change screen id */
				$this->screen->id = $this->screen->id . 'shortcode';
				add_filter( "manage_{$this->screen->id}_columns", array( $this, 'get_columns' ), 0 );

				if ( ! $args['plural'] )
					$args['plural'] = $this->screen->base;

				$args['plural'] = sanitize_key( $args['plural'] );
				$args['singular'] = sanitize_key( $args['singular'] );

				$this->_args = $args;
				if ( $args['ajax'] ) {
					add_action( 'admin_footer', array( $this, '_js_vars' ) );
				}
			}

			function get_columns() {
				/* Setup column */
				$columns = array(
					'field_name'		=> __( 'Field name', 'profile-extra-fields' ),
					'description'		=> __( 'Description', 'profile-extra-fields' ),
					'show'				=> __( 'Show this field', 'profile-extra-fields' ),
					'selected'			=> __( 'Show only if next value is selected', 'profile-extra-fields' )
				);
				return $columns;
			}

			function column_show( $item ) {
				global $prflxtrflds_options;
				
				if ( is_array( $prflxtrflds_options['available_fields'] ) ) {
					$prflxtrflds_checked = checked( in_array( $item['field_id'], $prflxtrflds_options['available_fields'] ), 1, false );
				} else {
					$prflxtrflds_checked = '';
				}
				return sprintf( '<input type="checkbox" class="prflxtrflds-available-fields" name="prflxtrflds_options_available_fields[]" value="%d" %s />', $item['field_id'], $prflxtrflds_checked );
			}

			function column_selected( $item ) {
				global $prflxtrflds_options;
				/* If field have more 1 values, print select */
				if ( ! empty( $item['available_values'] ) ) {
					$prflxtrflds_option_list = '';
					foreach ( $item['available_values'] as $value ) {
						if ( is_array( $prflxtrflds_options['available_values'] ) ) {
							$value_selected = selected( in_array( $value['value_id'], $prflxtrflds_options['available_values'] ), 1, false );
						} else {
							$value_selected = '';
						}
						$prflxtrflds_option_list .= "<option value='" . $value['value_id'] . "' " . $value_selected . ">" . $value['value_name'] . "</option>";

					}
					return sprintf( '<select class="prflxtrflds-wplist-select" name="prflxtrflds_options_available_values[%s]">
					<option value="">%s</option>
					%s
					</select>', $item['field_id'], __( 'Show if any values', 'profile-extra-fields' ), $prflxtrflds_option_list );
				} else {
					return '';
				}
			}

			/* Override this function to set nonce from options */
			function display_tablenav( $which ) {
				if ( 'top' == $which )
					wp_nonce_field( 'update-options' ); ?>
				<div class="tablenav <?php echo esc_attr( $which ); ?>">
					<div class="alignleft actions bulkactions"><?php $this->bulk_actions( $which ); ?></div>
					<?php $this->extra_tablenav( $which );
					$this->pagination( $which ); ?>
					<br class="clear" />
				</div>
			<?php }

			function prepare_items() {
				global $wpdb;

				$get_fields_list_sql = "SELECT `field_name`, `field_id`, `description`, `field_type_id` FROM `" . $wpdb->base_prefix . "prflxtrflds_fields_id`";
				/* Get the total number of items */
				$total_sql  = $get_fields_list_sql;
				$totalitems = $wpdb->query( $total_sql );
				/* get the value of number of items on one page */
				$perpage = $this->get_items_per_page( 'fields_per_page', 20 );
				/* the total number of pages */
				$totalpages = ceil( $totalitems / $perpage );
				/* set pagination arguments */
				$this->set_pagination_args( array(
					"total_items" 	  => $totalitems,
					"total_pages" 	  => $totalpages,
					"fields_per_page" => $perpage,
				) );
				/* calculate offset for pagination */
				$paged = ( isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) && 0 < $_GET['paged'] ) ? $_GET['paged'] : 1;
				/* set pagination arguments */
				$offset = ( $paged - 1 ) * $perpage;
				/* Save current query */
				$this->current_query = $get_fields_list_sql . " LIMIT " . $offset . ", " . $perpage;

				$available_fields = $wpdb->get_results( $get_fields_list_sql, ARRAY_A );
				if ( 0 < sizeof( $available_fields ) ) {
					/* Add available values to array with available fields */
					foreach ( $available_fields as &$field ) {
						if ( 2 == $field['field_type_id'] || 3 == $field['field_type_id'] || 4 == $field['field_type_id'] )
							$field['available_values'] = $wpdb->get_results( $wpdb->prepare( "SELECT `value_id`, `value_name` FROM " . $wpdb->base_prefix . "prflxtrflds_field_values WHERE `field_id`=%d", $field['field_id'] ), ARRAY_A );
					}
					unset( $field );
				}

				$this->_column_headers = $this->get_column_info();
				$this->items 			= $available_fields;
			}

			function column_default( $item, $column_name ) {
				/* setting default view for column items */
				switch ( $column_name ) {
					case 'field_name':
					case 'description':
					case 'show':
					case 'selected':
						return $item[ $column_name ];
					default:
						/* Show array */
						return print_r( $item, true );
				}
			}
		}
	}
}

/* Remove info about field from database */
if ( ! function_exists( 'prflxtrflds_remove_field' ) ) {
	function prflxtrflds_remove_field( $field_id ) {
		global $wpdb;
		$wpdb->delete(
			$wpdb->base_prefix . "prflxtrflds_fields_id",
			array(
				'field_id' => $field_id,
			)
		);
		$wpdb->delete(
			$wpdb->base_prefix . "prflxtrflds_roles_and_fields",
			array(
				'field_id' => $field_id,
			)
		);
		$wpdb->delete(
			$wpdb->base_prefix . "prflxtrflds_field_values",
			array(
				'field_id' => $field_id,
			)
		);
		$wpdb->delete(
			$wpdb->base_prefix . "prflxtrflds_user_field_data",
			array(
				'field_id' => $field_id,
			)
		);
	}
}

/* settings page */
if ( ! function_exists( 'prflxtrflds_settings_page' ) ) {
	function prflxtrflds_settings_page() {
		global $wpdb, $prflxtrflds_options, $wp_version, $prflxtrflds_plugin_info, $prflxtrflds_option_defaults;
		$plugin_basename = plugin_basename( __FILE__ );
		/* Remove slug */
		if ( isset( $_GET['remove'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'prflxtrflds_nonce_name' ) ) {
			if ( isset( $_GET['prflxtrflds_field_id'] ) ) {
				$field_id = filter_input( INPUT_GET, 'prflxtrflds_field_id', FILTER_SANITIZE_STRING );
				prflxtrflds_remove_field( $field_id );
			}
		} 

		/* Get all available fields and print it */
		$available_fields =  $wpdb->get_results( "SELECT `field_id` FROM `" . $wpdb->base_prefix . "prflxtrflds_fields_id` LIMIT 1;", ARRAY_A );

		if ( isset( $_GET['tab-action'] ) && 'shortcode' == $_GET['tab-action'] ) {
			
			if ( 0 < sizeof( $available_fields ) ) {

				if ( isset( $_REQUEST['prflxtrflds_form_submit'] ) && check_admin_referer( $plugin_basename, 'prflxtrflds_nonce_name' ) ) {

					$prflxtrflds_options['empty_value'] 			= stripslashes( esc_html( $_POST['prflxtrflds_empty_value'] ) );
					$prflxtrflds_options['not_available_message']	= stripslashes( esc_html( $_POST['prflxtrflds_not_available_message'] ) );
					$prflxtrflds_options['sort_sequence']			= $_POST['prflxtrflds_sort_sequence'];
					$prflxtrflds_options['show_empty_columns']		= isset( $_POST['prflxtrflds_show_empty_columns'] ) ? 1 : 0;
					$prflxtrflds_options['show_id']					= isset( $_POST['prflxtrflds_show_id'] ) ? 1 : 0;
					$prflxtrflds_options['header_table']			= $_POST['prflxtrflds_header_table'];

					$prflxtrflds_options['available_fields'] = ! empty( $_POST['prflxtrflds_options_available_fields'] ) ? $_POST['prflxtrflds_options_available_fields'] : array();
					$prflxtrflds_options['available_values'] = ! empty( $_POST['prflxtrflds_options_available_values'] ) ? $_POST['prflxtrflds_options_available_values'] : array();

					update_option( 'prflxtrflds_options', $prflxtrflds_options );
					$message = __( 'Settings saved', 'profile-extra-fields' );

				}

				if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					$prflxtrflds_options = $prflxtrflds_option_defaults;
					update_option( 'prflxtrflds_options', $prflxtrflds_options );
					$message = __( 'All plugin settings were restored.', 'profile-extra-fields' );
				}
			}				
		} ?>
		<div class="wrap">
			<h1>Profile Extra Fields</h1>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['tab-action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=profile-extra-fields.php"><?php _e( 'Extra fields', 'profile-extra-fields' ); ?></a>
				<a class="nav-tab <?php if ( isset( $_GET['tab-action'] ) && 'userdata' == $_GET['tab-action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=profile-extra-fields.php&amp;tab-action=userdata"><?php _e( 'User data', 'profile-extra-fields' ); ?></a>
				<?php if ( 0 < sizeof( $available_fields ) ) { ?>
					<a class="nav-tab <?php if ( isset( $_GET['tab-action'] ) && 'shortcode' == $_GET['tab-action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=profile-extra-fields.php&amp;tab-action=shortcode"><?php _e( 'Shortcode settings', 'profile-extra-fields' ); ?></a>
				<?php } ?>
			</h2>
			<?php /* add new/edit entry  */
			if ( isset( $_POST['prflxtrflds_new_entry'] ) || isset( $_GET['edit'] ) ) {
				prflxtrflds_edit_field();
			} elseif ( ! isset( $_GET['tab-action'] ) ) { ?>
				<form method="post" action="<?php get_admin_url(); ?>?page=profile-extra-fields.php">
					<input type="hidden" name="prflxtrflds_new_entry" value="1" />
					<p>
						<input type="submit" class="button action" name="prflxtrflds_add_new_field" value="<?php _e( 'Add new field', 'profile-extra-fields' ) ?>" />
					</p>
				</form>
				<div class="prflxtrflds-wplisttable-fullwidth-sort-container">
					<?php $prflxtrflds_fields_list_table = new Srrlxtrflds_Fields_List(); /* Wp list table to show all fields */
					$prflxtrflds_fields_list_table->prepare_items();
					if ( isset( $prflxtrflds_fields_list_table->items ) && ( 1 < sizeof( $prflxtrflds_fields_list_table->items ) ) ) { /* Show drag-n-drop message if items > 2 */?>
						<p class="hide-if-no-js prflxtrflds-hide-if-is-mobile">
							<?php _e( 'Drag each item into the order you prefer display fields on user page', 'profile-extra-fields' ); ?>
						</p>
					<?php } ?>
					<form class="prflxtrflds-wplisttable-searchform" method="get" action="<?php get_admin_url(); ?>?page=profile-extra-fields.php">
						<input type="hidden" name="page" value="profile-extra-fields.php" />
						<?php wp_nonce_field( 'prflxtrflds_nonce_name', 'prflxtrflds_nonce_name', false );
						$prflxtrflds_fields_list_table->search_box( 'search', 'search_id' ); ?>
						<?php $prflxtrflds_fields_list_table->display(); ?>
					</form>
				</div><!-- .prflxtrflds-wplisttable-container -->
			<?php } else if ( isset( $_GET['tab-action'] ) && 'userdata' == $_GET['tab-action'] ) {
				global $prflxtrflds_userdatalist_table;
				if ( ! isset( $prflxtrflds_userdatalist_table ) )
					$prflxtrflds_userdatalist_table = new Srrlxtrflds_Userdata_List();
				$prflxtrflds_userdatalist_table->prepare_items(); ?>
				<div class="prflxtrflds-wplisttable-fullwidth-container">
					<form method="get" class="prflxtrflds-wplisttable-form">
						<input type="hidden" name="page" value="profile-extra-fields.php" />
						<input type="hidden" name="tab-action" value="userdata" />
						<?php if ( ! empty( $_GET['role'] ) ) { ?>
							<input type="hidden" name="role" value="<?php echo $_GET['role']; ?>" />
						<?php }
						$prflxtrflds_userdatalist_table->search_box( 'search', 'search_id' );
						$prflxtrflds_userdatalist_table->display(); ?>
					</form>
				</div>
			<?php } else if ( isset( $_GET['tab-action'] ) && 'shortcode' == $_GET['tab-action'] && 0 < sizeof( $available_fields ) ) {
				bws_show_settings_notice();
				if ( ! empty( $message ) ) { ?>
					<div class="updated fade"><p><?php echo $message; ?></p></div>
				<?php }
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} else { ?>
					<br/>
					<div><?php printf( 
						__( "If you would like to add user data to your page or post, please use %s button", 'profile-extra-fields' ), 
						'<span class="bws_code"><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt=""/></span>' ); ?> 
						<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help">
							<div class="bws_hidden_help_text" style="min-width: 180px;">
								<?php printf( 
									__( "You can add user data to your page or post by clicking on %s button in the content edit block using the Visual mode. If the button isn't displayed, please use the shortcode %s, where you can specify a header position (top, left or right), a user role and a user ID", 'profile-extra-fields' ), 
									'<code><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt="" /></code>',
									'<code>[prflxtrflds_user_data display=* user_role=* user_id=*]</code>'
								); ?>
							</div>
						</div>
					</div>
					<form class="bws_form" method="post" action="">
						<table class="form-table">
							<tbody>
								<tr>
									<th><?php _e( 'Message for empty field', 'profile-extra-fields' ); ?></th>
									<td>
										<input type="text" name="prflxtrflds_empty_value" value="<?php echo $prflxtrflds_options['empty_value']; ?>" />
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Message for the field unavaliable for the user', 'profile-extra-fields' ); ?></th>
									<td>
										<input type="text" name="prflxtrflds_not_available_message" value="<?php echo $prflxtrflds_options['not_available_message']; ?>" />
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Sort by user name', 'profile-extra-fields' ); ?></th>
									<td>
										<select name="prflxtrflds_sort_sequence" >
											<option value="ASC" <?php selected( $prflxtrflds_options['sort_sequence'], 'ASC' ); ?>><?php _e( 'ASC', 'profile-extra-fields' ); ?></option>
											<option value="DESC" <?php selected( $prflxtrflds_options['sort_sequence'], 'DESC' ); ?>><?php _e( 'DESC', 'profile-extra-fields' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Show empty fields', 'profile-extra-fields' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="prflxtrflds_show_empty_columns" value="1" <?php checked( $prflxtrflds_options['show_empty_columns'] ); ?> />
											<?php _e( 'Show the field if the value is not filled in by any user', 'profile-extra-fields' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Show user ID', 'profile-extra-fields' ); ?></th>
									<td>
										<input type="checkbox" name="prflxtrflds_show_id" value="1" <?php checked( $prflxtrflds_options['show_id'] ) ?> />
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Position of the table header', 'profile-extra-fields' ); ?></th>
									<td>
										<select name="prflxtrflds_header_table" >
											<option value="top"<?php selected( $prflxtrflds_options['header_table'], 'top' ); ?>><?php _e( 'Top', 'profile-extra-fields' ); ?></option>
											<option value="side"<?php selected( $prflxtrflds_options['header_table'], 'side' ); ?>><?php _e( 'Side', 'profile-extra-fields' ); ?></option>
										</select>
									</td>
								</tr>
							</tbody>
						</table><!--.form-table-->
						<div class="prflxtrflds-wplisttable-container">
							<?php $prflxtrflds_shortcodelist_table = new Srrlxtrflds_Shortcode_List();
							/* Wp lis table for shortcode settings */
							$prflxtrflds_shortcodelist_table->prepare_items();
							$prflxtrflds_shortcodelist_table->views();
							$prflxtrflds_shortcodelist_table->display(); ?>
						</div><!--.prflxtrflds-wplisttable-container-->
						<p class="submit">
							<input type="hidden" name="prflxtrflds_form_submit" value="submit" />
							<?php wp_nonce_field( $plugin_basename, 'prflxtrflds_nonce_name' ); ?>
							<input id="bws-submit-button" type="submit" class="button-primary" name="prflxtrflds_save_changes" value="<?php _e( 'Save Changes', 'profile-extra-fields' ); ?>" />
						</p>
					</form>
					<?php bws_form_restore_default_settings( $plugin_basename );
				}
			}
			bws_plugin_reviews_block( $prflxtrflds_plugin_info['Name'], 'profile-extra-fields' ); ?>
		</div><!--.wrap-->
	<?php }
}

/* print shortcode */
if ( ! function_exists( 'prflxtrflds_show_data' ) ) {
	function prflxtrflds_show_data( $param ) {
		global $wpdb, $prflxtrflds_options;
		$error_message = "";
		$user_ids = array();		

		if ( ! isset( $prflxtrflds_options ) )
			$prflxtrflds_options = get_option( 'prflxtrflds_options' );

		extract( shortcode_atts( array(
			'user_id'    => '',
			'user_role'  => '',
			'display'    => '',
		), $param ) );
	
		/* Get user id param */
		if ( ! empty( $param['user_id'] ) ) {
			$user_ids = explode( ",", $param['user_id'] );
			if ( is_array( $user_ids ) ) {
				/* If lot user ids */
				foreach ( $user_ids as $user_id ) {
					/* Check for existing user */
					if ( ! is_numeric( $user_id ) || ! get_user_by( 'id', intval( $user_id ) ) ) {
						/* Show error if user id not exist, or data is uncorrect */
						$error_message = sprintf( __( 'User with entered id ( id=%s ) is not exist!', 'profile-extra-fields' ), esc_html( $user_id ) );
						break;
					}
				}
			}
		}
		/* Get user role param */
		if ( ! empty( $param['user_role'] ) ) {
			$user_roles = explode( ",", $param['user_role'] );
			if ( is_array( $user_roles ) ) {
				foreach ( $user_roles as $role ) {
					$role = str_replace( '_', ' ', stripslashes( $role ) );
					/* Check for exist user role */
					if ( $role_id = $wpdb->get_var( "SELECT `role_id` FROM `" . $wpdb->base_prefix . "prflxtrflds_roles_id` WHERE `role` LIKE '%" . $role . "%'" ) ) { /* $wpdb->prepare with % not work */
						/* Get user ids by role */
						$ids_for_role = $wpdb->get_col( $wpdb->prepare( "SELECT `user_id` FROM `" . $wpdb->base_prefix . "prflxtrflds_user_roles` WHERE `role_id`=%d", $role_id ) );
						if ( ! empty( $ids_for_role ) ) {
							$user_ids = array_merge( $user_ids, $ids_for_role );
						}
					} else {
						/* If role not exists, generate error */
						$error_message .= sprintf( __( 'Role with entered name ( %s ) is not exist! If you entered the name correctly, try enter data without whitespaces in English.', 'profile-extra-fields' ), esc_html( $role ) );
						break;
					}
				}
				/* If not exist users for choisen role. User ids is empty and select all users */
				if ( empty( $user_ids ) )
					$error_message = sprintf( __( 'For selected roles ( %s ) are no users', 'profile-extra-fields' ), esc_html( $param['user_role'] ) );
			}
		}
		/* Get display options */
		if ( ! empty( $param['display'] ) ) {
			/* If this values is not supported */
			if ( ! in_array( $param['display'], array( 'left', 'top', 'right', 'side' ) ) )
				$error_message .= sprintf( __( 'Unsupported shortcode option ( display=%s )', 'profile-extra-fields' ), esc_html( $param['display'] ) );
			else
				$display = $param['display'];
		} else {
			/* If value not in shortcode, get from options. Top by default */
			$display = isset( $prflxtrflds_options['header_table'] ) ? $prflxtrflds_options['header_table'] : 'top';
		}
		
		if ( ! empty( $error_message ) ) {
			return sprintf( '<p>%s. %s</p>', __( 'Shortcode output error', 'profile-extra-fields' ), $error_message );
		} else {

			$wp_users				= $wpdb->base_prefix . 'users';
			$table_fields_id 		= $wpdb->base_prefix . 'prflxtrflds_fields_id';
			$table_user_field_data  = $wpdb->base_prefix . 'prflxtrflds_user_field_data';			
			$table_field_values 	= $wpdb->base_prefix . 'prflxtrflds_field_values';

			$table_roles_id 		= $wpdb->base_prefix . 'prflxtrflds_roles_id';
			$table_roles_and_fields = $wpdb->base_prefix . 'prflxtrflds_roles_and_fields';
			$table_user_roles 		= $wpdb->base_prefix . 'prflxtrflds_user_roles';	

			/* Collate all users ids */
			$get_for_selected_users = '';
			if ( ! empty( $user_ids ) )				
				$get_for_selected_users = " AND `" . $table_user_roles . "`.`user_id` IN ( '" . implode( "', '", $user_ids ) . "' )";

			/* Get options - Which fields must be displayed */
			$get_for_available_fields = '';
			if ( ! empty( $prflxtrflds_options['available_fields'] ) ) {
				$field_ids = implode( "', '", $prflxtrflds_options['available_fields'] );			
				$get_for_available_fields = " AND `" . $table_fields_id . "`.`field_id` IN ('" . $field_ids . "')";
			}

			$get_for_available_field_value = '';
			if ( ! empty( $prflxtrflds_options['available_values'] ) ) {
				$i = 0;
				$extended_value = '';
				foreach ( $prflxtrflds_options['available_values'] as $field_id => $field_value ) {
					if ( '' != $field_value ) {
						if ( 0 != $i )
							$extended_value .= " OR ";

						$extended_value .= "(`user_value`='" . $field_value . "' AND `field_id`='" . $field_id . "')";
						$i++;
					}				
				}
				if ( '' != $extended_value )
					$get_for_available_field_value = " AND `" . $table_user_roles . "`.`user_id` IN 
						(SELECT `user_id` 
							FROM `" . $table_user_field_data . "` 
							WHERE " . $extended_value . ")";
			}

			$get_users_data_sql = "SELECT " . $wp_users . ".`user_nicename` , " .
 				$table_user_roles . ".`user_id`, " .
				$table_fields_id . ".`field_name`, " .
				$table_fields_id . ".`field_id`, " .
				$table_fields_id . ".`field_type_id` " .
			"FROM " . $wp_users . 
				" INNER JOIN " . $table_user_roles .
				" ON " . $table_user_roles . ".`user_id`=" . $wp_users . ".`ID` " . $get_for_selected_users . $get_for_available_field_value .
					" LEFT JOIN " . $table_roles_and_fields .
						" ON " . $table_roles_and_fields . ".`role_id`=" . $table_user_roles . ".`role_id` " .
					" LEFT JOIN " . $table_roles_id .
						" ON " . $table_roles_id . ".`role_id`=" . $table_user_roles . ".`role_id` " .
					" LEFT JOIN " . $table_fields_id .
						" ON " . $table_fields_id . ".`field_id`=" . $table_roles_and_fields . ".`field_id` " . $get_for_available_fields;

			/* group all and Add sorting order */
			$get_users_data_sql  .= " GROUP BY `" . $wp_users . "`.`ID`, `" . $table_fields_id . "`.`field_id` ORDER BY `" . $wp_users . "`.`user_nicename` " . $prflxtrflds_options['sort_sequence'];

			/* Begin collate data to print shortcode */
			ob_start();

			$printed_table = $wpdb->get_results( $get_users_data_sql, ARRAY_A );

			if ( ! empty( $printed_table ) ) {
				foreach ( $printed_table as $key => $column ) {					
					if ( ! empty( $column['field_id'] ) ) {
						if ( in_array( $column['field_type_id'], array( '2', '3', '4' ) ) ) {
							$user_value = $wpdb->get_col( $wpdb->prepare( 
								"SELECT `value_name`
								FROM " . $table_field_values .
								" WHERE `value_id` IN ( SELECT `user_value` FROM " . $table_user_field_data . " WHERE `user_id`=%d AND `field_id`=%d )",
								$column['user_id'], $column['field_id'] ) );

							$printed_table[ $key ]['value'] = implode( ', ', $user_value );
						} else {
							$printed_table[ $key ]['value'] = $wpdb->get_var( $wpdb->prepare( 
								"SELECT `user_value`
								FROM `" . $table_user_field_data . "` WHERE `user_id`= %d
									AND `field_id`=%d LIMIT 1;",
								 $column['user_id'], $column['field_id'] ) );
						}
					}
				}

				/* Get all field names */
				if ( isset( $field_ids ) ) {
					$all_fields_sql = "SELECT DISTINCT `field_id`, `field_name` FROM `" . $table_fields_id . "` WHERE `field_id` IN ('" . $field_ids . "')";
				} else {
					/* By default show all fields */
					$all_fields_sql = "SELECT DISTINCT `field_id`, `field_name` FROM " . $table_fields_id;
				}

				$all_fields = $wpdb->get_results( $all_fields_sql, ARRAY_A );
	
				/* If need not show empty collumns */
				if ( 0 == $prflxtrflds_options['show_empty_columns'] ) {
					/* delete not filled columns */
					foreach ( $all_fields as $key => $one_field ) {
						$is_empty = 1;
						foreach ( $printed_table as $printed_line ) {
							/* If field not empty */
							if ( $printed_line['field_id'] == $one_field['field_id'] ) {								
								if ( ! empty( $printed_line['value'] ) ) {
									$is_empty = 0;
									break;
								}
							}
						}
						if ( 1 == $is_empty ) {
							/* Delete if empty from all fields */
							unset( $all_fields[ $key ] );
						}
					}
				}

				if ( 'top' == $display ) { ?>
					<table class="prflxtrflds-userdata-tbl">
						<thead>
							<tr>
								<?php if ( 1 == $prflxtrflds_options['show_id'] ) { ?>
									<th><?php _e( 'User ID', 'profile-extra-fields' ); ?></th>
								<?php } ?>
								<th><?php _e( 'Username', 'profile-extra-fields' ); ?></th>
								<?php foreach ( $all_fields as $one_field ) { ?>
									<th><?php echo $one_field['field_name']; ?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $printed_table as $column_key => $column ) {
								/* If is new username */
								if ( ! isset( $printed_table[ $column_key - 1 ] ) ||
									( isset( $printed_table[ $column_key - 1 ] ) && $printed_table[ $column_key - 1 ]['user_nicename'] != $column['user_nicename'] ) ) { ?>
									<tr>
										<?php if ( 1 == $prflxtrflds_options['show_id'] ) { ?>
											<td><?php echo $column['user_id']; ?></td>
										<?php } ?>
										<td><?php echo esc_html( $column['user_nicename'] ); ?></td>
									<?php $user_fields_temp = $all_fields;
								}

								foreach ( $user_fields_temp as $key => $one_field ) {
									if ( $column['field_id'] == $one_field['field_id'] ) {
										if ( ! empty( $column['value'] ) ) {
											$user_fields_temp[ $key ]['user_value'] = esc_html( $column['value'] );
											break;
										} else {
											$user_fields_temp[ $key ]['user_value'] = $prflxtrflds_options['empty_value'];										
										}
									}									
								}
								if ( ! isset( $printed_table[ $column_key + 1 ] ) ||
									( isset( $printed_table[ $column_key + 1 ] ) && $printed_table[ $column_key + 1 ]['user_nicename'] != $column['user_nicename'] ) ) {
									if ( ! empty( $user_fields_temp ) ) {
										foreach ( $user_fields_temp as $key => $value ) {
											if ( isset( $value['user_value'] ) )
												echo '<td>' . $value['user_value'] . '</td>';
											else 
												echo '<td>' . $prflxtrflds_options['not_available_message'] . '</td>';
										}									
									} ?>
									</tr>
								<?php }
							} ?>
						</tbody>
					</table><!--.prflxtrflds-userdata-tbl-->
				<?php } else {
					$distinct_users = array();
					foreach ( $printed_table as $one_row ) {
						/* Create array of distinct users */
						if ( 0 < $one_row['user_id'] && ! isset( $distinct_users[ $one_row['user_id'] ] ) ) {
							$distinct_users[ $one_row['user_id'] ] = $one_row['user_nicename'];
						}
					} ?>
					<table>
						<?php if ( 1 == $prflxtrflds_options['show_id'] ) { ?>
						<tr>
							<th><?php _e( 'User ID', 'profile-extra-fields' ); ?></th>
							<?php foreach ( array_keys( $distinct_users ) as $user_id ) { ?>
								<td><?php echo esc_html( $user_id ); ?></td>
							<?php } ?>
						</tr>
						<?php } /* Show user name */?>
						<tr>
							<th><?php _e( 'Username', 'profile-extra-fields' ); ?></th>
							<?php foreach ( $distinct_users as $user_name ) { ?>
								<td><?php echo esc_html( $user_name ); ?></td>
							<?php } ?>
						</tr>
						<?php foreach ( $all_fields as $one_field ) { /* Create new row for every field */?>
							<tr>
								<th><?php echo esc_html( $one_field['field_name'] ); ?></th>
								<?php foreach ( array_keys( $distinct_users ) as $one_user_id ) /* Create column for every user */{
									foreach ( $printed_table as $one_row ) /* Get data for current field id and user */ {
										/* Skip if data not for current user */
										if ( $one_user_id != $one_row['user_id'] )
											continue;
										if ( $one_field['field_id'] == $one_row['field_id'] ) {
											/* If no key exist, no set $user_field_data */
											if ( key_exists( 'value', $one_row ) ) {
												if ( empty( $one_row['value'] ) ) {
													/* Empty data for empty user value */
													$user_field_data = '';
												} else {
													/* Save user value */
													$user_field_data = $one_row['value'];
												}
											}
										}
									} ?>
									<td>
										<?php if ( ! isset( $user_field_data ) ) {
											/* Current field not avaialible for current user */
											echo $prflxtrflds_options['not_available_message'];
										} elseif( empty( $user_field_data ) ) {
											/* This value is empty. Unset user data for next user */
											echo $prflxtrflds_options['empty_value'];
											unset( $user_field_data );
										} else {
											/* Print user data. Unset for next user */
											echo esc_html( $user_field_data );
											unset( $user_field_data );
										} ?>
									</td>
								<?php } ?>
							</tr>
						<?php } ?>
					</table>
				<?php }
			/* If printed table is empty */ 
			} else { ?>
				<p><?php _e( 'No data for a current shortcode settings', 'profile-extra-fields' ); ?></p>
			<?php }
			$prflxtrflds_shortcode_output = ob_get_contents();
			ob_end_clean();

			if ( ! empty( $prflxtrflds_shortcode_output ) )
				return $prflxtrflds_shortcode_output;
		}
	}
}

/*this function show content in user profile page*/
if ( ! function_exists( 'prflxtrflds_user_profile_fields' ) ) {
	function prflxtrflds_user_profile_fields() {
		global $wpdb;
		/*get user id (this method not work in my profile)*/
		$userid = isset( $_REQUEST['user_id'] ) ? intval( $_REQUEST['user_id'] ) : get_current_user_id();
		$user_info = get_userdata( $userid ); /* get userinfo by id */
		$current_role = implode( "', '", $user_info->roles );

		/* All need tables */
		$table_fields_id 		= $wpdb->base_prefix . 'prflxtrflds_fields_id';
		$table_roles_id 		= $wpdb->base_prefix . 'prflxtrflds_roles_id';
		$table_roles_and_fields = $wpdb->base_prefix . 'prflxtrflds_roles_and_fields';

		$sql_all_entry = "SELECT " . $table_fields_id . ".`field_id`, " .
			$table_fields_id . ".`field_name`, " .
			$table_fields_id . ".`required`, " .
			$table_fields_id . ".`description`, " .
			$table_fields_id . ".`field_type_id`, " .
			$table_roles_and_fields . ".`field_order`
			FROM " . $table_fields_id . ", " . $table_roles_and_fields . ", " . $table_roles_id .
			" WHERE " . $table_roles_and_fields . ".`role_id`=" . $table_roles_id . ".`role_id`" .
					" AND " . $table_roles_and_fields . ".`field_id`=" . $table_fields_id . ".`field_id`" .
					" AND " . $table_roles_id . ".`role` IN ( '" . $current_role . "' ) 
						ORDER BY " . $table_roles_and_fields . ".`field_order` ASC, " . $table_roles_and_fields . ".`field_id` ASC";

		$all_entry = $wpdb->get_results( $sql_all_entry, ARRAY_A );
		if ( ! $all_entry ) {
			/* If data for current role not exists, update table and try again */
			prflxtrflds_update_roles_id();
			$all_entry = $wpdb->get_results( $sql_all_entry, ARRAY_A );
		}

		/* Group result array by field_id */
		foreach ( $all_entry as $key => $one_entry ) {
			/* add field values */
			$all_entry[ $key ]['available_fields'] = $wpdb->get_results( 
				"SELECT `value_id`, `value_name`
				FROM `" . $wpdb->base_prefix . "prflxtrflds_field_values`
				WHERE `field_id` = '" . $one_entry['field_id'] . "' ORDER BY `order`", ARRAY_A );
			
			if ( ! empty( $_POST['prflxtrflds_user_field_value'] ) ) {
				if ( isset( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] ) && is_array( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] ) ) {
					/* for checkboxes */
					foreach ( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] as $user_value ) {
						$all_entry[ $key ]['user_value'][] = $user_value;
					}
				} else {
					$all_entry[ $key ]['user_value'] = isset( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] ) ? stripslashes( esc_html( $_POST['prflxtrflds_user_field_value'][ $one_entry['field_id'] ] ) ) : '';
				}
			} else {
				/* add selectsd values */
				if ( '2' == $one_entry['field_type_id'] ) {
					$user_value = $wpdb->get_results( "SELECT `user_value` FROM `" . $wpdb->base_prefix . "prflxtrflds_user_field_data` 
							WHERE `user_id`='" . $userid . "' AND `field_id` ='" . $one_entry['field_id'] . "'", ARRAY_A );

					if ( ! empty( $user_value ) ) {
						foreach ( $user_value as $key_value => $value_single ) {
							$all_entry[ $key ]['user_value'][] = $value_single['user_value'];
						}
					} else
						$all_entry[ $key ]['user_value'] = array();					

				} else {
					$all_entry[ $key ]['user_value'] = $wpdb->get_var( $wpdb->prepare( "SELECT `user_value` FROM `" . $wpdb->base_prefix . "prflxtrflds_user_field_data` 
							WHERE `user_id`= %s AND `field_id` ='" . $one_entry['field_id'] . "'", $userid ) );
				}
			}
		}		

		if ( 0 < sizeof( $all_entry ) ) { /* Render user data */ ?>
			<!-- Begin code from user role extra field -->
			<h2><?php _e( "Extra profile information", "profile-extra-fields" ); ?></h2>
			<table class="form-table">
				<?php foreach ( $all_entry as $one_entry ) { ?>
					<tr>
						<th>
							<?php echo $one_entry['field_name'];
							if ( "1" == $one_entry['required'] ) { ?>
								<span class="description"><?php _e( '(required)', 'profile-extra-fields' )?></span>
								<input type="hidden" name="prflxtrflds_required[<?php echo $one_entry['field_id']; ?>]" value="true">
							<?php } ?>
							<input type="hidden" name="prflxtrflds_field_name[<?php echo $one_entry['field_id']; ?>]" value="<?php echo $one_entry['field_name']; ?>">
						</th>
						<td>
							<?php switch ( $one_entry['field_type_id'] ) {
								case '1': ?>
										<input type="text" id="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" name="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" value="<?php if ( isset( $one_entry['user_value'] ) ) echo $one_entry['user_value']; ?>">
									<?php break;
								case '2':
										foreach ( $one_entry['available_fields'] as $one_sub_entry ) { ?>
											<label>
												<input type="checkbox" name="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>][]" value="<?php echo $one_sub_entry['value_id']; ?>"<?php if ( ! empty( $one_entry['user_value'] ) && in_array( $one_sub_entry['value_id'], $one_entry['user_value'] ) ) echo " checked"; ?> />
												<?php echo $one_sub_entry['value_name']; ?>
											</label>
											<br />
										<?php }
									break;
								case '3':
										foreach ( $one_entry['available_fields'] as $one_sub_entry ) { ?>
											<label>
												<input type="radio" name="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" value="<?php echo $one_sub_entry['value_id']; ?>"<?php if ( isset( $one_entry['user_value'] ) && $one_sub_entry['value_id'] == $one_entry['user_value'] ) echo " checked"; ?>>
												<?php echo $one_sub_entry['value_name']; ?>
											</label>
											<br />
										<?php }
									break;
								case '4': ?>
										<select id="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" name="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]">
											<option></option>
											<?php foreach ( $one_entry['available_fields'] as $one_sub_entry ) { ?>
												<option value="<?php echo $one_sub_entry['value_id']; ?>"<?php if ( isset( $one_entry['user_value'] ) && $one_sub_entry['value_id'] == $one_entry['user_value'] ) echo " selected"; ?>><?php echo $one_sub_entry['value_name']; ?></option>
											<?php } ?>
										</select>								
									<?php break;
								case '8': ?>										
										<input type="number" id="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" name="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" value="<?php if ( isset( $one_entry['user_value'] ) ) echo $one_entry['user_value']; ?>">
									<?php break;
								case '5': ?>
										<input class="prflxtrflds_datetimepicker" type="text" id="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" name="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" value="<?php if ( isset( $one_entry['user_value'] ) ) echo $one_entry['user_value']; ?>">
										 <?php if ( strripos( $one_entry['available_fields'][0]['value_name'], 'T' ) ) echo date_i18n( 'T' ); ?>
										<input type="hidden" name="prflxtrflds_date_format" value="<?php echo trim( str_replace( 'T', '', $one_entry['available_fields'][0]['value_name'] ) ); ?>">
										<input type="hidden" name="prflxtrflds_user_field_datetime[<?php echo $one_entry['field_id']; ?>]" value="<?php echo $one_entry['available_fields'][0]['value_name']; ?>">
									<?php break;
								case '6': ?>
										<input class="prflxtrflds_datetimepicker" type="text" id="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" name="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" value="<?php if ( isset( $one_entry['user_value'] ) ) echo $one_entry['user_value']; ?>">
										 <?php if ( strripos( $one_entry['available_fields'][0]['value_name'], 'T' ) ) echo date_i18n( 'T' ); ?>
										<input type="hidden" name="prflxtrflds_time_format" value="<?php echo trim( str_replace( 'T', '', $one_entry['available_fields'][0]['value_name'] ) ); ?>">
										<input type="hidden" name="prflxtrflds_user_field_datetime[<?php echo $one_entry['field_id']; ?>]" value="<?php echo $one_entry['available_fields'][0]['value_name']; ?>">
									<?php break;
								case '7': 
										$date_and_time = unserialize( $one_entry['available_fields'][0]['value_name'] ); ?>
										<input class="prflxtrflds_datetimepicker" type="text" id="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" name="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" value="<?php if ( isset( $one_entry['user_value'] ) ) echo $one_entry['user_value']; ?>">
										  <?php if ( strripos( $date_and_time['time'], 'T' ) || strripos(  $date_and_time['date'], 'T' ) ) echo date_i18n( 'T' ); ?>
										<input type="hidden" name="prflxtrflds_time_format" value="<?php echo trim( str_replace( 'T', '', $date_and_time['time'] ) ); ?>">
										<input type="hidden" name="prflxtrflds_date_format" value="<?php echo trim( str_replace( 'T', '', $date_and_time['date'] ) ); ?>">
										<input type="hidden" name="prflxtrflds_user_field_datetime[<?php echo $one_entry['field_id']; ?>]" value="<?php echo $date_and_time['date'] . ' ' . $date_and_time['time']; ?>">
									<?php break;
								case '9': ?>
										<input type="text" id="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" name="prflxtrflds_user_field_value[<?php echo $one_entry['field_id']; ?>]" value="<?php if ( isset( $one_entry['user_value'] ) ) echo $one_entry['user_value']; ?>">
										<input type="hidden" name="prflxtrflds_user_field_pattern[<?php echo $one_entry['field_id']; ?>]" value="<?php echo $one_entry['available_fields'][0]['value_name']; ?>">
									<?php break;
							}
							if ( isset( $one_entry['description'] ) ) { ?>
								<p class="description"> <?php echo $one_entry['description']; ?></p>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			</table><!--.form-table-->
		<?php }
	}
}

/* Send errors to edit user page */
if ( ! function_exists( 'prflxtrflds_create_user_error' ) ) {
	function prflxtrflds_create_user_error( $errors, $update = null, $user = null ) {
		$required_array = array();

		if ( ! empty( $_POST['prflxtrflds_required'] ) ) {
			/* Get all reqired ids */
			foreach ( $_POST['prflxtrflds_required'] as $required_id => $required_value ) {
				if ( empty( $_POST['prflxtrflds_user_field_value'][ $required_id ] ) ) {
					/* Error for non-textfield */
					$errors->add( 'prflxtrflds_required_error', sprintf( __( 'Required field %s is not filled. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $_POST['prflxtrflds_field_name'][ $required_id ] . '</strong>' ) );
					$required_array[] = $required_id;
				}
			}
		}
		if ( ! empty( $_POST['prflxtrflds_user_field_pattern'] ) ) {
			foreach ( $_POST['prflxtrflds_user_field_pattern'] as $field_id => $pattern ) {
				if ( ! empty( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) && ! in_array( $field_id, $required_array ) ) {
					if ( ! preg_match( '/^' . str_replace( '\*', '[0-9]', preg_quote( $pattern ) ) . '$/',  $_POST['prflxtrflds_user_field_value'][ $field_id ] ) )
						$errors->add( 'prflxtrflds_match_error', sprintf( __( 'Field %s do not match %s. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $_POST['prflxtrflds_field_name'][ $field_id ] . '</strong>', '<strong>' . $pattern . '</strong>' ) );
				}
			}
		}

		if ( ! empty( $_POST['prflxtrflds_user_field_datetime'] ) ) {
			foreach ( $_POST['prflxtrflds_user_field_datetime'] as $field_id => $pattern ) {
				if ( ! empty( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) && ! in_array( $field_id, $required_array ) ) {
					$pattern = trim( str_replace( 'T', '', $pattern ) );
					if ( function_exists( 'date_create_from_format' ) ) {
						$d = date_create_from_format( $pattern, $_POST['prflxtrflds_user_field_value'][ $field_id ] );					
						if ( ! $d || ! $d->format( $pattern ) == $_POST['prflxtrflds_user_field_value'][ $field_id ] )
							$errors->add( 'prflxtrflds_match_error', sprintf( __( 'Field %s do not match %s. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $_POST['prflxtrflds_field_name'][ $field_id ] . '</strong>', '<strong>' . $pattern . '</strong>' ) );
					} elseif ( ! strtotime( $_POST['prflxtrflds_user_field_value'][ $field_id ] ) ) {
						$errors->add( 'prflxtrflds_match_error', sprintf( __( 'Field %s do not match %s. Data was not saved!', 'profile-extra-fields' ), '<strong>' . $_POST['prflxtrflds_field_name'][ $field_id ] . '</strong>', '<strong>' . $pattern . '</strong>' ) );
					}
				}
			}
		}
	}
}

/* Save user data from Edit user page */
if ( ! function_exists( 'prflxtrflds_save_user_data' ) ) {
	function prflxtrflds_save_user_data() {
		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : get_current_user_id();
		/* Get errors */
		$errors = edit_user( $user_id );
		if ( ! is_wp_error( $errors ) && ! empty( $_POST['prflxtrflds_user_field_value'] ) ) {
			global $wpdb;
			/* If array exists ( exist available fields for current user ), remove old data */
			$wpdb->delete(
				$wpdb->base_prefix . 'prflxtrflds_user_field_data',
				array( 'user_id' => $user_id )
			);

			/* Create array with user values */
			foreach ( $_POST['prflxtrflds_user_field_value'] as $id => $val ) {
				if ( is_array( $val ) ) {
					/* for checkboxes */
					foreach ( $val as $user_value ) {
						/* insert or update value  */
						$wpdb->replace(
							$wpdb->base_prefix . 'prflxtrflds_user_field_data',
							array(
								'user_id' 		=> $user_id,
								'field_id' 		=> $id,
								'user_value' 	=> $user_value
							)
						);
					}
				} else {
					$user_value = stripslashes( esc_html( $val ) );
					/* insert or update value  */
					$wpdb->replace(
						$wpdb->base_prefix . 'prflxtrflds_user_field_data',
						array(
							'user_id' 		=> $user_id,
							'field_id' 		=> $id,
							'user_value' 	=> $user_value
						)
					);
				}
			}
		}
	}
}

/* Save field order from wp list table */
if ( ! function_exists( 'prflxtrflds_table_order' ) ) {
	function prflxtrflds_table_order() {
		/* Check ajax. Function fie if error */
		check_ajax_referer( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' );
		/* If check ok, edit order */
		if ( isset( $_POST['table_order'] ) ) {
			/* Into string is values with coma separate */
			$sort_parametrs = filter_input( INPUT_POST, 'table_order', FILTER_SANITIZE_STRING );

			/* Role id = 0 for 'all' users */
			$role_id = ( isset( $_POST['field_id'] ) && 'all' != $_POST['field_id'] ) ? intval( $_POST['field_id'] ) : 0 ;

			if ( '0' != $sort_parametrs ) {
				global $wpdb;
				$table_roles_and_fields = $wpdb->base_prefix . "prflxtrflds_roles_and_fields";
				/* Create array */
				$sort_parametrs = explode( ', ', $sort_parametrs );
				if ( is_array( $sort_parametrs ) ) {
					$i = 0;
					foreach ( $sort_parametrs as $field_id ) {
						$field_id = intval( $field_id );
						if ( 0 !=$role_id ) {
							$wpdb->update(
								$table_roles_and_fields,
								array(
									'field_order' => $i,
								),
								array(
									'field_id'	=> $field_id,
									'role_id'	=> $role_id,
								),
								array( '%d' ),
								array( '%d', '%d' )
							);
						} else {
							/* If role id == 0, change sort settings for all roles */
							$wpdb->update(
								$table_roles_and_fields,
								array(
									'field_order' => $i,
								),
								array(
									'field_id'	=> $field_id,
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
		/* Check ajax. Function fie if error */
		check_ajax_referer( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' );
		$users = get_users();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) { ?>
				<option value="<?php echo $user->ID; ?>"><?php echo esc_html( $user->display_name ); ?></option>
			<?php }
		}
		wp_die();
	}
}

if ( ! function_exists( 'prflxtrflds_get_roles' ) ) {
	function prflxtrflds_get_roles() {
		global $wpdb;
		/* Check ajax. Function fie if error */
		check_ajax_referer( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' );
		$roles = $wpdb->get_results( "SELECT `role` FROM `" . $wpdb->base_prefix . "prflxtrflds_roles_id`", ARRAY_A );
		if ( ! empty( $roles ) ) {
			foreach ( $roles as $role ) { ?>
				<option value="<?php echo strtolower( $role['role'] ); ?>"><?php echo $role['role']; ?></option>
			<?php }
		}
		wp_die();
	}
}

/* add shortcode content  */
if ( ! function_exists( 'prflxtrflds_shortcode_button_content' ) ) {
	function prflxtrflds_shortcode_button_content( $content ) {
		global $wp_version, $prflxtrflds_options; 
		if ( empty( $prflxtrflds_options ) )
			$prflxtrflds_options = get_option( 'prflxtrflds_options' ); ?>
		<div id="prflxtrflds" style="display:none;">
			<fieldset>
				<label>	
					<select id='prflxtrflds_header_table' name="prflxtrflds_header_table">
						<option value="top"<?php selected( $prflxtrflds_options['header_table'], 'top' ); ?>><?php _e( 'Top', 'profile-extra-fields' ); ?></option>
						<option value="side"<?php selected( $prflxtrflds_options['header_table'], 'side' ); ?>><?php _e( 'Side', 'profile-extra-fields' ); ?></option>
					</select>
					<span class="title"><?php _e( 'Position of the table header', 'profile-extra-fields' ); ?></span>
				</label><br/>
				<label>
					<input id="prflxtrflds_specify_role" type="checkbox" value="prflxtrflds_specify_role" />
					<span><?php _e( 'Specify a user role', 'profile-extra-fields' ); ?></span><br/>
					<img id='prflxtrflds_role_loader' class="hidden" src="<?php echo plugins_url( 'images/loader.gif', __FILE__ ); ?>" alt="Loading" />
					<select id='prflxtrflds_user_role' name="prflxtrflds_user_role" multiple="multiple" style="max-height: 70px; width: 355px;" class="hidden"></select>
				</label><br/>
				<label>
					<input id="prflxtrflds_specify_user" type="checkbox" value="prflxtrflds_specify_user" />
					<span><?php _e( 'Specify a user', 'profile-extra-fields' ); ?></span><br/>
					<img id='prflxtrflds_user_loader' class="hidden" src="<?php echo plugins_url( 'images/loader.gif', __FILE__ ); ?>" alt="Loading" />
					<select id='prflxtrflds_user' name="prflxtrflds_user" multiple="multiple" style="max-height: 55px; width: 355px;" class="hidden"></select>
				</label><br/>
			</fieldset>
			<input class="bws_default_shortcode" type="hidden" name="default" value="[prflxtrflds_user_data]" />
			<script type="text/javascript">
				function prflxtrflds_get_shortcode( current_object ) {
					(function($) {
						var header = $( current_object + ' #prflxtrflds_header_table option:selected' ).val();
						if ( $( current_object + ' #prflxtrflds_specify_role' ).is( ':checked' ) ) {
							var user_role = $( current_object + ' #prflxtrflds_user_role option:selected' ).map(function(){ return this.value.replace(/ /gi, '_'); }).get().join(",");
						}
						if ( $( current_object + ' #prflxtrflds_specify_user' ).is( ':checked' ) ) {
							var user = $( current_object + ' #prflxtrflds_user option:selected' ).map(function(){ return this.value }).get().join(",");
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

						$( current_object + ' #bws_shortcode_display' ).text( shortcode );
					})(jQuery);
				}
				function prflxtrflds_shortcode_init() {
					(function($) {	
						<?php if ( $wp_version < '3.9' ) { ?>	
							var current_object = '#TB_ajaxContent';
						<?php } else { ?>
							var current_object = '.mce-reset';
						<?php } ?>	

						$( current_object + ' #prflxtrflds_specify_user' ).on( 'change', function() {							
							if ( $( this ).is( ':checked' ) ) {
								$( current_object + ' #prflxtrflds_user_role' ).hide();
								if ( $( current_object + ' #prflxtrflds_user_loader' ).length > 0 ) {
									$( current_object + ' #prflxtrflds_user_loader' ).show();
									$.ajax({
										url: ajaxurl,
										type: "POST",
										data: 'action=prflxtrflds_get_users&prflxtrflds_ajax_nonce_field=<?php echo wp_create_nonce( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' ); ?>',
										success: function( result ) {
											$( current_object + ' #prflxtrflds_user' ).html( result );
											$( current_object + ' #prflxtrflds_user' ).show();
											$( current_object + ' #prflxtrflds_user_loader' ).remove();
										},
										error: function( request, status, error ) {
											console.log( error + request.status );
										}
									});
								} else {
									$( current_object + ' #prflxtrflds_user' ).show();
								}
							} else {
								$( current_object + ' #prflxtrflds_user' ).hide();
								prflxtrflds_get_shortcode( current_object );	
							}
						});
						$( current_object + ' #prflxtrflds_specify_role' ).on( 'change', function() {
							if ( $( this ).is( ':checked' ) ) {
								$( current_object + ' #prflxtrflds_user' ).hide();							
								if ( $( current_object + ' #prflxtrflds_role_loader' ).length > 0 ) {
									$( current_object + ' #prflxtrflds_role_loader' ).show();
									$.ajax({
										url: ajaxurl,
										type: "POST",
										data: 'action=prflxtrflds_get_roles&prflxtrflds_ajax_nonce_field=<?php echo wp_create_nonce( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' ); ?>',
										success: function( result ) {
											$( current_object + ' #prflxtrflds_user_role' ).html( result );
											$( current_object + ' #prflxtrflds_user_role' ).show();
											$( current_object + ' #prflxtrflds_role_loader' ).remove();
										},
										error: function( request, status, error ) {
											console.log( error + request.status );
										}
									});
								} else {
									$( current_object + ' #prflxtrflds_user_role' ).show();
								}
							} else {
								$( current_object + ' #prflxtrflds_user_role' ).hide();
								prflxtrflds_get_shortcode( current_object );
							}
						});

						$( current_object + ' #prflxtrflds_header_table, ' + current_object + ' #prflxtrflds_user_role, ' + current_object + ' #prflxtrflds_user' ).on( 'change', function() {
							prflxtrflds_get_shortcode( current_object );
						});         
					})(jQuery);
				}
			</script>
			<div class="clear"></div>
		</div>
	<?php }
}

/* This links under plugin name */
if ( ! function_exists ( 'prflxtrflds_plugin_action_links' ) ) {
	function prflxtrflds_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=profile-extra-fields.php">' . __( 'Settings', 'profile-extra-fields' ) . '</a>';
				array_unshift( $links, $settings_link );	/* add settings link to begin of array */
			}
		}
		return $links;
	}
}

/* This links in plugin description */
if ( ! function_exists ( 'prflxtrflds_register_plugin_links' ) ) {
	function prflxtrflds_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[] = '<a href="admin.php?page=profile-extra-fields.php">' . __( 'Settings', 'profile-extra-fields' ) . '</a>';
			$links[] = '<a href="http://bestwebsoft.com/products/profile-extra-fields/">' . __( 'FAQ', 'profile-extra-fields' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support', 'profile-extra-fields' ) . '</a>';
		}
		return $links;
	}
}

/* add admin notices */
if ( ! function_exists ( 'prflxtrflds_admin_notices' ) ) {
	function prflxtrflds_admin_notices() {
		global $hook_suffix, $prflxtrflds_plugin_info;
		if ( 'plugins.php' == $hook_suffix && ! is_network_admin() ) {
			bws_plugin_banner_to_settings( $prflxtrflds_plugin_info, 'prflxtrflds_options', 'profile-extra-fields', 'admin.php?page=profile-extra-fields.php' );
		}
	}
}

/* Register scripts */
if ( ! function_exists('prflxtrflds_load_script') ) {
	function prflxtrflds_load_script() {
		global $hook_suffix;
		if ( isset( $_GET['page'] ) && 'profile-extra-fields.php' == $_GET['page'] ) {
			wp_enqueue_style( 'prflxtrflds_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );

			if ( wp_is_mobile() )
				wp_enqueue_script( 'jquery-touch-punch' );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'prflxtrflds_script', plugins_url( '/js/script.js', __FILE__ ) );
			$script_vars = array(
				'prflxtrflds_ajax_url'	=> admin_url( 'admin-ajax.php' ),
				'prflxtrflds_nonce' 	=> wp_create_nonce( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' )
			);
			wp_localize_script( 'prflxtrflds_script', 'prflxtrflds_ajax', $script_vars );
		}
		if ( 'user-edit.php' == $hook_suffix || 'profile.php' == $hook_suffix ) {
			wp_enqueue_style( 'jquery.datetimepicker.css', plugins_url( 'css/jquery.datetimepicker.css', __FILE__ ) );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery.datetimepicker.full.min.js', plugins_url( '/js/jquery.datetimepicker.full.min.js', __FILE__ ) );
			
			wp_enqueue_script( 'prflxtrflds_profile_script', plugins_url( '/js/profile_script.js', __FILE__ ) );
			$script_vars = array(
				'prflxtrflds_nonce' 	=> wp_create_nonce( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' )
			);
			wp_localize_script( 'prflxtrflds_profile_script', 'prflxtrflds_vars', $script_vars );
			
		}
	}
}

/* Uninstall plugin */
if ( ! function_exists( 'prflxtrflds_uninstall' ) ) {
	function prflxtrflds_uninstall() {
		global $wpdb;
		/* Drop all plugin tables */
		$table_names = array(
			'`' . $wpdb->base_prefix . 'prflxtrflds_fields_id`',
			'`' . $wpdb->base_prefix . 'prflxtrflds_field_types`',
			'`' . $wpdb->base_prefix . 'prflxtrflds_field_values`',
			'`' . $wpdb->base_prefix . 'prflxtrflds_roles_and_fields`',
			'`' . $wpdb->base_prefix . 'prflxtrflds_roles_id`',
			'`' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`',
			'`' . $wpdb->base_prefix . 'prflxtrflds_user_roles`',
		);
		$wpdb->query( "DROP TABLE IF EXISTS " . implode( ', ', $table_names ) );
		/* Delete options */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( 'prflxtrflds_options' );
			}
			switch_to_blog( $old_blog );
		} else {
			delete_option( 'prflxtrflds_options' );
		}
	}
}

register_activation_hook( __FILE__, 'prflxtrflds_create_table' );
/*bws menu*/
add_action( 'admin_menu', 'prflxtrflds_admin_menu' );
/*plugin init*/
add_action( 'init', 'prflxtrflds_init' );
add_action( 'admin_init', 'prflxtrflds_admin_init' );
add_action( 'plugins_loaded', 'prflxtrflds_plugins_loaded' );
/* This links under plugin name */
add_filter( 'plugin_action_links', 'prflxtrflds_plugin_action_links', 10, 2 );
/* This links in plugin description */
add_filter( 'plugin_row_meta', 'prflxtrflds_register_plugin_links', 10, 2 );
/* add admin notices */
add_action( 'admin_notices', 'prflxtrflds_admin_notices' );
/*add basic shortcode*/
add_shortcode( 'prflxtrflds_user_data', 'prflxtrflds_show_data' );
add_filter( 'widget_text', 'do_shortcode' );
/* update table if user create */
add_action( 'user_register', 'prflxtrflds_update_user_roles', 10, 2 );
/* update table on edit user profile */
add_action( 'profile_update', 'prflxtrflds_update_user_roles', 10, 2 );
/* update on set user role */
add_action( 'set_user_role', 'prflxtrflds_update_user_roles', 10, 2 );
/*show info in user profile page*/
add_action( 'show_user_profile', 'prflxtrflds_user_profile_fields' );
add_action( 'edit_user_profile', 'prflxtrflds_user_profile_fields' );
/* save user information where Save button is pressed */
add_action( 'edit_user_profile_update', 'prflxtrflds_save_user_data' );
add_action( 'personal_options_update', 'prflxtrflds_save_user_data' );
/* load scripts */
add_action( 'admin_enqueue_scripts', 'prflxtrflds_load_script' );
/* Check fields from user settings page */
add_filter( 'user_profile_update_errors', 'prflxtrflds_create_user_error' );
/* Save order through ajax */
add_action( 'wp_ajax_prflxtrflds_table_order', 'prflxtrflds_table_order' );
add_action( 'wp_ajax_prflxtrflds_get_users', 'prflxtrflds_get_users' );
add_action( 'wp_ajax_prflxtrflds_get_roles', 'prflxtrflds_get_roles' );
/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'prflxtrflds_shortcode_button_content' );
/* Uninstall plugin */
register_uninstall_hook( __FILE__, 'prflxtrflds_uninstall' );