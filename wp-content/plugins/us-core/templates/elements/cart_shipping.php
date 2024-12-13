<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Cart Shipping
 * Note: All classes and key elements from WooCommerce are retained
 *
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/cart/cart-totals.php
 */

if ( ! class_exists( 'woocommerce' ) ) {
	return;
} elseif ( ! usb_is_post_preview() ) {
	if ( is_null( WC()->cart ) OR WC()->cart->is_empty() ) {
		return;
	}
	if ( function_exists( 'is_cart' ) AND ! is_cart() ) {
		return;
	}
}

$classes = isset( $classes )
	? ' ' . $classes
	: '';

// Calculate cart before output
if ( isset( $_POST['calc_shipping'] ) ) {
	WC_Shortcode_Cart::calculate_shipping();
}

do_action( 'woocommerce_check_cart_items' ); // Check cart items are valid
WC()->cart->calculate_totals(); // Calc totals.

?>
<div class="w-cart-shipping<?php echo $classes; ?>">
	<table cellspacing="0" class="shop_table shop_table_responsive">
		<?php
			if ( WC()->cart->needs_shipping() AND WC()->cart->show_shipping() ) {
				do_action( 'woocommerce_cart_totals_before_shipping' );
				wc_cart_totals_shipping_html();
				do_action( 'woocommerce_cart_totals_after_shipping' );

			} elseif ( WC()->cart->needs_shipping() AND 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) {
			?>
			<tr class="shipping">
				<th><?php echo esc_html( us_translate( 'Shipping', 'woocommerce' ) ); ?></th>
				<td data-title="<?php echo esc_attr( us_translate( 'Shipping', 'woocommerce' ) ); ?>">
					<?php woocommerce_shipping_calculator(); ?>
				</td>
			</tr>
			<?php
			}
		?>
	</table>
</div>
