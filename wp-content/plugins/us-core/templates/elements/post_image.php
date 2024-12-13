<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Post Image element
 *
 * @var $thumbnail_size string Image WordPress size
 * @var $popup_thumbnail_size string Image WordPress size
 * @var $placeholder bool Use placeholder if post has no thumbnail?
 * @var $media_preview bool Show media preview for video and gallery posts?
 * @var $link string Link type: 'post' / 'custom' / 'none'
 * @var $custom_link array
 * @var $design_options array
 *
 * @var $classes string
 * @var $id string
 *
 * @var $has_ratio bool is use aspect ratio: '1'/'0'
 * @var $ratio string ratio value: '1x1'/'custom'
 * @var $ratio_width string width value: '1'
 * @var $ratio_height string height value: '1'
 */

if ( is_admin() AND ! wp_doing_ajax() ) {
	return;
}

global $_wp_additional_image_sizes, $us_post_img_ratio, $us_post_slider_size;

$_atts['class'] = 'w-post-elm post_image';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['class'] .= $circle ? ' as_circle' : '';

// When some values are set in Design options, add the specific classes
if ( us_design_options_has_property( $css, 'width' ) ) {
	$_atts['class'] .= ' has_width';
}
if ( us_design_options_has_property( $css, array( 'height', 'max-height' ) ) ) {
	$_atts['class'] .= ' has_height';
}

// Set Aspect Ratio values
$ratio_helper = '';
if ( $has_ratio ) {
	$ratio_array = us_get_aspect_ratio_values( $ratio, $ratio_width, $ratio_height );
	$ratio_helper = '<div style="padding-bottom:' . round( $ratio_array[1] / $ratio_array[0] * 100, 4 ) . '%"></div>';
	$_atts['class'] .= ' has_ratio';
} elseif ( $stretch ) {
	$_atts['class'] .= ' stretched';
}

if ( ! empty( $el_id ) AND $us_elm_context == 'shortcode' ) {
	$_atts['id'] = $el_id;
}

// Overwrite image size from Grid/List elements, if set
global $us_grid_img_size;
if ( $us_grid_img_size ) {
	$thumbnail_size = $us_grid_img_size;
}

// Calculate aspect ratio for media preview and for placeholder
if (
	isset( $_wp_additional_image_sizes[ $thumbnail_size ] )
	AND $_wp_additional_image_sizes[ $thumbnail_size ]['width'] != 0
	AND $_wp_additional_image_sizes[ $thumbnail_size ]['height'] != 0
) {
	$us_post_img_ratio = round( $_wp_additional_image_sizes[ $thumbnail_size ]['height'] / $_wp_additional_image_sizes[ $thumbnail_size ]['width'] * 100, 4 );
}

global $us_grid_item_type;

if ( $us_elm_context == 'grid' AND $us_grid_item_type == 'term' ) {
	global $us_grid_term;
} elseif ( $us_elm_context == 'shortcode' AND ( is_tax() OR is_tag() OR is_category() ) ) {
	$us_grid_term = get_queried_object();
} else {
	$us_grid_term = NULL;
}

// Disable lazy loading if set
$img_loading_attr = array();
$wc_placeholder_img_atts = array(
	'loading' => 'lazy',
	'alt' => '',
);
if ( $disable_lazy_loading ) {
	$img_loading_attr['loading'] = FALSE;
	unset( $wc_placeholder_img_atts['loading'] );
}

// Get the ID of the current object including Grid items context
$_current_ID = us_get_current_id();

// Link
$link_atts = us_generate_link_atts( $link, /* additional data */array( 'img_id' => $_current_ID ) );

$_post_preview = '';

// In Live Builder for Reusable Block / Page Template show a placeholder for shortcode
if ( usb_is_template_preview() AND $us_elm_context == 'shortcode' ) {
	$_post_preview = us_get_img_placeholder( $thumbnail_size );
}

// Get image of taxonomy term (works for WooCommerce Product categories, brands, tags etc.)
if ( isset( $us_grid_term->taxonomy ) ) {
	if ( $term_thumbnail_id = get_term_meta( $us_grid_term->term_id, 'thumbnail_id', TRUE ) ) {
		$_post_preview = wp_get_attachment_image( $term_thumbnail_id, $thumbnail_size, FALSE, $img_loading_attr );

	} elseif ( $placeholder ) {
		$_atts['class'] .= ' with_placeholder';

		// Use WooCommerce placeholder if enabled
		if ( function_exists( 'wc_placeholder_img_src' ) ) {
			$wc_placeholder_img_atts['src'] = wc_placeholder_img_src( $thumbnail_size );
			$_post_preview = '<img' . us_implode_atts( $wc_placeholder_img_atts ) . '>';
		} else {
			$_post_preview = us_get_img_placeholder( $thumbnail_size );
		}
	} else {
		return;
	}
}

// Generate media preview
if ( $_post_preview == '' AND $media_preview AND ! post_password_required() AND ! $us_grid_term ) {

	// Generate specific preview for posts with Video, Audio, Gallery formats
	if ( in_array( get_post_format(), array( 'video', 'audio', 'gallery' ) ) ) {
		$us_post_slider_size = $thumbnail_size;
		$the_content = get_the_content();

		// Pass custom padding to keep proper preview aspect ratio in Video element
		if ( get_post_format() == 'video' AND isset( $ratio_array ) ) {
			$us_post_img_ratio = round( $ratio_array[1] / $ratio_array[0] * 100, 4 );
			$ratio_helper = '';
		}

		$_post_preview = us_get_post_preview( $the_content );

		if ( $_post_preview != '' ) {
			$_atts['class'] .= ' media_preview'; // add CSS class for media preview
			$link_atts = array(); // remove link for media preview
		}

		// Generate simple hover gallery for other cases
	} else {

		// For products get its WooCommerce gallery images
		if ( get_post_type() == 'product' ) {
			$images = get_post_meta( $_current_ID, '_product_image_gallery', TRUE );

			// For other post types get "Additional Settings" images
		} else {
			$images = get_post_meta( $_current_ID, 'us_tile_additional_image', TRUE );
		}

		if ( $images ) {

			// If post has a Featured image, append its ID
			if ( get_post_thumbnail_id() ) {
				$images = get_post_thumbnail_id() . ',' . $images;
			}

			$img_ids = explode( ',', $images );

			// Remove empty ids to avoid duplications in output
			$img_ids = array_diff( $img_ids, array( '' ) );

			// Use specified images amount only
			$img_ids = array_slice( $img_ids, 0, $gallery_images_amount );

			foreach ( $img_ids as $key => $img_id ) {
				$_img_width = round( 100 / count( $img_ids ), 2 );

				$_post_preview .= '<div class="w-post-slider-trigger" style="width:' . $_img_width . '%; left:' . $key * $_img_width . '%;"></div>';

				// Display the placeholder if set and the image is absent
				if ( $_img_html = wp_get_attachment_image( $img_id, $thumbnail_size ) ) {
					$_post_preview .= $_img_html;
				} elseif ( $placeholder ) {
					$_post_preview .= us_get_img_placeholder( $thumbnail_size );
				}
			}
		}
	}
}


// Output image of attachment post type
if ( $_post_preview == '' AND get_post_type() == 'attachment' ) {
	$_post_preview = wp_get_attachment_image( $_current_ID, $thumbnail_size, FALSE, $img_loading_attr );
}

// Output Featured image if the current post has it
if ( $_post_preview == '' AND ! $us_grid_term ) {
	$_post_preview = get_the_post_thumbnail( $_current_ID, $thumbnail_size, $img_loading_attr );
}

// Output the first image from the content of Gallery format
if ( $_post_preview == '' AND get_post_format() == 'gallery' ) {
	$the_content = get_the_content();
	if ( preg_match( '~\[us_image_slider.+?\]|\[gallery.+?\]~', $the_content, $matches ) ) {
		$gallery = preg_replace( '~(vc_gallery|gallery)~', 'us_image_slider', $matches[0] );
		preg_match( '~\[us_image_slider(.+?)\]~', $gallery, $matches2 );
		$shortcode_atts = shortcode_parse_atts( $matches2[1] );
		if ( ! empty( $shortcode_atts['ids'] ) ) {
			$ids = explode( ',', $shortcode_atts['ids'] );
			if ( count( $ids ) > 0 ) {
				$_post_preview = wp_get_attachment_image( $ids[0], $thumbnail_size, FALSE, $img_loading_attr );
			}
		}
	}
}

// Output placeholder if enabled
if ( $_post_preview == '' AND $placeholder ) {
	$_atts['class'] .= ' with_placeholder';

	// Use WooCommerce placeholder if enabled
	if ( get_post_type() == 'product' AND function_exists( 'wc_placeholder_img_src' ) ) {
		$wc_placeholder_img_atts['src'] = wc_placeholder_img_src( $thumbnail_size );
		$_post_preview = '<img' . us_implode_atts( $wc_placeholder_img_atts ) . '>';
	} else {
		$_post_preview = us_get_img_placeholder( $thumbnail_size );
	}
}

// Don't output the element without any content unless it's US Builder page and shortcode context
if (
	$_post_preview == ''
	AND ! ( usb_is_post_preview() AND $us_elm_context == 'shortcode' )
) {
	return;
}

$output = '<div' . us_implode_atts( $_atts ) . '>';
$output .= $ratio_helper;
if ( ! empty( $link_atts['href'] ) ) {
	$link_atts['aria-label'] = strip_tags( get_the_title() );
	$output .= '<a' . us_implode_atts( $link_atts ) . '>';
}

$output .= $_post_preview;

if ( ! empty( $link_atts['href'] ) ) {
	$output .= '</a>';
}
$output .= '</div>';

echo apply_filters( 'us_post_image', $output, $_post_preview, $link_atts );
