<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );

// Get title
$title = isset( $title )
	? $title
	: '(no title)'; // default

// Get URL
$url = isset( $url )
	? $url
	: 'http://templates.us-themes.com/'; // default

// Markup output
$output = '';
if ( ! empty( $template_category_id ) ) {
	$output .= '<div class="usb-template" data-template-category-id="' . esc_attr( $template_category_id ) . '">';
	$output .= '<div class="usb-template-title">' . strip_tags( $title ) . '</div>';

	if ( ! empty( $templates ) ) {
		$output .= '<div class="usb-template-list">';

		$section_num = 1;

		foreach ( $templates as $template_id => $template_img_src ) {

			$item_atts = array(
				'class' => 'usb-template-item',
			);

			// Check if the license is activated or US_DEV_SECRET or US_THEMETEST_CODE is set
			if (
				defined( 'US_DEV_SECRET' )
				OR defined( 'US_THEMETEST_CODE' )
				OR get_option( 'us_license_activated' )
				OR get_option( 'us_license_dev_activated' )
			) {
				$item_atts['data-template-id'] = $template_id;
				$item_atts['data-type'] = 'import_template';

				// Preview button
				$preview_btn_atts = array(
					'class' => 'usb-template-view ui-icon_eye',
					'href' => $url . '#section-' . sprintf( '%02d', $section_num ),
					'target' => '_blank',
					'title' => us_translate( 'Preview' ),
				);
				$preview_html = '<a ' . us_implode_atts( $preview_btn_atts ) . '></a>';

				// ...if not, output the screenlock for every template
			} else {
				$preview_html = '<div class="usb-template-activation-screenlock">';
				$preview_html .= sprintf(
					__( '<a href="%s" target="_blank">Activate the theme</a> to unlock templates', 'us' ),
					admin_url( 'admin.php?page=us-home#activation' )
				);
				$preview_html .= '</div>';
			}

			// Set placeholder if the image is empty
			if ( ! empty( $template_img_src ) AND is_array( $template_img_src ) ) {
				$img_atts = array(
					'src' => $template_img_src[0],
					'width' => $template_img_src[1],
					'height' => $template_img_src[2],
					'loading' => 'lazy',
					'decoding' => 'async',
					'alt' => '',
				);
				$img_html = '<img' . us_implode_atts( $img_atts ) . '>';
			} else {
				$img_html = us_get_img_placeholder();
			}

			// Output the item
			$output .= '<div ' . us_implode_atts( $item_atts ) . '>';
			$output .= $img_html;
			$output .= '<span>' . strip_tags( $template_category_id . $template_id ) . '</span>';
			$output .= $preview_html;
			$output .= '</div>'; // .usb-template-item

			$section_num++;
		}

		$output .= '</div>'; // .usb-template-list
	}

	$output .= '</div>'; // .usb-template
}

echo $output;
