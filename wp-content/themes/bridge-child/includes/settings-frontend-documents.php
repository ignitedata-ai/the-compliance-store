<?php
/**
 * Add settings page under frontend document custom post type
 */
function tcs_fd_add_settings_page() {
    add_submenu_page(
        'edit.php?post_type=frontend_documents',
        __('Frontend Documents Settings', 'frontend-documents'),
        __('Frontend Documents Settings', 'frontend-documents'),
        'manage_options',
        'frontend-documents-settings',
        'tcs_fd_render_settings_page'
    );
    
    // Register settings
    add_action('admin_init', 'tcs_fd_register_settings');
}
add_action('admin_menu', 'tcs_fd_add_settings_page');

/**
 * Register settings
 */
function tcs_fd_register_settings() {
    register_setting('tcs_fd_settings_group', 'tcs_fd_upload_access');
    register_setting('tcs_fd_settings_group', 'tcs_fd_display_message');
    
    // Add settings section with empty callback
    add_settings_section(
        'tcs_fd_settings_section',
        __('Frontend Documents Settings', 'frontend-documents'),
        '__return_empty_string', // Empty callback
        'frontend-documents-settings'
    );
    
    // Add settings fields
    add_settings_field(
        'tcs_fd_upload_access',
        __('Access to upload frontend document:', 'frontend-documents'),
        'tcs_fd_upload_access_callback',
        'frontend-documents-settings',
        'tcs_fd_settings_section'
    );
    
    add_settings_field(
        'tcs_fd_display_message',
        __('Display Message:', 'frontend-documents'),
        'tcs_fd_display_message_callback',
        'frontend-documents-settings',
        'tcs_fd_settings_section'
    );
}

/**
 * Upload access field callback
 */
function tcs_fd_upload_access_callback() {
    $upload_access = get_option('tcs_fd_upload_access', 'enable');
    ?>
    <select name="tcs_fd_upload_access" id="tcs_fd_upload_access">
        <option value="enable" <?php selected($upload_access, 'enable'); ?>><?php _e('Enable', 'frontend-documents'); ?></option>
        <option value="disable" <?php selected($upload_access, 'disable'); ?>><?php _e('Disable', 'frontend-documents'); ?></option>
    </select>
    <?php
}

/**
 * Display message field callback
 */
function tcs_fd_display_message_callback() {
    $display_message = get_option('tcs_fd_display_message', '');
    wp_editor(
        wp_unslash($display_message),
        'tcs_fd_display_message',
        array(
            'textarea_name' => 'tcs_fd_display_message',
            'media_buttons' => false,
            'textarea_rows' => 10,
            'tinymce' => array(
                'toolbar1' => 'formatselect,bold,italic,bullist,numlist,alignleft,aligncenter,alignright,link,unlink,undo,redo',
                'toolbar2' => '',
                'block_formats' => 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4'
            )
        )
    );
}

/**
 * Render settings page
 */
function tcs_fd_render_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Show success message if settings were updated
    if (isset($_GET['settings-updated'])) {
        add_settings_error(
            'tcs_fd_messages',
            'tcs_fd_message',
            __('Settings Saved.', 'frontend-documents'),
            'updated'
        );
    }
    
    // Show error/update messages
    settings_errors('tcs_fd_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html('');?></h1>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('tcs_fd_settings_group');
            do_settings_sections('frontend-documents-settings');
            submit_button(__('Save Settings', 'frontend-documents'));
            ?>
        </form>
    </div>
    <?php
}