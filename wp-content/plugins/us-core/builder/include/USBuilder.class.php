<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Main class of US Builder
 */
final class USBuilder {

	/**
	 * Main container
	 *
	 * @var string
	 */
	const MAIN_CONTAINER = 'container';

	/**
	 * Key for storing custom styles
	 *
	 * @var string
	 */
	const KEY_CUSTOM_CSS = 'usb_post_custom_css';

	/**
	 * Css classes for the body element
	 *
	 * @var array
	 */
	private $_body_classes = array( 'us-builder', US_THEMENAME ); // Theme name is used for UI icons

	/**
	 * @access public
	 */
	function __construct() {
		global $wpdb;

		if ( is_admin() ) {

			// Check if the current user has a certain ability to edit
			if (
				! current_user_can( 'manage_options' )
				AND usb_is_site_settings()
			) {
				return;
			}

			// On the page builder we initialize all the necessary handlers and functionality
			if ( usb_is_builder_page() ) {

				// Get the id of the post being edited
				$post_id = usb_get_post_id();

				// Get minimal information about a record
				global $post; // force WP_Post object
				$post = get_post( $post_id );

				// Go to the home page and edit the "Site Settings"
				if (
					! wp_doing_ajax()
					AND ! usb_is_site_settings()
					AND (
						! us_get_option( 'live_builder' )
						OR ! $post_id
					)
				) {
					if ( ! $post_id OR is_null( $post ) ) {
						$post_id = (int) get_option( 'page_on_front' );
					}
					wp_redirect( usb_get_edit_link( $post_id, array( 'action' => US_BUILDER_SITE_SETTINGS_SLUG ) ) );
					exit;
				}

				// Post unlock link handler in the builder
				if ( ! empty( $post_id ) AND ! empty( $_GET['get-post-lock'] ) ) {
					return add_action( 'admin_init', function() use ( $post_id ) {
						check_admin_referer( 'lock-post_' . $post_id );
						wp_set_post_lock( $post_id );
						wp_redirect( usb_get_edit_link( $post_id ) );
						exit;
					}, 1 );
				}

				// If the post_id is set then check the post
				if ( ! empty( $post_id ) ) {

					// Check if a post exists
					if ( is_null( $post ) ) {
						wp_die( us_translate( 'You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?' ) );
					}

					// Checking edit permission by post type
					if ( ! in_array( $post->post_type, $this->get_allowed_edit_post_types() ) ) {
						wp_die( 'Editing of this page is not supported.' );
					}

					// Checking edit permission by post ID
					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						wp_die( us_translate( 'Sorry, you are not allowed to access this page.' ) );
					}

					// Publish the post if it doesn't exist
					if ( $post->post_status === 'auto-draft' ) {
						wp_update_post(
							array(
								'ID' => $post_id,
								'post_title' => '#' . $post_id,
								'post_status' => 'publish',
							)
						);
					}
				}

				USBuilder_Panel::instance(); // this class describes the functionality of the panel

				// Initializing the builder actions
				add_action( "admin_action_" . US_BUILDER_SLUG, array( $this, 'init_builder_page' ), 1 );
				add_action( "admin_action_" . US_BUILDER_SITE_SETTINGS_SLUG, array( $this, 'init_builder_page' ), 1 );

				// Fires when scripts and styles are enqueued for the code editor.
				add_action( 'wp_enqueue_code_editor', array( $this, 'wp_enqueue_code_editor' ), 501, 1 );

				// Filters a string cleaned and escaped for output as a URL.
				add_filter( 'clean_url', array( $this, 'defer_admin_assets' ), 101, 1 );

				// Add styles and scripts on the builder page
				add_action( 'wp_print_styles', array( $this, 'enqueue_assets_for_builder' ), 1 );

				// Outputs the editor scripts, stylesheets, and default settings.
				add_action( 'usb_admin_footer_scripts', array( $this, 'admin_footer_scripts' ), 1 );

				// At regular admin pages ...
			} else {

				// Save generated shortcodes, posts meta.
				add_action( 'save_post', array( USBuilder_Post::instance(), 'save_post' ), 0, 1 );

				// .. adding a link to US Builder editor for posts and pages
				add_filter( 'post_row_actions', array( $this, 'row_actions' ), 501, 2 );
				add_filter( 'page_row_actions', array( $this, 'row_actions' ), 501, 2 );

				add_action( 'edit_form_after_title', array( $this, 'output_builder_switch' ) );
			}

		} else {

			// Disabling style output to avoid duplicates since support for mirroring
			// styles in `(vc|_wpb)_post_custom_css` <-> `usb_post_custom_css` is implemented
			add_action( 'vc_post_custom_css', '__return_false' );

			// The output custom css for a page
			if ( usb_is_post_preview() ) {
				add_action( 'us_before_closing_head_tag', array( $this, 'output_post_custom_css' ), 9 );
			}

			// Setting up a site as a preview
			if ( usb_is_preview() ) {
				new USBuilder_Preview; // this is a class for working with shortcodes

				// Bind a method to manipulate the admin menu
			} elseif ( has_action( 'admin_bar_menu' ) AND us_get_option( 'live_builder' ) ) {
				add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu_action' ], 81 );
			}
		}

		// Init the class for handling AJAX requests
		if ( wp_doing_ajax() ) {
			USBuilder_Ajax::init();
		}
	}

	/**
	 * Post types that are available for editing by the builder
	 *
	 * @access private
	 * @return array List of post types names
	 */
	private function get_allowed_edit_post_types() {
		return array_merge(
			array_keys( us_get_public_post_types( array( /* add post types that should not yet be supported by US Builder here */ ) ) ),
			array( 'us_page_block', 'us_content_template' )
		);
	}

	/**
	 * Add styles and scripts on the builder page
	 *
	 * @access public
	 */
	function enqueue_assets_for_builder() {
		global $wp_scripts, $wp_styles;

		// Reset scripts
		$wp_scripts = new WP_Scripts;

		// Reset styles
		$wp_styles = new WP_Styles;
		wp_default_styles( $wp_styles );

		// Remove assets that are not in use
		wp_dequeue_script( 'admin-bar' );

		// Include all files needed to use the WordPress media API
		wp_enqueue_media();
		wp_enqueue_style( 'media' );
		wp_enqueue_editor();

		// WordPress styles for correct appearance of fields
		wp_enqueue_style( 'forms' );

		/**
		 * Hook for changing the output of assets on the constructor page
		 * Note: Execute before outputting builder and theme files
		 */
		do_action( 'usb_enqueue_assets_for_builder' );

		// Theme admin styles
		us_admin_print_styles();

		// Enqueue JS or CSS files separately, when US_DEV is set
		$min = '';
		if ( ! defined( 'US_DEV' ) ) {
			$min = '.min';
		}

		// Support for WP services in the builder
		wp_enqueue_script( 'usb.wpservices', US_BUILDER_URL . '/assets/js/wpservices'. $min .'.js', array( 'jquery', 'heartbeat' ), US_CORE_VERSION );

		// If the post is locked, then we display only the styles
		if ( usb_post_editing_is_locked() ) {
			// Set builder styles
			wp_enqueue_style( US_BUILDER_SLUG, US_BUILDER_URL . '/assets/css/builder'. $min .'.css', array(), US_CORE_VERSION );
			return;
		}

		// Enqueue USOF JS files separately, when US_DEV is set
		if ( defined( 'US_DEV' ) ) {
			foreach ( us_config( 'assets-admin.js', array() ) as $i => $src ) {
				wp_enqueue_script( "usof-{$i}", US_CORE_URI . $src, array(), US_CORE_VERSION );
			}

			// USBInit - Basic object for mounting and initializing all extensions of the builder
			wp_enqueue_script( US_BUILDER_SLUG, US_BUILDER_URL . '/assets/js/usb.init.js', array( 'jquery' ), US_CORE_VERSION );
			wp_enqueue_style( US_BUILDER_SLUG, US_BUILDER_URL . '/assets/css/builder.css', array(), US_CORE_VERSION );

			// Builder JS extensions
			$js_extensions = us_config( 'us-builder.js_extensions', array() );
			foreach ( $js_extensions as $js_extension ) {
				$src = sprintf( US_BUILDER_URL . '/assets/js/%s.js', $js_extension );
				wp_enqueue_script( 'usb-' . $js_extension, $src, array( US_BUILDER_SLUG ), US_CORE_VERSION );
			}

		} else {
			wp_enqueue_script( 'usof-scripts', US_CORE_URI . '/usof/js/usof.min.js', array( 'jquery' ), US_CORE_VERSION, TRUE );
			wp_enqueue_script( US_BUILDER_SLUG, US_BUILDER_URL . '/assets/js/usb.init.min.js', array( 'jquery' ), US_CORE_VERSION );
			wp_enqueue_style( US_BUILDER_SLUG, US_BUILDER_URL . '/assets/css/builder.min.css', array(), US_CORE_VERSION );
		}
	}

	/**
	 * Filters a string cleaned and escaped for output as a URL
	 *
	 * @access public
	 * @param string $url The cleaned URL to be returned
	 * @return string
	 */
	function defer_admin_assets( $url ) {
		$basename = wp_basename( $url );
		if (
			strpos( $basename, '.css' ) !== FALSE
			OR strpos( $basename, 'usof-' ) === 0 // All USOF files in DEV mode
		) {
			return "$url' defer='defer";
		}

		return $url;
	}

	/**
	 * Filter for getting a link to unblock a post in the builder
	 *
	 * @access public
	 * @param string $link The edit link
	 * @param int $post_id Post ID
	 * @return Returns a link to unblock a post
	 */
	function _get_unlock_post_link( $link, $post_id ) {
		return usb_get_edit_link( $post_id );
	}

	/**
	 * Including additional scripts or settings in the output
	 * Note: The output of scripts in this method should exclude the initialization
	 * of the wp editor, the initialization is performed in the USOF
	 *
	 * @access public
	 */
	function admin_footer_scripts() {
		// Get output footer scripts
		do_action( 'admin_print_footer_scripts' );

		// Heartbeat is a simple server polling API that sends XHR requests to
		// the server every 15 - 60 seconds and triggers events (or callbacks) upon
		// receiving data. Currently these 'ticks' handle transports for post locking
		wp_enqueue_script( 'heartbeat' );

		// @see https://developer.wordpress.org/reference/classes/wp_user_query/#parameters
		$users_args = array(
			'fields' => 'ID',
			'number' => 2
		);

		// Outputs the HTML for the notice to say that someone else is editing or has taken over editing of this post
		if (
			function_exists( '_admin_notice_post_locked' )
			AND (
				is_multisite()
				OR count( get_users( $users_args ) ) > 1
			)
		) {
			add_filter( 'get_edit_post_link', array( $this, '_get_unlock_post_link' ), 501, 2 );
			_admin_notice_post_locked(); // output the html
			remove_filter( 'get_edit_post_link', array( $this, '_get_unlock_post_link' ) );
		}

		// Prints scripts in document head that are in the $handles queue
		if ( function_exists( 'wp_print_scripts' ) ) {
			wp_print_scripts();
		}

		// If the post is locked, then exit
		if ( usb_post_editing_is_locked() ) {
			return;
		}

		// Get data for deferred assets
		$deferred_assets = array();
		foreach ( us_config( 'us-builder.deferred_assets', array() ) as $name => $handles ) {
			$deferred_assets[ $name ] = USBuilder_Assets::instance( $name )
				->add( $handles )
				->get_assets();
		}

		// This is the output of methods for params callbacks
		$js_callbacks = array();
		foreach ( us_config( 'shortcodes.theme_elements', array(), /* reload */ TRUE ) as $elm_filename ) {
			if ( $elm_config = us_config( "elements/$elm_filename", array() ) ) {
				// Ignore elements which are not available via condition
				if ( isset( $elm_config['place_if'] ) AND ! $elm_config['place_if'] ) {
					continue;
				}
				// Do not run further code in cycle if the element has no params
				if ( empty( $elm_config['params'] ) ) {
					continue;
				}
				$elm_filename = us_get_shortcode_full_name( $elm_filename );
				foreach ( $elm_config['params'] as $param_name => $param_config ) {
					if ( us_arr_path( $param_config, 'usb_preview', TRUE ) === TRUE ) {
						continue;
					}
					if ( empty( $param_config['usb_preview'][0] ) ) {
						$param_config['usb_preview'] = array( $param_config['usb_preview'] );
					}
					foreach ( $param_config['usb_preview'] as $instructions ) {
						if ( empty( $instructions['callback'] ) ) {
							continue;
						}
						$callback_body = (string) $instructions['callback'];
						$js_callbacks[] = $elm_filename . '_' . $param_name . ':function( value ){' . $callback_body . '}';
					}
				}
			}
		}
		$jscode = '
			window.$usbdata = window.$usbdata || {}; // single space for data
			window.$usbdata.previewCallbacks = {' . implode( ",", $js_callbacks ) . '};
			window.$usbdata.deferredAssets = ' . json_encode( $deferred_assets ) . ';
		';
		echo '<script>' . $jscode . '</script>';

		// Prints the templates used in the media manager
		if ( function_exists( 'wp_print_media_templates' ) ) {
			wp_print_media_templates();
		}
	}

	/**
	 * Fires when scripts and styles are enqueued for the code editor
	 *
	 * @access public
	 * @param array $settings Settings for the enqueued code editor
	 */
	function wp_enqueue_code_editor( $settings ) {
		// Remove assets from the general output, they will be loaded as
		// needed at the time of initialization of the code editor
		if (
			us_arr_path( $settings, 'codemirror.mode' ) === 'text/css'
			AND wp_script_is( 'code-editor' )
		) {
			wp_dequeue_script( 'code-editor' );
			wp_dequeue_style( 'code-editor' );
			wp_dequeue_script( 'csslint' );
		}
	}

	/**
	 * This is the hook used to add, remove, or manipulate admin bar items
	 * Note: Only for Page Live Builder!
	 *
	 * @access public
	 * @param WP_Admin_Bar $wp_admin_bar The admin bar
	 */
	function admin_bar_menu_action( \WP_Admin_Bar $wp_admin_bar ) {
		// Get the post id
		if ( is_front_page() ) {
			$post_id = get_option( 'page_on_front' );

		} elseif ( is_home() ) {
			$post_id = get_option( 'page_for_posts' );

		} elseif ( is_404() ) {
			$post_id = us_get_option( 'page_404' );

			} elseif ( is_search() AND ! is_post_type_archive( 'product' ) ) {
			$post_id = us_get_option( 'search_page' );

		} elseif ( is_singular( $this->get_allowed_edit_post_types() ) ) {
			$post_id = get_queried_object_id();

			// WooCommerce Shop page
		} elseif ( function_exists( 'is_shop' ) AND is_shop() ) {
			$post_id = wc_get_page_id( 'shop' );

		} else {
			$post_id = ''; // no id to disable Live link
		}

		// If there is no ID, then terminate the execution of the method.
		if ( ! is_numeric( $post_id ) OR empty( $post_id ) ) {
			return;
		}

		$edit_post_link = usb_get_edit_link( $post_id );
		if (
			empty( $edit_post_link )
			OR ! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		/**
		 * Params for the admin menu item
		 *
		 * @link https://developer.wordpress.org/reference/classes/wp_admin_bar/add_node/#parameters
		 * @var array
		 */
		$node = array(
			'id' => 'us-builder',
			'title' => __( 'Edit Live', 'us' ),
			'href' => $edit_post_link,
			'meta' => array(
				'class' => 'us-builder',
				'html' => '<style>.us-builder > a,.us-builder > .ab-empty-item{font-weight:600!important;color:#23ccaa!important}</style>',
			),
		);

		// Remove link from menu but keep item
		if ( function_exists( 'is_shop' ) AND is_shop() ) {
			unset( $node['href'] );
		}

		$wp_admin_bar->add_node( $node );
	}

	/**
	 * Add a link that will be displayed under the title of the record in the records table in the admin panel
	 * Note: Only for Page Live Builder!
	 *
	 * @access public
	 * @param array $actions
	 * @param \WP_Post $post The current post object.
	 * @return array
	 */
	function row_actions( $actions, \WP_Post $post ) {
		if (
			us_get_option( 'live_builder' )
			AND in_array( $post->post_type, $this->get_allowed_edit_post_types() )
			AND $post->post_status !== 'trash' // don't add link for deleted posts
		) {
			// Add link to edit post live
			$edit_post_link = usb_get_edit_link( $post->ID );
			$actions['edit_us_builder'] = '<a href="' . esc_url( $edit_post_link ) . '">' . strip_tags( __( 'Edit Live', 'us' ) ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Add a button that switched editing to US builder
	 * Note: Only for Live Builder enabled!
	 *
	 * @access public
	 * @param \WP_Post $post The current post object
	 */
	function output_builder_switch( \WP_Post $post ) {
		if (
			! us_get_option( 'live_builder' )
			OR  ! in_array( $post->post_type, $this->get_allowed_edit_post_types() )
		) {
			return;
		}
		?>
		<div id="usb-switch">
			<a href="<?php echo usb_get_edit_link( $post->ID ) ?>" class="button button-primary">
				<span><?php echo __( 'Edit Live', 'us' ); ?></span>
			</a>
		</div>
		<?php
	}

	/**
	 * Output custom css for a page
	 *
	 * @access public
	 */
	function output_post_custom_css() {
		$post_id = usb_get_post_id();

		// Get custom css for latest revision
		if ( 'true' === us_arr_path( $_GET, 'preview' ) && wp_revisions_enabled( get_post( $post_id ) ) ) {
			$latest_revision = wp_get_post_revisions( $post_id );
			if ( ! empty( $latest_revision ) ) {
				$array_values = array_values( $latest_revision );
				$post_id = $array_values[0]->ID;
			}
		}

		// Get and output custom css to current page
		$post_custom_css = get_post_meta( $post_id, USBuilder::KEY_CUSTOM_CSS, TRUE );
		$post_custom_css = apply_filters( 'usb_post_custom_css', $post_custom_css, $post_id );
		if ( ! empty( $post_custom_css ) ) {
			echo '<style type="text/css" data-type="' . USBuilder::KEY_CUSTOM_CSS . '">';
			echo wp_strip_all_tags( $post_custom_css );
			echo '</style>';
		}
	}

	/**
	 * Get the config for "Page Builder" or "Site Settings"
	 *
	 * @access public
	 * @param string $action
	 * @return array Returns an array of configs
	 */
	static function get_config( $action = '' ) {
		if ( empty( $action ) ) {
			return array();
		}

		// Get config for "Site Settings"
		if ( $action == US_BUILDER_SITE_SETTINGS_SLUG ) {
			$config = array();

			// Get all the titles for the groups
			foreach ( us_config( 'live-options' ) as $group_id => $group ) {
				if ( $group_title = us_arr_path( $group, 'title' ) ) {
					$config['group_titles'][ $group_id ] = $group_title;
				}
			}

			// Get font weights and styles
			global $us_google_fonts;
			if ( empty( $us_google_fonts ) ) {
				foreach ( us_config( 'google-fonts' ) as $font_family => $font_options ) {
					$us_google_fonts[ $font_family ] = ! empty( $font_options[ 'variants' ] )
						? implode( ',', (array) $font_options[ 'variants' ] )
						: '';
				}
			}

			return array(
				'site' => $config,
				'typography' => array(
					'googleFonts' => $us_google_fonts,
				),
			);
		}

		// Get config for "Page Builder"
		$post_id = usb_get_post_id();
		$post_type = get_post_type( $post_id ); // get current post type

		// Get options for jsoncss generator
		$jsoncss_options = (array) us_get_jsoncss_options();
		if ( isset( $jsoncss_options['css_mask'] ) ) {
			unset( $jsoncss_options['css_mask'] );
		}

		// Create a list of colors variables, based on CSS vars
		$color_vars = array();
		foreach ( us_config( 'theme-options.colors.fields', array() ) as $color_option => $color_option_params ) {
			// Do not add empty color values
			if ( us_get_color( $color_option, TRUE, FALSE ) === '' ) {
				continue;
			}
			// Do not add variables without "color" prefix in its names
			if ( strpos( $color_option, 'color' ) !== 0 ) {
				continue;
			}
			// Remove "color" prefix
			$color_option = substr( $color_option, strlen( 'color' ) );
			// Add color to general list
			$color_vars[ $color_option ] = us_get_color( $color_option, /* gradient */ TRUE, /* css var */ TRUE );
		}

		// Create a list of global fonts variables, based on CSS vars
		$font_vars = array();
		foreach ( US_TYPOGRAPHY_TAGS as $_tag ) {
			if ( $_tag == 'body' ) {
				$font_vars[ $_tag ] = 'var(--font-family)';
			} else {
				$font_vars[ $_tag ] = sprintf( 'var(--%s-font-family)', $_tag );
			}
		}

		// Config for the "Page Builder"
		$config = array(

			// Settings for shortcodes
			'shortcode' => array(
				// List of container shortcodes (e.g. [us_hwrapper]...[/us_hwrapper])
				'containers' => array(),
				// List of shortcodes which have inner content (e.g. [us_message]...[/us_message]), but shouldn't be containers
				'edit_content' => array(),
				// List of default values for shortcodes
				'default_values' => array(),
				// The a list of strict relations between shortcodes (separate multiple values with comma)
				'relations' => array(
					'as_parent' => array(
						// Since we introduced a new type of root `container` at the level of shortcodes and builder,
						// then we will add a rule for it that should be ignored when adding a new element
						self::MAIN_CONTAINER => array(
							'only' => us_config( 'us-builder.as_parent_container_only', /* default */'vc_row' )
						),
					),
				),
				// Elements, when changed or added, which must be updated inclusively from the parent
				'update_parent' => array(),

				// Elements with more than one node in the result must have a common wrap
				'with_wrappers' => us_config( 'us-builder.with_wrappers', /* default */array() ),
			),

			// Dynamic assets for the correct operation of fieldsets
			'dynamicFieldsetAssets' => array(),

			// Titles for available elements (even those that are not supported)
			'elm_titles' => (array) apply_filters( 'usb_config_elm_titles', /* value */array() ),

			// Icons of available elements
			'elm_icons' => array(),

			// List of elements supported by the builder
			'elms_supported' => array(),

			// Default placeholder (Used in importing shortcodes)
			'placeholder' => us_get_img_placeholder( 'full', /* src only */TRUE ),

			// Post types for selection in Grid element (Used in importing shortcodes)
			'grid_post_types' => us_grid_available_post_types_for_import(),

			// Templates shortcodes or html
			'template' => us_config( 'us-builder.templates', array() ),

			// Settings for the css compiler
			'designOptions' => array_merge(
				$jsoncss_options,
				array(
					// prefix for custom classes when generating styles from design options
					'customPrefix' => 'usb_custom_',
					'fontVars' => $font_vars,
					'colorVars' => $color_vars,
				)
			),

			// Maximum size of changes in the data history
			'maxDataHistory' => (int) us_config( 'us-builder.max_data_history', /* default */100 ),

			// List of usof field types for which to use throttle
			'useThrottleForFields' => (array) us_config( 'us-builder.use_throttle_for_fields', /* default */array() ),

			// List of usof field types for which the update interval is used
			'useLongUpdateForFields' => (array) us_config( 'us-builder.use_long_update_for_fields', /* default */array() ),

			// Columns Layout via CSS grid
			'isGridColumnsLayout' => (bool) us_get_option( 'grid_columns_layout' ),

			// List of selectors for overriding the root node in containers
			'rootContainerSelectors' => array(),

			// List of available aliases for usbid
			'aliases' => array(
				'tab' => 'tab-button',
			),

			// Elements outside of the main container, such as the header, footer, or sidebar
			'elms_outside_main_container' => us_config( 'us-builder.elms_outside_main_container', array() ),
		);

		// Get all elements available in the theme
		$fieldsets = array();
		foreach ( us_config( 'shortcodes.theme_elements', array(), TRUE ) as $elm_filename ) {
			if ( $elm_config = us_config( "elements/$elm_filename", array() ) ) {
				// Ignore elements which are not available via condition
				if ( isset( $elm_config['place_if'] ) AND ! $elm_config['place_if'] ) {
					continue;
				}
				$fieldsets[ $elm_filename ] = $elm_config;
				$is_container = ! empty( $elm_config['is_container'] );
				// The list of all containers
				if ( $is_container ) {
					$config['shortcode']['containers'][] = $elm_filename;
				}
				// Check for a selector to find the root container
				if ( $is_container AND $root_container_selector = us_arr_path( $elm_config, 'usb_root_container_selector' ) ) {
					$config['rootContainerSelectors'][ $elm_filename ] = (string) $root_container_selector;
				}
				// Elements, when changed or added, which must be updated inclusively from the parent
				if ( ! empty( $elm_config['usb_update_parent'] ) ) {
					$config['shortcode']['update_parent'][] = $elm_filename;
				}
				// List of elements that have movement along axis X enabled
				if ( ! empty( $elm_config['usb_moving_only_x_axis'] ) ) {
					$config['moving_only_x_axis'][] = $elm_filename;
				}
				// The list of strict relations between shortcodes
				// All permissions are extracted from WPB settings for compatibility and correct work both on the USBuilder and WPB
				foreach ( array( 'as_parent', 'as_child' ) as $relation ) {
					if (
						isset( $elm_config[ $relation ] ) AND
						! empty( $elm_config[ $relation ] ) AND
						is_array( $elm_config[ $relation ] )
					) {
						$separator = ',';
						foreach ( $elm_config[ $relation ] as $condition => $shortcodes ) {
							if ( $shortcodes = explode( $separator, $shortcodes ) ) {
								foreach ( $shortcodes as &$shortcode ) {
									$shortcode = us_get_shortcode_name( $shortcode );
								}
							}
							if ( is_array( $shortcodes ) ) {
								/**
								 * Checking a condition for correctness or absence ( Required only|except )
								 * @link https://kb.wpbakery.com/docs/developers-how-tos/nested-shortcodes-container/
								 * @var string
								 */
								$condition = in_array( $condition, array( 'only', 'except' ) )
									? $condition
									: 'only';
								// Separate multiple values with comma
								$shortcodes = implode( $separator, $shortcodes );
								$config['shortcode']['relations'][ $relation ][ $elm_filename ][ $condition ] = $shortcodes;
							}
						}
					}
				}
				// Get element icon
				$elm_icon = us_arr_path( $elm_config, 'icon', '' );
				// Create elements icon
				if ( $elm_icon ) {
					$config['elm_icons'][ $elm_filename ] = $elm_icon;
				}
			}
		}

		// Shortcodes that contain inner content for the editor as a value
		foreach ( $fieldsets as $elm_name => $fieldset ) {
			foreach ( us_arr_path( $fieldset, 'params', array() ) as $param_name => $options ) {

				// Get default values for the edited content, if any
				if ( $param_name == 'content' OR $options['type'] == 'editor' ) {
					$elm_name = us_get_shortcode_name( $elm_name );
					$config['shortcode']['edit_content'][ $elm_name ] = $param_name;
					if ( ! empty( $options['std'] ) ) {
						$config['shortcode']['default_values'][ $elm_name ][ $param_name ] = $options['std'];
					}
				}

				// Get default values for select
				if ( $options['type'] == 'select' AND empty( $options['std'] ) AND is_array( $options['options'] ) ) {
					$keys = array_keys( $options['options'] );
					if ( $value = us_arr_path( $keys, '0' ) ) {
						$config['shortcode']['default_values'][ $elm_name ][ $param_name ] = $value;
					}
				}

				// For the Group default value transform array to a string (compatibility with WPBakery builder)
				if ( $options['type'] == 'group' AND ! empty( $options['std'] ) ) {
					$elm_name = us_get_shortcode_name( $elm_name );
					$config['shortcode']['default_values'][ $elm_name ][ $param_name ] = rawurlencode( json_encode( $options['std'] ) );
				}

				// Remove prefixes needed for compatibility from Visual Composer
				if ( ! empty( $options['type'] ) ) {
					$fieldsets[ $elm_name ]['params'][ $param_name ]['type'] = us_get_shortcode_name( $options['type'] );
				}

				// Determine the availability of dynamic assets for fieldsets
				if ( us_arr_path( $options, 'encoded' ) === TRUE ) {
					$config['dynamicFieldsetAssets'][ 'codeEditor' ][] = $elm_name;
					$_codeEditor = &$config['dynamicFieldsetAssets'][ 'codeEditor' ];
					$_codeEditor = array_unique( $_codeEditor );
				}
			}

			// List of elements supported by the builder
			$config['elms_supported'][] = $elm_name;

			// Add a supported element to the titles list
			$config['elm_titles'][ $elm_name ] = us_arr_path( $fieldset, 'title', $elm_name );

			// All fieldsets that are loaded via AJAX are excluded from the output
			if ( ! us_arr_path( $fieldset, 'usb_preload', FALSE ) ) {
				unset( $fieldsets[ $elm_name ] );
			}
		}

		return $config;
	}

	/**
	 * Initializing the builder page
	 *
	 * @access public
	 */
	public function init_builder_page() {
		$post_id = usb_get_post_id();
		$post_type = get_post_type( $post_id ); // get current post type
		$live_builder_is_enabled = us_get_option( 'live_builder', /* default */FALSE );

		if ( is_rtl() ) {
			$this->_body_classes[] = 'rtl';
		}

		// Post edit locked
		if ( usb_post_editing_is_locked() ) {
			$this->_body_classes[] = 'edit_locked';
		}

		// Get a link to a page
		$post_link = ! empty( $post_id )
			? get_permalink( $post_id )
			: get_home_url( NULL, '/' );
		$post_link = apply_filters( 'the_permalink', $post_link );

		/**
		 * @var array Get all breakpoints
		 */
		$breakpoints = (array) us_get_responsive_states();
		foreach ( (array) us_get_jsoncss_options()['breakpoints'] as $screen => $css_media ) {
			$breakpoints[ $screen ][ 'media' ] = $css_media;
		}

		// For the default screen, add the maximum width for the preview, with the responsive screens option enabled
		$breakpoints['default']['max_width'] = (int) us_config( 'us-builder.max_screen_width', /* default */2560 );

		// Mask for the title of the edited page
		$admin_page_title = strip_tags( __( 'Edit Live', 'us' ) . ' - %s' );

		/**
		 * The general settings for US Builder
		 *
		 * Note: All parameters and data are used on the front-end in USBuilder
		 * and changing or deleting may break the work of the USBuilder
		 */
		$usb_config = (array) USBuilder_Ajax::get_actions();
		$usb_config += array(
			'_nonce' => USBuilder_Ajax::create_nonce(),

			// Mask for the title of the edited page
			'adminPageTitleMask' => $admin_page_title,

			// Meta key for post custom css
			'keyCustomCss' => USBuilder::KEY_CUSTOM_CSS,

			// List of actions by which extensions are loaded and initialized
			'actions' => array(
				'site_settings' => US_BUILDER_SITE_SETTINGS_SLUG,
				'post_editing' => US_BUILDER_SLUG,
			),

			// Settings for preview
			'preview' => array(
				// Params for identifying site previews via URL
				'url_site_param' => US_BUILDER_SITE_SETTINGS_SLUG,
				'url_site_nonce' => wp_create_nonce( US_BUILDER_SITE_SETTINGS_SLUG ),

				// Minimum preview screen width (in pixels)
				'minWidth' => (int) us_config( 'us-builder.min_screen_width', /* default */320 ),

				// Minimum preview screen height (in pixels)
				'minHeight' => (int) us_config( 'us-builder.min_screen_height', /* default */320 ),
			),

			// Default parameters for AJAX requests
			'ajaxArgs' => array(
				'post' => $post_id,
				'_nonce' => (
					! usb_post_editing_is_locked()
						? wp_create_nonce( US_BUILDER_SLUG )
						: ''
				),
			),

			// The set responsive states
			'responsiveStates' => array_keys( $breakpoints ),

			// Set breakpoints
			'breakpoints' => $breakpoints,

			// Determining if a license is activated
			'license_is_activated' => (
				defined( 'US_DEV_SECRET' )
				OR defined( 'US_THEMETEST_CODE' )
				OR get_option( 'us_license_activated' )
				OR get_option( 'us_license_dev_activated' )
			),
		);

		// Add typography settings
		$usb_config['typography'] = array(
			'font_display' => us_get_option( 'font_display', 'swap' ),
			'fonts_id' => us_config( 'us-builder.fonts_id', 'us-fonts-css' ), //`<link id="{fonts_id}"...>` element ID to include the Google Font
			'googleapis' => sprintf( '%s://fonts.googleapis.com/css', is_ssl() ? 'https' : 'http' ),
			'tags' => US_TYPOGRAPHY_TAGS, // basic tags for customizing typography
		);

		// Get edit post link
		$edit_post_link = get_edit_post_link( $post_id );

		/**
		 * Texts for the builder and different custom messages
		 * Note: Translation keys are duplicated in JavaScript files!
		 * @var array
		 */
		$text_translations = array(
			'empty_clipboard' => __( 'There is nothing to paste.', 'us' ),
			'failed_set_data_for_action' => us_translate( 'An error has occurred. Please reload the page and try again.' ),
			'invalid_data' => us_translate( 'Invalid value.' ),
			'cannot_paste' => __( 'Cannot paste into this container.' ),
			'page_leave_warning' => us_translate( 'The changes you made will be lost if you navigate away from this page.' ),
			'site_settings_updated' => sprintf(
				'%s <a href="%s" target="_blank">%s</a>',
				us_translate( 'Changes saved.' ),
				$post_link,
				us_translate( 'View Page' )
			),
			'editing_not_supported' => sprintf(
				'%s<br><a href="%s" target="_blank">%s</a>',
				__( 'Editing of this element is not supported.', 'us' ),
				$edit_post_link,
				__( 'Edit page in Backend', 'us' )
			),
			'section' => __( 'Section', 'us' ), // used in Tabs/Tour/Accordion elements
		);

		// Add translations of panel titles
		$text_translations = array_merge( USBuilder_Panel::get_titles(), $text_translations );

		// Notification text depending on the message type when the page is saved
		// For Page Templated display only notification ...
		if ( $post_type === 'us_page_block' ) {
			$text_translations['page_updated'] = __( 'Reusable Block updated', 'us' );

		} elseif ( $post_type === 'us_content_template' ) {
			$text_translations['page_updated'] = __( 'Page Template updated', 'us' );

			// ... for posts, pages and other CPT also display link to its page on site
		} else {
			$text_translations['page_updated'] = sprintf(
				'%s <a href="%s" target="_blank">%s</a>',
				us_translate( 'Page updated.' ),
				$post_link,
				us_translate( 'View Page' )
			);
		}

		// Get current preview URL and extend config
		if ( ! $live_builder_is_enabled OR usb_is_site_settings() ) {
			$current_preview_url = add_query_arg(
				array(
					US_BUILDER_SITE_SETTINGS_SLUG => wp_create_nonce( US_BUILDER_SITE_SETTINGS_SLUG ),
				),
				$post_link
			);
			$extend_config = static::get_config( /* action */US_BUILDER_SITE_SETTINGS_SLUG );

		} else {
			$current_preview_url = add_query_arg(
				array(
					US_BUILDER_SLUG => wp_create_nonce( US_BUILDER_SLUG ),
				),
				$post_link
			);
			if ( ! $live_builder_is_enabled ) {
				$current_preview_url = '';
			}
			// If the post is blocked, then load as a simple page, not a preview
			if ( usb_post_editing_is_locked() ) {
				$preview_urls[ US_BUILDER_SLUG ] = $post_link;
			}
			// If the post is not locked, then expand the config
			if ( ! usb_post_editing_is_locked() ) {
				$extend_config = static::get_config( /* action */US_BUILDER_SLUG );
			}
		}

		if ( isset( $extend_config ) ) {
			$usb_config = us_array_merge( $usb_config, $extend_config );
		}

		// Add an action class to apply all styles on load
		$this->_body_classes[] = sprintf( 'action_%s', (
			usb_is_site_settings()
				? US_BUILDER_SITE_SETTINGS_SLUG // default action
				: US_BUILDER_SLUG
		) );

		// Formation of the title of the edited page
		$admin_page_title = sprintf( $admin_page_title, ! empty( $post_id ) ? get_the_title( $post_id ) : /* default */get_bloginfo( 'name' ) );

		// The formation of the main page
		us_load_template(
			'builder/templates/main', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'body_class' => implode( ' ', $this->_body_classes ),
				'current_preview_url' => $current_preview_url,
				'edit_post_link' => $edit_post_link,
				'live_builder_is_enabled' => $live_builder_is_enabled,
				'post_link' => $post_link,
				'post_type' => $post_type,
				'text_translations' => $text_translations,
				'title' => $admin_page_title,
				'usb_config' => $usb_config,
			)
		);
		exit;
	}
}
