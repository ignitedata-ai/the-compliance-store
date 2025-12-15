<?php
if (!defined('ABSPATH'))
{
    exit;
}

if (!class_exists('PT_Export_Users'))
{
    class PT_Export_Users
    {
        public function __construct()
        {
            add_action('admin_menu', array($this, 'add_export_user_page'));
            // add_action( 'admin_init', array($this, 'pt_user_admin_init') );
            add_action("wp_ajax_pt_na_export_all_user", array($this, 'pt_na_export_all_user'));
            add_action("wp_ajax_nopriv_pt_na_export_all_user", array($this, 'pt_na_export_all_user'));
            add_action("wp_ajax_pt_query_call_for_all_export", array($this, 'pt_query_call_for_all_export'));
            add_action("wp_ajax_nopriv_pt_query_call_for_all_export", array($this, 'pt_query_call_for_all_export'));
        }

        public function pt_query_call_for_all_export()
        {
            $chunk_index = $_POST['data']['chunk'];
            $all_users = get_users(array(
                'fields' => 'ID',
                'orderby' => 'email',
                'order' => 'ASC',
                'number' => 500,
                'page' => ($chunk_index + 1) ,
                'offset' => $chunk_index * 500,
                'role__not_in' => array('administrator') ,
            ));

            foreach ($all_users as $exported_user)
            {
                $business_term = $corporate_term = $admin_term = "";
                $user_obj = get_user_by('id', $exported_user);
                $user_business_id = get_the_author_meta('business_companies', $exported_user);
                if (isset($user_business_id) && !empty($user_business_id))
                {
                    $business_term = get_term($user_business_id, 'company');
                }

                $user_corporate_id = get_the_author_meta('corporate_companies', $exported_user);
                if (isset($user_corporate_id) && !empty($user_corporate_id))
                {
                    $corporate_term = get_term($user_corporate_id, 'company');
                }

                $user_admin_id = get_the_author_meta('admin_companies', $exported_user);
                if (isset($user_admin_id) && !empty($user_admin_id))
                {
                    $admin_term = get_term($user_admin_id, 'company');
                }

                $status = ucwords(get_user_meta($exported_user, 'user_status', true));
                $tools_templates_access = ucwords(get_user_meta($exported_user, 'tools_templates_access', true));
                $policies_procedures_access = ucwords(get_user_meta($exported_user, 'policies_procedures_access', true));
                $fed_access = ucwords(get_user_meta($exported_user, 'frontend_docs_upload_access', true));

                $fields = array(
                    $user_obj->first_name,
                    $user_obj->last_name,
                    $status,
                    $tools_templates_access,
                    $policies_procedures_access,
                    $fed_access,
                    get_user_meta($exported_user, 'work_phone', true) ,
                    $user_obj->user_email,
                    get_user_meta($exported_user, 'street_1', true) ,
                    get_user_meta($exported_user, 'street_2', true) ,
                    get_user_meta($exported_user, 'city', true) ,
                    get_user_meta($exported_user, 'state', true) ,
                    get_user_meta($exported_user, 'zip_code', true) ,
                    $user_obj->roles[0],
                    $business_term->name,
                    $corporate_term->name,
                    $admin_term->name,
                    get_user_meta($exported_user, 'account_manager', true) ,
                    date('m/d/Y', get_user_meta($exported_user, 'create_date', true)) ,
                    // get_user_meta($exported_user, 'create_date', true) ,
                    get_user_meta($exported_user, 'start_date', true) ,

                );
                $csv_file_path = REPORTS_PATH . "all-users.csv";
                $fap = fopen($csv_file_path, 'a');
                fputcsv($fap, $fields);
                fclose($fap);
            }
            $no_cache= time();
            $file_url = REPORTS_URL . '/all-users.csv?nocache=' .$no_cache;
            $html = "<div class='ftf-ajax-message'>
            <p class='ftf-action'><a href='$file_url' id='ftf-communication-dos' ></a> <b>All users exported successfully.<b></p>
        </div>
        
        <script type='text/javascript'>
            setTimeout(function() {
                document.getElementById('ftf-communication-dos').click();
            }, 1000); 
        </script>";
            echo $html;
            exit;
        }

        public function pt_na_export_all_user()
        {
            $header_names = array(
                "First Name",
                "Last Name",
                "User Status",
                "Tools & Templates Access",
                "Policies & Procedures Access",
                "Upload Documents On Frontend",
                "Work Phone",
                "Email",
                "Street",
                "Street2",
                "City",
                "State",
                "Zip",
                "Role",
                "Business Member",
                "Corporate Facility",
                "Admin Facility",
                "Account Manager",
                "Creation Date",
                "Start Date"
            );
            
            $total_users = count_users();
            if ($total_users['total_users'] % 500 == 0)
            {
                $total_chunks = $total_users['total_users'] / 500;
            }
            else
            {
                $total_chunks = ($total_users['total_users'] / 500) + 1;
            }            
            echo $total_chunks;
            
            $csv_file_path = REPORTS_PATH . "all-users.csv";
            $fp = fopen($csv_file_path, 'w');
            fputcsv($fp, $header_names);
            // for ( $i = 0; $i < $total_chunks; $i++ )
            exit;         
        }

        public function add_export_user_page()
        {
            add_submenu_page('users.php', 'Export Users', 'Export Users', 'manage_options', 'pt-users-exports', array(
                $this,
                'import_user_submenu_callback'
            ));
        }

        public function import_user_submenu_callback()
        {
            global $wpdb;

            $user_meta_table = $wpdb->prefix . 'usermeta';
            $user_table = $wpdb->prefix . 'users';
            $key = 'create_date';
            $DESC = 'DESC';
            $header_names = array(
                "First Name",
                "Last Name",
                "User Status",
                "Tools & Templates Access",
                "Policies & Procedures Access",
                "Upload Documents On Frontend",
                "Work Phone",
                "Email",
                "Street",
                "Street2",
                "City",
                "State",
                "Zip",
                "Role",
                "Business Member",
                "Corporate Facility",
                "Admin Facility",
                "Account Manager",
                "Creation Date",
                "Start Date"
            );

?>
            <div class="wrap">

                <h2><?php _e('Export Users Report', 'reports'); ?></h2>

                <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
                <p><?php _e('Use this page to export all users', 'reports'); ?></p>
                </div>

                <div id="poststuff">
                    <div id="post-body">
                        <div class="postbox">
                            <!-- Log export button -->
                            <h3 class="hndle">
                                <label for="title"><?php _e('Export All Users', 'reports'); ?></label>
                            </h3>
                            <div class="inside">
                                <form id="na-pt-export-all" method="post" action="">
                                    <div class="submit">
                                        <input type="submit" class="button" name="reports_export_users" value="<?php _e('Export All Users to CSV File', 'reports'); ?>" />
                                    </div>
                                    <div class="pt-ajax-res-all-user-export">
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="postbox">
                            <h3 class="hndle">
                                <label for="title"><?php _e('Filter Users', 'reports'); ?></label>
                            </h3>
                            <div class="inside">
                                <form id="user_export_choose_date" method="post">
                                    <div>
                                        <h4 style="margin-bottom: 0;">Filter by Date Range (dd-mm-yyyy)</h4>
                                        <input type="text" placeholder="Start Date: " class="datepicker" name="user_account_start_date" value="<?php echo $_POST['user_account_start_date']; ?>">
                                        <input type="text" placeholder="End Date: " class="datepicker" name="user_account_end_date" value="<?php echo $_POST['user_account_end_date']; ?>">

                                        <select id="list" style="margin-top: -5px;">
                                            <option>Select Date Filter</option>
                                            <option data-start-date="<?php echo date("m-d-Y"); ?>" data-end-date="<?php echo date("m-d-Y"); ?>">Today</option>
                                            <option data-start-date="<?php echo date('m-d-Y', strtotime("-1 days")); ?>" data-end-date="<?php echo date('m-d-Y', strtotime("-1 days")); ?>">Yesterday</option>
                                            <?php
            $previous_week = strtotime("-1 week +1 day");

            $start_week = strtotime("last monday", $previous_week);
            $end_week = strtotime("next sunday", $start_week);

            $start_week = date("m-d-Y", $start_week);
            $end_week = date("m-d-Y", $end_week);
?>
                                            <option  data-start-date="<?php echo $start_week; ?>" data-end-date="<?php echo $end_week; ?>">Last Week</option>
                                            <?php
            $d = strtotime("today");
            $start_week = strtotime("last monday", $d);
            $start = date("m-d-Y", $start_week);
?>
                                            <option  data-start-date="<?php echo $start; ?>" data-end-date="<?php echo date("m-d-Y"); ?>">This Week</option>
                                            <option data-start-date="<?php echo date('m-d-Y', strtotime('first day of last month')); ?>" data-end-date="<?php echo date('m-d-Y', strtotime('last day of last month')); ?>">Last Month</option>
                                            <option data-start-date="<?php echo date('m-01-Y'); ?>" data-end-date="<?php echo date('m-d-Y'); ?>">This Month</option>
                                            <option data-start-date="<?php echo date("01-01-Y", strtotime("-1 year")); ?>" data-end-date="<?php echo date("12-31-Y", strtotime('last year')); ?>">Last Year</option>
                                            <option data-start-date="<?php echo date('01-01-Y'); ?>" data-end-date="<?php echo date('m-d-Y'); ?>">This Year</option>
                                        </select>
                                    </div>
                                    <div>
                                        <h4 style="margin-bottom: 5px;">Filter by Company</h4>
                                        <?php
            $post_type = 'users';
            $taxonomy = 'company';
            $info_taxonomy = get_taxonomy($taxonomy);

            $selected = selected($section, $value, false);
            $options = wp_dropdown_categories(array(
                'show_option_all' => __("Select Company...") ,
                'taxonomy' => $taxonomy,
                'name' => 'company_export_users',
                'orderby' => 'name',
                'selected' => $section,
                'value_field' => 'id',
                'hierarchical' => 1,
                'hide_empty' => 0,

            ));
?>
                                    </div>
                                    <div class="submit">
                                        <input type="submit" name="export-by-date" value="Generate" class="button-primary" value="<?php _e('Generate Report', 'reports'); ?>">
                                    </div>
                                    <?php
            if (isset($_POST['export-by-date']))
            {

                $company = $_POST['company_export_users'];

                $user_account_start_date = strtotime($_POST['user_account_start_date']);

                $new_s_date = explode("-", $_POST['user_account_start_date']);
                
                $new_s_date = $new_s_date[2]."-".$new_s_date[0]."-".$new_s_date[1];
                $user_account_start_date = strtotime($new_s_date);

                echo $new_s_date;
                // $user_account_end_date = strtotime($_POST['user_account_end_date']);

                $newdate = explode("-", $_POST['user_account_end_date']);
                
                $newdate = $newdate[2]."-".$newdate[0]."-".$newdate[1];
                $user_account_end_date = strtotime($newdate);

                // $user_account_start_date_t = date('m-d-y', strtotime($_POST['user_account_start_date']));
                // $user_account_end_date_t = date('m-d-y', strtotime($_POST['user_account_end_date']));

                // $user_account_start_date = strtotime($user_account_start_date_t);
                // $user_account_end_date = strtotime($user_account_end_date_t);



                if (!empty($_POST['user_account_start_date']) && !empty($_POST['user_account_end_date']) && !empty($_POST['company_export_users']))
                {

                    $res = $wpdb->get_results("SELECT u.ID
                                                FROM $user_table u 
                                                LEFT JOIN $user_meta_table  um1 ON u.ID = um1.user_id
                                                LEFT JOIN $user_meta_table  um2 ON u.ID = um2.user_id
                                                WHERE
                                                um1.meta_value = '$company' AND (um1.meta_key = 'business_companies' OR um1.meta_key = 'corporate_companies' OR um1.meta_key = 'admin_companies' ) AND
                                                um2.meta_key = '$key' AND (um2.meta_value >= '$user_account_start_date' AND um2.meta_value <= '$user_account_end_date')
                                                GROUP BY u.ID");
                }
                elseif (!empty($_POST['company_export_users']))
                {
                    $res = $wpdb->get_results("SELECT u.ID
                                                FROM $user_table u 
                                                LEFT JOIN $user_meta_table  um1 ON u.ID = um1.user_id
                                                LEFT JOIN $user_meta_table  um2 ON u.ID = um2.user_id
                                                WHERE
                                                um1.meta_value = '$company' AND (um1.meta_key = 'business_companies' OR um1.meta_key = 'corporate_companies' OR um1.meta_key = 'admin_companies' )
                                                GROUP BY u.ID");
                }
                elseif (!empty($_POST['user_account_start_date']) && !empty($_POST['user_account_end_date']))
                {
                    $res = $wpdb->get_results("SELECT u.ID
                                                FROM $user_table u 
                                                LEFT JOIN $user_meta_table  um1 ON u.ID = um1.user_id
                                                LEFT JOIN $user_meta_table  um2 ON u.ID = um2.user_id
                                                WHERE
                                                um2.meta_key = '$key' AND (um2.meta_value >= '$user_account_start_date' AND um2.meta_value <= '$user_account_end_date')
                                                GROUP BY u.ID");
                    // echo $res;
                    // die; 
                }
                else
                {
                    $res = $wpdb->get_results("SELECT u.ID
                                                FROM $user_table u 
                                                LEFT JOIN $user_meta_table  um1 ON u.ID = um1.user_id
                                                GROUP BY u.ID");

                }
                
                $csv_file_path = REPORTS_PATH . "users-by-date.csv";
                $fp = fopen($csv_file_path, 'w');

                fputcsv($fp, $header_names);

                foreach ($res as $res_user)
                {

                    $business_term = $corporate_term = $admin_term = "";

                    $res_user_obj = get_user_by('id', $res_user->ID);

                    $user_business_id = get_the_author_meta('business_companies', $res_user->ID);
                    if (isset($user_business_id) && !empty($user_business_id))
                    {
                        $business_term = get_term($user_business_id, 'company');
                    }

                    $user_corporate_id = get_the_author_meta('corporate_companies', $res_user->ID);
                    if (isset($user_corporate_id) && !empty($user_corporate_id))
                    {
                        $corporate_term = get_term($user_corporate_id, 'company');
                    }

                    $user_admin_id = get_the_author_meta('admin_companies', $res_user->ID);
                    if (isset($user_admin_id) && !empty($user_admin_id))
                    {
                        $admin_term = get_term($user_admin_id, 'company');
                    }

                    $status = ucwords(get_user_meta($res_user->ID, 'user_status', true));
                    $tools_templates_access = ucwords(get_user_meta($res_user->ID, 'tools_templates_access', true));
                    $policies_procedures_access = ucwords(get_user_meta($res_user->ID, 'policies_procedures_access', true));

                    $fed_access = ucwords(get_user_meta($res_user->ID, 'frontend_docs_upload_access', true));

                    $fields = array(
                        $res_user_obj->first_name,
                        $res_user_obj->last_name,
                        $status,
                        $tools_templates_access,
                        $policies_procedures_access,
                        $fed_access,
                        get_user_meta($res_user->ID, 'work_phone', true) ,
                        $res_user_obj->user_email,
                        get_user_meta($res_user->ID, 'street_1', true) ,
                        get_user_meta($res_user->ID, 'street_2', true) ,
                        get_user_meta($res_user->ID, 'city', true) ,
                        get_user_meta($res_user->ID, 'state', true) ,
                        get_user_meta($res_user->ID, 'zip_code', true) ,
                        $res_user_obj->roles[0],
                        $business_term->name,
                        $corporate_term->name,
                        $admin_term->name,
                        get_user_meta($res_user->ID, 'account_manager', true) ,
                        date('m/d/Y', get_user_meta($res_user->ID, 'create_date', true)) , 
                        // get_user_meta($res_user->ID, 'create_date', true) ,
                        get_user_meta($res_user->ID, 'start_date', true) ,

                    );

                    fputcsv($fp, $fields);
                }
                fclose($fp);
                $no_cache= time();
                $users_by_date = REPORTS_URL . '/users-by-date.csv?nocache=' .$no_cache;

                echo '<p>';
                _e('Log entries exported! Click on the following link to download the file.', 'reports');
                echo '<br /><a class="file-download-btn" href="' . $users_by_date . '">' . __('Download Users CSV File', 'reports') . '</a>';
                echo '</p>';
            }
?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                jQuery(function () {
                    jQuery('.datepicker').datepicker({
                        dateFormat: 'mm-dd-yy'
                    });
                });
                jQuery('#list').change(function (e) {
                    jQuery('#user_export_choose_date').find('input[name="user_account_start_date"]').val(jQuery(this).find(':selected').attr('data-start-date'));
                    jQuery('#user_export_choose_date').find('input[name="user_account_end_date"]').val(jQuery(this).find(':selected').attr('data-end-date'));
                });
                
            </script>
            <?php
        }

        public function export_all_user_records()
        {
            // Check that user has proper security level
            if (!current_user_can('manage_options')) wp_die('Not allowed');

        }
    }
    new PT_Export_Users();
}
