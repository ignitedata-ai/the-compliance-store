<?php

/**
 * Template Name: View All Frontend Documents
 */

// vars
 
$company_assigned_id = '';
$company_frntend_doc_access = '';

// user meta

$current_user_id = get_current_user_id();
$account_manager = get_user_meta($current_user_id, 'account_manager', true );

// companies

$corporate_companies = get_user_meta($current_user_id, 'corporate_companies', true );
$admin_companies = get_user_meta($current_user_id, 'admin_companies', true );

if(!empty($admin_companies)) {
      $company_assigned_id = $admin_companies;
}

if(!empty($corporate_companies)) {
      $company_assigned_id = $corporate_companies;
}

if (isset($company_assigned_id) && !empty($company_assigned_id)) {
      $company_frntend_doc_access = get_term_meta( $company_assigned_id, 'users_comapny_company_frontend_documents_access', true);
}

if( $company_frntend_doc_access == 'disable' ) {

      get_header();

            echo '<div class="frontend-docs-view-permission-container">';
                  echo "<h3>You don't have permission to view the documents.</h3>";
                  echo '<h4>Please contact your Account Manager <a href="mailto:' . $account_manager . '">' . $account_manager . '</a><br>OR call us at <a href="tel:8775827347">877.582.7347</a></h4>';
            echo '</div>';

      get_footer();

      return;

}
    
$args = array(
      'post_type'   => 'frontend_documents',
      'posts_per_page'  =>    -1,
      'post_status'   => 'publish',
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

$results = new WP_Query( $args );
$totalData = $results->found_posts;

?>

<?php get_header(); ?>

<div class="content content_top_margin"> 
      <div class="container">
            <div class="default_template_holder clearfix view-all-frontends-documents">
                  <div class="row">
                        <div class="vc_col-sm-12">
                              <div class="loader-wrapper">
                                    <img src="<?php echo get_stylesheet_directory_uri() . '/includes/assets/images/ajax-loader.gif'; ?>" alt="loader-gif">
                              </div>
                              
                              <!-- frontend document categories -->
                              <nav class="navbar navbar-default">
                                    <div class="container-fluid">
                                          <div class="navbar-header">
                                                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#view-all-docs-categories-menu">
                                                      <span class="sr-only">Toggle navigation</span>
                                                      <span class="icon-bar"></span>
                                                      <span class="icon-bar"></span>
                                                      <span class="icon-bar"></span>
                                                </button>
                                          </div>

                                          <?php
                                          
                                                if ( has_nav_menu( 'fd_categories_menu' ) ) {
                                                      
                                                      wp_nav_menu( array(
                                                            'theme_location'    => 'fd_categories_menu',
                                                            'depth'             => 2,
                                                            'container'         => 'div',
                                                            'container_class'   => 'collapse navbar-collapse',
                                                            'container_id'      => 'view-all-docs-categories-menu',
                                                            'menu_class'        => 'nav navbar-nav',
                                                            'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                                                            'walker'            => new WP_Bootstrap_Navwalker(),
                                                      ) );

                                                }
                                          ?>
                                    </div>

                              </nav>

                              <table id="view-all-documents" class="display" style="width:100%">
                                    <thead>
                                          <tr>
                                                <th>Name</th>
                                                <th>Author</th>
                                                <th>Categories</th>
                                                <th>Document Download</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                          </tr>
                                    </thead>
                                    <tfoot>
                                          <tr>
                                                <th>Name</th>
                                                <th>Author</th>
                                                <th>Categories</th>
                                                <th>Document Download</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                          </tr>
                                    </tfoot>
                              </table>
                        </div>
                  </div>
            </div>
      </div>
</div>


<?php get_footer(); ?>