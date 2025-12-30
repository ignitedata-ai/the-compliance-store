<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.presstigers.com/
 * @since             0.0.1
 * @package           Reports
 *
 * @wordpress-plugin
 * Plugin Name:       Reports
 * Plugin URI:        https://www.presstigers.com/
 * Description:       This plugin is used to generate reports. Which are based on users activities, documents and links. Note: This plugin requires WP Link Status Pro and User Login History to be activated.
 * Version:           1.1.0
 * Author:            PressTigers
 * Author URI:        https://www.presstigers.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       reports
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('REPORTS_VERSION', '0.0.1');
define('REPORTS_DIR_NAME', dirname(plugin_basename(__FILE__)));
define('REPORTS_URL', plugins_url('', __FILE__));
define('REPORTS_PATH', plugin_dir_path(__FILE__));
define('REPORTS_SITE_HOME_URL', home_url());

global $reports_db_version;
$reports_db_version = '1.0';

//File includes
include_once('admin/reports-utility-functions-admin-side.php');
include_once('admin/reports-views-list-table.php');
include_once('admin/reports-downloads-list-table.php');
include_once('admin/reports-deleted-documents-list-table.php');
include_once('admin/reports-searched-list-table.php');
include_once('admin/reports-user-activity-list-table.php');
include_once('admin/reports-user-kickout-list-table.php');
include_once('admin/reports-utility-functions.php');
// include_once('public/reports-utility-functions-public-side.php');

// frontend documents list table files
include_once('admin/reports-frontend-documents-views-list-table.php');
include_once('admin/reports-frontend-documents-downloads-list-table.php');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-reports-activator.php
 */
function activate_reports() {
	global $wpdb;
	global $reports_db_version;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$downloads_table_name = $wpdb->prefix . 'reports_downloads';
	$downloads_sql = 'CREATE TABLE ' . $downloads_table_name . ' (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			post_id mediumint(9) NOT NULL,
			visitor_ip mediumtext NOT NULL,
			date_time datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
			user_id mediumint(9) NOT NULL,
			UNIQUE KEY id (id)
		);';
	dbDelta($downloads_sql);

	$frontend_doc_downloads = $wpdb->prefix . 'frontend_doc_downloads';
	$frontend_doc_downloads_sql = 'CREATE TABLE ' . $frontend_doc_downloads . ' (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			post_id mediumint(9) NOT NULL,
			visitor_ip mediumtext NOT NULL,
			user_id mediumint(9) NOT NULL,
			company_id mediumint(9) NOT NULL,
			account_manager varchar(255) NOT NULL,
			user_status mediumtext NOT NULL,
			date_time datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
			UNIQUE KEY id (id)
		);';
	dbDelta($frontend_doc_downloads_sql);

	$views_table_name = $wpdb->prefix . 'reports_views';
	$views_sql = 'CREATE TABLE ' . $views_table_name . ' (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			post_id mediumint(9) NOT NULL,
			visitor_ip mediumtext NOT NULL,
			date_time datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
			user_id mediumint(9) NOT NULL,
			UNIQUE KEY id (id)
		);';
	dbDelta($views_sql);

	$frontend_docs_views_table_name = $wpdb->prefix . 'frontend_doc_views';
	$frontend_docs_views_sql = 'CREATE TABLE ' . $frontend_docs_views_table_name . ' (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			post_id mediumint(9) NOT NULL,
			visitor_ip mediumtext NOT NULL,
			user_id mediumint(9) NOT NULL,
			company_id mediumint(9) NOT NULL,
			account_manager varchar(255) NOT NULL,
			user_status mediumtext NOT NULL,
			date_time datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
			UNIQUE KEY id (id)
		);';
	dbDelta($frontend_docs_views_sql);

	$activity_table_name = $wpdb->prefix . 'reports_activity';
	$activity_sql = 'CREATE TABLE ' . $activity_table_name . ' (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id mediumint(9) NOT NULL,
			views_count mediumint(9) NOT NULL,
			downloads_count mediumint(9) NOT NULL,
			activity_count mediumint(9) NOT NULL,
			UNIQUE KEY id (id)
		);';
	dbDelta($activity_sql);

	$search_results_table_name = $wpdb->prefix . 'search_results';
	$search_results_sql = 'CREATE TABLE ' . $search_results_table_name . ' (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			searched_query mediumtext NOT NULL,
			visitor_ip mediumtext NOT NULL,
			date_time datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
			user_id mediumint(9) NOT NULL,
			UNIQUE KEY id (id)
		);';
	dbDelta($search_results_sql);

	$kickout_users_table_name = $wpdb->prefix . 'kickout_users';
	$kickout_users_sql = 'CREATE TABLE ' . $kickout_users_table_name . ' (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id mediumint(9) NOT NULL,
			visitor_ip mediumtext NOT NULL,
			browser mediumtext NOT NULL,
			OS mediumtext NOT NULL,
			date_time datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
			UNIQUE KEY id (id)
		);';
	dbDelta($kickout_users_sql);

	$deleted_doc_table_name = $wpdb->prefix . 'reports_deleted_documents';
	$deleted_doc_sql = 'CREATE TABLE ' . $deleted_doc_table_name . ' (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		post_id mediumint(9) NOT NULL,
		post_title varchar(255) NOT NULL,
		user_id mediumint(9) NOT NULL,
		date_time datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
		UNIQUE KEY id (id)
	);';
	dbDelta($deleted_doc_sql);

    // Flush rules after install/activation
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'activate_reports' );
/*
 * * Handle Generic Init tasks
 */
add_action('admin_init', 'reports_init_time_tasks');
//add_action('admin_init', 'reports_admin_init_time_tasks');

function reports_init_time_tasks() {
    //Handle download request if any
    //handle_sdm_download_via_direct_post();
	//Register Google Charts library
	/*wp_register_script('reports_google_charts', 'https://www.gstatic.com/charts/loader.js', array(), null, true);
    wp_enqueue_script( 'reports_google_charts' );*/
/*	wp_register_style('reports_jquery_ui_style', REPORTS_URL . '/css/jquery.ui.min.css', array(), null, 'all');*/
    wp_enqueue_script( 'jquery-ui-datepicker' );
    //wp_enqueue_style( 'reports_jquery_ui_style' );
}

/*Get Operating system*/
function getOS() { 

    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $os_platform  = "Unknown OS Platform";

    $os_array     = array(
                          '/windows nt 10/i'      =>  'Windows 10',
                          '/windows nt 6.3/i'     =>  'Windows 8.1',
                          '/windows nt 6.2/i'     =>  'Windows 8',
                          '/windows nt 6.1/i'     =>  'Windows 7',
                          '/windows nt 6.0/i'     =>  'Windows Vista',
                          '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                          '/windows nt 5.1/i'     =>  'Windows XP',
                          '/windows xp/i'         =>  'Windows XP',
                          '/windows nt 5.0/i'     =>  'Windows 2000',
                          '/windows me/i'         =>  'Windows ME',
                          '/win98/i'              =>  'Windows 98',
                          '/win95/i'              =>  'Windows 95',
                          '/win16/i'              =>  'Windows 3.11',
                          '/macintosh|mac os x/i' =>  'Mac OS X',
                          '/mac_powerpc/i'        =>  'Mac OS 9',
                          '/linux/i'              =>  'Linux',
                          '/ubuntu/i'             =>  'Ubuntu',
                          '/iphone/i'             =>  'iPhone',
                          '/ipod/i'               =>  'iPod',
                          '/ipad/i'               =>  'iPad',
                          '/android/i'            =>  'Android',
                          '/blackberry/i'         =>  'BlackBerry',
                          '/webos/i'              =>  'Mobile'
                    );

    foreach ($os_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $os_platform = $value;

    return $os_platform;
}
/*Get Web Browser*/
function getBrowser() {

    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $browser        = "Unknown Browser";

    $browser_array = array(
                            '/msie/i'      => 'Internet Explorer',
                            '/firefox/i'   => 'Firefox',
                            '/safari/i'    => 'Safari',
                            '/chrome/i'    => 'Chrome',
                            '/edge/i'      => 'Edge',
                            '/opera/i'     => 'Opera',
                            '/netscape/i'  => 'Netscape',
                            '/maxthon/i'   => 'Maxthon',
                            '/konqueror/i' => 'Konqueror',
                            '/mobile/i'    => 'Handheld Browser'
                     );

    foreach ($browser_array as $regex => $value)
        if (preg_match($regex, $user_agent))
            $browser = $value;

    return $browser;
}


function download_file() {
    $id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
    global $wpdb;

	$download_id = absint($id);
	//Do some validation checks
	if ($download_id == 0) {
		$response = 'incorrect id';
	} else {
		$ipaddress = reports_get_ip_address();
		$date_time = current_time('mysql');
		$current_user = wp_get_current_user();
		$visitor_id = $current_user->ID;
		$allowed_roles = array('business', 'subscriber_-_admin_facility', 'subscriber_-_corporate' , 'subscriber_-_facility_user');
		if( array_intersect($allowed_roles, $current_user->roles ) ) {
			// We need to log this download.
		    $table = $wpdb->prefix . 'reports_downloads';
		    $activity_table = $wpdb->prefix . 'reports_activity';
		    $data = array(
				'post_id' => $download_id,
				'visitor_ip' => $ipaddress,
				'date_time' => $date_time,
				'user_id' => $visitor_id
		    );

		    $data = array_filter($data); //Remove any null values.
		    $insert_table = $wpdb->insert($table, $data);
		    if ($insert_table) {
				$response = 'successfully logged to views table';
		    } else {
				$response = 'failed to log in downlods table';
		    }
	     	$query = "SELECT * FROM $activity_table WHERE user_id='$visitor_id'";
	     	$data_results = $wpdb->get_results($query);

     		if(count($data_results) !== 0) {
	     		$wpdb->query("UPDATE `$activity_table` SET `downloads_count`=`downloads_count`+1 WHERE `user_id`='$visitor_id'");
	     		$wpdb->query("UPDATE `$activity_table` SET `activity_count`=`activity_count`+1 WHERE `user_id`='$visitor_id'");
	     		$response = 'successfully logged to activity table';
	     	} else {

	     		$activity_data = array(
					'user_id' => $visitor_id,
					'views_count' => '0',
					'downloads_count' => '1',
					'activity_count' => '1'
				);
				$activity_data = array_filter($activity_data); //Remove any null values.
		    		$insert_activity_table = $wpdb->insert($activity_table, $activity_data);
			    if ($insert_activity_table) {
					$response = 'successfully logged a new entry to activity table';
			    } else {
					$response = 'failed to log in activity table';
			    }
		    }

		}
		else {
			$response = 'user not allowed';
		}

	}

    echo $response;
    die();
}
add_action( 'wp_ajax_download_file', 'download_file' );

function view_file() {

	global $wpdb;
	$get_id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : '');
	$extract_id = explode('-', $get_id);
	$id = $extract_id[1];

	$view_id = absint($id);
	//Do some validation checks
	if (!$view_id) {
		$response = 'incorrect id';
	}

	$ipaddress = reports_get_ip_address();
	$date_time = current_time('mysql');
	//$visitor_name = reports_get_logged_in_user();
	$current_user = wp_get_current_user();
	$visitor_id = $current_user->ID;
	$allowed_roles = array('business', 'subscriber_-_admin_facility', 'subscriber_-_corporate' , 'subscriber_-_facility_user');
	
	if( array_intersect($allowed_roles, $current_user->roles ) ) {
		// We need to log this view.
		$table = $wpdb->prefix . 'reports_views';
		$activity_table = $wpdb->prefix . 'reports_activity';
		$data = array(
			'post_id' => $view_id,
			'visitor_ip' => $ipaddress,
			'date_time' => $date_time,
			'user_id' => $visitor_id
		);
		$data = array_filter($data); //Remove any null values.
		$insert_table = $wpdb->insert($table, $data);
		if ($insert_table) {
				$response = 'success';
		} else {
			$response = 'failed to log in views table';
	    	}
	    	$query = "SELECT * FROM $activity_table WHERE user_id='$visitor_id'";
     		$data_results = $wpdb->get_results($query);
     		if(count($data_results) !== 0) {
	 		$wpdb->query("UPDATE `$activity_table` SET `views_count`=`views_count`+1 WHERE `user_id`='$visitor_id'");
	 		$wpdb->query("UPDATE `$activity_table` SET `activity_count`=`activity_count`+1 WHERE `user_id`='$visitor_id'");
	 		$response = 'successfully logged to activity table';
	 	} else {
	 		$activity_data = array(
				'user_id' => $visitor_id,
				'views_count' => '1',
				'downloads_count' => '0',
				'activity_count' => '1'
			);
			$activity_data = array_filter($activity_data); //Remove any null values.
	    		$insert_activity_table = $wpdb->insert($activity_table, $activity_data);
		    if ($insert_activity_table) {
				$response = 'successfully logged a new entry to activity table';
		    } else {
				$response = 'failed to log in activity table';
		    }
		}
	}
	else {
		$response = 'user not allowed';
	}

    echo $response;
    die();
}
add_action( 'wp_ajax_view_file', 'view_file' );

/*Insert search in DB*/
function save_search( $query ) {
    global $wpdb;
    if ($query->is_search && !is_admin()) {
      $searched_query = get_search_query();
    	$ipaddress = reports_get_ip_address();
	$date_time = current_time('mysql');
    	$current_user = wp_get_current_user();
    	$visitor_id = $current_user->ID;
    	$allowed_roles = array('business', 'subscriber_-_admin_facility', 'subscriber_-_corporate' , 'subscriber_-_facility_user');
    	if( array_intersect($allowed_roles, $current_user->roles ) && !is_paged()) {
			// We need to log this search query.
		    $table = $wpdb->prefix . 'search_results';
		    $data = array(
				'searched_query' => $searched_query,
				'visitor_ip' => $ipaddress,
				'date_time' => $date_time,
				'user_id' => $visitor_id
		    );

		    $data = array_filter($data); //Remove any null values.
		    $insert_table = $wpdb->insert($table, $data);
		}
    }
}
add_action( 'pre_get_posts', 'save_search' );


// Allow only one logged in session for WordPress users
/*function wp_destroy_all_other_sessions() {
    $token = wp_get_session_token();
    if ( $token ) {
        global $wpdb;
    	if ( ! is_super_admin() || ! is_admin() ) {
	        $manager = WP_Session_Tokens::get_instance( get_current_user_id() );
	        $get_all = $manager->get_all();
	        $count = count($get_all);
	        if ($count>= 2) {
				//We need to log this kickout user.
			    $table = $wpdb->prefix . 'kickout_users';
			    $data = array(
					'user_id' => get_current_user_id(),
					'visitor_ip' => reports_get_ip_address(),
					'browser' => getBrowser(),
					'OS' => getOS(),
					'date_time' => current_time('mysql'),
			    );
			    $data = array_filter($data); //Remove any null values.
			    $insert_table = $wpdb->insert($table, $data);
			    //destroy other sessions
	        	$manager->destroy_others( $token );
	        }
		}
    }
}
add_action('init', 'wp_destroy_all_other_sessions');
*/

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-reports-deactivator.php
 */
function deactivate_reports() {
/*	global $wpdb;
	$wpdb->query("DROP TABLE " . $wpdb->prefix . "reports_downloads");
	$wpdb->query("DROP TABLE " . $wpdb->prefix . "reports_views");
	deactivate_plugins(plugin_basename(__FILE__));
    flush_rewrite_rules(false);
    wp_die();*/
	/*require_once plugin_dir_path( __FILE__ ) . 'includes/class-reports-deactivator.php';
	Reports_Deactivator::deactivate();*/
}
register_deactivation_hook( __FILE__, 'deactivate_reports' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-reports.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_reports() {

	$plugin = new Reports();
	$plugin->run();

}
run_reports();

// Houston... we have lift-off!!
class reportsManager {

    public function __construct() {

    	add_action('wp_enqueue_scripts', array($this, 'reports_frontend_scripts'));  // Register frontend scripts

    	if (is_admin()) {
	    add_action('admin_menu', array($this, 'reports_create_menu_pages'));  // Create admin pages

	    //add_action('add_meta_boxes', array($this, 'sdm_create_upload_metabox'));  // Create metaboxes

	    //add_action('save_post', array($this, 'sdm_save_description_meta_data'));  // Save 'description' metabox
	    /*add_action('save_post', array($this, 'sdm_save_upload_meta_data'));  // Save 'upload file' metabox
	    add_action('save_post', array($this, 'sdm_save_dispatch_meta_data'));  // Save 'dispatch' metabox
	    add_action('save_post', array($this, 'sdm_save_misc_properties_meta_data'));  // Save 'misc properties/settings' metabox
	    add_action('save_post', array($this, 'sdm_save_thumbnail_meta_data'));  // Save 'thumbnail' metabox
	    add_action('save_post', array($this, 'sdm_save_statistics_meta_data'));  // Save 'statistics' metabox
	    add_action('save_post', array($this, 'sdm_save_other_details_meta_data'));  // Save 'other details' metabox*/

	    //add_action('admin_enqueue_scripts', array($this, 'sdm_admin_scripts'));  // Register admin scripts
	    //add_action('admin_print_styles', array($this, 'sdm_admin_styles'));  // Register admin styles

	    //add_action('admin_init', array($this, 'sdm_register_options'));  // Register admin options
	    //add_filter('post_row_actions', array($this, 'sdm_remove_view_link_cpt'), 10, 2);  // Remove 'View' link in all downloads list view
	    
        /*add_filter('page_row_actions', array($this, 'sdm_add_clone_record_btn'), 10, 2);  // Add 'Clone' link in all downloads list view
        add_filter('post_row_actions', array($this, 'sdm_add_clone_record_btn'), 10, 2);  // Add 'Clone' link in all downloads list view*/
    
            //add_action('admin_action_sdm_clone_post', array($this, 'sdm_action_clone_post'));
		}
        
    }


    	public function reports_frontend_scripts() {
		//Use this function to enqueue fron-end js scripts.
		/*	wp_enqueue_style('reports-styles', REPORTS_URL . '/css/reports_admin.css');
		wp_register_script('reports-scripts', REPORTS_URL . '/js/reports_admin.js', array('jquery'));
		wp_enqueue_script('reports-scripts');*/
		
		//Check if reCAPTCHA is enabled.
		/*$main_advanced_opts = get_option('reports_advanced_options');
		$recaptcha_enable = isset($main_advanced_opts['recaptcha_enable']) ? true : false;
		if ($recaptcha_enable) {
		wp_register_script('reports-recaptcha-scripts-js', REPORTS_URL . '/js/sdm_g_recaptcha.js', array(), true);
		wp_localize_script("reports-recaptcha-scripts-js", "sdm_recaptcha_opt", array("site_key" => $main_advanced_opts['recaptcha_site_key']));
		wp_register_script('reports-recaptcha-scripts-lib',  "//www.google.com/recaptcha/api.js?hl=".get_locale()."&onload=sdm_reCaptcha&render=explicit", array(), false);
		wp_enqueue_script('reports-recaptcha-scripts-js');
		wp_enqueue_script('reports-recaptcha-scripts-lib');
		}*/

		// Localize ajax script for frontend
		wp_localize_script('reports-scripts', 'reports_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php')));
	}

	public function reports_create_menu_pages() {
		include_once('includes/reports-admin-menu-handler.php');
		report_handle_admin_menu();
	}

}

//End of reportsManager class
//Initialize the reportsManager class
$reportsManager = new reportsManager();