<?php

if ( !defined('ABSPATH') ) {
    exit;
}

if ( !class_exists('PT_Import_Users') ) {
    class PT_Import_Users {
        
        public function __construct() {
            add_action( 'admin_menu', array($this, 'add_import_user_page') );
            add_action( 'admin_init', array($this, 'pt_user_admin_init') );
        }
        
        public function pt_user_admin_init() {
            add_action( 'admin_post_import_pt_users', array($this, 'import_user_records') );
        }
        
        public function add_import_user_page() {
            add_submenu_page(
                'users.php',
                'Import Users',
                'Import Users',
                'manage_options',
                'pt-users-imports',
                array($this, 'import_user_submenu_callback'));
        }
        
        public function import_user_submenu_callback() { ?>
            <!-- Form to upload new users in csv format -->
            <form method="post"
                  action="<?php echo admin_url( 'admin-post.php' ); ?>" 
                  enctype="multipart/form-data">
                <input type="hidden" name="action" value="import_pt_users" />

                <!-- Adding security through hidden referrer field -->
                <?php wp_nonce_field( 'pt_users_import' ); ?>

                <h3>Import Users</h3>
                    Import user records from CSV File
                    (For reference see this <a href="<?php echo get_stylesheet_directory_uri() . '/includes/user-module/importtemplate.csv'; ?>">Template</a> file)
                    <input name="importusers" type="file" /> <br /><br />
                <input type="submit" value="Import" class="button-primary"/>
            </form>
        <?php
        }
        
        
        public function import_user_records() {
            // Check that user has proper security level
            if ( !current_user_can( 'manage_options' ) )
                wp_die( 'Not allowed' );

            // Check if nonce field is present
            check_admin_referer( 'pt_users_import' );

            // Check if file has been uploaded
            if( array_key_exists( 'importusers', $_FILES ) && isset($_FILES['importusers']['tmp_name']) && $_FILES['importusers']['tmp_name'] !== '') {
                // If file exists, open it in read mode
                $handle = fopen( $_FILES['importusers']['tmp_name'], 'r' );
                $row = 0;
                // If file is successfully open, extract a row of data
                // based on comma separator, and store in $data array
                if ( $handle ) {
                    global $wpdb;
                    $prefix = $wpdb->get_blog_prefix();
                    while (( $data = fgetcsv($handle, 5000, ',') ) !== FALSE ) {
                        $row += 1;

                        // If row count is ok and row is not header row
                        // Create array and insert in database
                        if ( $row != 1 ) {
                            $new_record = array(
                                'first_name' => $data[0],
                                'last_name' => $data[1],
                                'user_status' => $data[2],
                                'tools_templates_access' => $data[3],
                                'work_phone' => $data[4],
                                'user_email' => $data[5],
                                'street_1' => $data[6],
                                'street_2' => $data[7],
                                'city' => $data[8],
                                'state' => $data[9],
                                'zip_code' => $data[10],
                                'capabilities' => $data[11],
                                'company' => $data[12],
                                'business' => $data[13],
                                'corporate_facility' => $data[14],
                                'admin_facility' => $data[15],
                                'account_manager' => $data[16],
                                'create_date' => $data[17],
                                'start_date' => $data[18],
                                'end_date' => $data[19],
                                );

                            $user_id = username_exists( $new_record['user_email'] );
                            $user_email = email_exists( $new_record['user_email'] );
                            
                            if ( !$user_id && $user_email == false ) {
                                
                                //$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
                                $random_password = 'password';
                                $user_id = wp_create_user( $new_record['user_email'], $random_password, $new_record['user_email'] );

                                update_user_meta($user_id, 'first_name', $new_record['first_name']);
                                update_user_meta($user_id, 'last_name', $new_record['last_name']);
                                $user_status = strtolower($new_record['user_status']);
                                update_user_meta($user_id, 'user_status', $user_status);
                                $tools_templates_access = strtolower($new_record['tools_templates_access']);
                                update_user_meta($user_id, 'tools_templates_access', $tools_templates_access);
                                update_user_meta($user_id, 'work_phone', $new_record['work_phone']);
                                update_user_meta($user_id, 'street_1', $new_record['street_1']);
                                update_user_meta($user_id, 'street_2', $new_record['street_2']);
                                update_user_meta($user_id, 'city', $new_record['city']);
                                update_user_meta($user_id, 'state', $new_record['state']);
                                update_user_meta($user_id, 'zip_code', $new_record['zip_code']);
                                update_user_meta($user_id, 'company', $new_record['company']);
                                update_user_meta($user_id, 'create_date', $new_record['create_date']);
                                update_user_meta($user_id, 'start_date', $new_record['start_date']);
                                update_user_meta($user_id, 'end_date', $new_record['end_date']);
                                if($new_record['capabilities'] == 'subscriber_-_facility_user') {
                                    $get_admin_facility = get_users(array(
                                        'role' => 'subscriber_-_admin_facility',
                                        'meta_key'     => 'company',
                                        'meta_value' => $new_record['admin_facility'],
                                        'meta_compare' => '=',
                                        'fields' => array( 'user_email' )
                                    ));
                                    update_user_meta($user_id, 'admin_facility', $get_admin_facility[0]->user_email);
                                }
                                elseif ($new_record['capabilities'] == 'subscriber_-_admin_facility') {
                                    $get_users = get_users(array(
                                        'role' => 'subscriber_-_corporate',
                                        'meta_key'     => 'company',
                                        'meta_value' => $new_record['corporate_facility'],
                                        'meta_compare' => '=',
                                        'fields' => array( 'user_email' )
                                    ));
                                    update_user_meta($user_id, 'corporate_facility', $get_users[0]->user_email);
                                }
                                elseif ($new_record['capabilities'] == 'subscriber_-_corporate') {
                                    $get_users = get_users(array(
                                        'role' => 'business',
                                        'meta_key'     => 'company',
                                        'meta_value' => $new_record['business'],
                                        'meta_compare' => '=',
                                        'fields' => array( 'user_email' )
                                    ));
                                    update_user_meta($user_id, 'business', $get_users[0]->user_email);
                                }
                                //exit();
                                update_user_meta($user_id, 'account_manager', $new_record['account_manager']);
                                update_user_meta( $user_id, 'login_amount', '1' );
                                
                                $user_roles = explode(",", $new_record['capabilities']);
                                $u = new WP_User( $user_id );
                                $u->remove_role( 'subscriber' );
                                foreach ($user_roles as $role) {
                                    $u->add_role( $role );
                                }
                                
                                //wp_new_user_notification( $user_id, null, 'both' );
                                    
                            }
                        }
                    }
                }
            }

            // Redirect the page to the user submission form
            wp_redirect( 'users.php' );
            exit;
        }
    }
    
    new PT_Import_Users();
}
