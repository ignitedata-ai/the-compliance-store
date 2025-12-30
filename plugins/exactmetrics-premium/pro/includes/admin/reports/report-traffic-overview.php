<?php
/**
 * Traffic Overview Report
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

final class ExactMetrics_Report_Traffic_Overview extends ExactMetrics_Report {

	public $class = 'ExactMetrics_Report_Traffic_Overview';
	public $name  = 'traffic_overview';
	public $level = 'plus';

	protected $api_path = 'traffic-overview';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		$this->title = __( 'Traffic Overview', 'exactmetrics-premium' );

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
		// Allow filters to modify the initial data first.
		$data = apply_filters( 'exactmetrics_report_traffic_sessions_chart_data', $data, $this->start_date, $this->end_date );

		if ( ! empty( $data['data'] ) ) {
			if ( empty( $data['data']['galinks'] ) || ! is_array( $data['data']['galinks'] ) ) {
				$data['data']['galinks'] = array();
			}

			// Link to GA4 Traffic Acquisition (default channel group).
			$data['data']['galinks']['traffic_channels'] = $this->get_ga_report_url( 'lifecycle-traffic-acquisition', $data['data'] );
		}

		return $data;
	}

}
