<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * FileBird Support
 *
 * @link https://wordpress.org/plugins/filebird/
 */

if ( ! function_exists( 'FileBird\\init' ) ) {
	return;
}

if ( ! function_exists( 'usb_filebird_enqueue_scripts' ) ) {
	/**
	 * Add FileBird assets to the USBuilder page
	 * TODO: Find the best asset connection solution!
	 */
	function usb_filebird_enqueue_scripts() {
		$classes = array(
			'\\FileBird\\Controller\\Folder', // for version < 6.x
			'\\FileBird\\Classes\\Core', // for version >= 6.x
		);
		foreach ( $classes as $class ) {
			if ( class_exists( $class ) ) {
				$instance = $class::getInstance();
				if ( method_exists( $instance, 'enqueueAdminScripts' ) ) {
					$instance->enqueueAdminScripts( sprintf( '%s.php', get_current_screen()->id ) );
				}
			}
		}
		// or do_action( 'admin_enqueue_scripts' ); // Note: loads other resources for the admin that are not needed
	}
	add_action( 'usb_enqueue_assets_for_builder', 'usb_filebird_enqueue_scripts', 1 );
}

if ( ! function_exists( 'us_filebird_remove_activate_message' ) ) {
	/**
	 * Removing additional messages for activate plugin
	 *
	 */
	function us_filebird_remove_activate_message() {
		if ( defined( 'NJFB_PLUGIN_FILE' ) AND NJFB_PLUGIN_FILE ) {
			remove_all_actions( 'in_plugin_update_message-' . plugin_basename( NJFB_PLUGIN_FILE ), 10 );
		}
	}

	add_action( 'init', 'us_filebird_remove_activate_message', 30 );
}

if ( ! function_exists( 'us_filebird_remove_update_message' ) ) {
	/**
	 * Removing additional messages for update plugin, if theme is not activated
	 *
	 * @param $transient
	 * @return mixed
	 */
	function us_filebird_remove_update_message( $transient ) {
		if (
			! (
				get_option( 'us_license_activated' )
				OR get_option( 'us_license_dev_activated' )
			)
			AND is_object( $transient )
			AND isset( $transient->response[ plugin_basename( NJFB_PLUGIN_FILE ) ] )
		) {
			unset( $transient->response[ plugin_basename( NJFB_PLUGIN_FILE ) ] );
		}

		return $transient;
	}

	add_filter( 'site_transient_update_plugins', 'us_filebird_remove_update_message' );
}
