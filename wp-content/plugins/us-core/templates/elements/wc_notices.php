<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output WooCommerce Notices
 * Note: All classes and key elements from WooCommerce are retained
 *
 * @see https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-notice-functions.html
 */

if ( ! class_exists( 'woocommerce' ) ) {
	return;
}

$_atts['class'] = 'w-wc-notices woocommerce-notices-wrapper style_' . $style;
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Remove all duplicates before output
if ( ! usb_is_post_preview() ) {
	$changed = FALSE;
	$all_notices = ( isset( WC()->session ) ) ? WC()->session->get( 'wc_notices', array() ) : array();

	foreach ( $all_notices as $notice_type => $notices ) {
		if ( count( $notices ) <= 1 ) {
			continue;
		}
		$unique_notices = array();
		foreach( $notices as $i => $notice ) {
			$_notice = json_encode( $notice );
			if ( ! in_array( $_notice, $unique_notices ) ) {
				$unique_notices[] = $_notice;
			} else {
				unset( $all_notices[ $notice_type ][ $i ] );
				$changed = TRUE;
			}
		}
	}
	if ( $changed ) {
		WC()->session->set( 'wc_notices', $all_notices );
	}
}

?>
<div<?php echo us_implode_atts( $_atts ) ?>><?php
	// DO NOT add EOL after the "<div>" for correct work of ".woocommerce-notices-wrapper:empty"
	if ( usb_is_post_preview() ) {
		echo '<p class="wc-block-components-notice-banner is-info">'. __( 'This is a notice example', 'us' ) .'</p>';
	} elseif ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}
?></div>
