/**
 * USOF Field: Design Options
 * TODO: Make code optimizations to improve performance!
 */
! function( $, undefined ) {
	var _window = window,
		_document = document,
		_undefined = undefined;

	if ( _window.$usof === _undefined ) {
		return;
	}

	$usof.field[ 'design_options' ] = {
		init: function() {
			var self = this;

			// Variables
			self.defaultGroupValues = {}; // default parameter values by groups
			self.defaultValues = {}; // default inline values
			self.groupParams = {};
			self._lastSelectedScreen = 'default';

			// Elements
			self.$container = $( '.usof-design-options', self.$row );
			self.$input = $( 'textarea.usof_design_value', self.$container );

			// Elements import
			self.$import = $( '.usof-design-options-import', self.$container );
			self.$importHeader = $( '.usof-design-options-import-header', self.$import );
			self.$importFooter = $( '.usof-design-options-import-footer', self.$import );
			self.$importBtnCopy = $( 'button[data-action="copy"]', self.$importHeader );

			// Get responsive states
			self.states = self.$container[ 0 ].onclick() || ['default'];
			self.extStates = self.states.slice( 1 );
			self.$container.removeAttr( 'onclick' );

			// Fix live click for WPBakery Page Builder
			self.isWPBakery = self.$input.hasClass( 'wpb_vc_param_value' );

			// The value is a string otherwise it will be an object
			self.hasStringValue = ( self.isWPBakery || self.isEditLive() );

			if ( self.isWPBakery ) {
				self.$container.closest( '.edit_form_line' ).addClass( 'usof-not-live' );
			}

			/**
			 * Check for changes in the parameter group
			 * Note: The code is moved to a separate function since `debounced` must be initialized before calling
			 *
			 * @private
			 * @type debounced
			 * TODO: There was a replacement for `$ush.debounce()` and we need to test the functionality well
			 */
			self.__checkChangeValues = $ush.debounce( self.checkChangeValues.bind( self ), 1/* ms */ );

			// Creates copy settings for different screen sizes
			if ( self.extStates.length ) {
				$( '[data-responsive-state-content="default"]', self.$container ).each( function( _, content ) {
					var $content = $( content );
					self.extStates.map( function( screen ) {
						var $cloneContent = $content
							.clone()
							.attr( 'data-responsive-state-content', screen )
							.addClass( 'hidden' );
						$content
							.after( $cloneContent );
					} );
				} );
			}

			// State grouping
			self.states.map( function( screen ) {
				self.groupParams[ screen ] = new $usof.GroupParams(
					$( '[data-responsive-state-content="' + screen + '"]', self.$container )
				);
			} );

			$.each( self.groupParams, function( screen, groupParams ) {
				// Group start parameters
				$.each( groupParams.fields, function( fieldName, field ) {
					var $group = field.$row.closest( '[data-accordion-content]' ),
						value = field.getValue();
					if ( $group.length ) {
						var groupKey = $group.data( 'accordion-content' );

						// Save groups
						if ( ! self.defaultGroupValues.hasOwnProperty( groupKey ) ) {
							self.defaultGroupValues[ groupKey ] = {};
						}
						if ( ! self.defaultGroupValues[ groupKey ].hasOwnProperty( screen ) ) {
							self.defaultGroupValues[ groupKey ][ screen ] = {};
						}
						self.defaultGroupValues[ groupKey ][ screen ][ fieldName ] = value;

						// Save default value
						if ( ! self.defaultValues.hasOwnProperty( screen ) ) {
							self.defaultValues[ screen ] = {};
						}
						self.defaultValues[ screen ][ fieldName ] = value;

						// Add devive type to group and field
						$group.data( 'responsive-state', screen )
						field.screen = screen;
					}
				} );

				// Initializing control over parameter associations
				$.each( groupParams.fields, function( _, field ) {
					var $row = field.$row;
					if ( $row.attr( 'onclick' ) ) {
						field._data = $row[ 0 ].onclick() || '';
						$row.removeAttr( 'onclick' );
						if ( field._data.hasOwnProperty( 'relations' ) ) {
							$row.append( '<i class="fas fa-unlink"></i>' )
								.on( 'click', 'i.fas', self._events.watchAttrLink.bind( self, field ) );
						}
					}
					// Watch events
					field
						.trigger( 'beforeShow' )
						.on( 'change', $ush.debounce( self._events.changeValue.bind( self ), 1 ) );
				} );
			} );

			// Initializing parameters for shortcodes
			var pid = setTimeout( function() {
				if ( ! self.inited ) {
					self.setValue( self.$input.val() );
					// Check for changes in the parameter group
					self.checkChangeValues.call( self );
				}
				// Controlling the display of the button for copying
				self.$importBtnCopy.prop( 'disabled', ! self.getValue() );
				clearTimeout( pid );
			}, 1 );

			// Hide/Show states panel
			$( '.usof-responsive-buttons', self.$container )
				.toggleClass( 'hidden', ! self.extStates.length );

			// Watch events
			self.$container
				.on( 'click', '[data-accordion-id]', self._events.toggleAccordion.bind( self ) )
				.on( 'click', '.usof-design-options-reset', self._events.resetValues.bind( self ) )
				.on( 'click', '.usof-design-options-responsive', self._events.toggleResponsive.bind( self ) )
				.on( 'click', '[data-responsive-state]', self._events.selectResponsiveState.bind( self ) );

			// Watch import events
			self.$import
				.on( 'click', 'button[data-action]', self._events.importActions.bind( self ) );
		},

		// Event handlers
		_events: {
			/**
			 * Collects parameters into a string when changing any parameter
			 *
			 * @param {$usof.field} field USOF Field
			 */
			changeValue: function( field ) {
				var self = this,
					result = {}, // result of design options
					enabledResponsives = [], // params with active responsive settings
					valuesChanged = {}; // params whose values are set

				// Define parameters that have resposive settings enabled and values set
				$( '[data-accordion-id].responsive', self.$container ).each( function( _, node ) {
					var accordionId = $( node ).data( 'accordionId' ),
						props = self.defaultGroupValues[ accordionId ].default || {};
					for ( var k in props ) {
						enabledResponsives.push( k );
						valuesChanged[ k ] = [];
						$.each( self.groupParams, function( _, groupParams ) {
							valuesChanged[ k ].push( groupParams.getValue( k ) );
						} );
					}
				} );

				// Get values for result
				$.each( self.groupParams, function( screen, groupParams ) {
					$.each( groupParams.getValues(), function( param, value ) {
						var defaultValue = self.defaultValues[ screen ][ param ],
							isValueChanged = false;
						// If responsive screens are enabled, then check the value setting on any screen
						if ( enabledResponsives.indexOf( param ) > -1 ) {
							for ( var i in valuesChanged[ param ] ) {
								isValueChanged = valuesChanged[ param ][ i ] !== defaultValue;
								if ( isValueChanged ) {
									break;
								}
							}
						}
						// If the value is set, then add to the result
						if ( isValueChanged || value !== defaultValue ) {
							if ( ! $.isPlainObject( result[ screen ] ) ) {
								result[ screen ] = {};
							}
							if ( param === 'background-image' && /http/.test( value ) ) {
								value = 'url(' + value + ')'; // image URL support
							}
							result[ screen ][ param ] = value;
						}
					} );
				} );

				// Due to the nature of WPBakery Page Builder, we convert
				// special characters standard escape function
				result = $ush.toString( result );
				if ( result === /* $ush.toString( {} ) > */'%7B%7D' ) {
					result = '';
				}

				// Set result value
				self.$input.val( result );

				// Only when the result changes, then fire the change event.
				if ( ! self._lastResult || self._lastResult !== result ) {
					self._lastResult = result;
					self.trigger( 'change', result );
				}

				// Check for changes in the parameter group
				self.__checkChangeValues();

				// Controlling the display of the button for copying
				self.$importBtnCopy.prop( 'disabled', ! result );

				// Send a signal about a field changed ( this event is used in USBuilder )
				self .trigger( 'changeDesignField', field );
			},

			/**
			 * Resets all group settings to default
			 *
			 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			resetValues: function( e ) {
				e.stopPropagation();
				var self = this,
					$target = $( e.currentTarget ),
					$groupHeader = $target.closest( '[data-accordion-id]' ),
					groupName = $groupHeader.data( 'accordion-id' );

				// Hide responsive options
				if ( $groupHeader.hasClass( 'responsive' ) ) {
					self._events.toggleResponsive.call( self, e );
				}
				if ( self.defaultGroupValues.hasOwnProperty( groupName ) ) {
					$.each( self.defaultGroupValues[ groupName ], function( screen, defaultValues ) {
						var groupParams = self.groupParams[ screen ];
						/**
						 * Note: Setting the default values is done by combining from the
						 * current ones because of the way usof works.
						 */
						groupParams.setValues( $.extend( groupParams.getValues(), defaultValues ) );
						// Didable fields link
						$.each( defaultValues, function( groupParams, name ) {
							var fields = groupParams.fields;
							if (
								fields.hasOwnProperty( name )
								&& fields[ name ].hasOwnProperty( '_data' )
								&& fields[ name ]._data.hasOwnProperty( 'relations' )
							) {
								var $link = $( 'i.fas', groupParams.$fields[ name ] );
								if ( $link.length && $link.hasClass( 'fa-link' ) ) {
									$link.trigger( 'click' );
								}
							}
						}.bind( self, groupParams ) );
					} );
				}
				var pid = setTimeout( function() {
					$groupHeader.removeClass( 'changed' );
					clearTimeout( pid );
				}, 1000 * 0.5 );
			},

			/**
			 * Enable or disable duplication
			 *
			 * @param {$usof.field} field USOF Field
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 * @param {boolean|undefined} state
			 */
			watchAttrLink: function( field, e, state ) {
				var self = this,
					$target = $( e.currentTarget ),
					isUnlink = $target.hasClass( 'fa-unlink' ),
					relations = [];
				if ( state !== _undefined ) {
					isUnlink = state;
				}
				if ( field.hasOwnProperty( '_data' ) && field.hasOwnProperty( 'screen' ) ) {
					$.each( self.groupParams[ field.screen ].fields, function( _name, item ) {
						if ( $.inArray( item.name, field._data.relations || [] ) !== - 1 ) {
							relations.push( item );
						}
					} );
				}
				$target
					.toggleClass( 'fa-link', isUnlink )
					.toggleClass( 'fa-unlink', ! isUnlink );
				if ( relations.length ) {
					relations.map( function( item ) {
						item.$input.prop( 'disabled', isUnlink );
					} );
					field.watchValue = isUnlink;
					if ( isUnlink ) {
						field.$input
							.focus()
							.on( 'input', self._events.changeRelationsValue.bind( self, relations ) )
							.trigger( 'input' );
					} else {
						field.$input.off( 'input' );
					}
				}
			},

			/**
			 * Duplicates settings to related fields
			 *
			 * @param {{}} fields
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			changeRelationsValue: function( fields, e ) {
				var value = $( e.currentTarget ).val();
				fields.map( function( item ) {
					if ( item instanceof $usof.field ) {
						item.setValue( value );
					}
				} );
			},

			/**
			 * Accordion Switch
			 *
			 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			toggleAccordion: function( e ) {
				var $target = $( e.currentTarget ),
					$content = $( '[data-accordion-content="' + $target.data( 'accordion-id' ) + '"]' );

				if ( $target.hasClass( 'active' ) ) {
					$target.removeClass( 'active' );
					$content.removeClass( 'active' );
				} else {
					$target.siblings().removeClass( 'active' );
					$content.siblings().removeClass( 'active' );
					$target.addClass( 'active' );
					$content.addClass( 'active' );
				}
			},

			/**
			 * ON/OFF Responsive options
			 *
			 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			toggleResponsive: function( e ) {
				e.preventDefault();
				e.stopPropagation();

				var self = this,
					$target = $( e.currentTarget ),
					$header = $target.closest( '[data-accordion-id]' ),
					groupKey = $header.data( 'accordion-id' ),
					isEnabled = $header.hasClass( 'responsive' ),
					// Determine the first responsive settings or not
					isFirstResponsive = ! isEnabled
						? ! $( '.usof-design-options-header.responsive:first', self.$container ).length
						: false;

				$header.toggleClass( 'responsive', ! isEnabled );

				if ( ! isEnabled ) {
					// If the first setting will send a change event
					if ( isFirstResponsive ) {
						self.trigger( 'syncResponsiveState', self._lastSelectedScreen );
					}
					self.switchResponsiveState( self._lastSelectedScreen );
				} else {
					self.switchResponsiveState( 'default', /* hidden */true );
				}

				if ( self.defaultGroupValues.hasOwnProperty( groupKey ) ) {
					self.extStates.map( function( screen ) {
						// Reset values for a group whose responsive support is enabled
						var values = $.extend( {}, self.defaultGroupValues[ groupKey ][ screen ] || {} );
						if ( ! isEnabled ) {
							// Set default values for current screen
							$.each( values, function( prop ) {
								if ( self.groupParams[ 'default' ].fields.hasOwnProperty( prop ) ) {
									values[ prop ] = self.groupParams[ 'default' ].fields[ prop ].getValue();
								}
							} );
						}
						if (
							self.groupParams.hasOwnProperty( screen )
							&& self.groupParams[ screen ] instanceof $usof.GroupParams
						) {
							// Get current values to support already set values
							values = $.extend( self.groupParams[ screen ].getValues(), values );
							self.groupParams[ screen ].setValues( values, /* quiet mode */ true );
						}
						// Checking and duplicating wiretap related fields
						if ( ! isEnabled && self.groupParams.hasOwnProperty( screen ) ) {
							$.each( self.groupParams[ 'default' ].fields, function( _, field ) {
								if ( field.hasOwnProperty( 'watchValue' ) ) {
									$( '.fas', self.groupParams[ screen ].fields[ field.name ].$row )
										.trigger( 'click', field.watchValue );
								}
							} );
						}
					} );
				}
			},

			/**
			 * The action handler for import
			 *
			 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			importActions: function( e ) {
				var self = this,
					$target = $( e.target ),
					action = '' + $target.data( 'action' );
				// Copy design options to clipboard
				if ( action == 'copy' ) {
					self._copyValueToClipboard();

					// Show the field for entering design options
				} else if ( action == 'paste' ) {
					self.$import.addClass( 'show_input' );
					self.$input[0].select();

					// Close field without changes
				} else if ( action == 'cancel' ) {
					self.$import.removeClass( 'show_input show_novalid' );

					// Apply design options
				} else if ( action == 'apply' ) {
					var value = self.$input.val();
					if ( ! value ) {
						return;
					}
					// Is valid value
					var isValidValue = self._isValidValue( value );
					self.$import.toggleClass( 'show_novalid', ! isValidValue );
					if ( ! isValidValue ) {
						self.$input.val( '' );
						return;
					}
					// Reset values to default
					$.each( self.groupParams, function( screen, groupParams ) {
						groupParams.setValues( self.defaultValues[ screen ] || {}, true );
					} );
					self.setValue( value ); // set new values
					// Reset css classes and atts
					self.$import.removeClass( 'show_input' );
					self.$importBtnCopy.prop( 'disabled', ! value );
				}
			},

			/**
			 * Handler for selecting a responsive state on click of a button
			 *
			 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			selectResponsiveState: function( e ) {
				var self = this,
					screen = $( e.currentTarget ).data( 'responsive-state' );
				self.switchResponsiveState( screen );
				self.trigger( 'syncResponsiveState', screen );
				self._lastSelectedScreen = screen;
			}
		},

		/**
		 * This is the install handler `screen` of builder
		 *
		 * @private
		 * @event handler
		 * @param {Event} _
		 * @param {string} state The device type
		 */
		_usbSyncResponsiveState: function( _, state ) {
			var self = this;
			self._lastSelectedScreen = state || 'default';
			self.switchResponsiveState( self._lastSelectedScreen );
		},

		/**
		 * Switch device type
		 *
		 * @param {string} screen
		 * @param {boolean} hidden
		 */
		switchResponsiveState:function( screen, hidden ) {
			var self = this;
			if ( ! screen ) {
				return;
			}
			// Switch between hidden/shown targets
			var hasResponsiveClass = ! hidden
				? '.responsive'
				: ':not(.responsive)'

			// Get active responsive blocks
			var $target = $( '[data-accordion-id]' + hasResponsiveClass, self.$container )
					.next( '[data-accordion-content]' )
					.find( '[data-responsive-state="'+ screen +'"]' );
			// Remove active class from siblings
			$target
				.siblings()
				.removeClass( 'active' );
			// Show the required content by device type
			$target
				.addClass( 'active' )
				.closest( '.usof-design-options-content' )
				.find( '> [data-responsive-state-content]' )
				.addClass( 'hidden' )
				.filter( '[data-responsive-state-content="' + screen + '"]' )
				.removeClass( 'hidden' );
		},

		/**
		 * Determines whether the specified value is valid value
		 *
		 * @private
		 * @param {string} value The value
		 * @return {boolean} True if the specified value is valid value, False otherwise
		 */
		_isValidValue: function( value ) {
			var self = this;
			try {
				value = $ush.toPlainObject( ( '' + value ).trim() );
				for ( var i in self.states ) {
					var state = self.states[ i ];
					if ( state && !! value[ state ] ) {
						return true;
					}
				}
			} catch ( err ) {}
			return false;
		},

		/**
		 * Check for changes in the parameter group
		 */
		checkChangeValues: function() {
			var self = this,
				currentGroupValues = {}; // get current values
			$.each( self.groupParams, function( screen, groupParams ) {
				$.each( groupParams.fields, function( _, field ) {
					var groupName = field.$row
						.closest( '[data-accordion-content]' )
						.data( 'accordion-content' );
					if ( ! currentGroupValues.hasOwnProperty( groupName ) ) {
						currentGroupValues[ groupName ] = {};
					}
					if ( ! currentGroupValues[ groupName ].hasOwnProperty( screen ) ) {
						currentGroupValues[ groupName ][ screen ] = {};
					}
					currentGroupValues[ groupName ][ screen ][ field.name ] = field.getValue();
				} );
			} );
			$.each( self.defaultGroupValues, function( groupName, devices ) {
				var change = false;
				$.each( devices, function( screen, values ) {
					if ( ! currentGroupValues.hasOwnProperty( groupName ) || ! currentGroupValues[ groupName ].hasOwnProperty( screen ) ) {
						return;
					}
					change = ( change || JSON.stringify( values ) !== JSON.stringify( currentGroupValues[ groupName ][ screen ] ) );
				} );
				$( '[data-accordion-id=' + groupName + ']', self.$container )
					.toggleClass( 'changed', change );
			} );
		},

		/**
		 * Get the value
		 *
		 * @return {string}
		 */
		getValue: function() {
			var self = this,
				value = $.trim( self.$input.val() );
			if ( ! self.hasStringValue && value && typeof value === 'string' ) {
				value = $ush.toPlainObject( value );
			}
			return value;
		},

		/**
		 * Set the value.
		 *
		 * @param {string} value
		 * @param {boolean} quiet The quiet
		 */
		setValue: function( value, quiet ) {
			var self = this;

			// Get saved parameter values
			var savedValues = {};
			if ( typeof value === 'string' ) {
				savedValues = $ush.toPlainObject( value );
			} else if ( $.isPlainObject( value ) ) {
				savedValues = value;
			}
			var pid = setTimeout( function() {
				// Set values and check link
				$.each( self.groupParams, function( screen, groupParams ) {
					// Reset values
					if ( ! self.hasStringValue ) {
						groupParams.setValues( self.defaultValues[ screen ] || {}, true );
					}
					var values = savedValues[ screen ] || {};
						propName = 'background-image';
					// Image URL support
					if ( values.hasOwnProperty( propName ) && /url\(/.test( values[ propName ] || '' ) ) {
						values[ propName ] = values[ propName ]
							.replace( /\s?url\("?(.*?)"?\)/gi, '$1' );
					}
					// Border style support.
					for ( var k in values ) {
						if ( ! /border-(\w+)-style/.test( k ) ) continue;
						values[ 'border-style' ] = values[ k ];
						delete values[ k ];
					}
					// Set values
					groupParams.setValues( values, true );
					// Check relations link
					$.each( groupParams.fields, function( _, field ) {
						if ( field.hasOwnProperty( '_data' ) && field._data.hasOwnProperty( 'relations' ) ) {
							var $row = field.$row,
								value = $.trim( field.getValue() ),
								isLink = [];
							// Matching all related parameters, and if necessary enable communication.
							( field._data.relations || [] ).map( function( name ) {
								if ( value && self.groupParams[ field.screen ].fields.hasOwnProperty( name ) ) {
									isLink.push( value === $.trim( self.groupParams[ field.screen ].fields[ name ].getValue() ) );
								}
							} );
							if ( isLink.length ) {
								isLink = isLink.filter( function( v ) {
									return v == true
								} );
								if ( isLink.length === 3 ) {
									var pid = setTimeout( function() {
										$( 'i.fas', $row ).trigger( 'click' );
										clearTimeout( pid );
									}, 1 );
								}
							}
						}
					} );
				} );

				// Check options for devices
				var responsiveGroups = {};
				self.extStates.map( function( screen ) {
					var values = savedValues[ screen ] || {};
					$.each( self.defaultGroupValues, function( groupKey, devices ) {
						var isEnable = false;
						$.each( devices[ screen ], function( prop ) {
							if ( ! responsiveGroups[ groupKey ] ) {
								responsiveGroups[ groupKey ] = values.hasOwnProperty( prop );
							}
						} );
					} );
				} );

				$.each( responsiveGroups, function( groupKey, isEnable ) {
					$( '[data-accordion-id="' + groupKey + '"]', self.$container )
						.toggleClass( 'responsive', isEnable );
				} );

				// Check for changes in the parameter group
				self.checkChangeValues.call( self );

				// Default tab selection
				self.switchResponsiveState( 'default', /* hidden */true );

				clearTimeout( pid );
			}, 1 );

			// Set value
			if ( value ) {
				value = self.hasStringValue ? value : $ush.toString( value );
			}
			self.$input.val( '' + value );

			if ( ! quiet ) {
				self.trigger( 'change', [ value ] );
			}

			// Hide all sections of the accordion
			if ( ! self.$input.hasClass( 'wpb_vc_param_value' ) ) {
				$( '> div', self.$container ).removeClass( 'active' );
			}
		},

		/**
		 * Сopy value to clipboard.
		 *
		 * @private
		 */
		_copyValueToClipboard: function() {
			var self = this;
			if ( ! self.$input.val() ) {
				return;
			}
			self.$input[ 0 ].select();
			_document.execCommand( 'copy' );
			// The unselect data
			if ( _window.getSelection ) {
				_window.getSelection().removeAllRanges();
			} else if ( _document.selection ) {
				_document.selection.empty();
			}
		},

		/**
		 * Force value for WPBakery
		 */
		forceWPBValue: function() {
			var self = this;
			if ( self.hasStringValue ) {
				self.setValue( self.getValue() );
			}
		}
	};
}( jQuery );
