<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: counter
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'Counter', 'us' ),
	'description' => __( 'Animated number with text', 'us' ),
	'category' => __( 'Interactive', 'us' ),
	'icon' => 'fas fa-stopwatch-20',
	'params' => us_set_params_weight(
		array(
			'initial' => array(
				'title' => __( 'Initial counting value', 'us' ),
				'description' => __( 'Examples:', 'us' ) . ' <span class="usof-example">0</span>, <span class="usof-example">$0</span>, <span class="usof-example">1%</span>, <span class="usof-example">0.001</span>, <span class="usof-example">1kg</span>',
				'type' => 'text',
				'std' => '1',
				'usb_preview' => TRUE,
			),
			'final' => array(
				'title' => __( 'Final counting value', 'us' ),
				'description' => __( 'Examples:', 'us' ) . ' <span class="usof-example">100</span>, <span class="usof-example">$70</span>, <span class="usof-example">98%</span>, <span class="usof-example">0.374</span>, <span class="usof-example">35kg</span>',
				'type' => 'text',
				'std' => '99',
				'dynamic_values' => TRUE,
				'holder' => 'div',
				'usb_preview' => TRUE,
			),
			'duration' => array(
				'title' => __( 'Animation Duration', 'us' ),
				'type' => 'slider',
				'std' => '2.0s',
				'options' => array(
					's' => array(
						'min' => 0.5,
						'max' => 4.0,
						'step' => 0.5,
					),
				),
				'usb_preview' => TRUE, // Note: Settings for js library (counter.js)
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
				'std' => 'center',
				'usb_preview' => array(
					'mod' => 'align',
				),
			),
			'color' => array(
				'title' => us_translate( 'Color' ),
				'type' => 'select',
				'options' => array(
					'primary' => __( 'Primary (theme color)', 'us' ),
					'secondary' => __( 'Secondary (theme color)', 'us' ),
					'heading' => __( 'Heading (theme color)', 'us' ),
					'text' => __( 'Text (theme color)', 'us' ),
					'custom' => __( 'Custom', 'us' ),
				),
				'std' => 'primary',
				'usb_preview' => array(
					'mod' => 'color'
				),
			),
			'custom_color' => array(
				'type' => 'color',
				'clear_pos' => 'right',
				'with_gradient' => FALSE,
				'std' => '',
				'classes' => 'for_above',
				'show_if' => array( 'color', '=', 'custom' ),
				'usb_preview' => array(
					'elm' => '.w-counter-value',
					'css' => 'color',
				),
			),

			// Title
			'title' => array(
				'title' => us_translate( 'Title' ),
				'type' => 'text',
				'std' => __( 'Projects completed', 'us' ),
				'dynamic_values' => TRUE,
				'holder' => 'div',
				'group' => us_translate( 'Title' ),
				'usb_preview' => array(
					'attr' => 'html',
					'elm' => '.w-counter-title',
				),
			),
			'title_size' => array(
				'title' => __( 'Title Size', 'us' ),
				'description' => $misc['desc_font_size'],
				'type' => 'text',
				'std' => '',
				'group' => us_translate( 'Title' ),
				'usb_preview' => array(
					'css' => 'font-size',
					'elm' => '.w-counter-title',
				),
			),
			'title_tag' => array(
				'title' => __( 'Title HTML tag', 'us' ),
				'type' => 'select',
				'options' => $misc['html_tag_values'],
				'std' => 'h6',
				'cols' => 2,
				'group' => us_translate( 'Title' ),
				'usb_preview' => array(
					'attr' => 'tag',
					'elm' => '.w-counter-title',
				),
			),
			'title_weight' => array(
				'title' => __( 'Title Weight', 'us' ),
				'type' => 'select',
				'options' => array(
					'' => us_translate( 'Default' ),
					'100' => '100 ' . __( 'thin', 'us' ),
					'200' => '200 ' . __( 'extra-light', 'us' ),
					'300' => '300 ' . __( 'light', 'us' ),
					'400' => '400 ' . __( 'normal', 'us' ),
					'500' => '500 ' . __( 'medium', 'us' ),
					'600' => '600 ' . __( 'semi-bold', 'us' ),
					'700' => '700 ' . __( 'bold', 'us' ),
					'800' => '800 ' . __( 'extra-bold', 'us' ),
					'900' => '900 ' . __( 'ultra-bold', 'us' ),
				),
				'std' => '',
				'cols' => 2,
				'group' => us_translate( 'Title' ),
				'usb_preview' => array(
					'css' => 'font-weight',
					'elm' => '.w-counter-title',
				),
			),
			'title_indent' => array(
				'title' => __( 'Title Indent', 'us' ),
				'description' => __( 'Examples:', 'us' ) . ' <span class="usof-example">0</span>, <span class="usof-example">-10px</span>, <span class="usof-example">0.5em</span>, <span class="usof-example">1rem</span>',
				'type' => 'text',
				'std' => '0.6rem',
				'group' => us_translate( 'Title' ),
				'usb_preview' => array(
					'css' => 'margin-top',
					'elm' => '.w-counter-title',
				),
			),
		),

		$conditional_params,
		$design_options_params
	),
	'usb_init_js' => '$elm.wCounter(); $us.$window.trigger( \'scroll.waypoints\' )',
);
