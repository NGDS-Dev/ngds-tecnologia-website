/**
 * US Helper Library
 * @requires jQuery
 */
! function( $, undefined ) {
	"use strict";

	// Private variables that are used only in the context of this function, it is necessary to optimize the code
	var _window = window,
		_document = document,
		_navigator = navigator,
		_undefined = undefined,
		isPlainObject = $.isPlainObject;

	// Math API
	var max = Math.max,
		min = Math.min,
		pow = Math.pow;

	// If the object exists, then exit
	// Note: this is important for iframe pages, e.g. builder
	if ( isPlainObject( _window.$ush ) ) {
		return;
	}

	// Check for is set objects
	_window.$ush = _window.$ush || {};

	var
		/**
		 * @vat {String} Get the current userAgent
		 */
		ua = _navigator.userAgent.toLowerCase(),
		/**
		 * @var {String} Characters to encode and decode a string base64
		 */
		base64Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=',
		/**
		 * @var {Function} The method returns a string created from the specified sequence of UTF-16 code units
		 */
		fromCharCode = String.fromCharCode;

	/**
	 * Current userAgent
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent
	 * @return {String} Return the userAgent
	 */
	$ush.ua = ua;

	/**
	 * Detect MacOS
	 *
	 * @return {Boolean} True if MacOS, False otherwise
	 */
	$ush.isMacOS = /(Mac|iPhone|iPod|iPad)/i.test( _navigator.platform );

	/**
	 * Detect Firefox
	 *
	 * @return {Boolean} True if firefox, False otherwise
	 */
	$ush.isFirefox = ua.indexOf( 'firefox' ) > -1;

	/**
	 * Detect Safari
	 *
	 * @return {Boolean} True if safari, False otherwise
	 */
	$ush.isSafari = /^((?!chrome|android).)*safari/i.test( ua );

	/**
	 * Determines if touchend
	 *
	 * @return {Boolean} True if touchend, False otherwise
	 */
	$ush.isTouchend = ( 'ontouchend' in _document );

	/**
	 * Function wrapper for use in debounce or throttle
	 *
	 * @param {Function} fn The function to be executed
	 */
	$ush.fn = function( fn ) {
		if ( $.isFunction( fn ) ) {
			fn();
		}
	};

	/**
	 * Determines whether the specified value is undefined type or string.
	 *
	 * @param {Mixed} value The value to check.
	 * @return {Boolean} True if the specified value is undefined, False otherwise
	 */
	$ush.isUndefined = function( value ) {
		return '' + _undefined === '' + value;
	};

	/**
	 * Determines if rtl.
	 *
	 * @return {Boolean} True if rtl, False otherwise.
	 */
	$ush.isRtl = function() {
		return this.toString( _document.body.className ).split(' ').indexOf( 'rtl' ) > -1;
	};

	/**
	 * Determines whether the specified element is node type.
	 *
	 * @param {Node|Mixed} node The node from document.
	 * @return {Boolean} True if the specified elm is node type, False otherwise.
	 */
	$ush.isNode = function( node ) {
		return !! node && node.nodeType;
	};

	/**
	 * Generate unique ID with specified length, will not affect uniqueness!
	 *
	 * @param {String} prefix The prefix to be added to the beginn of the result line
	 * @return {String} Returns unique id
	 */
	$ush.uniqid = function( prefix ) {
		return ( prefix || '' ) + Math.random().toString( 36 ).substr( 2, 9 );
	};

	/**
	 * Converts a string from UTF-8 to ISO-8859-1, replacing invalid or unrepresentable characters
	 *
	 * @param {String} A UTF-8 encoded string
	 * @return {String} Returns the ISO-8859-1 translation of string
	 */
	$ush.utf8Decode = function( data ) {
		var tmp_arr = [], i = 0, ac = 0, c1 = 0, c2 = 0, c3 = 0;
		data += '';
		while ( i < data.length ) {
			c1 = data.charCodeAt( i );
			if ( c1 < 128 ) {
				tmp_arr[ ac ++ ] = fromCharCode( c1 );
				i ++;
			} else if ( c1 > 191 && c1 < 224 ) {
				c2 = data.charCodeAt( i + 1 );
				tmp_arr[ ac ++ ] = fromCharCode( ( ( c1 & 31 ) << 6 ) | ( c2 & 63 ) );
				i += 2;
			} else {
				c2 = data.charCodeAt( i + 1 );
				c3 = data.charCodeAt( i + 2 );
				tmp_arr[ ac ++ ] = fromCharCode( ( ( c1 & 15 ) << 12 ) | ( ( c2 & 63 ) << 6 ) | ( c3 & 63 ) );
				i += 3;
			}
		}
		return tmp_arr.join( '' );
	};

	/**
	 * Converts a string from ISO-8859-1 to UTF-8
	 *
	 * @param {String} An ISO-8859-1 string
	 * @return {String} Returns the UTF-8 translation of string
	 */
	$ush.utf8Encode = function( data ) {
		if ( data === null || this.isUndefined( data ) ) {
			return '';
		}
		var string = ( '' + data ), utftext = '', start, end, stringl = 0;
		start = end = 0;
		stringl = string.length;
		for ( var n = 0; n < stringl; n ++ ) {
			var c1 = string.charCodeAt( n );
			var enc = null;
			if ( c1 < 128 ) {
				end ++;
			} else if ( c1 > 127 && c1 < 2048 ) {
				enc = fromCharCode( ( c1 >> 6 ) | 192 ) + fromCharCode( ( c1 & 63 ) | 128 );
			} else {
				enc = fromCharCode( ( c1 >> 12 ) | 224 ) + fromCharCode( ( ( c1 >> 6 ) & 63 ) | 128 ) + fromCharCode( ( c1 & 63 ) | 128 );
			}
			if ( enc !== null ) {
				if ( end > start ) {
					utftext += string.slice( start, end );
				}
				utftext += enc;
				start = end = n + 1;
			}
		}
		if ( end > start ) {
			utftext += string.slice( start, stringl );
		}
		return utftext;
	};

	/**
	 * Decodes data encoded with MIME base64
	 *
	 * @param {String} data The encoded data
	 * @return {string} Returns the decoded data or empty data on failure
	 */
	$ush.base64Decode = function( data ) {
		var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, ac = 0, dec = '', tmp_arr = [], self = this;
		if ( ! data ) {
			return data;
		}
		data += '';
		do {
			h1 = base64Chars.indexOf( data.charAt( i ++ ) );
			h2 = base64Chars.indexOf( data.charAt( i ++ ) );
			h3 = base64Chars.indexOf( data.charAt( i ++ ) );
			h4 = base64Chars.indexOf( data.charAt( i ++ ) );
			bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;
			o1 = bits >> 16 & 0xff;
			o2 = bits >> 8 & 0xff;
			o3 = bits & 0xff;
			if ( h3 == 64 ) {
				tmp_arr[ ac ++ ] = fromCharCode( o1 );
			} else if ( h4 == 64 ) {
				tmp_arr[ ac ++ ] = fromCharCode( o1, o2 );
			} else {
				tmp_arr[ ac ++ ] = fromCharCode( o1, o2, o3 );
			}
		} while ( i < data.length );
		return self.utf8Decode( tmp_arr.join( '' ) );
	};

	/**
	 * Encodes data with MIME base64
	 *
	 * @param {String} The data to encode
	 * @return {String} Returns the encoded data, as a string
	 */
	$ush.base64Encode = function( data ) {
		var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, ac = 0, enc = '', tmp_arr = [], self = this;
		if ( ! data ) {
			return data;
		}
		data = self.utf8Encode( '' + data );
		do {
			o1 = data.charCodeAt( i ++ );
			o2 = data.charCodeAt( i ++ );
			o3 = data.charCodeAt( i ++ );
			bits = o1 << 16 | o2 << 8 | o3;
			h1 = bits >> 18 & 0x3f;
			h2 = bits >> 12 & 0x3f;
			h3 = bits >> 6 & 0x3f;
			h4 = bits & 0x3f;
			tmp_arr[ ac ++ ] = base64Chars.charAt( h1 ) + base64Chars.charAt( h2 ) + base64Chars.charAt( h3 ) + base64Chars.charAt( h4 );
		} while ( i < data.length );
		enc = tmp_arr.join( '' );
		var r = data.length % 3;
		return ( r ? enc.slice( 0, r - 3 ) : enc ) + '==='.slice( r || 3 );
	};

	/**
	 * Strip HTML and PHP tags from a string
	 *
	 * @param {String} input The input string
	 * @return {String} Returns the stripped string
	 */
	$ush.stripTags = function( input ) {
		return $ush.toString( input )
			.replace( /(<([^>]+)>)/ig, '' )
			.replace( '"', '&quot;' );
	};

	/**
	 * Decode URL-encoded strings
	 *
	 * @param {String} str The URL to be decoded
	 * @return {String} Returns the decoded URL, as a string
	 */
	$ush.rawurldecode = function( str ) {
		return decodeURIComponent( '' + str )
	};

	/**
	 * URL-encode according to RFC 3986
	 *
	 * @param {String} The URL to be encoded
	 * @return {String} Returns a string in which all non-alphanumeric characters except `-_`
	 */
	$ush.rawurlencode = function( str ) {
		return encodeURIComponent( '' + str )
			.replace( /!/g, '%21' )
			.replace( /'/g, '%27' )
			.replace( /\(/g, '%28' )
			.replace( /\)/g, '%29' )
			.replace( /\*/g, '%2A' );
	};

	/**
	 * Behaves the same as setTimeout except uses requestAnimationFrame() where possible for better performance
	 *
	 * @param {Function} fn The callback function
	 * @param {Number} delay The delay in milliseconds
	 */
	$ush.timeout = function( fn, delay ) {
		var handle = {},
			start = new Date().getTime(),
			requestAnimationFrame = _window.requestAnimationFrame;
		function loop() {
			var current = new Date().getTime(),
				delta = current - start;
			delta >= delay
				? fn.call()
				: handle.value = requestAnimationFrame( loop );
		}
		handle.value = requestAnimationFrame( loop );
		return handle;
	};

	/**
	 * Behaves the same as clearTimeout except uses cancelRequestAnimationFrame() where possible for better performance
	 *
	 * @param {Number|{}} fn The callback function
	 */
	$ush.clearTimeout = function( handle ) {
		if ( isPlainObject( handle ) ) {
			handle = handle.value;
		}
		if ( $.isNumeric( handle ) ) {
			_window.cancelAnimationFrame( handle );
		}
	};

	/**
	 * Returns a new function that, when invoked, invokes `fn` at most once per `wait` milliseconds.
	 *
	 * @param {Function} fn Function to wrap
	 * @param {Number} wait Timeout in ms (`100`)
	 * @param {Boolean} no_trailing Optional, defaults to false.
	 *		If no_trailing is true, `fn` will only execute every `wait` milliseconds while the
	 *		throttled-function is being called. If no_trailing is false or unspecified,
	 *		`fn` will be executed one final time after the last throttled-function call.
	 *		(After the throttled-function has not been called for `wait` milliseconds, the internal counter is reset)
	 *
	 * In this visualization, | is a throttled-function call and X is the actual
	 * callback execution:
	 *
	 * > Throttled with `no_trailing` specified as False or unspecified:
	 *	||||||||||||||||||||||||| (pause) |||||||||||||||||||||||||
	 *	X    X    X    X    X    X        X    X    X    X    X    X
	 *
	 * > Throttled with `no_trailing` specified as True:
	 *	||||||||||||||||||||||||| (pause) |||||||||||||||||||||||||
	 *	X    X    X    X    X             X    X    X    X    X
	 *
	 * @return (Function) A new, throttled, function.
	 */
	$ush.throttle = function( fn, wait, no_trailing, debounce_mode ) {
		var self = this;
		if ( ! $.isFunction( fn ) ) {
			return $.noop;
		}
		if ( ! $.isNumeric( wait ) ) {
			wait = 0; // default
		}
		if ( typeof no_trailing !== 'boolean' ) {
			no_trailing = _undefined;
		}

		var last_exec = 0, timeout, context, args;
		return function () {
			context = this;
			args = arguments;
			var elapsed = +new Date() - last_exec;
			function exec() {
				last_exec = +new Date();
				fn.apply( context, args );
			}
			function clear() {
				timeout = _undefined;
			}
			if ( debounce_mode && ! timeout ) {
				exec();
			}
			timeout && self.clearTimeout( timeout );
			if ( self.isUndefined( debounce_mode ) && elapsed > wait ) {
				exec();
			} else if ( no_trailing !== true ) {
				timeout = self.timeout(
					debounce_mode
						? clear
						: exec,
					self.isUndefined( debounce_mode )
						? wait - elapsed
						: wait
				);
			}
		};
	};

	/**
	 * Returns a function, that, as long as it continues to be invoked, will not
	 * be triggered. The functionwill be called after it stops being called for
	 * N milliseconds. If `immediate` is passed, trigger the functionon the
	 * leading edge, instead of the trailing. The functionalso has a property 'clear'
	 * that is a functionwhich will clear the timer to prevent previously scheduled executions.
	 *
	 * @param {Function} fn Function to wrap
	 * @param {Number} wait Timeout in ms (`100`)
	 * @param {Boolean} at_begin Optional, defaults to false.
	 *		If at_begin is false or unspecified, `fn` will only be executed `wait` milliseconds after
	 *		the last debounced-function call. If at_begin is true, `fn` will be executed only at the
	 *		first debounced-function call. (After the throttled-function has not been called for `wait`
	 *		milliseconds, the internal counter is reset)
	 *
	 * In this visualization, | is a throttled-function call and X is the actual
	 * callback execution:
	 *
	 * > Debounced with `at_begin` specified as False or unspecified:
	 *	||||||||||||||||||||||||| (pause) |||||||||||||||||||||||||
	 *	                         X                                 X
	 *
	 * > Debounced with `at_begin` specified as True:
	 *	||||||||||||||||||||||||| (pause) |||||||||||||||||||||||||
	 *	X                                 X
	 *
	 * @return {Function} A new, debounced, function
	 */
	$ush.debounce = function( fn, wait, at_begin ) {
		var self = this;
		return self.isUndefined( at_begin )
			? self.throttle( fn, wait, _undefined, false )
			: self.throttle( fn, wait, at_begin !== false );
	};

	/**
	 * Function call after 1ms
	 *
	 * @private
	 * @type debounced
	 * @param {Function} fn Function to wrap
	 */
	$ush.debounce_fn_1ms = $ush.debounce( $ush.fn, /* wait */1 );

	/**
	 * Function call after 10ms
	 *
	 * @private
	 * @type debounced
	 * @param {Function} fn Function to wrap
	 */
	$ush.debounce_fn_10ms = $ush.debounce( $ush.fn, /* wait */10 );

	/**
	 * The function parses a string argument and returns an integer of the specified radix
	 *
	 * @param {String} value The value
	 * @return {Number} Returns an number from the given string, or 0 instead of NaN
	 */
	$ush.parseInt = function( value ) {
		value = parseInt( value );
		return ! isNaN( value ) ? value : 0;
	};

	/**
	 * Thefunction parses an argument (converting it to a string first if needed)
	 * and returns a floating point number
	 * Note: IEEE 754 standard (https://en.wikipedia.org/wiki/Signed_zero)
	 *
	 * @param {Mixed} value The value to parse
	 * @return {Number} A floating point number parsed from the given string
	 */
	$ush.parseFloat = function( value ) {
		value = parseFloat( value );
		return ! isNaN( value ) ? value : 0;
	};

	/**
	 * Get a value not exceeding a specified range.
	 *
	 * @param {Number} value The current value.
	 * @param {Number} minValue The minimum value.
	 * @param {Number} maxValue The maximum value.
	 * @return {Number} Returns a value in a range.
	 */
	$ush.limitValueByRange = function( value, minValue, maxValue ) {
		return $ush.parseFloat( min( maxValue, max( minValue, value ) ) );
	};

	/**
	 * Converts data objects to a simple array (Arguments, HTMLCollection and etc)
	 *
	 * @param {{}} data The data objects
	 * @return {[]} Returns an array anyway
	 */
	$ush.toArray = function( data ) {
		try {
			data = [].slice.call( data );
		} catch ( err ) {
			console.error( err );
			data = [];
		}
		return data;
	};

	/**
	 * Converts JS value to string.
	 *
	 * @param {Mixed} value The value to convert to a string.
	 * @return {String} Returns a value if successful, otherwise an empty string.
	 */
	$ush.toString = function( value ) {
		var self = this;
		if ( self.isUndefined( value ) || value === null ) {
			return '';
		}
		else if ( isPlainObject( value ) || $.isArray( value ) ) {
			return self.rawurlencode( JSON.stringify( value ) );
		}
		return '' + value;
	};

	/**
	 * Converts a string representation to an plain object
	 *
	 * @param {String} value The value
	 * @return {{}} Returns an object
	 */
	$ush.toPlainObject = function( value ) {
		var self = this;
		try {
			value = JSON.parse( self.rawurldecode( '' + value ) || '{}' );
		} catch ( err ) {}
		if ( ! isPlainObject( value ) ) {
			value = {};
		}
		return value;
	};

	/**
	 * Converts a string to lowercase
	 *
	 * @param {String} value The value
	 * @return {String} Returns a lowercase string
	 */
	$ush.toLowerCase = function( value ) {
		return ( '' + value ).toLowerCase();
	};

	/**
	 * Get a full copy of the object
	 *
	 * @param {{}} _object The object
	 * @param {{}} _default The default object
	 * @return {{}}
	 */
	$ush.clone = function( _object, _default ) {
		return $.extend( /* deep copy */true, {}, _default || {}, _object || {} );
	};

	/**
	 * Escape special characters for PCRE (Perl Compatible Regular Expressions)
	 *
	 * @param {String} value The value
	 * @return {String}
	 */
	$ush.escapePcre = function( value ) {
		return ( '' + value ).replace( /[.*+?^${}()|\:[\]\\]/g, '\\$&' ); // $& means the whole matched string
	};

	/**
	 * Remove all spaces and tabs
	 *
	 * @param {String} text The text
	 * @return {String} Returns a string without spaces
	 */
	$ush.removeSpaces = function( text ) {
		return ( '' + text ).replace( /\s/g, '' );
	};

	/**
	 * Get the size of the element and its position relative to the viewport
	 *
	 * @param {Node} node The node from document
	 * @return {{}}
	 */
	$ush.$rect = function( node ) {
		return this.isNode( node )
			? node.getBoundingClientRect()
			: {};
	};

	/**
	 * Set the caret position.
	 *
	 * @param {Node} node The node.
	 * @param {Numeric} position The position.
	 */
	$ush.setCaretPosition = function( node, position ) {
		var self = this;
		if ( ! self.isNode( node ) ) {
			return;
		}
		// Set caret to end by default
		if ( self.isUndefined( position ) ) {
			position = node.value.length;
		}
		if ( node.createTextRange ) {
			var range = node.createTextRange();
			range.move( 'character', position );
			range.select();
		} else {
			if ( node.selectionStart ) {
				node.focus();
				node.setSelectionRange( position, position );
			} else {
				node.focus();
			}
		}
	};

	/**
	 * Set the cookie
	 *
	 * @param {String} name The cookie name
	 * @param {String} value The cookie value
	 * @param {Number} expiry The expiry in days
	 */
	$ush.setCookie = function ( name, value, expiry ) {
		var date = new Date()
		date.setTime( date.getTime() + ( expiry * /* 24 * 60 * 60 * 1000 */86400000 ) );
		// Cookies cannot exceed 4096 bytes, as specified in RFC 2109 (No. 6.3), RFC 2965 (No. 5.3) and RFC 6265.
		_document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
	};

	/**
	 * Get the cookie
	 *
	 * @param {String} name The cookie name
	 * @return {String|null} Returns a value on success, otherwise null
	 */
	$ush.getCookie = function( name ) {
		name += '='
		var decodedCookie = decodeURIComponent( _document.cookie ),
			cookies = decodedCookie.split(';');
		for ( var i = 0; i < cookies.length; i++ ) {
			var cookie = cookies[i];
			while ( cookie.charAt(0) == ' ' ) {
				cookie = cookie.substring(1);
			}
			if ( cookie.indexOf( name ) == 0 ) {
				return cookie.substring( name.length, cookie.length );
			}
		}
		return null;
	};

	/**
	 * Remove a cookie
	 * Note: Method not used
	 *
	 * @param {String} name The cookie name
	 */
	$ush.removeCookie = function( name ) {
		var self = this;
		if ( self.getCookie( name ) !== null ) {
			self.setCookie( name, /* value */1, /* days */-1 );
		}
	};

	/**
	 * @var {{}} Event methods for import into an object
	 */
	$ush.mixinEvents = {
		/**
		 * Attach a handler to an event for the class instance
		 *
		 * @param {String} eventType A string contain event type
		 * @param {Function} handler A functionto execute each time the event is triggered
		 * @param {Boolean} one A function that is executed only once when an event is triggered
		 * @return self
		 */
		on: function( eventType, handler, one ) {
			var self = this;
			if ( self.$$events === _undefined ) {
				self.$$events = {};
			}
			if ( self.$$events[ eventType ] === _undefined ) {
				self.$$events[ eventType ] = [];
			}
			self.$$events[ eventType ].push( {
				handler: handler,
				one: !! one,
			} );
			return self;
		},
		/**
		 * Attach a handler to an event for the class instance. The handler is executed at most once
		 *
		 * @param {String} eventType A string contain event type
		 * @param {Function} handler A function to execute each time the event is triggered
		 * @return self
		 */
		one: function( eventType, handler ) {
			return this.on( eventType, handler, /* one */true );
		},
		/**
		 * Remove a previously-attached event handler from the class instance
		 *
		 * @chainable
		 * @param {String} eventType A string contain event type
		 * @param {Function} [handler] The functionthat is to be no longer executed
		 * @return self
		 */
		off: function( eventType, handler ) {
			var self = this;
			if (
				self.$$events === _undefined
				|| self.$$events[ eventType ] === _undefined
			) {
				return self;
			}
			if ( handler !== _undefined ) {
				for ( var handlerPos in self.$$events[ eventType ] ) {
					if ( handler === self.$$events[ eventType ][ handlerPos ].handler ) {
						self.$$events[ eventType ].splice( handlerPos, 1 );
					}
				}
			} else {
				self.$$events[ eventType ] = [];
			}
			return self;
		},
		/**
		 * Execute all handlers and behaviours attached to the class instance for the given event type
		 *
		 * @chainable
		 * @param {String} eventType A string contain event type
		 * @param {[]} extraParams Additional parameters to pass along to the event handler
		 * @return self
		 */
		trigger: function( eventType, extraParams ) {
			var self = this;
			if (
				self.$$events === _undefined
				|| self.$$events[ eventType ] === _undefined
				|| self.$$events[ eventType ].length === 0
			) {
				return self;
			}
			var args = arguments,
				params = ( args.length > 2 || ! $.isArray( extraParams ) )
					? [].slice.call( args, 1 )
					: extraParams;
			for ( var i = 0; i < self.$$events[ eventType ].length; i++ ) {
				var event = self.$$events[ eventType ][ i ];
				event.handler.apply( event.handler, params );
				if ( !! event.one ) {
					self.off( eventType, event.handler );
				}
			}
			return self;
		}
	};

}( jQuery );
