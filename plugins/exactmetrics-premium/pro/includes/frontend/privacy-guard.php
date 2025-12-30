<?php

/**
 * Load JS file to manage privacy guard functionality.
 */
function exactmetrics_add_privacy_guard_script_tag() {
	if ( ! exactmetrics_get_option( 'privacy_guard', false ) ) {
		return;
	}

	$suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$src         = plugins_url( 'pro/assets/js/privacy-guard' . $suffix . '.js', EXACTMETRICS_PLUGIN_FILE );
	$attr_string = exactmetrics_get_frontend_analytics_script_atts();

	printf( '<script src="%s" %s></script>' . PHP_EOL, $src, $attr_string ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- False positive.
}

add_action( 'exactmetrics_tracking_gtag_frontend_before_script_tag', 'exactmetrics_add_privacy_guard_script_tag' );
