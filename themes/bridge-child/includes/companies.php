<?php
if (!function_exists('companies_custom_taxonomy')) {

	// Register Custom Companies
    function companies_custom_taxonomy() {

    	$company_labels = array(
            'name' => 'Companies',
            'singular_name' => 'Company',
            'menu_name' => 'Companies',
            'search_items' => 'Search Company',
            'popular_items' => 'Popular Companies',
            'all_items' => 'All Companies',
            'edit_item' => 'Edit Company',
            'update_item' => 'Update Company',
            'add_new_item' => 'Add New Company',
            'new_item_name' => 'New Company Name',
        );

        $company_args = array(
            'hierarchical' => true,
            'labels' => $company_labels,
            'public' => true,
            'capabilities' => array(
                'manage_terms' => 'manage_company',
                'edit_terms' => 'edit_company',
                'delete_terms' => 'delete_company',
                'assign_terms' => 'assign_company',
            ),
            'update_count_callback' => function() {
                return; //important
            }
        );

        register_taxonomy('company', 'user', $company_args);
    }

    add_action('init', 'companies_custom_taxonomy');
}


function add_user_company_admin_page() {
    $taxonomy = get_taxonomy('company');
    add_users_page(
            esc_attr($taxonomy->labels->menu_name), //page title
            esc_attr($taxonomy->labels->menu_name), //menu title
            $taxonomy->cap->manage_terms, //capability
            'edit-tags.php?taxonomy=' . $taxonomy->name //menu slug
    );
}

add_action('admin_menu', 'add_user_company_admin_page');

function set_user_company_submenu_active($submenu_file) {
    global $parent_file;
    if ('edit-tags.php?taxonomy=' . 'company' == $submenu_file) {
        $parent_file = 'users.php';
    }
    return $submenu_file;
}

add_filter('submenu_file', 'set_user_company_submenu_active');

function update_users_company_count($new_terms_ids, $meta_key) {
    global $wpdb;

    if (empty($new_terms_ids)) {
        return;
    } else {
        $usermeta = 'wp_usermeta';
        $query = "SELECT COUNT(*) FROM $usermeta WHERE meta_key = '$meta_key' AND meta_value LIKE '%$new_terms_ids%'";
        $count = $wpdb->get_var($query);
        $wpdb->update($wpdb->term_taxonomy, array('count' => $count), array('term_taxonomy_id' => $new_terms_ids));
    }
}

add_filter('pre_insert_term', function($term, $tax) {
    if (('company' === $tax) && isset($_POST['users_comapny_state']) && empty($_POST['users_comapny_state'])) {
        return new \WP_Error('empty_term_name', __('State cannot be empty!', 'text-domain'));
    } else {
        return $term;
    }
}, -1, 2);

function create_company_meta( $term_id, $tt_id ){
    $current_user = wp_get_current_user();
    add_term_meta($term_id, 'company_author', $current_user->ID );
    add_term_meta($term_id, 'company_created_date', current_time('mysql') );
}
add_action( 'create_company', 'create_company_meta', 10, 2 );

function edit_company_meta( $term_id, $tt_id ){
    $current_user = wp_get_current_user();
    update_term_meta( $term_id, 'company_last_edit', current_time('mysql') );
    update_term_meta( $term_id, 'company_edit_by', $current_user->ID );
}
add_action( 'edit_company', 'edit_company_meta', 10, 2 );

function add_company_columns($columns){
    $columns['state'] = 'State';
    $columns['company_status'] = 'Company Status';
    $columns['excess_to_frontend_documents'] = 'Access Document Library';
    $columns['company_date_author'] = 'Created on';
    $columns['last_modified'] = 'Last Modified on';
    return $columns;
}

add_filter('manage_edit-company_columns', 'add_company_columns');

function add_company_column_content($content,$column_name,$term_id){
    switch ($column_name) {
        case 'state':
            $content = get_term_meta( $term_id, 'users_comapny_state', true);
            break;
        case 'company_status':
            $users_comapny_company_status = get_term_meta( $term_id, 'users_comapny_company_status', true);
            if ($users_comapny_company_status) {
                $content = $users_comapny_company_status;
            } else {
                $content = 'none';
            }
            break;
        case 'excess_to_frontend_documents':
            $excess_to_frontend_documents = get_term_meta( $term_id, 'users_comapny_company_frontend_documents_access', true);
            if ($excess_to_frontend_documents) {
                $content = $excess_to_frontend_documents;
            } else {
                $content = '-';
            }
            break;
        case 'company_date_author':
            $company_author = get_term_meta( $term_id, 'company_author', true);
            $company_created_date = get_term_meta( $term_id, 'company_created_date', true);
            if ($company_author) {
                $get_company_author = get_user_by( 'ID', $company_author );
                $content = $company_created_date.' by '.$get_company_author->display_name;
            } else {
                $content = '';
            }
            break;
        case 'last_modified':
            $company_last_edit = get_term_meta( $term_id, 'company_last_edit', true);
            $company_edit_by = get_term_meta( $term_id, 'company_edit_by', true);
            if ($company_edit_by) {
                $get_company_edit_by = get_user_by( 'ID', $company_edit_by );
                $content = $company_last_edit.' by '.$get_company_edit_by->display_name;
            } else {
                $content = '';
            }
            break;
        default:
            break;
    }
    return $content;
}
add_filter('manage_company_custom_column', 'add_company_column_content',10,3);