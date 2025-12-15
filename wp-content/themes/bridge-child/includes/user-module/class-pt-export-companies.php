<?php

if ( !defined('ABSPATH') ) {
    exit;
}

if ( !class_exists('PT_Export_Companies') ) {
    class PT_Export_Companies {
        
        public function __construct() {
            add_action( 'admin_menu', array($this, 'add_export_company_page') );
            add_action( 'admin_init', array($this, 'pt_company_admin_init') );
        }
        
        public function pt_company_admin_init() {
            add_action( 'admin_post_export_pt_company', array($this, 'export_company_records') );
        }
        
        public function add_export_company_page() {
            add_submenu_page(
                'users.php',
                'Export Companies',
                'Export Companies',
                'manage_options',
                'pt-companies-exports',
                array($this, 'export_companies_submenu_callback'),
            );
        }
        
        public function export_companies_submenu_callback() { 
            global $wpdb;

            $header_names = array(
                "Company Name",
                "Company Status",
                "State",
                "Date Created",
                "Created By",
                "Last Modified on",
            );

            ?>
            <div class="wrap">

                <h2><?php _e( 'Export Companies', 'reports' ); ?></h2>
            </div>

                <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
                <p><?php _e( 'Use this page to export all companies', 'reports' ); ?></p>
                </div>

                <div id="poststuff">
                    <div id="post-body">
                        <div class="postbox">
                            <!-- Log export button -->
                            <h3 class="hndle">
                                <label for="title"><?php _e( 'Export All Companies', 'reports' ); ?></label>
                            </h3>
                            <div class="inside">
                                <form method="post" action="" onSubmit="return confirm('Are you sure you want to export all companies?');" >
                                <?php
                                if ( isset( $_POST[ 'reports_export_companies' ] ) ) {

                                    $csv_file_path = REPORTS_PATH . "all-companies.csv";
                                    $fp = fopen($csv_file_path, 'w');

                                    fputcsv($fp, $header_names);

                                    foreach( get_terms( 'company', array( 'hide_empty' => false, 'parent' => 0 ) ) as $parent_term ) {
                                        $parent_comapny_company_status = $parent_company_last_edit =$parent_company_edit_by = $parent_last_modified_on = $child_comapny_company_status = $child_company_last_edit = $child_last_modified_on = ''; 
                                        $parent_comapny_company_status = get_term_meta( $parent_term->term_id, 'users_comapny_company_status', true);
                                        if ($parent_comapny_company_status) {
                                            $parent_comapny_company_status = $parent_comapny_company_status;
                                        } else {
                                            $parent_comapny_company_status = 'none';
                                        }
                                        $parent_company_last_edit = get_term_meta( $parent_term->term_id, 'company_last_edit', true);
                                        $parent_company_edit_by = get_term_meta( $parent_term->term_id, 'company_edit_by', true);
                                        $company_author = get_term_meta( $parent_term->term_id, 'company_author', true);
                                        $get_company_author = get_user_by( 'ID', $company_author );
                                        if ($parent_company_edit_by) {
                                            $get_parent_company_edit_by = get_user_by( 'ID', $parent_company_edit_by );
                                            $parent_last_modified_on = $parent_company_last_edit.' by '.$get_parent_company_edit_by->display_name;
                                        } else {
                                            $parent_last_modified_on = '';
                                        }
                                        $fields = array(
                                            $parent_term->name,
                                            $parent_comapny_company_status,
                                            get_term_meta( $parent_term->term_id, 'users_comapny_state', true),
                                            get_term_meta( $parent_term->term_id, 'company_created_date', true),
                                            $get_company_author->display_name,
                                            $parent_last_modified_on,

                                        );
                                        fputcsv($fp, $fields);

                                        foreach( get_terms( 'company', array( 'hide_empty' => false, 'parent' => $parent_term->term_id ) ) as $child_term ) {
                                            // display name of all childs of the parent term
                                            $child_comapny_company_status = get_term_meta( $child_term->term_id, 'users_comapny_company_status', true);
                                            if ($child_comapny_company_status) {
                                                $child_comapny_company_status = $child_comapny_company_status;
                                            } else {
                                                $child_comapny_company_status = 'none';
                                            }
                                            $child_company_last_edit = get_term_meta( $child_term->term_id, 'company_last_edit', true);
                                            $child_company_edit_by = get_term_meta( $child_term->term_id, 'company_edit_by', true);
                                            $child_company_author = get_term_meta( $child_term->term_id, 'company_author', true);
                                            $get_child_company_author = get_user_by( 'ID', $child_company_author );
                                            if ($child_company_edit_by) {
                                                $get_child_company_edit_by = get_user_by( 'ID', $child_company_edit_by );
                                                $child_last_modified_on = $child_company_last_edit.' by '.$get_child_company_edit_by->display_name;
                                            } else {
                                                $child_last_modified_on = '';
                                            }
                                            $child_fields = array(
                                                ' -'.$child_term->name,
                                                $child_comapny_company_status,
                                                get_term_meta( $child_term->term_id, 'users_comapny_state', true),
                                                get_term_meta( $child_term->term_id, 'company_created_date', true),
                                                $get_child_company_author->display_name,
                                                $child_last_modified_on,

                                            );
                                            fputcsv($fp, $child_fields);
                                            foreach( get_terms( 'company', array( 'hide_empty' => false, 'parent' => $child_term->term_id ) ) as $sub_child_term ) {
                                                // display name of all childs of the parent term
                                                $sub_child_comapny_company_status = get_term_meta( $sub_child_term->term_id, 'users_comapny_company_status', true);
                                                if ($sub_child_comapny_company_status) {
                                                    $sub_child_comapny_company_status = $sub_child_comapny_company_status;
                                                } else {
                                                    $sub_child_comapny_company_status = 'none';
                                                }
                                                $sub_child_company_last_edit = get_term_meta( $sub_child_term->term_id, 'company_last_edit', true);
                                                $sub_child_company_edit_by = get_term_meta( $sub_child_term->term_id, 'company_edit_by', true);
                                                $sub_child_company_author = get_term_meta( $sub_child_term->term_id, 'company_author', true);
                                                $get_sub_child_company_author = get_user_by( 'ID', $sub_child_company_author );
                                                if ($sub_child_company_edit_by) {
                                                    $get_sub_child_company_edit_by = get_user_by( 'ID', $sub_child_company_edit_by );
                                                    $sub_child_last_modified_on = $sub_child_company_last_edit.' by '.$get_sub_child_company_edit_by->display_name;
                                                } else {
                                                    $sub_child_last_modified_on = '';
                                                }
                                                $sub_child_fields = array(
                                                    ' - -'.$sub_child_term->name,
                                                    $sub_child_comapny_company_status,
                                                    get_term_meta( $sub_child_term->term_id, 'users_comapny_state', true),
                                                    get_term_meta( $sub_child_term->term_id, 'company_created_date', true),
                                                    $get_sub_child_company_author->display_name,
                                                    $sub_child_last_modified_on,

                                                );
                                                fputcsv($fp, $sub_child_fields);
                                                
                                            }
                                        }

                                    }

                                    fclose($fp);
									$no_cache= rand(10,999999);
                                    $file_url = REPORTS_URL . '/all-companies.csv?nocache=' .$no_cache;

                                    echo '<p style="color: green;">';
                                    _e( 'Log entries exported! Click on the following button to download the file.', 'reports' );
                                    echo '<br /><a class="file-download-btn" href="' . $file_url . '">' . __( 'Download Companies CSV File', 'reports' ) . '</a>';
                                    echo '</p>';
                                } else { ?>
                                    <div class="submit">
                                        <input type="submit" class="button" name="reports_export_companies" value="<?php _e( 'Export All Companies to CSV File', 'reports' ); ?>" />
                                    </div>
                                <?php
                                } ?>
                                </form>
                            </div>
                        </div>
                        <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Choose Date Range (yyyy-mm-dd)', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="company_choose_date" method="post">
                            <?php
                            if ( isset( $_POST[ 'company_start_date' ] ) ) {
                                $start_date = sanitize_text_field( $_POST[ 'company_start_date' ] );
                            } else {
                                // default start date is 30 days back
                                $start_date = date( 'Y-m-d', time() - 60 * 60 * 24 * 30 );
                            }

                            if ( isset( $_POST[ 'company_end_date' ] ) ) {
                                $end_date = sanitize_text_field( $_POST[ 'company_end_date' ] );
                            } else {
                                $end_date = date( 'Y-m-d', time() );
                            }
                            ?>
                            <?php _e( 'Start Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="company_start_date" value="<?php echo $start_date; ?>">
                            <?php _e( 'End Date: ', 'reports' ); ?><input type="text" class="datepicker" name="company_end_date" value="<?php echo $end_date; ?>">
                            <p id="reports_company_date_buttons">
                                <button type="button" data-start-date="<?php echo date("Y-m-d"); ?>" data-end-date="<?php echo date("Y-m-d"); ?>"><?php _e( 'Today', 'reports' ); ?></button>
                                <button type="button" data-start-date="<?php echo date('Y-m-d',strtotime("-1 days")); ?>" data-end-date="<?php echo date('Y-m-d',strtotime("-1 days")); ?>"><?php _e( 'Yesterday', 'reports' ); ?></button>
                                <?php 
                                $previous_week = strtotime("-1 week +1 day");

                                $start_week = strtotime("last monday",$previous_week);
                                $end_week = strtotime("next sunday",$start_week);

                                $start_week = date("Y-m-d",$start_week);
                                $end_week = date("Y-m-d",$end_week); 
                                ?>
                                <button type="button" data-start-date="<?php echo $start_week; ?>" data-end-date="<?php echo $end_week; ?>"><?php _e( 'Last Week', 'reports' ); ?></button>
                                <?php 
                                $d = strtotime("today");
                                $start_week = strtotime("last monday",$d);
                                $start = date("Y-m-d",$start_week);
                                ?>
                                <button type="button" data-start-date="<?php echo $start; ?>" data-end-date="<?php echo date("Y-m-d"); ?>"><?php _e( 'This Week', 'reports' ); ?></button>
                                <button type="button" data-start-date="<?php echo date( 'Y-m-d', strtotime( 'first day of last month' ) ); ?>" data-end-date="<?php echo date( 'Y-m-d', strtotime( 'last day of last month' ) ); ?>"><?php _e( 'Last Month', 'reports' ); ?></button>
                                <button type="button" data-start-date="<?php echo date( 'Y-m-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>"><?php _e( 'This Month', 'reports' ); ?></button>
                                <button button type="button" data-start-date="<?php echo date( "Y-01-01", strtotime( "-1 year" ) ); ?>" data-end-date="<?php echo date( "Y-12-31", strtotime( 'last year' ) ); ?>"><?php _e( 'Last Year', 'reports' ); ?></button>
                                <button button type="button" data-start-date="<?php echo date( 'Y-01-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>"><?php _e( 'This Year', 'reports' ); ?></button>
                            </p>
                            <div class="submit">
                                <input type="submit" class="button-primary" value="<?php _e( 'Generate Report', 'reports' ); ?>">
                            </div>
                            <?php 
                            if ( isset( $_POST[ 'company_start_date' ] ) && isset( $_POST[ 'company_end_date' ] )) {
                                //Export log entries
                                $csv_file_path = REPORTS_PATH . "company-date-created-logs.csv";
                                $csv_file = fopen($csv_file_path, 'w');
                                $header_names = array(
                                    "Company Name",
                                    "Company Status",
                                    "State",
                                    "Date Created",
                                    "Created By",
                                    "Last Modified on",
                                );
                                fputcsv($csv_file, $header_names);

                                $args = array(
                                    'taxonomy' => 'company',
                                    //'meta_key' => 'company_created_date',
                                    'hide_empty' => false,
                                    //'parent' => 0,
                                    'meta_query' => array(
                                        //'relation' => 'AND',
                                        array(
                                            'key'     => 'company_created_date',
                                            'value'   => array( $start_date, $end_date ),
                                            'compare' => 'BETWEEN',
                                            'type' => 'DATE',
                                        ),
                                    ),
                                );
                                
                                $terms = get_terms( $args );
                                foreach( $terms as $parent_term ) {
                                    $parent_comapny_company_status = $parent_company_last_edit =$parent_company_edit_by = $parent_last_modified_on = $child_comapny_company_status = $child_company_last_edit = $child_last_modified_on = ''; 
                                    $parent_comapny_company_status = get_term_meta( $parent_term->term_id, 'users_comapny_company_status', true);
                                    if ($parent_comapny_company_status) {
                                        $parent_comapny_company_status = $parent_comapny_company_status;
                                    } else {
                                        $parent_comapny_company_status = 'none';
                                    }
                                    $parent_company_last_edit = get_term_meta( $parent_term->term_id, 'company_last_edit', true);
                                    $parent_company_edit_by = get_term_meta( $parent_term->term_id, 'company_edit_by', true);
                                    $company_author = get_term_meta( $parent_term->term_id, 'company_author', true);
                                    $get_company_author = get_user_by( 'ID', $company_author );
                                    if ($parent_company_edit_by) {
                                        $get_parent_company_edit_by = get_user_by( 'ID', $parent_company_edit_by );
                                        $parent_last_modified_on = $parent_company_last_edit.' by '.$get_parent_company_edit_by->display_name;
                                    } else {
                                        $parent_last_modified_on = '';
                                    }
                                    $fields = array(
                                        $parent_term->name,
                                        $parent_comapny_company_status,
                                        get_term_meta( $parent_term->term_id, 'users_comapny_state', true),
                                        get_term_meta( $parent_term->term_id, 'company_created_date', true),
                                        $get_company_author->display_name,
                                        $parent_last_modified_on,

                                    );
                                    fputcsv($csv_file, $fields);

                                    /*$child_args = array(
                                        'taxonomy' => 'company',
                                        'meta_key' => 'company_created_date',
                                        'hide_empty' => false,
                                        'parent' => $parent_term->term_id,
                                        'meta_query' => array(
                                            //'relation' => 'AND',
                                            array(
                                                'key'     => 'company_created_date',
                                                'value'   => array( $start_date, $end_date ),
                                                'compare' => 'BETWEEN',
                                                'type' => 'DATE',
                                            ),
                                        ),
                                    );
                                    $child_terms = get_terms( $child_args );
                                    echo count($child_terms);
                                    foreach( $child_terms as $child_term ) {
                                        // display name of all childs of the parent term
                                        $child_comapny_company_status = get_term_meta( $child_term->term_id, 'users_comapny_company_status', true);
                                        if ($child_comapny_company_status) {
                                            $child_comapny_company_status = $child_comapny_company_status;
                                        } else {
                                            $child_comapny_company_status = 'none';
                                        }
                                        $child_company_last_edit = get_term_meta( $child_term->term_id, 'company_last_edit', true);
                                        $child_company_edit_by = get_term_meta( $child_term->term_id, 'company_edit_by', true);
                                        $child_company_author = get_term_meta( $child_term->term_id, 'company_author', true);
                                        $get_child_company_author = get_user_by( 'ID', $child_company_author );
                                        if ($child_company_edit_by) {
                                            $get_child_company_edit_by = get_user_by( 'ID', $child_company_edit_by );
                                            $child_last_modified_on = $child_company_last_edit.' by '.$get_child_company_edit_by->display_name;
                                        } else {
                                            $child_last_modified_on = '';
                                        }
                                        $child_fields = array(
                                            ' -'.$child_term->name,
                                            $child_comapny_company_status,
                                            get_term_meta( $child_term->term_id, 'users_comapny_state', true),
                                            get_term_meta( $child_term->term_id, 'company_created_date', true),
                                            $get_child_company_author->display_name,
                                            $child_last_modified_on,

                                        );
                                        fputcsv($csv_file, $child_fields);

                                        $sub_child_args = array(
                                            'taxonomy' => 'company',
                                            'meta_key' => 'company_created_date',
                                            'hide_empty' => false,
                                            'parent' => $child_term->term_id,
                                            'meta_query' => array(
                                                //'relation' => 'AND',
                                                array(
                                                    'key'     => 'company_created_date',
                                                    'value'   => array( $start_date, $end_date ),
                                                    'compare' => 'BETWEEN',
                                                    'type' => 'DATE',
                                                ),
                                            ),
                                        );
                                        $sub_child_terms = get_terms( $sub_child_args );
                                        foreach( $sub_child_terms as $sub_child_term ) {
                                            // display name of all childs of the parent term
                                            $sub_child_comapny_company_status = get_term_meta( $sub_child_term->term_id, 'users_comapny_company_status', true);
                                            if ($sub_child_comapny_company_status) {
                                                $sub_child_comapny_company_status = $sub_child_comapny_company_status;
                                            } else {
                                                $sub_child_comapny_company_status = 'none';
                                            }
                                            $sub_child_company_last_edit = get_term_meta( $sub_child_term->term_id, 'company_last_edit', true);
                                            $sub_child_company_edit_by = get_term_meta( $sub_child_term->term_id, 'company_edit_by', true);
                                            $sub_child_company_author = get_term_meta( $sub_child_term->term_id, 'company_author', true);
                                            $get_sub_child_company_author = get_user_by( 'ID', $sub_child_company_author );
                                            if ($sub_child_company_edit_by) {
                                                $get_sub_child_company_edit_by = get_user_by( 'ID', $sub_child_company_edit_by );
                                                $sub_child_last_modified_on = $sub_child_company_last_edit.' by '.$get_sub_child_company_edit_by->display_name;
                                            } else {
                                                $sub_child_last_modified_on = '';
                                            }
                                            $sub_child_fields = array(
                                                ' - -'.$sub_child_term->name,
                                                $sub_child_comapny_company_status,
                                                get_term_meta( $sub_child_term->term_id, 'users_comapny_state', true),
                                                get_term_meta( $sub_child_term->term_id, 'company_created_date', true),
                                                $get_sub_child_company_author->display_name,
                                                $sub_child_last_modified_on,

                                            );
                                            fputcsv($csv_file, $sub_child_fields);
                                        }
                                    }*/

                                }
                                fclose($csv_file);
								$remove_cache= rand(10,99999);
                                $get_companies_by_date = REPORTS_URL . '/company-date-created-logs.csv?nocache=' .$remove_cache;
                                echo '<p>';
                                _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
                                echo '<br /><a class="file-download-btn" href="' . $get_companies_by_date . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
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
                jQuery('#reports_company_date_buttons button').click(function (e) {
                jQuery('#company_choose_date').find('input[name="company_start_date"]').val(jQuery(this).attr('data-start-date'));
                jQuery('#company_choose_date').find('input[name="company_end_date"]').val(jQuery(this).attr('data-end-date'));
                });
                jQuery(function () {
                jQuery('.datepicker').datepicker({
                    dateFormat: 'yy-mm-dd'
                });
                });
            </script>
            <?php

        }
        
    }
    
    new PT_Export_Companies();
}
