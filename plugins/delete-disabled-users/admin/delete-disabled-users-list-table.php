<?php

//*****
//*****  Check WP_List_Table exists
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//*****
//*****  Define our new Table
class disabled_users_List_Table extends WP_List_Table {

    function __construct() {

        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => __('Disabled User', 'reports'), //singular name of the listed records
            'plural' => __('Disabled Users', 'reports'), //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));
    }

    function column_default($item, $column_name) {
        
        switch ($column_name) {
            //case 'cb':
            case 'email':
            case 'name':
            case 'user-role':
            case 'business':
            case 'corporate':
            case 'admin':
            case 'account_manager':
            case 'disabled_by':
            case 'last_login':
            case 'export_logs':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_name($item) {
        $delete_log_nonce = wp_create_nonce('disabled_users_delete_log_entry');
        //Build row actions
        $actions = array(
                'delete' => sprintf('<a href="?page=delete-disabled-users&action=%s&&row_id=%s&_wpnonce=%s" onclick="return confirm(\'Are you sure you want to delete this user data and all of its logs?\')">' . __('Delete User', 'reports') . '</a>',
                'delete',
                $item['row_id'],
                $delete_log_nonce
            ),
        );

        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(User id:%2$s)</span>%3$s',
                /* $1%s */ $item['name'],
                /* $2%s */ $item['row_id'],
                /* $3%s */ $this->row_actions($actions)
        );
    }

    function column_cb($item) {

        return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                /* $1%s */ $this->_args['singular'], //Let's simply repurpose the table's singular label ("views")
                /* $2%s */ $item['row_id'] //The value of the checkbox should be the record's id
        );
    }

    function get_columns() {
        
        $columns['cb'] = '<input type="checkbox" />';
        $columns['name'] = __('Name', 'reports');
        $columns['email'] = __('Email', 'reports');
        $columns['user-role'] = __('Role', 'reports');
        $columns['business'] =   __('Business', 'reports');
        $columns['corporate'] =   __('Corporate Facility', 'reports');
        $columns['admin'] =   __('Admin Facility', 'reports');
        $columns['account_manager'] = __('Account Manager', 'reports');
        $columns['disabled_by'] = __('Disabled by', 'reports');
        $columns['last_login'] = __('Last Login', 'reports');
        $columns['export_logs'] = __('Export Userdata & Logs', 'reports');
        
        return $columns;
    }

    function get_sortable_columns() {

        $sortable_columns = array(
            'last_login' => array('last_login', false),//true means it's already sorted
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {

        $actions = array();
        $actions['delete2'] = __('Delete Permanently', 'reports');

        return $actions;
    }

    function process_bulk_action() {

        global $wpdb;
        $views_table_name = $wpdb->prefix . 'reports_views';
        $downloads_table_name = $wpdb->prefix . 'reports_downloads';
        $activity_table_name = $wpdb->prefix . 'reports_activity';
        $search_table_name = $wpdb->prefix . 'search_results';
        $kickout_table_name = $wpdb->prefix . 'kickout_users';
        $user_logins_table_name = $wpdb->prefix . 'fa_user_logins';


        // if bulk 'Delete Permanently' was clicked
        if ('delete2' === $this->current_action()) {

            //Check bulk delete nonce
            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            if (!isset($_POST['disableduser']) || $_POST['disableduser'] == null) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('No entries were selected.', 'reports') . '</strong></p><p><em>' . __('Click to Dismiss', 'reports') . '</em></p></div>';
                return;
            }
            

            foreach ($_POST['disableduser'] as $item) {
                $user_id = sanitize_text_field($item);
                if (!is_numeric($user_id)){
                    wp_die(__('Error! The row id value of a log entry must be numeric.', 'reports'));
                }

                $view_del = $wpdb->query('DELETE FROM ' . $views_table_name .' WHERE user_id = "' . $user_id . '"');
                $download_del = $wpdb->query('DELETE FROM ' . $downloads_table_name . ' WHERE user_id = "' . $user_id . '"');
                $search_del = $wpdb->query('DELETE FROM ' . $search_table_name . ' WHERE user_id = "' . $user_id . '"');
                $kickout_del = $wpdb->query('DELETE FROM ' . $kickout_table_name . ' WHERE user_id = "' . $user_id . '"');
                $activity_table_del = $wpdb->query('DELETE FROM ' . $activity_table_name . ' WHERE user_id = "' . $user_id . '"');
                $user_logins_del = $wpdb->query('DELETE FROM ' . $user_logins_table_name . ' WHERE user_id = "' . $user_id . '"');

                $delete_user = wp_delete_user( $user_id );
            }
            
            echo '<div id="message" class="updated fade">';

            if ($view_del) {
                echo '<p><strong>Document Views Logs Deleted!</strong></p>';
            }
            if ($download_del) {
                echo '<p><strong>Document Downloads Logs Deleted!</strong></p>';
            }
            if ($search_del) {
                echo '<p><strong>Searched Logs Deleted!</strong></p>';
            }
            if ($kickout_del) {
                echo '<p><strong>Kickout Logs Deleted!</strong></p>';
            }
            if ($user_logins_del) {
                echo '<p><strong>Login History Deleted!</strong></p>';
            }

            if ($delete_user) {
                echo '<p><strong>User Deleted!</strong></p>';
            } else {
                echo '<p><strongError</strong></p>';

            }

            echo '<p><em>Click to Dismiss</em></div>';
        }

        // If single entry 'Delete' was clicked
        elseif ('delete' === $this->current_action()) {

            //Check bulk delete nonce
            $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'disabled_users_delete_log_entry';
            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            //Grab the row id
            $user_id = filter_input(INPUT_GET, 'row_id', FILTER_SANITIZE_STRING);
            
            $view_del = $wpdb->query('DELETE FROM ' . $views_table_name .' WHERE user_id = "' . $user_id . '"');
            $download_del = $wpdb->query('DELETE FROM ' . $downloads_table_name . ' WHERE user_id = "' . $user_id . '"');
            $search_del = $wpdb->query('DELETE FROM ' . $search_table_name . ' WHERE user_id = "' . $user_id . '"');
            $kickout_del = $wpdb->query('DELETE FROM ' . $kickout_table_name . ' WHERE user_id = "' . $user_id . '"');
            $activity_table_del = $wpdb->query('DELETE FROM ' . $activity_table_name . ' WHERE user_id = "' . $user_id . '"');
            $user_logins_del = $wpdb->query('DELETE FROM ' . $user_logins_table_name . ' WHERE user_id = "' . $user_id . '"');

            $delete_user = wp_delete_user( $user_id );

            echo '<div id="message" class="updated fade">';

            if ($view_del) {
                echo '<p><strong>Document Views Logs Deleted!</strong></p>';
            }
            if ($download_del) {
                echo '<p><strong>Document Downloads Logs Deleted!</strong></p>';
            }
            if ($search_del) {
                echo '<p><strong>Searched Logs Deleted!</strong></p>';
            }
            if ($kickout_del) {
                echo '<p><strong>Kickout Logs Deleted!</strong></p>';
            }
            if ($user_logins_del) {
                echo '<p><strong>Login History Deleted!</strong></p>';
            }

            if ($delete_user) {
                echo '<p><strong>User Deleted!</strong></p>';
            } else {
                echo '<p><strongError</strong></p>';

            }

            echo '<p><em>Click to Dismiss</em></div>';
        }

        elseif ('export-disabled-user' === $this->current_action()) {

            //Grab the row id
            $user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_STRING);
            $user = get_user_by( 'id', $user_id );
            if ($user) {

                $resultset = $wpdb->get_results("SELECT user_id,views_count,downloads_count,activity_count FROM `$activity_table_name` WHERE user_id = '$user_id'");

                $csv_file_path = DELETE_DISABLED_USERS_PATH . "disabled-user.csv";
                $fp = fopen($csv_file_path, 'w');

                $header_names = array(
                    "First Name",
                    "Last Name",
                    "User Status",
                    "Tools Templates Access",
                    "Work Phone",
                    "Email",
                    "Street",
                    "Street 2",
                    "City",
                    "State",
                    "Zip Code",
                    "Role (Slug)",
                    "Business Member",
                    "Corporate Facility",
                    "Admin Facility",
                    "Account Manager",
                    "Creation Date",
                    "Start Date",
                    "Number of Documents Viewed",
                    "Viewed Documents (Document ID | Document Title | Viewed Date)",
                    "Number of Documents Downloaded",
                    "Downloaded Documents (Document ID | Document Title | Viewed Date)",
                );
                fputcsv($fp, $header_names);
                foreach ($resultset as $result) {

                    $views = $downloads = $business_term = $corporate_term = $admin_term = '';

                    $viewsresult = $wpdb->get_results("SELECT user_id,post_id,date_time FROM $views_table_name WHERE user_id = $user_id ORDER BY date_time desc", OBJECT);
                    
                    foreach ($viewsresult as $viewed_documents) {
                        $viewedtime = strtotime($viewed_documents->date_time);
                        $viewedtitle = get_the_title($viewed_documents->post_id);

                        if(get_post_status($viewed_documents->post_id) === FALSE){
                            $viewedtitle = 'Document Id: '.$viewed_documents->post_id .' (Deleted)';
                        }


                        $views .= $viewed_documents->post_id .' | '.$viewedtitle.' | '.date('Y-m-d', $viewedtime)."\r\n";
                    }

                    $downloadssquery = $wpdb->get_results("SELECT user_id,post_id,date_time FROM $downloads_table_name WHERE user_id = $user->ID ORDER BY date_time desc", OBJECT);

                    foreach ($downloadssquery as $downloaded_documents) {
                        $downloadedtime = strtotime($downloaded_documents->date_time);
                        $downloadedtitle = get_the_title($downloaded_documents->post_id);

                        if(get_post_status($downloaded_documents->post_id) === FALSE){
                            $downloadedtitle = 'Document Id: '.$downloaded_documents->post_id .' (Deleted)';
                        }

                        $downloads .= $downloaded_documents->post_id .' | '.$downloadedtitle.' | '.date('Y-m-d', $downloadedtime)."\r\n";
                    }

                    $account_manager = get_the_author_meta( 'account_manager', $result->user_id );
                    /*if (isset($account_manager) && !empty($account_manager)) {
                        $account_manager_detail = get_user_by( 'email', $account_manager );
                        if ($account_manager_detail) {
                            $account_manager_name =  get_the_author_meta( 'first_name', $account_manager_detail->ID ) . ' ' . get_the_author_meta( 'last_name', $account_manager_detail->ID );
                        }
                    }*/

                    $user_business_id = get_the_author_meta( 'business_companies', $result->user_id );
                    if (isset($user_business_id) && !empty($user_business_id)) {
                        $business_term = get_term( $user_business_id, 'company' );
                    }

                    $user_corporate_id = get_the_author_meta( 'corporate_companies', $result->user_id );
                    if (isset($user_corporate_id) && !empty($user_corporate_id)) {
                        $corporate_term = get_term( $user_corporate_id, 'company' );
                    }

                    $user_admin_id = get_the_author_meta( 'admin_companies', $result->user_id );
                    if (isset($user_admin_id) && !empty($user_admin_id)) {
                        $admin_term = get_term( $user_admin_id, 'company' );
                    }
                    $user_meta = get_userdata($result->user_id);


                    $fields = array(
                        get_user_meta( $result->user_id, 'first_name', true ), //"First Name"
                        get_user_meta( $result->user_id, 'last_name', true ), //"Last Name"
                        ucwords(get_user_meta( $result->user_id, 'user_status', true )),//User Status
                        ucwords(get_user_meta( $result->user_id, 'tools_templates_access', true )), // T&T access
                        get_user_meta( $result->user_id, 'work_phone', true ), //Work Phone
                        $user_meta->user_email, //"Email"
                        get_user_meta( $result->user_id, 'street_1', true ), // Street 1
                        get_user_meta( $result->user_id, 'street_2', true ), // Street 2
                        get_user_meta( $result->user_id, 'city', true ), // City
                        get_user_meta( $result->user_id, 'state', true ), // State
                        get_user_meta( $result->user_id, 'zip_code', true ), // Zipcode
                        $user_meta->roles[0], // Role
                        $business_term->name,
                        $corporate_term->name,
                        $admin_term->name,
                        get_the_author_meta( 'account_manager', $result->user_id ),
                        get_user_meta( $result->user_id, 'create_date', true ), // create_date
                        get_user_meta( $result->user_id, 'start_date', true ), // start_date
                        $result->views_count, //"Number of Documents Viewed"
                        $views, //"Viewd Documents",
                        $result->downloads_count, //"Number of Documents Downloaded",
                        $downloads,
                    );
                    fputcsv($fp, $fields);

                }

                fclose($fp);

                $activity_by_id = DELETE_DISABLED_USERS_URL . '/disabled-user.csv';
                echo '<div id="message" class="updated fade"><p>';
                _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
                echo '<br /><a class="file-download-btn" href="' . $activity_by_id . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
                echo '</p></div>';
            } else {
                echo "<p style='color: red;margin: 0;'>Invalid Email or User doesn't exists</p>";
            }
        }
    }

    function prepare_items() {

        global $wpdb; //This is used only if making any database queries
        $per_page = '20';
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $current_page = $this->get_pagenum();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();

        // Grab the sort inputs then sanitize the values before using it in the query. Use a whitelist approach to sanitize it.
        $sort_order = isset($_GET['order'])? sanitize_text_field($_GET['order']):'DESC';
        $sort_order = reports_sanitize_value_by_array($sort_order, array('DESC' => '1', 'ASC' => '1'));  

        //Do a query to find the total number of rows then calculate the query limit
        $table_name = $wpdb->prefix . 'users';
        $logins_table_name = $wpdb->prefix . 'fa_user_logins';
        $usermeta_table_name = $wpdb->prefix . 'usermeta';
    

        /*$query = "SELECT $logins_table_name.user_id,MAX($logins_table_name.time_last_seen) AS time_last_seen FROM `$logins_table_name` INNER JOIN $usermeta_table_name ON $usermeta_table_name.user_id = $logins_table_name.user_id WHERE $logins_table_name.time_last_seen <= (now() - interval 6 month) AND $usermeta_table_name.`meta_key` LIKE 'user_status' AND $usermeta_table_name.`meta_value` LIKE 'disable' GROUP BY $logins_table_name.user_id";*/

        $query = "SELECT * FROM (SELECT $logins_table_name.user_id,MAX($logins_table_name.time_last_seen) AS time_last_seen FROM `$logins_table_name` INNER JOIN $usermeta_table_name ON $usermeta_table_name.user_id = $logins_table_name.user_id WHERE $usermeta_table_name.`meta_key` LIKE 'user_status' AND $usermeta_table_name.`meta_value` LIKE 'disable' GROUP BY $logins_table_name.user_id) as t WHERE t.time_last_seen <= (now() - interval 6 month)";
        $count_query = "SELECT COUNT(*) FROM (".$query.") AS Agg";
        $query.=" ORDER BY t.`time_last_seen` $sort_order";//Limit to query to only load a limited number of records
        
        $offset = ($current_page - 1) * $per_page;
        $query.=' LIMIT ' . (int) $offset . ',' . (int) $per_page;//Limit to query to only load a limited number of records

        $data_results = $wpdb->get_results($query);
        $total_items = $wpdb->get_var($count_query);//For pagination requirement
        
        //Prepare the array with the correct index names that the table is expecting.
        $data = array();

        foreach ($data_results as $data_result) {

            $account_manager = get_the_author_meta( 'account_manager', $data_result->user_id );
            if (isset($account_manager) && !empty($account_manager)) {
                $account_manager_detail = get_user_by( 'email', $account_manager );
                if ($account_manager_detail) {
                    $account_manager_name =  '<a href="users.php?page=pt-users-filter&account-manager='.$account_manager.'">'. get_the_author_meta( 'first_name', $account_manager_detail->ID ) . ' ' . get_the_author_meta( 'last_name', $account_manager_detail->ID ).'</a>';
                }
            }
            $business = $corporate = $admin = '';
            $get_user = get_userdata($data_result->user_id);
            $userRoleArray = $get_user->roles;

            // Check if the specified role is present in the array.
            if ( isset($userRoleArray) && is_array($userRoleArray) ) {
                if ( in_array( 'subscriber_-_admin_facility', $get_user->roles, true ) ) {
                    $user_role = 'Subscriber - Admin Facility';
                } elseif ( in_array( 'subscriber_-_corporate', $get_user->roles, true ) ) {
                    $user_role = 'Subscriber - Corporate';
                } elseif ( in_array( 'subscriber_-_facility_user', $get_user->roles, true ) ) {
                    $user_role = 'Subscriber - Facility User';
                } elseif ( in_array( 'business', $get_user->roles, true ) ) {
                    $user_role = 'Business';
                } elseif ( in_array( 'backend-admin', $get_user->roles, true ) ) {
                    $user_role = 'Backend Admin';
                } elseif ( in_array( 'account_manager', $get_user->roles, true ) ) {
                    $user_role = 'Account Manager';
                } elseif ( in_array( 'administrator', $get_user->roles, true ) ) {
                    $user_role = 'Administrator';
                }
            } else {
                $user_role = '';
            }

            $user_business_id = get_the_author_meta( 'business_companies', $data_result->user_id );
            if (isset($user_business_id) && !empty($user_business_id)) {
                $business_term = get_term( $user_business_id, 'company' );
                $business =  '<a href="users.php?page=pt-users-filter&company_id='.$business_term->term_id.'">'. $business_term->name.'</a>';
            }

            $user_corporate_id = get_the_author_meta( 'corporate_companies', $data_result->user_id );
            if (isset($user_corporate_id) && !empty($user_corporate_id)) {
                $corporate_term = get_term( $user_corporate_id, 'company' );
                $corporate = '<a href="users.php?page=pt-users-filter&company_id='.$corporate_term->term_id.'">'. $corporate_term->name.'</a>';
            }


            $user_admin_id = get_the_author_meta( 'admin_companies', $data_result->user_id );
            if (isset($user_admin_id) && !empty($user_admin_id)) {
                $admin_term = get_term( $user_admin_id, 'company' );
                $admin =  '<a href="users.php?page=pt-users-filter&company_id='.$admin_term->term_id.'">'. $admin_term->name.'</a>';
            }

            $data[] = array(
                'row_id' => $data_result->user_id,
                'name' => $get_user->first_name." ".$get_user->last_name,
                'email' => $get_user->user_email,
                'user-role' => $user_role,
                'business' => $business,
                'corporate' => $corporate,
                'admin' => $admin,
                'account_manager' => $account_manager_name,
                'disabled_by' => get_user_meta( $data_result->user_id, 'disabled_date', true ),
                'last_login' => $data_result->time_last_seen,
                'export_logs' => '<a class="button" href="?page=delete-disabled-users&action=export-disabled-user&user_id='.$data_result->user_id.'">Export Logs</a>',
            );
        }
        
        // Now we add our *sorted* data to the items property, where it can be used by the rest of the class.
        $this->items = $data;   

        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page, //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }

}
