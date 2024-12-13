<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: post_image
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );
$hover_options_params = us_config( 'elements_hover_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'Post Image', 'us' ),
	'category' => __( 'Post Elements', 'us' ),
	'icon' => 'fas fa-image',
	'params' => us_set_params_weight(

		// General section
		array(
			'placeholder' => array(
				'type' => 'switch',
				'switch_text' => __( 'Show placeholder when post image is absent', 'us' ),
				'std' => 0,
				'usb_preview' => TRUE,
			),
			'media_preview' => array(
				'type' => 'switch',
				'switch_text' => __( 'Show media preview', 'us' ),
				'description' => __( 'Shows gallery images on hover if they are set. For posts with video format it shows the video player.', 'us' ),
				'std' => 0,
				'classes' => 'desc_3',
				'usb_preview' => TRUE,
			),
			'gallery_images_amount' => array(
				'title' => __( 'Gallery images amount', 'us' ),
				'type' => 'slider',
				'options' => array(
					'' => array(
						'min' => 2,
						'max' => 10,
					),
				),
				'std' => '10',
				'show_if' => array( 'media_preview', '=', 1 ),
				'usb_preview' => TRUE,
			),
			'circle' => array(
				'type' => 'switch',
				'switch_text' => __( 'Enable rounded image', 'us' ),
				'std' => 0,
				'usb_preview' => array(
					'toggle_class' => 'as_circle',
				),
			),
			'has_ratio' => array(
				'switch_text' => __( 'Set Aspect Ratio', 'us' ),
				'type' => 'switch',
				'std' => 0,
				'usb_preview' => TRUE,
			),
			'ratio' => array(
				'type' => 'select',
				'options' => array(
					'1x1' => '1x1 ' . __( 'square', 'us' ),
					'4x3' => '4x3 ' . __( 'landscape', 'us' ),
					'3x2' => '3x2 ' . __( 'landscape', 'us' ),
					'16x9' => '16:9 ' . __( 'landscape', 'us' ),
					'2x3' => '2x3 ' . __( 'portrait', 'us' ),
					'3x4' => '3x4 ' . __( 'portrait', 'us' ),
					'custom' => __( 'Custom', 'us' ),
				),
				'std' => '1x1',
				'classes' => 'for_above',
				'show_if' => array( 'has_ratio', '=', 1 ),
				'usb_preview' => TRUE,
			),
			'ratio_width' => array(
				'placeholder' => us_translate( 'Width' ),
				'type' => 'text',
				'std' => '21',
				'cols' => 2,
				'classes' => 'for_above',
				'show_if' => array( 'ratio', '=', 'custom' ),
				'usb_preview' => TRUE,
			),
			'ratio_height' => array(
				'placeholder' => us_translate( 'Height' ),
				'type' => 'text',
				'std' => '9',
				'cols' => 2,
				'classes' => 'for_above',
				'show_if' => array( 'ratio', '=', 'custom' ),
				'usb_preview' => TRUE,
			),
			'stretch' => array(
				'type' => 'switch',
				'switch_text' => __( 'Stretch the image to the container width', 'us' ),
				'std' => 1,
				'show_if' => array( 'has_ratio', '=', 0 ),
				'usb_preview' => array(
					'toggle_class' => 'stretched',
				),
			),
			'disable_lazy_loading' => array(
				'switch_text' => __( 'Disable Lazy Loading', 'us' ),
				'type' => 'switch',
				'std' => 0,
			),
			'thumbnail_size' => array(
				'title' => __( 'Image Size', 'us' ),
				'description' => $misc['desc_img_sizes'],
				'type' => 'select',
				'options' => us_get_image_sizes_list(),
				'std' => 'large',
				'admin_label' => TRUE,
				'usb_preview' => TRUE,
			),
			'link' => array(
				'title' => us_translate( 'Link' ),
				'type' => 'link',
				'dynamic_values' => array(
					'post' => array(
						'post' => __( 'Post Link', 'us' ),
						'popup_image' => __( 'Open Post Image in a Popup', 'us' ),
						'custom_field|us_tile_link' => sprintf( '%s: %s', __( 'Additional Settings', 'us' ), __( 'Custom Link', 'us' ) ),
					),
				),
				'std' => '{"type":"post"}',
				'shortcode_std' => '',
				'usb_preview' => TRUE,
			),
		),

		$conditional_params,
		$design_options_params,
		$hover_options_params
	),

	// Not used params, required for correct fallback
	'fallback_params' => array(
		'custom_link',
		'link_new_tab',
	),
);
