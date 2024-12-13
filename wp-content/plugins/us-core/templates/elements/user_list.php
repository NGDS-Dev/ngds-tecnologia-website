<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_user_list
 */

// Never output a Grid element inside other Grids
global $us_grid_outputs_items;
if ( ! empty( $us_grid_outputs_items ) ) {
	return;
}

// Set the item type for grid php templates
global $us_grid_item_type;
$us_grid_item_type = 'user';

// Define relevant values into global variable
global $us_grid_no_results;
$us_grid_no_results = array(
	'action' => $no_items_action,
	'message' => $no_items_message,
);

// "Hide on" values are needed for the "No results" block
global $us_grid_hide_on_states;
$us_grid_hide_on_states = $hide_on_states;

// Get the ID of the current object (post, term, user)
$current_object_id = us_get_current_id();

/*
 * Generate query for get_users()
 */
$query_args = array();

// Include selected users
if ( $source == 'include' ) {
	$query_args['include'] = explode( ',', $user_ids );

	// Exclude selected users
} elseif ( $source == 'exclude' ) {
	$query_args['exclude'] = explode( ',', $user_ids );

	// Users with selected roles
} elseif ( $source == 'role__in' ) {
	$query_args['role__in'] = explode( ',', $role );

	// Users except selected roles
} elseif ( $source == 'role__not_in' ) {
	$query_args['role__not_in'] = explode( ',', $role );
}

// Exclude the current user
if ( $exclude_current AND is_archive() ) {
	if ( ! empty( $query_args['exclude'] ) ) {
		$query_args['exclude'][] = $current_object_id;
	} else {
		$query_args['exclude'] = $current_object_id;
	}
}

// Only with published posts
$query_args['has_published_posts'] = (bool) $has_published_posts;

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
	! $show_all
	AND (int) $number
	AND $orderby !== 'rand'
) {
	$query_args['number'] = (int) $number;
}

// Apply filter for developers purposes
$query_args = apply_filters( 'us_user_list_query_args', $query_args );

// Only user IDs are enough for getting result
$query_args['fields'] = 'ID';

// Get result by query args
$users = get_users( $query_args );

// Order by random
if ( $orderby == 'rand' ) {
	shuffle( $users );

	if ( ! $show_all AND (int) $number ) {
		$users = array_slice( $users, 0, (int) $number );
	}
}

// Set unique element ID
$grid_elm_id = ! empty( $el_id ) ? $el_id : 'us_grid_' . us_uniqid();

// Get Grid Layout settings
$grid_layout_settings = us_get_grid_layout_settings( $items_layout, /* default_template */ 'user_1' );

// Get all needed variables to pass into listing-start & listing-end templates
$template_vars = array(
	'grid_atts' => $_atts ?? array(),
	'classes' => $classes ?? '',
	'grid_elm_id' => $grid_elm_id,
	'grid_layout_settings' => $grid_layout_settings,
	'type' => 'grid',
	'no_results' => empty( $users ),
	'items_count' => count( $users ),
);

// Additional CSS class to define that is the User List element 
$template_vars['classes'] .= ' us_user_list';

// Add default values for unset variables from the config
foreach ( us_shortcode_atts( array(), 'us_user_list' ) as $param => $value ) {
	$template_vars[ $param ] = $$param ?? $value;
}

// Load List Start
us_load_template( 'templates/us_grid/listing-start', $template_vars );

if ( ! empty( $users ) ) {

	// Define variables which needed in the item template
	$item_vars = array(
		'columns' => $columns,
		'grid_layout_settings' => $grid_layout_settings,
		'type' => 'grid',
		'load_animation' => $load_animation,
		'overriding_link' => $overriding_link,
	);

	// Load List User
	global $us_grid_user_ID;
	foreach ( $users as $user_id ) {
		$us_grid_user_ID = $user_id;
		us_load_template( 'templates/us_grid/listing-user', $item_vars );
	}
	$us_grid_user_ID = NULL;
}

// Load List End
us_load_template( 'templates/us_grid/listing-end', $template_vars );
