<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: post_comments
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );
$hover_options_params = us_config( 'elements_hover_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'Post Comments', 'us' ),
	'category' => __( 'Post Elements', 'us' ),
	'icon' => 'fas fa-comments',
	'params' => us_set_params_weight(

		// General section
		array(
			'layout' => array(
				'title' => us_translate( 'Show' ),
				'type' => 'select',
				'options' => array(
					'comments_template' => __( 'List of comments with response form', 'us' ),
					'amount' => __( 'Comments amount', 'us' ),
				),
				'std' => 'comments_template',
				'admin_label' => TRUE,
				'context' => array( 'shortcode' ),
				'usb_preview' => TRUE,
			),
			'hide_zero' => array(
				'type' => 'switch',
				'switch_text' => __( 'Hide this element if no comments', 'us' ),
				'std' => 0,
				'show_if' => array( 'layout', '=', 'amount' ),
				'usb_preview' => TRUE,
			),
			'number' => array(
				'type' => 'switch',
				'switch_text' => __( 'Show only number', 'us' ),
				'std' => 0,
				'show_if' => array( 'layout', '=', 'amount' ),
				'usb_preview' => TRUE,
			),
			'link' => array(
				'title' => us_translate( 'Link' ),
				'type' => 'link',
				'dynamic_values' => array(
					'post' => array(
						'post' => __( 'Post Link', 'us' ),
						'post_comments' => __( 'Post Comments', 'us' ),
						'custom_field|us_tile_link' => sprintf( '%s: %s', __( 'Additional Settings', 'us' ), __( 'Custom Link', 'us' ) ),
					),
				),
				'std' => '{"type":"post_comments"}',
				'show_if' => array( 'layout', '=', 'amount' ),
				'usb_preview' => TRUE,
			),
			'color_link' => array(
				'title' => __( 'Link Color', 'us' ),
				'type' => 'switch',
				'switch_text' => __( 'Inherit from text color', 'us' ),
				'std' => 1,
				'usb_preview' => array(
					'toggle_class' => 'color_link_inherit',
				),
			),
			'icon' => array(
				'title' => __( 'Icon', 'us' ),
				'type' => 'icon',
				'std' => '',
				'show_if' => array( 'layout', '=', 'amount' ),
				'usb_preview' => TRUE,
			),
		),

		$conditional_params,
		$design_options_params,
		$hover_options_params
	),

	// Not used params, required for correct fallback
	'fallback_params' => array(
		'custom_link',
	),
);
