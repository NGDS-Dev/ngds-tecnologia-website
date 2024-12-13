<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: product_gallery
 */

$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * @return array
 */
return array(
	'title' => us_translate( 'Product gallery', 'woocommerce' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-images',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'product' ),
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		// General section
		array(
			'_hide_input' => array(
				'description' => sprintf( __( 'Edit Product gallery appearance on %sTheme Options%s.', 'us' ), '<a target="_blank" href="' . admin_url() . 'admin.php?page=us-theme-options#woocommerce">', '</a>' ),
				'type' => 'message',
			),
		),

		$conditional_params,
		$design_options_params
	),
);
