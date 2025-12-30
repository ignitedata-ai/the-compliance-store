<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Email' ) ) :

	final class WPSC_EP_Email {

		/**
		 * Ticket ID
		 *
		 * @var integer
		 */
		public $ticket_id = 0;

		/**
		 * Email sent to addresses. Used to check forwarding emails
		 *
		 * @var array
		 */
		public $to_addresses = array();

		/**
		 * CC addresses. Used to insert as additional recipients if allowed in setting
		 *
		 * @var array
		 */
		public $cc_addresses = array();

		/**
		 * Email customer name
		 *
		 * @var string
		 */
		public $from_name;

		/**
		 * Email customer email address
		 *
		 * @var string
		 */
		public $from_email;

		/**
		 * Reply to email address
		 *
		 * @var string
		 */
		public $reply_to;

		/**
		 * Email subject line
		 *
		 * @var string
		 */
		public $subject;

		/**
		 * HTML body of email
		 *
		 * @var string
		 */
		public $html_body = '';

		/**
		 * Plain text body of email
		 *
		 * @var string
		 */
		public $text_body = '';

		/**
		 * Email attachments
		 *
		 * @var array
		 */
		public $attachments = array();

		/**
		 * Ticket source (imap, gmail, me, etc.)
		 *
		 * @var string
		 */
		public $source;

		/**
		 * Set whether incoming email is forwarded from email address listed in general settings
		 *
		 * @var boolean
		 */
		public $is_forwarded = false;

		/**
		 * Set forwarding address if it is forwarded from email address listed in general settings
		 *
		 * @var string
		 */
		public $forwarded_from = '';

		/**
		 * Message ID
		 *
		 * @var string
		 */
		public $message_id;

		/**
		 * Used to insert as email logs
		 *
		 * @var array
		 */
		public $email_logger = array();

		/**
		 * Email piping process status
		 *
		 * @var array
		 */
		public $status = 0;

		/**
		 * Pipe current email
		 *
		 * @return void
		 */
		public function pipe() {

			$this->email_logger[] = 'Piping started';

			$this->set_ticket_id();

			// do not pipe if email is not valid as per settings.
			if ( ! $this->is_valid() ) {

				$this->email_logger[] = 'Email validation completed: not valid email. Aborting!';
				// Add email log.
				$this->add_email_log( $this );
				return;
			}

			// set forwarding email if it is forwarded.
			$this->set_forwarding_address();

			// decide whether it is ticket reply or new email.
			if ( $this->ticket_id ) {

				$this->email_logger[] = 'Start creating a new reply for ticket #' . $this->ticket_id;
				$this->pipe_reply();
			} else {

				$this->email_logger[] = 'Start creating a new ticket';
				$this->pipe_new_ticket();
			}

			WPSC_Email_Notifications::send_background_emails();

			// Add email log.
			$this->add_email_log( $this );
		}

		/**
		 * Add new email log.
		 *
		 * @param WPSC_EP_Email $email - Email object.
		 * @return void
		 */
		public static function add_email_log( $email ) {

			// Create email logger data.
			$data['email_subject'] = $email->subject;
			$data['email_to'] = implode( ', ', $email->to_addresses );
			$data['email_cc'] = implode( ', ', $email->cc_addresses );
			$data['email_from'] = $email->from_email;
			$data['message_id'] = $email->message_id;
			$data['logs'] = wp_json_encode( $email->email_logger );
			$data['status'] = $email->status;
			$data['date_created'] = ( new DateTime() )->format( 'Y-m-d H:i:s' );
			WPSC_EP_Logger::insert( $data );
		}

		/**
		 * Set ticket id
		 *
		 * @return void
		 */
		private function set_ticket_id() {
			$this->email_logger[] = 'Checking for ticket id';
			$gs           = get_option( 'wpsc-gs-general' );
			$ticket_alice = $gs['ticket-alice'];
			if ( preg_match( '/\[' . $ticket_alice . '(\d*)\]/i', $this->subject, $matches ) ) {
				$ticket_id = intval( $matches[1] );
				$ticket    = new WPSC_Ticket( $ticket_id );
				if ( $ticket->id ) {
					$this->ticket_id                = $ticket_id;
					WPSC_Individual_Ticket::$ticket = $ticket;
					$this->email_logger[] = 'Found ticket id #' . $ticket_id;
				}
			} else {
				$this->email_logger[] = 'Ticket id not found';
			}
			do_action( 'wpsc_after_set_ticket_id', $this );
		}

		/**
		 * Set forwarding address if it is forwarded from listed forwarding addresses in general settings
		 *
		 * @return void
		 */
		private function set_forwarding_address() {

			$piping_address = WPSC_EP_Settings_General::get_piping_email_address();

			// if email piped through main piping addredss consider this as not forwarded.
			if (
				in_array( $piping_address, $this->to_addresses ) ||
				in_array( $piping_address, $this->cc_addresses )
			) {
				return;
			}

			// check every forwarding email and return matched.
			$general = get_option( 'wpsc-ep-general-settings' );
			$fwd_addresses = array();
			if ( is_array( $general['forwarding-addresses'] ) ) {
				$fwd_addresses = array_map(
					function ( $str ) {
						return strtolower( trim( $str ) );
					},
					$general['forwarding-addresses']
				);
			}

			foreach ( $fwd_addresses as $email ) {
				if (
					in_array( $email, $this->to_addresses ) ||
					in_array( $email, $this->cc_addresses )
				) {
					$this->is_forwarded   = true;
					$this->forwarded_from = $email;
					return;
				}
			}
		}

		/**
		 * Check whether it is valid to import as per settings
		 *
		 * @return boolean
		 */
		private function is_valid() {

			$this->email_logger[] = 'Email validation start';

			$general     = get_option( 'wpsc-ep-general-settings' );
			$tl_advanced = get_option( 'wpsc-tl-ms-advanced' );
			$ms_advanced = get_option( 'wpsc-ms-advanced-settings' );
			$user        = WPSC_Current_User::change_current_user( $this->from_email );

			if ( $this->ticket_id ) {
				$ticket = new WPSC_Ticket( $this->ticket_id );
				if ( ! $ticket->is_active ) {
					$this->email_logger[] = 'Reject: Ticket is deleted';
					return false;
				}
			}

			// return if from address and piping address are same.
			if ( $this->from_email == WPSC_EP_Settings_General::get_piping_email_address() ) {

				$this->email_logger[] = 'Reject: From email address and piping email address are same';
				return false;
			}

			// block by email address.
			foreach ( $general['block-emails'] as $pattern ) {

				$pattern_log = $pattern;
				if ( preg_match( '/^\/[\s\S]+\/i$/', $pattern ) ) { // regex.

					if ( preg_match( $pattern, $this->from_email ) ) {

						$this->email_logger[] = 'Reject: Blocked by pattern ' . $pattern_log;
						return false;
					}
				} elseif ( preg_match( '/^\*[\s\S]+\*$/', $pattern ) ) { // e.g. "*reply*".

					$pattern = str_replace( '*', '', $pattern );
					if ( preg_match( '/' . $pattern . '/', $this->from_email ) ) {

						$this->email_logger[] = 'Reject: Blocked by pattern ' . $pattern_log;
						return false;
					}
				} elseif ( preg_match( '/^\*[\s\S]+$/', $pattern ) ) { // e.g. "*@test.com".

					$pattern = str_replace( '*', '[\s\S]+', $pattern );
					if ( preg_match( '/' . $pattern . '$/', $this->from_email ) ) {

						$this->email_logger[] = 'Reject: Blocked by pattern ' . $pattern_log;
						return false;
					}
				} elseif ( preg_match( '/[\s\S]+\*$/', $pattern ) ) { // e.g. "noreply@*".

					$pattern = str_replace( '*', '[\s\S]+', $pattern );
					if ( preg_match( '/^' . $pattern . '/', $this->from_email ) ) {

						$this->email_logger[] = 'Reject: Blocked by pattern ' . $pattern_log;
						return false;
					}
				} elseif ( trim( $pattern ) == trim( $this->from_email ) ) {

					// e.g. "test@test.com".
					$this->email_logger[] = 'Reject: Blocked by pattern ' . $pattern_log;
					return false;
				}
			}

			// block by subject.
			foreach ( $general['block-subject'] as $pattern ) {

				$pattern_log = $pattern;
				if ( preg_match( '/^\/[\s\S]+\/i$/', $pattern ) ) { // regex.

					if ( preg_match( $pattern, $this->subject ) ) {

						$this->email_logger[] = 'Reject: Blocked by pattern ' . $pattern_log;
						return false;
					}
				} elseif ( preg_match( '/^\*[\s\S]+\*$/', $pattern ) ) { // e.g. "*reply*".

					$pattern = str_replace( '*', '', $pattern );
					if ( preg_match( '/' . $pattern . '/', $this->subject ) ) {

						$this->email_logger[] = 'Reject: Blocked by pattern ' . $pattern_log;
						return false;
					}
				} elseif ( preg_match( '/^\*[\s\S]+$/', $pattern ) ) { // e.g. "*@test.com".

					$pattern = str_replace( '*', '[\s\S]+', $pattern );
					if ( preg_match( '/' . $pattern . '$/', $this->subject ) ) {

						$this->email_logger[] = 'Reject: Blocked by pattern ' . $pattern_log;
						return false;
					}
				} elseif ( preg_match( '/[\s\S]+\*$/', $pattern ) ) { // e.g. "noreply@*".

					$pattern = str_replace( '*', '[\s\S]+', $pattern );
					if ( preg_match( '/^' . $pattern . '/', $this->subject ) ) {

						$this->email_logger[] = 'Reject: Blocked by pattern ' . $pattern_log;
						return false;
					}
				} else {

					$pattern = '/' . $pattern . '/i';
					if ( preg_match( $pattern, $this->subject ) ) {

						$this->email_logger[] = 'Reject: Blocked by pattern ' . $pattern_log;
						return false;
					}
				}
			}

			// delivery failure email.
			if (
				preg_match( '/postmaster@/i', $this->from_email ) &&
				(
					preg_match( '/Delivery has failed to these recipients/i', $this->text_body ) ||
					preg_match( '/Delivery has failed to these recipients/i', $this->html_body )
				)
			) {

				$this->email_logger[] = 'Reject: Blocked by pattern /postmaster@/i';
				return false;
			}

			// not allowed reply emails.
			if ( $this->ticket_id && $general['allowed-emails'] == 'new' ) {

				do_action( 'wpsc_ep_reject_reply_emails', $this, $this->ticket_id );
				$this->email_logger[] = 'Reject: Reply emails are not allowed';
				return false;
			}

			// not allowed new emails.
			if ( ! $this->ticket_id && $general['allowed-emails'] == 'reply' ) {

				do_action( 'wpsc_ep_reject_new_emails', $this );
				$this->email_logger[] = 'Reject: New emails are not allowed';
				return false;
			}

			// allowed users.
			if ( ( ! $user->user->ID && $general['allowed-users'] == 'registered' ) &&
				(
					( ! $this->ticket_id ) ||
					( $this->ticket_id && $ticket->id && ! in_array( $this->from_email, $ticket->add_recipients ) )
				)
			) {
				// User reject pipe email template and send email on below action.
				do_action( 'wpsc_ep_reject_user_pipe', $this );
				$this->email_logger[] = 'Reject: Guest users are not allowed';
				return false;
			}

			// allow reply to closed tickets.
			if ( $this->ticket_id ) {
				$ticket = new WPSC_Ticket( $this->ticket_id );
				if (
					in_array( $ticket->status->id, $tl_advanced['closed-ticket-statuses'] ) &&
					(
						( $user->is_agent && ! in_array( 'agent', $ms_advanced['allow-reply-to-close-ticket'] ) ) ||
						( ! $user->is_agent && ! in_array( 'customer', $ms_advanced['allow-reply-to-close-ticket'] ) )
					)
				) {

					// Reject closed ticket reply email template and send email on below action.
					do_action( 'wpsc_ep_reject_closed_ticket_reply', $this, $ticket );
					$this->email_logger[] = 'Reject: Reply to close tickets are not allowed';
					return false;
				}
			}

			$this->email_logger[] = 'Email validation completed: valid email';
			return apply_filters( 'wpsc_ep_email_is_valid', true, $this );
		}

		/**
		 * Pipe reply to existing ticket
		 *
		 * @return void
		 */
		private function pipe_reply() {

			$current_user = WPSC_Current_User::$current_user;
			$general      = get_option( 'wpsc-ep-general-settings' );
			$ticket       = new WPSC_Ticket( $this->ticket_id );

			// create customer record if not exists.
			if ( ! $current_user->customer->id ) {
				$customer     = WPSC_Customer::insert(
					array(
						'user'  => 0,
						'name'  => $this->from_name,
						'email' => $this->from_email,
					)
				);
				$current_user = WPSC_Current_User::change_current_user( $this->from_email );
				$this->email_logger[] = 'New customer record created';
			}

			// Set reply profile.
			WPSC_Individual_Ticket::$reply_profile = 'customer';
			if ( $current_user->is_agent && ! WPSC_Individual_Ticket::is_customer() ) {
				WPSC_Individual_Ticket::$reply_profile = 'agent';
			}

			// description.
			$description = '';
			if (
				( $general['body-reference'] == 'text' && $this->text_body ) ||
				( $general['body-reference'] == 'html' && ! $this->html_body && $this->text_body )
			) {

				$description = preg_replace( '/(<(script|style)\b[^>]*>).*?(<\/\2>)/s', '', $this->text_body );
				$description = nl2br( $description );

				if ( $general['reply-above-text'] ) {

					// reply above.
					$pos = strpos( $description, $general['reply-above-text'] );
					if ( is_numeric( $pos ) ) {
						$description = substr( $description, 0, $pos );
						$description = preg_replace( '/\nOn(.*?)wrote:(.*?)$/si', '', $description );
					}
				}
			} elseif (
				( $general['body-reference'] == 'html' && $this->html_body ) ||
				( $general['body-reference'] == 'text' && ! $this->text_body && $this->html_body )
			) {

				$description = preg_replace( '/(<(script|style)\b[^>]*>).*?(<\/\2>)/s', '', $this->html_body );
				$description = wp_kses_post( $description );

				if ( $general['reply-above-text'] ) {

					// reply above.
					$pos = strpos( $description, $general['reply-above-text'] );
					if ( is_numeric( $pos ) ) {
						$description = substr( $description, 0, $pos );
						$description = $this->closetags( $description );
					}
				}
			} else {

				$description = esc_attr__( 'No email body found!', 'wpsc-ep' );
			}

			// remove divs from message.
			$description = preg_replace(
				array( '/<div(.*?)>/s', '/<\/div>/s', '/<table(.*?)>/s', '/<tbody(.*?)>/s', '/<tr(.*?)>/s', '/<th(.*?)>/s', '/<td(.*?)>/s', '/<pre(.*?)>/s', '/<\/pre>/s' ),
				array( '<p>', '</p>', '<table>', '<tbody>', '<tr>', '<th>', '<td>', '<p>', '</p>' ),
				$description
			);

			// Check if agent and add his signature.
			if ( $current_user->is_agent && $current_user->customer->email == $this->from_email ) {
				$description .= $current_user->agent->get_signature();
			}

			// add ticket auth url to tinymce attachments.
			if ( preg_match_all( '/' . preg_quote( home_url( '/' ), '/' ) . '\?wpsc_attachment=(\d*)/', $description, $matches ) ) {
				foreach ( $matches[0] as $url ) {
					$new_url = add_query_arg( 'auth_code', $ticket->auth_code, $url );
					$description = str_replace( $url, $new_url, $description );
				}
			}

			// attachments.
			$attachments = $this->attachments ? implode( '|', $this->attachments ) : '';

			if ( $this->is_duplicate_email( $current_user->customer, $description, $ticket ) ) {
				$this->email_logger[] = 'Reject duplicate email';
				return;
			}

			$thread_data = array(
				'ticket'      => $ticket->id,
				'customer'    => $current_user->customer->id,
				'type'        => 'reply',
				'body'        => $description,
				'attachments' => $attachments,
				'source'      => $this->source,
				'message_id'  => $this->message_id,
			);

			$thread_data = apply_filters( 'wpsc_ep_reply_thread_data', $thread_data, $ticket, $this );

			// submit reply.
			$thread = WPSC_Thread::insert( $thread_data );
			if ( ! $thread->id ) {
				$this->email_logger[] = 'Can not create reply';
				return;
			}

			// activate description attachments.
			foreach ( $this->attachments as $id ) :
				$attachment            = new WPSC_Attachment( $id );
				$attachment->is_active = 1;
				$attachment->source    = 'reply';
				$attachment->source_id = $thread->id;
				$attachment->ticket_id = $ticket->id;
				$attachment->save();
			endforeach;

			// tinymce img attachments.
			if ( preg_match_all( '/' . preg_quote( home_url( '/' ), '/' ) . '\?wpsc_attachment=(\d*)(&auth_code=[^&\s]*)?/', $description, $matches ) ) {
				foreach ( $matches[1] as $id ) {
					$attachment            = new WPSC_Attachment( $id );
					$attachment->is_active = 1;
					$attachment->source_id = $thread->id;
					$attachment->ticket_id = $ticket->id;
					$attachment->source = 'img_editor';
					$attachment->save();
				}
			}

			$ticket = WPSC_Individual_Ticket::$ticket;
			// add additional recepients.
			$new = $this->get_additional_recipients( array() );
			if ( $new ) {
				$new  = explode( '|', $new );
				$new = array_diff( $new, array( $ticket->customer->email ) );
				$thread->other_recipients = $new;
				$prev = $ticket->add_recipients;
				$new = array_unique( array_merge( $prev, $new ) );
				if ( ( array_diff( $new, $prev ) || array_diff( $prev, $new ) ) ) {

					$ticket->add_recipients = $new;
					do_action( 'wpsc_change_ticket_add_recipients', $ticket, $prev, $new, $current_user->customer->id );
				}
				$thread->save();
			}

			// update last reply on.
			$ticket->date_updated  = new DateTime();
			$ticket->last_reply_on = new DateTime();
			$ticket->last_reply_by = $current_user->customer->id;
			$ticket->last_reply_source = $this->source;
			$ticket->save();

			$this->email_logger[] = 'Reply for ticket #' . $ticket->id . ' created!';
			$this->status = 1;

			$thread = new WPSC_Thread( $thread->id );
			do_action( 'wpsc_post_reply', $thread );
		}

		/**
		 * Pipe new ticket
		 *
		 * @return void
		 */
		private function pipe_new_ticket() {

			$general = get_option( 'wpsc-ep-general-settings' );

			// get applicable pipe rule.
			$rule = $this->apply_ep_rule();

			// start with subject.
			$subject = '';
			if ( ! $this->subject ) {
				$cf = WPSC_Custom_Field::get_cf_by_slug( 'subject' );
				$subject = $cf->default_value[0];
			}
			$data = array(
				'subject' => $this->subject ? $this->subject : $subject,
			);

			$customer = WPSC_DF_Customer::get_customer_record( $this->from_name, strtolower( $this->from_email ) );
			$data['customer'] = $customer->id ? $customer->id : 0;

			// user type.
			$data['user_type'] = $customer->user ? 'registered' : 'guest';

			// description.
			$description = '';
			if (
				( $general['body-reference'] == 'text' && $this->text_body ) ||
				( $general['body-reference'] == 'html' && ! $this->html_body && $this->text_body )
			) {
				$description = preg_replace( '/(<(script|style)\b[^>]*>).*?(<\/\2>)/s', '', $this->text_body );
				$description = nl2br( $description );
			} elseif (
				( $general['body-reference'] == 'html' && $this->html_body ) ||
				( $general['body-reference'] == 'text' && ! $this->text_body && $this->html_body )
			) {
				$description = preg_replace( '/(<(script|style)\b[^>]*>).*?(<\/\2>)/s', '', $this->html_body );
				// remove divs from message.
				$description = preg_replace(
					array( '/<div(.*?)>/s', '/<\/div>/s', '/<table(.*?)>/s', '/<tbody(.*?)>/s', '/<tr(.*?)>/s', '/<th(.*?)>/s', '/<td(.*?)>/s', '/<pre(.*?)>/s', '/<\/pre>/s' ),
					array( '<p>', '</p>', '<table>', '<tbody>', '<tr>', '<th>', '<td>', '<p>', '</p>' ),
					$description
				);
				$description = wp_kses_post( make_clickable( $description ) );

				// fix: email have unclosed tags, need encoding for non utf-8 characters.
				$dom_doc           = new DOMDocument();
				$dom_doc->encoding = 'utf-8';
				@$dom_doc->loadHTML( mb_convert_encoding( $description, 'HTML-ENTITIES', 'UTF-8' ) ); // phpcs:ignore
				$description = $dom_doc->saveHTML( $dom_doc->documentElement ); // phpcs:ignore

			} else {
				$description = esc_attr__( 'No email body found!', 'wpsc-ep' );
			}

			// Check if agent and add his signature.
			$agent = WPSC_Agent::get_by_customer( $customer );
			if ( $agent->id ) {
				$description .= $agent->get_signature();
			}

			// attachments.
			foreach ( $this->attachments as $id ) {
				$attachment = new WPSC_Attachment( $id );
				$attachment->is_active = 1;
				$attachment->save();
			}
			$attachments = $this->attachments ? implode( '|', $this->attachments ) : '';

			// source.
			$data['source'] = $this->source;

			// additional recipients.
			$data['add_recipients'] = $this->get_additional_recipients( $rule );

			// Email notification from email address.
			$data['en_from'] = $this->get_en_from_email();

			// apply pipe rule for ticket and agentonly fields.
			$ignore_cft = apply_filters(
				'wpsc_ep_create_ticket_ignore_cft',
				array(
					'df_id',
					'df_customer',
					'df_customer_name',
					'df_customer_email',
					'df_user_type',
					'df_subject',
					'df_description',
					'df_source',
					'df_last_reply_source',
					'cf_file_attachment_multiple',
					'cf_file_attachment_single',
					'df_add_recipients',
					'df_en_from',
					'cf_edd_order',
					'cf_woo_order',
					'cf_woo_subscription',
					'df_time_spent',
					'df_sf_rating',
					'df_sf_feedback',
					'df_sf_date',
					'df_sla',
					'cf_html',
					'df_last_reply_on',
					'df_last_reply_by',
					'df_usergroups',
					'cf_lifter_order',
					'cf_learnpress_order',
					'cf_tutor_order',
				)
			);

			foreach ( WPSC_Custom_Field::$custom_fields as $cf ) {

				if (
					! class_exists( $cf->type ) ||
					! in_array( $cf->field, array( 'ticket', 'agentonly' ) )
				) {
					continue;
				}

				// ignore already set.
				if ( in_array( $cf->type::$slug, $ignore_cft ) ) {
					continue;
				}

				$data[ $cf->slug ] = isset( $rule[ $cf->slug ] ) && $rule[ $cf->slug ] ? $rule[ $cf->slug ] : $cf->type::get_default_value( $cf );
			}

			$data['last_reply_on'] = ( new DateTime() )->format( 'Y-m-d H:i:s' );
			$data['last_reply_by'] = $customer->id;
			$data['last_reply_source'] = $this->source;
			$data['auth_code'] = WPSC_Functions::get_random_string();

			if ( $this->is_duplicate_email( $customer, $description ) ) {
				$this->email_logger[] = 'Reject duplicate email';
				return;
			}

			$data = apply_filters( 'wpsc_ep_ticket_data', $data, $this );

			// create new ticket.
			$ticket = WPSC_Ticket::insert( $data );
			if ( ! $ticket->id ) {
				$this->email_logger[] = 'Can not create new ticket';
				return;
			}

			// add ticket auth url to tinymce attachments.
			if ( preg_match_all( '/' . preg_quote( home_url( '/' ), '/' ) . '\?wpsc_attachment=(\d*)/', $description, $matches ) ) {
				foreach ( $matches[0] as $url ) {
					$new_url = add_query_arg( 'auth_code', $ticket->auth_code, $url );
					$description = str_replace( $url, $new_url, $description );
				}
			}

			$thread_data = array(
				'ticket'           => $ticket->id,
				'customer'         => $ticket->customer->id,
				'type'             => 'report',
				'body'             => $description,
				'attachments'      => $attachments,
				'source'           => $this->source,
				'message_id'       => $this->message_id,
				'other_recipients' => $data['add_recipients'],
			);

			$thread_data = apply_filters( 'wpsc_ep_thread_data', $thread_data, $ticket, $this );

			// create report thread.
			$thread = WPSC_Thread::insert( $thread_data );

			// tinymce img attachments.
			if ( preg_match_all( '/' . preg_quote( home_url( '/' ), '/' ) . '\?wpsc_attachment=(\d*)(&auth_code=[^&\s]*)?/', $description, $matches ) ) {
				foreach ( $matches[1] as $id ) {
					$attachment = new WPSC_Attachment( $id );
					$attachment->is_active = 1;
					$attachment->source_id = $thread->id;
					$attachment->ticket_id = $ticket->id;
					$attachment->source = 'img_editor';
					$attachment->save();
				}
			}

			$this->email_logger[] = 'A new ticket #' . $ticket->id . ' created!';
			$this->status = 1;

			do_action( 'wpsc_create_new_ticket', $ticket );
		}

		/**
		 * Apply email piping rule for current email
		 *
		 * @return array
		 */
		private function apply_ep_rule() {

			$rules = get_option( 'wpsc-ep-pipe-rules', array() );
			foreach ( $rules as $key => $rule ) {

				// check forwarding address.
				$forwarding_address = isset( $rule['forwarding-address'] ) ? $rule['forwarding-address'] : array();
				if ( $forwarding_address && $this->is_forwarded ) {

					foreach ( $forwarding_address as $pattern ) {

						$pattern_log = $pattern;
						if ( preg_match( '/^\/[\s\S]+\/i$/', $pattern ) ) { // regex.

							if ( preg_match( $pattern, $this->forwarded_from ) ) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( preg_match( '/^\*[\s\S]+\*$/', $pattern ) ) { // e.g. "*reply*".

							$pattern = str_replace( '*', '', $pattern );
							if ( preg_match( '/' . $pattern . '/', $this->forwarded_from ) ) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( preg_match( '/^\*[\s\S]+$/', $pattern ) ) { // e.g. "*@test.com".

							$pattern = str_replace( '*', '[\s\S]+', $pattern );
							if ( preg_match( '/' . $pattern . '$/', $this->forwarded_from ) ) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( preg_match( '/[\s\S]+\*$/', $pattern ) ) { // e.g. "noreply@*".

							$pattern = str_replace( '*', '[\s\S]+', $pattern );
							if ( preg_match( '/^' . $pattern . '/', $this->forwarded_from ) ) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( $pattern == $this->forwarded_from ) {
							// e.g. "test@test.com".
							$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
							return $rule;
						}
					}
				}

				// check from address.
				$from_address = isset( $rule['from-address'] ) ? $rule['from-address'] : array();
				if ( $from_address ) {

					foreach ( $from_address as $pattern ) {

						$pattern_log = $pattern;
						if ( preg_match( '/^\/[\s\S]+\/i$/', $pattern ) ) { // regex.

							if ( preg_match( $pattern, $this->from_email ) ) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( preg_match( '/^\*[\s\S]+\*$/', $pattern ) ) { // e.g. "*reply*".

							$pattern = str_replace( '*', '', $pattern );
							if ( preg_match( '/' . $pattern . '/', $this->from_email ) ) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( preg_match( '/^\*[\s\S]+$/', $pattern ) ) { // e.g. "*@test.com".

							$pattern = str_replace( '*', '[\s\S]+', $pattern );
							if ( preg_match( '/' . $pattern . '$/', $this->from_email ) ) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( preg_match( '/[\s\S]+\*$/', $pattern ) ) { // e.g. "noreply@*".

							$pattern = str_replace( '*', '[\s\S]+', $pattern );
							if ( preg_match( '/^' . $pattern . '/', $this->from_email ) ) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( $pattern == $this->from_email ) {
							// e.g. "test@test.com".
							$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
							return $rule;
						}
					}
				}

				// check has words.
				$has_words = isset( $rule['has-words'] ) ? $rule['has-words'] : array();
				if ( $has_words ) {

					foreach ( $has_words as $pattern ) {

						$pattern_log = $pattern;
						if ( preg_match( '/^\/[\s\S]+\/i$/', $pattern ) ) { // regex.

							if (
								preg_match( $pattern, $this->subject ) ||
								preg_match( $pattern, $this->text_body ) ||
								preg_match( $pattern, $this->html_body )
							) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( preg_match( '/^\*[\s\S]+\*$/', $pattern ) ) { // e.g. "*reply*".

							$pattern = '/' . str_replace( '*', '', $pattern ) . '/';
							if (
								preg_match( $pattern, $this->subject ) ||
								preg_match( $pattern, $this->text_body ) ||
								preg_match( $pattern, $this->html_body )
							) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( preg_match( '/^\*[\s\S]+$/', $pattern ) ) { // e.g. "*@test.com".

							$pattern = '/' . str_replace( '*', '[\s\S]+', $pattern ) . '/';
							if (
								preg_match( $pattern, $this->subject ) ||
								preg_match( $pattern, $this->text_body ) ||
								preg_match( $pattern, $this->html_body )
							) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} elseif ( preg_match( '/[\s\S]+\*$/', $pattern ) ) { // e.g. "noreply@*".

							$pattern = '/' . str_replace( '*', '[\s\S]+', $pattern ) . '/';
							if (
								preg_match( $pattern, $this->subject ) ||
								preg_match( $pattern, $this->text_body ) ||
								preg_match( $pattern, $this->html_body )
							) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						} else {

							$pattern = '/' . $pattern . '/i';
							if (
								preg_match( $pattern, $this->subject ) ||
								preg_match( $pattern, $this->text_body ) ||
								preg_match( $pattern, $this->html_body )
							) {
								$this->email_logger[] = 'Piping rule applied: ' . $rule['title'] . ' using pattern: ' . $pattern_log;
								return $rule;
							}
						}
					}
				}
			}

			return array();
		}

		/**
		 * Return additional recipients for the email
		 *
		 * @param array $rule - pipe rule.
		 * @return String
		 */
		private function get_additional_recipients( $rule ) {

			$general    = get_option( 'wpsc-ep-general-settings' );
			$ep_general = get_option( 'wpsc-ep-general-settings' );
			$gmail      = get_option( 'wpsc-ep-gmail-settings' );
			$imap       = get_option( 'wpsc-ep-imap-settings' );

			$add_recipients = array();

			// import cc as additional recipients.
			if ( $general['import-cc'] && $this->cc_addresses ) {
				$add_recipients = array_unique( array_merge( $add_recipients, $this->cc_addresses ) );
			}

			$piping_mail = $ep_general['connection'] == 'imap' ? $imap['email-address'] : $gmail['email-address'];

			// import to as additional recipients.
			if ( $general['import-cc'] && $this->to_addresses ) {
				$add_recipients = array_unique( array_merge( $add_recipients, $this->to_addresses ) );
			}

			// check with pipe rule.
			if ( isset( $rule['add_recipients'] ) && $rule['add_recipients'] ) {
				$rule_add_recipients = explode( '|', $rule['add_recipients'] );
				foreach ( $rule_add_recipients as $email_address ) {
					$add_recipients[] = $email_address;
				}
			}
			$add_recipients = array_unique( $add_recipients );

			// exclude piping and forwarding emails.
			if ( $add_recipients ) {
				$exclude_emails = array( WPSC_EP_Settings_General::get_piping_email_address() );
				$exclude_emails = array_merge( $exclude_emails, WPSC_EP_Settings_General::get_forwarding_addresses() );
				foreach ( $add_recipients as $key => $email ) {
					if ( in_array( $email, $exclude_emails ) ) {
						unset( $add_recipients[ $key ] );
					}
				}
			}

			return implode( '|', $add_recipients );
		}

		/**
		 * Get email notification from email
		 *
		 * @return String
		 */
		public function get_en_from_email() {

			return $this->forwarded_from;
		}

		/**
		 * Close all open tags in HTML string
		 *
		 * @param html $html - html string.
		 * @return html
		 */
		public static function closetags( $html ) {

			preg_match_all( '#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result );
			$openedtags = $result[1];
			preg_match_all( '#</([a-z]+)>#iU', $html, $result );

			$closedtags = $result[1];
			$len_opened = count( $openedtags );

			if ( count( $closedtags ) == $len_opened ) {
				return $html;
			}
			$openedtags = array_reverse( $openedtags );
			for ( $i = 0; $i < $len_opened; $i++ ) {
				if ( ! in_array( $openedtags[ $i ], $closedtags ) ) {
					$html .= '</' . $openedtags[ $i ] . '>';
				} else {
					unset( $closedtags[ array_search( $openedtags[ $i ], $closedtags ) ] );
				}
			}
			return $html;
		}

		/**
		 * Check for duplicate email.
		 *
		 * @param WPSC_Customer $customer - customer object.
		 * @param string        $description - thread body.
		 * @param WPSC_Ticket   $ticket  - ticket object/null.
		 * @return boolean
		 */
		public function is_duplicate_email( $customer, $description, $ticket = null ) {

			$general = get_option( 'wpsc-ep-general-settings' );
			if ( ! $general['spam-filter'] ) {
				return false;
			}

			// check for message id.
			$filters = array(
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'slug'    => 'message_id',
						'compare' => '=',
						'val'     => $this->message_id,
					),
					array(
						'slug'    => 'source',
						'compare' => 'IN',
						'val'     => array( 'imap', 'gmail', 'me' ),
					),
					array(
						'slug'    => 'type',
						'compare' => 'IN',
						'val'     => array( 'reply', 'report' ),
					),
					array(
						'slug'    => 'is_active',
						'compare' => '=',
						'val'     => '1',
					),
				),
				'orderby'        => 'id',
				'order'          => 'DESC',
				'items_per_page' => 1,
			);
			$threads = WPSC_Thread::find( $filters );
			if ( $threads['total_items'] ) {
				$this->email_logger[] = 'Duplicate found: duplicate message id';
				return true;
			}

			if ( $ticket ) {
				// check for same text.
				$filters = array(
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'slug'    => 'customer',
							'compare' => '=',
							'val'     => $customer->id,
						),
						array(
							'slug'    => 'type',
							'compare' => 'IN',
							'val'     => array( 'reply', 'report' ),
						),
						array(
							'slug'    => 'ticket',
							'compare' => '=',
							'val'     => $ticket->id,
						),
						array(
							'slug'    => 'is_active',
							'compare' => '=',
							'val'     => '1',
						),
					),
					'orderby'        => 'id',
					'order'          => 'DESC',
					'items_per_page' => 1,
				);
				$threads = WPSC_Thread::find( $filters );
			} else {
				// check for same text.
				$filters = array(
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'slug'    => 'customer',
							'compare' => '=',
							'val'     => $customer->id,
						),
						array(
							'slug'    => 'type',
							'compare' => 'IN',
							'val'     => array( 'report' ),
						),
						array(
							'slug'    => 'is_active',
							'compare' => '=',
							'val'     => '1',
						),
					),
					'orderby'        => 'id',
					'order'          => 'DESC',
					'items_per_page' => 1,
				);
				$threads = WPSC_Thread::find( $filters );
			}

			$last_reply = isset( $threads['results'][0] ) ? $threads['results'][0] : '';
			if ( ! $last_reply ) {
				return false;
			}

			// check for similarity in text.
			similar_text( $last_reply->body, $description, $perc );

			// check for time difference in two replies.
			$reply_date = $last_reply->date_created;
			$diff = ( new DateTime() )->diff( $reply_date );
			$min_diff = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;

			if ( $perc > 95 && $min_diff <= 5 ) {

				$this->email_logger[] = 'Duplicate found: duplicate email with more than 95% similarity and email time is less than 5 minutes.';
				return true;
			}

			return false;
		}
	}
endif;
