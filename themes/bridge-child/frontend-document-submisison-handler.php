<?php

/**
 * Register the form and fields for our front-end submission form
 */
function tcs_frontend_documents_form_register() {

      $allTerms = [];

	$prefix = 'frontend_document_';

	// frontend documents categories

      $terms = get_terms( array(
            'taxonomy' => 'frontend_documents_category',
            'hide_empty' => false,
      ) );

	foreach ($terms as $term) {
		$allTerms[$term->slug] = $term->name;
      }

      $cmb = new_cmb2_box(
            array(
                  'id'           => 'front-end-post-form',
                  'object_types' => array( 'frontend_documents' ),
                  'hookup'       => false,
                  'save_fields'  => false,
            )
      );
  
      $cmb->add_field(
            array(
                  'name' => 'Document Name (<span class="required-indicator">*</span>)',
                  'id'      => $prefix . 'title',
                  'desc' => esc_html__( 'Add the document name.', 'bridge-child' ),
                  'type'    => 'text',
                  'attributes' => array(
                        'required' => 'required',
                  ),
            )
      );

	$cmb->add_field( array(
            'name'    => 'Category (<span class="required-indicator">*</span>)',
            'desc'    => 'Select the category for the document.',
            'id'      => $prefix . 'categories',
            'type'    => 'select',
            'options' => $allTerms
      ) );

	$cmb->add_field(
            array(
                  'name' => 'Upload Document (<span class="required-indicator">*</span>)&nbsp;&nbsp;<br><span style="color:#424242; font-size: 12px;">Only files with extension  doc/docx, xls/xlsx, ppt/pptx and pdf are allowed</span>',
                  'id'   => $prefix . 'file',
                  'type' => 'file',
                  'text'    => array(
                        'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
                  ),
                  'query_args' => array(
                        'type' => array(
                              'application/msword',
                              'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                              'application/pdf',
                              'application/vnd.ms-powerpoint',
                              'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                              'application/vnd.ms-excel',
                              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ),
                  ),
                  'attributes' => array(
                        'required' => 'required',
// 					  'readonly' => 'readonly'
                  ),
            )
      );
}

add_action( 'cmb2_init', 'tcs_frontend_documents_form_register' );

  
/**
 * Gets the front-end-post-form cmb instance
 *
 * @return CMB2 object
 */
function tcs_frontend_cmb2_get() {

	$metabox_id = 'front-end-post-form';

	// Post/object ID is not applicable since we're using this form for submission
	$object_id  = 'fake-oject-id';

	// Get CMB2 metabox object
	return cmb2_get_metabox( $metabox_id, $object_id );
}

/**
 * Handle the cmb_frontend_form shortcode
 *
 * @param  array  $atts Array of shortcode attributes
 * @return string       Form html
 */

function tcs_frontend_form_submission_shortcode( $atts = array() ) {

	// Get CMB2 metabox object
	$cmb = tcs_frontend_cmb2_get();

	// Get $cmb object_types
	$post_types = $cmb->prop( 'object_types' );

	// Current user
	$user_id = get_current_user_id();

	// Initiate our output variable
	$output = '';

	// Get any submission errors
	if ( ( $error = $cmb->prop( 'submission_error' ) ) && is_wp_error( $error ) ) {
		// If there was an error with the submission, add it to our ouput.
		$output .= '<h3 class="frd-er-msg">' . sprintf( __( 'There was an error in the submission %s', 'bridge-child' ), '<strong>'. $error->get_error_message() .'</strong>' ) . '</h3>';
	}

	// If the post was submitted successfully, notify the user.
	if ( isset( $_GET['post_submitted'] ) && empty($_GET['document_id']) ) {

		// Add notice of submission to our output
		$output .= '<h3 class="document-success-message">Thank you, your new document has been published.</h3>';
	}

	// ouput form submit button

	$output .= cmb2_get_metabox_form( $cmb, 'fake-oject-id', array( 'save_button' => __( 'Submit Document', 'bridge-child' ) ) );

	// Get our form

	return $output;
}

add_shortcode( 'cmb-frontend-form', 'tcs_frontend_form_submission_shortcode' );

/**
 * Handles form submission on save. Redirects if save is successful, otherwise sets an error message as a cmb property
 *
 * @return void
 */
function tcs_handle_frontend_new_post_form_submission() {

	$new_submission_id = '';
	$prefix = 'frontend_document_';

	// If no form submission, return
	if ( empty( $_POST ) ) {
		return false;
	}

	// Get CMB2 metabox object
	$cmb = tcs_frontend_cmb2_get();
	$post_data = array();
	$user_id = get_current_user_id();

	// Check security nonce
	if ( ! isset( $_POST[ $cmb->nonce() ] ) || ! wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() ) ) {
		return $cmb->prop( 'submission_error', new WP_Error( 'security_fail', __( '<br>Security check failed.' ) ) );
	}

	// Fetch sanitized values
	$sanitized_values = $cmb->get_sanitized_values( $_POST );

		$allowed_types = array('pdf','doc', 'docx', 'csv', 'xla|xls|xlt|xlw', 'xlam', 'xlsb', 'xlsm', 'xltm', 'pps|ppt|pot', 'ppam', 'pptm', 'sldm', 'ppsm','potm', 'csv', 'dotx', 'docx', 'xltx', 'xlsx', 'potx', 'ppsx', 'sldx', 'ppt','pptx' );
	$file_extension = strtolower(pathinfo($sanitized_values['frontend_document_file'], PATHINFO_EXTENSION));

	if( isset($file_extension) && !in_array($file_extension, $allowed_types)) {
		return $cmb->prop( 'submission_error', new WP_Error( 'security_fail', __( '<br><span>Invalid Document File</span>' ) ) );
	}

	
	// Set our post data arguments
	$post_data['post_type']   = 'frontend_documents';
	$post_data['post_title']   = $sanitized_values[$prefix . 'title'];
      $post_data['meta_input']   = array(
		$prefix . 'file'	=>	$sanitized_values[$prefix . 'file']
	);
	$post_data['post_status'] = 'publish';

	// document categories

	$documentCategories = $sanitized_values[$prefix . 'categories'];

	if( !empty($post_data) ) {

		// Create the new document
		$new_submission_id = wp_insert_post( $post_data, true );
		$documentFileURL = get_post_meta($new_submission_id, 'frontend_document_file', true);

            if( !empty($documentFileURL) ) {

                  $documentFileID = attachment_url_to_postid( $documentFileURL );

			wp_update_post( array(
				'ID'            => $documentFileID,
				'post_parent'   => $new_submission_id,
			), true );

			update_post_meta( $documentFileID, '_frontend_document', 'frontend_document' );
			
            }

	} else {

		return $cmb->prop( 'submission_error', new WP_Error( 'security_fail', __( 'Something went wrong. Resubmit the document again.' ) ) );

	}

	// If we hit a snag, update the user
	if ( is_wp_error( $new_submission_id ) ) {
		return $cmb->prop( 'submission_error', $new_submission_id );
	} else {
		wp_set_object_terms($new_submission_id , $documentCategories, 'frontend_documents_category', false);

		// store company name along with post meta

		$corporate_companies = get_user_meta($user_id, 'corporate_companies', true );
	    	$admin_companies = get_user_meta($user_id, 'admin_companies', true );

		if(!empty($corporate_companies)) {
			update_post_meta($new_submission_id, 'frontend_document_author_company', $corporate_companies);
		} else {
			update_post_meta($new_submission_id, 'frontend_document_author_company', $admin_companies);
		}

	}

	// redirect back to the form page with a query variable with the new post ID

	wp_redirect( esc_url_raw( add_query_arg( 'post_submitted', $new_submission_id ) ) ); // new document published

	exit;

}

add_action( 'cmb2_after_init', 'tcs_handle_frontend_new_post_form_submission' );

/**
 * Change Upload Directory for Custom Post-Type (frontend-documents)
 * 
 */

function tcs_custom_documents_upload_directory( $args ) {

	$company_assigned_id = '';
	$assigned_company_data = '';
	$assigned_company_name = '';

	$current_user_id = get_current_user_id();
	$currentURL = $_SERVER['HTTP_REFERER'];
	$basename = pathinfo($currentURL, PATHINFO_BASENAME);

	$corporate_companies = get_user_meta($current_user_id, 'corporate_companies', true );
	$admin_companies = get_user_meta($current_user_id, 'admin_companies', true );

	if(!empty($admin_companies)) {
		$company_assigned_id = $admin_companies;
	}

	if(!empty($corporate_companies)) {
		$company_assigned_id = $corporate_companies;
	}

	if(!empty($company_assigned_id)) {
		$assigned_company_data = get_term( $company_assigned_id, 'company' );
		// $assigned_company_name = str_replace(' ', '', $assigned_company_data->name); // remove spaces from the text
	}

	// create the directory based on the company name
  
	if( $basename == 'add-new-document' ) {
		$args['path'] = $args['basedir'] . "/frontend-documents/" . $assigned_company_data->slug . $args['subdir'];
		$args['url']  = $args['baseurl'] . "/frontend-documents/" . $assigned_company_data->slug . $args['subdir'];
	}

	return $args;

}

/**
 * Function to apply filter to the uploaded documents
 */

function register_pre_upload_files( $file ) {

	add_filter( 'upload_dir', 'tcs_custom_documents_upload_directory' );
	return $file;

}

add_filter( 'wp_handle_upload_prefilter', 'register_pre_upload_files' );