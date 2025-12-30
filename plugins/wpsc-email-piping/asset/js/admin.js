/**
 * Load email piping section
 */
function wpsc_get_ep_settings(is_humbargar = false) {

	if (is_humbargar) {
		wpsc_toggle_humbargar();
	}

	jQuery( '.wpsc-setting-nav, .wpsc-humbargar-menu-item' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-nav.email-piping, .wpsc-humbargar-menu-item.email-piping' ).addClass( 'active' );
	jQuery( '.wpsc-humbargar-title' ).html( supportcandy.humbargar_titles.email_piping );

	if (supportcandy.current_section !== 'email-piping') {
		supportcandy.current_section = 'email-piping';
		supportcandy.current_tab     = 'general';
	}

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = {
		action: 'wpsc_get_ep_settings',
		tab: supportcandy.current_tab
	};
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-body' ).html( response );
			wpsc_reset_responsive_style();
			jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).trigger( "click" );
		}
	);
}

/**
 * Load general tab ui
 */
function wpsc_ep_get_general_settings() {

	supportcandy.current_tab = 'general';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_ep_get_general_settings' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Set general settings
 */
function wpsc_ep_set_general_settings(el) {

	var form     = jQuery( '.wpsc-ep-general-settings' )[0];
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
			wpsc_ep_get_general_settings();
		}
	);
}

/**
 * Set general settings
 */
function wpsc_ep_reset_general_settings(el, nonce) {

	jQuery( el ).text( supportcandy.translations.please_wait );
	var data = { action: 'wpsc_ep_reset_general_settings', _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_ep_get_general_settings();
		}
	);
}

/**
 * Load pipe rules ui
 */
function wpsc_ep_get_pipe_rules() {

	supportcandy.current_tab = 'pipe-rules';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_ep_get_pipe_rules' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Add new piping rule
 */
function wpsc_ep_get_add_pipe_rule(nonce) {

	wpsc_show_modal();
	var data = { action: 'wpsc_ep_get_add_pipe_rule', _ajax_nonce: nonce };
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
 * Set add new rule
 */
function wpsc_ep_set_add_pipe_rule(el) {

	var form     = jQuery( '.wpsc-ep-add-rule' )[0];
	var dataform = new FormData( form );

	var title = dataform.get( 'title' );
	if ( ! title ) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

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
			wpsc_ep_get_edit_pipe_rule( res.index, res.nonce );
		}
	);
}

/**
 * Get edit pipe rule
 */
function wpsc_ep_get_edit_pipe_rule(id, nonce) {

	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );
	var data = { action: 'wpsc_ep_get_edit_pipe_rule', id, _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
		}
	);
}

/**
 * Set edit pipe rule
 */
function wpsc_ep_set_edit_pipe_rule(el) {

	var form     = jQuery( '.wpsc-ep-edit-rule' )[0];
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
			wpsc_ep_get_pipe_rules();
		}
	);
}

/**
 * Delete email pipe rule
 */
function wpsc_ep_delete_pipe_rule(id, nonce) {

	var flag = confirm( supportcandy.translations.confirm );
	if ( ! flag) {
		return;
	}

	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );
	var data = { action: 'wpsc_ep_delete_pipe_rule', id, _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_ep_get_pipe_rules();
		}
	);
}

/**
 * Load imap settings ui
 */
function wpsc_ep_get_imap_settings() {

	supportcandy.current_tab = 'imap';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_ep_get_imap_settings' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Set imap settings
 */
function wpsc_ep_set_imap_settings(el) {

	var form     = jQuery( '.wpsc-ep-imap-settings' )[0];
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
			wpsc_ep_get_imap_settings();
		}
	);
}

/**
 * Reset imap settings
 */
function wpsc_ep_reset_imap_settings(el, nonce) {

	var data = { action: 'wpsc_ep_reset_imap_settings', _ajax_nonce: nonce };
	jQuery( el ).text( supportcandy.translations.please_wait );
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_ep_get_imap_settings();
		}
	);
}

/**
 * Load gmail settings ui
 */
function wpsc_ep_get_gmail_settings() {

	supportcandy.current_tab = 'gmail';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_ep_get_gmail_settings' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Set gmail settings
 */
function wpsc_ep_set_gmail_settings(el) {

	var form     = jQuery( '.wpsc-ep-gmail-settings' )[0];
	var dataform = new FormData( form );
	var btnText  = jQuery( el ).text();
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

			window.location.href = res.redirectURL;

		}
	).fail(
		function (res) {

			alert( res.responseJSON.data );
			jQuery( el ).text( btnText );
		}
	);
}

/**
 * Reset gmail settings
 * @param {*} el 
 * @param {*} nonce 
 */
function wpsc_ep_reset_gmail_settings(el, nonce) {

	var data = { action: 'wpsc_ep_reset_gmail_settings', _ajax_nonce: nonce };
	jQuery( el ).text( supportcandy.translations.please_wait );
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_ep_get_gmail_settings();
		}
	);
}

/**
 * Load microsoft exchange settings ui
 */
 function wpsc_ep_get_me_settings() {

	supportcandy.current_tab = 'microsoft-exchange';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_ep_get_me_settings' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Set microsoft exchange settings
 */
function wpsc_ep_set_me_settings(el) {

	var form     = jQuery( '.wpsc-ep-me-settings' )[0];
	var dataform = new FormData( form );
	var btnText  = jQuery( el ).text();
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
			window.location.href = res.redirectURL;
		}
	).fail(
		function (res) {
			alert( res.responseJSON.data );
			jQuery( el ).text( btnText );
		}
	);
}

/**
 * Reset microsoft exchange settings
 * @param {*} el 
 * @param {*} nonce 
 */
function wpsc_ep_reset_me_settings(el, nonce) {

	var data = { action: 'wpsc_ep_reset_me_settings', _ajax_nonce: nonce };
	jQuery( el ).text( supportcandy.translations.please_wait );
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_ep_get_me_settings();
		}
	);
}

/**
 * Load email logs settings ui
 */
function wpsc_ep_get_email_logs() {

	supportcandy.current_tab = 'email-logs';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_ep_get_email_logs' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Get view email logs
 */
function wpsc_ep_view_email_logs(log_id, nonce) {
  wpsc_show_modal();
  var data = {
    action: "wpsc_ep_view_email_logs",
    log_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (response) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(response.title);
    jQuery(".wpsc-modal-body").html(response.body);
    jQuery(".wpsc-modal-footer").html(response.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Allow usertype warning and closed ticket warning
 */
function wpsc_ep_warning_settings(is_humbargar = false) {

	if (is_humbargar) {
		wpsc_toggle_humbargar();
	}

	jQuery( '.wpsc-setting-nav, .wpsc-humbargar-menu-item' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-nav.email-warning, .wpsc-humbargar-menu-item.email-warning' ).addClass( 'active' );
	jQuery( '.wpsc-humbargar-title' ).html( supportcandy.humbargar_titles.email_warning );

	if (supportcandy.current_section !== 'email-warning') {
		supportcandy.current_section = 'email-warning'
		supportcandy.current_tab     = 'allow-user-type';
	}

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-email-notifications&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();
	var data = {
		action: 'wpsc_ep_warning_settings',
		tab: supportcandy.current_tab
	};

	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-body' ).html( response );
			wpsc_reset_responsive_style();
			jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).trigger( "click" );
		}
	);

}

/**
 * Load allow user type warning
 */
function wpsc_allowed_usertype_warning() {

	supportcandy.current_tab = 'allow-user-type';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-email-notifications&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_allowed_usertype_warning' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Set user type email warning
 */
function wpsc_set_usertype_email_warning(el) {

	var form        = jQuery( '.wpsc-ep-warning-email' )[0];
	var dataform    = new FormData( form );
	var is_tinymce  = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();
	var description = is_tinymce ? tinyMCE.activeEditor.getContent().trim() : jQuery( '#email-warning-message' ).val();
	if ( ! description ) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	dataform.append( 'email-warning-message', description );

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
			wpsc_allowed_usertype_warning();
		}
	);
}

/**
 * Reset user type email warning
 */
function wpsc_reset_ep_warning(el, nonce) {

	jQuery( '.wpsc-modal-footer button' ).attr( 'disabled', true );
	jQuery( el ).text( supportcandy.translations.please_wait );
	var data = { action: 'wpsc_reset_ep_warning', _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_allowed_usertype_warning();
		}
	);
}

/**
 * Load closed ticket warning
 */
function wpsc_closed_ticket_warning() {

	supportcandy.current_tab = 'close-ticket';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-email-notifications&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_closed_ticket_warning' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Set close ticket email warning
 */
function wpsc_set_close_ticket_warning(el) {

	var form        = jQuery( '.wpsc-ep-close-ticket-warning-email' )[0];
	var dataform    = new FormData( form );
	var is_tinymce  = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();
	var description = is_tinymce ? tinyMCE.activeEditor.getContent().trim() : jQuery( '#close-ticket-html' ).val();
	if ( ! description ) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}
	dataform.append( 'close-ticket-html', description );

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
			wpsc_closed_ticket_warning();
		}
	);
}

/**
 * Reset close ticket email warning
 */
function wpsc_reset_close_ticket_warning(el, nonce) {

	jQuery( '.wpsc-modal-footer button' ).attr( 'disabled', true );
	jQuery( el ).text( supportcandy.translations.please_wait );
	var data = { action: 'wpsc_reset_close_ticket_warning', _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_closed_ticket_warning();
		}
	);
}

/**
 * Load new email warning
 */
function wpsc_allowed_new_email_warning() {

	supportcandy.current_tab = 'allow-new-email-warning';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-email-notifications&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_allowed_new_email_warning' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Set new email warning
 */
function wpsc_set_new_email_warning(el) {

	var form        = jQuery( '.wpsc-ep-newemail-warning' )[0];
	var dataform    = new FormData( form );
	var is_tinymce  = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();
	var description = is_tinymce ? tinyMCE.activeEditor.getContent().trim() : jQuery( '#new-email-warning-message' ).val();
	if ( ! description ) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}
	dataform.append( 'new-email-warning-message', description );

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
			wpsc_allowed_new_email_warning();
		}
	);
}

/**
 * Reset new email warning
 */
function wpsc_reset_new_email_warning(el, nonce) {

	jQuery( '.wpsc-modal-footer button' ).attr( 'disabled', true );
	jQuery( el ).text( supportcandy.translations.please_wait );
	var data = { action: 'wpsc_reset_new_email_warning', _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_allowed_new_email_warning();
		}
	);
}

/**
 * Load new email warning
 */
function wpsc_allowed_reply_email_warning() {

	supportcandy.current_tab = 'allow-reply-email-warning';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-email-notifications&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_allowed_reply_email_warning' };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
			wpsc_reset_responsive_style();
		}
	);
}

/**
 * Set new email warning
 */
function wpsc_set_reply_email_warning(el) {

	var form        = jQuery( '.wpsc-ep-replyemail-warning' )[0];
	var dataform    = new FormData( form );
	var is_tinymce  = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();
	var description = is_tinymce ? tinyMCE.activeEditor.getContent().trim() : jQuery( '#reply-email-warning-message' ).val();
	if ( ! description ) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}
	dataform.append( 'reply-email-warning-message', description );

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
			wpsc_allowed_reply_email_warning();
		}
	);
}

/**
 * Reset new email warning
 */
function wpsc_reset_reply_email_warning(el, nonce) {

	jQuery( '.wpsc-modal-footer button' ).attr( 'disabled', true );
	jQuery( el ).text( supportcandy.translations.please_wait );
	var data = { action: 'wpsc_reset_reply_email_warning', _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_allowed_reply_email_warning();
		}
	);
}

/**
 * Dismiss imap connection error notice
 */
function wpsc_dismiss_imap_connection_error_notice(nonce) {

	jQuery( '.supportcandy.imap.connection-error.notice' ).remove();
	var data = { action: 'wpsc_dismiss_imap_connection_error_notice', _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data
	);
}
