<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: person
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'Person', 'us' ),
	'description' => __( 'Photo, name, description and social links', 'us' ),
	'icon' => 'fas fa-user',
	'params' => us_set_params_weight(

		// General section
		array(
			'image' => array(
				'title' => __( 'Photo', 'us' ),
				'type' => 'upload',
				'dynamic_values' => TRUE,
				'extension' => 'png,jpg,jpeg,gif,svg',
				'cols' => 2,
				'usb_preview' => TRUE,
			),
			'image_hover' => array(
				'title' => __( 'Photo on hover (optional)', 'us' ),
				'type' => 'upload',
				'dynamic_values' => TRUE,
				'extension' => 'png,jpg,jpeg,gif,svg',
				'cols' => 2,
				'usb_preview' => TRUE,
			),
			'name' => array(
				'title' => us_translate( 'Name' ),
				'type' => 'text',
				'std' => 'John Doe',
				'dynamic_values' => TRUE,
				'holder' => 'div',
				'usb_preview' => array(
					'attr' => 'html',
					'elm' => '.w-person-name > *',
				),
			),
			'name_tag' => array(
				'title' => __( 'Name HTML tag', 'us' ),
				'type' => 'select',
				'options' => $misc['html_tag_values'],
				'std' => 'h4',
				'cols' => 2,
				'show_if' => array( 'name', '!=', '' ),
				'usb_preview' => array(
					'elm' => '.w-person-name',
					'attr' => 'tag',
				),
			),
			'name_size' => array(
				'title' => __( 'Name Size', 'us' ),
				'description' => $misc['desc_font_size'],
				'type' => 'text',
				'std' => '',
				'cols' => 2,
				'show_if' => array( 'name', '!=', '' ),
				'usb_preview' => array(
					'elm' => '.w-person-name',
					'css' => 'font-size',
				),
			),
			'role' => array(
				'title' => __( 'Role', 'us' ),
				'type' => 'text',
				'std' => 'UpSolution Team',
				'dynamic_values' => TRUE,
				'holder' => 'div',
				'usb_preview' => array(
					'attr' => 'html',
					'elm' => '.w-person-role',
				),
			),
			'content' => array(
				'title' => us_translate( 'Description' ),
				'type' => 'textarea',
				'show_ai_icon' => TRUE,
				'std' => '',
				'holder' => 'div',
				'usb_preview' => array(
					'attr' => 'html',
					'elm' => '.w-person-description',
				),
			),
		),

		// More Options section
		array(
			'link' => array(
				'title' => us_translate( 'Link' ),
				'description' => __( 'Applies to the Name and to the Photo', 'us' ),
				'type' => 'link',
				'dynamic_values' => array(
					'global' => array(
						'homepage' => us_translate( 'Homepage' ),
						'popup_image' => __( 'Open Image in a Popup', 'us' ),
					),
				),
				'std' => '{"url":""}',
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => TRUE,
			),
			'layout' => array(
				'title' => __( 'Layout', 'us' ),
				'type' => 'select',
				'options' => array(
					'simple' => __( 'Simple', 'us' ),
					'simple_circle' => __( 'Simple (rounded photo)', 'us' ),
					'square' => __( 'Compact', 'us' ),
					'circle' => __( 'Compact (rounded photo)', 'us' ),
					'modern' => __( 'Modern', 'us' ),
					'trendy' => __( 'Trendy', 'us' ),
					'cards' => __( 'Cards', 'us' ),
				),
				'std' => 'circle',
				'cols' => 2,
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => array(
					'mod' => 'layout',
				),
			),
			'effect' => array(
				'title' => __( 'Photo Effect', 'us' ),
				'type' => 'select',
				'options' => array(
					'none' => us_translate( 'None' ),
					'sepia' => __( 'Sepia', 'us' ),
					'bw' => __( 'Black & White', 'us' ),
					'faded' => __( 'Faded', 'us' ),
					'colored' => __( 'Colored', 'us' ),
				),
				'std' => 'none',
				'cols' => 2,
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => array(
					'mod' => 'effect',
				),
			),
			'img_size' => array(
				'title' => __( 'Image Size', 'us' ),
				'description' => $misc['desc_img_sizes'],
				'type' => 'select',
				'options' => us_get_image_sizes_list(),
				'std' => 'large',
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => TRUE,
			),
			'email' => array(
				'title' => us_translate( 'Email' ),
				'type' => 'text',
				'std' => '',
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => TRUE,
			),
			'facebook' => array(
				'title' => 'Facebook',
				'type' => 'text',
				'std' => '',
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => TRUE,
			),
			'twitter' => array(
				'title' => 'Twitter',
				'type' => 'text',
				'std' => '',
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => TRUE,
			),
			'google_plus' => array(
				'title' => 'Google',
				'type' => 'text',
				'std' => '',
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => TRUE,
			),
			'linkedin' => array(
				'title' => 'LinkedIn',
				'type' => 'text',
				'std' => '',
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => TRUE,
			),
			'skype' => array(
				'title' => 'Skype',
				'type' => 'text',
				'std' => '',
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => TRUE,
			),
			'custom_link' => array(
				'title' => __( 'Custom Link', 'us' ),
				'type' => 'link',
				'dynamic_values' => array(
					'global' => array(
						'homepage' => us_translate( 'Homepage' ),
						'popup_image' => __( 'Open Image in a Popup', 'us' ),
					),
				),
				'std' => '{"url":""}',
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => TRUE,
			),
			'custom_icon' => array(
				'title' => __( 'Custom Link Icon', 'us' ),
				'type' => 'icon',
				'std' => 'fas|star',
				'show_if' => array( 'custom_link', '!=', '' ),
				'group' => __( 'More Options', 'us' ),
				'usb_preview' => TRUE,
			),
		),

		$conditional_params,
		$design_options_params
	),

	// Not used params, required for correct fallback
	'fallback_params' => array(
		'custom_link_new_tab',
	),
);
