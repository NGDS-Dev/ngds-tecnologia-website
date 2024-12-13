<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Live Options - Settings that are editable via Edit Live with preview capability
 */

/* Site Layout
--------------------------------------------------------------------------------------------------------*/
$site_layout_config = us_config( 'theme-options.layout' );

// Unset notification
unset( $site_layout_config['fields']['layout_head_message'] );

/* Typography
--------------------------------------------------------------------------------------------------------*/
$typography_config = us_config( 'theme-options.typography' );

// Unset unnedeed options
unset( $typography_config['fields']['typography_head_message'] );
unset( $typography_config['fields']['h_typography_3'] );
unset( $typography_config['fields']['custom_font'] );
unset( $typography_config['fields']['h_typography_4'] );
unset( $typography_config['fields']['uploaded_fonts'] );
unset( $typography_config['fields']['h_typography_5'] );
unset( $typography_config['fields']['font_display'] );

return array(
	'layout' => $site_layout_config,
	'typography' => $typography_config,
);
