(function( $ ) {
	$( document ).ready( function() {
		if ( $( '.prflxtrflds_datetimepicker' ).length > 0 && $.fn.datetimepicker ) {
			$.each( $( '.prflxtrflds_datetimepicker' ), function() {
				var current_time_format = $( this ).parent().find( 'input[name="prflxtrflds_time_format"]' );
				var current_date_format = $( this ).parent().find( 'input[name="prflxtrflds_date_format"]' );
				if ( $( current_time_format ).length > 0 && $( current_date_format ).length > 0  ) {
					$( this ).datetimepicker({
						format:$( current_date_format ).val() + ' ' + $( current_time_format ).val(),
						formatTime:$( current_time_format ).val(),
						formatDate:$( current_date_format ).val()
					});
				} else if ( $( current_time_format ).length > 0 ) {
					$( this ).datetimepicker({
						datepicker:false,
						format:$( current_time_format ).val(),
						formatTime:$( current_time_format ).val(),	
					});
				} else {
					$( this ).datetimepicker({
						timepicker:false,
						format:$( current_date_format ).val(),
						formatDate:$( current_date_format ).val()
					});
				}
			});
		}
	});
})( jQuery );