<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: text
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );
$hover_options_params = us_config( 'elements_hover_options' );
$effect_options_params = us_config( 'elements_effect_options' );

/**
 * @return array
 */
return array(
	'title' => us_translate( 'Text' ),
	'description' => __( 'Custom text with link and icon', 'us' ),
	'category' => __( 'Basic', 'us' ),
	'icon' => 'fas fa-font',
	'params' => us_set_params_weight(

		// General section
		array(
			'text' => array(
				'title' => us_translate( 'Text' ),
				'type' => 'text',
				'show_ai_icon' => TRUE,
				'std' => 'Some text',
				'dynamic_values' => TRUE,
				'holder' => 'div',
				'usb_preview' => array(
					'attr' => 'html',
					'elm' => '.w-text-value',
				),
			),
			'wrap' => array(
				'type' => 'switch',
				'switch_text' => __( 'Allow move content to the next line', 'us' ),
				'std' => 0,
				'classes' => 'for_above',
				'context' => array( 'header' ),
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
				'std' => '{"url":""}',
				'usb_preview' => TRUE,
			),
			'hide_with_empty_link' => array(
				'type' => 'switch',
				'switch_text' => __( 'Hide this element if there is no link', 'us' ),
				'std' => 0,
				'classes' => 'for_above',
			),
			'tag' => array(
				'title' => __( 'HTML tag', 'us' ),
				'type' => 'select',
				'options' => $misc['html_tag_values'],
				'std' => 'div',
				'admin_label' => TRUE,
				'usb_preview' => array(
					'attr' => 'tag',
				),
			),
			'icon' => array(
				'title' => __( 'Icon', 'us' ),
				'type' => 'icon',
				'std' => '',
				'usb_preview' => TRUE,
			),
			'iconpos' => array(
				'title' => __( 'Icon Position', 'us' ),
				'type' => 'radio',
				'options' => array(
					'left' => __( 'Before text', 'us' ),
					'right' => __( 'After text', 'us' ),
				),
				'std' => 'left',
				'usb_preview' => TRUE,
			),
		),

		$effect_options_params,
		$conditional_params,
		$design_options_params,
		$hover_options_params
	),

	// Not used params, required for correct fallback
	'fallback_params' => array(
		'align',
		'link_type',
		'link_new_tab',
		'onclick_code',
	),
);
