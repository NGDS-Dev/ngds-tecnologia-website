/**
 * Support for WP services in the builder
 * Example: edit lock, autosave, notifications, etc.
 */
! function( $, undefined ) {

	// Private variables that are used only in the context of this function, it is necessary to optimize the code
	var _window = window,
		_document = document,
		_location = location,
		_undefined = undefined;

	// Check for is set availability objects
	[ 'wp', '$usbdata' ].map( function( name ) {
		_window[ name ] = _window[ name ] || {};
	} );

	/**
	 * @class USHeartbeat - Service for polling the server and synchronizing data or events
	 * @see https://developer.wordpress.org/plugins/javascript/heartbeat-api/
	 */
	function USHeartbeat() {
		var self = this;

		/**
		 * @var {{}} Bondable events
		 */
		self._events = {
			send_refreshLock: self._send_refreshLock.bind( self ),
			tick_refreshLock: self._tick_refreshLock.bind( self ),
		};

		// Events
		$( _document )
			.on( 'heartbeat-send.refresh-lock', self._events.send_refreshLock )
			.on( 'heartbeat-tick.refresh-lock', self._events.tick_refreshLock );

		// Set the heartbeat interval to 15 seconds
		wp.heartbeat.interval( 15/* second */ );
	};

	// USHeartbeat API
	$.extend( USHeartbeat.prototype, {

		/**
		 * Used to lock editing of an object by only one user at a time
		 *
		 * When the user does not send a heartbeat in a heartbeat-time
		 * the user is no longer editing and another user can start editing
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 * @param {{}} data
		 */
		_send_refreshLock: function( e, data ) {
			var post_id = _window.$usbdata.post_id || 0;
			if ( post_id ) {
				data['wp-refresh-post-lock'] = {
					post_id: post_id
				};
			}
		},

		/**
		 * Post locks: update the lock string or show the dialog if somebody has taken over editing
		 *
		 * @event handler
		 * @param {Event} e The Event interface represents an event which takes place in the DOM
		 * @param {{}} data
		 */
		_tick_refreshLock: function( e, data ) {
			var $dialog = $( '#post-lock-dialog' );
			if ( ! data['wp-refresh-post-lock'] || ! $dialog.length ) {
				return;
			}
			var received = data['wp-refresh-post-lock'];
			if ( received.lock_error ) {

				// Avatar updates
				if ( received.lock_error.avatar_src && ! $dialog.is( ':visible' ) ) {
					var $avatar = $( '<img />', {
						'class': 'avatar avatar-64 photo',
						width: 64,
						height: 64,
						alt: '',
						src: received.lock_error.avatar_src,
						srcset: received.lock_error.avatar_src_2x ?
							received.lock_error.avatar_src_2x + ' 2x' :
							_undefined
					} );
					$( 'div.post-locked-avatar', $dialog ).empty().append( $avatar );
				}

				var $dialogText = $( '.currently-editing', $dialog );
				if ( ! $dialogText.text() && received.lock_error.text ) {
					$dialogText.text( received.lock_error.text );
				}

				$dialog.removeClass( 'hidden' ).show();
				$( '.wp-tab-first', $dialog ).trigger( 'focus' );
			}
			else if ( received.new_lock ) {
				// Reloading the page, because when the output
				// is blocked, the functionality is turned off
				if ( $dialog.is( ':visible' ) ) {
					_location.reload();
				}
			}
		}

	} );

	new USHeartbeat; // init service

}( jQuery );
