<?php get_header(); ?>
<?php 
global $wp_query;
$id = $wp_query->get_queried_object_id();

if (get_query_var('paged')) {
    $paged = get_query_var('paged');
} elseif (get_query_var('page')) {
    $paged = get_query_var('page');
} else {
    $paged = 1;
}

$sidebar = $qode_options_proya['category_blog_sidebar'];


if(isset($qode_options_proya['blog_page_range']) && $qode_options_proya['blog_page_range'] != ""){
	$blog_page_range = $qode_options_proya['blog_page_range'];
} else{
	$blog_page_range = $wp_query->max_num_pages;
}

?>
	
	<?php if(get_post_meta($id, "qode_page_scroll_amount_for_sticky", true)) { ?>
		<script>
			var page_scroll_amount_for_sticky = <?php echo get_post_meta($id, "qode_page_scroll_amount_for_sticky", true); ?>;
		</script>
	<?php } ?>

	<?php get_template_part( 'title' ); ?>
	
	<div class="container">
    <?php if(isset($qode_options_proya['overlapping_content']) && $qode_options_proya['overlapping_content'] == 'yes') {?>
        <div class="overlapping_content"><div class="overlapping_content_inner">
    <?php } ?>
	<div class="container_inner default_template_holder clearfix pt-custom-search">
		<?php if(($sidebar == "default")||($sidebar == "")) : ?>
			                    <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')) ?>">
                        <h1 class="tcs-search-heading"><?php echo 'Search Results for: ' ?></h1>
                        <span class="custom-search">
                            <button type="submit" class="tcs-search-sort tcs-sort-cat" data-order="<?php echo get_query_var('order'); ?>">
                                <i class="fa fa-sort fa-2x" aria-hidden="true"></i>
                            </button>
                        </span>

						<input type="hidden" class="ps-sort" id="order" name="order" value="<?php echo strtolower(get_query_var('order')) == 'asc' ? 'desc' : 'asc'; ?>">	
                        <label class="tcs-search-sort" >	
                            Sort By:	
                            <select name="orderby" id="orderby" class="search-field ps-sort">
                                <option value="date" <?php echo strtolower(get_query_var('orderby')) == 'date' ? 'selected' : ''; ?>>Date Posted</option>
                                <option value="title" <?php echo strtolower(get_query_var('orderby')) == 'title' ? 'selected' : ''; ?>>Document Title</option>
                            </select>
                        </label>


                        <div class="custom-search">
                            <div class="inner-form">

                                <label>
                                    <span class="screen-reader-text"><?php _x('Search for:', 'label') ?></span>
                                    <input type="search" id="s" class="search-field" placeholder="<?php echo esc_attr_x('Search &hellip;
                                ', 'placeholder') ?>" value="<?php echo get_search_query() ?>" name="s" />
                                </label>
                                <button type="submit" class="search-submit"><i class="fa fa-search"></i>Search</button>

                            </div>	
                        </div>
                    </form>	
			<div class="blog_holder blog_large_image panel-group" role="tablist" aria-multiselectable="true" id="dococument-parent">
				<?php if(have_posts()) : while ( have_posts() ) : the_post(); ?>
						<?php 
							get_template_part('templates/blog_search', 'loop');
						?>
				
			
				<?php endwhile; ?>
				<?php if($qode_options_proya['pagination'] != "0") : ?>
					<?php
					wp_pagenavi();
					?>
				<?php endif; ?>
				<?php else: //If no posts are present ?>
						<div class="entry">                        
								<p><?php _e('No Documents were found.', 'qode'); ?></p>    
						</div>
				<?php endif; ?>
			</div>	
		<?php elseif($sidebar == "1" || $sidebar == "2"): ?>
			<div class="<?php if($sidebar == "1"):?>two_columns_66_33<?php elseif($sidebar == "2") : ?>two_columns_75_25<?php endif; ?> background_color_sidebar grid2 clearfix">
				<div class="column1">
					<div class="column_inner">
						<div class="blog_holder blog_large_image">
							<?php if(have_posts()) : while ( have_posts() ) : the_post(); ?>
									<?php 
										get_template_part('templates/blog_search', 'loop');
									?>
							
						
							<?php endwhile; ?>
							<?php if($qode_options_proya['pagination'] != "0") : ?>
								<?php pagination($wp_query->max_num_pages, $blog_page_range, $paged); ?>
							<?php endif; ?>
							<?php else: //If no posts are present ?>
									<div class="entry">                        
											<p><?php _e('No posts were found.', 'qode'); ?></p>    
									</div>
							<?php endif; ?>
						</div>	
					</div>
				</div>
				<div class="column2">
					<?php get_sidebar(); ?>	
				</div>
			</div>
	<?php elseif($sidebar == "3" || $sidebar == "4"): ?>
			<div class="<?php if($sidebar == "3"):?>two_columns_33_66<?php elseif($sidebar == "4") : ?>two_columns_25_75<?php endif; ?> background_color_sidebar grid2 clearfix">
				<div class="column1">
				<?php get_sidebar(); ?>	
				</div>
				<div class="column2">
					<div class="column_inner">
						<div class="blog_holder blog_large_image">
							<?php if(have_posts()) : while ( have_posts() ) : the_post(); ?>
									<?php 
										get_template_part('templates/blog_search', 'loop');
									?>
							<?php endwhile; ?>
							<?php if($qode_options_proya['pagination'] != "0") : ?>
								<?php pagination($wp_query->max_num_pages, $blog_page_range, $paged); ?>
							<?php endif; ?>
							<?php else: //If no posts are present ?>
									<div class="entry">                        
											<p><?php _e('No posts were found.', 'qode'); ?></p>    
									</div>
							<?php endif; ?>
						</div>	
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
    <?php if(isset($qode_options_proya['overlapping_content']) && $qode_options_proya['overlapping_content'] == 'yes') {?>
        </div></div>
    <?php } ?>
</div>
	
<?php get_footer(); ?>