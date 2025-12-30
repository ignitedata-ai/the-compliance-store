<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_ME_Importer' ) ) :

	final class WPSC_EP_ME_Importer {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// update refresh token.
			add_action( 'wpsc_cron_daily', array( __CLASS__, 'update_refresh_token' ) );
		}

		/**
		 * Import IMAP emails
		 *
		 * @return void
		 */
		public static function import() {

			$me = get_option( 'wpsc-ep-me-settings' );
			if ( ! intval( $me['is-active'] ) ) {
				return;
			}

			$access_token = WPSC_EP_Settings_ME::get_access_token();
			if ( false === $access_token ) {
				return;
			}

			// get delta messeges.
			$response = wp_remote_get(
				$me['delta-url'],
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
						'Prefer'        => 'odata.maxpagesize=10',
					),
				)
			);

			if ( ! WPSC_EP_Settings_ME::is_valid_response( $response ) ) {
				return;
			}

			$response = json_decode( $response['body'], true );
			$me['delta-url'] = isset( $response['@odata.deltaLink'] ) ? $response['@odata.deltaLink'] : $response['@odata.nextLink'];
			update_option( 'wpsc-ep-me-settings', $me );

			if ( $response['value'] ) {

				foreach ( $response['value'] as $messege ) {

					$email = new WPSC_EP_Email();

					// retrive "to addresses".
					$email->to_addresses = array_map(
						fn( $to ) => $to['emailAddress']['address'],
						$messege['toRecipients']
					);

					// retrive "cc addreeses".
					$email->cc_addresses = array_map(
						fn( $cc ) => $cc['emailAddress']['address'],
						$messege['ccRecipients']
					);

					// from name.
					$email->from_name = $messege['from']['emailAddress']['name'];

					// Reply to email.
					$email->reply_to = $messege['replyTo'] ? $messege['replyTo'][0]['emailAddress']['address'] : '';

					// from email.
					$email->from_email = $email->reply_to ? $email->reply_to : $messege['from']['emailAddress']['address'];

					// subject.
					$email->subject = $messege['subject'];

					// HTML body.
					$email->html_body = $messege['body']['contentType'] == 'html' ? $messege['body']['content'] : '';

					// Text body.
					$email->text_body = $messege['body']['contentType'] == 'text' ? $messege['body']['content'] : '';

					// Attachments.
					self::parse_attachments( $messege, $email );

					// source.
					$email->source = 'me';

					// message id.
					$email->message_id = $messege['internetMessageId'];

					$email = apply_filters( 'wpsc_me_parsed_email', $email );

					$email->pipe();
				}
			}
		}

		/**
		 * Parse attachments
		 *
		 * @param array         $messege - messege array.
		 * @param WPSC_EP_Email $email - email object.
		 * @return void
		 */
		private static function parse_attachments( $messege, $email ) {

			$access_token = WPSC_EP_Settings_ME::get_access_token();
			if ( false === $access_token ) {
				return;
			}

			// get messege attachments.
			$response = wp_remote_get(
				'https://graph.microsoft.com/v1.0/me/messages/' . $messege['id'] . '/attachments',
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
					),
				)
			);

			if ( ! WPSC_EP_Settings_ME::is_valid_response( $response ) ) {
				return;
			}

			$response = json_decode( $response['body'], true );
			foreach ( $response['value'] as $attachment ) {
				if ( $attachment['contentId'] && preg_match( '/cid:' . $attachment['contentId'] . '/', $email->html_body ) ) {
					self::upload_embeded_img( $attachment, $email );
				} else {
					self::upload_thread_attachment( $attachment, $email );
				}
			}
		}

		/**
		 * Upload embeded image in the description
		 *
		 * @param array         $attachment - attachment array received from microsoft exchange server.
		 * @param WPSC_EP_Email $email - email object.
		 * @return void
		 */
		private static function upload_embeded_img( $attachment, $email ) {

			$filename      = uniqid() . '_' . sanitize_file_name( $attachment['name'] );
			$extension     = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
			$today         = new DateTime();
			$upload_dir    = wp_upload_dir();

			// Check file extension.
			$img_extensions = array( 'jpg', 'jpeg', 'png', 'gif' );
			if ( ! in_array( $extension, $img_extensions ) ) {
				self::upload_thread_attachment( $attachment, $email );
			}

			// File path.
			$file_path = $upload_dir['basedir'] . '/wpsc/' . $today->format( 'Y' ) . '/' . $today->format( 'm' );
			if ( ! file_exists( $file_path ) ) {
				mkdir( $file_path, 0755, true );
			}
			$file_path .= '/' . $filename;

			// write file.
			$file_contents = strtr(
				$attachment['contentBytes'],
				array(
					'-' => '+',
					'_' => '/',
				)
			);
			file_put_contents( $file_path, base64_decode( $file_contents ) ); //phpcs:ignore

			$filepath_short = '/wpsc/' . $today->format( 'Y' ) . '/' . $today->format( 'm' ) . '/' . $filename;
			// Insert attachment record.
			$attach = WPSC_Attachment::insert(
				array(
					'name'         => sanitize_file_name( $attachment['name'] ),
					'file_path'    => $filepath_short,
					'is_image'     => 1,
					'date_created' => $today->format( 'Y-m-d H:i:s' ),
					'is_active'    => 0,
					'source'       => 'img_editor',
				)
			);

			if ( $attach->id ) {
				$img_url = home_url( '/' ) . '?wpsc_attachment=' . $attach->id;
				$email->html_body = str_replace( 'cid:' . $attachment['contentId'], $img_url, $email->html_body );
			}
		}

		/**
		 * Upload regular thread attachments
		 *
		 * @param array         $attachment - attachment array received from microsoft exchange server.
		 * @param WPSC_EP_Email $email - email object.
		 * @return void
		 */
		private static function upload_thread_attachment( $attachment, $email ) {

			$file_settings = get_option( 'wpsc-gs-file-attachments' );
			$filename      = uniqid() . '_' . sanitize_file_name( $attachment['name'] );
			$extension     = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
			$today         = new DateTime();
			$upload_dir    = wp_upload_dir();

			// Allowed file extension.
			$allowed_file_extensions = explode( ',', $file_settings['allowed-file-extensions'] );
			$allowed_file_extensions = array_map( 'trim', $allowed_file_extensions );
			$allowed_file_extensions = array_map( 'strtolower', $allowed_file_extensions );
			if ( ! ( in_array( $extension, $allowed_file_extensions ) ) ) {
				return;
			}

			// Init attachment data.
			$data = array(
				'name'         => sanitize_file_name( $attachment['name'] ),
				'date_created' => $today->format( 'Y-m-d H:i:s' ),
			);

			// Check for image type. Add a ".txt" extension to non-image file to prevent executing uploaded files on server.
			$img_extensions = array( 'png', 'jpeg', 'jpg', 'bmp', 'pdf', 'gif' );
			if ( ! in_array( $extension, $img_extensions ) ) {
				$data['is_image'] = 0;
			} else {
				$data['is_image'] = 1;
			}

			// File path.
			$file_path = $upload_dir['basedir'] . '/wpsc/' . $today->format( 'Y' ) . '/' . $today->format( 'm' );
			if ( ! file_exists( $file_path ) ) {
				mkdir( $file_path, 0755, true );
			}
			$file_path .= '/' . $filename;

			$filepath_short = '/wpsc/' . $today->format( 'Y' ) . '/' . $today->format( 'm' ) . '/' . $filename;
			$data['file_path'] = $filepath_short;

			// write file.
			$file_contents = strtr(
				$attachment['contentBytes'],
				array(
					'-' => '+',
					'_' => '/',
				)
			);
			file_put_contents( $file_path, base64_decode( $file_contents ) ); //phpcs:ignore

			// Insert attachment record.
			$attach = WPSC_Attachment::insert( $data );

			if ( $attach->id ) {
				$email->attachments[] = $attach->id;
			}
		}

		/**
		 * Get new refresh token.
		 *
		 * @return void
		 */
		public static function update_refresh_token() {

			$me = get_option( 'wpsc-ep-me-settings' );
			if ( ! isset( $me['is-active'] ) || ! $me['is-active'] ) {
				return;
			}

			// Get Refresh Token.
			$response = wp_remote_post(
				'https://login.microsoftonline.com/organizations/oauth2/v2.0/token?scope=offline_access%20user.read%20mail.read%20mail.readwrite',
				array(
					'body' => array(
						'client_id'     => $me['client-id'],
						'client_secret' => $me['client-secret'],
						'grant_type'    => 'refresh_token',
						'refresh_token' => $me['refresh-token'],
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				$me['last-error'] = $response->get_error_message();
			} elseif ( $response['response']['code'] !== 200 ) {
				$me['last-error'] = $response['body'];
			} else {

				$response = json_decode( $response['body'] );
				$me['refresh-token'] = $response->refresh_token;
				set_transient( 'wpsc_ep_me_access_token', $response->access_token, $response->expires_in - 5 );
			}

			update_option( 'wpsc-ep-me-settings', $me );
		}
	}
endif;

WPSC_EP_ME_Importer::init();
