<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Settings_Gmail' ) ) :

	final class WPSC_EP_Settings_Gmail {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// settings ui.
			add_action( 'wp_ajax_wpsc_ep_get_gmail_settings', array( __CLASS__, 'get_gmail_settings' ) );
			add_action( 'wp_ajax_wpsc_ep_set_gmail_settings', array( __CLASS__, 'set_gmail_settings' ) );
			add_action( 'wp_ajax_wpsc_ep_reset_gmail_settings', array( __CLASS__, 'reset_gmail_settings' ) );

			// gmail process connection response.
			add_action( 'admin_init', array( __CLASS__, 'process_response' ) );
		}

		/**
		 * Reset settings
		 *
		 * @return void
		 */
		public static function reset() {

			update_option(
				'wpsc-ep-gmail-settings',
				array(
					'email-address' => '',
					'client-id'     => '',
					'client-secret' => '',
					'is-active'     => 0,
					'last-error'    => '',
					'refresh-token' => '',
					'history-id'    => '',
				)
			);
		}

		/**
		 * Load gmail settings
		 *
		 * @return void
		 */
		public static function get_gmail_settings() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$gmail = get_option( 'wpsc-ep-gmail-settings' );?>

			<form action="#" onsubmit="return false;" class="wpsc-ep-gmail-settings">

				<div class="wpsc-dock-container">
					<?php
					printf(
						/* translators: Click here to see the documentation */
						esc_attr__( '%s to see the documentation!', 'supportcandy' ),
						'<a href="https://supportcandy.net/docs/gmail-settings/" target="_blank">' . esc_attr__( 'Click here', 'supportcandy' ) . '</a>'
					);
					?>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container"><label for=""><?php esc_attr_e( 'Email Address', 'wpsc-ep' ); ?></label>
					<span class="required-char">*</span>
				</div>
					<input type="text" name="email-address" value="<?php echo esc_attr( $gmail['email-address'] ); ?>">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container"><label for="">Client ID</label></div>
					<input type="text" name="client-id" value="<?php echo esc_attr( $gmail['client-id'] ); ?>">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container"><label for="">Client Secret</label></div>
					<input type="text" name="client-secret" value="<?php echo esc_attr( $gmail['client-secret'] ); ?>">
				</div>

				<?php $url = wp_parse_url( home_url() ); ?>
				<div class="wpsc-input-group">
					<div class="label-container"><label for="">Authorized Javascript Origin</label></div>
					<input type="text" readonly value="<?php echo esc_url( $url['scheme'] . '://' . $url['host'] ); ?>">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container"><label for="">Authorized Redirect URI</label></div>
					<input type="text" readonly value="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
				</div>

				<input type="hidden" name="action" value="wpsc_ep_set_gmail_settings">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_set_gmail_settings' ) ); ?>">

			</form>

			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_ep_set_gmail_settings(this);">
					<?php esc_attr_e( 'Save & Connect', 'wpsc-ep' ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_ep_reset_gmail_settings(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_reset_gmail_settings' ) ); ?>');">
					<?php echo esc_attr( wpsc__( 'Reset default', 'supportcandy' ) ); ?></button>
			</div>
			<?php

			if ( $gmail['is-active'] ) {
				?>
				<div style="margin-top: 15px; color: #009432;"><?php esc_attr_e( 'Connected !', 'wpsc-ep' ); ?></div>
				<?php
			} else {
				?>
				<div style="margin-top: 15px; color: #ff0000;"><?php esc_attr_e( 'Not Connected !', 'wpsc-ep' ); ?></div>
				<?php
				if ( $gmail['last-error'] ) {
					?>
					<div style="margin-top: 15px; color: #ff0000;"><?php echo 'Error: ' . esc_attr( $gmail['last-error'] ); ?></div>
					<?php
				}
			}
			wp_die();
		}

		/**
		 * Set gmail settings
		 *
		 * @return void
		 */
		public static function set_gmail_settings() {

			if ( check_ajax_referer( 'wpsc_ep_set_gmail_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			// initialize the connection.
			set_transient( 'wpsc_ep_connection_init', 'yes', MINUTE_IN_SECONDS * 10 );

			$gmail = get_option( 'wpsc-ep-gmail-settings' );

			$email_address = isset( $_POST['email-address'] ) ? sanitize_text_field( wp_unslash( $_POST['email-address'] ) ) : '';
			if ( ! $email_address ) {
				wp_send_json_error( esc_attr__( 'Email Address should not be empty!', 'wpsc-ep' ), 400 );
			}
			if ( ! filter_var( $email_address, FILTER_VALIDATE_EMAIL ) ) {
				wp_send_json_error( esc_attr__( 'Invalid Email Address!', 'wpsc-ep' ), 400 );
			}
			$gmail['email-address'] = $email_address;

			$client_id = isset( $_POST['client-id'] ) ? sanitize_text_field( wp_unslash( $_POST['client-id'] ) ) : '';
			if ( ! $client_id ) {
				wp_send_json_error( esc_attr__( 'Client ID should not be empty!', 'wpsc-ep' ), 400 );
			}
			$gmail['client-id'] = $client_id;

			$client_secret = isset( $_POST['client-secret'] ) ? sanitize_text_field( wp_unslash( $_POST['client-secret'] ) ) : '';
			if ( ! $client_secret ) {
				wp_send_json_error( esc_attr__( 'Client Secret should not be empty!', 'wpsc-ep' ), 400 );
			}
			$gmail['client-secret'] = $client_secret;

			update_option( 'wpsc-ep-gmail-settings', $gmail );

			// Google oAuth URL.
			$url  = 'https://accounts.google.com/o/oauth2/v2/auth';
			$url .= '?scope=' . esc_url_raw( 'https://www.googleapis.com/auth/gmail.readonly' );
			$url .= '&access_type=offline';
			$url .= '&redirect_uri=' . esc_url_raw( admin_url( 'admin.php' ) );
			$url .= '&response_type=code';
			$url .= '&state=wpsc_ep,' . wp_create_nonce( 'wpsc-ep-gmail' );
			$url .= '&client_id=' . $client_id;

			wp_send_json( array( 'redirectURL' => esc_url_raw( $url ) ), 200 );
		}

		/**
		 * Reset gmail settings
		 *
		 * @return void
		 */
		public static function reset_gmail_settings() {

			if ( check_ajax_referer( 'wpsc_ep_reset_gmail_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			self::reset();
			wp_die();
		}

		/**
		 * Return piping email address
		 *
		 * @return String
		 */
		public static function get_piping_email_address() {

			$gmail = get_option( 'wpsc-ep-gmail-settings' );
			return $gmail['email-address'];
		}

		/**
		 * Process gmail connection response
		 *
		 * @return void
		 */
		public static function process_response() {

			$state = isset( $_REQUEST['state'] ) ? array_filter( array_map( 'sanitize_text_field', explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['state'] ) ) ) ) ) : array();
			if ( ! ( isset( $state[0] ) && $state[0] == 'wpsc_ep' ) ) {
				return;
			}

			// verify nonce.
			$nonce = isset( $state[1] ) ? $state[1] : '';
			if ( wp_verify_nonce( $nonce, 'wpsc-ep-gmail' ) != 1 ) {
				wp_die( 'Unauthorized request!' );
			}

			// check whether code received or not.
			$code = isset( $_REQUEST['code'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['code'] ) ) : '';
			if ( ! $code ) {
				wp_die( 'Bad request!' );
			}

			$gmail    = get_option( 'wpsc-ep-gmail-settings' );
			$is_valid = true;

			// Get Refresh and Access Tokens.
			$url      = 'https://www.googleapis.com/oauth2/v4/token';
			$response = wp_remote_post(
				$url,
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
						'redirect_uri'  => admin_url( 'admin.php' ),
						'code'          => $code,
						'grant_type'    => 'authorization_code',
					),
					'cookies'     => array(),
				)
			);

			if ( is_wp_error( $response ) ) {
				$is_valid            = false;
				$gmail['last-error'] = $response->get_error_message();
			}

			// refresh token.
			if ( $is_valid ) {

				$access = json_decode( $response['body'], true );
				if ( isset( $access['refresh_token'] ) ) {

					$gmail['refresh-token'] = $access['refresh_token'];

				} else {

					$is_valid            = false;
					$gmail['last-error'] = 'Refresh token not found!';
				}
			}

			// get last history id.
			if ( $is_valid ) {

				$access   = json_decode( $response['body'], true );
				$response = wp_remote_post(
					'https://www.googleapis.com/gmail/v1/users/' . $gmail['email-address'] . '/profile',
					array(
						'method'      => 'GET',
						'timeout'     => 45,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking'    => true,
						'headers'     => array(),
						'body'        => array(
							'access_token' => $access['access_token'],
						),
						'cookies'     => array(),
					)
				);

				if ( is_wp_error( $response ) ) {
					$is_valid            = false;
					$gmail['last-error'] = $response->get_error_message();
				}

				if ( $is_valid ) {
					$profile = json_decode( $response['body'], true );
					if ( isset( $profile['historyId'] ) ) {
						$gmail['history-id'] = $profile['historyId'];
					} else {
						$is_valid            = false;
						$gmail['last-error'] = 'History ID not found!';
					}
				}
			}

			if ( $is_valid ) {
				$gmail['is-active'] = 1;
			}

			update_option( 'wpsc-ep-gmail-settings', $gmail );
			delete_transient( 'wpsc_ep_connection_init' );
			echo 'Please wait!';
			?>
			<script>
				window.location.href = "<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=wpsc-settings&section=email-piping&tab=gmail";
			</script>
			<?php
		}
	}
endif;

WPSC_EP_Settings_Gmail::init();
