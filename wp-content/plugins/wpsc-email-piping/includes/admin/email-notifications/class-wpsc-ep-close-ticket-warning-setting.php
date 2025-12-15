<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_EP_Close_Ticket_Warning_Setting' ) ) :

	final class WPSC_EP_Close_Ticket_Warning_Setting extends WPSC_Email_Notifications {

		/**
		 * Initialization
		 *
		 * @return void
		 */
		public static function init() {

			add_action( 'wp_ajax_wpsc_closed_ticket_warning', array( __CLASS__, 'closed_ticket_warning' ) );
			add_action( 'wp_ajax_wpsc_set_close_ticket_warning', array( __CLASS__, 'save_settings' ) );
			add_action( 'wp_ajax_wpsc_reset_close_ticket_warning', array( __CLASS__, 'reset_settings' ) );

			// User reject pipe email template and send email on below action.
			add_action( 'wpsc_ep_reject_closed_ticket_reply', array( __CLASS__, 'reject_closed_ticket_reply' ), 10, 2 );
		}

		/**
		 * Reset settings
		 *
		 * @return void
		 */
		public static function reset() {

			$close_ticket_message         = '<p>Hello {{customer_name}},</p><p>The ticket #{{ticket_id}} is closed. Please create a new ticket on our website for further queries!</p>';
			$close_ticket_message_setting = apply_filters(
				'wpsc_close_ticket_page_settings',
				array(
					'close-ticket-html' => $close_ticket_message,
					'editor'            => 'html',
					'enable'            => 1,
				)
			);
			update_option( 'wpsc-close-ticket-page-settings', $close_ticket_message_setting );
		}

		/**
		 * Get general settings
		 *
		 * @return void
		 */
		public static function closed_ticket_warning() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( 'Unauthorized!', 401 );
			}

			$settings = get_option( 'wpsc-close-ticket-page-settings', array() ); ?>

			<form action="#" onsubmit="return false;" class="wpsc-ep-close-ticket-warning-email">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for=""><?php echo esc_attr( wpsc__( 'Enable', 'supportcandy' ) ); ?></label>
					</div>
					<select name="enable">
						<option <?php selected( $settings['enable'], '1' ); ?> value="1"><?php echo esc_attr( wpsc__( 'Yes', 'supportcandy' ) ); ?></option>
						<option <?php selected( $settings['enable'], '0' ); ?> value="0"><?php echo esc_attr( wpsc__( 'No', 'supportcandy' ) ); ?></option>
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
								<button class="visual wpsc-switch-editor <?php echo esc_attr( $settings['editor'] ) == 'html' ? 'active' : ''; ?>" type="button" onclick="wpsc_get_tinymce(this, 'close-ticket-html','close_ticket_html_body');"><?php echo esc_attr( wpsc__( 'Visual', 'supportcandy' ) ); ?></button>
								<button class="text wpsc-switch-editor <?php echo esc_attr( $settings['editor'] ) == 'text' ? 'active' : ''; ?>" type="button" onclick="wpsc_get_textarea(this, 'close-ticket-html')"><?php echo esc_attr( wpsc__( 'Text', 'supportcandy' ) ); ?></button>
							</div>
						</div>
						<?php
						$ct_warning = WPSC_Translations::get( 'wpsc-close-ticket-html', stripslashes( $settings['close-ticket-html'] ) );
						?>
						<textarea name="close-ticket-html" id="close-ticket-html" class="wpsc_textarea"><?php echo esc_attr( $ct_warning ); ?></textarea>
						<div class="wpsc-it-editor-action-container">
							<div class="actions">
								<div class="wpsc-editor-actions">
									<span class="wpsc-link" onclick="wpsc_get_macros()"><?php echo esc_attr( wpsc__( 'Insert Macro', 'supportcandy' ) ); ?></span>
								</div>
							</div>
						</div>
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
						function wpsc_get_textarea(el, selector){
							jQuery(el).parent().find('.visual').removeClass('active');
							jQuery(el).addClass('active');
							tinymce.remove();
							jQuery('#editor').val('text');
						}
					</script>
				</div>
				<?php do_action( 'wpsc_ep_usertype_warning_mail' ); ?>
				<input type="hidden" name="action" value="wpsc_set_close_ticket_warning">
				<input id="editor" type="hidden" name="editor" value="<?php echo esc_attr( $settings['editor'] ); ?>">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_close_ticket_warning' ) ); ?>">
			</form>
			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_set_close_ticket_warning(this);">
					<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_reset_close_ticket_warning(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_reset_close_ticket_warning' ) ); ?>');">
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

			if ( check_ajax_referer( 'wpsc_set_close_ticket_warning', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$close_ticket_warning_page = apply_filters(
				'wpsc_set_close_ticket_warning_email',
				array(
					'close-ticket-html' => isset( $_POST ) && isset( $_POST['close-ticket-html'] ) ? wp_kses_post( wp_unslash( $_POST['close-ticket-html'] ) ) : '',
					'editor'            => isset( $_POST['editor'] ) ? sanitize_text_field( wp_unslash( $_POST['editor'] ) ) : 'html',
					'enable'            => isset( $_POST['enable'] ) ? sanitize_text_field( wp_unslash( $_POST['enable'] ) ) : 1,
				)
			);
			update_option( 'wpsc-close-ticket-page-settings', $close_ticket_warning_page );

			// remove string translations.
			WPSC_Translations::remove( 'close-ticket-html' );

			// add string translations.
			WPSC_Translations::add( 'close-ticket-html', $close_ticket_warning_page['close-ticket-html'] );

			wp_die();
		}

		/**
		 * Reset settings to default
		 *
		 * @return void
		 */
		public static function reset_settings() {

			if ( check_ajax_referer( 'wpsc_reset_close_ticket_warning', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}
			self::reset();
			wp_die();
		}

		/**
		 * Send email when reply to closed ticket is not allowed
		 *
		 * @param object $email - email details.
		 * @param object $ticket - ticket object.
		 * @return void
		 */
		public static function reject_closed_ticket_reply( $email, $ticket ) {

			$settings = get_option( 'wpsc-close-ticket-page-settings' );
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
			$en->subject    = WPSC_Macros::replace( $email->subject, $ticket );
			$ct_warning     = $settings['close-ticket-html'] ? WPSC_Translations::get( 'wpsc-close-ticket-html', stripslashes( $settings['close-ticket-html'] ) ) : stripslashes( $settings['close-ticket-html'] );
			$en->body       = WPSC_Macros::replace( $ct_warning, $ticket );
			$en->to         = $email->from_email;
			$en->send();
		}
	}
endif;

WPSC_EP_Close_Ticket_Warning_Setting::init();
