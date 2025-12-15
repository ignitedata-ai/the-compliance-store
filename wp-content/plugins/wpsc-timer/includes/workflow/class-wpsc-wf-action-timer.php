<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_WF_Action_Timer' ) ) :

	final class WPSC_WF_Action_Timer {

		/**
		 * Slug for this action
		 *
		 * @var string
		 */
		public static $slug = 'timer';

		/**
		 * Initialize the class
		 */
		public static function init() {

			add_filter( 'wpsc_wf_actions', array( __CLASS__, 'wpsc_wf_time_action' ) );
		}

		/**
		 * Print input field
		 *
		 * @param array $wf_actions - pre-defined json value.
		 * @return array
		 */
		public static function wpsc_wf_time_action( $wf_actions ) {

			$wf_actions['timer'] = array(
				'title' => esc_attr__( 'Timer', 'wpsc-workflows' ),
				'class' => 'WPSC_WF_Action_Timer',
			);

			return $wf_actions;
		}

		/**
		 * Print input field
		 *
		 * @param array $action - pre-defined json value.
		 * @return void
		 */
		public static function print( $action = array() ) {

			$unique_id = uniqid( 'wpsc_' );
			$agents   = WPSC_Agent::find( array( 'items_per_page' => 0 ) )['results'];
			?>
			<div class="wf-action-item" data-slug="<?php echo esc_attr( self::$slug ); ?>">
				<div class="wf-action-header">
					<span class="wf-action-title"><?php echo esc_attr( WPSC_WF_Actions::$actions[ self::$slug ]['title'] ); ?></span>
					<span class="wf-remove-action" onclick="wpsc_wf_remove_action(this)"><?php WPSC_Icons::get( 'times-circle' ); ?></span>
				</div>
				<div class="wf-action-body">
					<?php $value = isset( $action['timer'] ) ? $action['timer'] : ''; ?>
					<div class="wpsc-input-group">
						<div class="label-container">
							<label for="">
								<?php echo esc_attr( wpsc__( 'Timer', 'wpsc-workflows' ) ); ?>
								<span class="required-char">*</span>
							</label>
						</div>
						<select name="actions[<?php echo esc_attr( self::$slug ); ?>][timer]" id="timer-select">
							<option <?php selected( 'start', $value, true ); ?> value="start"><?php esc_attr_e( 'Start', 'wpsc-workflows' ); ?></option>
							<option <?php selected( 'stop', $value, true ); ?> value="stop"><?php esc_attr_e( 'Stop', 'wpsc-workflows' ); ?></option>
						</select>
					</div>
					<div class="wpsc-input-group">
						<div class="label-container">
							<label for="">
								<?php echo esc_attr( wpsc__( 'Current assignee (if any)', 'wpsc-workflows' ) ); ?>
							</label>
						</div>
						<?php $current_agent = isset( $action['current-agent'] ) ? intval( $action['current-agent'] ) : 0; ?>
						<select name="actions[<?php echo esc_attr( self::$slug ); ?>][current-agent]">
							<option <?php selected( $current_agent, 1, true ); ?> value="1"><?php echo esc_attr( wpsc__( 'Yes', 'supportcandy' ) ); ?></option>
							<option <?php selected( $current_agent, 0, true ); ?> value="0"><?php echo esc_attr( wpsc__( 'No', 'supportcandy' ) ); ?></option>
						</select>
					</div>
					<?php $value = isset( $action['agent'] ) ? $action['agent'] : 0; ?>
					<div class="wpsc-input-group" id="wpsc-agent-input-group">
						<div class="label-container">
							<label for=""><?php esc_attr_e( 'Fallback agent', 'wpsc-workflows' ); ?></label>
						</div>
						<select class="agent" name="actions[<?php echo esc_attr( self::$slug ); ?>][agent]">
							<?php
							foreach ( $agents as $agent ) {
								?>
								<option <?php selected( $agent->id, $value, true ); ?> value="<?php echo esc_attr( $agent->id ); ?>"><?php echo esc_attr( $agent->name ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
				</div>
				<script>
					// agent autocomplete.
					jQuery('select.agent').selectWoo({
						ajax: {
							url: supportcandy.ajax_url,
							dataType: 'json',
							delay: 250,
							data: function (params) {
								return {
									q: params.term, // search term.
									page: params.page,
									action: 'wpsc_agent_autocomplete_admin_access',
									_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_agent_autocomplete_admin_access' ) ); ?>',
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
			</div>
			<?php
		}

		/**
		 * Sanitize action input data to store in db
		 *
		 * @param array $action - actioin input array of this type.
		 * @return array
		 */
		public static function sanitize_action( $action ) {

			$timer = isset( $action['timer'] ) ? $action['timer'] : 0;
			if ( ! $timer ) {
				wp_send_json_error( esc_attr__( 'Timer not selectd!', 'wpsc-workflows' ), 400 );
			}

			$current_agent = isset( $action['current-agent'] ) ? intval( $action['current-agent'] ) : null;
			if ( ! is_numeric( $current_agent ) ) {
				wp_send_json_error( wpsc__( 'Bad request!', 'supportcandy' ), 400 );
			}

			$agent = isset( $action['agent'] ) ? $action['agent'] : 0;
			if ( ! $agent ) {
				wp_send_json_error( esc_attr__( 'Agent not selectd!', 'wpsc-workflows' ), 400 );
			}

			return array(
				'timer'         => $timer,
				'current-agent' => $current_agent,
				'agent'         => $agent,
			);
		}

		/**
		 * Execute the action of this type
		 *
		 * @param array       $action - action details.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @param array       $workflow - workflow array.
		 * @return void
		 */
		public static function execute( $action, $ticket, $workflow ) {

			$assign_agents = array();
			if ( isset( $action['current-agent'] ) && $action['current-agent'] ) {
				foreach ( $ticket->assigned_agent as $agents ) {
					$assign_agents[] = $agents->id;
				}
			}

			if ( empty( $assign_agents ) ) {
				$assign_agents[] = isset( $action['agent'] ) ? $action['agent'] : 0;
			}

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
						'compare' => 'IN',
						'val'     => $assign_agents,
					),
					array(
						'slug'    => 'status',
						'compare' => 'IN',
						'val'     => array( 'running', 'paused' ),
					),
				),
			);
			$logs = WPSC_Timer_Log::find( $arg )['results'];

			if ( ! $logs && $action['timer'] == 'start' ) {

				foreach ( $assign_agents as $agent ) {

						WPSC_Timer_Log::insert(
							array(
								'ticket'       => $ticket->id,
								'author'       => $agent,
								'date_started' => ( new DateTime() )->format( 'Y-m-d H:i:s' ),
								'temp_start'   => ( new DateTime() )->format( 'Y-m-d H:i:s' ),
								'status'       => 'running',
							)
						);
				}
			} elseif ( $action['timer'] == 'stop' ) {

				if ( $logs ) {
					foreach ( $logs as $log ) {
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

WPSC_WF_Action_Timer::init();
