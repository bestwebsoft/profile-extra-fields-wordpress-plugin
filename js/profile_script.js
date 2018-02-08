( function( $ ) {
	$( document ).ready( function() {
		if ( $( '.prflxtrflds_datetimepicker:not([disabled])' ).length > 0 && $.fn.datetimepicker ) {
			$.each( $( '.prflxtrflds_datetimepicker' ), function() {
				var current_time_format = $( this ).parent().find( 'input[name="prflxtrflds_time_format"]' );
				var current_date_format = $( this ).parent().find( 'input[name="prflxtrflds_date_format"]' );
				if ( $( current_time_format ).length > 0 && $( current_date_format ).length > 0  ) {
					$( this ).datetimepicker( {
						format:$( current_date_format ).val() + ' ' + $( current_time_format ).val(),
						formatTime:$( current_time_format ).val(),
						formatDate:$( current_date_format ).val()
					} );
				} else if ( $( current_time_format ).length > 0 ) {
					$( this ).datetimepicker( {
						datepicker:false,
						format:$( current_time_format ).val(),
						formatTime:$( current_time_format ).val(),
					} );
				} else {
					$( this ).datetimepicker( {
						timepicker:false,
						format:$( current_date_format ).val(),
						formatDate:$( current_date_format ).val()
					} );
				}
			} );
		}

		if ( $( '.prflxtrflds_phone' ).length > 0 && $.fn.inputmask ) {
			$.each( $( '.prflxtrflds_phone' ), function() {
				var pattern = $( this ).next( 'input[type="hidden"]' ).val();
				if ( pattern ) {
					pattern = pattern.replace( /\*/g, "9" );
					$( this ).inputmask( { "mask": pattern } );
				}
			} );
		}

		if ( $( '.prflxtrflds_number' ).length > 0 ) {
			$.each( $( '.prflxtrflds_number' ), function() {
				$( this ).change( function() {
					var max = parseInt( $( this ).attr( 'max' ) );
					if ( $( this ).val() > max ) {
						$( this ).val( max );
					}
				} );
			} );
		}
	} );
} )( jQuery );