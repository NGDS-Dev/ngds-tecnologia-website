<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Horizontal Wrapper
 */

$_atts['class'] = 'w-hwrapper';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['class'] .= ' valign_' . $valign;
$_atts['class'] .= ( $wrap ) ? ' wrap' : '';
$_atts['class'] .= ( $stack_on_mobiles ) ? ' stack_on_mobiles' : '';

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Set alignment classes
if ( $_align_classes = us_get_class_by_responsive_values( $alignment, /* template */'align_%s' ) ) {
	$_atts['class'] .= ' ' . $_align_classes;
}

if ( trim( (string) $inner_items_gap ) != '1.2rem' ) {
	$_atts['style'] = '--hwrapper-gap:' . $inner_items_gap;
}

// Output the element
$output = '<div' . us_implode_atts( $_atts ) . '>';
$output .= do_shortcode( $content );
$output .= '</div>';

echo $output;
