<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: us_gallery
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

// Get Media Category terms for selection
$media_category_terms = array();
if ( us_is_elm_editing_page() ) {
	$terms_args = array(
		'taxonomy' => 'us_media_category',
		'get' => 'all',
		'number' => 20,
	);
	if ( $terms = get_terms( $terms_args ) AND ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$media_category_terms[ $term->term_id ] = $term->name;
		}
	}
}

// Show checkboxes, if terms are 15 or less, if not - show autocomplete
$media_category_is_autocomplete = count( $media_category_terms ) > 15;
if ( $media_category_is_autocomplete ) {
	$media_category_terms = array_slice( $media_category_terms, /* offset */0, /* limit */15, /* preserve_keys */TRUE );
}

// Order options for Media
$orderby_options = array(
	'post__in' => __( 'Order of selected images', 'us' ),
	'date' => __( 'Date of upload', 'us' ),
	'modified' => __( 'Date of update', 'us' ),
	'rand' => us_translate( 'Random' ),
	'title' => us_translate( 'Title' ),
);

// General
$general_params = array(

	'ids' => array(
		'title' => us_translate( 'Images' ),
		'type' => 'upload',
		'is_multiple' => TRUE,
		'extension' => 'png,jpg,jpeg,gif,svg',
		'std' => '',
		'classes' => 'for_above',
		'dynamic_values' => TRUE,
		'usb_preview' => TRUE,
	),

	// Media Categories
	'include_us_media_category' => array(
		'title' => sprintf( __( 'Show Images by %s', 'us' ), __( 'Media Categories', 'us' ) ),
		// Show checkboxes, if terms are 15 or less, if not - show autocomplete
		// Note: checkboxes data for Visual Composer and USBuilder are displayed differently
		'type' => ( $media_category_is_autocomplete ? 'autocomplete' : 'checkboxes' ),
		'options_prepared_for_wpb' => TRUE,
		'ajax_data' => array(
			'_nonce' => wp_create_nonce( 'us_ajax_get_terms_for_autocomplete' ),
			'action' => 'us_get_terms_for_autocomplete',
			'taxonomy' => 'us_media_category', // get data for this taxonomy
			'use_term_ids' => TRUE, // use ids instead of slugs
		),
		'is_multiple' => TRUE,
		'options' => $media_category_terms,
		'std' => '',
		'place_if' => (bool) $media_category_terms,
		'usb_preview' => TRUE,
	),
	'exclude_us_media_category' => array(
		'title' => sprintf( __( 'Exclude Images by %s', 'us' ), __( 'Media Categories', 'us' ) ),
		// Show checkboxes, if terms are 15 or less, if not - show autocomplete
		// Note: checkboxes data for Visual Composer and USBuilder are displayed differently
		'type' => ( $media_category_is_autocomplete ? 'autocomplete' : 'checkboxes' ),
		'options_prepared_for_wpb' => TRUE,
		'ajax_data' => array(
			'_nonce' => wp_create_nonce( 'us_ajax_get_terms_for_autocomplete' ),
			'action' => 'us_get_terms_for_autocomplete',
			'taxonomy' => 'us_media_category', // get data for this taxonomy
			'use_term_ids' => TRUE, // use ids instead of slugs
		),
		'is_multiple' => TRUE,
		'options' => $media_category_terms,
		'std' => '',
		'place_if' => (bool) $media_category_terms,
		'usb_preview' => TRUE,
	),

	// Order
	'orderby' => array(
		'title' => __( 'Order by', 'us' ),
		'type' => 'select',
		'options' => apply_filters( 'us_gallery_orderby_options', $orderby_options ),
		'std' => 'post__in',
		'usb_preview' => TRUE,
	),
	'order_invert' => array(
		'type' => 'switch',
		'switch_text' => __( 'Invert order', 'us' ),
		'std' => 0,
		'classes' => 'for_above',
		'show_if' => array( 'orderby', '!=', array( 'post__in', 'rand' ) ),
		'usb_preview' => TRUE,
	),

	// Quantity
	'show_all' => array(
		'title' => __( 'Quantity', 'us' ),
		'type' => 'switch',
		'switch_text' => __( 'Show All Images', 'us' ),
		'std' => 0,
		'usb_preview' => TRUE,
	),
	'quantity' => array(
		'type' => 'slider',
		'options' => array(
			'' => array(
				'min' => 1,
				'max' => 30,
			),
		),
		'std' => '12',
		'show_if' => array( 'show_all', '=', 0 ),
		'classes' => 'for_above',
		'usb_preview' => TRUE,
	),

	// No results
	'no_items_action'=> array(
		'title' => __( 'Action when no images found', 'us' ),
		'type' => 'select',
		'options' => array(
			'message' => __( 'Show the message', 'us' ),
			'hide' => __( 'Hide this element', 'us' ),
		),
		'std' => 'message',
		'usb_preview' => TRUE,
	),
	'no_items_message' => array(
		'type' => 'text',
		'std' => us_translate( 'No results found.' ),
		'classes' => 'for_above',
		'show_if' => array( 'no_items_action', '=', 'message' ),
		'usb_preview' => array(
			'elm' => '.w-gallery-no-results',
			'attr' => 'html',
		),
	),

	// Pagination
	'pagination' => array(
		'title' => us_translate( 'Pagination' ),
		'type' => 'select',
		'options' => array(
			'none' => us_translate( 'None' ),
			'load_on_scroll' => __( 'Load items on page scroll', 'us' ),
			'load_on_btn' => __( 'Load items on button click', 'us' ),
		),
		'std' => 'none',
		'usb_preview' => TRUE,
		'show_if' => array( 'show_all', '=', 0 ),
	),
	'pagination_btn_text' => array(
		'title' => __( 'Button Label', 'us' ),
		'type' => 'text',
		'std' => __( 'Load More', 'us' ),
		'cols' => 2,
		'show_if' => array( 'pagination', '=', 'load_on_btn' ),
		'usb_preview' => array(
			'elm' => '.w-gallery-loadmore:first .w-btn-label',
			'attr' => 'text',
		),
	),
	'pagination_btn_style' => array(
		'title' => __( 'Button Style', 'us' ),
		'description' => $misc['desc_btn_styles'],
		'type' => 'select',
		'options' => us_get_btn_styles(),
		'std' => '1',
		'show_if' => array( 'pagination', '=', 'load_on_btn' ),
		'usb_preview' => array(
			'elm' => '.w-gallery-loadmore:first .w-btn',
			'mod' => 'us-btn-style',
		),
	),
	'pagination_btn_fullwidth' => array(
		'type' => 'switch',
		'switch_text' => __( 'Stretch to the full width', 'us' ),
		'std' => 0,
		'show_if' => array( 'pagination', '=', 'load_on_btn' ),
		'usb_preview' => array(
			'elm' => '.w-gallery-loadmore:first',
			'toggle_class' => 'width_full',
		),
	),
	'pagination_btn_indent' => array(
		'title' => __( 'Button Indent', 'us' ),
		'type' => 'slider',
		'std' => '1.5em',
		'options' => array(
			'px' => array(
				'min' => 0,
				'max' => 60,
			),
			'em' => array(
				'min' => 0.0,
				'max' => 4.0,
				'step' => 0.1,
			),
			'rem' => array(
				'min' => 0.0,
				'max' => 4.0,
				'step' => 0.1,
			),
		),
		'show_if' => array( 'pagination', '=', 'load_on_btn' ),
		'usb_preview' => array(
			'elm' => '.w-gallery-loadmore:first',
			'css' => '--btn-indent',
		),
	),
	'pagination_btn_size' => array(
		'title' => __( 'Button Size', 'us' ),
		'description' => $misc['desc_font_size'],
		'type' => 'text',
		'std' => '',
		'cols' => 2,
		'show_if' => array( 'pagination', '=', 'load_on_btn' ),
		'usb_preview' => array(
			'elm' => '.w-gallery-loadmore:first .w-btn',
			'css' => 'font-size',
		),
	),
);

// Appearance
$appearance_params = array(
	'layout' => array(
		'title' => __( 'Layout', 'us' ),
		'type' => 'imgradio',
		'preview_path' => '/admin/img/gallery/%s.png',
		'options' => array(
			'grid' => __( 'Regular Grid', 'us' ),
			'masonry' => __( 'Masonry', 'us' ),
			'metro_1' => 'METRO 1',
			'metro_2' => 'METRO 2',
			'metro_3' => 'METRO 3',
			'metro_4' => 'METRO 4',
			'mosaic_hor' => __( 'Mosaic', 'us' ),
		),
		'std' => 'grid',
		'admin_label' => TRUE,
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'columns' => array(
		'title' => us_translate( 'Columns' ),
		'type' => 'slider',
		'options' => array(
			'' => array(
				'min' => 1,
				'max' => 10,
			),
		),
		'std' => '4',
		'admin_label' => TRUE,
		'show_if' => array( 'layout', '=', array( 'grid', 'masonry' ) ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE, // TODO: use 'css' => '--columns' for non-masonry
	),
	'items_height' => array(
		'title' => __( 'Height of Images', 'us' ),
		'type' => 'slider',
		'std' => '350px',
		'options' => array(
			'px' => array(
				'min' => 200,
				'max' => 600,
				'step' => 10,
			),
		),
		'show_if' => array( 'layout', '=', 'mosaic_hor' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => array(
			'css' => '--items-height',
		),
	),
	'items_gap' => array(
		'title' => __( 'Gap between Images', 'us' ),
		'type' => 'slider',
		'std' => '10px',
		'options' => array(
			'px' => array(
				'min' => 0,
				'max' => 50,
			),
			'rem' => array(
				'min' => 0.0,
				'max' => 3.0,
				'step' => 0.1,
			),
			'vmax' => array(
				'min' => 0.0,
				'max' => 3.0,
				'step' => 0.1,
			),
			'vmin' => array(
				'min' => 0.0,
				'max' => 3.0,
				'step' => 0.1,
			),
		),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE, // TODO: use 'css' => '--items-gap' for non-masonry
	),
	'items_ratio' => array(
		'title' => __( 'Image Aspect Ratio', 'us' ),
		'type' => 'select',
		'options' => array(
			'1x1' => '1:1 ' . __( 'square', 'us' ),
			'4x3' => '4:3 ' . __( 'landscape', 'us' ),
			'3x2' => '3:2 ' . __( 'landscape', 'us' ),
			'16x9' => '16:9 ' . __( 'landscape', 'us' ),
			'3x4' => '3:4 ' . __( 'portrait', 'us' ),
			'2x3' => '2:3 ' . __( 'portrait', 'us' ),
			'custom' => __( 'Custom', 'us' ),
		),
		'std' => '1x1',
		'show_if' => array( 'layout', '!=', array( 'masonry', 'mosaic_hor', 'mosaic_ver' ) ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => array(
			'mod' => 'ratio',
		),
	),
	'items_ratio_width' => array(
		'description' => __( 'Examples:', 'us' ) . ' <span class="usof-example">21</span>, <span class="usof-example">1200</span>, <span class="usof-example">640px</span>',
		'type' => 'text',
		'std' => '21',
		'cols' => 2,
		'classes' => 'for_above',
		'show_if' => array( 'items_ratio', '=', 'custom' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => array(
			'css' => '--items-ratio-width',
		),
	),
	'items_ratio_height' => array(
		'description' => __( 'Examples:', 'us' ) . ' <span class="usof-example">9</span>, <span class="usof-example">750</span>, <span class="usof-example">380px</span>',
		'type' => 'text',
		'std' => '9',
		'cols' => 2,
		'classes' => 'for_above',
		'show_if' => array( 'items_ratio', '=', 'custom' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => array(
			'css' => '--items-ratio-height',
		),
	),
	'items_title' => array(
		'type' => 'switch',
		'switch_text' => __( 'Show Title of Images', 'us' ),
		'std' => 0,
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'items_click_action' => array(
		'title' => __( 'Action on Image click', 'us' ),
		'type' => 'select',
		'options' => array(
			'none' => us_translate( 'None' ),
			'popup_image' => __( 'Open Image in a popup', 'us' ),
			'link' => __( 'Custom Link', 'us' ),
		),
		'std' => 'none',
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'items_link' => array(
		'type' => 'link',
		'dynamic_values' => array(
			'global' => array(),
			'media' => array(
				'custom_field|us_attachment_link' => __( 'Custom Link', 'us' ),
			),
			'post' => array(),
			'user' => array(),
		),
		'std' => '{"url":""}',
		'classes' => 'for_above',
		'show_if' => array( 'items_click_action', '=', 'link' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'img_size' => array(
		'title' => __( 'Image Size', 'us' ),
		'description' => $misc['desc_img_sizes'],
		'type' => 'select',
		'options' => us_get_image_sizes_list(),
		'std' => 'large',
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
);

return array(
	'title' => us_translate( 'Gallery' ),
	'category' => __( 'Grid', 'us' ),
	'icon' => 'fas fa-th-large',
	'params' => us_set_params_weight(
		$general_params,
		$appearance_params,
		$conditional_params,
		$design_options_params
	),
	'fallback_params' => array(
		'source',
	),
	'usb_init_js' => '$elm.filter( \'.type_masonry\' ).wGallery();',
);
