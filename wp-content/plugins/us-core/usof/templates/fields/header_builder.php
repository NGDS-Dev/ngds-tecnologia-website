<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: header_builder
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

// Apply fallback for old header data (when header builder was a separate plugin)
if ( function_exists( 'us_header_settings_fallback' ) ) {
	$value = us_header_settings_fallback( $value );
}
if ( function_exists( 'us_fix_header_settings' ) ) {
	$value = us_fix_header_settings( $value );
}

// Apply fallback to element values
if ( is_array( $value ) AND ! empty( $value[ 'data' ] ) ) {
	foreach( $value['data'] as $elm_id => $elm_options ) {
		$value['data'][ $elm_id ] = apply_filters( 'us_edit_atts_fallback_us_' . /* elm_name */strtok( $elm_id, ':' ), $elm_options );
	}
}

$output = '<div class="us-bld" data-ajaxurl="' . esc_attr( admin_url( 'admin-ajax.php' ) ) . '">';

// States
$output .= '<div class="us-bld-states">';

foreach ( us_get_responsive_states() as $state => $data ) {
	$state_atts = array(
		'class' => 'us-bld-state ui-icon_devices_' . $state,
	);
	if ( $state === 'default' ) {
		$state_atts['class'] .= ' active';
	}
	$output .= '<div' . us_implode_atts( $state_atts ) . '>' . $data['title'] . '</div>';
}

$output .= '</div>';

// Workspace
$output .= '<div class="us-bld-workspace for_default">';

// Editor
if ( ! function_exists( 'ushb_get_elms_placeholders' ) ) {
	/**
	 * Prepare HTML for elements list for a certain elements area
	 *
	 * @param array $layout
	 * @param array $data Elements data
	 * @param string $place
	 *
	 * @return string
	 */
	function ushb_get_elms_placeholders( &$layout, &$data, $place ) {
		$output = '';
		if ( ! isset( $layout[ $place ] ) OR ! is_array( $layout[ $place ] ) ) {
			return $output;
		}
		foreach ( $layout[ $place ] as $elm ) {
			if ( substr( $elm, 1, 7 ) == 'wrapper' ) {
				$output .= '<div class="us-bld-editor-wrapper type_' . ( ( $elm[0] == 'h' ) ? 'horizontal' : 'vertical' );
				if ( ! isset( $layout[ $elm ] ) OR empty( $layout[ $elm ] ) ) {
					$output .= ' empty';
				}
				$output .= '" data-id="' . esc_attr( $elm ) . '">';
				$output .= '<div class="us-bld-editor-wrapper-content">';
				$output .= ushb_get_elms_placeholders( $layout, $data, $elm );
				$output .= '</div>';
				$output .= '<div class="us-bld-editor-wrapper-controls">';
				$output .= '<a href="javascript:void(0)" class="us-bld-editor-control type_add" title="' . esc_attr( __( 'Add element into wrapper', 'us' ) ) . '"></a>';
				$output .= '<a href="javascript:void(0)" class="us-bld-editor-control type_edit" title="' . esc_attr( __( 'Edit wrapper', 'us' ) ) . '"></a>';
				$output .= '<a href="javascript:void(0)" class="us-bld-editor-control type_delete" title="' . esc_attr( us_translate( 'Delete' ) ) . '"></a>';
				$output .= '</div>';
				$output .= '</div><!-- .us-bld-editor-wrapper -->';
			} else {

				// Handling standard single element
				$type = strtok( $elm, ':' );
				$elm_title = us_config( 'elements/' . $type . '.title', $type );
				$values = $data[ $elm ] ?? array();

				$output .= '<div class="us-bld-editor-elm type_' . $type . '" data-id="' . esc_attr( $elm ) . '">';
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

					// Menu element
				} elseif ( $type == 'menu' AND ! empty( $values['source'] ) ) {
					$nav_menus = us_get_nav_menus();
					if ( isset( $nav_menus[ $values['source'] ] ) ) {
						$output .= $nav_menus[ $values['source'] ];
					} else {
						$output .= $values['source'];
					}
					
					// Simple Menu element
				} elseif ( $type == 'additional_menu' AND ! empty( $values['source'] ) ) {
					$nav_menus = us_get_nav_menus();
					if ( isset( $nav_menus[ $values['source'] ] ) ) {
						$output .= $nav_menus[ $values['source'] ];
					} else {
						$output .= $values['source'];
					}

					// Search element
				} elseif ( $type == 'search' AND ! empty( $values['text'] ) ) {
					$output .= strip_tags( $values['text'] );

					// Dropdown element
				} elseif ( $type == 'dropdown' AND isset( $values['source'] ) ) {
					if ( ! empty( $values['link_icon'] ) ) {
						$output .= us_prepare_icon_tag( $values['link_icon'] );
					}
					if ( in_array( $values['source'], array( 'wpml', 'polylang' ) ) ) {
						$output .= us_config( 'elements/dropdown.params.source.options.' . $values['source'] );
					} else {
						$output .= empty( $values['link_title'] ) ? $elm_title : strip_tags( $values['link_title'] );
					}

					// Social Links element
				} elseif ( $type == 'socials' ) {
					$socials_html = '';
					foreach ( $values['items'] as $key => $value ) {
						if ( $value['type'] == 'custom' AND isset( $value['icon'] ) ) {
							$socials_html .= us_prepare_icon_tag( $value['icon'] );
						} else {
							$socials_html .= '<i class="fab fa-' . $value['type'] . '"></i>';
						}
					}
					$output .= empty( $socials_html ) ? $elm_title : $socials_html;

					// Custom HTML element
				} elseif ( $type == 'html' ) {
					$output .= 'HTML';

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
$output .= '<div class="us-bld-editor type_';
$output .= ( us_arr_path( $value, 'default.options.orientation', 'hor' ) == 'ver' ) ? 'ver' : 'hor';
$output .= '">';
foreach ( array( 'top', 'middle', 'bottom' ) as $at_y ) {
	$output .= '<div class="us-bld-editor-row at_' . $at_y;
	if ( ( $at_y == 'top' OR $at_y == 'bottom' ) AND ! us_arr_path( $value, 'default.options.' . $at_y . '_show' ) ) {
		$output .= ' disabled';
	}
	$output .= '">';
	$output .= '<div class="us-bld-editor-row-h">';
	foreach ( array( 'left', 'center', 'right' ) as $at_x ) {
		$output .= '<div class="us-bld-editor-cell at_' . $at_x . '">';

		// Output inner widgets
		$output .= ushb_get_elms_placeholders( $value['default']['layout'], $value['data'], $at_y . '_' . $at_x );
		$output .= '<a href="javascript:void(0)" class="us-bld-editor-add" title="' . esc_attr( __( 'Add element', 'us' ) ) . '"></a>';
		$output .= '</div>';
	}
	$output .= '</div>';
	$output .= '</div><!-- .us-bld-editor-row -->';
}

// Outputting hidden elements
$output .= '<div class="us-bld-editor-row for_hidden">';
$output .= '<div class="us-bld-editor-row-desc">' . __( 'Hidden Elements', 'us' ) . '</div>';
$output .= '<div class="us-bld-editor-row-h">';
$output .= ushb_get_elms_placeholders( $value['default']['layout'], $value['data'], 'hidden' );
$output .= '</div>';
$output .= '</div><!-- .us-bld-editor-row.for_hidden -->';
$output .= '</div><!-- .us-bld-editor -->';

// Options
$output .= '<div class="us-bld-options">';
$hb_options_sections = array(
	'global' => __( 'General Header Settings', 'us' ),
	'top' => __( 'Top Area', 'us' ),
	'middle' => __( 'Main Area', 'us' ),
	'bottom' => __( 'Bottom Area', 'us' ),
);

$options_values = us_arr_path( $value, 'default.options', array() );

// Setting starting state to properly handle show_if rules
$options_values['state'] = 'default';
foreach ( $hb_options_sections as $hb_section => $hb_section_title ) {
	$output .= '<div class="us-bld-options-section' . ( ( $hb_section == 'global' ) ? ' active' : '' ) . '" data-id="' . $hb_section . '">';
	$output .= '<div class="us-bld-options-section-title">' . $hb_section_title . '</div>';
	$output .= '<div class="us-bld-options-section-content" style="display: ' . ( ( $hb_section == 'global' ) ? 'block' : 'none' ) . ';">';
	foreach ( us_config( 'header-settings.options.' . $hb_section, array() ) as $field_name => $fld ) {
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
	$output .= '</div><!-- .us-bld-options-section-content -->';
	$output .= '</div><!-- .us-bld-options-section -->';
}
$output .= ' </div ><!-- .us-bld-options -->';

// Export data to JS
$js_data = array(
	'value' => $value,
	'states' => us_get_responsive_states( /* only keys */TRUE ),
	'params' => array(
		'navMenus' => us_get_nav_menus(),
		// TODO Default values
	),
);

// Elements and Options default values
$elms_titles = $elms_defaults = $options_defaults = array();
foreach ( us_config( 'header-settings.elements', array() ) as $type ) {
	$elm = us_config( 'elements/' . $type );
	$elms_titles[ $type ] = isset( $elm['title'] ) ? $elm['title'] : $type;
	$elms_defaults[ $type ] = us_get_elm_defaults( $type, 'header' );
}
foreach ( us_config( 'header-settings.options', array() ) as $group ) {
	foreach ( $group as $param_name => $param ) {
		$options_defaults[ $param_name ] = ( isset( $param['std'] ) ) ? $param['std'] : '';
	}
}
$js_data['elms_defaults'] = $elms_defaults;
$js_data['options_defaults'] = $options_defaults;

$js_data['translations'] = array(
	'template_replace_confirm' => __( 'Selected template will overwrite all your current elements and settings! Are you sure want to apply it?', 'us' ),
	'orientation_change_confirm' => __( 'Are you sure want to change the header orientation? Some of your elements\' positions may be changed', 'us' ),
	'element_delete_confirm' => __( 'Are you sure want to delete the element?', 'us' ),
	'add_element' => __( 'Add element into wrapper', 'us' ),
	'edit_element' => __( 'Edit element', 'us' ),
	'clone_element' => __( 'Duplicate', 'us' ),
	'delete_element' => us_translate( 'Delete' ),
	'edit_wrapper' => __( 'Edit wrapper', 'us' ),
	'delete_wrapper' => us_translate( 'Delete' ),
	'menu' => us_translate( 'Menu' ),
	'additional_menu' => __( 'Simple Menu', 'us' ),
	'dropdown' => __( 'Dropdown', 'us' ),
	'social_links' => __( 'Social Links', 'us' ),
	'button' => __( 'Button', 'us' ),
	'cart' => us_translate( 'Cart', 'woocommerce' ),
);

$js_data['translations']['elms_titles'] = $elms_titles;

$output .= '<div class="us-bld-data hidden"'. us_pass_data_to_js( $js_data ) .'></div>';
$output .= '</div>';

// List of elements that can be added
$output .= us_get_template(
	'usof/templates/window_add', array(
	'elements' => us_config( 'header-settings.elements', array() ),
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
	'title' => __( 'Header Export / Import', 'us' ),
	'text' => __( 'You can export the saved Header by copying the text inside this field. To import another Header replace the text in this field and click "Import Header" button.', 'us' ),
	'save_text' => __( 'Import Header', 'us' ),
)
);

// Empty header templates window for loading the templates afterwards
$output .= us_get_template(
	'usof/templates/window_header_templates', array(
		'body' => '',
	)
);

echo $output;
