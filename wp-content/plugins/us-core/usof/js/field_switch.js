/**
 * USOF Field: Switch
 */
! function( $, undefined ) {
	var _window = window,
		_undefined = undefined;

	if ( _window.$usof === _undefined ) {
		return;
	}

	$usof.field[ 'switch' ] = {
		/**
		 * Initializes the object.
		 */
		init: function() {
			var self = this;

			// Elements
			self.$hidden = $( 'input.wpb_vc_param_value', self.$row );

			// Events
			self.$input.on( 'change', function( e ) {
				var value = self.getValue();
				e.target.value = value;

				// Hidden field linked from WPBakery
				if ( self.isVCParamValue() ) {
					self.$hidden.val( value ).trigger( 'change' )
				}
				self.trigger( 'change', [ value ] );
			} );
		},

		/**
		 * Determines if a value is a param for Visual Composer
		 * Note: Method overridden because a hidden field is used for the current control
		 *
		 * @return {Boolean} True if vc parameter value, False otherwise.
		 */
		isVCParamValue: function() {
			return !! this.$hidden.length;
		},

		/**
		 * Value to number format
		 *
		 * @private
		 * @param {Mixed} value The value
		 * @return {Number} Returns the numerical value of the state, it is 1 or 0
		 */
		_toNumber: function( value ) {
			if ( $.isNumeric( value ) ) {
				value = parseInt( value );
			}
			return value ? 1 : 0;
		},

		/**
		 * Get the value
		 *
		 * @return {Mixed} The value 1 or 0
		 */
		getValue: function() {
			var self = this;
			return self._toNumber( self.$input.is( ':checked' ) );
		},

		/**
		 * Set the value
		 *
		 * @param {Mixed} value The value
		 * @param {Boolean} quiet The quiet
		 */
		setValue: function( value, quiet ) {
			var self = this;
			// Value to number format
			value = self._toNumber( value );
			// Set current value
			self.$input
				.val( value )
				.prop( 'checked', value );
			if ( ! quiet ) {
				self.trigger( 'change', [ value ] );
			}
		}
	};
}( jQuery );
