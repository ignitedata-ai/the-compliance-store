<?php

add_action('init', function () {

	if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports.php';
		new ExactMetrics_Admin_Pro_Reports();

		// Email summaries related classes
		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/emails/summaries-infoblocks.php';
		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/emails/summaries.php';
		new ExactMetrics_Email_Summaries();

		// SharedCounts functionality.
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/sharedcount.php';

		// Include notification events of pro version
		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/notifications/notification-events.php';

		// Load API classes
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/api/class-exactmetrics-api-error.php';
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/api/class-exactmetrics-api.php';
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/api/class-exactmetrics-api-reports.php';
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/api/class-exactmetrics-api-tracking.php';
		
		// Pro-only API
		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/api/class-exactmetrics-api-ads.php';

		// Load Google Ads admin classes
		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/ppc/google/class-exactmetrics-google-ads.php';
	}

	if ( is_admin() ) {

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/dashboard-widget.php';
		new ExactMetrics_Dashboard_Widget_Pro();

		// Load the Welcome class.
		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/welcome.php';

		if ( isset( $_GET['page'] ) && 'exactmetrics-onboarding' === $_GET['page'] ) { // phpcs:ignore -- CSRF ok, input var ok.
			// Only load the Onboarding wizard if the required parameter is present.
			require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/onboarding-wizard.php';
		}

		//  Common Site Health logic
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/wp-site-health.php';

		//  Pro-only Site Health logic
		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/wp-site-health.php';

		if (
			class_exists( 'ExactMetrics_eCommerce' ) &&
			file_exists( WP_PLUGIN_DIR . '/exactmetrics-user-journey/exactmetrics-user-journey.php' ) &&
			! class_exists( 'ExactMetrics_User_Journey' )
		) {
			// Initialize User Journey
			require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/user-journey/init.php';
		}
	}

	//  Gtag selector
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/gtag-selector.php';

	//  AI Insights
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/ai/class-exactmetrics-ai-insights.php';

	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/frontend/class-frontend.php';

	// Popular posts.
	require_once EXACTMETRICS_PLUGIN_DIR . 'includes/popular-posts/class-popular-posts-themes.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'includes/popular-posts/class-popular-posts.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'includes/popular-posts/class-popular-posts-helper.php';
	// Pro popular posts specific.
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/popular-posts/class-popular-posts-inline.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/popular-posts/class-popular-posts-cache.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/popular-posts/class-popular-posts-widget.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/popular-posts/class-popular-posts-widget-sidebar.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/popular-posts/class-popular-posts-ajax.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/popular-posts/class-popular-posts-ga.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/popular-posts/class-popular-posts-products.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/popular-posts/class-popular-posts-products-sidebar.php';
	// Pro Gutenberg blocks.
	require_once EXACTMETRICS_PLUGIN_DIR . 'includes/gutenberg/exactmetrics-stats-block.php';
	// Privacy Guard.
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/frontend/privacy-guard.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/gutenberg/frontend.php';
	require_once EXACTMETRICS_PLUGIN_DIR . 'includes/connect.php';

	// Run hook to load ExactMetrics addons.
	do_action( 'exactmetrics_load_plugins' ); // the updater class for each addon needs to be instantiated via `exactmetrics_updater`

	if ( !is_admin() ) {
		// Load PPC Core
		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/ppc/class-exactmetrics-ppc-tracking-core.php';
	}
}, 0 );