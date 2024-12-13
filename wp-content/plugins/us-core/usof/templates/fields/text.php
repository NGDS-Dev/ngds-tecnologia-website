<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Text
 *
 * Simple text field.
 *
 * @var $name  string Field name
 * @var $id    string Field ID
 * @var $field array Field options
 *
 * @param $field ['title'] string Field title
 * @param $field ['description'] string Field title
 * @param $field ['placeholder'] string Field placeholder
 *
 * @var $value string Current value
 */

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

// TODO: Move to `/usof/templates/field.php`
if ( is_array( $hidden_atts['value'] ) ) {
	$hidden_atts['value'] = rawurlencode( json_encode( $hidden_atts['value'] ) );
}

// Output the element
$output = '<input' . us_implode_atts( $hidden_atts ) . '/>';

// By default we display the default value
if ( is_array( $value ) AND isset( $value['default'] ) ) {
	$value = $value['default'];
}

// Text input field
$input_atts = array(
	'type' => 'text',
	'value' => $value,
);
if ( ! empty( $field['placeholder'] ) ) {
	$input_atts['placeholder'] = $field['placeholder'];
}
$input_html = '<input' . us_implode_atts( $input_atts ) . '/>';

// Custom html for dynamic value indication
if ( $dynamic_values = us_arr_path( $field, 'dynamic_values' ) ) {
	$popup_id = us_uniqid( /* length */6 );

	$output .= '<div class="usof-form-input-group">';
	$output .= $input_html;
	$output .= '<div class="usof-form-input-dynamic-value hidden" data-popup-show="' . esc_attr( $popup_id ) . '">';
	$output .= '<span class="usof-form-input-dynamic-value-title"></span>';
	$output .= '<button type="button" class="action_remove_dynamic_value ui-icon_close" title="' . esc_attr( us_translate( 'Remove' ) ) . '"></button>';
	$output .= '</div>'; // .usof-form-input-dynamic-value

	$output .= '<div class="usof-form-input-group-controls">';
	$show_button_atts = array(
		'class' => 'fas fa-database',
		'data-popup-show' => $popup_id,
		'title' => __( 'Select Dynamic Value', 'us' ),
	);
	$output .= '<button' . us_implode_atts( $show_button_atts ) . '></button>';
	$output .= '</div>'; // .usof-form-input-group-controls
	$output .= '</div>'; // .usof-form-input-group

	// Predefined text values
	$predefined_dynamic_values = array(
		'global' => array(
			'{{site_title}}' => us_translate( 'Site Title' ),
		),
		'post' => array(
			'{{the_title}}' => __( 'Post Title', 'us' ),
			'{{post_type_singular}}' => __( 'Post Type (singular)', 'us' ),
			'{{post_type_plural}}' => __( 'Post Type (plural)', 'us' ),
			'{{comment_count}}' => __( 'Comments Amount', 'us' ),
		),
		'acf_types' => array(
			'text',
			'number',
			'range',
			'email',
			'date_picker',
			'date_time_picker',
			'time_picker',
		),
	);

	// Append dynamic values from the config if defined
	if ( is_array( $dynamic_values ) ) {
		$predefined_dynamic_values = array_merge( $predefined_dynamic_values, $dynamic_values );
	}

	// Add popup to output
	$output .= us_get_template( 'usof/templates/popup', /* popup vars */array(
		'popup_id' => $popup_id,
		'popup_group_buttons' => (array) apply_filters( 'us_text_dynamic_values', $predefined_dynamic_values ),
	) );

} else {
	$output .= $input_html;
}

echo $output;
