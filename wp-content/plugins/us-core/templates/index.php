<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * The template for displaying the Posts page
 */

$_posts_page_ID = us_get_page_for_posts();

// Output the content of a page, which is set in Settings > Reading > Posts page
if ( $posts_page = get_post( $_posts_page_ID ) AND ! empty( $posts_page->post_content ) ) {

	// If the page has a translated version, use it instead
	if ( has_filter( 'us_tr_object_id' ) ) {
		$posts_page = get_post( (int) apply_filters( 'us_tr_object_id', $posts_page->ID, 'page', TRUE ) );
	}

	get_header();

	// Load custom css of this page
	if ( is_object( $posts_page ) ) {
		us_output_design_css( [ $posts_page ] );
	}
	?>
	<main id="page-content" class="l-main"<?php echo ( us_get_option( 'schema_markup' ) ) ? ' itemprop="mainContentOfPage"' : ''; ?>>

		<?php
		do_action( 'us_before_page' );

		if ( us_get_option( 'enable_sidebar_titlebar', 0 ) ) {

			// Titlebar, if it is enabled in Theme Options
			us_load_template( 'templates/titlebar' );

			// START wrapper for Sidebar
			us_load_template( 'templates/sidebar', array( 'place' => 'before' ) );
		}

		// Check if a Page Template is set...
		if ( $content_area_id = us_get_page_area_id( 'content' ) AND get_post_status( $content_area_id ) != FALSE ) {
			us_load_template( 'templates/content' );

		} else {
			us_open_wp_query_context();

			us_add_page_shortcodes_custom_css( $posts_page->ID );

			us_close_wp_query_context();

			// Setting Posts page ID as $us_page_block_id for grid shortcodes
			us_add_to_page_block_ids( $posts_page->ID );

			$posts_page_content = $posts_page->post_content;

			// If the page content doesn't have any grid, add one with items of the current query
			if ( strpos( $posts_page_content, '[us_grid ' ) === FALSE ) {
				$posts_page_content .= '[vc_row][vc_column][us_grid post_type="current_query" pagination="regular"][/vc_column][/vc_row]';
			}

			echo apply_filters( 'the_content', $posts_page_content );

			us_remove_from_page_block_ids();
		}

		if ( us_get_option( 'enable_sidebar_titlebar', 0 ) ) {
			// AFTER wrapper for Sidebar
			us_load_template( 'templates/sidebar', array( 'place' => 'after' ) );
		}

		do_action( 'us_after_page' );
		?>

	</main>
	<?php

	get_footer();

	// Output default archive layout
} else {
	us_load_template( 'templates/archive' );
}
