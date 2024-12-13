<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );
/**
 * The body of the page builder
 *
 * @var array $elms_categories
 * @var array $fieldsets
 * @var string $post_type
 */

// Checking required variables
$elms_categories = isset( $elms_categories ) ? $elms_categories : array();
$fieldsets = isset( $fieldsets ) ? $fieldsets : array();
$post_id = (int) usb_get_post_id();
$post_type = isset( $post_type ) ? $post_type : '';
$section_templates_included = us_get_option( 'section_templates', /* default */1 );

//List of assigned classes to various sections
$classes = array();

// Section output control by param active
$classes['tab_elements'] = $classes['add_elms'] = '';
if ( usb_get_active_panel_name() == 'add_elms' ) {
	$classes['add_elms'] = 'active';
	$classes['tab_elements'] = (
		! usb_post_editing_is_locked()
			? 'hidden'
			: ''
	);
}

// Hidden panel sections
foreach( array( 'paste_row', 'page_custom_css', 'page_settings' ) as $panel_name ) {
	$classes[ $panel_name ] = usb_get_active_panel_name() != $panel_name ? 'hidden' : '';
}

?>
<!-- Templates Transit -->
<div class="usb-template-transit hidden">
	<i class="fas fa-border-all"></i>
	<span>Template section</span>
</div>

<!-- Add Items -->
<div class="usb-panel-tab-elements usof-tabs <?php esc_attr_e( $classes['tab_elements'] )?>">
	<?php if ( $section_templates_included ): ?>
	<div class="usof-tabs-list">
		<div class="usof-tabs-item usb_action_tab_add_elms <?php esc_attr_e( $classes['add_elms'] ) ?>"><?php esc_attr_e( __( 'Elements', 'us' ) ) ?></div>
		<div class="usof-tabs-item usb_action_show_templates"><?php esc_attr_e( us_translate( 'Templates' ) ) ?></div>
	</div>
	<?php endif; ?>
	<div class="usof-tabs-sections">
		<div class="usof-tabs-section <?php esc_attr_e( $classes['add_elms'] ) ?>">
			<!-- Begin Add Element List -->
			<div class="usb-panel-elms">
				<div class="usb-panel-elms-search">
					<input type="text" name="search" autocomplete="off" placeholder="<?php esc_attr_e( us_translate( 'Search' ) ) ?>">
					<i class="ui-icon_close usb_action_panel_reset_search hidden" title="<?php esc_attr_e( __( 'Reset', 'us' ) ) ?>"></i>
				</div>
				<div class="usb-panel-elms-search-noresult hidden"><?php esc_attr_e( us_translate( 'No results found.' ) ) ?></div>
				<?php foreach ( $elms_categories as $category => $elms ): ?>
					<?php
					// Category title
					$title = ! empty( $category ) ? $category : us_translate( 'General' );
					echo '<h2 class="usb-panel-elms-header">' . strip_tags( $title ) . '</h2>';

					// Category elements
					$output = '<div class="usb-panel-elms-list">';
					foreach ( $elms as $type => $elm ) {
						$elm_atts = array(
							'class' => 'usb-panel-elms-item usb-elm-has-icon',
							'data-title' => strip_tags( $elm['title'] ),
							'data-type' => (string) $type,
						);
						if ( ! empty( $elm['is_container'] ) ) {
							$elm_atts['data-isContainer'] = TRUE;
						}

						// Hide specific elements
						if (
							! empty( $elm['hide_on_adding_list'] )
							OR (
								! empty( $elm['show_for_post_types'] )
								AND ! in_array( $post_type, (array) $elm['show_for_post_types'] )
							)
							OR (
								! empty( $elm[ 'hide_for_post_ids' ] )
								AND in_array( $post_id, (array) $elm['hide_for_post_ids'] )
							)
						) {
							$elm_atts['class'] .= ' hidden';

						} elseif( ! empty( $elm_atts['data-title'] ) ) {
							$elm_atts['data-search-text'] = us_strtolower( $elm_atts['data-title'] );
						}
						$output .= '<div' . us_implode_atts( $elm_atts ) . '>';
						$output .= '<i class="' . $elm['icon'] . '"></i>';
						$output .= '</div>';
					}
					$output .= '</div>';
					echo $output;
					?>
				<?php endforeach; ?>
			</div>
			<!-- End Add Element List -->
		</div>
		<?php if ( $section_templates_included ): ?>
		<div class="usof-tabs-section">
			<!-- Begin Templates List -->
			<div id="usb-templates" class="usb-templates">
				<div class="usb-templates-error"><?php echo strip_tags( us_translate( 'No results found.' ) ) ?></div>
			</div>
			<!-- End Templates List -->
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- Elements Fieldsets -->
<div id="usb-tmpl-fieldsets" class="hidden">
	<?php foreach ( $fieldsets as $fieldset_name => $fieldset ): ?>
		<form class="usb-panel-fieldset" data-name="<?php esc_attr_e( $fieldset_name ) ?>">
			<?php us_load_template(
				'usof/templates/edit_form', array(
					'type' => $fieldset_name,
					'params' => isset( $fieldset['params'] ) ? $fieldset['params'] : array(),
					'context' => 'shortcode'
				)
			) ?>
		</form>
	<?php endforeach; ?>
</div>

<!-- Paste Row/Section -->
<div class="usb-panel-import-content usof-container inited <?php esc_attr_e( $classes['paste_row'] ) ?>">
	<textarea placeholder="[vc_row][vc_column] ... [/vc_column][/vc_row]"></textarea>
	<button class="usof-button usb_action_save_import_content disabled" disabled>
		<span><?php esc_attr_e( __( 'Append Section', 'us' ) ) ?></span>
		<span class="usof-preloader"></span>
	</button>
</div>

<!-- Page Custom CSS -->
<div class="usb-panel-page-custom-css usof-container inited <?php esc_attr_e( $classes['page_custom_css'] ) ?>">
	<div class="type_css" data-name="<?php esc_attr_e( USBuilder::KEY_CUSTOM_CSS ) ?>">
		<?php us_load_template(
			'usof/templates/fields/css', array(
				'name' => USBuilder::KEY_CUSTOM_CSS, // Meta key for post custom css
				'value' => '', // NOTE: The value is empty because the data should be loaded from the preview frame.
			)
		) ?>
	</div>
</div>

<!-- Page Settings -->
<div class="usb-panel-page-settings usof-container inited <?php esc_attr_e( $classes['page_settings'] ) ?>">
	<!-- Begin page fields -->
	<?php us_load_template(
		'usof/templates/edit_form', array(
			'context' => 'us_builder',
			'params' => us_config( 'us-builder.page_fields.params', array() ),
			'type' => 'page_fields',
			'values' => array(), // Values will be set on the JS side after loading the iframe.
		)
	) ?>
	<!-- End page fields -->
	<!-- Begin page metadata -->
	<div class="usb-panel-page-meta">
		<?php foreach ( (array) us_config( 'meta-boxes', array() ) as $metabox_config ): ?>
			<?php
			if (
				! us_arr_path( $metabox_config, 'usb_context' )
				OR ! in_array( $post_type, (array) us_arr_path( $metabox_config, 'post_types', array() ) )
			) {
				continue;
			}
			?>
			<div class="usb-panel-page-meta-title"><?php esc_html_e( $metabox_config['title'] ) ?></div>
			<?php us_load_template(
				'usof/templates/edit_form', array(
					'params' => us_arr_path( $metabox_config, 'fields', array() ),
					'type' => us_arr_path( $metabox_config, 'id', '' ),
					'values' => array(), // Values will be set on the JS side after loading the iframe.
				)
			) ?>
		<?php endforeach; ?>
	</div>
	<!-- End page metadata -->
</div>
