<?php
/**
 * Fields Table
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for Fields Table
 */
if ( ! class_exists( 'Prflxtrflds_Fields_List' ) ) {
	class Prflxtrflds_Fields_List extends WP_List_Table {

		/**
		 * Display info
		 *
		 * @param bool $display_nav Display navigation.
		 */
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

		/**
		 * Get all columns
		 */
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

		/**
		 * Seting sortable collumns
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'field_name'  => array( 'field_name', true ),
				'field_order' => array( 'field_order', true ),
				'field_type'  => array( 'field_type_id', true ),
				'required'    => array( 'required', true ),
			);
			return $sortable_columns;
		}

		/**
		 * Get Bulk actions
		 */
		public function get_bulk_actions() {
			/** Adding bulk action */
			$actions = array(
				'delete_fields' => __( 'Delete Permanently', 'profile-extra-fields' ),
			);
			return $actions;
		}

		/**
		 * Override this function to delete nonce from options
		 *
		 * @param string $which Top or Bottom class.
		 */
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

		/**
		 * Bulk actions handler
		 */
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
									prflxtrflds_remove_field( absint( $id ) );
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

		/**
		 * Add views
		 */
		public function get_views() {
			/** Show links at the columns of table */
			global $wpdb;
			$views   = array();
			$current = ( ! empty( $_GET['role_id'] ) ) ? absint( $_GET['role_id'] ) : 'all';

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

		/**
		 * Extra Table Navigation
		 *
		 * @param string $which Top or Bottom class.
		 */
		public function extra_tablenav( $which ) {
			if ( 'columns' === $which ) {
				global $wpdb;
				$current = ( ! empty( $_GET['prflxtrflds_role_id'] ) ) ? absint( $_GET['prflxtrflds_role_id'] ) : 'all';
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

		/**
		 * Column Cb
		 *
		 * @param array $item Array with data.
		 */
		public function column_cb( $item ) {
			/** Customize displaying cb collumn */
			return sprintf(
				'<input type="checkbox" name="prflxtrflds_field_id[]" value="%1$s" />',
				$item['field_id']
			);
		}

		/**
		 * Column Field name
		 *
		 * @param array $item Array with data.
		 */
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

		/**
		 * Column Field type
		 *
		 * @param array $item Array with data.
		 */
		public function column_field_type( $item ) {
			$prflxtrflds_field_type_id = prflxtrflds_get_field_type_id();
			return sprintf(
				'%1$s',
				$prflxtrflds_field_type_id[ $item['field_type_id'] ]
			);
		}

		/**
		 * Column Required
		 *
		 * @param array $item Array with data.
		 */
		public function column_required( $item ) {
			return empty( $item['required'] ) ? __( 'No', 'profile-extra-fields' ) : __( 'Yes', 'profile-extra-fields' );
		}

		/**
		 * Column Showdefault
		 *
		 * @param array $item Array with data.
		 */
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

		/**
		 * Column Show always
		 *
		 * @param array $item Array with data.
		 */
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

		/**
		 * Column Roles
		 *
		 * @param array $item Array with data.
		 */
		public function column_roles( $item ) {
			/** Delete last comma */
			return sprintf( '%1$s', chop( $item['roles'], ', ' ) );
		}

		/**
		 * Prepare items
		 *
		 * @param string $where Where for sql.
		 */
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
				if ( is_plugin_active( 'bws-login-register-pro/bws-login-register-pro.php' ) || is_plugin_active( 'bws-login-register/bws-login-register.php' ) ) {
					array_push( $plugins_data, array( 'slug' => 'bws_login_register_form' ) );
				}
				$slugs        = array_column( $plugins_data, 'slug' );
				$where        = implode( "', '", $slugs );
				$not          = 'NOT';
			}

			$where_sql = 'AND ' . $table_fields_id . '.`field_id` ' . $not . ' IN (
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
					' WHERE ' . $searchrequest . ' ' . $where_sql . ' ' .
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

		/**
		 * Setting default view for column items
		 *
		 * @param array  $item        Array with data.
		 * @param string $column_name Column name.
		 */
		public function column_default( $item, $column_name ) {
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
