/**
 * USOF Field: Design Options for Visual Composer
 * TODO:Move to usof_compatibility.js
 */
! function( $, undefined ) {
	"use strict";
	// Init inline css
	var $designOptions = $( '.vc_wrapper-param-type-us_design_options .type_design_options' );
	( new $usof.field( $designOptions ) ).init( $designOptions[ 0 ] );

	// Force value for WPBakery
	var usofField = $designOptions.data( 'usofField' );
	if ( usofField instanceof $usof.field ) {
		usofField.forceWPBValue()
	}

	// Run click event to initialize all group settings
	// TODO: fix new group params init in WPBakery so we get rid of this crutch
	if ( $( 'input.wpb_vc_param_value[name=offset]' ).length == 0 ) {
		$.each( $( '.vc_ui-tabs-line button' ).toArray().reverse(), function() {
			var $this = $( this ),
				targetTabID = $this.data( 'vc-ui-element-target' ),
				$targetTab = ( targetTabID !== undefined ) ? $( targetTabID ) : null;
			if ( targetTabID === '#vc_edit-form-tab-0' ) {
				$this.trigger( 'click' );
			} else if ( $targetTab !== null && $targetTab.length !== 0 && $targetTab.find( '.usof-design-options' ).length !== 0 ) {
				$this.trigger( 'click' );
			}
		} );
	}


}( jQuery );
