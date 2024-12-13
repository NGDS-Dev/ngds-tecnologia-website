<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * CURCY - WooCommerce Multi Currency Premium
 *
 * @link https://villatheme.com/extensions/woo-multi-currency/
 */

if ( ! class_exists( 'WOOMULTI_CURRENCY' ) ) {
	return FALSE;
}

// Disabling update notice for bundled version of the plugin
$transient_name = 'woocommerce-multi-currency_hide';
if ( is_admin() AND ! get_transient( $transient_name ) ) {
	set_transient( $transient_name, 1, 2592000 );
}

// Enqueue minified CSS only when Optimize Assets is disabled (and DEV mode is disabled too)
if (
	! defined( 'US_DEV' )
	AND ! us_get_option( 'optimize_assets' )
	OR usb_is_post_preview()
) {
	add_action( 'wp_enqueue_scripts', 'us_woo_multi_currency_enqueue_styles', 14 );
}
function us_woo_multi_currency_enqueue_styles( $styles ) {
	global $us_template_directory_uri;
	wp_enqueue_style( 'us-multi-currency', $us_template_directory_uri . '/common/css/plugins/us-multi-currency.min.css', array(), US_THEMEVERSION, 'all' );
}
