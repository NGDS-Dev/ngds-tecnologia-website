<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Working with post metadata
 */

if ( ! function_exists( 'us_document_title_parts' ) ) {
	add_filter( 'document_title_parts', 'us_document_title_parts', 101, 1 );
	/**
	 * Set page title from meta-boxes data
	 *
	 * @param array parts
	 * @return array
	 */
	function us_document_title_parts( $parts ) {
		if ( ! us_get_option( 'og_enabled' ) ) {
			return $parts;
		}

		if ( $meta_title = us_get_custom_field( 'us_meta_title' ) ) {
			$parts['title'] = trim( strip_tags( wp_specialchars_decode( $meta_title ) ) );

			if ( isset( $parts['site'] ) ) {
				unset( $parts['site'] );
			}
			if ( isset( $parts['tagline'] ) ) {
				unset( $parts['tagline'] );
			}
		}

		return $parts;
	}
}

if ( ! function_exists( 'us_output_meta_tags' ) ) {
	add_action( 'wp_head', 'us_output_meta_tags', 5 );
	/**
	 * Get and output metadata for a page
	 */
	function us_output_meta_tags() {

		// Default meta tags
		$meta_tags = array(
			'viewport' => 'width=device-width, initial-scale=1',
			'SKYPE_TOOLBAR' => 'SKYPE_TOOLBAR_PARSER_COMPATIBLE',
		);

		// Set color of address bar in browsers, if supported
		if ( $theme_color = us_get_option( 'color_chrome_toolbar', '' ) ) {
			$meta_tags['theme-color'] = $theme_color;
		}

		// Add SEO tags, if enabled
		if ( us_get_option( 'og_enabled', 1 ) ) {

			// TODO: add hreflang attributes, if post has several language versions

			// The `title` from meta-boxe settings
			if ( $meta_title = us_get_custom_field( 'us_meta_title' ) ) {
				$meta_tags['og:title'] = $meta_title;

				// or default page title
			} else {
				$meta_tags['og:title'] = wp_get_document_title();
			}

			// The `description` from meta-box settings
			if ( $meta_description = us_get_custom_field( 'us_meta_description' ) ) {
				$meta_tags['description'] = $meta_description;

				// or Post Excerpt
			} elseif (
				has_excerpt()
				AND $the_excerpt = get_the_excerpt()
			) {
				$meta_tags['description'] = $the_excerpt;

				// or Term Description
			} elseif ( $term_description = term_description() ) {
				$meta_tags['description'] = $term_description;
			}

			// The `robots` from meta-box settings
			if (
				get_option( 'blog_public' )
				AND $robots = us_get_custom_field( 'us_meta_robots' )
			) {
				$meta_tags['robots'] = $robots;
			}

			/*
			 * The Open Graph data
			 * @link https://ogp.me/
			 */
			$meta_tags['og:url'] = home_url( us_get_safe_var( 'REQUEST_URI' ) );
			$meta_tags['og:locale'] = get_locale();
			$meta_tags['og:site_name'] = get_option( 'blogname' );

			// The og:type data
			if ( function_exists( 'is_product' ) AND is_product() ) {
				$meta_tags['og:type'] = 'product';
			} elseif ( is_single() ) {
				$meta_tags['og:type'] = 'article';
			} else {
				$meta_tags['og:type'] = 'website';
			}

			// The og:image data
			if ( function_exists( 'is_shop' ) AND is_shop() ) {
				$meta_tags['og:image'] = get_the_post_thumbnail_url( get_option( 'woocommerce_shop_page_id' ), 'large' );

			} elseif ( has_post_thumbnail() ) {
				$meta_tags['og:image'] = get_the_post_thumbnail_url( NULL, 'large' );

			} elseif ( $meta_image = us_get_custom_field( 'us_og_image' ) ) {
				$meta_tags['og:image'] = (string) $meta_image;
			}
		}

		// Output the tags
		if ( $meta_tags = (array) apply_filters( 'us_meta_tags', $meta_tags ) ) {
			foreach ( $meta_tags as $tag_name => $tag_content ) {
				if (
					! is_string( $tag_content )
					// The filtering values
					OR ! $tag_content = trim( strip_tags( wp_specialchars_decode( $tag_content ) ) )
				) {
					continue;
				}

				if ( strpos( $tag_name, 'og:' ) === 0 ) {
					$tag_atts = array(
						'property' => $tag_name,
						'content' => $tag_content,
					);
					// Add specific attribute for WhatsApp
					if ( $tag_name === 'og:image' ) {
						$tag_atts['itemprop'] = 'image';
					}
				} else {
					$tag_atts = array(
						'name' => $tag_name,
						'content' => $tag_content,
					);
				}
				echo "<meta" . us_implode_atts( $tag_atts ) . ">\n";
			}
		}
	}
}

if ( ! function_exists( 'us_save_post_add_og_image' ) ) {
	add_action( 'save_post', 'us_save_post_add_og_image' );
	/**
	 * Save og_image for the post if there is a setting
	 *
	 * @param int $post_id The post identifier
	 */
	function us_save_post_add_og_image( $post_id ) {

		// If the post has thumbnail, clear og_image meta data
		if ( has_post_thumbnail( $post_id ) ) {
			delete_post_meta( $post_id, 'us_og_image' );

			// in other case try to find an image inside post content
		} elseif ( $post = get_post( $post_id ) AND ! empty( $post->post_content ) ) {
			$the_content = apply_filters( 'us_content_template_the_content', $post->post_content );

			if ( preg_match( '/<img [^>]*src=["|\']([^"|\']+)/i', $the_content, $matches ) ) {
				update_post_meta( $post_id, 'us_og_image', $matches[1] );
			} else {
				delete_post_meta( $post_id, 'us_og_image' );
			}
		}
	}
}

if (
	! function_exists( 'us_term_custom_fields' )
	AND ! function_exists( 'us_save_term_custom_fields' )
) {

	/**
	 * Add custom fields to terms of taxonomies on the "Edit" admin screen
	 *
	 * @param object $term Term object
	 */
	function us_term_custom_fields( $term ) {
		$misc = us_config( 'elements_misc' );

		/**
		 * @var bool
		 */
		$is_public = TRUE;

		// The taxonomy publication validation
		if ( $taxonomy = get_taxonomy( $term->taxonomy ) ) {
			$is_public = $taxonomy->public;
		}

		// First default option
		$default_option = array( '__defaults__' => sprintf( '&ndash; %s &ndash;', __( 'As in Theme Options', 'us' ) ) );

		// Get all areas
		$areas = array(
			'header' => array(
				'misc_key_prefix' => 'headers',
				'options' => $default_option + us_get_posts_titles_for( 'us_header' ),
				'title' => _x( 'Header', 'site top area', 'us' ),
			),
			'content' => array(
				'misc_key_prefix' => 'content',
				'options' => $default_option + us_get_posts_titles_for( 'us_content_template' ),
				'title' => __( 'Page Template', 'us' ),
			),
			'footer' => array(
				'misc_key_prefix' => 'footers',
				'options' => $default_option + us_get_posts_titles_for( 'us_page_block' ),
				'title' => __( 'Footer', 'us' ),
			),
		);

		// All values
		$values = array();

		// Set default value for "Pages Layout"
		foreach ( array_keys( $areas ) as $area ) {
			$area_key = sprintf( 'pages_%s_id', $area );
			if ( ! $values[ $area_key ] = get_term_meta( $term->term_id, $area_key, TRUE ) ) {
				$values[ $area_key ] = '__defaults__';
			}
		}

		// Output "Arhive" setting, only if the taxonomy is available for frontend visitors
		if ( $tax = get_taxonomy( $term->taxonomy ) AND $tax->publicly_queryable ) {
			// Set default value for "Arhive Layout"
			foreach ( array_keys( $areas ) as $area ) {
				$area_key = sprintf( 'archive_%s_id', $area );
				if ( ! $values[ $area_key ] = get_term_meta( $term->term_id, $area_key, TRUE ) ) {
					$values[ $area_key ] = '__defaults__';
				}
			}
		}

		// Output "Arhive" setting, only if the taxonomy is available for frontend visitors
		if ( $tax = get_taxonomy( $term->taxonomy ) AND $tax->publicly_queryable ) { ?>

		<!-- Begin UpSolution meta settings for Archive -->
		<tr class="us-term-meta-title">
			<td colspan="2">
				<?= strip_tags( __( 'Archives Layout', 'us' ) ) ?>
			</td>
		</tr>
		<?php foreach( $areas as $area_name => $area ): ?>
		<?php $area_key = esc_attr( sprintf( 'archive_%s_id', $area_name ) ); ?>
		<tr class="form-field term-display-<?= $area_key ?>-wrap">
			<th scope="row" valign="top">
				<label for="<?= $area_key ?>">
					<?= strip_tags( us_arr_path( $area, 'title', '' ) ) ?>
				</label>
			</th>
			<td>
				<select id="<?= $area_key ?>" name="<?= $area_key ?>" class="postform">
					<?php foreach ( (array) us_arr_path( $area, 'options', array() ) as $value => $name ): ?>
						<option value="<?= esc_attr( $value ) ?>" <?php selected( $value, us_arr_path( $values, $area_key, '' ) ) ?>>
							<?= strip_tags( $name ) ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					<?= sprintf( __( 'Will apply to the "%s" archive page.', 'us' ), $term->name ) ?>
					<?php if ( $misc_description = us_arr_path( $misc, $area['misc_key_prefix'] . '_description' ) ): ?>
						<br><?= $misc_description ?>
					<?php endif; ?>
				</p>
			</td>
		</tr>
		<?php endforeach; ?>
		<!-- End UpSolution meta settings for Archive -->

		<?php } ?>

		<!-- Begin UpSolution meta settings for Pages -->
		<tr class="us-term-meta-title">
			<td colspan="2">
				<?= strip_tags( __( 'Pages Layout', 'us' ) ) ?>
			</td>
		</tr>
		<?php foreach( $areas as $area_name => $area ): ?>
		<?php $area_key = esc_attr( sprintf( 'pages_%s_id', $area_name ) ); ?>
		<tr class="form-field term-display-<?= $area_key ?>-wrap">
			<th scope="row" valign="top">
				<label for="<?= $area_key ?>">
					<?= strip_tags( us_arr_path( $area, 'title', '' ) ) ?>
				</label>
			</th>
			<td>
				<select id="<?= $area_key ?>" name="<?= $area_key ?>" class="postform">
					<?php foreach ( (array) us_arr_path( $area, 'options', array() ) as $value => $name ): ?>
						<option value="<?= esc_attr( $value ) ?>" <?php selected( $value, us_arr_path( $values, $area_key, '' ) ) ?>>
							<?= strip_tags( $name ) ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					<?= sprintf( __( 'Will apply to all pages with the "%s" taxonomy.', 'us' ), $term->name ) ?>
				</p>
			</td>
		</tr>
		<?php endforeach; ?>
		<!-- End UpSolution meta settings for Pages -->

		<!-- Begin UpSolution meta settings -->
		<?php if ( us_get_option( 'og_enabled' ) AND $is_public ) {
			$seo_meta_fields = us_config( 'seo-meta-fields', array() );
			foreach ( array_keys( $seo_meta_fields ) as $meta_key ) {
				$$meta_key = get_term_meta( $term->term_id, $meta_key, TRUE );
			}
		?>
		<tr class="us-term-meta-title">
			<td colspan="2"><?= __( 'SEO meta tags', 'us' ) ?></td>
		</tr>
		<?php foreach ( $seo_meta_fields as $meta_key => $meta_options ) { ?>
		<tr class="form-field term-<?= $meta_key ?>-wrap">
			<th scope="row" valign="top">
				<?php if ( ! empty( $meta_options['title'] ) ){ ?>
				<label for="<?php esc_attr_e( $meta_key ) ?>">
					<?= strip_tags( $meta_options['title'] ) ?>
				</label>
				<?php } ?>
			</th>
			<td>
				<?php $_atts = array(
					'type' => 'text',
					'id' => $meta_key,
					'name' => $meta_key,
				); ?>
				<?php if ( $meta_options['type'] === 'text' ) { ?>
					<input<?= us_implode_atts( array_merge( $_atts, array( 'value' => $$meta_key ) ) ) ?> >
				<?php } else { ?>
					<textarea<?= us_implode_atts( array_merge( $_atts, array(
						'rows' => 5,
						'cols' => 50,
						'class' => 'large-text',
					) ) ) ?>><?= $$meta_key ?></textarea>
				<?php } ?>
				<?php if ( ! empty( $meta_options['description'] ) ) { ?>
					<p class="description"><?= $meta_options['description'] ?></p>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
		<script type="text/javascript">
			;(function( $, undefined ) {
				$( '.usof-example' ).on( 'click', function( e ) {
					var $target = $( e.currentTarget );
					$target
						.closest( 'tr' )
						.find( 'input[type="text"], textarea' )
						.val( $target.text() );
				} );
			})(jQuery);
		</script>
		<!-- End UpSolution meta settings -->

		<?php }
	}

	/**
	 * Save terms custom fields
	 *
	 * @param mixed $term_id Term ID being saved.
	 */
	function us_save_term_custom_fields( $term_id ) {
		// Get keys for SEO Meta Fields
		$meta_keys = array_keys( us_config( 'seo-meta-fields', array() ) );

		// Get all layout keys
		foreach( array( 'header', 'content', 'footer' ) as $area ) {
			$meta_keys = array_merge( $meta_keys, array(
				sprintf( 'pages_%s_id', $area ),
				sprintf( 'archive_%s_id', $area ),
			) );
		}

		// Metadata updates if there are values
		foreach ( array_unique( $meta_keys ) as $meta_key ) {
			if ( ! isset( $_POST[ $meta_key ] ) ) {
				continue;
			};
			$meta_value = esc_attr( $_POST[ $meta_key ] );
			if ( $meta_value === '__defaults__' ) {
				// Remove metadata on default value
				delete_term_meta( $term_id, $meta_key );
			} else {
				update_term_meta( $term_id, $meta_key, $meta_value );
			}
		}
	}

	// Action assignments for all available taxonomies
	add_action( 'init', function () {
		foreach ( array_keys( us_get_taxonomies() ) as $tax_slug ) {
			add_action( "{$tax_slug}_edit_form_fields", 'us_term_custom_fields', 9 );
			add_action( "edited_{$tax_slug}", 'us_save_term_custom_fields', 10, 3 );
		}
	} );
}
