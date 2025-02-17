/**
 * UpSolution Element: Modal Popup
 */
! function( $ ) {
	"use strict";
	$us.WPopup = function( container ) {
		this.$container = $( container );
		this.$content = $( '.w-popup-box-content', this.$container );

		this._events = {
			show: this.show.bind( this ),
			afterShow: this.afterShow.bind( this ),
			hide: this.hide.bind( this ),
			hideOnLinkClick: this.hideOnLinkClick.bind( this ),
			preventHide: function( e ) {
				e.stopPropagation();
			},
			afterHide: this.afterHide.bind( this ),
			keyup: function( e ) {
				if ( e.key == "Escape" ) {
					this.hide();
				}
			}.bind( this ),
			scroll: function() {
				// Trigger an event for check lazyLoadXT
				$us.$document.trigger( 'scroll' );
			},
			touchmove: function( e ) {
				this.savePopupSizes();
				// Prevent underlying content scroll
				if (
					( this.popupSizes.wrapHeight > this.popupSizes.contentHeight )
					|| ! $( e.target ).closest( '.w-popup-box' ).length
				) {
					e.preventDefault();
				}
			}.bind( this ),
		};

		// Event name for triggering CSS transition finish
		this.transitionEndEvent = ( navigator.userAgent.search( /webkit/i ) > 0 ) ? 'webkitTransitionEnd' : 'transitionend';
		this.isFixed = ! jQuery.isMobile;

		this.$trigger = this.$container.find( '.w-popup-trigger' );
		this.triggerType = this.$trigger.usMod( 'type' );
		if ( this.triggerType == 'load' ) {
			// Check trigger display on which `hide_on_*` can be applied
			if ( this.$container.css( 'display' ) != 'none' ) {
				var delay = this.$trigger.data( 'delay' ) || 2;
				$ush.timeout( this.show.bind( this ), delay * 1000 );
			}
		} else if ( this.triggerType == 'selector' ) {
			var selector = this.$trigger.data( 'selector' );
			if ( selector ) {
				$us.$body.on( 'click', selector, this._events.show );
			}
		} else {
			this.$trigger.on( 'click', this._events.show );
		}
		this.$wrap = this.$container.find( '.w-popup-wrap' )
			.usMod( 'pos', this.isFixed ? 'fixed' : 'absolute' )
			.on( 'click', this._events.hide );
		this.$box = this.$container.find( '.w-popup-box' );
		this.$overlay = this.$container.find( '.w-popup-overlay' )
			.usMod( 'pos', this.isFixed ? 'fixed' : 'absolute' )
			.on( 'click', this._events.hide );
		this.$container.find( '.w-popup-closer' ).on( 'click', this._events.hide );
		this.$box.on( 'click', this._events.preventHide );

		// Hide popup, if find link with '#' in content
		this.$wrap.find( 'a' ).on( 'click', this._events.hideOnLinkClick.bind( this ) );

		this.$media = $( 'video,audio', this.$box );
		this.$wVideoItems = $( '.w-video [data-src]', this.$box );

		this.timer = null;

		// Save sizes to prevent scroll on iPhones, iPads
		this.popupSizes = {
			boxHeight: 0,
			wrapHeight: 0,
			contentHeight: 0,
			initialWindowHeight: window.innerHeight,
			openedWindowHeight: 0,
		}
	};
	$us.WPopup.prototype = {
		_hasScrollbar: function() {
			return document.documentElement.scrollHeight > document.documentElement.clientHeight;
		},
		_getScrollbarSize: function() {
			if ( $us.scrollbarSize === undefined ) {
				var scrollDiv = document.createElement( 'div' );
				scrollDiv.style.cssText = 'width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;';
				document.body.appendChild( scrollDiv );
				$us.scrollbarSize = scrollDiv.offsetWidth - scrollDiv.clientWidth;
				document.body.removeChild( scrollDiv );
			}
			return $us.scrollbarSize;
		},
		show: function( e ) {
			if ( e !== undefined ) {
				e.preventDefault();
			}
			this.saveWindowSizes();
			clearTimeout( this.timer );
			this.$overlay.appendTo( $us.$body ).show();
			this.$wrap.appendTo( $us.$body ).css( 'display', 'flex' );
			if ( this.isFixed ) {
				$us.$html.addClass( 'usoverlay_fixed' );
				// Storing the value for the whole popup visibility session
				this.windowHasScrollbar = this._hasScrollbar();
				if ( this.windowHasScrollbar && this._getScrollbarSize() ) {
					$us.$html.css( 'margin-right', this._getScrollbarSize() );
				}
			} else {
				this.$wrap.css( 'top', $us.$window.scrollTop() );
				$us.$body.addClass( 'popup-active' );

				this.savePopupSizes();
				// iOS UI-bar shown
				if (
					( this.popupSizes.initialWindowHeight === this.popupSizes.openedWindowHeight )
					&& ( this.popupSizes.boxHeight >= this.popupSizes.wrapHeight )
				) {
					this.$wrap.addClass( 'popup-ios-height' );
				}

				this.$wrap.on( 'touchmove', this._events.touchmove );
				$us.$document.on( 'touchmove', this._events.touchmove );
			}

			$us.$body.on( 'keyup', this._events.keyup );
			this.$wrap.on( 'scroll', this._events.scroll );
			this.timer = setTimeout( this._events.afterShow, 25 );
		},
		afterShow: function() {
			clearTimeout( this.timer );
			this.$overlay.addClass( 'active' );
			this.$box.addClass( 'active' );
			if ( window.$us !== undefined && $us.$canvas !== undefined ) {
				$us.$canvas.trigger( 'contentChange', { elm: this.$container } );
			}
			// If popup contains our video elements with iframes, restore their src from data attribute
			// this is made to make sure these video elements play only when popup is opened
			if ( this.$wVideoItems.length ) {
				this.$wVideoItems.each( function( _, wVideoItem ) {
					var $wVideoItem = $( wVideoItem ),
						$video = $wVideoItem.parent( 'video' ),
						src = $wVideoItem.data( 'src' );

					if ( ! src ) {
						return;
					}
					$wVideoItem.attr( 'src', src );

					// Init video
					if ( $video.length ) {
						$video[ 0 ].load();
					}
				} );
			}
			$us.$body
				.addClass( 'has_uspopup' );

			$us.$window
				.trigger( 'resize' )
				.trigger( 'us.wpopup.afterShow', this )
		},
		hide: function() {
			clearTimeout( this.timer );
			$us.$body.off( 'keyup', this._events.keyup );
			this.$box.on( this.transitionEndEvent, this._events.afterHide );
			this.$overlay.removeClass( 'active' );
			this.$box.removeClass( 'active' );
			this.$wrap.off( 'scroll', this._events.scroll );
			$us.$document.off( 'touchmove', this._events.touchmove );

			// Closing it anyway
			this.timer = setTimeout( this._events.afterHide, 1000 );
		},
		hideOnLinkClick: function( event ){
			var $item = $( event.currentTarget ),
				place = $item.attr( 'href' );

			// Do not hide if: ...
			if (
				// ... the link is not a scroll link
				(
					place.indexOf( '#' ) === -1
				)
				// ... or current popup contains scroll link target
				|| (
					place !== '#'
					&& place.indexOf( '#' ) === 0
					&& this.$wrap.find( place ).length
				)
			) {
				return;
			}

			this.hide();
		},
		afterHide: function() {
			clearTimeout( this.timer );
			this.$box.off( this.transitionEndEvent, this._events.afterHide );
			this.$overlay.appendTo( this.$container ).hide();
			this.$wrap.appendTo( this.$container ).hide();
			if ( this.isFixed ) {
				$us.$html.removeClass( 'usoverlay_fixed' );
				if ( this.windowHasScrollbar ) {
					$us.$html.css( 'margin-right', '' );
				}
				$us.$window
					.trigger( 'resize', true ) // Pass true not to trigger this event in Page Scroller
					.trigger( 'us.wpopup.afterHide', this );
			} else {
				$us.$body.removeClass( 'popup-active' );
				this.$wrap.removeClass( 'popup-ios-height' );
			}
			// If popup contains media elements, then we will pause after closing the window
			if ( this.$media.length ) {
				this.$media.trigger( 'pause' );
			}
			// If popup contains our video elements with iframes, empty their src attribute
			// this is made to make sure these video elements play only when popup is opened
			if ( this.$wVideoItems.length ) {
				this.$wVideoItems.attr( 'src', '' );
			}

			$us.$body
				.removeClass( 'has_uspopup' );
		},
		savePopupSizes: function() {
			this.popupSizes.boxHeight = this.$box.height();
			this.popupSizes.wrapHeight = this.$wrap.height();
			this.popupSizes.contentHeight = this.$content.outerHeight( true );
		},
		saveWindowSizes: function() {
			this.popupSizes.openedWindowHeight = window.innerHeight;
		}
	};
	$.fn.wPopup = function( options ) {
		return this.each( function() {
			$( this ).data( 'wPopup', new $us.WPopup( this, options ) );
		} );
	};

	$( function() {
		$( '.w-popup' ).wPopup();
	} );
}( jQuery );
