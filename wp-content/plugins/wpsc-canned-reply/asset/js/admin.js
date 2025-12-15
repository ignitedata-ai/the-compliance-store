/**
 *
 * Save new canned reply from admin panel
 */
function wpsc_set_add_new_cr_admin() {

	var form     = jQuery( '.wpsc-frm-add-canned-reply' )[0];
	var dataform = new FormData( form );

	var title = dataform.get( 'title' ).trim();
	if ( ! title) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	var is_tinymce = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();
	var body       = is_tinymce ? tinyMCE.get( 'wpsc-cr-body' ).getContent().trim() : jQuery( '.wpsc_textarea' ).val().trim();

	if ( ! body) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}
	dataform.append( 'body', body );

	var category = dataform.getAll( 'category[]' );
	if (category.length == 0) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );
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
			wpsc_scroll_top();
			window.history.replaceState( {}, null, 'admin.php?page=wpsc-canned-reply' );
			window.location.reload();
		}
	);
}

/**
 * Edit canned reply from admin panel
 */
function wpsc_get_edit_cr_admin(id, nonce) {

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-canned-reply' );
	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_get_edit_cr_admin', id, _ajax_nonce: nonce };
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
 *
 * Update canned reply from admin panel
 */
function wpsc_set_edit_cr_admin() {

	var form     = jQuery( '.wpsc-frm-edit-canned-reply' )[0];
	var dataform = new FormData( form );

	var title = dataform.get( 'title' ).trim();
	if ( ! title) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	var is_tinymce = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();
	var body       = is_tinymce ? tinyMCE.get( 'wpsc-cr-body' ).getContent().trim() : jQuery( '.wpsc_textarea' ).val().trim();

	if ( ! body) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}
	dataform.append( 'body', body );

	var category = dataform.getAll( 'category[]' );
	if (category.length == 0) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	var visibility = dataform.get( 'visibility' ).trim();
	if ( ! visibility) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}

	jQuery( '.wpsc-setting-section-body' ).html( supportcandy.loader_html );
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
			window.history.replaceState( {}, null, 'admin.php?page=wpsc-canned-reply' );
			window.location.reload();
		}
	);
}

/**
 * Delete canned reply
 */
function wpsc_delete_cr_admin(id, nonce) {

	var flag = confirm( supportcandy.translations.confirm );
	if ( ! flag) {
		return;
	}

	var data = { action: 'wpsc_delete_cr_admin', id, _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {
			window.history.replaceState( {}, null, 'admin.php?page=wpsc-canned-reply' );
			window.location.reload();
		}
	);
}

/**
 * Get canned category settings
 */
function wpsc_get_cr_categories(is_humbargar = false) {

	if (is_humbargar) {
		wpsc_toggle_humbargar();
	}

	jQuery( '.wpsc-setting-nav, .wpsc-humbargar-menu-item' ).removeClass( 'active' );
	jQuery( '.wpsc-setting-nav.canned-reply-categories, .wpsc-humbargar-menu-item.canned-reply-categories' ).addClass( 'active' );
	jQuery( '.wpsc-humbargar-title' ).html( supportcandy.humbargar_titles.canned_reply_categories );

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-settings&section=canned-reply-categories' );
	jQuery( '.wpsc-setting-body' ).html( supportcandy.loader_html );

	wpsc_scroll_top();

	var data = { action: 'wpsc_get_cr_categories' };
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
 *  Set add new category
 */
function wpsc_set_add_cr_category(el) {

	var form     = jQuery( '.wpsc-frm-add-cr-category' )[0];
	var dataform = new FormData( form );

	if ( dataform.get( 'label' ).trim() == '' ) {
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
			wpsc_get_cr_categories();
		}
	);
}

/**
 * Get edit category modal
 */
function wpsc_get_edit_cr_category(id, nonce) {

	wpsc_show_modal();
	var data = { action: 'wpsc_get_edit_cr_category', id, _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {

			// Set to modal.
			jQuery( '.wpsc-modal-header' ).text( res.title );
			jQuery( '.wpsc-modal-body' ).html( res.body );
			jQuery( '.wpsc-modal-footer' ).html( res.footer );
			// Display modal.
			wpsc_show_modal_inner_container();
		}
	);
}

/**
 * Update category
 */
function wpsc_set_edit_cr_category(el) {
	
	var form     = jQuery( '.wpsc-frm-edit-cr-category' )[0];
	var dataform = new FormData( form );

	if ( dataform.get( 'label' ).trim() == '' ) {
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
			wpsc_get_cr_categories();
		}
	);
}

/**
 * Delete CR category modal
 */
function wpsc_get_delete_cr_category(id, nonce) {

	var flag = confirm( supportcandy.translations.confirm );
	if ( ! flag) {
		return;
	}

	wpsc_show_modal();
	var data = { action: 'wpsc_get_delete_cr_category', id, _ajax_nonce: nonce };
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
 * Delete CR category
 */
 function wpsc_set_delete_cr_category(el) {

	jQuery( '.wpsc-modal-footer button' ).attr( 'disabled', true );
	jQuery( el ).text( supportcandy.translations.please_wait );

	var form     = jQuery( '.wpsc-frm-delete-cr-category' )[0];
	var dataform = new FormData( form );
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
			wpsc_get_cr_categories();
		}
	);
}

/**
 * Get back to canned reply in admin
 */
function wpsc_get_cr_admin() {

	window.history.replaceState( {}, null, 'admin.php?page=wpsc-canned-reply' );
	window.location.reload();
}
