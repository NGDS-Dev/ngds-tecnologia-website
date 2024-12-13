/**
 * UpSolution Element: Grid Filter
 */
;( function( $, undefined ) {
	"use strict";

	var _document = document,
		_undefined = undefined;

	/**
	 * US Grid Filter
	 *
	 * @class WGridFilter
	 * @param {String} container The container
	 * @param {{}} options The options
	 */
	$us.WGridFilter = function ( container, options ) {
		this.init( container, options );
	};

	// Export API
	$.extend( $us.WGridFilter.prototype, $us.mixins.Events, {
		/**
		 * @param {String} container The container.
		 * @param {{}} options The options.
		 */
		init: function ( container, options ) {
			var self = this;

			if ( ! $.isPlainObject( options ) ) {
				options = {};
			}

			// Related parameters for getting data, number of records for taxonomy, price range for WooCommerce, etc
			self.filtersArgs = {};

			// Elements
			self.$container = $( container );
			self.$filtersItem = $( '.w-filter-item', self.$container );

			// Load json data
			if ( self.$container.is( '[onclick]' ) ) {
				$.extend( options, self.$container[0].onclick() || {} );
				// Delete data everywhere except for the preview of the USBuilder, the data may be needed again to restore the elements
				if ( ! $us.usbPreview() ) {
					self.$container.removeAttr( 'onclick' );
				}
			}

			// Set options
			options = $.extend( {
				filterPrefix: 'filter', // default prefix
				gridNotFoundMessage: false,
				gridPaginationSelector: '.w-grid-pagination',
				gridSelector: '.w-grid[data-filterable="true"]:first',
				layout: 'hor',
				mobileWidth: 600,
				use_grid: 'first' // default
			}, options );
			self.options = options;

			// Connect use grid if it is set in options
			if ( options.use_grid !== 'first' ) {
				var $use_grid = $( options.use_grid );
				if ( $use_grid.length && $use_grid.hasClass( 'w-grid' ) ) {
					self.$grid = $use_grid;
				}
			}

			// If no grid assigned in options, search for first filterable grid
			if ( self.$grid === _undefined ) {
				self.$grid = $( '.l-main ' + options.gridSelector, $us.$canvas );
			}

			// Load filters args
			var $filtersArgs = $( '.w-filter-json-filters-args:first', self.$container );
			if ( $filtersArgs.length ) {
				self.filtersArgs = $filtersArgs[0].onclick() || {};
				$filtersArgs.remove();
			}

			// Show the message when suitable Grid is not found
			if ( ! self.$grid.length && options.gridNotFoundMessage ) {
				self.$container.prepend( '<div class="w-filter-message">' + options.gridNotFoundMessage + '</div>' );
			}

			// Set class to define the grid is used by Grid Filter
			self.$grid.addClass( 'used_by_grid_filter' );

			// Events
			self.$container
				.on( 'click', '.w-filter-opener', self._events.filterOpener.bind( self ) )
				.on( 'click', '.w-filter-list-closer, .w-filter-list-panel > .w-btn', self._events.filterListCloser.bind( self ) );

			// Item events
			self.$filtersItem
				// Exclude [type="number"] these types for range
				.on( 'change', 'input, select', self._events.changeFilter.bind( self ) )
				.on( 'click', '.w-filter-item-reset', self._events.resetItem.bind( self ) );

			// Pagination
			$( options.gridPaginationSelector, self.$grid )
				.on( 'click', '.page-numbers', self._events.loadPageNumber.bind(self ) );
			$us.$window.on( 'resize load', $ush.debounce( self._events.resize.bind( self ), 10 ) );

			// Built-in private event system
			self.on( 'changeItemValue', self._events.toggleItemValue.bind( self ) );

			// Show or Hide filter item
			if ( self.$container.hasClass( 'show_on_click' ) ) {
				self.$filtersItem.on( 'click', '.w-filter-item-title', self._events.showItem.bind( self ) );
				$( _document ).mouseup( self._events.hideItem.bind( self ) );
			}

			// Adding filter options to Woocommerce ordering
			$( 'form.woocommerce-ordering', $us.$canvas )
				.off( 'change', 'select.orderby' )
				.on( 'change', 'select.orderby', self._events.woocommerceOrdering.bind( self ) );

			// Change item values
			self.checkItemValues.call( self );

			// If there are selected parameters then add the class `active` to the main container
			self.$container.toggleClass( 'active', self.$filtersItem.hasClass('has_value') );

			// Subscription to receive data on recounts of amounts
			self.on( 'us_grid_filter.update-items-amount', self._events.updateItemsAmount.bind( self ) );

			// Set state to fix mobile Safari issue
			self._events.resize.call( self );

			if ( self.$container.hasClass( 'togglable' ) ) {
				self.$filtersItem.on( 'click', '.w-filter-item-title', self._events.showHideAccordionItem.bind( self ) );
			}
		},

		/**
		 * Determines if mobile
		 *
		 * @return {Boolean} True if mobile, False otherwise
		 */
		isMobile: function() {
			return parseInt( $us.$window.width() ) <= parseInt( this.options.mobileWidth );
		},

		/**
		 * Kill current event
		 *
		 * @private
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_stop: function( e ) {
			e.preventDefault();
			e.stopPropagation();
		},

		/**
		 * Event handlers
		 * @private
		 */
		_events: {
			/**
			 * Change values
			 *
			 * @private
		 	 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			changeFilter: function( e ) {
				var self = this,
					$target = $( e.currentTarget ),
					$item = $target.closest( '.w-filter-item' ),
					uiType = $item.data( 'ui_type' );

				// Locked filters
				$item.removeClass( 'disabled' );
				self.$filtersItem
					.not( $item )
					.addClass( 'disabled' );

				if ( ['radio', 'checkbox'].indexOf( uiType ) !== -1 ) {
					// Reset All
					if ( uiType === 'radio' ) {
						$( '.w-filter-item-value', $item )
							.removeClass( 'selected' );
					}
					$target
						.closest( '.w-filter-item-value' )
						.toggleClass( 'selected', $target.is( ':checked ') );

				} else if( uiType === 'range' ) {
					var $inputs = $( 'input[type!=hidden]', $item ),
						rangeValues = [];
					$inputs.each( function( i, input ) {
						var $input = $( input ),
							value = input.value || 0;
						// If no value, check placeholders
						if ( ! value && $input.hasClass( 'type_' + [ 'min', 'max' ][ i ] ) && rangeValues.length == i ) {
							value = $input.attr( 'placeholder' ) || 0;
						}
						value = parseInt( value );
						rangeValues.push( ! isNaN( value ) ? value : 0 );
					} );
					// Set values and trigger change event
					rangeValues = rangeValues.join('-');
					$( 'input[type="hidden"]', $item )
						.val( rangeValues !== '0-0' ? rangeValues : '' );
				}

				var value = self.getValue();
				$ush.debounce_fn_1ms( self.URLSearchParams.bind( self, value ) );

				self.triggerGrid( 'us_grid.updateState', [ value, /* page */ 1, self ] );

				// Change item values
				self.trigger( 'changeItemValue', $item );

				// If there are selected parameters then add the class `active` to the main container
				self.$container.toggleClass( 'active', self.$filtersItem.hasClass('has_value') );
			},

			/**
			 * Load a grid page via AJAX
			 *
			 * @private
		 	 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			loadPageNumber: function ( e ) {
				var self = this;
				self._stop( e );
				var $target = $( e.currentTarget ),
					href = $target.attr( 'href' ) || '',
					matches = ( href.match( /page(=|\/)(\d+)(\/?)/ ) || [] ),
					page = parseInt( matches[2] || 1 /* default first page */ );

				history.replaceState( _document.title, _document.title, href );
				self.triggerGrid( 'us_grid.updateState', [ self.getValue(), page, self ] );
			},

			/**
			 * Reset item selected
			 *
			 * @private
		 	 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			resetItem: function( e ) {
				var self = this;
				self._stop( e );

				var $item = $( e.currentTarget ).closest( '.w-filter-item' ),
					uiType = $item.data( 'ui_type' );

				if ( ! uiType ) {
					return;
				}

				// Reset checkboxes and radio buttons.
				if ( 'checkbox|radio'.indexOf( uiType ) !== -1 ) {
					$( 'input:checked', $item ).prop( 'checked', false );

					// Select `All` radio button
					$( 'input[value="*"]:first', $item ).each( function( _, input ) {
						$( input )
							.prop( 'checked', true )
							.closest( '.w-filter-item' )
							.addClass( 'selected' );
					} );
				}

				// Reset range values
				if ( uiType === 'range' ) {
					$( 'input', $item ).val( '' );
				}

				// Reset select option
				if ( uiType === 'dropdown' ) {
					$( 'option', $item ).prop( 'selected', false );
				}

				// Clear css classes
				$( '.w-filter-item-value', $item ).removeClass( 'selected' );

				// Change item values
				self.trigger( 'changeItemValue', $item );

				// If there are selected parameters then add the class `active` to the main container
				self.$container.toggleClass( 'active', self.$filtersItem.hasClass('has_value') );

				// Update URL
				var value = self.getValue();
				$ush.debounce_fn_1ms( self.URLSearchParams.bind( self, value ) );
				self.URLSearchParams( value );
				self.triggerGrid( 'us_grid.updateState', [ value, /* page */ 1, self ] );
			},

			/**
			 * Change item values
			 *
			 * @param {{}} _ self
			 * @param {Mixed} item
			 */
			toggleItemValue: function( _, item ) {
				var self = this,
					$item = $( item ),
					title = '',
					hasValue = false,
					uiType = $item.data('ui_type'),
					$selected = $( 'input:not([value="*"]):checked', $item );

				if ( ! uiType ) {
					return;
				}
				// Get title from radio buttons and checkboxes
				if ( 'checkbox|radio'.indexOf( uiType ) !== -1 ) {
					hasValue = $selected.length;

					// For a horizontal filter, if there are selected parameters, display either the selected parameter or quantity
					if ( self.options.layout == 'hor' ) {
						var title = '';
						if ( $selected.length === 1 ) {
							title += ': ' + $selected.nextAll( '.w-filter-item-value-label:first' ).text();
						} else if( $selected.length > 1 ) {
							title += ': ' + $selected.length;
						}
					}
				}

				if ( uiType === 'dropdown' ) {
					var value = $( 'select:first', $item ).val();
					hasValue = ( value !== '*' )
						? !! value
						: '';
				}
				// Get title from range inputs
				if ( uiType === 'range' ) {
					var value = $( 'input[type="hidden"]:first', $item ).val();
					hasValue = !! value;
					if ( self.options.layout == 'hor' && value ) {
						title += ': ' + value;
					}
				}

				// Add of `has_value` class when selecting options
				$item.toggleClass( 'has_value', !! hasValue );

				// Add open class when selecting options
				if (
					self.$container.hasClass( 'togglable' )
					&& hasValue
				) {
					$item.addClass( 'open' );
				}

				// Update item title
				$( '> .w-filter-item-title > span:not(.w-filter-item-reset)', item ).html( title );
			},

			/**
			 * Changes when resizing the screen
			 */
			resize: function() {
				var self = this;
				self.$container.usMod( 'state', self.isMobile() ? 'mobile' : 'desktop' );
				if ( ! self.isMobile() ) {
					$us.$body.removeClass( 'us_filter_open' );
					self.$container.removeClass( 'open' /* filter opener */ );
				}
			},

			/**
			 * Open Mobile Filter
			 */
			filterOpener: function() {
				$us.$body.addClass( 'us_filter_open' );
				this.$container.addClass( 'open' );
			},

			/**
			 * Close Mobile Filter
			 */
			filterListCloser: function() {
				$us.$body.removeClass( 'us_filter_open' );
				this.$container.removeClass( 'open' );
			},

			/**
			 * Show vertical items
			 *
			 * @private
		 	 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			showItem: function( e ) {
				var $target = $( e.currentTarget ),
					$item = $target.closest( '.w-filter-item' );
				$item.addClass( 'show' );
			},

			/**
			 * Hide vertical items when click outside the item
			 *
			 * @private
		 	 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			hideItem: function( e ) {
				var self = this;
				if ( ! self.$filtersItem.hasClass( 'show' ) ) {
					return;
				}
				self.$filtersItem.filter( '.show' ).each( function( _, item ) {
					var $item = $( item );
					if ( ! $item.is( e.target ) && $item.has( e.target ).length === 0 ) {
						$item.removeClass( 'show' );
					}
				} );
			},

			/**
			 * Show or hide accordion item on click .w-filter-item-title
			 *
			 * @private
		 	 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			showHideAccordionItem: function( e ) {
				var $target = $( e.currentTarget ),
					$item = $target.closest( '.w-filter-item' );

				$item.toggleClass('open');
			},

			/**
			 * Add grid filter options to sort request
			 *
			 * @private
		 	 * @event handler
			 * @param {Event} e The Event interface represents an event which takes place in the DOM
			 */
			woocommerceOrdering: function( e ) {
				e.stopPropagation();
				var self = this,
					$form = $( e.currentTarget ).closest( 'form' );
				$( 'input[name^="'+ self.options.filterPrefix +'"]', $form )
					.remove();
				$.each( self.getValue().split( '&' ), function( _, item ) {
					var value = item.split( '=' );
					if ( value.length === 2 ) {
						$form.append( '<input type="hidden" name="'+ value[0] +'" value="'+ value[1] +'"/>' );
					}
				} );
				$form.trigger( 'submit' );
			},

			/**
			 * Update amount items
			 *
			 * @param {$us.WGridFilter} _
			 * @param {{}} data
			 */
			updateItemsAmount: function( _, data ) {
				var self = this;

				// Unlock filters
				self.$filtersItem.removeClass( 'disabled' );

				// Taxonomy updates
				$.each( ( data.taxonomies_query_args || {} ), function( key, items ) {
					var $item = self.$filtersItem.filter( '[data-source="'+ key +'"]' ),
						uiType = $item.data('ui_type'),
						showCount = 0;
					$.each( items, function( value, amount ) {
						var disabled = ! amount;
						if ( ! disabled ) {
							showCount++;
						}
						// For dropdowns
						if ( uiType === 'dropdown' ) {
							var $option = $( 'select:first option[value="'+ value +'"]', $item ),
								template = $option.data( 'template' ) || '';
							// Apply option template
							if ( template ) {
								template = template
									.replace( '%s', ( amount ? '(' + amount + ')' : '' ) );
								$option.text( template );
							}
							$option
								.prop( 'disabled', disabled )
								.toggleClass( 'disabled', disabled );

							// For inputs
						} else {
							var $input = $( 'input[value="'+ value +'"]', $item );

							$input
								.prop( 'disabled', disabled )
								.nextAll( '.w-filter-item-value-amount' )
								.text( amount );
							$input
								.closest( '.w-filter-item-value' )
								.toggleClass( 'disabled', disabled );

							// Disable option if there are no entries for it
							if ( disabled && $input.is( ':checked' ) ) {
								$input.prop( 'checked', false );
							}
						}
					} );

					if ( ! showCount && self.options.hideDisabledValues ) {
						$item.addClass( 'disabled' );
					}

				} );

				// Prices range update
				if (
					data.hasOwnProperty( 'wc_min_max_price' )
					&& data.wc_min_max_price instanceof Object
				) {
					var $price = self.$filtersItem.filter( '[data-source$="|_price"]' );
					$.each( ( data.wc_min_max_price || {} ), function( name, value ) {
						var $input = $( 'input.type_' + name, $price );
						$input.attr( 'placeholder', value ? value : $input.attr( 'aria-label' ) );
					} );
				}

				// Update URL
				if ( ! $.isEmptyObject( data ) ) {
					if ( self.handle ) {
						$ush.clearTimeout( self.handle );
					}
					self.handle = $ush.timeout( function() {
						$ush.debounce_fn_1ms( self.URLSearchParams.bind( self, self.getValue() ) );
						self.checkItemValues.call( self );
					}, 100 );
				}
			}
		},

		/**
		 * Raises a private event in the grid
		 *
		 * @param {String} eventType
		 * @param mixed extraParameters
		 */
		triggerGrid: function ( eventType, extraParameters ) {
			$ush.debounce_fn_10ms( function() { $us.$body.trigger( eventType, extraParameters ); } );
		},

		/**
		 * Check item values
		 */
		checkItemValues: function() {
			var self = this;
			self.$filtersItem.each( function( _, item ) {
				self.trigger( 'changeItemValue', item );
			} );
		},

		/**
		 * Get the value
		 *
		 * @return {string}
		 */
		getValue: function() {
			var value = '',
				filters = {};
			$.each( this.$container.serializeArray(), function( _, filter ) {
				if ( filter.value === '*' /* all */ || ! filter.value ) {
					return;
				}
				if ( ! filters.hasOwnProperty( filter.name ) ) {
					filters[ filter.name ] = [];
				}
				filters[ filter.name ].push( filter.value );
			} );
			// Convert params in a string
			for ( var k in filters ) {
				if ( value ) {
					value += '&';
				}
				if ( $.isArray( filters[ k ] ) ) {
					value += k + '=' + filters[ k ].join( ',' );
				}
			}

			return encodeURI( value );
		},

		/**
		 * Set search queries in the url
		 *
		 * @param {String} params The query parameters
		 */
		URLSearchParams: function( params ) {
			var url = location.origin + location.pathname + ( location.pathname.slice( -1 ) != '/' ? '/' : '' ),
				// Get current search and remove filter params
				search = location.search.replace( new RegExp( this.options.filterPrefix + "(.+?)(&|$)", "g" ), '' );
			if ( ! search || search.substr( 0, 1 ) !== '?' ) {
				search += '?';
			} else if ( '?&'.indexOf( search.slice( -1 ) ) === -1 ) {
				search += '&';
			}
			// Remove last ?&
			if ( ! params && '?&'.indexOf( search.slice( -1 ) ) !== -1 ) {
				search = search.slice( 0, -1 );
			}
			history.replaceState( _document.title, _document.title, url + search + params );
		}
	} );

	// Add to jQuery
	$.fn.wGridFilter = function ( options ) {
		return this.each( function () {
			$( this ).data( 'wGridFilter', new $us.WGridFilter( this, options ) );
		} );
	};

	// Init
	$( function() {
		$( '.w-filter', $us.$canvas ).wGridFilter();
	} );
})( jQuery );
