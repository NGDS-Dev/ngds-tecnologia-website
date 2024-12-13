<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: woocommerce_notices
 */

$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

return array(
	'title' => __( 'Shop Notices Box', 'us' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-exclamation-triangle',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'product' , 'page' ),
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(
		array(
			'style' => array(
				'title' => us_translate( 'Style' ),
				'type' => 'radio',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
				'std' => '1',
				'usb_preview' => array(
					'mod' => 'style',
				),
			),
		),

		$conditional_params,
		$design_options_params
	),
);
