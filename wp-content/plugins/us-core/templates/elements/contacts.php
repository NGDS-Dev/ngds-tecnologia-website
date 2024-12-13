<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_contacts
 *
 * Dev note: if you want to change some of the default values or acceptable attributes, overload the shortcodes config.
 *
 * @var   $shortcode      string Current shortcode name
 * @var   $shortcode_base string The original called shortcode name (differs if called an alias)
 * @var   $content        string Shortcode's inner content
 * @var   $classes        string Extend class names
 *
 * @param  $address		 string Addresss
 * @param  $phone		 string Phone
 * @param  $fax			 string Mobiles
 * @param  $email		 string Email
 * @param  $el_class	 string Extra class name
 */

$_atts['class'] = 'w-contacts';
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Output the element
$output = '';
if ( ! empty( $address ) OR usb_is_post_preview() ) {
	$is_hidden = ( empty( $address ) AND usb_is_post_preview() )
		? ' hidden'
		: '';

	$address = us_replace_dynamic_value( $address );
	$output .= '<div class="w-contacts-item for_address' . $is_hidden . '">';
	$output .= '<span class="w-contacts-item-value">' . $address . '</span>';
	$output .= '</div>';
}

if ( ! empty( $phone ) OR usb_is_post_preview() ) {
	$is_hidden = ( empty( $phone ) AND usb_is_post_preview() )
		? ' hidden'
		: '';

	$phone = us_replace_dynamic_value( $phone );
	$output .= '<div class="w-contacts-item for_phone' . $is_hidden . '">';
	$output .= '<span class="w-contacts-item-value">' . $phone . '</span>';
	$output .= '</div>';
}

if ( ! empty( $fax ) OR usb_is_post_preview() ) {
	$is_hidden = ( empty( $fax ) AND usb_is_post_preview() )
		? ' hidden'
		: '';

	$fax = us_replace_dynamic_value( $fax );
	$output .= '<div class="w-contacts-item for_mobile' . $is_hidden . '">';
	$output .= '<span class="w-contacts-item-value">' . $fax . '</span>';
	$output .= '</div>';
}

if ( ! empty( $email ) OR usb_is_post_preview() ) {
	$email = us_replace_dynamic_value( $email );
	if ( is_email( $email ) OR usb_is_post_preview() ) {
		$is_hidden = ( empty( $email ) AND usb_is_post_preview() )
			? ' hidden'
			: '';

		$output .= '<div class="w-contacts-item for_email' . $is_hidden . '">';
		$output .= '<span class="w-contacts-item-value"><a href="mailto:' . $email . '">' . $email . '</a></span>';
		$output .= '</div>';
	}
}

if ( empty( $output ) ) {
	return;
}

echo '<div' . us_implode_atts( $_atts ) . '>';
echo '<div class="w-contacts-list">' . $output . '</div>';
echo '</div>';
