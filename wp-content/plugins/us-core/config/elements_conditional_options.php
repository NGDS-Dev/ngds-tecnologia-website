<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Conditional Settings for elements
 */

// User roles
$user_roles = array();

// Months
$_months = array();

// Days of the week
$_weekdays = array();

// Avoid DB queries on the frontend
if ( us_is_elm_editing_page() ) {

	// Check if the get_editable_roles function exists for AJAX calls of other plugins compatibility
	$editable_roles = ( function_exists( 'get_editable_roles' ) ) ? get_editable_roles() : array();

	foreach ( $editable_roles as $_slug => $_data ) {
		$user_roles[ $_slug ] = translate_user_role( $_data['name'] );
	}

	for ( $i = 1; $i < 13; $i++ ) {
		global $wp_locale;
		$monthnum = zeroise( $i, 2 );
		$monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
		$_months[ $monthnum ] = $monthtext;
	}

	for ( $i = 0; $i < 7; $i++ ) {
		global $wp_locale;
		$_weekdays[ $i ] = $wp_locale->get_weekday( $i );
	}
}

// Days
$_days = array();
for ( $i = 1; $i < 32; $i++ ) {
	$_day = zeroise( $i, 2 );
	$_days[ $_day ] = $_day;
}

// Years
$_years = array();
for ( $i = 0; $i < 11; $i++ ) {
	$_year = (int) current_time( 'Y' ) + $i;
	$_years[ $_year ] = (string) $_year;
}

// Hours
$_hours = array();
for ( $i = 0; $i < 24; $i++ ) {
	$_hour = zeroise( $i, 2 );
	$_hours[ $_hour ] = $_hour;
}

// Minutes
$_minutes = array();
for ( $i = 0; $i < 60; $i++ ) {
	$_minute = zeroise( $i, 2 );
	$_minutes[ $_minute ] = $_minute;
}

$wc_account_endpoints = array();
$woocommerce_options = array();

// Add WooCommerce options separately
if ( class_exists( 'woocommerce' ) ) {

	// WooCommerce Account endpoints
	if ( us_is_elm_editing_page() ) {
		$wc_account_endpoints = wc_get_account_menu_items();
	}

	$woocommerce_options = array(
		'cart_status' => __( 'Cart State', 'us' ),
		'cart_total' => __( 'Cart Total', 'us' ),
		'wc_account_endpoint' => us_translate( 'Account endpoints', 'woocommerce' ),
	);
}

// Get options from "Advanced Custom Fields" plugin
$acf_custom_fields = array();
if (
	function_exists( 'us_acf_get_fields' )
	AND us_is_elm_editing_page()
) {
	$exclude_types = array(
		'checkbox',
		'clone',
		'file',
		'flexible_content',
		'gallery',
		'google_map',
		'group',
		'image',
		'link',
		'message',
		'page_link',
		'post_object',
		'relationship',
		'repeater',
		'tab',
		'taxonomy',
		'user',
		'wysiwyg',
	);
	foreach( (array) us_acf_get_fields() as $group_id => $fields ) {
		if ( ! is_array( $fields ) ) {
			continue;
		}

		// Get label for current group
		if ( $group_label = us_arr_path( $fields, '__group_label__' ) ) {
			unset( $fields['__group_label__'] );
		}

		foreach( $fields as $field ) {

			// Exclude specific ACF types, which can't output a simple 'string' value
			if ( ! in_array( $field['type'], $exclude_types ) ) {
				$acf_custom_fields[ $group_id ][ $field['name'] ] = $field['label'];
			}
		}

		// Add a group label to the overall result
		if ( $group_label AND ! empty( $acf_custom_fields[ $group_id ] ) ) {
			$acf_custom_fields[ $group_id ]['__group_label__'] = $group_label;
		}
	}
}

$conditions_params = array(
	'param' => array(
		'type' => 'select',
		'options' => array_merge(
			array(
				'time' => us_translate( 'Date/time' ),
				'custom_field' => __( 'Custom Field', 'us' ),
				'page_url' => __( 'Page URL', 'us' ),
				'post_type' => __( 'Post Type', 'us' ),
				'post_id' => __( 'Post ID', 'us' ),
				'tax_term' => __( 'Taxonomy Term', 'us' ),
				'user_role' => __( 'User Role', 'us' ),
				'user_state' => __( 'User State', 'us' ),
			),
			$woocommerce_options
		),
		'std' => 'time',
		'admin_label' => TRUE,
	),
	'cf_name_predefined' => array(
		'type' => 'select',
		'options' => array_merge(
			array(
				'-' => '&ndash; ' . __( 'Select an option', 'us' ) . ' &ndash;',
				'custom' => __( 'Custom', 'us' ),
			),
			$acf_custom_fields
		),
		'std' => '-',
		'show_if' => array( 'param', '=', 'custom_field' ),
	),
	'cf_name' => array(
		'placeholder' => __( 'Field Name', 'us' ),
		'description' => __( 'Enter a custom field name to get its value.', 'us' ),
		'type' => 'text',
		'std' => '',
		'show_if' => array( 'cf_name_predefined', '=', 'custom' ),
	),
	'tax' => array(
		'type' => 'select',
		'options' => us_get_taxonomies(),
		'std' => 'category',
		'show_if' => array( 'param', '=', 'tax_term' ),
	),

	'mode' => array(
		'type' => 'radio',
		'options' => array(
			'=' => __( 'Includes', 'us' ),
			'!=' => __( 'Excludes', 'us' ),
		),
		'std' => '=',
		'show_if' => array( 'param', '=', array( 'page_url', 'post_type', 'post_id', 'tax_term', 'user_role' ) ),
	),
	'cf_mode' => array(
		'type' => 'select',
		'options' => array(
			'=' => __( 'Equal', 'us' ),
			'!=' => __( 'Not equal', 'us' ),
			'>' => __( 'Greater than', 'us' ),
			'<' => __( 'Less than', 'us' ),
			'has_value' => __( 'Has a value', 'us' ),
			'no_value' => __( 'Doesn\'t have a value', 'us' ),
		),
		'std' => '=',
		'show_if' => array( 'param', '=', 'custom_field' ),
	),

	'page_url' => array(
		'placeholder' => us_translate( 'Value' ),
		'type' => 'text',
		'std' => '',
		'show_if' => array( 'param', '=', 'page_url' ),
	),
	'cf_value' => array(
		'placeholder' => us_translate( 'Value' ),
		'type' => 'text',
		'std' => '',
		'show_if' => array( 'cf_mode', '!=', array( 'has_value', 'no_value' ) ),
	),
	'post_value' => array(
		'placeholder' => __( 'Post ID', 'us' ),
		'description' => __( 'For several values use commas', 'us' ),
		'type' => 'text',
		'std' => '',
		'show_if' => array( 'param', '=', 'post_id' ),
	),
	'term_value' => array(
		'placeholder' => us_translate( 'Value' ),
		'description' => __( 'Use ID or slug.', 'us' ) . ' ' . __( 'For several values use commas', 'us' ),
		'type' => 'text',
		'std' => '',
		'show_if' => array( 'param', '=', 'tax_term' ),
	),
	'time_operator' => array(
		'description' => sprintf( us_translate( 'Local time is %s.' ), '<strong>' . wp_date( 'M d Y H:i' ) . '</strong>' ),
		'type' => 'select',
		'options' => array(
			'since' => _x( 'Since', 'specified date', 'us' ),
			'until' => _x( 'Until', 'specified date', 'us' ),
			'w' => __( 'Day of the week', 'us' ),
			'd' => __( 'Day of the month', 'us' ),
			'dm' => __( 'Day of the year', 'us' ),
			'm' => us_translate( 'Month' ),
		),
		'std' => 'since',
		'show_if' => array( 'param', '=', 'time' ),
	),
	'time_weekday' => array(
		'type' => 'select',
		'options' => $_weekdays,
		'std' => '0',
		'cols' => 3,
		'show_if' => array( 'time_operator', '=', 'w' ),
	),
	'time_month' => array(
		'type' => 'select',
		'options' => $_months,
		'std' => current_time( 'm' ),
		'cols' => 4,
		'show_if' => array( 'time_operator', '=', array( 'since', 'until', 'dm', 'm' ) ),
	),
	'time_day' => array(
		'type' => 'select',
		'options' => $_days,
		'std' => current_time( 'd' ),
		'cols' => 6,
		'show_if' => array( 'time_operator', '=', array( 'since', 'until', 'd', 'dm' ) ),
	),
	'time_year' => array(
		'type' => 'select',
		'options' => $_years,
		'std' => current_time( 'Y' ),
		'cols' => 4,
		'show_if' => array( 'time_operator', '=', array( 'since', 'until' ) ),
	),
	'time_hour' => array(
		'type' => 'select',
		'options' => $_hours,
		'std' => '00',
		'cols' => 6,
		'show_if' => array( 'time_operator', '=', array( 'since', 'until' ) ),
	),
	'time_minute' => array(
		'type' => 'select',
		'options' => $_minutes,
		'std' => '00',
		'cols' => 6,
		'show_if' => array( 'time_operator', '=', array( 'since', 'until' ) ),
	),
	'post_type' => array(
		'type' => 'select',
		'options' => us_grid_available_post_types( TRUE ),
		'std' => 'post',
		'show_if' => array( 'param', '=', 'post_type' ),
	),
	'user_state' => array(
		'type' => 'radio',
		'options' => array(
			'logged_in' => __( 'Logged in', 'us' ),
			'logged_out' => __( 'Logged out', 'us' ),
		),
		'std' => 'logged_in',
		'show_if' => array( 'param', '=', 'user_state' ),
	),
	'user_role' => array(
		'type' => 'autocomplete',
		'search_text' => __( 'Select roles', 'us' ),
		'options' => $user_roles,
		'std' => '',
		'is_multiple' => TRUE,
		'show_if' => array( 'param', '=', 'user_role' ),
	),
);

if ( class_exists( 'woocommerce' ) ) {
	$conditions_params = array_merge(
		$conditions_params,
		array(
			'cart_status' => array(
				'type' => 'radio',
				'options' => array(
					'empty' => _x( 'Empty', 'Cart State', 'us' ),
					'not_empty' => _x( 'Not Empty', 'Cart State', 'us' ),
				),
				'std' => 'empty',
				'show_if' => array( 'param', '=', 'cart_status' ),
			),
			'cart_total_mode' => array(
				'type' => 'radio',
				'options' => array(
					'>' => __( 'Greater than', 'us' ),
					'<' => __( 'Less than', 'us' ),
				),
				'std' => '>',
				'show_if' => array( 'param', '=', 'cart_total' ),
			),
			'cart_total' => array(
				'type' => 'text',
				'std' => '100',
				'show_if' => array( 'param', '=', 'cart_total' ),
			),
			'wc_account_endpoint' => array(
				'type' => 'select',
				'options' => $wc_account_endpoints,
				'std' => 'dashboard',
				'show_if' => array( 'param', '=', 'wc_account_endpoint' ),
			),
		)
	);
}

return array(

	'conditions_operator' => array(
		'title' => __( 'Display this Element', 'us' ),
		'type' => 'select',
		'options' => array(
			'always' => __( 'Always', 'us' ),
			'and' => __( 'If EVERY condition below is met', 'us' ),
			'or' => __( 'If ANY condition below is met', 'us' ),
		),
		'std' => 'always',
		'group' => __( 'Display Logic', 'us' ),
	),

	'conditions' => array(
		'type' => 'group',
		'group' => __( 'Display Logic', 'us' ),
		'show_controls' => TRUE,
		'is_sortable' => FALSE,
		'is_accordion' => TRUE,
		'accordion_title' => 'param',
		'std' => array(),
		'show_if' => array( 'conditions_operator', '!=', 'always' ),
		'params' => $conditions_params,
	),
);
