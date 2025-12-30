<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Timer_AG_Settings' ) ) :

	final class WPSC_Timer_AG_Settings {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// add icon.
			add_filter( 'wpsc_icons', array( __CLASS__, 'add_icons' ) );

			// timer menu access.
			add_action( 'wpsc_add_agent_role_other_permissions', array( __CLASS__, 'add_agent_role_other_permissions' ) );
			add_filter( 'wpsc_set_add_agent_role', array( __CLASS__, 'set_add_agent_role_other_permission' ), 10, 2 );
			add_action( 'wpsc_edit_agent_role_other_permissions', array( __CLASS__, 'edit_agent_role_other_permissions' ) );
			add_filter( 'wpsc_set_edit_agent_role', array( __CLASS__, 'set_edit_agent_role_other_permission' ), 10, 3 );

			// add option in raised by.
			add_filter( 'wpsc_itw_raisedby_actions', array( __CLASS__, 'wpsc_itw_raisedby_actions' ) );
			add_action( 'wp_ajax_wpsc_get_total_time_spent', array( __CLASS__, 'get_total_time_spent' ) );

			// add macro.
			add_filter( 'wpsc_macros', array( __CLASS__, 'add_macros' ) );
			add_filter( 'wpsc_replace_macros', array( __CLASS__, 'replace_macro' ), 10, 3 );

			// auto start timer.
			add_action( 'wpsc_create_new_ticket', array( __CLASS__, 'auto_start_timer' ) );

			// auto stop timer.
			add_action( 'wpsc_change_ticket_status', array( __CLASS__, 'auto_stop_timer' ), 10, 4 );
		}

		/**
		 * Add icons to library
		 *
		 * @param array $icons - icon name.
		 * @return array
		 */
		public static function add_icons( $icons ) {

			$icons['stopwatch'] = file_get_contents( WPSC_TIMER_ABSPATH . 'assets/icons/stopwatch-solid.svg' ); //phpcs:ignore
			$icons['stop-button'] = file_get_contents( WPSC_TIMER_ABSPATH . 'assets/icons/stop-button.svg' ); //phpcs:ignore
			return $icons;
		}

		/**
		 * Add permisstion settings to add agent role
		 *
		 * @return void
		 */
		public static function add_agent_role_other_permissions() {
			?>

			<div>
				<input name="caps[]" type="checkbox" value="modify-timer-log">
				<span><?php esc_attr_e( 'Modify others time log', 'wpsc-timer' ); ?></span>
			</div>
			<?php
		}

		/**
		 * Set other permissions for this filter
		 *
		 * @param array  $args - argument array.
		 * @param string $caps - capability string.
		 * @return array
		 */
		public static function set_add_agent_role_other_permission( $args, $caps ) {

			$args['caps']['modify-timer-log'] = in_array( 'modify-timer-log', $caps ) ? true : false;
			return $args;
		}

		/**
		 * Edit permisstion settings to add agent role
		 *
		 * @param array $role - role name.
		 * @return void
		 */
		public static function edit_agent_role_other_permissions( $role ) {
			?>
			<div>
				<input name="caps[]" type="checkbox" <?php checked( $role['caps']['modify-timer-log'], 1 ); ?> value="modify-timer-log">
				<span><?php esc_attr_e( 'Modify others time log', 'wpsc-timer' ); ?></span>
			</div>
			<?php
		}

		/**
		 * Set edit agent role
		 *
		 * @param array  $new - changed value.
		 * @param array  $prev - existing value.
		 * @param string $caps -capability string.
		 * @return array
		 */
		public static function set_edit_agent_role_other_permission( $new, $prev, $caps ) {

			$new['caps']['modify-timer-log'] = in_array( 'modify-timer-log', $caps ) ? true : false;
			return $new;
		}

		/**
		 * Add timer action in raised by widget
		 *
		 * @param array $actions - action name.
		 * @return array
		 */
		public static function wpsc_itw_raisedby_actions( $actions ) {

			$actions['timer'] = array(
				'label'    => esc_attr__( 'Total time spent', 'wpsc-timer' ),
				'icon'     => 'stopwatch',
				'callback' => 'wpsc_get_total_time_spent',
			);
			return $actions;
		}

		/**
		 * Get total time spent by customer
		 *
		 * @return void
		 */
		public static function get_total_time_spent() {

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

			$title = $ticket->customer->name;

			// filters.
			$filters = array(
				'filterSlug'     => 'all',
				'orderby'        => 'date_created',
				'order'          => 'DESC',
				'items_per_page' => 0,
				'is_active'      => 1,
			);

			// system query.
			$filters['system_query'] = $current_user->get_tl_system_query( $filters );

			// meta query.
			$filters['meta_query'] = array(
				'relation' => 'AND',
				array(
					'slug'    => 'customer',
					'compare' => '=',
					'val'     => $ticket->customer->id,
				),
				array(
					'slug'    => 'time_spent',
					'compare' => '!=',
					'val'     => 'PT0M',
				),
			);

			$tickets     = WPSC_Ticket::find( $filters )['results'];
			$total_spent = array();
			$unique_id   = uniqid();

			ob_start();
			?>
			<div style="overflow-x:auto; width:100%;">
				<table class="wpsc-all-time-spent-ticket wpsc-setting-tbl">
					<thead>
						<tr>
							<th><?php echo esc_attr( wpsc__( 'Ticket', 'supportcandy' ) ); ?></th>
							<th><?php esc_attr_e( 'Time Spent', 'wpsc-timer' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ( $tickets ) {
							$gs = get_option( 'wpsc-gs-general' );
							foreach ( $tickets as $ticket ) {

								$url           = admin_url( 'admin.php?page=wpsc-tickets&section=ticket-list&id=' . $ticket->id );
								$subject       = '<a class="wpsc-link" href="' . $url . '" target="__blank">[' . $gs['ticket-alice'] . $ticket->id . '] ' . $ticket->subject . '</a>';
								$total_spent[] = $ticket->time_spent;
								?>
								<tr>
									<td><?php echo wp_kses_post( $subject ); ?></td>
									<td><?php echo esc_attr( WPSC_Functions::date_interval_to_readable( $ticket->time_spent ) ); ?></td>
								</tr>
								<?php
							}
						}
						?>
					</tbody>
				</table>
				<script>
					jQuery('table.wpsc-all-time-spent-ticket').DataTable({
						ordering: false,
						pageLength: 20,
						bLengthChange: false,
						columnDefs: [ 
							{ targets: -1, searchable: false },
							{ targets: '_all', className: 'dt-left' }
						],
						language: supportcandy.translations.datatables
					});
				</script>
			</div>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			if ( $total_spent ) {
				?>
				<span style="float:right;">
					<?php
					$total_spent = WPSC_Functions::date_interval_sum( $total_spent );
					printf(
						/* translators: %1$s: time spent (e.g. 2h 3m) */
						esc_attr__( 'Total time spent: %1$s', 'wpsc-timer' ),
						esc_attr( WPSC_Functions::date_interval_to_readable( $total_spent ) )
					);
					?>
				</span>
				<?php
			}
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Add time logs macro
		 *
		 * @param array $macro - macro name.
		 * @return array
		 */
		public static function add_macros( $macro ) {

			$macro[] = array(
				'tag'        => '{{time_logs}}',
				'title'      => esc_attr__( 'Timer logs', 'wpsc-timer' ),
				'extra-info' => '',
			);
			return $macro;
		}

		/**
		 * Replace macros
		 *
		 * @param string      $str - string.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @param string      $macro - macro name.
		 * @return string
		 */
		public static function replace_macro( $str, $ticket, $macro ) {

			if ( $macro == 'time_logs' ) {

				$logs = WPSC_Timer_Log::find(
					array(
						'meta_query' => array(
							'relation' => 'AND',
							array(
								'slug'    => 'ticket',
								'compare' => '=',
								'val'     => $ticket->id,
							),
							array(
								'slug'    => 'status',
								'compare' => '=',
								'val'     => 'stopped',
							),
						),
					)
				)['results'];

				$log_tbl = '';
				if ( $logs ) {
					ob_start();
					?>
					<table class="wpsc-timer-logs-tbl">
						<thead>
							<tr>
								<th style="padding: 5px;"><?php esc_attr_e( 'Author', 'wpsc-timer' ); ?></th>
								<th style="padding: 5px;"><?php esc_attr_e( 'Date Started', 'wpsc-timer' ); ?></th>
								<th style="padding: 5px;"><?php esc_attr_e( 'Time Spent', 'wpsc-timer' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$tz = wp_timezone();
							foreach ( $logs as $log ) {

								$date_started = $log->date_started;
								$date_started->setTimezone( $tz );
								$time_spent = WPSC_Functions::date_interval_to_readable( $log->time_spent );
								?>
								<tr>
									<td style="padding: 5px;"><?php echo esc_attr( $log->author->name ); ?></td>
									<td style="padding: 5px;"><?php echo esc_attr( ( $date_started )->format( 'Y-m-d H:i:s' ) ); ?></td>
									<td style="padding: 5px;"><?php echo esc_attr( $time_spent ); ?></td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					<?php
					$log_tbl = ob_get_clean();
				}

				$str = str_replace(
					'{{time_logs}}',
					$log_tbl,
					$str
				);
			}

			return $str;
		}

		/**
		 * Automatically start timer after creating a ticket
		 *
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return void
		 */
		public static function auto_start_timer( $ticket ) {

			$setting = get_option( 'wpsc-timer-settings' );
			if ( ! $setting['auto-start'] ) {
				return;
			}

			$agent = new WPSC_Agent( $setting['agent_id'] );
			if ( ! $agent->id ) {
				return;
			}

			WPSC_Timer_Log::insert(
				array(
					'ticket'       => $ticket->id,
					'author'       => $agent->id,
					'date_started' => ( new DateTime() )->format( 'Y-m-d H:i:s' ),
					'temp_start'   => ( new DateTime() )->format( 'Y-m-d H:i:s' ),
					'status'       => 'running',
				)
			);
		}

		/**
		 * Automatically stop timer after closing the ticket
		 *
		 * @param WPSC_Ticket $ticket - ticket objecy.
		 * @param array       $prev - existing value.
		 * @param array       $new - changed value.
		 * @param int         $customer_id - customer id.
		 * @return void
		 */
		public static function auto_stop_timer( $ticket, $prev, $new, $customer_id ) {

			$setting = get_option( 'wpsc-timer-settings' );
			if ( ! $setting['auto-stop'] ) {
				return;
			}

			$gs          = get_option( 'wpsc-gs-general' );
			$tl_advanced = get_option( 'wpsc-tl-ms-advanced' );
			if ( $new == $gs['close-ticket-status'] || in_array( $new, $tl_advanced['closed-ticket-statuses'] ) ) {

				$logs = WPSC_Timer_Log::find(
					array(
						'meta_query' => array(
							'relation' => 'AND',
							array(
								'slug'    => 'ticket',
								'compare' => '=',
								'val'     => $ticket->id,
							),
							array(
								'slug'    => 'status',
								'compare' => 'IN',
								'val'     => array( 'running', 'paused' ),
							),
						),
					)
				)['results'];

				if ( $logs ) {

					foreach ( $logs as $key => $log ) {

						if ( $log->status == 'paused' ) {
							$diff = $log->time_spent;
						} else {
							$diff = WPSC_Functions::date_interval_sum( array( $log->time_spent, $log->temp_start->diff( new DateTime() ) ) );
						}

						$log->time_spent = $diff;
						$log->status     = 'stopped';
						$log->save();
					}

					WPSC_TW_Timer::set_total_time_spent( $ticket );
				}
			}
		}
	}
endif;

WPSC_Timer_AG_Settings::init();
