<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.presstigers.com/
 * @since      1.0.0
 *
 * @package    Delete_Disabled_Users
 * @subpackage Delete_Disabled_Users/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Delete_Disabled_Users
 * @subpackage Delete_Disabled_Users/includes
 * @author     PressTigers <mstypinski@turenneteam.com>
 */
class Delete_Disabled_Users_Activator {

	/**
	 * Create disabled_users table in db if not exist.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

	    // Flush rules after install/activation
	    flush_rewrite_rules();
	}
}