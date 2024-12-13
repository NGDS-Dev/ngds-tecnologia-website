<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Post Comments
 */

if ( is_admin() AND ! wp_doing_ajax() ) {
	return;
}

global $us_grid_item_type;

// Cases when the Comments shouldn't be shown
if ( $us_elm_context == 'grid' AND $us_grid_item_type == 'term' ) {
	return;
} elseif ( $us_elm_context == 'shortcode' AND is_archive() ) {
	return;
} elseif ( get_post_format() == 'link' ) {
	return;
} elseif ( ! comments_open() AND ! get_comments_number() AND ! usb_is_post_preview() ) {
	return;
}

// Exclude 'comments_template' layout for Grid context
if ( $us_elm_context != 'shortcode' ) {
	$layout = 'amount';
}

$_atts['class'] = 'w-post-elm post_comments';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['class'] .= ' layout_' . $layout;

if ( ! empty( $el_id ) AND $us_elm_context == 'shortcode' ) {
	$_atts['id'] = $el_id;
}

if ( $layout == 'amount' ) {

	// Get link attributes
	$link_atts = us_generate_link_atts( $link );
	$link_atts['class'] = 'smooth-scroll';

	if ( $color_link ) {
		$_atts['class'] .= ' color_link_inherit';
	}

	// Define no comments indication
	$comments_none = '0';
	if ( ! $number ) {
		$_atts['class'] .= ' with_word';
		$comments_none = us_translate( 'No Comments' );
	}

	$comments_number = get_comments_number();

	// "Hide this element if no comments"
	if ( $hide_zero AND empty( $comments_number ) ) {

		// Output empty container for Live Builder
		if ( usb_is_post_preview() ) {
			echo '<div class="w-post-elm"></div>';
		}

		return;
	}
}

// Output the element
$output = '<div' . us_implode_atts( $_atts ) . '>';

if ( $layout == 'comments_template' ) {

	// Output comments template if it's not Live preview for templates
	if ( ! usb_is_template_preview() ) {
		if ( ! us_amp() ) {
			wp_enqueue_script( 'comment-reply' );
		}

		ob_start();
		comments_template();
		$output .= ob_get_clean();

		if ( ! comments_open() AND get_comments_number() ) {
			$output .= '<p class="no-comments">' . us_translate( 'Comments are closed.' ) . '</p>';
		}
	}

} else {
	if ( ! empty( $icon ) ) {
		$output .= us_prepare_icon_tag( $icon );
	}
	if ( ! empty( $link_atts['href'] ) ) {
		$output .= '<a' . us_implode_atts( $link_atts ) . '>';
	}

	if ( class_exists( 'woocommerce' ) AND get_post_type() == 'product' ) {

		// "screen-reader-text" is needed for working "Show only number" option
		$output .= sprintf( us_translate_n( '%s customer review', '%s customer reviews', $comments_number, 'woocommerce' ), '<span class="count">' . strip_tags( $comments_number ) . '</span><span class="screen-reader-text">' );
		$output .= '</span>';
	} else {
		ob_start();
		$comments_label = sprintf( us_translate_n( '%s <span class="screen-reader-text">Comment</span>', '%s <span class="screen-reader-text">Comments</span>', $comments_number ), $comments_number );
		comments_number( $comments_none, $comments_label, $comments_label );
		$output .= ob_get_clean();
	}

	if ( ! empty( $link_atts['href'] ) ) {
		$output .= '</a>';
	}
}

$output .= '</div>';

echo $output;
