<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * AJAX pagination for the Gallery shortcode
 */
if ( ! function_exists( 'us_ajax_gallery' ) ) {
	add_action( 'wp_ajax_nopriv_us_ajax_gallery', 'us_ajax_gallery' );
	add_action( 'wp_ajax_us_ajax_gallery', 'us_ajax_gallery' );
	function us_ajax_gallery() {

		// Passing IDs for the next page via AJAX ...
		// ... and setting source = 'ids' in shortcode params to retrieve images for the IDs
		$template_vars =array_merge(
			us_shortcode_atts(
				array_merge(
					array( 'source' => 'ids', ),
					us_maybe_get_post_json( 'template_vars' )
				),
				'us_gallery'
			),
			// This parameter is required by the gallery template
			array( 'shortcode_base' => 'us_gallery', )
		);


		us_load_template( 'templates/elements/gallery', $template_vars );

		die;
	}
}
