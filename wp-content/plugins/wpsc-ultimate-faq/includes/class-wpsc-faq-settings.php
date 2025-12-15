<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_FAQ_Settings' ) ) :

	final class WPSC_FAQ_Settings {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// faq icon.
			add_filter( 'wpsc_icons', array( __CLASS__, 'faq_icon' ) );

			// faq tab in settings.
			add_filter( 'wpsc_settings_page_sections', array( __CLASS__, 'faq_setting_tab' ) );

			// faq settings.
			add_action( 'wp_ajax_wpsc_get_faq_settings', array( __CLASS__, 'load_settings_ui' ) );
			add_action( 'wp_ajax_wpsc_set_faq_settings', array( __CLASS__, 'save_settings' ) );
			add_action( 'wp_ajax_wpsc_reset_faq_settings', array( __CLASS__, 'reset_settings' ) );
		}

		/**
		 * Reset settings
		 *
		 * @return void
		 */
		public static function reset() {

			$faq_settings = apply_filters(
				'wpsc_faq_settings',
				array(
					'faq' => '',
				)
			);
			update_option( 'wpsc-faq-settings', $faq_settings );
		}

		/**
		 * FAQ icon
		 *
		 * @param array $icons - icon array.
		 * @return array
		 */
		public static function faq_icon( $icons ) {

			$icons['faq'] = file_get_contents( WPSC_FAQ_ABSPATH . 'asset/icons/question-circle-solid.svg' ); //phpcs:ignore
			return $icons;
		}

		/**
		 * FAQ Settings tab
		 *
		 * @param array $sections - section array.
		 * @return array
		 */
		public static function faq_setting_tab( $sections ) {

			$sections['faq'] = array(
				'slug'     => 'faq_settings',
				'icon'     => 'faq',
				'label'    => esc_attr__( 'FAQ', 'wpsc-faq' ),
				'callback' => 'wpsc_get_faq_settings',
			);
			return $sections;
		}

		/**
		 * Settings user interface
		 *
		 * @return void
		 */
		public static function load_settings_ui() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}
			$faq_settings = get_option( 'wpsc-faq-settings', array() );?>
			<div class="wpsc-setting-header">
				<h2><?php esc_attr_e( 'FAQ', 'wpsc-faq' ); ?></h2>
			</div>
			<div class="wpsc-setting-section-body">
				<form action="#" onsubmit="return false;" class="wpsc-frm-faq-settings">
					<div class="wpsc-input-group">
						<div class="radio-container">
							<?php $unique_id = uniqid( 'wpsc_' ); ?>
							<input id="<?php echo esc_attr( $unique_id ); ?>" type="radio" <?php checked( $faq_settings['faq'], 'ultimate_faq' ); ?> name="faq" value="ultimate_faq"/>
							<label for="<?php echo esc_attr( $unique_id ); ?>">Ultimate FAQ (<a href="https://wordpress.org/plugins/ultimate-faqs/" target="_blank">Plugin Url</a>)</label>
							<span class="faq-warning">
							<?php
							if ( $faq_settings['faq'] == 'ultimate_faq' && ! class_exists( 'ewdufaqInit' ) ) {
								?>
								<div class="wpsc_faq_notice">Please install Ultimate FAQ plugin!</div>
								<?php
							}
							?>
							</span>
						</div>
					</div>
					<div class="wpsc-input-group">
						<div class="radio-container">
							<?php $unique_id = uniqid( 'wpsc_' ); ?>
							<input id="<?php echo esc_attr( $unique_id ); ?>" type="radio" <?php checked( $faq_settings['faq'], 'acronix_faq' ); ?> name="faq" value="acronix_faq"/>
							<label for="<?php echo esc_attr( $unique_id ); ?>">Acronix FAQ (<a href="https://wordpress.org/plugins/arconix-faq/" target="_blank">Plugin Url</a>)</label>
							<span class="faq-warning">
							<?php
							if ( $faq_settings['faq'] == 'acronix_faq' && ! class_exists( 'Arconix_FAQ' ) ) {
								?>
								<div class="wpsc_faq_notice">Please install Acronix FAQ plugin!</div>
								<?php
							}
							?>
							</span>
						</div>
					</div>
					<div class="wpsc-input-group">
						<div class="radio-container">
							<?php $unique_id = uniqid( 'wpsc_' ); ?>
							<input id="<?php echo esc_attr( $unique_id ); ?>" type="radio" <?php checked( $faq_settings['faq'], 'easy_accordion' ); ?> name="faq" value="easy_accordion"/>
							<label for="<?php echo esc_attr( $unique_id ); ?>">Easy Accordion FAQ (<a href="https://wordpress.org/plugins/easy-accordion-free/" target="_blank">Plugin Url</a>)</label>
							<span class="faq-warning">
							<?php
							if ( $faq_settings['faq'] == 'easy_accordion' && ! class_exists( 'SP_EASY_ACCORDION_FREE' ) ) {
								?>
								<div class="wpsc_faq_notice">Please install Easy Accordion FAQ plugin!</div>
								<?php
							}
							?>
							</span>
						</div>
					</div>
					<div class="wpsc-input-group">
						<div class="radio-container">
							<?php $unique_id = uniqid( 'wpsc_' ); ?>
							<input id="<?php echo esc_attr( $unique_id ); ?>" type="radio" <?php checked( $faq_settings['faq'], 'betterdocs_faq' ); ?> name="faq" value="betterdocs_faq"/>
							<label for="<?php echo esc_attr( $unique_id ); ?>">BetterDocs FAQ (<a href="https://wordpress.org/plugins/betterdocs/" target="_blank">Plugin Url</a>)</label>
							<span class="faq-warning">
							<?php
							if ( $faq_settings['faq'] == 'betterdocs_faq' && ! function_exists( 'betterdocs' ) ) {
								?>
								<div class="wpsc_faq_notice">Please install BetterDocs FAQ plugin!</div>
								<?php
							}
							?>
							</span>
						</div>
					</div>
					<?php do_action( 'wpsc_faq_settings' ); ?>
					<input type="hidden" name="action" value="wpsc_set_faq_settings">
					<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_faq_settings' ) ); ?>">
				</form>
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_set_faq_settings(this);">
					<?php echo esc_attr( wpsc__( 'Submit', 'supportcandy' ) ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_reset_faq_settings(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_reset_faq_settings' ) ); ?>');">
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

			if ( check_ajax_referer( 'wpsc_set_faq_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$faq_settings = apply_filters(
				'wpsc_set_faq_settings',
				array(
					'faq' => isset( $_POST['faq'] ) ? sanitize_text_field( wp_unslash( $_POST['faq'] ) ) : '',
				)
			);
			update_option( 'wpsc-faq-settings', $faq_settings );
			wp_die();
		}

		/**
		 * Reset settings to default
		 *
		 * @return void
		 */
		public static function reset_settings() {

			if ( check_ajax_referer( 'wpsc_reset_faq_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}
			self::reset();
			wp_die();
		}
	}
endif;

WPSC_FAQ_Settings::init();
