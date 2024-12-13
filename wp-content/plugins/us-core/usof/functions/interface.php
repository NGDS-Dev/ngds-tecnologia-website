<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

add_action( 'admin_menu', 'us_options_admin_menu', 9 );
function us_options_admin_menu() {
	if ( ! defined ( 'US_THEMENAME' ) ) {
		return;
	}
	add_menu_page( __( 'Theme Options', 'us' ), apply_filters( 'us_theme_name', US_THEMENAME ), 'manage_options', 'us-theme-options', 'us_theme_options_page', NULL, '59.001' );

	$usof_page = add_submenu_page( 'us-theme-options', __( 'Theme Options', 'us' ), __( 'Theme Options', 'us' ), 'edit_theme_options', 'us-theme-options', 'us_theme_options_page' );

	add_action( 'admin_print_scripts-' . $usof_page, 'usof_print_scripts' );

	add_action( 'admin_print_styles-' . $usof_page, function() {
		global $usof_options;

		// Enqueue Google Fonts CSS file
		us_enqueue_google_fonts();

		// Get css for include uploaded fonts
		if ( $inline_css = us_get_uploaded_fonts_css() ) {
			echo "<style id='us-uploaded-fonts'>" . $inline_css . "</style>";
		}

		// Typography CSS variables
		$typography_vars = '';

		// Get body font-family which may be used in other settings preview (Headings, Button Styles, Field Style)
		if ( isset( $usof_options['body']['font-family'] ) AND $value = $usof_options['body']['font-family'] ) {

			// Add quotes for family name with spaces
			if (
				strpos( $value, ',' ) === FALSE
				AND strpos( $value, ' ' ) !== FALSE
			) {
				$value = '"' . $value . '"';
			}
			$typography_vars .= sprintf( '--body-font-family: %s;', $value );
		}

		// Get H1 values which may be used in other headings preview via the "As in Heading 1" value
		if ( isset( $usof_options['h1'] ) ) {
			foreach ( array( 'font-family', 'font-weight', 'bold-font-weight', 'font-style' ) as $name ) {
				if ( $value = trim( (string) us_arr_path( $usof_options, 'h1.' . $name, '' ) ) ) {

					// Add quotes for family name with spaces
					if (
						$name == 'font-family'
						AND strpos( $value, ',' ) === FALSE
						AND strpos( $value, ' ' ) !== FALSE
					) {
						$value = '"' . $value . '"';
					}
					$typography_vars .= sprintf( '--h1-%s: %s;', $name, $value  );
				}
			}
		}
		echo "<style id='us-typography-vars'>:root{" . $typography_vars . "}</style>";

	} );

	add_action( 'admin_print_scripts-post-new.php', 'usof_print_scripts' );
	add_action( 'admin_print_scripts-post.php', 'usof_print_scripts' );

	add_action( 'admin_print_scripts-nav-menus.php', 'usof_print_scripts' );

	// Hide admin notices on Theme options page and HB / GB builders pages
	// Notice: we do not need this for US Page Builder, since it doesn't use default admin files
	add_action( 'in_admin_header', function() {
		global $pagenow, $post;
		if (
			// Theme Options
			(
				$pagenow == 'admin.php'
				AND isset( $_GET['page'] )
				AND $_GET['page'] == 'us-theme-options'
			)
			// Header and Grid builders
			OR (
				$pagenow == 'post.php'
				AND ! empty( $post )
				AND ! empty( $post->post_type )
				AND in_array( $post->post_type, array( 'us_header', 'us_grid_layout' ) )
			)
		) {
			add_action( 'admin_notices', 'usof_hide_admin_notices_start', 1 );
			add_action( 'admin_notices', 'usof_hide_admin_notices_end', 1000 );
		}
	} );

}

function us_theme_options_page() {

	// Set global variables
	global $usof_options;
	usof_load_options_once();
	$usof_options = array_merge( usof_defaults(), $usof_options );

	// For admin notices
	echo '<div class="wrap"><h2 class="hidden"></h2>';

	// Output UI
	echo '<div class="usof-container" data-ajaxurl="' . admin_url( 'admin-ajax.php' ) . '">';
	echo '<form class="usof-form" method="post" action="#" autocomplete="off">';

	// Output _nonce and _wp_http_referer hidden fields for ajax secuirity checks
	wp_nonce_field( 'usof-actions' );
	echo '<div class="usof-header">';
	echo '<div class="usof-header-logo">';
	echo apply_filters( 'us_theme_name', US_THEMENAME ) . ' <span>' . US_THEMEVERSION . '</span></div>';
	echo '<div class="usof-header-title"><span>' . __( 'Theme Options', 'us' ) . '&nbsp;&mdash;&nbsp;</span>';
	echo '<h2>' . us_translate( 'General' ) . '</h2></div>';

	// Control for opening color schemes window
	echo '<div class="usof-control for_schemes hidden">';
	echo '<a href="javascript:void(0);">' . __( 'Color Schemes', 'us' ) . '</a>';
	echo '</div>';

	// Control for saving changes button
	echo '<div class="usof-control for_save status_clear">';
	echo '<button class="usof-button button-primary type_save" type="button"><span>' . us_translate( 'Save Changes' ) . '</span>';
	echo '<span class="usof-preloader"></span></button>';
	echo '<div class="usof-control-message"></div>';
	echo '</div>';
	echo '</div>';

	// Saving empty or outdated selects
	$empty_select_present = FALSE;
	$updated_options = array();
	foreach ( $usof_options as $key => $val ) {
		$updated_options[ $key ] = $val;
	}

	// Reloading theme options config and values to fill values that depend on each other
	$config = us_config( 'theme-options', array(), TRUE );
	usof_load_options_once( TRUE );
	foreach ( $config as $section_id => $section ) {
		if ( isset( $section['fields'] ) ) {
			foreach ( $section['fields'] as $field_id => $field ) {
				if ( $field['type'] == 'select' ) {
					$field_values = array_keys( $field['options'] );
					if ( ! isset( $updated_options[ $field_id ] ) OR ! in_array( $updated_options[ $field_id ], $field_values ) ) {
						$empty_select_present = TRUE;
						$updated_options[ $field_id ] = array_shift( $field_values );
					}
				}
			}
		}
	}

	if ( $empty_select_present ) {
		usof_save_options( $updated_options );
	}

	// Export typography options to `$usof._$$data` object
	if ( $typography_options = array_intersect_key( $usof_options, array_fill_keys( US_TYPOGRAPHY_TAGS, /* value */'' ) ) ) {
		$typography_options = json_encode( (array) $typography_options, JSON_HEX_APOS );
		echo '<script>
			window.$usof = window.$usof || { _$$data: {} };
			window.$usof._$$data.typographyOptions = \''. $typography_options .'\';
		</script>';
	}

	// Sided Menu
	$visited_new_sections = array();
	if ( isset( $_COOKIE ) AND isset( $_COOKIE['usof_visited_new_sections'] ) ) {
		$visited_new_sections = array_map( 'intval', explode( ',', $_COOKIE['usof_visited_new_sections'] ) );
	}
	echo '<div class="usof-nav"><div class="usof-nav-bg"></div><ul class="usof-nav-list level_1">';
	foreach ( $config as $section_id => &$section ) {
		if ( isset( $section['place_if'] ) AND ! $section['place_if'] ) {
			continue;
		}
		if ( ! isset( $active_section ) ) {
			$active_section = $section_id;
		}
		echo '<li class="usof-nav-item level_1 id_' . $section_id . ( ( $section_id == $active_section ) ? ' current' : '' ) . '"';
		echo ' data-id="' . $section_id . '">';

		// Get anchor atts
		$anchor_atts = array(
			'class' => 'usof-nav-anchor level_1',
			'href' => '#' . $section_id,
		);
		echo '<a ' . us_implode_atts( $anchor_atts ) . '>';
		if ( ! isset( $section['icon'] ) ) {
			$us_icon_uri = US_CORE_URI . '/admin/img/' . $section_id;
			echo '<img class="usof-nav-icon" src="' . $us_icon_uri . '.png" srcset="' . $us_icon_uri . '-2x.png 2x" alt="icon">';
		}
		echo '<span class="usof-nav-title">' . $section['title'] . '</span>';
		echo '<span class="usof-nav-arrow"></span>';
		echo '</a>';
		if ( isset( $section['new'] ) AND $section['new'] AND ! in_array( $section_id, $visited_new_sections ) ) {
			echo '<span class="usof-nav-popup">' . __( 'New', 'us' ) . '</span>';
		}
		echo '</li>';
	}
	echo '<ul></div>';

	// Content
	$hidden_fields_values = array(); // preserve values for hidden fields
	echo '<div class="usof-content">';
	foreach ( $config as $section_id => &$section ) {
		if ( isset( $section['place_if'] ) AND ! $section['place_if'] ) {
			if ( isset( $section['fields'] ) ) {
				$hidden_fields_values = array_merge( $hidden_fields_values, array_intersect_key( $usof_options, $section['fields'] ) );
			}
			continue;
		}
		echo '<section class="usof-section ' . ( ( $section_id == $active_section ) ? 'current' : '' ) . '" data-id="' . $section_id . '">';
		echo '<div class="usof-section-header" data-id="' . $section_id . '">';
		echo '<h3>' . $section['title'] . '</h3><span class="usof-section-header-control"></span></div>';
		echo '<div class="usof-section-content" style="display: ' . ( ( $section_id == $active_section ) ? 'block' : 'none' ) . '">';
		if ( isset( $section['fields'] ) ) {
			foreach ( $section['fields'] as $field_name => &$field ) {
				if ( isset( $field['place_if'] ) AND ! $field['place_if'] ) {
					if ( isset( $usof_options[ $field_name ] ) ) {
						$hidden_fields_values[ $field_name ] = $usof_options[ $field_name ];
					}
					continue;
				}
				us_load_template(
					'usof/templates/field', array(
						'name' => $field_name,
						'id' => 'usof_' . $field_name,
						'field' => $field,
						'values' => &$usof_options,
					)
				);
				if ( isset( $hidden_fields_values[ $field_name ] ) ) {
					unset( $hidden_fields_values[ $field_name ] );
				}
			}
		}
		echo '</div></section>';
	}
	echo '</div>';

	echo '</form>';
	echo '</div>';

	echo '</div>';
	echo '<div class="usof-hidden-fields"' . us_pass_data_to_js( $hidden_fields_values ) . '></div>';
}

function usof_print_scripts() {
	if ( ! did_action( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	// Enqueue USOF JS files separately, when US_DEV is set
	if ( defined( 'US_DEV' ) ) {
		foreach ( us_config( 'assets-admin.js', array() ) as $key => $admin_js_file ) {
			wp_enqueue_script( 'usof-js-' . $key, US_CORE_URI . $admin_js_file, array(), US_CORE_VERSION );
		}
	} else {
		wp_enqueue_script( 'usof-scripts', US_CORE_URI . '/usof/js/usof.min.js', array( 'jquery' ), US_CORE_VERSION, TRUE );
	}

	do_action( 'usof_print_scripts' );
}

function usof_hide_admin_notices_start() {
	?><div class="hidden"><?php
}

function usof_hide_admin_notices_end() {
	?></div><?php
}
