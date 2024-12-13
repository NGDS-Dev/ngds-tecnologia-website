<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Switch
 *
 * On-off switcher
 *
 * @var   $name  string Field name
 * @var   $id    string Field ID
 * @var   $field array Field options
 *
 * @param $field ['title'] string Field title
 * @param $field ['description'] string Field title
 * @param $field ['options'] array Array of two key => title pairs
 * @param $field ['text'] array Additional text to show right near the switcher
 *
 * @var   $value string Current value
 *
 */

$input_atts = array(
	'name' => $name,
	'value' => (int) $value,
	'type' => 'checkbox',
);
if ( ! empty( $field['disabled'] ) ) {
	$input_atts['disabled'] = '';
}
// For control in html output
if ( us_arr_path( $input_atts, 'value' ) === 1 ) {
	$input_atts['checked'] = 'checked';
}

$output = '<div class="usof-switcher">';

/*
 * Note: Visual Composer has specific handling of `type=checkbox` fields,
 * so we need to add a `type=hidden` field for it to work properly
 */
if ( isset( $field['us_vc_field'] ) ) {
	$hidden_atts = array(
		// Note: `js_hidden` class exclude field to bind by name in usof, this allows control of fields for inputs
		'class' => 'wpb_vc_param_value js_hidden',
		'name' => $name,
		'type' => 'hidden',
		'value' => $value,
	);
	$output .= '<input '. us_implode_atts( $hidden_atts ) .'>';
}

$output .= '<label>';
$output .= '<input' . us_implode_atts( $input_atts ) . '>';
$output .= '<span class="usof-switcher-box"><i></i></span>';
if ( ! empty( $field['switch_text'] ) ) {
	$output .= '<span class="usof-switcher-text">' . $field['switch_text'] . '</span>';
}
$output .= '</label></div>';

echo $output;
