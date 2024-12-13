<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Grid Templates
 */

$icon_quote = ( US_THEMENAME == 'Zephyr' ) ? 'material|format_quote' : 'far|quote-left';

return array(

/* Blog - several columns =========================================================================== */

'blog_1' => array(
	'title' => 'Image & Title',
	'group' => __( 'Blog Templates', 'us' ) . ' ' . __( '(for several columns)', 'us' ),
	'data' => array(
		'post_image:1' => array(
			'css' => array(
				'default' => array(
					'margin-bottom' => '0.5rem',
				)
			),
		),
		'post_title:1' => array(
			'show_count' => 1,
			'css' => array(
				'default' => array(
					'font-size' => '1rem',
				)
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'post_title:1',
			),
		),
	),
),

'blog_4' => array(
	'title' => 'Image first (date)',
	'data' => array(
		'post_image:1' => array(
			'css' => array(
				'default' => array(
					'margin-bottom' => '0.6rem',
				),
			),
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '1.2rem',
					'margin-bottom' => '0.3rem',
				),
			),
		),
		'post_date:1' => array(
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.8rem',
					'line-height' => '1.6',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'post_title:1',
				'post_date:1',
			),
		),
	),
),

'blog_classic' => array(
	'title' => 'Image first (category, date, excerpt)',
	'cols' => '2',
	'data' => array(
		'post_image:1' => array(
			'media_preview' => 1,
			'css' => array(
				'default' => array(
					'margin-bottom' => '1rem',
				),
			),
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '1.4rem',
					'font-weight' => '700',
					'margin-bottom' => '0.5rem',
				),
			),
		),
		'hwrapper:1' => array(
			'wrap' => 1,
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'text-transform' => 'uppercase',
				),
			),
		),
		'post_content:1' => array(
			'css' => array(
				'default' => array(
					'max-width' => '600px',
					'margin-top' => '0.5rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'post_title:1',
				'hwrapper:1',
				'post_content:1',
			),
			'hwrapper:1' => array(
				'post_taxonomy:1',
				'post_date:1',
			),
		),
	),
),

'blog_11' => array(
	'title' => 'Title First (category, date, excerpt)',
	'cols' => '2',
	'data' => array(
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '1.4rem',
					'font-weight' => '700',
					'margin-bottom' => '0.5rem',
				),
				'mobiles' => array(
					'font-size' => '1.2rem',
				),
			),
		),
		'hwrapper:1' => array(
			'wrap' => 1,
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'text-transform' => 'uppercase',
				),
			),
		),
		'post_image:1' => array(
			'media_preview' => 1,
			'css' => array(
				'default' => array(
					'margin-top' => '0.7rem',
				),
			),
		),
		'post_content:1' => array(
			'css' => array(
				'default' => array(
					'max-width' => '600px',
					'margin-top' => '0.8rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_title:1',
				'hwrapper:1',
				'post_image:1',
				'post_content:1',
			),
			'hwrapper:1' => array(
				'post_taxonomy:1',
				'post_date:1',
			),
		),
	),
),

'blog_6' => array(
	'title' => 'Corner Tile (category, date)',
	'cols' => '2',
	'data' => array(
		'post_image:1' => array(
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'scale_hover' => '1.1',
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '1.4rem',
					'color' => 'inherit',
				),
				'mobiles' => array(
					'font-size' => '1.2rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'text-transform' => 'uppercase',
					'margin-left' => is_rtl() ? '0.6rem' : '',
					'margin-right' => is_rtl() ? '' : '0.6rem',
				),
			),
		),
		'post_date:1' => array(),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'background-color' => '_content_bg',
					'color' => '_content_heading',
					'position' => 'absolute',
					'left' => is_rtl() ? '2rem' : '0',
					'right' => is_rtl() ? '0' : '2rem',
					'bottom' => '0',
					'padding-top' => '0.8rem',
					'padding-left' => is_rtl() ? '2rem' : '',
					'padding-right' => is_rtl() ? '' : '2rem',
				),
			),
		),
		'hwrapper:1' => array(
			'wrap' => 1,
			'css' => array(
				'default' => array(
					'font-size' => '14px',
					'line-height' => '20px',
					'color' => '_content_faded',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'hwrapper:1',
				'post_title:1',
			),
			'hwrapper:1' => array(
				'post_taxonomy:1',
				'post_date:1',
			),
		),
		'options' => array(
			'fixed' => 1,
			'ratio' => '4x3',
		),
	),
),

'blog_8' => array(
	'title' => 'Square Image overlapped by Title (category, date, excerpt)',
	'cols' => '2',
	'data' => array(
		'hwrapper:1' => array(),
		'post_image:1' => array(
			'placeholder' => 1,
			'has_ratio' => 1,
			'css' => array(
				'default' => array(
					'width' => '50%',
				),
			),
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'width' => '50%',
					'margin-left' => is_rtl() ? '0' : '',
					'margin-right' => is_rtl() ? '' : '0',
				),
			),
		),
		'hwrapper:2' => array(
			'wrap' => 1,
			'css' => array(
				'default' => array(
					'margin-bottom' => '0',
				),
			),
		),
		'post_taxonomy:1' => array(
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-size' => '14px',
					'line-height' => '1.4',
					'font-weight' => '700',
					'text-transform' => 'uppercase',
					'margin-left' => is_rtl() ? '0.8rem' : '',
					'margin-right' => is_rtl() ? '' : '0.8rem',
				),
			),
		),
		'post_date:1' => array(
			'hide_below' => '600px',
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '14px',
					'line-height' => '1.4',
				),
			),
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'color' => '_content_heading',
					'background-color' => '_content_bg',
					'font-size' => '1.8rem',
					'line-height' => '1.2',
					'margin-left' => is_rtl() ? '-35%' : '',
					'margin-right' => is_rtl() ? '' : '-35%',
					'margin-bottom' => '0',
					'padding-top' => '1rem',
					'padding-left' => is_rtl() ? '1.2rem' : '',
					'padding-right' => is_rtl() ? '' : '1.2rem',
					'padding-bottom' => '1rem',
				),
				'mobiles' => array(
					'font-size' => '1.2rem',
					'margin-left' => is_rtl() ? '0' : '',
					'margin-right' => is_rtl() ? '' : '0',
					'padding-bottom' => '0',
				),
			),
		),
		'post_content:1' => array(
			'length' => '24',
			'css' => array(
				'default' => array(
					'font-size' => '0.9rem',
					'line-height' => '1.6',
					'padding-left' => is_rtl() ? '1.5rem' : '',
					'padding-right' => is_rtl() ? '' : '1.5rem',
				),
			),
			'hide_below' => '600px',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'hwrapper:1',
			),
			'hwrapper:1' => array(
				'vwrapper:1',
				'post_image:1',
			),
			'vwrapper:1' => array(
				'hwrapper:2',
				'post_title:1',
				'post_content:1',
			),
			'hwrapper:2' => array(
				'post_taxonomy:1',
				'post_date:1',
			),
		),
		'options' => array(
			'overflow' => 1,
		),
	),
),

'blog_9' => array(
	'title' => 'Card White (category, date)',
	'items_gap' => '5px',
	'data' => array(
		'post_image:1' => array(
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'padding-top' => '5%',
					'padding-right' => '8%',
					'padding-bottom' => '8%',
					'padding-left' => '8%',
				),
			),
		),
		'hwrapper:1' => array(
			'wrap' => 1,
			'css' => array(
				'default' => array(
					'margin-bottom' => '0.2rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-size' => '0.8rem',
					'font-weight' => '700',
					'text-transform' => 'uppercase',
					'margin-left' => is_rtl() ? '0.6rem' : '',
					'margin-right' => is_rtl() ? '' : '0.6rem',
				),
			),
		),
		'post_date:1' => array(
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.8rem',
					'margin-bottom' => '0.3rem',
				),
			),

		),
		'post_title:1' => array(
			'show_count' => 1,
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-weight' => '700',
					'font-size' => '1.4rem',
				),
				'mobiles' => array(
					'font-size' => '1.2rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'hwrapper:1',
				'post_title:1',
			),
			'hwrapper:1' => array(
				'post_taxonomy:1',
				'post_date:1',
			),
		),
		'options' => array(
			'overflow' => 1,
			'color_bg' => '_content_bg',
			'color_text' => '_content_heading',
			'border_radius' => us_get_option( 'rounded_corners' ) ? '0.3rem' : '',
			'box_shadow' => '0.3rem',
			'box_shadow_hover' => '1.2rem',
		),
	),
),

'blog_cards' => array(
	'title' => 'Card White (category, date, excerpt)',
	'items_gap' => '5px',
	'data' => array(
		'post_image:1' => array(
			'css' => array(
				'default' => array(
					'margin-bottom' => '-2rem',
				),
			),
		),
		'post_title:1' => array(
			'show_count' => 1,
			'css' => array(
				'default' => array(
					'font-size' => '1.4rem',
					'font-weight' => '700',
				),
				'mobiles' => array(
					'font-size' => '1.2rem',
				),
			),
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'margin-top' => '2rem',
					'padding-top' => '9%',
					'padding-right' => '11%',
					'padding-bottom' => '11%',
					'padding-left' => '11%',
				),
			),
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'category',
			'style' => 'badge',
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'text-transform' => 'uppercase',
					'font-size' => '10px',
					'position' => 'absolute',
					'z-index' => '2',
					'top' => '1.2rem',
					'right' => '1.2rem',
					'left' => '1.2rem',
				),
			),
		),
		'hwrapper:1' => array(
			'wrap' => 1,
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
				),
			),
		),
		'post_date:1' => array(
		),
		'post_content:1' => array(
			'length' => '20',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'post_taxonomy:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'hwrapper:1',
				'post_content:1',
			),
			'hwrapper:1' => array(
				'post_date:1',
			),
		),
		'options' => array(
			'overflow' => 1,
			'color_bg' => '_content_bg',
			'color_text' => '_content_text',
			'border_radius' => us_get_option( 'rounded_corners' ) ? '0.3rem' : '',
			'box_shadow' => '0.3rem',
			'box_shadow_hover' => '1rem',
		),
	),
),

'blog_10' => array(
	'title' => 'Card Gradient (category)',
	'items_gap' => '5px',
	'data' => array(
		'post_image:1' => array(
			'placeholder' => 1,
			'hover' => 1,
			'scale_hover' => '1.1',
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'background-color' => 'linear-gradient(transparent, rgba(30,30,30,0.8))',
					'position' => 'absolute',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '15%',
					'padding-right' => '7%',
					'padding-bottom' => '7%',
					'padding-left' => '7%',
				),
			),
		),
		'post_taxonomy:1' => array(
			'style' => 'badge',
			'css' => array(
				'default' => array(
					'font-size' => '10px',
					'font-weight' => '700',
					'text-transform' => 'uppercase',
					'margin-bottom' => '0.5rem',
				),
			),
		),
		'post_title:1' => array(
			'show_count' => 1,
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '1.4rem',
					'font-weight' => '700',
				),
				'mobiles' => array(
					'font-size' => '1.2rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_taxonomy:1',
				'post_title:1',
			),
		),
		'options' => array(
			'overflow' => 1,
			'color_bg' => '#333',
			'border_radius' => us_get_option( 'rounded_corners' ) ? '0.3rem' : '',
			'box_shadow' => '0.3rem',
			'box_shadow_hover' => '1.2rem',
		),
	),
),

'blog_tiles' => array(
	'title' => 'Tile Gradient (category, date)',
	'items_gap' => '3px',
	'data' => array(
		'post_image:1' => array(
			'placeholder' => 1,
			'hover' => 1,
			'scale_hover' => '1.1',
		),
		'vwrapper:1' => array(
			'valign' => 'bottom',
			'css' => array(
				'default' => array(
					'background-color' => 'linear-gradient(transparent, rgba(30,30,30,0.8))',
					'position' => 'absolute',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '5rem',
					'padding-right' => '8%',
					'padding-bottom' => '1.5rem',
					'padding-left' => '8%',
				),
			),
		),
		'post_title:1' => array(
			'show_count' => 1,
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '1.2rem',
					'font-weight' => '700',
					'margin-bottom' => '0.3rem',
				),
			),
		),
		'post_date:1' => array(
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '0.9rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'category',
			'style' => 'badge',
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'text-transform' => 'uppercase',
					'font-size' => '10px',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_taxonomy:1',
				'post_title:1',
				'post_date:1',
			),
		),
		'options' => array(
			'overflow' => 1,
		),
	),
),

'blog_flat' => array(
	'title' => 'Tile Centered (date, excerpt)',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'media_preview' => 1,
			'css' => array(
				'default' => array(
					'margin-bottom' => '-1rem',
				),
			),
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'css' => array(
				'default' => array(
					'padding-top' => '2.5rem',
					'padding-right' => '2.5rem',
					'padding-bottom' => '2.5rem',
					'padding-left' => '2.5rem',
				),
			),
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '1.2rem',
					'font-weight' => '700',
				),
			),
		),
		'hwrapper:2' => array(
			'alignment' => 'center',
			'wrap' => 1,
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
				),
			),
		),
		'post_content:1' => array(
			'length' => '20',
		),
		'post_date:1' => array(
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'hwrapper:2' => array(
				'post_date:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'hwrapper:2',
				'post_content:1',
			),
		),
		'options' => array(
			'overflow' => 1,
			'color_bg' => '_content_bg',
			'color_text' => '_content_text',
			'box_shadow_hover' => '1.5rem',
		),
	),
),

'blog_side_image' => array(
	'title' => 'Circle Image (date, comments, excerpt)',
	'cols' => '2',
	'data' => array(
		'hwrapper:1' => array(
			'wrap' => 1,
		),
		'post_image:1' => array(
			'placeholder' => 1,
			'circle' => 1,
			'has_ratio' => 1,
			'css' => array(
				'default' => array(
					'width' => '30%',
					'margin-right' => is_rtl() ? '0' : '5%',
					'margin-left' => is_rtl() ? '5%' : '0',
				),
				'mobiles' => array(
					'width' => '100%',
					'margin-right' => '0',
					'margin-left' => '0',
					'margin-bottom' => '5%',
				),
			),
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'width' => '65%',
				),
				'mobiles' => array(
					'width' => '100%',
				),
			),
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '1.4rem',
				),
			),
		),
		'hwrapper:2' => array(
			'wrap' => 1,
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
				),
			),
		),
		'post_content:1' => array(
		),
		'post_date:1' => array(
		),
		'post_comments:1' => array(
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'hwrapper:1',
			),
			'hwrapper:1' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'hwrapper:2',
				'post_content:1',
			),
			'hwrapper:2' => array(
				'post_date:1',
				'post_comments:1',
			),
		),
	),
),

'blog_5' => array(
	'title' => 'Square Image (date)',
	'data' => array(
		'post_image:1' => array(
			'placeholder' => 1,
			'thumbnail_size' => 'thumbnail',
			'has_ratio' => 1,
			'css' => array(
				'default' => array(
					'width' => '30%',
					'margin-right' => is_rtl() ? '' : '5%',
					'margin-left' => is_rtl() ? '5%' : '',
				),
			),
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '1.2rem',
					'margin-bottom' => '0.3rem',
				),
			),
		),
		'hwrapper:1' => array(),
		'post_date:1' => array(
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.8rem',
					'line-height' => '1.6',
				),
			),
		),
		'vwrapper:1' => array(),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'hwrapper:1',
			),
			'hwrapper:1' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_date:1',
			),
		),
	),
),

'tile_21_left' => array(
	'title' => 'Side Image Left 2:1',
	'cols' => '2',
	'items_gap' => '5px',
	'data' => array(
		'post_image:1' => array(
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '50%',
					'bottom' => '0',
					'left' => '0',
				),
			),
		),
		'post_title:1' => array(
			'show_count' => 1,
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.2rem',
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '50%',
					'padding-top' => '8%',
					'padding-right' => '8%',
					'padding-bottom' => '8%',
					'padding-left' => '8%',
				),
				'mobiles' => array(
					'font-size' => '1rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_title:1',
				'post_image:1',
			),
		),
		'options' => array(
			'fixed' => 1,
			'ratio' => 'custom',
			'ratio_width' => '2',
			'ratio_height' => '1',
			'color_bg' => '_content_bg_alt',
			'color_text' => '_content_heading',
		),
	),
),

'tile_21_right' => array(
	'title' => 'Side Image Right 2:1',
	'cols' => '2',
	'items_gap' => '5px',
	'data' => array(
		'post_image:1' => array(
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '50%',
				),
			),
		),
		'post_title:1' => array(
			'show_count' => 1,
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.2rem',
					'position' => 'absolute',
					'top' => '0',
					'right' => '50%',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '8%',
					'padding-right' => '8%',
					'padding-bottom' => '8%',
					'padding-left' => '8%',
				),
				'mobiles' => array(
					'font-size' => '1rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_title:1',
				'post_image:1',
			),
		),
		'options' => array(
			'fixed' => 1,
			'ratio' => 'custom',
			'ratio_width' => '2',
			'ratio_height' => '1',
			'color_bg' => '_content_bg_alt',
			'color_text' => '_content_heading',
		),
	),
),

/* Blog - 1 column =========================================================================== */

'side_image_2' => array(
	'title' => 'Image at Right (category)',
	'group' => __( 'Blog Templates', 'us' ) . ' ' . __( '(for single column)', 'us' ),
	'cols' => '1',
	'items_gap' => '3rem',
	'data' => array(
	'post_image:1' => array(
		'placeholder' => 1,
		'has_ratio' => 1,
		'ratio' => '16x9',
		'css' => array(
		'default' => array(
			'width' => '70%',
		),
		'laptops' => array(
			'width' => '70%',
		),
		'tablets' => array(
			'width' => '100%',
		),
		'mobiles' => array(
			'width' => '100%',
		),
		),
	),
	'post_title:1' => array(
		'css' => array(
		'default' => array(
			'font-size' => 'max( 1.4rem, 2vw )',
			'font-weight' => '700',
		),
		),
	),
	'hwrapper:1' => array(
		'valign' => 'bottom',
		'inner_items_gap' => '0rem',
		'wrap' => 1,
	),
	'vwrapper:1' => array(
		'css' => array(
		'default' => array(
			'width' => '30%',
			'padding-right' => is_rtl() ? '' : '5%',
			'padding-left' => is_rtl() ? '5%' : '',
			'padding-top' => '1rem',
		),
		'laptops' => array(
			'width' => '30%',
			'padding-right' => is_rtl() ? '' : '5%',
			'padding-left' => is_rtl() ? '5%' : '',
			'padding-top' => '1rem',
		),
		'tablets' => array(
			'width' => '100%',
			'padding-left' => '0',
			'padding-right' => '0',
			'padding-top' => '1rem',
		),
		'mobiles' => array(
			'width' => '100%',
			'padding-left' => '0',
			'padding-right' => '0',
			'padding-top' => '1rem',
		),
		),
	),
	'post_taxonomy:1' => array(
		'color_link' => '',
		'css' => array(
		'default' => array(
			'font-size' => '0.8rem',
			'font-weight' => '700',
			'text-transform' => 'uppercase',
		),
		),
	),
	),
	'default' => array(
	'layout' => array(
		'middle_center' => array(
		0 => 'hwrapper:1',
		),
		'hwrapper:1' => array(
		0 => 'vwrapper:1',
		1 => 'post_image:1',
		),
		'vwrapper:1' => array(
		0 => 'post_taxonomy:1',
		1 => 'post_title:1',
		),
	),
	),
),

'side_image_3' => array(
	'title' => 'Trendy Image at Left (date)',
	'cols' => '1',
	'items_gap' => '3rem',
	'data' => array(
	'post_image:1' => array(
		'placeholder' => 1,
		'has_ratio' => 1,
		'ratio' => '16x9',
		'css' => array(
		'default' => array(
			'width' => '70%',
			'box-shadow-h-offset' => is_rtl() ? '-100px' : '100px',
			'box-shadow-v-offset' => '20px',
			'box-shadow-blur' => '0',
			'box-shadow-color' => '_content_bg_alt',
		),
		'laptops' => array(
			'width' => '70%',
		),
		'tablets' => array(
			'width' => '100%',
		),
		'mobiles' => array(
			'width' => '100%',
		),
		),
	),
	'post_title:1' => array(
		'css' => array(
		'default' => array(
			'font-size' => 'max( 1.4rem, 2vw )',
			'font-weight' => '700',
		),
		),
	),
	'hwrapper:1' => array(
		'valign' => 'middle',
		'inner_items_gap' => '0rem',
		'wrap' => 1,
		'conditions' => array(
		),
	),
	'vwrapper:1' => array(
		'conditions' => array(
		),
		'css' => array(
		'default' => array(
			'width' => '30%',
			'padding-right' => is_rtl() ? '4%' : '',
			'padding-left' => is_rtl() ? '' : '4%',
			'padding-top' => '1rem',
			'padding-bottom' => '1rem',
		),
		'laptops' => array(
			'width' => '30%',
			'padding-right' => is_rtl() ? '4%' : '',
			'padding-left' => is_rtl() ? '' : '4%',
			'padding-top' => '1rem',
			'padding-bottom' => '1rem',
		),
		'tablets' => array(
			'width' => '100%',
			'padding-left' => '0',
			'padding-right' => '0',
			'padding-top' => '1rem',
			'padding-bottom' => '0',
		),
		'mobiles' => array(
			'width' => '100%',
			'padding-left' => '0',
			'padding-right' => '0',
			'padding-top' => '1rem',
			'padding-bottom' => '0',
		),
		),
	),
	'post_date:1' => array(
		'format' => 'smart',
	),
	),
	'default' => array(
	'layout' => array(
		'middle_center' => array(
		0 => 'hwrapper:1',
		),
		'hwrapper:1' => array(
		0 => 'post_image:1',
		1 => 'vwrapper:1',
		),
		'vwrapper:1' => array(
		0 => 'post_title:1',
		1 => 'post_date:1',
		),
	),
	),
),

'side_image_4' => array(
	'title' => '4:3 Image at Left (date, excerpt, more)',
	'cols' => '1',
	'items_gap' => '4rem',
	'data' => array(
	'post_image:1' => array(
		'placeholder' => 1,
		'has_ratio' => 1,
		'ratio' => '4x3',
		'css' => array(
		'default' => array(
			'width' => '45%',
			'max-width' => '400px',
		),
		'laptops' => array(
			'width' => '45%',
		),
		'tablets' => array(
			'width' => '100%',
		),
		'mobiles' => array(
			'width' => '100%',
		),
		),
	),
	'post_title:1' => array(
		'css' => array(
		'default' => array(
			'font-size' => '1.6rem',
			'font-weight' => '700',
			'margin-bottom' => '0.2rem',
		),
		'laptops' => array(
			'font-size' => '1.6rem',
			'font-weight' => '700',
		),
		'tablets' => array(
			'font-size' => '1.6rem',
			'font-weight' => '700',
		),
		'mobiles' => array(
			'font-size' => '1.3rem',
			'font-weight' => '700',
		),
		),
	),
	'hwrapper:1' => array(
		'valign' => 'stretch',
		'inner_items_gap' => '0rem',
		'wrap' => 1,
	),
	'vwrapper:1' => array(
		'conditions' => array(
		),
		'css' => array(
		'default' => array(
			'width' => '55%',
			'padding-left' => is_rtl() ? '' : '2rem',
			'padding-right' => is_rtl() ? '2rem' : '',
			'padding-top' => '0',
		),
		'laptops' => array(
			'width' => '55%',
			'padding-left' => is_rtl() ? '' : '2rem',
			'padding-right' => is_rtl() ? '2rem' : '',
			'padding-top' => '0',
		),
		'tablets' => array(
			'width' => '100%',
			'max-width' => '600px',
			'padding-left' => '0',
			'padding-right' => '0',
			'padding-top' => '1rem',
		),
		'mobiles' => array(
			'width' => '100%',
			'padding-left' => '0',
			'padding-right' => '0',
			'padding-top' => '1rem',
		),
		),
	),
	'post_date:1' => array(
		'format' => 'smart',
		'css' => array(
		'default' => array(
			'color' => '_content_faded',
			'font-size' => '0.9rem',
			'margin-bottom' => 'auto',
		),
		),
	),
	'post_content:1' => array(
		'css' => array(
		'default' => array(
			'margin-top' => '1rem',
			'margin-bottom' => '1.3rem',
		),
		),
	),
	'text:1' => array(
		'text' => __( 'Learn more', 'us' ),
		'link' => '{"type":"post"}',
		'icon' => 'far|plus',
		'css' => array(
		'default' => array(
			'color' => '_content_text',
			'font-size' => '0.9rem',
			'line-height' => '1',
			'margin-top' => 'auto',
		),
		'mobiles' => array(
			'font-size' => '1rem',
		),
		),
	),
	),
	'default' => array(
	'layout' => array(
		'middle_center' => array(
		0 => 'hwrapper:1',
		),
		'hwrapper:1' => array(
		0 => 'post_image:1',
		1 => 'vwrapper:1',
		),
		'vwrapper:1' => array(
		0 => 'post_title:1',
		1 => 'post_date:1',
		2 => 'post_content:1',
		3 => 'text:1',
		),
	),
	),
),

'blog_12' => array(
	'title' => 'Modern Blog (excerpt, date, category)',
	'cols' => '1',
	'data' => array(
	'post_title:1' => array(
		'css' => array(
		'default' => array(
			'max-width' => '610px',
			'margin-left' => 'auto',
			'margin-bottom' => '3rem',
			'margin-right' => 'auto',
		),
		),
	),
	'post_taxonomy:1' => array(
		'color_link' => '',
	),
	'post_date:1' => array(
		'format' => 'time_diff',
	),
	'post_image:1' => array(
		'media_preview' => 1,
		'thumbnail_size' => 'full',
		'css' => array(
		'default' => array(
			'margin-bottom' => '2rem',
		),
		),
	),
	'post_content:1' => array(
		'length' => '50',
		'css' => array(
		'default' => array(
			'font-size' => '1.2rem',
			'line-height' => '1.7',
			'max-width' => '610px',
			'margin-left' => 'auto',
			'margin-bottom' => '3rem',
			'margin-right' => 'auto',
		),
		),
	),
	'vwrapper:1' => array(
		'inner_items_gap' => '0rem',
		'css' => array(
		'default' => array(
			'max-width' => '610px',
			'margin-left' => 'auto',
			'margin-bottom' => '5rem',
			'margin-right' => 'auto',
			'padding-bottom' => '5rem',
			'border-style' => 'solid',
			'border-bottom-width' => '1px',
		),
		),
	),
	),
	'default' => array(
	'layout' => array(
		'middle_center' => array(
		0 => 'post_title:1',
		1 => 'post_image:1',
		2 => 'post_content:1',
		3 => 'vwrapper:1',
		),
		'vwrapper:1' => array(
		0 => 'post_date:1',
		1 => 'post_taxonomy:1',
		),
	),
	),
),

'blog_14' => array(
	'title' => 'Float Image at Left (date, excerpt)',
	'cols' => '1',
	'items_gap' => '3rem',
	'data' => array(
		'hwrapper:1' => array(
			'valign' => 'center',
			'wrap' => 1,
			'css' => array(
				'default' => array(
					'color' => '_content_text',
					'background-color' => '_content_bg_alt',
					'margin-left' => is_rtl() ? '' : '10%',
					'margin-right' => is_rtl() ? '10%' : '',
					'padding-top' => '6%',
					'padding-bottom' => '6%',
				),
				'mobiles' => array(
					'margin-left' => '0',
					'margin-right' => '0',
					'padding-top' => '0',
					'padding-bottom' => '0',
				),
			),
		),
		'post_image:1' => array(
			'media_preview' => 1,
			'css' => array(
				'default' => array(
					'width' => '50%',
					'margin-left' => is_rtl() ? '0' : '-11%',
					'margin-right' => is_rtl() ? '-11%' : '0',
				),
				'mobiles' => array(
					'width' => '100%',
					'margin-left' => '0',
					'margin-right' => '0',
				),
			),
			'hide_below' => '480px',
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'width' => '48%',
					'margin-left' => is_rtl() ? '' : '7%',
					'margin-right' => is_rtl() ? '7%' : '',
				),
				'mobiles' => array(
					'width' => '86%',
					'margin-top' => '5%',
					'margin-left' => '7%',
					'margin-right' => '7%',
					'margin-bottom' => '7%',
				),
			),
		),
		'post_date:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '0.9rem',
				),
			),
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'line-height' => '1.2',
				),
			),
		),
		'post_content:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '0.9rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'hwrapper:1',
			),
			'hwrapper:1' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_date:1',
				'post_title:1',
				'post_content:1',
			),
		),
	),
),

'blog_13' => array(
	'title' => 'Float Image at Right (date, excerpt)',
	'cols' => '1',
	'items_gap' => '3rem',
	'data' => array(
		'hwrapper:1' => array(
			'valign' => 'center',
			'wrap' => 1,
			'css' => array(
				'default' => array(
					'color' => '_content_text',
					'background-color' => '_content_bg_alt',
					'margin-left' => is_rtl() ? '10%' : '',
					'margin-right' => is_rtl() ? '' : '10%',
					'padding-top' => '6%',
					'padding-bottom' => '6%',
				),
				'mobiles' => array(
					'margin-left' => '0',
					'margin-right' => '0',
					'padding-top' => '0',
					'padding-bottom' => '0',
				),
			),
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'width' => '48%',
					'margin-left' => is_rtl() ? '0' : '6%',
					'margin-right' => is_rtl() ? '6%' : '0',
				),
				'mobiles' => array(
					'width' => '86%',
					'margin-top' => '7%',
					'margin-left' => '7%',
					'margin-right' => '7%',
					'margin-bottom' => '5%',
				),
			),
		),
		'post_image:1' => array(
			'media_preview' => 1,
			'css' => array(
				'default' => array(
					'width' => '50%',
					'margin-left' => is_rtl() ? '-11%' : '7%',
					'margin-right' => is_rtl() ? '7%' : '-11%',
				),
				'mobiles' => array(
					'width' => '100%',
					'margin-left' => '0',
					'margin-right' => '0',
				),
			),
			'hide_below' => '480px',
		),
		'post_date:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '0.9rem',
				),
			),
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'line-height' => '1.2',
				),
			),
		),
		'post_content:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '0.9rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'hwrapper:1',
			),
			'hwrapper:1' => array(
				'vwrapper:1',
				'post_image:1',
			),
			'vwrapper:1' => array(
				'post_date:1',
				'post_title:1',
				'post_content:1',
			),
		),
	),
),

/* News =========================================================================== */

'blog_3' => array(
	'title' => 'Title only',
	'group' => __( 'Blog Templates', 'us' ) . ' ' . __( '(without images)', 'us' ),
	'cols' => '4',
	'data' => array(
		'post_title:1' => array(
			'show_count' => 1,
			'tag' => 'div',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_title:1',
			),
		),
	),
),

'blog_2' => array(
	'title' => 'Title & Date',
	'cols' => '4',
	'data' => array(
		'post_title:1' => array(
			'show_count' => 1,
			'tag' => 'h2',
			'css' => array(
				'default' => array(
					'font-size' => '1rem',
				),
			),
		),
		'post_date:1' => array(
			'format' => 'smart',
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.8rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_title:1',
				'post_date:1',
			),
		),
	),
),

'blog_compact' => array(
	'title' => 'Title & Date inline',
	'data' => array(
		'hwrapper:1' => array(
			'wrap' => 1,
			'valign' => 'baseline',
			'inner_items_gap' => '0.8rem',
		),
		'post_title:1' => array(
			'show_count' => 1,
			'color_link' => 0,
			'tag' => 'div',
			'css' => array(
				'default' => array(
					'font-size' => '1rem',
					'margin-bottom' => '0',
				),
			),
		),
		'post_date:1' => array(
			'format' => 'smart',
			'css' => array(
				'default' => array(
					'font-size' => '0.8rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'hwrapper:1',
			),
			'hwrapper:1' => array(
				'post_title:1',
				'post_date:1',
			),
		),
	),
),

'text_card' => array(
	'title' => 'Text Card (category, date)',
	'items_gap' => '10px',
	'data' => array(
	'vwrapper:1' => array(
		'inner_items_gap' => '0.5rem',
		'css' => array(
		'default' => array(
			'padding-top' => '10%',
			'padding-right' => '10%',
			'padding-bottom' => '10%',
			'padding-left' => '10%',
			'border-style' => 'solid',
			'border-top-width' => '1px',
			'border-right-width' => '1px',
			'border-bottom-width' => '1px',
			'border-left-width' => '1px',
			'border-color' => '_content_border',
			'border-radius' => us_get_option( 'rounded_corners' ) ? '0.3rem' : '',
		),
		),
		'hover' => 1,
		'color_border_hover' => 'transparent',
	),
	'post_title:1' => array(
		'show_count' => 1,
		'css' => array(
		'default' => array(
			'font-weight' => '700',
			'font-size' => '1.1rem',
		),
		),
	),
	'post_taxonomy:1' => array(
		'color_link' => '0',
		'css' => array(
		'default' => array(
			'font-weight' => '700',
			'text-transform' => 'uppercase',
			'font-size' => '14px',
		),
		),
	),
	'post_date:1' => array(
		'format' => 'smart',
		'css' => array(
		'default' => array(
			'font-size' => '14px',
		),
		),
	),
	),
	'default' => array(
	'options' => array(
		'border_radius' => us_get_option( 'rounded_corners' ) ? '0.3rem' : '',
		'box_shadow_hover' => '2rem',
	),
	'layout' => array(
		'middle_center' => array(
		0 => 'vwrapper:1',
		),
		'vwrapper:1' => array(
		0 => 'post_taxonomy:1',
		1 => 'post_title:1',
		2 => 'post_date:1',
		),
	),
	),
),

/* Gallery =========================================================================== */

'gallery_default' => array(
	'title' => __( 'Image Gallery', 'us' ),
	'group' => __( 'Gallery Templates', 'us' ),
	'cols' => '6',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
			),
		),
	),
),

'gallery_cropped' => array(
	'title' => __( 'Image Gallery', 'us' ) . ' (' . __( 'cropped', 'us' ) . ')',
	'cols' => '6',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'left' => '0',
					'top' => '0',
					'bottom' => '0',
					'right' => '0',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				0 => 'post_image:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'gallery_with_titles_below' => array(
	'title' => __( 'Image Gallery with titles BELOW the image', 'us' ),
	'cols' => '6',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'has_ratio' => 1,
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'css' => array(
				'default' => array(
					'padding-top' => '0.5rem',
					'padding-right' => '1rem',
					'padding-bottom' => '1rem',
					'padding-left' => '1rem',
				),
			),
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'tag' => 'div',
			'el_class' => 'hide_if_not_first',
			'css' => array(
				'default' => array(
					'font-size' => '0.8rem',
					'line-height' => '1.6',
					'margin-bottom' => '0',
				),
			),
		),
		'post_custom_field:1' => array(
			'key' => 'custom',
			'custom_key' => '_wp_attachment_image_alt',
			'hide_empty' => 1,
			'el_class' => 'hide_if_not_first',
			'css' => array(
				'default' => array(
					'font-size' => '0.8rem',
					'line-height' => '1.6',
					'margin-bottom' => '0',
				),
			),
		),
		'post_content:1' => array(
			'type' => 'excerpt_only',
			'css' => array(
				'default' => array(
					'font-size' => '0.8rem',
					'line-height' => '1.6',
					'margin-bottom' => '0',
				),
			),
		),
		'post_content:2' => array(
			'type' => 'full_content',
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.8rem',
					'line-height' => '1.6',
					'margin-top' => '0.2rem',
					'margin-bottom' => '0.2rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_content:1',
				'post_custom_field:1',
				'post_title:1',
				'post_content:2',
			),
		),
	),
),

'gallery_with_titles_over' => array(
	'title' => __( 'Image Gallery with titles OVER the image', 'us' ),
	'cols' => '6',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'has_ratio' => 1,
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'background-color' => 'linear-gradient(transparent, rgba(30,30,30,0.8))',
					'position' => 'absolute',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '15%',
					'padding-right' => '7%',
					'padding-bottom' => '6%',
					'padding-left' => '7%',

				),
			),
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'tag' => 'div',
			'el_class' => 'hide_if_not_first',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '0.8rem',
					'line-height' => '1.6',
					'margin-bottom' => '0',
				),
			),
		),
		'post_custom_field:1' => array(
			'key' => 'custom',
			'custom_key' => '_wp_attachment_image_alt',
			'hide_empty' => 1,
			'el_class' => 'hide_if_not_first',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '0.8rem',
					'line-height' => '1.6',
					'margin-bottom' => '0',
				),
			),
		),
		'post_content:1' => array(
			'type' => 'excerpt_only',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '0.8rem',
					'line-height' => '1.6',
					'margin-bottom' => '0',
				),
			),
		),
		'post_content:2' => array(
			'type' => 'full_content',
			'css' => array(
				'default' => array(
					'color' => 'rgba(255,255,255,0.5)',
					'font-size' => '0.8rem',
					'line-height' => '1.6',
					'margin-top' => '0.2rem',
					'margin-bottom' => '0.2rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_content:1',
				'post_custom_field:1',
				'post_title:1',
				'post_content:2',
			),
		),
		'options' => array(
			'overflow' => 1,
		),
	),
),

/* Portfolio =========================================================================== */

'portfolio_1' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 1',
	'group' => __( 'Portfolio Templates', 'us' ),
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'translateY_hover' => '-10%',
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'css' => array(
				'default' => array(
					'background-color' => 'inherit',
					'position' => 'absolute',
					'right' => '0',
					'bottom' => '-1px',
					'left' => '0',
					'padding-top' => '1.2rem',
					'padding-right' => '1.5rem',
					'padding-bottom' => '1.2rem',
					'padding-left' => '1.5rem',
				),
			),
			'el_class' => 'grid_arrow_top',
			'hover' => 1,
			'translateY' => '101%',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'show_count' => 1,
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.4rem',
					'margin-bottom' => '0.3rem',
				),
			),
		),
		'post_custom_field:1' => array(
			'key' => 'us_tile_additional_image',
			'hide_empty' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'translateY' => '100%',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'post_custom_field:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
			'color_bg' => '_content_bg',
			'color_text' => '_content_text',
		),
	),
),

'portfolio_2' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 2',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0.1',
			'scale_hover' => '1.1',
			'transition_duration' => '0.35s',
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'background-color' => 'linear-gradient(transparent, rgba(30,30,30,0.8))',
					'position' => 'absolute',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '4rem',
					'padding-right' => '2rem',
					'padding-bottom' => '1.5rem',
					'padding-left' => '2rem',
				),
			),
			'transition_duration' => '0.35s',
		),
		'post_title:1' => array(
			'show_count' => 1,
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '1.2rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '0.9rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
			'color_bg' => '#333',
		),
	),
),

'portfolio_3' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 3',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity' => '0.25',
			'transition_duration' => '0.4s',
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '2rem',
					'padding-right' => '2rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2rem',
				),
			),
		),
		'post_title:1' => array(
			'show_count' => 1,
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.4rem',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0',
			'translateY_hover' => '-100%',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'hover' => 1,
			'opacity_hover' => '0',
			'translateY_hover' => '100%',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_4' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 4',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0.1',
		),
		'vwrapper:1' => array(
			'valign' => 'bottom',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '2rem',
					'padding-right' => '2rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2rem',
				),
			),
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.4rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'translateY' => '-40px',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'hover' => 1,
			'opacity' => '0',
			'opacity_hover' => '0.75',
			'translateY' => '-20px',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_5' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 5',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'scale_hover' => '1.2',
			'transition_duration' => '0.4s',
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'background-color' => 'inherit',
					'position' => 'absolute',
					'top' => '1.3rem',
					'right' => '1.3rem',
					'bottom' => '1.3rem',
					'left' => '1.3rem',
					'padding-top' => '2rem',
					'padding-right' => '2rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'opacity_hover' => '0.95',
			'scale' => '0',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.4rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
			'color_bg' => '_content_bg',
			'color_text' => '_content_text',
		),
	),
),

'portfolio_6' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 6',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0.1',
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '2rem',
					'padding-right' => '2rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'scale' => '1.5',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.4rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_7' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 7',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0.1',
			'scale' => '1.1',
			'transition_duration' => '0.4s',
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '2.6rem',
					'padding-right' => '2.6rem',
					'padding-bottom' => '2.6rem',
					'padding-left' => '2.6rem',
				),
			),
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.4rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'translateY' => '-50%',
			'transition_duration' => '0.4s',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'hover' => 1,
			'opacity' => '0',
			'translateY' => '50%',
			'transition_duration' => '0.4s',
		),
		'html:1' => array(
			'content' => '',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '1.3rem',
					'right' => '1.3rem',
					'bottom' => '1.3rem',
					'left' => '1.3rem',
					'border-style' => 'solid',
					'border-top-width' => '2px',
					'border-right-width' => '2px',
					'border-bottom-width' => '2px',
					'border-left-width' => '2px',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'scale' => '1.1',
			'transition_duration' => '0.4s',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'html:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_8' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 8',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'width' => '110%',
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0.1',
			'translateX' => is_rtl() ? '8%' : '-8%',
			'transition_duration' => '0.4s',
		),
		'vwrapper:1' => array(
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '2rem',
					'padding-right' => '2rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2rem',
				),
			),
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.4rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'translateX' => '-33%',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'hover' => 1,
			'opacity' => '0',
			'opacity_hover' => '0.75',
			'translateX' => '40%',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_9' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 9',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0',
			'scale_hover' => '4',
			'transition_duration' => '0.4s',
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '2rem',
					'padding-right' => '2rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2rem',
				),
			),
			'hover' => 1,
			'scale' => '0',
			'transition_duration' => '0.5s',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.4rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_10' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 10',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'background-color' => 'linear-gradient(transparent, rgba(30,30,30,0.8))',
					'position' => 'absolute',
					'right' => '0',
					'bottom' => '-1px',
					'left' => '0',
					'padding-top' => '5rem',
					'padding-right' => '2rem',
					'padding-bottom' => '1.5rem',
					'padding-left' => '2rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'transition_duration' => '0.4s',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '1.4rem',
				),
			),
			'hover' => 1,
			'translateY' => '35%',
			'transition_duration' => '0.35s',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '0.9rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'opacity_hover' => '0.75',
			'translateY' => '100%',
			'transition_duration' => '0.35s',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_11' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 11',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0.1',
			'transition_duration' => '0.35s',
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '2rem',
					'padding-right' => '2rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'translateY' => '-25%',
			'transition_duration' => '0.35s',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.4rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
		),
		'html:1' => array(
			'content' => '',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '10px',
					'left' => '0',
				),
			),
			'hover' => 1,
			'translateY' => '100%',
			'transition_duration' => '0.35s',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
				'html:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_12' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 12',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0.1',
			'transition_duration' => '0.25s',
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '4rem',
					'padding-right' => '4rem',
					'padding-bottom' => '4rem',
					'padding-left' => '4rem',
				),
			),
			'el_class' => 'grid_style_12',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.4rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'translateY' => '-50%',
			'transition_duration' => '0.25s',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'hover' => 1,
			'opacity' => '0',
			'opacity_hover' => '0.75',
			'translateY' => '75%',
			'transition_duration' => '0.25s',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_13' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 13',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0.1',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.5rem',
					'margin-bottom' => '1.3rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'translateY' => '20px',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'left' => is_rtl() ? '' : '0',
					'right' => is_rtl() ? '0' : '',
					'bottom' => '0',
					'padding-right' => '2rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'translateY' => '100px',
		),
		'html:1' => array(
			'content' => '',
			'css' => array(
				'default' => array(
					'width' => '100%',
					'border-style' => 'solid',
					'border-top-width' => '3px',
					'border-bottom-width' => '0',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'translateY' => '60px',
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'left' => '0',
					'padding-top' => '2rem',
					'padding-right' => '2rem',
					'padding-left' => '2rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
				'post_taxonomy:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'html:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_14' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 14',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0.1',
			'scale' => '1.15',
			'transition_duration' => '0.35s',
			'transform_origin_X' => '100%',
			'transform_origin_Y' => '100%',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.5rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'translateX' => '-2rem',
			'transition_duration' => '0.35s',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'hover' => 1,
			'opacity' => '0',
			'translateX' => '-1rem',
			'transition_duration' => '0.35s',
		),
		'vwrapper:1' => array(
			'valign' => 'bottom',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '10%',
					'padding-right' => '10%',
					'padding-bottom' => '10%',
					'padding-left' => '10%',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_taxonomy:1',
				'post_title:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_15' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 15',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0',
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '2rem',
					'padding-right' => '2rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2rem',
				),
			),
			'el_class' => 'grid_style_15',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.5rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'translateY' => '20px',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'hover' => 1,
			'opacity' => '0',
			'translateX' => '20px',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_16' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 16',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'circle' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'el_class' => 'grid_corner_image',
			'hover' => 1,
			'scale' => '0.3',
			'scale_hover' => '1',
			'transform_origin_X' => '90%',
			'transform_origin_Y' => '90%',
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'left' => '0',
					'padding-top' => '10%',
					'padding-right' => '30%',
					'padding-left' => '10%',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0',
			'scale_hover' => '2',
			'translateX_hover' => '-50%',
			'translateY_hover' => '-50%',
			'transition_duration' => '0.4s',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1.5rem',
					'font-weight' => '700',
				),
			),
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
			'color_bg' => '_content_bg_alt',
			'color_text' => '_content_heading',
		),
	),
),

'portfolio_17' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 17',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity_hover' => '0.1',
			'scale_hover' => '1.3',
			'transition_duration' => '0.8s',
			'transform_origin_X' => '100%',
			'transform_origin_Y' => '100%',
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '10%',
					'padding-right' => '10%',
					'padding-bottom' => '10%',
					'padding-left' => '10%',
				),
			),
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '2rem',
					'margin-bottom' => '1rem',
				),
			),
			'hover' => 1,
			'scale' => '0.5',
			'transition_duration' => '0.4s',
			'transform_origin_X' => is_rtl() ? '100%' : '0%',
			'transform_origin_Y' => '0%',
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'line_height' => '1.2',
			'hover' => 1,
			'opacity' => '0',
			'scale' => '0.5',
			'translateY' => '-150px',
			'transition_duration' => '0.4s',
			'transform_origin_X' => '0%',
			'transform_origin_Y' => '0%',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_18' => array(
	'title' => __( 'Portfolio', 'us' ) . ' 18',
	'items_gap' => '0',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
		),
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'background-color' => 'linear-gradient(transparent, rgba(30,30,30,0.8))',
					'position' => 'absolute',
					'right' => '0',
					'bottom' => '-1px',
					'left' => '0',
					'padding-top' => '5rem',
					'padding-right' => '2rem',
					'padding-bottom' => '1.5rem',
					'padding-left' => '2rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'transition_duration' => '1s',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '1.4rem',
				),
			),
		),
		'post_taxonomy:1' => array(
			'taxonomy_name' => 'us_portfolio_category',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '0.9rem',
				),
			),
		),
		'post_custom_field:1' => array(
			'key' => 'us_tile_additional_image',
			'hide_empty' => 1,
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity' => '0',
			'transition_duration' => '1s',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'post_custom_field:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'post_taxonomy:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

'portfolio_compact' => array(
	'title' => __( 'Portfolio', 'us' ) . ' ' . __( 'Compact', 'us' ),
	'cols' => '10',
	'items_gap' => '0.5px',
	'data' => array(
		'post_image:1' => array(
			'link' => '{"url":""}',
			'placeholder' => 1,
			'thumbnail_size' => 'thumbnail',
			'css' => array(
				'default' => array(
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'background-color' => 'rgba(0,0,0,0.8)',
					'position' => 'absolute',
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'padding-top' => '0.5rem',
					'padding-left' => '0.5rem',
					'padding-right' => '0.5rem',
					'padding-bottom' => '0.5rem',
				),
			),
			'hover' => 1,
			'opacity' => '0',
		),
		'post_title:1' => array(
			'link' => '{"url":""}',
			'tag' => 'div',
			'hide_empty' => 1,
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '11px',
					'line-height' => '1.4',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
			),
		),
		'options' => array(
			'fixed' => 1,
		),
	),
),

/* Testimonial =========================================================================== */

'testimonial_1' => array(
	'title' => __( 'Testimonial', 'us' ) . ' 1',
	'group' => __( 'Testimonial Templates', 'us' ),
	'data' => array(
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'border-style' => 'solid',
					'border-top-width' => '2px',
					'border-right-width' => '2px',
					'border-bottom-width' => '2px',
					'border-left-width' => '2px',
					'border-color' => '_content_border',
					'border-radius' => us_get_option( 'rounded_corners' ) ? '0.3rem' : '',
					'padding-top' => '2rem',
					'padding-right' => '2rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2rem',
				),
			),
			'hover' => 1,
			'color_border_hover' => '_content_primary',
		),
		'post_content:1' => array(
			'type' => 'full_content',
		),
		'hwrapper:1' => array(
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'margin-bottom' => '1rem',
				),
			),
		),
		'post_image:1' => array(
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'circle' => 1,
			'thumbnail_size' => 'thumbnail',
			'css' => array(
				'default' => array(
					'width' => '4rem',
					'margin-left' => is_rtl() ? '1rem' : '0',
					'margin-right' => is_rtl() ? '0' : '1rem',
				),
			),
		),
		'vwrapper:2' => array(
		),
		'post_custom_field:1' => array(
			'key' => 'us_testimonial_author',
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'line-height' => '1.5',
					'margin-bottom' => '0',
				),
			),
		),
		'post_custom_field:2' => array(
			'key' => 'us_testimonial_role',
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
					'line-height' => '1.5',
				),
			),
		),
		'post_custom_field:3' => array(
			'key' => 'us_testimonial_rating',
			'css' => array(
				'default' => array(
					'color' => '_content_primary',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_custom_field:3',
				'post_content:1',
				'hwrapper:1',
			),
			'hwrapper:1' => array(
				'post_image:1',
				'vwrapper:2',
			),
			'vwrapper:2' => array(
				'post_custom_field:1',
				'post_custom_field:2',
			),
		),
	),
),

'testimonial_2' => array(
	'title' => __( 'Testimonial', 'us' ) . ' 2',
	'data' => array(
		'post_content:1' => array(
			'type' => 'full_content',
		),
		'hwrapper:1' => array(
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'margin-top' => '0.5rem',
				),
			),
		),
		'post_image:1' => array(
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'circle' => 1,
			'thumbnail_size' => 'thumbnail',
			'css' => array(
				'default' => array(
					'width' => '4rem',
					'margin-left' => is_rtl() ? '1rem' : '',
					'margin-right' => is_rtl() ? '' : '1rem',
				),
			),
		),
		'vwrapper:1' => array(
		),
		'post_custom_field:1' => array(
			'key' => 'us_testimonial_author',
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'line-height' => '1.5',
					'margin-bottom' => '0',
				),
			),
		),
		'post_custom_field:2' => array(
			'key' => 'us_testimonial_role',
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
					'line-height' => '1.5',
					'margin-bottom' => '0',
				),
			),
		),
		'vwrapper:2' => array(
			'css' => array(
				'default' => array(
					'padding-top' => '3.5rem',
					'padding-left' => is_rtl() ? '' : '2rem',
					'padding-right' => is_rtl() ? '2rem' : '',
				),
			),
		),
		'post_custom_field:3' => array(
			'key' => 'custom',
			'icon' => $icon_quote,
			'css' => array(
				'default' => array(
					'color' => '_content_primary',
					'font-size' => '3rem',
					'line-height' => '1',
					'position' => 'absolute',
					'top' => '0',
					'left' => is_rtl() ? '' : '0',
					'right' => is_rtl() ? '0' : '',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'vwrapper:2',
			),
			'hwrapper:1' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_custom_field:1',
				'post_custom_field:2',
			),
			'vwrapper:2' => array(
				'post_custom_field:3',
				'post_content:1',
				'hwrapper:1',
			),
		),
	),
),

'testimonial_3' => array(
	'title' => __( 'Testimonial', 'us' ) . ' 3',
	'data' => array(
		'post_content:1' => array(
			'type' => 'full_content',
		),
		'hwrapper:1' => array(
			'valign' => 'middle',
		),
		'post_image:1' => array(
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'circle' => 1,
			'thumbnail_size' => 'thumbnail',
			'css' => array(
				'default' => array(
					'width' => '4rem',
					'margin-left' => is_rtl() ? '1rem' : '',
					'margin-right' => is_rtl() ? '' : '1rem',
				),
			),
		),
		'vwrapper:1' => array(
		),
		'post_custom_field:1' => array(
			'key' => 'us_testimonial_author',
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'line-height' => '1.5',
					'margin-bottom' => '0',
				),
			),
		),
		'post_custom_field:2' => array(
			'key' => 'us_testimonial_role',
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
					'line-height' => '1.5',
				),
			),
		),
		'vwrapper:2' => array(
			'css' => array(
				'default' => array(
					'padding-left' => is_rtl() ? '' : '2rem',
					'padding-right' => is_rtl() ? '2rem' : '',
				),
			),
		),
		'post_custom_field:3' => array(
			'key' => 'custom',
			'icon' => $icon_quote,
			'css' => array(
				'default' => array(
					'font-size' => '1.4rem',
					'line-height' => '1',
					'position' => 'absolute',
					'top' => '0',
					'left' => is_rtl() ? '' : '0',
					'right' => is_rtl() ? '0' : '',
				),
			),
			'hover' => 1,
			'opacity' => '0.2',
			'opacity_hover' => '0.2',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'vwrapper:2',
			),
			'hwrapper:1' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_custom_field:1',
				'post_custom_field:2',
			),
			'vwrapper:2' => array(
				'post_custom_field:3',
				'post_content:1',
				'hwrapper:1',
			),
		),
	),
),

'testimonial_4' => array(
	'title' => __( 'Testimonial', 'us' ) . ' 4',
	'data' => array(
		'post_content:1' => array(
			'type' => 'full_content',
		),
		'hwrapper:1' => array(
		),
		'post_image:1' => array(
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'placeholder' => 1,
			'circle' => 1,
			'thumbnail_size' => 'thumbnail',
			'css' => array(
				'default' => array(
					'width' => '5.5rem',
				),
			),
			'el_class' => 'with_quote_icon',
		),
		'vwrapper:1' => array(
		),
		'post_custom_field:1' => array(
			'key' => 'us_testimonial_author',
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'line-height' => '1.5',
					'margin-bottom' => '0',
				),
			),
		),
		'post_custom_field:2' => array(
			'key' => 'us_testimonial_role',
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
					'line-height' => '1.5',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'hwrapper:1',
			),
			'hwrapper:1' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_content:1',
				'post_custom_field:1',
				'post_custom_field:2',
			),
		),
	),
),

'testimonial_5' => array(
	'title' => __( 'Testimonial', 'us' ) . ' 5',
	'data' => array(
		'post_content:1' => array(
			'type' => 'full_content',
		),
		'post_image:1' => array(
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'circle' => 1,
			'thumbnail_size' => 'thumbnail',
			'css' => array(
				'default' => array(
					'width' => '7rem',
				),
			),
		),
		'post_custom_field:1' => array(
			'key' => 'us_testimonial_author',
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'line-height' => '1.5',
					'margin-bottom' => '0',
				),
			),
		),
		'post_custom_field:2' => array(
			'key' => 'us_testimonial_role',
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
					'line-height' => '1.5',
				),
			),
		),
		'vwrapper:2' => array(
			'alignment' => 'center',
		),
		'post_custom_field:3' => array(
			'key' => 'us_testimonial_rating',
			'css' => array(
				'default' => array(
					'color' => '_content_primary',
					'font-size' => '1.2rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'vwrapper:2',
			),
			'vwrapper:2' => array(
				'post_custom_field:3',
				'post_content:1',
				'post_image:1',
				'post_custom_field:1',
				'post_custom_field:2',
			),
		),
	),
),

'testimonial_6' => array(
	'title' => __( 'Testimonial', 'us' ) . ' 6',
	'data' => array(
		'post_content:1' => array(
			'type' => 'full_content',
		),
		'hwrapper:1' => array(
			'valign' => 'middle',
			'css' => array(
				'default' => array(
					'padding-top' => '1.5rem',
					'padding-right' => '2.5rem',
					'padding-left' => '2.5rem',
				),
			),
		),
		'post_image:1' => array(
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'circle' => 1,
			'thumbnail_size' => 'thumbnail',
			'css' => array(
				'default' => array(
					'width' => '4rem',
					'margin-left' => is_rtl() ? '1rem' : '0',
					'margin-right' => is_rtl() ? '0' : '1rem',
				),
			),
		),
		'vwrapper:1' => array(
		),
		'post_custom_field:1' => array(
			'key' => 'us_testimonial_author',
			'link' => '{"type":"custom_field","custom_field":"us_testimonial_link"}',
			'color_link' => 0,
			'css' => array(
				'default' => array(
					'font-weight' => '700',
					'line-height' => '1.5',
					'margin-bottom' => '0',
				),
			),
		),
		'post_custom_field:2' => array(
		'key' => 'us_testimonial_role',
			'css' => array(
				'default' => array(
					'color' => '_content_faded',
					'font-size' => '0.9rem',
					'line-height' => '1.5',
				),
			),
		),
		'vwrapper:2' => array(
			'css' => array(
				'default' => array(
					'background-color' => '_content_bg_alt',
					'color' => '_content_text',
					'padding-top' => '2rem',
					'padding-right' => '2.5rem',
					'padding-bottom' => '2rem',
					'padding-left' => '2.5rem',
					'border-radius' => us_get_option( 'rounded_corners' ) ? '0.3rem' : '',
				),
			),
			'el_class' => 'grid_arrow_bottom',
		),
		'post_custom_field:3' => array(
			'key' => 'us_testimonial_rating',
			'css' => array(
				'default' => array(
					'color' => '#fb0',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'vwrapper:2',
				'hwrapper:1',
			),
			'hwrapper:1' => array(
				'post_image:1',
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_custom_field:1',
				'post_custom_field:2',
			),
			'vwrapper:2' => array(
				'post_custom_field:3',
				'post_content:1',
			),
		),
	),
),

'testimonial_7' => array(
	'title' => __( 'Testimonial', 'us' ) . ' 7',
	'data' => array(
		'post_custom_field:1' => array(
			'key' => 'us_testimonial_author',
			'tag' => 'h6',
			'css' => array(
				'default' => array(
					'padding-top' => '1rem',
				),
			),
		),
		'post_content:1' => array(
			'type' => 'full_content',
		),
		'post_custom_field:2' => array(
			'key' => 'us_testimonial_rating',
			'css' => array(
				'default' => array(
					'color' => '_content_link',
					'margin-bottom' => '1rem',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				0 => 'post_custom_field:2',
				1 => 'post_content:1',
				2 => 'post_custom_field:1',
			),
		),
	),
),

'testimonial_8' => array(
	'title' => __( 'Testimonial', 'us' ) . ' 8',
	'data' => array(
		'vwrapper:1' => array(
			'inner_items_gap' => '1.5rem',
			'css' => array(
				'default' => array(
					'color' => '_content_text',
					'background-color' => '_content_bg_alt',
					'padding-left' => '10%',
					'padding-top' => '10%',
					'padding-bottom' => '10%',
					'padding-right' => '10%',
					'border-radius' => us_get_option( 'rounded_corners' ) ? '0.3rem' : '',
				),
			),
		),
		'post_content:1' => array(
			'type' => 'full_content',
		),
		'post_custom_field:1' => array(
			'key' => 'us_testimonial_role',
			'css' => array(
				'default' => array(
					'font-size' => '14px',
				),
			),
		),
		'post_custom_field:2' => array(
			'key' => 'us_testimonial_author',
			'css' => array(
				'default' => array(
					'color' => '_content_heading',
					'font-size' => '1.2rem',
					'line-height' => '1.5',
				),
			),
		),
		'vwrapper:2' => array(
			'inner_items_gap' => '0rem',
			'css' => array(
				'default' => array(
					'margin-top' => 'auto',
				),
			),
		),
		'post_custom_field:3' => array(
			'key' => 'us_testimonial_rating',
			'css' => array(
				'default' => array(
					'color' => '_content_primary',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				0 => 'vwrapper:1',
			),
			'vwrapper:1' => array(
				0 => 'post_custom_field:3',
				1 => 'post_content:1',
				2 => 'vwrapper:2',
			),
			'vwrapper:2' => array(
				0 => 'post_custom_field:2',
				1 => 'post_custom_field:1',
			),
		),
	),
),

/* Shop =========================================================================== */

'shop_standard' => array(
	'title' => 'Shop Standard',
	'group' => __( 'Shop Templates', 'us' ),
	'cols' => '4',
	'items_gap' => '1rem',
	'data' => array(
		'post_image:1' => array(
			'placeholder' => 1,
			'thumbnail_size' => 'woocommerce_thumbnail',
		),
		'product_field:1' => array(
			'type' => 'sale_badge',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '12px',
					'font-weight' => '700',
					'text-transform' => 'uppercase',
					'background-color' => '_content_primary',
					'position' => 'absolute',
					'top' => '10px',
					'left' => is_rtl() ? '' : '10px',
					'right' => is_rtl() ? '10px' : '',
					'padding-left' => '0.8rem',
					'padding-right' => '0.8rem',
					'border-radius' => '2rem',
				),
			),
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '1rem',
					'margin-top' => '0.8rem',
					'margin-bottom' => '0.2rem',
				),
			),
		),
		'product_field:2' => array(
			'type' => 'rating',
			'css' => array(
				'default' => array(
					'margin-bottom' => '0.2rem',
				),
			),
		),
		'product_field:3' => array(
			'css' => array(
				'default' => array(
					'font-weight' => '700',
				),
			),
		),
		'add_to_cart:1' => array(
			'view_cart_link' => 1,
			'css' => array(
				'default' => array(
					'font-size' => '0.8rem',
					'margin-top' => '0.4rem',
					'border-radius' => us_get_option( 'rounded_corners' ) ? '0.2rem' : '',
				),
			),
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'product_field:1',
				'post_title:1',
				'product_field:2',
				'product_field:3',
				'add_to_cart:1',
			),
		),
	),
),

'shop_modern' => array(
	'title' => 'Shop Modern',
	'cols' => '4',
	'items_gap' => '5px',
	'data' => array(
		'post_image:1' => array(
			'placeholder' => 1,
			'thumbnail_size' => 'woocommerce_thumbnail',
		),
		'product_field:1' => array(
			'type' => 'sale_badge',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '12px',
					'font-weight' => '700',
					'text-transform' => 'uppercase',
					'background-color' => '_content_primary',
					'position' => 'absolute',
					'top' => '10px',
					'left' => is_rtl() ? '' : '10px',
					'right' => is_rtl() ? '10px' : '',
					'padding-right' => '0.8rem',
					'padding-left' => '0.8rem',
					'border-radius' => '2rem',
				),
			),
		),
		'vwrapper:1' => array(
			'alignment' => 'center',
			'css' => array(
				'default' => array(
					'background-color' => 'inherit',
					'padding-top' => '1rem',
					'padding-right' => '1.2rem',
					'padding-bottom' => '1rem',
					'padding-left' => '1.2rem',
				),
			),
			'hover' => 1,
			'translateY_hover' => '-2.4rem',
			'transition_duration' => '0.2s',
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1rem',
					'margin-bottom' => '0.3rem',
				),
			),
		),
		'product_field:2' => array(
			'type' => 'rating',
			'css' => array(
				'default' => array(
					'margin-bottom' => '0.3rem',
				),
			),
		),
		'product_field:3' => array(
			'css' => array(
				'default' => array(
					'font-weight' => '700',
				),
			),
		),
		'add_to_cart:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '0.8rem',
					'width' => '100%',
					'border-radius' => '0',
					'position' => 'absolute',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				),
			),
			'hover' => 1,
			'opacity' => '0',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'post_image:1',
				'product_field:1',
				'vwrapper:1',
				'add_to_cart:1',
			),
			'vwrapper:1' => array(
				'post_title:1',
				'product_field:2',
				'product_field:3',
			),
		),
		'options' => array(
			'overflow' => 1,
			'color_bg' => '_content_bg',
			'color_text' => '_content_text',
			'border_radius' => us_get_option( 'rounded_corners' ) ? '0.3rem' : '',
			'box_shadow' => '0.3rem',
			'box_shadow_hover' => '1rem',
		),
	),
),

'shop_trendy' => array(
	'title' => 'Shop Trendy',
	'cols' => '4',
	'items_gap' => '0',
	'data' => array(
		'vwrapper:1' => array(
			'css' => array(
				'default' => array(
					'padding-right' => '10px',
					'padding-top' => '10px',
					'padding-left' => '10px',
				),
			),
		),
		'post_image:1' => array(
			'placeholder' => 1,
			'media_preview' => 1,
			'thumbnail_size' => 'woocommerce_thumbnail',
		),
		'product_field:1' => array(
			'type' => 'sale_badge',
			'css' => array(
				'default' => array(
					'color' => '#fff',
					'font-size' => '12px',
					'font-weight' => '700',
					'text-transform' => 'uppercase',
					'background-color' => '_content_primary',
					'position' => 'absolute',
					'top' => '10px',
					'left' => is_rtl() ? '' : '10px',
					'right' => is_rtl() ? '10px' : '',
					'padding-right' => '0.8rem',
					'padding-left' => '0.8rem',
				),
			),
		),
		'post_title:1' => array(
			'css' => array(
				'default' => array(
					'color' => 'inherit',
					'font-size' => '1rem',
					'margin-bottom' => '0.4rem',
				),
			),
		),
		'product_field:2' => array(
			'type' => 'rating',
			'css' => array(
				'default' => array(
					'margin-bottom' => '0.2rem',
				),
			),
		),
		'product_field:3' => array(
			'css' => array(
				'default' => array(
					'font-weight' => '700',
				),
			),
		),
		'add_to_cart:1' => array(
			'css' => array(
				'default' => array(
					'font-size' => '15px',
					'width' => '100%',
					'border-radius' => '0',
					'position' => 'absolute',
					'top' => '100%',
					'right' => '0',
					'left' => '0',
				),
			),
			'hide_below' => '600px',
			'hover' => 1,
			'opacity' => '0',
		),
	),
	'default' => array(
		'layout' => array(
			'middle_center' => array(
				'vwrapper:1',
			),
			'vwrapper:1' => array(
				'post_image:1',
				'product_field:1',
				'post_title:1',
				'product_field:2',
				'product_field:3',
				'add_to_cart:1',
			),
		),
		'options' => array(
			'color_bg' => '_content_bg',
			'color_text' => '_content_text',
			'box_shadow_hover' => '1rem',
		),
	),
),

'shop_single_row' => array(
	'title' => 'Single Row',
	'cols' => '1',
	'items_gap' => '0',
	'data' => array(
	'hwrapper:1' => array(
		'inner_items_gap' => '0rem',
		'wrap' => 1,
		'css' => array(
		'default' => array(
			'padding-left' => '1.5rem',
			'padding-top' => '1.5rem',
			'padding-bottom' => '1.5rem',
			'padding-right' => '1.5rem',
			'border-style' => 'solid',
			'border-left-width' => '1px',
			'border-top-width' => '0',
			'border-bottom-width' => '1px',
			'border-right-width' => '1px',
			'border-color' => '_content_border',
		),
		),
	),
	'post_image:1' => array(
		'media_preview' => 1,
		'thumbnail_size' => 'woocommerce_thumbnail',
		'css' => array(
		'default' => array(
			'width' => '20%',
		),
		'mobiles' => array(
			'width' => '100%',
		),
		),
	),
	'vwrapper:1' => array(
		'css' => array(
		'default' => array(
			'width' => '55%',
			'padding-left' => '3%',
			'padding-right' => '3%',
		),
		'laptops' => array(
			'width' => '55%',
			'padding-left' => '3%',
			'padding-right' => '3%',
		),
		'tablets' => array(
			'width' => '55%',
			'padding-left' => '3%',
			'padding-right' => '3%',
		),
		'mobiles' => array(
			'width' => '100%',
			'padding-left' => '0',
			'padding-top' => '1rem',
			'padding-bottom' => '1rem',
			'padding-right' => '0',
		),
		),
	),
	'post_title:1' => array(
		'css' => array(
		'default' => array(
			'font-size' => '1.5rem',
			'line-height' => '1.2',
		),
		),
	),
	'post_content:1' => array(
		'type' => 'excerpt_only',
		'excerpt_length' => '20',
		'css' => array(
		'default' => array(
			'font-size' => '0.9rem',
		),
		),
	),
	'product_field:1' => array(
		'type' => 'rating',
	),
	'vwrapper:2' => array(
		'css' => array(
		'default' => array(
			'width' => '25%',
		),
		'mobiles' => array(
			'width' => '100%',
		),
		),
	),
	'product_field:2' => array(
		'css' => array(
		'default' => array(
			'font-weight' => '700',
			'font-size' => '1.5rem',
		),
		),
	),
	'add_to_cart:1' => array(
		'view_cart_link' => 1,
		'css' => array(
		'default' => array(
			'font-size' => '0.85rem',
		),
		),
	),
	'product_field:3' => array(
		'type' => 'sku',
		'css' => array(
		'default' => array(
			'font-size' => '0.85rem',
		),
		),
	),
	'product_field:4' => array(
		'type' => 'attributes',
		'css' => array(
		'default' => array(
			'font-size' => '0.85rem',
		),
		),
	),
	'product_field:5' => array(
		'type' => 'stock',
		'out_of_stock_only' => 1,
		'css' => array(
		'default' => array(
			'font-weight' => '700',
		),
		),
	),
	'product_field:6' => array(
		'type' => 'sale_badge',
		'css' => array(
		'default' => array(
			'color' => '#ffffff',
			'text-transform' => 'uppercase',
			'font-size' => '12px',
			'background-color' => '_content_primary',
			'padding-left' => '10px',
			'padding-right' => '10px',
			'position' => 'absolute',
			'z-index' => '1',
		),
		),
	),
	),
	'default' => array(
	'layout' => array(
		'middle_center' => array(
		0 => 'hwrapper:1',
		),
		'hwrapper:1' => array(
		0 => 'product_field:6',
		1 => 'post_image:1',
		2 => 'vwrapper:1',
		3 => 'vwrapper:2',
		),
		'vwrapper:1' => array(
		0 => 'post_title:1',
		1 => 'product_field:1',
		2 => 'post_content:1',
		3 => 'product_field:3',
		4 => 'product_field:4',
		),
		'vwrapper:2' => array(
		0 => 'product_field:2',
		1 => 'product_field:5',
		2 => 'add_to_cart:1',
		),
	),
	'options' => array(
		'box_shadow_hover' => '1rem',
	),
	),
),

/* User =========================================================================== */

'user_1' => [
	'title' => us_translate( 'User' ) . ' 1',
	'group' => __( 'User Templates', 'us' ),
	'cols' => '3',
	'items_gap' => '10px',
	"data" => [
		"user_data:1" => [
			"link" => "%7B%22type%22%3A%22author_page%22%7D",
			"color_link" => 1,
			"conditions" => [],
			"css" => [
				"default" => [
					"text-transform" => "capitalize",
				],
			],
		],
		"user_data:2" => [
			"type" => "role",
			"link" => "%7B%22url%22%3A%22%22%7D",
			"css" => [
				"default" => [
					"color" => "_content_faded",
					"font-size" => "13px",
					"margin-top" => "2px",
				],
			],
		],
		"user_picture:1" => [
			"width" => "32px",
			"circle" => 1,
			"link" => "%7B%22url%22%3A%22%22%7D",
			"css" => ["default" => ["background-color" => "_content_bg_alt"]],
		],
		"hwrapper:1" => [
			"valign" => "middle",
			"inner_items_gap" => "0.8rem",
			"wrap" => 1,
		],
	],
	"default" => [
		"layout" => [
			"middle_center" => ["hwrapper:1"],
			"hwrapper:1" => ["user_picture:1", "user_data:1", "user_data:2"],
		],
	],
],

'user_2' => [
	'title' => us_translate( 'User' ) . ' 2',
	'cols' => '3',
	'items_gap' => '1rem',
	"data" => [
		"user_data:1" => [
			"link" => "%7B%22type%22%3A%22author_page%22%7D",
			"tag" => "h2",
			"css" => [
				"default" => [
					"font-size" => "1.2rem",
					"text-transform" => "capitalize",
				],
			],
		],
		"user_data:2" => [
			"type" => "role",
			"link" => "%7B%22url%22%3A%22%22%7D",
			"css" => [
				"default" => [
					"color" => "_content_faded",
					"font-size" => "11px",
					"line-height" => "20px",
					"font-weight" => "400",
					"text-transform" => "uppercase",
					"border-color" => "_content_border",
					"border-style" => "solid",
					"border-top-width" => "1px",
					"border-left-width" => "1px",
					"border-right-width" => "1px",
					"border-bottom-width" => "1px",
					"padding-left" => "8px",
					"padding-right" => "8px",
					"border-radius" => "20px",
				],
			],
		],
		"user_picture:1" => [
			"default_avatar" => "robohash",
			"width" => "64px",
			"circle" => 1,
			"link" => "%7B%22url%22%3A%22%22%7D",
			"css" => ["default" => ["background-color" => "_content_border"]],
		],
		"hwrapper:1" => [
			"valign" => "middle",
			"stack_on_mobiles" => 1,
		],
		"vwrapper:1" => [
			'alignment' => is_rtl() ? 'right' : 'left',
			"inner_items_gap" => "10px",
		],
	],
	"default" => [
		"layout" => [
			"middle_center" => ["hwrapper:1"],
			"hwrapper:1" => ["user_picture:1", "vwrapper:1"],
			"vwrapper:1" => ["user_data:1", "user_data:2"],
		],
	],
],

'user_3' => array(
	'title' => us_translate( 'User' ) . ' 3',
	'cols' => '3',
	'items_gap' => '1.5rem',
	'data' => array(
	'vwrapper:1' => array(
		'inner_items_gap' => '8px',
	),
	'user_data:1' => array(
		'link' => '%7B%22url%22%3A%22%22%7D',
		'tag' => 'h2',
		'css' => array(
		'default' => array(
			'font-size' => '1.4rem',
			'text-transform' => 'capitalize',
		),
		),
	),
	'user_data:3' => array(
		'type' => 'user_registered',
		'link' => '%7B%22url%22%3A%22%22%7D',
		'text_before' => 'Registered: ',
		'css' => array(
		'default' => array(
			'font-size' => '13px',
			'line-height' => '20px',
		),
		),
	),
	'user_data:4' => array(
		'type' => 'post_count',
		'link' => '%7B%22url%22%3A%22%22%7D',
		'text_before' => 'Published Posts: ',
		'css' => array(
		'default' => array(
			'font-size' => '13px',
			'line-height' => '20px',
		),
		),
	),
	'user_data:2' => array(
		'type' => 'role',
		'link' => '%7B%22url%22%3A%22%22%7D',
		'text_before' => 'Role: ',
		'css' => array(
		'default' => array(
			'font-size' => '13px',
			'line-height' => '20px',
		),
		),
	),
	),
	'default' => array(
	'layout' => array(
		'middle_center' => array(
		'vwrapper:1',
		),
		'vwrapper:1' => array(
			'user_data:1',
			'user_data:2',
			'user_data:3',
			'user_data:4',
		),
		'hidden' => array(
		),
	),
	),
),

'user_4' => array(
	'title' => us_translate( 'User' ) . ' 4',
	'cols' => '4',
	'items_gap' => '10px',
	'data' => array(
	'user_data:1' => array(
		'link' => '%7B%22type%22%3A%22author_page%22%7D',
		'tag' => 'h2',
		'conditions' => array(
		),
		'css' => array(
		'default' => array(
			'font-size' => '1.4rem',
			'font-weight' => '600',
			'text-transform' => 'capitalize',
			'padding-top' => '10px',
		),
		),
	),
	'vwrapper:1' => array(
		'alignment' => is_rtl() ? 'right' : 'left',
		'inner_items_gap' => '10px',
		'css' => array(
		'default' => array(
			'color' => '_content_text',
			'font-size' => '0.85em',
			'line-height' => '1.7',
			'background-color' => '_content_bg',
			'padding-left' => '12%',
			'padding-top' => '12%',
			'padding-bottom' => '12%',
			'padding-right' => '12%',
			'border-radius' => '10px',
			'box-shadow-v-offset' => '0.5em',
			'box-shadow-blur' => '1rem',
			'box-shadow-color' => 'rgba(0,0,0,0.10)',
		),
		),
	),
    'btn:1' =>    array (
      'label' => 'Author Website',
      'link' => '%7B%22type%22%3A%22author_website%22%7D',
      'hide_with_empty_link' => 1,
      'css' =>      array (
        'default' =>        array (
          'font-size' => '12px',
          'margin-top' => '10px',
        ),
      ),
    ),
	'user_data:3' => array(
		'type' => 'description',
		'link' => '%7B%22url%22%3A%22%22%7D',
		'conditions' => array(
		),
	),
	'user_data:2' => array(
		'type' => 'post_count',
		'link' => '%7B%22url%22%3A%22%22%7D',
		'text_before' => 'Published Posts: ',
		'conditions' => array(
		),
		'css' => array(
		'default' => array(
			'font-weight' => '600',
		),
		),
	),
	'user_picture:1' => array(
		'default_avatar' => 'retro',
		'width' => '96px',
		'link' => '%7B%22type%22%3A%22author_page%22%7D',
		'conditions' => array(
		),
		'css' => array(
		'default' => array(
			'border-radius' => '5px',
		),
		),
	),
	),
	'default' => array(
	'layout' => array(
		'middle_center' => array(
		0 => 'vwrapper:1',
		),
		'vwrapper:1' => array(
		0 => 'user_picture:1',
		1 => 'user_data:1',
		2 => 'user_data:2',
		3 => 'user_data:3',
		4 => 'btn:1',
		),
		'hidden' => array(
		),
	),
	),
),

);
