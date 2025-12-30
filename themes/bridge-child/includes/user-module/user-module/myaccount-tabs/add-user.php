<?php
/* Get user info. */
global $user_details;
?>
<div class="vc_row wpb_row section vc_row-fluid vc_inner pt-dashboard-row" style=" text-align:left;">
    <div class=" full_section_inner clearfix profile-form edit-profile-form">
        <div id="ajax-loader">
            <img id="add-user-loading-image" src="<?php echo get_stylesheet_directory_uri(); ?>/includes/assets/images/ajax-loader.gif" style="display:none;"/>
        </div>
        <form id="add-user-form" method="post" action="">
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
                                        <li><label>Email:</label><input type="email" name="add-email" id="add-email" value="" placeholder="Email"><p id="add-email-error" class="error"></p></li>
                                        <li><label>First Name:</label><input type="text" name="add-first-name" id="add-first-name" value="" placeholder="First Name"><p id="add-first-name-error" class="error"></p></li>
                                        <li><label>Last Name:</label><input type="text" name="add-last-name" id="add-last-name" value="" placeholder="Last Name"></li>
                                        <li><label>Street 1:</label><input type="text" name="add-street-1" id="add-street-1" value="" placeholder="Street 1"></li>
                                        <li><label>Street 2:</label><input type="text" name="add-street-2" id="add-street-2" value="" placeholder="Street 2" class="placeholder"></li>
                                        <li><label>City:</label><input type="text" name="add-city" id="add-city" value="" placeholder="City"></li>
                                        <li>
                                            <label>State:</label>
                                            <select name="add-state" id="add-state">
                                                <option value="">Select State</option>
                                                <?php $titles = PT_Defaults_Values::get_usa_states_options();
                                                foreach ( $titles as $value => $option ) {
                                                    echo '<option value="'. $value .'">'. $option .'</option>';
                                                } ?>
                                            </select>
                                        </li>
                                        <li><label>Zip Code:</label><input type="text" name="add-zip-code" id="add-zip-code" value="" placeholder="Zip Code"></li>
                                    </ul>
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
                                            <h3 class="service_title" style="color: #00a0d7;">Other Information</h3>
                                        </div>
                                    </div>
                                </li>
                                <li class="service_table_content" style="">
                                    <ul>
                                        <li><label>Work Phone:</label><input type="text" name="add-work-phone" id="add-work-phone" value="" placeholder="Work Phone"></li>
                                        <li><label>Company Name:</label><input type="text" name="add-company" id="add-company" value="" placeholder="Company Name"></li>
                                        <li>
                                        	<label>User Status:</label>
                        					<label>
                        						<input name="add-user-status" type="radio" value="enable">
                        						<span>Enable</span>
                        					</label>
                        					<label>
                        						<input name="add-user-status" type="radio" value="disable">
                        						<span>Disable</span>
                        					</label>
                                        </li>
                                        <li>
                                        	<label>Tools & Templates:</label>
                        					<label>
                        						<input name="add-tools-templates-access" type="radio" value="enable">
                        						<span>Enable</span>
                        					</label>
                        					<label>
                        						<input name="add-tools-templates-access" type="radio" value="disable">
                        						<span>Disable</span>
                        					</label>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wpb_column vc_column_container vc_col-sm-12">
                <div class="vc_column-inner ">
                    <div class="wpb_wrapper">
                        <div class="profile-btn-wrapper">
                            <input name="adduser" type="submit" id="adduser" class="submit button" value="Add User" />
                            <?php wp_nonce_field( 'add-user', 'add-user-nonce' ); ?>
                            <input type="hidden" name="add_user_id" value="<?php echo $user_details->ID; ?>" />
                            <input name="action" type="hidden" id="add_user_action" value="add-user" />
                            <div class="user-added"></div>
                        </div><!-- .form-submit -->
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>