/**
 * Get canned reply
 */
function wpsc_get_canned_reply(nonce) {

	wpsc_show_modal();
	var data = { action: 'wpsc_get_canned_reply', _ajax_nonce: nonce };

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
 * Delete canned reply
 */
function wpsc_delete_canned_reply(id, nonce) {

	var flag = confirm( supportcandy.translations.confirm );
	if ( ! flag) {
		return;
	}

	var data = { action: 'wpsc_delete_canned_reply', id, _ajax_nonce: nonce };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {
			wpsc_get_canned_reply();
		}
	);
}

/**
 * Add canned reply in reply and create ticket section
 *
 * @param {INT} id
 */
function wpsc_add_cr_text(id, nonce) {

	var data = { action: 'wpsc_add_cr_text', id, _ajax_nonce: nonce, is_editor: isWPSCEditor  };
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {
			var is_tinymce = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();
			if (is_tinymce) {
				tinymce.activeEditor.execCommand( 'mceInsertContent', false, res );
			} else {
				var txt              = jQuery( ".wpsc_textarea" );
				var caretPos         = txt[0].selectionStart;
				var textAreaTxt      = txt.val();
				const strippedString = res.replace( /(<([^>]+)>)/gi, "" );
				txt.val( textAreaTxt.substring( 0, caretPos ) + strippedString + textAreaTxt.substring( caretPos ) );
			}
			wpsc_close_modal();
		}
	);
}

/**
 * Add new canned reply from
 */
function wpsc_it_add_new_canned_reply(nonce) {

	var is_tinymce = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();
	var body       = is_tinymce ? tinyMCE.activeEditor.getContent().trim() : jQuery( '.wpsc_textarea' ).val().trim();

	if ( ! body) {
		return;
	}

	wpsc_show_modal();
	var data = { action: 'wpsc_it_add_new_canned_reply', _ajax_nonce: nonce };
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
 * Save new canned reply
 *
 * @returns
 */
function wpsc_it_set_new_canned_reply(el) {

	var form     = jQuery( '.wpsc-frm-add-it-cr' )[0];
	var dataform = new FormData( form );

	var title = dataform.get( 'title' ).trim();
	if ( ! title) {
		alert( supportcandy.translations.req_fields_missing );
		return;
	}
	var is_tinymce = (typeof tinyMCE != "undefined") && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden();
	var body       = is_tinymce ? tinyMCE.activeEditor.getContent().trim() : jQuery( '.wpsc_textarea' ).val().trim();

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

			ticket_id = jQuery('#wpsc-current-ticket').val();
			wpsc_clear_saved_draft_reply(ticket_id);
			wpsc_close_modal();
		}
	);
}