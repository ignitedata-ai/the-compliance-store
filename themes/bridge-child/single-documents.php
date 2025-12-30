<?php
get_header();
$document = $download = '';
while ( have_posts() ) : the_post();
    $tools_templates_access = get_user_meta($current_user->ID, 'tools_templates_access', true );
    $policies_procedures_access = get_user_meta($current_user->ID, 'policies_procedures_access', true );
    
    get_template_part( 'title' );
    ?>
    <div class="content content_top_margin">
        <div class="container">
            <div class="container_inner default_template_holder clearfix">
                <div class="container_inner default_template_holder clearfix page_container_inner taxonomy-header">
                    <div class="wpb_wrapper" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div class="entry-content">
                        <?php
                        $terms = get_the_terms( get_the_ID(), 'documents_category' );
                        $documents_category = $documents_category_p_p = array();
                                                 
                        if ( $terms && ! is_wp_error( $terms ) ) {
                            
                            foreach ( $terms as $term ) {

                                $term_tools_templates_access = get_term_meta( $term->term_id, 'documents_category_users_tools_templates_access', true);
                                $documents_category[] = $term_tools_templates_access;
                                
                                $term_policies_procedures_access = get_term_meta( $term->term_id, 'documents_category_users_policies_procedures_access', true);
                                $documents_category_p_p[] = $term_policies_procedures_access;
                            }
                        }

                        if (
                                in_array('disable', $documents_category) && $tools_templates_access == 'disable' ||
                                in_array('disable', $documents_category_p_p) && $policies_procedures_access == 'disable'
                                ) {
                            $account_manager = get_user_meta($current_user->ID, 'account_manager', true );
                            echo '<h3 style="text-align: center;">This feature has been disbaled by your administrator.</h3>';
                                    echo '<h4 style="text-align: center;">Please contact your Account Manager <a style="color: #f28800;" href="mailto:'.$account_manager.'">'.$account_manager.'</a><br>OR call us at <a style="color: #f28800;" href="tel:877.582.7347">877.582.7347</a></h4>';
                        }
                        else {
                            ?>
                            <h3><?php the_title(); ?></h3>
                            <div class="wpb_text_column wpb_content_element ">
                                <div class="wpb_wrapper">
                                    <?php the_content(); ?>
                                </div> 
                            </div>
                            <?php
                            $document = get_post_meta( get_the_ID(), 'bridge_document_document_file', true );
                            $download = get_post_meta( get_the_ID(), 'bridge_document_document_download', true );
                            $youtube_video = esc_url( get_post_meta( get_the_ID(), 'bridge_document_document_youtube', true ) );
                            $gdrive = esc_url( get_post_meta( get_the_ID(), 'bridge_document_document_gdrive', true ) );

                            if (!empty($document)) { ?>
                            <a class="download-doc" rel="nofollow" id="<?php the_ID(); ?>" href="<?php if (!empty($download)) : echo $download; else : echo $document; endif; ?>" target="_blank" download>Download</a>
                            <?php }
                            if (!empty($document)) {
                               echo do_shortcode('[pdf-embedder url="'.$document.'" ]');

                            } elseif (!empty($youtube_video)) { ?>
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
                                echo "</div>";
                            }
                         } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
endwhile;
get_footer();