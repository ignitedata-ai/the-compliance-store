<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_TW_Timer' ) ) :

	final class WPSC_TW_Timer {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// add icon.
			add_filter( 'wpsc_icons', array( __CLASS__, 'add_icons' ) );

			// edit widget settings.
			add_action( 'wp_ajax_wpsc_get_tw_timer', array( __CLASS__, 'get_tw_timer' ) );
			add_action( 'wp_ajax_wpsc_set_tw_timer', array( __CLASS__, 'set_tw_timer' ) );

			// refresh timer content.
			add_action( 'wp_ajax_wpsc_it_refresh_timer', array( __CLASS__, 'refresh_timer' ) );
			add_action( 'wp_ajax_nopriv_wpsc_it_refresh_timer', array( __CLASS__, 'refresh_timer' ) );

			// start/pause/stop timer.
			add_action( 'wp_ajax_wpsc_start_timer', array( __CLASS__, 'start_timer' ) );
			add_action( 'wp_ajax_nopriv_wpsc_start_timer', array( __CLASS__, 'start_timer' ) );
			add_action( 'wp_ajax_wpsc_pause_timer', array( __CLASS__, 'pause_timer' ) );
			add_action( 'wp_ajax_nopriv_wpsc_pause_timer', array( __CLASS__, 'pause_timer' ) );
			add_action( 'wp_ajax_wpsc_resume_timer', array( __CLASS__, 'resume_timer' ) );
			add_action( 'wp_ajax_nopriv_wpsc_resume_timer', array( __CLASS__, 'resume_timer' ) );
			add_action( 'wp_ajax_wpsc_get_stop_timer', array( __CLASS__, 'get_stop_timer' ) );
			add_action( 'wp_ajax_nopriv_wpsc_get_stop_timer', array( __CLASS__, 'get_stop_timer' ) );
			add_action( 'wp_ajax_wpsc_set_stop_timer', array( __CLASS__, 'set_stop_timer' ) );
			add_action( 'wp_ajax_nopriv_wpsc_set_stop_timer', array( __CLASS__, 'set_stop_timer' ) );
			add_action( 'wp_ajax_wpsc_delete_timer_log', array( __CLASS__, 'delete_timer_log' ) );
			add_action( 'wp_ajax_nopriv_wpsc_delete_timer_log', array( __CLASS__, 'delete_timer_log' ) );

			// add new timer log.
			add_action( 'wp_ajax_wpsc_add_new_timer_log', array( __CLASS__, 'add_new_timer_log' ) );
			add_action( 'wp_ajax_nopriv_wpsc_add_new_timer_log', array( __CLASS__, 'add_new_timer_log' ) );
			add_action( 'wp_ajax_wpsc_set_new_log_timer', array( __CLASS__, 'set_new_log_timer' ) );
			add_action( 'wp_ajax_nopriv_wpsc_set_new_log_timer', array( __CLASS__, 'set_new_log_timer' ) );

			// view timer log.
			add_action( 'wp_ajax_wpsc_view_timer_logs', array( __CLASS__, 'view_timer_logs' ) );
			add_action( 'wp_ajax_nopriv_wpsc_view_timer_logs', array( __CLASS__, 'view_timer_logs' ) );

			// export tickets.
			add_action( 'wp_ajax_wpsc_export_timer_logs', array( __CLASS__, 'export_timer_logs' ) );
			add_action( 'wp_ajax_nopriv_wpsc_export_timer_logs', array( __CLASS__, 'export_timer_logs' ) );

			add_action( 'init', array( __CLASS__, 'download_timer_logs_file' ) );

			// edit timer log.
			add_action( 'wp_ajax_wpsc_get_edit_timer_log', array( __CLASS__, 'get_edit_timer_log' ) );
			add_action( 'wp_ajax_nopriv_wpsc_get_edit_timer_log', array( __CLASS__, 'get_edit_timer_log' ) );
			add_action( 'wp_ajax_wpsc_set_edit_timer_log', array( __CLASS__, 'set_edit_timer_log' ) );
			add_action( 'wp_ajax_nopriv_wpsc_set_edit_timer_log', array( __CLASS__, 'set_edit_timer_log' ) );

			// reset timer.
			add_action( 'wp_ajax_wpsc_reset_timer', array( __CLASS__, 'reset_timer' ) );

			// agent autocomplete timer widget.
			add_action( 'wp_ajax_wpsc_agent_autocomplete_timer_widget', array( __CLASS__, 'agent_autocomplete_timer_widget' ) );

			// print timer script.
			add_action( 'wpsc_it_layout_section', array( __CLASS__, 'print_timer_script' ) );
		}

		/**
		 * Add icons to library
		 *
		 * @param array $icons - icon name.
		 * @return array
		 */
		public static function add_icons( $icons ) {

			$icons['add-timer'] = file_get_contents( WPSC_TIMER_ABSPATH . 'assets/icons/add-timer-solid.svg' ); //phpcs:ignore
			$icons['export-timer'] = file_get_contents( WPSC_TIMER_ABSPATH . 'assets/icons/export-timer-solid.svg' ); //phpcs:ignore
			$icons['reset-timer'] = file_get_contents( WPSC_TIMER_ABSPATH . 'assets/icons/reset-timer-solid.svg' ); //phpcs:ignore
			$icons['view-timer-log'] = file_get_contents( WPSC_TIMER_ABSPATH . 'assets/icons/view-timer-log-solid.svg' ); //phpcs:ignore
			return $icons;
		}

		/**
		 * Prints body of current widget
		 *
		 * @param object $ticket - ticket object.
		 * @param array  $settings - setting array.
		 * @return void
		 */
		public static function print_widget( $ticket, $settings ) {

			$current_user = WPSC_Current_User::$current_user;
			if ( ! (
				(
					(
						WPSC_Individual_Ticket::$view_profile == 'customer' ||
						$ticket->customer->id == $current_user->customer->id
					) &&
					$settings['allow-customer']
				) ||
				( WPSC_Individual_Ticket::$view_profile == 'agent' && in_array( $current_user->agent->role, $settings['allowed-agent-roles'] ) )
			) ) {
				return;
			}
			?>

			<div class="wpsc-it-widget wpsc-itw-timer">
				<div class="wpsc-widget-header">
					<h2><?php echo esc_attr( $settings['title'] ); ?></h2>
					<span onclick="wpsc_it_refresh_timer(<?php echo esc_attr( $ticket->id ); ?>)"><?php WPSC_Icons::get( 'sync' ); ?></span>
					<span class="wpsc-itw-toggle" data-widget="wpsc-itw-timer"><?php WPSC_Icons::get( 'chevron-up' ); ?></span>
				</div>
				<div class="wpsc-widget-body">
					<div class="wpsc-it-tw-body"></div>
					<?php
					do_action( 'wpsc_itw_timer', $ticket )
					?>
				</div>
			</div>
			<?php
		}

		/**
		 * Print timer script in open ticket.
		 *
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return void
		 */
		public static function print_timer_script( $ticket ) {
			?>
			<script>
				wpsc_it_refresh_timer(<?php echo esc_attr( $ticket->id ); ?>);
			</script>
			<?php
		}

		/**
		 * Refresh timer widget content
		 *
		 * @return void
		 */
		public static function refresh_timer() {

			$current_user = WPSC_Current_User::$current_user;
			if ( $current_user->is_guest ) {
				WPSC_Individual_Ticket::$view_profile = 'customer';
			}

			WPSC_Individual_Ticket::load_current_ticket();

			$ticket = WPSC_Individual_Ticket::$ticket;

			if ( ! ( ! ( $ticket->customer == $current_user ) || ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			self::get_tw_body( $ticket );
			wp_die();
		}

		/**
		 * Timer widget
		 *
		 * @param object $ticket - ticket object.
		 * @return object
		 */
		public static function get_tw_body( $ticket ) {

			ob_start();
			$time_spent = WPSC_Functions::date_interval_to_readable( $ticket->time_spent );
			$time_spent = $time_spent ? $time_spent : '0m';
			?>
			<div class="wpsc-timer-count"><?php echo esc_attr( $time_spent ); ?></div>


			<?php
			$current_user = WPSC_Current_User::$current_user;
			if ( ! $current_user->is_agent ) {
				echo ob_get_clean(); //phpcs:ignore
				return;
			}

			// Check if ticket is deleted.
			if ( ! $ticket->is_active ) {
				?>
				<div class="user-list-item">
					<div class="ul-body">
						<div class="ul-actions wpsc-timer-actions">
							<span title="<?php echo esc_attr__( 'View logs', 'wpsc-timer' ); ?>" onclick="wpsc_view_timer_logs(<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_view_timer_logs' ) ); ?>')"><?php WPSC_Icons::get( 'view-timer-log' ); ?></span>
						</div>
					</div>
				</div>
				<?php
				return;
			}
			?>

			<div style="margin-bottom: 10px;">
				<?php
				$arg  = array(
					'order'      => 'DESC',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'slug'    => 'ticket',
							'compare' => '=',
							'val'     => $ticket->id,
						),
						array(
							'slug'    => 'author',
							'compare' => '=',
							'val'     => $current_user->agent->id,
						),
						array(
							'slug'    => 'status',
							'compare' => 'IN',
							'val'     => array( 'running', 'paused' ),
						),
					),
				);
				$logs = WPSC_Timer_Log::find( $arg )['results'];

				if ( $logs ) {

					$log = $logs[0];

					if ( $log->status == 'running' ) {
						?>
						<button class="wpsc-button normal secondary" onclick="wpsc_pause_timer(<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_pause_timer' ) ); ?>')"><?php esc_attr_e( 'Pause', 'wpsc-timer' ); ?></button>
						<button class="wpsc-button normal secondary" onclick="wpsc_stop_timer(<?php echo esc_attr( $ticket->id ); ?>, <?php echo esc_attr( $current_user->agent->id ); ?>,  '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_stop_timer' ) ); ?>')"><?php esc_attr_e( 'Stop Timer', 'wpsc-timer' ); ?></button>
						<?php
					} else {
						?>
						<button class="wpsc-button normal secondary" onclick="wpsc_resume_timer(<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_resume_timer' ) ); ?>')"><?php esc_attr_e( 'Resume', 'wpsc-timer' ); ?></button>
						<button class="wpsc-button normal secondary" onclick="wpsc_stop_timer(<?php echo esc_attr( $ticket->id ); ?>, <?php echo esc_attr( $current_user->agent->id ); ?>,  '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_stop_timer' ) ); ?>')"><?php esc_attr_e( 'Stop Timer', 'wpsc-timer' ); ?></button>
						<?php
					}
				} else {
					?>
					<button class="wpsc-button normal secondary" onclick="wpsc_start_timer(<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_start_timer' ) ); ?>')"><?php esc_attr_e( 'Start Timer', 'wpsc-timer' ); ?></button>
					<?php
				}
				?>

			</div>
			<?php

			$status_translations = array(
				'running' => esc_attr__( 'Running', 'wpsc-timer' ),
				'paused'  => esc_attr__( 'Paused', 'wpsc-timer' ),
				'stopped' => esc_attr__( 'Stopped', 'wpsc-timer' ),
			);

			$arg  = array(
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
			);
			$logs = WPSC_Timer_Log::find( $arg )['results'];

			if ( $logs ) {

				foreach ( $logs as $key => $log ) {

					$diff = null;
					if ( $log->status == 'paused' ) {
						$diff = $log->time_spent;
					} else {
						$diff = WPSC_Functions::date_interval_sum( array( $log->time_spent, $log->temp_start->diff( new DateTime() ) ) );
					}

					$time_spent = $diff->d || $diff->days || $diff->h || $diff->i ? WPSC_Functions::date_interval_to_readable( $diff ) : esc_attr__( 'Just started', 'wpsc-timer' );
					?>
					<div style="margin-bottom: 5px;">
						<div class="wpsc-tln wpsc-agent-timer-log">
							<div class="wpsc-timer-agent-info">
								<?php echo esc_attr( $log->author->name ); ?>
								<div class="wpsc-tlt">
									<?php
									printf(
										/* translators: %1$s: time spent %2$s: timer status e.g. 1h 45m (Running) */
										esc_attr__( '%1$s (%2$s)', 'wpsc-timer' ),
										$time_spent, $status_translations[ $log->status ] //phpcs:ignore
									);
									?>
								</div>
							</div>
							<div class="wpsc-timer-stop-button">
								<?php
								if ( $current_user->agent->id != $log->author->id && $current_user->agent->has_cap( 'modify-timer-log' ) ) {
									?>
									<span title="<?php esc_attr_e( 'Stop', 'wpsc-timer' ); ?>" class="wpsc-stop-timer" onclick="wpsc_stop_timer(<?php echo esc_attr( $ticket->id ); ?>, <?php echo esc_attr( $log->author->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_stop_timer' ) ); ?>')">
									<?php WPSC_Icons::get( 'stop-button' ); ?>
									</span>
									<?php
								}
								?>
							</div>
						</div>
					</div>
					<?php
				}
			}
			?>

			<div class="user-list-item">
				<div class="ul-body">
					<div class="ul-actions wpsc-timer-actions">
						<span title="<?php echo esc_attr__( 'Add new log', 'wpsc-timer' ); ?>" onclick="wpsc_add_new_timer_log(<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_add_new_timer_log' ) ); ?>')"><?php WPSC_Icons::get( 'add-timer' ); ?></span>
						&nbsp;|&nbsp;&nbsp;<span title="<?php echo esc_attr__( 'View logs', 'wpsc-timer' ); ?>" onclick="wpsc_view_timer_logs(<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_view_timer_logs' ) ); ?>')"><?php WPSC_Icons::get( 'view-timer-log' ); ?></span>
						&nbsp;|&nbsp;&nbsp;<span title="<?php echo esc_attr__( 'Export logs', 'wpsc-timer' ); ?>" onclick="wpsc_export_timer_logs(<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_export_timer_logs' ) ); ?>')"><?php WPSC_Icons::get( 'export-timer' ); ?></span>
						<?php
						if ( WPSC_Functions::is_site_admin() ) {
							?>
							&nbsp;|&nbsp;&nbsp;<span title="<?php echo esc_attr__( 'Reset timer', 'wpsc-timer' ); ?>" onclick="wpsc_reset_timer(<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_reset_timer' ) ); ?>')"><?php WPSC_Icons::get( 'reset-timer' ); ?></span>
							<?php
						}
						?>
						<?php do_action( 'wpsc_timer_widget_actions', $ticket ); ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Start timer
		 *
		 * @return void
		 */
		public static function start_timer() {

			if ( check_ajax_referer( 'wpsc_start_timer', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

			$arg = array(
				'order'      => 'DESC',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'slug'    => 'ticket',
						'compare' => '=',
						'val'     => $ticket->id,
					),
					array(
						'slug'    => 'author',
						'compare' => '=',
						'val'     => $current_user->agent->id,
					),
					array(
						'slug'    => 'status',
						'compare' => 'IN',
						'val'     => array( 'running', 'paused' ),
					),
				),
			);
			$logs = WPSC_Timer_Log::find( $arg )['results'];

			if ( ! $logs ) {
				WPSC_Timer_Log::insert(
					array(
						'ticket'       => $ticket->id,
						'author'       => $current_user->agent->id,
						'date_started' => ( new DateTime() )->format( 'Y-m-d H:i:s' ),
						'temp_start'   => ( new DateTime() )->format( 'Y-m-d H:i:s' ),
						'status'       => 'running',
					)
				);
			}

			echo esc_attr( self::get_tw_body( $ticket ) );
			wp_die();
		}

		/**
		 * Pause timer
		 *
		 * @return void
		 */
		public static function pause_timer() {

			if ( check_ajax_referer( 'wpsc_pause_timer', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

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
							'slug'    => 'author',
							'compare' => '=',
							'val'     => $current_user->agent->id,
						),
						array(
							'slug'    => 'status',
							'compare' => '=',
							'val'     => 'running',
						),
					),
				)
			)['results'];

			if ( ! $logs ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$log = $logs[0];

			$diff            = $log->temp_start->diff( new DateTime() );
			$log->time_spent = WPSC_Functions::date_interval_sum( array( $log->time_spent, $diff ) );
			$log->status     = 'paused';
			$log->save();

			echo esc_attr( self::get_tw_body( $ticket ) );
			wp_die();
		}

		/**
		 * Resume timer
		 *
		 * @return void
		 */
		public static function resume_timer() {

			if ( check_ajax_referer( 'wpsc_resume_timer', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

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
							'slug'    => 'author',
							'compare' => '=',
							'val'     => $current_user->agent->id,
						),
						array(
							'slug'    => 'status',
							'compare' => '=',
							'val'     => 'paused',
						),
					),
				)
			)['results'];

			if ( ! $logs ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$log = $logs[0];

			$log->temp_start = new DateTime();
			$log->status     = 'running';
			$log->save();

			echo esc_attr( self::get_tw_body( $ticket ) );

			wp_die();
		}

		/**
		 * Get stop timer log popup
		 *
		 * @return void
		 */
		public static function get_stop_timer() {

			if ( check_ajax_referer( 'wpsc_get_stop_timer', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

			$agent_id = isset( $_POST['agent_id'] ) ? intval( wp_unslash( $_POST['agent_id'] ) ) : 0;
			if ( ! $agent_id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$agent = new WPSC_Agent( $agent_id );
			if ( ! $agent->id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

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
							'slug'    => 'author',
							'compare' => '=',
							'val'     => $agent->id,
						),
						array(
							'slug'    => 'status',
							'compare' => 'IN',
							'val'     => array( 'paused', 'running' ),
						),
					),
				)
			)['results'];

			if ( ! $logs ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$log = $logs[0];

			$diff = null;
			if ( $log->status == 'paused' ) {
				$diff = $log->time_spent;
			} else {
				$diff = WPSC_Functions::date_interval_sum( array( $log->time_spent, $log->temp_start->diff( new DateTime() ) ) );
			}

			$time_spent = WPSC_Functions::date_interval_to_readable( $diff );
			$time_spent = $time_spent ? $time_spent : '1m';

			$tz           = wp_timezone();
			$date_started = $log->date_started;
			$date_started->setTimezone( $tz );

			$title = esc_attr__( 'Add new log', 'wpsc-timer' );

			ob_start();
			?>

			<form action="#" onsubmit="return false;" class="wpsc-frm-add-log">

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Time spent', 'wpsc-timer' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<input placeholder="e.g. 2d 1h 37m" name="time_spent" type="text" value="<?php echo esc_attr( $time_spent ); ?>" autocomplete="off">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Date started', 'wpsc-timer' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<input id="date_started" type="text" name="date_started" value="<?php echo esc_attr( $date_started->format( 'Y-m-d H:i' ) ); ?>" autocomplete="off"/>
					<script>
						jQuery('#date_started').flatpickr({
							maxDate: new Date(),
							enableTime: true,
							disableMobile: true,
						});
					</script>
				</div>
				<?php
				if ( $current_user->agent->has_cap( 'modify-timer-log' ) ) {
					?>
					<div class="wpsc-input-group">
						<div class="label-container">
							<label for="">
								<?php esc_attr_e( 'Author', 'wpsc-timer' ); ?>
								<span class="required-char">*</span>
							</label>
						</div>
						<select name="log_author" id="log_author">
							<option value="<?php echo esc_attr( $agent->id ); ?>"><?php echo esc_attr( $agent->name ); ?></option>
						</select>
					</div>
					<?php
				} elseif ( $current_user->agent->has_cap( 'modify-timer-log' ) ) {
					?>
					<input type="hidden" name="log_author" value="<?php echo esc_attr( $agent->id ); ?>">
					<?php
				}
				?>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Description', 'wpsc-timer' ); ?>
						</label>
					</div>
					<textarea type="text" name="log_description" autocomplete="off" rows="5"></textarea>
				</div>

				<script>
					// author autocomplete
					jQuery('#log_author').selectWoo({
						ajax: {
							url: supportcandy.ajax_url,
							dataType: 'json',
							delay: 250,
							data: function (params) {
								return {
									q: params.term, // search term
									page: params.page,
									action: 'wpsc_agent_autocomplete_timer_widget',
									_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_agent_autocomplete_timer_widget' ) ); ?>',
									isMultiple: 1, // to avoid none
									isAgentgroup: 0
								};
							},
							processResults: function (data, params) {
								var terms = [];
								if ( data ) {
									jQuery.each( data, function( id, text ) {
										terms.push( { id: text.id, text: text.title } );
									});
								}
								return {
									results: terms
								};
							},
							cache: true
						},
						escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
						minimumInputLength: 0,
						allowClear: false,
					});
				</script>

				<?php do_action( 'wpsc_get_timer_log_body' ); ?>

				<input type="hidden" name="ticket_id" value="<?php echo esc_attr( $ticket->id ); ?>">
				<input type="hidden" name="log_id" value="<?php echo esc_attr( $log->id ); ?>">
				<input type="hidden" name="action" value="wpsc_set_stop_timer">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_stop_timer' ) ); ?>">

			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_set_stop_timer(this);">
				<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_delete_log(<?php echo esc_attr( $log->id ); ?>, <?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_delete_log' ) ); ?>');">
				<?php esc_attr_e( 'Delete log', 'wpsc-timer' ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Stop timer and add log
		 *
		 * @return void
		 */
		public static function set_stop_timer() {

			if ( check_ajax_referer( 'wpsc_set_stop_timer', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

			$id = isset( $_POST['log_id'] ) ? sanitize_text_field( wp_unslash( $_POST['log_id'] ) ) : '';
			if ( ! $id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$log = new WPSC_Timer_Log( $id );
			if ( ! $log ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$time_spent = isset( $_POST['time_spent'] ) ? sanitize_text_field( wp_unslash( $_POST['time_spent'] ) ) : '';
			if (
				! $time_spent ||
				! preg_match( '/^(\d*d)?(\d*h)?(\d*m)?$/', str_replace( ' ', '', $time_spent ) )
			) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$date_started = isset( $_POST['date_started'] ) ? sanitize_text_field( wp_unslash( $_POST['date_started'] ) ) : '';
			if (
				! $date_started ||
				! preg_match( '/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}/', $date_started )
			) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			if ( $current_user->agent->has_cap( 'modify-timer-log' ) ) {

				$author_id = isset( $_POST['log_author'] ) ? intval( $_POST['log_author'] ) : 0;
				if ( ! $author_id ) {
					wp_send_json_error( 'Bad Request', 400 );
				}
			} else {

				$author_id = $current_user->agent->id;
			}

			$author = new WPSC_Agent( $author_id );
			if ( ! $author ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$description = isset( $_POST['log_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['log_description'] ) ) : '';

			$log->time_spent   = WPSC_Functions::readable_to_date_interval( $time_spent );
			$log->date_started = WPSC_Functions::get_utc_date_str( $date_started . ':00' );
			$log->author       = $author;
			$log->log_by       = $current_user->agent->id;
			$log->description  = trim( $description );
			$log->status       = 'stopped';
			$log = apply_filters( 'wpsc_set_edit_cf_log_timer', $log );
			$log->save();

			self::set_total_time_spent( $ticket );
			wp_die();
		}

		/**
		 * Delete timer log
		 *
		 * @return void
		 */
		public static function delete_timer_log() {

			if ( check_ajax_referer( 'wpsc_delete_log', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();

			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

			$id = isset( $_POST['log_id'] ) ? sanitize_text_field( wp_unslash( $_POST['log_id'] ) ) : '';
			if ( ! $id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$log = new WPSC_Timer_Log( $id );
			if ( ! $log ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			if ( ! ( $current_user->agent == $log->author || $current_user->agent->has_cap( 'modify-timer-log' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			WPSC_Timer_Log::destroy( $log );

			self::set_total_time_spent( $ticket );
			wp_die();
		}

		/**
		 * Get edit widget settings
		 *
		 * @return void
		 */
		public static function get_tw_timer() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$ticket_widgets = get_option( 'wpsc-ticket-widget', array() );
			$timer          = $ticket_widgets['timer'];
			$title          = $timer['title'];
			$roles          = get_option( 'wpsc-agent-roles', array() );
			ob_start();
			?>

			<form action="#" onsubmit="return false;" class="wpsc-frm-edit-timer">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Title', 'supportcandy' ) ); ?></label>
					</div>
					<input name="label" type="text" value="<?php echo esc_attr( $timer['title'] ); ?>" autocomplete="off">
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Enable', 'supportcandy' ) ); ?></label>
					</div>
					<select name="is_enable">
						<option <?php selected( $timer['is_enable'], '1' ); ?> value="1"><?php echo esc_attr( wpsc__( 'Yes', 'supportcandy' ) ); ?></option>
						<option <?php selected( $timer['is_enable'], '0' ); ?>  value="0"><?php echo esc_attr( wpsc__( 'No', 'supportcandy' ) ); ?></option>
					</select>
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Allowed for customer', 'supportcandy' ); ?></label>
					</div>
					<select id="allow-customer" name="allow-customer">
						<option <?php selected( $timer['allow-customer'], '1' ); ?> value="1"><?php echo esc_attr( wpsc__( 'Yes', 'supportcandy' ) ); ?></option>
						<option <?php selected( $timer['allow-customer'], '0' ); ?> value="0"><?php echo esc_attr( wpsc__( 'No', 'supportcandy' ) ); ?></option>
					</select>
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Allowed agent roles', 'supportcandy' ) ); ?></label>
					</div>
					<select multiple id="wpsc-select-agents" name="agents[]" placeholder="">
						<?php
						foreach ( $roles as $key => $role ) {
							$selected = in_array( $key, $timer['allowed-agent-roles'] ) ? 'selected' : ''
							?>
							<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $role['label'] ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<script>
					jQuery('#wpsc-select-agents').selectWoo({
						allowClear: false,
						placeholder: ""
					});
				</script>
				<?php do_action( 'wpsc_get_timer_body' ); ?>
				<input type="hidden" name="action" value="wpsc_set_tw_timer">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_tw_timer' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_set_tw_timer(this);">
				<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			do_action( 'wpsc_get_tw_timer_widget_footer' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Set edit widget settings
		 *
		 * @return void
		 */
		public static function set_tw_timer() {

			if ( check_ajax_referer( 'wpsc_set_tw_timer', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$label = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : '';
			if ( ! $label ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$is_enable          = isset( $_POST['is_enable'] ) ? intval( $_POST['is_enable'] ) : 0;
			$allow_for_customer = isset( $_POST['allow-customer'] ) ? intval( $_POST['allow-customer'] ) : 0;
			$agents             = isset( $_POST['agents'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['agents'] ) ) ) : array();

			$ticket_widgets                                 = get_option( 'wpsc-ticket-widget', array() );
			$ticket_widgets['timer']['title']               = $label;
			$ticket_widgets['timer']['is_enable']           = $is_enable;
			$ticket_widgets['timer']['allow-customer']      = $allow_for_customer;
			$ticket_widgets['timer']['allowed-agent-roles'] = $agents;
			update_option( 'wpsc-ticket-widget', $ticket_widgets );

			// remove string translations.
			WPSC_Translations::remove( 'wpsc-twt-timer' );
			WPSC_Translations::add( 'wpsc-twt-timer', stripslashes( $label ) );
			wp_die();
		}

		/**
		 * Adds new timer to log
		 *
		 * @return void
		 */
		public static function add_new_timer_log() {

			if ( check_ajax_referer( 'wpsc_add_new_timer_log', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

			// Check if ticket is deleted.
			if ( ! $ticket->is_active ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$title = esc_attr__( 'Add new log', 'wpsc-timer' );

			ob_start();
			?>

			<form action="#" onsubmit="return false;" class="wpsc-frm-add-new-log">

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Time spent', 'wpsc-timer' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<input placeholder="e.g. 2d 1h 37m" name="time_spent" type="text" value="" autocomplete="off">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Date started', 'wpsc-timer' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<input id="date_started" type="text" name="date_started" value="" autocomplete="off"/>
					<script>
						jQuery('#date_started').flatpickr({
							maxDate: new Date(),
							enableTime: true,
							disableMobile: true,
						});
					</script>
				</div>
				<?php

				if ( $current_user->agent->has_cap( 'modify-timer-log' ) ) {
					?>
					<div class="wpsc-input-group">
						<div class="label-container">
							<label for="">
								<?php esc_attr_e( 'Author', 'wpsc-timer' ); ?>
								<span class="required-char">*</span>
							</label>
						</div>
						<select name="log_author" id="log_author">
							<option value="<?php echo esc_attr( $current_user->agent->id ); ?>"><?php echo esc_attr( $current_user->agent->name ); ?></option>
						</select>
					</div>
					<?php
				}
				?>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Description', 'wpsc-timer' ); ?>
						</label>
					</div>
					<textarea type="text" name="log_description" autocomplete="off" rows="5"></textarea>
				</div>

				<script>
					// author autocomplete.
					jQuery('#log_author').selectWoo({
						ajax: {
							url: supportcandy.ajax_url,
							dataType: 'json',
							delay: 250,
							data: function (params) {
								return {
									q: params.term, // search term.
									page: params.page,
									action: 'wpsc_agent_autocomplete_timer_widget',
									_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_agent_autocomplete_timer_widget' ) ); ?>',
									isMultiple: 1, // to avoid none
									isAgentgroup: 0
								};
							},
							processResults: function (data, params) {
								var terms = [];
								if ( data ) {
									jQuery.each( data, function( id, text ) {
										terms.push( { id: text.id, text: text.title } );
									});
								}
								return {
									results: terms
								};
							},
							cache: true
						},
						escapeMarkup: function (markup) { return markup; }, // let our custom formatter work.
						minimumInputLength: 0,
						allowClear: false,
					});
				</script>
				<?php do_action( 'wpsc_get_timer_log_body' ); ?>
				<input type="hidden" name="ticket_id" value="<?php echo esc_attr( $ticket->id ); ?>">
				<input type="hidden" name="action" value="wpsc_set_new_log_timer">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_new_log_timer' ) ); ?>">

			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_set_new_log_timer(this);">
				<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Set new timer log
		 *
		 * @return void
		 */
		public static function set_new_log_timer() {

			if ( check_ajax_referer( 'wpsc_set_new_log_timer', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}
			$data = array();
			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;
			$data['ticket'] = $ticket->id;

			$time_spent = isset( $_POST['time_spent'] ) ? sanitize_text_field( wp_unslash( $_POST['time_spent'] ) ) : '';
			if (
				! $time_spent ||
				! preg_match( '/^(\d*d)?(\d*h)?(\d*m)?$/', str_replace( ' ', '', $time_spent ) )
			) {
				wp_send_json_error( 'Bad Request', 400 );
			}
			$time_spent = WPSC_Functions::readable_to_date_interval( $time_spent );
			$data['time_spent'] = WPSC_Functions::date_interval_to_string( $time_spent );

			$date_started = isset( $_POST['date_started'] ) ? sanitize_text_field( wp_unslash( $_POST['date_started'] ) ) : '';
			if (
				! $date_started ||
				! preg_match( '/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}/', $date_started )
			) {
				wp_send_json_error( 'Bad Request', 400 );
			}
			$data['date_started'] = WPSC_Functions::get_utc_date_str( $date_started . ':00' );

			if ( $current_user->agent->has_cap( 'modify-timer-log' ) ) {

				$author_id = isset( $_POST['log_author'] ) ? intval( $_POST['log_author'] ) : 0;
				if ( ! $author_id ) {
					wp_send_json_error( 'Bad Request', 400 );
				}
			} else {

				$author_id = $current_user->agent->id;
			}

			$author = new WPSC_Agent( $author_id );
			if ( ! $author ) {
				wp_send_json_error( 'Bad Request', 400 );
			}
			$data['author'] = $author->id;
			$data['log_by'] = $current_user->agent->id;

			$description = isset( $_POST['log_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['log_description'] ) ) : '';
			$data['description'] = trim( $description );

			$data = apply_filters( 'wpsc_set_new_cf_log_timer', $data );

			WPSC_Timer_Log::insert( $data );

			self::set_total_time_spent( $ticket );
			wp_die();
		}

		/**
		 * Tabular representaion of timer log
		 *
		 * @return void
		 */
		public static function view_timer_logs() {

			if ( check_ajax_referer( 'wpsc_view_timer_logs', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

			// Check if ticket is deleted.
			$is_disabled = false;
			if ( ! $ticket->is_active ) {
				$is_disabled = true;
			}

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

			$total_spent = WPSC_Functions::date_interval_to_readable( $ticket->time_spent );

			$unique_id = uniqid();
			$title     = esc_attr__( 'Time logs', 'wpsc-timer' );
			ob_start();
			?>

			<div style="width: 100%">

				<div class="wpsc-widget-body" style="padding-left: 0;">
					<div class="info-list-item">
						<div class="info-label"><?php esc_attr_e( 'Total time spent:', 'wpsc-timer' ); ?></div>
						<div class="info-val"><?php echo esc_attr( $total_spent ); ?></div>
					</div>
				</div>

				<table class="wpsc-timer-logs wpsc-setting-tbl">
					<thead>
						<tr>
							<th><?php esc_attr_e( 'Author', 'wpsc-timer' ); ?></th>
							<th><?php esc_attr_e( 'Date Started', 'wpsc-timer' ); ?></th>
							<th><?php esc_attr_e( 'Time Spent', 'wpsc-timer' ); ?></th>
							<th><?php esc_attr_e( 'Description', 'wpsc-timer' ); ?></th>
							<?php do_action( 'wpsc_timer_logs_th' ); ?>
							<?php
							if ( ! $is_disabled ) {
								?>
								<th><?php echo esc_attr( wpsc__( 'Actions', 'supportcandy' ) ); ?></th>
								<?php
							}
							?>
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
								<td><?php echo esc_attr( $log->author->name ); ?></td>
								<td><?php echo esc_attr( ( $date_started )->format( 'Y-m-d H:i:s' ) ); ?></td>
								<td><?php echo esc_attr( $time_spent ); ?></td>
								<td><?php echo esc_attr( $log->description ); ?></td>
								<?php do_action( 'wpsc_timer_logs_td', $log ); ?>
								<td>
									<?php
									if ( ( $current_user->agent == $log->author || $current_user->agent->has_cap( 'modify-timer-log' ) ) && ! $is_disabled ) {
										?>
										<span class="wpsc-link" onclick="wpsc_get_edit_timer_log(<?php echo esc_attr( $ticket->id ); ?>,<?php echo esc_attr( $log->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_edit_timer_log' ) ); ?>')"><?php echo esc_attr( wpsc__( 'Edit', 'supportcandy' ) ); ?></span> |
										<span class="wpsc-link" onclick="wpsc_delete_log(<?php echo esc_attr( $log->id ); ?>,<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_delete_log' ) ); ?>')"><?php echo esc_attr( wpsc__( 'Delete', 'supportcandy' ) ); ?></span>
										<?php
									}
									?>
								</td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<script>
					jQuery('table.wpsc-timer-logs').DataTable({
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

			if ( ! $is_disabled ) {
				?>
				<button class="wpsc-button small primary" onclick="wpsc_add_new_timer_log(<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_add_new_timer_log' ) ); ?>');">
					<?php echo esc_attr( wpsc__( 'Add new', 'supportcandy' ) ); ?>
				</button>
				<button class="wpsc-button small primary" onclick="wpsc_export_timer_logs(<?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_export_timer_logs' ) ); ?>');">
					<?php echo esc_attr( wpsc__( 'Export', 'supportcandy' ) ); ?>
				</button>
				<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
					<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
				</button>
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
		 * Export timer logs depends on current filter
		 *
		 * @return void
		 */
		public static function export_timer_logs() {

			if ( check_ajax_referer( 'wpsc_export_timer_logs', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;

			if ( ! $current_user->is_agent ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

			// Check if ticket is deleted.
			if ( ! $ticket->is_active ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$unique_id      = wp_rand( 111111111, 999999999 );
			$file_name      = $unique_id . '.csv';
			$path_to_export = get_temp_dir() . $file_name;

			$url_to_export = add_query_arg(
				array(
					'wpsc-export-timer-logs' => $unique_id,
					'_ajax_nonce'            => wp_create_nonce( 'wpsc-export-timer-logs' ),
				),
				get_home_url()
			);

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

			$fp = fopen( $path_to_export, 'w' ); // phpcs:ignore

			// Write CSV header.
			fputcsv(
				$fp,
				array(
					esc_attr__( 'Ticket', 'supportcandy' ),
					esc_attr__( 'Author', 'wpsc-timer' ),
					esc_attr__( 'Date Started', 'wpsc-timer' ),
					esc_attr__( 'Time Spent', 'wpsc-timer' ),
					esc_attr__( 'Description', 'supportcandy' ),
				)
			);

			$tz = wp_timezone();
			foreach ( $logs as $log ) {

				// Continue is time is PT0M (Zero).
				$is_time = WPSC_Functions::date_interval_to_string( $log->time_spent );
				if ( $is_time == 'PT0M' ) {
					continue;
				}

				$date_started = ( $log->date_started )->setTimezone( $tz );
				$time_spent = WPSC_Functions::date_interval_to_readable( $log->time_spent );

				// Create an array with log data.
				$log_data = array(
					intval( $log->ticket->id ),
					esc_attr( $log->author->name ),
					esc_attr( $date_started->format( 'Y-m-d H:i:s' ) ),
					esc_attr( $time_spent ),
					esc_attr( $log->description ),
				);

				// Write log data to CSV.
				fputcsv( $fp, $log_data );
			}

			fclose( $fp ); // phpcs:ignore

			echo '{"url_to_export":"' . esc_url_raw( $url_to_export ) . '"}';

			wp_die();
		}

		/**
		 * Download export timer logs file
		 *
		 * @return void
		 */
		public static function download_timer_logs_file() {

			if ( isset( $_GET['wpsc-export-timer-logs'] ) && isset( $_GET['_ajax_nonce'] ) ) {

				$nonce = sanitize_text_field( wp_unslash( $_GET['_ajax_nonce'] ) );
				if ( ! wp_verify_nonce( $nonce, 'wpsc-export-timer-logs' ) ) {
					exit( 0 );
				}

				$file_name = sanitize_text_field( wp_unslash( $_GET['wpsc-export-timer-logs'] ) );
				if ( ! $file_name ) {
					exit( 0 );
				}

				$file_name      = $file_name . '.csv';
				$path_to_export = get_temp_dir() . $file_name;

				header( 'Content-Type: ' . mime_content_type( $path_to_export ) );
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

		/**
		 * Get edit timer log
		 *
		 * @return void
		 */
		public static function get_edit_timer_log() {

			if ( check_ajax_referer( 'wpsc_get_edit_timer_log', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$id = isset( $_POST['log_id'] ) ? sanitize_text_field( wp_unslash( $_POST['log_id'] ) ) : '';
			if ( ! $id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$log = new WPSC_Timer_Log( $id );
			if ( ! $log ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

			if ( ! ( $current_user->agent == $log->author || $current_user->agent->has_cap( 'modify-timer-log' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$time_spent = WPSC_Functions::date_interval_to_readable( $log->time_spent );

			$tz           = wp_timezone();
			$date_started = $log->date_started;
			$date_started->setTimezone( $tz );

			$title = esc_attr__( 'Edit log', 'wpsc-timer' );

			ob_start();
			?>

			<form action="#" onsubmit="return false;" class="wpsc-frm-edit-log">

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Time spent', 'wpsc-timer' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<input placeholder="e.g. 2d 1h 37m" name="time_spent" type="text" value="<?php echo esc_attr( $time_spent ); ?>" autocomplete="off">
				</div>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Date started', 'wpsc-timer' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<input id="date_started" type="text" name="date_started" value="<?php echo esc_attr( $date_started->format( 'Y-m-d H:i' ) ); ?>" autocomplete="off"/>
					<script>
						jQuery('#date_started').flatpickr({
							maxDate: new Date(),
							enableTime: true,
							disableMobile: true,
						});
					</script>
				</div>
				<?php

				if ( $current_user->agent->has_cap( 'modify-timer-log' ) ) {
					?>
					<div class="wpsc-input-group">
						<div class="label-container">
							<label for="">
								<?php esc_attr_e( 'Author', 'wpsc-timer' ); ?>
								<span class="required-char">*</span>
							</label>
						</div>
						<select name="log_author" id="log_author">
							<option value="<?php echo esc_attr( $log->author->id ); ?>"><?php echo esc_attr( $log->author->name ); ?></option>
						</select>
					</div>
					<?php
				}
				?>

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Description', 'wpsc-timer' ); ?>
						</label>
					</div>
					<textarea type="text" name="log_description" autocomplete="off" rows="5"><?php echo esc_attr( $log->description ); ?></textarea>
				</div>

				<script>
					// author autocomplete.
					jQuery('#log_author').selectWoo({
						ajax: {
							url: supportcandy.ajax_url,
							dataType: 'json',
							delay: 250,
							data: function (params) {
								return {
									q: params.term, // search term.
									page: params.page,
									action: 'wpsc_agent_autocomplete_timer_widget',
									_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_agent_autocomplete_timer_widget' ) ); ?>',
									isMultiple: 1, // to avoid none
									isAgentgroup: 0
								};
							},
							processResults: function (data, params) {
								var terms = [];
								if ( data ) {
									jQuery.each( data, function( id, text ) {
										terms.push( { id: text.id, text: text.title } );
									});
								}
								return {
									results: terms
								};
							},
							cache: true
						},
						escapeMarkup: function (markup) { return markup; }, // let our custom formatter work.
						minimumInputLength: 0,
						allowClear: false,
					});
				</script>
				<?php do_action( 'wpsc_get_edit_timer_log_body', $log ); ?>
				<input type="hidden" name="ticket_id" value="<?php echo esc_attr( $ticket->id ); ?>">
				<input type="hidden" name="log_id" value="<?php echo esc_attr( $log->id ); ?>">
				<input type="hidden" name="action" value="wpsc_set_edit_timer_log">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_edit_timer_log' ) ); ?>">

			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_set_edit_timer_log(this);">
				<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_delete_log(<?php echo esc_attr( $log->id ); ?>, <?php echo esc_attr( $ticket->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_delete_log' ) ); ?>');">
				<?php esc_attr_e( 'Delete log', 'wpsc-timer' ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Set edit timer log
		 *
		 * @return void
		 */
		public static function set_edit_timer_log() {

			if ( check_ajax_referer( 'wpsc_set_edit_timer_log', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$current_user = WPSC_Current_User::$current_user;
			if ( ! ( $current_user->is_agent && WPSC_Individual_Ticket::has_ticket_cap( 'view' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$ticket = WPSC_Individual_Ticket::$ticket;

			$id = isset( $_POST['log_id'] ) ? sanitize_text_field( wp_unslash( $_POST['log_id'] ) ) : '';
			if ( ! $id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$log = new WPSC_Timer_Log( $id );
			if ( ! $log ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			if ( ! ( $current_user->agent == $log->author || $current_user->agent->has_cap( 'modify-timer-log' ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$time_spent = isset( $_POST['time_spent'] ) ? sanitize_text_field( wp_unslash( $_POST['time_spent'] ) ) : '';
			if (
				! $time_spent ||
				! preg_match( '/^(\d*d)?(\d*h)?(\d*m)?$/', str_replace( ' ', '', $time_spent ) )
			) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$date_started = isset( $_POST['date_started'] ) ? sanitize_text_field( wp_unslash( $_POST['date_started'] ) ) : '';
			if (
				! $date_started ||
				! preg_match( '/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}/', $date_started )
			) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			if ( $current_user->agent->has_cap( 'modify-timer-log' ) ) {

				$author_id = isset( $_POST['log_author'] ) ? intval( $_POST['log_author'] ) : 0;
				if ( ! $author_id ) {
					wp_send_json_error( 'Bad Request', 400 );
				}
			} else {

				$author_id = $current_user->agent->id;
			}

			$author = new WPSC_Agent( $author_id );
			if ( ! $author ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$description = isset( $_POST['log_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['log_description'] ) ) : '';

			$log->time_spent   = WPSC_Functions::readable_to_date_interval( $time_spent );
			$log->date_started = WPSC_Functions::get_utc_date_str( $date_started . ':00' );
			$log->author       = $author;
			$log->log_by       = $current_user->agent->id;
			$log->description  = trim( $description );
			$log = apply_filters( 'wpsc_set_edit_cf_log_timer', $log );
			$log->save();

			self::set_total_time_spent( $ticket );
			wp_die();
		}

		/**
		 * Calculate total time spent for ticket
		 *
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return void
		 */
		public static function set_total_time_spent( $ticket ) {

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

			$time_spent = array();
			foreach ( $logs as $log ) {
				$time_spent[] = $log->time_spent;
			}

			$ticket->time_spent = $time_spent ? WPSC_Functions::date_interval_sum( $time_spent ) : 'PT0M';
			$ticket->save();
		}

		/**
		 * Reset timer
		 *
		 * @return void
		 */
		public static function reset_timer() {

			if ( check_ajax_referer( 'wpsc_reset_timer', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			WPSC_Individual_Ticket::load_current_ticket();
			$ticket = WPSC_Individual_Ticket::$ticket;

			// Check if ticket is deleted.
			if ( ! $ticket->is_active ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$logs = WPSC_Timer_Log::find(
				array(
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'slug'    => 'ticket',
							'compare' => '=',
							'val'     => $ticket->id,

						),
					),
				)
			)['results'];

			if ( $logs ) {

				foreach ( $logs as $log ) {
					WPSC_Timer_Log::destroy( $log );
				}

				$ticket->time_spent = 'PT0M';
				$ticket->save();
			}

			$ticket->time_spent = 'PT0M';
			$ticket->save();
			wp_die();
		}

		/**
		 * Agent autocomplete for timer widget
		 *
		 * @return void
		 */
		public static function agent_autocomplete_timer_widget() {

			if ( check_ajax_referer( 'wpsc_agent_autocomplete_timer_widget', '_ajax_nonce', false ) !== 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$current_user = WPSC_Current_User::$current_user;
			if ( ! $current_user->agent->has_cap( 'modify-timer-log' ) ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$filters = array(
				'term'       => isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '',
				'filter_by'  => 'all',
				'sort_by'    => 'name',
				'isMultiple' => 1,
			);

			$filters['isAgentgroup'] = 0;
			if ( class_exists( 'WPSC_Agentgroups' ) ) {
				$filters['isAgentgroup'] = isset( $_GET['isAgentgroup'] ) ? intval( $_GET['isAgentgroup'] ) : null;
			}

			$response = WPSC_Agent::agent_autocomplete( $filters );
			wp_send_json( $response );
		}

		/**
		 * Get total running time spent for ticket
		 *
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function get_total_time_spent( $ticket ) {

			$logs = WPSC_Timer_Log::find(
				array(
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'slug'    => 'ticket',
							'compare' => '=',
							'val'     => $ticket->id,
						),
					),
				)
			)['results'];

			$time_spent = array();
			foreach ( $logs as $log ) {
				$now = new DateTime();
				if ( $log->status == 'stopped' || $log->status == 'paused' ) {
					$time_spent[] = $log->time_spent;
				} else {
					$time_spent[] = WPSC_Functions::date_interval_sum( array( $log->time_spent, $log->temp_start->diff( new DateTime() ) ) );
				}
			}

			return $time_spent ? WPSC_Functions::date_interval_sum( $time_spent ) : 'PT0M';
		}
	}
endif;

WPSC_TW_Timer::init();
