<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: vc_row_inner
 *
 * Overloaded by UpSolution custom implementation.
 *
 * Dev note: if you want to change some of the default values or acceptable attributes, overload the shortcodes config.
 *
 * @var $shortcode         string Current shortcode name
 * @var $shortcode_base    string The original called shortcode name (differs if called an alias)
 * @var $content           string Shortcode's inner content
 *
 * @var $content_placement string Columns Content Position: 'top' / 'middle' / 'bottom'
 * @var $columns_gap       string
 * @var $el_id             string
 * @var $el_class          string
 * @var $css               string
 * @var $classes           string Extend class names
 */

// Class "wpb_row" is required for correct output of some plugins, like Ultimate Addons
$_atts['class'] = 'g-cols wpb_row';
$_atts['style'] = '';

// Disable Row if set, works in both builders
if ( ! empty( $atts['disable_element'] ) ) {
	if ( usb_is_post_preview() ) {
		$_atts['class'] .= ' disabled_for_usb';
	} elseif ( function_exists( 'vc_is_page_editable' ) AND vc_is_page_editable() ) {
		$_atts['class'] .= ' vc_hidden-lg vc_hidden-md vc_hidden-sm vc_hidden-xs';
	} else {
		return '';
	}
}

$_atts['class'] .= isset( $classes ) ? $classes : '';

// New Columns Layout after version 8.0
if ( us_get_option( 'live_builder' ) AND us_get_option( 'grid_columns_layout' ) ) {

	// Fallback for old columns layout (after version 8.0)
	$columns_fallback_result = us_vc_row_columns_fallback_helper( $shortcode_base, $content );
	if ( $columns === '1' AND ! empty( $columns_fallback_result['columns'] ) ) {
		$columns = $columns_fallback_result['columns'];
	}
	if ( ! empty( $columns_fallback_result['columns_layout'] ) ) {
		$columns_layout = $columns_fallback_result['columns_layout'];
	}

	// Fallback for $gap param (after version 8.0)
	if ( $columns_type ) {

		// If the "Additional gap" was set, get its value and double it as new columns gap
		// Example: 5px becomes 10px
		// Example: 0.7rem becomes 1.4rem
		if ( ! empty( $gap ) AND preg_match( '~^(\d*\.?\d*)(.*)$~', $gap, $matches ) ) {
			$columns_gap = ( $matches[1] * 2 ) . $matches[2];
		}
	} elseif ( ! empty( $gap ) AND ! is_numeric( $gap ) ) {
		$columns_gap = 'calc(3rem + ' . $gap . ')';
	}

	$_atts['class'] .= ' via_grid';
	$_atts['class'] .= ' cols_' . $columns;
	$_atts['class'] .= ' laptops-cols_' . $laptops_columns;
	$_atts['class'] .= ' tablets-cols_' . $tablets_columns;
	$_atts['class'] .= ' mobiles-cols_' . $mobiles_columns;
	
	// Responsive gap
	if ( $columns_gap_array = (array) us_get_responsive_values( $columns_gap ) ) {
		foreach ( $columns_gap_array as $state => $value ) {
			if ( $state == 'default' ) {
				$_atts['style'] .= sprintf( '--gap:%s;', $value );
			} else {
				$_atts['style'] .= sprintf( '--%s-gap:%s;', $state, $value );
			}
		}
	} else {
		$_atts['style'] .= '--gap:' . $columns_gap . ';';
	}

	// Add custom columns layout via inline style
	if ( $columns === 'custom' AND ! empty( $columns_layout ) ) {
		$_atts['style'] .= '--custom-columns:' . $columns_layout;
	}

} else {
	$_atts['class'] .= ' via_flex';
	if ( ! empty( $gap ) ) {
		$_atts['style'] .= '--additional-gap:' . $gap . ';';
	}
}

$_atts['class'] .= ' valign_' . $content_placement;

if ( ! empty( $columns_type ) ) {
	$_atts['class'] .= ' type_boxes';
} else {
	$_atts['class'] .= ' type_default';
}
if ( ! empty( $columns_reverse ) ) {
	$_atts['class'] .= ' reversed';
}
if ( empty( $ignore_columns_stacking ) ) {
	$_atts['class'] .= ' stacking_default';
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Output the element
$output = '<div' . us_implode_atts( $_atts ) . '>';
$output .= do_shortcode( $content );
$output .= '</div>';

echo $output;
