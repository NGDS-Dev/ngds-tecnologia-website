<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * This class describes the functionality of the panel
 */
final class USBuilder_Panel {

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
	 * @access public
	 */
	function __construct() {

		// Filter to get the panel title
		add_filter( 'usb_get_panel_title', array( $this, 'get_title' ), 1, 1 );

		// Filter to get the panel content
		add_filter( 'usb_get_panel_content', array( $this, 'get_content' ), 1, 1 );

	}

	/**
	 * Get all titles in 'key => value'
	 *
	 * @access public
	 * @return array Returns an array of all headers for the panel
	 */
	static function get_titles() {
		return array(
			'page_custom_css' => __( 'Page Custom CSS', 'us' ),
			'page_settings' => __( 'Page Settings', 'us' ),
			'paste_row' => __( 'Paste Row/Section', 'us' ),
			'site_settings' => __( 'Site Settings', 'us' ),
		);
	}

	/**
	 * Get current panel title
	 *
	 * @access public
	 * @param string $title The panel title
	 * @return string
	 */
	function get_title( $title = '' ) {
		$panel_titles = $this->get_titles();

		// For "Site Settings"
		if ( usb_is_site_settings() ) {
			$title = us_arr_path( $panel_titles, 'site_settings', $title );
		}

		// For current panel
		if (
			$active_panel_name = usb_get_active_panel_name()
			AND! empty( $panel_titles[ $active_panel_name ] )
		) {
			$title = $panel_titles[ $active_panel_name ];
		}

		return (string) $title;
	}

	/**
	 * Get current panel content
	 *
	 * @access public
	 * @return string Returns the content of the panel depending on the context
	 */
	function get_content() {
		// Return the content for site settin
		if ( usb_is_site_settings() ) {
			return $this->get_content_for_site_settings();
		}
		// Return the content for post editing
		return $this->get_content_for_post_editing();
	}

	/**
	 * Get the content for post editing
	 *
	 * @access private
	 * @return string The content for post editing
	 */
	private function get_content_for_post_editing() {
		$post_id = usb_get_post_id();

		// Get data for page builder
		$fieldsets = $elms_categories = array();

		// Get all elements available in the theme
		foreach ( us_config( 'shortcodes.theme_elements', array(), TRUE ) as $elm_filename ) {
			if ( $elm_config = us_config( "elements/$elm_filename", array() ) ) {
				// Ignore elements which are not available via condition
				if ( isset( $elm_config['place_if'] ) AND ! $elm_config['place_if'] ) {
					continue;
				}
				$fieldsets[ $elm_filename ] = $elm_config;
				// Get element icon
				$elm_icon = us_arr_path( $elm_config, 'icon', '' );
				// Create elements list
				$elm_filename = us_get_shortcode_full_name( $elm_filename );
				$elms_categories[ us_arr_path( $elm_config, 'category', '' ) ][ $elm_filename ] = array(
					'hide_for_post_ids' => us_arr_path( $elm_config, 'hide_for_post_ids' ),
					'hide_on_adding_list' => us_arr_path( $elm_config, 'hide_on_adding_list', '' ),
					'icon' => $elm_icon,
					'is_container' => us_arr_path( $elm_config, 'is_container', FALSE ),
					'show_for_post_types' => us_arr_path( $elm_config, 'show_for_post_types' ),
					'title' => us_arr_path( $elm_config, 'title', $elm_filename ),
				);
			}
		}

		// Shortcodes that contain inner content for the editor as a value
		foreach ( $fieldsets as $elm_name => $fieldset ) {
			foreach ( us_arr_path( $fieldset, 'params', array() ) as $param_name => $options ) {

				// Get default values for the edited content, if any
				if ( $param_name == 'content' OR $options['type'] == 'editor' ) {
					$elm_name = us_get_shortcode_name( $elm_name );
				}

				// For the Group default value transform array to a string (compatibility with WPBakery builder)
				if ( $options['type'] == 'group' AND ! empty( $options['std'] ) ) {
					$elm_name = us_get_shortcode_name( $elm_name );
				}

				// Remove prefixes needed for compatibility from Visual Composer
				if ( ! empty( $options['type'] ) ) {
					$fieldsets[ $elm_name ]['params'][ $param_name ]['type'] = us_get_shortcode_name( $options['type'] );
				}
			}

			// All fieldsets that are loaded via AJAX are excluded from the output
			if ( ! us_arr_path( $fieldset, 'usb_preload', FALSE ) ) {
				unset( $fieldsets[ $elm_name ] );
			}
		}

		// Returns everything needed to post editing
		return us_get_template( 'builder/templates/post_editing', array(
			'elms_categories' => $elms_categories,
			'fieldsets' => $fieldsets,
			'post_type' => get_post_type( $post_id ),
		) );
	}

	/**
	 * Get the content for site settings
	 *
	 * @return string The content for site settings
	 */
	private function get_content_for_site_settings() {
		return us_get_template( 'builder/templates/site_settings' );
	}
}
