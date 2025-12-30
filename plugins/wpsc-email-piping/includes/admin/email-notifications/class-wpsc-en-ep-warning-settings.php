<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EN_EP_Warning_Settings' ) ) :

	final class WPSC_EN_EP_Warning_Settings {

		/**
		 * Tabs for this section
		 *
		 * @var array
		 */
		private static $tabs;

		/**
		 * Current tab
		 *
		 * @var string
		 */
		public static $current_tab;

		/**
		 * Initialize this class
		 */
		public static function init() {

			// add settings section.
			add_filter( 'wpsc_icons', array( __CLASS__, 'add_icons' ) );
			add_filter( 'wpsc_email_notification_page_sections', array( __CLASS__, 'add_settings_tab' ) );

			// Load tabs for this section.
			add_action( 'admin_init', array( __CLASS__, 'load_tabs' ) );

			// Add current tab to admin localization data.
			add_filter( 'wpsc_admin_localizations', array( __CLASS__, 'localizations' ) );

			// Load section tab layout.
			add_action( 'wp_ajax_wpsc_ep_warning_settings', array( __CLASS__, 'ep_warning_settings' ) );
		}

		/**
		 * Add icons to library
		 *
		 * @param array $icons - icon name.
		 * @return array
		 */
		public static function add_icons( $icons ) {

			$icons['envelope-open-text'] = file_get_contents( WPSC_EP_ABSPATH . 'asset/icons/envelope-open-text-solid.svg' ); //phpcs:ignore
			return $icons;
		}

		/**
		 * Email piping tab
		 *
		 * @param array $sections - section name.
		 * @return array
		 */
		public static function add_settings_tab( $sections ) {

			$sections['email-warning'] = array(
				'slug'     => 'email_warning',
				'icon'     => 'envelope-open-text',
				'label'    => esc_attr__( 'Email Piping', 'wpsc-ep' ),
				'callback' => 'wpsc_ep_warning_settings',
			);
			return $sections;
		}

		/**
		 * Load tabs for this section
		 */
		public static function load_tabs() {

			self::$tabs        = apply_filters(
				'wpsc_ep_warning_tabs',
				array(
					'allow-user-type'           => array(
						'slug'     => 'allow_user_type',
						'label'    => esc_attr__( 'Allowed user type warning', 'wpsc-ep' ),
						'callback' => 'wpsc_allowed_usertype_warning',
					),
					'allow-new-email-warning'   => array(
						'slug'     => 'allow_new_email_warning',
						'label'    => esc_attr__( 'Allowed new email warning', 'wpsc-ep' ),
						'callback' => 'wpsc_allowed_new_email_warning',
					),
					'allow-reply-email-warning' => array(
						'slug'     => 'allow_reply_email_warning',
						'label'    => esc_attr__( 'Allowed reply email warning', 'wpsc-ep' ),
						'callback' => 'wpsc_allowed_reply_email_warning',
					),
					'close-ticket'              => array(
						'slug'     => 'close_ticket',
						'label'    => esc_attr__( 'Closed ticket warning', 'wpsc-ep' ),
						'callback' => 'wpsc_closed_ticket_warning',
					),
				)
			);
			self::$current_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'allow-user-type'; //phpcs:ignore
		}

		/**
		 * Add localizations to local JS
		 *
		 * @param array $localizations - localization.
		 * @return array
		 */
		public static function localizations( $localizations ) {

			if ( ! ( WPSC_EN_Settings::$is_current_page && WPSC_EN_Settings::$current_section === 'email-warning' ) ) {
				return $localizations;
			}

			// Current section.
			$localizations['current_tab'] = self::$current_tab;

			return $localizations;
		}

		/**
		 * Email piping warning setting
		 *
		 * @return void
		 */
		public static function ep_warning_settings() {
			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}?>

			<div class="wpsc-setting-tab-container">
				<?php
				foreach ( self::$tabs as $key => $tab ) {
					$active = self::$current_tab === $key ? 'active' : ''
					?>
					<button 
						class="<?php echo esc_attr( $key ) . ' ' . esc_attr( $active ); ?>"
						onclick="<?php echo esc_attr( $tab['callback'] ) . '();'; ?>">
						<?php echo esc_attr( $tab['label'] ); ?>
						</button>
					<?php
				}
				?>
			</div>
			<div class="wpsc-setting-section-body"></div>
			<?php
			wp_die();
		}
	}

endif;


WPSC_EN_EP_Warning_Settings::init();
