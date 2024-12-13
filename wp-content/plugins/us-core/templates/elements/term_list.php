<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_term_list
 */

// Never output a Grid element inside other Grids
global $us_grid_outputs_items;
if ( ! empty( $us_grid_outputs_items ) ) {
	return;
}

// Set the item type for grid php templates
global $us_grid_item_type;
$us_grid_item_type = 'term';

// Define relevant values into global variable
global $us_grid_no_results;
$us_grid_no_results = array(
	'action' => $no_items_action,
	'message' => $no_items_message,
	'page_block' => $no_items_page_block,
);

// "Hide on" values are needed for the "No results" block
global $us_grid_hide_on_states;
$us_grid_hide_on_states = $hide_on_states;

// Get the ID of the current object (post, term, user)
$current_object_id = us_get_current_id();

/*
 * Generate query for get_terms()
 */
$query_args = array();

// Child terms of the current term
if ( $source == 'current_term' AND ! usb_is_template_preview() ) {
	if ( $current_term = get_term( $current_object_id ) ) {
		$query_args['taxonomy'] = $current_term->taxonomy;
	}

	// Show all levels of child terms
	if ( $include_children ) {
		$query_args['child_of'] = $current_object_id;

		// Show only the first sub-level of child terms
	} else {
		$query_args['parent'] = $current_object_id;
	}

	// Cases with user selected taxonomy
} else {
	if ( ! empty( $taxonomy ) ) {
		$query_args['taxonomy'] = explode( ',', $taxonomy );
	} else {
		$query_args['taxonomy'] = 'category';
	}

	// Include selected terms
	if ( $source == 'include' ) {
		$query_args['include'] = explode( ',', $term_ids );

		// Exclude selected terms
	} elseif ( $source == 'exclude' ) {

		// Exclude child terms or not
		if ( $include_children AND $term_ids ) {
			$query_args['exclude_tree'] = explode( ',', $term_ids );
		} else {
			$query_args['exclude'] = explode( ',', $term_ids );
		}

		// Child terms of the first selected term
	} elseif ( $source == 'children' ) {

		// Get only the first term ID
		if ( ! $_first_term_id = strstr( $term_ids, ',', TRUE ) ) {
			$_first_term_id = $term_ids;
		}

		// Show all levels of child terms
		if ( $include_children ) {
			$query_args['child_of'] = $_first_term_id;

			// Show only the first sub-level of child terms
		} else {
			$query_args['parent'] = $_first_term_id;
		}

		// Terms of the current post
	} elseif ( $source == 'current_post' AND ! usb_is_template_preview() ) {

		// Use the current post ID to get its terms
		$query_args['object_ids'] = $current_object_id;

		// All terms
	} else {
		if ( ! $include_children ) {
			$query_args['parent'] = '0';
		}
	}

	// Exclude the current term
	if ( $exclude_current AND is_archive() ) {
		if ( ! empty( $query_args['exclude'] ) ) {
			$query_args['exclude'][] = $current_object_id;
		} else {
			$query_args['exclude'] = $current_object_id;
		}
	}
}

// Hide empty terms
$query_args['hide_empty'] = (bool) $hide_empty;

// Order
if ( $order_invert ) {
	$query_args['order'] = 'DESC';
} else {
	$query_args['order'] = 'ASC';
}

// Order by
if ( $orderby == 'custom' AND ! empty( $orderby_custom_field ) ) {
	if ( $orderby_custom_type ) {
		$orderby = 'meta_value_num';
	} else {
		$orderby = 'meta_value';
	}
	$query_args['meta_key'] = $orderby_custom_field;
}
$query_args['orderby'] = $orderby;

// Generate meta_query based on Custom Fields conditions
if ( is_string( $meta_query ) ) {
	$meta_query = json_decode( urldecode( $meta_query ), TRUE );
}
if ( ! is_array( $meta_query ) ) {
	$meta_query = array();
}
if ( $meta_query_relation != 'none' AND ! empty( $meta_query ) ) {
	foreach ( $meta_query as &$_meta ) {

		// Unset the field value for specific "compare" values
		if ( in_array( $_meta['compare'], array( 'EXISTS', 'NOT EXISTS' ) ) ) {
			unset( $_meta['value'] );
		} else {
			$_meta['value'] = us_replace_dynamic_value( $_meta['value'] );
		}

		// Set the NUMERIC type for specific "compare" values
		if ( in_array( $_meta['compare'], array( '>', '>=', '<', '<=' ) ) ) {
			$_meta['type'] = 'NUMERIC';
		}
	}
	$meta_query['relation'] = $meta_query_relation;
	$query_args['meta_query'] = $meta_query;
}

// Number
if (
	$limit_number
	AND (int) $number
	AND $orderby !== 'rand'
) {
	$query_args['number'] = (int) $number;
}

// Apply filter for developers purposes
$query_args = apply_filters( 'us_term_list_query_args', $query_args );

// Get result by query args
$terms = get_terms( $query_args );

// Reset the result in case of error
if ( is_wp_error( $terms ) ) {
	$terms = array();
}

// Order by random
if ( $orderby == 'rand' ) {
	shuffle( $terms );

	if ( $limit_number AND (int) $number ) {
		$terms = array_slice( $terms, 0, (int) $number );
	}
}

// Set unique element ID
$grid_elm_id = ! empty( $el_id ) ? $el_id : 'us_grid_' . us_uniqid();

// Get Grid Layout settings
$grid_layout_settings = us_get_grid_layout_settings( $items_layout );

// Get all needed variables to pass into listing-start & listing-end templates
$template_vars = array(
	'grid_atts' => isset( $_atts ) ? $_atts : array(),
	'classes' => isset( $classes ) ? $classes : '',
	'grid_elm_id' => $grid_elm_id,
	'grid_layout_settings' => $grid_layout_settings,
	'no_results' => empty( $terms ),
	'items_count' => count( $terms ),
);

// Additional CSS class to define that is the Term List element 
$template_vars['classes'] .= ' us_term_list';

// Add default values for unset variables from the config
foreach ( us_shortcode_atts( array(), 'us_term_list' ) as $param => $value ) {
	$template_vars[ $param ] = isset( $$param ) ? $$param : $value;
}

// Load List Start
us_load_template( 'templates/us_grid/listing-start', $template_vars );

if ( ! empty( $terms ) ) {

	// Define variables which needed in the item template
	$item_vars = array(
		'columns' => $columns,
		'grid_layout_settings' => $grid_layout_settings,
		'type' => $type,
		'load_animation' => $load_animation,
		'overriding_link' => $overriding_link,
	);

	// Load List Term
	global $us_grid_term;
	foreach ( $terms as $term ) {
		$us_grid_term = $term;
		us_load_template( 'templates/us_grid/listing-term', $item_vars );
	}
	$us_grid_term = NULL;
}

// Load List End
us_load_template( 'templates/us_grid/listing-end', $template_vars );
