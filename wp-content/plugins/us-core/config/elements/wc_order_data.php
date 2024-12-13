<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Order data
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

return array(
	'title' => us_translate( 'Order details', 'woocommerce' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-receipt',
	'show_for_post_types' => array( 'us_content_template' ),
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		array(
			'type' => array(
				'title' => us_translate( 'Show' ),
				'type' => 'select',
				'options' => array(
					'number' => us_translate( 'Order Number', 'woocommerce' ),
					'payment-instructions' => us_translate( 'Payment method:', 'woocommerce' ) . ' ' . us_translate( 'Instructions', 'woocommerce' ),
					'details' => us_translate( 'Order details', 'woocommerce' ),
					'default_actions' => __( 'Actions for plugins compatibility', 'us' ),
				),
				'std' => 'number',
				'admin_label' => TRUE,
				'usb_preview' => TRUE,
			),
			'number_style' => array(
				'title' => us_translate( 'Style' ),
				'type' => 'select',
				'options' => array(
					'default' => us_translate( 'Default' ),
					'modern' => __( 'Modern', 'us' ),
					'none' => us_translate( 'None' ),
				),
				'std' => 'default',
				'show_if' => array( 'type', '=', 'number' ),
				'usb_preview' => array(
					'mod' => 'style',
				),
			),
		),

		$conditional_params,
		$design_options_params
	)
);
