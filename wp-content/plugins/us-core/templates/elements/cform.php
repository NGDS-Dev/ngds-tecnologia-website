<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_cform
 *
 * Dev note: if you want to change some of the default values or acceptable attributes, overload the shortcodes config.
 *
 * @var   $shortcode          string Current shortcode name
 * @var   $shortcode_base     string The original called shortcode name (differs if called an alias)
 *
 * @param $receiver_email     string Receiver Email
 * @param $name_field         string Name field state: 'required' / 'shown' / 'hidden'
 * @param $email_field        string Email field state: 'required' / 'shown' / 'hidden'
 * @param $phone_field        string Phone field state: 'required' / 'shown' / 'hidden'
 * @param $message_field      string Message field state: 'required' / 'shown' / 'hidden'
 * @param $captcha_field      string Message field state: 'hidden' / 'required'
 * @param $checkbox_field     string Checkbox field state: 'hidden' / 'required'
 * @param $button_color       string Button color: 'primary' / 'secondary' / 'light' / 'contrast' / 'black' / 'white'
 * @param $button_bg_color    string Button background color
 * @param $button_text_color  string Button text color
 * @param $button_style       string Button style: 'raised' / 'flat'
 * @param $button_size        string Button size
 * @param $button_align       string Button alignment: 'left' / 'center' / 'right'
 * @param $button_text        string Button text
 * @param $icon               string Icon name
 * @param $iconpos            string Icon Position: 'left' / 'right'
 * @param $el_class           string Extra class name
 * @var   $classes            string Extend class names
 */

global $us_cform_index, $us_cform_prev_post_id, $us_page_block_ids;

if ( ! empty( $us_page_block_ids ) ) {
	$post_id = $us_page_block_ids[0];
} else {
	$post_id = get_queried_object_ID();
}

if ( ! empty( $us_cform_prev_post_id ) AND $us_cform_prev_post_id != $post_id ) {
	$us_cform_index = 0;
}

// Form indexes start from 1
$us_cform_index = ! empty( $us_cform_index ) ? ( $us_cform_index + 1 ) : 1;
$us_cform_prev_post_id = $post_id;

// Set button alignment classes
$submit_class = us_get_class_by_responsive_values( $button_align, /* template */'align_%s' );

$classes = isset( $classes ) ? $classes : '';
$classes .= ' layout_' . $fields_layout;

$el_id = isset( $el_id ) ? $el_id : '';

// Generate fields params
if ( is_string( $items ) ) {
	$fields = json_decode( urldecode( $items ), TRUE );
	if ( ! is_array( $fields ) ) {
		$fields = array();
	}
} else {
	$fields = $items;
}

// Add the needed hidden fields
$fields[] = array(
	'type' => 'hidden',
	'label' => 'action',
	'value' => 'us_ajax_cform',
);

$fields[] = array(
	'type' => 'hidden',
	'label' => 'post_id',
	'value' => $post_id,
);
$fields[] = array(
	'type' => 'hidden',
	'label' => 'form_index',
	'value' => $us_cform_index,
);

// Determine type and ID current page
if ( $queried_object = get_queried_object() ) {
	$queried_object_id = get_queried_object_id();
	$queried_object_type = 'post';

	if ( $queried_object instanceof WP_Term ) {
		$queried_object_type = 'term';
	} elseif ( $queried_object instanceof WP_User ) {
		$queried_object_type = 'author';
	} elseif ( $queried_object instanceof WP_Post_Type ) {
		$queried_object_type = 'post_type';
		$queried_object_id = $queried_object->name;
	}

	$fields[] = array(
		'type' => 'hidden',
		'label' => 'queried_object_id',
		'value' => $queried_object_id,
	);
	$fields[] = array(
		'type' => 'hidden',
		'label' => 'queried_object_type',
		'value' => $queried_object_type,
	);
}

// Submit button
$submit_params = array(
	'type' => 'submit',
	'class' => $submit_class,
	'icon' => ( ! empty( $icon ) ) ? $icon : '',
	'icon_pos' => ( ! empty( $iconpos ) ) ? $iconpos : 'left',
	'btn_classes' => ( ! empty( $iconpos ) AND ! empty( $icon ) ) ? 'icon_at' . $iconpos : '',
	'btn_inner_css' => ( ! empty( $button_size ) ) ? 'font-size:' . $button_size . ';' : '',
	'btn_size_mobiles' => ( ! empty( $button_size_mobiles ) ) ? $button_size_mobiles : '',
	'title' => ( ! empty( $button_text ) ) ? $button_text : us_config( 'elements/cform.params.button_text.std' ),
);

$submit_params['btn_classes'] .= ' ' . us_get_btn_class( $button_style );
$fields[] = $submit_params;

// Load form template
us_load_template(
	'templates/form/form', array(
		'type' => 'cform',
		'fields' => $fields,
		'fields_gap' => $fields_gap,
		'_atts' => isset( $_atts ) ? $_atts : array(),
		'classes' => $classes,
		'el_id' => $el_id,
	)
);
