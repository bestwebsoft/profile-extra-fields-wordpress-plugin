<?php
/**
 * Userdata Table
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Prflxtrflds_Userdata_List' ) ) {
	class Prflxtrflds_Userdata_List extends WP_List_Table {

		/**
		 * Constract for class
		 *
		 * @param array $args Args for class.
		 */
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

		/**
		 * Get all columns
		 */
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

		/**
		 * Override this function to delete nonce from options
		 *
		 * @param string $which Top or Bottom class.
		 */
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

		/**
		 * Column Role
		 *
		 * @param array $item Array with data.
		 */
		public function column_role( $item ) {
			/** Translate user role */
			return sprintf( '%1$s', esc_attr( translate_user_role( ucfirst( $item['role'] ) ) ) );
		}

		/**
		 * Column Name
		 *
		 * @param array $item Array with data.
		 */
		public function column_name( $item ) {
			$actions = array(
				'edit_user' => '<span><a href="' . sprintf( 'user-edit.php?user_id=%1$s&amp;wp_http_referer=%2$s', $item['user_id'], rawurlencode( admin_url( 'admin.php?page=profile-extra-fields.php&tab-action=userdata' ) ) ) . '">' . __( 'Edit user', 'profile-extra-fields' ) . '</a></span>',
			);
			return sprintf( '%1$s %2$s', $item['name'] . '<div class="user_id">' . __( 'User ID', 'profile-extra-fields' ) . ': ' . $item['user_id'] . '</div>', $this->row_actions( $actions ) );
		}

		/**
		 * Seting sortable collumns
		 */
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

		/**
		 * Extra Table Navigation
		 *
		 * @param string $which Top or Bottom class.
		 */
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

		/**
		 * Prepare items
		 */
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

			foreach ( $all_users as $user ) {
				$userdata[ $i ]['name']      = $user->user_nicename;
				$userdata[ $i ]['role']      = implode( ', ', $user->roles );
				$userdata[ $i ]['user_id']   = $user->ID;
				$userdata[ $i ]['disp_name'] = $user->first_name . ' ' . $user->last_name;
				$userdata[ $i ]['email']     = $user->user_email;
				$userdata[ $i ]['posts']     = $post_counts[ $user->ID ];

				/** Get fields for current user */
				$filled_fields = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT `' . $wpdb->base_prefix . 'prflxtrflds_field_values`.`field_id`, `value_name` AS `user_value`, `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_type_id`
							FROM ' . $wpdb->base_prefix . 'prflxtrflds_user_field_data, ' . $wpdb->base_prefix . 'prflxtrflds_fields_id, `' . $wpdb->base_prefix . 'prflxtrflds_field_values`
							WHERE `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`.`user_value` = `' . $wpdb->base_prefix . 'prflxtrflds_field_values`.`value_id`
								AND `user_id` = %d
								AND `' . $wpdb->base_prefix . 'prflxtrflds_field_values`.`field_id`= `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_id`
								AND `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`.`field_id`= `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_id`
								AND `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_type_id` IN ( 3, 4, 5 )
						UNION
						SELECT `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`.`field_id`, `user_value`, `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_type_id`
							FROM ' . $wpdb->base_prefix . 'prflxtrflds_user_field_data, ' . $wpdb->base_prefix . 'prflxtrflds_fields_id
							WHERE `user_id` = %d
								AND `' . $wpdb->base_prefix . 'prflxtrflds_user_field_data`.`field_id`= `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_id`
								AND `' . $wpdb->base_prefix . 'prflxtrflds_fields_id`.`field_type_id` NOT IN ( 3, 4, 5 )
						',
						$user->ID,
						$user->ID
					),
					ARRAY_A
				);

				if ( ! empty( $filled_fields ) ) {
					foreach ( $filled_fields as $field ) {
						if ( isset( $userdata[ $i ][ $field['field_id'] ] ) ) {
							/** Add value name */
							$userdata[ $i ][ $field['field_id'] ] .= ', ' . wp_unslash( $field['user_value'] );
						} elseif ( '13' === $field['field_type_id'] ) {
							$countries = prflxtrflds_get_country();
							$userdata[ $i ][ $field['field_id'] ] = $countries[ $field['user_value'] ];
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
				$hidden_columns = get_user_option( 'managebws-panel_page_profile-extra-fieldsuserdatacolumnshidden' );
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
			$hidden                = get_user_option( 'managebws-panel_page_profile-extra-fieldsuserdatacolumnshidden' );
			$sortable              = $this->get_sortable_columns();
			$primary               = 'name';
			$this->_column_headers = $this->get_column_info();

			$this->items = array_slice( $userdata, ( ( $current_page - 1 ) * $users_per_page ), $users_per_page );
		}

		/**
		 * Setting default view for column items
		 *
		 * @param array  $item        Array with data.
		 * @param string $column_name Column name.
		 */
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
