/**
 * USOF Fields: Radio & Imgradio
 */
! function( $, undefined ) {
	var _window = window,
		_undefined = undefined;

	if ( _window.$usof === _undefined ) {
		return;
	}

	/**
	 * NOTE: Do not set the field `input[type=radio]` name to disable links between the selection by the browser itself!
	 */
	$usof.field[ 'radio' ] = $usof.field[ 'imgradio' ] = {
		/**
		 * Field initialization
		 *
		 * @param {{}} options [optional]
		 */
		init: function() {
			var self = this;
			// Elements
			self.$radio = $( 'input[type=radio]', self.$row );
			// Bondable events
			self._events = {
				changeCurrentValue: self._changeCurrentValue.bind( self ),
				syncRadioButtons: self._syncRadioButtons.bind( self )
			};
			// Events
			self.$row
				.on( 'click', 'input[type=radio]', self._events.changeCurrentValue );

			// Radio button sync handler after state change
			if ( self.hasResponsive() ) {
				self.on( 'setResponsiveState', self._events.syncRadioButtons );
			}
		},

		/**
		 * This is a handler for changes to the selected buttons
		 *
		 * @private
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_changeCurrentValue: function( e ) {
			var self = this,
				value = ( e.target || {} ).value;
			if ( value === _undefined ) {
				value = self.getDefaultValue();
			}
			// Set current value
			self.setCurrentValue( value );
			self._syncRadioButtons();
		},

		/**
		 * Sync buttons to the current value
		 *
		 * @private
		 * @event handler
		 */
		_syncRadioButtons: function() {
			var self = this;
			self.$radio
				.removeAttr( 'checked' )
				.filter( '[value="' + self.getCurrentValue() + '"]' )
				.prop( 'checked', true );
		},

		/**
		 * Set the value
		 *
		 * @param {String} value The value to be selected
		 * @param {Boolean} quiet Sets in quiet mode without events
		 */
		setValue: function( value, quiet ) {
			var self = this;
			// Set parent value
			self.parentSetValue( '' + value );
			// Sync buttons to the current value
			self._syncRadioButtons();
		}
	};

}( jQuery );
