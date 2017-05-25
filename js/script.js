(function( $ ) {
	$( document ).ready( function() {
		/* Show trash icon */
		$( '.prflxtrflds-value-delete input' ).addClass( 'prflxtrflds-value-delete-check' );
		$( '.prflxtrflds-value-delete label' ).click(function() {
			/* clear value */
			$( this ).parent().parent().children( 'input.prflxtrflds-add-options-input' ).val( '' );
			/* hide field */
			$( this ).parent().parent().hide();

			if ( typeof bws_show_settings_notice == 'function' ) {
				bws_show_settings_notice();
			}
		});
		/* Add additional fields for checkbox, radio, select */
		$( '#prflxtrflds-add-field' ).click(function() {
			/* Clone previous input */
			var lastfield = $( '.prflxtrflds-drag-values' ).last().clone( true );
			/* remove hidden input */
			lastfield.children( 'input.hidden' ).remove();
			/* clear textfield */
			lastfield.children( 'input.prflxtrflds-add-options-input' ).val( '' );
			/* Insert field before button */
			lastfield.clone( true ).removeClass( 'hide-if-js' ).show().insertAfter( $( '.prflxtrflds-drag-values' ).last() );
		});
		/* Show fields for diffrent field type */
		$( '#prflxtrflds-select-type' ).on( 'change', function() {
			type_value = $( this ).val();
			$( '.prflxtrflds-fields-container, .prflxtrflds-pattern, .prflxtrflds-time-format, .prflxtrflds-date-format, .prflxtrflds-maxlength' ).hide();

			if ( type_value == '2' || type_value == '3' || type_value == '4'  ) {
				$( '.prflxtrflds-fields-container' ).show();
			} else if ( '9' == type_value ) {
				$( '.prflxtrflds-pattern' ).show();		
			} else {
				if ( '5' == type_value || '7' == type_value )
					$( '.prflxtrflds-date-format' ).show();

				if ( '6' == type_value || '7' == type_value )
					$( '.prflxtrflds-time-format' ).show();

				if ( '1' == type_value || '8' == type_value )
					$( '.prflxtrflds-maxlength' ).show();	
			}
		}).trigger( 'change' );

		$("input[name='prflxtrflds_date_format']").click(function(){
			if ( "prflxtrflds_date_format_custom_radio" != $( this ).attr( "id" ) )
				$( "input[name='prflxtrflds_date_format_custom']" ).val( $( this ).val() ).siblings( '.example' ).text( $( this ).parent( 'label' ).text() );
		});
		$("input[name='prflxtrflds_date_format_custom']").focus(function(){
			$( '#prflxtrflds_date_format_custom_radio' ).prop( 'checked', true );
		});

		$("input[name='prflxtrflds_time_format']").click(function(){
			if ( "prflxtrflds_time_format_custom_radio" != $( this ).attr("id") )
				$( "input[name='prflxtrflds_time_format_custom']" ).val( $( this ).val() ).siblings( '.example' ).text( $( this ).parent( 'label' ).text() );
		});
		$("input[name='prflxtrflds_time_format_custom']").focus(function(){
			$( '#prflxtrflds_time_format_custom_radio' ).prop( 'checked', true );
		});
		$("input[name='prflxtrflds_date_format_custom'], input[name='prflxtrflds_time_format_custom']").change( function() {
			var format = $( this );
			format.siblings( '.spinner' ).addClass( 'is-active' );
			$.post(ajaxurl, {
					action: 'prflxtrflds_date_format_custom' == format.attr( 'name' ) ? 'date_format' : 'time_format',
					date : format.val()
				}, function(d) { format.siblings( '.spinner' ).removeClass( 'is-active' ); format.siblings('.example').text(d); } );
		});

        /* Sortable table settings */
        if ( $.fn.sortable ) {
	        if ( $( '.prflxtrflds-wplisttable-fullwidth-sort-container .wp-list-table tbody tr' ).size() > 1 ) {
	            $( '.prflxtrflds-wplisttable-fullwidth-sort-container .wp-list-table tr' ).addClass( 'prflxtrflds-cursor-move' );
	            $( '.prflxtrflds-wplisttable-fullwidth-sort-container #the-list' ).sortable({
	                cursor: 'move',
	                placeholder: 'prflxtrflds-placeholder',
					stop: function( event, ui ) { 
						var order = [];
						$( '.prflxtrflds-wplisttable-fullwidth-sort-container #the-list tr th input' ).each( function( i, row ) {
							row = $( row );
							order[ i ] = row.attr( 'value' );
						});
						var fieldId = $( '#prflxtrflds-role-id option:selected' ).val();
						/* Save order with ajax */
						$.ajax({
							url: prflxtrflds_ajax.prflxtrflds_ajax_url,
							type: "POST",
							data: 'action=prflxtrflds_table_order&table_order=' + order.join( ', ' ) + '&prflxtrflds_ajax_nonce_field=' + prflxtrflds_ajax.prflxtrflds_nonce + '&field_id=' + fieldId,
							success: function( result ) {
							},
							error: function( request, status, error ) {
								console.log( error + request.status );
							}
						});
					}
	            });
	        }

			/* Drag n drop values list */		
            $( '.prflxtrflds-drag-values-container' ).sortable({
                itemSelector: 'div',
                /* Without container selector script return error */
                containerSelector: '.prflxtrflds-drag-values-container',
                handle: '.prflxtrflds-drag-field',
                placeholder: 'prflxtrflds-placeholder',
                stop: function( event, ui ) { 
					if ( typeof bws_show_settings_notice == 'function' ) {
						bws_show_settings_notice();
					}
				}
            });
        }

		/* Disable select if field unchecked after render page */
		$( '.prflxtrflds-available-fields').each( function() {
			if ( ! this.checked ) {
				$( this ).parent().next().children( 'select' ).prop( 'disabled', 'disabled' );
			}
		});
		/* Dynamic enable or disable select */
		$( '.prflxtrflds-available-fields' ).change( function() {
			if ( this.checked ) {
				$( this ).parent().next().children( 'select' ).prop( 'disabled', false );
			} else {
				$( this ).parent().next().children( 'select' ).prop( 'disabled', 'disabled' );
			}
		});

		/* Show 'select all' checkbox if js enabled */
		$( '#prflxtrflds-div-select-all' ).show();

		$( '#prflxtrflds-select-checkboxes td' ).each( function() {
			var wrap = $( this ),
				all_checkbox = wrap.find( '.prflxtrflds-checkboxes-select-all-in-roles' ),
				checkboxes = wrap.find( 'input[type="checkbox"]' ).not( all_checkbox ).filter( ':visible' );

			if ( checkboxes.length && checkboxes.filter( ':checked' ).length && checkboxes.length == checkboxes.filter( ':checked' ).length ) {
				all_checkbox.attr( 'checked', true );
			}
			
			if ( $( '#prflxtrflds-select-roles-editable, #prflxtrflds-select-roles-visibility' ).find( '.prflxtrflds-checkboxes-in-roles' ).not( ':disabled' ).length ) {
				all_checkbox.attr( 'disabled', false );
			}

			all_checkbox.on( 'click', function() {
				var $this = $( this ),
					is_available_checkbox = 'prflxtrflds-select-all' == $this.attr( 'id' ),
					is_checked = $this.is( ':checked' ),
					equals_in_column = $this.closest( 'td' ).find( '.prflxtrflds-checkboxes-in-roles' ).filter( ':visible' );
				
				if ( is_available_checkbox ) {
					if ( is_checked ) {
						$( '.prflxtrflds-checkboxes-in-roles' ).not( '#prflxtrflds-select-roles .prflxtrflds-checkboxes-in-roles' ).attr( 'disabled', false );
						$( '.prflxtrflds-checkboxes-select-all-in-roles' ).not( '#prflxtrflds-select-all' ).attr( 'disabled', false );
					} else {
						$( '.prflxtrflds-checkboxes-in-roles, .prflxtrflds-checkboxes-select-all-in-roles' )
							.not( '#prflxtrflds-select-roles .prflxtrflds-checkboxes-in-roles' ).not( $this )
							.attr( 'disabled', true ).attr( 'checked', false );
					}
				} else {
					if ( $this.is( ':disabled' ) )
						return false;
				}

				checkboxes.not( ':disabled' ).attr( 'checked', is_checked );
			});
		});

		$( '.prflxtrflds-checkboxes-in-roles' ).on( 'click', function() {
		 	var $this = $( this ),
		 		is_available_checkboxes = $this.attr( 'name' ) == 'prflxtrflds_roles[]',
		 		equals_in_column = $this.closest( 'td' ).find( '.prflxtrflds-checkboxes-in-roles' ).not( $this ).not( '.prflxtrflds-checkboxes-select-all-in-roles' ).filter( ':visible' ),
		 		current_val = $this.val(),
		 		all_checkbox = $this.closest( 'td' ).find( '.prflxtrflds-checkboxes-select-all-in-roles' );

		 	if ( is_available_checkboxes ) {
		 		var equals_in_row = $( '.prflxtrflds-checkboxes-in-roles' ).filter( function() {
					return this.value == current_val;
    			}).not( $this );

		 		if ( $this.is( ':checked' ) ) {
		 			equals_in_row.prop( 'disabled', false );
		 			$( '.prflxtrflds-checkboxes-select-all-in-roles' ).attr( 'disabled', false );
		 		} else {
		 			if ( equals_in_column.is( ':checked' ) ) {
		 				$( '#prflxtrflds-select-all-editable, #prflxtrflds-select-all-visibility' ).attr( 'checked', false );
		 			} else {
		 				$( '#prflxtrflds-select-all-editable, #prflxtrflds-select-all-visibility' ).attr( 'disabled', true );
		 			}
		 			equals_in_row.prop( 'disabled', true ).attr( 'checked', false );
		 		}
		 	} else {
		 		if ( $this.is( ':disabled' ) ) {
		 			return false;
		 		}
		 	}

 			if ( $this.is( ':checked' ) && equals_in_column.not( ':disabled' ).length == equals_in_column.filter( ':checked' ).length ) {
 				$this.closest( 'td' ).find( '.prflxtrflds-checkboxes-select-all-in-roles' ).attr( 'checked', true );
 			} else {
 				$this.closest( 'td' ).find( '.prflxtrflds-checkboxes-select-all-in-roles' ).attr( 'checked', false );
	 		}
		});
	});
})( jQuery );