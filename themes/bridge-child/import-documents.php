<?php
/**
 * Template Name: Import Documents
 */
//get_header();
/*Get all documents posts*/
/*global $post,$wpdb;

$args = array( 
    'role'      => 'subscriber_-_facility_user',
    'fields'    => array( 'ID','user_email' ),
    'offset'    => 9000,
    'number'    => 2000,
);
$users = get_users($args);
foreach ($users as $user) {
    echo $user->ID.'<br>';
    
    $nickname = explode('@',$user->user_email,);
    $nickname = $nickname[0];
    update_user_meta($user->ID, 'nickname', $nickname);
    $return = $wpdb->update(
        $wpdb->prefix.'users',
        array( 'display_name' => $nickname ),
        array( 'ID' => $user->ID ),
    );
}*/
/*$downloads_table_name = $wpdb->prefix . 'reports_downloads';
$views_table_name = $wpdb->prefix . 'reports_views';
$activity_table_name = $wpdb->prefix . 'reports_activity';

$user_ids = $wpdb->get_results("SELECT `ID` FROM `wp_jw5prm2trf_users`");


$max = 10000;*/
/*echo count($user_ids);*/
/*for ($i=9500; $i < count($user_ids) ; $i++) {*/
/*for ($i=7500; $i < $max ; $i++) {*/

/*    $data_results = $wpdb->get_results("SELECT * FROM $activity_table_name WHERE user_id='".$user_ids[$i]->ID."'");

    if(count($data_results) === 0) {
        $views_count = $wpdb->get_results("SELECT count(*) AS views_count FROM $views_table_name WHERE `user_id` = '".$user_ids[$i]->ID."'");
        $downloads_count = $wpdb->get_results("SELECT count(*) AS download_counts FROM $downloads_table_name WHERE  `user_id` ='".$user_ids[$i]->ID."'");

        $total_sum = $downloads_count[0]->download_counts+$views_count[0]->views_count;

        if ($total_sum !==0) {
            $activity_data = array(
                'user_id' => $user_ids[$i]->ID,
                'views_count' => $views_count[0]->views_count,
                'downloads_count' => $downloads_count[0]->download_counts,
                'activity_count' => $total_sum
            );
            $activity_data = array_filter($activity_data); //Remove any null values.
            $insert_activity_table = $wpdb->insert($activity_table_name, $activity_data);
            if ($insert_activity_table) {
                echo 'successfully logged a new '.$user_ids[$i]->ID.' to activity table<br>';
            } else {
                echo 'failed to logged a new '.$user_ids[$i]->ID.' to activity table<br>';
            }
        } else {
            echo $user_ids[$i]->ID.' sum is zero<br>';
        }
    } else {
        echo $user_ids[$i]->ID.' User already exist<br>';
    }
}*/
// foreach ($user_ids as $user_id) {
//     $data_results = $wpdb->get_results("SELECT * FROM $activity_table_name WHERE user_id='$user_id->ID'");

//     if(count($data_results) === 0) {
//         $views_count = $wpdb->get_results("SELECT count(*) AS views_count FROM $views_table_name WHERE `user_id` = '$user_id->ID'");
//         $downloads_count = $wpdb->get_results("SELECT count(*) AS download_counts FROM $downloads_table_name WHERE `user_id` = '$user_id->ID'");

//         $total_sum = $downloads_count[0]->download_counts+$views_count[0]->views_count;
//         if ($total_sum !==0) {
//             $activity_data = array(
//                 'user_id' => $user_id->ID,
//                 'views_count' => $views_count[0]->views_count,
//                 'downloads_count' => $downloads_count[0]->download_counts,
//                 'activity_count' => $total_sum
//             );
//             $activity_data = array_filter($activity_data); //Remove any null values.
//             $insert_activity_table = $wpdb->insert($activity_table_name, $activity_data);
//             if ($insert_activity_table) {
//                 echo 'successfully logged a new '.$user_id->ID.' to activity table<br>';
//             } else {
//                 echo 'failed to logged a new '.$user_id->ID.' to activity table<br>';
//             }
//         }
//     }

// $i++;
// if($i==3500) break;
// }

exit();

/*$main_query = "SELECT users.id as user_id,
(SELECT count(*) FROM $views_table_name as `views` WHERE `views`.user_id = users.id) as `views_count`,
(SELECT count(*) FROM $downloads_table_name as `downloads` WHERE `downloads`.user_id = users.id) as `downloads_count`
FROM wp_jw5prm2trf_users as `users` HAVING views_count+downloads_count <> 0 limit 600";
$main_query_results = $wpdb->get_results($main_query);


$i=0;
foreach ($main_query_results as $data) {
    $result = "SELECT user_id FROM $activity_table_name WHERE user_id='$data->user_id'";
    $result_user_id = $wpdb->get_results($result);
    if(count($result_user_id) !== 0) {
        echo $data->user_id. "User exists <br>";        
    } else {
        echo $data->user_id . "User doesn't exists<br>";
        $activity_count = $data->views_count + $data->downloads_count;
        $data = array(
            'user_id' =>  $data->user_id,
            'views_count' => $data->views_count,
            'downloads_count' => $data->downloads_count,
            'activity_count' => $activity_count,
        );

        $data = array_filter($data); //Remove any null values.
        $insert_table = $wpdb->insert($activity_table_name, $data);
    }
$i++;
if($i==200) break;
}
exit();*/

// Remove a capability from a specific user.
/*$user_id = '10541';// The ID of the user to remove the capability from.
$data = get_userdata( $user_id );
 
if ( is_object( $data) ) {
    $current_user_caps = $data->allcaps;
     
    // print it to the screen
    echo '<pre>' . print_r( $current_user_caps, true ) . '</pre>';
}

$user = new WP_User( $user_id );
$user->remove_cap( ' UserAdministrator' );
*/
/*$URL='https://www.thecompliancestore.net/secure/wp-content/uploads/sites/2/documents/2018/04/CCJR-Policy-Changes.pdf';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$URL);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
//curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
$login_cookie = 'wordpress_logged_in'; // This cookie can be found once a user is logged in.
curl_setopt( $ch, CURLOPT_COOKIE, $login_cookie );
$result = curl_exec ($ch);
echo $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
curl_close ($ch);*/

/*$loop15465 = new WP_Query( array( 'post_type' => 'documents', 'post_status' => 'publish', 'posts_per_page' => -1) );
if ( $loop15465->have_posts() ) :
    while ( $loop15465->have_posts() ) : $loop15465->the_post();

        //$title = get_the_title();
        $id_store[] = basename(get_permalink());
        //$id_store[] = get_post_meta( get_the_ID(), 'bridge_document_document_file', true );
    endwhile;
endif;
wp_reset_postdata();
$posts = $terms = array();

$querystr = "SELECT * FROM `wp_jw5prm2trf_2_posts` p JOIN `wp_jw5prm2trf_2_term_relationships` tr ON (p.ID = tr.object_id) JOIN `wp_jw5prm2trf_2_term_taxonomy` tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) JOIN `wp_jw5prm2trf_2_terms` t ON (tt.term_id = t.term_id) WHERE p.post_type='attachment' AND p.post_mime_type = 'application/pdf' AND tt.taxonomy = 'attachment_category' AND t.term_id = '50'";
$attachments = $wpdb->get_results($querystr);

foreach ($attachments as $attachment) {
    $meta =  get_post_meta( $attachment->ID, '_wp_attached_file', true );
    $myterms = wp_get_post_terms( $attachment->ID, 'attachment_category' );
    $terms = array_column($myterms, 'slug');

    $posts[] = array(
            'post_slug' => $attachment->post_name,
            'post_title' => "{imported} ".$attachment->post_title,
            'post_date' => $attachment->post_date,
            'post_author' => $attachment->post_author,
            'file' => 'https://compliancestor.staging.wpengine.com/secure/wp-content/uploads/sites/2/documents/'.$meta,
            'term'=> $terms,
        );
}
$i=0;*/
/*foreach ($posts as $post) {
    $slug = "imported-".$post['post_slug'];
    if (!in_array($slug, $id_store)) {
        echo $post['post_title']."  Imported";
        echo "<br>";
    	$post_arr = array(
    	    'post_title'   => $post['post_title'],
    	    'post_type' => 'documents',
    	    'post_status'  => 'publish',
    	    'post_author'  => $post['post_author'],
    	    'post_date' => $post['post_date'],
    	    'meta_input'   => array(
    	        //'bridge_document_document_file' => $post['file'],
                'bridge_document_document_download' => $post['file'],
    	    ),
    	);
    	$postinserted = wp_insert_post( $post_arr );
        wp_set_object_terms( $postinserted, $post['term'], 'documents_category' );
    }
$i++;
if($i==380) break;
}*/
//wp_footer();