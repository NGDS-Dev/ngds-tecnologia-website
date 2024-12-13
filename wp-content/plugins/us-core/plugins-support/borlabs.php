<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Borlabs Cookie
 *
 * @link https://borlabs.io/borlabs-cookie/
 *
 */

if (
	! defined( 'BORLABS_COOKIE_VERSION' )
	AND ! class_exists( 'BorlabsCookie\\Cookie' )
) {
	return;
}

/**
 * Block content in content_templates and page_block
 */
if ( class_exists( 'BorlabsCookie\\Cookie\\Frontend\\ContentBlocker' ) ) {
	add_filter( 'us_page_block_the_content', [ \BorlabsCookie\Cookie\Frontend\ContentBlocker::getInstance(), 'detectIframes' ], 100, 1 );
	add_filter( 'us_content_template_the_content', [ \BorlabsCookie\Cookie\Frontend\ContentBlocker::getInstance(), 'detectIframes' ], 100, 1 );
}