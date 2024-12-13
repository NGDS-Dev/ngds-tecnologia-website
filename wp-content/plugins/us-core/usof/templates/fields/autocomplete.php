<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Autocomplete
 *
 * @param $field array All passed parameters for the field
 * @param $field ['options'] array Initial Parameter List
 * @param $field ['is_multiple'] boolean Multi Select Support
 * @param $field ['is_sortable'] boolean Drag and drop
 * @param $field ['value_separator'] string value separator
 * @param $field ['classes'] string field container classes
 * @param $field ['ajax_data'] array Data transfer for AJAX requests
 * @param $field ['us_vc_field'] bool Field used in Visual Composer
 *
 * The Visual composer
 * @var $name string The name field
 * @var $value string The value of the selected parameters
 */

// For edit mode in USBuilder, these parameters are enabled
$name = isset( $name ) ? $name : '';
$value = isset( $value ) ? $value : '';

// Default field params values
$classes = isset( $field['classes'] ) ? $field['classes'] : '';
$search_text = isset( $field['search_text'] ) ? $field['search_text'] : us_translate_x( 'Search &hellip;', 'placeholder' );
$multiple = isset( $field['is_multiple'] ) ? $field['is_multiple'] : FALSE;
$sortable = isset( $field['is_sortable'] ) ? $field['is_sortable'] : FALSE;
$value_separator = isset( $field['value_separator'] ) ? $field['value_separator'] : ',';
$options = isset( $field['options'] ) ? $field['options'] : array();
$ajax_data = isset( $field['ajax_data'] ) ? (array)$field['ajax_data'] : array();

// If the site uses translations, then we will transfer the current language
if ( has_filter( 'us_tr_current_language' ) ) {
	$ajax_data['lang'] = apply_filters( 'us_tr_current_language', NULL );
}

// Export settings
$json_data = array(
	'multiple' => $multiple,
	'no_results_found' => us_translate( 'No results found.' ),
	'value_separator' => $value_separator,
	'sortable' => $sortable,
);
if ( ! empty( $ajax_data ) ) {
	$json_data['ajax_data'] = $ajax_data;
}

/**
 * Create options list.
 *
 * @param array $options The options.
 * @param int $level
 * @return string
 */
$func_create_options_list = function ( $options ) use ( &$func_create_options_list ) {
	$output = '';
	foreach ( $options as $value => $name ) {
		if ( is_array( $name ) ) {
			$output .= '<div class="usof-autocomplete-list-group" data-group="' . esc_attr( $value ) . '">';
			$output .= $func_create_options_list( $name );
			$output .= '</div>';
		} else {
			$atts = array(
				'data-value' => $value,
				'data-text' => strtolower( strip_tags( $name ) ),
				'tabindex' => '3',
			);
			$output .= '<div' . us_implode_atts( $atts ) . '>' . $name . '</div>';
		}
	}

	return $output;
};

// Input atts
$input_atts = array(
	'type' => 'hidden',
	'class' => 'usof-autocomplete-value ' . esc_attr( $classes ),
	// remove unwanted spaces, so value is passed to the input tag without errors
	'value' => str_replace( $value_separator . ' ', $value_separator, $value ),
);

// Field for editing in WPBakery
// Via the `wpb_vc_param_value` class WPBakery receives the final value
if ( isset( $field['us_vc_field'] ) ) {
	$input_atts['name'] = $name;
	$input_atts['class'] .= ' wpb_vc_param_value';
}

$container_atts = array(
	'class' => 'usof-autocomplete',
	'onclick' => us_pass_data_to_js( $json_data, /* onclick */FALSE )
);
if ( $multiple ) {
	$container_atts['class'] .= ' multiple';
}

// Output HTML
$output = '';
if ( ! empty( $field['preview_text'] ) ) {
	$output .= us_load_template( 'usof/templates/fields/preview_text', $field['preview_text'] );
}
$output .= '<div' . us_implode_atts( $container_atts ) . '>';
$output .= '<input' . us_implode_atts( $input_atts ) . '>';
$output .= '<div class="usof-autocomplete-options"></div>';
$output .= '<div class="usof-autocomplete-toggle">';
$output .= '<input type="text" autocomplete="off" placeholder="' . esc_attr( $search_text ) . '" tabindex="2">';
$output .= '<div class="usof-autocomplete-list">' . $func_create_options_list( $options ) . '</div>';
$output .= '<div class="usof-autocomplete-message hidden"></div>';
$output .= '</div>'; // .usof-autocomplete-list

// Note: js_composer compatibility fixes
$output .= '<div style="display: none;">';
ob_start();
$output .= ob_get_clean();

$output .= '</div>'; // usof-autocomplete-toggle
$output .= '</div>'; // .usof-autocomplete

echo $output;
