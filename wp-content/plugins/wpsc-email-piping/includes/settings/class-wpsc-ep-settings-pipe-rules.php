<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Settings_Pipe_Rules' ) ) :

	final class WPSC_EP_Settings_Pipe_Rules {

		/**
		 * Ignore custom field types to match for email piping rules
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

			// list.
			add_action( 'wp_ajax_wpsc_ep_get_pipe_rules', array( __CLASS__, 'get_pipe_rules' ) );

			// add new.
			add_action( 'wp_ajax_wpsc_ep_get_add_pipe_rule', array( __CLASS__, 'get_add_pipe_rule' ) );
			add_action( 'wp_ajax_wpsc_ep_set_add_pipe_rule', array( __CLASS__, 'set_add_pipe_rule' ) );

			// edit.
			add_action( 'wp_ajax_wpsc_ep_get_edit_pipe_rule', array( __CLASS__, 'get_edit_pipe_rule' ) );
			add_action( 'wp_ajax_wpsc_ep_set_edit_pipe_rule', array( __CLASS__, 'set_edit_pipe_rule' ) );

			// delete.
			add_action( 'wp_ajax_wpsc_ep_delete_pipe_rule', array( __CLASS__, 'delete_rule' ) );

			// sort.
			add_action( 'wp_ajax_wpsc_sort_ep_rules', array( __CLASS__, 'sort_rules' ) );
		}

		/**
		 * Set ignore custom field types for pipe rules
		 *
		 * @return void
		 */
		public static function set_ignore_cft_list() {

			self::$ignore_cft = apply_filters(
				'wpsc_ep_rules_ignore_cft',
				array(
					'df_id',
					'df_customer',
					'df_customer_name',
					'df_customer_email',
					'df_subject',
					'df_description',
					'df_assigned_agent',
					'df_date_created',
					'df_date_updated',
					'df_date_closed',
					'df_agent_created',
					'df_ip_address',
					'df_source',
					'df_last_reply_source',
					'df_browser',
					'df_os',
					'df_prev_assignee',
					'df_user_type',
					'cf_file_attachment_multiple',
					'cf_file_attachment_single',
					'cf_edd_order',
					'df_sla',
					'df_usergroups',
					'cf_woo_order',
					'cf_woo_subscription',
					'df_sf_rating',
					'df_sf_feedback',
					'df_sf_date',
					'df_time_spent',
					'cf_html',
					'df_last_reply_on',
					'df_last_reply_by',
					'cf_tutor_order',
					'cf_learnpress_order',
					'cf_lifter_order',
				)
			);
		}

		/**
		 * Get general settings
		 *
		 * @return void
		 */
		public static function get_pipe_rules() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$rules = get_option( 'wpsc-ep-pipe-rules', array() );

			if ( $rules ) :?>
				<div class="wpsc-setting-cards-container ui-sortable">
					<?php
					foreach ( $rules as $id => $rule ) :
						?>
						<div class="wpsc-setting-card" data-id="<?php echo esc_attr( $id ); ?>">
							<span class="wpsc-sort-handle action-btn"><?php WPSC_Icons::get( 'sort' ); ?></span>
							<span class="title"><?php echo esc_attr( $rule['title'] ); ?></span>
							<div class="actions">
								<span class="action-btn" onclick="wpsc_ep_get_edit_pipe_rule(<?php echo esc_attr( $id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_get_edit_pipe_rule' ) ); ?>');"><?php WPSC_Icons::get( 'edit' ); ?></span>
								<span class="action-btn" onclick="wpsc_ep_delete_pipe_rule(<?php echo esc_attr( $id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_delete_pipe_rule' ) ); ?>');"><?php WPSC_Icons::get( 'trash-alt' ); ?></span>
							</div>
						</div>
						<?php
					endforeach;
					?>
				</div>
				<?php
			else :
				?>
				<div style="margin-bottom: 15px;"><?php esc_attr_e( 'No rules found!', 'wpsc-ep' ); ?></div>
				<?php
			endif;
			?>

			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_ep_get_add_pipe_rule('<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_get_add_pipe_rule' ) ); ?>');">
					<?php echo esc_attr( wpsc__( 'Add new', 'supportcandy' ) ); ?>
				</button>
				<?php
				if ( $rules ) :
					?>
					<button 
						class="wpsc-save-sort-order wpsc-button normal secondary">
						<?php echo esc_attr( wpsc__( 'Save Order', 'supportcandy' ) ); ?></button>
					<?php
				endif;
				?>
			</div>

			<script>
				var items = jQuery( ".wpsc-setting-cards-container" ).sortable({ handle: '.wpsc-sort-handle' });
				jQuery(".wpsc-save-sort-order").click(function(){
					var slugs = items.sortable( "toArray", {attribute: 'data-id'} );
					var data = { action: 'wpsc_sort_ep_rules', slugs };
					jQuery(this).text(supportcandy.translations.please_wait);
					jQuery.post(supportcandy.ajax_url, data, function (res) {
						wpsc_ep_get_pipe_rules();
					});
				});
			</script>
			<?php

			wp_die();
		}

		/**
		 * Get add pipe modal
		 *
		 * @return void
		 */
		public static function get_add_pipe_rule() {

			if ( check_ajax_referer( 'wpsc_ep_get_add_pipe_rule', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$title = esc_attr( wpsc__( 'Add new', 'wpsc-ep' ) );

			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="wpsc-ep-add-rule">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php echo esc_attr( wpsc__( 'Title', 'supportcandy' ) ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<input type="text" name="title" autocomplete="off">
				</div>
				<?php do_action( 'wpsc_ep_get_add_pipe_rule' ); ?>
				<input type="hidden" name="action" value="wpsc_ep_set_add_pipe_rule">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_set_add_pipe_rule' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_ep_set_add_pipe_rule(this);">
				<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			do_action( 'wpsc_get_edit_agent_footer' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);

			wp_send_json( $response, 200 );
		}

		/**
		 * Set add new pipe rule
		 *
		 * @return void
		 */
		public static function set_add_pipe_rule() {

			if ( check_ajax_referer( 'wpsc_ep_set_add_pipe_rule', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			if ( ! $title ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$index = 1;
			$rules = get_option( 'wpsc-ep-pipe-rules', array() );
			if ( $rules ) {
				$last_index = max( array_keys( $rules ) );
				$index      = intval( $last_index ) + 1;
			}

			$rules[ $index ] = array(
				'title' => $title,
			);

			update_option( 'wpsc-ep-pipe-rules', $rules );

			$nonce = wp_create_nonce( 'wpsc_ep_get_edit_pipe_rule' );
			wp_send_json(
				array(
					'index' => $index,
					'nonce' => $nonce,
				),
				200
			);
		}

		/**
		 * Get edit pipe rule
		 *
		 * @return void
		 */
		public static function get_edit_pipe_rule() {

			if ( check_ajax_referer( 'wpsc_ep_get_edit_pipe_rule', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$rules = get_option( 'wpsc-ep-pipe-rules', array() );
			if ( ! isset( $rules[ $id ] ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$rule = $rules[ $id ];
			?>

			<form action="#" onsubmit="return false;" class="wpsc-ep-edit-rule">

				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php echo esc_attr( wpsc__( 'Title', 'supportcandy' ) ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<input type="text" name="title" value="<?php echo esc_attr( $rule['title'] ); ?>" autocomplete="off">
				</div>

				<div class="wpsc-accordion">
					<h3><?php esc_attr_e( 'Condition', 'wpsc-ep' ); ?></h3>
					<div>
						<div class="wpsc-sm-12 wpsc-md-12 wpsc-lg-12 wpsc-visible wpsc-xs-12">
							<?php $forwarding_address = isset( $rule['forwarding-address'] ) ? $rule['forwarding-address'] : array(); ?>
							<div class="wpsc-input-group">
								<div class="label-container">
									<label for=""><?php esc_attr_e( 'Forwarding email address (one per line)', 'wpsc-ep' ); ?></label>
								</div>
								<?php $forwarding_address = implode( PHP_EOL, $forwarding_address ); ?>
								<textarea name="forwarding-address" rows="5"><?php echo esc_attr( $forwarding_address ); ?></textarea>
							</div>
							<?php $from_address = isset( $rule['from-address'] ) ? $rule['from-address'] : array(); ?>
							<div class="wpsc-input-group">
								<div class="label-container">
									<label for=""><?php esc_attr_e( 'From email address (one per line)', 'wpsc-ep' ); ?></label>
								</div>
								<?php $from_address = implode( PHP_EOL, $from_address ); ?>
								<textarea name="from-address" rows="5"><?php echo esc_attr( $from_address ); ?></textarea>
							</div>

							<?php $has_words = isset( $rule['has-words'] ) ? $rule['has-words'] : array(); ?>
							<div class="wpsc-input-group">
								<div class="label-container">
									<label for=""><?php esc_attr_e( 'Has Words (one per line)', 'wpsc-ep' ); ?></label>
								</div>
								<?php $has_words = implode( PHP_EOL, $has_words ); ?>
								<textarea name="has-words" rows="5"><?php echo esc_attr( $has_words ); ?></textarea>
							</div>
						</div>
					</div>

					<!-- Ticket Fields -->
					<h3><?php echo esc_attr( wpsc__( 'Ticket Fields', 'supportcandy' ) ); ?></h3>
					<div>
						<div class="wpsc-sm-12 wpsc-md-12 wpsc-lg-12 wpsc-visible wpsc-xs-12">
							<div class="wpsc-epr-fields wpsc-epr-tf">
								<?php
								foreach ( WPSC_Custom_Field::$custom_fields as $cf ) {
									if ( $cf->field != 'ticket' || in_array( $cf->type::$slug, self::$ignore_cft ) ) {
										continue;
									}
									$value = isset( $rule[ $cf->slug ] ) ? $rule[ $cf->slug ] : '';
									$cf->type::print_cf_input( $cf, $value );
								}
								?>
							</div>
						</div>
					</div>

					<!-- Agentonly Fields -->
					<h3><?php echo esc_attr( wpsc__( 'Agentonly Fields', 'supportcandy' ) ); ?></h3>
					<div>
						<div class="wpsc-sm-12 wpsc-md-12 wpsc-lg-12 wpsc-visible wpsc-xs-12">
							<div class="wpsc-epr-fields wpsc-epr-aof">
								<?php
								$count = 0;
								foreach ( WPSC_Custom_Field::$custom_fields as $cf ) {
									if ( $cf->field != 'agentonly' || in_array( $cf->type::$slug, self::$ignore_cft ) ) {
										continue;
									}
									$value = isset( $rule[ $cf->slug ] ) ? $rule[ $cf->slug ] : '';
									$cf->type::print_cf_input( $cf, $value );
									++$count;
								}
								?>
							</div>
						</div>
					</div>

				</div>
				<script>jQuery('.wpsc-accordion').accordion({heightStyle: "content", collapsible: true, navigation: true});</script>

				<?php do_action( 'wpsc_ep_get_edit_pipe_rule' ); ?>

				<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>">
				<input type="hidden" name="action" value="wpsc_ep_set_edit_pipe_rule">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_ep_set_edit_pipe_rule' ) ); ?>">

			</form>

			<div class="setting-footer-actions">

				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_ep_set_edit_pipe_rule(this);">
					<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?></button> 

				<button class="wpsc-button normal secondary" onclick="wpsc_ep_get_pipe_rules();">
					<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?></button>

			</div>
			<?php

			wp_die();
		}

		/**
		 * Set edit pipe rule
		 *
		 * @return void
		 */
		public static function set_edit_pipe_rule() {

			if ( check_ajax_referer( 'wpsc_ep_set_edit_pipe_rule', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$rules = get_option( 'wpsc-ep-pipe-rules', array() );
			if ( ! isset( $rules[ $id ] ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$rule = $rules[ $id ];

			$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			if ( ! $title ) {
				wp_send_json_error( 'Bad Request', 400 );
			}
			$rule['title'] = $title;

			// forwarding addresses.
			$forwarding_address = isset( $_POST['forwarding-address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['forwarding-address'] ) ) : '';
			$rule['forwarding-address'] = array_unique( array_filter( array_map( 'sanitize_text_field', explode( PHP_EOL, $forwarding_address ) ) ) );

			// from addresses.
			$from_address = isset( $_POST['from-address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['from-address'] ) ) : '';
			$rule['from-address'] = array_unique( array_filter( array_map( 'sanitize_text_field', explode( PHP_EOL, $from_address ) ) ) );

			// has words.
			$has_words = isset( $_POST['has-words'] ) ? sanitize_textarea_field( wp_unslash( $_POST['has-words'] ) ) : '';
			$rule['has-words'] = array_unique( array_filter( array_map( 'sanitize_text_field', explode( PHP_EOL, $has_words ) ) ) );

			// custom fields.
			foreach ( WPSC_Custom_Field::$custom_fields as $cf ) {

				if ( in_array( $cf->type::$slug, self::$ignore_cft ) || ! in_array( $cf->field, array( 'ticket', 'agentonly' ) ) ) {
					continue;
				}

				$rule[ $cf->slug ] = $cf->type::get_cf_input_val( $cf );
			}

			$rules[ $id ] = $rule;
			update_option( 'wpsc-ep-pipe-rules', $rules );
			wp_die();
		}

		/**
		 * Delete email piping rule
		 *
		 * @return void
		 */
		public static function delete_rule() {

			if ( check_ajax_referer( 'wpsc_ep_delete_pipe_rule', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			$rules = get_option( 'wpsc-ep-pipe-rules', array() );
			if ( ! isset( $rules[ $id ] ) ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			unset( $rules[ $id ] );

			update_option( 'wpsc-ep-pipe-rules', $rules );

			wp_die();
		}

		/**
		 * Sort rules
		 *
		 * @return void
		 */
		public static function sort_rules() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$slugs = isset( $_POST['slugs'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['slugs'] ) ) ) : array(); //phpcs:ignore
			if ( ! $slugs ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$rules = get_option( 'wpsc-ep-pipe-rules', array() );

			if ( count( $slugs ) != count( $rules ) ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$temp = array();
			foreach ( $slugs as $id ) {
				if ( ! isset( $rules[ $id ] ) ) {
					wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
				}
				$temp[ $id ] = $rules[ $id ];
			}

			$rules_keys = array_keys( $rules );
			foreach ( $slugs as $slug ) {
				if ( ! in_array( $slug, $rules_keys ) ) {
					wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
				}
			}

			update_option( 'wpsc-ep-pipe-rules', $temp );

			wp_die();
		}
	}
endif;

WPSC_EP_Settings_Pipe_Rules::init();
