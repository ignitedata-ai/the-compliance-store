<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_CR_Categories' ) ) :

	final class WPSC_CR_Categories {

		/**
		 * Initialize the class
		 *
		 * @return void
		 */
		public static function init() {

			// add canned reply category section.
			add_filter( 'wpsc_icons', array( __CLASS__, 'add_icons' ) );
			add_filter( 'wpsc_settings_page_sections', array( __CLASS__, 'add_settings_tab' ) );

			// List.
			add_action( 'wp_ajax_wpsc_get_cr_categories', array( __CLASS__, 'get_cr_categories' ) );

			// Add new.
			add_action( 'wp_ajax_wpsc_get_add_new_cr_category', array( __CLASS__, 'get_add_new_category' ) );
			add_action( 'wp_ajax_wpsc_set_add_cr_category', array( __CLASS__, 'set_add_cr_category' ) );

			// Edit.
			add_action( 'wp_ajax_wpsc_get_edit_cr_category', array( __CLASS__, 'get_edit_cr_category' ) );
			add_action( 'wp_ajax_wpsc_set_edit_cr_category', array( __CLASS__, 'set_edit_cr_category' ) );

			// Delete.
			add_action( 'wp_ajax_wpsc_get_delete_cr_category', array( __CLASS__, 'get_delete_cr_category' ) );
			add_action( 'wp_ajax_wpsc_set_delete_cr_category', array( __CLASS__, 'set_delete_cr_category' ) );
		}

		/**
		 * Add icon in icons library. This is used in canned reply categories section of settings.
		 *
		 * @param array $icons - icons list array.
		 * @return array
		 */
		public static function add_icons( $icons ) {

			$icons['save'] = file_get_contents( WPSC_CR_ABSPATH . 'asset/icons/save-solid.svg' ); // phpcs:ignore
			return $icons;
		}

		/**
		 * Canned reply category settings tab
		 *
		 * @param array $sections - array of setting tabs.
		 * @return array
		 */
		public static function add_settings_tab( $sections ) {

			$sections['canned-reply-categories'] = array(
				'slug'     => 'canned_reply_categories',
				'icon'     => 'save',
				'label'    => esc_attr__( 'Canned Reply Categories', 'wpsc-cr' ),
				'callback' => 'wpsc_get_cr_categories',
			);
			return $sections;
		}

		/**
		 * Load canned category list
		 */
		public static function get_cr_categories() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$categories = WPSC_CR_Category::find( array( 'items_per_page' => 0 ) )['results'];
			?>

			<div class="wpsc-setting-header">
				<h2><?php esc_attr_e( 'Canned Reply Categories', 'wpsc-cr' ); ?></h2>
			</div>
			<div class="wpsc-setting-section-body">

				<table class="wpsc-cr-categories wpsc-setting-tbl">
					<thead>
						<tr>
							<th><?php echo esc_attr( wpsc__( 'Name', 'supportcandy' ) ); ?></th>
							<th><?php echo esc_attr( wpsc__( 'Actions', 'supportcandy' ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $categories as $category ) {
							?>
							<tr>
								<td><?php echo esc_attr( $category->name ); ?></td>
								<td>
									<a href="javascript:wpsc_get_edit_cr_category(<?php echo esc_attr( $category->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_edit_cr_category' ) ); ?>');" class="wpsc-link"><?php echo esc_attr( wpsc__( 'Edit', 'supportcandy' ) ); ?></a>
									<?php
									if ( $category->id != 1 ) {
										?>
										| <a href="javascript:wpsc_get_delete_cr_category(<?php echo esc_attr( $category->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_delete_cr_category' ) ); ?>');" class="wpsc-link"><?php echo esc_attr( wpsc__( 'Delete', 'supportcandy' ) ); ?></a>
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
					jQuery('.wpsc-cr-categories').DataTable({
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
											var data = { action: 'wpsc_get_add_new_cr_category', _ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_add_new_cr_category' ) ); ?>' };
											jQuery.post(
												supportcandy.ajax_url,
												data,
												function (res) {

													jQuery( '.wpsc-modal-header' ).text( res.title );
													jQuery( '.wpsc-modal-body' ).html( res.body );
													jQuery( '.wpsc-modal-footer' ).html( res.footer );
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
			<?php
			wp_die();
		}

		/**
		 * Get add new category ui
		 *
		 * @return void
		 */
		public static function get_add_new_category() {

			if ( check_ajax_referer( 'wpsc_get_add_new_cr_category', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$title = esc_attr( wpsc__( 'Add new', 'supportcandy' ) );

			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="wpsc-frm-add-cr-category">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Name', 'supportcandy' ) ); ?></label>
						<span class="required-char">*</span>
					</div>
					<input name="label" type="text" autocomplete="off">
				</div>
				<?php do_action( 'wpsc_get_cr_add_category_body' ); ?>
				<input type="hidden" name="action" value="wpsc_set_add_cr_category">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_add_cr_category' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_set_add_cr_category(this);">
				<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			do_action( 'wpsc_get_add_cr_category_footer' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response, 200 );
		}

		/**
		 * Insert new category
		 *
		 * @return void
		 */
		public static function set_add_cr_category() {

			if ( check_ajax_referer( 'wpsc_set_add_cr_category', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$label = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : '';
			if ( ! $label ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$data     = array( 'name' => $label );
			$category = WPSC_CR_Category::insert( $data );
			wp_die();
		}

		/**
		 * Edit category modal
		 *
		 * @return void
		 */
		public static function get_edit_cr_category() {

			if ( check_ajax_referer( 'wpsc_get_edit_cr_category', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$title = esc_attr( wpsc__( 'Edit', 'supportcandy' ) );

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$category = new WPSC_CR_Category( $id );

			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="wpsc-frm-edit-cr-category">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Name', 'supportcandy' ) ); ?></label>
						<span class="required-char">*</span>
					</div>
					<input name="label" type="text" value="<?php echo esc_attr( $category->name ); ?>" autocomplete="off">
				</div>
				<?php do_action( 'wpsc_get_edit_cr_category_body', $id ); ?>
				<input type="hidden" name="id" value="<?php echo esc_attr( $category->id ); ?>">
				<input type="hidden" name="action" value="wpsc_set_edit_cr_category">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_edit_cr_category' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_set_edit_cr_category(this);">
				<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
			</button>
			<?php
			do_action( 'wpsc_get_edit_cr_category_footer' );
			$footer = ob_get_clean();

			$response = array(
				'title'  => $title,
				'body'   => $body,
				'footer' => $footer,
			);
			wp_send_json( $response, 200 );
		}

		/**
		 * Set edit category
		 *
		 * @return void
		 */
		public static function set_edit_cr_category() {

			if ( check_ajax_referer( 'wpsc_set_edit_cr_category', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$label = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : '';
			if ( ! $label ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$category       = new WPSC_CR_Category( $id );
			$category->name = $label;
			$category->save();
			wp_die();
		}

		/**
		 * Delete Category
		 */
		public static function get_delete_cr_category() {

			if ( check_ajax_referer( 'wpsc_get_delete_cr_category', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( in_array( $id, array( 0, 1 ) ) ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$title = esc_attr__( 'Delete canned reply category', 'supportcandy' );

			$category = new WPSC_CR_Category( $id );

			$categories = WPSC_CR_Category::find( array( 'items_per_page' => 0 ) )['results'];
			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="wpsc-frm-delete-cr-category">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Replace with', 'supportcandy' ); ?></label>
					</div>
					<select name="replace_id">
						<?php
						foreach ( $categories as $cat ) {
							if ( $cat->id == $category->id ) {
								continue;
							}
							?>
							<option value="<?php echo esc_attr( $cat->id ); ?>"><?php echo esc_attr( $cat->name ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<input type="hidden" name="id" value="<?php echo esc_attr( $category->id ); ?>">
				<input type="hidden" name="action" value="wpsc_set_delete_cr_category">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_delete_cr_category' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button class="wpsc-button small primary" onclick="wpsc_set_delete_cr_category(this);">
				<?php esc_attr_e( 'Submit', 'supportcandy' ); ?>
			</button>
			<button class="wpsc-button small secondary" onclick="wpsc_close_modal();">
				<?php esc_attr_e( 'Cancel', 'supportcandy' ); ?>
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
		 * Delete CR Category
		 */
		public static function set_delete_cr_category() {

			global $wpdb;

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			if ( check_ajax_referer( 'wpsc_set_delete_cr_category', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$cr_category = new WPSC_CR_Category( $id );
			if ( ! $cr_category->id ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$replace_id = isset( $_POST['replace_id'] ) ? intval( $_POST['replace_id'] ) : 0;
			if ( ! $replace_id ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$replace = new WPSC_CR_Category( $replace_id );
			if ( ! $replace->id ) {
				wp_send_json_error( __( 'Bad request!', 'supportcandy' ), 400 );
			}

			$canned_reply = WPSC_Canned_Reply_Model::find(
				array(
					'items_per_page' => 0,
					'meta_query'     => array(
						'relation' => 'OR',
						array(
							'slug'    => 'categories',
							'compare' => 'IN',
							'val'     => array( $cr_category->id ),
						),
					),
				)
			)['results'];
			foreach ( $canned_reply as $creply ) {

				$categories = $creply->categories;
				$index = array_search( $cr_category, $categories );
				unset( $categories[ $index ] );
				$categories = array_unique( array_merge( $categories, array( $replace ) ), SORT_REGULAR );
				$creply->categories = $categories;
				$creply->save();
			}

			$cr_category->destroy( $cr_category );

			wp_die();
		}
	}
endif;

WPSC_CR_Categories::init();
