<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Link
 *
 * Link settings field
 *
 * @var $name string Field name
 * @var $id string Field ID
 * @var $field array Field options
 * @var $field['dynamic_values'] bool|array TRUE of List of grouped dynamic values
 */

// Field for the main result
$hidden_input_atts = array(
	'name' => $name,
	'type' => 'hidden',
	'value' => $value,
);

// Field for editing in WPBakery
// Via the `wpb_vc_param_value` class WPBakery receives the final value
if ( isset( $field['us_vc_field'] ) ) {
	$hidden_input_atts['class'] = 'wpb_vc_param_value';
}

// Output content
$output = '<div class="usof-link">';
$output .= '<input' . us_implode_atts( $hidden_input_atts ) . '>';
$output .= '<div class="usof-form-input-group">';

// Link input field
$input_atts = array(
	'class' => 'usof-link-input-url js_hidden',
	'data-nonce' => wp_create_nonce( 'usof_search_items_for_link' ),
	'name' => 'url',
	'placeholder' => us_translate( 'Paste URL or type to search' ),
	'type' => 'text',
);
$output .= '<input ' . us_implode_atts( $input_atts ) . '>';

// Hidden template for dynamic value indication
if ( $dynamic_values = us_arr_path( $field, 'dynamic_values' ) ) {
	$popup_id = us_uniqid( /* length */6 );

	$output .= '<div class="usof-form-input-dynamic-value hidden" data-popup-show="' . esc_attr( $popup_id ) . '">';
	$output .= '<span class="usof-form-input-dynamic-value-title"></span>';
	$output .= '<button type="button" class="action_remove_dynamic_value ui-icon_close" title="' . esc_attr( us_translate( 'Remove' ) ) . '"></button>';
	$output .= '</div>'; // .usof-form-input-dynamic-value
}

$output .= '<div class="usof-form-input-group-controls">';
$output .= '<button class="action_toggle_menu fas fa-cog" title="' . esc_attr( us_translate( 'Link options' ) ) . '"></button>';

// Attributes for dynamic data button
if ( $dynamic_values ) {
	$show_button_atts = array(
		'class' => 'fas fa-database',
		'data-popup-show' => $popup_id,
		'title' => __( 'Select Dynamic Value', 'us' ),
	);
	$output .= '<button' . us_implode_atts( $show_button_atts ) . '></button>';
}
$output .= '</div>'; // .usof-form-input-group-controls
$output .= '</div>'; // .usof-link-input

// Link Posts search
$output .= '<div class="usof-link-search-results hidden">';
$output .= '<div class="usof-link-search-message hidden"></div>';
$output .= '</div>';

// Link attributes settings
$output .= '<div class="usof-link-attributes">';

// Target attribute
$output .= '<div class="usof-checkbox"><label>';
$output .= '<input type="checkbox" name="target" value="_blank">' . strip_tags( us_translate( 'Open link in a new tab' ) );
$output .= '</label></div>';

// Rel attribute
$output .= '<div class="usof-checkbox"><label>';
$output .= '<input type="checkbox" name="rel" value="nofollow">' . strip_tags( __( 'Add "nofollow" attribute' , 'us' ) );
$output .= '</label></div>';

// Title attribute
// Note: To bind a checkbox to a text field, use the prefix '{$checkbox_name}_value' in the field name to enter the value
$output .= '<div class="usof-checkbox"><label>';
$output .= '<input type="checkbox" name="title">' . strip_tags( us_translate( 'Title Attribute' ) );
$output .= '<input type="text" name="title_value" placeholder="' . esc_attr( us_translate( 'Text' ) ) . '">';
$output .= '</label></div>';

// Onclick attribute
// Note: To bind a checkbox to a text field, use the prefix '{$checkbox_name}_value' in the field name to enter the value
$output .= '<div class="usof-checkbox"><label>';
$output .= '<input type="checkbox" name="onclick">' . strip_tags( __( 'Onclick JavaScript event', 'us' ) );
$output .= '<input type="text" name="onclick_value" placeholder="return false">';
$output .= '</label></div>';

$output .= '</div>'; // .usof-link-attributes
$output .= '</div>'; // .usof-link

// Popup
if ( $dynamic_values ) {

	// Predefined link values
	$predefined_dynamic_values = array(
		'global' => array(
			'homepage' => us_translate( 'Homepage' ),
		),
		'term' => array(),
		'post' => array(
			'post' => __( 'Post Link', 'us' ),
			'custom_field|us_tile_link' => sprintf( '%s: %s', __( 'Additional Settings', 'us' ), __( 'Custom Link', 'us' ) ),
			'custom_field|us_testimonial_link' => sprintf( '%s: %s', __( 'Testimonial', 'us' ), __( 'Author Link', 'us' ) ),
		),
		'media' => array(),
		'user' => array(
			'author_page' => __( 'Author Archive', 'us' ),
			'author_website' => __( 'User Website (if set)', 'us' ),
		),
		'acf_types' => array(
			'email',
			'file',
			'link',
			'page_link',
			'post_object',
			'url',
		),
	);

	// Append dynamic values from the config if defined
	if ( is_array( $dynamic_values ) ) {
		$predefined_dynamic_values = array_merge( $predefined_dynamic_values, $dynamic_values );
	}

	// Add Testimonial author link, if Testimonials are enabled
	if ( us_get_option( 'enable_testimonials', 1 ) AND $predefined_dynamic_values['post'] ) {
		$predefined_dynamic_values['post']['custom_field|us_testimonial_link'] = sprintf( '%s: %s', __( 'Testimonial', 'us' ), __( 'Author Link', 'us' ) );
	}

	// Add popup to output
	$output .= us_get_template( 'usof/templates/popup', /* popup vars */array(
		'popup_id' => $popup_id,
		'popup_group_buttons' => (array) apply_filters( 'us_link_dynamic_values', $predefined_dynamic_values ),
	) );
}

echo $output;
