<?php 

if ( !defined('ABSPATH') ) {
    exit;
}

require_once( get_stylesheet_directory() . '/includes/user-module/class-pt-defaults-values.php' );

if ( !class_exists('PT_User_Fields') ) {
    class PT_User_Fields {

        public function __construct() {
            /* Hooks to add custom fields on all user screens */
            add_action( 'show_user_profile', array( $this, 'custom_user_profile_fields') );
            add_action( 'edit_user_profile',  array( $this, 'custom_user_profile_fields') );
            add_action( 'edit_user_profile',  array( $this, 'custom_user_profile_fields_edit') );
            add_action( "user_new_form",  array( $this, 'custom_user_profile_fields') );

            /* Hooks to save user custom fields */
            add_action('user_register', array( $this, 'save_custom_user_profile_fields') );
            add_action('user_register', array( $this, 'generate_custom_user_profile_fields') );
            add_action('profile_update', array( $this, 'save_custom_user_profile_fields') );
            add_action('profile_update', array( $this, 'profile_update_custom_user_profile_fields') );
            
        }

        public function custom_user_profile_fields_edit($user) {

            $company_assigned_id = '';

            if(is_object($user))  {
                $get_user_author = get_userdata( esc_attr( get_user_meta( $user->ID, 'created_by', true ) ) );
                $create_date = esc_attr( date('d/m/Y', get_user_meta( $user->ID, 'create_date', true )) );
                $start_date = esc_attr( get_user_meta( $user->ID, 'start_date', true ) );
                $end_date = esc_attr( get_user_meta( $user->ID, 'end_date', true ) );
                $corporate_companies = get_user_meta($current_user_id, 'corporate_companies', true );
                $admin_companies = get_user_meta($current_user_id, 'admin_companies', true );

                if(!empty($admin_companies)) {
                    $company_assigned_id = $admin_companies;
                }
                
                if(!empty($corporate_companies)) {
                    $company_assigned_id = $corporate_companies;
                }

            } else {
                $start_date =  $end_date = null;
            }
            ?>
            <table class="form-table pt-user-table">
                <tr class="form-field">
                    <th scope="row">
                        <label for="create_date">Account Created By</label>
                    </th>
                    <td>
                        <input type="text" readonly class="regular-text pt-text-field" name="created_by" user_id="<?php echo $get_user_author->ID; ?>" value="<?php echo $get_user_author->user_email; ?>" id="created_by" />
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="create_date">Account Created Date</label>
                    </th>
                    <td class="create-date-btn">
                        <input type="text" class="regular-text pt-text-field cp_tooltip" name="create_date" value="<?php echo $create_date; ?>" id="create_date" disabled/>
                    </td>
                </tr>
                
                <tr class="form-field">
                    <th scope="row">
                        <label for="start_date">Account start Date</label>
                    </th>
                    <td>
                        <input type="text" class="regular-text pt-text-field cp_tooltip" name="start_date" value="<?php echo $start_date; ?>" id="start_date" />
                    </td>
                </tr>
                
                <tr class="form-field">
                    <th scope="row">
                        <label for="end_date">Account End Date</label>
                    </th>
                    <td>
                        <input type="text" class="regular-text pt-text-field cp_tooltip" name="end_date" value="<?php echo $end_date; ?>" id="end_date" />
                    </td>
                </tr>
                
            </table>

        <?php
        }

        public function custom_user_profile_fields($user) {

            $company_assigned_id = '';

            if (is_object($user)) {
                $user_status = esc_attr(get_user_meta($user->ID, 'user_status', true));
                $trusted_refer = esc_attr(get_user_meta($user->ID, 'trusted_refer', true));
                $tools_templates_access = esc_attr(get_user_meta($user->ID, 'tools_templates_access', true));
                
                $policies_procedures_access;
                $temp = esc_attr(get_user_meta($user->ID, 'policies_procedures_access', true));

                if ( empty( $temp ) ){
                    $policies_procedures_access = 'enable';
                    update_user_meta($user->ID, 'policies_procedures_access', 'enable');
                }
                else{
                    $policies_procedures_access = esc_attr(get_user_meta($user->ID, 'policies_procedures_access', true));
                }

                // frontend documents upload access

                $frontend_docs_upload_access;
                $existing_docs_upload_value = esc_attr(get_user_meta($user->ID, 'frontend_docs_upload_access', true));

                if ( empty( $existing_docs_upload_value ) ){
                    $frontend_docs_upload_access = 'disable';
                    update_user_meta($user->ID, 'frontend_docs_upload_access', 'disable');
                }
                else{
                    $frontend_docs_upload_access = esc_attr(get_user_meta($user->ID, 'frontend_docs_upload_access', true));
                }
                
                $work_phone = esc_attr(get_user_meta($user->ID, 'work_phone', true));
                $street_1 = esc_attr(get_user_meta($user->ID, 'street_1', true));
                $street_2 = esc_attr(get_user_meta($user->ID, 'street_2', true));
                $city = esc_attr(get_user_meta($user->ID, 'city', true));
                $state = esc_attr(get_user_meta($user->ID, 'state', true));
                $zip_code = esc_attr(get_user_meta($user->ID, 'zip_code', true));
                $title = esc_attr(get_user_meta($user->ID, 'title', true));

                /* Accounts Details */
                $business_companies = get_user_meta($user->ID, 'business_companies', true);
                $corporate_companies = esc_attr(get_user_meta($user->ID, 'corporate_companies', true));
                $admin_companies = esc_attr(get_user_meta($user->ID, 'admin_companies', true));

                $account_manager = esc_attr(get_user_meta($user->ID, 'account_manager', true));
                $security_question = esc_attr(get_user_meta($user->ID, 'security_question', true));
                $security_question_answer = esc_attr(get_user_meta($user->ID, 'security_question_answer', true));

            } else {

                $user = new stdClass();
                @$user->roles = array(); // the warning is ignored thanks to @


                $creating = isset($_POST['createuser']);

                $work_phone = $creating && isset($_POST['work_phone']) ? wp_unslash($_POST['work_phone']) : '';
                $street_1 = $creating && isset($_POST['street_1']) ? wp_unslash($_POST['street_1']) : '';
                $street_2 = $creating && isset($_POST['street_2']) ? wp_unslash($_POST['street_2']) : '';
                $city = $creating && isset($_POST['city']) ? wp_unslash($_POST['city']) : '';
                $state = $creating && isset($_POST['state']) ? wp_unslash($_POST['state']) : '';
                $zip_code = $creating && isset($_POST['zip_code']) ? wp_unslash($_POST['zip_code']) : '';
                $title = $creating && isset($_POST['title']) ? wp_unslash($_POST['title']) : '';

                $trusted_refer = $creating && isset($_POST['trusted_refer']) ? wp_unslash($_POST['trusted_refer']) : '';
                $user_status = $creating && isset($_POST['user_status']) ? wp_unslash($_POST['user_status']) : '';
                $tools_templates_access = $creating && isset($_POST['tools_templates_access']) ? wp_unslash($_POST['tools_templates_access']) : '';
                $policies_procedures_access = $creating && isset($_POST['policies_procedures_access']) ? wp_unslash($_POST['policies_procedures_access']) : '';
                $frontend_docs_upload_access = $creating && isset($_POST['frontend_docs_upload_access']) ? wp_unslash($_POST['frontend_docs_upload_access']) : '';

                $business_companies = $creating && isset($_POST['business_companies']) ? wp_unslash($_POST['business_companies']) : '';
                $corporate_companies = $creating && isset($_POST['corporate_companies']) ? wp_unslash($_POST['corporate_companies']) : '';
                $admin_companies = $creating && isset($_POST['admin_companies']) ? wp_unslash($_POST['admin_companies']) : '';
                $account_manager = $creating && isset($_POST['account_manager']) ? wp_unslash($_POST['account_manager']) : '';
                $security_question = $creating && isset($_POST['security_question']) ? wp_unslash($_POST['security_question']) : '';
                $security_question_answer = $creating && isset($_POST['security_question_answer']) ? wp_unslash($_POST['security_question_answer']) : '';
            }

            if(!empty($admin_companies)) {
                $company_assigned_id = $admin_companies;
            }
        
            if(!empty($corporate_companies)) {
                $company_assigned_id = $corporate_companies;
            }

            ?>
            <h3>User Other Information</h3>
            <table class="form-table pt-user-table">
                
                <tr class="form-field">
                    <th scope="row">
                        <label for="work_phone">Work Phone</label>
                    </th>
                    <td>
                        <input type="tel" class="regular-text pt-text-field" name="work_phone" value="<?php echo $work_phone; ?>" id="work_phone" />
                    </td>
                </tr>
                
                <tr class="form-field">
                    <th scope="row">
                        <label for="street_1">Street 1 <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" class="regular-text pt-text-field" name="street_1" value="<?php echo $street_1; ?>" id="street_1" />
                    </td>
                </tr>
                
                <tr class="form-field">
                    <th scope="row">
                        <label for="street_2">Street 2</label>
                    </th>
                    <td>
                        <input type="text" class="regular-text pt-text-field" name="street_2" value="<?php echo $street_2; ?>" id="street_2" />
                    </td>
                </tr>
                
                <tr class="form-field">
                    <th scope="row">
                        <label for="city">City <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" class="regular-text pt-text-field" name="city" value="<?php echo $city; ?>" id="city" />
                    </td>
                </tr>
                
                <tr class="form-field">
                    <th scope="row"><label for="state">State <span class="description">(required)</span></label></th>
                    <td>
                        <select name="state" id="state" aria-required="true">
                            <option value="" <?php echo $state == null ? 'selected' : ''; ?>>Select State</option>
                            <?php
                            $titles = PT_Defaults_Values::get_usa_states_options();
                            foreach ( $titles as $value => $option ) {
                                $selected = selected( $state,$value);
                                echo '<option value="'. $value .'"'. $selected .'>'. $option .'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                
                <tr class="form-field">
                    <th scope="row">
                        <label for="zip_code">Zip Code <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" class="regular-text pt-text-field" name="zip_code" value="<?php echo $zip_code; ?>" id="zip_code" />
                    </td>
                </tr>
                
                <tr class="form-field">
                    <th scope="row"><label for="title">Title <span class="description"><!–– (required) --></span></label></th>
                    <td>
                        <select name="title" id="title" aria-required="true">
                            <option value=""<?php echo $title == null ? 'selected' : ''; ?>>Select Title</option>
                            <?php
                            $titles = PT_Defaults_Values::get_title_options();
                            foreach ( $titles as $value => $option ) {
                                $selected = selected( $title,$value);
                                echo '<option value="'. $value .'"'. $selected .'>'. $option .'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr class="form-field">
                    <th scope="row"><label for="status">Trusted Refferer Login Link</label></th>
                    <td>
                        <div class="generate-trusted-refer" style="display: inline-block;">
                            <input type="text" readonly name="trusted_refer" id="trusted_refer" class="regular-text strong" value="<?php echo $trusted_refer; ?>" autocomplete="off" aria-describedby="pass-strength-result" style="width: 500px;">                            
                        </div>
                        <button type="button" class="button btn-remove-trusted-refer" style="<?php
                            if ($trusted_refer) {
                                echo "display: inline-block;";
                            } else {
                                echo 'display: none;';
                            }
                        ?> margin-left: 10px;">Remove Link</button>
                        <button type="button" class="button btn-generate-trusted-refer" style="display: inline-block;margin-left: 10px;">Generate Login Link</button>
                    </td>                    
                </tr>
                <tr class="form-field user_status_field">
                    <th scope="row"><label for="status">User Status <span class="description">(required)</span></label></th>
                    <td>
                        <p>
                            <?php
                            $user_statuses = PT_Defaults_Values::get_user_status_options();
                            $i = 0;
                            
                            foreach ( $user_statuses as $value => $label ) {
                                    $checked = checked( $user_status === $value, true, false );
                                    if($user_status == false){
                                        echo '<label><input id="user_status_'.++$i.'" name="user_status" type="radio" value="'. $value .'" checked> ';
                                        echo '<span>'. $label .'</span></label><br />';
                                        
                                    }else{
                                    echo '<label><input id="user_status_'.++$i.'" name="user_status" type="radio" value="'. $value .'"'. $checked .'> ';
                                    echo '<span>'. $label .'</span></label><br />';
                                    }
                            }

                            $disabled_date = get_user_meta( $user->ID, 'disabled_date', true );
                            ?>
                        </p>
                    </td>
                </tr>
                <tr class="form-field tools_templates_field">
                    <th scope="row"><label for="tools_templates_access">Access to Tools & Templates <span class="description">(required)</span></label></th>
                    <td>
                        <p>
                            <?php
                            $user_tools_template_statuses = PT_Defaults_Values::get_user_tnt_options();
                            $j = 0;
                            foreach ( $user_tools_template_statuses as $value => $label ) {
                                    $checked = checked( $tools_templates_access === $value, true, false );
                                    if($tools_templates_access == false){
                                        echo '<label><input id="tools_templates_access_'.++$j.'" name="tools_templates_access" type="radio" value="'. $value .'" checked> ';
                                        echo '<span>'. $label .'</span></label><br />';    
                                    }else
                                    {
                                    echo '<label><input id="tools_templates_access_'.++$j.'" name="tools_templates_access" type="radio" value="'. $value .'"'. $checked .'> ';
                                    echo '<span>'. $label .'</span></label><br />';
                                    }
                            }
                            ?>
                        </p>
                    </td>
                </tr>
                <!--get_user_pnp_status-->
                <tr class="form-field policies_procedures_field">
                    <th scope="row"><label for="policies_procedures_access">Access to Policies & Procedures <span class="description">(required)</span></label></th>
                    <td>
                        <p>
                            <?php
                                $user_pnp_statuses = PT_Defaults_Values::get_user_pnp_status();
                            
                                $j = 0;
                                foreach ( $user_pnp_statuses as $value => $label ) {
                                        $checked = checked( $policies_procedures_access === $value, true, false );
                                        if($policies_procedures_access == false){
                                            echo '<label><input id="policies_procedures_access_'.++$j.'" name="policies_procedures_access" type="radio" value="'. $value .'" checked> ';
                                            echo '<span>'. $label .'</span></label><br />';     
                                        }else
                                        {
                                        echo '<label><input id="policies_procedures_access_'.++$j.'" name="policies_procedures_access" type="radio" value="'. $value .'"'. $checked .'> ';
                                        echo '<span>'. $label .'</span></label><br />';
                                        }
                                }
                                
                            ?>
                        </p>
                    </td>
                </tr>

                <!-- frontend document upload permission -->
                <tr class="form-field frontend-documents-upload-permission-field">
                    <th scope="row"><label for="frontend_docs_upload_access">Allow to upload documents on frontend <span class="description">(required)</span></label></th>
                    <td>
                        <p>
                            <?php
                                $user_doc_upload_permission_status = PT_Defaults_Values::get_user_documents_upload_permission_status();
                            
                                $j = 0;

                                foreach ( $user_doc_upload_permission_status as $value => $label ) {
                                    $checked = checked( $frontend_docs_upload_access === $value, true, false );
                                    if($j == 0){
                                        echo '<label><input id="frontend_docs_upload_access_'. ++$j .'" name="frontend_docs_upload_access" type="radio" value="'. $value .'" checked> ';
                                        echo '<span>'. $label .'</span></label><br />';    
                                    }else{
                                        echo '<label><input id="frontend_docs_upload_access_'. ++$j .'" name="frontend_docs_upload_access" type="radio" value="'. $value .'"'. $checked .'> ';
                                        echo '<span>'. $label .'</span></label><br />';    
                                    }
                                }
                                
                            ?>
                        </p>
                    </td>
                </tr>
                
            </table>
            
            <h3>Account Detail</h3>

            <table class="form-table pt-user-table">
                <?php
                if (!in_array("backend-admin", $user->roles) && !in_array("account_manager", $user->roles) ) { ?>
                <tr class="form-field">
                    <th scope="row"><label for="business">Business</label></th>
                    <td>
                        <?php
                            $post_type = 'users';
                            $taxonomy  = 'company';
                            $info_taxonomy = get_taxonomy($taxonomy);
//                          $meta_values = get_user_meta( $user->ID, 'business_companies', true );
                            $selected = isset($business_companies) ? $business_companies : '';
                            wp_dropdown_categories(array(
                                'show_option_all' => __("Select Business"),
                                'taxonomy'        => $taxonomy,
                                'name'            => 'business_companies',
                                'orderby'         => 'name',
                                'selected'        => $selected,
                                'value_field' => 'id',
                                'hierarchical' => 1,
                                'hide_empty'      => 0,
                                'parent'      => 0,

                            ));
                        ?>
                    </td>
                </tr>
                <?php
                }
                if (!in_array("backend-admin", $user->roles) && !in_array("account_manager", $user->roles) && !in_array("business", $user->roles)) {
                ?>
                <tr class="form-field">
                    <th scope="row"><label for="corporate_facility">Corporate Facility</label></th>
                    <td>
                    <?php
                        $post_type = 'users';
                        $taxonomy  = 'company';
                        $info_taxonomy = get_taxonomy($taxonomy);
    //                     $meta_values = get_user_meta( $user->ID, 'corporate_companies', true );
                        $selected = isset($corporate_companies) ? $corporate_companies : '';
                        wp_dropdown_categories(array(
                            'show_option_all' => __("Select Corporate Facility"),
                            'taxonomy'        => $taxonomy,
                            'name'            => 'corporate_companies',
                            'orderby'         => 'name',
                            'selected'        => $selected,
                            'value_field' => 'id',
                            'hierarchical' => 1,
                            'hide_empty'      => 0,
                        ));
                    ?>
                    </td>
                </tr>
                <?php
                }
                if (!in_array("backend-admin", $user->roles) && !in_array("account_manager", $user->roles) && !in_array("business", $user->roles) && !in_array("subscriber_-_corporate", $user->roles)) {
                ?>
                <tr class="form-field">
                    <th scope="row"><label for="admin_facility">Admin Facility</label></th>
                    <td>
                    <?php
                        $post_type = 'users';
                        $taxonomy  = 'company';
                        $info_taxonomy = get_taxonomy($taxonomy);
 //                        $meta_values = get_user_meta( $user->ID, 'admin_companies', true );
                        $selected = isset($admin_companies) ? $admin_companies : '';
                        wp_dropdown_categories(array(
                            'show_option_all' => __("Select Admin Facility"),
                            'taxonomy'        => $taxonomy,
                            'name'            => 'admin_companies',
                            'orderby'         => 'name',
                            'selected'        => $selected,
                            'value_field' => 'id',
                            'hierarchical' => 1,
                            'hide_empty'      => 0,
                        ));
                    ?>
                    </td>
                </tr>
                <?php
                }
                if (!in_array("account_manager", $user->roles) && !in_array("backend-admin", $user->roles)) {
                ?>
                <tr class="form-field form-required field-account">
                    <th scope="row"><label for="account_manager">Account Manager <span class="description">(required)</span></label></th>
                    <td>
                        <select name="account_manager" id="account_manager" aria-required="true">
                            <option value="" <?php echo $account_manager == null ? 'selected' : ''; ?>>Select Account Manager</option>
                            <?php
                            $account_managers = PT_Defaults_Values::get_account_manager_users_list();
                            foreach ( $account_managers as $acc_manager ) {
                                $selected = selected( $account_manager,$acc_manager->user_email);
                                $first_name = get_the_author_meta( 'first_name', $acc_manager->ID );
                                echo '<option value="'. $acc_manager->user_email .'"'. $selected .'>';
                                if (isset($first_name) && !empty($first_name)) {
                                    echo $first_name . ' ' . get_the_author_meta( 'last_name', $acc_manager->ID );
                                } else {
                                    echo $acc_manager->user_email;
                                }
                                echo '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php }
                ?>
                
                <tr class="form-field">
                    <th scope="row"><label for="security_question">Security Question</label></th>
                    <td>
                        <select name="security_question" id="security_question">
                            <option value="" <?php echo $security_question == null ? 'selected' : ''; ?>>Select Security Question</option>
                            <?php
                            $security_questions = PT_Defaults_Values::get_security_questions();
                            foreach ( $security_questions as $value => $option ) {
                                $selected = selected($security_question,$option);
                                echo '<option value="'. $option .'"'. $selected .'>'. $option .'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                
                <tr class="form-field">
                    <th scope="row">
                        <label for="security_question_answer">Security Question Answer</label>
                    <td>
                        <input type="text" class="regular-text pt-text-field" name="security_question_answer" value="<?php echo $security_question_answer; ?>" id="security_question_answer" />
                    </td>
                </tr>

                <tr class="form-field hide-form-field">
                    <th scope="row">
                        <label for="company_assigned">Company Assigned</label>
                    <td>
                        <input type="text" class="regular-text pt-text-field" name="company_assigned" value="<?php echo $company_assigned_id; ?>" id="company_assigned" readonly />
                    </td>
                </tr>
                
            </table>
        <?php
        }

        public function save_custom_user_profile_fields($user_id) {
            /*if( !current_user_can('manage_options') )
                return false;*/

            if(isset($_POST['user_status'])) {
                update_user_meta($user_id, 'user_status', $_POST['user_status']);
            }

            if ($_POST['user_status'] === 'disable') {
                $current_user = wp_get_current_user();
                $get_user_author = get_userdata( $current_user->ID );
                update_user_meta($user_id, 'disabled_date', current_time('m/d/Y').' by '. $get_user_author->user_email);
            } elseif ($_POST['user_status'] === 'enable') {
                delete_user_meta($user_id, 'disabled_date');
            }

            if(isset($_POST['trusted_refer'])) {
                update_user_meta($user_id, 'trusted_refer', $_POST['trusted_refer']);
            }
            if(isset($_POST['tools_templates_access'])) {
                update_user_meta($user_id, 'tools_templates_access', $_POST['tools_templates_access']);
            }
            if(isset($_POST['policies_procedures_access'])) {
                update_user_meta($user_id, 'policies_procedures_access', $_POST['policies_procedures_access']);
            }
            if(isset($_POST['frontend_docs_upload_access'])) {
                update_user_meta($user_id, 'frontend_docs_upload_access', $_POST['frontend_docs_upload_access']);
            }
            if(isset($_POST['work_phone'])) {
                update_user_meta($user_id, 'work_phone', $_POST['work_phone']);
            }
            if(isset($_POST['street_1'])) {
                update_user_meta($user_id, 'street_1', $_POST['street_1']);
            }
            if(isset($_POST['street_2'])) {
                update_user_meta($user_id, 'street_2', $_POST['street_2']);
            }
            if(isset($_POST['city'])) {
                update_user_meta($user_id, 'city', $_POST['city']);
            }
            if(isset($_POST['state'])) {
                update_user_meta($user_id, 'state', $_POST['state']);
            }
            if(isset($_POST['zip_code'])) {
                update_user_meta($user_id, 'zip_code', $_POST['zip_code']);
            }
            if(isset($_POST['title'])) {
                update_user_meta($user_id, 'title', $_POST['title']);
            }

            if(isset($_POST['account_manager'])) {
                update_user_meta($user_id, 'account_manager', $_POST['account_manager']);
            }
            if(isset($_POST['security_question'])) {
                update_user_meta($user_id, 'security_question', $_POST['security_question']);
            }
            if(isset($_POST['security_question_answer'])) {
                update_user_meta($user_id, 'security_question_answer', $_POST['security_question_answer']);
            }
            if(isset($_POST['start_date'])) {
                update_user_meta($user_id, 'start_date', $_POST['start_date']);
            }
            if(isset($_POST['end_date'])) {
                update_user_meta($user_id, 'end_date', $_POST['end_date']);
            }
            if(isset($_POST['business_companies'])) {
                update_user_meta( $user_id, 'business_companies', $_POST['business_companies'] );
                update_users_company_count( $_POST['business_companies'], 'business_companies' );
            }
            if(isset($_POST['corporate_companies'])) {
                update_user_meta( $user_id, 'corporate_companies', $_POST['corporate_companies'] );
                update_users_company_count( $_POST['corporate_companies'], 'corporate_companies' );
            }
            if(isset($_POST['admin_companies'])) {
                update_user_meta( $user_id, 'admin_companies', $_POST['admin_companies'] );
                update_users_company_count( $_POST['admin_companies'], 'admin_companies' );
            }

        }

        public function generate_custom_user_profile_fields($user_id) {
            global $wpdb;

            update_user_meta($user_id, 'create_date', current_time('m/d/Y'));
            $current_user = wp_get_current_user();
            update_user_meta( $user_id, 'created_by', $current_user->ID );

            $get_user = get_userdata( $user_id );
            $get_user_author = get_userdata( $current_user->ID );

            $email = $_POST['email'];
            $nickname = explode('@',$email);
            $nickname = $nickname[0];
            update_user_meta($user_id, 'nickname', $nickname);
            $wpdb->update(
                $wpdb->prefix.'users',
                array( 'display_name' => $nickname ),
                array( 'ID' => $user_id )
            );

            $message = "";
            $message .= "Admin,<br><br>";
            $message .= "A New User: ".$get_user->user_email." has been registered on Secure Site.<br><br>";
            $message .= "Created by ".$get_user_author->user_email."<br><br>";
            $message .= "Thanks,";
            $subject = "New User Registration by ".$get_user_author->user_email;
            $headers = array();
            add_filter( 'wp_mail_content_type', function( $content_type ) {
                return 'text/html';
            });
            $admin_email = get_option( 'admin_email' );
            //$headers[] = 'From: The Compliance Store <customerservice@thecompliancestore.com>'."\r\n";
            wp_mail( $admin_email, $subject, $message);

            remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
        }

        public function profile_update_custom_user_profile_fields($user_id) {
            global $wpdb;

            $current_user = wp_get_current_user();
            /*update_user_meta( $user_id, 'created_by', $current_user->ID );*/

            $get_user = get_userdata( $user_id );
            $get_user_author = get_userdata( $current_user->ID );

            $nickname = $_POST['nickname'];
            $wpdb->update(
                $wpdb->prefix.'users',
                array( 'display_name' => $nickname ),
                array( 'ID' => $user_id )
            );

            $message = "";
            $message .= "Admin,<br><br>";
            $message .= "A User: ".$get_user->user_email." has been updated on Secure Site.<br><br>";
            $message .= "Updated by ".$get_user_author->user_email."<br><br>";
            $message .= "Thanks,";
            $subject = "User Updated by ".$get_user_author->user_email;
            $headers = array();
            add_filter( 'wp_mail_content_type', function( $content_type ) {
                return 'text/html';
            });
            $admin_email = get_option( 'admin_email' );
            //$headers[] = 'From: The Compliance Store <customerservice@thecompliancestore.com>'."\r\n";
            wp_mail( $admin_email, $subject, $message);

            remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
        }
    }	

    new PT_User_Fields();
}