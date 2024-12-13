<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode attributes
 *
 * @var $el_class
 * @var $css_animation
 * @var $css
 * @var $content - shortcode content
 * @var $show_more_toggle - Hide part of a content with the "Show More" link
 * @var $show_more_toggle_height - Height of visible content
 * Shortcode class
 * @var $this WPBakeryShortCode_VC_Column_text
 * @var $classes string Extend class names
 */

$_atts['class'] = 'wpb_text_column';
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Add specific classes, when "Show More" is enabled
if ( $show_more_toggle AND ! us_amp() ) {
	$_atts['class'] .= ' with_collapsible_content';
	$_atts['data-content-height'] = $show_more_toggle_height;
}

// Output the element
$output = '<div'. us_implode_atts( $_atts ) .'>';
$output .= '<div class="wpb_wrapper">';
$output .= apply_filters( 'widget_text_content', $content );
$output .= '</div>';

if ( $show_more_toggle AND ! us_amp() ) {
	$output .= '<div class="toggle-links align_' . $show_more_toggle_alignment . '">';
	$output .= '<button class="collapsible-content-more">' . strip_tags( $show_more_toggle_text_more ) . '</button>';
	$output .= '<button class="collapsible-content-less">' . strip_tags( $show_more_toggle_text_less ) . '</button>';
	$output .= '</div>';
}

$output .= '</div>';

echo $output;
