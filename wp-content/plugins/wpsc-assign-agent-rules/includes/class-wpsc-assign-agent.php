<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Assign_Agent' ) ) {

	final class WPSC_Assign_Agent {

		/**
		 * Initialize the class
		 *
		 * @return void
		 */
		public static function init() {

			// assign agents to ticket when it is created.
			add_action( 'wpsc_create_new_ticket', array( __CLASS__, 'assign_agents' ), 2 );

			// assign agents to unassigned ticket after reply.
			add_action( 'wpsc_post_reply', array( __CLASS__, 'auto_assign_first_responder' ), 199 );
		}

		/**
		 * Assign agents to ticket
		 *
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return void
		 */
		public static function assign_agents( $ticket ) {

			$tz           = wp_timezone();
			$date_created = ( $ticket->date_created )->setTimezone( $tz );
			$rules        = get_option( 'wpsc-aar-rules', array() );

			// return if no rules fount.
			if ( ! $rules ) {
				return;
			}

			// return if ticket already has assigned agents.
			if ( $ticket->assigned_agent ) {
				return;
			}

			$agents = array();
			foreach ( $rules as $id => $rule ) :

				if ( WPSC_Ticket_Conditions::is_valid( $rule['conditions'], $ticket ) ) {

					// assign agents to ticket.
					if ( $rule['agents'] ) {

						$rule_agents = explode( '|', $rule['agents'] );
						if ( $rule['assign_method'] == 'assign_all' ) {

							$agents = array_merge( $agents, $rule_agents );

						} elseif ( $rule['assign_method'] == 'cwh_agent' ) {

							$agents[] = self::get_one_agent_with_cwh( $rule_agents, $date_created );

						} elseif ( $rule['assign_method'] == 'mw_agent' ) {

							$agents[] = self::get_one_agent_with_mw( $rule_agents, $date_created );
						}
					}

					$agents = apply_filters( 'wpsc_arr_assign_agent_list', $agents, $rule, $ticket );
				}

			endforeach;

			// return if no agents matched.
			if ( ! $agents ) {
				return;
			}

			// assign agents to ticket.
			$ticket->assigned_agent = array_unique( $agents );
			$ticket->save();
		}

		/**
		 * Get one agent with closest working hr
		 *
		 * @param array    $agents - agent ids.
		 * @param DateTime $date_created - rule created date.
		 * @return integer
		 */
		public static function get_one_agent_with_cwh( $agents, $date_created ) {

			// return first index in case of only one agent.
			if ( count( $agents ) == 1 ) {
				return $agents[0];
			}

			$working_hrs = array();
			foreach ( $agents as $agent_id ) {
				$working_hrs[] = array_merge( array( 'agent_id' => $agent_id ), WPSC_Working_Hour::get_closest_wh_by_date( $date_created, $agent_id ) );
			}

			// sort by start time.
			usort(
				$working_hrs,
				function ( $item1, $item2 ) {
					return $item1['start_time'] <=> $item2['start_time'];
				}
			);

			// sort by same start time.
			$temp = array_filter(
				$working_hrs,
				function ( $v, $k ) use ( $working_hrs ) {
					return $v['start_time'] == $working_hrs[0]['start_time'];
				},
				ARRAY_FILTER_USE_BOTH
			);

			// return if there is only one agent.
			if ( count( $temp ) == 1 ) {
				return $temp[0]['agent_id'];
			}

			// add wokload to array.
			foreach ( $temp as $key => $wh ) {
				$agent          = new WPSC_Agent( $wh['agent_id'] );
				$wh['workload'] = $agent->workload;
				$temp[ $key ]   = $wh;
			}

			// sort by workload.
			usort(
				$temp,
				function ( $item1, $item2 ) {
					return $item1['workload'] <=> $item2['workload'];
				}
			);

			return $temp[0]['agent_id'];
		}

		/**
		 * Get one agent with minimum work load
		 *
		 * @param array    $agents - agent ids.
		 * @param DateTime $date_created - rule created date.
		 * @return integer
		 */
		public static function get_one_agent_with_mw( $agents, $date_created ) {

			// return first index in case of only one agent.
			if ( count( $agents ) == 1 ) {
				return $agents[0];
			}

			$temp_agents = array();
			foreach ( $agents as $agent_id ) {
				$agent         = new WPSC_Agent( $agent_id );
				$temp_agents[] = array(
					'agent'    => $agent,
					'workload' => $agent->workload,
				);
			}

			// sort agents by workload.
			usort(
				$temp_agents,
				function ( $item1, $item2 ) {
					return $item1['workload'] <=> $item2['workload'];
				}
			);

			// sort by same start time.
			$temp = array_filter(
				$temp_agents,
				function ( $v, $k ) use ( $temp_agents ) {
					return $v['workload'] == $temp_agents[0]['workload'];
				},
				ARRAY_FILTER_USE_BOTH
			);

			// if it is only one agent left, return it.
			if ( count( $temp ) == 1 ) {
				return ( $temp[0]['agent'] )->id;
			}

			// get working hrs of remaining agents.
			$working_hrs = array();
			foreach ( $temp as $agent_temp ) {
				$working_hrs[] = array_merge(
					array( 'agent' => $agent_temp['agent'] ),
					WPSC_Working_Hour::get_closest_wh_by_date( $date_created, $agent_temp['agent']->id )
				);
			}

			// sort by start time.
			usort(
				$working_hrs,
				function ( $item1, $item2 ) {
					return $item1['start_time'] <=> $item2['start_time'];
				}
			);

			// sort by same start time.
			$temp = array_filter(
				$working_hrs,
				function ( $v, $k ) use ( $working_hrs ) {
					return $v['start_time'] == $working_hrs[0]['start_time'];
				},
				ARRAY_FILTER_USE_BOTH
			);

			return $temp[0]['agent']->id;
		}

		/**
		 * Auto-assign first responder to unassigned ticket
		 *
		 * @param WPSC_Thread $thread - thread object.
		 * @return void
		 */
		public static function auto_assign_first_responder( $thread ) {

			$ticket = new WPSC_Ticket( $thread->ticket->id );
			if ( ! $ticket->id ) {
				wp_send_json_error( 'Something went wrong!', 400 );
			}

			if ( $ticket->assigned_agent ) {
				return;
			}

			$general = get_option( 'wpsc-aar-general-settings' );
			if ( $general['auto-assign-agent'] == 0 ) {
				return;
			}

			$current_user = WPSC_Current_User::$current_user;
			if ( ! $current_user->is_agent ) {
				return;
			}

			$agent = new WPSC_Agent( $current_user->agent->id );

			if ( $agent ) {
				WPSC_Individual_Ticket::change_assignee( array(), array( $agent ), $current_user->customer->id );
			}
		}
	}
}

WPSC_Assign_Agent::init();
