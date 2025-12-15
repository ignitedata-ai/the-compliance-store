<?php get_header(); 
//global $wp_query;
global $wpdb;
//$id = $wp_query->get_queried_object_id(); // Get the id of the taxonomy
$term = get_queried_object();
$tools_templates_access = '';
$tools_templates_access = get_user_meta($current_user->ID, 'tools_templates_access', true );
if ($tools_templates_access == 'disable') {
    $my_account = site_url().'/my-account/';
    if ($term->slug == 'tools-and-templates') {
        wp_redirect( $my_account );
        die();
    }
    $tools_templates_childs = get_term_children( '2198', 'documents_category' );
    if (in_array($term->term_id, $tools_templates_childs)) {
        wp_redirect( $my_account );
        die();
    }
}
$get_category = get_category($term);
//$termchildren = get_term_children($id,$term->taxonomy); // Get the children of said taxonomy
$parent = $term->parent;
if ( get_query_var('paged') ) { $paged = get_query_var('paged'); }
elseif ( get_query_var('page') ) { $paged = get_query_var('page'); }
else { $paged = 1; }


$sidebar = $qode_options_proya['category_blog_sidebar'];

if(isset($qode_options_proya['blog_page_range']) && $qode_options_proya['blog_page_range'] != ""){
	$blog_page_range = $qode_options_proya['blog_page_range'];
} else{
	$blog_page_range = $wp_query->max_num_pages;
}
if(get_post_meta($id, "qode_page_scroll_amount_for_sticky", true)) { ?>
	<script>
	var page_scroll_amount_for_sticky = <?php echo get_post_meta($id, "qode_page_scroll_amount_for_sticky", true); ?>;
	</script>
<?php }
get_template_part( 'title' ); ?>
<div class="content content_top_margin"> 
	<div class="container">
		<?php if(isset($qode_options_proya['overlapping_content']) && $qode_options_proya['overlapping_content'] == 'yes') { ?>
		    <div class="overlapping_content"><div class="overlapping_content_inner">
		<?php } ?>
		<div class="container_inner default_template_holder clearfix">
			<div class="container_inner default_template_holder clearfix page_container_inner taxonomy-header">
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
                                                    /*'orderby' => 'count',
                                                    'order'      => 'DESC',*/
    												//'hide_title_if_empty' => true,
                                                    /*'orderby' => 'meta_value_num',
                                                    'meta_key' => 'bridge_child_term_term_order',*/
                                                    /*'order' => 'DESC',*/
													//'hierarchical' => true,
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
                                    /*$trail = get_term_parents_list( $term->term_id, $term->taxonomy, array( 'inclusive' => false, 'link' => false, 'separator' => ' /' ) );
                                    if (empty($trail)) {
                                        echo $term->name;
                                    }
                                    else {
                                        echo $trail .'<p class="breadcrumb-tail">'. $term->name.'</p>';
                                    }*/
                                    ?>
                                </h2>
                                <?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
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
                                                <div class="panel panel-default">
                                                    <div class="panel-heading" role="tab" id="heading-<?php the_ID(); ?>">
                                                      <h4 class="panel-title">
                                                        <a class="tab-title view-count" role="button" data-toggle="collapse" data-parent="#dococument-parent" href="#collapse-<?php the_ID(); ?>" aria-expanded="true" aria-controls="collapse-<?php the_ID(); ?>"><?php echo get_the_date('m/d/Y')." - ".get_the_title(); ?></a>
                                                        <?php
                                                        if (!empty($document)) { ?>
                                                        <a class="download-doc" id="<?php the_ID(); ?>" rel="nofollow" href="<?php if (!empty($download)) : echo $download; else : echo $document; endif; ?>" download>Download</a>
                                                        <?php } ?>
                                                      </h4>
                                                    </div>
                                                    <div id="collapse-<?php the_ID(); ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-<?php the_ID(); ?>">
                                                      <div class="panel-body">
                                                        <?php
                                                        the_content();
                                                        if (!empty($document)) {
                                                            echo do_shortcode('[wonderplugin_pdf src="'.$document.'" width="100%" height="800px" style="border:0;"]');
                                                        }
                                                        elseif (!empty($youtube_video))  { 
                                                            echo '<div class="video-container">';
                                                            echo wp_oembed_get( $youtube_video );
                                                            echo '</div>';
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
                                            </div>
                                            <?php
                                            endwhile;
                                            //echo '<nav class="pagination justify-content-end">';
                                            wp_pagenavi();
                                            //echo '</nav>';
                                        else : ?>'  '
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
			</div>
		</div>
	</div>
    <?php if(isset($qode_options_proya['overlapping_content']) && $qode_options_proya['overlapping_content'] == 'yes') {?>
        </div></div>
        <?php } ?>
	</div>
</div>
<?php
get_footer();  ?>