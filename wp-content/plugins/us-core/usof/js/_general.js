// Private variables that are used only in the context of this function, it is necessary to optimize the code
var _window = window,
	_document = document,
	_undefined = undefined;

// Check for is set availability objects
[ '$ush', '$usof' ].map( function( name ) {
	_window[ name ] = _window[ name ] || {};
} );

/**
 * Retrieve/set/erase dom modificator class <mod>_<value> for UpSolution CSS Framework
 *
 * @param {String} mod Modificator namespace
 * @param {String} [value] Value
 * @returns {String|jQuery}
 *
 * TODO: add support for multiple ([]) values
 */
jQuery.fn.usMod = function( mod, value ) {
	if ( this.length == 0 ) {
		return this;
	}
	// Remove class modificator
	if ( value === false ) {
		return this.each( function() {
			this.className = this.className.replace( new RegExp( '(^| )' + mod + '\_[a-zA-Z0-9\_\-]+( |$)' ), '$2' );
		} );
	}
	var pcre = new RegExp( '^.*?' + mod + '\_([a-zA-Z0-9\_\-]+).*?$' ),
		arr;
	// Retrieve modificator
	if ( $ush.isUndefined( value ) ) {
		return ( arr = pcre.exec( this.get( 0 ).className ) ) ? arr[ 1 ] : false;
	}
	// Set modificator
	else {
		var regexp = new RegExp( '(^| )' + mod + '\_[a-zA-Z0-9\_\-]+( |$)' );
		return this.each( function() {
			if ( this.className.match( regexp ) ) {
				this.className = this.className.replace( regexp, '$1' + mod + '_' + value + '$2' );
			} else {
				this.className += ' ' + mod + '_' + value;
			}
		} ).trigger( 'usof.' + mod, value );
	}
};

// Fields
! function( $ ) {

	if ( $ush.isUndefined( $usof.mixins ) ) {
		$usof.mixins = {};
	}

	// Prototype mixin for all classes working with events
	// TODO: Replace with $ush.mixinEvents
	$usof.mixins.Events = {
		/**
		 * Attach a handler to an event for the class instance
		 *
		 * @param {String} eventType A string containing event type, such as 'beforeShow' or 'change'
		 * @param {Function} handler A function to execute each time the event is triggered
		 */
		on: function( eventType, handler ) {
			var self = this;
			if ( $ush.isUndefined( self.$$events ) ) {
				self.$$events = {};
			}
			( eventType + '' ).split( ' ' ).map( function( _eventType ) {
				if ( $ush.isUndefined( self.$$events[ _eventType ] ) ) {
					self.$$events[ _eventType ] = [];
				}
				if( $.isFunction( handler ) ) {
					self.$$events[ _eventType ].push( handler );
				} else {
					console.error( 'Invalid handler:', [ _eventType, handler ] );
				}
			} );
			return self;
		},

		/**
		 * Remove a previously-attached event handler from the class instance
		 *
		 * @param {String} eventType A string containing event type, such as 'beforeShow' or 'change'
		 * @param {Function} [handler] The function that is to be no longer executed
		 * @chainable
		 */
		off: function( eventType, handler ) {
			var self = this;
			if (
				$ush.isUndefined( self.$$events )
				|| $ush.isUndefined( self.$$events[ eventType ] )
			) {
				return self;
			}
			if ( ! $ush.isUndefined( handler ) ) {
				var handlerPos = $.inArray( handler, self.$$events[ eventType ] );
				if ( handlerPos != - 1 ) {
					self.$$events[ eventType ].splice( handlerPos, 1 );
				}
			} else {
				self.$$events[ eventType ] = [];
			}
			return self;
		},

		/**
		 * @param {String} eventType
		 * @return {Boolean}
		 */
		has: function( eventType ) {
			var self = this;
			return ! $ush.isUndefined( self.$$events[ eventType ] ) && self.$$events[ eventType ].length;
		},

		/**
		 * Execute all handlers and behaviours attached to the class instance for the given event type
		 *
		 * @param {String} eventType A string containing event type, such as 'beforeShow' or 'change'
		 * @param {Array} extraParameters Additional parameters to pass along to the event handler
		 * @chainable
		 */
		trigger: function( eventType, extraParameters ) {
			var self = this;
			if (
				$ush.isUndefined( self.$$events )
				|| $ush.isUndefined( self.$$events[ eventType ] )
				|| self.$$events[ eventType ].length == 0
			) {
				return self;
			}
			var args = arguments,
				params = ( args.length > 2 || ! $.isArray( extraParameters ) )
				? Array.prototype.slice.call( args, 1 )
				: extraParameters;
			// First argument is the current class instance
			params.unshift( self );
			for ( var i = 0; i < self.$$events[ eventType ].length; i ++ ) {
				self.$$events[ eventType ][ i ].apply( self.$$events[ eventType ][ i ], params );
			}
			return self;
		}
	};

	if ( $ush.isUndefined( $usof._$$data ) ) {
		$usof._$$data = {};
	}

	/**
	 * Get USOF data by key
	 *
	 * @param {String} key Key to the data object
	 * @return {{}} Returns a data object on success, otherwise an empty simple object
	 */
	$usof.getData = function( key ) {
		var self = this;
		if ( typeof key !== 'string' ) {
			return {};
		}
		if ( ! $.isPlainObject( self._$$data[ key ] ) ) {
			try {
				self._$$data[ key ] = JSON.parse( self._$$data[ key ] || '{}' );
			} catch ( e ) {
				self._$$data[ key ] = {};
			}
		}
		return $ush.clone( self._$$data[ key ] || {} );
	};

	// TODO:Rename `self.$input` to `self.$$input`
	$usof.field = function( row, options ) {
		var self = this;

		// Elements
		self.$document = $( _document );
		self.$row = $( row );
		self.$responsive = $( '> .usof-form-row-responsive', self.$row );

		// Get field data
		var data = self.$row.data() || {};

		// Variables
		self.type = self.$row.usMod( 'type' );
		self.id = data.id;
		self.uniqid = $ush.uniqid();
		self.name = data.name;
		self.inited = !! data.inited;
		self.relatedOn = data.relatedOn;

		// Get current input by name
		self.$input = $( '[name="' + data.name + '"]:not(.js_hidden)', self.$row );

		if ( self.inited ) {
			return;
		}

		/**
		 * @var {{}} Boundable field events
		 */
		self.$$events = {
			beforeShow: [],
			afterShow: [],
			change: [],
			beforeHide: [],
			afterHide: []
		};

		// Overloading selected functions, moving parent functions to "parent" namespace: init => parentInit
		if ( ! $ush.isUndefined( $usof.field[ self.type ] ) ) {
			for ( var fn in $usof.field[ self.type ] ) {
				if (
					! $usof.field[ self.type ].hasOwnProperty( fn )
					|| fn.substr( 0, 2 ) === '_$' // Deny access via parent for private methods
				) {
					continue;
				}
				if ( ! $ush.isUndefined( self[ fn ] ) ) {
					var parentFn = 'parent' + fn.charAt( 0 ).toUpperCase() + fn.slice( 1 );
					self[ parentFn ] = self[ fn ];
				}
				self[ fn ] = $usof.field[ self.type ][ fn ];
			}
		}

		// Events
		self.$document // Forwarding events through document
			.on( 'usb.syncResponsiveState', self._usbSyncResponsiveState.bind( self ) );

		// Save current object to row element
		self.$row.data( 'usofField', self );

		// Init on first show
		var initEvent = function() {
			self.init( options );
			self.inited = true;
			self.$row.data( 'inited', self.inited );
			self.off( 'beforeShow', initEvent );
			// Remember the default value
			self._std = data.hasOwnProperty( 'std' )
				? data.std // NOTE: Used for now only for `type=select`
				: self.getCurrentValue();
			// If responsive mode support is enabled for the field, then we initialize the functionality
			self.initResponsive();
		};
		self.on( 'beforeShow', initEvent );
	};

	/**
	 * The main functionality of the field
	 * Note: When developing or updating a field, pay attention to the basic methods!
	 */
	$.extend( $usof.field.prototype, $usof.mixins.Events, {

		init: function() {
			var self = this;
			if ( $ush.isUndefined( self._events ) ) {
				self._events = {};
			}
			self._events.change = function() {
				self.trigger( 'change', [ self.getValue() ] );
			};
			self.$input.on( 'change', self._events.change );
			return self;
		},

		/**
		 * Determines if edit live.
		 *
		 * @return {Boolean} True if edit live, False otherwise.
		 */
		isEditLive: function() {
			var self = this;
			return !! (
				// The wrapper is always present in the context of the builder panel
				self.$row.closest( '.usb-panel-fieldset' ).length
				// Builder panel context (childrens are dynamic)
				|| self.$row.parents( '.usb-panel-body' ).length
			);
		},

		/**
		 * Initializes the necessary functionality for responsive mode.
		 */
		initResponsive: function() {
			var self = this;
			if ( ! self.hasResponsive() ) {
				return;
			}

			// Elements
			self.$switchResponsive = $( '.usof-switch-responsive:first', self.$row );
			self.$responsiveButtons = $( '[data-responsive-state]', self.$responsive );

			// Variables
			self._currentState = 'default';
			self._states = [ 'default' ];

			// Get responsive states
			if ( self.$responsive.is( '[onclick]' ) ) {
				self._states = self.$responsive[ 0 ].onclick() || self._states;
				self.$responsive.removeAttr( 'onclick' );
			}

			// Events
			self.$switchResponsive
				.on( 'click', self._$switchResponsive.bind( self ) );

			self.$responsive
				.on( 'click', '[data-responsive-state]', self._$selectResponsiveState.bind( self ) );
		},

		/**
		 * Determine if there is a responsive mode.
		 *
		 * @return {Boolean} True has responsive, False otherwise.
		 */
		hasResponsive: function() {
			return !! this.$responsive.length;
		},

		/**
		 * Determine if responsive mode is enabled.
		 *
		 * @return {Boolean} True if responsive, False otherwise.
		 */
		isResponsive: function() {
			var self = this;
			return self.hasResponsive() && self.$row.hasClass( 'responsive' );
		},

		/**
		 * Determine responsive value format or not.
		 *
		 * @param {Mixed} value The checked value.
		 * @return {Boolean} True if responsive value, False otherwise.
		 */
		isResponsiveValue: function( value ) {
			var self = this;
			if ( value ) {
				if ( self.isObjectValue( value ) ) {
					value = $ush.toPlainObject( value );
				}
				if ( $.isPlainObject( value ) ) {
					for ( var i in self._states ) {
						if ( value.hasOwnProperty( self._states[ i ] ) ) {
							return true;
						}
					}
				}
			}
			return false;
		},

		/**
		 * Determines whether the specified value is object value.
		 *
		 * @param {String} value The value.
		 * @return {String} True if the specified value is object value, False otherwise.
		 * TODO:Remove here and in the `./field_typography_options.js`.
		 */
		isObjectValue: function( value ) {
			return value && ( '' + value ).indexOf( $ush.rawurlencode( '{' ) ) === 0;
		},

		/**
		 * Determines whether the specified state is valid state.
		 *
		 * @param {Mixed} state The state.
		 * @return {Boolean} True if the specified state is valid state, False otherwise.
		 */
		isValidState: function( state ) {
			return state && ( this._states || [] ).indexOf( $ush.toString( state ) ) !== -1;
		},

		/**
		 * Determines if a value is a param for Visual Composer.
		 *
		 * @return {Boolean}True if vc parameter value, False otherwise.
		 */
		isVCParamValue: function() {
			return this.$input.hasClass( 'wpb_vc_param_value' );
		},

		/**
		 * Determines whether the specified value is dynamic value.
		 *
		 * @param {String} value The value
		 * @return {Boolean} True if the specified value is dynamic value, False otherwise.
		 */
		isDynamicValue: function( value ) {
			return value && /^\{\{(.*)\}\}$/.test( $ush.toString( value ).trim() );
		},

		/**
		 * Get parent object.
		 *
		 * @return {Mixed} Returns the parent object if successful, otherwise undefined.
		 */
		getParent: $.noop,

		/**
		 * Get the related field.
		 * Note: The method is overridden to '/plugins-support/js_composer/js/usof_compatibility.js'
		 *
		 * @return {$usof.field|undefined} Returns the related field object, otherwise undefined.
		 */
		getRelatedField: function() {
			var self = this,
				parent = self.getParent();
			if (
				! $ush.isUndefined( self.relatedOn )
				&& parent instanceof $usof.GroupParams
			) {
				return ( parent.fields || {} )[ $ush.toString( self.relatedOn ) ];
			}
			return _undefined;
		},

		/**
		 * Get the current state.
		 *
		 * @return {String} The current state.
		 */
		getCurrentState: function() {
			var self = this;
			if ( ! self.isValidState( self._currentState ) ) {
				self._currentState = 'default';
			}
			return self._currentState;
		},

		/**
		 * Get the default value.
		 * Note: This is the default value from the config,
		 * not the default value from the responsive value.
		 *
		 * @return {String} The default value.
		 */
		getDefaultValue: function() {
			var self = this;
			return ! $ush.isUndefined( self._std ) ? self._std : '';
		},

		/**
		 * Get the value by state name.
		 *
		 * @param {String} state The state name.
		 * @param {String} value The value.
		 * @return {String} Returns values by state name or default.
		 */
		getValueByState: function( state, value ) {
			var self = this;
			if ( self.isResponsiveValue( value ) ) {
				if ( ! self.isValidState( state ) ) {
					state = 'default';
				}
				if ( ! $.isPlainObject( value ) ) {
					value = $ush.toPlainObject( value );
				}
				if ( value.hasOwnProperty( state ) ) {
					return value[ state ];
				}
			}
			return self.getDefaultValue();
		},

		/**
		 * Set the value by state.
		 *
		 * @param {String} state The state.
		 * @param {String} input The input value.
		 * @param {String} value The value.
		 * @return {String} Returns the value from the updated data for the state.
		 */
		setValueByState: function( state, input, value ) {
			var self = this;
			if ( ! self.isValidState( state ) ) {
				return '';
			}
			if ( self.isResponsiveValue( value ) ) {
				value = $ush.toPlainObject( value );
			} else {
				value = {};
			}
			// Set or update values for a state
			value[ state ] = input;
			return $ush.toString( value );
		},

		/**
		 * Get the current value, taking into account the state if used.
		 *
		 * @return {Mixed} The current value.
		 */
		getCurrentValue: function() {
			var self = this,
				value = self.getValue();
			// Get the current value in responsive state
			if ( self.isResponsiveValue( value ) ) {
				value = self.getValueByState( self._currentState, value );
			}
			// Get value if it is string object
			if ( self.isObjectValue( value ) ) {
				value = $ush.toPlainObject( value );
			}
			return value;
		},

		/**
		 * Set the current value, taking into account the state if used.
		 *
		 * @param {Mixed} value The value.
		 * @param {Boolean} quiet The quiet.
		 */
		setCurrentValue: function( value, quiet ) {
			var self = this;
			// Set the current value in responsive state
			if ( self.isResponsive() ) {
				value = self.setValueByState( self._currentState, value, self.getValue() );
			}
			// Set value if it is plain object
			if ( $.isPlainObject( value ) ) {
				value = $ush.toString( value );
			}
			// Set general value
			// Note: setValue should not be used here since it is intended to be set from outside!
			self.$input.val( value );
			self.trigger( 'change', value );
			// Run events on a hidden field for WPBakery as it is tied to it
			if ( self.isVCParamValue() && self.$input.is(':hidden') ) {
				self.$input.trigger( 'change' );
			}
		},

		/**
		 * Get the value.
		 *
		 * @return {String} The value.
		 */
		getValue: function() {
			return this.$input.val();
		},

		/**
		 * Set the value.
		 *
		 * @param {Mixed} value The value.
		 * @param {Boolean} quiet The quiet.
		 */
		setValue: function( value, quiet ) {
			var self = this;
			// Responsive mode switch by value
			if (
				! self.isResponsive()
				&& self.isResponsiveValue( value )
			) {
				self.$row.addClass( 'responsive' );
			}
			self.$input.val( value );
			if ( ! quiet ) {
				self.trigger( 'change', [ value ] );
			}
			// For fields that are bound to the values of the Visual Composer,
			// we will fire an event for the correct execution of the Visual Composer logic
			if ( self.isVCParamValue() ) {
				self.$input.trigger( 'change' );
			}
		},

		/**
		 * This is the install handler `responsiveState` of builder.
		 * Note: This event is global and can be overridden as needed.
		 *
		 * @event handler
		 * @param {Event} _ The Event interface represents an event which takes place in the DOM.
		 * @param {string} state The device type.
		 */
		_usbSyncResponsiveState: function( _, state ) {
			var self = this, state = state || 'default';
			if (
				! self.isResponsive()
				|| ! self.isValidState( state )
			) {
				return;
			}
			self._$setResponsiveState( state );
		},

		/**
		 * Set responsive state.
		 *
		 * @param {String} state.
		 */
		_$setResponsiveState: function( state ) {
			var self = this;
			if ( ! self.hasResponsive() ) {
				return;
			}
			// Set current state
			if ( ! self.isValidState( state ) ) {
				state = 'default';
			}

			// Enable current state button
			self.$responsiveButtons
				.removeClass( 'active' )
				.filter( '[data-responsive-state="'+ state +'"]' )
				.addClass( 'active' );

			// Save current state
			self._currentState = state;

			// Send a signal about a responsive state change
			self.trigger( 'setResponsiveState', state );
		},

		/**
		 * Responsive mode switch.
		 *
		 * @event handler
		 */
		_$switchResponsive: function() {
			var self = this;
			if ( ! self.hasResponsive() ) {
				return;
			}
			// Define next mode
			var nextMode = ! self.isResponsive();

			// Set or unset responsive mode
			self.$row
				.toggleClass( 'responsive', nextMode );

			var value = self.getCurrentValue();
			if ( nextMode ) {
				var responsiveValue = {};
				self._states.map( function( state ) {
					responsiveValue[ state ] = value;
				} );
				value = $ush.toString( responsiveValue );

			} else {
				// Set default state
				self._$setResponsiveState( 'default' );

				// Set value if it is plain object
				if ( $.isPlainObject( value ) ) {
					value = $ush.toString( value );
				}
			}

			// Update the value according to the set mode
			self.setValue( value );
		},

		/**
		 * Handler for selecting a responsive state on click of a button.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_$selectResponsiveState: function( e ) {
			var self = this;
			if ( ! self.isResponsive() ) {
				return;
			}
			// Get selected state
			var state = $( e.target ).data( 'responsive-state' ) || self._currentState;

			// Set responsive state
			self._$setResponsiveState( state );

			// Forward events to other handlers (for example, in the builder)
			self.trigger( 'syncResponsiveState', state );
			self.$document.trigger( 'field.syncResponsiveState', state );
		}
	} );

	/**
	 * Field initialization
	 *
	 * @param {{}} options object
	 * @returns {$usof.field}
	 */
	$.fn.usofField = function( options ) {
		return new $usof.field( this, options );
	};

	/**
	 * USOF Group
	 * TODO: Need to refactor and get rid of dependencies, the object must provide an API!
	 */
	$usof.Group = function( row, options ) {
		this.init( row, options );
	};

	$.extend( $usof.Group.prototype, $usof.mixins.Events, {

		init: function( elm, options ) {

			// Elements
			this.$field = $( elm );
			this.$btnAddGroup = $( '.usof-form-group-add', this.$field );
			this.$groupPrototype = $( '.usof-form-group-prototype', this.$field );

			// Variables
			this.groupName = this.$field.data( 'name' );
			this.params = [];
			this.groupParams = [];

			/**
			 * @private
			 * @var {{}} Bondable events
			 */
			this._events = {
				changeGroupParams: this._changeGroupParams.bind( this ),
			};

			this.isBuilder = !! this.$field.parents( '.us-bld-window' ).length; // This is the builder located in the admin panel
			this.isEditLive = !! this.$field.parents( '.usb-panel-fieldset' ).length;
			this.isSortable = this.$field.hasClass( 'sortable' );
			this.isAccordion = this.$field.hasClass( 'type_accordion' );
			this.isForButtons = this.$field.hasClass( 'preview_button' );

			var $translations = this.$field.find( '.usof-form-group-translations' );
			this.groupTranslations = $translations.length ? ( $translations[ 0 ].onclick() || {} ) : {};

			if ( this.isBuilder ) {
				this.$parentElementForm = this.$field.closest( '.usof-form' );
				this.elementName = this.$parentElementForm.usMod( 'for' );
				this.$builderWindow = this.$field.closest( '.us-bld-window' );
			} else {
				this.$parentSection = this.$field.closest( '.usof-section' );
				$( '.usof-form-group-item', this.$field ).each( function( i, groupParams ) {
					var $groupParams = $( groupParams );
					if ( $groupParams.closest( '.usof-form-group-prototype' ).length ) {
						return;
					}
					this.groupParams.push( new $usof.GroupParams( $groupParams ) );
				}.bind( this ) );
			}

			// The value is a string otherwise it will be an object
			this.hasStringValue = !! this.$field.closest( '.usb-panel-fieldset' ).length;

			// Remember the default value
			this._std = this.getValue();

			// Events
			this.$btnAddGroup
				.off( 'click' ) // TODO: Fix double initialization for USBuilder
				.on( 'click', this.addGroup.bind( this, _undefined ) );
			this.$field
				.on( 'change', function() {
					this.trigger( 'change', this );
				}.bind( this ) )
				.on( 'click', '.ui-icon_duplicate', this.duplicateGroup.bind( this ) )
				.on( 'click', '.usof-form-group-item-controls > .ui-icon_delete', function( event ) {
					event.stopPropagation();
					var $btn = $( event.target ),
						$group = $btn.closest( '.usof-form-group-item' );
					this.groupDel( $group );
				}.bind( this ) );

			if ( this.isAccordion ) {
				this.$sections = this.$field.find( '.usof-form-group-item' );
				this.$field
					.on( 'click', '.usof-form-group-item-title', function( event ) {
						this.$sections = this.$field
							.find( '.usof-form-group-item' );
						var $parentSection = $( event.target )
							.closest( '.usof-form-group-item' );
						if ( $parentSection.hasClass( 'active' ) ) {
							$parentSection
								.removeClass( 'active' )
								.children( '.usof-form-group-item-content' )
								.slideUp();
						} else {
							$parentSection
								.addClass( 'active' )
								.children( '.usof-form-group-item-content' )
								.slideDown();
						}
					}.bind( this ) );
			}

			if ( this.isSortable ) {
				// Elements
				this.$body = $( _document.body );
				this.$window = $( _window );
				this.$dragshadow = $( '<div class="us-bld-editor-dragshadow"></div>' );
				// Events
				this.$field
					.on( 'dragstart', function( e ) { e.preventDefault() })
					.on( 'mousedown', '.ui-icon_move', this._dragStart.bind( this ) );
				// Event handlers
				$.extend( this._events, {
					_maybeDragMove: this._maybeDragMove.bind( this ),
					_dragMove: this._dragMove.bind( this ),
					_dragEnd: this._dragEnd.bind( this )
				} );
			}
		},

		_hasClass: function( node, cls ) {
			return ( ' ' + node.className + ' ' ).indexOf( ' ' + cls + ' ' ) > - 1;
		},

		_isShadow: function( node ) {
			return this._hasClass( node, 'usof-form-group-dragshadow' );
		},

		_isSortable: function( node ) {
			return this._hasClass( node, 'usof-form-group-item' );
		},

		/**
		 * Handler of field changes in a parameter group
		 *
		 * @private
		 * @event handler
		 * @param {$usof.field} usofField
		 * @param {Mixed} value The usofField value
		 */
		_changeGroupParams: function( usofField, value ) {
			this.trigger( 'change', this );
		},

		/**
		 * Reinit params
		 *
		 * @private
		 */
		_reInitParams: function() {
			var self = this;
			self.groupParams = [];
			$( '.usof-form-group-item', self.$field ).each( function( i, groupParams ) {
				var $groupParams = $( groupParams );
				if( $groupParams.closest( '.usof-form-group-prototype' ).length ) return;
				var groupParams = $groupParams.data( 'usofGroupParams' );
				for ( var k in groupParams.fields ) {
					groupParams.fields[ k ].on( 'change', self._events.changeGroupParams );
				}
				self.groupParams.push( groupParams );
			} );

			if ( ! self.isBuilder ) {
				if ( $.isEmptyObject( $usof.instance.valuesChanged ) ) {
					clearTimeout( $usof.instance.saveStateTimer );
					$usof.instance.$saveControl.usMod( 'status', 'notsaved' );
				}
				var value = self.getValue();
				$usof.instance.valuesChanged[ self.groupName ] = value;
				self.$field.trigger( 'change', value );
			}
		},

		/**
		 * Get the default field value
		 *
		 * @return {Mixed} The default value
		 */
		getDefaultValue: function() {
			var self = this;
			return ! $ush.isUndefined( self._std ) ? self._std : '';
		},

		/**
		 * Get the current value.
		 *
		 * @return {[]} Returns the current value given the selected response state, if any.
		 */
		getCurrentValue: function() {
			var self = this, result = [];
			for ( var i in self.groupParams ) {
				result.push( self.groupParams[ i ].getCurrentValues() );
			}
			if ( self.hasStringValue ) {
				try {
					result = $ush.toString( result );
				} catch ( err ) {
					console.error( result, err );
					result = '';
				}
			}
			return result;
		},

		/**
		 * Set the value
		 *
		 * @param {String|[]} value The value
		 */
		setValue: function( value ) {
			var self = this;
			// If the value came as a string, then we will try to convert it into an object
			if ( typeof value === 'string' && self.hasStringValue ) {
				try {
					value = JSON.parse( $ush.rawurldecode( value ) || '[]' );
				} catch ( err ) {
					console.error( value, err );
					value = [];
				}
			}
			self.groupParams = [];
			$( '.usof-form-group-item', self.$field ).each( function( i, groupParams ) {
				var $groupParams = $( groupParams );
				if ( ! $groupParams.parent().hasClass( 'usof-form-group-prototype' ) ) {
					$groupParams.remove();
				}
			} );
			$.each( value, function( index, paramsValues ) {
				var groupPrototype = self.$groupPrototype.html();
				if ( self.$btnAddGroup.length ) {
					self.$btnAddGroup.before( groupPrototype );
				} else {
					self.$field.append( groupPrototype );
				}
				var $groupParams = $( '.usof-form-group-item', self.$field ).last();
				var groupParams = new $usof.GroupParams( $groupParams );
				groupParams.setValues( paramsValues, 1 );
				for ( var k in groupParams.fields ) {
					if ( ! groupParams.fields.hasOwnProperty( k ) ) {
						continue;
					}
					groupParams.fields[ k ].trigger( 'change' );
					break;
				}
			} );

			self._reInitParams();
		},

		/**
		 * Get the value
		 *
		 * @return {String|[]} The value
		 */
		getValue: function() {
			var self = this,
				result = [];
			$.each( self.groupParams, function( i, groupParams ) {
				result.push( groupParams.getValues() );
			} );

			if ( self.hasStringValue ) {
				if ( result.length ) {
					try {
						result = $ush.toString( result );
					} catch ( err ) {
						console.error( result, err );
						result = self.getDefaultValue();
					}
				} else {
					result = self.getDefaultValue();
				}
			}
			return result;
		},

		/**
		 * Add group
		 *
		 * @param {Number} index Add a group after the specified index
		 * @return {{}} $usof.GroupParams
		 */
		addGroup: function( index ) {
			this.$btnAddGroup.addClass( 'adding' );
			var $groupPrototype = $( this.$groupPrototype.html() );
			if ( this.isForButtons && ! $ush.isUndefined( index ) ) {
				this.$btnAddGroup
					.closest( '.usof-form-group' )
					.find( ' > .usof-form-group-item:eq(' + parseInt( index ) + ')' )
					.after( $groupPrototype );
			} else {
				this.$btnAddGroup.before( $groupPrototype );
			}
			var groupParams = new $usof.GroupParams( $groupPrototype );
			for ( var k in groupParams.fields ) {
				groupParams.fields[ k ].on( 'change', this._events.changeGroupParams );
			}
			if ( this.isForButtons && index !== _undefined ) {
				this.groupParams.splice( index + 1, 0, groupParams );
			} else {
				this.groupParams.push( groupParams )
			}

			if ( ! this.isBuilder ) {
				if ( $.isEmptyObject( $usof.instance.valuesChanged ) ) {
					clearTimeout( $usof.instance.saveStateTimer );
					$usof.instance.$saveControl.usMod( 'status', 'notsaved' );
				}
				var value = this.getValue();
				$usof.instance.valuesChanged[ this.groupName ] = value;
				this.$field
					.trigger( 'change', value );
			}
			// TODO: Need to get rid of the crutch this.isForButtons
			if ( this.isForButtons ) {
				var newIndex = this.groupParams.length,
					newId = 1,
					newIndexIsUnique;
				for ( var i in this.groupParams ) {
					newId = Math.max( ( parseInt( this.groupParams[ i ].fields.id.getValue() ) || 0 ) + 1, newId );
				}
				do {
					newIndexIsUnique = true;
					for ( var i in this.groupParams ) {
						if ( this.groupParams[ i ].fields.name.getValue() == this.groupTranslations.style + ' ' + newIndex ) {
							newIndex ++;
							newIndexIsUnique = false;
							break;
						}
					}
				} while ( ! newIndexIsUnique );
				groupParams.fields.name.setValue( this.groupTranslations.style + ' ' + newIndex );
				groupParams.fields.id.setValue( newId );

				// Set main class for the button
				groupParams.$container.find( '.usof-btn-class-main' ).text( 'us-btn-style_' + newId );
			}
			// If the group is running in a EditLive context then set the title for accordion
			// NOTE: This is a forced decision that will be fixed when refactoring the code!
			if ( this.isEditLive ) {
				groupParams._setTitleForAccordion();
			}
			this.$btnAddGroup.removeClass( 'adding' );
			return groupParams;
		},

		/**
		 * Duplicate group
		 *
		 * @param {Event} e
		 */
		duplicateGroup: function( e ) {
			var $target = $( e.currentTarget ),
				$group = $target.closest( '.usof-form-group-item' ),
				index = $group.index() - 1;
			if ( this.groupParams.hasOwnProperty( index ) ) {
				var $item = this.groupParams[ index ],
					values = $item.getValues(),
					number = 0;
				values.name = $.trim( values.name.replace( /\s?\(.*\)$/, '' ) );
				// Create new group name
				for ( var i in this.groupParams ) {
					var name = this.groupParams[ i ].getValue( 'name' ) || '',
						copyPattern = new RegExp( values.name + '\\s?\\((\\d+)*', 'm' );
					var numMatches = name.match( copyPattern );
					if ( numMatches !== null ) {
						number = Math.max( number, parseInt( numMatches[ 1 ] || 1 ) );
					}
				}
				values.name += ' (' + ( ++ number ) + ')';
				var newGroup = this.addGroup( index );
				newGroup.setValues( $.extend( values, {
					id: newGroup.getValue( 'id' )
				} ) );
			}
		},

		groupDel: function( $group ) {
			$group.addClass( 'deleting' );
			$group.remove();
			this._reInitParams();
		},

		// Drag'n'drop functions
		_dragStart: function( event ) {
			event.stopPropagation();
			this.$draggedElm = $( event.target ).closest( '.usof-form-group-item' );
			this.detached = false;
			this._updateBlindSpot( event );
			this.elmPointerOffset = [parseInt( event.pageX ), parseInt( event.pageY )];
			this.$body.on( 'mousemove', this._events._maybeDragMove );
			this.$window.on( 'mouseup', this._events._dragEnd );
		},

		_updateBlindSpot: function( event ) {
			this.blindSpot = [event.pageX, event.pageY];
		},

		_isInBlindSpot: function( event ) {
			return Math.abs( event.pageX - this.blindSpot[ 0 ] ) <= 20 && Math.abs( event.pageY - this.blindSpot[ 1 ] ) <= 20;
		},

		_maybeDragMove: function( event ) {
			event.stopPropagation();
			if ( this._isInBlindSpot( event ) ) {
				return;
			}
			this.$body.off( 'mousemove', this._events._maybeDragMove );
			this._detach();
			this.$body.on( 'mousemove', this._events._dragMove );
		},

		_detach: function( event ) {
			var offset = this.$draggedElm.offset();
			this.elmPointerOffset[ 0 ] -= offset.left;
			this.elmPointerOffset[ 1 ] -= offset.top;
			this.$draggedElm.find( '.usof-form-group-item-title' ).hide();
			if ( ! this.isAccordion || this.$draggedElm.hasClass( 'active' ) ) {
				this.$draggedElm.find( '.usof-form-group-item-content' ).hide();
			}
			this.$dragshadow.css( {
				width: this.$draggedElm.outerWidth()
			} ).insertBefore( this.$draggedElm );
			this.$draggedElm.addClass( 'dragged' ).css( {
				position: 'absolute',
				'pointer-events': 'none',
				zIndex: 10000,
				width: this.$draggedElm.width(),
				height: this.$draggedElm.height()
			} ).css( offset ).appendTo( this.$body );
			if ( this.isBuilder ) {
				this.$builderWindow.addClass( 'dragged' );
			}
			this.detached = true;
		},

		_dragMove: function( event ) {
			event.stopPropagation();
			this.$draggedElm.css( {
				left: event.pageX - this.elmPointerOffset[ 0 ],
				top: event.pageY - this.elmPointerOffset[ 1 ]
			} );
			if ( this._isInBlindSpot( event ) ) {
				return;
			}
			var elm = event.target;
			// Checking two levels up
			for ( var level = 0; level <= 2; level ++, elm = elm.parentNode ) {
				if ( this._isShadow( elm ) ) {
					return;
				}

				if ( this._isSortable( elm ) ) {

					// Dropping element before or after sortables based on their relative position in DOM
					var nextElm = elm.previousSibling,
						shadowAtLeft = false;
					while ( nextElm ) {
						if ( nextElm == this.$dragshadow[ 0 ] ) {
							shadowAtLeft = true;
							break;
						}
						nextElm = nextElm.previousSibling;
					}
					this.$dragshadow[ shadowAtLeft ? 'insertAfter' : 'insertBefore' ]( elm );
					this._dragDrop( event );
					break;
				}
			}
		},

		/**
		 * Complete drop
		 *
		 * @private
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_dragDrop: function( e ) {
			this._updateBlindSpot( e );
		},

		_dragEnd: function( event ) {
			this.$body.off( 'mousemove', this._events._maybeDragMove ).off( 'mousemove', this._events._dragMove );
			this.$window.off( 'mouseup', this._events._dragEnd );
			if ( this.detached ) {
				this.$draggedElm.removeClass( 'dragged' ).removeAttr( 'style' ).insertBefore( this.$dragshadow );
				this.$dragshadow.detach();
				if ( this.isBuilder ) {
					this.$builderWindow.removeClass( 'dragged' );
				}
				this.$draggedElm.find( '.usof-form-group-item-title' ).show();
				if ( ! this.isAccordion || this.$draggedElm.hasClass( 'active' ) ) {
					this.$draggedElm.find( '.usof-form-group-item-content' ).show();
				}
				this._reInitParams();
			}
		}
	} );

	/**
	 * Group initialization
	 */
	$.fn.usofGroup = function( options ) {
		return new $usof.Group( this, options );
	};

}( jQuery );

// Drag & Drop
;( function( $ ) {
	/**
	 * Drag and Drop Plugin
	 * Triggers: init, dragdrop, dragstart, dragend, drop, over, leave
	 *
	 * @param {String|{}} container
	 * @param {{}} options
	 * @return {$usof.dragDrop} Returns the Drag & Drop instance
	 */
	$usof.dragDrop = function( container, options ) {
		// Variables
		this._defaults = {
			// The selector that will move
			itemSelector: '.usof-draggable-selector',
			// CSS classes for displaying states
			css: {
				moving: 'usof-dragdrop-moving',
				active: 'usof-dragdrop-active',
				over: 'usof-dragdrop-over'
			},
		};
		this._name = '$usof.dragDrop'; // the export plugin name
		this.options = $.extend( {}, this._defaults, options || {} );

		// CSS Classes for the plugin that reflect the actions within the plugin
		this.css = this.options.css;

		// Elements
		this.$container = $( container );

		// Plugin initialization
		this.init.call( this );
	};

	// Extend prototype with events and new methods
	$.extend( $usof.dragDrop.prototype, $usof.mixins.Events, {
		/**
		 * Initializes the object
		 *
		 * @return self
		 */
		init: function() {
			var itemSelector = this.options.itemSelector;
			if ( ! itemSelector ) {
				return;
			}

			this.$container.data( 'usofDragDrop', this );
			this.trigger( 'init', this );

			this.$container
				.addClass( 'usof-dragdrop' )

				// Begin handler's from item selector
				.on( 'mouseup', itemSelector, function( e ) {
					this.$container
						.removeClass( this.css.moving )
						.find( '> [draggable]' )
						.removeAttr( 'draggable' );
					$( '> .' + this.css.active, this.$container )
						.removeClass( this.css.active );
					this.trigger( 'dragdrop', e, this );
					return true;
				}.bind( this ) )

				.on( 'dragenter', itemSelector, function( e ) {
					e.stopPropagation();
					e.preventDefault();
					this.trigger( 'dragdrop', e, this );
					return true;
				}.bind( this ) )

				.on( 'drop', itemSelector, function( e ) {
					var targetId = e.originalEvent.dataTransfer.getData( 'Text' ),
						$el = $( '> [usof-target-id="' + targetId + '"]', this.$container ),
						$target = $( e.currentTarget );
					$el.removeAttr( 'usof-target-id' );

					$target
						.before( $el );
					$( '> .' + this.css.active, this.$container )
						.removeClass( this.css.active );
					$( '> .' + this.css.over, this.$container )
						.removeClass( this.css.over );
					e.stopPropagation();
					this.trigger( 'drop', e, this )
						.trigger( 'dragdrop', e, this );
					return false;
				}.bind( this ) )

				.on( 'dragover', itemSelector, function( e ) {
					e.stopPropagation();
					e.preventDefault();
					$( e.currentTarget === e.target ? e.target : e.currentTarget )
						.addClass( this.css.over );
					this.trigger( 'over', e, this )
						.trigger( 'dragdrop', e, this );
					return true;
				}.bind( this ) )

				.on( 'dragleave', itemSelector, function( e ) {
					e.stopPropagation();
					e.preventDefault();
					$( e.target ).removeClass( this.css.over );
					this.trigger( 'leave', e, this )
						.trigger( 'dragdrop', e, this );
					return true;
				}.bind( this ) )

				// Begin handler's from container
				.on( 'mousedown', itemSelector, function( e ) {
					this.$container
						.addClass( this.css.moving );
					var $target = $( this._getTarget( e ) );
					$target.addClass( this.css.active );
					if ( ! $target.is( '[draggable="false"]' ) && ! $target.is( 'input' ) ) {
						$target.attr( 'draggable', true );
					}
					this.trigger( 'dragdrop', e, this );
					return true;
				}.bind( this ) )

				.on( 'mouseup', itemSelector, function( e ) {
					$( '> .' + this.css.active, this.$container )
						.removeClass( this.css.active );
					this.trigger( 'dragdrop', e, this );
					return true;
				}.bind( this ) )

				.on( 'dragstart', itemSelector, function( e ) {
					e.stopPropagation();
					var $target = $( this._getTarget( e ) ),
						targetId = $ush.uniqid(); // generate unique id for transfer text
					$target.attr( 'usof-target-id', targetId );
					e.originalEvent.dataTransfer.effectAllowed = 'move';
					e.originalEvent.dataTransfer.setData( 'Text', targetId );
					e.originalEvent.dataTransfer.setDragImage( $target.get( 0 ), e.offsetX, e.offsetY );
					this.trigger( 'dragstart', e, this )
						.trigger( 'dragdrop', e, this );
					return true;
				}.bind( this ) )

				.on( 'dragend', function( e ) {
					e.stopPropagation();
					this.$container
						.removeClass( this.css.moving )
						.find( '> [draggable]' )
						.removeAttr( 'draggable' );
					this.trigger( 'dragend', e, this )
						.trigger( 'dragdrop', e, this );
					return true;
				}.bind( this ) );

			return this;
		},

		/**
		 * Get the node to be moved
		 *
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 * @return {Node}
		 */
		_getTarget: function( e ) {
			var $target = $( e.target ),
				itemSelector = ( this.options.itemSelector || '' ).replace( '>', '' ).trim();
			if ( itemSelector && !! $target.parent( itemSelector ).length ) {
				$target = $target.parent( itemSelector );
			}
			return $target.get( 0 );
		}
	} );

	/**
	 * @param {{}} options The options
	 * @return {jQuery} jQuery object
	 */
	$.fn.usofDragDrop = function( options ) {
		return this.each( function() {
			if ( ! $.data( this, 'usofDragDrop' ) ) {
				$.data( this, 'usofDragDrop', new $usof.dragDrop( this, options ) );
			}
		} );
	};

} )( jQuery );

/**
 * USOF Core
 */
;! function( $ ) {

	$usof.ajaxUrl = $( '.usof-container' ).data( 'ajaxurl' ) || /* wp variable */ ajaxurl;

	// Prototype mixin for all classes working with fields
	if ( $ush.isUndefined( $usof.mixins ) ) {
		$usof.mixins = {};
	}

	// TODO: Need to refactor and get rid of dependencies, the object must provide an API!
	$usof.mixins.Fieldset = {
		/**
		 * Initialize fields inside of a container
		 *
		 * @param {jQuery} $container
		 */
		initFields: function( $container ) {
			var self = this;

			// Check variables
			[ '$fields', 'fields', 'groups', 'showIf', 'showIfDeps' ].map( function( prop ) {
				if ( ! $.isPlainObject( self[ prop ] ) ) {
					self[ prop ] = {};
				}
			} );

			var groupElms = [];
			$( '.usof-form-row, .usof-form-wrapper, .usof-form-group', $container ).each( function( _, node ) {
				var $field = $( node ),
					name = $field.data( 'name' ),
					isRow = $field.hasClass( 'usof-form-row' ),
					isGroup = $field.hasClass( 'usof-form-group' ),
					isInGroup = $field.parents( '.usof-form-group' ).length,
					$showIf = $field.find(
						( isRow || isGroup )
							? '> .usof-form-row-showif'
							: '> .usof-form-wrapper-content > .usof-form-wrapper-showif'
					);

				// If the element is in the prototype, then we will ignore the init
				if ( $field.closest( '.usof-form-group-prototype' ).length ) {
					return;
				}

				// Exclude fields for `design_options` as they have their own group
				if (
					isRow
					&& $field.closest( '.usof-design-options' ).length
					&& ! $container.is('[data-responsive-state-content]')
				) {
					return;
				}

				self.$fields[ name ] = $field;
				if ( $showIf.length > 0 ) {
					self.showIf[ name ] = $showIf[ 0 ].onclick() || [];
					// Writing dependencies
					var showIfVars = self._getShowIfVariables( self.showIf[ name ] );
					for ( var i = 0; i < showIfVars.length; i ++ ) {
						if ( $ush.isUndefined( self.showIfDeps[ showIfVars[ i ] ] ) ) {
							self.showIfDeps[ showIfVars[ i ] ] = [];
						}
						self.showIfDeps[ showIfVars[ i ] ].push( name );
					}
				}
				if ( isRow && ( ! isInGroup || self.isGroupParams ) ) {
					self.fields[ name ] = $field.usofField();
					// Method of get parent object
					self.fields[ name ].getParent = function() {
						return self;
					};
				} else if ( isGroup ) {
					self.groups[ name ] = $field.usofGroup();
				}
			} );

			for ( var fieldName in self.showIfDeps ) {
				if (
					! self.showIfDeps.hasOwnProperty( fieldName )
					|| $ush.isUndefined( self.fields[ fieldName ] )
				) {
					continue;
				}
				self.fields[ fieldName ].on( 'change', function( field ) {
					self.updateVisibility( field.name );
				} );
				// Update displayed fields on initialization
				if ( !! self.isGroupParams ) {
					self.updateVisibility( fieldName, /* isAnimated */false, /* isCurrentShown */self.getCurrentShown( fieldName ) );
				}
			}

			// Get default values for fields
			if ( $ush.isUndefined( self._defaultValues ) ) {
				self._defaultValues = self.getValues();
			}
		},

		/**
		 * Show/Hide the field based on its showIf condition
		 *
		 * @param {String} fieldName The field name
		 * @param {Boolean} isAnimated Indicates if animated
		 * @param {Boolean} isCurrentShown Indicates if parent
		 */
		updateVisibility: function( fieldName, isAnimated, isCurrentShown ) {
			var self = this;
			if ( ! fieldName || ! self.showIfDeps[ fieldName ] ) return;

			// TODO: Clear code
			if ( $ush.isUndefined( isAnimated ) ) {
				isAnimated = true;
			}
			if ( $ush.isUndefined( isCurrentShown ) ) {
				isCurrentShown = true;
			}

			/**
			 * Get the display conditions for the previous field, if it exists
			 *
			 * @type {Boolean|undefined}
			 */
			var isPrevShown = self.$fields[ fieldName ].data( 'isShown' );

			self.showIfDeps[ fieldName ].map( function( depFieldName ) {
				var field = self.fields[ depFieldName ] || self.groups[ depFieldName ],
					$field = self.$fields[ depFieldName ],
					isShown = self.getCurrentShown( depFieldName ),
					shouldBeShown = self.executeShowIf( self.showIf[ depFieldName ], self.getValue.bind( self ) );

				// Check visible
				if ( ( ! shouldBeShown && isShown ) || ! isCurrentShown ) {
					isShown = false;
				} else if ( shouldBeShown && ! isShown ) {
					isShown = true;
				}

				// Check the display of previous fields in chains, if any
				if ( ! $ush.isUndefined( isPrevShown ) ) {
					isShown = isPrevShown && isShown;
				}

				// Set current visibility
				$field
					.stop( true, false )
					.data( 'isShown', isShown );

				if ( isShown ) {
					self.fireFieldEvent( $field, 'beforeShow' );
					// TODO: Add css animations is enabled isAnimated
					$field.show();
					self.fireFieldEvent( $field, 'afterShow' );
					if ( field instanceof $usof.field ) {
						field.trigger( 'change', [ field.getValue() ] );
					}
				} else {
					self.fireFieldEvent( $field, 'beforeHide' );
					// TODO: Add css animations is enabled isAnimated
					$field.hide();
					self.fireFieldEvent( $field, 'afterHide' );
					if ( field instanceof $usof.Group ) {
						field.setValue( field.getDefaultValue() );
					}
				}

				// Set visibility for tree dependencies
				if ( !! self.showIfDeps[ depFieldName ] ) {
					self.updateVisibility( depFieldName, isAnimated, isShown );
				}
			} );
		},

		/**
		 * Get a shown state
		 *
		 * @param {String} fieldName The field name
		 * @return {Boolean} True if the specified field identifier is shown, False otherwise
		 */
		getCurrentShown: function( fieldName ) {
			var self = this;
			if ( ! fieldName || ! self.$fields[ fieldName ] ) return true;
			var $field = self.$fields[ fieldName ],
				isShown = $field.data( 'isShow' );
			if ( $ush.isUndefined( isShown ) ) {
				isShown = $field.css( 'display' ) !== 'none';
			}
			return !! isShown;
		},

		/**
		 * Get all field names that affect the given 'show_if' condition
		 *
		 * @param {[]} condition
		 * @returns {[]}
		 */
		_getShowIfVariables: function( condition ) {
			var self = this;
			if ( ! $.isArray( condition ) || condition.length < 3 ) {
				return [];
			} else if ( $.inArray( condition[ 1 ].toLowerCase(), [ 'and', 'or' ] ) != - 1 ) {
				// Complex or / and statement
				var vars = self._getShowIfVariables( condition[ 0 ] ),
					index = 2;
				while ( ! $ush.isUndefined( condition[ index ] ) ) {
					vars = vars.concat( self._getShowIfVariables( condition[ index ] ) );
					index = index + 2;
				}
				return vars;
			} else {
				return [ condition[ 0 ] ];
			}
		},

		/**
		 * Execute 'show_if' condition
		 *
		 * @param {[]} condition
		 * @param {Function} getValue Function to get the needed value
		 * @returns {Boolean} Should be shown?
		 */
		executeShowIf: function( condition, getValue ) {
			var self = this,
				result = true;
			if ( ! $.isArray( condition ) || condition.length < 3 ) {
				return result;
			} else if ( $.inArray( condition[ 1 ].toLowerCase(), [ 'and', 'or' ] ) != - 1 ) {
				// Complex or / and statement
				result = self.executeShowIf( condition[ 0 ], getValue );
				var index = 2;
				while ( ! $ush.isUndefined( condition[ index ] ) ) {
					condition[ index - 1 ] = condition[ index - 1 ].toLowerCase();
					if ( condition[ index - 1 ] == 'and' ) {
						result = ( result && self.executeShowIf( condition[ index ], getValue ) );
					} else if ( condition[ index - 1 ] == 'or' ) {
						result = ( result || self.executeShowIf( condition[ index ], getValue ) );
					}
					index = index + 2;
				}
			} else {
				var value = getValue( condition[ 0 ] );
				if ( $ush.isUndefined( value ) ) {
					return true;
				}
				if ( condition[ 1 ] == '=' ) {
					if ( $.isArray( condition[ 2 ] ) ) {
						result = ( $.inArray( value, condition[ 2 ] ) != - 1 );
					} else {
						result = ( value == condition[ 2 ] );
					}
				} else if ( condition[ 1 ] == '!=' ) {
					if ( $.isArray( condition[ 2 ] ) ) {
						result = ( $.inArray( value, condition[ 2 ] ) == - 1 );
					} else {
						result = ( value != condition[ 2 ] );
					}
				} else if ( condition[ 1 ] == '<=' ) {
					result = ( value <= condition[ 2 ] );
				} else if ( condition[ 1 ] == '<' ) {
					result = ( value < condition[ 2 ] );
				} else if ( condition[ 1 ] == '>' ) {
					result = ( value > condition[ 2 ] );
				} else if ( condition[ 1 ] == '>=' ) {
					result = ( value >= condition[ 2 ] );
				} else if (  condition[ 1 ] == 'str_contains' ) {
					result = ( '' + value ).indexOf( '' + condition[ 2 ] ) > -1;
				} else {
					result = true;
				}
			}
			return result;
		},

		/**
		 * Find all the fields within $container and fire a certain event there
		 *
		 * @param {jQuery} $container
		 * @param {String} trigger
		 */
		fireFieldEvent: function( $container, trigger ) {
			if ( ! $container.hasClass( 'usof-form-row' ) ) {
				$( '.usof-form-row', $container ).each( function( _, row ) {
					var $row = $( row ),
						isShown = $row.data( 'isShown' );
					if ( $ush.isUndefined( isShown ) ) {
						isShown = $row.css( 'display' ) != 'none';
					}
					// The block is not actually shown or hidden in this case
					// Note: Fields with `class="hidden"` will not be initialized!
					if ( ! isShown && [ 'beforeShow', 'afterShow', 'beforeHide', 'afterHide' ].indexOf( trigger ) !== -1 ) {
						return;
					}
					if ( $ush.isUndefined( $row.data( 'usofField' ) ) ) {
						return;
					}
					$row.data( 'usofField' ).trigger( trigger );
				} );

			} else if ( $container.data( 'usofField' ) instanceof $usof.field ) {
				$container.data( 'usofField' ).trigger( trigger );
			}
		},

		/**
		 * Get the value
		 *
		 * @param {String} id The id
		 * @return {Mixed} The value
		 */
		getValue: function( id ) {
			var self = this;
			if ( $ush.isUndefined( self.fields[ id ] ) ) {
				return _undefined;
			}
			return self.fields[ id ].getValue();
		},

		/**
		 * Set some particular field value
		 *
		 * @param {String} id
		 * @param {String} value
		 * @param {Boolean} quiet Don't fire onchange events
		 */
		setValue: function( id, value, quiet ) {
			var self = this;
			if ( $ush.isUndefined( self.fields[ id ] ) ) {
				return;
			}
			var shouldFireShow = ! self.fields[ id ].inited;
			if ( shouldFireShow ) {
				self.fields[ id ].trigger( 'beforeShow' );
				self.fields[ id ].trigger( 'afterShow' );
			}
			self.fields[ id ].setValue( value, quiet );
			if ( shouldFireShow ) {
				self.fields[ id ].trigger( 'beforeHide' );
				self.fields[ id ].trigger( 'afterHide' );
			}
		},

		/**
		 * Get the values
		 *
		 * @return {Mixed} The values
		 */
		getValues: function() {
			var self = this, values = {};
			// Regular values
			for ( var fieldId in self.fields ) {
				if ( ! self.fields.hasOwnProperty( fieldId ) ) {
					continue;
				}
				values[ fieldId ] = self.getValue( fieldId );
			}
			// Groups
			for ( var groupId in self.groups ) {
				values[ groupId ] = self.groups[ groupId ].getValue();
			}
			return values;
		},

		/**
		 * Set the values
		 *
		 * @param {{}} values
		 * @param {Boolean} quiet Don't fire onchange events, just change the interface
		 */
		setValues: function( values, quiet ) {
			var self = this;
			// Regular values
			for ( fieldId in self.fields ) {
				if ( values.hasOwnProperty( fieldId ) ) {
					var currentValue = values[ fieldId ];
					self.setValue( fieldId, currentValue, quiet );
					if ( ! quiet ) {
						self.fields[ fieldId ].trigger( 'change', [ currentValue ] );
					}

					// Restoring the default value
				} else if( self._defaultValues.hasOwnProperty( fieldId ) ) {
					var defaultValue = self._defaultValues[ fieldId ];
					self.setValue( fieldId, defaultValue, quiet );
				}
			}
			// Groups
			for ( var groupId in self.groups ) {
				self.groups[ groupId ].setValue( values[ groupId ] );
			}
			if ( quiet ) {
				// Update fields visibility anyway
				for ( var fieldName in self.showIfDeps ) {
					if (
						! self.showIfDeps.hasOwnProperty( fieldName )
						|| $ush.isUndefined( self.fields[ fieldName ] )
					) {
						continue;
					}
					self.updateVisibility( fieldName, /* isAnimated */false );
				}
			}
		},

		/**
		 * Get the current values.
		 *
		 * @return {{}} Returns the current value given the selected response state, if any.
		 */
		getCurrentValues: function() {
			var self = this, result = {};
			for ( var name in self.fields ) {
				result[ name ] = self.fields[ name ].getCurrentValue();
			}
			for ( var name in self.groups ) {
				result[ name ] = self.groups[ name ].getCurrentValue();
			}
			return result;
		},

		/**
		 * JavaScript representation of us_prepare_icon_tag helper function + removal of wrong symbols
		 *
		 * @param {String} iconClass
		 * @returns {String}
		 */
		prepareIconTag: function( iconValue ) {
			iconValue = iconValue.trim().split( '|' );
			if ( iconValue.length != 2 ) {
				return '';
			}
			var iconTag = '';
			iconValue[ 0 ] = iconValue[ 0 ].toLowerCase();
			if ( iconValue[ 0 ] == 'material' ) {
				iconTag = '<i class="material-icons">' + iconValue[ 1 ] + '</i>';
			} else {
				if ( iconValue[ 1 ].substr( 0, 3 ) == 'fa-' ) {
					iconTag = '<i class="' + iconValue[ 0 ] + ' ' + iconValue[ 1 ] + '"></i>';
				} else {
					iconTag = '<i class="' + iconValue[ 0 ] + ' fa-' + iconValue[ 1 ] + '"></i>';
				}
			}

			return iconTag
		}
	};

	// TODO: Need to refactor and get rid of dependencies, the object must provide an API!
	$usof.GroupParams = function( container ) {

		this.$container = $( container );
		this.$group = this.$container.closest( '.usof-form-group' );
		this.group = this.$group.data( 'name' );

		this.isGroupParams = true;
		this.isBuilder = !! this.$container.parents( '.us-bld-window' ).length;
		this.isForButtons = this.$group.hasClass( 'preview_button' );
		this.isForFormElms = this.$group.hasClass( 'preview_input_fields' );

		this.initFields( this.$container );
		this.fireFieldEvent( this.$container, 'beforeShow' );
		this.fireFieldEvent( this.$container, 'afterShow' );

		this.accordionTitle = ! $ush.isUndefined( this.$group.data( 'accordion-title' ) )
			? decodeURIComponent( this.$group.data( 'accordion-title' ) )
			: '';

		// If the title for the accordion is not empty then we will watch
		// the changes in the fields in order to correctly update the title
		if ( ! this._isEmptyAccordionTitle() ) {
			for ( var fieldId in this.fields ) {
				if ( ! this.fields.hasOwnProperty( fieldId ) ) {
					continue;
				}
				this.fields[ fieldId ].on( 'change', this._setTitleForAccordion.bind( this ) );
			}
		}

		// Live edit extra class for the buttons
		if ( this.isForButtons ) {
			for ( var fieldId in this.fields ) {
				if ( fieldId !== 'class' && this.fields.hasOwnProperty( fieldId ) ) {
					continue;
				}
				this.fields[ fieldId ].on( 'change', function( field, value ) {
					this.$extraClass = this.$container.find( '.usof-btn-class-extra' );
					this.$extraClass.text( value );
				}.bind( this ) );
			}
		}

		if ( ! this.isBuilder ) {
			for ( var fieldId in this.fields ) {
				if ( ! this.fields.hasOwnProperty( fieldId ) ) {
					continue;
				}
				this.fields[ fieldId ].on( 'change', function( field, value ) {
					if ( $.isEmptyObject( $usof.instance.valuesChanged ) ) {
						clearTimeout( $usof.instance.saveStateTimer );
						$usof.instance.$saveControl.usMod( 'status', 'notsaved' );
					}
					if (
						! $ush.isUndefined( this.group )
						&& $usof.instance.groups[ this.group ] instanceof $usof.Group
					) {
						$usof.instance.valuesChanged[ this.group ] = $usof.instance.groups[ this.group ].getValue();
					}
				}.bind( this ) );
			}
		}

		this.$container.data( 'usofGroupParams', this );

		if ( this.isForButtons ) {
			this.$buttonPreview = $( '.usof-form-group-item-title .usof-btn-preview', this.$container );
			new $usof.ButtonPreview( this.$buttonPreview );
		} else if ( this.isForFormElms ) {
			new $usof.FormElmsPreview( $( '.usof-input-preview', this.$container ) );
		}
	};

	$.extend( $usof.GroupParams.prototype, $usof.mixins.Fieldset, {

		/**
		 * Determines if empty accordion title
		 *
		 * @private
		 * @return {Boolean} True if empty accordion title, False otherwise
		 */
		_isEmptyAccordionTitle: function() {
			return $ush.isUndefined( this.accordionTitle ) || this.accordionTitle === '';
		},

		/**
		 * Sets the title for accordion
		 *
		 * @private
		 */
		_setTitleForAccordion: function() {
			if ( this._isEmptyAccordionTitle() ) return;
			// Get element $title
			this.$title = this.$container.find( '.usof-form-group-item-title' );
			if ( this.isForButtons ) {
				this.$title = this.$title.find( '.usof-btn-label' );
			}
			var title = this.accordionTitle;
			for ( var fieldId in this.fields ) {
				if (
					! this.fields.hasOwnProperty( fieldId )
					|| title.indexOf( fieldId ) < 0
				) {
					continue;
				}
				var field = this.fields[ fieldId ],
					value = this.getValue( fieldId );
				if (
					field.hasOwnProperty( 'type' )
					&& field.type === 'select'
				) {
					var $option = $( 'option[value="' + value + '"]', field.$container );
					if ( $option.length && $option.html() !== '' ) {
						value = $option.html();
					}
				}
				title = title.replace( fieldId, value );
			}

			this.$title.text( title );
		}
	} );

	var USOF_Meta = function( container ) {
		this.$container = $( container );
		this.initFields( this.$container );

		this.fireFieldEvent( this.$container, 'beforeShow' );
		this.fireFieldEvent( this.$container, 'afterShow' );

		for ( var fieldId in this.fields ) {
			if ( ! this.fields.hasOwnProperty( fieldId ) ) {
				continue;
			}
			this.fields[ fieldId ].on( 'change', function( field, value ) {
				USMMSettings = {};
				for ( var savingFieldId in this.fields ) {
					USMMSettings[ savingFieldId ] = this.fields[ savingFieldId ].getValue();
				}
				$( _document.body ).trigger( 'usof_mm_save' );
			}.bind( this ) );
		}

	};
	$.extend( USOF_Meta.prototype, $usof.mixins.Fieldset, {} );

	var USOF = function( container ) {
		$usof.instance = this;
		this.$container = $( container );
		this.$title = this.$container.find( '.usof-header-title h2' );

		this.$container.addClass( 'inited' );

		this.initFields( this.$container );

		this.active = null;
		this.$sections = {};
		this.$sectionContents = {};
		this.sectionFields = {};
		$.each( this.$container.find( '.usof-section' ), function( index, section ) {
			var $section = $( section ),
				sectionId = $section.data( 'id' );
			this.$sections[ sectionId ] = $section;
			this.$sectionContents[ sectionId ] = $section.find( '.usof-section-content' );
			if ( $section.hasClass( 'current' ) ) {
				this.active = sectionId;
			}
			this.sectionFields[ sectionId ] = [];
			$.each( $section.find( '.usof-form-row' ), function( index, row ) {
				var $row = $( row ),
					fieldName = $row.data( 'name' );
				if ( fieldName ) {
					this.sectionFields[ sectionId ].push( fieldName );
				}
			}.bind( this ) );
		}.bind( this ) );

		this.sectionTitles = {};
		$.each( this.$container.find( '.usof-nav-item.level_1' ), function( index, item ) {
			var $item = $( item ),
				sectionId = $item.data( 'id' );
			this.sectionTitles[ sectionId ] = $item.find( '.usof-nav-title' ).html();
		}.bind( this ) );

		this.navItems = this.$container.find( '.usof-nav-item.level_1, .usof-section-header' );
		this.sectionHeaders = this.$container.find( '.usof-section-header' );
		this.sectionHeaders.each( function( index, item ) {
			var $item = $( item ),
				sectionId = $item.data( 'id' );
			$item.on( 'click', function() {
				this.openSection( sectionId );
			}.bind( this ) );
		}.bind( this ) );

		// Handling initial document hash
		if ( _document.location.hash && _document.location.hash.indexOf( '#!' ) == - 1 ) {
			this.openSection( _document.location.hash.substring( 1 ) );
		}

		// Initializing fields at the shown section
		if ( ! $ush.isUndefined( this.$sections[ this.active ] ) ) {
			this.fireFieldEvent( this.$sections[ this.active ], 'beforeShow' );
			this.fireFieldEvent( this.$sections[ this.active ], 'afterShow' );
		}

		// Save action
		this.$saveControl = this.$container.find( '.usof-control.for_save' );
		this.$saveBtn = this.$saveControl.find( '.usof-button' ).on( 'click', this.save.bind( this ) );
		this.$saveMessage = this.$saveControl.find( '.usof-control-message' );
		this.valuesChanged = {};
		this.saveStateTimer = null;
		for ( var fieldId in this.fields ) {
			if ( ! this.fields.hasOwnProperty( fieldId ) ) {
				continue;
			}
			this.fields[ fieldId ].on( 'change', function( field, value ) {
				if ( $.isEmptyObject( this.valuesChanged ) ) {
					clearTimeout( this.saveStateTimer );
					this.$saveControl.usMod( 'status', 'notsaved' );
				}
				this.valuesChanged[ field.name ] = value;
			}.bind( this ) );
		}

		this.$window = $( _window );
		this.$header = this.$container.find( '.usof-header' );
		this.$schemeBtn = this.$container.find( '.for_schemes' );
		this.$schemeBtn.on( 'click', function() {
			$( '.usof-form-row.type_style_scheme' ).show()
		}.bind( this ) );

		this._events = {
			scroll: this.scroll.bind( this ),
			resize: this.resize.bind( this )
		};

		this.resize();
		this.$window.on( 'resize load', this._events.resize );
		this.$window.on( 'scroll', this._events.scroll );
		this.$window.on( 'hashchange', function() {
			this.openSection( _document.location.hash.substring( 1 ) );
		}.bind( this ) );

		$( _window ).bind( 'keydown', function( event ) {
			if ( event.ctrlKey || event.metaKey ) {
				if ( String.fromCharCode( event.which ).toLowerCase() == 's' ) {
					event.preventDefault();
					$usof.instance.save();
				}
			}
		} );
	};
	$.extend( USOF.prototype, $usof.mixins.Fieldset, {
		scroll: function() {
			this.$container.toggleClass( 'footer_fixed', this.$window.scrollTop() > this.headerAreaSize );
		},

		resize: function() {
			if ( ! this.$header.length ) {
				return;
			}
			this.headerAreaSize = this.$header.offset().top + this.$header.outerHeight();
			this.scroll();
		},

		openSection: function( sectionId ) {
			if ( sectionId == this.active || $ush.isUndefined( this.$sections[ sectionId ] ) ) {
				return;
			}
			if ( ! $ush.isUndefined( this.$sections[ this.active ] ) ) {
				this.hideSection();
			}
			this.showSection( sectionId );

			this.$schemeBtn = this.$container.find( '.for_schemes' );
			if ( sectionId == 'colors' ) {
				this.$schemeBtn.removeClass( 'hidden' );
			} else {
				this.$schemeBtn.addClass( 'hidden' );
			}
		},

		showSection: function( sectionId ) {
			var self = this,
				curItem = self.navItems.filter( '[data-id="' + sectionId + '"]' );
			curItem.addClass( 'current' );
			self.fireFieldEvent( self.$sectionContents[ sectionId ], 'beforeShow' );
			self.$sectionContents[ sectionId ].stop( true, false ).fadeIn();
			self.$title.html( self.sectionTitles[ sectionId ] );
			self.fireFieldEvent( self.$sectionContents[ sectionId ], 'afterShow' );
			// Item popup
			var itemPopup = curItem.find( '.usof-nav-popup' );
			if ( itemPopup.length > 0 ) {
				// Current usof_visited_new_sections cookie
				var matches = _document.cookie.match( /(?:^|; )usof_visited_new_sections=([^;]*)/ ),
					cookieValue = matches ? decodeURIComponent( matches[ 1 ] ) : '',
					visitedNewSections = ( cookieValue == '' ) ? [] : cookieValue.split( ',' );
				if ( visitedNewSections.indexOf( sectionId ) == - 1 ) {
					visitedNewSections.push( sectionId );
					_document.cookie = 'usof_visited_new_sections=' + visitedNewSections.join( ',' )
				}
				itemPopup.remove();
			}
			self.active = sectionId;
		},

		hideSection: function() {
			this.navItems.filter( '[data-id="' + this.active + '"]' ).removeClass( 'current' );
			this.fireFieldEvent( this.$sectionContents[ this.active ], 'beforeHide' );
			this.$sectionContents[ this.active ].stop( true, false ).hide();
			this.$title.html( '' );
			this.fireFieldEvent( this.$sectionContents[ this.active ], 'afterHide' );
			this.active = null;
		},

		/**
		 * Save the new values
		 */
		save: function() {
			if ( $.isEmptyObject( this.valuesChanged ) ) {
				return;
			}
			clearTimeout( this.saveStateTimer );
			this.$saveMessage.html( '' );
			this.$saveControl.usMod( 'status', 'loading' );

			$.ajax( {
				type: 'POST',
				url: $usof.ajaxUrl,
				dataType: 'json',
				data: {
					action: 'usof_save',
					usof_options: JSON.stringify( this.valuesChanged ),
					_wpnonce: this.$container.find( '[name="_wpnonce"]' ).val(),
					_wp_http_referer: this.$container.find( '[name="_wp_http_referer"]' ).val()
				},
				success: function( result ) {
					if ( result.success ) {
						this.valuesChanged = {};
						this.$saveMessage.html( result.data.message );
						this.$saveControl.usMod( 'status', 'success' );
						this.saveStateTimer = setTimeout( function() {
							this.$saveMessage.html( '' );
							this.$saveControl.usMod( 'status', 'clear' );
						}.bind( this ), 4000 );
					} else {
						this.$saveMessage.html( result.data.message );
						this.$saveControl.usMod( 'status', 'error' );
						this.saveStateTimer = setTimeout( function() {
							this.$saveMessage.html( '' );
							this.$saveControl.usMod( 'status', 'notsaved' );
						}.bind( this ), 4000 );
					}
				}.bind( this )
			} );
		}
	} );

	$( function() {
		new USOF( '.usof-container:not(.inited)' );

		$.each( $( '.usof-container.for_meta' ), function( _, node ) {
			new USOF_Meta( node );
		} );

		$( _document.body ).off( 'usof_mm_load' ).on( 'usof_mm_load', function() {
			$( '.us-mm-settings' ).each( function( _, node ) {
				new USOF_Meta( node );
			} );
		} );
	} );

}( jQuery );
