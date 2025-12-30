<?php

get_header();

global $wpdb;
$term = get_queried_object();
$company_assigned_id = '';
$company_frntend_doc_access = '';

$tools_templates_access = get_user_meta($current_user->ID, 'tools_templates_access', true );
$policies_procedures_access = get_user_meta($current_user->ID, 'policies_procedures_access', true );

$account_manager = get_user_meta($current_user->ID, 'account_manager', true );

// companies

$corporate_companies = get_user_meta($current_user->ID, 'corporate_companies', true );
$admin_companies = get_user_meta($current_user->ID, 'admin_companies', true );

if(!empty($admin_companies)) {
    $company_assigned_id = $admin_companies;
}

if(!empty($corporate_companies)) {
    $company_assigned_id = $corporate_companies;
}

if (isset($company_assigned_id) && !empty($company_assigned_id)) {
    $company_frntend_doc_access = get_term_meta( $company_assigned_id, 'users_comapny_company_frontend_documents_access', true);
}

$tools_templates_parents = get_ancestors( get_queried_object_id(), 'frontend_documents_category' );
$tools_templates_childs = get_term_children( get_queried_object_id(), 'frontend_documents_category' );

$term_chlds_ids_array  = $parent_tools_templates_access_array = $parent_policies_procedures_access_array = array();

$get_category = get_category($term);

?>
<div class="content content_top_margin"> 
    <div class="container">
        <div style="display: none;">
            <?php echo do_shortcode('[pdf-embedder url=""]'); ?>
        </div>
        <?php if(isset($qode_options_proya['overlapping_content']) && $qode_options_proya['overlapping_content'] == 'yes') { ?>
            <div class="overlapping_content"><div class="overlapping_content_inner">
        <?php } ?>
            <div class="container_inner default_template_holder clearfix">
                <div class="container_inner default_template_holder clearfix page_container_inner taxonomy-header" tax-slug="<?php echo $term->taxonomy; ?>" cpt-name="frontend_documents">
                    <?php
                        /* if company disabled the access */

                        
                        if( $company_frntend_doc_access == 'disable' ) {
                            echo '<h3 style="text-align: center;">This feature has been disbaled by your administrator.</h3>';
                            echo '<h4 style="text-align: center;">Please contact your Account Manager <a style="color: #f28800;" href="mailto:'.$account_manager.'">'.$account_manager.'</a><br>OR call us at <a style="color: #f28800;" href="tel:877.582.7347">877.582.7347</a></h4>';
                        }

                        else if ( $tools_templates_childs && ! is_wp_error( $tools_templates_childs ) ) {

                            foreach ( $tools_templates_childs as $child ) {

                                if ($tools_templates_access == 'disable') {
                                    $term_chlds_ids_array[] = $child;
                                }
                                
                                if ($policies_procedures_access == 'disable') {
                                    $term_chlds_ids_array[] = $child;
                                }

                            }
                    
                            get_template_part( 'title' );
                            ?>
                            <div class="wpb_wrapper">
                                <div class="entry-content">
                                    <div class="vc_column_container vc_col-sm-3">
                                        <div class="panel panel-default">
                                            <div class="panel-body">
                                                <div class="slide-container">
                                                    <div class="list-group" id="mg-multisidetabs">
                                                        <a href="#" class="list-group-item parent-item"><span><?php echo $term->name; ?></span><i class="fa fa-arrow-down pull-right"></i></a>
                                                        <div class="panel list-sub mg-show" style="display: block;">
                                                            <div class="panel-body">
                                                                <div class="list-group">
                                                                    <?php
                                                                    $catargs = array(
                                                                        'taxonomy'     => $term->taxonomy,
                                                                        'child_of' => $term->term_id,
                                                                        'hide_empty'   => false,
                                                                        'title_li'=> false,
                                                                        'exclude'=> $term_chlds_ids_array,           
                                                                        'walker' => new Walker_Categories_Template()
                                                                    );
                                                                    wp_list_categories( $catargs );
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div><!-- ./ end list-group -->
                                                </div><!-- ./ end slide-container -->
                                            </div><!-- ./ end panel-body -->
                                        </div><!-- ./ end panel panel-default-->
                                    </div>
                                    <div class="vc_column_container vc_col-sm-9">
                                        <div id="ajax-loader">
                                            <img id="loading-image" src="<?php echo get_stylesheet_directory_uri(); ?>/includes/assets/images/ajax-loader.gif" style="display:none;"/>
                                        </div>
                                        <div id="ajax-posts" class="ajax-docs">

                                            <h2><?php echo do_shortcode('[flexy_breadcrumb]'); ?></h2>
                                            
                                            <?php
                                                the_archive_description( '<div class="taxonomy-description">', '</div>' );
                                                if ($get_category->category_parent != '0') { ?>
                                                    <div class="row" style="margin:0; padding-bottom: 40px;">
                                                        <div class="col-12 tcs-sort-container">
                                                            <span class="custom-search">
                                                                <a href="#" class="tcs-search-sort tcs-sort-cat" data-order="<?php echo get_query_var('order'); ?>" data-id="<?php echo $term->term_id; ?>">
                                                                    <i class="fa fa-sort fa-2x" aria-hidden="true"></i>
                                                                </a>
                                                            </span>

                                                            <label class="tcs-search-sort">
                                                                Sort By:
                                                                <select name="orderby" id="orderby" class="search-field">
                                                                    <option value="date" <?php echo get_query_var('orderby') == 'date' ? 'selected' : ''; ?>>Date Posted</option>
                                                                    <option value="title" <?php echo get_query_var('orderby') == 'title' ? 'selected' : ''; ?>>Document Title</option>
                                                                </select>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <div class="panel-group" id="dococument-parent" role="tablist" aria-multiselectable="true">
                                                <?php
                                                if ($get_category->category_parent == '0') {}
                                                else {

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
                                                    
                                                    $args = array(
                                                        'post_type'   => 'frontend_documents',
                                                        'post_status'   => 'publish',
                                                        'posts_per_page' =>  25,
                                                        'paged' => $paged,
                                                        'tax_query' => array(
                                                            array(
                                                                'taxonomy' => 'frontend_documents_category',
                                                                'field'    => 'id',
                                                                'terms'    => get_queried_object_id(),
                                                                'include_children' => false
                                                            ),
                                                        ),
                                                    );

                                                    if(!current_user_can( 'manage_options' )) {
                                                        $args['meta_query'] = array(
                                                            array(
                                                                    'key'     => 'frontend_document_author_company',
                                                                    'value'   => array( $company_assigned_id ),
                                                                    'compare' => 'IN',
                                                            )
                                                        );
                                                    }

                                                    $documentsQuery = new WP_Query( $args );

                                                    if($documentsQuery->have_posts()) : 
                                                        while ( $documentsQuery->have_posts() ) : $documentsQuery->the_post();
                                                            $document = get_post_meta( get_the_ID(), 'frontend_document_file', true );
                                                            ?>
                                                            <div id="<?php the_ID(); ?>" <?php post_class('panel panel-default'); ?>>
                                                                <div class="panel-heading" role="tab">
                                                                    <h4 class="panel-title">
                                                                        <span class="tab-title view-count"><?php echo get_the_date('m/d/Y')." - ".get_the_title(); ?></span>
                                                                        <?php if (!empty($document)) { ?>
                                                                            <a class="frontend-download-doc" id="<?php the_ID(); ?>" rel="nofollow" href="<?php
                                                                                if (!empty($document)) : echo $document; 
                                                                                else : echo $document;
                                                                                endif;
                                                                                ?>" download>Download
                                                                            </a>
                                                                        <?php } ?>
                                                                    </h4>
                                                                </div>
                                                                
                                                            </div>
                                                        <?php
                                                        endwhile;
                                                        wp_pagenavi( array( 'query' => $documentsQuery ) );
                                                    else : ?>
                                                        <div class="">
                                                            <p><?php _e( 'No documents yet.', 'bridge-child' ); ?></p>
                                                        </div>
                                                    <?php
                                                    endif;
                                                } ?>
                                            </div>
                                        </div>
                                    </div>                          
                                </div>
                            </div>
                        <?php
                        }
                        else if ( $tools_templates_parents && ! is_wp_error( $tools_templates_parents ) ) {
                    
                            foreach ($tools_templates_parents as $parent) {

                                $parent_tools_templates_access = get_term_meta( $parent, 'documents_category_users_tools_templates_access', true);
                                $parent_tools_templates_access_array[] = $parent_tools_templates_access;
                                
                                $parent_policies_procedures_access = get_term_meta( $parent, 'documents_category_users_policies_procedures_access', true);
                                $parent_policies_procedures_access_array[] = $parent_policies_procedures_access;
                                
                            }
                            
                                get_template_part( 'title' ); ?>
                                <div class="wpb_wrapper">
                                    <div class="entry-content">
                                        <div class="vc_column_container vc_col-sm-3">
                                            <div class="panel panel-default">
                                                <div class="panel-body">
                                                    <div class="slide-container">
                                                        <div class="list-group" id="mg-multisidetabs">
                                                        <a href="#" class="list-group-item parent-item"><span><?php echo $term->name; ?></span><i class="fa fa-arrow-down pull-right"></i></a>
                                                            <div class="panel list-sub mg-show" style="display: block;">
                                                                <div class="panel-body">
                                                                    <div class="list-group">
                                                                        <?php
                                                                            $catargs = array(
                                                                                'taxonomy'     => $term->taxonomy,
                                                                                'child_of' => $term->term_id,
                                                                                'hide_empty'   => false,
                                                                                'title_li'=> false,
                                                                                'exclude'=> $term_chlds_ids_array,           
                                                                                'walker' => new Walker_Categories_Template()
                                                                            );
                                                                            wp_list_categories( $catargs );
                                                                        ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div><!-- ./ end list-group -->
                                                    </div><!-- ./ end slide-container -->
                                                </div><!-- ./ end panel-body -->
                                            </div><!-- ./ end panel panel-default-->
                                        </div>
                                        <div class="vc_column_container vc_col-sm-9">
                                            <div id="ajax-loader">
                                                <img id="loading-image" src="<?php echo get_stylesheet_directory_uri(); ?>/includes/assets/images/ajax-loader.gif" style="display:none;"/>
                                            </div>
                                            <div id="ajax-posts" class="ajax-docs">
                                                <h2><?php echo do_shortcode('[flexy_breadcrumb]'); ?></h2>
                                                
                                                <?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
                                                <div class="row" style="margin:0; padding-bottom: 40px;">
                                                    <div class="col-12 tcs-sort-container">
                                                        <span class="custom-search">
                                                            <a href="#" class="tcs-search-sort tcs-sort-cat" data-order="<?php echo get_query_var('order'); ?>" data-id="<?php echo $term->term_id; ?>">
                                                                <i class="fa fa-sort fa-2x" aria-hidden="true"></i>
                                                            </a>
                                                        </span>

                                                        <label class="tcs-search-sort">
                                                            Sort By:
                                                            <select name="orderby" id="orderby" class="search-field">
                                                                <option value="date" <?php echo get_query_var('orderby') == 'date' ? 'selected' : ''; ?>>Date Posted</option>
                                                                <option value="title" <?php echo get_query_var('orderby') == 'title' ? 'selected' : ''; ?>>Document Title</option>
                                                            </select>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="panel-group" id="dococument-parent" role="tablist" aria-multiselectable="true">
                                                    <?php
                                                        if ($get_category->category_parent == '0') {}
                                                        else {

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

                                                            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
                                                            
                                                            $args = array(
                                                                'post_type'   => 'frontend_documents',
                                                                'post_status'   => 'publish',
                                                                'posts_per_page' =>  25,
                                                                'paged' => $paged,
                                                                'tax_query' => array(
                                                                    array(
                                                                        'taxonomy' => 'frontend_documents_category',
                                                                        'field'    => 'id',
                                                                        'terms'    => get_queried_object_id(),
                                                                        'include_children' => false
                                                                    ),
                                                                ),
                                                            );

                                                            if(!current_user_can( 'manage_options' )) {
                                                                $args['meta_query'] = array(
                                                                    array(
                                                                        'key'     => 'frontend_document_author_company',
                                                                        'value'   => array( $company_assigned_id ),
                                                                        'compare' => 'IN',
                                                                    )
                                                                );
                                                            }

                                                            $documentsQuery = new WP_Query( $args );

                                                            if($documentsQuery->have_posts()) : 
                                                                while ( $documentsQuery->have_posts() ) : $documentsQuery->the_post();

                                                                    $document = get_post_meta( get_the_ID(), 'frontend_document_file', true );
                                                                    
                                                                    ?>
                                                                    <div id="<?php the_ID(); ?>" <?php post_class('panel panel-default'); ?>>
                                                                        <div class="panel-heading" role="tab" >
                                                                            <h4 class="panel-title">
                                                                                <span class="tab-title view-count"><?php echo get_the_date('m/d/Y')." - ".get_the_title(); ?></span>
                                                                                <?php
                                                                                    if (!empty($document)) { ?>
                                                                                        <a class="frontend-download-doc" id="<?php the_ID(); ?>" rel="nofollow" href="<?php if (!empty($document)) : echo $document; else : echo $document; endif; ?>" download>Download</a>
                                                                                    <?php } ?>
                                                                            </h4>
                                                                        </div>
                                                                        
                                                                    </div>
                                                                    <?php
                                                                endwhile;
                                                                wp_pagenavi( array( 'query' => $documentsQuery ) );
                                                            else : ?>
                                                            <div class="">
                                                                <p><?php _e( 'No documents yet.', 'bridge-child' ); ?></p>
                                                            </div>
                                                            <?php
                                                            endif;
                                                        }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>                          
                                    </div>
                                </div>
                            <?php
                        }

                        else {

                            get_template_part( 'title' ); ?>
                            <div class="wpb_wrapper">
                                <div class="entry-content">
                                    <div class="vc_column_container vc_col-sm-3">
                                        <div class="panel panel-default">
                                            <div class="panel-body">
                                                <div class="slide-container">
                                                    <div class="list-group" id="mg-multisidetabs">
                                                    <a href="#" class="list-group-item parent-item"><span><?php echo $term->name; ?></span><i class="fa fa-arrow-down pull-right"></i></a>
                                                        <div class="panel list-sub mg-show" style="display: block;">
                                                            <div class="panel-body">
                                                                <div class="list-group">
                                                                    <?php
                                                                    $catargs = array(
                                                                        'taxonomy'     => $term->taxonomy,
                                                                        'child_of' => $term->term_id,
                                                                        'hide_empty'   => false,
                                                                        'title_li'=> false,
                                                                        'exclude'=> $term_chlds_ids_array,           
                                                                        'walker' => new Walker_Categories_Template()
                                                                    );
                                                                    wp_list_categories( $catargs );
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div><!-- ./ end list-group -->
                                                </div><!-- ./ end slide-container -->
                                            </div><!-- ./ end panel-body -->
                                        </div><!-- ./ end panel panel-default-->
                                    </div>
                                    <div class="vc_column_container vc_col-sm-9">
                                        <div id="ajax-loader">
                                            <img id="loading-image" src="<?php echo get_stylesheet_directory_uri(); ?>/includes/assets/images/ajax-loader.gif" style="display:none;"/>
                                        </div>
                                        <div id="ajax-posts" class="ajax-docs">
                                            <h2><?php echo do_shortcode('[flexy_breadcrumb]'); ?></h2>
                                            
                                            <?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
                                            <div class="row" style="margin:0; padding-bottom: 40px;">
                                                <div class="col-12 tcs-sort-container">
                                                    <span class="custom-search">
                                                        <a href="#" class="tcs-search-sort tcs-sort-cat" data-order="<?php echo get_query_var('order'); ?>" data-id="<?php echo $term->term_id; ?>">
                                                            <i class="fa fa-sort fa-2x" aria-hidden="true"></i>
                                                        </a>
                                                    </span>

                                                    <label class="tcs-search-sort">
                                                        Sort By:
                                                        <select name="orderby" id="orderby" class="search-field">
                                                            <option value="date" <?php echo get_query_var('orderby') == 'date' ? 'selected' : ''; ?>>Date Posted</option>
                                                            <option value="title" <?php echo get_query_var('orderby') == 'title' ? 'selected' : ''; ?>>Document Title</option>
                                                        </select>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="panel-group calling-this-one" id="dococument-parent" role="tablist" aria-multiselectable="true">
                                                <?php

                                                    if(have_posts()) : 
                                                        while ( have_posts() ) : the_post();
                                                            $document = get_post_meta( get_the_ID(), 'frontend_document_file', true );
                                                           
                                                            ?>
                                                            <div id="<?php the_ID(); ?>" <?php post_class('panel panel-default'); ?>>
                                                                <div class="panel-heading" role="tab">
                                                                    <h4 class="panel-title">
                                                                        <span class="tab-title view-count"><?php echo get_the_date('m/d/Y')." - ".get_the_title(); ?></span>
                                                                        <?php
                                                                            if (!empty($document)) { ?>
                                                                                <a class="frontend-download-doc" id="<?php the_ID(); ?>" rel="nofollow" href="<?php if (!empty($document)) : echo $document; else : echo $document; endif; ?>" download>Download</a>
                                                                            <?php } ?>
                                                                    </h4>
                                                                </div>
                                                                
                                                            </div>
                                                        <?php
                                                        endwhile;
                                                        wp_pagenavi();
                                                        wp_reset_postdata();
                                                    else : ?>
                                                    <div class="">
                                                        <p><?php _e( 'No documents yet.', 'bridge-child' ); ?></p>
                                                    </div>
                                                    <?php endif; ?>
                                                
                                            </div>
                                        </div>
                                    </div>                          
                                </div>
                            </div>
                    <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();