<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

// Get Reusable Blocks
$us_page_blocks_list = array();
if ( us_is_elm_editing_page() ) {
	$us_page_blocks_list = us_get_posts_titles_for( 'us_page_block' );
}

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'Popup', 'us' ),
	'category' => __( 'Interactive', 'us' ),
	'icon' => 'fas fa-window-restore',
	'params' => us_set_params_weight(
		array(
			// General
			'use_page_block' => array(
				'title' => __( 'Reusable Block', 'us' ),
				'type' => 'select',
				'hints_for' => 'us_page_block',
				'options' => us_array_merge(
					array( 'none' => '– ' . us_translate( 'None' ) . ' –' ),
					$us_page_blocks_list
				),
				'std' => 'none',
				'group' => us_translate( 'Content' ),
				'usb_preview' => TRUE,
			),
			'title' => array(
				'title' => us_translate( 'Title' ),
				'type' => 'text',
				'std' => '',
				'holder' => 'div',
				'show_if' => array( 'use_page_block', '=', 'none' ),
				'group' => us_translate( 'Content' ),
				'usb_preview' => TRUE,
			),
			'content' => array(
				'type' => 'editor',
				'std' => __( 'This content will appear inside a popup...', 'us' ),
				'show_if' => array( 'use_page_block', '=', 'none' ),
				'group' => us_translate( 'Content' ),
				'usb_preview' => TRUE,
			),

			// Appearance
			'layout' => array(
				'title' => __( 'Layout', 'us' ),
				'type' => 'select',
				'options' => array(
					'default' => us_translate( 'Default' ),
					'fullscreen' => __( 'Full Screen', 'us' ),
					'left_panel' => __( 'Left Panel', 'us' ),
					'right_panel' => __( 'Right Panel', 'us' ),
				),
				'std' => 'default',
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'animation' => array(
				'title' => __( 'Animation', 'us' ),
				'type' => 'select',
				'options' => array(
					'fadeIn' => __( 'Fade', 'us' ),
					'scaleUp' => __( 'Scale Up', 'us' ),
					'scaleDown' => __( 'Scale Down', 'us' ),
					'slideTop' => __( 'Slide from the Top', 'us' ),
					'slideLeft' => __( 'Slide from the Left', 'us' ),
					'slideRight' => __( 'Slide from the Right', 'us' ),
					'slideBottom' => __( 'Slide from the Bottom', 'us' ),
					'flipHor' => __( '3D Flip', 'us' ) . ' (' . __( 'Horizontal', 'us' ) . ')',
					'flipVer' => __( '3D Flip', 'us' ) . ' (' . __( 'Vertical', 'us' ) . ')',
				),
				'std' => 'fadeIn',
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'closer_pos' => array(
				'title' => __( 'Close Button Position', 'us' ),
				'type' => 'select',
				'options' => array(
					'outside' => __( 'Outside the Popup', 'us' ),
					'inside' => __( 'Inside the Popup', 'us' ),
					'none' => us_translate( 'None' ),
				),
				'std' => 'outside',
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'popup_width' => array(
				'title' => __( 'Popup Width', 'us' ),
				'description' => $misc['desc_width'],
				'type' => 'text',
				'std' => '600px',
				'show_if' => array( 'layout', '!=', 'fullscreen' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'popup_padding' => array(
				'title' => __( 'Popup Padding', 'us' ),
				'description' => $misc['desc_padding'],
				'type' => 'text',
				'std' => '5%',
				'cols' => 2,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'popup_border_radius' => array(
				'title' => __( 'Popup Border Radius', 'us' ),
				'description' => $misc['desc_border_radius'],
				'type' => 'text',
				'std' => '',
				'cols' => 2,
				'show_if' => array( 'layout', '=', 'default' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'title_bgcolor' => array(
				'title' => __( 'Title Background', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'std' => '_content_bg_alt',
				'cols' => 2,
				'show_if' => array( 'use_page_block', '=', 'none' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'title_textcolor' => array(
				'title' => __( 'Title Text', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'with_gradient' => FALSE,
				'std' => '_content_heading',
				'cols' => 2,
				'show_if' => array( 'use_page_block', '=', 'none' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'content_bgcolor' => array(
				'title' => __( 'Popup Background', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'std' => '_content_bg',
				'cols' => 2,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'content_textcolor' => array(
				'title' => __( 'Popup Text', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'with_gradient' => FALSE,
				'std' => '_content_text',
				'cols' => 2,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'overlay_bgcolor' => array(
				'title' => __( 'Background Overlay', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'std' => 'rgba(0,0,0,0.85)',
				'cols' => 2,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'closer_color' => array(
				'title' => __( 'Close Button', 'us' ),
				'type' => 'color',
				'clear_pos' => 'right',
				'std' => '',
				'cols' => 2,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),

			// Trigger
			'show_on' => array(
				'title' => __( 'Show Popup via', 'us' ),
				'type' => 'select',
				'options' => array(
					'btn' => us_translate( 'Button' ),
					'image' => us_translate( 'Image' ),
					'icon' => __( 'Icon', 'us' ),
					'selector' => __( 'Custom element', 'us' ),
					'load' => __( 'Page load', 'us' ),
				),
				'std' => 'btn',
				'group' => __( 'Trigger', 'us' ),
				'usb_preview' => TRUE,
			),
			'btn_label' => array(
				'title' => __( 'Button Label', 'us' ),
				'type' => 'text',
				'std' => __( 'Click Me', 'us' ),
				'dynamic_values' => TRUE,
				'cols' => 2,
				'admin_label' => TRUE,
				'show_if' => array( 'show_on', '=', 'btn' ),
				'group' => __( 'Trigger', 'us' ),
				'usb_preview' => array(
					'elm' => '.w-popup-trigger.type_btn',
					'attr' => 'text',
				),
			),
			'btn_size' => array(
				'title' => __( 'Button Size', 'us' ),
				'description' => $misc['desc_font_size'],
				'type' => 'text',
				'std' => '',
				'cols' => 2,
				'show_if' => array( 'show_on', '=', 'btn' ),
				'group' => __( 'Trigger', 'us' ),
				'usb_preview' => array(
					'elm' => '.w-popup-trigger.type_btn',
					'css' => 'font-size',
				),
			),
			'btn_style' => array(
				'title' => __( 'Button Style', 'us' ),
				'description' => $misc['desc_btn_styles'],
				'type' => 'select',
				'options' => us_get_btn_styles(),
				'std' => '1',
				'show_if' => array( 'show_on', '=', 'btn' ),
				'group' => __( 'Trigger', 'us' ),
				'usb_preview' => array(
					'elm' => '.w-popup-trigger.type_btn',
					'mod' => 'us-btn-style',
				)
			),
			'image' => array(
				'title' => us_translate( 'Image' ),
				'type' => 'upload',
				'dynamic_values' => TRUE,
				'cols' => 2,
				'show_if' => array( 'show_on', '=', 'image' ),
				'group' => __( 'Trigger', 'us' ),
				'usb_preview' => TRUE,
			),
			'image_size' => array(
				'title' => __( 'Image Size', 'us' ),
				'description' => $misc['desc_img_sizes'],
				'type' => 'select',
				'options' => us_get_image_sizes_list(),
				'std' => 'large',
				'cols' => 2,
				'show_if' => array( 'show_on', '=', 'image' ),
				'group' => __( 'Trigger', 'us' ),
				'usb_preview' => TRUE,
			),
			'align' => array(
				'title' => us_translate( 'Alignment' ),
				'type' => 'radio',
				'labels_as_icons' => 'fas fa-align-*',
				'options' => array(
					'none' => us_translate( 'Default' ),
					'left' => us_translate( 'Left' ),
					'center' => us_translate( 'Center' ),
					'right' => us_translate( 'Right' ),
					'justify' => __( 'Stretch to the full width', 'us' ),
				),
				'std' => 'none',
				'show_if' => array( 'show_on', '=', array( 'btn', 'image', 'icon' ) ),
				'context' => array( 'shortcode' ),
				'group' => __( 'Trigger', 'us' ),
				'is_responsive' => TRUE,
				'usb_preview' => array(
					'mod' => 'align',
				),
			),
			'btn_icon' => array(
				'title' => __( 'Icon', 'us' ),
				'type' => 'icon',
				'std' => '',
				'show_if' => array( 'show_on', '=', array( 'btn', 'icon' ) ),
				'group' => __( 'Trigger', 'us' ),
				'usb_preview' => TRUE,
			),
			'btn_iconpos' => array(
				'title' => __( 'Icon Position', 'us' ),
				'type' => 'radio',
				'options' => array(
					'left' => us_translate( 'Left' ),
					'right' => us_translate( 'Right' ),
				),
				'std' => 'left',
				'show_if' => array( 'show_on', '=', 'btn' ),
				'group' => __( 'Trigger', 'us' ),
				'usb_preview' => TRUE,
			),
			'trigger_selector' => array(
				'title' => __( 'Custom element CSS selector', 'us' ),
				'description' => __( 'Use class or ID.', 'us' ) . ' ' . __( 'Examples:', 'us' ) . ' <span class="usof-example">.my-element</span>, <span class="usof-example">#my-element</span>',
				'type' => 'text',
				'std' => '.my-element',
				'show_if' => array( 'show_on', '=', 'selector' ),
				'group' => __( 'Trigger', 'us' ),
				'usb_preview' => TRUE,
			),
			'show_delay' => array(
				'title' => __( 'Delay after page load (in seconds)', 'us' ),
				'type' => 'text',
				'std' => '2',
				'show_if' => array( 'show_on', '=', 'load' ),
				'group' => __( 'Trigger', 'us' ),
				'usb_preview' => TRUE,
			),
		),

		$conditional_params,
		$design_options_params
	),

	'usb_init_js' => '$elm.wPopup()',
);
