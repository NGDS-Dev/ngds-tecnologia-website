<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

add_action( 'wp_ajax_usof_save', 'usof_ajax_save' );
function usof_ajax_save() {

	if ( ! check_admin_referer( 'usof-actions' ) ) {
		wp_send_json_error(
			array(
				'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			)
		);
	}

	do_action( 'usof_before_ajax_save' );

	global $usof_options;
	usof_load_options_once();

	$config = us_config( 'theme-options', array(), TRUE );

	// Logic do not seek here, young padawan. For WPML string translation compability such copying method is used.
	// If result of array_merge is put directly to $updated_options, the options will not save.
	$usof_defaults = usof_defaults();
	$usof_options_fallback = array_merge( $usof_defaults, $usof_options );
	$updated_options = array();
	foreach ( $usof_options_fallback as $key => $val ) {
		$updated_options[ $key ] = $val;
	}

	$post_options = us_maybe_get_post_json( 'usof_options' );

	if ( empty( $post_options ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'There\'s no options to save', 'us' ),
			)
		);
	}

	foreach ( $post_options as $key => $value ) {
		if ( isset( $updated_options[ $key ] ) AND isset( $usof_defaults[ $key ] ) ) {
			$updated_options[ $key ] = $value;
		}

		// Saving typography settings as an array
		// TODO: Find out why the typography settings are not in $usof_defaults and fix it
		if ( in_array( $key, US_TYPOGRAPHY_TAGS ) AND is_string( $value ) ) {
			$updated_options[ $key ] = json_decode( rawurldecode( $value ), /* as array */TRUE );
		}

	}

	usof_save_options( $updated_options );

	do_action( 'usof_after_ajax_save' );

	wp_send_json_success(
		array(
			'message' => us_translate( 'Changes saved.' ),
		)
	);
}

add_action( 'wp_ajax_usof_reset', 'usof_ajax_reset' );
function usof_ajax_reset() {

	if ( ! check_admin_referer( 'usof-actions' ) ) {
		wp_send_json_error(
			array(
				'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			)
		);
	}

	// Preloading default values of theme options
	// and then reloading theme options config to fill values that depend on each other
	global $usof_options;
	$usof_options = usof_defaults();
	us_config( 'theme-options', array(), TRUE );

	$updated_options = usof_defaults();
	usof_save_options( $updated_options );
	wp_send_json_success(
		array(
			'message' => __( 'Options were reset', 'us' ),
			'usof_options' => $updated_options,
		)
	);
}

add_action( 'wp_ajax_usof_backup', 'usof_ajax_backup' );
function usof_ajax_backup() {

	if ( ! check_admin_referer( 'usof-actions' ) ) {
		wp_send_json_error(
			array(
				'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			)
		);
	}

	usof_backup();

	$backup_time = strtotime( current_time( 'mysql', TRUE ) ) + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;

	wp_send_json_success(
		array(
			'status' => __( 'Last Backup', 'us' ) . ': <span>' . date_i18n( 'F j, Y - G:i T', $backup_time ) . '</span>',
		)
	);
}

add_action( 'wp_ajax_usof_restore_backup', 'usof_ajax_restore_backup' );
function usof_ajax_restore_backup() {

	if ( ! check_admin_referer( 'usof-actions' ) ) {
		wp_send_json_error(
			array(
				'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			)
		);
	}

	global $usof_options;

	$backup = get_option( 'usof_backup_' . US_THEMENAME );
	if ( ! $backup OR ! is_array( $backup ) OR ! isset( $backup['usof_options'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'There\'s no backup to restore', 'us' ),
			)
		);
	}

	$usof_options = $backup['usof_options'];
	update_option( 'usof_options_' . US_THEMENAME, $usof_options, TRUE );

	wp_send_json_success(
		array(
			'message' => __( 'Backup was restored', 'us' ),
			'usof_options' => $usof_options,
		)
	);
}

add_action( 'wp_ajax_usof_save_style_scheme', 'usof_ajax_save_style_scheme' );
function usof_ajax_save_style_scheme() {

	if ( ! check_admin_referer( 'usof-actions' ) ) {
		wp_send_json_error(
			array(
				'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			)
		);
	}

	$custom_color_schemes = get_option( 'usof_style_schemes_' . US_THEMENAME );
	if ( ! is_array( $custom_color_schemes ) ) {
		$custom_color_schemes = array();
	}

	$scheme = us_maybe_get_post_json( 'scheme' );
	if ( isset( $scheme['id'] ) ) {
		$scheme_id = $scheme['id'];
	} else {
		$max_index = 0;
		if ( count( $custom_color_schemes ) > 0 ) {
			$max_index = (int) max( array_keys( $custom_color_schemes ) );
		}
		$scheme_id = $max_index + 1;
	}

	$custom_color_schemes[ $scheme_id ] = array( 'title' => $scheme['name'], 'values' => $scheme['colors'] );
	update_option( 'usof_style_schemes_' . US_THEMENAME, $custom_color_schemes, TRUE );

	$color_schemes = us_config( 'color-schemes' );
	$output = '';

	$custom_color_schemes_reversed = array_reverse( $custom_color_schemes, TRUE );

	foreach ( $custom_color_schemes_reversed as $key => &$scheme ) {
		$output .= '<li class="usof-schemes-item type_custom" data-id="' . $key . '">';
		$output .= us_color_scheme_preview( $scheme );
		// Overwrite btn
		$output .= '<div class="usof-schemes-item-save" title="' . us_translate( 'Save' ) . '"></div>';
		// Delete btn
		$output .= '<div class="usof-schemes-item-delete" title="' . us_translate( 'Delete' ) . '"></div>';
		$output .= '</li>';
	}
	foreach ( $color_schemes as $key => &$scheme ) {
		$output .= '<li class="usof-schemes-item" data-id="' . $key . '">';
		$output .= us_color_scheme_preview( $scheme );
		$output .= '</li>';
	}

	wp_send_json_success(
		array(
			'customSchemes' => $custom_color_schemes,
			'schemes' => $color_schemes,
			'schemesHtml' => $output,
		)
	);
}

add_action( 'wp_ajax_usof_delete_style_scheme', 'usof_ajax_delete_style_scheme' );
function usof_ajax_delete_style_scheme() {
	if ( ! check_admin_referer( 'usof-actions' ) ) {
		wp_send_json_error(
			array(
				'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			)
		);
	}

	$scheme = sanitize_text_field( $_POST['scheme'] );

	$custom_color_schemes = get_option( 'usof_style_schemes_' . US_THEMENAME );

	if ( ! is_array( $custom_color_schemes ) ) {
		$custom_color_schemes = array();
	}
	if ( isset( $custom_color_schemes[ $scheme ] ) ) {
		unset( $custom_color_schemes[ $scheme ] );
	}
	update_option( 'usof_style_schemes_' . US_THEMENAME, $custom_color_schemes, TRUE );

	$color_schemes = us_config( 'color-schemes' );
	$output = '';

	$custom_color_schemes_reversed = array_reverse( $custom_color_schemes, TRUE );

	foreach ( $custom_color_schemes_reversed as $key => &$scheme ) {
		$output .= '<li class="usof-schemes-item type_custom" data-id="' . $key . '">';
		$output .= us_color_scheme_preview( $scheme );
		// Overwrite btn
		$output .= '<div class="usof-schemes-item-save" title="' . us_translate( 'Save' ) . '"></div>';
		// Delete btn
		$output .= '<div class="usof-schemes-item-delete" title="' . us_translate( 'Delete' ) . '"></div>';
		$output .= '</li>';
	}
	foreach ( $color_schemes as $key => &$scheme ) {
		$output .= '<li class="usof-schemes-item" data-id="' . $key . '">';
		$output .= us_color_scheme_preview( $scheme );
		$output .= '</li>';
	}

	wp_send_json_success(
		array(
			'customSchemes' => $custom_color_schemes,
			'schemes' => $color_schemes,
			'schemesHtml' => $output,
		)
	);
}

// Adding palette values for color picker
add_action( 'wp_ajax_usof_color_palette', 'usof_ajax_color_palette' );
function usof_ajax_color_palette() {
	if ( ! check_admin_referer( 'usof-actions' ) ) {
		wp_send_json_error(
			array(
				'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			)
		);
	}
	$paletteColors = get_option( 'usof_color_palette_' . US_THEMENAME );
	if ( ! is_array( $paletteColors ) ) {
		$paletteColors = array();
	}
	$paletteLength = count( $paletteColors );
	$color = us_maybe_get_post_json( 'color' );
	$output = '';
	if ( isset( $color['value'] ) AND $paletteLength < 8 ) {
		// Appending new color
		$paletteColors[] = $color['value'];
		update_option( 'usof_color_palette_' . US_THEMENAME, $paletteColors, TRUE );
		foreach ( $paletteColors as $color ) {
			$output .= '<div class="usof-colpick-palette-value"><span style="background:' . $color . '" title="' . esc_attr( $color ) . '"></span><div class="usof-colpick-palette-delete" title="' . us_translate( 'Delete' ) . '"></div></div>';
		}
		$output .= '<div class="usof-colpick-palette-add" title="' . __( 'Add the current color to the palette', 'us' ) . '"></div>';
		wp_send_json_success(
			array(
				'output' => $output,
			)
		);
	} elseif ( isset( $color['colorId'] ) ) {
		// Deleting current color
		if ( isset( $paletteColors[ $color['colorId'] ] ) ) {
			unset( $paletteColors[ $color['colorId'] ] );
		}
		$newPalette = array();
		foreach ( $paletteColors as $color ) {
			$newPalette[] = $color;
		}
		update_option( 'usof_color_palette_' . US_THEMENAME, $newPalette, TRUE );
		foreach ( $paletteColors as $color ) {
			$output .= '<div class="usof-colpick-palette-value"><span style="background:' . $color . '" title="' . esc_attr( $color ) . '"></span><div class="usof-colpick-palette-delete" title="' . us_translate( 'Delete' ) . '"></div></div>';
		}
		$output .= '<div class="usof-colpick-palette-add" title="' . __( 'Add the current color to the palette', 'us' ) . '"></div>';
		wp_send_json_success(
			array(
				'output' => $output,
			)
		);
	} else {
		wp_send_json_error(
			array(
				'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			)
		);
	}
}

// Get Color Schemes
add_action( 'wp_ajax_usof_get_style_schemes', 'usof_get_style_schemes' );
function usof_get_style_schemes() {
	if ( ! check_admin_referer( 'usof-actions' ) ) {
		wp_send_json_error(
			array(
				'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			)
		);
	}
	$color_schemes = us_config( 'color-schemes' );
	$custom_color_schemes = get_option( 'usof_style_schemes_' . US_THEMENAME );
	if ( ! is_array( $custom_color_schemes ) ) {
		$custom_color_schemes = array();
	}

	// Reverse Custom schemes order to make last added item first
	$custom_color_schemes = array_reverse( $custom_color_schemes, TRUE );

	wp_send_json_success(
		array(
			'schemes' => $color_schemes,
			'custom_schemes' => $custom_color_schemes,
		)
	);
}

// Get Google Fonts
add_action( 'wp_ajax_usof_get_google_fonts', 'usof_get_google_fonts' );
function usof_get_google_fonts() {
	if ( ! check_admin_referer( 'usof-actions' ) ) {
		wp_send_json_error(
			array(
				'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			)
		);
	}
	$google_fonts = us_config( 'google-fonts', array() );

	wp_send_json_success(
		array(
			'google_fonts' => $google_fonts,
		)
	);
}

if ( wp_doing_ajax() AND ! function_exists( 'usof_ajax_used_icons_info' ) ) {
	/**
	 * Get a list of used icon sets
	 *
	 * @return string
	 */
	function usof_ajax_used_icons_info() {
		if ( ! check_ajax_referer( 'usof_ajax_used_icons_info', '_nonce', FALSE ) ) {
			wp_send_json_error(
				array(
					'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
				)
			);
			wp_die();
		}

		/**
		 * @var array
		 */
		$res = array();
		if ( class_exists( 'US_Get_Used_Icons' ) ) {

			/* @var $instance US_Get_Used_Icons */
			$instance = new US_Get_Used_Icons;

			// Run next step or start
			$instance->run();

			// Check is processing
			$res['processing'] = $instance->is_processing();

			if ( ! us_arr_path( $res, 'processing', TRUE ) ) {

				// Get results and group icons
				$used_icons = $icon_links = array();
				foreach ( $instance->get_data( 'icons' ) as $icon ) {
					if ( strpos( $icon, '|' ) === FALSE ) {
						continue;
					}
					list( $icon_prefix, $icon_name ) = explode( '|', $icon, 2 );
					$used_icons[ $icon_prefix ][] = $icon;
				}
				// Add locations by post id
				foreach ( $instance->get_data( 'posts' ) as $icon => $posts ) {
					foreach ( $posts as $id ) {
						$_title = get_the_title( $id );
						$_title = empty( $_title ) ? us_translate( '(no title)' ) : $_title;
						$icon_links[ $icon ][] = '<a href="' . admin_url( 'post.php?post='. $id .'&action=edit' ) . '" target="_blank">' . strip_tags( $_title ) . '</a>';
					}
				}
				// Add custom locations
				foreach( $instance->get_data( 'locations' ) as $icon => $locations ) {
					foreach ( $locations as $custom_link => $_options ) {
						if ( empty( $_options['title'] ) ) {
							$_options['title'] = us_translate( '(no title)' );
						}
						$target = isset( $_options['target'] )
							? ' target="_blank"'
							: '';
						$icon_links[ $icon ][] = '<a href="' . esc_url( $custom_link ) . '"'. $target .'>' . strip_tags( $_options['title'] ) . '</a>';
					}
				}

				$result = '';
				foreach ( us_config( 'icon-sets', array() ) as $key => $icon_config ) {
					if ( $icons = us_arr_path( $used_icons, $key ) ) {
						sort( $icons );
						$result .= '<div class="usof-icons-info-group">';

						if ( $group_name = us_arr_path( $icon_config, 'set_name', '' ) ) {
							$result .= '<div class="usof-icons-info-group-name">' . strip_tags( $group_name ) . '</div>';
						}

						// Calculate custom height to immitate CSS "columns" to avoid appearance bug with "position:absolute"
						$result .= '<ul class="usof-icons-info-group-list" style="height:' . ( 26 * ceil( count( $icons ) / 3 ) ) . 'px">';
						foreach ( $icons as $icon ) {

							// Get the icon_name of the icon
							$icon_name = substr( strstr( $icon, '|' ), 1 );

							// Highlight fallback icons except "FA Duotone"
							if ( $key != 'fad' AND us_is_fallback_icon( $icon_name ) ) {
								$result .= '<li class="type_fallback">';
							} else {
								$result .= '<li>';
							}
							$result .= us_prepare_icon_tag( $icon );
							$result .= '<span>' . strip_tags( $icon_name ) . '</span>';

							// Links to posts where the icon was found
							if ( ! empty( $icon_links[ $icon ] ) AND is_array( $icon_links[ $icon ] ) ) {
								$result .= '<div class="usof-tooltip-text">' . implode( '', $icon_links[ $icon ] ) . '</div>';
							}
							$result .= '</li>';
						}
						$result .= '</ul></div>';
					}
				}
				$res['result'] = $result;
			}
		}
		if ( empty( $res['result'] ) ) {
			$res['result'] = us_translate( 'No results found.' );
		}

		wp_send_json_success( $res );
	}
	add_action( 'wp_ajax_usof_used_icons_info', 'usof_ajax_used_icons_info', 1 );
}

if ( wp_doing_ajax() AND ! function_exists( 'usof_search_items_for_link' ) ) {
	/**
	 * Get a list of posts and terms via the search request for link field
	 *
	 * @return array
	 */
	function usof_search_items_for_link() {
		// Security check via nonce
		if ( ! check_ajax_referer( 'usof_search_items_for_link', '_nonce', FALSE ) ) {
			wp_send_json_error(
				array(
					'message' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
				)
			);
			wp_die();
		}

		$items_per_page = 20; // how many items to load on scroll
		$result = array(
			'items' => array(),
		);

		$search_phrase = ! empty( $_POST['search'] ) ? wp_unslash( $_POST['search'] ) : '';
		$posts_offset = ! empty( $_POST['posts_offset'] ) ? absint( $_POST['posts_offset'] ) : 0;
		$posts_search_completed = ! empty( $_POST['posts_complete'] ) ? absint( $_POST['posts_complete'] ) : 0;
		$terms_offset = ! empty( $_POST['terms_offset'] ) ? absint( $_POST['terms_offset'] ) : 0;
		$terms_search_completed = 0; // Cannot be passed from JS, since when terms search is completed no more calls performed

		// Search posts
		if ( ! $posts_search_completed ) {
			$available_post_types = get_post_types( array( 'name' => 'page', 'publicly_queryable' => TRUE ), 'objects', 'or' );

			$posts_query = array(
				'post_type' => array_keys( $available_post_types ),
				'suppress_filters' => TRUE,
				'update_post_term_cache' => FALSE,
				'update_post_meta_cache' => FALSE,
				'post_status' => array( 'publish', 'private' ),
				'posts_per_page' => $items_per_page,
				'offset' => $posts_offset,
			);

			if ( $search_phrase ) {
				$posts_query['s'] = $search_phrase;
			}

			$get_posts = new WP_Query();
			$posts = $get_posts->query( $posts_query );
			$number_of_posts_found = count( $posts );

			// Increase posts offset for possible next AJAX call
			$posts_offset += $number_of_posts_found;

			foreach ( $posts as $post ) {
				$result['items'][] = array(
					'ID' => $post->ID,
					'title' => trim( esc_html( $post->post_title ) ),
					'permalink' => get_permalink( $post->ID ),
					'type' => $available_post_types[ $post->post_type ]->labels->singular_name,
				);
			}

			if ( $number_of_posts_found < $items_per_page ) {
				$posts_search_completed = 1;
			}
		}

		// Search terms (when finished with posts)
		if ( $posts_search_completed AND ! $terms_search_completed ) {
			$available_taxonomies = get_taxonomies( array( 'public' => TRUE ), 'objects' );

			$terms_query = array(
				'taxonomy' => array_keys( $available_taxonomies ),
				'hide_empty' => FALSE,
				'number' => $items_per_page,
				'offset' => $terms_offset,
			);

			if ( $search_phrase ) {
				$terms_query['search'] = $search_phrase;
			}

			$terms = get_terms( $terms_query );
			$number_of_terms_found = count( $terms );

			// Increase terms offset for possible next AJAX call
			$terms_offset += $number_of_terms_found;

			foreach ( $terms as $term ) {
				$result['items'][] = array(
					'ID' => $term->term_id,
					'title' => $term->name,
					'permalink' => get_term_link( $term ),
					'type' => $available_taxonomies[ $term->taxonomy ]->labels->singular_name,
				);
			}

			if ( $number_of_terms_found < $items_per_page ) {
				$terms_search_completed = 1;
			}
		}

		$result['posts_offset'] = $posts_offset;
		$result['posts_search_completed'] = $posts_search_completed;
		$result['terms_offset'] = $terms_offset;
		$result['terms_search_completed'] = $terms_search_completed;

		if (
			empty( $result['items'] )
			AND $posts_offset == 0
			AND $terms_offset == 0
		) {
			$result['notice'] = us_translate( 'No results found.' );
		}

		wp_send_json_success( $result );
	}
	add_action( 'wp_ajax_usof_search_items_for_link', 'usof_search_items_for_link', 1 );
}
