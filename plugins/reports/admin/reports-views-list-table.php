<?php

//*****
//*****  Check WP_List_Table exists
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//*****
//*****  Define our new Table
class views_List_Table extends WP_List_Table {

    function __construct() {

        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => __('Views', 'reports'), //singular name of the listed records
            'plural' => __('Views', 'reports'), //plural name of the listed records
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
            case 't_t_access':
            case 'p_p_access':
            case 'visitor_ip':
            case 'date':
            case 'title':
            case 'cats':
            case 'counts':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item) {
        $delete_log_nonce = wp_create_nonce('views_delete_log_entry');
        //Build row actions
        $actions = array(
            'edit' => sprintf('<a href="' . admin_url('post.php?post=' . $item['ID'] . '&action=edit') . '">' . __('Edit', 'reports') . '</a>'),
            'delete' => sprintf('<a href="?page=views-reports&action=%s&view=%s&row_id=%s&_wpnonce=%s" onclick="return confirm(\'Are you sure you want to delete this entry?\')">' . __('Delete', 'reports') . '</a>', 'delete', $item['ID'], $item['row_id'], $delete_log_nonce),
            'view-counts' => sprintf('<a href="' . admin_url('admin.php?page=views-reports&action=reports-logs-by-views&document_id=' . $item['ID'] ). '">' . __('View Counts', 'reports') . '</a>'),
        );

        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
                /* $1%s */ $item['title'],
                /* $2%s */ $item['ID'],
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
        if(isset($_REQUEST['document_id']) && !empty($_REQUEST['document_id'])){
            $columns['cb'] = '<input type="checkbox" />';
            $columns['email'] = __('Email', 'reports');
            $columns['name'] =  __('Name', 'reports');
            $columns['role'] =  __('Role', 'reports');
            $columns['status'] =  __('Status', 'reports');
            $columns['business'] =   __('Business', 'reports');
            $columns['corporate'] =   __('Corporate Facility', 'reports');
            $columns['admin'] =   __('Admin Facility', 'reports');
            $columns['account_manager'] =   __('Account Manager', 'reports');
            $columns['t_t_access'] =   __('T&T Access', 'reports');
            $columns['p_p_access'] =   __('P&P Access', 'reports');
            $columns['visitor_ip'] =  __('Visitor IP', 'reports');
            $columns['date'] =  __('Date', 'reports');
        }
        else {
            $columns['cb'] = '<input type="checkbox" />';
            $columns['title'] = __('Title', 'reports');
            $columns['cats'] = __('Categories', 'reports');
            $columns['counts'] = __('Number of Views', 'reports');
        }
        return $columns;
    }

    function get_sortable_columns() {

        $sortable_columns = array(
            'date' => array('date_time', false),//true means it's already sorted
            'counts' => array('counts', false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {

        $actions = array();
        $actions['bulk-delete'] = __('Delete Permanently', 'reports');

        return $actions;
    }

    function process_bulk_action() {

        // if bulk 'Delete Permanently' was clicked
        if ('bulk-delete' === $this->current_action()) {

            //Check bulk delete nonce
            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            if (!isset($_POST['views']) || $_POST['views'] == null) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('No entries were selected.', 'reports') . '</strong></p></div>';
                return;
            }

            foreach ($_POST['views'] as $item) {
                $row_id = sanitize_text_field($item);
                if (!is_numeric($row_id)){
                    wp_die(__('Error! The row id value of a log entry must be numeric.', 'reports'));
                }

                global $wpdb;
                $del_row = $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'reports_views WHERE id = "' . $row_id . '"');
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
            $action = 'views_delete_log_entry';
            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            //Grab the row id
            $row_id = filter_input(INPUT_GET, 'row_id', FILTER_SANITIZE_STRING);
            
            global $wpdb;
            $del_row = $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'reports_views WHERE id = "' . $row_id . '"');
            if ($del_row) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Entry Deleted!', 'reports') . '</strong></p></div>';
            } else {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Error', 'reports') . '</strong></p></div>';
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
            $orderby_column = "counts";
            $sort_order = "DESC";
            if(isset($_REQUEST['document_id']) && !empty($_REQUEST['document_id'])){
                $orderby_column = "date_time";
                $sort_order = "DESC";
            }
        }
        $orderby_column = reports_sanitize_value_by_array($orderby_column, array( 'counts'=>'1', 'date_time'=>'1'));
        $sort_order = reports_sanitize_value_by_array($sort_order, array('DESC' => '1', 'ASC' => '1'));  

        //Do a query to find the total number of rows then calculate the query limit
        $table_name = $wpdb->prefix . 'reports_views';

        $post_id = 'post_id';

        if(isset($_REQUEST['document_id']) && !empty($_REQUEST['document_id'])){
            //For specific download logs
            $view_id = sanitize_text_field($_REQUEST['document_id']);
            $query = "SELECT * FROM $table_name WHERE $post_id = $view_id ORDER BY $orderby_column $sort_order";
            $count_query = "SELECT COUNT(*) FROM ( SELECT $post_id FROM $table_name WHERE $post_id = $view_id ) AS count_query";
            $total_items = $wpdb->get_var($count_query);//For pagination requirement
        } else {
            //For all view logs
            $query = "SELECT *, COUNT(*) AS counts FROM $table_name GROUP BY $post_id ORDER BY $orderby_column $sort_order";
            $count_query = "SELECT COUNT(*) FROM ( SELECT $post_id FROM $table_name GROUP BY $post_id) AS Agg";
            $total_items = $wpdb->get_var($count_query);//For pagination requirement
        }

        $offset = ($current_page - 1) * $per_page;
        $query.=' LIMIT ' . (int) $offset . ',' . (int) $per_page;//Limit to query to only load a limited number of records

        $data_results = $wpdb->get_results($query);
        
        //Prepare the array with the correct index names that the table is expecting.
        $data = array();
        if(isset($_REQUEST['document_id']) && !empty($_REQUEST['document_id'])){
            foreach ($data_results as $data_result) {
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

                $status = ucwords(get_user_meta( $data_result->user_id, 'user_status', true ));

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


                $account_manager = get_the_author_meta( 'account_manager', $data_result->user_id );
                if (isset($account_manager) && !empty($account_manager)) {
                    $account_manager_detail = get_user_by( 'email', $account_manager );
                    if ($account_manager_detail) {
                        $account_manager_name =  '<a href="users.php?page=pt-users-filter&account-manager='.$account_manager.'">'. get_the_author_meta( 'first_name', $account_manager_detail->ID ) . ' ' . get_the_author_meta( 'last_name', $account_manager_detail->ID ).'</a>';
                    }
                }

                $terms = get_the_terms( $data_result->post_id , 'documents_category' );
                $i = 1;
                $cats = '';
                if(is_array($terms)){
                    foreach ( $terms as $term ) {
                        $cats .= $term->name;
                        // Increment counter
                        $cats .= ($i < count($terms))? ", " : "";
                        $i++;
                    }
                }
                $count_views = "SELECT COUNT(*) FROM $table_name WHERE post_id = $data_result->post_id";
                $total_views = $wpdb->get_var($count_views);
                $counts = '<a href="' . admin_url('admin.php?page=views-reports&action=reports-logs-by-views&document_id=' . $data_result->post_id ). '">' . $total_views . '</a>';

                /**
                 * T&T and P&P getting for displaying in ListTable
                 */
                $tools_templates_access = ucwords( esc_attr(get_user_meta($data_result->user_id, 'tools_templates_access', true)) );
                $policies_procedures_access = ucwords( esc_attr(get_user_meta($data_result->user_id, 'policies_procedures_access', true)) );
                
                if( empty( $tools_templates_access ) ) {
                    update_user_meta($data_result->user_id, 'tools_templates_access', 'enable');
                    $tools_templates_access = ucwords( esc_attr(get_user_meta($data_result->user_id, 'tools_templates_access', true)) );
                }
                if( empty( $policies_procedures_access ) ) {
                    update_user_meta($data_result->user_id, 'policies_procedures_access', 'enable');
                    $policies_procedures_access = ucwords( esc_attr(get_user_meta($data_result->user_id, 'policies_procedures_access', true)) );
                }
                
                if ( get_user_by('id', $data_result->user_id) ) {
                    $data[] = array(
                        'row_id' => $data_result->id,
                        'ID' => $data_result->post_id,
                        'title' => get_the_title($data_result->post_id),
                        'email' => $get_user->user_email,
                        'name' => $get_user->first_name . " " . $get_user->last_name,
                        'role' => $user_role,
                        'status' => $status,
                        'business' => $business,
                        'corporate' => $corporate,
                        'admin' => $admin,
                        'account_manager' => $account_manager_name,
                        't_t_access' => $tools_templates_access,
                        'p_p_access' => $policies_procedures_access,
                        'visitor_ip' => $data_result->visitor_ip,
                        'date' => $data_result->date_time,
                    );
                } else {
                    $data[] = array(
                        'row_id' => $data_result->id,
                        'ID' => $data_result->post_id,
                        'title' => get_the_title($data_result->post_id),
                        'email' => '<span style="color:red;">User Deleted<span style="color:silver;"> (id:'.$data_result->user_id.')</span></span>',
                        'name' => '',
                        'role' => '',
                        'status' => '',
                        'business' => '',
                        'corporate' => '',
                        'admin' => '',
                        'account_manager' => '',
                        't_t_access' => '',
                        'p_p_access' => '',
                        'visitor_ip' => $data_result->visitor_ip,
                        'date' => $data_result->date_time,
                    );
                }
            }
        } else {
            foreach ($data_results as $data_result) {
                $terms = get_the_terms( $data_result->post_id , 'documents_category' );
                $i = 1;
                $cats = '';
                if(is_array($terms)){
                    foreach ( $terms as $term ) {
                        $cats .= $term->name;
                        // Increment counter
                        $cats .= ($i < count($terms))? ", " : "";
                        $i++;
                    }
                }
                $count_views = "SELECT COUNT(*) FROM $table_name WHERE post_id = $data_result->post_id";
                $total_views = $wpdb->get_var($count_views);
                $counts = '<a href="' . admin_url('admin.php?page=views-reports&action=reports-logs-by-views&document_id=' . $data_result->post_id ). '">' . $total_views . '</a>';

                if(get_post_status($data_result->post_id) === FALSE){
                    $data[] = array(
                        'row_id' => $data_result->id,
                        'ID' => $data_result->post_id,
                        'title' => 'Document Id: '.$data_result->post_id .' (Deleted)',
                        'cats' => '',
                        'counts' => $counts
                    );
                } else {
                    $data[] = array(
                        'row_id' => $data_result->id,
                        'ID' => $data_result->post_id,
                        'title' => get_the_title($data_result->post_id),
                        'cats' => $cats,
                        'counts' => $counts
                    );
                }

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
