<?php
get_header(); 
//global $wp_query;
global $wpdb;
//$id = $wp_query->get_queried_object_id(); // Get the id of the taxonomy
$term = get_queried_object();

$term_tools_templates_access = get_term_meta( get_queried_object_id(), 'documents_category_users_tools_templates_access', true);
$term_policies_procedures_access = get_term_meta( get_queried_object_id(), 'documents_category_users_policies_procedures_access', true);

$tools_templates_access = get_user_meta($current_user->ID, 'tools_templates_access', true );
$policies_procedures_access = get_user_meta($current_user->ID, 'policies_procedures_access', true );

$account_manager = get_user_meta($current_user->ID, 'account_manager', true );

$tools_templates_parents = get_ancestors( get_queried_object_id(), 'documents_category' );
$tools_templates_childs = get_term_children( get_queried_object_id(), 'documents_category' );
$term_chlds_ids_array  = $parent_tools_templates_access_array = $parent_policies_procedures_access_array = array();
$get_category = get_category($term);

?>
<div class="content content_top_margin"> 
    <div class="container">
        <div style="display: none;">
            <?php echo do_shortcode('[pdf-embedder url="" ]'); ?>
        </div>
        <?php if(isset($qode_options_proya['overlapping_content']) && $qode_options_proya['overlapping_content'] == 'yes') { ?>
            <div class="overlapping_content"><div class="overlapping_content_inner">
        <?php } ?>
        <div class="container_inner default_template_holder clearfix">
            <div class="container_inner default_template_holder clearfix page_container_inner taxonomy-header" tax-slug="<?php echo $term->taxonomy; ?>" cpt-name="documents">
                <?php
                /* if user is disabled to access t&t and current term is disabled */
                if (
                        $tools_templates_access == 'disable' && $term_tools_templates_access == 'disable' ||
                        $policies_procedures_access == 'disable' && $term_policies_procedures_access == 'disable'
                        ) {
                    echo '<h3 style="text-align: center;">This feature has been disbaled by your administrator.</h3>';
                    echo '<h4 style="text-align: center;">Please contact your Account Manager <a style="color: #f28800;" href="mailto:'.$account_manager.'">'.$account_manager.'</a><br>OR call us at <a style="color: #f28800;" href="tel:877.582.7347">877.582.7347</a></h4>';
                }
                elseif ( $tools_templates_childs && ! is_wp_error( $tools_templates_childs ) ) {

                    foreach ( $tools_templates_childs as $child ) {
                        $term_tools_templates_access = get_term_meta( $child, 'documents_category_users_tools_templates_access', true);
                        if ($tools_templates_access == 'disable' && $term_tools_templates_access == 'disable') {
                            $term_chlds_ids_array[] = $child;
                        }
                        
                        $term_policies_procedures_access = get_term_meta( $child, 'documents_category_users_policies_procedures_access', true);
                        if ($policies_procedures_access == 'disable' && $term_policies_procedures_access == 'disable') {
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
                                    <h2><?php
                                        echo do_shortcode('[flexy_breadcrumb]');
                                        ?>
                                    </h2>
                                    
                                    <?php
                                    the_archive_description( '<div class="taxonomy-description">', '</div>' );
                                    if ($get_category->category_parent != '0' && have_posts()) { ?>
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
                                    <?php        
                                    }
                                    ?>
                                    <div class="panel-group" id="dococument-parent" role="tablist" aria-multiselectable="true">
                                        <?php
                                        if ($get_category->category_parent == '0') {

                                        }
                                        else {
                                            if(have_posts()) : 
                                                while ( have_posts() ) : the_post();
                                                $document = get_post_meta( get_the_ID(), 'bridge_document_document_file', true );
                                                $download = get_post_meta( get_the_ID(), 'bridge_document_document_download', true );
                                                $youtube_video = esc_url( get_post_meta( get_the_ID(), 'bridge_document_document_youtube', true ) );
                                                $gdrive = esc_url( get_post_meta( get_the_ID(), 'bridge_document_document_gdrive', true ) );
                                                ?>
                                                <div id="<?php the_ID(); ?>" <?php post_class('panel panel-default'); ?>>
                                                    <div class="panel-heading" role="tab" id="heading-<?php the_ID(); ?>">
                                                      <h4 class="panel-title">
                                                        <a class="tab-title view-count" role="button" data-toggle="collapse" data-parent="#dococument-parent" href="#collapse-<?php the_ID(); ?>" aria-expanded="true" aria-controls="collapse-<?php the_ID(); ?>"><?php echo get_the_date('m/d/Y')." - ".get_the_title(); ?></a>
                                                        <?php if (!empty($document)) { ?>
                                                        <a class="download-doc" id="<?php the_ID(); ?>" rel="nofollow" href="<?php
                                                        if (!empty($download)) : echo $download; 
                                                        else : echo $document;
                                                        endif;
                                                        ?>" download>Download</a>
                                                        <?php } ?>
                                                      </h4>
                                                    </div>
                                                    <div id="collapse-<?php the_ID(); ?>" class="panel-collapse collapse documents-panel" role="tabpanel" aria-labelledby="heading-<?php the_ID(); ?>">
                                                      <div class="panel-body">
                                                        <?php
                                                        the_content();
                                                        if (!empty($document)) {
                                                            //echo do_shortcode('[wonderplugin_pdf src="'.$document.'" width="100%" height="800px" style="border:0;"]');
                                                            echo do_shortcode('[pdf-embedder url="'.$document.'" ]');
                                                        } elseif (!empty($youtube_video))  { 
                                                            ?>
                                                            <div class="video-container">
                                                                <?php
                                                                $video_id = explode('=', $youtube_video);                    
                                                                if(strpos($video_id[1],"&")){
                                                                    $explode_more = explode('&', $video_id[1]);
                                                                    $video_id = $explode_more[0];
                                                                } else {
                                                                    $video_id = $video_id[1];
                                                                }
                                                                ?>
                                                                <div class="yt-lazyload" data-id="<?php echo $video_id ?>" data-random=""></div>
                                                            </div>
                                                            <?php
                                                        } elseif (!empty($gdrive)) {
                                                            echo '<div class="video-container">';
                                                            echo '<iframe src="'.$gdrive.'"></iframe>';
                                                            echo '</div>';
                                                        }
                                                        ?>
                                                      </div>
                                                    </div>
                                                </div>
                                                <?php
                                                endwhile;
                                                wp_pagenavi();
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
                elseif ( $tools_templates_parents && ! is_wp_error( $tools_templates_parents ) ) {
                    
                    foreach ($tools_templates_parents as $parent) {
                        $parent_tools_templates_access = get_term_meta( $parent, 'documents_category_users_tools_templates_access', true);
                        $parent_tools_templates_access_array[] = $parent_tools_templates_access;
                        
                        $parent_policies_procedures_access = get_term_meta( $parent, 'documents_category_users_policies_procedures_access', true);
                        $parent_policies_procedures_access_array[] = $parent_policies_procedures_access;
                        
//                        $parent_policies_procedures_access_array
                    }
                    if (
                            $tools_templates_access == 'disable' && in_array('disable', $parent_tools_templates_access_array) ||
                            $policies_procedures_access == 'disable' && in_array('disable', $parent_policies_procedures_access_array)
                            ) {
                        echo '<h3 style="text-align: center;">This feature has been disbaled by your administrator.</h3>';
                        echo '<h4 style="text-align: center;">Please contact your Account Manager <a style="color: #f28800;" href="mailto:'.$account_manager.'">'.$account_manager.'</a><br>OR call us at <a style="color: #f28800;" href="tel:877.582.7347">877.582.7347</a></h4>';
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
                                        <h2><?php
                                            echo do_shortcode('[flexy_breadcrumb]');
                                            ?>
                                        </h2>
                                        
                                        <?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
                                        <?php if(have_posts()): ?>
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
                                        <?php endif; ?>
                                        <div data-id="calling" class="panel-group" id="dococument-parent" role="tablist" aria-multiselectable="true">
                                            <?php
                                            if ($get_category->category_parent == '0') {
                                            }
                                            else {
                                                if(have_posts()) : 
                                                    while ( have_posts() ) : the_post();
                                                    $document = get_post_meta( get_the_ID(), 'bridge_document_document_file', true );
                                                    $download = get_post_meta( get_the_ID(), 'bridge_document_document_download', true );
                                                    $youtube_video = esc_url( get_post_meta( get_the_ID(), 'bridge_document_document_youtube', true ) );
                                                    $gdrive = esc_url( get_post_meta( get_the_ID(), 'bridge_document_document_gdrive', true ) );
                                                    ?>
                                                    <div id="<?php the_ID(); ?>" <?php post_class('panel panel-default'); ?>>
                                                        <div class="panel-heading" role="tab" id="heading-<?php the_ID(); ?>">
                                                          <h4 class="panel-title">
                                                            <a class="tab-title view-count" role="button" data-toggle="collapse" data-parent="#dococument-parent" href="#collapse-<?php the_ID(); ?>" aria-expanded="true" aria-controls="collapse-<?php the_ID(); ?>"><?php echo get_the_date('m/d/Y')." - ".get_the_title(); ?></a>
                                                            <?php
                                                            if (!empty($document)) { ?>
                                                            <a class="download-doc" id="<?php the_ID(); ?>" rel="nofollow" href="<?php if (!empty($download)) : echo $download; else : echo $document; endif; ?>" download>Download</a>
                                                            <?php } ?>
                                                          </h4>
                                                        </div>
                                                        <div id="collapse-<?php the_ID(); ?>" class="panel-collapse collapse documents-panel" role="tabpanel" aria-labelledby="heading-<?php the_ID(); ?>">
                                                          <div class="panel-body">
                                                            <?php
                                                            the_content();
                                                            if (!empty($document)) {
                                                                //echo do_shortcode('[wonderplugin_pdf src="'.$document.'" width="100%" height="800px" style="border:0;"]');
                                                                echo do_shortcode('[pdf-embedder url="'.$document.'" ]');
                                                            }
                                                            elseif (!empty($youtube_video))  { 
                                                                ?>
                                                                <div class="video-container">
                                                                    <?php
                                                                    $video_id = explode('=', $youtube_video);                    
                                                                    if(strpos($video_id[1],"&")){
                                                                        $explode_more = explode('&', $video_id[1]);
                                                                        $video_id = $explode_more[0];
                                                                    } else {
                                                                        $video_id = $video_id[1];
                                                                    }
                                                                    ?>
                                                                    <div class="yt-lazyload" data-id="<?php echo $video_id ?>" data-random=""></div>
                                                                </div>
                                                                <?php
                                                            }
                                                            elseif (!empty($gdrive)) {
                                                                echo '<div class="video-container">';
                                                                echo '<iframe src="'.$gdrive.'"></iframe>';
                                                                echo '</div>';
                                                            }
                                                            ?>
                                                          </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    endwhile;
                                                    wp_pagenavi();
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
                                    <h2><?php
                                        echo do_shortcode('[flexy_breadcrumb]');
                                        ?>
                                    </h2>
                                    
                                    <?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
                                    <?php if(have_posts()): ?>
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
                                    <?php endif; ?>
                                    <div class="panel-group" id="dococument-parent" role="tablist" aria-multiselectable="true">
                                        <?php
                                        if ($get_category->category_parent == '0') {
                                        }
                                        else {
                                            if(have_posts()) : 
                                                while ( have_posts() ) : the_post();
                                                $document = get_post_meta( get_the_ID(), 'bridge_document_document_file', true );
                                                $download = get_post_meta( get_the_ID(), 'bridge_document_document_download', true );
                                                $youtube_video = esc_url( get_post_meta( get_the_ID(), 'bridge_document_document_youtube', true ) );
                                                $gdrive = esc_url( get_post_meta( get_the_ID(), 'bridge_document_document_gdrive', true ) );
                                                ?>
                                                <div id="<?php the_ID(); ?>" <?php post_class('panel panel-default'); ?>>
                                                    <div class="panel-heading" role="tab" id="heading-<?php the_ID(); ?>">
                                                      <h4 class="panel-title">
                                                        <a class="tab-title view-count" role="button" data-toggle="collapse" data-parent="#dococument-parent" href="#collapse-<?php the_ID(); ?>" aria-expanded="true" aria-controls="collapse-<?php the_ID(); ?>"><?php echo get_the_date('m/d/Y')." - ".get_the_title(); ?></a>
                                                        <?php
                                                        if (!empty($document)) { ?>
                                                        <a class="download-doc" id="<?php the_ID(); ?>" rel="nofollow" href="<?php if (!empty($download)) : echo $download; else : echo $document; endif; ?>" download>Download</a>
                                                        <?php } ?>
                                                      </h4>
                                                    </div>
                                                    <div id="collapse-<?php the_ID(); ?>" class="panel-collapse collapse documents-panel" role="tabpanel" aria-labelledby="heading-<?php the_ID(); ?>">
                                                      <div class="panel-body">
                                                        <?php
                                                        the_content();
                                                        if (!empty($document)) {
                                                            //echo do_shortcode('[wonderplugin_pdf src="'.$document.'" width="100%" height="800px" style="border:0;"]');
                                                            echo do_shortcode('[pdf-embedder url="'.$document.'" ]');
                                                        }
                                                        elseif (!empty($youtube_video))  { 
                                                            ?>
                                                            <div class="video-container">
                                                                <?php
                                                                $video_id = explode('=', $youtube_video);                    
                                                                if(strpos($video_id[1],"&")){
                                                                    $explode_more = explode('&', $video_id[1]);
                                                                    $video_id = $explode_more[0];
                                                                } else {
                                                                    $video_id = $video_id[1];
                                                                }
                                                                ?>
                                                                <div class="yt-lazyload" data-id="<?php echo $video_id ?>" data-random=""></div>
                                                            </div>
                                                        <?php
                                                        }
                                                        elseif (!empty($gdrive)) {
                                                            echo '<div class="video-container">';
                                                            echo '<iframe src="'.$gdrive.'"></iframe>';
                                                            echo '</div>';
                                                        }
                                                        ?>
                                                      </div>
                                                    </div>
                                                </div>
                                                <?php
                                                endwhile;
                                                wp_pagenavi();
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
                ?>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();