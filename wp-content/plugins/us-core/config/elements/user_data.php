<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for User Data element
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );
$hover_options_params = us_config( 'elements_hover_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'User Data', 'us' ),
	'icon' => 'fas fa-user',
	'params' => us_set_params_weight(

		// General section
		array(
			'type' => array(
				'title' => us_translate( 'Show' ),
				'type' => 'select',
				'options' => array(
					'display_name' => us_translate( 'User Display Name' ),
					'first_name' => us_translate( 'First Name' ),
					'last_name' => us_translate( 'Last Name' ),
					'nickname' => us_translate( 'Nickname' ),
					'user_email' => us_translate( 'Email' ),
					'user_url' => us_translate( 'Website' ),
					'description' => us_translate( 'Biographical Info' ),
					'role' => us_translate( 'Role' ),
					'user_registered' => __( 'Registration Date', 'us' ),
					'post_count' => __( 'Amount of posts', 'us' ),
					'custom' => __( 'Custom Field', 'us' ),
				),
				'std' => 'display_name',
			),
			'custom_field' => array(
				'placeholder' => 'custom_field_name',
				'description' => __( 'Enter a custom field name to get its value.', 'us' ),
				'type' => 'text',
				'std' => '',
				'classes' => 'for_above',
				'show_if' => array( 'type', '=', 'custom' ),
			),
			'date_format' => array(
				'title' => us_translate( 'Date Format' ),
				'description' => '<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">' . __( 'Documentation on date and time formatting.', 'us' ) . '</a>',
				'type' => 'text',
				'std' => 'F j, Y',
				'show_if' => array( 'type', '=', 'user_registered' ),
			),
			'link' => array(
				'title' => us_translate( 'Link' ),
				'type' => 'link',
				'dynamic_values' => array(
					'global' => array(
						'elm_value' => __( 'Clickable value (email, phone, website)', 'us' ),
					),
					'post' => array(),
				),
				'std' => '{"url":""}',
				'show_if' => array( 'type', '!=', 'description' ),
			),
			'color_link' => array(
				'title' => __( 'Link Color', 'us' ),
				'type' => 'switch',
				'switch_text' => __( 'Inherit from text color', 'us' ),
				'std' => 0,
				'show_if' => array( 'type', '!=', 'description' ),
			),
			'tag' => array(
				'title' => __( 'HTML tag', 'us' ),
				'type' => 'select',
				'options' => $misc['html_tag_values'],
				'std' => 'div',
			),
			'text_before' => array(
				'title' => __( 'Text before value', 'us' ),
				'type' => 'text',
				'std' => '',
			),
			'text_after' => array(
				'title' => __( 'Text after value', 'us' ),
				'type' => 'text',
				'std' => '',
			),
		),

		$conditional_params,
		$design_options_params,
		$hover_options_params
	),
);
