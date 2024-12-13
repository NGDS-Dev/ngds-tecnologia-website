/**
 * USOF Fields: Text
 * TODO:Move to usof_compatibility.js
 */
! function( $, undefined ) {
	"use strict";
	$( '.vc_ui-panel-window.vc_active .type_text' ).each( function() {
		( new $usof.field( this ) ).init( this );
	} );
}( jQuery );
