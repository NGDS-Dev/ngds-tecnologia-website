<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: post_custom_field
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );
$hover_options_params = us_config( 'elements_hover_options' );

// Predefined Custom Fields, used in the theme built-in elements
$us_custom_fields = array(
	__( 'Additional Settings', 'us' ) => array(
		'us_tile_additional_image' => us_translate( 'Images' ),
		'us_tile_icon' => __( 'Icon', 'us' ),
	),
);
if ( us_get_option( 'enable_testimonials', 1 ) ) {
	$us_custom_fields = array_merge(
		$us_custom_fields,
		array(
			__( 'Testimonial', 'us' ) => array(
				'us_testimonial_author' =>  __( 'Author Name', 'us' ),
				'us_testimonial_role' => __( 'Author Role', 'us' ),
				'us_testimonial_company' => __( 'Author Company', 'us' ),
				'us_testimonial_rating' => __( 'Rating', 'us' ),
			),
		)
	);
}

// Defined image types for show_if conditions
$image_fields = array( 'us_tile_additional_image' );
$repeater_fields = array( '' ); // empty string is needed for correct "show_if" execution

// Get options from "Advanced Custom Fields" plugin
$acf_custom_fields = array();
if ( function_exists( 'us_acf_get_fields' ) ) {
	$exclude_types = array(
		'clone',
		'file',
		'gallery',
		'google_map',
		'group',
		'link',
		'message',
		'post_object',
		'relationship',
		'tab',
		'taxonomy',
		'true_false',
		'user',
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

			// Exclude specific ACF types, which are not supported by the Post Custom Field
			if ( ! in_array( $field['type'], $exclude_types ) ) {
				$acf_custom_fields[ $group_id ][ $field['name'] ] = $field['label'];
			}

			// Add Image types for show_if conditions
			if ( $field['type'] == 'image' ) {
				$image_fields[] = $field['name'];
			}

			// Add Repeater types for show_if conditions
			if ( in_array( $field['type'], array( 'repeater', 'flexible_content' ) ) ) {
				$repeater_fields[] = $field['name'];
			}
		}

		// Add a group label to the overall result
		if ( $group_label AND ! empty( $acf_custom_fields[ $group_id ] ) ) {
			$acf_custom_fields[ $group_id ]['__group_label__'] = $group_label;
		}
	}
}

/**
 * @return array
 */
return array(
	'title' => __( 'Post Custom Field', 'us' ),
	'category' => __( 'Post Elements', 'us' ),
	'icon' => 'fas fa-cog',
	'params' => us_set_params_weight(

		// General section
		array(
			'key' => array(
				'title' => us_translate( 'Show' ),
				'type' => 'select',
				'options' => array_merge(
					$us_custom_fields,
					$acf_custom_fields,
					array( 'custom' => __( 'Custom Field', 'us' ) )
				),
				'std' => 'us_tile_additional_image',
				'admin_label' => TRUE,
				'usb_preview' => TRUE,
			),
			'custom_key' => array(
				'placeholder' => 'custom_field_name',
				'description' => __( 'Enter a custom field name to get its value.', 'us' ),
				'type' => 'text',
				'std' => '',
				'admin_label' => TRUE,
				'classes' => 'for_above',
				'show_if' => array( 'key', '=', 'custom' ),
				'usb_preview' => TRUE,
			),
			'display_type' => array(
				'switch_text' => __( 'Show as table', 'us' ),
				'type' => 'switch',
				'std' => 0,
				'show_if' => array( 'key', '=', $repeater_fields ),
				'usb_preview' => array(
					'toggle_class' => 'display_table',
				),
			),
			'has_ratio' => array(
				'switch_text' => __( 'Set Aspect Ratio', 'us' ),
				'type' => 'switch',
				'std' => 0,
				'show_if' => array( 'key', '=', $image_fields ),
				'usb_preview' => TRUE,
			),
			'ratio' => array(
				'type' => 'select',
				'options' => array(
					'1x1' => '1x1 ' . __( 'square', 'us' ),
					'4x3' => '4x3 ' . __( 'landscape', 'us' ),
					'3x2' => '3x2 ' . __( 'landscape', 'us' ),
					'16x9' => '16:9 ' . __( 'landscape', 'us' ),
					'2x3' => '2x3 ' . __( 'portrait', 'us' ),
					'3x4' => '3x4 ' . __( 'portrait', 'us' ),
					'custom' => __( 'Custom', 'us' ),
				),
				'std' => '1x1',
				'classes' => 'for_above',
				'show_if' => array( 'has_ratio', '=', 1 ),
				'usb_preview' => TRUE,
			),
			'ratio_width' => array(
				'placeholder' => us_translate( 'Width' ),
				'type' => 'text',
				'std' => '21',
				'cols' => 2,
				'classes' => 'for_above',
				'show_if' => array( 'ratio', '=', 'custom' ),
				'usb_preview' => TRUE,
			),
			'ratio_height' => array(
				'placeholder' => us_translate( 'Height' ),
				'type' => 'text',
				'std' => '9',
				'cols' => 2,
				'classes' => 'for_above',
				'show_if' => array( 'ratio', '=', 'custom' ),
				'usb_preview' => TRUE,
			),
			'stretch' => array(
				'type' => 'switch',
				'switch_text' => __( 'Stretch the image to the container width', 'us' ),
				'std' => 0,
				'show_if' => array( 'has_ratio', '=', 0 ),
				'usb_preview' => array(
					'toggle_class' => 'stretched',
				),
			),
			'disable_lazy_loading' => array(
				'switch_text' => __( 'Disable Lazy Loading', 'us' ),
				'type' => 'switch',
				'std' => 0,
				'show_if' => array( 'key', '=', $image_fields ),
			),
			'thumbnail_size' => array(
				'title' => __( 'Image Size', 'us' ),
				'description' => $misc['desc_img_sizes'],
				'type' => 'select',
				'options' => us_get_image_sizes_list(),
				'std' => 'large',
				'show_if' => array( 'key', '=', $image_fields + $repeater_fields ),
				'usb_preview' => TRUE,
			),
			'hide_empty' => array(
				'type' => 'switch',
				'switch_text' => __( 'Hide this element if its value is empty', 'us' ),
				'std' => 0,
				'show_if' => array( 'key', '!=', 'us_testimonial_rating' ),
			),
			'link' => array(
				'title' => us_translate( 'Link' ),
				'type' => 'link',
				'dynamic_values' => array(
					'global' => array(
						'homepage' => us_translate( 'Homepage' ),
						'elm_value' => __( 'Clickable value (email, phone, website)', 'us' ),
						'popup_image' => __( 'Open Image in a Popup', 'us' ),
					),
				),
				'std' => '{"url":""}',
				'show_if' => array( 'key', '!=', $repeater_fields ),
				'usb_preview' => TRUE,
			),
			'hide_with_empty_link' => array(
				'type' => 'switch',
				'switch_text' => __( 'Hide this element if there is no link', 'us' ),
				'std' => 0,
				'classes' => 'for_above',
				'show_if' => array( 'link', 'str_contains', 'custom_field' ),
			),
			'color_link' => array(
				'title' => __( 'Link Color', 'us' ),
				'type' => 'switch',
				'switch_text' => __( 'Inherit from text color', 'us' ),
				'std' => 1,
				'show_if' => array( 'key', '!=', $image_fields + $repeater_fields ),
				'usb_preview' => array(
					'toggle_class' => 'color_link_inherit',
				),
			),
			'tag' => array(
				'title' => __( 'HTML tag', 'us' ),
				'type' => 'select',
				'options' => $misc['html_tag_values'],
				'std' => 'div',
				'show_if' => array( 'key', '!=', $image_fields ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => array(
					'attr' => 'tag',
				),
			),
			'icon' => array(
				'title' => __( 'Icon', 'us' ),
				'type' => 'icon',
				'std' => '',
				'show_if' => array( 'key', '!=', array( 'us_testimonial_rating', 'us_tile_icon' ) ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'text_before' => array(
				'title' => __( 'Text before value', 'us' ),
				'type' => 'text',
				'std' => '',
				'admin_label' => TRUE,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'text_before_tag' => array(
				'description' => __( 'HTML tag', 'us' ),
				'type' => 'select',
				'options' => $misc['html_tag_values'],
				'std' => 'span',
				'classes' => 'for_above',
				'show_if' => array( 'tag', '=', 'div' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => array(
					'elm' => '.w-post-elm-before',
					'attr' => 'tag',
				),
			),
			'text_after' => array(
				'title' => __( 'Text after value', 'us' ),
				'type' => 'text',
				'std' => '',
				'admin_label' => TRUE,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'text_after_tag' => array(
				'description' => __( 'HTML tag', 'us' ),
				'type' => 'select',
				'options' => $misc['html_tag_values'],
				'std' => 'span',
				'classes' => 'for_above',
				'show_if' => array( 'tag', '=', 'div' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => array(
					'elm' => '.w-post-elm-after',
					'attr' => 'tag',
				),
			),
		),

		$conditional_params,
		$design_options_params,
		$hover_options_params
	),
	'usb_preview_dummy_data' => array(
		'duration' => '10',
		'price' => '$10',
		'us_testimonial_rating' => '4',
		'us_tile_icon' => 'fas|star'
	),

	// Not used params, required for correct fallback
	'fallback_params' => array(
		'custom_link',
		'link_new_tab',
		'onclick_code',
	),
);
