(function( $ ) {
	$( document ).ready(function(){
		/* Show trash icon */
		$( '.prflxtrflds-value-delete input' ).addClass( 'prflxtrflds-value-delete-check' );
		$( '.prflxtrflds-value-delete label' ).click(function(){
			/* clear value */
			$( this ).parent().parent().children( 'input.prflxtrflds-add-options-input').val( '' );
			/* hide field */
			$( this ).parent().parent().hide();
		});
		/* Add additional fields for checkbox, radio, select */
		$( '#prflxtrflds-add-field' ).click(function(){
			/* Clone previous input */
			var lastfield = $( '.prflxtrflds-drag-values').last().clone( true );
			/* remove hidden input */
			lastfield.children( 'input.hidden' ).remove();
			/* clear textfield */
			lastfield.children( 'input.prflxtrflds-add-options-input' ).val( '' );
			/* Insert field before button */
			lastfield.clone( true ).insertBefore( $( this ).parent() );
		});
		/* Show fields if type field is not textfield */
		if ( $( '#prflxtrflds-select-type' ).val() == '1' ) {
			$( '.prflxtrflds-fields-container' ).hide();
		}
		/* Show or hide fields on change field type */
		$( '#prflxtrflds-select-type' ).on( 'change', function(){
			if ( $( this ).val() == '1' ) {
				$( '.prflxtrflds-fields-container' ).hide();
			} else {
				$( '.prflxtrflds-fields-container' ).show();
				if ( $( '.prflxtrflds-add-options-input' ).val() == "prflxtrflds_textfield" ) {
					/* Clear value if is textfield */
					$( '.prflxtrflds-add-options-input' ).val( '' );
				}
			}
		});
        /* Detect mobile device */
        var ismobile = false;
        if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
            ismobile = true;
        }

        if ( ismobile == false ) {
            /* Sortable table settings */
            if ( $( '.prflxtrflds-wplisttable-fullwidth-sort-container .wp-list-table tbody tr' ).size() > 1 ){
                $( '.prflxtrflds-wplisttable-fullwidth-sort-container .wp-list-table tr' ).addClass( 'prflxtrflds-cursor-move' );
                $( '.prflxtrflds-wplisttable-fullwidth-sort-container .wp-list-table' ).sortable({
                    containerSelector: 'table',
                    itemPath: '> tbody',
                    itemSelector: 'tr',
                    cursor: 'move',
                    draggedClass: 'prflxtrflds-dragged',
                    bodyClass: 'prflxtrflds-dragging',
                    placeholder: '<tr class="prflxtrflds-placeholder"/>',
                    onDragStart: function ( $item, container, _super ) {
                        oldIndex = $item.index();
                        $item.appendTo( $item.parent() );
                        _super( $item, container );
                    },
                    onDrop: function ( $item, container, _super ) {
                        var order = [];
                        $item.closest( 'tbody' ).find( 'tr th input' ).each( function( i, row ) {
                            row = $( row );
                            order[ i ] = row.attr( 'value' );
                        });
                        _super( $item, container );
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
                draggedClass: 'prflxtrflds-dragged',
                bodyClass: 'prflxtrflds-dragging',
                handle: '.prflxtrflds-drag-field',
                onDragStart: function ( $item, container, _super ) {
                    _super( $item, container );
                },
                onDrop: function ( $item, container, _super ) {
                    var $noticeblock = $( '#prflxtrflds-settings-notice' );
                    if ( $noticeblock.hasClass( 'hidden' ) ) {
                        $noticeblock.removeClass( 'hidden' );
                    }
                    _super( $item, container );
                }
            });
        } else {
            /* Hide notice if is mobile */
            $( '.prflxtrflds-hide-if-is-mobile' ).hide();
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
		/* Set checkbox "Select all" if all roles checked */
		if ( $( '#prflxtrflds-select-roles input[name="prflxtrflds_roles[]"]').filter( ':visible').size() == $( '#prflxtrflds-select-roles input[name="prflxtrflds_roles[]"]').filter( ':visible').filter( ':checked' ).size() ) {
			$( '#prflxtrflds-select-roles input#prflxtrflds-select-all' ).attr( 'checked', true );
		}
		$( '#prflxtrflds-select-roles input' ).bind( "change click select", function() {
			var	$select_all = $( '#prflxtrflds-select-roles input#prflxtrflds-select-all' ),
					$checkboxes = $( '#prflxtrflds-select-roles input[name="prflxtrflds_roles[]"]').filter( ':visible' ),
					checkboxes_size = $checkboxes.size(),
					checkboxes_selected_size = $checkboxes.filter( ':checked' ).size();
			if ( $( this ).attr( 'id' ) == $select_all.attr( 'id' ) ) {
				if ( $select_all.is( ':checked' ) ) {
					/* If 'select all' checkbox select on unselect, do this */
					$checkboxes.attr( 'checked', true );
				} else {
					$checkboxes.attr( 'checked', false );
				}
			} else {
				/* If all chexbox selected make checked 'select all' checkbox */
				if ( checkboxes_size == checkboxes_selected_size ) {
					$select_all.attr( 'checked', true );
				} else {
					$select_all.attr( 'checked', false );
				}
			}
		});
		/* Show notice if some changes */
		$( '[name^="prflxtrflds"], #prflxtrflds-select-all').bind( "change click select", function(){
			var $noticeblock = $( '#prflxtrflds-settings-notice' );
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				if ( $noticeblock.hasClass( 'hidden' ) ) {
					$noticeblock.removeClass( 'hidden' );
				}
                if ( $( '.prflxtrflds-settings-saved').length > 0 ) {
                    /* Hide 'data saved notice' */
                    $( '.prflxtrflds-settings-saved').hide();
                }
			}
		});
	});
})( jQuery );