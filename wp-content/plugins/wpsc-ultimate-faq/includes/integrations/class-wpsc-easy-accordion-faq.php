<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Easy_Accordion_FAQ' ) ) :

	final class WPSC_Easy_Accordion_FAQ {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// get add/replace macros for this faq.
			add_filter( 'wpsc_macros', array( __CLASS__, 'add_macro' ), 10, 1 );
			add_filter( 'wpsc_replace_macros', array( __CLASS__, 'easy_accordion_replace_macro' ), 10, 3 );

			// get add faq in create ticket and individual ticket.
			add_action( 'wpsc_tff_editor_actions', array( __CLASS__, 'easy_accordion_faq_tab' ) );
			add_action( 'wpsc_it_editor_actions', array( __CLASS__, 'easy_accordion_faq_tab' ) );

			// get faq details.
			add_action( 'wp_ajax_wpsc_get_easy_accordion_faq', array( __CLASS__, 'get_easy_accordion_faq' ) );
			add_action( 'wp_ajax_wpsc_easy_accordion_faq_insert_link', array( __CLASS__, 'easy_accordion_insert_link' ) );
			add_action( 'wp_ajax_wpsc_easy_accordion_faq_insert_text', array( __CLASS__, 'easy_accordion_insert_text' ) );
		}

		/**
		 * Add easy accordion faq macro
		 *
		 * @param array $macros - easy accordion macro name.
		 * @return array
		 */
		public static function add_macro( $macros ) {

			if ( class_exists( 'SP_EASY_ACCORDION_FREE' ) ) :
				$macros[] = array(
					'tag'   => '{{easy_accordion_faq}}',
					'title' => 'Easy accordion FAQ suggestions',
				);
			endif;
			return $macros;
		}

		/**
		 * Easy accordion faq
		 *
		 * @return void
		 */
		public static function easy_accordion_faq_tab() {

			$current_user = WPSC_Current_User::$current_user;
			$faq_settings = get_option( 'wpsc-faq-settings' );

			if ( ! $current_user->user->ID || ! $current_user->is_agent || ! class_exists( 'SP_EASY_ACCORDION_FREE' ) ) {
				return;
			}
			if ( $faq_settings['faq'] == 'easy_accordion' ) :?>
				<span class="wpsc-link" onclick="wpsc_get_easy_accordion_faq('<?php echo esc_attr( wp_create_nonce( 'wpsc_get_easy_accordion_faq' ) ); ?>')"><?php esc_attr_e( 'FAQ', 'wpsc-faq' ); ?></span>
				<?php
			endif;
		}

		/**
		 * Get easy accordion faq
		 *
		 * @return void
		 */
		public static function get_easy_accordion_faq() {

			if ( check_ajax_referer( 'wpsc_get_easy_accordion_faq', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			$current_user = WPSC_Current_User::$current_user;
			if ( ! $current_user->user->ID || ! $current_user->is_agent || ! class_exists( 'SP_EASY_ACCORDION_FREE' ) ) {
				exit;
			}

			$args = array(
				'post_type'      => 'sp_accordion_faqs',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'order'          => 'ID',
				'orderby'        => 'DESC',
			);
			$posts = get_posts( $args );

			$title = esc_attr__( 'FAQ', 'wpsc-faq' );
			$unique_id = uniqid();
			ob_start();
			?>
			<div style="width: 100%;">
				<table class="<?php echo esc_attr( $unique_id ); ?>">
					<thead>
						<tr>
							<th><?php esc_attr_e( 'Insert', 'wpsc-faq' ); ?></th>
							<th><?php esc_attr_e( 'FAQ', 'wpsc-faq' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $posts as $post ) {
							?>
							<tr>
								<td> 
									<a class="wpsc-link" href="javascript:wpsc_easy_accordion_faq_insert_link(<?php echo esc_attr( $post->ID ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_easy_accordion_faq_insert_link' ) ); ?>');"><?php esc_attr_e( 'Link', 'wpsc-faq' ); ?></a> | 
									<a class="wpsc-link" href="javascript:wpsc_easy_accordion_faq_insert_text(<?php echo esc_attr( $post->ID ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_easy_accordion_faq_insert_text' ) ); ?>');"><?php esc_attr_e( 'Content', 'wpsc-faq' ); ?></a>
								</td>
								<td><?php echo esc_attr( $post->post_title ); ?></td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<script>jQuery('.<?php echo esc_attr( $unique_id ); ?>').DataTable();</script>
			</div>
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
			wp_send_json( $response );
		}

		/**
		 * Easy accordion faq insert link
		 *
		 * @return void
		 */
		public static function easy_accordion_insert_link() {

			if ( check_ajax_referer( 'wpsc_easy_accordion_faq_insert_link', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			$current_user = WPSC_Current_User::$current_user;
			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			if ( ! $post_id || ! $current_user->user->ID || ! $current_user->is_agent || ! class_exists( 'SP_EASY_ACCORDION_FREE' ) ) {
				exit;
			}

			$faq = get_post( $post_id );
			echo '<a href="' . esc_url( get_permalink( $post_id ) ) . '" target="_blank">' . esc_attr( $faq->post_title ) . '</a>';
			wp_die();
		}

		/**
		 * Easy accordion faq insert text
		 *
		 * @return void
		 */
		public static function easy_accordion_insert_text() {

			if ( check_ajax_referer( 'wpsc_easy_accordion_faq_insert_text', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			$current_user = WPSC_Current_User::$current_user;
			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			if ( ! $post_id || ! $current_user->user->ID || ! $current_user->is_agent || ! class_exists( 'SP_EASY_ACCORDION_FREE' ) ) {
				exit;
			}

			$faq = get_post( $post_id );
			echo wp_kses_post( wpautop( $faq->post_content ) );
			wp_die();
		}

		/**
		 * Replace macro
		 *
		 * @param string      $str - string.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @param string      $macro - string macro name.
		 * @return string
		 */
		public static function easy_accordion_replace_macro( $str, $ticket, $macro ) {

			if ( $macro === 'easy_accordion_faq' ) {

				$subject_words = explode( ' ', $ticket->subject );
				$args = array(
					'post_type'      => 'sp_accordion_faqs',
					'post_status'    => 'publish',
					'posts_per_page' => 3,
					's'              => implode( ' ', array_slice( $subject_words, 0, 3 ) ),
				);
				$posts = get_posts( $args );

				$count        = 0;
				$replace_data = '<ul>';

				foreach ( $posts as $post ) {
					++$count;
					$replace_data .= '<li><a href="' . get_permalink( $post->ID ) . '" target="_blank">' . $post->post_title . '</a></li>';
				}

				$replace_data .= '</ul>';

				if ( ! $count ) {
					$replace_data .= esc_attr__( 'No FAQ suggestions found!', 'wpsc-faq' );
				}

				$str = str_replace( '{{easy_accordion_faq}}', $replace_data, $str );
			}
			return $str;
		}
	}
endif;
WPSC_Easy_Accordion_FAQ::init();
