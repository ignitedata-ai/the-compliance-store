<?php

/* 
 * Template Name: Password Reset
 */

get_header();
$user_details = wp_get_current_user();
$key = get_password_reset_key($user_details);
?>
<div class="container">
	<div class="container_inner default_template_holder clearfix page_container_inner">
		<div class="vc_row wpb_row section vc_row-fluid " style=" text-align:center;">
			<div class=" full_section_inner clearfix">
				<div class="wpb_column vc_column_container vc_col-sm-3">
					<div class="vc_column-inner">
						<div class="wpb_wrapper"></div>
					</div>
				</div>
				<div class="wpb_column vc_column_container vc_col-sm-6">
					<div class="vc_column-inner">
						<div class="wpb_wrapper">
							<div class="wpb_text_column wpb_content_element ">
								<div class="wpb_wrapper">
									<div class="form-wrapper">
										<div class="tml tml-login" id="password-reset-form">			
											<form method="post" action="<?php echo get_bloginfo('url') ?>/wp-login.php" id="passwordreset" name="passwordreset">
												<h3>Reset your password</h3>
												<p class="tml-user-pass-wrap">
													<label for="user_pass">New Password</label>
													<input type="password" name="user_password" id="user_password" class="input" value="" size="20" autocomplete="off">
												</p>
												<p class="tml-user-pass-wrap">
													<label for="user_password1">Confirm Password</label>
													<input type="password" name="user_password1" id="user_password1" class="input" value="" size="20" autocomplete="off">
												</p>
												<p id="match-text" class="error"></p>
	                                    		<p id="strength-text"></p>
												<input type="hidden" name="user_login" value="<?php echo $user_details->user_login; ?>" autocomplete="off">
                                				<input type="hidden" name="userkey" value="<?php echo $key; ?>" />
                                    			<input type="hidden" name="user_id" value="<?php echo $user_details->ID; ?>" />
												<div class="login-error"></div>
												<div class="tml-rememberme-submit-wrap">					
													<p class="tml-submit-wrap">
														<input type="submit" name="pass-reset" id="pass-reset" value="Set New Password">
													</p>
												</div>
											</form>
										</div>
									</div>
								</div> 
							</div>
						</div>
					</div>
				</div>
				<div class="wpb_column vc_column_container vc_col-sm-3">
					<div class="vc_column-inner">
						<div class="wpb_wrapper"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php get_footer();