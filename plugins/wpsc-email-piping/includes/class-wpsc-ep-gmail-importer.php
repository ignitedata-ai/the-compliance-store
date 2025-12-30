<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Gmail_Importer' ) ) :

	final class WPSC_EP_Gmail_Importer {

		/**
		 * Gmail settings
		 *
		 * @var array
		 */
		private static $gmail;

		/**
		 * Access token to be used
		 *
		 * @var array
		 */
		private static $access_token;

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {}

		/**
		 * Import IMAP emails
		 *
		 * @return void
		 */
		public static function import() {

			$gmail = get_option( 'wpsc-ep-gmail-settings' );
			if ( ! intval( $gmail['is-active'] ) ) {
				return;
			}

			self::$gmail = $gmail;

			// get access token using refresh token.
			$response = wp_remote_post(
				'https://www.googleapis.com/oauth2/v4/token',
				array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(),
					'body'        => array(
						'client_id'     => $gmail['client-id'],
						'client_secret' => $gmail['client-secret'],
						'refresh_token' => $gmail['refresh-token'],
						'grant_type'    => 'refresh_token',
					),
					'cookies'     => array(),
				)
			);

			if ( is_wp_error( $response ) ) {
				return;
			}
			$access             = json_decode( $response['body'], true );
			self::$access_token = $access['access_token'];

			// get new messeges.
			$response = wp_remote_post(
				'https://www.googleapis.com/gmail/v1/users/' . $gmail['email-address'] . '/history',
				array(
					'method'      => 'GET',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(),
					'body'        => array(
						'access_token'   => self::$access_token,
						'startHistoryId' => $gmail['history-id'],
						'historyTypes'   => 'messageAdded',
						'labelId'        => 'INBOX',
					),
					'cookies'     => array(),
				)
			);

			if ( is_wp_error( $response ) ) {
				return;
			}
			$history = json_decode( $response['body'], true );
			if ( ! isset( $history['history'] ) ) {
				return;
			}

			$counter = 0;
			foreach ( $history['history'] as $history_item ) {

				$gmail['history-id'] = intval( $history_item['id'] );
				update_option( 'wpsc-ep-gmail-settings', $gmail );

				foreach ( $history_item['messagesAdded'] as $messege ) {

					if ( ! isset( $messege['message'] ) ) {
						return;
					}
					$messege_id = $messege['message']['id'];

					$response = wp_remote_post(
						'https://www.googleapis.com/gmail/v1/users/' . $gmail['email-address'] . '/messages/' . $messege_id,
						array(
							'method'      => 'GET',
							'timeout'     => 45,
							'redirection' => 5,
							'httpversion' => '1.0',
							'blocking'    => true,
							'headers'     => array(),
							'body'        => array(
								'access_token' => self::$access_token,
							),
							'cookies'     => array(),
						)
					);

					if ( is_wp_error( $response ) ) {
						return;
					}
					$messege = json_decode( $response['body'], true );
					$email   = self::prepare_email( $messege['payload'], $messege_id );
					$email->pipe();
				}

				++$counter;
				if ( $counter == 5 ) {
					break;
				}
			}
		}

		/**
		 * Prepare email for pipe
		 *
		 * @param array $payload - payload.
		 * @param array $messege_id - id.
		 * @return object
		 */
		private static function prepare_email( $payload, $messege_id ) {

			$headers = $payload['headers'];
			$email   = new WPSC_EP_Email();

			// retrive "to addresses".
			$email->to_addresses = self::get_to_addresses( $headers );

			// retrive "cc addreeses".
			$email->cc_addresses = self::get_cc_addresses( $headers );

			// from name & email.
			$email = self::set_from_user( $email, $headers );

			// subject.
			$email->subject = self::get_header( $headers, 'Subject' );

			// email body (plain text and html).
			$email = self::set_body( $email, $payload );

			// attachments.
			$email->attachments = self::get_attachments( $payload, $messege_id, $email );

			// source.
			$email->source = 'gmail';

			$index = array_search( 'Message-ID', array_column( $headers, 'name' ) );
			$email->message_id = intval( $index ) ? $headers [ $index ]['value'] : $headers [ array_search( 'Message-Id', array_column( $headers, 'name' ) ) ]['value'];

			return apply_filters( 'wpsc_gmail_parsed_email', $email );
		}

		/**
		 * Parse specific header from headers
		 *
		 * @param array  $headers - headers.
		 * @param string $name - name.
		 * @return array
		 */
		private static function get_header( $headers, $name ) {

			foreach ( $headers as $header ) {

				if ( $header['name'] == $name ) {
					return $header['value'];
				}
			}
		}

		/**
		 * Decode encloded body parts
		 *
		 * @param array $body - body.
		 * @return string
		 */
		private static function decode_body( $body ) {

			$raw_data        = $body;
			$sanitized_data  = strtr( $raw_data, '-_', '+/' );
			$decoded_message = base64_decode( $sanitized_data );
			if ( ! $decoded_message ) {
				$decoded_message = false;
			}
			return $decoded_message;
		}

		/**
		 * Return "to addresses"
		 *
		 * @param array $headers - header.
		 * @return string
		 */
		private static function get_to_addresses( $headers ) {

			$to_addresses = array();
			$text         = self::get_header( $headers, 'To' );
			preg_match_all( '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i', $text, $matches );
			foreach ( $matches[0] as $email_address ) {
				$to_addresses[] = $email_address;
			}
			return $to_addresses;
		}

		/**
		 * Return "CC addresses"
		 *
		 * @param array $headers - header.
		 * @return string
		 */
		private static function get_cc_addresses( $headers ) {

			$cc_addresses = array();
			$text         = self::get_header( $headers, 'Cc' );
			preg_match_all( '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i', $text, $matches );
			foreach ( $matches[0] as $email_address ) {
				$cc_addresses[] = $email_address;
			}

			$text = self::get_header( $headers, 'CC' );
			preg_match_all( '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i', $text, $matches );
			foreach ( $matches[0] as $email_address ) {
				$cc_addresses[] = $email_address;
			}

			return $cc_addresses;
		}

		/**
		 * Set "From Name" and "From Email"
		 *
		 * @param object $email - email object.
		 * @param array  $headers - header.
		 * @return object
		 */
		private static function set_from_user( $email, $headers ) {

			$text = self::get_header( $headers, 'From' );
			$email->reply_to = self::get_header( $headers, 'Reply-To' );
			if (
				preg_match( '/^"([\s\S]+)"\s?<([^>]+)>$/i', $text, $matches ) || // "John Doe" <john@doe.org>.
				preg_match( '/^([\s\S]+)\s?<([^>]+)>$/i', $text, $matches )      // John Doe <john@doe.org>.
			) {
				$email->from_name = trim( $matches[1] );
				$from_email = $email->reply_to ? $email->reply_to : trim( $matches[2] );
				$email->from_email = trim( str_replace( array( '<', '>' ), '', $from_email ) );
			} else { // john@doe.org, <john@doe.org>.
				preg_match( '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i', $text, $matches );
				$plain_email = $email->reply_to ? $email->reply_to : $matches[0];
				$plain_email = trim( str_replace( array( '<', '>' ), '', $plain_email ) );
				$email->from_name = $plain_email;
				$email->from_email = $plain_email;
			}

			return $email;
		}

		/**
		 * Set email body (plain text and html)
		 *
		 * @param object $email - email object.
		 * @param array  $payload - payload.
		 * @return object
		 */
		private static function set_body( $email, $payload ) {

			$found_body = array(
				'text' => '',
				'html' => '',
			);

			$parts = isset( $payload['parts'] ) ? $payload['parts'] : array();

			// parse html.
			foreach ( $parts as $part ) {

				if ( $part['mimeType'] === 'text/html' && $part['body'] ) {
					$found_body['html'] = self::decode_body( $part['body']['data'] );
					break;
				}

				if ( isset( $part['parts'] ) ) {

					foreach ( $part['parts'] as $p ) {

						if ( $p['mimeType'] === 'text/html' && $p['body'] ) {
							$found_body['html'] = self::decode_body( $p['body']['data'] );
							break;
						}

						if ( $found_body['html'] ) {
							break;
						}
						if ( isset( $p['parts'] ) ) {
							foreach ( $p['parts'] as $pt ) {

								if ( $pt['mimeType'] === 'text/html' && $pt['body'] ) {
									$found_body['html'] = self::decode_body( $pt['body']['data'] );
									break;
								}

								if ( $found_body['html'] ) {
									break;
								}
							}
						}
					}
				}

				if ( $found_body['html'] ) {
					break;
				}
			}

			// parse plain text.
			foreach ( $parts as $part ) {

				if ( $part['mimeType'] === 'text/plain' && $part['body'] ) {

					$found_body['text'] = self::decode_body( $part['body']['data'] );
					break;
				}

				if ( isset( $part['parts'] ) ) {

					foreach ( $part['parts'] as $p ) {

						if ( $p['mimeType'] === 'text/plain' && $p['body'] ) {
							$found_body['text'] = self::decode_body( $p['body']['data'] );
							break;
						}

						if ( $found_body['text'] ) {
							break;
						}

						if ( isset( $p['parts'] ) ) {
							foreach ( $p['parts'] as $pt ) {
								if ( $pt['mimeType'] === 'text/plain' && $pt['body'] ) {
									$found_body['text'] = self::decode_body( $pt['body']['data'] );
									break;
								}

								if ( $found_body['text'] ) {
									break;
								}
							}
						}
					}
				}
			}

			if ( ! $found_body['text'] && ! $found_body['html'] ) {

				$body         = $payload['body'];
				$body_content = isset( $body['data'] ) ? self::decode_body( $body['data'] ) : '';

				if ( ! ( strpos( $body_content, '<html' ) > -1 || strpos( $body_content, '<body' ) > -1 ) ) {
					$found_body['text'] = $body_content;
				} else {
					$found_body['html'] = $body_content;
				}
			}

			$email->text_body = $found_body['text'];
			$email->html_body = $found_body['html'];
			return $email;
		}

		/**
		 * Return attachments for email
		 *
		 * @param array         $payload - payload.
		 * @param array         $messege_id - id.
		 * @param WPSC_EP_Email $email - email object.
		 * @return array
		 */
		private static function get_attachments( $payload, $messege_id, $email ) {

			$attachment_ids = array();
			$parts          = isset( $payload['parts'] ) ? $payload['parts'] : array();

			$general = get_option( 'wpsc-ep-general-settings' );

			foreach ( $parts as $part ) {

				if ( isset( $part['filename'] ) && $part['filename'] ) {
					$file_name        = $part['filename'];
					$mime_type        = $part['mimeType'];
					$attachment_id    = $part['body']['attachmentId'];

					$key = array_search( 'Content-Id', array_column( $part['headers'], 'name' ) );
					if ( ! $key ) {
						$key = array_search( 'Content-ID', array_column( $part['headers'], 'name' ) );
					}

					$content_id = '';
					if ( ! empty( $key ) || $key > 0 ) {
						$content_id = $part['headers'][ $key ]['value'];
						$content_id = trim( str_replace( array( '<', '>' ), array( '', '' ), $content_id ) );
					}

					if ( $general['body-reference'] == 'html' && $content_id && preg_match( '/cid:' . $content_id . '/', $email->html_body ) ) { //phpcs:ignore
						self::upload_embeded_img( $file_name, $attachment_id, $messege_id, $email, $content_id );
					} else {
						$attachment_ids[] = self::get_attachment_id( $file_name, $attachment_id, $messege_id );
					}
				}

				if ( isset( $part['parts'] ) ) {

					foreach ( $part['parts'] as $prt ) {
						if ( isset( $prt['filename'] ) && $prt['filename'] ) {
							$file_name        = $prt['filename'];
							$attachment_id    = $prt['body']['attachmentId'];

							$key = array_search( 'Content-Id', array_column( $prt['headers'], 'name' ) );
							if ( ! $key ) {
								$key = array_search( 'Content-ID', array_column( $part['headers'], 'name' ) );
							}

							$content_id = '';
							if ( ! empty( $key ) || $key > 0 ) {
								$content_id = $prt['headers'][ $key ]['value'];
								$content_id = trim( str_replace( array( '<', '>' ), array( '', '' ), $content_id ) );
							}

							if ( $general['body-reference'] == 'html' && $content_id && preg_match( '/cid:' . $content_id . '/', $email->html_body ) ) { //phpcs:ignore
								self::upload_embeded_img( $file_name, $attachment_id, $messege_id, $email, $content_id );
							} else {
								$attachment_ids[] = self::get_attachment_id( $file_name, $attachment_id, $messege_id );
							}
						}
					}

					if ( isset( $prt['parts'] ) ) {

						foreach ( $prt['parts'] as $p1 ) {
							if ( isset( $p1['filename'] ) && $p1['filename'] ) {
								$file_name        = $p1['filename'];
								$attachment_id    = $p1['body']['attachmentId'];

								$key = array_search( 'Content-Id', array_column( $p1['headers'], 'name' ) );
								if ( ! $key ) {
									$key = array_search( 'Content-ID', array_column( $part['headers'], 'name' ) );
								}

								$content_id = '';
								if ( ! empty( $key ) || $key > 0 ) {
									$content_id = $p1['headers'][ $key ]['value'];
									$content_id = trim( str_replace( array( '<', '>' ), array( '', '' ), $content_id ) );
								}

								if ( $general['body-reference'] == 'html' && $content_id && preg_match( '/cid:' . $content_id . '/', $email->html_body ) ) { //phpcs:ignore
									self::upload_embeded_img( $file_name, $attachment_id, $messege_id, $email, $content_id );
								} else {
									$attachment_ids[] = self::get_attachment_id( $file_name, $attachment_id, $messege_id );
								}
							}
						}
					}
				}
			}

			return $attachment_ids;
		}

		/**
		 * Return WPSC Attachment id for parsed attachments
		 *
		 * @param array $file_name - file_name.
		 * @param array $attachment_id - attachment_id.
		 * @param array $messege_id - message_id.
		 * @return void
		 */
		private static function get_attachment_id( $file_name, $attachment_id, $messege_id ) {

			$response = wp_remote_post(
				'https://www.googleapis.com/gmail/v1/users/' . self::$gmail['email-address'] . '/messages/' . $messege_id . '/attachments/' . $attachment_id,
				array(
					'method'      => 'GET',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(),
					'body'        => array(
						'access_token' => self::$access_token,
					),
					'cookies'     => array(),
				)
			);

			if ( is_wp_error( $response ) ) {
				return;
			}
			$attachment = json_decode( $response['body'], true );

			// Database insert array init.
			$data = array( 'name' => $file_name );

			// saperate file name and extension.
			$filename  = explode( '.', $file_name );
			$extension = strtolower( $filename[ count( $filename ) - 1 ] );
			unset( $filename[ count( $filename ) - 1 ] );
			$filename = implode( '.', $filename );
			$filename = str_replace( ' ', '_', $filename );
			$filename = str_replace( ',', '_', $filename );
			$filename = uniqid() . '_' . preg_replace( '/[^A-Za-z0-9\-]/', '', $filename );

			// allowed file extenstions.
			$file_settings           = get_option( 'wpsc-gs-file-attachments' );
			$allowed_file_extensions = explode( ',', $file_settings['allowed-file-extensions'] );
			$allowed_file_extensions = array_map( 'trim', $allowed_file_extensions );
			$allowed_file_extensions = array_map( 'strtolower', $allowed_file_extensions );
			if ( ! ( in_array( $extension, $allowed_file_extensions ) ) ) {
				return;
			}

			// Check for image type.
			$img_extensions = array( 'png', 'jpeg', 'jpg', 'bmp', 'pdf', 'gif' );
			if ( ! in_array( $extension, $img_extensions ) ) {
				$data['is_image'] = 0;
			} else {
				$data['is_image'] = 1;
			}

			// Create file path.
			$today      = new DateTime();
			$upload_dir = wp_upload_dir();
			$filepath   = $upload_dir['basedir'] . '/wpsc/' . $today->format( 'Y' );
			if ( ! file_exists( $filepath ) ) {
				mkdir( $filepath, 0755, true );
			}
			$filepath .= '/' . $today->format( 'm' );
			if ( ! file_exists( $filepath ) ) {
				mkdir( $filepath, 0755, true );
			}
			$filepath .= '/' . $filename . '.' . $extension;

			$filepath_short = '/wpsc/' . $today->format( 'Y' ) . '/' . $today->format( 'm' ) . '/' . $filename . '.' . $extension;
			$data['file_path'] = $filepath_short;

			// Create time.
			$data['date_created'] = $today->format( 'Y-m-d H:i:s' );

			// write file.
			$file_contents = strtr(
				$attachment['data'],
				array(
					'-' => '+',
					'_' => '/',
				)
			);
			file_put_contents( $filepath, base64_decode( $file_contents ) ); //phpcs:ignore

			// insert db record and return id.
			$att = WPSC_Attachment::insert( $data );
			return $att->id;
		}

		/**
		 * Return WPSC Attachment id for parsed attachments
		 *
		 * @param array         $file_name - file_name.
		 * @param array         $attachment_id - attachment_id.
		 * @param array         $messege_id - message_id.
		 * @param WPSC_EP_Email $email - email object.
		 * @param string        $content_id - content id.
		 * @return void
		 */
		private static function upload_embeded_img( $file_name, $attachment_id, $messege_id, $email, $content_id ) {

			$response = wp_remote_post(
				'https://www.googleapis.com/gmail/v1/users/' . self::$gmail['email-address'] . '/messages/' . $messege_id . '/attachments/' . $attachment_id,
				array(
					'method'      => 'GET',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(),
					'body'        => array(
						'access_token' => self::$access_token,
					),
					'cookies'     => array(),
				)
			);

			if ( is_wp_error( $response ) ) {
				return;
			}
			$attachment = json_decode( $response['body'], true );

			// Database insert array init.
			$data = array( 'name' => $file_name );

			// saperate file name and extension.
			$filename  = explode( '.', $file_name );
			$extension = strtolower( $filename[ count( $filename ) - 1 ] );
			unset( $filename[ count( $filename ) - 1 ] );
			$filename = implode( '.', $filename );
			$filename = str_replace( ' ', '_', $filename );
			$filename = str_replace( ',', '_', $filename );
			$filename = uniqid() . '_' . preg_replace( '/[^A-Za-z0-9\-]/', '', $filename );

			// allowed file extenstions.
			$img_extensions = array( 'png', 'jpeg', 'jpg', 'gif' );
			if ( ! ( in_array( $extension, $img_extensions ) ) ) {
				self::get_attachment_id( $file_name, $attachment_id, $messege_id );
			}

			$data['is_image'] = 1;
			$data['source'] = 'img_editor';

			// Create file path.
			$today      = new DateTime();
			$upload_dir = wp_upload_dir();
			$filepath   = $upload_dir['basedir'] . '/wpsc/' . $today->format( 'Y' );
			if ( ! file_exists( $filepath ) ) {
				mkdir( $filepath, 0755, true );
			}
			$filepath .= '/' . $today->format( 'm' );
			if ( ! file_exists( $filepath ) ) {
				mkdir( $filepath, 0755, true );
			}
			$filepath .= '/' . $filename . '.' . $extension;

			$filepath_short = '/wpsc/' . $today->format( 'Y' ) . '/' . $today->format( 'm' ) . '/' . $filename . '.' . $extension;
			$data['file_path'] = $filepath_short;

			// Create time.
			$data['date_created'] = $today->format( 'Y-m-d H:i:s' );

			// write file.
			$file_contents = strtr(
				$attachment['data'],
				array(
					'-' => '+',
					'_' => '/',
				)
			);
			file_put_contents( $filepath, base64_decode( $file_contents ) ); //phpcs:ignore

			// insert db record and return id.
			$attach = WPSC_Attachment::insert( $data );
			if ( $attach->id ) {
				$img_url = home_url( '/' ) . '?wpsc_attachment=' . $attach->id;
				$email->html_body = str_replace( 'cid:' . $content_id, $img_url, $email->html_body ); //phpcs:ignore
			}
		}
	}
endif;

WPSC_EP_Gmail_Importer::init();
