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
                array($this, 'import_user_submenu_callback')
            );
        }
        
        public function import_user_submenu_callback() { ?>
            <!-- Form to upload new users in csv format -->
            <form method="post"
                  action="<?php echo admin_url( 'admin-post.php' ); ?>" 
                  enctype="multipart/form-data">
                <input type="hidden" name="action"  value="import_pt_users" />

                <!-- Adding security through hidden referrer field -->
                <?php wp_nonce_field( 'pt_users_import' ); 
                $no_cache= rand(10,99999);?>

                <h3>Import Users</h3>
                    Import user records from CSV File
                    (For reference see this <a href="<?php echo get_stylesheet_directory_uri() . '/includes/user-module/importtemplate.csv?nocache=' .$no_cache; ?>">Template</a> file)
                    <input name="importusers" type="file" accept=".csv"/> <br /><br />
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
                                'policies_procedures_access' => $data[4],
                                'frontend_docs_upload_access' => $data[5],
                                'work_phone' => $data[6],
                                'user_email' => $data[7],
                                'street_1' => $data[8],
                                'street_2' => $data[9],
                                'city' => $data[10],
                                'state' => $data[11],
                                'zip_code' => $data[12],
                                'capabilities' => $data[13],
                                'business' => $data[14],
                                'corporate_facility' => $data[15],
                                'admin_facility' => $data[16],
                                'account_manager' => $data[17],
                                // 'create_date' => $data[18],
                                // 'start_date' => $data[19],
                                // 'end_date' => $data[20],
                                // 'end_date' => $data[21],
                                'no_of_documents_viewed' => $data[18],
                                'no_of_documents_downloaded' => $data[19],
                            );
                            $user_id = username_exists( $new_record['user_email'] );
                            $user_email = email_exists( $new_record['user_email'] );
                            $role = get_role( $new_record['capabilities'] );
                            
                            if ($user_id) {
                                $user = get_user_by('id', $user_id);
                                echo "User with Email: ".$user->user_email . " already exists<br>";
                            } elseif($user_email) {
                                 echo "User with Email: ".$user_email . " already exists<br>";
                            } elseif( empty($new_record['first_name']) ) {
                                echo "First Name not given for Email: ". $new_record['user_email']."<br>";
                            } elseif( empty($new_record['last_name']) ) {
                                echo "Last Name not given for Email: ". $new_record['user_email']."<br>";
                            } elseif($role == null) {
                                echo "Invalid role given for Email: ". $new_record['user_email']."<br>";
                            } elseif( empty($new_record['street_1']) ) {
                                echo "Street address not given for Email: ". $new_record['user_email']."<br>";
                            } elseif( empty($new_record['city']) ) {
                                echo "City not given for Email: ". $new_record['user_email']."<br>";
                            } elseif( empty($new_record['state']) ) {
                                echo "State not given for Email: ". $new_record['user_email']."<br>";
                            } elseif( empty($new_record['zip_code']) ) {
                                echo "Zipcode not given for Email: ". $new_record['user_email']."<br>";
                            } elseif( empty($new_record['user_status']) || ( strtolower($new_record['user_status']) != 'enable' && strtolower($new_record['user_status']) != 'disable' ) ) {
                                echo "User Status is Invalid for Email: ". $new_record['user_email']."<br>";
                            } elseif( empty($new_record['tools_templates_access']) || ( strtolower($new_record['tools_templates_access']) != 'enable' && strtolower($new_record['tools_templates_access']) != 'disable' ) ) {
                                echo "User Tools and Template access is invalid for Email: ". $new_record['user_email']."<br>";
                            } elseif( empty($new_record['policies_procedures_access']) || ( strtolower($new_record['policies_procedures_access']) != 'enable' && strtolower($new_record['policies_procedures_access']) != 'disable' ) ) {
                                echo "User Policies and Procedures access is invalid for Email: ". $new_record['user_email']."<br>";
                            } elseif( empty($new_record['frontend_docs_upload_access']) || ( strtolower($new_record['frontend_docs_upload_access']) != 'enable' && strtolower($new_record['frontend_docs_upload_access']) != 'disable' ) ) {
                                echo "User Frontend Documents access is invalid for Email: ". $new_record['user_email']."<br>";
                            } elseif( empty($new_record['account_manager']) ) {
                                echo "Account Manager not given for Email: ". $new_record['user_email']."<br>";
                            } elseif (!$user_id && $user_email == false && $role !== null) {

                                $company_assigned_id = '';
                                
                                $user_id = wp_create_user( $new_record['user_email'], 'password', $new_record['user_email'] );
                                /* For import file*/
                                // $udata = get_userdata($user_id);
                                // $registered_date = date( 'd/m/Y', strtotime( $udata->user_registered ) );
                                // update_user_meta($user_id, 'create_date', $registered_date);

                                echo "User Created ". $new_record['user_email']."<br>";

                                update_user_meta($user_id, 'first_name', $new_record['first_name']);
                                update_user_meta($user_id, 'last_name', $new_record['last_name']);
                                $user_status = strtolower($new_record['user_status']);
                                update_user_meta($user_id, 'user_status', $user_status);
                                $tools_templates_access = strtolower($new_record['tools_templates_access']);
                                $policies_procedures_access = strtolower($new_record['policies_procedures_access']);
                                $fed_access = strtolower($new_record['frontend_docs_upload_access']);
                                update_user_meta($user_id, 'tools_templates_access', $tools_templates_access);
                                update_user_meta($user_id, 'policies_procedures_access', $policies_procedures_access);
                                update_user_meta($user_id, 'frontend_docs_upload_access', $fed_access);
                                update_user_meta($user_id, 'work_phone', $new_record['work_phone']);
                                update_user_meta($user_id, 'street_1', $new_record['street_1']);
                                update_user_meta($user_id, 'street_2', $new_record['street_2']);
                                update_user_meta($user_id, 'city', $new_record['city']);
                                update_user_meta($user_id, 'state', $new_record['state']);
                                update_user_meta($user_id, 'zip_code', $new_record['zip_code']);

                                $business = term_exists( $new_record['business'], 'company' );
                                if ( 0 !== $business && null !== $business ) {
                                    /*Company Already Exists*/
                                    $get_business = get_term_by('name', $new_record['business'], 'company');
                                    update_user_meta( $user_id, 'business_companies', $get_business->term_id );
                                    update_users_company_count( $get_business->term_id, 'business_companies' );
                                } else {
                                    /*Company Doesn't Exists*/
                                    $company_id = wp_insert_term(
                                        $new_record['business'], // the company 
                                        'company' // the taxonomy
                                    );
                                    if (! is_wp_error( $company_id ) ) {
                                        $company_id = $company_id['term_id'];
                                        $current_user = wp_get_current_user();
                                        add_term_meta($company_id, 'company_author', $current_user->ID );
                                        add_term_meta($company_id, 'company_created_date', current_time('mysql') );
                                        update_user_meta( $user_id, 'business_companies', $company_id );
                                        update_users_company_count( $company_id, 'business_companies' );
                                    }
                                }

                                $corporate_facility = term_exists( $new_record['corporate_facility'], 'company' );
                                if ( 0 !== $corporate_facility && null !== $corporate_facility ) {
                                    /*Company Already Exists*/
                                    $get_corporate_facility = get_term_by('name', $new_record['corporate_facility'], 'company');
                                    update_user_meta( $user_id, 'corporate_companies', $get_corporate_facility->term_id );
                                    update_users_company_count( $get_corporate_facility->term_id, 'corporate_companies' );
                                } else {
                                    /*Company Doesn't Exists*/
                                    $get_parent_business = get_term_by('name', $new_record['business'], 'company');
                                    $parent_business = term_exists( $get_parent_business->term_id, 'company' );


                                    if ( 0 !== $parent_business && null !== $parent_business ) {
                            
                                        $corporate_company_id = wp_insert_term(
                                            $new_record['corporate_facility'], // the company 
                                            'company', // the taxonomy
                                            array(
                                                'parent'      => $get_parent_business->term_id,
                                            )
                                        );
                                        if (! is_wp_error( $corporate_company_id ) ) {
                                            $corporate_company_id = $corporate_company_id['term_id'];
                                            $current_user = wp_get_current_user();
                                            add_term_meta($corporate_company_id, 'company_author', $current_user->ID );
                                            add_term_meta($corporate_company_id, 'company_created_date', current_time('mysql') );
                                            update_user_meta( $user_id, 'corporate_companies', $corporate_company_id );
                                            update_users_company_count( $corporate_company_id, 'corporate_companies' );
                                        }
                                    } else {
                                        $corporate_company_id = wp_insert_term(
                                            $new_record['corporate_facility'], // the company 
                                            'company' // the taxonomy
                                        );
                                        if (! is_wp_error( $corporate_company_id ) ) {
                                            $corporate_company_id = $corporate_company_id['term_id'];
                                            update_user_meta( $user_id, 'corporate_companies', $corporate_company_id );
                                            update_users_company_count( $corporate_company_id, 'corporate_companies' );
                                        }

                                    }

                                }
                                $admin_facility = term_exists( $new_record['admin_facility'], 'company' );
                                if ( 0 !== $admin_facility && null !== $admin_facility ) {
                                    /*Company Already Exists*/
                                    $get_admin_facility = get_term_by('name', $new_record['admin_facility'], 'company');
                                    update_user_meta( $user_id, 'admin_companies', $get_admin_facility->term_id );
                                    update_users_company_count( $get_admin_facility->term_id, 'admin_companies' );
                                } else {
                                    /*Company Doesn't Exists*/
                                    $get_parent_corporate = get_term_by('name', $new_record['corporate_facility'], 'company');
                                    $parent_corporate = term_exists( $get_parent_corporate->term_id, 'company' );
                                    if ( 0 !== $parent_corporate && null !== $parent_corporate ) {
                                        $admin_company_id = wp_insert_term(
                                            $new_record['admin_facility'], // the company 
                                            'company', // the taxonomy
                                            array(
                                                'parent'      => $get_parent_corporate->term_id,
                                            )
                                        );
                                        if (! is_wp_error( $admin_company_id ) ) {
                                            $admin_company_id = $admin_company_id['term_id'];
                                            $current_user = wp_get_current_user();
                                            add_term_meta($admin_company_id, 'company_author', $current_user->ID );
                                            add_term_meta($admin_company_id, 'company_created_date', current_time('mysql') );
                                            update_user_meta( $user_id, 'admin_companies', $admin_company_id );
                                            update_users_company_count( $admin_company_id, 'admin_companies' );
                                        }
                                    } else {
                                        $admin_company_id = wp_insert_term(
                                            $new_record['admin_facility'], // the company 
                                            'company' // the taxonomy
                                        );
                                        if (! is_wp_error( $admin_company_id ) ) {
                                            $admin_company_id = $admin_company_id['term_id'];
                                            update_user_meta( $user_id, 'admin_companies', $admin_company_id );
                                            update_users_company_count( $admin_company_id, 'admin_companies' );
                                        }

                                    }
                                }
                                /* Comment to remove import start, end and create date*/
                                // update_user_meta($user_id, 'create_date', $new_record['create_date']);
                                // update_user_meta($user_id, 'start_date', $new_record['start_date']);
                                // update_user_meta($user_id, 'end_date', $new_record['end_date']);


                                update_user_meta($user_id, 'account_manager', $new_record['account_manager']);
                                update_user_meta( $user_id, 'login_amount', '1' );

                                $u = new WP_User( $user_id );
                                $u->remove_role( 'subscriber_-_facility_user' );
                                $u->add_role( $new_record['capabilities'] );

                                $nickname = explode( '@',$new_record['user_email'] );
                                $nickname = $nickname[0];
                                update_user_meta($user_id, 'nickname', $nickname);
                                $wpdb->update(
                                    $wpdb->prefix.'users',
                                    array( 'display_name' => $nickname ),
                                    array( 'ID' => $user_id )
                                );

				                if ($new_record['no_of_documents_viewed'] || $new_record['no_of_documents_downloaded']) {

                                    global $wpdb;
                                    $activity_table = $wpdb->prefix . 'reports_activity';

                                    if ($new_record['no_of_documents_viewed']) {
                                        $views_count = $new_record['no_of_documents_viewed'];
                                    } else {
                                        $views_count = '0';
                                    }

                                    if ($new_record['no_of_documents_downloaded']) {
                                        $downloads_count = $new_record['no_of_documents_downloaded'];
                                    } else {
                                        $downloads_count = '0';
                                    }

                                    $activity_data = array(
                                        'user_id' => $user_id,
                                        'views_count' => $views_count,
                                        'downloads_count' => $downloads_count,
                                        'activity_count' => $views_count + $downloads_count,
                                    );
                                    $activity_data = array_filter($activity_data); //Remove any null values.
                                    $insert_activity_table = $wpdb->insert($activity_table, $activity_data);
                                }


                                if(!empty($new_record['admin_facility'])) {
                                    $company_assigned_id = $new_record['admin_facility'];
                                }
                            
                                if(!empty($new_record['corporate_facility'])) {
                                    $company_assigned_id = $new_record['corporate_facility'];
                                }

                                update_user_meta($user_id, 'company_assigned', $company_assigned_id);

                                //wp_new_user_notification( $user_id, null, 'both' );
                                /*$user = get_user_by('id', $user_id);
                                $email = $user->user_email;
                                $key = get_password_reset_key( $user );
                                $user_login = $user->user_login;

                                $rp_link =  network_site_url()."wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login);
                                $message = "";
                                $message .= "Username: ".$email."<br><br>";
                                $message .= "To set your password, visit the following address: <br><br>";
                                $message .= '<a href="'.$rp_link.'">'.$rp_link."</a>\r\n";
                                $subject = "[TCS Secure] Login Details";
                                $headers = array();
                                add_filter( 'wp_mail_content_type', function( $content_type ) {return 'text/html';});
                                //$headers[] = 'From: The Compliance Store <customerservice@thecompliancestore.com>'."\r\n";
                                wp_mail( $email, $subject, $message);

                                remove_filter( 'wp_mail_content_type', 'set_html_content_type' );*/
                            }
                        }
                    }
                }
            }

            // Redirect the page to the user submission form
            /*wp_redirect( 'users.php' );
            exit;*/
        }
    }
    
    new PT_Import_Users();
}
