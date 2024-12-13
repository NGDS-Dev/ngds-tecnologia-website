<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: vc_video
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * General section
 *
 * @var array
 */
$general_params = array(
	'link' => array(
		'title' => us_translate( 'Link' ),
		'description' => sprintf( __( 'Check supported formats on %s', 'us' ), '<a href="https://wordpress.org/support/article/embeds/" target="_blank">WordPress Codex</a>' ),
		'type' => 'text',
		'std' => 'https://www.youtube.com/watch?v=GLpv-9ZuEfM',
		'dynamic_values' => array(
			'global' => array(),
			'post' => array(),
			'acf_types' => array( 'url', 'oembed' ),
		),
		'admin_label' => TRUE,
		'usb_preview' => TRUE,
	),
	'autoplay' => array(
		'type' => 'switch',
		'switch_text' => us_translate( 'Autoplay' ),
		'std' => 0,
		'context' => array( 'shortcode' ),
	),
	'loop' => array(
		'type' => 'switch',
		'switch_text' => us_translate( 'Loop' ),
		'std' => 0,
		'classes' => 'for_above',
		'usb_preview' => TRUE,
	),
	'muted' => array(
		'type' => 'switch',
		'switch_text' => us_translate( 'Mute' ),
		'std' => 0,
		'classes' => 'for_above',
		'usb_preview' => TRUE,
	),
	'controls' => array(
		'type' => 'switch',
		'switch_text' => _x( 'Controls', 'Video player', 'us' ),
		'std' => 1,
		'classes' => 'for_above',
		'usb_preview' => TRUE,
	),
	'hide_video_title' => array(
		'switch_text' => __( 'Hide Vimeo video title (only if the owner allows)', 'us' ),
		'type' => 'switch',
		'std' => 0,
		'classes' => 'for_above',
		'usb_preview' => TRUE,
	),
	'ratio' => array(
		'title' => __( 'Aspect Ratio', 'us' ),
		'type' => 'select',
		'options' => array(
			'initial' => __( 'Initial', 'us' ),
			'21x9' => '21:9',
			'16x9' => '16:9',
			'4x3' => '4:3',
			'3x2' => '3:2',
			'1x1' => '1:1',
			'9x16' => '9:16',
			'custom' => __( 'Custom', 'us' ),
		),
		'std' => '16x9',
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
	'align' => array(
		'title' => __( 'Video Alignment', 'us' ),
		'type' => 'radio',
		'labels_as_icons' => 'fas fa-align-*',
		'options' => array(
			'none' => us_translate( 'Default' ),
			'left' => us_translate( 'Left' ),
			'center' => us_translate( 'Center' ),
			'right' => us_translate( 'Right' ),
		),
		'std' => 'none',
		'context' => array( 'shortcode' ),
		'usb_preview' => array(
			'mod' => 'align',
		),
	),
	'overlay_image' => array(
		'title' => __( 'Image Overlay', 'us' ),
		'type' => 'upload',
		'dynamic_values' => TRUE,
		'std' => '',
		'usb_preview' => TRUE,
	),
	'overlay_icon' => array(
		'switch_text' => __( 'Show Play icon', 'us' ),
		'type' => 'switch',
		'std' => 1,
		'show_if' => array( 'overlay_image', '!=', '' ),
		'usb_preview' => TRUE,
	),
	'overlay_icon_bg_color' => array(
		'title' => __( 'Background Color', 'us' ),
		'type' => 'color',
		'clear_pos' => 'right',
		'std' => 'rgba(0,0,0,0.5)',
		'cols' => 2,
		'show_if' => array( 'overlay_icon', '=', 1 ),
		'usb_preview' => array(
			'elm' => '.w-video-icon',
			'css' => 'background',
		),
	),
	'overlay_icon_text_color' => array(
		'title' => __( 'Icon Color', 'us' ),
		'type' => 'color',
		'clear_pos' => 'right',
		'std' => '#fff',
		'cols' => 2,
		'show_if' => array( 'overlay_icon', '=', 1 ),
		'usb_preview' => array(
			'elm' => '.w-video-icon',
			'css' => 'color',
		),
	),
	'overlay_icon_size' => array(
		'title' => __( 'Icon Size', 'us' ),
		'description' => us_arr_path( $misc, 'desc_font_size', '' ),
		'type' => 'text',
		'std' => '1.5rem',
		'show_if' => array( 'overlay_icon', '=', 1 ),
		'usb_preview' => array(
			'elm' => '.w-video-icon',
			'css' => 'font-size',
		),
	),
);

/**
 * @return array
 */
return array(
	'title' => __( 'Video Player', 'us' ),
	'icon' => 'fas fa-play-circle',
	'params' => us_set_params_weight(
		$general_params,
		$conditional_params,
		$design_options_params
	),

	// Default VC params which are not supported by the theme
	'vc_remove_params' => array(
		'css_animation',
		'el_aspect',
		'el_width',
		'title',
	),

	// Not used params, required for correct fallback
	'fallback_params' => array(
		'source',
	),

	'usb_init_js' => '$elm.wVideo()',
);
