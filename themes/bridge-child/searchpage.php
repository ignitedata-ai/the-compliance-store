<?php 
/*
Template Name: Search Page
*/
global $wp_query;
$id = $wp_query->get_queried_object_id();
$sidebar = get_post_meta($id, "qode_show-sidebar", true);  

$enable_page_comments = false;
if(get_post_meta($id, "qode_enable-page-comments", true) == 'yes') {
	$enable_page_comments = true;
}

if(get_post_meta($id, "qode_page_background_color", true) != ""){
	$background_color = get_post_meta($id, "qode_page_background_color", true);
}else{
	$background_color = "";
}

$content_style_spacing = "";
if(get_post_meta($id, "qode_margin_after_title", true) != ""){
	if(get_post_meta($id, "qode_margin_after_title_mobile", true) == 'yes'){
		$content_style_spacing = "padding-top:".esc_attr(get_post_meta($id, "qode_margin_after_title", true))."px !important";
	}else{
		$content_style_spacing = "padding-top:".esc_attr(get_post_meta($id, "qode_margin_after_title", true))."px";
	}
}

if ( get_query_var('paged') ) { $paged = get_query_var('paged'); }
elseif ( get_query_var('page') ) { $paged = get_query_var('page'); }
else { $paged = 1; }

?>
	<?php get_header(); ?>
		<?php if(get_post_meta($id, "qode_page_scroll_amount_for_sticky", true)) { ?>
			<script>
			var page_scroll_amount_for_sticky = <?php echo get_post_meta($id, "qode_page_scroll_amount_for_sticky", true); ?>;
			</script>
		<?php } ?>
			<?php get_template_part( 'title' ); ?>
		<?php
		$revslider = get_post_meta($id, "qode_revolution-slider", true);
		if (!empty($revslider)){ ?>
			<div class="q_slider">
				<div class="q_slider_inner">
					<?php echo do_shortcode($revslider); ?>
				</div>
			</div>
		<?php
		}
		?>
		<div class="container"<?php if($background_color != "") { echo " style='background-color:". $background_color ."'";} ?>>
            <?php if(isset($qode_options_proya['overlapping_content']) && $qode_options_proya['overlapping_content'] == 'yes') {?>
                <div class="overlapping_content"><div class="overlapping_content_inner">
            <?php } ?>
			<div class="container_inner default_template_holder clearfix page_container_inner" <?php qode_inline_style($content_style_spacing); ?>>
				<div class="custom-search">
					<h2>WHAT ARE YOU LOOKING FOR?</h2>
					<div class="inner-form">
						<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ) ?>">
							<label>
								<span class="screen-reader-text"><?php _x( 'Search for:', 'label' )?></span>
								<input type="search" id="s" class="search-field" placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder' ) ?>" value="<?php echo get_search_query() ?>" name="s" />
							</label>
							<button type="submit" class="search-submit"><i class="fa fa-search"></i>Search</button>
						</form>							
					</div>	
				</div>
			</div>
			
		</div>
        <?php if(isset($qode_options_proya['overlapping_content']) && $qode_options_proya['overlapping_content'] == 'yes') {?>
            </div></div>
        <?php } ?>
	</div>
	<?php do_action('qodef_page_after_container') ?>
	<?php get_footer(); ?>