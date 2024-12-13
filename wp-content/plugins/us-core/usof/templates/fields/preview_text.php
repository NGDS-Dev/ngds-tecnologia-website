<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Text Preview
 */

global $usof_options;

// Preview Data
$typography_tag = isset( $typography_tag ) ? $typography_tag : 'body';
$preview_params = isset( $usof_options[ $typography_tag ] ) ? $usof_options[ $typography_tag ] : array();
$preview_text = isset( $text ) ? $text : '0123456789 ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz';

$_atts = array(
	'class' => 'usof-text-preview',
	'style' => '',
);

$wrapper_atts = array(
	'class' => 'usof-text-preview-wrapper',
	'style' => '',
);

// Wrapper colors
$wrapper_atts['style'] .= 'background: ' . $usof_options['color_content_bg'] . ';';
if ( $typography_tag == 'body' ) {
	$wrapper_atts['style'] .= 'color: ' . $usof_options['color_content_text'] . ';';
} else {
	$wrapper_atts['style'] .= 'color: ' . $usof_options['color_content_heading'] . ';';
}

// Add inline css properties
foreach ( $preview_params as $param_name => $param_value ) {
	if ( ! empty( $param_value ) AND is_string( $param_value ) ) {

		// Get an array of responsive values if they exist
		if ( strpos( $param_value, rawurlencode( '{' ) ) === 0 ) {
			$values_array = json_decode( rawurldecode( $param_value ), /* as array */TRUE );
			$param_value = $values_array['default'];
		}

		// Skip unneeded values
		if ( $param_name == 'font-family' AND in_array( $param_value, array( 'none', 'inherit' ) ) ) {
			continue;
		}

		// Skip unsupported CSS property
		if ( $param_name == 'bold-font-weight' ) {
			continue;
		}

		$_atts['style'] .= sprintf( '%s: %s;', $param_name, $param_value );
	}
}

// Generate gradient styles for Headings
$gradient_style = '';
if (
	$typography_tag != 'body'
	AND $color_value = $usof_options['color_content_heading']
	AND strpos( $color_value, 'gradient' ) !== FALSE
	AND strpos( $_atts['style'], 'color' ) === FALSE
) {
	$gradient_style .= ' style="';
	$gradient_style .= 'background-image:' . $color_value . ';';
	$gradient_style .= '-webkit-background-clip: text;';
	$gradient_style .= 'color: transparent;';
	$gradient_style .= '"';
}

$output = '<div' . us_implode_atts( $wrapper_atts ) . '>';
$output .= '<div' . us_implode_atts( $_atts ) . '>';
$output .= '<div' . $gradient_style . '>' . $preview_text . '</div>';
$output .= '</div>';
$output .= '</div>';

$output .= '<div class="usof-preview-params-json"' . us_pass_data_to_js( $preview_params ) . '></div>';

echo $output;
