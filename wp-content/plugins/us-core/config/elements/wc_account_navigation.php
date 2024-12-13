<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: wc_account_navigation
 */

$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

$hide_for_post_ids = array();
if ( 
	function_exists( 'wc_get_page_id' )
	AND us_is_elm_editing_page()
) {
	$hide_for_post_ids[] = wc_get_page_id( 'shop' );
	$hide_for_post_ids[] = wc_get_page_id( 'cart' );
	$hide_for_post_ids[] = wc_get_page_id( 'checkout' );
}

return array(
	'title' => us_translate( 'My Account Page', 'woocommerce' ) . ' – ' . us_translate( 'Menu' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-bars',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'page' ),
	'hide_for_post_ids' => $hide_for_post_ids,
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		// General section
		array(
			'layout' => array(
				'title' => __( 'Layout', 'us' ),
				'type' => 'radio',
				'options' => array(
					'ver' => __( 'Vertical', 'us' ),
					'hor' => __( 'Horizontal', 'us' ),
				),
				'std' => 'ver',
				'admin_label' => TRUE,
				'context' => array( 'shortcode' ),
				'usb_preview' => array(
					'mod' => 'layout',
				),
			),
			'spread' => array(
				'type' => 'switch',
				'switch_text' => __( 'Spread menu items evenly over the available width', 'us' ),
				'std' => 0,
				'classes' => 'for_above',
				'show_if' => array( 'layout', '=', 'hor' ),
				'usb_preview' => array(
					'toggle_class' => 'spread',
				),
			),
			'responsive_width' => array(
				'title' => __( 'Switch to vertical at screens below', 'us' ),
				'description' => __( 'Examples:', 'us' ) . ' <span class="usof-example">600px</span>, <span class="usof-example">768px</span>. ' . __( 'Leave blank to enable horizontal scrolling on small screens.', 'us' ),
				'type' => 'text',
				'std' => '600px',
				'context' => array( 'shortcode' ),
				'show_if' => array( 'layout', '=', 'hor' ),
				'usb_preview' => TRUE,
			),
		),

		// Main items section
		array(
			'main_gap' => array(
				'title' => __( 'Gap between Items', 'us' ),
				'type' => 'slider',
				'std' => '1.5rem',
				'options' => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
					'rem' => array(
						'min' => 0.0,
						'max' => 3.0,
						'step' => 0.1,
					),
					'em' => array(
						'min' => 0.0,
						'max' => 3.0,
						'step' => 0.1,
					),
				),
				'group' => _x( 'Main items', 'In menus', 'us' ),
				'usb_preview' => array(
					'css' => '--main-gap',
				),
			),
			'main_style' => array(
				'title' => us_translate( 'Style' ),
				'type' => 'radio',
				'options' => array(
					'links' => us_translate( 'Links' ),
					'blocks' => us_translate( 'Blocks' ),
				),
				'std' => 'links',
				'context' => array( 'shortcode' ),
				'group' => _x( 'Main items', 'In menus', 'us' ),
				'usb_preview' => array(
					'mod' => 'style',
				),
			),
			'main_ver_indent' => array(
				'title' => __( 'Vertical Indents', 'us' ),
				'type' => 'slider',
				'std' => '0.8em',
				'options' => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
					'rem' => array(
						'min' => 0.0,
						'max' => 3.0,
						'step' => 0.1,
					),
					'em' => array(
						'min' => 0.0,
						'max' => 3.0,
						'step' => 0.1,
					),
				),
				'cols' => 2,
				'show_if' => array( 'main_style', '=', 'blocks' ),
				'context' => array( 'shortcode' ),
				'group' => _x( 'Main items', 'In menus', 'us' ),
				'usb_preview' => array(
					'css' => '--main-ver-indent',
				),
			),
			'main_hor_indent' => array(
				'title' => __( 'Horizontal Indents', 'us' ),
				'description' => __( 'Examples:', 'us' ) . ' <span class="usof-example">0.8em</span>, <span class="usof-example">20px</span>',
				'type' => 'slider',
				'std' => '0.8em',
				'options' => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
					'rem' => array(
						'min' => 0.0,
						'max' => 3.0,
						'step' => 0.1,
					),
					'em' => array(
						'min' => 0.0,
						'max' => 3.0,
						'step' => 0.1,
					),
				),
				'cols' => 2,
				'show_if' => array( 'main_style', '=', 'blocks' ),
				'context' => array( 'shortcode' ),
				'group' => _x( 'Main items', 'In menus', 'us' ),
				'usb_preview' => array(
					'css' => '--main-hor-indent',
				),
			),
		),

		// Main items color section
		array(
			'main_color_bg' => array(
				'title' => __( 'Menu Item Background', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'std' => 'rgba(0,0,0,0.1)',
				'cols' => 2,
				'context' => array( 'shortcode' ),
				'show_if' => array( 'main_style', '=', 'blocks' ),
				'group' => _x( 'Main items', 'In menus', 'us' ),
				'usb_preview' => array(
					'css' => '--main-bg-color',
				),
			),
			'main_color_text' => array(
				'title' => __( 'Menu Item Text', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'with_gradient' => FALSE,
				'std' => 'inherit',
				'cols' => 2,
				'context' => array( 'shortcode' ),
				'group' => _x( 'Main items', 'In menus', 'us' ),
				'usb_preview' => array(
					'css' => '--main-color',
				),
			),
			'main_color_bg_hover' => array(
				'title' => __( 'Menu Item Background on hover', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'std' => '',
				'cols' => 2,
				'context' => array( 'shortcode' ),
				'show_if' => array( 'main_style', '=', 'blocks' ),
				'group' => _x( 'Main items', 'In menus', 'us' ),
				'usb_preview' => array(
					'css' => '--main-hover-bg-color',
				),
			),
			'main_color_text_hover' => array(
				'title' => __( 'Menu Item Text on hover', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'with_gradient' => FALSE,
				'std' => '',
				'cols' => 2,
				'context' => array( 'shortcode' ),
				'group' => _x( 'Main items', 'In menus', 'us' ),
				'usb_preview' => array(
					'css' => '--main-hover-color',
				),
			),
			'main_color_bg_active' => array(
				'title' => __( 'Active Menu Item Background', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'std' => '',
				'cols' => 2,
				'context' => array( 'shortcode' ),
				'show_if' => array( 'main_style', '=', 'blocks' ),
				'group' => _x( 'Main items', 'In menus', 'us' ),
				'usb_preview' => array(
					'css' => '--main-active-bg-color',
				),
			),
			'main_color_text_active' => array(
				'title' => __( 'Active Menu Item Text', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'with_gradient' => FALSE,
				'std' => '',
				'cols' => 2,
				'context' => array( 'shortcode' ),
				'group' => _x( 'Main items', 'In menus', 'us' ),
				'usb_preview' => array(
					'css' => '--main-active-color',
				),
			),
		),

		$conditional_params,
		$design_options_params
	),
);
