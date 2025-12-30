<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Action' ) ) :

	final class WPSC_EP_Action {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// email piping/thread source.
			add_filter( 'wpsc_source_list', array( __CLASS__, 'add_ticket_source' ) );

			// email piping/thread last reply source.
			add_filter( 'wpsc_last_reply_source_list', array( __CLASS__, 'add_last_reply_source' ) );

			// Merge piping and forwarding email.
			add_filter( 'wpsc_en_blocked_emails', array( __CLASS__, 'en_blocked_emails' ) );

			add_filter( 'wpsc_thread_schema', array( __CLASS__, 'update_thread_schema' ) );

			add_action( 'wpsc_thread_info_body', array( __CLASS__, 'ep_thread_info' ) );
		}

		/**
		 * Ticket/thread source list
		 *
		 * @param array $sources - source name.
		 * @return array
		 */
		public static function add_ticket_source( $sources ) {

			$sources['imap']  = 'IMAP';
			$sources['gmail'] = 'Gmail';
			$sources['me'] = 'Microsoft Exchange';
			return $sources;
		}

		/**
		 * Ticket/thread last reply source list
		 *
		 * @param array $sources - source name.
		 * @return array
		 */
		public static function add_last_reply_source( $sources ) {

			$sources['imap']  = 'IMAP';
			$sources['gmail'] = 'Gmail';
			$sources['me'] = 'Microsoft Exchange';
			return $sources;
		}


		/**
		 * Merge piping and forwarding email addresses in blocked email address
		 *
		 * @param array $block_emails - blocked emails.
		 * @return array
		 */
		public static function en_blocked_emails( $block_emails ) {

			$general = get_option( 'wpsc-ep-general-settings' );
			$piping_address = WPSC_EP_Settings_General::get_piping_email_address();
			$block_emails = array_merge( $block_emails, array( $piping_address ) );
			$block_emails = array_merge( $block_emails, $general['forwarding-addresses'] );
			return array_values( array_filter( $block_emails ) );
		}

		/**
		 * Update thread schema.
		 *
		 * @param array $schema - thread schema.
		 * @return array
		 */
		public static function update_thread_schema( $schema ) {

			$schema['message_id'] = array(
				'has_ref'          => false,
				'ref_class'        => '',
				'has_multiple_val' => false,
			);
			$schema['other_recipients'] = array(
				'has_ref'          => false,
				'ref_class'        => '',
				'has_multiple_val' => true,
			);
			return $schema;
		}

		/**
		 * EP Thread log.
		 *
		 * @param array $thread - thread log.
		 * @return void
		 */
		public static function ep_thread_info( $thread ) {

			$other_recipients = $thread->other_recipients ? implode( ', ', $thread->other_recipients ) : esc_attr__( 'Not Applicable', 'supportcandy' );
			?>
			<div class="info-list-item">
				<div class="info-label"><?php esc_attr_e( 'Other recipients', 'supportcandy' ); ?>:</div>
				<div class="info-val">
				<div class="info-val"><?php echo esc_attr( $other_recipients ); ?></div>
				</div>
			</div>
			<?php
		}
	}

endif;
WPSC_EP_Action::init();
