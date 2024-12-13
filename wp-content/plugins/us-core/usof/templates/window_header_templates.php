<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output elements list to choose from
 *
 * @var $body string Optional predefined body
 */
global $us_template_directory_uri;
$templates = us_config( 'header-templates', array() );

if ( ! isset( $body ) ) {
	$body = '<ul class="us-bld-window-list">';
	foreach ( $templates as $name => $template ) {
		if ( function_exists( 'us_fix_header_template_settings' )) {
			$template = us_fix_header_template_settings( $template );
		}
		$body .= '<li class="us-bld-window-item" data-name="' . esc_attr( $name ) . '">';
		$body .= '<img src="' . $us_template_directory_uri . '/common/admin/img/header-templates/' . $name . '.png" alt="' . $name . '">';
		$body .= '<div class="us-bld-window-item-data"' . us_pass_data_to_js( $template ) . '></div>';
		$body .= '</li>';
	}
	$body .= '</ul>';
}

$output = '<div class="us-bld-window for_templates type_htemplate">';
$output .= '<div class="us-bld-window-h">';

$output .= '<div class="us-bld-window-header">';
$output .= '<div class="us-bld-window-title">' . __( 'Header Templates', 'us' ) . '</div>';
$output .= '<div class="us-bld-window-closer" title="' . us_translate( 'Close' ) . '"></div>';
$output .= '</div>';

$output .= '<div class="us-bld-window-body">';
$output .= $body;
$output .= '<span class="usof-preloader"></span>';
$output .= '</div>';

$output .= '</div>';
$output .= '</div>';

echo $output;
