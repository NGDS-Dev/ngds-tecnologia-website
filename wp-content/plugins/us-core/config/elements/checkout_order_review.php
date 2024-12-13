<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: checkout_order_review
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

$hide_for_post_ids = array();
if ( 
	function_exists( 'wc_get_page_id' )
	AND us_is_elm_editing_page()
) {
	$hide_for_post_ids[] = wc_get_page_id( 'shop' );
	$hide_for_post_ids[] = wc_get_page_id( 'cart' );
	$hide_for_post_ids[] = wc_get_page_id( 'myaccount' );
}

return array(
	'title' => us_translate( 'Checkout Page', 'woocommerce' ) . ' – ' . us_translate( 'Order Total', 'woocommerce' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-money-check-alt',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'page' ),
	'hide_for_post_ids' => $hide_for_post_ids,
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		array(
			'title' => array(
				'title' => us_translate( 'Title' ),
				'type' => 'text',
				'std' => us_translate( 'Your order', 'woocommerce' ),
				'usb_preview' => array(
					'attr' => 'text',
					'elm' => '> h3',
				),
			),
			'title_size' => array(
				'title' => __( 'Title Size', 'us' ),
				'description' => $misc['desc_font_size'],
				'type' => 'text',
				'std' => '',
				'show_if' => array( 'title', '!=', '' ),
				'usb_preview' => array(
					'css' => '--title-size',
				),
			),
			'products_list' => array(
				'type' => 'switch',
				'switch_text' => __( 'Show Products List', 'us' ),
				'std' => 1,
				'usb_preview' => array(
					'toggle_class_inverse' => 'hide_products_list',
				),
			),
			'subtotal' => array(
				'type' => 'switch',
				'switch_text' => __( 'Show Subtotal', 'us' ),
				'std' => 1,
				'usb_preview' => array(
					'toggle_class_inverse' => 'hide_subtotal',
				),
			),
			'total_size' => array(
				'title' => __( 'Total Font Size', 'us' ),
				'description' => $misc['desc_font_size'],
				'type' => 'text',
				'std' => '1.4rem',
				'usb_preview' => array(
					'css' => '--total-size',
				),
			),
		),

		$conditional_params,
		$design_options_params
	)
);
