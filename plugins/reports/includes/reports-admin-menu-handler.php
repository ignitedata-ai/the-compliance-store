<?php

/*
 * Creates/adds the other admin menu page links to the main Reports menu
 */

function report_handle_admin_menu() {
//*****  Create the 'logs' and 'settings' submenu pages

    add_menu_page( 'Documents Views', 'All Reports', 'manage_options', 'views-reports',
        'reports_create_view_page', 'dashicons-chart-pie', 16 );
    add_submenu_page( 'views-reports', __( 'Documents Views', 'reports' ), __( 'Documents Views', 'reports' ), 'manage_options', 'views-reports', 'reports_create_view_page' );
    add_submenu_page( 'views-reports', __( 'Frontend Documents Views', 'reports' ), __( 'Frontend Documents Views', 'reports' ), 'manage_options', 'frontend-document-views-report', 'frontend_documents_views_report_handler' );
    add_submenu_page( 'views-reports', __( 'Documents Downloads', 'reports' ), __( 'Documents Downloads', 'reports' ), 'manage_options', 'download-reports', 'reports_create_download_page' );
    add_submenu_page( 'views-reports', __( 'Frontend Documents Downloads', 'reports' ), __( 'Frontend Documents Downloads', 'reports' ), 'manage_options', 'frontend-document-downloads-report', 'frontend_documents_downloads_report_handler' );
    add_submenu_page( 'views-reports', __( 'Frontend Documents Deleted History', 'reports' ), __( 'Frontend Documents Deleted History', 'reports' ), 'manage_options', 'frontend-documents-deleted-history', 'frontend_documents_deleted_history' );
    add_submenu_page( 'views-reports', __( 'Searched Queries', 'reports' ), __( 'Searched Queries', 'reports' ), 'manage_options', 'search-reports', 'reports_create_search_page' );
    add_submenu_page( 'views-reports', __( 'Documents Date Posted', 'reports' ), __( 'Documents Date Posted', 'reports' ), 'manage_options', 'date-posted-reports', 'documents_date_posted_page' );
    add_submenu_page( 'views-reports', __( 'Kickout Users', 'reports' ), __( 'Kickout Users', 'reports' ), 'manage_options', 'kickout-reports', 'reports_create_kickout_page' );
    add_submenu_page( 'views-reports', __( 'Users Activity', 'reports' ), __( 'Users Activity', 'reports' ), 'manage_options', 'users-activity-reports', 'reports_create_user_activity_page' );
    add_submenu_page( 'views-reports', __( 'Export Last Modified', 'reports' ), __( 'Export Last Modified', 'reports' ), 'manage_options', 'export-last-modified-reports', 'reports_export_last_modified' );

}

add_action( 'admin_menu', 'report_handle_admin_menu' );


/*
 * * Reports Main page
 */
function reports_create_main_page(){
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';
            
    
    echo '</div>';//<!-- end of wrap -->
}
/*
 * * View Reports page
 */
function reports_create_view_page(){
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';

    $views_menu_tabs = array(
        'views-reports' => __('Main View Logs', 'reports'),
        'views-reports&action=view-reports-export' => __('Export View Reports', 'reports'),
        'views-reports&action=reports-logs-by-views' => __('Specific Item Logs', 'reports'),
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
            case 'view-reports-export':
                export_documents_views();
                break;
            case 'reports-logs-by-views':
                get_document_view_report_by_id();
                break;
            default:
                get_document_views();
                break;
        }
    } else {
        get_document_views();
    }
            
    echo '</div>';//<!-- end of wrap -->
}

/**
 * Frontend documents views report
 */

function frontend_documents_views_report_handler(){

    $current = '';
    $content = '';

    if ( !current_user_can('manage_options') ) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';

    $views_menu_tabs = array(
        'frontend-document-views-report' => __('Views Logs', 'reports'),
        'frontend-document-views-report&action=export-views' => __('Export View Reports', 'reports'),
        'frontend-document-views-report&action=specific-item-report' => __('Specific Item Logs', 'reports'),
    );

    if (isset($_GET['page'])) {
        $current = sanitize_text_field($_GET['page']);
        if (isset($_GET['action'])) {
            $current .= "&action=" . sanitize_text_field($_GET['action']);
        }
    }

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

    // define actions for tabs
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'export-views':
                export_documents_views();  // function defined inside the reports-utility-functions-admin-side.php
                break;
            case 'specific-item-report':
                get_document_view_report_by_id(); // function defined inside the reports-utility-functions-admin-side.php
                break;
            default:
                get_document_views(); // function defined inside the reports-utility-functions-admin-side.php
                break;
        }
    } else {
        get_document_views(); // function defined inside the reports-utility-functions-admin-side.php
    }
    
    echo '</div>';
}

/**
 * Frontend documents downloads report
 */

function frontend_documents_downloads_report_handler(){

    $current = '';
    $content = '';

    if ( !current_user_can('manage_options') ) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';

    $views_menu_tabs = array(
        'frontend-document-downloads-report' => __('Downloads Logs', 'reports'),
        'frontend-document-downloads-report&action=export-downloads' => __('Export Download Reports', 'reports'),
        'frontend-document-downloads-report&action=specific-item-report' => __('Specific Item Logs', 'reports'),
    );

    if (isset($_GET['page'])) {
        $current = sanitize_text_field($_GET['page']);
        if (isset($_GET['action'])) {
            $current .= "&action=" . sanitize_text_field($_GET['action']);
        }
    }

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

    // define actions for tabs
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'export-downloads':
                export_documents_downloads();  // function defined inside the reports-utility-functions-admin-side.php
                break;
            case 'specific-item-report':
                get_document_download_report_by_id(); // function defined inside the reports-utility-functions-admin-side.php
                break;
            default:
                get_document_downloads(); // function defined inside the reports-utility-functions-admin-side.php
                break;
        }
    } else {
        get_document_downloads(); // function defined inside the reports-utility-functions-admin-side.php
    }
    
    echo '</div>';
}

/*
 * * Download Reports menu page
 */
function reports_create_download_page(){
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';
            
    $downloads_menu_tabs = array(
        'download-reports' => __('Main Download Logs', 'reports'),
        'download-reports&action=download-reports-export' => __('Export Download Reports', 'reports'),
        'download-reports&action=reports-logs-by-download' => __('Specific Item Logs', 'reports'),
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
    foreach ($downloads_menu_tabs as $location => $tabname) {
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
            case 'download-reports-export':
                export_documents_downloads();
                break;
            case 'reports-logs-by-download':
                get_document_download_report_by_id();
                break;
	    default:
                get_document_downloads();
                break;
        }
    } else {
        get_document_downloads();
    }
    
    echo '</div>';//<!-- end of wrap -->
}

/**
 * Frontend Documents Deleted History
 */

function frontend_documents_deleted_history(){

    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';
    
        deleted_documents_log_handler(); // function is inside the reports-utility-functions-admin-side.php
    
    echo '</div>';//<!-- end of wrap -->
}
/*
 * * Search Reports page
 */
function reports_create_search_page(){
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';

    $search_results_menu_tabs = array(
        'search-reports' => __('Main Search Logs', 'reports'),
        'search-reports&action=tally-search-logs' => __('Tally Search Logs', 'reports'),
        'search-reports&action=search-reports-export' => __('Export Search Reports', 'reports'),
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
    foreach ($search_results_menu_tabs as $location => $tabname) {
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
            case 'search-reports-export':
                reports_search_results_export_tab_page();
                break;
        default:
                reports_search_results_main_tab_page();
                break;
        }
    } else {
        reports_search_results_main_tab_page();
    }
            
    
    echo '</div>';//<!-- end of wrap -->
}
/*
 * * User Reports page
 */
function reports_create_user_activity_page(){

    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';

    $views_menu_tabs = array(
        'users-activity-reports' => __('Users Activity Logs', 'reports'),
        'users-activity-reports&action=users-activity-export' => __('Export Users Activity Reports', 'reports'),
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
            case 'users-activity-export':
                reports_handle_user_activity_export_tab_page();
                break;
        default:
                reports_user_activity_reports();
                break;
        }
    } else {
        reports_user_activity_reports();
    }
            
    
    echo '</div>';//<!-- end of wrap -->
}
/*
 * * Kickout Reports page
 */
function reports_create_kickout_page(){
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';

    $views_menu_tabs = array(
        'kickout-reports' => __('Users Kickout Reports', 'reports'),
        'kickout-reports&action=users-kickout-export' => __('Export Users Kickout Reports', 'reports'),
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
            case 'users-kickout-export':
                reports_handle_user_kickout_export_tab_page();
                break;
        default:
                reports_user_kickout_reports();
                break;
        }
    } else {
        reports_user_kickout_reports();
    }
            
    
    echo '</div>';//<!-- end of wrap -->
}
/*
 * * Create Last Modified Export page 
 */
function reports_export_last_modified(){
    if (!current_user_can('administrator')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';

    $views_menu_tabs = array(
        'export-last-modified-reports' => __('Export Documents Last Modified', 'reports'),
        'export-last-modified-reports&action=company-last-modified' => __('Export Company Last Modified', 'reports'),
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
            case 'company-last-modified':
                reports_handle_company_last_modified_export_tab_page();
                break;
        default:
                reports_handle_documents_last_modified_export_tab_page();
                break;
        }
    } else {
        reports_handle_documents_last_modified_export_tab_page();
    }
            
    
    echo '</div>';//<!-- end of wrap -->
}
/*
 * * Documents Date posted reports page
 */
function documents_date_posted_page(){
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';
            
    export_documents_by_date_posted();

    echo '</div>';//<!-- end of wrap -->
}