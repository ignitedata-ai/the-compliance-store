<?php

require_once( get_stylesheet_directory() . '/includes/user-module/class-pt-defaults-values.php' );

global $user_details;
$security_ques = get_user_meta($user_details->ID, 'security_question', true);
// $key = get_password_reset_key($user_details);
//$security_questions = PT_Defaults_Values::get_security_questions();

/*Load Scripts for password reset page*/
/*wp_enqueue_script( 'zxcvbn-async' );
wp_enqueue_script( 'user-profile' );
wp_enqueue_script( 'password-strength-meter' );
wp_enqueue_script( 'user-suggest' );*/
?>
<div class="vc_row wpb_row section vc_row-fluid vc_inner pt-dashboard-row" style=" text-align:left;">
    <div class=" full_section_inner clearfix">
        <div class="wpb_column vc_column_container vc_col-sm-12">
            <div id="ajax-loader">
                <img id="loading-image" src="<?php echo get_stylesheet_directory_uri(); ?>/includes/assets/images/ajax-loader.gif" style="display:none;"/>
            </div>
            <div class="vc_column-inner ">
                <div class="wpb_wrapper">                    
                    <div class="profile-form">
                        <div class="service_table_holder">
                            <?php if (!empty($security_ques)) { ?>
                                <form method="post" action="<?php echo get_bloginfo('url') ?>/wp-login.php" id="resetpassaccount" name="resetpassaccount">
                                    <div class="service_table_holder pt-dashboard-table">
                                            <h3 class="service_title">Answer your Security Question</h3>
                                            <label><?php echo $security_ques; ?></label>
                                            <input type="text" name="security_question_answer" id="security_question_answer" value="" placeholder="Write Your Answer">
                                            <p id="question-text" class="error"></p>
                                            <h3 class="service_title">Add new Password</h3>
                                            <input  type="password" tabindex="10" size="20"  value="" placeholder="New Password" id="passreset1" name="passreset1">
                                            <span toggle="#passreset1" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                                            <input  type="password" tabindex="20" size="20" value="" placeholder="Confirm Password" id="passreset2" name="passreset2" style="margin-bottom: 0;">
                                            <span toggle="#passreset2" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                                            <p id="password-match-text" class="error"></p>
                                            <meter max="4" id="password-strength-meter"></meter>
                                            <p id="password-strength-text"></p>
                                            <input type="hidden" name="login" value="<?php echo $user_details->user_login; ?>" autocomplete="off">
                                            <input type="hidden" name="user_id" value="<?php echo $user_details->ID; ?>" />
                                            <p class="forgotpass-submit">
                                                <a id="submitforgotpasswordform" href="javascript:void(0)" ><input type="submit" tabindex="100" value="Set New Password" id="forgot-submit" name="wp-submit"></a>
                                            </p>
                                            <div class="login-error"></div>
                                    </div>
                                </form>
                            <?php }
                            else { ?>
                                <form method="post" action="<?php echo get_bloginfo('url') ?>/wp-login.php" id="setsecurityquestion" name="setsecurityquestion">
                                    <div class="service_table_holder pt-dashboard-table">
                                        <p><b>Please set your Security Question. Then you can reset your Password</b></p>
                                        <select name="security_question" id="security_question">
                                            <option value="">Select Security Question</option>
                                            <?php $security_questions = PT_Defaults_Values::get_security_questions();
                                            foreach ( $security_questions as $value => $option ) {
                                                echo '<option value="'. $option .'">'. $option .'</option>';
                                            } ?>
                                        </select>
                                         <input type="text" name="security_question_answer" id="security_question_answer" value="" placeholder="Write Your Answer">
                                         <input type="hidden" name="user_id" value="<?php echo $user_details->ID; ?>" />
                                         <p>
                                            <a id="security-ques-submit" href="javascript:void(0)" ><input type="submit" tabindex="100" value="Set Security Question" id="security-question-submit" name="security-question-submit"></a>
                                        </p>
                                        <div class="error"></div>
                                    </div>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>