/**
 * USOF Field: Switch for Visual Composer
 * TODO:Move to usof_compatibility.js
 */
! function( $, undefined ) {
	"use strict";
	$( '.vc_ui-panel-window.vc_active [data-name].type_switch' ).each( function() {
		 ( new $usof.field( this ) ).init( this )
	} );
}( jQuery );
