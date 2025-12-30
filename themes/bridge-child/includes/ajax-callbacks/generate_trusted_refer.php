<?php

function generate_trusted_refer() {

    $site_url = site_url();

    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    $random = implode($pass); //turn the array into a string
    $link = $site_url . '?refferer=' . $random;

    echo $link;
    die;
}

add_action('wp_ajax_generate_trusted_refer', 'generate_trusted_refer');
