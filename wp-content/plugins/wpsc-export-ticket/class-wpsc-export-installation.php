<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EXPORT_Installation' ) ) :

	final class WPSC_EXPORT_Installation {

		/**
		 * Currently installed version
		 *
		 * @var integer
		 */
		public static $current_version;

		/**
		 * For checking whether upgrade available or not
		 *
		 * @var boolean
		 */
		public static $is_upgrade = false;

		/**
		 * Initialize installation
		 */
		public static function init() {

			self::get_current_version();
			self::check_upgrade();

			// db upgrade addon installer hook.
			add_action( 'wpsc_upgrade_install_addons', array( __CLASS__, 'upgrade_install' ) );

			// Database upgrade is in progress.
			if ( defined( 'WPSC_DB_UPGRADING' ) ) {
				return;
			}

			if ( self::$is_upgrade ) {

				define( 'WPSC_EXPORT_INSTALLING', true );

				// Do not allow parallel process to run.
				if ( 'yes' === get_transient( 'wpsc_export_installing' ) ) {
					return;
				}

				// Set transient.
				set_transient( 'wpsc_export_installing', 'yes', MINUTE_IN_SECONDS * 10 );

				// Run installation.
				if ( self::$current_version == 0 ) {

					add_action( 'init', array( __CLASS__, 'initial_setup' ), 1 );
					add_action( 'init', array( __CLASS__, 'set_upgrade_complete' ), 1 );

				} else {

					add_action( 'init', array( __CLASS__, 'upgrade' ), 1 );
				}

				// Delete transient.
				delete_transient( 'wpsc_export_installing' );
			}

			// activation functionality.
			register_activation_hook( WPSC_EXPORT_PLUGIN_FILE, array( __CLASS__, 'activate' ) );

			// Deactivate functionality.
			register_deactivation_hook( WPSC_EXPORT_PLUGIN_FILE, array( __CLASS__, 'deactivate' ) );
		}

		/**
		 * Check version
		 */
		public static function get_current_version() {

			self::$current_version = get_option( 'wpsc_export_current_version', 0 );
		}

		/**
		 * Check for upgrade
		 */
		public static function check_upgrade() {

			if ( self::$current_version != WPSC_EXPORT_VERSION ) {
				self::$is_upgrade = true;
			}
		}

		/**
		 * DB upgrade addon installer hook callback
		 *
		 * @return void
		 */
		public static function upgrade_install() {

			self::initial_setup();
			self::set_upgrade_complete();
		}

		/**
		 * First time installation
		 */
		public static function initial_setup() {

			update_option(
				'wpsc-export-roles',
				array(
					'allow-export-ticket' => array( 'registered-user', 1, 2 ),
				)
			);

			update_option(
				'wpsc-agent-export-settings',
				array(
					'id',
					'status',
					'name',
					'subject',
					'category',
					'priority',
					'assigned_agent',
					'date_updated',
					'date_created',
				)
			);

			update_option(
				'wpsc-register-export-settings',
				array(
					'id',
					'status',
					'subject',
					'category',
					'date_created',
					'date_updated',
				)
			);
		}

		/**
		 * Upgrade the version
		 */
		public static function upgrade() {

			self::set_upgrade_complete();
		}

		/**
		 * Mark upgrade as complete
		 */
		public static function set_upgrade_complete() {

			update_option( 'wpsc_export_current_version', WPSC_EXPORT_VERSION );
			self::$current_version = WPSC_EXPORT_VERSION;
			self::$is_upgrade      = false;
		}

		/**
		 * Actions to perform after plugin activated
		 *
		 * @return void
		 */
		public static function activate() {

			do_action( 'wpsc_export_activate' );
		}

		/**
		 * Actions to perform after plugin deactivated
		 *
		 * @return void
		 */
		public static function deactivate() {

			do_action( 'wpsc_export_deactivate' );
		}
	}
endif;

WPSC_EXPORT_Installation::init();
