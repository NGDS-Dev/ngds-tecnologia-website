!( function( $, _undefined ) {

	// Private variables that are used only in the context of this function, it is necessary to optimize the code
	var _document = document;

	/**
	 * UpSolution WooCommerce elements
	 * Note: All classes and key elements from WooCommerce are retained
	 *
	 * The code depends on:
	 * 	- `../plugins/woocommerce/assets/js/frontend/cart.js`
	 * 	- `../plugins/woocommerce/assets/js/frontend/checkout.js`
	 *
	 * @param container
	 * @requires $us.$body
	 * @requires $us.$canvas
	 * @requires $ush.debounce
	 * @requires $ush.timeout
	 * @constructor
	 */
	var USWooCommerce = function() {
		// Elements
		this.$cart = $( '.w-cart' );

		// Variables
		this._activeJqXHR = {}; // This is the object of the last ajax request
		this._cartOpened = false;
		this._removeProcesses = 0; // Number of remove processes simultaneously

		/**
		 * Event handlers
		 * @private
		 */
		this._events = {
			addToCart: this._addToCart.bind( this ),
			applyCouponCode: this._applyCouponCode.bind( this ),
			changeCartQuantity: this._changeCartQuantity.bind( this ),
			changedFragments: this._changedFragments.bind( this ),
			couponCodeChange: this._couponCodeChange.bind( this ),
			couponDisplaySwitch: this._couponDisplaySwitch.bind( this ),
			enterCouponCode: this._enterCouponCode.bind( this ),
			minusCartQuantity: this._minusCartQuantity.bind( this ),
			moveNotifications: this._moveNotifications.bind( this ),
			outsideClickEvent: this._outsideClickEvent.bind( this ),
			plusCartQuantity: this._plusCartQuantity.bind( this ),
			removeCartItem: this._removeCartItem.bind( this ),
			updateCart: this._updateCart.bind( this ),
			updatedCartTotals: this._updatedCartTotals.bind( this ),
			showLoginForm: this._showLoginForm.bind( this ),
			submitLoginForm: this._submitLoginForm.bind( this ),
			loginFieldKeydown: this._loginFieldKeydown.bind( this )
		};

		// Init cart elements
		if ( this.isCart() ) {
			// Cart elements
			this.$cartNotification = $( '.w-cart-notification', this.$cart );

			// Events
			this.$cartNotification.on( 'click', function() {
				this.$cartNotification.fadeOutCSS();
			} );

			// Handler of outside click for mobile devices
			if ( $.isMobile ) {
				this.$cart.on( 'click', '.w-cart-link', function( e ) {
					if ( ! this._cartOpened ) {
						e.preventDefault();
						this.$cart.addClass( 'opened' );
						$us.$body.on( 'touchstart', this._events.outsideClickEvent );
					} else {
						this.$cart.removeClass( 'opened' );
						$us.$body.off( 'touchstart', this._events.outsideClickEvent );
					}
					this._cartOpened = ! this._cartOpened;
				}.bind( this ) );
			}

			$us.$body
				// Events of `../plugins/woocommerce/assets/js/frontend/add-to-cart.js`,
				// `../plugins/woocommerce/assets/js/frontend/cart-fragments.js`
				.on( 'wc_fragments_loaded wc_fragments_refreshed', this._events.changedFragments )
				// Events of `../plugins/woocommerce/assets/js/frontend/add-to-cart.js`
				.on( 'added_to_cart', this._events.addToCart )
				.on( 'removed_from_cart', this._events.updateCart );
		}
		if ( this.isCartPage() ) {
			// Events
			$us.$body
				.on( 'change initControls', 'input.qty', this._events.changeCartQuantity )
				.on( 'change', '.w-wc-coupon-form input', this._events.couponCodeChange )
				.on( 'keyup', '.w-wc-coupon-form input', this._events.enterCouponCode )
				.on( 'click', '.w-wc-coupon-form button', this._events.applyCouponCode )
				.on( 'click', 'a.remove', this._events.removeCartItem )
				.on( 'click', 'input.minus', this._events.minusCartQuantity )
				.on( 'click', 'input.plus', this._events.plusCartQuantity )
				// Events of `../plugins/woocommerce/assets/js/frontend/cart.js`
				.on( 'applied_coupon removed_coupon', this._events.couponDisplaySwitch )
				.on( 'updated_cart_totals', this._events.updatedCartTotals );

			// Initializing controls after the ready document
			$( 'input.qty', $us.$canvas )
				.trigger( 'initControls' );

			// Get the last active request for cart updates
			$.ajaxPrefilter( function( _, originalOptions, jqXHR ) {
				var data = ( '' + originalOptions.data );
				if ( data.indexOf( '&update_cart' ) > -1 ) {
					this._activeJqXHR.updateCart = jqXHR;
				}
				// Distance information updates in shortcode `[us_cart_shipping]`
				if ( data.indexOf( '&us_calc_shipping' ) > -1 ) {
					jqXHR.done( function( res ) {
						$( '.w-cart-shipping .woocommerce-shipping-destination' )
							.html( $( '.w-cart-shipping:first .woocommerce-shipping-destination', res ).html() );
					} );
				}
			}.bind( this ) );

			$( '.w-cart-shipping form.woocommerce-shipping-calculator', $us.$canvas )
				.append( '<input type="hidden" name="us_calc_shipping">' );
		}
		if ( this.isCheckoutPage() ) {
			// Events
			$us.$body
				.on( 'change', '.w-wc-coupon-form input', this._events.couponCodeChange )
				.on( 'click', '.w-wc-coupon-form button', this._events.applyCouponCode )
				// Events of `../plugins/woocommerce/assets/js/frontend/checkout.js`
				.on( 'applied_coupon_in_checkout removed_coupon_in_checkout', this._events.couponDisplaySwitch )
				.on( 'applied_coupon_in_checkout removed_coupon_in_checkout checkout_error', this._events.moveNotifications )
				.on( 'click', '.w-checkout-login .showlogin', this._events.showLoginForm )
				.on( 'click', '.w-checkout-login button', this._events.submitLoginForm )
				.on( 'keydown', '.w-checkout-login input, .w-checkout-login button', this._events.loginFieldKeydown );
		}
	};

	/**
	 * Export API
	 */
	$.extend( USWooCommerce.prototype, {

		/**
		 * Determines if cart
		 *
		 * @return {boolean} True if cartesian, False otherwise.
		 */
		isCart: function() {
			return !! this.$cart.length;
		},

		/**
		 * Determines if current cartesian page
		 *
		 * @return {boolean} True if current cartesian page, False otherwise.
		 */
		isCartPage: function() {
			return $us.$body.hasClass( 'woocommerce-cart' );
		},

		/**
		 * Determines if current checkout page
		 *
		 * @return {boolean} True if current checkout page, False otherwise
		 */
		isCheckoutPage: function() {
			return $us.$body.hasClass( 'woocommerce-checkout' );
		},

		/**
		 * Validation of the value and casting to the required type
		 *
		 * @param {mixed} value The value
		 * @return {number} Returns a valid value or zero
		 * TODO:Remove the current method and replace it with `$ush.parseInt()`.
		 */
		toNumValue: function( value ) {
			value = Math.abs( value );
			return ! isNaN( value )
				? value
				: 0;
		},

		/**
		 * Init cart switch
		 *
		 * @private
		 */
		_switchCart: function() {
			// TODO: Check if the code is up to date
			this.$cart.on( 'focus.upsolution blur.upsolution', function( e ) {
				$( e.target )[ e.type === 'focus.upsolution' ? 'addClass': 'removeClass' ]( 'opened' );
			} );
		},

		/**
		 * Update cart element
		 *
		 * @private
		 */
		_updateCart: function() {
			$.each( this.$cart, function( _, cart ) {
				var $cart = $( cart ),
					$cartQuantity = $( '.w-cart-quantity', $cart ),
					miniCartAmount = $( '.us_mini_cart_amount:first', $cart ).text();

				if ( $cart.hasClass( 'opened' ) ) {
					$cart.removeClass( 'opened' );
				}

				if ( miniCartAmount !== _undefined ) {
					miniCartAmount = ( miniCartAmount + '' ).match( /\d+/g );
					$cartQuantity.html( miniCartAmount > 0 ? miniCartAmount : '0' );
					$cart[ miniCartAmount > 0 ? 'removeClass' : 'addClass' ]( 'empty' );
				} else {
					// fallback in case our action wasn't fired somehow
					var total = 0;
					$( '.quantity', $cart ).each( function( _, quantity ) {
						var matches = ( '' + quantity.innerText ).match( /\d+/g );

						if ( matches ) {
							total += parseInt( matches[ 0 ], 10 );
						}
					} );
					$cartQuantity.html( total > 0 ? total : '0' );
					$cart[ total > 0 ? 'removeClass' : 'addClass' ]( 'empty' );
				}
			}.bind( this ) );
		},

		/**
		 * Handler for tracking changed fragments
		 *
		 * @private
		 * @type event
		 */
		_changedFragments: function() {
			this._updateCart(); // Update cart element
			this._switchCart(); // Init cart switch
		},

		/**
		 * Add a product to the cart
		 *
		 * @private
		 * @type event
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 * @param {{} fragments The fragments
		 * @param {node} $button The button
		 */
		_addToCart: function( e, fragments, _, $button ) {
			if ( e === _undefined ) {
				return;
			}

			// Update cart element
			this._updateCart();

			var $notification = this.$cartNotification,
				$productName = $( '.product-name', $notification ),
				productName = $productName.text();

			productName = $button
				.closest( '.product' )
				.find( '.woocommerce-loop-product__title' )
				.text();

			$productName.html( productName );

			$notification.addClass( 'shown' );
			$notification.on( 'mouseenter', function() {
				$notification.removeClass( 'shown' );
			} );

			$ush.timeout( function() {
				$notification
					.removeClass( 'shown' )
					.off( 'mouseenter' );
			}, 3000 );
		},

		/**
		 * Handler for outside click events for mobile devices
		 * Note: Designed for mobile devices
		 *
		 * @private
		 * @type event
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_outsideClickEvent: function( e ) {
			if ( $.contains( this.$cart[ 0 ], e.target ) ) {
				return;
			}
			this.$cart.removeClass( 'opened' );
			$us.$body.off( 'touchstart', this._events.outsideClickEvent );
			this._cartOpened = false;
		},

		/**
		 * Handler when remove a item
		 *
		 * @private
		 * @type event
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_removeCartItem: function( e ) {
			var $item = $( e.target )
				.closest( '.cart_item' )
				.addClass( 'change_process' );
			// If the element is the last, then delete the table for correct operation `cart.js:update_wc_div`
			if ( ! $item.siblings( '.cart_item:not(.change_process)' ).length ) {
				$( '.w-cart-table', $us.$canvas ).remove();
			}
		},

		/**
		 * Handler on quantity change
		 *
		 * @private
		 * @type event
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_changeCartQuantity: function( e ) {
			// On Edit Live page ignoring quantity changes
			if ( $us.usbPreview() ) {
				return;
			}

			var $input = $( e.target ),
				max = ( this.toNumValue( $input.attr( 'max' ) ) || -1 ),
				min = ( this.toNumValue( $input.attr( 'min' ) ) || 1 ),
				value = this.toNumValue( $input.val() );

			// If the input field is disabled, complete the quantity updates
			if ( $input.is( ':disabled' ) ) {
				return;
			}
			// If the value is less than the min, set the min
			if ( min >= value ) {
				value = min;
			}
			// If the value is greater than the max, set the max
			if ( max > 1 && value >= max ) {
				value = max;
			}
			if ( value != $input.val() ) {
				$input.val( value );
			}

			// Showing controls
			$input
				.siblings( 'input.plus:first' )
				.prop( 'disabled', ( max > 0 && value >= max ) );
			$input
				.siblings( 'input.minus:first' )
				.prop( 'disabled', ( value <= min ) );

			// If the event type is `initControls` then this is the
			// first init when loading the document
			if ( e.type == 'initControls' ) {
				return;
			}

			// Add a flag that there was a change in the quantity to the cart elements
			$( 'input[name=us_cart_quantity]', $us.$canvas )
				.val( true );

			// Update the cart by means of WooCommerce
			if ( ! $( '.w-cart-table', $us.$canvas ).hasClass( 'processing' ) ) {
				this.__updateCartForm_long( this._updateCartForm.bind( this ) );
			} else {
				this._updateCartForm();
			}
		},

		/**
		 * Handler when decreasing quantity
		 *
		 * @private
		 * @type event
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_minusCartQuantity: function( e ) {
			var $target = $( e.target ),
				$input = $target.siblings( 'input.qty:first' );

			if ( ! $input.length ) {
				return;
			}

			var step = this.toNumValue( $input.attr( 'step' ) || 1 );
			$input // Update quantity
				.val( this.toNumValue( $input.val() ) - step )
				.trigger( 'change' );
		},

		/**
		 * Handler on increasing quantity
		 *
		 * @private
		 * @type event
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_plusCartQuantity: function( e ) {
			var $target = $( e.target ),
				$input = $target.siblings( 'input.qty:first' );

			if ( ! $input.length ) {
				return;
			}

			var step = this.toNumValue( $input.attr( 'step' ) || 1 );
			$input // Update quantity
				.val( this.toNumValue( $input.val() ) + step )
				.trigger( 'change' );
		},

		/**
		 * Update the cart form by means of WooCommerce
		 * Note: The code is moved to a separate function since `debounced`
		 * must be initialized before calling
		 *
		 * @private
		 * @param {function} fn The function to be executed
		 * @type debounced
		 */
		__updateCartForm_long: $ush.debounce( $ush.fn, /* wait */50 ),

		/**
		 * Update the cart form by means of WooCommerce
		 *
		 * @private
		 */
		_updateCartForm: function() {
			// Abort previous cart update request
			if ( $.isFunction( ( this._activeJqXHR.updateCart || {} ).abort ) ) {
				this._activeJqXHR.updateCart.abort();
			}
			// Initialize cart update
			$( '.w-cart-table > button[name=update_cart]', $us.$canvas )
				.removeAttr( 'disabled' )
				.trigger( 'click' );
		},

		/**
		 * Handler for updated cart totals
		 *
		 * @private
		 * @type event
		 */
		_updatedCartTotals: function() {
			// Reset last active request
			if ( !! this._activeJqXHR.updateCart ) {
				this._activeJqXHR.updateCart = _undefined;
			}
			// Removing animated class if any element had it
			var wooElementClasses = [
				'w-cart-shipping',
				'w-cart-table',
				'w-cart-totals',
				'w-checkout-billing',
				'w-checkout-order-review',
				'w-checkout-payment',
				'w-wc-coupon-form',
			];
			for ( var i in wooElementClasses ) {
				$( '.' + wooElementClasses[i] + '.us_animate_this', $us.$canvas ).removeClass( 'us_animate_this' );
			}

			// Shipping element sync after totals update
			var $elm = $( '.w-cart-shipping .shipping', $us.$canvas );
			if ( ! $elm.length ) {
				return;
			}
			$elm.html( $( '.w-cart-totals .shipping:first', $us.$canvas ).html() );
		},

		/**
		 * Handler when entering a coupon in a field
		 *
		 * @private
		 * @type event
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_couponCodeChange: function( e ) {
			// Transit value to the cart form to add a coupon by WooCommerce logic
			$( '.w-cart-table, form.checkout_coupon', $us.$canvas )
				.find( 'input[name=coupon_code]' )
				.val( e.target.value );
		},

		/**
		 * Enters a coupon code
		 *
		 * @private
		 * @type event
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_enterCouponCode: function( e ) {
			if ( e.keyCode !== 13 ) {
				return;
			}
			$( e.target )
				.trigger( 'change' )
				.siblings( 'button:first' )
				.trigger( 'click' );
		},

		/**
		 * Handler for the add coupon button
		 *
		 * @private
		 * @type event
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_applyCouponCode: function( e ) {
			// Stop event (Important on the checkout page)
			e.stopPropagation();
			e.preventDefault();
			// Initialize coupon additions using WooCommerce logic
			$( '.w-cart-table, form.checkout_coupon', $us.$canvas )
				.find( 'button[name=apply_coupon]' )
				.trigger( 'click' );
			// Clear input field
			$( e.target ).closest( '.w-wc-coupon-form' ).find( 'input:first' ).val( '' );
		},

		/**
		 * Coupon form display switch
		 *
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_couponDisplaySwitch: function( e ) {
			var $coupon = $( '.w-wc-coupon-form', $us.$canvas );
			if ( ! $coupon.length ) {
				return;
			}
			// Add a class if the coupon is applied
			if ( e.type.indexOf( 'applied_coupon' ) > -1 && ! $( '.wc-block-components-notice-banner', $us.$canvas ).length ) {
				$coupon.addClass( 'coupon_applied' );
			}
			// Remove a class if all coupons were removed
			if ( e.type.indexOf( 'removed_coupon' ) > -1 && $( '.woocommerce-remove-coupon', $us.$canvas ).length <= 1 ) {
				$coupon.removeClass( 'coupon_applied' );
			}
		},

		/**
		 * Move notifications to `[wc_notices...]`
		 *
		 * @private
		 * @type event
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 */
		_moveNotifications: function( e ) {
			var $wcNotices = $( '.w-wc-notices:first', $us.$canvas ),
				args = arguments;

			// Do not proceed with notices adjustment if there are no US Cart / Checkout elements on the page
			if ( ! $wcNotices.length ) {
				var $cartTotals = $( '.w-cart-totals', $us.$canvas ),
					$checkoutPayment = $( '.w-checkout-payment', $us.$canvas );
				if ( ! $cartTotals.length && ! $checkoutPayment.length ) {
					return;
				}
			}

			// Get elms notices
			var $message;
			if ( e.type === 'checkout_error' && !! args[1] ) {
				$message = $( /* err_message */args[1] );
			} else {
				$message = $( '.wc-block-components-notice-banner', $us.$canvas );
			}

			// Show notification in notification element
			if ( $message.length ) {
				$wcNotices.html( $message.clone() );
			}
			$message.remove();

			// Remove NoticeGroup
			if ( e.type === 'checkout_error' ) {
				$( '.woocommerce-NoticeGroup-checkout' ).remove();
			}
		},

		_showLoginForm: function() {
			$( '.woocommerce-form-login' ).toggleClass( 'hidden' );
			return false;
		},

		_submitLoginForm: function() {
			// Prevent double sending
			if ( this.isSubmittingLoginForm ) {
				return false;
			}
			this.isSubmittingLoginForm = true;

			// Get the form substitute view and all its fields
			var $formView = $( '.w-checkout-login' ),
				$usernameField = $formView.find( '#us_checkout_login_username' ),
				$passwordField = $formView.find( '#us_checkout_login_password' ),
				$redirectField = $formView.find( '#us_checkout_login_redirect' ),
				$nonceField = $formView.find( '#us_checkout_login_nonce' );

			// Make sure all fields are present
			if (
				$usernameField.length == 0
				|| $passwordField.length == 0
				|| $redirectField.length == 0
				|| $nonceField.length == 0
			) {
				return false;
			}

			// Append a new form with needed fields to <body> and submit it
			var fields = {
					'login': 'Login',
					'rememberme': 'forever',
					'username': $usernameField.val(),
					'password': $passwordField.val(),
					'redirect': $redirectField.val(),
					'woocommerce-login-nonce': $nonceField.val(),
				},
				$form = $( '<form>', {
					method: 'post'
				} );
			$.each( fields, function( key, val ) {
				$( '<input>' ).attr( {
					type: "hidden",
					name: key,
					value: val
				} ).appendTo( $form );
			} );

			$form.appendTo( 'body' ).submit();

			return false;
		},

		_loginFieldKeydown: function( e ) {
			var keyCode = e.keyCode || e.which;

			// Enter Handler
			if ( keyCode == 13 ) {
				e.stopPropagation();
				e.preventDefault();
				this._submitLoginForm();
			}

		}

	} );

	new USWooCommerce();

} )( jQuery );
