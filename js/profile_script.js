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

		if ( $( '.prflxtrflds_url' ).length > 0 && $.fn.inputmask ) {
			$.each( $( '.prflxtrflds_url' ), function() {
				regexp =  /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
				$( this ).change( function(){
					var value = $( this ).val();
					if ( regexp.test( value ) ){
						$( this ).removeAttr( 'style' );
						$( '#submit' ).prop('disabled', false);
						$( '#createusersub' ).prop('disabled', false);
					} else {
						$( this ).css( {'border' : 'solid red'} );
						$( '#createusersub' ).prop('disabled', true);
						$( '#submit' ).prop('disabled', true);
					}
				} );
			} );
		}
	} );
} )( jQuery );