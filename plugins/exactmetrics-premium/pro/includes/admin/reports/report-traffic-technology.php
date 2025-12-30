<?php
/**
 * Traffic Technology Report
 *
 * Ensures all the reports have a uniform class with helper functions.
 *
 * @since 8.17
 *
 * @package ExactMetrics
 * @subpackage Reports
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExactMetrics_Report_Traffic_Technology extends ExactMetrics_Report {

	public $class = 'ExactMetrics_Report_Traffic_Technology';
	public $name  = 'traffic_technology';
	public $level = 'plus';

	protected $api_path = 'traffic-technology';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		$this->title = __( 'Traffic Technology', 'exactmetrics-premium' );

		parent::__construct();
	}

}
