<?php

//*****
//*****  Check WP_List_Table exists
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//*****
//*****  Define our new Table
class searched_List_Table extends WP_List_Table {

    function __construct() {

        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => __('Search Result', 'reports'), //singular name of the listed records
            'plural' => __('Search Results', 'reports'), //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));
    }

    function column_default($item, $column_name) {
        
        switch ($column_name) {
            case 'email':
            case 'name':
            case 'role':
            case 'status':
            case 'business':
            case 'corporate':
            case 'admin':
            case 'account_manager':
            case 'searched_query':
            case 't_t_access':
            case 'p_p_access':
            case 'visitor_ip':
            case 'date':
            case 'count_query':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_searched_query($item) {
        $delete_log_nonce = wp_create_nonce('searched_delete_log_entry');
        //Build row actions
        $actions = array(
            'delete' => sprintf('<a href="?page=search-reports&action=%s&searchresult=%s&row_id=%s&_wpnonce=%s" onclick="return confirm(\'Are you sure you want to delete this entry?\')">' . __('Delete', 'reports') . '</a>', 'delete', $item['row_id'], $item['row_id'], $delete_log_nonce),
        );

        //Return the title contents
        return sprintf('%1$s %3$s',
                /* $1%s */ $item['searched_query'],
                /* $2%s */ $item['row_id'],
                /* $3%s */ $this->row_actions($actions)
        );
    }

    function column_cb($item) {

        return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                /* $1%s */ $this->_args['singular'], //Let's simply repurpose the table's singular label ("Download")
                /* $2%s */ $item['row_id'] //The value of the checkbox should be the record's id
        );
    }

    function get_columns() {

        if ( !isset($_REQUEST['action']) || ($_REQUEST['action']=='tally-search-logs' && !empty($_REQUEST['search_query'])) ) {
            $columns = array(
                'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
                'email' => __('Email', 'reports'),
                'name' =>  __('Name', 'reports'),
                'role' =>  __('Role', 'reports'),
                'status' =>  __('Status', 'reports'),
                'business' =>   __('Business', 'reports'),
                'corporate' =>   __('Corporate Facility', 'reports'),
                'admin' =>   __('Admin Facility', 'reports'),
                'account_manager' =>   __('Account Manager', 'reports'),
                'searched_query' => __('Searched Query', 'reports'),
                't_t_access' => __('T&T Access', 'reports'),
                'p_p_access' => __('P&P Access', 'reports'),
                'visitor_ip' =>  __('Visitor IP', 'reports'),
                'date' =>  __('Date', 'reports'),
            );
        } else {
            $columns = array(
                'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
                'searched_query' => __('Searched Query', 'reports'),
                'count_query' =>  __('No. of time searched', 'reports'),
            );
        }

        return $columns;
    }

    function get_sortable_columns() {

        $sortable_columns = array(
            'count_query' => array('count_query', false),
            'date' => array('date_time', false),
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
        $search_table_name = $wpdb->prefix . 'search_results';
        // if bulk 'Delete Permanently' was clicked
        if ('delete2' === $this->current_action()) {

            //Check bulk delete nonce
            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            if (!isset($_POST['searchresult']) || $_POST['searchresult'] == null) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('No entries were selected.', 'reports') . '</strong></p><p><em>' . __('Click to Dismiss', 'reports') . '</em></p></div>';
                return;
            }

            foreach ($_POST['searchresult'] as $item) {
                $row_id = sanitize_text_field($item);
                if (!is_numeric($row_id)){
                    wp_die(__('Error! The row id value of a log entry must be numeric.', 'reports'));
                }

                $del_row = $wpdb->query('DELETE FROM ' . $search_table_name . ' WHERE id = "' . $row_id . '"');
            }
            if ($del_row) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Entries Deleted!', 'reports') . '</strong></p><p><em>' . __('Click to Dismiss', 'reports') . '</em></p></div>';
            } else {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Error', 'reports') . '</strong></p><p><em>' . __('Click to Dismiss', 'reports') . '</em></p></div>';
            }
        }

        // If single entry 'Delete' was clicked
        if ('delete' === $this->current_action()) {

            //Check bulk delete nonce
            $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'searched_delete_log_entry';
            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            //Grab the row id
            $row_id = filter_input(INPUT_GET, 'row_id', FILTER_SANITIZE_STRING);
            
            $del_row = $wpdb->query('DELETE FROM ' . $search_table_name . ' WHERE id = "' . $row_id . '"');
            if ($del_row) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Entry Deleted!', 'reports') . '</strong></p><p><em>' . __('Click to Dismiss', 'reports') . '</em></p></div>';
            } else {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Error', 'reports') . '</strong></p><p><em>' . __('Click to Dismiss', 'reports') . '</em></p></div>';
            }
        }
    }

    function prepare_items() {

        global $wpdb; //This is used only if making any database queries
        $per_page = '50';
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $current_page = $this->get_pagenum();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();

        // Grab the sort inputs then sanitize the values before using it in the query. Use a whitelist approach to sanitize it.
        $orderby_column = isset($_GET['orderby'])? sanitize_text_field($_GET['orderby']):'';
        $sort_order = isset($_GET['order'])? sanitize_text_field($_GET['order']):'';
        if(empty($orderby_column)){
            if ( !isset($_REQUEST['action']) || ($_REQUEST['action']=='tally-search-logs' && !empty($_REQUEST['search_query'])) ) {
                $orderby_column = "date_time";
            } else {
                $orderby_column = "count_query";
            }
            $sort_order = "DESC";

        }
        $orderby_column = reports_sanitize_value_by_array($orderby_column, array('count_query'=>'1', 'date_time'=>'1'));
        $sort_order = reports_sanitize_value_by_array($sort_order, array('DESC' => '1', 'ASC' => '1'));  

        //Do a query to find the total number of rows then calculate the query limit
        $table_name = $wpdb->prefix . 'search_results';


        if ( !isset($_REQUEST['action']) || ($_REQUEST['action']=='tally-search-logs' && !empty($_REQUEST['search_query'])) ) {
            $where = "";
            if ($_REQUEST['action']=='tally-search-logs' && !empty($_REQUEST['search_query'])) {
                $where .= " WHERE searched_query = '".$_REQUEST['search_query']."' ";
            }
            $query = "SELECT * FROM $table_name ".$where;
            $countquery = "SELECT count(*) FROM $table_name ".$where;
        } else {
            $query = "SELECT searched_query, COUNT(*) AS count_query FROM $table_name GROUP BY searched_query ";
            $countquery = "SELECT COUNT(*) FROM $table_name GROUP BY searched_query ";
            $countquery = "SELECT COUNT(*) FROM  ( $countquery ) as query_count";
        }
        $query .= " ORDER BY $orderby_column $sort_order ";

        $offset = ($current_page - 1) * $per_page;
        $query.=' LIMIT ' . (int) $offset . ',' . (int) $per_page;//Limit to query to only load a limited number of records

        $data_results = $wpdb->get_results($query);

        //$total_items = count($data_results);
        $total_items = $wpdb->get_var($countquery);
        
        //Prepare the array with the correct index names that the table is expecting.
        $data = array();
        if ( !isset($_REQUEST['action']) || ($_REQUEST['action']=='tally-search-logs' && !empty($_REQUEST['search_query'])) ) {
            foreach ($data_results as $user_data) {
                $business = $corporate = $admin = '';
                $get_user = get_userdata($user_data->user_id);
                $user_roles = $get_user->roles;

                if ( isset($user_roles) && is_array($user_roles) ) {
                    // Check if the specified role is present in the array.
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
                    }elseif ( in_array( 'administrator', $get_user->roles, true ) ) {
                        $user_role = 'Administrator';
                    }
                } else {
                    $user_role = '';
                }
                $status = ucwords(get_user_meta( $user_data->user_id, 'user_status', true ));

                $user_business_id = get_the_author_meta( 'business_companies', $user_data->user_id );
                if (isset($user_business_id) && !empty($user_business_id)) {
                    $business_term = get_term( $user_business_id, 'company' );
                    $business =  '<a href="users.php?page=pt-users-filter&company_id='.$business_term->term_id.'">'. $business_term->name.'</a>';
                }

                $user_corporate_id = get_the_author_meta( 'corporate_companies', $user_data->user_id );
                if (isset($user_corporate_id) && !empty($user_corporate_id)) {
                    $corporate_term = get_term( $user_corporate_id, 'company' );
                    $corporate = '<a href="users.php?page=pt-users-filter&company_id='.$corporate_term->term_id.'">'. $corporate_term->name.'</a>';
                }


                $user_admin_id = get_the_author_meta( 'admin_companies', $user_data->user_id );
                if (isset($user_admin_id) && !empty($user_admin_id)) {
                    $admin_term = get_term( $user_admin_id, 'company' );
                    $admin =  '<a href="users.php?page=pt-users-filter&company_id='.$admin_term->term_id.'">'. $admin_term->name.'</a>';
                }


                $account_manager = get_the_author_meta( 'account_manager', $user_data->user_id );
                if (isset($account_manager) && !empty($account_manager)) {
                    $account_manager_detail = get_user_by( 'email', $account_manager );
                    if ($account_manager_detail) {
                        $account_manager_name =  '<a href="users.php?page=pt-users-filter&account-manager='.$account_manager.'">'. get_the_author_meta( 'first_name', $account_manager_detail->ID ) . ' ' . get_the_author_meta( 'last_name', $account_manager_detail->ID ).'</a>';
                    }
                }

                /**
                 * T&T and P&P getting for displaying in ListTable
                 */
                $tools_templates_access = ucwords( esc_attr(get_user_meta($user_data->user_id, 'tools_templates_access', true)) );
                $policies_procedures_access = ucwords( esc_attr(get_user_meta($user_data->user_id, 'policies_procedures_access', true)) );
                
                if( empty( $tools_templates_access ) ) {
                    update_user_meta($user_data->user_id, 'tools_templates_access', 'enable');
                    $tools_templates_access = ucwords( esc_attr(get_user_meta($user_data->user_id, 'tools_templates_access', true)) );
                }
                if( empty( $policies_procedures_access ) ) {
                    update_user_meta($user_data->user_id, 'policies_procedures_access', 'enable');
                    $policies_procedures_access = ucwords( esc_attr(get_user_meta($user_data->user_id, 'policies_procedures_access', true)) );
                }
                
                if ( get_user_by( 'id', $user_data->user_id ) ) {
                    $data[] = array(
                        'row_id' => $user_data->id,
                        'email' => $get_user->user_email,
                        'name' => $get_user->first_name." ".$get_user->last_name,
                        'role' => $user_role,
                        'status' => $status,
                        'business' => $business,
                        'corporate' => $corporate,
                        'admin' => $admin,
                        'account_manager' => $account_manager_name,
                        'searched_query' => $user_data->searched_query,
                        't_t_access' => $tools_templates_access,
                        'p_p_access' => $policies_procedures_access,
                        'visitor_ip' => $user_data->visitor_ip,
                        'date' => $user_data->date_time
                    );
                } else {
                    $data[] = array(
                        'row_id' => $user_data->id,
                        'email' => '<span style="color:red;">User Deleted<span style="color:silver;"> (id:'.$user_data->user_id.')</span></span>',
                        'name' => '',
                        'role' => '',
                        'status' => '',
                        'business' => '',
                        'corporate' => '',
                        'admin' => '',
                        'account_manager' => '',
                        'searched_query' => $user_data->searched_query,
                        't_t_access' => '',
                        'p_p_access' => '',
                        'visitor_ip' => $user_data->visitor_ip,
                        'date' => $user_data->date_time,
                    );
                }
            }
        } else {
            foreach ($data_results as $user_data) {
                $count_query = '<a href="admin.php?page=search-reports&action=tally-search-logs&search_query='.$user_data->searched_query.'">'. $user_data->count_query .'</a>';
                $data[] = array(
                    'row_id' => '',
                    'searched_query' => $user_data->searched_query,
                    'count_query' => $count_query,
                );
            }
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