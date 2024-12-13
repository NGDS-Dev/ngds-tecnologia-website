/**
 * USOF Field: Radio for Visual Composer
 * TODO:Move to usof_compatibility.js
 */
! function( $, undefined ) {
	"use strict";
	$( '.vc_ui-panel-window.vc_active .type_radio' ).each( function() {
		var usofField = $( this ).usofField();
		if ( usofField instanceof $usof.field ) {
			// Exclude `design_options` since initialization comes from USOF controls
			if ( usofField.$input.closest( '.type_design_options' ).length ) {
				return;
			}
			usofField.trigger( 'beforeShow' );
			usofField.setValue( usofField.$input.val() );
		}
	} );
}( jQuery );
