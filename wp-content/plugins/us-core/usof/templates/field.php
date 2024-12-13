<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output single USOF Field.
 *
 * Multiple selector.
 *
 * @var $name string Field name
 * @var $id string Field ID
 * @var $field array Field options
 * @var $values array Set of values for the current and relevant fields
 * @var $context string The context in which the field is loaded
 *
 * @var $usof_popup_vars Variables for the popup only within the current field
 */
if ( isset( $field['place_if'] ) AND ! $field['place_if'] ) {
	return;
}
if ( ! isset( $field['type'] ) ) {
	if ( WP_DEBUG ) {
		wp_die( $name . ' has no defined type' );
	}

	return;
}

$type = us_arr_path( $field, 'type' ); // get current field type
$context = isset( $context ) ? $context : 'header'; // get current context

$show_field = isset( $show_field )
	? $show_field
	: ( ! isset( $field['show_if'] ) OR usof_execute_show_if( $field['show_if'], $values ) );

// Specific field params for the usbuilder
$usb_params = array();
if ( usb_is_builder_page() ) {
	foreach ( $field as $prop_name => $prop_value ) {
		if ( strpos( $prop_name, 'usb_' ) === 0 ) {
			$usb_params[ $prop_name ] = $prop_value;
		}
	}
}

// Determine the use of responsive for the current field
$is_responsive = ! in_array( $type, /* disable for these field types */array( 'design_options', 'group' ) )
	? us_arr_path( $field, 'is_responsive', FALSE )
	: FALSE;

// Output Wrapper
if ( $type == 'wrapper_start' ) {
	$row_class = '';
	if ( ! empty( $field['classes'] ) ) {
		$row_class .= ' ' . $field['classes'];
	}
	echo '<div class="usof-form-wrapper ' . $row_class . '" data-name="' . $name . '" ';
	echo 'style="display: ' . ( $show_field ? 'block' : 'none' ) . '">';
	if ( ! empty( $field['title'] ) ) {
		echo '<div class="usof-form-wrapper-title">' . $field['title'] . '</div>';
	}
	echo '<div class="usof-form-wrapper-content">';
	if ( ! empty( $field['show_if'] ) AND is_array( $field['show_if'] ) ) {
		// Showing conditions
		echo '<div class="usof-form-wrapper-showif hidden"' . us_pass_data_to_js( $field['show_if'] ) . '></div>';
	}

	return;
} elseif ( $type == 'wrapper_end' ) {
	echo '</div>'; // .usof-form-wrapper-content
	echo '</div>'; // .usof-form-wrapper

	return;
}

// Set default value if not set
if ( ! isset( $field['std'] ) ) {
	$field['std'] = NULL;
}

// Get current value
$value = $field['std'];
if ( isset( $values[ $name ] ) ) {
	$value = $values[ $name ];
}

// Output Group params
if ( $type == 'group' ) {
	$atts_group = array(
		'class' => 'usof-form-group',
		'data-name' => $name,
		'style' => 'display:' . ( $show_field ? 'block' : 'none' ),
	);
	if ( ! empty( $field['classes'] ) ) {
		$atts_group['class'] .= ' ' . $field['classes'];
	}
	if ( ! empty( $field['is_accordion'] ) ) {
		$atts_group['class'] .= ' type_accordion';

		// Add data which param value use as accordions title
		if ( ! empty( $field['accordion_title'] ) ) {
			$atts_group['data-accordion-title'] = rawurlencode( $field['accordion_title'] );
		}
	} else {
		$atts_group['class'] .= ' type_simple';
	}
	if ( ! empty( $field['is_sortable'] ) ) {
		$atts_group['class'] .= ' sortable';
	}
	if ( ! empty( $field['preview'] ) ) {
		$atts_group['class'] .= ' preview_' . $field['preview'];
	}

	// Parameters for USBuilder (display only on the USBuilder page)
	if ( ! empty( $usb_params ) ) {
		$atts_group['data-usb-params'] = us_json_encode( $usb_params );
	}

	echo '<div' . us_implode_atts( $atts_group ) . '>';
	if ( ! empty( $field['title'] ) ) {
		echo '<div class="usof-form-group-title"><span>' . strip_tags( $field['title'] ) . '</span></div>';
	}
	echo '<div class="usof-form-group-prototype hidden">';
	us_load_template(
		'usof/templates/fields/group_param', array(
			'params_values' => array(),
			'field' => $field,
			'context' => $context,
		)
	);
	echo '</div>'; // .usof-form-group-prototype

	if ( is_array( $value ) AND count( $value ) > 0 ) {
		foreach ( $value as $index => $params_values ) {
			us_load_template(
				'usof/templates/fields/group_param', array(
					'params_values' => $params_values,
					'field' => $field,
					'context' => $context,
				)
			);
		}
	}

	// Output "Add" button, if "show_controls" is set
	if ( ! empty( $field['show_controls'] ) ) {
		if ( ! empty( $field['label_for_add_button'] ) ) {
			$label_for_add_button = $field['label_for_add_button'];
		} else {
			$label_for_add_button = us_translate( 'Add' );
		}
		echo '<span class="usof-form-group-add">';
		echo '<span class="usof-form-group-add-title">' . esc_html( $label_for_add_button ) . '</span>';
		echo '<span class="usof-preloader"></span>';
		echo '</span>';
		$translations = array(
			'style' => us_translate( 'Style' ),
		);
		echo '<span class="usof-form-group-translations hidden"' . us_pass_data_to_js( $translations ) . '></span>';
	}

	// Show_if conditions
	if ( ! empty( $field['show_if'] ) AND is_array( $field['show_if'] ) ) {
		echo '<div class="usof-form-row-showif hidden"' . us_pass_data_to_js( $field['show_if'] ) . '></div>';
	}
	echo '</div>'; // .usof-form-group

	return;
}

$row_class = ' type_' . $type;

// View typography fields as accordion in Live Builder
if ( $type == 'typography_options' AND usb_is_builder_page() ) {
	$row_class .= ' view_as_accordion';
}

if (
	! in_array( $type, array( 'message', 'heading' ) )
	AND (
		! isset( $field['classes'] )
		OR strpos( $field['classes'], 'desc_' ) === FALSE
	)
) {
	$row_class .= ' desc_1';
}

if ( isset( $field['cols'] ) ) {
	$row_class .= ' cols_' . $field['cols'];
}
if ( isset( $field['title_pos'] ) AND ! usb_is_builder_page() ) {
	$row_class .= ' titlepos_' . $field['title_pos'];
}
if ( ! empty( $field['classes'] ) ) {
	$row_class .= ' ' . $field['classes'];
}
if ( ! empty( $field['disabled'] ) ) {
	$row_class .= ' disabled';
}
if ( ! empty( $field['preview_text'] ) ) {
	$row_class .= ' with_preview_text';
}

// Output option row
$atts_row = array(
	'class' => 'usof-form-row' . $row_class,
	'data-name' => $name,
	'style' => sprintf( 'display: %s', $show_field ? 'block' : 'none' ),
);

// Add the field name which affects the current options
if ( $options_filtered_by_param = us_arr_path( $field, 'options_filtered_by_param' ) ) {
	$atts_row['data-related-on'] = $options_filtered_by_param;
}

// Add the output of the default value for `type=select`
if ( in_array( $type, array( 'select' ) ) ) {
	$atts_row['data-std'] = $field['std'];
}

// Parameters for USBuilder (display only on the USBuilder page)
if ( ! empty( $usb_params ) ) {
	$atts_row['data-usb-params'] = us_json_encode( $usb_params );
}

// HTML data output for js
if ( isset( $field['html-data'] ) ) {
	$atts_row['onclick'] = us_pass_data_to_js( $field['html-data'], /* onclick */FALSE );
}

echo '<div'. us_implode_atts( $atts_row ) .'>';

// Title
if ( $show_title = ! empty( $field['title'] ) ) {
	echo '<div class="usof-form-row-title"><span>' . $field['title'] . '</span>';
}
if ( $is_responsive ) {
	echo '<span class="usof-switch-responsive ui-icon_devices" title="'. esc_attr( __( 'Responsive', 'us' ) ) .'"></span>';
}
if ( ! empty( $field['reset_button'] ) ) {
	echo '<span class="usof-form-row-reset">' . strip_tags( __( 'Reset', 'us' ) ) . '</span>';
}
if ( $show_title ) {
	if (
		! empty( $field['description'] )
		AND (
			! empty( $field['classes'] )
			AND strpos( $field['classes'], 'desc_4' ) !== FALSE
		)
	) {
		echo '<div class="usof-form-row-desc">';
		echo '<div class="usof-form-row-desc-icon"></div>';
		echo '<div class="usof-form-row-desc-text">' . $field['description'] . '</div>';
		echo '</div>'; // .usof-form-row-desc
	}
	echo '</div>'; // .usof-form-row-title
}

// Output of responsive buttons
if ( $is_responsive ) {
	echo '<div class="usof-form-row-responsive" '. us_pass_data_to_js( us_get_responsive_states( /* only keys */TRUE ) ) .'>';
	echo usof_get_responsive_buttons();
	echo '</div>'; // .usof-form-row-responsive
}

echo '<div class="usof-form-row-field">';

// Include the field control itself
echo '<div class="usof-form-row-control">';
us_load_template(
	'usof/templates/fields/' . $type, /* vars */array(
		'name' => $name,
		'id' => $id,
		'field' => $field,
		'value' => $value,
		'context' => $context,
	)
);
echo '</div>'; // .usof-form-row-control

// Add the description html
if ( ! empty( $field['description'] ) AND ( empty( $field['classes'] ) OR strpos( $field['classes'], 'desc_4' ) === FALSE ) ) {
	echo '<div class="usof-form-row-desc">';
	echo '<div class="usof-form-row-desc-icon"></div>';
	echo '<div class="usof-form-row-desc-text">' . $field['description'] . '</div>';
	echo '</div>'; // .usof-form-row-desc
}

if ( isset( $field['hints_for'] ) ) {
	// Check if post type exist
	$post_type_obj = get_post_type_object( $field['hints_for'] );

	if ( $post_type_obj ) {

		$hint_text = '';

		// Check if 'Edit selected' links should lead to a Live Builder page
		global $pagenow;
		$show_link_edit_live = (

			// Headers and Grid Layouts always being edited in backend
			! in_array( $field['hints_for'], array( 'us_header', 'us_grid_layout' ) )
			AND (

				// Builder elements panel
				usb_is_builder_page()

				// Theme options, if the live builder is ON
				OR (
					$pagenow == 'admin.php'
					AND $_GET['page'] == 'us-theme-options'
					AND us_get_option( 'live_builder', 1 )
				)
			)
		);

		$edit_link = $show_link_edit_live
			? admin_url( 'post.php?post={{post_id}}&action=' . US_BUILDER_SLUG )
			: admin_url( 'post.php?post={{post_id}}&action=edit' );

		// Get post labels for hints
		$hints = array(
			'edit_url' => '<a href="' . $edit_link . '" target="_blank">{{hint}}</a>',
			// for JS
			'add' => $post_type_obj->labels->add_new,
			'edit' => __( 'Edit selected', 'us' ),
			'edit_specific' => us_translate( 'Edit' ),
		);

		// Count published posts
		if ( wp_count_posts( $field['hints_for'] )->publish ) {

			$edit_link = $show_link_edit_live
				? usb_get_edit_link( /* post_id */$value )
				: get_edit_post_link( $value );

			// Output "Edit" link if post exists and assigned
			if ( $edit_link AND $value ) {
				$hint_text = '<a href="' . $edit_link . '" target="_blank">' . $hints['edit'] . '</a>';
			}

			// Output "Add New" link if there are no published posts
		} else {
			$hint_text = '<a href="' . admin_url( 'post-new.php?post_type=' . $field['hints_for'] ) . '" target="_blank">' . $hints['add'] . '</a>';
			$hints['no_posts'] = TRUE;
		}

		if ( isset( $hints['add'] ) ) {
			unset( $hints['add'] );
		}

		echo '<div class="usof-form-row-hint"' . us_pass_data_to_js( $hints ) . '>' . $hint_text . '</div>';
	}
}

echo '</div>'; // .usof-form-row-field

if ( ! empty( $field['show_if'] ) AND is_array( $field['show_if'] ) ) {
	// Showing conditions
	echo '<div class="usof-form-row-showif"' . us_pass_data_to_js( $field['show_if'] ) . '></div>';
}

echo '</div>'; // .usof-form-row
