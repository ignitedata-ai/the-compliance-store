<?php

function get_documents_from_db() {

      $request = $_POST;
      $return_json = array();
      $company_assigned_id = '';
      $delete_document_ID = '';
      $document_attachment = '';

      $current_user_id = get_current_user_id();

      // companies

      $corporate_companies = get_user_meta($current_user_id, 'corporate_companies', true );
      $admin_companies = get_user_meta($current_user_id, 'admin_companies', true );

      if(!empty($admin_companies)) {
            $company_assigned_id = $admin_companies;
      }

      if(!empty($corporate_companies)) {
            $company_assigned_id = $corporate_companies;
      }
      
      $args = array(
            'post_type'   => 'frontend_documents',
            'post_status'   => 'publish',
            'posts_per_page' => $request['length'],
            'offset' => $request['start'],
            'order' => $request['order'][0]['dir'],
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

      // title

      if($request['order'][0]['column'] == '0') {
            $args['orderby'] = 'post_title';
      }

      // date

      if($request['order'][0]['column'] == '4') {
            $args['orderby'] = 'date';
      }

      // When datatables search is used

      if( !empty($request['search']['value']) ) {
            $args['s'] = $request['search']['value'];
      }
      
      $postsQ = new WP_Query( $args );
      $totalData = $postsQ->found_posts;

      if($postsQ->have_posts()) {

            while($postsQ->have_posts()) {
      
                  $postsQ->the_post();
                  
                  // Get the product categories terms ids in the product
                  $terms_ids = wp_get_post_terms( get_the_ID(), 'frontend_documents_category', array('fields' => 'names', 'order'   =>  'ASC') );
                  $categories = implode( ', ' , $terms_ids );

                  // delete documents

                  if(get_the_author_meta( 'ID' ) == $current_user_id || current_user_can( 'manage_options' )) {
                        $delete_document_ID = get_the_ID();
                  } else {
                        $delete_document_ID = '';
                  }

                  // document attachment

                  if( !empty(get_post_meta(get_the_ID(), 'frontend_document_file', true)) ) {
                        $document_attachment = get_the_ID() . '|' . get_post_meta(get_the_ID(), 'frontend_document_file', true);
                  } else {
                        $document_attachment = '';
                  }
      
                  $row = array(
                        'document_name' => get_the_title(),
                        'categories' => $categories,
                        'upload_document'    =>    $document_attachment,
                        'author_name'    =>    get_the_author(),
                        'post_date'    =>    get_the_date('m-d-Y'), // 24 hours format
                        'delete_document'    =>    $delete_document_ID,
                  );
                  $return_json[] = $row;
            }

      }

      //return the result to the ajax request and die
      echo json_encode(array(
            'draw'      =>     intval($request['draw']),
            'recordsFiltered' =>     intval($totalData),
            'recordsTotal'    =>     intval($totalData),
            'data' => $return_json,
      ));

      wp_die();

}

add_action( 'wp_ajax_get_documents_from_db', 'get_documents_from_db' );