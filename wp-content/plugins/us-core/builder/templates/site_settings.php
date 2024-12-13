<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );
/**
 * The body of the site settings panel
 */

$classes = array(
	'settings_menu' => isset( $_GET['group'] ) ? ' hidden' : '',
);

?>
<!-- Site Settings -->
<div id="usb-site-settings" class="usb-panel-site-settings">

	<!-- General Menu -->
	<div class="usb-panel-site-settings-menu <?php esc_attr_e( $classes['settings_menu'] )?>">
		<?php foreach( us_config( 'live-options' ) as $group_id => $group ): ?>
			<?php if ( empty( $group['fields'] ) ) continue; ?>
			<div data-group-id="<?php esc_attr_e( $group_id ) ?>">
				<?php $group_icon_uri = US_CORE_URI . '/admin/img/' . $group_id; ?>
				<img src="<?= $group_icon_uri ?>.png" srcset="<?= $group_icon_uri ?>-2x.png 2x" alt="">
				<span><?php echo us_arr_path( $group, 'title', 'Title' ); ?></span>
			</div>
		<?php endforeach ?>
	</div>

	<!-- Export Fieldsets -->
	<?php $fieldsets = array();
		foreach( us_config( 'live-options' ) as $group_id => $group ) {
			if ( ! empty( $group['fields'] ) ) {

				// Set default typography options
				foreach ( $group['fields'] as $key => &$fields ) {
					if ( us_arr_path( $fields, 'type' ) != 'typography_options' ) {
						continue;
					}
					// Responsive options do not support arrays, but only work with strings,
					// so we will convert the default value to the desired format.
					// Note: The string format was not chosen by chance, but to support storing
					// data in a string format, for example, shortcode, WPBakery, exchange
					// through input fields as on the post page
					$fields['std'] = us_json_encode( usof_defaults( $key ) );
				}
				unset( $fields );

				$fieldset = us_get_template(
					'usof/templates/edit_form', array(
						'type' => $group_id,
						'params' => $group['fields'],
					)
				);
				$fieldsets[ $group_id ] = '<form class="usb-panel-fieldset">' . $fieldset . '</form>';
			}
		}
	?>
	<div id="usb-site-settings-fieldsets" class="hidden"<?php echo us_pass_data_to_js( $fieldsets ) ?>></div>
</div>
