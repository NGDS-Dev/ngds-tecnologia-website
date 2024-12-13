<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcodes
 *
 * @filter us_config_shortcodes
 */

return array(

	// Main theme elements. The order affects on position in the "Add Element" list in USBuilder
	'theme_elements' => array(

		// Containers
		'vc_row',
		'vc_row_inner',
		'vc_column',
		'vc_column_inner',
		'hwrapper',
		'vwrapper',
		'vc_tta_accordion',
		'vc_tta_tabs',
		'vc_tta_tour',
		'vc_tta_section',

		// Basic
		'vc_column_text',
		'text',
		'btn',
		'iconbox',
		'image',
		'separator',

		// Grid
		'grid',
		'grid_filter',
		'grid_order',
		'carousel',
		'gallery',
		'term_list',
		'user_list',

		// Interactive
		'counter',
		'flipbox',
		'image_slider',
		'ibanner',
		'itext',
		'message',
		'popup',
		'progbar',
		'scroller',

		// Other
		'page_block',
		'cform',
		'contacts',
		'cta',
		'dropdown',
		'gmaps',
		'login',
		'person',
		'pricing',
		'additional_menu',
		'search',
		'sharing',
		'socials',
		'vc_video',
		'html',
		'gravityform',
		'contact-form-7',

		// Post Elements
		'post_content',
		'post_image',
		'post_title',
		'post_custom_field',
		'post_date',
		'post_taxonomy',
		'post_author',
		'post_comments',
		'post_navigation',
		'post_views',
		'breadcrumbs',

		// WooCommerce
		'add_to_cart',
		'product_field',
		'product_gallery',
		'product_list',
		'product_ordering',
		'cart_table',
		'cart_totals',
		'checkout_billing',
		'checkout_order_review',
		'checkout_payment',
		'checkout_login',
		'wc_account_login',
		'wc_account_navigation',
		'wc_account_content',
		'wc_coupon_form',
		'wc_notices',
		'wc_order_data',
		'woocommerce_cart',
		'woocommerce_checkout',
		'woocommerce_my_account',

		// Import templates
		'import_template'
	),

	// Shortcodes, that use template file of other shortcodes
	'alias' => array(
		'vc_column_inner' => 'vc_column', // for example, vc_column_inner uses vc_column template file
		'vc_tta_accordion' => 'vc_tta_tabs',
		'vc_tta_tour' => 'vc_tta_tabs',
		'us_carousel' => 'us_grid',
		'gallery' => 'us_gallery',
	),

	// VC shortcodes, which are disabled by default
	'disabled' => array(
		'vc_accordion',
		'vc_accordion_tab',
		'vc_acf',
		'vc_basic_grid',
		'vc_btn',
		'vc_button2',
		'vc_cta',
		'vc_empty_space',
		'vc_facebook',
		'vc_flickr',
		'vc_gallery',
		'vc_gmaps',
		'vc_googleplus',
		'vc_gutenberg',
		'vc_hoverbox',
		'vc_icon',
		'vc_images_carousel',
		'vc_masonry_grid',
		'vc_masonry_media_grid',
		'vc_media_grid',
		'vc_message',
		'vc_pie',
		'vc_pinterest',
		'vc_posts_slider',
		'vc_pricing_table',
		'vc_progress_bar',
		'vc_section',
		'vc_separator',
		'vc_single_image',
		'vc_tab',
		'vc_tabs',
		'vc_text_separator',
		'vc_toggle',
		'vc_tour',
		'vc_tta_pageable',
		'vc_tta_toggle',
		'vc_tweetmeme',
		'vc_wp_text',
		'vc_zigzag',

		// WooCommerce
		'add_to_cart_url',
		// 'product_page', // TODO: fix the case when this shortcode breaks page editing in Live builder
		'product',
		'products',
		'product_category',
		'product_categories',
		'top_rated_products',
		'best_selling_products',
		'recent_products',
		'featured_products',
		'sale_products',
	),

	// VC shortcodes, which don't have theme configs, but needed theme Design options
	'added_design_options' => array(
		'vc_custom_heading',
		'vc_line_chart',
		'vc_raw_html',
		'vc_round_chart',
	),
);
