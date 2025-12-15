<?php // phpcs:ignore
/**
 * Plugin Name: SupportCandy - Timer
 * Plugin URI: https://supportcandy.net/
 * Description: Timer add-on for SupportCandy
 * Version: 3.1.6
 * Author: SupportCandy
 * Author URI: https://supportcandy.net/
 * Requires at least: 5.6
 * Tested up to: 6.8
 * Text Domain: wpsc-timer
 * Domain Path: /i18n
 */

if ( ! class_exists( 'PSM_Support_Candy' ) ) {
	return;
}

if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
	return;
}

// exit if core plugin is installing.
if ( defined( 'WPSC_INSTALLING' ) ) {
	return;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!.
}


if ( ! class_exists( 'WPSC_Timer' ) ) :

	final class WPSC_Timer {

		/**
		 * Versoin
		 *
		 * @var string
		 */
		public static $version = '3.1.6';

		/**
		 * Constructor for main class
		 */
		public static function init() {

			self::define_constants();
			self::load_files();

			// Return if installation is in progress.
			if ( defined( 'WPSC_DB_UPGRADING' ) || defined( 'WPSC_TIMER_INSTALLING' ) ) {
				return;
			}

			add_action( 'admin_init', array( __CLASS__, 'plugin_updator' ) );
		}

		/**
		 * Defines global constants that can be availabel anywhere in WordPress
		 *
		 * @return void
		 */
		public static function define_constants() {

			self::define( 'WPSC_TIMER_PLUGIN_FILE', __FILE__ );
			self::define( 'WPSC_TIMER_ABSPATH', __DIR__ . '/' );
			self::define( 'WPSC_TIMER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			self::define( 'WPSC_TIMER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			self::define( 'WPSC_TIMER_STORE_ID', 23906 );
			self::define( 'WPSC_TIMER_VERSION', self::$version );
		}

		/**
		 * Load all classes
		 *
		 * @return void
		 */
		private static function load_files() {

			// Load installation.
			include_once WPSC_TIMER_ABSPATH . 'class-wpsc-timer-installation.php';

			// Return if installation is in progress.
			if ( defined( 'WPSC_DB_UPGRADING' ) || defined( 'WPSC_TIMER_INSTALLING' ) ) {
				return;
			}

			// Load common classes.
			foreach ( glob( WPSC_TIMER_ABSPATH . 'includes/*.php' ) as $filename ) {
				include_once $filename;
			}
		}

		/**
		 * Define constants
		 *
		 * @param string $name - name of global constant.
		 * @param string $value - value of constant.
		 * @return void
		 */
		private static function define( $name, $value ) {

			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Plugin updator
		 *
		 * @return void
		 */
		public static function plugin_updator() {

			$licenses = get_option( 'wpsc-licenses', array() );
			$license  = isset( $licenses['timer'] ) ? $licenses['timer'] : array();
			if ( $license ) {
				$edd_updater = new WPSC_EDD_SL_Plugin_Updater(
					WPSC_STORE_URL,
					__FILE__,
					array(
						'version' => WPSC_TIMER_VERSION,
						'license' => $license['key'],
						'item_id' => WPSC_TIMER_STORE_ID,
						'author'  => 'Pradeep Makone',
						'url'     => home_url(),
					)
				);
			}
		}
	}
endif;

WPSC_Timer::init();
