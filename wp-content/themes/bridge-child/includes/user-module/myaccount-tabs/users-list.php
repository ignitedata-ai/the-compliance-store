<?php 
$user_details = '';
if (is_user_logged_in()) {
    $user_details = wp_get_current_user();
    $user_roles = $user_details->roles;
}
if (in_array('subscriber_-_admin_facility', $user_roles)) { 
    echo '<p>Facility Users under <strong>Admin Facility: '.$user_details->first_name.'</strong> are listed below:</p>';
    $args = array( // get all users where
        'meta_key' => 'admin_facility', // the key 'specialkey'
        'meta_compare' => '=', // has a value that is equal to 
        'meta_value' => $user_details->user_email // hello world
    );
}
elseif (in_array('subscriber_-_corporate', $user_roles)) {
    echo '<p>Users under <strong>Corporate Facility: '.$user_details->first_name.'</strong> are listed below:</p>';
    $args = array( // get all users where
        'role' => 'subscriber_-_admin_facility',
        'meta_key' => 'corporate_facility', // the key 'specialkey'
        'meta_compare' => '=', // has a value that is equal to 
        'meta_value' => $user_details->user_email // hello world
    );
}
elseif (in_array('business', $user_roles)) {
    echo '<p>Users under <strong>Business: '.$user_details->first_name.'</strong> are listed below:</p>';
    $args = array( // get all users where
        'role' => 'subscriber_-_corporate',
        'meta_key' => 'business', // the key 'specialkey'
        'meta_compare' => '=', // has a value that is equal to 
        'meta_value' => $user_details->user_email // hello world
    );
}
// The Query
$user_query = new WP_User_Query( $args ); ?>
<?php
if ( !empty( $user_query->results ) ) { ?>
	<div class=" full_section_inner clearfix" style="background-color: #cacaca;">
	    <div class="wpb_column vc_column_container vc_col-sm-3 vc_col-has-fill">
	        <div class="vc_column-inner vc_custom_1551184768435">
	            <div class="wpb_wrapper">
	                <div class="wpb_text_column wpb_content_element ">
	                    <div class="wpb_wrapper">
	                    	<strong>Email</strong>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
	    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
	        <div class="vc_column-inner vc_custom_1551184768435">
	            <div class="wpb_wrapper">
	                <div class="wpb_text_column wpb_content_element ">
	                    <div class="wpb_wrapper">
	                    	<strong>Name</strong>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div> 
	    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
	        <div class="vc_column-inner vc_custom_1551184768435">
	            <div class="wpb_wrapper">
	                <div class="wpb_text_column wpb_content_element ">
	                    <div class="wpb_wrapper">
	                    	<strong>Status</strong>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>  
	    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
	        <div class="vc_column-inner vc_custom_1551184768435">
	            <div class="wpb_wrapper">
	                <div class="wpb_text_column wpb_content_element ">
	                    <div class="wpb_wrapper">
	                    	<strong>Tools and Templates</strong>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
	    <div class="wpb_column vc_column_container vc_col-sm-3 vc_col-has-fill">
	        <div class="vc_column-inner vc_custom_1551184768435">
	            <div class="wpb_wrapper">
	                <div class="wpb_text_column wpb_content_element ">
	                    <div class="wpb_wrapper">
	                    	<strong>Role</strong>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
	 </div>          
	<?php 
    foreach ( $user_query->results as $user ) { ?>
    	<div class=" full_section_inner clearfix" <?php if (in_array('subscriber_-_corporate', $user_roles)) { echo 'style="background-color: #eee"'; } ?>>
    		<div class="wpb_column vc_column_container vc_col-sm-3 vc_col-has-fill">
		        <div class="vc_column-inner vc_custom_1551184768435">
		            <div class="wpb_wrapper">
		                <div class="wpb_text_column wpb_content_element ">
		                    <div class="wpb_wrapper">
		                    	<p><?php echo $user->user_email; ?></p>
		                    </div>
		                </div>
		            </div>
		        </div>
		    </div>
		    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
		        <div class="vc_column-inner vc_custom_1551184768435">
		            <div class="wpb_wrapper">
		                <div class="wpb_text_column wpb_content_element ">
		                    <div class="wpb_wrapper">
		                    	<p><?php echo $user->user_firstname.' '.$user->user_lastname; ?></p>
		                    </div>
		                </div>
		            </div>
		        </div>
		    </div> 
		    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
		        <div class="vc_column-inner vc_custom_1551184768435">
		            <div class="wpb_wrapper">
		                <div class="wpb_text_column wpb_content_element ">
		                    <div class="wpb_wrapper">
		                    	<p><?php $user_status = get_user_meta( $user->ID, 'user_status', true );
		                    	echo (isset($user_status) && !empty($user_status)) ? ucwords($user_status) : 'Not set yet'; ?></p>
		                    </div>
		                </div>
		            </div>
		        </div>
		    </div>  
		    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
		        <div class="vc_column-inner vc_custom_1551184768435">
		            <div class="wpb_wrapper">
		                <div class="wpb_text_column wpb_content_element ">
		                    <div class="wpb_wrapper">
		                    	<p><?php $tools_templates_access = get_user_meta( $user->ID, 'tools_templates_access', true );
		                    	echo (isset($tools_templates_access) && !empty($tools_templates_access)) ? ucwords($tools_templates_access) : 'Not set yet'; ?></p>
		                    </div>
		                </div>
		            </div>
		        </div>
		    </div>
		    <div class="wpb_column vc_column_container vc_col-sm-3 vc_col-has-fill">
		        <div class="vc_column-inner vc_custom_1551184768435">
		            <div class="wpb_wrapper">
		                <div class="wpb_text_column wpb_content_element ">
		                    <div class="wpb_wrapper">
		                    	<?php foreach ($user->roles as $role) {
                                        $role =  str_replace("_"," ",$role);
                                        echo ucwords($role);
                                    }
                                ?>
		                    </div>
		                </div>
		            </div>
		        </div>
		    </div>
		</div>
		<?php
		if (in_array('subscriber_-_corporate', $user_roles)) {
            $args_facility = array( // get all users where
                'role' => 'subscriber_-_facility_user',
                'meta_key' => 'admin_facility', // the key 
                'meta_compare' => '=', // has a value that is equal to 
                'meta_value' => $user->user_email
            );
            $user_query_args_facility = new WP_User_Query( $args_facility );
            if ( !empty( $user_query_args_facility->results ) ) {
                foreach ( $user_query_args_facility->results as $user_facility ) { ?>
                    <div class=" full_section_inner clearfix" >
			    		<div class="wpb_column vc_column_container vc_col-sm-3 vc_col-has-fill">
					        <div class="vc_column-inner vc_custom_1551184768435">
					            <div class="wpb_wrapper">
					                <div class="wpb_text_column wpb_content_element ">
					                    <div class="wpb_wrapper">
					                    	<p>— <?php echo $user_facility->user_email; ?></p>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div>
					    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
					        <div class="vc_column-inner vc_custom_1551184768435">
					            <div class="wpb_wrapper">
					                <div class="wpb_text_column wpb_content_element ">
					                    <div class="wpb_wrapper">
					                    	<p><?php echo $user_facility->user_firstname.' '.$user_facility->user_lastname; ?></p>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div> 
					    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
					        <div class="vc_column-inner vc_custom_1551184768435">
					            <div class="wpb_wrapper">
					                <div class="wpb_text_column wpb_content_element ">
					                    <div class="wpb_wrapper">
					                    	<p><?php $user_status = get_user_meta( $user_facility->ID, 'user_status', true );
					                    	echo (isset($user_status) && !empty($user_status)) ? ucwords($user_status) : 'Not set yet'; ?></p>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div>  
					    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
					        <div class="vc_column-inner vc_custom_1551184768435">
					            <div class="wpb_wrapper">
					                <div class="wpb_text_column wpb_content_element ">
					                    <div class="wpb_wrapper">
					                    	<p><?php $tools_templates_access = get_user_meta( $user_facility->ID, 'tools_templates_access', true );
					                    	echo (isset($tools_templates_access) && !empty($tools_templates_access)) ? ucwords($tools_templates_access) : 'Not set yet'; ?></p>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div>
					    <div class="wpb_column vc_column_container vc_col-sm-3 vc_col-has-fill">
					        <div class="vc_column-inner vc_custom_1551184768435">
					            <div class="wpb_wrapper">
					                <div class="wpb_text_column wpb_content_element ">
					                    <div class="wpb_wrapper">
					                    	<?php foreach ($user_facility->roles as $role) {
		                                            $role =  str_replace("_"," ",$role);
		                                            echo ucwords($role);
		                                        }
		                                    ?>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div>
					</div>
					<?php
                }
            }
            else { ?>
            	<div class=" full_section_inner clearfix">
		    		<div class="wpb_column vc_column_container vc_col-sm-12 vc_col-has-fill">
				        <div class="vc_column-inner vc_custom_1551184768435">
				            <div class="wpb_wrapper">
				                <div class="wpb_text_column wpb_content_element ">
				                    <div class="wpb_wrapper">
				                    	<p>— No Facility users found under Admin Facility: <?php echo $user->user_email; ?></p>
				                    </div>
				                </div>
				            </div>
				        </div>
				    </div>
				</div>
			<?php
            }
        }
        if (in_array('business', $user_roles)) {
		    $args_facility = array( // get all users where
		        'role' => 'subscriber_-_admin_facility',
		        'meta_key' => 'corporate_facility', // the key
		        'meta_compare' => '=', // has a value that is equal to 
		        'meta_value' => $user->user_email
		    );
		    $user_query_args_facility = new WP_User_Query( $args_facility );
		    if ( !empty( $user_query_args_facility->results ) ) {
		        foreach ( $user_query_args_facility->results as $user_facility ) { ?>
		            <div class=" full_section_inner clearfix" >
			    		<div class="wpb_column vc_column_container vc_col-sm-3 vc_col-has-fill">
					        <div class="vc_column-inner vc_custom_1551184768435">
					            <div class="wpb_wrapper">
					                <div class="wpb_text_column wpb_content_element ">
					                    <div class="wpb_wrapper">
					                    	<p>— <?php echo $user_facility->user_email; ?></p>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div>
					    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
					        <div class="vc_column-inner vc_custom_1551184768435">
					            <div class="wpb_wrapper">
					                <div class="wpb_text_column wpb_content_element ">
					                    <div class="wpb_wrapper">
					                    	<p><?php echo $user_facility->user_firstname.' '.$user_facility->user_lastname; ?></p>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div> 
					    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
					        <div class="vc_column-inner vc_custom_1551184768435">
					            <div class="wpb_wrapper">
					                <div class="wpb_text_column wpb_content_element ">
					                    <div class="wpb_wrapper">
					                    	<p><?php $user_status = get_user_meta( $user_facility->ID, 'user_status', true );
					                    	echo (isset($user_status) && !empty($user_status)) ? ucwords($user_status) : 'Not set yet'; ?></p>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div>  
					    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
					        <div class="vc_column-inner vc_custom_1551184768435">
					            <div class="wpb_wrapper">
					                <div class="wpb_text_column wpb_content_element ">
					                    <div class="wpb_wrapper">
					                    	<p><?php $tools_templates_access = get_user_meta( $user_facility->ID, 'tools_templates_access', true );
					                    	echo (isset($tools_templates_access) && !empty($tools_templates_access)) ? ucwords($tools_templates_access) : 'Not set yet'; ?></p>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div>
					    <div class="wpb_column vc_column_container vc_col-sm-3 vc_col-has-fill">
					        <div class="vc_column-inner vc_custom_1551184768435">
					            <div class="wpb_wrapper">
					                <div class="wpb_text_column wpb_content_element ">
					                    <div class="wpb_wrapper">
					                    	<?php foreach ($user_facility->roles as $role) {
		                                            $role =  str_replace("_"," ",$role);
		                                            echo ucwords($role);
		                                        }
		                                    ?>
					                    </div>
					                </div>
					            </div>
					        </div>
					    </div>
					</div>
					<?php
		            $args_users = array( // get all users where
		                'role' => 'subscriber_-_facility_user',
		                'meta_key' => 'admin_facility', // the key
		                'meta_compare' => '=', // has a value that is equal to 
		                'meta_value' => $user_facility->user_email
		            );
		            $args_users_query = new WP_User_Query( $args_users );
		            if ( !empty( $args_users_query->results ) ) {
		                foreach ( $args_users_query->results as $facility_users ) { ?>
	    		            <div class=" full_section_inner clearfix" >
					    		<div class="wpb_column vc_column_container vc_col-sm-3 vc_col-has-fill">
							        <div class="vc_column-inner vc_custom_1551184768435">
							            <div class="wpb_wrapper">
							                <div class="wpb_text_column wpb_content_element ">
							                    <div class="wpb_wrapper">
							                    	<p>—— <?php echo $facility_users->user_email; ?></p>
							                    </div>
							                </div>
							            </div>
							        </div>
							    </div>
							    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
							        <div class="vc_column-inner vc_custom_1551184768435">
							            <div class="wpb_wrapper">
							                <div class="wpb_text_column wpb_content_element ">
							                    <div class="wpb_wrapper">
							                    	<p><?php echo $facility_users->user_firstname.' '.$facility_users->user_lastname; ?></p>
							                    </div>
							                </div>
							            </div>
							        </div>
							    </div> 
							    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
							        <div class="vc_column-inner vc_custom_1551184768435">
							            <div class="wpb_wrapper">
							                <div class="wpb_text_column wpb_content_element ">
							                    <div class="wpb_wrapper">
							                    	<p><?php $user_status = get_user_meta( $facility_users->ID, 'user_status', true );
							                    	echo (isset($user_status) && !empty($user_status)) ? ucwords($user_status) : 'Not set yet'; ?></p>
							                    </div>
							                </div>
							            </div>
							        </div>
							    </div>  
							    <div class="wpb_column vc_column_container vc_col-sm-2 vc_col-has-fill">
							        <div class="vc_column-inner vc_custom_1551184768435">
							            <div class="wpb_wrapper">
							                <div class="wpb_text_column wpb_content_element ">
							                    <div class="wpb_wrapper">
							                    	<p><?php $tools_templates_access = get_user_meta( $facility_users->ID, 'tools_templates_access', true );
							                    	echo (isset($tools_templates_access) && !empty($tools_templates_access)) ? ucwords($tools_templates_access) : 'Not set yet'; ?></p>
							                    </div>
							                </div>
							            </div>
							        </div>
							    </div>
							    <div class="wpb_column vc_column_container vc_col-sm-3 vc_col-has-fill">
							        <div class="vc_column-inner vc_custom_1551184768435">
							            <div class="wpb_wrapper">
							                <div class="wpb_text_column wpb_content_element ">
							                    <div class="wpb_wrapper">
							                    	<?php foreach ($facility_users->roles as $role) {
				                                            $role =  str_replace("_"," ",$role);
				                                            echo ucwords($role);
				                                        }
				                                    ?>
							                    </div>
							                </div>
							            </div>
							        </div>
							    </div>
							</div>
						<?php
		                }
		            }
		            else { ?>
		            	<div class=" full_section_inner clearfix">
				    		<div class="wpb_column vc_column_container vc_col-sm-12 vc_col-has-fill">
						        <div class="vc_column-inner vc_custom_1551184768435">
						            <div class="wpb_wrapper">
						                <div class="wpb_text_column wpb_content_element ">
						                    <div class="wpb_wrapper">
						                    	<p>— No Facility users found under Admin Facility: <?php echo $user_facility->user_email; ?></p>
						                    </div>
						                </div>
						            </div>
						        </div>
						    </div>
						</div>
					<?php
		            }
		        }
		    }
		    else { ?>
		    	<div class=" full_section_inner clearfix">
		    		<div class="wpb_column vc_column_container vc_col-sm-12 vc_col-has-fill">
				        <div class="vc_column-inner vc_custom_1551184768435">
				            <div class="wpb_wrapper">
				                <div class="wpb_text_column wpb_content_element ">
				                    <div class="wpb_wrapper">
				                    	<p>— No Admin Facility users found under Corporate Facility: <?php echo $user->user_email; ?></p>
				                    </div>
				                </div>
				            </div>
				        </div>
				    </div>
				</div>
		    <?php
		    }
		}
	}
}
else {
	echo 'No users found.';
}