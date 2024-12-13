<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: grid
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

// Get the available post types for selection
$available_posts_types = us_grid_available_post_types( TRUE );

// Fetching the available taxonomies for selection
$taxonomies_params = $filter_taxonomies_params = $available_taxonomies = array();

$known_post_type_taxonomies = us_grid_available_taxonomies();

foreach ( $known_post_type_taxonomies as $post_type => $taxonomy_slugs ) {
	if ( isset( $available_posts_types[ $post_type ] ) ) {
		$filter_values = array();
		foreach ( $taxonomy_slugs as $taxonomy_slug ) {
			$taxonomy_class = get_taxonomy( $taxonomy_slug );
			if ( ! empty( $taxonomy_class ) AND ! empty( $taxonomy_class->labels ) AND ! empty( $taxonomy_class->labels->name ) ) {
				if ( isset ( $available_taxonomies[ $taxonomy_slug ] ) ) {
					$available_taxonomies[ $taxonomy_slug ]['post_type'][] = $post_type;
				} else {
					$available_taxonomies[ $taxonomy_slug ] = array(
						'name' => $taxonomy_class->labels->name,
						'post_type' => array( $post_type ),
					);
				}

				$filter_value_label = $taxonomy_class->labels->name;
				$filter_values[ $taxonomy_slug ] = $filter_value_label;
			}
		}

		if ( count( $filter_values ) > 0 ) {
			$filter_taxonomies_params[ 'filter_' . $post_type ] = array(
				'title' => __( 'Filter by', 'us' ),
				'type' => 'select',
				'options' => array_merge(
					array( '' => '– ' . us_translate( 'None' ) . ' –' ), $filter_values
				),
				'std' => '',
				'show_if' => array( 'post_type', '=', $post_type ),
				'exclude_for_carousel' => TRUE,
				'group' => us_translate( 'Filter' ),
				'usb_preview' => TRUE,
			);
		}
	}
}

foreach ( $available_taxonomies as $taxonomy_slug => $taxonomy ) {
	$taxonomy_items = array();

	// Receive data for taxonomies only on the edit page or create a record
	if ( us_is_elm_editing_page() ) {
		$terms_args = array(
			'taxonomy' => $taxonomy_slug,
			'get' => 'all',
			'number' => 20,
		);
		if ( $terms = get_terms( $terms_args ) AND ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$taxonomy_items[ $term->slug ] = $term->name;
			}
		}
	}

	if ( count( $taxonomy_items ) > 0 ) {

		// Do not output the only "Uncategorized" of Posts and Products
		if ( in_array( $taxonomy_slug, array( 'category', 'product_cat' ) ) AND count( $taxonomy_items ) == 1 ) {
			continue;
		}

		foreach ( $taxonomy['post_type'] as $taxonomy_post_type ) {
			$taxonomies_params[ 'taxonomy_' . str_replace( '-', '_', $taxonomy_slug ) ] = array(
				'title' => sprintf( __( 'Show Items of selected %s', 'us' ), $taxonomy['name'] ),
				// Show checkboxes, if terms are 15 or less, if not - show autocomplete
				// Note: checkboxes data for Visual Composer and USBuilder are displayed differently
				'type' => ( count( $taxonomy_items ) > 15 ? 'autocomplete' : 'checkboxes' ),
				'options_prepared_for_wpb' => TRUE,
				'ajax_data' => array(
					'_nonce' => wp_create_nonce( 'us_ajax_get_terms_for_autocomplete' ),
					'action' => 'us_get_terms_for_autocomplete',
					'taxonomy' => $taxonomy_slug,
				),
				'is_multiple' => TRUE,
				'options' => $taxonomy_items,
				'show_if' => array( 'post_type', '=', $taxonomy['post_type'] ),
				'usb_preview' => TRUE,
			);
		}
	}
}

// Additional values for WooCommerce products
if ( class_exists( 'woocommerce' ) ) {
	$products_show_values = array(
		'product_gallery' => us_translate( 'Product gallery', 'woocommerce' ),
		'product_upsells' => us_translate( 'Upsells', 'woocommerce' ),
		'product_crosssell' => us_translate( 'Cross-sells', 'woocommerce' ),
	);
	$products_exclude_values = array(
		'out_of_stock' => us_translate( 'Out of stock', 'woocommerce' ),
	);
} else {
	$products_exclude_values = $products_show_values = array();
}

// Get "Gallery" and "Post Object" options from ACF PRO plugin
$acf_show_values = array();
if (
	function_exists( 'us_acf_get_fields' )
	AND us_is_elm_editing_page()
) {
	foreach( (array) us_acf_get_fields() as $group_id => $fields ) {
		if ( ! is_array( $fields ) ) {
			continue;
		}

		// Get label for current group
		if ( $group_label = us_arr_path( $fields, '__group_label__' ) ) {
			unset( $fields['__group_label__'] );
		}

		foreach( $fields as $field ) {
			$field_type = us_arr_path( $field, 'type', '' );
			if ( ! in_array( $field_type, array( 'gallery', 'post_object' ) ) ) {
				continue;
			}
			if ( $field_type === 'gallery' ) {
				$field['name'] = 'acf_gallery_' . $field['name'];
			}
			elseif ( $field_type === 'post_object' ) {
				$field['name'] = 'acf_posts_' . $field['name'];
			}
			$acf_show_values[ $group_id ][ $field['name'] ] = $field['label'];
		}

		// Add a group label to the overall result
		if ( $group_label AND ! empty( $acf_show_values[ $group_id ] ) ) {
			$acf_show_values[ $group_id ]['__group_label__'] = $group_label;
		}
	}
}

$grid_config = array(
	'title' => __( 'Grid', 'us' ) . ' / ' . __( 'Listing', 'us' ),
	'category' => __( 'Grid', 'us' ),
	'description' => __( 'List of images, posts, pages or any custom post types', 'us' ),
	'icon' => 'fas fa-th-large',
	'params' => array(),
);

// Get Reusable Blocks
$us_page_blocks_list = array();
if ( us_is_elm_editing_page() ) {
	$us_page_blocks_list = us_get_posts_titles_for( 'us_page_block' );
}

// Visual Composer sends 'post_id' POST variable to the element popup. We will use it to remove current Reusable Block from list
if (
	$post_id = (int) us_arr_path( $_POST, 'post_id', 0 )
	AND isset( $us_page_blocks_list[ $post_id ] )
) {
	unset( $us_page_blocks_list[ $post_id ] );
}

// General
$general_params = array_merge(
	array(

		'post_type' => array(
			'title' => us_translate( 'Show' ),
			'type' => 'select',
			'options' => array_merge(
				$available_posts_types,
				$acf_show_values,
				array(
					__( 'Additional Settings', 'us' ) => array(
						'cf|us_tile_additional_image' => us_translate( 'Images' ),
					),
					__( 'More Options', 'us' ) => array(
						'related' => __( 'Items with the same taxonomy of current post', 'us' ),
						'current_query' => __( 'Items of the current query (used for archives and search results)', 'us' ),
						'current_child_pages' => __( 'Сhild pages of current page', 'us' ),
						'ids' => __( 'Manually selected items', 'us' ),
					),
					__( 'Taxonomy Terms', 'us' ) => array(
						'taxonomy_terms' => __( 'Terms of selected taxonomy', 'us' ),
						'current_child_terms' => __( 'Child terms of the current term', 'us' ),
						'ids_terms' => __( 'Selected terms', 'us' ),
					),
					us_translate( 'WooCommerce', 'woocommerce' ) => $products_show_values,
				)
			),
			'std' => 'post',
			'admin_label' => TRUE,
			'usb_preview' => TRUE,
		),
		'related_taxonomy' => array(
			'type' => 'select',
			'options' => us_get_taxonomies(),
			'std' => 'category',
			'classes' => 'for_above',
			'show_if' => array( 'post_type', '=', array( 'related', 'taxonomy_terms' ) ),
			'usb_preview' => TRUE,
		),
		'related_post_type' => array(
			'title' => us_translate( 'Post Type' ),
			'type' => 'checkboxes',
			'options' => $available_posts_types,
			'std' => '',
			'show_if' => array( 'post_type', '=', array( 'related' ) ),
			'usb_preview' => TRUE,
		),
		'ids' => array(
			'type' => 'autocomplete',
			'options_prepared_for_wpb' => TRUE,
			'ajax_data' => array(
				'_nonce' => wp_create_nonce( 'us_ajax_get_post_ids_for_autocomplete' ),
				'action' => 'us_get_post_ids_for_autocomplete',
			),
			'options' => us_get_post_ids_for_autocomplete(),
			'is_multiple' => TRUE,
			'is_sortable' => TRUE,
			'classes' => 'for_above',
			'show_if' => array( 'post_type', '=', 'ids' ),
			'usb_preview' => TRUE,
		),
		'ids_terms' => array(
			'type' => 'autocomplete',
			'options_prepared_for_wpb' => TRUE,
			'ajax_data' => array(
				'_nonce' => wp_create_nonce( 'us_ajax_get_terms_for_autocomplete' ),
				'action' => 'us_get_terms_for_autocomplete',
				'use_term_ids' => TRUE, // use ids instead of slugs
			),
			'options' => us_get_terms_for_autocomplete( /* taxonomy */'', /* use_term_ids */TRUE ),
			'is_multiple' => TRUE,
			'is_sortable' => TRUE,
			'classes' => 'for_above',
			'show_if' => array( 'post_type', '=', 'ids_terms' ),
			'usb_preview' => TRUE,
		),
		'images' => array(
			'title' => us_translate( 'Images' ),
			'type' => 'upload',
			'is_multiple' => TRUE,
			'extension' => 'png,jpg,jpeg,gif,svg',
			'show_if' => array( 'post_type', '=', 'attachment' ),
			'usb_preview' => TRUE,
		),
		'ignore_sticky' => array(
			'type' => 'switch',
			'switch_text' => __( 'Ignore sticky posts', 'us' ),
			'std' => 0,
			'classes' => 'for_above',
			'show_if' => array( 'post_type', '=', 'post' ),
			'usb_preview' => TRUE,
		),
		'include_post_thumbnail' => array(
			'type' => 'switch',
			'switch_text' => __( 'Include Featured image', 'us' ),
			'std' => 1,
			'classes' => 'for_above',
			'show_if' => array( 'post_type', '=', array( 'cf|us_tile_additional_image', 'product_gallery' ) ),
			'usb_preview' => TRUE,
		),
		'products_include' => array(
			'type' => 'checkboxes',
			'options' => array(
				'sale' => us_translate( 'On-sale products', 'woocommerce' ),
				'featured' => us_translate( 'Featured products', 'woocommerce' ),
			),
			'std' => '',
			'classes' => 'for_above',
			'show_if' => array( 'post_type', '=', 'product' ),
			'usb_preview' => TRUE,
		),
		'terms_include' => array(
			'type' => 'checkboxes',
			'options' => array(
				'children' => __( 'Show child terms', 'us' ),
				'empty' => __( 'Show empty', 'us' ),
			),
			'std' => '',
			'classes' => 'for_above',
			'show_if' => array( 'post_type', '=', array( 'taxonomy_terms', 'current_child_terms' ) ),
			'usb_preview' => TRUE,
		),
		'events_calendar_show_past' => array(
			'type' => 'switch',
			'switch_text' => __( 'Show past events', 'us' ),
			'std' => 0,
			'classes' => 'for_above',
			'show_if' => array( 'post_type', '=', array( 'tribe_events' ) ),
			'usb_preview' => TRUE,
		),
	), $taxonomies_params, array(
		'orderby' => array(
			'title' => us_translate( 'Order' ),
			'type' => 'select',
			'options' => us_grid_get_orderby_options(),
			'std' => 'date',
			'show_if' => array( 'post_type', '!=', array( 'current_query', 'taxonomy_terms', 'current_child_terms', 'ids_terms' ) ),
			'usb_preview' => TRUE,
		),
		'orderby_custom_field' => array(
			'description' => __( 'Enter custom field name to order items by its value', 'us' ),
			'type' => 'text',
			'std' => '',
			'classes' => 'for_above',
			'show_if' => array( 'orderby', '=', 'custom' ),
			'usb_preview' => TRUE,
		),
		'orderby_custom_type' => array(
			'type' => 'switch',
			'switch_text' => __( 'Order by numeric values', 'us' ),
			'std' => 0,
			'classes' => 'for_above',
			'show_if' => array( 'orderby', '=', 'custom' ),
			'usb_preview' => TRUE,
		),
		'order_invert' => array(
			'type' => 'switch',
			'switch_text' => __( 'Invert order', 'us' ),
			'std' => 0,
			'classes' => 'for_above',
			'show_if' => array( 'orderby', '!=', 'rand' ),
			'usb_preview' => TRUE,
		),
		'terms_orderby' => array(
			'title' => us_translate( 'Order' ),
			'type' => 'select',
			'options' => array(
				'name' => __( 'By title', 'us' ),
				'rand' => us_translate( 'Random' ),
				'count' => __( 'Items Quantity', 'us' ),
				'menu_order' => __( 'Manually, if available', 'us' ),
			),
			'std' => 'name',
			'cols' => 2,
			'show_if' => array( 'post_type', '=', array( 'taxonomy_terms', 'current_child_terms', 'ids_terms' ) ),
			'usb_preview' => TRUE,
		),
		'items_quantity' => array(
			'title' => __( 'Items Quantity', 'us' ),
			'type' => 'slider',
			'options' => array(
				'' => array(
					'min' => 1,
					'max' => 50,
				),
			),
			'std' => '10',
			'cols' => 2,
			'show_if' => array( 'post_type', '!=', array( 'current_query' ) ),
			'usb_preview' => TRUE,
		),
		'exclude_items' => array(
			'title' => __( 'Exclude Items', 'us' ),
			'type' => 'select',
			'options' => array_merge(
				array(
					'none' => us_translate( 'None' ),
					'prev' => __( 'of previous Grids on this page', 'us' ),
					'offset' => __( 'by the given quantity from the beginning of output', 'us' ),
				), $products_exclude_values
			),
			'std' => 'none',
			'cols' => 2,
			'show_if' => array( 'post_type', '!=', array( 'current_query', 'taxonomy_terms', 'current_child_terms', 'ids_terms' ) ),
			'usb_preview' => TRUE,
		),
		'items_offset' => array(
			'title' => __( 'Items Quantity to skip', 'us' ),
			'type' => 'text',
			'std' => '1',
			'show_if' => array( 'exclude_items', '=', 'offset' ),
			'usb_preview' => TRUE,
		),
		'no_items_action'=> array(
			'title' => __( 'Action when no results found', 'us' ),
			'type' => 'select',
			'options' => array(
				'message' => __( 'Show the message', 'us' ),
				'page_block' => __( 'Show the Reusable Block', 'us' ),
				'hide_grid' => __( 'Hide this Grid', 'us' ),
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
				'elm' => '.w-grid-none',
				'attr' => 'html',
			),
		),
		'no_items_page_block' => array(
			'options' => $us_page_blocks_list,
			'type' => 'select',
			'hints_for' => 'us_page_block',
			'std' => '',
			'classes' => 'for_above',
			'show_if' => array( 'no_items_action', '=', 'page_block' ),
		),
		'pagination' => array(
			'title' => us_translate( 'Pagination' ),
			'type' => 'select',
			'options' => array(
				'none' => us_translate( 'None' ),
				'regular' => __( 'Numbered pagination', 'us' ),
				'ajax' => __( 'Load items on button click', 'us' ),
				'infinite' => __( 'Load items on page scroll', 'us' ),
			),
			'std' => 'none',
			'show_if' => array(
				'post_type',
				'!=',
				array( 'taxonomy_terms', 'current_child_terms', 'product_upsells', 'product_crosssell', 'ids_terms' ),
			),
			'exclude_for_carousel' => TRUE,
			'usb_preview' => TRUE,
		),
		'pagination_style' => array(
			'title' => __( 'Pagination Style', 'us' ),
			'description' => $misc['desc_btn_styles'],
			'type' => 'select',
			'options' => us_array_merge(
				array(
					'' => '– ' . us_translate( 'Default' ) . ' –',
				), us_get_btn_styles()
			),
			'std' => '',
			'show_if' => array( 'pagination', '=', 'regular' ),
			'exclude_for_carousel' => TRUE,
			'usb_preview' => array(
				array(
					'elm' => 'nav.pagination:first > .nav-links',
					'toggle_class' => 'custom',
				),
				array(
					'elm' => 'nav.pagination:first > .nav-links',
					'mod' => 'us-nav-style',
				),
			),
		),
		'pagination_btn_text' => array(
			'title' => __( 'Button Label', 'us' ),
			'type' => 'text',
			'std' => __( 'Load More', 'us' ),
			'cols' => 2,
			'show_if' => array( 'pagination', '=', 'ajax' ),
			'exclude_for_carousel' => TRUE,
			'usb_preview' => array(
				'elm' => '.g-loadmore:first .w-btn-label',
				'attr' => 'text',
			),
		),
		'pagination_btn_size' => array(
			'title' => __( 'Button Size', 'us' ),
			'description' => $misc['desc_font_size'],
			'type' => 'text',
			'std' => '',
			'cols' => 2,
			'show_if' => array( 'pagination', '=', 'ajax' ),
			'exclude_for_carousel' => TRUE,
			'usb_preview' => array(
				'elm' => '.g-loadmore:first .w-btn',
				'css' => 'font-size',
			),
		),
		'pagination_btn_style' => array(
			'title' => __( 'Button Style', 'us' ),
			'description' => $misc['desc_btn_styles'],
			'type' => 'select',
			'options' => us_get_btn_styles(),
			'std' => '1',
			'show_if' => array( 'pagination', '=', 'ajax' ),
			'exclude_for_carousel' => TRUE,
			'usb_preview' => array(
				'elm' => '.g-loadmore:first .w-btn',
				'mod' => 'us-btn-style',
			),
		),
		'pagination_btn_fullwidth' => array(
			'type' => 'switch',
			'switch_text' => __( 'Stretch to the full width', 'us' ),
			'std' => 0,
			'show_if' => array( 'pagination', '=', 'ajax' ),
			'exclude_for_carousel' => TRUE,
			'usb_preview' => array(
				'elm' => '.g-loadmore:first',
				'toggle_class' => 'width_full',
			),
		),
	)
);

// Appearance
$appearance_params = array(
	'items_layout' => array(
		'title' => __( 'Grid Layout', 'us' ),
		'description' => $misc['desc_grid_layout'],
		'type' => 'select',
		'options' => us_get_grid_layouts_for_selection(),
		'std' => 'blog_1',
		'classes' => 'for_grid_layouts',
		'settings' => array(
			'html-data' => array(
				'edit_link' => admin_url( '/post.php?post=%d&action=edit' ),
			),
		),
		'admin_label' => TRUE,
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'type' => array(
		'title' => __( 'Display as', 'us' ),
		'type' => 'select',
		'options' => array(
			'grid' => __( 'Regular Grid', 'us' ),
			'masonry' => __( 'Masonry', 'us' ),
			'metro' => __( 'METRO (works with square items only)', 'us' ),
		),
		'std' => 'grid',
		'admin_label' => TRUE,
		'exclude_for_carousel' => TRUE,
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'items_valign' => array(
		'switch_text' => __( 'Center items vertically', 'us' ),
		'type' => 'switch',
		'std' => 0,
		'classes' => 'for_above',
		'show_if' => array( 'type', '=', 'grid' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'ignore_items_size' => array(
		'switch_text' => __( 'Ignore items custom size', 'us' ),
		'type' => 'switch',
		'std' => 0,
		'classes' => 'for_above',
		'show_if' => array( 'type', '!=', 'metro' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'load_animation' => array(
		'title' => __( 'Items animation on load', 'us' ),
		'type' => 'select',
		'options' => array(
			'none' => us_translate( 'None' ),
			'fade' => __( 'Fade', 'us' ),
			'afc' => __( 'Appear From Center', 'us' ),
			'afl' => __( 'Appear From Left', 'us' ),
			'afr' => __( 'Appear From Right', 'us' ),
			'afb' => __( 'Appear From Bottom', 'us' ),
			'aft' => __( 'Appear From Top', 'us' ),
			'hfc' => __( 'Height Stretch', 'us' ),
			'wfc' => __( 'Width Stretch', 'us' ),
		),
		'std' => 'none',
		'exclude_for_carousel' => TRUE,
		'group' => us_translate( 'Appearance' ),
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
		'std' => '2',
		'admin_label' => TRUE,
		'cols' => 2,
		'show_if' => array( 'type', '!=', 'metro' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'items_gap' => array(
		'title' => __( 'Gap between Items', 'us' ),
		'type' => 'slider',
		'std' => '1.5rem',
		'options' => array(
			'px' => array(
				'min' => 0,
				'max' => 60,
			),
			'%' => array(
				'min' => 0,
				'max' => 5,
				'step' => 0.5,
			),
			'rem' => array(
				'min' => 0.0,
				'max' => 4.0,
				'step' => 0.1,
			),
			'vw' => array(
				'min' => 0.0,
				'max' => 4.0,
				'step' => 0.1,
			),
			'vh' => array(
				'min' => 0.0,
				'max' => 4.0,
				'step' => 0.1,
			),
		),
		'cols' => 2,
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'img_size' => array(
		'title' => __( 'Post Image Size', 'us' ),
		'description' => $misc['desc_img_sizes'],
		'type' => 'select',
		'options' => array_merge(
			array( 'default' => __( 'As in Grid Layout', 'us' ) ), us_get_image_sizes_list()
		),
		'std' => 'default',
		'cols' => 2,
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'title_size' => array(
		'title' => __( 'Post Title Size', 'us' ),
		'description' => $misc['desc_font_size'],
		'type' => 'text',
		'std' => '',
		'cols' => 2,
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'items_ratio' => array(
		'title' => __( 'Items Aspect Ratio', 'us' ),
		'type' => 'select',
		'options' => array(
			'default' => __( 'As in Grid Layout', 'us' ),
			'1x1' => '1x1 ' . __( 'square', 'us' ),
			'4x3' => '4x3 ' . __( 'landscape', 'us' ),
			'3x2' => '3x2 ' . __( 'landscape', 'us' ),
			'16x9' => '16:9 ' . __( 'landscape', 'us' ),
			'2x3' => '2x3 ' . __( 'portrait', 'us' ),
			'3x4' => '3x4 ' . __( 'portrait', 'us' ),
			'custom' => __( 'Custom', 'us' ),
		),
		'std' => 'default',
		'show_if' => array( 'type', '!=', 'metro' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'items_ratio_width' => array(
		'description' => __( 'Examples:', 'us' ) . ' <span class="usof-example">21</span>, <span class="usof-example">1200</span>, <span class="usof-example">640px</span>',
		'type' => 'text',
		'std' => '21',
		'cols' => 2,
		'classes' => 'for_above',
		'show_if' => array( 'items_ratio', '=', 'custom' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'items_ratio_height' => array(
		'description' => __( 'Examples:', 'us' ) . ' <span class="usof-example">9</span>, <span class="usof-example">750</span>, <span class="usof-example">380px</span>',
		'type' => 'text',
		'std' => '9',
		'cols' => 2,
		'classes' => 'for_above',
		'show_if' => array( 'items_ratio', '=', 'custom' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'overriding_link' => array(
		'title' => __( 'Overriding Link', 'us' ),
		'description' => __( 'Applies to every item of this Grid.', 'us' ) . ' ' . __( 'All inner elements become not clickable.', 'us' ),
		'type' => 'link',
		'dynamic_values' => array(
			'post' => array(
				'post' => __( 'Post Link', 'us' ),
				'popup_post' => __( 'Open Post in a Popup', 'us' ),
				'popup_image' => __( 'Open Post Image in a Popup', 'us' ),
				'custom_field|us_tile_link' => sprintf( '%s: %s', __( 'Additional Settings', 'us' ), __( 'Custom Link', 'us' ) ),
			),
			'media' => array(
				'custom_field|us_attachment_link' => __( 'Custom Link', 'us' ),
			),
		),
		'std' => '{"url":""}',
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'popup_width' => array(
		'title' => __( 'Popup Width', 'us' ),
		'description' => $misc['desc_width'],
		'type' => 'text',
		'std' => '',
		'show_if' => array( 'overriding_link', 'str_contains', 'popup_post' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'popup_arrows' => array(
		'switch_text' => __( 'Prev/Next arrows', 'us' ),
		'type' => 'switch',
		'std' => 1,
		'show_if' => array( 'overriding_link', 'str_contains', 'popup_post' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
);

// Built-in filters
$filter_params = array_merge(
	$filter_taxonomies_params, array(
		'filter_style' => array(
			'title' => __( 'Filter Bar Style', 'us' ),
			'type' => 'radio',
			'options' => array(
				'style_1' => '1',
				'style_2' => '2',
				'style_3' => '3',
			),
			'std' => 'style_1',
			'show_if' => array( 'post_type', '=', array_keys( $known_post_type_taxonomies ) ),
			'exclude_for_carousel' => TRUE,
			'group' => us_translate( 'Filter' ),
			'usb_preview' => TRUE,
		),
		'filter_align' => array(
			'title' => __( 'Filter Bar Alignment', 'us' ),
			'type' => 'radio',
			'labels_as_icons' => 'fas fa-align-*',
			'options' => array(
				'none' => us_translate( 'Default' ),
				'left' => us_translate( 'Left' ),
				'center' => us_translate( 'Center' ),
				'right' => us_translate( 'Right' ),
			),
			'std' => 'center',
			'show_if' => array( 'post_type', '=', array_keys( $known_post_type_taxonomies ) ),
			'exclude_for_carousel' => TRUE,
			'group' => us_translate( 'Filter' ),
			'usb_preview' => array(
				'elm' => '.g-filters:first',
				'mod' => 'align',
			),
		),
		'filter_show_all' => array(
			'switch_text' => __( 'Show "All" item in filter bar', 'us' ),
			'type' => 'switch',
			'std' => 1,
			'show_if' => array( 'post_type', '=', array_keys( $known_post_type_taxonomies ) ),
			'exclude_for_carousel' => TRUE,
			'group' => us_translate( 'Filter' ),
			'usb_preview' => TRUE,
		),
	)
);

// Responsive Options
$responsive_params = array(
	'breakpoint_1_width' => array(
		'title' => __( 'Below screen width', 'us' ),
		'type' => 'slider',
		'options' => array(
			'px' => array(
				'min' => 900,
				'max' => 1500,
			),
		),
		'std' => ( (int) us_get_option( 'laptops_breakpoint' ) + 1 ) . 'px',
		'cols' => 2,
		'show_if' => array( 'type', '!=', 'metro' ),
		'exclude_for_carousel' => TRUE,
		'group' => __( 'Responsive', 'us' ),
		'usb_preview' => TRUE,
	),
	'breakpoint_1_cols' => array(
		'title' => __( 'show', 'us' ),
		'type' => 'select',
		'options' => $misc['column_values'],
		'std' => '3',
		'cols' => 2,
		'show_if' => array( 'type', '!=', 'metro' ),
		'exclude_for_carousel' => TRUE,
		'group' => __( 'Responsive', 'us' ),
		'usb_preview' => TRUE,
	),
	'breakpoint_2_width' => array(
		'title' => __( 'Below screen width', 'us' ),
		'type' => 'slider',
		'options' => array(
			'px' => array(
				'min' => 600,
				'max' => 1200,
			),
		),
		'std' => ( (int) us_get_option( 'tablets_breakpoint' ) + 1 ) . 'px',
		'cols' => 2,
		'show_if' => array( 'type', '!=', 'metro' ),
		'exclude_for_carousel' => TRUE,
		'group' => __( 'Responsive', 'us' ),
		'usb_preview' => TRUE,
	),
	'breakpoint_2_cols' => array(
		'title' => __( 'show', 'us' ),
		'type' => 'select',
		'options' => $misc['column_values'],
		'std' => '2',
		'cols' => 2,
		'show_if' => array( 'type', '!=', 'metro' ),
		'exclude_for_carousel' => TRUE,
		'group' => __( 'Responsive', 'us' ),
		'usb_preview' => TRUE,
	),
	'breakpoint_3_width' => array(
		'title' => __( 'Below screen width', 'us' ),
		'type' => 'slider',
		'options' => array(
			'px' => array(
				'min' => 300,
				'max' => 900,
			),
		),
		'std' => ( (int) us_get_option( 'mobiles_breakpoint' ) + 1 ) . 'px',
		'cols' => 2,
		'show_if' => array( 'type', '!=', 'metro' ),
		'exclude_for_carousel' => TRUE,
		'group' => __( 'Responsive', 'us' ),
		'usb_preview' => TRUE,
	),
	'breakpoint_3_cols' => array(
		'title' => __( 'show', 'us' ),
		'type' => 'select',
		'options' => $misc['column_values'],
		'std' => '1',
		'cols' => 2,
		'show_if' => array( 'type', '!=', 'metro' ),
		'exclude_for_carousel' => TRUE,
		'group' => __( 'Responsive', 'us' ),
		'usb_preview' => TRUE,
	),
);

$grid_config['params'] = us_set_params_weight(
	$general_params, $appearance_params, $filter_params, $responsive_params, $conditional_params, $design_options_params
);

$grid_config['usb_init_js'] = '$elm.wGrid();$us.$window.trigger( \'scroll.waypoints\' );jQuery( \'[data-content-height]\', $elm ).usCollapsibleContent()';

/**
 * @return array
 */
return $grid_config;
