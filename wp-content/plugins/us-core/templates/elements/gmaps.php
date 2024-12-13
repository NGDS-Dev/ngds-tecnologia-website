<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_gmaps
 *
 * Dev note: if you want to change some of the default values or acceptable attributes, overload the shortcodes config.
 *
 * @param $confirm_load               string Block loading resources until a visitor click
 * @param $custom_marker_img          int Custom marker image (from WordPress media)
 * @param $custom_marker_size         int Custom marker size
 * @param $disable_dragging           bool Disable dragging on touch screens
 * @param $disable_zoom               bool Disable map zoom on mouse wheel scroll
 * @param $el_class                   string Extra class name
 * @param $height                     int Map height
 * @param $hide_controls              bool Hide all map controls
 * @param $layer_style                string Leaflet Map TileLayer
 * @param $map_bg_color               string Map Background Color
 * @param $map_style_json             string Map Style
 * @param $marker_address             string Marker 1 address
 * @param $marker_text                string Marker 1 text
 * @param $markers                    array Additional Markers
 * @param $provider                   string Map Provider: 'google' / 'osm'
 * @param $show_infowindow            bool Show Marker's InfoWindow
 * @param $type                       string Map type: 'roadmap' / 'satellite' / 'hybrid' / 'terrain'
 * @param $unlock_btn_style           int Unlock button style
 * @param $zoom                       int Map zoom
 *
 * @var   $classes        string Extend class names
 * @var   $content        string Shortcode's inner content
 * @var   $shortcode      string Current shortcode name
 * @var   $shortcode_base string The original called shortcode name (differs if called an alias)
 *
 * @filter 'us_maps_js_options' Allows to filter options, passed to JavaScript
 */

// If set custom field and it's array
if (
	$map_field = us_get_custom_field( $marker_address )
	AND is_array( $map_field )
) {
	$marker_address = us_arr_path( $map_field, 'address', '' );
} else { // .. else apply filters to address
	$marker_address = us_replace_dynamic_value( $marker_address );
}

// Don't output the element if the address is empty
if ( empty( $marker_address ) AND ! usb_is_post_preview() ) {
	return;
}

$_atts['class'] = 'w-map provider_' . $provider;
$_atts['class'] .= isset( $classes ) ? $classes : '';

// When some values are set in Design options, add the specific classes
if ( us_design_options_has_property( $css, 'font-size' ) ) {
	$_atts['class'] .= ' has_font_size';
}

/**
 * @var bool
 */
$vc_is_page_editable = function_exists( 'vc_is_page_editable' )
	? vc_is_page_editable()
	: FALSE;

// Set unique map ID
if ( empty( $el_id ) ) {
	if ( usb_is_post_preview() OR $vc_is_page_editable ) {
		$el_id = us_uniqid();
	} else {
		global $us_maps_index;
		$us_maps_index = isset( $us_maps_index ) ? ( $us_maps_index + 1 ) : 1;
		$el_id = 'us_map_' . $us_maps_index;
	}
}
$_atts['id'] = $el_id;

// Generate specific HTML for AMP version
if ( us_amp() ) {
	$_atts['src'] = '';
	$_atts['width'] = '1000';
	$_atts['height'] = '400';
	$_atts['layout'] = 'responsive';
	$_atts['sandbox'] = 'allow-scripts allow-same-origin allow-popups';
	$_atts['frameborder'] = '0';

	if ( $provider == 'google' ) {
		$query_args = array(
			'key' => us_get_option( 'gmaps_api_key', '' ),
			'q' => urlencode( strip_tags( $marker_address ) ), // URL encode for embed compatibility
			'zoom' => (int) $zoom,
		);
		$_atts['src'] = 'https://www.google.com/maps/embed/v1/place?' . build_query( $query_args );
	}

	if (
		$provider == 'osm'
		AND preg_match( '#([0-9]+.[0-9]+),(\s)?([0-9]+.[0-9]+)#', $marker_address, $matches ) // make sure we have coordinates, not address
	) {
		$query_args = array(
			'bbox' => us_map_get_bbox( $matches[1], $matches[3], (int) $zoom ),
			'layer' =>'mapnik',
			'marker' => $matches[1] . ',' . $matches[3],
		);
		$_atts['src'] = 'https://www.openstreetmap.org/export/embed.html?' . build_query( $query_args );
	}

	// Placeholder image required for case when map is too close to the top of the page
	$amp_img = '<amp-img layout="fill" src="' . us_get_img_placeholder( 'full', TRUE ) . '" placeholder></amp-img>';
	echo '<amp-iframe' . us_implode_atts( $_atts ) . '>' . $amp_img . '</amp-iframe>';

	return;
}

/**
 * Get compiled text for marker
 *
 * @param string $marker_text The marker text
 * @param string $marker_address
 * @return string Returns the text for the marker
 */
$func_compile_marker_text = function( $marker_text, $marker_address = '' ) {
	if ( empty( $marker_text ) ) {
		return '';
	}

	$marker_text = str_replace( '{{address}}', $marker_address, $marker_text );
	$marker_text = us_replace_dynamic_value( $marker_text );
	if ( strpos( $marker_text, '[' ) !== FALSE ) {
		$marker_text = do_shortcode( $marker_text );
	}
	return (string) $marker_text;
};

// Form all options needed for JS API
$map_options = array(
	'address' => $marker_address,
	'disableDragging' => (int) $disable_dragging,
	'disableZoom' => (int) $disable_zoom,
	'hideControls' => (int) $hide_controls,
	'zoom' => (int) $zoom,
	'markers' => array(
		array(
			'address' => $marker_address,
			'html' => $func_compile_marker_text( rawurldecode( base64_decode( $marker_text ) ), $marker_address ),
			'infowindow' => $show_infowindow,
		),
	),
);

// Additional markers
if ( is_string( $markers ) ) {
	$markers = json_decode( urldecode( $markers ), TRUE );
}
if ( ! is_array( $markers ) ) {
	$markers = array();
}

foreach ( $markers as $index => $marker ) {
	if ( empty( $marker['marker_address'] ) ) {
		continue;
	}
	/**
	 * Filter the included markers
	 *
	 * @param $marker['marker_address'] string Address
	 * @param $marker['marker_text'] string Marker Text
	 * @param $marker['marker_img'] string Marker Image
	 * @param $marker['marker_size'] string Marker Size
	 */
	$map_options['markers'][] = array(
		'address' => $marker['marker_address'],
		'html' => $func_compile_marker_text( $marker['marker_text'], $marker['marker_address'] ),
		'marker_img' => ! empty( $marker['marker_img'] )
			? wp_get_attachment_image_src( $marker['marker_img'], 'thumbnail' )
			: NULL,
		'marker_size' => ! empty( $marker['marker_size'] )
			? array( $marker['marker_size'], $marker['marker_size'] )
			: NULL,
	);
}

// Custom Marker Image
if ( $custom_marker_img_url = wp_get_attachment_image_url( $custom_marker_img, 'thumbnail' ) ) {
	$map_options['icon'] = array(
		'url' => $custom_marker_img_url,
		'size' => array( (int) $custom_marker_size, (int) $custom_marker_size ),
	);
}

// Add map type for Google Maps
if ( $provider == 'google' ) {
	$map_options['maptype'] = strtoupper( $type );
}

// Layer style, required for OSM
if ( $provider == 'osm' ) {
	if ( empty( $layer_style ) ) {
		$layer_style = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
	}
	$map_options['style'] = $layer_style;
}

/**
 * @var array
 */
$map_options = (array) apply_filters( 'us_maps_js_options', $map_options, get_the_ID(), $_atts['id'] );

// Enqueue relevant scripts
$is_ajax_load_js = us_get_option( 'ajax_load_js', 0 ) == 0;
if ( $provider == 'google' ) {
	wp_enqueue_script( 'us-google-maps' ); // Note: Connection initializes in JS
	if ( $is_ajax_load_js ) {
		wp_enqueue_script( 'us-gmap' );
	}
} elseif ( $provider == 'osm' AND $is_ajax_load_js ) {
	wp_enqueue_script( 'us-lmap' );
}

// Map output
$output = '<div class="w-map-inner">';
$output .= '<div class="w-map-json"' . us_pass_data_to_js( $map_options ) . '></div>';
if ( $provider == 'google' AND $map_style_json != '' ) {
	$map_style_json = str_replace( "'", '&#39;', rawurldecode( base64_decode( $map_style_json ) ) );
	$output .= '<div class="w-map-style-json" onclick=\'return ' . $map_style_json . '\'></div>';
}
$output .= '</div>';

// If we are in WPB front end editor mode, apply JS to maps
if ( $vc_is_page_editable ) {
	if ( $provider == 'osm' ) {
		$js_output = '
			if ( typeof $ush !== "undefined" && ! $ush.isUndefined( ( $us || {} ).wLmaps ) ) {
				jQuery("#' . $el_id . '").wLmaps();
			}
		';
	} else {
		$js_output = '
			if ( typeof $ush !== "undefined" && ! $ush.isUndefined( ( $us || {} ).wGmaps ) ) {
				jQuery("#' . $el_id . '").wGmaps();
			}
		';
	}
	$output .= '<script>' . $js_output . '</script>';
}

/**
 * @var string The name of the cookie under which the permission will be saved
 */
$provider_cookie_name = sprintf( '%s_load_confirmed', $provider );

$show_privacy_block = ( us_get_option( 'block_third_party_files', 0 ) AND ! us_arr_path( $_COOKIE, $provider_cookie_name ) );

// Apply filter for 3rd-parties usage
$show_privacy_block = apply_filters( 'us_show_privacy_block', $show_privacy_block, $provider );

// Output a template to be initialized on the JavaScript side
if ( $show_privacy_block ) {

	// Overwriting the output and encoding of the map body
	$output = '<script type="text/template">' . base64_encode( $output ) . '</script>';

	$privacy_config = us_config( 'providers_privacy_policy.' . $provider );

	// Privacy Notification Block
	$output .= '<div class="w-map-privacy">';
	$output .= '<p>';
	$output .= sprintf( strip_tags( __( 'By loading this map, you agree to the privacy policy of %s.', 'us' ) ), '<a href="' . esc_url( $privacy_config['privacy_policy'] ) . '" target="_blank" rel="nofollow noreferrer">' . $privacy_config['title'] . '</a>' );
	$output .= '</p>';
	$output .= '<p><label>';
	$output .= '<input type="checkbox" name="' . $provider_cookie_name . '[' . $el_id . ']" value="0">';
	$output .= '<span>' . strip_tags( __( 'Always load such maps on this site', 'us' ) ) . '</span>';
	$output .= '</label></p>';
	$output .= '<p><button class="w-btn ' . us_get_btn_class() . ' action_confirm_load" type="button">';
	$output .= '<span class="w-btn-label">' . strip_tags( __( 'Load Map', 'us' ) ) . '</span>';
	$output .= '</button></p>';
	$output .= '</div>'; // .w-map-privacy

	// The class that the element requires a load confirmation
	$_atts['class'] .= ' confirm_load';

	// The cookie name to save the load consent
	$_atts['data-cookie-name'] = $provider_cookie_name;
}

echo '<div' . us_implode_atts( $_atts ) . '>' . $output . '</div>';
