<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Account Content
 * Note: All classes and key elements from WooCommerce are retained
 *
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/myaccount/my-account.php
 */

if (
	! class_exists( 'woocommerce' )
	OR ! is_user_logged_in()
) {
	return;
}

$_atts['class'] = 'w-account woocommerce woocommerce-MyAccount-content';
$_atts['class'] .= isset( $classes ) ? $classes : '';

// Generate class based on the Account endpoint
if ( ! $wc_current_endpoint = WC()->query->get_current_endpoint() ) {
	$wc_current_endpoint = 'dashboard';
}
$_atts['class'] .= ' for_' . $wc_current_endpoint;

// Hide the default Dashboard content
if ( ! $dashboard ) {
	$_atts['class'] .= ' hide_dashboard';
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
} ?>

<div<?php echo us_implode_atts( $_atts ) ?>>
	<?php
	do_action( 'woocommerce_account_content' );
	?>
</div>
