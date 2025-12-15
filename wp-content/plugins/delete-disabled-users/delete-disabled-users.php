<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.presstigers.com/
 * @since             1.0.0
 * @package           Delete_Disabled_Users
 *
 * @wordpress-plugin
 * Plugin Name:       Delete Disabled Users
 * Plugin URI:        https://presstigers.com/
 * Description:       This plugin will list those disabled users that didn’t logged-in for 6 months. They can be deleted or exported. Note: This plugin requires Bridge Child Theme to be activated.
 * Version:           1.0.0
 * Author:            PressTigers
 * Author URI:        https://www.presstigers.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       delete-disabled-users
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('DELETE_DISABLED_USERS_VERSION', '1.0.0' );
define('DELETE_DISABLED_USERS_DIR_NAME', dirname(plugin_basename(__FILE__)));
define('DELETE_DISABLED_USERS_URL', plugins_url('', __FILE__));
define('DELETE_DISABLED_USERS_PATH', plugin_dir_path(__FILE__));
define('DELETE_DISABLED_USERS_SITE_HOME_URL', home_url());

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-delete-disabled-users-activator.php
 */
function activate_delete_disabled_users() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-delete-disabled-users-activator.php';
	Delete_Disabled_Users_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_delete_disabled_users' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-delete-disabled-users-deactivator.php
 */
/*function deactivate_delete_disabled_users() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-delete-disabled-users-deactivator.php';
	Delete_Disabled_Users_Deactivator::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivate_delete_disabled_users' );*/

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-delete-disabled-users.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_delete_disabled_users() {

	$plugin = new Delete_Disabled_Users();
	$plugin->run();

}
run_delete_disabled_users();