<?php
/**
 * @package 	WordPress
 * @subpackage 	Bridge Child
 * @version		1.0.0
 * 
 * Child Theme Functions File
 * 
 */

/**
 * Enqueue Child Theme styles & Scripts for admin panel
 */
function bridge_child_enqueue_styles() {
    $ver_cache = rand(10,10000);
    wp_enqueue_style('child-theme-style', get_stylesheet_directory_uri() . '/style.css', '', $ver_cache);
    /*wp_enqueue_style('mean-menu-style', get_stylesheet_directory_uri() . '/includes/assets/css/meanmenu.css');*/

    /* CSS file containe style for my-account page */
    if ( is_page_template( 'template-myaccount.php' ) ) {
    	wp_enqueue_style('myaccount-template-style', get_stylesheet_directory_uri() . '/includes/user-module/css/myaccount.css');
    	wp_enqueue_script( 'frontend-ajax', get_stylesheet_directory_uri() . '/includes/user-module/js/user-ajax.js', array('jquery'), null, true );
    }
    /* CSS file containe style for my-account page */
    if ( is_page_template( 'password-reset.php' ) ) {
    	wp_enqueue_script( 'pass-reset', get_stylesheet_directory_uri() . '/includes/assets/js/pass-reset.js', array('jquery'), null, true );
    }
    if (is_tax('documents_category') || is_tax('frontend_documents_category') || is_search() || is_page_template('template-most-popular.php') || is_page('view-all-documents') ) {
        wp_enqueue_style('child-theme-bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        wp_enqueue_script('child-theme-bootstrap-js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), null, true);
        wp_enqueue_style('child-theme-lazyload', get_stylesheet_directory_uri() . '/includes/assets/css/yt-lazyload.min.css');
        wp_enqueue_script('child-theme-lazyload-js', get_stylesheet_directory_uri() . '/includes/assets/js/yt-lazyload.min.js', array('jquery'), null, true);
    }
    if (is_singular( 'documents' )) {
        wp_enqueue_style('child-theme-lazyload', get_stylesheet_directory_uri() . '/includes/assets/css/yt-lazyload.min.css');
        wp_enqueue_script('child-theme-lazyload-js', get_stylesheet_directory_uri() . '/includes/assets/js/yt-lazyload.min.js', array('jquery'), null, true);
    }
    
    if (is_user_logged_in()) {
	$current_user = wp_get_current_user();

		wp_enqueue_script( 'zxcvbn-script', get_stylesheet_directory_uri() . '/includes/assets/js/zxcvbn.js', array(), true );
    	wp_enqueue_script( 'frontend-ajax', get_stylesheet_directory_uri() . '/includes/assets/js/custom-ajax.js', array('jquery'), $ver_cache, true );

		$business_company_status = $admin_company_status = $corporate_company_status = '';
	    $business_companies = get_user_meta($current_user->ID, 'business_companies', true );
	    $corporate_companies = get_user_meta($current_user->ID, 'corporate_companies', true );
	    $admin_companies = get_user_meta($current_user->ID, 'admin_companies', true );

	    if (isset($business_companies) && !empty($business_companies)) {
	        $business_company_status = get_term_meta( $business_companies, 'users_comapny_company_status', true);
	    }
	    if (isset($admin_companies) && !empty($admin_companies)) {
	        $admin_company_status = get_term_meta( $admin_companies, 'users_comapny_company_status', true);
	    }
	    if (isset($corporate_companies) && !empty($corporate_companies)) {
	        $corporate_company_status = get_term_meta( $corporate_companies, 'users_comapny_company_status', true);
	    }
		if ($business_company_status == 'disable' || $admin_company_status == 'disable' || $corporate_company_status == 'disable') {
			$custom_css = "nav.main_menu{display: none !important;}nav.mobile_menu{display: none !important;}";
        	wp_add_inline_style( 'child-theme-style', $custom_css );
		}
	}
	if ( is_page('login') ){
        wp_enqueue_script('child-theme-lazyload-js', get_stylesheet_directory_uri() . '/includes/assets/js/password-toggle.js', array('jquery'), null, true);
    }

    global $wp_query;
    wp_localize_script( 'frontend-ajax', 'frontend_ajax_object', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'site_url' => get_site_url(),
		'query_vars' => json_encode( $wp_query->query )
	));

    if( is_page('view-all-documents') || is_page('document-views-report') || is_page('document-downloads-report') ) {
        wp_enqueue_script('datatables', 'https://cdn.datatables.net/v/dt/dt-1.10.20/datatables.min.js', array('jquery') );
        wp_enqueue_script('datatables-sorting-plugin', 'https://cdn.datatables.net/plug-ins/1.11.3/sorting/natural.js', array('jquery') );
        wp_enqueue_style('datatables', 'https://cdn.datatables.net/v/dt/dt-1.10.20/datatables.min.css' );
    }

    if( is_page('view-all-documents') ) {
        wp_enqueue_style('sweetalert-style', get_stylesheet_directory_uri() . '/includes/assets/sweetalert2/sweetalert2.min.css', array(), '1.0.1', 'all');
        wp_enqueue_script('sweetalert-script', get_stylesheet_directory_uri() . '/includes/assets/sweetalert2/sweetalert2.min.js', array(), '1.0.1', true);
    }
    wp_register_script( 'pt-cst-script', get_stylesheet_directory_uri() . '/includes/assets/js/pt-cst.js', array( 'jquery' ), '1.0.0', true );	
    wp_enqueue_script( 'pt-cst-script' );
}
add_action('wp_enqueue_scripts', 'bridge_child_enqueue_styles');

 /* All user export */
 function my_script_enqueuer() {
    /* Ajax call */
        wp_enqueue_script("pt-all-exp-ajax-handle", get_stylesheet_directory_uri() .'/includes/user-module/js/all-user-export.js', array('jquery'), 1.1, true);
        wp_localize_script('pt-all-exp-ajax-handle', 'the_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
    add_action( 'admin_enqueue_scripts', 'my_script_enqueuer' );
    
//Dequeue CSS files for login and lost password page
function bridge_child_dequeue_unnecessary_styles() {
    if (!is_user_logged_in()) {
        //dequeue plugins/PDFEmbedder-premium/css/pdfemb-blocks.css
        wp_dequeue_style( 'pdfemb-gutenberg-block-backend-js' );
        wp_deregister_style( 'pdfemb-gutenberg-block-backend-js' );
        //dequeue plugins/everest-forms/assets/css/everest-forms.css
        wp_dequeue_style( 'everest-forms-general' );
        wp_deregister_style( 'everest-forms-general' );
        //dequeue plugins/wpforo/wpf-themes/classic/widgets.css
        wp_dequeue_style( 'wpforo-widgets' );
        wp_deregister_style( 'wpforo-widgets' );
        //dequeue plugins/wp-pagenavi/pagenavi-css.css
        wp_dequeue_style( 'wp-pagenavi' );
        wp_deregister_style( 'wp-pagenavi' );
        //dequeue plugins/flexy-breadcrumb/public/css/flexy-breadcrumb-public.css
        wp_dequeue_style( 'flexy-breadcrumb' );
        wp_deregister_style( 'flexy-breadcrumb' );
        //dequeue plugins/flexy-breadcrumb/public/css/font-awesome.min.css
        wp_dequeue_style( 'flexy-breadcrumb-font-awesome' );
        wp_deregister_style( 'flexy-breadcrumb-font-awesome' );
        //dequeue plugins/wpforo/wpf-themes/classic/colors.css
        wp_dequeue_style( 'wpforo-dynamic-style' );
        wp_deregister_style( 'wpforo-dynamic-style' );
        //dequeue plugins/wp-blog-and-widgets/css/styleblog.css
        wp_dequeue_style( 'cssblog' );
        wp_deregister_style( 'cssblog' );
	//dequeue plugins/wp-blog-and-widgets/assets/css/wpbaw-public.css
        wp_dequeue_style( 'wpbaw-public-style' );
        wp_deregister_style( 'wpbaw-public-style' );
    }
}
add_action( 'wp_print_styles', 'bridge_child_dequeue_unnecessary_styles' );

//Dequeue JS files for login and lost password page
function bridge_child_dequeue_unnecessary_scripts() {
    if (!is_user_logged_in()) {
        //dequeue plugins/flexy-breadcrumb/public/js/flexy-breadcrumb-public.js
        wp_dequeue_script( 'flexy-breadcrumb' );
        wp_deregister_script( 'flexy-breadcrumb' );
        //dequeue plugins/wp-last-modified-info/assets/js/frontend.min.js
        wp_dequeue_script( 'wplmi-frontend' );
        wp_deregister_script( 'wplmi-frontend' );
    }
}
add_action( 'wp_print_scripts', 'bridge_child_dequeue_unnecessary_scripts' );

/**
 * Enqueue Child Theme styles & Scripts for admin panel
 */
function bridge_child_enqueue_admin_styles($hook) {
    /* Style and css for admin view */
    wp_enqueue_style('user-module-admin-styles', get_stylesheet_directory_uri() . '/includes/user-module/css/admin.css');
    wp_enqueue_script('phone-mask', get_stylesheet_directory_uri() . '/includes/user-module/js/jquery.inputmask.js', array('jquery'), false, true);
    
    /* For date picker */
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'tiptipjs', get_stylesheet_directory_uri() . '/includes/user-module/js/jquery.tipTip.js' );
    wp_enqueue_style( 'tiptip', get_stylesheet_directory_uri() . '/includes/user-module/css/tipTip.css' );

    wp_enqueue_script('backend-ajax', get_stylesheet_directory_uri() . '/includes/user-module/js/admin.js', array ('jquery'), null, true );
    global $wp_query;
    wp_localize_script( 'backend-ajax', 'backend_ajax_object', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'query_vars' => json_encode( $wp_query->query ),
        'site_url' => get_site_url(),
        'child_theme_path'   =>  get_stylesheet_directory_uri(),
	));

	global $typenow;
    if ((($GLOBALS['pagenow'] == 'edit.php') && ($_GET['post_type'] == 'documents')) ||
            ($_GET['action'] == 'edit' && "documents" == $typenow) || (($GLOBALS['pagenow'] == 'edit.php') && ($_GET['post_type'] == 'frontend_documents')) ||
            ($_GET['action'] == 'edit' && "frontend_documents" == $typenow)) {
        wp_enqueue_style('sweetalert-style', get_stylesheet_directory_uri() . '/includes/assets/sweetalert2/sweetalert2.min.css', array(), '1.0.1', 'all');
        wp_enqueue_script('sweetalert-script', get_stylesheet_directory_uri() . '/includes/assets/sweetalert2/sweetalert2.min.js', array(), '1.0.1', true);
        wp_enqueue_script('backend-documents', get_stylesheet_directory_uri() . '/includes/assets/js/backend-documents-ajax.js', array(), '1.0.1', true);
        wp_localize_script('backend-documents', 'backend_doc_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php') ));
    }
}
add_action( 'admin_enqueue_scripts', 'bridge_child_enqueue_admin_styles' );

if ( ! function_exists( 'custom_nav_menu_location' ) ) {
 
    function custom_nav_menu_location (){
        register_nav_menus( array(
            'fd_categories_menu' => __( 'Frontend Documents Categories Menu', 'bridge-child' )
        ) );
    }
    add_action( 'after_setup_theme', 'custom_nav_menu_location', 0 );
}

/**
 * Include All AJAX-Callback Functions
 */
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/add_user_frontend.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/delete_document_attachments.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/delete_frontend_document_attachments.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/documents_ajax_pagination.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/generate_trusted_refer.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/get_ajax_documents.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/get_popular_documents.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/reset_user_pass.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/reset_user_password.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/set_security_question.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/update_profile_frontend.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/view-all-documents-ajax.php' );
require_once( get_stylesheet_directory() . '/includes/ajax-callbacks/delete-document.php' );

require_once( get_stylesheet_directory() . '/includes/CategoriesWalkerExtended.php' );
require_once( get_stylesheet_directory() . '/includes/class-wp-bootstrap-navwalker.php' );
require_once( get_stylesheet_directory() . '/includes/custom-post-registration.php' );
require_once( get_stylesheet_directory() . '/includes/taxonomy-registration.php' );
/*Including Custom Metabox functions*/
require_once locate_template('includes/metabox-functions.php');

/* Add user fields to user screens in admin panel ( Add user, edit profile, edit user )*/
require_once( get_stylesheet_directory() . '/includes/user-module/class-pt-user-fields.php' );

/* Contain functionality for importing users*/
require_once( get_stylesheet_directory() . '/includes/user-module/class-pt-import-users.php' );
/* Contain functionality for Exporting users reports*/
require_once( get_stylesheet_directory() . '/includes/user-module/class-pt-export-users-reports.php' );
/* Contain filter for coperate and admin users*/
require_once( get_stylesheet_directory() . '/includes/user-module/class-pt-filter-users.php' );
/* Contain functionality for companies taxonomy */
require_once( get_stylesheet_directory() . '/includes/companies.php' );
/* Contain functionality for Exporting companies reports */
require_once( get_stylesheet_directory() . '/includes/user-module/class-pt-export-companies.php' );
/* Contain functionality for frontend form submission */
require_once( get_stylesheet_directory() . '/frontend-document-submisison-handler.php' );
/* Contain functionality for select2 */
require_once( get_stylesheet_directory() . '/includes/cmb2-select2.php' );
/* Contain functionality for settings of frontend documents */
require_once( get_stylesheet_directory() . '/includes/settings-frontend-documents.php' );

/**
 * Change Upload Directory for documents CPT
 */
function change_documents_upload_dir($param) {
    $current_page = $_SERVER['HTTP_REFERER'];
    $id = $_REQUEST['post_id'];
    if ("documents" == get_post_type($id)) {
        $mydir = '/documents' . $param['subdir'];
        $param['path'] = $param['basedir'] . $mydir;
        $param['url'] = $param['baseurl'] . $mydir;
    } elseif (strpos($current_page, 'documents')) {
        $mydir = '/documents' . $param['subdir'];
        $param['path'] = $param['basedir'] . $mydir;
        $param['url'] = $param['baseurl'] . $mydir;
    }
    return $param;
}

function change_documents_upload($file) {
    add_filter('upload_dir', 'change_documents_upload_dir');
    return $file;
}

add_filter('wp_handle_upload_prefilter', 'change_documents_upload');

/**
 *  Redirect non-logged users to homepage upon accessing documents
 */
function redirect_to_home(){
	$current_user = wp_get_current_user();

	if (! is_user_logged_in()) {
		$site_url = site_url();

		if( isset($_GET['refferer'])  ) {
			$meta_value = $site_url.'?refferer='.$_GET['refferer'];

			$get_user = get_users(array(
                'blog_id'      => $GLOBALS['blog_id'],
                'meta_key'     => 'trusted_refer',
                'meta_value' => $meta_value,
                'meta_compare' => '=',
                'fields' => array( 'ID' )
            ));
            $user = get_user_by('ID', $get_user[0]->ID);
            if ( isset($user) && !empty($user) ) {
		        wp_set_current_user($user->ID, $user->user_login);
		        wp_set_auth_cookie($user->ID);
        	} else {
        		wp_redirect( wp_login_url() );
				die();
        	}
        } elseif (!is_page('login') && !is_page('lostpassword') && !is_page('reset-password')) {
			wp_redirect( wp_login_url() );
			die();
		}
	} elseif (is_user_logged_in()) {
		$user_status = get_user_meta($current_user->ID, 'user_status', true );

		$business_company_status = $admin_company_status = $corporate_company_status = '';
	    $business_companies = get_user_meta($current_user->ID, 'business_companies', true );
	    $corporate_companies = get_user_meta($current_user->ID, 'corporate_companies', true );
	    $admin_companies = get_user_meta($current_user->ID, 'admin_companies', true );
	    if (isset($business_companies) && !empty($business_companies)) {
	        $business_company_status = get_term_meta( $business_companies, 'users_comapny_company_status', true);
	    }
	    if (isset($admin_companies) && !empty($admin_companies)) {
	        $admin_company_status = get_term_meta( $admin_companies, 'users_comapny_company_status', true);
	    }
	    if (isset($corporate_companies) && !empty($corporate_companies)) {
	        $corporate_company_status = get_term_meta( $corporate_companies, 'users_comapny_company_status', true);
	    }
		if ($business_company_status == 'disable' || $admin_company_status == 'disable' || $corporate_company_status == 'disable' || $user_status == 'disable') {
			if (!is_page('my-account') && !is_page('password-reset') && !is_page('logout')) {
			
	        	include (get_stylesheet_directory() . '/template-access-denied.php');
	            exit;
	        }
		}
	}
}
add_action('template_redirect', 'redirect_to_home');
/**
 * Sort Documents Category archive by published date 
 *  */
function sort_documents_archive_loop($query) { 
    if ($query->is_tax('documents_category') && $query->is_main_query()) {

    	$term_order = get_term_meta( get_queried_object_id(), 'documents_category_order_documents', true);

    	if ($term_order == 'alphabatically') {
    		$args =  array( 'title' => 'ASC' );
    	} elseif ($term_order == 'date') {
    		$args =  array( 'date' => 'DESC' );
    	} elseif ($term_order == 'menu_order') {
    		$args =  array( 'menu_order' => 'ASC' );
    	}  else {
    		$args =  array( 'menu_order' => 'ASC', 'date' => 'DESC' );
    	}
    	$query->set( 'orderby', $args );

    	$current_user = wp_get_current_user();
		$tools_templates_access = get_user_meta($current_user->ID, 'tools_templates_access', true );
		$policies_procedures_access = get_user_meta($current_user->ID, 'policies_procedures_access', true );
                
		if($tools_templates_access == 'disable' || $policies_procedures_access == 'disable' ) {
                    // Exclude Terms by ID from Search and Archive Listings
                    $args = array(
                            'taxonomy' => 'documents_category',
                            'meta_query' => [
                                'relation' => 'OR',    
                                [
                                    'key'  => 'documents_category_users_tools_templates_access',
                                    'value' => 'disable',
                                    'compare' => '=',
                                ],
                                [
                                    'key'  => 'documents_category_users_policies_procedures_access',
                                    'value' => 'disable',
                                    'compare' => '=',
                                ],
                            ],
                        );

                        $terms = get_terms( $args );

                    $term_id_array = array();
                    foreach ($terms as $terms_id) {
                        $term_id_array[] = $terms_id->term_id;
                    }
                    $tax_query = array([
                        'taxonomy' => 'documents_category',
                        'field' => 'term_id',
                        'terms' => $term_id_array,
                        'operator' => 'NOT IN',
                    ]);
                    $query->set( 'tax_query', $tax_query );
                }
    }
    /*Alter WP Defualt Pagination for documents archive*/
	if (!is_admin() && $query->is_main_query()){
		if (is_tax('documents_category')) {
			$query->set('posts_per_page', 25);
		}
	}
}
add_action('pre_get_posts', 'sort_documents_archive_loop');

/**
  * Modify query for frontend documents taxonomy
  */

function fed_documents_archive_loop($query) {

    if ($query->is_tax( 'frontend_documents_category', get_queried_object() ) && !is_admin()) {

        $current_user_id = get_current_user_id();

        // companies

        $corporate_companies = get_user_meta($current_user_id, 'corporate_companies', true );
        $admin_companies = get_user_meta($current_user_id, 'admin_companies', true );

        if(!empty($admin_companies)) {
            $company_assigned_id = $admin_companies;
        }

        if(!empty($corporate_companies)) {
            $company_assigned_id = $corporate_companies;
        }

        $query->set('post_type', 'frontend_documents');
        $query->set('post_status', 'publish');

        if(!current_user_can( 'manage_options' )) {

            $meta_query = array(
                array(
                    'key'     => 'frontend_document_author_company',
                    'value'   => array( $company_assigned_id ),
                    'compare' => 'IN',
                ),
            );

            $query->set( 'meta_query', $meta_query );

        }

    }

}

add_action('pre_get_posts', 'fed_documents_archive_loop');

function get_deepest_terms($terms) {
    $deepest_terms = [];

    foreach ($terms as $term) {
        $children = get_term_children($term->term_id, $term->taxonomy);

        if (empty($children)) {
            // If no children, add the term itself
            $deepest_terms[] = $term;
        } else {
            // Fetch the last child (deepest child)
            $last_child_id = end($children);
            $deepest_terms[] = get_term($last_child_id, $term->taxonomy);
        }
    }

    return $deepest_terms;
}


function excludecategory($query) {

    if ($query->is_search && !is_admin()) {

        $current_user = wp_get_current_user();
        $tools_templates_access = get_user_meta($current_user->ID, 'tools_templates_access', true);
        $policies_procedures_access = get_user_meta($current_user->ID, 'policies_procedures_access', true );

        $tcs_search = get_option('tcs_search');

        if ($tcs_search == 'enable') {
        // Extended search (include search keyword in tags too)
            if ($tools_templates_access == 'disable' || $policies_procedures_access == 'disable' ) {

                // Exclude Terms by ID from Search
                if($tools_templates_access == 'disable'){
                        $args = array(
                            'taxonomy' => 'documents_category',
                            'meta_query' => [    
                                [
                                    'key'  => 'documents_category_users_tools_templates_access',
                                    'value' => 'disable',
                                    'compare' => '=',
                                ],
                            ],
                        );
                }
    
                if($policies_procedures_access == 'disable'){
                    $args = array(
                        'taxonomy' => 'documents_category',
                        'meta_query' => [    
                            [
                                'key'  => 'documents_category_users_policies_procedures_access',
                                'value' => 'disable',
                                'compare' => '=',
                            ],
                        ],
                    );
                }
    
                if($policies_procedures_access == 'disable' && $tools_templates_access == 'disable'){
                    $args = array(
                        'taxonomy' => 'documents_category',
                        'meta_query' => [
                            'relation' => 'OR',    
                            [
                                'key'  => 'documents_category_users_tools_templates_access',
                                'value' => 'disable',
                                'compare' => '=',
                            ],
                            [
                                'key'  => 'documents_category_users_policies_procedures_access',
                                'value' => 'disable',
                                'compare' => '=',
                            ],
                        ],
                    );
                }
    
                $terms = get_terms($args);
                $filtered_terms = get_deepest_terms($terms);
     
                 $term_id_array = array();
                 foreach ($filtered_terms as $terms_id) {
                     $term_id_array[] = $terms_id->term_id;
                 }
                 $tax_query = array([
                         'taxonomy' => 'documents_category',
                         'field' => 'term_id',
                         'terms' => $term_id_array,
                         'operator' => 'NOT IN',
                 ]);
                 $query->set('tax_query', $tax_query);
                 $query->set('post_type', array('documents','blog_post'));
                     
                 // Pass $term_id_array as a second argument to the filter
                 add_filter('posts_where', function($where) use ($term_id_array) {
                     return bridge_search_where($where, $term_id_array);
                 });
                 add_filter('posts_join', 'bridge_search_join');
                 add_filter('posts_groupby', 'bridge_search_groupby');
            } else {

                function bridge_search_where_for_enable_case($where) {
                    global $wpdb;
                    if (is_search()) {
                
                          $where .= "OR (t.name LIKE '%".get_search_query()."%' AND {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'documents')";
                    }
                    return $where;
                }

            add_filter('posts_where', 'bridge_search_where_for_enable_case');
            add_filter('posts_join', 'bridge_search_join');
            add_filter('posts_groupby', 'bridge_search_groupby');
            }

        } else if ($tcs_search == 'disable') {
            if ($tools_templates_access == 'disable' || $policies_procedures_access == 'disable') {
               // Exclude Terms by ID from Search and Archive Listings
               if($tools_templates_access == 'disable'){
                    $args = array(
                        'taxonomy' => 'documents_category',
                        'meta_query' => [    
                            [
                                'key'  => 'documents_category_users_tools_templates_access',
                                'value' => 'disable',
                                'compare' => '=',
                            ],
                        ],
                    );
               }

               if($policies_procedures_access == 'disable'){
                $args = array(
                    'taxonomy' => 'documents_category',
                    'meta_query' => [    
                        [
                            'key'  => 'documents_category_users_policies_procedures_access',
                            'value' => 'disable',
                            'compare' => '=',
                        ],
                    ],
                );
              }

              if($policies_procedures_access == 'disable' && $tools_templates_access == 'disable'){
                $args = array(
                    'taxonomy' => 'documents_category',
                    'meta_query' => [
                        'relation' => 'OR',    
                        [
                            'key'  => 'documents_category_users_tools_templates_access',
                            'value' => 'disable',
                            'compare' => '=',
                        ],
                        [
                            'key'  => 'documents_category_users_policies_procedures_access',
                            'value' => 'disable',
                            'compare' => '=',
                        ],
                    ],
                );
              }

              $terms = get_terms($args);
              $filtered_terms = get_deepest_terms($terms);

            $term_id_array = array();
            foreach ($filtered_terms as $terms_id) {
                $term_id_array[] = $terms_id->term_id;
            }
            $tax_query = array([
                    'taxonomy' => 'documents_category',
                    'field' => 'term_id',
                    'terms' => $term_id_array,
                    'operator' => 'NOT IN',
            ]);
            $query->set('tax_query', $tax_query);
            $query->set('post_type', array('documents','blog_post'));
            
            } else {
                // For both enable
                $terms = get_terms(array(
                    'taxonomy' => 'documents_category',
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => 'documents_category_users_policies_procedures_access',
                            'value' => array('none', 'disable'),
                            'compare' => 'IN'
                        ),
                        array(
                            'key' => 'documents_category_users_tools_templates_access',
                            'value' => array('none', 'disable'),
                            'compare' => 'IN'
                        ),
                    ),
                ));

                $term_id_array = array();
                foreach ($terms as $term) {
                    $term_id_array[] = $term->term_id;
                }

                $tax_query = array(
                    array(
                        'taxonomy' => 'documents_category',
                        'field'    => 'term_id',
                        'terms'    => $term_id_array,
                        'operator' => 'IN',  // Retrieve posts that belong to any of these terms
                    ),
                );
            // $query->set('tax_query', $tax_query);
            $args = array(
                'post_type' => array('documents','blog_post'),
                'tax_query' => $tax_query,
            );
            
            $query = new WP_Query($args);

            }

        }
    }
}

add_action('pre_get_posts', 'excludecategory');


function bridge_search_where($where, $term_id_array) {
    global $wpdb;
   
    if (is_search()) {
        // Prepare a string of term IDs to exclude
        $term_ids = implode(',', array_map('intval', $term_id_array));

        // Exclude posts associated with the term IDs in the $term_id_array
        $where .= $wpdb->prepare(" OR (t.name LIKE %s AND {$wpdb->posts}.post_status = 'publish' AND {$wpdb->posts}.post_type = 'documents' AND {$wpdb->posts}.ID NOT IN (
            SELECT tr.object_id 
            FROM {$wpdb->term_relationships} tr 
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
            WHERE tt.term_id IN ($term_ids)
        ))", '%' . get_search_query() . '%');
    }

    return $where;
}

function bridge_search_join($join) {
    global $wpdb;
    if (is_search()) {
        $join .= " LEFT JOIN {$wpdb->term_relationships} tr ON {$wpdb->posts}.ID = tr.object_id";
        $join .= " INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
        $join .= " INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id";
    }
    return $join;
}

function bridge_search_groupby($groupby) {
    global $wpdb;

    // We need to group on post ID
    $groupby_id = "{$wpdb->posts}.ID";
    if (!is_search() || strpos($groupby, $groupby_id) !== false) {
        return $groupby;
    }

    // Groupby was empty, use ours
    if (!strlen(trim($groupby))) {
        return $groupby_id;
    }

    // Wasn't empty, append ours
    return $groupby . ", " . $groupby_id;
}


/*Skip Confirmation Email Checkbox on Add new users*/
/*function skip_confirmation_email_checkbox_on_add_new_user($user){
	?>
    <table class="form-table">
		<?php
		if (!is_super_admin( $user_id )
			&& 
			(current_user_can('account_manager') || current_user_can('sales_admin'))) { ?>
			<tr>
				<th scope="row"><?php _e('Skip Confirmation Email') ?></th>
				<td><input type="checkbox" name="noconfirmation" value="1" <?php checked( $_POST['noconfirmation'], 1 ); ?> /> Add the user without sending an email that requires their confirmation.</td>
			</tr>
		<?php
		} ?>
		<tr class="form-field">
			<th scope="row"><label for="first_name">First Name </label></th>
			<td><input name="first_name" type="text" id="first_name" value=""></td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="last_name">Last Name </label></th>
			<td><input name="last_name" type="text" id="last_name" value=""></td>
		</tr>
	</table>
    <?php
}
add_action( "user_new_form", "skip_confirmation_email_checkbox_on_add_new_user" );

function custom_user_register( $user_id ) {
    if ( ! empty( $_POST['first_name'] ) ) {
        update_user_meta( $user_id, 'first_name', trim( $_POST['first_name'] ) );
    }
    if ( ! empty( $_POST['last_name'] ) ) {
        update_user_meta( $user_id, 'last_name', trim( $_POST['last_name'] ) );
    	
    }
}
add_action( 'user_register', 'custom_user_register' ); */

/* function auto_activate_users_on_add_new_user($user, $user_email, $key, $meta){

	if(!current_user_can('manage_options')) {
        return false;
	}
	if (!empty($_POST['noconfirmation']) && $_POST['noconfirmation'] == 1) {
		wpmu_activate_signup($key);
		return false;
	} 
}
add_filter('wpmu_signup_user_notification', 'auto_activate_users_on_add_new_user', 10, 4);*/


/*function bbg_wpmu_welcome_user_notification($user_id, $password, $meta = '') {
    global $current_site;

    $welcome_email = get_site_option( 'welcome_user_email' );

    $user = new WP_User($user_id);

    $welcome_email = apply_filters( 'update_welcome_user_email', $welcome_email, $user_id, $password, $meta);

    // Get the current blog name
    $blogname = get_option( 'blogname' );
    $welcome_email = str_replace( 'SITE_NAME', $blogname, $welcome_email );

    $welcome_email = str_replace( 'USERNAME', $user->user_login, $welcome_email );
    $welcome_email = str_replace( 'PASSWORD', $password, $welcome_email );
    $welcome_email = str_replace( 'LOGINLINK', wp_login_url(), $welcome_email );

    $admin_email = get_site_option( 'admin_email' );

    if ( $admin_email == '' )
         $admin_email = 'support@' . $_SERVER['SERVER_NAME'];

    $message = $welcome_email;

    $subject = apply_filters( 'update_welcome_user_subject', sprintf(__('New %1$s User: %2$s'), $blogname, $user->user_login) );
    wp_mail($user->user_email, $subject, $message);

    return false; // make sure wpmu_welcome_user_notification() doesn't keep running
}
add_filter( 'wpmu_welcome_user_notification', 'bbg_wpmu_welcome_user_notification', 10, 3 );*/

/*Disable Admin notification upon password reset*/
//add_filter( 'send_password_change_email', '__return_false' );


/*Disable welcome email to newly added users*/
/*add_filter( 'wpmu_welcome_notification', '__return_false' ); */

function set_custom_id_documents_column($columns) {
    $columns['id'] = 'Document ID';

    return $columns;
}

add_filter( 'manage_documents_posts_columns', 'set_custom_id_documents_column' );

function custom_documents_column_data( $column, $post_id ) {
    if( 'id' == $column ) {
    	echo $post_id;
  	}
}

add_action( 'manage_documents_posts_custom_column' , 'custom_documents_column_data', 10, 2 );

/**
 * Custom column for frontend documents table
 */

function register_custom_frontend_documents_column($columns) {
    $columns['id'] = 'Document ID';
    $columns['company'] = 'Company';

    return $columns;
}

add_filter( 'manage_frontend_documents_posts_columns', 'register_custom_frontend_documents_column' );

/**
 * Custom column data for frontend documents table
 */

function custom_frontend_documents_column_data( $column, $post_id ) {

    $company_assigned_id = '';
    $authorID = get_the_author_meta( 'ID' );

    // companies

    $corporate_companies = get_user_meta($authorID, 'corporate_companies', true );
    $admin_companies = get_user_meta($authorID, 'admin_companies', true );

    if(!empty($admin_companies)) {
        $company_assigned_id = $admin_companies;
    }

    if(!empty($corporate_companies)) {
        $company_assigned_id = $corporate_companies;
    }

    $term = get_term( $company_assigned_id );

    if( 'id' == $column ) {
    	echo $post_id;
  	}

    if( 'company' == $column ) {
    	echo $term->name;
  	}
      
}

add_action( 'manage_frontend_documents_posts_custom_column' , 'custom_frontend_documents_column_data', 10, 2 );

/*Filter documents by category in backend*/
function filter_documents_by_category() {
	global $typenow;
	$post_type = 'documents';
	$taxonomy  = 'documents_category';
	if ($typenow == $post_type) {
		$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
		$info_taxonomy = get_taxonomy($taxonomy);
		wp_dropdown_categories(array(
			'show_option_all' => __("Show All {$info_taxonomy->label}"),
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'orderby'         => 'name',
			'selected'        => $selected,
			'show_count'      => true,
			'value_field' => 'slug',
			'hierarchical' => 1,
			'hide_empty'      => true,
		));
	};
}
add_action('restrict_manage_posts', 'filter_documents_by_category');

/*add_action( 'load-profile.php', function() {
    if( ! current_user_can( 'manage_options' ) )
        exit( wp_safe_redirect( admin_url() ) );
} );*/

/*Remove dashboard access for subscribers*/
function remove_dashboard_access() {

  if(
	  	is_admin() && !defined('DOING_AJAX') && 
	  	( ! 
	  		( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) )
	  	)
	) {
    wp_redirect(home_url());
    exit;
  }
}
add_action('init', 'remove_dashboard_access');

/*function cc_hide_admin_bar() {
  if ( ! ( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) ) )
    show_admin_bar(false);
}
add_action('set_current_user', 'cc_hide_admin_bar');*/

function remove_admin_bar() {
	if (! ( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) ) ) {
	  show_admin_bar(false);
	}
}
add_action('after_setup_theme', 'remove_admin_bar');

function pt_column_users( $defaults ) {
	$defaults['pt-usercolumn-user-status'] = __('Status', 'user-column');
    $defaults['pt-usercolumn-tools-templates'] = __('T&T Access', 'user-column');
    $defaults['pt-usercolumn-policies-procedures'] = __('P&P Access', 'user-column');
    $defaults['pt-usercolumn-company-status'] = __('Company Status', 'user-column');
    $defaults['pt-usercolumn-docs-upload-access'] = __('Docs Upload Access', 'user-column');
    $defaults['pt-usercolumn-bussiness'] = __('Business', 'user-column');
    $defaults['pt-usercolumn-corporate-facility'] = __('Corporate Facility', 'user-column');
    $defaults['pt-usercolumn-admin-facility'] = __('Admin Facility', 'user-column');
    $defaults['pt-usercolumn-account-manager'] = __('Account Manager', 'user-column');
    $defaults['pt-usercolumn-export-user'] = __('Export Userdata & Logs', 'user-column');
    return $defaults;
}

add_filter('manage_users_columns', 'pt_column_users', 15, 1);

function pt_custom_column_users($value, $column_name, $id) {
	if( $column_name == 'pt-usercolumn-user-status' ) {
		return ucwords(get_user_meta( $id, 'user_status', true ));
    } elseif( $column_name == 'pt-usercolumn-tools-templates' ) {
		return ucwords(get_the_author_meta( 'tools_templates_access', $id ));
    } elseif( $column_name == 'pt-usercolumn-policies-procedures' ) {
                
                $get_val = get_the_author_meta( 'policies_procedures_access', $id );
                
                if( empty( $get_val ) ) {
                    update_user_meta($id, 'policies_procedures_access', 'enable');
                }
                
                return ucwords( get_the_author_meta( 'policies_procedures_access', $id ) );
		
    } elseif( $column_name == 'pt-usercolumn-company-status' ) {

        $business_company_status = $admin_company_status = $corporate_company_status = '';
        $business_companies = get_the_author_meta( 'business_companies', $id );
        $corporate_companies = get_the_author_meta( 'corporate_companies', $id );
        $admin_companies = get_the_author_meta( 'admin_companies', $id );

        if (isset($business_companies) && !empty($business_companies)) {
            $business_company_status = get_term_meta( $business_companies, 'users_comapny_company_status', true);
        }
        if (isset($admin_companies) && !empty($admin_companies)) {
            $admin_company_status = get_term_meta( $admin_companies, 'users_comapny_company_status', true);
        }
        if (isset($corporate_companies) && !empty($corporate_companies)) {
            $corporate_company_status = get_term_meta( $corporate_companies, 'users_comapny_company_status', true);
        }
        if ($business_company_status == 'disable' || $admin_company_status == 'disable' || $corporate_company_status == 'disable') {
            return "Disable";
        } else {
            return "Enable";
        }
    } elseif( $column_name == 'pt-usercolumn-bussiness' ) {
        $user_business_id = get_the_author_meta( 'business_companies', $id );
        if (isset($user_business_id) && !empty($user_business_id)) {
            $term = get_term( $user_business_id, 'company' );
            return '<a href="users.php?page=pt-users-filter&company_id='.$term->term_id.'">'. $term->name.'</a>';
        }
    } elseif( $column_name == 'pt-usercolumn-docs-upload-access' ) {
    	$is_allowed = get_the_author_meta( 'frontend_docs_upload_access', $id );
    	if ($is_allowed == 'disable') {
            return "Disable";
    	} else {
            return "Enable";
        }
    } elseif( $column_name == 'pt-usercolumn-corporate-facility' ) {
        $user_corporate_id = get_the_author_meta( 'corporate_companies', $id );
        if (isset($user_corporate_id) && !empty($user_corporate_id)) {
            $term = get_term( $user_corporate_id, 'company' );
            return '<a href="users.php?page=pt-users-filter&company_id='.$term->term_id.'">'. $term->name.'</a>';
        }
    } elseif( $column_name == 'pt-usercolumn-admin-facility' ) {
        $user_admin_id = get_the_author_meta( 'admin_companies', $id );
        if (isset($user_admin_id) && !empty($user_admin_id)) {
            $term = get_term( $user_admin_id, 'company' );
            return '<a href="users.php?page=pt-users-filter&company_id='.$term->term_id.'">'. $term->name.'</a>';
        }
    } elseif( $column_name == 'pt-usercolumn-account-manager' ) {
        $user_email = get_the_author_meta( 'account_manager', $id );
        if (isset($user_email) && !empty($user_email)) {
            $user_detail = get_user_by( 'email', $user_email );
            if ($user_detail) {
                return '<a href="users.php?page=pt-users-filter&account-manager='.get_the_author_meta( 'account_manager', $id ).'">'. get_the_author_meta( 'first_name', $user_detail->ID ) . ' ' . get_the_author_meta( 'last_name', $user_detail->ID ).'</a>';
            }
        }
    } elseif( $column_name == 'pt-usercolumn-export-user' ) {
        return '<a class="button" href="users.php?s&action=export-user-data&user_id='.$id.'">Export Logs</a>';
    }
}
add_action('manage_users_custom_column', 'pt_custom_column_users', 15, 3);

//removed last updated column which was coming from wp-last-modified-plugin also it was not working returing warnings
function remove_users_columns($column_headers) {
    //unset last-updated column
    unset($column_headers['last-updated']);
    return $column_headers;
}
add_filter('manage_users_columns','remove_users_columns');

//export userdata & logs on all users
function export_individual_user_data_logs_on_all_users() {

    if(isset($_GET['action']) && $_GET['action'] === 'export-user-data') {  // Check if our custom action was selected
        $user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_STRING); //Grab the user_id

        global $wpdb;
        $views_table_name = $wpdb->prefix . 'reports_views';
        $downloads_table_name = $wpdb->prefix . 'reports_downloads';
        $activity_table_name = $wpdb->prefix . 'reports_activity';
        $search_table_name = $wpdb->prefix . 'search_results';
        $kickout_table_name = $wpdb->prefix . 'kickout_users';
        $user_logins_table_name = $wpdb->prefix . 'fa_user_logins';

        $csv_file_path = get_stylesheet_directory() . '/includes/user-module/exporteduser.csv';
        $fp = fopen($csv_file_path, 'w');

        $header_names = array(
            "First Name",
            "Last Name",
            "User Status",
            "Tools Templates Access",
            "Policies & Procedures Access",
            "Upload Documents On Frontend",
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

        $resultset = $wpdb->get_results("SELECT user_id,views_count,downloads_count,activity_count FROM `$activity_table_name` WHERE user_id = '$user_id'");

        foreach ($resultset as $result) {

            $viewsresult = $wpdb->get_results("SELECT user_id,post_id,date_time FROM $views_table_name WHERE user_id = $user_id ORDER BY date_time desc", OBJECT);
            
            foreach ($viewsresult as $viewed_documents) {
                $viewedtime = strtotime($viewed_documents->date_time);
                $viewedtitle = get_the_title($viewed_documents->post_id);

                if(get_post_status($viewed_documents->post_id) === FALSE){
                    $viewedtitle = 'Document Id: '.$viewed_documents->post_id .' (Deleted)';
                }


                $views .= $viewed_documents->post_id .' | '.$viewedtitle.' | '.date('Y-m-d', $viewedtime)."\r\n";
            }

            $downloadssquery = $wpdb->get_results("SELECT user_id,post_id,date_time FROM $downloads_table_name WHERE user_id = $user_id ORDER BY date_time desc", OBJECT);

            foreach ($downloadssquery as $downloaded_documents) {
                $downloadedtime = strtotime($downloaded_documents->date_time);
                $downloadedtitle = get_the_title($downloaded_documents->post_id);

                if(get_post_status($downloaded_documents->post_id) === FALSE){
                    $downloadedtitle = 'Document Id: '.$downloaded_documents->post_id .' (Deleted)';
                }

                $downloads .= $downloaded_documents->post_id .' | '.$downloadedtitle.' | '.date('Y-m-d', $downloadedtime)."\r\n";
            }

        }

        $user_business_id = get_the_author_meta( 'business_companies', $user_id );
        if (isset($user_business_id) && !empty($user_business_id)) {
            $business_term = get_term( $user_business_id, 'company' );
        }

        $user_corporate_id = get_the_author_meta( 'corporate_companies', $user_id );
        if (isset($user_corporate_id) && !empty($user_corporate_id)) {
            $corporate_term = get_term( $user_corporate_id, 'company' );
        }

        $user_admin_id = get_the_author_meta( 'admin_companies', $user_id );
        if (isset($user_admin_id) && !empty($user_admin_id)) {
            $admin_term = get_term( $user_admin_id, 'company' );
        }
        $user_meta = get_userdata($user_id);

        $fields = array(
            get_user_meta( $user_id, 'first_name', true ), //"First Name"
            get_user_meta( $user_id, 'last_name', true ), //"Last Name"
            ucwords(get_user_meta( $user_id, 'user_status', true )),//User Status
            ucwords(get_user_meta( $user_id, 'tools_templates_access', true )), // T&T access
            ucwords(get_user_meta( $user_id, 'policies_procedures_access', true )), // P&P access
            ucwords(get_user_meta( $user_id, 'frontend_docs_upload_access', true )), // User FED upload access
            get_user_meta( $user_id, 'work_phone', true ), //Work Phone
            $user_meta->user_email, //"Email"
            get_user_meta( $user_id, 'street_1', true ), // Street 1
            get_user_meta( $user_id, 'street_2', true ), // Street 2
            get_user_meta( $user_id, 'city', true ), // City
            get_user_meta( $user_id, 'state', true ), // State
            get_user_meta( $user_id, 'zip_code', true ), // Zipcode
            $user_meta->roles[0], // Role
            $business_term->name,
            $corporate_term->name,
            $admin_term->name,
            get_the_author_meta( 'account_manager', $user_id ),
            date('m/d/Y', get_user_meta($user_id, 'create_date', true)) ,
            // get_user_meta( $user_id, 'create_date', true ), // create_date
            get_user_meta( $user_id, 'start_date', true ), // start_date
            $result->views_count, //"Number of Documents Viewed"
            $views, //"Viewd Documents",
            $result->downloads_count, //"Number of Documents Downloaded",
            $downloads,
        );
        fputcsv($fp, $fields);

        fclose($fp);

        $activity_by_id =  get_stylesheet_directory_uri() . '/includes/user-module/exporteduser.csv';
        echo '<div id="message" class="updated fade"><p>';
        _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
        echo '<br /><a class="file-download-btn" href="' . $activity_by_id . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
        echo '</p></div>';
    }
}
add_action('load-users.php', 'export_individual_user_data_logs_on_all_users');

//delete user logs upon user delete
function delete_user_activity_logs_all($user_id) {

    global $wpdb;
    $views_table_name = $wpdb->prefix . 'reports_views';
    $downloads_table_name = $wpdb->prefix . 'reports_downloads';
    $activity_table_name = $wpdb->prefix . 'reports_activity';
    $search_table_name = $wpdb->prefix . 'search_results';
    $kickout_table_name = $wpdb->prefix . 'kickout_users';
    $user_logins_table_name = $wpdb->prefix . 'fa_user_logins';

    $view_del = $wpdb->query('DELETE FROM ' . $views_table_name .' WHERE user_id = "' . $user_id . '"');
    $download_del = $wpdb->query('DELETE FROM ' . $downloads_table_name . ' WHERE user_id = "' . $user_id . '"');
    $search_del = $wpdb->query('DELETE FROM ' . $search_table_name . ' WHERE user_id = "' . $user_id . '"');
    $kickout_del = $wpdb->query('DELETE FROM ' . $kickout_table_name . ' WHERE user_id = "' . $user_id . '"');
    $activity_table_del = $wpdb->query('DELETE FROM ' . $activity_table_name . ' WHERE user_id = "' . $user_id . '"');
    $user_logins_del = $wpdb->query('DELETE FROM ' . $user_logins_table_name . ' WHERE user_id = "' . $user_id . '"');
}
add_action( 'delete_user', 'delete_user_activity_logs_all' );

function custom_documents_category_column( $columns ){
    $columns['t-t-disabled'] = 'T&T Access';
    $columns['p-p-access'] = 'P&P Access';
    $columns['order'] = 'Order Documents'; 
    return $columns;
}
add_filter( "manage_edit-documents_category_columns", 'custom_documents_category_column', 10);

function custom_documents_category_column_content( $value, $column_name, $tax_id ){

    //for multiple custom column, you may consider using the column name to distinguish
    if ($column_name === 't-t-disabled') {
        $tools_templates_access = get_term_meta( $tax_id, 'documents_category_users_tools_templates_access', true);
		if (!$tools_templates_access) { 
			$tools_templates_access = "none";
		}
		return ucfirst($tools_templates_access);
    }
    if ($column_name === 'p-p-access') {
        
        $tools_templates_access = get_term_meta( $tax_id, 'documents_category_users_policies_procedures_access', true);
		if (!$tools_templates_access) { 
			$tools_templates_access = "none";
		}
		return ucfirst($tools_templates_access);
    }
    if ($column_name === 'order') {
        $order_documents = get_term_meta( $tax_id, 'documents_category_order_documents', true);
		if (!$order_documents) { 
			$order_documents = "none";
		}
		return ucfirst($order_documents);
    }
    // return $columns;
}
add_action( "manage_documents_category_custom_column", 'custom_documents_category_column_content', 10, 3);

/**
 * Frontend documents category order column
 */

function frontend_documents_category_order_column( $columns ){
    $columns['order'] = 'Order Documents'; 
    return $columns;
}
add_filter( "manage_edit-frontend_documents_category_columns", 'frontend_documents_category_order_column', 10);

/**
 * Frontend documents category order column content
 */

function frontend_documents_category_column_content( $value, $column_name, $tax_id ){

    if ($column_name === 'order') {
        $order_documents = get_term_meta( $tax_id, 'frontend_documents_category_order_documents', true);
		if (empty($order_documents)) { 
			$order_documents = "none";
		}
		return ucfirst($order_documents);
    }
    
}
add_action( "manage_frontend_documents_category_custom_column", 'frontend_documents_category_column_content', 10, 3);


/**
 * Adding bulk actions in document categories for P&P and T&T 
 */

function register_bulk_actions_document_category( $bulk_actions ) {
    $bulk_actions['t_t_enable'] = __( 'T&T Access: Enable', 'bridge-child');
    $bulk_actions['t_t_disable'] = __( 'T&T Access: Disable', 'bridge-child');
    $bulk_actions['p_p_enable'] = __( 'P&P Access: Enable', 'bridge-child');
    $bulk_actions['p_p_disable'] = __( 'P&P Access: Disable', 'bridge-child');
    return $bulk_actions;
}
add_filter( 'bulk_actions-edit-documents_category', 'register_bulk_actions_document_category' );

/**
 * Handling bulk actions in document categories for P&P and T&T 
 */
 
function bulk_action_handler_document_category( $redirect_to, $action_name, $post_ids ) {
    
    if ( 't_t_enable' === $action_name ) { //if selection bulk option is T&T Access: Enable
        //  for all documents, update term meta
        foreach ( $post_ids as $post_id ) {
            //update taxonomy status meta
            update_term_meta( $post_id, 'documents_category_users_tools_templates_access', 'none');
        }
        //redirect 
        $redirect_to = add_query_arg( array(
            'set_document_t_t_status' => 'enable',
            'updated_document_count' => count( $post_ids ),
        ), $redirect_to );
    }
    elseif( 't_t_disable' === $action_name ) {
        
        foreach ( $post_ids as $post_id ) {
            update_term_meta( $post_id, 'documents_category_users_tools_templates_access', 'disable');
        }
        
        $redirect_to = add_query_arg( array(
            'set_document_t_t_status' => 'disable',
            'updated_document_count' => count( $post_ids ),
        ), $redirect_to );
    }
    elseif( 'p_p_enable' === $action_name ) {
        foreach ( $post_ids as $post_id ) {
            update_term_meta( $post_id, 'documents_category_users_policies_procedures_access', 'none');
        }
        
        $redirect_to = add_query_arg( array(
            'set_document_p_p_status' => 'enable',
            'updated_document_count' => count( $post_ids ),
        ), $redirect_to );
    }
    elseif( 'p_p_disable' === $action_name ) {
        foreach ( $post_ids as $post_id ) {
            update_term_meta( $post_id, 'documents_category_users_policies_procedures_access', 'disable');
        }
        
        $redirect_to = add_query_arg( array(
            'set_document_p_p_status' => 'disable',
            'updated_document_count' => count( $post_ids ),
        ), $redirect_to );
    }
    return $redirect_to;
}
add_filter( 'handle_bulk_actions-edit-documents_category', 'bulk_action_handler_document_category', 10, 3 );

//Searching Meta Data in User Search Backend
if(is_admin()) {
	add_action ( 'pre_user_query', 'tcs_user_search' );
	function tcs_user_search($wp_user_query) {
	    if(false === strpos($wp_user_query->query_where, '@') && !empty($_GET["s"])) {
	        global $wpdb;
	        $uids = array();
	        $iusib_add = '';
	        // the escaped query string
	        $qstr = esc_sql($_GET["s"]);
	        $usermeta_affected_ids = $wpdb->get_results("
	            SELECT DISTINCT user_id
	            FROM $wpdb->usermeta
	            WHERE (meta_key='first_name' OR meta_key='last_name' OR meta_key='company' OR meta_key='city'".$iusib_add.")
	            AND LOWER(meta_value) LIKE '%".$qstr."%'"
	        );
	        foreach($usermeta_affected_ids as $maf) {
	            array_push($uids,$maf->user_id);
	        }

	        $users_affected_ids = $wpdb->get_results("
	            SELECT DISTINCT ID FROM $wpdb->users
	            WHERE LOWER(user_nicename)
	            LIKE '%".$qstr."%'
	            OR LOWER(user_email)
	            LIKE '%".$qstr."%'"
	        );

	        foreach($users_affected_ids as $maf) {
	            if(!in_array($maf->ID,$uids)) {
	                array_push($uids,$maf->ID);
	            }
	        }

	        $id_string = implode(",",$uids);
	        $wp_user_query->query_where = preg_replace('/user_nicename\sLIKE\s\'{[0-9A-Za-z]+}'.$qstr.'{[0-9A-Za-z]+}\'\s/', "ID IN(".$id_string.") ",$wp_user_query->query_where);
	        // $wp_user_query->query_where = str_replace("user_nicename LIKE '%".$qstr."%'", "ID IN(".$id_string.")", $wp_user_query->query_where);
	    }
	    return $wp_user_query;
	}
}

function view_document_log() {
	if ( is_singular('documents') ) {
	    global $wpdb;
	    $get_id = get_the_ID();

		$view_id = absint($get_id);
		$ipaddress = reports_get_ip_address();
		$date_time = current_time('mysql');
	    $current_user = wp_get_current_user();
	    $visitor_id = $current_user->ID;
		$allowed_roles = array('business', 'subscriber_-_admin_facility', 'subscriber_-_corporate' , 'subscriber_-_facility_user');
		if( array_intersect($allowed_roles, $current_user->roles ) ) {
			// We need to log this view.
		    $table = $wpdb->prefix . 'reports_views';
		    $activity_table = $wpdb->prefix . 'reports_activity';
		    $data = array(
				'post_id' => $view_id,
				'visitor_ip' => $ipaddress,
				'date_time' => $date_time,
				'user_id' => $visitor_id
		    );

		    $data = array_filter($data); //Remove any null values.
		    $insert_table = $wpdb->insert($table, $data);
		    $query = "SELECT * FROM $activity_table WHERE user_id='$visitor_id'";
	     	$data_results = $wpdb->get_results($query);
	     	if(count($data_results) !== 0) {
		 		$wpdb->query("UPDATE `$activity_table` SET `views_count`=`views_count`+1 WHERE `user_id`='$visitor_id'");
		 		$wpdb->query("UPDATE `$activity_table` SET `activity_count`=`activity_count`+1 WHERE `user_id`='$visitor_id'");
		 	} else {
		 		$activity_data = array(
					'user_id' => $visitor_id,
					'views_count' => '1',
					'downloads_count' => '0',
					'activity_count' => '1'
				);
				$activity_data = array_filter($activity_data); //Remove any null values.
		    	$insert_activity_table = $wpdb->insert($activity_table, $activity_data);
			}
		}
	}
}
add_action( 'template_redirect', 'view_document_log' );

/*Custom Filters*/
function custom_filter_for_users($which) {
    global $wpdb;
    $users_table = $wpdb->prefix .'users';
    $user_meta_table = $wpdb->prefix .'usermeta';
    $capabilities = $wpdb->prefix .'capabilities';

    /*
        Filter users by company
    */

    //Get selected company
    if ( isset( $_GET[ 'company_top' ]) ) {
        $company_section = $_GET[ 'company_top' ];
    } elseif ( isset( $_GET[ 'company_bottom' ]) ) {
        $company_section = $_GET[ 'company_bottom' ];
    } else {
        $company_section = '';
    }
    
    //Get selected company value
    $selected = selected( $company_section,$value,false);

    //display companies dropdown
    	$company_options = wp_dropdown_categories(array(
        'show_option_all' => __("Company..."),
        'taxonomy'        => 'company',
        'name'            => 'company_'.$which,
        'orderby'         => 'name',
        'selected'        => $company_section,
        'value_field' => 'id',
        'hierarchical' => 1,
        'hide_empty'      => 0,
        'class'              => 'backend-dropdown',
    ));

    //display filter button for companies dropdown
    submit_button(__( 'Filter' ), null, $which, false);

    /*
        Filter users by Account Manager
    */

    //Get selected Account Manager
    if ( isset( $_GET[ 'acc_manager_top' ]) ) {
        $acc_manager_section = $_GET[ 'acc_manager_top' ];
    } elseif ( isset( $_GET[ 'acc_manager_bottom' ]) ) {
        $acc_manager_section = $_GET[ 'acc_manager_bottom' ];
    } else {
        $acc_manager_section = '';
    }

    //get Account Manager dropdown html
    $acc_manager_select_html = '<select name="acc_manager_%s" style="float:none;margin-left:10px;">
    <option value="">%s</option><option value="not-assigned"'. selected( $acc_manager_section,'not-assigned', false) .'>Not Assigned any</option>%s</select>';

    //Get Account Managers query
    $acc_query = "SELECT u.ID, u.user_email
        FROM $users_table u, $user_meta_table m
        WHERE u.ID = m.user_id
        AND m.meta_key LIKE '$capabilities'
        AND m.meta_value LIKE '%account_manager%'";
    $acc_managers = $wpdb->get_results($acc_query);

    //Get Account Managers Name and display as dropdown options
    foreach ( $acc_managers as $acc_manager ) {
        $acc_manager_selected = selected( $acc_manager_section,$acc_manager->user_email, false);
        $first_name = get_the_author_meta( 'first_name', $acc_manager->ID );
        $options .=  '<option value="'. $acc_manager->user_email .'"'. $acc_manager_selected .'>';
        if (isset($first_name) && !empty($first_name)) {
            $options .= $first_name . ' ' . get_the_author_meta( 'last_name', $acc_manager->ID );
        } else {
            $options .= $acc_manager->user_email;
        }
        $options .= '</option>';
    }

    //display account managers dropdown
    $acc_manager_select = sprintf( $acc_manager_select_html, $which, __( 'Account Manager' ), $options );
    // output <select>
    echo $acc_manager_select;

    //display filter button for account manager dropdown
    submit_button(__( 'Filter' ), null, $which, false);

    /*
        Filter users by User status
    */

    //Get selected User status via GET
    if ( isset( $_GET[ 'status_top' ]) ) {
        $status_section = $_GET[ 'status_top' ];
    } elseif ( isset( $_GET[ 'status_bottom' ]) ) {
        $status_section = $_GET[ 'status_bottom' ];
    } else {
        $status_section = '';
    }
    // User status select html
    $status_section_html = '<select name="status_%s" style="float:none;margin-left:10px;">
    <option value="">%s</option>%s</select>';

    // Get selected status
    $enable_status_selected = $disable_status_selected = '';
    if ($status_section == 'enable') {
        $enable_status_selected = ' selected="selected"';
    }
    else if ($status_section == 'disable') {
        $disable_status_selected = ' selected="selected"';
    }

    // User status options
    $user_status_options = '<option value="enable"' . $enable_status_selected . '>Enable</option>
    <option value="disable"' . $disable_status_selected . '>Disable</option>';

    //display user status dropdown
    $status_select = sprintf( $status_section_html, $which, __( 'Status...' ), $user_status_options );

    // output <select>
    echo $status_select;

    //display filter button for user status dropdown
    submit_button(__( 'Filter' ), null, $which, false);

    /*
        Filter users by Tools & Templates
    */

    //Get selected Tools & Templates status
    if ( isset( $_GET[ 'tools_templates_status_top' ]) ) {
        $tools_templates_status_section = $_GET[ 'tools_templates_status_top' ];
    } elseif ( isset( $_GET[ 'tools_templates_status_bottom' ]) ) {
        $tools_templates_status_section = $_GET[ 'tools_templates_status_bottom' ];
    } else {
        $tools_templates_status_section = '';
    }

    // Tools & Templates select html
    $tools_templates_html = '<select name="tools_templates_status_%s" style="float:none;margin-left:10px;max-width: 145px;">
    <option value="">%s</option>%s</select>';

    // Get selected tools & templates status
    $tools_templates_enable_selected = $tools_templates_disable_selected = '';
    if ($tools_templates_status_section == 'enable') {
        $tools_templates_enable_selected = ' selected="selected"';
    }
    else if ($tools_templates_status_section == 'disable') {
        $tools_templates_disable_selected = ' selected="selected"';
    }

    // Tools & Templates options
    $tools_templates_options = '<option value="enable"' . $tools_templates_enable_selected . '>Enable</option>
    <option value="disable"' . $tools_templates_disable_selected . '>Disable</option>';

    //display tools & templates dropdown
    $tools_templates_select = sprintf( $tools_templates_html, $which, __( 'T&T Access' ), $tools_templates_options );

    // output <select>
    echo $tools_templates_select;

    //display filter button for tools & templates dropdown
    submit_button(__( 'Filter' ), null, $which, false);

    /*
        Filter users by Policies & Procedures
    */
    
    if ( isset( $_GET[ 'policies_procedures_status_top' ]) ) {
        $policies_procedures_status_section = $_GET[ 'policies_procedures_status_top' ];
    } elseif ( isset( $_GET[ 'policies_procedures_status_bottom' ]) ) {
        $policies_procedures_status_section = $_GET[ 'policies_procedures_status_bottom' ];
    } else {
        $policies_procedures_status_section = '';
    }

    // Policies & Procedures select html
    $policies_procedures_html = '<select name="policies_procedures_status_%s" style="float:none;margin-left:10px;max-width: 145px;">
    <option value="">%s</option>%s</select>';

    // Get selected Policies & Procedures status
    $policies_procedures_enable_selected = $policies_procedures_disable_selected = '';
    if ($policies_procedures_status_section == 'enable') {
        $policies_procedures_enable_selected = ' selected="selected"';
    }
    else if ($policies_procedures_status_section == 'disable') {
        $policies_procedures_disable_selected = ' selected="selected"';
    }

    // Policies & Procedures options
    $policies_procedures_options = '<option value="enable"' . $policies_procedures_enable_selected . '>Enable</option>
    <option value="disable"' . $policies_procedures_disable_selected . '>Disable</option>';

    //display tools & templates dropdown
    $policies_procedures_select = sprintf( $policies_procedures_html, $which, __( 'P&P Access' ), $policies_procedures_options );

    // output <select>
    echo $policies_procedures_select;

    //display filter button for policies & procedures dropdown
    submit_button(__( 'Filter' ), null, $which, false);
    
    /*
        Filter users by Company Status
    */

    //Get selected company status
    if ( isset( $_GET[ 'company_status_top' ]) ) {
        $company_status_section = $_GET[ 'company_status_top' ];
    } elseif ( isset( $_GET[ 'company_status_bottom' ]) ) {
        $company_status_section = $_GET[ 'company_status_bottom' ];
    } else {
        $company_status_section = '';
    }

    // Company status select html
    $company_status_html = '<select name="company_status_%s" style="float:none;margin-left:10px;max-width: 145px;">
    <option value="">%s</option>%s</select>';

    // Get selected Company status
    $company_status_enable_selected = $company_status_disable_selected = '';
    if ($company_status_section == 'none') {
        $company_status_enable_selected = ' selected="selected"';
    }
    else if ($company_status_section == 'disable') {
        $company_status_disable_selected = ' selected="selected"';
    }

    // Company status options
    $company_status_options = '<option value="none"' . $company_status_enable_selected . '>Enable</option>
    <option value="disable"' . $company_status_disable_selected . '>Disable</option>';

    //display Company status dropdown
    $company_status_select = sprintf( $company_status_html, $which, __( 'Company Status' ), $company_status_options );

    // output <select>
    echo $company_status_select;
    //display filter button for Company status dropdown
    submit_button(__( 'Filter' ), null, $which, false);

    /*
        Filter users by Trusted Refferer
    */

    //Get selected Trusted Refferer option
    if ( isset( $_GET[ 'trusted_refer_top' ]) ) {
        $trusted_refer_section = $_GET[ 'trusted_refer_top' ];
    } else {
        $trusted_refer_section = '';
    }

    // Trusted Refferer select html
    $trusted_refer_html = '<select name="trusted_refer_%s" style="float:none;margin-left:10px;max-width: 125px;">
    <option value="">%s</option>%s</select>';

    // Get selected Trusted Refferer
    $trusted_refer_enable_selected  = '';
    if ($trusted_refer_section == 'yes') {
        $trusted_refer_enable_selected = ' selected="selected"';
    }

    // Trusted Refferer options
    $trusted_refer_options = '<option value="yes"' . $trusted_refer_enable_selected . '>Yes</option>';

    //display Trusted Refferer dropdown
    $trusted_refer_select = sprintf( $trusted_refer_html, $which, __( 'Trusted Refferer' ), $trusted_refer_options );

    // output <select>
    echo $trusted_refer_select;
    //display filter button for Trusted Refferer dropdown
    submit_button(__( 'Filter' ), null, $which, false);
}
add_action('restrict_manage_users', 'custom_filter_for_users');


function filter_users_by_company_section($query) {
    global $pagenow;
    if (is_admin() && 'users.php' == $pagenow) {
        if ( isset( $_GET[ 'company_top' ]) || isset( $_GET[ 'company_bottom' ])) {
            $top = $_GET['company_top'];
            $bottom = $_GET['company_bottom'];
            if (!empty($top) OR !empty($bottom)) {
                $section = !empty($top) ? $top : $bottom;

                // change the meta query based on which option was chosen
                $meta_query = array (
                    'relation' => 'OR',
                    array (
                        'key' => 'business_companies',
                        'value' => $section,
                        'compare' => 'LIKE'
                    ),
                    array (
                        'key' => 'corporate_companies',
                        'value' => $section,
                        'compare' => 'LIKE'
                    ),
                    array (
                        'key' => 'admin_companies',
                        'value' => $section,
                        'compare' => 'LIKE'
                    ),
                );
                $query->set('meta_query', $meta_query);
            }
        }
    }
}
add_filter('pre_get_users', 'filter_users_by_company_section');

/*Filters users by Account Manager*/
/*function filter_users_by_acc_manager($which) {

    if ( isset( $_GET[ 'acc_manager_top' ]) ) {
        $section = $_GET[ 'acc_manager_top' ];
    } elseif ( isset( $_GET[ 'acc_manager_bottom' ]) ) {
        $section = $_GET[ 'acc_manager_bottom' ];
    } else {
        $section = '';
    }

    // template for filtering
    $st = '<select name="acc_manager_%s" style="float:none;margin-left:10px;">
    <option value="">%s</option><option value="not-assigned"'. selected( $section,'not-assigned', false) .'>Not Assigned any</option>%s</select>';

    global $wpdb;
    $users_table = $wpdb->prefix .'users';
    $user_meta_table = $wpdb->prefix .'usermeta';
    $capabilities = $wpdb->prefix .'capabilities';

    $acc_query = "SELECT u.ID, u.user_email
        FROM $users_table u, $user_meta_table m
        WHERE u.ID = m.user_id
        AND m.meta_key LIKE '$capabilities'
        AND m.meta_value LIKE '%account_manager%'";

    $acc_managers = $wpdb->get_results($acc_query);

    foreach ( $acc_managers as $acc_manager ) {

        $selected = selected( $section,$acc_manager->user_email, false);
        $first_name = get_the_author_meta( 'first_name', $acc_manager->ID );
        $options .=  '<option value="'. $acc_manager->user_email .'"'. $selected .'>';
        if (isset($first_name) && !empty($first_name)) {
            $options .= $first_name . ' ' . get_the_author_meta( 'last_name', $acc_manager->ID );
        } else {
            $options .= $acc_manager->user_email;
        }
        $options .= '</option>';
    }

    // combine template and options
    $select = sprintf( $st, $which, __( 'Account Manager..' ), $options );

    // output <select> and submit button
    echo $select;

    submit_button(__( 'Filter' ), null, $which, false);
}
add_action('restrict_manage_users', 'filter_users_by_acc_manager'); */


function filter_users_by_acc_manager_section($query) {
    global $pagenow;
    if (is_admin() && 'users.php' == $pagenow) {
        if ( isset( $_GET[ 'acc_manager_top' ]) || isset( $_GET[ 'acc_manager_bottom' ])) {
            $top = $_GET['acc_manager_top'];
            $bottom = $_GET['acc_manager_bottom'];
            if (!empty($top) OR !empty($bottom)) {
                $section = !empty($top) ? $top : $bottom;
                if ($section == 'not-assigned') {
                    $meta_query = array (
                        array (
                            'key' => 'account_manager',
                            'value' => '',
                            'compare' => '='
                        ),
                    );
                } else {
                    $meta_query = array (
                        array (
                            'key' => 'account_manager',
                            'value' => $section,
                            'compare' => 'LIKE'
                        ),
                    );
                }
                $query->set('meta_query', $meta_query);
            }
        }
    }
}
add_filter('pre_get_users', 'filter_users_by_acc_manager_section');

/*** Filter Users by User Status ***/
/*function filter_users_by_user_status($which) {

	if ( isset( $_GET[ 'status_top' ]) ) {
		$section = $_GET[ 'status_top' ];
	} elseif ( isset( $_GET[ 'status_bottom' ]) ) {
		$section = $_GET[ 'status_bottom' ];
	} else {
		$section = '';
	}
	// template for filtering
	$st = '<select name="status_%s" style="float:none;margin-left:10px;">
	<option value="">%s</option>%s</select>';

	// generate options
	$enableselected = $disableselected = '';
	if ($section == 'enable') {
		$enableselected = ' selected="selected"';
	}
	else if ($section == 'disable') {
		$disableselected = ' selected="selected"';
	}

	$options = '<option value="enable"' . $enableselected . '>Enable</option>
	<option value="disable"' . $disableselected . '>Disable</option>';

	// combine template and options
	$select = sprintf( $st, $which, __( 'Status...' ), $options );

	// output <select> and submit button
	echo $select;
	submit_button(__( 'Filter' ), null, $which, false);
}
add_action('restrict_manage_users', 'filter_users_by_user_status');*/


function filter_users_by_user_status_section($query) {
	global $pagenow;
	if (is_admin() && 'users.php' == $pagenow) {
		if ( isset( $_GET[ 'status_top' ]) || isset( $_GET[ 'status_bottom' ])) {
			// figure out which button was clicked. The $which in filter_users_by_user_status()
			$top = $_GET['status_top'];
			$bottom = $_GET['status_bottom'];
			if (!empty($top) OR !empty($bottom)) {
				$section = !empty($top) ? $top : $bottom;

				// change the meta query based on which option was chosen
				$meta_query = array (array (
				'key' => 'user_status',
				'value' => $section,
				'compare' => 'LIKE'
				));
				$query->set('meta_query', $meta_query);
			}
		}
	}
}
add_filter('pre_get_users', 'filter_users_by_user_status_section');

/*Filters users by access to tools and templates*/
/*function filter_users_by_tools_templates_status($which) {

	if ( isset( $_GET[ 'tools_templates_status_top' ]) ) {
		$section = $_GET[ 'tools_templates_status_top' ];
	} elseif ( isset( $_GET[ 'tools_templates_status_bottom' ]) ) {
		$section = $_GET[ 'tools_templates_status_bottom' ];
	} else {
		$section = '';
	}
	// template for filtering
	$st = '<select name="tools_templates_status_%s" style="float:none;margin-left:10px;max-width: 145px;">
	<option value="">%s</option>%s</select>';

	// generate options
	$enableselected = $disableselected = '';
	if ($section == 'enable') {
		$enableselected = ' selected="selected"';
	}
	else if ($section == 'disable') {
		$disableselected = ' selected="selected"';
	}
	$options = '<option value="enable"' . $enableselected . '>Enable</option>
	<option value="disable"' . $disableselected . '>Disable</option>';

	// combine template and options
	$select = sprintf( $st, $which, __( 'Tools & Templates' ), $options );

	// output <select> and submit button
	echo $select;
	submit_button(__( 'Filter' ), null, $which, false);
}
add_action('restrict_manage_users', 'filter_users_by_tools_templates_status');*/


function filter_users_by_tools_templates_section($query) {
	global $pagenow;
	if (is_admin() && 'users.php' == $pagenow) {
		if ( isset( $_GET[ 'tools_templates_status_top' ]) || isset( $_GET[ 'tools_templates_status_bottom' ])) {
			$top = $_GET['tools_templates_status_top'];
			$bottom = $_GET['tools_templates_status_bottom'];
			if (!empty($top) OR !empty($bottom)) {
				$section = !empty($top) ? $top : $bottom;

				// change the meta query based on which option was chosen
				$meta_query = array (array (
				'key' => 'tools_templates_access',
				'value' => $section,
				'compare' => 'LIKE'
				));
				$query->set('meta_query', $meta_query);
			}
		}
	}
}
add_filter('pre_get_users', 'filter_users_by_tools_templates_section');

/**
 * Filter and funciton for P & P
 */
function filter_users_by_p_p_section( $query ){
    global $pagenow;
    if (is_admin() && 'users.php' == $pagenow) {
        if ( isset( $_GET[ 'policies_procedures_status_top' ]) || isset( $_GET[ 'policies_procedures_status_bottom' ])) {
            $top = $_GET['policies_procedures_status_top'];
            $bottom = $_GET['policies_procedures_status_bottom'];
            if (!empty($top) OR !empty($bottom)) {
                $section = !empty($top) ? $top : $bottom;

                // change the meta query based on which option was chosen
                $meta_query = array (array (
                'key' => 'policies_procedures_access',
                'value' => $section,
                'compare' => 'LIKE'
                ));
                $query->set('meta_query', $meta_query);
            }
        }
    }
}
add_filter('pre_get_users', 'filter_users_by_p_p_section');

/*Filters users by company status*/
/*function filter_users_by_company_status($which) {

    if ( isset( $_GET[ 'company_status_top' ]) ) {
        $section = $_GET[ 'company_status_top' ];
    } elseif ( isset( $_GET[ 'company_status_bottom' ]) ) {
        $section = $_GET[ 'company_status_bottom' ];
    } else {
        $section = '';
    }
    // template for filtering
    $st = '<select name="company_status_%s" style="float:none;margin-left:10px;max-width: 145px;">
    <option value="">%s</option>%s</select>';

    // generate options
    $enableselected = $disableselected = '';
    if ($section == 'none') {
        $enableselected = ' selected="selected"';
    }
    else if ($section == 'disable') {
        $disableselected = ' selected="selected"';
    }
    $options = '<option value="none"' . $enableselected . '>Enable</option>
    <option value="disable"' . $disableselected . '>Disable</option>';

    // combine template and options
    $select = sprintf( $st, $which, __( 'Company Status' ), $options );

    // output <select> and submit button
    echo $select;
    submit_button(__( 'Filter' ), null, $which, false);
}
add_action('restrict_manage_users', 'filter_users_by_company_status');*/


function filter_users_by_company_status_section($query) {
    global $pagenow;
    if (is_admin() && 'users.php' == $pagenow) {
        if ( isset( $_GET[ 'company_status_top' ]) || isset( $_GET[ 'company_status_bottom' ])) {
            $top = $_GET['company_status_top'];
            $bottom = $_GET['company_status_bottom'];
            if (!empty($top) OR !empty($bottom)) {
                $section = !empty($top) ? $top : $bottom;

                $get_all_companies = get_terms([
                    'taxonomy' => 'company',
                    'meta_key' => 'users_comapny_company_status',
                    'meta_value' => $section,
                    'hide_empty' => false,
                    'fields' => 'ids',
                ]);

                // change the meta query based on which option was chosen
                $meta_query = array (
                    'relation' => 'OR',
                    array (
                        'key' => 'business_companies',
                        'value' => $get_all_companies,
                        'compare' => 'IN'
                    ),
                    array (
                        'key' => 'corporate_companies',
                        'value' => $get_all_companies,
                        'compare' => 'IN'
                    ),
                    array (
                        'key' => 'admin_companies',
                        'value' => $get_all_companies,
                        'compare' => 'IN'
                    ),
                );
                $query->set('meta_query', $meta_query);
            }
        }
    }
}
add_filter('pre_get_users', 'filter_users_by_company_status_section');

/*** Filter Users by Trusted Refferer  ***/
/*function filter_users_by_trusted_refer($which) {

	if ( isset( $_GET[ 'trusted_refer_top' ]) ) {
		$section = $_GET[ 'trusted_refer_top' ];
	} else {
		$section = '';
	}
	// template for filtering
	$st = '<select name="trusted_refer_%s" style="float:none;margin-left:10px;max-width: 125px;">
	<option value="">%s</option>%s</select>';

	// generate options
	$enableselected  = '';
	if ($section == 'yes') {
		$enableselected = ' selected="selected"';
	}

	$options = '<option value="yes"' . $enableselected . '>Yes</option>';

	// combine template and options
	$select = sprintf( $st, $which, __( 'Trusted Refferer' ), $options );

	// output <select> and submit button
	echo $select;
	submit_button(__( 'Filter' ), null, $which, false);
}
add_action('restrict_manage_users', 'filter_users_by_trusted_refer');*/

function filter_users_by_trusted_refer_section($query) {
	global $pagenow;
	if (is_admin() && 'users.php' == $pagenow) {
		if ( isset($_GET[ 'trusted_refer_top' ]) && !empty($_GET[ 'trusted_refer_top' ]) ) {
			$top = $_GET['trusted_refer_top'];
			if ($top == 'yes') { 
				// change the meta query based on which option was chosen
				$meta_query = array (array (
					'key' => 'trusted_refer',
					'value' => '',
					'compare' => '!=',
				));
			}
			$query->set('meta_query', $meta_query);
		}
	}
}
add_filter('pre_get_users', 'filter_users_by_trusted_refer_section');

function add_download_file_link( $meta_id, $post_id, $meta_key, $meta_value) {
    $post_type = get_post_type($post_id);

    // If this isn't a 'documents' post, don't update it.
    if ( "documents" != $post_type ) return;
    // - Update the post's metadata.
    $document = get_post_meta( $post_id, 'bridge_document_document_file', true );
    $download = get_post_meta( $post_id, 'bridge_document_document_download', true );
    $sdm_upload = get_post_meta( $post_id, 'sdm_upload', true );

    if (!empty($download)) {
        update_post_meta( $post_id, 'sdm_upload', $download);
    }elseif (!empty($document)) {
        update_post_meta( $post_id, 'sdm_upload', $document);
    } else {
        delete_post_meta( $post_id, 'sdm_upload', $sdm_upload  );
    }
}

add_action( 'added_post_meta', 'add_download_file_link', 10, 4 );
add_action( 'updated_post_meta', 'add_download_file_link', 10, 4 );

function check_first_login_redirect($user_login, $user) {
	$logincontrol = get_user_meta($user->ID, 'login_amount', 'TRUE');
	$site_url = site_url();
	$password_reset_page = $site_url.'/password-reset/';
	if ( $logincontrol ) {
		//set the user to old
		update_user_meta( $user->ID, 'login_amount', '0' );
		//Do the redirects or whatever you need to do for the first login
		wp_redirect( $password_reset_page, 302 );
		exit;
	} else {
		wp_redirect(home_url());
		exit();
	}
}
add_action('wp_login', 'check_first_login_redirect', 10, 2);

/*
 Customized Role assign dropdown
 Removed following roles administrator, account_manager, sales_admin and backend-admin for backend-admin, account_manager and sales_admin to set
 */
function remove_roles_for_backend_admin_to_edit($editable_roles) {
    global $pagenow;
    $user = wp_get_current_user();

    if ( 'user-edit.php' == $pagenow || 'user-new.php' == $pagenow || 'users.php' == $pagenow ) { //if current screen is all users, add user or edit user

	//if current logged in user is backend admin,account manager or sales admin
        if (
            in_array('backend-admin', (array) $user->roles) ||
            in_array('account_manager', (array) $user->roles) ||
            in_array('sales_admin', (array) $user->roles)
        ) {
            unset($editable_roles['administrator']);
            unset($editable_roles['account_manager']);
            unset($editable_roles['sales_admin']);
            unset($editable_roles['backend-admin']);
        }
    }

    return $editable_roles;
}

add_filter('editable_roles', 'remove_roles_for_backend_admin_to_edit');

function new_user_required_fields() {
    if (in_array($GLOBALS['pagenow'], array('user-new.php', 'user-edit.php'))) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery("input#pass1").attr("autocomplete","off");
                jQuery("input#first_name").closest("tr.form-field").addClass("form-required");
                jQuery("input#first_name").closest("tr.form-field").find("label").append(" <span class='description'>(required)</span>");
                jQuery("input#first_name").closest("tr.user-first-name-wrap").addClass("form-required");
                jQuery("input#first_name").closest("tr.user-first-name-wrap").find("label").append(" <span class='description'>(required)</span>");
                jQuery("input#last_name").closest("tr.form-field").addClass("form-required");
                jQuery("input#last_name").closest("tr.form-field").find("label").append(" <span class='description'>(required)</span>");
                jQuery("input#last_name").closest("tr.user-last-name-wrap").addClass("form-required");
                jQuery("input#last_name").closest("tr.user-last-name-wrap").find("label").append(" <span class='description'>(required)</span>");

                jQuery("input#street_1").closest("tr.form-field").addClass("form-required");
                jQuery("input#city").closest("tr.form-field").addClass("form-required");
                jQuery("select#state").closest("tr.form-field").addClass("form-required");
                jQuery("input#zip_code").closest("tr.form-field").addClass("form-required");
                jQuery("input#title").closest("tr.form-field").addClass("form-required");

                jQuery("input#url").closest("tr.form-field").remove();
                jQuery("input#url").closest("tr.user-url-wrap").remove();
                jQuery("input#send_user_notification").closest("tr").remove();
                jQuery('select#role option').filter(function() {
                    return !this.value || jQuery.trim(this.value).length == 0 || jQuery.trim(this.text).length == 0;
                })
                .remove();
            });
        </script>
        <?php

    }
}

add_action('admin_footer', 'new_user_required_fields');


function hook_javascript_gAnalytics() {
    ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-145924430-2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-145924430-2');
    </script>
    <?php
}

add_action('wp_head', 'hook_javascript_gAnalytics');

add_filter('pre_insert_term', function($term, $tax)
{
    if ( ('company' === $tax) && isset($_POST['users_comapny_state']) && empty($_POST['users_comapny_state']) ) {
        return new \WP_Error('empty_term_name', __('State cannot be empty!', 'text-domain'));
    } else {
        return $term;
    }
}, -1, 2);

/*Custom Bulk Actions on all users*/
function register_custom_bulk_action_for_users($bulk_actions) {
    //get all account managers
    global $wpdb;
    $users_table = $wpdb->prefix .'users';
    $user_meta_table = $wpdb->prefix .'usermeta';
    $capabilities = $wpdb->prefix .'capabilities';

    $acc_query = "SELECT u.ID, u.user_email
        FROM $users_table u, $user_meta_table m
        WHERE u.ID = m.user_id
        AND m.meta_key LIKE '$capabilities'
        AND m.meta_value LIKE '%account_manager%'";

    $acc_managers = $wpdb->get_results($acc_query);
    foreach ( $acc_managers as $acc_manager ) {
        $first_name = get_the_author_meta( 'first_name', $acc_manager->ID );
        if (isset($first_name) && !empty($first_name)) {
            $first_name = $first_name;
        } else {
            $first_name .= $acc_manager->user_email;
        }
        //bulk options to assign account manager
        $bulk_actions['assign_acc_manager_'.$acc_manager->user_email] = 'Assign Account Manager: '.$first_name;
    }
    //bulk options to set user status enable/disable
    $bulk_actions['enable_user_status'] = 'Set User Status: Enable';
    $bulk_actions['disable_user_status'] = 'Set User Status: Disable';
    
    //bulk options to set t&t status enable/disable
    $bulk_actions['enable_t_t_status'] = 'Set T&T Access: Enable';
    $bulk_actions['disable_t_t_status'] = 'Set T&T Access: Disable';
    
    //bulk options to set p&p status enable/disable
    $bulk_actions['enable_p_p_status'] = 'Set P&P Access: Enable';
    $bulk_actions['disable_p_p_status'] = 'Set P&P Access: Disable';
    
    
    return $bulk_actions;
}
add_filter( 'bulk_actions-users', 'register_custom_bulk_action_for_users' );

 
function custom_bulk_action_for_users_handler( $redirect_to, $action_name, $user_ids ) {
	
	if ( 'enable_user_status' === $action_name ) { //if selection bulk option is Set User Status: Enable
        //for all selected users
        foreach ( $user_ids as $user_id ) {
                //update user status meta
                update_user_meta( $user_id, 'user_status', 'enable' );
            }
            //redirect 
            $redirect_to = add_query_arg( array(
                            'set_user_status' => 'enable',
                            'updated_users_count' => count( $user_ids ),
                        ), $redirect_to );
    } elseif ( 'disable_user_status' === $action_name ) {
       foreach ( $user_ids as $user_id ) {
                update_user_meta( $user_id, 'user_status', 'disable' );
            }
            $redirect_to = add_query_arg( array(
                            'set_user_status' => 'disable',
                            'updated_users_count' => count( $user_ids ),
                        ), $redirect_to );
    } 
    elseif ( 'enable_t_t_status' === $action_name ) {
        foreach ( $user_ids as $user_id ) {
            update_user_meta( $user_id, 'tools_templates_access', 'enable' );
        }
        
        $redirect_to = add_query_arg( array(
                        'set_t_t_status' => 'enable',
                        'updated_users_count' => count( $user_ids ),
                    ), $redirect_to );
    }
    elseif ( 'disable_t_t_status' === $action_name ) {
        foreach ( $user_ids as $user_id ) {
            update_user_meta( $user_id, 'tools_templates_access', 'disable' );
        }
        $redirect_to = add_query_arg( array(
                        'set_t_t_status' => 'disable',
                        'updated_users_count' => count( $user_ids ),
                    ), $redirect_to );
    } 
    elseif ( 'enable_p_p_status' === $action_name ) {
        foreach ( $user_ids as $user_id ) {
            update_user_meta( $user_id, 'policies_procedures_access', 'enable' );
        }
        $redirect_to = add_query_arg( array(
                        'set_p_p_status' => 'enable',
                        'updated_users_count' => count( $user_ids ),
                    ), $redirect_to );
    }
    elseif ( 'disable_p_p_status' === $action_name ) {
        foreach ( $user_ids as $user_id ) {
            update_user_meta( $user_id, 'policies_procedures_access', 'disable' );
        }
        $redirect_to = add_query_arg( array(
                        'set_p_p_status' => 'disable',
                        'updated_users_count' => count( $user_ids ),
                    ), $redirect_to );
    }
    else {
        global $wpdb;
        $users_table = $wpdb->prefix .'users';
        $user_meta_table = $wpdb->prefix .'usermeta';
        $capabilities = $wpdb->prefix .'capabilities';

	    $acc_query = "SELECT u.ID, u.user_email
	        FROM $users_table u, $user_meta_table m
	        WHERE u.ID = m.user_id
	        AND m.meta_key LIKE '$capabilities'
	        AND m.meta_value LIKE '%account_manager%'";

	    $acc_managers = $wpdb->get_results($acc_query);

	    foreach ( $acc_managers as $acc_manager ) {

	        if ( 'assign_acc_manager_'.$acc_manager->user_email === $action_name ) { 
	            foreach ( $user_ids as $user_id ) {
	                update_user_meta( $user_id, 'account_manager', $acc_manager->user_email );
	            }
	            $redirect_to = add_query_arg( array(
	                            'assign_acc_manager' => $acc_manager->user_email,
	                            'updated_users_count' => count( $user_ids ),
	                        ), $redirect_to );
		}
            }
    }

    return $redirect_to; 
}
add_filter( 'handle_bulk_actions-users', 'custom_bulk_action_for_users_handler', 10, 3 );


function pt_bulk_action_admin_notices() {

    global $pagenow;
    if (is_admin() && 'users.php' == $pagenow) {
        if ( ! empty( $_REQUEST['assign_acc_manager'] ) ) {
            echo '<div class="updated settings-error notice is-dismissible"><p><b>'. $_REQUEST['assign_acc_manager'] .'</b> assigned as Account Manager to <b>'.intval( $_REQUEST['updated_users_count'] ).' Users</b>.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        if ( ! empty( $_REQUEST['set_user_status'] ) ) {
            echo '<div class="updated settings-error notice is-dismissible">
                <p>User Status set as <b>'. $_REQUEST['set_user_status'] .'</b> for <b>'.intval( $_REQUEST['updated_users_count'] ).' Users</b>.</p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        if( ! empty( $_REQUEST['set_t_t_status'] ) ) {
            echo '<div class="updated settings-error notice is-dismissible">
                <p>T&T Status set as <b>'. $_REQUEST['set_t_t_status'] .'</b> for <b>'.intval( $_REQUEST['updated_users_count'] ).' Users</b>.</p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        if( ! empty( $_REQUEST['set_p_p_status'] ) ) {
            echo '<div class="updated settings-error notice is-dismissible">
                <p>P&P Status set as <b>'. $_REQUEST['set_p_p_status'] .'</b> for <b>'.intval( $_REQUEST['updated_users_count'] ).' Users</b>.</p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
	if ( ! empty( $_REQUEST['delete_count'] ) ) {
            echo '<div class="updated settings-error notice is-dismissible">
                <p><strong>Document Views Logs Deleted!</strong></p>
                <p><strong>Document Downloads Logs Deleted!</strong></p>
                <p><strong>Searched Logs Deleted!</strong></p>
                <p><strong>Kickout Logs Deleted!</strong></p>
                <p><strong>Login History Deleted!</strong></p>
                <p><strong>Login History Deleted!</strong></p>
                <p><em>Click to Dismiss</em></div>';
        }
    }

}
add_action( 'admin_notices', 'pt_bulk_action_admin_notices' );

/**
 * Restrict to show files only by user
 * Show files to user who belongs to same company
 */

add_filter( 'mla_media_modal_query_final_terms', 'show_current_user_attachments', 1, 99 );

// function show_current_user_attachments( $query ) {

//     $userIds = [];
//     $user_id = get_current_user_id();
//     $user_company = get_user_meta($user_id, 'corporate_companies', true);

//     $args = array(
//         'meta_query' => array(
//             array(
//                 'key' => 'corporate_companies',
//                 'value' => $user_company,
//                 'compare' => 'LIKE'
//             )
//         ),
//     );

//     $usersData = get_users($args);

//     foreach ($usersData as $user) {

//         $userIds[] = $user->ID;

//     }

//     if ( $user_id && !current_user_can( 'manage_options' ) ) {

//         $query['author__in'] = $userIds;

//     }

//     return $query;
    
// }


// function show_current_user_attachments( $query ) {

//     $userIds = [];
//     $user_id = get_current_user_id();
//     $user_company = get_user_meta($user_id, 'corporate_companies', true);

//     $args = array(
//         'meta_query' => array(
//             array(
//                 'key' => 'corporate_companies',
//                 'value' => $user_company,
//                 'compare' => 'LIKE'
//             )
//         ),
//     );

//     $usersData = get_users($args);

//     foreach ($usersData as $user) {

//         $userIds[] = $user->ID;

//     }

//     if ( $user_id && !current_user_can( 'manage_options' ) ) {

//         $query['author__in'] = $userIds;

//     }

//     return $query;
    
// }

function show_current_user_attachments( $query ) {

    $userIds = [];
    $company_assigned_id = '';
    $user_id = get_current_user_id();

    $admin_companies = get_user_meta($user_id, 'admin_companies', true );
    $corporate_companies = get_user_meta($user_id, 'corporate_companies', true );

    if(!empty($admin_companies)) {
        $company_assigned_id = $admin_companies;
    }

    if(!empty($corporate_companies)) {
        $company_assigned_id = $corporate_companies;
    }

    $args = array(
        'meta_query' => array(
            array(
                'key' => 'company_assigned',
                'value' => $company_assigned_id,
                'compare' => 'LIKE'
            )
        ),
    );

    $usersData = get_users($args);

    foreach ($usersData as $user) {

        $userIds[] = $user->ID;

    }

    if ( $user_id && !current_user_can( 'manage_options' ) ) {
        $userIds[] = $user_id;
        $query['author__in'] = $userIds;

    }

    if(!empty($userIds)) {

        $query['meta_query'] = array(
            array(
                'key'    =>  '_frontend_document',
                'value'  =>    'frontend_document',
                'compare'    =>     'LIKE',
            )
        );

    }

    return $query;
    
}

/**
 * Hide menu item from menu bar based on company meta value
 */

function hide_menu_item_from_menubar( $menu_objects, $args ) {

    $company_assigned_id = '';
    $company_frntend_doc_access = '';

    $corporate_companies = get_user_meta(get_current_user_id(), 'corporate_companies', true );
    $admin_companies = get_user_meta(get_current_user_id(), 'admin_companies', true );

    if(!empty($admin_companies)) {
        $company_assigned_id = $admin_companies;
    }

    if(!empty($corporate_companies)) {
        $company_assigned_id = $corporate_companies;
    }

    if (isset($company_assigned_id) && !empty($company_assigned_id)) {
        $company_frntend_doc_access = get_term_meta( $company_assigned_id, 'users_comapny_company_frontend_documents_access', true);
    }

    $theme_location = 'top-navigation';
    $target_menu_title = 'Document Café';
    $class_to_add = 'hide-menu-item';

    if ($args->theme_location == $theme_location && $company_frntend_doc_access == 'disable') {
        foreach ($menu_objects as $key => $menu_object) {
            if ($menu_object->title == $target_menu_title) {
                $menu_object->classes[] = $class_to_add;
                break;
            }
        }
    }

    return $menu_objects;

}

add_filter( 'wp_nav_menu_objects', 'hide_menu_item_from_menubar', 10, 2 );

/**
 * Update the users list to add all users
 */

function add_all_users_to_users_dropdown( $authorsHTML ) {

    global $post;
    $current_post_type = get_post_type();

    if( $current_post_type == 'frontend_documents' ) {

        $authorsHTML = '';
        $selectedAuthor = $post->post_author;

        $users = get_users( array( 
            'fields' => array( 'ID', 'display_name', 'user_email' ),
            'meta_query'=> array(
                array(
                    'key' => 'frontend_docs_upload_access',
                    'value' => 'enable',
                    'compare' => '==',
                ),
            )
        ) );

        $authorsHTML .= '<select class="media-authors-dropdown" name="post_author">';
        
            foreach ( $users as $user ) {

                $authorsHTML .= '<option value="' . $user->ID . '" ' . ( $selectedAuthor == $user->ID ? 'selected="selected"' : '' ) . '>' . esc_html( $user->user_email ) . ' (' . esc_html( $user->display_name ) . ')</option>';
                
            }

        $authorsHTML .= '</select>';

        return $authorsHTML;

    }else{
        return $authorsHTML;
    }

}

add_filter( 'wp_dropdown_users', 'add_all_users_to_users_dropdown', 10, 2 );

/**
 * Filter to show for documents to filter
 * cpt: Custom Post Type (documents, frontend_documents)
 */

function filter_media_by_cpt()
{
    $scr = get_current_screen();

    if ( $scr->base !== 'upload' ) return;

    if ( isset( $_REQUEST[ 'documents_media_filter' ]) ) {
        $selectedCpt = $_REQUEST[ 'documents_media_filter' ];
    } else {
        $selectedCpt = '';
    }

    echo '<select name="documents_media_filter" class="postform">';
        echo '<option value="">All Documents</option>';
        echo '<option value="tcs-documents"' . ( $selectedCpt == 'tcs-documents' ? 'selected="selected"' : '' ) . '>TCS Documents</option>';
        echo '<option value="frontend-documents"' . ( $selectedCpt == 'frontend-documents' ? 'selected="selected"' : '' ) . '>Frontend Documents</option>';
    echo '</select>';

    // authors filter

    $users = get_users( array( 
        'fields' => array( 'ID', 'display_name' )
    ) );

    $queryArgs = array(
        'post_type' =>    'attachment',
        'post_status'   =>  'inherit'
    );

    if ( isset( $_REQUEST[ 'author_dropdown' ]) ) {
        $selectedAuthor = $_REQUEST[ 'author_dropdown' ];
    } else {
        $selectedAuthor = '';
    }
    
    $authors_select_html = '<select class="documents-media-filter" name="author_dropdown"><option value="">%s</option>%s</select>';

    foreach ( $users as $user ) {

        $queryArgs['author'] = $user->ID;

        $allPosts = new WP_Query($queryArgs);

        if($allPosts->have_posts()) {
            
            $options .= '<option value="' . $user->ID . '"  ' . ( $selectedAuthor == $user->ID ? 'selected="selected"' : '' ) . '>' . esc_html( $user->display_name ) . '</option>';
        }
    }

    $authors_select = sprintf( $authors_select_html, __( 'Select author...' ), $options );

    echo $authors_select;

}
add_action('restrict_manage_posts', 'filter_media_by_cpt');

/**
 * Modify query to handle filter for media
 */

function filter_media_by_cpt_handler($query) {

    // Bail if this is not the admin area.
    if ( ! is_admin() ) {
        return;
    }

    // Bail if this is not the main query.
    if ( ! $query->is_main_query() ) {
        return;
    }

    // Only proceed if this the attachment upload screen.
    $screen = get_current_screen();
    if ( ! $screen || 'upload' !== $screen->id || 'attachment' !== $screen->post_type ) {
        return;
    }

    if( $_REQUEST['documents_media_filter'] == 'frontend-documents' ) {
        
        $meta_query = array(
            array(
               'key'    =>  '_frontend_document',
               'value'  =>    'frontend_document',
               'compare'    =>     'LIKE',
            ),
        );
    
        $query->set( 'meta_query', $meta_query );

    } else if ( $_REQUEST['documents_media_filter'] == 'tcs-documents' ) {

        $meta_query = array(
            array(
               'key'    =>  '_frontend_document',
               'compare'    =>     'NOT EXISTS',
            ),
        );
    
        $query->set( 'meta_query', $meta_query );

    }

    if ( isset( $_REQUEST[ 'author_dropdown' ] ) ) {

        $query->set( 'author', $_REQUEST[ 'author_dropdown' ] );

    }

}

add_action('pre_get_posts','filter_media_by_cpt_handler');

/**
 * Register bulk action for media library
 */

add_filter( 'bulk_actions-upload', 'register_my_bulk_actions' );
 
function register_my_bulk_actions($bulk_actions) {

    $bulk_actions['edit'] = __( 'Edit', 'bidge-child');
    return $bulk_actions;

}

/**
 * Get all authors who published the documents (media) files
 */

function get_all_users() {

    $authorsHTML = '';

    $users = get_users( array( 
        'fields' => array( 'ID', 'display_name', 'user_email' ),
        'meta_query'=> array(
            array(
                'key' => 'frontend_docs_upload_access',
                'value' => 'enable',
                'compare' => '==',
            ),
        )
    ) );

    $authorsHTML .= '<select class="media-authors-dropdown" name="media_author">';
            
        foreach ( $users as $user ) {
                
            $authorsHTML .= '<option value="' . $user->ID . '">' . esc_html( $user->user_email ) . ' (' . esc_html( $user->display_name ) . ')</option>';
            
        }

    $authorsHTML .= '</select>';

    echo $authorsHTML;

    wp_die();

}

add_action('wp_ajax_get_all_users', 'get_all_users');

/**
 * Update attachments for media library
 */

function bulk_update_media_attachments() {

    $documentIDS = $_POST['ids'];
    $authorID = $_POST['authorID'];

    if(!empty($documentIDS)) {

        foreach ($documentIDS as $id) {
            
            $attachment = array(
                'ID' => $id,
                'post_author' => $authorID
            );
    
            wp_update_post( $attachment );

        }

        echo json_encode(array(
            'status'    =>  true
        ));

    } else {

        echo json_encode(array(
            'status'    =>  false,
            'message'   =>  'Something wrong.'
        )); 

    }

    wp_die();

}

add_action('wp_ajax_bulk_update_media_attachments', 'bulk_update_media_attachments');

/**
 * Exclude FED documents from link args
 */

add_filter( 'wp_link_query_args', 'remove_fed_post_type_from_wp_link_query_args' );
 
function remove_fed_post_type_from_wp_link_query_args( $query ) {
    // this is the post type I want to exclude
    $cpt_to_remove = 'frontend_documents';
 
    // find the corresponding array key
    $key = array_search( $cpt_to_remove, $query['post_type'] ); 
 
    // remove the array item
    if( $key )
        unset( $query['post_type'][$key] );
 
    return $query;
}

/**
 * Rewrite fed documents slug
 */

function change_fed_taxonomy_slug_args( $taxonomy, $object_type, $args ){
    if( 'frontend_documents_category' == $taxonomy ){
        remove_action( current_action(), __FUNCTION__ );
        $args['rewrite'] = array( 'slug' => 'fed-category' );
        register_taxonomy( $taxonomy, $object_type, $args );
    }
}

add_action( 'registered_taxonomy', 'change_fed_taxonomy_slug_args', 10, 3 );


/* User Login Hostroy with ajax download */

add_action("wp_ajax_pt_export_all_user_login_history", 'pt_export_all_user_login_history');
add_action("wp_ajax_nopriv_pt_export_all_user_login_history", 'pt_export_all_user_login_history');
add_action("wp_ajax_pt_query_call_for_all_user_login_history", 'pt_query_call_for_all_user_login_history');
add_action("wp_ajax_nopriv_pt_query_call_for_all_user_login_history", 'pt_query_call_for_all_user_login_history');


function pt_query_call_for_all_user_login_history()
{ 

    $chunk_index = $_POST['data']['chunk'];
    //echo $chunk_index;
    $date_from = $_POST['data']['date_from'];
    $date_to = $_POST['data']['date_to'];
    $user_type = $_POST['data']['user_type'];
    $user_id = $_POST['data']['user_id'];
    $user_name = $_POST['data']['user_name'];
    $user_role = $_POST['data']['user_role'];
    $login_status = $_POST['data']['login_status'];
    $user_status = $_POST['data']['user_status'];
    $t_t_status = $_POST['data']['t_t_status'];
    $p_p_status = $_POST['data']['p_p_status'];
    $comp_id = $_POST['data']['comp_id'];
    
    $exp_offset = $chunk_index * 1000;
    

    global $wpdb;

    $where_query = "SELECT FaUserLogin.*, UserMeta.meta_value,";
    
    if (!empty($user_status)) {
        $where_query .= " UserMetaStatus.meta_value,";
    }
    
    if (!empty($t_t_status)) {
        $where_query .= " UserMetaStatus.meta_value,";
    }
    
    if (!empty($p_p_status)) {
        $where_query .= " UserMetaStatus.meta_value,";
    }

    if (!empty($comp_id)) {
        $where_query .= " UserMetaCompany.meta_value,";
    }

    $where_query .=" TIMESTAMPDIFF(SECOND,FaUserLogin.time_login,FaUserLogin.time_last_seen) as duration FROM wp_fa_user_logins  AS FaUserLogin INNER JOIN wp_usermeta AS UserMeta ON ( UserMeta.user_id=FaUserLogin.user_id AND UserMeta.meta_key LIKE  'wp_capabilities' )";
                     

    if (!empty($user_role)) {
        
        $where_query .= " AND `UserMeta`.`meta_value` LIKE '%" . esc_sql($user_role) . "%'";
    }

    if (!empty($user_status)) {
        
            $where_query .= " INNER JOIN $wpdb->usermeta AS UserMetaStatus ON ( UserMetaStatus.user_id=FaUserLogin.user_id AND UserMetaStatus.meta_key = 'user_status' ) AND `UserMetaStatus`.`meta_value` = '" . esc_sql($user_status) . "'";
    }
    
    if (!empty($t_t_status)) {
            
            $where_query .= " INNER JOIN $wpdb->usermeta AS UserMetaStatus ON ( UserMetaStatus.user_id=FaUserLogin.user_id AND UserMetaStatus.meta_key = 'tools_templates_access' ) AND `UserMetaStatus`.`meta_value` = '" . esc_sql($t_t_status) . "'";
    }
    
    if (!empty($p_p_status)) {
        
            $where_query .= " INNER JOIN $wpdb->usermeta AS UserMetaStatus ON ( UserMetaStatus.user_id=FaUserLogin.user_id AND UserMetaStatus.meta_key = 'policies_procedures_access' ) AND `UserMetaStatus`.`meta_value` = '" . esc_sql($p_p_status) . "'";
    }


    
    
    if (!empty($login_status)) {

        if ("unknown" == $login_status) {
            $where_query .= " AND `FaUserLogin`.`login_status` = '' ";
        } else {
            $where_query .= " AND `FaUserLogin`.`login_status` = '" . esc_sql($login_status) . "'";
        }
    }

    if (!empty($comp_id)) {

        $where_query .= " INNER JOIN $wpdb->usermeta AS UserMetaCompany ON ( UserMetaCompany.user_id=FaUserLogin.user_id AND ( UserMetaCompany.meta_key = 'business_companies' OR UserMetaCompany.meta_key = 'corporate_companies' OR UserMetaCompany.meta_key = 'admin_companies' ) ) AND `UserMetaCompany`.`meta_value` = '" . esc_sql($comp_id) . "'";
    }
    
    if (!empty($user_name)) {
        $where_query .= " AND `FaUserLogin`.`username` = '" . esc_sql($user_name) . "'";
    }

    if (!empty($user_id)) {
        $where_query .= " AND `FaUserLogin`.`user_id` = '" . esc_sql($user_id) . "'";
    }

    
    if (!empty($user_type)) {

        $date_type = $user_type;
        if (in_array($date_type, array('all', 'login', 'logout', 'last_seen'))) {

            if (!empty($date_from) && !empty($date_to)) {
                $date_type = esc_sql($date_type);

                if ($date_type == 'all') {
                    $where_query .= " AND `FaUserLogin`.`time_login` >= '" . esc_sql($date_from) . " 00:00:00'";
                    $where_query .= " AND `FaUserLogin`.`time_login` <= '" . esc_sql($date_to) . " 23:59:59'";
                }
                else {
                    $where_query .= " AND `FaUserLogin`.`time_$date_type` >= '" . esc_sql($date_from) . "00:00:00'";
                    $where_query .= " AND `FaUserLogin`.`time_$date_type` <= '" . esc_sql($date_to) . " 23:59:59'";

                }
            } else {
                unset($date_from);
                unset($date_to);
            }
        }
    }
                    
    $where_query .="WHERE 1  ORDER BY id DESC LIMIT 1000 OFFSET ".$exp_offset."";
    $results = $wpdb->get_results($where_query, 'ARRAY_A');
    
    foreach ($results as $result)
    {
        $user_id = $result['user_id'];
        $user_email = $result['username'];
        $user_role = get_userdata($result['user_id'])->roles[0];
        $user_role = ucwords(str_replace('_', ' ', $user_role));
        $user_fname = get_userdata($result['user_id'])->user_firstname;
        $user_lname = get_userdata($result['user_id'])->user_lastname;
        $user_status = ucwords(get_user_meta( $result['user_id'], 'user_status', true));
        $t_t_access = ucwords(get_user_meta( $result['user_id'], 'tools_templates_access', true));
        $p_p_access = ucwords(get_user_meta( $result['user_id'], 'policies_procedures_access', true));
        $business_companies_id = get_user_meta( $result['user_id'], 'business_companies', true);
        $business_companies = get_term( $business_companies_id )->name;
        $corporate_companies_id = get_user_meta( $result['user_id'], 'corporate_companies', true);
        $corporate_companies = get_term( $corporate_companies_id )->name;
        $admin_facility_id = get_user_meta( $result['user_id'], 'admin_companies', true);
        $admin_facility = get_term( $admin_facility_id )->name;
        $account_manager = get_user_meta( $result['user_id'], 'account_manager', true);
        $ip_add = $result['ip_address'];
        $web_browser = $result['browser'].' ('.$result['browser_version'].')';
        $os = $result['operating_system'];
        // $time = round($result['duration']);
        // $duration = sprintf('%02d:%02d:%02d', ($time/3600),($time/60%60), $time%60);
        $duration = $result['duration']; // values 0 and higher are supported!
        $duration_result = ltrim( sprintf( '%02d hour %02d min %02d seconds', floor( $duration / 3600 ), floor( ( $duration / 60 ) % 60 ), ( $duration % 60 ) ), '0 hours mins' );
        if( $duration_result == ' seconds' ) { $duration_result = '0 seconds'; }
            // $duration = gmdate("H:i:s", );
        $time_last_seen = $result['time_last_seen'];
        $time_login = $result['time_login'];
        if($result['time_logout'] == ""){
            $time_logout = '----';
        }else{
            $time_logout = $result['time_logout'];
        }
        if($result['login_status'] == "login"){

            $login_status = 'Logged In';
            
        }elseif($result['login_status'] == "logout"){
            $login_status = 'Logged Out';
        }
        
        
        $fields = array(
            $user_email,
            $user_fname,
            $user_lname,
            $user_status,
            $t_t_access,
            $p_p_access,
            $user_role,
            $business_companies,
            $corporate_companies,
            $admin_facility,
            $account_manager,
            $ip_add,
            $web_browser,
            $os,
            $duration_result,
            $time_last_seen,
            $time_login,
            $time_logout,
            $login_status,
        );
        $csv_file_path = REPORTS_PATH . "all-users-login-history.csv";
        $fap = fopen($csv_file_path, 'a');
        fputcsv($fap, $fields);
        fclose($fap);
    }


    $nocache = rand(100,999999);
    $file_url = REPORTS_URL . "/all-users-login-history.csv?nocache=".$nocache."";
    // $file_url = REPORTS_URL . '/all-users-login-history.csv';
    $html = "<div class='faulh-ajax-message'>
    <p class='faulh-action'><a href='$file_url' id='faulh-communication-dos' ></a> <b>All users exported successfully.<b></p>
</div>

<script type='text/javascript'>
    setTimeout(function() {
        document.getElementById('faulh-communication-dos').click();
    }, 1000); 
    
    setTimeout(function() {
        document.getElementById('faulh-download-progress').remove();
        document.getElementById('faulh-records').remove();
    }, 1000); 
</script>";
    echo $html;

    $t_chunk = $_POST['data']['t_chunk'];
    //echo $t_chunk;
    if($chunk_index + 1 >= $t_chunk  ){

        $userEmail = $_POST['data']['userEmail'];
        if(isset($userEmail) && !empty($userEmail)){
            $to = $userEmail;
            $subject = 'TCS Download File is Ready';
            $body = '<p>Your file is ready to download</p><a href="'. get_site_url().'/wp-content/plugins/reports/all-users-login-history.csv?nocache='.$nocache.'">Click here to Download CSV</a>';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail( $to, $subject, $body, $headers );
        }
    }
    exit;
}

function pt_export_all_user_login_history()
{

    $date_from = $_POST['data']['date_from'];
    $date_to = $_POST['data']['date_to'];
    $user_type = $_POST['data']['user_type'];
    $user_id = $_POST['data']['user_id'];
    $user_name = $_POST['data']['user_name'];
    $user_role = $_POST['data']['user_role'];
    $login_status = $_POST['data']['login_status'];
    $user_status = $_POST['data']['user_status'];
    $t_t_status = $_POST['data']['t_t_status'];
    $p_p_status = $_POST['data']['p_p_status'];
    $comp_id = $_POST['data']['comp_id'];

    $header_names = array(
        "Email",
        "First Name",
        "Last Name",
        "User Status",
        "T&T Access",
        "P&P Access",
        "Role",
        "Business Member",
        "Corporate Facility",
        "Admin Facility",
        "Account Manager",
        "IP Address",
        "Web Browser",
        "Operating System",
        "Duration",
        "Last Seen",
        "Login",
        "Logout",
        "Login Status",
    );
    
    global $wpdb;
    
    $where_query = "SELECT COUNT(*) AS 'total_count',";
    
    if (!empty($user_status)) {
        $where_query .= " UserMetaStatus.meta_value,";
    }
    
    if (!empty($t_t_status)) {
        $where_query .= " UserMetaStatus.meta_value,";
    }
    
    if (!empty($p_p_status)) {
        $where_query .= " UserMetaStatus.meta_value,";
    }

    if (!empty($comp_id)) {
        $where_query .= " UserMetaCompany.meta_value,";
    }

    $where_query .=" TIMESTAMPDIFF(SECOND,FaUserLogin.time_login,FaUserLogin.time_last_seen) as duration FROM wp_fa_user_logins  AS FaUserLogin INNER JOIN wp_usermeta AS UserMeta ON ( UserMeta.user_id=FaUserLogin.user_id AND UserMeta.meta_key LIKE  'wp_capabilities' )";
                     

    if (!empty($user_role)) {
        
        $where_query .= " AND `UserMeta`.`meta_value` LIKE '%" . esc_sql($user_role) . "%'";
    }

    if (!empty($user_status)) {
        
            $where_query .= " INNER JOIN $wpdb->usermeta AS UserMetaStatus ON ( UserMetaStatus.user_id=FaUserLogin.user_id AND UserMetaStatus.meta_key = 'user_status' ) AND `UserMetaStatus`.`meta_value` = '" . esc_sql($user_status) . "'";
    }
    
    if (!empty($t_t_status)) {
            
            $where_query .= " INNER JOIN $wpdb->usermeta AS UserMetaStatus ON ( UserMetaStatus.user_id=FaUserLogin.user_id AND UserMetaStatus.meta_key = 'tools_templates_access' ) AND `UserMetaStatus`.`meta_value` = '" . esc_sql($t_t_status) . "'";
    }
    
    if (!empty($p_p_status)) {
        
            $where_query .= " INNER JOIN $wpdb->usermeta AS UserMetaStatus ON ( UserMetaStatus.user_id=FaUserLogin.user_id AND UserMetaStatus.meta_key = 'policies_procedures_access' ) AND `UserMetaStatus`.`meta_value` = '" . esc_sql($p_p_status) . "'";
    }
    
    if (!empty($login_status)) {

        if ("unknown" == $login_status) {
            $where_query .= " AND `FaUserLogin`.`login_status` = '' ";
        } else {
            $where_query .= " AND `FaUserLogin`.`login_status` = '" . esc_sql($login_status) . "'";
        }
    }

    if (!empty($comp_id)) {

        $where_query .= " INNER JOIN $wpdb->usermeta AS UserMetaCompany ON ( UserMetaCompany.user_id=FaUserLogin.user_id AND ( UserMetaCompany.meta_key = 'business_companies' OR UserMetaCompany.meta_key = 'corporate_companies' OR UserMetaCompany.meta_key = 'admin_companies' ) ) AND `UserMetaCompany`.`meta_value` = '" . esc_sql($comp_id) . "'";
    }


    if (!empty($user_name)) {
        $where_query .= " AND `FaUserLogin`.`username` = '" . esc_sql($user_name) . "'";
    }

    if (!empty($user_id)) {
        $where_query .= " AND `FaUserLogin`.`user_id` = '" . esc_sql($user_id) . "'";
    }

    if (!empty($user_type)) {
        $date_type = $user_type;
        if (in_array($date_type, array('all', 'login', 'logout', 'last_seen'))) {

            if (!empty($date_from) && !empty($date_to)) {
                $date_type = esc_sql($date_type);
                
                if ($date_type == 'all') {
                    $where_query .= " AND `FaUserLogin`.`time_login` >= '" . esc_sql($date_from) . " 00:00:00'";
                    $where_query .= " AND `FaUserLogin`.`time_login` <= '" . esc_sql($date_to) . " 23:59:59'";
                }
                else {
                    $where_query .= " AND `FaUserLogin`.`time_$date_type` >= '" . esc_sql($date_from) . " 00:00:00'";
                    $where_query .= " AND `FaUserLogin`.`time_$date_type` <= '" . esc_sql($date_to) . " 23:59:59'";

                }
            } else {
                unset($date_from);
                unset($date_to);
            }
        }
    }

    $total_users = $wpdb->get_results($where_query)[0]->total_count;    

    // $total_users = 10000;
    if ($total_users % 1000 == 0)
    {
        $total_chunks = $total_users / 1000;
    }
    elseif($total_users >= 1000)
    {
        $total_chunks = ($total_users / 1000) + 1;
    }
    else{
        $total_chunks = 1;
    }            
    // $total_chunks = 10;
   echo $total_chunks;
    
    $csv_file_path = REPORTS_PATH . "all-users-login-history.csv";
    $fp = fopen($csv_file_path, 'w');
    fputcsv($fp, $header_names);
    // for ( $i = 0; $i < $total_chunks; $i++ )
    exit;         
}


add_action('admin_head', 'my_custom_admin_style');

function my_custom_admin_style() {
  echo '<style>
  #faulh-export-all input#uemail {
    margin-top: 9px;
    }
.uemail-label{
    margin-top:5px;
  </style>';
}

function up_new_user( $user_id ) {

    $registered_date = date( 'Y-m-d' );
    $r_date = strtotime($registered_date);
    update_user_meta($user_id, 'create_date', $r_date);

}

 add_action( 'user_register', 'up_new_user', 20, 1 );

/* File Upload Restriction */
function restrict_file_upload( $file ) {
    // Check if user is logged in and not an administrator
    if ( is_user_logged_in() && ! current_user_can( 'administrator' ) ) {
        $allowed_types = array(
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/pdf',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );

        $file_info = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );

        if ( ! in_array( $file_info['type'], $allowed_types ) ) {
            $file['error'] = 'Sorry, only document files are allowed.';
        }
    }

    return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'restrict_file_upload' );


/**
 * Add menu item visibility control based on custom classes
 */
add_filter('wp_nav_menu_objects', 'advanced_menu_visibility_control', 10, 2);

function advanced_menu_visibility_control($items, $args) {
    $hidden_items = [];
    
    // Build complete tree of all menu items
    $menu_tree = [];
    foreach ($items as $item) {
        $menu_tree[$item->ID] = [
            'object' => $item,
            'children' => []
        ];
    }
    foreach ($items as $item) {
        if ($item->menu_item_parent && isset($menu_tree[$item->menu_item_parent])) {
            $menu_tree[$item->menu_item_parent]['children'][] = $item->ID;
        }
    }

    // Check conditions and mark hidden items
    foreach ($menu_tree as $item_id => $item_data) {
        $item = $item_data['object'];
        $should_hide = false;
        
        // Condition 1: Logged in
        if (in_array('tcs-cond-not-logged-in', $item->classes) && !is_user_logged_in()) {
            $should_hide = true;
        }elseif (in_array('tcs-cond-logged-in', $item->classes) && is_user_logged_in()) {
            $should_hide = true;
        }
        // Condition 2: Active user
        elseif (in_array('tcs-cond-log-in-active-user', $item->classes)) {
            if (!is_user_logged_in() || get_user_meta(get_current_user_id(), 'user_status', true) == 'disable') {
                $should_hide = true;
            }
        }
        // Condition 3: Full access
        elseif (in_array('tcs-cond-us-tt-access', $item->classes)) {
            if (!is_user_logged_in() ||
                get_user_meta(get_current_user_id(), 'user_status', true) == 'disable' || 
                get_user_meta(get_current_user_id(), 'tools_templates_access', true) == 'disable') {
                $should_hide = true;
            }
        }
                // Condition 3: Full access
        elseif (in_array('tcs-cond-us-pp-access', $item->classes)) {
            if (!is_user_logged_in() ||
                get_user_meta(get_current_user_id(), 'user_status', true) == 'disable' || 
                get_user_meta(get_current_user_id(), 'policies_procedures_access', true) == 'disable') {
                $should_hide = true;
            }
        }

        if ($should_hide) {
            $hidden_items[$item_id] = true;
            // Recursively mark all descendants
            $stack = [$item_id];
            while (!empty($stack)) {
                $current = array_pop($stack);
                foreach ($menu_tree[$current]['children'] as $child_id) {
                    $hidden_items[$child_id] = true;
                    array_push($stack, $child_id);
                }
            }
        }
    }

    // Filter out hidden items
    $visible_items = [];
    foreach ($items as $item) {
        if (!isset($hidden_items[$item->ID])) {
            $visible_items[] = $item;
        }
    }

    return $visible_items;
}