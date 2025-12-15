<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_AAR_Settings' ) ) :

	final class WPSC_AAR_Settings {

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
			add_filter( 'wpsc_settings_page_sections', array( __CLASS__, 'add_settings_tab' ) );

			// Load tabs for this section.
			add_action( 'admin_init', array( __CLASS__, 'load_tabs' ) );

			// Add current tab to admin localization data.
			add_filter( 'wpsc_admin_localizations', array( __CLASS__, 'localizations' ) );

			// Load section tab layout.
			add_action( 'wp_ajax_wpsc_get_aar_settings', array( __CLASS__, 'get_aar_settings' ) );
		}

		/**
		 * Settings tab
		 *
		 * @param array $sections - section name array.
		 * @return array
		 */
		public static function add_settings_tab( $sections ) {

			$sections['aar'] = array(
				'slug'     => 'aar',
				'icon'     => 'headset',
				'label'    => esc_attr__( 'Assign Agent Rules', 'wpsc-aar' ),
				'callback' => 'wpsc_get_aar_settings',
			);
			return $sections;
		}

		/**
		 * Load tabs for this section
		 *
		 * @return void
		 */
		public static function load_tabs() {

			self::$tabs        = apply_filters(
				'wpsc_aar_tabs',
				array(
					'general'   => array(
						'slug'     => 'general',
						'label'    => esc_attr( wpsc__( 'General', 'supportcandy' ) ),
						'callback' => 'wpsc_aar_get_general_settings',
					),
					'aar-rules' => array(
						'slug'     => 'aar-rules',
						'label'    => esc_attr__( 'Assign Rules', 'wpsc-aar' ),
						'callback' => 'wpsc_aar_get_rules',
					),
				)
			);
			self::$current_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'general'; // phpcs:ignore
		}

		/**
		 * Add localizations to local JS
		 *
		 * @param array $localizations - localization list.
		 * @return array
		 */
		public static function localizations( $localizations ) {

			if ( ! ( WPSC_Settings::$is_current_page && WPSC_Settings::$current_section === 'aar' ) ) {
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
		public static function get_aar_settings() {

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

WPSC_AAR_Settings::init();
