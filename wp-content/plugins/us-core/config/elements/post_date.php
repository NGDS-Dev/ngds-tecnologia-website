<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: post_date
 */

$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );
$hover_options_params = us_config( 'elements_hover_options' );

$smart_date_example = ' (';
$smart_date_example .= sprintf( us_translate( '%1$s at %2$s' ), us_translate( 'Today' ), '11:04' );
$smart_date_example .= ', ';
$smart_date_example .= sprintf( us_translate( '%1$s at %2$s' ), __( 'Yesterday', 'us' ), '08:55' );
$smart_date_example .= ')';

$time_difference_example = ' (';
$time_difference_example .= sprintf( us_translate( '%s ago' ), human_time_diff( strtotime( '5 hours ago' ) ) );
$time_difference_example .= ', ';
$time_difference_example .= sprintf( us_translate( '%s ago' ), human_time_diff( strtotime( '3 months ago' ) ) );
$time_difference_example .= ')';

/**
 * @return array
 */
return array(
	'title' => __( 'Post Date', 'us' ),
	'category' => __( 'Post Elements', 'us' ),
	'icon' => 'fas fa-calendar-alt',
	'params' => us_set_params_weight(

		// General section
		array(
			'type' => array(
				'title' => us_translate( 'Show' ),
				'type' => 'radio',
				'options' => array(
					'published' => __( 'Date of creation', 'us' ),
					'modified' => __( 'Date of update', 'us' ),
				),
				'std' => 'published',
				'admin_label' => TRUE,
				'usb_preview' => TRUE,
			),
			'format' => array(
				'title' => us_translate( 'Date Format' ),
				'type' => 'select',
				'options' => array(
					'smart' => __( 'Human friendly', 'us' ) . $smart_date_example,
					'time_diff' => __( 'Time difference', 'us' ) . $time_difference_example,
					'default' => us_translate( 'Default' ) . ': ' . date_i18n( get_option( 'date_format' ) ),
					'jS F Y' => date_i18n( 'jS F Y' ),
					'j M, G:i' => date_i18n( 'j M, G:i' ),
					'm/d/Y' => date_i18n( 'm/d/Y' ),
					'j.m.y' => date_i18n( 'j.m.y' ),
					'custom' => __( 'Custom', 'us' ),
				),
				'std' => 'time_diff',
				'admin_label' => TRUE,
				'usb_preview' => TRUE,
			),
			'format_custom' => array(
				'description' => '<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank">' . __( 'Documentation on date and time formatting.', 'us' ) . '</a>',
				'type' => 'text',
				'std' => 'F j, Y',
				'classes' => 'for_above',
				'show_if' => array( 'format', '=', 'custom' ),
				'usb_preview' => TRUE,
			),
			'icon' => array(
				'title' => __( 'Icon', 'us' ),
				'type' => 'icon',
				'std' => '',
				'usb_preview' => TRUE,
			),
			'text_before' => array(
				'title' => __( 'Text before value', 'us' ),
				'type' => 'text',
				'std' => '',
				'usb_preview' => array(
					array(
						'elm' => '.w-post-elm-before',
						'toggle_class_inverse' => 'hidden',
					),
					array(
						'elm' => '.w-post-elm-before',
						'attr' => 'html',
					),
				),
			),
		),

		$conditional_params,
		$design_options_params,
		$hover_options_params
	),
);
