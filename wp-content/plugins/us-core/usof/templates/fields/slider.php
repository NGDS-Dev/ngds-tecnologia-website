<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Slider
 *
 * Slider-selector of the integer value within some range.
 *
 * @param $field ['title'] string Field title
 * @param $field ['description'] string Field title
 * @param $field ['options'] array with "min", "max", "step" values
 * @param $field ['std'] string Default value
 *
 * @var   $id    string Field ID
 * @var   $field array Field options
 *
 * @var   $name  string Field name
 * @var   $value string Current value
 */

// Get range settings for units
$units = array(
	'px' => array( // default unit
		'max' => 100, // max value
		'min' => 0, // min value
		'step' => 1,
	),
);
if ( isset( $field['options'] ) AND is_array( $field['options'] ) ) {
	$units = $field['options'];
}

$unit_keys = array_keys( $units );
$first_unit = isset( $unit_keys[ 0 ] ) ? $unit_keys[ 0 ] : /* default */'';
$default_unit = $first_unit;

// Main attributes
$_atts = array(
	'class' => 'usof-slider',
);
if ( count( $units ) > 1 ) {
	$_atts[ 'class' ] .= ' with_units'; // add class when more then 1 units in config
}

// Get screen values
$values = $value;
// Get an array of responsive values if they exist
if ( is_string( $values ) AND strpos( $values, rawurlencode( '{' ) ) === 0 ) {
	$values = json_decode( rawurldecode( $values ), /* as array */TRUE );
}
if ( ! is_array( $values ) ) {
	$values = array( 'default' => $values );
}

// Prepare values for regex
$units_expression = implode( '|', array_map( 'preg_quote', $unit_keys ) ); // cut the string to get unit

// Check and normalize values
foreach ( $values as $screen => $current_value ) {
	$current_value = esc_attr( trim( (string) $current_value ) );
	$current_value = str_replace( ',', '.', $current_value );
	$current_unit = $first_unit;

	// Normalize value
	if ( preg_match( '/^((-?\d+)(\.)?(\d+)?)([a-z\%]+)?$/i', $current_value, $matches ) ) {
		// Get current unit
		if ( isset( $matches[ /* unit */5 ] ) ) {
			$_unit = (string) $matches[ /* unit */5 ];
			if ( in_array( $_unit, $unit_keys ) ) {
				$current_unit = $_unit;
			}
		}
		$current_value = $matches[ /* value */1 ]; // values without unit
	}

	// Set default unit
	if ( $screen == 'default' ) {
		$default_unit = $current_unit;
	}

	$values[ $screen ] = $current_value . $current_unit;
}

$min = us_arr_path( $units, $default_unit . '.min', /* default */0 );
$max = us_arr_path( $units, $default_unit . '.max', /* default */100 );

$default_value = us_arr_path( $values, 'default', /* default */$value );
$float_value = (float) $default_value;
$offset = 100;

// Calculate slider range offset in percents based on current "min" and "max" values
if ( $max >= $min ) {
	if ( $float_value < $min ) {
		$offset = 0;
	} elseif ( $float_value >= $max ) {
		$offset = 100;
	} else {
		$offset = ( min( $max, max( $min, $float_value ) ) - $min ) * 100 / ( $max - $min );
	}
}

// Range attributes
$range_atts = array(
	'class' => 'usof-slider-range',
	'style' => sprintf( '%s:%s', ( is_rtl() ? 'right' : 'left' ), $offset ) . '%',
);

// Data for export to JS
$_atts['onclick'] = us_pass_data_to_js( $units, /* onclick */FALSE );

// Support for responsive values
if ( is_array( $values ) ) {
	$values = count( $values ) > 1
		? rawurlencode( json_encode( $values ) )
		: $default_value;
}

// Hidden field containing the end result
$input_hidden = array(
	'name' => $name,
	'type' => 'hidden',
	'value' => $values,
);

// Field for editing in Visual Composer
if ( isset( $field['us_vc_field'] ) ) {
	$input_hidden['class'] = 'wpb_vc_param_value';
}

// Output
$output = '<div ' . us_implode_atts( $_atts ) . '>';
$output .= '<div class="usof-slider-selector">';
$output .= '<input type="text" value="' . esc_attr( $default_value ) . '">';
$output .= '<div class="usof-slider-selector-units">';
foreach ( $unit_keys as $unit ) {
	$unit_atts = array(
		'class' => 'usof-slider-selector-unit',
		'data-unit' => $unit,
	);
	// Case for empty unit, like in "line-height" option
	if ( empty( $unit ) ) {
		$unit = '<i>' . __( 'No units', 'us' ) . '</i>';
	}
	$output .= '<div ' . us_implode_atts( $unit_atts ) . '>' . $unit . '</div>';
}
$output .= '</div>'; // .usof-slider-selector-units
$output .= '</div>'; // .usof-slider-selector
// TODO: Check if `<input type="range" min="" max="" step="">` can be customized and replace
$output .= '<div class="usof-slider-box">';
$output .= '<div class="usof-slider-box-h">';
$output .= '<div ' . us_implode_atts( $range_atts ) . '>';
$output .= '<div class="usof-slider-runner" draggable="true"></div>';
$output .= '</div>';
$output .= '</div>'; // .usof-slider-box-h
$output .= '</div>'; // .usof-slider-box
$output .= '<input ' . us_implode_atts( $input_hidden ) . '>';
$output .= '</div>'; // .usof-slider

echo $output;
