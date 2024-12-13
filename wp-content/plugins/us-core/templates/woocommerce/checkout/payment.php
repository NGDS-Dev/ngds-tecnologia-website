<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output of the `Checkout payment` shortcode if it is on the checkout page
 * Note: This output is required to update totals via AJAX with WooCommerce capabilities
 */
if ( $post = get_post( (int) wc_get_page_id( 'checkout' ) ) ) {
	$pattern = '/' . get_shortcode_regex( array( 'us_checkout_payment' ) ) . '/';
	if ( preg_match( $pattern, $post->post_content, $matches ) ) {
		$output = do_shortcode( $matches[0] );
		// For AJAX requests, removing wrapper due to the specific
		// of `../plugins/woocommerce/assets/js/frontend/checkout.js`
		if ( wp_doing_ajax() ) {
			$output = preg_replace( '/^<[^>]*>(.*?)<[^>]*>$/is', "$1", trim( $output ) );
		}
		echo $output;
	}
}
