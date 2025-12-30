/**
 * Get AAR settings
 */
function wpsc_get_aar_settings(is_humbargar = false) {

	if (is_humbargar) {
		wpsc_toggle_humbargar();
	}

	jQuery( '.wpsc-setting-nav, .wpsc-humbargar-menu-item' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-nav.aar, .wpsc-humbargar-menu-item.aar' ).addClass( 'active' );
	jQuery( '.wpsc-humbargar-title' ).html( supportcandy.humbargar_titles.aar );

	if (supportcandy.current_section !== 'aar') {
		supportcandy.current_section = 'aar';
		supportcandy.current_tab     = 'general';
	}

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = {
		action: 'wpsc_get_aar_settings',
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
 * Get AAR general settings
 */
function wpsc_aar_get_general_settings() {

	supportcandy.current_tab = 'general';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_aar_get_general_settings' };
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
 * Set AAR general settings
 */
function wpsc_aar_set_general_settings(el) {

	var form     = jQuery( '.wpsc-aar-general-settings' )[0];
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
			wpsc_aar_get_general_settings();
		}
	);
}

/**
 * Reset AAR general settings
 */
function wpsc_aar_reset_general_settings(el, nonce) {

	jQuery( el ).text( supportcandy.translations.please_wait );
	var data = { action: 'wpsc_aar_reset_general_settings', _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_aar_get_general_settings();
		}
	);
}

/**
 * Get AAR rules
 */
function wpsc_aar_get_rules() {

	supportcandy.current_tab = 'aar-rules';
	jQuery( '.wpsc-setting-tab-container button' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-tab-container button.' + supportcandy.current_tab ).addClass( 'active' );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=' + supportcandy.current_section + '&tab=' + supportcandy.current_tab );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_aar_get_rules' };
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
 * Set add new rule
 */
function wpsc_aar_set_add_rule(el) {

	var form     = jQuery( '.wpsc-aar-add-rule' )[0];
	var dataform = new FormData( form );

	if (dataform.get( 'title' ).trim() == '') {
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
			wpsc_aar_get_edit_rule( res.index, res.nonce );
		}
	);
}

/**
 * Get edit aar rule
 */
function wpsc_aar_get_edit_rule(id, nonce) {

	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );
	var data = { action: 'wpsc_aar_get_edit_rule', id, _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			jQuery( '.wpsc-setting-section-body' ).html( response );
		}
	);
}

/**
 * Set edit rule
 */
function wpsc_aar_set_edit_rule(el) {

	var form     = jQuery( '.wpsc-aar-edit-rule' )[0];
	var dataform = new FormData( form );

	if (dataform.get( 'title' ).trim() == '') {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	var filters = wpsc_get_condition_json( 'conditions' );
	if ( filters.length === 0 || ( filters.length === 1 && filters[0].length === 0 )  ) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	dataform.append( 'conditions', JSON.stringify( filters ) );

	var agents      = dataform.getAll( 'agents[]' );
	var agentgroups = dataform.getAll( 'agentgroups[]' );
	if ( ! (agents.length || agentgroups.length)) {
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
			wpsc_aar_get_rules();
		}
	);
}

/**
 * Delete rule
 */
function wpsc_aar_delete_rule(id, nonce) {

	var flag = confirm( supportcandy.translations.confirm );
	if ( ! flag) {
		return;
	}

	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );
	var data = { action: 'wpsc_aar_delete_rule', id, _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_aar_get_rules();
		}
	);
}
