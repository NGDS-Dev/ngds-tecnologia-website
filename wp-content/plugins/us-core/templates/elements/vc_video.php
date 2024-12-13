<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_btn
 *
 * Dev note: if you want to change some of the default values or acceptable attributes, overload the shortcodes config.
 *
 * @var $shortcode      string Current shortcode name
 * @var $shortcode_base string The original called shortcode name (differs if called an alias)
 * @var $content        string Shortcode's inner content
 * @var $classes        string Extend class names
 *
 * @var $link           string Video link
 * @var $ratio          string Ratio: '16x9' / '4x3' / '3x2' / '1x1'
 * @var $ratio_width    string Ratio custom width
 * @var $ratio_height   string Ratio custom height
 * @var $max_width      string Max width in pixels
 * @var $align          string Video alignment: 'left' / 'center' / 'right'
 * @var $css            string Extra css
 * @var $el_id          string element ID
 * @var $el_class       string Extra class name
 * @var $source         string Iframe from custom field
 * @var $controls       int Hide/show the player controls: 0 / 1
 * @var $autoplay       int Start playing video immediately after the player is loaded: 0 / 1
 * @var $loop           int Video loop playback: 0 / 1
 * @var $muted          int Mute the video: 0 / 1
 */

$_atts['class'] = 'w-video';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['class'] .= ' align_' . $align;
$_atts['class'] .= ' ratio_' . $ratio;

// When some values are set in Design options, add the specific classes
if ( us_design_options_has_property( $css, 'border-radius' ) ) {
	$_atts['class'] .= ' has_border_radius';
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Image Overlay
if (
	$overlay_image = us_replace_dynamic_value( $overlay_image, /* acf_format */ FALSE )
	AND ! us_amp()
) {
	$overlay_image_src = wp_get_attachment_image_url( $overlay_image, 'full' );

	if ( empty( $overlay_image_src ) ) {
		$overlay_image_src = us_get_img_placeholder( 'full', TRUE );
	}

	$_atts['class'] .= ' with_overlay';
	$_atts['style'] = 'background-image:url(' . $overlay_image_src . ');';
}

// Empty embed by default because video can be loaded with JS
$embed_html = '';

// Apply filter
$link = strip_tags( us_replace_dynamic_value( $link, FALSE ) );

// Check providers
if ( ! empty( $link ) ) {
	foreach ( us_config( 'embeds' ) as $provider => $embed ) {
		// If there is no video ID then skip iteration
		if ( ! preg_match( $embed['url_regex'], $link, $matches ) ) {
			continue;
		}
		$video_id = $matches[/* video_id */1];

		// A class for defining video to iframe
		$_atts['class'] .= ' has_iframe';

		// Get a unique ID for an video player
		$player_id = $provider . '-' . us_uniqid();

		// Get HTML/JS code to init the player
		$player_html = us_arr_path( $embed, 'player_html', '' );

		// Get raw iframe markup to show as is
		if ( ! $overlay_image ) {
			$embed_html = us_arr_path( $embed, 'iframe_html', '' );
		}

		// Player variables
		$player_vars = us_arr_path( $embed, 'player_vars', array() );

		// https://developers.google.com/youtube/player_parameters?hl=ru#Parameters
		if ( $provider == 'youtube' ) {
			$player_vars['origin'] =  get_home_url();

			// https://developer.vimeo.com/player/sdk/embed
		} elseif ( $provider == 'vimeo' ) {
			$player_vars['byline'] = (int) ! $hide_video_title;
			$player_vars['title'] = (int) ! $hide_video_title;
		}

		// Configuring the Player Settings
		foreach ( array( 'controls', 'autoplay', 'loop', 'muted' ) as $prop ) {
			if ( isset( ${$prop} ) ) {
				$var_name = $prop;
				if ( $prop == 'muted' AND $provider == 'youtube' ) {
					$var_name = 'mute';
				}
				$player_vars[ $var_name ] = (int) ${$prop};
			}
		}

		// If an overlay is used, then set autoplay
		if ( $overlay_image AND ! us_amp() ) {
			$player_vars['autoplay'] = 1;
		}

		// Set a playlist for YouTube
		if (
			$provider == 'youtube'
			AND ( $overlay_image OR ! empty( $player_vars['loop'] ) )
		) {
			$player_vars['playlist'] = $video_id;
		}

		// Set hash key for vimeo privacy video
		if ( $provider == 'vimeo' AND $privacy_id = call_user_func( $embed['get_video_privacy'], $link ) ) {
			$player_vars['h'] = $privacy_id;
		}

		// Unset autoplay for the Live Edit
		if ( usb_is_post_preview() ) {
			unset( $player_vars['autoplay'] );
		}

		// Set value to <variable>
		$variables = array(
			'video_id' => $video_id,
			'player_id' => $player_id,
			'player_vars' => json_encode( $player_vars ),
			'player_url_params' => build_query( $player_vars ),
		);

		foreach ( $variables as $variable => $value ) {
			$player_html = str_replace( "<{$variable}>", $value, $player_html );
			$embed_html = str_replace( "<{$variable}>", $value, $embed_html );
		}

		// Export data to JS
		$js_data = array(
			'player_id' => $player_id,
			'player_api' => us_arr_path( $embed, 'player_api', '' ),
			'player_html' => $player_html,
		);

		$_atts['onclick'] = us_pass_data_to_js( $js_data, /* onclicks */ FALSE );

		// One successful iteration is enough
		break;
	}

	// Do not output the element with empty link
} elseif ( ! usb_is_post_preview() ) {
	return;
}

if ( empty( $_atts['onclick'] ) ) {
	if ( preg_match( '/^.*\.(mp4|m4v|webm|ogv|flv)$/i', $link ) ) {

		// Set default value for $provider
		$provider = 'file';

		// Default attributes for the <video>
		$video_atts = array(
			'preload' => 'auto',
			'playsinline' => '',
		);

		foreach ( array( 'controls', 'autoplay', 'loop', 'muted' ) as $prop ) {
			if ( ! empty( ${$prop} ) ) {
				$video_atts[ $prop ] = '';
			}
		}

		// Unset autoplay for the Live Builder
		if ( usb_is_post_preview() ) {
			unset( $video_atts['autoplay'] );
		}

		$embed_html = '<video ' . us_implode_atts( $video_atts ) . '>';

		$video_ext = 'mp4'; // use mp4 as default extension
		$file_path_info = pathinfo( $link );
		if ( isset( $file_path_info['extension'] ) ) {
			if ( in_array( $file_path_info['extension'], array( 'ogg', 'ogv' ) ) ) {
				$video_ext = 'ogg';
			} elseif ( $file_path_info['extension'] == 'webm' ) {
				$video_ext = 'webm';
			}
		}

		$embed_html .= '<source type="video/' . $video_ext . '" src="' . $link . '" />';
		$embed_html .= '</video>';

		$js_data = array(
			'player_html' => $embed_html,
		);

	} else {
		global $wp_embed;

		// Determining if this is iframe
		$provider = 'embed';

		// Using the default WordPress way
		$embed_html = do_shortcode( $wp_embed->run_shortcode( '[embed]' . $link . '[/embed]' ) );
		$js_data = array(
			'player_html' => $embed_html,
		);
	}

	$_atts['onclick'] = us_pass_data_to_js( $js_data, FALSE );
}

/**
 * @var string The name of the cookie under which the permission will be saved
 */
$provider_cookie_name = sprintf( '%s_load_confirmed', $provider );

$show_privacy_block = (
	$provider !== 'file'
	AND us_get_option( 'block_third_party_files', 0 )
	AND ! us_arr_path( $_COOKIE, $provider_cookie_name )
);

// Apply filter for 3rd-parties usage
$show_privacy_block = apply_filters( 'us_show_privacy_block', $show_privacy_block, $provider );

// Output a template to be initialized on the JavaScript side
if ( $show_privacy_block ) {
	$privacy_config = us_config( 'providers_privacy_policy.' . $provider );

	// Overwriting the output and encoding the element html
	$embed_html = '<script type="text/template">' . base64_encode( $embed_html ) . '</script>';

	// Privacy message from a config
	$privacy_message = '';
	if ( ! empty( $privacy_config ) ) {
		$privacy_message = sprintf( __( 'By loading this video, you agree to the privacy policy of %s.', 'us' ), '<a href="' . esc_url( $privacy_config['privacy_policy'] ) . '" target="_blank" rel="nofollow noreferrer">' . $privacy_config['title'] . '</a>' );

		// ... or default message for cases without config
	} elseif ( $parse_link = parse_url( $link ) ) {
		$privacy_message = sprintf( __( 'By loading this video, you agree to the privacy policy of %s.', 'us' ), $parse_link['host'] );
	}

	// Privacy Notification Block
	$embed_html .= '<div class="w-video-privacy">';
	$embed_html .= '<p>' . $privacy_message . '</p>';
	if ( ! empty( $privacy_config ) ) {
		$embed_html .= '<p><label>';
		$embed_html .= '<input type="checkbox" name="' . $provider_cookie_name . '[' . $el_id . ']" value="0">';
		$embed_html .= '<span>' . sprintf( __( 'Always load %s videos on this site.', 'us' ), $privacy_config['title'] ) . '</span>';
		$embed_html .= '</label></p>';
	}
	$embed_html .= '<p><button class="w-btn ' . us_get_btn_class() . ' action_confirm_load" type="button">';
	$embed_html .= '<span class="w-btn-label">' . strip_tags( __( 'Load Video', 'us' ) ) . '</span>';
	$embed_html .= '</button></p>';
	$embed_html .= '</div>';

	// The class that the element requires a load confirmation
	$_atts['class'] .= ' confirm_load';

	// The cookie name to save the load consent
	$_atts['data-cookie-name'] = $provider_cookie_name;
}

if ( ! $overlay_image ) {
	unset( $_atts['onclick'] );
}

// Generate inline style for custom aspect ratio
$ratio_style = '';
if ( $ratio == 'custom' ) {
	$ratio_array = us_get_aspect_ratio_values( $ratio, $ratio_width, $ratio_height );
	$ratio_style = ' style="padding-bottom:' . round( $ratio_array[1] / $ratio_array[0] * 100, 4 ) . '%"';
}

$output = '<div' . us_implode_atts( $_atts ) . '>';
$output .= '<div class="w-video-h"' . $ratio_style . '>' . $embed_html . '</div>';

// Add play icon in output
if ( $overlay_icon AND ! us_amp() ) {
	$tag_style = '';
	if ( ! empty( $overlay_icon_size ) ) {
		$tag_style .= 'font-size:' . esc_attr( $overlay_icon_size ) . ';';
	}
	if ( ! empty( $overlay_icon_bg_color ) ) {
		$tag_style .= 'background:' . us_get_color( $overlay_icon_bg_color, /* Gradient */ TRUE ) . ';';
	}
	if ( ! empty( $overlay_icon_text_color ) ) {
		$tag_style .= 'color:' . us_get_color( $overlay_icon_text_color );
	}
	$output .= '<div class="w-video-icon" style="' . $tag_style . '"></div>';
}
$output .= '</div>';

echo $output;
