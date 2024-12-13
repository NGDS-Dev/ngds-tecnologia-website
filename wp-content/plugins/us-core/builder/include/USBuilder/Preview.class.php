<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * This class describes setting up a site as a preview
 */
final class USBuilder_Preview {

	/**
	 * @access public
	 */
	function __construct() {

		// Hide admin bar
		add_filter( 'show_admin_bar', '__return_false' );

		// Add styles and scripts on the preview
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_preview_assets' ) );

		// Handler for outputting data before closing tag '</head>'
		add_action( 'us_before_closing_head_tag', array( $this, 'us_before_closing_head_tag' ), 501 );

		// Export data for the builder (templates, objects, translations, etc.)
		add_action( 'us_after_footer', array( $this, 'export_builder_data' ) );

		// Setting up a preview to page edit
		if ( usb_is_post_preview() ) {

			// Disable output of WPB custom styles
			add_filter( 'vc_post_custom_css', '__return_false', 1 );

			// Cancel the output of styles for content on the preview page
			add_filter( 'us_is_output_design_css_for_content', '__return_false', 1 );

			// Ignore the Auto Optimize (all front-end assets will be loaded in the preview)
			add_filter( 'usof_get_option_optimize_assets', '__return_false' );

			// Disable a privacy block for Map and Video Player elements
			add_filter( 'us_show_privacy_block', '__return_false', 9999 );

			// This is an instance of the class for working with shortcodes
			$shortcode = USBuilder_Shortcode::instance();

			// After setting up the WP environment, install the page data in builder
			add_action( 'wp', array( $shortcode, 'set_page_content' ), 1 );

			// Prepares shortcodes for display on the preview page
			add_action( 'the_content', array( $shortcode, 'prepare_text' ), 1, 1 );

			// Add data-usbid attribute to html when output shortcode result
			add_filter( 'do_shortcode_tag', array( $shortcode, 'add_usbid_to_html' ), 9999, 3 );

			// Export page content, page metadata, custom css, fields data for Builder
			add_action( 'wp_footer', array( $shortcode, 'export_page_data' ), 9999 );

			// Add a handler to the page components header, footer, etc.
			add_filter( 'us_header_atts', array( $this, 'page_component' ), 1, 2 );
			add_filter( 'us_footer_atts', array( $this, 'page_component' ), 1, 2);

			// Substitution of metadata to preview changes before saving
			add_filter( 'usof_meta', array( $this, 'meta_to_preview' ), 1, 2 );

			// Disabling header / footer for Reusable Block and Page Template preview pages
			add_action( 'us_get_page_area_id', array( $this, 'get_page_area_id_filter' ) );
		}
	}

	/**
	 * Filter for Page Area ID (like header, footer, etc).
	 * Disables all page areas except own content of edited post for the Builder preview page ...
	 * ... while editing Reusable Block and Page Template
	 *
	 * Used in 'us_get_page_area_id' fiter
	 *
	 * @param int $area_id Original area ID
	 * @return int / string
	 */
	function get_page_area_id_filter( $area_id ) {
		if ( in_array( get_post_type(), array( 'us_page_block', 'us_content_template' ) ) ) {
			return '';
		}
		return $area_id;
	}

	/**
	 * Add styles and scripts on the preview
	 *
	 * @access public
	 */
	function enqueue_preview_assets() {
		// Enqueue USOF JS files separately, when US_DEV is set
		$min = '';
		if ( ! defined( 'US_DEV' ) ) {
			$min = '.min';
		}
		// Preview of "Site Settings"
		if ( usb_is_site_settings_preview() ) {
			wp_enqueue_script( 'usb-site-preview-js', US_BUILDER_URL . '/assets/js/site-preview' . $min . '.js', array( 'jquery' ), US_CORE_VERSION );

			// Preview of "Page Builder"
		} else {
			wp_enqueue_script( 'usb-page-preview-js', US_BUILDER_URL . '/assets/js/builder-preview' . $min . '.js', array( 'jquery' ), US_CORE_VERSION );
			wp_enqueue_style( 'usb-page-preview-css', US_BUILDER_URL . '/assets/css/builder-preview' . $min . '.css', array(), US_CORE_VERSION );
		}
	}

	/**
	 * Handler for outputting data before closing tag '</head>'
	 *
	 * @access public
	 */
	function us_before_closing_head_tag() {
		// The output typography css for a page
		// Note: Dedicated tag for updating on the JS side!
		if ( $inline_css = us_get_typography_inline_css() ) {
			echo sprintf( '<style id="%s">%s</style>', US_BUILDER_TYPOGRAPHY_TAG_ID, $inline_css );
		}
	}

	/**
	 * Handler for modifying page components like header, footer, etc
	 *
	 * @access public
	 * @param array $atts The component attributes
	 * @param int $post_id The сomponent unique ID
	 * @return array Returns extended attributes including attributes for us-builder
	 */
	function page_component( $atts, $post_id ) {

		// Determine and set component ID
		$current_filter = current_filter();
		if ( strpos( $current_filter, 'us_header' ) === 0 ) {
			$atts['data-usbid'] = 'us_header:1';
			$atts['data-usb-highlight'] = us_json_encode( array(
				'edit_label' => _x( 'Edit Header', 'site top area', 'us' ),
				'edit_permalink' => get_edit_post_link( $post_id, /* context */'' ),
				'disable_controls' => TRUE, // sisabling control buttons in the highlight
			) );
		}
		elseif ( strpos( $current_filter, 'us_footer' ) === 0 ) {
			$atts['data-usbid'] = 'us_footer:1';
			$atts['data-usb-highlight'] = us_json_encode( array(
				'edit_label' => __( 'Edit Footer', 'us' ),
				'edit_permalink' => (string) usb_get_edit_link( $post_id ),
				'disable_controls' => TRUE, // disabling control buttons in the highlight
			) );
		}

		return $atts;
	}

	/**
	 * Substitution of metadata to preview changes before saving
	 *
	 * @access public
	 * @param null|array|string $value The value get_metadata() should return a single metadata value, or an array of values
	 * @param string $meta_key Meta key
	 * @return array|null|string The attachment metadata value, array of values, or null
	 */
	function meta_to_preview( $value, $meta_key ) {
		return us_arr_path( $_REQUEST, "meta.{$meta_key}", /* default value */ $value );
	}

	/**
	 * Returns data for the builder (templates, objects, translations, etc.)
	 *
	 * @access public
	 */
	function export_builder_data() {
		$jscode = '';

		// Data for "Page Builder"
		if ( usb_is_post_preview() ) {
			// Template for highlight an element on hover
			echo '
			<!-- Begin builder hover -->
			<div class="usb-hover">
				<div class="usb-hover-panel">
					<div class="usb-hover-panel-name">Element</div>
					<a class="usb-hover-panel-edit" href="javascript:void(0)" target="_blank"></a>
					<div class="usb-hover-panel-btn type_copy ui-icon_copy" title="' . esc_attr( us_translate( 'Copy' ) ) . '"></div>
					<div class="usb-hover-panel-btn type_paste ui-icon_paste" title="' . esc_attr( us_translate( 'Paste' ) ) . '"></div>
					<div class="usb-hover-panel-btn ui-icon_duplicate" title="' . esc_attr( __( 'Duplicate', 'us' ) ) . '"></div>
					<div class="usb-hover-panel-btn ui-icon_delete" title="' . esc_attr( us_translate( 'Delete' ) ) . '"></div>
				</div>
				<div class="usb-hover-h"></div>
			</div>
			<!-- End builder hover -->';

			// Methods for element initialization
			$js_init_methods = array();
			foreach ( us_config( 'shortcodes.theme_elements', array(), /* reload */ TRUE ) as $elm_filename ) {
				if ( $elm_config = us_config( "elements/$elm_filename", array() ) ) {
					// Ignore elements which are not available via condition
					if ( isset( $elm_config['place_if'] ) AND ! $elm_config['place_if'] ) {
						continue;
					}
					$elm_filename = us_get_shortcode_full_name( $elm_filename );
					// Add function for JS initialization of preview from `usb_init_js` option
					if ( ! empty( $elm_config['usb_init_js'] ) ) {
						$js_init_methods[] = $elm_filename . ':function( $elm ){' . (string) $elm_config['usb_init_js'] . '}';
					}

				}
			}
			$jscode .= 'window.$usbdata.elmsInitJSMethods = {' . implode( ",", $js_init_methods ) . '};';
		}

		// Data for "Site Settings"
		if ( usb_is_site_settings_preview() ) {
			// Remove the filter insert demo options and get real
			remove_filter( 'usof_load_options_once', 'usb_insert_demo_options_in_usof_options' );
			usof_load_options_once(); // force load options

			$jscode .= 'window.$usbdata.liveOptions = ' . json_encode( us_get_live_options() ) . ';';
		}

		// Exporting data to a single space
		if ( ! empty( $jscode )) {
			echo '<script>window.$usbdata = window.$usbdata || {};' . $jscode . '</script>';
		}
	}
}
