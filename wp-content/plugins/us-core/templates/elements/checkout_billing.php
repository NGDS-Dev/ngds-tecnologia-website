<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Checkout Billing
 * Note: This element is a trigger for the card form, it is required
 * to use the functionality of WooCommerce
 *
 * @var string $column - Columns
 * @var string $title - Title (Billing details)
 * @var string $title_size - Title size
 * @var string $fields_gap - Gap between fields
 *
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/checkout/form-checkout.php
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/checkout/form-billing.php
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

$_atts['class'] = 'w-checkout-billing cols_' . $cols;
$_atts['class'] .= isset( $classes ) ? $classes : '';

$_atts['style'] = sprintf( '--fields-gap:%s;', $fields_gap );

// Set title size if set
if ( $title_size ) {
	$_atts['style'] .= sprintf( '--title-size:%s;', $title_size );
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Check cart contents for errors
do_action( 'woocommerce_check_cart_items' );

// Get checkout object
$checkout = WC()->checkout();

if ( empty( $_POST ) AND wc_notice_count( 'error' ) > 0 ) {
	wc_get_template( 'checkout/cart-errors.php', array( 'checkout' => $checkout ) );
	wc_clear_notices();
	return;
}
?>
<div<?php echo us_implode_atts( $_atts ) ?>>

	<?php if ( $checkout->get_checkout_fields() ): ?>
		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
		<?php
			// Remove stdout of fields from the hook
			remove_action( 'woocommerce_checkout_billing', array( $checkout, 'checkout_form_billing' ) );
			do_action( 'woocommerce_checkout_billing' );
		?>

		<div class="woocommerce-billing-fields">
			<h3><?php echo esc_html( $title ); ?></h3>

			<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>
			<div class="woocommerce-billing-fields__field-wrapper">
				<?php
				$fields = $checkout->get_checkout_fields( 'billing' );
				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>
			<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
		</div>

		<?php if ( ! is_user_logged_in() AND $checkout->is_registration_enabled() ) : ?>
			<div class="woocommerce-account-fields">
				<?php if ( ! $checkout->is_registration_required() ) : ?>
					<p class="form-row form-row-wide create-account">
						<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php echo esc_html( us_translate( 'Create an account?', 'woocommerce' ) ); ?></span>
						</label>
					</p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

				<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
					<div class="create-account">
						<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
							<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
						<?php endforeach; ?>
						<div class="clear"></div>
					</div>
				<?php endif; ?>

				<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>

	<?php do_action( 'woocommerce_checkout_shipping' ); ?>

</div>
