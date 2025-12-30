<?php

/* 
 * Template Name: Most Popular Documents
 */

get_header(); 
/*$d = strtotime("today");*/

//$start_week = strtotime("last monday",$d);
//$start = date("Y-m-d",$start_week);
$last_7_days = date('Y-m-d', strtotime('-7 days'));
$last_30_days = date('Y-m-d', strtotime('-30 days'));
$last_365_days = date('Y-m-d', strtotime('-365 days'));
?>
<div class="content content_top_margin">
    <div class="container">
    	<div class="container_inner default_template_holder clearfix page_container_inner">
            <div id="most-popular-documents" class="vc_row wpb_row section vc_row-fluid most-popular-documents vc_custom_1551185081599" >
            	<h2>Most Popular Downloads</h2>
            	<p>Want to know what your colleagues are looking at?  View the top ten downloads of the past week, month or year.</p>
                <div style="display: none;">
                    <?php echo do_shortcode('[pdf-embedder url="" ]'); ?>
                </div>
            	<div class=" full_section_inner clearfix text-center">
                    <div class="wpb_column vc_column_container vc_col-sm-4 vc_col-has-fill">
                        <div class="wpb_text_column wpb_content_element popular-tab ">
                            <button type="button" class="popular-doc-btn <?php if ($_SERVER["QUERY_STRING"] == 'weekly') { echo 'active'; } ?>" data-start-date="<?php echo $last_7_days; ?>" data-end-date="<?php echo date("Y-m-d"); ?>"> <strong><?php _e( 'Week', 'reports' ); ?></strong></button>
                        </div>
                    </div>
                    <div class="wpb_column vc_column_container vc_col-sm-4 vc_col-has-fill">
                        <div class="wpb_text_column wpb_content_element popular-tab ">
					        <button type="button" class="popular-doc-btn <?php if ($_SERVER["QUERY_STRING"] == 'monthly') { echo 'active'; } ?>" data-start-date="<?php echo $last_30_days; ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>"><strong><?php _e( 'Month', 'reports' ); ?></strong></button>
                        </div>
                    </div>
                    <div class="wpb_column vc_column_container vc_col-sm-4 vc_col-has-fill">
                        <div class="wpb_text_column wpb_content_element popular-tab ">
					        <button type="button" class="popular-doc-btn <?php if ($_SERVER["QUERY_STRING"] == 'yearly') { echo 'active'; } ?>" data-start-date="<?php echo $last_365_days; ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>"><strong><?php _e( 'Year', 'reports' ); ?></strong></button>
                        </div>
                    </div>
                </div>
                <div id="popular-loader">
			        <img class="loading-image" src="<?php echo get_stylesheet_directory_uri(); ?>/includes/assets/images/ajax-loader.gif" style="display:none;"/>
			    </div>
                <div class="panel-group" role="tablist" aria-multiselectable="true" id="popular-documents">
                    <?php
                    if (isset($_SERVER['QUERY_STRING'])) {
                        global $wpdb;
                        $start_date = '';
                        $end_date = '';
                        if ($_SERVER["QUERY_STRING"] == 'weekly') {
                            $start_date = $last_7_days;
                            $end_date = date("Y-m-d");
                        } elseif ($_SERVER["QUERY_STRING"] == 'monthly') {
                            $start_date = $last_30_days;
                            $end_date = date( 'Y-m-d' );
                        } elseif ($_SERVER["QUERY_STRING"] == 'yearly') {                            
                            $start_date = $last_365_days;
                            $end_date = date( 'Y-m-d' );
                        }

                        $tools_templates_access = get_user_meta($current_user->ID, 'tools_templates_access', true );
                        $policies_procedures_access = get_user_meta($current_user->ID, 'policies_procedures_access', true );
                        
                        $q = $wpdb->prepare("SELECT *, COUNT(*) AS count FROM " . $wpdb->prefix . "reports_downloads
                            WHERE DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')>=%s
                            AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')<=%s
                            GROUP BY post_id ORDER BY count DESC LIMIT 10", $start_date, $end_date);

                        $results = $wpdb->get_results($q, OBJECT);

                        $disabled_array = array();

                        foreach ($results as $result) { ?>
                            <article id="post-<?php echo $result->post_id; ?>" class="panel panel-default">
                                <?php
                                $document = get_post_meta( $result->post_id, 'bridge_document_document_file', true );
                                $download = get_post_meta( $result->post_id, 'bridge_document_document_download', true );
                                $youtube_video = esc_url( get_post_meta( $result->post_id, 'bridge_document_document_youtube', true ) );
                                $gdrive = esc_url( get_post_meta( $result->post_id, 'bridge_document_document_gdrive', true ) );

                                $terms = get_the_terms( $result->post_id, 'documents_category' );
                                
                                if ( $terms && ! is_wp_error( $terms ) ) {
                                    $term_ids_array  = array();
                                    foreach ( $terms as $term ) {
                                        $term_tools_templates_access = get_term_meta( $term->term_id, 'documents_category_users_tools_templates_access', true);
                                        if ($tools_templates_access == 'disable' && $term_tools_templates_access == 'disable') {
                                            $disabled_array[] = $result->post_id;
                                        }
                                        
                                        $term_policies_procedures_access = get_term_meta( $term->term_id, 'documents_category_users_policies_procedures_access', true);
                                        
                                        if ($policies_procedures_access == 'disable' && $term_policies_procedures_access == 'disable') {
                                            $disabled_array[] = $result->post_id;
                                        }
                                    }
                                }
                                if (in_array($result->post_id, $disabled_array)) { ?>
                                    <div class="panel-heading" role="tab" id="heading-<?php echo $result->post_id; ?>" style="cursor: not-allowed;">
                                        <h4 class="panel-title"><?php echo get_the_date('m/d/Y',$result->post_id)." - ".get_the_title($result->post_id); ?></h4>
                                    </div>
                                <?php
                                } else { ?>
                                    <div class="panel-heading" role="tab" id="heading-<?php echo $result->post_id; ?>">
                                        <h4 class="panel-title">
                                                <a class="tab-title" role="button" data-toggle="collapse" data-parent="#popular-documents" href="#collapse-<?php echo $result->post_id; ?>" aria-expanded="true" aria-controls="collapse-<?php echo $result->post_id; ?>"><?php echo get_the_date('m/d/Y',$result->post_id)." - ".get_the_title($result->post_id); ?></a>
                                                <?php
                                                if (!empty($document)) { ?>
                                                <a class="download-doc" id="<?php echo $result->post_id; ?>" rel="nofollow" href="<?php if (!empty($download)) : echo $download; else : echo $document; endif; ?>" target="_blank" download>Download</a>
                                            <?php } ?>
                                        </h4>
                                </div>
                                <div id="collapse-<?php echo $result->post_id; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-<?php echo $result->post_id; ?>">
                                    <div class="panel-body">
                                        <?php
                                        if (!empty($document)) {
                                            //echo do_shortcode('[wonderplugin_pdf src="'.$document.'" width="100%" height="800px" style="border:0;"]');
                                            echo do_shortcode('[pdf-embedder url="'.$document.'" ]');
                                        }
                                        elseif (!empty($youtube_video)) { ?>
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
                                        <?php }
                                        elseif (!empty($gdrive)) {
                                            echo '<div class="video-container">';
                                            echo '<iframe src="'.$gdrive.'"></iframe>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php } ?>
                            </article>
                        <?php
                        }
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
<script type="text/javascript">
    (function ($) {
        'use strict';
        $(document).ready(function () {
            $('.popular-doc-btn').click(function(){ // when a .myDiv is clicked
                $('.popular-doc-btn.active').removeClass('active').addClass('not-active'); // replace former active with not-active
                $(this).removeClass('not-active').addClass('active'); // add class active to current element instead of class not-active
            })
        });
    })(jQuery);
</script>