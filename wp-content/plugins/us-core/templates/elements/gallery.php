<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Template is used for both [us_gallery] and [gallery] shortcodes
 */

$_atts['class'] = 'w-gallery';
$_atts['class'] .= isset( $classes ) ? $classes : '';

// Override attributes from WordPress [gallery] shortcode
if ( $shortcode_base == 'gallery' ) {
	$_atts['class'] .= ' wp_gallery';

	// Force showing all images
	$show_all = 1;

	// Columns
	if ( empty( $atts['columns'] ) ) {
		$columns = 3;
	}

	// Force items ratio to auto for 1 column
	if ( $columns == 1 ) {
		$items_ratio = 'auto';
	}

	// Orderby
	if ( empty( $atts['orderby'] ) ) {
		$orderby = 'post__in';
	}

	// Size
	if ( empty( $atts['size'] ) ) {
		$img_size = 'thumbnail';
	} else {
		$img_size = $atts['size'];
	}

	// Link
	if (
		isset( $atts['link'] )
		AND $atts['link'] == 'none'
		OR isset( $atts['link_type'] ) // used in WordPress sidebar widget
		AND $atts['link_type'] == 'none'
	) {
		$items_click_action = 'none';
	} else {
		$items_click_action = 'popup_image';
	}

	// Masonry
	if ( isset( $atts['masonry'] ) AND $atts['masonry'] == 'true' ) {
		$layout = 'masonry';
	}

	// Meta
	if ( isset( $atts['meta'] ) AND $atts['meta'] == 'true' ) {
		$items_title = TRUE;
	}

	// Indents
	if ( isset( $atts['indents'] ) AND $atts['indents'] == 'true' ) {
		$items_gap = '8px';
	} else {
		$items_gap = '0px';
	}
}

$_atts['class'] .= ' type_' . $layout;

if ( $items_click_action != 'none' ) {
	$_atts['class'] .= ' action_' . $items_click_action;
}

if ( $el_id ) {
	$_atts['id'] = $el_id;
}

// Force "auto" aspect ratio for Masonry layout
if ( $layout == 'masonry' ) {
	$items_ratio = 'auto';
}

$_atts['class'] .= ' ratio_' . $items_ratio;
$_atts['style'] = '--columns:' . $columns . ';';
$_atts['style'] .= '--items-gap:' . $items_gap . ';';

// CSS height var for specific layout
if ( $layout == 'mosaic_hor' ) {
	$_atts['style'] .= '--items-height:' . $items_height . ';';
}

// CSS vars for custom aspect ratio
if ( $items_ratio == 'custom' ) {
	$_atts['style'] .= '--items-ratio-width:' . (int) $items_ratio_width . ';';
	$_atts['style'] .= '--items-ratio-height:' . (int) $items_ratio_height . ';';
}

// Generate query to get images
$query_args = array(
	'post_type' => 'attachment',
	'post_mime_type' => 'image',
	'post_status' => 'inherit',
	'orderby' => $orderby,
	'order' => $order_invert ? 'ASC' : 'DESC',
	'numberposts' => ( $show_all OR $pagination != 'none' ) ? 999 : $quantity,
);

$query_args['include'] = us_replace_dynamic_value( $ids, /* acf_format */ FALSE );

// Add tax query for Media Categories
$tax_query = array();
if ( $include_us_media_category ) {
	$tax_query[] = array(
		'taxonomy' => 'us_media_category',
		'terms' => explode( ',', $include_us_media_category ),
	);
}
if ( $exclude_us_media_category ) {
	$tax_query[] = array(
		'taxonomy' => 'us_media_category',
		'terms' => explode( ',', $exclude_us_media_category ),
		'operator' => 'NOT IN',
	);
}
$query_args['tax_query'] = $tax_query;

// Apply filter for developer purposes
$query_args = apply_filters( 'us_gallery_query_args', $query_args, $el_id );

// Get images by query
$img_posts = get_posts( $query_args );

// Collect all image ids for ajax pagination
if ( ! $show_all AND $pagination !== 'none' ) {
	$all_image_ids = array();
	if ( ! empty( $img_posts ) AND ! wp_doing_ajax() ) {
		foreach ( $img_posts as $img_post ) {
			$all_image_ids[] = $img_post->ID;
		}
	}
}

// Count all posts by query
$count_img_posts = count( $img_posts );

// Add class with amount of images
$_atts['class'] .= ' count_' . $count_img_posts;

// Narrow the result amount, because 'include' param ignores the 'numberposts' param in get_posts()
if ( ! $show_all ) {
	$img_posts = array_slice( $img_posts, 0, $quantity );
}

// Don't show the element whithout images, if set
if (
	empty( $img_posts )
	AND $no_items_action == 'hide'
	AND ! usb_is_preview()
) {
	return;
}

// Output the element
$output = '<div' . us_implode_atts( $_atts ) . '>';

if ( ! empty( $img_posts ) ) {
	$output .= '<div class="w-gallery-list">';

	foreach ( $img_posts as $img_post ) {

		// Use the Caption as image title
		$title = $img_post->post_excerpt;

		// If no Caption, use the "Alt"
		if ( empty( $title ) ) {
			$title = get_post_meta( $img_post->ID, '_wp_attachment_image_alt', TRUE );
		}

		// If no "Alt", use the Title
		if ( empty( $title ) ) {
			$title = $img_post->post_title;
		}

		// Output list item
		$output .= '<div class="w-gallery-item">';
		$output .= '<div class="w-gallery-item-img">';
		$output .= wp_get_attachment_image( $img_post->ID, $img_size );
		$output .= '</div>';

		// Output image title if set
		if ( $items_title ) {
			$output .= '<div class="w-gallery-item-meta">';
			$output .= '<div class="w-gallery-item-title">' . $title . '</div>';

			// Output image description for old gallery shortcode only
			if ( $shortcode_base == 'gallery' AND ! empty( $img_post->post_content ) ) {
				$output .= '<div class="w-gallery-item-description">' . $img_post->post_content . '</div>';
			}
			$output .= '</div>';
		}

		// Generate item link
		if ( $items_click_action != 'none' ) {

			$_link_atts = array(
				'class' => 'w-gallery-item-link',
				'aria-label' => $title,
			);

			// Open original image in a popup
			if ( $items_click_action == 'popup_image' ) {
				$_link_atts['href'] = wp_get_attachment_url( $img_post->ID );
//				$_link_atts['ref'] = 'magnificPopup';

				// Add Title attribute, that used in the popup showing Title
				if ( $items_title ) {
					$_link_atts['title'] = $title;
				}

				// Open custom link
			} elseif ( $items_click_action == 'link' ) {
				$_link_atts += us_generate_link_atts( $items_link, array(), $img_post->ID );
			}

			if ( ! empty( $_link_atts['href'] ) ) {
				$output .= '<a' . us_implode_atts( $_link_atts ) . '></a>';
			}
		}

		$output .= '</div>'; // .w-gallery-item
	}
	$output .= '</div>'; // .w-gallery-list

	// Additional HTML when Gallery has ajax pagination
	if ( ! $show_all AND $pagination !== 'none' ) {
		if ( $count_img_posts > count( $img_posts ) ) {

			// Global preloader type
			$preloader_type = us_get_option( 'preloader' );
			if ( ! in_array( $preloader_type, array_merge( us_get_preloader_numeric_types(), array( 'custom' ) ) ) ) {
				$preloader_type = 1;
			}
			$output .= '<div class="w-gallery-loadmore' . ( $pagination_btn_fullwidth ? ' width_full' : '' ) . '" style="--btn-indent:' . $pagination_btn_indent . '">';

			// "Load More" button
			if ( $pagination == 'load_on_btn' ) {
				$_button_atts = array(
					'class' => 'w-btn ' . us_get_btn_class( $pagination_btn_style ),
				);
				if ( $pagination_btn_size ) {
					$_button_atts['style'] = 'font-size: ' . $pagination_btn_size;
				}

				$output .= '<button ' . us_implode_atts( $_button_atts ) . '>';
				$output .= '<span>' . esc_html( $pagination_btn_text ) . '</span>';
				$output .= '</button>';
			}

			$output .= '<div class="g-preloader type_' . ( $preloader_type == 'custom' ? '1' : $preloader_type ) . '"><div></div></div>';
			$output .= '</div>';// .w-gallery-loadmore
		}

		$json_data = array(
			'action' => 'us_ajax_gallery',
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'template_vars' => array(
				'ids' => $all_image_ids,
				'img_size' => $img_size,
				'items_click_action' => $items_click_action,
				'items_link' => $items_link,
				'items_title' => $items_title,
				'max_num_pages' => ceil( $count_img_posts / (int) $quantity ),
				'quantity' => $quantity,
				'pagination' => $pagination,
			),
		);
		$output .= '<div class="w-gallery-json hidden" ' . us_pass_data_to_js( $json_data ) . '></div>';
	}

	// Output the "No results" message if set
} elseif ( $no_items_action == 'message' ) {
	$output .= '<div class="w-gallery-no-results">' . strip_tags( $no_items_message ) . '</div>';
}

$output .= '</div>'; // .w-gallery

echo $output;
