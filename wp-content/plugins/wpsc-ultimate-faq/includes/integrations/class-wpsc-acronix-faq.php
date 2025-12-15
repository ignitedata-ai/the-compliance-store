<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Acronix_FAQ' ) ) :

	final class WPSC_Acronix_FAQ {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// get add macros for this faq.
			add_filter( 'wpsc_macros', array( __CLASS__, 'add_macro' ), 10, 1 );

			// get add faq in create ticket and individual ticket.
			add_action( 'wpsc_tff_editor_actions', array( __CLASS__, 'acronix_faq_tab' ) );
			add_action( 'wpsc_it_editor_actions', array( __CLASS__, 'acronix_faq_tab' ) );

			// get faq details.
			add_action( 'wp_ajax_wpsc_get_acronix_faq', array( __CLASS__, 'get_acronix_faq' ) );
			add_action( 'wp_ajax_wpsc_acronix_faq_insert_link', array( __CLASS__, 'acronix_insert_link' ) );
			add_action( 'wp_ajax_wpsc_acronix_faq_insert_text', array( __CLASS__, 'acronix_insert_text' ) );

			// get replace macro in macros.
			add_filter( 'wpsc_replace_macros', array( __CLASS__, 'acronix_replace_macro' ), 10, 3 );
		}

		/**
		 * Add acronix faq macro
		 *
		 * @param array $macros - macro name array.
		 * @return array
		 */
		public static function add_macro( $macros ) {

			if ( class_exists( 'Arconix_FAQ' ) ) :
				$macros[] = array(
					'tag'   => '{{acronix_faq}}',
					'title' => 'Acronix FAQ suggestions',
				);
			endif;
			return $macros;
		}

		/**
		 * Acronix faq tab
		 *
		 * @return void
		 */
		public static function acronix_faq_tab() {

			$current_user = WPSC_Current_User::$current_user;
			$faq_settings = get_option( 'wpsc-faq-settings' );

			if ( ! $current_user->user->ID || ! $current_user->is_agent || ! class_exists( 'Arconix_FAQ' ) ) {
				return;
			}
			if ( $faq_settings['faq'] == 'acronix_faq' ) :?>
				<span class="wpsc-link" onclick="wpsc_get_acronix_faq('<?php echo esc_attr( wp_create_nonce( 'wpsc_get_acronix_faq' ) ); ?>')"><?php esc_attr_e( 'FAQ', 'wpsc-faq' ); ?></span>
				<?php
			endif;
		}

		/**
		 * Acronix faq insert link
		 *
		 * @return void
		 */
		public static function acronix_insert_link() {

			if ( check_ajax_referer( 'wpsc_acronix_faq_insert_link', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			$current_user = WPSC_Current_User::$current_user;

			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			if ( ! $post_id || ! $current_user->user->ID || ! $current_user->is_agent || ! class_exists( 'Arconix_FAQ' ) ) {
				exit;
			}

			$faq = get_post( $post_id );
			echo '<a href="' . esc_url( get_permalink( $post_id ) ) . '" target="_blank">' . esc_attr( $faq->post_title ) . '</a>';
			wp_die();
		}

		/**
		 * Acronix faq insert text
		 *
		 * @return void
		 */
		public static function acronix_insert_text() {

			if ( check_ajax_referer( 'wpsc_acronix_faq_insert_text', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			$current_user = WPSC_Current_User::$current_user;

			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

			if ( ! $post_id || ! $current_user->user->ID || ! $current_user->is_agent || ! class_exists( 'Arconix_FAQ' ) ) {
				exit;
			}

			$faq = get_post( $post_id );
			echo wp_kses_post( wpautop( $faq->post_content ) );
			wp_die();
		}

		/**
		 * Get acronix faq
		 *
		 * @return void
		 */
		public static function get_acronix_faq() {

			if ( check_ajax_referer( 'wpsc_get_acronix_faq', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 400 );
			}

			$current_user = WPSC_Current_User::$current_user;
			if ( ! $current_user->user->ID || ! $current_user->is_agent || ! class_exists( 'Arconix_FAQ' ) ) {
				exit;
			}

			$args = array(
				'post_type'      => 'faq',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);

			$posts = get_posts( $args );
			$title = esc_attr__( 'FAQ', 'wpsc-faq' );

			// Unique ID.
			$unique_id = uniqid();

			ob_start();
			?>
			<div style="width: 100%;">
				<table class="<?php echo esc_attr( $unique_id ); ?>">
					<thead>
						<tr>
							<th><?php esc_attr_e( 'Insert', 'wpsc-faq' ); ?></th>
							<th><?php esc_attr_e( 'FAQ', 'wpsc-faq' ); ?></th>
							<th><?php echo esc_attr( wpsc_translate_common_strings( 'category' ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $posts as $post ) {

							$categories = get_the_terms( $post->ID, 'group' );
							if ( ! empty( $categories ) ) {
								$category_name = array();
								foreach ( $categories as $category ) :
									$category_name[] = $category->name;
									endforeach;
								$category_name = implode( ', ', $category_name );
							} else {
								$category_name = esc_attr__( 'Uncategorized', 'wpsc-faq' );
							}
							?>
							<tr>
								<td> 
									<a class="wpsc-link" href="javascript:wpsc_acronix_faq_insert_link(<?php echo esc_attr( $post->ID ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_acronix_faq_insert_link' ) ); ?>');"><?php esc_attr_e( 'Link', 'wpsc-faq' ); ?></a> | 
									<a class="wpsc-link" href="javascript:wpsc_acronix_faq_insert_text(<?php echo esc_attr( $post->ID ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_acronix_faq_insert_text' ) ); ?>');"><?php esc_attr_e( 'Content', 'wpsc-faq' ); ?></a>
								</td>
								<td><?php echo esc_attr( $post->post_title ); ?></td>
								<td><?php echo esc_attr( $category_name ); ?></td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<script>
					var table = jQuery('.<?php echo esc_attr( $unique_id ); ?>').DataTable();
					jQuery(document).ready(function() {
						jQuery('div.dt-search input', table.table().container()).focus();
					} );
				</script>

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
		 * Replace macro for acronix faq
		 *
		 * @param string      $str - string.
		 * @param WPSC_Ticket $ticket - ticket object.
		 * @param string      $macro - string macro name.
		 * @return stirng
		 */
		public static function acronix_replace_macro( $str, $ticket, $macro ) {

			if ( $macro === 'acronix_faq' ) {

				$ticket_subject = explode( ' ', $ticket->subject );
				$args           = array(
					'post_type'      => 'faq',
					'post_status'    => 'publish',
					'posts_per_page' => 3,
					'tax_query'      => array(
						array(
							'taxonomy' => 'group',
							'field'    => 'name',
							'terms'    => $ticket_subject,
							'operator' => 'IN',
						),
					),
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

				$str = str_replace( '{{acronix_faq}}', $replace_data, $str );
			}
			return $str;
		}
	}
endif;

WPSC_Acronix_FAQ::init();
