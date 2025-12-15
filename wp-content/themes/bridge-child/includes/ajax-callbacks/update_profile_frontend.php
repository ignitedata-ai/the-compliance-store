<?php

function update_profile_frontend() {
    parse_str($_POST['profile_form_values'], $params);

    // Check if the username already exists
    $args = array(  
        'meta_key'     => 'nickname',
        'meta_value'   => esc_attr($params['nickname']),
    );
    $getUsers = get_users( $args );
    if (!empty($getUsers)) {
        echo json_encode([
            'response' => 'username_exists'
        ]);
        die;
    }

    /* Update user information. */

    if (!empty($params['first-name'])) {
        update_user_meta($params['user_id'], 'first_name', esc_attr($params['first-name']));
        update_user_meta($params['user_id'], 'last_name', esc_attr($params['last-name']));
        update_user_meta($params['user_id'], 'street_1', esc_attr($params['street_1']));
        update_user_meta($params['user_id'], 'street_2', esc_attr($params['street_2']));
        update_user_meta($params['user_id'], 'city', esc_attr($params['city']));
        update_user_meta($params['user_id'], 'state', esc_attr($params['state']));
        update_user_meta($params['user_id'], 'zip_code', esc_attr($params['zip_code']));
        update_user_meta($params['user_id'], 'work_phone', esc_attr($params['work_phone']));
        update_user_meta($params['user_id'], 'company', esc_attr($params['company']));
        update_user_meta($params['user_id'], 'nickname', esc_attr($params['nickname']));
        update_user_meta($params['user_id'], 'display-name', esc_attr($params['nickname']));

        echo json_encode([
            'first_name' => $params['first-name'],
            'last_name' => $params['last-name'],
            'street_1' => $params['street_1'],
            'street_2' => $params['street_2'],
            'city' => $params['city'],
            'state' => $params['state'],
            'zip_code' => $params['zip_code'],
            'work_phone' => $params['work_phone'],
            'state' => $params['state'],
            'company' => $params['company'],
            'nickname' => $params['nickname'],
            'response' => 'success'
        ]);
    }
    die;
}

add_action('wp_ajax_update_profile_frontend', 'update_profile_frontend');
