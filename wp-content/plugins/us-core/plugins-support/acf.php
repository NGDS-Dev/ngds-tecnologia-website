<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Advanced Custom Fields
 *
 * @link https://www.advancedcustomfields.com/
 *
 * TODO: Globally replace the architecture of storing and working fields,
 * use an identifier instead of a name, since now there is a problem if fields in
 * different groups have the same name does not work correctly.
 */

if ( ! class_exists( 'ACF' ) ) {
	return;
}

// Register Google Maps API key
// https://www.advancedcustomfields.com/resources/google-map/
if ( ! function_exists( 'us_acf_google_map_api' ) ) {
	function us_acf_google_map_api( $api ) {
		// Get the Google Maps API key from the Theme Options
		$gmaps_api_key = trim( esc_attr( us_get_option('gmaps_api_key', '') ) );
		/*
		 * Set the API key for ACF only if it is not empty,
		 * to prevent possible erase of the same value set in other plugins
		 */
		if ( ! empty( $gmaps_api_key ) ) {
			$api['key'] = $gmaps_api_key;
		}

		return $api;
	}

	add_filter( 'acf/fields/google_map/api', 'us_acf_google_map_api' );
}

// Removing custom plugin messages for ACF Pro
if ( ! function_exists( 'us_acf_pro_remove_update_message' ) ) {
	function us_acf_pro_remove_update_message() {
		if ( class_exists( 'ACF_Updates' ) ) {
			$class = new ACF_Updates();
			remove_filter( 'pre_set_site_transient_update_plugins', array( $class, 'modify_plugins_transient' ), 15 );
		}

		// Remove additional messages for buying license
		if (
			function_exists( 'acf_get_setting' )
			AND $acf_basename = acf_get_setting( 'basename' )
		) {
			remove_all_actions( 'in_plugin_update_message-' . $acf_basename );
		}
	}

	add_action( 'init', 'us_acf_pro_remove_update_message', 30 );
}

if ( ! function_exists( 'us_acf_get_fields' ) ) {
	/**
	 * Get a list of all fields
	 *
	 * @param string|array $types The field types to get
	 * @param bool $to_list If a list is given, the result will be [ group => [ key => value ] ]
	 * @param string $separator The separator for the "option" prefix, example: `option{separator}name`
	 * @return array Returns a list of fields
	 */
	function us_acf_get_fields( $types = array(), $to_list = FALSE, $separator = '|' ) {

		if ( ! is_array( $types ) AND ! empty( $types ) ) {
			$types = array( $types );
		}
		$result = array();

		// Bypass all field groups.
		foreach ( (array) acf_get_field_groups() as $group ) {

			/**
			 * Add the field prefix, if the group is used in ACF Options page.
			 * @link https://www.advancedcustomfields.com/resources/get-values-from-an-options-page/
			 */
			$options_prefix = '';
			if ( is_array( $group['location'] ) ) {
				foreach ( $group['location'] as $location_or ) {
					foreach ( $location_or as $location_and ) {
						if ( $location_and['param'] === 'options_page' AND $location_and['operator'] === '==' ) {
							$options_prefix = 'option' . $separator;
							break 2;
						}
					}
				}
			}

			// Get all the fields of the group and generating the result.
			$fields = array();
			foreach( (array) acf_get_fields( $group['ID'] ) as $field ) {

				// If types are given and the field does not correspond
				// to one type, then skip this field.
				if (
					! empty( $types )
					AND is_array( $types )
					AND ! in_array( $field['type'], $types )
				) {
					continue;
				}

				// If there is a prefix, then add all the field names.
				if ( $options_prefix ) {
					$field['name'] = $options_prefix . $field['name'];
				}

				// If the list format is specified, then we will form the list.
				if ( $to_list ) {
					$fields[ $field['name'] ] = $field['label'];
				} else {
					$fields[] = $field;
				}
			}

			if ( count( $fields ) ) {
				// This is the full name of the group that can be used for output in dropdowns or other controls
				$result[ $group['ID'] ] = array( '__group_label__' => $group['title'] );
				$result[ $group['ID'] ] += $fields;
			}
		}
		return $result;
	}
}

if ( ! function_exists( 'us_acf_get_fields_keys' ) ) {
	/**
	 * Get a list of all keys
	 *
	 * @param string|array $types The field types to get
	 * @param string $separator The separator for the "option" prefix, example: `option{separator}name`
	 * @return array Returns a list of all keys
	 */
	function us_acf_get_fields_keys( $types = array(), $separator = '|' ) {
		$keys = array();
		foreach( us_acf_get_fields( $types, /* to_list */TRUE, $separator ) as $fields ) {
			// Remove a group label
			if ( isset( $fields['__group_label__'] ) ) {
				unset( $fields['__group_label__'] );
			}
			$keys = array_merge( $keys, array_keys( $fields ) );
		}
		return $keys;
	}
}

if ( ! function_exists( 'us_acf_get_custom_field' ) ) {
	add_filter( 'us_get_custom_field', 'us_acf_get_custom_field', 2, 4 );
	/**
	 * Filters a custom field value to apply the ACF return format
	 *
	 * @param mixed $value The meta value
	 * @param string $name The field name
	 * @param int|string $current_id The current id
	 * @param string|null $meta_type The meta type
	 * @return mixed Returns a value given specific fields
	 */
	function us_acf_get_custom_field( $value, $name, $current_id, $meta_type = NULL ) {

		// Built-in fields where the name starts with us_ are returned unchanged
		if ( strpos( $name, 'us_' ) === 0 ) {
			return $value;
		}

		// Use the meta slug as prefix in the current id for ACF functions
		// https://www.advancedcustomfields.com/resources/get_field/#get-a-value-from-different-objects
		if (
			$meta_type == 'term'
			AND $term = get_term( $current_id )
			AND $term instanceof WP_Term
		) {
			$current_id = $term->taxonomy . '_' . $current_id;
		}
		if ( $meta_type == 'user' ) {
			$current_id = 'user_' . $current_id;
		}

		// Get field object
		// https://www.advancedcustomfields.com/resources/get_field_object/
		$field = get_field_object( $name, $current_id );

		// In case the field is not exist in ACF return the initial value
		// This allows getting values for non-ACF custom fields correctly (_wp_attachment_image_alt, etc.)
		if ( $field === FALSE ) {
			return $value;
		}

		// Return value from an field object
		return us_arr_path( $field, 'value', /* default */$value );
	}
}

if ( ! function_exists( 'us_acf_link_dynamic_values' ) ) {
	add_filter( 'us_link_dynamic_values', 'us_acf_link_dynamic_values' );
	/**
	 * Append ACF predefined field types to link dynamic values
	 *
	 * @param array $dynamic_values Groups of dynamic values
	 * @return array Returns an expanded array of variables
	 */
	function us_acf_link_dynamic_values( $dynamic_values ) {

		// Skip adding values if it's not edit mode
		if ( ! us_is_elm_editing_page() OR empty( $dynamic_values['acf_types'] ) ) {
			return $dynamic_values;
		}

		$acf_dynamic_values = array();

		foreach( us_acf_get_fields( $dynamic_values['acf_types'], /* to_list */TRUE, /* separator */'/' ) as $fields ) {
			$group_label = us_arr_path( $fields, '__group_label__', /* default */'' );
			foreach ( $fields as $field_key => $field_name ) {
				if ( $field_key == '__group_label__' ) {
					continue;
				}
				$acf_dynamic_values[ $group_label ][ 'custom_field|' . $field_key ] = $field_name;
			}
		}

		return array_merge( $dynamic_values, $acf_dynamic_values );
	}
}

if ( ! function_exists( 'us_acf_dynamic_values' ) ) {
	add_filter( 'us_text_dynamic_values', 'us_acf_dynamic_values' );
	add_filter( 'us_image_dynamic_values', 'us_acf_dynamic_values' );
	/**
	 * Append ACF predefined field types to text dynamic values
	 *
	 * @param array $dynamic_values Groups of dynamic values
	 * @return array Returns an expanded array of variables
	 */
	function us_acf_dynamic_values( $dynamic_values ) {

		// Skip adding values if it's not edit mode
		if ( ! us_is_elm_editing_page() OR empty( $dynamic_values['acf_types'] ) ) {
			return $dynamic_values;
		}

		$acf_dynamic_values = array();

		foreach( us_acf_get_fields( $dynamic_values['acf_types'], /* to_list */TRUE, /* separator */'/' ) as $fields ) {
			$group_label = us_arr_path( $fields, '__group_label__', /* default */'' );
			foreach ( $fields as $field_key => $field_name ) {
				if ( $field_key == '__group_label__' ) {
					continue;
				}
				$acf_dynamic_values[ $group_label ][ '{{' . $field_key . '}}' ] = $field_name;
			}
		}

		return array_merge( $dynamic_values, $acf_dynamic_values );

	}
}
