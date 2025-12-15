<?php
/**
 * Traffic Source / Medium Report
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

final class ExactMetrics_Report_Traffic_Source_Medium extends ExactMetrics_Report {

	public $class = 'ExactMetrics_Report_Traffic_Source_Medium';
	public $name  = 'traffic_source_medium';
	public $level = 'plus';

	protected $api_path = 'traffic-source-medium';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		$this->title = __( 'Traffic Source / Medium', 'exactmetrics-premium' );

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
		$data = apply_filters( 'exactmetrics_report_traffic_sessions_chart_data', $data, $this->start_date, $this->end_date );
	
		if ( ! empty( $data['data'] ) ) {
			if ( empty( $data['data']['galinks'] ) || ! is_array( $data['data']['galinks'] ) ) {
				$data['data']['galinks'] = array();
			}

			// Link to GA4 Traffic Acquisition with Source/Medium dimension selected.
			$data['data']['galinks']['traffic_source_medium'] = $this->get_ga_report_url(
				'lifecycle-traffic-acquisition',
				$data['data'],
				'_r.explorerCard..seldim=["sessionSourceMedium"]'
			);
		}
		return $data;
	}

}
