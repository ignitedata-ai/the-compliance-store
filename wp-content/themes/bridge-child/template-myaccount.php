<?php

/* 
 * Template Name: My Account
 */

get_header(); 

$user_details = '';
if (is_user_logged_in()) {
    $user_details = wp_get_current_user();
    /*$user_status = $user_details->user_status;*/
    $roles = $user_details->roles;
    $account_manager = get_user_meta( $user_details->ID, 'account_manager', true );
    $user_status = get_user_meta( $user_details->ID, 'user_status', true );
    $account_manager_user = $account_manager_work_phone = $user_firstname = '';
    if (isset($account_manager) && !empty($account_manager)) {
        $account_manager_user = get_user_by( 'email', $account_manager );
        $user_firstname = @$account_manager_user->user_firstname;
        $account_manager_work_phone = @get_user_meta( $account_manager_user->ID, 'work_phone', true );
    }
} else {
	$wp_login_url = wp_login_url();
	wp_redirect( $wp_login_url );
	die();
}
?>
<div class="content content_top_margin">
    <div class="container">
        <div class="container_inner default_template_holder clearfix page_container_inner">
            <div id="welcome-myaccount" class="vc_row wpb_row section vc_row-fluid  welcome-myaccount vc_custom_1551185081599" style=" text-align:left;">
                <div class=" clearfix">
                    <div class="wpb_column vc_column_container <?php if ($user_status == 'disable') { echo 'vc_col-sm-6'; } else { echo 'vc_col-sm-4';  } ?> vc_col-has-fill">
                        <div class="vc_column-inner vc_custom_1551184768435">
                            <div class="wpb_wrapper">
                                <div class="wpb_text_column wpb_content_element ">
                                    <div class="wpb_wrapper">
                                        <?php if ($user_status == 'disable') { ?>
                                        <p><strong>Your Account is Disabled. </strong>Please Contact your Account Manager: <strong><?php echo $user_firstname; ?></strong></p>
                                        <?php }
                                        else { ?>
                                        <p>Need Assistance? Contact your Account Manager: <strong><?php echo $user_firstname; ?></strong></p>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="wpb_column vc_column_container <?php if ($user_status == 'disable') { echo 'vc_col-sm-2'; } else { echo 'vc_col-sm-4'; } ?> vc_col-has-fill">
                        <div class="vc_column-inner vc_custom_1551184505837">
                            <div class="wpb_wrapper">
                                <div class="wpb_text_column wpb_content_element  vc_custom_1551183236904">
                                    <div class="wpb_wrapper">
                                        <p>Call: <a href="tel:<?php echo $account_manager_work_phone; ?>"><?php echo $account_manager_work_phone; ?></a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="wpb_column vc_column_container vc_col-sm-4 vc_col-has-fill">
                        <div class="vc_column-inner vc_custom_1551184513361">
                            <div class="wpb_wrapper">
                                <div class="wpb_text_column wpb_content_element  vc_custom_1551183218836">
                                    <div class="wpb_wrapper">
                                        <p>E-mail: <a href="mailto:<?php echo $account_manager_user->user_email; ?>"><?php echo $account_manager_user->user_email; ?></a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" full_section_inner clearfix">
                    <div class="wpb_column vc_column_container vc_col-sm-12 vc_col-has-fill" style="border-top: none;">
                        <div class="vc_column-inner vc_custom_1551184768435">
                            <div class="wpb_wrapper">
                                <div class="wpb_text_column wpb_content_element ">
                                    <div class="wpb_wrapper" align="center">
                                        <p>Please update your information. If correct please click Home to proceed.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>                                   
                    
                </div>
            </div>
            
            <div class="vc_row wpb_row section vc_row-fluid " style=" text-align:left;">
                <div class=" full_section_inner clearfix">
                    <div class="wpb_column vc_column_container vc_col-sm-12">
                        <div class="vc_column-inner ">
                            <div class="wpb_wrapper">
                                <div class="qode-advanced-tabs qode-advanced-tabs qode-advanced-horizontal-tab clearfix qode-advanced-tab-without-icon qode-advanced-tabs-column-5 clearfix ui-tabs ui-widget ui-widget-content ui-corner-all">
                                    <ul class="qode-advanced-tabs-nav ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all pt-myaccount-ul" role="tablist">
                                        <li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active" role="tab" tabindex="0" aria-controls="tab-dashboard" aria-labelledby="ui-id-1" aria-selected="true" aria-expanded="true">
                                            <h4>
                                                <a href="#tab-dashboard" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-1">
                                                    <span class="qode-advanced-tab-text-after-icon">Dashboard</span>
                                                </a>
                                            </h4>
                                        </li>
                                        <?php
                                        /*if (in_array('subscriber_-_admin_facility', $roles) || in_array('subscriber_-_corporate', $roles) || in_array('business', $roles)) {
                                            if ($user_status == 'enable') {*/ ?>  
                                                <!-- <li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="tab-user-list" aria-labelledby="ui-id-3" aria-selected="false" aria-expanded="false">
                                                    <h4>
                                                        <a href="#tab-user-list" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-3">
                                                            <span class="qode-advanced-tab-text-after-icon">User List</span>
                                                        </a>
                                                    </h4>
                                                </li> -->
                                            <?php
                                            //}
                                        //} ?>
                                        <li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="tab-edit-profile" aria-labelledby="ui-id-4" aria-selected="false" aria-expanded="false">
                                            <h4>
                                                <a href="#tab-edit-profile" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-4">
                                                    <span class="qode-advanced-tab-text-after-icon">Edit Profile</span>
                                                </a>
                                            </h4>
                                        </li>
                                        <li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="tab-password-reset" aria-labelledby="ui-id-5" aria-selected="false" aria-expanded="false">
                                            <h4>
                                                <a href="#tab-password-reset" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-5">
                                                    <span class="qode-advanced-tab-text-after-icon">Password Reset</span>
                                                </a>
                                            </h4>
                                        </li>
                                    </ul>
                                    
                                    
                                    <div class="qode-advanced-tab-container ui-tabs-panel ui-widget-content ui-corner-bottom" id="tab-dashboard" data-icon-pack="" data-icon-html="" aria-labelledby="ui-id-1" role="tabpanel" aria-hidden="false" style="display: block;">
                                        <?php /* Dashboard */
                                        if ( file_exists( dirname( __FILE__ ) . '/includes/user-module/myaccount-tabs/dashboard.php' ) ) {
                                            require_once dirname( __FILE__ ) . '/includes/user-module/myaccount-tabs/dashboard.php';
                                        } ?>
                                    </div>
                                    <?php
                                    /*if (in_array('subscriber_-_admin_facility', $roles) || in_array('subscriber_-_corporate', $roles) || in_array('business', $roles)) { 
                                        if ($user_status == 'enable') {*/ ?>  
                                    <!-- <div class="qode-advanced-tab-container ui-tabs-panel ui-widget-content ui-corner-bottom" id="tab-user-list" data-icon-pack="" data-icon-html="" aria-labelledby="ui-id-3" role="tabpanel" aria-hidden="true" style="display: none;"> -->
                                        <?php /* Users list */
                                        /*if ( file_exists( dirname( __FILE__ ) . '/includes/user-module/myaccount-tabs/users-list.php' ) ) {
                                            require_once dirname( __FILE__ ) . '/includes/user-module/myaccount-tabs/users-list.php';
                                        }*/ ?>
                                    <!-- </div> -->
                                    <?php //} } ?>
                                    
                                    <div class="qode-advanced-tab-container ui-tabs-panel ui-widget-content ui-corner-bottom" id="tab-edit-profile" data-icon-pack="" data-icon-html="" aria-labelledby="ui-id-4" role="tabpanel" aria-hidden="true" style="display: none;">
                                        <?php /* Edit Profile */
                                        if ( file_exists( dirname( __FILE__ ) . '/includes/user-module/myaccount-tabs/edit-profile.php' ) ) {
                                            require_once dirname( __FILE__ ) . '/includes/user-module/myaccount-tabs/edit-profile.php';
                                        } ?>
                                    </div>
                                    
                                    <div class="qode-advanced-tab-container ui-tabs-panel ui-widget-content ui-corner-bottom" id="tab-password-reset" data-icon-pack="" data-icon-html="" aria-labelledby="ui-id-5" role="tabpanel" aria-hidden="true" style="display: none;">
                                        <?php /* Password reset */
                                        if ( file_exists( dirname( __FILE__ ) . '/includes/user-module/myaccount-tabs/reset-password.php' ) ) {
                                            require_once dirname( __FILE__ ) . '/includes/user-module/myaccount-tabs/reset-password.php';
                                        } ?>
                                    </div>
                                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer();