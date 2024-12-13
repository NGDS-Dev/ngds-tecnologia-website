<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Login, Registration, Lost Password forms
 * Note: All classes and key elements from WooCommerce are retained
 *
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/myaccount/my-account.php
 */

if ( ! class_exists( 'woocommerce' ) ) {
	return;
}

// Disable output on the Checkout page to avoid conflicts with the checkout form
if ( function_exists( 'is_checkout' ) AND is_checkout() ) {
	if ( usb_is_post_preview() ) {
		echo '<div class="w-account-login">This element can\'t be used on the Checkout page.</div>';
	}

	return;
}

$_atts['class'] = 'w-account-login woocommerce style_' . $style;
$_atts['class'] .= isset( $classes ) ? $classes : '';

// Set sizes if set
if ( $title_size ) {
	$_atts['style'] = sprintf( '--title-size:%s;', $title_size );
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Output the default WooCommerce logic for non-logged users
if ( ! is_user_logged_in() ) {
	?>
	<div<?php echo us_implode_atts( $_atts ) ?>>
		<?php
		if ( is_lost_password_page() ) {

			// Use the default WooCommerce function
			WC_Shortcode_My_Account::lost_password();

		} else {
			wc_get_template( 'myaccount/form-login.php' );
		}
		?>
	</div>
	<?php

	// Output the default Login form for Live Builder Preview
} elseif ( usb_is_post_preview() ) {
	?>
	<div<?php echo us_implode_atts( $_atts ) ?>>
		<?php wc_get_template( 'myaccount/form-login.php' ) ?>
	</div>
	<?php
}
