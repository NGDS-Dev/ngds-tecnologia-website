<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: btn
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );
$hover_options_params = us_config( 'elements_hover_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'Button', 'us' ),
	'icon' => 'fas fa-hand-pointer',
	'category' => __( 'Basic', 'us' ),
	'admin_enqueue_js' => US_CORE_URI . '/plugins-support/js_composer/js/us_icon_view.js',
	'js_view' => 'ViewUsIcon', // used in WPBakery editor
	'params' => us_set_params_weight(

		// General section
		array(
			'label' => array(
				'title' => __( 'Button Label', 'us' ),
				'type' => 'text',
				'std' => __( 'Click Me', 'us' ),
				'holder' => 'button',
				'dynamic_values' => TRUE,
				'usb_preview' => array(
					'attr' => 'html',
					'elm' => '.w-btn-label',
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
				'std' => '{"url":"#"}',
			),
			'hide_with_empty_link' => array(
				'type' => 'switch',
				'switch_text' => __( 'Hide this element if there is no link', 'us' ),
				'std' => 0,
				'classes' => 'for_above',
			),
			'style' => array(
				'title' => us_translate( 'Style' ),
				'description' => $misc['desc_btn_styles'],
				'type' => 'select',
				'options' => us_get_btn_styles(),
				'std' => '1',
				'usb_preview' => array(
					'mod' => 'us-btn-style',
					'elm' => '.w-btn',
				),
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
					'justify' => __( 'Stretch to the full width', 'us' ),
				),
				'std' => 'none',
				'context' => array( 'shortcode' ),
				'is_responsive' => TRUE,
				'usb_preview' => array(
					'mod' => 'align',
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

		$conditional_params,
		$design_options_params,
		$hover_options_params
	),
	'fallback_params' => array(
		'link_new_tab',
		'link_type',
		'onclick_code',
		'width_type',
	),
);
