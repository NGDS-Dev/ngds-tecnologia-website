/**
 * Available spaces:
 *
 * _window.$usbp - USBPreview class instance
 * _window.parent.$usb - Basic object for mounting and initializing all extensions of the builder
 * _window.parent.$usbcore - Auxiliary functions for the builder and his extensions
 * _window.parent.$usbdata - Data for import into the USBuilder
 * _window.$usbdata - Data for import into the USBuilderPreview
 * _window.$ush - US Helper Library
 * _window.$us - UpSolution Theme Core JavaScript Code
 *
 * Note: Double underscore `__funcname` is introduced for functions that are created through `$ush.debounce(...)`.
 */
! function( $, undefined ) {

	// Private variables that are used only in the context of this function, it is necessary to optimize the code
	var _window = window,
		_document = document,
		_undefined = undefined;

	// Math API
	var abs = Math.abs,
		pow = Math.pow,
		ceil = Math.ceil,
		floor = Math.floor;

	// Get parent window
	var parent = _window.parent || {};

	// If there is no parent window object, we will complete the execute script
	if ( ! parent.$usb ) {
		return;
	}
	var $usb = parent.$usb,
		$usbcore = parent.$usbcore || {}; // get $usbcore helpers

	// Check for is set availability objects
	[ '$us', '$usbdata' ].map( function( name ) {
		_window[ name ] = _window[ name ] || {};
	} );
	_window.$ush = _window.$ush || parent.$ush || {};

	/**
	 * @var {{}} Direction constants
	 */
	const _DIRECTION_ = {
		BOTTOM: 'bottom',
		LEFT: 'left',
		RIGHT: 'right',
		TOP: 'top',
		UNKNOWN: ''
	};

	/**
	 * @var {{}} Axes of a two-dimensional coordinate system
	 */
	const _2D_AXES_ = {
		x: 'x-axis', // the horizontal axis is called the abscissa axis (X axis)
		y: 'y-axis' // the vertical axis - ordinate axis (Y axis)
	};

	/**
	 * @var {{}} Possible containers
	 */
	const _CONTAINER_ = {
		ANY: 'any', // any first container
		CHILD: 'child', // child container, for example: vc_column, vc_column_inner, vc_tta_section, etc
		ROOT: 'root' // root container, for example: vc_row, vc_row_inner, vc_tabs etc
	};

	/**
	 * @link https://developer.mozilla.org/en-US/docs/Web/API/MouseEvent/button#value
	 * @var {{}} Indicators of the buttons that were pressed with the mouse to trigger the event
	 */
	const _MOUSE_EVENT_BUTTON_ = {
		MAIN: 0 // main button pressed, usually the left button or the un-initialized state
	};

	/**
	 * @var {{}} Default data
	 */
	var _$$default = {
		customPrefix: 'us_custom_' // for design options
	}

	/**
	 * @class USBPreview - The class displays page settings and shortcode changes
	 */
	var USBPreview = function() {
		var self = this;

		// Variables
		body = _document.body;
		self.fps = 1000 / 200;
		self._preloaders = {}; // all active preloaders
		self._highlights = {}; // all highlights

		// Elements
		self.$document = $( _document );
		self.$body = $( body );
		self.$htmlBody = $( 'html,body', _document );
		self.highlight = $( '.usb-hover', body )[0];
		self.elmMainContainer = self.getElmNode( $usb.builder.mainContainer );

		// Bondable events
		self._events = {
			// Track Drag & Drop events
			clickedControlsHoverPanel: self._clickedControlsHoverPanel.bind( self ),
			endDrag: self._endDrag.bind( self ),
			maybeDrag: self._maybeDrag.bind( self ),
			maybeStartDrag: self._maybeStartDrag.bind( self ),

			// Other handlers
			DOMContentLoaded: self._DOMContentLoaded.bind( self ),
			elmAnimationEnd: self._elmAnimationEnd.bind( self ),
			elmAnimationStart: self._elmAnimationStart.bind( self ),
			elmCopy: self._elmCopy.bind( self ),
			elmPaste: self._elmPaste.bind( self ),
			elmDelete: self._elmDelete.bind( self ),
			elmDuplicate: self._elmDuplicate.bind( self ),
			elmMove: self._elmMove.bind( self ),
			elmSelected: self._elmSelected.bind( self ),
			linkClickHandler: self._linkClickHandler.bind( self ),
			stop: self._stop.bind( self ),

			// Alias for call on events
			autoSetHighlightsPosition: $ush.debounce( self.setHighlightsPosition.bind( self ), self.fps )
		};

		// This event is needed to get various data from the iframe
		_window.onmessage = $usb._onMessage.bind( self );

		// When leave the window with the cursor
		_window.onmouseout = $ush.debounce( function( e ) {
			e = e || _window.event;
			var node = e.relatedTarget || e.toElement;
			if ( ! node || $ush.toLowerCase( node.nodeName ) === 'html' ) {
				self._mouseLeavesIframe.call( self, e );
			}
		}, 1 );

		// Highlight position updates on window resize or page scroll
		_window.onresize = self._events.autoSetHighlightsPosition;
		_document.onscroll = self._events.autoSetHighlightsPosition;

		// Disable Drag & Drop on body
		self.$body.attr( 'draggable', 'false' );

		// Events
		self.$document
			// The event fires when the initial HTML document has been completely loaded and parsed,
			// without wait for stylesheets, images, and subframes to finish load
			.ready( self._events.DOMContentLoaded )
			// Capture keyboard shortcuts
			.on( 'keydown', $usb._events.keydown )
			// Disabled dragstart from default
			.on( 'dragstart', function() { return false } )
			// Highlight actions
			.on( 'mousedown', '.usb-hover-panel', self._events.clickedControlsHoverPanel )
			.on( 'mouseup', '.ui-icon_duplicate', self._events.elmDuplicate )
			.on( 'mouseup', '.ui-icon_copy', self._events.elmCopy )
			.on( 'mouseup', '.ui-icon_paste', self._events.elmPaste )
			.on( 'mouseup', '.ui-icon_delete', self._events.elmDelete )
			// Track Drag & Drop events
			.on( 'mousedown', self._events.maybeStartDrag )
			.on( 'mousemove', self._events.maybeDrag )
			.on( 'mouseup', $ush.debounce( self._events.endDrag, 2 ) ) // call after `_events.elmSelected`
			// Other events
			.on( 'mouseup', '[data-usbid]', $ush.debounce( self._events.elmSelected, 1 ) ) // call before `_events.endDrag`
			.on( 'mousemove', $ush.debounce( self._events.elmMove, self.fps ) )
			.on( 'mouseleave', $ush.debounce( self._events.elmLeave, self.fps ) )
			// Handlers for css animation in elements
			.on( 'animationstart', '[data-usbid]', $ush.debounce( self._events.elmAnimationStart, 1 ) )
			.on( 'animationend', '[data-usbid]', $ush.debounce( self._events.elmAnimationEnd, 1 ) )
			// When the cursor is within `header` or `footer` then hide all highlights
			.on( 'mouseenter', '.l-header, .l-footer', $ush.debounce( self.hideHighlight.bind( self ), 100 ) )
			// Watch content changes (via us scripts)
			.on( 'contentChange', '.l-canvas:first', self._events.autoSetHighlightsPosition );

		self.$body
			// Handler for all link clicks
			.on( 'click', 'a', self._events.linkClickHandler );

		/**
		 * Private events
		 * The events that can come from the main collector window
		 */
		for ( var handler in self._$events ) {
			if ( $.isFunction( self._$events[ handler ] ) ) {
				self.on( handler, self._$events[ handler ].bind( self ) );
			}
		}
	};

	/**
	 * @var {Prototype}
	 */
	var prototype = USBPreview.prototype;

	/**
	 * Transports for send messages between windows or objects
	 */
	$.extend( prototype, $ush.mixinEvents, {
		/**
		 * Send a message to the parent window
		 *
		 * @param {String} eventType A string contain event type
		 * @param {[]} extraParams Additional parameters to pass along to the event handler
		 * @chainable
		 */
		postMessage: function( eventType, extraParams ) {
			parent.postMessage( JSON.stringify( [ /* namespace */'usb', eventType, extraParams ] ) );
		}
	} );

	/**
	 * Functionality for implement Drag & Drop
	 * All the necessary methods that are somehow involved in this approach
	 */
	$.extend( prototype, {
		// The number of pixels when drag after which the movement will be initialized
		_dragStartDistance: $usb.builder._dragStartDistance || 8,

		/**
		 * Get all data from the event that is needed for Drag & Drop
		 *
		 * @private
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 * @return {{}} The event data
		 */
		_extractEventData: function( e ) {
			return {
				clientX: e.clientX,
				clientY: e.clientY,
				pageX: e.pageX,
				pageY: e.pageY,
				target: e.target
			};
		},

		/**
		 * Event handler for click on any element in the highlight controls on HoverPanel
		 *
		 * @private
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_clickedControlsHoverPanel: function( e ) {
			var self = this,
				elmId = $( e.currentTarget ).closest( '.usb-hover' ).data( 'elmid' );
			if ( ! elmId ) {
				return;
			}

			// Set element data on click on hover panel
			self.hoveredElmId = elmId;
			self.hoveredElm = self.getElmNode( elmId );

			// The we activate observations to start move the element
			$( self.hoveredElm )
				.trigger( 'mousedown', [ e.pageX, e.pageY ] );
		},

		/**
		 * Handler for check movement
		 *
		 * @private
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 * @param {Number} pageX [optional] The coordinate at which the mouse was clicked, relative to the left edge of the document
		 * @param {Number} pageY [optional] The coordinate at which the mouse was clicked, relative to the top edge of the document
		 */
		_maybeStartDrag: function( e, pageX, pageY ) {
			e.stopPropagation();
			var self = this;

			if (
				// Stop process for preview mode
				! $usb.panel.isShow()
				// If there drag in the parent window, then we will exit this method
				|| $usb.builder.isParentDragging()
				// Don't start move start for all buttons except left mouse button or trackpad
				|| e.button !== _MOUSE_EVENT_BUTTON_.MAIN
			) {
				return;
			}

			// Define the element to move
			var target = ( self.hoveredElm && $usbcore.$hasClass( e.target, 'usb-hover-panel-name' ) )
				? self.hoveredElm
				: self._getNearestNode( e.target );
			if ( ! target ) {
				return;
			}

			// Destroy all previous cache data if any
			self.clearDragAssets();
			// Save cache intermediate data at the time of Drag & Drop
			$usbcore.cache( 'iframeDrag' ).set( {
				currentId: null, // id of the element being moved (has an alias if given)
				currentParentId: null, // parent element id relative to currentId
				isDragging: false, // active when the element is in the process of being moved
				isParentTab: false, // move happens in the context of tabs (Tabs/Tour)
				startDrag: true,
				startX: e.pageX || pageX || 0,
				startY: e.pageY || pageY || 0,
				target: target // the node of the element being moved
			} );

			// Note: Firefox has problems using the `mousemove` event where it is not possible to
			// get the element that is under the cursor if it has `overflow: hidden`
			if ( $ush.isFirefox ) {
				[ target ].concat( $ush.toArray( target.getElementsByTagName( '*' ) ) ).map( function( node ) {
					if ( ! $ush.isNode( node ) || $usbcore.$hasClass( node, 'usb_firefox_clip' ) ) return;
					if ( _window.getComputedStyle( node, /* pseudoElt */null ).getPropertyValue( 'overflow' ) === 'hidden' ) {
						$usbcore.$addClass( node, 'usb_firefox_clip' );
					}
				} );
			}
		},

		/**
		 * Position selection handler for move element
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @private
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 *
		 * TODO: Improve id structure consider aliases
		 */
		_maybeDrag: function( e ) {
			var self = this,
				target = e.target,
				currentPreviewOffset = $usb.preview.getCurrentOffset(),
				// Get offset for transit
				transit = {
					pageX: ceil( currentPreviewOffset.x + e.pageX - _window.scrollX ), // x-axis
					pageY: ceil( currentPreviewOffset.y + e.pageY - _window.scrollY ) // y-axis
				};

			if ( ! $ush.isFirefox && $usb.builder.isParentDragging() ) {
				// Determination of the place where the element can fall
				self._maybeDrop( self._extractEventData( e ) );
				// Set position for transit when add a new element
				$usb.builder.setTransitPosition( transit.pageX, transit.pageY );
				return;
			}

			// Get cache data
			var dragData = $usbcore.cache( 'iframeDrag' ).data();
			if ( $.isEmptyObject( dragData ) || ! dragData.startDrag || ! dragData.target ) {
				return;
			}

			// Get ffsets from origin along axis X and Y
			var diffX = abs( dragData.startX - e.pageX ),
				diffY = abs( dragData.startY - e.pageY );

			// The check the distance of the germinated mouse and if it is more than
			// the specified one, then activate all the necessary methods
			if ( diffX >= self._dragStartDistance || diffY >= self._dragStartDistance ) {

				// Get and current id
				dragData.currentId = $usb.builder.getElmId( dragData.target );

				// Get base currentId without alias
				var baseCurrentId = $usb.builder.isAliasElmId( dragData.currentId )
					? $usb.builder.removeAliasFromId( dragData.currentId )
					: dragData.currentId;

				if ( $usb.builder.isMode( 'editor' ) ) {

					// Flush active move data
					$usbcore.cache( 'dragProcessData' ).flush();

					// Select mode of move elements
					$usb.builder.setMode( 'drag:move' );
					// Add flag that drag is activated
					dragData.isDragging = true;
					// Get parent element id
					dragData.currentParentId = $usb.builder.getElmParentId( baseCurrentId );
					// Move in sections in the context of Tabs/Tour
					dragData.isParentTab = (
						!! $usb.builder.isElmTab( dragData.currentParentId )
						// Note: Tabs can turn into accordions on different window sizes
						&& ! $usbcore.$hasClass( self.getElmNode( dragData.currentParentId ), 'accordion' )
					);
					// Show the transit
					$usb.builder.showTransit( dragData.currentId );
					// Add helpers classes for visual control
					$usbcore
						.$addClass( dragData.target, 'usb_transit' )
						.$addClass( _document.body, 'usb_dragging' );
					// Hide all highlights
					self.hideEditableHighlight();
				}

				if (
					! $usb.builder.isMode( 'drag:move' )
					// Do not move sections in the context of tabs (only buttons are allowed to move)
					|| ( dragData.isParentTab && ! $usb.builder.isAliasElmId( dragData.currentId ) )
					// Elements outside of the container, such as the header, footer, or sidebar
					|| $usb.builder.isOutsideMainContainer( baseCurrentId )
				) {
					self.clearDragAssets()
					return;
				}

				// Save content to a temporary variable and remove the float
				if ( $usb.builder.isEmptyTempContent() ) {
					// The save content to temp
					$usb.builder.saveTempContent();
					// Temporarily remove the element to be moved from the content
					$usb.builder.pageData.content = ( '' + $usb.builder.pageData.content )
						.replace( $usb.builder.getElmShortcode( baseCurrentId ), '' );
				}

				// Determination of the place where the element can fall
				self._maybeDrop( self._extractEventData( e ) );

				// Set position for transit when move element
				$usb.builder.setTransitPosition( transit.pageX, transit.pageY );
			}
		},

		/**
		 *
		 * Determines the location where the element will be drag
		 * This method is called from both the current window and the parent.window
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @private
		 * @param {{}} data The data from event
		 */
		_maybeDrop: function( data ) {
			var self = this;
			if (
				! data
				|| ! data.target
				|| ! $usb.builder.isMode( 'drag:add', 'drag:move' )
				|| $usb.builder.hasDragScrolling() // skip target search when scroll
			) {
				return;
			}

			var // Get data from temporary storage (usually intermediate data)
				dragData = $usbcore.cache( 'iframeDrag' ).data(),
				// This is the id of the new or moved element
				currentId = $usb.builder.isMode( 'drag:add' )
					? $usb.builder.getNewElmId()
					: dragData.currentId;

			var // The target the cursor is over
				target = self._getNearestNode( data.target ) || self.elmMainContainer,
				// The real ID of the target the cursor is over
				targetId = $usb.builder.getElmId( target ),
				// The first parent container of the target that the cursor is over
				targetContainer,
				// ID of the container within which the element will be added or moved
				targetContainerId;

			// Get the target when move elements strictly attached to
			// the parent, move only in the context of the parent
			if (
				$usb.builder.isChildElmContainer( currentId )
				&& $usb.builder.isValidId( dragData.currentParentId )
			) {
				targetContainerId = dragData.currentParentId;
				targetContainer = self.getElmNode( targetContainerId );

				// Get the container the cursor is over
			} else if ( $usb.builder.isElmContainer( targetId ) ) {
				targetContainerId = targetId;
				targetContainer = target;

				// Get containers based on the current target the cursor
				// is over, if the target is not a container
			} else {
				targetContainer = self._getNearestNode( target,
					$usb.builder.isChildElmContainer( currentId )
						? _CONTAINER_.ROOT
						: _CONTAINER_.ANY
				);
				targetContainerId = $usb.builder.getElmId( targetContainer );
			}

			// Get a valid container element (on error it's elmMainContainer)
			targetContainer = targetContainer || self.elmMainContainer;

			// If element and target are `vc_row` then change target to main container
			if (
				$usb.builder.isRow( targetContainerId )
				&& (
					$usb.builder.isRow( currentId )
					|| $usb.templates.isTemplate( currentId ) // for section templates
				)
			) {
				targetContainerId = $usb.builder.mainContainer;
			}

			// Remove alias from target container ID
			if ( $usb.builder.isAliasElmId( targetContainerId ) ) {
				targetContainerId = $usb.builder.removeAliasFromId( targetContainerId );
			}

			// Get a list of all children of the container where mouse movement occurs
			var children = $usb.builder.getElmChildren( targetContainerId );

			// Logic for define cursor on container borders
			if ( $usb.builder.isElmContainer( targetContainerId ) ) {
				// Get the root container in which the location of the cursor on
				// the borders will be determined
				var _parentTargetContainerId = targetContainerId;
				for ( var i = 3; i > 0; i-- ) {
					if ( ! $usb.builder.isChildElmContainer( _parentTargetContainerId ) ) {
						break;
					}
					_parentTargetContainerId = $usb.builder.getElmParentId( targetContainerId );
				}
				// Get a valid container ID
				_parentTargetContainerId = $usb.builder.getValidContainerId( _parentTargetContainerId );

				var targetHeight = $ush.parseInt( $ush.$rect( target ).height ),
					// If there are elements in the container and the latter is greater than 50, then increase
					// the boundary, which will help improve the user interface
					// Note: For empty containers, do not set a large value, as this can lead to problems
					borderAround = ( children.length && targetHeight >= 50 ? 15 : 5 ),
					// Get the border of the container if the cursor is in it
					borderContainerToCursor = self._getBorderContainerToCursor(
						self.getElmNode( _parentTargetContainerId ),
						data.clientX,
						data.clientY,
						borderAround
					);

				// If the cursor is on the border of the container, then redefine targets
				// to support add before and after the container
				if ( _DIRECTION_.UNKNOWN !== borderContainerToCursor ) {
					// Get a valid container ID (on error it's mainContainer)
					_parentTargetContainerId = $usb.builder.getValidContainerId(
						$usb.builder.getElmParentId( _parentTargetContainerId )
					);
					// Redefine target container
					targetContainer = self.getElmNode( _parentTargetContainerId );
					targetContainerId = _parentTargetContainerId;
				}
			}

			// Get alias and base currentId without alias
			var aliasId, baseCurrentId = currentId;
			if ( $usb.builder.isAliasElmId( currentId ) ) {
				aliasId = $usb.builder.getAliasFromId( currentId );
				baseCurrentId = $usb.builder.removeAliasFromId( currentId );
			}

			// Determine if it is a descendant of itself
			if ( $usb.builder.hasSameTypeParent( baseCurrentId, targetContainerId ) ) {
				return;
			}

			// Strict mode is a hard dependency between elements!
			var strictMode = (
				// The check if the moved element is a tab, accordion, tour or vc_column(_inner), if so, then enable strict mode
				$usb.builder.isChildElmContainer( baseCurrentId )
				// Allow add any non-row element to the container only if there is no content
				|| (
					! $usb.builder.isEmptyContent()
					&& $usb.builder.isMainContainer( targetContainerId )
					&& ! (
						$usb.builder.isRow( baseCurrentId )
						|| $usb.templates.isTemplate( baseCurrentId ) // for section templates
					)
				)
			);

			// Check if the element can be a child of the hover element
			if ( ! $usb.builder.canBeChildOf( baseCurrentId, targetContainerId, strictMode ) ) {
				return;
			}

			var // Determine if the target container is a tab
				// Note: Tabs have the ability to turn into accordions, so you need to check
				// for the presence of classes, since the usbis can be for tabs
				targetContainerIsTab = (
					$usb.builder.isElmTab( targetContainerId )
					&& ! $usbcore.$hasClass( targetContainer, 'accordion' )
				),
				// This is the index or sequential number of add an element to the list of nodes
				currentIndex = 0;

			// Update data as target have been changed
			if ( _DIRECTION_.UNKNOWN !== borderContainerToCursor ) {
				children = $usb.builder.getElmChildren( targetContainerId );
			}

			// If there is an alias and it is a tab button then add an alias to all children,
			// this is necessary to determine the position of the tab buttons
			if ( aliasId && aliasId === $usb.config( 'aliases.tab' ) ) {
				children = children.map( function( id ) {
					return $usb.builder.addAliasToElmId( aliasId, id );
				} );
			}

			// Stabilizers provide more stable operation when move columns, especially for multi-line cases
			if ( $usb.builder.isMode( 'drag:move' ) && $usb.builder.isColumn( baseCurrentId ) ) {
				// Exit if cursor is out of row
				if ( ! self.isInsideNode( self.getElmNode( dragData.currentParentId ), data.pageX, data.pageY ) ) {
					return;
				}
				// Exit if cursor is within element
				if (
					$usbcore.$hasClass( dragData.target, 'usb_transit' )
					&& self.isInsideNode( dragData.target, data.pageX, data.pageY )
				) {
					return;
				}
			}

			// Positional calculations are based on the distance of the cursor from the children
			if ( children.length ) {
				var _nearestSegment;

				for ( var elmIndex in children ) {
					var elmId = children[ elmIndex ],
						elm = self.getElmNode( elmId );
					if ( ! elm ) {
						continue;
					}
					// Get the coordinates of an element, taking into account all scrolls
					var elmRect = $ush.$rect( elm ),
						elmX = ( elmRect.x + _window.scrollX ),
						elmY = ( elmRect.y + _window.scrollY );

					// Fixed a case where an element has an additional indent
					// on the X-axis, which violates the calculation of the
					// formula `(y2 - y1)² + (x2 - x1)²`
					elmX -= $ush.parseInt( ( elm.currentStyle || _window.getComputedStyle( elm ) ).marginLeft );

					// All columns that are not on the same line will be skipped
					// Note: Stabilizer when work with columns
					if (
						$usb.builder.isColumn( baseCurrentId )
						&& ! ( data.pageY >= elmY && data.pageY <= ( elmY + elmRect.height ) )
					) {
						continue;
					}

					// The formula `(y2 - y1)² + (x2 - x1)²` allows you to determine the nearest element
					var _segmentLength = Math.sqrt( pow( data.pageY - elmY, 2 ) + pow( data.pageX - elmX, 2 ) );
					if ( ! _nearestSegment || _nearestSegment > _segmentLength ) {
						_nearestSegment = _segmentLength;
						currentIndex = parseInt( elmIndex ); // save nearest index to cursor
					}
				}
			}

			// Positional calculations for all elements, where the position of
			// the cursor relative to the target is determined
			if ( currentIndex === children.length -1 || $usb.builder.isColumn( baseCurrentId ) ) {
				var only_x_axis = $usb.config( 'moving_only_x_axis', [] )
					.indexOf( $usb.builder.getElmName( dragData.parentId ) ) > -1;

				// Note: Tabs have the ability to turn into accordions, so you need to check
				// for the presence of classes, since the usbis can be for tabs
				if (
					$usb.builder.isElmTab( targetContainerId )
					&& $usbcore.$hasClass( targetContainer, 'accordion' )
				) {
					only_x_axis = false;
				}

				// Get сurrent cursor direction in target
				var currentCursorDirectionInTarget = self._getCurrentCursorDirectionInTarget(
					self.getElmNode( children[ currentIndex ] ),
					data.clientX,
					data.clientY,
					// Axes of a two-dimensional coordinate system
					_2D_AXES_[ only_x_axis ? 'x' : 'y' ]
				);
				// If the cursor is more towards the bottom of the element then put the
				// index after it, otherwise the index will be before the element
				if ( $usbcore.indexOf( currentCursorDirectionInTarget, [ _DIRECTION_.BOTTOM, _DIRECTION_.RIGHT ] ) > -1 ) {
					currentIndex += 1;
				}
			}

			// If the target container is a TTA section and it is hidden,
			// then all elements will be appended to the end
			if ( self.isHiddenTab( targetContainerId ) ) {
				currentIndex = children.length;
			}

			// Save the last found container
			if ( dragData.lastFoundContainer !== targetContainer ) {
				$usbcore
					.$removeClass( dragData.lastFoundContainer, 'usb_dropcontainer' )
					.$addClass( targetContainer, 'usb_dropcontainer' );
				dragData.lastFoundContainer = targetContainer
			}

			// Get base targetContainerId without alias
			if ( $usb.builder.isAliasElmId( targetContainerId ) ) {
				targetContainerId = $usb.builder.removeAliasFromId( targetContainerId );
			}

			// Save insert data to a temp variable
			dragData.parentId = targetContainerId;
			dragData.currentId = baseCurrentId;
			dragData.currentIndex = currentIndex;

			// Save data for Firefox since endDrag in the frame window does not work
			if ( $ush.isFirefox ) {
				$usbcore.cache( 'drag' ).set( {
					parentId: targetContainerId,
					currentId: currentId,
					currentIndex: currentIndex
				} );
			}

			// Get insert position.
			var insert = $usb.builder.getInsertPosition( targetContainerId, currentIndex );

			// Additional check for `insert` changes to reduce the number of document calls
			if ( $usbcore.comparePlainObject( insert, dragData.lastInsert ) ) {
				return;
			}
			dragData.lastInsert = $ush.clone( insert );

			// Create new dropplace element
			if ( $usb.builder.isColumn( currentId ) ) {
				dragData.place = self.getElmNode( currentId );
			} else {
				$usbcore.$remove( dragData.place ); // remove old dropplace node
				dragData.place = _document.createElement( 'div' );
				dragData.place.className = 'usb_dropplace';

				// // This is where additional settings are added for the vertical line when move containers
				var isHorizontalWrapper = ( $usb.builder.getElmName( targetContainerId ) === 'hwrapper' );
				if ( $usb.builder.isRootElmContainer( targetContainerId ) || isHorizontalWrapper || dragData.isParentTab ) {
					if ( isHorizontalWrapper ) {
						// Add height to the wrapper as elements inside it are not blocks
						dragData.place.style.height = $ush.$rect( targetContainer ).height + 'px';
					}
				}
			}

			// Explicit target replacement for elements of type Tabs that allows
			// you to display the movement of the tab buttons
			if ( dragData.isParentTab && aliasId ) {
				if ( $usb.builder.isValidId( insert.parent ) ) {
					insert.parent = ( $usbcore.indexOf( insert.position, [ 'prepend', 'append' ] ) > -1 )
						? self.getElmNode( currentId ).parentNode || targetContainer
						: $usb.builder.addAliasToElmId( aliasId, insert.parent );
				}
			}

			// Add a temporary container to the place where the item will be added
			self.trigger( 'insertElm', [ insert.parent, insert.position, dragData.place ] );
		},

		/**
		 * End a drag
		 *
		 * @private
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_endDrag: function( e ) {
			var self = this;

			// Check if the mode is correct
			if ( ! $usb.builder.isMode( 'drag:add', 'drag:move', /* for Firefox mode `drag:add` */'editor' ) ) {
				return;
			}

			/**
			 * Kill current event
			 * Note: For FF, we ignore stop since the transmitted object is data, not an event object,
			 * all due to the peculiarities of the FF from work the iframe
			 */
			if ( ! $ush.isFirefox ) {
				self._events.stop( e );
			}

			// Reset the hover element, this will cancel the open of mode
			if ( $usb.builder.isMode( 'drag:add', 'drag:move' ) ) {
				self.hoveredElm = null;
			}

			// Get drag data
			var dragData = $usbcore.cache( 'iframeDrag' ).data();

			// Note: With a quick change of events, it may turn out that the code in the
			// intermediate event may not have time to be executed or during the renderer
			// or other factors, so we will do a control check
			if ( $usb.builder.isAliasElmId( dragData.currentId ) ) {
				dragData.currentId = $usb.builder.removeAliasFromId( dragData.currentId );
			}

			// Duplicate the signal in the parent window for correct completion
			if ( $usb.builder.isParentDragging() ) {
				// Move the data to add a new element
				$usbcore.cache( 'drag' ).set( {
					parentId: dragData.parentId,
					currentId: dragData.currentId,
					currentIndex: dragData.currentIndex
				} );
				self.postMessage( 'builder.endDrag' );
				return;
			}

			// Reset all data
			$usb.builder.setMode( 'editor' );

			// If the move is not activated or not started then clear all assets
			if ( ! dragData.startDrag || ! dragData.isDragging ) {
				// Clear all asset and temporary data to move
				self.clearDragAssets();
				// Select an element for edit
				if (
					! dragData.isDragging
					&& $usbcore.$hasClass( e.target, 'usb-hover-panel-name' )
				) {
					// Hide highlight for editable element
					self.hideEditableHighlight();
					// Run a trigger to initialize shortcode mode
					var $highlight = $( e.target ).closest( '.usb-hover' );
					if ( $highlight.length ) {
						var elmId = $highlight.data( 'elmid' );
						self.hoveredElm = self.getElmNode( elmId );
						$( self.hoveredElm ).trigger( 'mouseup' );
					}
				}
				// End execution
				return;
			}

			// Restore the current content that contains the float element
			$usb.builder.restoreTempContent();

			// Move the element to a new position
			if ( !! dragData.parentId && !! dragData.currentId ) {
				$usb.builder.moveElm( dragData.currentId, dragData.parentId, dragData.currentIndex || 0 );
				// If the final container is a TTA section then open this section
				if ( $usb.builder.isElmSection( dragData.parentId ) ) {
					self.openSectionById( dragData.parentId );
				}
				// Trigger the content change event
				$us.$canvas.trigger('contentChange');
			}

			// Clear all asset and temporary data to move
			self.clearDragAssets();
		},

		/**
		 * Clear all asset and cache data to move
		 */
		clearDragAssets: function() {
			var self = this;

			// Get drag data
			var dragData = $usbcore.cache( 'iframeDrag' ).data();
			if ( $.isEmptyObject( dragData ) ) {
				return;
			}

			$usbcore
				// Remove classes
				.$removeClass( dragData.target, 'usb_transit' )
				.$removeClass( dragData.lastFoundContainer, 'usb_dropcontainer' )
				.$removeClass( _document.body, 'usb_dragging' );

			// Remove dropplace element
			// Note: Row columns are not deleted as they are pointers
			if ( ! $usb.builder.isColumn( dragData.currentId ) ) {
				$usbcore.$remove( dragData.place );
			}

			$usb.builder.hideTransit(); // hide the transit
			$usbcore.cache( 'iframeDrag' ).flush() // flush data

			// When the element is moved and the cursor leaves the preview area,
			// the text may be captured, so when clear the data, clear the buffer
			if ( $usb.builder.isMode( 'drag:move' ) ) {
				if ( _window.getSelection ) {
					_window.getSelection().removeAllRanges();
				} else if ( _document.selection ) {
					_document.selection.empty();
				}
			}
		},

		/**
		 * Auto-scroll while drag
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @private
		 * @event handler
		 * @param {Mixed} direction The scroll direction
		 * @param {Number} acceleration The scroll acceleration
		 */
		_scrollDragging: $ush.debounce( function( direction, acceleration ) {
			var self = this,
				scrollHeight = $ush.parseInt( _document.body.scrollHeight ),
				scrollTo = _undefined, // No value does not start animation
				scrollTop = $ush.parseInt( _window.scrollY );

			// Stop the animation here
			if ( self.$htmlBody.is( ':animated' ) ) {
				self.$htmlBody.stop( /* clearQueue */true );
			}

			if ( direction === _DIRECTION_.TOP && scrollHeight && scrollTop > 0 ) {
				scrollTo = 0; // up of page
			}
			else if (
				direction === _DIRECTION_.BOTTOM
				&& scrollTop < $ush.parseInt( scrollHeight - _window.innerHeight )
			) {
				scrollTo = scrollHeight; // down the page
			}

			if ( ! $.isNumeric( scrollTo ) ) {
				$usb.builder.removeDragScrollData(); // remove a drag scroll data
				return;
			}

			var duration = 2000, // duration without acceleration to end point
				maxAcceleration = 4; // multiplicity of maximum acceleration

			// Get acceleration and check max {acceleration}x
			acceleration = $ush.parseInt( acceleration );
			if ( acceleration > maxAcceleration ) {
				acceleration = maxAcceleration;
			}

			// The initiate animation
			self.$htmlBody.animate(
				{ scrollTop: scrollTo },
				{
					// Duration with acceleration
					duration: floor( duration / ( acceleration || /* default */1 ) ),
					// After the animation is complete, call the scroll completion handler
					complete: $usb.builder.stopDragScrolling.bind( $usb )
				}
			);

			self.hideHighlight(); // hide the highlight
		}, 1 )
	} );

	/**
	 * Functionality for the implementation of highlights
	 * TODO: Improve highlights and reduce the load on the DOM in #2313
	 */
	$.extend( prototype, {
		/**
		 * Show the highlight
		 * This method is called many times, so the implementation should be Vanilla JS
		 */
		showHighlight: function() {
			var self = this;
			if (
				! $usb.builder.isMode( 'editor' )
				|| ! $usb.builder.isValidId( self.hoveredElmId )
			) {
				return;
			}
			var parentId = self.hoveredElmId,
				iteration = 0;
			// Get base parentId without alias
			if ( $usb.builder.isAliasElmId( parentId ) ) {
				parentId = $usb.builder.removeAliasFromId( parentId );
			}
			while ( parentId !== $usb.builder.mainContainer && parentId !== null ) {
				if ( iteration++ >= /* max number of iterations */1000 ) {
					break;
				}

				// Add a clone for the new found element
				self._createHighlight( parentId );

				// Show highlight
				var item = self._highlights[ parentId ];
				if ( $( self.getElmNode( parentId ) ).is( ':visible' ) ) {
					item.active = true;
					item.highlight.style.display = 'block';
				}

				/**
				 * @var {String|null} Get next parent elm
				 */
				parentId = $usb.builder.getElmParentId( parentId );
			}
			// Set the highlight position
			self.setHighlightsPosition.call( self );
		},

		/**
		 * Hide the highlight
		 * This method is called many times, so the implementation should be Vanilla JS
		 */
		hideHighlight: function() {
			var self = this;
			if ( $.isEmptyObject( self._highlights ) ) {
				return;
			}
			for ( var elmId in self._highlights ) {
				var item = self._highlights[ elmId ];
				item.active = false;
				item.highlight.style.display = 'none';
			}
			self.hoveredElm = null;
			self.hoveredElmId = null;
		},

		/**
		 * Set the highlights position
		 * This method is called many times, so the implementation should be Vanilla JS
		 */
		setHighlightsPosition: function() {
			var self = this;
			if (
				! $usb.builder.isMode( 'editor' )
				|| $.isEmptyObject( self._highlights )
			) {
				return;
			}

			for ( var elmId in self._highlights ) {
				if ( ! $usb.builder.isValidId( elmId ) ) {
					continue;
				}
				var item = self._highlights[ elmId ],
					// Receive at this stage is necessary because the elements can be completely rebooted
					elm = self.getElmNode( elmId );
				if (
					! $ush.isNode( elm )
					|| (
						! item.active
						&& ! item.editable
					)
				) {
					continue;
				}

				var // If there are negative margins, we will take this into account when highlighting
					negativeTop = elm.offsetTop < 0 ? abs( elm.offsetTop ) : 0,
					negativeLeft = elm.offsetLeft < 0 ? abs( elm.offsetLeft ) : 0,

					elmRect = $ush.$rect( elm ),
					cssProps = {
						height: elmRect.height - negativeTop,
						left: elmRect.left + negativeLeft + ( _window.pageXOffset || elm.scrollLeft ),
						top: elmRect.top + negativeTop + ( _window.pageYOffset || elm.scrollTop ),
						width: elmRect.width - negativeLeft
					};

				// Set css props
				$( item.highlight ).css( cssProps );

				// UX improvement when the element width is less then hover panel
				$usbcore.$toggleClass( item.highlight, 'small', ( elmRect.width < 75 ) );
			}
		},

		/**
		 * Show highlight for editable element
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 */
		showEditableHighlight: function( id ) {
			var self = this;
			if ( ! $usb.builder.isValidId( id ) ) {
				return;
			}
			// Hide highlight for editable element
			self.hideEditableHighlight();
			// Get highlight object
			var item = self._highlights[ id ];
			// Create new highlight
			if ( ! item ) {
				self.hideHighlight();
				item = self._createHighlight( id );
				if ( item ) {
					item.active = true;
				}
				self.setHighlightsPosition();
			}
			// Show editable mode
			if ( item ) {
				item.editable = true;
				$usbcore.$addClass( item.highlight, 'usb_editable' );
			}
		},

		/**
		 * Hide highlight for editable element
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1" (Optional parameter)
		 */
		hideEditableHighlight: function() {
			var self = this;
			if ( $.isEmptyObject( self._highlights ) ) {
				return;
			}
			var id = '' + arguments[ 0 ],
				highlights = self._highlights;
			// We update the list where we leave the highlights by the passed id
			if ( !! id && self.hasEditableHighlight( id ) ) {
				highlights = [ highlights[ id ] ];
			}
			for ( var elmId in highlights ) {
				var item = highlights[ elmId ];
				if ( ! item.editable ) {
					continue;
				}
				// Remove the class that includes the highlight of the editable element
				$usbcore.$removeClass( item.highlight, 'usb_editable' );
			}
			self.selectedElmId = null;
		},

		/**
		 * Determines if editable highlight
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {Boolean} True if editable highlight, False otherwise
		 */
		hasEditableHighlight: function( id ) {
			return !! ( this._highlights[ id ] || {} ).editable;
		},

		/**
		 * The MutationObserver interface provides the ability to watch for changes being made to the DOM tree
		 * @see https://developer.mozilla.org/en-US/docs/Web/API/MutationObserver#mutationobserverinit
		 *
		 * @private
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {MutationObserver|undefined}
		 */
		_getMutationObserver: function( id ) {
			var target, self = this;
			if (
				! $usb.builder.isValidId( id )
				|| ! ( target = self.getElmNode( id ) )
			) {
				return;
			}
			var observer = new MutationObserver( $ush.debounce( self.setHighlightsPosition.bind( self ), 1 ) );
			observer.observe( target, {
				characterData: true,
				childList: true,
				subtree: true
			} );
			return observer;
		},

		/**
		 * Create new highlight
		 *
		 * @private
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {{}|null} The highlight object
		 */
		_createHighlight: function( id ) {
			var self = this;
			if (
				! $usb.builder.isValidId( id )
				|| self._highlights[ id ]
				|| ! $ush.isNode( self.highlight )
				|| $usb.builder.isAliasElmId( id ) // skip for aliases (in this case, tab buttons)
			) {
				return null;
			}

			// Clone an element from a template
			var highlightElm = self.highlight.cloneNode( true ),
				node = self.getElmNode( id ),
				// Get settings and data for highlight
				dataHighlight = $ush.toPlainObject( $usbcore.$attr( node, 'data-usb-highlight' ) );
			if ( ! $.isPlainObject( dataHighlight ) ) {
				dataHighlight = {};
			}
			// Add a title for highlighting
			highlightElm
				.querySelector( '.usb-hover-panel-name' )
				.innerText = $usb.builder.getElmTitle( id );
			// Add Edit link if set
			if ( dataHighlight.edit_permalink && dataHighlight.edit_label ) {
				var node = highlightElm.querySelector( '.usb-hover-panel-edit' );
				if ( node ) {
					node.innerText = dataHighlight.edit_label;
					$usbcore.$attr( node, 'href', dataHighlight.edit_permalink );
				}
			}
			// Add all the necessary settings
			$usbcore
				.$attr( highlightElm, 'data-elmid', id )
				.$addClass( highlightElm, 'elm_' + $usb.builder.getElmType( id ) )
				.$toggleClass( highlightElm, 'usb_disable_controls', !! dataHighlight.disable_controls );
			self.highlight
				.after( highlightElm );

			/**
			 * Definition and purpose of zIndex for highlight only
			 * Note: Necessary for correct display on mobile responsive mode
			 */
			var zIndex = 9999; // the default zIndex
			if ( $usb.builder.isChildElmContainer( id ) ) {
				zIndex -= 1;
			} else if ( $usb.builder.isRootElmContainer( id ) ) {
				zIndex -= 2;
			}
			highlightElm.style.zIndex = zIndex;

			// Add nodes to a temporary variable
			return self._highlights[ id ] = {
				active: false,
				editable: false,
				highlight: highlightElm,
				MutationObserver: self._getMutationObserver( id )
			};
		},

		/**
		 * Remove a highlights
		 *
		 * @param {Boolean} force Force removal of highlights
		 */
		removeHighlights: function( force ) {
			var self = this;
			if ( $.isEmptyObject( self._highlights ) ) {
				return;
			}
			for ( var elmId in self._highlights ) {
				if ( ! $usb.builder.isValidId( elmId ) ) {
					continue;
				}
				if ( !! force || null === self.getElmNode( elmId ) ) {
					// Get current highlight data
					var data = self._highlights[ elmId ];
					/**
					 * Disconnect from watch mutations
					 * @see https://developer.mozilla.org/en-US/docs/Web/API/MutationObserver/disconnect
					 */
					if ( data.MutationObserver instanceof MutationObserver ) {
						data.MutationObserver.disconnect();
					}
					$usbcore.$remove( data.highlight ); // remove node element
					delete self._highlights[ elmId ]; // remove data
				}
			}
		}
	} );

	/**
	 * Functionality for handle events
	 */
	$.extend( prototype, {
		/**
		 * Kill current event.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_stop: function( e ) {
			e.preventDefault();
			e.stopPropagation();
		},

		/**
		 * The event fires when the initial HTML document has been completely loaded and parsed,
		 * without waite for stylesheets, images, and subframes to finish load.
		 */
		_DOMContentLoaded: function() {
			// Added class after load documents so that all scripts have time to be initialized
			this.$body.addClass( 'usb_content_ready' );
		},

		/**
		 * Link click handler.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_linkClickHandler: function( e ) {
			var self = this,
				$target = $( e.currentTarget ),
				href = $ush.toLowerCase( $target.attr( 'href' ) || '' );

			// Anyth to exclude from open in a new window
			if (
				href.charAt( 0 ) == '#'
				|| href.substr( 0, 'javascript:'.length ) == 'javascript:'
				|| $target.is( '[ref=magnificPopup]' )
				|| $target.hasClass( '.w-tabs-item' ) // exclude all TTA buttons
			) {
				return;
			}

			// Kill event
			self._events.stop( e );

			// Open links in a new window
			_window.open( href, '_blank' );
		},

		/**
		 * Handler for start css animation in element.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_elmAnimationStart: function( e ) {
			var self = this;
			if ( ! $usbcore.$attr( e.target, 'data-usbid' ) ) {
				return;
			}
			if (
				self.selectedElmId
				&& $usb.builder.getElmType( self.selectedElmId ) !== 'us_grid'
			) {
				self.hideEditableHighlight();
			}
		},

		/**
		 * Handler for end css animation in element.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_elmAnimationEnd: function( e ) {
			var self = this;
			if ( ! $usbcore.$attr( e.target, 'data-usbid' ) ) {
				return;
			}
			if (
				$usb.builder.isValidId( self.selectedElmId )
				&& $usb.builder.getElmType( self.selectedElmId ) !== 'us_grid'
			) {
				self.showEditableHighlight( self.selectedElmId );
				self.setHighlightsPosition();
			}
		},

		/**
		 * The handler is triggered every time the cursor leaves the iframe window.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_mouseLeavesIframe: function( e ) {
			var self = this;
			if ( $usb.panel.isShow() ) {
				self.hideHighlight(); // the hide all highlights
			}
		},

		/**
		 * Selected element.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_elmSelected: function( e ) {
			var self = this;

			// Check the `editor` mode (Only in mode we can select elements to change)
			if (
				! $usb.builder.isMode( 'editor' )
				|| ! $usb.panel.isShow()
			) {
				return;
			}

			var node = self._getNearestNode( e.target );
			if ( ! $ush.isNode( node ) ) {
				return;
			}
			var id = $usb.builder.getElmId( node );
			// Get base selectedElmId without alias
			if ( $usb.builder.isAliasElmId( id ) ) {
				id = $usb.builder.removeAliasFromId( id );
			}
			self.selectedElmId = id;
			// Open in the edit panel
			self.postMessage( 'builder.elmSelected', id );
			self.postMessage( 'navigator.scrollTo', id );
			self.showEditableHighlight( id );
		},

		/**
		 * Handler when the cursor enters the bounds of an element.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_elmMove: function( e ) {
			var self = this;
			if ( ! $usb.panel.isShow() ) {
				return;
			}
			var elm = self._getNearestNode( e.target );
			if ( elm && elm !== self.hoveredElm ) {
				self.hideHighlight();
				self.hoveredElm = elm;
				self.hoveredElmId = $usb.builder.getElmId( elm );
				self.showHighlight();
			}
		},

		/**
		 * Handler when the cursor moves out of the bounds of an element.
		 *
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_elmLeave: function( e ) {
			var self = this;
			if ( ! $usb.panel.isShow() ) {
				return;
			}
			if ( $ush.isNode( self._getNearestNode( e.target ) ) ) {
				self.hoveredElm = null;
				self.hoveredElmId = null;
			}
		},

		/**
		 * Handler when the duplicate element.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_elmDuplicate: function( e ) {
			var self = this,
				$highlight = $( e.currentTarget ).closest( '.usb-hover' ),
				elmId = $highlight.data( 'elmid' );
			if ( ! elmId ) {
				return;
			}
			self.postMessage( 'navigator.showPreloader', elmId );
			self.postMessage( 'builder.elmDuplicate', elmId );
		},

		/**
		 * Remove the class for the copy button, used instead of timeout to simplify logic.
		 * Note: The code is moved to a separate function since `debounced` must be initialized before call.
		 *
		 * @param {Function} fn The function to be executed
		 * @type debounced
		 */
		__removeClassInCopiedElm: $ush.debounce( $ush.fn, 1000 * 4 /* 4 second */ ),

		/**
		 * Handler for copy shortcode to clipboard.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_elmCopy: function( e ) {
			var self = this,
				$target = $( e.currentTarget ),
				$highlight = $target.closest( '.usb-hover' ),
				elmId = $highlight.data( 'elmid' );
			if ( ! elmId ) {
				return;
			}
			// Send an event to the main window
			self.postMessage( 'builder.elmCopy', elmId );
			$target // add a temporary class that the item is copied to the clipboard
				.addClass( 'copied' );
			// Delete a temporary class after a specified time
			self.__removeClassInCopiedElm( function() {
				$target.removeClass( 'copied' );
			} );
		},

		/**
		 * Handler for paste shortcode to content.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_elmPaste: function( e ) {
			var self = this,
				$target = $( e.currentTarget ),
				$highlight = $target.closest( '.usb-hover' ),
				elmId = $highlight.data( 'elmid' );
			// Send an event to the main window
			if ( elmId ) {
				self.postMessage( 'builder.elmPaste', elmId );
			}
		},

		/**
		 * Handler when the delete element.
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM.
		 */
		_elmDelete: function( e ) {
			var self = this,
				$highlight = $( e.currentTarget ).closest( '.usb-hover' ),
				elmId = $highlight.data( 'elmid' );
			if ( ! elmId ) {
				return;
			}
			$usbcore
				.$remove( self._highlights[ elmId ].highlight || null );
			delete self._highlights[ elmId ];
			self.postMessage( 'builder.elmDelete', elmId );
		}
	});

	/**
	 * Functionality for the implementation of Design options
	 * TODO: Make refactoring and transfer functionality to $usb.cssCompiler()
	 */
	$.extend( prototype, {
		/**
		 * Delayed start of CSS animation
		 * Note: The code is moved to a separate function since `debounced` must be initialized before call
		 *
		 * @private
		 * @type debounced
		 */
		__startAnimation: $ush.debounce( function( node ) {
			$usbcore.$addClass( node, 'start' );
		}, 1 ),

		/**
		 * Add or update custom styles in a document
		 *
		 * @private
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @param {String} jsoncss The line of design settings from the $usof.field[ 'design_options' ]
		 * @param {{}} specificClasses List of specific classes that will be added if there is a value by key name
		 */
		_addDesignOptions: function( id, jsoncss, specificClasses ) {
			var self = this,
				$style;
			if ( ! $usb.builder.isValidId( id ) ) {
				return;
			}

			jsoncss += ''; // jsoncss to string

			// Find element of styles for shortcode
			_document.querySelectorAll( 'style[data-for="'+ id +'"][data-classname]' )
				.forEach( function( style, i ) {
					if ( i === 0 ) {
						return $style = style;
					}
					// Delete all unnecessary if any
					$usbcore.$remove( style );
				} );

			/**
			 * Get animated properties in one line
			 *
			 * @param {Node} node
			 * @return {String|undefinded}
			 */
			var getAnimateProps = function( node ) {
				if ( ! $ush.isNode( node ) ) {
					return;
				}
				var style = _window.getComputedStyle( node ),
					name = style.getPropertyValue( 'animation-name' ),
					delay = style.getPropertyValue( 'animation-delay' );
				if ( name && name !== 'none' ) {
					return name + delay;
				}
				return;
			};

			// Get shortcode element
			var node = self.getElmNode( id );

			// Get the first child for buttons
			// Note: Exception for elements that have wrapper that are not main.
			if ( $usb.builder.getElmType( id ) === 'us_btn' && $ush.isNode( node ) ) {
				node = node.firstChild || node;
			}

			// If there is no style element then create a new one
			if ( ! $style ) {
				var // Custom prefix
					customPrefix = $usb.config( 'designOptions.customPrefix', _$$default.customPrefix ),
					// Generate unique class name
					className = $ush.uniqid( customPrefix );
				// If the element is absent then we will complete the action
				if ( ! $ush.isNode( node ) ) return;
				$style = $( '<style data-for="'+ id +'" data-classname="'+ className +'"></style>' )[0];
				// Add a new style element to the page
				node.before( $style );
				// Remove the old custom class in the absence of a style element `<style data-for="..." data-classname="..."></style>`
				if ( node.className.indexOf( customPrefix ) > -1 ) {
					node.className = node.className.replace(
						new RegExp( '(' + $ush.escapePcre( customPrefix ) + '\\w+)' ),
						''
					);
				}
				// Add a new class for custom styles
				$usbcore.$addClass( node, className );
			}

			// Determine the presence of an animation name
			var hasAnimateName = jsoncss.indexOf( 'animation-name' ) > -1,
				oldAnimateProp;

			// Compile and add styles to document
			if ( $style ) {
				var _className = $usbcore.$attr( $style, 'data-classname' );
				// If there are animation settings, keep the old value
				if ( hasAnimateName ) {
					oldAnimateProp = getAnimateProps( node );
				}
				$style.innerText = self._compileDesignOptions( _className, jsoncss );
			}

			// Check classes and restart animation
			if ( hasAnimateName ) {
				var currentAnimateProps = getAnimateProps( node );
				if ( currentAnimateProps && currentAnimateProps !== oldAnimateProp ) {
					// Adjust classes for normal animation work
					$usbcore.$addClass( node, 'us_animate_this' );
					$usbcore.$removeClass( node, 'start' );
					// Delayed start of CSS animation
					self.__startAnimation( node );
				}
			} else if ( $usbcore.$hasClass( node, 'us_animate_this' ) ) {
				$usbcore.$removeClass( node, 'us_animate_this start' );
			}

			// Switch specific design classes depend on the given properties
			self._toggleDesignSpecificClasses.apply( self, arguments );
		},

		/**
		 * Remove design styles for elements that do not exist
		 *
		 * @private
		 */
		_removeDesignForElmsNotExist: function() {
			var self = this;
			_document.querySelectorAll( 'style[data-for]' )
				.forEach( function( style ) {
					var id = $usbcore.$attr( style, 'data-for' );
					if ( id && null === self.getElmNode( id ) ) {
						$usbcore.$remove( style );
					}
				} );
		},

		/**
		 * Remove style tag by element ID
		 *
		 * @private
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 */
		_removeDesignById: function( id ) {
			_document.querySelectorAll( 'style[data-for="'+ id +'"]' )
				.forEach( function( style ) {
					$usbcore.$remove( style );
				} );
		},

		/**
		 * Compile and add styles to document
		 * Note: The method can be called many times, especially when choose a color, it should be as efficient and
		 * fast as possible
		 *
		 * @private
		 * @param {String} className The unique name of the class attached to the element
		 * @param {{}} jsoncss The jsoncss object
		 * @return {String} Compiled css string
		 */
		_compileDesignOptions: function( className, jsoncss ) {
			var self = this, collections = {};

			// Get object jsoncss
			jsoncss = $ush.toPlainObject( jsoncss );
			// If there are no jsoncss options, return an empty string
			if ( $.isEmptyObject( jsoncss ) ) {
				return '';
			}
			// Create of collections for different responsive states
			$usb.config( 'responsiveStates', [] ).map( function( responsiveState ) {
				if ( !! jsoncss[ responsiveState ] ) {
					collections[ responsiveState ] = self._normalizeJsoncss( jsoncss[ responsiveState ] );
				}
			} );

			var result = ''; // result string, these are the compiled styles
			// The formation of styles for different responsive states
			for ( var responsiveState in collections ) {
				if ( $.isEmptyObject( collections[ responsiveState ] ) ) {
					continue;
				}
				var // Final css code
					cssCode = '',
					// Get the current collection ( Apply masks to css properties )
					collection = self._applyMaskToBackgroundCss( collections[ responsiveState ] ),
					// Get breakpoint sizes
					breakpoint = $usb.config( 'designOptions.breakpoints.' + responsiveState, /* default */'' );
				// Collection to string options
				for( var prop in collection ) {
					var propValue = collection[ prop ];
					if ( ! prop || ! propValue ) {
						continue;
					}
					cssCode += prop + ':' + propValue + '!important;';
					// Cancel transparency for an element without animation
					// when using animation on different screens
					if ( prop === 'animation-name' && propValue === 'none' ) {
						cssCode += 'opacity:1!important;';
					}
				}
				// Add class to styles
				if ( cssCode ) {
					cssCode = '.' + className + '{'+ cssCode +'}';
				}
				// Add styles to the result
				result += ( breakpoint )
					? '@media '+ breakpoint +' {'+ cssCode +'}'
					: cssCode;
			}
			return result;
		},

		/**
		 * This helper method is for normalize css options ( jsoncss option -> css option )
		 * TODO: Minimal functionality provide only style applications without optimizations
		 *
		 * @private
		 * @param {{}} cssOptions The css options
		 * @return {{}}
		 */
		_normalizeJsoncss: function( options ) {
			var self = this;

			if ( $.isEmptyObject( options ) ) {
				return options;
			}

			// For background-image get an image URL by attachment ID (Preliminary check)
			if ( !! options[ 'background-image' ] ) {
				var url = $usb.getAttachmentUrl( options[ 'background-image' ] );
				if ( !! url ) {
					options[ 'background-image' ] = 'url('+ url +')';
				}
			}

			// Normalization of css parameters
			for ( var prop in options ) {
				if ( ! prop || ! options[ prop ] ) {
					continue;
				}
				var value = options[ prop ];

				/**
				 * If the name contains the text color and the values start from the underscore,
				 * try to get the css variable
				 *
				 * Example: color, background-color, border-color, box-shadow-color etc
				 */
				if ( /(^color|-color$)/.test( prop ) && ( '' + value ).charAt( 0 ) === '_' ) {
					value = self.getColorValue( value );
					// Remove gradient for text color
					if ( prop == 'color' && self._isCssGradient( value ) ) {
						value = value.replace( '-grad', '' );
					}
					options[ prop ] = value;
				}

				// Generate correct font-family value
				if ( prop === 'font-family' ) {
					var font_name = $usb.config( 'designOptions.fontVars.' + value, /* default */value );

					// Add quotes, if custom font is uploaded
					if ( value.indexOf( ' ' ) > -1 && value.indexOf( ',' ) == -1 ) {
						font_name = '"' + font_name + '"';
					}
					options[ prop ] = font_name;
				}
				// border-style to border-{position}-style provided that there is a width of this border
				if ( prop === 'border-style' ) {
					[ 'left', 'top', 'right', 'bottom' ] // list of possible positions
						.map( function( position ) {
							var borderWidth = options[ 'border-'+ position +'-width' ];
							if ( ! $ush.isUndefined( borderWidth ) && borderWidth !== '' ) {
								options[ 'border-'+ position +'-style' ] = '' + value;
							}
						} );
					delete options[ prop ];
				}
				// Check for line space
				if ( prop === 'font-height' ) {
					if ( !! value ) {
						options[ 'line-height' ] = value;
					}
					delete options[ prop ];
				}
			}

			// Form `box-shadow` from the list of parameters
			if (
				!! options
				&& (
					!! options[ 'box-shadow-h-offset' ]
					|| !! options[ 'box-shadow-v-offset' ]
					|| !! options[ 'box-shadow-blur' ]
					|| !! options[ 'box-shadow-spread' ]
				)
			) {
				var _boxShadow = [];
				// Value map for `box-shadow` this map is needed to turn the list into a string,
				// the order is also very important here!
				[ 'h-offset', 'v-offset', 'blur', 'spread', 'color' ].map( function( key ) {
					var value = options[ 'box-shadow-' + key ];
					if ( $ush.isUndefined( value ) ) {
						value = ( key === 'color' )
							? 'currentColor' // the default color
							: '0';
					}
					_boxShadow.push( value );
					delete options[ 'box-shadow-' + key ];
				} );
				if ( _boxShadow.length ) {
					options[ 'box-shadow' ] = _boxShadow.join( ' ' );
				}
			}

			// Form `text-shadow` from the list of parameters
			if (
				!! options
				&& (
					!! options[ 'text-shadow-h-offset' ]
					|| !! options[ 'text-shadow-v-offset' ]
					|| !! options[ 'text-shadow-blur' ]
				)
			) {
				var _textShadow = [];
				// Value map for `text-shadow` this map is needed to turn the list into a string,
				// the order is also very important here!
				[ 'h-offset', 'v-offset', 'blur', 'color' ].map( function( key ) {
					var value = options[ 'text-shadow-' + key ];
					if ( $ush.isUndefined( value ) ) {
						value = ( key === 'color' )
							? 'currentColor' // the default color
							: '0';
					}
					_textShadow.push( value );
					delete options[ 'text-shadow-' + key ];
				} );
				if ( _textShadow.length ) {
					options[ 'text-shadow' ] = _textShadow.join( ' ' );
				}
			}

			return options;
		},

		/**
		 * Apply masks to css properties
		 *
		 * @private
		 * @param {{}} collection The collection
		 * @return {{}}
		 */
		_applyMaskToBackgroundCss: function( collection ) {
			var self = this;
			collection = $.extend( {}, collection || {} );
			/**
			 * Masks for optimize and combine styles
			 * NOTE: The order of all values must match the specification of the css
			 * @var {[]} property array
			 */
			var propNames = 'color|image|repeat|attachment|position|size'.split( '|' ) || [],
				assignedProps = {},
				backgroupdPropValue = '';
			// If there are masks, then check and remove from the main collection
			for ( var i in propNames ) {
				var name = propNames[ i ],
					cssName = 'background-' + name;

				if ( !! collection[ cssName ] ) {
					assignedProps[ name ] = collection[ cssName ];
					delete collection[ cssName ];
				}
			}
			/**
			 * Adjust background options before merge
			 * @link https://www.w3schools.com/cssref/css3_pr_background.asp
			 */
			var _gradient = '';
			if ( !! assignedProps[ 'image' ] && self._isCssGradient( assignedProps[ 'color' ] ) ) {
				_gradient = assignedProps[ 'color' ];
				delete assignedProps[ 'color' ];
			}
			if ( !! assignedProps[ 'size' ] ) {
				// If size is set, position should have a value, setting default value for position if it is not set
				if ( ! assignedProps[ 'position' ] ) {
					assignedProps[ 'position' ] = 'left top';
				}
				assignedProps[ 'size' ] = '/ ' + assignedProps[ 'size' ];
			}

			for ( var i in propNames ) {
				var name = propNames[ i ];
				if ( !! assignedProps[ name ] ) {
					backgroupdPropValue += ' ' + assignedProps[ name ];
				}
			}
			// If there is a gradient then add to the end
			if ( _gradient ) {
				backgroupdPropValue += ', ' + _gradient;
			}
			// Add a property created by the mask
			collection[ 'background' ] = backgroupdPropValue.trim();

			return collection;
		},

		/**
		 * Determines whether the specified value is css gradient
		 *
		 * @private
		 * @param {String} value The css value
		 * @return {String} True if the specified value is css gradient, False otherwise
		 */
		_isCssGradient: function( value ) {
			value += ''; // To string
			return value.indexOf( 'gradient' ) > -1 || /\s?var\(.*-grad\s?\)$/.test( value ); // the support css var(*-grad);
		},

		/**
		 * Switch specific design classes depend on the given properties
		 *
		 * @private
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @param {String} jsoncss The line of design settings from the $usof.field[ 'design_options' ]
		 * @param {{}} specificClasses List of specific classes that will be added if there is a value by key name
		 */
		_toggleDesignSpecificClasses: function( id, jsoncss, specificClasses ) {
			var self = this,
				toggleClasses = {};
			if ( ! $.isPlainObject( specificClasses ) ) {
				return toggleClasses;
			}
			// Get shortcode element
			var node = self.getElmNode( id );
			// Get the first child for buttons
			// Note: Exception for elements that have wrapper that are not main
			if ( $usb.builder.getElmType( id ) === 'us_btn' && $ush.isNode( node ) ) {
				node = node.firstChild || node;
			}
			// Convert to json string
			if ( jsoncss ) {
				jsoncss = unescape( '' + jsoncss ) || '{}';
			}
			// Check jsoncss properties and add or remove classes
			for ( var prop in specificClasses ) {
				var state = ( jsoncss.indexOf( '"'+ prop +'"' ) > -1 );
				$usbcore.$toggleClass( node, specificClasses[ prop ], state );
			}
		},
	} );

	/**
	 * Functionality for the implementation of Main API
	 */
	$.extend( prototype, {

		/**
		 * Get the border of the container if the cursor is in it
		 *
		 * @private
		 * @param {Node} target The target node
		 * @param {Number} clientX The coordinates along the X axis
		 * @param {Number} clientY The coordinates along the Y axis
		 * @param {Number} borderAround The virtual border size around
		 * @return {String} Returns the mouse positions on the virtual border
		 */
		_getBorderContainerToCursor: function( target, clientX, clientY, borderAround ) {
			var self = this;
			if (
				! $ush.isNode( target )
				|| target === self.elmMainContainer
				|| ! $.isNumeric( clientX )
				|| ! $.isNumeric( clientY )
			) {
				return _DIRECTION_.UNKNOWN;
			}

			// Scroll corrections
			clientX += _window.scrollX;
			clientY += _window.scrollY;

			var // Get sizes
				elmRect = $ush.$rect( target ),
				elmLeft = floor( abs( elmRect.x ) + _window.scrollX ),
				elmTop = floor( abs( elmRect.y ) + _window.scrollY ),
				elmRight = floor( elmLeft + elmRect.width ),
				elmBottom = floor( elmTop + elmRect.height );

			// If the value is not a number, then set the default value
			if ( ! $.isNumeric( borderAround ) ) {
				borderAround = 5; // default border around
			}

			// Top border
			if ( clientY > elmTop && clientY <= ( elmTop + borderAround ) ) {
				return _DIRECTION_.TOP;
			}
			// Bottom border
			else if ( clientY < elmBottom && clientY >= ( elmBottom - borderAround ) ) {
				return _DIRECTION_.BOTTOM;
			}
			// Left border
			else if ( clientX > elmLeft && clientX <= ( elmLeft + borderAround ) ) {
				return _DIRECTION_.LEFT;
			}
			// Rigth border
			else if ( clientX < elmRight && clientX >= ( elmRight - borderAround ) ) {
				return _DIRECTION_.RIGHT;
			}

			return _DIRECTION_.UNKNOWN;
		},

		/**
		 * Get сurrent cursor direction in target
		 *
		 * @private
		 * @param {Node} target The target relative to which the direction will be calculated
		 * @param {Number} clientX The coordinates along the X axis
		 * @param {Number} clientY The coordinates along the Y axis
		 * @param {String} axis The axis along which the cursor movement will be determined
		 * @return {String} Returns the direction of movement of the cursor along the specified axis
		 *
		 * Angle visual example of a map in 360°:
		 * +--------------------+--------------------+
		 * | -165              -90               -15 |
		 * |                    |                    |
		 * | -180               |                 -1 |
		 * +------------------- 0 -------------------+
		 * |  180               |                  1 |
		 * |                    |                    |
		 * |  165               90                15 |
		 * +--------------------+--------------------+
		 */
		_getCurrentCursorDirectionInTarget: function( target, clientX, clientY, axis ) {
			// Check if the target is a node
			if ( ! $ush.isNode( target ) ) {
				return _DIRECTION_.UNKNOWN;
			}
			var self = this,
				// Radius to Degree
				RAD_TO_DEG = 180 / Math.PI,
				// Get the size of the container and its position relative to the viewport
				rect = $ush.$rect( target ),
				// Get the center of the container
				center = {
					x: rect.width / 2 + rect.left,
					y: rect.height / 2 + rect.top
				},
				// Get a vector relative to the target (container)
				vector = {
					x: clientX - center.x,
					y: clientY - center.y
				},
				// Get a vector length
				vectorLength = Math.sqrt( vector.x * vector.x + vector.y * vector.y ),
				// Get a directions
				direction = {
					x: vector.x / vectorLength,
					y: vector.y / vectorLength
				},
				// Get current cursor movement angle
				angle = Math.atan2( direction.y, direction.x ) * RAD_TO_DEG;

			// Get the name of the axis on which you want to get directions
			if ( $usbcore.indexOf( axis, _2D_AXES_ ) < 0 ) {
				axis = _2D_AXES_.y;
			}
			// Definition by x-axis
			if ( axis === _2D_AXES_.x ) {
				return ( angle > -180 && angle <= -130 || angle <= 180 && angle > 130 )
					? _DIRECTION_.LEFT
					: _DIRECTION_.RIGHT;
			}
			// Definition by y-axis ( default )
			return ( angle < 0 )
				? _DIRECTION_.TOP
				: _DIRECTION_.BOTTOM;
		},

		/**
		 * Determines if coordinates are inside a node
		 *
		 * @param {Node} node The node
		 * @param {Number} pageX The X coordinate of the mouse pointer relative to the whole document
		 * @param {Number} pageY The Y coordinate of the mouse pointer relative to the whole document
		 * @return {Boolean} True if inside node, False otherwise
		 */
		isInsideNode: function( node, pageX, pageY ) {
			if (
				! $ush.isNode( node )
				|| $ush.isUndefined( pageX )
				|| $ush.isUndefined( pageY )
			) {
				return false;
			}
			var nodeRect = $ush.$rect( node ),
				offsetTop = ( nodeRect.x + _window.scrollX ),
				offsetLeft = ( nodeRect.y + _window.scrollY );
			return (
				pageX >= offsetTop
				&& pageX <= ( offsetTop + nodeRect.width )
				&& pageY >= offsetLeft
				&& pageY <= ( offsetLeft + nodeRect.height )
			);
		},

		/**
		 * Get the nearest node
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @private
		 * @param {Node} node The node
		 * @param {String} asContainer Filter to get the desired container, the follow values are available:
		 *     `any|root|child`
		 * @return {Mixed} Returns the node or `null` on failure
		 */
		_getNearestNode: function( node, asContainer ) {
			var self = this,
				args = arguments,
				foundId;
			if ( ! $ush.isNode( node ) ) {
				return null;
			}

			// Finds the first ID in the node tree
			while ( ! ( foundId = $usbcore.$attr( node, 'data-usbid' ) ) ) {
				if ( ! node.parentNode ) {
					return null;
				}
				node = node.parentNode;
			}
			// If the filter is not set, then return the element if found
			if ( ! asContainer ) {
				return self.getElmNode( foundId || $usb.builder.mainContainer );
			}
			// Check the correctness of the filter
			if ( $usbcore.indexOf( asContainer, _CONTAINER_ ) < 0 ) {
				$usb.log( 'Error: The asContainer value is invalid:', args );
				return null;
			}
			/**
			 * Get the id of the element that matches the filters
			 *
			 * @private
			 * @param {String} currentId id of the currently found element.
			 * @return {Mixed} If successful, returns the desired id by filter, otherwise null
			 */
			var _filter = function( currentId ) {
				var args = arguments,
					parentId = $usb.builder.getElmParentId( currentId );
				if ( ! parentId ) {
					return null;
				}
				var recursionLevel = $ush.parseInt( args[ /* current recursion level */1 ] );
				if ( recursionLevel >= /* max number of levels when recursin */20 ) {
					$usb.log( 'Notice: Exceeded number of levels in recursion:', args );
					return null;
				}
				if (
					// Any first container
					( asContainer === _CONTAINER_.ANY && $usb.builder.isElmContainer( parentId ) )
					// Root container, for example: vc_row, vc_row_inner, vc_tabs etc
					|| ( asContainer === _CONTAINER_.ROOT && $usb.builder.isRootElmContainer( parentId ) )
					// Child container, for example: vc_column, vc_column_inner, vc_tta_section, etc
					|| ( asContainer === _CONTAINER_.CHILD && $usb.builder.isChildElmContainer( parentId ) )
				) {
					return parentId;
				}
				// Recursively check the prev parent
				return _filter( parentId, recursionLevel++ );
			};

			var foundId = _filter( foundId ) || $usb.builder.mainContainer;
			return self.getElmNode( foundId );
		},

		/**
		 * Get the color value
		 * Note: The color result can include variable css
		 *
		 * @param {String} value The value
		 * @return {String} The color value
		 */
		getColorValue: function( value ) {
			if ( ( '' + value ).indexOf( '_' ) > -1 ) {
				return $usb.config( 'designOptions.colorVars.' + value, /* default */value );
			}
			return value;
		},

		/**
		 * Get the target element
		 *
		 * @private
		 * @param {String} targetId Shortcode's usbid, e.g. "us_btn:1" or `container`
		 * @param {String} position The position
		 * @return {Mixed}
		 */
		_getTargetElm: function( targetId, position ) {
			var self = this;
			// Check the correctness of the data in the variables
			if (
				! targetId
				|| ! $usb
				|| $usbcore.indexOf( position, [ 'before', 'prepend', 'append', 'after' ] ) < 0
			) {
				return;
			}

			var isMainContainer = $usb.builder.isMainContainer( targetId ),
				// Find parent element
				// TODO:Optimize and implement without jQuery
				$targetElm = $( self.getElmNode( isMainContainer ? $usb.builder.mainContainer : targetId ) );
			// When positioned before or after, return the $parentElm unchanged
			if ( $usbcore.indexOf( position, [ 'before', 'after' ] ) > -1 ) {
				return $targetElm;
			}
			/**
			 * Parent adjustment for different shortcodes
			 *
			 * Note: All searches for the location of the root element are strictly tied to
			 * the structure and classes, see the switch construction!
			 */
			if ( ! isMainContainer && $targetElm.length ) {
				var elmType = $usb.builder.getElmType( targetId ),
					elmRootSelector = $usb.config( 'rootContainerSelectors.' + elmType );
				if ( elmRootSelector ) {
					// The settings can contain a list of containers `.container, .container > *`,
					// but we only get the first one found
					$targetElm = $( '' + elmRootSelector, $targetElm ).first();
					if ( ! $targetElm.length ) {
						$usb.log( 'Error: No element set for container `%s` in rootContainerSelectors'.replace( '%s', elmType ) );
					}
				}
			}

			return $targetElm;
		},

		/**
		 * Get an node or nodes by ID
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @param {String|[]} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {null|Node|[Node...]}
		 */
		getElmNode: function( id ) {
			if ( ! id ) {
				return;
			}
			var self = this,
				ids = id;

			// The convert to a single type to data
			if ( ! $.isArray( ids ) ) {
				ids = [ ids ];
			}

			// Check if the ID's is correct
			ids = ids.filter( function( id ) {
				// We will leave everyth that passes the validation, and delete the rest
				return $usb.builder.isValidId( id ) || $usb.builder.isMainContainer( id );
			} );

			// Convert ID's to selectors
			ids = ids.map( function( id ) {
				return '[data-usbid="'+ id +'"]';
			} );

			// The get one node
			if ( $.type( id ) === 'string' && ids.length === 1 ) {
				return _document.querySelector( ids[ 0 ] );

			}
			// The get an array of nodes
			if ( $.isArray( id ) && ids.length ) {
				var nodes =_document.querySelectorAll( ids.join( ',' ) );
				return $ush.toArray( nodes );
			}

			// If there is noth, return `null`
			return null;
		},

		/**
		 * Get all html for a node include styles
		 *
		 * @param {String|[]} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {String}
		 */
		getElmOuterHtml: function( id ) {
			var node = this.getElmNode( id );
			if ( $ush.isNode( node ) ) {
				return ( ( _document.querySelector( 'style[data-for="'+ id +'"]' ) || {} ).outerHTML || '' ) + node.outerHTML;
			}
			return '';
		},

		/**
		 * Determines whether the specified identifier is hidden tab
		 *
		 * @param {String} id The id e.g. "vc_tta_section:1"
		 * @return {Boolean} True if the specified identifier is hidden tab, False otherwise
		 */
		isHiddenTab: function( id ) {
			var self = this;
			return $usb.builder.isElmSection( id ) && ! $( '.w-tabs-section-content:first', self.getElmNode( id ) ).is( ':visible' );
		},

		/**
		 * Opens a section by ID
		 *
		 * @param {String} id The id e.g. "vc_tta_section:1"
		 */
		openSectionById: function( id ) {
			var self = this;
			if ( self.isHiddenTab( id ) ) {
				$( '.w-tabs-section-header:first', self.getElmNode( id ) )
					.trigger( 'click' ); // the open accordion or tab
			}
		},

		/**
		 * Scroll to an item if it is outside the window
		 *
		 * @param {String} id The id e.g. "vc_row:1"
		 */
		scrollToOutsideElm: function( id ) {
			var self = this;
			if ( ! $usb.builder.isValidId( id ) ) {
				return;
			}
			// Get the node and
			var node = self.getElmNode( id );
			if ( ! $ush.isNode( node ) ) {
				return;
			}
			// If the element is not outside the view, then exit
			var rect = $ush.$rect( node );
			if (
				! ( rect.top < 0 || rect.bottom > ( _window.innerHeight || rect.height ) )
			) {
				return;
			}
			// Note: If there is $us.scroll use it, because there are a lot
			// of nuances related to the header, basement, etc
			if (
				! $ush.isUndefined( $us.scroll )
				&& $.isFunction( $us.scroll.scrollTo )
			) {
				$us.scroll.scrollTo( $( node ), /*animate*/true );
			}
			// Alternative way of scrolling to the node
			else {
				self.$htmlBody
					.stop( true, false )
					.animate( { scrollTop: $( node ).offset().top + 'px' } );
			}
		},

		/**
		 * Set the highlights position
		 * Note: The code is moved to a separate function since `debounced` must be initialized before call
		 *
		 * @private
		 * @type debounced
		 */
		__setHighlightsPosition: $ush.debounce( function() {
			this.setHighlightsPosition();
		}, 10 ),

		/**
		 * Handlers for private events
		 * @private
		 */
		_$events: {

			/**
			 * The handler is called every time the panel display changes
			 *
			 * @private
			 * @event handler
			 */
			changeSwitchPanel: function() {
				var self = this;
				self.$body.toggleClass( 'usb_preview', $usb.panel.isShow() );
			},

			/**
			 * Show the load
			 *
			 * @private
			 * @event handler
			 * @param {String} targetId Shortcode's usbid, e.g. "us_btn:1"
			 * @param {String} position The position ( possible values: before, prepend, append, after )
			 * @param {Boolean} isContainer If these values are true, then a container class will be added for
			 *     customization
			 * @param {String} id The unique id for preloader
			 */
			showPreloader: function( targetId, position, isContainer, id ) {
				var self = this;
				// The replace element
				if ( $ush.isUndefined( position ) ) {
					$( self.getElmNode( targetId ) )
						.addClass( $usb.config( 'className.elmLoad', '' ) );
					return;
				}

				// Don't add preloader, if container have
				if (
					targetId === 'container'
					&& ! $ush.isUndefined( self._preloaders[ id || targetId ] )
				) {
					return;
				}

				// Create a new preloader
				var $preloader = $( '<div class="g-preloader type_1 for_usbuilder"></div>' )
					// If a container is added to the tucked place, then we add a class to be able to customize the display
					.toggleClass( 'usb-loading-container', !! isContainer );

				// Add to the list of active preloaders
				self._preloaders[ id || targetId ] = $preloader.get( 0 );

				// The insert element
				self.trigger( 'insertElm', [ targetId, position, $preloader ] );
			},

			/**
			 * Hide the load
			 *
			 * @private
			 * @event handler
			 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
			 */
			hidePreloader: function( id ) {
				var self = this;
				if ( !! id && self._preloaders[ id ] ) {
					$usbcore
						.$remove( self._preloaders[ id ] );
					delete self._preloaders[ id ];
				}
			},

			/**
			 * Remove an element from a document by its ID
			 *
			 * @private
			 * @event handler
			 * @param {String|[]} id The element that is being removed, e.g. "us_btn:1"
			 */
			removeHtmlById: function ( removeId ) {
				var self = this;
				if ( ! removeId ) {
					return;
				}
				if ( ! $.isArray( removeId ) ) {
					removeId = [ removeId ];
				}
				// Get all nodes to remove
				var nodes = self.getElmNode( removeId ) || [];
				if ( ! nodes.length ) {
					return;
				}

				// Remove all nodes
				nodes.map( function( node ) {
					if ( ! $ush.isNode( node ) ) {
						return;
					}
					var $node = $( node ),
						$tabs = $node.closest( '.w-tabs' );

					// Remove a button and open a free section
					if ( $usb.builder.isUpdateIncludeParent( node ) ) {
						$( '[aria-controls="content-'+ $node.attr( 'id' ) +'"]:first', $tabs )
							.remove();
						// The open the first section
						$tabs
							.find( '.w-tabs-list a:first, .w-tabs-section-title:first' )
							.trigger('click')
					}
					// Remove node
					$node
						// Trigger events about the remove of an element
						// to track changes in the elements
						.trigger( 'usb.removeHtml' )
						// Remove a element
						.remove();
					// Remove highlights. Refactoring in #2313
					self.removeHighlights();
					// Remove design styles for elements that do not exist. Refactoring in #2313
					self._removeDesignForElmsNotExist();
				} );
			},

			/**
			 * Add new item to document
			 *
			 * @event handler
			 * @param {String|Node} parent Shortcode's usbid, e.g. "us_btn:1" or `container`
			 * @param {String} position The position ( possible values: before, prepend, append, after )
			 * @param {String} html The html
			 * @param {Boolean} scrollIntoView If the True are set, then after add the scroll to the new node
			 */
			insertElm: function( parent, position, html, scrollIntoView ) {
				var self = this,
					// Definition based on `usbid` and position
					$parentElm = ! $ush.isNode( parent )
						? self._getTargetElm( parent, position )
						: $( parent ); // if explicitly passed node to `parent`
				// TODO: This code is often called when move or add a new item, so you need to implement in VanillaJS
				if ( $parentElm instanceof $ ) {
					var $html = $( html );
					$parentElm[ position ]( $html );
					// Init its JS if needed
					$( '[data-usbid]', $html ).each( function( _, node ) {
						self.trigger( 'maybeInitElmJS', [ $usbcore.$attr( node, 'data-usbid' ) ] );
					} );
					// Scrolls the current container of the parent of the element so that the new element is visible to the user
					if ( scrollIntoView ) {
						$html[0].scrollIntoView();
						// The animation start control
						$( ( '[class*="us_animate_"]:not(.start)' ), $html )
							.addClass( 'start' );
					}
				}
			},

			/**
			 * Move element on preview page
			 *
			 * @event handler
			 * @param {String} parent Shortcode's usbid, e.g. "us_btn:1" or `container`
			 * @param {String} position The position ( possible values: before, prepend, append, after )
			 * @param {String} elmId Shortcode's usbid, e.g. "us_btn:1"
			 */
			moveElm: function( parent, position, elmId ) {
				var self = this,
					$parentElm = self._getTargetElm( parent, position ),
					$elm = $( self.getElmNode( elmId ) );
				if ( $parentElm instanceof $ && $elm.length ) {
					$parentElm[ position ]( $elm );
					// Since we always have custom styles after the elements, when we
					// move the element, we will move the styles if any
					var $style = $( 'style[data-for="' + elmId + '"]:first', self.$body );
					if ( $style.length ) {
						$elm.before( $style );
					}
					// Synchronization of the button order of tabs and sections
					// Note: Tab buttons must always match the order of the sections otherwise crashes may occur!
					var parentId = $usb.builder.getElmParentId( elmId );
					if ( parentId && $usb.builder.isElmTab( parentId ) ) {
						var elmNode = self.getElmNode( $usb.builder.addAliasToElmId( 'tab-button', elmId ) ),
							parentNode = $usb.builder.isElmTab( parent )
								? elmNode.parentNode
								: self.getElmNode( $usb.builder.addAliasToElmId( 'tab-button', parent ) );
						$( parentNode )[ position ]( elmNode );
					}
				}
			},

			/**
			 * Updates the selected element on the page
			 *
			 * @event handler
			 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
			 * @param {String} html This is the html code of the element and additionally,
			 * 				   if necessary, the styles in a separate tag after the element
			 */
			updateSelectedElm: function( id, html ) {
				if ( ! id ) {
					return;
				}
				var self = this,
					node = self.getElmNode( id );
				if ( ! $ush.isNode( node ) ) {
					return;
				}

				// Remove style tag by element ID
				self._removeDesignById( id );
				node.outerHTML = '' + html; // refresh entire node

				// Init its JS if needed
				self.trigger( 'maybeInitElmJS', [ id ] );

				// Update highlight for the element
				self.__setHighlightsPosition();
			},

			/**
			 * Update custom css on the preview page
			 *
			 * @param {String} css The css
			 */
			updatePageCustomCss: function( css ) {
				var self = this,
					// Meta key for post custom css
					keyCustomCss = $usb.config( 'settings.keyCustomCss', /* default */'usb_post_custom_css' );

				// Note: Since this is outputed inside the WPBakery Page Builder, we can correct it here
				var $style = $( 'style[data-type="'+ keyCustomCss +'"]', self.$document );
				if ( ! $style.length ) {
					$style = $( '<style data-type="'+ keyCustomCss +'">' );
					$( 'head', self.$document )
						.append( $style );
				}
				$style.text( css || '' );
			},

			/**
			 * Update element content
			 * Note: This method is only for update content
			 *
			 * @param {String|Node} selector The selector to find nodes
			 * @param {String} content Text or HTML content to be installed
			 * @param {String} method Method to be used
			 */
			updateElmContent: function( selector, content, method ) {
				if ( $usbcore.indexOf( method, ['text', 'html'] ) < 0 ) {
					method = 'text';
				}
				$( selector, this.$document )[ method ]( '' + content );
			},

			/**
			 * Init its JS if needed
			 *
			 * @param {String} targetId Shortcode's usbid, e.g. "vc_row:1"
			 */
			maybeInitElmJS: function( targetId ) {
				var self = this,
					initMethods = $.isPlainObject( _window.$usbdata.elmsInitJSMethods )
						? _window.$usbdata.elmsInitJSMethods
						: {},
					elmType = $usb.builder.getElmType( targetId );
				if (
					! $ush.isUndefined( initMethods[ elmType ] )
					&& $.isFunction( initMethods[ elmType ] )
				) {
					var $node = $( self.getElmNode( targetId ) );
				 	// If an element has a common wrapper, then we get the element node, not the wrapper
					if ( $node.length && $usbcore.indexOf( elmType, $usb.config( 'shortcode.with_wrappers', /* default */[] ) ) > -1 ) {
						$node = $( ':first-child', $node );
					}
					initMethods[ elmType ]( $node );
				}
			},

			/**
			 * Apply changes to the element
			 *
			 * instruction: `
			 * {
			 * 		'attr': 'html|text|tag|{attribute}(style|class|...)',
			 * 		'css': '{selectors}',
			 * 		'elm': '{selectors}',
			 * 		'mod': '{name}',
			 * 		'toggle_atts': {
			 * 			'attribute': '{value}',
			 * 			'attribute2': '{value2}',
			 * 		},
			 * 		'toggle_class': '{class name}',
			 * 		'toggle_class_inverse': '{class name}',
			 * 		'design_options': {
			 * 			// List of specific classes that will be added if there is a value by key name
			 * 			color: 'has_text_color',
			 * 			width: 'has_width',
			 * 			height: 'has_height',
			 * 			...
			 * 		},
			 * }`
			 * or array instructions: `
			 * [
			 *        {...},
			 *        {...}
			 * ]`
			 *
			 * @event handler
			 * @param {String} targetId Shortcode's usbid, e.g. "us_btn:1"
			 * @param {{}} instructions The are instructions for update elements
			 * @param {Mixed} value The value
			 * @param {String} fieldType Field type
			 * @param {Boolean} isResponsiveValue Determine adaptive value format or not (by
			 *     $usof.field.isResponsiveValue)
			 *
			 * TODO: Add responsive value support for all instruction types!
			 */
			onPreviewParamChange: function( targetId, instructions, value, fieldType, isResponsiveValue ) {
				var self = this,
					$target = $( self.getElmNode( targetId ) );
				if ( ! $target.length ) {
					return;
				}

				// Get responsive states
				var states = $usb.config( 'responsiveStates', [] ) || '';

				if ( $ush.isUndefined( instructions[ 0 ] ) ) {
					instructions = [ instructions || {} ];
				}

				// If the field type is color and the value has a key, then we get css color variable
				if ( fieldType === 'color' && ( '' + value ).charAt( 0 ) === '_' ) {
					value = self.getColorValue( value );
				}

				for ( var i in instructions ) {
					var instruction = instructions[ i ],
						// Define the element to change
						$elm = ! $ush.isUndefined( instruction[ 'elm' ] )
							? $target.find( instruction[ 'elm' ] )
							: $target;

					if ( ! $elm.length ) {
						continue;
					}

					// Change the class modifier of an element
					if ( ! $ush.isUndefined( instruction[ 'mod' ] ) ) {
						var mod = '' + instruction[ 'mod' ],
							escapeMod = $ush.escapePcre( mod ),
							// Expression for remove classes, include those with prefixes for responsive modes
							pcre = new RegExp( '((^|\\s)(('+ states.join('|') +')_)?'+ escapeMod + '[a-zA-Z0-9\_\-]+)', 'g' );

						// Remove all classes from modifier
						$elm.each( function( _, elm ) {
							elm.className = elm.className.replace( pcre, '' );
						} );

						if ( isResponsiveValue ) {
							$.each( value || [], function( state, value ) {
								if ( value ) {
									$elm.addClass( state + '_' + mod + '_' + value );
								}
							} );

						} else {
							// If the value is not responsive, check for a set and turn it into an array
							value = $.isArray( value ) ? value : ( '' + value ).split( ',' );
							$.each( value || [], function( _, value ) {
								if ( value ) {
									$elm.addClass( mod + '_' + value );
								}
							} );
						}
					}

					// Change the inline parameter
					if ( ! $ush.isUndefined( instruction[ 'css' ] ) ) {
						// For the font-family property, check for the presence of global keys `body`, 'h1`, `h2` etc
						if ( 'font-family' === instruction[ 'css' ] ) {
							// Get the font family from the design options
							value = $usb.config( 'designOptions.fontVars.' + value, /* default */value );
						}
						$elm.css( instruction[ 'css' ], value );

						/*
						 * Ugly hack for Safari compatibility:
						 * since it would not re-render element after change grid-gap CSS property,
						 * force re-render by change opacity property
						 */
						if (
							$ush.isSafari // safari detection
							&& 'grid-gap' === instruction[ 'css' ]
						) {
							$elm.css( 'opacity', '0.99' );
							$ush.timeout( function() {
								$elm.css( 'opacity', '' );
							}, 50 );
						}
					}

					// Change some attribute (or embedded text, html)
					if ( ! $ush.isUndefined( instruction[ 'attr' ] ) ) {
						var attr_name = '' + instruction[ 'attr' ];

						switch ( attr_name ) {
							case 'html': // set html to $elm
								$elm.html( value );
								break;
							case 'text': // set text to $elm
								$elm.text( value );
								break;
							case 'tag': // replace tag name in $elm
								$elm.replaceWith( function() {
									var that = this,
										$tag = $( '<' + value + '>' ).html( $( that ).html() );
									for ( var i = that.attributes.length - 1; i >= 0; -- i ) {
										var item = that.attributes[ i ];
										$tag.attr( item.name, item.value );
									}
									return $tag;
								} );
								break;
							case 'class': // add a custom class
								$elm
									.removeClass( $elm.data( 'last-classname' ) || '' )
									.addClass( value )
									.data( 'last-classname', value );
								break;
							case 'onclick': // add error protection for event values
								// If there are errors in custom JS, an error message will be displayed
								// in the console, and this will not break the work of the site
								if ( value ) {
									value = 'try{' + value + '}catch(err){console.error(err)}';
								}
								// Note: no break; here, so default: code is executed too
							default: // update other attributes
								$elm.attr( attr_name, value );
						}
					}

					// Attribute toggles
					if ( ! $ush.isUndefined( instruction[ 'toggle_atts' ] ) ) {
						for ( var k in instruction[ 'toggle_atts' ] ) {
							if ( value == true ) {
								$elm.attr( k, instruction[ 'toggle_atts' ][ k ] ); // set attribute
							} else {
								$elm.removeAttr( k ); // remove attribute
							}
						}
					}

					// Turn on/off css class
					if ( ! $ush.isUndefined( instruction[ 'toggle_class' ] ) ) {
						$elm.toggleClass( instruction[ 'toggle_class' ], !! value );
					}

					// Turn on/off css class (inverse)
					if ( ! $ush.isUndefined( instruction[ 'toggle_class_inverse' ] ) ) {
						$elm.toggleClass( instruction[ 'toggle_class_inverse' ], ! value );
					}

					// Compile and update design styles
					if ( ! $ush.isUndefined( instruction[ 'design_options' ] ) ) {
						self._addDesignOptions( targetId, /* jsoncss string */value, /* specific classes */instruction[ 'design_options' ] );
					}

					// Refresh node (Allows you to reload an element to apply attributes, such as for vide or audio nodes)
					if ( ! $ush.isUndefined( instruction[ 'refresh' ] ) ) {
						$elm.replaceWith( $elm.clone() );
					}
				}

				// Event for react in extensions
				$target.trigger( 'usb.contentChange' );

				// Set the highlight position
				self.setHighlightsPosition();
			},

			/**
			 * Called when a new element is added and gets the coordinates of the mouse
			 *
			 * @event handler
			 * @param {String} method The event name
			 * @param {{}} data The mouse event data
			 */
			onParentEventData: function( method, data ) {
				if ( ! method ) {
					return;
				}
				// Determination of the element that is under the coordinates, and obtain all additional data
				data = $.extend( /* default */{ eventX: 0, eventY: 0, clientX: 0, clientY: 0, pageX: 0, pageY: 0 }, data || {} );
				data.target = _document.elementFromPoint( data.eventX, data.eventY );
				this.trigger( 'doAction', [ method, data ] );
			},

			/**
			 * This method calls another method that is specified in
			 * the parameters and, if necessary, passes arguments
			 *
			 * @event handler
			 * @param {String} name Method name to run
			 * @param {{}} args Arguments to be passed to the method
			 */
			doAction: function( name, args ) {
				var self = this;
				if ( ! name || ! $.isFunction( self[ name ] ) ) {
					return;
				}
				args = args || [];
				self[ name ].apply( self, $.isArray( args ) ? args : [ args ] );
			},

			/**
			 * This handler is called every time the column/column_inner in change
			 * Note: At the moment, the same distribution of space between the columns is implemented
			 *
			 * @event handler
			 * @param {String} rootContainerId Shortcode's usbid, e.g. "vc_row:1", "vc_row_inner:1"
			 */
			vcColumnChanged: function( rootContainerId ) {
				var self = this;
				if ( ! rootContainerId || ! $usb.builder.isValidId( rootContainerId ) ) {
					return;
				}

				var columns = $usb.builder.getElmChildren( rootContainerId );
				$( columns.map( function( usbid ) { return '[data-usbid="'+ usbid +'"]' } ).join( ',' ), self.$body )
					.each( function( i, column ) {
						// Get width depend on mesh type Grid/Flex
						var width = '' + $usb.builder.getElmValue( columns[i], 'width' );
						if ( /(\d+)\/(\d+)/.test( width ) ) {
							var isGridColumnsLayout = $usb.config( 'isGridColumnsLayout', /* default */false );
							if ( ! isGridColumnsLayout && width.indexOf( '/5') != -1 ) { // specific to classes 1/5, 2/5, N/5
								// do noth
							} else {
								var parts = width.split( '/' );
								width = ceil( parts[ /*x*/0 ] / parts[ /*y*/1 ] * 12 );
							}
						}
						if ( ! width ) {
							return;
						}
						for ( var i = 3; i > -1; i-- ) {
							var prefix = [ 'xs', 'sm', 'md', 'lg' ][ i ],
								matches = ( new RegExp( '(vc_col)-('+ prefix +')-[0-9\\/]+' ) ).exec( column.className );
							if ( ! matches ) {
								continue;
							}
							// TODO: Change the algorithm to calculate the width without change the already exist columns
							column.className = column.className.replace( matches[0], matches[1] + '-' + prefix + '-' + width );
						}
					} );
			}
		}
	} );

	// Get nodes after the document is ready
	$( function() {
		_window.$usbp = new USBPreview;
	} );

}( jQuery );
