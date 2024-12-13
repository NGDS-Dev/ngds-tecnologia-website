<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: wc_account_content
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
	'title' => us_translate( 'My Account Page', 'woocommerce' ) . ' – ' . us_translate( 'Content' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-address-card',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'page' ),
	'hide_for_post_ids' => $hide_for_post_ids,
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		// General section
		array(
			'dashboard' => array(
				'switch_text' => __( 'Show the default Dashboard content', 'us' ),
				'type' => 'switch',
				'std' => 1,
				'usb_preview' => array(
					'toggle_class_inverse' => 'hide_dashboard',
				),
			),
		),

		$conditional_params,
		$design_options_params
	),
);
