<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Cron' ) ) :

	final class WPSC_EP_Cron {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// Add custom cron interval.
			add_filter( 'cron_schedules', array( __CLASS__, 'custom_interval' ) ); // phpcs:ignore WordPress.WP.CronInterval -- namespaces not yet fully supported

			// Schedule cron job.
			add_action( 'init', array( __CLASS__, 'schedule_event' ) );

			// pipe emails.
			add_action( 'wpsc_ep_import_emails', array( __CLASS__, 'import_emails' ) );
		}

		/**
		 * Custom cron job intervals for SupportCandy
		 *
		 * @param array $schedules - schedule names.
		 * @return array
		 */
		public static function custom_interval( $schedules ) {

			$general  = get_option( 'wpsc-ep-general-settings' );
			$interval = intval( $general['time-frequency'] ) * 60;

			$schedules['wpsc_ep'] = array(
				'interval' => $interval,
				'display'  => esc_attr__( 'Email Piping', 'wpsc_ep' ),
			);

			return $schedules;
		}

		/**
		 * Schedule cron job events for SupportCandy
		 *
		 * @return void
		 */
		public static function schedule_event() {

			if ( ! wp_next_scheduled( 'wpsc_ep_import_emails' ) ) {
				wp_schedule_event(
					time(),
					'wpsc_ep',
					'wpsc_ep_import_emails'
				);
			}
		}

		/**
		 * Remove existing scheduled event.
		 * Can be used while deactivation of plugin or resetting schedules after an update etc.
		 *
		 * @return void
		 */
		public static function unschedule_event() {

			// Remove every minute cron.
			$timestamp = wp_next_scheduled( 'wpsc_ep_import_emails' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'wpsc_ep_import_emails' );
			}
		}

		/**
		 * Import emails
		 *
		 * @return void
		 */
		public static function import_emails() {

			// Do not import while initialization process.
			if ( 'yes' === get_transient( 'wpsc_ep_connection_init' ) ) {
				return;
			}

			$general          = get_option( 'wpsc-ep-general-settings' );
			$connection_class = isset( WPSC_EP_Settings_General::$piping_connections[ $general['connection'] ] ) ? WPSC_EP_Settings_General::$piping_connections[ $general['connection'] ]['class'] : '';
			if ( $connection_class && class_exists( $connection_class ) ) {
				$connection_class::import();
			}
		}
	}
endif;

WPSC_EP_Cron::init();
