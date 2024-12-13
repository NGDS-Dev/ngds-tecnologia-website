<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: vc_row
 *
 * Overloaded by UpSolution custom implementation to allow creating fullwidth sections and provide lots of additional
 * features.
 *
 * Dev note: if you want to change some of the default values or acceptable attributes, overload the shortcodes config.
 *
 * @var $shortcode                    string Current shortcode name
 * @var $shortcode_base               string The original called shortcode name (differs if called an alias)
 * @var $content                      string Shortcode's inner content
 * @var $content_placement            string Columns Content Position: 'top' / 'middle' / 'bottom'
 * @var $columns_gap                  string gap class for columns
 * @var $height                       string Height type. Possible values: 'default' / 'small' / 'medium' / 'large' / 'huge' / 'auto' /  'full'
 * @var $valign                       string Vertical align for full-height sections: '' / 'center'
 * @var $width                        string Section width: '' / 'full'
 * @var $color_scheme                 string Color scheme: '' / 'alternate' / 'primary' / 'secondary' / 'custom'
 * @var $us_bg_image_source           string Background image source: 'none' / 'media' / 'featured' / 'custom'
 * @var $us_bg_image                  int Background image ID (from WordPress media)
 * @var $us_bg_size                   string Background size: 'cover' / 'contain' / 'initial'
 * @var $us_bg_repeat                 string Background size: 'repeat' / 'repeat-x' / 'repeat-y' / 'no-repeat'
 * @var $us_bg_pos                    string Background position: 'top left' / 'top center' / 'top right' / 'center left' / 'center center' / 'center
                                             right' /  'bottom left' / 'bottom center' / 'bottom right'
 * @var $us_bg_parallax               string Parallax type: '' / 'vertical' / 'horizontal' / 'still'
 * @var $us_bg_parallax_width         string Parallax background width: '110' / '120' / '130' / '140' / '150'
 * @var $us_bg_parallax_reverse       bool Reverse vertival parllax effect?
 * @var $us_bg_video                  string Link to video file
 * @var $us_bg_overlay_color          string
 * @var $sticky                       bool Fix this row at the top of a page during scroll
 * @var $sticky_disable_width         int When screen width is less than this value, sticky row becomes not sticky
 * @var $us_bg_video_disable_width    int When screen width is less than this value, video will be replaced with background image
 * @var $el_id                        string
 * @var $el_class                     string
 * @var $css                          string
 * @var $us_shape_show_top            string Is display Shape top Divider value '1' / '0'
 * @var $us_shape_show_bottom         string Is display Shape bottom Shape Divider value '1' / '0'
 * @var $us_shape_top                 string Shape Divider type: 'curve' / 'triangle'
 * @var $us_shape_bottom              string Shape Divider type: 'curve' / 'triangle'
 * @var $us_shape_custom_top          string Shape Divider id of media attached file
 * @var $us_shape_custom_bottom       string Shape Divider id of media attached file
 * @var $us_shape_height_top          string Shape Divider height in vh '15vh' / '25vh'
 * @var $us_shape_height_bottom       string Shape Divider height in vh '15vh' / '25vh'
 * @var $us_shape_color_top           string Shape Divider color
 * @var $us_shape_color_bottom        string Shape Divider color
 * @var $us_shape_overlap_top         string Shape Divider on front or no
 * @var $us_shape_overlap_bottom      string Shape Divider on front or no
 * @var $us_shape_flip_top            string Shape Divider invert layout
 * @var $us_shape_flip_bottom         string Shape Divider invert layout
 * @var $_atts['class']               string Extend class names
 * @var $conditions_operator          string Conditions operator to be used for validation
 * @var $conditions                   string List of conditions
 *
 * @var $us_shape_bring_to_front string Bring to front element
 */

// Check the inner content for Reusable Blocks ans Post Content with the parent Row excluded,
// if so, output these Reusable Blocks and Post Content only
if (
	preg_match_all( '/\[(us_\w+)\s(.*remove_rows="parent_row"?[^\]]+)\]/', $content, $shortcode_matches )
	AND ! usb_is_post_preview()
) {
	$new_content = '';
	$shortcodes_regex = get_shortcode_regex( $shortcode_matches[1] );

	if ( preg_match_all( '/' . $shortcodes_regex . '/', $content, $matches, PREG_PATTERN_ORDER ) ) {
		foreach ( us_arr_path( $matches, '0', array() ) as $item_shortcode ) {
			if ( strpos( $item_shortcode, 'remove_rows="parent_row"' ) !== FALSE ) {
				$new_content .= $item_shortcode;
			}
		}
	}
	echo do_shortcode( $new_content );

	return;
}

// Class "wpb_row" is required for correct output of some plugins, like Ultimate Addons
$_atts['class'] = 'l-section wpb_row';

// Disable Row if set, works in both builders
if ( ! empty( $atts['disable_element'] ) ) {
	if ( usb_is_post_preview() ) {
		$_atts['class'] .= ' disabled_for_usb';
	} elseif ( function_exists( 'vc_is_page_editable' ) AND vc_is_page_editable() ) {
		$_atts['class'] .= ' vc_hidden-lg vc_hidden-md vc_hidden-sm vc_hidden-xs';
	} else {
		return '';
	}
}

// Fallback for old full height value (after version 8.0)
if ( $height == 'full' ) {
	$full_height = TRUE;
	$height = 'medium';

	if ( ! empty( $atts['valign'] ) ) {
		$v_align = $atts['valign'];
	} else {
		$v_align = 'top';
	}
}

// Fallback for old full width value (after version 8.13)
if ( ! empty( $width ) AND $width == '1' ) {
	$width = 'full';
}

$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( $height == 'default' ) {
	$_atts['class'] .= ' height_' . us_get_option( 'row_height', 'medium' );
} else {
	$_atts['class'] .= ' height_' . $height;
}
if ( $full_height ) {
	$_atts['class'] .= ' full_height valign_' . $v_align;
}
if ( $width != 'default' ) {
	$_atts['class'] .= ' width_' . $width;
	if ( $width == 'custom' ) {
		$_atts['style'] = '--site-content-width:' . $width_custom . ';';
	}
}
if ( $color_scheme != '' ) {
	$_atts['class'] .= ' color_' . $color_scheme;
}
if ( $sticky ) {
	$_atts['class'] .= ' type_sticky';
}
if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Generate Background Image output
// Media library source
if ( $us_bg_image_source == 'media' ) {
	$bg_img_src = wp_get_attachment_image_src( $us_bg_image, 'full' );

	// Use placeholder, if the specified image doesn't exist
	if ( ! empty( $us_bg_image ) AND ! $bg_img_src ) {
		$bg_img_url = us_get_img_placeholder( 'full', TRUE );
	}

	// Featured image source
} elseif ( $us_bg_image_source == 'featured' ) {

	$current_id = us_get_current_id();

	// Use placeholder for the Live Builder preview
	if ( usb_is_template_preview() ) {
		$bg_img_url = us_get_img_placeholder( 'full', TRUE );

		// Use the Product Category thumbnail as a Featured image
	} elseif (
		class_exists( 'woocommerce' )
		AND is_product_category()
		AND $term_thumbnail_id = get_term_meta( $current_id, 'thumbnail_id', TRUE )
	) {
		$bg_img_src = wp_get_attachment_image_src( $term_thumbnail_id, 'full' );

	} else {
		$bg_img_src = wp_get_attachment_image_src( get_post_thumbnail_id( $current_id ), 'full' );
	}

	// Custom field source
} elseif ( $us_bg_image_source != 'none' ) {

	// Use placeholder for the Live Builder preview
	if ( usb_is_template_preview() ) {
		$bg_img_url = us_get_img_placeholder( 'full', TRUE );
	}

	// Get custom value
	$_img_id = us_get_custom_field( $us_bg_image_source, FALSE );

	// If it is an ID - getting attachment by ID
	if ( is_numeric( $_img_id ) ) {
		$bg_img_src = wp_get_attachment_image_src( $_img_id, 'full' );

		// Otherwise checking it is a scalar value (not array or object) and using value as URL
	} elseif ( is_scalar( $_img_id ) ) {
		$bg_img_url = (string) $_img_id;
	}
}

// Get background image attributes
$bg_img_atts = array(
	'class' => 'l-section-img',
	'role' => 'img',
);
if ( ! empty( $bg_img_src ) ) {
	$bg_img_url = $bg_img_src[0];
	$bg_img_atts['data-img-width'] = $bg_img_src[1];
	$bg_img_atts['data-img-height'] = $bg_img_src[2];
}

// Generate background html, if the image exists
$bg_img_html = '';
if ( ! empty( $bg_img_url ) ) {
	$_atts['class'] .= ' with_img';
	$bg_img_atts['style'] = 'background-image: url(' . esc_url( $bg_img_url ) . ');';
	if ( $us_bg_pos != 'center center' ) {
		$bg_img_atts['style'] .= 'background-position: ' . $us_bg_pos . ';';
	}
	if ( $us_bg_repeat != 'repeat' ) {
		$bg_img_atts['style'] .= 'background-repeat: ' . $us_bg_repeat . ';';
	}
	if ( $us_bg_size == 'initial' ) {
		$bg_img_atts['style'] .= 'background-size: auto;'; // change to the correct default value
	} elseif ( $us_bg_size != 'cover' ) {
		$bg_img_atts['style'] .= 'background-size: ' . $us_bg_size . ';';
	}
	$bg_img_html = '<div' . us_implode_atts( $bg_img_atts ) . '></div>';
}

$bg_slider_html = $bg_video_html = '';
if ( ! us_amp() ) {

	// Background Video
	if ( $us_bg_show == 'video' AND $us_bg_video ) {
		$_atts['class'] .= ' with_video';

		// Remove css units if any
		$us_bg_video_disable_width = (int) $us_bg_video_disable_width;

		// Apply filter to video link
		$us_bg_video = us_replace_dynamic_value( $us_bg_video, FALSE );

		// If the "Hide video at screen width" set as zero, do not output the video on mobile devices
		if (
			$us_bg_video == ''
			OR (
				$us_bg_video_disable_width == '0'
				AND wp_is_mobile()
			)
		) {
			$bg_video_html = '';

		} else {
			$uniqid = us_uniqid();
			$bg_video_atts = array( 'class' => 'l-section-video', );

			// Hide the videos on a specified screen width
			if ( $us_bg_video_disable_width ) {
				$bg_video_atts['id'] = 'us_bg_video_' . $uniqid;
				$bg_video_html .= '<style>@media(max-width:' . $us_bg_video_disable_width . 'px){#' . $bg_video_atts['id'] . '{display:none!important}}</style>';
			}

			// Check providers and get params
			$provider = $video_id = $vimeo_privacy_id = '';
			foreach ( us_config( 'embeds' ) as $_provider => $embed ) {
				// If there is no video ID then skip iteration
				if ( ! preg_match( $embed['url_regex'], $us_bg_video, $matches ) ) {
					continue;
				}
				$video_id = $matches[1];
				$provider = $_provider;
				// Get hash key for vimeo privacy video
				if ( $provider == 'vimeo' AND $privacy_id = call_user_func( $embed['get_video_privacy'], $us_bg_video ) ) {
					$vimeo_privacy_id = $privacy_id;
				}
				$_atts['class'] .= ' with_' . $provider;
				break;
			}

			// Youtube Player: https://developers.google.com/youtube/player_parameters?hl=ru#Parameters
			if ( $provider == 'youtube' ) {
				$bg_video_html .= '<script id="youtube_' . $uniqid . '">
					window.onYouTubeIframeAPIReady =  function() {
						var player = new YT.Player( "youtube_' . $uniqid . '" , {
							videoId: "' . $video_id . '",
							playerVars: {
								autohide: 1,
								autoplay: 1,
								mute: 1,
								controls: 0,
								disablekb: 1,
								enablejsapi: 1,
								fs: 0,
								loop: 1,
								modestbranding: 1,
								playlist: "' . $video_id . '",
								rel: 0,
								showinfo: 0,
							},
							events: {
								onReady: function ( e ) {
									e.target.mute();
									e.target.playVideo();
									e.target.g.allow = "autoplay; fullscreen; picture-in-picture";
									e.target.g.width = window.innerWidth;
									e.target.g.loading = "lazy";
								}
							}
						});
						document.querySelector( "script#youtube_api_'. $uniqid .'" ).remove();
					};
					var script = document.createElement( "script" );
					script.id = "youtube_api_' . $uniqid . '";
					script.src = "https://www.youtube.com/iframe_api";
					document.head.append( script );
				</script>';
			}

			// Vimeo Player: https://github.com/vimeo/player.js and https://developer.vimeo.com/player/sdk/embed
			elseif ( $provider == 'vimeo' ) {
				$bg_video_html .= '<script class="vimeo_' . $uniqid . '">
					var script = document.createElement( "script" );
					script.classList.add( "vimeo_' . $uniqid . '" );
					script.src = "https://player.vimeo.com/api/player.js";
					script.onload = function() {
						var player = new Vimeo.Player( "' . $bg_video_atts['id'] . '", {
							controls: false,
							autoplay: true,
							byline: false,
							id: "' . $video_id . '",
							loop: true,
							muted: true,
							title: false,
							width: window.innerWidth,
						} );
						player.ready().then( function() {
							player.element.allow = "autoplay; fullscreen; picture-in-picture";
							player.element.loading = "lazy";
						} );
						document.querySelectorAll( "script.vimeo_'. $uniqid .'" ).forEach( function( node ) { node.remove() } );
					};
					document.head.append( script );
				</script>';
			}

			// HTML5 Player: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/video
			else {
				$player_vars = array(
					'autoplay' => '',
					'loop' => '',
					'muted' => '',
					'playsinline' => '',
					'preload' => 'auto',
				);
				// Determine file extension
				$video_ext = 'mp4'; // use mp4 as default extension
				$file_path_info = pathinfo( $us_bg_video );
				if ( isset( $file_path_info['extension'] ) ) {
					if ( in_array( $file_path_info['extension'], array( 'ogg', 'ogv' ) ) ) {
						$video_ext = 'ogg';
					} elseif ( $file_path_info['extension'] == 'webm' ) {
						$video_ext = 'webm';
					}
				}
				$bg_video_html .= '<video ' . us_implode_atts( $player_vars ) . '>';
				$bg_video_html .= '<source type="video/' . $video_ext . '" src="' . $us_bg_video . '" />';
				$bg_video_html .= '</video>';
			}

			$bg_video_html = '<div' . us_implode_atts( $bg_video_atts ) . '>' . $bg_video_html . '</div>';
		}

		// Apply parallax effect for cases without background video
	} else {
		if ( $us_bg_parallax == 'vertical' ) {
			$_atts['class'] .= ' parallax_ver';
			if ( $us_bg_parallax_reverse ) {
				$_atts['class'] .= ' parallaxdir_reversed';
			}
			if ( in_array( $us_bg_pos, array( 'top right', 'center right', 'bottom right' ) ) ) {
				$_atts['class'] .= ' parallax_xpos_right';
			} elseif ( in_array( $us_bg_pos, array( 'top left', 'center left', 'bottom left' ) ) ) {
				$_atts['class'] .= ' parallax_xpos_left';
			}
		} elseif ( $us_bg_parallax == 'fixed' OR $us_bg_parallax == 'still' ) {
			$_atts['class'] .= ' parallax_fixed';
		} elseif ( $us_bg_parallax == 'horizontal' ) {
			$_atts['class'] .= ' parallax_hor';
			$_atts['class'] .= ' bgwidth_' . (int) $us_bg_parallax_width;
		}
	}

	// Image Slider
	if (
		! us_amp()
		AND $us_bg_show == 'img_slider'
		AND $us_bg_slider_ids = us_replace_dynamic_value( $us_bg_slider_ids, /* acf_format */ FALSE )
	) {
		$_atts['class'] .= ' with_slider';
		$img_slider_shortcode = '[us_image_slider';
		$img_slider_shortcode .= ' ids="' . $us_bg_slider_ids . '"';
		$img_slider_shortcode .= ' transition="' . $us_bg_slider_transition . '"';
		$img_slider_shortcode .= ' transition_speed="' . (int) $us_bg_slider_speed . '"';
		$img_slider_shortcode .= ' autoplay_period="' . (int) $us_bg_slider_interval . '"';
		$img_slider_shortcode .= ' arrows="hide" autoplay="1" pause_on_hover="" img_size="full" img_fit="cover"]';

		$bg_slider_html = '<div class="l-section-slider">' . do_shortcode( $img_slider_shortcode ) . '</div>';

		// Revolution Slider
	} elseif ( $us_bg_show == 'rev_slider' AND class_exists( 'RevSlider' ) ) {
		$_atts['class'] .= ' with_slider';
		$bg_slider_html = '<div class="l-section-slider">' . do_shortcode( '[rev_slider ' . $us_bg_rev_slider . ']' ) . '</div>';
	}
}

// Background Overlay
$bg_overlay_html = '';
if ( usb_is_post_preview() OR ! empty( $us_bg_overlay_color ) ) {
	$bg_overlay_html = '<div class="l-section-overlay" style="background:' . us_get_color( $us_bg_overlay_color, TRUE ) . '"></div>';
}

// Shape Divider
$bg_shape_html = '';

/*
 * Fallback for old shape params (after version 7.1)
 */
if (
	empty( $us_shape_show_top )
	AND empty( $us_shape_show_bottom )
	AND isset( $atts['us_shape'] )
	AND ( $atts['us_shape'] !== 'none' )
) {
	if ( ! isset( $atts['us_shape_position'] ) ) {
		$old_shape_pos = 'bottom';
	} else {
		$old_shape_pos = 'top';
	}

	${'us_shape_show_' . $old_shape_pos} = 1;
	${'us_shape_' . $old_shape_pos} = $atts['us_shape'];

	if ( ! empty( $atts['us_shape_height'] )	) {
		${'us_shape_height_' . $old_shape_pos} = $atts['us_shape_height'];
	}
	if ( ! empty( $atts['us_shape_color'] )	) {
		${'us_shape_color_' . $old_shape_pos} = $atts['us_shape_color'];
	}
	if ( ! empty( $atts['us_shape_overlap'] )	) {
		${'us_shape_overlap_' . $old_shape_pos} = $atts['us_shape_overlap'];
	}
	if ( ! empty( $atts['us_shape_flip'] )	) {
		${'us_shape_flip_' . $old_shape_pos} = $atts['us_shape_flip'];
	}
}
if ( $us_shape_show_top OR $us_shape_show_bottom ) {
	$_atts['class'] .= ' with_shape';

	$positions = array();
	if ( $us_shape_show_top ) {
		$positions[] = 'top';
	}
	if ( $us_shape_show_bottom ) {
		$positions[] = 'bottom';
	}

	foreach ( $positions as $pos ) {

		// If checkbox checked for current position (top or bottom) generate shape html
		if ( ${'us_shape_show_' . $pos} ) {
			$shape_html = '';

			// Get built-in shapes
			$svg_filepath = sprintf( '%s/assets/shapes/%s.svg', US_CORE_DIR, ${'us_shape_' . $pos} );

			// Get custom file, if it was uploaded in Row settings
			if ( ${'us_shape_' . $pos} === 'custom' AND $shape_id = ${'us_shape_custom_' . $pos} ) {

				// Get file MIME type to handle SVGs separately
				$mime_type = get_post_mime_type( $shape_id );
				if ( strpos( $mime_type, 'svg' ) !== FALSE ) {
					$svg_filepath = get_attached_file( $shape_id );

					// Support non-SVG images
				} else {
					$svg_filepath = '';
					$shape_html = wp_get_attachment_image( $shape_id, 'full' );
				}
			}

			// In case SVG is valid, use its content as shape html
			if ( ! empty( $svg_filepath ) AND $svg_filepath = realpath( $svg_filepath ) ) {
				$shape_html = file_get_contents( $svg_filepath );
			}

			// Attributes for shape div
			${'shape_atts_' . $pos} = array(
				'class' => 'l-section-shape',
				'style' => '',
			);

			// Type and position classes
			${'shape_atts_' . $pos}['class'] .= ' type_' . ${'us_shape_' . $pos};
			${'shape_atts_' . $pos}['class'] .= " pos_{$pos}";

			// Overlap class
			if ( ${'us_shape_overlap_' . $pos} ) {
				${'shape_atts_' . $pos}['class'] .= ' on_front';
			}

			// Flip class
			if ( ${'us_shape_flip_' . $pos} ) {
				${'shape_atts_' . $pos}['class'] .= ' hor_flip';
			}

			// Height style
			if ( ${'us_shape_height_' . $pos} !== '15vh' ) {
				${'shape_atts_' . $pos}['style'] .= 'height:' . ${'us_shape_height_' . $pos} . ';';
			}

			// Color style
			if ( ${'us_shape_color_' . $pos} !== '_content_bg' ) {
				${'shape_atts_' . $pos}['style'] .= 'color:' . us_get_color( ${'us_shape_color_' . $pos} );
			}

			$bg_shape_html .= '<div' . us_implode_atts( ${'shape_atts_' . $pos} ) . '>';
			$bg_shape_html .= $shape_html;
			$bg_shape_html .= '</div>';
		}
	}
}

// Output the element
$output = '<section' . us_implode_atts( $_atts ) . '>';
$output .= apply_filters( 'us_vc_row_bg_img_html', $bg_img_html );
$output .= apply_filters( 'us_vc_row_bg_video_html', $bg_video_html );
$output .= apply_filters( 'us_vc_row_bg_slider_html', $bg_slider_html );
$output .= apply_filters( 'us_vc_row_bg_overlay_html', $bg_overlay_html );
$output .= apply_filters( 'us_vc_row_bg_shape_html', $bg_shape_html );
$output .= '<div class="l-section-h i-cf">';

$cols_atts = array(
	'class' => 'g-cols vc_row',
	'style' => '',
);

// "CSS Grid" columns layout after version 8.0
if ( us_get_option( 'live_builder' ) AND us_get_option( 'grid_columns_layout' ) ) {

	// Fallback for old columns layout (after version 8.0)
	$columns_fallback_result = us_vc_row_columns_fallback_helper( $shortcode_base, $content );
	if ( $columns === '1' AND ! empty( $columns_fallback_result['columns'] ) ) {
		$columns = $columns_fallback_result['columns'];
	}
	if ( ! empty( $columns_fallback_result['columns_layout'] ) ) {
		$columns_layout = $columns_fallback_result['columns_layout'];
	}

	// Fallback for $gap param (after version 8.0)
	if ( $columns_type ) {

		// If the "Additional gap" was set, get its value and double it as new columns gap
		// Example: 5px becomes 10px
		// Example: 0.7rem becomes 1.4rem
		if ( ! empty( $gap ) AND preg_match( '~^(\d*\.?\d*)(.*)$~', $gap, $matches ) ) {
			$columns_gap = ( $matches[1] * 2 ) . $matches[2];
		}
	} elseif ( ! empty( $gap ) ) {
		$columns_gap = 'calc(3rem + ' . $gap . ')';
	}

	$cols_atts['class'] .= ' via_grid';
	$cols_atts['class'] .= ' cols_' . $columns;
	$cols_atts['class'] .= ' laptops-cols_' . $laptops_columns;
	$cols_atts['class'] .= ' tablets-cols_' . $tablets_columns;
	$cols_atts['class'] .= ' mobiles-cols_' . $mobiles_columns;

	// Responsive gap
	if ( $columns_gap_array = (array) us_get_responsive_values( $columns_gap ) ) {
		foreach ( $columns_gap_array as $state => $value ) {
			if ( $state == 'default' ) {
				$cols_atts['style'] .= sprintf( '--gap:%s;', $value );
			} else {
				$cols_atts['style'] .= sprintf( '--%s-gap:%s;', $state, $value );
			}
		}

		// Add basic gap when it doesn't have default value
	} elseif ( $columns_gap !== '3rem' ) {
		$cols_atts['style'] .= '--gap:' . $columns_gap . ';';
	}

	// Add custom columns layout via inline style
	if ( $columns === 'custom' AND ! empty( $columns_layout ) ) {
		$cols_atts['style'] .= '--custom-columns:' . $columns_layout;
	}

} else {
	$cols_atts['class'] .= ' via_flex';
	if ( ! empty( $gap ) ) {
		$cols_atts['style'] .= '--additional-gap:' . $gap . ';';
	}
}

$cols_atts['class'] .= ' valign_' . $content_placement;

if ( ! empty( $columns_type ) ) {
	$cols_atts['class'] .= ' type_boxes';
} else {
	$cols_atts['class'] .= ' type_default';
}
if ( ! empty( $columns_reverse ) ) {
	$cols_atts['class'] .= ' reversed';
}
if ( empty( $ignore_columns_stacking ) ) {
	$cols_atts['class'] .= ' stacking_default';
}

$output .= '<div' . us_implode_atts( $cols_atts ) . '>';
$output .= do_shortcode( $content );
$output .= '</div>';

$output .= '</div>';
$output .= '</section>';

echo $output;
