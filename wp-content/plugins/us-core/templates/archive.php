<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * The template for displaying Archive Pages
 */

get_header();

?>
<main id="page-content" class="l-main"<?php echo ( us_get_option( 'schema_markup' ) ) ? ' itemprop="mainContentOfPage"' : ''; ?>>
	<?php
	if ( us_get_option( 'enable_sidebar_titlebar', 0 ) ) {

		// Titlebar, if it is enabled in Theme Options
		us_load_template( 'templates/titlebar' );

		// START wrapper for Sidebar
		us_load_template( 'templates/sidebar', array( 'place' => 'before' ) );
	}

	// Check if a Page Template is set...
	if ( $content_area_id = us_get_page_area_id( 'content' ) AND get_post_status( $content_area_id ) != FALSE ) {
		us_load_template( 'templates/content' );

		// ...if not, use the default output
	} else {
	?>
	<section class="l-section height_<?php echo us_get_option( 'row_height', 'medium' ); ?>">
		<div class="l-section-h i-cf">

			<?php
			do_action( 'us_before_archive' );

			// Use Grid element with default values and "Regular" pagination
			us_load_template( 'templates/us_grid/listing', array( 'pagination' => 'regular' ) );

			do_action( 'us_after_archive' );
			?>

		</div>
	</section>
	<?php
	}
	if ( us_get_option( 'enable_sidebar_titlebar', 0 ) ) {
		// AFTER wrapper for Sidebar
		us_load_template( 'templates/sidebar', array( 'place' => 'after' ) );
	}
	?>
</main>

<?php
get_footer();
