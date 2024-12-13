<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Post Views Counter
 *
 * @var $us_elm_context string Item context
 * @var $classes string Custom classes
 * @var $hide_empty bool Hide this element if its value is empty
 * @var $text_before string Text before value output
 * @var $text_after string Text after value output
 * @var $el_id string Item Id
 * @var $result_format bool Use "K" shorthand for thousands
 * @var $result_format_separator string Thousand separator
 * @var $icon string Icon
 *
 */

if ( ! function_exists( 'pvc_get_post_views' ) AND ! usb_is_post_preview() ) {
	return;
}

global $us_grid_item_type;

if ( $us_elm_context == 'grid' AND $us_grid_item_type == 'term' ) {
	return;
} elseif ( $us_elm_context == 'shortcode' AND ( is_tax() OR is_tag() OR is_category() ) ) {
	return;
}

$_atts['class'] = 'w-post-elm post_views';
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( ! empty( $el_id ) AND $us_elm_context == 'shortcode' ) {
	$_atts['id'] = $el_id;
}

// Text before value
$text_before = trim( (string) $text_before );
if ( $text_before != '' ) {
	$text_before = '<span class="w-post-elm-before">' . $text_before . ' </span>';
}

// Text after value
$text_after = trim( (string) $text_after );
if ( $text_after != '' ) {
	$text_after = '<span class="w-post-elm-after"> ' . $text_after . '</span>';
}

// Get the value
$value = ! usb_is_post_preview()
	? pvc_get_post_views()
	: 0;
$value = (int) $value;
if ( $result_thousand_short AND $value > 999 ) {
	$value = number_format( floor( $value / 1000 ), 0, '', $result_thousand_separator );
	$value .= 'K';
} else {
	$value = number_format( $value, 0, '', $result_thousand_separator );
}

// Output the element
$output = '<div' . us_implode_atts( $_atts ) . '>';
if ( ! empty( $icon ) ) {
	$output .= us_prepare_icon_tag( $icon );
}
if ( $text_before ) {
	$output .= $text_before;
}
$output .= $value;
if ( $text_after ) {
	$output .= $text_after;
}
$output .= '</div>';

echo $output;
