<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.presstigers.com/
 * @since      1.0.0
 *
 * @package    Reports
 * @subpackage Reports/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Reports
 * @subpackage Reports/public
 * @author     PressTigers <wt@presstigers.com>
 */
class Reports_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// add records inside the database for views and downloads

		add_action( 'wp_ajax_view_document_log_handler', array($this, 'view_document_log_handler') );
		add_action( 'wp_ajax_download_document_log_handler', array($this, 'download_document_log_handler') );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Reports_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Reports_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/reports-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Reports_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Reports_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'reports-ajax', plugin_dir_url( __FILE__ ) . 'js/reports-public.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( 'reports-ajax', 'frontend_ajax_object', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' )
		));

	}

	/**
	 * View frontend documents
	 * Records the views of the frontend documents inside the database table for logs
	 */

	function view_document_log_handler() {

		global $wpdb;
		$doc_id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');

		if (!$doc_id) {

			$response = 'incorrect id';

		} else {

			$ipaddress = reports_get_ip_address();
			$date_time = current_time('mysql');
			$current_user = wp_get_current_user();
			$visitor_id = $current_user->ID;
			$company_id = get_post_meta($doc_id, 'frontend_document_author_company', true);
			$account_manager = get_the_author_meta( 'account_manager', $current_user->ID );
			$user_status = get_user_meta( $current_user->ID, 'user_status', true );
			$allowed_roles = array('business', 'subscriber_-_admin_facility', 'subscriber_-_corporate' , 'subscriber_-_facility_user');

			if( array_intersect($allowed_roles, $current_user->roles ) ) {
				// We need to log this view.
				$table = $wpdb->prefix . 'frontend_doc_views';
				$data = array(
					'post_id' => $doc_id,
					'visitor_ip' => $ipaddress,
					'user_id' => $visitor_id,
					'company_id' => $company_id,
					'account_manager' => $account_manager,
					'user_status' => $user_status,
					'date_time' => $date_time
				);

				$data = array_filter($data); //Remove any null values.
				$is_successfully_inserted = $wpdb->insert($table, $data, array('%d', '%s', '%d', '%d', '%s', '%s', '%s'));

				if ($is_successfully_inserted) {
					$response = 'View document logged successfully.';
				} else {
					$response = 'Failed to log viewed document.';
				}
			
			}
			else {
				$response = 'User not allowed.';
			}
		}

		echo $response;

		wp_die();

	}

	/**
	 * Download document ajax callback
	 * Record document download entry inside the database table for logs
	 */

	public function download_document_log_handler() {

		global $wpdb;

		$id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
		$download_doc_id = absint($id);

		if ($download_doc_id == 0) {
			$response = 'Incorrect Id.';
		} else {
			$ipaddress = reports_get_ip_address();
			$date_time = current_time('mysql');
			$current_user = wp_get_current_user();
			$visitor_id = $current_user->ID;
			$company_id = get_post_meta($download_doc_id, 'frontend_document_author_company', true);
			$account_manager = get_the_author_meta( 'account_manager', $current_user->ID );
			$user_status = get_user_meta( $current_user->ID, 'user_status', true );
			$allowed_roles = array('business', 'subscriber_-_admin_facility', 'subscriber_-_corporate' , 'subscriber_-_facility_user');
			if( array_intersect($allowed_roles, $current_user->roles ) ) {
				// We need to log this download.
				$table = $wpdb->prefix . 'frontend_doc_downloads';
				$data = array(
					'post_id' => $download_doc_id,
					'visitor_ip' => $ipaddress,
					'user_id' => $visitor_id,
					'company_id' => $company_id,
					'account_manager' => $account_manager,
					'user_status' => $user_status,
					'date_time' => $date_time
				);

				$data = array_filter($data); //Remove any null values.
				$is_successfully_inserted = $wpdb->insert($table, $data, array('%d', '%s', '%d', '%d', '%s', '%s', '%s'));

				if ($is_successfully_inserted) {
					$response =  'Download document logged successfully.';
				} else {
					$response = 'Failed to log downloaded document.';
				}

			}
			else {
				$response = 'User not allowed.';
			}

		}

		echo $response;
		wp_die();
		 
	}

}