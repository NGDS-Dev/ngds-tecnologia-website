<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: cart
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * @return array
 */
return array(
	'title' => us_translate( 'Cart', 'woocommerce' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-shopping-cart',
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		// General section
		array(
			'hide_empty' => array(
				'type' => 'switch',
				'switch_text' => us_translate( 'Hide if cart is empty', 'woocommerce' ),
				'std' => 0,
			),
			'vstretch' => array(
				'type' => 'switch',
				'switch_text' => __( 'Stretch to the full available height', 'us' ),
				'std' => 1,
				'classes' => 'for_above',
			),
			'quantity_color_bg' => array(
				'title' => __( 'Quantity Badge Background', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'std' => '_header_middle_text_hover',
				'cols' => 2,
			),
			'quantity_color_text' => array(
				'title' => __( 'Quantity Badge Text', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'with_gradient' => FALSE,
				'std' => '_header_middle_bg',
				'cols' => 2,
			),
			'dropdown_effect' => array(
				'title' => __( 'Dropdown Effect', 'us' ),
				'type' => 'select',
				'options' => $misc['dropdown_effect_values'],
				'std' => 'height',
			),
			'icon' => array(
				'title' => __( 'Icon', 'us' ),
				'type' => 'icon',
				'std' => 'fas|shopping-cart',
			),
			'heading_1' => array(
				'title' => __( 'Icon Size', 'us' ),
				'type' => 'heading',
			),
			'size' => array(
				'title' => __( 'Desktops', 'us' ),
				'type' => 'text',
				'std' => '26px',
				'cols' => 4,
				'classes' => 'for_above',
			),
			'size_laptops' => array(
				'title' => __( 'Laptops', 'us' ),
				'type' => 'text',
				'std' => '24px',
				'cols' => 4,
				'classes' => 'for_above',
			),
			'size_tablets' => array(
				'title' => __( 'Tablets', 'us' ),
				'type' => 'text',
				'std' => '22px',
				'cols' => 4,
				'classes' => 'for_above',
			),
			'size_mobiles' => array(
				'title' => __( 'Mobiles', 'us' ),
				'type' => 'text',
				'std' => '20px',
				'cols' => 4,
				'classes' => 'for_above',
			),
		),

		$conditional_params,
		$design_options_params
	),
);
