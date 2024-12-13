<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: Contact Form 7
 */

$design_options_params = us_config( 'elements_design_options' );

// Remove the ID setting, to avoid conflicts with the built-in Form ID
unset( $design_options_params['el_id'] );
unset( $design_options_params['enable_custom_html_atts'] );
unset( $design_options_params['custom_html_atts'] );

// Get a list of available forms
if ( $cforms = get_posts( 'post_type="wpcf7_contact_form"&numberposts=-1' ) ) {
	foreach ( $cforms as $cform ) {
		$contact_forms[ $cform->ID ] = strip_tags( $cform->post_title );
	}
} else {
	$contact_forms[''] = us_translate( 'No contact forms were found. Create a contact form first.', 'contact-form-7' );
}

/**
 * @return array
 */
return array(
	'title' => us_translate( 'Contact Form 7', 'contact-form-7' ),
	'icon' => 'fas fa-envelope',
	'override_config_only' => TRUE, // This is not our element and we only store the configuration for support in the builders
	'weight' => 379,
	'place_if' => class_exists( 'WPCF7' ),
	'params' => us_set_params_weight(

		// General params
		array(
			'id' => array(
				'title' => us_translate( 'Contact Form', 'contact-form-7' ),
				'type' => 'select',
				'options' => $contact_forms,
				'std' => '',
				'admin_label' => TRUE,
				'usb_preview' => TRUE,
			),
		),

		$design_options_params
	),
);
