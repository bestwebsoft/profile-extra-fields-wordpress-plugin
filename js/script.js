( function( $ ) {
	$( document ).ready(
		function() {
				/* Show trash icon */
				$( '.prflxtrflds-value-delete input' ).addClass( 'prflxtrflds-value-delete-check' );
				$( '.prflxtrflds-value-delete label' ).click(
					function() {
						/* clear value */
						$( this ).parent().parent().children( 'input.prflxtrflds-add-options-input' ).val( '' );
						/* hide field */
						$( this ).parent().parent().hide();

						if ( 'function' == typeof bws_show_settings_notice ) {
							bws_show_settings_notice();
						}
					}
				);
				/* Add additional fields for checkbox, radio, select */
				$( '#prflxtrflds-add-field' ).click(
					function() {
						/* Clone previous input */
						var lastfield = $( '.prflxtrflds-drag-values' ).last().clone( true );
						/* remove hidden input */
						lastfield.children( 'input.hidden' ).remove();
						/* clear textfield */
						lastfield.children( 'input.prflxtrflds-add-options-input' ).val( '' );
						/* Insert field before button */
						lastfield.clone( true ).removeClass( 'hide-if-js' ).show().insertAfter( $( '.prflxtrflds-drag-values' ).last() );
					}
				);
				/* Show fields for diffrent field type */
				$( '#prflxtrflds-select-type' ).on(
					'change',
					function() {
						type_value = $( this ).val();
						$( '.prflxtrflds-fields-container, .prflxtrflds-pattern, .prflxtrflds-time-format, .prflxtrflds-date-format, .prflxtrflds-maxlength, .prflxtrflds-rows, .prflxtrflds-cols, .prflxtrflds-selected-extensions' ).hide();
						$( '.prflxtrflds-fields-edit-table' ).show();

						if ( '3' == type_value || '4' == type_value || '5' == type_value ) {
							  $( '.prflxtrflds-fields-container' ).show();
						} else if ( '10' == type_value ) {
							 $( '.prflxtrflds-pattern' ).show();
						} else if ( '12' == type_value ) {
							$( '.prflxtrflds-selected-extensions' ).show();
						} else {
							if ( '6' == type_value || '8' == type_value ) {
								$( '.prflxtrflds-date-format' ).show();
							}
							if ( '7' == type_value || '8' == type_value ) {
								$( '.prflxtrflds-time-format' ).show();
							}
							if ( '1' == type_value || '9' == type_value || '11' == type_value ) {
								$( '.prflxtrflds-maxlength' ).show();
							}
							if ( '2' == type_value ) {
								$( '.prflxtrflds-rows, .prflxtrflds-cols, .prflxtrflds-maxlength' ).show();
							}
						}
					}
				).trigger( 'change' );

				$( "input[name='prflxtrflds_date_format']" ).click(
					function(){
						if ( "prflxtrflds_date_format_custom_radio" != $( this ).attr( "id" ) ) {
							$( "input[name='prflxtrflds_date_format_custom']" ).val( $( this ).val() ).siblings( '.example' ).text( $( this ).parent( 'label' ).text() );
						}
					}
				);
				$( "input[name='prflxtrflds_date_format_custom']" ).focus(
					function(){
						$( '#prflxtrflds_date_format_custom_radio' ).prop( 'checked', true );
					}
				);

				$( "input[name='prflxtrflds_time_format']" ).click(
					function(){
						if ( "prflxtrflds_time_format_custom_radio" != $( this ).attr( "id" ) ) {
							$( "input[name='prflxtrflds_time_format_custom']" ).val( $( this ).val() ).siblings( '.example' ).text( $( this ).parent( 'label' ).text() );
						}
					}
				);
				$( "input[name='prflxtrflds_time_format_custom']" ).focus(
					function(){
						$( '#prflxtrflds_time_format_custom_radio' ).prop( 'checked', true );
					}
				);
				$( "input[name='prflxtrflds_date_format_custom'], input[name='prflxtrflds_time_format_custom']" ).change(
					function() {
						var format = $( this );
						format.siblings( '.spinner' ).addClass( 'is-active' );
						$.post(
							ajaxurl,
							{
								action: 'prflxtrflds_date_format_custom' == format.attr( 'name' ) ? 'date_format' : 'time_format',
								date : format.val()
							},
							function( d ) { format.siblings( '.spinner' ).removeClass( 'is-active' ); format.siblings( '.example' ).text( d ); }
						);
					}
				);

				/* Sortable table settings */
			if ( $.fn.sortable ) {
				if ( $( '.prflxtrflds-wplisttable-fullwidth-sort-container .wp-list-table tbody tr' ).size() > 1 ) {
					$( '.prflxtrflds-wplisttable-fullwidth-sort-container .wp-list-table tr' ).addClass( 'prflxtrflds-cursor-move' );
					$( '.prflxtrflds-wplisttable-fullwidth-sort-container #the-list' ).sortable(
						{
							cursor: 'move',
							placeholder: 'prflxtrflds-placeholder',
							stop: function( event, ui ) {
								  var order = [];
								$( '.prflxtrflds-wplisttable-fullwidth-sort-container #the-list tr th input' ).each(
									function( i, row ) {
										row        = $( row );
										order[ i ] = row.attr( 'value' );
									}
								);
								  var fieldId = $( '#prflxtrflds-role-id option:selected' ).val();
								  /* Save order with ajax */
								$.ajax(
									{
										url: prflxtrflds_ajax.prflxtrflds_ajax_url,
										type: "POST",
										data: 'action=prflxtrflds_table_order&table_order=' + order.join( ', ' ) + '&prflxtrflds_ajax_nonce_field=' + prflxtrflds_ajax.prflxtrflds_nonce + '&field_id=' + fieldId,
										success: function( result ) {
										},
										error: function( request, status, error ) {
											console.log( error + request.status );
										}
									}
								);
							}
							}
					);
				}

				/* Drag n drop values list */
				$( '.prflxtrflds-drag-values-container' ).sortable(
					{
						itemSelector: 'div',
						 /* Without container selector script return error */
						containerSelector: '.prflxtrflds-drag-values-container',
						handle: '.prflxtrflds-drag-field',
						placeholder: 'prflxtrflds-placeholder',
						stop: function( event, ui ) {
							if ( 'function' == typeof bws_show_settings_notice ) {
								bws_show_settings_notice();
							}
						}
						 }
				);
			}

				/* Disable select if field unchecked after render page */
				$( '.prflxtrflds-available-fields' ).each(
					function() {
						if ( ! this.checked ) {
							$( this ).parent().next().children( 'select' ).prop( 'disabled', 'disabled' );
						}
					}
				);
				/* Dynamic enable or disable select */
				$( '.prflxtrflds-available-fields' ).change(
					function() {
						if ( this.checked ) {
							$( this ).parent().next().children( 'select' ).prop( 'disabled', false );
						} else {
							$( this ).parent().next().children( 'select' ).prop( 'disabled', 'disabled' );
						}
					}
				);

				$( '[id*=prflxtrflds-show-in-]' ).on(
					'click',
					function() {
						if ( $( this ).is( ':checked' ) ) {
							  $( '.' + this.id ).show();
						} else {
							 $( '.' + this.id ).hide().find( '[type="checkbox"]' ).prop( 'checked', false );
						}
					}
				);

				$( '[id*=prflxtrflds-show-in-]' ).each(
					function() {
						if ( $( this ).is( ':checked' ) ) {
							  $( '.' + this.id ).show();
						} else {
							 $( '.' + this.id ).hide();
						}
					}
				);

				/* Make inputs always send value */
				$( '.prflxtrflds-hidden-checkbox' ).each(
					function( i, e ) {
						e.disabled = $( '[type="checkbox"][name="' + e.name + '"]' ).prop( 'checked' );

						$( '[name="' + e.name + '"]' ).change(
							function( e ) {
								$( '[type="hidden"][name="' + e.target.name + '"]' ).prop( 'disabled', e.target.checked );
							}
						);
					}
				);

				/* Show 'select all' checkbox if js enabled */
				$( '#prflxtrflds-div-select-all' ).show();

				$( '.prflxtrflds-checkboxes-select-all-in-roles' ).on(
					'click',
					function() {
						var $parent        = $( this ).closest( 'td' ),
						$child_cb          = $parent.find( '.prflxtrflds-checkboxes-in-roles' ),
						$child_cb_disabled = $parent.find( '.prflxtrflds-checkboxes-in-roles' ).filter( ':disabled' );
						if ( $( this ).is( ':checked' ) ) {
							  $child_cb.prop( 'checked', true ).trigger( 'change' );
							  $child_cb_disabled.removeAttr( 'checked' ).trigger( 'change' );
						} else {
							 $child_cb.removeAttr( 'checked' ).trigger( 'change' );
						}
					}
				);

				$( '.prflxtrflds-checkboxes-in-roles' ).on(
					'change',
					function() {
						var $parent = $( this ).closest( 'td' ),
						$cb_all     = $parent.find( '.prflxtrflds-checkboxes-select-all-in-roles' ),

						$checkboxes         = $parent.find( '.prflxtrflds-checkboxes-in-roles' ).filter( ':enabled' ),
						$enabled_checkboxes = $checkboxes.filter( ':checked' );

						if ( $checkboxes.length > 0 && $checkboxes.length == $enabled_checkboxes.length ) {
							  $cb_all.prop( 'checked', true );
							  $cb_all.removeAttr( 'disabled' );
						} else {
							 $cb_all.removeAttr( 'checked' );
							 $cb_all.removeAttr( 'disabled' );
						}
					}
				).trigger( 'change' );

				$( '.prflxtrflds-checkboxes-available' ).on(
					'change',
					function() {
						var $parent = $( this ).closest( 'td' ),
						role_id     = $( this ).data( 'prflxtrflds-role-id' ),
						checkboxes  = $( '.prflxtrflds-checkboxes-editable[data-prflxtrflds-role-id="' + role_id + '"], .prflxtrflds-checkboxes-visible[data-prflxtrflds-role-id="' + role_id + '"]' );
						if ( $( this ).is( ':checked' ) ) {
							  checkboxes.removeAttr( 'checked' ).trigger( 'change' );
							  checkboxes.removeAttr( 'disabled' ).trigger( 'change' );
						} else {
							 checkboxes.prop( 'disabled', true ).trigger( 'change' );
							 checkboxes.removeAttr( 'checked' ).trigger( 'change' );
						}
					}
				);

				var table = $( 'table.toplevel_page_profile-extra-fields' );
			if ( table.length > 1 ) {
				table.wrap( '<div class="postbox prflxtrflds-table-wrap"><div class="inside"></div></div>' );
				table.parents( 'form' ).addClass( 'meta-box-sortables' );

				var table_wrap = $( '.prflxtrflds-table-wrap' );

				table_wrap.prepend( '<h2 class="hndle"></h2>' );

				table_wrap.each(
					function( i, e ) {
							var 	$this = $( e ),
							name          = $this.next( '.prflxtrflds-tables-name' ).val(),
							button        = '<button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: <label><input class="hide-if-no-js" id="plugins_checkbox" type="checkbox" value="prflxtrflds_tab_button">' + name + '</label></span><span class="toggle-indicator" aria-hidden="true"></span></button>';

							$this.prepend( button );
							$this.find( '.hndle' ).text( name );
					}
				);

					$( '.prflxtrflds-table-wrap .hndle, .prflxtrflds-table-wrap .handlediv' ).click(
						function( e ) {
							$( e.target ).parents( '.prflxtrflds-table-wrap' ).toggleClass( 'closed' );
						}
					);
			}
		}
	);
} )( jQuery );
