<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Select
 *
 * Drop-down selector field.
 *
 * @param $field ['title'] string Field title
 * @param $field ['description'] string Field title
 * @param $field ['options'] array List of value => title pairs
 * @param $field ['settings'] array End-to-end settings for example for Ajax requests
 * @param $field ['us_vc_field'] bool Field used in Visual Composer
 *
 * @var $name  string Field name
 * @var $id    string Field ID
 * @var $field array Field options
 * @var $classes string
 *
 * @var   $value string Current value
 */

if ( empty( $field['options'] ) OR ! is_array( $field['options'] ) ) {
	return;
}

$value = isset( $value )
	? (string) $value
	: '';

$_atts = array(
	'class' => 'usof-select',
);

if ( ! empty( $classes ) ) {
	$_atts['class'] .= ' ' . $classes;
}

// The custom html-data example: `<div data-{name}="{value}"...>...</div>`
if ( isset( $field['settings']['html-data'] ) AND is_array( $field['settings']['html-data'] ) ) {
	foreach ( $field['settings']['html-data'] as $attr_name => $attr_value ) {
		$_atts[ 'data-' . $attr_name ] = (string) $attr_value;
	}
}

// Hidden result field
$hidden_atts = array(
	'name' => $name,
	'type' => 'hidden',
	'value' => $value,
);

// Field for editing in WPBakery
// Via the `wpb_vc_param_value` class WPBakery receives the final value
if ( isset( $field['us_vc_field'] ) ) {
	$hidden_atts['class'] = 'wpb_vc_param_value';
}

// By default we display the default value
if ( is_array( $value ) AND isset( $value['default'] ) ) {
	$value = $value['default'];
}

/**
 * Generating the option tag
 *
 * @param string $key The key
 * @param string $name The name
 * @return string
 */
$func_gen_option = function ( $key, $title ) use ( $value, $field, $name ) {
	$option_atts = array(
		'value' => $key,
	);
	if ( selected( $value, $key, /* echo */FALSE ) ) {
		$option_atts[ 'selected' ] = 'selected';
	}
	/*
	 * If this is a select for Page Layout settings,
	 * checking if default templates for page areas were set in Theme Options
	 * and adding data for links to those templates edit pages
	 */
	if (
		usb_is_builder_page()
		AND isset( $field['hints_for'] )
		AND in_array(
			$name, array(
			'us_header_id',
			'us_titlebar_id',
			'us_content_id',
			'us_sidebar_id',
			'us_footer_id',
		)
		)
		AND $key == '__defaults__'
	) {
		$post_type = get_post_type( (int) usb_get_post_id() );
		$area = str_replace( array( 'us_', '_id' ), '', $name );
		$default_id = us_get_page_area_id( $area, array( 'page_type' => 'post', 'post_type' => $post_type ) );
		if ( get_post_status( $default_id ) !== FALSE ) {
			$default_layout = array(
				'id' => $default_id,
			);
			if ( $default_title = get_the_title( $default_id ) ) {
				$default_layout['title'] = $default_title;
			}
		}
		$option_atts['data-default-layout'] = us_json_encode( $default_layout );
	}
	return '<option ' . us_implode_atts( $option_atts ) . '>' . esc_html( $title ) . '</option>';
};

$output = '<div' . us_implode_atts( $_atts ) . '>';
$output .= '<input'. us_implode_atts( $hidden_atts ) .'/>';
$output .= '<select>';

foreach ( $field['options'] as $option_key => $option_value ) {
	if ( is_string( $option_value ) ) {
		$output .= $func_gen_option( $option_key, $option_value );

	} elseif ( ! empty( $option_value ) AND is_array( $option_value ) ) {

		// Node: The label can be set via the special key `_group_label__`
		if ( $group_label = us_arr_path( $option_value, '__group_label__' ) ) {
			unset( $option_value['__group_label__'] );
			$option_key = (string) $group_label;
		}

		$output .= '<optgroup label="' . esc_attr( $option_key ) . '">';
		foreach ( $option_value as $item_key => $item_value ) {
			if ( empty( $item_value ) ) {
				continue;
			}
			$output .= $func_gen_option( $item_key, $item_value );
		}
		$output .= '</optgroup>';
	}
}

$output .= '</select>';
$output .= '</div>';
echo $output;
