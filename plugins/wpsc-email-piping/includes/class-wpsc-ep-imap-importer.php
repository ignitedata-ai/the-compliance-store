<?php
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_IMAP_Importer' ) ) :

	final class WPSC_EP_IMAP_Importer {

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

			$imap = get_option( 'wpsc-ep-imap-settings' );
			if ( ! $imap['is_active'] ) {
				return;
			}

			$encryption_text = '';
			if ( $imap['encryption'] == 'none' ) {
				$encryption_text = 'novalidate-cert';
			} elseif ( $imap['encryption'] == 'ssl' ) {
				$encryption_text = 'imap/ssl/novalidate-cert';
			} elseif ( $imap['encryption'] == 'tls' ) {
				$encryption_text = 'imap/tls/novalidate-cert';
			}

			// attachment directory.
			$upload_dir = wp_upload_dir();
			$filepath   = $upload_dir['basedir'] . '/wpsc-ep/imap';
			if ( ! file_exists( $filepath ) ) {
				mkdir( $filepath, 0755, true );
			}

			$mailbox = new Mailbox(
				'{' . $imap['incoming-mail-server'] . ':' . $imap['port'] . '/' . $encryption_text . '}INBOX', // IMAP server and mailbox folder.
				$imap['email-address'], // Username for the before configured mailbox.
				$imap['password'], // Password for the before configured username.
				$filepath, // Directory, where attachments will be saved (optional).
			);

			$last_uid = get_option( 'wpsc_ep_imap_uid', 0 );
			$next_uid = intval( $last_uid ) + 1;

			try {

				$history = @imap_fetch_overview( $mailbox->getImapStream(), $next_uid . ':*', FT_UID );  //phpcs:ignore
				$uids    = array();
				$counter = 0;
				foreach ( $history as $overview ) {
					if ( $overview->uid == $last_uid ) {
						continue;
					}
					$uids[] = $overview->uid;
					++$counter;
					if ( $counter == 5 ) {
						break;
					}
				}

				foreach ( $uids as $uid ) {

					$last_uid = get_option( 'wpsc_ep_imap_uid', 0 );
					if ( $last_uid < $uid ) {
						update_option( 'wpsc_ep_imap_uid', $uid );
					} else {
						break;
					}
					self::empty_attachments( $filepath );
					$mail  = $mailbox->getMail( $uid );
					$email = self::prepare_email( $mail, $mailbox );
					$email->pipe();
				}

				$mailbox->disconnect();

			} catch ( Exception $ex ) { // phpcs:ignore

				if ( preg_match( '/AUTHENTICATIONFAILED/', $ex->getMessage() ) ) {
					// set not active flag in imap settings and show admin notice!
					$imap['is_active'] = 0;
					update_option( 'wpsc-ep-imap-settings', $imap );
					update_option( 'wpsc-ep-imap-connection-notice', 1 );
				}
			}
		}

		/**
		 * Remove all attachments from temporary folder before importing next email
		 *
		 * @param array $filepath - filepath.
		 * @return void
		 */
		private static function empty_attachments( $filepath ) {

			$files = glob( $filepath . '/*' );
			foreach ( $files as $file ) { // iterate files.
				wp_delete_file( $file ); // delete file.
			}
		}

		/**
		 * Parse and pipe email
		 *
		 * @param object $mail - mail id.
		 * @param object $mailbox - mailbox.
		 *
		 * @return WPSC_EP_Email
		 */
		public static function prepare_email( $mail, $mailbox ) {

			$email = new WPSC_EP_Email();

			// retrive "to addresses".
			$email->to_addresses = array_keys( $mail->to );

			// retrive "cc addreeses".
			$email->cc_addresses = array_keys( $mail->cc );

			// from name.
			$email->from_name = $mail->fromName ? $mail->fromName : $mail->fromAddress; //phpcs:ignore

			// Reply to email.
			$email->reply_to = array_keys( $mail->replyTo )[0]; //phpcs:ignore

			// from email.
			$email->from_email = $email->reply_to ? $email->reply_to : $mail->fromAddress; //phpcs:ignore

			// subject.
			$email->subject = $mail->subject;

			// HTML body.
			$email->html_body = $mail->textHtml; //phpcs:ignore

			// Text body.
			$email->text_body = $mail->textPlain; //phpcs:ignore

			// attachments.
			self::parse_attachments( $email, $mail );

			// source.
			$email->source = 'imap';

			// message id.
			$email->message_id = $mail->headers->message_id;

			return apply_filters( 'wpsc_imap_parsed_email', $email, $mail );
		}

		/**
		 * Parse attachments
		 *
		 * @param WPSC_EP_Email $email - email object.
		 * @param object        $mail - email object.
		 * @return void
		 */
		private static function parse_attachments( $email, $mail ) {

			if ( ! $mail->hasAttachments() ) {
				return;
			}

			$general = get_option( 'wpsc-ep-general-settings' );

			$attachments = $mail->getAttachments();
			foreach ( $attachments as $attachment ) {
				if ( $general['body-reference'] == 'html' && $attachment->contentId && preg_match( '/cid:' . $attachment->contentId . '/', $email->html_body ) ) { //phpcs:ignore
					self::upload_embeded_img( $attachment, $mail, $email );
				} else {
					self::upload_thread_attachment( $attachment, $mail, $email );
				}
			}
		}

		/**
		 * Upload embeded image in the description
		 *
		 * @param array         $attachment - attachment array received from IMAP.
		 * @param object        $mail - email object.
		 * @param WPSC_EP_Email $email - email object.
		 * @return void
		 */
		private static function upload_embeded_img( $attachment, $mail, $email ) {

			// Database insert array init.
			$data = array( 'name' => $attachment->name );

			// saperate file name and extension.
			$filename  = explode( '.', $attachment->name );
			$extension = strtolower( $filename[ count( $filename ) - 1 ] );
			unset( $filename[ count( $filename ) - 1 ] );
			$filename = implode( '.', $filename );
			$filename = str_replace( ' ', '_', $filename );
			$filename = str_replace( ',', '_', $filename );
			$filename = uniqid() . '_' . preg_replace( '/[^A-Za-z0-9\-]/', '', $filename );

			// allowed file extenstions.
			$img_extensions = array( 'png', 'jpeg', 'jpg', 'gif' );
			if ( ! ( in_array( $extension, $img_extensions ) ) ) {
				self::upload_thread_attachment( $attachment, $mail, $email );
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
			file_put_contents( $filepath, $attachment->getContents() ); //phpcs:ignore

			// insert db record.
			$attach = WPSC_Attachment::insert( $data );

			if ( $attach->id ) {
				$img_url = home_url( '/' ) . '?wpsc_attachment=' . $attach->id;
				$email->html_body = str_replace( 'cid:' . $attachment->contentId, $img_url, $email->html_body ); //phpcs:ignore
			}
		}

		/**
		 * Upload regular thread attachments
		 *
		 * @param array         $attachment - attachment array received from microsoft exchange server.
		 * @param object        $mail - email object.
		 * @param WPSC_EP_Email $email - email object.
		 * @return void
		 */
		private static function upload_thread_attachment( $attachment, $mail, $email ) {

			// Database insert array init.
			$data = array( 'name' => $attachment->name );

			// saperate file name and extension.
			$filename  = explode( '.', $attachment->name );
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

			// check for allowed file size.
			$max_size = intval( $file_settings['attachments-max-filesize'] ) * 1000000;
			if ( $attachment->sizeInBytes >= $max_size ) {  //phpcs:ignore
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
			file_put_contents( $filepath, $attachment->getContents() ); //phpcs:ignore

			// insert db record.
			$att = WPSC_Attachment::insert( $data );

			if ( $att->id ) {
				$email->attachments[] = $att->id;
			}
		}
	}
endif;

WPSC_EP_IMAP_Importer::init();
