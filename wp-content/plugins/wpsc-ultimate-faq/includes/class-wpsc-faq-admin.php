<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_FAQ_Admin' ) ) :

	final class WPSC_FAQ_Admin {

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

			echo file_get_contents( WPSC_FAQ_ABSPATH . 'asset/js/admin.js' ) . PHP_EOL . PHP_EOL; // phpcs:ignore
		}
	}
endif;

WPSC_FAQ_Admin::init();
