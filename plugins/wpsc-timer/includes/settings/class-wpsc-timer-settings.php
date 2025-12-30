<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Timer_Settings' ) ) :

	final class WPSC_Timer_Settings {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// add settings section.
			add_filter( 'wpsc_settings_page_sections', array( __CLASS__, 'add_settings_tab' ) );

			// Load section tab layout.
			add_action( 'wp_ajax_wpsc_get_timer_settings', array( __CLASS__, 'get_timer_settings' ) );
			add_action( 'wp_ajax_wpsc_set_timer_setting', array( __CLASS__, 'set_timer_setting' ) );
			add_action( 'wp_ajax_wpsc_reset_timer_setting', array( __CLASS__, 'reset_timer_setting' ) );
		}

		/**
		 * Reset settings
		 *
		 * @return void
		 */
		public static function reset() {

			$agent = WPSC_Agent::find(
				array(
					'orderby'    => 'id',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'slug'    => 'is_agentgroup',
							'compare' => '=',
							'val'     => 0,
						),
					),
				)
			)['results'][0];

			update_option(
				'wpsc-timer-settings',
				array(
					'auto-start' => 0,
					'auto-stop'  => 0,
					'agent_id'   => $agent->id,
				)
			);
		}

		/**
		 * Settings tab
		 *
		 * @param array $sections - section name.
		 * @return array
		 */
		public static function add_settings_tab( $sections ) {

			$sections['timer'] = array(
				'slug'     => 'timer',
				'icon'     => 'stopwatch',
				'label'    => esc_attr__( 'Timer', 'wpsc-timer' ),
				'callback' => 'wpsc_get_timer_settings',
			);
			return $sections;
		}

		/**
		 * General setion body layout
		 *
		 * @return void
		 */
		public static function get_timer_settings() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$setting = get_option( 'wpsc-timer-settings' );
			$agent   = new WPSC_Agent( $setting['agent_id'] );?>

			<div class="wpsc-setting-header">
				<h2><?php esc_attr_e( 'Timer', 'wpsc-timer' ); ?></h2>
			</div>
			<div class="wpsc-setting-section-body">
				<form action="#" onsubmit="return false;" class="wpsc-timer-setting">
					<div class="wpsc-input-group">
						<div class="label-container">
							<label for=""><?php esc_attr_e( 'Auto start on create new ticket', 'wpsc-timer' ); ?></label>
						</div>
						<select name="auto-start">
							<option <?php selected( $setting['auto-start'], 1 ); ?> value="1"><?php echo esc_attr( wpsc__( 'Yes', 'supportcandy' ) ); ?></option>
							<option <?php selected( $setting['auto-start'], 0 ); ?> value="0"><?php echo esc_attr( wpsc__( 'No', 'supportcandy' ) ); ?></option>
						</select>
					</div>
					<div class="wpsc-input-group">
						<div class="label-container">
							<label for=""><?php esc_attr_e( 'Default agent for auto start', 'wpsc-timer' ); ?></label>
						</div>
						<select name="agent_id" class="agent">
							<?php
							if ( $agent->id ) {
								?>
								<option value="<?php echo esc_attr( $agent->id ); ?>"><?php echo esc_attr( $agent->name ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="wpsc-input-group">
						<div class="label-container">
							<label for=""><?php esc_attr_e( 'Auto stop on close ticket', 'wpsc-timer' ); ?></label>
						</div>
						<select name="auto-stop">
							<option <?php selected( $setting['auto-stop'], 1 ); ?> value="1"><?php echo esc_attr( wpsc__( 'Yes', 'supportcandy' ) ); ?></option>
							<option <?php selected( $setting['auto-stop'], 0 ); ?> value="0"><?php echo esc_attr( wpsc__( 'No', 'supportcandy' ) ); ?></option>
						</select>
					</div>
					<?php do_action( 'wpsc_timer_settings' ); ?>
					<input type="hidden" name="action" value="wpsc_set_timer_setting">
					<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_timer_setting' ) ); ?>">
					<script>
						// agent autocomplete.
						jQuery('select.agent').selectWoo({
							ajax: {
								url: supportcandy.ajax_url,
								dataType: 'json',
								delay: 250,
								data: function (params) {
									return {
										q: params.term, // search term.
										page: params.page,
										action: 'wpsc_agent_autocomplete_admin_access',
										_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_agent_autocomplete_admin_access' ) ); ?>',
										isMultiple: 1, // to avoid none
										isAgentgroup: 0
									};
								},
								processResults: function (data, params) {
									var terms = [];
									if ( data ) {
										jQuery.each( data, function( id, text ) {
											terms.push( { id: text.id, text: text.title } );
										});
									}
									return {
										results: terms
									};
								},
								cache: true
							},
							escapeMarkup: function (markup) { return markup; }, // let our custom formatter work.
							minimumInputLength: 0,
							allowClear: false,
						});
					</script>
				</form>
				<div class="setting-footer-actions">
					<button 
						class="wpsc-button normal primary margin-right"
						onclick="wpsc_set_timer_setting(this);">
						<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?></button>
					<button 
						class="wpsc-button normal secondary"
						onclick="wpsc_reset_timer_setting(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_reset_timer_setting' ) ); ?>');">
						<?php echo esc_attr( wpsc__( 'Reset default', 'supportcandy' ) ); ?></button>
				</div>
			</div>
			<?php
			wp_die();
		}

		/**
		 * Save settings
		 *
		 * @return void
		 */
		public static function set_timer_setting() {

			if ( check_ajax_referer( 'wpsc_set_timer_setting', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$general = apply_filters(
				'wpsc_timer_settings',
				array(
					'auto-start' => isset( $_POST['auto-start'] ) ? intval( $_POST['auto-start'] ) : 0,
					'auto-stop'  => isset( $_POST['auto-stop'] ) ? intval( $_POST['auto-stop'] ) : 0,
					'agent_id'   => isset( $_POST['agent_id'] ) ? intval( $_POST['agent_id'] ) : 0,
				)
			);
			update_option( 'wpsc-timer-settings', $general );

			wp_die();
		}

		/**
		 * Reset timer settings to default
		 *
		 * @return void
		 */
		public static function reset_timer_setting() {

			if ( check_ajax_referer( 'wpsc_reset_timer_setting', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}
			self::reset();
			wp_die();
		}
	}
endif;

WPSC_Timer_Settings::init();
