<?php

function set_security_question() {

    parse_str($_POST['form_values'], $params);

    update_user_meta($params['user_id'], 'security_question', $params['security_question']);
    update_user_meta($params['user_id'], 'security_question_answer', $params['security_question_answer']);

    $status = 'success';

    echo $status;
    die;
}

add_action('wp_ajax_set_security_question', 'set_security_question');
