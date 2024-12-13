<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! function_exists( 'us_post_meta_boxes_setup' ) ) {
	add_action( 'load-post.php', 'us_post_meta_boxes_setup' );
	add_action( 'load-post-new.php', 'us_post_meta_boxes_setup' );

	function us_post_meta_boxes_setup() {

		$config = us_config( 'meta-boxes', array() );

		foreach ( $config as &$meta_box ) {
			new US_Meta_Box( $meta_box );
		}
	}
}

class US_Meta_Box {

	public $meta_box;

	public function __construct( $meta_box ) {
		if ( ! is_admin() ) {
			return;
		}

		// Support for both arrays and strings
		if ( ! is_array( $meta_box['post_types'] ) ) {
			$meta_box['post_types'] = array( $meta_box['post_types'] );
		}

		$this->meta_box = $meta_box;

		// Add meta box
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Save meta box
		foreach ( $this->meta_box['post_types'] as $post_type ) {
			add_action( 'save_post_' . $post_type, array( $this, 'save_meta_boxes' ) );
		}

	}

	public function add_meta_boxes() {
		foreach ( $this->meta_box['post_types'] as $post_type ) {
			$callback_args = NULL;

			add_meta_box(
				$this->meta_box['id'],
				$this->meta_box['title'],
				array(
					$this,
					'meta_box_body',
				),
				$post_type,
				$this->meta_box['context'],
				$this->meta_box['priority'],
				$callback_args
			);
		}
	}

	public function meta_box_body() {

		echo '<div class="usof-container for_meta">';
		global $us_metabox_marker_placed;
		if ( empty( $us_metabox_marker_placed ) ) {
			echo '<input type="hidden" name="us_metabox_save" value="1">';
			$us_metabox_marker_placed = TRUE;
		}

		$post = get_post();
		$post_id = isset( $post->ID ) ? $post->ID : 0;
		$values = array();

		// Output "Used in" locations for Reusable Blocks metabox
		if ( 'us_post_info' == $this->meta_box['id'] ) {
			$this->meta_box['fields']['used_in_locations']['description'] = us_get_used_in_locations( $post->ID, TRUE );
		}

		foreach ( $this->meta_box['fields'] as $field_key => $field ) {
			if ( $post_id ) {
				if ( metadata_exists( 'post', $post_id, $field_key ) ) {
					$meta_value = get_post_meta( $post_id, $field_key, TRUE );

					// Filter compatible meta value for different versions
					$meta_value = apply_filters( 'us_fallback_metabox_value', $meta_value, $field_key, $field );

					$values[ $field_key ] = $meta_value;
				} elseif ( isset( $field['std'] ) ) {
					$values[ $field_key ] = $field['std'];
				} else {
					$values[ $field_key ] = '';
				}
			}

			// Reset to the default for select and radio types, if the value is not presented in options
			if (
				isset( $field['options'] )
				AND $field['type'] !== 'checkboxes'
				AND ! in_array( $values[ $field_key ], array_keys( $field['options'] ) )
			) {
				$values[ $field_key ] = isset( $field['std'] ) ? $field['std'] : '';
			}
		}

		foreach ( $this->meta_box['fields'] as $field_key => $field ) {
			us_load_template(
				'usof/templates/field', array(
					'name' => $field_key,
					'id' => 'usof_' . $field_key,
					'field' => $field,
					'values' => &$values,
					'context' => 'metabox',
				)
			);
		}

		echo '</div>';
	}

	public function save_meta_boxes( $post_id ) {
		if ( ! ( isset( $_POST['us_metabox_save'] ) AND $_POST['us_metabox_save'] == 1 ) ) {
			return;
		}
		foreach ( $this->meta_box['fields'] as $field_key => $field ) {
			if ( $field['type'] == 'heading' ) {
				continue;
			}

			// If the meta field is disabled, perhaps delete its value (in case it was created earlier)
			if ( isset( $field['place_if'] ) AND ! $field['place_if'] ) {
				delete_post_meta( $post_id, $field_key );
				continue;
			}

			$meta_value = isset( $_POST[ $field_key ] ) ? $_POST[ $field_key ] : NULL;

			// Save the meta field only with non-default value
			if ( $meta_value !== NULL AND $meta_value != $field['std'] ) {
				update_post_meta( $post_id, $field_key, $meta_value );

				// Delete the meta field in other cases (this removes early created fields with default values)
			} else {
				delete_post_meta( $post_id, $field_key );
			}
		}
	}
}
