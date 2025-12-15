<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_DBW_Active_Timer' ) ) :

	final class WPSC_DBW_Active_Timer {

		/**
		 * Widget slug
		 *
		 * @var string
		 */
		public static $widget = 'active-timer';

		/**
		 * Initialize this class
		 */
		public static function init() {

			// Get list of agents.
			add_action( 'wp_ajax_wpsc_dash_get_out_of_timer_list', array( __CLASS__, 'get_out_of_timer_list' ) );
			add_action( 'wp_ajax_nopriv_wpsc_dash_get_out_of_timer_list', array( __CLASS__, 'get_out_of_timer_list' ) );
		}

		/**
		 * Active timer view
		 *
		 * @param string $slug - slug name.
		 * @param array  $widget - widget array.
		 * @return void
		 */
		public static function print_dashboard_widget( $slug, $widget ) {

			$current_user = WPSC_Current_User::$current_user;
			if ( $current_user->is_guest ||
				! ( $current_user->is_agent && in_array( $current_user->agent->role, $widget['allowed-agent-roles'] ) )
			) {
				return;
			}
			?>
			<div class="wpsc-dash-widget wpsc-dash-widget-mid wpsc-<?php echo esc_attr( $slug ); ?>">
				<div class="wpsc-dash-widget-header">
					<div class="wpsc-dashboard-widget-icon-header">
						<?php WPSC_Icons::get( 'list' ); ?>
						<span>
							<?php
							$title = $widget['title'] ? WPSC_Translations::get( 'wpsc-dashboard-widget-' . $slug, stripslashes( htmlspecialchars( $widget['title'] ) ) ) : stripslashes( htmlspecialchars( $widget['title'] ) );
							echo esc_attr( $title );
							?>
						</span>
					</div>
					<div class="wpsc-dash-widget-actions"></div>
				</div>
				<div class="wpsc-dash-widget-content wpsc-dbw-info" id="wpsc-dash-out-of-timer-list"></div>
			</div>
			<script>
				wpsc_dash_get_out_of_timer_list();
				function wpsc_dash_get_out_of_timer_list(){
					jQuery('#wpsc-dash-out-of-timer-list').html( supportcandy.loader_html );
					var data = { action: 'wpsc_dash_get_out_of_timer_list', view: supportcandy.is_frontend, _ajax_nonce: supportcandy.nonce };
					jQuery.post(
						supportcandy.ajax_url,
						data,
						function (response) {
							jQuery('#wpsc-dash-out-of-timer-list').html(response.html);
						}
					);
				}
			</script>
			<?php
		}

		/**
		 * Get agent list and ticket count.
		 *
		 * @return void
		 */
		public static function get_out_of_timer_list() {

			if ( check_ajax_referer( 'general', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$view = isset( $_POST['view'] ) ? sanitize_text_field( wp_unslash( $_POST['view'] ) ) : '0';

			$current_user = WPSC_Current_User::$current_user;
			$widgets = get_option( 'wpsc-dashboard-widgets', array() );
			if ( $current_user->is_guest ||
				! ( $current_user->is_agent && in_array( $current_user->agent->role, $widgets[ self::$widget ]['allowed-agent-roles'] ) )
			) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$logs = WPSC_Timer_Log::find(
				array(
					'items_per_page' => 0,
					'orderby'        => 'time_spent',
					'order'          => 'DESC',
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'slug'    => 'status',
							'compare' => '=',
							'val'     => 'running',
						),
					),
				)
			)['results'];

			$tickets = array();
			foreach ( $logs as $log ) {
				$tickets[] = $log->ticket->id;
			}
			$tickets = array_unique( $tickets );
			?>
			<table class="wpsc-db-out-of-timer-list">
				<thead>
					<tr>
						<th><?php echo esc_attr__( 'Ticket', 'wpsc-timer' ); ?></th>
						<th><?php echo esc_attr__( 'Subject', 'wpsc-timer' ); ?></th>
						<th><?php echo esc_attr__( 'Timer', 'wpsc-timer' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ( $tickets ) {
						foreach ( $tickets as $ticket ) {
							$ticket = new WPSC_Ticket( $ticket );
							WPSC_Individual_Ticket::$ticket = $ticket;
							if ( ! ( $ticket->is_active && $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
								continue;
							}
							$url = WPSC_Functions::get_ticket_url( $ticket->id, $view );
							$spent = WPSC_Functions::date_interval_to_readable( WPSC_TW_Timer::get_total_time_spent( $ticket ) );
							?>
							<tr>
								<td><a href="<?php echo esc_attr( $url ); ?>" target="_blank"><?php echo esc_attr( '#' . $ticket->id ); ?></a></td>
								<td><?php echo esc_attr( $ticket->subject ); ?></td>
								<td><?php echo esc_attr( $spent ); ?></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
			<script>
					var indexLastColumn = jQuery("table.wpsc-db-out-of-timer-list").find('tr')[0].cells.length-1;
					jQuery('table.wpsc-db-out-of-timer-list').DataTable({
						ordering: true,
						order: [[indexLastColumn, 'desc']],
						pageLength: 10,
						bLengthChange: false,
						info: false,
						paging: false,
						searching: false,
						columnDefs: [
							{ targets: '_all', className: 'dt-left' },
							{
								"targets": '_all', // All columns
								"searchable": false,
								"orderable": true
							}
						],
						language: supportcandy.translations.datatables
					});
			</script>
			<?php
			$table = ob_get_clean();
			wp_send_json( array( 'html' => $table ) );
		}
	}
endif;
WPSC_DBW_Active_Timer::init();
