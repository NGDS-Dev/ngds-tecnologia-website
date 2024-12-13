<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * This class describes an us builder ajax
 * TODO: Put the class in order, structure, improve naming
 */
final class USBuilder_Ajax {

	/**
	 * Pattern for getting layout data from shortcodes (Used in importing shortcodes)
	 *
	 * @var string
	 */
	const PATTERN_GRID_LAYOUT_DATA = '/(\s?grid_layout_data="([^"]+)")/';

	/**
	 * Init hooks for AJAX actions
	 *
	 * @access public
	 */
	static function init() {

		// Checking for edit permission
		if (
			! is_user_logged_in()
			AND (
				! current_user_can( 'edit_posts' )
				OR ! current_user_can( 'edit_pages' )
			)
		) {
			return;
		}

		// Check for an action in the list of all actions
		if (
			! $action = us_arr_path( $_POST, 'action' )
			OR ! in_array( $action, self::get_actions() )
		) {
			return;
		}

		// Check if the current user has a certain ability to edit
		if (
			! current_user_can( 'manage_options' )
			AND $action == static::get_action( 'action_save_live_options' )
		) {
			return;
		}

		// The check _nonce
		if ( ! check_ajax_referer( __CLASS__, '_nonce', FALSE ) ) {
			wp_send_json_error(
				array(
					'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
				)
			);
		}

		// Setup postdata and add global $post, $wp_query for correct render of post related data (title, date, custom fields, etc.)
		if ( $post_id = (int) us_arr_path( $_REQUEST, 'post' ) ) {
			global $post, $wp_query;
			$query_args = array(
				'p' => $post_id,
				'post_type' => array_keys( us_get_public_post_types() ),
			);
			$wp_query->query( $query_args );
			$post = get_queried_object();
			setup_postdata( $post );
		}

		// Adds actions to process requests
		foreach ( self::get_actions() as $action_name ) {
			if ( ! empty( $action_name ) AND is_string( $action_name ) ) {
				// The add corresponding method from the class to the AJAX action
				$method_name = substr( $action_name, strlen( 'usb_' ) );
				add_action( 'wp_ajax_' . $action_name, __CLASS__ . "::$method_name" );
			}
		}

		// For AJAX requests, we activate the definition of the builder page,
		// this is necessary for the correct loading of fieldsets
		add_filter( 'usb_is_builder_page', '__return_true' );
	}

	/**
	 * Get the actions
	 *
	 * @access public
	 * @return array The actions
	 */
	static function get_actions() {
		$actions = array(
			'action_get_deferred_fieldsets' => 'usb_get_deferred_fieldsets',
			'action_render_shortcode' => 'usb_render_shortcode',
			'action_save_live_options' => 'usb_save_live_options',
			'action_save_post' => 'usb_save_post',
		);
		// If section templates are enabled, activate the handlers
		if ( us_get_option( 'section_templates', /* default */TRUE ) ) {
			$actions = array_merge( $actions, array(
				'action_get_templates_config' => 'usb_get_templates_config',
				'action_preload_template_category' => 'usb_preload_template_category',
			) );
		}
		return $actions;
	}

	/**
	 * Gets the action
	 *
	 * @param string $key The key
	 * @return Returns action by key if present, otherwise false
	 */
	static function get_action( $key ) {
		if ( empty( $key ) ) {
			return FALSE;
		}
		$actions = static::get_actions();
		return us_arr_path( $actions, $key, /* default */FALSE );
	}

	/**
	 * Creates a nonce
	 *
	 * @access public
	 * @return string
	 */
	static function create_nonce() {
		return wp_create_nonce( __CLASS__ );
	}

	/**
	 * The renders the resulting shortcodes via AJAX
	 *
	 * @access public
	 */
	static function render_shortcode() {
		// Loading all shortcodes
		if ( class_exists( 'WPBMap' ) AND method_exists( 'WPBMap', 'addAllMappedShortcodes' ) ) {
			WPBMap::addAllMappedShortcodes();
		}

		$res = array( 'html' => '' ); // response data
		$content = '';

		// Render default shortcodes
		if ( us_arr_path( $_POST, 'content' ) ) {
			$content = us_arr_path( $_POST, 'content' );
		}

		// Render templates shortcode
		$section_templates_included = us_get_option( 'section_templates', /* default */1 );
		if (
			$section_templates_included
			AND $template_category_id = us_arr_path( $_POST, 'template_category_id' )
			AND $template_category = static::get_template_category( $template_category_id )
		) {
			if ( $error_message = us_arr_path( $template_category, 'error_message' ) ) {
				wp_send_json_error( array( 'message' => $error_message ) );
			}

			// Get template from a category by template_id
			if ( $template_id = us_arr_path( $_POST, 'template_id' ) ) {
				$content = us_arr_path( $template_category, $template_id, /* default */'' );
			}
		}

		// Prepare shortcode
		if ( ! empty( $content ) ) {
			$content = wp_unslash( $content );

			// Prepare template shortcode to default view
			$content = USBuilder_Shortcode::instance()->prepare_text( $content );

			// Execute bound actions before shortcode is rendered
			do_action( 'usb_before_render_shortcode', $content );

			// If there is data of layouts, then we import layouts
			if ( strpos( $content, 'grid_layout_data' ) !== FALSE ) {
				$content = preg_replace_callback(
					self::PATTERN_GRID_LAYOUT_DATA,
					function ( $matches ) {
						return ' items_layout="' . us_import_grid_layout( $matches[/* items_layout */2] ) . '"';
					},
					$content
				);
			}

			// Adds data-usbid attribute to html when output shortcode result
			add_filter( 'do_shortcode_tag', array( USBuilder_Shortcode::instance(), 'add_usbid_to_html' ), 9999, 3 );
			$res['html'] = (string) do_shortcode( $content );
		}

		// Add content to the result (This can be useful for complex changes)
		if ( $section_templates_included AND isset( $_POST['isReturnContent'] ) ) {
			$res['content'] = $content;
		}

		// The response data
		wp_send_json_success( $res );
	}

	/**
	 * Get all deferred fieldsets for elements
	 *
	 * @access public
	 * @return string
	 */
	static function get_deferred_fieldsets() {

		// Get a list of all elements in a theme
		$theme_elements = us_config( 'shortcodes.theme_elements', array(), TRUE );

		// If the element name was specified explicitly, then check the relevance and install it
		if ( $name = us_arr_path( $_POST, 'name' ) AND in_array( $name, $theme_elements ) ) {
			$theme_elements = array( $name );
		}

		// Get all elements available in the theme
		$fieldsets = array();
		foreach ( $theme_elements as $elm_filename ) {
			if ( $elm_config = us_config( "elements/$elm_filename", array() ) ) {
				if (
					// Ignore elements which are not available via condition
					( isset( $elm_config['place_if'] ) AND ! $elm_config['place_if'] )
					OR us_arr_path( $elm_config, 'usb_preload', FALSE )
				) {
					continue;
				}

				// Remove prefixes needed for compatibility from Visual Composer
				foreach ( us_arr_path( $elm_config, 'params', array() ) as $param_name => $options ) {
					if ( ! empty( $options['type'] ) ) {
						$elm_config['params'][ $param_name ]['type'] = us_get_shortcode_name( $options['type'] );
					}
				}

				// Attributes for the form tag
				$form_atts = array(
					'class' => 'usb-panel-fieldset',
					'data-name' => $elm_filename,
				);

				$html = '<form ' . us_implode_atts( $form_atts ) . '>';
				$html .= us_get_template(
					'usof/templates/edit_form', array(
						'type' => $elm_filename,
						'params' => us_arr_path( $elm_config, 'params', array() ),
						'context' => 'shortcode',
					)
				);
				$html .= '</form>';

				$fieldsets[ $elm_filename ] = $html;
			}
		}
		wp_send_json_success( $fieldsets );
	}

	/**
	 * Get the templates configuration
	 *
	 * @access public
	 */
	static function get_templates_config() {
		$transient = 'us_templates_config';

		/**
		 * @var bool True, if the data are stored in the cache, otherwise false
		 */
		$is_transient = (
			! defined( 'US_DEV' )
			AND (
				defined( 'US_DEV_SECRET' )
				OR defined( 'US_THEMETEST_CODE' )
				OR get_option( 'us_license_activated' )
				OR get_option( 'us_license_dev_activated' )
			)
		);

		// Get data from cache
		if ( $is_transient AND ( $results = get_transient( $transient ) ) !== FALSE ) {
			wp_send_json_success( $results );
		}

		/**
		 * @var array HTTP GET variables
		 */
		$get_variables = array(
			'domain' => wp_parse_url( site_url(), PHP_URL_HOST ),
			'secret' => (string) get_option( 'us_license_secret' ),
		);

		// Get template configurations
		$us_api_response = us_api( '/us.api/templates_config/:us_themename', $get_variables, US_API_RETURN_ARRAY );

		// If there is no data, we will return an error
		if ( empty( $us_api_response ) OR ! isset( $us_api_response['data'] ) ) {
			wp_send_json_error( array( 'message' => 'Failed to get templates data' ) );
		}

		// Get templates
		$result = array();
		foreach ( (array) $us_api_response['data'] as $template_category_id => $data ) {
			$result[ $template_category_id ] = us_get_template(
				'usof/templates/templates_list', array(
					'template_category_id' => $template_category_id,
					'templates' => us_arr_path( $data, 'templates' ),
					'title' => us_arr_path( $data, 'name' ),
					'url' => us_arr_path( $data, 'url' ),
				)
			);
		}

		// Save data in the cache
		if ( $is_transient ) {
			set_transient( $transient, $result, HOUR_IN_SECONDS );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Check and preload category templates
	 *
	 * @access public
	 * @return Returns successful ajax response if successful, otherwise ajax error and message
	 */
	static function preload_template_category() {
		// Get category templates
		$template_category_id = us_arr_path( $_POST, 'template_category_id' );
		$template_category = static::get_template_category( $template_category_id ); // Note: On success the data is cached

		// If there are errors print a message
		if ( $error_message = us_arr_path( $template_category, 'error_message' ) ) {
			wp_send_json_error( array( 'message' => $error_message ) );
		}

		// If successful, we return an empty result, which is enough
		wp_send_json_success();
	}

	/**
	 * Get template category
	 *
	 * @access private
	 * @param string $template_category_id The template category id
	 * @return array Returns an array of data
	 */
	static private function get_template_category( $template_category_id ) {
		if ( empty( $template_category_id ) ) {
			return array( 'error_message' => 'No Template Category ID' );
		}

		// The unique category key to store data in a temporary cache
		$transient = 'us_get_template_category:' . $template_category_id;

		/**
		 * @var bool True, if the data are stored in the cache, otherwise false
		 */
		$is_transient = (
			! defined( 'US_DEV' )
			AND (
				defined( 'US_DEV_SECRET' )
				OR defined( 'US_THEMETEST_CODE' )
				OR get_option( 'us_license_activated' )
				OR get_option( 'us_license_dev_activated' )
			)
		);

		// Get data from cache
		if ( $is_transient AND ( $data = get_transient( $transient ) ) !== FALSE ) {
			return $data;
		}

		/**
		 * @var array HTTP GET variables
		 */
		$get_variables = array(
			'category' => $template_category_id,
			'domain' => wp_parse_url( site_url(), PHP_URL_HOST ),
			'secret' => (string) get_option( 'us_license_secret' ),
		);

		// Get template content
		$us_api_response = us_api( '/us.api/templates_content/:us_themename', $get_variables, US_API_RETURN_ARRAY );
		if ( empty( $us_api_response ) ) {
			return array( 'error_message' => 'us.api.404' ); // Note: Error no response from the us.api

		} elseif ( ! empty( $us_api_response['errors'] ) AND is_array( $us_api_response['errors'] ) ) {
			// Note: Define error messages returned by the help portal
			$error_message = current( $us_api_response['errors'] );

			// TODO: Create a configuration file that will contain all possible error codes and descriptions received from help portal
			if ( $error_message == 'validation.purchase_code.wrong' ) {
				$error_message = 'Invalid theme activation code';
			}
			return array( 'error_message' => $error_message );
		}

		if ( empty( $us_api_response['data'] ) ) {
			return array( 'error_message' => 'Failed to load templates data' );
		}

		if ( is_array( $us_api_response['data'] ) ) {

			// Key in the imported data, where to set the placeholder
			$use_placeholder = 'use:placeholder';

			// Get image placeholder
			$src_placeholder = us_get_img_placeholder( 'full', /* src only */TRUE );

			foreach( $us_api_response['data'] as &$row_content ) {
				// Replace use:placeholder in design options (css)
				$use_placeholder_raw = rawurlencode( $use_placeholder );
				if ( strpos( $row_content, $use_placeholder_raw ) !== FALSE ) {
					$row_content = str_replace( $use_placeholder_raw, rawurlencode( $src_placeholder ), $row_content );
				}
				// Replace use:placeholder in content (src="use:placeholder")
				if ( strpos( $row_content, $use_placeholder ) !== FALSE ) {
					$row_content = str_replace( $use_placeholder, $src_placeholder, $row_content );
				}
			}
			unset( $row_content );
		}

		// Save data in the cache
		if ( $is_transient ) {
			set_transient( $transient, $us_api_response['data'], HOUR_IN_SECONDS );
		}

		return $us_api_response['data'];
	}

	/**
	 * Updates post or term data
	 *
	 * @access public
	 */
	// TODO: check capabilities add support for translated posts
	static function save_post() {

		if ( ! $post_id = us_arr_path( $_POST, 'post' ) ) {
			wp_send_json_error( array( 'message' => us_translate( 'Post ID not set' ) ) );
		}
		if ( ! $post = get_post( (int) $post_id ) ) {
			wp_send_json_error( array( 'message' => us_translate( 'Record could not be found' ) ) );
		}

		// Applying the WordPress stripslashes_deep function to remove all added slashes
		$_POST = array_map( 'stripslashes_deep', $_POST );

		if ( isset( $_POST['post_title'] ) ) {
			if ( empty( $_POST['post_title'] ) ) {
				wp_send_json_error( array( 'message' => us_translate( 'Post title cannot be empty' ) ) );
			} elseif ( mb_strlen( $_POST['post_title'] ) > 255 ) {
				wp_send_json_error( array( 'message' => us_translate( 'Post title cannot exceed 255 characters' ) ) );
			}
			$post->post_title = $_POST['post_title'];
		}

		// If content is set, then we get it and apply filters to it
		if ( isset( $_POST['post_content'] ) ) {
			// Get and clearing content from usbid="..."
			$post->post_content = preg_replace( '/(\s?usbid="([^\"]+)")/', '', (string) $_POST['post_content'] );
			// Additional cleaning of layout data
			$post->post_content = preg_replace( self::PATTERN_GRID_LAYOUT_DATA, '', $post->post_content );
		}

		// Updating post metadata
		if ( $metadata = us_arr_path( $_POST, 'pageMeta', /* Default */ array() ) ) {
			// Clearing metadata from HTML tags
			$key_custom_css = USBuilder::KEY_CUSTOM_CSS;
			if ( isset( $metadata[ $key_custom_css ] ) ) {
				$metadata[ $key_custom_css ] = wp_strip_all_tags( $metadata[ $key_custom_css ] );
				// Add entries for Visual Composer
				$vc_meta_keys = array( '_wpb_post_custom_css', 'vc_post_custom_css' );
				$metadata = array_merge( array_fill_keys( $vc_meta_keys, $metadata[ $key_custom_css ] ), $metadata );
			}

			$post->meta_input = (array) $metadata;
		}

		if ( isset( $_POST['post_status'] ) ) {
			if ( ! array_key_exists( $_POST['post_status'], get_post_stati() ) ) {
				wp_send_json_error( array( 'message' => us_translate( 'Invalid post status' ) ) );
			}
			$post->post_status = $_POST['post_status'];
		}

		wp_update_post( $post, /* WP_Error */ TRUE );
		$result = NULL; // TODO fix for error handling
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success();
	}

	/**
	 * Updates to live_options
	 * Note: All options are stored in a common object to provide access through basic methods and do not disturb import or export
	 *
	 * @access public
	 */
	static function save_live_options() {
		if ( $post_options = us_maybe_get_post_json( 'live_options' ) ) {
			global $usof_options;
			if ( ! is_array( $usof_options ) ) {
				wp_send_json_error( array( 'message' => '$usof_options is not set' ) );
			}

			// Update all options
			foreach( us_get_live_options( /* only default */TRUE ) as $name => $default_options ) {
				$usof_options[ $name ] = $default_options;
				if ( isset( $post_options[ $name ] ) ) {
					$usof_options[ $name ] = $post_options[ $name ];
				}
			}

			// Get only the actual parameters according to the config files
			// TODO: Find out why `usof_defaults()` does not return all parameters,
			// namely not the full list of post_types, and fix it.
			// $usof_options = array_intersect_key( $usof_options, usof_defaults() );

			// Save current usof options values from global $usof_options variable to database
			usof_save_options( $usof_options );
			wp_send_json_success();
		}
		wp_send_json_error( array( 'message' => 'There is no options to save' ) );
	}
}
