<?php

// Register Documents Custom Post Type
if (!function_exists('documents_custom_post_type')) {

    function documents_custom_post_type() {

        $labels = array(
            'name' => _x('Documents', 'bridge-child'),
            'singular_name' => _x('Document', 'bridge-child'),
            'menu_name' => __('Documents', 'bridge-child'),
            'name_admin_bar' => __('Documents', 'bridge-child'),
            'archives' => __('Documents Archives', 'bridge-child'),
            'attributes' => __('Document Attributes', 'bridge-child'),
            'parent_item_colon' => __('Parent Document:', 'bridge-child'),
            'all_items' => __('All Documents', 'bridge-child'),
            'add_new_item' => __('Add New Document', 'bridge-child'),
            'add_new' => __('Add New', 'bridge-child'),
            'new_item' => __('New Document', 'bridge-child'),
            'edit_item' => __('Edit Document', 'bridge-child'),
            'update_item' => __('Update Document', 'bridge-child'),
            'view_item' => __('View Document', 'bridge-child'),
            'view_items' => __('View Document', 'bridge-child'),
            'search_items' => __('Search Document', 'bridge-child'),
            'not_found' => __('Not found', 'bridge-child'),
            'not_found_in_trash' => __('Not found in Trash', 'bridge-child'),
            'featured_image' => __('Featured Image', 'bridge-child'),
            'set_featured_image' => __('Set featured image', 'bridge-child'),
            'remove_featured_image' => __('Remove featured image', 'bridge-child'),
            'use_featured_image' => __('Use as featured image', 'bridge-child'),
            'insert_into_item' => __('Insert into Document', 'bridge-child'),
            'uploaded_to_this_item' => __('Uploaded to this Document', 'bridge-child'),
            'items_list' => __('Documents list', 'bridge-child'),
            'items_list_navigation' => __('Documents list navigation', 'bridge-child'),
            'filter_items_list' => __('Filter Documents list', 'bridge-child'),
        );
        $capabilities = array(
            'edit_post' => 'edit_documents',
            'read_post' => 'read_documents',
            'delete_post' => 'delete_documents',
            'delete_posts' => 'delete_documents',
            'edit_posts' => 'edit_documents',
            'edit_others_posts' => 'edit_others_documents',
            'publish_posts' => 'publish_documents',
            'read_private_posts' => 'read_private_documents',
        );
        /* $rewrite = array(
          'slug'                  => '/',
          'with_front'            => false,
          'pages'                 => false,
          ); */
        $args = array(
            'label' => __('Document', 'bridge-child'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes'),
            /* 'taxonomies'            => array( 'category', 'post_tag' ), */
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-format-aside',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            //'rewrite'               => $rewrite,
            'capabilities' => $capabilities,
        );
        register_post_type('documents', $args);
    }

    add_action('init', 'documents_custom_post_type');
}

// Register Frontend Documents Custom Post Type
if (!function_exists('frontend_documents_custom_post_type')) {

    function frontend_documents_custom_post_type() {

        $labels = array(
            'name' => _x('Frontend Documents', 'bridge-child'),
            'singular_name' => _x('Frontend Document', 'bridge-child'),
            'menu_name' => __('Frontend Documents', 'bridge-child'),
            'name_admin_bar' => __('Frontend Documents', 'bridge-child'),
            'archives' => __('Frontend Documents Archives', 'bridge-child'),
            'attributes' => __('Frontend Document Attributes', 'bridge-child'),
            'parent_item_colon' => __('Parent Frontend Document:', 'bridge-child'),
            'all_items' => __('All Frontend Documents', 'bridge-child'),
            'add_new_item' => __('Add New Frontend Document', 'bridge-child'),
            'add_new' => __('Add New', 'bridge-child'),
            'new_item' => __('New Frontend Document', 'bridge-child'),
            'edit_item' => __('Edit Frontend Document', 'bridge-child'),
            'update_item' => __('Update Frontend Document', 'bridge-child'),
            'view_item' => __('View Frontend Document', 'bridge-child'),
            'view_items' => __('View Frontend Documents', 'bridge-child'),
            'search_items' => __('Search Frontend Documents', 'bridge-child'),
            'not_found' => __('Not found', 'bridge-child'),
            'not_found_in_trash' => __('Not found in Trash', 'bridge-child'),
            'featured_image' => __('Featured Image', 'bridge-child'),
            'set_featured_image' => __('Set featured image', 'bridge-child'),
            'remove_featured_image' => __('Remove featured image', 'bridge-child'),
            'use_featured_image' => __('Use as featured image', 'bridge-child'),
            'insert_into_item' => __('Insert into Frontend Document', 'bridge-child'),
            'uploaded_to_this_item' => __('Uploaded to this Frontend Document', 'bridge-child'),
            'items_list' => __('Frontend Documents list', 'bridge-child'),
            'items_list_navigation' => __('Frontend Documents list navigation', 'bridge-child'),
            'filter_items_list' => __('Filter Frontend Documents list', 'bridge-child'),
        );
        $args = array(
            'label' => __('Frontend Document', 'bridge-child'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'thumbnail', 'author', 'custom-fields', 'page-attributes'),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 6,
            'menu_icon' => 'dashicons-format-aside',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'rewrite' => array('slug' => 'frontend-documents')
        );
        register_post_type('frontend_documents', $args);
    }

    add_action('init', 'frontend_documents_custom_post_type');
}

/**
 * Custom filter for custom post type (frontend documents)
 */

function custom_users_filter_for_cpt( $post_type, $which ) {

    if ( 'frontend_documents' !== $post_type ) {
        return;
    }

    companies_filter();
    
    authors_filter();
    
}

add_action( 'restrict_manage_posts', 'custom_users_filter_for_cpt', 10, 2 );

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

    $users = get_users( array( 
        'fields' => array( 
            'ID', 'display_name',
        ),
        'has_published_posts'   =>  [ 'frontend_documents' ]
    ) );

    if ( isset( $_REQUEST[ 'author_dropdown' ]) ) {
        $selectedAuthor = $_REQUEST[ 'author_dropdown' ];
    } else {
        $selectedAuthor = '';
    }
    
    $authors_select_html = '<select name="author_dropdown"><option value="">%s</option>%s</select>';

    foreach ( $users as $user ) {

        $options .= '<option value="' . $user->ID . '" ' . ( $selectedAuthor == $user->ID ? 'selected="selected"' : '' ) . '>' . esc_html( $user->display_name ) . '</option>';
        
    }

    $authors_select = sprintf( $authors_select_html, __( 'Select author...' ), $options );

    echo $authors_select;

}

/**
 * Process the filters and modify the query
 */

function process_filters($query){

    global $post_type;

    if($post_type != 'frontend_documents') {

        return;

    }

    // company

    if(isset($_REQUEST['company_dropdown']) && !empty($_REQUEST['company_dropdown'])){

        $company_assigned = $_REQUEST['company_dropdown'];

        $meta_query = array(
            array(
                'key'    =>  'frontend_document_author_company',
                'value'  =>  $company_assigned,
                'compare' => 'LIKE'
            ),
        );

        $query->set('meta_query', $meta_query);

    }

    // author

    if(isset($_REQUEST['author_dropdown']) && !empty($_REQUEST['author_dropdown'])){

        $selected_author = $_REQUEST['author_dropdown'];

        $query->set('author', $selected_author);

    }

}

add_action('pre_get_posts', 'process_filters');