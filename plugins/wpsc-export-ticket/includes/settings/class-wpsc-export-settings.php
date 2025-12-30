<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EXPORT_Settings' ) ) :

	final class WPSC_EXPORT_Settings {

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

			// Load tabs for this section.
			add_action( 'admin_init', array( __CLASS__, 'load_tabs' ) );

			// Add current tab to admin localization data.
			add_filter( 'wpsc_admin_localizations', array( __CLASS__, 'localizations' ) );

			// export icon.
			add_filter( 'wpsc_icons', array( __CLASS__, 'export_icons' ) );

			// export tab in settings.
			add_filter( 'wpsc_settings_page_sections', array( __CLASS__, 'export_setting_tab' ) );

			// Load section tab layout.
			add_action( 'wp_ajax_wpsc_get_export_settings', array( __CLASS__, 'get_export_settings' ) );

			// load custom field module.
			add_filter( 'wpsc_cf_allowed_modules', array( __CLASS__, 'load_cf_module' ) );
		}

		/**
		 * Load tabs for this section
		 */
		public static function load_tabs() {

			self::$tabs        = apply_filters(
				'wpsc_export_tabs',
				array(
					'roles'           => array(
						'slug'     => 'roles',
						'label'    => esc_attr( wpsc__( 'General', 'supportcandy' ) ),
						'callback' => 'wpsc_get_roles_export_settings',
					),
					'agent-export'    => array(
						'slug'     => 'agent_export',
						'label'    => esc_attr( wpsc__( 'Agent view', 'supportcandy' ) ),
						'callback' => 'wpsc_get_agent_export_settings',
					),
					'register-export' => array(
						'slug'     => 'register_export',
						'label'    => esc_attr( wpsc__( 'Customer view', 'supportcandy' ) ),
						'callback' => 'wpsc_get_register_export_settings',
					),
				)
			);
			self::$current_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'roles'; // phpcs:ignore
		}

		/**
		 * Add localizations to local JS
		 *
		 * @param array $localizations - localizations.
		 * @return array
		 */
		public static function localizations( $localizations ) {

			if ( ! ( WPSC_Settings::$is_current_page && WPSC_Settings::$current_section === 'export-settings' ) ) {
				return $localizations;
			}

			// Current section.
			$localizations['current_tab'] = self::$current_tab;

			return $localizations;
		}

		/**
		 * Load allowed modules for custom field type
		 *
		 * @param array $modules - modules.
		 * @return array
		 */
		public static function load_cf_module( $modules ) {

			$modules['export-tickets'] = array( 'ticket', 'customer', 'agentonly' );
			return $modules;
		}

		/**
		 * Add Export plugin icon
		 *
		 * @param array $icons - icon array.
		 * @return array
		 */
		public static function export_icons( $icons ) {

			$icons['file-export'] = file_get_contents( WPSC_EXPORT_ABSPATH . 'asset/icons/file-export-solid.svg' ); //phpcs:ignore
			return $icons;
		}

		/**
		 * Export Settings tab
		 *
		 * @param array $sections - section array.
		 * @return array
		 */
		public static function export_setting_tab( $sections ) {

			$sections['export-settings'] = array(
				'slug'     => 'export_settings',
				'icon'     => 'file-export',
				'label'    => esc_attr__( 'Export Tickets', 'wpsc-et' ),
				'callback' => 'wpsc_get_export_settings',
			);
			return $sections;
		}

		/**
		 * General setion body layout
		 *
		 * @return void
		 */
		public static function get_export_settings() {

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

WPSC_EXPORT_Settings::init();
