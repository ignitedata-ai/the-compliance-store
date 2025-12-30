<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Settings' ) ) :

	final class WPSC_EP_Settings {

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
		 *
		 * @return void
		 */
		public static function init() {

			// add settings section.
			add_filter( 'wpsc_icons', array( __CLASS__, 'add_icons' ) );
			add_filter( 'wpsc_settings_page_sections', array( __CLASS__, 'add_settings_tab' ) );

			// Load tabs for this section.
			add_action( 'admin_init', array( __CLASS__, 'load_tabs' ) );

			// Add current tab to admin localization data.
			add_filter( 'wpsc_admin_localizations', array( __CLASS__, 'localizations' ) );

			// Load section tab layout.
			add_action( 'wp_ajax_wpsc_get_ep_settings', array( __CLASS__, 'get_ep_settings' ) );
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
		 * Settings tab
		 *
		 * @param array $sections - section name.
		 * @return array
		 */
		public static function add_settings_tab( $sections ) {

			$sections['email-piping'] = array(
				'slug'     => 'email_piping',
				'icon'     => 'envelope-open-text',
				'label'    => esc_attr__( 'Email Piping', 'wpsc-ep' ),
				'callback' => 'wpsc_get_ep_settings',
			);
			return $sections;
		}

		/**
		 * Load tabs for this section
		 */
		public static function load_tabs() {

			self::$tabs        = apply_filters(
				'wpsc_ep_tabs',
				array(
					'general'            => array(
						'slug'     => 'general',
						'label'    => esc_attr( wpsc__( 'General', 'supportcandy' ) ),
						'callback' => 'wpsc_ep_get_general_settings',
					),
					'pipe-rules'         => array(
						'slug'     => 'pipe_rules',
						'label'    => esc_attr__( 'Pipe Rules', 'wpsc-ep' ),
						'callback' => 'wpsc_ep_get_pipe_rules',
					),
					'imap'               => array(
						'slug'     => 'imap',
						'label'    => 'IMAP',
						'callback' => 'wpsc_ep_get_imap_settings',
					),
					'gmail'              => array(
						'slug'     => 'gmail',
						'label'    => 'Gmail',
						'callback' => 'wpsc_ep_get_gmail_settings',
					),
					'microsoft-exchange' => array(
						'slug'     => 'microsoft_exchange',
						'label'    => 'Microsoft Exchange',
						'callback' => 'wpsc_ep_get_me_settings',
					),
					'email-logs'         => array(
						'slug'     => 'email_logs',
						'label'    => 'Email Logs',
						'callback' => 'wpsc_ep_get_email_logs',
					),
				)
			);
			self::$current_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'general'; //phpcs:ignore
		}

		/**
		 * Add localizations to local JS
		 *
		 * @param array $localizations - localizations.
		 * @return array
		 */
		public static function localizations( $localizations ) {

			if ( ! ( WPSC_Settings::$is_current_page && WPSC_Settings::$current_section === 'email-piping' ) ) {
				return $localizations;
			}

			// Current section.
			$localizations['current_tab'] = self::$current_tab;

			return $localizations;
		}

		/**
		 * General setion body layout
		 *
		 * @return void
		 */
		public static function get_ep_settings() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}?>

			<div class="wpsc-setting-tab-container">
				<?php
				foreach ( self::$tabs as $key => $tab ) :
					$active = self::$current_tab === $key ? 'active' : ''
					?>
					<button 
						class="<?php echo esc_attr( $key ) . ' ' . esc_attr( $active ); ?>"
						onclick="<?php echo esc_attr( $tab['callback'] ) . '();'; ?>">
						<?php echo esc_attr( $tab['label'] ); ?>
						</button>
					<?php
				endforeach;
				?>
			</div>
			<div class="wpsc-setting-section-body"></div>
			<?php
			wp_die();
		}
	}
endif;

WPSC_EP_Settings::init();
