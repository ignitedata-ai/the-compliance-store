<?php

/*
 * Creates/adds the other admin menu page links to the main Reports menu
 */

function delete_disabled_users_handle_admin_menu() {
//*****  Create the 'logs' and 'settings' submenu pages

    /*add_menu_page( 'Disabled Users', 'Disabled Users', 'manage_options', 'delete-disabled-users',
        'delete_disabled_users_main_page', 'dashicons-chart-pie', 16 );*/
    add_submenu_page(
        'users.php',
        'Disabled Users',
        'Disabled Users',
        'manage_options',
        'delete-disabled-users',
        'delete_disabled_users_main_page',
    );

}
/*die();*/

/*
 * * View Reports page
 */
function delete_disabled_users_main_page(){
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';

    /*$views_menu_tabs = array(
        'views-reports' => __('Main View Logs', 'reports'),
        'views-reports&action=view-reports-export' => __('Export View Reports', 'reports'),
        'views-reports&action=reports-logs-by-views' => __('Specific Item Logs', 'reports'),
    );*/

    /*$current = "";
    if (isset($_GET['page'])) {
        $current = sanitize_text_field($_GET['page']);
        if (isset($_GET['action'])) {
            $current .= "&action=" . sanitize_text_field($_GET['action']);
        }
    }*/
    $content = '';
    $content .= '<h2 class="nav-tab-wrapper">';
    /*foreach ($views_menu_tabs as $location => $tabname) {
        if ($current == $location) {
            $class = ' nav-tab-active';
        } else {
            $class = '';
        }
        $content .= '<a class="nav-tab' . $class . '" href="?page=' . $location . '">' . $tabname . '</a>';
    }*/
    $content .= '</h2>';
    echo $content;


    delete_disabled_users_handle_main_page();
    
    /*if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'view-reports-export':
                reports_handle_views_export_tab_page();
                break;
            case 'reports-logs-by-views':
                reports_handle_individual_views_logs_tab_page();
                break;
        default:
                reports_handle_views_main_tab_page();
                break;
        }
    } else {
        reports_handle_views_main_tab_page();
    }*/
            
    
    echo '</div>';//<!-- end of wrap -->
}