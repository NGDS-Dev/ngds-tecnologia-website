<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Button element
 *
 * @var $link string Link json variables
 */

global $us_grid_item_type;

$_atts['class'] = 'w-btn ' . us_get_btn_class( $style );
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

$wrapper_class = '';
if ( $us_elm_context == 'shortcode' ) {

	// Set alignment classes
	if ( $_align_classes = us_get_class_by_responsive_values( $align, /* template */'align_%s' ) ) {
		$wrapper_class .= ' ' . $_align_classes;
	}

	// Moving classes `hide_on_*` from the button to the wrapper
	$hide_on_prefix = 'hide_on_';
	if ( strpos( $_atts['class'], $hide_on_prefix ) !== FALSE ) {
		$classes = &$_atts['class'];
		foreach ( (array) us_get_responsive_states( /* only keys */TRUE ) as $state ) {
			$hide_classname = $hide_on_prefix . $state;
			if ( strpos( $classes, $hide_classname ) !== FALSE ) {
				$wrapper_class .= ' ' . $hide_classname;
				$classes = preg_replace( '/\s?' . $hide_classname . '/', '', $classes );
			}
		}
		unset( $classes );
	}
}

// Icon
$icon_html = '';
if ( ! empty( $icon ) ) {
	$icon_html = us_prepare_icon_tag( $icon );
	$_atts['class'] .= ' icon_at' . $iconpos;
}

// Apply filters to button label
$label = us_replace_dynamic_value( $label );
$label = trim( strip_tags( $label, '<br>' ) );
$label = wptexturize( $label );

if ( $label == '' ) {
	$_atts['class'] .= ' text_none';
	$_atts['aria-label'] = us_translate( 'Button' );
}

// Default button html tag
$tag = 'a';

// Get link attributes
$link_atts = us_generate_link_atts( $link, /* additional data */array( 'label' => $label ) );

// If the result URL is empty
if ( empty( $link_atts['href'] ) ) {

	// Do not output the element with empty link, if set
	if (
		$hide_with_empty_link
		AND ! usb_is_post_preview()
	) {
		return;

		// in other cases use the <button> html tag
	} else {
		$tag = 'button';
	}
}

// Output the element
$output = '';

if ( $us_elm_context == 'shortcode' ) {
	$output .= '<div class="w-btn-wrapper' . $wrapper_class . '">';
}

$output .= '<' . $tag . us_implode_atts( $_atts + $link_atts ) . '>';
if ( $iconpos == 'left' ) {
	$output .= $icon_html;
}
if ( $label !== '' OR usb_is_preview() ) {
	$output .= '<span class="w-btn-label">' . $label . '</span>';
}
if ( $iconpos == 'right' ) {
	$output .= $icon_html;
}
$output .= '</' . $tag . '>';

if ( $us_elm_context == 'shortcode' ) {
	$output .= '</div>';
}

echo $output;
