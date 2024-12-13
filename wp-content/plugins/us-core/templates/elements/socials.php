<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Social Links element
 */

$_atts['class'] = 'w-socials';
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Fallback since 7.1
if ( ! empty( $align ) ) {
	$_atts['class'] .= ' align_' . $align;
}

$_atts['class'] .= ' color_' . $icons_color;
$_atts['class'] .= ' shape_' . $shape;
if ( $shape != 'none' ) {
	$_atts['class'] .= ' style_' . $style;
	$_atts['class'] .= ' hover_' . $hover;

	// 'header' context doesn't have the 'stretch' option
	if ( ! empty( $stretch ) ) {
		$_atts['class'] .= ' stretch';
	}
}

if ( $us_elm_context == 'shortcode' ) {
	$_atts['style'] = '';

	if ( ! in_array( $gap, array( '', '0', '0em', '0px' ) ) ) {
		$_atts['style'] .= '--gap:' . $gap . ';';
	}

	// Used in Widget
	if ( ! empty( $size ) ) {
		$_atts['style'] .= 'font-size:' . $size;
	}
} else {
	$hide_tooltip = TRUE; // force hiding tooltip in header
}

// Output the element
$output = '<div' . us_implode_atts( $_atts ) . '>';
$output .= '<div class="w-socials-list">';

$social_links = us_config( 'social_links' );

// Decoding items in case it is shortcode
if ( is_string( $items ) ) {
	$items = json_decode( urldecode( $items ), TRUE );
}
if ( ! is_array( $items ) ) {
	$items = array();
}

foreach ( $items as $index => $item ) {

	$item_custom_bg = '';
	$item_title = isset( $social_links[ $item['type'] ] ) ? $social_links[ $item['type'] ] : $item['type'];
	$item_url = us_arr_path( $item, 'url', '' );

	// Link
	$link_atts = us_generate_link_atts( $item_url );
	$link_atts['class'] = 'w-socials-item-link';

	// Skip empty links
	if ( empty( $link_atts['href'] ) AND ! usb_is_preview() ) {
		continue;
	}

	// Custom type
	if ( $item['type'] == 'custom' ) {
		$item_icon = $item['icon'];

		// Add fallback "Title" if title is not set by user
		$item_title = ! empty( $item['title'] ) ? $item['title'] : us_translate( 'Title' );

		$item_custom_bg = us_prepare_inline_css(
			array(
				'background' => us_get_color( us_arr_path( $item, 'color', '' ), /* Gradient */ TRUE ),
			)
		);

		if ( $icons_color == 'brand' AND ! empty( $item['color'] ) ) {
			$link_atts['style'] = 'color:' . us_get_color( $item['color'] );
		}

	// 500px
	} elseif ( $item['type'] == 's500px' ) {
		$item_icon = 'fab|500px';

	// Vimeo
	} elseif ( $item['type'] == 'vimeo' ) {
		$item_icon = 'fab|vimeo-v';

	// WeChat
	} elseif ( $item['type'] == 'wechat' ) {
		$item_icon = 'fab|weixin';

	// Tripadvisor fallback (due to removing their icon from Font Awesome)
	} elseif ( $item['type'] == 'tripadvisor' ) {
		$item_icon = 'fas|plane';

	// RSS
	} elseif ( $item['type'] == 'rss' ) {
		$item_icon = 'fas|rss';

	// Email
	} elseif ( $item['type'] == 'email' ) {
		$item_icon = 'fas|envelope';

	} else {
		$item_icon = 'fab|' . $item['type'];
	}

	if ( ! isset( $link_atts['title'] ) ) {
		$link_atts['title'] = $item_title;
	}
	$link_atts['aria-label'] = $item_title;

	$output .= '<div class="w-socials-item ' . $item['type'] . '">';

	$output .= '<a' . us_implode_atts( $link_atts ) . '>';
	$output .= '<span class="w-socials-item-link-hover"' . $item_custom_bg . '></span>';
	$output .= us_prepare_icon_tag( $item_icon );
	$output .= '</a>';

	if ( ! $hide_tooltip ) {
		$output .= '<div class="w-socials-item-popup"><span>' . strip_tags( $item_title ) . '</span></div>';
	}
	$output .= '</div>';
}

$output .= '</div></div>';

echo $output;
