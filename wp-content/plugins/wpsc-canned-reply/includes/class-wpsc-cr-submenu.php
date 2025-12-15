<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_CR_Submenu' ) ) :

	final class WPSC_CR_Submenu {

		/**
		 * Initialize the class
		 *
		 * @return void
		 */
		public static function init() {

			add_action( 'wpsc_before_setting_admin_menu', array( __CLASS__, 'load_admin_menu' ) );
		}

		/**
		 * Load admin submenu
		 *
		 * @return void
		 */
		public static function load_admin_menu() {

			add_submenu_page(
				'wpsc-tickets',
				esc_attr__( 'Canned Reply', 'wpsc-cr' ),
				esc_attr__( 'Canned Reply', 'wpsc-cr' ),
				'manage_options',
				'wpsc-canned-reply',
				array( __CLASS__, 'layout' )
			);
		}

		/**
		 * Canned reply admin submenu layout
		 *
		 * @return void
		 */
		public static function layout() {
			?>

			<div class="wrap">
				<hr class="wp-header-end">
				<div id="wpsc-container">

					<div class="wpsc-setting-header">
						<h2><?php esc_attr_e( 'Canned Reply', 'wpsc-cr' ); ?></h2>
					</div>

					<div class="wpsc-setting-section-body">
						<?php self::load_canned_replies(); ?>
					</div>

				</div>
			</div>
			<?php
		}

		/**
		 * Load canned reply table
		 *
		 * @return void
		 */
		public static function load_canned_replies() {

			$unique_id    = uniqid();
			$canned_reply = WPSC_Canned_Reply_Model::find( array( 'items_per_page' => 0 ) )['results'];
			?>
			<table class="<?php echo esc_attr( $unique_id ); ?> wpsc-setting-tbl">
				<thead>
					<tr>
						<th><?php echo esc_attr( wpsc__( 'Title', 'supportcandy' ) ); ?></th>
						<th><?php esc_attr_e( 'Author', 'wpsc-cr' ); ?></th>
						<th><?php esc_attr_e( 'Categories', 'wpsc-cr' ); ?></th>
						<th><?php esc_attr_e( 'Visibility', 'wpsc-cr' ); ?></th>
						<th><?php echo esc_attr( wpsc__( 'Date Created', 'supportcandy' ) ); ?></th>
						<th><?php echo esc_attr( wpsc__( 'Actions', 'supportcandy' ) ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $canned_reply as $creply ) {

						$visibility = $creply->visibility == 'private' ? esc_attr__( 'Private', 'wpsc-cr' ) : esc_attr__( 'Public', 'wpsc-cr' );
						$tz         = wp_timezone();
						$date       = $creply->date_created;
						$date->setTimezone( $tz );
						$cat_names = array();
						foreach ( $creply->categories as $category ) {
							$cat_names[] = $category->name;
						}
						?>
						<tr>
							<td>
								<a href="javascript:wpsc_get_edit_cr_admin(<?php echo esc_attr( $creply->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_edit_cr_admin' ) ); ?>')"><?php echo esc_attr( $creply->title ); ?></a>
							</td>
							<td><?php echo esc_attr( $creply->author->name ); ?></td>
							<td>
								<?php
								$cat_name = implode( ', ', $cat_names );
								echo esc_attr( $cat_name );
								?>
							</td>
							<td><?php echo esc_attr( $visibility ); ?></td>
							<td><?php echo esc_attr( $date->format( 'Y-m-d H:i:s' ) ); ?></td>
							<td>
								<a href="javascript:wpsc_get_edit_cr_admin(<?php echo esc_attr( $creply->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_edit_cr_admin' ) ); ?>')"><?php echo esc_attr( wpsc__( 'Edit', 'supportcandy' ) ); ?></a> |
								<a href="javascript:wpsc_delete_cr_admin(<?php echo esc_attr( $creply->id ); ?>, '<?php echo esc_attr( wp_create_nonce( 'wpsc_delete_cr_admin' ) ); ?>')"><?php echo esc_attr( wpsc__( 'Delete', 'supportcandy' ) ); ?></a>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<script>
				jQuery(document).ready(function() {
					jQuery('.<?php echo esc_attr( $unique_id ); ?>').DataTable({
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

											window.history.replaceState( {}, null, 'admin.php?page=wpsc-canned-reply' );

											jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

											wpsc_scroll_top();

											var data = { action: 'wpsc_get_add_new_cr_admin', _ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_get_add_new_cr_admin' ) ); ?>' };
											jQuery.post(
												supportcandy.ajax_url,
												data,
												function (response) {
													jQuery( '.wpsc-setting-section-body' ).html( response );
													wpsc_reset_responsive_style();
												}
											);
										}
									}
								],
							},
						},
						language: supportcandy.translations.datatables
					});
				});
			</script>
			<?php
		}
	}
endif;

WPSC_CR_Submenu::init();
