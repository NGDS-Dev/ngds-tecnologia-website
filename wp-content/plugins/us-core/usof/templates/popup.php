<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Popup for fields.
 *
 * @var $popup_id string The unique popup ID
 * @var $popup_content string The popup ccontent
 * @var $popup_group_buttons array Groups of buttons for selecting dynamic values
 */

// Get popup id
if ( ! isset( $popup_id ) OR empty( $popup_id ) ) {
	$popup_id = us_uniqid( /* length */6 );
}

// Get popup content
if ( ! isset( $popup_content ) ) {
	$popup_content = '';
}

// Buttons for selecting dynamic values
if ( ! empty( $popup_group_buttons ) AND is_array( $popup_group_buttons ) ) {

	// Remove unneeded ACF types
	if ( isset( $popup_group_buttons['acf_types'] ) ) {
		unset( $popup_group_buttons['acf_types'] );
	}

	foreach( $popup_group_buttons as $group_name => $buttons ) {
		if ( empty( $buttons ) ) {
			continue;
		}

		// Predefined Group Names
		$predefined_group_names = array(
			'global' => __( 'Global Values', 'us' ),
			'term' => __( 'Term Data', 'us' ),
			'post' => __( 'Post Data', 'us' ),
			'media' => us_translate( 'Media File' ),
			'user' => __( 'User Data', 'us' ),
		);

		// Swap the slug to the predefined name if exists
		if ( isset( $predefined_group_names[ $group_name ] ) ) {
			$group_name = $predefined_group_names[ $group_name ];
			$is_predefined_group = TRUE;
		} else {
			$is_predefined_group = FALSE;
		}

		$popup_content .= '<div class="usof-popup-group">';
		$popup_content .= '<div class="usof-popup-group-title">' . strip_tags( $group_name ) . '</div>';
		$popup_content .= '<div class="usof-popup-group-values">';
		foreach ( $buttons as $value => $label ) {
			$button_atts = array(
				'class' => 'usof-popup-group-value',
				'data-dynamic-value' => $value,
				'data-dynamic-label' => $is_predefined_group ? $label : sprintf( '%s: %s', $group_name, $label ),
			);
			$popup_content .= '<button' . us_implode_atts( $button_atts ) . '>';
			$popup_content .= strip_tags( $label );
			$popup_content .= '</button>';
		}
		$popup_content .= '</div>'; // .usof-popup-group-values
		$popup_content .= '</div>'; // .usof-popup-group
	}
} else {
	$popup_content .= '<div class="usof-popup-no-results">' . __( 'No relevant custom fields found.', 'us' ) . '</div>';
}

// Output popup
$output ='<div class="usof-popup" data-popup-id="' . esc_attr( $popup_id ) . '">';

// Popup header
$output .= '<div class="usof-popup-header">';
$output .= '<div class="usof-popup-header-title">' . strip_tags( __( 'Select Dynamic Value', 'us' ) ) . '</div>';
$output .= '<button class="usof-popup-close ui-icon_close" title="' . esc_attr( us_translate( 'Close' ) ) . '"></button>';
$output .= '</div>'; // .usof-popup-header

// Popup body
$output .= '<div class="usof-popup-body">' . $popup_content . '</div>';

$output .= '<div class="usof-preloader"></div>';
$output .= '</div>'; // .usof-popup

echo $output;
