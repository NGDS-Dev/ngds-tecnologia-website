<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: checkout_billing
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
	'title' => us_translate( 'Checkout Page', 'woocommerce' ) . ' – ' . us_translate( 'Billing details', 'woocommerce' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-money-check-alt',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'page' ),
	'hide_for_post_ids' => $hide_for_post_ids,
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		// General section
		array(
			'title' => array(
				'title' => us_translate( 'Title' ),
				'type' => 'text',
				'std' => us_translate( 'Billing details', 'woocommerce' ),
				'usb_preview' => array(
					'attr' => 'text',
					'elm' => '.woocommerce-billing-fields > h3',
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
			'cols' => array(
				'title' => us_translate( 'Columns' ),
				'type' => 'radio',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
				'std' => '1',
				'usb_preview' => array(
					'mod' => 'cols',
				),
			),
			'fields_gap' => array(
				'title' => __( 'Gap between Fields', 'us' ),
				'type' => 'slider',
				'std' => '1.5rem',
				'options' => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
					'rem' => array(
						'min' => 0.0,
						'max' => 3.0,
						'step' => 0.1,
					),
					'vh' => array(
						'min' => 0.0,
						'max' => 9.0,
						'step' => 0.1,
					),
				),
				'usb_preview' => array(
					'css' => '--fields-gap',
				),
			),
		),

		$conditional_params,
		$design_options_params
	)
);
