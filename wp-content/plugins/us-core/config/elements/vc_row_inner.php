<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: vs_row_inner
 */

$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * General section
 *
 * @var array
 */
$general_params = array();

// Copy the parameters from vc_row
$copy_params = array(
	'columns',
	'columns_gap',
	'columns_layout',
	'columns_reverse',
	'content_placement',
	'columns_type',
	'gap',
	'laptops_columns',
	'mobiles_columns',
	'tablets_columns',
	'ignore_columns_stacking',
	'disable_element',
);
$vc_row_params = us_config( 'elements/vc_row.params', array() );
foreach ( $copy_params as $param_name ) {
	if ( ! empty( $vc_row_params[ $param_name ] ) ) {

		// Remove 'elm' name for correct preview in builder
		if ( isset( $vc_row_params[ $param_name ]['usb_preview']['elm'] ) ) {
			unset( $vc_row_params[ $param_name ]['usb_preview']['elm'] );
		}

		$general_params[ $param_name ] = $vc_row_params[ $param_name ];
	}
}

/**
 * @return array
 */
return array(
	'title' => __( 'Inner Row', 'us' ),
	'category' => __( 'Containers', 'us' ),
	'is_container' => TRUE,
	'usb_moving_only_x_axis' => TRUE,
	'icon' => 'fas fa-border-all',
	'as_child' => array(
		'only' => 'vc_column,vc_tta_section',
	),
	'as_parent' => array(
		'only' => 'vc_column_inner'
	),
	'params' => us_set_params_weight(
		$general_params,
		$conditional_params,
		$design_options_params
	),

	// Default VC params which are not supported by the theme
	'vc_remove_params' => array(
		'equal_height',
		'rtl_reverse',
	),
);
