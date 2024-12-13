<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Hidden
 *
 * Simple hidden field.
 *
 * @var   $name  string Field name
 * @var   $id    string Field ID
 * @var   $field array Field options
 * @var   $value string Current value
 */

$input_atts = array(
	'name' => $name,
	'type' => 'hidden',
	'value' => $value,
);

// Field for editing in WPBakery
// Via the `wpb_vc_param_value` class WPBakery receives the final value
if ( isset( $field['us_vc_field'] ) ) {
	$input_atts['class'] = 'wpb_vc_param_value';
}

echo '<input ' . us_implode_atts( $input_atts ) . ' >';
