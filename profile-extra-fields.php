<?php
/*
Plugin Name: Profile Extra Fields by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: Plugin Profile Extra Fields add extra data to user profile page.
Author: BestWebSoft
Version: 1.0.0
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
		bws_add_general_menu( plugin_basename( __FILE__ ) );
		$hook = add_submenu_page( 'bws_plugins', __( 'Profile Extra Fields Settings', 'profile_extra_fields' ), 'Profile Extra Fields', 'manage_options', 'profile-extra-fields.php', 'prflxtrflds_settings_page' );
		add_action( "load-$hook", 'prflxtrflds_screen_options' );
	}
}

/*plugin init*/
if ( ! function_exists ( 'prflxtrflds_init' ) ) {
	function prflxtrflds_init() {
		global $prflxtrflds_plugin_info;
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'profile_extra_fields', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		/*add bws menu. use in prflxtrflds_admin_menu*/
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_functions.php' );
		/* Get plugin data */
		if ( empty( $prflxtrflds_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$prflxtrflds_plugin_info = get_plugin_data( __FILE__ );
		}
		/* Function check if plugin is compatible with current WP version */
		bws_wp_version_check( 'profile-extra-fields/profile-extra-fields.php', $prflxtrflds_plugin_info, '3.8' );
	}
}

/* admin init */
if ( ! function_exists ( 'prflxtrflds_admin_init' ) ) {
	function prflxtrflds_admin_init() {
		global $bws_plugin_info, $prflxtrflds_plugin_info;
		/* Add variable for bws_menu */
		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '300', 'version' => $prflxtrflds_plugin_info["Version"] );
		/* Call register settings function */
		if ( isset( $_GET['page'] ) && 'profile-extra-fields.php' == $_GET['page'] )
			prflxtrflds_settings();
	}
}

/* update new users and roles */
if ( ! function_exists( 'prflxtrflds_update_users' ) ) {
	function prflxtrflds_update_users() {
		global $wpdb;
		$table_name = $wpdb->base_prefix . "prflxtrflds_user_data";

		$users_data_from_db =  $wpdb->get_results( "SELECT `id`, `role` FROM " . $table_name, ARRAY_A );
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
					$wpdb->insert( $table_name, array( 'userid' => $user->ID, 'role' => implode( ', ', $user->roles ) ) );
				}
			}
		}
	}
}

/* this is settings functions */
if ( ! function_exists( 'prflxtrflds_settings' ) ) {
	function prflxtrflds_settings() {
		global $prflxtrflds_field_type_id, $prflxtrflds_options, $prflxtrflds_plugin_info;
		/* Conformity between field type id and field type name */
		if ( empty( $prflxtrflds_field_type_id ) ) {
			$prflxtrflds_field_type_id = array(
					'1' => __( 'Textfield', 'profile_extra_fields' ),
					'2' => __( 'Checkbox', 'profile_extra_fields' ),
					'3' => __( 'Radiobutton', 'profile_extra_fields' ),
					'4' => __( 'Drop down list', 'profile_extra_fields' ),
			);
		}
		/* Db version in plugin */
		$prflxtrflds_db_version = 1;
		/* Create array with default options */
		$prflxtrflds_option_defaults = array(
				'sort_sequence'			=> 'asc',
				'available_fields'		=> '',
				'available_values'		=> '',
				'show_empty_columns'	=> '1',
				'show_id'	            => '1',
				'header_table'          => 'top',
				'empty_value'			=> __( 'Field not filled', 'profile_extra_fields' ),
				'not_available_message'	=> __( 'N/A', 'profile_extra_fields' ),
				'plugin_db_version'		=> $prflxtrflds_db_version,
				'plugin_option_version'	=> $prflxtrflds_plugin_info["Version"],
		);
		/* In prflxtrflds_settings_page add hidden field to save values after option update (!) */
		if ( ! get_option( 'prflxtrflds_options' ) ) {
			/* Set default options */
			add_option( 'prflxtrflds_options', $prflxtrflds_option_defaults );
			/* And create database */
			prflxtrflds_create_table();
		}
		/* Get options from database */
		$prflxtrflds_options = get_option( 'prflxtrflds_options' );
		/* Update options if other option version */
		if ( ! isset( $prflxtrflds_options['plugin_option_version'] ) || $prflxtrflds_options['plugin_option_version'] != $prflxtrflds_plugin_info["Version"] ) {
			$prflxtrflds_options = array_merge( $prflxtrflds_option_defaults, $prflxtrflds_options );
			$prflxtrflds_options['plugin_option_version'] = $prflxtrflds_plugin_info["Version"];
			$update_option = true;
		}
		/* Update database */
		if ( ! isset( $prflxtrflds_options['plugin_db_version'] ) || $prflxtrflds_options['plugin_db_version'] != $prflxtrflds_db_version ) {
			prflxtrflds_create_table();
			$prflxtrflds_options['plugin_db_version'] = $prflxtrflds_db_version;
			$update_option = true;
		}
		/* If option was updated */
		if ( isset( $update_option ) ) {
			update_option( 'prflxtrflds_options', $prflxtrflds_options );
		}
	}
}

if ( ! function_exists( 'prflxtrflds_create_table' ) ) {
	function prflxtrflds_create_table() {
		global $wpdb;
		/* require db Delta */
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$table_name = $wpdb->base_prefix . "prflxtrflds_roles_id";
		/* create table for roles types */
		$sql = "CREATE TABLE " . $table_name . " (
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

		$table_name = $wpdb->base_prefix . "prflxtrflds_user_roles";
		/* create table for conformity user_id and user role id */
		$sql = "CREATE TABLE " . $table_name . " (
			user_id bigint(20) NOT NULL,
			role_id bigint(20) NOT NULL,
			UNIQUE KEY  (user_id)
			);";
		/* call dbDelta */
		dbDelta( $sql );
		/* Create roles id */
		if ( function_exists( 'prflxtrflds_update_user_roles' ) ) {
			prflxtrflds_update_user_roles();
		}

		$table_name = $wpdb->base_prefix . "prflxtrflds_fields_id";
		$sql = "CREATE TABLE " . $table_name . " (
			field_id bigint(20) NOT NULL AUTO_INCREMENT,
			field_name text NOT NULL COLLATE utf8_general_ci,
			required int(1) NOT NULL DEFAULT '0',
			show_default int(1) NOT NULL DEFAULT '0',
			show_always int(1) NOT NULL DEFAULT '0',
			description text NOT NULL COLLATE utf8_general_ci,
			UNIQUE KEY  (field_id)
			);";
		/* call dbDelta */
		dbDelta( $sql );

		$table_name = $wpdb->base_prefix . "prflxtrflds_roles_and_fields";
		/* create table conformity roles id with fields id */
		$sql = "CREATE TABLE " . $table_name . " (
			role_id bigint(20) NOT NULL DEFAULT '0',
			field_id bigint(20) NOT NULL DEFAULT '0',
			field_order bigint(20) NOT NULL DEFAULT '0',
			UNIQUE KEY  (role_id, field_id)
			);";
		/* call dbDelta */
		dbDelta( $sql );

		$table_name = $wpdb->base_prefix . "prflxtrflds_field_types";
		/* create table conformity field_type_id and field type name */
		$sql = "CREATE TABLE " . $table_name . " (
			field_id bigint(20) NOT NULL DEFAULT '0',
			field_type_id bigint(20) NOT NULL DEFAULT '0',
			UNIQUE KEY  (field_id)
			);";
		/* 1 - textfield
		 * 2 - checkbox
		 * 3 - radiobutton
		 * 4 - select
		 * */
		/* call dbDelta */
		dbDelta( $sql );

		$table_name = $wpdb->base_prefix . "prflxtrflds_field_values";
		/* create table conformity field id with available value */
		$sql = "CREATE TABLE " . $table_name . " (
			value_id bigint(20) NOT NULL AUTO_INCREMENT,
			field_id bigint(20) NOT NULL DEFAULT '0',
			value_name text NOT NULL COLLATE utf8_general_ci,
			UNIQUE KEY  (value_id)
			);";
		/* call dbDelta */
		dbDelta( $sql );

		$table_name = $wpdb->base_prefix . "prflxtrflds_user_field_data";
		/* create table conformity field id with available value */
		$sql = "CREATE TABLE " . $table_name . " (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			value_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			user_value text NOT NULL COLLATE utf8_general_ci,
			UNIQUE KEY  (id)
			);";
		/* call dbDelta */
		dbDelta( $sql );
	}
}

/* Create conformity between roles and role_id */
if ( ! function_exists( 'prflxtrflds_update_roles_id' ) ) {
	function prflxtrflds_update_roles_id() {
		global $wpdb, $wp_roles;
		$prflxtrflds_table_name = $wpdb->base_prefix . "prflxtrflds_roles_id";
		/* Get all available role */
		$all_roles = $wp_roles->roles;
		if ( ! empty( $all_roles ) ) {
			/* Check role for existing in plugin table */
			$check_sql = "SELECT `role_id` FROM " . $prflxtrflds_table_name . " WHERE `role` = %s LIMIT 1";
			/* Get role name from array */
			foreach ( $all_roles as $role ) {
				if ( ! $wpdb->get_var( $wpdb->prepare( $check_sql, $role['name'] ) ) ) {
					/* Create field if not exist */
					$wpdb->insert(
						$prflxtrflds_table_name,
						array(
							'role' => $role['name'],
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
		if ( ( NULL != $user_id ) && ( ( NULL == $role ) || ( ! is_string( $role ) ) ) ) {
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
					'role_id' => $role_id,
				)
			);
		}
	}
}

/* Edit or create new field */
if ( ! function_exists( 'prflxtrflds_edit_field' ) ) {
	function prflxtrflds_edit_field( $field_id = NULL, $prflxtrflds_error = NULL, $prflxtrflds_is_old_field = NULL ) {
		global $wpdb, $prflxtrflds_field_type_id, $prflxtrflds_error;
		/* Check user data */
		/* if $prflxtrflds_error not empty. get data from $_POST */
		if ( ! empty( $prflxtrflds_error ) && ! empty( $_POST ) ) {
			$field_name 	= isset( $_POST['prflxtrflds_field_name'] ) ? stripslashes( esc_html( $_POST['prflxtrflds_field_name'] ) ) : '';
			$field_type_id  = isset( $_POST['prflxtrflds_type'] ) ? intval( $_POST['prflxtrflds_type'] ) : '';
			$description 	= isset( $_POST['prflxtrflds-description'] ) ? stripslashes( esc_html( $_POST['prflxtrflds-description'] ) ) : '';
			$checked_roles  = isset( $_POST['prflxtrflds_roles'] ) ? array_map( 'stripslashes_deep', $_POST['prflxtrflds_roles'] ) : array(); /* is array */
			$field_order 	= isset( $_POST['prflxtrflds_order'] ) ? intval( $_POST['prflxtrflds_order'] ) : '0';
			$field_required = isset( $_POST['prflxtrflds_required'] ) ? intval( $_POST['prflxtrflds_required'] ) : 0;
			$field_show_default = isset( $_POST['prflxtrflds_show_default'] ) ? intval( $_POST['prflxtrflds_show_default'] ) : 0;
			$field_show_always = isset( $_POST['prflxtrflds_show_always'] ) ? intval( $_POST['prflxtrflds_show_always'] ) : 0;
			$ratio 			= isset( $_POST['prflxtrflds_ratio'] ) ? array_map( 'stripslashes_deep', $_POST['prflxtrflds_ratio'] ) : ''; /* is array */
			if ( isset( $_POST['prflxtrflds_field_id'] ) ) {
				$field_id = intval( $_POST['prflxtrflds_field_id'] );
			} else {
				$field_id           = NULL;
				$prflxtrflds_error .= sprintf( '<p><strong>%s</strong></p>', __( 'Unknown field ID', 'profile_extra_fields' ) );
			}

			if ( isset( $_POST['prflxtrflds_available_values'] ) && is_array( $_POST['prflxtrflds_available_values'] ) ) {
				$nonsort_available_values = array_map( 'stripslashes_deep', $_POST['prflxtrflds_available_values'] );
				$available_values = array();
				$i = 0;
				foreach ( $nonsort_available_values as $value ) {
					$available_values[ $i ]['value_name'] = $value;
					if ( isset( $ratio[ $i ] ) ) {
						$available_values[ $i ]['value_id'] = $ratio[ $i ];
					} else {
						$available_values[ $i ]['value_id'] = '';
					}
					$i++;
				}
			} else {
				$available_values = '';
			}
			/* Name of page if error */
			$name_of_page = __( 'Edit field', 'profile_extra_fields' );
		}	elseif ( ! is_null( $field_id ) ) {
			/* Name of page if field exist */
			$name_of_page = __( 'Edit field', 'profile_extra_fields' );
			/* If get $field_id - edit field */
			$table_name = $wpdb->base_prefix . "prflxtrflds_fields_id";
			$sql = "SELECT `field_name`, `required`, `description`, `show_default`, `show_always` FROM " . $table_name . " WHERE `field_id`=%d";
			if ( ! $field_options = $wpdb->get_row( $wpdb->prepare( $sql, $field_id ), ARRAY_A ) ) {
				/* If entry not exist - create new entry */
				$field_id 		    = NULL;
				$field_required     = 0;
				$field_show_default = 0;
				$field_show_always  = 0;
				$field_name         = '';
			} else {
				$field_name 	    = isset( $field_options['field_name'] ) ? $field_options['field_name'] : '';
				$field_required     = isset( $field_options['required'] ) ? $field_options['required'] : 0;
				$description 	    = isset( $field_options['description'] ) ? $field_options['description'] : '';
				$field_show_default = isset( $field_options['show_default'] ) ? $field_options['show_default'] : '';
				$field_show_always  = isset( $field_options['show_always'] ) ? $field_options['show_always'] : '';
			}
			/* Get field type */
			$table_name = $wpdb->base_prefix . "prflxtrflds_field_types";
			$sql 		= "SELECT `field_type_id` FROM " . $table_name . " WHERE `field_id`=%d";
			/* Get field type id */
			$field_type_id = $wpdb->get_var( $wpdb->prepare( $sql, $field_id ) );
			/* Get avaliable roles */
			$table_name 	= $wpdb->base_prefix . "prflxtrflds_roles_and_fields";
			$sql 			= "SELECT `role_id` FROM " . $table_name . " WHERE `field_id`=%d";
			$checked_roles  = $wpdb->get_col( $wpdb->prepare( $sql, $field_id ) );
			$field_order 	= $wpdb->get_var( $wpdb->prepare( "SELECT `field_order` FROM " . $table_name . " WHERE `field_id`=%d LIMIT 1", $field_id ) );
			/* Get available values to checkbox, radiobutton, select, etc */
			$table_name 		= $wpdb->base_prefix . "prflxtrflds_field_values";
			$sql 				= "SELECT `value_id`, `value_name` FROM " . $table_name . " WHERE `field_id`=%d";
			$available_values   = $wpdb->get_results( $wpdb->prepare( $sql, $field_id ), ARRAY_A );
		} else {
			/* if no error and id is null */
			$name_of_page = __( 'Add new field', 'profile_extra_fields' );
			$description  = '';
			/* If field id is NULL - create new entry */
			$table_name = $wpdb->base_prefix . "prflxtrflds_fields_id";
			$sql 		= "SELECT MAX(`field_id`) FROM " . $table_name;
			if ( ! $field_id = $wpdb->get_var( $sql ) ) {
				/* If table is empty */
				$field_id = 1;
			} else {
				/* Generate new id */
				$field_id++;
			}
			$field_name 	= '';
			$field_type_id  = '';
		}
		if ( empty( $field_order ) )
			$field_order = '0';
		/* $fields_count use in fender available fields */
		if ( isset( $available_values ) && is_array( $available_values ) ) {
			$fields_count = sizeof( $available_values ) ;
		} else {
			$fields_count = 1;
		}
		/* Update roles id */
		prflxtrflds_update_roles_id();
		/* Get all avaliable roles */
		$table_name = $wpdb->base_prefix . "prflxtrflds_roles_id";
		$sql 		= "SELECT `role_id`, `role` FROM " . $table_name;
		/* Use in render available roles for field */
		$all_roles = $wpdb->get_results( $sql ); ?>
		<div class="wrap">
			<h2> <?php echo $name_of_page; ?> </h2>
			<div id="prflxtrflds-settings-notice" class="updated fade hidden below-h2">
				<p>
					<strong><?php _e( 'Notice:', 'profile_extra_fields' ); ?></strong>
					<?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'profile_extra_fields' ); ?>
				</p>
			</div><!--#prflxtrflds-settings-notice-->
			<?php if( ! empty( $prflxtrflds_error ) ) { ?>
				<div class="error below-h2">
					<?php echo $prflxtrflds_error; /* Show error */ ?>
				</div><!--.error-->
			<?php } elseif( isset( $_POST['prflxtrflds_updated'] ) && ( true == $_POST['prflxtrflds_updated'] ) ) { ?>
				<div class="updated fade below-h2 prflxtrflds-settings-saved">
					<p>
						<?php if ( isset( $prflxtrflds_is_old_field ) && ( true == $prflxtrflds_is_old_field ) ) {
							_e( 'Field updated', 'profile_extra_fields' );
						} else {
							_e( 'Field created', 'profile_extra_fields' );
						} ?>
					</p>
				</div>
			<?php }
			$nonce = wp_create_nonce( 'prflxtrflds_nonce_name' ); ?>
			<form method="post" action="<?php get_admin_url(); ?>?page=profile-extra-fields.php&amp;edit=1&amp;prflxtrflds_field_id=<?php echo $field_id; ?>&amp;_wpnonce=<?php echo $nonce; ?>">
				<table class="form-table">
					<tbody>
						<tr>
							<th>
								<label for="prflxtrflds-field-name"><?php _e( 'Name', 'profile_extra_fields' ); ?></label>
							</th>
							<td>
								<input type="text" id="prflxtrflds-field-name" name="prflxtrflds_field_name" value="<?php echo sanitize_text_field( $field_name ); ?>" />
							</td>
						</tr>
						<tr>
							<th>
								<label for="prflxtrflds-select-type"><?php _e( 'Type', 'profile_extra_fields' ); ?></label>
							</th>
							<td>
								<select id="prflxtrflds-select-type" name="prflxtrflds_type">
									<?php foreach ( $prflxtrflds_field_type_id as $id => $field_name ) { /* Create select with field types */ ?>
										<option value="<?php echo $id; ?>"<?php selected( $field_type_id, $id ); ?>><?php echo $field_name; ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr class="prflxtrflds-fields-container">
							<th>
								<?php _e( 'Available values', 'profile_extra_fields' ); ?>
							</th>
							<td>
								<div class="prflxtrflds-values-info">
									<div class="prflxtrflds-value-name">
										<?php _e( 'Name of value', 'profile_extra_fields' ); ?>
									</div>
									<div class="prflxtrflds-delete">
										<?php _e( 'Delete', 'profile_extra_fields' ); ?>
									</div>
								</div><!--.prflxtrflds-values-info-->
								<div class="prflxtrflds-drag-values-container">
									<?php for ( $i = 0; $i <= $fields_count; $i++ ) { /* Create few additional fields. Create more via JS  */ ?>
										<div class="prflxtrflds-drag-values">
											<input type="hidden" class="hidden" <?php if ( ! empty( $available_values[ $i ]['value_id'] ) ) { echo "name='prflxtrflds_ratio[" . $i . "]'"; } ?> value="<?php if ( ! empty( $available_values[ $i ]['value_id'] ) ) { echo $available_values[ $i ]['value_id']; } ?>">
											<img class="prflxtrflds-drag-field hide-if-no-js prflxtrflds-hide-if-is-mobile" title="" src="<?php echo plugins_url( 'images/dragging-arrow.png', __FILE__ ); ?>" alt="drag-arrow"/>
											<input class="prflxtrflds-add-options-input" type="text" name="prflxtrflds_available_values[]" value="<?php if ( isset( $available_values[ $i ] ) && is_array( $available_values[ $i ] ) ) { echo sanitize_text_field( $available_values[ $i ]['value_name'] ); } ?>" />
											<span class="prflxtrflds-value-delete">
												<input type="checkbox" name="prflxtrflds-value-delete[]" value="<?php if ( ! empty( $available_values[ $i ]['value_id'] ) ) { echo $available_values[ $i ]['value_id']; } ?>" /><label></label>
											</span>
										</div><!--.prflxtrflds-drag-values-->
									<?php } ?>
								</div><!--.prflxtrflds-drag-values-container-->
								<div class="prflxtrflds-add-button-container">
									<input type="button" class="button-small button prflxtrflds-small-button hide-if-no-js" id="prflxtrflds-add-field" name="prflxtrflds-add-field" value="<?php _e( 'Add', 'profile_extra_fields' ); ?>" />
									<p class="hide-if-js"><?php _e( 'To add more values, click save button', 'profile_extra_fields' ); ?></p>
								</div>
							</td>
						</tr>
						<tr>
							<th>
								<label for="prflxtrflds-description"><?php _e( 'Description', 'profile_extra_fields' ); ?></label>
							</th>
							<td>
								<textarea id="prflxtrflds-description" class="prflxtrflds-description" name="prflxtrflds-description" ><?php echo esc_textarea( $description ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th>
								<?php _e( 'Available for roles', 'profile_extra_fields' ); ?>
							</th>
							<td>
								<div id="prflxtrflds-select-roles">
									<?php if( $all_roles ){ ?>
										<div id="prflxtrflds-div-select-all">
											<input type="checkbox" name="prflxtrflds-select-all" id="prflxtrflds-select-all" />
											<label for="prflxtrflds-select-all"><b><?php _e( 'Select all', 'profile_extra_fields' ); ?></b></label>
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
							</td>
						</tr>
						<tr>
							<th>
								<?php _e( 'Required', 'profile_extra_fields' ); ?>
							</th>
							<td>
								<input type="checkbox" id="prflxtrflds-required" name="prflxtrflds_required" value="1" <?php if ( isset( $field_required ) ) { checked( $field_required, "1" ); } ?> />
								<label for="prflxtrflds-required"><?php _e( 'Mark this field as required', 'profile_extra_fields' ); ?></label>
							</td>
						</tr>
						<tr>
							<th>
								<?php _e( 'Show by default in User data', 'profile_extra_fields' ); ?>
							</th>
							<td>
								<input type="checkbox" id="prflxtrflds-show-default" name="prflxtrflds_show_default" value="1" <?php if ( isset( $field_show_default ) ) { checked( $field_show_default, "1" ); } ?> />
								<label for="prflxtrflds-show-default"><?php _e( 'Show this field by default in User data. You can change it using screen options', 'profile_extra_fields' ); ?></label>
							</td>
						</tr>
						<tr>
							<th>
								<?php _e( 'Show always in User data', 'profile_extra_fields' ); ?>
							</th>
							<td>
								<input type="checkbox" id="prflxtrflds-show-always" name="prflxtrflds_show_always" value="1" <?php if ( isset( $field_show_always ) ) { checked( $field_show_always, "1" ); } ?> />
								<label for="prflxtrflds-show-always"><?php _e( 'Show this field in User data on any display. You can change it using screen options', 'profile_extra_fields' ); ?></label>
							</td>
						</tr>
						<tr>
							<th>
								<label for="prflxtrflds-order"><?php _e( 'Field order', 'profile_extra_fields' ); ?></label>
							</th>
							<td>
								<input type="number" id="prflxtrflds-order" min="0" max="999" name="prflxtrflds_order" value="<?php if ( isset( $field_order ) ) { echo $field_order; } else { echo '0'; } ?>" />
							</td>
						</tr>
					</tbody>
				</table>
				<input type="hidden" name="prflxtrflds_save_field" value="true" />
				<input type="hidden" name="prflxtrflds_updated" value="true" />
				<input type="hidden" name="prflxtrflds_field_id" value="<?php echo $field_id; ?>" />
				<p class="submit">
					<input type="submit" class="button-primary" name="prflxtrflds_save_settings" value="<?php _e( 'Save settings', 'profile_extra_fields' ); ?>" />
				</p>
			</form>
		</div><!--.wrap-->
	<?php }
}

/* Screen option. Settings for display where items per page show in wp list table */
if ( ! function_exists( 'prflxtrflds_screen_options' ) ) {
	function prflxtrflds_screen_options() {

		$option = 'per_page';
		$args = array(
			'label'   => __( 'Fields per page', 'profile_extra_fields' ),
			'default' => 20,
			'option'  => 'fields_per_page',
		);
		add_screen_option( $option, $args );

		if ( ( isset( $_GET['tab-action'] ) ) && ( 'userdata' == $_GET['tab-action'] ) ) {
			global $prflxtrflds_userdatalist_table;
			if ( ! isset( $prflxtrflds_userdatalist_table ) )
				$prflxtrflds_userdatalist_table = new Srrlxtrflds_Userdata_List;
		} elseif ( ( isset( $_GET['tab-action'] ) ) && ( 'shortcode' == $_GET['tab-action'] ) ) {
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

/* Set screen options */
if ( ! function_exists( 'prflxtrflds_table_set_option' ) ) {
	function prflxtrflds_table_set_option( $status, $option, $value ) {
		return $value;
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
					'field_name'	=> __( 'Name', 'profile_extra_fields' ),
					'description'	=> __( 'Description', 'profile_extra_fields' ),
					'field_type'	=> __( 'Type', 'profile_extra_fields' ),
					'required'		=> __( 'Required', 'profile_extra_fields' ),
					'show_default'	=> __( 'Show default', 'profile_extra_fields' ),
					'show_always'	=> __( 'Show always', 'profile_extra_fields' ),
					'roles'			=> __( 'Roles', 'profile_extra_fields' ),
					'field_order'	=> __( 'Field order', 'profile_extra_fields' ),
				);
				/* If is first user login, add to hidden */
				if ( false == get_user_option( 'manage' . $this->screen->id . 'columnshidden' ) ) {
					$hidden_columns = array(
						'required',
						'roles',
						'field_order',
						'show_default',
						'show_always',
					);
					update_user_option( get_current_user_id() , 'manage' . $this->screen->id . 'columnshidden', $hidden_columns, true );
				}
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
					'delete_fields'	=> __( 'Delete Permanently', 'profile_extra_fields' ),
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
							$action = 'prflxtrflds_nonce_name';
							if ( wp_verify_nonce( $nonce, $action ) ) {
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
				if ( ! empty( $_GET['role_id'] ) ) {
					$current = $_GET['role_id'];
				} else {
					$current = 'all';
				}

				/* All link */
				$all_url = htmlspecialchars( add_query_arg( 'role_id', 'all' ) );
				if ( 'all' == $current ) {
					$class = 'class="current"';
				} else {
					$class = '';
				}
				$views['all'] = "<a href='" . $all_url . "' " . $class . " >" . __( 'All', 'profile_extra_fields' ) . "</a>";

				/* Get actual users data */
				$table_roles_id = $wpdb->base_prefix . 'prflxtrflds_roles_id';
				$sql 	= "SELECT * FROM " . $table_roles_id;
				$roles  = $wpdb->get_results( $sql );
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
					$table_roles_id = $wpdb->base_prefix . 'prflxtrflds_roles_id';
					$sql 	 = "SELECT * FROM " . $table_roles_id;
					$roles = $wpdb->get_results( $sql ); ?>
					<div class="alignleft prflxtrflds-filter actions bulkactions">
						<label for="prflxtrflds-role-id">
							<select name="prflxtrflds_role_id" id="prflxtrflds-role-id">
								<option value="all" <?php selected( $current, "all" ); ?>><?php _e( 'All roles', 'profile_extra_fields' ); ?></option>
								<?php if ( ! empty( $roles ) ) {
									/* Create select with field types */
									foreach ( $roles as $role ) { ?>
										<option value="<?php echo $role->role_id; ?>"<?php selected( $current, $role->role_id ); ?>><?php echo translate_user_role( $role->role ); ?></option>
									<?php }
								} ?>
							</select>
						</label>
						<input type="submit" class="button" name="prflxtrflds_apply_filter" value="<?php _e( 'Filter', 'profile_extra_fields' ); ?>" />
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
					'edit_fields'	=> '<span><a href="' . wp_nonce_url( sprintf( '?page=profile-extra-fields.php&amp;edit=1&amp;prflxtrflds_field_id=%s', $item['field_id'] ), 'prflxtrflds_nonce_name' ) . '">' . __( 'Edit field', 'profile_extra_fields' ) . '</a></span>',
					'delete_fields'	=> '<span class="trash"><a href="' . wp_nonce_url( sprintf( '?page=profile-extra-fields.php&amp;remove=1&amp;prflxtrflds_field_id=%s', $item['field_id'] ), 'prflxtrflds_nonce_name' ) . '">' . __( 'Delete Permanently', 'profile_extra_fields' ) . '</a></span>',
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
					1 => __( 'Yes', 'profile_extra_fields' ),
					0 => __( 'No', 'profile_extra_fields' ),
				);
				return sprintf(
					'%s', $is_required[ $item['required'] ]
				);
			}
			function column_show_default( $item ) {
				$is_default = array(
					1 => __( 'Yes', 'profile_extra_fields' ),
					0 => __( 'No', 'profile_extra_fields' ),
				);
				return sprintf(
					'%s', $is_default[ $item['show_default'] ]
				);
			}
			function column_show_always( $item ) {
				$is_always = array(
					1 => __( 'Yes', 'profile_extra_fields' ),
					0 => __( 'No', 'profile_extra_fields' ),
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
				$table_field_types 		= $wpdb->base_prefix . 'prflxtrflds_field_types';
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
						$table_field_types . ".`field_type_id` " .
						" FROM " . $table_fields_id .
						" LEFT JOIN " .  $table_roles_and_fields .
						" ON " . $table_roles_and_fields . ".`field_id`=" . $table_fields_id . ".`field_id`" .
						" LEFT JOIN " .  $table_field_types .
						" ON " . $table_field_types . ".`field_id`=" . $table_roles_and_fields . ".`field_id`" .
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

				if ( empty( $this->modes ) ) {
					$this->modes = array(
						'list'    => __( 'List View' ),
						'excerpt' => __( 'Excerpt View' )
					);
				}
			}

			function get_columns() {
				global $wpdb;
				/* Setup column */
				$columns = array(
					'user_id'     => __( 'User ID', 'profile_extra_fields' ),
					'name'		  => __( 'Username', 'profile_extra_fields' ),
					'role'		  => __( 'User role', 'profile_extra_fields' ),
					'disp_name'   => __( 'Name', 'profile_extra_fields' ),
					'email'       => __( 'Email', 'profile_extra_fields' ),
					'posts'       => __( 'Posts', 'profile_extra_fields' ),
				);
				$table_fields_id = $wpdb->base_prefix . 'prflxtrflds_fields_id';
				/* Get all fields from database and set as column */
				$all_fields_array =  $wpdb->get_results( "SELECT `field_id`, `field_name` FROM " . $table_fields_id, ARRAY_A );
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
					$show_default = $wpdb->get_col( "SELECT `field_id` FROM " . $table_fields_id . " WHERE `show_default`='1'" );
					if ( isset( $new_columns ) && is_array( $new_columns ) ) {
						foreach( $new_columns as $key=>$column ) {
							if ( in_array( $key, $show_default ) )
								continue;
							/* Add new fields to hidden, if no set option show_default */
							$hidden_columns[] = $key;
						}
					}
					$show_always = $wpdb->get_col( "SELECT `field_id` FROM " . $table_fields_id . " WHERE `show_always`='1'" );
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
					$not_show_default = $wpdb->get_col( "SELECT `field_id` FROM " . $table_fields_id . " WHERE `show_default`='0'" );
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
					'edit_user'	=> '<span><a href="' . sprintf( '%suser-edit.php?user_id=%s&amp;wp_http_referer=%s', get_admin_url(), $item['user_id'], urlencode( '/wp-admin/admin.php?page=profile-extra-fields.php' ) ) . '">' . __( 'Edit user', 'profile_extra_fields' ) . '</a></span>',
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
					'email'	    => array( 'email', true ),
				);
				return $sortable_columns;
			}

			function extra_tablenav( $which ) {
				/* Extra tablenav. Create filter. */
				if ( ! empty( $_GET['prflxtrflds_role'] ) ) {
					$current_role = $_GET['prflxtrflds_role'];
				} else {
					$current_role = 'all';
				}
				if ( "top" == $which ) {
					$roles = get_editable_roles(); ?>
					<div class="alignleft prflxtrflds-filter actions bulkactions">
						<label for="prflxtrflds-role">
							<select id="prflxtrflds-role" name="prflxtrflds_role">
								<option value="all" <?php selected( $current_role, "all" ); ?>><?php _e( 'All roles', 'profile_extra_fields' ); ?></option>
								<?php if ( isset( $roles ) )foreach ( $roles as $role ) { /* Create select with field types */ ?>
									<option value="<?php echo $role['name']; ?>"<?php selected( $current_role, $role['name'] ); ?>><?php echo translate_user_role( $role['name'] ); ?></option>
								<?php } ?>
							</select>
						</label>
						<input type="submit" class="button" name="prflxtrflds_apply_filter" value="<?php _e( 'Filter', 'profile_extra_fields' ); ?>" />
					</div><!--.alignleft prflxtrflds-filter-->
				<?php
				}
			}

			function prepare_items() {
				global $wpdb, $role;
				$userdata = array();
				$i = 0;
				$search = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

				if ( ( isset( $_REQUEST['prflxtrflds_role'] ) && ( '' != $_GET['prflxtrflds_role'] ) && ( 'all' != $_GET['prflxtrflds_role'] ) ) ) {
					$role = $_GET['prflxtrflds_role'];
				} else {
					$role = '';
				}

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
				/* Get fields for current user */
				$get_user_data_sql = "SELECT " . $table_field_values . ".`field_id`, " .
					$table_field_values . ".`value_name`," .
					$table_user_field_data . ".`user_value`
				FROM " . $table_field_values .
					" LEFT JOIN " . $table_user_field_data .
					" ON " . $table_user_field_data . ".`value_id`=" . $table_field_values . ".`value_id`
					WHERE " . $table_user_field_data . ".`user_id`=%d"
				;

				foreach ( $all_users as $user ) {
					$userdata[ $i ]['name'] = $user->user_nicename;
					$userdata[ $i ]['role'] = implode( ', ', $user->roles );
					$userdata[ $i ]['user_id'] = $user->ID;
					$userdata[ $i ]['disp_name'] = $user->first_name . ' ' . $user->last_name;
					$userdata[ $i ]['email'] = $user->user_email;
					$userdata[ $i ]['posts'] = $post_counts[ $user->ID ];
					$filled_fields =  $wpdb->get_results( $wpdb->prepare( $get_user_data_sql, $user->ID ), ARRAY_A );
					if ( isset( $filled_fields ) && is_array( $filled_fields ) ) {
						foreach ( $filled_fields as $field ) {
							if ( 'prflxtrflds_textfield' == $field['value_name'] ) {
								/* For textfield */
								$userdata[ $i ][ $field['field_id'] ] = $field['user_value'];
							} else {
								if ( isset( $userdata[ $i ][ $field['field_id'] ] ) ) {
									/* Add value name */
									$userdata[ $i ][ $field['field_id'] ] .= ", " .  $field['value_name'];
								} else {
									/* First write value name */
									$userdata[ $i ][ $field['field_id'] ] = $field['value_name'];
								}
							}
						}
						/* Clear for next user */
						unset( $filled_fields );
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
					foreach( $userdata as $key=>$oneuserdata ) {
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

				if ( empty( $this->modes ) ) {
					$this->modes = array(
						'list'    => __( 'List View' ),
						'excerpt' => __( 'Excerpt View' )
					);
				}
			}

			function get_columns() {
				/* Setup column */
				$columns = array(
					'field_name'		=> __( 'Field name', 'profile_extra_fields' ),
					'description'		=> __( 'Description', 'profile_extra_fields' ),
					'show'				=> __( 'Show this field', 'profile_extra_fields' ),
					'selected'			=> __( 'Show only if next value is selected', 'profile_extra_fields' ),
				);
				return $columns;
			}

			function column_show( $item ) {
				global $prflxtrflds_options;
				/* get option for available fields */
				if ( ! isset( $prflxtrflds_options ) ) {
					$prflxtrflds_options = get_option( 'prflxtrflds_options' );
				}
				isset( $prflxtrflds_options['available_fields'] ) ? $option_available_fields = $prflxtrflds_options['available_fields'] : $option_available_fields = '';
				if ( is_array( $option_available_fields ) ) {
					$prflxtrflds_checked = checked( in_array( $item['field_id'], $option_available_fields ), 1, false );
				} else {
					$prflxtrflds_checked = '';
				}
				return sprintf( '<input type="checkbox" class="prflxtrflds-available-fields" name="prflxtrflds_options[available_fields][]" value="%d" %s />', $item['field_id'], $prflxtrflds_checked );
			}

			function column_selected( $item ) {
				global $prflxtrflds_options;
				if ( ! isset( $prflxtrflds_options ) ) {
					$prflxtrflds_options = get_option( 'prflxtrflds_options' );
				}
				/* Get available values */
				isset( $prflxtrflds_options['available_values'] ) ? $option_available_values = $prflxtrflds_options['available_values'] : $option_available_values = '';
				/* If field have more 1 values, print select */
				if ( is_array( $item['available_values'] ) && sizeof( $item['available_values'] ) > 0 ) {
					$prflxtrflds_option_list = '';
					foreach ( $item['available_values'] as $value ) {
						if ( is_array( $option_available_values ) ) {
							$value_selected = selected( in_array( $value['value_id'], $option_available_values ), 1, false );
						} else {
							$value_selected = '';
						}
						$prflxtrflds_option_list .= "<option value='" . $value['value_id'] . "' " . $value_selected . ">" . $value['value_name'] . "</option>";

					}
					return sprintf( '<select class="prflxtrflds-wplist-select" name="prflxtrflds_options[available_values][]">
					<option value="-1">%s</option>
					%s
					</select>', __( 'Show if any values', 'profile_extra_fields' ), $prflxtrflds_option_list );
				} else {
					return '';
				}
			}

			/* Override this function to set nonce from options */
			function display_tablenav( $which ) {
				if ( 'top' == $which )
					wp_nonce_field( 'update-options' ); ?>
				<div class="tablenav <?php echo esc_attr( $which ); ?>">
					<div class="alignleft actions bulkactions">
						<?php $this->bulk_actions( $which ); ?>
					</div>
					<?php $this->extra_tablenav( $which );
					$this->pagination( $which ); ?>
					<br class="clear" />
				</div>
			<?php }

			function prepare_items() {
				global $wpdb;
				$table_fields_id 	= $wpdb->base_prefix . 'prflxtrflds_fields_id';
				$table_field_values	= $wpdb->base_prefix . 'prflxtrflds_field_values';
				/* General query */
				$get_fields_list_sql = "SELECT `field_name`, `field_id`, `description` FROM " . $table_fields_id;
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
				/* Result query */
				$get_fields_list_sql .= " LIMIT " . $offset . ", " . $perpage;
				/* Save current query */
				$this->current_query = $get_fields_list_sql;

				$available_fields = $wpdb->get_results( $get_fields_list_sql, ARRAY_A );
				if ( 0 < sizeof( $available_fields ) ) {
					/* Show available values where is not textfield */
					$field_values_sql = "SELECT `value_id`, `value_name` FROM " . $table_field_values . " WHERE `field_id`=%d AND `value_name`<>'prflxtrflds_textfield'";
					/* Add available values to array with available fields */
					foreach ( $available_fields as &$field ) {
						$field['available_values'] = $wpdb->get_results( $wpdb->prepare( $field_values_sql, $field['field_id'] ), ARRAY_A );
					}
					unset( $field );
				}

				$this->_column_headers = $this->get_column_info();
				$this->items 			= $available_fields;;
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
			$wpdb->base_prefix . "prflxtrflds_field_types",
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
		/* Get value ids */
		$get_ids_sql = "SELECT `value_id` FROM " . $wpdb->base_prefix . "prflxtrflds_field_values WHERE `field_id`=%d";
		$need_to_del_ids = $wpdb->get_col( $wpdb->prepare( $get_ids_sql, $field_id ) );
		$wpdb->delete(
			$wpdb->base_prefix . "prflxtrflds_field_values",
			array(
				'field_id' => $field_id,
			)
		);
		if ( is_array( $need_to_del_ids ) && 0 < sizeof( $need_to_del_ids ) ) {
			$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "prflxtrflds_user_field_data WHERE `value_id` IN (" . implode( ', ', $need_to_del_ids ) . ")" );
		}
	}
}

/* save field settings */
if ( ! function_exists( 'prflxtrflds_save_field' ) ) {
	function prflxtrflds_save_field() {
		global $wpdb;
		/* First check for error */
		$prflxtrflds_error = '';
		/* false for new field, true for old field */
		$prflxtrflds_is_old_field = false;
		/* If isset $_POST - user data send */
		if ( ( ! isset( $_POST['prflxtrflds_field_name'] ) ) || ( empty( $_POST['prflxtrflds_field_name'] ) ) ) {
			/* If field name not filled */
			$prflxtrflds_error .= sprintf( '<p><strong>%s</strong></p>', __( 'Field name is empty', 'profile_extra_fields' ) );
		}
		/* $_POST['prflxtrflds_roles'] must have one role with id=0 for default sort settings */
		if ( ( ! isset( $_POST['prflxtrflds_roles'] ) ) || ( empty( $_POST['prflxtrflds_roles'] ) ) || 1 == sizeof( $_POST['prflxtrflds_roles'] ) ) {
			/* If roles not selected */
			$prflxtrflds_error .= sprintf( '<p><strong>%s</strong></p>', __( 'Select at least one role', 'profile_extra_fields' ) );
		}
		if ( ( isset( $_POST['prflxtrflds_type'] ) ) && ( ( '1' !=  $_POST['prflxtrflds_type'] ) && ( ! empty( $_POST['prflxtrflds_available_values'] ) ) ) ) {
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
					$prflxtrflds_error .= sprintf( '<p><strong>%s</strong></p>', __( 'Select at least one available value', 'profile_extra_fields' ) );
				} elseif ( ( 2 > $filled ) && ( isset( $_POST['prflxtrflds_type'] ) ) && ( ( 3 == $_POST['prflxtrflds_type'] ) || ( 4 == $_POST['prflxtrflds_type'] ) ) ) {
					/* If is radiobutton or select, select more if two available values */
					$prflxtrflds_error .= sprintf( '<p><strong>%s</strong></p>', __( 'Select at least two available values', 'profile_extra_fields' ) );
				}
			} else {
				$prflxtrflds_error .= sprintf( '<p><strong>%s</strong></p>', __( 'Select at least one available value', 'profile_extra_fields' ) );
			}
		}/* End check error */
		if ( empty( $prflxtrflds_error ) ) {
			$sql_field = array();
			if ( isset( $_POST['prflxtrflds_field_name'] ) ) {
				$sql_field['name'] = stripslashes( esc_html( sanitize_text_field( $_POST['prflxtrflds_field_name'] ) ) );
			} else {
				$sql_field['name'] = '';
			}
			if ( isset( $_POST['prflxtrflds_field_id'] ) ) {
				$sql_field['field_id'] = intval( filter_input( INPUT_POST, 'prflxtrflds_field_id', FILTER_SANITIZE_STRING ) );
			}
			if ( isset( $_POST['prflxtrflds_type'] ) ) {
				$sql_field['field_type'] = intval( filter_input( INPUT_POST, 'prflxtrflds_type', FILTER_SANITIZE_STRING ) );
			} else {
				$sql_field['field_type'] = 1;
			}
			if ( isset( $_POST['prflxtrflds-description'] ) ) {
				$sql_field['description'] = stripslashes( esc_html( sanitize_text_field( $_POST['prflxtrflds-description'] ) ) );
			} else {
				$sql_field['description'] = '';
			}
			if ( isset( $_POST['prflxtrflds_order'] ) ) {
				$sql_field['order'] = intval( filter_input( INPUT_POST, 'prflxtrflds_order', FILTER_SANITIZE_STRING ) );
			} else {
				$sql_field['order'] = 0;
			}
			if ( isset( $_POST['prflxtrflds-value-delete'] ) && is_array( $_POST['prflxtrflds-value-delete'] ) ) {
				$fields_to_delete = array_map( 'stripslashes_deep', $_POST['prflxtrflds-value-delete'] );
			} else {
				$fields_to_delete[0] = '';
			}
			if ( isset( $_POST['prflxtrflds_available_values'] ) && is_array( $_POST['prflxtrflds_available_values'] ) ) {
				$available_values = array_map( 'stripslashes_deep', $_POST['prflxtrflds_available_values'] );
			} else {
				/* If avaliable values is empty, create one empty value */
				$available_values[0] = '';
			}
			if ( isset( $_POST['prflxtrflds_roles'] ) && is_array( $_POST['prflxtrflds_roles'] ) ) {
				$avaliable_roles = array_map( 'stripslashes_deep', $_POST['prflxtrflds_roles'] );
			}
			if ( isset( $_POST['prflxtrflds_ratio'] ) && is_array( $_POST['prflxtrflds_ratio'] ) ) {
				$ratio = array_map( 'stripslashes_deep', $_POST['prflxtrflds_ratio'] );
			}
			if ( ( isset( $_POST['prflxtrflds_required'] ) ) && ( "1" == $_POST['prflxtrflds_required'] ) ) {
				$required = 1;
			} else {
				$required = 0;
			}
			if ( ( isset( $_POST['prflxtrflds_show_default'] ) ) && ( "1" == $_POST['prflxtrflds_show_default'] ) ) {
				$show_default = 1;
			} else {
				$show_default = 0;
			}
			if ( ( isset( $_POST['prflxtrflds_show_always'] ) ) && ( "1" == $_POST['prflxtrflds_show_always'] ) ) {
				$show_always = 1;
			} else {
				$show_always = 0;
			}

			/* prflxtrflds_fields_id update */
			if ( $sql_field['field_id'] ) {
				/* Check for exist field id */
				if ( 1 == $wpdb->query( $wpdb->prepare( "SELECT `field_id` FROM " . $wpdb->base_prefix . "prflxtrflds_fields_id WHERE `field_id`=%d", $sql_field['field_id'] ) ) ) {
					$prflxtrflds_is_old_field = true;
				}
				/* Update data */
				$wpdb->replace(
					$wpdb->base_prefix . "prflxtrflds_fields_id",
					array(
						'field_id' 	 	 => $sql_field['field_id'],
						'field_name' 	 => $sql_field['name'],
						'required'	 	 => $required,
						'description'	 => $sql_field['description'],
						'show_default'   => $show_default,
						'show_always'    => $show_always,
					)
				);
			}
			/* prflxtrflds_field_types update */
			if ( $sql_field['field_id'] ) {
				/* Update data */
				$wpdb->replace(
					$wpdb->base_prefix . "prflxtrflds_field_types",
					array(
						'field_id' 		=> $sql_field['field_id'],
						'field_type_id' => $sql_field['field_type'],
					)
				);
			}
			/* prflxtrflds_roles_and_fields update */
			if ( $field_id = $sql_field['field_id'] ) {
				$table = $wpdb->base_prefix . "prflxtrflds_roles_and_fields";
				/* Get field order */
				if ( ! isset( $sql_field['order'] ) ) {
					$field_order_query = "SELECT `field_order` FROM " . $table . " WHERE `field_id`='" . $field_id . "'AND `role_id`='0' LIMIT 1";
					$field_order =  $wpdb->get_var( $field_order_query );
				} elseif ( is_numeric( $sql_field['order'] ) ) {
					$field_order = $sql_field['order'];
				} else {
					/* Order by default */
					$field_order = 0;
				}

				/* Get all available roles id */
				$get_current_roles_query = "SELECT `role_id` FROM " . $table . " WHERE `field_id`='" . $field_id . "'";
				$all_roles_in_db 		 = $wpdb->get_col( $get_current_roles_query );
				/* Delete role if need */
				if ( ! empty( $avaliable_roles ) ) {
					$roles_to_delete = array_diff( $all_roles_in_db, $avaliable_roles );
				} else {
					/* If no checked roles, delete ell roles in db */
					$roles_to_delete = $all_roles_in_db;
				}
				if ( ! empty( $roles_to_delete ) ) {
					foreach ( $roles_to_delete as $role_id ) {
						/* Delete unchecked role */
						$wpdb->delete(
							$table,
							array(
								'field_id'  => $field_id,
								'role_id'	=> $role_id,
							)
						);
					}
				}
				/* update data */
				if ( ! empty( $avaliable_roles ) ) {
					/* If field order change, apply it for all roles */
					$default_order = $wpdb->get_var( "SELECT `field_order` FROM " . $table . " WHERE `field_id`=" . $field_id . " AND `role_id`=0" );
					if ( $field_order != $default_order ) {
						foreach ( $avaliable_roles as $role_id ) {
							$wpdb->replace(
								$table,
								array(
									'field_id' 		=> $field_id,
									'role_id' 		=> $role_id,
									'field_order' 	=> $field_order,
								)
							);
						}
					} else {
						/* If field order not change, not apply it. Hold old data */
						foreach ( $avaliable_roles as $role_id ) {
							$old_order = $wpdb->get_var( $wpdb->prepare( "SELECT `field_order` FROM " . $table . " WHERE `field_id`=" . $field_id . " AND `role_id`=%s", $role_id ) );
							/* If old order not exists, set default order */
							if ( ( ! isset( $old_order ) ) && ( isset( $field_order ) ) ) {
								/* For new roles */
								$old_order = $field_order;
							} elseif( ( ! isset( $old_order ) ) && ( ! isset( $field_order ) ) ) {
								/* Default order if not exist */
								$old_order = 0;
							}
							$wpdb->replace(
								$table,
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
						$table,
						array(
							'field_id' 		=> $field_id,
							'role_id' 		=> 0,
							'field_order'	=> $field_order,
						)
					);
				}
			}

			/* prflxtrflds_field_values update */
			if ( ! empty( $available_values ) ) {
				$i = 0;
				foreach ( $available_values as $value ) {
					/* If entry with current id exists, update it */
					if ( ! empty( $ratio ) && ( isset( $ratio[ $i ] ) ) ) {
						if( ! empty( $value ) ) {
							/* Update entry if not empty field (rename entry) */
							$wpdb->update(
								$wpdb->base_prefix . "prflxtrflds_field_values",
								array(
									'value_name' 	=> $value,
									'value_id' 		=> $ratio[ $i ],
								),
								array(
									'value_id' => $ratio[ $i ],
								),
								array( '%s', '%d' ),
								array( '%d' )
							);
							$i++;
						} else {
							/* If field empty - delete entry */
							$wpdb->delete(
								$wpdb->base_prefix . "prflxtrflds_field_values",
								array(
									'value_id' => $ratio[ $i ],
								)
							);
							$i++;
						}
					} else {
						/* If entry with current id not exist, create new entry */
						if ( ! empty( $value ) ) {
							/* Do not insert empty entry */
							$wpdb->replace(
								$wpdb->base_prefix . "prflxtrflds_field_values",
								array(
									'value_name'  => $value,
									'field_id'	  => $sql_field['field_id'],
								)
							);
						}
					}
				}
			}

			if ( 1 == $sql_field['field_type'] ) {
				/* Create empty entry to textfield if not exist. Textfield must have only one entry */
				$table 					= $wpdb->base_prefix . "prflxtrflds_field_values";
				$field_id 				= $sql_field['field_id'];
				$check_exist_entry_sql  = "SELECT `value_id` FROM " . $table . " WHERE `field_id`='" . $field_id . "' AND `value_name`='prflxtrflds_textfield' LIMIT 2";
				if ( 1 != $wpdb->query( $check_exist_entry_sql ) ) {
					/* If field type is changed from other type to textfield */
					$wpdb->delete(
						$wpdb->base_prefix . "prflxtrflds_field_values",
						array(
							'field_id'	=> $sql_field['field_id'],
						)
					);
					$wpdb->insert(
						$wpdb->base_prefix . "prflxtrflds_field_values",
						array(
							'value_name'	=> 'prflxtrflds_textfield',
							'field_id'	  => $sql_field['field_id'],
						)
					);
				}
			}
			/* Delete fields if necessary */
			if ( ( ! empty( $fields_to_delete ) ) && ( is_array( $fields_to_delete ) ) ) {
				foreach ( $fields_to_delete as $deleting_field_id ) {
					/* remove field */
					$wpdb->delete(
						$wpdb->base_prefix . "prflxtrflds_field_values",
						array(
							'value_id' => $deleting_field_id,
						)
					);
					/* remove user data */
					$wpdb->delete(
						$wpdb->base_prefix . "prflxtrflds_user_field_data",
						array(
							'value_id' => $deleting_field_id,
						)
					);
				}
			}
		}
		return array( $prflxtrflds_error, $prflxtrflds_is_old_field );
	}
}

/* settings page */
if ( ! function_exists( 'prflxtrflds_settings_page' ) ) {
	function prflxtrflds_settings_page() {
		global $prflxtrflds_error, $prflxtrflds_plugin_info;
		/* Remove slug */
		if ( isset( $_GET['remove'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'prflxtrflds_nonce_name' ) ) {
			if ( isset( $_GET['prflxtrflds_field_id'] ) ) {
				$field_id = filter_input( INPUT_GET, 'prflxtrflds_field_id', FILTER_SANITIZE_STRING );
				prflxtrflds_remove_field( $field_id );
			}
		} ?>
		<div class="wrap">
			<h2>Profile Extra Fields</h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['tab-action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=profile-extra-fields.php"><?php _e( 'Extra fields', 'profile_extra_fields' ); ?></a>
				<a class="nav-tab <?php if ( isset( $_GET['tab-action'] ) && 'userdata' == $_GET['tab-action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=profile-extra-fields.php&amp;tab-action=userdata"><?php _e( 'User data', 'profile_extra_fields' ); ?></a>
				<a class="nav-tab <?php if ( isset( $_GET['tab-action'] ) && 'shortcode' == $_GET['tab-action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=profile-extra-fields.php&amp;tab-action=shortcode"><?php _e( 'Shortcode settings', 'profile_extra_fields' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/profile-extra-fields/" target="_blank"><?php _e( 'FAQ', 'profile_extra_fields' ); ?></a>
			</h2>
			<?php /* Edit field */
			if ( isset( $_GET['edit'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'prflxtrflds_nonce_name' ) ) {
				/* Get field id with post or get */
				$field_id = $_REQUEST['prflxtrflds_field_id'];
				/* if is save settings page, call save field function */
				if ( isset( $_POST['prflxtrflds_save_field'] ) && 'true' == $_POST['prflxtrflds_save_field'] ) {
					/* Check for error and show field page after save or error */
					list( $prflxtrflds_error, $prflxtrflds_is_old_field ) = prflxtrflds_save_field();
					prflxtrflds_edit_field( $field_id, $prflxtrflds_error, $prflxtrflds_is_old_field );
				} else {
					prflxtrflds_edit_field( $field_id, null, null );
				}
			}
			/* add new entry  */
			if ( isset( $_POST['prflxtrflds_new_entry'] ) && '1' == filter_input( INPUT_POST, 'prflxtrflds_new_entry', FILTER_SANITIZE_STRING ) ) {
				prflxtrflds_edit_field();
			}
			/* Main interface */
			if ( ( ! isset( $_POST['prflxtrflds_new_entry'] ) && ( ! isset( $_POST['update_entry'] ) ) && ( ! isset( $_GET['tab-action'] ) ) ) ) {
				if ( ! isset( $_GET['edit'] ) ) { ?>
					<form method="post" action="<?php get_admin_url(); ?>?page=profile-extra-fields.php">
						<input type="hidden" name="prflxtrflds_new_entry" value="1" />
						<p>
							<input type="submit" class="button action" name="prflxtrflds_add_new_field" value="<?php _e( 'Add new field', 'profile_extra_fields' ) ?>" />
						</p>
					</form>
					<div class="prflxtrflds-wplisttable-fullwidth-sort-container">
						<?php $prflxtrflds_fields_list_table = new Srrlxtrflds_Fields_List(); /* Wp list table to show all fields */
						$prflxtrflds_fields_list_table->prepare_items();
						if ( isset( $prflxtrflds_fields_list_table->items ) && ( 1 < sizeof( $prflxtrflds_fields_list_table->items ) ) ) { /* Show drag-n-drop message if items > 2 */?>
							<p class="hide-if-no-js prflxtrflds-hide-if-is-mobile">
								<?php _e( 'Drag each item into the order you prefer display fields on user page', 'profile_extra_fields' ); ?>
							</p>
						<?php } ?>
						<form class="prflxtrflds-wplisttable-searchform" method="get" action="<?php get_admin_url(); ?>?page=profile-extra-fields.php">
							<input type="hidden" name="page" value="profile-extra-fields.php" />
							<?php wp_nonce_field( 'prflxtrflds_nonce_name', 'prflxtrflds_nonce_name', false );
							$prflxtrflds_fields_list_table->search_box( 'search', 'search_id' ); ?>
							<?php $prflxtrflds_fields_list_table->display(); ?>
						</form>
					</div><!-- .prflxtrflds-wplisttable-container -->
				<?php }
			}
			if ( isset( $_GET['tab-action'] ) && 'userdata' == $_GET['tab-action'] ) {
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
			<?php }
			if ( isset($_GET['tab-action'] ) && 'shortcode' == $_GET['tab-action'] ) {
				global $wpdb, $prflxtrflds_options; ?>
				<div id="prflxtrflds-settings-notice" class="updated fade hidden">
					<p>
						<strong><?php _e( 'Notice:', 'profile_extra_fields' ); ?></strong>
						<?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'profile_extra_fields' ); ?>
					</p>
				</div><!--#prflxtrflds-settings-notice-->
				<?php if( isset( $_GET['settings-updated'] ) && true == $_GET['settings-updated'] ) { /* Show message if data saved */ ?>
					<div class="updated fade prflxtrflds-settings-saved">
						<p>
							<?php _e( 'Settings saved', 'profile_extra_fields' ); ?>
						</p>
					</div>
				<?php } ?>
				<p>
					<?php _e( 'Use shortcode', 'profile_extra_fields' ); ?> <span class="prflxtrflds-shortcode">[prflxtrflds_user_data]</span> <?php _e( 'to see user data in frontend.', 'profile_extra_fields' ); ?>
				</p>
				<p>
					<?php _e( 'You can specify a user ID, separated by commas without spaces.', 'profile_extra_fields' ); ?> <?php _e( 'Example', 'profile_extra_fields' ); ?>: <span class="prflxtrflds-shortcode">[prflxtrflds_user_data user_id=3,1]</span>
				</p>
				<p>
					<?php _e( 'You can specify a user role, separated by commas without spaces.', 'profile_extra_fields' ); ?> <?php _e( 'Example', 'profile_extra_fields' ); ?>: <span class="prflxtrflds-shortcode">[prflxtrflds_user_data user_role=administrator,contributor]</span>
				</p>
				<p>
					<?php _e( 'You can specify a header position manually (top, left or right).', 'profile_extra_fields' ); ?> <?php _e( 'Example', 'profile_extra_fields' ); ?>: <span class="prflxtrflds-shortcode">[prflxtrflds_user_data display=top]</span>
				</p>
				<p>
					<?php _e( 'You can select multiple options.', 'profile_extra_fields' ); ?>
				</p>
				<?php $table_fields_id  = $wpdb->base_prefix . 'prflxtrflds_fields_id';
				$table_field_values 	= $wpdb->base_prefix . 'prflxtrflds_field_values';
				/* Get all available fields and print it */
				$get_all_fields_sql = "SELECT * FROM " . $table_fields_id;
				$available_fields 	=  $wpdb->get_results( $get_all_fields_sql, ARRAY_A );
				/* Show available values where is not textfield */
				$field_values_sql = "SELECT `value_id`, `value_name` FROM " . $table_field_values . " WHERE `field_id`=%d AND `value_name`<>'prflxtrflds_textfield'";
				if ( 0 < sizeof( $available_fields ) ) {
				/* Add available values to array with available fields */
					foreach ( $available_fields as &$field ) {
						$field['available_values'] = $wpdb->get_results( $wpdb->prepare( $field_values_sql, $field['field_id'] ), ARRAY_A );
					}
					unset( $field );
					if ( ! isset( $prflxtrflds_options ) ) {
						/* Get options if variable not exists */
						$prflxtrflds_options = get_option( 'prflxtrflds_options' );
					}
					$option_sort_sequence 			= isset( $prflxtrflds_options['sort_sequence'] ) ? $prflxtrflds_options['sort_sequence'] : '';
					$option_empty_value 			= isset( $prflxtrflds_options['empty_value'] ) ? $prflxtrflds_options['empty_value'] : '';
					$option_not_available_message 	= isset( $prflxtrflds_options['not_available_message'] ) ? $prflxtrflds_options['not_available_message'] : '';
					$option_header_table 	        = isset( $prflxtrflds_options['header_table'] ) ? $prflxtrflds_options['header_table'] : '';
					/* Mark as checked if is 1 */
					$checked_show_empty_columns = isset( $prflxtrflds_options['show_empty_columns'] ) ? checked( $prflxtrflds_options['show_empty_columns'], '1', false ) : '';
					$checked_show_id = isset( $prflxtrflds_options['show_id'] ) ? checked( $prflxtrflds_options['show_id'], '1', false ) : ''; ?>
					<form method="post" action="options.php">
						<table class="form-table">
							<tbody>
								<tr>
									<th>
										<label for="prflxtrflds-options-empty-value"><?php _e( 'Message for empty field', 'profile_extra_fields' ); ?></label>
									</th>
									<td>
										<input type="text" id="prflxtrflds-options-empty-value" name="prflxtrflds_options[empty_value]" value="<?php echo sanitize_text_field( $option_empty_value ); ?>">
									</td>
								</tr>
								<tr>
									<th>
										<label for="prflxtrflds-options-not-available-message"><?php _e( 'Message for the field unavaliable for the user', 'profile_extra_fields' ); ?></label>
									</th>
									<td>
										<input type="text" id="prflxtrflds-options-not-available-message" name="prflxtrflds_options[not_available_message]" value="<?php echo sanitize_text_field( $option_not_available_message ); ?>">
									</td>
								</tr>
								<tr>
									<th>
										<label for="prflxtrflds-options-sort-sequence"><?php _e( 'Sort by user name', 'profile_extra_fields' ); ?></label>
									</th>
									<td>
										<select id="prflxtrflds-options-sort-sequence" name="prflxtrflds_options[sort_sequence]" >
											<option value="asc"<?php selected( $option_sort_sequence, 'asc' ); ?>><?php _e( 'ASC', 'profile_extra_fields' ); ?></option>
											<option value="desc"<?php selected( $option_sort_sequence, 'desc' ); ?>><?php _e( 'DESC', 'profile_extra_fields' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th>
										<?php _e( 'Hide empty fields', 'profile_extra_fields' ); ?>
									</th>
									<td>
										<input type="checkbox" id="prflxtrflds-options-show-empty-columns" name="prflxtrflds_options[show_empty_columns]" value="1" <?php echo $checked_show_empty_columns; ?> />
										<label for="prflxtrflds-options-show-empty-columns"><?php _e( 'Hide the field if the value is not filled in by any user', 'profile_extra_fields' ); ?></label>
									</td>
								</tr>
								<tr>
									<th>
										<label for="prflxtrflds-options-show-id"><?php _e( 'Show user ID', 'profile_extra_fields' ); ?></label>
									</th>
									<td>
										<input type="checkbox" id="prflxtrflds-options-show-id" name="prflxtrflds_options[show_id]" value="1" <?php echo $checked_show_id; ?> />
									</td>
								</tr>
								<tr>
									<th>
										<label for="prflxtrflds-position-header-table"><?php _e( 'Position of the table header', 'profile_extra_fields' ); ?></label>
									</th>
									<td>
										<select id="prflxtrflds-position-header-table" name="prflxtrflds_options[header_table]" >
											<option value="top"<?php selected( $option_header_table, 'top' ); ?>><?php _e( 'Top', 'profile_extra_fields' ); ?></option>
											<option value="left"<?php selected( $option_header_table, 'left' ); ?>><?php _e( 'Left', 'profile_extra_fields' ); ?></option>
											<option value="right"<?php selected( $option_header_table, 'right' ); ?>><?php _e( 'Right', 'profile_extra_fields' ); ?></option>
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
						<input type="hidden" name="action" value="update" />
						<!--other options. Save all prflxtrflds_options-->
						<input type="hidden" name="prflxtrflds_options[plugin_db_version]" value="<?php echo esc_html( $prflxtrflds_options['plugin_db_version'] ) ?>" />
						<input type="hidden" name="prflxtrflds_options[plugin_option_version]" value="<?php echo esc_html( $prflxtrflds_options['plugin_option_version'] ) ?>" />
						<!--end other options-->
						<input type="hidden" name="prflxtrflds_shcd_saved" value="true" />
						<input type="hidden" name="page_options" value="prflxtrflds_options" />
						<p class="submit">
							<input type="submit" class="button-primary" name="prflxtrflds_save_changes" value="<?php _e( 'Save Changes', 'profile_extra_fields' ); ?>" />
						</p>
					</form>
				<?php }
			}
			bws_plugin_reviews_block( $prflxtrflds_plugin_info['Name'], 'profile-extra-fields' ); ?>
		</div><!--.wrap-->
	<?php }
}

/* print shortcode */
if ( ! function_exists( 'prflxtrflds_show_data' ) ) {
	function prflxtrflds_show_data( $param ) {
		global $wpdb, $prflxtrflds_options;
		if ( ! isset( $prflxtrflds_options ) ) {
			$prflxtrflds_options = get_option( 'prflxtrflds_options' );
		}
		extract( shortcode_atts( array(
			'user_id'    => '',
			'user_role'  => '',
			'display'    => '',
		), $param ) );
		$shortcode_error 		= 0;
		$table_fields_id 		= $wpdb->base_prefix . 'prflxtrflds_fields_id';
		$table_field_types 		= $wpdb->base_prefix . 'prflxtrflds_field_types';
		$table_roles_id 		= $wpdb->base_prefix . 'prflxtrflds_roles_id';
		$table_roles_and_fields = $wpdb->base_prefix . 'prflxtrflds_roles_and_fields';
		$table_user_roles 		= $wpdb->base_prefix . 'prflxtrflds_user_roles';
		$wp_users				= $wpdb->base_prefix . 'users';
		$table_user_field_data  = $wpdb->base_prefix . 'prflxtrflds_user_field_data';
		$filter_options 		= " ";
		/* Default order */
		$order_options 			= " ORDER BY " . $wp_users . ".`user_nicename` ASC";
		$order_settings 		= $prflxtrflds_options['sort_sequence'];
		if ( ! empty( $order_settings ) ) {
			$order_options = " ORDER BY " . $wp_users . ".`user_nicename`";
			if ( 'asc' == $order_settings ) {
				$order_options .= " ASC";
			} elseif ( 'desc' == $order_settings ) {
				$order_options .= " DESC";
			} else {
				/* Default */
				$order_options .= " ASC";
			}
		}
		/* Only filled option */
		$user_option 		= "";
		$extended_value = "";
		/* Get user id param */
		if ( ( ! empty( $param['user_id'] ) ) ) {
			$user_ids = explode( ",", $param['user_id'] );
			if ( is_array( $user_ids ) ) {
				/* If lot user ids */
				foreach ( $user_ids as $user_id ) {
					/* Check for existing user */
					if ( ( ! is_numeric( $user_id ) ) || ( ! get_user_by( 'id', intval( $user_id ) ) ) ) {
						/* Show error if user id not exist, or data is uncorrect */
						$shortcode_error = 1;
						$error_message = sprintf( __( 'User with entered id ( id=%s ) is not exist! If you entered id corrected, try enter data without spaces.', 'profile_extra_fields' ), esc_html( $user_id ) );
						break;
					}
				}
				if ( 0 != $shortcode_error ) {
					/* Delete ids if error */
					$user_ids 	 = array();
				}
			}
		}
		/* Get user role param */
		if ( ( ! empty( $param['user_role'] ) ) ) {
			$user_roles = explode( ",", $param['user_role'] );
			if ( is_array( $user_roles ) ) {
				/* Create array if empty */
				if ( ! isset( $user_ids ) )
					$user_ids = array();
				foreach ( $user_roles as $role ) {
					$role = stripslashes( $role );
					/* Check for exist user role */
					if ( $role_id = $wpdb->get_var( "SELECT `role_id` FROM " . $table_roles_id . " WHERE `role` LIKE '%" . $role . "%'" ) ) { /* $wpdb->prepare with % not work */
						/* Get user ids by role */
						$ids_for_role = $wpdb->get_col( $wpdb->prepare( "SELECT `user_id` FROM " . $table_user_roles . " WHERE `role_id`=%d", $role_id ) );
						if ( ! empty( $ids_for_role ) ) {
							$user_ids = array_merge( $user_ids, $ids_for_role );
						}
					} else {
						/* If role not exists, generate error */
						$shortcode_error = 1;
						$error_message = sprintf( __( 'Role with entered name ( %s ) is not exist! If you entered the name correctly, try enter data without whitespaces in English.', 'profile_extra_fields' ), esc_html( $role ) );
						break;
					}
				}
			}
		}
		/* Get display options */
		if ( ( ! empty( $param['display'] ) ) ) {
			$permitted_display = array( 'left', 'top', 'right' );
			/* If this values is supported */
			if ( in_array( $param['display'], $permitted_display ) ) {
				$display = $param['display'];
			} else {
				$shortcode_error = 1;
				$error_message = sprintf( __( 'Unsupported shortcode option ( display = %s )', 'profile_extra_fields' ), esc_html( $param['display'] ) );
			}
		} else {
			/* If value not in shortcode, get from options. Top by default */
			$display = isset( $prflxtrflds_options['header_table'] ) ? $prflxtrflds_options['header_table'] : 'top';
		}

		if ( ( 0 == $shortcode_error ) ) {
			if ( ! empty( $user_ids ) ) {
				/* Collate all users ids */
				$user_option = "AND " . $table_user_roles . ".`user_id` IN (" . implode( ',', $user_ids ) . ")";
			} elseif ( ! empty( $user_roles ) && is_array( $user_roles ) ) {
				/* If not exist users for choisen role. User ids is empty and select all users */
				$shortcode_error = 1;
				$error_message = sprintf( __( 'For selected roles ( %s ) are no users', 'profile_extra_fields' ), esc_html( implode( ', ', $user_roles ) ) );
			} else {
				$user_option = "";
			}
		}
		isset( $prflxtrflds_options['available_values'] ) ? $field_value = $prflxtrflds_options['available_values'] : $field_value = '';
		/* If empty = true - no extended value */
		$empty = true;
		if ( is_array( $field_value ) && ( 0 < sizeof( $field_value ) ) ) {
			$extended_value	 = "AND " . $table_user_roles . ".`user_id`=";
			$extended_value .= "(SELECT " . $table_user_field_data . ".`user_id` FROM " . $table_user_field_data;
			$i = 0;
			foreach ( $field_value as $one_field_value ) {
				if ( '-1' != $one_field_value ) {
					/* If fields is empty - no set extended value */
					$empty = false;
				}
				if ( 0 == $i ) {
					$extended_value .= " WHERE " . $table_user_field_data . ".`value_id`='" . $one_field_value . "'" .
						" AND " . $table_user_field_data . ".`user_id`=" . $table_user_roles . ".`user_id`" .
						" AND " . $table_user_field_data . ".`user_value`='1'";
				} else {
					$extended_value .= " OR " . $table_user_field_data . ".`value_id`='" . $one_field_value . "'" .
						" AND " . $table_user_field_data . ".`user_id`=" . $table_user_roles . ".`user_id`
						 AND " . $table_user_field_data . ".`user_value`='1'";
				}
				$i++;
			}
			$extended_value .= ")";
		}
				/* If required value not selected, not add extended value */
		if ( false == $empty ) {
			$user_option = $user_option . $extended_value;
		}
		/* Get all fields for all users, but not get values */
		$get_users_data_sql = "SELECT " . $wp_users . ".`user_nicename` , " .
 				$table_roles_id . ".`role` , " .
 				$table_user_roles . ".`user_id` , " .
				$table_fields_id . ".`field_name` , " .
				$table_fields_id . ".`field_id` , " .
				$table_field_types . ".`field_type_id` " .
			"FROM " . $wp_users .
				" INNER JOIN " . $table_user_roles .
				" ON " . $table_user_roles . ".`user_id`=" . $wp_users .".`ID` " .
				$user_option .
					" LEFT JOIN " . $table_roles_and_fields .
						" ON " . $table_roles_and_fields . ".`role_id`=" . $table_user_roles . ".`role_id` " .
					" LEFT JOIN " . $table_roles_id .
						" ON " . $table_roles_id . ".`role_id`=" . $table_user_roles . ".`role_id` " .
					" LEFT JOIN " . $table_fields_id .
						" ON " . $table_fields_id . ".`field_id`=" . $table_roles_and_fields . ".`field_id` " .
					" LEFT JOIN " . $table_field_types .
						" ON " . $table_field_types . ".`field_id`=" . $table_fields_id . ".`field_id`";
		/* Get options. Which fields must be displayed */
		isset( $prflxtrflds_options['available_fields'] ) ? $field_options = $prflxtrflds_options['available_fields'] : $field_options = '';
		if ( ! empty( $field_options ) && is_array( $field_options ) ) {
			foreach ( $field_options as $one_field_id ) {
				if ( " " == $filter_options ) {
					/* If option is first */
					$filter_options .= " WHERE " . $table_fields_id . ".`field_id`='" . $one_field_id . "'";
				} else {
					/* For second or more options */
					$filter_options .= " OR " . $table_fields_id . ".`field_id`='" . $one_field_id . "'";
				}
			}
		} else {
			/* If not change fields to display on settings page */
			$filter_options = " WHERE " . $table_fields_id . ".`field_id`='-1'";
		}
		/* Create resut query. */
		$get_users_data_sql = $get_users_data_sql . $filter_options . $order_options;
		/* Begin collate data to print shortcode */
		ob_start();
		if ( 0 == $shortcode_error ) {
			$printed_table = $wpdb->get_results( $get_users_data_sql, ARRAY_A );
			if ( ! empty( $printed_table ) ) {
				$table_field_values 	= $wpdb->base_prefix . 'prflxtrflds_field_values';
				$table_user_field_data  = $wpdb->base_prefix . 'prflxtrflds_user_field_data';
				$get_textfield_sql 		= "SELECT " . $table_user_field_data . ".`user_value`
					FROM " . $table_user_field_data .
					" WHERE " . $table_user_field_data . ".`user_id`=%d
						AND " . $table_user_field_data . ".`value_id`=(
							SELECT `value_id`
								FROM " . $table_field_values .
								" WHERE `field_id`=%d LIMIT 1
						)";
				/* Get user data from non-textfield */
				$get_non_textfield_sql = "SELECT `value_name`
						FROM " . $table_field_values .
						" WHERE `value_id` IN (
							SELECT `value_id`
								FROM " . $table_user_field_data .
								" WHERE `user_id`=%d
									AND `user_value`='1'
							)
								AND `field_id`=%d
								AND `value_name`<>'prflxtrflds_textfield'";
				/* Add column value to $printed table */
				foreach ( $printed_table as $key=>&$column ) {
					if ( '1' == $column['field_type_id'] ) {
						/* If is textfield */
						$column['value'] = $wpdb->get_var( $wpdb->prepare( $get_textfield_sql, $column['user_id'], $column['field_id'] ) );
					} else {
						/* If is not textfield */
						$column['value'] = implode( ', ', $wpdb->get_col( $wpdb->prepare( $get_non_textfield_sql, $column['user_id'], $column['field_id'] ) ) );
					}
				}
				unset( $column );
				/* Create last empty entry for correct processing */
				$last_elem 					 = array();
				$last_elem['user_nicename']  = '';
				$last_elem['field_id'] 		 = 0;
				$last_elem['value'] 		 = '';
				$last_elem['user_id'] 		 = -1;
				/* Add last empty entry */
				array_push( $printed_table, $last_elem );
				/* Get options */
				$option_empty_value 		  = isset( $prflxtrflds_options['empty_value'] ) ? $prflxtrflds_options['empty_value'] : '';
				$option_not_available_message = isset( $prflxtrflds_options['not_available_message'] ) ? $prflxtrflds_options['not_available_message'] : '';
				$option_show_id               = isset( $prflxtrflds_options['show_id'] ) ? $prflxtrflds_options['show_id'] : null;
				/* Get all field names */
				if ( isset( $field_options ) ) {
					$all_fields_sql = "SELECT `field_name`, `field_id` FROM " . $table_fields_id . " WHERE `field_id` IN ( " . implode( ', ', $field_options ) . " )";
				} else {
					/* By default show all fields */
					$all_fields_sql = "SELECT DISTINCT `field_name`, `field_id` FROM " . $table_fields_id;
				}
				$all_fields = $wpdb->get_results( $all_fields_sql, ARRAY_A );
				/* If need not show empty collumns */
				if ( ! empty( $prflxtrflds_options['show_empty_columns'] ) ) {
					/* This forecah delete not filled columns */
					foreach ( $all_fields as $key=>$one_field ) {
						$is_empty = 1;
						foreach ( $printed_table as $printed_line ) {
							/* If field not empty */
							if ( ( $printed_line['field_id'] == $one_field['field_id'] ) ) {
								if ( ( isset( $printed_line['value'] ) ) && ( ! empty( $printed_line['value'] ) ) ) {
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
					/* This foreach collate not empty user ids */
					$not_empty_user_id = array();
					foreach ( $all_fields as $key=>$one_field ) {
						foreach ( $printed_table as $printed_line ) {
							if ( ( $printed_line['field_id'] == $one_field['field_id'] ) ) {
								if ( ! empty( $printed_line['value'] ) ) {
									/* If field not empty, add user to array */
									$not_empty_user_id[] = $printed_line['user_id'];
								}
							}
						}
					}
				}
				if ( 'top' == $display ) { ?>
					<table class="prflxtrflds-userdata-tbl">
						<thead>
							<tr>
								<?php if ( isset( $option_show_id ) ) { ?>
									<th><?php _e( 'User ID', 'profile_extra_fields' ); ?></th>
								<?php } ?>
								<th><?php _e( 'Username', 'profile_extra_fields' ); ?></th>
								<?php foreach( $all_fields as $one_field ) { ?>
									<th><?php echo $one_field['field_name']; ?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php $prev_nicename		 = '';
							/* Set default varaibles */
							$line_values 				 = array();
							foreach ( $printed_table as $column ) {
								/* If exist array with empty users */
								if ( isset( $not_empty_user_id ) && is_array( $not_empty_user_id ) ) {
									/* Save last user with id=-1 for correct processing array */
									if (  ( -1 != $column['user_id'] ) && ( ! in_array( $column['user_id'], $not_empty_user_id ) ) ) {
										/* If current user is empty - skip this entry */
										continue;
									}
								}
								/* If is new username */
								if ( $prev_nicename != $column['user_nicename'] ) {
									/* First, render array */
									if ( ! empty( $line_values ) ) {
										/* If all field available */
										if ( ( sizeof( $line_values ) - 1 ) == sizeof( $all_fields ) && isset( $prflxtrflds_options['show_empty_columns'] ) ) { ?>
											<tr>
												<?php if ( isset( $option_show_id ) ) { ?>
													<td>
														<?php echo esc_html( get_user_by( 'slug', $line_values['nicename'] )->ID ); ?>
													</td>
												<?php }
												foreach ( $line_values as $key=>$value ) { ?>
													<td>
														<?php if ( ! empty( $value ) ) {
															/* Print user value */
															echo esc_html( $value );
														} else {
															/* If value is empty, print empty message */
															echo esc_html( $option_empty_value );
														} ?>
													</td>
												<?php } ?>
											</tr>
										<?php } else { /* If not all field available */?>
											<tr>
												<?php if ( isset( $option_show_id ) ) { ?>
													<td>
														<?php echo esc_html( get_user_by( 'slug', $line_values['nicename'] )->ID ); ?>
													</td>
												<?php } ?>
												<td>
													 <?php echo esc_html( $line_values['nicename'] ); ?>
												 </td>
												<?php	foreach ( $all_fields as $one_field ) {
													/* Check for available fields and pritn it  */
													if ( key_exists( $one_field['field_id'], $line_values ) ) { ?>
														<td>
															<?php if ( ! empty( $line_values[ $one_field['field_id'] ] ) ) {
																/* Print user value */
																echo esc_html( $line_values[ $one_field['field_id'] ] );
															} else {
																/* This field user not filled */
																echo esc_html( $option_empty_value );
															} ?>
														</td>
													<?php } else { ?>
														<td>
															<?php echo esc_html( $option_not_available_message ); /* This field not available for current user */ ?>
														</td>
													<?php }
												} ?>
											</tr>
										<?php }
									}
									/* Clear with user data line array */
									$line_values = array();
									/* Create array for new user */
									$prev_nicename 						= $column['user_nicename'];
									$line_values['nicename'] 			= $column['user_nicename'];
									$line_values[ $column['field_id'] ] = $column['value'];
								} else {
									/* If is previous user, just add data */
									$line_values[ $column['field_id'] ] = $column['value'];
								}
							} ?>
						</tbody>
					</table><!--.prflxtrflds-userdata-tbl-->
				<?php } elseif ( ( 'left' == $display ) || ( 'right' == $display ) ) {
					$distinct_users = array();
					foreach( $printed_table as $one_row ) {
						/* Skip empty users if exist */
						if ( isset( $not_empty_user_id ) && is_array( $not_empty_user_id ) ) {
							if ( ! in_array( $one_row['user_id'], $not_empty_user_id ) )
								continue;
						}
						/* Create array of distinct users */
						if ( 0 < $one_row['user_id'] && ! isset( $distinct_users[ $one_row['user_id'] ] ) ) {
							$distinct_users[ $one_row['user_id'] ] = $one_row['user_nicename'];
						}
					} ?>
					<table>
						<?php if ( isset( $option_show_id ) ) { /* Show users id if need */?>
						<tr>
							<?php if ( 'left' == $display ) { ?>
								<th><?php _e( 'User ID', 'profile_extra_fields' ); ?></th>
							<?php }
							foreach ( array_keys( $distinct_users ) as $user_id ) { ?>
								<td><?php echo esc_html( $user_id ); ?></td>
							<?php }
							if ( 'right' == $display ) { ?>
								<th><?php _e( 'User ID', 'profile_extra_fields' ); ?></th>
							<?php } ?>
						</tr>
						<?php } /* Show user name */?>
						<tr>
							<?php if ( 'left' == $display ) { ?>
								<th><?php _e( 'Username', 'profile_extra_fields' ); ?></th>
							<?php }
							foreach ( $distinct_users as $user_name ) { ?>
								<td><?php echo esc_html( $user_name ); ?></td>
							<?php }
							if ( 'right' == $display ) { ?>
								<th><?php _e( 'Username', 'profile_extra_fields' ); ?></th>
							<?php } ?>
						</tr>
						<?php foreach ( $all_fields as $one_field ) { /* Create new row for every field */?>
							<tr>
								<?php if ( 'left' == $display ) { ?>
									<th>
										<?php echo esc_html( $one_field['field_name'] ); ?>
									</th>
								<?php }
								foreach ( array_keys( $distinct_users ) as $one_user_id ) /* Create column for every user */{
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
											echo esc_html( $option_not_available_message );
										} elseif( empty( $user_field_data ) ) {
											/* This value is empty. Unset user data for next user */
											echo esc_html( $option_empty_value );
											unset( $user_field_data );
										} else {
											/* Print user data. Unset for next user */
											echo esc_html( $user_field_data );
											unset( $user_field_data );
										} ?>
									</td>
								<?php }
								if ( 'right' == $display ) { ?>
									<th>
										<?php echo esc_html( $one_field['field_name'] ); ?>
									</th>
								<?php } ?>
							</tr>
						<?php } ?>
					</table>
				<?php }
			} else { /* If printed table is empty */?>
				<p>
					<?php _e( 'No data for a current shortcode settings', 'profile_extra_fields' ); ?>
				</p>
			<?php }
			$prflxtrflds_shortcode_output = ob_get_contents();
			ob_end_clean();
		}
		if ( ! empty( $prflxtrflds_shortcode_output ) ) {
			return $prflxtrflds_shortcode_output;
		} else {
			if ( ! isset( $error_message ) ) {
				/* Create empty error message if not exist */
				$error_message = '';
			}
			return sprintf( '<p>%s. %s</p>', __( 'Shortcode output error', 'profile_extra_fields' ), $error_message );
		}
	}
}

/*this function show content in user profile page*/
if ( ! function_exists( 'prflxtrflds_user_profile_fields' ) ) {
	function prflxtrflds_user_profile_fields() {
		global $wpdb;
		/*get user id (this method not work in my profile)*/
		if ( isset( $_GET['user_id'] ) ) {
			/* Get if page edit, post if error page */
			$userid = intval( $_GET['user_id'] );
		} elseif ( isset( $_POST['user_id'] ) ) {
			$userid = intval( $_POST['user_id'] );
		} else {
			$userid = get_current_user_id();
		}
		$user_info = get_userdata( $userid ); /* get userinfo by id */
		$current_role = implode( ', ', $user_info->roles );
		/* All need tables */
		$table_fields_id 		= $wpdb->base_prefix . 'prflxtrflds_fields_id';
		$table_field_types 		= $wpdb->base_prefix . 'prflxtrflds_field_types';
		$table_roles_id 		= $wpdb->base_prefix . 'prflxtrflds_roles_id';
		$table_roles_and_fields = $wpdb->base_prefix . 'prflxtrflds_roles_and_fields';
		$table_field_values		= $wpdb->base_prefix . 'prflxtrflds_field_values';
		$table_user_field_data  = $wpdb->base_prefix . 'prflxtrflds_user_field_data';
		/* Get data for current role */
		$get_fields_id_sql = "SELECT " . $table_roles_and_fields . ".`field_id`, " .
 			$table_field_types . ".`field_type_id`, " .
 			$table_fields_id . ".`field_name`, " .
 			$table_fields_id . ".`required`, " .
 			$table_fields_id . ".`description`, " .
 			$table_field_values . ".`value_id`, " .
 			$table_field_values . ".`value_name`, " .
 			$table_user_field_data . ".`user_value`, " .
 			$table_roles_and_fields . ".`field_order`
			FROM " . $table_roles_id .
			" INNER JOIN " . $table_roles_and_fields .
					" ON " . $table_roles_and_fields . ".`role_id`=" . $table_roles_id . ".`role_id`" .
					" AND " . $table_roles_id . ".`role`='" . $current_role . "'
				INNER JOIN " . $table_field_types .
					" ON " . $table_field_types . ".`field_id`=" . $table_roles_and_fields . ".`field_id`
				INNER JOIN " . $table_fields_id .
					" ON " . $table_fields_id . ".`field_id`=" . $table_roles_and_fields . ".`field_id`
				LEFT JOIN " . $table_field_values .
					" ON " . $table_field_values . ".`field_id`=" . $table_roles_and_fields . ".`field_id`
				LEFT JOIN " . $table_user_field_data .
					" ON " . $table_user_field_data . ".`value_id`=" . $table_field_values . ".`value_id`
						AND " . $table_user_field_data . ".`user_id`='" . $userid . "'
							ORDER BY " . $table_roles_and_fields . ".`field_order` ASC, " .
								$table_roles_and_fields . ".`field_id` ASC, " .
								$table_field_values . ".`value_id` ASC";
		if ( ! $all_entry = $wpdb->get_results( $get_fields_id_sql, ARRAY_A ) ) {
			/* If data for current role not exists, update table and try again */
			prflxtrflds_update_roles_id();
			$all_entry = $wpdb->get_results( $get_fields_id_sql, ARRAY_A );
		}
		/* Group result array by field_id */
		$render_data = array();
		foreach ( $all_entry as $one_entry ) {
			if ( 1 == $one_entry['field_type_id'] ) {
				/* For textfield */
				$current_field_id = $one_entry['value_id'];
			} else {
				/* For non-textfield */
				$current_field_id = $one_entry['field_id'];
			}
			if ( isset( $_POST['prflxtrflds_user_field_value'][ $current_field_id ] ) ) {
				$current_post = $_POST['prflxtrflds_user_field_value'][ $current_field_id ];
				/* If return error, get post and print entered data  */
				if ( is_array( $current_post ) && ( FALSE != in_array( strval( $one_entry['value_id'] ), $current_post ) && ( '1' !=  $one_entry['field_type_id'] ) ) ) {
					/* If this value checked - make it checked */
					$one_entry['user_value'] = "1";
				} elseif ( is_array( $current_post ) && ( FALSE == in_array( $one_entry['value_id'], $current_post ) && ( '1' !=  $one_entry['field_type_id'] ) ) ) {
					/* Else - uncheck */
					$one_entry['user_value'] = "0";
				} elseif( ( '1' ==  $one_entry['field_type_id'] ) && ( is_array( $current_post ) ) ) {
					/* If user filled this field - show it */
					$one_entry['user_value'] = stripslashes( esc_html( $current_post['0'] ) );
				}
			}
			$render_data[ $current_field_id ][] = $one_entry;
		}
		if ( 0 < sizeof( $render_data ) ) { /* Render user data */ ?>
			<!-- Begin code from user role extra field -->
			<h3><?php _e( "Extra profile information", "profile_extra_fields" ); ?></h3>
			<input type="hidden" name="prflxtrflds_user_field_value" value="" >
			<table class="form-table">
				<?php foreach ( $render_data as $one_entry ) {
				switch ( $one_entry[0]['field_type_id'] ) {
					case '1': ?>
							<tr>
								<th>
									<input type="hidden" name="prflxtrflds_textfields[]" value="<?php echo $one_entry[0]['value_id']; ?>">
									<label for="<?php echo 'prflxtrflds_user_field_value[' . $one_entry[0]['value_id'] . ']'; ?>">
										<?php echo $one_entry[0]['field_name']; ?>
									</label>
									<?php if ( "1" == $one_entry[0]['required'] ) { ?>
										<span class="description"><?php _e( '(required)', 'profile_extra_fields' )?></span>
										<input type="hidden" name="<?php echo "prflxtrflds_required[" . $one_entry[0]['value_id'] . "][]"; ?>" value="<?php echo $one_entry[0]['field_name']; ?>">
									<?php } ?>
								</th>
								<td>
									<input type="text" id="<?php echo 'prflxtrflds_user_field_value[' . $one_entry[0]['value_id'] . ']'; ?>" name="<?php echo 'prflxtrflds_user_field_value[' . $one_entry[0]['value_id'] . '][]'; ?>" value="<?php if( isset( $one_entry[0]['user_value'] ) ) { echo $one_entry[0]['user_value']; } ?>">
									<?php if ( isset( $one_entry[0]['description'] ) ) { ?>
										<p class="description"> <?php echo $one_entry[0]['description']; ?></p>
									<?php } ?>
								</td>
							</tr>
							<?php break;
					case '2': ?>
							<tr>
								<th>
									<?php echo $one_entry[0]['field_name'];
									if( "1" == $one_entry[0]['required'] ){ ?>
										<span class="description"><?php _e( '(required)', 'profile_extra_fields' )?></span>
										<input type="hidden" name="<?php echo "prflxtrflds_required[" . $one_entry[0]['field_id'] . "][]"; ?>" value="<?php echo $one_entry[0]['field_name']; ?>">
									<?php } ?>
								</th>
								<td>
									<?php
									foreach ( $one_entry as $one_sub_entry ) { ?>
										<input type="checkbox" id="<?php echo $one_sub_entry['value_id']; ?>" name="<?php echo 'prflxtrflds_user_field_value[' . $one_entry[0]['field_id'] . '][]'; ?>" value="<?php echo $one_sub_entry['value_id']; ?>"<?php if( isset( $one_sub_entry['user_value'] ) ) checked( $one_sub_entry['user_value'], '1' );  ?> />
										<label for="<?php echo $one_sub_entry['value_id']; ?>"><?php echo $one_sub_entry['value_name']; ?></label>
										<br />
									<?php }
									if ( isset( $one_entry[0]['description'] ) ) { ?>
										<p class="description"> <?php echo $one_entry[0]['description']; ?></p>
									<?php } ?>
								</td>
							</tr>
							<?php break;
					case '3': ?>
							<tr>
								<th>
									<?php echo $one_entry[0]['field_name'];
									if ( "1" == $one_entry[0]['required'] ) { ?>
										<span class="description"><?php _e( '(required)', 'profile_extra_fields' )?></span>
										<input type="hidden" name="<?php echo "prflxtrflds_required[" . $one_entry[0]['field_id'] . "][]"; ?>" value="<?php echo $one_entry[0]['field_name']; ?>">
									<?php } ?>
								</th>
								<td>
									<?php
									foreach ( $one_entry as $one_sub_entry ) { ?>
										<input type="radio" id="<?php echo $one_sub_entry['value_id']; ?>" name="<?php echo 'prflxtrflds_user_field_value[' . $one_entry[0]['field_id'] . '][]'; ?>" value="<?php echo $one_sub_entry['value_id']; ?>"<?php if( isset( $one_sub_entry['user_value'] ) ) checked( $one_sub_entry['user_value'], '1' ); ?>>
										<label for="<?php echo $one_sub_entry['value_id']; ?>"><?php echo $one_sub_entry['value_name']; ?></label>
										<br />
									<?php }
									if ( isset( $one_entry[0]['description'] ) ) { ?>
										<p class="description"> <?php echo $one_entry[0]['description']; ?></p>
									<?php } ?>
								</td>
							</tr>
							<?php break;
					case '4': ?>
						<tr>
							<th>
								<label for="<?php echo 'prflxtrflds_user_field_value[' . $one_entry[0]["field_id"] . ']'; ?>">
									<?php echo $one_entry[0]["field_name"]; ?>
								</label>
								<?php if ( "1" == $one_entry[0]['required'] ) { ?>
									<span class="description"><?php _e( '(required)', 'profile_extra_fields' )?></span>
									<input type="hidden" name="<?php echo "prflxtrflds_required[" . $one_entry[0]['field_id'] . "][]"; ?>" value="<?php echo $one_entry[0]['field_name']; ?>" />
								<?php } ?>
							</th>
								<td>
									<select id="<?php echo 'prflxtrflds_user_field_value[' . $one_entry[0]["field_id"] . ']'; ?>" name="<?php echo 'prflxtrflds_user_field_value[' . $one_entry[0]["field_id"] . '][]'; ?>">
										<option></option>
										<?php foreach( $one_entry as $one_sub_entry ) { ?>
											<option value="<?php echo $one_sub_entry['value_id']; ?>"<?php if(  isset( $one_sub_entry['user_value'] ) ) selected( $one_sub_entry['user_value'], '1' ); ?>><?php echo $one_sub_entry['value_name']; ?></option>
										<?php } ?>
									</select>
									<?php if ( isset( $one_entry[0]['description'] ) ) { ?>
										<p class="description"> <?php echo $one_entry[0]['description']; ?></p>
									<?php } ?>
								</td>
						</tr>
						<?php break;
				}
			} ?>
			</table><!--.form-table--><!-- End code from user role extra fields -->
		<?php }
		}
	}

/* Send errors to edit user page */
if ( ! function_exists( 'prflxtrflds_create_user_error' ) ) {
	function prflxtrflds_create_user_error( $errors, $update = null, $user = null ) {
		if ( isset( $_POST['prflxtrflds_required'] ) ) {
			/* Get all reqired ids */
			foreach ( $_POST['prflxtrflds_required'] as $required_id=>$required_name ) {
				if ( ( isset( $_POST['prflxtrflds_user_field_value'] ) ) && ( ! array_key_exists( $required_id, $_POST['prflxtrflds_user_field_value'] ) ) ) {
					/* Error for non-textfield */
					$errors->add( 'required_error', sprintf( __( 'Required field %s is not filled. Data was not saved!', 'profile_extra_fields' ), '<strong>' . $required_name[0] . '</strong>' ) );
				} elseif ( ( isset( $_POST['prflxtrflds_user_field_value'] ) ) && ( is_array( $_POST['prflxtrflds_user_field_value'][ $required_id ] ) ) ){
					/* Trim whitespaces */
					$enter_data = trim( $_POST['prflxtrflds_user_field_value'][ $required_id ][0] );
					if ( ( empty( $enter_data ) ) ) {
						/* Error for textfields */
						$errors->add( 'required_error', sprintf( __( 'Required field %s is not filled. Data was not saved!', 'profile_extra_fields' ), '<strong>' . $required_name[0] . '</strong>' ) );
					}
				}
			}
		}
	}
}

/* Save user data from Edit user page */
if ( ! function_exists( 'prflxtrflds_save_user_data' ) ) {
	function prflxtrflds_save_user_data() {
		if ( isset( $_POST['user_id'] ) ) {
			$user_id = intval( filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT ) );
		} else {
			$user_id = get_current_user_id();
		}
		/* Get errors */
		$errors 		= edit_user( $user_id );
		$textfields_id  = array();
		if ( isset( $_POST['prflxtrflds_textfields'] ) ) {
			/* Is array */
			$textfields_id = array_map( 'stripslashes_deep', $_POST['prflxtrflds_textfields'] );
		}
		/* Create array with user values */
		foreach ( $_POST as $name => $val ) {
			if ( strstr( $name, 'prflxtrflds_user_field_value' ) ) {
				/* if entry exist for current user, create array */
				$update_userdata_sql = array();
				if ( is_array( $val ) ) {
					foreach ( $val as $id => $user_value ) {
						if ( in_array( $id, $textfields_id ) ) {
							/* If is textfield */
							$update_userdata_sql[ $id ] = $user_value[0];
						} elseif ( is_array( $user_value ) ) {
							/* Is array of value ids  */
							foreach ( $user_value as $id ) {
								$update_userdata_sql[ $id ] = '1';
							}
						}
					}
				}
			}
		}
		if ( ! is_wp_error( $errors ) ) {
			/* write data to database if no errors */
			global $wpdb;
			$table_user_field_data = $wpdb->base_prefix . 'prflxtrflds_user_field_data';
			/* If all data is correct, delete old data */
			if ( isset( $update_userdata_sql ) ) {
				/* If array exists ( exist available fields for current user ), remove old data */
				$wpdb->delete(
					$table_user_field_data,
					array( 'user_id' => $user_id )
				);
				foreach ( $update_userdata_sql as $value_id => $user_value ) {
					/* Sanitize data before write */
					$user_value = stripslashes( esc_html( $user_value ) );
					$value_id 	= intval( $value_id );
					$wpdb->update(
						$table_user_field_data,
						array( 'user_value' => $user_value ),
						array(
							'value_id'  => $value_id,
							'user_id'   => $user_id,
						),
						array( '%s' ),
						array( '%d', '%d' )
					);
					$check_existing_sql = "SELECT `id` FROM " . $table_user_field_data . " WHERE `user_id`=%d AND `value_id`=%d LIMIT 1";
					if ( 0 == ( $wpdb->query( $wpdb->prepare( $check_existing_sql, $user_id, $value_id ) ) ) ) {
						$wpdb->insert(
							$table_user_field_data,
							array(
								'user_value' => $user_value,
								'value_id' 	 => $value_id,
								'user_id'	 => $user_id,
							),
							array( '%s', '%d', '%d' )
						);
					}
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

			if ( isset( $_POST['field_id'] ) && ( 'all' != $_POST['field_id'] ) ) {
				$role_id = intval( filter_input( INPUT_POST, 'field_id', FILTER_SANITIZE_STRING ) );
			} else {
				/* Role id = 0 for 'all' users */
				$role_id = 0;
			}
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
			wp_die();
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
		$wpdb->query( "DROP TABLE IF EXISTS " . implode( ', ', $table_names) );
		/* Delete options */
		delete_option( 'prflxtrflds_options' );
	}
}

/* This links under plugin name */
if ( ! function_exists ( 'prflxtrflds_plugin_action_links' ) ) {
	function prflxtrflds_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=profile-extra-fields.php">' . __( 'Settings', 'profile_extra_fields' ) . '</a>';
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
				$links[] = '<a href="admin.php?page=profile-extra-fields.php">' . __( 'Settings', 'profile_extra_fields' ) . '</a>';
			$links[] = '<a href="http://bestwebsoft.com/products/profile-extra-fields/">' . __( 'FAQ', 'profile_extra_fields' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support', 'profile_extra_fields' ) . '</a>';
		}
		return $links;
	}
}

/* Register scripts */
if ( ! function_exists('prflxtrflds_load_script') ) {
	function prflxtrflds_load_script() {
		if ( isset( $_GET['page'] ) && 'profile-extra-fields.php' == $_GET['page'] ) {
			wp_enqueue_style( 'prflxtrflds_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_script( 'prflxtrflds_script', plugins_url( '/js/script.js', __FILE__ ) );
			wp_enqueue_script( 'prflxtrflds_sortable', plugins_url( '/js/jquery-sortable.js', __FILE__ ) );
			$script_vars = array(
				'prflxtrflds_ajax_url'	=> admin_url( 'admin-ajax.php' ),
				'prflxtrflds_nonce' 	=> wp_create_nonce( plugin_basename( __FILE__ ), 'prflxtrflds_ajax_nonce_field' ),
			);
			wp_localize_script( 'prflxtrflds_script', 'prflxtrflds_ajax', $script_vars );
		}
	}
}

/*bws menu*/
add_action( 'admin_menu', 'prflxtrflds_admin_menu' );
/*plugin init*/
add_action( 'init', 'prflxtrflds_init' );
/*plugin admin init*/
add_action( 'admin_init', 'prflxtrflds_admin_init' );
/* To screen options */
add_filter( 'set-screen-option', 'prflxtrflds_table_set_option', 10, 3 );
/* This links under plugin name */
add_filter( 'plugin_action_links', 'prflxtrflds_plugin_action_links', 10, 2 );
/* This links in plugin description */
add_filter( 'plugin_row_meta', 'prflxtrflds_register_plugin_links', 10, 2 );
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
/* Uninstall plugin */
register_uninstall_hook( __FILE__, 'prflxtrflds_uninstall' );