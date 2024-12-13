<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Post Title element
 *
 * @var $link string Link type: 'post' / 'custom' / 'none'
 * @var $custom_link array
 * @var $tag string 'h1' / 'h2' / 'h3' / 'h4' / 'h5' / 'h6' / 'p' / 'div'
 * @var $color string Custom color
 * @var $icon string Icon name
 * @var $design_options array
 *
 * @var $classes string
 * @var $id string
 */

// Never display a Post title, when it's being output via Post Content element in grid items
global $us_post_content_in_grid_outputs_content;
if ( $us_post_content_in_grid_outputs_content ) {
	return;
}

// Overriding the type of an object based on the availability of terms
global $us_grid_item_type;
if ( $us_elm_context == 'grid' AND $us_grid_item_type == 'term' ) {
	global $us_grid_term;
	$title = $us_grid_term->name;

	if ( ! empty( $show_count ) ) {
		$title .= '&nbsp;<b>' . $us_grid_term->count . '</b>';
	}

} elseif ( $us_elm_context == 'shortcode' ) {

	// Get title based on page type
	if ( is_home() ) {
		if ( ! is_front_page() ) {
			// Get Posts Page Title
			$title = get_the_title( get_option( 'page_for_posts' ) );
		} else {
			$title = us_translate( 'All Posts' );
		}
	} elseif ( is_search() ) {
		$title = sprintf( us_translate( 'Search Results for &#8220;%s&#8221;' ), get_search_query() );
	} elseif ( is_author() ) {
		$title = sprintf( us_translate( 'Posts by %s' ), get_the_author_meta( 'display_name' ) );
	} elseif ( is_tag() ) {
		$title = single_tag_title( '', FALSE );
	} elseif ( is_category() ) {
		$title = single_cat_title( '', FALSE );
	} elseif ( function_exists( 'is_shop' ) AND is_shop() ) {
		$title = woocommerce_page_title( '', FALSE );
	} elseif ( is_tax() ) { // Note: It is important that this check should be after `is_shop`
		$title = single_term_title( '', FALSE );
	} elseif ( is_archive() ) {
		$title = get_the_archive_title();
	} elseif ( is_404() ) {
		$title = us_translate( 'Page not found' );
		// The Events Calendar
	} elseif ( $queried_object = get_queried_object() AND $queried_object->post_type === 'tribe_events' ) {
		$title = $queried_object->post_title;
	} else {
		$title = get_the_title();
	}

} else {
	$title = get_the_title();
}

$_atts['class'] = 'w-post-elm post_title';
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( $align != 'none' ) {
	$_atts['class'] .= ' align_' . $align;
}
if ( $us_elm_context == 'grid' AND get_post_type() == 'product' ) {
	$_atts['class'] .= ' woocommerce-loop-product__title'; // needed for adding to cart
} else {
	$_atts['class'] .= ' entry-title'; // needed for Google structured data
}

// Extra class for link color
if ( $color_link ) {
	$_atts['class'] .= ' color_link_inherit';
}

if ( ! empty( $el_id ) AND $us_elm_context == 'shortcode' ) {
	$_atts['id'] = $el_id;
}

// Link
$link_atts = us_generate_link_atts( $link );

// Apply correct symbols
$title = wptexturize( $title );

// Shorten title length if set
if ( $shorten_length ) {
	$title = wp_html_excerpt( $title, $shorten_length_count, '&hellip;' );
}

// Output the element
$output = '<' . $tag . us_implode_atts( $_atts ) . '>';

if ( ! empty( $icon ) ) {
	$output .= us_prepare_icon_tag( $icon );
}

if ( ! empty( $link_atts['href'] ) ) {
	$output .= '<a' . us_implode_atts( $link_atts ) . '>';
}

$output .= $title;

if ( ! empty( $link_atts['href'] ) ) {
	$output .= '</a>';
}
$output .= '</' . $tag . '>';

echo $output;
