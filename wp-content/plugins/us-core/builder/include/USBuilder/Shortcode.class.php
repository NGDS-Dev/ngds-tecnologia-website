<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * This is a class for working with shortcodes
 */
final class USBuilder_Shortcode {

	/**
	 * Shortcodes with IDs for the builder
	 */
	private $content;

	/**
	 * @var USBuilder_Shortcode
	 */
	protected static $instance;

	/**
	 * @access public
	 * @return USBuilder_Shortcode
	 */
	static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Normalizes texts and adds vc_column_text if needed
	 *
	 * @access private
	 * @param string $content The content
	 * @param bool $is_container Is the element's parent a container
	 * @return string Returns content with valid shortcodes for text
	 */
	private function normalize_texts( $content, $is_container = FALSE ) {
		if ( ! is_string( $content ) ) {
			return $content;
		}

		// Get theme elements
		$theme_elements = (array) us_config( 'shortcodes.theme_elements', array() );
		$theme_elements = array_map( 'us_get_shortcode_full_name', $theme_elements );

		// Get all shortcodes
		$pattern = '/' . get_shortcode_regex( $theme_elements ) . '/';
		if ( ! preg_match_all( $pattern, trim( $content ), $matches ) ) {
			return ( $is_container AND strlen( $content ) > 0 )
				? $this->normalize_texts( '[vc_column_text]' . $content . '[/vc_column_text]', /* is_container */FALSE )
				: $content;
		}

		$result = '';
		foreach ( $matches[/* siblings elms */2] as $i => $elm_name ) {
			$elm_config = us_config( 'elements/' . us_get_shortcode_name( $elm_name ), array() );
			// Has a property indicating that this is a container
			$is_container = us_arr_path( $elm_config, 'is_container' ) === TRUE;

			$elm_atts = $matches[/* atts */ 3][ $i ];
			$elm_content = $this->normalize_texts( $matches[/* content */ 5][ $i ], $is_container );

			// Save the current shortcode (for containers with a closing tag)
			// Note: Has a `content` parameter, which makes it a container by default (this is WPBakery specific)
			if ( $is_container || us_arr_path( $elm_config, 'params.content' ) !== NULL ) {
				$result .= sprintf( '[%s%s]%s[/%s]', $elm_name, $elm_atts, $elm_content, /* end */ $elm_name );
			} else {
				$result .= sprintf( '[%s%s]%s', $elm_name, $elm_atts, $elm_content );
			}
		}

		return (string) $result;
	}

	/**
	 * Set page content (shortcodes)
	 *
	 *  @access public
	 */
	function set_page_content() {
		global $post;
		if ( $post instanceof WP_Post ) {
			// Set current post_content for $usb.pageData
			$this->content = $this->prepare_text( $post->post_content );
		}
	}

	/**
	 * Prepares shortcodes for display on the preview page
	 *
	 * @access public
	 * @param string $content This is the content of the page
	 * @return string
	 */
	function prepare_text( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		$post_id = (int) usb_get_post_id();
		$shortcode_regex = get_shortcode_regex();
		$content = shortcode_unautop( trim( $content ) );

		$this->fallback_content( $content );

		// Checking if we are preparing the edited post content
		$preparing_main_content = FALSE;
		if ( $post_id == get_the_ID() ) {
			$preparing_main_content = TRUE;
		}

		// For the edited post, we are forcing row/column structure for now
		if (
			$preparing_main_content
			// Get rows without shortcodes
			AND $not_shortcodes = preg_split( '/'. $shortcode_regex .'/', $content, /* limit */-1, PREG_SPLIT_OFFSET_CAPTURE )
		) {
			// Shortcode for simple content
			$content_shortcode = ! defined( 'USB_REMOVE_ROWS' )
				? '[vc_row][vc_column][vc_column_text]%s[/vc_column_text][/vc_column][/vc_row]'
				: '[vc_column_text]%s[/vc_column_text]';

			// List of tags to be removed when checking for an empty string
			$strip_tags = array( '<p>', '</p>' );

			foreach ( $not_shortcodes as $matches ) {
				$text = $matches[0];
				$offset = $matches[1];

				// Skip blank lines
				if ( strlen( trim( str_replace( $strip_tags, '', $text ) ) ) === 0 ) {
					continue;
				}

				// Replacing a simple string with a shortcode structure
				$content = substr_replace( $content, sprintf( $content_shortcode, $text ), $offset, strlen( $text ) );
			}
		}

		// Normalizes texts and adds vc_column_text if needed
		$content = $this->normalize_texts( $content );

		$indexes = array(); // the indexes for shortcodes

		/**
		 * Adds usbid for shortcodes
		 *
		 * @param $matches The matches
		 * @return string Modified shortcode
		 */
		$func_prepare_shortcode = function ( $matches ) use ( &$indexes ) {
			// Matched variables
			$shortcode_name = $matches[2];
			$shortcode_atts = $matches[3];
			$shortcode_content = $matches[5];

			// A shortcode can have only one identifier, so if there is an identifier,
			// we will return the result unchanged
			if ( strpos( $shortcode_atts, 'usbid="' ) !== FALSE ) {
				return $matches[0]; // original shortcode unchanged
			}

			// Gets a unique index for a shortcode
			if ( empty( $indexes[ $shortcode_name ] ) ) {
				$indexes[ $shortcode_name ] = 1;
			} else {
				$indexes[ $shortcode_name ]++;
			}

			// Creating a unique tag ID
			$usbid = $shortcode_name . ':' . $indexes[ $shortcode_name ];

			// Add the usbid to the general list of shortcode attributes
			return '[' . $shortcode_name.$shortcode_atts . ' usbid="' . $usbid .'"]' . $shortcode_content;
		};
		$content = preg_replace_callback( '/'. $shortcode_regex .'/Ui', $func_prepare_shortcode, $content );

		return (string) apply_filters( 'usb_shortcode_preparate_text', $content );
	}

	/**
	 * Adds data-usbid attribute to html when output shortcode result
	 *
	 * @access public
	 * @param string $output The shortcode output
	 * @param string $tag The shortcode tag name
	 * @param array $atts The shortcode attributes array or empty string
	 * @return string
	 */
	function add_usbid_to_html( $output, $tag, $atts ) {
		if ( ! ( $usbid = us_arr_path( $atts, 'usbid' ) ) ) {
			return $output;
		}

		/**
		 * Get custom css by ID
		 *
		 * @private
		 * @param int $post_id The post ID
		 * @return string Returns custom styles (CSS)
		 */
		$func_get_custom_css = function ( $post_id ) {
			$result = '';
			$jsoncss_data = get_post_meta( (int) $post_id, '_us_jsoncss_data', TRUE );
			if ( ! empty( $jsoncss_data ) AND is_array( $jsoncss_data ) ) {
				$jsoncss_data_collection = array();
				foreach ( $jsoncss_data as $jsoncss ) {
					us_add_jsoncss_to_collection( $jsoncss, $jsoncss_data_collection );
				}
				if ( $custom_css = (string) us_jsoncss_compile( $jsoncss_data_collection ) ) {
					$result .= $custom_css;
				}
			}

			return $result;
		};

		$style_tags = $us_page_block_custom_css = '';

		// Get custom styles for Reusable Blocks
		if ( $tag == 'us_page_block' AND $post_id = us_arr_path( $atts, 'id' ) ) {
			$us_page_block_custom_css .= $func_get_custom_css( $post_id );
		}

		// Get custom styles for Reusable Blocks in grids
		if (
			in_array( $tag, array( 'us_grid', 'us_carousel' ) )
			AND $post_id = us_arr_path( $atts, 'no_items_page_block' )
		) {
			$us_page_block_custom_css .= $func_get_custom_css( $post_id );
		}

		// Add custom related styles to output (e.g. styles from page_block )
		if ( ! empty( $us_page_block_custom_css ) ) {
			$_styles_atts = array(
				'data-for' => $usbid,
				'data-relation-for' => 'no_items_page_block',
			);
			$style_tags .= '<style ' . us_implode_atts( $_styles_atts ) . '>' . $us_page_block_custom_css . '</style>';
		}

		/**
		 * @var array Elements with more than one node in the result must have a common wrap
		 */
		$with_wrappers = (array) us_config( 'us-builder.with_wrappers', /* default */array() );

		// This element does not have its own markup only a wrapper to connect the content, so separately from the other
		$with_wrappers[] = 'us_page_block';

		// Add wrappers for us_grid / us_page_block elements, this is necessary to detect the element in the builder
		if ( in_array( $tag, $with_wrappers ) ) {
			$wrapper_atts = array();
			// Attributes for the Reusable Block wrapper
			if ( $tag == 'us_page_block' AND $post_id = us_arr_path( $atts, 'id' ) ) {
				$wrapper_atts = array(
					'class' => 'w-page-block',
					'data-usb-highlight' => us_json_encode( array(
						'edit_permalink' => (string) usb_get_edit_link( $post_id ),
						'edit_label' => __( 'Edit Reusable Block', 'us' ),
					) ),
				);
			}
			$output = '<div' . us_implode_atts( $wrapper_atts ) . '>' . $output . '</div>';
		}

		// Additional attributes for output
		$output = preg_replace( '/(<[a-z\d\-]+)(.*)/', '$1 ' . 'data-usbid="' . $usbid . '"' . '$2', $output, 1 );

		// Add custom styles to the output
		if ( $jsoncss = us_arr_path( $atts, 'css', /* Default */ FALSE ) ) {
			$jsoncss_collection = array();
			$unique_class_name = (string) us_add_jsoncss_to_collection( $jsoncss, $jsoncss_collection );

			// Replacing the existing class with a new one to avoid duplicates with the same design settings
			$new_unique_class_name = 'usb_custom_' . str_replace( ':', '', $usbid );
			$output = str_replace( $unique_class_name, $new_unique_class_name, $output );

			// Replacing classes in a jsoncss collection
			$new_jsoncss_collection = array();
			foreach ( $jsoncss_collection as $state => $collection ) {
				$new_jsoncss_collection[ $state ][ $new_unique_class_name ] = $collection[ $unique_class_name ];
			}
			unset( $jsoncss_collection );

			// Add custom element styles to output
			if ( $custom_css = (string) us_jsoncss_compile( $new_jsoncss_collection ) ) {
				// Note: Updated on the JS side (builder.js) for template imports
				$_style_atts = array(
					'data-classname' => $new_unique_class_name,
					'data-for' => $usbid,
				);
				$style_tags .= '<style ' . us_implode_atts( $_style_atts ) . '>' . $custom_css . '</style>';
			}
		}

		return $style_tags . $output;
	}

	/**
	 * Export page content, page metadata, custom css, fields data for Builder
	 *
	 * Note:
	 * 		windov.$usb.content This is the content of the page
	 * 		window.$usb.pageCustomCss This is a custom custom css for the page
	 *
	 * @access public
	 */
	function export_page_data() {

		/**
		 * Selector for find style node
		 * NOTE: Since this is outputed in the bowels of the WPBakery Page Builder, we can correct it here
		 *
		 * @var string
		 */
		$custom_css_selector = 'style[data-type='. USBuilder::KEY_CUSTOM_CSS .']';

		/**
		 * Page fields such as post_title, post_name, post_status etc
		 *
		 * @var array
		 */
		$page_fields = array(
			// Get the title of the current page
			'post_title' => esc_attr( get_the_title() ),
		);

		/**
		 * Current metadata settings for the page
		 *
		 * @var array
		 */
		$page_meta = array();

		/**
		 * Get post metadata based on meta-boxes config
		 * Note: In `usof_meta`, metadata can be overridden for preview in the USBuilder
		 *
		 * @var array
		 */
		$metadata = get_post_custom( (int) usb_get_post_id() );
		foreach ( (array) us_config( 'meta-boxes', array() ) as $metabox_config ) {
			if (
				! us_arr_path( $metabox_config, 'usb_context' )
				OR ! in_array( get_post_type(), (array) us_arr_path( $metabox_config, 'post_types', array() ) )
			) {
				continue;
			}

			foreach ( us_arr_path( $metabox_config, 'fields', array() ) as $field_key => $field ) {
				$meta_value = us_arr_path( $metadata, "{$field_key}.0", /* default */us_arr_path( $field, 'std', '' ) );

				// Filter compatible meta value for different versions
				$meta_value = apply_filters( 'us_fallback_metabox_value', $meta_value, $field_key, $field );

				$page_meta[ $field_key ] = is_serialized( $meta_value )
					? unserialize( $meta_value )
					: $meta_value;
			}
		}
		unset( $metadata );

		// JS code for import page data to USBuilder
		$jscode = '
			// Check the is iframe current window
			if ( window.self !== window.top ) {
				window.$usbdata = window.$usbdata || {};
				window.$usbdata.pageData = window.$usbdata.pageData || {};
				// Export page data.
				var pageData = window.$usbdata.pageData;
				pageData.content = document.getElementById("usb-content").innerHTML || "";
				pageData.fields = ' . json_encode( $page_fields ) . ';
				pageData.pageMeta = ' . json_encode( $page_meta ) . ';
				// Get data from stdout
				pageData.customCss = ( document.querySelector("'. $custom_css_selector .'") || {} ).innerHTML || "";
			}
		';
		// This is the content of the page
		echo '<script id="usb-content" type="text/post_content">' . $this->content .'</script><script>' . $jscode . '</script>';

	}

	/**
	 * @param string $content
	 * @return bool Returns true if the content has changed, otherwise false
	 */
	function fallback_content( &$content ) {
		$content_changed = FALSE;
		if ( preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches ) ) {
			if ( count( $matches[2] ) ) {
				foreach ( $matches[2] as $i => $shortcode_name ) {
					$shortcode_content_changed = $shortcode_changed = FALSE;
					$shortcode_string = $matches[0][ $i ];
					$shortcode_atts_string = $matches[3][ $i ];
					$shortcode_content = $matches[5][ $i ];

					$atts_filter = 'us_edit_atts_fallback_' . $shortcode_name;
					$name_filter = 'usb_fallback_name_' . $shortcode_name;

					if ( has_filter( $atts_filter ) ) {
						$shortcode_atts = shortcode_parse_atts( $shortcode_atts_string );
						if ( ! is_array( $shortcode_atts ) ) {
							$shortcode_atts = array();
						}
						$fallback_atts = (array) apply_filters( $atts_filter, $shortcode_atts, $shortcode_content );
						$shortcode_changed = TRUE;
						$shortcode_atts_string = us_implode_atts( $fallback_atts, /* is shortcode */ TRUE );
					}
					if ( has_filter( $name_filter ) ) {
						$shortcode_changed = TRUE;
						$shortcode_name = apply_filters( $name_filter, $shortcode_name );
					}

					// Using recursion to fallback shortcodes inside this shortcode content
					if ( ! empty( $shortcode_content ) ) {
						$shortcode_content_changed = $this->fallback_content( $shortcode_content );
					}

					if ( $shortcode_changed OR $shortcode_content_changed ) {
						$new_shortcode_string = '[' . $shortcode_name . $shortcode_atts_string . ']';
						if ( ! empty( $shortcode_content ) ) {
							$new_shortcode_string .= $shortcode_content;
						}
						if ( strpos( $shortcode_string, '[/' . $matches[2][ $i ] . ']' ) ) {
							$new_shortcode_string .= '[/' . $shortcode_name . ']';
						}

						// Doing str_replace only once to avoid collisions
						$pos = strpos( $content, $shortcode_string );
						if ( $pos !== FALSE ) {
							$content = substr_replace( $content, $new_shortcode_string, $pos, strlen( $shortcode_string ) );
						}

						$content_changed = TRUE;
					}
				}
			}
		}

		return $content_changed;
	}

}
