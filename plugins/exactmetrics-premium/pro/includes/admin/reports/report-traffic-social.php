<?php
/**
 * Traffic Social Report
 *
 * Ensures all the reports have a uniform class with helper functions.
 *
 * @package ExactMetrics
 * @subpackage Reports
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExactMetrics_Report_Traffic_Social extends ExactMetrics_Report {

	public $class = 'ExactMetrics_Report_Traffic_Social';
	public $name  = 'traffic_social';
	public $level = 'plus';

	protected $api_path = 'traffic-social';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		$this->title = __( 'Traffic Social', 'exactmetrics-premium' );

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
		if ( isset( $data['data']['social_table'] ) && is_array( $data['data']['social_table'] ) ) {
			foreach ( $data['data']['social_table'] as &$social ) {
				$social['icon'] = $this->get_social_network_icon( $social['network'] );
			}
		}

		return apply_filters( 'exactmetrics_report_traffic_sessions_chart_data', $data, $this->start_date, $this->end_date );
	}

	/**
	 * Get social network icon file name of SVG file.
	 *
	 * @return string|array
	 */
	private function get_social_network_icon( $network ) {
		if ( is_array( $network ) ) {
			$network = $network[0];
		}

		$filename = strtolower( $network );

		if ( 'Twitter' == $network ) {
			$filename = 'x';
		}

		if ( preg_match("/blogspot\.com/i", $network ) ) {
			$filename = 'blogspot';
		}

		if ( preg_match("/feedspot\.com/i", $network ) ) {
			$filename = 'feedspot';
		}

		if ( file_exists( EXACTMETRICS_PLUGIN_DIR . 'pro/assets/img/social/icon-' . $filename . '.svg' ) ) {
			return $filename;
		}

		return '';
	}
}
