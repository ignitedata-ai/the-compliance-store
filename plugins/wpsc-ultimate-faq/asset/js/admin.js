/**
 * Get faq Settings
 */
function wpsc_get_faq_settings(is_humbargar = false) {

	if (is_humbargar) {
		wpsc_toggle_humbargar( is_humbargar = false );
	}

	jQuery( '.wpsc-setting-nav, .wpsc-humbargar-menu-item' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-nav.faq, .wpsc-humbargar-menu-item.faq' ).addClass( 'active' );
	jQuery( '.wpsc-humbargar-title' ).html( supportcandy.humbargar_titles.faq_settings );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=faq' );
	jQuery( '.wpsc-setting-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_get_faq_settings' };
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
 * Save faq settings
 */
function wpsc_set_faq_settings(el) {

	var form     = jQuery( '.wpsc-frm-faq-settings' )[0];
	var dataform = new FormData( form );
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
			wpsc_get_faq_settings();
		}
	);
}

/**
 * Reset faq settings
 */
function wpsc_reset_faq_settings(el, nonce) {

	jQuery( '.wpsc-modal-footer button' ).attr( 'disabled', true );
	jQuery( el ).text( supportcandy.translations.please_wait );
	var data = { action: 'wpsc_reset_faq_settings', _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (response) {
			wpsc_get_faq_settings();
		}
	);
}
