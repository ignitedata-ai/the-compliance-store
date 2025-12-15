<?php // phpcs:ignore
/**
 * Plugin Name: SupportCandy - Email Piping
 * Plugin URI: https://supportcandy.net/
 * Description: Email Piping add-on for SupportCandy
 * Version: 3.2.7
 * Author: SupportCandy
 * Author URI: https://supportcandy.net/
 * Requires at least: 5.6
 * Tested up to: 6.7
 * Text Domain: wpsc-ep
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
	exit; // Exit if accessed directly!
}


if ( ! class_exists( 'WPSC_EP' ) ) :

	final class WPSC_EP {

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public static $version = '3.2.7';

		/**
		 * Constructor for main class
		 */
		public static function init() {

			self::define_constants();
			self::load_files();

			// Return if installation is in progress.
			if ( defined( 'WPSC_DB_UPGRADING' ) || defined( 'WPSC_EP_INSTALLING' ) ) {
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

			self::define( 'WPSC_EP_PLUGIN_FILE', __FILE__ );
			self::define( 'WPSC_EP_ABSPATH', __DIR__ . '/' );
			self::define( 'WPSC_EP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			self::define( 'WPSC_EP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			self::define( 'WPSC_EP_STORE_ID', 117 );
			self::define( 'WPSC_EP_VERSION', self::$version );
		}

		/**
		 * Load all classes
		 *
		 * @return void
		 */
		private static function load_files() {

			// Load installation.
			include_once WPSC_EP_ABSPATH . 'class-wpsc-ep-installation.php';

			// Return if installation is in progress.
			if ( defined( 'WPSC_DB_UPGRADING' ) || defined( 'WPSC_EP_INSTALLING' ) ) {
				return;
			}

			// Load php-imap library.
			if ( ! class_exists( 'PhpImap\Mailbox' ) ) {
				WPSC_Functions::load_library( WPSC_EP_ABSPATH . 'asset/lib/php-imap' );
			}

			// Load common classes.
			foreach ( glob( WPSC_EP_ABSPATH . 'includes/*.php' ) as $filename ) {
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
			$license  = isset( $licenses['email-piping'] ) ? $licenses['email-piping'] : array();
			if ( $license ) {
				$edd_updater = new WPSC_EDD_SL_Plugin_Updater(
					WPSC_STORE_URL,
					__FILE__,
					array(
						'version' => WPSC_EP_VERSION,
						'license' => $license['key'],
						'item_id' => WPSC_EP_STORE_ID,
						'author'  => 'Pradeep Makone',
						'url'     => home_url(),
					)
				);
			}
		}
	}
endif;

WPSC_EP::init();
