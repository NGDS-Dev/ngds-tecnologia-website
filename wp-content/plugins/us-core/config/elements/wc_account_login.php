<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: wc_account_login
 */

$misc = us_config( 'elements_misc' );
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
	'title' => us_translate( 'My Account Page', 'woocommerce' ) . ' – ' . us_translate( 'Login', 'woocommerce' ),
	'category' => 'WooCommerce',
	'icon' => 'fas fa-sign-in-alt',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'page' ),
	'hide_for_post_ids' => $hide_for_post_ids,
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		// General section
		array(
			'_hide_input' => array(
				'description' => sprintf( 'To enable/disable the "%s" form, go to the "%s" settings.', us_translate( 'Register', 'woocommerce' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=account' ) . '" target="_blank">' . us_translate( 'Accounts &amp; Privacy', 'woocommerce' ) . '</a>' ),
				'type' => 'message',
			),
			// TODO: hide if WC registration is disabled
			'title_size' => array(
				'title' => __( 'Title Size', 'us' ),
				'description' => $misc['desc_font_size'],
				'type' => 'text',
				'std' => '',
				'usb_preview' => array(
					'css' => '--title-size',
				),
			),
			'style' => array(
				'title' => us_translate( 'Style' ),
				'type' => 'radio',
				'options' => array(
					'default' => us_translate( 'Default' ),
					'none' => us_translate( 'None' ),
				),
				'std' => 'default',
				'usb_preview' => array(
					'mod' => 'style',
				),
			),
		),

		$conditional_params,
		$design_options_params
	),
);
