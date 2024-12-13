<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Group
 *
 * Grouped options
 *
 * @var   $field array Group options
 * @var   $params_values array Group values
 *
 */

global $usof_options;

$output = '<div class="usof-form-group-item">';
$output .= '<style></style>';
$group_content_styles = '';

// Output group title block, if "is_accordion" is set
if ( ! empty( $field['is_accordion'] ) ) {
	$group_content_styles = ' style="display:none;"';

	$accordion_title = ! empty( $field['accordion_title'] ) ? $field['accordion_title'] : '';
	foreach ( $field['params'] as $param_name => $param ) {
		if ( strpos( $accordion_title, $param_name ) !== FALSE ) {
			$param_value = isset( $params_values[ $param_name ] ) ? $params_values[ $param_name ] : $field['params'][ $param_name ]['std'];
			if ( $param['type'] == 'select' AND ! empty( $param['options'][ $param_value ] ) ) {
				$param_value = $param['options'][ $param_value ];
			}
			$param_value = esc_attr( trim( (string) $param_value ) );
			$accordion_title = str_replace( $param_name, $param_value, $accordion_title );
		}
	}

	$output .= '<div class="usof-form-group-item-title">';

	// Output Button preview, if preview attribute is set as "button"
	if ( isset( $field['preview'] ) AND $field['preview'] == 'button' ) {

		// Show the button class
		$_btn_class = $_btn_extra_class = '';
		if ( isset( $params_values['id'] ) ) {
			$_btn_class = 'us-btn-style_' . $params_values['id'];
		}
		if ( ! empty( $params_values['class'] ) ) {
			$_btn_extra_class = esc_attr( $params_values['class'] );
		}
		$output .= '<div class="usof-btn-class"><span class="usof-btn-class-main">' . $_btn_class . '</span> <span class="usof-btn-class-extra">' . $_btn_extra_class . '</span></div>';

		$output .= '<div class="usof-btn-preview hov_fade">';
		$output .= '<div class="usof-btn"><span class="usof-btn-label">' . strip_tags( $accordion_title ) . '</span></div>';
		$output .= '</div>';
	} else {
		$output .= strip_tags( $accordion_title );
	}
	$output .= '</div>';

} elseif ( isset( $field['preview'] ) AND $field['preview'] == 'input_fields' ) {

	// Output Input Fields preview, if preview attribute is set as "input_fields"
	$output .= '<div class="usof-input-preview" style="background: ' . $usof_options['color_content_bg'] . '">';
	$output .= '<input class="usof-input-preview-elm" type="text"';
	$output .= ' value="' . esc_attr( us_translate( 'Text' ) . ' ' . __( '(single line)', 'us' ) ) . '"';
	$output .= ' placeholder="' . esc_attr( us_translate( 'Text' ) . ' ' . __( '(single line)', 'us' ) ) . '">';
	$output .= '<div class="usof-input-preview-select">';
	$output .= '<select class="usof-input-preview-elm">';
	$output .= '<option>' . __( 'Dropdown', 'us' ) . '</option>';
	$output .= '<option>' . __( 'Dropdown', 'us' ) . ' 2</option>';
	$output .= '<option>' . __( 'Dropdown', 'us' ) . ' 3</option>';
	$output .= '</select>';
	$output .= '</div></div>';
}

// Output group content block
$output .= '<div class="usof-form-group-item-content"' . $group_content_styles . '>';
ob_start();
foreach ( $field['params'] as $param_name => $param ) {
	us_load_template(
		'usof/templates/field', array(
			'name' => $param_name,
			'id' => 'usof_' . $param_name,
			'field' => $param,
			'values' => $params_values,
			'context' => $context,
		)
	);
}
$output .= ob_get_clean();
$output .= '</div>';

// Output controls, if set
if ( ! empty( $field['show_controls'] ) ) {
	$output .= '<div class="usof-form-group-item-controls">';

	// Show "Move" button, if "is_sortable" is set
	if ( ! empty( $field['is_sortable'] ) ) {
		$output .= '<div class="ui-icon_move" title="' . us_translate( 'Move' ) . '"></div>';
	}

	// Show "Duplicate" button, if "is_duplicate" is set
	if ( ! empty( $field['is_duplicate'] ) ) {
		$output .= '<div class="ui-icon_duplicate" title="' . __( 'Duplicate', 'us' ) . '"></div>';
	}
	$output .= '<div class="ui-icon_delete" title="' . us_translate( 'Delete' ) . '"></div>';
	$output .= '</div>';
}

$output .= '</div>';

echo $output;
