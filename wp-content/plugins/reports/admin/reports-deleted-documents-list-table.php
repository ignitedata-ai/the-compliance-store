<?php

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Deleted_Documents_Logs extends WP_List_Table {

    function __construct() {

        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => __('Deleted Documents', 'reports'), // singular name of the listed records
            'plural' => __('Deleted Documents', 'reports'), // plural name of the listed records
            'ajax' => false        // does this table support ajax?
        ));
    }

    /**
     * Define columns
     */

    function column_default($item, $column_name) {
        
        switch ($column_name) {
            case 'title':
            case 'user':
            case 'date':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Define titles for columns
     */

    function column_title($item) {

        $delete_log_nonce = wp_create_nonce('documents_delete_log_entry');

        $actions = array(
            'delete' => sprintf('<a href="?page=frontend-documents-deleted-history&action=%s&document_id=%s&_wpnonce=%s" onclick="return confirm(\'Are you sure you want to delete this entry?\')">' . __('Delete', 'reports') . '</a>', 'delete', $item['ID'], $delete_log_nonce),
        );

        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
                /* $1%s */ $item['title'],
                /* $2%s */ $item['ID'],
                /* $3%s */ $this->row_actions($actions)
        );

    }

    /**
     * Define checkbox for selection of items
     */

    function column_cb($item) {

        return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                /* $1%s */ "document_id", //Let's simply repurpose the table's singular label ("Download")
                /* $2%s */ $item['ID'] //The value of the checkbox should be the record's id
        );
    }

    /**
     * Get all columns
     */

    function get_columns() {

        $columns['cb'] = '<input type="checkbox" />';
        $columns['title'] = __('Title', 'reports');
        $columns['user'] = __('User (Who Deleted Document)', 'reports');
        $columns['date'] = __('Date & Time', 'reports');

        return $columns;
    }

    /**
     * Define bulk actions
     */

    function get_bulk_actions() {

        $actions = array();
        $actions['delete2'] = __('Delete Permanently', 'reports');

        return $actions;
    }

    /**
     * Bulk action handler
     */

    function process_bulk_action() {

        global $wpdb;

        // if bulk 'Delete Permanently' was clicked
        if ('delete2' === $this->current_action()) {

            //Check bulk delete nonce
            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            if (!isset($_POST['document_id']) || $_POST['document_id'] == null) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('No entries were selected.', 'reports') . '</strong></p><p><em>' . __('Click to Dismiss', 'reports') . '</em></p></div>';
                return;
            }

            foreach ($_POST['document_id'] as $item) {

                $document_id = sanitize_text_field($item);
                if (!is_numeric($document_id)){
                    wp_die(__('Error! The row id value of a log entry must be numeric.', 'reports'));
                }

                $is_deleted = $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'reports_deleted_documents WHERE post_id = "' . $document_id . '"');

            }

            if ($is_deleted) {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Entries Deleted!', 'reports') . '</strong></p><p><em>' . __('Click to Dismiss', 'reports') . '</em></p></div>';
            } else {
                echo '<div id="message" class="updated fade"><p><strong>' . __('Error', 'reports') . '</strong></p><p><em>' . __('Click to Dismiss', 'reports') . '</em></p></div>';
            }
            
        }

        // If single entry 'Delete' was clicked
        if ('delete' === $this->current_action()) {

            //Check bulk delete nonce
            $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'documents_delete_log_entry';
            if (!wp_verify_nonce($nonce, $action)){
                wp_die(__('Nope! Security check failed!', 'reports'));
            }
            
            //Grab the row id
            $document_id = filter_input(INPUT_GET, 'document_id', FILTER_SANITIZE_STRING);
            
            $is_deleted = $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'reports_deleted_documents WHERE post_id = "' . $document_id . '"');
            if ($is_deleted) {
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
        $current_page = $this->get_pagenum();

        $this->_column_headers = array($columns, $hidden);
        $this->process_bulk_action();

        // Grab the sort inputs then sanitize the values before using it in the query. Use a whitelist approach to sanitize it.
        $orderby_column = isset($_GET['orderby'])? sanitize_text_field($_GET['orderby']):'';
        $sort_order = isset($_GET['order'])? sanitize_text_field($_GET['order']):'';
        if(empty($orderby_column)){
            $orderby_column = "counts";
            $sort_order = "DESC";
        }
        $sort_order = reports_sanitize_value_by_array($sort_order, array('DESC' => '1', 'ASC' => '1'));  

        // query to get data from database

        $post_id = 'post_id';
        $table_name = $wpdb->prefix . 'reports_deleted_documents';

        $query = "SELECT *, COUNT(*) AS counts FROM $table_name GROUP BY $post_id ORDER BY $orderby_column $sort_order";
        $count_query = "SELECT COUNT(*) FROM ( SELECT $post_id FROM $table_name GROUP BY $post_id) AS Agg";
        $total_items = $wpdb->get_var($count_query);
        
        $offset = ($current_page - 1) * $per_page;
        $query.=' LIMIT ' . (int) $offset . ',' . (int) $per_page; //Limit to query to only load a limited number of records

        $data_results = $wpdb->get_results($query);
        // Prepare the array with the correct index names that the table is expecting.
        $data = array();

        foreach ($data_results as $data_result) {

            $user = get_userdata($data_result->user_id);

            $data[] = array(
                'ID' => $data_result->post_id,
                'title' => $data_result->post_title,
                'user' => $user->display_name,
                'date' => $data_result->date_time
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