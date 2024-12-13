<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Add to cart element
 */

if ( ! class_exists( 'woocommerce' ) ) {
	return;
}

global $product, $us_grid_item_type;

// Never output this element inside Grids with terms
if ( $us_elm_context === 'grid' AND $us_grid_item_type === 'term' ) {
	return;
}

$classes = isset( $classes ) ? $classes : '';

// Output WooCommerce Add to cart
if ( $us_elm_context == 'shortcode' ) {

	// Do not output this shortcode on the front-end of non-product pages
	if ( ! $product AND ! usb_is_post_preview() ) {
		return;
	}

	$_atts['class'] = 'w-post-elm add_to_cart';
	$_atts['class'] .= $classes;

	// For correct button placeholder preview in Live builder
	if ( usb_is_template_preview() ) {
		$_atts['class'] .= ' woocommerce';
	}

	if ( ! empty( $el_id ) ) {
		$_atts['id'] = $el_id;
	}

	echo '<div' . us_implode_atts( $_atts ) . '>';
	if ( is_object( $product ) AND method_exists( $product, 'get_type' ) ) {
		/*
		 * Checking if both woocommerce_output_all_notices and wc_print_notices functions exist
		 * because woocommerce_output_all_notices uses wc_print_notices,
		 * however these functions being included separately
		 */
		woocommerce_template_single_add_to_cart();
		if (
			function_exists( 'woocommerce_output_all_notices' )
			AND function_exists( 'wc_print_notices' )
		) {
			woocommerce_output_all_notices();
		}

		// Output placeholder for Live Builder for Page Template / Reusable Block
	} elseif ( usb_is_template_preview() ) {
		echo '<div class="button add_to_cart_button">' . us_translate( 'Add to cart', 'woocommerce' ) . '</div>';
	}
	echo '</div>';

} elseif ( function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {
	add_filter( 'woocommerce_product_add_to_cart_text', 'us_add_to_cart_text', 99, 2 );
	add_filter( 'woocommerce_loop_add_to_cart_link', 'us_add_to_cart_text_replace', 99, 3 );

	if ( us_design_options_has_property( $css, 'border-radius' ) ) {
		$classes .= ' has_border_radius';
	}
	if ( us_design_options_has_property( $css, 'font-size' ) ) {
		$classes .= ' has_font_size';
	}
	if ( empty( $view_cart_link ) ) {
		$classes .= ' no_view_cart_link';
	}

	echo '<div class="w-btn-wrapper woocommerce' . $classes . '">';
	woocommerce_template_loop_add_to_cart();
	echo '</div>';

	remove_filter( 'woocommerce_product_add_to_cart_text', 'us_add_to_cart_text', 99 );
	remove_filter( 'woocommerce_loop_add_to_cart_link', 'us_add_to_cart_text_replace', 99 );
}
