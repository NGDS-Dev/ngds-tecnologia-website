<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: html
 */

$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );
$hover_options_params = us_config( 'elements_hover_options' );

/**
 * @return array
 */
return array(
	'title' => us_translate( 'Custom HTML' ),
	'icon' => 'fas fa-code',
	'params' => us_set_params_weight(

		// General section
		array(
			'content' => array(
				'description' => sprintf( __( 'Added content will be displayed inside the %s block', 'us' ), '<code>&lt;div class="w-html"&gt;&lt;/div&gt;</code>' ),
				'type' => 'html',
				'encoded' => TRUE,
				'std' => base64_encode( '<p>I am raw html block.<br/>Click edit button to change this html</p>' ),
				'usb_preview' => TRUE,
			),
		),

		$conditional_params,
		$design_options_params,
		$hover_options_params
	),
);
