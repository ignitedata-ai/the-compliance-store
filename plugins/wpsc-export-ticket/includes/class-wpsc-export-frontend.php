<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EXPORT_Frontend' ) ) :

	final class WPSC_EXPORT_Frontend {

		/**
		 * Set whether tickets to be queried is active or deleted
		 *
		 * @var integer
		 */
		private static $is_active = 1;

		/**
		 * Ignore custom field types.
		 *
		 * @var array
		 */
		public static $ignore_cft = array();

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// ignore custom field types.
			add_action( 'init', array( __CLASS__, 'set_ignore_cft_list' ) );

			// scripts and styles.
			add_action( 'wpsc_js_frontend', array( __CLASS__, 'frontend_scripts' ) );
			add_action( 'wpsc_css_frontend', array( __CLASS__, 'frontend_styles' ) );

			// export button.
			add_filter( 'wpsc_admin_tl_more_actions', array( __CLASS__, 'export_button' ) );

			// export tickets.
			add_action( 'wp_ajax_wpsc_get_export_tickets', array( __CLASS__, 'export_tickets' ) );
			add_action( 'wp_ajax_nopriv_wpsc_get_export_tickets', array( __CLASS__, 'export_tickets' ) );

			// delete export file from upload directory.
			add_action( 'wpsc_cron_daily', array( __CLASS__, 'delete_export_files' ) );

			add_action( 'init', array( __CLASS__, 'download_export_file' ) );
		}

		/**
		 * Set ignore custom field types for assign agent rules
		 *
		 * @return void
		 */
		public static function set_ignore_cft_list() {

			self::$ignore_cft = apply_filters(
				'wpsc_export_ticket_ignore_cft',
				array(
					'cf_html',
				)
			);
		}

		/**
		 * Frontend scripts
		 *
		 * @return void
		 */
		public static function frontend_scripts() {

			echo file_get_contents( WPSC_EXPORT_ABSPATH . 'asset/js/public.js' ) . PHP_EOL . PHP_EOL; // phpcs:ignore
		}

		/**
		 * Frontend styles
		 *
		 * @return void
		 */
		public static function frontend_styles() {

			if ( is_rtl() ) {
				echo file_get_contents( WPSC_EXPORT_ABSPATH . 'asset/css/public-rtl.css' ) . PHP_EOL . PHP_EOL; // phpcs:ignore
			} else {
				echo file_get_contents( WPSC_EXPORT_ABSPATH . 'asset/css/public.css' ) . PHP_EOL . PHP_EOL; // phpcs:ignore
			}
		}

		/**
		 * Export button
		 *
		 * @param array $actions - action array.
		 * @return array
		 */
		public static function export_button( $actions ) {

			$current_user = WPSC_Current_User::$current_user;
			$et_settings  = get_option( 'wpsc-export-roles' );

			if ( isset( $et_settings['allow-export-ticket'] ) && is_array( $et_settings['allow-export-ticket'] ) &&
				( ( ! $current_user->user->ID && in_array( 'guest', $et_settings['allow-export-ticket'] ) ) ||
				( $current_user->is_agent && in_array( $current_user->agent->role, $et_settings['allow-export-ticket'] ) ) ||
				( ! $current_user->is_agent && $current_user->user->ID && in_array( 'registered-user', $et_settings['allow-export-ticket'] ) ) )
			) :

				$actions['export'] = array(
					'icon'     => 'file-export',
					'label'    => esc_attr__( 'Export', 'wpsc-et' ),
					'callback' => 'wpsc_get_export_tickets',
				);
			endif;
			return $actions;
		}

		/**
		 * Export tickets depends on current filter
		 *
		 * @return void
		 */
		public static function export_tickets() {

			if ( check_ajax_referer( 'wpsc_get_export_tickets', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			$current_user = WPSC_Current_User::$current_user;
			$et_settings  = get_option( 'wpsc-export-roles' );

			if ( ! (
				( ! $current_user->user->ID && in_array( 'guest', $et_settings['allow-export-ticket'] ) ) ||
				( $current_user->is_agent && in_array( $current_user->agent->role, $et_settings['allow-export-ticket'] ) ) ||
				( ! $current_user->is_agent && $current_user->user->ID && in_array( 'registered-user', $et_settings['allow-export-ticket'] ) )
			) ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$unique_id      = wp_rand( 111111111, 999999999 );
			$file_name      = $unique_id . '.csv';
			$path_to_export = get_temp_dir() . $file_name;

			$separator = apply_filters( 'wpsc_export_csv_separator', ',' );

			$url_to_export = add_query_arg(
				array(
					'download-wpsc-export' => $unique_id,
					'_ajax_nonce'          => wp_create_nonce( 'download-wpsc-export' ),
				),
				get_home_url()
			);

			$fp = fopen( $path_to_export, 'w' ); // phpcs:ignore

			// Added below output BOM (Byte Order Mark). Needed for chinese double byte characters.
			fwrite($fp, "\xEF\xBB\xBF");// phpcs:ignore

			$export_items = $current_user->is_agent ? get_option( 'wpsc-agent-export-settings' ) : get_option( 'wpsc-register-export-settings' );

			$column_name = array();
			foreach ( $export_items as $slug ) :
				$c_field = WPSC_Custom_Field::get_cf_by_slug( $slug );
				if ( $c_field ) {
					$column_name[] = $c_field->name;
				}
			endforeach;
			$column_name = apply_filters( 'wpsc_add_export_column', $column_name );
			fputcsv( $fp, $column_name, $separator );

			$filters = isset( $_COOKIE['wpsc-tl-filters'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['wpsc-tl-filters'] ) ) : '';
			$filters = $filters ? json_decode( $filters, true ) : array();

			$default_filters = $current_user->is_agent ? get_option( 'wpsc-atl-default-filters' ) : get_option( 'wpsc-ctl-default-filters' );
			$saved_filters   = $current_user->get_saved_filters();

			$default_flag = preg_match( '/default-(\d*)$/', $filters['filterSlug'], $default_matches );
			if ( $default_flag ) {
				$filter_id = $default_matches[1];
			}

			$saved_flag = preg_match( '/saved-(\d*)$/', $filters['filterSlug'], $saved_matches );
			if ( $saved_flag ) {
				$filter_id = $saved_matches[1];
			}

			// Order by.
			if ( ! isset( $filters['orderby'] ) && ! $default_flag && isset( $default_filters[ $filters['filterSlug'] ] ) ) {
				$filters['orderby'] = $more_settings['default-sort-by'];
			}
			if ( ! isset( $filters['orderby'] ) && $default_flag ) {
				$filters['orderby'] = $default_filters[ $filter_id ]['sort-by'];
			}
			if ( ! isset( $filters['orderby'] ) && $saved_flag ) {
				$filters['orderby'] = $saved_filters[ $filter_id ]['sort-by'];
			}

			// Order.
			if ( ! isset( $filters['order'] ) && ! $default_flag && isset( $default_filters[ $filters['filterSlug'] ] ) ) {
				$filters['order'] = $more_settings['default-sort-order'];
			}
			if ( ! isset( $filters['order'] ) && $default_flag ) {
				$filters['order'] = $default_filters[ $filter_id ]['sort-order'];
			}
			if ( ! isset( $filters['order'] ) && $saved_flag ) {
				$filters['order'] = $saved_filters[ $filter_id ]['sort-order'];
			}

			// Search.
			$filters['search'] = isset( $filters['search'] ) ? sanitize_text_field( $filters['search'] ) : '';

			// total items.
			$filters['items_per_page'] = 0;

			// System query.
			$filters['system_query'] = $current_user->get_tl_system_query( $filters );

			// Meta query.
			$meta_query = array( 'relation' => 'AND' );

			if (
				isset( $default_filters[ $filters['filterSlug'] ] ) ||
				( $default_flag && isset( $default_filters[ $filter_id ] ) ) ||
				( $saved_flag && isset( $saved_filters[ $filter_id ] ) ) ||
				$filters['filterSlug'] == 'custom'
			) {

				$slug = $default_flag || $saved_flag ? $filter_id : '';
				if ( ! $slug ) {
					$slug = $filters['filterSlug'];
				}

				$parent_slug = is_numeric( $slug ) && $default_flag ? $default_filters[ $slug ]['parent-filter'] : '';
				if ( ! $parent_slug && is_numeric( $slug ) && $saved_flag ) {
					$parent_slug = $saved_filters[ $slug ]['parent-filter'];
				}
				if ( ! $parent_slug && $slug == 'custom' ) {
					$parent_slug = $filters['parent-filter'];
				}
				if ( ! $parent_slug ) {
					$parent_slug = $slug;
				}

				// Get parent meta queries.
				$meta_query = array_merge( $meta_query, self::get_parent_meta_query( $parent_slug ) );

				if ( $default_flag ) {
					$meta_query = array_merge( $meta_query, WPSC_Ticket_Conditions::get_meta_query( $default_filters[ $slug ]['filters'] ) );
				}

				if ( $saved_flag ) {
					$filters_str = $saved_filters[ $slug ]['filters'];
					$meta_query = array_merge( $meta_query, WPSC_Ticket_Conditions::get_meta_query( $filters_str ) );
				}

				if ( $filters['filterSlug'] == 'custom' ) {
					$meta_query = array_merge( $meta_query, WPSC_Ticket_Conditions::get_meta_query( $filters['filters'] ) );
				}
			}

			$filters['meta_query'] = $meta_query;
			$filters['is_active']  = self::$is_active;

			$tickets = WPSC_Ticket::find( $filters )['results'];
			if ( $tickets ) {

				foreach ( $tickets as $ticket ) {

					$column_value = array();

					foreach ( $export_items as $slug ) {

						$cf = WPSC_Custom_Field::get_cf_by_slug( $slug );

						if ( ! $cf || in_array( $cf->type::$slug, self::$ignore_cft ) ) {
							continue;
						}

						$column_value[] = in_array( $cf->field, array( 'ticket', 'agentonly' ) ) ? $cf->type::get_ticket_field_val( $cf, $ticket ) : $cf->type::get_customer_field_val( $cf, $ticket->customer );
					}
					$column_value = apply_filters( 'wpsc_add_export_column_value', $column_value, $ticket );
					fputcsv( $fp, $column_value, $separator );
				}
			}

			fclose( $fp ); // phpcs:ignore

			echo '{"url_to_export":"' . esc_url_raw( $url_to_export ) . '"}';

			wp_die();
		}

		/**
		 * Get parent meta query
		 *
		 * @param string $parent_slug - parent slug string.
		 * @return array
		 */
		public static function get_parent_meta_query( $parent_slug ) {

			$current_user    = WPSC_Current_User::$current_user;
			$more_settings   = $current_user->is_agent ? get_option( 'wpsc-tl-ms-agent-view' ) : get_option( 'wpsc-tl-ms-customer-view' );
			$default_filters = $current_user->is_agent ? get_option( 'wpsc-atl-default-filters' ) : get_option( 'wpsc-ctl-default-filters' );
			$meta_query      = array();
			switch ( $parent_slug ) {

				case 'all':
					break;

				case 'unresolved':
					$meta_query[] = array(
						'slug'    => 'status',
						'compare' => 'IN',
						'val'     => $more_settings['unresolved-ticket-statuses'],
					);
					break;

				case 'unassigned':
					$meta_query[] = array(
						'slug'    => 'assigned_agent',
						'compare' => '=',
						'val'     => '',
					);
					break;

				case 'mine':
					$meta_query[] = array(
						'slug'    => 'assigned_agent',
						'compare' => '=',
						'val'     => $current_user->agent->id,
					);
					$meta_query[] = array(
						'slug'    => 'status',
						'compare' => 'IN',
						'val'     => $more_settings['unresolved-ticket-statuses'],
					);
					break;

				case 'closed':
					$ms_advanced     = get_option( 'wpsc-tl-ms-advanced' );
					$gs              = get_option( 'wpsc-gs-general' );
					$closed_statuses = array( $gs['close-ticket-status'] );
					$closed_statuses = array_merge( $closed_statuses, $ms_advanced['closed-ticket-statuses'] );
					$closed_statuses = array_unique( $closed_statuses );
					$meta_query[]    = array(
						'slug'    => 'status',
						'compare' => 'IN',
						'val'     => $closed_statuses,
					);
					break;

				case 'deleted':
					if ( self::$is_active !== 0 ) {
						self::$is_active = 0;
					}
					break;

				default:
					// Break if not exists.
					if ( ! is_numeric( $parent_slug ) || ! isset( $default_filters[ $parent_slug ] ) ) {
						break;
					}

					$filters_json = $default_filters[ $parent_slug ]['filters'];
					$parent_filter  = $default_filters[ $parent_slug ]['parent-filter'];
					$meta_query   = array_merge( $meta_query, self::get_parent_meta_query( $parent_filter ) );
					$meta_query   = array_merge( $meta_query, WPSC_Ticket_Conditions::get_meta_query( $filters_json ) );
			}

			return $meta_query;
		}

		/**
		 * Delete export file from upload directory
		 *
		 * @return void
		 */
		public static function delete_export_files() {

			$upload_dir     = wp_upload_dir();
			$export_file    = time() . '.csv';
			$path_to_export = $upload_dir['basedir'] . '/wpsc/export';

			// Load files.
			foreach ( glob( $path_to_export . '/*.csv' ) as $filename ) {
				$time = (int) filter_var( $filename, FILTER_SANITIZE_NUMBER_INT );
				$d    = ( new DateTime() )->sub( new DateInterval( 'P1D' ) );
				$date = new DateTime();
				$date->setTimestamp( abs( $time ) );

				if ( $date->format( 'Y-m-d H:i:s' ) < $d->format( 'Y-m-d H:i:s' ) ) {
					wp_delete_file( $filename );
				}
			}
		}

		/**
		 * Download export file
		 *
		 * @return void
		 */
		public static function download_export_file() {

			if ( isset( $_GET['download-wpsc-export'] ) && isset( $_GET['_ajax_nonce'] ) ) {

				$nonce = sanitize_text_field( wp_unslash( $_GET['_ajax_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'download-wpsc-export' ) ) {
					exit( 0 );
				}

				$file_name = intval( $_GET['download-wpsc-export'] );
				if ( ! $file_name ) {
					exit( 0 );
				}

				$file_name      = $file_name . '.csv';
				$path_to_export = get_temp_dir() . $file_name;

				header( 'Content-Type: application/force-download' );
				header( 'Content-Description: File Transfer' );
				header( 'Cache-Control: public' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Disposition: attachment;filename="' . $file_name . '"' );
				header( 'Content-Length: ' . filesize( $path_to_export ) );
				flush();
				readfile( $path_to_export ); // phpcs:ignore

				$fd = wp_delete_file( $path_to_export );

				exit( 0 );
			}
		}
	}
endif;

WPSC_EXPORT_Frontend::init();
