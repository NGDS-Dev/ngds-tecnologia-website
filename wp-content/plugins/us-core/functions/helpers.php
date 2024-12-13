<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

// The basic tags for customizing typography
if ( ! defined( 'US_TYPOGRAPHY_TAGS' ) ) {
	define( 'US_TYPOGRAPHY_TAGS', array( 'body', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) );
}

if ( ! function_exists( 'us_get_safe_var' ) ) {
	/**
	 * Get safe values from the `$_SERVER` global variable.
	 *
	 * Note: Using filter_input( INPUT_SERVER, $name ) is not guaranteed
	 * to work correctly as there are known issues.
	 * @link https://www.php.net/manual/es/function.filter-input.php#77307
	 *
	 * @link https://www.php.net/manual/en/filter.filters.php
	 *
	 * @param string $name The name from global variable.
	 * @param int $filter The ID of the filter to apply.
	 * @return mixed Returns a safe value from a global variable.
	 */
	function us_get_safe_var( $name, $filter = FILTER_DEFAULT ) {
		// Note that scalar values are converted to string internally before they are filtered
		return filter_var( getenv( $name ), $filter );
	}
}

if ( ! function_exists( 'us_strtolower' ) ) {
	/**
	 * Make a string lowercase.
	 * Try to use mb_strtolower() when available.
	 *
	 * @param string $string String to format.
	 * @return string
	 */
	function us_strtolower( $string ) {
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $string ) : strtolower( $string );
	}
}

if ( ! function_exists( 'us_prepare_icon_tag' ) ) {
	/**
	 * Prepare a proper icon tag from user's custom input
	 *
	 * @param {String} $icon
	 * @return mixed|string
	 */
	function us_prepare_icon_tag( $icon, $inline_css = '' ) {
		if ( empty( $icon ) ) {
			return '';
		}
		$icon = apply_filters( 'us_icon_class', $icon );
		$icon_arr = explode( '|', $icon );
		if ( count( $icon_arr ) != 2 ) {
			return '';
		}

		$icon_arr[1] = strtolower( sanitize_text_field( $icon_arr[1] ) );
		if ( $icon_arr[0] == 'material' ) {
			$icon_tag = '<i class="material-icons"' . $inline_css . '>' . str_replace(
					array(
						' ',
						'-',
					), '_', $icon_arr[1]
				) . '</i>';
		} else {
			if ( substr( $icon_arr[1], 0, 3 ) == 'fa-' ) {
				$icon_tag = '<i class="' . $icon_arr[0] . ' ' . $icon_arr[1] . '"' . $inline_css . '></i>';
			} else {
				$icon_tag = '<i class="' . $icon_arr[0] . ' fa-' . $icon_arr[1] . '"' . $inline_css . '></i>';
			}
		}

		return apply_filters( 'us_icon_tag', $icon_tag );
	}
}

if ( ! function_exists( 'us_modify_twitter_icon_tag' ) ) {
	/**
	 * Change old Twitter icon to the "X" via svg until Font Awesome 5 updates it
	 */
	add_filter( 'us_icon_tag', 'us_modify_twitter_icon_tag' );
	function us_modify_twitter_icon_tag( $icon_tag ) {
		if ( strpos( $icon_tag, '"fab fa-twitter"' ) === FALSE ) {
			return $icon_tag;
		}
		$x_twitter_icon = '<i class="fab fa-x-twitter">';
		$x_twitter_icon .= '<svg style="width:1em; margin-bottom:-.1em;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">';
		$x_twitter_icon .= '<path fill="currentColor" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/>';
		$x_twitter_icon .= '</svg>';
		$x_twitter_icon .= '</i>';
		return $x_twitter_icon;
	}
}

if ( ! function_exists( 'us_load_template' ) ) {
	/**
	 * Load some specified template and pass variables to it's scope.
	 * Note: The function is duplicated in `common/functions/helpers.php`
	 *
	 * (!) If you create a template that is loaded via this method, please describe the variables that it should receive.
	 *
	 * @param string $template_name Template name to include (ex: 'templates/form/form')
	 * @param array $vars Array of variables to pass to a included templated
	 */
	function us_load_template( $template_name, $vars = NULL ) {

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

if ( ! function_exists( 'us_get_template' ) ) {
	/**
	 * Get some specified template output with variables passed to it's scope.
	 *
	 * (!) If you create a template that is loaded via this method, please describe the variables that it should receive.
	 *
	 * @param string $template_name Template name to include (ex: 'templates/form/form')
	 * @param array $vars Array of variables to pass to a included templated
	 * @return string
	 */
	function us_get_template( $template_name, $vars = NULL ) {
		ob_start();
		us_load_template( $template_name, $vars );

		return ob_get_clean();
	}
}

if ( ! function_exists( 'us_get_option' ) ) {
	/**
	 * Get theme option or return default value
	 * Note: The function is duplicated in `common/functions/helpers.php`
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

if ( ! function_exists( 'us_update_option' ) ) {
	/**
	 * Theme Settings Updates
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return bool
	 */
	function us_update_option( $name, $value ) {
		if ( function_exists( 'usof_save_options' ) ) {
			global $usof_options;
			usof_load_options_once();

			if ( isset( $usof_options[ $name ] ) ) {
				$usof_options[ $name ] = $value;
				usof_save_options( $usof_options );

				return TRUE;
			}
		}

		return FALSE;
	}
}

/**
 * @var $us_wp_queries array Allows to use different global $wp_query in different context safely
 * TODO: This variable in use?
 */
$us_wp_queries = array();

if ( ! function_exists( 'us_open_wp_query_context' ) ) {
	/**
	 * Opens a new context to use a new custom global $wp_query
	 *
	 * (!) Don't forget to close it!
	 */
	function us_open_wp_query_context() {
		if ( ! isset( $GLOBALS['us_wp_queries'] ) OR ! is_array( $GLOBALS['us_wp_queries'] ) ) {
			$GLOBALS['us_wp_queries'] = array();
		}
		if ( is_array( $GLOBALS ) AND isset( $GLOBALS['wp_query'] ) ) {
			array_unshift( $GLOBALS['us_wp_queries'], $GLOBALS['wp_query'] );
		}
	}
}

if ( ! function_exists( 'us_close_wp_query_context' ) ) {
	/**
	 * Closes last context with a custom
	 */
	function us_close_wp_query_context() {
		if ( isset( $GLOBALS['us_wp_queries'] ) AND is_array( $GLOBALS['us_wp_queries'] ) AND count( $GLOBALS['us_wp_queries'] ) > 0 ) {
			$GLOBALS['wp_query'] = array_shift( $GLOBALS['us_wp_queries'] );
			wp_reset_postdata();
		} else {
			// In case someone forgot to open the context
			wp_reset_query();
		}
	}
}

if ( ! function_exists( 'us_add_to_page_block_ids' ) ) {
	/**
	 * Opens a new Reusable Block context
	 *
	 */
	function us_add_to_page_block_ids( $page_block_id = NULL ) {

		global $us_page_block_ids;
		if ( empty( $us_page_block_ids ) ) {
			$us_page_block_ids = array();
		}
		if ( $page_block_id != NULL ) {
			array_unshift( $us_page_block_ids, $page_block_id );
		}

	}
}

if ( ! function_exists( 'us_remove_from_page_block_ids' ) ) {
	/**
	 * Closes last page_block context
	 */
	function us_remove_from_page_block_ids() {
		global $us_page_block_ids;

		return array_shift( $us_page_block_ids );
	}
}

if ( ! function_exists( 'us_arr_path' ) ) {
	/**
	 * Get a value from multidimensional array by path
	 * Note: The function is duplicated in `common/functions/helpers.php`
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

if ( ! function_exists( 'us_implode_atts' ) ) {
	/**
	 * Converts array to attribute string for html tag or shortcode
	 *
	 * @param array $atts Attributes Array
	 * @param bool $for_shortcode Attributes for the shortcode
	 * @param string $separator Separator between parameters
	 * @return string
	 */
	function us_implode_atts( $atts = array(), $for_shortcode = FALSE, $separator = ' ' ) {
		if ( empty( $atts ) OR ! is_array( $atts ) ) {
			return '';
		}

		/**
		 * Attributes which shouldn't be displayed if empty (this does not apply to shortcode attributes)
		 * @var array
		 */
		$not_empty_atts = array(
			'class',
			'href',
			'rel',
			'src',
			'style',
			'target',
			'title',
		);

		// Filtering the list of classes and leaving only unique ones
		if ( isset( $atts['class'] ) ) {
			$atts['class'] = implode( ' ', array_unique( explode( ' ', trim( (string) $atts['class'] ) ) ) );
		}

		$result = array();
		foreach ( $atts as $key => $value ) {

			// For shortcode
			if ( $for_shortcode ) {

				// Decode html entities, if any, and delete all the html except the permitted ones
				$value = strip_tags( wp_specialchars_decode( $value ), '<br><code><i><small><span><strong><sub><sup>' );
				$result[] = sprintf( '%s="%s"', $key, $value );

				// For html tag
			} else {

				// Skip attributes with empty values if they are not allowed
				if ( $value === '' AND in_array( $key, $not_empty_atts ) ) {
					continue;
				}

				// The returns the href value back to normal
				if ( $key === 'href' AND ! empty( $value ) ) {
					$value = rawurldecode( (string) $value );
				}

				// The return classname dynamically
				if ( $key == 'class' AND ! empty( $value ) ) {
					$value = us_replace_dynamic_value( $value );
				}

				$key = esc_attr( $key );

				$result[] = ( $value !== '' )
					? sprintf( '%s="%s"', $key, esc_attr( $value ) )
					: $key;
			}
		}

		$separator = (string) $separator;

		return $separator . implode( $separator, $result );
	}
}

if ( ! function_exists( 'us_config' ) ) {
	/**
	 * Load and return some specific config or it's part
	 * Note: The function is duplicated in `common/functions/helpers.php`
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

if ( ! function_exists( 'us_is_elm_editing_page' ) ) {
	/**
	 * Returns true if it is the admin "Edit" page or the Live Builder page or an ajax request.
	 * Used in elements config files to reduce DB queries.
	 *
	 * @return bool
	 */
	function us_is_elm_editing_page() {
		global $pagenow;
		if (
			in_array( $pagenow, array( 'post.php', 'post-new.php' ) )
			OR wp_doing_ajax()
			OR usb_is_builder_page()
		) {
			return TRUE;
		}
		return FALSE;
	}
}

if ( ! function_exists( 'us_get_image_size_params' ) ) {
	/**
	 * Get image size information as an array
	 *
	 * @param string $size_name
	 * @return array
	 */
	function us_get_image_size_params( $size_name ) {
		$img_sizes = wp_get_additional_image_sizes();

		// Getting custom image size
		if ( isset( $img_sizes[ $size_name ] ) ) {
			return $img_sizes[ $size_name ];

			// Get standard image size
		} else {
			return array(
				'width' => get_option( "{$size_name}_size_w" ),
				'height' => get_option( "{$size_name}_size_h" ),
				'crop' => get_option( "{$size_name}_crop", '0' ),
			);
		}
	}
}

if ( ! function_exists( 'us_pass_data_to_js' ) ) {
	/**
	 * Transform some variable to elm's onclick attribute, so it could be obtained from JavaScript as:
	 * var data = elm.onclick()
	 *
	 * @param mixed $data Data to pass
	 * @param bool $onclick Returning the result from the onclick attribute
	 * @return string Element attribute ' onclick="..."'
	 */
	function us_pass_data_to_js( $data, $onclick = TRUE ) {
		$result = 'return ' . us_json_encode( $data );

		return $onclick
			? ' onclick=\'' . $result . '\''
			: $result;
	}
}

if ( ! function_exists( 'us_json_encode' ) ) {
	/**
	 * Returns a JSON representation of the data
	 *
	 * @param mixed $data The data
	 * @return string
	 */
	function us_json_encode( $data ) {
		return htmlspecialchars( json_encode( $data ), ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'us_maybe_get_post_json' ) ) {
	/**
	 * Try to get variable from JSON-encoded post variable
	 * Note: we pass some params via json-encoded variables, as via pure post some data (ex empty array) will be absent
	 *
	 * @param string $name $_POST's variable name
	 * @return array
	 */
	function us_maybe_get_post_json( $name = 'template_vars' ) {
		if ( isset( $_POST[ $name ] ) AND is_string( $_POST[ $name ] ) ) {
			$result = json_decode( stripslashes( $_POST[ $name ] ), TRUE );
			if ( ! is_array( $result ) ) {
				$result = array();
			}

			return $result;
		} else {
			return array();
		}
	}
}

if ( ! function_exists( 'us_array_merge_insert' ) ) {
	/**
	 * Merge arrays, inserting $arr2 into $arr1 before/after certain key
	 *
	 * @param array $arr Modifyed array
	 * @param array $inserted Inserted array
	 * @param string $position 'before' / 'after' / 'top' / 'bottom'
	 * @param string $key Associative key of $arr1 for before/after insertion
	 * @return array
	 */
	function us_array_merge_insert( array $arr, array $inserted, $position = 'bottom', $key = NULL ) {
		if ( $position == 'top' ) {
			return array_merge( $inserted, $arr );
		}
		$key_position = ( $key === NULL ) ? FALSE : array_search( $key, array_keys( $arr ) );
		if ( $key_position === FALSE OR ( $position != 'before' AND $position != 'after' ) ) {
			return array_merge( $arr, $inserted );
		}
		if ( $position == 'after' ) {
			$key_position ++;
		}

		return array_merge( array_slice( $arr, 0, $key_position, TRUE ), $inserted, array_slice( $arr, $key_position, NULL, TRUE ) );
	}
}

if ( ! function_exists( 'us_array_merge' ) ) {
	/**
	 * Recursively merge two or more arrays in a proper way
	 *
	 * @param array $array1
	 * @param array $array2
	 * @param array ...
	 * @return array
	 */
	function us_array_merge( $array1, $array2 ) {
		$keys = array_keys( $array2 );
		// Is associative array?
		if ( array_keys( $keys ) !== $keys ) {
			foreach ( $array2 as $key => $value ) {
				if ( is_array( $value ) AND isset( $array1[ $key ] ) AND is_array( $array1[ $key ] ) ) {
					$array1[ $key ] = us_array_merge( $array1[ $key ], $value );
				} else {
					$array1[ $key ] = $value;
				}
			}
		} else {
			foreach ( $array2 as $value ) {
				if ( ! in_array( $value, $array1, TRUE ) ) {
					$array1[] = $value;
				}
			}
		}

		if ( func_num_args() > 2 ) {
			foreach ( array_slice( func_get_args(), 2 ) as $array2 ) {
				$array1 = us_array_merge( $array1, $array2 );
			}
		}

		return $array1;
	}
}

if ( ! function_exists( 'us_shortcode_atts' ) ) {
	/**
	 * Combine user attributes with known attributes and fill in defaults from config when needed.
	 *
	 * @param array $atts Passed attributes
	 * @param string $shortcode Shortcode name
	 * @param string $param_name Shortcode's config param to take pairs from
	 * @return array
	 */
	function us_shortcode_atts( $atts, $shortcode ) {
		$pairs = array();
		$element = ( strpos( $shortcode, 'vc_' ) === 0 )
			? $shortcode
			: substr( $shortcode, 3 ); // The us_{element}

		if ( in_array( $element, us_config( 'shortcodes.theme_elements', array() ) ) ) {
			$element_config = us_config( "elements/$element", array() );
			if ( ! empty( $element_config['params'] ) ) {
				foreach ( $element_config['params'] as $param_name => $param_config ) {

					// Override the default value for shortcodes only, if set
					if ( isset( $param_config['shortcode_std'] ) ) {
						$param_config['std'] = $param_config['shortcode_std'];
					}

					$pairs[ $param_name ] = isset( $param_config['std'] ) ? $param_config['std'] : NULL;
				}
			}

			// Fallback params always have an empty string as std
			if ( ! empty( $element_config['fallback_params'] ) ) {
				foreach ( $element_config['fallback_params'] as $param_name ) {
					$pairs[ $param_name ] = '';
				}
			}
		} elseif ( array_key_exists( $shortcode, us_config( 'shortcodes.modified', array() ) ) ) {
			$pairs = us_config( 'shortcodes.modified.' . $shortcode . '.' . 'atts', array() );
		}

		// Allow ID for the US Builder
		if ( ! empty( $atts['usbid'] ) ) {
			$pairs['usbid'] = '';
		}

		$atts = shortcode_atts( $pairs, $atts, $shortcode );

		return apply_filters( 'us_shortcode_atts', $atts, $shortcode );
	}
}

if ( ! function_exists( 'us_prepare_inline_css' ) ) {
	/**
	 * Prepare a proper inline-css string from given css property
	 *
	 * @param array|string $props Array ( key => value ) of css properties or property name
	 * @param mixed $prop_value Value for property if name is used
	 * @return string
	 */
	function us_prepare_inline_css( $props, $prop_value = NULL ) {
		$return = '';
		if ( is_string( $props ) AND ! empty( $prop_value ) ) {
			$props = array( $props => $prop_value );
		}
		if ( ! is_array( $props ) OR empty( $props ) ) {
			return $return;
		}
		foreach ( $props as $prop => $value ) {
			$value = trim( (string) $value );

			// Do not apply if a value is empty string or begins double minus `--`
			if ( $value === '' OR strpos( $value, '--' ) === 0 ) {
				continue;
			}

			// The normalization of specific values
			switch ( us_strtolower( $prop ) ) {
				case 'font-family':
					if ( in_array( $value, US_TYPOGRAPHY_TAGS ) ) {
						if ( $value == 'body' ) {
							$value = 'var(--font-family)';
						} else {
							$value = sprintf( 'var(--%s-font-family)', $value );
						}
					}
					break;
				case 'background-image':
					if ( $image = wp_get_attachment_image_url( (int) $value, 'full' ) ) {
						$value = 'url(' . $image . ')';
					} else {
						$value = 'url(' . $value . ')';
					}
					break;
			}

			if ( ! empty( $prop ) AND ! empty( $value ) ) {
				$return .= "{$prop}:{$value};";
			}
		}

		return ( ! empty( $return ) )
			? ' style="' . esc_attr( $return ) . '"'
			: $return;
	}
}

if ( ! function_exists( 'us_minify_css' ) ) {
	/**
	 * Prepares a minified version of CSS file
	 *
	 * @link http://manas.tungare.name/software/css-compression-in-php/
	 * @param string $css
	 * @return string
	 */
	function us_minify_css( $css ) {

		// Remove unwanted symbols
		$css = wp_strip_all_tags( $css, TRUE );

		// Remove comments
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

		// Remove spaces
		$css = str_replace( array( ' {', '{ ' ), '{', $css );
		$css = str_replace( ': ', ':', $css );
		$css = str_replace( ' > ', '>', $css );
		$css = str_replace( ' ~ ', '~', $css );
		$css = str_replace( '; ', ';', $css );
		$css = str_replace( ' !', '!', $css );
		$css = str_replace( ', ', ',', $css );

		// Remove doubled spaces
		$css = str_replace( array( '  ', '    ', '    ' ), '', $css );

		// Remove semicolon before closing bracket
		$css = str_replace( array( ';}', '; }', ' }', '} ' ), '}', $css );

		return $css;
	}
}

if ( ! function_exists( 'usof_meta' ) ) {
	/**
	 * Get metabox option value
	 *
	 * @return string|array
	 */
	function usof_meta( $key, $post_id = NULL ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$value = '';
		if ( ! empty( $key ) ) {
			if ( metadata_exists( 'post', $post_id, $key ) ) {
				$value = get_post_meta( $post_id, $key, TRUE );
				// Return default value if meta does not exist
			} else {
				$config = us_config( 'meta-boxes', array() );
				foreach ( $config as $meta_box ) {
					if ( ! empty( $meta_box['fields'] ) ) {
						foreach ( $meta_box['fields'] as $meta_field_key => $meta_field_data ) {
							if ( $meta_field_key == $key AND ! empty( $meta_field_data['std'] ) ) {
								$value = $meta_field_data['std'];
								break 2;
							}
						}
					}
				}
			}

		}

		return apply_filters( 'usof_meta', $value, $key, $post_id );
	}
}

if ( ! function_exists( 'usof_get_responsive_buttons' ) ) {
	/**
	 * Get the layout of responsive buttons
	 *
	 * @return string
	 */
	function usof_get_responsive_buttons() {
		$output = '';
		foreach ( (array) us_get_responsive_states() as $state => $data ) {
			$state_atts = array(
				'class' => 'usof-responsive-button ui-icon_devices_' . $state,
				'data-responsive-state' => $state,
				'title' => strip_tags( $data['title'] ),
			);
			if ( $state === 'default' ) {
				$state_atts['class'] .= ' active';
			}
			$output .= '<div' . us_implode_atts( $state_atts ) . '></div>';
		}
		return '<div class="usof-responsive-buttons">'. $output .'</div>';
	}
}

if ( ! function_exists( 'us_get_preloader_numeric_types' ) ) {
	/**
	 * Get preloader numbers
	 *
	 * @return array
	 */
	function us_get_preloader_numeric_types() {
		$config = us_config( 'theme-options' );
		$result = array();

		if ( isset( $config['general']['fields']['preloader']['options'] ) ) {
			$options = $config['general']['fields']['preloader']['options'];
		} else {
			return array();
		}

		if ( is_array( $options ) ) {
			foreach ( $options as $option => $title ) {
				if ( (int) $option != 0 ) {
					$result[] = $option;
				}
			}

			return $result;
		} else {
			return array();
		}
	}
}

if ( ! function_exists( 'us_shade_color' ) ) {
	/**
	 * Shade color https://stackoverflow.com/a/13542669
	 *
	 * @param string $color
	 * @param string $percent
	 * @return string
	 */
	function us_shade_color( $color, $percent = '0.2' ) {
		$default = '';

		if ( empty( $color ) ) {
			return $default;
		}
		// TODO: make RGBA values appliable
		$color = str_replace( '#', '', $color );

		if ( strlen( $color ) == 6 ) {
			$RGB = str_split( $color, 2 );
			$R = hexdec( $RGB[0] );
			$G = hexdec( $RGB[1] );
			$B = hexdec( $RGB[2] );
		} elseif ( strlen( $color ) == 3 ) {
			$RGB = str_split( $color, 1 );
			$R = hexdec( $RGB[0] );
			$G = hexdec( $RGB[1] );
			$B = hexdec( $RGB[2] );
		} else {
			return $default;
		}

		// Determine color lightness (from 0 to 255)
		$lightness = $R * 0.213 + $G * 0.715 + $B * 0.072;

		// Make result lighter, when initial color lightness is low
		$t = $lightness < 60 ? 255 : 0;

		// Correct shade percent regarding color lightness
		$percent = $percent * ( 1.3 - $lightness / 255 );

		$output = 'rgb(';
		$output .= round( ( $t - $R ) * $percent ) + $R . ',';
		$output .= round( ( $t - $G ) * $percent ) + $G . ',';
		$output .= round( ( $t - $B ) * $percent ) + $B . ')';

		$output = us_rgba2hex( $output );

		// Return HEX color
		return $output;
	}
}

if ( ! function_exists( 'us_hex2rgba' ) ) {
	/**
	 * Convert HEX to RGBA
	 *
	 * @param string $color
	 * @param bool $opacity
	 * @return string
	 */
	function us_hex2rgba( $color, $opacity = FALSE ) {
		$default = 'rgb(0,0,0)';

		// Return default if no color provided
		if ( empty( $color ) ) {
			return $default;
		}

		// Sanitize $color if "#" is provided
		if ( $color[0] == '#' ) {
			$color = substr( $color, 1 );
		}

		// Check if color has 6 or 3 characters and get values
		if ( strlen( $color ) == 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		// Convert hexadec to rgb
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb)
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ",", $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ",", $rgb ) . ')';
		}

		// Return rgb(a) color string
		return $output;
	}
}

if ( ! function_exists( 'us_get_taxonomies' ) ) {
	/**
	 * Get taxonomies for selection
	 * Note: Due to dynamic registration of taxonomies, it is not possible to use the cache in this method
	 *
	 * @param $public_only bool
	 * @param $show_slug bool
	 * @param $output string 'woocommerce_exclude' / 'woocommerce_only'
	 * @param $key_prefix string 'tax|'
	 * @return array: slug => title (plural label)
	 */
	function us_get_taxonomies( $public_only = FALSE, $show_slug = TRUE, $output = '', $key_prefix = '' ) {
		$result = array();

		// Check if 'woocommerce_only' is requested and WooCommerce is not active
		if ( $output == 'woocommerce_only' AND ! class_exists( 'woocommerce' ) ) {
			// Return an empty result in this case
			return $result;
		}
		/*
		 * Getting list of taxonomies. Some public taxonomies may have no regular UI, so we combine two conditions.
		 * Public taxonomies may have no regular admin UI.
		 * And rest of taxonomies should have admin UI to get into our taxonomies list.
		 */
		$not_public_args = array( 'show_ui' => TRUE );
		$public_args = array( 'public' => TRUE, 'publicly_queryable' => TRUE );
		$taxonomies = array();
		if ( ! $public_only ) {
			$taxonomies = get_taxonomies( $not_public_args, 'object' );
		}

		$taxonomies = array_merge( $taxonomies, get_taxonomies( $public_args, 'object' ) );
		foreach ( $taxonomies as $taxonomy ) {

			// Exclude taxonomies, which can't have their own archives
			if ( in_array( $taxonomy->name, array( 'link_category', 'product_shipping_class' ) ) ) {
				continue;
			}

			// Exclude taxonomy which is not linked to any post type
			if ( empty( $taxonomy->object_type ) OR empty( $taxonomy->object_type[0] ) ) {
				continue;
			}

			// Skipping already added taxonomies
			if ( isset( $result[ $key_prefix . $taxonomy->name ] ) ) {
				continue;
			}

			// Check if the taxonomy is related to WooCommerce
			if ( class_exists( 'woocommerce' ) ) {
				$is_woo_tax = FALSE;
				if (
					$taxonomy->name == 'product_cat'
					OR $taxonomy->name == 'product_tag'
					OR (
						strpos( $taxonomy->name, 'pa_' ) === 0
						AND is_object_in_taxonomy( 'product', $taxonomy->name )
					)
				) {
					$is_woo_tax = TRUE;
				}

				// Exclude WooCommerce taxonomies
				if ( $output == 'woocommerce_exclude' ) {
					if ( $is_woo_tax ) {
						continue;
					}

					// Exclude all except WooCommerce taxonomies
				} elseif ( $output == 'woocommerce_only' ) {
					if ( ! $is_woo_tax ) {
						continue;
					}
				}
			}

			$taxonomy_title = $taxonomy->labels->name;

			// Show slug if set
			if ( $show_slug ) {
				$taxonomy_title .= ' (' . $taxonomy->name . ')';
			}

			$result[ $key_prefix . $taxonomy->name ] = $taxonomy_title;
		}

		return (array) apply_filters( 'us_get_taxonomies', $result, $public_only, $show_slug, $output );
	}
}

if ( ! function_exists( 'us_get_live_options' ) ) {
	/**
	 * Get the live options
	 *
	 * @param bool $only_defaults Default options only [optional]
	 * @return array Returns an array of live options
	 */
	function us_get_live_options( $only_defaults = FALSE ) {
		global $usof_options;

		$result = array();
		foreach( us_config( 'live-options' ) as $group_id => $group ) {
			if ( ! isset( $group['fields'] ) OR ! is_array( $group['fields'] ) ) {
				continue;
			}
			foreach( $group['fields'] as $name => $field ) {
				$result[ $name ] = usof_defaults( $name );
				if ( ! $only_defaults AND isset( $usof_options[ $name ] ) ) {
					$result[ $name ] = $usof_options[ $name ];
				}
			}
		}

		return $result;
	}
}

if ( ! function_exists( 'us_get_typography_option_values' ) ) {
	/**
	 * Get typography options distributed for responsive states
	 *
	 * @param string $screen Screen name for which you want to get options
	 * @return array
	 */
	function us_get_typography_option_values( $screen = NULL ) {
		$result = array();
		$live_options = (array) us_get_live_options();

		foreach ( US_TYPOGRAPHY_TAGS as $tagname ) {
			if (
				! isset( $live_options[ $tagname ] )
				OR ! is_array( $live_options[ $tagname ] )
			) {
				continue;
			}

			// Distribute options across responsive states
			foreach ( us_get_responsive_states( /* only_keys */TRUE ) as $state ) {
				foreach( $live_options[ $tagname ] as $prop_name => $prop_value ) {
					$responsive_prop_value = us_get_responsive_values( $prop_value );
					if ( isset( $responsive_prop_value[ $state ] ) ) {
						$result[ $state ][ $tagname ][ $prop_name ] = $responsive_prop_value[ $state ];
					} else {
						$result[ $state ][ $tagname ][ $prop_name ] = $prop_value;
					}
				}
			}
		}

		// Get typography options for screen
		if ( $screen ) {
			return (array) us_arr_path( $result, $screen, /* default */array() );
		}

		return $result;
	}
}

if ( ! function_exists( 'us_get_uploaded_fonts_css' ) ) {
	/**
	 * Get font-face css for Uploaded Fonts (uploaded by user)
	 *
	 * @return string
	 */
	function us_get_uploaded_fonts_css() {
		$uploaded_fonts_css = '';
		if ( $uploaded_fonts = us_get_option( 'uploaded_fonts', /* default */array() ) ) {
			foreach ( $uploaded_fonts as $uploaded_font ) {
				$files = explode( ',', $uploaded_font['files'] );
				$urls = array();
				foreach ( $files as $file ) {
					if ( $url = wp_get_attachment_url( $file ) ) {

						// Remove a domain from a URL, so it will work for subdomains of languages
						if ( $url_path = wp_parse_url( $url, PHP_URL_PATH ) ) {
							$url = $url_path;
						}

						$urls[] = sprintf( 'url(%s) format("%s")', esc_url( $url ), pathinfo( $url, PATHINFO_EXTENSION ) );
					}
				}
				if ( count( $urls ) ) {
					$uploaded_fonts_css .= '@font-face {';
					$uploaded_fonts_css .= 'font-display:' . us_get_option( 'font_display', 'swap' ) . ';';
					$uploaded_fonts_css .= 'font-style:' . ( $uploaded_font['italic'] ? 'italic' : 'normal' ) . ';';
					$uploaded_fonts_css .= 'font-family:"' . us_sanitize_font_family( $uploaded_font['name'] ) . '";';
					$uploaded_fonts_css .= 'font-weight:' . $uploaded_font['weight'] . ';';
					$uploaded_fonts_css .= 'src:' . implode( ', ', $urls ) . ';';
					$uploaded_fonts_css .= '}';
				}
			}
		}
		return $uploaded_fonts_css;
	}
}

if ( ! function_exists( 'us_get_typography_inline_css' ) ) {
	/**
	 * Get Typography CSS variables
	 *
	 * @return string Returns the generated style
	 */
	function us_get_typography_inline_css() {
		$result = array();

		$typography_option_values = us_get_typography_option_values();

		// Create CSS variables
		foreach ( $typography_option_values as $state => $options ) {

			// Reset CSS variables at the beginning of each responsive state
			$css_vars = array();

			foreach ( $options as $tagname => $tag_options ) {
				foreach ( $tag_options as $prop_name => $prop_value ) {

					$original_prop_value = $prop_value;

					// Filter specific 'font-family' values
					if ( $prop_name == 'font-family' ) {

						// Add quotes for names with spaces
						if (
							strpos( $prop_value, ' ' ) !== FALSE
							AND strpos( $prop_value, ',' ) === FALSE
						) {
							$prop_value = sprintf( '"%s"', $prop_value );
						}

						// Change "none" to "inherit" to be a valid font-family CSS value
						if ( $prop_value == 'none' ) {
							$prop_value = 'inherit';
						}

						// Add Google font fallback
						if ( $google_font_fallback = us_config( 'google-fonts.' . $prop_value . '.fallback' ) ) {
							$prop_value .= ', ' . $google_font_fallback;
						}
					}

					// Filter color values
					if ( $prop_name == 'color' ) {
						$prop_value = us_get_color( $prop_value );
					}

					// Skip values that can't be CSS variable
					if ( $prop_name == 'color_override' ) {
						continue;
					}

					if ( $tagname == 'body' ) {
						$var_name = sprintf( '--%s', $prop_name );
					} else {
						$var_name = sprintf( '--%s-%s', $tagname, $prop_name );
					}

					if (
						$prop_value != ''
						AND (
							$state == 'default'
							OR $original_prop_value != $typography_option_values['default'][ $tagname ][ $prop_name ]
						)
					) {
						$css_vars[ $var_name ] = $prop_value;
					}
				}
			}
			if ( ! empty( $css_vars ) ) {
				$result[ $state ] = array( ':root' => $css_vars );
			} elseif ( isset( $result[ $state ] ) ) {
				unset( $result[ $state ] );
			}
		}

		// Generate css styles
		if ( ! $result = us_jsoncss_compile( $result ) ) {
			return '';
		}

		return $result;
	}
}

if ( ! function_exists( 'us_enqueue_google_fonts' ) ) {
	/**
	 * Enqueue Google Fonts CSS file, used in frontend and admin pages
	 *
	 * @param bool $return_url Return url to font connections
	 * @return mixed
	 */
	function us_enqueue_google_fonts( $return_url = FALSE ) {
		$fonts = $uploaded_font_names = array();

		$google_fonts = us_config( 'google-fonts' );

		// We need names of Uploaded Fonts to exclude loading fonts with the same name from Google
		if ( $uploaded_fonts = us_get_option( 'uploaded_fonts', /* default */array() ) ) {
			foreach ( $uploaded_fonts as $uploaded_font ) {
				$uploaded_font_names[] = us_sanitize_font_family( $uploaded_font['name'] );
			}
		}

		// Get Additional Google Fonts from Theme Options
		if ( $additional_google_fonts = us_get_option( 'custom_font', /* default */array() ) ) {
			foreach ( $additional_google_fonts as $additional_google_font ) {
				$_font_array = explode( '|', $additional_google_font['font_family'], /* limit */2 );

				// Check the existance of font-family name in the config
				if ( isset( $google_fonts[ $_font_array[ /* font-family */0 ] ] ) ) {
					if ( ! empty( $_font_array[ /* font-weight */1 ] ) ) {
						$_font_weights = $_font_array[ /* font-weight */1 ];
					} else {
						$_font_weights = '400,700'; // default weights
					}
					$fonts[ $_font_array[ /* font-family */0 ] ] = explode( ',', $_font_weights );
				}
			}
		}

		// Get typography options
		foreach ( us_get_typography_option_values() as $screen => $options ) {
			foreach ( $options as $tag_name => $tag_options ) {
				if ( ! $font_family = us_arr_path( $tag_options, 'font-family' ) ) {
					continue;
				}

				// Get the Heading 1 font name if inherited
				if ( strpos( $font_family, 'var(--h1-' ) !== FALSE AND isset( $options['h1']['font-family'] ) ) {
					$font_family = $options['h1']['font-family'];
				}

				// Get the Global Text font name if inherited
				if ( $font_family == 'inherit' AND isset( $options['body']['font-family'] ) ) {
					$font_family = $options['body']['font-family'];
				}

				// Exclude Web-safe and Uploaded fonts and all font families not in the Google fonts list
				if (
					strpos( $font_family, ',' ) !== FALSE
					OR in_array( $font_family, $uploaded_font_names )
					OR ! isset( $google_fonts[ $font_family ] )
				) {
					continue;
				}

				// Add font name to result
				if ( ! isset( $fonts[ $font_family ] ) ) {
					$fonts[ $font_family ] = array();
				}

				// Get variation values (font-weight and font-style)
				foreach ( $tag_options as $property => $value ) {

					// Skip unneeded properties
					if ( ! in_array( $property, array( 'font-weight', 'bold-font-weight' ) ) ) {
						continue;
					}

					// Get the Heading 1 value if inherited
					if ( strpos( $value, 'var(--h1-' ) !== FALSE AND isset( $options['h1'][ $property ] ) ) {
						$value = $options['h1'][ $property ];
					}

					// Add italic variation if set
					$is_italic = FALSE;
					if ( isset( $tag_options['font-style'] ) AND $tag_options['font-style'] == 'italic' ) {
						$is_italic = TRUE;
						$value .= 'italic';
					}

					// If the font variant is not available for selected Google font, then:
					if ( ! in_array( $value, $google_fonts[ $font_family ]['variants'] ) ) {
						// First, if font is italic, see if non-italic variant is available
						if ( $is_italic AND in_array( (int) $value, $google_fonts[ $font_family ]['variants'] ) ) {
							$value = (int) $value;
							// Then, if font is italic,see if 400italic variant is available
						} elseif ( $is_italic AND in_array( '400italic', $google_fonts[ $font_family ]['variants'] ) ) {
							$value = '400italic';
							// Then, see if 400 is available
						} elseif ( in_array( '400', $google_fonts[ $font_family ]['variants'] ) ) {
							$value = '400';
							// Then pick the first available variant
						} else {
							$value = $google_fonts[ $font_family ]['variants'][0];
						}
					}

					$fonts[ $font_family ][] = $value;
				}
			}
		}

		if ( empty( $fonts ) ) {
			return false;
		}

		// Create a single URL to include all found fonts from Google
		$family = array();
		$font_url = sprintf( '%s://fonts.googleapis.com/css?family=', is_ssl() ? 'https' : 'http' );
		foreach ( $fonts as $font_family => $font_variations ) {
			$font_variations = array_unique( $font_variations );
			$font_family = str_replace( ' ', '+', $font_family ); // rawurlencode: ' ' => '+'
			if ( ! empty( $font_variations ) ) {
				$font_family .= ':' . implode( '%2C', $font_variations ); // rawurlencode: ',' => '%2C'
			}
			$family[] = $font_family;
		}
		$font_url .= implode( '|', $family );
		$font_url .= '&display=' . us_get_option( 'font_display', 'swap' );

		if ( $return_url ) {
			return $font_url;
		}
		wp_enqueue_style( 'us-fonts', $font_url );
	}
}

if ( ! function_exists( 'us_get_fonts_for_selection' ) ) {
	/**
	 * Get fonts for selection
	 *
	 * @return array
	 */
	function us_get_fonts_for_selection() {
		static $options = array();
		if ( ! empty( $options ) ) {
			return (array) $options;
		}

		// Default empty value
		$options = array( '' => '– ' . us_translate( 'Default' ) . ' –' );

		// Fonts from Typography options (Default/Desktops responsive state only)
		$typography_fonts_group = __( 'Fonts from Typography settings', 'us' );
		foreach ( us_get_typography_option_values( 'default' ) as $tagname => $tag_options ) {
			foreach ( $tag_options as $prop_name => $prop_value ) {
				if ( $prop_name == 'font-family' ) {

					// Get old values before typography fallback will be applied (after version 8.17)
					if (
						$old_font_value = us_get_option( $tagname . '_font_family' )
						AND $old_font_value != 'none|'
						AND $old_font_value != 'get_h1|'
					) {
						$prop_value = strstr( $old_font_value, '|', TRUE );
					}

					// Skip unneeded values
					if ( in_array( $prop_value, array( 'inherit', 'var(--h1-font-family)', FALSE ), TRUE ) ) {
						continue;
					}

					$options[ $typography_fonts_group ][ $tagname ] = ( $tagname == 'body' )
						? $prop_value . ' (' . __( 'used as default font', 'us' ) . ')'
						: $prop_value . ' (' . sprintf( __( 'used in Heading %s', 'us' ), substr( $tagname, 1 ) ) . ')';
				}
			}
		}

		// Additional Google Fonts
		$custom_fonts_group = __( 'Additional Google Fonts', 'us' );
		if ( $custom_fonts = us_get_option( 'custom_font' ) ) {
			foreach ( $custom_fonts as $custom_font ) {
				$font_options = explode( '|', $custom_font['font_family'], 2 );
				$options[ $custom_fonts_group ][ $font_options[0] ] = $font_options[0];
			}
		}

		// Uploaded Fonts
		$uploaded_fonts_group = __( 'Uploaded Fonts', 'us' );
		if ( $uploaded_fonts = us_get_option( 'uploaded_fonts' ) ) {
			foreach ( $uploaded_fonts as $uploaded_font ) {
				$uploaded_font_name = us_sanitize_font_family( $uploaded_font['name'] );
				if (
					empty( $uploaded_font_name )
					OR empty( $uploaded_font['files'] )
				) {
					continue;
				}
				$options[ $uploaded_fonts_group ][ $uploaded_font_name ] = $uploaded_font_name;
			}
		}

		// Web Safe Fonts
		$websafe_fonts_group = __( 'Web safe font combinations (do not need to be loaded)', 'us' );
		foreach ( us_config( 'web-safe-fonts' ) as $web_safe_font ) {
			$options[ $websafe_fonts_group ][ $web_safe_font ] = $web_safe_font;
		}

		return $options;
	}
}

if ( ! function_exists( 'us_get_all_google_fonts' ) ) {
	/**
	 * Get all Google fonts for selection.
	 *
	 * @param bool $in_group Return result in group.
	 * @return array Returns list Google fonts.
	 */
	function us_get_all_google_fonts( $in_group = TRUE ) {
		if ( ! $google_fonts = us_config( 'google-fonts' ) ) {
			return array();
		}
		$keys = array_keys( $google_fonts );
		$result = array_combine( array_map( 'esc_attr', $keys ), $keys );
		if ( $in_group ) {
			$result = array(
				__( 'Google Fonts (loaded from Google servers)', 'us' ) => $result
			);
		}
		return $result;
	}
}

// TODO maybe move to admin area functions
if ( ! function_exists( 'us_get_ip' ) ) {
	/**
	 * Get the remote IP address
	 *
	 * @return string
	 */
	function us_get_ip() {
		// check ip from share internet
		if ( ! $ip = us_get_safe_var( 'HTTP_CLIENT_IP' ) ) {
			// to check ip is pass from proxy
			$ip = us_get_safe_var( 'HTTP_X_FORWARDED_FOR' );
		}
		if ( empty( $ip ) ) {
			$ip = us_get_safe_var( 'REMOTE_ADDR' );
		}
		return apply_filters( 'us_get_ip', $ip );
	}
}

if ( ! function_exists( 'us_get_sidebars' ) ) {
	/**
	 * Get Sidebars for selection
	 *
	 * @return array
	 */
	function us_get_sidebars() {
		static $sidebars = array();
		if ( ! empty( $sidebars ) ) {
			return (array) $sidebars;
		}

		global $wp_registered_sidebars;
		if ( is_array( $wp_registered_sidebars ) AND ! empty( $wp_registered_sidebars ) ) {
			foreach ( $wp_registered_sidebars as $sidebar ) {
				if ( $sidebar['id'] == 'default_sidebar' ) {
					// Add Default Sidebar to the beginning
					$sidebars = array_merge( array( $sidebar['id'] => $sidebar['name'] ), $sidebars );
				} else {
					$sidebars[ $sidebar['id'] ] = $sidebar['name'];
				}
			}
		}

		return $sidebars;
	}
}

if ( ! function_exists( 'us_get_public_post_types' ) ) {
	/**
	 * Get post types, which have frontend single template, taking into account theme options.
	 *
	 * @param string|array $exclude post types to exlude from result.
	 * @param bool $archive_only only archived post types.
	 * @return array: name => title (plural label).
	 */
	function us_get_public_post_types( $exclude = NULL, $archive_only = FALSE ) {

		if ( is_string( $exclude ) ) {
			$exclude = array( $exclude );
		}
		if ( ! is_array( $exclude ) ) {
			$exclude = array();
		}

		// Default result includes built-in pages and posts
		$result = array(
			'page' => us_translate_x( 'Pages', 'post type general name' ),
			'post' => us_translate_x( 'Posts', 'post type general name' ),
		);

		// Append custom post types with specified arguments
		$query_args = array( // an array of key => value arguments to match against each object
			'public' => TRUE,
			'publicly_queryable' => TRUE,
			'_builtin' => FALSE,
		);
		if ( $archive_only ) {
			$query_args['has_archive'] = TRUE;
		}
		$custom_post_types = get_post_types( $query_args, /* output */'objects');
		foreach ( $custom_post_types as $post_type_name => $post_type_obj ) {
			$result[ $post_type_name ] = ( $archive_only )
				? us_translate( 'Archives' ) . ': ' . $post_type_obj->labels->name // add prefix for better UX
				: $post_type_obj->labels->name;
		}

		// Exclude predefined post types, which can't have single frontend template
		$exclude_post_types = array_merge(
			array(
				'reply', // bbPress
				'us_testimonial',
			),
			$exclude
		);
		foreach ( $exclude_post_types as $type ) {
			if ( isset( $result[ $type ] ) ) {
				unset( $result[ $type ] );
			}
		}

		return $result;
	}
}

if ( ! function_exists( 'us_get_page_area_id' ) ) {
	/**
	 * Get value of specified area ID for current / given page.
	 *
	 * @param string $area : header / titlebar / Page Template / sidebar / footer.
	 * @param array $page_args Array with arguments describing site page to get area ID for.
	 * @return int Returns the post ID of the designated area.
	 */
	function us_get_page_area_id( $area, $page_args = array() ) {
		if ( empty( $area ) ) {
			return FALSE;
		}

		// Filling page args for possible later use in us_get_page_area_id() calls during AJAX requests
		global $us_page_args;
		if ( ! isset( $us_page_args ) OR ! is_array( $us_page_args ) ) {
			$us_page_args = array();
		}

		/*
		 * Checking if $page_args is set and retrieving info from it
		 */
		// Page type: post / archive / other special types. Should allways be set when getting given page info
		// TODO: list all used page types
		$page_type = ( ! empty( $page_args['page_type'] ) ) ? $page_args['page_type'] : NULL;
		if ( $page_type ) {
			// Post type for all pages of page / post / custom post type pages
			$post_type = ( $page_type == 'post' AND ! empty( $page_args['post_type'] ) ) ? $page_args['post_type'] : NULL;
			// Post ID for specific single post page
			$post_ID = ( $page_type == 'post' AND ! empty( $page_args['post_ID'] ) ) ? $page_args['post_ID'] : NULL;

			// Taxonomy type for all pages of taxonomy archives
			$taxonomy_type = ( $page_type == 'archive' AND ! empty( $page_args['taxonomy_type'] ) ) ? $page_args['taxonomy_type'] : NULL;
			// Taxonomy ID for specific taxonomy archive page
			$taxonomy_ID = ( $page_type == 'archive' AND ! empty( $page_args['taxonomy_ID'] ) ) ? $page_args['taxonomy_ID'] : NULL;
		} else {
			$post_type = $post_ID = $taxonomy_type = $taxonomy_ID = NULL;
		}

		// Check if we need to fill page args during this function call
		$fill_page_args = ( empty( $us_page_args['page_type'] ) AND $page_type == NULL );

		// Get public post types except Pages and Products
		$public_post_types = array_keys( us_get_public_post_types( /* exclude */array( 'page', 'product' ) ) );

		// Get public taxonomies EXCEPT Products
		$public_taxonomies = array_keys( us_get_taxonomies( TRUE, FALSE, 'woocommerce_exclude' ) );

		// Get Products taxonomies ONLY
		$product_taxonomies = array_keys( us_get_taxonomies( TRUE, FALSE, 'woocommerce_only' ) );

		// Default from Theme Options
		$area_id = $default_area_id = us_get_option( $area . '_id', '' );

		// WooCommerce Products
		if (
			$post_type == 'product' // Given page params
			OR ( function_exists( 'is_product' ) AND is_product() ) // Current page
		) {
			$area_id = us_get_option( $area . '_product_id' );

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'post';
				$us_page_args['post_type'] = 'product';
			}

			// WooCommerce Shop Page
		} elseif (
			$page_type == 'shop' // Given page params
			OR ( // Current page
				function_exists( 'is_shop' )
				AND is_shop()
				AND ! is_search()
			)
		) {
			$area_id = us_get_option( $area . '_shop_id' );

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'shop';
			}

			// WooCommerce Products Search
		} elseif (
			$page_type == 'shop_search' // Given page params
			OR ( // Current page
				class_exists( 'woocommerce' )
				AND is_post_type_archive( 'product' )
				AND is_search()
			)
		) {
			$area_id = us_get_option( $area . '_shop_search_id' );

			if ( $area_id === '__defaults__' ) {
				$area_id = us_get_option( $area . '_shop_id' );
			}

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'shop_search';
			}

			// Author Pages
		} elseif (
			$page_type == 'author' // Given page params
			OR is_author() // Current page
		) {
			$area_id = us_get_option( $area . '_author_id', '__defaults__' );

			if ( $area_id == '__defaults__' ) {
				$area_id = us_get_option( $area . '_archive_id', '' );
			}

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'author';
			}

			// Archives
		} elseif ( $page_type == 'archive' // Given page params
			OR ( // Current page
				is_archive()
				OR is_tax( $public_taxonomies )
				OR ( ! empty( $product_taxonomies )
					AND is_tax( $product_taxonomies ) )
			)
		) {
			// For product taxonomies use "Shop Page" by default
			if (
				in_array( $taxonomy_type, $product_taxonomies ) // Given page params
				OR ( ! empty( $product_taxonomies ) AND is_tax( $product_taxonomies ) ) // Current page
			) {
				$area_id = us_get_option( $area . '_shop_id' );

				// For others use "Archives" by default
			} else {
				$area_id = us_get_option( $area . '_archive_id' );
			}

			// Given page params
			if ( $taxonomy_type ) {
				$current_tax = $taxonomy_type;

				// The rest of this if /elseif / else clause - for current page
			} elseif ( is_category() ) {
				$current_tax = 'category';
			} elseif ( is_tag() ) {
				$current_tax = 'post_tag';

				/*
				 * Checking WooCommerce taxonomies,
				 * same as is_category / is_tag they require separate check
				 */
			} elseif (
				function_exists( 'is_product_category' )
				AND is_product_category()
			) {
				$current_tax = 'product_cat';
			} elseif (
				function_exists( 'is_product_tag' )
				AND is_product_tag()
			) {
				$current_tax = 'product_tag';
			} elseif ( is_tax() ) {
				$current_tax = get_query_var( 'taxonomy' );
			} elseif ( is_post_type_archive() ) {
				$current_tax = get_query_var( 'post_type' );
			} else {
				$current_tax = NULL; // default value
			}

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'archive';
				$us_page_args['taxonomy_type'] = $current_tax;
			}

			// Archives Layout template (header, content, footer), specified in terms "Edit" admin screen
			if (
				in_array( $area, array( 'header', 'content', 'footer' ) )
				AND (
					$current_taxonomy_ID = $taxonomy_ID // Given page params
					OR ( $current_taxonomy_ID = get_queried_object_id() ) // Current page
				)
			) {
				if (
					$archive_area_id = get_term_meta( $current_taxonomy_ID, 'archive_' . $area . '_id', TRUE )
					AND is_numeric( $archive_area_id )
				) {
					$area_id = $archive_area_id;
					$current_tax = NULL;

					if ( $fill_page_args ) {
						$us_page_args['taxonomy_ID'] = $current_taxonomy_ID;
					}
				}
			}

			if (
				! empty( $current_tax )
				AND ( $_area_id = us_get_option( $area . '_tax_' . $current_tax . '_id' ) ) !== NULL
				AND $_area_id !== '__defaults__'
			) {
				$area_id = $_area_id;
			}

			// Other Post Types
		} elseif (
			$post_type // Given page params
			OR ( ! empty( $public_post_types ) AND is_singular( $public_post_types ) ) // Current page
		) {

			// Given page params
			if ( $post_type ) {
				$current_post_type = $post_type;

				// The rest of this if /elseif / else clause - for current page
			} elseif ( is_attachment() ) {
				$current_post_type = 'post'; // force "post" suffix for attachments
			} elseif ( is_singular( 'us_portfolio' ) ) {
				$current_post_type = 'portfolio'; // force "portfolio" suffix to avoid migration from old theme options
			} elseif ( is_singular( 'tribe_events' ) ) {
				$current_post_type = 'tribe_events'; // force "tribe_*" suffix cause The Events Calendar always returns "page" type
			} elseif ( is_singular( 'tribe_venue' ) ) {
				$current_post_type = 'tribe_venue';
			} elseif ( is_singular( 'tribe_organizer' ) ) {
				$current_post_type = 'tribe_organizer';
			} else {
				$current_post_type = get_post_type();
			}

			$area_id = us_get_option( $area . '_' . $current_post_type . '_id', '__defaults__' );

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'post';
				$us_page_args['post_type'] = $current_post_type;
			}
		}

		// Forums archive page
		if (
			$page_type == 'forum' // Given page params
			OR ( // Current page
				is_post_type_archive( 'forum' )
				OR ( function_exists( 'bbp_is_search' ) AND bbp_is_search() )
				OR ( function_exists( 'bbp_is_search_results' ) AND bbp_is_search_results() )
			)
		) {
			$area_id = us_get_option( $area . '_forum_id' );

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'forum';
			}
		}

		// Events calendar archive page
		if ( $page_type == 'tribe_events' // Given page params
			OR is_post_type_archive( 'tribe_events' ) // Current page
		) {
			$area_id = us_get_option( $area . '_tax_tribe_events_cat_id', '__defaults__' );

			if ( $area_id == '__defaults__' ) {
				$area_id = us_get_option( $area . '_archive_id', '' );
			}

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'tribe_events';
			}
		}

		// Search Results page
		if (
			$page_type == 'search' // Given page params
			OR ( // Current page
				is_search()
				AND ! is_post_type_archive( 'product' )
				AND $postID = us_get_option( 'search_page', 'default' )
				AND is_numeric( $postID )
				AND metadata_exists( 'post', $postID, 'us_' . $area . '_id' )
			)
		) {
			$area_id = get_post_meta( $postID, 'us_' . $area . '_id', TRUE );

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'search';
			}
		}

		// Posts page
		if (
			$page_type == 'home' // Given page params
			OR ( // Current page
				is_home()
				AND $postID = us_get_page_for_posts()
				AND metadata_exists( 'post', $postID, 'us_' . $area . '_id' )
			)
		) {
			$area_id = get_post_meta( $postID, 'us_' . $area . '_id', TRUE );

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'home';
			}
		}

		// 404 page
		if (
			$page_type == '404' // Given page params
			OR ( // Current page
				is_404()
				AND $postID = us_get_option( 'page_404', 'default' )
				AND is_numeric( $postID )
				AND metadata_exists( 'post', $postID, 'us_' . $area . '_id' )
			)
		) {
			$area_id = get_post_meta( $postID, 'us_' . $area . '_id', TRUE );

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = '404';
			}
		}

		// Specific page
		if (
			$page_type == 'post' // Given page params
			OR is_singular() // Current page
		 ) {
			$current_post_ID = ( $post_ID )
				? $post_ID // Given page params
				: get_queried_object_id(); // Current page

			// Check all terms of the post and get "Pages Page Template" term custom field (any first numeric value it's enough)
			if (
				in_array( $area, array( 'header', 'content', 'footer' ) )
				AND ! empty( get_post_taxonomies( $current_post_ID ) )
			) {
				foreach ( get_post_taxonomies( $current_post_ID ) as $taxonomy_slug ) {

					$terms = get_the_terms( $current_post_ID, $taxonomy_slug );

					if ( ! empty( $terms ) AND is_array( $terms ) ) {
						foreach ( $terms as $term ) {
							if ( is_numeric( $pages_content_id = get_term_meta( $term->term_id, 'pages_'. $area .'_id', TRUE ) ) ) {
								$area_id = $pages_content_id;
								break 2;
							}
						}
					}

				}
			}

			// Check the existance of post custom field and get its value
			if ( $current_post_ID AND metadata_exists( 'post', $current_post_ID, 'us_' . $area . '_id' ) ) {

				$singular_area_id = get_post_meta( $current_post_ID, 'us_' . $area . '_id', TRUE );

				if (
					$singular_area_id == '' // corresponds to "Do not display" value for theme version 8.14 and below
					OR $singular_area_id == '0' // corresponds to "Do not display" value for theme versions above 8.14
					OR is_registered_sidebar( $singular_area_id ) // checks existance of sidebar by slug
					OR get_post_status( $singular_area_id ) != FALSE // checks existance of Reusable Block by ID (avoid cases of deleted blocks)
				) {
					$area_id = $singular_area_id;
				}
			}

			if ( $fill_page_args ) {
				$us_page_args['page_type'] = 'post';
				$us_page_args['post_ID'] = $current_post_ID;
			}
		}

		// Reset Pages defaults
		if ( $area_id == '__defaults__' ) {
			$area_id = $default_area_id;
		}

		// If you have WPML or Polylang plugins then check the translations
		if ( has_filter( 'us_tr_object_id' ) AND is_numeric( $area_id ) ) {
			if ( $area_post_type = get_post_type( $area_id ) ) {
				$area_id = (int) apply_filters( 'us_tr_object_id', $area_id, $area_post_type, TRUE );
			} else {
				$area_id = (int) apply_filters( 'us_tr_object_id', $area_id );
			}
		}

		return apply_filters( 'us_get_page_area_id', $area_id, $area, $page_args );
	}
}

if ( ! function_exists( 'us_get_current_id' ) ) {
	/**
	 * Get the ID of the current object including Grid items context
	 *
	 * TODO: Check after grid refactoring
	 * @return int Returns the object ID on success, otherwise `0` or `-1`
	 */
	function us_get_current_id() {
		$current_id = 0;
		$current_object_type = 'post';

		// Grid item ID
		global $us_grid_outputs_items, $us_grid_item_type, $us_grid_term;
		if ( $us_grid_outputs_items ) {

			// Grid term ID
			if ( $us_grid_item_type == 'term' AND $us_grid_term instanceof WP_Term ) {
				$current_id = $us_grid_term->term_id;
				$current_object_type = $us_grid_term->taxonomy;

				// Grid post ID
			} else {
				$current_id = get_the_ID();
			}

			// WooCommerce Shop page ID if set
		} elseif ( class_exists( 'woocommerce' ) AND is_shop() ) {
			$current_id = wc_get_page_id( 'shop' ); // Returns -1 on error

			// Search Results page ID if set
		} elseif ( is_search() AND ( $search_page = us_get_option( 'search_page' ) ) !== 'default' ) {
			$current_id = (int) $search_page;

			// 404 page ID if set
		} elseif ( is_404() AND ( $page_404 = us_get_option( 'page_404' ) ) !== 'default' ) {
			$current_id = (int) $page_404;

			// Posts page ID if set
		} elseif ( is_home() AND $posts_page = us_get_page_for_posts() ) {
			$current_id = (int) $posts_page;

			// Other cases
		} else {
			$current_id = get_queried_object_id();
		}

		return (int) apply_filters( 'us_tr_object_id', $current_id, $current_object_type );
	}
}

if ( ! function_exists( 'us_get_current_meta_type' ) ) {
	/**
	 * Get current meta type including Grid context
	 * Note: Comments are not supported
	 *
	 * TODO: Check after grid refactoring
	 * @return string Returns the type of metadata
	 */
	function us_get_current_meta_type() {

		// First check Grid context
		global $us_grid_outputs_items, $us_grid_item_type;
		if ( $us_grid_outputs_items ) {
			return $us_grid_item_type;
		}

		// User metadata
		if ( is_author() ) {
			return 'user';

			// Term metadata
		} elseif ( is_category() OR is_tag() OR is_tax() ) {
			return 'term';
		}

		// In all other cases return the "post" metadata type
		return 'post';
	}
}

if ( ! function_exists( 'us_get_custom_field' ) ) {
	/**
	 * Get the value of a custom field including all contexts
	 * Note: Default values are saved in the field by the ACF plugin until it is edited
	 *
	 * TODO: Check after grid refactoring
	 *
	 * @param string $name The field name
	 * @param bool $acf_format Applies the ACF "Return Format" to the returned value, if FALSE - the function returns the raw value
	 * @param integer $object_id An optional object id, if not null, return field by that id
	 * @return mixed Returns values on success, FALSE - the field does not exist, NULL - the field exists, but its value does not exist
	 */
	function us_get_custom_field( $name, $acf_format = TRUE, $object_id = NULL ) {

		// If the name is not set, then terminate the execution
		if ( empty( $name ) ) {
			return FALSE;
		}

		// Remove spaces or decode if necessary
		$name = trim( rawurldecode( $name ) );

		// Get name without double curly braces
		if ( preg_match( "/{{([^}]+)}}/", $name, $matches ) ) {
			$name = $matches[/* name */1];
		}



		// Get a value from the ACF option page, if name includes the prefix
		if ( preg_match( '/^option(\/|\|)/', $name, $matches ) ) {
			$name = substr( $name, strlen( $matches[/* prefix */0] ) );

			$current_id = 'option';
			$current_meta_type = NULL;
			$value = get_option( 'options_' . $name );

			// Otherwise get the value from meta
		} else {
			$current_id = $object_id ? $object_id : us_get_current_id();
			$current_meta_type = us_get_current_meta_type();

			// Get metadata from available sources
			// @link https://developer.wordpress.org/reference/functions/get_metadata_raw/#return
			$value = get_metadata_raw( $current_meta_type, $current_id, $name, /* single */TRUE );
		}

		return $acf_format
			? apply_filters( 'us_get_custom_field', $value, $name, $current_id, $current_meta_type )
			: $value;
	}
}

if ( ! function_exists( 'us_get_post_content' ) ) {
	/**
	 * Get post content including nested template blocks
	 *
	 * @param int|WP_Post $post The post ID or post object
	 * @param bool $get_nested_blocks Recursively get nested template blocks content
	 * @param int $current_level Current nested recursion call level (private variable)
	 * @return string|null Returns the content of the entire post if successful, otherwise null
	 */
	function us_get_post_content( $post, $get_nested_blocks = TRUE, $current_level = 1 ) {

		// Check if the current recursion level exceeds the limit
		if ( $current_level > /* limit */15 ) {
			return '';
		}

		// Get post ID
		$post_ID = $post;
		if ( $post instanceof WP_Post ) {
			$post_ID = $post->ID;
		}
		if ( ! is_numeric( $post_ID ) ) {
			return '';
		}

		// Get post object
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}
		if ( ! ( $post instanceof WP_Post ) ) {
			return '';
		}

		$post_content = $post->post_content;

		// Recursively check for template blocks present and get their content
		if ( $get_nested_blocks AND ! empty( $post_content ) ) {
			/**
			 * Get shortcode content
			 *
			 * @param array $matches The matches
			 * @return string Return post content
			 */
			$func_get_shortcode_content = function( $matches ) use ( $current_level ) {
				$tagname = $matches[/* tagname */2];
				$atts = $matches[/* shortcode atts */3];

				// Get current post id for 'full_content'
				$current_id = 0;
				if ( $tagname == 'us_post_content' AND strpos( $atts, 'type="full_content"' ) !== FALSE ) {
					$current_id = us_get_current_id(); // Note: The function is context sensitive

					// Get reusable block id
				} elseif ( $tagname == 'us_page_block' ) {
					$atts = shortcode_parse_atts( $atts );
					$current_id = (int) us_arr_path( $atts, 'id', /* default */0 );
				}

				// Get post content by id
				if ( $current_id > 0 ) {
					return us_get_post_content( $current_id, /* get_nested_blocks */TRUE, ++$current_level );
				}

				return '';
			};

			// Get content for given tagnames
			$pattern = get_shortcode_regex( array( 'us_post_content', 'us_page_block' ) );
			$post_content = preg_replace_callback( '/' . $pattern . '/', $func_get_shortcode_content, $post_content );
		}

		return $post_content;
	}
}

if ( ! function_exists( 'us_get_page_content' ) ) {
	/**
	 * Get page content
	 *
	 * @param array|int $page_args Array with arguments describing site page or page id
	 * @return string Returns the content of the entire page if successful, otherwise an empty string
	 */
	function us_get_page_content( $page_args = array() ) {

		// If the arguments are a number, then post_id is passed
		if ( is_numeric( $page_args ) ) {
			$page_args = array( 'post_ID' => (int) $page_args );
		}
		$page_content = '';

		// Note: The sequence of obtaining `$area` is important in this order titlebar, content, sidebar, footer
		foreach( array( 'titlebar', 'content', 'sidebar', 'footer' ) as $area ) {

			// Get value of specified area ID for current / given page
			$area_id = us_get_page_area_id( $area, $page_args );

			// If `titlebar` or `sidebar` are not `us_page_block` then skip get content for them
			if (
				in_array( $area, array( 'titlebar', 'sidebar' ) )
				AND get_post_type( $area_id ) !== 'us_page_block'
			) {
				continue;
			}

			/**
			 * Note: For archives from `content` where 'show results via grid elements with
			 * defaults' is indicated, skip this for now and return an empty result
			 */
			if ( $area == 'content' AND empty( $area_id ) ) {

				// Get public taxonomies EXCEPT Products
				$public_taxonomies = array_keys( us_get_taxonomies( TRUE, FALSE, 'woocommerce_exclude' ) );

				// Get Products taxonomies ONLY
				$product_taxonomies = array_keys( us_get_taxonomies( TRUE, FALSE, 'woocommerce_only' ) );

				// Archive
				if (
					us_arr_path( $page_args, 'page_type' ) == 'archive'
					OR is_archive()
					OR is_tax( $public_taxonomies )
					OR (
						! empty( $product_taxonomies )
						AND is_tax( $product_taxonomies )
					)
				) {
					continue;
				}

				// If the arguments contain `post_ID` and no page template, get the content by post_id
				if ( $post_id = us_arr_path( $page_args, 'post_ID' ) ) {
					$area_id = (int) $post_id;
				}
			}

			// Add the received content to the general output
			if ( $post_content = us_get_post_content( $area_id ) ) {
				$page_content .= shortcode_unautop( $post_content );
			}
		}

		return shortcode_unautop( $page_content );
	}
}

if ( ! function_exists( 'us_get_current_page_block_ids' ) ) {
	/**
	 * Get Reusable Blocks ids of the current page
	 *
	 * @return array
	 */
	function us_get_current_page_block_ids() {
		$ids = array();
		foreach ( array( 'footer', 'content', 'titlebar' ) as $name ) {
			if ( $area_id = us_get_page_area_id( $name ) AND is_numeric( $area_id ) ) {
				if ( has_filter( 'us_tr_object_id' ) ) {
					$translated_id = apply_filters( 'us_tr_object_id', $area_id, 'us_page_block', TRUE );
					if ( $translated_id != $area_id ) {
						$area_id = $translated_id;
					}
				}
				$ids[] = $area_id;
			}
		}

		return array_unique( $ids );
	}
}

if ( ! function_exists( 'us_get_current_page_block_content' ) ) {
	/**
	 * Get Reusable Blocks content of the current page
	 *
	 * @return string
	 */
	function us_get_current_page_block_content() {
		$output = '';
		if ( $page_block_ids = (array) us_get_current_page_block_ids() ) {
			$query_args = array(
				'nopaging' => TRUE,
				'post__in' => $page_block_ids,
				'post_type' => array( 'us_page_block', 'us_content_template' ),
				'suppress_filters' => TRUE,
			);
			foreach ( get_posts( $query_args ) as $post ) {
				if ( ! empty( $post->post_content ) ) {
					$output .= $post->post_content;
				}
			}
		}

		return $output;
	}
}

if ( ! function_exists( 'us_get_btn_styles' ) ) {
	/**
	 * Get Button Styles created on Theme Options > Button Styles
	 *
	 * @return array: id => name
	 */
	function us_get_btn_styles() {
		static $results = array();
		if ( ! empty( $results ) ) {
			return (array) $results;
		}

		$btn_styles = us_get_option( 'buttons', array() );

		if ( is_array( $btn_styles ) ) {
			foreach ( $btn_styles as $btn_style ) {
				$btn_name = trim( (string) $btn_style['name'] );
				if ( $btn_name == '' ) {
					$btn_name = us_translate( 'Style' ) . ' ' . $btn_style['id'];
				}

				$results[ $btn_style['id'] ] = esc_html( $btn_name );
			}
		}

		return $results;
	}
}

if ( ! function_exists( 'us_get_btn_class' ) ) {
	/**
	 * Return the button class based on style ID from Theme Options > Button Styles
	 *
	 * @param int $style_id
	 * @return string
	 */
	function us_get_btn_class( $style_id = 1 ) {
		static $btn_classes = array();

		if ( empty( $btn_classes ) AND $btn_styles = us_get_option( 'buttons' ) ) {
			foreach ( $btn_styles as $btn_style ) {
				$btn_class = 'us-btn-style_' . $btn_style['id'];

				if ( ! empty( $btn_style['class'] ) ) {
					$btn_class .= ' ' . esc_attr( $btn_style['class'] );
				}

				$btn_classes[ $btn_style['id'] ] = $btn_class;
			}
		}

		// If a button style is not exist use the first one
		if ( ! array_key_exists( $style_id, $btn_classes ) ) {
			$style_id = array_key_first( $btn_classes );
		}

		if ( ! empty( $btn_classes ) ) {
			return $btn_classes[ $style_id ];
		} else {
			return 'us-btn-style_0'; // placeholder class if button styles are not exist
		}
	}
}

if ( ! function_exists( 'us_get_image_sizes_list' ) ) {
	/**
	 * Get image size values for selection
	 *
	 * @param array [$size_names] List of size names
	 * @return array
	 */
	function us_get_image_sizes_list( $include_full = TRUE ) {
		if ( ! is_admin() ) {
			return array();
		}

		if ( $include_full ) {
			$image_sizes = array( 'full' => us_translate( 'Full Size' ) );
		} else {
			$image_sizes = array();
		}

		foreach ( get_intermediate_image_sizes() as $size_name ) {

			// Get size params
			$size = us_get_image_size_params( $size_name );

			// Do not include sizes with both zero values
			if ( (int) $size['width'] == 0 AND (int) $size['height'] == 0 ) {
				continue;
			}

			$size_title = ( ( (int) $size['width'] == 0 ) ? __( 'any', 'us' ) : $size['width'] );
			$size_title .= '×';
			$size_title .= ( (int) $size['height'] == 0 ) ? __( 'any', 'us' ) : $size['height'];
			if ( $size['crop'] ) {
				$size_title .= ' ' . __( 'cropped', 'us' );
			}

			$size_title = strip_tags( $size_title );

			if ( ! in_array( $size_title, $image_sizes ) ) {
				$image_sizes[ $size_name ] = $size_title;
			}
		}

		return apply_filters( 'us_image_sizes_select_values', $image_sizes );
	}
}

if ( ! function_exists( 'us_generate_link_atts' ) ) {

	/**
	 * Generate attributes for <a> tag based on link options
	 *
	 * @param array|string $atts The element atts or link value
	 * @param string $additional_data [optional] The specific data `array( 'label' => '', 'term_id' => '', 'img_id' => '' )`
	 * @param integer $object_id An optional object id, used in the us_get_custom_field
	 * @return array Returns an array of attributes for the link
	 */
	function us_generate_link_atts( $link = '', $additional_data = array(), $object_id = NULL ) {
		if ( is_string( $link ) ) {
			$link_atts = json_decode( rawurldecode( $link ), /* as array */TRUE );
		} else {
			$link_atts = $link;
		}

		if ( ! is_array( $link_atts ) ) {
			return array();
		}

		// Get link type
		if ( ! empty( $link_atts['type'] ) ) {
			$link_type = $link_atts['type'];
			unset( $link_atts['type'] );
		} else {
			$link_type = 'url';
		}

		// TYPE: Post Link
		if ( $link_type == 'post' OR $link_type == 'popup_post' ) {

			global $us_grid_item_type, $us_grid_term;
			if ( $us_grid_item_type == 'term' ) {
				$link_atts['url'] = get_term_link( $us_grid_term );

				// Reset the value in case of error
				if ( is_wp_error( $link_atts['url'] ) ) {
					$link_atts['url'] = '';
				}
			} else {
				$link_atts['url'] = (string) apply_filters( 'the_permalink', get_permalink() );
			}

			// TYPE: Post Comments Link
		} elseif ( $link_type == 'post_comments' ) {

			if ( get_post_type() == 'product' ) {
				$link_atts['url'] = apply_filters( 'the_permalink', get_permalink() ) . '#reviews';
			} else {
				$link_atts['url'] = get_comments_link();
			}

			// TYPE: Taxonomy Archive Link
		} elseif ( $link_type == 'archive' ) {

			// Check the provided term ID
			if ( ! empty( $additional_data['term_id'] ) ) {
				$link_atts['url'] = get_term_link( (int) $additional_data['term_id'] );

				// Reset the value in case of error
				if ( is_wp_error( $link_atts['url'] ) ) {
					$link_atts['url'] = '';
				}
			} else {
				$link_atts['url'] = '';
			}

			// TYPE: Clickable value (email, phone, website)
		} elseif ( $link_type == 'elm_value' ) {

			// Check the provided text
			if ( ! empty( $additional_data['label'] ) ) {
				if ( is_email( $additional_data['label'] ) ) {
					$link_atts['url'] = 'mailto:' . $additional_data['label'];
				} elseif ( strpos( $additional_data['label'], '.' ) === FALSE ) {
					$link_atts['url'] = 'tel:' . $additional_data['label'];
				} else {
					$link_atts['url'] = esc_url( $additional_data['label'] );
				}
			} else {
				$link_atts['url'] = '';
			}

			// TYPE: Open image in popup
		} elseif ( $link_type == 'popup_image' ) {
			if ( ! us_amp() ) {
				$link_atts['ref'] = 'magnificPopup';
			}

			// Use the provided ID to get the image url
			if ( ! empty( $additional_data['img_id'] ) ) {
				if ( get_post_type( $additional_data['img_id'] ) == 'attachment' ) {
					$full_image_url = wp_get_attachment_image_url( $additional_data['img_id'], 'full' );
				} else {
					$full_image_url = get_the_post_thumbnail_url( $additional_data['img_id'], 'full' );
				}
			}

			// Use the image url if exists
			if ( ! empty( $full_image_url ) ) {
				$link_atts['url'] = $full_image_url;

				// .. if not use the placeholder
			} else {
				$link_atts['url'] = us_get_img_placeholder( 'full', TRUE );
			}

			// TYPE: Author Page
		} elseif ( $link_type == 'author_page' ) {

			// Check the user ID from grid
			global $us_grid_user_ID;

			$user_id = $us_grid_user_ID ?? get_the_author_meta( 'ID' );

			$link_atts['url'] = get_author_posts_url( $user_id );

			// TYPE: Author Website
		} elseif ( $link_type == 'author_website' ) {

			// Check the user ID from grid
			global $us_grid_user_ID;

			$link_atts['url'] = $us_grid_user_ID
				? get_the_author_meta( 'url', $us_grid_user_ID )
				: get_the_author_meta( 'url' );

			// TYPE: Home page
		} elseif ( $link_type == 'homepage' ) {
			$link_atts['url'] = get_bloginfo( 'url' );

			// TYPE: Custom field
		} elseif ( $link_type == 'custom_field' AND ! empty( $link_atts['custom_field'] ) ) {
			$meta_value = us_get_custom_field( $link_atts['custom_field'], /* acf_format */FALSE, $object_id );

			// Transform JSON value into array
			if ( is_string( $meta_value ) AND strpos( rawurldecode( $meta_value ), '{' ) === 0 ) {
				$meta_value = json_decode( rawurldecode( $meta_value ), /* as array */TRUE );
			}

			if ( is_array( $meta_value ) ) {
				$link_atts += $meta_value;
			} else {
				$link_atts['url'] = (string) $meta_value;
			}

			// Check if the value is a valid email
			if ( ! empty( $link_atts['url'] ) AND is_email( $link_atts['url'] ) ) {
				$link_atts['url'] = 'mailto:' . $link_atts['url'];
			}

			unset( $link_atts['custom_field'] );
		}

		// Decode all attributes for better comparison below
		$link_atts = array_map( 'rawurldecode', $link_atts );

		// Filters the URL with predefined conditions
		if ( ! empty( $link_atts['url'] ) ) {
			$link_atts['url'] = us_replace_dynamic_value( $link_atts['url'] );

			// Transform numeric URL into correct URL
			if ( is_numeric( $link_atts['url'] ) ) {

				// First check if the attachment file exists (used in ACF "File" type)
				if ( $_file_url = wp_get_attachment_url( $link_atts['url'] ) ) {
					$link_atts['url'] = $_file_url;

					// then check if the post with this ID exists (used in ACF "Page link" type)
				} elseif ( $_post_url = get_permalink( $link_atts['url'] ) ) {
					$link_atts['url'] = $_post_url;

					// in other cases reset the value
				} else {
					$link_atts['url'] = '';
				}
			}

			// Replace [lang] shortcode with the current language code
			if ( strpos( $link_atts['url'], '[lang]' ) !== FALSE ) {
				$_current_lang = apply_filters( 'us_tr_current_language', NULL );
				$_default_lang = apply_filters( 'us_tr_default_language', NULL );
				if ( $_current_lang != $_default_lang ) {
					$replacer = $_current_lang;
				} else {
					$replacer = '';
				}
				$link_atts['url'] = str_replace( '[lang]', $replacer, $link_atts['url'] );
			}

			// Move "url" into "href"
			$link_atts['href'] = $link_atts['url'];
		}

		// Remove the "url" attribute
		if ( isset( $link_atts['url'] ) ) {
			unset( $link_atts['url'] );
		}

		return (array) apply_filters( 'us_generate_link_atts', $link_atts, $link, $additional_data );
	}
}

if ( ! function_exists( 'us_get_smart_date' ) ) {
	/**
	 * Return date and time in Human readable format
	 *
	 * @param int $from Unix timestamp from which the difference begins.
	 * @param int $to Optional. Unix timestamp to end the time difference. Default becomes current_time() if not set.
	 * @return string Human readable date and time.
	 */
	function us_get_smart_date( $from, $to = '' ) {
		if ( empty( $to ) ) {
			$to = current_time( 'U' );
		}

		$diff = (int) abs( $to - $from );

		// Get time format from site general settings
		$site_time_format = get_option( 'time_format', 'g:i a' );

		$time_string = date( $site_time_format, $from );
		$day = (int) date( 'jmY', $from );
		$current_day = (int) date( 'jmY', $to );
		$yesterday = (int) date( 'jmY', strtotime( 'yesterday', $to ) );
		$year = (int) date( 'Y', $from );
		$current_year = (int) date( 'Y', $to );

		if ( $diff < HOUR_IN_SECONDS ) {
			$mins = round( $diff / MINUTE_IN_SECONDS );
			if ( $mins <= 1 ) {
				$mins = 1;
			}

			// 1-59 minutes ago
			$mins_string = sprintf( us_translate_n( '%s min', '%s mins', $mins ), $mins );
			$result = sprintf( us_translate( '%s ago' ), $mins_string );
		} elseif ( $diff <= ( HOUR_IN_SECONDS * 4 ) ) {
			$hours = round( $diff / HOUR_IN_SECONDS );
			if ( $hours <= 1 ) {
				$hours = 1;
			}

			// 1-4 hours ago
			$hours_string = sprintf( us_translate_n( '%s hour', '%s hours', $hours ), $hours );
			$result = sprintf( us_translate( '%s ago' ), $hours_string );
		} elseif ( $current_day == $day ) {

			// Today at 9:30
			$result = sprintf( us_translate( '%1$s at %2$s' ), us_translate( 'Today' ), $time_string );
		} elseif ( $yesterday == $day ) {

			// Yesterday at 9:30
			$result = sprintf( us_translate( '%1$s at %2$s' ), __( 'Yesterday', 'us' ), $time_string );
		} elseif ( $current_year == $year ) {

			// 23 Jan at 12:30
			$result = sprintf( us_translate( '%1$s at %2$s' ), date_i18n( 'j M', $from ), $time_string );
		} else {

			// Use format from site general settings
			$result = date_i18n( get_option( 'date_format' ), $from );
		}

		return $result;
	}
}

if ( ! function_exists( 'us_get_posts_titles_for' ) ) {
	/**
	 * Get list of posts titles by a certain post types
	 * @param array $post_types Post types to get
	 * @param bool $force_no_cache Allow using cache (use FALSE to force not-cached version)
	 * @return array
	 */
	function us_get_all_posts_titles_for( $post_types, $orderby = 'title', $force_no_cache = TRUE ) {
		if ( empty( $post_types ) OR ! is_array( $post_types ) ) {
			return array();
		}

		static $results = array();
		$post_types = array_map( 'trim', $post_types );

		$is_empty_result = FALSE;
		foreach ( $post_types as $post_type ) {
			if ( ! isset( $results[ $post_type ] ) ) {
				$results[ $post_type ] = array();
				$is_empty_result = TRUE;
			}
		}

		if ( $is_empty_result ) {
			global $wpdb;
			$query = "
				SELECT
					ID, post_title, post_status, post_type
				FROM {$wpdb->posts}
				WHERE
					post_type IN('" . implode( "','", $post_types ) . "')
					AND post_status IN('publish', 'private')
			";
			if ( ! empty( $orderby ) AND $orderby == 'title' ) {
				$query .= " ORDER BY post_title ASC";
			}
			$posts = array();
			foreach ( $wpdb->get_results( $query ) as $post ) {
				$posts[ $post->ID ] = $post;
			}
			// Filtering by language
			if ( apply_filters( 'us_tr_selected_lang_page', /* Default value */ FALSE ) ) {
				$posts = apply_filters( 'us_filter_posts_by_language', $posts );
			}
			foreach ( $posts as $post ) {
				$results[ $post->post_type ][ $post->ID ] = ( $post->post_title )
					? $post->post_title
					: us_translate( '(no title)' );
			}
		}

		return $results;
	}

	/**
	 * Get list of posts titles by a certain post type
	 * @param string $post_type Post type to get
	 * @param bool $force_no_cache Allow using cache (use FALSE to force not-cached version)
	 * @return array
	 */
	function us_get_posts_titles_for( $post_type, $orderby = 'title', $force_no_cache = TRUE ) {
		$results = (array) us_get_all_posts_titles_for( array( $post_type ), $orderby, $force_no_cache );

		return us_arr_path( $results, $post_type );
	}
}

if ( ! class_exists( 'Us_Vc_Base' ) ) {
	// some functions from Vc_Base, without extending from Vc_Base
	class Us_Vc_Base {

		/**
		 * Initializes the object.
		 */
		public function init() {
			add_action( 'wp_head', array( $this, 'addFrontCss' ), 1000 );
		}

		/**
		 * Determines if vc active.
		 *
		 * @return bool True if vc active, False otherwise.
		 */
		public function is_vc_active() {
			return class_exists( 'Vc_Manager' );
		}

		/**
		 * Add css styles for current page and elements design options added w\ editor.
		 */
		public function addFrontCss() {
			$this->addPageCustomCss();
			$this->addShortcodesCustomCss();
		}

		/**
		 * Add custom styles to the page
		 * Note: This method outputs custom styles for both the page and the Reusable Blocks in the content,
		 * which can lead to a lot of calls.
		 *
		 * @param mixed $id Unique post id
		 * TODO: Update method after implementing new inference logic from #2457.
		 */
		public function addPageCustomCss( $id = NULL ) {
			$ids = array();
			// If the ID is explicitly specified, then add it to get the styles if any
			// (the ID is explicitly indicated to connect the page components, Reusable Blocks, Page Templates etc)
			if ( is_numeric( $id ) ) {
				$ids[] = $id;

				// For pages, get the ID from the queried object
			} elseif ( is_front_page() OR is_home() OR is_singular() ) {
				$ids[] = get_queried_object_id();
			}

			// For search page
			if ( is_search() AND $search_page = us_get_option( 'search_page' ) ) {
				$ids[] = (int)$search_page;
			}

			global $us_page_block_ids, $us_output_custom_css_ids;
			if ( ! empty( $us_page_block_ids ) ) {
				$ids = array_merge( $ids, $us_page_block_ids );
			}
			if ( ! is_array( $us_output_custom_css_ids ) ) {
				$us_output_custom_css_ids = array();
			}

			// Get a template on the "Checkout → Order received page"
			if ( us_is_order_received_page() AND $order_template_id = us_get_option( 'content_order_id' ) ) {
				$ids[] = $order_template_id;
			}

			// Get only unique ids
			$ids = array_unique( $ids );

			// Get custom styles by available identifiers
			foreach ( $ids as $id ) {
				if ( $this->is_vc_active() AND 'true' === vc_get_param( 'preview' ) ) {
					$latest_revision = wp_get_post_revisions( $id );
					if ( ! empty( $latest_revision ) ) {
						$array_values = array_values( $latest_revision );
						$id = $array_values[0]->ID;
					}
				}
				/*
				* Check if the css has not been displayed yet then output
				* Note: Re-call can be for Reusable Blocks
				*/
				if ( in_array( $id, $us_output_custom_css_ids ) ) {
					continue;
				}
				// Get and if available output custom styles
				foreach ( array( '_wpb_post_custom_css', usb_get_key_custom_css() ) as $meta_key ) {
					if ( ! empty ( $meta_key ) AND $post_custom_css = get_post_meta( $id, $meta_key, TRUE ) ) {
						$us_output_custom_css_ids[] = $id;
						echo sprintf( '<style data-type="us_custom-css">%s</style>', us_minify_css( $post_custom_css ) );
						break;
					}
				}
			}
		}

		public function addShortcodesCustomCss( $id = NULL ) {
			if ( ! is_singular() AND ! $id ) {
				return;
			}
			if ( ! $id ) {
				$id = get_the_ID();
			}

			if ( $id ) {
				if ( $this->is_vc_active() AND 'true' === vc_get_param( 'preview' ) ) {
					$latest_revision = wp_get_post_revisions( $id );
					if ( ! empty( $latest_revision ) ) {
						$array_values = array_values( $latest_revision );
						$id = $array_values[0]->ID;
					}
				}
				if ( $shortcodes_custom_css = get_post_meta( $id, '_wpb_shortcodes_custom_css', TRUE ) ) {
					echo '<style data-type="vc_shortcodes-custom-css">';
					echo us_minify_css( $shortcodes_custom_css );
					echo '</style>';
				}
			}
		}
	}
}

if ( ! function_exists( 'us_get_img_placeholder' ) ) {
	/**
	 * Returns image placeholder
	 *
	 * @param string $size The image size
	 * @param string $src_only if TRUE returns file URL, if FALSE returns string with <img>
	 * @return string
	 */
	function us_get_img_placeholder( $size = 'full', $src_only = FALSE ) {

		// Default placeholder
		$img_src = US_CORE_URI . '/assets/images/placeholder.svg';

		$size_array = us_get_image_size_params( $size );
		$img_atts = array(
			'class' => 'g-placeholder',
			'src' => $img_src,
			'width' => $size_array['width'],
			'height' => $size_array['height'],
			'alt' => '',
		);
		$img_html = '<img' . us_implode_atts( $img_atts ) . '>';

		// If Images Placeholder is set, use its attachment ID
		if (
			$img_id = us_get_option( 'img_placeholder', '' )
			AND is_numeric( $img_id )
			AND $img_src = wp_get_attachment_image_url( $img_id, $size )
		) {
			$img_html = wp_get_attachment_image( $img_id, $size, TRUE, array( 'class' => 'g-placeholder' ) );
		}

		if ( $src_only ) {
			return $img_src;
		} else {
			return $img_html;
		}
	}
}

if ( ! function_exists( 'us_sanitize_font_family' ) ) {
	/**
	 * Remove any characters other than letters and numbers from font family
	 *
	 * @param $font_family
	 * @return string
	 */
	function us_sanitize_font_family( $font_family ) {
		$font_family = strip_tags( $font_family );
		$font_family = str_replace( '&nbsp;', '', $font_family );
		$font_family = preg_replace( array( '/[^0-9a-zA-Z\-\_]/', '/\s+/' ), ' ', $font_family );
		$font_family = trim( $font_family );

		return $font_family;
	}
}

if ( ! function_exists( 'us_output_design_css' ) ) {
	/**
	 * Prepares all custom styles for page output
	 * TODO: Implement get styles based on `us_get_page_content()`
	 *
	 * @return string
	 */
	function us_output_design_css( $custom_posts = [] ) {
		global $wp_query;

		// Load css for specific page
		$posts = is_404() ? array() : $wp_query->posts;

		// Controlling the output of styles on the page, if the filter
		// returns FALSE then the output will be canceled.
		if ( ! apply_filters( 'us_is_output_design_css_for_content', TRUE ) ) {
			$posts = array();
		}

		if ( ! empty( $custom_posts ) AND is_array( $custom_posts ) ) {
			$posts = array_merge( $posts, $custom_posts );
		}

		$query_posts_id = array();
		foreach ( $posts as $post ) {
			$query_posts_id[] = $post->ID;
		}

		// 404 Page Not Found
		if ( is_404() AND $page_404_id = us_get_option( 'page_404' ) ) {
			$page_404_id = has_filter( 'us_tr_object_id' )
				? (int) apply_filters( 'us_tr_object_id', $page_404_id, 'page', TRUE )
				: $page_404_id;
			if ( $page_404_id AND $page_404 = get_post( $page_404_id ) ) {
				$posts[] = $page_404;
			}
		}

		// Maintenance Page
		if ( us_get_option( 'maintenance_mode' ) AND $maintenance_page_id = us_get_option( 'maintenance_page' ) ) {
			$maintenance_page_id = has_filter( 'us_tr_object_id' )
				? (int) apply_filters( 'us_tr_object_id', $maintenance_page_id, 'page', TRUE )
				: $maintenance_page_id;
			if ( $maintenance_page = get_post( $maintenance_page_id ) ) {
				$posts[] = $maintenance_page;
			}
		}

		// Shop page
		if (
			function_exists( 'is_shop' )
			AND is_shop()
			AND $shop_page_ID = get_option( 'woocommerce_shop_page_id' )
		) {
			$shop_page_ID = has_filter( 'us_tr_object_id' )
				? (int) apply_filters( 'us_tr_object_id', $shop_page_ID, 'page', TRUE )
				: $shop_page_ID;
			if ( $shop_page = get_post( $shop_page_ID ) ) {
				$posts[] = $shop_page;
			}
		}

		// List of post IDs
		$include_ids = array();

		foreach ( array( 'header', 'titlebar', 'sidebar', 'content', 'footer' ) as $area ) {
			if ( $area_id = us_get_page_area_id( $area ) AND $post = get_post( (int) $area_id ) ) {

				// Specific manipulations with Headers
				if ( $area === 'header' ) {
					$header_options = json_decode( $post->post_content, TRUE );
					$data = us_arr_path( $header_options, 'data', array() );
					foreach ( $data as $key => $item ) {

						// Check Menu element, if it uses Reusable Block as menu item
						if ( strpos( $key, 'menu' ) === 0 AND ! empty( $item['source'] ) ) {
							$menu = wp_get_nav_menu_object( $item['source'] );
							if ( $menu === FALSE ) {
								continue;
							}
							$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => FALSE ) );
							foreach ( $menu_items as $menu_item ) {
								if ( $menu_item->object === 'us_page_block' ) {
									$posts[] = get_post( (int) $menu_item->object_id );
								}
							}
							unset( $menu, $menu_items );

							// Get Reusable Block IDs from Popup element
						} elseif ( strpos( $key, 'popup' ) === 0 AND ! empty( $item['use_page_block'] ) ) {
							$include_ids[] = (int) $item['use_page_block'];
						}
					}
				} else {
					$posts[] = $post;
				}
			}
		}

		// The Event Calendar plugin uses a non-standard way of receiving data, so we get the id from the request object
		if (
			is_singular( array( 'tribe_events', 'tribe_venue', 'tribe_organizer' ) )
			AND get_queried_object() instanceof WP_Post
		) {
			$include_ids[] = get_queried_object_id();
		}

		// If we are on the search results page, add the page ID from Theme Options
		if ( $wp_query->is_search AND $search_page_ID = us_get_option( 'search_page' ) ) {
			$include_ids[] = (int) $search_page_ID;
		}

		// If we are on the "Order received" page, add the Page Template ID from Theme Options
		if (
			us_is_order_received_page()
			AND $order_template_ID = us_get_option( 'content_order_id' )
		) {
			$include_ids[] = (int) $order_template_ID;
		}

		// Get a custom page to display posts
		if ( is_home() AND $posts_page_ID = us_get_page_for_posts() ) {
			$include_ids[] = (int) $posts_page_ID;
		}

		// Get Reusable Block IDs from popups
		if ( apply_filters( 'us_is_output_design_css_for_content', TRUE ) ) {
			foreach ( $posts as $post ) {
				if (
					strpos( $post->post_content, 'use_page_block="' )
					AND preg_match_all( '/\suse_page_block="(\d+)"/', $post->post_content, $matches )
				) {
					$include_ids = array_merge( $include_ids, $matches[1] );
				}
			}
		}

		$include_ids = array_unique( $include_ids );

		// The include posts to $posts
		if ( ! empty( $include_ids ) ) {
			$include_posts = get_posts(
				array(
					'include' => array_map( 'intval', $include_ids ),
					'post_type' => array_keys( get_post_types() ),
				)
			);
			$posts = array_merge( $include_posts, $posts );
		}

		// List of already parsed Reusable Blocks to prevent excessive load to server
		$walked_post_ids = array();

		/**
		 * Recursively retrieving all posts assigned to `no_items_page_block`
		 * @param WP_Post $post
		 */
		$func_get_no_items_page_block = function ( $post, $key, $max_level = 3, $current_level = 1 ) use ( &$posts, &$walked_post_ids, &$func_get_no_items_page_block ) {
			if ( $current_level > $max_level ) {
				return;
			}
			$walked_post_ids[] = $post->ID;
			if (
				strpos( $post->post_content, 'no_items_page_block="' ) !== FALSE
				AND preg_match_all( '/no_items_page_block="(\d+)"/', $post->post_content, $matches )
			) {
				$query_args = array(
					'include' => $matches[1], // match ids
					'post_type' => array_keys( get_post_types() ),
				);
				foreach ( get_posts( $query_args ) as $page_block ) {
					if ( in_array( $page_block->ID, $walked_post_ids ) ) {
						return;
					}
					$posts[] = $page_block;
					$func_get_no_items_page_block( $page_block, NULL, $max_level, ++$current_level );
				}
			}
		};
		array_walk( $posts, $func_get_no_items_page_block );

		// Get Templatera IDs and add templates to $posts
		if ( class_exists( 'VcTemplateManager' ) ) {
			$templatera_ids = array();
			foreach ( $posts as $post ) {
				if (
					! empty( $post->post_content )
					AND preg_match_all( '/\[templatera([^\]]+)\]/', $post->post_content, $matches )
				) {
					foreach ( us_arr_path( $matches, '1', array() ) as $atts ) {
						if ( empty( $atts ) ) {
							continue;
						}
						$atts = shortcode_parse_atts( $atts );
						if ( $id = us_arr_path( $atts, 'id' ) ) {
							$templatera_ids[] = $id;
						}
					}
				}
			}
			if ( ! empty( $templatera_ids ) ) {
				$include_posts = get_posts(
					array(
						'include' => array_map( 'intval', $templatera_ids ),
						'post_type' => 'templatera',
						'posts_per_page' => - 1,
					)
				);
				$posts = array_merge( $include_posts, $posts );
			}
		}

		/**
		 * Collect all Reusable Blocks into one variable
		 * @param WP_Post $post
		 */
		$func_acc_posts = function ( $post ) use ( &$posts ) {
			if ( $post instanceof WP_Post ) {
				$posts[ $post->ID ] = $post;
			}
		};

		foreach ( $posts as $post ) {
			if ( $post instanceof WP_Post AND strpos( $post->post_content, 'us_page_block' ) !== FALSE ) {
				us_get_recursive_parse_page_block( $post, $func_acc_posts );
			}
		}

		// Get reusable blocks selected for popup
		$use_page_block_ids = array();
		foreach( $posts as $post ) {
			if ( strpos( $post->post_content, 'use_page_block' ) === FALSE ) {
				continue;
			}
			if ( preg_match_all( '/use_page_block="(\d+)"/ ', $post->post_content, $matches ) ) {
				foreach ( $matches[ /* use_page_block ids */1 ] as $item_id ) {
					if ( ! isset( $posts[ $item_id ] ) ) {
						$use_page_block_ids[] = $item_id;
					}
				}
		 	}
		}
		if ( ! empty( $use_page_block_ids ) ) {
			$args = array(
				'include' => array_unique( $use_page_block_ids ),
				'post_type' => array_keys( get_post_types() ),
			);
			foreach ( get_posts( $args ) as $post ) {
				$func_acc_posts( $post );
			}
		}

		// Get custom CSS from shortcodes
		$jsoncss_collection = array();
		foreach ( $posts as $post ) {

			// Do not display internal styles for archives page
			if ( in_array( $post->ID, $query_posts_id ) AND count( $query_posts_id ) > 1 ) {
				continue;
			}

			$jsoncss_data = get_post_meta( $post->ID, '_us_jsoncss_data', TRUE );
			if ( $jsoncss_data === '' AND function_exists( 'us_update_postmeta_for_custom_css' ) ) {
				$jsoncss_data = us_update_postmeta_for_custom_css( $post );
			}
			if ( ! empty( $jsoncss_data ) AND is_array( $jsoncss_data ) ) {
				foreach ( $jsoncss_data as $jsoncss ) {
					us_add_jsoncss_to_collection( $jsoncss, $jsoncss_collection );
				}
			}
		}

		// Apply filters
		$jsoncss_collection = apply_filters( 'us_output_design_css', $jsoncss_collection, $posts );

		// Generate CSS code and output data
		if ( $custom_css = us_jsoncss_compile( $jsoncss_collection ) ) {
			echo sprintf( '<style id="us-design-options-css">%s</style>', $custom_css );
		}
	}

	add_action( 'us_before_closing_head_tag', 'us_output_design_css', 10 );
}

if ( ! function_exists( 'us_add_jsoncss_to_collection' ) ) {
	/**
	 * Adds jsoncss styles to a jsoncss_collection
	 *
	 * @param string $jsoncss The jsoncss
	 * @param array $jsoncss_collection The jsoncss collection
	 * @return string Unique classname
	 */
	function us_add_jsoncss_to_collection( $jsoncss, &$jsoncss_collection ) {
		$unique_class_name = '';
		if ( ! empty( $jsoncss ) AND is_string( $jsoncss ) ) {
			$unique_class_name = us_get_unique_css_class_name( $jsoncss );
			$jsoncss = rawurldecode( $jsoncss );
			if ( $jsoncss AND $jsoncss = json_decode( $jsoncss, TRUE ) ) {
				foreach ( (array) us_get_responsive_states( /* Only keys */ TRUE ) as $state ) {
					if ( $css_options = us_arr_path( $jsoncss, $state, FALSE ) ) {
						if (
							! empty( $jsoncss_collection[ $state ] )
							AND in_array( $unique_class_name, $jsoncss_collection[ $state ] )
						) {
							continue;
						}
						$css_options = apply_filters( 'us_output_design_css_options', $css_options, $state );
						$jsoncss_collection[ $state ][ $unique_class_name ] = $css_options;
					}
				}
			};
		}

		return $unique_class_name;
	}
}

if ( ! function_exists( 'us_filter_design_css_colors' ) ) {
	/**
	 * Replace variable colors with values
	 *
	 * @param array $css_options
	 * @return array
	 */
	function us_filter_design_css_colors( $css_options ) {
		// key => with_gradient
		$keys = array(
			'color' => FALSE,
			'background' => TRUE,
			'background-color' => TRUE,
			'border-color' => FALSE,
			'text-shadow-color' => FALSE,
			'box-shadow-color' => FALSE,
		);
		foreach ( $keys as $key => $with_gradient ) {
			if ( ! empty( $css_options[ $key ] ) ) {
				$css_options[ $key ] = us_get_color( $css_options[ $key ], $with_gradient );
			}
		}

		return $css_options;
	}

	add_filter( 'us_output_design_css_options', 'us_filter_design_css_colors', 1, 1 );
}

if ( ! function_exists( 'us_get_unique_css_class_name' ) ) {
	/**
	 * Get unique css class name.
	 *
	 * @param string $value The value to get the hash.
	 * @param string $class_name The prefix for css class name.
	 * @return string Returns a unique class based on value and prefix.
	 */
	function us_get_unique_css_class_name( $value, $prefix = 'us_custom' ) {
		if ( ! empty( $value ) AND ! empty( $prefix ) ) {
			return $prefix . '_' . hash( 'crc32b', $value );
		}

		return '';
	}
}

if ( ! function_exists( 'us_get_recursive_parse_page_block' ) ) {
	/**
	 * Recursive parse page_block
	 *
	 * @param WP_Post $post The post
	 * @param function $callback The callback `function( $post, $atts ){}`
	 * @param integer $max_level The max level
	 * @param integer $current_level The current level
	 * @return array Reusable Block ids
	 */
	function us_get_recursive_parse_page_block( $post, $callback = NULL, $max_level = 15, $current_level = 1 ) {
		$output = array();
		if ( $current_level > $max_level ) {
			return $output;
		}
		global $us_recursive_parse_page_blocks;
		if ( ! is_array( $us_recursive_parse_page_blocks ) ) {
			$us_recursive_parse_page_blocks = array();
		}
		if ( $post instanceof WP_Post AND ! empty( $post->post_content ) ) {
			$page_block_pattern = '/' . get_shortcode_regex( array( 'us_page_block' ) ) . '/';
			if ( preg_match_all( $page_block_pattern, $post->post_content, $matches ) ) {
				foreach ( us_arr_path( $matches, '3', array() ) as $atts ) {
					$atts = shortcode_parse_atts( $atts );
					$output[] = $id = us_arr_path( $atts, 'id' );
					if ( ! in_array( $id, array_keys( $us_recursive_parse_page_blocks ) ) ) {
						$us_recursive_parse_page_blocks[ $id ] = get_post( $id );
					}
					$next_post = $us_recursive_parse_page_blocks[ $id ];
					if ( is_callable( $callback ) ) {
						call_user_func( $callback, $next_post, $atts );
					}
					if ( $next_post instanceof WP_Post AND strrpos( $next_post->post_content, 'us_page_block' ) !== FALSE ) {
						$output = array_merge( $output, us_get_recursive_parse_page_block( $next_post, $callback, $max_level, ++ $current_level ) );
					}
				}
			}
		}

		return (array) $output;
	}
}

if ( ! function_exists( 'us_find_element_in_post_page_blocks' ) ) {
	/**
	 * Check for shortcode in all nested Reusable Blocks
	 *
	 * @param inteer $post_id The post identifier
	 * @param string $find_value The find value
	 * @return boolean
	 */
	function us_find_element_in_post_page_blocks( $post_id, $find_value = '' ) {
		$result = FALSE;
		if (
			! empty( $find_value )
			AND ! empty( $post_id )
			AND $post = get_post( $post_id )
			AND function_exists( 'us_get_recursive_parse_page_block' )
		) {
			us_get_recursive_parse_page_block(
				$post, function ( $post ) use ( &$result, $find_value ) {
				if ( $result ) {
					return;
				}
				if ( $post instanceof WP_Post ) {
					$result = stripos( $post->post_content, $find_value ) !== FALSE;
				}
			}
			);
		}

		return $result;
	}
}

if ( ! function_exists( 'us_get_responsive_states' ) ) {
	/**
	 * Get responsive states
	 *
	 * @param bool $only_keys Enable only keys in the result
	 * @return array( slug => array( title, breakpoint ) )
	 */
	function us_get_responsive_states( $only_keys = FALSE ) {
		$laptops_breakpoint = (int) us_get_option( 'laptops_breakpoint' );
		$tablets_breakpoint = (int) us_get_option( 'tablets_breakpoint' );
		$mobiles_breakpoint = (int) us_get_option( 'mobiles_breakpoint' );

		// Note: The order of all keys is important, it affects the order of output in different parts of the project!
		$result = array(
			'default' => array(
				'min_width' => $laptops_breakpoint + 1,
				'media_query' => '(min-width:' . ( $laptops_breakpoint + 1 ) . 'px)',
				'title' => __( 'Desktops', 'us' ) . ' <i>≥' . ( $laptops_breakpoint + 1 ) . 'px</i>',
			),
			'laptops' => array(
				'max_width' => $laptops_breakpoint,
				'min_width' => $tablets_breakpoint + 1,
				'media_query' => '(min-width:' . ( $tablets_breakpoint + 1 ) . 'px) and (max-width:' . $laptops_breakpoint . 'px)',
				'title' => __( 'Laptops', 'us' ) . ' <i>' . ( $tablets_breakpoint + 1 )  . '-' . $laptops_breakpoint . 'px</i>',
			),
			'tablets' => array(
				'max_width' => $tablets_breakpoint,
				'min_width' => $mobiles_breakpoint + 1,
				'media_query' => '(min-width:' . ( $mobiles_breakpoint + 1 ) . 'px) and (max-width:' . $tablets_breakpoint . 'px)',
				'title' => __( 'Tablets', 'us' ) . ' <i>' . ( $mobiles_breakpoint + 1 ) . '-' . $tablets_breakpoint . 'px</i>',
			),
			'mobiles' => array(
				'max_width' => $mobiles_breakpoint,
				'min_width' => 300,
				'media_query' => '(max-width:' . $mobiles_breakpoint . 'px)',
				'title' => __( 'Mobiles', 'us' ) . ' <i>≤' . $mobiles_breakpoint . 'px</i>',
			),
		);

		return $only_keys ? array_keys( $result ) : $result;
	}
}

if ( ! function_exists( 'us_get_responsive_values' ) ) {
	/**
	 * Get an array of responsive values
	 *
	 * @param mixed $value The value
	 * @return array Returns an array of responsive values
	 */
	function us_get_responsive_values( $value ) {
		$result = array();
		if (
			is_string( $value )
			AND $value = json_decode( rawurldecode( $value ), /* return as array */TRUE )
			AND is_array( $value )
		) {
			$result = array();
			foreach ( (array) us_get_responsive_states( /* only_keys */TRUE ) as $state ) {
				if ( isset( $value[ $state ] ) ) {
					$result[ $state ] = $value[ $state ];
				}
			}
		}
		return $result;
	}
}

if ( ! function_exists( 'us_get_class_by_responsive_values' ) ) {
	/**
	 * Generates classes for an element based on value.
	 *
	 * @param string $value The value.
	 * @param string $template The template for composing the value.
	 * @return string Returns the generated classes if successful, otherwise an empty string.
	 */
	function us_get_class_by_responsive_values( $value, $template = '' ) {
		// In case value or template are empty, return empty string
		if (
			! is_string( $value ) OR empty( $value )
			OR ! is_string( $template ) OR empty( $template )
		) {
			return '';
		}

		if ( $values = (array) us_get_responsive_values( $value ) ) {
			$result = array();
			foreach ( $values as $state => $value ) {
				$result[] = sprintf( '%s_' . $template, $state, $value ); // template {state}_name_{value}
			}
			return implode( ' ', $result );
		}

		return sprintf( $template, $value ); // template: name_{value}
	}
}

if ( ! function_exists( 'us_get_jsoncss_options' ) ) {
	/**
	 * Get all settings for jsoncss compilation
	 * NOTE: A helper function is needed to get the settings for both the backend and the frontend
	 *
	 * @param array breakpoints The breakpoints of responsive states
	 * @param array $custom_states The custom responsive states
	 * @return array
	 */
	function us_get_jsoncss_options( $breakpoints = array(), $custom_states = array() ) {

		// Get responsive states
		$states = us_array_merge( (array) us_get_responsive_states(), $custom_states );

		// Get breakpoints of responsive states
		$breakpoints = us_array_merge(
			array(
				'default' => '',
				'laptops' => $states['laptops']['media_query'],
				'tablets' => $states['tablets']['media_query'],
				'mobiles' => $states['mobiles']['media_query'],
			),
			$breakpoints
		);

		/**
		 * Masks for optimizing and combining styles
		 * NOTE: The order of all values must match the specification of the css
		 * @var array
		 */
		$css_mask = array(
			'background' => 'color image repeat attachment position size',
			'padding' => 'top right bottom left',
			'margin' => 'top right bottom left',
			'border-style' => 'top right bottom left',
			'border-width' => 'top right bottom left',
			'border' => 'width style color',
			'text-shadow' => 'h-offset v-offset blur color',
			'box-shadow' => 'h-offset v-offset blur spread color',
			'font' => 'style weight size height family',
		);
		foreach ( $css_mask as &$mask_keys ) {
			$mask_keys = explode( ' ', $mask_keys );
		}
		unset( $mask_keys );

		return array(
			'breakpoints' => $breakpoints,
			'css_mask' => $css_mask,
		);
	}
}

if ( ! function_exists( 'us_jsoncss_compile' ) ) {
	/**
	 * Compilation of jsoncss styles
	 *
	 * @param array $jsoncss_collection
	 * @param array $breakpoints
	 * @param bool $important The !important rule in CSS is used to add more importance to a property/value than normal
	 * @return string
	 */
	function us_jsoncss_compile( $jsoncss_collection, $breakpoints = array() ) {
		if ( empty( $jsoncss_collection ) OR ! is_array( $jsoncss_collection ) ) {
			return '';
		}

		// Get all the necessary settings for compilation
		$jsoncss_options = us_get_jsoncss_options( $breakpoints );
		$breakpoints = $jsoncss_options['breakpoints'];
		$css_mask = $jsoncss_options['css_mask'];
		unset( $jsoncss_options );

		/**
		 * Optimization of the CSS options
		 * @param array $css_options
		 * @param string $state
		 * @return array
		 */
		$css_optimize = function ( $css_options, $state ) use ( $css_mask ) {

			// Normalization of css parameters
			foreach ( $css_options as $prop_name => $prop_value ) {

				// For background-image get an image URL by attachment ID
				if ( $prop_name === 'background-image' AND ! empty( $prop_value ) ) {

					$prop_value = us_replace_dynamic_value( $prop_value, /* acf_format */ FALSE );

					// Get an image by ID
					// DEV: do not use is_numeric() condition to support old values "123|full"
					if ( $image_url = wp_get_attachment_image_url( $prop_value, 'full' ) ) {
						$prop_value = sprintf( 'url(%s)', $image_url );

						// Skip cases when the value has url(), like after Demo Import
					} elseif ( strpos( $prop_value , 'url(' ) !== 0 ) {
						$prop_value = sprintf( 'url(%s)', $prop_value );
					}
				}

				// Generate correct font-family value for predefined fonts
				if ( $prop_name == 'font-family' ) {
					if ( in_array( $prop_value, US_TYPOGRAPHY_TAGS ) ) {
						if ( $prop_value == 'body' ) {
							$prop_value = 'var(--font-family)';
						} else {
							$prop_value = sprintf( 'var(--%s-font-family)', $prop_value );
						}
					}
				}

				$css_options[ $prop_name ] = trim( (string)$prop_value );

				// border-style to border-{position}-style provided that there is a width of this border
				if ( $prop_name === 'border-style' AND isset( $css_mask['border-width'] ) ) {
					foreach ( $css_mask['border-width'] as $position ) {
						$_prop = sprintf( 'border-%s-width', $position );
						if ( isset( $css_options[ $_prop ] ) AND $css_options[ $_prop ] != '' ) {
							$css_options[ sprintf( 'border-%s-style', $position ) ] = $css_options[ $prop_name ];
						}
					}
					unset( $css_options[ $prop_name ] );
				}
			}

			// Preparing styles for $css_mask
			$map_values = array();

			foreach ( $css_mask as $mask_name => $map_keys ) {
				// Grouping parameters by $css_mask
				foreach ( $map_keys as $mask_value ) {

					switch ( $mask_name ) {
						case 'border-width':
							$prop_name = sprintf( 'border-%s-width', $mask_value );
							break;
						case 'border-style':
							$prop_name = sprintf( 'border-%s-style', $mask_value );
							break;
						default:
							$prop_name = $mask_name . '-' . $mask_value;
							break;
					}

					if ( $prop_name == 'font-height' ) {
						$prop_name = 'line-height';
					}

					if ( isset( $css_options[ $prop_name ] ) AND trim( (string) $css_options[ $prop_name ] ) != '' ) {
						$map_values[ $mask_name ][ $mask_value ] = $css_options[ $prop_name ];

						// Set default value for background-position
					} elseif (
						$mask_value === 'position'
						AND empty( $map_values[ $mask_name ][ $mask_value ] )
						AND ! empty( $css_options['background-size'] )
					) {
						$map_values[ $mask_name ][ $mask_value ] = 'left top';

						// If there is at least one parameter for box-shadow & text-shadow, then fill in the missing ones with defaults
					} elseif (
						strpos( $prop_name, '-shadow-' ) !== FALSE
						AND strpos( implode( ' ', array_keys( $css_options ) ), '-shadow-' ) !== FALSE
					) {
						$map_values[ $mask_name ][ $mask_value ] = ( $mask_value == 'color' )
							? 'currentColor' // default color
							: '0';
					}

					// Combine the same options for padding, margin and border-width
					if (
						in_array( $mask_name, array( 'padding', 'margin', 'border-width', 'border-style' ) )
						AND isset( $map_values[ $mask_name ] )
						AND count( $map_values[ $mask_name ] ) === count( $map_keys )
						AND $unique_map_values = array_unique( $map_values[ $mask_name ] )
						AND count( $unique_map_values ) === 1
					) {
						$css_options[ $mask_name ] = array_shift( $unique_map_values );
					}
				}
			}

			// Checking css masks and adjusting parameters
			foreach ( $map_values as $mask_name => &$mask_props ) {
				if ( count( $mask_props ) === count( $css_mask[ $mask_name ] ) OR $mask_name == 'background' ) {

					// Clear unwanted params
					foreach ( array_keys( $mask_props ) as $mask_prop ) {

						// Creating a prop name
						$mask_prop = ( $mask_name === 'border-width' )
							? sprintf( 'border-%s-width', $mask_prop )
							: $mask_name . '-' . $mask_prop;
						if ( isset( $css_options[ $mask_prop ] ) ) {
							unset( $css_options[ $mask_prop ] );
						}
					}

					// Adjust background options before merging
					if ( $mask_name == 'background' ) {

						// If there is a gradinet, then add it to the end of the parameters
						if ( ! empty( $mask_props['color'] )
							AND (
								strpos( $mask_props['color'], 'gradient' ) !== FALSE
								OR preg_match( '~\s?var\(.*-grad\s?\)$~', $mask_props['color'] )
							)
						) {
							if ( ! empty( $mask_props['image'] ) ) {
								$_gradient = ', ' . $mask_props['color'];
								if ( isset( $mask_props['color'] ) ) {
									unset( $mask_props['color'] );
								}

								end( $mask_props );
								$mask_props[ key( $mask_props ) ] .= $_gradient;
							} else {
								$mask_props = array_slice( $mask_props, 0, 1, TRUE );
							}
						}
						if ( ! empty( $mask_props['size'] ) ) {
							$mask_props['size'] = '/ ' . $mask_props['size'];
						}
					}

					// Correction for the font parameter
					if ( $mask_name === 'font' AND isset( $mask_props['height'] ) ) {
						$mask_props['height'] = '/ ' . $mask_props['height'];
						if ( isset( $css_options['line-height'] ) ) {
							unset( $css_options['line-height'] );
						}
					}

					// Remove border-{position}-style properties
					if ( $mask_name === 'border-style' ) {
						foreach ( array_keys( $mask_props ) as $position ) {
							if ( isset( $css_options[ sprintf( 'border-%s-style', $position ) ] ) ) {
								unset( $css_options[ sprintf( 'border-%s-style', $position ) ] );
							}
						}
					}

					// Remove empty shadows
					if ( strpos( $mask_name, '-shadow' ) !== FALSE ) {
						$_value = $map_values[ $mask_name ];
						if ( isset( $_value['color'] ) ) {
							unset( $_value['color'] );
						}
						// Note: Values can be floating point numbers
						if ( $state == 'default' AND array_sum( array_map( 'abs', array_map( 'floatval', $_value ) ) ) === 0.0 ) {
							continue;
						}
					}

					// Combine parameters in one line
					if ( ! isset( $css_options[ $mask_name ] ) OR $css_options[ $mask_name ] == '' ) {
						$css_options[ $mask_name ] = implode( ' ', $map_values[ $mask_name ] );
					}
				} else {
					unset( $map_values[ $mask_name ] );
				}
			}
			unset( $mask_props, $map_values );

			return $css_options;
		};

		$output_css = '';

		if ( ! empty( $jsoncss_collection ) ) {
			// Optimization and the formation of CSS
			foreach ( array_keys( $breakpoints ) as $state ) {
				if ( ! empty( $jsoncss_collection[ $state ] ) ) {
					foreach ( $jsoncss_collection[ $state ] as $class_name => &$css_options ) {
						$css_options = $css_optimize( $css_options, $state );
					}
					unset( $css_options );
				}
			}

			// Convert options to css styles
			foreach ( $breakpoints as $state => $media ) {
				if ( ! empty( $jsoncss_collection[ $state ] ) ) {
					$media_css = '';
					foreach ( $jsoncss_collection[ $state ] as $class_name => $css_options ) {
						$styles = '';
						foreach ( $css_options as $prop_name => $prop_value ) {
							if ( trim( (string) $prop_value ) == '' ) {
								continue;
							}
							$styles .= sprintf( '%s:%s%s;', $prop_name, $prop_value, strpos( $prop_name, '--' ) === 0 ? '' : '!important' );
							// Cancel transparency for an element without animation
							// when using animation on different screens
							if ( $prop_name == 'animation-name' AND $prop_value == 'none' ) {
								$styles .= 'opacity:1!important;';
							}
						}
						if ( ! empty( $styles ) ) {
							if (
								in_array( $class_name, US_TYPOGRAPHY_TAGS )
								OR strpos( $class_name, ':' ) === 0 // ':root', ':hover' etc.
							) {
								$media_css .= sprintf( '%s{%s}', $class_name, $styles );
							} else {
								$media_css .= sprintf( '.%s{%s}', $class_name, $styles );
							}
						}
					}
				}
				if ( empty( $media_css ) ) {
					continue;
				}
				$output_css .= ! empty( $media )
					? sprintf( '@media %s {%s}', $media, $media_css )
					: $media_css;
			}
		}

		return us_minify_css( $output_css );
	}
}

if ( ! function_exists( 'us_remove_url_protocol' ) ) {
	/**
	 * Removing a protocol from a link
	 *
	 * @param string $url
	 * @return string
	 */
	function us_remove_url_protocol( $url ) {
		return str_replace( array( 'http:', 'https:' ), '', $url );
	}
}

if ( ! function_exists( 'us_get_aspect_ratio_values' ) ) {
	/**
	 * Calculate Aspect Ratio width and height, used in Grids
	 *
	 * @param string $_ratio
	 * @param string $_width
	 * @param string $_height
	 * @return array
	 */
	function us_get_aspect_ratio_values( $_ratio = '1x1', $_width = '1', $_height = '1' ) {
		if ( $_ratio == '4x3' ) {
			$_width = 4;
			$_height = 3;
		} elseif ( $_ratio == '3x2' ) {
			$_width = 3;
			$_height = 2;
		} elseif ( $_ratio == '2x3' ) {
			$_width = 2;
			$_height = 3;
		} elseif ( $_ratio == '3x4' ) {
			$_width = 3;
			$_height = 4;
		} elseif ( $_ratio == '16x9' ) {
			$_width = 16;
			$_height = 9;
		} elseif ( $_ratio == 'custom' ) {
			$_width = (float) str_replace( ',', '.', preg_replace( '/^[^\d.,]+$/', '', $_width ) );
			if ( $_width <= 0 ) {
				$_width = 1;
			}
			$_height = (float) str_replace( ',', '.', preg_replace( '/^[^\d.,]+$/', '', $_height ) );
			if ( $_height <= 0 ) {
				$_height = 1;
			}
		} else {
			$_width = $_height = 1;
		}

		return array( $_width, $_height );
	}
}

if ( ! function_exists( 'us_filter_posts_by_language' ) ) {
	/**
	 * Filters posts and remove unnecessary translations from the list
	 *
	 * @param $array $posts
	 * @return array
	 */
	function us_filter_posts_by_language( $posts ) {
		if (
			has_filter( 'us_tr_current_language' )
			AND ! empty( $posts )
			AND is_array( $posts )
		) {
			$current_lang = apply_filters( 'us_tr_current_language', NULL );
			if ( ! is_null( $current_lang ) ) {
				foreach ( $posts as $post_id => $post ) {

					// Exclude Grid Layouts
					if ( get_post_type( $post_id ) === 'us_grid_layout' ) {
						continue;
					}

					$post_lang_code = apply_filters( 'us_tr_get_post_language_code', (int) $post_id );
					if ( ! is_null( $post_lang_code ) AND $current_lang !== $post_lang_code ) {
						unset( $posts[ $post_id ] );
					}
				}
			}
		}

		return $posts;
	}

	add_filter( 'us_filter_posts_by_language', 'us_filter_posts_by_language', 10, 1 );
}

if ( ! function_exists( 'us_set_time_limit' ) ) {
	/**
	 * Set the number of seconds a script is allowed to run
	 *
	 * @param int $limit The limit
	 */
	function us_set_time_limit( $limit = 0 ) {
		$limit = (int) $limit;
		if (
			function_exists( 'set_time_limit' )
			&& FALSE === strpos( ini_get( 'disable_functions' ), 'set_time_limit' )
			&& ! ini_get( 'safe_mode' )
		) {
			set_time_limit( $limit );
		} elseif ( function_exists( 'ini_set' ) ) {
			ini_set( 'max_execution_time', $limit );
		}
	}
}

if ( ! function_exists( 'us_replace_dynamic_value' ) ) {
	/**
	 * Filters the string via replacing {{}} with custom field value or some predefined data
	 *
	 * @param string $string
	 * @param bool $acf_format
	 * @return string
	 */
	function us_replace_dynamic_value( $string, $acf_format = TRUE ) {
		$pattern = '/{{([^}]+)}}/';

		if (
			! is_string( $string )
			OR ! preg_match( $pattern, $string )
		) {
			return $string;
		}

		/**
		 * Filter the string, only if it contains the {{}} value
		 *
		 * @param array $matches 0 - variable code, 1 - variable name
		 * @return string
		 */
		return (string) preg_replace_callback( $pattern, function ( $matches ) use ( $acf_format ) {

			// Get id in call context
			if ( ( $current_id = us_get_current_id() ) < 1 ) {
				return ''; // Unable to determine the current ID
			}

			// Predefined: change '{{comment_count}}' to comments amount of the current post
			if ( $matches[/* code */0] == '{{comment_count}}' AND us_get_current_meta_type() == 'post' ) {
				return wp_count_comments( $current_id )->approved;

				// Predefined: change '{{post_count}}' to published posts amount
			} elseif ( $matches[/* code */0] == '{{post_count}}' ) {
				return wp_count_posts()->publish;

				// Predefined: change '{{user_count}}' to total users amount
			} elseif ( $matches[/* code */0] == '{{user_count}}' ) {
				return count_users()['total_users'];

				// Predefined: change '{{the_title}}' to the current page title
			} elseif ( $matches[/* code */0] == '{{the_title}}' ) {
				return strip_tags( do_shortcode( '[us_post_title]' ) );

				// Predefined: change '{{site_title}}' to the Site Title
			} elseif ( $matches[/* code */0] == '{{site_title}}' ) {
				return strip_tags( get_bloginfo( 'name' ) );

				// Predefined: change '{{site_icon}}' to the Site Icon ID
			} elseif ( $matches[/* code */0] == '{{site_icon}}' ) {
				return (string) get_option( 'site_icon' );

				// Predefined: change '{{the_thumbnail}}' to the current post thumbnail ID
			} elseif ( $matches[/* code */0] == '{{the_thumbnail}}' ) {
				return (string) get_post_thumbnail_id( $current_id );

				// Predefined: change '{{post_type_singular}}' to Post Type singular label
			} elseif ( $matches[/* code */0] == '{{post_type_singular}}' ) {
				if (
					$post_type = get_post_type( $current_id )
					AND $_object = get_post_type_object( $post_type )
				) {
					return $_object->labels->singular_name;
				} else {
					return '';
				}

				// Predefined: change '{{post_type_plural}}' to Post Type plural label
			} elseif ( $matches[/* code */0] == '{{post_type_plural}}' ) {
				if (
					$post_type = get_post_type( $current_id )
					AND $_object = get_post_type_object( $post_type )
				) {
					return $_object->labels->name;
				} else {
					return '';
				}

				// Predefined: change '{{today}}' to the current date (including Timezone) with format is used for comparison with ACF date fields
			} elseif ( $matches[/* code */0] == '{{today}}' ) {
				return current_time( 'Ymd' );

				// Predefined: change '{{today_now}}' to the current time (including Timezone) with format is used for comparison with ACF date fields
			} elseif ( $matches[/* code */0] == '{{today_now}}' ) {
				return current_time( 'YmdHis' );

				// Predefined: change '{{now}}' to the current time (including Timezone) with format is used for comparison with ACF date fields
			} elseif ( $matches[/* code */0] == '{{now}}' ) {
				return current_time( 'His' );

				// Get the custom field value
			} else {
				$meta_value = us_get_custom_field( $matches[/* name */1], $acf_format );

				if ( is_string( $meta_value ) ) {
					return $meta_value;
				}

				// If the value is an array containing non-array values, return them with comma separated
				// Example: ACF Gallery type will return a string like '12,34,675'
				if ( is_array( $meta_value ) AND ! array_filter( $meta_value, 'is_array' ) ) {
					return implode( ',', $meta_value );
				}

				return '';
			}
		}, $string );
	}
}

if ( ! function_exists( 'us_get_color_schemes' ) ) {
	/**
	 * Get available color schemes, both predefined and custom
	 *
	 * @return array
	 */
	function us_get_color_schemes( $only_titles = FALSE ) {
		$schemes = $schemes_titles = array();

		// Get custom schemes
		$custom_schemes = get_option( 'usof_style_schemes_' . US_THEMENAME );

		// Reverse Custom schemes order to make last added item first
		if ( is_array( $custom_schemes ) ) {
			$custom_schemes = array_reverse( $custom_schemes, TRUE );
		} else {
			$custom_schemes = array();
		}

		foreach ( $custom_schemes as $key => $custom_scheme ) {
			$schemes += array( 'custom_' . $key => $custom_scheme );
			$schemes_titles += array( 'custom_' . $key => $custom_scheme['title'] );
		}

		// Get predefined schemes
		$predefined_schemes = us_config( 'color-schemes' );
		$schemes += $predefined_schemes;

		foreach ( $predefined_schemes as $key => $predefined_scheme ) {
			$schemes_titles += array( $key => $predefined_scheme['title'] );
		}

		return ( $only_titles ) ? $schemes_titles : $schemes;
	}
}

if ( ! function_exists( 'us_get_available_icon_sets' ) ) {
	/**
	 * Get available icon sets
	 *
	 * @return array
	 */
	function us_get_available_icon_sets() {
		static $icon_sets = array();
		if ( ! empty( $icon_sets ) ) {
			return (array) $icon_sets;
		}

		$icon_sets = us_config( 'icon-sets', array() );
		foreach ( $icon_sets as $icon_slug => $icon_set ) {
			if ( us_get_option( 'icons_' . $icon_slug ) === 'none' ) {
				unset( $icon_sets[ $icon_slug ] );
			}
		}

		return $icon_sets;
	}
}

if ( ! function_exists( 'us_map_get_bbox' ) ) {
	/**
	 * Get bounding box from coordinates for OpenStreetMap, used for AMP only
	 * https://wiki.openstreetmap.org/wiki/Slippy_map_tilenames
	 *
	 * @param float $latitude
	 * @param float $longitude
	 * @param int $zoom
	 * @return string
	 */
	function us_map_get_bbox( $latitude, $longitude, $zoom ) {
		$width = 1000;
		$height = 600;
		$tile_size = 256;

		$xtile = floor( ( ( $longitude + 180 ) / 360 ) * pow( 2, $zoom ) );
		$ytile = floor( ( 1 - log( tan( deg2rad( $latitude ) ) + 1 / cos( deg2rad( $latitude ) ) ) / pi() ) / 2 * pow( 2, $zoom ) );

		$xtile_s = ( $xtile * $tile_size - $width / 2 ) / $tile_size;
		$ytile_s = ( $ytile * $tile_size - $height / 2 ) / $tile_size;
		$xtile_e = ( $xtile * $tile_size + $width / 2 ) / $tile_size;
		$ytile_e = ( $ytile * $tile_size + $height / 2 ) / $tile_size;

		$south = us_map_lon_lat( $xtile_s, $ytile_s, $zoom );
		$east = us_map_lon_lat( $xtile_e, $ytile_e, $zoom );

		return ( implode( ',', $south ) . ',' . implode( ',', $east ) );
	}
}

if ( ! function_exists( 'us_map_lon_lat' ) ) {
	/**
	 * Get Longitude and Latitude based on tile size and zoom for OpenStreetMap, used for AMP only
	 *
	 * @param $xtile float from us_map_get_bbox()
	 * @param $ytile float from us_map_get_bbox()
	 * @param $zoom int zoom from us_map_get_bbox()
	 * @return array
	 */
	function us_map_lon_lat( $xtile, $ytile, $zoom ) {
		$n = pow( 2, $zoom );
		$lon_deg = $xtile / $n * 360.0 - 180.0;
		$lat_deg = rad2deg( atan( sinh( pi() * ( 1 - 2 * $ytile / $n ) ) ) );

		return array( $lon_deg, $lat_deg );
	}
}

if ( ! function_exists( 'us_set_params_weight' ) ) {
	/**
	 * Set weights for params to keep the correct position in output
	 *
	 * @params One or more arrays that will be combined into one common array
	 * @return array
	 */
	function us_set_params_weight() {
		$params = array();
		foreach ( func_get_args() as $arg ) {
			if ( empty( $arg ) OR ! is_array( $arg ) ) {
				continue;
			}
			$params += $arg;
		}
		$count = count( $params );
		foreach ( $params as &$param ) {
			if ( isset( $param['weight'] ) ) {
				continue;
			}
			$param['weight'] = $count --;
		}

		return $params;
	}
}

if ( ! function_exists( 'us_user_profile_html' ) ) {
	/**
	 * Get profile info for Login element/widget
	 *
	 * @param $logout_redirect
	 * @param bool $hidden
	 * @return string
	 */
	function us_user_profile_html( $logout_redirect, $hidden = FALSE ) {
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			return '';
		}

		// Redirect to the current page, if other is not set
		if ( empty( $logout_redirect ) ) {
			$logout_redirect = home_url( us_get_safe_var( 'REQUEST_URI' ) );
		}

		$output = '<div class="w-profile' . ( $hidden ? ' hidden' : '' ) . '">';
		$output .= '<a class="w-profile-link for_user" href="' . esc_url( admin_url( 'profile.php' ) ) . '">';
		$output .= '<span class="w-profile-avatar">' . get_avatar( get_current_user_id(), '64' ) . '</span>';
		$output .= '<span class="w-profile-name">' . wp_get_current_user()->display_name . '</span>';
		$output .= '</a>';
		$output .= '<a class="w-profile-link for_logout" href="' . esc_url( wp_logout_url( $logout_redirect ) ) . '">' . us_translate( 'Log Out' ) . '</a>';
		$output .= '</div>';

		return apply_filters( 'us_user_profile_html', $output, $logout_redirect, $hidden );
	}
}

if ( ! function_exists( 'us_design_options_has_property' ) ) {
	/**
	 * Check for CSS property in the shortcode attribute
	 *
	 * @param string|array $css
	 * @param string|array $props
	 * @param bool $strict
	 * @return array
	 */
	function us_design_options_has_property( $css, $props, $strict = FALSE ) {
		$result = array();

		if ( empty( $props ) ) {
			return $result;
		}

		if ( ! is_array( $props ) ) {
			$props = array( (string) $props );
		}

		$props = array_map( 'trim', $props );
		$props = array_map( 'us_strtolower', $props );

		if ( is_string( $css ) ) {
			$css = json_decode( rawurldecode( $css ), TRUE );
		}

		if ( ! empty( $css ) AND is_array( $css ) ) {
			foreach ( $css as $state => $values ) {
				$values = array_keys( $values );
				$values = array_map( 'us_strtolower', $values );

				foreach ( $props as $prop ) {
					if ( ! in_array( $state, $result ) AND array_search( $prop, $values, $strict ) !== FALSE ) {
						$result[] = $state;
					}
				}
			}
		}

		return array_unique( $result );
	}
}

if ( ! function_exists( 'us_add_page_shortcodes_custom_css' ) ) {
	/**
	 * Add design options CSS for shortcodes in custom pages and Reusable Blocks
	 *
	 * @param int $id The ID
	 * TODO: Update method after implementing new inference logic from #2457.
	 */
	function us_add_page_shortcodes_custom_css( $id ) {
		// Output css styles
		$us_vc = new Us_Vc_Base;
		$us_vc->addPageCustomCss( $id );
		$us_vc->addShortcodesCustomCss( $id );
	}
}

if ( ! function_exists( 'us_get_shortcode_name' ) ) {
	/**
	 * Get shorcode name without prefix
	 *
	 * @param string $elm_name The elm name
	 * @param string $prefix Default prefix "us_"
	 * @return string
	 */
	function us_get_shortcode_name( $elm_name, $prefix = 'us_' ) {
		if ( strpos( $elm_name, $prefix ) === 0 ) {
			return substr( $elm_name, strlen( $prefix ) );
		}
		return $elm_name;
	}
}

if ( ! function_exists( 'us_get_shortcode_full_name' ) ) {
	/**
	 * Get shortcode full name
	 *
	 * @param string $elm_name The elm name
	 * @return string
	 */
	function us_get_shortcode_full_name( $elm_name ) {
		if (
			strpos( $elm_name , 'vc_' ) === 0
			// If it is not a theme element then we return the name as it is
			OR us_config( 'elements/' . $elm_name . '.override_config_only' )
		) {
			return $elm_name;
		}
		return 'us_' . $elm_name;
	}
}

if ( ! function_exists( 'us_uniqid' ) ) {
	/**
	 * Generate unique ID with specified length, will not affect uniqueness!
	 *
	 * @param string $length amount of characters
	 * @return string Returns unique id
	 */
	function us_uniqid( $length = 4 ) {
		if ( $length <= 0 ) {
			return '';
		}
		// Making sure first char of ID to be letter for correct CSS class/ID
		$seed = str_split( 'abcdefghijklmnopqrstuvwxyz' );
		$result = $seed[ array_rand( $seed ) ];

		if ( (int) $length > 1 ) {
			$result .= substr( uniqid(), - ( (int) $length - 1 ) );
		}

		return $result;
	}
}

if ( ! function_exists( 'us_get_edit_post_link' ) ) {
	/**
	 * Retrieves the edit post link for post (No database queries)
	 *
	 * @param string $post_id The post ID
	 * @param string $post_type The post type
	 * @return string
	 */
	function us_get_edit_post_link( $post_id, $post_type = 'page' ) {
		if ( empty( $post_id ) OR empty( $post_type ) ) {
			return '';
		}

		$link = '';

		// Get a link for editing in USBuilder
		if ( us_get_option( 'live_builder' ) AND class_exists( 'USBuilder' ) ) {
			$link = usb_get_edit_link( $post_id );

			// Get a link for editing in admin page
		} elseif ( $post_type_object = get_post_type_object( $post_type ) ) {
			if ( $post_type_object->_edit_link ) {
				$action = ( $post_type != 'revision' )
					? '&action=edit'
					: '';
				$link = admin_url( sprintf( $post_type_object->_edit_link, $post_id ) . $action );
			}

		}

		return (string) apply_filters( 'us_get_edit_post_link', $link, $post_id );
	}
}

if ( ! function_exists( 'us_is_cart' ) ) {
	/**
	 * Checks if the current page is a cart page
	 * Note: Supports checking on the builder page and in the admin panel
	 *
	 * @return bool Returns true when viewing the cart page
	 */
	function us_is_cart() {
		if ( ! class_exists( 'woocommerce' ) ) {
			return FALSE;
		}
		if ( is_admin() AND function_exists( 'wc_get_page_id' ) ) {
			return us_arr_path( $_REQUEST, 'post' ) == wc_get_page_id( 'cart' );
		}

		return function_exists( 'is_cart' ) AND is_cart();
	}
}

if ( ! function_exists( 'us_is_checkout' ) ) {
	/**
	 * Checks if the current page is a checkout page
	 * Note: Supports checking on the builder page and in the admin panel
	 *
	 * @return bool Returns true when viewing the checkout page
	 */
	function us_is_checkout() {
		if ( ! class_exists( 'woocommerce' ) ) {
			return FALSE;
		}
		if ( is_admin() AND function_exists( 'wc_get_page_id' ) ) {
			return us_arr_path( $_REQUEST, 'post' ) == wc_get_page_id( 'checkout' );
		}

		return function_exists( 'is_checkout' ) AND is_checkout();
	}
}

if ( ! function_exists( 'us_is_order_received_page' ) ) {
	/**
	 * Checks if the current page is a order received page
	 *
	 * @return bool Returns true when viewing the order received page
	 */
	function us_is_order_received_page() {
		return (
			class_exists( 'woocommerce' )
			AND function_exists( 'is_order_received_page' )
			AND is_order_received_page()
		);
	}
}

if ( ! function_exists( 'us_conditions_are_met' ) ) {
	/**
	 * Check if provided conditions are met
	 *
	 * @param array|string $conditions
	 * @param string $conditions_operator
	 * @return bool Returns true if conditions are met
	 */
	function us_conditions_are_met( $conditions, $conditions_operator ) {

		if (
			usb_is_post_preview()
			OR $conditions_operator === 'always'
			OR empty( $conditions )
		) {
			return TRUE;
		}

		$conditions_results = array();

		if ( is_string( $conditions ) ) {
			$conditions = json_decode( urldecode( $conditions ), /* to array */TRUE );
		}
		if ( ! is_array( $conditions ) ) {
			$conditions = array();
		}

		// Get the current object ID
		$current_id = us_get_current_id();

		// Get the current term object if exists
		global $us_grid_outputs_items, $us_grid_term;
		if ( $us_grid_outputs_items ) {
			$current_term = $us_grid_term;

		} elseif ( is_tax() OR is_tag() OR is_category() ) {
			$current_term = get_queried_object();
		}

		/**
		 * Function for comparing values for a condition according to a given mode
		 *
		 * @param string $needle The searched value
		 * @param string|array $haystack The array
		 * @param string $mode The mode
		 * @param bool $multiple_haystack if the haystack has several values
		 * @return bool True if successful, otherwise False
		 */
		$func_compare_values = function( $needle, $haystack, $mode = '=', $multiple_haystack = FALSE ) {
			if ( ! is_scalar( $needle ) ) {
				return FALSE;
			}

			// Explode the value with comma into several values, if set
			// Used in 'post_id' and 'tax_term' comparisons
			if (
				$multiple_haystack
				AND is_string( $haystack )
				AND strpos( $haystack, ',' ) !== FALSE
			) {
				$haystack = explode( ',', $haystack );
			}

			if ( ! is_array( $haystack ) ) {
				$haystack = array( $haystack );
			}
			$haystack = array_map( 'trim', array_map( 'strval', $haystack ) );

			/**
			 * The `mode` is implemented on the basis of standard comparison operators
			 * @link https://www.php.net/manual/en/language.operators.comparison.php#language.operators.comparison
			 */
			if ( $mode == '=' ) {
				return in_array( $needle, $haystack );
			}
			if ( $mode == '!=' ) {
				return ! in_array( $needle, $haystack );
			}
			if ( $mode == '>' ) {
				return ( $needle > $haystack[0] );
			}
			if ( $mode == '>=' ) {
				return ( $needle >= $haystack[0] );
			}
			if ( $mode == '<' ) {
				return ( $needle < $haystack[0] );
			}
			if ( $mode == '<=' ) {
				return ( $needle <= $haystack[0] );
			}
			if ( $mode == 'has_value' ) {
				return $needle !== '';
			}
			if ( $mode == 'no_value' ) {
				return $needle === '';
			}
			return FALSE;
		};

		// Conditions loop
		foreach( $conditions as $i => $condition ) {
			$condition = array_map( 'trim', $condition );
			if ( ! $condition_param = us_arr_path( $condition, 'param' ) ) {
				continue;
			}
			$mode = us_arr_path( $condition, 'mode', /* default */'=' );

			$condition_result = FALSE; // the default result is false

			// Checks by specified conditions
			if ( $condition_param == 'post_type' ) {
				$condition_result = $func_compare_values(
					get_post_type(),
					us_arr_path( $condition, 'post_type' ),
					$mode
				);

				// Object ID (page, term, user or comment)
			} elseif ( $condition_param === 'post_id' AND ! empty( $current_id ) ) {
				$condition_result = $func_compare_values(
					$current_id,
					us_arr_path( $condition, 'post_value' ),
					$mode,
					/* multiple_haystack */TRUE
				);

				// Page URL
			} elseif ( $condition_param == 'page_url' ) {
				$current_page_url = rawurlencode( home_url( us_get_safe_var( 'REQUEST_URI' ) ) );

				$custom_url = us_arr_path( $condition, 'page_url', '' );
				$custom_url = us_replace_dynamic_value( $custom_url );
				$custom_url = rawurlencode( $custom_url );

				// Use strpos() instead of func_compare_values()
				if ( $custom_url != '' ) {
					if ( $mode == '=' ) {
						$condition_result = ( strpos( $current_page_url, $custom_url ) !== FALSE );
					} else {
						$condition_result = ( strpos( $current_page_url, $custom_url ) === FALSE );
					}
				}

				// User Role
			} elseif ( $condition_param == 'user_role' ) {
				foreach( (array) wp_get_current_user()->roles as $user_role ) {
					$condition_result = (
						$condition_result
						OR $func_compare_values(
							$user_role,
							us_arr_path( $condition, 'user_role' ),
							$mode,
							/* multiple_haystack */TRUE
						)
					);
				}

				// User logged in?
			} elseif ( $condition_param == 'user_state' ) {
				$user_state = is_user_logged_in() ? 'logged_in' : 'logged_out';
				$condition_result = $func_compare_values(
					$user_state,
					us_arr_path( $condition, 'user_state' )
				);

				// Taxonomy Term
			} elseif ( $condition_param == 'tax_term' AND $condition_taxonomy = us_arr_path( $condition, 'tax' ) ) {
				$condition_value = us_arr_path( $condition, 'term_value', '' );

				// Immitate term with empty value for correct equation with empty string
				$terms = array( 1 => '' );

				// Get terms of the current post
				if ( empty( $current_term ) ) {

					if ( ! $terms = get_the_terms( $current_id, $condition_taxonomy ) ) {
						$terms = array( 1 => '' );
					}

					// Check the taxonomy name on the term archive page
				} elseif ( $current_term->taxonomy == $condition_taxonomy ) {
					$terms = array( $current_term );
				}

				foreach( $terms as $term ) {
					if ( ! empty( $term ) ) {
						$term_value = is_numeric( $condition_value )
							? $term->term_id
							: $term->slug;
					} else {
						$term_value = '';
					}

					$condition_result = $func_compare_values(
						$term_value,
						us_strtolower($condition_value ),
						$mode,
						TRUE
					);

					// Cancel the terms loop for the first needed result
					if ( $mode == '!=' AND ! $condition_result ) {
						break;
					} elseif ( $mode == '=' AND $condition_result ) {
						break;
					}
				}

				// Custom field
			} elseif ( $condition_param == 'custom_field' AND $meta_key = us_arr_path( $condition, 'cf_name_predefined', 'custom' ) ) {
				if ( $meta_key == 'custom' ) {
					$meta_key = us_arr_path( $condition, 'cf_name', '' );
				}

				// Get the custom field value
				$meta_value = us_get_custom_field( $meta_key );

				// Transform array, object, null variables into strings
				if ( ! is_scalar( $meta_value ) ) {
					if ( empty( $meta_value ) ) {
						$meta_value = '';
					} else {
						$meta_value = '1';
					}
				}

				// Get url from link object
				else if ( strpos( $meta_value, rawurlencode( '{"url"' ) ) === 0 ) {
					$meta_value = us_generate_link_atts( $meta_value );
					$meta_value = (string) us_arr_path( $meta_value, 'href' );
				}

				$condition_result = $func_compare_values(
					$meta_value,
					us_replace_dynamic_value( us_arr_path( $condition, 'cf_value', /* default */'' ) ),
					us_arr_path( $condition, 'cf_mode', '=' )
				);

				// Cart Status
			} elseif ( $condition_param == 'cart_status' AND class_exists( 'woocommerce' ) AND isset( WC()->cart ) ) {
				$cart_status = WC()->cart->is_empty() ? 'empty' : 'not_empty';
				$condition_result = $func_compare_values(
					$cart_status,
					us_arr_path( $condition, 'cart_status' )
				);

				// Cart Total
			} elseif ( $condition_param == 'cart_total' AND class_exists( 'woocommerce' ) AND isset( WC()->cart ) ) {
				$cart_total = WC()->cart->total;
				$custom_value = us_arr_path( $condition, 'cart_total', '' );
				$custom_value = us_replace_dynamic_value( $custom_value );
				$condition_result = $func_compare_values(
					$cart_total,
					$custom_value,
					us_arr_path( $condition, 'cart_total_mode' )
				);

				// WooCommerce Endpoints
			} elseif ( $condition_param == 'wc_account_endpoint' AND class_exists( 'woocommerce' ) AND is_user_logged_in() ) {
				if ( ! $wc_current_endpoint = WC()->query->get_current_endpoint() ) {
					$wc_current_endpoint = 'dashboard';
				}
				$condition_result = $func_compare_values(
					$wc_current_endpoint,
					us_arr_path( $condition, 'wc_account_endpoint', '' ),
					$mode
				);

				// Time
			} elseif ( $condition_param == 'time' ) {
				/**
				 * Pairs based on standard time format
				 * @link https://www.php.net/manual/en/datetime.format.php#refsect1-datetime.format-parameters
				 */
				$time_pairs = array(
					'd' => us_arr_path( $condition, 'time_day', '00' ),
					'H' => us_arr_path( $condition, 'time_hour', '00' ),
					'i' => us_arr_path( $condition, 'time_minute', '00' ),
					'm' => us_arr_path( $condition, 'time_month', '00' ),
					'Y' => us_arr_path( $condition, 'time_year', '2000' ),
					'w' => us_arr_path( $condition, 'time_weekday', '00' ),
				);

				// Define the mode
				switch ( us_arr_path( $condition, 'time_operator', 'since' ) ) {
					case 'until':
						$_current_time = wp_date( 'YmdHi' );
						$_custom_time = strtr( 'YmdHi', $time_pairs );
						$mode = '<=';
						break;

					case 'dm':
						$_current_time = wp_date( 'dm' );
						$_custom_time = strtr( 'dm', $time_pairs );
						$mode = '=';
						break;

					case 'w':
						$_current_time = wp_date( 'w' );
						$_custom_time = $time_pairs['w'];
						$mode = '=';
						break;

					case 'd':
						$_current_time = wp_date( 'd' );
						$_custom_time = $time_pairs['d'];
						$mode = '=';
						break;

					case 'm':
						$_current_time = wp_date( 'm' );
						$_custom_time = $time_pairs['m'];
						$mode = '=';
						break;

					case 'since':
					default:
						$_current_time = wp_date( 'YmdHi' );
						$_custom_time = strtr( 'YmdHi', $time_pairs );
						$mode = '>=';
						break;
				}

				$condition_result = $func_compare_values(
					$_current_time,
					$_custom_time,
					$mode
				);
			}

			$conditions_results[ $condition_param . $i ] = $condition_result;

			// Cancel the element output if statement is `and` and there is any `false`
			if ( $conditions_operator == 'and' AND ! $condition_result ) {
				return FALSE;
			}

			// Cancel the loop if the operator is `or` and `true` is found
			if ( $conditions_operator == 'or' AND $condition_result ) {
				break;
			}
		}

		// Cancel the element output if statement is `or` and not `true`
		if (
			$conditions_operator == 'or'
			AND ! array_search( /* needle */TRUE, $conditions_results, /* strict */TRUE )
		) {
			return FALSE;
		}

		return TRUE;
	}
}

if ( ! function_exists( 'us_amp' ) ) {
	/**
	 * The current page is AMP page
	 *
	 * @return bool
	 */
	function us_amp() {
		return function_exists( 'amp_is_request' ) AND amp_is_request();
	}
}

if ( ! function_exists( 'us_get_specific_classes_by_shortcode' ) ) {
	/**
	 * Get a list of specific css classes based on shortcode params
	 *
	 * @param array $atts The is an array of shorcode attributes
	 * @param bool $to_string The flag that changes the format of returned data
	 * @return string|array Returns a list of unique css classes generated based on the params
	 */
	function us_get_specific_classes_by_shortcode( $atts, $to_string = TRUE ) {
		if ( ! is_array( $atts ) OR empty( $atts ) ) {
			return '';
		}
		/**
		 * List of specific classes
		 * @var array
		 */
		$css_classes = array();

		// Get a unique class for connecting design options
		if ( isset( $atts['css'] ) AND $unique_class_name = us_get_unique_css_class_name( $atts['css'] ) ) {
			$css_classes[] = (string) $unique_class_name;
		}

		// Adding classes specified by the user in the shortcode settings
		if ( ! empty( $atts['el_class'] ) ) {
			$css_classes = array_merge( $css_classes, explode( ' ', (string) $atts['el_class'] ) );
		}

		// Add specific class if some value is set in Design options
		if ( ! empty( $atts['css'] ) AND us_design_options_has_property( $atts['css'], 'color' ) ) {
			$css_classes[] = 'has_text_color';
		}

		// Add animation class if set in Design options
		if ( ! us_amp() AND ! empty( $atts['css'] ) AND us_design_options_has_property( $atts['css'], 'animation-name' ) ) {
			$css_classes[] = 'us_animate_this';
		}

		// Add class names based on "Hide on" settings
		if ( ! empty( $atts['hide_on_states'] ) ) {
			foreach ( explode( ',', (string) $atts['hide_on_states'] ) as $state ) {
				$css_classes[] = sprintf( 'hide_on_%s', $state );
			}
		}

		// Filtering the list of classes and leaving only unique ones
		$css_classes = array_unique( array_map( 'strval', $css_classes ) );

		// Return the list of classes in the required format
		return $to_string ? implode( ' ', $css_classes ) : $css_classes;
	}
}

if ( ! function_exists( 'us_fallback_metabox_value' ) ) {
	add_filter( 'us_fallback_metabox_value', 'us_fallback_metabox_value', 1, 3 );
	/**
	 * Filter compatible meta value for different versions
	 *
	 * @param mixed $meta_value The meta value
	 * @param string $meta_key The meta key
	 * @param array $field The field options
	 * @return mixed Returns compatible meta value for different versions
	 */
	function us_fallback_metabox_value( $meta_value, $meta_key = '', $field = array() ) {

		if ( ! is_array( $field ) OR ! isset( $field['type'] ) ) {
			return $meta_value;
		}

		// Fallback for "Do not display" values for versions above 8.14
		if ( $field['type'] == 'select' AND $meta_value == '' AND preg_match( '/^us_([a-z\_]+)_id$/', $meta_key ) ) {
			return '0'; // show content as is
		}

		// Fallback for "switch" value, where a number, not a string, is needed to work correctly
		else if ( $field['type'] == 'switch' ) {
			return (int) $meta_value;
		}

		return $meta_value;
	}
}
