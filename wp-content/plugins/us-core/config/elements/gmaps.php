<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configuration for shortcode: gmaps
 */

$misc = us_config( 'elements_misc' );
$conditional_params = us_config( 'elements_conditional_options' );
$design_options_params = us_config( 'elements_design_options' );

/**
 * @return array
 */
return array(
	'title' => __( 'Map', 'us' ),
	'icon' => 'fas fa-map-marked-alt',
	'params' => us_set_params_weight(

		// General section
		array(
			'marker_address' => array(
				'title' => __( 'Address', 'us' ),
				'description' => __( 'Specify address in accordance with the format used by the national postal service of the country concerned.', 'us' ) . ' ' . __( 'Or use geo coordinates, for example:', 'us' ) . ' <span class="usof-example">38.6774156, 34.8520661</span>',
				'type' => 'text',
				'std' => '1600 Amphitheatre Parkway, Mountain View, CA 94043, United States',
				'dynamic_values' => array(
					'global' => array(),
					'post' => array(),
					'acf_types' => array( 'text', 'google_map' ),
				),
				'holder' => 'div',
				'classes' => 'for_above',
				'usb_preview' => TRUE,
			),
			'marker_text' => array(
				'title' => __( 'Marker Text', 'us' ),
				'description' => __( 'HTML tags are allowed.', 'us' ) . ' ' . sprintf( __( 'Use %s to show the address value.', 'us' ), '<span class="usof-example">{{address}}</span>' ),
				'type' => 'html',
				'encoded' => TRUE,
				'std' => base64_encode( '<h6>Hey, we are here!</h6><p>We will be glad to see you in our office.</p>' ),
				'classes' => 'vc_col-sm-12 pretend_textfield', // appearance fix in WPBakery editing window
				'usb_preview' => TRUE,
			),
			'show_infowindow' => array(
				'type' => 'switch',
				'switch_text' => __( 'Show Marker Text when map is loaded', 'us' ),
				'std' => 0,
				'show_if' => array( 'marker_text', '!=', '' ),
				'usb_preview' => TRUE,
			),
			'custom_marker_img' => array(
				'title' => __( 'Custom Marker Image', 'us' ),
				'type' => 'upload',
				'cols' => 2,
				'extension' => 'png,jpg,jpeg,gif,svg',
				'usb_preview' => TRUE,
			),
			'custom_marker_size' => array(
				'title' => __( 'Marker Image Size', 'us' ),
				'type' => 'select',
				'options' => array(
					'20' => '20px',
					'30' => '30px',
					'40' => '40px',
					'50' => '50px',
					'60' => '60px',
					'70' => '70px',
					'80' => '80px',
				),
				'std' => '30',
				'show_if' => array( 'custom_marker_img', '!=', '' ),
				'cols' => 2,
				'usb_preview' => TRUE,
			),

			// Additional Markers
			'markers' => array(
				'type' => 'group',
				'show_controls' => TRUE,
				'is_accordion' => TRUE,
				'accordion_title' => 'marker_address',
				'params' => array(
					'marker_address' => array(
						'title' => __( 'Address', 'us' ),
						'description' => __( 'Specify address in accordance with the format used by the national postal service of the country concerned.', 'us' ) . ' ' . sprintf( __( 'Or use geo coordinates, for example: %s', 'us' ), '38.6774156, 34.8520661' ),
						'type' => 'text',
						'std' => '',
						'admin_label' => TRUE,
					),
					'marker_text' => array(
						'title' => __( 'Marker Text', 'us' ),
						'description' => __( 'HTML tags are allowed.', 'us' ),
						'type' => 'textarea',
						'std' => '',
						'classes' => 'vc_col-sm-12 pretend_textfield', // appearance fix in shortcode editing window
					),
					'marker_img' => array(
						'title' => __( 'Custom Marker Image', 'us' ),
						'type' => 'upload',
						'cols' => 2,
						'extension' => 'png,jpg,jpeg,gif,svg',
					),
					'marker_size' => array(
						'title' => __( 'Marker Image Size', 'us' ),
						'type' => 'select',
						'options' => array(
							'20' => '20px',
							'30' => '30px',
							'40' => '40px',
							'50' => '50px',
							'60' => '60px',
							'70' => '70px',
							'80' => '80px',
						),
						'std' => '30',
						'show_if' => array( 'marker_img', '!=', '' ),
						'cols' => 2,
					),
				),
				'std' => array(),
				'group' => __( 'Additional Markers', 'us' ),
				'usb_preview' => TRUE,
			),
		),

		// More options section
		array(
			'provider' => array(
				'title' => __( 'Map Provider', 'us' ),
				'type' => 'radio',
				'options' => array(
					'google' => __( 'Google Maps', 'us' ),
					'osm' => 'OpenStreetMap',
				),
				'std' => 'google',
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'type' => array(
				'title' => __( 'Map Type', 'us' ),
				'type' => 'select',
				'options' => array(
					'roadmap' => __( 'Roadmap', 'us' ),
					'terrain' => __( 'Roadmap + Terrain', 'us' ),
					'satellite' => __( 'Satellite', 'us' ),
					'hybrid' => __( 'Satellite + Roadmap', 'us' ),
				),
				'std' => 'roadmap',
				'show_if' => array( 'provider', '=', 'google' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'zoom' => array(
				'title' => __( 'Map Zoom', 'us' ),
				'type' => 'slider',
				'options' => array(
					'' => array(
						'min' => 1,
						'max' => 20,
					),
				),
				'std' => '14',
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'hide_controls' => array(
				'type' => 'switch',
				'switch_text' => __( 'Hide all map controls', 'us' ),
				'std' => 0,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'disable_zoom' => array(
				'type' => 'switch',
				'switch_text' => __( 'Disable map zoom on mouse wheel scroll', 'us' ),
				'std' => 0,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'disable_dragging' => array(
				'type' => 'switch',
				'switch_text' => __( 'Disable dragging on touch screens', 'us' ),
				'std' => 0,
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'map_style_json' => array(
				'title' => __( 'Map Style', 'us' ),
				'description' => sprintf( __( 'Check available styles on %s.', 'us' ), '<a href="https://snazzymaps.com/" target="_blank">snazzymaps.com</a>' ),
				'type' => 'html',
				'std' => '',
				'show_if' => array( 'provider', '=', 'google' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
			'layer_style' => array(
				'title' => __( 'Map Style', 'us' ),
				'description' => sprintf( __( 'Check available styles on %s.', 'us' ), '<a href="https://leaflet-extras.github.io/leaflet-providers/preview/" target="_blank">Leaflet Provider Demo</a>' ) . ' ' . __( 'Examples:', 'us' ) . ' https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
				'type' => 'text',
				'std' => '',
				'show_if' => array( 'provider', '=', 'osm' ),
				'group' => us_translate( 'Appearance' ),
				'usb_preview' => TRUE,
			),
		),

		$conditional_params,
		$design_options_params
	),

	// Not used params, required for correct fallback
	'fallback_params' => array(
		'source',
	),

	'usb_init_js' => '$elm.filter(\'.w-map.provider_google\').wGmaps();$elm.filter(\'.w-map.provider_osm\').wLmaps()',
);
