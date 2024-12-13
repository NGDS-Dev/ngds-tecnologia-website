<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

class US_Shortcodes {

	/**
	 * @var {String} Template directory
	 */
	protected $_template_directory;

	protected $_stylesheet_directory;

	protected $config;

	/**
	 * @var array Current shortcode config
	 */
	protected $_settings;

	/**
	 * Retrieve one setting (used for compatibility with VC)
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function settings( $key ) {
		return isset( $this->_settings[ $key ] ) ? $this->_settings[ $key ] : NULL;
	}

	/**
	 * @var US_Shortcodes
	 */
	protected static $instance;

	/**
	 * Singleton pattern: US_Shortcodes::instance()->us_grid($atts, $content)
	 *
	 * @return US_Shortcodes
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	protected function __construct() {
		global $us_template_directory, $us_stylesheet_directory;
		$this->config = us_config( 'shortcodes' );

		add_filter( 'the_content', array( $this, 'paragraph_fix' ) );
		add_filter( 'us_page_block_the_content', array( $this, 'paragraph_fix' ), 11 );
		add_filter( 'us_content_template_the_content', array( $this, 'paragraph_fix' ), 11 );

		add_filter( 'the_content', array( $this, 'a_to_img_magnific_pupup' ) );
		add_filter( 'us_page_block_the_content', array( $this, 'a_to_img_magnific_pupup' ) );
		add_filter( 'us_content_template_the_content', array( $this, 'a_to_img_magnific_pupup' ) );

		// Make sure that priority makes the class init after WPBakery Page Builder
		add_action( 'init', array( $this, 'init' ), 20 );

		$this->_template_directory = $us_template_directory;
		$this->_stylesheet_directory = $us_stylesheet_directory;
	}

	/**
	 * @var bool Is the shortcode inited?
	 */
	protected $inited = FALSE;

	public function init() {

		// Adding new shortcodes
		if ( isset( $this->config['theme_elements'] ) ) {
			foreach ( $this->config['theme_elements'] as $element ) {
				$shortcode = ( strpos( $element, 'vc_' ) === 0 ) ? $element : 'us_' . $element;
				add_shortcode( $shortcode, array( $this, $shortcode ) );
			}
		}

		// Adding aliases
		if ( isset( $this->config['alias'] ) ) {
			foreach ( $this->config['alias'] as $shortcode => $alias ) {
				// Overloading the previous declaration if exists
				if ( shortcode_exists( $shortcode ) ) {
					remove_shortcode( $shortcode );
				}
				add_shortcode( $shortcode, array( $this, $shortcode ) );
			}
		}

		// Removing disabled shortcodes
		if ( us_get_option( 'disable_extra_vc', 1 ) AND isset( $this->config['disabled'] ) ) {
			foreach ( $this->config['disabled'] as $shortcode ) {
				if ( shortcode_exists( $shortcode ) ) {
					remove_shortcode( $shortcode );
				}
			}
		}

		$this->inited = TRUE;
	}

	/**
	 * Handling shortcodes
	 *
	 * @param string $shortcode Shortcode name
	 * @param array $args
	 *
	 * @return string Generated shortcode output
	 *
	 */
	public function __call( $shortcode, $args ) {
		$_output = '';
		$shortcode_base = $shortcode;

		// Checking if it is alias and getting real shortcode name
		if ( isset( $this->config['alias'][ $shortcode ] ) ) {
			$shortcode = $this->config['alias'][ $shortcode ];
		}

		// Check if it is theme element or modified shortcode
		$element = us_get_shortcode_name( $shortcode );

		if (
			// If it is not a theme element then we return the $output as it is.
			us_config( 'elements/' . $element . '.override_config_only' )
			OR ! in_array( $element, $this->config['theme_elements'] )
		) {
			return $_output;
		}

		// Preparing params for shortcodes (can be used inside of the input)
		$atts = isset( $args[0] ) ? $args[0] : array();
		$content = isset( $args[1] ) ? $args[1] : '';

		// VC's special chars replacement
		if ( is_array( $atts ) ) {
			$atts_result = array();
			foreach ( $atts as $key => $val ) {
				$atts_result[ $key ] = str_replace(
					array(
						'`{`',
						'`}`',
						'``',
					), array(
					'[',
					']',
					'"',
				), $val
				);
			}
			$atts = $atts_result;
		}

		// Preserving VC before hook
		if ( strpos( $shortcode_base, 'vc_' ) === 0 AND defined( 'VC_SHORTCODE_BEFORE_CUSTOMIZE_PREFIX' ) ) {
			$custom_output_before = VC_SHORTCODE_BEFORE_CUSTOMIZE_PREFIX . $shortcode_base;
			if ( function_exists( $custom_output_before ) ) {
				$_output .= $custom_output_before( $atts, $content );
			}
			unset( $custom_output_before );
		}

		$_filename = us_locate_file( 'templates/elements/' . $element . '.php' );

		// We are using the context variable in some elements templates
		$us_elm_context = 'shortcode';

		// Fallback for elements that used both in shortcodes and in grid builder
		global $us_grid_item_type;
		if ( ! $us_grid_item_type ) {
			$us_grid_item_type = 'post';
		}

		// Apply fallback to non-empty attributes
		$atts = apply_filters( 'us_fallback_atts_' . $shortcode, $atts, $content );

		// Fill empty attributes with default values (from config)
		$filled_atts = us_shortcode_atts( $atts, $shortcode );

		// Only for theme elements aliases: get params for both base and alias
		if ( $shortcode_base != $shortcode ) {
			$filled_atts_base = us_shortcode_atts( $atts, $shortcode_base );
			$filled_atts = us_array_merge( $filled_atts, $filled_atts_base );
		}

		// Disable the element output, if provided conditions aren't met
		$conditions = us_arr_path( $filled_atts, 'conditions', array() );
		$conditions_operator = us_arr_path( $filled_atts, 'conditions_operator', 'always' );
		if ( ! us_conditions_are_met( $conditions, $conditions_operator ) ) {
			return '';
		}

		// Get design css class to be overridden in specific shortcodes
		if (
			isset( $filled_atts['css'] )
			AND $design_class_name = us_get_unique_css_class_name( $filled_atts['css'] )
		) {
			$filled_atts['design_css_class'] = $design_class_name;
		}

		// Get a list of specific classes based on shortcode settings
		if ( $css_classes = (string) us_get_specific_classes_by_shortcode( $filled_atts ) ) {
			if ( ! isset( $filled_atts['classes'] ) ) {
				$filled_atts['classes'] = ' ' . $css_classes;
			} else {
				$filled_atts['classes'] .= ' ' . $css_classes;
			}
		}

		// Check the "Custom HTML attributes" and generate the '_atts' variable
		if ( ! empty( $filled_atts['enable_custom_html_atts'] ) AND ! empty( $filled_atts['custom_html_atts'] ) ) {
			$atts_array = json_decode( rawurldecode( $filled_atts['custom_html_atts'] ), TRUE );
			if ( is_array( $atts_array ) ) {
				foreach ( $atts_array as $attr ) {
					if ( ! empty( $attr['name'] ) ) {

						// Remove any symbols except latin letters and dash "-", underscore "_" and dot "." to be valid HTML attribute
						$attr['name'] = preg_replace( '/[^A-Za-z\-_.]+/', '', $attr['name'] );

						// Skip predefined HTML attributes to avoid conflicts
						if ( in_array( $attr['name'], array( 'class', 'id', 'style' ) ) ) {
							continue;
						}

						$html_atts[ $attr['name'] ] = isset( $attr['value'] ) ? $attr['value'] : '';
					}
				}
			}
			$filled_atts['_atts'] = $html_atts;
		}

		// Generate "Scroll Effect" data if set
		if ( ! empty( $filled_atts['scroll_effect'] ) ) {
			$effect_classes = ' has_scroll_effects';

			$us_scroll_params = array(
				'start_position' => $filled_atts['scroll_start_position'],
				'end_position' => $filled_atts['scroll_end_position'],
				'delay' => $filled_atts['scroll_delay'],
			);

			// Vertical Offset
			if ( ! empty( $filled_atts['scroll_translate_y'] ) ) {
				$effect_classes .= ' has_translate_y';
				$us_scroll_params['translate_y_direction'] = $filled_atts['scroll_translate_y_direction'];
				$us_scroll_params['translate_y_speed'] = $filled_atts['scroll_translate_y_speed'];
			}

			// Horizontal Offset
			if ( ! empty( $filled_atts['scroll_translate_x'] ) ) {
				$effect_classes .= ' has_translate_x';
				$us_scroll_params['translate_x_direction'] = $filled_atts['scroll_translate_x_direction'];
				$us_scroll_params['translate_x_speed'] = $filled_atts['scroll_translate_x_speed'];
			}

			// Transparency
			if ( ! empty( $filled_atts['scroll_opacity'] ) ) {
				$effect_classes .= ' has_opacity';
				$us_scroll_params['opacity_direction'] = $filled_atts['scroll_opacity_direction'];
			}

			// Add effect classes
			if ( ! isset( $filled_atts['classes'] ) ) {
				$filled_atts['classes'] = '';
			}
			$filled_atts['classes'] .= $effect_classes;

			$filled_atts['_atts']['data-us-scroll'] = us_json_encode( $us_scroll_params );
		}

		if ( isset( $filled_atts['content'] ) ) {
			unset( $filled_atts['content'] );
		}
		extract( $filled_atts );

		ob_start();
		require $_filename;
		$_output .= ob_get_clean();

		// Preserving VC after hooks
		if ( substr( $shortcode_base, 0, 3 ) == 'vc_' ) {
			// Get function name
			$custom_output_after = defined( 'VC_SHORTCODE_AFTER_CUSTOMIZE_PREFIX' )
				? VC_SHORTCODE_AFTER_CUSTOMIZE_PREFIX
				: 'vc_theme_after_';
			$custom_output_after .= $shortcode_base;

			// Call the function if it exists
			if ( function_exists( $custom_output_after ) ) {
				$_output .= $custom_output_after( $atts, $content );
			}
			$this->_settings = array(
				'base' => $shortcode_base,
			);
			$_output = apply_filters( 'vc_shortcode_output', $_output, $this, isset( $args[0] ) ? $args[0] : array(), $shortcode );
		}

		return $_output;
	}

	public function paragraph_fix( $content ) {
		$array = array(
			'<p>[' => '[',
			']</p>' => ']',
			']<br />' => ']',
			']<br>' => ']',
		);

		$content = strtr( $content, $array );

		return $content;
	}

	public function a_to_img_magnific_pupup( $content ) {
		$pattern = "/<a(.*?)href=('|\")([^>]*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
		$replacement = '<a$1ref="magnificPopup" href=$2$3.$4$5$6>';
		$content = preg_replace( $pattern, $replacement, $content );

		return $content;
	}

	/**
	 * Remove some of the shortcodes handlers to use native VC shortcodes instead for front-end compatibility
	 */
	public function vc_front_end_compatibility() {
		if ( WP_DEBUG AND $this->inited ) {
			wp_die( 'Shortcodes VC front end compatibility should be provided before the shortcodes init' );
		}
		if ( is_array( $this->config['alias'] ) ) {
			unset(
				$this->config['alias']['vc_tta_accordion'],
				$this->config['alias']['vc_tta_tour']
			);
		}

		foreach ( array( 'vc_tta_tabs', 'vc_tta_accordion', 'vc_tta_tour', 'vc_tta_section' ) as $shortcode_name ) {
			if (
				( $key = array_search( $shortcode_name, $this->config['theme_elements'] ) ) !== FALSE
				AND isset( $this->config['theme_elements'][ $key ] )
			) {
				unset( $this->config['theme_elements'][ $key ] );
			}
		}
	}

}

global $us_shortcodes;
$us_shortcodes = US_Shortcodes::instance();

if ( ! function_exists( 'us_media_templates' ) ) {
	add_action( 'print_media_templates', 'us_media_templates' );
	/**
	 * Add custom options to WP Gallery window
	 */
	function us_media_templates() {
		?>
		<script type="text/html" id="tmpl-us-custom-gallery-setting">
			<label class="setting">
				<span><?php _e( 'Add indents between items', 'us' ) ?></span>
				<input type="checkbox" data-setting="indents">
			</label>
			<label class="setting">
				<span><?php _e( 'Display as', 'us' ) ?>&nbsp;<?php _e( 'Masonry', 'us' ) ?></span>
				<input type="checkbox" data-setting="masonry">
			</label>
			<label class="setting">
				<span><?php _e( 'Show image title and description', 'us' ) ?></span>
				<input type="checkbox" data-setting="meta">
			</label>
		</script>
		<script>
			jQuery().ready( function() {
				if ( wp === undefined || wp.media === undefined ) {
					return;
				}

				// add your shortcode attribute and its default value to the
				// gallery settings list; $.extend should work as well...
				jQuery.extend( wp.media.gallery.defaults, {
					type: 'default_val'
				} );

				// merge default gallery settings template with yours
				wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend( {
					template: function( view ) {
						return wp.media.template( 'gallery-settings' )( view )
							+ wp.media.template( 'us-custom-gallery-setting' )( view );
					}
				} );
			} );
		</script>

		<script type="text/html" id="tmpl-us-custom-image-setting">
			<label class="setting" data-setting="us_attachment_link">
				<span class="name"><?php _e( 'Custom Link', 'us' ); ?></span>
				<input type="text" value="{{ data.us_attachment_link || '' }}"><?php /* value="{{ data.meta.us_attachment_link || '' }}" */ ?>
			</label>

		</script>
		<script>
			jQuery().ready( function() {
				if ( wp === undefined || wp.media === undefined ) {
					return;
				}

				wp.media.view.Attachment.Details = wp.media.view.Attachment.Details.extend( {
					template: function( view ) {
						return wp.media
							.template( 'attachment-details' )( view )
							.replace( 'attachment-info">', 'attachment-info">' + wp.media.template( 'us-custom-image-setting' )( view ) );
					}
				} );
			} );
		</script>
		<?php
	}
}

if ( ! function_exists( 'us_ajax_save_attachment' ) ) {
	add_action( 'wp_ajax_save-attachment', 'us_ajax_save_attachment', 1 );
	function us_ajax_save_attachment() {
		if (
			! isset( $_REQUEST['id'] )
			OR ! isset( $_REQUEST['changes'] )
			OR ! $id = absint( $_REQUEST['id'] )
		) {
			wp_send_json_error();
		}

		check_ajax_referer( 'update-post_' . $id, 'nonce' );
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_send_json_error();
		}

		$changes = $_REQUEST['changes'];
		$post = get_post( $id, ARRAY_A );

		if ( 'attachment' != $post['post_type'] ) {
			wp_send_json_error();
		}

		if ( isset( $changes['us_attachment_link'] ) ) {
			update_post_meta( $post['ID'], 'us_attachment_link', $changes['us_attachment_link'] );
		}
	}
}

if ( ! function_exists( 'us_prepare_attachment_for_js' ) ) {
	add_filter( 'wp_prepare_attachment_for_js', 'us_prepare_attachment_for_js', 10, 3 );
	function us_prepare_attachment_for_js( $response, $attachment, $meta ) {
		$response['us_attachment_link'] = get_post_meta( $attachment->ID, 'us_attachment_link', TRUE );
		return $response;
	}
}

if ( ! function_exists( 'us_image_size_names_choose' ) ) {
	// Add theme image sizes to WP selector in Gallery options
	add_filter( 'image_size_names_choose', 'us_image_size_names_choose' );
	function us_image_size_names_choose( $sizes ) {
		return us_get_image_sizes_list();
	}
}

if ( ! function_exists( 'us_add_design_class_to_shortcode' ) ) {
	add_filter( 'do_shortcode_tag', 'us_add_design_class_to_shortcode', 501, 3 );
	/**
	 * Add unique design class to shortcode
	 *
	 * @param string $output The shortcode output
	 * @param string $tag The shortcode tag name
	 * @param array $atts The shortcode attributes array or empty string
	 * @return string Returns a string with the added css class to the first html element of the output
	 */
	function us_add_design_class_to_shortcode( $output, $tag, $atts ) {
		if ( ! in_array( $tag, array( 'contact-form-7', 'gravityform' ) ) ) {
			return $output;
		}
		// Get a list of specific classes based on shortcode settings
		if ( $css_classes = (string) us_get_specific_classes_by_shortcode( $atts ) ) {
			return preg_replace( '/(<\S+.*?class=[\"|\'].*?)([\"|\'].*?>)/', '$1 '. $css_classes .'$2', $output, /* limit */1 );
		}
		return $output;
	}
}
