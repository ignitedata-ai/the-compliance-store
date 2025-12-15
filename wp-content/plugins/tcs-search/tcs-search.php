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
 * @since             1.1.0
 * @package           Tcs_Search
 *
 * @wordpress-plugin
 * Plugin Name:       TCS Extended Search
 * Plugin URI:        https://www.presstigers.com/
 * Description:       This plugin will provide an option in admin area to enable/disable extended search.
 * Version:           1.1.0
 * Author:            PressTigers
 * Author URI:        https://www.presstigers.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tcs-search
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
define( 'TCS_SEARCH_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tcs-search-activator.php
 */
function activate_tcs_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tcs-search-activator.php';
	Tcs_Search_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tcs-search-deactivator.php
 */
function deactivate_tcs_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tcs-search-deactivator.php';
	Tcs_Search_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tcs_search' );
register_deactivation_hook( __FILE__, 'deactivate_tcs_search' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tcs-search.php';


include_once('includes/tcs-search-admin-menu-handler.php');
include_once('admin/partials/tcs-search-admin-display.php');



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tcs_search() {

	$plugin = new Tcs_Search();
	$plugin->run();

}
run_tcs_search();
