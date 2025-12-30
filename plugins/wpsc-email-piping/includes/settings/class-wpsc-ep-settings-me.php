<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Settings_ME' ) ) :

	final class WPSC_EP_Settings_ME {

		/**
		 * Number of records per page for initializtion script
		 *
		 * @var integer
		 */
		public static $records_per_page = 100;

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// settings ui.
			add_action( 'wp_ajax_wpsc_ep_get_me_settings', array( __CLASS__, 'get_settings' ) );
			add_action( 'wp_ajax_wpsc_ep_set_me_settings', array( __CLASS__, 'set_settings' ) );
			add_action( 'wp_ajax_wpsc_ep_reset_me_settings', array( __CLASS__, 'reset_settings' ) );

			// initialize delta.
			add_action( 'wp_ajax_wpsc_ep_me_init', array( __CLASS__, 'initialize_delta' ) );

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
				'wpsc-ep-me-settings',
				array(
					'email-address' => '',
					'client-id'     => '',
					'client-secret' => '',
					'is-active'     => 0,
					'last-error'    => '',
					'refresh-token' => '',
					'delta-url'     => '',
				)
			);
		}

		/**
		 * Load settings
		 *
		 * @return void
		 */
		public static function get_settings() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$me = get_option( 'wpsc-ep-me-settings' );
			if ( $me['is-active'] === 1 && $me['is-init'] === 0 ) {
				self::initialize_delta_ui();
			} elseif ( 'yes' === get_transient( 'wpsc_ep_connection_init' ) ) {
				delete_transient( 'wpsc_ep_connection_init' );
			}
			?>

			<form action="#" onsubmit="return false;" class="wpsc-ep-me-settings">

				<div class="wpsc-dock-container">
					<?php
					printf(
						/* translators: Click here to see the documentation */
						esc_attr__( '%s to see the documentation!', 'supportcandy' ),
						'<a href="https://supportcandy.net/docs/microsoft-exchange/" target="_blank">' . esc_attr__( 'Click here', 'supportcandy' ) . '</a>'
					);
					?>
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">Client ID</label><span class="required-char">*</span>
					</div>
					<input type="text" name="client-id" value="<?php echo esc_attr( $me['client-id'] ); ?>">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container"><label for="">Client Secret</label></div>
					<input type="text" name="client-secret" value="<?php echo esc_attr( $me['client-secret'] ); ?>">
				</div>

				<input type="hidden" name="action" value="wpsc_ep_set_me_settings">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_set_me_settings' ) ); ?>">

			</form>

			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_ep_set_me_settings(this);">
					<?php esc_attr_e( 'Save & Connect', 'wpsc-ep' ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_ep_reset_me_settings(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_reset_me_settings' ) ); ?>');">
					<?php echo esc_attr( wpsc__( 'Reset default', 'supportcandy' ) ); ?></button>
			</div>

			<table class="wpsc-info-listing">
				<thead>
					<tr>
						<th><?php esc_attr_e( 'Field', 'supportcandy' ); ?></th>
						<th><?php esc_attr_e( 'Value', 'supportcandy' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_attr_e( 'Status', 'supportcandy' ); ?></th>
						<td>
							<?php
							if ( $me['is-active'] ) {
								?>
								<span style="color: #009432;"><?php esc_attr_e( 'Connected !', 'wpsc-ep' ); ?></span>
								<?php
							} else {
								?>
								<span style="color: #ff0000;"><?php esc_attr_e( 'Not Connected !', 'wpsc-ep' ); ?></span>
								<?php
							}
							?>
						</td>
					</tr>
					<tr>
						<td><?php esc_attr_e( 'Redirect URI', 'wpsc-ep' ); ?></td>
						<td><?php echo esc_url( admin_url( 'admin.php' ) ); ?></td>
					</tr>
					<tr>
						<td><?php esc_attr_e( 'Piping Address', 'wpsc-ep' ); ?></td>
						<td><?php echo esc_attr( $me['email-address'] ); ?></td>
					</tr>
					<tr>
						<td><?php esc_attr_e( 'Last Error', 'wpsc-ep' ); ?></td>
						<td><?php echo esc_attr( $me['last-error'] ); ?></td>
					</tr>
				</tbody>
			</table>

			<script>
				jQuery('table.wpsc-info-listing').DataTable({
					ordering: false,
					pageLength: 20,
					searching:false,
					paging: false,
					info: false,
					language: supportcandy.translations.datatables
				});
			</script>
			<style>
				table.wpsc-info-listing {
					margin-top: 30px;
				}
			</style>

			<?php
			wp_die();
		}

		/**
		 * Set gmail settings
		 *
		 * @return void
		 */
		public static function set_settings() {

			if ( check_ajax_referer( 'wpsc_ep_set_me_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			// initialize the connection.
			set_transient( 'wpsc_ep_connection_init', 'yes', MINUTE_IN_SECONDS * 60 );

			$me = get_option( 'wpsc-ep-me-settings' );

			$client_id = isset( $_POST['client-id'] ) ? sanitize_text_field( wp_unslash( $_POST['client-id'] ) ) : '';
			if ( ! $client_id ) {
				wp_send_json_error( esc_attr__( 'Client ID should not be empty!', 'wpsc-ep' ), 400 );
			}
			$me['client-id'] = $client_id;

			$client_secret = isset( $_POST['client-secret'] ) ? sanitize_text_field( wp_unslash( $_POST['client-secret'] ) ) : '';
			if ( ! $client_secret ) {
				wp_send_json_error( esc_attr__( 'Client Secret should not be empty!', 'wpsc-ep' ), 400 );
			}
			$me['client-secret'] = $client_secret;

			update_option( 'wpsc-ep-me-settings', $me );

			// MS Graph oAuth URL.
			$url  = 'https://login.microsoftonline.com/organizations/oauth2/v2.0/authorize';
			$url .= '?client_id=' . $client_id;
			$url .= '&response_type=code';
			$url .= '&redirect_uri=' . esc_url_raw( admin_url( 'admin.php' ) );
			$url .= '&response_mode=query';
			$url .= '&scope=offline_access%20user.read%20mail.read%20mail.readwrite';
			$url .= '&state=wpsc_ep_me,' . wp_create_nonce( 'wpsc-ep-me' );

			wp_send_json( array( 'redirectURL' => esc_url_raw( $url ) ), 200 );
		}

		/**
		 * Reset gmail settings
		 *
		 * @return void
		 */
		public static function reset_settings() {

			if ( check_ajax_referer( 'wpsc_ep_reset_me_settings', '_ajax_nonce', false ) != 1 ) {
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

			$me = get_option( 'wpsc-ep-me-settings' );
			return $me['email-address'];
		}

		/**
		 * Process gmail connection response
		 *
		 * @return void
		 */
		public static function process_response() {

			$state = isset( $_REQUEST['state'] ) ? array_filter( array_map( 'sanitize_text_field', explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['state'] ) ) ) ) ) : array();
			if ( ! ( isset( $state[0] ) && $state[0] == 'wpsc_ep_me' ) ) {
				return;
			}

			// verify nonce.
			$nonce = isset( $state[1] ) ? $state[1] : '';
			if ( wp_verify_nonce( $nonce, 'wpsc-ep-me' ) != 1 ) {
				wp_die( 'Unauthorized request!' );
			}

			// check whether code received or not.
			$code = isset( $_REQUEST['code'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['code'] ) ) : '';
			if ( ! $code ) {
				wp_die( 'Bad request!' );
			}

			$me = get_option( 'wpsc-ep-me-settings' );
			$is_valid = true;

			// Get Refresh Token.
			$response = wp_remote_post(
				'https://login.microsoftonline.com/organizations/oauth2/v2.0/token?scope=offline_access%20user.read%20mail.read%20mail.readwrite',
				array(
					'body' => array(
						'client_id'     => $me['client-id'],
						'client_secret' => $me['client-secret'],
						'redirect_uri'  => admin_url( 'admin.php' ),
						'grant_type'    => 'authorization_code',
						'code'          => $code,
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				$is_valid = false;
				$me['last-error'] = $response->get_error_message();
			} elseif ( $is_valid && $response['response']['code'] !== 200 ) {

				$is_valid = false;
				$me['last-error'] = $response['body'];

			} else {

				$response = json_decode( $response['body'] );
				$me['refresh-token'] = $response->refresh_token;
				set_transient( 'wpsc_ep_me_access_token', $response->access_token, $response->expires_in - 5 );

				// get piping email address.
				$response = wp_remote_get(
					'https://graph.microsoft.com/v1.0/me',
					array(
						'headers' => array(
							'Authorization' => 'Bearer ' . $response->access_token,
						),
					)
				);

				if ( is_wp_error( $response ) ) {
					$is_valid = false;
					$me['last-error'] = $response->get_error_message();
				}

				if ( $is_valid && $response['response']['code'] !== 200 ) {
					$is_valid = false;
					$me['last-error'] = $response['body'];
				}

				if ( $is_valid ) {
					$response = json_decode( $response['body'] );
					$me['email-address'] = $response->mail;
				}
			}

			if ( $is_valid ) {
				$me['is-active'] = 1;
				$me['last-error'] = '';
				$me['delta-url'] = '';
				$me['is-init'] = 0;
				$me['init-current-page'] = 0;
				$me['init-total-pages'] = 0;
			}

			update_option( 'wpsc-ep-me-settings', $me );
			?>
			<script>
				window.location.href = "<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=wpsc-settings&section=email-piping&tab=microsoft-exchange";
			</script>
			<?php
		}

		/**
		 * Initialize inbox delta ui
		 *
		 * @return void
		 */
		private static function initialize_delta_ui() {

			$access_token = self::get_access_token();
			$me = get_option( 'wpsc-ep-me-settings' );

			// get inbox properties.
			if ( false !== $access_token ) {

				$response = wp_remote_get(
					'https://graph.microsoft.com/v1.0/me/mailFolders/inbox',
					array(
						'headers' => array(
							'Authorization' => 'Bearer ' . $access_token,
						),
					)
				);

				if ( ! self::is_valid_response( $response ) ) {
					$me['is-init'] = 0;
					$me['is-active'] = 0;
					update_option( 'wpsc-ep-me-settings', $me );
					?>
					<script>
						window.location.href = "<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=wpsc-settings&section=email-piping&tab=microsoft-exchange";
					</script>
					<?php
					wp_die();
				}

				$response = json_decode( $response['body'], true );

				if ( $response['totalItemCount'] == 0 ) {

					$response = wp_remote_get(
						'https://graph.microsoft.com/v1.0/me/mailFolders/inbox/messages/delta?changeType=created',
						array(
							'headers' => array(
								'Authorization' => 'Bearer ' . $access_token,
							),
						)
					);

					if ( self::is_valid_response( $response ) ) {
						$response = json_decode( $response['body'], true );
						$me['delta-url'] = $response['@odata.deltaLink'];
						$me['last-error'] = '';
						$me['is-init'] = 1;
					} else {
						$me['is-init'] = 0;
						$me['is-active'] = 0;
					}
					update_option( 'wpsc-ep-me-settings', $me );
					?>
					<script>
						window.location.href = "<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=wpsc-settings&section=email-piping&tab=microsoft-exchange";
					</script>
					<?php
					wp_die();
				}
			} else {

				$me['is-init'] = 0;
				$me['is-active'] = 0;
				$me['last-error'] = 'Invalid access token';
				update_option( 'wpsc-ep-me-settings', $me );
				?>
				<script>
					window.location.href = "<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=wpsc-settings&section=email-piping&tab=microsoft-exchange";
				</script>
				<?php
				wp_die();
			}

			$me['init-total-pages'] = ceil( $response['totalItemCount'] / self::$records_per_page );
			update_option( 'wpsc-ep-me-settings', $me );
			?>
			<div class="wpsc-loader">
				<img src="<?php echo esc_url( WPSC_PLUGIN_URL . 'asset/images/loader.gif' ); ?>" alt="Loading..." />
			</div>
			<h1 style="text-align:center;"><?php esc_html_e( 'Initializing...', 'wpsc-ep' ); ?><span class="percentage">0</span>%</h1>
			<script>
				wpsc_runner();
				async function wpsc_runner() {
					var runner = true;
					do {
						let response = await wpsc_ep_me_init();
						if ( response.success ) {
							let percentage = Math.round((response.currentPage/response.totalPages)*100);
							jQuery('span.percentage').text(percentage);
							if ( response.isInit ) {
								window.location.reload();
							}
						} else {
							runner = false;
							setTimeout(() => {
								console.log( 'Re-tring for connection!' );
								wpsc_runner();
							}, 10000);
						}
					} while( runner );
				}
				function wpsc_ep_me_init() {
					var data = { action: 'wpsc_ep_me_init' };
					return new Promise( resolve => {
						jQuery.post(supportcandy.ajax_url, data, function (response) {
							resolve(response);
						}).fail( function() {
							resolve({ success:false });
						});
					});
				}
			</script>
			<?php
			wp_die();
		}

		/**
		 * Initialize delta
		 *
		 * @return void
		 */
		public static function initialize_delta() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$access_token = self::get_access_token();
			if ( false === $access_token ) {
				wp_send_json( array( 'success' => false ), 200 );
			}

			$me = get_option( 'wpsc-ep-me-settings' );

			$url = $me['delta-url'] ? $me['delta-url'] : 'https://graph.microsoft.com/v1.0/me/mailFolders/inbox/messages/delta?changeType=created';
			$response = wp_remote_get(
				$url,
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
						'Prefer'        => 'odata.maxpagesize=' . self::$records_per_page,
					),
				)
			);

			$res = array(
				'success' => true,
				'isInit'  => false,
			);

			if ( self::is_valid_response( $response ) ) {

				$response = json_decode( $response['body'], true );
				if ( isset( $response['@odata.deltaLink'] ) ) { // initialization complete.
					$me['delta-url'] = $response['@odata.deltaLink'];
					$me['is-init'] = 1;
					$res['isInit'] = true;
				} else {
					$me['delta-url'] = $response['@odata.nextLink'];
				}

				$me['init-current-page'] = $me['init-current-page'] + 1;
				$me['last-error'] = '';
				update_option( 'wpsc-ep-me-settings', $me );

				$res['currentPage'] = $me['init-current-page'];
				$res['totalPages'] = $me['init-total-pages'];
			} else {
				$res['success'] = false;
			}

			wp_send_json( $res, 200 );
		}

		/**
		 * Get new access token using refresh token
		 *
		 * @return mixed
		 */
		public static function get_access_token() {

			$access_token = get_transient( 'wpsc_ep_me_access_token' );
			if ( $access_token !== false ) {
				return $access_token;
			}

			$me = get_option( 'wpsc-ep-me-settings' );
			if ( $me['refresh-token'] === '' ) {
				return false;
			}

			// get access token using refresh token.
			$response = wp_remote_post(
				'https://login.microsoftonline.com/organizations/oauth2/v2.0/token',
				array(
					'body' => array(
						'client_id'     => $me['client-id'],
						'client_secret' => $me['client-secret'],
						'grant_type'    => 'refresh_token',
						'refresh_token' => $me['refresh-token'],
					),
				)
			);

			if ( ! self::is_valid_response( $response ) ) {
				return false;
			}

			$response = json_decode( $response['body'] );
			set_transient( 'wpsc_ep_me_access_token', $response->access_token, $response->expires_in - 5 );
			return $response->access_token;
		}

		/**
		 * Check whether API response is valid
		 *
		 * @param array $response - REST API response array.
		 * @return boolean
		 */
		public static function is_valid_response( $response ) {

			$me = get_option( 'wpsc-ep-me-settings' );

			if ( is_wp_error( $response ) ) {

				$me['last-error'] = $response->get_error_message();
				update_option( 'wpsc-ep-me-settings', $me );
				return false;
			}

			if ( $response['response']['code'] !== 200 ) {

				$me['last-error'] = $response['body'];
				update_option( 'wpsc-ep-me-settings', $me );
				return false;

			} else {

				return true;
			}
		}
	}
endif;

WPSC_EP_Settings_ME::init();
