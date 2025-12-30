<?php

function reset_user_password() {

    parse_str($_POST['formData'], $params);

    $user = check_password_reset_key($params['userkey'], $params['user_login']);

    $status = '';

// Check if key is valid
    if (is_wp_error($user)) {
        if ($user->get_error_code() === 'expired_key') {
            $status = 'expiredkey';
        } else {
            $status = 'invalidkey';
        }

        echo $status;
        die;
    }

// check if keys match
    if (isset($params['user_password']) && $params['user_password'] != $params['user_password1']) {
        $status = 'mismatch';
    } else {
// Update the user pass
        reset_password($user, $params['user_password']);

        $status = 'success';
    }

    echo $status;
    die;
}

add_action('wp_ajax_reset_user_password', 'reset_user_password');