<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Timer_Admin' ) ) :

	final class WPSC_Timer_Admin {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// load scripts & styles.
			add_action( 'wpsc_js_backend', array( __CLASS__, 'backend_scripts' ) );

			// Add message to localization data.
			add_filter( 'wpsc_admin_localizations', array( __CLASS__, 'localizations' ) );
			add_filter( 'wpsc_frontend_localizations', array( __CLASS__, 'localizations' ) );
		}

		/**
		 * Backend scripts
		 *
		 * @return void
		 */
		public static function backend_scripts() {

			echo file_get_contents( WPSC_TIMER_ABSPATH . 'assets/js/admin.js' ) . PHP_EOL . PHP_EOL; // phpcs:ignore
		}

		/**
		 * Add localizations to local JS
		 *
		 * @param array $localizations - localizations.
		 * @return array
		 */
		public static function localizations( $localizations ) {

			// localization string.
			$localizations['translations']['invalid_timer_format'] = esc_attr__( 'Please enter a valid time!', 'supportcandy' );
			return $localizations;
		}
	}
endif;

WPSC_Timer_Admin::init();
