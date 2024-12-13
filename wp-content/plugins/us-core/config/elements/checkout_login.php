<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: us_checkout_login
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

/**
 * @return array
 */
return array(
	'title' => us_translate( 'Checkout Page', 'woocommerce' ) . ' – ' . us_translate( 'Login', 'woocommerce' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-money-check-alt',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'page' ),
	'hide_for_post_ids' => $hide_for_post_ids,
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(
		array(
			'notice_style' => array(
				'title' => __( 'Notice Style', 'us' ),
				'type' => 'radio',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
				'std' => '1',
				'usb_preview' => array(
					'elm' => '.w-wc-notices',
					'mod' => 'style',
				),
			),
			'notice_message' => array(
				'title' => __( 'Notice Text', 'us' ),
				'type' => 'text',
				'std' => us_translate( 'Returning customer?', 'woocommerce' ),
				'usb_preview' => array(
					'elm' => '.w-wc-notices span',
					'attr' => 'text',
				),
			),
			'message' => array(
				'title' => us_translate( 'Message' ),
				'type' => 'textarea',
				'std' => us_translate( 'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.', 'woocommerce' ),
			),
			'btn_style' => array(
				'title' => __( 'Button Style', 'us' ),
				'description' => $misc['desc_btn_styles'],
				'type' => 'select',
				'options' => us_get_btn_styles(),
				'std' => '1',
				'usb_preview' => array(
					'elm' => '.w-btn',
					'mod' => 'us-btn-style',
				),
			),
		),

		$conditional_params,
		$design_options_params
	),
);
