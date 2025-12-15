<?php
if ( ! class_exists( 'WPSC_AAR_Action' ) ) {

	final class WPSC_AAR_Action {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// delete user from rules.
			add_action( 'wpsc_delete_agent', array( __CLASS__, 'delete_agent_from_rules' ) );
		}

		/**
		 * Delete user from rules
		 *
		 * @param WPSC_Agent $agent - Agent object.
		 * @return void
		 */
		public static function delete_agent_from_rules( $agent ) {

			$rules = get_option( 'wpsc-aar-rules', array() );

			foreach ( $rules as $id => $rule ) {

				$agents = isset( $rule['agents'] ) && strlen( $rule['agents'] ) ? explode( '|', $rule['agents'] ) : array();
				if ( ! $agents ) {
					continue;
				}

				$index = array_search( $agent->id, $agents );
				if ( $index === false ) {
					continue;
				}

				unset( $agents[ $index ] );

				$rule['agents'] = $agents ? implode( '|', $agents ) : '';
				$rules[ $id ]   = $rule;
			}

			update_option( 'wpsc-aar-rules', $rules );
		}
	}
}
WPSC_AAR_Action::init();
