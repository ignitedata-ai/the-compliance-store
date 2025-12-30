<?php 
/* User Object */
global $user_details;

$user_id = $user_details->ID;

$user_business_id = get_the_author_meta( 'business_companies', $user_id );
if (isset($user_business_id) && !empty($user_business_id)) {
    $business_term = get_term( $user_business_id, 'company' );
    $business = $business_term->name;
} else {
    $business =  'Not set yet';
}
$user_corporate_id = get_the_author_meta( 'corporate_companies', $user_id );
if (isset($user_corporate_id) && !empty($user_corporate_id)) {
    $corporate_term = get_term( $user_corporate_id, 'company' );
    $corporate_facility = $corporate_term->name;
} else {
    $corporate_facility =  'Not set yet';
}

$user_admin_id = get_the_author_meta( 'admin_companies', $user_id );
if (isset($user_admin_id) && !empty($user_admin_id)) {
    $admin_term = get_term( $user_admin_id, 'company' );
    $admin_facility =  $admin_term->name;
} else {
    $admin_facility =  'Not set yet';
}

$title = get_user_meta($user_id, 'title', true);
$fname = $user_details->user_firstname;
$lname = $user_details->user_lastname;
$work_phone = get_user_meta($user_id, 'work_phone', true);
$uEmail = $user_details->user_email;

if(get_user_meta($user_id, 'street_1', true) != '' 
        || get_user_meta($user_id, 'city', true) != ''
        || get_user_meta($user_id, 'state', true) != ''
        || get_user_meta($user_id, 'zip_code', true) != '') {
    $user_address = get_user_meta($user_id, 'street_1', true)."<br>"
        . get_user_meta($user_id, 'street_2', true)
        . get_user_meta($user_id, 'city', true).", "
        . get_user_meta($user_id, 'state', true)." - "
        . get_user_meta($user_id, 'zip_code', true);
}
$tools_templates = get_user_meta($user_id, 'tools_templates_access', true);
$policies_procedures = get_user_meta($user_id, 'policies_procedures_access', true);

$business_company_status = $admin_company_status = $corporate_company_status = '';

$business_companies = get_user_meta($current_user->ID, 'business_companies', true );
$corporate_companies = get_user_meta($current_user->ID, 'corporate_companies', true );
$admin_companies = get_user_meta($current_user->ID, 'admin_companies', true );
if (isset($business_companies) && !empty($business_companies)) {
    $business_company_status = get_term_meta( $business_companies, 'users_comapny_company_status', true);
}
if (isset($admin_companies) && !empty($admin_companies)) {
    $admin_company_status = get_term_meta( $admin_companies, 'users_comapny_company_status', true);
}
if (isset($corporate_companies) && !empty($corporate_companies)) {
    $corporate_company_status = get_term_meta( $corporate_companies, 'users_comapny_company_status', true);
}

$account_created = get_user_meta($user_id, 'create_date', true);
$end_date = get_user_meta($user_id, 'end_date', true);

?>
<div class="vc_row wpb_row section vc_row-fluid vc_inner pt-dashboard-row" style=" text-align:left;">
    <div class=" full_section_inner clearfix">
        <div class="wpb_column vc_column_container vc_col-sm-6">
            <div class="vc_column-inner ">
                <div class="wpb_wrapper">
                    <div class="service_table_holder pt-dashboard-table">
                        <ul class="service_table_inner">
                            <li class="service_table_title_holder background_color_type" style="">
                                <div class="service_table_title_inner">
                                    <div class="service_table_title_inner2">
                                        <h3 class="service_title" style="color: #00a0d7;">Personal Information</h3>
                                    </div>
                                </div>
                            </li>
                            <li class="service_table_content" style="">
                                <p></p>
                                <ul>
                                    <li><strong>First Name:</strong> <span id="first-name-dashboard"><?php echo (isset($fname) && !empty($fname)) ? $fname : 'Not set yet'; ?></span></li>
                                    <li><strong>Last Name:</strong> <span id="last-name-dashboard"><?php echo (isset($lname) && !empty($lname)) ? $lname : 'Not set yet'; ?></span></li>
                                    <li><strong>Title:</strong> <?php echo (isset($title) && !empty($title)) ? $title : 'Not set yet'; ?> </li>
                                    <li><strong>Work Phone:</strong> <span id="phone-dashboard"><?php echo (isset($work_phone) && !empty($work_phone)) ? $work_phone : 'Not set yet'; ?></span></li>
                                    <li><strong>Email Address:</strong> <?php echo (isset($uEmail) && !empty($uEmail)) ? $uEmail : 'Not set yet'; ?></span></li>
                                    <li><strong>Address:</strong> <span id="address-dashboard"><?php echo (isset($user_address) && !empty($user_address)) ? $user_address : 'Not set yet'; ?></span></li>
                                </ul>
                                <p></p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="wpb_column vc_column_container vc_col-sm-6">
            <div class="vc_column-inner ">
                <div class="wpb_wrapper">
                    <div class="service_table_holder pt-dashboard-table">
                        <ul class="service_table_inner">
                            <li class="service_table_title_holder background_color_type" style="">
                                <div class="service_table_title_inner">
                                    <div class="service_table_title_inner2">
                                        <h3 class="service_title" style="color: #00a0d7;">Account Information</h3>
                                    </div>
                                </div>
                            </li>
                            <li class="service_table_content" style="">
                                <p></p>
                                <ul>
                                    <li><strong>Business:</strong>  <span id="company-dashboard"><?php echo $business; ?></span></li>
                                    <li><strong>Corporate Facility:</strong> <?php echo $corporate_facility; ?></li>
                                    <li><strong>Admin Facility:</strong> <?php echo $admin_facility; ?></li>
                                    <li><strong>Access to Tools & Templates:</strong> <?php echo (isset($tools_templates) && !empty($tools_templates)) ? ucfirst($tools_templates) : 'Not set yet'; ?></li>
                                    <li><strong>Access to Policies & Procedures:</strong> <?php echo (isset($policies_procedures) && !empty($policies_procedures)) ? ucfirst($policies_procedures) : 'Not set yet'; ?></li>
                                    <li><strong>Company Status:</strong>
                                        <?php if ($business_company_status == 'disable' || $admin_company_status == 'disable' || $corporate_company_status == 'disable') {
                                            echo "Disable";
                                            } else  {
                                                echo "Enable";
                                            }
                                        ?>
                                    </li>
                                    <li><strong>Account Created:</strong> <?php echo (isset($account_created) && !empty($account_created)) ? $account_created : 'Not set yet'; ?></li>
                                    <li><strong>End date:</strong> <?php echo (isset($end_date) && !empty($end_date)) ? $end_date : 'Not set yet'; ?></li>
                                </ul>
                                <p></p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>