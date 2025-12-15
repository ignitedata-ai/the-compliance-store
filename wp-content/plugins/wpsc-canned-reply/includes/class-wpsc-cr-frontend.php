<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_CR_Frontend' ) ) :

	final class WPSC_CR_Frontend {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// load scripts & styles.
			add_action( 'wpsc_js_frontend', array( __CLASS__, 'frontend_scripts' ) );
			add_action( 'wpsc_css_frontend', array( __CLASS__, 'frontend_styles' ) );

			// get add canned reply in create ticket and individual ticket.
			add_action( 'wpsc_tff_editor_actions', array( __CLASS__, 'add_canned_reply_tab' ) );
			add_action( 'wpsc_it_editor_actions', array( __CLASS__, 'add_canned_reply_tab' ) );

			// get canned reply.
			add_action( 'wp_ajax_wpsc_get_canned_reply', array( __CLASS__, 'get_canned_reply' ) );
			add_action( 'wp_ajax_nopriv_wpsc_get_canned_reply', array( __CLASS__, 'get_canned_reply' ) );

			// add canned reply text in reply and create ticket section.
			add_action( 'wp_ajax_wpsc_add_cr_text', array( __CLASS__, 'add_cr_text' ) );
			add_action( 'wp_ajax_nopriv_wpsc_add_cr_text', array( __CLASS__, 'add_cr_text' ) );

			// add new canned reply.
			add_filter( 'wpsc_it_submit_actions', array( __CLASS__, 'add_it_canned_reply_tab' ), 10, 2 );

			// add new canned reply to individual ticket.
			add_action( 'wp_ajax_wpsc_it_add_new_canned_reply', array( __CLASS__, 'it_add_new_canned_reply' ) );
			add_action( 'wp_ajax_nopriv_wpsc_it_add_new_canned_reply', array( __CLASS__, 'it_add_new_canned_reply' ) );

			// save! new canned reply.
			add_action( 'wp_ajax_wpsc_it_set_new_canned_reply', array( __CLASS__, 'it_set_new_canned_reply' ) );
			add_action( 'wp_ajax_nopriv_wpsc_it_set_new_canned_reply', array( __CLASS__, 'it_set_new_canned_reply' ) );

			// delete canned reply.
			add_action( 'wp_ajax_wpsc_delete_canned_reply', array( __CLASS__, 'delete_canned_reply' ) );
			add_action( 'wp_ajax_wpsc_nopriv_delete_canned_reply', array( __CLASS__, 'delete_canned_reply' ) );
		}

		/**
		 * Backend scripts
		 *
		 * @return void
		 */
		public static function frontend_scripts() {

			echo file_get_contents( WPSC_CR_ABSPATH . 'asset/js/public.js' ) . PHP_EOL . PHP_EOL; // phpcs:ignore
		}

		/**
		 * Backend scripts
		 *
		 * @return void
		 */
		public static function frontend_styles() {

			if ( is_rtl() ) {
				echo file_get_contents( WPSC_CR_ABSPATH . 'asset/css/public-rtl.css' ) . PHP_EOL . PHP_EOL; // phpcs:ignore
			} else {
				echo file_get_contents( WPSC_CR_ABSPATH . 'asset/css/public.css' ) . PHP_EOL . PHP_EOL; // phpcs:ignore
			}
		}

		/**
		 * Add canned reply in create ticket and individual ticket
		 *
		 * @return void
		 */
		public static function add_canned_reply_tab() {

			$current_user = WPSC_Current_User::$current_user;
			if ( $current_user->is_agent ) {
				?>
				<span class="wpsc-link wpsc-canned-reply" onclick="wpsc_get_canned_reply('<?php echo esc_attr( wp_create_nonce( 'wpsc_get_canned_reply' ) ); ?>')"><?php esc_attr_e( 'Canned Reply', 'wpsc-cr' ); ?></span>
				<?php
			}
		}

		/**
		 * Get canned reply
		 *
		 * @return void
		 */
		public static function get_canned_reply() {

			$current_user = WPSC_Current_User::$current_user;

			if ( ! $current_user->is_agent ) {
				wp_send_json_error( 'Unauthorized', 400 );
			}

			$unique_id = uniqid();
			$args      = array(
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'slug'    => 'author',
						'compare' => '=',
						'val'     => $current_user->agent->id,
					),
					array(
						'slug'    => 'visibility',
						'compare' => '=',
						'val'     => 'public',
					),
				),
			);

			$title        = esc_attr__( 'Canned Reply', 'wpsc-cr' );
			$canned_reply = WPSC_Canned_Reply_Model::find( $args )['results'];

			ob_start();
			?>
			<div style="width: 100%;">
				<table class="wpsc-canned-reply wpsc-setting-tbl">
					<thead>
						<tr>
							<th><?php echo esc_attr( wpsc__( 'Title', 'supportcandy' ) ); ?></th>
							<th><?php esc_attr_e( 'Categories', 'wpsc-cr' ); ?></th>
							<th><?php echo esc_attr( wpsc__( 'Actions', 'supportcandy' ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $canned_reply as $creply ) {

							$cat_names = array();
							foreach ( $creply->categories as $category ) {
								$cat_names[] = $category->name;
							}
							?>
							<tr>
								<td class="wpsc-link" onclick="wpsc_add_cr_text(<?php echo esc_attr( $creply->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_add_cr_text' ) ); ?>')"><?php echo esc_attr( $creply->title ); ?></td>
								<td>
									<?php
										$cat_names = implode( ', ', $cat_names );
										echo esc_attr( $cat_names );
									?>
								</td>
								<td>
									<?php
									if ( $current_user->agent->id == $creply->author->id || WPSC_Functions::is_site_admin() ) :
										?>
										<a href="javascript:wpsc_delete_canned_reply(<?php echo esc_attr( $creply->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_delete_canned_reply' ) ); ?>' )"><?php echo esc_attr( wpsc__( 'Delete', 'supportcandy' ) ); ?></a>
										<?php
									endif;
									?>
								</td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
			</div>
			<script>
				var table = jQuery('table.wpsc-canned-reply').DataTable({
					ordering: false,
					pageLength: 20,
					bLengthChange: false,
					columnDefs: [ 
						{ targets: -1, searchable: false },
						{ targets: '_all', className: 'dt-left' }
					],
					language: supportcandy.translations.datatables
				});
				jQuery(document).ready(function() {
					jQuery('div.dt-search input', table.table().container()).focus();
				} );

			</script>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
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
			wp_send_json( $response, 200 );
		}

		/**
		 * Add canned reply in reply section and new create ticket
		 *
		 * @return void
		 */
		public static function add_cr_text() {

			if ( check_ajax_referer( 'wpsc_add_cr_text', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$current_user = WPSC_Current_User::$current_user;

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

			if ( ! $id || ! $current_user->is_agent ) {
				wp_send_json_error( __( 'Unauthorized', 'supportcandy' ), 401 );
			}

			$is_editor = isset( $_POST['is_editor'] ) ? intval( $_POST['is_editor'] ) : 0;

			$canned_reply = new WPSC_Canned_Reply_Model( $id );
			if ( ! $canned_reply->id ) {
				wp_send_json_error( 'Bad Request', 400 );
			}

			if ( $is_editor ) {
				echo wp_kses_post( wpautop( $canned_reply->body, false ) );
			} else {
				$str = str_replace(
					array(
						'<br>',
						'<br/>',
						'<br />',
					),
					"\n",
					$canned_reply->body
				);
				echo esc_html( $str );
			}
			wp_die();
		}

		/**
		 * Add individual ticket sumbit action
		 *
		 * @param array       $submit_actions - action.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @return string
		 */
		public static function add_it_canned_reply_tab( $submit_actions, $ticket ) {

			if ( WPSC_Individual_Ticket::has_ticket_cap( 'reply' ) ) {
				$submit_actions['Canned-reply'] = array(
					'icon'     => 'save',
					'label'    => esc_attr__( 'Canned Reply', 'wpsc-cr' ),
					'callback' => 'wpsc_it_add_new_canned_reply(\'' . wp_create_nonce( 'wpsc_it_add_new_canned_reply' ) . '\');',
				);
			}

			return $submit_actions;
		}

		/**
		 * Get add new canned reply
		 *
		 * @return void
		 */
		public static function it_add_new_canned_reply() {

			if ( check_ajax_referer( 'wpsc_it_add_new_canned_reply', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$current_user = WPSC_Current_User::$current_user;

			if ( ! $current_user->is_agent ) {
				wp_send_json_error( __( 'Unauthorized', 'supportcandy' ), 401 );
			}

			$title      = esc_attr( wpsc__( 'Add new', 'supportcandy' ) );
			$categories = WPSC_CR_Category::find( array( 'items_per_page' => 0 ) )['results'];
			$unique_id  = uniqid( 'wpsc_' );

			ob_start();
			?>
			<form action="#" onsubmit="return false;" class="wpsc-frm-add-it-cr">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Title', 'supportcandy' ) ); ?></label>
						<span class="required-char">*</span>
					</div>
					<input name="title" type="text" autocomplete="off">
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Categories', 'wpsc-cr' ); ?></label>
						<span class="required-char">*</span>
					</div>
					<select class="<?php echo esc_attr( $unique_id ); ?>" multiple name="category[]">
						<option value=""></option>
						<?php
						foreach ( $categories as $category ) {
							?>
							<option value="<?php echo esc_attr( $category->id ); ?>"><?php echo esc_attr( $category->name ); ?></option>
							<?php
						}
						?>
					</select>
					<script>
						jQuery('select.<?php echo esc_attr( $unique_id ); ?>').selectWoo({
							allowClear: true,
							placeholder: ""
						});
					</script>
				</div>
				<input type="hidden" name="action" value="wpsc_it_set_new_canned_reply">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_it_set_new_canned_reply' ) ); ?>">
			</form>
			<?php
			$body = ob_get_clean();

			ob_start();
			?>
			<button 
				class="wpsc-button normal primary margin-right"
				onclick="wpsc_it_set_new_canned_reply(this);">
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
			wp_send_json( $response, 200 );
		}

		/**
		 * Save new canned reply
		 *
		 * @return void
		 */
		public static function it_set_new_canned_reply() {

			if ( check_ajax_referer( 'wpsc_it_set_new_canned_reply', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorised request!', 401 );
			}

			$current_user = WPSC_Current_User::$current_user;

			if ( ! $current_user->is_agent ) {
				wp_send_json_error( 'Unauthorisezed', 400 );
			}

			// title.
			$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			if ( ! $title ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			// body.
			$body = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
			if ( ! $body ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			// category.
			$category = isset( $_POST['category'] ) ? array_filter( array_map( 'intval', $_POST['category'] ) ) : array();
			if ( ! $category ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$now = new DateTime();
			WPSC_Canned_Reply_Model::insert(
				array(
					'title'        => $title,
					'author'       => $current_user->agent->id,
					'body'         => $body,
					'categories'   => implode( '|', $category ),
					'visibility'   => 'private',
					'date_created' => $now->format( 'Y-m-d H:i:s' ),
				)
			);

			wp_die();
		}

		/**
		 * Delete canned reply
		 *
		 * @return void
		 */
		public static function delete_canned_reply() {

			if ( check_ajax_referer( 'wpsc_delete_canned_reply', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			$current_user = WPSC_Current_User::$current_user;
			if ( ! $current_user->is_agent ) {
				wp_send_json_error( __( 'Unauthorized', 'supportcandy' ), 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$canned_reply = new WPSC_Canned_Reply_Model( $id );
			if ( ! $canned_reply->id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			WPSC_Canned_Reply_Model::destroy( $canned_reply );

			wp_die();
		}
	}
endif;

WPSC_CR_Frontend::init();
