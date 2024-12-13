<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for User Picture element
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );
$hover_options_params = us_config( 'elements_hover_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'User Picture', 'us' ),
	'icon' => 'fas fa-image',
	'params' => us_set_params_weight(

		// General section
		array(
			'default_avatar' => array(
				'title' => us_translate( 'Default Avatar', 'us' ),
				'type' => 'select',
				'options' => array(
					'mystery' => us_translate( 'Mystery Person' ),
					'gravatar_default' => us_translate( 'Gravatar Logo' ),
					'identicon' => us_translate( 'Identicon (Generated)' ),
					'retro' => us_translate( 'Retro (Generated)' ),
					'robohash' => us_translate( 'RoboHash (Generated)' ),
				),
				'std' => 'mystery',
			),
			'width' => array(
				'title' => __( 'Picture Width', 'us' ),
				'type' => 'slider',
				'options' => array(
					'px' => array(
						'min' => 32,
						'max' => 256,
					),
				),
				'std' => '128px',
			),
			'circle' => array(
				'type' => 'switch',
				'switch_text' => __( 'Enable rounded image', 'us' ),
				'std' => 0,
			),
			'link' => array(
				'title' => us_translate( 'Link' ),
				'type' => 'link',
				'dynamic_values' => array(
					'global' => array(),
					'post' => array(),
				),
				'std' => '{"url":""}',
			),
		),

		$conditional_params,
		$design_options_params,
		$hover_options_params
	),
);
