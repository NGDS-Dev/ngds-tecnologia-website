/**
 * Available spaces:
 *
 * _window.$usb - Basic object for mounting and initializing all extensions of the builder
 * _window.$usbcore - Auxiliary functions for the builder and his extensions
 * _window.$usbdata - Data for import into the USBuilder
 * _window.$usof - UpSolution CSS Framework
 * _window.$ush - US Helper Library
 *
 * Note: Double underscore `__funcname` is introduced for functions that are created through `$ush.debounce(...)`
 */
! function( $, undefined ) {

	// Private variables that are used only in the context of this function, it is necessary to optimize the code
	var _window = window,
		_document = document,
		_undefined = undefined;

	// Math API
	var abs = Math.abs,
		ceil = Math.ceil;

	// Check for is set availability objects
	[ '$ush', '$usbdata' ].map( function( name ) {
		_window[ name ] = _window[ name ] || {};
	} );

	/**
	 * @var {{}} Direction constants
	 */
	const _DIRECTION_ = {
		BOTTOM: 'bottom',
		TOP: 'top'
	};

	/**
	 * @var {RegExp} Regular expression for check and extract alias from usbid
	 */
	const _REGEXP_USBID_ALIAS_ = /^([\w\-]+:\d+)\|([a-z\d\-]+)$/;

	/**
	 *
	 * @type {String} The mode configures and loads the environment in which it will run the builder page
	 */
	var _$mode = 'preview';

	/**
	 * @var {{}} Default page data
	 */
	var _$defaultPageData = {
		content: '', // page content
		customCss: '', // page Custom CSS
		fields: {}, // page fields post_title, post_status, post_name etc
		pageMeta: {} // page Meta Data
	};

	/**
	 * @var {{}} Private temp data
	 */
	var _$temp = {
		generatedIds: [], // list of generated IDs
		isInitDragDrop: false, // is init drag & drop
		isProcessSave: false, // the AJAX process of save data on the backend
		savedPageData: $ush.clone( _$defaultPageData ), // save the last saved page data
		transit: null // transit data
	};

	/**
	 * @var {{}} Default builder configuration
	 */
	// var _$defaultConfig = {
	// 	shortcode: {
	// 		containers: [], // list of container shortcodes (with a close tag)
	// 		default_values: {}, // list of default values for shortcodes
	// 		edit_content: {}, // list of shortcodes whose value is content
	// 		relations: {}, // // list of strict relations between shortcodes
	// 	},
	// 	ajaxArgs: {}, // default arguments for AJAX requests
	// 	breakpoints: {}, // get screen sizes of responsive states
	// 	elm_icons: {}, // icons of available elements
	// 	elm_titles: {}, // available shortcodes and their titles
	// 	elms_supported: [], // list of elements supported by the builder
	// 	grid_post_types: [], // post types for selection in Grid element (Used in import shortcodes)
	// 	keyCustomCss: 'usb_post_custom_css', // default meta_key for post custom css
	// 	placeholder: '', // default placeholder (Used in import shortcodes)
	// 	template: {}, // templates shortcodes or html
	// 	useLongUpdateForFields: [], // list of usof field types for which the update interval is used
	// 	useThrottleForFields: [], // list of usof field types for which to use throttle
	// 	className: { // single place for the names of classes that are used in different places in the builder
	// 		elmLoad: 'usb-elm-loading' // class that indicates that the element is in the state of load from the server
	// 	}
	// };

	/**
	 * @class Page Builder - Builder for edit, remove and add shortcodes to a page
	 * @param {String} container The main container
	 */
	var Builder = function( container ) {
		var self = this;

		/**
		 * The main container that is the root of the current page
		 */
		self.mainContainer = 'container';

		/**
		 * @var {String} Selected element (shortcode) usbid, e.g. 'us_btn:1'
		 */
		self.selectedElmId;

		// Variables
		self._isReloadPreviewAfterSave = false; // reload preview after save
		self.pageData = $ush.clone( _$defaultPageData ); // empty default data object

		/*
		 * When the user is trying to load another page, or reloads current page
		 * show a confirmation dialog when there are unsaved changes
		 */
		_window.onbeforeunload = function( e ) {
			if ( self.isPageChanged() ) {
				e.preventDefault();
				// The return string is needed for browser compat
				// See https://developer.mozilla.org/en-US/docs/Web/API/Window/beforeunload_event
				return $usb.getTextTranslation( 'page_leave_warning' );
			}
		};

		/**
		 * @var {{}} Bondable events
		 */
		self._events = {
			dragstart: self._dragstart.bind( self ),
			iframeReady: self._iframeReady.bind( self ),
			maybeDrag: self._maybeDrag.bind( self ),
			maybeStartDrag: self._maybeStartDrag.bind( self ),
			modeChanged: self._modeChanged.bind( self ),

			// Event handler's for iframe
			elmCopy: self._elmCopy.bind( self ),
			elmPaste: self._elmPaste.bind( self ),
			elmDelete: self._elmDelete.bind( self ),
			elmDuplicate: self._elmDuplicate.bind( self ),
			elmSelected: self._elmSelected.bind( self ),
			endDrag: self._endDrag.bind( self ),
		};

		// Get nodes after the document is ready
		$( function() {

			// Elements
			self.$container = $( container );

			// Events
			$usb.$document
				.on( 'dragstart', self._events.dragstart ); // reset drag start defaults
		} );

		// Private events
		$usb
			.on( 'iframeReady', self._events.iframeReady )
			.on( 'builder.modeChanged', self._events.modeChanged )
			.on( 'builder.endDrag', self._events.endDrag ) // the drag completion handler in the iframe
			.on( 'builder.elmCopy', self._events.elmCopy ) // handler of copy shortcode to clipboard
			.on( 'builder.elmPaste', self._events.elmPaste ) // handler of paste shortcode to content
			.on( 'builder.elmDelete', self._events.elmDelete ) // handler when delete element
			.on( 'builder.elmDuplicate', self._events.elmDuplicate ) // handler when duplicate element
			.on( 'builder.elmSelected', self._events.elmSelected ); // handler of select an element, and get an id
	};

	/**
	 * @var {Prototype}
	 */
	var prototype = Builder.prototype;

	// Private Events
	$.extend( prototype, $ush.mixinEvents, {
		/**
		 * The handler that is called every time the mode is changed
		 *
		 * @event handler
		 */
		_modeChanged: function() {
			$usb.postMessage( 'doAction', 'hideHighlight' ); // hide all highlights
		},

		/**
		 * Iframe ready event handler
		 *
		 * @event handler
		 */
		_iframeReady: function() {
			var self = this;
			if ( ! $usb.iframeIsReady ) {
				return;
			}

			// Get iframe window
			var iframeWindow = $usb.iframe.contentWindow;

			// If meta parameters are set for preview we ignore data save
			if ( ( iframeWindow.location.search || '' ).indexOf( '&meta' ) !== -1 ) {
				return;
			}

			// The hide all highlights
			$usb.postMessage( 'doAction', 'hideHighlight' );

			/**
			 * Note: The data is unrelated because the preview can be reloaded to show the changes
			 * @var {{}} Import data and save the current and last saved object
			 */
			self.pageData = $ush.clone( ( iframeWindow.$usbdata || {} ).pageData || {}, _$defaultPageData );
			_$temp.savedPageData = $ush.clone( self.pageData ); // set first saved pageData
		},

		/**
		 * Handler when the select an element
		 *
		 * @event handler
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 */
		_elmSelected: function( id ) {
			var self = this;
			if (
				! self.isMode( 'editor' )
				|| ! self.doesElmExist( id )
			) {
				return;
			}

			// Set the active element in navigator
			$usb.navigator.setActive( id, /* expand parents */true );

			if ( self.selectedElmId === id ) {
				return;
			}

			if ( self.doesElmExist( id ) ) {
				if ( $usb.find( 'panel' ) ) {
					// Reset scroll after fieldset init
					self.one( 'panel.afterInitFieldset', function() {
						$usb.panel.resetBodyScroll();
					} );
					$usb.builderPanel.initElmFieldset( id ); // show fieldset for element
				}
			} else {
				// The hide all highlights
				$usb.postMessage( 'doAction', 'hideHighlight' );
			}
		},

		/**
		 * Handler when the duplicate element
		 *
		 * @event handler
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 */
		_elmDuplicate: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) ) {
				return;
			}
			var // Determine the need to update include the parent
				isUpdateIncludeParent = self.isUpdateIncludeParent( id ),
				// Get parent ID
				parentId = self.getElmParentId( id ),
				// Get shortcode string
				strShortcode = self.getElmShortcode( id ) || '',
				newId; // new spare ID

			strShortcode = strShortcode
				// Remove all `el_id` from the design_options
				.replace( /(\s?el_id="([^\"]+)")/gi, '' )
				// Replace all ids
				.replace( /usbid="([^\"]+)"/gi, function( _, elmId ) {
					elmId = self.getSpareElmId( elmId );
					if ( ! newId ) {
						newId = elmId;
					}
					return 'usbid="'+ elmId +'"';
				} );

			if ( ! strShortcode || ! newId ) return;

			// Determine index for duplicate
			var index = 0,
				siblingsIds = self.getElmSiblingsId( id ) || [];
			for ( var i in siblingsIds ) {
				if ( siblingsIds[ i ] === id ) {
					index = ++i;
					break;
				}
			}

			// Added shortcode to content
			if ( ! self._addShortcodeToContent( parentId, index, strShortcode ) ) {
				return;
			}

			var // Position to add on the preview page
				position = 'after',
				isContainer = self.isElmContainer( id );

			// Add temporary loader
			$usb.postMessage( 'showPreloader', [ id, position, isContainer, /* preloader id */newId ] );

			// Get a rendered shortcode
			self._renderShortcode( /* request id */newId, {
				data: {
					content: isUpdateIncludeParent
						? self.getElmShortcode( parentId )
						: strShortcode
				},
				success: function( res ) {
					// Remove temporary loader
					$usb.postMessage( 'hidePreloader', newId );
					if ( ! res.success ) {
						return;
					}
					var html = $ush.toString( res.data.html );
					// Show all elements that have animations
					html = html.replace( /(us_animate_this)/g, "$1 start" );

					// Add new shortcde to preview page
					if ( isUpdateIncludeParent ) {
						$usb.postMessage( 'updateSelectedElm', [ parentId, html ] );
					} else {
						$usb.postMessage( 'insertElm', [ id, position, html ] );
						// Init its JS if needed
						$usb.postMessage( 'maybeInitElmJS', newId );
					}

					// Commit to save changes to history
					$usb.history.commitChange( newId, _CHANGED_ACTION_.CREATE );

					// Event for react in extensions
					$usb.trigger( 'builder.contentChange' );
				},
				abort: function( abortId ) {
					$usb.postMessage( 'hidePreloader', abortId );
				}
			} );
		},

		/**
		 * Handler for copy shortcode to clipboard.
		 *
		 * @event handler
		 * @param {String} id Shortcode's usbid, e.g. "vc_row:1".
		 */
		_elmCopy: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) ) {
				return;
			}
			// Add copied text to buffer
			var content = $ush.toString( self.getElmShortcode( id ) );
			$usbcore.copyTextToClipboard( content.replace( /\susbid="([^\"]+)"/gi, '' ) );
			// Note: We will save the content in the storage unchanged,
			// and when adding it to the page, we will update all IDs.
			$usbcore.storage( 'usb' ).set( 'сlipboard', content );
		},

		/**
		 * Handler for paste shortcode to content.
		 *
		 * @event handler
		 * @param {String} id Shortcode's usbid, e.g. "vc_row:1".
		 */
		_elmPaste: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) ) {
				return;
			}
			var content = $ush.toString( $usbcore.storage( 'usb' ).get( 'сlipboard' ) );
			if ( ! content ) {
				$usb.notify.add( $usb.getTextTranslation( 'empty_clipboard' ), _NOTIFY_TYPE_.INFO );
				return;
			}

			var newElmId;
			content = content
				// Remove all `el_id` from the design_options
				.replace( /(\s?el_id="([^\"]+)")/gi, '' )
				// Replace all ids with current ones
				.replace( /usbid="([^\"]+)"/gi, function( _, elmId ) {
					elmId = self.getSpareElmId( elmId );
					if ( ! newElmId ) {
						newElmId = elmId;
					}
					return 'usbid="'+ elmId +'"';
				} );

			// Strict mode is a hard dependency between elements!
			// The check if the moved element is a TTA elements, section or vc_column(_inner), if so, then enable strict mode.
			var strictMode = (
				self.isElmTTA( id )
				|| self.isChildElmContainer( id )
			);

			// Define the container into which the element will be added
			var parentId = id
			if (
				( self.isRow( newElmId ) && self.isRow( id ) )
				|| ( self.isRowInner( newElmId ) && self.isRowInner( id ) )
				|| ( self.isElmSection( newElmId ) && self.isElmSection( id ) )
			) {
				parentId = self.getElmParentId( id );
			}

			// Check if the element can be a child of the hover element
			if (
				! self.canBeChildOf( newElmId, parentId, strictMode )
				// Note: Only in this place is it allowed to add sections to the TTA
				&& ! (
					self.isElmSection( newElmId ) && self.isElmTTA( parentId )
				)
			) {
				$usb.notify.add( $usb.getTextTranslation( 'cannot_paste' ), _NOTIFY_TYPE_.INFO );
				return;
			}

			// Get index for new element
			var index = 0;
			if ( parentId !== id ) {
				index = $ush.parseInt( self.getElmIndex( id ) ) + 1; // next index

				// Section at the end
			} else if ( self.isElmTTA( parentId ) ) {
				index = self.getElmChildren( parentId ).length + 1; // end
			}

			// Add shortcodes to content
			if ( ! self._addShortcodeToContent( parentId, index, content ) ) {
				$usb.notify.add( $usb.getTextTranslation( 'invalid_data' ), _NOTIFY_TYPE_.ERROR );
				return;
			}

			// Determine the position for the new element
			var position;
			if ( index === 0 ) {
				position = 'prepend';
			} else {
				position = 'after';
			}

			// Add temporary loader
			$usb.postMessage( 'showPreloader', [ id, position, self.isElmContainer( id ), /* preloader id */newElmId ] );

			// Determine the need to update include the parent
			var isUpdateIncludeParent = self.isUpdateIncludeParent( newElmId );

			// Render pasted content
			$usb.builder._renderShortcode( /* request id */newElmId, {
				data: {
					isReturnContent: true, // Add content to the result (This can be useful for complex changes)
					content: isUpdateIncludeParent
						? self.getElmShortcode( parentId )
						: content
				},
				// Successful request handler
				success: function( res ) {
					// Remove temporary loader
					$usb.postMessage( 'hidePreloader', newElmId );
					if ( ! res.success || ! res.data.html ) {
						return;
					}

					// Get html content
					var html = $ush.toString( res.data.html )
						// Show all elements that have animations
						.replace( /(us_animate_this)/g, "$1 start" );

					// Add new shortcde to preview page
					if ( isUpdateIncludeParent ) {
						$usb.postMessage( 'updateSelectedElm', [ parentId, html ] );
						// Commit to save changes to history
						$usb.history.commitChange( parentId, _CHANGED_ACTION_.UPDATE );

					} else {
						$usb.postMessage( 'insertElm', [ id, position, html ] );
						$usb.postMessage( 'maybeInitElmJS', newElmId ); // init its JS if needed
						// Commit to save changes to history
						$usb.history.commitChange( newElmId, _CHANGED_ACTION_.CREATE );
					}

					// Event for react in extensions
					$usb.trigger( 'builder.contentChange' );
				},
				abort: function( abortId ) {
					$usb.postMessage( 'hidePreloader', abortId );
				}
			} );
		},

		/**
		 * Handler when the delete element
		 *
		 * @event handler
		 * @param {String} removeId Shortcode's usbid, e.g. "us_btn:1"
		 */
		_elmDelete: function( removeId ) {
			var self = this;
			if ( ! self.isValidId( removeId ) ) {
				return;
			}

			// The check if this is the last column then delete the parent row*
			if (
				self.isChildElmContainer( removeId )
				&& self.getElmSiblingsId( removeId ).length === 1
			) {
				removeId = self.getElmParentId( removeId );
			}

			// Remove the element
			self.removeElm( removeId );
		}
	});

	// Functionality for add new elements via Drag & Drop
	$.extend( prototype, {

		// The number of pixels when drag after which the movement will be initialized
		_dragStartDistance: 5, // the recommended value of 3, which will be optimal for all browsers, was found out after tests

		/**
		 * Standard `dragstart` browser event handler
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 * @return {Boolean} If the event occurs in context `MediaFrame`, then we will enable it, otherwise we will
		 *     disable it
		 */
		_dragstart: function( e ) {
			return !! $( e.target ).closest( '.media-frame' ).length;
		},

		/**
		 * Init Drag & Drop
		 */
		_initDragDrop: function() {
			var self = this;
			if ( _$temp.isInitDragDrop ) {
				return;
			}
			_$temp.isInitDragDrop = true;

			// Track events for Drag & Drop
			$usb.$document
				.on( 'mousedown', self._events.maybeStartDrag )
				.on( 'mousemove', self._events.maybeDrag )
				.on( 'mouseup', self._events.endDrag );

			// Reset all data by default for more reliable operation
			$usbcore.cache( 'drag' ).set( {
				startX: 0, // x-axis start position
				startY: 0 // y-axis start position
			} );
		},

		/**
		 * Destroy Drag & Drop
		 */
		_destroyDragDrop: function() {
			var self = this;
			if ( ! _$temp.isInitDragDrop ) {
				return;
			}
			_$temp.isInitDragDrop = false;

			// Remove events
			$usb.$document
				.off( 'mousedown', self._events.maybeStartDrag )
				.off( 'mousemove', self._events.maybeDrag )
				.off( 'mouseup', self._events.endDrag );

			$usbcore.cache( 'drag' ).flush(); // flush data
		},

		/**
		 * Get a new unique id for an element
		 *
		 * @return {String} The unique id e.g. "us_btn:1"
		 */
		getNewElmId: function() {
			return $usbcore.cache( 'drag' ).get( 'newElmId', /* default */'' );
		},

		/**
		 * Get the event data for send iframe
		 *
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 * @return {{}} The event data
		 */
		_getEventData: function( e ) {
			var self = this;
			if ( ! $usb.iframeIsReady ) {
				return;
			}

			// Get data on the coordinates of the mouse for iframe and relative to this iframe
			var rect = $ush.$rect( $usb.iframe ),
				iframeWindow = $usb.iframe.contentWindow,
				data = {
					clientX: e.clientX,
					clientY: e.clientY,
					eventX: e.pageX - rect.x,
					eventY: e.pageY - rect.y,
					pageX: ( e.pageX + iframeWindow.scrollX ) - rect.x,
					pageY: ( e.pageY + iframeWindow.scrollY ) - rect.y,
				};
			// Additional check of values for errors
			for ( var prop in data ) {
				var value = data[ prop ] || NaN;
				if ( isNaN( value ) || value < 0 ) {
					data[ prop ] = 0;
				} else {
					data[ prop ] = ceil( data[ prop ] );
				}
			}
			return data;
		},

		/**
		 * Determines if parent drag
		 *
		 * @return {Boolean} True if drag, False otherwise
		 */
		isParentDragging: function() {
			return !! _$temp.isParentDragging;
		},

		/**
		 * Show the transit
		 *
		 * @param {String} type The type element
		 */
		showTransit: function( type ) {
			var self = this;
			if ( ! type ) {
				return;
			}

			// The destroy an object if it is set
			if ( self.hasTransit() ) {
				self.hideTransit();
			}

			// If type is an `id` then we get from `id` type
			if ( self.isValidId( type ) ) {
				type = self.getElmType( type );
			}

			// Get a node by attribute type
			var target = _document.querySelector( '[data-type="'+ type +'"]' );

			// Show templates transit for get rect
			var isTemplate = $usb.templates.isTemplate( type );
			if ( isTemplate ) {
				target = $usb.templates.showTransit();
			}

			if ( ! $ush.isNode( target ) ) {
				return;
			}

			// Object with intermediate data for transit
			var transit = {
				rect: $ush.$rect( target ),
				scrollAcceleration: 0, // scroll acceleration while drag
				scrollDirection: _undefined, // scroll directions while drag
				target: target.cloneNode( /* deep */true ) // copy of target to transit
			};

			$usbcore // Remove class `hidden` if element is hidden
				.$removeClass( transit.target, 'hidden' );

			// Hide templates transit
			if ( isTemplate ) {
				$usb.templates.hideTransit();
			}

			// Set the height and width of the transit element
			[ 'width', 'height' ].map( function( prop ) {
				var value = ceil( transit.rect[ prop ] );
				transit.target.style[ prop ] = value
					? value + 'px'
					: 'auto';
			} );

			$usbcore // Add css class to apply basic styles
				.$addClass( transit.target, 'elm_transit' )
				.$addClass( transit.target, ! self.isMode( 'drag:add' ) ? 'state_drag_move' : '' );

			// Add transit element to document
			_document.body.append( transit.target );

			// Save transit to _$temp
			_$temp.transit = transit;
		},

		/**
		 * Determines if transit
		 *
		 * @return {Boolean} True if transit, False otherwise
		 */
		hasTransit: function() {
			return !! _$temp.transit;
		},

		/**
		 * Determines if drag scroll
		 *
		 * @return {Boolean} True if drag scroll, False otherwise
		 */
		hasDragScrolling: function() {
			return $usbcore.indexOf( ( _$temp.transit || {} ).scrollDirection, [ _DIRECTION_.TOP, _DIRECTION_.BOTTOM ] ) > -1;
		},

		/**
		 * Set the transit position
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @param {Number} pageX The event.pageX
		 * @param {Number} pageY The event.pageY
		 */
		setTransitPosition: function( pageX, pageY ) {
			var self = this;
			if (
				! self.hasTransit()
				|| ! self.isMode( 'drag:add', 'drag:move' )
			) {
				return;
			}
			var transit = _$temp.transit || {};
			if ( ! $ush.isNode( transit.target ) ) {
				return;
			}

			// Get indents to transit center
			var isDragAdd = self.isMode( 'drag:add' ),
				transitHeight = transit.rect.height,
				transitTop = $ush.parseInt( isDragAdd ? pageY - ( transitHeight / 2 ) : pageY ),
				transitLeft = $ush.parseInt( isDragAdd ? pageX - ( transit.rect.width / 2 ) : pageX );

			// Set transit center in under cursor
			transit.target.style.top = transitTop.toFixed( 3 ) + 'px';
			transit.target.style.left = transitLeft.toFixed( 3 ) + 'px';

			if ( ! $usb.iframeIsReady ) {
				return;
			}

			// Control auto-scroll preview when drag
			var remainderToEnd = 0, // Remainder to scroll end point (up|down)
				scrollDirection, // No value does not start animation
				viewportBottom = $ush.parseInt( _window.innerHeight - transitHeight );

			// Get direction to scroll preview
			if ( pageY < transitHeight ) {
				remainderToEnd = abs( pageY - transitHeight );
				scrollDirection = _DIRECTION_.TOP;

			} else if ( pageY > viewportBottom ) {
				remainderToEnd = abs( pageY - viewportBottom );
				scrollDirection = _DIRECTION_.BOTTOM;
			}

			// Note: After pass every `step` pixels, the speed will increase by x1 ( speed / scrollAcceleration )
			var scrollAcceleration = ceil( abs( remainderToEnd / /* acceleration step in px */30 ) );

			// Transit data updates and scroll control
			if (
				scrollDirection !== transit.scrollDirection
				|| scrollAcceleration !== transit.scrollAcceleration
			) {
				transit.scrollDirection = scrollDirection;
				transit.scrollAcceleration = scrollAcceleration;
				$usb.postMessage( 'doAction', [
					/* method */'_scrollDragging',
					/* params */[ scrollDirection, scrollAcceleration ]
				] );
			}
		},

		/**
		 * Hide the transit
		 */
		hideTransit: function() {
			var self = this,
				transit = _$temp.transit || {};
			if (
				! self.hasTransit()
				|| ! $ush.isNode( transit.target )
			) {
				return;
			}
			self.stopDragScrolling(); // stop drag scroll
			$usbcore.$remove( transit.target );
			delete _$temp.transit;
		},

		/**
		 * Determines the start of move elements
		 * This should be a single method to determine if something needs to be moved or not
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_maybeStartDrag: function( e ) {
			var self = this;
			// If there is no target, then terminate the method
			if ( ! $usb.iframeIsReady || ! e.target ) {
				return;
			}
			var found,
				iteration = 0,
				target = e.target;
			// The check if the goal is a new element
			while ( ! ( found = !! $usbcore.$attr( target, 'data-type' ) ) && iteration++ < /*max number of iterations*/100 ) {
				if ( ! target.parentNode ) {
					found = false;
					break;
				}
				target = target.parentNode;
			}
			// If it was possible to determine the element, then we will save all the data into a temporary variable
			if ( found ) {
				// Set drag data to cache
				$usbcore.cache( 'drag' ).set( {
					startDrag: true,
					startX: e.pageX || 0,
					startY: e.pageY || 0,
					target: target,
				} );
			}
		},

		/**
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_maybeDrag: function( e ) {
			var self = this,
				dragData = $usbcore.cache( 'drag' ).data(); // get drag data

			if ( ! dragData.startDrag || ! dragData.target ) {
				return;
			}

			// Get offsets from origin along axis X and Y
			var diffX = abs( dragData.startX - e.pageX ),
				diffY = abs( dragData.startY - e.pageY );

			// The check the distance of the mouse drag and if it is more than
			// the specified one, then activate all the necessary methods
			if ( diffX > self._dragStartDistance || diffY > self._dragStartDistance ) {
				if ( self.isMode( 'editor' ) ) {
					// Flush active move data
					$usbcore.cache( 'dragProcessData' ).flush();
					// Set mode parent drag
					_$temp.isParentDragging = true;
					// Select mode of add elements
					self.setMode( 'drag:add' );
					// Get target type
					var tempTargetType = $usbcore.$attr( dragData.target, 'data-type' );
					// Set new element ID ( Save to cache is required for `self.getNewElmId()` )
					dragData.newElmId = self.getSpareElmId( tempTargetType );
					// Show the transit
					self.showTransit( tempTargetType, e.pageX, e.pageY );
					// Add helpers classes for visual control
					$usbcore
						.$addClass( dragData.target, 'elm_add_shadow' )
						.$addClass( _document.body, 'elm_add_draging' );
				}
				// Firefox blocks events between current page and iframe so will use `onParentEventData`
				// Other browsers in iframe intercepts events
				if ( $ush.isFirefox && self.isParentDragging() ) {
					var eventData = self._getEventData( e );
					if ( eventData.pageX ) {
						$usb.postMessage( 'onParentEventData', [ '_maybeDrop', eventData ] );
					}
				}

				// Set the transit element position
				self.setTransitPosition( e.pageX, e.pageY );
			}
		},

		/**
		 * End a drag
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_endDrag: function( e ) {
			var self = this;
			if ( ! $usb.iframeIsReady ) {
				return;
			}

			// Get drag data
			var dragData = $usbcore.cache( 'drag' ).data();

			// Remove classes
			if ( $ush.isNode( dragData.target ) ) {
				$usbcore
					.$removeClass( dragData.target, 'elm_add_shadow' )
					.$removeClass( _document.body, 'elm_add_draging' );
			}

			// Case relevant only for FF when a new element has been dropped above
			// the panel and should not be added to the page
			if (
				$usb.panel.isShow()
				&& $ush.isFirefox
				&& $usb.preview.getCurrentOffset().x >= e.clientX
			) {
				// Clear all asset and cache data to drag:add
				self._clearDragAssets();
				return;
			}

			// Check is parent drag
			if ( ! self.isParentDragging() ) {
				$usbcore.cache( 'drag' ).flush(); // flush data
				return;
			}

			// Create the new element
			if ( !! dragData.parentId && !! dragData.currentId ) {
				var currentIndex = $ush.parseInt( dragData.currentIndex );

				// Get base parentId without alias
				if ( self.isAliasElmId( dragData.parentId ) ) {
					dragData.parentId = self.removeAliasFromId( dragData.parentId );
				}

				// If the target has a template id, then continue processing as a template
				var templateId = $usbcore.$attr( dragData.target, 'data-template-id' );
				if ( templateId ) {
					var templateCategoryId = $( dragData.target )
						.closest( '.usb-template' )
						.data( 'template-category-id' );

					// Insert template in content and preview
					$usb.templates.insertTemplate( templateCategoryId, templateId, dragData.parentId, currentIndex );
				}
				else {
					// Create and add a new element
					self.createElm( self.getElmType( dragData.currentId ), dragData.parentId, currentIndex );
				}

				// If the final container is a TTA section then open this section
				if ( self.isElmSection( dragData.parentId ) ) {
					$usb.postMessage( 'doAction', [ 'openSectionById', dragData.parentId ] );
				}
			}

			// Firefox blocks events between current page and frame so will use onParentEventData
			// Other browsers in iframe intercepts events
			if ( $ush.isFirefox ) {
				$usb.postMessage( 'onParentEventData', '_endDrag' );
			}

			// Clear all asset and cache data to drag:add
			self._clearDragAssets();
		},

		/**
		 * Clear all asset and cache data to `drag:add`
		 */
		_clearDragAssets: function() {
			var self = this;
			self.hideTransit(); // hide transit
			_$temp.isParentDragging = false; // reset parent drag
			$usbcore.cache( 'drag' ).flush(); // flush data
			self.setMode( 'editor' ); // set editor mode
			// Clear all asset and temporary data to move
			$usb.postMessage( 'doAction', 'clearDragAssets' );
		},

		/**
		 * Remove a drag scroll data
		 */
		removeDragScrollData: function() {
			delete ( _$temp.transit || {} ).scrollDirection;
		},

		/**
		 * Stop a drag scroll
		 */
		stopDragScrolling: function() {
			var self = this,
				transit = _$temp.transit || {};
			if ( ! self.hasDragScrolling() ) {
				return;
			}
			self.removeDragScrollData(); // remove a drag scroll data
			$usb.postMessage( 'doAction', '_scrollDragging' );
		}
	} );

	// Builder API
	$.extend( prototype, {
		/**
		 * Determines if process save
		 *
		 * @return {Boolean} True if process save, False otherwise
		 */
		isProcessSave: function() {
			return _$temp.isProcessSave;
		},

		/**
		 * Save page data on the server
		 *
		 * @param {Function} complete The complete
		 */
		savePageData: function( complete ) {
			var self = this;

			// The page data
			var data = {
				// The available key=>value:
				//	post_content: '',
				//	post_status: '' ,
				//	post_title: '',
				//	pageMeta: [ key => value ]
				pageMeta: {},
			};

			// Add updated content
			if ( self.isContentChanged() ) {
				data.post_content = self.pageData.content;
			}
			if ( self.isPageFieldsChanged() ) {
				for ( var prop in self.pageData.fields ) {
					data[ prop ] = self.pageData.fields[ prop ];
 				}
			}
			// Add updated meta data
			if ( self.isPageMetaChanged() ) {
				for ( var prop in self.pageData.pageMeta ) {
					data.pageMeta[ prop ] = self.pageData.pageMeta[ prop ];
				}
			}
			if ( self.isPageCustomCssChanged() ) {
				data.pageMeta[ $usb.config( 'keyCustomCss', '' ) ] = self.pageData.customCss;
			}

			// Set the save execution flag
			_$temp.isProcessSave = true;

			// Send data to server
			$usb.ajax( /* request id */'_savePageData', {
				data: $.extend( data, {
					_nonce: $usb.config( '_nonce' ),
					action: $usb.config( 'action_save_post' ),
				} ),
				// Handler to be called if the request succeeds
				success: function( res ) {
					if ( ! res.success ) {
						return;
					}
					$usb.notify.add( $usb.getTextTranslation( 'page_updated' ), _NOTIFY_TYPE_.SUCCESS );
					// Reload preview page
					if (
						self._isReloadPreviewAfterSave
						&& self.isPageMetaChanged()
					) {
						self._isReloadPreviewAfterSave = false; // reset value after page reload
						$usb.reloadPreview(); // refresh preview
					}

					// Save the last page data
					_$temp.savedPageData = $ush.clone( self.pageData );
				},
				// Handler to be called when the request finishes (after success and error callbacks are executed)
				complete: function() {
					if ( $.isFunction( complete ) ) {
						complete();
					}
					_$temp.isProcessSave = false;
				}
			} );
		},

		/**
		 * Determines if ontent hanged
		 *
		 * @return {Boolean} True if ontent hanged, False otherwise
		 */
		isContentChanged: function() {
			return ( _$temp.savedPageData.content || '' ) !== ( this.pageData.content || '' );
		},

		/**
		 * Determines if page custom css hanged
		 *
		 * @return {Boolean} True if page custom css hanged, False otherwise
		 */
		isPageCustomCssChanged: function() {
			return ( _$temp.savedPageData.customCss || '' ) !== ( this.pageData.customCss || '' );
		},

		/**
		 * Determines if page fields changed
		 *
		 * @return {Boolean} True if page fields changed, False otherwise
		 */
		isPageFieldsChanged: function() {
			return ! $usbcore.comparePlainObject( _$temp.savedPageData.fields, this.pageData.fields );
		},

		/**
		 * Determines if page meta data changed
		 *
		 * @return {Boolean} True if page meta data changed, False otherwise
		 */
		isPageMetaChanged: function() {
			return ! $usbcore.comparePlainObject( _$temp.savedPageData.pageMeta, this.pageData.pageMeta );
		},

		/**
		 * Determines if page changed
		 *
		 * @return {Boolean} True if page changed, False otherwise
		 */
		isPageChanged: function() {
			var self = this;
			return (
				self.isContentChanged()
				|| self.isPageMetaChanged()
				|| self.isPageFieldsChanged()
				|| self.isPageCustomCssChanged()
			);
		},

		/**
		 * Save content temporarily in a temporary variable, this is necessary
		 * for the move state where the moved element should not be present in
		 * the content. These method are mainly needed for Drag & Drop in move state
		 */
		saveTempContent: function() {
			_$temp.tempContent = '' + this.pageData.content;
		},

		/**
		 * Restore content from a temporary variable, these method are mainly
		 * needed for Drag & Drop in move state. This method works from `self.saveTempContent()`
		 *
		 * @return {Boolean} True if the content has been restored, False otherwise
		 */
		restoreTempContent: function() {
			var self = this;
			if ( ! self.isEmptyTempContent() ) {
				self.pageData.content = ( '' + _$temp.tempContent ) || self.pageData.content;
				delete _$temp.tempContent;
				return true
			}
			return false;
		},

		/**
		 * This method to determine if temporary content is installed
		 *
		 * @return {Boolean} True if temporary content, False otherwise
		 */
		isEmptyTempContent: function() {
			return $ush.isUndefined( _$temp.tempContent )
		},

		/**
		 * This method determines whether the page content is empty or not
		 *
		 * @return {Boolean} True if empty content, False otherwise
		 */
		isEmptyContent: function() {
			return ( '' + this.pageData.content ).indexOf( '[vc_row' ) === -1;
		},

		/**
		 * Determine if the value is an object of the responsive format
		 *
		 * @param {mixed} value The value
		 * @return {boolean} True if the specified value is responsive object, False otherwise
		 */
		isResponsiveObject: function( value ) {
			if ( ! $.isPlainObject( value ) ) {
				return false;
			}
			// Get responsive states
			var states = $usb.config( 'responsiveStates', [] );
			for ( var i in states ) if ( value.hasOwnProperty( states[ i ] ) ) {
				return true;
			}
			return false;
		},

		/**
		 * Determines whether the specified mode is valid mode
		 *
		 * @param {String} mode The mode
		 * @return {Boolean} True if the specified mode is valid mode, False otherwise
		 */
		isValidMode: function( mode ) {
			/**
			 * @var {{}} Available modes
			 */
			var _availableModes = [
				'unknown', // mode disables all of the following
				'editor', // shortcode editing mode
				'preview', // preview mode without saving
				'drag:add', // mode of add a new element
				'drag:move', // mode of movement of the element
			];
			return mode && $usbcore.indexOf( mode, _availableModes ) > -1;
		},

		/**
		 * Determines if mode.
		 * As parameters, you can set both one mode and several to check for matches,
		 * if at least one of the results matches, then it will be true
		 *
		 * @return {Boolean} Returns true if there is a mode, otherwise false
		 */
		isMode: function() {
			// Get set modes, example: 'unknown', editor', 'preview', 'drag:add', 'drag:move'
			var self = this,
				args = arguments;
			for ( var i in args ) {
				if ( self.isValidMode( args[ i ] ) && _$mode === args[ i ] ) return true;
			}
			return false;
		},

		/**
		 * Set the mode
		 *
		 * @param {String} mode The mode
		 * @return {Boolean} True if mode changed successfully, False otherwise
		 */
		setMode: function( mode ) {
			var self = this;
			if (
				mode
				&& self.isValidMode( mode )
				&& mode !== _$mode
			) {
				$usb.trigger( 'builder.modeChanged', _$mode = mode );
				return true;
			}
			return false;
		},

		/**
		 * Generate a RegExp to identify a shortcode
		 * Note: RegExp does not know how to work with neste the shortcode in itself.
		 *
		 * Capture groups:
		 *
		 * 1. An extra `[` to allow for escape shortcodes with double `[[]]`
 		 * 2. The shortcode name
 		 * 3. The shortcode argument list
 		 * 4. The self close `/`
 		 * 5. The content of a shortcode when it wraps some content
 		 * 6. The close tag
 		 * 7. An extra `]` to allow for escape shortcodes with double `[[]]`
		 *
		 * @param {String} tag The shortcode tag "us_btn" or "vc_row|vc_column|..."
		 * @return {RegExp} The elm shortcode regular expression
		 */
		getShortcodePattern: function( tag ) {
			return new RegExp( '\\[(\\[?)(' + tag + ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*(?:\\[(?!\\/\\2\\])[^\\[]*)*)(\\[\\/\\2\\]))?)(\\]?)', 'g' );
		},

		/**
		 * Remove html from start and end content
		 *
		 * @param {String} content
		 * @return {String}
		 */
		removeHtmlWrap: function( content ) {
			return $ush.toString( content )
				.replace( /^<[^\[]+|[^\]]+$/gi, '' );
		},

		/**
		 * Parse shortcode text in parts
		 *
		 * @param {String} shortcode The shortcode text
		 * @return {{}}
		 */
		parseShortcode: function( shortcode ) {
			var self = this;
			if ( ! shortcode ) {
				return {};
			}
			// Remove html from start and end of content
			shortcode = self.removeHtmlWrap( shortcode );

			// Get shortcode parts
			var firstTag = ( shortcode.match( /^.*?\[([\w\-]+)\s/ ) || [] )[ /* tag name */1 ] || '',
				result = ( self.getShortcodePattern( firstTag ) ).exec( shortcode );

			if ( result ) {
				return {
					tag: result[ 2 ], // the shortcode tag of the current object
					atts: self._unescapeAttr( result[ 3 ] || '' ), // the a string representation of the shortcode attributes
					input: result[ 0 ], // the input shortcode text
					content: result[ 5 ] || '', // the content of the shortcode if there is of course
					hasClosingTag: !! result[ 6 ] // the need for an close tag
				};
			}

			return {};
		},

		/**
		 * Convert attributes from string to object
		 *
		 * @param {String} atts The string atts
		 * @return {{}}
		 */
		parseAtts: function( str ) {
			var result = {};
			if ( ! str ) {
				return result;
			}
			// Map zero-width spaces to actual spaces
			str = str.replace( /[\u00a0\u200b]/g, ' ' );
			// The retrieve attributes from a string
			( str.match( /[\w-_]+="([^\"]+)?"/g ) || [] ).forEach( function( attribute ) {
				attribute = attribute.match( /([\w-_]+)="([^\"]+)?"/ );
				if ( ! attribute ) {
					return;
				}
				// Restoring escaped values from a shortcode attribute
				var value = $ush.toString( attribute[ /* value */2 ] )
					.replace( /``/g, '"' )
					.replace( /`{`/g, '[' )
					.replace( /`}`/g, ']' );
				result[ attribute[ /* key */1 ] ] = value.trim();
			});
			return result;
		},

		/**
		 * Converts a shortcode object to a string
		 *
		 * @param {{}} object The shortcode object
		 * @param {{}} attsDefaults The default atts
		 * @return {String}
		 */
		buildShortcode: function( shortcode, attsDefaults ) {
			if ( $.isEmptyObject( shortcode ) ) {
				return '';
			}
			var self = this,
				// Create shortcode
				result = '[' + shortcode.tag;
			// The add attributes
			if ( shortcode.atts || attsDefaults ) {
				if ( ! $.isEmptyObject( attsDefaults ) ) {
					shortcode.atts = self.buildAtts( self.parseAtts( shortcode.atts ), attsDefaults );
				}
				// Escape for shortcode attributes
				shortcode.atts = self._escapeAttr( shortcode.atts );
				result += ' ' + shortcode.atts.trim();
			}
			result += ']';
			// The add content
			if ( shortcode.content ) {
				result += shortcode.content;
			}
			// The add end tag
			if ( shortcode.hasClosingTag ) {
				result += '[/'+ shortcode.tag +']';
			}
			return '' + result;
		},

		/**
		 * Returns a string representation of an attributes
		 *
		 * @param {{}} atts This is an attributes object
		 * @param {{}} defaults The default atts
		 * @return {String} String representation of the attributes
		 */
		buildAtts: function( atts, defaults ) {
			if ( ! atts || $.isEmptyObject( atts ) ) {
				return '';
			}
			if ( $.isEmptyObject( defaults ) ) {
				defaults = {};
			}
			var result = [];
			for ( var k in atts ) {
				var value = atts[ k ];
				// Check the values for correctness, otherwise we will skip the additions
				if (
					value === null
					|| $ush.isUndefined( value )
					|| (
						! $ush.isUndefined( defaults[ k ] )
						&& defaults[ k ] === value
					)
				) {
					continue;
				}
				// Convert parameter list to string (for wp link)
				if ( $.isPlainObject( value ) ) {
					var inlineValue = [];
					for ( var i in value ) {
						if ( value[ i ] ) {
							inlineValue.push( i + ':' + value[ i ] );
						}
					}
					value = inlineValue.join('|');
				}
				// Escaping reserved values for a shortcode attribute
				value = $ush.toString( value )
					.replace( /\"/g, '``' )
					.replace( /\[/g, '`{`' )
					.replace( /\]/g, '`}`' );
				result.push( k + '="' + value + '"' );
			}
			return result.join( ' ' );
		},

		/**
		 * Determines whether the specified id is valid ID
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {Boolean} True if the specified id is valid id, False otherwise
		 */
		isValidId: function( id ) {
			return id && /^([\w\-]+):(\d+)(\|[a-z\-]+)?$/.test( id );
		},

		/**
		 * Determines whether the specified id is row.
		 *
		 * @param {String} id Shortcode's usbid, e.g. "vc_row:1".
		 * @return {Boolean} True if the specified id is row, False otherwise.
		 */
		isRow: function( id ) {
			return this.getElmName( id ) === 'vc_row';
		},

		/**
		 * Determines whether the specified id is row_inner.
		 *
		 * @param {String} id Shortcode's usbid, e.g. "vc_row_inner:1".
		 * @return {Boolean} True if the specified id is row_inner, False otherwise.
		 */
		isRowInner: function( id ) {
			return this.getElmName( id ) === 'vc_row_inner';
		},

		/**
		 * Determines whether the specified id is column(_inner).
		 *
		 * @param {String} id Shortcode's usbid, e.g. "vc_column:1".
		 * @return {Boolean} True if the specified id is column, False otherwise.
		 */
		isColumn: function( id ) {
			return /^vc_column(_inner)?$/.test( this.getElmName( id ) );
		},

		/**
		 * Determines whether the specified id is outside main container
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_header:1"
		 * @return {Boolean} True if the specified identifier is outside container, False otherwise
		 */
		isOutsideMainContainer: function( id ) {
			var self = this;
			return $usbcore.indexOf( self.getElmName( id ), $usb.config( 'elms_outside_main_container', [] ) ) > -1;
		},

		/**
		 * Determines whether the specified id is main container id,
		 * this is the root whose name is assigned to `self.mainContainer`,
		 * for example name: `container`
		 *
		 * @param {String} id Shortcode's usbid, e.g. "container"
		 * @return {Boolean} True if the specified id is container id, False otherwise
		 */
		isMainContainer: function( id ) {
			return id && id === this.mainContainer;
		},

		/**
		 * Determines whether the specified ID is container
		 *
		 * @param {String} name Shortcode's usbid, e.g. "vwrapper:1"
		 * @return {Boolean} True if the specified id is container, False otherwise
		 */
		isElmContainer: function( name ) {
			var self = this;
				name = self.isValidId( name )
					? self.getElmName( name )
					: name;
			return name && $usb.config( 'shortcode.containers', [] ).indexOf( name ) > -1;
		},

		/**
		 * Determines whether the specified id is node root container,
		 * for example: `vc_row`, `vc_row_inner`, `vc_tta_tabs`, `vc_tta_accordion` etc
		 *
		 * @param {String} name Shortcode's usbid, e.g. "us_btn:1"
		 * @return {Boolean} True if the specified id is elm root container, False otherwise
		 */
		isRootElmContainer: function( name ) {
			var self = this;
				name = self.isValidId( name )
					? self.getElmName( name )
					: name;
			return (
				self.isElmContainer( name )
				&& !! $usb.config( 'shortcode.relations.as_parent.' + name + '.only' )
			);
		},

		/**
		 * Determines whether the specified id is second node container,
		 * for example: `vc_column`, `vc_column_inner`, `vc_tta_section` etc
		 *
		 * @param {String} name Shortcode's usbid, e.g. "us_btn:1"
		 * @return {Boolean} True if the specified id is elm root container, False otherwise
		 */
		isChildElmContainer: function( name ) {
			var self = this;
				name = self.isValidId( name )
					? self.getElmName( name )
					: name;
			return (
				self.isElmContainer( name )
				&& ! self.isRootElmContainer( name )
				&& !! $usb.config( 'shortcode.relations.as_child.' + name + '.only' )
			);
		},

		/**
		 * Determines whether an element needs to be updated from the parent
		 *
		 * @param {String|Node} id Shortcode's usbid, e.g. "vc_tta_section:1"
		 * @return {Boolean} True if the specified id is elm parent update, False otherwise
		 */
		isUpdateIncludeParent: function( id ) {
			var self = this;
			if ( $ush.isNode( id ) ) {
				id = self.getElmId( id );
			}
			if ( ! self.isValidId( id ) ) {
				return false;
			}
			var name = self.getElmName( id );
			return name && $usb.config( 'shortcode.update_parent', [] ).indexOf( name ) > -1;
		},

		/**
		 * Determines whether the specified name is elm TTA.
		 *
		 * TTA - [T]abs [T]our [A]ccordion.
		 *
		 * @param {String} name The name e.g. "vc_tta_tabs:1".
		 * @return {Boolean} True if the specified name is elm tta, False otherwise.
		 */
		isElmTTA: function( name ) {
			var self = this;
			if ( self.isValidId( name ) ) {
				name = self.getElmType( name );
			}
			return /^vc_tta_(tabs|tour|accordion)$/.test( name );
		},

		/**
		 * Determines whether the specified name is tabs or tour.
		 *
		 * @param {String} name The name e.g. "vc_tta_tabs:1".
		 * @return {Boolean} True if the specified id is tabs or tour, False otherwise.
		 */
		isElmTab: function( name ) {
			var self = this;
			if ( self.isValidId( name ) ) {
				name = self.getElmType( name );
			}
			return /^vc_tta_(tabs|tour)$/.test( name );
		},

		/**
		 * Determines whether the specified name is tta section.
		 *
		 * @param {String} name The name.
		 * @return {Boolean} True if the specified name is tta section, False otherwise.
		 */
		isElmSection: function( name ) {
			var self = this;
			if ( self.isValidId( name ) ) {
				name = self.getElmType( name );
			}
			return name === 'vc_tta_section';
		},

		/**
		 * Escape for shortcode attributes.
		 *
		 * @param {String} value The value.
		 * @return {String} Returns a string from escaped with special characters.
		 */
		_escapeAttr: function( value ) {
			return $ush.toString( value )
				.replace( /\[/g, '&#91;' )
				.replace( /\]/g, '&#93;' );
		},

		/**
		 * Unescape for shortcode attributes.
		 *
		 * @param {String} value The value.
		 * @return {String} Returns a string from the canceled escaped special characters.
		 */
		_unescapeAttr: function( value ) {
			return $ush.toString( value )
				.replace( /&#91;/g, '[' )
				.replace( /&#93;/g, ']' );
		},

		/**
		 * Check the possibility of move the shortcode to the specified parent
		 * Note: This method has specific exceptions in `move:add` for self.mainContainer
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @param {String} parent Shortcode's usbid, e.g. "vc_column:1"
		 * @param {Boolean} strict The ON/OFF strict mode (Strict mode is a hard dependency between elements!)
		 * @return {Boolean} True if able to be child of, False otherwise
		 */
		canBeChildOf: function( id, parent, strict ) {
			var self = this,
				args = arguments,
				isMainContainer = self.isMainContainer( parent );
			if (
				self.isMainContainer( id ) // it is forbidden to move the main container!
				|| ! self.isValidId( id )
				|| ! ( self.isValidId( parent ) || isMainContainer )
			) {
				return false;
			}

			// Get all names without prefixes and indices
			var targetName = self.getElmName( id ),
				parentName = isMainContainer
					? parent
					: self.getElmName( parent ),
				// Get all relations for shortcodes
				shortcodeRelations = $.extend( {}, $usb.config( 'shortcode.relations', {} ) ),
				result = true;

			// If there are no deps, we will allow everyone to move
			if ( $.isEmptyObject( shortcodeRelations ) ) {
				$usb.log( 'Notice: There are no relations and movement is allowed for every one', args );
				return true;
			}

			// Passing the result through the drag data cache function
			return self._cacheDragProcessData(
				function() {
					/**
					 * The a check all shortcodes relations
					 *
					 * Relations name `as_parent` and `as_child` obtained from Visual Composer
					 * @see https://kb.wpbakery.com/docs/developers-how-tos/nested-shortcodes-container/
					 *
					 * Example relations: {
					 *		as_child: {
					 *			vc_row: {
					 *				only: 'container',
					 *			},
					 *			vc_tta_section: { // Separate multiple values with comma
					 *				only: 'vc_tta_tabs,vc_tta_accordion...',
					 *			},
					 *			...
					 *		},
					 *		as_parent: {
					 *			vc_row: {
					 *				only: 'vc_column',
					 *			},
					 *			hwrapper: { // Separate multiple values with comma
					 *				except: 'vc_row,vc_column...',
					 *			},
					 *			...
					 *		}
					 * }
					 */
					for ( var name in shortcodeRelations ) {
						if ( ! result ) {
							break;
						}
						var relations = shortcodeRelations[ name ][ name === 'as_child' ? targetName : parentName ];
						if ( ! $ush.isUndefined( relations ) ) {
							for ( var condition in relations ) {
								// If check occurs in `move:add` then skip the rule for the main container, when add
								// a new element, it is allowed to add simple elements to the main container
								if (
									self.isMode( 'drag:add' )
									&& parentName === self.mainContainer
									&& ! self.isChildElmContainer( id )
								) {
									continue;
								}
								// If the rules have already prohibited the specified connection, then we complete the check
								if ( ! result ) {
									break;
								}
								var allowed = ( relations[ condition ] || '' ).split(','),
									isFound = allowed.indexOf( name === 'as_child' ? parentName : targetName ) !== -1;
								if (
									( condition === 'only' && ! isFound )
									|| ( condition === 'except' && isFound )
								) {
									result = false;
								}
							}
						}
					}

					// Strict validation will ensure that secondary elements
					// are allowed to move within the same parent
					if (
						result
						&& !! strict
						&& (
							isMainContainer
							|| self.isChildElmContainer( id )
						)
					) {
						// The check if temporary content, then we will restore it to get the correct data,
						// this is only necessary for the `drag:move`
						var isTempContent = ( self.isMode( 'drag:move' ) && ! self.isEmptyTempContent() ),
							tempContent;
						if ( isTempContent ) {
							tempContent = self.pageData.content;
							self.restoreTempContent();
						}

						// Get a parent for the floated `id`
						var elmParentId = self.getElmParentId( id );

						// After receive the data, we restore the variable,
						// this is only necessary for the `drag:move`
						if ( isTempContent && tempContent ) {
							self.saveTempContent();
							self.pageData.content = '' + tempContent;
						}

						return parent === elmParentId;
					}

					return result;
				},
				/* key */'canBeChildOf:' + $ush.toArray( args ).join('|'),
				/* default value */false
			);
		},

		/**
		 * Determine has same type parent
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @param {String} type The tag type "us_btn|us_btn:1"
		 * @param {String} parent Shortcode's usbid, e.g. "vc_column:1"
		 * @return {Boolean} True if able to be parent of, False otherwise
		 */
		hasSameTypeParent: function( type, parent ) {
			var self = this;
			if (
				self.isMainContainer( type )
				|| self.isMainContainer( parent )
				|| ! self.isValidId( parent )
			) {
				return false;
			}
			// Get type
			type = self.isValidId( type )
				? self.getElmType( type )
				: type;
			// If the type is from the parent of the same type
			if ( type === self.getElmType( parent ) ) {
				return true;
			}
			// Search all parents
			var iteration = 0;
			while( parent !== null || self.isMainContainer( parent ) ) {
				// After exceede the specified number of iterations, the loop will be stopped
				if ( iteration++ >= /* max number of iterations */1000 ) {
					break;
				}
				parent = self.getElmParentId( parent );
				if ( self.getElmType( parent ) === type ) {
					return true;
				}
			}
			return false;
		},

		/**
		 * Get a valid container ID
		 *
		 * @param {Mixed} container The container
		 * @return {String} Returns a valid container in any case (on error it's mainContainer)
		 */
		getValidContainerId: function( container ) {
			var self = this;
			return ! self.isElmContainer( container )
				? self.mainContainer
				: container;
		},

		/**
		 * Determines whether the specified ID is alias usbid
		 *
		 * @param {String} id Shortcode's usbid, e.g. "vc_tta_section:0|alias"
		 * @return {Boolean} True if the specified id is alias usbid, False otherwise
		 */
		isAliasElmId: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) ) {
				return false;
			}
			return _REGEXP_USBID_ALIAS_.test( id );
		},

		/**
		 * Get alias from ID
		 * Note: For any usbid, several aliases can be created that will still refer to the main usbid.
		 * This allows you to implement functionality for specific elements, for example: transfer
		 * features from sections to tab buttons
		 *
		 * @param {String} id Shortcode's usbid, e.g. "vc_tta_section:0|alias"
		 * @return {String|null} Returns the alias name if any, otherwise null
		 */
		getAliasFromId: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) ) {
				return null;
			}
			return ( id.match( _REGEXP_USBID_ALIAS_ ) || [] )[ /* alias */2 ] || null;
		},

		/**
		 * Add alias to ID
		 *
		 * @param {String} alias The alias e.g. "alias-name"
		 * @param {String} id Shortcode's usbid, e.g. "vc_tta_section:0"
		 * @return {String} Returns the id from the appended alias
		 */
		addAliasToElmId: function( alias, id ) {
			var self = this,
				args = arguments;
			if ( alias && typeof alias === 'string' && self.isValidId( id ) ) {
				id += '|' + alias;
			} else {
				$usb.log( 'Notice: Failed to add alias to id', args );
			}
			return id;
		},

		/**
		 * Remove an alias from ID
		 *
		 * @param {String} id Shortcode's usbid, e.g. "vc_tta_section:0|alias"
		 * @return {String} Returns id without alias
		 */
		removeAliasFromId: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) ) {
				return id;
			}
			return ( id.match( _REGEXP_USBID_ALIAS_ ) || [] ) [ /* usbid */1 ] || id;
		},

		/**
		 * Get the elm type
		 *
		 * @param {String|Node} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {String} The elm type
		 */
		getElmType: function( id ) {
			var self = this;
			if ( $ush.isNode( id ) ) {
				id = self.getElmId( id );
			}
			return self.isValidId( id )
				? id.split(':')[ /* type */0 ] || ''
				: '';
		},

		/**
		 * Get the elm name
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {String} Returns the name of the element (without index)
		 */
		getElmName: function( id ) {
			var self = this;

			// Passing the result through the drag data cache function
			return self._cacheDragProcessData(
				function() {
					var type = self.getElmType( id );
					return ( type.match( /us_(.*)/ ) || [] )[ /* name */1 ] || type;
				},
				/* key */'getElmName:' + id,
				/* default value */''
			);
		},

		/**
		 * Get the elm title
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {String}
		 */
		getElmTitle: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) ) {
				return 'Unknown';
			}
			var name = self.getElmName( id );
			return $usb.config( 'elm_titles.' + name ) || name;
		},

		/**
		 * Check if a shortcode with a given name exists or not
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {Boolean} Returns True if id exists, otherwise returns False
		 */
		doesElmExist: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) || ! self.pageData.content ) {
				return false;
			}

			// Passing the result through the drag data cache function
			return self._cacheDragProcessData(
				function() {
					return ( new RegExp( '\\['+ self.getElmType( id ) +'[^\\]]+usbid=\\"'+ $ush.escapePcre( id ) +'\\"' ) )
						.test( '' + self.pageData.content )
				},
				/* key */'doesElmExist:' + id,
				/* default value */false
			);
		},

		/**
		 * Get the elm id
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @param {Node} node The target element
		 * @return {String} id Shortcode's usbid, e.g. "us_btn:1"
		 */
		getElmId: function( node ) {
			if ( ! $ush.isNode( node ) ) {
				return '';
			}
			if ( ! node.hasOwnProperty( '_$$usbid' ) ) {
				var self = this,
					id = $usbcore.$attr( node, 'data-usbid' );
				node._$$usbid = ( self.isValidId( id ) || self.isMainContainer( id ) )
					? id
					: '';
			}
			return node._$$usbid;
		},

		/**
		 * Get the index of an element by ID
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {Number|null} The index of the element (Returns `null` in case of an error)
		 */
		getElmIndex: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) ) {
				return null;
			}
			var index = ( self.getElmSiblingsId( id ) || [] ).indexOf( id );
			return index > -1
				? index
				: null;
		},

		/**
		 * Generate a spare shortcode usbid for a new element
		 *
		 * @param {String} type The type or usbid from which the type will be derived
		 * @return {String}
		 */
		getSpareElmId: function( type ) {
			var self = this;
			if ( ! type ) {
				return '';
			}
			// If the type has an id, then we get the type
			if ( self.isValidId( type ) ) {
				type = self.getElmType( type );
			}
			if ( ! _$temp.generatedIds ) {
				_$temp.generatedIds = [];
			}
			for ( var index = 1;; index++ ) {
				var id = type + ':' + index;
				if ( ! self.doesElmExist( id ) && _$temp.generatedIds.indexOf( id ) < 0 ) {
					_$temp.generatedIds.push( id );
					return id;
				}
			}
		},

		/**
		 * Get element's direct parent's ID or a 'container' if element is at the root
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {String|Boolean|null} Returns the parent id if successful, otherwise null or False
		 */
		getElmParentId: function( id ) {
			var self = this,
				parentId = self.mainContainer;

			if ( id === parentId || ! self.doesElmExist( id ) ) {
				return null;
			}

			// Passing the result through the drag data cache function
			return self._cacheDragProcessData(
				function() {
					var result = parentId,
						content = ( '' + self.pageData.content ),
						// Get the index of the start of the shortcode
						elmRegex = new RegExp( '\\['+ self.getElmType( id ) +'[^\\]]+usbid=\\"'+ $ush.escapePcre( id ) +'\\"' ),
						startPosition = content.search( elmRegex ),
						// Get content before and after shortcode
						prevContent = content.slice( 0, startPosition ),
						nextContent = content.slice( startPosition )
							// Remove all shortcodes of the set type
							.replace( self.getShortcodePattern( self.getElmType( id ) ), '' ),
						closingTags = nextContent.match( /\[\/(\w+)/g ) || [],
						parentTagMatch, parentTag, parentTagAtts;

					$.each( closingTags, function( index, closingTag ) {
						closingTag = closingTag.substr( 2 );
						// Trying to find last open tag in prevContent
						// TODO: make sure that tags without atts work
						parentTagMatch = prevContent.match( new RegExp( '\\[' + closingTag + '\\s([^\\]]+)(?!.*\\[\\/' + closingTag + '(\\s|\\]))', 's' ) );

						if ( parentTagMatch !== null ) {
							// If matches tag found, check if its content has current element
							parentTagAtts = self.parseAtts( parentTagMatch[ 1 ] );
							parentTag = self.getElmShortcode( parentTagAtts['usbid'] );
							if ( parentTag.search( elmRegex ) > -1 ) {
								result = parentTagAtts['usbid'];
								return false;
							}
						}
					} );

					return result;
				},
				/* key */'getElmParentId:' + id,
				/* default value */parentId
			);
		},

		/**
		 * Get the element next id
		 * Note: The code is not used
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {String|null} The element next id or null
		 */
		getElmNextId: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) || self.isMainContainer( id ) ) {
				return null;
			}
			var children = self.getElmChildren( self.getElmParentId( id ) ),
				currentIndex = children.indexOf( id );
			if ( currentIndex < 0 || children.length === currentIndex ) {
				return null;
			}
			return children[ ++currentIndex ] || null;
		},

		/**
		 * Get the element previous id
		 * Note: The code is not used
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {String|null} The element previous id or null
		 */
		getElmPrevId: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) || self.isMainContainer( id ) ) {
				return null;
			}
			var children = self.getElmChildren( self.getElmParentId( id ) ),
				currentIndex = children.indexOf( id );
			if ( currentIndex < 0 || currentIndex === 0 ) {
				return null;
			}
			return children[ --currentIndex ] || null;
		},

		/**
		 * Get the element siblings id
		 *
		 * @param {String} id The id e.g. "us_btn:1"
		 * @return {[]} The element siblings id
		 */
		getElmSiblingsId: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) || self.isMainContainer( id ) ) {
				return [];
			}
			return self.getElmChildren( self.getElmParentId( id ) );
		},

		/**
		 * Get element's direct children IDs (or empty array, if element doesn't have children)
		 * Note: The method is called many times, so performance is important here!
		 *
		 * @param {String} id Shortcode's usbid, e.g. "vc_row:1"
		 * @return {[]} Returns an array of child IDs
		 */
		getElmChildren: function( id ) {
			var self = this,
				isMainContainer = self.isMainContainer( id );

			if ( ! id || ! ( self.isValidId( id ) || isMainContainer ) ) {
				return [];
			}

			// Passing the result through the drag data cache function
			return self._cacheDragProcessData(
				function() {
					var content = ! isMainContainer
						? ( self.parseShortcode( self.getElmShortcode( id ) ) || {} ).content || ''
						: '' + self.pageData.content;
					if ( ! content ) {
						return [];
					}
					var i = 0,
						result = [],
						firstShortcode;
					// Get the shortcode siblings ids
					while ( firstShortcode = self.parseShortcode( content ) ) {
						if ( i++ > /* max number of iterations */9999 || $.isEmptyObject( firstShortcode ) ) {
							break;
						}
						var usbid = self.parseAtts( firstShortcode.atts )['usbid'] || null;
						if ( usbid ) {
							result.push( usbid );
						}
						content = content.replace( firstShortcode.input, '' );
					}
					return result;
				},
				/* key */'getElmChildren:' + id,
				/* default value */[]
			);
		},

		/**
		 * Get all element's direct children IDs (or empty array, if element doesn't have children)
		 *
		 * @param {String} id Shortcode's usbid, e.g. "vc_row:1"
		 * @return {[]}
		 */
		getElmAllChildren: function( id ) {
			var self = this;
			if ( ! self.isValidId( id ) || ! self.isElmContainer( id ) ) {
				return [];
			}
			var results = [],
				args = arguments,
				childrenIDs = self.getElmChildren( id ),
				recursionLevel = $ush.parseInt( args[ /* current recursion level */1 ] );
			for ( var i in childrenIDs ) {
				var childrenId = childrenIDs[i];
				if ( ! self.isValidId( childrenId ) ) {
					continue;
				}
				results.push( childrenId );
				if ( self.isElmContainer( childrenId ) ) {
					if ( recursionLevel >= /* max number of levels when recursin */20 ) {
						$usb.log( 'Notice: Exceeded number of levels in recursion:', args );
					} else {
						results = results.concat( self.getElmAllChildren( childrenId, recursionLevel++ ) );
					}
				}
			}
			return results;
		},

		/**
		 * Get element's shortcode (with all the children if they exist)
		 *
		 * @param {String} id Shortcode's usbid (e.g. "us_btn:1")
		 * @return {String}
		 */
		getElmShortcode: function( id ) {
			var self = this,
				content = ( '' + self.pageData.content );
			if ( $ush.isUndefined( id ) ) {
				return content;
			}
			if ( ! self.isValidId( id ) ) {
				return '';
			}

			// The getting shortcodes
			var matches = content.match( self.getShortcodePattern( self.getElmType( id ) ) );

			if ( matches ) {
				for ( var i in matches ) {
					if ( matches[ i ].indexOf( 'usbid="' + id + '"' ) !== -1 ) {
						return matches[ i ];
					}
				}
			}
			return '';
		},

		/**
		 * Get an node or nodes by ID
		 *
		 * @param {String|[]} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {null|Node|[Node..]}
		 */
		getElmNode: function( id ) {
			var self = this;
			if ( ! $usb.iframeIsReady ) {
				return null;
			}
			return ( $usb.iframe.contentWindow.$usbp || {} ).getElmNode( id );
		},

		/**
		 * Get all html for a node include styles
		 *
		 * @param {String|[]} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {String}
		 */
		getElmOuterHtml: function( id ) {
			var self = this;
			if ( ! $usb.iframeIsReady ) {
				return '';
			}
			return ( $usb.iframe.contentWindow.$usbp || {} ).getElmOuterHtml( id ) || '';
		},

		/**
		 * Get shortcode's params values
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @return {{}}
		 */
		getElmValues: function( id ) {
			var self = this;
			if ( ! self.doesElmExist( id ) ) {
				return {};
			}
			// The convert attributes from string to object
			var shortcode = self.parseShortcode( self.getElmShortcode( id ) );
			if ( ! $.isEmptyObject( shortcode ) ) {
				var result = self.parseAtts( shortcode.atts ),
					elmName = self.getElmName( id );
				// Add content value to the result
				var editContent = $usb.config( 'shortcode.edit_content', {} );
				if ( !! editContent[ elmName ] ) {
					result[ editContent[ elmName ] ] = '' + shortcode.content;
				}
				return result;
			}
			return {};
		},

		/**
		 * Get shortcode param value by key name
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @param {String} key This is the name of the parameter
		 * @param {Mixed} defaultValue The default value
		 * @return {Mixed}
		 */
		getElmValue: function( id, key, defaultValue ) {
			return this.getElmValues( id )[ key ] || defaultValue;
		},

		/**
		 * Set shortcode's params values
		 *
		 * @param {String} id Shortcode's usbid, e.g. "us_btn:1"
		 * @param {{}} values
		 */
		setElmValues: function( id, values ) {
			var self = this;
			if ( ! self.doesElmExist( id ) || $.isEmptyObject( values ) ) {
				return;
			}

			// Get the shortcode object
			var shortcodeText = self.getElmShortcode( id ),
				shortcode = self.parseShortcode( shortcodeText );
			if ( $.isEmptyObject( shortcode ) ) {
				return;
			}

			// Set new attributes for the shortcode
			shortcode.atts = ' ' + self.buildAtts( $.extend( self.getElmValues( id ), values ) );

			// Apply content changes
			var newContent = ( self.pageData.content || '' )
				.replace(
					// The original shortcode text
					shortcodeText,
					// The converts a shortcode object to a shortcode string
					self.buildShortcode( shortcode )
				);
			self.pageData.content = newContent;

			// Trigger the content change event
			$usb.trigger( 'builder.contentChange' );
		},

		/**
		 * Cached data as part of the drag & drop process
		 * Note: The method caches data only during the move, after which everything is deleted
		 *
		 * @param {Function} callback The callback function to get the result
		 * @param {String} key The unique key to save data
		 * @param {Mixed} defaultValue The default value if no result
		 * @return {Mixed} Returns the result from the cache or the result of a callback function
		 */
		_cacheDragProcessData: function( callback, key, defaultValue ) {
			var self = this;
			if ( ! $.isFunction( callback ) ) {
				return defaultValue;
			}
			if ( self.isMode( 'drag:add', 'drag:move' ) ) {
				return $usbcore
					.cache( 'dragProcessData' )
					.get( key, callback );
			}
			return callback.call( self );
		},

		/**
		 * Rendered shortcode
		 *
		 * @param {String} requestId The request id
		 * @param {{}} settings A set of key/value pairs that configure the Ajax request
		 */
		_renderShortcode: function( requestId, settings ) {
			var self = this;
			if ( ! requestId || $.isEmptyObject( settings ) ) {
				return;
			}
			if ( ! $.isPlainObject( settings.data ) ) {
				settings.data = {};
			}
			// Add required settings
			$.extend( settings.data, {
				_nonce: $usb.config( '_nonce' ),
				action: $usb.config( 'action_render_shortcode' )
			} );
			// Content preparation
			if ( $ush.isUndefined( settings.data.content ) ) {
				settings.data.content = '';
			} else {
				settings.data.content += ''; // to string
			}
			// Send a request to the server
			$usb.ajax( requestId, settings );
		},

		/**
		 * Controls the number of columns in a row
		 *
		 * @param {String} id Shortcode's usbid, e.g. "vc_row:1"
		 * @param {String} layout The layout
		 */
		_updateColumnsLayout: function( rowId, layout ) {
			// Exclusion of custom settings, since we do not change the rows, but only apply `--custom-columns`
			if ( 'custom' === layout ) {
				return;
			}
			var self = this,
				columns = self.getElmChildren( rowId ),
				columnsCount = columns.length,
				renderNeeded = false,
				columnType = self.isRow( rowId ) ? 'vc_column' : 'vc_column_inner',
				newColumnsWidths = [],
				newColumnsWidthsBase = 0,
				newColumnsWidthsTmp,
				newColumnsCount;

			// Make sure layout has the string type, so our checks will be performed right way
			layout = '' + layout;

			// Parse layout value into columns array
			// Complex layout with all column widths specified
			if ( layout.indexOf( '-' ) > - 1 ) {
				newColumnsWidthsTmp = layout.split( '-' );
				newColumnsCount = newColumnsWidthsTmp.length;
				// Calculate columns width base
				for ( var i = 0; i < newColumnsCount; i ++ ) {
					newColumnsWidthsBase += $ush.parseInt( newColumnsWidthsTmp[ i ] );
				}
				// Calculate and assign columns widths
				for ( var i = 0; i < newColumnsCount; i ++ ) {
					var columnWidthBaseTmp = newColumnsWidthsBase / newColumnsWidthsTmp[ i ];
					// Try to transform width to a simple value (for example 2/4 will be transformed to 1/2)
					if ( columnWidthBaseTmp % 1 === 0 ) {
						newColumnsWidths.push( '1/' + columnWidthBaseTmp );
					} else {
						newColumnsWidths.push( newColumnsWidthsTmp[ i ] + '/' + newColumnsWidthsBase );
					}
				}
				// Layout with column that use grid-template-columns
			} else if ( layout.indexOf( '(' ) === - 1 && layout.indexOf( 'fr' ) > - 1 ) {
				var customColumns = $.trim( layout ).split( ' ' );
				newColumnsCount = 0;

				for ( var i in customColumns ) {
					var columnName = customColumns[ i ];

					// If column doesn't have "fr", then do not add it
					if ( columnName.indexOf( 'fr' ) > - 1 ) {
						newColumnsWidths.push( '1/1' );
						newColumnsCount++;
					}
				}
				// Simple layout with column number only
			} else {
				newColumnsCount = $ush.parseInt( layout );
				for ( var i = 0; i < newColumnsCount; i ++ ) {
					newColumnsWidths.push( '1/' + layout );
				}
			}

			// Add new columns if needed
			if ( columnsCount < newColumnsCount ) {
				for ( var i = columnsCount; i < newColumnsCount; i ++ ) {
					var newColumnId = self.getSpareElmId( columnType );
					self._addShortcodeToContent( rowId, i, '[' + columnType + ' usbid="' + newColumnId + '"][/' + columnType + ']' );
				}
				columnsCount = newColumnsCount;
				// Wee need to render newly added columns
				renderNeeded = true;
				// Trying to remove extra columns if needed (only empty columns may be removed)
			} else if ( columnsCount > newColumnsCount ) {
				var columnsCountDifference = columnsCount - newColumnsCount;
				for ( var i = columnsCount - 1; ( i >= 0 ) && ( columnsCountDifference > 0 ); i -- ) {
					var columnChildren = self.getElmChildren( columns[ i ] );
					if ( columnChildren.length === 0 ) {
						self.removeElm( columns[ i ] );
						columnsCountDifference--;
					}
				}
				columnsCount = newColumnsCount + columnsCountDifference;
			}

			// Refresh columns list
			columns = self.getElmChildren( rowId );

			// Event for react in extensions
			$usb.trigger( 'builder.contentChange' );

			// Set new widths for columns
			for ( var i = 0; i < columnsCount; i ++ ) {
				self.setElmValues( columns[ i ], { width: newColumnsWidths[ i % newColumnsWidths.length ] } );
			}

			if ( ! renderNeeded ) {
				$usb.postMessage( 'vcColumnChanged', /* row|row_inner id */rowId );
			} else {
				// Add temporary loader
				$usb.postMessage( 'showPreloader', rowId );

				// Render updated shortcode
				self._renderShortcode( /* request id */'_renderShortcode', {
					data: {
						content: self.getElmShortcode( rowId )
					},
					success: function( res ) {
						if ( res.success ) {
							$usb.postMessage( 'updateSelectedElm', [ rowId, '' + res.data.html ] );
						}
					}
				} );
			}
		},

		/**
		 * Get the insert position
		 *
		 * @param {String} parent Shortcode's usbid, e.g. "us_btn:1" or "container"
		 * @param {Number} index Position of the element inside the parent
		 * @return {{}} Object with new data
		 */
		getInsertPosition: function( parent, index ) {
			var position,
				self = this,
				isRootElmContainer = self.isElmContainer( parent );
			// Index check and position determination
			index = $ush.parseInt( index );
			// Position definitions within any containers
			if ( self.isMainContainer( parent ) || isRootElmContainer ) {
				var children = self.getElmChildren( parent );
				if ( index === 0 || children.length === 0 ) {
					position = 'prepend'
				} else if ( index > children.length || children.length === 1 ) {
					index = children.length;
					position = 'append';
				} else {
					parent = children[ index - 1 ] || parent;
					position = 'after';
				}
			} else {
				position = ( index < 1 ? 'before' : 'after' );
			}
			return {
				position: position,
				parent: parent
			}
		},

		/**
		 * Add shortcode to a given position
		 *
		 * @param {String} parent Shortcode's usbid, e.g. "us_btn:1"
		 * @param {Number} index Position of the element inside the parent
		 * @param {String} newShortcode The new shortcode
		 * @return {Boolean} True if successful, False otherwise
		 */
		_addShortcodeToContent: function( parent, index, newShortcode ) {
			var self = this;
			// Check the correctness of the data in the variables
			if (
				! newShortcode
				|| ! ( self.isValidId( parent ) || self.isMainContainer( parent ) )
			) {
				return false;
			}

			// Get the insert position
			var insertPosition = self.getInsertPosition( parent, index );
				parent = insertPosition.parent;
			// Get old data
			var insertShortcode = '',
				isMainContainer = self.isMainContainer( parent ),
				oldShortcode = ! isMainContainer
					? self.getElmShortcode( parent )
					: self.pageData.content || '',
				elmType = ! isMainContainer
					? self.getElmType( parent )
					: '';

			// Remove html from start and end
			oldShortcode = self.removeHtmlWrap( oldShortcode );

			// Check the position for the root element, if the position is before or after then add the element to the `prepend`
			var position = insertPosition.position;
			if ( isMainContainer ) {
				position = $usbcore.indexOf( position, [ 'before', 'after'] ) > -1
					? 'container:prepend'
					: 'container:' + position;
			}

			// Create new shortcode
			switch ( position ) {
				case 'before':
				case 'container:prepend':
					insertShortcode = newShortcode + oldShortcode;
					break;
				case 'prepend':
					insertShortcode = oldShortcode
						.replace( new RegExp( '^(\\['+ elmType +'.*?[\\^\\]]+)' ), "$1" + newShortcode.replace( '$1', '&dollar;1' ) );
					break;
				case 'append':
					if ( self.parseShortcode( oldShortcode ).hasClosingTag ) {
						insertShortcode = oldShortcode
							.replace( new RegExp( '(\\[\\/'+ elmType +'\])$' ), newShortcode.replace( '$1', '&dollar;1' ) + "$1" )

					} else {
						insertShortcode = oldShortcode + newShortcode;
					}
					break;
				case 'after':
				case 'container:append':
				default:
					insertShortcode = oldShortcode + newShortcode;
			}

			// Update content variable
			self.pageData.content = ( '' + self.pageData.content ).replace( oldShortcode, insertShortcode );
			return true;
		},

		/**
		 * Add row wrapper for passed content
		 *
		 * @param {String} content The content
		 * @return {String}
		 */
		_addRowWrapper: function( content ) {
			var self = this;
			// Convert pattern to string from result
			return $usb.buildString(
				$usb.config( 'template.vc_row', '' ),
				// The values for variables `{%var_name%}`
				{
					vc_row: self.getSpareElmId( 'vc_row' ),
					vc_column: self.getSpareElmId( 'vc_column' ),
					content: ''+content
				}
			);
		},

		/**
		 * Get the default content
		 * Note: Get content by default has been moved to a separate method to unload and simplify methods
		 *
		 * @param {String} elmType The elm type
		 * @return {String} The default content
		 */
		_getDefaultContent: function( elmType ) {
			var self = this,
				// Child type, if any for the current `elmType`
				child,
				// Get settings for shortcodes
				shortcodeSettings = $usb.config( 'shortcode', {} ),
				/**
				 * Get the default content
				 *
				 * @param {String} type The type
				 * @return {String} The default content
				 */
				_getDefaultContent = function( type ) {
					var defaultValues = ( shortcodeSettings.default_values || {} )[ type ] || false,
						editContent = ( shortcodeSettings.edit_content || {} )[ type ] || false;
					if ( editContent && defaultValues && defaultValues[ editContent ] ) {
						return defaultValues[ editContent ];
					}
					return '';
				};
			// Determine the descendant if any
			var asChild = $.extend( {}, shortcodeSettings.relations.as_child || {} );
			for ( var k in asChild ) {
				if ( ( ( asChild[ k ][ 'only' ] || '' ).split( ',' ) ).indexOf( elmType ) > -1 ) {
					child = k;
					break;
				}
			}
			if ( ! child ) {
				return _getDefaultContent( elmType );
			}

			// Add elements for tab structures
			if ( self.isElmSection( child ) ) {

				// Get a title template for a section
				var titleTemplate = $usb.getTextTranslation( 'section' ),

				// Get parameters for a template
				params = {
					title_1: ( titleTemplate + ' 1' ),
					title_2: ( titleTemplate + ' 2' ),
					vc_column_text: self.getSpareElmId( 'vc_column_text' ),
					vc_column_text_content: _getDefaultContent( 'vc_column_text' ),
					vc_tta_section_1: self.getSpareElmId( /* vc_tta_section */child ),
					vc_tta_section_2: self.getSpareElmId( /* vc_tta_section */child )
				};
				// Build shortcode
				return $usb.buildString( $usb.config( 'template.' + /* vc_tta_section */child, '' ), params );

				// Add an empty element with no content
			} else {
				return '['+ child +' usbid="'+ self.getSpareElmId( child ) +'"][/'+ child +']';
			}
		},

		/**
		 * Create and add a new element
		 *
		 * @param {String} type The element type
		 * @param {String} parent The parent id
		 * @param {Number} index Position of the element inside the parent
		 * @param {{}} values The element values
		 * @param {Function} callback The callback
		 * @return {Mixed}
		 */
		createElm: function( type, parent, index, values, callback ) {
			var self = this,
				args = arguments,
				isMainContainer = self.isMainContainer( parent );

			if (
				! type
				|| ! parent
				|| ! ( self.isValidId( parent ) || isMainContainer )
			) {
				$usb.log( 'Error: Invalid params', args );
				return;
			}

			// Check parents and prohibit invest in yourself
			if ( self.hasSameTypeParent( type, parent ) ) {
				$usb.log( 'Error: It is forbidden to add inside the same type', args );
				return;
			}

			// The hide all highlights
			$usb.postMessage( 'doAction', 'hideHighlight' );

			// Index check and position determination
			index = $ush.parseInt( index );

			// If there is no parent element, add the element to the `container`
			if ( ! isMainContainer && ! self.doesElmExist( parent ) ) {
				parent = self.mainContainer;
				index = 0;
			}

			var elmId = self.getSpareElmId( type ),
				// Get name from ID
				elmName = self.getElmName( elmId ),
				// Get insert position
				insert = self.getInsertPosition( parent, index );

			// Validate Values
			if ( ! values || $.isEmptyObject( values ) ) {
				values = {};
				// Fix for group default values
				var defaultValues = $usb.config( 'shortcode.default_values.' + elmName, false );
				if ( defaultValues ) {
					for ( var _attr in defaultValues ) {
						if ( defaultValues.hasOwnProperty( _attr ) && _attr !== 'content' ) {
							values[ _attr ] = defaultValues[ _attr ];
						}
					}
				}
			}

			var // Create shortcode string
				buildShortcode = self.buildShortcode({
					tag: type,
					atts: self.buildAtts( $.extend( { usbid: elmId }, values ) ),
					content: self._getDefaultContent( elmName ),
					hasClosingTag: ( self.isElmContainer( elmName ) || !! $usb.config( 'shortcode.edit_content.' + elmName ) )
				} );

			// The check if the element is not the root container and is added to the main container,
			// then add a wrapper `vc_row`. It is forbidden to add elements without a line to the root container!
			if (
				self.isMainContainer( parent )
				&& ! self.isRow( elmId )
				&& ! $usb.templates.isTemplate( type )
			) {
				buildShortcode = self._addRowWrapper( buildShortcode );
			}

			// Added shortcode to content
			if ( ! self._addShortcodeToContent( parent, index, buildShortcode ) ) {
				return false;
			}

			// Get html shortcode code and set on preview page
			$usb.postMessage( 'showPreloader', [
				insert.parent,
				insert.position,
				// If these values are true, then a container class will be added for customization
				/* isContainer */self.isElmContainer( type )
			] );
			// Get a rendered shortcode
			self._renderShortcode( /* request id */'_renderShortcode', {
				data: {
					content: buildShortcode
				},
				success: function( res ) {
					$usb.postMessage( 'hidePreloader', insert.parent );
					if ( res.success ) {
						// Add new shortcde to preview page
						$usb.postMessage( 'insertElm', [ insert.parent, insert.position, '' + res.data.html ] );
						// Init its JS if needed
						$usb.postMessage( 'maybeInitElmJS', [ elmId ] );
						// Event for react in extensions
						$usb.trigger( 'builder.contentChange' );
						// Commit to save changes to history
						$usb.history.commitChange( elmId, _CHANGED_ACTION_.CREATE );
					}
					if ( $.isFunction( callback ) ) {
						// This callback function from method arguments which will be called
						// after add the new element
						callback.call( self, elmId );
					}
				}
			} );

			return elmId;
		},

		/**
		 * Move the element to a new position
		 *
		 * @param {String} moveId ID of the element that is being moved, e.g. "us_btn:1"
		 * @param {String} newParent ID of the element's new parent element
		 * @param {Number} newIndex Position of the element inside the new parent
		 * @return {Boolean}
		 */
		moveElm: function( moveId, newParent, newIndex ) {
			var self = this,
				args = arguments;
			if ( self.isMainContainer( moveId ) ) {
				$usb.log( 'Error: Cannot move the container', args );
				return false;
			}
			var isMainContainer = self.isMainContainer( newParent );

			// Check parents and prohibit invest in yourself
			if ( self.hasSameTypeParent( moveId, newParent ) ) {
				$usb.log( 'Error: It is forbidden to add inside the same type', args );
				return;
			}

			// Check the correctness of ids
			if (
				! self.isValidId( moveId )
				|| ! ( self.isValidId( newParent ) || isMainContainer )
			) {
				$usb.log( 'Error: Invalid ID specified', args );
				return false;
			}
			if (
				! self.doesElmExist( moveId )
				|| ! ( self.doesElmExist( newParent ) || isMainContainer )
			) {
				$usb.log( 'Error: Element doesn\'t exist', args );
				return false;
			}

			// Index check and position determination
			newIndex = $ush.parseInt( newIndex );

			// The hide all highlights
			$usb.postMessage( 'doAction', 'hideHighlight' );

			// If there is no newParent element, add the element to the `container`
			if ( ! isMainContainer && ! self.doesElmExist( newParent ) ) {
				newParent = self.mainContainer;
				newIndex = 0;
			}

			// Commit to save changes to history
			$usb.history.commitChange( moveId, _CHANGED_ACTION_.MOVE );

			// Get old shortcode and remove in content
			var oldShortcode = self.getElmShortcode( moveId );
			self.pageData.content = ( '' + self.pageData.content )
				.replace( oldShortcode, '' );

			// Get parent position
			var insert = self.getInsertPosition( newParent, newIndex );

			// Added shortcode to content
			if ( ! self._addShortcodeToContent( newParent, newIndex, oldShortcode ) ) {
				return false;
			}

			// Move element on preview page
			$usb.postMessage( 'moveElm', [ insert.parent, insert.position, moveId ] );

			// Event for react in extensions
			$usb.trigger( 'builder.contentChange' );

			return true;
		},

		/**
		 * Remove the element
		 *
		 * @param {String} removeId ID of the element that is being removed, e.g. "us_btn:1"
		 * @return {Boolean}
		 */
		removeElm: function( removeId ) {
			var self = this;
			if ( ! self.isValidId( removeId ) ) {
				return false;
			}
			// Remove element from preview
			$usb.postMessage( 'removeHtmlById', removeId );
			var selectedElmId = self.selectedElmId,
				allChildren = self.getElmAllChildren( removeId ),
				rootContainerId;
			// Get the root container to send the change event
			if ( self.isColumn( removeId ) ) {
				rootContainerId = self.getElmParentId( removeId );
			}

			// Commit to save changes to history
			$usb.history.commitChange( removeId, _CHANGED_ACTION_.REMOVE );

			// Remove shortcode from content
			self.pageData.content = ( '' + self.pageData.content )
				.replace( self.getElmShortcode( removeId ), '' );

			// Event for react in extensions
			$usb.trigger( 'builder.contentChange' );

			if ( rootContainerId ) {
				// Handler is called every time the column/column_inner in change
				$usb.postMessage( 'vcColumnChanged', /* row|row_inner id */rootContainerId );
			}

			if (
				selectedElmId
				&& (
					removeId == selectedElmId // for current element
					|| allChildren.indexOf( selectedElmId ) > -1 // for parent element
				)
			) {
				// Show the section "Add elements"
				$usb.trigger( 'panel.showAddElms' );
			}

			// Remove an elm via navigator if it is there
			$usb.navigator.removeElm( removeId );

			return true;
		}
	} );

	// Export API
	$usb.builder = new Builder( /* main container */'#usb-wrapper' );

} ( jQuery );
