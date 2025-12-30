<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Installation' ) ) :

	final class WPSC_EP_Installation {

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

				define( 'WPSC_EP_INSTALLING', true );

				// Do not allow parallel process to run.
				if ( 'yes' === get_transient( 'wpsc_ep_installing' ) ) {
					return;
				}

				// Set transient.
				set_transient( 'wpsc_ep_installing', 'yes', MINUTE_IN_SECONDS * 10 );

				// Create or update database tables.
				self::create_db_tables();

				// Run installation.
				if ( self::$current_version == 0 ) {

					add_action( 'init', array( __CLASS__, 'initial_setup' ), 1 );
					add_action( 'init', array( __CLASS__, 'set_upgrade_complete' ), 1 );

				} else {

					add_action( 'init', array( __CLASS__, 'upgrade' ), 1 );
				}

				// Delete transient.
				delete_transient( 'wpsc_ep_installing' );
			}

			// activation functionality.
			register_activation_hook( WPSC_EP_PLUGIN_FILE, array( __CLASS__, 'activate' ) );

			// Deactivate functionality.
			register_deactivation_hook( WPSC_EP_PLUGIN_FILE, array( __CLASS__, 'deactivate' ) );
		}

		/**
		 * Check version
		 */
		public static function get_current_version() {

			self::$current_version = get_option( 'wpsc_ep_current_version', 0 );
		}

		/**
		 * Check for upgrade
		 */
		public static function check_upgrade() {

			if ( self::$current_version != WPSC_EP_VERSION ) {
				self::$is_upgrade = true;
			}
		}

		/**
		 * DB upgrade addon installer hook callback
		 *
		 * @return void
		 */
		public static function upgrade_install() {

			self::create_db_tables();
			self::initial_setup();
			self::set_upgrade_complete();
		}

		/**
		 * Create database tables
		 */
		public static function create_db_tables() {

			global $wpdb;

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$collate = '';
			if ( $wpdb->has_cap( 'collation' ) ) {
				$collate = $wpdb->get_charset_collate();
			}

			$tables = "
			CREATE TABLE {$wpdb->prefix}psmsc_ep_logger (
					id INT NOT NULL AUTO_INCREMENT,
					email_subject TEXT NOT NULL,
					email_to TINYTEXT NULL DEFAULT NULL,
					email_cc TEXT NULL DEFAULT NULL,
					email_from VARCHAR(100) NOT NULL,
					message_id TINYTEXT NULL,
					logs LONGTEXT NULL,
					status INT(1) NOT NULL DEFAULT '0',
					date_created DATETIME NOT NULL,
					PRIMARY KEY (id)
				) $collate;
			";

			dbDelta( $tables );
		}

		/**
		 * First time installation
		 */
		public static function initial_setup() {

			global $wpdb;

			$string_translations = get_option( 'wpsc-string-translation' );

			// add 'en_from' column to tickets table.
			$sql = "ALTER TABLE {$wpdb->prefix}psmsc_tickets ADD en_from TINYTEXT NULL";
			$wpdb->query( $sql );

			// general settings.
			update_option(
				'wpsc-ep-general-settings',
				array(
					'connection'               => 'imap',
					'reply-above-text'         => '-- Reply Above --',
					'allowed-emails'           => 'all',
					'allowed-users'            => 'anyone',
					'body-reference'           => 'text',
					'import-cc'                => 0,
					'time-frequency'           => 5,
					'block-emails'             => array(
						'/no(t)?(\\-|_)?reply@/i', // noreply.
						'/mail(er)?[\\-_]daemon@/i', // mailer daemon.
					),
					'block-subject'            => array(
						'/^[\\[\\(]?Auto(mat(ic|ed))?[\\s\\-]?reply/i', // automatic reply.
						'/^Out of Office/i', // out of office.
						'/Delivery Status Notification \\(Failure\\)/i', // delevery failure.
						'/Returned mail\\: see transcript for details/i', // delevery failure.
						'DELIVERY FAILURE', // delevery failure.
						'Undelivered Mail Returned to Sender', // delevery failure.
					),
					'forwarding-addresses'     => array(),
					'forwarding-as-from-email' => 0,
					'delete-email-logs-after'  => 30,
					'spam-filter'              => 1,
				)
			);

			// imap piping settings.
			update_option(
				'wpsc-ep-imap-settings',
				array(
					'email-address'        => '',
					'password'             => '',
					'encryption'           => 'ssl',
					'incoming-mail-server' => '',
					'port'                 => '',
					'is_active'            => 0,
					'last-error'           => '',
				)
			);

			// gmail piping settings.
			update_option(
				'wpsc-ep-gmail-settings',
				array(
					'email-address' => '',
					'client-id'     => '',
					'client-secret' => '',
					'is-active'     => 0,
					'last-error'    => '',
					'refresh-token' => '',
					'history-id'    => '',
				)
			);

			// close ticket warning settings.
			$translation = '<p>Hello {{customer_name}},</p><p>The ticket #{{ticket_id}} is closed. Please create a new ticket on our website for further queries!</p>';
			update_option(
				'wpsc-close-ticket-page-settings',
				array(
					'close-ticket-html' => $translation,
					'editor'            => 'html',
					'enable'            => 1,
				)
			);
			$string_translations['wpsc-close-ticket-html'] = $translation;

			// new email warning.
			$translation = '<p>Hello there,</p><p>A new ticket can not be created via email!</p>';
			update_option(
				'wpsc-ep-new-email-warning',
				array(
					'new-email-warning-message' => $translation,
					'editor'                    => 'html',
					'enable'                    => 1,
				)
			);
			$string_translations['wpsc-new-email-warning-message'] = $translation;

			// reply email warning.
			$translation = '<p>Hello {{customer_name}},</p><p>Your reply to the ticket is not allowed via email.</p>';
			update_option(
				'wpsc-reply-mail-page-settings',
				array(
					'reply-email-warning-message' => $translation,
					'editor'                      => 'html',
					'enable'                      => 1,
				)
			);
			$string_translations['wpsc-reply-email-warning-message'] = $translation;

			// allowed user type warning.
			$translation = '<p>Hello there,</p><p>The ticket can not be created for non-registered users!</p>';
			update_option(
				'wpsc-ep-usertype-warning-email',
				array(
					'email-warning-message' => '<p>Hello there,</p><p>The ticket can not be created for non-registered users!</p>',
					'editor'                => 'html',
					'enable'                => 1,
				)
			);
			$string_translations['wpsc-ep-usertype-warning-message'] = $translation;

			// add 'email_message_id' column to tickets table.
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}psmsc_threads ADD message_id TINYTEXT NULL" );

			// add 'other_recipients' column to threads table.
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}psmsc_threads ADD other_recipients TEXT NULL DEFAULT NULL" );

			// update string translations.
			update_option( 'wpsc-string-translation', $string_translations );
		}

		/**
		 * Upgrade the version
		 */
		public static function upgrade() {

			global $wpdb;

			if ( version_compare( self::$current_version, '3.0.2', '<' ) ) {

				// repaire general settings.
				$general = get_option( 'wpsc-ep-general-settings', array() );
				$general['block-emails'] = isset( $general['block-emails'] ) ? array_unique( array_filter( array_map( 'trim', $general['block-emails'] ) ) ) : array();
				$general['block-subject'] = isset( $general['block-subject'] ) ? array_unique( array_filter( array_map( 'trim', $general['block-subject'] ) ) ) : array();
				$general['forwarding-addresses'] = isset( $general['forwarding-addresses'] ) ? array_unique( array_filter( array_map( 'trim', $general['forwarding-addresses'] ) ) ) : array();
				update_option( 'wpsc-ep-general-settings', $general );

				// repaire email piping rules.
				$rules = get_option( 'wpsc-ep-pipe-rules', array() );
				foreach ( $rules as $index => $rule ) {
					$rule['forwarding-address'] = isset( $rule['forwarding-address'] ) ? array_unique( array_filter( array_map( 'trim', $rule['forwarding-address'] ) ) ) : array();
					$rule['from-address'] = isset( $rule['from-address'] ) ? array_unique( array_filter( array_map( 'trim', $rule['from-address'] ) ) ) : array();
					$rule['has-words'] = isset( $rule['has-words'] ) ? array_unique( array_filter( array_map( 'trim', $rule['has-words'] ) ) ) : array();
					$add_recipients = isset( $rule['add_recipients'] ) ? array_unique( array_filter( array_map( 'trim', explode( '|', $rule['add_recipients'] ) ) ) ) : array();
					$rule['add_recipients'] = $add_recipients ? implode( '|', $add_recipients ) : '';
					$rules[ $index ] = $rule;
				}
				update_option( 'wpsc-ep-pipe-rules', $rules );
			}

			if ( version_compare( self::$current_version, '3.1.5', '<' ) ) {

				// add 'email_message_id' column to tickets table.
				$column_exists = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}psmsc_threads LIKE 'message_id'" );
				if ( empty( $column_exists ) ) {
					$wpdb->query( "ALTER TABLE {$wpdb->prefix}psmsc_threads ADD message_id TINYTEXT NULL" );
				}
			}

			if ( version_compare( self::$current_version, '3.1.9', '<' ) ) {

				// add 'other_recipients' column to threads table.
				$column_exists = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}psmsc_threads LIKE 'other_recipients'" );
				if ( empty( $column_exists ) ) {
					$wpdb->query( "ALTER TABLE {$wpdb->prefix}psmsc_threads ADD other_recipients VARCHAR(200) NULL DEFAULT NULL" );
				}
			}

			if ( version_compare( self::$current_version, '3.2.0', '<' ) ) {

				$general = get_option( 'wpsc-ep-general-settings' );
				$general['spam-filter'] = 1;
				$general['delete-email-logs-after'] = 30;
				update_option( 'wpsc-ep-general-settings', $general );
			}

			if ( version_compare( self::$current_version, '3.2.2', '<' ) ) {

				$column_exists = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}psmsc_threads LIKE 'other_recipients'" );
				if ( ! empty( $column_exists ) ) {
						$wpdb->query( "ALTER TABLE {$wpdb->prefix}psmsc_threads MODIFY other_recipients TEXT NULL DEFAULT NULL" );
				}
			}

			self::set_upgrade_complete();
		}

		/**
		 * Mark upgrade as complete
		 */
		public static function set_upgrade_complete() {

			update_option( 'wpsc_ep_current_version', WPSC_EP_VERSION );
			self::$current_version = WPSC_EP_VERSION;
			self::$is_upgrade      = false;
		}

		/**
		 * Actions to perform after plugin activated
		 *
		 * @return void
		 */
		public static function activate() {

			do_action( 'wpsc_ep_activate' );
		}

		/**
		 * Actions to perform after plugin deactivated
		 *
		 * @return void
		 */
		public static function deactivate() {

			WPSC_EP_Cron::unschedule_event();
			do_action( 'wpsc_ep_deactivate' );
		}
	}
endif;

WPSC_EP_Installation::init();
