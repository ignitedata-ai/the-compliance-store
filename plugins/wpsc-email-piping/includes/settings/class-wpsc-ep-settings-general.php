<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Settings_General' ) ) :

	final class WPSC_EP_Settings_General {

		/**
		 * Connections
		 *
		 * @var array
		 */
		public static $piping_connections = array();

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// connections.
			add_action( 'init', array( __CLASS__, 'set_piping_connections' ) );

			// setting actions.
			add_action( 'wp_ajax_wpsc_ep_get_general_settings', array( __CLASS__, 'get_general_settings' ) );
			add_action( 'wp_ajax_wpsc_ep_set_general_settings', array( __CLASS__, 'set_general_settings' ) );
			add_action( 'wp_ajax_wpsc_ep_reset_general_settings', array( __CLASS__, 'reset_general_settings' ) );
		}

		/**
		 * Install settings
		 *
		 * @return void
		 */
		public static function reset() {

			// block email defauls.
			$block_emails = array(
				'/no(t)?(\\-|_)?reply@/i', // noreply.
				'/mail(er)?[\\-_]daemon@/i', // mailer daemon.
			);

			// block subject defauls.
			$block_subject = array(
				'/^[\\[\\(]?Auto(mat(ic|ed))?[\\s\\-]?reply/i', // automatic reply.
				'/^Out of Office/i', // out of office.
				'/Delivery Status Notification \\(Failure\\)/i', // delevery failure.
				'/Returned mail\\: see transcript for details/i', // delevery failure.
				'DELIVERY FAILURE', // delevery failure.
				'Undelivered Mail Returned to Sender', // delevery failure.
			);

			update_option(
				'wpsc-ep-general-settings',
				array(
					'connection'               => 'imap',
					'reply-above-text'         => '-- Reply Above --',
					'allowed-emails'           => 'all',
					'allowed-users'            => 'anyone',
					'body-reference'           => 'text',
					'import-cc'                => 0,
					'time-frequency'           => 5,
					'block-emails'             => $block_emails,
					'block-subject'            => $block_subject,
					'forwarding-addresses'     => array(),
					'forwarding-as-from-email' => 0,
					'delete-email-logs-after'  => 30,
					'spam-filter'              => 1,
				)
			);
		}

		/**
		 * Set piping connections
		 *
		 * @return void
		 */
		public static function set_piping_connections() {

			self::$piping_connections = apply_filters(
				'wpsc_ep_piping_connections',
				array(
					'imap'               => array(
						'label'         => 'IMAP',
						'class'         => 'WPSC_EP_IMAP_Importer',
						'setting-class' => 'WPSC_EP_Settings_Imap',
					),
					'gmail'              => array(
						'label'         => 'Gmail',
						'class'         => 'WPSC_EP_Gmail_Importer',
						'setting-class' => 'WPSC_EP_Settings_Gmail',
					),
					'microsoft-exchange' => array(
						'label'         => 'Microsoft Exchange',
						'class'         => 'WPSC_EP_ME_Importer',
						'setting-class' => 'WPSC_EP_Settings_ME',
					),
				)
			);
		}

		/**
		 * Get general settings
		 *
		 * @return void
		 */
		public static function get_general_settings() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$general = get_option( 'wpsc-ep-general-settings' );
			?>
			<form action="#" onsubmit="return false;" class="wpsc-ep-general-settings">
				<div class="wpsc-dock-container">
					<?php
					printf(
						/* translators: Click here to see the documentation */
						esc_attr__( '%s to see the documentation!', 'supportcandy' ),
						'<a href="https://supportcandy.net/docs/general-settings-2/" target="_blank">' . esc_attr__( 'Click here', 'supportcandy' ) . '</a>'
					);
					?>
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Connection', 'wpsc-ep' ); ?></label>
					</div>
					<select name="connection">
						<?php
						foreach ( self::$piping_connections as $key => $connection ) {
							?>
							<option <?php selected( $key, $general['connection'] ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $connection['label'] ); ?></option>
							<?php
						}
						?>
					</select>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Reply above text', 'wpsc-ep' ); ?></label>
					</div>
					<input type="text" name="reply-above-text" value="<?php echo esc_attr( $general['reply-above-text'] ); ?>">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Allowed emails', 'wpsc-ep' ); ?></label>
					</div>
					<select name="allowed-emails">
						<option <?php selected( $general['allowed-emails'], 'all' ); ?> value="all"><?php esc_attr_e( 'All emails', 'wpsc-ep' ); ?></option>
						<option <?php selected( $general['allowed-emails'], 'new' ); ?> value="new"><?php esc_attr_e( 'New emails only', 'wpsc-ep' ); ?></option>
						<option <?php selected( $general['allowed-emails'], 'reply' ); ?> value="reply"><?php esc_attr_e( 'Reply emails only', 'wpsc-ep' ); ?></option>
					</select>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Allowed users', 'wpsc-ep' ); ?></label>
					</div>
					<select name="allowed-users">
						<option <?php selected( $general['allowed-users'], 'anyone' ); ?> value="anyone"><?php esc_attr_e( 'Anyone (including guest users)', 'wpsc-ep' ); ?></option>
						<option <?php selected( $general['allowed-users'], 'registered' ); ?> value="registered"><?php esc_attr_e( 'Registered users only', 'wpsc-ep' ); ?></option>
					</select>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Email body preference', 'wpsc-ep' ); ?></label>
					</div>
					<select name="body-reference">
						<option <?php selected( $general['body-reference'], 'text' ); ?> value="text"><?php echo esc_attr( wpsc__( 'Text', 'supportcandy' ) ); ?></option>
						<option <?php selected( $general['body-reference'], 'html' ); ?> value="html"><?php esc_attr_e( 'HTML', 'wpsc-ep' ); ?></option>
					</select>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Import other emails as additional recipients', 'wpsc-ep' ); ?></label>
					</div>
					<select name="import-cc">
						<option <?php selected( $general['import-cc'], '1' ); ?> value="1"><?php echo esc_attr( wpsc__( 'Yes', 'supportcandy' ) ); ?></option>
						<option <?php selected( $general['import-cc'], '0' ); ?> value="0"><?php echo esc_attr( wpsc__( 'No', 'supportcandy' ) ); ?></option>
					</select>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Time frequency to check new emails (minutes). Must be less than 60 minutes.', 'wpsc-ep' ); ?></label>
					</div>
					<input type="number" name="time-frequency" value="<?php echo esc_attr( $general['time-frequency'] ); ?>">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Block by email address (one per line)', 'wpsc-ep' ); ?></label>
					</div>
					<?php $block_email = implode( PHP_EOL, $general['block-emails'] ); ?>
					<textarea name="block-emails" rows="5"><?php echo esc_attr( $block_email ); ?></textarea>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Block by subject (one per line)', 'wpsc-ep' ); ?></label>
					</div>
					<?php $block_subject = implode( PHP_EOL, $general['block-subject'] ); ?>
					<textarea name="block-subject" rows="5"><?php echo esc_attr( $block_subject ); ?></textarea>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Forwarding email addresses (one per line)', 'wpsc-ep' ); ?></label>
					</div>
					<?php $forwarding_addresses = implode( PHP_EOL, $general['forwarding-addresses'] ); ?>
					<textarea name="forwarding-addresses" rows="5"><?php echo esc_attr( $forwarding_addresses ); ?></textarea>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Use forwarding address as "From Email" in ticket email notifications', 'wpsc-ep' ); ?></label>
					</div>
					<select name="forwarding-as-from-email">
						<option <?php selected( $general['forwarding-as-from-email'], '1' ); ?> value="1"><?php echo esc_attr( wpsc__( 'Yes', 'supportcandy' ) ); ?></option>
						<option <?php selected( $general['forwarding-as-from-email'], '0' ); ?> value="0"><?php echo esc_attr( wpsc__( 'No', 'supportcandy' ) ); ?></option>
					</select>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Spam filter', 'wpsc-ep' ); ?></label>
					</div>
					<select name="spam-filter">
						<option <?php selected( $general['spam-filter'], '1' ); ?> value="1"><?php echo esc_attr( wpsc__( 'Enable', 'supportcandy' ) ); ?></option>
						<option <?php selected( $general['spam-filter'], '0' ); ?> value="0"><?php echo esc_attr( wpsc__( 'Disable', 'supportcandy' ) ); ?></option>
					</select>
				</div>
				
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Delete email logs after', 'wpsc-ep' ); ?></label>
					</div>
					<select name="delete-email-logs-after">
						<option <?php selected( $general['delete-email-logs-after'], '7' ); ?> value="7"><?php echo esc_attr__( '7 days', 'wpsc-ep' ); ?></option>
						<option <?php selected( $general['delete-email-logs-after'], '15' ); ?> value="15"><?php echo esc_attr__( '15 days', 'wpsc-ep' ); ?></option>
						<option <?php selected( $general['delete-email-logs-after'], '30' ); ?> value="30"><?php echo esc_attr__( '30 days', 'wpsc-ep' ); ?></option>
						<option <?php selected( $general['delete-email-logs-after'], '90' ); ?> value="90"><?php echo esc_attr__( '90 days', 'wpsc-ep' ); ?></option>
						<option <?php selected( $general['delete-email-logs-after'], '180' ); ?> value="180"><?php echo esc_attr__( '180 days', 'wpsc-ep' ); ?></option>
					</select>
				</div>
				
				<?php do_action( 'wpsc_ep_get_general_settings' ); ?>

				<input type="hidden" name="action" value="wpsc_ep_set_general_settings">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_set_general_settings' ) ); ?>">

			</form>

			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_ep_set_general_settings(this);">
					<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_ep_reset_general_settings(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_reset_general_settings' ) ); ?>');">
					<?php echo esc_attr( wpsc__( 'Reset default', 'supportcandy' ) ); ?></button>
			</div>
			<?php

			wp_die();
		}

		/**
		 * Set general settings
		 *
		 * @return void
		 */
		public static function set_general_settings() {

			if ( check_ajax_referer( 'wpsc_ep_set_general_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$reply_above_text = isset( $_POST['reply-above-text'] ) ? sanitize_text_field( wp_unslash( $_POST['reply-above-text'] ) ) : '';

			$block_emails = isset( $_POST['block-emails'] ) ? sanitize_textarea_field( wp_unslash( $_POST['block-emails'] ) ) : '';
			$block_emails = array_filter( array_map( 'sanitize_text_field', explode( PHP_EOL, $block_emails ) ) );

			$block_subject = isset( $_POST['block-subject'] ) ? sanitize_textarea_field( wp_unslash( $_POST['block-subject'] ) ) : '';
			$block_subject = array_filter( array_map( 'sanitize_text_field', explode( PHP_EOL, $block_subject ) ) );

			$forwarding_addresses = isset( $_POST['forwarding-addresses'] ) ? sanitize_textarea_field( wp_unslash( $_POST['forwarding-addresses'] ) ) : '';
			$forwarding_addresses = array_filter( array_map( 'sanitize_text_field', explode( PHP_EOL, $forwarding_addresses ) ) );

			update_option(
				'wpsc-ep-general-settings',
				array(
					'connection'               => isset( $_POST['connection'] ) ? sanitize_text_field( wp_unslash( $_POST['connection'] ) ) : 'imap',
					'reply-above-text'         => $reply_above_text,
					'allowed-emails'           => isset( $_POST['allowed-emails'] ) ? sanitize_text_field( wp_unslash( $_POST['allowed-emails'] ) ) : 'all',
					'allowed-users'            => isset( $_POST['allowed-users'] ) ? sanitize_text_field( wp_unslash( $_POST['allowed-users'] ) ) : 'anyone',
					'body-reference'           => isset( $_POST['body-reference'] ) ? sanitize_text_field( wp_unslash( $_POST['body-reference'] ) ) : 'text',
					'import-cc'                => isset( $_POST['import-cc'] ) ? intval( $_POST['import-cc'] ) : 0,
					'time-frequency'           => isset( $_POST['time-frequency'] ) && intval( $_POST['time-frequency'] ) < 60 ? intval( $_POST['time-frequency'] ) : 5,
					'block-emails'             => $block_emails,
					'block-subject'            => $block_subject,
					'forwarding-addresses'     => $forwarding_addresses,
					'forwarding-as-from-email' => isset( $_POST['forwarding-as-from-email'] ) ? intval( $_POST['forwarding-as-from-email'] ) : 0,
					'delete-email-logs-after'  => isset( $_POST['delete-email-logs-after'] ) ? intval( $_POST['delete-email-logs-after'] ) : 30,
					'spam-filter'              => isset( $_POST['spam-filter'] ) ? intval( $_POST['spam-filter'] ) : 0,
				)
			);

			// reset cron schedule.
			WPSC_EP_Cron::unschedule_event();

			do_action( 'wpsc_ep_set_general_settings' );

			wp_die();
		}

		/**
		 * Reset general settings
		 *
		 * @return void
		 */
		public static function reset_general_settings() {

			if ( check_ajax_referer( 'wpsc_ep_reset_general_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			self::reset();

			do_action( 'wpsc_ep_reset_general_settings' );

			wp_die();
		}

		/**
		 * Return piping main email address
		 *
		 * @return String
		 */
		public static function get_piping_email_address() {

			$general = get_option( 'wpsc-ep-general-settings', array() );
			$email = '';
			if ( isset( $general['connection'] ) ) {
				$email = self::$piping_connections[ $general['connection'] ]['setting-class']::get_piping_email_address();
			}
			return $email;
		}

		/**
		 * Return array of forwarding email addresses
		 *
		 * @return array
		 */
		public static function get_forwarding_addresses() {

			$general = get_option( 'wpsc-ep-general-settings', array() );
			return $general['forwarding-addresses'];
		}
	}
endif;

WPSC_EP_Settings_General::init();
