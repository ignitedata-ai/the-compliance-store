<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EXPORT_Admin' ) ) :

	final class WPSC_EXPORT_Admin {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			add_action( 'wpsc_js_backend', array( __CLASS__, 'backend_scripts' ) );
		}

		/**
		 * Backend scripts
		 *
		 * @return void
		 */
		public static function backend_scripts() {

			echo file_get_contents( WPSC_EXPORT_ABSPATH . 'asset/js/admin.js' ) . PHP_EOL . PHP_EOL; // phpcs:ignore
		}
	}
endif;

WPSC_EXPORT_Admin::init();
