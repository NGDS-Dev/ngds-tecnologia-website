/**
 * USOF Field: Link for Visual Composer
 * TODO:Move to usof_compatibility.js
 */
! function( $, undefined ) {
	"use strict";
	$( '.vc_ui-panel-window.vc_active .type_link' ).each( function() {
		var usofField = $( this ).usofField();
		if ( usofField instanceof $usof.field ) {
			usofField.trigger( 'beforeShow' );
		}
	} );
}( jQuery );
