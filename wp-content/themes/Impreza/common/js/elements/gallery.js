/**
 * UpSolution Element: Gallery
 */
;( function( $, undefined ) {
	"use strict";

	$us.WGallery = function( container, options ) {
		this.init( container, options );
	};

	$us.WGallery.prototype = {
		init: function( container, options ) {
			this.$container = $( container );
			this.$list = $( '.w-gallery-list', this.$container );
			this.$loadmore = $( '.w-gallery-loadmore', this.$container );
			this.$jsonContainer = $( '.w-gallery-json', this.$container );
			this.currentPage = 1;
			this.ajaxData = {};
			this.allImageIds = [];

			if ( this.$jsonContainer.length && ! $us.usbPreview() ) {
				this.ajaxData = this.$jsonContainer[ 0 ].onclick() || {};
				this.allImageIds = this.ajaxData.template_vars.ids || [];
			}

			if ( this.$container.hasClass( 'type_masonry' ) ) {
				this.initMasonry();
			}

			if ( this.$container.hasClass( 'action_popup_image' ) ) {
				this.initMagnificPopup();
			}

			if ( ! this.allImageIds.length ) {
				return;
			}

			$( 'button', this.$loadmore ).on( 'click', this.ajax.bind( this ) );

			if ( this.ajaxData.template_vars.pagination == 'load_on_scroll' ) {
				$us.waypoints.add( this.$loadmore, /* offset */'-70%', function() {
					this.ajax.call( this );
				}.bind( this ) );
			}
		},

		initMagnificPopup: function() {
			$us.getScript( $us.templateDirectoryUri + '/common/js/vendor/magnific-popup.js', function() {
				$( 'a.w-gallery-item-link', this.$container ).magnificPopup( {
					type: 'image',
					gallery: {
						enabled: true,
						navigateByImgClick: true,
						preload: [0, 1],
						tPrev: $us.langOptions.magnificPopup.tPrev, // Alt text on left arrow
						tNext: $us.langOptions.magnificPopup.tNext, // Alt text on right arrow
						tCounter: $us.langOptions.magnificPopup.tCounter // Markup for "1 of 7" counter
					},
					removalDelay: 300,
					mainClass: 'mfp-fade',
					fixedContentPos: true
				} );
			}.bind( this ) );
		},

		initMasonry: function() {
			var self = this,
				isotopeOptions = {
					layoutMode: 'masonry',
					isOriginLeft: ! $( 'body' ).hasClass( 'rtl' )
				};

			if ( self.$list.parents( '.w-tabs-section-content-h' ).length ) {
				isotopeOptions.transitionDuration = 0;
			}

			$us.getScript( $us.templateDirectoryUri + '/common/js/vendor/isotope.js', function() {
				self.$list.imagesLoaded( function() {
					self.$list.isotope( isotopeOptions );
					self.$list.isotope();
				} );
				$us.$canvas.on( 'contentChange', function() {
					self.$list.imagesLoaded( function() {
						self.$list.isotope();
					} );
				} );
			} );
		},

		ajax: function() {
			var self = this;

			if ( self.$loadmore.hasClass( 'done' ) ) {
				return;
			}

			self.currentPage += 1;

			// Get next part ids
			self.ajaxData.template_vars.ids = self.allImageIds.slice(
				self.ajaxData.template_vars.quantity * ( self.currentPage - 1 ), /* Start */
				self.ajaxData.template_vars.quantity * self.currentPage /* End */
			);

			// Stop ajax actions, if all ids loaded
			if ( ! self.ajaxData.template_vars.ids.length ) {
				self.$loadmore.addClass( 'done' );

				return;
			}

			self.$loadmore.addClass( 'loading' );

			$.ajax( {
				type: 'post',
				url: self.ajaxData.ajax_url,
				data: {
					action: self.ajaxData.action,
					template_vars: JSON.stringify( self.ajaxData.template_vars ),
				},
				success: function( html ) {
					var $result = $( html ),
						$items = $( '.w-gallery-list > *', $result );

					if ( ! $items.length || self.currentPage === self.ajaxData.template_vars.max_num_pages ) {
						self.$loadmore
							.addClass( 'done' );
					}

					self.$list.append( $items );

					if ( self.$container.hasClass( 'action_popup_image' ) ) {
						self.initMagnificPopup();
					}

					if ( self.$container.hasClass( 'type_masonry' ) ) {
						var isotope = self.$list.data( 'isotope' );
						if ( isotope ) {
							isotope.insert( $items );
							isotope.reloadItems();
						}
					}

					if ( self.ajaxData.template_vars.pagination == 'load_on_scroll' ) {
						$us.waypoints.add( self.$loadmore, /* offset */'-70%', function() {
							self.ajax.call( self );
						}.bind( this ) );
					}

					self.$loadmore.removeClass( 'loading' );
				},
				error: function() {
					self.$loadmore.removeClass( 'loading' );
				}
			} );
		},
	};

	$.fn.wGallery = function( options ) {
		return this.each( function() {
			$( this ).data( 'WGallery', new $us.WGallery( this, options ) );
		} );
	};

	$( '.w-gallery' ).wGallery();

} )( jQuery );
