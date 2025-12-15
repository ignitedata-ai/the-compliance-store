<?php
get_header();
$account_manager = get_user_meta($current_user->ID, 'account_manager', true );
get_template_part( 'title' );
?>
<div class="content content_top_margin">
    <div class="container">
        <div class="container_inner default_template_holder clearfix">
            <div class="container_inner default_template_holder clearfix page_container_inner taxonomy-header">
                <div class="wpb_wrapper" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="entry-content">
                        <h3 style="text-align: center;">Access Denied. You are not allowed to access this.</h3>
                        <h4 style="text-align: center;">Please contact your Account Manager <a style="color: #f28800;" href="mailto:"<?php echo $account_manager; ?>><?php echo $account_manager; ?></a><br>OR call us at <a style="color: #f28800;" href="tel:877.582.7347">877.582.7347</a></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();