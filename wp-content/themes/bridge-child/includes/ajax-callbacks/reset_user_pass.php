<?php

function reset_user_pass() {

    $user_id = get_current_user_id();
    parse_str($_POST['form_values'], $params);

    // $user = check_password_reset_key($params['key'], $params['login']);
    $security_ques_ans = get_user_meta($params['user_id'], 'security_question_answer', true);

    $status = '';

// Check if key is valid
    // if (is_wp_error($user)) {
    //     if ($user->get_error_code() === 'expired_key') {
    //         $status = 'expiredkey';
    //     } else {
    //         $status = 'invalidkey';
    //     }

    //     echo $status;
    //     die;
    // }

// check if keys match
    if (isset($params['passreset1']) && $params['passreset1'] != $params['passreset2']) {
        $status = 'mismatch';
    } elseif ($security_ques_ans !== $params['security_question_answer']) {
        $status = 'wronganswer';
    } else {
// Update the user pass
        // reset_password($user, $params['passreset1']);
        wp_set_password( $params['passreset1'], $user_id );

        $status = 'success';
    }

    echo $status;
    die;
}

add_action('wp_ajax_reset_user_pass', 'reset_user_pass');