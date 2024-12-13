<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );
/**
 * @var array $usb_config Array of configs for live edit
 * @var string $ajaxurl Link for AJAX requests
 * @var string $body_class Installed classes for the page body
 * @var string $current_preview_url Current preview URL
 * @var string $edit_post_link Link to the post editing in the admin area
 * @var string $post_link Link to the post on frontend
 */

// Get the current post ID
$post_id = usb_get_post_id();

if ( empty( $post_type ) OR in_array( $post_type, array( 'us_page_block', 'us_content_template' ) ) ) {
	$post_has_frontend_view = FALSE;
} else {
	$post_has_frontend_view = TRUE;
}

?>
<!DOCTYPE HTML>
<html dir="<?php echo( is_rtl() ? 'rtl' : 'ltr' ) ?>" <?php language_attributes( 'html' ) ?>>
<head>
	<title><?php echo $title ?></title>
	<meta charset="<?php bloginfo( 'charset' ) ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<?php wp_print_styles() ?>
	<script type="text/javascript">
		// Link to get data via AJAX for USOF
		var ajaxurl = '<?php esc_attr_e( $ajaxurl ) ?>';
		// Text translations for USBuilder
		window.$usbdata = window.$usbdata || {}; // Single space for data
		window.$usbdata.post_id = <?php echo $post_id ?>; // for wp services
		window.$usbdata.textTranslations = <?php echo json_encode( $text_translations ) ?>;
	</script>
</head>
<body class="<?php echo $body_class ?>">
<div id="usb-wrapper" class="usb-wrapper"<?php echo us_pass_data_to_js( $usb_config ) ?>>
	<!-- Begin left sidebar -->
	<aside id="usb-panel" class="usb-panel wp-core-ui">
		<div class="usb-panel-switcher ui-icon_left" title="<?php _e( 'Hide/Show panel', 'us' ) ?>"></div>

		<!-- Panel preloader -->
		<span id="usb-panel-body-preloader" class="usof-preloader"></span>

		<!-- Panel Header -->
		<header class="usb-panel-header">

			<!-- Menu -->
			<div class="usb-panel-header-menu">
				<button class="icon_menu" title="<?php esc_attr_e( us_translate( 'Menu' ) )?>">
					<span></span>
				</button>
				<div class="usb-panel-header-menu-list">
					<?php
					$menu_items = array(
						array(
							'label' => us_translate( 'View Page' ),
							'atts'  => array(
								'href' => $post_link,
								'target' => '_blank',
							),
							'place_if' => $post_has_frontend_view,
						),
						array(
							'label' => us_translate( 'Visit Site' ),
							'atts'  => array(
								'href' => home_url( '/' ),
								'target' => '_blank',
							),
							'place_if' => ! $post_has_frontend_view,
						),
						array(
							'label' => __( 'Go to Theme Options', 'us' ),
							'atts'  => array(
								'href' => admin_url( 'admin.php?page=us-theme-options' ),
								'target' => '_blank',
							),
							'place_if' => current_user_can( 'manage_options' ),
						),
						array(
							'label' => us_translate( 'Undo' ),
							'atts'  => array(
								'class' => 'usb_action_undo disabled',
								'href' => 'javascript:void(0)',
							),
							'html_after_label' => '<span data-macos-shortcuts="Command+Z">Ctrl+Z</span>',
							'place_if' => ! usb_is_site_settings(),
						),
						array(
							'label' => us_translate( 'Redo' ),
							'atts'  => array(
								'class' => 'usb_action_redo disabled',
								'href' => 'javascript:void(0)',
							),
							'html_after_label' => '<span data-macos-shortcuts="Command+Shift+Z">Ctrl+Shift+Z</span>',
							'place_if' => ! usb_is_site_settings(),
						),
						array(
							'label' => __( 'Site Settings', 'us' ),
							'atts'  => array(
								'class' => 'usb_action_to_site_settings',
								'href' => usb_get_edit_link( $post_id, array( 'action' => US_BUILDER_SITE_SETTINGS_SLUG ) ),
							),
							'place_if' => ( ! usb_is_site_settings() AND current_user_can( 'manage_options' ) ),
						),
						array(
							'label' => __( 'Edit Page Live', 'us' ),
							'atts'  => array(
								'class' => 'usb_action_open_builder',
								'href' => usb_get_edit_link( $post_id, array( 'action' => US_BUILDER_SLUG ) ),
							),
							'place_if' => ( $live_builder_is_enabled AND usb_is_site_settings() AND ! empty( $post_type ) ),
						),
						array(
							'label' => __( 'Edit Page in Backend', 'us' ),
							'atts'  => array(
								'href' => $edit_post_link,
							),
							'place_if' => ! empty( $post_type ),
						),
						array(
							'label' => __( 'Paste Row/Section', 'us' ),
							'atts'  => array(
								'class' => 'usb_action_show_import_content',
								'href' => 'javascript:void(0)',
							),
							'place_if' => ! usb_is_site_settings(),
						),
						array(
							'label' => __( 'Exit to dashboard', 'us' ),
							'atts'  => array(
								'href' => admin_url(),
							),
						),
					);
					foreach ( $menu_items as $menu_item ) {
						if ( isset( $menu_item['place_if'] ) AND ! $menu_item['place_if'] ) {
							continue;
						}
						echo '<a' . us_implode_atts( $menu_item['atts'] ) . '>';
						echo '<span>' . strip_tags( $menu_item['label'] ) . '</span>';
						if ( isset( $menu_item['html_after_label'] ) ) {
							echo $menu_item['html_after_label'];
						}
						echo '</a>';
					}
					?>
				</div>
			</div>

			<!-- Panel header title -->
			<div class="usb-panel-header-title">
				<?php echo apply_filters( 'usb_get_panel_title', /* default */__( 'Add element', 'us' ) ) ?>
			</div>

			<!-- Button "Add Elements" -->
			<?php
				$add_elms_button_atts = array(
					'class' => 'usb_action_show_add_elms',
					'title' => __( 'Add element', 'us' ),
				);
				if ( usb_is_site_settings() ) {
					$add_elms_button_atts['class'] .= ' hidden';
				}
			?>
			<button <?php echo us_implode_atts( $add_elms_button_atts ) ?>>
				<span class="ui-icon_add"></span>
			</button>

			<!-- Button "Go back" -->
			<?php
				$go_back_button_atts = array(
					'title' => us_translate( 'Go back' ),
					'class' => 'usb_action_go_to_back',
				);
				if ( ! usb_is_site_settings() ) {
					$go_back_button_atts['class'] .= ' hidden';
				}
				if ( ! isset( $_GET['group'] ) ) {
					$go_back_button_atts['class'] .= ' disabled';
				}
			?>
			<button <?php echo us_implode_atts( $go_back_button_atts ) ?>>
				<span class="fas fa-arrow-left"></span>
			</button>
		</header>
		<div class="usb-panel-body">
			<!-- Messages Panel -->
			<div class="usb-panel-messages hidden"></div>

			<?php echo apply_filters( 'usb_get_panel_content', /* default */'' ) ?>
		</div>
		<footer class="usb-panel-footer">
			<button class="usb_action_show_page_settings ui-icon_settings" title="<?php esc_attr_e( __( 'Page Settings', 'us' ) ) ?>"></button>
			<button class="usb_action_show_page_custom_css ui-icon_css3" title="<?php esc_attr_e( __( 'Page Custom CSS', 'us' ) ) ?>"></button>
			<button class="usb_action_switch_toolbar ui-icon_devices" title="<?php esc_attr_e( __( 'Responsive', 'us' ) ) ?>"></button>
			<button class="usb_action_switch_navigator disabled" title="<?php esc_attr_e( us_translate( 'Navigator' ) ) ?>">
				<span class="fas fa-layer-group"></span>
			</button>
			<?php if ( $post_id AND $post_has_frontend_view ): ?>
			<!-- Begin data for create revision and show a preview page -->
			<form action="<?php echo admin_url( 'post.php' ) ?>" method="post" id="wp-preview" target="wp-preview-<?php echo $post_id ?>">
				<textarea class="hidden" name="post_content"></textarea>
				<input type="hidden" name="post_ID" value="<?php echo $post_id ?>">
				<input type="hidden" name="wp-preview" value="dopreview">
				<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'update-post_' . $post_id ) ?>">
				<!-- Begin post meta data -->
				<textarea class="hidden" name="<?php esc_attr_e( USBuilder::KEY_CUSTOM_CSS ) ?>"></textarea>
				<!-- End post meta data -->
				<button type="submit" class="ui-icon_eye" title="<?php esc_attr_e( us_translate( 'Preview Changes' ) ) ?>"></button>
			</form>
			<!-- End data for create revision and show a preview page -->
			<?php endif; ?>
			<button class="usb_action_save_changes type_save disabled" disabled>
				<span><?php echo strip_tags( us_translate( 'Update' ) ) ?></span>
				<span class="usof-preloader"></span>
			</button>
		</footer>
		<!-- Notification Prototype -->
		<div class="usb-notification hidden">
			<span></span>
			<i class="ui-icon_close usb_action_notification_close" title="<?php esc_attr_e( us_translate( 'Close' ) ) ?>"></i>
		</div>
	</aside>
	<!-- End left sidebar -->
	<main id="usb-preview" class="usb-preview">
		<!-- Responsive Toolbar -->
		<div class="usb-preview-toolbar">
			<div class="usof-responsive-controls">
				<?php echo usof_get_responsive_buttons() ?>
				<!-- Begin responsive screen sizes -->
				<div class="usof-responsive-sizes">
					<input <?php echo us_implode_atts( array(
						'type' => 'number',
						'name' => 'screenWidth', // here the correct field names are important!
						'placeholder' => 'auto',
						'disabled' => '',
						'title' => us_translate( 'Screen Width' ),
						'min' => us_arr_path( $usb_settings, 'preview.minWidth', /* default */320 ),
					) ) ?>>
					<span>×</span>
					<input <?php echo us_implode_atts( array(
						'type' => 'number',
						'name' => 'screenHeight', // here the correct field names are important!
						'placeholder' => 'auto',
						'disabled' => '',
						'title' => us_translate( 'Screen Height' ),
						'min' => us_arr_path( $usb_settings, 'preview.minHeight', /* default */320 ),
					) ) ?>>
				</div>
				<!-- End responsive screen sizes -->
			</div>
			<button class="ui-icon_close usb_action_hide_toolbar" title="<?php esc_attr_e( us_translate( 'Close' ) ) ?>"></button>
		</div>
		<!-- Preview Wrapper -->
		<div class="usb-preview-wrapper">
			<!-- Begin preview screen-->
			<div class="usb-preview-screen">
				<div class="usb-preview-screen-wrapper">
					<div class="usb-preview-resize-control left" data-resize-control="left"></div>
					<iframe <?php echo usb_post_editing_is_locked() ? '' : /* preload */'data-' ?>src="<?php esc_attr_e( $current_preview_url ) ?>"></iframe>
					<div class="usb-preview-resize-control right" data-resize-control="right"></div>
				</div>
				<div class="usb-preview-resize-control bottom" data-resize-control="bottom"></div>
			</div>
			<!-- End preview screen -->
		</div>
	</main>
	<?php us_load_template( 'builder/templates/navigator' ) ?>
</div>
<!-- Begin scritps -->
<?php do_action( 'usb_admin_footer_scripts' ) ?>
</body>
</html>
