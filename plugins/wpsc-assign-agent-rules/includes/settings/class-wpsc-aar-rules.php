<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_AAR_Rules' ) ) :

	final class WPSC_AAR_Rules {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// filter conditions.
			add_filter( 'wpsc_aar_conditions', array( __CLASS__, 'filter_conditions' ) );

			// setting actions.
			add_action( 'wp_ajax_wpsc_aar_get_rules', array( __CLASS__, 'get_aar_rules' ) );

			// add new.
			add_action( 'wp_ajax_wpsc_aar_get_add_rule', array( __CLASS__, 'get_add_rule' ) );
			add_action( 'wp_ajax_wpsc_aar_set_add_rule', array( __CLASS__, 'set_add_rule' ) );

			// edit.
			add_action( 'wp_ajax_wpsc_aar_get_edit_rule', array( __CLASS__, 'get_edit_rule' ) );
			add_action( 'wp_ajax_wpsc_aar_set_edit_rule', array( __CLASS__, 'set_edit_rule' ) );

			// delete.
			add_action( 'wp_ajax_wpsc_aar_delete_rule', array( __CLASS__, 'delete_rule' ) );
		}

		/**
		 * Get Assign agent rules
		 *
		 * @return void
		 */
		public static function get_aar_rules() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$rules = get_option( 'wpsc-aar-rules', array() );
			?>
			<div class="wpsc-setting-cards-container">
				<div style="width: 100%;">
					<table class="agent-rule-list-table wpsc-setting-tbl">
						<thead>
							<tr>
								<th><?php echo esc_attr( wpsc__( 'Name', 'supportcandy' ) ); ?></th>
								<th><?php echo esc_attr( wpsc__( 'Actions', 'supportcandy' ) ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( $rules ) {
								foreach ( $rules as $id => $rule ) {
									?>
									<tr>
										<td>
											<span class="title"><?php echo esc_attr( $rule['title'] ); ?></span>
										</td>
										<td>
											<a href="javascript:wpsc_aar_get_edit_rule(<?php echo esc_attr( $id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_aar_get_edit_rule' ) ); ?>');"><?php echo esc_attr( wpsc__( 'Edit', 'supportcandy' ) ); ?></a> |
											<a href="javascript:wpsc_aar_delete_rule(<?php echo esc_attr( $id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_aar_delete_rule' ) ); ?>');"><?php echo esc_attr( wpsc__( 'Delete', 'supportcandy' ) ); ?></a>
										</td>
									</tr>
									<?php
								}
							}
							?>
						</tbody>
					</table>
					<script>
						jQuery('table.agent-rule-list-table').DataTable({
							ordering: false,
							pageLength: 20,
							bLengthChange: false,
							columnDefs: [ 
								{ targets: -1, searchable: false },
								{ targets: '_all', className: 'dt-left' }
							],
							layout: {
								topStart: {
									buttons: [
										{
											text: '<?php echo esc_attr( wpsc__( 'Add new', 'supportcandy' ) ); ?>',
											className: 'wpsc-button small primary',
											action: function ( e, dt, node, config ) {
												wpsc_show_modal();
												var data = { action: 'wpsc_aar_get_add_rule', _ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_aar_get_add_rule' ) ); ?>' };
												jQuery.post(
													supportcandy.ajax_url,
													data,
													function (response) {

														jQuery( '.wpsc-modal-header' ).text( response.title );
														jQuery( '.wpsc-modal-body' ).html( response.body );
														jQuery( '.wpsc-modal-footer' ).html( response.footer );
														wpsc_show_modal_inner_container();
													}
												);
											}
										}
									],
								},
							},
							language: supportcandy.translations.datatables
						});
					</script>
				</div>
			</div>
			<?php
			wp_die();
		}

		/**
		 * Get add rule popup
		 *
		 * @return void
		 */
		public static function get_add_rule() {

			if ( check_ajax_referer( 'wpsc_aar_get_add_rule', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$title = esc_attr__( 'Add New Rule', 'wpsc-aar' );

			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="wpsc-aar-add-rule">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php echo esc_attr( wpsc__( 'Title', 'supportcandy' ) ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<input type="text" name="title" autocomplete="off">
				</div>
				<?php do_action( 'wpsc_aar_get_add_rule' ); ?>
				<input type="hidden" name="action" value="wpsc_aar_set_add_rule">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_aar_set_add_rule' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_aar_set_add_rule(this);">
				<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			do_action( 'wpsc_aar_get_add_rule' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response, 200 );
		}

		/**
		 * Set add rule
		 *
		 * @return void
		 */
		public static function set_add_rule() {

			if ( check_ajax_referer( 'wpsc_aar_set_add_rule', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( ( $_POST['title'] ) ) ) : '';
			if ( ! $title ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$index = 1;
			$rules = get_option( 'wpsc-aar-rules', array() );
			if ( $rules ) {
				end( $rules );
				$last_index = key( $rules );
				reset( $rules );
				$index = intval( $last_index ) + 1;
			}

			$rules[ $index ] = array(
				'title'      => $title,
				'conditions' => '',
			);

			$nonce = wp_create_nonce( 'wpsc_aar_get_edit_rule' );

			update_option( 'wpsc-aar-rules', $rules );
			wp_send_json(
				array(
					'index' => $index,
					'nonce' => $nonce,
				),
				200
			);
		}

		/**
		 * Get edit rule
		 *
		 * @return void
		 */
		public static function get_edit_rule() {

			if ( check_ajax_referer( 'wpsc_aar_get_edit_rule', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$rules = get_option( 'wpsc-aar-rules', array() );
			if ( ! isset( $rules[ $id ] ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$rule = $rules[ $id ];
			?>

			<form action="#" onsubmit="return false;" class="wpsc-aar-edit-rule">

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php echo esc_attr( wpsc__( 'Title', 'supportcandy' ) ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<input type="text" name="title" value="<?php echo esc_attr( $rule['title'] ); ?>" autocomplete="off">
				</div>

				<?php WPSC_Ticket_Conditions::print( 'conditions', 'wpsc_aar_conditions', $rule['conditions'], true ); ?>

				<?php $agents = isset( $rule['agents'] ) && strlen( $rule['agents'] ) ? explode( '|', $rule['agents'] ) : array(); ?>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Agents', 'wpsc-aar' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>    
					<div class="aar_edit_agent_opt">
						<?php esc_attr_e( 'Select Agents', 'wpsc-aar' ); ?>
						<select name="agents[]" class="agents" multiple>
							<?php
							if ( $agents ) {
								foreach ( $agents as $agent_id ) {
									$agent = new WPSC_Agent( $agent_id );
									?>
									<option selected value="<?php echo esc_attr( $agent->id ); ?>"><?php echo esc_attr( $agent->name ); ?></option>
									<?php
								}
							}
							?>
						</select>
						<?php
						$assign_method = isset( $rule['assign_method'] ) ? esc_attr( $rule['assign_method'] ) : 'assign_all';
						?>
						<div class="aar_edit_agent_radio">
							<div class="radio-container">
								<input type="radio" <?php checked( $assign_method, 'assign_all' ); ?> id="assign_all_agents" name="assign_agent_method" value="assign_all">
								<label for="assign_all_agents"><?php esc_attr_e( 'Assign all of above', 'wpsc-aar' ); ?></label>
							</div>
							<div class="radio-container">
								<?php
								$checked = ( $assign_method == 'cwh_agent' || $assign_method == 'mw_agent' ) ? 'checked' : ''
								?>
								<input type="radio" <?php echo esc_attr( $checked ); ?> id="assign_one_agent" name="assign_agent_method" value="assign_one">
								<label for="assign_one_agent"><?php esc_attr_e( 'Assign one of above', 'wpsc-aar' ); ?></label>
							</div>
							<div class="radio-container radio-sub-option">
								<input type="radio" <?php checked( $assign_method, 'cwh_agent' ); ?> id="assign_cwh_agent" name="assign_agent_cwh_mw" value="cwh_agent">
								<label for="assign_cwh_agent"><?php esc_attr_e( 'Closest working hours', 'wpsc-aar' ); ?></label>
							</div>
							<div class="radio-container radio-sub-option">
								<input type="radio" <?php checked( $assign_method, 'mw_agent' ); ?> id="assign_mw_agent" name="assign_agent_cwh_mw" value="mw_agent">
								<label for="assign_mw_agent"><?php esc_attr_e( 'Minimum workload', 'wpsc-aar' ); ?></label>
							</div>
						</div>
					</div>
				</div>

				<script>
					jQuery('.aar_edit_agent_radio :radio').on('change', function(){
						selected_value = jQuery(this).val();
						if(selected_value == 'cwh_agent' || selected_value == 'mw_agent'){
							jQuery('#assign_one_agent').prop("checked", true);
						}else if(selected_value == 'assign_one'){
							jQuery('#assign_cwh_agent').prop("checked", true);
						}else if(selected_value == 'assign_all'){
							jQuery('#assign_cwh_agent').prop("checked", false);
							jQuery('#assign_mw_agent').prop("checked", false);
						}
					});
					// agents autocomplete
					jQuery('select.agents').selectWoo({
						ajax: {
							url: supportcandy.ajax_url,
							dataType: 'json',
							delay: 250,
							data: function (params) {
								return {
									q: params.term, // search term
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
						escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
						minimumInputLength: 0,
						allowClear: false,
					});
				</script>
				<?php do_action( 'wpsc_arr_get_edit_rule', $rule ); ?>
				<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>">
				<input type="hidden" name="action" value="wpsc_aar_set_edit_rule">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_aar_set_edit_rule' ) ); ?>">

			</form>

			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary"
					onclick="wpsc_aar_set_edit_rule(this);">
					<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
				</button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_aar_get_rules();">
					<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
				</button>
			</div>
			<?php

			wp_die();
		}

		/**
		 * Set edit policy
		 *
		 * @return void
		 */
		public static function set_edit_rule() {

			if ( check_ajax_referer( 'wpsc_aar_set_edit_rule', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$rules = get_option( 'wpsc-aar-rules', array() );
			if ( ! isset( $rules[ $id ] ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}
			$rule = $rules[ $id ];

			$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			if ( ! $title ) {
				wp_send_json_error( 'Bad Request', 400 );
			}
			$rule['title'] = $title;

			// conditions.
			$conditions = isset( $_POST['conditions'] ) ? sanitize_text_field( wp_unslash( $_POST['conditions'] ) ) : '';
			if ( ! $conditions || $conditions == '[]' || ! WPSC_Ticket_Conditions::is_valid_input_conditions( 'wpsc_aar_conditions', $conditions ) ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			$rule['conditions'] = $conditions;

			$assign_method = isset( $_POST['assign_agent_method'] ) ? sanitize_text_field( wp_unslash( $_POST['assign_agent_method'] ) ) : '';
			if ( ! $assign_method ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			if ( $assign_method == 'assign_one' ) {
				$assign_method = isset( $_POST['assign_agent_cwh_mw'] ) ? sanitize_text_field( wp_unslash( $_POST['assign_agent_cwh_mw'] ) ) : 'cwh_agent';
			}
			$rule['assign_method'] = $assign_method;

			$agents         = isset( $_POST['agents'] ) ? array_filter( array_map( 'intval', $_POST['agents'] ) ) : array();
			$rule['agents'] = $agents ? implode( '|', $agents ) : '';

			$rule = apply_filters( 'wpsc_set_edit_rule_data', $rule );

			if ( ! ( array_key_exists( 'agents', $rule ) || array_key_exists( 'agentgroups', $rule ) ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$rules[ $id ] = $rule;
			do_action( 'wpsc_arr_after_set_edit_rule', $id );

			update_option( 'wpsc-aar-rules', $rules );
			wp_die();
		}

		/**
		 * Delete rule
		 *
		 * @return void
		 */
		public static function delete_rule() {

			if ( check_ajax_referer( 'wpsc_aar_delete_rule', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$rules = get_option( 'wpsc-aar-rules', array() );
			if ( ! isset( $rules[ $id ] ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			unset( $rules[ $id ] );
			update_option( 'wpsc-aar-rules', $rules );
			wp_die();
		}

		/**
		 * Filter conditions for email templates
		 *
		 * @param array $conditions - all possible ticket conditions.
		 * @return array
		 */
		public static function filter_conditions( $conditions ) {

			$ignore_list = apply_filters(
				'wpsc_aar_conditions_ignore_list',
				array(
					'cft'   => array( // custom field types.
						'df_id',
						'df_assigned_agent',
						'df_date_created',
						'df_date_updated',
						'df_date_closed',
						'df_ip_address',
						'df_source',
						'df_last_reply_source',
						'df_browser',
						'df_os',
						'df_prev_assignee',
						'cf_file_attachment_multiple',
						'cf_file_attachment_single',
						'df_last_reply_on',
						'df_last_reply_by',
						'cf_woo_order',
						'cf_woo_subscription',
						'cf_edd_order',
						'cf_tutor_order',
						'cf_learnpress_order',
						'cf_lifter_order',
					),
					'other' => array(), // other(custom) condition slug.
				)
			);

			foreach ( $conditions as $slug => $item ) {

				if ( $item['type'] == 'cf' ) {

					$cf = WPSC_Custom_Field::get_cf_by_slug( $slug );
					if ( in_array( $cf->type::$slug, $ignore_list['cft'] ) ) {
						unset( $conditions[ $slug ] );
					}
				} elseif ( in_array( $slug, $ignore_list['other'] ) ) {
					unset( $conditions[ $slug ] );
				}
			}

			return $conditions;
		}
	}
endif;

WPSC_AAR_Rules::init();
