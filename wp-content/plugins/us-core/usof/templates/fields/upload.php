<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Upload
 *
 * Upload some file with the specified settings.
 *
 * @param $field ['title'] string Field title
 * @param $field ['description'] string Field title
 * @param $field ['preview_type'] string 'image' / 'text'
 * @param $field ['is_multiple'] bool
 *
 * @var   $field array Field options
 *
 * @var   $name  string Field name
 * @var   $id    string Field ID
 * @var   $value mixed Either full path to the file, or ID from WordPress media uploads
 */

if ( ! isset( $field['preview_type'] ) ) {
	$field['preview_type'] = 'image';
}

if ( ! isset( $field['is_multiple'] ) ) {
	$field['is_multiple'] = FALSE;
}

$is_dynamic_value = ( is_string( $value ) AND strpos( $value, '{{' ) !== FALSE );

$attachments = array();

// Transform the value to attachments array
if ( ! empty( $value ) ) {
	$img_size = $field['is_multiple'] ? 'thumbnail' : 'medium';
	$attachments_ids = explode( ',', $value );
	foreach ( $attachments_ids as $attachment_id ) {

		// For dynamic values
		if ( $is_dynamic_value ) {
			$attachments[] = array( 'id' => $attachment_id, 'url' => '' );

			// For image preview type get url to the thumbnail of an image
		} elseif ( $field['preview_type'] == 'image' ) {
			if ( $url = wp_get_attachment_image_url( $attachment_id, $img_size ) ) {
				$attachments[] = array( 'id' => $attachment_id, 'url' => $url );
			} elseif ( count( $attachments ) == 1 ) {
				// Fallback for value as single image URL
				$attachments[] = array( 'id' => -1, 'url' => $attachment_id );
			}

			// For other cases get url to the attachment itself
		} elseif ( $url = wp_get_attachment_url( $attachment_id ) ) {
			$attachments[] = array( 'id' => $attachment_id, 'url' => $url );
		}
	}
}

// Attributes for main container
$main_atts = array(
	'class' => 'usof-upload preview_' . ( $is_dynamic_value ? 'dynamic_value' : $field['preview_type'] ),
	'data-preview-type' => $field['preview_type'],
);
$main_atts['class'] .= $field['is_multiple'] ? ' is_multiple' : ' is_single';

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

// Output the field
$output = '<div ' . us_implode_atts( $main_atts ) . '>';
$output .= '<input ' . us_implode_atts( $hidden_atts ) . '>';

// Keep default image in a hidden field to show it after clearing value via JS
if ( $field['preview_type'] == 'image' AND ! empty( $field['std'] ) ) {
	$output .= '<input type="hidden" name="placeholder" value="' . $field['std'] . '">';
}

// Get grouped dynamic values
if ( $dynamic_values = us_arr_path( $field, 'dynamic_values' ) ) {

	// Predefined image values
	$predefined_dynamic_values = array(
		'global' => array(
			'{{site_icon}}' => us_translate( 'Site Icon' ),
		),
		'term' => array(),
		'post' => array(
			'{{the_thumbnail}}' => us_translate_x( 'Featured image', 'post' ),
			'{{us_tile_additional_image}}' => sprintf( '%s: %s', __( 'Additional Settings', 'us' ), us_translate( 'Images' ) ),
		),
		'acf_types' => array(
			'image',
			'gallery',
		),
	);

	// Remove single predefined values in case of multiple possible values
	if ( $field['is_multiple'] ) {
		unset( $predefined_dynamic_values['global'] );
		unset( $predefined_dynamic_values['post']['{{the_thumbnail}}'] );
		unset( $predefined_dynamic_values['acf_types'][0] );
	}

	// Append dynamic values from the config if defined
	if ( is_array( $dynamic_values ) ) {
		$predefined_dynamic_values = array_merge( $predefined_dynamic_values, $dynamic_values );
	}

	// Add Product Gallery if WooCommerce is active
	if ( class_exists( 'woocommerce' ) AND $predefined_dynamic_values['post'] ) {
		$predefined_dynamic_values['post']['{{_product_image_gallery}}'] = us_translate( 'Product gallery', 'woocommerce' );
	}
}

// Output previews
$output .= '<div class="usof-upload-preview' . ( ( count( $attachments ) OR ! empty( $field['std'] ) ) ? '' : ' hidden' ) . '">';
if ( count( $attachments ) ) {
	foreach ( $attachments as $attachment ) {
		$output .= '<div class="usof-upload-preview-file" data-value="' . esc_attr( $attachment['id'] ) . '">';

		// For output dynamic values
		if ( $is_dynamic_value ) {
			$output .= '<span>' . (string) us_arr_path( $predefined_dynamic_values, $attachment['id'], $value ) . '</span>';

			// For output img tag
		} elseif ( $field['preview_type'] == 'image' ) {
			$output .= '<img src="' . esc_attr( $attachment['url'] ) . '" alt="" loading="lazy">';

			// For output attachment name
		} elseif ( $field['preview_type'] == 'text' ) {
			$output .= '<span>' . wp_basename( $attachment['url'] ) . '</span>';
		}

		$output .= '<div class="ui-icon_close" title="' . us_translate( 'Delete' ) . '"></div>';
		$output .= '</div>'; // .usof-upload-preview-file
	}

	// If there's no attachments, check if image placeholder is present and output it
} elseif ( ! empty( $field['std'] ) AND $field['preview_type'] == 'image' ) {
	$output .= '<div class="usof-upload-preview-file">';
	$output .= '<img src="' . esc_attr( $field['std'] ) . '" alt="" loading="lazy">';
	$output .= '</div>'; // .usof-upload-preview-file
}
$output .= '</div>'; // .usof-upload-preview

// "Add" button
$output .= '<div class="ui-icon_add"></div>';

// "Select Dynamic Value" button
if ( $dynamic_values ) {
	$popup_id = us_uniqid( /* length */6 );
	$button_atts = array(
		'class' => 'fas fa-database for_select_dynamic_value',
		'data-popup-show' => $popup_id,
		'title' => __( 'Select Dynamic Value', 'us' ),
	);
	$output .= '<button ' . us_implode_atts( $button_atts ) . '></button>';
}

// Internationalization
$i18n = array(
	'delete' => us_translate( 'Delete' ),
);
$output .= '<div class="usof-upload-i18n hidden"' . us_pass_data_to_js( $i18n ) . '></div>';
$output .= '</div>'; // .usof-upload

// Add popup to output
if ( $dynamic_values ) {
	$output .= us_get_template( 'usof/templates/popup', /* popup vars */array(
		'popup_id' => $popup_id,
		'popup_group_buttons' => (array) apply_filters( 'us_image_dynamic_values', $predefined_dynamic_values ),
	) );
}

echo $output;
