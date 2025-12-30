<?php

function delete_document_attachments_bypostid($postid = NULL) {
    if ($postid) {
        $file1 = get_post_meta($postid, 'bridge_document_document_file', true);
        if ($file1) {
            $attachid = attachment_url_to_postid($file1);
            if ($attachid == 0) {
                $wordpress_upload_dir = wp_upload_dir();
                $attachurl = str_replace($wordpress_upload_dir['baseurl'], $wordpress_upload_dir['basedir'], str_replace('https://', 'http://', $file1));
                wp_delete_file($attachurl);
            } else {
                wp_delete_attachment($attachid, true);
            }
        }

        $file2 = get_post_meta($postid, 'bridge_document_document_download', true);
        if ($file2) {
            $attachid = attachment_url_to_postid($file2);
            if ($attachid == 0) {
                $wordpress_upload_dir = wp_upload_dir();
                $attachurl = str_replace($wordpress_upload_dir['baseurl'], $wordpress_upload_dir['basedir'], str_replace('https://', 'http://', $file2));
                wp_delete_file($attachurl);
            } else {
                wp_delete_attachment($attachid, true);
            }
        }
        return;
    } else {
        return;
    }
}

function delete_document_attachments() {

    $postURL = filter_input(INPUT_POST, 'postdelURL', FILTER_SANITIZE_URL);
    $postArr = filter_input(INPUT_POST, 'postArr', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);

    if (isset($postURL) && !empty($postURL)) {

        $url_components = parse_url($postURL);
        parse_str($url_components['query'], $params);
        $postid = $params['post'];

        delete_document_attachments_bypostid($postid);
    } else {
        foreach ($postArr as $postid) {
            delete_document_attachments_bypostid($postid);
        }
    }
    die();
}

// Fire AJAX action for both logged in and non-logged in users
add_action('wp_ajax_delete_document_attachments', 'delete_document_attachments');
