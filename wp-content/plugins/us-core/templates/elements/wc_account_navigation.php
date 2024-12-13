<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );

/**
 * Output Account Navigation
 * Note: All classes and key elements from WooCommerce are retained
 *
 * @see https://github.com/woocommerce/woocommerce/blob/5.8.0/templates/myaccount/navigation.php
 */

if (
	! class_exists( 'woocommerce' )
	OR ! is_user_logged_in()
) {
	return;
}

$_atts['class'] = 'w-menu woocommerce-MyAccount-navigation';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['class'] .= ' layout_' . $layout;
$_atts['class'] .= ( $spread ) ? ' spread' : '';

$_atts['style'] = '';
$css_styles = '';
if ( $us_elm_context == 'shortcode' ) {
	$responsive_width = trim( (string) $responsive_width );

	$_atts['class'] .= ' style_' . $main_style;
	$_atts['class'] .= empty( $responsive_width ) ? ' not_responsive' : '';

	// Needs to override alignment on mobiles
	if ( in_array( 'mobiles', us_design_options_has_property( $css, 'text-align' ) ) ) {
		$_atts['class'] .= ' has_text_align_on_mobiles';
	}

	// Generate unique ID for US builder preview
	$us_menu_id = us_uniqid();

	$_atts['class'] .= ' us_menu_' . $us_menu_id;

	// Add inline CSS vars
	if ( ! in_array( $main_gap, array( '', '0', '0em', '0px' ) ) ) {
		$_atts['style'] .= '--main-gap:' . $main_gap . ';';
	}
	if ( ! in_array( $main_ver_indent, array( '', '0', '0em', '0px' ) ) ) {
		$_atts['style'] .= '--main-ver-indent:' . $main_ver_indent . ';';
	}
	if ( ! in_array( $main_hor_indent, array( '', '0', '0em', '0px' ) ) ) {
		$_atts['style'] .= '--main-hor-indent:' . $main_hor_indent . ';';
	}

	// Main Items colors
	if ( $main_color_bg = us_get_color( $main_color_bg, /* Gradient */ TRUE ) AND $main_style == 'blocks' ) {
		$_atts['style'] .= '--main-bg-color:' . $main_color_bg . ';';
	}
	if ( $main_color_text = us_get_color( $main_color_text ) ) {
		$_atts['style'] .= '--main-color:' . $main_color_text . ';';
	}
	if ( $main_color_bg_hover = us_get_color( $main_color_bg_hover, /* Gradient */ TRUE ) AND $main_style == 'blocks' ) {
		$_atts['style'] .= '--main-hover-bg-color:' . $main_color_bg_hover . ';';
	}
	if ( $main_color_text_hover = us_get_color( $main_color_text_hover ) ) {
		$_atts['style'] .= '--main-hover-color:' . $main_color_text_hover . ';';
	}
	if ( $main_color_bg_active = us_get_color( $main_color_bg_active, /* Gradient */ TRUE ) AND $main_style == 'blocks' ) {
		$_atts['style'] .= '--main-active-bg-color:' . $main_color_bg_active . ';';
	}
	if ( $main_color_text_active = us_get_color( $main_color_text_active ) ) {
		$_atts['style'] .= '--main-active-color:' . $main_color_text_active . ';';
	}

	// Switch horizontal to vertical at screens below defined width
	if ( ! empty( $responsive_width ) ) {
		$css_styles .= '@media ( max-width:' . $responsive_width . ' ) {';
		$css_styles .= '.us_menu_' . $us_menu_id . ' .menu { display: block !important; }';
		$css_styles .= '.us_menu_' . $us_menu_id . ' .menu > li { margin: 0 0 var(--main-gap,' . $main_gap . ') !important; }';
		$css_styles .= '}';
	}
}

if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

do_action( 'woocommerce_before_account_navigation' ); ?>

<nav<?php echo us_implode_atts( $_atts ); ?>>
	<?php
	if ( ! empty( $css_styles ) ) {
		echo '<style>' . us_minify_css( $css_styles ) . '</style>';
	}
	?>
	<ul class="menu">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) { ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
			</li>
		<?php } ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
