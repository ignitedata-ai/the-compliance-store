<?php

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Frontend_Documents_Views_List_Table extends WP_List_Table {

    function __construct() {

        global $status, $page;

        parent::__construct(array(
            'singular' => __('Views', 'reports'),
            'plural' => __('Views', 'reports'),
            'ajax' => false
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

        $delete_log_nonce = wp_create_nonce('delete_view_log_entry');

        $actions = array(
            'edit' => sprintf('<a href="' . admin_url('post.php?post=' . $item['ID'] . '&action=edit') . '">' . __('Edit', 'reports') . '</a>'),
            'delete' => sprintf('<a href="?page=frontend-document-views-report&action=%s&view=%s&row_id=%s&_wpnonce=%s" onclick="return confirm(\'Are you sure you want to delete this entry?\')">' . __('Delete', 'reports') . '</a>', 'delete', $item['ID'], $item['row_id'], $delete_log_nonce),
            'view-counts' => sprintf('<a href="' . admin_url('admin.php?page=frontend-document-views-report&action=specific-item-report&document_id=' . $item['ID'] ). '">' . __('View Counts', 'reports') . '</a>'),
        );

        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            $item['title'],
            $item['ID'],
            $this->row_actions($actions)
        );

    }

    function column_cb($item) {

        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item['row_id']
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

        global $wpdb;

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

                $del_row = $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'frontend_doc_views WHERE id = "' . $row_id . '"');
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
            $action = 'delete_view_log_entry';
            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            $row_id = filter_input(INPUT_GET, 'row_id', FILTER_SANITIZE_STRING);
            $del_row = $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'frontend_doc_views WHERE id = "' . $row_id . '"');

            if ($del_row) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Entry Deleted!', 'reports') . '</strong></p></div>';
            } else {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Error', 'reports') . '</strong></p></div>';
            }

        }

        

    }

    function prepare_items() {

        global $wpdb;
        
        $i = 1;
        $cats = '';
        $where = '';
        $per_page = '50';
        $post_id = 'post_id';
        $table_name = $wpdb->prefix . 'frontend_doc_views';
        $whereClauseArray = [];
        $data = array();

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $current_page = $this->get_pagenum();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();

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

        // if any filter apply / filter button clicked

        if(isset($_REQUEST['views_filters_action'])) {

            // if company selected

            if ( isset($_REQUEST[ 'company_dropdown' ]) && !empty($_REQUEST[ 'company_dropdown' ]) ) {

                $company_id = $_REQUEST['company_dropdown'];
                $where = 'WHERE company_id= ' . $company_id . '';
                $whereClauseArray[] = 'company_dropdown';

            }            

            // if account manager selected

            if ( isset($_REQUEST[ 'acc_manager' ]) && !empty($_REQUEST[ 'acc_manager' ]) ) {

                $account_manager = $_REQUEST['acc_manager'];
                $where .= 'WHERE account_manager= "' . $account_manager . '"';
                $whereClauseArray[] = 'acc_manager';

            }

            // if user status selected

            if ( isset($_REQUEST[ 'user_status' ]) && !empty($_REQUEST[ 'user_status' ]) ) {

                $user_status = $_REQUEST['user_status'];
                $where .= 'WHERE user_status= "' . $user_status . '"';
                $whereClauseArray[] = 'user_status';

            }

            if ( !empty($whereClauseArray) ) {

                // if multiple items selected
    
                if(count($whereClauseArray) > 1) {

                    $where = str_replace('WHERE', ' AND', $where); // replace all 'WHERE' words to 'AND'
                    $where = preg_replace('/AND/', 'WHERE', $where, 1); // replace first word in string to 'WHERE'
        
                }

            }

        }

        if(isset($_REQUEST['document_id']) && !empty($_REQUEST['document_id'])){
            // for specific document view logs
            $view_id = sanitize_text_field($_REQUEST['document_id']);
            $query = "SELECT * FROM $table_name WHERE $post_id = $view_id ORDER BY $orderby_column $sort_order";
            $count_query = "SELECT COUNT(*) FROM ( SELECT $post_id FROM $table_name WHERE $post_id = $view_id ) AS count_query";
            $total_items = $wpdb->get_var($count_query);
        } else {
            // for all view logs
            $query = "SELECT *, COUNT(*) AS counts FROM $table_name $where GROUP BY $post_id ORDER BY $orderby_column $sort_order";
            $count_query = "SELECT COUNT(*) FROM ( SELECT $post_id FROM $table_name $where GROUP BY $post_id) AS Agg";
            $total_items = $wpdb->get_var($count_query);
        }

        $offset = ($current_page - 1) * $per_page;
        $query.=' LIMIT ' . (int) $offset . ',' . (int) $per_page;

        $data_results = $wpdb->get_results($query);
        
        if(isset($_REQUEST['document_id']) && !empty($_REQUEST['document_id'])){

            foreach ($data_results as $data_result) {

                $business = $corporate = $admin = '';
                $get_user = get_userdata($data_result->user_id);
                $userRoleArray = $get_user->roles;

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
                    $business =  $business_term->name;
                }

                $user_corporate_id = get_the_author_meta( 'corporate_companies', $data_result->user_id );
                if (isset($user_corporate_id) && !empty($user_corporate_id)) {
                    $corporate_term = get_term( $user_corporate_id, 'company' );
                    $corporate = $corporate_term->name;
                }


                $user_admin_id = get_the_author_meta( 'admin_companies', $data_result->user_id );
                if (isset($user_admin_id) && !empty($user_admin_id)) {
                    $admin_term = get_term( $user_admin_id, 'company' );
                    $admin =  $admin_term->name;
                }


                $account_manager = get_the_author_meta( 'account_manager', $data_result->user_id );
                if (isset($account_manager) && !empty($account_manager)) {
                    $account_manager_detail = get_user_by( 'email', $account_manager );
                    if ($account_manager_detail) {
                        $account_manager_name =  get_the_author_meta( 'first_name', $account_manager_detail->ID ) . ' ' . get_the_author_meta( 'last_name', $account_manager_detail->ID );
                    }
                }

                $terms = get_the_terms( $data_result->post_id , 'frontend_documents_category' );
                
                if(is_array($terms)){
                    foreach ( $terms as $term ) {
                        $cats .= $term->name;
                        $cats .= ($i < count($terms))? ", " : "";
                        $i++;
                    }
                }

                $count_views = "SELECT COUNT(*) FROM $table_name WHERE post_id = $data_result->post_id";
                $total_views = $wpdb->get_var($count_views);
                $counts = '<a href="' . admin_url('admin.php?page=frontend-document-views-report&action=specific-item-report&document_id=' . $data_result->post_id ). '">' . $total_views . '</a>';

                // T&T and P&P getting for displaying in ListTable

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
                        'name' => $get_user->first_name." ".$get_user->last_name,
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
                $i = 1;
                $cats = '';
                $terms = get_the_terms( $data_result->post_id , 'frontend_documents_category' );
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
                $counts = '<a href="' . admin_url('admin.php?page=frontend-document-views-report&action=specific-item-report&document_id=' . $data_result->post_id ). '">' . $total_views . '</a>';

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
        
        $this->items = $data;   

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));


    }

    /**
     * Add extra filters inside the views list table
     */

    function extra_tablenav( $which ) {

        if( $which == 'top' ){

            if($_REQUEST['page'] == 'frontend-document-views-report' && empty($_REQUEST['document_id'])) {

                $this->companies_filter();
    
                $this->authors_filter();
        
                $this->user_status_filter();
                    
                submit_button( __( 'Filter' ), '', 'views_filters_action', false );

            }

        }

    }

    /**
     * Companies filter
     */

    function companies_filter() {

        //Get selected company
        if ( isset( $_REQUEST[ 'company_dropdown' ]) ) {
            $company_section = $_REQUEST[ 'company_dropdown' ];
        } else {
            $company_section = '';
        }
        
        //Get selected company value
        $selected = selected( $company_section, $value, false );

        $company_options = array(
            'show_option_all' => __("Select Company..."),
            'taxonomy'        => 'company',
            'name'            => 'company_dropdown',
            'orderby'         => 'name',
            'selected'        => $company_section,
            'value_field' => 'id',
            'hierarchical' => 1,
            'hide_empty'      => 0,
            'class'              => 'company-dropdown',
        );

        wp_dropdown_categories( $company_options );

    }

    /**
     * Authors filter
     */

    function authors_filter() {

        global $wpdb;

        $users_table = $wpdb->prefix .'users';
        $user_meta_table = $wpdb->prefix .'usermeta';
        $capabilities = $wpdb->prefix .'capabilities';

        if ( isset( $_REQUEST[ 'acc_manager' ]) ) {
            $acc_manager_section = $_REQUEST[ 'acc_manager' ];
        } else {
            $acc_manager_section = '';
        }
        
        $acc_manager_select_html = '<select class="account-manager-filter" name="acc_manager"><option value="">%s</option>%s</select>';

        $acc_manager_query = "SELECT u.ID, u.user_email
            FROM $users_table u, $user_meta_table m
            WHERE u.ID = m.user_id
            AND m.meta_key LIKE '$capabilities'
            AND m.meta_value LIKE '%account_manager%'";

        $acc_managers = $wpdb->get_results($acc_manager_query);

        foreach ( $acc_managers as $acc_manager ) {
            
            $acc_manager_selected = selected( $acc_manager_section, $acc_manager->user_email, false);
            $first_name = get_the_author_meta( 'first_name', $acc_manager->ID );
            $options .=  '<option value="' . $acc_manager->user_email . '"' . $acc_manager_selected . '>';
            
                if (isset($first_name) && !empty($first_name)) {
                    $options .= $first_name . ' ' . get_the_author_meta( 'last_name', $acc_manager->ID );
                } else {
                    $options .= $acc_manager->user_email;
                }

            $options .= '</option>';

        }

        $acc_manager_select = sprintf( $acc_manager_select_html, __( 'Select account manager...' ), $options );

        echo $acc_manager_select;

    }

    /**
     * User status filter
     */

    function user_status_filter() {

        if ( isset( $_REQUEST[ 'user_status' ]) ) {
            $status_section = $_REQUEST[ 'user_status' ];
        } else {
            $status_section = '';
        }

        $status_section_html = '<select class="user-status-filter" name="user_status"><option value="">%s</option>%s</select>';

        $enable_status_selected = $disable_status_selected = '';
        if ($status_section == 'enable') {
            $enable_status_selected = ' selected="selected"';
        }
        else if ($status_section == 'disable') {
            $disable_status_selected = ' selected="selected"';
        }

        $user_status_options = '<option value="enable"' . $enable_status_selected . '>Enable</option>
        <option value="disable"' . $disable_status_selected . '>Disable</option>';

        $status_select = sprintf( $status_section_html, __( 'Select user status...' ), $user_status_options );

        echo $status_select;

    }

}
