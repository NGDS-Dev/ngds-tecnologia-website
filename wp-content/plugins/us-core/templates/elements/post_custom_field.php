<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Post Custom Field element
 *
 * @var $classes string
 * @var $id string
 */

$value = $_field_label = '';
$acf_format = TRUE;

// Set the field type for specific meta keys
$image_fields = array( 'us_tile_additional_image' );
$repeater_fields = array();
if ( function_exists( 'us_acf_get_fields_keys' ) ) {
	$image_fields = array_merge( $image_fields, (array) us_acf_get_fields_keys( /* type */'image' ) );
	$repeater_fields = (array) us_acf_get_fields_keys( /* types */array( 'repeater', 'flexible_content' ) );
}

if ( in_array( $key, $image_fields ) ) {
	$type = 'image';

	// Disable the ACF "Return Format" for image types (will return the ID)
	$acf_format = FALSE;

} elseif ( in_array( $key, $repeater_fields ) ) {
	$type = 'repeater';
} elseif ( in_array( $key, array( 'us_tile_icon', 'us_testimonial_rating' ) ) ) {
	$type = 'icon';
} else {
	$type = 'text';
}

// Use the custom field name if set
if ( $key == 'custom' ) {
	$_field_label = __( 'Custom Field', 'us' );
	$key = $custom_key;

	// Disable the ACF "Return Format" for custom values to exclude extra checks of ACF functions
	$acf_format = FALSE;
}

// First use the dummy data, if this element is shown in the Edit Template preview
if ( usb_is_template_preview() AND $us_elm_context == 'shortcode' ) {

	// For image type always show a placeholder
	if ( $type === 'image' ) {
		$value = us_get_img_placeholder();

		// If dummy data exists, show it
	} elseif ( $dummy_value = us_config( 'elements/post_custom_field.usb_preview_dummy_data.' . $key, '' ) ) {
		$value = $dummy_value;

		// In other cases show the field title and name itself
	} else {

		// Get the Field Name from the config
		if ( empty( $_field_label ) ) {
			$_field_options = us_config( 'elements/post_custom_field.params.key.options', array() );
			foreach ( $_field_options as $_field_group ) {
				if ( is_array( $_field_group ) AND isset( $_field_group[ $key ] ) ) {
					$_field_label = $_field_group[ $key ];
					break;
				}
			}
		}

		$value = $_field_label . ' <small>(' . $key . ')</small>';
	}

	// In case it's not Edit Template preview, just get the value on provided custom field name
} else {
	$value = us_get_custom_field( $key, $acf_format );
}

// Add <p> and <br> if the text value has 'End Of Line' symbols
if (
	$type === 'text'
	AND $tag === 'div' // excludes non-valid HTML code like <p> inside <p>
	AND is_string( $value )
	AND strpos( $value, PHP_EOL ) !== FALSE
) {
	$value = wpautop( $value );
}

// At this point the $value can contain an array, so we need to transform it to a string
if ( is_array( $value ) ) {

	// For ACF Repeater field generate the relevant HTML value
	if ( $type === 'repeater' ) {
		$_value_html = '<div class="repeater">';

		foreach ( $value as $_repeater_fields ) {
			$_value_html .= '<div class="repeater-row">';

			foreach ( (array) $_repeater_fields as $_repeater_field_name => $_repeater_field_value ) {
				// Skip flex_content layout name
				if ( $_repeater_field_name === 'acf_fc_layout' ) {
					continue;
				}

				$_value_html .= '<div class="repeater-field ' . esc_attr( $_repeater_field_name ) . '">';

				// Get the string and numeric values only
				if ( is_string( $_repeater_field_value ) OR is_numeric( $_repeater_field_value ) ) {
					$_value_html .= $_repeater_field_value;

					// Get the image from the ID
				} elseif ( $_img_id = us_arr_path( $_repeater_field_value, 'ID' ) ) {
					$_value_html .= wp_get_attachment_image( $_img_id, $thumbnail_size );
				}

				$_value_html .= '</div> ';
			}

			$_value_html .= '</div>';
		}

		$_value_html .= '</div>';
		$value = $_value_html;

		// In other cases try to get a string value
	} else {

		// If array contain arrays or objects inside, output specified notification
		if ( array_filter( $value, 'is_array' ) OR array_filter( $value, 'is_object' ) ) {
			$value = 'Unsupported format';

			// in other cases separate values by comma
		} else {
			$value = implode( ', ', $value );
		}
	}
}

// In case the value is an object output specified notification
if ( is_object( $value ) ) {
	$value = 'Unsupported format';
}

// Don't output the element, when its value is empty
if (
	! usb_is_post_preview()
	AND $hide_empty
	AND (
		$value === ''
		OR $value === FALSE
		OR $value === NULL
	)
) {
	return;
}

// CSS classes & ID
$_atts['class'] = 'w-post-elm post_custom_field';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['class'] .= ' type_' . $type;
$_atts['class'] .= ' ' . $key;
if ( $color_link ) {
	$_atts['class'] .= ' color_link_inherit';
}
if ( $display_type ) {
	$_atts['class'] .= ' display_table';
}

// When some values are set in Design Options, add the specific class
if ( us_design_options_has_property( $css, 'border-radius' ) ) {
	$_atts['class'] .= ' has_border_radius';
}
if ( us_design_options_has_property( $css, array( 'height', 'max-height' ) ) ) {
	$_atts['class'] .= ' has_height';
}
if ( ! empty( $el_id ) AND $us_elm_context == 'shortcode' ) {
	$_atts['id'] = $el_id;
}

// Generate icon specific HTML
if ( $type == 'icon' ) {

	// Generate specific HTML for the Testimonial Rating
	if ( $key == 'us_testimonial_rating' ) {
		$rating_value = (int) strip_tags( (string) $value );

		if ( $rating_value === 0 ) {
			return;

		} else {
			$value = '<div class="w-testimonial-rating">';
			for ( $i = 1; $i <= $rating_value; $i ++ ) {
				$value .= '<i></i>';
			}
			$value .= '</div>';
		}

	} else {
		$value = us_prepare_icon_tag( $value );
	}
}

// Generate specific Image HTML
$ratio_helper_html = $value_image_ID = '';
if ( $type === 'image' ) {

	// Overwrite image size from Grid/List elements, if set
	global $us_grid_img_size;
	if ( $us_grid_img_size ) {
		$thumbnail_size = $us_grid_img_size;
	}

	// Remember image ID for further conditions
	$value_image_ID = $value;

	// Get image by ID
	$img_loading_attr = array();
	if ( $disable_lazy_loading ) {
		$img_loading_attr['loading'] = FALSE;
	}
	$value = wp_get_attachment_image( $value_image_ID, $thumbnail_size, FALSE, $img_loading_attr );

	// If there is no image, display the placeholder
	if ( empty( $value ) ) {
		$value = us_get_img_placeholder( $thumbnail_size );
	}

	// Set Aspect Ratio values
	if ( $has_ratio ) {
		$ratio_array = us_get_aspect_ratio_values( $ratio, $ratio_width, $ratio_height );
		$ratio_helper_html = '<div style="padding-bottom:' . round( $ratio_array[1] / $ratio_array[0] * 100, 4 ) . '%"></div>';
		$_atts['class'] .= ' has_ratio';
	} elseif ( $stretch ) {
		$_atts['class'] .= ' stretched';
	}
}

// Text before/after values
$text_before = trim( strip_tags( $text_before, '<br><sup><sub>' ) );
$text_after = trim( strip_tags( $text_after, '<br><sup><sub>' ) );

// Force <span> tags if the parent HTML tag is not <div>
$text_before_tag = ( $tag == 'div' ) ? $text_before_tag : 'span';
$text_after_tag = ( $tag == 'div' ) ? $text_after_tag : 'span';

if ( $text_before !== '' ) {
	$text_before_html = sprintf( '<%s class="w-post-elm-before">%s </%s>', $text_before_tag, $text_before, $text_before_tag );
} else {
	$text_before_html = '';
}
if ( $text_after !== '' ) {
	$text_after_html = sprintf( '<%s class="w-post-elm-after"> %s</%s>', $text_after_tag, $text_after, $text_after_tag );
} else {
	$text_after_html = '';
}

// Reset the link for Repeater type of field
if ( $type === 'repeater' ) {
	$link = ''; 
}

// Link
$link_atts = us_generate_link_atts( $link, /* additional data */array( 'label' => (string) $value, 'img_id' => $value_image_ID ) );
$link_html = '';
if ( ! empty( $link_atts['href'] ) ) {
	$link_html = '<a' . us_implode_atts( $link_atts ) . '>';
} else {

	// Do not output the element with empty link, if set
	if (
		$hide_with_empty_link
		AND ! usb_is_post_preview()
	) {
		return;
	}
}

// Output the element
$output = '<' . $tag . us_implode_atts( $_atts ) . '>';
if ( ! empty( $icon ) ) {
	$output .= us_prepare_icon_tag( $icon );
}
$output .= $text_before_html;
$output .= $link_html;
$output .= $ratio_helper_html;

// Wrap the value into additional <span>, if it doesn't have a <div> or <p>
if (
	$type === 'text'
	AND	strpos( (string) $value, '<div' ) === FALSE
	AND strpos( (string) $value, '<p' ) === FALSE
) {
	$output .= '<span class="w-post-elm-value">' . $value . '</span>';
} else {
	$output .= $value;
}

if ( ! empty( $link_atts['href'] ) ) {
	$output .= '</a>';
}
$output .= $text_after_html;
$output .= '</' . $tag . '>';

echo $output;
