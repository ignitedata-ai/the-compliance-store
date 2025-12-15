<?php
/**
 * Template file to render filter form on the listing table.
 *
 * @link       https://github.com/faiyazalam
 *
 * @package    User_Login_History
 * @subpackage User_Login_History/admin/partials/form
 */

// Sanitize the page parameter before using it in URLs
$page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
$reset_URI = "admin.php?page=" . esc_attr($page);
$reset_URL = is_network_admin() ? network_admin_url($reset_URI) : admin_url($reset_URI);
/**
 * Download using ajax functionality
 */
?>

<!-- Login History Export Button -->

<div class="<?php echo esc_attr($this->plugin_name); ?>-search-filter">
    <form name="<?php echo esc_attr($this->plugin_name . '-search-form'); ?>" method="get" action="" id="<?php echo esc_attr($this->plugin_name . '-search-form'); ?>">
        <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>" />
        <input type="hidden" name="order" value="<?php echo !empty($_GET['order']) ? esc_attr(sanitize_text_field($_GET['order'])) : ""; ?>" />
        <input type="hidden" name="orderby" value="<?php echo !empty($_GET['orderby']) ? esc_attr(sanitize_text_field($_GET['orderby'])) : ""; ?>" />
        <div class="secondRow">
            <div><input readonly autocomplete="off" placeholder="<?php esc_html_e("From", "faulh"); ?>" id="date_from" name="date_from" value="<?php echo isset($_GET['date_from']) ? esc_attr(sanitize_text_field($_GET['date_from'])) : ""; ?>" ></div>
            <div><input readonly autocomplete="off" placeholder="<?php esc_html_e("To", "faulh"); ?>" name="date_to" id="date_to" value="<?php echo isset($_GET['date_to']) ? esc_attr(sanitize_text_field($_GET['date_to'])) : ""; ?>" ></div>
            <div> <select id="user_type" name="date_type" >
                    <?php Faulh_Template_Helper::dropdown_time_field_types(isset($_GET['date_type']) ? sanitize_text_field($_GET['date_type']) : NULL); ?>
                </select></div>
        </div>
        <div class="secondRow">
            <div><input type="number" placeholder="<?php esc_html_e("Enter User ID", "faulh"); ?>" id="user_id" name="user_id" value="<?php echo isset($_GET['user_id']) ? esc_attr(absint($_GET['user_id'])) : ""; ?>" ></div>
            <div><input placeholder="<?php esc_html_e("Enter Username/Email", "faulh"); ?>" id="username" name="username" value="<?php echo isset($_GET['username']) ? esc_attr(sanitize_user($_GET['username'])) : ""; ?>" ></div>
            
            <?php if (is_network_admin()) { ?>
                <div><input placeholder="<?php esc_html_e("Blog ID", "faulh"); ?>" name="blog_id" value="<?php echo isset($_GET['blog_id']) ? esc_attr(absint($_GET['blog_id'])) : ""; ?>" ></div>
            <?php } ?>
        </div>

        <div class="secondRow">
            <div><select id="user_role" name="role">
                    <option value=""><?php esc_html_e("Select Current Role", "faulh"); ?></option>
                    <?php
                    $selected_role = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : NULL;
                    wp_dropdown_roles($selected_role);
                    ?>
                    <?php if (is_network_admin()) { ?>
                        <option value="superadmin" <?php selected($selected_role, "superadmin"); ?> ><?php esc_html_e("Super Administrator", "faulh"); ?></option>
                    <?php } ?>
                </select></div>

            <div> <select id="login_status" name="login_status">
                    <option value=""><?php esc_html_e('Select Login Status', 'faulh'); ?></option>
                    <?php $selected_login_status = isset($_GET['login_status']) ? sanitize_text_field($_GET['login_status']) : ""; ?>
                    <option value="unknown" <?php selected($selected_login_status, "unknown"); ?> ><?php esc_html_e('Unknown', 'faulh'); ?></option>
                    <?php Faulh_Template_Helper::dropdown_login_statuses($selected_login_status); ?>
                </select></div>
            <div> <select id="user_status" name="user_status">
                    <option value=""><?php esc_html_e('Select User Status', 'faulh'); ?></option>
                    <?php $selected_user_status = isset($_GET['user_status']) ? sanitize_text_field($_GET['user_status']) : ""; ?>
                    <option value="enable" <?php selected($selected_user_status, "enable"); ?>>Enable</option>
                    <option value="disable" <?php selected($selected_user_status, "disable"); ?>>Disable</option>
                </select>
            </div>
            <div> <select id="t_t_status" name="t_t_status">
                    <option value=""><?php esc_html_e('Select T&T Access', 'faulh'); ?></option>
                    <?php $selected_t_t_status = isset($_GET['t_t_status']) ? sanitize_text_field($_GET['t_t_status']) : ""; ?>
                    <option value="enable" <?php selected($selected_t_t_status, "enable"); ?>>Enable</option>
                    <option value="disable" <?php selected($selected_t_t_status, "disable"); ?>>Disable</option>
                </select>
            </div>
            <div> <select id="p_p_status" name="p_p_status">
                    <option value=""><?php esc_html_e('Select P&P Access', 'faulh'); ?></option>
                    <?php $selected_p_p_status = isset($_GET['p_p_status']) ? sanitize_text_field($_GET['p_p_status']) : ""; ?>
                    <option value="enable" <?php selected($selected_p_p_status, "enable"); ?>>Enable</option>
                    <option value="disable" <?php selected($selected_p_p_status, "disable"); ?>>Disable</option>
                </select>
            </div>
            <?php if (is_network_admin()) { ?>
                <div>
                    <select name="is_super_admin">
                        <option value=""><?php esc_html_e('Select Super Admin', 'faulh'); ?></option>
                        <?php
                        Faulh_Template_Helper::dropdown_is_super_admin(isset($_GET['is_super_admin']) ? sanitize_text_field($_GET['is_super_admin']) : NULL);
                        ?>
                    </select>
                </div>
            <?php }
                $post_type = 'users';
                $taxonomy  = 'company';
                $info_taxonomy = get_taxonomy($taxonomy);
                $selected_company = isset($_GET['filter_company']) ? absint($_GET['filter_company']) : 0;
                $selected = $selected_company;
                wp_dropdown_categories(array(
                    'show_option_all' => __("Select Company"),
                    'taxonomy'        => $taxonomy,
                    'name'            => 'filter_company',
                    'orderby'         => 'name',
                    'selected'        => $selected,
                    'value_field' => 'id',
                    'hierarchical' => 1,
                    'hide_empty'      => 0,
                ));
            ?>
        </div>
        <?php do_action('faulh_admin_listing_search_form'); ?>
        <div class="submitAction">
            <div id="publishing-action">
                <a class="faulhbtn action" href="<?php echo esc_url($reset_URL); ?>"><?php esc_html_e('RESET', 'faulh'); ?></a>
                <input type="hidden" name="<?php echo esc_attr($this->plugin_name); ?>_export_csv" id="export-csv" value="">
                <input type="hidden" name="<?php echo esc_attr($this->plugin_name); ?>_export_nonce" id="<?php echo esc_attr($this->plugin_name); ?>_export_nonce" value="<?php echo esc_attr(wp_create_nonce($this->plugin_name . '_export_csv')); ?>">
                <input class="faulhbtn faulh-btn-primary" id="submit" type="submit" name="submit" value="<?php esc_html_e('FILTER', 'faulh'); ?>" />
            </div>
        </div>
    </form>
    <div class="inside">
        <form id="faulh-export-all" method="get" action="">
            <label for="fname" class="uemail-label"><b><?php esc_html_e('Please enter your email to get download file link', 'faulh'); ?></b></label><br>
            <input type="email" id="uemail" name="uemail" required><br>
            <div class="submit" style="margin-top:-10px;">
                <input id="dwn-btn" type="submit" class="button" name="reports_export_users" value="<?php esc_attr_e('Download CSV', 'faulh'); ?>">
            </div>
            <div id="all-cnt" class="faulh-ajax-res-all-user-export">
            </div>
        </form>
    </div>
    <br class="clear">
</div>