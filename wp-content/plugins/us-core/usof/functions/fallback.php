<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! function_exists( 'usof_apply_assets_fallback' ) ) {
	/*
	 * Starting from version 7.5 'assets' option will have a new format.
	 * In this function we will provide a fallback for it.
	 */
	function usof_apply_assets_fallback( $value ) {

		// If value is set for the option and we detect it is in old format, we should transform it.
		if ( is_array( $value ) AND ( reset( $value ) !== 0 ) AND ( reset( $value ) !== 1 ) ) {
			$assets_config = us_config( 'assets', array() );
			$new_value = array();

			// First check / uncheck assets from older versions
			foreach ( $assets_config as $component => $component_atts ) {
				if ( empty( $component_atts['title'] ) ) {
					continue;
				}
				$new_value[ $component ] = in_array( $component, $value ) ? 1 : 0;
			}

			// Then force check assets added since 7.5
			$new_assets = array(
				'gallery',
				'grid_filter',
				'grid_templates',
				'grid_pagination',
				'grid_popup',
				'hor_parallax',
				'hwrapper',
				'image_slider',
				'magnific_popup',
				'post_elements',
				'post_navigation',
				'simple_menu',
				'text',
				'ver_parallax',
				'vwrapper',
				'wp_widgets',
			);
			foreach ( $new_assets as $component ) {
				$new_value[ $component ] = 1;
			}

			return $new_value;
		}

		// If value is empty or format is OK return it as is
		return $value;
	}
}

if ( ! function_exists( 'us_get_page_for_posts' ) ) {
	/*
	 * Includes fallback for an old USOF option before versions 8.16
	 * https://github.com/upsolution/wp/issues/3345
	 */
	function us_get_page_for_posts() {

		// Return the old value if exists
		if ( us_get_option( 'posts_page' ) ) {
			return (string) us_get_option( 'posts_page' );
		}

		return get_option( 'page_for_posts' );
	}
}

if ( ! function_exists( 'usof_apply_posts_page_fallback' ) ) {
	/*
	 * Starting from version 8.16 we need to move the "Posts Page" after saving Theme Options
	 * https://github.com/upsolution/wp/issues/3345
	 */
	function usof_apply_posts_page_fallback( $usof_options ) {

		// Trigger this fallback only if the old option exists
		if ( isset( $usof_options['posts_page'] ) ) {

			// Check if pages are set in both options and they aren't the same and they exist
			if (
				$usof_options['posts_page'] != 'default'
				AND $_wp_posts_page_ID = get_option( 'page_for_posts' )
				AND $_wp_posts_page_ID != $usof_options['posts_page']
				AND $_wp_posts_page = get_post( $_wp_posts_page_ID )
				AND get_post( $usof_options['posts_page'] )
			) {
				// Delete the WP posts page first to avoid duplicating slugs
				wp_delete_post( $_wp_posts_page_ID, TRUE );

				// Update the theme posts page to keep its content
				wp_update_post(
					array(
						'ID' => $usof_options['posts_page'],
						'post_title' => $_wp_posts_page->post_title,
						'post_name' => $_wp_posts_page->post_name,
					)
				);

				// Remove unneeded variables
				unset( $_wp_posts_page, $_wp_posts_page_ID );

				// Update the Reading option
				update_option( 'page_for_posts', $usof_options['posts_page'] );
			}

			// Remove the old option value
			unset( $usof_options['posts_page'] );
		}

		return $usof_options;
	}

	add_filter( 'usof_updated_options', 'usof_apply_posts_page_fallback' );
}

if ( ! function_exists( 'usof_apply_fallback_for_options' ) ) {
	function usof_apply_fallback_for_options( $usof_options ) {

		// Fallback for Optimize CSS and JS option (after version 7.5)
		if ( isset( $usof_options['assets'] ) ) {
			$usof_options['assets'] = usof_apply_assets_fallback( $usof_options['assets'] );
		}

		// Fallback for versions before 8.0
		if ( isset( $usof_options['disable_block_editor_assets'] ) ) {

			// Turn off the "Disable legacy HTML" (but it will keep enabled on new installations)
			$usof_options['grid_columns_layout'] = 0;

			// Turn on the "Gutenberg (block editor)" module, if it was enabled
			if ( ! $usof_options['disable_block_editor_assets'] ) {
				$usof_options['block_editor'] = 1;
			}
			unset( $usof_options['disable_block_editor_assets'] );
		}

		// Fallback for Button Styles
		if ( ! empty( $usof_options['buttons'] ) AND is_array( $usof_options['buttons'] ) ) {
			foreach ( $usof_options['buttons'] as $key => $_params ) {
				if ( isset( $_params['shadow'] ) ) {
					$usof_options['buttons'][ $key ]['shadow_offset_v'] = ( (float) $_params['shadow'] / 2 ) . 'em';
					$usof_options['buttons'][ $key ]['shadow_blur'] = $_params['shadow'];

					unset( $usof_options['buttons'][ $key ]['shadow'] );
				}
				if ( isset( $_params['shadow_hover'] ) ) {
					$usof_options['buttons'][ $key ]['shadow_hover_offset_v'] = ( (float) $_params['shadow_hover'] / 2 ) . 'em';
					$usof_options['buttons'][ $key ]['shadow_hover_blur'] = $_params['shadow_hover'];

					unset( $usof_options['buttons'][ $key ]['shadow_hover'] );
				}
			}
		}

		// Fallback for H1 - H6 transform checkboxes
		for ( $i = 1; $i <= 6; $i ++ ) {
			if ( isset( $usof_options[ 'h' . $i . '_transform' ] ) ) {

				if ( is_array( $usof_options[ 'h' . $i . '_transform' ] ) ) {
					$usof_options[ 'h' . $i . '_transform' ] = implode( ',', $usof_options[ 'h' . $i . '_transform' ] );
				}

				// After 8.6 version
				if ( strpos( $usof_options[ 'h' . $i . '_transform' ], 'uppercase' ) !== FALSE ) {
					$usof_options[ 'h' . $i . '_texttransform' ] = 'uppercase';
				}
				if ( strpos( $usof_options[ 'h' . $i . '_transform' ], 'italic' ) !== FALSE ) {
					$usof_options[ 'h' . $i . '_fontstyle' ] = 'italic';
				}

				unset( $usof_options[ 'h' . $i . '_transform' ] );
			}
		}

		/*
		 * Fallback for Live options > Typography (after version 8.17)
		 */
		if ( isset( $usof_options[ 'body_font_family' ] ) ) {
			// Extracted params from USOF options
			$old_font_params =
			// Font families for each typography tag
			$font_family_values =
			// Font weights for each typography tag
			$font_weight_values =
			// Bold font weights for each typography tag
			$bold_font_weight_values =
			// Google font family names for each tag
			$google_font_families =
			// Font weights that are actually loaded for Google fonts (grouped by google font names, not typography tags).
			$google_font_weights =
			// Font weights that are actually used for typography tags
			$used_google_font_weights =
			// Additional google fonts that we will have to add if there are unused font weights for Google fonts after fallback
			$new_additional_google_fonts = array();

			$google_fonts_config = us_config( 'google-fonts' );

			/*
			 * Remove default values of new typography settings
			 */
			foreach ( US_TYPOGRAPHY_TAGS as $_tag ) {
				$usof_options[ $_tag ] = array();
			}

			/*
			 * Prepare font family params from old "font" usof control, like "Roboto Slab|300,400,500italic,700,700italic,900"
			 */
			$old_font_params['body'] = explode( '|', $usof_options[ 'body_font_family' ] );
			for ( $h = 1; $h <= 6; $h++ ) {
				$old_font_params[ 'h' . $h ] = explode( '|', $usof_options[ 'h' . $h . '_font_family' ] );
			}

			/*
			 * Get font family values and real Google font family names (where set) for all typography tags
			 */
			// Body tag
			$font_family_values['body'] = ( ! empty( $old_font_params['body'][0] ) ) ? $old_font_params['body'][0] : 'none';
			$google_font_families['body'] = ( ! empty( $google_fonts_config[ $font_family_values['body'] ] ) )
				? $font_family_values['body']
				: NULL;

			// H1 tag
			$font_family_values['h1'] = ( ! empty( $old_font_params['h1'][0] ) ) ? $old_font_params['h1'][0] : 'inherit';

			// Fallback for new default H1 value
			if ( $font_family_values['h1'] == 'none' ) {
				$font_family_values['h1'] = 'inherit';
			}

			// Check if the font value is inherited before verifying if the font family is a Google font
			if ( $font_family_values['h1'] == 'inherit' ) {
				$google_font_families['h1'] = $google_font_families['body'];
			} else {
				$google_font_families['h1'] = ( ! empty( $google_fonts_config[ $font_family_values['h1'] ] ) )
					? $font_family_values['h1']
					: NULL;
			}

			// H2 - H6
			for ( $h = 2; $h <= 6; $h++ ) {
				$font_family_values[ 'h' . $h ] = ( ! empty( $old_font_params[ 'h' . $h ][0] ) ) ? $old_font_params[ 'h' . $h ][0] : 'var(--h1-font-family)';

				// Fallback for new default H2 - H6 value
				if ( $font_family_values[ 'h' . $h ] == 'get_h1' ) {
					$font_family_values[ 'h' . $h ] = 'var(--h1-font-family)';
				} elseif ( $font_family_values[ 'h' . $h ] == 'none' ) {
					$font_family_values[ 'h' . $h ] = 'inherit';
				}

				// Check if the font value is inherited before verifying if the font family is a Google font
				if ( $font_family_values[ 'h' . $h ] == 'var(--h1-font-family)' ) {
					$google_font_families[ 'h' . $h ] = $google_font_families['h1'];
				} elseif ( $font_family_values[ 'h' . $h ] == 'inherit' ) {
					$google_font_families[ 'h' . $h ] = $google_font_families['body'];
				} else {
					$google_font_families[ 'h' . $h ] = ( ! empty( $google_fonts_config[ $font_family_values[ 'h' . $h ] ] ) )
						? $font_family_values[ 'h' . $h ]
						: NULL;
				}
			}

			/*
			 * Get which weights for are loaded for Google fonts
			 */
			foreach ( US_TYPOGRAPHY_TAGS as $_tag ) {

				// If this tag does not use a Google font, or inherits it from another tag - skip font weights processing for it
				if ( empty( $google_fonts_config[ $font_family_values[ $_tag ] ] ) ) {
					continue;
				}

				// Process font weights from old options values
				if ( isset( $old_font_params[ $_tag ][1] ) ) {
					$_font_weights = explode( ',', $old_font_params[ $_tag ][1] ); // result array( 300, 400, 500italic, 700, 700italic, 900 )
					$_font_weights = array_unique( array_map( 'intval', $_font_weights ) ); // result array( 300, 400, 500, 700, 900 )
					$_google_font_weights = array_unique( array_map(
						'intval',
						$google_fonts_config[ $font_family_values[ $_tag ] ]['variants']
					) );
					// Remove all weight values that are not present in the Google fonts config
					$_font_weights = array_intersect(
						$_font_weights,
						$_google_font_weights
					);
				}

				// Set default weights for Google font if no value is set in old options
				if ( empty( $_font_weights ) ) {
					$_font_weights = array_intersect(
						array( '400', '700' ),
						$_google_font_weights
					);
				}

				// If the font weights for this Google font is not added yet, create an empty array for it
				if ( empty( $google_font_weights[ $font_family_values[ $_tag ] ] ) ) {
					$google_font_weights[ $font_family_values[ $_tag ] ] = array();
				}

				// Add found font weights
				$google_font_weights[ $font_family_values[ $_tag ] ] = array_unique(
					array_merge(
						$google_font_weights[ $font_family_values[ $_tag ] ],
						$_font_weights
					)
				);

			}

			/*
			 * Find the closest suitable font weights
			 */
			// Function that searches for closest font weight among the array of all loaded for Google font weights
			$func_get_closest_font_weight = function( $target_weight, $available_weights, $config_weights ) {

				// Order of font weight used by browser when there is no exact match between CSS and loaded fonts
				$font_weight_fallback_order = array(
					'100' => array ( 200, 300, 400, 500, 600, 700, 800, 900, ),
					'200' => array ( 100, 300, 400, 500, 600, 700, 800, 900, ),
					'300' => array ( 200, 100, 400, 500, 600, 700, 800, 900, ),
					'400' => array ( 500, 300, 200, 100, 600, 700, 800, 900, ),
					'500' => array ( 400, 300, 200, 100, 600, 700, 800, 900, ),
					'600' => array ( 700, 800, 900, 500, 400, 300, 200, 100, ),
					'700' => array ( 800, 900, 600, 500, 400, 300, 200, 100, ),
					'800' => array ( 900, 700, 600, 500, 400, 300, 200, 100, ),
					'900' => array ( 800, 700, 600, 500, 400, 300, 200, 100, ),
				);

				// In rare case of broken data set the default font weight
				if ( ! in_array( $target_weight, array_keys( $font_weight_fallback_order ) ) ) {
					$target_weight = 400;
				}

				// If the target weight is present in array of loaded weights - return it
				if ( in_array( $target_weight, $available_weights) ) {
					return $target_weight;
				}

				// If target weight is not available, try to find most suitable font weight among available weights
				foreach ( $font_weight_fallback_order[ $target_weight ] as $fallback_font_weight ) {
					if ( in_array( $fallback_font_weight, $available_weights ) ) {
						$closest_weight = $fallback_font_weight;
						break;
					}
				}

				// Browsers will increase weight for thinner variants if bold font-weight is set in CSS,
				// take this into account by increasing $closest_weight by 100
				if ( $target_weight >= 600 AND $closest_weight < 600 ) {
					$closest_weight += 100;
					// If the increased font weight is not available for this Google font, use the original weight
					if ( ! in_array( $closest_weight, $config_weights ) ) {
						$closest_weight = $target_weight;
					}
				}

				return $closest_weight;
			};

			foreach ( US_TYPOGRAPHY_TAGS as $_tag ) {
				if ( $_tag == 'body' ) {
					if (
						$usof_options['body_font_family'] !== 'none'
						AND $_body_font_arr = explode( '|', $usof_options['body_font_family'] )
						AND ! empty( $_body_font_arr[1] )
					) {
						$_font_weight = (int) $_body_font_arr[1];
					} else {
						$_font_weight = 400;
					}
				} else {
					$_font_weight = ( ! empty( $usof_options[ $_tag . '_fontweight' ] ) )
						? $usof_options[ $_tag . '_fontweight' ]
						: 400;
				}
				$_bold_font_weight = 700;

				if (
					! empty( $google_font_families[ $_tag ] )
					AND ! empty( $google_font_weights[ $google_font_families[ $_tag ] ] )
				) {
					$_available_google_fonts_weights = $google_font_weights[ $google_font_families[ $_tag ] ];

					$_config_font_weights = array_unique( array_map(
						'intval',
						$google_fonts_config[ $google_font_families[ $_tag ] ]['variants']
					) );

					$_font_weight = $func_get_closest_font_weight( $_font_weight, $_available_google_fonts_weights, $_config_font_weights );
					$_bold_font_weight = $func_get_closest_font_weight( $_bold_font_weight, $_available_google_fonts_weights, $_config_font_weights );

					if ( ! isset( $used_google_font_weights[ $google_font_families[ $_tag ] ] ) ) {
						$used_google_font_weights[ $google_font_families[ $_tag ] ] = array();
					}
					if ( ! in_array( $_font_weight, $used_google_font_weights[ $google_font_families[ $_tag ] ] ) ) {
						$used_google_font_weights[ $google_font_families[ $_tag ] ][] = $_font_weight;
					}
					if ( ! in_array( $_bold_font_weight, $used_google_font_weights[ $google_font_families[ $_tag ] ] ) ) {
						$used_google_font_weights[ $google_font_families[ $_tag ] ][] = $_bold_font_weight;
					}
				}

				$font_weight_values[ $_tag ] = $_font_weight;
				$bold_font_weight_values[ $_tag ] = $_bold_font_weight;
			}

			// See if we need to add any additional Google fonts to compensate for unused font weights
			foreach( $google_font_weights as $_font_family => $_font_weights ) {
				$_unused_font_weights = array_diff( $_font_weights, $used_google_font_weights[ $_font_family ] );
				if ( count( $_unused_font_weights ) ) {
					if ( ! isset( $new_additional_google_fonts[ $_font_family ] ) ) {
						$new_additional_google_fonts[ $_font_family ] = array();
					}
					$new_additional_google_fonts[ $_font_family ] = array_unique(
						array_merge(
							$new_additional_google_fonts[ $_font_family ],
							$_unused_font_weights
						)
					);
				}
			}

			if ( count( $new_additional_google_fonts ) > 0 ) {
				foreach ( $new_additional_google_fonts as $_font_family => $_font_weights ) {

					// Skip font-family not from Google fonts config
					if ( empty( $google_fonts_config[ $_font_family ] ) ) {
						continue;
					}

					$_additional_font_exists = FALSE;

					// Make sure the Additional Google Fonts setting is array
					if (
						empty( $usof_options['custom_font'] )
						OR ! is_array( $usof_options['custom_font'] )
					) {
						$usof_options['custom_font'] = array();
					}
					foreach ( $usof_options['custom_font'] as $_additional_font ) {
						if ( strpos( $_additional_font['font_family'], $_font_family ) !== FALSE ) {
							$_additional_font_exists = TRUE;
							foreach ( $_font_weights as $_font_weight ) {
								if ( strpos( $_additional_font['font_family'], $_font_weight ) === FALSE ) {
									$_additional_font['font_family'] .= ',' . $_font_weight;
								}
							}
						}
					}
					if ( ! $_additional_font_exists ) {
						$usof_options['custom_font'][] = array(
							'font_family' => $_font_family . '|' . implode( ',', $_font_weights ),
						);
					}
				}
			}

			// Body
			$usof_options['body'] = array(
				'font-family' => $font_family_values['body'],
				'font-size' => $usof_options['body_fontsize'],
				'line-height' => $usof_options['body_lineheight'],
				'font-weight' => $font_weight_values['body'],
				'bold-font-weight' => $bold_font_weight_values['body'],
			);

			// Add font size for mobiles if it's not empty and doesn't equal the default font size
			if (
				$usof_options['body_fontsize_mobile'] != ''
				AND $usof_options['body_fontsize_mobile'] != $usof_options['body_fontsize']
			) {
				$usof_options['body']['font-size'] = array();
				foreach ( us_get_responsive_states( /* only keys */TRUE ) as $state ) {
					$usof_options['body']['font-size'][ $state ] = ( $state == 'mobiles' )
						? $usof_options['body_fontsize_mobile']
						: $usof_options['body_fontsize'];
				}
				$usof_options['body']['font-size'] = rawurlencode( json_encode( $usof_options['body']['font-size'] ) );
			}

			// Add line height for mobiles if it's not empty and doesn't equal the default font size
			if (
				$usof_options['body_lineheight_mobile'] != ''
				AND $usof_options['body_lineheight_mobile'] != $usof_options['body_lineheight']
			) {
				$usof_options['body']['line-height'] = array();
				foreach ( us_get_responsive_states( /* only keys */TRUE ) as $state ) {
					$usof_options['body']['line-height'][ $state ] =  ( $state == 'mobiles' )
						? $usof_options['body_lineheight_mobile']
						: $usof_options['body_lineheight'];
				}
				$usof_options['body']['line-height'] = rawurlencode( json_encode( $usof_options['body']['line-height'] ) );
			}

			// Headings
			for ( $h = 1; $h <= 6; $h++ ) {

				$usof_options[ 'h' . $h ] = array(
					'font-family' => $font_family_values[ 'h' . $h ],
					'font-size' => $usof_options[ 'h' . $h . '_fontsize' ],
					'line-height' => $usof_options[ 'h' . $h . '_lineheight' ],
					'font-weight' => $font_weight_values[ 'h' . $h ],
					'bold-font-weight' => $bold_font_weight_values[ 'h' . $h ],
					'text-transform' => $usof_options[ 'h' . $h . '_texttransform' ],
					'font-style' => ( $usof_options[ 'h' . $h . '_fontstyle' ] ) ? 'italic' : 'normal', // checkbox was used
					'letter-spacing' => $usof_options[ 'h' . $h . '_letterspacing' ],
					'margin-bottom' => $usof_options[ 'h' . $h . '_bottom_indent' ],
					'color' => $usof_options[ 'h' . $h . '_color' ],
					'color_override' => $usof_options[ 'h' . $h . '_color_override' ],
				);

				// Change values to the H1 inheritance
				if ( $h != 1 ) {
					if ( $usof_options[ 'h' . $h ]['font-weight'] == $usof_options['h1']['font-weight'] ) {
						$usof_options[ 'h' . $h ]['font-weight'] = 'var(--h1-font-weight)';
					}
					if ( $usof_options[ 'h' . $h ]['bold-font-weight'] == $usof_options['h1']['bold-font-weight'] ) {
						$usof_options[ 'h' . $h ]['bold-font-weight'] = 'var(--h1-bold-font-weight)';
					}
					if ( $usof_options[ 'h' . $h ]['text-transform'] == $usof_options['h1']['text-transform'] ) {
						$usof_options[ 'h' . $h ]['text-transform'] = 'var(--h1-text-transform)';
					}
					if ( $usof_options[ 'h' . $h ]['font-style'] == $usof_options['h1']['font-style'] ) {
						$usof_options[ 'h' . $h ]['font-style'] = 'var(--h1-font-style)';
					}
				}

				// Add font size for mobiles if it's not empty and doesn't equal the default font size
				if (
					$usof_options[ 'h' . $h . '_fontsize_mobile' ] != ''
					AND $usof_options[ 'h' . $h . '_fontsize_mobile' ] AND $usof_options[ 'h' . $h . '_fontsize' ]
				) {
					$usof_options[ 'h' . $h ]['font-size'] = array();
					foreach ( us_get_responsive_states( /* only keys */TRUE ) as $state ) {
						$usof_options[ 'h' . $h ]['font-size'][ $state ] =  ( $state == 'mobiles' )
							? $usof_options[ 'h' . $h . '_fontsize_mobile' ]
							: $usof_options[ 'h' . $h . '_fontsize' ];
					}
					$usof_options[ 'h' . $h ]['font-size'] = rawurlencode( json_encode( $usof_options[ 'h' . $h ]['font-size'] ) );
				}
			}

			// Remove old options
			$options_to_remove = array(
				'body_font_family',
				'body_fontsize',
				'body_fontsize_mobile',
				'body_lineheight',
				'body_lineheight_mobile',
			);
			for ( $h = 1; $h <= 6; $h ++ ) {
				$options_to_remove[] = 'h' . $h . '_font_family';
				$options_to_remove[] = 'h' . $h . '_fontsize';
				$options_to_remove[] = 'h' . $h . '_fontsize_mobile';
				$options_to_remove[] = 'h' . $h . '_lineheight';
				$options_to_remove[] = 'h' . $h . '_fontweight';
				$options_to_remove[] = 'h' . $h . '_bottom_indent';
				$options_to_remove[] = 'h' . $h . '_color';
				$options_to_remove[] = 'h' . $h . '_texttransform';
				$options_to_remove[] = 'h' . $h . '_color_override';
				$options_to_remove[] = 'h' . $h . '_fontstyle';
			}

			foreach ( $options_to_remove as $option_to_remove ) {
				unset( $usof_options[ $option_to_remove ] );
			}
		}

		return $usof_options;
	}

	add_filter( 'usof_load_options_once', 'usof_apply_fallback_for_options' );
}