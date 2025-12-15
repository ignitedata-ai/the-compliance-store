<?php

/**
 * Delete frontend documents from the admin panel
 */

function delete_frontend_document_attachments() {

      $postURL = filter_input(INPUT_POST, 'postdelURL', FILTER_SANITIZE_URL);
      $postArr = filter_input(INPUT_POST, 'postArr', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
  
      if (isset($postURL) && !empty($postURL)) {
  
          $url_components = parse_url($postURL);
          parse_str($url_components['query'], $params);
          $postid = $params['post'];
  
          delete_frontend_document_attachments_id($postid);
  
      } else {
  
          foreach ($postArr as $postid) {
              delete_frontend_document_attachments_id($postid);
          }
  
      }
  
      die();
}
  
add_action('wp_ajax_delete_frontend_document_attachments', 'delete_frontend_document_attachments');
  
function delete_frontend_document_attachments_id($document_id) {
  
      if(!empty($document_id)) {
  
          $documentFileURL = get_post_meta($document_id, 'frontend_document_file', true);
  
          if( !empty($documentFileURL) ) {
              $documentFileID = attachment_url_to_postid( $documentFileURL );
              wp_delete_attachment( $documentFileID, true );
          }
  
      }
  
      return;
  
}