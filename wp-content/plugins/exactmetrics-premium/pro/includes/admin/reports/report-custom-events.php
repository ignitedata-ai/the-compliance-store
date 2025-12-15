<?php
/**
 * Custom Events Report class
 *
 * @package ExactMetrics
 * @subpackage Reports
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExactMetrics_Report_Custom_Events extends ExactMetrics_Report {

	public $class = 'ExactMetrics_Report_Custom_Events';
	public $name  = 'custom_events';
	public $level = 'pro';

	protected $api_path = 'custom-events';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		$this->title = __( 'Custom Events', 'exactmetrics-premium' );

		parent::__construct();
	}

}
