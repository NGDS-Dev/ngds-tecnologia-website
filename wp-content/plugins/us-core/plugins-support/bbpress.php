<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * bbPress Support
 *
 * @link https://bbpress.org/
 */

if ( ! class_exists( 'bbPress' ) ) {
	return;
}

add_action( 'wp_enqueue_scripts', 'us_bbpress_enqueue_styles', 14 );
function us_bbpress_enqueue_styles( $styles ) {
	wp_dequeue_style( 'bbp-default' );

	global $us_template_directory_uri;
	$min_ext = defined( 'US_DEV' ) ? '' : '.min';
	if (
		defined( 'US_DEV' )
		OR ! us_get_option( 'optimize_assets' )
		OR usb_is_post_preview()
	) {
		wp_enqueue_style( 'us-bbpress', $us_template_directory_uri . '/common/css/plugins/bbpress' . $min_ext . '.css', array(), US_THEMEVERSION, 'all' );
	}
}

// Remove Forum summaries
add_filter( 'bbp_get_single_forum_description', '__return_false', 10, 2 );
add_filter( 'bbp_get_single_topic_description', '__return_false', 10, 2 );

// Adding BBPress post type support in grids for regular visitors on front
if (
	function_exists( 'bbp_get_forum_post_type' )
	AND function_exists( 'bbp_get_forum_post_type_labels' )
) {
	add_filter( 'us_grid_available_post_types', 'us_add_forums_to_grid_available_post_types' );
	function us_add_forums_to_grid_available_post_types( $posts_types ) {
		$post_type = bbp_get_forum_post_type();
		if ( ! isset( $posts_types[ $post_type ] ) ) {
			$labels = bbp_get_forum_post_type_labels();
			$posts_types[ $post_type ] = $labels['name'] . ' (' . $post_type . ')';
		}

		return $posts_types;
	}
}

if ( ! function_exists( 'us_bbp_after_theme_compat_reset_post_parse_args' ) ) {
	/**
	 * Add filter to override global objects
	 *
	 * Filter mask: `bbp_after_{theme_compat_reset_post}_parse_args`
	 * @link https://github.com/bbpress/bbPress/blob/trunk/src/includes/common/functions.php#L1641
	 */
	add_filter( 'bbp_after_theme_compat_reset_post_parse_args', 'us_bbp_after_theme_compat_reset_post_parse_args', 1, 1 );

	/**
	 * Cancels redefinition of global objects
	 *
	 * Note: bbPress hard and force redefinition of global objects, which breaks
	 * standard methods WordPress.
	 *
	 * @link https://github.com/bbpress/bbPress/blob/trunk/src/includes/core/theme-compat.php#L362
	 *
	 * @param array $reset_post A specific post data array that does not exist
	 * @return mixed
	 */
	function us_bbp_after_theme_compat_reset_post_parse_args( $reset_post ) {
		if (
			is_archive()
			AND function_exists( 'us_get_page_area_id' )
			AND us_get_page_area_id( 'content' )
		) {
			return NULL;
		}
		return $reset_post;
	}
}
