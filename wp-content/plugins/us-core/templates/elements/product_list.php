<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * WooCommerce Product List
 */

if ( ! class_exists( 'woocommerce' ) ) {
	return;
}

// Never output a Grid element inside other Grids
global $us_grid_outputs_items;
if ( ! empty( $us_grid_outputs_items ) ) {
	return;
}

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
 * Generate query for WP_Query
 */
$query_args = array(
	'post_type' => array( 'product' ), // keep it be array for further conditions
	'posts_per_page' => $show_all ? 999 : (int) $quantity,
	'ignore_sticky_posts' => TRUE, // speeds up the query
	'no_found_rows' => TRUE, // speeds up the query
	'post__not_in' => array(),
	'tax_query' => array(),
	'meta_query' => array(),
);

// Fix post status because in Live preview they are different from the front-end
if ( usb_is_preview() ) {
	$query_args['post_status'] = array( 'publish', 'private' );
}

// Selected products
if ( $source == 'post__in' ) {
	$query_args['post__in'] = explode( ',', $ids );

	// Exclude selected products
} elseif ( $source == 'post__not_in' ) {
	$query_args['post__not_in'] = explode( ',', $ids );

	// UpSells products
} elseif ( $source == 'upsells' AND ! usb_is_template_preview() ) {
	$query_args['post_type'][] = 'product_variation';
	$query_args['post__in'] = (array) get_post_meta( get_the_ID(), '_upsell_ids', TRUE );

	// Cross-Sells products
} elseif ( $source == 'crosssell' AND ! usb_is_template_preview() ) {
	$crosssell_ids = array();

	// Cart Page Cross-sells
	if ( is_cart() ) {
		$cross_sells = array_filter( array_map( 'wc_get_product', WC()->cart->get_cross_sells() ), 'wc_products_array_filter_visible' );
		foreach ( $cross_sells as $cross_sell ) {
			$crosssell_ids[] = $cross_sell->get_id();
		}

		// Product Page Cross-sells
	} else {
		$crosssell_ids = get_post_meta( get_the_ID(), '_crosssell_ids', TRUE );
	}

	$query_args['post_type'][] = 'product_variation';
	$query_args['post__in'] = (array) $crosssell_ids;
}

// Exclude the current product from the query
if ( $exclude_current_product AND $current_object_id ) {
	$query_args['post__not_in'] = array_merge( $query_args['post__not_in'], array( $current_object_id ) );
}

// Include On-sale products only
if ( $onsale_only ) {

	// Get all onsale product ids
	$onsale_ids = wc_get_product_ids_on_sale();

	// Exclude ids matching 'post__not_in' first
	if ( ! empty( $query_args['post__not_in'] ) ) {
		$onsale_ids = array_diff( $onsale_ids, $query_args['post__not_in'] );
	}

	// then add ids matching 'post__in' if set
	if ( ! empty( $query_args['post__in'] ) ) {
		$query_args['post__in'] = array_intersect( $onsale_ids, $query_args['post__in'] );
	} else {
		$query_args['post__in'] = $onsale_ids;
	}
}

// Force zero id in case of empty 'post__in' array, because it is ignored by WP_Query
if ( isset( $query_args['post__in'] ) AND empty( $query_args['post__in'] ) ) {
	$query_args['post__in'] = array( 0 );
}

// Tax query based on Product terms conditions
if ( is_string( $tax_query ) ) {
	$tax_query = json_decode( urldecode( $tax_query ), TRUE );
}
if ( ! is_array( $tax_query ) ) {
	$tax_query = array();
}
if ( $tax_query_relation != 'none' AND ! empty( $tax_query ) ) {
	foreach ( $tax_query as &$_tax ) {

		// Explode terms IDs to array
		if ( ! empty( $_tax['terms'] ) ) {
			$_tax['terms'] = explode( ',', $_tax['terms'] );
		}

		// Get terms of the current post 
		if ( $_tax['operator'] == 'CURRENT' AND ! usb_is_template_preview() ) {
			$_tax['terms'] = wp_get_object_terms( $current_object_id, $_tax['taxonomy'], array( 'fields' => 'ids' ) );
			$_tax['operator'] = 'IN';
		}

		// Transfer to bool var type
		$_tax['include_children'] = (bool) $_tax['include_children'];
	}
	$tax_query['relation'] = $tax_query_relation;
	$query_args['tax_query'] = $tax_query;
}

// Featured products only
if ( $featured_only ) {
	$query_args['tax_query'][] = array(
		'taxonomy' => 'product_visibility',
		'field' => 'name',
		'terms' => 'featured',
		'operator' => 'IN',
	);
}

// Exclude Hidden / Out of Stock products via the "product_visibility" taxonomy
if ( $exclude_hidden OR $exclude_out_of_stock ) {
	$_product_visibility_terms = array();
	if ( $exclude_hidden ) {
		$_product_visibility_terms[] = 'exclude-from-catalog';
	}
	if ( $exclude_out_of_stock ) {
		$_product_visibility_terms[] = 'outofstock';
	}
	$query_args['tax_query'][] = array(
		'taxonomy' => 'product_visibility',
		'field' => 'name',
		'terms' => $_product_visibility_terms,
		'operator' => 'NOT IN',
	);

	// DEV: when tax_query has "OR" relation from user settings, exclusions above won't work, so change the relation to "AND"
	$query_args['tax_query']['relation'] = 'AND';
}

// Meta query based on Custom Fields conditions
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

// Price comparison
if ( $price_compare != 'none' AND $price ) {

	$price = str_replace( ' ', '', $price ); // remove spaces
	$price = str_replace( ',', '.', $price ); // replace comma by dot

	$price_meta_query = array(
		'key' => '_price',
		'value' => (float) $price,
	);

	switch ( $price_compare ) {
		case 'not_equal':
			$price_meta_query['compare'] = '!=';
			break;

		case 'greater':
			$price_meta_query['compare'] = '>';
			$price_meta_query['type'] = 'DECIMAL(9,2)'; // for correct comparison with float value
			break;

		case 'greater_equal':
			$price_meta_query['compare'] = '>=';
			$price_meta_query['type'] = 'DECIMAL(9,2)';
			break;

		case 'less':
			$price_meta_query['compare'] = '<';
			$price_meta_query['type'] = 'DECIMAL(9,2)';
			break;

		case 'less_equal':
			$price_meta_query['compare'] = '<=';
			$price_meta_query['type'] = 'DECIMAL(9,2)';
			break;

		case 'in_range':
			$price_max = str_replace( ' ', '', $price_max ); // remove spaces
			$price_max = str_replace( ',', '.', $price_max ); // replace comma by dot

			$price_meta_query['value'] = array( (float) $price, (float) $price_max );
			$price_meta_query['compare'] = 'BETWEEN';
			$price_meta_query['type'] = 'DECIMAL(9,2)';
			break;

		default:
			$price_meta_query['compare'] = '=';
			break;
	}

	// DEV: when meta_query has "OR" relation from user settings, price comparison above won't work, so change the relation to "AND"
	$query_args['meta_query']['relation'] = 'AND';

	$query_args['meta_query'][] = $price_meta_query;
}



// Order
if ( $orderby == 'custom' AND ! empty( $orderby_custom_field ) ) {
	if ( $orderby_custom_type ) {
		$orderby = 'meta_value_num';
	} else {
		$orderby = 'meta_value';
	}
	$query_args['meta_key'] = $orderby_custom_field;
}

$query_args['orderby'] = $orderby;
$query_args['order'] = $order_invert ? 'DESC' : 'ASC';

// Order by Price
if ( $orderby == 'price' ) {
	$query_args['orderby'] = 'meta_value_num';
	$query_args['meta_key'] = '_price';
}

// Order by Total Sales
if ( $orderby == 'total_sales' ) {
	$query_args['orderby'] = 'meta_value_num';
	$query_args['meta_key'] = 'total_sales';
}

// Order by Rating
if ( $orderby == 'rating' ) {
	$query_args['orderby'] = 'meta_value_num';
	$query_args['meta_key'] = '_wc_average_rating';
}



// Apply filter for developers purposes
$query_args = apply_filters( 'us_product_list_query_args', $query_args );

// Get the new query result
$product_query = new WP_Query( $query_args );

// Define if no results
$no_results = $product_query->have_posts() ? FALSE : TRUE;

// Get Grid Layout settings
$grid_layout_settings = us_get_grid_layout_settings( $items_layout, /* default_template */ 'shop_standard' );

// Get all needed variables to pass into listing-start & listing-end templates
$template_vars = array(
	'grid_atts' => isset( $_atts ) ? $_atts : array(),
	'classes' => isset( $classes ) ? $classes : '',
	'grid_elm_id' => ! empty( $el_id ) ? $el_id : ( 'us_grid_' . us_uniqid() ),
	'grid_layout_settings' => $grid_layout_settings,
	'no_results' => $no_results,
);

// Additional CSS class to define that is the Product List element 
$template_vars['classes'] .= ' us_product_list';

// Add default values for unset variables from the config
foreach ( us_shortcode_atts( array(), 'us_product_list' ) as $param => $value ) {
	$template_vars[ $param ] = isset( $$param ) ? $$param : $value;
}

// Load List Start
us_load_template( 'templates/us_grid/listing-start', $template_vars );

if ( ! $no_results ) {

	// Define variables which needed in the item template
	$item_vars = array(
		'columns' => $columns,
		'grid_layout_settings' => $grid_layout_settings,
		'type' => $type,
		'ignore_items_size' => $ignore_items_size,
		'load_animation' => $load_animation,
		'overriding_link' => $overriding_link,
	);

	while( $product_query->have_posts() ) {
		$product_query->the_post();

		// Load List Post for every product
		us_load_template( 'templates/us_grid/listing-post', $item_vars );
	}

	// Reset the global $post variable
	wp_reset_postdata();
}

// Load List End
us_load_template( 'templates/us_grid/listing-end', $template_vars );
