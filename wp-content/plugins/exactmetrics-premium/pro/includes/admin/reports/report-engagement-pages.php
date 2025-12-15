<?php
/**
 * Engagement Pages Report
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

final class ExactMetrics_Report_Engagement_Pages extends ExactMetrics_Report {

	public $class = 'ExactMetrics_Report_Engagement_Pages';
	public $name  = 'engagement_pages';
	public $level = 'plus';

	protected $api_path = 'engagement-pages';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		$this->title = __( 'Pages Report', 'exactmetrics-premium' );

		parent::__construct();
	}

}
