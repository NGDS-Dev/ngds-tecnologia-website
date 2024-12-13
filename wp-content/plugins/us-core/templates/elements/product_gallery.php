<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * WooCommerce Product gallery
 */

if ( ! class_exists( 'woocommerce' ) ) {
	return;
}

$_atts['class'] = 'w-post-elm product_gallery';
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( ! empty( $el_id ) AND $us_elm_context == 'shortcode' ) {
	$_atts['id'] = $el_id;
}

// Output the element
echo '<div' . us_implode_atts( $_atts ) . '>';

// In Live Builder for Reusable Block / Page Template show a placeholder
if ( usb_is_template_preview() ) {
	echo us_get_img_placeholder();

} elseif ( is_product() ) {
	wc_get_template( 'single-product/product-image.php' );
}

echo '</div>';
