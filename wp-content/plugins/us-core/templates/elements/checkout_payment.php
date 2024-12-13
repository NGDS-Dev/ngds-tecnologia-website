<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Checkout Payment
 * Note: This element is a trigger for the card form, it is required
 * to use the functionality of WooCommerce
 *
 * @var string $btn_label - Button Label
 * @var string $btn_style - Button Style
 * @var string $btn_size - Button Size
 * @var bool $btn_fullwidth - Button Fullwidth
 *
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/checkout/form-checkout.php
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/checkout/payment.php
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

$_atts['class'] = 'w-checkout-payment';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['class'] .= ' payments-style_' . $payments_style;

if ( ! empty( $btn_fullwidth ) ) {
	$_atts['class'] .= ' btn_fullwidth';
}

// Set sizes if set
if ( $btn_size ) {
	$_atts['style'] = sprintf( '--btn-size:%s;', $btn_size );
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Get available gateways
if ( WC()->cart->needs_payment() ) {
	$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
	WC()->payment_gateways()->set_current_gateway( $available_gateways );
} else {
	$available_gateways = array();
}

// Order button HTML
$_btn_atts = array(
	'class' => 'w-btn ' . us_get_btn_class( $btn_style ),
	'id' => 'place_order',
	'name' => 'woocommerce_checkout_place_order',
	'type' => 'submit',
	'value' => $btn_label,
);
$_button_html = '<button' . us_implode_atts( $_btn_atts ) . '>';
$_button_html .= '<span class="w-btn-label">' . strip_tags( $btn_label ) . '</span>';
$_button_html .= '</button>';

?>
<div<?php echo us_implode_atts( $_atts ) ?>>

	<?php
		if ( ! wp_doing_ajax() ) {
			do_action( 'woocommerce_review_order_before_payment' );
		}
	?>

	<div id="payment" class="woocommerce-checkout-payment">
		<?php if ( WC()->cart->needs_payment() ) : ?>
			<ul class="wc_payment_methods payment_methods methods">
				<?php
				if ( ! empty( $available_gateways ) ) {
					foreach ( $available_gateways as $gateway ) {
						wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
					}
				} else {
					echo '<li class="wc-block-components-notice-banner is-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html( us_translate( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ) : esc_html( us_translate( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ) ) . '</li>';
				}
				?>
			</ul>
		<?php endif; ?>
		<div class="form-row place-order">
			<noscript>
				<?php
				/* translators: $1 and $2 opening and closing emphasis tags respectively */
				printf( esc_html( us_translate( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ) ), '<em>', '</em>' );
				?>
				<br/><button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php echo esc_attr( us_translate( 'Update totals', 'woocommerce' ) ); ?>"><?php echo esc_html( us_translate( 'Update totals', 'woocommerce' ) ); ?></button>
			</noscript>

			<?php wc_get_template( 'checkout/terms.php' ); ?>

			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

			<?php echo apply_filters( 'woocommerce_order_button_html', $_button_html ); ?>

			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

			<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
		</div>
	</div>

	<?php
		if ( ! wp_doing_ajax() ) {
			do_action( 'woocommerce_review_order_after_payment' );
		}
	?>
</div>
