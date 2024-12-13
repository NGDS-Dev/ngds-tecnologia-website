<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output of the `Cart totals` shortcode if it is on the cart page
 * Note: This output is required to update totals via AJAX with WooCommerce capabilities
 */
if ( $post = get_post( (int) wc_get_page_id( 'cart' ) ) ) {
	$pattern = '/' . get_shortcode_regex( array( 'us_cart_totals' ) ) . '/';
	if ( preg_match( $pattern, $post->post_content, $matches ) ) {
		echo do_shortcode( $matches[0] );
	}
}
