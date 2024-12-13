<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: search
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * @return array
 */
return array(
	'title' => us_translate( 'Search' ),
	'icon' => 'fas fa-search',
	'params' => us_set_params_weight(

		// General section
		array(
			'text' => array(
				'title' => __( 'Placeholder', 'us' ),
				'type' => 'text',
				'std' => us_translate( 'Search' ),
				'dynamic_values' => TRUE,
				'admin_label' => TRUE,
				'usb_preview' => array(
					array(
						'elm' => 'input',
						'attr' => 'placeholder',
					),
					array(
						'elm' => 'input',
						'attr' => 'aria-label',
					),
				),
			),
			'search_post_type' => array(
				'title' => __( 'Search specific post type', 'us' ),
				'type' => 'checkboxes',
				/* Exclude us_* because for the usbuilder they are included for editing */
				'options' => us_get_public_post_types( /* exclude */array( 'page' , 'us_content_template', 'us_page_block' ) ),
				'std' => '',
			),
			'layout' => array(
				'title' => __( 'Layout', 'us' ),
				'type' => 'radio',
				'context' => array( 'header' ),
				'options' => array(
					'simple' => __( 'Simple', 'us' ),
					'modern' => __( 'Modern', 'us' ),
					'fullwidth' => __( 'Full Width', 'us' ),
					'fullscreen' => __( 'Full Screen', 'us' ),
				),
				'std' => 'fullwidth',
				'usb_preview' => array(
					'mod' => 'layout',
				),
			),
			'field_bg_color' => array(
				'title' => __( 'Search Field Background', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'std' => '',
				'cols' => 2,
				'usb_preview' => array(
					'css' => 'background',
					'elm' => 'input[type=text]',
				),
			),
			'field_text_color' => array(
				'title' => __( 'Search Field Text', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'with_gradient' => FALSE,
				'std' => '',
				'cols' => 2,
				'usb_preview' => array(
					'css' => 'color',
					'elm' => 'input[type=text]',
				),
			),
			'icon' => array(
				'title' => __( 'Icon', 'us' ),
				'type' => 'icon',
				'std' => 'fas|search',
				'usb_preview' => TRUE,
			),
			'icon_size' => array(
				'title' => __( 'Icon Size', 'us' ),
				'description' => __( 'Desktops', 'us' ),
				'type' => 'text',
				'std' => '18px',
				'header_cols' => 4,
				'usb_preview' => array(
					'css' => 'font-size',
					'elm' => '.w-search-form-btn',
				),
			),
			'icon_size_laptops' => array(
				'title' => '&nbsp;',
				'description' => __( 'Laptops', 'us' ),
				'type' => 'text',
				'std' => '24px',
				'cols' => 4,
				'context' => array( 'header' ),
			),
			'icon_size_tablets' => array(
				'title' => '&nbsp;',
				'description' => __( 'Tablets', 'us' ),
				'type' => 'text',
				'std' => '22px',
				'cols' => 4,
				'context' => array( 'header' ),
			),
			'icon_size_mobiles' => array(
				'title' => '&nbsp;',
				'description' => __( 'Mobiles', 'us' ),
				'type' => 'text',
				'std' => '20px',
				'cols' => 4,
				'context' => array( 'header' ),
			),
			'field_width' => array(
				'title' => __( 'Field Width', 'us' ),
				'description' => __( 'Desktops', 'us' ),
				'type' => 'text',
				'std' => '300px',
				'cols' => 3,
				'show_if' => array( 'layout', '=', array( 'simple', 'modern' ) ),
				'context' => array( 'header' ),
			),
			'field_width_laptops' => array(
				'title' => '&nbsp;',
				'description' => __( 'Laptops', 'us' ),
				'type' => 'text',
				'std' => '250px',
				'cols' => 3,
				'show_if' => array( 'layout', '=', array( 'simple', 'modern' ) ),
				'context' => array( 'header' ),
			),
			'field_width_tablets' => array(
				'title' => '&nbsp;',
				'description' => __( 'Tablets', 'us' ),
				'type' => 'text',
				'std' => '200px',
				'cols' => 3,
				'show_if' => array( 'layout', '=', array( 'simple', 'modern' ) ),
				'context' => array( 'header' ),
			),
		),

		$conditional_params,
		$design_options_params
	),
	'fallback_params' => array(
		'product_search',
	)
);
