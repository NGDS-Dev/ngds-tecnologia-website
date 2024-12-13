<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: contacts
 */

$design_options_params = us_config( 'elements_design_options' );
$conditional_params = us_config( 'elements_conditional_options' );

/**
 * @return array
 */
return array(
	'title' => us_translate( 'Contact Info' ),
	'icon' => 'fas fa-phone',
	'params' => us_set_params_weight(

		// General section
		array(
			'address' => array(
				'title' => __( 'Address', 'us' ),
				'type' => 'text',
				'std' => '',
				'dynamic_values' => TRUE,
				'usb_preview' => array(
					array(
						'elm' => '.for_address',
						'toggle_class_inverse' => 'hidden',
					),
					array(
						'elm' => '.for_address > .w-contacts-item-value',
						'attr' => 'text',
					),
				),
			),
			'phone' => array(
				'title' => __( 'Phone', 'us' ),
				'type' => 'text',
				'std' => '0123456789',
				'dynamic_values' => TRUE,
				'usb_preview' => array(
					array(
						'elm' => '.for_phone',
						'toggle_class_inverse' => 'hidden',
					),
					array(
						'elm' => '.for_phone > .w-contacts-item-value',
						'attr' => 'text',
					),
				),
			),
			'fax' => array(
				'title' => __( 'Mobiles', 'us' ),
				'type' => 'text',
				'std' => '',
				'dynamic_values' => TRUE,
				'usb_preview' => array(
					array(
						'elm' => '.for_mobile',
						'toggle_class_inverse' => 'hidden',
					),
					array(
						'elm' => '.for_mobile > .w-contacts-item-value',
						'attr' => 'text',
					),
				),
			),
			'email' => array(
				'title' => us_translate( 'Email' ),
				'type' => 'text',
				'std' => '',
				'dynamic_values' => TRUE,
				'usb_preview' => array(
					array(
						'elm' => '.for_email',
						'toggle_class_inverse' => 'hidden',
					),
					array(
						'elm' => '.for_email > .w-contacts-item-value > a',
						'attr' => 'text',
					),
				),
			),
		),

		$conditional_params,
		$design_options_params
	),
);
