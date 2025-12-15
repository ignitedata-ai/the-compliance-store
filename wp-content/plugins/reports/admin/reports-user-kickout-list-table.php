<?php

//*****
//*****  Check WP_List_Table exists
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//*****
//*****  Define our new Table
class user_kickout_List_Table extends WP_List_Table {

    function __construct() {

        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => __('User Kickout', 'reports'), //singular name of the listed records
            'plural' => __('Users Kickouts', 'reports'), //plural name of the listed records
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
            case 'visitor_ip':
            case 'browser':
            case 'OS':
            case 'date':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }


    function column_email($item) {
        $delete_log_nonce = wp_create_nonce('kickout_user_delete_log_entry');
        //Build row actions
        $actions = array(
            'delete' => sprintf('<a href="?page=kickout-reports&action=%s&userkickout=%s&row_id=%s&_wpnonce=%s" onclick="return confirm(\'Are you sure you want to delete this entry?\')">' . __('Delete', 'reports') . '</a>', 'delete', $item['row_id'], $item['row_id'], $delete_log_nonce),
        );

        //Return the title contents
        return sprintf('%1$s %3$s',
                /* $1%s */ $item['email'],
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
            'visitor_ip' =>   __('Visitor IP', 'reports'),
            'browser' =>   __('Web Browser', 'reports'),
            'OS' =>   __('Operating System', 'reports'),
            'date' =>   __('Date', 'reports'),
        );

        return $columns;
    }

    function get_sortable_columns() {
            $sortable_columns = array(
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

        // if bulk 'Delete Permanently' was clicked
        global $wpdb;
        $kickout_users_table_name = $wpdb->prefix . 'kickout_users';
        if ('delete2' === $this->current_action()) {

            //Check bulk delete nonce
            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            if (!isset($_POST['userkickout']) || $_POST['userkickout'] == null) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('No entries were selected.', 'reports') . '</strong></p></div>';
                return;
            }

            foreach ($_POST['userkickout'] as $item) {
                $row_id = sanitize_text_field($item);
                if (!is_numeric($row_id)){
                    wp_die(__('Error! The row id value of a log entry must be numeric.', 'reports'));
                }

                $del_row = $wpdb->query('DELETE FROM ' . $kickout_users_table_name . ' WHERE id = "' . $row_id . '"');
            }
            if ($del_row) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Entries Deleted!', 'reports') . '</strong></p></div>';
            } else {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Error', 'reports') . '</strong></p></div>';
            }
        }

        // If single entry 'Delete' was clicked
        if ('delete' === $this->current_action()) {

            //Check bulk delete nonce
            $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'kickout_user_delete_log_entry';
            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            //Grab the row id
            $row_id = filter_input(INPUT_GET, 'row_id', FILTER_SANITIZE_STRING);
            
            global $wpdb;
            $del_row = $wpdb->query('DELETE FROM ' . $kickout_users_table_name . ' WHERE id = "' . $row_id . '"');
            if ($del_row) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Entry Deleted!', 'reports') . '</strong></p></div>';
            } else {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Error', 'reports') . '</strong></p></div>';
            }
        }
    }


    function prepare_items() {

        global $wpdb; //This is used only if making any database queries
        $per_page = '100';
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
                $orderby_column = "date_time";
                $sort_order = "DESC";
        }
        $orderby_column = reports_sanitize_value_by_array($orderby_column, array('date_time'=>'1'));
        $sort_order = reports_sanitize_value_by_array($sort_order, array('DESC' => '1', 'ASC' => '1'));

        $kickout_users_table_name = $wpdb->prefix . 'kickout_users';


        $query = "SELECT * FROM $kickout_users_table_name ORDER BY $orderby_column $sort_order";
        $query_count = "SELECT count(*) FROM $kickout_users_table_name";

        $total_items = $wpdb->get_var($query_count);

        $offset = ($current_page - 1) * $per_page;

        $query.=' LIMIT ' . (int) $offset . ',' . (int) $per_page;//Limit to query to only load a limited number of records

        $data_results = $wpdb->get_results($query);

        ?>
        <h2><?php _e( 'All Kicked Out Users', 'reports' ); ?></h2>

        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        <p><?php _e( 'This page lists all tracked kicked out users.', 'reports' ); ?></p>
        </div>
        <?php
         
        //Prepare the array with the correct index names that the table is expecting.
        $data = array();
        foreach ($data_results as $user_data) {
            $business = $corporate = $admin = '';
            $get_user = get_userdata($user_data->user_id);
            $user_role = $get_user->roles;

            // Check if the specified role is present in the array.
            if ( isset($user_role) && is_array($user_role) ) {
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
                    'visitor_ip' => $user_data->visitor_ip,
                    'browser' => $user_data->browser,
                    'OS' => $user_data->OS,
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
                    'visitor_ip' => $user_data->visitor_ip,
                    'browser' => $user_data->browser,
                    'OS' => $user_data->OS,
                    'date' => $user_data->date_time
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