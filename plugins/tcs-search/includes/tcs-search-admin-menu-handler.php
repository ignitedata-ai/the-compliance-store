<?php

/*
 * Creates/adds the other admin menu page links to Settings menu
 */
function tcs_search_handle_admin_menu() {
//*****  Create the 'logs' and 'settings' submenu pages

    add_submenu_page(
        'options-general.php',
        __( 'Extended Search', 'tcs-search' ),
        __( 'Extended Search', 'tcs-search' ),
        'manage_options',
        'extended-search',
        'extended_search_page'
    );

}

add_action( 'admin_menu', 'tcs_search_handle_admin_menu' );

/*
 * * View Extended Search page
 */
function extended_search_page(){
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this settings page.');
    }

    echo '<div class="wrap">';

    tcs_handle_search_main_tab_page();
            
    
    echo '</div>';//<!-- end of wrap -->
}