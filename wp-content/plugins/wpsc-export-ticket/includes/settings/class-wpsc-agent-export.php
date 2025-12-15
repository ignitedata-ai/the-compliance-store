<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Agent_Export' ) ) :

	final class WPSC_Agent_Export {

		/**
		 * Ignore custom field types
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

			// User interface.
			add_action( 'wp_ajax_wpsc_get_agent_export_settings', array( __CLASS__, 'agent_export_settings' ) );

			// add and delete export items.
			add_action( 'wp_ajax_wpsc_get_add_agent_export_item', array( __CLASS__, 'get_add_agent_export_item' ) );
			add_action( 'wp_ajax_wpsc_set_add_agent_export_item', array( __CLASS__, 'set_add_agent_export_item' ) );
			add_action( 'wp_ajax_wpsc_delete_agent_export_item', array( __CLASS__, 'delete_agent_export_item' ) );

			// Edit.
			add_action( 'wp_ajax_wpsc_get_edit_agent_export_item', array( __CLASS__, 'get_edit_agent_export_item' ) );
			add_action( 'wp_ajax_wpsc_set_edit_agent_export_item', array( __CLASS__, 'set_edit_agent_export_item' ) );

			// ignore cft.
			add_action( 'init', array( __CLASS__, 'ignore_cft' ) );
		}

		/**
		 * Settings user interface
		 *
		 * @return void
		 */
		public static function agent_export_settings() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$agent_export = get_option( 'wpsc-agent-export-settings', array() );?>

			<table class="wpsc-agent-export wpsc-setting-tbl">
				<thead>
					<tr>
						<th><?php echo esc_attr( wpsc__( 'Field', 'supportcandy' ) ); ?></th>
						<th><?php echo esc_attr( wpsc__( 'Actions', 'supportcandy' ) ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $agent_export as $slug ) {
						$cf = WPSC_Custom_Field::get_cf_by_slug( $slug );
						if ( ! ( $cf && $cf->id ) || ! ( $cf->type::$is_ctf || $cf->type::$is_list ) ) {
							continue;
						}
						?>
						<tr>
							<td><?php echo esc_attr( $cf->name ); ?></td>
							<td>
								<a href="javascript:wpsc_get_edit_agent_export_item('<?php echo esc_attr( $slug ); ?>', '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_edit_agent_export_item' ) ); ?>');" class="wpsc-link"><?php echo esc_attr( wpsc__( 'Edit', 'supportcandy' ) ); ?></a> |
								<a href="javascript:wpsc_delete_agent_export_item('<?php echo esc_attr( $slug ); ?>', '<?php echo esc_attr( wp_create_nonce( 'wpsc_delete_agent_export_item' ) ); ?>');" class="wpsc-link"><?php echo esc_attr( wpsc__( 'Delete', 'supportcandy' ) ); ?></a>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<script>
				jQuery('table.wpsc-agent-export').DataTable({
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
										var data = { action: 'wpsc_get_add_agent_export_item', _ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_add_agent_export_item' ) ); ?>' };
										jQuery.post(
											supportcandy.ajax_url,
											data,
											function (response) {

												// Set to modal.
												jQuery( '.wpsc-modal-header' ).text( response.title );
												jQuery( '.wpsc-modal-body' ).html( response.body );
												jQuery( '.wpsc-modal-footer' ).html( response.footer );
												// Display modal.
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
			<?php
			do_action( 'wpsc_agent_export_settings' );
			wp_die();
		}

		/**
		 * Get add agent ticket list items modal UI
		 *
		 * @return void
		 */
		public static function get_add_agent_export_item() {

			if ( check_ajax_referer( 'wpsc_get_add_agent_export_item', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}
			$title = esc_attr( wpsc__( 'Add new', 'supportcandy' ) );

			$custom_fields = WPSC_Custom_Field::$custom_fields;

			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="frm-add-agent-export-items">
				<div class="wpsc-input-group field-type">
					<div class="label-container">
						<label for="">
							<?php echo esc_attr( wpsc__( 'Select field', 'supportcandy' ) ); ?> 
							<span class="required-char">*</span>
						</label>
					</div>
					<select multiple id="wpsc-select-agent-lt-items" name="cf_id[]">
					<?php
					foreach ( $custom_fields as $cf ) {

						if (
							class_exists( $cf->type ) &&
							in_array( $cf->field, WPSC_CF_Settings::$allowed_modules['export-tickets'] ) &&
							( $cf->type::$is_ctf || $cf->type::$is_list ) &&
							! in_array( $cf->type::$slug, self::$ignore_cft )
						) {
							?>
								<option value="<?php echo esc_attr( $cf->id ); ?>"><?php echo esc_attr( $cf->name ); ?></option>
								<?php
						}
					}
					?>
					</select>
					<script>
						jQuery('#wpsc-select-agent-lt-items').selectWoo({
							allowClear: false,
							placeholder: ""
						});
					</script>
				</div>
				<input type="hidden" name="action" value="wpsc_set_add_agent_export_item">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_add_agent_export_item' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_set_add_agent_export_item(this);">
				<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			do_action( 'wpsc_get_add_agent_export_item' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Set add new agent ticket list item
		 *
		 * @return void
		 */
		public static function set_add_agent_export_item() {

			if ( check_ajax_referer( 'wpsc_set_add_agent_export_item', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$ids = isset( $_POST['cf_id'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['cf_id'] ) ) ) : array();
			if ( ! $ids ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$agent_export = get_option( 'wpsc-agent-export-settings', array() );
			foreach ( $ids as $id ) {
				$cf = new WPSC_Custom_Field( $id );
				if ( ! $cf->id || ! ( $cf->type::$is_ctf || $cf->type::$is_list ) ) {
					continue;
				}
				if ( ! in_array( $cf->slug, $agent_export ) ) {
					$agent_export[] = $cf->slug;
				}
			}

			update_option( 'wpsc-agent-export-settings', $agent_export );
			wp_die();
		}

		/**
		 * Delete agent ticket list items
		 *
		 * @return void
		 */
		public static function delete_agent_export_item() {

			if ( check_ajax_referer( 'wpsc_delete_agent_export_item', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : 0;
			if ( ! $slug ) {
				wp_send_json_error( __( 'Unauthorized access', 'supportcandy' ), 401 );
			}

			$agent_export = get_option( 'wpsc-agent-export-settings', array() );

			$key = array_search( $slug, $agent_export );
			if ( $key !== false ) :
				unset( $agent_export[ $key ] );
				$agent_export = array_values( $agent_export );
				update_option( 'wpsc-agent-export-settings', $agent_export );
				do_action( 'delete_agent_export_item', $slug );
			endif;
			wp_die();
		}

		/**
		 * Get edit agent export list items modal UI
		 *
		 * @return void
		 */
		public static function get_edit_agent_export_item() {

			if ( check_ajax_referer( 'wpsc_get_edit_agent_export_item', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
			if ( ! $slug ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$cf = WPSC_Custom_Field::get_cf_by_slug( $slug );
			if ( ! $cf ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$title      = $cf->name;
			$list_items = get_option( 'wpsc-agent-export-settings', array() );

			// calculate load order.
			$offset     = array_search( $cf->slug, $list_items );
			$load_after = $offset == 0 ? '__TOP__' : $list_items[ $offset - 1 ];

			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="frm-edit-agent-export-items">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Load after', 'supportcandy' ) ); ?></label>
					</div>
					<select name="load-after" class="load-after">
						<option <?php selected( $load_after, '__TOP__', true ); ?> value="__TOP__">-- <?php echo esc_attr( wpsc__( 'TOP', 'supportcandy' ) ); ?> --</option>
						<?php
						foreach ( $list_items as $slug ) {
							$cff = WPSC_Custom_Field::get_cf_by_slug( $slug );
							if ( ! $cff || $cff == $cf ) {
								continue;
							}
							?>
							<option <?php selected( $load_after, $cff->slug, true ); ?> value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_attr( $cff->name ); ?></option>
							<?php
						}
						?>
						<option value="__END__">-- <?php echo esc_attr( wpsc__( 'END', 'supportcandy' ) ); ?> --</option>
					</select>
					<script>jQuery('select.load-after').selectWoo();</script>
				</div>
				<input type="hidden" name="action" value="wpsc_set_edit_agent_export_item">
				<input type="hidden" name="slug" value="<?php echo esc_attr( $cf->slug ); ?>">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_edit_agent_export_item' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_set_edit_agent_export_item(this);">
				<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			do_action( 'get_edit_agent_export_item' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response );
		}

		/**
		 * Set edit agent export list item
		 *
		 * @return void
		 */
		public static function set_edit_agent_export_item() {

			if ( check_ajax_referer( 'wpsc_set_edit_agent_export_item', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
			if ( ! $slug ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$cf = WPSC_Custom_Field::get_cf_by_slug( $slug );
			if ( ! $cf ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$load_after = isset( $_POST['load-after'] ) ? sanitize_text_field( wp_unslash( $_POST['load-after'] ) ) : '__END__';

			$list_items = get_option( 'wpsc-agent-export-settings', array() );

			// unset from list so that load after should work.
			$list_items = array_values( array_diff( $list_items, array( $cf->slug ) ) );

			// set load after.
			switch ( $load_after ) {

				case '__TOP__':
					$list_items = array_merge( array( $cf->slug ), $list_items );
					break;

				case '__END__':
					$list_items = array_merge( $list_items, array( $cf->slug ) );
					break;

				default:
					$load_after = WPSC_Custom_Field::get_cf_by_slug( $load_after );
					if ( ! $load_after || ! in_array( $load_after->slug, $list_items ) ) {
						wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
					}
					$offset     = array_search( $load_after->slug, $list_items ) + 1;
					$arr1       = array_slice( $list_items, 0, $offset );
					$arr2       = array_slice( $list_items, $offset );
					$list_items = array_merge( $arr1, array( $cf->slug ), $arr2 );
					break;
			}

			update_option( 'wpsc-agent-export-settings', $list_items );
			wp_die();
		}

		/**
		 * Set ignore cft for export tickets
		 *
		 * @return void
		 */
		public static function ignore_cft() {

			self::$ignore_cft = apply_filters(
				'wpsc_export_ignore_cft',
				array(
					'df_description',
				)
			);
		}
	}
endif;

WPSC_Agent_Export::init();
