<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Font
 *
 * Select font
 *
 * @param $field ['title'] string Field title
 * @param $field ['description'] string Field title
 * @param $field ['preview'] array
 * @param $field ['preview']['text'] string Preview text
 * @param $field ['preview']['size'] string Font size in css format
 *
 * @var   $name  string Field name
 * @var   $id    string Field ID
 * @var   $field array Field options
 *
 * @var   $value array List of checked keys
 */

$font_value = explode( '|', $value );
if ( ! isset( $font_value[1] ) OR empty( $font_value[1] ) ) {
	$font_value[1] = '400,700';
}
$font_value = array(
	'name' => $font_value[0],
	'weights' => explode( ',', $font_value[1] ),
);

// Get Google fonts
$google_fonts = $google_fonts_data = array();
foreach( us_config( 'google-fonts' ) as $font_family => $font_data ) {
	$key = esc_attr( $font_family );
	$google_fonts_data[ $key ] = $font_data;
	$google_fonts[ $key ] = $font_family;
}

if ( ! isset( $font_weights ) ) {
	$font_weights = array(
		'100' => '100 ' . __( 'thin', 'us' ),
		'100italic' => '100 ' . __( 'thin', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'200' => '200 ' . __( 'extra-light', 'us' ),
		'200italic' => '200 ' . __( 'extra-light', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'300' => '300 ' . __( 'light', 'us' ),
		'300italic' => '300 ' . __( 'light', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'400' => '400 ' . __( 'normal', 'us' ),
		'400italic' => '400 ' . __( 'normal', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'500' => '500 ' . __( 'medium', 'us' ),
		'500italic' => '500 ' . __( 'medium', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'600' => '600 ' . __( 'semi-bold', 'us' ),
		'600italic' => '600 ' . __( 'semi-bold', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'700' => '700 ' . __( 'bold', 'us' ),
		'700italic' => '700 ' . __( 'bold', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'800' => '800 ' . __( 'extra-bold', 'us' ),
		'800italic' => '800 ' . __( 'extra-bold', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
		'900' => '900 ' . __( 'ultra-bold', 'us' ),
		'900italic' => '900 ' . __( 'ultra-bold', 'us' ) . ' <i>' . __( 'italic', 'us' ) . '</i>',
	);
}
$fontVariants = us_arr_path( $google_fonts_data, $font_value['name'] . '.variants', array() );

// Unified export of data for Google fonts
global $us_export_google_font_data;
if ( is_null( $us_export_google_font_data ) ) {
	$us_export_google_font_data = TRUE;
	echo '<script>
		window.$usof = window.$usof || {}; window.$usof.googleFonts = ' . json_encode( $google_fonts_data ) . ';
	</script>';
}

$output = '<div class="usof-font">';
$output .= '<input type="hidden" name="' . $name . '" value="' . esc_attr( $value ) . '" />';
if ( ! empty( $field['preview_text'] ) ) {
	$output .= us_load_template( 'usof/templates/fields/preview_text', $field['preview_text'] );
}
// Field for get font name through autocomplete
$output .= '<div class="type_autocomplete" data-name="font_name">';
$output .= us_get_template( 'usof/templates/fields/autocomplete', array(
	'value' => $font_value['name'],
	'field' => array(
		'value_separator' => '|',
		'options' => $google_fonts,
	),
) );
$output .= '</div>'; // .type_autocomplete
$output .= '<ul class="usof-checkbox-list">';
foreach ( $font_weights as $font_weight => $font_title ) {
	$font_weight = (string) $font_weight;
	$checkbox_atts = array(
		'class' => 'usof-checkbox',
		'data-value' => $font_weight,
	);
	if ( ! in_array( $font_weight, $fontVariants ) ) {
		$checkbox_atts['class'] .= ' hidden';
	}
	$output .= '<li ' . us_implode_atts( $checkbox_atts ) . '>';
	$output .= '<label>';
	$output .= '<input type="checkbox" value="' . $font_weight . '"';
	if ( array_search( $font_weight, $font_value['weights'], TRUE ) !== FALSE ) {
		$output .= ' checked';
	}
	$output .= '>';
	$output .= '<span class="usof-checkbox-text">';
	$output .= $font_title . '</span></label></li>';
}
$output .= '</ul>'; // .usof-checkbox-list
$output .= '</div>'; // .usof-font
echo $output;
