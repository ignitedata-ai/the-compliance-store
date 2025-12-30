<?php

/**
 * Get document views from database
 */

function get_document_views() {

    global $wpdb;
    $table_name = '';
    $viewsListTable = '';

    // check the page to define tablename and table data to list

    if($_REQUEST['page'] == 'frontend-document-views-report') {

        $table_name = $wpdb->prefix . 'frontend_doc_views';
        $viewsListTable = new Frontend_Documents_Views_List_Table();
        $viewsListTable->prepare_items();


    } else {

        $table_name = $wpdb->prefix . 'reports_views';
        $viewsListTable = new views_List_Table();
        $viewsListTable->prepare_items();

    }

    // when reset logs button clicked

    if ( isset( $_POST[ 'reset_documents_views_entries' ] ) ) {

        $query = "TRUNCATE $table_name";
        $wpdb->query( $query );

        echo '<div id="message" class="updated fade"><p>';
            _e( 'All views logs entries deleted!', 'reports' );
        echo '</p></div>';

        header('Location: ' . $_SERVER['REQUEST_URI']);

    }

    ?>    

    <h2><?php _e( 'Documents Views Logs', 'reports' ); ?></h2>

    <div class="reports-notification-wrapper">
        <p><?php _e( 'This page lists all tracked views for documents.', 'reports' ); ?></p>
    </div>

    <div id="poststuff"><div id="post-body">

        <!-- Log reset button -->
        <div class="postbox">
            <h3 class="hndle"><label for="title"><?php _e( 'Reset Documents Views Log Entries', 'reports' ); ?></label></h3>
            <div class="inside">
                <form method="post" action="" onSubmit="return confirm('Are you sure you want to reset all the log entries?');" >
                    <div class="submit">
                        <input type="submit" class="button" name="reset_documents_views_entries" value="<?php _e( 'Reset Log Entries', 'reports' ); ?>" />
                    </div>
                </form>
            </div>
        </div>

    </div>

    <form id="reports_views-filter" method="post">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST[ 'page' ] ) ?>" />
        <?php $viewsListTable->display(); ?>
    </form>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.fade').click(function () {
                $(this).fadeOut('slow');
            });
        });
    </script>
    <?php
}

/**
 * Get document downloads
 */

function get_document_downloads() {

    global $wpdb;
    $table_name = '';
    $downloadsListTable = '';

    // check the page to define tablename and table data to list

    if($_REQUEST['page'] == 'frontend-document-downloads-report') {

        $table_name = $wpdb->prefix . 'frontend_doc_downloads';
        $downloadsListTable = new Frontend_Documents_Downloads_List_Table();
        $downloadsListTable->prepare_items();

    } else {

        $table_name = $wpdb->prefix . 'reports_downloads';
        $downloadsListTable = new downloads_List_Table();
        $downloadsListTable->prepare_items();

    }

    // when reset logs button clicked

    if ( isset( $_POST[ 'reset_documents_download_entries' ] ) ) {

        $query = "TRUNCATE $table_name";
        $result = $wpdb->query( $query );

        echo '<div id="message" class="updated fade"><p>';
            _e( 'Download log entries deleted!', 'reports' );
        echo '</p></div>';

        header('Location: ' . $_SERVER['REQUEST_URI']);

    }

    ?>    

    <h2><?php _e( 'Documents Downloads Logs', 'reports' ); ?></h2>

    <div class="reports-notification-wrapper">
        <p><?php _e( 'This page lists all tracked downloads for documents.', 'reports' ); ?></p>
    </div>

    <div id="poststuff">
        <div id="post-body">
            <div class="postbox">
                <h3 class="hndle"><label for="title"><?php _e( 'Reset Download Log Entries', 'reports' ); ?></label></h3>
                <div class="inside">
                    <form method="post" action="" onSubmit="return confirm('Are you sure you want to reset all the log entries?');" >
                        <div class="submit">
                            <input type="submit" class="button" name="reset_documents_download_entries" value="<?php _e( 'Reset Log Entries', 'reports' ); ?>" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form id="reports_downloads-filter" method="post">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST[ 'page' ] ) ?>" />
        <?php $downloadsListTable->display(); ?>
    </form>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.fade').click(function () {
                $(this).fadeOut('slow');
            });
        });
    </script>
    <?php
}

/**
 * Export documents views
 * It works for both custom post types (documents && frontend_documents)
 */

function export_documents_views() {

    global $wpdb;
    $file_to_download = '';
    $table_name = '';

    if ( isset( $_POST[ 'views_start_date' ] ) ) {
        $start_date = sanitize_text_field( $_POST[ 'views_start_date' ] );
    } else {
        // default start date is 30 days back
        $start_date = date( 'Y-m-d', time() - 60 * 60 * 24 * 30 );
    }

    if ( isset( $_POST[ 'views_end_date' ] ) ) {
        $end_date = sanitize_text_field( $_POST[ 'views_end_date' ] );
    } else {
        $end_date = date( 'Y-m-d', time() );
    }

    // check the page to define tablename

    if($_REQUEST['page'] == 'frontend-document-views-report') {

        $table_name = $wpdb->prefix . 'frontend_doc_views';

    } else {

        $table_name = $wpdb->prefix . 'reports_views';

    }

    // if all export button clicked

    if ( isset( $_POST[ 'export_all_views_entries' ] ) ) {

        $file_to_download = reports_export_logs_to_csv('', '', '', $table_name);

    } else if ( $_POST['export_by_company'] ) {

        $company_id = $_POST['company_dropdown'];

        $file_to_download = reports_export_logs_to_csv('', '', $company_id, $table_name);

    } else {

        // if start date and end date selected for reports

        $file_to_download = reports_export_logs_to_csv($start_date, $end_date, '', $table_name);

    }

    // notification message based on the document exported (ready to download button)

    if ( ( isset( $_POST[ 'views_start_date' ] ) && isset( $_POST[ 'views_end_date' ] ) ) || isset( $_POST[ 'export_all_views_entries' ] ) || isset( $_POST[ 'export_by_company' ] ) ) {

        echo '<div id="message" class="updated">';
            echo '<p>';
                _e( 'Log entries exported! Click on the below button to download the file.', 'reports' );
                echo '<br /><a class="file-download-btn" href="' . $file_to_download . '?nocache'.rand(1, 999999).'">' . __( 'Download Reports View Logs CSV File', 'reports' ) . '</a>';
            echo '</p>';
        echo '</div>';

    }

    ?>
    <div class="wrap">

        <h2><?php _e( 'Export Documents Views Logs', 'reports' ); ?></h2>

        <div class="reports-notification-wrapper">
            <p><?php _e( 'Use this page to export all tracked views for documents.', 'reports' ); ?></p>
        </div>

        <div id="poststuff">
            <div id="post-body">
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Export All Log Entries', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form method="post" action="" onSubmit="return confirm('Are you sure you want to export all the log entries?');" >
                            <div class="submit">
                                <input type="submit" class="button" name="export_all_views_entries" value="<?php _e( 'Export All Log Entries to CSV File', 'reports' ); ?>" />
                            </div>
                        </form>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Choose Date Range (yyyy-mm-dd)', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="views_choose_date" method="post">
                            <?php _e( 'Start Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="views_start_date" value="<?php echo $start_date; ?>">
                            <?php _e( 'End Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="views_end_date" value="<?php echo $end_date; ?>">
                            
                            <p id="views_date_selection_buttons">

                                <!-- today and yesterday buttons -->
                                
                                <button type="button" data-start-date="<?php echo date("Y-m-d"); ?>" data-end-date="<?php echo date("Y-m-d"); ?>"><?php _e( 'Today', 'reports' ); ?></button>
                                <button type="button" data-start-date="<?php echo date('Y-m-d',strtotime("-1 days")); ?>" data-end-date="<?php echo date('Y-m-d',strtotime("-1 days")); ?>"><?php _e( 'Yesterday', 'reports' ); ?></button>

                                <?php 
                                    $previous_week = strtotime("-1 week +1 day");

                                    $start_week = strtotime("last monday",$previous_week);
                                    $end_week = strtotime("next sunday",$start_week);

                                    $start_week = date("Y-m-d",$start_week);
                                    $end_week = date("Y-m-d",$end_week);
                                ?>

                                <!-- last week button -->

                                <button type="button" data-start-date="<?php echo $start_week; ?>" data-end-date="<?php echo $end_week; ?>"><?php _e( 'Last Week', 'reports' ); ?></button>
                                
                                <?php 
                                    $d = strtotime("today");
                                    $start_week = strtotime("last monday",$d);
                                    $start = date("Y-m-d",$start_week);
                                ?>

                                <!-- this week | last day of last month | this month | last year | this year -->

                                <button type="button" data-start-date="<?php echo $start; ?>" data-end-date="<?php echo date("Y-m-d"); ?>"><?php _e( 'This Week', 'reports' ); ?></button>
                                <button type="button" data-start-date="<?php echo date( 'Y-m-d', strtotime( 'first day of last month' ) ); ?>" data-end-date="<?php echo date( 'Y-m-d', strtotime( 'last day of last month' ) ); ?>"><?php _e( 'Last Month', 'reports' ); ?></button>
                                <button type="button" data-start-date="<?php echo date( 'Y-m-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>"><?php _e( 'This Month', 'reports' ); ?></button>
                                <button button type="button" data-start-date="<?php echo date( "Y-01-01", strtotime( "-1 year" ) ); ?>" data-end-date="<?php echo date( "Y-12-31", strtotime( 'last year' ) ); ?>"><?php _e( 'Last Year', 'reports' ); ?></button>
                                <button button type="button" data-start-date="<?php echo date( 'Y-01-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>"><?php _e( 'This Year', 'reports' ); ?></button>
                            
                            </p>

                            <div class="submit">
                                <input type="submit" class="button-primary" value="<?php _e( 'Generate Report', 'reports' ); ?>">
                            </div>

                        </form>
                    </div>
                </div>

                <?php if($_REQUEST['page'] == 'frontend-document-views-report' || $_REQUEST['page'] == 'frontend-document-downloads-report') { ?>

                    <!-- export based on company -->

                    <div class="postbox">
                        <h3 class="hndle">
                            <label for="title"><?php _e( 'Filters', 'reports' ); ?></label>
                        </h3>
                        <div class="inside">
                            <form id="documents-filters" method="post">
                                
                                <?php
                                
                                    $company_options = array(
                                        'show_option_all' => __("Select Company..."),
                                        'taxonomy'        => 'company',
                                        'name'            => 'company_dropdown',
                                        'orderby'         => 'name',
                                        'value_field' => 'id',
                                        'hierarchical' => 1,
                                        'hide_empty'      => 0,
                                        'class'              => 'company-dropdown',
                                    );
                            
                                    wp_dropdown_categories( $company_options );
                                
                                ?>

                                <div class="submit">
                                    <input type="submit" name="export_by_company" class="button-primary" value="<?php _e( 'Generate Report', 'reports' ); ?>">
                                </div>

                            </form>
                        </div>
                    </div>

                <?php } ?>

            </div>
        </div>
    </div>
    <script>
        jQuery('#views_date_selection_buttons button').click(function (e) {
            jQuery('#views_choose_date').find('input[name="views_start_date"]').val(jQuery(this).attr('data-start-date'));
            jQuery('#views_choose_date').find('input[name="views_end_date"]').val(jQuery(this).attr('data-end-date'));
        });
        jQuery(function () {
            jQuery('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd'
            });
        });
    </script>
<?php
}

/**
 * Export documents downloads
 * It works for both custom post types (documents && frontend_documents)
 */

function export_documents_downloads() {

    global $wpdb;
    $file_to_download = '';
    $table_name = '';

    if ( isset( $_POST[ 'downloads_start_date' ] ) ) {
        $start_date = sanitize_text_field( $_POST[ 'downloads_start_date' ] );
    } else {
        // default start date is 30 days back
        $start_date = date( 'Y-m-d', time() - 60 * 60 * 24 * 30 );
    }

    if ( isset( $_POST[ 'downloads_end_date' ] ) ) {
        $end_date = sanitize_text_field( $_POST[ 'downloads_end_date' ] );
    } else {
        $end_date = date( 'Y-m-d', time() );
    }

    // check the page to define tablename

    if($_REQUEST['page'] == 'frontend-document-downloads-report') {

        $table_name = $wpdb->prefix . 'frontend_doc_downloads';

    } else {

        $table_name = $wpdb->prefix . 'reports_downloads';

    }

    // if all export button clicked

    if ( isset( $_POST[ 'export_all_download_entries' ] ) ) {

        $file_to_download = reports_export_logs_to_csv('', '', '', $table_name);

    } else if ( $_POST['export_by_company'] ) {

        $company_id = $_POST['company_dropdown'];

        $file_to_download = reports_export_logs_to_csv('', '', $company_id, $table_name);

    } else {

        // if start date and end date selected for reports

        $file_to_download = reports_export_logs_to_csv($start_date, $end_date, '', $table_name);

    }

    // notification message based on the document exported (ready to download button)

    if ( ( isset( $_POST[ 'downloads_start_date' ] ) && isset( $_POST[ 'downloads_end_date' ] ) ) || isset( $_POST[ 'export_all_download_entries' ] ) || isset( $_POST[ 'export_by_company' ] ) ) {

        echo '<div id="message" class="updated">';
            echo '<p>';
                _e( 'Log entries exported! Click on the below button to download the file.', 'reports' );
                echo '<br /><a class="file-download-btn" href="' . $file_to_download . '?nocache'.rand(1, 999999).'">' . __( 'Download Reports View Logs CSV File', 'reports' ) . '</a>';
            echo '</p>';
        echo '</div>';

    }

    ?>
    <div class="wrap">

        <h2><?php _e( 'Export Documents Downloads Logs', 'reports' ); ?></h2>

        <div class="reports-notification-wrapper">
            <p><?php _e( 'Use this page to export all tracked views for document downloads.', 'reports' ); ?></p>
        </div>

        <div id="poststuff">
            <div id="post-body">
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Export All Log Entries', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form method="post" action="" onSubmit="return confirm('Are you sure you want to export all the log entries?');" >
                            <div class="submit">
                                <input type="submit" class="button" name="export_all_download_entries" value="<?php _e( 'Export All Log Entries to CSV File', 'reports' ); ?>" />
                            </div>
                        </form>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Choose Date Range (yyyy-mm-dd)', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="downloads_choose_date" method="post">
                            <?php _e( 'Start Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="downloads_start_date" value="<?php echo $start_date; ?>">
                            <?php _e( 'End Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="downloads_end_date" value="<?php echo $end_date; ?>">
                            
                            <p id="downloads_date_selection_buttons">

                                <!-- today and yesterday buttons -->
                                
                                <button type="button" data-start-date="<?php echo date("Y-m-d"); ?>" data-end-date="<?php echo date("Y-m-d"); ?>"><?php _e( 'Today', 'reports' ); ?></button>
                                <button type="button" data-start-date="<?php echo date('Y-m-d',strtotime("-1 days")); ?>" data-end-date="<?php echo date('Y-m-d',strtotime("-1 days")); ?>"><?php _e( 'Yesterday', 'reports' ); ?></button>

                                <?php 
                                    $previous_week = strtotime("-1 week +1 day");

                                    $start_week = strtotime("last monday",$previous_week);
                                    $end_week = strtotime("next sunday",$start_week);

                                    $start_week = date("Y-m-d",$start_week);
                                    $end_week = date("Y-m-d",$end_week);
                                ?>

                                <!-- last week button -->

                                <button type="button" data-start-date="<?php echo $start_week; ?>" data-end-date="<?php echo $end_week; ?>"><?php _e( 'Last Week', 'reports' ); ?></button>
                                
                                <?php 
                                    $d = strtotime("today");
                                    $start_week = strtotime("last monday",$d);
                                    $start = date("Y-m-d",$start_week);
                                ?>

                                <!-- this week | last day of last month | this month | last year | this year -->

                                <button type="button" data-start-date="<?php echo $start; ?>" data-end-date="<?php echo date("Y-m-d"); ?>"><?php _e( 'This Week', 'reports' ); ?></button>
                                <button type="button" data-start-date="<?php echo date( 'Y-m-d', strtotime( 'first day of last month' ) ); ?>" data-end-date="<?php echo date( 'Y-m-d', strtotime( 'last day of last month' ) ); ?>"><?php _e( 'Last Month', 'reports' ); ?></button>
                                <button type="button" data-start-date="<?php echo date( 'Y-m-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>"><?php _e( 'This Month', 'reports' ); ?></button>
                                <button button type="button" data-start-date="<?php echo date( "Y-01-01", strtotime( "-1 year" ) ); ?>" data-end-date="<?php echo date( "Y-12-31", strtotime( 'last year' ) ); ?>"><?php _e( 'Last Year', 'reports' ); ?></button>
                                <button button type="button" data-start-date="<?php echo date( 'Y-01-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>"><?php _e( 'This Year', 'reports' ); ?></button>
                            
                            </p>

                            <div class="submit">
                                <input type="submit" class="button-primary" value="<?php _e( 'Generate Report', 'reports' ); ?>">
                            </div>

                        </form>
                    </div>
                </div>

                <!-- export based on company -->

                <?php if( $_REQUEST['action'] == 'export-downloads' ): ?>

                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Filters', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="documents-filters" method="post">
                            
                            <?php
                            
                                $company_options = array(
                                    'show_option_all' => __("Select Company..."),
                                    'taxonomy'        => 'company',
                                    'name'            => 'company_dropdown',
                                    'orderby'         => 'name',
                                    'value_field' => 'id',
                                    'hierarchical' => 1,
                                    'hide_empty'      => 0,
                                    'class'              => 'company-dropdown',
                                );
                        
                                wp_dropdown_categories( $company_options );
                            
                            ?>

                            <div class="submit">
                                <input type="submit" name="export_by_company" class="button-primary" value="<?php _e( 'Generate Report', 'reports' ); ?>">
                            </div>

                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        jQuery('#downloads_date_selection_buttons button').click(function (e) {
            jQuery('#downloads_choose_date').find('input[name="downloads_start_date"]').val(jQuery(this).attr('data-start-date'));
            jQuery('#downloads_choose_date').find('input[name="downloads_end_date"]').val(jQuery(this).attr('data-end-date'));
        });
        jQuery(function () {
            jQuery('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd'
            });
        });
    </script>
<?php
}

function reports_handle_user_activity_export_tab_page() {

    global $wpdb;

    $views_table_name = $wpdb->prefix . 'reports_views';
    $downloads_table_name = $wpdb->prefix . 'reports_downloads';
    $activity_table_name = $wpdb->prefix . 'reports_activity';

    if ( isset( $_POST[ 'user_activity_start_date' ] ) ) {
        $start_date = sanitize_text_field( $_POST[ 'user_activity_start_date' ] );
    } else {
        // default start date is 30 days back
        $start_date = date( 'Y-m-d', time() - 60 * 60 * 24 * 30 );
    }

    if ( isset( $_POST[ 'user_activity_end_date' ] ) ) {
        $end_date = sanitize_text_field( $_POST[ 'user_activity_end_date' ] );
    } else {
        $end_date = date( 'Y-m-d', time() );
    }
    ?>
    <div class="wrap">

        <h2><?php _e( 'Export Users Activities Logs', 'reports' ); ?></h2>

        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        <p><?php _e( 'Use this page to export all tracked activity logs for users.', 'reports' ); ?></p>
        </div>

        <div id="poststuff">
            <div id="post-body" style="position:relative;">
                <div id="ajax-loader" style="display:none;">
                    <h3>This can take 20-25 minutes. Please do not close or reload this page. </h3>
                    <img id="loader" src="<?php echo plugin_dir_url( __FILE__ ); ?>images/ajax-loader.gif" />
                </div>
                <div class="postbox">
                    <!-- Log export button -->
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Export All Tracked Activity Log Entries', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form method="post" action="" onSubmit="return confirm('Are you sure you want to export all the log entries?');" >
                        <?php
                        if ( isset( $_POST[ 'reports_export_users_activity_logs' ] ) ) {
                            //Export log entries
                            $resultset = $wpdb->get_results("SELECT * FROM $activity_table_name ORDER BY activity_count DESC", OBJECT);

                            $csv_file_path = REPORTS_PATH . "reports-users-activity-logs.csv";
                            $fp = fopen($csv_file_path, 'w');

                            $header_names = array(
                                "Email",
                                "First Name",
                                "Last Name",
                                "Business Member",
                                "Corporate Facility",
                                "Admin Facility",
                                "T&T Access",
                                "P&P Access",
                                "Number of Documents Viewed",
                                //"Viewed Documents (Document ID | Document Title | Viewed Date)",
                                "Number of Documents Downloaded",
                                //"Downloaded Documents (Document ID | Document Title | Viewed Date)",
                            );

                            fputcsv($fp, $header_names);

                            foreach ($resultset as $result) {

                                $business_term = $corporate_term = $admin_term = '';

                                $user_meta = get_userdata($result->user_id);

                                $user_business_id = get_the_author_meta( 'business_companies', $result->user_id );
                                if (isset($user_business_id) && !empty($user_business_id)) {
                                    $business_term = get_term( $user_business_id, 'company' );
                                }

                                $user_corporate_id = get_the_author_meta( 'corporate_companies', $result->user_id );
                                if (isset($user_corporate_id) && !empty($user_corporate_id)) {
                                    $corporate_term = get_term( $user_corporate_id, 'company' );
                                }

                                $user_admin_id = get_the_author_meta( 'admin_companies', $result->user_id );
                                if (isset($user_admin_id) && !empty($user_admin_id)) {
                                    $admin_term = get_term( $user_admin_id, 'company' );
                                }

                                /**
                                * T&T and P&P getting for displaying in ListTable
                                */
                                $tools_templates_access = ucwords( esc_attr(get_user_meta($result->user_id, 'tools_templates_access', true)) );
                                $policies_procedures_access = ucwords( esc_attr(get_user_meta($result->user_id, 'policies_procedures_access', true)) );

                                if( empty( $tools_templates_access ) ) {
                                    update_user_meta($result->user_id, 'tools_templates_access', 'enable');
                                    $tools_templates_access = ucwords( esc_attr(get_user_meta($result->user_id, 'tools_templates_access', true)) );
                                }
                                if( empty( $policies_procedures_access ) ) {
                                    update_user_meta($result->user_id, 'policies_procedures_access', 'enable');
                                    $policies_procedures_access = ucwords( esc_attr(get_user_meta($result->user_id, 'policies_procedures_access', true)) );
                                }
                                
                                $fields = array(
                                    $user_meta->user_email, //"Email"
                                    $user_meta->first_name, //"First Name"
                                    $user_meta->last_name, //"Last Name"
                                    $business_term->name,
                                    $corporate_term->name,
                                    $admin_term->name,
                                    $tools_templates_access, //"T&T Access"
                                    $policies_procedures_access, // "P&P Access"
                                    $result->views_count, //"Number of Documents Viewed"
                                    //$views, //"Viewd Documents",
                                    $result->downloads_count, //"Number of Documents Downloaded",
                                    //$downloads,
                                );
                                fputcsv($fp, $fields);
                            }

                            fclose($fp);

                            $log_file_url = REPORTS_URL . '/reports-users-activity-logs.csv';
                            echo '<p style="color: green;">';
                                _e( 'Log entries exported! Click on the following button to download the file.', 'reports' );
                                echo '<br /><a class="file-download-btn" href="' . $log_file_url . '">' . __( 'Download Users Activity Logs CSV File', 'reports' ) . '</a>';
                            echo '</p>';
                        }
                        else { ?>
                            <div class="submit">
                                <input type="submit" class="button" name="reports_export_users_activity_logs" value="<?php _e( 'Export All Log Entries to CSV File', 'reports' ); ?>" />
                            </div>
                        <?php
                        }
                        ?>
                        </form>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Export by Date Range', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="user_activity_choose_date" method="post">
                            <input type="text" class="datepicker" name="user_activity_start_date" value="<?php echo $start_date; ?>">
                            <input type="text" class="datepicker" name="user_activity_end_date" value="<?php echo $end_date; ?>">
                            <p id="reports_view_date_buttons">
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
                                <button type="button" data-start-date="<?php echo date( "Y-01-01", strtotime( "-1 year" ) ); ?>" data-end-date="<?php echo date( "Y-12-31", strtotime( 'last year' ) ); ?>">Last Year</button>

                                <button type="button" data-start-date="<?php echo date( 'Y-01-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>">This Year</button>
                            </p>
                            <div class="submit">
                                <?php
                                    $count_activity_table = $wpdb->get_var("SELECT COUNT(*) FROM $activity_table_name");
                                ?>
                                <input type="hidden" name="user_activity_total_count" value="<?php echo $count_activity_table; ?>">
                                <input type="submit" id="user_activity_choose_date_button" class="button-primary" value="<?php _e( 'Generate Report', 'reports' ); ?>">
                            </div>
                            <div class="user_activity_date_result">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Export Activity Reports by Company', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="user_activity_filter_company" method="post">
                            <h4 style="margin-bottom: 5px;">Filter by Company</h4>
                            <?php
                                $post_type = 'users';
                                $taxonomy  = 'company';
                                $info_taxonomy = get_taxonomy($taxonomy);
                                
                                $selected = selected( $section,$value,false);
                                $options = wp_dropdown_categories(array(
                                    //'show_option_all' => __("Select Company..."),
                                    //'show_option_all'   => '',
                                    'show_option_none'  => "Select Company...",
                                    'option_none_value' => '',
                                    'taxonomy'        => $taxonomy,
                                    'name'            => 'company_export_activity',
                                    'orderby'         => 'name',
                                    'selected'        => $section,
                                    'value_field' => 'id',
                                    'hierarchical' => 1,
                                    'hide_empty'      => 0,
                                    'required'           => true

                                ));
                            ?>
                            <div>
                                <h4 style="margin-bottom: 0;">AND Date Range (yyyy-mm-dd)</h4>
                                <input type="text" placeholder="Start Date: " class="datepicker" name="activity_company_start_date" value="">
                                <input type="text" placeholder="End Date: " class="datepicker" name="activity_company_end_date" value="">
                                <select id="date_activity_company_list" style="margin-top: -5px;">
                                    <option>Select Date Filter</option>
                                    <option data-start-date="<?php echo date("Y-m-d"); ?>" data-end-date="<?php echo date("Y-m-d"); ?>">Today</option>
                                    <option data-start-date="<?php echo date('Y-m-d',strtotime("-1 days")); ?>" data-end-date="<?php echo date('Y-m-d',strtotime("-1 days")); ?>">Yesterday</option>
                                    <?php 
                                    $previous_week = strtotime("-1 week +1 day");

                                    $start_week = strtotime("last monday",$previous_week);
                                    $end_week = strtotime("next sunday",$start_week);

                                    $start_week = date("Y-m-d",$start_week);
                                    $end_week = date("Y-m-d",$end_week); 
                                    ?>
                                    <option  data-start-date="<?php echo $start_week; ?>" data-end-date="<?php echo $end_week; ?>">Last Week</option>
                                    <?php 
                                    $d = strtotime("today");
                                    $start_week = strtotime("last monday",$d);
                                    $start = date("Y-m-d",$start_week);
                                    ?>
                                    <option  data-start-date="<?php echo $start; ?>" data-end-date="<?php echo date("Y-m-d"); ?>">This Week</option>
                                    <option data-start-date="<?php echo date( 'Y-m-d', strtotime( 'first day of last month' ) ); ?>" data-end-date="<?php echo date( 'Y-m-d', strtotime( 'last day of last month' ) ); ?>">Last Month</option>
                                    <option data-start-date="<?php echo date( 'Y-m-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>">This Month</option>
                                    <option data-start-date="<?php echo date( "Y-01-01", strtotime( "-1 year" ) ); ?>" data-end-date="<?php echo date( "Y-31-12", strtotime( 'last year' ) ); ?>">Last Year</option>
                                    <option data-start-date="<?php echo date( 'Y-01-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>">This Year</option>
                                </select>
                            </div>
                            <div class="submit">
                                <input type="submit" class="button-primary" name="submit_activity_company" value="<?php _e( 'Generate Report', 'reports' ); ?>">
                            </div>
                            <?php 
                            if ($_POST[ 'submit_activity_company' ] ) {

                                $company_id = $_POST[ 'company_export_activity' ];

                                $args_company = new WP_User_Query( 
                                    array(
                                        'meta_query'    => array(
                                            'relation'  => 'OR',
                                            array( 
                                                'key'     => 'business_companies',
                                                'value'   => $company_id,
                                                'compare' => '='
                                            ),
                                            array(
                                                'key'     => 'corporate_companies',
                                                'value'   => $company_id,
                                                'compare' => '='
                                            ),
                                            array(
                                                'key'     => 'admin_companies',
                                                'value'   => $company_id,
                                                'compare' => '='
                                            )
                                        )
                                    ) 
                                );
                                $users = $args_company->get_results();
                                // Check for results
                                if (!empty($users)) {
                                    $numItems = count($users);
                                    if ( !empty( $_POST[ 'activity_company_start_date' ] ) && !empty( $_POST[ 'activity_company_end_date' ] )) {
                                        $activity_company_start_date = $_POST[ 'activity_company_start_date' ];
                                        $activity_company_end_date = $_POST[ 'activity_company_end_date' ];
                                        $activity_company_end_date = $activity_company_end_date ." 23:59:59";
                                        $company_query = "
                                        SELECT users.user_id as user_id,
                                        (SELECT count(*) FROM  $views_table_name as `views` WHERE `views`.user_id = users.user_id AND date_time>='$activity_company_start_date' AND date_time<='$activity_company_end_date') as `views_count`,
                                        (SELECT count(*) FROM $downloads_table_name as `downloads` WHERE `downloads`.user_id = users.user_id AND date_time>='$activity_company_start_date' AND date_time<='$activity_company_end_date') as `downloads_count`
                                        FROM $activity_table_name as `users` WHERE user_id IN (";
                                        $j = 0;
                                        foreach ($users as $user) {
                                            $company_query .= $user->ID;
                                            if(++$j !== $numItems) {
                                                $company_query .= ',';
                                            }
                                        }
                                        $company_query .= ")";
                                        $company_query .= " HAVING views_count+downloads_count<>0";
                                    } else {
                                        $company_query = "SELECT user_id,views_count,downloads_count,activity_count FROM `$activity_table_name` WHERE user_id IN (";
                                        $i = 0;
                                        foreach ($users as $user) {
                                            $company_query .= $user->ID;
                                            if(++$i !== $numItems) {
                                                $company_query .= ',';
                                            }
                                        }
                                        $company_query .= ")";
                                    }
                                    $resultset = $wpdb->get_results($company_query);
                                    if (!empty($resultset)) {
                                        $csv_file_path = REPORTS_PATH . "reports-users-activity-by-company.csv";
                                        $fp = fopen($csv_file_path, 'w');

                                        $header_names = array(
                                            "Email",
                                            "First Name",
                                            "Last Name",
                                            "Business Member",
                                            "Corporate Facility",
                                            "Admin Facility",
                                            "T&T Access",
                                            "P&P Access",
                                            "Number of Documents Viewed",
                                            "Viewed Documents (Document ID | Document Title | Viewed Date)",
                                            "Number of Documents Downloaded",
                                            "Downloaded Documents (Document ID | Document Title | Viewed Date)",
                                        );
                                        fputcsv($fp, $header_names);
                                        foreach ($resultset as $result) {

                                            $views = $downloads = $business_term = $corporate_term = $admin_term = '';

                                            if ( !empty( $_POST[ 'activity_company_start_date' ] ) && !empty( $_POST[ 'activity_company_end_date' ] )) {
                                                $viewsquery = $wpdb->prepare("SELECT user_id,post_id,date_time FROM " .$views_table_name."
                                                WHERE user_id = ".$result->user_id." AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')>=%s
                                                AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')<=%s", $activity_company_start_date, $activity_company_end_date);
                                            $viewsresult = $wpdb->get_results($viewsquery, OBJECT);                                   
                                            } else {
                                                $viewsresult = $wpdb->get_results("SELECT user_id,post_id,date_time FROM $views_table_name WHERE user_id = $result->user_id ORDER BY date_time desc", OBJECT);
                                            }


                                            foreach ($viewsresult as $viewed_documents) {
                                                $viewedtime = strtotime($viewed_documents->date_time);
                                                $viewedtitle = get_the_title($viewed_documents->post_id);

                                                if(get_post_status($viewed_documents->post_id) === FALSE){
                                                    $viewedtitle = 'Document Id: '.$viewed_documents->post_id .' (Deleted)';
                                                }


                                                $views .= $viewed_documents->post_id .' | '.$viewedtitle.' | '.date('Y-m-d', $viewedtime)."\r\n";
                                            }


                                            if ( !empty( $_POST[ 'activity_company_start_date' ] ) && !empty( $_POST[ 'activity_company_end_date' ] )) {
                                                $downloadssquery = $wpdb->prepare("SELECT user_id,post_id,date_time FROM " .$downloads_table_name."
                                                WHERE user_id = ".$result->user_id." AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')>=%s
                                                AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')<=%s", $activity_company_start_date, $activity_company_end_date);

                                            $downloadsresult = $wpdb->get_results($downloadssquery, OBJECT);                                   
                                            } else {
                                                $downloadsresult = $wpdb->get_results("SELECT user_id,post_id,date_time FROM $downloads_table_name WHERE user_id = $result->user_id ORDER BY date_time desc", OBJECT);
                                            }


                                            foreach ($downloadsresult as $downloaded_documents) {
                                                $downloadedtime = strtotime($downloaded_documents->date_time);
                                                $downloadedtitle = get_the_title($downloaded_documents->post_id);

                                                if(get_post_status($downloaded_documents->post_id) === FALSE){
                                                    $downloadedtitle = 'Document Id: '.$downloaded_documents->post_id .' (Deleted)';
                                                }

                                                $downloads .= $downloaded_documents->post_id .' | '.$downloadedtitle.' | '.date('Y-m-d', $downloadedtime)."\r\n";
                                            }

                                            $user_business_id = get_the_author_meta( 'business_companies', $result->user_id );
                                            if (isset($user_business_id) && !empty($user_business_id)) {
                                                $business_term = get_term( $user_business_id, 'company' );
                                            }

                                            $user_corporate_id = get_the_author_meta( 'corporate_companies', $result->user_id );
                                            if (isset($user_corporate_id) && !empty($user_corporate_id)) {
                                                $corporate_term = get_term( $user_corporate_id, 'company' );
                                            }

                                            $user_admin_id = get_the_author_meta( 'admin_companies', $result->user_id );
                                            if (isset($user_admin_id) && !empty($user_admin_id)) {
                                                $admin_term = get_term( $user_admin_id, 'company' );
                                            }
                                            $user_meta = get_userdata($result->user_id);
                                            
                                            /**
                                            * T&T and P&P getting for displaying in ListTable
                                            */
                                            $tools_templates_access = ucwords( esc_attr(get_user_meta($result->user_id, 'tools_templates_access', true)) );
                                            $policies_procedures_access = ucwords( esc_attr(get_user_meta($result->user_id, 'policies_procedures_access', true)) );

                                            if( empty( $tools_templates_access ) ) {
                                                update_user_meta($result->user_id, 'tools_templates_access', 'enable');
                                                $tools_templates_access = ucwords( esc_attr(get_user_meta($result->user_id, 'tools_templates_access', true)) );
                                            }
                                            if( empty( $policies_procedures_access ) ) {
                                                update_user_meta($result->user_id, 'policies_procedures_access', 'enable');
                                                $policies_procedures_access = ucwords( esc_attr(get_user_meta($result->user_id, 'policies_procedures_access', true)) );
                                            }

                                            $fields = array(
                                                $user_meta->user_email, //"Email"
                                                get_user_meta( $result->user_id, 'first_name', true ), //"First Name"
                                                get_user_meta( $result->user_id, 'last_name', true ), //"Last Name"
                                                $business_term->name,
                                                $corporate_term->name,
                                                $admin_term->name,
                                                $tools_templates_access, //"T&T Access"
                                                $policies_procedures_access, // "P&P Access"
                                                $result->views_count, //"Number of Documents Viewed"
                                                $views, //"Viewd Documents",
                                                $result->downloads_count, //"Number of Documents Downloaded",
                                                $downloads,
                                            );
                                            fputcsv($fp, $fields);

                                        }

                                        fclose($fp);

                                        $activity_by_company = REPORTS_URL . '/reports-users-activity-by-company.csv';
                                        echo '<p>';
                                        _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
                                        echo '<br /><a class="file-download-btn" href="' . $activity_by_company . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
                                        echo '</p>';
                                    } else {
                                        echo "<p style='color: red;margin: 0;'>No Activty Found</p>";
                                    }
                                } else {
                                    echo "<p style='color: red;margin: 0;'>No Users found</p>";
                                }
                            }
                            ?>
                        </form>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Get individual User Activity Reports', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="user_activity_filter_by_user" method="post">
                            Enter the Email Address of the user: <input type="email" name="reports_activity_user_email" value="" size="40" required>
                            <div>
                                <h4 style="margin-bottom: 0;">AND Date Range (yyyy-mm-dd)</h4>
                                <input type="text" placeholder="Start Date: " class="datepicker" name="activity_individual_start_date" value="">
                                <input type="text" placeholder="End Date: " class="datepicker" name="activity_individual_end_date" value="">
                                <select id="date_activity_individual_list" style="margin-top: -5px;">
                                    <option>Select Date Filter</option>
                                    <option data-start-date="<?php echo date("Y-m-d"); ?>" data-end-date="<?php echo date("Y-m-d"); ?>">Today</option>
                                    <option data-start-date="<?php echo date('Y-m-d',strtotime("-1 days")); ?>" data-end-date="<?php echo date('Y-m-d',strtotime("-1 days")); ?>">Yesterday</option>
                                    <?php 
                                    $previous_week = strtotime("-1 week +1 day");

                                    $start_week = strtotime("last monday",$previous_week);
                                    $end_week = strtotime("next sunday",$start_week);

                                    $start_week = date("Y-m-d",$start_week);
                                    $end_week = date("Y-m-d",$end_week); 
                                    ?>
                                    <option  data-start-date="<?php echo $start_week; ?>" data-end-date="<?php echo $end_week; ?>">Last Week</option>
                                    <?php 
                                    $d = strtotime("today");
                                    $start_week = strtotime("last monday",$d);
                                    $start = date("Y-m-d",$start_week);
                                    ?>
                                    <option  data-start-date="<?php echo $start; ?>" data-end-date="<?php echo date("Y-m-d"); ?>">This Week</option>
                                    <option data-start-date="<?php echo date( 'Y-m-d', strtotime( 'first day of last month' ) ); ?>" data-end-date="<?php echo date( 'Y-m-d', strtotime( 'last day of last month' ) ); ?>">Last Month</option>
                                    <option data-start-date="<?php echo date( 'Y-m-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>">This Month</option>
                                    <option data-start-date="<?php echo date( "Y-01-01", strtotime( "-1 year" ) ); ?>" data-end-date="<?php echo date( "Y-12-31", strtotime( 'last year' ) ); ?>">Last Year</option>
                                    <option data-start-date="<?php echo date( 'Y-01-01' ); ?>" data-end-date="<?php echo date( 'Y-m-d' ); ?>">This Year</option>
                                </select>
                            </div>
                            <div class="submit">
                                <input type="submit" class="button-primary"  name="export_activity_by_user_email" value="<?php _e( 'Generate Report', 'reports' ); ?>">
                            </div>
                            <?php 
                            if ($_POST[ 'export_activity_by_user_email' ] ) {

                                $user = get_user_by( 'email', $_POST[ 'reports_activity_user_email' ] );
                                if ($user) {

                                    if ( !empty( $_POST[ 'activity_individual_start_date' ] ) && !empty( $_POST[ 'activity_individual_end_date' ] )) {
                                        $activity_individual_start_date = $_POST[ 'activity_individual_start_date' ];
                                        $activity_individual_end_date = $_POST[ 'activity_individual_end_date' ];
                                        $activity_individual_end_date = $activity_individual_end_date ." 23:59:59";

                                        $resultset = $wpdb->get_results("
                                        SELECT users.user_id as user_id,
                                        (SELECT count(*) FROM  $views_table_name as `views` WHERE `views`.user_id = users.user_id AND date_time>='$activity_individual_start_date' AND date_time<='$activity_individual_end_date') as `views_count`,
                                        (SELECT count(*) FROM $downloads_table_name as `downloads` WHERE `downloads`.user_id = users.user_id AND date_time>='$activity_individual_start_date' AND date_time<='$activity_individual_end_date') as `downloads_count`
                                        FROM $activity_table_name as `users` WHERE user_id = '$user->ID' HAVING views_count+downloads_count<>0");

                                    } else {
                                        $resultset = $wpdb->get_results("SELECT user_id,views_count,downloads_count,activity_count FROM `$activity_table_name` WHERE user_id = '$user->ID'");

                                    }
                                    if (!empty($resultset)) {

                                        $csv_file_path = REPORTS_PATH . "reports-users-activity-by-email.csv";
                                        $fp = fopen($csv_file_path, 'w');

                                        $header_names = array(
                                            "Email",
                                            "First Name",
                                            "Last Name",
                                            "Business Member",
                                            "Corporate Facility",
                                            "Admin Facility",
                                            "T&T Access",
                                            "P&P Access",
                                            "Number of Documents Viewed",
                                            "Viewed Documents (Document ID | Document Title | Viewed Date)",
                                            "Number of Documents Downloaded",
                                            "Downloaded Documents (Document ID | Document Title | Viewed Date)",
                                        );
                                        fputcsv($fp, $header_names);
                                        foreach ($resultset as $result) {

                                            $views = $downloads = $business_term = $corporate_term = $admin_term = '';
                                            if ( !empty( $_POST[ 'activity_individual_start_date' ] ) && !empty( $_POST[ 'activity_individual_end_date' ] )) {

                                                $viewsquery = $wpdb->prepare("SELECT user_id,post_id,date_time FROM " .$views_table_name."
                                                    WHERE user_id = ".$result->user_id." AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')>=%s
                                                    AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')<=%s", $activity_individual_start_date, $activity_individual_end_date);
                                                $viewsresult = $wpdb->get_results($viewsquery, OBJECT);

                                            } else {
                                                $viewsresult = $wpdb->get_results("SELECT user_id,post_id,date_time FROM $views_table_name WHERE user_id = $user->ID ORDER BY date_time desc", OBJECT);

                                            }
                                            
                                            foreach ($viewsresult as $viewed_documents) {
                                                $viewedtime = strtotime($viewed_documents->date_time);
                                                $viewedtitle = get_the_title($viewed_documents->post_id);

                                                if(get_post_status($viewed_documents->post_id) === FALSE){
                                                    $viewedtitle = 'Document Id: '.$viewed_documents->post_id .' (Deleted)';
                                                }


                                                $views .= $viewed_documents->post_id .' | '.$viewedtitle.' | '.date('Y-m-d', $viewedtime)."\r\n";
                                            }
                                            if ( !empty( $_POST[ 'activity_individual_start_date' ] ) && !empty( $_POST[ 'activity_individual_end_date' ] )) {

                                                 $downloads_query_prepare = $wpdb->prepare("SELECT user_id,post_id,date_time FROM " .$downloads_table_name."
                                                    WHERE user_id = ".$result->user_id." AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')>=%s
                                                    AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')<=%s", $activity_individual_start_date, $activity_individual_end_date);
                                                $downloadssquery = $wpdb->get_results($downloads_query_prepare, OBJECT);

                                            } else {

                                                $downloadssquery = $wpdb->get_results("SELECT user_id,post_id,date_time FROM $downloads_table_name WHERE user_id = $user->ID ORDER BY date_time desc", OBJECT);
                                            }
                                            foreach ($downloadssquery as $downloaded_documents) {
                                                $downloadedtime = strtotime($downloaded_documents->date_time);
                                                $downloadedtitle = get_the_title($downloaded_documents->post_id);

                                                if(get_post_status($downloaded_documents->post_id) === FALSE){
                                                    $downloadedtitle = 'Document Id: '.$downloaded_documents->post_id .' (Deleted)';
                                                }

                                                $downloads .= $downloaded_documents->post_id .' | '.$downloadedtitle.' | '.date('Y-m-d', $downloadedtime)."\r\n";
                                            }

                                            $user_business_id = get_the_author_meta( 'business_companies', $result->user_id );
                                            if (isset($user_business_id) && !empty($user_business_id)) {
                                                $business_term = get_term( $user_business_id, 'company' );
                                            }

                                            $user_corporate_id = get_the_author_meta( 'corporate_companies', $result->user_id );
                                            if (isset($user_corporate_id) && !empty($user_corporate_id)) {
                                                $corporate_term = get_term( $user_corporate_id, 'company' );
                                            }

                                            $user_admin_id = get_the_author_meta( 'admin_companies', $result->user_id );
                                            if (isset($user_admin_id) && !empty($user_admin_id)) {
                                                $admin_term = get_term( $user_admin_id, 'company' );
                                            }
                                            $user_meta = get_userdata($result->user_id);
                                            
                                            /**
                                            * T&T and P&P getting for displaying in ListTable
                                            */
                                            $tools_templates_access = ucwords( esc_attr(get_user_meta($result->user_id, 'tools_templates_access', true)) );
                                            $policies_procedures_access = ucwords( esc_attr(get_user_meta($result->user_id, 'policies_procedures_access', true)) );

                                            if( empty( $tools_templates_access ) ) {
                                                update_user_meta($result->user_id, 'tools_templates_access', 'enable');
                                                $tools_templates_access = ucwords( esc_attr(get_user_meta($result->user_id, 'tools_templates_access', true)) );
                                            }
                                            if( empty( $policies_procedures_access ) ) {
                                                update_user_meta($result->user_id, 'policies_procedures_access', 'enable');
                                                $policies_procedures_access = ucwords( esc_attr(get_user_meta($result->user_id, 'policies_procedures_access', true)) );
                                            }

                                            $fields = array(
                                                $user_meta->user_email, //"Email"
                                                get_user_meta( $result->user_id, 'first_name', true ), //"First Name"
                                                get_user_meta( $result->user_id, 'last_name', true ), //"Last Name"
                                                $business_term->name,
                                                $corporate_term->name,
                                                $admin_term->name,
                                                $tools_templates_access, //"T&T Access"
                                                $policies_procedures_access, // "P&P Access"
                                                $result->views_count, //"Number of Documents Viewed"
                                                $views, //"Viewd Documents",
                                                $result->downloads_count, //"Number of Documents Downloaded",
                                                $downloads,
                                            );
                                            fputcsv($fp, $fields);

                                        }

                                        fclose($fp);

                                        $activity_by_email = REPORTS_URL . '/reports-users-activity-by-email.csv';
                                        echo '<p>';
                                        _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
                                        echo '<br /><a class="file-download-btn" href="' . $activity_by_email . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
                                        echo '</p>';
                                    } else {
                                        echo "<p style='color: red;margin: 0;'>No Activty Found</p>";
                                    }
                                } else {
                                    echo "<p style='color: red;margin: 0;'>Invalid Email or User doesn't exists</p>";
                                }
                            }
                            ?>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script>
        jQuery('#reports_view_date_buttons button').click(function (e) {
        jQuery('#user_activity_choose_date').find('input[name="user_activity_start_date"]').val(jQuery(this).attr('data-start-date'));
        jQuery('#user_activity_choose_date').find('input[name="user_activity_end_date"]').val(jQuery(this).attr('data-end-date'));
        });
        jQuery(function () {
        jQuery('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        });
        jQuery('#date_activity_company_list').change(function (e) {
            jQuery('#user_activity_filter_company').find('input[name="activity_company_start_date"]').val(jQuery(this).find(':selected').attr('data-start-date'));
            jQuery('#user_activity_filter_company').find('input[name="activity_company_end_date"]').val(jQuery(this).find(':selected').attr('data-end-date'));
        });
        jQuery('#date_activity_individual_list').change(function (e) {
            jQuery('#user_activity_filter_by_user').find('input[name="activity_individual_start_date"]').val(jQuery(this).find(':selected').attr('data-start-date'));
            jQuery('#user_activity_filter_by_user').find('input[name="activity_individual_end_date"]').val(jQuery(this).find(':selected').attr('data-end-date'));
        });
    </script>
<?php
}


function user_activity_date_range() {

    $start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : ''; 
    $end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';
    $end_date = $end_date . ' 23:59:59';
    $counter = isset($_REQUEST['loop_counter']) ? $_REQUEST['loop_counter'] : '';
    $activity_total_count = isset($_REQUEST['all_counter']) ? $_REQUEST['all_counter'] : '';
    $activity_total_count = $activity_total_count - 1;
    global $wpdb;
    $views_table_name = $wpdb->prefix . 'reports_views';
    $downloads_table_name = $wpdb->prefix . 'reports_downloads';
    $activity_table_name = $wpdb->prefix . 'reports_activity';

    $Results_per_page = 100; // Results per page

    $value = $Results_per_page * $counter;

    $users = $wpdb->get_results("SELECT user_id FROM $activity_table_name LIMIT $value,$Results_per_page");

    $numItems = count($users);

    $rs_query = "
    SELECT users.user_id as user_id,
    (SELECT count(*) FROM  $views_table_name as `views` WHERE `views`.user_id = users.user_id AND date_time>='$start_date' AND date_time<='$end_date') as `views_count`,
    (SELECT count(*) FROM $downloads_table_name as `downloads` WHERE `downloads`.user_id = users.user_id AND date_time>='$start_date' AND date_time<='$end_date') as `downloads_count`
    FROM $activity_table_name as `users` WHERE user_id IN (";
    $j = 0;
    foreach ($users as $user) {
        $rs_query .= $user->user_id;
        if(++$j !== $numItems) {
            $rs_query .= ',';
        }
    }
    $rs_query .= ") HAVING views_count+downloads_count<>0";

    $rs = $wpdb->get_results($rs_query);

    $cfp = REPORTS_PATH . "reports-users-activity-by-date-logs.csv";

    if ($counter == 0) {

        $fp = fopen($cfp, 'w');
        $header = array(
            "Email",
            "First Name",
            "Last Name",
            "Business Member",
            "Corporate Facility",
            "Admin Facility",
            "T&T Access",
            "P&P Access",
            "Number of Documents Viewed",
            "Number of Documents Downloaded",
        );
        fputcsv($fp, $header);

    } else {
        $fp = fopen($cfp, 'a');
    }

    foreach ($rs as $r) {

        if ( get_user_by( 'id', $r->user_id ) ) {

            $bus_t = $cor_t = $ad_t = '';
            $bus_id = get_the_author_meta( 'business_companies', $r->user_id );
            if (isset($bus_id) && !empty($bus_id)) {
                $bus_t = get_term( $bus_id, 'company' );
            }

            $cor_id = get_the_author_meta( 'corporate_companies', $r->user_id );
            if (isset($cor_id) && !empty($cor_id)) {
                $cor_t = get_term( $cor_id, 'company' );
            }

            $adm_id = get_the_author_meta( 'admin_companies', $r->user_id );
            if (isset($adm_id) && !empty($adm_id)) {
                $ad_t = get_term( $adm_id, 'company' );
            }

            $u_meta = get_userdata($r->user_id);
            
            /**
            * T&T and P&P getting for displaying in ListTable
            */
            $tools_templates_access = ucwords( esc_attr(get_user_meta($r->user_id, 'tools_templates_access', true)) );
            $policies_procedures_access = ucwords( esc_attr(get_user_meta($r->user_id, 'policies_procedures_access', true)) );

            if( empty( $tools_templates_access ) ) {
                update_user_meta($r->user_id, 'tools_templates_access', 'enable');
                $tools_templates_access = ucwords( esc_attr(get_user_meta($r->user_id, 'tools_templates_access', true)) );
            }
            if( empty( $policies_procedures_access ) ) {
                update_user_meta($r->user_id, 'policies_procedures_access', 'enable');
                $policies_procedures_access = ucwords( esc_attr(get_user_meta($r->user_id, 'policies_procedures_access', true)) );
            }
            
            $fields = array(
                $u_meta->user_email, //"Email"
                get_user_meta( $r->user_id, 'first_name', true ), //"First Name"
                get_user_meta( $r->user_id, 'last_name', true ), //"Last Name"
                $bus_t->name,
                $cor_t->name,
                $ad_t->name,
                $tools_templates_access, //"T&T Access"
                $policies_procedures_access, // "P&P Access"
                $r->views_count, //"Number of Documents Viewed"
                $r->downloads_count, //"Number of Documents Downloaded",
            );
        } else {
            $fields = array(
                'User Deleted', //"Email"
                'ID: '.$r->user_id, //"First Name"
                '', //"Last Name"
                '',
                '',
                '',
                '',
                '',
                $r->views_count, //"Number of Documents Viewed"
                $r->downloads_count, //"Number of Documents Downloaded",
            );

        }

        fputcsv($fp, $fields);

    }

    if ($counter == $activity_total_count) {

        fclose($fp);
/*        $timestamp = strtotime("now");
        $d_l = REPORTS_URL . '/reports-users-activity-by-date-logs.csv?nocache='.$timestamp;*/
        $response = '<p>Log entries exported! Click on the following link to download the file. <br /><a class="file-download-btn" href="' . REPORTS_URL . '/reports-users-activity-by-date-logs.csv?nocache='.strtotime("now") . '">Download Logs CSV File</a></p>';
        echo $response;

    }
    die; // leave ajax call

}
add_action('wp_ajax_user_activity_date_range', 'user_activity_date_range');


/**
 * Deleted documents log handler
 * Function called inside the "reports=admin-menu-handler.php"
 */

function deleted_documents_log_handler() {
    
    $deleted_documents_table = new Deleted_Documents_Logs();
    $deleted_documents_table->prepare_items();

    ?>

        <h2><?php _e( 'Deleted Documents Logs', 'reports' ); ?></h2>

        <div class="deleted-documents-logs-wrapper">
            <p><?php _e( 'This page lists all documents deleted from the frontend.', 'reports' ); ?></p>
        </div>

        <!-- Now we can render the completed list table -->
        <?php $deleted_documents_table->display(); ?>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
        $('.fade').click(function () {
            $(this).fadeOut('slow');
        });
        });
    </script>
    <?php
}

/**
 * Export document logs to CSV
 * @param $startDate: Start date selection
 * @param $endDate: End date selection
 * @param $tableName: Database table name based on the custom post type (documents || frontend_documents)
 * 
 */

function reports_export_logs_to_csv($startDate, $endDate, $company_id, $tableName) {

    global $wpdb;
    $fileName = '';
    $taxonomyName = '';
    $results = '';

    // query based on the selection (date or all export)

    if(!empty($startDate) && !empty($endDate)) {

        $query = $wpdb->prepare("SELECT *, COUNT(*) AS count FROM " . $tableName . "
            WHERE DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')>=%s
            AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')<=%s
            GROUP BY post_id ORDER BY count DESC", $startDate, $endDate);

        $results = $wpdb->get_results($query, OBJECT);

    } else if (!empty($company_id)) {

        $query = "SELECT *, COUNT(*) AS count FROM $tableName WHERE company_id= $company_id GROUP BY post_id ORDER BY count DESC";

        $term = get_term( $company_id, 'company' );
        $company_name = $term->slug;

        $fileName = $company_name . '-';

        $results = $wpdb->get_results("SELECT *, COUNT(*) AS count FROM $tableName WHERE company_id= $company_id GROUP BY post_id ORDER BY count DESC", OBJECT);

    } else {

        $results = $wpdb->get_results("SELECT *, COUNT(*) AS count FROM $tableName GROUP BY post_id ORDER BY count DESC", OBJECT);

    }

    // check the page before making export

    switch ($_REQUEST['page']) {

        case 'views-reports':
            $fileName .= 'reports-views-logs.csv';
            $taxonomyName = 'documents_category';
            $header_names = array(
                "Document ID",
                "Document Title",
                "Categories",
                "Number of Views"
            );
            break;
        case 'frontend-document-views-report':
            $fileName .= 'frontend-document-views-logs.csv';
            $taxonomyName = 'frontend_documents_category';
            $header_names = array(
                "Document ID",
                "Document Title",
                "Categories",
                "Number of Views"
            );
            break;
        case 'download-reports':
            $fileName .= 'reports-downloads-logs.csv';
            $taxonomyName = 'documents_category';
            $header_names = array(
                "Document ID",
                "Document Title",
                "Categories",
                "Number of Downloads"
            );
            break;
        case 'frontend-document-downloads-report':
            $fileName .= 'frontend-document-downloads-logs.csv';
            $taxonomyName = 'frontend_documents_category';
            $header_names = array(
                "Document ID",
                "Document Title",
                "Categories",
                "Number of Downloads"
            );
            break;

    }

    // write data to file

    $csv_file_path = REPORTS_PATH . $fileName;
    $fp = fopen($csv_file_path, 'w');

    fputcsv($fp, $header_names);

    if(!empty($results)) {

        foreach ($results as $document) {
    
            $count = 1;
            $categories = '';
    
            $terms = get_the_terms( $document->post_id , $taxonomyName );
    
            if( is_array($terms) ){
                foreach ( $terms as $term ) {
                    $categories .= $term->name;
                    $categories .= ( $count < count($terms) ) ? ", " : "";
                    $count++;
                }
            }
    
            $title = get_the_title($document->post_id);
    
            if(get_post_status($document->post_id) === FALSE){
                $title = 'Document Id: ' . $document->post_id . ' (Deleted)';
            }
    
            $fields = array(
                $document->post_id,
                $title,
                $categories,
                $document->count
            );
    
            fputcsv($fp, $fields);
        }

    }

    fclose($fp);

    // return file URL to download

    $file_url = REPORTS_URL . '/' . $fileName;

    return $file_url;

}

function reports_export_download_logs_to_csv() {

    global $wpdb;
    $table_name = $wpdb->prefix . 'reports_downloads';
    $resultset = $wpdb->get_results("SELECT *, COUNT(*) AS count FROM $table_name GROUP BY post_id ORDER BY count DESC", OBJECT);

    $csv_file_path = REPORTS_PATH . "reports-download-logs.csv";
    $fp = fopen($csv_file_path, 'w');

    $header_names = array("Document ID", "Document Title", "Categories", "Number of Downloads");
    fputcsv($fp, $header_names);

    foreach ($resultset as $result) {

        $post_title = get_the_title( $result->post_id );
        $terms = get_the_terms( $result->post_id , 'documents_category' );
        $i = 1;
        $cats = '';
        if(is_array($terms)){
            foreach ( $terms as $term ) {
                $cats .= $term->name;
                 // Increment counter
                $cats .= ($i < count($terms))? ", " : "";
                $i++;
            }
        }

        if(get_post_status($result->post_id) === FALSE){
            $post_title = 'Document Id: '.$result->post_id .' (Deleted)';
        }

        $fields = array($result->post_id, $post_title, $cats, $result->count );
        fputcsv($fp, $fields);
    }

    fclose($fp);

    $file_url = REPORTS_URL . '/reports-download-logs.csv';
    return $file_url;
}

function get_downloads_by_date($start_date = '', $end_date = '', $returnStr = true) {
    global $wpdb;

    $q = $wpdb->prepare("SELECT *, COUNT(*) AS count FROM " . $wpdb->prefix . "reports_downloads
            WHERE DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')>=%s
            AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')<=%s
            GROUP BY post_id ORDER BY count DESC", $start_date, $end_date);

    $res = $wpdb->get_results($q, OBJECT);
    $csv_file_path = REPORTS_PATH . "reports-download-logs.csv";
    $fp = fopen($csv_file_path, 'w');

    $header_names = array("Document ID", "Document Title", "Categories", "Number of Downloads");
    fputcsv($fp, $header_names);

    foreach ($res as $result) {

        $post_title = get_the_title( $result->post_id);
        $terms = get_the_terms( $result->post_id , 'documents_category' );
        $i = 1;
        $cats = '';
        if(is_array($terms)){
            foreach ( $terms as $term ) {
                $cats .= $term->name;
                 // Increment counter
                $cats .= ($i < count($terms))? ", " : "";
                $i++;
            }
        }

        if(get_post_status($result->post_id) === FALSE){
            $post_title = 'Document Id: '.$result->post_id .' (Deleted)';
        }

        $fields = array($result->post_id, $post_title, $cats, $result->count);
        fputcsv($fp, $fields);
    }

    fclose($fp);

    $file_url = REPORTS_URL . '/reports-download-logs.csv';
    return $file_url;
}

/**
 * Get document view report by id
 */

function get_document_view_report_by_id(){

    $action = '';
    $page = '';
    $viewsListTable = '';
    $documentType = '';

    echo '<h2>';
        _e( 'Specific View Item Logs', 'reports' );
    echo '</h2>';
    
    $document_id = isset($_REQUEST['document_id'])? sanitize_text_field($_REQUEST['document_id']): '';

    // check the page to define tablename

    if($_REQUEST['page'] == 'frontend-document-views-report') {

        $table_name = $wpdb->prefix . 'frontend_doc_views';
        $action = 'specific-item-report';
        $page = 'frontend-document-views-report';
        $documentType = 'Frontend Documents';

        $viewsListTable = new Frontend_Documents_Views_List_Table();

    } else {

        $table_name = $wpdb->prefix . 'reports_views';
        $action = 'reports-logs-by-views';
        $page = 'views-reports';
        $documentType = 'Documents';

        $viewsListTable = new views_List_Table();

    }

    // if submit button clicked
    
    if(isset($_REQUEST['view_specific_item_logs'])){
        if(!empty($document_id)){
            $target_url = 'admin.php?page='. $page .'&action=' . $action . '&document_id=' . $document_id;
            wp_redirect( $target_url );
            exit;
        }
    }
       
    ?>

    <div class="reports-notification-wrapper">
        <p><?php _e( 'This menu allows you to check view logs of individual items.', 'reports' ); ?></p>
    </div>

    <div id="poststuff">
        <div id="post-body">
            <div class="postbox">
                <h3 class="hndle"><label for="title"><?php _e( 'View Specific Item Logs', 'reports' ); ?></label></h3>
                <div class="inside">
                    <form method="post" action="" >
                        <?php _e('Enter the ID of the document: ', 'reports' ); ?>
                            <input type="text" name="document_id" value="<?php echo esc_attr($document_id); ?>" size="10" />
                        <p class='description'>
                            <?php _e('You can find the ID of an item from the ' . $documentType . ' menu.', 'reports' ); ?>
                        </p>
                        <div class="submit">
                            <input type="submit" class="button" name="view_specific_item_logs" value="<?php _e( 'View Logs', 'reports' ); ?>" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php

    // if input value is set, show table with records

    if(isset($document_id) && !empty($document_id)){
     
        $viewsListTable->prepare_items();

        echo '<strong>The following table shows the view logs of the document: ' . get_the_title($document_id) . '</strong>'
        ?>
            <form id="reports_downloads-filter" method="post">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST[ 'page' ] ) ?>" />
                <?php $viewsListTable->display(); ?>
            </form>
        <?php

    }

}

/**
 * Get document download report by id
 */

function get_document_download_report_by_id(){

    $page = '';
    $action = '';
    $downloadListTable = '';
    $documentType = '';

    echo '<h2>';
        _e( 'Specific Download Item Logs', 'reports' );
    echo '</h2>';
    
    $document_id = isset($_REQUEST['document_id'])? sanitize_text_field($_REQUEST['document_id']): '';

    // check the page to define tablename

    if($_REQUEST['page'] == 'frontend-document-downloads-report') {

        $table_name = $wpdb->prefix . 'frontend_doc_views';
        $action = 'specific-item-report';
        $page = 'frontend-document-downloads-report';
        $documentType = 'Frontend Documents';

        $downloadListTable = new Frontend_Documents_Downloads_List_Table();

    } else {

        $table_name = $wpdb->prefix . 'reports_views';
        $action = 'reports-logs-by-views';
        $page = 'views-reports';
        $documentType = 'Documents';

        $downloadListTable = new downloads_List_Table();

    }

    // if submit button clicked
    
    if(isset($_REQUEST['view_specific_item_logs'])){
        if(!empty($document_id)){
            $target_url = 'admin.php?page='. $page .'&action=' . $action . '&document_id=' . $document_id;
            wp_redirect( $target_url );
            exit;
        }
    }
       
    ?>

    <div class="reports-notification-wrapper">
        <p><?php _e( 'This menu allows you to check download logs of individual items.', 'reports' ); ?></p>
    </div>

    <div id="poststuff">
        <div id="post-body">
            <div class="postbox">
                <h3 class="hndle"><label for="title"><?php _e( 'View Specific Item Logs', 'reports' ); ?></label></h3>
                <div class="inside">
                    <form method="post" action="" >
                        <?php _e('Enter the ID of the document: ', 'reports' ); ?>
                            <input type="text" name="document_id" value="<?php echo esc_attr($document_id); ?>" size="10" />
                        <p class='description'>
                            <?php _e('You can find the ID of an item from the ' . $documentType . ' menu.', 'reports' ); ?>
                        </p>
                        <div class="submit">
                            <input type="submit" class="button" name="view_specific_item_logs" value="<?php _e( 'View Logs', 'reports' ); ?>" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php

    // if input value is set, show table with records

    if(isset($document_id) && !empty($document_id)){
     
        $downloadListTable->prepare_items();

        echo '<strong>The following table shows the download logs of the document: ' . get_the_title($document_id) . '</strong>'
        ?>
            <form id="reports_downloads-filter" method="post">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST[ 'page' ] ) ?>" />
                <?php $downloadListTable->display(); ?>
            </form>
        <?php

    }

}

function reports_search_results_main_tab_page() {
    global $wpdb;

    if ( isset( $_POST[ 'reports_reset_search_results' ] ) ) {
    //Reset log entries
    $table_name  = $wpdb->prefix . 'search_results';
    $query       = "TRUNCATE $table_name";
    $result      = $wpdb->query( $query );
    echo '<div id="message" class="updated fade"><p>';
    _e( 'Search Results entries deleted!', 'reports' );
    echo '</p></div>';
    }

    /* Display the logs table */
    //Create an instance of our package class...
    
    $searchedListTable = new searched_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $searchedListTable->prepare_items();
    ?>    

        <h2><?php _e( 'Searched Queries Logs', 'reports' ); ?></h2>

        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        <p><?php (!isset($_REQUEST['action']) || empty($_REQUEST['search_query'])) ? _e( 'This page lists all tracked searched queries.', 'reports' ) : _e( 'This page lists all tracked logs for <b>'.$_REQUEST['search_query'].'</b>.', 'reports' ) ; ?></p>
        </div>

        <?php if (!isset($_REQUEST['action']) && !isset($_REQUEST['search_query'])) { ?>
            <div id="poststuff">
                <div id="post-body">

                <!-- Log reset button -->
                <div class="postbox">
                <h3 class="hndle"><label for="title"><?php _e( 'Reset Searched Queries Log Entries', 'reports' ); ?></label></h3>
                <div class="inside">
                    <form method="post" action="" onSubmit="return confirm('Are you sure you want to reset all the log entries?');" >
                    <div class="submit">
                        <input type="submit" class="button" name="reports_reset_search_results" value="<?php _e( 'Reset Log Entries', 'reports' ); ?>" />
                    </div>
                    </form>
                </div>
                </div>

            </div>
        </div><!-- end of .poststuff and .post-body -->
        <?php } ?>

        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="searched_queries-filter" method="post">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST[ 'page' ] ) ?>" />
        <!-- Now we can render the completed list table -->
        <?php $searchedListTable->display() ?>
        </form>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
        $('.fade').click(function () {
            $(this).fadeOut('slow');
        });
        });
    </script>
    <?php
}

function reports_search_results_export_tab_page() {
    global $wpdb;


    if ( isset( $_POST[ 'reports_export_search_entries' ] ) ) {
        //Export log entries
        
        $log_file_url = reports_export_search_logs_to_csv();
        
        echo '<div id="message" class="updated"><p>';
        _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
        echo '<br /><a class="file-download-btn" href="' . $log_file_url . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
        echo '</p></div>';
    }

    if ( isset( $_POST[ 'search_stats_start_date' ] ) ) {
        $start_date = sanitize_text_field( $_POST[ 'search_stats_start_date' ] );
    } else {
        // default start date is 30 days back
        $start_date = date( 'Y-m-d', time() - 60 * 60 * 24 * 30 );
    }

    if ( isset( $_POST[ 'search_stats_end_date' ] ) ) {
        $end_date = sanitize_text_field( $_POST[ 'search_stats_end_date' ] );
    } else {
        $end_date = date( 'Y-m-d', time() );
    }
    ?>
    <div class="wrap">

        <h2><?php _e( 'Export Searched Query Logs', 'reports' ); ?></h2>

        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        <p><?php _e( 'Use this page to export all tracked searched queries', 'reports' ); ?></p>
        </div>

        <div id="poststuff">
            <div id="post-body">
                <div class="postbox">
                    <!-- Log export button -->
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Export All Search Log Entries', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form method="post" action="" onSubmit="return confirm('Are you sure you want to export all the log entries?');" >
                        <div class="submit">
                            <input type="submit" class="button" name="reports_export_search_entries" value="<?php _e( 'Export All Log Entries to CSV File', 'reports' ); ?>" />
                        </div>
                        </form>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Choose Date Range (yyyy-mm-dd)', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="search_choose_date" method="post">
                            <?php _e( 'Start Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="search_stats_start_date" value="<?php echo $start_date; ?>">
                            <?php _e( 'End Date: ', 'reports' ); ?><input type="text" class="datepicker" name="search_stats_end_date" value="<?php echo $end_date; ?>">
                            <p id="reports_search_date_buttons">
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
                            if ( isset( $_POST[ 'search_stats_start_date' ] ) && isset( $_POST[ 'search_stats_end_date' ] )) {
                                //Export log entries
                                $get_search_by_date = get_search_by_date($start_date, $end_date );
                                echo '<p>';
                                _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
                                echo '<br /><a class="file-download-btn" href="' . $get_search_by_date . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
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
        jQuery('#reports_search_date_buttons button').click(function (e) {
        jQuery('#search_choose_date').find('input[name="search_stats_start_date"]').val(jQuery(this).attr('data-start-date'));
        jQuery('#search_choose_date').find('input[name="search_stats_end_date"]').val(jQuery(this).attr('data-end-date'));
        });
        jQuery(function () {
        jQuery('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        });
    </script>
<?php
}


function reports_export_search_logs_to_csv() {

    global $wpdb;
    $table_name = $wpdb->prefix . 'search_results';
    $resultset = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date_time DESC", OBJECT);

    $csv_file_path = REPORTS_PATH . "reports-searched-logs.csv";
    $fp = fopen($csv_file_path, 'w');

    /*$header_names = array("Search ID", "Searched Query", "Date", "Visitor IP", "Affiliated Company");*/
    $header_names = array(
        "Email",
        "First Name",
        "Last Name",
        "User Status",
        "Role",
        "Business Member",
        "Corporate Facility",
        "Admin Facility",
        "Account Manager",
        "Searched Query",
        "T&T Access",
        "P&P Access",
        "Visitor IP",
        "Date",
    );
    fputcsv($fp, $header_names);

    foreach ($resultset as $result) {
        $business_term = $corporate_term = $admin_term = '';
        $user_meta = get_userdata($result->user_id);
        $user_role_array = $user_meta->roles;
        if ( isset( $user_role ) && is_array( $user_role_array ) ) {
            if (in_array('subscriber_-_facility_user', $user_meta->roles)) {
                $user_role = 'Subscriber - Facility User';
            }
            elseif (in_array('subscriber_-_admin_facility', $user_meta->roles)) {
                $user_role = 'Subscriber - Admin Facility';
            } elseif ( in_array( 'business', $user_meta->roles, true ) ) {
                $user_role = 'Business';
            } elseif ( in_array( 'backend-admin', $user_meta->roles, true ) ) {
                $user_role = 'Backend Admin';
            } elseif ( in_array( 'account_manager', $user_meta->roles, true ) ) {
                $user_role = 'Account Manager';
            }elseif ( in_array( 'administrator', $user_meta->roles, true ) ) {
                $user_role = 'Administrator';
            }
        } else {
            $user_role = '';
        }
        $status = ucwords(get_user_meta( $result->user_id, 'user_status', true ));

        $user_business_id = get_the_author_meta( 'business_companies', $result->user_id );
        if (isset($user_business_id) && !empty($user_business_id)) {
            $business_term = get_term( $user_business_id, 'company' );
        }

        $user_corporate_id = get_the_author_meta( 'corporate_companies', $result->user_id );
        if (isset($user_corporate_id) && !empty($user_corporate_id)) {
            $corporate_term = get_term( $user_corporate_id, 'company' );
        }

        $user_admin_id = get_the_author_meta( 'admin_companies', $result->user_id );
        if (isset($user_admin_id) && !empty($user_admin_id)) {
            $admin_term = get_term( $user_admin_id, 'company' );
        }

        /**
        * T&T and P&P getting for displaying in ListTable
        */
        
        $tools_templates_access = ucwords( esc_attr(get_user_meta($result->user_id, 'tools_templates_access', true)) );
        $policies_procedures_access = ucwords( esc_attr(get_user_meta($result->user_id, 'policies_procedures_access', true)) );

        if( empty( $tools_templates_access ) ) {
            update_user_meta($result->user_id, 'tools_templates_access', 'enable');
            $tools_templates_access = ucwords( esc_attr(get_user_meta($result->user_id, 'tools_templates_access', true)) );
        }
        if( empty( $policies_procedures_access ) ) {
            update_user_meta($result->user_id, 'policies_procedures_access', 'enable');
            $policies_procedures_access = ucwords( esc_attr(get_user_meta($result->user_id, 'policies_procedures_access', true)) );
        }
        
        if ( get_user_by( 'id', $result->user_id ) ) {
            $fields = array(
                $user_meta->user_email, //"Email"
                $user_meta->first_name, //"First Name"
                $user_meta->last_name, //"Last Name"
                $status,
                $user_role, //"Role"
                $business_term->name,
                $corporate_term->name,//"Corporate Facility"
                $admin_term->name, // Admin Facility 
                get_user_meta( $result->user_id, 'account_manager', true ), //"Account Manager"
                $result->searched_query, //"Searched Query"
                $tools_templates_access, //"T&T Access"
                $policies_procedures_access, //"P&P Access"
                $result->visitor_ip, //"Visitor IP"
                $result->date_time, //"Date
            );
        } else {
            $fields = array(
                'Deleted User (Id:'.$result->user_id.')', //"Email"
                '', //"First Name"
                '', //"Last Name"
                '',
                '', //"Role"
                '',
                '',//"Corporate Facility"
                '', 
                '', //"Account Manager"
                $result->searched_query, //"Searched Query"
                '', //"T&T Access"
                '', //"P&P Access"
                $result->visitor_ip, //"Visitor IP"
                $result->date_time, //"Date
            );
        }
        fputcsv($fp, $fields);
    }

    fclose($fp);

    $file_url = REPORTS_URL . '/reports-searched-logs.csv';
    return $file_url;
}

function get_search_by_date($start_date = '', $end_date = '', $returnStr = true) {
    global $wpdb;

    $q = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "search_results
            WHERE DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')>=%s
            AND DATE_FORMAT(`date_time`,'%%Y-%%m-%%d')<=%s
            ORDER BY date_time DESC", $start_date, $end_date);

    $res = $wpdb->get_results($q, OBJECT);
    $csv_file_path = REPORTS_PATH . "reports-searched-by-date-logs.csv";
    $fp = fopen($csv_file_path, 'w');

    $header_names = array(
        "Email",
        "First Name",
        "Last Name",
        "User Status",
        "Role",
        "Business Member",
        "Corporate Facility",
        "Admin Facility",
        "Account Manager",
        "Searched Query",
        "T&T Access",
        "P&P Access",
        "Visitor IP",
        "Date",
    );
    fputcsv($fp, $header_names);



    foreach ($res as $result) {
        $business_term = $corporate_term = $admin_term = '';
        $user_meta = get_userdata($result->user_id);
        $user_role_array = $user_meta->roles;

        if ( isset($user_role_array) && is_array($user_role_array) ) {
            if (in_array('subscriber_-_facility_user', $user_meta->roles)) {
                $user_role = 'Subscriber - Facility User';
            } elseif (in_array('subscriber_-_admin_facility', $user_meta->roles)) {
                $user_role = 'Subscriber - Admin Facility';
            } elseif ( in_array( 'business', $user_meta->roles, true ) ) {
                $user_role = 'Business';
            } elseif ( in_array( 'backend-admin', $user_meta->roles, true ) ) {
                $user_role = 'Backend Admin';
            } elseif ( in_array( 'account_manager', $user_meta->roles, true ) ) {
                $user_role = 'Account Manager';
            } elseif ( in_array( 'administrator', $user_meta->roles, true ) ) {
                $user_role = 'Administrator';
            }
        } else {
            $user_role = '';
        }
        $status = ucwords(get_user_meta( $result->user_id, 'user_status', true ));

        $user_business_id = get_the_author_meta( 'business_companies', $result->user_id );
        if (isset($user_business_id) && !empty($user_business_id)) {
            $business_term = get_term( $user_business_id, 'company' );
        }

        $user_corporate_id = get_the_author_meta( 'corporate_companies', $result->user_id );
        if (isset($user_corporate_id) && !empty($user_corporate_id)) {
            $corporate_term = get_term( $user_corporate_id, 'company' );
        }

        $user_admin_id = get_the_author_meta( 'admin_companies', $result->user_id );
        if (isset($user_admin_id) && !empty($user_admin_id)) {
            $admin_term = get_term( $user_admin_id, 'company' );
        }
        
        /**
        * T&T and P&P getting for displaying in ListTable
        */
        
        $tools_templates_access = ucwords( esc_attr(get_user_meta($result->user_id, 'tools_templates_access', true)) );
        $policies_procedures_access = ucwords( esc_attr(get_user_meta($result->user_id, 'policies_procedures_access', true)) );

        if( empty( $tools_templates_access ) ) {
            update_user_meta($result->user_id, 'tools_templates_access', 'enable');
            $tools_templates_access = ucwords( esc_attr(get_user_meta($result->user_id, 'tools_templates_access', true)) );
        }
        if( empty( $policies_procedures_access ) ) {
            update_user_meta($result->user_id, 'policies_procedures_access', 'enable');
            $policies_procedures_access = ucwords( esc_attr(get_user_meta($result->user_id, 'policies_procedures_access', true)) );
        }
        
        if ( get_user_by( 'id', $result->user_id ) ) {
            $fields = array(
                $user_meta->user_email, //"Email"
                $user_meta->first_name, //"First Name"
                $user_meta->last_name, //"Last Name"
                $status,
                $user_role, //"Role"
                $business_term->name,
                $corporate_term->name,//"Corporate Facility"
                $admin_term->name, 
                get_user_meta( $result->user_id, 'account_manager', true ), //"Account Manager"
                $result->searched_query, //"Searched Query"
                $tools_templates_access, //"T&T Access"
                $policies_procedures_access, //"P&P Access"
                $result->visitor_ip, //"Visitor IP"
                $result->date_time, //"Date
            );
        } else {
            $fields = array(
                'Deleted User (id:'.$result->user_id.')', //"Email"
                '', //"First Name"
                '', //"Last Name"
                '',
                '', //"Role"
                '',
                '',//"Corporate Facility"
                '', 
                '', //"Account Manager"
                $result->searched_query, //"Searched Query"
                $tools_templates_access, //"T&T Access"
                $policies_procedures_access, //"P&P Access"
                $result->visitor_ip, //"Visitor IP"
                $result->date_time, //"Date
            );
        }
        fputcsv($fp, $fields);
    }

    fclose($fp);

    $file_url = REPORTS_URL . '/reports-searched-by-date-logs.csv';
    return $file_url;
}
function reports_user_activity_reports() {
    /* Display the logs table */
    //Create an instance of our package class...
    
    $user_Activity_ListTable = new user_activity_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $user_Activity_ListTable->prepare_items();?>

        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="searched_queries-filter" method="post">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST[ 'page' ] ) ?>" />
        <!-- Now we can render the completed list table -->
        <?php $user_Activity_ListTable->display() ?>
        </form>


        <?php
    
}

// Categories walker extended for taxanomy archive sidebar
class Walker_Terms_Reports_Template extends Walker_Category {  

    function start_lvl(&$output, $depth=1, $args=array()) {  
        $output .= "";    
    }  

    function end_lvl(&$output, $depth=0, $args=array()) {  
        $output .= "";   
    } 
    function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        extract($args);

        $cat_name = esc_attr( $item->name );
        $cat_name = apply_filters( 'list_cats', $cat_name, $item );
        /*$link = 'href="' . esc_url( get_term_link($item) ) . '" ';*/
        $output .= $cat_name;
        $output .= ', ';

    }
    /*function end_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        $output .= " | ";
    }*/
}

function export_documents_by_date_posted() {
    global $wpdb;
    $post_type = 'documents';
    $posts_table = $wpdb->prefix . 'posts';

    if ( isset( $_POST[ 'export_all_documents' ] ) ) {
        //Export log entries
        $resultset = $wpdb->get_results("
            SELECT ID FROM $posts_table
            WHERE post_type = '$post_type'
            AND post_status =  'publish'
            ORDER BY post_date DESC", OBJECT
            );
        $csv_file_path = REPORTS_PATH . "all-documents-exported.csv";
        $fp = fopen($csv_file_path, 'w');

        $header_names = array("Date Posted", "Document ID", "Author", "Document Title", "Categories", "File URL", "Last Modified" );
        fputcsv($fp, $header_names);

        foreach ($resultset as $res) {                 
            // Get the term IDs assigned to post.
            $post_terms = wp_get_object_terms( $res->ID, 'documents_category', array( 'fields' => 'ids' ) );

            //last modified date and time 
            $last_modified_date_and_author = get_post_modified_time( 'F j, Y @ g:i a', false, $res->ID );
            $last_id = get_post_meta( $res->ID, '_edit_last', true );
            $last_modified_author = get_user_by( 'ID', $last_id );
            $last_modified_date_and_author .= ' by '.$last_modified_author->display_name;

            $cats = $path = '';
            if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
             
                $term_ids = implode( ',' , $post_terms );
             
                $terms = wp_list_categories( array(
                    'title_li' => '',
                    'style'    => '',
                    'echo'     => false,
                    'taxonomy' => 'documents_category',
                    'include'  => $term_ids,
                    'walker' => new Walker_Terms_Reports_Template()
                    
                ) );
             
                $cats .= rtrim( trim( str_replace( '<br />',  ', ', $terms ) ), ', ' );

            }


            $document = get_post_meta( $res->ID, 'bridge_document_document_file', true );
            $download = get_post_meta( $res->ID, 'bridge_document_document_download', true );
            $youtube_video = esc_url( get_post_meta( $res->ID, 'bridge_document_document_youtube', true ) );
            $gdrive = esc_url( get_post_meta( $res->ID, 'bridge_document_document_gdrive', true ) );
            $author_id = get_post_field ('post_author', $res->ID);
            $display_name = get_the_author_meta( 'display_name' , $author_id );

            if (!empty($download)) {
                $path = $download;
            } elseif (!empty($document)) {
                $path = $document;
            }
            elseif (!empty($youtube_video))  {
                $path = $youtube_video;
            } elseif (!empty($gdrive)) {
                $path = $gdrive;
            } else {
                $content = get_post_field('post_content', $res->ID);
                if ( preg_match('/<a (.+?)>/', $content, $match) ) {

                    $link = array();
                    foreach ( wp_kses_hair($match[1], array('https','http')) as $attr) {
                        $link[$attr['name']] = $attr['value'];
                    }
                    $path = $link['href'];
                }
            }
            $flds = array(get_the_date('m/d/Y', $res->ID), $res->ID, ucfirst($display_name), get_the_title($res->ID), $cats, $path,$last_modified_date_and_author );
            fputcsv($fp, $flds);
        }

        fclose($fp);

        $file_url = REPORTS_URL . '/all-documents-exported.csv';
        echo '<div id="message" class="updated"><p>';
        _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
        echo '<br /><a class="file-download-btn" href="' . $file_url . '">' . __( 'Download Reports View Logs CSV File', 'reports' ) . '</a>';
        echo '</p></div>';
    }
    if ( isset( $_POST[ 'documents_export_start_date' ] ) ) {
        $start_date = sanitize_text_field( $_POST[ 'documents_export_start_date' ] );
    } else {
        // default start date is 30 days back
        $start_date = date( 'Y-m-d', time() - 60 * 60 * 24 * 30 );
    }

    if ( isset( $_POST[ 'documents_export_end_date' ] ) ) {
        $end_date = sanitize_text_field( $_POST[ 'documents_export_end_date' ] );
    } else {
        $end_date = date( 'Y-m-d', time() );
    }
    ?>

    <div class="wrap">

        <h2><?php _e( 'Export Documents', 'reports' ); ?></h2>

        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        </div>

        <div id="poststuff">
            <div id="post-body">
                <div class="postbox">
                    <!-- Log export button -->
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Export All Documents', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form method="post" action="" onSubmit="return confirm('Are you sure you want to export all the documents?');" >
                        <div class="submit">
                            <input type="submit" class="button" name="export_all_documents" value="<?php _e( 'Export All Documents to CSV File', 'reports' ); ?>" />
                        </div>
                        </form>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Choose Date Range (yyyy-mm-dd)', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="documents_export_choose_date" method="post">
                            <?php _e( 'Start Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="documents_export_start_date" value="<?php echo $start_date; ?>">
                            <?php _e( 'End Date: ', 'reports' ); ?><input type="text" class="datepicker" name="documents_export_end_date" value="<?php echo $end_date; ?>">
                            <p id="export_documents_view_date_buttons">
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
                            if ( isset( $_POST[ 'documents_export_start_date' ] ) && isset( $_POST[ 'documents_export_end_date' ] )) {
                                $end_date = $end_date . ' 23:59:59';
                                //Export log entries

                                $q = $wpdb->prepare("SELECT ID FROM $posts_table
                                        WHERE post_date>=%s
                                        AND post_date<=%s
                                        AND post_type = '$post_type'
                                        AND post_status = 'publish'
                                        ORDER BY post_date DESC", $start_date, $end_date);                 

                                $res = $wpdb->get_results($q, OBJECT);


                                $csv_file_path = REPORTS_PATH . "documents-by-date-posted.csv";
                                $fp = fopen($csv_file_path, 'w');

                                $header_names = array("Date Posted", "Document ID", "Author", "Document Title", "Categories", "File URL", "Last Modified" );
                                fputcsv($fp, $header_names);

                                foreach ($res as $result) {

                                    // Get the term IDs assigned to post.
                                    $post_terms = wp_get_object_terms( $result->ID, 'documents_category', array( 'fields' => 'ids' ) );

                                    //last modified date and time 
                                    $last_modified_date_and_author = get_post_modified_time( 'F j, Y @ g:i a', false, $result->ID );
                                    $last_id = get_post_meta( $result->ID, '_edit_last', true );
                                    $last_modified_author = get_user_by( 'ID', $last_id );
                                    $last_modified_date_and_author .= ' by '.$last_modified_author->display_name;

                                    $cats = $path = '';
                                    if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
                                     
                                        $term_ids = implode( ',' , $post_terms );
                                     
                                        $terms = wp_list_categories( array(
                                            'title_li' => '',
                                            'style'    => '',
                                            'echo'     => false,
                                            'taxonomy' => 'documents_category',
                                            'include'  => $term_ids,
                                            'walker' => new Walker_Terms_Reports_Template()
                                            
                                        ) );
                                     
                                        $cats .= rtrim( trim( str_replace( '<br />',  ', ', $terms ) ), ', ' );

                                    }

                                    $document = get_post_meta( $result->ID, 'bridge_document_document_file', true );
                                    $download = get_post_meta( $result->ID, 'bridge_document_document_download', true );
                                    $youtube_video = esc_url( get_post_meta( $result->ID, 'bridge_document_document_youtube', true ) );
                                    $gdrive = esc_url( get_post_meta( $result->ID, 'bridge_document_document_gdrive', true ) );
                                    $author_id = get_post_field ('post_author', $result->ID);
                                    $display_name = get_the_author_meta( 'display_name' , $author_id ); 

                                    if (!empty($download)) {
                                        $path = $download;
                                    } elseif (!empty($document)) {
                                        $path = $document;
                                    }
                                    elseif (!empty($youtube_video))  {
                                        $path = $youtube_video;
                                    } elseif (!empty($gdrive)) {
                                        $path = $gdrive;
                                    } else {
                                        $content = get_post_field('post_content', $result->ID);
                                        if ( preg_match('/<a (.+?)>/', $content, $match) ) {

                                            $link = array();
                                            foreach ( wp_kses_hair($match[1], array('https','http')) as $attr) {
                                                $link[$attr['name']] = $attr['value'];
                                            }
                                            $path = $link['href'];
                                        }
                                    }

                                    $fields = array(get_the_date('m/d/Y', $result->ID), $result->ID, ucfirst($display_name), get_the_title($result->ID), $cats, $path, $last_modified_date_and_author );
                                    fputcsv($fp, $fields);
                                }

                                fclose($fp);

                                $documents_by_export_date = REPORTS_URL . "/documents-by-date-posted.csv";
                                echo '<p>';
                                _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
                                echo '<br /><a class="file-download-btn" href="' . $documents_by_export_date . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
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
        jQuery('#export_documents_view_date_buttons button').click(function (e) {
        jQuery('#documents_export_choose_date').find('input[name="documents_export_start_date"]').val(jQuery(this).attr('data-start-date'));
        jQuery('#documents_export_choose_date').find('input[name="documents_export_end_date"]').val(jQuery(this).attr('data-end-date'));
        });
        jQuery(function () {
        jQuery('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        });
    </script>

        <?php

}
function reports_user_kickout_reports() {
    /* Display the logs table */
    //Create an instance of our package class...
    
    $user_Kickout_ListTable = new user_kickout_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $user_Kickout_ListTable->prepare_items();?>

        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="kickout-filter" method="post">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST[ 'page' ] ) ?>" />
        <!-- Now we can render the completed list table -->
        <?php $user_Kickout_ListTable->display() ?>
        </form>


        <?php
    
}

function reports_handle_user_kickout_export_tab_page() {
    global $wpdb;

    $kickout_users_table_name = $wpdb->prefix . 'kickout_users';
    $orderby_column = "date_time";
    $sort_order = "DESC";


    if ( isset( $_POST[ 'reports_export_users_kickout_logs' ] ) ) {
        //Export log entries
        $resultset = $wpdb->get_results("SELECT * FROM $kickout_users_table_name ORDER BY $orderby_column $sort_order", OBJECT);

        $csv_file_path = REPORTS_PATH . "reports-users-kickout-logs.csv";
        $fp = fopen($csv_file_path, 'w');

        $header_names = array(
            "Email",
            "First Name",
            "Last Name",
            "User Status",
            "Role",
            "Business Member",
            "Corporate Facility",
            "Admin Facility",
            "Account Manager",
            "Visitor IP",
            "Web Browser",
            "Operating System",
            "Date",
        );
        fputcsv($fp, $header_names);

        foreach ($resultset as $result) {
            $business_term = $corporate_term = $admin_term = '';
            $user_meta = get_userdata($result->user_id);
            $user_roles_array = $user_meta->roles;

            if ( isset($user_roles_array) && is_array($user_roles_array) ) {
                if (in_array('subscriber_-_facility_user', $user_meta->roles)) {
                    $user_role = 'Subscriber - Facility User';
                } elseif (in_array('subscriber_-_admin_facility', $user_meta->roles)) {
                    $user_role = 'Subscriber - Admin Facility';
                } elseif ( in_array( 'business', $user_meta->roles, true ) ) {
                    $user_role = 'Business';
                } elseif ( in_array( 'backend-admin', $user_meta->roles, true ) ) {
                    $user_role = 'Backend Admin';
                } elseif ( in_array( 'account_manager', $user_meta->roles, true ) ) {
                    $user_role = 'Account Manager';
                }elseif ( in_array( 'administrator', $user_meta->roles, true ) ) {
                    $user_role = 'Administrator';
                }
            } else {
                $user_role = '';
            }
            $status = ucwords(get_user_meta( $result->user_id, 'user_status', true ));

            $user_business_id = get_the_author_meta( 'business_companies', $result->user_id );
            if (isset($user_business_id) && !empty($user_business_id)) {
                $business_term = get_term( $user_business_id, 'company' );
            }

            $user_corporate_id = get_the_author_meta( 'corporate_companies', $result->user_id );
            if (isset($user_corporate_id) && !empty($user_corporate_id)) {
                $corporate_term = get_term( $user_corporate_id, 'company' );
            }

            $user_admin_id = get_the_author_meta( 'admin_companies', $result->user_id );
            if (isset($user_admin_id) && !empty($user_admin_id)) {
                $admin_term = get_term( $user_admin_id, 'company' );
            }


            if ( get_user_by( 'id', $result->user_id ) ) {
                $fields = array(
                    $user_meta->user_email, //"Email"
                    $user_meta->first_name, //"First Name"
                    $user_meta->last_name, //"Last Name"
                    $status,
                    $user_role, //"Role"
                    $business_term->name,
                    $corporate_term->name,//"Corporate Facility"
                    $admin_term->name, 
                    get_user_meta( $result->user_id, 'account_manager', true ), //"Account Manager"
                    $result->visitor_ip, //"Visitor IP"
                    $result->browser, //"Web Browser
                    $result->OS, //"Operating System
                    $result->date_time, //"Date
                );
            } else {
                $fields = array(
                    'Deleted User (id:'.$result->user_id.')', //"Email"
                    '', //"First Name"
                    '', //"Last Name"
                    '',
                    '', //"Role"
                    '',
                    '',//"Corporate Facility"
                    '', 
                    '', //"Account Manager"
                    $result->visitor_ip, //"Visitor IP"
                    $result->browser, //"Web Browser
                    $result->OS, //"Operating System
                    $result->date_time, //"Date
                );
            }
            fputcsv($fp, $fields);
        }

        fclose($fp);

        $log_file_url = REPORTS_URL . '/reports-users-kickout-logs.csv';
        echo '<div id="message" class="updated"><p>';
        _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
        echo '<br /><a class="file-download-btn" href="' . $log_file_url . '">' . __( 'Download Users Kickout Logs CSV File', 'reports' ) . '</a>';
        echo '</p></div>';
    }

    if ( isset( $_POST[ 'user_kickout_start_date' ] ) ) {
        $start_date = sanitize_text_field( $_POST[ 'user_kickout_start_date' ] );
    } else {
        // default start date is 30 days back
        $start_date = date( 'Y-m-d', time() - 60 * 60 * 24 * 30 );
    }

    if ( isset( $_POST[ 'user_kickout_end_date' ] ) ) {
        $end_date = sanitize_text_field( $_POST[ 'user_kickout_end_date' ] );
    } else {
        $end_date = date( 'Y-m-d', time() );
    }
    ?>
    <div class="wrap">

        <h2><?php _e( 'Export Users Kickout Logs', 'reports' ); ?></h2>

        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        <p><?php _e( 'Use this page to export all tracked kickout logs for users.', 'reports' ); ?></p>
        </div>

        <div id="poststuff">
            <div id="post-body">
                <div class="postbox">
                    <!-- Log export button -->
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Export All Tracked Kickout Log Entries', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form method="post" action="" onSubmit="return confirm('Are you sure you want to export all the log entries?');" >
                        <div class="submit">
                            <input type="submit" class="button" name="reports_export_users_kickout_logs" value="<?php _e( 'Export All Log Entries to CSV File', 'reports' ); ?>" />
                        </div>
                        </form>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Choose Date Range (yyyy-mm-dd)', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="user_kickout_choose_date" method="post">
                            <?php _e( 'Start Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="user_kickout_start_date" value="<?php echo $start_date; ?>">
                            <?php _e( 'End Date: ', 'reports' ); ?><input type="text" class="datepicker" name="user_kickout_end_date" value="<?php echo $end_date; ?>">
                            <p id="reports_kickout_date_buttons">
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
                            if ( isset( $_POST[ 'user_kickout_start_date' ] ) && isset( $_POST[ 'user_kickout_end_date' ] )) {

                                $end_date = $end_date . ' 23:59:59';
                                //Export log entries

                                $q = $wpdb->prepare("SELECT * FROM $kickout_users_table_name
                                        WHERE date_time>=%s
                                        AND date_time<=%s
                                        ORDER BY $orderby_column $sort_order", $start_date, $end_date);

                                $resultset = $wpdb->get_results($q, OBJECT);


                                $csv_file_path = REPORTS_PATH . "reports-users-kickout-by-date-logs.csv";
                                $fp = fopen($csv_file_path, 'w');

                                $header_names = array(
                                    "Email",
                                    "First Name",
                                    "Last Name",
                                    "User Status",
                                    "Role",
                                    "Business Member",
                                    "Corporate Facility",
                                    "Admin Facility",
                                    "Account Manager",
                                    "Visitor IP",
                                    "Web Browser",
                                    "Operating System",
                                    "Date",
                                );
                                fputcsv($fp, $header_names);

                                foreach ($resultset as $result) {
                                    $business_term = $corporate_term = $admin_term = '';
                                    $user_meta = get_userdata($result->user_id);
                                    $user_roles_array = $user_meta->roles;

                                    if ( isset($user_roles_array) && is_array($user_meta->roles) ) {
                                        if (in_array('subscriber_-_facility_user', $user_meta->roles)) {
                                            $user_role = 'Subscriber - Facility User';
                                        } elseif (in_array('subscriber_-_admin_facility', $user_meta->roles)) {
                                            $user_role = 'Subscriber - Admin Facility';
                                        } elseif ( in_array( 'business', $user_meta->roles, true ) ) {
                                            $user_role = 'Business';
                                        } elseif ( in_array( 'backend-admin', $user_meta->roles, true ) ) {
                                            $user_role = 'Backend Admin';
                                        } elseif ( in_array( 'account_manager', $user_meta->roles, true ) ) {
                                            $user_role = 'Account Manager';
                                        } elseif ( in_array( 'administrator', $user_meta->roles, true ) ) {
                                            $user_role = 'Administrator';
                                        }
                                    } else {
                                        $user_role = '';
                                    }

                                    $status = ucwords(get_user_meta( $result->user_id, 'user_status', true ));

                                    $user_business_id = get_the_author_meta( 'business_companies', $result->user_id );
                                    if (isset($user_business_id) && !empty($user_business_id)) {
                                        $business_term = get_term( $user_business_id, 'company' );
                                    }

                                    $user_corporate_id = get_the_author_meta( 'corporate_companies', $result->user_id );
                                    if (isset($user_corporate_id) && !empty($user_corporate_id)) {
                                        $corporate_term = get_term( $user_corporate_id, 'company' );
                                    }

                                    $user_admin_id = get_the_author_meta( 'admin_companies', $result->user_id );
                                    if (isset($user_admin_id) && !empty($user_admin_id)) {
                                        $admin_term = get_term( $user_admin_id, 'company' );
                                    }

                                    if ( get_user_by( 'id', $result->user_id ) ) {
                                        $fields = array(
                                            $user_meta->user_email, //"Email"
                                            $user_meta->first_name, //"First Name"
                                            $user_meta->last_name, //"Last Name"
                                            $status,
                                            $user_role, //"Role"
                                            $business_term->name,
                                            $corporate_term->name,//"Corporate Facility"
                                            $admin_term->name, 
                                            get_user_meta( $result->user_id, 'account_manager', true ), //"Account Manager"
                                            $result->visitor_ip, //"Visitor IP"
                                            $result->browser, //"Web Browser
                                            $result->OS, //"Operating System
                                            $result->date_time, //"Date
                                        );
                                    } else {
                                        $fields = array(
                                            'Deleted User (id:'.$result->user_id.')', //"Email"
                                            '', //"First Name"
                                            '', //"Last Name"
                                            '',
                                            '', //"Role"
                                            '',
                                            '',//"Corporate Facility"
                                            '', 
                                            '', //"Account Manager"
                                            $result->visitor_ip, //"Visitor IP"
                                            $result->browser, //"Web Browser
                                            $result->OS, //"Operating System
                                            $result->date_time, //"Date
                                        );
                                    }
                                    fputcsv($fp, $fields);
                                }

                                fclose($fp);

                                $activity_by_date_logs = REPORTS_URL . '/reports-users-kickout-by-date-logs.csv';
                                echo '<p>';
                                _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
                                echo '<br /><a class="file-download-btn" href="' . $activity_by_date_logs . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
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
        jQuery('#reports_kickout_date_buttons button').click(function (e) {
        jQuery('#user_kickout_choose_date').find('input[name="user_kickout_start_date"]').val(jQuery(this).attr('data-start-date'));
        jQuery('#user_kickout_choose_date').find('input[name="user_kickout_end_date"]').val(jQuery(this).attr('data-end-date'));
        });
        jQuery(function () {
        jQuery('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        });
    </script>
    <?php
}

function reports_handle_documents_last_modified_export_tab_page() {
    global $wpdb;

    if( isset($_POST['reports_export_documents_last_modified_logs']) ) {
        $log_file_url = reports_handle_documents_last_modified_export_logs_csv();
        echo '<div id="message" class="updated"><p>';
        if ($log_file_url) {
            _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
            echo '<br /><a class="file-download-btn" href="' . $log_file_url . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
        } else {
            echo '<span style="color: red;">'.__( 'No record found', 'reports' ).'</span>';
        }
        echo '</p></div>';
    }
    
    if ( isset( $_POST[ 'documents_last_modified_start_date' ] ) ) {
        $start_date = sanitize_text_field( $_POST[ 'documents_last_modified_start_date' ] );
    } else {
        // default start date is 30 days back
        $start_date = date( 'Y-m-d', time() - 60 * 60 * 24 * 30 );
    }

    if ( isset( $_POST[ 'documents_last_modified_end_date' ] ) ) {
        $end_date = sanitize_text_field( $_POST[ 'documents_last_modified_end_date' ] );
    } else {
        $end_date = date( 'Y-m-d', time() );
    }
    
    ?>
    <div class="wrap">

        <h2><?php _e( 'Export Documents Last Modified', 'reports' ); ?></h2>

        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        <p><?php _e( 'Use this page to export all documents last modified logs.', 'reports' ); ?></p>
        </div>

        <div id="poststuff">
            <div id="post-body">
                <div class="postbox">
                    <!-- Log export button -->
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Export All Tracked Documents Last Modified Log Entries', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form method="post" action="" onSubmit="return confirm('Are you sure you want to export all the log entries?');" >
                        <div class="submit">
                            <input type="submit" class="button" name="reports_export_documents_last_modified_logs" value="<?php _e( 'Export All Log Entries to CSV File', 'reports' ); ?>" />
                        </div>
                        </form>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Choose Date Range (yyyy-mm-dd)', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="documents_last_modified_choose_date" method="post">
                            <?php _e( 'Start Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="documents_last_modified_start_date" value="<?php echo $start_date; ?>">
                            <?php _e( 'End Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="documents_last_modified_end_date" value="<?php echo $end_date; ?>">
                            <p id="reports_documents_last_modified_date_buttons">
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
                            if ( isset( $_POST[ 'documents_last_modified_start_date' ] ) && isset( $_POST[ 'documents_last_modified_end_date' ] )) {
                                //Export log entries
                                $get_last_modified_export_logs_by_date = reports_handle_documents_last_modified_export_logs_csv($start_date, $end_date );
                                if ($get_last_modified_export_logs_by_date) {
                                    echo '<p>';
                                    _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
                                    echo '<br /><a class="file-download-btn" href="' . $get_last_modified_export_logs_by_date . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
                                    echo '</p>';
                                } else {
                                    echo '<span style="color: red;">'.__( 'No record found', 'reports' ).'</span>';
                                }
                            }
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        jQuery('#reports_documents_last_modified_date_buttons button').click(function (e) {
        jQuery('#documents_last_modified_choose_date').find('input[name="documents_last_modified_start_date"]').val(jQuery(this).attr('data-start-date'));
        jQuery('#documents_last_modified_choose_date').find('input[name="documents_last_modified_end_date"]').val(jQuery(this).attr('data-end-date'));
        });
        jQuery(function () {
        jQuery('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        });
    </script>
    <?php
}

function reports_handle_documents_last_modified_export_logs_csv($start_date = '', $end_date = '') {
    global $wpdb; 
    $post_type = 'documents';
    $posts_table = $wpdb->prefix . 'posts';
    $query = "SELECT ID, post_title, post_modified FROM $posts_table WHERE post_type = '$post_type' AND post_status = 'publish' ";
    if ( !empty($start_date) && !empty($end_date) ) {
        $query = $wpdb->prepare(
            $query."AND DATE_FORMAT(`post_modified`,'%%Y-%%m-%%d')>=%s
            AND DATE_FORMAT(`post_modified`,'%%Y-%%m-%%d')<=%s ", $start_date, $end_date
        );
    }
    $query .= " ORDER BY post_modified DESC";
    $posts = $wpdb->get_results($query, OBJECT);

    $csv_file_path = REPORTS_PATH . "reports-document-last-modified-logs.csv";
    $csv_file = fopen($csv_file_path, 'w');

    $header_names = array(
        "Document Last Modified",
        "Document ID",
        "Document Title",
        "Categories",
        "File URL",
    );
    fputcsv($csv_file, $header_names);
    
    foreach ($posts as $post) {
        $post_modified_date = $post->post_modified;
        $post_modified_date = date("F j, Y @ g:i a", strtotime($post_modified_date));

        $author_id = get_post_meta( $post->ID, '_edit_last', true );
        $user_meta = get_userdata($author_id);

        $post_terms = wp_get_object_terms( $post->ID, 'documents_category', array( 'fields' => 'ids' ) );

        $cats = '';
        if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {            
            $term_ids = implode( ',' , $post_terms );
            
            $terms = wp_list_categories( array(
                'title_li' => '',
                'style'    => '',
                'echo'     => false,
                'taxonomy' => 'documents_category',
                'include'  => $term_ids,
                'walker' => new Walker_Terms_Reports_Template()
                
            ) );
            $cats .= rtrim( trim( str_replace( '<br />',  ', ', $terms ) ), ', ' );
        }

        $document = get_post_meta( $post->ID, 'bridge_document_document_file', true );
        $download = get_post_meta( $post->ID, 'bridge_document_document_download', true );

        $path = '';
        if (!empty($document)) {
            if (!empty($download)) :
                $path = $download;
            else :
                $path = $document;
            endif;
        }

        $fields = array( 
            $post_modified_date.' by '.$user_meta->display_name, 
            $post->ID, 
            $post->post_title, 
            $cats, 
            $path 
        );
        fputcsv($csv_file, $fields);
    }
    fclose($csv_file);
    if ( count($posts) < 1 ) {
        return false;
    }
    $file_url = REPORTS_URL . '/reports-document-last-modified-logs.csv';
    return $file_url;
}

function reports_handle_company_last_modified_export_tab_page() {
    if( isset($_POST['reports_export_company_last_modified_logs']) ) {
        $log_file_url = reports_handle_companys_last_modified_export_logs_csv();
        echo '<div id="message" class="updated"><p>';
        if ($log_file_url) {
            _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
            echo '<br /><a class="file-download-btn" href="' . $log_file_url . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
        } else {
            echo '<span style="color: red;">'.__( 'No record found', 'reports' ).'</span>';
        }
        echo '</p></div>';
    }
    
    if ( isset( $_POST[ 'company_last_modified_start_date' ] ) ) {
        $start_date = sanitize_text_field( $_POST[ 'company_last_modified_start_date' ] );
    } else {
        // default start date is 30 days back
        $start_date = date( 'Y-m-d', time() - 60 * 60 * 24 * 30 );
    }

    if ( isset( $_POST[ 'company_last_modified_end_date' ] ) ) {
        $end_date = sanitize_text_field( $_POST[ 'company_last_modified_end_date' ] );
    } else {
        $end_date = date( 'Y-m-d', time() );
    }
    
    ?>
    <div class="wrap">

        <h2><?php _e( 'Export Company Last Modified', 'reports' ); ?></h2>

        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        <p><?php _e( 'Use this page to export all company last modified logs.', 'reports' ); ?></p>
        </div>

        <div id="poststuff">
            <div id="post-body">
                <div class="postbox">
                    <!-- Log export button -->
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Export All Tracked Company Last Modified Log Entries', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form method="post" action="" onSubmit="return confirm('Are you sure you want to export all the log entries?');" >
                        <div class="submit">
                            <input type="submit" class="button" name="reports_export_company_last_modified_logs" value="<?php _e( 'Export All Log Entries to CSV File', 'reports' ); ?>" />
                        </div>
                        </form>
                    </div>
                </div>
                <div class="postbox">
                    <h3 class="hndle">
                        <label for="title"><?php _e( 'Choose Date Range (yyyy-mm-dd)', 'reports' ); ?></label>
                    </h3>
                    <div class="inside">
                        <form id="company_last_modified_choose_date" method="post">
                            <?php _e( 'Start Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="company_last_modified_start_date" value="<?php echo $start_date; ?>">
                            <?php _e( 'End Date: ', 'reports' ); ?>
                                <input type="text" class="datepicker" name="company_last_modified_end_date" value="<?php echo $end_date; ?>">
                            <p id="reports_company_last_modified_date_buttons">
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
                            if ( isset( $_POST[ 'company_last_modified_start_date' ] ) && isset( $_POST[ 'company_last_modified_end_date' ] )) {
                                //Export log entries
                                $get_last_modified_export_logs_by_date = reports_handle_companys_last_modified_export_logs_csv($start_date, $end_date );
                                if ($get_last_modified_export_logs_by_date) {
                                    echo '<p>';
                                    _e( 'Log entries exported! Click on the following link to download the file.', 'reports' );
                                    echo '<br /><a class="file-download-btn" href="' . $get_last_modified_export_logs_by_date . '">' . __( 'Download Logs CSV File', 'reports' ) . '</a>';
                                    echo '</p>';
                                } else {
                                    echo '<span style="color: red;">No record found</span>';
                                }
                            }
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        jQuery('#reports_company_last_modified_date_buttons button').click(function (e) {
        jQuery('#company_last_modified_choose_date').find('input[name="company_last_modified_start_date"]').val(jQuery(this).attr('data-start-date'));
        jQuery('#company_last_modified_choose_date').find('input[name="company_last_modified_end_date"]').val(jQuery(this).attr('data-end-date'));
        });
        jQuery(function () {
        jQuery('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        });
    </script>
    <?php
}

function reports_handle_companys_last_modified_export_logs_csv($start_date = '', $end_date = '') {
    $args = array(
        'taxonomy' => 'company',
        'meta_key' => 'company_last_edit',
        'orderby' => 'company_last_edit',
        'order' => 'DESC',
        'hide_empty' => false,
    );
    if ( !empty($start_date) && !empty($end_date) ) {
        $args['meta_query'] = array(
            'relation' => 'AND',
            array(
                'key'     => 'company_last_edit',
                'value'   => array( $start_date, $end_date ),
                'compare' => 'BETWEEN',
                'type' => 'DATE',
            ),
        );
    }
    
    $csv_file_path = REPORTS_PATH . "reports-company-last-modified-logs.csv";
    $csv_file = fopen($csv_file_path, 'w');

    $header_names = array(
        "Company Name",
        "State",
        "Company Status",
        "Last Modified on",
    );
    fputcsv($csv_file, $header_names);

    $terms = get_terms( $args );
    foreach ($terms as $term) {
        $term_last_modified = get_term_meta( $term->term_id, 'company_last_edit', true);
        $term_last_by = get_term_meta( $term->term_id, 'company_edit_by', true);
        $term_state = get_term_meta( $term->term_id, 'users_comapny_state', true );
        $term_status = get_term_meta( $term->term_id, 'users_comapny_company_status', true );
        if ( empty($term_status) ) {
            $term_status = 'none';
        }

        $term_last_modified = date("F j, Y @ g:i a", strtotime($term_last_modified));
        $user_meta = get_userdata($term_last_by);

        $fields = array( 
            $term->name ,
            $term_state ,
            $term_status ,
            $term_last_modified." by ".$user_meta->display_name, 
        );
        fputcsv($csv_file, $fields);
    }
    fclose($csv_file);
    if ( count($terms) < 1 ) {
        return false;
    }
    $file_url = REPORTS_URL . '/reports-company-last-modified-logs.csv';
    return $file_url;
}

function frontend_documents_custom_filters($post_type, $which) {

    print_r($_REQUEST['page']);
    die();

    global $post_type;
    $screen = get_current_screen();

    if( $_REQUEST['page'] == 'frontend-document-views-report' ){
        //Get selected company
        if ( isset( $_GET[ 'company_top' ]) ) {
            $company_section = $_GET[ 'company_top' ];
        } elseif ( isset( $_GET[ 'company_bottom' ]) ) {
            $company_section = $_GET[ 'company_bottom' ];
        } else {
            $company_section = '';
        }
        
        //Get selected company value
        // $selected = selected( $company_section,$value,false);

        //display companies dropdown
        $company_options = wp_dropdown_categories(array(
            'show_option_all' => __("Company..."),
            'taxonomy'        => 'company'
        ));

        print_r($company_options);
        die();

    }

}