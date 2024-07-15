<?php
/**
 * Shortcode Table
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Prflxtrflds_Shortcode_List' ) ) {
	/**
	 * Class for Shortcode_List
	 */
	class Prflxtrflds_Shortcode_List extends WP_List_Table {

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

		/**
		 * Get all columns
		 */
		public function get_columns() {
			/** Setup column */
			return array(
				'field_name'  => __( 'Field Name', 'profile-extra-fields' ),
				'description' => __( 'Description', 'profile-extra-fields' ),
				'show'        => __( 'Show This Field', 'profile-extra-fields' ),
				'selected'    => __( 'Show Only If the Next Value is Selected', 'profile-extra-fields' ),
			);
		}

		/**
		 * Column Show
		 *
		 * @param array $item Array with data.
		 */
		public function column_show( $item ) {
			global $prflxtrflds_options;

			if ( is_array( $prflxtrflds_options['available_fields'] ) ) {
				$prflxtrflds_checked = checked( in_array( $item['field_id'], $prflxtrflds_options['available_fields'] ), 1, false );
			} else {
				$prflxtrflds_checked = '';
			}
			return sprintf( '<input type="checkbox" class="prflxtrflds-available-fields" name="prflxtrflds_options_available_fields[%1$d]" value="%1$d" %2$s /><input class="hidden" name="prflxtrflds_options_available_fields_hidden[%1$d]" value="%1$d">', $item['field_id'], $prflxtrflds_checked );
		}

		/**
		 * Column Selected
		 *
		 * @param array $item Array with data.
		 */
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

		/**
		 * Override this function to delete nonce from options
		 *
		 * @param string $which Top or Bottom class.
		 */
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

		/**
		 * Prepare items
		 */
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
			if ( 0 < count( $available_fields ) ) {
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
			$prflxtrflds_user_option = get_user_option( 'managebws-panel_page_profile-extra-fieldsshortcodecolumnshidden' );
			$hidden                  = ! empty( $prflxtrflds_user_option ) ? $prflxtrflds_user_option : array();
			$sortable                = array();
			$primary                 = $this->get_primary_column_name();
			$this->_column_headers   = array( $columns, $hidden, $sortable, $primary );
			$this->items             = array_slice( $available_fields, ( ( $current_page - 1 ) * $perpage ), $perpage );
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
