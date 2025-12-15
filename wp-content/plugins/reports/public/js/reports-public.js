(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	/**
	 * Record views for frontend documents
	 */

	 $(document).on("shown.bs.collapse", ".frontend-docs-panel", function (event) {

		var $panel = $(this).closest('.panel');
		var $open = $(this).closest('.panel-group').find('.panel-collapse.in');
    
		var additionalOffset = 200;
		if($panel.prevAll().filter($open.closest('.panel')).length !== 0)
		{
		  additionalOffset =  $open.height();
		}
		$('html,body').animate({
		  scrollTop: $panel.offset().top - additionalOffset
		}, 500);
    
		if ($(this).is(event.target)) {
		    var id = this.id.split('-')[1];
		}

		$.ajax({
		    url: frontend_ajax_object.ajaxurl,
		    type: 'post',
		    data: {
			action: 'view_document_log_handler',
			id: id,
		    },
		})
	});

	/**
	 * Record view of document when viewed from "view all documents" template
	 */

	$(document).on( 'click', '.frontend-document-view', function( event ) {

		var doc_id = $(this).attr('id');

		$.ajax({
			url: frontend_ajax_object.ajaxurl,
			type: 'post',
			data: {
			  action: 'view_document_log_handler',
			  id: doc_id,
			},
		});

	});


})( jQuery );