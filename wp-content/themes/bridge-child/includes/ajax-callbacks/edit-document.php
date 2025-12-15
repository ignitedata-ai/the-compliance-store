<?php

function get_document_data_using_id() {

      $document_id = $_POST['document_id'];
      $return_json = array();

      $documentData = get_post( $document_id );

      // Get the product categories terms ids in the product
      $terms_ids = wp_get_post_terms( $documentData->ID, 'frontend_documents_category', array('fields' => 'slugs', 'order'   =>  'DESC') );
      $categories = $terms_ids;

      $row = array(
            'document_name' => $documentData->post_title,
            'categories' => $categories,
            'pdf_document'    =>    get_post_meta($documentData->ID, 'bridge_document_document_file', true),
            'upload_document'    =>    get_post_meta($documentData->ID, 'bridge_document_document_download', true),
            'youtube_link'    =>    get_post_meta($documentData->ID, 'bridge_document_document_youtube', true),
            'google_drive_link'    =>    get_post_meta($documentData->ID, 'bridge_document_document_gdrive', true)
      );
      $return_json = $row;

      //return the result to the ajax request and die
      echo json_encode(array(
            'data' => $return_json
      ));

      wp_die();

}

add_action( 'wp_ajax_get_document_data_using_id', 'get_document_data_using_id' );