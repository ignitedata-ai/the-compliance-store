<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Usertype_Warning_Setting' ) ) :

	final class WPSC_EP_Usertype_Warning_Setting extends WPSC_Email_Notifications {

		/**
		 * Initialization
		 *
		 * @return void
		 */
		public static function init() {

			// setting options.
			add_action( 'wp_ajax_wpsc_allowed_usertype_warning', array( __CLASS__, 'allowed_usertype_warning' ) );
			add_action( 'wp_ajax_wpsc_set_usertype_email_warning', array( __CLASS__, 'save_settings' ) );
			add_action( 'wp_ajax_wpsc_reset_ep_warning', array( __CLASS__, 'reset_settings' ) );

			// User reject pipe email template and send email on below action.
			add_action( 'wpsc_ep_reject_user_pipe', array( __CLASS__, 'reject_user_pipe_email' ) );
		}

		/**
		 * Reset settings
		 *
		 * @return void
		 */
		public static function reset() {

			$wpsc_warning_mail_message = '<p>Hello there,</p><p>The ticket can not be created for non-registered users!</p>';
			$ep_warning_mail_settings  = apply_filters(
				'wpsc_ep_usertype_warning_email',
				array(
					'email-warning-message' => $wpsc_warning_mail_message,
					'editor'                => 'html',
					'enable'                => 1,
				)
			);
			update_option( 'wpsc-ep-usertype-warning-email', $ep_warning_mail_settings );
		}

		/**
		 * Get general settings
		 *
		 * @return void
		 */
		public static function allowed_usertype_warning() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$settings = get_option( 'wpsc-ep-usertype-warning-email', array() ); ?>
			<form action="#" onsubmit="return false;" class="wpsc-ep-warning-email">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Enable', 'supportcandy' ) ); ?></label>
					</div>
					<select name="enable">
						<option <?php selected( $settings['enable'], 1 ); ?> value="1"><?php echo esc_attr( wpsc__( 'Yes', 'supportcandy' ) ); ?></option>
						<option <?php selected( $settings['enable'], 0 ); ?> value="0"><?php echo esc_attr( wpsc__( 'No', 'supportcandy' ) ); ?></option>
					</select>
				</div>
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="">
							<?php esc_attr_e( 'Email body', 'wpsc-ep' ); ?>
							<span class="required-char">*</span>
						</label>
					</div>
					<div class = "textarea-container ">
						<div class = "wpsc_tinymce_editor_btns">
							<div class="inner-container">
								<button class="visual wpsc-switch-editor <?php echo esc_attr( $settings['editor'] ) == 'html' ? 'active' : ''; ?>" type="button" onclick="wpsc_get_tinymce(this, 'email-warning-message','email_warning_message_body');"><?php echo esc_attr( wpsc__( 'Visual', 'supportcandy' ) ); ?></button>
								<button class="text wpsc-switch-editor <?php echo esc_attr( $settings['editor'] ) == 'text' ? 'active' : ''; ?>" type="button" onclick="wpsc_get_textarea(this, 'email-warning-message')"><?php echo esc_attr( wpsc__( 'Text', 'supportcandy' ) ); ?></button>
							</div>
						</div>
						<?php
						$ut_warning = WPSC_Translations::get( 'wpsc-ep-usertype-warning-message', stripslashes( $settings['email-warning-message'] ) );
						?>
						<textarea name="email-warning-message" id="email-warning-message" class="wpsc_textarea"><?php echo esc_attr( $ut_warning ); ?></textarea>
					</div>
					<script>
						<?php
						if ( $settings['editor'] == 'html' ) {
							?>
							jQuery('.wpsc-switch-editor.visual').trigger('click');
							<?php
						} else {
							?>
							jQuery('.wpsc-switch-editor.text').trigger('click');
							<?php
						}
						?>

						/**
						 * Switch to editor
						 */
						function wpsc_get_tinymce(el, selector, body_id){
							jQuery(el).parent().find('.text').removeClass('active');
							jQuery(el).addClass('active');
							tinymce.remove();
							tinymce.init({ 
								selector:'#'+selector,
								body_id: body_id,
								menubar: false,
								statusbar: false,
								height : '200',
								plugins: [
								'lists link image directionality paste'
								],
								image_advtab: true,
								toolbar: 'bold italic underline blockquote | alignleft aligncenter alignright | bullist numlist | rtl | link image',
								directionality: '<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>',
								branding: false,
								autoresize_bottom_margin: 20,
								browser_spellcheck : true,
								relative_urls : false,
								remove_script_host : false,
								convert_urls : true,
								paste_as_text: true,
								setup: function (editor) {
								}
							});
							jQuery('#editor').val('html');
						}
						/**
						 * Switch to plain text
						 */
						function wpsc_get_textarea(el, selector) {
							jQuery(el).parent().find('.visual').removeClass('active');
							jQuery(el).addClass('active');
							tinymce.remove();
							jQuery('#editor').val('text');
						}
					</script>
				</div>
				<?php do_action( 'wpsc_ep_usertype_warning_mail' ); ?>
				<input type="hidden" name="action" value="wpsc_set_usertype_email_warning">
				<input id="editor" type="hidden" name="editor" value="<?php echo esc_attr( $settings['editor'] ); ?>">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_usertype_email_warning' ) ); ?>">
			</form>
			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_set_usertype_email_warning(this);">
					<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_reset_ep_warning(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_reset_ep_warning' ) ); ?>');">
					<?php echo esc_attr( wpsc__( 'Reset default', 'supportcandy' ) ); ?></button>
			</div>
			<?php
			wp_die();
		}

		/**
		 * Save settings
		 *
		 * @return void
		 */
		public static function save_settings() {

			if ( check_ajax_referer( 'wpsc_set_usertype_email_warning', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$usertype_warning_page = apply_filters(
				'wpsc_set_ep_email_warning',
				array(
					'email-warning-message' => isset( $_POST ) && isset( $_POST['email-warning-message'] ) ? wp_kses_post( wp_unslash( $_POST['email-warning-message'] ) ) : '',
					'editor'                => isset( $_POST['editor'] ) ? sanitize_text_field( wp_unslash( $_POST['editor'] ) ) : 'html',
					'enable'                => isset( $_POST['enable'] ) ? sanitize_text_field( wp_unslash( $_POST['enable'] ) ) : 1,
				)
			);
			update_option( 'wpsc-ep-usertype-warning-email', $usertype_warning_page );

			// remove string translations.
			WPSC_Translations::remove( 'wpsc-ep-usertype-warning-message' );

			// add string translations.
			WPSC_Translations::add( 'wpsc-ep-usertype-warning-message', $usertype_warning_page['email-warning-message'] );

			wp_die();
		}

		/**
		 * Reset settings to default
		 *
		 * @return void
		 */
		public static function reset_settings() {

			if ( check_ajax_referer( 'wpsc_reset_ep_warning', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 400 );
			}
			self::reset();
			wp_die();
		}

		/**
		 * Send email when allowed user type is set to "Yes"
		 *
		 * @param object $email - email info.
		 * @return void
		 */
		public static function reject_user_pipe_email( $email ) {

			$settings = get_option( 'wpsc-ep-usertype-warning-email' );
			if ( ! $settings['enable'] ) {
				return;
			}

			$en_general = get_option( 'wpsc-en-general' );
			$en         = new self();

			// from name & email.
			if ( ! $en_general['from-name'] || ! $en_general['from-email'] ) {
				return false;
			}
			$en->from_name  = $en_general['from-name'];
			$en->from_email = $en_general['from-email'];
			$en->reply_to   = $en_general['reply-to'] ? $en_general['reply-to'] : $en->from_email;
			$en->subject    = $email->subject;
			$ut_warning     = $settings['email-warning-message'] ? WPSC_Translations::get( 'wpsc-ep-usertype-warning-message', stripslashes( $settings['email-warning-message'] ) ) : stripslashes( $settings['email-warning-message'] );
			$en->body       = $ut_warning;
			$en->to         = $email->from_email;
			$en->send();
		}
	}
endif;

WPSC_EP_Usertype_Warning_Setting::init();
