<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: design_options
 *
 * Design options.
 *
 * @var $name string Field name
 * @var $params array Fields options
 * @var $states array States list
 * @var $classes string Class for value field needed to support js_composer
 * @var $value array Current value
 *
 * @param $field [us_vc_field] bool Field used in Visual Composer
 */

$name = isset( $name ) ? $name : '';
$value = ( isset( $value ) AND is_string( $value ) ) ? $value : '';
if ( ! isset( $params ) ) {
	$params = isset( $field['params'] ) ? $field['params'] : array();
}
if ( ! isset( $classes ) ) {
	$classes = isset( $field['classes'] ) ? $field['classes'] : '';
}

// Field for editing in WPBakery
// Via the `wpb_vc_param_value` class WPBakery receives the final value
if ( isset( $field['us_vc_field'] ) ) {
	$classes .= ' wpb_vc_param_value';
}

$out_params = array();

// Group params
if ( $groups = wp_list_pluck( $params, 'group' ) ) {
	foreach ( array_unique( array_values( $groups ) ) as $group ) {
		$group_id = str_replace( ' ' , '_', $group );
		$header = '<div class="usof-design-options-header" data-accordion-id="' . esc_attr( $group_id ) . '">';
		$header .= '<span class="usof-design-options-header-title">' . strip_tags( $group ) . '</span>';
		$header .= '<span class="usof-design-options-responsive ui-icon_devices"></span>';
		$header .= '<span class="usof-design-options-reset">' . strip_tags( __( 'Reset', 'us' ) ) . '</span>';
		$header .= '</div>';
		$out_params[ $group_id ]['name'] = $header;
	}
}

// Parameters to be added to inline css
foreach ( $params as $param_name => $param ) {

	$field = us_get_template(
		'usof/templates/field', array(
			'name' => $param_name,
			'id' => 'usof_design_' . $param_name,
			'field' => $param,
			'std' => '',
		)
	);
	$group_id = str_replace( ' ' , '_', $param['group'] );
	if ( ! empty( $group_id ) AND array_key_exists( $group_id, $out_params ) ) {
		$out_params[ $group_id ][] = $field;
	} else {
		$out_params[] = $field;
	}
}

// HTML output structure
$output = '<div class="usof-design-options" ' . us_pass_data_to_js( us_get_responsive_states( /* only keys */TRUE ) ) . '>';

// Structure for displaying buttons Copy/Paste and import the value
$output .= '<div class="usof-design-options-import">';

$output .= '<div class="usof-design-options-import-header">';
$output .= '<button class="usof-button" data-action="copy" type="button" disabled>' . strip_tags( us_translate( 'Copy' ) ) . '</button>';
$output .= '<button class="usof-button" data-action="paste" type="button">' . strip_tags( us_translate( 'Paste' ) ) . '</button>';
$output .= '</div>';

// Required field for working design options, all parameters values will be written into it
$input_atts = array(
	'class' => 'usof_design_value ' . $classes,
	'name' => $name,
);

$output .= '<textarea' . us_implode_atts( $input_atts ) . '>' . $value . '</textarea>';
$output .= '<div class="usof-design-options-import-novalid">' . strip_tags( us_translate( 'Invalid value.' ) ) . '</div>';

$output .= '<div class="usof-design-options-import-footer">';
$output .= '<button class="usof-button" data-action="cancel" type="button">' . strip_tags( us_translate( 'Close' ) ) . '</button>';
$output .= '<button class="usof-button button-primary" data-action="apply" type="button">' . strip_tags( us_translate( 'Apply' ) ) . '</button>';
$output .='</div>';

$output .= '</div>';

if ( ! empty( $out_params ) ) {
	foreach ( $out_params as $id => $param ) {
		if ( isset( $param['name'] ) ) {
			$output .= $param['name'];
			unset( $param['name'] );
		}
		$output .= '<div class="usof-design-options-content" data-accordion-content="' . esc_attr( $id ) . '">';

		// Get the layout of responsive buttons
		$output .= usof_get_responsive_buttons();

		// This block is duplicated on the frontend side to implement settings for different devices
		$output .= '<div class="usof-design-options-content-fields" data-responsive-state-content="default">';
		$output .= is_array( $param ) ? implode( '', $param ) : $param;
		$output .= '</div>';

		$output .= '</div>';
	}
}
$output .= '</div>';

echo $output;
