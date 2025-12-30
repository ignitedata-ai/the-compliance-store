<?php

if ( !defined('ABSPATH') ) {
    exit;
}

if ( !class_exists('PT_Filters_Users') ) {
    class PT_Filters_Users {
        
        public function __construct() {
            add_action( 'admin_menu', array($this, 'add_filters_user_page') );
            //add_action( 'admin_init', array($this, 'pt_user_filter_admin_init') );
        }
        
        /*public function pt_user_filter_admin_init() {
            add_action( 'admin_post_import_pt_users', array($this, 'import_user_records') );
        }*/
        
        public function add_filters_user_page() {
            add_submenu_page(
                'users.php',
                'Filter Users',
                'Filter Users',
                'manage_options',
                'pt-users-filter',
                array($this, 'filter_user_submenu_callback'));
        }
        
        public function filter_user_submenu_callback() { ?>
            <h3>Filter Users</h3>

            <?php
            if (isset($_GET["company_id"]) && !empty($_GET["company_id"])) {
                $get_company = get_term_by('id', $_GET["company_id"], 'company');
                $company_exists = term_exists( $get_company->term_id, 'company' );
                if ( 0 !== $company_exists && null !== $company_exists ) {
                    /*Company Exists*/
                    //var_dump($get_company);
                    echo '<p>Users under <strong>Company: '.$get_company->name.'</strong> are listed below:</p>';
                    $args = array(
                        //'role' => 'business',
                        'meta_key' => 'business_companies', 
                        'meta_compare' => '=',  
                        'meta_value' => $get_company->term_id
                    );
                }
            }
            elseif (isset($_GET["account-manager"]) && !empty($_GET["account-manager"])) {
                $user_detail_account_manager = get_user_by( 'email', $_GET["account-manager"] );
                if ($user_detail_account_manager) {
                    echo '<p>Users under <strong>Account Manager: '.$user_detail_account_manager->first_name.' '.$user_detail_account_manager->last_name.'</strong> are listed below:</p>';
                }
                $args = array( 
                    //'role' => 'subscriber_-_admin_facility',
                    'meta_key' => 'account_manager', 
                    'meta_compare' => '=',  
                    'meta_value' => $_GET["account-manager"]
                );
            }
            else {
                $args = array( // get all users where
                );

            }
            // The Query
            $user_query = new WP_User_Query( $args );
            echo '<table class="wp-list-table widefat fixed striped users">
                            <thead>
                                <tr>
                                    <th scope="col" class="manage-column column-name">Username</th>
                                    <th scope="col" class="manage-column column-name">Name</th>
                                    <th scope="col" class="manage-column column-name">Email</th>
                                    <th scope="col" class="manage-column column-role">Role</th>
                                </tr>
                            
                            </thead><tbody id="the-list" data-wp-lists="list:user">';

            // User Loop
            if ( !empty( $user_query->results ) ) {
                foreach ( $user_query->results as $user ) {
                    if (isset($_GET["corporate-facility"]) && !empty($_GET["corporate-facility"])) {
                        echo '<tr id="user-'.$user->ID.'" style="background: #eee;">';
                    } elseif (isset($_GET["business"]) && !empty($_GET["business"])) {
                        echo '<tr id="user-'.$user->ID.'" style="background: #eee;">';
                    }
                     else {
                        echo '<tr id="user-'.$user->ID.'">';
                    }
                    echo '<td class="username column-username has-row-actions column-primary" data-colname="Username">
                                <strong>'.$user->display_name.'</strong>
                            </td>
                            <td class="name column-name" data-colname="Name">'.$user->user_firstname.' '.$user->user_lastname.'</td>
                            <td class="email column-email" data-colname="Email">'.$user->user_email.'</td><td class="role column-role" data-colname="Role">';
                    foreach ($user->roles as $role) {
                        $role =  str_replace("_"," ",$role);
                        echo ucwords($role);
                    }
                    echo '</td>
                        </tr>';
                    if (isset($_GET["corporate-facility"]) && !empty($_GET["corporate-facility"])) {
                        $args_facility = array( // get all users where
                            'role' => 'subscriber_-_facility_user',
                            'meta_key' => 'admin_facility', // the key 
                            'meta_compare' => '=', // has a value that is equal to 
                            'meta_value' => $user->user_email
                        );
                        $user_query_args_facility = new WP_User_Query( $args_facility );
                        if ( !empty( $user_query_args_facility->results ) ) {
                            foreach ( $user_query_args_facility->results as $user_facility ) {
                                echo '<tr id="user-'.$user_facility->ID.'" style="background: inherit;">
                                        <td class="username column-username has-row-actions column-primary" data-colname="Username">
                                            <strong>— '.$user_facility->display_name.'</strong>
                                        </td>
                                        <td class="name column-name" data-colname="Name">'.$user_facility->user_firstname.' '.$user_facility->user_lastname.'</td>
                                        <td class="email column-email" data-colname="Email">'.$user_facility->user_email.'</td>
                                        <td class="role column-role" data-colname="Role">';
                                        foreach ($user_facility->roles as $role) {
                                            $role =  str_replace("_"," ",$role);
                                            echo ucwords($role);
                                        }
                                echo '</td>
                                        </tr>';
                            }
                        }
                        else {
                            echo '<tr>
                                <td class="username column-username column-primary" data-colname="Username">
                                        No Facility users found under Corporate Facility: '.$_GET["corporate-facility"].'
                                </td>
                            </tr>';
                        }
                    }
                    if (isset($_GET["business"]) && !empty($_GET["business"])) {
                        $args_facility = array( // get all users where
                            'role' => 'subscriber_-_admin_facility',
                            'meta_key' => 'corporate_facility', // the key
                            'meta_compare' => '=', // has a value that is equal to 
                            'meta_value' => $user->user_email
                        );
                        $user_query_args_facility = new WP_User_Query( $args_facility );
                        if ( !empty( $user_query_args_facility->results ) ) {
                            foreach ( $user_query_args_facility->results as $user_facility ) {
                                echo '<tr id="user-'.$user_facility->ID.'" style="background: inherit;">
                                        <td class="username column-username has-row-actions column-primary" data-colname="Username">
                                            <strong>— '.$user_facility->display_name.'</strong>
                                        </td>
                                        <td class="name column-name" data-colname="Name">'.$user_facility->user_firstname.' '.$user_facility->user_lastname.'</td>
                                        <td class="email column-email" data-colname="Email">'.$user_facility->user_email.'</td>
                                        <td class="role column-role" data-colname="Role">';
                                        foreach ($user_facility->roles as $role) {
                                            $role =  str_replace("_"," ",$role);
                                            echo ucwords($role);
                                        }
                                echo '</td>
                                        </tr>';
                                $args_users = array( // get all users where
                                    'role' => 'subscriber_-_facility_user',
                                    'meta_key' => 'admin_facility', // the key
                                    'meta_compare' => '=', // has a value that is equal to 
                                    'meta_value' => $user_facility->user_email
                                );
                                $args_users_query = new WP_User_Query( $args_users );
                                if ( !empty( $args_users_query->results ) ) {
                                    foreach ( $args_users_query->results as $facility_users ) {
                                        echo '<tr id="user-'.$facility_users->ID.'" style="background: inherit;">
                                        <td class="username column-username has-row-actions column-primary" data-colname="Username">
                                            <strong>—— '.$facility_users->display_name.'</strong>
                                        </td>
                                        <td class="name column-name" data-colname="Name">'.$facility_users->user_firstname.' '.$facility_users->user_lastname.'</td>
                                        <td class="email column-email" data-colname="Email">'.$facility_users->user_email.'</td>
                                        <td class="role column-role" data-colname="Role">';
                                        foreach ($facility_users->roles as $role) {
                                            $role =  str_replace("_"," ",$role);
                                            echo ucwords($role);
                                        }
                                        echo '</td>
                                        </tr>';
                                    }
                                }
                                else {
                                    echo '<tr>
                                        <td class="username column-username column-primary" data-colname="Username">
                                                —— No Facility users found under Corporate Facility: '.$user_facility->user_email.'
                                        </td>
                                    </tr>';
                                }

                            }
                        }
                        else {
                            echo '<tr>
                                <td class="username column-username column-primary" data-colname="Username">
                                        No users found under Corporate Facility: '.$user->user_email.'
                                </td>
                            </tr>';
                        }
                    }
                }
            } else {
                echo '<tr>
                        <td class="username column-username column-primary" data-colname="Username">
                                No users found. 
                        </td>
                    </tr>';
            }
            echo  '</tbody><tfoot>
             <tr>
                                    <th scope="col" class="manage-column column-name">Username</th>
                                    <th scope="col" class="manage-column column-name">Name</th>
                                    <th scope="col" class="manage-column column-name">Email</th>
                                    <th scope="col" class="manage-column column-role">Role</th>
                                </tr>

                        </tfoot>

                    </table>';
        }
        
    }
    
    new PT_Filters_Users();
}
