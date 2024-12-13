<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Germanized for WooCommerce.
 *
 * Germanized extends WooCommerce to technically match specific german legal conditions.
 * The objective of this plugin is to adapt WooCommerce to the special requirements of german market.
 *
 * @link https://wordpress.org/plugins/woocommerce-germanized
 */
if ( ! class_exists( 'WooCommerce_Germanized' ) ) {
	return FALSE;
}

if ( ! function_exists( 'us_gzd_checkout_has_shortcode' ) ) {
	/**
	 * Check if the checkout page has a set shortcode in the content.
	 *
	 * @param string $shortcode The shortcode name.
	 * @return bool Returns true if the shortcode is found, otherwise false.
	 */
	function us_gzd_checkout_has_shortcode( $shortcode ) {
		if ( empty( $shortcode ) OR ! is_string( $shortcode ) ) {
			return FALSE;
		}
		/**
		 * Get checkout page content
		 * Note: Global variable used elsewhere.
		 */
		global $_us_gzd_checkout_page_content;
		if ( is_null( $_us_gzd_checkout_page_content ) ) {
			$page = get_post( (int) get_option( 'woocommerce_checkout_page_id' ) );
			$_us_gzd_checkout_page_content = ( $page instanceof WP_Post )
				?  $page->post_content
				: '';
		}
		return strpos( $_us_gzd_checkout_page_content, $shortcode ) !== FALSE;
	}
}

if ( ! function_exists( 'us_woocommerce_gzd_order_button_payment_gateway_text' ) ) {
	add_filter( 'woocommerce_gzd_order_button_payment_gateway_text', 'us_woocommerce_gzd_order_button_payment_gateway_text', 501 );
	/**
	 * Filter to adjust the forced order submit button text per gateway.
	 * By default Woo allows gateways to adjust the submit button text.
	 * This behaviour does not comply with the button solution - that is why Germanized adds the
	 * option-based static text by default.
	 *
	 * @param string $button_text The static button text from within the options.
	 * @return string Returns the button html code.
	 */
	function us_woocommerce_gzd_order_button_payment_gateway_text( $button_text ) {
		// Extract button text from shortcode on checkout page.
		if ( us_gzd_checkout_has_shortcode( '[us_checkout_payment' ) ) {
			global $_us_gzd_checkout_page_content;
			$pattern = get_shortcode_regex( array( 'us_checkout_payment' ) );
			if ( preg_match( '/' . $pattern . '/', (string) $_us_gzd_checkout_page_content, $matches ) ) {
				$atts = (array) shortcode_parse_atts( $matches[ /* shortcode atts */3 ] );
				$button_text = us_arr_path( $atts, 'btn_label', /* Default */$button_text );
			}
		}
		return $button_text;
	}
}

if ( ! function_exists( 'us_gzd_woocommerce_review_order_before_submit' ) ) {
	if ( us_gzd_checkout_has_shortcode( '[us_checkout_' ) ) {
		add_action( 'woocommerce_review_order_before_submit', 'us_gzd_woocommerce_review_order_before_submit', 1 );
	}
	/**
	 * Added handlers to display the checkbox and terms manually in the payments block before the send data button.
	 */
	function us_gzd_woocommerce_review_order_before_submit() {
		add_action( 'woocommerce_review_order_before_submit', 'woocommerce_gzd_template_render_checkout_checkboxes', 501 );
		add_action( 'woocommerce_review_order_before_submit', 'woocommerce_gzd_template_checkout_set_terms_manually', 501 );
	}
}

if ( ! function_exists( 'us_gzd_woocommerce_review_order_before_submit_1501' ) ) {
	if ( us_gzd_checkout_has_shortcode( '[us_checkout_' ) ) {
		add_action( 'woocommerce_review_order_before_submit', 'us_gzd_woocommerce_review_order_before_submit_1501', 1501 );
	}
	/**
	 * Removing the handler that will change the data submit button.
	 */
	function us_gzd_woocommerce_review_order_before_submit_1501() {
		remove_filter( 'woocommerce_order_button_html', 'woocommerce_gzd_template_button_temporary_hide', 1500 );
	}
}

if ( ! function_exists( 'us_gzd_woocommerce_checkout_order_review' ) ) {
	if ( us_gzd_checkout_has_shortcode( '[us_checkout_' ) ) {
		add_action( 'woocommerce_checkout_order_review', 'us_gzd_woocommerce_checkout_order_review', 1 );
	}
	/**
	 * Hooks that is called on the checkout page when generating output.
	 *
	 * @link https://github.com/vendidero/woocommerce-germanized/blob/master/includes/wc-gzd-template-hooks.php
	 */
	function us_gzd_woocommerce_checkout_order_review() {

		// Remove the button added by the plugin for submitting an order.
		remove_action(
			'woocommerce_checkout_order_review',
			'woocommerce_gzd_template_order_submit',
			wc_gzd_get_hook_priority( 'checkout_order_submit' )
		);

		// Remove the checkbox added by the plugin and terms_manually.
		remove_action( 'woocommerce_review_order_after_payment', 'woocommerce_gzd_template_render_checkout_checkboxes', 10 );
		remove_action(
			'woocommerce_review_order_after_payment',
			'woocommerce_gzd_template_checkout_set_terms_manually',
			wc_gzd_get_hook_priority( 'checkout_set_terms' )
		);

		// Cancel the moving the payment block and viewing the order to another location.
		remove_action(
			'woocommerce_checkout_order_review',
			'woocommerce_order_review',
			wc_gzd_get_hook_priority( 'checkout_order_review' )
		);
		remove_action(
			'woocommerce_checkout_order_review',
			'woocommerce_checkout_payment',
			wc_gzd_get_hook_priority( 'checkout_payment' )
		);
	}
}
