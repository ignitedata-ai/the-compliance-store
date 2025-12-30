<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.presstigers.com/
 * @since      1.0.0
 *
 * @package    Delete_Disabled_Users
 * @subpackage Delete_Disabled_Users/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Delete_Disabled_Users
 * @subpackage Delete_Disabled_Users/includes
 * @author     PressTigers <mstypinski@turenneteam.com>
 */
class Delete_Disabled_Users_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'delete-disabled-users',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
