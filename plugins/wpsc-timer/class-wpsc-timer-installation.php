<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Timer_Installation' ) ) :

	final class WPSC_Timer_Installation {

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

				define( 'WPSC_TIMER_INSTALLING', true );

				// Do not allow parallel process to run.
				if ( 'yes' === get_transient( 'wpsc_timer_installing' ) ) {
					return;
				}

				// Set transient.
				set_transient( 'wpsc_timer_installing', 'yes', MINUTE_IN_SECONDS * 10 );

				// Create database tables.
				self::create_db_tables();

				// Run installation.
				if ( self::$current_version == 0 ) {

					add_action( 'init', array( __CLASS__, 'initial_setup' ), 1 );
					add_action( 'init', array( __CLASS__, 'set_upgrade_complete' ), 1 );

				} else {

					add_action( 'init', array( __CLASS__, 'upgrade' ), 1 );
				}

				// Delete transient.
				delete_transient( 'wpsc_timer_installing' );
			}

			// activation functionality.
			register_activation_hook( WPSC_TIMER_PLUGIN_FILE, array( __CLASS__, 'activate' ) );

			// Deactivate functionality.
			register_deactivation_hook( WPSC_TIMER_PLUGIN_FILE, array( __CLASS__, 'deactivate' ) );
		}

		/**
		 * Check version
		 */
		public static function get_current_version() {

			self::$current_version = get_option( 'wpsc_tr_current_version', 0 );
		}

		/**
		 * Check for upgrade
		 */
		public static function check_upgrade() {

			if ( self::$current_version != WPSC_TIMER_VERSION ) {
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
				CREATE TABLE {$wpdb->prefix}psmsc_timer_logs (
					id BIGINT NOT NULL AUTO_INCREMENT,
					ticket BIGINT NOT NULL,
					author BIGINT NOT NULL,
					log_by BIGINT NOT NULL,
					date_started datetime NOT NULL,
					time_spent VARCHAR(200) NOT NULL DEFAULT 'PT0M',
					temp_start datetime NULL DEFAULT NULL,
					description LONGTEXT NULL DEFAULT NULL,
					status VARCHAR(50) NOT NULL DEFAULT 'stopped',
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

			// custom field type.
			$name = esc_attr__( 'Time Spent', 'wpsc-timer' );
			$wpdb->insert(
				$wpdb->prefix . 'psmsc_custom_fields',
				array(
					'name'  => $name,
					'slug'  => 'time_spent',
					'field' => 'ticket',
					'type'  => 'df_time_spent',
				)
			);
			$string_translations[ 'wpsc-cf-name-' . $wpdb->insert_id ] = $name;

			// ticket colomn.
			$sql = "ALTER TABLE {$wpdb->prefix}psmsc_tickets ADD time_spent VARCHAR(50) NOT NULL DEFAULT 'PT0M'";
			$wpdb->query( $sql );

			// agent role permission.
			$roles = get_option( 'wpsc-agent-roles', array() );
			foreach ( $roles as $index => $role ) {
				$roles[ $index ]['caps']['modify-timer-log'] = $index == 1 ? true : false;
			}
			update_option( 'wpsc-agent-roles', $roles );

			// settings.
			update_option(
				'wpsc-timer-settings',
				array(
					'auto-start' => 0,
					'auto-stop'  => 0,
					'agent_id'   => 1,
				)
			);

			// update string translations.
			update_option( 'wpsc-string-translation', $string_translations );

			// install widget.
			self::install_widget();

			// dashboard card / widget.
			self::install_db_cards_widgets();
		}

		/**
		 * Upgrade the version
		 */
		public static function upgrade() {

			if ( version_compare( self::$current_version, '3.1.2', '<' ) ) {

				// dashboard card / widget.
				self::install_db_cards_widgets();
			}
			self::set_upgrade_complete();
		}

		/**
		 * Mark upgrade as complete
		 */
		public static function set_upgrade_complete() {

			update_option( 'wpsc_tr_current_version', WPSC_TIMER_VERSION );
			self::$current_version = WPSC_TIMER_VERSION;
			self::$is_upgrade      = false;
		}

		/**
		 * Actions to perform after plugin activated
		 *
		 * @return void
		 */
		public static function activate() {

			// Widget might not be installed as a result of race condition while upgrade.
			// There is an option for administrator to deactivate and then activate the plugin.
			self::install_widget();
			self::install_db_cards_widgets();
			do_action( 'wpsc_timer_activate' );
		}

		/**
		 * Actions to perform after plugin deactivated
		 * zzz
		 *
		 * @return void
		 */
		public static function deactivate() {

			do_action( 'wpsc_timer_deactivate' );
		}

		/**
		 * Install widget if not already installed
		 *
		 * @return void
		 */
		public static function install_widget() {

			$widgets = get_option( 'wpsc-ticket-widget', array() );
			if ( ! isset( $widgets['timer'] ) ) {

				$agent_roles = array_keys( get_option( 'wpsc-agent-roles', array() ) );
				$label = esc_attr__( 'Timer', 'wpsc-timer' );
				$widgets['timer'] = array(
					'title'               => $label,
					'is_enable'           => 1,
					'allow-customer'      => 0,
					'allowed-agent-roles' => $agent_roles,
					'callback'            => 'wpsc_get_tw_timer()',
					'class'               => 'WPSC_TW_Timer',
				);
				update_option( 'wpsc-ticket-widget', $widgets );

				// string translations.
				$string_translations = get_option( 'wpsc-string-translation' );
				$string_translations['wpsc-twt-timer'] = $label;
				update_option( 'wpsc-string-translation', $string_translations );
			}
		}

		/**
		 * Install database cards or widget if not already installed
		 *
		 * @return void
		 */
		public static function install_db_cards_widgets() {

			// Timer dashboard widget.
			$widgets = get_option( 'wpsc-dashboard-widgets', array() );
			$string_translations = get_option( 'wpsc-string-translation', array() );

			if ( ! isset( $widgets['active-timer'] ) ) {

				$label = esc_attr__( 'Active Timer', 'supportcandy' );
				$string_translations['wpsc-dashboard-widget-active-timer'] = $label;
				$widgets['active-timer'] = array(
					'title'               => $label,
					'is_enable'           => 1,
					'allowed-agent-roles' => array( 1, 2 ),
					'callback'            => 'wpsc_dbw_active_timer()',
					'class'               => 'WPSC_DBW_Active_Timer',
					'type'                => 'default',
					'chart-type'          => '',
				);
			}

			if ( ! isset( $widgets['agents-active-timer'] ) ) {

				$label = esc_attr__( 'Agents Active Timer', 'supportcandy' );
				$string_translations['wpsc-dashboard-widget-agents-active-timer'] = $label;
				$widgets['agents-active-timer'] = array(
					'title'               => $label,
					'is_enable'           => 1,
					'allowed-agent-roles' => array( 1 ),
					'callback'            => 'wpsc_dbw_agents_active_timer()',
					'class'               => 'WPSC_DBW_Agents_Active_Timer',
					'type'                => 'default',
					'chart-type'          => '',
				);
			}

			update_option( 'wpsc-dashboard-widgets', $widgets );
			update_option( 'wpsc-string-translation', $string_translations );
		}
	}
endif;

WPSC_Timer_Installation::init();
