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
                        $terms = get_the_terms( get_the_ID(), 'frontend_documents_category' );

                        if ( $tools_templates_access == 'disable' && $policies_procedures_access == 'disable' ) {
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
                            $document = get_post_meta( get_the_ID(), 'frontend_document_file', true );

                            if (!empty($document)) { ?>
                                <a class="frontend-download-doc" rel="nofollow" id="<?php the_ID(); ?>" href="<?php if (!empty($document)) : echo $document; else : echo $document; endif; ?>" target="_blank" download>Download</a>
                            <?php }
                            if (!empty($document)) {
                               echo do_shortcode('[pdf-embedder url="'.$document.'" ]');

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