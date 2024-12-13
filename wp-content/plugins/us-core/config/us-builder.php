<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Configurations for USBuilder
 */

/**
 * Configuring fields for the page settings screen
 *
 * @var array
 */
$page_fields = array(
	'params' => array(
		'post_title' => array(
			'title' => us_translate( 'Title' ),
			'type' => 'text',
			'std' => '',
		), // post_status, post_name etc.
	),
);

/**
 * Different templates that are required for the USBuilder to work on the frontend side
 *
 * @var array
 */
$templates = array(
	'vc_row' => '[vc_row usbid="{%vc_row%}"][vc_column usbid="{%vc_column%}"]{%content%}[/vc_column][/vc_row]',
);

// VC TTA (Tabs/Tour/Accordion) Section ( The sections that are created with a new element )
$vc_tta_section = '[vc_tta_section title="{%title_1%}" usbid="{%vc_tta_section_1%}"]';
$vc_tta_section .= '[vc_column_text usbid="{%vc_column_text%}"]{%vc_column_text_content%}[/vc_column_text]';
$vc_tta_section .= '[/vc_tta_section]';
$vc_tta_section .= '[vc_tta_section title="{%title_2%}" usbid="{%vc_tta_section_2%}"][/vc_tta_section]';
$templates['vc_tta_section'] = $vc_tta_section;

/**
 * Deferred assets for the admin part of the builder
 *
 * @var array
 */
$deferred_assets = array(
	// A set of minimal assets for initializing a code editor (Order is important here)
	'codeEditor' => array(
		'wp-codemirror',
		'csslint',
		'esprima',
		'code-editor',
	),
);

/**
 * List of usof field types for which to use throttle
 * Note: Types of fields for which a large interval of recording changes in history is used,
 * this is necessary for fields that have a high frequency of changes, for example,
 * when entering text in a text field.
 *
 * @var array
 */
$use_throttle_for_fields = array(
	'editor', 'color', 'text', 'textarea',
);

/**
 * List of usof field types for which the update interval is used
 * Note: Field types that use spacing when the preview refreshes are required
 * for fields that have a high rate of change, such as when choosing a color.
 *
 * @var array
 */
$use_long_update_for_fields = array(
	'color', 'design_options',
);

/**
 * Elements outside of the main container, such as the header, footer, or sidebar
 *
 * @var array
 */
$elms_outside_main_container = array(
	'header', 'footer',
);

/**
 * Builder JS extensions (These are also the JS files names)
 * Note: Order is important for now, we'll get rid of it later!
 *
 * @var array
 */
$js_extensions = array(
	// Common extensions
	'common/usbcore', // USBCore - Auxiliary functions for the builder and his extensions
	'common/url-manager', // URLManager - Functionality for interaction with the address bar
	'common/notify', // Notify - Notification system
	'common/panel', // Panel - Basic panel functionality (left sidebar)
	'common/preview', // Preview - Functionality of the preview and responsive screens area
	'common/css-generator', // CSSGenerator - Functionality for generating css based on collections
	'common/fonts', // Fonts - Functionality for working with font settings

	// Builder extensions
	'builder/builder', // Page Builder - Builder for edit, remove and add shortcodes to a page
	'builder/builder.panel', // Builder Panel - Functionality of the main builder panel (left sidebar)
	'builder/navigator', // Navigator - Shortcode navigator functionality in the page content (right sidebar)
	'builder/templates', // Templates - Functionality of importing and adding rows from provided templates
	'builder/page', // Page - Functionality for customizing the page, styles or metadata of the edited page
	'builder/history', // History - Functionality for keeping a history of changes on the page, which allows you to undo or restore changes

	// Site extensions
	'site/settings', // Site Settings - Site settings functionality (Theme Settings)
);

/**
 * @var array
 */
return array(
	'deferred_assets' => $deferred_assets,
	'page_fields' => $page_fields,
	'templates' => $templates,
	'elms_outside_main_container' => $elms_outside_main_container,

	// `<link id="{fonts_id}"...>` element ID to include the Google Font
	'fonts_id' => 'us-fonts-css',

	// Builder JS extensions
	'js_extensions' => $js_extensions,

	// Undo/Redo settings
	'use_long_update_for_fields' => $use_long_update_for_fields,
	'use_throttle_for_fields' => $use_throttle_for_fields,

	// Maximum size of changes in the data history
	'max_data_history' => 100,

	// Minimum preview screen height (in pixels)
	'min_screen_height' => 320,

	// Minimum preview screen width (in pixels)
	'min_screen_width' => 320,

	// Maximum preview screen width (in pixels)
	'max_screen_width' => 2560,

	// Since we introduced a new type of root `container` at the level of shortcodes and builder,
	// then we will add a rule for it that should be ignored when adding a new element
	'as_parent_container_only' => 'vc_row,import_template',

	// Elements with more than one node in the result must have a common wrap
	// Example: `<div class="one">...</div><div class="two">...</div>`
	'with_wrappers' => array( 'us_grid', 'us_carousel', 'us_product_list', 'us_term_list', 'us_user_list' ),
);
