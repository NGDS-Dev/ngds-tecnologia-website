<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output text element
 *
 * @var $text           string
 * @var $size           int Text size
 * @var $size_tablets   int Text size for tablets
 * @var $size_mobiles   int Text size for mobiles
 * @var $link           string Link
 * @var $icon           string FontAwesome or Material icon
 * @var $font           string Font Source
 * @var $color          string Custom text color
 * @var $design_options array
 * @var $_atts['class'] string
 * @var $id             string
 */

$_atts['class'] = 'w-text';
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( $us_elm_context == 'header' AND empty( $wrap ) ) {
	$_atts['class'] .= ' nowrap';
}

// Fallback since version 7.1
if ( ! empty( $align ) ) {
	$_atts['class'] .= ' align_' . $align;
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Apply filters to text
$text = us_replace_dynamic_value( $text );
$text = strip_tags( $text, '<br><code><i><small><span><strong><sub><sup>' );
$text = wptexturize( $text );

// Link
$link_atts = us_generate_link_atts( $link, /* additional data */array( 'label' => $text ) );

if ( ! empty( $link_atts['href'] ) ) {
	$link_tag = 'a';

	// Add placeholder aria-label for Accessibility
	if ( $text === '' AND empty( $link_atts['title'] ) ) {
		$link_atts['aria-label'] = us_translate( 'Link' );
	}

} else {

	// Do not output the element with empty link, if set
	if (
		$hide_with_empty_link
		AND ! usb_is_post_preview()
	) {
		return;
	}

	$link_tag = 'span';
}

$link_atts['class'] = 'w-text-h';

// Icon
$icon_html = '';
if ( ! empty( $icon ) ) {
	$icon_html = us_prepare_icon_tag( $icon );
	$_atts['class'] .= ' icon_at' . $iconpos;
}

// Output the element
$output = '<' . $tag . us_implode_atts( $_atts ) . '>';
$output .= '<' . $link_tag . us_implode_atts( $link_atts ) . '>';

if ( $iconpos == 'left' ) {
	$output .= $icon_html;
}
$output .= '<span class="w-text-value">' . $text . '</span>';
if ( $iconpos == 'right' ) {
	$output .= $icon_html;
}

$output .= '</' . $link_tag . '>';
$output .= '</' . $tag . '>';

echo $output;
