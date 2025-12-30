<?php

function get_ajax_documents() {

    $panelClass = '';
    $current_user_id = get_current_user_id();

    $taxonomy_order = isset($_REQUEST['order']) ? strtolower($_REQUEST['order']) : '';
    $taxonomy_orderby = isset($_REQUEST['orderby']) ? strtolower($_REQUEST['orderby']) : '';
    $cptName = isset($_REQUEST['cptName']) ? strtolower($_REQUEST['cptName']) : '';
    $taxonomySlug = isset($_REQUEST['taxonomySlug']) ? strtolower($_REQUEST['taxonomySlug']) : '';
    $taxonomy_term = isset($_REQUEST['termid']) ? $_REQUEST['termid'] : '';

    if (!empty($taxonomy_order) && !empty($taxonomy_orderby)) {
        $orderby = $taxonomy_orderby;
        $order = $taxonomy_order;
//        echo $taxonomy_order . 'This is tesitng' . $taxonomy_orderby;
//        die();
    } else {
        $term_order = get_term_meta($_REQUEST['termid'], 'documents_category_order_documents', true);
        if ($term_order == 'alphabatically') {
            $orderby = array('title' => 'ASC');
        } elseif ($term_order == 'date') {
            $orderby = array('date' => 'DESC');
        } elseif ($term_order == 'menu_order') {
            $orderby = array('menu_order' => 'ASC');
        } else {
            $orderby = array('menu_order' => 'ASC', 'date' => 'DESC');
        }
    }

    if($cptName == 'documents') {
        $panelClass = 'documents-panel';
    } else {
        $panelClass = 'frontend-docs-panel';

        // companies

        $corporate_companies = get_user_meta($current_user_id, 'corporate_companies', true );
        $admin_companies = get_user_meta($current_user_id, 'admin_companies', true );

        if(!empty($admin_companies)) {
            $company_assigned_id = $admin_companies;
        }

        if(!empty($corporate_companies)) {
            $company_assigned_id = $corporate_companies;
        }

    }


// Query Arguments
    $args = array(
        'post_type' => $cptName,
        'post_status' => 'publish',
        'posts_per_page' => 25,
        /*        'nopaging' => true, */
        'orderby' => $orderby,
        'order' => $order,
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomySlug,
                'terms' => $taxonomy_term,
                'field' => 'id',
                'include_children' => true,
                'operator' => 'IN'
            )
        ),
    );

    if(!current_user_can( 'manage_options' ) && $cptName == 'frontend_documents') {
        $args['meta_query'] = array(
            array(
                'key'     => 'frontend_document_author_company',
                'value'   => array( $company_assigned_id ),
                'compare' => 'IN',
            )
        );
    }

// The Query
    $ajaxposts = new WP_Query($args);

    $response = $path = '';

    $trail = get_term_parents_list($taxonomy_term, $taxonomySlug, array('inclusive' => false, 'link' => false, 'separator' => ' / '));
    $TermObject = get_term_by('id', $taxonomy_term, $taxonomySlug);
    $response .= '<h2>';
    if (empty($trail)) {
        $response .= $TermObject->name;
    } else {
        $response .= $trail . '<span class="tail">' . $TermObject->name . '</span>';
    }
    $response .= '</h2>';
    $response .= '<div class="panel-group" id="dococument-parent" role="tablist" aria-multiselectable="true">';
    $term_description = term_description($taxonomy_term, $taxonomySlug);
    if (!empty($term_description)) {
        $response .= '<div class="taxonomy-description">' . $term_description . '</div>';
    }
    $response .= '<div class="row" style="margin:0; padding-bottom: 40px;">
                    <div class="col-12 tcs-sort-container">
                        <span class="custom-search">
                                <a href="#" class="tcs-search-sort tcs-sort-cat" data-order="' . $taxonomy_order . '" data-id="' . $taxonomy_term . '">
                                <i class="fa fa-sort fa-2x" aria-hidden="true"></i>
                            </a>
                        </span>

                        <label class="tcs-search-sort">
                            Sort By:
                            <select name="orderby" id="orderby" class="search-field">
                                <option value="date" ';
    if ($taxonomy_orderby == 'date') {
        $response .= 'selected';
    }
    $response .= '>Date Posted</option>
                                                            <option value="title" ';
    if ($taxonomy_orderby == 'title') {
        $response .= 'selected';
    }
    $response .= '>Document Title</option>
                            </select>
                        </label>
                    </div>
                </div>';
// The Query
    if ($ajaxposts->have_posts()) {
        while ($ajaxposts->have_posts()) {
            $ajaxposts->the_post();

            if($cptName == 'documents') {
                $document = get_post_meta(get_the_ID(), 'bridge_document_document_file', true);
                $download = get_post_meta(get_the_ID(), 'bridge_document_document_download', true);
                $youtube_video = esc_url(get_post_meta(get_the_ID(), 'bridge_document_document_youtube', true));
                $gdrive = esc_url(get_post_meta(get_the_ID(), 'bridge_document_document_gdrive', true));
            } else {
                $document = get_post_meta(get_the_ID(), 'frontend_document_file', true);
                $download = get_post_meta(get_the_ID(), 'frontend_document_file', true);
                $youtube_video = '';
                $gdrive = '';
            }

            if ($cptName == 'documents') {

                $response .= '<div id="' . get_the_ID() . '" class="panel panel-default">
                                <div class="panel-heading" role="tab" id="heading-' . get_the_ID() . '">
                                <h4 class="panel-title">
                                    <a class="tab-title view-count" role="button" data-toggle="collapse" data-parent="#dococument-parent" href="#collapse-' . get_the_ID() . '">' . get_the_date('m/d/Y') . ' - ' . get_the_title() . '</a>';
                if (!empty($document)) {
                    if (!empty($download)) :
                        $path = $download;
                    else :
                        $path = $document;
                    endif;
                    $response .= '<a class="download-doc" id="' . get_the_ID() . '" rel="nofollow" href="' . $path . '" download>Download</a>';
                }
                $response .= '</h4>
                        </div>
                        <div id="collapse-' . get_the_ID() . '" class="panel-collapse collapse '. $panelClass .'" role="tabpanel" aria-labelledby="heading-' . get_the_ID() . '">
                            <div class="panel-body">' . get_the_content();
                $doc_content = '';
                if (!empty($document)) {
                    $doc_content = do_shortcode('[pdf-embedder url="' . $document . '"]');
                } elseif (!empty($youtube_video)) {
                    $doc_content = '<div class="video-container">' . wp_oembed_get($youtube_video) . '</div>';
                } elseif (!empty($gdrive)) {
                    $doc_content = '<div class="video-container"><iframe src="' . $gdrive . '"></iframe></div>';
                }
                $response .= $doc_content;
                $response .= '</div>
                        </div>
                </div>';

            } else {

                $response .= '<div class="panel panel-default">
                            <div class="panel-heading" role="tab" id="heading-' . get_the_ID() . '">
                              <h4 class="panel-title">
                                <span class="tab-title view-count">' . get_the_date('m/d/Y') . ' - ' . get_the_title() . '</span>';
                if (!empty($document)) {
                    if (!empty($download)) :
                        $path = $download;
                    else :
                        $path = $document;
                    endif;
                    $response .= '<a class="download-doc" id="' . get_the_ID() . '" rel="nofollow" href="' . $path . '" download>Download</a>';
                }
                $response .= '</h4>
                        </div>
                </div>';

            }
        }
        $big = 999999999; // need an unlikely integer
        $total_pages = $ajaxposts->max_num_pages;
        if ($total_pages > 1) {
            $response .= '<div class="wp-pagenavi ajax-pagenavi" role="navigation">';
            $response .= '<div hidden id="term-id-pagnav">' . $taxonomy_term . '</div>';
            $response .= '<span class="pages">Showing 1 of ' . $total_pages . '</span>';
            $response .= paginate_links(array(
                'base' => str_replace($big, '%#%', get_pagenum_link($big)),
                'format' => '?paged=%#%',
                //'current' => max( 1, get_query_var('paged') ),
                'total' => $total_pages,
                'prev_text' => __('Previous'),
                'next_text' => __('Next'),
            ));
            $response .= "</div>";
        }
        wp_reset_query();
        $response .= "</div>";
    } else {
        $response .= '<h3 class="not-found">No documents yet.</h3>';
    }
    echo $response;

    die;
}

// Fire AJAX action for both logged in and non-logged in users
add_action('wp_ajax_get_ajax_documents', 'get_ajax_documents');
/* add_action('wp_ajax_nopriv_get_ajax_documents', 'get_ajax_documents'); */