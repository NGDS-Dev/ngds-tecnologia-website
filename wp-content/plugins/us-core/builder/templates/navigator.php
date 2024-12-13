<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' ); ?>

<!-- Begin right sidebar -->
<aside id="usb-navigator" class="usb-navigator">
	<header class="usb-navigator-header">
		<button class="usb_action_navigator_expand_all" title="<?php esc_attr_e( __( 'Expand/Collapse All', 'us' ) ) ?>">
			<i class="fas fa-chevron-circle-down"></i>
		</button>
		<div class="usb-navigator-header-title"><?php echo strip_tags( __( 'Navigator', 'us' ) ) ?></div>
		<button class="usb_action_navigator_hide ui-icon_close" title="<?php esc_attr_e( us_translate( 'Close' ) ) ?>"></button>
	</header>
	<div class="usb-navigator-body"></div>
	<!-- Begin navigator item template -->
	<script type="text/html" id="usb-tmpl-navigator-item">
		<div class="usb-navigator-item" data-for="{%usbid%}">
			<div class="usb-navigator-item-header">
				<i class="usb_action_navigator_expand"></i>
				<div class="usb-navigator-item-title usb-elm-has-icon" data-type="{%elm_type%}">
					<i class="{%elm_icon%}"></i>
					<span>{%elm_title%}</span>
					<span class="for_attr_id">{%attr_id%}</span>
				</div>
				<div class="usb-navigator-item-actions">
					<button class="usb_action_navigator_duplicate_elm ui-icon_duplicate" title="<?php esc_attr_e( __( 'Duplicate', 'us' ) ) ?>"></button>
					<button class="usb_action_navigator_remove_elm ui-icon_delete" title="<?php esc_attr_e( us_translate( 'Delete' ) ) ?>"></button>
				</div>
			</div>
		</div>
	</script>
	<!-- End navigator item template -->
</aside>
<!-- End right sidebar -->
