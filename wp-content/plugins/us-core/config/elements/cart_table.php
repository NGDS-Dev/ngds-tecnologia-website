<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: cart_table
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
	$hide_for_post_ids[] = wc_get_page_id( 'checkout' );
	$hide_for_post_ids[] = wc_get_page_id( 'myaccount' );
}

return array(
	'title' => us_translate( 'Cart Page', 'woocommerce' ) . ' – ' . __( 'Product Table', 'us' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-shopping-cart',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'page' ),
	'hide_for_post_ids' => $hide_for_post_ids,
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		// General section
		array(
			'table_head' => array(
				'type' => 'switch',
				'switch_text' => __( 'Table Head', 'us' ),
				'std' => 0,
				'usb_preview' => TRUE,
			),
			'table_lines' => array(
				'title' => __( 'Table Lines', 'us' ),
				'type' => 'radio',
				'options' => array(
					'all' => __( 'All', 'us' ),
					'between' => __( 'Between', 'us' ),
					'none' => us_translate( 'None' ),
				),
				'std' => 'all',
				'usb_preview' => array(
					'mod' => 'table-lines',
				),
			),
			'valign_middle' => array(
				'type' => 'switch',
				'switch_text' => __( 'Center items vertically', 'us' ),
				'std' => 1,
				'usb_preview' => array(
					'toggle_class' => 'valign_middle',
				),
			),
			'thumbnail' => array(
				'type' => 'switch',
				'switch_text' => us_translate( 'Thumbnail' ),
				'std' => 1,
				'usb_preview' => TRUE,
			),
			'thumbnail_width' => array(
				'title' => us_translate( 'Thumbnail Width' ),
				'description' => __( 'Examples:', 'us' ) . ' <span class="usof-example">150px</span>, <span class="usof-example">14%</span>, <span class="usof-example">8rem</span>',
				'type' => 'text',
				'std' => '90px',
				'show_if' => array( 'thumbnail', '=', 1 ),
				'usb_preview' => array(
					'css' => '--thumbnail-width',
				),
			),
			'price_before_qty' => array(
				'type' => 'switch',
				'switch_text' => __( 'Price Before Quantity', 'us' ),
				'std' => 0,
				'usb_preview' => TRUE,
			),
			'qty_btn_style' => array(
				'title' => __( 'Quantity Buttons Style', 'us' ),
				'type' => 'radio',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
				'std' => '1',
				'usb_preview' => array(
					'mod' => 'qty-btn-style',
				),
			),
			'qty_btn_size' => array(
				'title' => __( 'Quantity Buttons Size', 'us' ),
				'description' => $misc['desc_font_size'],
				'type' => 'text',
				'std' => '1rem',
				'usb_preview' => array(
					'css' => '--qty-btn-size',
				),
			),
			'subtotal_size' => array(
				'title' => __( 'Size of Subtotal Price', 'us' ),
				'description' => $misc['desc_font_size'],
				'type' => 'text',
				'std' => '1rem',
				'usb_preview' => array(
					'css' => '--subtotal-size',
				),
			),
			'removing_link' => array(
				'title' => __( 'Show Removing Link', 'us' ),
				'type' => 'radio',
				'options' => array(
					'after_subtotal' => __( 'After Subtotal', 'us' ),
					'below_qty' => __( 'Below Quantity', 'us' ),
				),
				'std' => 'after_subtotal',
				'usb_preview' => TRUE,
			),
		),

		$conditional_params,
		$design_options_params
	),
);
