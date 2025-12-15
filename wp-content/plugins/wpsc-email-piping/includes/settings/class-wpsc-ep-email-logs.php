<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Email_Logs' ) ) :

	final class WPSC_EP_Email_Logs {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// get email logs list.
			add_action( 'wp_ajax_wpsc_ep_get_email_logs', array( __CLASS__, 'ep_get_email_logs' ) );
			add_action( 'wp_ajax_wpsc_get_email_logs_list', array( __CLASS__, 'get_email_logs_list' ) );

			// View log details.
			add_action( 'wp_ajax_wpsc_ep_view_email_logs', array( __CLASS__, 'ep_view_email_logs' ) );

			// Schedule cron jobs.
			add_action( 'init', array( __CLASS__, 'schedule_events' ) );
			add_action( 'wpsc_delete_email_logs', array( __CLASS__, 'delete_email_logs' ) );
		}

		/**
		 * Schedule cron job events for SupportCandy
		 *
		 * @return void
		 */
		public static function schedule_events() {

			// Delete email logs.
			if ( ! wp_next_scheduled( 'wpsc_delete_email_logs' ) ) {
				wp_schedule_event(
					WPSC_Cron::get_midnight_timestamp(),
					'daily',
					'wpsc_delete_email_logs'
				);
			}
		}

		/**
		 * Load settings
		 *
		 * @return void
		 */
		public static function ep_get_email_logs() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}
			?>
			<table class="wpsc-email-logs wpsc-setting-tbl">
				<thead>
					<tr>
						<th><?php esc_attr_e( 'Subject', 'wpsc-ep' ); ?></th>
						<th><?php esc_attr_e( 'From', 'wpsc-ep' ); ?></th>
						<th><?php esc_attr_e( 'To', 'wpsc-ep' ); ?></th>
						<th><?php esc_attr_e( 'Date Created', 'wpsc-ep' ); ?></th>
						<th><?php esc_attr_e( 'Status', 'wpsc-ep' ); ?></th>
						<th><?php esc_attr_e( 'Action', 'wpsc-ep' ); ?></th>
					</tr>
				</thead>
			</table>
			<script>
				jQuery(document).ready(function() {

					jQuery('.wpsc-email-logs').dataTable({
						processing: true,
						serverSide: true,
						serverMethod: 'post',
						ajax: { 
							url: supportcandy.ajax_url,
							data: {
								'action': 'wpsc_get_email_logs_list',
								'_ajax_nonce': '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_email_logs_list' ) ); ?>'
							}
						},
						'columns': [
							{ data: 'subject' },
							{ data: 'from' },
							{ data: 'to' },
							{ data: 'date_created' },
							{ data: 'status' },
							{ data: 'action' },
						],
						'bDestroy': true,
						'searching': true,
						'ordering': false,
						'bLengthChange': false,
						pageLength: 20,
						columnDefs: [ 
							{ targets: '_all', className: 'dt-left' },
						],
						language: supportcandy.translations.datatables
					});
				});
			</script>
			<?php
			wp_die();
		}

		/**
		 * Get list of all email logs
		 *
		 * @return void
		 */
		public static function get_email_logs_list() {

			if ( check_ajax_referer( 'wpsc_get_email_logs_list', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			$search     = isset( $_POST['search'] ) && isset( $_POST['search']['value'] ) ? sanitize_text_field( wp_unslash( $_POST['search']['value'] ) ) : '';
			$draw       = isset( $_POST['draw'] ) ? intval( $_POST['draw'] ) : 1;
			$start      = isset( $_POST['start'] ) ? intval( $_POST['start'] ) : 1;
			$rowperpage = isset( $_POST['length'] ) ? intval( $_POST['length'] ) : 20;
			$page_no    = ( $start / $rowperpage ) + 1;

			$args  = WPSC_EP_Logger::find(
				array(
					'search'         => $search,
					'items_per_page' => $rowperpage,
					'page_no'        => $page_no,
					'orderby'        => 'date_created',
					'order'          => 'DESC',
				)
			);
			$logs = $args['results'];

			$data = array();
			foreach ( $logs as $log ) {

				$date_created = is_object( $log->date_created ) ? wp_date( 'Y-m-d H:i:s', $log->date_created->setTimezone( wp_timezone() )->getTimestamp() ) : '';
				$status = $log->status ? esc_attr__( 'Success', 'wpsc-ep' ) : esc_attr__( 'Fail', 'wpsc-ep' );

				ob_start();
				?>
				<a onclick="wpsc_ep_view_email_logs(<?php echo esc_attr( $log->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_view_email_logs' ) ); ?>' )">
					<?php echo esc_attr__( 'View', 'wpsc-ep' ); ?>
				</a>
				<?php
				$actions = ob_get_clean();

				$data[] = array(

					'subject'      => $log->email_subject,
					'from'         => $log->email_from,
					'to'           => $log->email_to,
					'date_created' => $date_created,
					'status'       => $status,
					'action'       => $actions,
				);
			}

			$response = array(
				'draw'                 => intval( $draw ),
				'iTotalRecords'        => $args['total_items'],
				'iTotalDisplayRecords' => $args['total_items'],
				'data'                 => $data,
			);

			wp_send_json( $response );
		}

		/**
		 * View email log popup
		 *
		 * @return void
		 */
		public static function ep_view_email_logs() {

			if ( check_ajax_referer( 'wpsc_ep_view_email_logs', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$log_id = isset( $_POST['log_id'] ) && intval( $_POST['log_id'] ) ? intval( $_POST['log_id'] ) : 0;
			$log_details = new WPSC_EP_Logger( $log_id );
			if ( ! $log_details->id ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$title = esc_attr__( 'Email Logs details', 'wpsc-ep' );
			ob_start();
			?>
			<div style="width: 100%" class="wpsc-ep-email-details">
				<div class="wpsc-ep-log-info">
					<div>
						<strong><span><?php esc_attr_e( 'Subject', 'wpsc-ep' ); ?>:</span></strong><?php echo esc_attr( $log_details->email_subject ); ?>
					</div>
					<div>
						<strong><span><?php esc_attr_e( 'From', 'wpsc-ep' ); ?>:</span></strong><?php echo esc_attr( $log_details->email_from ); ?>
					</div>
					<div>
						<strong><span><?php esc_attr_e( 'To', 'wpsc-ep' ); ?>:</span></strong><?php echo esc_attr( $log_details->email_to ); ?>
					</div>
					<div>
						<strong><span><?php esc_attr_e( 'CC', 'wpsc-ep' ); ?>:</span></strong><?php echo esc_attr( $log_details->email_cc ); ?>
					</div>
				</div>
				<div>
					<strong><span><?php esc_attr_e( 'Logs', 'wpsc-ep' ); ?>:</span></strong>
				</div>
				<div class="wpsc-ep-logs">
					<?php
					foreach ( json_decode( $log_details->logs ) as $log ) {
						?>
						<div class="wpsc-ep-log-item"><?php echo esc_attr( $log ); ?></div>
						<?php
					}
					?>
				</div>
			</div>
			<?php
			$body = ob_get_clean();
			ob_start();
			?>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php esc_attr_e( 'Close', 'supportcandy' ); ?>
			</button>
			<?php
			do_action( 'wpsc_get_tw_ticket_status_widget_footer' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Delete email logs
		 */
		public static function delete_email_logs() {

			$general = get_option( 'wpsc-ep-general-settings' );
			$tz = wp_timezone();
			$today = new DateTime( 'now', $tz );
			$diff = ( clone $today )->sub( new DateInterval( 'P' . ( $general['delete-email-logs-after'] - 1 ) . 'D' ) );

			$args = array(
				'items_per_page' => 0,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'slug'    => 'date_created',
						'compare' => '<',
						'val'     => $diff->format( 'Y-m-d' ),
					),
				),
			);
			$logs = WPSC_EP_Logger::find( $args )['results'];

			foreach ( $logs as $log ) {
				WPSC_EP_Logger::destroy( $log );
			}
		}
	}
endif;

WPSC_EP_Email_Logs::init();
