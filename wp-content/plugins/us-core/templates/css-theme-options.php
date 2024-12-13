<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Generates and outputs theme options' generated styleshets
 *
 * @action Before the template: us_before_template:templates/css-theme-options
 * @action After the template: us_after_template:templates/css-theme-options
 */

$with_shop = class_exists( 'woocommerce' );
$cols_via_grid = ( us_get_option( 'live_builder' ) AND us_get_option( 'grid_columns_layout' ) );
$responsive_states = us_get_responsive_states();

// Add filter to remove protocols from URLs for better compatibility with caching plugins and services
if ( ! us_get_option( 'keep_url_protocol' ) ) {
	add_filter( 'clean_url', 'us_remove_url_protocol', 10 );
}

// Helper function to determine if CSS asset is used
if ( ! function_exists( 'us_is_asset_used' ) ) {
	function us_is_asset_used( $asset_name ) {
		$_assets = us_get_option( 'assets' );
		if (
			us_get_option( 'optimize_assets' )
			AND isset( $_assets[ $asset_name ] )
			AND $_assets[ $asset_name ] == 0
		) {
			return FALSE;
		}
		return TRUE;
	}
}

/* GLOBAL CSS VARS
 ====================================================================================================== */

echo ':root {';
$color_options = us_config( 'theme-options.colors.fields' );
foreach( $color_options as $color_option => $color_option_params ) {

	// Do not output empty color values
	if ( us_get_color( $color_option, TRUE, FALSE ) === '' ) {
		continue;
	}

	// Do not output variables without "color" prefix in its names
	if ( strpos( $color_option, 'color' ) !== 0 ) {
		continue;
	}

	echo '--' . str_replace( '_', '-', $color_option ) . ': ' . us_get_color( $color_option, FALSE, FALSE ) . ';';

	// Add separate values from color pickers that support gradients
	if ( ! empty( $color_option_params['with_gradient'] ) ) {
		echo '--' . str_replace( '_', '-', $color_option ) . '-grad: ' . us_get_color( $color_option, TRUE, FALSE ) . ';';
	}
}

// Add CSS VARS, needed to simplify CSS values globally
echo '--color-content-primary-faded:' . us_hex2rgba( us_get_color( '_content_primary', FALSE, FALSE ), 0.15 ) . ';';
echo '--box-shadow: 0 5px 15px rgba(0,0,0,.15);';
echo '--box-shadow-up: 0 -5px 15px rgba(0,0,0,.15);';
echo '--site-canvas-width: ' . us_get_option( 'site_canvas_width' ) . ';';
echo '--site-content-width: ' . us_get_option( 'site_content_width' ) . ';';
if ( us_get_option( 'enable_sidebar_titlebar' ) ) {
	echo '--site-sidebar-width: ' . us_get_option( 'sidebar_width' ) . ';';
}
if ( us_get_option( 'row_height' ) == 'custom' ) {
	echo '--section-custom-padding: ' . us_get_option( 'row_height_custom' ) . ';';
}
echo '--text-block-margin-bottom: ' . us_get_option( 'text_bottom_indent' ) . ';';
echo '--inputs-font-size: ' . us_get_option( 'input_fields' )[0]['font_size'] . ';';
echo '--inputs-height: ' . us_get_option( 'input_fields' )[0]['height'] . ';';
echo '--inputs-padding: ' . us_get_option( 'input_fields' )[0]['padding'] . ';';
echo '--inputs-border-width: ' . us_get_option( 'input_fields' )[0]['border_width'] . ';';
echo '--inputs-text-color: ' . us_get_color( us_get_option( 'input_fields' )[0]['color_text'] ) . ';';
echo '}';

/* DARK COLORS CSS VARS
 ====================================================================================================== */
$dark_theme = us_get_option( 'dark_theme', 'none' );
if ( $dark_theme !== 'none' ) {
	$color_schemes = us_get_color_schemes();

	echo '@media (prefers-color-scheme: dark) {';
	echo ':root {';
	foreach( $color_schemes[ $dark_theme ]['values'] as $color_schemes_option => $color_value ) {
		echo '--' . str_replace( '_', '-', $color_schemes_option ) . ': ' . us_gradient2hex( $color_value ) . ';';

		// Add separate values from color pickers that support gradients
		foreach( $color_options as $color_option => $color_option_params ) {
			if ( ! empty( $color_option_params['with_gradient'] ) AND $color_option === $color_schemes_option ) {
				echo '--' . str_replace( '_', '-', $color_schemes_option ) . '-grad: ' . $color_value . ';';
			}
		}

		if ( $color_schemes_option === 'color_content_primary' ) {
			echo '--color-content-primary-faded:' . us_hex2rgba( us_gradient2hex( $color_value ), 0.15 ) . ';';
		}
	}
	echo '}';
	echo '}';
}

/* Specific styles for gradient Headings */
if ( strpos( us_get_color( '_content_heading', TRUE ), 'grad' ) !== FALSE ) {
echo 'h1, h2, h3, h4, h5, h6 {';
echo 'background: var(--color-content-heading-grad);';
echo '-webkit-background-clip: text;';
echo 'color: transparent;';
echo '}';
}

/* Specific styles for gradient Headings in Alternate Content colors */
if ( strpos( us_get_color( '_alt_content_heading', TRUE ), 'grad' ) !== FALSE ) {
echo '.l-section.color_alternate h1,';
echo '.l-section.color_alternate h2,';
echo '.l-section.color_alternate h3,';
echo '.l-section.color_alternate h4,';
echo '.l-section.color_alternate h5,';
echo '.l-section.color_alternate h6 {';
echo 'background: var(--color-alt-content-heading-grad);';
echo '-webkit-background-clip: text;';
echo 'color: transparent;';
echo '}';
}

/* Specific styles for gradient Headings in Footer colors */
if ( strpos( us_get_color( '_footer_heading', TRUE ), 'grad' ) !== FALSE ) {
echo '.l-section.color_footer-top h1,';
echo '.l-section.color_footer-top h2,';
echo '.l-section.color_footer-top h3,';
echo '.l-section.color_footer-top h4,';
echo '.l-section.color_footer-top h5,';
echo '.l-section.color_footer-top h6 {';
echo 'background: var(--color-footer-heading-grad);';
echo '-webkit-background-clip: text;';
echo 'color: transparent;';
echo '}';
}
if ( strpos( us_get_color( '_subfooter_heading', TRUE ), 'grad' ) !== FALSE ) {
echo '.l-section.color_footer-bottom h1,';
echo '.l-section.color_footer-bottom h2,';
echo '.l-section.color_footer-bottom h3,';
echo '.l-section.color_footer-bottom h4,';
echo '.l-section.color_footer-bottom h5,';
echo '.l-section.color_footer-bottom h6 {';
echo 'background: var(--color-subfooter-heading-grad);';
echo '-webkit-background-clip: text;';
echo 'color: transparent;';
echo '}';
}

/* Specific styles for gradient icons in IconBoxes and Counters */
if ( us_is_asset_used( 'iconbox' ) OR us_is_asset_used( 'counter' ) ) {
	if ( strpos( us_get_color( '_content_primary', TRUE ), 'grad' ) !== FALSE ) {
		echo '.w-counter.color_primary .w-counter-value,';
		echo '.w-iconbox.color_primary.style_default .w-iconbox-icon i:not(.fad) {';
		echo 'background: var(--color-content-primary-grad);';
		echo '-webkit-background-clip: text;';
		echo 'color: transparent;';
		echo '}';
	}
	if ( strpos( us_get_color( '_content_secondary', TRUE ), 'grad' ) !== FALSE ) {
		echo '.w-counter.color_secondary .w-counter-value,';
		echo '.w-iconbox.color_secondary.style_default .w-iconbox-icon i:not(.fad) {';
		echo 'background: var(--color-content-secondary-grad);';
		echo '-webkit-background-clip: text;';
		echo 'color: transparent;';
		echo '}';
	}
}

// Add colors styles for Block Editor (Gutenberg), if it's enabled on the frontend
if ( us_get_option( 'block_editor' ) ) {
	$predefined_colors = array(
		'color_content_primary',
		'color_content_secondary',
		'color_content_heading',
		'color_content_text',
		'color_content_faded',
		'color_content_border',
		'color_content_bg_alt',
		'color_content_bg',
	);
	foreach ( $predefined_colors as $color ) {
		$color_name = str_replace( 'color_', '', $color );
		$color_name = str_replace( '_', '-', $color_name );

		echo '.has-' . $color_name . '-color {';
		echo 'color: var(--color-' . $color_name . ');';
		echo '}';

		// Gradients are possible for background
		echo '.has-' . $color_name . '-background-color {';
		echo 'background: var(--color-' . $color_name . '-grad);';
		echo '}';
	}
}

/* Typography
 =============================================================================================================================== */

// Styles for include uploaded fonts
$css = (string) us_get_uploaded_fonts_css();

// Live options CSS variables
if ( ! usb_is_preview() ) {
	$css .= (string) us_get_typography_inline_css();
}

// Headings h1-h6
for ( $h = 1; $h <= 6; $h ++ ) {
	$css .= 'h' . $h . '{';
	$css .= 'font-family: var(--h' . $h . '-font-family, inherit);';
	$css .= 'font-weight: var(--h' . $h . '-font-weight, inherit);';
	$css .= 'font-size: var(--h' . $h . '-font-size, inherit);';
	$css .= 'font-style: var(--h' . $h . '-font-style, inherit);';
	$css .= 'line-height: var(--h' . $h . '-line-height, 1.4);';
	$css .= 'letter-spacing: var(--h' . $h . '-letter-spacing, inherit);';
	$css .= 'text-transform: var(--h' . $h . '-text-transform, inherit);';
	$css .= 'margin-bottom: var(--h' . $h . '-margin-bottom, 1.5rem);';
	$css .= '}';
	$css .= 'h' . $h . ' > strong {';
	$css .= 'font-weight: var(--h' . $h . '-bold-font-weight, bold);';
	$css .= '}';

	// Assign color including gradients
	if (
		isset( us_get_typography_option_values( /* screen */'default' )[ 'h' . $h ]['color'] )
		AND $heading_color = us_get_typography_option_values( /* screen */'default' )[ 'h' . $h ]['color']
	) {
		$heading_color_override = us_get_typography_option_values( /* screen */'default' )[ 'h' . $h ]['color_override'] ? ' !important' : '';

		$heading_color = us_get_color( $heading_color, /* gradient */TRUE );

		$css .= 'h' . $h . '{';
		if ( strpos( $heading_color, 'grad' ) !== FALSE ) {
			$css .= 'background:' . $heading_color . ';';
			$css .= '-webkit-background-clip: text;';
			$css .= 'color: transparent' . $heading_color_override . ';';
		} else {
			$css .= 'color: var(--h' . $h . '-color, inherit)' . $heading_color_override . ';';
		}
		$css .= '}';
	}
}
echo strip_tags( $css );



/* Site Layout
 =============================================================================================================================== */

// Save 1 rem unit in pixels, used in @media calculations
if (
	isset( us_get_typography_option_values( /* screen */'default' )['body']['font-size'] )
	AND $body_font_size = us_get_typography_option_values( /* screen */'default' )['body']['font-size']
	AND strpos( $body_font_size, 'px' ) !== FALSE
) {
	$rem_in_px = (int) $body_font_size;
} else {
	$rem_in_px = 16; // default browser value
}

// Generate body background value
$body_bg_image = '';
$body_bg_color = us_get_color( us_get_option( 'color_body_bg' ), TRUE );

// Add image properties when image is set
if ( $body_bg_image_id = us_get_option( 'body_bg_image' ) AND $body_bg_image_url = wp_get_attachment_image_url( $body_bg_image_id, 'full' ) ) {
	$body_bg_image .= 'url(' . $body_bg_image_url . ') ';
	$body_bg_image .= us_get_option( 'body_bg_image_position' );
	if ( us_get_option( 'body_bg_image_size' ) != 'initial' ) {
		$body_bg_image .= '/' . us_get_option( 'body_bg_image_size' );
	}
	$body_bg_image .= ' ';
	$body_bg_image .= us_get_option( 'body_bg_image_repeat' );
	if ( ! us_get_option( 'body_bg_image_attachment', 0 ) ) {
		$body_bg_image .= ' fixed';
		$body_bg_image_fixed = TRUE;
	}

	// If the color value contains gradient, add comma for correct appearance
	if ( strpos( $body_bg_color, 'grad' ) !== FALSE ) {
		$body_bg_image .= ',';
	}
}
?>
body {
	background: <?= esc_attr( $body_bg_image . ' ' . $body_bg_color ) ?>;
	}
<?php if ( ! empty( $body_bg_image_fixed ) ) { ?>
/* This hack is used for iOS Safari, which can't show the background image with the "backround-attachment: fixed" property */
html.ios-touch .l-body:before {
	content: '';
	position: fixed;
	z-index: -1;
	top: 0;
	left: 0;
	right: 0;
	height: 100vh;
	background: inherit;
	background-attachment: scroll;
	}
<?php } ?>

/* Limit width for centered images */
@media (max-width: <?= ( (int) us_get_option( 'site_content_width' ) + $rem_in_px * 5 ) ?>px) {
.l-main .aligncenter {
	max-width: calc(100vw - 5rem);
	}
}

/* Footer Reveal Effect */
<?php if ( us_get_option( 'footer_reveal' ) ) { ?>
@media (min-width: <?= us_get_option( 'columns_stacking_width' ) ?>) {
body.footer_reveal .l-canvas {
	position: relative;
	z-index: 1;
	}
body.footer_reveal .l-footer {
	position: fixed;
	bottom: 0;
	}
body.footer_reveal .l-canvas.type_boxed ~ .l-footer {
	left: 0;
	right: 0;
	}
}
<?php } ?>

/* MODIFY ELEMENTS ON RESPONSIVE STATES */
@media <?= $responsive_states['default']['media_query'] ?> {
body.usb_preview .hide_on_default {
	opacity: 0.25 !important;
	}
.vc_hidden-lg,
body:not(.usb_preview) .hide_on_default {
	display: none !important;
	}
.default_align_left {
	text-align: left;
	justify-content: flex-start;
	}
.default_align_right {
	text-align: right;
	justify-content: flex-end;
	}
.default_align_center {
	text-align: center;
	justify-content: center;
	}
.default_align_justify {
	justify-content: space-between;
	}
.w-hwrapper > .default_align_justify,
.default_align_justify > .w-btn {
	width: 100%;
	}
}
@media <?= $responsive_states['laptops']['media_query'] ?> {
body.usb_preview .hide_on_laptops {
	opacity: 0.25 !important;
	}
.vc_hidden-md,
body:not(.usb_preview) .hide_on_laptops {
	display: none !important;
	}
.laptops_align_left {
	text-align: left;
	justify-content: flex-start;
	}
.laptops_align_right {
	text-align: right;
	justify-content: flex-end;
	}
.laptops_align_center {
	text-align: center;
	justify-content: center;
	}
.laptops_align_justify {
	justify-content: space-between;
	}
.w-hwrapper > .laptops_align_justify,
.laptops_align_justify > .w-btn {
	width: 100%;
	}
.g-cols.via_grid[style*="--laptops-gap"] {
	grid-gap: var(--laptops-gap, 3rem);
	}
}
@media <?= $responsive_states['tablets']['media_query'] ?> {
body.usb_preview .hide_on_tablets {
	opacity: 0.25 !important;
	}
.vc_hidden-sm,
body:not(.usb_preview) .hide_on_tablets {
	display: none !important;
	}
.tablets_align_left {
	text-align: left;
	justify-content: flex-start;
	}
.tablets_align_right {
	text-align: right;
	justify-content: flex-end;
	}
.tablets_align_center {
	text-align: center;
	justify-content: center;
	}
.tablets_align_justify {
	justify-content: space-between;
	}
.w-hwrapper > .tablets_align_justify,
.tablets_align_justify > .w-btn {
	width: 100%;
	}
.g-cols.via_grid[style*="--tablets-gap"] {
	grid-gap: var(--tablets-gap, 3rem);
	}
}
@media <?= $responsive_states['mobiles']['media_query'] ?> {
body.usb_preview .hide_on_mobiles {
	opacity: 0.25 !important;
	}
.vc_hidden-xs,
body:not(.usb_preview) .hide_on_mobiles {
	display: none !important;
	}
.mobiles_align_left {
	text-align: left;
	justify-content: flex-start;
	}
.mobiles_align_right {
	text-align: right;
	justify-content: flex-end;
	}
.mobiles_align_center {
	text-align: center;
	justify-content: center;
	}
.mobiles_align_justify {
	justify-content: space-between;
	}
.w-hwrapper > .mobiles_align_justify,
.mobiles_align_justify > .w-btn {
	width: 100%;
	}
.w-hwrapper.stack_on_mobiles {
	display: block;
	}
	.w-hwrapper.stack_on_mobiles > * {
		display: block;
		margin: 0 0 var(--hwrapper-gap, 1.2rem);
		}
	.w-hwrapper.stack_on_mobiles > :last-child {
		margin-bottom: 0;
		}
.g-cols.via_grid[style*="--mobiles-gap"] {
	grid-gap: var(--mobiles-gap, 1.5rem);
	}
}

<?php
// CSS FLEX columns layout (legacy from WPBakery)
if ( us_is_asset_used( 'columns' ) AND ! $cols_via_grid ) {

	// Define the side for margins offset
	$_offset = is_rtl() ? 'right' : 'left';
	?>
	@media (max-width:<?= $responsive_states['mobiles']['max_width'] ?>px) {
	.g-cols.type_default > div[class*="vc_col-xs-"] {
		margin-top: 1rem;
		margin-bottom: 1rem;
		}
	.g-cols > div:not([class*="vc_col-xs-"]) {
		width: 100%;
		margin: 0 0 1.5rem;
		}
	.g-cols.reversed > div:last-of-type {
		order: -1;
		}
	.g-cols.type_boxes > div,
	.g-cols.reversed > div:first-child,
	.g-cols:not(.reversed) > div:last-child,
	.g-cols > div.has_bg_color {
		margin-bottom: 0;
		}
	.vc_col-xs-1 { width: 8.3333%; }
	.vc_col-xs-2 { width: 16.6666%; }
	.vc_col-xs-1\/5 { width: 20%; }
	.vc_col-xs-3 { width: 25%; }
	.vc_col-xs-4 { width: 33.3333%; }
	.vc_col-xs-2\/5 { width: 40%; }
	.vc_col-xs-5 { width: 41.6666%; }
	.vc_col-xs-6 { width: 50%; }
	.vc_col-xs-7 { width: 58.3333%; }
	.vc_col-xs-3\/5 { width: 60%; }
	.vc_col-xs-8 { width: 66.6666%; }
	.vc_col-xs-9 { width: 75%; }
	.vc_col-xs-4\/5 { width: 80%; }
	.vc_col-xs-10 { width: 83.3333%; }
	.vc_col-xs-11 { width: 91.6666%; }
	.vc_col-xs-12 { width: 100%; }
	.vc_col-xs-offset-0 { margin-<?= $_offset ?>: 0; }
	.vc_col-xs-offset-1 { margin-<?= $_offset ?>: 8.3333%; }
	.vc_col-xs-offset-2 { margin-<?= $_offset ?>: 16.6666%; }
	.vc_col-xs-offset-1\/5 { margin-<?= $_offset ?>: 20%; }
	.vc_col-xs-offset-3 { margin-<?= $_offset ?>: 25%; }
	.vc_col-xs-offset-4 { margin-<?= $_offset ?>: 33.3333%; }
	.vc_col-xs-offset-2\/5 { margin-<?= $_offset ?>: 40%; }
	.vc_col-xs-offset-5 { margin-<?= $_offset ?>: 41.6666%; }
	.vc_col-xs-offset-6 { margin-<?= $_offset ?>: 50%; }
	.vc_col-xs-offset-7 { margin-<?= $_offset ?>: 58.3333%; }
	.vc_col-xs-offset-3\/5 { margin-<?= $_offset ?>: 60%; }
	.vc_col-xs-offset-8 { margin-<?= $_offset ?>: 66.6666%; }
	.vc_col-xs-offset-9 { margin-<?= $_offset ?>: 75%; }
	.vc_col-xs-offset-4\/5 { margin-<?= $_offset ?>: 80%; }
	.vc_col-xs-offset-10 { margin-<?= $_offset ?>: 83.3333%; }
	.vc_col-xs-offset-11 { margin-<?= $_offset ?>: 91.6666%; }
	.vc_col-xs-offset-12 { margin-<?= $_offset ?>: 100%; }
	}
	@media (min-width:<?= $responsive_states['tablets']['min_width'] ?>px) {
	.vc_col-sm-1 { width: 8.3333%; }
	.vc_col-sm-2 { width: 16.6666%; }
	.vc_col-sm-1\/5 { width: 20%; }
	.vc_col-sm-3 { width: 25%; }
	.vc_col-sm-4 { width: 33.3333%; }
	.vc_col-sm-2\/5 { width: 40%; }
	.vc_col-sm-5 { width: 41.6666%; }
	.vc_col-sm-6 { width: 50%; }
	.vc_col-sm-7 { width: 58.3333%; }
	.vc_col-sm-3\/5 { width: 60%; }
	.vc_col-sm-8 { width: 66.6666%; }
	.vc_col-sm-9 { width: 75%; }
	.vc_col-sm-4\/5 { width: 80%; }
	.vc_col-sm-10 { width: 83.3333%; }
	.vc_col-sm-11 { width: 91.6666%; }
	.vc_col-sm-12 { width: 100%; }
	.vc_col-sm-offset-0 { margin-<?= $_offset ?>: 0; }
	.vc_col-sm-offset-1 { margin-<?= $_offset ?>: 8.3333%; }
	.vc_col-sm-offset-2 { margin-<?= $_offset ?>: 16.6666%; }
	.vc_col-sm-offset-1\/5 { margin-<?= $_offset ?>: 20%; }
	.vc_col-sm-offset-3 { margin-<?= $_offset ?>: 25%; }
	.vc_col-sm-offset-4 { margin-<?= $_offset ?>: 33.3333%; }
	.vc_col-sm-offset-2\/5 { margin-<?= $_offset ?>: 40%; }
	.vc_col-sm-offset-5 { margin-<?= $_offset ?>: 41.6666%; }
	.vc_col-sm-offset-6 { margin-<?= $_offset ?>: 50%; }
	.vc_col-sm-offset-7 { margin-<?= $_offset ?>: 58.3333%; }
	.vc_col-sm-offset-3\/5 { margin-<?= $_offset ?>: 60%; }
	.vc_col-sm-offset-8 { margin-<?= $_offset ?>: 66.6666%; }
	.vc_col-sm-offset-9 { margin-<?= $_offset ?>: 75%; }
	.vc_col-sm-offset-4\/5 { margin-<?= $_offset ?>: 80%; }
	.vc_col-sm-offset-10 { margin-<?= $_offset ?>: 83.3333%; }
	.vc_col-sm-offset-11 { margin-<?= $_offset ?>: 91.6666%; }
	.vc_col-sm-offset-12 { margin-<?= $_offset ?>: 100%; }
	}
	@media (min-width:<?= $responsive_states['laptops']['min_width'] ?>px) {
	.vc_col-md-1 { width: 8.3333%; }
	.vc_col-md-2 { width: 16.6666%; }
	.vc_col-md-1\/5 { width: 20%; }
	.vc_col-md-3 { width: 25%; }
	.vc_col-md-4 { width: 33.3333%; }
	.vc_col-md-2\/5 { width: 40%; }
	.vc_col-md-5 { width: 41.6666%; }
	.vc_col-md-6 { width: 50%; }
	.vc_col-md-7 { width: 58.3333%; }
	.vc_col-md-3\/5 { width: 60%; }
	.vc_col-md-8 { width: 66.6666%; }
	.vc_col-md-9 { width: 75%; }
	.vc_col-md-4\/5 { width: 80%; }
	.vc_col-md-10 { width: 83.3333%; }
	.vc_col-md-11 { width: 91.6666%; }
	.vc_col-md-12 { width: 100%; }
	.vc_col-md-offset-0 { margin-<?= $_offset ?>: 0; }
	.vc_col-md-offset-1 { margin-<?= $_offset ?>: 8.3333%; }
	.vc_col-md-offset-2 { margin-<?= $_offset ?>: 16.6666%; }
	.vc_col-md-offset-1\/5 { margin-<?= $_offset ?>: 20%; }
	.vc_col-md-offset-3 { margin-<?= $_offset ?>: 25%; }
	.vc_col-md-offset-4 { margin-<?= $_offset ?>: 33.3333%; }
	.vc_col-md-offset-2\/5 { margin-<?= $_offset ?>: 40%; }
	.vc_col-md-offset-5 { margin-<?= $_offset ?>: 41.6666%; }
	.vc_col-md-offset-6 { margin-<?= $_offset ?>: 50%; }
	.vc_col-md-offset-7 { margin-<?= $_offset ?>: 58.3333%; }
	.vc_col-md-offset-3\/5 { margin-<?= $_offset ?>: 60%; }
	.vc_col-md-offset-8 { margin-<?= $_offset ?>: 66.6666%; }
	.vc_col-md-offset-9 { margin-<?= $_offset ?>: 75%; }
	.vc_col-md-offset-4\/5 { margin-<?= $_offset ?>: 80%; }
	.vc_col-md-offset-10 { margin-<?= $_offset ?>: 83.3333%; }
	.vc_col-md-offset-11 { margin-<?= $_offset ?>: 91.6666%; }
	.vc_col-md-offset-12 { margin-<?= $_offset ?>: 100%; }
	}
	@media (min-width:<?= $responsive_states['default']['min_width'] ?>px) {
	.vc_col-lg-1 { width: 8.3333%; }
	.vc_col-lg-2 { width: 16.6666%; }
	.vc_col-lg-1\/5 { width: 20%; }
	.vc_col-lg-3 { width: 25%; }
	.vc_col-lg-4 { width: 33.3333%; }
	.vc_col-lg-2\/5 { width: 40%; }
	.vc_col-lg-5 { width: 41.6666%; }
	.vc_col-lg-6 { width: 50%; }
	.vc_col-lg-7 { width: 58.3333%; }
	.vc_col-lg-3\/5 { width: 60%; }
	.vc_col-lg-8 { width: 66.6666%; }
	.vc_col-lg-9 { width: 75%; }
	.vc_col-lg-4\/5 { width: 80%; }
	.vc_col-lg-10 { width: 83.3333%; }
	.vc_col-lg-11 { width: 91.6666%; }
	.vc_col-lg-12 { width: 100%; }
	.vc_col-lg-offset-0 { margin-<?= $_offset ?>: 0; }
	.vc_col-lg-offset-1 { margin-<?= $_offset ?>: 8.3333%; }
	.vc_col-lg-offset-2 { margin-<?= $_offset ?>: 16.6666%; }
	.vc_col-lg-offset-1\/5 { margin-<?= $_offset ?>: 20%; }
	.vc_col-lg-offset-3 { margin-<?= $_offset ?>: 25%; }
	.vc_col-lg-offset-4 { margin-<?= $_offset ?>: 33.3333%; }
	.vc_col-lg-offset-2\/5 { margin-<?= $_offset ?>: 40%; }
	.vc_col-lg-offset-5 { margin-<?= $_offset ?>: 41.6666%; }
	.vc_col-lg-offset-6 { margin-<?= $_offset ?>: 50%; }
	.vc_col-lg-offset-7 { margin-<?= $_offset ?>: 58.3333%; }
	.vc_col-lg-offset-3\/5 { margin-<?= $_offset ?>: 60%; }
	.vc_col-lg-offset-8 { margin-<?= $_offset ?>: 66.6666%; }
	.vc_col-lg-offset-9 { margin-<?= $_offset ?>: 75%; }
	.vc_col-lg-offset-4\/5 { margin-<?= $_offset ?>: 80%; }
	.vc_col-lg-offset-10 { margin-<?= $_offset ?>: 83.3333%; }
	.vc_col-lg-offset-11 { margin-<?= $_offset ?>: 91.6666%; }
	.vc_col-lg-offset-12 { margin-<?= $_offset ?>: 100%; }
	}
	@media <?= $responsive_states['tablets']['media_query'] ?> {
	.g-cols.via_flex.type_default > div[class*="vc_col-md-"],
	.g-cols.via_flex.type_default > div[class*="vc_col-lg-"] {
		margin-top: 1rem;
		margin-bottom: 1rem;
		}
	}
	@media <?= $responsive_states['laptops']['media_query'] ?> {
	.g-cols.via_flex.type_default > div[class*="vc_col-lg-"] {
		margin-top: 1rem;
		margin-bottom: 1rem;
		}
	}
<?php }

// CSS GRID columns layout
if ( us_is_asset_used( 'columns' ) AND $cols_via_grid ) {
	foreach ( $responsive_states as $state => $data ) {
		if ( $state == 'default' ) {
			continue;
		}
		?>
		@media (max-width: <?= $data['max_width'] ?>px) {
			.g-cols.<?= $state ?>-cols_1 {
				grid-template-columns: 100%;
				}
			.g-cols.<?= $state ?>-cols_1.reversed > div:last-of-type {
				order: -1;
				}
			.g-cols.<?= $state ?>-cols_2 {
				grid-template-columns: repeat(2, 1fr);
				}
			.g-cols.<?= $state ?>-cols_3 {
				grid-template-columns: repeat(3, 1fr);
				}
			.g-cols.<?= $state ?>-cols_4 {
				grid-template-columns: repeat(4, 1fr);
				}
			.g-cols.<?= $state ?>-cols_5 {
				grid-template-columns: repeat(5, 1fr);
				}
			.g-cols.<?= $state ?>-cols_6 {
				grid-template-columns: repeat(6, 1fr);
				}
			.g-cols.<?= $state ?>-cols_1-2 {
				grid-template-columns: 1fr 2fr;
				}
			.g-cols.<?= $state ?>-cols_2-1 {
				grid-template-columns: 2fr 1fr;
				}
			.g-cols.<?= $state ?>-cols_2-3 {
				grid-template-columns: 2fr 3fr;
				}
			.g-cols.<?= $state ?>-cols_3-2 {
				grid-template-columns: 3fr 2fr;
				}
			.g-cols.<?= $state ?>-cols_1-3 {
				grid-template-columns: 1fr 3fr;
				}
			.g-cols.<?= $state ?>-cols_3-1 {
				grid-template-columns: 3fr 1fr;
				}
			.g-cols.<?= $state ?>-cols_1-4 {
				grid-template-columns: 1fr 4fr;
				}
			.g-cols.<?= $state ?>-cols_4-1 {
				grid-template-columns: 4fr 1fr;
				}
			.g-cols.<?= $state ?>-cols_1-5 {
				grid-template-columns: 1fr 5fr;
				}
			.g-cols.<?= $state ?>-cols_5-1 {
				grid-template-columns: 5fr 1fr;
				}
			.g-cols.<?= $state ?>-cols_1-2-1 {
				grid-template-columns: 1fr 2fr 1fr;
				}
			.g-cols.<?= $state ?>-cols_1-3-1 {
				grid-template-columns: 1fr 3fr 1fr;
				}
			.g-cols.<?= $state ?>-cols_1-4-1 {
				grid-template-columns: 1fr 4fr 1fr;
				}
			<?php if ( $state == 'mobiles' ) { ?>
			.g-cols:not([style*="--gap"]) {
				grid-gap: 1.5rem;
				}
			<?php } ?>
		}
		<?php
	}
}
?>

/* COLUMNS STACKING WIDTH */
@media (max-width: <?= ( (int) us_get_option( 'columns_stacking_width' ) - 1 ) ?>px) {
.l-canvas {
	overflow: hidden;
	}
.g-cols.stacking_default.reversed > div:last-of-type {
	order: -1;
	}
.g-cols.stacking_default.via_flex > div:not([class*="vc_col-xs"]) {
	width: 100%;
	margin: 0 0 1.5rem;
	}
.g-cols.stacking_default.via_grid.mobiles-cols_1 {
	grid-template-columns: 100%;
	}
.g-cols.stacking_default.via_flex.type_boxes > div,
.g-cols.stacking_default.via_flex.reversed > div:first-child,
.g-cols.stacking_default.via_flex:not(.reversed) > div:last-child,
.g-cols.stacking_default.via_flex > div.has_bg_color {
	margin-bottom: 0;
	}
.g-cols.stacking_default.via_flex.type_default > .wpb_column.stretched {
	margin-left: -1rem;
	margin-right: -1rem;
	}
.g-cols.stacking_default.via_grid.mobiles-cols_1 > .wpb_column.stretched,
.g-cols.stacking_default.via_flex.type_boxes > .wpb_column.stretched {
	margin-left: -2.5rem;
	margin-right: -2.5rem;
	width: auto;
	}
.vc_column-inner.type_sticky > .wpb_wrapper,
.vc_column_container.type_sticky > .vc_column-inner {
	top: 0 !important;
	}
}

@media (min-width: <?= us_get_option( 'columns_stacking_width' ) ?>) {
body:not(.rtl) .l-section.for_sidebar.at_left > div > .l-sidebar,
.rtl .l-section.for_sidebar.at_right > div > .l-sidebar {
	order: -1;
	}
.vc_column_container.type_sticky > .vc_column-inner,
.vc_column-inner.type_sticky > .wpb_wrapper {
	position: -webkit-sticky;
	position: sticky;
	}
.l-section.type_sticky {
	position: -webkit-sticky;
	position: sticky;
	top: 0;
	z-index: 11;
	transform: translateZ(0); /* render fix for webkit browsers */
	transition: top 0.3s cubic-bezier(.78,.13,.15,.86) 0.1s;
	}
.header_hor .l-header.post_fixed.sticky_auto_hide {
	z-index: 12;
	}
.admin-bar .l-section.type_sticky {
	top: 32px;
	}
.l-section.type_sticky > .l-section-h {
	transition: padding-top 0.3s;
	}
.header_hor .l-header.pos_fixed:not(.down) ~ .l-main .l-section.type_sticky:not(:first-of-type) {
	top: var(--header-sticky-height);
	}
.admin-bar.header_hor .l-header.pos_fixed:not(.down) ~ .l-main .l-section.type_sticky:not(:first-of-type) {
	top: calc( var(--header-sticky-height) + 32px );
	}
.header_hor .l-header.pos_fixed.sticky:not(.down) ~ .l-main .l-section.type_sticky:first-of-type > .l-section-h {
	padding-top: var(--header-sticky-height);
	}
.header_hor.headerinpos_bottom .l-header.pos_fixed.sticky:not(.down) ~ .l-main .l-section.type_sticky:first-of-type > .l-section-h {
	padding-bottom: var(--header-sticky-height) !important;
	}
}

/* EMULATE INDENTS TO THE SCREEN EDGES */
<?php
// Calculate Vertical Header width on the "desktops" state
if ( us_get_header_option( 'orientation' ) === 'ver' ) {
	if ( strpos( us_get_header_option( 'width' ), 'px' ) !== FALSE ) {
		$header_width_px = (int) us_get_header_option( 'width' );
	} else {
		$header_width_px = (int) us_get_header_option( 'width' ) * $rem_in_px;
	}
} else {
	$header_width_px = 0;
}
?>
@media screen and (min-width: <?= ( (int) us_get_option( 'site_content_width' ) + $rem_in_px * 5 + $header_width_px ) ?>px) {
.g-cols.via_flex.type_default > .wpb_column.stretched:first-of-type {
	margin-left: calc( var(--site-content-width) / 2 + <?= $header_width_px ?>px / 2 + 1.5rem - 50vw);
	}
.g-cols.via_flex.type_default > .wpb_column.stretched:last-of-type {
	margin-right: calc( var(--site-content-width) / 2 + <?= $header_width_px ?>px / 2 + 1.5rem - 50vw);
	}
.l-main .alignfull, /* Full width for Gutenberg blocks */
.w-separator.width_screen,
.g-cols.via_grid > .wpb_column.stretched:first-of-type,
.g-cols.via_flex.type_boxes > .wpb_column.stretched:first-of-type {
	margin-left: calc( var(--site-content-width) / 2 + <?= $header_width_px ?>px / 2 - 50vw );
	}
.l-main .alignfull, /* Full width for Gutenberg blocks */
.w-separator.width_screen,
.g-cols.via_grid > .wpb_column.stretched:last-of-type,
.g-cols.via_flex.type_boxes > .wpb_column.stretched:last-of-type {
	margin-right: calc( var(--site-content-width) / 2 + <?= $header_width_px ?>px / 2 - 50vw );
	}
}

<?php if ( us_is_asset_used( 'forms' ) ) {
	?>
	@media (max-width: <?= (int) us_get_option( 'mobiles_breakpoint' ) ?>px) {
	.w-form-row.for_submit[style*=btn-size-mobiles] .w-btn {
		font-size: var(--btn-size-mobiles) !important;
		}
	}
	<?php
}

if ( us_get_option( 'keyboard_accessibility' ) ) { ?>
a:focus,
button:focus,
input[type=checkbox]:focus + i,
input[type=submit]:focus {
	outline: 2px dotted var(--color-content-primary);
	}
<?php } else { ?>
a,
button,
input[type=submit],
.ui-slider-handle {
	outline: none !important;
	}
<?php } ?>

/* "Back to top" and Vertical Header opening buttons */
<?php if ( us_get_option( 'back_to_top' ) AND ! us_get_option( 'back_to_top_style' ) ) { ?>
.w-toplink,
<?php } ?>
.w-header-show {
	background: <?php echo us_get_color( us_get_option( 'back_to_top_color' ), TRUE ) ?>;
	}
<?php if ( us_get_option( 'back_to_top' ) AND ! us_get_option( 'back_to_top_style' ) ) { ?>
.no-touch .w-toplink.active:hover,
<?php } ?>
.no-touch .w-header-show:hover {
	background: var(--color-content-primary-grad);
	}
<?php



/* BUTTONS STYLES
 ====================================================================================================== */
if ( us_is_asset_used( 'buttons' ) AND $btn_styles = us_get_option( 'buttons' ) ) {

	// Remove transition if the FIRST style has a gradient in its background (gradients don't support transition)
	if (
		strpos( $btn_styles[0]['color_bg'], 'grad' ) !== FALSE
		OR strpos( $btn_styles[0]['color_bg_hover'], 'grad' ) !== FALSE
	) {
		echo 'button[type=submit], input[type=submit] { transition: none; }';
	}

	// Generate Buttons Styles
	foreach ( $btn_styles as $key => $btn_style ) {

		// Set the FIRST style for non-editable button elements
		if ( $key === 0 ) {
			echo 'button[type=submit]:not(.w-btn),';
			echo 'input[type=submit]:not(.w-btn),';
		}
		if ( $with_shop AND us_get_option( 'shop_secondary_btn_style' ) == $btn_style['id'] ) {
			echo '.woocommerce .button, .woocommerce .actions .button,';
		}
		if ( $with_shop AND us_get_option( 'shop_primary_btn_style' ) == $btn_style['id'] ) {
			echo '.woocommerce .button.alt, .woocommerce .button.checkout, .woocommerce .button.add_to_cart_button,';
		}
		echo '.us-nav-style_' . $btn_style['id'] . ' > *,';
		echo '.navstyle_' . $btn_style['id'] . ' > .owl-nav button,';
		echo '.us-btn-style_' . $btn_style['id'] . '{';
		if ( ! empty( $btn_style['font'] ) ) {
			if ( in_array( $btn_style['font'], US_TYPOGRAPHY_TAGS ) ) {
				if ( $btn_style['font'] == 'body' ) {
					$btn_style['font'] = 'var(--font-family)';
				} else {
					$btn_style['font'] = sprintf( 'var(--%s-font-family)', $btn_style['font'] );
				}
			}
			echo 'font-family:' . $btn_style['font'] . ';';
		}
		if ( isset( $btn_style['font_size'] ) ) {
			echo 'font-size:' . $btn_style['font_size'] . ';';
		}
		if ( isset( $btn_style['line_height'] ) ) {
			echo 'line-height:' . $btn_style['line_height'] . '!important;';
		}

		// Fallback for var type
		if ( is_array( $btn_style['text_style'] ) ) {
			$btn_style['text_style'] = implode( ',', $btn_style['text_style'] );
		}

		echo 'font-weight:' . $btn_style['font_weight'] . ';';
		echo 'font-style:' . ( strpos( $btn_style['text_style'], 'italic' ) !== FALSE ? 'italic' : 'normal' ) . ';';
		echo 'text-transform:' . ( strpos( $btn_style['text_style'], 'uppercase' ) !== FALSE ? 'uppercase' : 'none' ) . ';';
		echo 'letter-spacing:' . $btn_style['letter_spacing'] . ';';
		if ( ! empty( $btn_style['border_radius'] ) ) {
			echo 'border-radius:' . $btn_style['border_radius'] . ';';
		}
		echo 'padding:' . $btn_style['height'] . ' ' . $btn_style['width'] . ';';
		echo 'background:' . ( ! empty( us_get_color( $btn_style['color_bg'], /* Gradient */ TRUE ) )
			? us_get_color( $btn_style['color_bg'], /* Gradient */ TRUE )
			: 'transparent' ) . ';';
		if ( ! empty( $btn_style['color_border'] ) ) {
			$border_color = us_get_color( $btn_style['color_border'], /* Gradient*/ TRUE );
			if ( strpos( $border_color, 'grad' ) !== FALSE ) {
				echo 'border-image:' . $border_color . ' 1;';
			} else {
				echo 'border-color:' . $border_color . ';';
			}
		} else {
			echo 'border-color: transparent;';
		}
		if ( ! empty( $btn_style['color_text'] ) ) {
			echo 'color:' . us_get_color( $btn_style['color_text'] ) . '!important;';
		}

		// Shadow
		if ( ! empty( $btn_style['color_shadow'] ) ) {
			$_inset = ! empty( $btn_style['shadow_inset'] ) ? 'inset' : '';
			$_offset_h = ! empty( $btn_style['shadow_offset_h'] ) ? $btn_style['shadow_offset_h'] : '0';
			$_offset_v = ! empty( $btn_style['shadow_offset_v'] ) ? $btn_style['shadow_offset_v'] : '0';
			$_blur = ! empty( $btn_style['shadow_blur'] ) ? $btn_style['shadow_blur'] : '0';
			$_spread = ! empty( $btn_style['shadow_spread'] ) ? $btn_style['shadow_spread'] : '0';
			echo sprintf(
				'box-shadow: %s %s %s %s %s %s;',
				$_inset,
				$_offset_h,
				$_offset_v,
				$_blur,
				$_spread,
				us_get_color( $btn_style['color_shadow'] )
			);
		}
		echo '}';

		// Border imitation
		if ( $key === 0 ) {
			echo 'button[type=submit]:not(.w-btn):before,';
			echo 'input[type=submit]:not(.w-btn),';
		}
		if ( $with_shop AND us_get_option( 'shop_secondary_btn_style' ) == $btn_style['id'] ) {
			echo '.woocommerce .button:before, .woocommerce .actions .button:before,';
		}
		if ( $with_shop AND us_get_option( 'shop_primary_btn_style' ) == $btn_style['id'] ) {
			echo '.woocommerce .button.alt:before, .woocommerce .button.checkout:before, .woocommerce .button.add_to_cart_button:before,';
		}
		echo '.us-nav-style_' . $btn_style['id'] . ' > *:before,';
		echo '.navstyle_' . $btn_style['id'] . ' > .owl-nav button:before,';
		echo '.us-btn-style_' . $btn_style['id'] . ':before {';
		echo 'border-width:' . $btn_style['border_width'] . ';';
		echo '}';

		// HOVER STATE
		if ( $key === 0 ) {
			echo '.no-touch button[type=submit]:not(.w-btn):hover,';
			echo '.no-touch input[type=submit]:not(.w-btn):hover,';
		}
		if ( $with_shop AND us_get_option( 'shop_secondary_btn_style' ) == $btn_style['id'] ) {
			echo '.no-touch .woocommerce .button:hover, .no-touch .woocommerce .actions .button:hover,';
		}
		if ( $with_shop AND us_get_option( 'shop_primary_btn_style' ) == $btn_style['id'] ) {
			echo '.no-touch .woocommerce .button.alt:hover, .no-touch .woocommerce .button.checkout:hover, .no-touch .woocommerce .button.add_to_cart_button:hover,';
		}
		echo '.us-nav-style_' . $btn_style['id'] . ' > span.current,';
		echo '.no-touch .us-nav-style_' . $btn_style['id'] . ' > a:hover,';
		echo '.no-touch .navstyle_' . $btn_style['id'] . ' > .owl-nav button:hover,';
		echo '.no-touch .us-btn-style_' . $btn_style['id'] . ':hover {';

		// Shadow on Hover
		if ( ! empty( $btn_style['color_shadow_hover'] ) ) {
			$_inset = ! empty( $btn_style['shadow_hover_inset'] ) ? 'inset' : '';
			$_offset_h = ! empty( $btn_style['shadow_hover_offset_h'] ) ? $btn_style['shadow_hover_offset_h'] : '0';
			$_offset_v = ! empty( $btn_style['shadow_hover_offset_v'] ) ? $btn_style['shadow_hover_offset_v'] : '0';
			$_blur = ! empty( $btn_style['shadow_hover_blur'] ) ? $btn_style['shadow_hover_blur'] : '0';
			$_spread = ! empty( $btn_style['shadow_hover_spread'] ) ? $btn_style['shadow_hover_spread'] : '0';
			echo sprintf(
				'box-shadow: %s %s %s %s %s %s;',
				$_inset,
				$_offset_h,
				$_offset_v,
				$_blur,
				$_spread,
				us_get_color( $btn_style['color_shadow_hover'] )
			);
		}

		echo 'background:' . (
			! empty( $btn_style['color_bg_hover'] )
				? us_get_color( $btn_style['color_bg_hover'], /* Gradient */ TRUE )
				: 'transparent'
			) . ';';
		if ( ! empty( $btn_style['color_border_hover'] ) ) {
			$border_color = us_get_color( $btn_style['color_border_hover'], /* Gradient */ TRUE );
			if ( strpos( $border_color, 'grad' ) !== FALSE ) {
				echo 'border-image:' . $border_color . ' 1;';
			} else {
				echo 'border-color:' . $border_color . ';';
			}
		} else {
			echo 'border-color: transparent;';
		}
		if ( ! empty( $btn_style['color_text_hover'] ) ) {
			echo 'color:' . us_get_color( $btn_style['color_text_hover'] ) . '!important;';
		}
		echo '}';

		// Add min-width for Pagination to make correct circles or squares
		if ( isset( $btn_style['line_height'] ) ) {
			$btn_line_height = strpos( $btn_style['line_height'], 'px' ) !== FALSE ? $btn_style['line_height'] : $btn_style['line_height'] . 'em';
		} else {
			$btn_line_height = '1.2em';
		}
		echo '.us-nav-style_' . $btn_style['id'] . ' > *{';
		echo 'min-width:calc(' . $btn_line_height . ' + 2 * ' . $btn_style['height'] . ');';
		echo '}';

		// Check if the button background has a gradient
		$has_gradient = FALSE;
		if (
			strpos( us_get_color( $btn_style['color_bg'], /* Gradient */ TRUE ), 'grad' ) !== FALSE
			OR strpos( us_get_color( $btn_style['color_bg_hover'], /* Gradient */ TRUE ), 'grad' ) !== FALSE
		) {
			$has_gradient = TRUE;
		}

		// Extra layer for "Slide" hover type OR for gradient backgrounds (cause gradients don't support transition)
		if ( ( isset( $btn_style['hover'] ) AND $btn_style['hover'] == 'slide' ) OR $has_gradient ) {

			if ( $with_shop AND us_get_option( 'shop_primary_btn_style' ) == $btn_style['id'] ) {
				echo '.woocommerce .button.add_to_cart_button,';
			}
			echo '.us-btn-style_' . $btn_style['id'] . '{';
			echo 'overflow: hidden;';
			echo '-webkit-transform: translateZ(0);'; // fix for Safari
			echo '}';

			if ( $with_shop AND us_get_option( 'shop_primary_btn_style' ) == $btn_style['id'] ) {
				echo '.no-touch .woocommerce .button.add_to_cart_button > *,';
			}
			echo '.us-btn-style_' . $btn_style['id'] . ' > * {';
			echo 'position: relative;';
			echo 'z-index: 1;';
			echo '}';

			if ( $with_shop AND us_get_option( 'shop_primary_btn_style' ) == $btn_style['id'] ) {
				echo '.no-touch .woocommerce .button.add_to_cart_button:hover,';
			}
			echo '.no-touch .us-btn-style_' . $btn_style['id'] . ':hover {';
			if ( ! empty( us_get_color( $btn_style['color_bg'], /* Gradient */ TRUE ) ) AND ! empty( $btn_style['color_bg_hover'] ) ) {
				echo 'background:' . us_get_color( $btn_style['color_bg'], /* Gradient */ TRUE ) . ';';
			} else {
				echo 'background: transparent;';
			}
			echo '}';

			if ( $with_shop AND us_get_option( 'shop_primary_btn_style' ) == $btn_style['id'] ) {
				echo '.no-touch .woocommerce .button.add_to_cart_button:after,';
			}
			echo '.no-touch .us-btn-style_' . $btn_style['id'] . ':after {';
			echo 'content: ""; position: absolute; top: 0; left: 0; right: 0;';
			if ( $btn_style['hover'] == 'slide' ) {
				echo 'height: 0; transition: height 0.3s;';
			} else {
				echo 'bottom: 0; opacity: 0; transition: opacity 0.3s;';
			}
			echo 'background:' . (
				! empty( $btn_style['color_bg_hover'] )
					? us_get_color( $btn_style['color_bg_hover'], /* Gradient */ TRUE )
					: 'transparent'
				) . ';';
			echo '}';

			if ( $with_shop AND us_get_option( 'shop_primary_btn_style' ) == $btn_style['id'] ) {
				echo '.no-touch .woocommerce .button.add_to_cart_button:hover:after,';
			}
			echo '.no-touch .us-btn-style_' . $btn_style['id'] . ':hover:after {';
			if ( $btn_style['hover'] == 'slide' ) {
				echo 'height: 100%;';
			} else {
				echo 'opacity: 1;';
			}
			echo '}';
		}
	}
}

/* FIELDS STYLE
 ====================================================================================================== */
foreach( us_get_option( 'input_fields' ) as $input_fields ) {

	// Check if the fields has default colors to override them in Rows with other Color Style
	if ( empty( $input_fields['color_bg'] ) OR $input_fields['color_bg'] === 'transparent' ) {
		$_fields_have_no_bg_color = TRUE;
	}
	if ( $input_fields['color_bg'] == '_content_bg_alt' ) {
		$_fields_have_alt_bg_color = TRUE;
	}
	if ( $input_fields['color_border'] == '_content_border' ) {
		$_fields_have_border_color = TRUE;
	}
	if ( $input_fields['color_text'] == '_content_text' ) {
		$_fields_have_text_color = TRUE;
	}

	// Default styles
	echo '.w-filter.state_desktop.style_drop_default .w-filter-item-title,';
	echo '.select2-selection,';
	echo 'select,';
	echo 'textarea,';
	echo 'input:not([type=submit]) {';
	if ( ! empty( $input_fields['font'] ) ) {
		if ( in_array( $input_fields['font'], US_TYPOGRAPHY_TAGS ) ) {
			if ( $input_fields['font'] == 'body' ) {
				$input_fields['font'] = 'var(--font-family)';
			} else {
				$input_fields['font'] = sprintf( 'var(--%s-font-family)', $input_fields['font'] );
			}
		}
		echo sprintf( 'font-family:%s;', $input_fields['font'] );
	}
	echo sprintf( 'font-weight:%s;', $input_fields['font_weight'] );
	echo sprintf( 'letter-spacing:%s;', $input_fields['letter_spacing'] );
	echo sprintf( 'border-radius:%s;', $input_fields['border_radius'] );

	if ( ! empty( $input_fields['color_bg'] ) ) {
		echo sprintf( 'background:%s;', us_get_color( $input_fields['color_bg'], /* Gradient */ TRUE ) );
	}
	if ( ! empty( $input_fields['color_border'] ) ) {
		echo sprintf( 'border-color:%s;', us_get_color( $input_fields['color_border'] ) );
	}
	if ( ! empty( $input_fields['color_text'] ) ) {
		echo sprintf( 'color:%s;', us_get_color( $input_fields['color_text'] ) );
	}
	if ( ! empty( $input_fields['color_shadow'] ) ) {
		$_shadow_inset = ! empty( $input_fields['shadow_inset'] ) ? 'inset' : '';
		echo sprintf(
			'box-shadow: %s %s %s %s %s %s;',
			$input_fields['shadow_offset_h'],
			$input_fields['shadow_offset_v'],
			$input_fields['shadow_blur'],
			$input_fields['shadow_spread'],
			us_get_color( $input_fields['color_shadow'] ),
			$_shadow_inset
		);
	}
	echo '}';

	// On Focus styles
	echo '.w-filter.state_desktop.style_drop_default .w-filter-item-title:focus,';
	echo '.select2-container--open .select2-selection,';
	echo 'select:focus,';
	echo 'textarea:focus,';
	echo 'input:not([type=submit]):focus {';

	if ( ! empty( $input_fields['color_bg_focus'] ) ) {
		echo sprintf( 'background:%s !important;', us_get_color( $input_fields['color_bg_focus'], /* Gradient */ TRUE ) );
	}
	if ( ! empty( $input_fields['color_border_focus'] ) ) {
		echo sprintf( 'border-color:%s !important;', us_get_color( $input_fields['color_border_focus'] ) );
	}
	if ( ! empty( $input_fields['color_text_focus'] ) ) {
		echo sprintf( 'color:%s !important;', us_get_color( $input_fields['color_text_focus'] ) );
	}
	if ( ! empty( $input_fields['color_shadow'] ) OR ! empty( $input_fields['color_shadow_focus'] )	) {

		$_shadow_focus_color = ! empty( $input_fields['color_shadow_focus'] )
			? us_get_color( $input_fields['color_shadow_focus'] )
			: us_get_color( $input_fields['color_shadow'] );
		$_shadow_focus_inset = ! empty( $input_fields['shadow_focus_inset'] ) ? 'inset' : '';

		echo sprintf(
			'box-shadow: %s %s %s %s %s %s;',
			$input_fields['shadow_focus_offset_h'],
			$input_fields['shadow_focus_offset_v'],
			$input_fields['shadow_focus_blur'],
			$input_fields['shadow_focus_spread'],
			$_shadow_focus_color,
			$_shadow_focus_inset
		);
	}
	echo '}';

	if ( ! empty( $input_fields['color_text_focus'] ) ) {
		echo '.w-form-row.focused .w-form-row-field > i {';
		echo sprintf( 'color:%s;', us_get_color( $input_fields['color_text_focus'] ) );
		echo '}';
	}

	// For form label separately
	echo '.w-form-row.move_label .w-form-row-label {';
	echo sprintf( 'font-size:%s;', $input_fields['font_size'] );
	echo sprintf( 'top: calc(%s/2 + %s - 0.7em);', $input_fields['height'], $input_fields['border_width'] );
	echo sprintf( 'margin: 0 %s;', $input_fields['padding'] );
	if ( ! empty( $_fields_have_no_bg_color ) ) {
		echo 'background: var(--color-content-bg-grad);';
	} else {
		echo sprintf( 'background-color:%s;', us_get_color( $input_fields['color_bg'] ) );
	}
	if ( ! empty( $input_fields['color_text'] ) ) {
		echo sprintf( 'color:%s;', us_get_color( $input_fields['color_text'] ) );
	}
	echo '}';
	echo '.w-form-row.with_icon.move_label .w-form-row-label {';
	echo sprintf( 'margin-%s: calc(1.6em + %s);', ( is_rtl() ? 'right' : 'left' ), $input_fields['padding'] );
	echo '}';
}

// Add specific input fields styles for sections with other color styles
if ( ! empty( $_fields_have_no_bg_color ) ) { ?>
.color_alternate .w-form-row.move_label .w-form-row-label {
	background: var(--color-alt-content-bg-grad);
	}
.color_footer-top .w-form-row.move_label .w-form-row-label {
	background: var(--color-subfooter-bg-grad);
	}
.color_footer-bottom .w-form-row.move_label .w-form-row-label {
	background: var(--color-footer-bg-grad);
	}
<?php }

if ( ! empty( $_fields_have_alt_bg_color ) ) { ?>
.color_alternate input:not([type=submit]),
.color_alternate textarea,
.color_alternate select,
.color_alternate .move_label .w-form-row-label {
	background: var(--color-alt-content-bg-alt-grad);
	}
.color_footer-top input:not([type=submit]),
.color_footer-top textarea,
.color_footer-top select,
.color_footer-top .w-form-row.move_label .w-form-row-label {
	background: var(--color-subfooter-bg-alt-grad);
	}
.color_footer-bottom input:not([type=submit]),
.color_footer-bottom textarea,
.color_footer-bottom select,
.color_footer-bottom .w-form-row.move_label .w-form-row-label {
	background: var(--color-footer-bg-alt-grad);
	}
<?php }

if ( ! empty( $_fields_have_border_color ) ) { ?>
.color_alternate input:not([type=submit]),
.color_alternate textarea,
.color_alternate select {
	border-color: var(--color-alt-content-border);
	}
.color_footer-top input:not([type=submit]),
.color_footer-top textarea,
.color_footer-top select {
	border-color: var(--color-subfooter-border);
	}
.color_footer-bottom input:not([type=submit]),
.color_footer-bottom textarea,
.color_footer-bottom select {
	border-color: var(--color-footer-border);
	}
<?php }

if ( ! empty( $_fields_have_text_color ) ) { ?>
.color_alternate input:not([type=submit]),
.color_alternate textarea,
.color_alternate select,
.color_alternate .w-form-row-field > i,
.color_alternate .w-form-row-field:after,
.color_alternate .widget_search form:after,
.color_footer-top input:not([type=submit]),
.color_footer-top textarea,
.color_footer-top select,
.color_footer-top .w-form-row-field > i,
.color_footer-top .w-form-row-field:after,
.color_footer-top .widget_search form:after,
.color_footer-bottom input:not([type=submit]),
.color_footer-bottom textarea,
.color_footer-bottom select,
.color_footer-bottom .w-form-row-field > i,
.color_footer-bottom .w-form-row-field:after,
.color_footer-bottom .widget_search form:after {
	color: inherit;
	}
<?php }

// Output styles for fallback icons
if ( us_get_option( 'optimize_assets' ) ) {
?>
.fa-angle-down:before { content: "\f107" }
.fa-angle-left:before { content: "\f104" }
.fa-angle-right:before { content: "\f105" }
.fa-angle-up:before { content: "\f106" }
.fa-bars:before { content: "\f0c9" }
.fa-check:before { content: "\f00c" }
.fa-comments:before { content: "\f086" }
.fa-copy:before { content: "\f0c5" }
.fa-envelope:before { content: "\f0e0" }
.fa-map-marker-alt:before { content: "\f3c5" }
.fa-mobile:before { content: "\f10b" }
.fa-phone:before { content: "\f095" }
.fa-play:before { content: "\f04b" }
.fa-quote-left:before { content: "\f10d" }
.fa-search-plus:before { content: "\f00e" }
.fa-search:before { content: "\f002" }
.fa-shopping-cart:before { content: "\f07a" }
.fa-star:before { content: "\f005" }
.fa-tags:before { content: "\f02c" }
.fa-times:before { content: "\f00d" }
<?php }

/* Default icon Leaflet URLs */
if ( us_is_asset_used( 'lmaps' ) ) {
	global $us_template_directory_uri;
?>
.leaflet-default-icon-path {
	background-image: url(<?= esc_url( $us_template_directory_uri ) ?>/common/css/vendor/images/marker-icon.png);
	}
<?php }

/* WooCommerce Product gallery settings
 =============================================================================================================================== */
if ( $with_shop AND us_get_option( 'product_gallery' ) == 'slider' ) {

	if ( us_get_option( 'product_gallery_thumbs_pos' ) == 'bottom' ) {
		$cols = (int) us_get_option( 'product_gallery_thumbs_cols', 4 );
		echo '.woocommerce-product-gallery--columns-' . $cols . ' li { width:' . sprintf( '%0.3f', 100 / $cols ) . '%; }';
	} else {
		echo '.woocommerce-product-gallery { display: flex;	}';
		echo '.woocommerce-product-gallery ol {	display: block; order: -1; }';
		echo '.woocommerce-product-gallery ol > li { width:' . us_get_option( 'product_gallery_thumbs_width', '6rem' ) . '; }';
	}

	// Gaps between thumbnails
	if ( $gap_half = (int) us_get_option( 'product_gallery_thumbs_gap', 0 ) / 2 ) {
		if ( us_get_option( 'product_gallery_thumbs_pos' ) == 'bottom' ) {
			echo '.woocommerce-product-gallery ol { margin:' . $gap_half . 'px -' . $gap_half . 'px 0; }';
		} else {
			echo '.woocommerce-product-gallery ol { margin: -' . $gap_half . 'px ' . $gap_half . 'px -' . $gap_half . 'px -' . $gap_half . 'px; }';
			echo '.rtl .woocommerce-product-gallery ol { margin: -' . $gap_half . 'px -' . $gap_half . 'px -' . $gap_half . 'px ' . $gap_half . 'px; }';
		}
		echo '.woocommerce-product-gallery ol > li { padding:' . $gap_half . 'px; }';
	}
}

/* Menu Dropdown Settings
 =============================================================================================================================== */
global $wpdb;

$wpdb_query = 'SELECT posts.ID as ID, meta.meta_value as value FROM ' . $wpdb->posts . ' posts ';
$wpdb_query .= 'RIGHT JOIN ' . $wpdb->postmeta . ' meta on (posts.id = meta.post_id AND meta.meta_key = "us_mega_menu_settings")';
$wpdb_query .= ' WHERE post_type = "nav_menu_item"';
$results = $wpdb->get_results( $wpdb_query, ARRAY_A );

foreach( $results as $result ) {

	$menu_item_id = $result['ID'];
	$settings = unserialize( $result['value'] );
	$dropdown_css_props = '';

	if ( ! isset( $settings['drop_to'] ) ) {

		// Fallback condition for theme versions prior to 6.2 (instead of migration)
		if ( isset( $settings['direction'] ) ) {
			$settings['drop_to'] = ( $settings['direction'] ) ? 'left' : 'right';
		} else {
			$settings['drop_to'] = 'right';
		}
	}

	// Full Width
	if ( $settings['width'] == 'full' ) {
		$dropdown_css_props .= 'left: 0; right: 0;';
		$dropdown_css_props .= 'transform-origin: 50% 0;';

		// Auto or Custom Width
	} else {

		// Center
		if ( $settings['drop_to'] == 'center' ) {
			$dropdown_css_props .= 'left: 50%; right: auto;';

			// Need margin-left for correct centering based on custom width divided by two
			if ( $settings['width'] == 'custom' AND preg_match( '~^(\d*\.?\d*)(.*)$~', $settings['custom_width'], $matches ) ) {
				$dropdown_css_props .= 'margin-left: -' . ( $matches[1] / 2 ) . $matches[2] . ';';
			} else {
				$dropdown_css_props .= 'margin-left: -6rem;';
			}

			// Left
		} elseif ( $settings['drop_to'] == 'left' ) {
			if ( is_rtl() ) {
				$dropdown_css_props .= 'left: 0; right: auto; transform-origin: 0 0;';
			} else {
				$dropdown_css_props .= 'left: auto; right: 0; transform-origin: 100% 0;';
			}
		}
	}

	$dropdown_bg_color = us_get_color( $settings['color_bg'], /* Gradient */ TRUE );
	$dropdown_bg_image = '';

	// Add image properties when image is set
	if ( $dropdown_bg_image_url = wp_get_attachment_image_url( $settings['bg_image'], 'full' ) ) {
		$dropdown_bg_image .= 'url(' . $dropdown_bg_image_url . ') ';
		$dropdown_bg_image .= $settings['bg_image_position'];
		if ( $settings['bg_image_size'] != 'initial' ) {
			$dropdown_bg_image .= '/' . $settings['bg_image_size'];
		}
		$dropdown_bg_image .= ' ';
		$dropdown_bg_image .= $settings['bg_image_repeat'];

		// If the color value contains gradient, add comma for correct appearance
		if ( strpos( $dropdown_bg_color, 'grad' ) !== FALSE ) {
			$dropdown_bg_image .= ',';
		}
	}

	// Output single combined background value
	if ( $dropdown_bg_image != '' OR $dropdown_bg_color != '' ) {
		$dropdown_css_props .= 'background:' . $dropdown_bg_image . ' ' . $dropdown_bg_color . ';';
	}

	if ( $settings['color_text'] != '' ) {
		$dropdown_css_props .= 'color:' . us_get_color( $settings['color_text'] ) . ';';
	}
	if ( $settings['width'] == 'custom' ) {
		$dropdown_css_props .= 'width:' . $settings['custom_width'] . ';';
	}

	// Stretch background to the screen edges
	if ( $settings['width'] == 'full' AND ! empty( $settings['stretch'] ) ) {
		$dropdown_css_props .= '--dropdown-padding: ' . $settings['padding'] . ';';
		$dropdown_css_props .= 'margin: 0 min( -2.5rem, var(--site-content-width) / 2 - 50vw );';
		$dropdown_css_props .= 'padding: var(--dropdown-padding, 0px) max( 2.5rem, 50vw - var(--site-content-width) / 2 );';
	} elseif ( (int) $settings['padding'] != 0 ) {
		$dropdown_css_props .= '--dropdown-padding: ' . $settings['padding'] . ';';
		$dropdown_css_props .= 'padding: var(--dropdown-padding, 0px);';
	}

	// Output dropdown CSS if it's not empty
	if ( ! empty( $dropdown_css_props ) ) {
		echo '.header_hor .w-nav.type_desktop .menu-item-' . $menu_item_id . ' .w-nav-list.level_2 {';
		echo strip_tags( $dropdown_css_props );
		echo '}';
	}

	// Make menu item static in 2 cases
	if ( $settings['width'] == 'full' OR ( isset( $settings['drop_from'] ) AND $settings['drop_from'] == 'header' ) ) {
		echo '.header_hor .w-nav.type_desktop .menu-item-' . $menu_item_id . ' { position: static; }';
	}

}

// Remove filter for protocols removal from URLs for better compatibility with caching plugins and services
if ( ! us_get_option( 'keep_url_protocol', 1 ) ) {
	remove_filter( 'clean_url', 'us_remove_url_protocol', 10 );
}
