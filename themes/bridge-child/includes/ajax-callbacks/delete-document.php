<?php

/**
 * Move post to trash
 */

 function delete_document_using_id() {
      $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
  
      if ($document_id) {
          $documentFileURL = get_post_meta($document_id, 'frontend_document_file', true);
  
          if (!empty($documentFileURL)) {
              $documentFileID = attachment_url_to_postid($documentFileURL);
          }
  
          $is_log_recorded = log_deleted_documents($document_id);
  
          if ($is_log_recorded) {
              $is_document_deleted = wp_delete_post($document_id, true);
          }
  
          $response = array(
              'document_data' => $is_document_deleted,
              'document_attachment' => isset($is_document_deleted) && !empty($documentFileID) ? wp_delete_attachment($documentFileID) : false
          );
  
          echo json_encode($response);
      } else {
          echo json_encode(array('error' => 'Invalid document ID'));
      }
  
      wp_die();
  }

add_action( 'wp_ajax_delete_document_using_id', 'delete_document_using_id' );

/**
 * Add trashed document inside the database table for logs
 */

 function log_deleted_documents($documentID) {

      global $wpdb;
      $date_time = current_time('mysql');
      $current_user = wp_get_current_user();
      $visitor_id = $current_user->ID;

      $response = false;
      
      $table = $wpdb->prefix . 'reports_deleted_documents';
      $data = array(
            'post_id' => $documentID,
            'post_title' => get_the_title($documentID),
            'user_id' => $visitor_id,
            'date_time' => $date_time
      );

      $data = array_filter($data); //Remove any null values.
      $is_successfully_inserted = $wpdb->insert($table, $data);

      if ($is_successfully_inserted) {
            $response = true;
      } else {
            $response = false;
      }

      return $response;

 }