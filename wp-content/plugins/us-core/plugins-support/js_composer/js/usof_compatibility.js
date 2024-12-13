/**
 * Compatibility and support for USOF in WPBakery Page Builder.
 */
! function( $, undefined ) {
	"use strict";

	// Private variables that are used only in the context of this function, it is necessary to optimize the code
	var _window = window;

	// Сheck the presence of the object
	_window.$usof = _window.$usof || {};

	// Functionality for $usof.field compatibility in WPBakery.
	if ( $.isFunction( $usof.field ) ) {
		/**
		 * Get the related field.
		 *
		 * @return {$usof.field|undefined} Returns the related field object, otherwise undefined.
		 */
		$usof.field.prototype.getRelatedField = function() {
			var self = this;

			var relatedOn = $ush.toString( self.relatedOn );
			if ( ! relatedOn ) {
				return; // undefined
			}

			// WPBakery, the fields in the group contain a prefix of the group name, so we adjust it for compatibility.
			// relatedOn: `{group_name}|{param_name}` to `{group_name}_{param_name}`
			var $field = self.$row.closest('.vc_shortcode-param'),
				$relatedField = $( '[data-name="' + ( relatedOn.replace( '|', '_' ) ) + '"]:first', $field.parent() ),
				usofField = $relatedField.data( 'usofField' );
			//  Set name without a group? relatedOn: {group_name}|{param_name}
			if ( relatedOn.indexOf( '|' ) > -1 ) {
				usofField.name = relatedOn.split( '|' )[ /* param_name */ 1] || relatedOn;
			}

			return usofField;
		};
	}

	// Init usof fields
	$( '.vc_ui-panel-window.vc_active [data-name]' ).each( function() {
		var $field = $( this );
		if ( $field.data( 'usofField' ) ) {
			return;
		}
		var usofField = $field.usofField();
		if ( usofField instanceof $usof.field ) {
			// Exclude `design_options` since initialization comes from USOF controls
			if ( usofField.$input.closest( '.type_design_options' ).length ) {
				return;
			}
			usofField.trigger( 'beforeShow' );
			usofField.setValue( usofField.$input.val() );

			// For related fields, we fire an event to apply the value from this field.
			// Note: In $usof.field['autocomplete'] loads the default items.
			if ( usofField.relatedOn ) {
				var relatedField = usofField.getRelatedField();
				if ( relatedField instanceof $usof.field ) {
					relatedField.trigger( 'change' );
				}
			}

			// The separate hidden field is necessary because Visual Composer
			// generates additional events that can loop into the USColorPicker.
			if ( usofField.type === 'color' ) {
				var $vcInput = $( 'input.wpb_vc_param_value:first', usofField.$row );
				usofField.on( 'change', function () {
					$vcInput
						.val( usofField.getValue() )
						.attr( 'name', usofField.name )
						.trigger( 'change' );
				} );
			}
		}
	} );

	/**
	 * This handler is required to initialize the USOF fields in each new element of the group
	 * Note: This handler is an internal callback mechanism that is not documented and is subject to change
	 *
	 * @private
	 * @type callback
	 *
	 * @param {Node} $newParam This is a new group param
	 * @param {String} action The action name
	 */
	_window._usVcParamGroupAfterAddParam = function( $newParam, action ) {
		if ( action == 'new' || action == 'clone' ) {
			// Finds all USOF fields.
			$( '.usof-form-row[data-name]', $newParam ).each( function( _, node ) {
				var $node = $( node );
				// If the field is already initialized, then skip it.
				if ( $node.data( 'usofField' ) instanceof $usof.field ) {
					return;
				}
				// Init of USOF fields that were added to the group.
				$node.usofField();
				$node.data( 'usofField' ).trigger( 'beforeShow' );
			} );
		}
	};

	// Finds all fields of Visual Composer type `param_group`
	$( '.vc_ui-panel-window.vc_active .wpb_el_type_param_group' )
		.each( function( _, node ) {
			// Slight delay to get data after initialization
			$ush.timeout( function() {
				var vcParamObject = $( node ).data( 'vcParamObject' ) || {};
				if ( $.isPlainObject( vcParamObject.options ) ) {
					$.extend( vcParamObject.options, {
						param: {
							callbacks: {
								after_add: '_usVcParamGroupAfterAddParam'
							}
						}
					} );
				}
			}, 800 );
		} );

}( jQuery );
