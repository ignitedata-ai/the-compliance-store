<?php

if (!function_exists('documents_custom_taxonomy')) {

// Register Custom Taxonomy
    function documents_custom_taxonomy() {

        $labels = array(
            'name' => _x('Categories', 'bridge-child'),
            'singular_name' => _x('Category', 'bridge-child'),
            'menu_name' => __('Categories', 'bridge-child'),
            'all_items' => __('All Categories', 'bridge-child'),
            'parent_item' => __('Parent Category', 'bridge-child'),
            'parent_item_colon' => __('Parent Category:', 'bridge-child'),
            'new_item_name' => __('New Category Name', 'bridge-child'),
            'add_new_item' => __('Add New Category', 'bridge-child'),
            'edit_item' => __('Edit Category', 'bridge-child'),
            'update_item' => __('Update Category', 'bridge-child'),
            'view_item' => __('View Category', 'bridge-child'),
            'separate_items_with_commas' => __('Separate categories with commas', 'bridge-child'),
            'add_or_remove_items' => __('Add or remove categories', 'bridge-child'),
            'choose_from_most_used' => __('Choose from the most used', 'bridge-child'),
            'popular_items' => __('Popular Categories', 'bridge-child'),
            'search_items' => __('Search Categories', 'bridge-child'),
            'not_found' => __('Not Found', 'bridge-child'),
            'no_terms' => __('No Categories', 'bridge-child'),
            'items_list' => __('Categories list', 'bridge-child'),
            'items_list_navigation' => __('Categories list navigation', 'bridge-child'),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            /* 'rewrite' => array( 'slug' => '/', 'with_front' => false ), */
            'show_tagcloud' => true,
            'capabilities' => array(
                'manage_terms' => 'manage_documents_category',
                'edit_terms' => 'edit_documents_category',
                'delete_terms' => 'delete_documents_category',
                'assign_terms' => 'assign_documents_category',
            ),
        );
        register_taxonomy('documents_category', array('documents'), $args);

        $labels_tags = array(
            'name' => _x('Tags', 'Taxonomy General Name', 'bridge-child'),
            'singular_name' => _x('Tag', 'Taxonomy Singular Name', 'bridge-child'),
            'menu_name' => __('Tags', 'bridge-child'),
            'all_items' => __('All Tags', 'bridge-child'),
            'parent_item' => __('Parent Tag', 'bridge-child'),
            'parent_item_colon' => __('Parent Tag:', 'bridge-child'),
            'new_item_name' => __('New Tag Name', 'bridge-child'),
            'add_new_item' => __('Add New Tag', 'bridge-child'),
            'edit_item' => __('Edit Tag', 'bridge-child'),
            'update_item' => __('Update Tag', 'bridge-child'),
            'view_item' => __('View Tag', 'bridge-child'),
            'separate_items_with_commas' => __('Separate tags with commas', 'bridge-child'),
            'add_or_remove_items' => __('Add or remove tags', 'bridge-child'),
            'choose_from_most_used' => __('Choose from the most used', 'bridge-child'),
            'popular_items' => __('Popular Tags', 'bridge-child'),
            'search_items' => __('Search Tags', 'bridge-child'),
            'not_found' => __('Not Found', 'bridge-child'),
            'no_terms' => __('No Tags', 'bridge-child'),
            'items_list' => __('Tags list', 'bridge-child'),
            'items_list_navigation' => __('Tags list navigation', 'bridge-child'),
        );
        $args_tags = array(
            'labels' => $labels_tags,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'capabilities' => array(
                'manage_terms' => 'manage_documents_tag',
                'edit_terms' => 'edit_documents_tag',
                'delete_terms' => 'delete_documents_tag',
                'assign_terms' => 'assign_documents_tag',
            ),
        );
        register_taxonomy('documents_tag', array('documents'), $args_tags);

    }

    add_action('init', 'documents_custom_taxonomy');
}

/**
 * Register custom taxonomy for frontend documents
 */

if (!function_exists('frontend_documents_custom_taxonomy')) {

// Register Custom Taxonomy
    function frontend_documents_custom_taxonomy() {

        $labels = array(
            'name' => _x('Categories', 'bridge-child'),
            'singular_name' => _x('Category', 'bridge-child'),
            'menu_name' => __('Categories', 'bridge-child'),
            'all_items' => __('All Categories', 'bridge-child'),
            'parent_item' => __('Parent Category', 'bridge-child'),
            'parent_item_colon' => __('Parent Category:', 'bridge-child'),
            'new_item_name' => __('New Category Name', 'bridge-child'),
            'add_new_item' => __('Add New Category', 'bridge-child'),
            'edit_item' => __('Edit Category', 'bridge-child'),
            'update_item' => __('Update Category', 'bridge-child'),
            'view_item' => __('View Category', 'bridge-child'),
            'separate_items_with_commas' => __('Separate categories with commas', 'bridge-child'),
            'add_or_remove_items' => __('Add or remove categories', 'bridge-child'),
            'choose_from_most_used' => __('Choose from the most used', 'bridge-child'),
            'popular_items' => __('Popular Categories', 'bridge-child'),
            'search_items' => __('Search Categories', 'bridge-child'),
            'not_found' => __('Not Found', 'bridge-child'),
            'no_terms' => __('No Categories', 'bridge-child'),
            'items_list' => __('Categories list', 'bridge-child'),
            'items_list_navigation' => __('Categories list navigation', 'bridge-child'),
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true
        );
        register_taxonomy('frontend_documents_category', array('frontend_documents'), $args);

    }

    add_action('init', 'frontend_documents_custom_taxonomy');
}