<?php
/* Get user info. */
global $user_details; 
?>
<div class="vc_row wpb_row section vc_row-fluid vc_inner pt-dashboard-row" style=" text-align:left;">
    <div class=" full_section_inner clearfix profile-form edit-profile-form">
        <div id="ajax-loader">
            <img id="loading-image" src="<?php echo get_stylesheet_directory_uri(); ?>/includes/assets/images/ajax-loader.gif" style="display:none;"/>
        </div>
        <form id="edit-profile-form" method="post" action="">
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
                                        <li><label>First Name:</label><input type="text" name="first-name" id="first-name" value="<?php the_author_meta( 'first_name', $user_details->ID ); ?>" placeholder="First Name"><p id="first-name-error" class="error"></p></li>
                                        <li><label>Last Name:</label><input type="text" name="last-name" id="last-name" value="<?php the_author_meta( 'last_name', $user_details->ID ); ?>" placeholder="Last Name"></li>
                                        <li><label>Street 1:</label><input type="text" name="street_1" id="street_1" value="<?php the_author_meta( 'street_1', $user_details->ID ); ?>" placeholder="Street 1"></li>
                                        <li><label>Street 2:</label><input type="text" name="street_2" id="street_2" value="<?php the_author_meta( 'street_2', $user_details->ID ); ?>" placeholder="Street 2" class="placeholder"></li>
                                        <li><label>City:</label><input type="text" class="requiredField email placeholder" name="city" id="city" value="<?php the_author_meta( 'city', $user_details->ID ); ?>" placeholder="City"></li>
                                        <li><label>State:</label><input type="text" name="state" id="state" value="<?php the_author_meta( 'state', $user_details->ID ); ?>" placeholder="State" class="placeholder"></li>
                                        <li><label>Zip Code:</label><input type="text" name="zip_code" id="zip_code" value="<?php the_author_meta( 'zip_code', $user_details->ID ); ?>" placeholder="Zip Code"></li>
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
                                        <li><label>Email:</label><input type="email" name="email" id="email" value="<?php the_author_meta( 'email', $user_details->ID ); ?>" disabled></li>
                                        <li style="display: none;"><label>Nickname:</label><input type="text" name="nickname" id="nickname" value="<?php the_author_meta( 'nickname', $user_details->ID ); ?>" placeholder="johndoe123"><p id="nickname-error" class="error"></p><p>Enter a username of 20 Characters and Alphabets. Starting with a Character!</p></li>
                                        <li><label>Work Phone:</label><input type="text" name="work_phone" id="work_phone" value="<?php the_author_meta( 'work_phone', $user_details->ID ); ?>" placeholder="Work Phone"></li>
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
                            <input name="updateuser" type="submit" id="updateuser" class="submit button" value="Update Profile" />
                            <?php wp_nonce_field( 'update-user', 'update-user-nonce' ); ?>
                            <input type="hidden" name="user_id" value="<?php echo $user_details->ID; ?>" />
                            <input name="action" type="hidden" id="action" value="update-user" />
                            <div class="profile-updated"></div>
                        </div><!-- .form-submit -->
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>