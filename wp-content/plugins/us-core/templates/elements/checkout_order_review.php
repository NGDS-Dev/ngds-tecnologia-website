<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Checkout Order Review
 * Note: This element is a trigger for the card form, it is required
 * to use the functionality of WooCommerce
 *
 * @var bool $show_products_list - Show products list
 * @var bool $show_subtotal - Show Subtotal
 *
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/checkout/form-checkout.php
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/checkout/review-order.php
 */

if ( ! class_exists( 'woocommerce' ) ) {
	return;
} elseif ( ! usb_is_post_preview() ) {
	if ( is_null( WC()->cart ) OR WC()->cart->is_empty() ) {
		return;
	}
	if ( function_exists( 'is_checkout' ) AND ! is_checkout() ) {
		return;
	}
}

$_atts['class'] = 'w-checkout-order-review woocommerce-checkout-review-order';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['style'] = '';

if ( empty( $subtotal ) ) {
	$_atts['class'] .= ' hide_subtotal';
}
if ( empty( $products_list ) ) {
	$_atts['class'] .= ' hide_products_list';
}

// Set sizes if set
if ( $title_size ) {
	$_atts['style'] .= sprintf( '--title-size:%s;', $title_size );
}
if ( $total_size ) {
	$_atts['style'] .= sprintf( '--total-size:%s;', $total_size );
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}


// Calculate shipping before totals. This will ensure any shipping methods
// that affect things like taxes are chosen prior to final totals being calculated.
WC()->cart->calculate_shipping();
WC()->cart->calculate_totals();

?>
<div<?php echo us_implode_atts( $_atts ) ?>><?php

	do_action( 'woocommerce_checkout_before_order_review_heading' );

	echo '<h3>' . esc_html( $title ) . '</h3>';

	do_action( 'woocommerce_checkout_before_order_review' );

	// Removing actions of elements that are now rendered in shortcodes
	remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
	remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
	do_action( 'woocommerce_checkout_order_review' );

	if ( function_exists( 'wc_get_template' ) ) {
		wc_get_template(
			'checkout/review-order.php',
			array( 'checkout' => WC()->checkout() )
		);
	}

	do_action( 'woocommerce_checkout_after_order_review' );
	?>
</div>
