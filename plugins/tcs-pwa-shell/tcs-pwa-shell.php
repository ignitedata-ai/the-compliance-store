<?php
/**
 * Plugin Name: TCS PWA Shell
 * Description: Provides a shell endpoint for the WordPress app under /pwa.
 * Version: 1.0.0
 * Author: TCS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue shell styles and inline helpers only for /pwa routes.
 *
 * The shell is rendered by WordPress while the React app is served separately
 * under /app via Nginx, so we avoid loading any React assets here.
 */
function tcs_pwa_shell_enqueue_styles() {
	if ( ! get_query_var( 'tcs_pwa_shell' ) ) {
		return;
	}

	// React is served via Nginx at /app; WordPress only renders the /pwa shell.

	$css = <<<CSS
.tcs-pwa-container {
	display: flex;
	flex-direction: column;
	min-height: calc(100vh - var(--tcs-pwa-offset, 0px));
}
.tcs-pwa-frame {
	width: 100%;
	border: 0;
	flex: 1 1 auto;
	min-height: calc(100vh - var(--tcs-pwa-offset, 0px));
}
CSS;

	wp_register_style( 'tcs-pwa-shell', false );
	wp_enqueue_style( 'tcs-pwa-shell' );
	wp_add_inline_style( 'tcs-pwa-shell', $css );

	$js = <<<JS
(function() {
	function getHeaderElement() {
		return document.querySelector('header, .site-header, #masthead, .qodef-page-header, .bridge-header, .header_top');
	}

	function applyPwaOffsets() {
		var container = document.querySelector('.tcs-pwa-container');
		if (!container) {
			return;
		}

		var adminBar = document.getElementById('wpadminbar');
		var adminBarHeight = adminBar ? adminBar.offsetHeight : 0;
		var header = getHeaderElement();
		var headerHeight = header ? header.offsetHeight : 0;
		var totalOffset = adminBarHeight + headerHeight;

		// Dynamic measurement is required because fixed headers and admin bar heights vary by theme/device.
		container.style.paddingTop = totalOffset + 'px';
		container.style.setProperty('--tcs-pwa-offset', totalOffset + 'px');
	}

	window.addEventListener('load', applyPwaOffsets);
	window.addEventListener('resize', applyPwaOffsets);
})();
JS;

	wp_register_script( 'tcs-pwa-shell', '', array(), null, true );
	wp_enqueue_script( 'tcs-pwa-shell' );
	$nonce = wp_create_nonce( 'wp_rest' );
	wp_add_inline_script(
		'tcs-pwa-shell',
		'window.tcsWpNonce = ' . wp_json_encode( $nonce ) . ';',
		'before'
	);
	wp_add_inline_script( 'tcs-pwa-shell', $js );
}
add_action( 'wp_enqueue_scripts', 'tcs_pwa_shell_enqueue_styles' );

/**
 * Add a rewrite rule to capture /pwa and /pwa/* for the shell.
 *
 * /app is intentionally excluded because Nginx serves the React app there.
 */
function tcs_pwa_shell_add_rewrite_rules() {
	// Capture /pwa and /pwa/*; /app is handled by Nginx + the React static server.
	add_rewrite_rule( '^pwa(?:/.*)?$', 'index.php?tcs_pwa_shell=1', 'top' );
}
add_action( 'init', 'tcs_pwa_shell_add_rewrite_rules' );

/**
 * Register a query var so WordPress recognizes the shell route.
 *
 * @param array $vars Query vars.
 * @return array
 */
function tcs_pwa_shell_query_vars( $vars ) {
	$vars[] = 'tcs_pwa_shell';
	return $vars;
}
add_filter( 'query_vars', 'tcs_pwa_shell_query_vars' );

/**
 * Expose session details for FastAPI via REST.
 *
 * This endpoint is the single source of truth for user identity and roles.
 */
function tcs_pwa_register_session_endpoint() {
	register_rest_route(
		'tcs/v1',
		'/session',
		array(
			'methods'             => 'GET',
			'callback'            => 'tcs_pwa_get_session',
			'permission_callback' => function () {
				return is_user_logged_in();
			},
		)
	);
}
add_action( 'rest_api_init', 'tcs_pwa_register_session_endpoint' );

/**
 * Return logged-in user identity and roles.
 *
 * @param WP_REST_Request $request Current request.
 * @return WP_REST_Response
 */
function tcs_pwa_get_session( WP_REST_Request $request ) {
	if ( ! is_user_logged_in() ) {
		return new WP_REST_Response( array( 'detail' => 'Not authenticated' ), 401 );
	}

	$user = wp_get_current_user();

	return new WP_REST_Response(
		array(
			'user_id'      => $user->ID,
			'user_login'   => $user->user_login,
			'display_name' => $user->display_name,
			'roles'        => $user->roles,
		),
		200
	);
}

/**
 * Render the shell or redirect unauthenticated users.
 *
 * The shell loads header/footer and embeds the React app in an iframe that
 * points to /app, keeping the experience same-origin.
 */
function tcs_pwa_shell_template_redirect() {
	if ( ! get_query_var( 'tcs_pwa_shell' ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		// Preserve the /pwa deep link after login.
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/pwa';
		$redirect_url = home_url( $request_uri );
		wp_safe_redirect( wp_login_url( $redirect_url ) );
		exit;
	}

	status_header( 200 );
	nocache_headers();

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/pwa';
	$path = strtok( $request_uri, '?' );
	$query = parse_url( $request_uri, PHP_URL_QUERY );
	$app_path = preg_replace( '#^/pwa#', '/app', $path, 1 );
	$app_url = $app_path . ( $query ? '?' . $query : '' );

	get_header();
	echo '<div class="tcs-pwa-container"><iframe class="tcs-pwa-frame" src="' . esc_url( $app_url ) . '" title="PWA App"></iframe></div>';
	get_footer();
	exit;
}
add_action( 'template_redirect', 'tcs_pwa_shell_template_redirect' );

/**
 * Flush rewrite rules on activation.
 */
function tcs_pwa_shell_activate() {
	tcs_pwa_shell_add_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'tcs_pwa_shell_activate' );
