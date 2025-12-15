<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EN_From' ) ) :

	final class WPSC_EN_From {

		/**
		 * Initialize the class
		 *
		 * @return void
		 */
		public static function init() {

			// add en_from to schema.
			add_action( 'wpsc_ticket_schema', array( __CLASS__, 'add_ticket_schema' ) );

			// replace "from email" in ticket notification.
			add_filter( 'wpsc_en_before_sending', array( __CLASS__, 'replace_from_email' ) );

			// ticket conditions.
			add_filter( 'wpsc_ticket_conditions', array( __CLASS__, 'load_ticket_condition' ) );
			add_action( 'wpsc_tc_print_operators', array( __CLASS__, 'tc_print_operators' ), 10, 2 );
			add_action( 'wpsc_tc_print_operand', array( __CLASS__, 'tc_print_operand' ), 10, 3 );
			add_filter( 'wpsc_tc_is_valid', array( __CLASS__, 'tc_is_valid' ), 10, 4 );
		}

		/**
		 * Add en_from schema for ticket
		 *
		 * @param array $schema - schema name.
		 * @return array
		 */
		public static function add_ticket_schema( $schema ) {

			$en_from_schema = array(
				'en_from' => array(
					'has_ref'          => false,
					'ref_class'        => '',
					'has_multiple_val' => false,
				),
			);

			return array_merge( $schema, $en_from_schema );
		}

		/**
		 * Replace "from email" of ticket notification with "en_from" if exists in ticket
		 *
		 * @param WPSC_Email_Notifications $en - en object.
		 * @return WPSC_Email_Notifications
		 */
		public static function replace_from_email( $en ) {

			$general = get_option( 'wpsc-ep-general-settings' );
			if ( $general['forwarding-as-from-email'] && $en->ticket->en_from ) {
				$en->from_email = $en->ticket->en_from;
				$en->reply_to   = $en->ticket->en_from;
			}

			// reply above.
			$general  = get_option( 'wpsc-ep-general-settings' );
			$en->body = $general['reply-above-text'] . $en->body;

			return $en;
		}

		/**
		 * Load ticket condition for forwarded from
		 *
		 * @param array $conditions - conditions filter array.
		 * @return array
		 */
		public static function load_ticket_condition( $conditions ) {

			$conditions['en_from'] = esc_attr__( 'Forwarded From (Email Piping)', 'wpsc-en' );
			return $conditions;
		}

		/**
		 * Print operator for forwarded from.
		 *
		 * @param string $slug - slug to check.
		 * @param array  $filter - preset condition.
		 * @return void
		 */
		public static function tc_print_operators( $slug, $filter ) {

			if ( $slug != 'en_from' ) {
				return;
			}

			?>
			<div class="item conditional">
				<select class="operator" onchange="wpsc_tc_get_operand(this, '<?php echo esc_attr( $slug ); ?>', '<?php echo esc_attr( wp_create_nonce( 'wpsc_tc_get_operand' ) ); ?>');">
					<option value=""><?php echo esc_attr( wpsc__( 'Compare As', 'supportcandy' ) ); ?></option>
					<option <?php isset( $filter['operator'] ) && selected( $filter['operator'], '=' ); ?> value="="><?php echo esc_attr( wpsc__( 'Equals', 'supportcandy' ) ); ?></option>
					<option <?php isset( $filter['operator'] ) && selected( $filter['operator'], 'IN' ); ?> value="IN"><?php echo esc_attr( wpsc__( 'Matches', 'supportcandy' ) ); ?></option>
					<option <?php isset( $filter['operator'] ) && selected( $filter['operator'], 'NOT IN' ); ?> value="NOT IN"><?php echo esc_attr( wpsc__( 'Not Matches', 'supportcandy' ) ); ?></option>
					<option <?php isset( $filter['operator'] ) && selected( $filter['operator'], 'LIKE' ); ?> value="LIKE"><?php echo esc_attr( wpsc__( 'Has Words', 'supportcandy' ) ); ?></option>
				</select>
			</div>
			<?php
		}

		/**
		 * Print operand for ticket conditions.
		 *
		 * @param string $slug - slug to check.
		 * @param string $operator - operator value.
		 * @param array  $filter - preset condition.
		 * @return void
		 */
		public static function tc_print_operand( $slug, $operator, $filter ) {

			if ( $slug != 'en_from' ) {
				return;
			}

			$value = isset( $filter['operand_val_1'] ) ? stripslashes( $filter['operand_val_1'] ) : '';
			if ( in_array( $operator, array( 'IN', 'NOT IN', 'LIKE' ) ) ) {

				?>
				<div class="item conditional operand single">
					<textarea class="operand_val_1" placeholder="<?php esc_attr_e( 'One condition per line!', 'supportcandy' ); ?>" style="width: 100%;"><?php echo esc_attr( $value ); ?></textarea>
				</div>
				<?php

			} else {

				?>
				<div class="item conditional operand single">
					<input 
						type="text" 
						class="operand_val_1"
						value="<?php echo esc_attr( $value ); ?>"
						autocomplete="off"/>
				</div>
				<?php
			}
		}

		/**
		 * Check whether condition for the forwarded from is valid
		 *
		 * @param boolean     $is_valid - filter value.
		 * @param string      $slug - slug to check.
		 * @param array       $condition - condition to check.
		 * @param WPSC_Ticket $ticket - ticket object on which condition to check.
		 * @return boolean
		 */
		public static function tc_is_valid( $is_valid, $slug, $condition, $ticket ) {

			if ( $slug != 'en_from' ) {
				return $is_valid;
			}

			$value = stripslashes( $ticket->en_from );
			$terms = array_filter(
				array_map(
					function ( $term ) {
						return strtolower( trim( $term ) );
					},
					explode( PHP_EOL, $condition['operand_val_1'] )
				)
			);

			switch ( $condition['operator'] ) {

				case '=':
					$is_valid = strtolower( trim( $condition['operand_val_1'] ) ) == $value ? true : false;
					break;

				case 'IN':
					$is_valid = false;
					foreach ( $terms as $term ) {
						if ( $term == $value ) {
							$is_valid = true;
							break;
						}
					}
					break;

				case 'NOT IN':
					foreach ( $terms as $term ) {
						if ( $term == $value ) {
							$is_valid = false;
							break;
						}
					}
					break;

				case 'LIKE':
					$is_valid = false;
					foreach ( $terms as $term ) {
						$index = strpos( $value, trim( stripslashes( $term ) ) );
						if ( is_numeric( $index ) ) {
							$is_valid = true;
							break;
						}
					}
					break;
			}

			return $is_valid;
		}
	}
endif;

WPSC_EN_From::init();
