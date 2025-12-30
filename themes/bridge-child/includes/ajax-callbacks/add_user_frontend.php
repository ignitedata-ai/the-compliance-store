<?php

function add_user_frontend() {
    parse_str($_POST['add_user_form_values'], $params);
    $email = $user_name = $params['add-email'];

//check if the email is not in use yet
    if (!email_exists($email)) {
        /* Add user information. */
//$random_password = wp_generate_password(16);
        $random_password = 'password';
//create the user 
        $user_id = wp_create_user($user_name, $random_password, $email);
        if (!is_wp_error($user_id)) {

//Not required: update user with details, role, firstname, lastname. 
            $userdata = array(
                "ID" => $user_id,
                "first_name" => $params['add-first-name'],
                "last_name" => $params['add-last-name'],
                "role" => "subscriber_-_facility_user",
            );
            $user_id = wp_update_user($userdata);

//send password reset to user. 
            update_user_meta($user_id, 'street_1', esc_attr($params['add-street-1']));
            update_user_meta($user_id, 'street_2', esc_attr($params['add-street-2']));
            update_user_meta($user_id, 'city', esc_attr($params['add-city']));
            update_user_meta($user_id, 'state', esc_attr($params['add-state']));
            update_user_meta($user_id, 'zip_code', esc_attr($params['add-zip-code']));
            update_user_meta($user_id, 'work_phone', esc_attr($params['add-work-phone']));
            update_user_meta($user_id, 'tools_templates_access', $params['add-tools-templates-access']);
            update_user_meta($user_id, 'login_amount', '1');
            $get_admin_user = get_user_by('id', $params['add_user_id']);
            $admin_user_email = $get_admin_user->user_email;
            update_user_meta($user_id, 'admin_facility', $admin_user_email);
//pt_send_password_reset_mail($user_id);
            /* $user = get_user_by('id', $user_id);
              $firstname = $user->first_name;
              $email = $user->user_email;
              $key = get_password_reset_key( $user );
              $user_login = $user->user_login; */
            if (is_wp_error($key)) {
                $status = 'invalidkey';
            }

            /* $rp_link =  network_site_url()."wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login);


              if ($firstname == "") $firstname = "User";
              $message = "Hi ".$firstname.",<br>";
              $message .= "An account has been created on ".get_bloginfo( 'name' )." for email address ".$email."<br>";
              $message .= "Click here to set the password for your account: <br>";
              $message .= '<a href="'.$rp_link.'">'.$rp_link."</a>\r\n";
              $subject = __("Your account on ".get_bloginfo( 'name'));
              $headers = array();
              add_filter( 'wp_mail_content_type', function( $content_type ) {return 'text/html';});
              $headers[] = 'From: The Compliance Store <customerservice@thecompliancestore.com>'."\r\n";
              wp_mail( $email, $subject, $message, $headers);

              remove_filter( 'wp_mail_content_type', 'set_html_content_type' ); */

            $status = 'success';
        } else {
            $status = 'somethingwrong';
        }
    } else {
        $status = 'emailexists';
    }
    echo $status;
    die;
}

add_action('wp_ajax_add_user_frontend', 'add_user_frontend');