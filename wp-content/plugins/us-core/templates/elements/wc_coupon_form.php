<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Checkout Coupon
 * Note: This element is a trigger for the card form, it is required
 * to use the functionality of WooCommerce
 *
 * @var string $placeholder - Placeholder
 * @var string $btn_label - Button Label
 * @var string $btn_style - Button Style
 *
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/checkout/form-coupon.php
 */

if ( ! class_exists( 'woocommerce' ) ) {
	return;
}

if ( ! usb_is_post_preview() ) {
	if (
		is_null( WC()->cart )
		OR WC()->cart->is_empty()
		OR ! wc_coupons_enabled()
	) {
		return;
	}
}

$_atts['class'] = 'w-wc-coupon-form';

// Define if the element is located on the Checkout page
if ( is_checkout() ) {
	$_atts['class'] .= ' is_checkout';
}

// Define if some coupon is applied
if ( ! empty( WC()->cart->get_coupons() ) ) {
	$_atts['class'] .= ' coupon_applied';
}

$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Input atts
$input_atts = array(
	'class' => 'input-text',
	'type' => 'text',
	'placeholder' => $placeholder,
);

?>
<div<?php echo us_implode_atts( $_atts ) ?>>
	<div class="woocommerce-form-coupon coupon">
		<input<?php echo us_implode_atts( $input_atts ) ?>/>
		<button class="w-btn <?php echo us_get_btn_class( $btn_style ); ?>" type="button">
			<span class="w-btn-label"><?php echo esc_html( $btn_label ) ?></span>
		</button>
	</div>
</div>
