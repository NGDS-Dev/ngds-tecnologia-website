<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: socials
 */

$social_links_params = us_config( 'social_links' );
$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'Social Links', 'us' ),
	'icon' => 'fab fa-facebook',
	'params' => us_set_params_weight(

		// General
		array(
			'items' => array(
				'type' => 'group',
				'show_controls' => TRUE,
				'is_sortable' => TRUE,
				'is_accordion' => FALSE,
				'params' => array(
					'type' => array(
						'title' => us_translate( 'Link' ),
						'type' => 'select',
						'options' => array_merge(
							$social_links_params,
							array( 'custom' => __( 'Custom', 'us' ) )
						),
						'std' => 's500px',
						'admin_label' => TRUE,
					),
					'url' => array(
						'type' => 'link',
						'dynamic_values' => TRUE,
						'std' => '{"url":""}',
						'classes' => 'for_above',
					),
					'icon' => array(
						'title' => __( 'Icon', 'us' ),
						'type' => 'icon',
						'std' => 'fab|apple',
						'show_if' => array( 'type', '=', 'custom' ),
					),
					'title' => array(
						'title' => us_translate( 'Title' ),
						'placeholder' => us_translate( 'Title' ),
						'type' => 'text',
						'std' => us_translate( 'Title' ),
						'show_if' => array( 'type', '=', 'custom' ),
					),
					'color' => array(
						'title' => us_translate( 'Color' ),
						'type' => 'color',
						'clear_pos' => 'right',
						'std' => '_content_faded',
						'show_if' => array( 'type', '=', 'custom' ),
					),
				),
				'std' => array(
					array(
						'type' => 'facebook',
						'url' => '{"url":"https://www.facebook.com/"}',
					),
					array(
						'type' => 'twitter',
						'url' => '{"url":"https://www.twitter.com/"}',
					),
				),
				'usb_preview' => TRUE,
			),
		),

		// Appearance
		array(
			'shape' => array(
				'title' => __( 'Icons Shape', 'us' ),
				'type' => 'select',
				'options' => array(
					'none' => us_translate( 'None' ),
					'square' => __( 'Square', 'us' ),
					'rounded' => __( 'Rounded Square', 'us' ),
					'circle' => __( 'Circle', 'us' ),
				),
				'std' => 'square',
				'admin_label' => TRUE,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => array(
					'mod' => 'shape',
				),
			),
			'style' => array(
				'title' => __( 'Icons Style', 'us' ),
				'type' => 'select',
				'options' => array(
					'default' => __( 'Simple', 'us' ),
					'colored' => __( 'Solid', 'us' ),
					'outlined' => __( 'Outlined', 'us' ),
					'solid' => __( 'With alternate background', 'us' ),
				),
				'std' => 'default',
				'show_if' => array( 'shape', '!=', 'none' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => array(
					'mod' => 'style',
				),
			),
			'icons_color' => array(
				'title' => __( 'Icons Color', 'us' ),
				'type' => 'select',
				'options' => array(
					'brand' => __( 'Default brands colors', 'us' ),
					'text' => __( 'Text (theme color)', 'us' ),
					'link' => __( 'Link (theme color)', 'us' ),
				),
				'std' => 'brand',
				'admin_label' => TRUE,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => array(
					'mod' => 'color',
				),
			),
			'hover' => array(
				'title' => __( 'Hover Style', 'us' ),
				'type' => 'radio',
				'options' => array(
					'fade' => __( 'Fade', 'us' ),
					'slide' => __( 'Slide', 'us' ),
					'none' => us_translate( 'None' ),
				),
				'std' => 'fade',
				'show_if' => array( 'shape', '!=', 'none' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => array(
					'mod' => 'hover',
				),
			),
			'stretch' => array(
				'type' => 'switch',
				'switch_text' => __( 'Stretch to the full width', 'us' ),
				'std' => 0,
				'context' => array( 'shortcode' ),
				'show_if' => array( 'shape', '!=', 'none' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => array(
					'toggle_class' => 'stretch',
				),
			),
			'gap' => array(
				'title' => __( 'Gap between Icons', 'us' ),
				'type' => 'slider',
				'std' => '0em',
				'options' => array(
					'px' => array(
						'min' => 0,
						'max' => 20,
					),
					'em' => array(
						'min' => 0.0,
						'max' => 1.0,
						'step' => 0.1,
					),
				),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => array(
					'css' => '--gap',
				),
			),
			'hide_tooltip' => array(
				'type' => 'switch',
				'switch_text' => __( 'Hide tooltip on hover', 'us' ),
				'std' => 0,
				'group' => us_translate( 'Appearance' ),
				'context' => array( 'shortcode' ),
				'usb_preview' => TRUE,
			),
		),

		$conditional_params,
		$design_options_params
	),
	'fallback_params' => array(
		'color',
		'align',
		'nofollow',
	),
);
