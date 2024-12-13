<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_iconbox
 *
 * Dev note: if you want to change some of the default values or acceptable attributes, overload the shortcodes config.
 *
 * @param $icon           string Icon
 * @param $style          string Icon style: 'default' / 'circle' / 'outlined'
 * @param $color          string Icon color: 'primary' / 'secondary' / 'light' / 'contrast' / 'custom'
 * @param $icon_color     string Icon color value
 * @param $circle_color   string Icon circle color
 * @param $iconpos        string Icon position: 'top' / 'left'
 * @param $size           string Icon size in pixels
 * @param $img            int Icon image (from WordPress media)
 * @param $title          string Title
 * @param $title_tag      string Title HTML tag: 'div' / 'h2'/ 'h3'/ 'h4'/ 'h5'/ 'h6'/ 'p'
 * @param $title_size     string Title Size
 * @param $link           string Link in a serialized format: 'url:http%3A%2F%2Fwordpress.org|title:WP%20Website|target:_blank|rel:nofollow'
 * @param $alignment      string Alignment of the whole element
 * @param $el_class       string Extra class name
 * @var   $shortcode      string Current shortcode name
 * @var   $shortcode_base string The original called shortcode name (differs if called an alias)
 * @var   $content        string Shortcode's inner content
 * @var   $classes        string Extend class names
 *
 */

$_atts['class'] = 'w-iconbox';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['class'] .= ' iconpos_' . $iconpos;
$_atts['class'] .= ' style_' . $style;
$_atts['class'] .= ' color_' . $color;
$_atts['class'] .= ' align_' . $alignment;

if ( $content == '' ) {
	$_atts['class'] .= ' no_text';
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Apply filters to title
$title = us_replace_dynamic_value( $title );
$title = wptexturize( $title );

if ( $title == '' ) {
	$_atts['class'] .= ' no_title';
}

// Use image instead icon, if set
$icon_html = '';
if ( $img = us_replace_dynamic_value( $img, /* acf_format */ FALSE ) ) {
	$_atts['class'] .= ' icontype_img';

	// Get file MIME type to output SVG into HTML (not inside <img>)
	if (
		$mime_type = get_post_mime_type( $img )
		AND strpos( $mime_type, 'svg' ) !== FALSE
		AND $svg_filepath = get_attached_file( $img )
		AND $svg_filepath = realpath( $svg_filepath )
	) {
		// Don't use "include()" the file to avoid SVG parsing errors
		$icon_html = file_get_contents( $svg_filepath );
		$icon_html = apply_filters( 'us_iconbox_svg_output', $icon_html, $img );

		// In other case use file as image
	} else {
		$icon_html = wp_get_attachment_image( $img, 'full' );
	}

	if ( empty( $icon_html ) ) {
		$icon_html = us_get_img_placeholder( 'full' );
	}

} elseif ( $icon != '' ) {
	$icon_html = us_prepare_icon_tag( $icon );
}

// Link
$link_opener = $link_closer = '';
$link_atts = us_generate_link_atts( $link, /* additional data */array( 'label' => $title ) );
if ( ! empty( $link_atts['href'] ) ) {
	$link_atts['class'] = 'w-iconbox-link';
	if ( $title != '' ) {
		$link_atts['aria-label'] = strip_tags( $title );
	} else {
		$link_atts['aria-label'] = $icon;
	}
	$link_opener = '<a' . us_implode_atts( $link_atts ) . '>';
	$link_closer = '</a>';
}

$icon_inline_css = 'font-size:' . $size . ';';

// Icon Custom Colors
$icon_color = us_get_color( $icon_color, /* Gradient */ TRUE );
$circle_color = us_get_color( $circle_color, /* Gradient */ TRUE );

// Add specific class, if gradient color is set
if ( strpos( $icon_color, 'gradient' ) !== FALSE ) {
	$_atts['class'] .= ' has_gradient';
}
if ( ! empty( $icon_color ) ) {
	$icon_inline_css .= '--icon-color:' . $icon_color . ';'; 
}
if ( ! empty( $circle_color ) ) {
	$icon_inline_css .= '--icon-bg-color:' . $circle_color . ';'; 
}

// Output the element
$output = '<div' . us_implode_atts( $_atts ) . '>';
if ( in_array( $iconpos, array( 'top', 'left' ) ) ) {
	$output .= $link_opener;
	$output .= '<div class="w-iconbox-icon" style="' . esc_attr( $icon_inline_css ) . '">' . $icon_html . '</div>';
	$output .= $link_closer;
	$output .= '<div class="w-iconbox-meta">';
} elseif ( $iconpos == 'right' ) {
	$output .= '<div class="w-iconbox-meta">';
}
if ( $title !== '' OR usb_is_post_preview() ) {
	$output .= $link_opener;
	$title_inline_css = us_prepare_inline_css(
		array(
			'font-size' => $title_size,
		)
	);
	$output .= '<' . $title_tag . ' class="w-iconbox-title"' . $title_inline_css . '>' . $title . '</' . $title_tag . '>';
	$output .= $link_closer;
}

if ( ! empty( $content ) OR usb_is_post_preview() ) {
	$output .= '<div class="w-iconbox-text">' . do_shortcode( wpautop( $content ) ) . '</div>';
}
if ( in_array( $iconpos, array( 'top', 'left' ) ) ) {
	$output .= '</div>';
} elseif ( $iconpos == 'right' ) {
	$output .= '</div>';
	$output .= $link_opener;
	$output .= '<div class="w-iconbox-icon" style="' . esc_attr( $icon_inline_css ) . '">' . $icon_html . '</div>';
	$output .= $link_closer;
}
$output .= '</div>';

echo $output;
