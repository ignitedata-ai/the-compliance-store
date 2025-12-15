<?php

// Load main class
require_once dirname(dirname(__FILE__)).'/admin/admin.php';

/**
 * Admin class
 *
 * @package WP Link Status Pro
 * @subpackage Admin
 */
class WPLNST_Admin_Pro extends WPLNST_Admin {



	// Initialization
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Creates a singleton object
	 */
	public static function instantiate($args = null) {
		return self::get_instance(get_class(), $args);
	}



	/**
	 * Enqueue specific versions scripts
	 */
	protected function admin_enqueue_version() {

		// Commmon admin styles
		wp_enqueue_style( 'wplnst-admin-pro-css', plugins_url('assets-pro/css/admin-pro.css', WPLNST_FILE), array(), $this->script_version);

		// Admin script version
		wp_enqueue_script('wplnst-admin-pro-script', plugins_url('assets-pro/js/admin-pro.js', WPLNST_FILE), array('jquery'), $this->script_version, true);

		// URL tools scripts
		if (WPLNST_Core_Pro_Plugin::slug.'-tools-url' == $_GET['page']) {
			wp_enqueue_script('wplnst-admin-script-tools-url', plugins_url('assets-pro/js/admin-tools-url.js', WPLNST_FILE), array('jquery', 'json2'), $this->script_version, true);
		}
	}



	// Menu hooks
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Admin menu utilities
	 */
	protected function admin_menu_utilities() {
		add_submenu_page(WPLNST_Core_Pro_Plugin::slug, __('URL Tools', 'wplnst'), __('URL Tools', 'wplnst'), WPLNST_Core_Pro_Plugin::capability, WPLNST_Core_Pro_Plugin::slug.'-tools-url', array(&$this, 'admin_menu_tools_url'));
	}



	/**
	 * Admin menu addons
	 */
	protected function admin_menu_addons() {}


	public function link_checker_scans() {

		if (!current_user_can('manage_options')) {
	        wp_die('You do not have permission to access this settings page.');
	    }

	    echo '<div class="wrap">';

	    $views_menu_tabs = array(
	        'wp-link-status' => __('All Scans', 'reports'),
	        'wp-link-status&action=wp-link-status-new-scan' => __('New Scans', 'reports'),
	        'wp-link-status&action=wp-link-status-tools-url' => __('URL Tools', 'reports'),
	        'wp-link-status&action=wp-link-status-settings' => __('Settings', 'reports'),
	    );

	    $current = "";
	    if (isset($_GET['page'])) {
	        $current = sanitize_text_field($_GET['page']);
	        if (isset($_GET['action'])) {
	            $current .= "&action=" . sanitize_text_field($_GET['action']);
	        }
	    }
	    $content = '';
	    $content .= '<h2 class="nav-tab-wrapper">';
	    foreach ($views_menu_tabs as $location => $tabname) {
	        if ($current == $location) {
	            $class = ' nav-tab-active';
	        } else {
	            $class = '';
	        }
	        $content .= '<a class="nav-tab' . $class . '" href="?page=' . $location . '">' . $tabname . '</a>';
	    }
	    $content .= '</h2>';
	    echo $content;
	    
	    if (isset($_GET['action'])) {
	        switch ($_GET['action']) {
	            case 'wp-link-status-new-scan':
	                wplnst_require('admin-pro', 'scans');
					new WPLNST_Admin_Pro_Scans($this, 'edit');
	                break;
                case 'wp-link-status-tools-url':
	                wplnst_require('admin-pro', 'tools-url');
					new WPLNST_Admin_Pro_Tools_URL($this);
	                break;
	            case 'wp-link-status-settings':
	                wplnst_require('admin', 'settings');
					new WPLNST_Admin_Settings($this);
	                break;
	        default:
	                wplnst_require('admin-pro', 'scans');
					new WPLNST_Admin_Pro_Scans($this, 'context');
	                break;
	        }
	    } else {
	       	wplnst_require('admin-pro', 'scans');
			new WPLNST_Admin_Pro_Scans($this, 'context');
	    }
	            
	    
	    echo '</div>';//<!-- end of wrap -->

	}



	/**
	 * Scans common page
	 */
	public function admin_menu_scans() {
	
    	wplnst_require('admin-pro', 'scans');
		new WPLNST_Admin_Pro_Scans($this, 'context');
	}



	/*
	 * New or edit scan page
	 */
	public function admin_menu_scans_new() {
		wplnst_require('admin-pro', 'scans');
		new WPLNST_Admin_Pro_Scans($this, 'edit');
	}



	/**
	 * Section for URL tools
	 */
	public function admin_menu_tools_url() {
		wplnst_require('admin-pro', 'tools-url');
		new WPLNST_Admin_Pro_Tools_URL($this);
	}



	// AJAX handlers
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Update results data
	 */
	public function ajax_results_update() {

		// Load dependencies
		wplnst_require('core-pro', 'results');

		// Instantiate and self start start processes
		WPLNST_Core_Pro_Results::instantiate();
	}



	/**
	 * Open or close advanced panel
	 */
	public function ajax_results_advanced_display() {

		// Check input
		if (!isset($_POST['display']) || !in_array($_POST['display'], array('off', 'on'))) {
			return;
		}

		// Check nonce
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wplnst-results-advanced-display')) {
			return;
		}

		// Update
		update_user_meta(get_current_user_id(), 'wplnst_advanced_search', $_POST['display']);
	}



	/**
	 * Handler for tools URL
	 */
	public function ajax_tools_url() {

		// Load dependencies
		wplnst_require('core-pro', 'tools-url-update');

		// Instantiate and self start start processes
		$tools = WPLNST_Core_Pro_Tools_URL_Update::instantiate();
	}



	// Utilities
	// ---------------------------------------------------------------------------------------------------



	/**
	 * Return plugin title for screen view
	 */
	protected function get_plugin_title() {
		return 'Broken Links Reports';
	}



}