/**
 * Scroll Effects - Functionality describing the logic of scrolling effect.
 */
! function( $, undefined ) {
	"use strict";

	// Private variables that are used only in the context of this function, it is necessary to optimize the code.
	var _window = window,
		_document = document,
		_body = _document.body;

	// Math API
	var abs = Math.abs,
		max = Math.max,
		min = Math.min,
		floor = Math.floor,
		round = Math.round;

	// Check for is set availability objects
	_window.$us = _window.$us || {};
	_window.$ush = _window.$ush || {};

	/**
	 * @var {{}} Effect directions.
	 */
	const _DIRECTION_ = {
		DOWN: 'down',
		RIGHT: 'right',
	};

	/**
	 * @var {Number} N(px) - The value provides the best balance: performance VS precision.
	 */
	const _TRANSLATE_FACTOR_ = 7;

	/**
	 * Get the current offset of the scrolls.
	 *
	 * @return {{}} Returns an object of current scroll values.
	 */
	function scroll() {
		return {
			top: _window.scrollY || _window.pageYOffset,
			left: _window.scrollX || _window.pageXOffset
		};
	}

	/**
	 * Determines if disable effects.
	 *
	 * @return {Boolean} True if disable effects, False otherwise.
	 */
	function isDisableEffects() {
		return $us.canvasOptions.disableEffectsWidth >= _body.clientWidth;
	}

	/**
	 * @var {{}} Private temp data.
	 */
	var _$temp = {
		bodyHeight: $ush.parseInt( _body.clientHeight ),
		disableEffects: isDisableEffects(),
	};

	/**
	 * @class ScrollEffects - Scroll effects manager functionality.
	 */
	function ScrollEffects() {
		var self = this;

		// Variables
		self.elms = [];

		/**
		 * @var {{}} Bondable events
		 */
		self._events = {
			scroll: self._handleScroll.bind( self ),
			resize: $ush.debounce( self._handleResize.bind( self ), 25 ),
			updateElmsInitialData: $ush.debounce( self._handleUpdateElmsInitialData.bind( self ), 1 ),
		};

		// Events
		$us.$window
			// Handler for the scroll event
			.on( 'scroll', self._events.scroll )
			// Handler for viewport resize event
			.on( 'resize', self._events.resize );

		// Handler for updating initial data when content changes
		$us.$canvas
			.on( 'contentChange', self._events.updateElmsInitialData );
	}

	// Scroll Effect API
	ScrollEffects.prototype = {

		/**
		 * Add scroll effects manager to elements.
		 *
		 * @param {Node|[Node...]} elms The element or array of elements.
		 */
		addElms: function( elms ) {
			var self = this;
			if ( ! $.isArray( elms ) ) {
				elms = [ elms ];
			}
			elms.map( function( element ) {
				if ( $ush.isNode( element ) ) {
					self.elms.push( new SE_Manager( element ) );
				}
			} );
		},

		/**
		 * Updates initial data for all elements.
		 */
		_handleUpdateElmsInitialData: function() {
			var self = this;
			self.elms.map( function( element ) {
				element.setInitialData();
			} );
		},

		/**
		 * Handler for viewport resize event.
		 *
		 * @event handler
		 */
		_handleResize: function() {
			var self = this;

			// Turn effects on or off for the current screen width
			var disableEffects = isDisableEffects();
			if ( _$temp.disableEffects !== disableEffects ) {
				_$temp.disableEffects = disableEffects;
				self.elms.map( function( element ) {
					element[ disableEffects ? 'removeEffects' : 'applyEffects' ]();
				} );
			}

			// If the body height has not changed, then exit
			var bodyHeight = $ush.parseInt( _body.clientHeight );
			if ( _$temp.bodyHeight === bodyHeight ) {
				return;
			}
			_$temp.bodyHeight = bodyHeight;
			self._handleUpdateElmsInitialData(); // updates initial data for all elements
		},

		/**
		 * Handler for the scroll event.
		 *
		 * @event handler
		 */
		_handleScroll: function() {
			var self = this;
			if ( isDisableEffects() ) {
				return;
			}
			self.elms.map( function( element ) {
				// If the node is outside the viewport, then skip it
				if ( ! element.isInViewport() ) {
					element.node.classList.remove( 'in_viewport' );
					return;
				}
				element.node.classList.add( 'in_viewport' );
				element.applyEffects();
			} );
		}

	};

	// Export API
	$us.scrollEffects = new ScrollEffects;

	/**
	 * @class SE_Manager - Scroll Effects Manager.
	 * @param {Node} node The node.
	 */
	function SE_Manager( node ) {
		var self = this;

		// Variables
		self.node = node;
		// Note: IEEE 754 standard (https://en.wikipedia.org/wiki/Signed_zero)
		self.offsetTop = -0; // offset top for synchronize effects
		self.nearestTop = -0; // nearest top of initial or current position
		self.initialData = {
			top: -0,
			height: -0,
		};
		self._config = {
			start_position: '0%', // distance from the bottom screen edge, where the element starts its animation
			end_position: '100%', // distance from the bottom screen edge, where the element ends its animation
		};

		// Load configuration
		var $node = $( node );
		$.extend( self._config, $node.data( 'us-scroll' ) || {} );
		$node.removeAttr( 'data-us-scroll' );

		// Set initial data
		self.setInitialData();

		// Set classes and css variable
		node.classList.toggle( 'in_viewport', self.isInViewport() );

		// Apply effects
		if ( ! isDisableEffects() ) {
			self.applyEffects();
		}

		// Important! Set after init node positions
		$ush.timeout( function() {
			node.style.setProperty( '--scroll-delay', self.getParam( 'delay' ) );
		}, 100 );
	}

	// Scroll Effects Manager API
	SE_Manager.prototype = {

		/**
		 * Set or update initial data.
		 */
		setInitialData: function() {
			var self = this,
				rect = $ush.$rect( self.node );

			self.initialData.height = rect.height;
			self.initialData.top = scroll().top + rect.top - $ush.parseFloat( self.style( '--translateY' ) );
		},

		/**
		 * Determines whether the element is in the viewport area.
		 *
		 * @return {Boolean} True if in viewport, False otherwise.
		 */
		isInViewport: function() {
			var self = this,
				rect = $ush.$rect( self.node ),
				initialTop = self.initialData.top - scroll().top,
				nearestTop = min( initialTop, rect.top ) - _window.innerHeight;

			self.offsetTop = rect.top;
			self.nearestTop = nearestTop;

			return (
				nearestTop <= 0
				&& ( max( initialTop, rect.top ) + rect.height ) >= 0
			);
		},

		/**
		 * Get or set a style property.
		 *
		 * @param {String} prop The property.
		 * @param {*} value The value [optional].
		 * @return {*} Returns values on get, nothing on set.
		 */
		style: function( prop, value ) {
			var elmStyle = this.node.style;
			if ( $ush.isUndefined( value ) ) {
				return elmStyle.getPropertyValue( prop );
			} else {
				elmStyle.setProperty( prop, $ush.toString( value ) );
			}
		},

		/**
		 * Get parameter values by param name.
		 * Note: The method supports dynamic date attributes in Edit Live.
		 *
		 * @param {String} name The param name.
		 * @param {*} defaultValue The default value.
		 * @return {*} Returns parameter values.
		 */
		getParam: function( name, defaultValue ) {
			var self = this;
			return (
				self.node.dataset[ name ]
				|| self._config[ name ]
				|| defaultValue
			);
		},

		/**
		 * Get position data for an element.
		 *
		 * @param {Number} offsetY The offset by axis relative to viewport.
		 * @param {Numner} distanceInPx The distance in viewing area (along any axis).
		 * @return {{}} Returns a data object.
		 */
		getPositionData: function( offsetY, distanceInPx ) {
			var self = this,
				// R = 100% - ( A1 / A2 * 100% )
				currentPosition = 100 - ( $ush.parseFloat( offsetY ) / $ush.parseFloat( distanceInPx ) * 100 ),
				startPosition = $ush.parseInt( self.getParam( 'start_position' ) ),
				endPosition = $ush.parseInt( self.getParam( 'end_position' ) );

			return {
				start: startPosition,
				current: $ush.limitValueByRange( currentPosition, 0, 100 ), // range: 0-100%
				end: endPosition,
				diff: ( endPosition - startPosition ),
			};
		},

		/**
		 * Get translate value based on arguments.
		 *
		 * @param {Number} offsetY The offset top.
		 * @param {Numner} distance The viewport height or width.
		 * @return {Number} Returns the offset position relative to the center.
		 */
		getTranslate: function( offsetY, distance, translateSpeed ) {
			var self = this,
				position = self.getPositionData( offsetY, distance ),
				currentPosition = position.current;

			// Set the "Animation Start Position" in viewport
			if ( position.start && floor( currentPosition ) <= position.start ) {
				currentPosition = position.start;
			}

			// Set the "Animation End Position" in viewport
			if ( position.end && floor( currentPosition ) >= position.end ) {
				currentPosition = position.end;
			}

			return ( currentPosition - /* center:(100%/2) */50 ) * _TRANSLATE_FACTOR_ * translateSpeed;
		},

		/**
		 * Apply the effects.
		 */
		applyEffects: function() {
			var self = this;
			self.setTranslateY();
			self.setTranslateX();
			self.setOpacity();
		},

		/**
		 * Remove the effects.
		 */
		removeEffects: function() {
			var self = this;
			[ '--translateY', '--translateX', '--opacity' ]
				.map( function( varName ) {
					self.style( varName, /* remove */'' );
				} );
		},

		/**
		 * Set vertical offset.
		 */
		setTranslateY: function() {
			var self = this;

			// If the speed is not set, then exit
			var translateSpeed = $ush.parseFloat( self.getParam( 'translate_y_speed' ) );
			if ( ! translateSpeed ) {
				return;
			}

			// Determine direction down
			var directionIsDown = self.getParam( 'translate_y_direction' ) === _DIRECTION_.DOWN;
			if ( ! directionIsDown ) {
				translateSpeed = -translateSpeed;
			}

			var elmHeight = self.initialData.height,
				// Get offset position
				translateY = self.getTranslate( self.offsetTop + elmHeight, _window.innerHeight + elmHeight, translateSpeed );

			// Set value to css variable
			self.style( '--translateY', translateY + 'px' );
		},

		/**
		 * Set horizontal offset.
		 */
		setTranslateX: function() {
			var self = this;

			// If the speed is not set, then exit
			var translateSpeed = $ush.parseFloat( self.getParam( 'translate_x_speed' ) );
			if ( ! translateSpeed ) {
				return;
			}

			// Determine direction
			var directionIsRight = self.getParam( 'translate_x_direction' ) === _DIRECTION_.RIGHT;
			if ( ! directionIsRight ) {
				translateSpeed = -translateSpeed;
			}

			var elmHeight = self.initialData.height,
				// Get offset position
				translateX = self.getTranslate( self.offsetTop + elmHeight, _window.innerHeight + elmHeight, translateSpeed );

			// Set value to css variable
			self.style( '--translateX', translateX + 'px' );
		},

		/**
		 * Set transparency.
		 */
		setOpacity: function() {
			var self = this;

			// Get opacity direction
			var opacityDirection = self.getParam( 'opacity_direction' );
			if ( ! opacityDirection ) {
				return;
			}

			// Get positions
			var elmHeight = self.initialData.height,
				viewportHeight = _window.innerHeight,
				offsetTop = viewportHeight + self.nearestTop + elmHeight,
				position = self.getPositionData( offsetTop, viewportHeight + elmHeight ),
				startPosition = position.start,
				currentPosition = $ush.limitValueByRange( round( position.current ), startPosition, position.end ); // range: start-end

			// Get opacity ( ( 100% / A1 ) * A2 ) / 100%
			var opacity = ( ( 100 / position.diff ) * ( currentPosition - startPosition ) ) / 100;
			if ( opacityDirection === 'in-out' ) {
				opacity = 1 - opacity; // reverse direction
			} else if ( opacityDirection === 'in-out-in' ) {
				opacity = ( 2 * opacity ) - 1;
			} else if ( opacityDirection === 'out-in-out' ) {
				opacity = ( opacity > 0.5 ? 2 : 0 ) - ( 2 * opacity );
			}
			self.style( '--opacity', $ush.limitValueByRange( abs( opacity ).toFixed( 3 ), 0, 1 ) );
		}
	};

	// Add to jQuery
	$.fn.usScrollEffects = function() {
		return this.each( function() {
			$us.scrollEffects.addElms( this );
		} )
	};

	// Init scroll effects
	$( function() {
		$( '[data-us-scroll]' ).usScrollEffects();
	} );

}( jQuery );
