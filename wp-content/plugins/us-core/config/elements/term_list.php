<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: us_term_list
 */

$elm_config = array(
	'title' => __( 'Term List', 'us' ),
	'category' => __( 'Grid', 'us' ),
	'description' => __( 'List of taxonomy terms.', 'us' ),
	'icon' => 'fas fa-th-large',
	'params' => array(),
);

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

// Order options for Terms
$orderby_options = array(
	'name' => us_translate( 'Title' ),
	'count' => __( 'Amount of posts', 'us' ),
	'include' => __( 'Order of selected terms', 'us' ),
	'menu_order' => __( 'Manual order (for WooCommerce taxonomies)', 'us' ),
	'rand' => us_translate( 'Random' ),
	'custom' => __( 'Custom Field', 'us' ),
);

// Get Reusable Blocks
$us_page_blocks_list = array();
if ( us_is_elm_editing_page() ) {
	$us_page_blocks_list = us_get_posts_titles_for( 'us_page_block' );
}

// WPBakery sends 'post_id' POST variable to the element popup, so remove the current Reusable Block from the list
if (
	$post_id = us_arr_path( $_POST, 'post_id' )
	AND isset( $us_page_blocks_list[ $post_id ] )
) {
	unset( $us_page_blocks_list[ $post_id ] );
}

// Get available taxonomies
$taxonomies = us_get_taxonomies();

// General
$general_params = array(

	'source' => array(
		'title' => us_translate( 'Show' ),
		'type' => 'select',
		'options' => array(
			'all' => __( 'All terms', 'us' ),
			'include' => __( 'Selected terms', 'us' ),
			'exclude' => __( 'Terms except selected', 'us' ),
			'current_term' => __( 'Child terms of the current term', 'us' ),
			'current_post' => __( 'Terms of the current post', 'us' ),
			// TODO: check if this is needed in real cases
			// 'children' => __( 'Child terms of selected term', 'us' ),
		),
		'std' => 'all',
		'admin_label' => TRUE,
		'usb_preview' => TRUE,
	),
	'taxonomy' => array(
		'type' => 'select',
		'options' => $taxonomies,
		'std' => 'category',
		'classes' => 'for_above',
		'show_if' => array( 'source', '!=', array( 'current_children' ) ),
		'usb_preview' => TRUE,
	),
	'term_ids' => array(
		'type' => 'autocomplete',
		'search_text' => __( 'Select terms', 'us' ),
		'is_multiple' => TRUE,
		'is_sortable' => TRUE,
		'ajax_data' => array(
			'_nonce' => wp_create_nonce( 'us_ajax_get_terms_for_autocomplete' ),
			'action' => 'us_get_terms_for_autocomplete',
			'use_term_ids' => TRUE, // use ids instead of slugs
		),
		'options' => array(), // will be loaded via ajax
		'std' => '',
		'classes' => 'for_above',
		'options_filtered_by_param' => 'taxonomy',
		'show_if' => array( 'source', '=', array( 'include', 'exclude', 'children' ) ),
		'usb_preview' => TRUE,
	),
	'include_children' => array(
		'type' => 'switch',
		'switch_text' => __( 'Show child terms', 'us' ),
		'std' => 0,
		'show_if' => array( 'source', '!=', array( 'include', 'current_post' ) ),
		'classes' => 'for_above',
		'usb_preview' => TRUE,
	),
	'hide_empty' => array(
		'type' => 'switch',
		'switch_text' => __( 'Hide empty terms', 'us' ),
		'std' => 0,
		'show_if' => array( 'source', '!=', array( 'current_post' ) ),
		'classes' => 'for_above',
		'usb_preview' => TRUE,
	),
	'exclude_current' => array(
		'type' => 'switch',
		'switch_text' => __( 'Exclude the current term', 'us' ),
		'std' => 0,
		'show_if' => array( 'source', '!=', array( 'current_children', 'current_post' ) ),
		'classes' => 'for_above',
		'usb_preview' => TRUE,
	),

	// ORDER
	'orderby' => array(
		'title' => __( 'Order by', 'us' ),
		'type' => 'select',
		'options' => apply_filters( 'us_term_list_orderby_options', $orderby_options ),
		'std' => 'name',
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

	// NUMBER
	'limit_number' => array(
		'title' => __( 'Quantity', 'us' ),
		'type' => 'switch',
		'switch_text' => __( 'Limit amount of terms', 'us' ),
		'std' => 0,
		'usb_preview' => TRUE,
	),
	'number' => array(
		'type' => 'slider',
		'options' => array(
			'' => array(
				'min' => 1,
				'max' => 30,
			),
		),
		'std' => '12',
		'classes' => 'for_above',
		'show_if' => array( 'limit_number', '=', 1 ),
		'usb_preview' => TRUE,
	),

	// CUSTOM FIELDS
	'meta_query_relation' => array(
		'title' => __( 'Show terms with specific custom fields', 'us' ),
		'type' => 'select',
		'options' => array(
			'none' => us_translate( 'None' ),
			'AND' => __( 'If EVERY condition below is met', 'us' ),
			'OR' => __( 'If ANY condition below is met', 'us' ),
		),
		'std' => 'none',
		'usb_preview' => TRUE,
	),
	'meta_query' => array(
		'type' => 'group',
		'show_controls' => TRUE,
		'label_for_add_button' => __( 'Add condition', 'us' ),
		'is_sortable' => FALSE,
		'is_accordion' => FALSE,
		'accordion_title' => 'key',
		'params' => array(
			'key' => array(
				'title' => __( 'Custom Field', 'us' ),
				'placeholder' => us_translate( 'Field name' ),
				'type' => 'text',
				'std' => 'custom_field_name',
				'admin_label' => TRUE,
			),
			'compare' => array(
				'type' => 'select',
				'options' => array(
					'=' => '=',
					'!=' => '!=',
					'>' => '>',
					'>=' => '≥',
					'<' => '<',
					'<=' => '≤',
					'LIKE' => __( 'Includes', 'us' ),
					'NOT LIKE' => __( 'Excludes', 'us' ),
					'EXISTS' => __( 'Has a value', 'us' ),
					'NOT EXISTS' => __( 'Doesn\'t have a value', 'us' ),
				),
				'std' => '=',
				'classes' => 'for_above',
			),
			'value' => array(
				'placeholder' => us_translate( 'Value' ),
				'type' => 'text',
				'std' => '',
				'show_if' => array( 'compare', '!=', array( 'EXISTS', 'NOT EXISTS' ) ),
				'classes' => 'for_above',
			),
		),
		'std' => array(
			array(
				'key' => 'custom_field_name',
				'compare' => '=',
				'value' => '',
			),
		),
		'show_if' => array( 'meta_query_relation', '!=', 'none' ),
		'usb_preview' => TRUE,
	),

	// NO RESULTS
	'no_items_action'=> array(
		'title' => __( 'Action when no terms found', 'us' ),
		'type' => 'select',
		'options' => array(
			'message' => __( 'Show the message', 'us' ),
			'page_block' => __( 'Show the Reusable Block', 'us' ),
			'hide_grid' => __( 'Hide this element', 'us' ),
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
);

// Appearance
$appearance_params = array(
	'items_layout' => array(
		'title' => __( 'Grid Layout', 'us' ),
		'description' => $misc['desc_grid_layout'],
		'type' => 'select',
		'options' => us_get_grid_layouts_for_selection( array( 'blog', 'tile', 'text', 'side', 'portfolio' ) ),
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
	'columns' => array(
		'title' => us_translate( 'Columns' ),
		'type' => 'select',
		'options' => array(
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6',
			'7' => '7',
			'8' => '8',
			'9' => '9',
			'10' => '10',
		),
		'std' => '3',
		'admin_label' => TRUE,
		'cols' => 2,
		'show_if' => array( 'type', '!=', 'metro' ),
		'group' => us_translate( 'Appearance' ),
		'usb_preview' => TRUE,
	),
	'items_gap' => array(
		'title' => __( 'Gap between Items', 'us' ),
		'type' => 'slider',
		'std' => '10px',
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
	'type' => array(
		'title' => __( 'Display as', 'us' ),
		'type' => 'select',
		'options' => array(
			'grid' => __( 'Regular Grid', 'us' ),
			'masonry' => __( 'Masonry', 'us' ),
			'metro' => __( 'METRO (works with square items only)', 'us' ),
		),
		'std' => 'grid',
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
		'group' => us_translate( 'Appearance' ),
	),
	'img_size' => array(
		'title' => __( 'Post Image Size', 'us' ),
		'description' => $misc['desc_img_sizes'],
		'type' => 'select',
		'options' => us_array_merge(
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
		'description' => __( 'Applies to every term of this list.', 'us' ) . ' ' . __( 'All inner elements become not clickable.', 'us' ),
		'type' => 'link',
		'dynamic_values' => array(
			'term' => array(
				'post' => __( 'Archive Page', 'us' ),
				'popup_post' => __( 'Open Archive Page in a Popup', 'us' ),
				'custom_field|us_tile_link' => sprintf( '%s: %s', __( 'Additional Settings', 'us' ), __( 'Custom Link', 'us' ) ),
			),
			'post' => array(),
			'user' => array(),
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
		'group' => __( 'Responsive', 'us' ),
		'usb_preview' => TRUE,
	),
	'breakpoint_1_cols' => array(
		'title' => __( 'show', 'us' ),
		'type' => 'select',
		'options' => us_array_merge(
			array( 'default' => '– ' . __( 'As on Desktops', 'us' ) . ' –' ), $misc['column_values']
		),
		'std' => 'default',
		'cols' => 2,
		'show_if' => array( 'type', '!=', 'metro' ),
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
		'group' => __( 'Responsive', 'us' ),
		'usb_preview' => TRUE,
	),
	'breakpoint_2_cols' => array(
		'title' => __( 'show', 'us' ),
		'type' => 'select',
		'options' => us_array_merge(
			array( 'default' => '– ' . __( 'As on Desktops', 'us' ) . ' –' ), $misc['column_values']
		),
		'std' => '2',
		'cols' => 2,
		'show_if' => array( 'type', '!=', 'metro' ),
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
		'group' => __( 'Responsive', 'us' ),
		'usb_preview' => TRUE,
	),
);

$elm_config['params'] = us_set_params_weight(
	$general_params,
	$appearance_params,
	$responsive_params,
	$conditional_params,
	$design_options_params
);

$elm_config['usb_init_js'] = '$elm.wGrid();$us.$window.trigger( \'scroll.waypoints\' );';

/**
 * @return array
 */
return $elm_config;
