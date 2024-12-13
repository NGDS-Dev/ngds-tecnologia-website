<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * The entry point for US Builder
 */

// Full link to the builder dir
if ( ! defined( 'US_BUILDER_DIR' ) ) {
	define( 'US_BUILDER_DIR', US_CORE_DIR . 'builder' );
}

// Link to builder directory
if ( ! defined( 'US_BUILDER_URL' ) ) {
	define( 'US_BUILDER_URL', US_CORE_URI . '/builder' );
}

if ( ! function_exists( 'usb_autoload' ) ) {
	/**
	 * Autoload for the USB* classes
	 *
	 * @param string $name The class name
	 */
	function usb_autoload( $name ) {
		/**
		 * @var array Map of available classes
		 * TODO: Rewrite using namespace!
		 */
		$class_map = array(
			'USBuilder' => 'USBuilder',
			'USBuilder_Ajax' => 'USBuilder/Ajax',
			'USBuilder_Assets' => 'USBuilder/Assets',
			'USBuilder_Panel' => 'USBuilder/Panel',
			'USBuilder_Post' => 'USBuilder/Post',
			'USBuilder_Preview' => 'USBuilder/Preview',
			'USBuilder_Shortcode' => 'USBuilder/Shortcode',
		);
		if ( ! isset( $class_map[ $name ] ) ) {
			return;
		}
		$path = US_BUILDER_DIR . '/include/' . $class_map[ $name ] . '.class.php';
		if ( file_exists( $path ) ) {
			require $path;
		}
	}
	spl_autoload_register( 'usb_autoload' );
}

if ( ! function_exists( 'us_init_builder' ) ) {
	// Note: It is important that the builder should be initialized only
	// after the core and plugins have been initialized!
	add_action( 'init', 'us_init_builder', 501 );
	function us_init_builder() {
		new USBuilder; // init Builder
	}
}
