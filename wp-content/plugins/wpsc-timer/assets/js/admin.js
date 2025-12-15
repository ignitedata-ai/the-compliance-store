/**
 *  Get timer settings
 */
function wpsc_get_timer_settings(is_humbargar = false) {
	if (is_humbargar) {
		wpsc_toggle_humbargar();
	}

	jQuery( '.wpsc-setting-nav, .wpsc-humbargar-menu-item' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-nav.timer, .wpsc-humbargar-menu-item.timer' ).addClass( 'active' );
	jQuery( '.wpsc-humbargar-title' ).html( supportcandy.humbargar_titles.timer );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=timer' );
	jQuery( '.wpsc-setting-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_get_timer_settings' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Set timer settings
 */
function wpsc_set_timer_setting(el) {

	var form     = jQuery( '.wpsc-timer-setting' )[0];
	var dataform = new FormData( form );
	jQuery( el ).text( supportcandy.translations.please_wait );
	jQuery.ajax(
		{
			url: supportcandy.ajax_url,
			type: 'POST',
			data: dataform,
			processData: false,
			contentType: false
		}
	).done(
		function (res) {
			wpsc_get_timer_settings();
		}
	);
}

/**
 * Reset timer settings
 */
function wpsc_reset_timer_setting(el, nonce) {

	jQuery( '.wpsc-modal-footer button' ).attr( 'disabled', true );
	jQuery( el ).text( supportcandy.translations.please_wait );
	var data = { action: 'wpsc_reset_timer_setting', _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_get_timer_settings();
		}
	);
}

/**
 * Get edit timer widget
 */
function wpsc_get_tw_timer() {

	wpsc_show_modal();
	var data = { action: 'wpsc_get_tw_timer' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {

			// Set to modal.
			jQuery( '.wpsc-modal-header' ).text( response.title );
			jQuery( '.wpsc-modal-body' ).html( response.body );
			jQuery( '.wpsc-modal-footer' ).html( response.footer );
			// Display modal.
			wpsc_show_modal_inner_container();
		}
	);
}

/**
 * Set edit timer widget
 */
function wpsc_set_tw_timer(el) {
	
	var form     = jQuery( '.wpsc-frm-edit-timer' )[0];
	var dataform = new FormData( form );
	
	if (dataform.get( 'label' ).trim() == '') {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	jQuery( '.wpsc-modal-footer button' ).attr( 'disabled', true );
	jQuery( el ).text( supportcandy.translations.please_wait );

	jQuery.ajax(
		{
			url: supportcandy.ajax_url,
			type: 'POST',
			data: dataform,
			processData: false,
			contentType: false
		}
	).done(
		function (res) {
			wpsc_close_modal();
			wpsc_get_ticket_widget();
		}
	);
}
