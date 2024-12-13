<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: vc_tta_section
 *
 * Overloaded by UpSolution custom implementation.
 *
 * Dev note: if you want to change some of the default values or acceptable attributes, overload the shortcodes config.
 *
 * @var $shortcode         string Current shortcode name
 * @var $shortcode_base    string The original called shortcode name (differs if called an alias)
 * @var $content           string Shortcode's inner content
 * @var $classes           string Extend class names
 * @var $design_css_class  string Custom design css class
 *
 * @var $title             string Section title
 * @var $icon              string Icon
 * @var $i_position        string Icon position: 'left' / 'right'
 * @var $active            bool Tab is opened when page loads
 * @var $indents           string Indents type: '' / 'none'
 * @var $bg_color          string Background color
 * @var $text_color        string Text color
 * @var $title_tag         string Title HTML tag (inherited from wrapping vc_tta_tabs shortcode): 'div' / 'h2'/ 'h3'/ 'h4'/ 'h5'/ 'h6'/ 'p'
 * @var $el_class          string Extra class name
 * @var $el_id             string ID
 */

/*
 * Global variable for storing the tabs container options along with its child tabs options
 */
global $us_tabs_options;
if ( is_array( $us_tabs_options ) ) {
	// Get last index
	$us_tabs_options_keys = array_keys( $us_tabs_options );
	$index = end( $us_tabs_options_keys );
	if ( ! empty( $us_tabs_options[ $index ] ) ) {
		// Tab indexes start from 1
		if ( ! isset( $us_tabs_options[ $index ]['us_tab_index'] ) ) {
			$us_tabs_options[ $index ]['us_tab_index'] = 0;
		}
		$us_tabs_options[ $index ]['us_tab_index'] ++;

		extract( $us_tabs_options[ $index ] );
	}
	unset( $index, $us_tabs_options_keys );
}

$us_tab_index = isset( $us_tab_index ) ? $us_tab_index : 1;

// We could overload some atts at vc_tabs implementation, so apply them here as well
if ( isset( $us_tabs_atts[ $us_tab_index - 1 ] ) ) {
	foreach ( $us_tabs_atts[ $us_tab_index - 1 ] as $_key => $_value ) {
		${$_key} = $_value;
	}
}

// Define tag as it isn't set by the element
$title_tag = isset( $title_tag ) ? $title_tag : 'div';

$content_html = do_shortcode( $content );

$_atts['class'] = 'w-tabs-section';
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( $indents ) {
	$_atts['class'] .= ' no_indents';
}
if ( $active ) {
	$_atts['class'] .= ' active';
}
if ( $text_color ) {
	$_atts['class'] .= ' has_text_color';
}

// Hide the section with empty content, if it is not USBuilder page
if ( $content_html == '' AND ! usb_is_post_preview() ) {
	$_atts['class'] .= ' content-empty';
}

// Generate reqiured ID
if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
} else {
	$_atts['id'] = us_uniqid();
}

$inline_css = us_prepare_inline_css(
	array(
		'background' => us_get_color( $bg_color, /* Gradient */ TRUE ),
		'color' => us_get_color( $text_color ),
	)
);

$title_atts = array(
	'class' => 'w-tabs-section-title',
);
$content_atts = array(
	'class' => 'w-tabs-section-content',
	'id' => 'content-' . $_atts['id'],
);
$content_h_atts = array(
	'class' => 'w-tabs-section-content-h i-cf',
);

// Add atts for FAQs page
if ( ! empty( $us_faq_markup ) ) {
	$_atts['itemscope'] = '';
	$_atts['itemprop'] = 'mainEntity';
	$_atts['itemtype'] = 'https://schema.org/Question';
	$title_atts['itemprop'] = 'name';
	$content_atts['itemscope'] = '';
	$content_atts['itemprop'] = 'acceptedAnswer';
	$content_atts['itemtype'] = 'https://schema.org/Answer';
	$content_h_atts['itemprop'] = 'text';
}

// Apply filters to title text
$title = us_replace_dynamic_value( $title );
$title = wptexturize( $title );

$btn_atts = array(
	'class' => 'w-tabs-section-header' . ( $active ? ' active' : '' ),
	'aria-controls' => 'content-' . $_atts['id'],
	'aria-expanded' => $active ? 'true' : 'false',
);

// Pageable Container, should work via WPBakery only
$pageable_container = '';

if (
	! is_admin()
	AND ! wp_doing_ajax()
	AND class_exists( 'WPBakeryShortCode_Vc_Tta_Section' )
	AND isset( WPBakeryShortCode_Vc_Tta_Section::$tta_base_shortcode )
	AND WPBakeryShortCode_Vc_Tta_Section::$tta_base_shortcode->getShortcode() == 'vc_tta_pageable'
) {
	$atts['tab_id'] = $_atts['id'];

	// Enable pagination
	WPBakeryShortCode_Vc_Tta_Section::$section_info[] = $atts;

	// Add section counter
	WPBakeryShortCode_Vc_Tta_Section::$self_count ++;

	// Get Pageable Container shortcode attributes
	$pageable_container_atts = WPBakeryShortCode_Vc_Tta_Section::$tta_base_shortcode->getAtts();
	$pageable_active_section = ! empty( $pageable_container_atts['active_section'] ) ? (int) $pageable_container_atts['active_section'] : 0;

	// Get active state from Pageable Container Settings
	$active = $pageable_active_section == WPBakeryShortCode_Vc_Tta_Section::$self_count;

	// Pageable Container class
	$_atts['class'] .= 'vc_tta-panel';
	$_atts['class'] .= $active ? ' vc_active' : '';
	$content_atts['class'] = 'vc_tta-panel-body';

	// Pageable Container data
	$_atts['data-vc-content'] = '.vc_tta-panel-body';

	// Pageable Container Section markup
	$pageable_container = '<span class="vc_tta-panel-title"><a data-vc-container=".vc_tta-container" data-vc-accordion="" data-vc-target="#' . $atts['tab_id'] . '"></a></span>';

}

// Move classes related to design settings from the `.w-tabs-section` to the `.w-tabs-section-content`
if ( ! empty( $design_css_class ) ) {
	foreach( array( $design_css_class, 'us_animate_this' ) as $css_class_name ) {
		if ( strpos( $_atts['class'], $css_class_name ) !== FALSE ) {
			$_atts['class'] = str_replace( ' ' . $css_class_name, '', $_atts['class'] );
			$content_atts['class'] .= ' ' . $css_class_name;
		}
	}
}

// If icon is set
if ( $icon ) {
	$btn_atts['class'] .= ' with_icon';
}

// Add specific attributes for opening sections on AMP without JS
if ( us_amp() ) {
	$btn_atts['id'] = 'btn-' . $_atts['id'];
	$btn_atts['on'] = 'tap:' . $_atts['id'] . '.toggleClass(class="active",force=true)';

	foreach ( $us_tabs_atts as $amp_id ) {
		if ( $amp_id['el_id'] == $_atts['id'] ) {
			continue;
		}
		$btn_atts['on'] .= ',' . $amp_id['el_id'] . '.toggleClass(class="active",force=false)';
	}
}

// Output the element
$output = '<div' . us_implode_atts( $_atts ) . $inline_css . '>';
$output .= '<button' . us_implode_atts( $btn_atts ) . '>';
if ( $icon AND $i_position == 'left' ) {
	$output .= us_prepare_icon_tag( $icon );
}
$output .= '<' . $title_tag . us_implode_atts( $title_atts ) . '>' . $title . '</' . $title_tag . '>';
if ( $icon AND $i_position == 'right' ) {
	$output .= us_prepare_icon_tag( $icon );
}

$output .= '<div class="w-tabs-section-control"></div>';
$output .= '</button>';
$output .= '<div ' . us_implode_atts( $content_atts ) . '>';
$output .= $pageable_container;
$output .= '<div' . us_implode_atts( $content_h_atts ) . '>' . $content_html . '</div>';
$output .= '</div>';
$output .= '</div>';

echo $output;
