/**
 * Refresh timer widget
 */
function wpsc_it_refresh_timer(ticket_id) {

	jQuery( '.wpsc-it-tw-body' ).html( supportcandy.loader_html );

	var data = { action: 'wpsc_it_refresh_timer', ticket_id };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {

			jQuery( '.wpsc-it-tw-body' ).html( res );
		}
	);
}

/**
 * Start timer
 */
function wpsc_start_timer(ticket_id, _ajax_nonce) {

	jQuery( '.wpsc-it-tw-body' ).html( supportcandy.loader_html );

	var data = { action: 'wpsc_start_timer', ticket_id, _ajax_nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {

			jQuery( '.wpsc-it-tw-body' ).html( res );
		}
	);
}

/**
 * Pause timer
 */
function wpsc_pause_timer(ticket_id, _ajax_nonce) {

	jQuery( '.wpsc-it-tw-body' ).html( supportcandy.loader_html );

	var data = { action: 'wpsc_pause_timer', ticket_id, _ajax_nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {
			jQuery( '.wpsc-it-tw-body' ).html( res );
		}
	);
}

/**
 * Resume timer
 */
function wpsc_resume_timer(ticket_id, _ajax_nonce) {

	jQuery( '.wpsc-it-tw-body' ).html( supportcandy.loader_html );

	var data = { action: 'wpsc_resume_timer', ticket_id, _ajax_nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {
			jQuery( '.wpsc-it-tw-body' ).html( res );
		}
	);
}

/**
 * Stop timer
 */
function wpsc_stop_timer(ticket_id, agent_id, _ajax_nonce) {

	wpsc_show_modal();
	var data = { action: 'wpsc_get_stop_timer', ticket_id, agent_id, _ajax_nonce };
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
 * Stop timer and add log
 */
function wpsc_set_stop_timer(el) {

	var form     = jQuery( '.wpsc-frm-add-log' )[0];
	var dataform = new FormData( form );

	var time_spent      = dataform.get( 'time_spent' );
	var date_started    = dataform.get( 'date_started' );
	var ticket_id       = dataform.get( 'ticket_id' );

	if ( ! (time_spent && date_started)) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	var pattern = /^(\d+d)?\s*(\d+h)?\s*(\d+m)?$/;
	if (!pattern.test(time_spent)) {
		alert(supportcandy.translations.invalid_timer_format);
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
			wpsc_it_refresh_timer( ticket_id );
		}
	);
}

/**
 * Delete timer log
 */
function wpsc_delete_log(log_id, ticket_id, nonce) {

	if ( ! confirm( supportcandy.translations.confirm )) {
		return;
	}

	var data = { action: 'wpsc_delete_timer_log', ticket_id, log_id, _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {
			wpsc_close_modal();
			wpsc_it_refresh_timer( ticket_id );
		}
	);
}

/**
 * New timer log
 */
function wpsc_add_new_timer_log(ticket_id, nonce) {

	wpsc_show_modal();
	var data = { action: 'wpsc_add_new_timer_log', ticket_id, _ajax_nonce: nonce };
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
 * Export timer logs
 */
function wpsc_export_timer_logs(ticket_id, nonce) {

	var data = { action: 'wpsc_export_timer_logs',ticket_id, _ajax_nonce: nonce};
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {
			var obj = jQuery.parseJSON( res );
			window.open( obj.url_to_export,'_blank' );
		}
	);
}

/**
 * Set new timer log
 */
function wpsc_set_new_log_timer(el) {

	var form     = jQuery( '.wpsc-frm-add-new-log' )[0];
	var dataform = new FormData( form );

	var time_spent      = dataform.get( 'time_spent' );
	var date_started    = dataform.get( 'date_started' );
	var ticket_id       = dataform.get( 'ticket_id' );

	if ( ! (time_spent && date_started)) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	var pattern = /^(\d+d)?\s*(\d+h)?\s*(\d+m)?$/;
	if (!pattern.test(time_spent)) {
		alert(supportcandy.translations.invalid_timer_format);
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
			wpsc_it_refresh_timer( ticket_id );
		}
	);
}

/**
 * View timer log
 */
function wpsc_view_timer_logs(ticket_id, nonce) {

	wpsc_show_modal();
	var data = { action: 'wpsc_view_timer_logs', ticket_id, _ajax_nonce: nonce };
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
 * Get edit timer log
 */
function wpsc_get_edit_timer_log(ticket_id, log_id, nonce) {

	wpsc_show_modal();
	var data = { action: 'wpsc_get_edit_timer_log', ticket_id, log_id, _ajax_nonce: nonce };
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
 * Set edit timer log
 */
function wpsc_set_edit_timer_log(el) {

	var form     = jQuery( '.wpsc-frm-edit-log' )[0];
	var dataform = new FormData( form );

	var time_spent      = dataform.get( 'time_spent' );
	var date_started    = dataform.get( 'date_started' );
	var ticket_id       = dataform.get( 'ticket_id' );

	if ( ! (time_spent && date_started)) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	var pattern = /^(\d+d)?\s*(\d+h)?\s*(\d+m)?$/;
	if (!pattern.test(time_spent)) {
		alert(supportcandy.translations.invalid_timer_format);
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
			wpsc_it_refresh_timer( ticket_id );
		}
	);
}

/**
 * Get total time spent by customer on tickets
 */
function wpsc_get_total_time_spent(el, ticket_id) {

	wpsc_show_modal();
	var data = { action: 'wpsc_get_total_time_spent', ticket_id };
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
 * Reset timer
 */
function wpsc_reset_timer(ticket_id, nonce) {

	if ( ! confirm( supportcandy.translations.confirm )) {
		return;
	}

	var data = { action: 'wpsc_reset_timer', ticket_id, _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {
			wpsc_close_modal();
			wpsc_it_refresh_timer( ticket_id );
		}
	);
}
