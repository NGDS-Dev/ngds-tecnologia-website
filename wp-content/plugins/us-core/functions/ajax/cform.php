<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Ajax method for sending contact form via us_cform shortcode
 */
if ( ! function_exists( 'us_ajax_cform' ) ) {
	add_action( 'wp_ajax_nopriv_us_ajax_cform', 'us_ajax_cform' );
	add_action( 'wp_ajax_us_ajax_cform', 'us_ajax_cform' );

	/**
	 * The handler of the received data from the Contact Form
	 */
	function us_ajax_cform() {
		$post_id = (int) us_arr_path( $_POST, 'post_id', /* Default */0 );
		if ( $post_id <= 0 ) {
			wp_send_json_error();
		}
		if ( ! $post = get_post( $post_id ) ) {
			wp_send_json_error();
		}

		// Get the serial number of a form on a page
		$form_index = (int) us_arr_path( $_POST, 'form_index', /* Default */1 );

		// Retrieving the relevant shortcode from the page to get options
		$post_content = $post->post_content;
		preg_match_all( '~(\[us_cform(.*?)\])((.*?)\[/us_cform\])?~', $post_content, $matches );

		if ( ! isset( $matches[0][ $form_index - 1 ] ) ) {
			wp_send_json_error();
		}

		// Getting the relevant shortcode options
		$shortcode = $matches[1][ $form_index - 1 ];

		// For proper shortcode_parse_atts behaviour
		$shortcode = substr_replace( $shortcode, ' ]', - 1 );
		$shortcode_atts = shortcode_parse_atts( $shortcode );

		// Compatibility with older versions (applying migrations)
		if ( class_exists( 'US_Migration' ) ) {
			foreach ( US_Migration::instance()->translators as $version => $translator ) {
				if ( method_exists( $translator, 'translate_us_cform' ) ) {
					$translator->translate_us_cform( 'us_cform', $shortcode_atts );
				}
			}
		}

		// Take all field types from config
		$available_fields = us_config( 'elements/cform.params.items.params.type.options' );
		$field_types = is_array( $available_fields ) ? array_keys( $available_fields ) : array();

		// Decode shortcode items
		$shortcode_items = json_decode( urldecode( $shortcode_atts['items'] ), TRUE );

		// Default shortcode has no content, take it from config
		if ( empty( $shortcode_items ) ) {
			$shortcode_items = json_decode( urldecode( us_config( 'elements/cform.params.items.std' ) ), TRUE );
		}

		$shortcode_items = $shortcode_items ? $shortcode_items : array();

		$fields_content = $from_email = $from_name = '';
		$errors = $headers = $existing_fields = array();

		// Validate fields and compose a message
		foreach( $shortcode_items as $field ) {
			$field_type = us_arr_path( $field, 'type' );

			// Check if the field type is correct
			if ( ! in_array( $field_type, $field_types ) ) {
				continue;
			}

			// Skip info field
			if ( $field_type == 'info' ) {
				continue;
			}

			// Set Agreement Box and Captcha to be always required
			if ( $field_type == 'agreement' OR $field_type == 'captcha' ) {
				$field['required'] = 1;
			}

			// Get a unique field name
			$field_uniqid = $form_index . '_' . $field_type;
			if ( ! isset( $existing_fields[ $field_uniqid ] ) ) {
				$existing_fields[ $field_uniqid ] = 0;
			}
			$existing_fields[ $field_uniqid ] += 1;
			$name = 'us_form_' . $field_uniqid . '_' . $existing_fields[ $field_uniqid ];

			// Use email field value inside "FROM: email"
			if (
				$field_type === 'email'
				AND ! empty( $field['is_used_as_from_email'] )
				AND ! empty( $_POST[ $name ] )
				AND is_email( $_POST[ $name ] )
			) {
				$from_email = sanitize_email( $_POST[ $name ] );
			}

			// Use text field value inside "FROM: name"
			if (
				$field_type === 'text'
				AND ! empty( $field['is_used_as_from_name'] )
				AND ! empty( $_POST[ $name ] )
			) {
				$from_name = sanitize_text_field( $_POST[ $name ] );
			}

			// Check if fields are required
			if ( ! empty( $field['required'] ) AND $name ) {
				if ( $field_type === 'captcha' ) {
					$captcha_value = isset( $_POST[ $name ] ) ? esc_attr( $_POST[ $name ] ) : NULL;
					if ( ! us_cform_is_valid_captcha( $captcha_value ) ) {
						$errors[ $name ]['error_message'] = __( 'Enter the equation result to proceed', 'us' );
					}

					// For file fields
				} elseif ( $field_type === 'file' ) {
					if ( empty( $_FILES[ $name ] ) ) {
						$errors[ $name ]['error_message'] = __( 'Fill out this field', 'us' );
					}

				} elseif ( ! isset( $_POST[ $name ] ) OR $_POST[ $name ] === '' ) {
					$errors[ $name ]['error_message'] = __( 'Fill out this field', 'us' );
				}
			}

			// Validation of file field
			if (
				$field_type === 'file'
				AND isset( $_FILES[ $name ] )
				AND us_arr_path( $_FILES, $name . '.error' ) === 0
			) {
				// File extension validation
				if ( ! us_cform_is_allowed_extensions( $_FILES[ $name ], $field['accept'] ) ) {
					$file_extension = us_strtolower( pathinfo( $_FILES[ $name ]['name'], PATHINFO_EXTENSION ) );
					$errors[ $name ]['error_message'] = sprintf( __( '%s file type is not allowed', 'us' ), $file_extension );
				}

				// If the size is not set, set the default
				if ( ! $file_max_size = (int) us_arr_path( $field, 'file_max_size' ) ) {
					$file_max_size = 10;
				}

				/**
				 * Get the size of the uploaded file in megabytes
				 * @var int
				 */
				$current_file_size = ceil( (int) $_FILES[ $name ]['size'] / 1048576 /* kb = 1mb */ );
				if ( $current_file_size > $file_max_size ) {
					$errors[ $name ]['error_message'] = sprintf( __( 'File size cannot exceed %s MB', 'us' ), $file_max_size );
				}
			}

			// Skip fields, which shouldn't have a text content
			if ( in_array( $field_type, array( 'captcha', 'file' ) ) ) {
				continue;
			}

			// Generate a message content
			if ( $field_type == 'agreement' AND ! empty( $field['value'] ) ) {
				$agreement_content = '' . __( 'The sender has given his consent.', 'us' ) . '<br>';
				$agreement_content .= __( 'Agreement text', 'us' ) . ': <strong>' . strip_tags( $field['value'], '<a>' ) . '</strong><br>';
				$agreement_content .= __( 'Agreement date and time', 'us' ) . ': <strong>' . gmdate( 'Y-m-d H:i:s' ) . ' GMT</strong><br>';
				$agreement_content .= __( 'IP address', 'us' ) . ': <strong>' . us_get_ip() . '</strong>';

			} else {
				$fields_content .= '<li>';

				if ( $label = us_arr_path( $field, 'label' ) ) {
					$fields_content .= sanitize_text_field( $label ) . ': ';
				} elseif ( $placeholder = us_arr_path( $field, 'placeholder' ) ) {
					$fields_content .= sanitize_text_field( $placeholder ) . ': ';
				}

				$mail_content = isset( $_POST[ $name ] ) ? $_POST[ $name ] : '';

				if ( is_array( $mail_content ) ) {
					$values_length = count( $mail_content );
					$counter = 0;
					foreach ( $mail_content as $value ) {
						$fields_content .= '<strong>' . wp_strip_all_tags( stripslashes( $value ) ) . '</strong>';
						$counter ++;
						if ( $counter < $values_length ) {
							$fields_content .= ', ';
						}
					}

				} elseif ( ! empty( $mail_content ) ) {
					$mail_content = wp_strip_all_tags( stripslashes( $mail_content ) );

					// Replace line breaks with <br> for correct appearance in HTML
					$fields_content .= '<strong>' . nl2br( $mail_content, FALSE ) . '</strong>';

					// Add the provided email as into the "reply-to"
					if ( $field_type == 'email' ) {
						$headers[] = 'Reply-To: ' . sanitize_email( stripslashes( $mail_content ) );
					}

				} else {
					$fields_content .= '-';
				}

				$fields_content .= '</li>';
			}
		}

		if ( ! empty( $errors ) ) {
			if ( us_amp() ) {
				$message = sprintf( us_translate( 'Required fields are marked %s' ), '*' );
				wp_send_json( compact( 'message' ), 400 );
			} else {
				wp_send_json_error( $errors );
			}
		}

		// Get the title and link from the page where the form is located
		$page_title = $post->post_title;
		$page_link = get_the_permalink( $post_id );

		if (
			$queried_object_id = us_arr_path( $_POST, 'queried_object_id' )
			AND $queried_object_type = us_arr_path( $_POST, 'queried_object_type' )
		) {
			if ( $queried_object_type == 'term' AND $term = get_term( $queried_object_id ) ) {
				$page_title = $term->name;
				$page_link = get_term_link( $term->term_id );
			} elseif ( $queried_object_type == 'author' AND $user = get_userdata( $queried_object_id ) ) {
				$page_title = $user->display_name;
				$page_link = get_author_posts_url( $user->ID );
			} elseif ( $queried_object_type == 'post_type' AND $post_type = get_post_type_object( $queried_object_id ) ) {
				$page_title = $post_type->label;
				$page_link = get_post_type_archive_link( $post_type->name );
			} elseif ( $post = get_post( $queried_object_id ) ) {
				$page_title = $post->post_title;
				$page_link = get_the_permalink( $post->ID );
			}
		}

		// Generate the mail content
		$mail_body = '<p>';
		$mail_body .= sprintf( __( 'This message was sent from the %s page.', 'us' ), '<a href="' . esc_url( $page_link ) . '">' . strip_tags( $page_title ) . '</a>' );
		$mail_body .= '</p>';
		$mail_body .= '<ul>';
		$mail_body .= $fields_content;
		$mail_body .= '</ul>';
		$mail_body .= isset( $agreement_content ) ? $agreement_content : '';

		if ( is_rtl() ) {
			$mail_body = '<div style="direction: rtl; unicode-bidi: embed;">' . $mail_body . '</div>';
		}

		// Get Subject from Contact Form settings
		$mail_subject = ! empty( $shortcode_atts['email_subject'] )
			? $shortcode_atts['email_subject']
			: sprintf( __( 'Message from %s', 'us' ), strip_tags( $page_title ) );

		// Decode special characters
		$mail_subject = htmlspecialchars_decode( $mail_subject, ENT_HTML5 | ENT_QUOTES );

		// Get email recipient
		$mail_to = get_option( 'admin_email' );
		if ( ! empty( $shortcode_atts['receiver_email'] ) ) {
			$mail_to = array_map( 'sanitize_email', explode( ',', $shortcode_atts['receiver_email'] ) );
		}

		// Change the "From" value
		if ( ! empty( $from_email ) ) {
			$headers[] = "From: $from_name <$from_email>";
		}
		if ( empty( $from_name ) ) {
			add_filter( 'wp_mail_from_name', 'us_cfrom_mail_from_name' );
		}

		// Add BCC email
		if (
			! empty( $shortcode_atts['bcc_email'] )
			AND $bcc_emails = array_map( 'sanitize_email', explode( ',', $shortcode_atts['bcc_email'] ) )
		) {
			$headers[] = 'bcc: ' . implode( ',', $bcc_emails );
		}

		// Change content type of email to support HTML tags
		$headers[] = 'content-type: text/html';

		// List of attached files
		$mail_attachments = array();
		if ( ! empty( $_FILES ) ) {
			foreach( $_FILES as &$attachment ) {
				/*
				 * @see https://developer.wordpress.org/reference/functions/wp_handle_upload/#top
				 */
				$uploaded_attachment = wp_handle_upload( $attachment, array( 'test_form' => FALSE ) );
				if (
					isset( $uploaded_attachment['file'] )
					AND file_exists( $uploaded_attachment['file'] )
				) {
					$mail_attachments[] = (string) $uploaded_attachment['file'];
				}
			}
		}
		unset( $attachment );

		// Send attempt
		$success = wp_mail( $mail_to, $mail_subject, $mail_body, $headers, $mail_attachments );

		// Delete attachments from the server
		foreach( $mail_attachments as $attachment ) {
			if ( file_exists( $attachment ) ) {
				wp_delete_file( $attachment );
			}
		}

		if ( $success ) {
			if ( ! empty( $shortcode_atts['success_message'] ) ) {
				$success_message = $shortcode_atts['success_message'];
			} else {
				$success_message = us_config( 'elements/cform.params.success_message.std' );
			}

			// If the message has base64 format, decode it
			if ( $message = base64_decode( $success_message, TRUE ) ) {
				$message = rawurldecode( $message );
			} else {
				$message = $success_message;
			}

			if ( us_amp() ) {
				wp_send_json( compact( 'message' ), 200 );
			} else {
				wp_send_json_success( $message );
			}
		} else {
			$message = __( 'Cannot send the message. Please contact the website administrator.', 'us' );
			if ( us_amp() ) {
				wp_send_json( compact( 'message' ), 400 );
			} else {
				wp_send_json_error( $message );
			}
		}
	}
}

if ( ! function_exists( 'us_cform_is_allowed_extensions' ) ) {
	/**
	 * Check file for allowed extension.
	 * Note: Extension check function is based on standard HTML5 file accept
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/accept
	 *
	 * @param array $file The file information
	 * @param string $accepts The allowed file types
	 * @return bool Returns True on success, False otherwise.
	 */
	function us_cform_is_allowed_extensions( $file, $accepts = '' ) {
		if ( empty( $accepts ) ) {
			return TRUE;
		}
		if ( empty( $file ) OR us_arr_path( $file, 'error' ) !== 0 ) {
			return FALSE;
		}

		// Get allowed extensions or mime types
		$accepts = array_map( 'trim', explode( ',', us_strtolower( $accepts ) ) );

		// Get file extension from name.
		$file['extension'] = '.' . us_strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		// Determination if file extension is available in the accepts
		if ( in_array( $file['extension'], $accepts ) ) {
			return TRUE;
		}

		foreach ( $accepts as $accept ) {
			if ( empty( $accept ) ) {
				continue;
			}
			// @link https://mimesniff.spec.whatwg.org
			if ( strpos( $accept, '/' ) !== FALSE ) {
				$accept_matches = explode( '/', $accept, /* min limit */2 );
				if (
					$accept === $file['type']
					OR (
						$accept_matches[1] === '*'
						AND strpos( $file['type'], $accept_matches[0] ) === 0
					)
				) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}
}

if ( ! function_exists( 'us_cform_is_valid_captcha' ) ) {
	/**
	 * Captcha validation
	 *
	 * @param string $value The captcha value
	 * @return bool True if successful, false otherwise
	 */
	function us_cform_is_valid_captcha( $value = NULL ) {
		$fields = array();
		foreach ( $_POST as $key => $field ) {
			if ( preg_match( '~^us_form_\d_([^_]+_)\d_(\w+)$~', $key, $matches ) ) {
				$fields[ $matches[1] . $matches[2] ] = $field;
			} elseif ( preg_match( '~^us_form_\d_([^_]+)_\d$~', $key, $matches ) ) {
				$fields[ $matches[1] ] = $field;
			}
		}
		if ( $hash = us_arr_path( $fields, 'captcha_hash', /* Default */NULL ) ) {
			$hash = stripslashes( $hash );
		}
		return $hash === md5( $value . NONCE_SALT );
	}
}

/**
 * Use the website name instead of the "WordPress" word in the "From:"
 */
if ( ! function_exists( 'us_cfrom_mail_from_name' ) ) {
	function us_cfrom_mail_from_name( $from_name ) {
		return get_bloginfo();
	}
}
