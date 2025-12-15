<?php

/**
 * Template Name: Frontend Document Upload
 */

$tcs_fd_upload_access = get_option('tcs_fd_upload_access');
$tcs_fd_display_message = get_option('tcs_fd_display_message');

// Set default value if upload access is empty
if (empty($tcs_fd_upload_access)) {
    $tcs_fd_upload_access = 'enable';
}

// Set default message if display message is empty
if (empty($tcs_fd_display_message)) {
    $tcs_fd_display_message = "Document Uploads Temporarily Unavailable.";
}
$tcs_fd_display_message = wpautop($tcs_fd_display_message);

$current_user_id = get_current_user_id();
$is_allowed_upload_docs = get_user_meta($current_user_id, 'frontend_docs_upload_access', true);
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

// Check if access is disabled
if ($tcs_fd_upload_access == 'disable') {
      get_header();
      // Display the stored message (with wpautop for paragraph formatting)
                  get_header();

                  echo '<div class="frontend-docs-upload-permission-container">';
                        echo $tcs_fd_display_message;
                  echo '</div>';

            get_footer();
      
}else{

      // if company disabled the upload files access or access disabled by individual user

      if( $company_frntend_doc_access == 'disable' || $is_allowed_upload_docs == 'disable' ) {

            get_header();

                  echo '<div class="frontend-docs-upload-permission-container">';
                        echo "<h3>You don't have permission to upload the docs.</h3>";
                        echo '<h4>Please contact your Account Manager <a href="mailto:' . $account_manager . '">' . $account_manager . '</a><br>OR call us at <a href="tel:8775827347">877.582.7347</a></h4>';
                  echo '</div>';

            get_footer();

            return;    
      }

      ?>

      <?php get_header(); ?>

      <div class="content content_top_margin"> 
            <div class="container">
                  <div class="container_inner default_template_holder clearfix frontend-document-upload-container">
                        <div class="row">
                              <div class="vc_col-sm-3"></div>
                              <div class="vc_col-sm-6">
                                    <div class="add-new-document">
                                          <div class="loader-wrapper">
                                                <img src="<?php echo get_stylesheet_directory_uri() . '/includes/assets/images/ajax-loader.gif'; ?>" alt="loader-gif">
                                          </div>
                                          <?php 
                                                if(!empty($document_id)) {

                                                      echo do_shortcode('[cmb-frontend-form]');
                                                
                                                } else {

                                                      echo do_shortcode('[cmb-frontend-form]');

                                                }  
                                          ?>
                                    </div>
                              </div>
                        </div>
                  </div>
            </div>
      </div>

      <?php get_footer();
      }
       ?>