<?php
/**
 * Traffic Landing Pages Report
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

final class ExactMetrics_Report_Traffic_Landing_Pages extends ExactMetrics_Report {

	public $class = 'ExactMetrics_Report_Traffic_Landing_Pages';
	public $name  = 'traffic_landing_pages';
	public $level = 'plus';

	protected $api_path = 'traffic-landing-pages';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		$this->title = __( 'Landing Page Details', 'exactmetrics-premium' );

		parent::__construct();
	}

	/**
	 * Add necessary information to data for Vue reports.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function prepare_report_data( $data ) {
		return apply_filters( 'exactmetrics_report_traffic_sessions_chart_data', $data, $this->start_date, $this->end_date );
	}

}
