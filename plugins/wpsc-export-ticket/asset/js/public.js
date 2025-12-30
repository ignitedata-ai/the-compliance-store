/**
 * Export tickets
 */
function wpsc_get_export_tickets(nonce) {

	var data = { action: 'wpsc_get_export_tickets', _ajax_nonce: nonce};
	jQuery.post(
		supportcandy.ajax_url,
		data,
		function (res) {
			var obj = jQuery.parseJSON( res );
			var wpsc_export = document.createElement('a');
			wpsc_export.href = obj.url_to_export;
			wpsc_export.setAttribute('target', '_blank');
			wpsc_export.click();
			// window.open( obj.url_to_export,'_blank' );
			setTimeout(function() {
				wpsc_export.remove();
			}, 1000);
		}
	);
}
