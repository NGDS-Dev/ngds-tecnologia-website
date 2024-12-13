<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! function_exists( 'us_locate_file' ) ) {
	/**
	 * Search for some file in child theme, in parent theme and in common folder
	 *
	 * @param string $filename Relative path to filename with extension
	 * @param bool $all List an array of found files
	 *
	 * @return mixed Single mode: full path to file or FALSE if no file was found
	 * @return array All mode: array or all the found files
	 */
	function us_locate_file( $filename, $all = FALSE ) {
		global $us_template_directory, $us_stylesheet_directory, $us_files_search_paths, $us_file_paths;
		if ( ! isset( $us_files_search_paths ) ) {
			$us_files_search_paths = array();
			if ( defined( 'US_THEMENAME' ) ) {
				if ( is_child_theme() ) {
					// Searching in child theme first
					$us_files_search_paths[] = trailingslashit( $us_stylesheet_directory );
				}
				// Parent theme
				$us_files_search_paths[] = trailingslashit( $us_template_directory );
				// The common folder with files common for all themes
				$us_files_search_paths[] = $us_template_directory . '/common/';
			}

			if ( defined( 'US_CORE_DIR' ) ) {
				$us_files_search_paths[] = US_CORE_DIR;
			}
			// Can be overloaded if you decide to overload something from certain plugin
			$us_files_search_paths = apply_filters( 'us_files_search_paths', $us_files_search_paths );
		}
		if ( ! $all ) {
			if ( ! isset( $us_file_paths ) ) {
				$us_file_paths = apply_filters( 'us_file_paths', array() );
			}
			$filename = untrailingslashit( $filename );
			if ( ! isset( $us_file_paths[ $filename ] ) ) {
				$us_file_paths[ $filename ] = FALSE;
				foreach ( $us_files_search_paths as $search_path ) {
					if ( file_exists( $search_path . $filename ) ) {
						$us_file_paths[ $filename ] = $search_path . $filename;
						break;
					}
				}
			}

			return $us_file_paths[ $filename ];
		} else {
			$found = array();

			foreach ( $us_files_search_paths as $search_path ) {
				if ( file_exists( $search_path . $filename ) ) {
					$found[] = $search_path . $filename;
				}
			}

			return $found;
		}
	}
}

if ( ! function_exists( 'us_get_option' ) ) {
	/**
	 * Get theme option or return default value
	 * Note: The function is duplicated in `us-core/functions/helpers.php`
	 *
	 * @param string $name
	 * @param mixed $default_value
	 *
	 * @return mixed
	 */
	function us_get_option( $name, $default_value = NULL ) {
		if ( function_exists( 'usof_get_option' ) ) {
			return usof_get_option( $name, $default_value );
		} else {
			return $default_value;
		}
	}
}

if ( ! function_exists( 'us_config' ) ) {
	/**
	 * Load and return some specific config or it's part
	 * Note: The function is duplicated in `us-core/functions/helpers.php`
	 *
	 * @param string $path <config_name>[.<key1>[.<key2>[...]]]
	 * @param mixed $default Value to return if no data is found
	 * @return mixed
	 */
	function us_config( $path, $default = NULL, $reload = FALSE ) {
		global $us_template_directory;
		// Caching configuration values in a inner static value within the same request
		static $configs = array();
		// Defined paths to configuration files
		$config_name = strtok( $path, '.' );
		if ( ! isset( $configs[ $config_name ] ) OR $reload ) {
			$config_paths = array_reverse( us_locate_file( 'config/' . $config_name . '.php', TRUE ) );
			if ( empty( $config_paths ) ) {
				if ( WP_DEBUG ) {
					// TODO rework this check for correct plugin activation
					//wp_die( 'Config not found: ' . $config_name );
				}
				$configs[ $config_name ] = array();
			} else {
				us_maybe_load_theme_textdomain();
				// Parent $config data may be used from a config file
				$config = array();
				foreach ( $config_paths as $config_path ) {
					$config = require $config_path;
					// Config may be forced not to be overloaded from a config file
					if ( isset( $final_config ) AND $final_config ) {
						break;
					}
				}
				$configs[ $config_name ] = apply_filters( 'us_config_' . $config_name, $config );
			}
		}

		$path = substr( $path, strlen( $config_name ) + 1 );
		if ( $path == '' ) {
			return $configs[ $config_name ];
		}

		return us_arr_path( $configs[ $config_name ], $path, $default );
	}
}

if ( ! function_exists( 'us_maybe_load_theme_textdomain' ) ) {
	/**
	 * Load theme's textdomain
	 *
	 * @param string $domain
	 * @param string $path Relative path to seek in child theme and theme
	 *
	 * @return bool
	 */
	function us_maybe_load_theme_textdomain( $domain = 'us', $path = '/languages' ) {
		if ( is_textdomain_loaded( $domain ) ) {
			return TRUE;
		}
		$locale = apply_filters( 'theme_locale', determine_locale(), $domain );
		$filepath = us_locate_file( trailingslashit( $path ) . $locale . '.mo' );
		if ( $filepath === FALSE ) {
			return FALSE;
		}

		return load_textdomain( $domain, $filepath );
	}
}

if ( ! function_exists( 'us_translate' ) ) {
	/**
	 * Call language function with string existing in WordPress or supported plugins and prevent those strings from going into theme .po/.mo files
	 *
	 * @return string Translated text.
	 */
	function us_translate( $text, $domain = NULL ) {
		if ( $domain == NULL ) {
			return __( $text );
		} else {
			return __( $text, $domain );
		}
	}
}

if ( ! function_exists( 'us_translate_x' ) ) {
	function us_translate_x( $text, $context, $domain = NULL ) {
		if ( $domain == NULL ) {
			return _x( $text, $context );
		} else {
			return _x( $text, $context, $domain );
		}
	}
}

if ( ! function_exists( 'us_translate_n' ) ) {
	function us_translate_n( $single, $plural, $number, $domain = NULL ) {
		if ( $domain == NULL ) {
			return _n( $single, $plural, $number );
		} else {
			return _n( $single, $plural, $number, $domain );
		}
	}
}

if ( ! function_exists( 'us_wp_link_pages' ) ) {
	/**
	 * Custom Post Pagination
	 * @param bool $echo
	 * @return string Returns or Echoes Pagination
	 */
	function us_wp_link_pages( $echo = FALSE ) {
		$links = wp_link_pages(
			array(
				'before' => '<nav class="post-pagination"><span class="title">' . us_translate( 'Pages:' ) . '</span>',
				'after' => '</nav>',
				'link_before' => '<span>',
				'link_after' => '</span>',
				'echo' => $echo,
			)
		);

		if ( ! $echo ) {
			return $links;
		}
	}
}

if ( ! defined( 'US_API_RETURN_OBJECT' ) ) {
	define( 'US_API_RETURN_OBJECT', 1 );
}

if ( ! defined( 'US_API_RETURN_ARRAY' ) ) {
	define( 'US_API_RETURN_ARRAY', 2 );
}

if ( ! function_exists( 'us_api' ) ) {
	/**
	 * Function for making requests to the remote server us.api
	 * Note: The function is duplicated in `us-core/functions/helpers.php`
	 *
	 * @param string $url The request URL
	 * @param array $get_variables HTTP GET variables
	 * @param int $return Returned data type
	 *
	 * @return array|object|null|string Returns query result if successful, otherwise null or ''
	 */
	function us_api( $url, $get_variables = array(), $return = NULL ) {
		global $help_portal_url;

		if ( empty( $url ) ) {
			return NULL;
		}

		// Add support for dynamic variables for ease of query
		$us_themename = defined( 'US_ACTIVATION_THEMENAME' )
			? US_ACTIVATION_THEMENAME
			: US_THEMENAME;
		$url = str_replace( ':us_themename', strtolower( $us_themename ), $url );

		// Generate request url
		$url = sprintf( '%s%s', untrailingslashit( $help_portal_url ), $url );

		// Set the developer's OR themetest secret key
		if ( isset( $get_variables['secret'] ) ) {
			if ( defined( 'US_DEV_SECRET' ) AND US_DEV_SECRET ) {
				$get_variables['dev_secret'] = US_DEV_SECRET;
				unset( $get_variables['secret'] );

			} else if ( defined( 'US_THEMETEST_CODE' ) AND US_THEMETEST_CODE ) {
				$get_variables['themetest_code'] = US_THEMETEST_CODE;
				unset( $get_variables['secret'] );
			}
		}

		/**
		 * Add HTTP GET variables
		 */
		if ( is_array( $get_variables ) AND ! empty( $get_variables ) ) {
			$url .= '?' . build_query( $get_variables );
		}

		// Make an HTTP request and get the result
		if (
			! $request = wp_remote_request( $url, array( 'timeout' => 60 ) )
			OR is_wp_error( $request )
		) {
			// Saving of cURL errors to the log
			error_log( $request->get_error_message() );
			return '';
		}

		if ( $return === US_API_RETURN_OBJECT ) {
			return json_decode( $request['body'] ); // return object
		}
		if ( $return === US_API_RETURN_ARRAY ) {
			return json_decode( $request['body'], /* as_array */TRUE ); // return array
		}
		return $request['body']; // return data
	}
}

if ( ! function_exists( 'us_setup_wizard_load_template' ) ) {
	/**
	 * Load some specified template and pass variables to it's scope.
	 * Note: The function is duplicate us_load_template in `us-core/functions/helpers.php`
	 * TODO: Remove this function, if edit logic for us_load_template
	 *
	 * (!) If you create a template that is loaded via this method, please describe the variables that it should receive.
	 *
	 * @param string $template_name Template name to include (ex: 'templates/form/form')
	 * @param array $vars Array of variables to pass to a included templated
	 */
	function us_setup_wizard_load_template( $template_name, $vars = NULL ) {

		// Searching for the needed file in a child theme, in the parent theme and, finally, in the common folder
		$file_path = us_locate_file( $template_name . '.php' );

		// Template not found
		if ( $file_path === FALSE ) {
			do_action( 'us_template_not_found:' . $template_name, $vars );

			return;
		}

		$vars = apply_filters( 'us_template_vars:' . $template_name, (array) $vars );
		if ( is_array( $vars ) AND count( $vars ) > 0 ) {
			extract( $vars, EXTR_SKIP );
		}

		do_action( 'us_before_template:' . $template_name, $vars );

		include $file_path;

		do_action( 'us_after_template:' . $template_name, $vars );
	}
}

if ( ! function_exists( 'us_setup_wizard_template_redirect' ) ) {
	/**
	 * Load Setup Wizard Preview template
	 *
	 * @param $template
	 * @return false|mixed
	 */
	add_action( 'template_include', 'us_setup_wizard_template_redirect', 99 );
	function us_setup_wizard_template_redirect( $template ) {
		if ( isset( $_GET['us_setup_wizard_preview'] ) AND $_GET['us_setup_wizard_preview'] ) {
			us_setup_wizard_load_template( 'common/admin/templates/sw_site_preview/page_html' );

			return FALSE;
		}

		return $template;
	}
}

if ( ! function_exists( 'us_color_scheme_preview' ) ) {
	/**
	 * Output preview for color scheme used by ajax and style_scheme
	 *
	 * @param array $scheme
	 *
	 * @return string
	 */
	function us_color_scheme_preview( $scheme ) {
		if ( empty( $scheme ) ) {
			return '';
		}

		$values = us_arr_path( $scheme, 'values', array() );

		$preview = '<div class="usof-scheme-preview">';
		// Header
		$preview .= '<div class="preview_header" style="background:' . us_get_color( $values['color_header_middle_bg'], /* Gradient */ TRUE ) . ';"></div>';
		// Content
		$preview .= '<div class="preview_content" style="background:' . us_get_color( $values['color_content_bg'], /* Gradient */ TRUE ) . ';">';
		// Heading
		$preview .= '<div class="preview_heading" style="color:' . us_get_color( $values['color_content_heading'] ) . ';">' . trim( esc_html( $scheme['title'] ) ) . '</div>';
		// Text
		$preview .= '<div class="preview_text" style="color:' . us_get_color( $values['color_content_text'] ) . ';">';
		$preview .= 'Lorem ipsum dolor sit amet, <span style="color:' . us_get_color( $values['color_content_link'] ) . ';">consectetur</span> elit.';
		$preview .= '</div>';
		// Primary
		$preview .= '<div class="preview_primary" style="background:' . us_get_color( $values['color_content_primary'], /* Gradient */ TRUE ) . ';"></div>';
		// Secondary
		$preview .= '<div class="preview_secondary" style="background:' . us_get_color( $values['color_content_secondary'], /* Gradient */ TRUE ) . ';"></div>';
		$preview .= '</div>';
		// Footer
		$preview .= '<div class="preview_footer" style="background:' . us_get_color( $values['color_footer_bg'], /* Gradient */ TRUE ) . ';"></div>';
		$preview .= '</div>';

		return $preview;
	}
}

if ( ! function_exists( 'us_arr_path' ) ) {
	/**
	 * Get a value from multidimensional array by path
	 * Note: The function is duplicated in `us-core/functions/helpers.php`
	 *
	 * @param array $arr
	 * @param string|array $path <key1>[.<key2>[...]]
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	function us_arr_path( &$arr, $path, $default = NULL ) {
		$path = is_string( $path ) ? explode( '.', $path ) : $path;
		foreach ( $path as $key ) {
			if ( ! is_array( $arr ) OR ! isset( $arr[ $key ] ) ) {
				return $default;
			}
			$arr = &$arr[ $key ];
		}

		return $arr;
	}
}

if ( ! function_exists( 'us_get_color' ) ) {
	/**
	 * Return filtered color value
	 *
	 * @param string $value
	 * @param bool $allow_gradient
	 * @return String
	 */
	function us_get_color( $value, $allow_gradient = FALSE, $css_var = TRUE ) {
		if ( empty( $value ) ) {
			return '';
		}

		// If the value begins "color", remove that prefix
		if ( strpos( $value, 'color' ) === 0 ) {
			$value = str_replace( 'color', '', $value );
		}

		// If the value begins "_", get the color from Theme Options > Colors
		if ( strpos( $value, '_' ) === 0 ) {
			$color = us_get_option( 'color' . $value, '' );

			// Transform the value into CSS var, if set
			if ( $css_var ) {

				// If the value has a gradient and gradient is allowed, add "-grad" suffix
				if ( strpos( $color, 'gradient' ) !== FALSE AND $allow_gradient ) {
					$color = 'var(--color' . str_replace( '_', '-', $value ) . '-grad)';
				} else {
					$color = 'var(--color' . str_replace( '_', '-', $value ) . ')';
				}
			}

			// in other cases use value as color
		} else {
			$color = $value;
		}

		return ( $allow_gradient ) ? $color : us_gradient2hex( $color );
	}
}

if ( ! function_exists( 'us_gradient2hex' ) ) {
	/**
	 * Extract first value from linear-gradient
	 *
	 * @param $color String linear-gradient value
	 * @return String hex value
	 */
	function us_gradient2hex( $color = '' ) {
		if ( preg_match( '~linear-gradient\(([^,]+),([^,]+),([^)]+)\)~', $color, $matches ) ) {
			$color = (string) $matches[2];

			if ( strpos( $color, 'rgb' ) !== FALSE AND preg_match( '~rgba?\([^)]+\)~', $matches[0], $rgba ) ) {
				$color = (string) $rgba[0];
				$color = us_rgba2hex( $color );
			}
		}

		return $color;
	}
}

if ( ! function_exists( 'us_rgba2hex' ) ) {
	/**
	 * Convert RGBA to HEX
	 *
	 * @param string $color
	 * @return string
	 */
	function us_rgba2hex( $color ) {
		// Returns HEX in case of RGB is provided, otherwise returns as is
		$default = "#000000";

		if ( empty( $color ) ) {
			return $default;
		}

		$rgb = array();
		$regex = '#\((([^()]+|(?R))*)\)#';

		if ( preg_match_all( $regex, $color, $matches ) ) {
			$rgba = explode( ',', implode( ' ', $matches[1] ) );
			// Cuts first 3 values for RGB
			$rgb = array_slice( $rgba, 0, 3 );
		} else {
			return (string) $color;
		}

		$output = "#";

		foreach ( $rgb as $color ) {
			$hex_val = dechex( (int) $color );
			if ( strlen( $hex_val ) === 1 ) {
				$output .= '0' . $hex_val;
			} else {
				$output .= $hex_val;
			}
		}

		return $output;
	}
}

if ( ! function_exists( 'us_setup_wizard_output_css_for_section' ) ) {
	/**
	 * Output css for the Setup Wizard Sections / Footer
	 *
	 * @param $section
	 * @return string
	 */
	function us_setup_wizard_output_css_for_section( $section, $with_css = TRUE ) {
		if ( empty( $section ) ) {
			return '';
		}

		if ( preg_match_all( '/\s?css="(.*?)"/i', $section, $matches ) ) {
			$jsoncss_data = us_arr_path( $matches, '1', array() );
		}

		$jsoncss_collection = array();
		if ( ! empty( $jsoncss_data ) and is_array( $jsoncss_data ) ) {
			foreach ( $jsoncss_data as $jsoncss ) {
				us_add_jsoncss_to_collection( $jsoncss, $jsoncss_collection );
			}
		}

		// Apply filters
		$jsoncss_collection = apply_filters( 'us_output_design_css', $jsoncss_collection );

		// Generate CSS code and output data
		if ( $custom_css = us_jsoncss_compile( $jsoncss_collection ) ) {
			if ( ! $with_css ) {
				return $custom_css;
			}
			return sprintf( '<style id="us-design-options-css">%s</style>', $custom_css );
		}
		return '';
	}
}

if ( ! function_exists( 'us_setup_wizard_load_header_settings' ) ) {
	/**
	 * Get selected Header Settings for the Setup Wizard Preview
	 *
	 * @return array|mixed
	 */
	function us_setup_wizard_load_header_settings( $header_settings = array() ) {
		global $us_setup_wizard_header_id;

		if ( ! empty( $us_setup_wizard_header_id ) ) {
			$header_id = $us_setup_wizard_header_id;
		} elseif ( ! empty( $_GET['header_id'] ) ) {
			$header_id = $_GET['header_id'];
		}

		if (
			! empty( $header_id )
			AND $header_templates = us_config( 'header-templates', array() )
			AND isset( $header_templates[ $header_id ] )
		) {
			$header_settings = us_fix_header_template_settings( $header_templates[ $header_id ] );
			if ( isset( $header_settings['is_hidden'] ) ) {
				$header_settings['is_hidden'] = FALSE;
			}
		}

		return $header_settings;
	}
}

if ( ! function_exists( 'us_setup_wizard_before_header' ) ) {
	/**
	 * Load selected Header Settings for the Setup Wizard Preview
	 *
	 * Used in preparation of Setup Wizard From-Scratch templates
	 */
	function us_setup_wizard_before_header() {
		global $us_header_settings;
		$us_header_settings = us_setup_wizard_load_header_settings();
	}
}

if ( ! function_exists( 'us_get_demo_import_config' ) ) {
	/**
	 * Get the config for Demo Import feature from support portal
	 * Note: The function is duplicated in `us-core/functions/helpers.php`
	 *
	 * @return array|mixed Demos Config
	 */
	function us_get_demo_import_config() {
		$transient = 'us_demo_import_config_data_' . US_THEMENAME;

		if ( ! defined( 'US_DEV' ) AND ( $results = get_transient( $transient ) ) !== FALSE ) {
			return $results;
		}

		$get_variables = array();
		if ( defined( 'US_DEV' ) ) {
			$get_variables['hidden'] = 1;
		}

		$us_api_response = us_api( '/us.api/demos_config/:us_themename', $get_variables, US_API_RETURN_ARRAY );
		if ( ! empty( $us_api_response ) AND ! empty( $us_api_response['data'] ) ) {
			$config = $us_api_response['data']; // TODO validation
		} else {
			$config = array();
		}
		set_transient( $transient, $config, HOUR_IN_SECONDS );

		return $config;
	}
}

if ( ! function_exists( 'us_check_and_activate_theme' ) ) {
	/**
	 * Check for activation and activate theme
	 *
	 */
	function us_check_and_activate_theme() {
		// Get current site domain
		$domain = parse_url( site_url(), PHP_URL_HOST );

		$get_variables = array(
			'secret' => '',
			'domain' => $domain,
			'version' => US_THEMEVERSION,
		);

		if ( isset( $_GET['activation_action'] ) AND $_GET['activation_action'] == 'activate' ) {

			if ( ! empty( $_GET['secret'] ) ) {
				$get_variables['secret'] = (string) $_GET['secret'];
			}

			// Get license data
			$us_api_response = us_api( '/envato_auth', $get_variables, US_API_RETURN_ARRAY );
			if (
				is_array( $us_api_response )
				AND isset( $us_api_response['status'] )
				AND $us_api_response['status'] == 1
			) {
				if ( isset( $us_api_response['site_type'] ) AND $us_api_response['site_type'] == 'dev' ) {
					update_option( 'us_license_dev_activated', /* set value */1 );
					delete_option( 'us_license_activated' );
				} else {
					update_option( 'us_license_activated', /* set value */1 );
					delete_option( 'us_license_dev_activated' );
				}

				update_option( 'us_license_secret', $get_variables['secret'] );
				delete_transient( 'us_update_addons_data_' . US_THEMENAME );
			}

		} elseif (
			get_option( 'us_license_activated' )
			OR get_option( 'us_license_dev_activated' )
		) {
			$get_variables['secret'] = (string) get_option( 'us_license_secret' );

			// Get license data
			$us_api_response = us_api( '/envato_auth', $get_variables, US_API_RETURN_ARRAY );
			if (
				is_array( $us_api_response )
				AND isset( $us_api_response['status'] )
				AND $us_api_response['status'] !== 1
			) {
				delete_option( 'us_license_dev_activated' );
				delete_option( 'us_license_activated' );
				delete_option( 'us_license_secret' );
				delete_transient( 'us_update_addons_data_' . US_THEMENAME );
			}
		}

		if ( get_option( 'us_license_dev_activated' ) AND function_exists( 'us_update_option' ) ) {
			us_update_option( 'maintenance_mode', /* set value */1 );
		}
	}
}

if ( ! function_exists( 'us_filesystem_permission_check' ) ) {
	/**
	 * Filters the filesystem credentials
	 *
	 * @return bool
	 */
	function us_filesystem_permission_check() {
		ob_start();
		$creds = request_filesystem_credentials( '', '', FALSE, FALSE, NULL );
		ob_get_clean();

		// Abort if permissions were not available.
		if ( ! WP_Filesystem( $creds ) ) {
			return FALSE;
		}

		return TRUE;
	}
}

if ( ! function_exists( 'us_install_plugin' ) ) {
	/**
	 * Install the plugin
	 *
	 * @param $plugin_slug
	 * @return false
	 */
	function us_install_plugin( $plugin_slug, $activate = TRUE ) {
		if ( ! $plugin_slug ) {
			return FALSE;
		}

		$result = us_load_plugin( $plugin_slug, $activate );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'plugin' => $plugin_slug ) );
	}
}

if ( ! function_exists( 'us_load_plugin' ) ) {
	/**
	 * Load the plugin
	 *
	 * @param $plugin_slug
	 * @return array|bool|int|true|WP_Error|null
	 */
	function us_load_plugin( $plugin_slug, $activate ) {
		$plugins = us_config( 'addons', array() );
		$default_error = new WP_Error( 'us-demo-import-plugin', us_translate( 'Failed to write file to disk.' ) );

		foreach ( $plugins as $i => $plugin ) {
			if ( ! isset( $plugins[ $i ]['premium'] ) ) {
				// Define WordPress repository plugin URL based on slug
				$plugins[ $i ]['source'] = 'https://downloads.wordpress.org/plugin/' . $plugin['slug'] . '.latest-stable.zip';
			}
		}

		if ( empty( $plugins ) ) {
			return $default_error;
		}

		$_plugins = array();
		foreach ( $plugins as $i => $plugin ) {
			$_plugins[ $plugin['slug'] ] = $plugin;
		}

		$plugins = $_plugins;

		$slug = urldecode( $plugin_slug );

		if ( ! isset( $plugins[ $slug ] ) ) {
			return $default_error;
		}

		// Get plugin folder for activate
		$plugin_folder = ( ! empty( $plugins[ $slug ]['folder'] ) ) ? $plugins[ $slug ]['folder'] : $plugins[ $slug ]['slug'];

		// Activate plugin if its has been loaded
		if ( file_exists( trailingslashit( WP_PLUGIN_DIR ) . $plugin_folder ) ) {
			return us_activate_plugin( $slug );
		}

		$plugin = $plugins[ $slug ];

		if ( ! isset( $plugin['source'] ) ) {
			return $default_error;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$skin = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );

		$install_result = $upgrader->install( $plugin['source'] );

		if ( is_wp_error( $install_result ) ) {
			return $install_result;
		}

		if ( ! $install_result ) {
			return new WP_Error( 'us-demo-import-plugin', us_translate( 'An error has occurred. Please reload the page and try again.' ) );
		}

		if ( ! $activate ) {
			return $skin->result;
		}

		return us_activate_plugin( $plugin_slug );
	}
}

if ( ! function_exists( 'us_activate_plugin' ) ) {
	/**
	 * Activate the plugin
	 *
	 * @param $plugin_slug
	 * @return bool|int|true|WP_Error|null
	 */
	function us_activate_plugin( $plugin_slug ) {

		$plugins = us_config( 'addons', array() );

		if ( empty( $plugins ) ) {
			return FALSE;
		}

		$_plugins = array();
		foreach ( $plugins as $i => $plugin ) {
			$_plugins[ $plugin['slug'] ] = $plugin;
		}

		$plugins = $_plugins;

		$slug = urldecode( $plugin_slug );

		if ( ! isset( $plugins[ $slug ] ) ) {
			return FALSE;
		}

		$plugin = $plugins[ $slug ];
		$plugin_folder = ( ! empty( $plugin['folder'] ) ) ? $plugin['folder'] : $plugin['slug'];
		if ( strpos( $plugin['slug'], '/' ) !== FALSE ) {
			$plugin_folder = substr(
				$plugin['slug'],
				0,
				strpos( $plugin['slug'], '/' )
			);
		}

		$plugin_data = get_plugins( '/' . $plugin_folder ); // Retrieve all plugins.
		$plugin_file = array_keys( $plugin_data ); // Retrieve all plugin files from installed plugins.

		$plugin_to_activate = trailingslashit( $plugin_folder ) . $plugin_file[0]; // Match plugin slug with appropriate plugin file.

		// Remove redirect after install plugin Tablepress
		$activate_silent = ( $plugin_slug == 'tablepress' ? TRUE : FALSE );

		$activate = activate_plugin(
			$plugin_to_activate,
			'',
			FALSE,
			$activate_silent
		);

		// Remove redirects after install plugin
		delete_transient( '_wc_activation_redirect' ); // Woocommerce
		delete_transient( '_revslider_welcome_screen_activation_redirect' ); // Slider Revolution
		delete_transient( 'cptui_activation_redirect' ); // CPT UI
		delete_transient( '_vc_page_welcome_redirect' ); // WPBakery


		if ( is_wp_error( $activate ) ) {
			return $activate;
		} else {
			return TRUE;
		}
	}
}
