<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function get_popular_documents() {
    global $wpdb;
    $current_user = wp_get_current_user();
    
    $tools_templates_access = get_user_meta($current_user->ID, 'tools_templates_access', true);
    $policies_procedures_access = get_user_meta($current_user->ID, 'policies_procedures_access', true );
    
    $start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';
    $end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';
    if (isset($_REQUEST['start_date']) && isset($_REQUEST['end_date'])) {

        $q = $wpdb->prepare("SELECT *, COUNT(*) AS count FROM " . $wpdb->prefix . "reports_downloads
            WHERE DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')>=%s
            AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')<=%s
            GROUP BY post_id ORDER BY count DESC LIMIT 10", $start_date, $end_date);
        $res = $wpdb->get_results($q, OBJECT);

        foreach ($res as $result) {
            $document = get_post_meta($result->post_id, 'bridge_document_document_file', true);
            $download = get_post_meta($result->post_id, 'bridge_document_document_download', true);
            $youtube_video = esc_url(get_post_meta($result->post_id, 'bridge_document_document_youtube', true));
            $gdrive = esc_url(get_post_meta($result->post_id, 'bridge_document_document_gdrive', true));
            $terms = get_the_terms($result->post_id, 'documents_category');

            $terms = get_the_terms($result->post_id, 'documents_category');
            
            if ($terms && !is_wp_error($terms)) {
                $term_ids_array = array();
                foreach ($terms as $term) {
                    $term_tools_templates_access = get_term_meta($term->term_id, 'documents_category_users_tools_templates_access', true);
                    if ($tools_templates_access == 'disable' && $term_tools_templates_access == 'disable') {
                        $disabled_array[] = $result->post_id;
                    }
                    
                    $term_policies_procedures_access = get_term_meta( $term->term_id, 'documents_category_users_policies_procedures_access', true);
                    if ($policies_procedures_access == 'disable' && $term_policies_procedures_access == 'disable') {
                        $disabled_array[] = $result->post_id;
                    }
                }
            }
            $response .= '<article id="post-' . $result->post_id . '" class="panel panel-default">';
            
            if ( in_array($result->post_id, $disabled_array) ) {
                $response .= '<div class="panel-heading" role="tab" style="cursor: not-allowed;" id="heading-' . $result->post_id . '">
				                    <h4 class="panel-title">' . get_the_date('m/d/Y', $result->post_id) . ' - ' . get_the_title($result->post_id) . '</h4>
                				</div>';
            } else {
                $response .= '<div class="panel-heading" role="tab" id="heading-' . $result->post_id . '">
				                    <h4 class="panel-title">
				                        <a class="tab-title" role="button" data-toggle="collapse" data-parent="#popular-documents" href="#collapse-' . $result->post_id . '" aria-expanded="true" aria-controls="collapse-' . $result->post_id . '">' . get_the_date('m/d/Y', $result->post_id) . ' - ' . get_the_title($result->post_id) . '</a>';
                if (!empty($document)) {
                    if (!empty($download)) :
                        $path = $download;
                    else :
                        $path = $document;
                    endif;
                    $response .= '<a class="download-doc" id="' . $result->post_id . '" rel="nofollow" href="' . $path . '" download>Download</a>';
                }
                $response .= '</h4>
                				</div>';
                $response .= '<div id="collapse-' . $result->post_id . '" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-' . $result->post_id . '">
	        				<div class="panel-body">';
                if (!empty($document)) {
                    $doc_content = do_shortcode('[pdf-embedder url="' . $document . '"]');
                } elseif (!empty($youtube_video)) {
                    $doc_content = '<div class="video-container">' . wp_oembed_get($youtube_video) . '</div>';
                } elseif (!empty($gdrive)) {
                    $doc_content = '<div class="video-container"><iframe src="' . $gdrive . '"></iframe></div>';
                }
                $response .= $doc_content;
                $response .= '</div>
	    				</div>';
            }
            $response .= '</article>';
        }
    }
    echo $response;

    die; // leave ajax call
}

// Fire AJAX action for both logged in and non-logged in users
add_action('wp_ajax_get_popular_documents', 'get_popular_documents');
