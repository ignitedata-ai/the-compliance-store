<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!.
}

if ( ! class_exists( 'WPSC_EXPORT_Roles' ) ) :

	final class WPSC_EXPORT_Roles {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// User interface.
			add_action( 'wp_ajax_wpsc_get_roles_export_settings', array( __CLASS__, 'load_settings_ui' ) );
			add_action( 'wp_ajax_wpsc_set_roles_export_settings', array( __CLASS__, 'save_settings' ) );
			add_action( 'wp_ajax_wpsc_reset_roles_export_settings', array( __CLASS__, 'reset_settings' ) );

			// after new agent role added.
			add_action( 'wpsc_after_add_agent_role', array( __CLASS__, 'after_add_new_role' ) );

			// after delete agent role.
			add_action( 'wpsc_after_delete_agent_role', array( __CLASS__, 'after_delete_role' ) );
		}

		/**
		 * Reset settings
		 *
		 * @return void
		 */
		public static function reset() {

			$roles               = get_option( 'wpsc-agent-roles' );
			$allow_export_ticket = array( 'registered-user' );
			foreach ( $roles as $key => $role ) {
				$allow_export_ticket[] = $key;
			}

			$export_roles = apply_filters(
				'wpsc_export_roles',
				array(
					'allow-export-ticket' => $allow_export_ticket,
				)
			);
			update_option( 'wpsc-export-roles', $export_roles );
		}

		/**
		 * Settings user interface
		 *
		 * @return void
		 */
		public static function load_settings_ui() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$settings = get_option( 'wpsc-export-roles', array() );
			if ( ! isset( $settings['allow-export-ticket'] ) || ! is_array( $settings['allow-export-ticket'] ) ) {
				self::reset();
			}
			$roles      = get_option( 'wpsc-agent-roles' );
			$visibility = array(
				'registered-user' => esc_attr( wpsc__( 'Registered User', 'supportcandy' ) ),
				'guest'           => esc_attr( wpsc__( 'Guest User', 'supportcandy' ) ),
			);
			foreach ( $roles as $key => $role ) {
				$visibility[ $key ] = $role['label'];
			}
			?>

			<form action="#" onsubmit="return false;" class="wpsc-frm-et-roles">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Allow export ticket', 'wpsc-et' ); ?></label>
					</div>
					<select id="wpsc-allow-export-ticket"  multiple name="allow-export-ticket[]">
					<?php
					foreach ( $visibility as $key => $role ) {
						$selected = in_array( $key, $settings['allow-export-ticket'] ) ? 'selected' : ''
						?>
							<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $role ); ?></option>
						<?php
					}
					?>

					</select>
					<script>jQuery('#wpsc-allow-export-ticket').selectWoo();</script>
				</div>
				<?php do_action( 'wpsc_et_roles' ); ?>
				<input type="hidden" name="action" value="wpsc_set_roles_export_settings">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_roles_export_settings' ) ); ?>">
			</form>
			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_set_roles_export_settings(this);">
					<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_reset_roles_export_settings(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_reset_roles_export_settings' ) ); ?>');">
					<?php echo esc_attr( wpsc__( 'Reset default', 'supportcandy' ) ); ?></button>
			</div>
			<?php
			wp_die();
		}

		/**
		 * Save settings
		 *
		 * @return void
		 */
		public static function save_settings() {

			if ( check_ajax_referer( 'wpsc_set_roles_export_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$general = apply_filters(
				'wpsc_set_export_roles',
				array(
					'allow-export-ticket' => isset( $_POST['allow-export-ticket'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['allow-export-ticket'] ) ) ) : array(),
				)
			);
			update_option( 'wpsc-export-roles', $general );
			wp_die();
		}

		/**
		 * Reset settings to default
		 *
		 * @return void
		 */
		public static function reset_settings() {

			if ( check_ajax_referer( 'wpsc_reset_roles_export_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}
			self::reset();
			wp_die();
		}

		/**
		 * After new agent role added add that role in allow to export ticket
		 *
		 * @param integer $role_id - role id.
		 * @return void
		 */
		public static function after_add_new_role( $role_id ) {

			$allow_expert = get_option( 'wpsc-export-roles' );

			$allow_expert['allow-export-ticket'][] = $role_id;
			update_option( 'wpsc-export-roles', $allow_expert );
		}

		/**
		 * After agent role deleted remove that role in allow export ticket
		 *
		 * @param integer $role_id - role id.
		 * @return void
		 */
		public static function after_delete_role( $role_id ) {

			$allow_expert = get_option( 'wpsc-export-roles' );

			if ( in_array( $role_id, $allow_expert['allow-export-ticket'] ) ) {
				unset( $allow_expert['allow-export-ticket'][ $role_id ] );
			}
			update_option( 'wpsc-export-roles', $allow_expert );
		}
	}
endif;

WPSC_EXPORT_Roles::init();
