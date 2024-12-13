<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: My Account
 */

$_description = '';
$hide_for_post_ids = array();

if ( us_is_elm_editing_page() ) {
	$_description = __( 'This is default WooCommerce element and it\'s not editable.' );
	$_description .= '<br><br>';
	$_description .= sprintf( __( 'To customize the %s page use the following elements instead:' ), us_translate( 'My Account', 'woocommerce' ) );
	$_description .= '<br><br><strong>';
	$_description .= '&nbsp;&bull;&nbsp;' . us_config( 'elements/wc_account_login.title' ) . '<br>';
	$_description .= '&nbsp;&bull;&nbsp;' . us_config( 'elements/wc_account_navigation.title' ) . '<br>';
	$_description .= '&nbsp;&bull;&nbsp;' . us_config( 'elements/wc_account_content.title' ) . '<br>';
	$_description .= '</strong>';

	if ( function_exists( 'wc_get_page_id' ) ) {
		$hide_for_post_ids[] = wc_get_page_id( 'shop' );
		$hide_for_post_ids[] = wc_get_page_id( 'cart' );
		$hide_for_post_ids[] = wc_get_page_id( 'checkout' );
	}
}

return array(
	'title' => us_translate( 'My Account', 'woocommerce' ),
	'category' => 'WooCommerce',
	'icon' => 'icon-wpb-woocommerce',
	'show_for_post_types' => array( 'us_content_template', 'us_page_block', 'page' ),
	'hide_for_post_ids' => $hide_for_post_ids,
	'override_config_only' => TRUE,
	'hide_on_adding_list' => TRUE,
	'weight' => 100,
	'place_if' => class_exists( 'woocommerce' ),
	'params' => us_set_params_weight(

		// General params
		array(
			'_hide_input' => array(
				'description' => $_description,
				'type' => 'message',
			),
		)

	),
);
