<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: gravityform
 */

$design_options_params = us_config( 'elements_design_options' );

// Remove the ID setting, to avoid conflicts with the built-in Gravity Form ID
unset( $design_options_params['el_id'] );
unset( $design_options_params['enable_custom_html_atts'] );
unset( $design_options_params['custom_html_atts'] );

// Get a list of available forms
$gravity_forms_options = array(
	'' => us_translate( 'Select a form to display.', 'js_composer' )
);
if ( class_exists( 'RGFormsModel' ) ) {
	/** @noinspection PhpUndefinedClassInspection */
	if ( $gravity_forms = (array) RGFormsModel::get_forms( /* is active */TRUE, /* sort column */'title' ) ) {
		foreach ( $gravity_forms as $gravity_form ) {
			$gravity_forms_options[ $gravity_form->id ] = strip_tags( $gravity_form->title );
		}
	}
}

/**
 * @return array
 */
return array(
	'title' => us_translate( 'Gravity Form', 'js_composer' ),
	'icon' => 'fas fa-envelope',
	'override_config_only' => TRUE, // This is not our element and we only store the configuration for support in the builders
	'weight' => 379,
	'place_if' => class_exists( 'GFForms' ),
	'params' => us_set_params_weight(

		// General params
		array(
			'id' => array(
				'type' => 'select',
				'title' => us_translate( 'Form', 'js_composer' ),
				'options' => $gravity_forms_options,
				'std' => '',
				'admin_label' => TRUE,
				'usb_preview' => TRUE,
			),
			'title' => array(
				'title' => us_translate( 'Display Form Title', 'js_composer' ),
				'type' => 'select',
				'options' => array(
					'false' => us_translate( 'No' ),
					'true' => us_translate( 'Yes' ),
				),
				'std' => 'true',
				'show_if' => array( 'id', '!=', '' ),
				'usb_preview' => TRUE,
			),
			'description' => array(
				'type' => 'select',
				'title' => us_translate( 'Display Form Description', 'js_composer' ),
				'options' => array(
					'false' => us_translate( 'No' ),
					'true' => us_translate( 'Yes' ),
				),
				'std' => 'true',
				'show_if' => array( 'id', '!=', '' ),
				'usb_preview' => TRUE,
			),
			'ajax' => array(
				'type' => 'select',
				'title' => us_translate( 'Enable AJAX?', 'js_composer' ),
				'options' => array(
					'false' => us_translate( 'No' ),
					'true' => us_translate( 'Yes' ),
				),
				'std' => 'false',
				'show_if' => array( 'id', '!=', '' ),
			),
			'tabindex' => array(
				'type' => 'text',
				'title' => us_translate( 'Tab Index', 'js_composer' ),
				'description' => us_translate( '(Optional) Specify the starting tab index for the fields of this form. Leave blank if you\'re not sure what this is.', 'js_composer' ),
				'std' => '',
				'show_if' => array( 'id', '!=', '' ),
			),
		),

		$design_options_params
	),
	// Displaying a form after loading, which was hidden by the main logic
	// to prevent flickering when initializing steps
	'usb_init_js' => 'jQuery( $elm ).show()',
);
