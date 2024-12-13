<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Cart Totals
 * Note: All classes and key elements from WooCommerce are retained
 *
 * @var string $title - Title
 * @var string $title_size - Title Size
 * @var boolean $show_subtotal - Show Subtotal
 * @var string $btn_label - Button Label
 * @var string $btn_style - Button Style
 * @var string $btn_size - Button Size
 * @var boolean $btn_fullwidth - Button Fullwidth
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

$_atts['class'] = 'w-cart-totals cart_totals';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['style'] = '';

if ( WC()->customer->has_calculated_shipping() ) {
	$_atts['class'] .= ' calculated_shipping';
}
if ( empty( $subtotal ) ) {
	$_atts['class'] .= ' hide_subtotal';
}
if ( ! empty( $btn_fullwidth ) ) {
	$_atts['class'] .= ' btn_fullwidth';
}

// Set sizes if set
if ( $title_size ) {
	$_atts['style'] .= sprintf( '--title-size:%s;', $title_size );
}
if ( $total_size ) {
	$_atts['style'] .= sprintf( '--total-size:%s;', $total_size );
}
if ( $btn_size ) {
	$_atts['style'] .= sprintf( '--checkout-btn-size:%s;', $btn_size );
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Calculate cart before output
if ( isset( $_POST['calc_shipping'] ) ) {
	WC_Shortcode_Cart::calculate_shipping();
}

do_action( 'woocommerce_check_cart_items' ); // Check cart items are valid
WC()->cart->calculate_totals(); // Calc totals.

?>
<div<?php echo us_implode_atts( $_atts ) ?>>
	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2><?php echo esc_html( $title ) ?></h2>

	<table cellspacing="0" class="shop_table shop_table_responsive">

		<tr class="cart-subtotal">
			<th><?php echo esc_html( us_translate( 'Subtotal', 'woocommerce' ) ); ?></th>
			<td data-title="<?php echo esc_attr( us_translate( 'Subtotal', 'woocommerce' ) ); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, FALSE ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() AND WC()->cart->show_shipping() ) : ?>
			<?php
				do_action( 'woocommerce_cart_totals_before_shipping' );
				wc_cart_totals_shipping_html();
				do_action( 'woocommerce_cart_totals_after_shipping' );
			?>

		<?php elseif ( WC()->cart->needs_shipping() AND 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
			<tr class="shipping">
				<th><?php echo esc_html( us_translate( 'Shipping', 'woocommerce' ) ); ?></th>
				<td data-title="<?php echo esc_attr( us_translate( 'Shipping', 'woocommerce' ) ); ?>">
					<?php woocommerce_shipping_calculator(); ?>
				</td>
			</tr>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td data-title="<?php echo esc_attr( $fee->name ); ?>">
					<?php wc_cart_totals_fee_html( $fee ); ?>
				</td>
			</tr>
		<?php endforeach; ?>

		<?php
		if ( wc_tax_enabled() AND ! WC()->cart->display_prices_including_tax() ) {
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text = '';

			if ( WC()->customer->is_customer_outside_base() AND ! WC()->customer->has_calculated_shipping() ) {
				/* translators: %s location. */
				$estimated_text = sprintf( ' <small>' . esc_html( us_translate( '(estimated for %s)', 'woocommerce' ) ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
			}

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
						<td data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
					<td data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>">
						<?php wc_cart_totals_taxes_total_html(); ?>
					</td>
				</tr>
				<?php
			}
		}
		?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>
		<tr class="order-total">
			<th><?php echo esc_html( us_translate( 'Total', 'woocommerce' ) ); ?></th>
			<td data-title="<?php echo esc_attr( us_translate( 'Total', 'woocommerce' ) ); ?>">
				<?php wc_cart_totals_order_total_html(); ?>
			</td>
		</tr>
		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	</table>

	<div class="wc-proceed-to-checkout">
		<?php
		// Remove standard button output
		remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
		do_action( 'woocommerce_proceed_to_checkout' );
		?>
		<a href="<?php echo esc_attr( wc_get_checkout_url() ) ?>" class="w-btn <?php echo us_get_btn_class( $btn_style ) ?>">
			<span class="w-btn-label"><?php echo esc_html( us_replace_dynamic_value( $btn_label ) ) ?></span>
		</a>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>
</div>
