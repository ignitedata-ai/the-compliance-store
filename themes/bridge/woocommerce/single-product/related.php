<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*** Our code modification inside Woo template - begin ***/
$title_tag         = 'h4';
$title_tag_options = bridge_qode_options()->getOptionValue( 'woo_product_single_related_post_tag' );
if ( $title_tag_options != '' ) {
	$title_tag = $title_tag_options;
}

$heading = apply_filters( 'woocommerce_product_related_products_heading', esc_html__( 'Related products', 'bridge' ) );

if ( $related_products ) :
	/**
	 * Ensure all images of related products are lazy loaded by increasing the
	 * current media count to WordPress's lazy loading threshold if needed.
	 * Because wp_increase_content_media_count() is a private function, we
	 * check for its existence before use.
	 */
	if ( function_exists( 'wp_increase_content_media_count' ) ) {
		$content_media_count = wp_increase_content_media_count( 0 );
		if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
			wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
		}
	}
	?>
	
	<div class="related products">
		<<?php echo bridge_qode_get_escape_title_tag( $title_tag ); ?> class="qode-related-upsells-title"><?php echo esc_html( $heading ); ?></<?php echo bridge_qode_get_escape_title_tag($title_tag); ?>>
		
		<?php woocommerce_product_loop_start(); ?>
		
		<?php foreach ( $related_products as $related_product ) : ?>
			
			<?php
			$post_object = get_post( $related_product->get_id() );
			
			setup_postdata( $GLOBALS['post'] =& $post_object );
			
			wc_get_template_part( 'content', 'product' ); ?>
		
		<?php endforeach; ?>
		
		<?php woocommerce_product_loop_end(); ?>
	
	</div>

<?php endif;

wp_reset_postdata();


/*** Our code modification inside Woo template - end ***/
