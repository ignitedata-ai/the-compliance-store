<?php
/**
 * Get remote IP address.
 * @link http://stackoverflow.com/questions/1634782/what-is-the-most-accurate-way-to-retrieve-a-users-correct-ip-address-in-php
 *
 * @param bool $ignore_private_and_reserved Ignore IPs that fall into private or reserved IP ranges.
 * @return mixed IP address as a string or null, if remote IP address cannot be determined (or is ignored).
 */
function reports_get_ip_address($ignore_private_and_reserved = false) {
    $flags = $ignore_private_and_reserved ? (FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) : 0;
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip); // just to be safe

                if (filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false) {
                    return $ip;
                }
            }
        }
    }
    return null;
}

/*
 * Checks if the string exists in the array key value of the provided array. If it doesn't exist, it returns the first key element from the valid values.
 */

function reports_sanitize_value_by_array($to_check, $valid_values) {
    $keys = array_keys($valid_values);
    $keys = array_map('strtolower', $keys);
    if (in_array($to_check, $keys)) {
        return $to_check;
    }
    return reset($keys); //Return the first element from the valid values
}