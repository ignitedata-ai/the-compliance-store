<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Canned_Reply_Data' ) ) :

	final class WPSC_Canned_Reply_Data {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// Settings section.
			add_action( 'wp_ajax_wpsc_get_add_new_cr_admin', array( __CLASS__, 'get_add_new_cr_admin' ) );
			add_action( 'wp_ajax_wpsc_set_add_new_cr_admin', array( __CLASS__, 'set_add_cr_admin' ) );
			add_action( 'wp_ajax_wpsc_get_edit_cr_admin', array( __CLASS__, 'get_edit_cr_admin' ) );
			add_action( 'wp_ajax_wpsc_set_edit_cr_admin', array( __CLASS__, 'set_edit_cr_admin' ) );
			add_action( 'wp_ajax_wpsc_delete_cr_admin', array( __CLASS__, 'delete_canned_reply' ) );
		}

		/**
		 * Get add new canned reply
		 */
		public static function get_add_new_cr_admin() {

			if ( check_ajax_referer( 'wpsc_get_add_new_cr_admin', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$categories = WPSC_CR_Category::find( array( 'items_per_page' => 0 ) )['results'];
			$title      = esc_attr( wpsc__( 'Add new', 'supportcandy' ) );
			$unique_id  = uniqid( 'wpsc_' );?>
			<form action="#" onsubmit="return false;" class="wpsc-frm-add-canned-reply">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Title', 'supportcandy' ) ); ?></label>
						<span class="required-char">*</span>
					</div>
					<input name="title" type="text" autocomplete="off">
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Body', 'supportcandy' ) ); ?></label>
						<span class="required-char">*</span>
					</div>
					<textarea name="body" id="wpsc-cr-body" class="wpsc_textarea"></textarea>
					<div class="wpsc-it-editor-action-container">
						<div class="actions">
							<div class="wpsc-editor-actions">
								<span class="wpsc-link" onclick="wpsc_get_macros()"><?php echo esc_attr( wpsc__( 'Insert Macro', 'supportcandy' ) ); ?></span>
							</div>
							<div class="<?php echo esc_attr( $unique_id ); ?> wpsc-editor-attachment-container"></div>
						</div>
					</div>
					<?php
					$advanced  = get_option( 'wpsc-te-advanced' );
					$is_editor = false;
					$agent     = get_option( 'wpsc-te-agent' );
					$toolbox = array();
					if ( $agent['enable'] ) {
						$is_editor = true;
						foreach ( $agent['toolbar'] as $key => $value ) {
							$toolbox[] = $value;
							if ( in_array( $value, array( 'blockquote', 'alignright', 'numlist', 'rtl', 'wpsc_insert_editor_img' ) ) ) {
								$toolbox[] = '|';
							}
						}
					}
					?>
					<script>
						<?php
						$implode_toolbox = implode( ' ', $toolbox );
						if ( $is_editor ) :
							?>
							tinymce.remove('#wpsc-cr-body');
							tinymce.init({ 
								selector:'#wpsc-cr-body',
								body_id: 'wpsc-cr-body',
								menubar: false,
								statusbar: false,
								autoresize_min_height: 150,
								wp_autoresize_on: true,
								plugins: [
									'lists link directionality wpautoresize textcolor <?php echo esc_attr( $advanced['html-pasting'] ) == 1 ? 'paste' : ''; ?>'
								],
								image_advtab: true,
								toolbar: '<?php echo esc_attr( $implode_toolbox ); ?>',
								directionality: '<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>',
								branding: false,
								autoresize_bottom_margin: 20,
								browser_spellcheck : true,
								relative_urls : false,
								remove_script_host : false,
								convert_urls : true,
								setup: function (editor) {
									// Add a custom button
									editor.addButton('wpsc_insert_editor_img', {
										title : 'Insert/edit image',
										onclick : function() {
											// Add you own code to execute something on click
											wpsc_add_custom_image_tinymce(editor, '<?php echo esc_attr( wp_create_nonce( 'wpsc_add_custom_image_tinymce' ) ); ?>');
										}
									});
									editor.on('click', function (e) {
										if(e.target.nodeName === "IMG"){
											wpsc_edit_custom_image_tinymce(editor, e.target, '<?php echo esc_attr( wp_create_nonce( 'wpsc_edit_custom_image_tinymce' ) ); ?>');
										}
									});
								}
							});
							<?php
						endif;
						?>
					</script>
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Categories', 'wpsc-cr' ); ?></label>
						<span class="required-char">*</span>
					</div>
					<select class="<?php echo esc_attr( $unique_id ); ?>"multiple name="category[]">
						<option value=""></option>
						<?php
						foreach ( $categories as $category ) :
							?>
							<option value="<?php echo esc_attr( $category->id ); ?>"><?php echo esc_attr( $category->name ); ?></option>
							<?php
						endforeach;
						?>
					</select>
					<script>
						jQuery('select.<?php echo esc_attr( $unique_id ); ?>').selectWoo({
							allowClear: true,
							placeholder: ""
						});
					</script>
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Visibility', 'wpsc-cr' ); ?></label>
					</div>
					<select name="visibility">
						<option value="private"><?php esc_attr_e( 'Private', 'wpsc-cr' ); ?></option>
						<option value="public"><?php esc_attr_e( 'Public', 'wpsc-cr' ); ?></option>
					</select>
				</div>
				<?php do_action( 'wpsc_get_add_cr_admin_body' ); ?>
				<input type="hidden" name="action" value="wpsc_set_add_new_cr_admin">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_add_new_cr_admin' ) ); ?>">
			</form>
			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_set_add_new_cr_admin();">
					<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
				</button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_get_cr_admin();">
					<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
				</button>
			</div>
			<?php
			wp_die();
		}

		/**
		 * Save new canned reply
		 *
		 * @return void
		 */
		public static function set_add_cr_admin() {

			if ( check_ajax_referer( 'wpsc_set_add_new_cr_admin', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$current_user = WPSC_Current_User::$current_user;

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

			// visibility.
			$visibility = isset( $_POST['visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['visibility'] ) ) : '';
			if ( ! $visibility ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$now = new DateTime();
			WPSC_Canned_Reply_Model::insert(
				array(
					'title'        => $title,
					'author'       => $current_user->agent->id,
					'body'         => $body,
					'categories'   => implode( '|', $category ),
					'visibility'   => $visibility,
					'date_created' => $now->format( 'Y-m-d H:i:s' ),
				)
			);

			wp_die();
		}

		/**
		 * Get edit canned reply
		 */
		public static function get_edit_cr_admin() {

			if ( check_ajax_referer( 'wpsc_get_edit_cr_admin', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$canned_reply = new WPSC_Canned_Reply_Model( $id );
			if ( ! $canned_reply ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$categories = WPSC_CR_Category::find( array( 'items_per_page' => 0 ) )['results'];
			$title      = esc_attr( wpsc__( 'Add new', 'supportcandy' ) );
			$unique_id  = uniqid( 'wpsc_' );
			?>
			<form action="#" onsubmit="return false;" class="wpsc-frm-edit-canned-reply">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Title', 'supportcandy' ) ); ?></label>
						<span class="required-char">*</span>
					</div>
					<input name="title" type="text" value="<?php echo esc_attr( $canned_reply->title ); ?>" autocomplete="off">
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Body', 'supportcandy' ) ); ?></label>
						<span class="required-char">*</span>
					</div>
					<textarea name="body" id="wpsc-cr-body" class="wpsc_textarea"><?php echo wp_kses_post( $canned_reply->body ); ?></textarea>
					<div class="wpsc-it-editor-action-container">
						<div class="actions">
							<div class="wpsc-editor-actions">
								<span class="wpsc-link" onclick="wpsc_get_macros()"><?php echo esc_attr( wpsc__( 'Insert Macro', 'supportcandy' ) ); ?></span>
							</div>
							<div class="<?php echo esc_attr( $unique_id ); ?> wpsc-editor-attachment-container"></div>
						</div>
					</div>
					<?php
					$advanced  = get_option( 'wpsc-te-advanced' );
					$is_editor = false;
					$agent     = get_option( 'wpsc-te-agent' );
					$toolbox = array();
					if ( $agent['enable'] ) {
						$is_editor = true;
						foreach ( $agent['toolbar'] as $key => $value ) {
							$toolbox[] = $value;
							if ( in_array( $value, array( 'blockquote', 'alignright', 'numlist', 'rtl', 'wpsc_insert_editor_img' ) ) ) {
								$toolbox[] = '|';
							}
						}
					}
					?>
					<script>
						<?php
						$implode_toolbox = implode( ' ', $toolbox );
						if ( $is_editor ) :
							?>
							tinymce.remove('#wpsc-cr-body');
							tinymce.init({ 
								selector:'#wpsc-cr-body',
								body_id: 'wpsc-cr-body',
								menubar: false,
								statusbar: false,
								autoresize_min_height: 150,
								wp_autoresize_on: true,
								plugins: [
									'lists link directionality wpautoresize textcolor <?php echo esc_attr( $advanced['html-pasting'] ) == 1 ? 'paste' : ''; ?>'
								],
								image_advtab: true,
								toolbar: '<?php echo esc_attr( $implode_toolbox ); ?>',
								directionality: '<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>',
								branding: false,
								autoresize_bottom_margin: 20,
								browser_spellcheck : true,
								relative_urls : false,
								remove_script_host : false,
								convert_urls : true,
								setup: function (editor) {
									// Add a custom button.
									editor.addButton('wpsc_insert_editor_img', {
										title : 'Insert/edit image',
										onclick : function() {
											// Add you own code to execute something on click
											wpsc_add_custom_image_tinymce(editor, '<?php echo esc_attr( wp_create_nonce( 'wpsc_add_custom_image_tinymce' ) ); ?>');
										}
									});
									editor.on('click', function (e) {
										if(e.target.nodeName === "IMG"){
											wpsc_edit_custom_image_tinymce(editor, e.target, '<?php echo esc_attr( wp_create_nonce( 'wpsc_edit_custom_image_tinymce' ) ); ?>');
										}
									});
								}
							});
							<?php
						endif;
						?>
					</script>
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Categories', 'wpsc-cr' ); ?></label>
						<span class="required-char">*</span>
					</div>
					<select class="<?php echo esc_attr( $unique_id ); ?>"multiple name="category[]">
						<?php
						$val = array();
						foreach ( $canned_reply->categories as $category ) {
							$val[] = $category->id;
						}

						foreach ( $categories as $category ) {
							$selected = $val && in_array( $category->id, $val ) ? 'selected' : ''
							?>
							<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $category->id ); ?>"><?php echo esc_attr( $category->name ); ?></option>
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
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php esc_attr_e( 'Visibility', 'wpsc-cr' ); ?></label>
					</div>
					<select name="visibility">
						<option <?php selected( $canned_reply->visibility, 'private' ); ?> value="private"><?php esc_attr_e( 'Private', 'wpsc-cr' ); ?></option>
						<option <?php selected( $canned_reply->visibility, 'public' ); ?> value="public"><?php esc_attr_e( 'Public', 'wpsc-cr' ); ?></option>
					</select>
				</div>
				<?php do_action( 'wpsc_get_edit_cr_admin_body' ); ?>
				<input type="hidden" name="action" value="wpsc_set_edit_cr_admin">
				<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_edit_cr_admin' ) ); ?>">
			</form>
			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_set_edit_cr_admin();">
					<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?>
				</button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_get_cr_admin();">
					<?php echo esc_attr( wpsc__( 'Cancel', 'supportcandy' ) ); ?>
				</button>
			</div>
			<?php
			wp_die();
		}

		/**
		 * Save edit canned reply
		 *
		 * @return void
		 */
		public static function set_edit_cr_admin() {

			if ( check_ajax_referer( 'wpsc_set_edit_cr_admin', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			$canned_reply = new WPSC_Canned_Reply_Model( $id );
			if ( ! $canned_reply ) {
				wp_send_json_error( 'Bad request', 400 );
			}

			// title.
			$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			if ( ! $title ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			$canned_reply->title = $title;

			// body.
			$body = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
			if ( ! $body ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			$canned_reply->body = $body;

			// category.
			$category = isset( $_POST['category'] ) ? array_filter( array_map( 'intval', $_POST['category'] ) ) : array();
			if ( ! $category ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			$canned_reply->categories = implode( '|', $category );

			// visibility.
			$visibility = isset( $_POST['visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['visibility'] ) ) : '';
			if ( ! $visibility ) {
				wp_send_json_error( 'Bad request', 400 );
			}
			$canned_reply->visibility = $visibility;

			$canned_reply->save();

			wp_die();
		}

		/**
		 * Delete canned reply
		 *
		 * @return void
		 */
		public static function delete_canned_reply() {

			if ( check_ajax_referer( 'wpsc_delete_cr_admin', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
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

WPSC_Canned_Reply_Data::init();
