/**
 * USOF Field: Color
 */
! function( $, undefined ) {
	var _window = window,
		_document = document;

	if ( _window.$usof === undefined ) {
		return;
	}

	$usof.field[ 'color' ] = {

		init: function( options ) {
			var self = this;

			// Elements
			self.$color = $( '.usof-color', self.$row );
			self.$clear = $( '.usof-color-clear', self.$color );
			self.$list = $( '.usof-color-list', self.$color );
			self.$preview = $( '.usof-color-preview', self.$color );

			// Variables
			self.withGradient = self.$color.hasClass( 'with-gradient' );
			self.isDynamicСolors = self.$color.hasClass( 'dynamic_colors' );

			// Set white text color for dark backgrounds
			self._toggleInputColor( self.getColor() );

			// Init colpick on click
			self.$input
				.off( 'click' )
				.on( 'click', self._events.initColpick.bind( self ) )
				.on( 'input', self._events.inputValue.bind( self ) )
				.on( 'change', self._events.changeValue.bind( self ) );

			self.$clear
				.on( 'click', self._events.inputClear.bind( self ) );

			// Init of a sheet of dynamic colors on click
			if ( self.isDynamicСolors ) {
				self.$color
					.on( 'click', '.usof-color-arrow', self._events.toggleList.bind( self ) )
					.on( 'click', '.usof-color-list-item', self._events._changeColorListItem.bind( self ) );
			}

			// If the sheet is open and there was a click outside the sheet, then close the sheet
			$( _document )
				.mouseup( self._events.hideList.bind( self ) );
		},
		_events: {
			/**
			 * Init colpick
			 */
			initColpick: function() {
				var self = this;
				self.$input.usof_colorPicker( {
					input: self.$input,
					value: self.getColor(),
					onChange: function( colors ) {
						self._invertInputColors( colors.color.first.rgba );
					}
				} );
			},

			/**
			 * Init of a sheet of dynamic variables
			 *
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			toggleList: function( e ) {
				var self = this;
				if ( ! self.$color.is( '.show' ) ) {
					self.initDynamicColors();
				}
				self.$color.toggleClass( 'show' );
			},

			/**
			 * Change color list item
			 *
			 * @private
			 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			_changeColorListItem: function( e ) {
				this.сhooseColorVar( $( e.currentTarget ).data('name') || '' );
			},

			/**
			 * Hide the list
			 *
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			hideList: function( e ) {
				var self = this;
				if ( ! self.$color.hasClass( 'show' ) ) {
					return;
				}
				if ( ! self.$color.is( e.target ) && self.$color.has( e.target ).length === 0 ) {
					self.$color.removeClass( 'show' );
				}
			},

			/**
			 * Input value
			 * @return value
			 */
			inputValue: function() {
				var self = this,
					value = self.getValue();
				// Preloading the list of variables if the value contains brackets
				if ( value.indexOf( '_' ) !== - 1 ) {
					self.initDynamicColors();
				}
			},

			/**
			 * Changed value
			 */
			changeValue: function() {
				var self = this,
					value = self.getValue();
				// Check the value for dynamic variables
				if ( value.indexOf( '_' ) !== - 1 ) {
					$( '[data-name^="' + value + '"]:first', self.$list )
						.trigger( 'click' );
				} else {
					self.setValue( value );
					self.trigger( 'change', value );
				}
			},

			/**
			 * Clear value
			 */
			inputClear: function() {
				var self = this;
				if ( self.$color.hasClass( 'show' ) ) {
					self.$color.removeClass( 'show' );
				}
				self.setValue( '' );
			}
		},
		/**
		 * Choose a color from a list of variables.
		 *
		 * @param {string} name The color name from the list example `content_bg_alt`
		 * @param {boolean} quiet The quiet mode
		 */
		сhooseColorVar: function( name, quiet ) {
			var self = this,
				$target = $( '[data-name="' + name + '"]:first', self.$list ),
				value = $target.data( 'value' ) || '';

			$( '[data-name]', self.$list ) // Reset all selected
				.removeClass( 'selected' );
			$target // Selected color
				.addClass( 'selected' );
			self.$preview // Show preview
				.css( 'background', value );
			self.$input // Set current value
				.val( $target.data( 'name' ) || '' );
			if ( ! quiet ) {
				self.trigger( 'change', self.$input.val() );
			}
			// Set white text color for dark backgrounds
			self._toggleInputColor( value );
			self.$color
				.removeClass( 'show' );
		},

		/**
		 * Add dynamic colors to the list
		 */
		initDynamicColors: function() {
			var self = this;
			if ( self.$color.hasClass( 'list-inited' ) ) {
				return;
			}

			var /**
				 * Add item to list.
				 *
				 * @param {node} $el
				 * @param {node} item
				 */
				insertItem = function( $el, item ) {
					// Exclude yourself
					if ( self.name === item.name ) {
						return;
					}
					var $item = $( '<div></div>' ),
						$palette = $( '<div class="usof-colpick-palette-value"><span></span></div>' ),
						value = self.getValue();
					$palette
						.find( 'span' )
						.css( 'background', item.value )
						.attr( 'title', item.value );
					$item
						.addClass( 'usof-color-list-item' )
						.attr( 'data-name', item.name )
						.data( 'value', item.value )
						.append( $palette )
						.append( '<span class="usof-color-list-item-name">' + item.title + '</span>' );
					if ( value.indexOf( '_' ) !== - 1 && item.name === value ) {
						$item.addClass( 'selected' );
					}
					$el.append( $item );
				};

			// Add dynamic colors to the list
			$.each( $usof.getData( 'dynamicColors' ) || {}, function( key, item ) {
				// Group options
				if ( $.isArray( item ) && item.length ) {
					$group = $( '> [data-group="' + key + '"]:first', self.$list );
					if ( ! $group.length ) {
						$group = $( '<div class="usof-color-list-group" data-group="' + key + '"></div>' );
						self.$list.append( $group );
					}
					$.each( item, function( _, _item ) {
						insertItem.call( self, $group, _item );
					} );
					// Options
				} else {
					insertItem.call( self, self.$list, item );
				}
			} );
			self.$color
				.addClass( 'list-inited' );
		},

		/**
		 * Set the value
		 *
		 * @param {string} value
		 * @param {boolean} quiet
		 */
		setValue: function( value, quiet ) {
			var self = this;
			value = value.trim();

			// Check the value for dynamic variables
			if ( value.indexOf( '_' ) !== - 1 ) {
				self.initDynamicColors();
				self.сhooseColorVar( value, quiet );
				return;
			}

			var r, g, b, a, hexR, hexG, hexB, gradient, rgba = {};

			self.convertRgbToHex = function( color ) {
				if ( m = /^([^0-9]{1,3})*(\d{1,3})[^,]*,([^0-9]{1,3})*(\d{1,3})[^,]*,([^0-9]{1,3})*(\d{1,3})[\s\S]*$/.exec( color ) ) {
					rgba = {
						r: m[ 2 ],
						g: m[ 4 ],
						b: m[ 6 ],
					};
					hexR = m[ 2 ] <= 255 ? ( "0" + parseInt( m[ 2 ], 10 ).toString( 16 ) ).slice( - 2 ) : 'ff';
					hexG = m[ 4 ] <= 255 ? ( "0" + parseInt( m[ 4 ], 10 ).toString( 16 ) ).slice( - 2 ) : 'ff';
					hexB = m[ 6 ] <= 255 ? ( "0" + parseInt( m[ 6 ], 10 ).toString( 16 ) ).slice( - 2 ) : 'ff';
					color = '#' + hexR + hexG + hexB;
					return color;
				}
			};

			if ( $.usof_colorPicker.isGradient( value ) ) {
				gradient = $.usof_colorPicker.gradientParser( value );
				rgba = $.usof_colorPicker.hexToRgba( gradient.hex );
			} else if ( ( m = /^[^,]*,[^,]*,[\s\S]*$/.exec( value ) ) ) {
				// Catch RGB and RGBa
				if ( m = /^[^,]*(,)[^,]*(,)[^,]*(,)[^.]*(\.|0)[\s\S]*$/.exec( value ) ) {
					// Catch only RGBa values
					if ( m[ 4 ] === '.' || m[ 4 ] == 0 ) {
						if ( m = /^([^0-9]{1,3})*(\d{1,3})[^,]*,([^0-9]{1,3})*(\d{1,3})[^,]*,([^0-9]{1,3})*(\d{1,3})[^,]*,[^.]*.([^0-9]{1,2})*(\d{1,2})[\s\S]*$/.exec( value ) ) {
							rgba = {
								r: m[ 2 ],
								g: m[ 4 ],
								b: m[ 6 ],
							};
							r = m[ 2 ] <= 255 ? m[ 2 ] : 255;
							g = m[ 4 ] <= 255 ? m[ 4 ] : 255;
							b = m[ 6 ] <= 255 ? m[ 6 ] : 255;
							a = m[ 8 ];
							value = 'rgba(' + r + ',' + g + ',' + b + ',0.' + a + ')';
						}
					} else {
						value = self.convertRgbToHex( value );
					}
				} else {
					value = self.convertRgbToHex( value );
				}
			} else {
				// Check Hex Colors
				if ( m = /^\#?[\s\S]*?([a-fA-F0-9]{1,6})[\s\S]*$/.exec( value ) ) {
					if ( value == 'inherit' || value == 'transparent' || $.usof_colorPicker.colorNameToHex( value ) ) {
						value = value;
					} else {
						value = $.usof_colorPicker.normalizeHex( m[ 1 ] );
						rgba = $.usof_colorPicker.hexToRgba( value );
					}
				}
			}

			if ( value == '' ) {
				self.$preview.removeAttr( 'style' );
				self.$input.removeClass( 'with_alpha' );
			} else {
				if ( value == 'inherit' || value == 'transparent' ) {
					self.$input.removeClass( 'white' );
					self.$preview.css( 'background', value );
				} else if ( gradient ) {
					if ( self.withGradient ) {
						self.$preview.css( 'background', gradient.gradient );
						self.$input.val( gradient.gradient );
					} else {
						// Don't allow to use gradient colors
						value = gradient.hex;
						self.$preview.css( 'background', value );
						self.$input.val( value );
					}
				} else {
					self.$preview.css( 'background', value );
					self.$input.val( value );
				}
			}

			if ( value == '' || value == 'inherit' || value == 'transparent' ) {
				self.$input.removeClass( 'white' );
			} else {
				self._invertInputColors( rgba );
			}

			self.parentSetValue( value, quiet );
		},

		/**
		 * Get the value
		 *
		 * @return string
		 */
		getValue: function() {
			return $.trim( this.$input.val() ) || '';
		},

		/**
		 * Get color, variables will be replaced with value
		 *
		 * @return {string}
		 */
		getColor: function() {
			var self = this,
				value = self.getValue();
			if ( value.indexOf( '_' ) !== - 1 ) {
				var itemValue = $( '[data-name="' + value + '"]:first', self.$list ).data( 'value' ) || '';
				value = itemValue || self.$color.data( 'value' ) || value;
			}
			return $.trim( value );
		},

		/**
		 * Set white text color for dark backgrounds
		 *
		 * @param {string} value
		 */
		_toggleInputColor: function( value ) {
			var self = this;
			if ( ! value ) {
				self.$input.removeClass( 'white' );
				return;
			}
			// If the HEX value is 3-digit, then convert it to 6-digit
			if ( value.slice( 0, 1 ) === '#' && value.length === 4 ) {
				value = value.replace( /^#([\dA-f])([\dA-f])([\dA-f])$/, "#$1$1$2$2$3$3" )
			}
			if (
				value !== 'inherit'
				&& value !== 'transparent'
				&& value.indexOf( 'linear-gradient' ) === - 1
			) {
				if ( $.usof_colorPicker.colorNameToHex( value ) ) {
					self._invertInputColors( $.usof_colorPicker.hexToRgba( $.usof_colorPicker.colorNameToHex( value ) ) );
				} else {
					self._invertInputColors( $.usof_colorPicker.hexToRgba( value ) );
				}
			} else if ( value.indexOf( 'linear-gradient' ) !== - 1 ) {
				var gradient = $.usof_colorPicker.gradientParser( value );
				// Make sure the gradient was parsed
				if ( gradient != false ) {
					self._invertInputColors( $.usof_colorPicker.hexToRgba( gradient.hex ) );
				}
			}
		},

		_invertInputColors: function( rgba ) {
			if ( ! rgba && ( typeof rgba != 'object' ) ) {
				return;
			}
			var r = rgba.r ? rgba.r : 0,
				g = rgba.g ? rgba.g : 0,
				b = rgba.b ? rgba.b : 0,
				a = ( rgba.a === 0 || rgba.a ) ? rgba.a : 1,
				light;
			// Determine lightness of color
			light = r * 0.213 + g * 0.715 + b * 0.072;
			// Increase lightness regarding color opacity
			if ( a < 1 ) {
				light = light + ( 1 - a ) * ( 1 - light / 255 ) * 235;
			}
			this.$input[ light < 178 ? 'addClass' : 'removeClass' ]( 'white' );
		}
	};

}( jQuery );
