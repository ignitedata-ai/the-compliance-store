<?php
/**
 * Create custom dimension and metrics.
 */
class ExactMetrics_Dimensions_Custom_Definitions {

	/**
	 * Holds singleton instance
	 */
	private static $instance;

	/**
	 * Option key for storing error message.
	 */
	private $option_key = 'exactmetrics_dimensions_definitions_error';

	/**
	 * Required dimensions.
	 *
	 * @var array
	 */
	private $required_dimensions = array(
		'aioseo_focus_keyphrase',
		'aioseo_truseo_score',
		'author',
		'category',
		'focus_keyword',
		'logged_in',
		'post_type',
		'published_at',
		'modified_at',
		'publish_day_of_week',
		'seo_score',
		'tags',
	);

	/**
	 * Return singleton instance
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor for singleton instance.
	 */
	private function __construct() {
		register_activation_hook( EXACTMETRICS_DIMENSIONS___FILE__, array( $this, 'activation_hook' ) );

		add_action( 'admin_notices', array( $this, 'show_notice' ) );
		add_action( 'admin_init', array( $this, 'retry' ) );
	}

	/**
	 * Run this callback when plugin activated.
	 */
	public function activation_hook() {
		$message = get_option( $this->option_key );

		if ( $message ) {
			delete_option( $this->option_key );
		}

		$message = sprintf(
			// Translators: 1: Opening hyperlink 2: Closing hyperlink
			__( 'ExactMetrics Dimensions Addon could not create custom dimensions and metrics in order to work properly. Please %1$stry again%2$s.', 'exactmetrics-dimensions' ),
			'<a href="' . admin_url( 'admin.php?exactmetrics_dimensions_definitions_retry=true' ) . '">',
			'</a>'
		);

		// ExactMetrics plugin is not installed.
		if ( ! class_exists( 'ExactMetrics_API_Request' ) ) {
			add_option( $this->option_key, $message );
			return;
		}

		$response = $this->send_api_request();

		if ( is_wp_error( $response ) ) {
			add_option( $this->option_key, $message );
		}

		if ( ! $response ) {
			add_option( $this->option_key, $message );
		}
	}

	/**
	 * Send checker api request.
	 *
	 * @return WP_Error|array
	 */
	private function send_api_request() {
		// Send request to API.
		$api = new ExactMetrics_API_Request( 'analytics/create-definitions/', array(), 'GET' );
		$response = $api->request(
			array(
				'dimensions' => $this->required_dimensions,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Check we get the proper data.
		if ( isset( $response['success'] ) && ! empty( $response['success'] ) ) {
			return $response['success'];
		}

		return new WP_Error( 'create-definitions-api-error', 'Could not find response data.' );
	}

	/**
	 * Display notice for error creating custom definitions.
	 */
	public function show_notice() {
		$message = get_option( $this->option_key );

		if ( ! $message ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p><?php echo wp_kses_post( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Re-try creation of custom dimestion and metrics.
	 */
	public function retry() {
		if ( ! $this->is_retry_route() ) {
			return;
		}

		// Run the process.
		$this->activation_hook();

		// Redirect back.
		wp_safe_redirect( wp_get_referer() );
		exit();
	}

	/**
	 * Is user request to retry.
	 *
	 * @return bool
	 */
	private function is_retry_route() {
		if ( isset( $_GET['exactmetrics_dimensions_definitions_retry'] ) && 'true' === $_GET['exactmetrics_dimensions_definitions_retry'] ) {
			return true;
		}

		return false;
	}
}

ExactMetrics_Dimensions_Custom_Definitions::get_instance();
