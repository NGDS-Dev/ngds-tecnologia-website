<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

// Slugs for builder loading handlers
// Note: This values must have valid characters for hooks and URL
if ( ! defined( 'US_BUILDER_SLUG' ) ) {
	define( 'US_BUILDER_SLUG', 'us-builder' );
}
if ( ! defined( 'US_BUILDER_SITE_SETTINGS_SLUG' ) ) {
	define( 'US_BUILDER_SITE_SETTINGS_SLUG', 'us-site-settings' );
}

// Typography style tag id in builder
if ( ! defined( 'US_BUILDER_TYPOGRAPHY_TAG_ID' ) ) {
	define( 'US_BUILDER_TYPOGRAPHY_TAG_ID', 'usb-customize-fonts' );
}

if ( ! function_exists( 'usb_get_post_id' ) ) {
	/**
	 * Get the ID of the post or term you are edit
	 *
	 * @return int Returns, if successful, post_id or term_id, otherwise zero
	 */
	function usb_get_post_id() {
		if ( usb_is_builder_page() OR usb_is_preview() ) {
			return (int) us_arr_path( $_REQUEST, 'post', get_queried_object_id() );
		}
		return 0;
	}
}

if ( ! function_exists( 'usb_is_post_editing' ) ) {
	/**
	 * Determines if this is a post edit page
	 *
	 * @return bool Returns TRUE if this is a post edit page, otherwise FALSE
	 */
	function usb_is_post_editing() {
		// Action definitions based on referral link for AJAX requests
		if ( wp_doing_ajax() ) {
			$url_params = (string) wp_parse_url( us_get_safe_var( 'HTTP_REFERER' ), PHP_URL_QUERY );
			return $url_params AND strpos( $url_params, '&action=' . US_BUILDER_SLUG ) !== FALSE;
		}
		global $pagenow;
		return (
			us_strtolower( basename( $pagenow, '.php' ) ) === 'post'
			AND isset( $_REQUEST['post'] )
			AND us_strtolower( us_arr_path( $_REQUEST, 'action' ) ) === US_BUILDER_SLUG
		);
	}
}

if ( ! function_exists( 'usb_is_site_settings' ) ) {
	/**
	 * Determines if this is a site settings edit page
	 *
	 * @return bool Returns TRUE if this is the site settings edit page, otherwise FALSE
	 */
	function usb_is_site_settings() {
		// Action definitions based on referral link for AJAX requests
		if ( wp_doing_ajax() ) {
			$url_params = (string) wp_parse_url( us_get_safe_var( 'HTTP_REFERER' ), PHP_URL_QUERY );
			return $url_params AND strpos( $url_params, '&action=' . US_BUILDER_SITE_SETTINGS_SLUG ) !== FALSE;
		}
		global $pagenow;
		return (
			us_strtolower( basename( $pagenow, '.php' ) ) === 'post'
			AND isset( $_REQUEST['post'] )
			AND us_strtolower( us_arr_path( $_REQUEST, 'action' ) ) === US_BUILDER_SITE_SETTINGS_SLUG
		);
	}
}

if ( ! function_exists( 'usb_is_builder_page' ) ) {
	/**
	 * Determines if this is a builder page
	 *
	 * @return bool Returns TRUE if this is a builder page, otherwise FALSE
	 */
	function usb_is_builder_page() {
		$is_builder_page = (
			usb_is_post_editing()
			OR usb_is_site_settings()
		);
		return (bool) apply_filters( 'usb_is_builder_page', $is_builder_page );
	}
}

if ( ! function_exists( 'usb_is_post_preview' ) ) {
	/**
	 * Determines if builder preview page is shown for Reusable Block or Page Template
	 *
	 * @return Returns TRUE if the current page is a preview in the builder, otherwise FALSE
	 */
	function usb_is_post_preview() {
		// Preview page definitions via query params
		if ( $nonce = us_arr_path( $_REQUEST, US_BUILDER_SLUG ) ) {
			return (bool) wp_verify_nonce( $nonce, US_BUILDER_SLUG );
		}
		// Preview page definitions via action in AJAX requests
		if ( wp_doing_ajax() ) {
			// Note: USBuilder_Ajax::get_action( 'action_render_shortcode' );
			return us_arr_path( $_REQUEST, 'action' ) == 'usb_render_shortcode';
		}

		return FALSE;
	}
}

if ( ! function_exists( 'usb_is_site_settings_preview' ) ) {
	/**
	 * Determines if builder preview site is shown
	 *
	 * @return bool TRUE if builder preview site, FALSE otherwise
	 */
	function usb_is_site_settings_preview() {
		// Preview page definitions via query params
		if ( $nonce = us_arr_path( $_REQUEST, US_BUILDER_SITE_SETTINGS_SLUG ) ) {
			return (bool) wp_verify_nonce( $nonce, US_BUILDER_SITE_SETTINGS_SLUG );
		}
		// Get a referral link on the basis of which we will try to determine the preview of the site
		$referer = us_get_safe_var( 'HTTP_REFERER' );
		if ( ! empty( $referer ) AND strpos( $referer, US_BUILDER_SITE_SETTINGS_SLUG ) !== FALSE ) {
			parse_str( wp_parse_url( $referer, PHP_URL_QUERY ), $params );
			if ( isset( $params[ US_BUILDER_SITE_SETTINGS_SLUG ] ) ) {
				return (bool) wp_verify_nonce( $params[ US_BUILDER_SITE_SETTINGS_SLUG ], US_BUILDER_SITE_SETTINGS_SLUG );
			}
		}

		return FALSE;
	}
}

if ( ! function_exists( 'usb_is_preview' ) ) {
	/**
	 * Determines if builder preview site or page is shown
	 *
	 * @return bool TRUE if builder preview site or page, FALSE otherwise
	 */
	function usb_is_preview() {
		return (
			usb_is_post_preview()
			OR usb_is_site_settings_preview()
		);
	}
}

if ( ! function_exists( 'usb_is_search_preview' ) ) {
	/**
	 * Determines if preview page for search page
	 *
	 * @return bool TRUE if search page, FALSE otherwise
	 */
	function usb_is_search_preview() {
		$post_id = usb_get_post_id();
		if ( $post_id AND $post_id == us_get_option( 'search_page' ) ) {
			return TRUE;
		}
		return FALSE;
	}
}

if ( ! function_exists( 'usb_is_template_preview' ) ) {
	/**
	 * Determines if builder preview page is shown for Reusable Block or Page Template
	 *
	 * @return bool TRUE if builder page, FALSE otherwise
	 */
	function usb_is_template_preview() {
		if ( usb_is_post_preview() ) {
			$post_type = get_post_type( usb_get_post_id() );
			if ( $post_type AND in_array( $post_type, array( 'us_page_block', 'us_content_template' ) ) ) {
				return TRUE;
			}
		}
		return FALSE;
	}
}

if ( ! function_exists( 'usb_post_editing_is_locked' ) ) {
	/**
	 * Determine if a post editing is locked
	 * Note: This method only works after WP is initialized!
	 *
	 * @return bool Returns true if the post editing is locked, false otherwise
	 */
	function usb_post_editing_is_locked() {
		if ( ! $post_id = usb_get_post_id() ) {
			return FALSE;
		}
		return (bool) wp_check_post_lock( $post_id );
	}
}

if ( ! function_exists( 'usb_get_active_panel_name' ) ) {
	/**
	 * Get active panel name
	 *
	 * @return string Returns the name of the active panel
	 */
	function usb_get_active_panel_name() {
		return us_arr_path( $_REQUEST, 'section', /* default */'add_elms' );
	}
}

if ( ! function_exists( 'usb_get_key_custom_css' ) ) {
	/**
	 * Get the meta key custom css
	 *
	 * @return string The meta key custom css
	 */
	function usb_get_key_custom_css() {
		return (string) (
		class_exists( 'USBuilder' )
			? USBuilder::KEY_CUSTOM_CSS
			: ''
		);
	}
}

if ( ! function_exists( 'usb_get_usbid_container' ) ) {
	/**
	 * Get the id of the main container
	 *
	 * @return string
	 */
	function usb_get_usbid_container() {
		return usb_is_post_preview()
			? ' data-usbid="' . USBuilder::MAIN_CONTAINER . '" '
			: '';
	}
}

if ( ! function_exists( 'usb_get_edit_link' ) ) {
	/**
	 * Get link to edits in builder
	 *
	 * @param int $post_id The post ID
	 * @param array $params The additional parameters for the URL
	 * @return string Returns a link to edits in builder
	 */
	function usb_get_edit_link( $post_id, $params = array() ) {
		$reserved_params = array(
			'post' => (int) $post_id,
		);
		$default_params = array(
			'action' => US_BUILDER_SLUG,
		);
		return admin_url( 'post.php?' . build_query( $reserved_params + array_merge( $default_params, (array) $params ) ) );
	}
}

if ( ! function_exists( 'usb_disable_query_monitor_on_preview_page' ) ) {
	add_filter( 'plugins_loaded', 'usb_disable_query_monitor_on_preview_page', /* before init QM */1 );
	/**
	 * Disable QueryMonitor on preview page
	 */
	function usb_disable_query_monitor_on_preview_page() {
		if ( class_exists( 'QueryMonitor' ) AND usb_is_preview() ) {
			// see https://github.com/johnbillion/query-monitor/blob/develop/classes/QueryMonitor.php#L16
			remove_action( 'plugins_loaded', array( QueryMonitor::init(), 'action_plugins_loaded' ) );
		}
	}
}

if ( ! function_exists( 'usb_extend_basic_options_to_show_previews' ) ) {
	add_filter( 'usof_load_options_once', 'usb_extend_basic_options_to_show_previews', 10, 1 );

	/**
	 * Extend the basic options to show previews
	 *
	 * @param array $usof_options The usof options
	 * @return array Returns advanced usof options
	 */
	function usb_extend_basic_options_to_show_previews( $usof_options ) {
		// Check if we are on the preview site
		if ( ! usb_is_site_settings_preview() ) {
			return $usof_options;
		}

		/**
		 * @var string Cookie name where options for previews are stored
		 */
		$_cookie_name = 'usb_preview_site_options';

		// Check the availability of live options for preview
		if ( ! isset( $_COOKIE ) OR empty( $_COOKIE[ $_cookie_name ] ) ) {
			return $usof_options;
		}

		// If there are options and not a preview context, then delete cookies and exit the function
		if ( ! usb_is_preview() ) {
			unset( $_COOKIE[ $_cookie_name ] );
			return $usof_options;
		}

		// Get live options and extend usod_options
		$preview_site_options = us_arr_path( $_COOKIE, $_cookie_name );
		$preview_site_options = json_decode( base64_decode( $preview_site_options ), /* as array */TRUE );
		if ( ! is_array( $preview_site_options ) ) {
			return $usof_options;
		}

		// If the parameters have not changed, return without change
		if ( $preview_site_options === us_get_live_options() ) {
			return $usof_options;
		}

		return us_array_merge( $usof_options, $preview_site_options );
	}
}
