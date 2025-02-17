<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: grid_builder
 *
 * Advanced header builder.
 *
 * @var $name string Field name
 * @var $id string Field ID
 * @var $field array Field options
 *
 * @var $value array Current value
 */

if ( ! empty( $value ) AND is_string( $value ) AND $value[0] === '{' ) {
	$value = json_decode( $value, /* to array */TRUE );
}
$value = us_fix_grid_settings( $value );

// Apply fallback to element values
if ( is_array( $value ) AND ! empty( $value[ 'data' ] ) ) {
	foreach( $value['data'] as $elm_id => $elm_options ) {
		$value['data'][ $elm_id ] = apply_filters( 'us_edit_atts_fallback_us_' . /* elm_name */strtok( $elm_id, ':' ), $elm_options );
	}
}

if ( ! function_exists( 'usgb_get_custom_field_labels' ) ) {
	/**
	 * Get translatable custom field labels divided by groupes
	 *
	 * @return array
	 */
	function usgb_get_custom_field_labels() {
		$post_custom_fields = us_config( 'elements/post_custom_field.params.key.options', array() );
		$post_custom_fields_translation = array();

		foreach ( $post_custom_fields as $group_id => $post_custom_field ) {
			if ( $group_id === 'custom' ) {
				$post_custom_fields_translation[ $group_id ] = $post_custom_field;
				continue;
			}

			$group_title = $group_id;
			if ( is_numeric( $group_id ) AND $post_custom_field['__group_label__'] ) {
				$group_title = $post_custom_field['__group_label__'];
			}
			foreach ( $post_custom_field as $field_key => $field_name ) {
				if ( $field_key === '__group_label__' ) {
					continue;
				}
				$post_custom_fields_translation[ $field_key ] = $group_title . ': ' . $field_name;
			}
		}

		return empty( $post_custom_fields_translation )
			? $post_custom_fields
			: $post_custom_fields_translation;
	}
}

$output = '<div class="us-bld" data-ajaxurl="' . esc_attr( admin_url( 'admin-ajax.php' ) ) . '">';

// States
$output .= '<div class="us-bld-states" style="display: none;">';
$output .= '<div class="us-bld-state ui-icon_devices_default active">' . us_translate( 'Default' ) . '</div>';
$output .= '</div>';

// Workspace
$output .= '<div class="us-bld-workspace for_default">';

// Editor
if ( ! function_exists( 'usgb_get_elms_placeholders' ) ) {
	/**
	 * Prepare HTML for elements list for a certain elements area
	 *
	 * @param array $layout
	 * @param array $data Elements data
	 * @param string $place
	 *
	 * @return string
	 */
	function usgb_get_elms_placeholders( &$layout, &$data, $place ) {
		$output = '';
		if ( ! isset( $layout[ $place ] ) OR ! is_array( $layout[ $place ] ) ) {
			return $output;
		}

		foreach ( $layout[ $place ] as $elm ) {

			// Check if the element has absolute position (= at least one design options position is not empty)
			$is_abs = FALSE;
			foreach ( (array) us_get_responsive_states( /* only keys */TRUE ) as $_state ) {
				$_position_value = us_arr_path( $data, $elm . '.css.' . $_state . '.position', '' );
				if ( $_position_value == 'absolute' ) {
					$is_abs = TRUE;
					break;
				}
			}

			// Wrapper element
			if ( substr( $elm, 1, 7 ) == 'wrapper' ) {
				$output .= '<div class="us-bld-editor-wrapper type_' . ( ( $elm[0] == 'h' ) ? 'horizontal' : 'vertical' );
				$output .= $is_abs ? ' pos_abs' : '';
				if ( ! isset( $layout[ $elm ] ) OR empty( $layout[ $elm ] ) ) {
					$output .= ' empty';
				}
				$output .= '" data-id="' . esc_attr( $elm ) . '">';
				$output .= '<div class="us-bld-editor-wrapper-content">';
				$output .= usgb_get_elms_placeholders( $layout, $data, $elm );
				$output .= '</div>';
				$output .= '<div class="us-bld-editor-wrapper-controls">';
				$output .= '<a href="javascript:void(0)" class="us-bld-editor-control type_add" title="' . esc_attr( __( 'Add element into wrapper', 'us' ) ) . '"></a>';
				$output .= '<a href="javascript:void(0)" class="us-bld-editor-control type_edit" title="' . esc_attr( __( 'Edit wrapper', 'us' ) ) . '"></a>';
				$output .= '<a href="javascript:void(0)" class="us-bld-editor-control type_clone" title="' . esc_attr( __( 'Duplicate', 'us' ) ) . '"></a>';
				$output .= '<a href="javascript:void(0)" class="us-bld-editor-control type_delete" title="' . esc_attr( us_translate( 'Delete' ) ) . '"></a>';
				$output .= '</div>';
				$output .= '</div>';

				// Standard element
			} else {
				$type = strtok( $elm, ':' );
				$elm_title = us_config( 'elements/' . $type . '.title', $type );
				$values = $data[ $elm ] ?? array();

				$output .= '<div class="us-bld-editor-elm type_' . $type;
				$output .= $is_abs ? ' pos_abs' : '';
				$output .= '" data-id="' . esc_attr( $elm ) . '">';
				$output .= '<div class="us-bld-editor-elm-content">';

				// Output icon if set
				if ( ! empty( $values['icon'] ) ) {
					$output .= us_prepare_icon_tag( $values['icon'] );
				}

				// Output specific title based on selected element value
				// Text element
				if ( $type == 'text' AND isset( $values['text'] ) ) {
					$output .= strip_tags( $values['text'] );

					// Button element
				} elseif ( $type == 'btn' AND isset( $values['label'] ) ) {
					$output .= strip_tags( $values['label'] );

					// Image element
				} elseif ( $type == 'image' ) {
					if (
						! empty( $values['img'] )
						AND $image_html = wp_get_attachment_image( $values['img'], 'medium' )
					) {
						$output .= $image_html;
					} else {
						$output .= '<i class="fas fa-image"></i>';
					}

					// Post Taxonomy element
				} elseif ( $type == 'post_taxonomy' AND isset( $values['taxonomy_name'] ) ) {
					$taxonomies_options = us_get_taxonomies();
					$output .= us_arr_path( $taxonomies_options, $values['taxonomy_name'], $elm_title );

					// Post Custom Field element
				} elseif ( $type == 'post_custom_field' AND isset( $values['key'] ) ) {
					$post_custom_field_labels = usgb_get_custom_field_labels();
					$output .= us_arr_path( $post_custom_field_labels, $values['key'], $elm_title );
					if ( $values['key'] === 'custom' ) {
						$output .= ': ' . strip_tags( us_arr_path( $values, 'custom_key', '' ) );
					}

					// Post Date element
				} elseif ( $type == 'post_date' AND isset( $values['type'] ) ) {
					$post_date_labels = us_config( 'elements/post_date.params.type.options', array() );
					$output .= us_arr_path( $post_date_labels, $values['type'], $elm_title );

					// Product Data element
				} elseif ( $type == 'product_field' AND isset( $values['type'] ) ) {
					$product_field_labels = us_config( 'elements/product_field.params.type.options', array() );
					$output .= us_arr_path( $product_field_labels, $values['type'], $elm_title );
					if ( $values['type'] == 'sale_badge' AND ! empty ( $values['sale_text'] ) ) {
						$output .= ': "' . strip_tags( $values['sale_text'] ) . '"';
					}

					// User Data element
				} elseif ( $type == 'user_data' AND isset( $values['type'] ) ) {
					$user_data_labels = us_config( 'elements/user_data.params.type.options', array() );
					$output .= us_arr_path( $user_data_labels, $values['type'], $elm_title );
					if ( $values['type'] === 'custom' ) {
						$output .= ': ' . strip_tags( us_arr_path( $values, 'custom_field', '' ) );
					}

				} else {
					$output .= $elm_title;
				}
				$output .= '</div>';
				$output .= '<div class="us-bld-editor-elm-controls">';
				$output .= '<a href="javascript:void(0)" class="us-bld-editor-control type_edit" title="' . esc_attr( __( 'Edit element', 'us' ) ) . '"></a>';
				$output .= '<a href="javascript:void(0)" class="us-bld-editor-control type_clone" title="' . esc_attr( __( 'Duplicate', 'us' ) ) . '"></a>';
				$output .= '<a href="javascript:void(0)" class="us-bld-editor-control type_delete" title="' . esc_attr( us_translate( 'Delete' ) ) . '"></a>';
				$output .= '</div>';
				$output .= '</div>';
			}
		}

		return $output;
	}
}
$output .= '<div class="us-bld-editor">';
$output .= '<div class="us-bld-editor-row at_middle">';
$output .= '<div class="us-bld-editor-row-h">';
$output .= '<div class="us-bld-editor-cell at_center">';
// Output inner widgets
// keeping middle_center for compatibility
$output .= usgb_get_elms_placeholders( $value['default']['layout'], $value['data'], 'middle_center' );
$output .= '<a href="javascript:void(0)" class="us-bld-editor-add" title="' . esc_attr( __( 'Add element', 'us' ) ) . '"></a>';
$output .= '</div>';
$output .= '</div>';
$output .= '</div>';
$output .= '</div><!-- .us-bld-editor -->';

// Options
$output .= '<div class="us-bld-options">';
$hb_options_sections = array(
	'global' => __( 'Grid Layout Settings', 'us' ),
	// 'hover' => __( 'On hover', 'us' ),
);

$options_values = us_arr_path( $value, 'default.options', array() );

// Setting starting state to properly handle show_if rules
$options_values['state'] = 'default';
foreach ( $hb_options_sections as $hb_section => $hb_section_title ) {
	$output .= '<div class="us-bld-options-section' . ( ( $hb_section == 'global' ) ? ' active' : '' ) . '" data-id="' . $hb_section . '">';
	$output .= '<div class="us-bld-options-section-title">' . $hb_section_title . '</div>';
	$output .= '<div class="us-bld-options-section-content" style="display: ' . ( ( $hb_section == 'global' ) ? 'block' : 'none' ) . ';">';
	foreach ( us_config( 'grid-settings.options.' . $hb_section, array() ) as $field_name => $fld ) {
		if ( ! isset( $fld['type'] ) ) {
			continue;
		}
		$field_html = us_get_template(
			'usof/templates/field', array(
				'name' => $field_name,
				'id' => 'hb_opt_' . $field_name,
				'field' => $fld,
				'values' => $options_values,
			)
		);
		// Changing rows' classes to prevent auto-init of these rows as main fields
		$field_html = preg_replace( '~usof\-form\-(row|wrapper) ~', 'usof-subform-$1 ', $field_html );
		$output .= $field_html;
	}
	$output .= '</div>';
	$output .= '</div>';
}
$output .= ' </div ><!-- .us-bld-options -->';

// Export data to JS
$js_data = array(
	'value' => $value,
	'params' => array(
		'navMenus' => us_get_nav_menus(),
	),
);

// Elements' default values
$elms_titles = array();
$elms_defaults = array();

foreach ( us_config( 'grid-settings.elements', array() ) as $type ) {
	$elm = us_config( 'elements/' . $type );
	$elms_titles[ $type ] = isset( $elm['title'] ) ? $elm['title'] : $type;
	$elms_defaults[ $type ] = us_get_elm_defaults( $type, 'grid' );
}
$js_data['defaults'] = $elms_defaults;

$translations = array(
	'template_replace_confirm' => __( 'Selected template will overwrite all your current elements and settings! Are you sure want to apply it?', 'us' ),
	'orientation_change_confirm' => __( 'Are you sure want to change the header orientation? Some of your elements\' positions may be changed', 'us' ),
	'element_delete_confirm' => __( 'Are you sure want to delete the element?', 'us' ),
	'add_element' => __( 'Add element into wrapper', 'us' ),
	'edit_element' => __( 'Edit element', 'us' ),
	'clone_element' => __( 'Duplicate', 'us' ),
	'delete_element' => us_translate( 'Delete' ),
	'edit_wrapper' => __( 'Edit wrapper', 'us' ),
	'delete_wrapper' => us_translate( 'Delete' ),
	'posts_taxonomies' => us_get_taxonomies(),
	'custom_fields_options' => usgb_get_custom_field_labels(),
	'post_date_types' => us_config( 'elements/post_date.params.type.options', array() ),
	'product_field_types' => us_config( 'elements/product_field.params.type.options', array() ),
	'user_data_types' => us_config( 'elements/user_data.params.type.options', array() ),
);
// Setting elements titles for translations
$translations['elms_titles'] = array();
foreach ( us_config( 'grid-settings.elements', array() ) as $elm ) {
	$elm_config = us_config( 'elements/' . $elm );
	$translations['elms_titles'][ $elm ] = us_arr_path( $elm_config, 'title', $elm );
}
$js_data['translations'] = $translations;

$output .= '<div class="us-bld-data hidden"'. us_pass_data_to_js( $js_data ) .'></div>';
$output .= '</div>';

// List of elements that can be added
$output .= us_get_template(
	'usof/templates/window_add', array(
		'elements' => us_config( 'grid-settings.elements', array() ),
	)
);

// Empty editor window for loading the elements afterwards
$output .= us_get_template(
	'usof/templates/window_edit', array(
		'titles' => $elms_titles,
		'body' => '',
	)
);

// Export & Import
$output .= us_get_template(
	'usof/templates/window_export_import', array(
		'title' => __( 'Export / Import', 'us' ),
		'text' => __( 'To import another Grid Layout replace the text in this field and click "Import" button.', 'us' ),
		'save_text' => __( 'Import Grid Layout', 'us' ),
	)
);

// Empty grid layout templates window for loading the templates afterwards
$output .= us_get_template(
	'usof/templates/window_templates', array(
		'body' => '',
	)
);
echo $output;
