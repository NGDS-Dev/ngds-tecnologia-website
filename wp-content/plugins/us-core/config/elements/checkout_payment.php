<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: checkout_payment
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
	'title' => us_translate( 'Checkout Page', 'woocommerce' ) . ' – ' . us_translate( 'Payment', 'woocommerce' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-money-check-alt',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'page' ),
	'hide_for_post_ids' => $hide_for_post_ids,
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		array(
			'payments_style' => array(
				'title' => __( 'Payment Methods Style', 'us' ),
				'type' => 'radio',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
				'std' => '1',
				'usb_preview' => array(
					'mod' => 'payments-style',
				),
			),
			'btn_label' => array(
				'title' => __( 'Button Label', 'us' ),
				'type' => 'text',
				'std' => us_translate( 'Place order', 'woocommerce' ),
				'usb_preview' => array(
					'attr' => 'text',
					'elm' => '.w-btn',
				),
			),
			'btn_style' => array(
				'title' => __( 'Button Style', 'us' ),
				'description' => $misc['desc_btn_styles'],
				'type' => 'select',
				'options' => us_get_btn_styles(),
				'std' => '1',
				'usb_preview' => array(
					'mod' => 'us-btn-style',
					'elm' => '.w-btn',
				),
			),
			'btn_size' => array(
				'title' => __( 'Button Size', 'us' ),
				'description' => $misc['desc_font_size'],
				'type' => 'text',
				'std' => '',
				'usb_preview' => array(
					'css' => '--btn-size',
				),
			),
			'btn_fullwidth' => array(
				'type' => 'switch',
				'switch_text' => __( 'Stretch to the full width', 'us' ),
				'std' => 0,
				'usb_preview' => array(
					'toggle_class' => 'btn_fullwidth',
				),
			),
		),

		$conditional_params,
		$design_options_params
	)
);
