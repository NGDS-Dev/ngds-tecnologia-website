/**
 * Available spaces:
 *
 * _window.$usb - Basic object for mounting and initializing all extensions of the builder
 * _window.$usbcore - Auxiliary functions for the builder and his extensions
 * _window.$ush - US Helper Library
 *
 * Note: Double underscore `__funcname` is introduced for functions that are created through `$ush.debounce(...)`.
 */
! function( $, undefined ) {

	// Private variables that are used only in the context of this function, it is necessary to optimize the code
	var _window = window,
		_document = document;

	if ( ! _window.$usb ) {
		return;
	}

	// Check for is set availability objects
	[ '$ush', '$usbcore' ].map( function( name ) {
		_window[ name ] = _window[ name ] || {};
	} );

	/**
	 * @var {RegExp} Regular expression for finding builder IDs
	 */
	const _REGEXP_USBID_ATTR_ = /(\s?usbid="([^\"]+)?")/g;

	/**
	 * @var {{}} Private temp data
	 */
	var _$temp = {
		$categorySections: {}, // list section categories
		isLoaded: {}, // this param will be True when templates are loaded by category {id}:{status}. Example: `fn:true`
		transit: null, // template transit node
	};

	/**
	 * @class Templates - Functionality of importing and adding rows from provided templates
	 * @param {String} container The container
	 */
	function Templates( container ) {
		var self = this;

		// Variable
		self.name = 'templates';

		/**
		 * @var {{}} Bondable events
		 */
		self._events = {
			clickTabTemplates: self._clickTabTemplates.bind( self ),
			expandCategory: self._expandCategory.bind( self ),
		}

		// Get nodes after the document is ready
		$( function() {

			// Elements
			self.$container = $( container );
			self.$tabButton = $( '.usb_action_show_templates', $usb.$panel );
			self.$error = $( '.usb-templates-error', self.$container );

			// Get transit node
			_$temp.transit = _document.querySelector( '.usb-template-transit' );

			// Events
			$usb.$panel
				// Handler for switch categories
				.on( 'click', '.usb-template-title', self._events.expandCategory )
				// Handler for show and loading templates
				.on( 'click', '.usb_action_show_templates', self._events.clickTabTemplates );
		} );
	}

	// Templates API
	$.extend( Templates.prototype, $ush.mixinEvents, {
		/**
		 * Determines if ready
		 *
		 * @return {Boolean} True if ready, False otherwise
		 */
		isReady: function() {
			return ! $ush.isUndefined( this.$container );
		},

		/**
		 * Determines if show
		 *
		 * @return {Boolean} True if show, False otherwise
		 */
		isShow: function() {
			return this.$container.is( ':visible' );
		},

		/**
		 * Determines whether the specified identifier is template
		 *
		 * @param @param {String} id Shortcode's usbid, e.g. "import_template:1"
		 * @return {Boolean} True if the specified id is template, False otherwise
		 */
		isTemplate: function( id ) {
			var self = this;
			if ( $usb.builder.isValidId( id ) ) {
				id = $usb.builder.getElmType( id );
			}
			return id === 'import_template';
		},

		/**
		 * Check the config load
		 *
		 * @return {Boolean} Returns true if config is loaded, otherwise false
		 */
		configIsLoaded: function() {
			return ! $.isEmptyObject( _$temp.$categorySections );
		},

		/**
		 * Check the category is load
		 *
		 * @param {String} categoryId The category id
		 * @return {Boolean} Returns true if the category is loaded, otherwise false
		 */
		categoryIsLoaded: function( categoryId ) {
			return _$temp.isLoaded.hasOwnProperty( categoryId );
		},

		/**
		 * Check the load of templates in the category
		 *
		 * @param {String} categoryId The category id
		 * @return {Boolean} Returns true if the category templates are loaded, otherwise false
		 */
		templateIsLoaded: function( categoryId ) {
			var self = this;
			if (
				self.categoryIsLoaded( categoryId )
				&& _$temp.isLoaded[ categoryId ]
			) {
				return true;
			}
			return false;
		},

		/**
		 * Show templates transit
		 *
		 * @return {Node} Returns the transit node
		 */
		showTransit: function() {
			$usbcore.$removeClass( _$temp.transit, 'hidden' );
			return _$temp.transit;
		},

		/**
		 * Hide templates transit
		 */
		hideTransit: function() {
			$usbcore.$addClass( _$temp.transit, 'hidden' );
		},

		/**
		 * Handler for click on tab button
		 *
		 * @event handler
		 */
		_clickTabTemplates: function() {
			var self = this;
			// Load the config if it is not loaded
			if ( ! self.configIsLoaded() ) {
				self._loadConfig();
			}
			// Init Drag & Drop
			if ( $usb.licenseIsActivated() ) {
				$usb.builder._initDragDrop();
			}
			// Set the "Add Elements" button to active in the header
			if ( $usb.find( 'builderPanel' ) ) {
				$usb.builderPanel.$actionAddElms.addClass( 'active' );
			}
			// Collapse all template categories
			$( '.usb-template', self.$container ).removeClass( 'expand' );
		},

		/**
		 * Handler for expand category
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_expandCategory: function( e ) {
			var self = this,
				categoryId = $( e.currentTarget ).parent().data( 'template-category-id' );
			// If it was not possible to load the category id, then exit the method
			if ( ! categoryId ) {
				return;
			}
			// Check and preload category templates
			if ( $usb.licenseIsActivated() ) {
				self._loadTemplates( categoryId );
			}
			// If click on an expand category, then collapse the category
			var $activeTemplate = $( '.usb-template.expand', self.$container );
			if ( $activeTemplate.data( 'template-category-id' ) === categoryId ) {
				$activeTemplate.removeClass( 'expand' );
				return;
			}
			/**
			 * Show category section by categoryId
			 *
			 * @param {String} categoryId The category id
			 */
			function showSectionById( categoryId ) {
				$( '.usb-template', self.$container )
					.removeClass( 'expand' )
					.filter( '[data-template-category-id="'+ categoryId +'"]' )
					.addClass( 'expand' );
			};
			// After load show category templates
			if ( ! self.configIsLoaded() ) {
				self.one( 'configLoaded', showSectionById.bind( self, categoryId ) );
			} else {
				showSectionById( categoryId );
			}
		},

		/**
		 * Load templates config
		 */
		_loadConfig: function() {
			var self = this;

			if ( self.configIsLoaded() ) {
				return;
			}

			// Show the preloader
			$usb.panel.showPreloader();

			// Load template sections
			$usb.ajax( /* request id */'templates.loadConfig', {
				data: {
					_nonce: $usb.config( '_nonce' ),
					action: $usb.config( 'action_get_templates_config' ),
				},
				success: function( res ) {
					if ( ! res.success || ! $.isPlainObject( res.data ) ) {
						// Show template loading error
						self.$error.addClass( 'active' );
						return;
					}

					for ( categoryId in res.data ) {
						// If the category section is loaded then skip the iteration
						if ( _$temp.$categorySections[ categoryId ] ) {
							continue;
						}

						// Get category section
						var categorySection = res.data[ categoryId ];
						if ( categorySection ) {
							self.$container.append( categorySection );
							_$temp.$categorySections[ categoryId ] = $( categorySection );
						}
					}

					// Triggering an event to complete the configuration loaded
					self.trigger( 'configLoaded' );
				},
				complete: function() {
					$usb.panel.hidePreloader(); // hide the preloader
				},
			} );
		},

		/**
		 * Check and preload category templates
		 *
		 * @param {String} categoryId The category id
		 */
		_loadTemplates: function( categoryId ) {
			var self = this;

			if (
				$ush.isUndefined( categoryId )
				|| categoryId == ''
				|| self.templateIsLoaded( categoryId )
			) {
				return;
			}

			$usb.ajax( /* request id */'templates.loadTemplates', {
				// Request data
				data:{
					_nonce: $usb.config( '_nonce' ),
					action: $usb.config( 'action_preload_template_category' ),
					template_category_id: categoryId,
				},
				success: function( res ) {
					// Saved the result in any case, to understand whether there was a download or not
					_$temp.isLoaded[ categoryId ] = res.success;

					if ( ! res.success ) {
						return;
					}

					// Set parameter for render shortcode start
					self.trigger( 'templatesLoaded', [ categoryId ] );
				},
			} );
		},

		/**
		 * Insert template in content and preview
		 *
		 * @param {String} categoryId The template category id
		 * @param {String} templateId The unique template id in the category
		 * @param {String} parentId ID of the element's parent element
		 * @param {Number} currentIndex Position of the element inside the parent
		 */
		insertTemplate: function( categoryId, templateId, parentId, currentIndex ) {
			var self = this;

			// Check if the templates category id is correct
			if ( ! categoryId ) {
				$usb.log( 'Error: Template category ID is not set', args );
				return;
			}

			// Check if the template id is correct
			if ( ! templateId ) {
				$usb.log( 'Error: Template ID is not set', args );
				return;
			}

			// Check if the parent container is correct
			if ( ! $usb.builder.isMainContainer( parentId ) ) {
				$usb.log( 'Error: Invalid parent container, templates can only be added to mainContainer', args );
				return;
			}

			// Get the insert position
			var insert = $usb.builder.getInsertPosition( parentId, currentIndex );

			/**
			 * @var {Function} Get template data
			 */
			var _getTemplateData = function() {
				// Get html shortcode code and set on preview page
				$usb.postMessage( 'showPreloader', [
					insert.parent,
					insert.position,
				] );

				$usb.builder._renderShortcode( /* request id */'templates.getTemplateData', {
					data: {
						template_category_id: categoryId,
						template_id: templateId,
						isReturnContent: true // returns the content for the page (shortcodes)
					},
					success: function( res ) {
						$usb.postMessage( 'hidePreloader', insert.parent );

						// Check the correctness of the answer and the availability of data
						if ( ! res.success || ! res.data.content || ! res.data.html ) {
							return;
						}

						var firstElmId, // first shortcode usbid (should be a vc_row)
							html = '' + res.data.html, // full template markup
							content = res.data.content, // page content (shortcodes)
							customPrefix = $usb.config( 'designOptions.customPrefix', /* default */'usb_custom_' );

						// Replace all usbid's in content and html
						content = content.replace( _REGEXP_USBID_ATTR_, function( match, input, elmId ) {
							// Get a new usbid of the same type
							var newElmId = $usb.builder.getSpareElmId( elmId );
							if ( ! firstElmId ) {
								firstElmId = newElmId; // get first shortcode usbid (should be a vc_row)
							}

							html = html
								// Replace all usbid's in attributes (Note: )
								.replace( new RegExp( 'data-(for|usbid)="'+ elmId +'"', 'g' ), 'data-$1="'+ newElmId +'"' )
								// Replace all custom element classes, old mask: `{customPrefix}{type}{index}`
								.replace( new RegExp( customPrefix + elmId.replace( ':', '' ), 'g' ), $ush.uniqid( customPrefix ) );

							// Return a new shortcode usbid
							return input.replace( elmId, newElmId );
						} );

						// Added shortcode to content
						if ( ! $usb.builder._addShortcodeToContent( parentId, currentIndex, content ) ) {
							return false;
						}

						// Add new template to preview page
						$usb.postMessage( 'insertElm', [ insert.parent, insert.position, html ] );

						// Add the first row to the history and open for edit
						if ( $usb.builder.isRow( firstElmId ) ) {
							// Commit to save changes to history
							$usb.history.commitChange( firstElmId, _CHANGED_ACTION_.CREATE );
						}

						$usb.trigger( 'builder.contentChange' ); // event for react in extensions
					}
				} );
			};

			// Determines if current category shortcodes loaded
			if ( ! self.templateIsLoaded( categoryId ) ) {
				self.off( 'templatesLoaded' )
					.one( 'templatesLoaded', function( _categoryId ) {
						if ( categoryId == _categoryId ) {
							_getTemplateData(); // get template data
						}
					} );
				if ( self.categoryIsLoaded( categoryId ) ) {
					$usb.log( 'Error: Failed to load template category:', [ categoryId ] );
				}
				return;
			}
			_getTemplateData(); // get template data
		}
	} );

	// Export API
	$usb.templates = new Templates( /* container */'#usb-templates' );

} ( jQuery );
