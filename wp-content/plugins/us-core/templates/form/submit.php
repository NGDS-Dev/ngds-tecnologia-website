<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output a form's hidden field
 *
 * @var $name                string Field name
 * @var $classes             string Additional field classes
 * @var $title               string Submit button title
 * @var $btn_classes         string Additional button classes
 * @var $btn_inner_css       string Button inner css
 * @var $btn_size_mobiles    string Button Size on Mobiles
 *
 * @action Before the template: 'us_before_template:templates/form/submit'
 * @action After the template: 'us_after_template:templates/form/submit'
 * @filter Template variables: 'us_template_vars:templates/form/submit'
 */

$title = ! empty( $title ) ? $title : us_translate( 'Submit' );
$icon = isset( $icon ) ? us_prepare_icon_tag( $icon ) : '';
$icon_pos = isset( $icon_pos ) ? $icon_pos : 'left';

$_atts['class'] = 'w-form-row';
$_atts['class'] .= ' for_' . $type;
if ( ! empty( $class ) ) {
	$_atts['class'] .= ' ' . $class;
}

// Apply filter to button label
$title = us_replace_dynamic_value( $title );

$btn_atts = array(
	'class' => 'w-btn',
	'aria-label' => $title,
	'type' => $type,
);
if ( ! empty( $btn_classes ) ) {
	$btn_atts['class'] .= ' ' . $btn_classes;
}
if ( ! empty( $btn_inner_css ) ) {
	$btn_atts['style'] = $btn_inner_css;
}

// Add size on mobiles as inline CSS var
if ( ! empty( $btn_size_mobiles ) ) {
	$_atts['style'] = '--btn-size-mobiles:' . $btn_size_mobiles . ';';
}

// Swap icon position for RTL
if ( is_rtl() ) {
	$icon_pos = ( $icon_pos == 'left' ) ? 'right' : 'left';
}

$after_btn_html = isset( $after_btn_html ) ? $after_btn_html : '';

?>
<div<?= us_implode_atts( $_atts ) ?>>
	<button<?= us_implode_atts( $btn_atts ) ?>>
		<span class="g-preloader type_1"></span>
		<?= ( $icon_pos == 'left' ) ? $icon : ''; ?>
		<span class="w-btn-label"><?= strip_tags( $title, '<br>' ) ?></span>
		<?= ( $icon_pos == 'right' ) ? $icon : ''; ?>
	</button>
	<?= $after_btn_html ?>
</div>
