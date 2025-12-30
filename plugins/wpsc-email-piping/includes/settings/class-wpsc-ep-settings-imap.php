<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Settings_Imap' ) ) :

	final class WPSC_EP_Settings_Imap {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// settings ui.
			add_action( 'wp_ajax_wpsc_ep_get_imap_settings', array( __CLASS__, 'get_imap_settings' ) );
			add_action( 'wp_ajax_wpsc_ep_set_imap_settings', array( __CLASS__, 'set_imap_settings' ) );
			add_action( 'wp_ajax_wpsc_ep_reset_imap_settings', array( __CLASS__, 'reset_imap_settings' ) );

			// connection error admin notice.
			add_action( 'admin_notices', array( __CLASS__, 'connection_error_admin_notice' ) );
			add_action( 'wp_ajax_wpsc_dismiss_imap_connection_error_notice', array( __CLASS__, 'dismiss_imap_connection_error_notice' ) );
		}

		/**
		 * Reset settings
		 *
		 * @return void
		 */
		public static function reset() {

			update_option(
				'wpsc-ep-imap-settings',
				array(
					'email-address'        => '',
					'password'             => '',
					'encryption'           => 'ssl',
					'incoming-mail-server' => '',
					'port'                 => '',
					'is_active'            => 0,
					'last-error'           => '',
				)
			);
		}

		/**
		 * Load imap settings
		 *
		 * @return void
		 */
		public static function get_imap_settings() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$imap = get_option( 'wpsc-ep-imap-settings' );?>
			<form action="#" onsubmit="return false;" class="wpsc-ep-imap-settings">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Email Address', 'wpsc-ep' ); ?></label>
					</div>
					<input type="text" name="email-address" value="<?php echo esc_attr( $imap['email-address'] ); ?>">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Password', 'wpsc-ep' ); ?></label>
					</div>
					<input type="password" name="password" value="<?php echo esc_attr( $imap['password'] ); ?>">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">Encryption</label>
					</div>
					<select name="encryption">
						<option <?php selected( $imap['encryption'], 'ssl' ); ?> value="ssl">SSL</option>
						<option <?php selected( $imap['encryption'], 'tls' ); ?> value="tls">TLS</option>
						<option <?php selected( $imap['encryption'], 'none' ); ?> value="none">None</option>
					</select>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">Incoming Mail Server</label>
					</div>
					<input type="text" name="incoming-mail-server" value="<?php echo esc_attr( $imap['incoming-mail-server'] ); ?>">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">Port</label>
					</div>
					<input type="number" name="port" value="<?php echo esc_attr( $imap['port'] ); ?>">
				</div>
				<input type="hidden" name="action" value="wpsc_ep_set_imap_settings">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_set_imap_settings' ) ); ?>">

			</form>

			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_ep_set_imap_settings(this);">
					<?php esc_attr_e( 'Save & Connect', 'wpsc-ep' ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_ep_reset_imap_settings(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_reset_imap_settings' ) ); ?>');">
					<?php echo esc_attr( wpsc__( 'Reset default', 'supportcandy' ) ); ?></button>
			</div>
			<?php
			if ( $imap['is_active'] ) {
				?>
				<div style="margin-top: 15px; color: #009432;"><?php esc_attr_e( 'Connected !', 'wpsc-ep' ); ?></div>
				<?php
			} else {
				?>
				<div style="margin-top: 15px; color: #ff0000;"><?php esc_attr_e( 'Not Connected !', 'wpsc-ep' ); ?></div>
				<?php
				if ( $imap['last-error'] ) {
					?>
					<div style="margin-top: 15px; color: #ff0000;"><?php echo 'Error: ' . esc_attr( $imap['last-error'] ); ?></div>
					<?php
				}
			}

			wp_die();
		}

		/**
		 * Set imap settings
		 *
		 * @return void
		 */
		public static function set_imap_settings() {

			if ( check_ajax_referer( 'wpsc_ep_set_imap_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			// initialize the connection.
			set_transient( 'wpsc_ep_connection_init', 'yes', MINUTE_IN_SECONDS * 10 );

			$imap = get_option( 'wpsc-ep-imap-settings' );

			$email_address = isset( $_POST['email-address'] ) ? sanitize_text_field( wp_unslash( $_POST['email-address'] ) ) : '';
			if ( ! $email_address ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}
			$imap['email-address'] = $email_address;

			$password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore
			if ( ! $password ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}
			$imap['password'] = $password;

			$encryption         = isset( $_POST['encryption'] ) ? sanitize_text_field( wp_unslash( $_POST['encryption'] ) ) : 'ssl';
			$imap['encryption'] = $encryption;

			$incoming_mail_server = isset( $_POST['incoming-mail-server'] ) ? sanitize_text_field( wp_unslash( $_POST['incoming-mail-server'] ) ) : '';
			if ( ! $incoming_mail_server ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}
			$imap['incoming-mail-server'] = $incoming_mail_server;

			$port = isset( $_POST['port'] ) ? sanitize_text_field( wp_unslash( $_POST['port'] ) ) : '';
			if ( ! $port ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}
			$imap['port'] = $port;

			$imap['is_active'] = 0;

			$encryption_text = '';
			if ( $encryption == 'none' ) {
				$encryption_text = 'novalidate-cert';
			} elseif ( $encryption == 'ssl' ) {
				$encryption_text = 'imap/ssl/novalidate-cert';
			} elseif ( $encryption == 'tls' ) {
				$encryption_text = 'imap/tls/novalidate-cert';
			}

			$flag = true;
			if ( ! extension_loaded( 'imap' ) ) {
				$flag               = false;
				$imap['last-error'] = '<strong>php-imap</strong> module not enabled on your server. Enable it from your cPanel or contact to your host provider.';
			}

			$user = get_user_by( 'email', $imap['email-address'] );
			if ( $flag && $user ) {
				$flag               = false;
				$imap['last-error'] = 'Email account belong to registered user is not allowed.';
			}

			if ( $flag ) {

				$conn = @imap_open( '{' . $incoming_mail_server . ':' . $port . '/' . $encryption_text . '}INBOX', $email_address, $password ); // phpcs:ignore

				if ( ! $conn ) {
					$flag               = false;
					$imap['last-error'] = imap_last_error();
				} else {
					$uids     = imap_search( $conn, 'ALL', SE_UID );
					$last_uid = $uids ? $uids[ count( $uids ) - 1 ] : 0;
					update_option( 'wpsc_ep_imap_uid', $last_uid );
				}
			}

			if ( $flag ) {
				$imap['is_active'] = 1;
			}

			update_option( 'wpsc-ep-imap-settings', $imap );
			delete_transient( 'wpsc_ep_connection_init' );
			wp_die();
		}

		/**
		 * Reset imap settings
		 *
		 * @return void
		 */
		public static function reset_imap_settings() {

			if ( check_ajax_referer( 'wpsc_ep_reset_imap_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			// show connection error notice.
			update_option( 'wpsc-ep-imap-connection-notice', 1 );

			self::reset();
			wp_die();
		}

		/**
		 * Return piping email address
		 *
		 * @return String
		 */
		public static function get_piping_email_address() {

			$imap = get_option( 'wpsc-ep-imap-settings' );
			return $imap['email-address'];
		}

		/**
		 * IMAP Connection error admin notice
		 *
		 * @return void
		 */
		public static function connection_error_admin_notice() {

			// return if current user does not have administrator previlages.
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( ! $current_user->is_guest && $current_user->user->has_cap( 'manage_options' ) ) ) {
				return;
			}

			$general           = get_option( 'wpsc-ep-general-settings' );
			$imap              = get_option( 'wpsc-ep-imap-settings' );
			$connection_notice = get_option( 'wpsc-ep-imap-connection-notice', 1 );

			// return if either connection is active or notice dismissed.
			if ( ! ( $general['connection'] == 'imap' && ! $imap['is_active'] && $connection_notice ) ) {
				return;
			}
			?>
			<div class="supportcandy imap connection-error notice notice-error">
				<p><?php esc_attr_e( 'SupportCandy: Email piping IMAP not connected!', 'wpsc-ep' ); ?></p>
				<p>
					<a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=wpsc-settings&section=email-piping&tab=imap' ) ); ?>">
						<?php echo esc_attr( wpsc_translate_common_strings( 'view-details' ) ); ?>
					</a>
					<a href="javascript:wpsc_dismiss_imap_connection_error_notice('<?php echo esc_attr( wp_create_nonce( 'wpsc_dismiss_imap_connection_error_notice' ) ); ?>');" style="margin-left:5px;"><?php esc_attr_e( 'Dismiss', 'wpsc-imap' ); ?></a>
				</p>
			</div>
			<?php
		}

		/**
		 * Dismiss imap connection error notice
		 *
		 * @return void
		 */
		public static function dismiss_imap_connection_error_notice() {

			if ( check_ajax_referer( 'wpsc_dismiss_imap_connection_error_notice', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			update_option( 'wpsc-ep-imap-connection-notice', 0 );
			wp_die();
		}
	}
endif;

WPSC_EP_Settings_Imap::init();
