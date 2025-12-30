<?php

// Load main class
require_once dirname(dirname(__FILE__)).'/core/plugin.php';

/**
 * Plugin class
 *
 * @package WP Link Status Pro
 * @subpackage Core
 */
class WPLNST_Core_Pro_Plugin extends WPLNST_Core_Plugin {



	/**
	 * URL to the tools section
	 */
	public static function get_url_tools_url() {
		return self::get_url_scans().'-tools-url';
	}



}