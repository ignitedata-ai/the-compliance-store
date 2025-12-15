<?php

//*****
//*****  Check WP_List_Table exists
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//*****
//*****  Define our new Table
class user_activity_List_Table extends WP_List_Table {

    function __construct() {

        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => __('User Activity', 'reports'), //singular name of the listed records
            'plural' => __('Users Activities', 'reports'), //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));
    }

    function column_default($item, $column_name) {
        
        switch ($column_name) {
            case 'username':
            case 'name':
            case 'role':
            case 'status':
            case 'business':
            case 'corporate':
            case 'admin':
            case 'account_manager':
            case 't_t_access':
            case 'p_p_access':
            case 'views':
            case 'downloads':
            case 'title':
            case 'cats':
            case 'date':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_columns() {

        if(
            (
                isset($_REQUEST['reports-views-user-activity-id']) && !empty($_REQUEST['reports-views-user-activity-id'])
            ) 
            ||
            (
                isset($_REQUEST['reports-downloads-user-activity-id']) && !empty($_REQUEST['reports-downloads-user-activity-id'])
            )
        ){
           $columns['title'] =  __('Document Title', 'reports');
           $columns['cats'] =  __('Categories', 'reports');
           $columns['date'] =  __('Date', 'reports');
        }
        else {
            $columns['username'] = __('Email', 'reports');
            $columns['name'] =  __('Name', 'reports');
            $columns['role'] =  __('Role', 'reports');
            $columns['status'] =  __('Status', 'reports');
            $columns['business'] =   __('Business', 'reports');
            $columns['corporate'] =   __('Corporate Facility', 'reports');
            $columns['admin'] =  __('Admin Facility', 'reports');
            $columns['account_manager'] =   __('Account Manager', 'reports');
            $columns['t_t_access'] =   __('T&T Access', 'reports');
            $columns['p_p_access'] =   __('P&P Access', 'reports');
            $columns['views'] =   __('Documents Views', 'reports');
            $columns['downloads'] =   __('Documents Downloads', 'reports');    
        }

        return $columns;
    }

    function get_sortable_columns() {
            $sortable_columns = array(
                'date' => array('date_time', false),//true means it's already sorted
                'views' => array('views_count', false),
                'downloads' => array('downloads_count', false),
            );
        return $sortable_columns;
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
            $orderby_column = "activity_count";
            $sort_order = "DESC";
             if(
                (
                    isset($_REQUEST['reports-views-user-activity-id']) && !empty($_REQUEST['reports-views-user-activity-id'])
                ) 
                ||
                (
                    isset($_REQUEST['reports-downloads-user-activity-id']) && !empty($_REQUEST['reports-downloads-user-activity-id'])
                )
            ){
                $orderby_column = "date_time";
                $sort_order = "DESC";
            }
        }
        $orderby_column = reports_sanitize_value_by_array($orderby_column, array('activity_count'=>'1', 'views_count'=>'1','downloads_count'=>'1', 'date_time'=>'1'));
        $sort_order = reports_sanitize_value_by_array($sort_order, array('DESC' => '1', 'ASC' => '1'));

        $views_table_name = $wpdb->prefix . 'reports_views';
        $downloads_table_name = $wpdb->prefix . 'reports_downloads';
        //$users_table_name = 'wp_jw5prm2trf_users';
        $activity_table_name = $wpdb->prefix . 'reports_activity';



        if(isset($_REQUEST['reports-views-user-activity-id']) && !empty($_REQUEST['reports-views-user-activity-id'])){
            //For specific download logs
            $user_id = sanitize_text_field($_REQUEST['reports-views-user-activity-id']);

            $query = "SELECT * FROM $views_table_name WHERE user_id = $user_id ORDER BY $orderby_column $sort_order";
        } 
        else if(isset($_REQUEST['reports-downloads-user-activity-id']) && !empty($_REQUEST['reports-downloads-user-activity-id'])){
            //For specific download logs
            $user_id = sanitize_text_field($_REQUEST['reports-downloads-user-activity-id']);

            $query = "SELECT * FROM $downloads_table_name WHERE user_id = $user_id ORDER BY $orderby_column $sort_order";
        } else {
            //For all logs
            $query = "SELECT * FROM $activity_table_name ORDER BY $orderby_column $sort_order";
        }

        $offset = ($current_page - 1) * $per_page;

        $query.=' LIMIT ' . (int) $offset . ',' . (int) $per_page;//Limit to query to only load a limited number of records

        $data_results = $wpdb->get_results($query);

        if(isset($_REQUEST['reports-views-user-activity-id']) && !empty($_REQUEST['reports-views-user-activity-id'])){
            $user_id = sanitize_text_field($_REQUEST['reports-views-user-activity-id']);
            $count_query = "SELECT count(*) FROM $views_table_name WHERE user_id = $user_id";
            $total_items = $wpdb->get_var($count_query);
            $user_info = get_userdata($user_id);
            $user_email = $user_info->user_email;
            ?>
            <h2><?php _e( 'Individual User Views Logs', 'reports' ); ?></h2>

            <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
            <p><?php echo 'This page lists tracked Views Logs of User: '.$user_email; ?></p>
            </div>
            <?php

        } 
        else if(isset($_REQUEST['reports-downloads-user-activity-id']) && !empty($_REQUEST['reports-downloads-user-activity-id'])){
            $user_id = sanitize_text_field($_REQUEST['reports-downloads-user-activity-id']);
            $count_query = "SELECT count(*) FROM $downloads_table_name WHERE user_id = $user_id";
            $total_items = $wpdb->get_var($count_query);
            $user_info = get_userdata($user_id);
            $user_email = $user_info->user_email;
            ?>
            <h2><?php _e( 'Individual User Download Logs', 'reports' ); ?></h2>

            <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
            <p><?php echo 'This page lists tracked Download Logs of User: '.$user_email; ?></p>
            </div>
            <?php
        }
        else {
            //For all logs
            $count_query = "SELECT COUNT(*) FROM $activity_table_name";
            $total_items = $wpdb->get_var($count_query);//For pagination requirement
            ?>
            <h2><?php _e( 'All Users Activity Logs', 'reports' ); ?></h2>

            <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
            <p><?php _e( 'This page lists all tracked Users activities.', 'reports' ); ?></p>
            </div>
            <?php
        }
         
        //Prepare the array with the correct index names that the table is expecting.
        $data = array();
        foreach ($data_results as $user_data) {
            if(
            (
                isset($_REQUEST['reports-views-user-activity-id']) && !empty($_REQUEST['reports-views-user-activity-id'])
            ) 
            ||
            (
                isset($_REQUEST['reports-downloads-user-activity-id']) && !empty($_REQUEST['reports-downloads-user-activity-id'])
            )
            ){
                $terms = get_the_terms( $user_data->post_id , 'documents_category' );
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
                $title = get_the_title($user_data->post_id);
                $post_exists = post_exists($title);
                if ($post_exists == 0) {
                    $title = 'Document Id: '.$user_data->post_id .' (Deleted)';
                }

                $data[] = array(
                    'title' =>$title,
                    'cats' => $cats,
                    'date' => $user_data->date_time
                );

            } else {
                $business = $corporate = $admin = '';
                $get_user = get_userdata($user_data->user_id);
                $user_email = $get_user->user_email;
                $user_role = $get_user->roles;

                if ( isset($user_role) && is_array($user_role) ) {
                    // Check if the specified role is present in the array.
                    if ( in_array( 'subscriber_-_admin_facility', $get_user->roles, true ) ) {
                        $user_role = 'Subscriber - Admin Facility';
                    } elseif ( in_array( 'subscriber_-_corporate', $get_user->roles, true ) ) {
                        $user_role = 'Subscriber - Corporate';
                    } elseif ( in_array( 'subscriber_-_facility_user', $get_user->roles, true ) ) {
                        $user_role = 'Subscriber - Facility User';
                    } elseif ( in_array( 'business', $get_user->roles, true ) ) {
                        $user_role = 'Business';
                    }
                } else {
                    $user_role = '';
                }

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
                if ($user_data->views_count == 0) {
                    $views_counts = 0;
                } else {
                    $views_counts = '<a href="' . admin_url('admin.php?page=users-activity-reports&reports-views-user-activity-id=' . $user_data->user_id ). '">' . $user_data->views_count . '</a>';
                }
                if ($user_data->downloads_count == 0) {
                    $downloads_counts = 0;
                } else {
                    $downloads_counts = '<a href="' . admin_url('admin.php?page=users-activity-reports&reports-downloads-user-activity-id=' . $user_data->user_id ). '">' . $user_data->downloads_count . '</a>';
                }
                $status = ucwords(get_user_meta( $user_data->user_id, 'user_status', true ));
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
                        'username' => $user_email,
                        'name' => $get_user->first_name." ".$get_user->last_name,
                        'role' => $user_role,
                        'status' => $status,
                        'business' => $business,
                        'corporate' => $corporate,
                        'admin' => $admin,
                        'account_manager' => $account_manager_name,
                        't_t_access' => $tools_templates_access,
                        'p_p_access' => $policies_procedures_access,
                        'views' => $views_counts,
                        'downloads' => $downloads_counts
                    );
                } else {
                    $data[] = array(
                        'username' => '<span style="color:red;">User Deleted<span style="color:silver;"> (id:'.$user_data->user_id.')</span></span>',
                        'name' => '',
                        'role' => '',
                        'status' => '',
                        'business' => '',
                        'corporate' => '',
                        'admin' => '',
                        'account_manager' => '',
                        't_t_access' => '',
                        'p_p_access' => '',
                        'views' => $views_counts,
                        'downloads' => $downloads_counts
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