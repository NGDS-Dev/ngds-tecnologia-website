<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: post_title
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );
$hover_options_params = us_config( 'elements_hover_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'Post Title', 'us' ),
	'category' => __( 'Post Elements', 'us' ),
	'icon' => 'fas fa-font',
	'params' => us_set_params_weight(

		// General section
		array(
			'tag' => array(
				'title' => __( 'HTML tag', 'us' ),
				'type' => 'select',
				'options' => $misc['html_tag_values'],
				'std' => 'h2',
				'admin_label' => TRUE,
				'usb_preview' => array(
					'attr' => 'tag',
				),
			),
			'show_count' => array(
				'type' => 'switch',
				'switch_text' => __( 'Show number of posts in the current term', 'us' ),
				'std' => 0,
				'context' => array( 'grid' ),
			),
			'align' => array(
				'title' => us_translate( 'Alignment' ),
				'type' => 'radio',
				'labels_as_icons' => 'fas fa-align-*',
				'options' => array(
					'none' => us_translate( 'Default' ),
					'left' => us_translate( 'Left' ),
					'center' => us_translate( 'Center' ),
					'right' => us_translate( 'Right' ),
				),
				'std' => 'none',
				'admin_label' => TRUE,
				'usb_preview' => array(
					'mod' => 'align',
				),
			),
			'link' => array(
				'title' => us_translate( 'Link' ),
				'type' => 'link',
				'dynamic_values' => array(
					'global' => array(
						'homepage' => us_translate( 'Homepage' ),
						'elm_value' => __( 'Clickable value (email, phone, website)', 'us' ),
					),
				),
				'std' => '{"type":"post"}',
				'shortcode_std' => '',
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
			'shorten_length' => array(
				'type' => 'switch',
				'switch_text' => __( 'Shorten title length', 'us' ),
				'std' => 0,
				'usb_preview' => TRUE,
			),
			'shorten_length_count' => array(
				'title' => __( 'Amount of characters to show', 'us' ),
				'type' => 'slider',
				'std' => '30',
				'options' => array(
					'' => array(
						'min' => 1,
						'max' => 60,
					),
				),
				'show_if' => array( 'shorten_length', '=', '1' ),
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
		'link_new_tab',
	),
);
