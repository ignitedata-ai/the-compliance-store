(function( $ ) {
	'use strict';
    $( window ).load(function() {
        jQuery('#createuser #work_phone').inputmask("(999) 999-9999");
        jQuery('#your-profile #work_phone').inputmask("(999) 999-9999");
    });
    
    jQuery(document).ready(function () {

        jQuery('#create_date').datepicker( {   
            dateFormat: 'dd/mm/yy',
            showOn: 'both',
            constrainInput: true
        });
        
        jQuery('#start_date').datepicker( {   
            dateFormat: 'dd/mm/yy',
            showOn: 'both',
            constrainInput: true
        });
        
        jQuery('#end_date').datepicker( {   
            dateFormat: 'dd/mm/yy',
            showOn: 'both',
            constrainInput: true
        });

        // authors filter for media library (backend)

        $('.documents-media-filter').select2({
            placeholder: 'Select author'
        });

    });
    
    jQuery( '.cp_tooltip' ).each( function() {
            jQuery( this ).tipTip();
    });
    jQuery(document).ready(function () {

        var allAuthors = '';

        if($('body').hasClass('upload-php')) {
    
            // ajax request to get all authors for media library
    
            $.ajax({
                url: backend_ajax_object.ajaxurl,
                type: 'post',
                data: {
                  action: 'get_all_users'
                },
                success: function (response) {
                    
                    allAuthors = response;
    
                }
            });

        }
        
        $('form#createuser').on('submit', function (e) {

            var user_status_1 = $('#createuser #user_status_1').is(':checked');
            var user_status_2 = $('#createuser #user_status_2').is(':checked');
            if (user_status_1 == false && user_status_2 == false) {
                $("#createuser .user_status_field").addClass("form-invalid");
                e.preventDefault();
            } else {
                $("#createuser .user_status_field").removeClass("form-invalid");
            }

            var t_and_t_1 = $('#createuser #tools_templates_access_1').is(':checked');
            var t_and_t_2 = $('#createuser #tools_templates_access_2').is(':checked');
            if (t_and_t_1 == false && t_and_t_2 == false) {
                $("#createuser .tools_templates_field").addClass("form-invalid");
                e.preventDefault();
            } else {
                $("#createuser .tools_templates_field").removeClass("form-invalid");
            }
            
            /**
             * Putting check condition on P&P of create user
             */
            
            var p_and_p_1 = $('#createuser #policies_procedures_access_1').is(':checked');
            var p_and_p_2 = $('#createuser #policies_procedures_access_2').is(':checked');
            if (p_and_p_1 == false && p_and_p_2 == false) {
                $("#createuser .policies_procedures_field").addClass("form-invalid");
                e.preventDefault();
            } else {
                $("#createuser .policies_procedures_field").removeClass("form-invalid");
            }
            
            if ($('#createuser #account_manager').val() == '') {
                $("#createuser .field-account").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#createuser .field-account").removeClass("form-invalid");
            } 
            
            
            
            /*
            if ($('#createuser #title').val() == '') {
                $("#createuser #title").closest("tr.form-field").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#createuser #title").closest("tr.form-field").removeClass("form-invalid");
            } */

            if ($('#createuser #state').val() == '') {
                $("#createuser #state").closest("tr.form-field").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#createuser #state").closest("tr.form-field").removeClass("form-invalid");
            }

        });

        $('form#your-profile').on('submit', function (e) {

            var user_status_1 = $('#your-profile #user_status_1').is(':checked');
            var user_status_2 = $('#your-profile #user_status_2').is(':checked');
            if (user_status_1 == false && user_status_2 == false) {
                $("#your-profile .user_status_field").addClass("form-invalid");
                e.preventDefault();
            } else {
                $("#your-profile .user_status_field").removeClass("form-invalid");
            }

            var t_and_t_1 = $('#your-profile #tools_templates_access_1').is(':checked');
            var t_and_t_2 = $('#your-profile #tools_templates_access_2').is(':checked');
            if (t_and_t_1 == false && t_and_t_2 == false) {
                $("#your-profile .tools_templates_field").addClass("form-invalid");
                e.preventDefault();
            } else {
                $("#your-profile .tools_templates_field").removeClass("form-invalid");
            }

            if ($('#your-profile #first_name').val() == '') {
                $("#your-profile #first_name").closest("tr.user-first-name-wrap ").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#your-profile #first_name").closest("tr.user-first-name-wrap ").removeClass("form-invalid");
            }

            if ($('#your-profile #last_name').val() == '') {
                $("#your-profile #last_name").closest("tr.user-last-name-wrap ").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#your-profile #last_name").closest("tr.user-last-name-wrap ").removeClass("form-invalid");
            }

            if ($('#your-profile #nickname').val() == '') {
                $("#your-profile #nickname").closest("tr.user-nickname-wrap ").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#your-profile #nickname").closest("tr.user-nickname-wrap ").removeClass("form-invalid");
            }

            if ($('#your-profile #account_manager').val() == '') {
                $("#your-profile .field-account").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#your-profile .field-account").removeClass("form-invalid");
            }
            /*if ($('#your-profile #title').val() == '') {
                $("#your-profile #title").closest("tr.form-field").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#your-profile #title").closest("tr.form-field").removeClass("form-invalid");
            }*/

            if ($('#your-profile #state').val() == '') {
                $("#your-profile #state").closest("tr.form-field").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#your-profile #state").closest("tr.form-field").removeClass("form-invalid");
            }
            if ($('#your-profile #street_1').val() == '') {
                $("#your-profile #street_1").closest("tr.form-field").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#your-profile #street_1").closest("tr.form-field").removeClass("form-invalid");
            }
            if ($('#your-profile #city').val() == '') {
                $("#your-profile #city").closest("tr.form-field").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#your-profile #city").closest("tr.form-field").removeClass("form-invalid");
            }
            if ($('#your-profile #zip_code').val() == '') {
                $("#your-profile #zip_code").closest("tr.form-field").addClass("form-invalid");
                 e.preventDefault();
            } else {
                $("#your-profile #zip_code").closest("tr.form-field").removeClass("form-invalid");
            }

        });

               
        $('#createuser .btn-remove-trusted-refer').click(function() {
            $('#createuser #trusted_refer').attr('value', '');
            $(this).hide();
        });
		$("#createuser .btn-generate-trusted-refer").click(function (e) {
			e.stopImmediatePropagation();
            $.ajax({
                url: backend_ajax_object.ajaxurl,
                type: 'post',
                data: {
                	action:'generate_trusted_refer'
                },
            })
            .done(function(status) {
                $('#createuser #trusted_refer').val(status);
                $('#createuser .btn-remove-trusted-refer').show();
            })
        });       
        $('#your-profile .btn-remove-trusted-refer').click(function() {
            $('#your-profile #trusted_refer').attr('value', '');
            $(this).hide();
        });
        $("#your-profile .btn-generate-trusted-refer").click(function (e) {
            e.stopImmediatePropagation();
            $.ajax({
                url: backend_ajax_object.ajaxurl,
                type: 'post',
                data: {
                    action:'generate_trusted_refer'
                },
            })
            .done(function(status) {
                $('#your-profile #trusted_refer').val(status);
                $('#your-profile .btn-remove-trusted-refer').show();
            })
        });


        $('body').on('click', '#doaction', function (event) {

            let docIdsHTML = '';
            const activeAction = $('#bulk-action-selector-top').val();
            
            if( activeAction == 'edit' ) {
                
                event.preventDefault();

                // authors dropdown for media library (backend bulk edit)

                // $('.media-authors-dropdown').select2({
                //     placeholder: 'Select author'
                // });

                // document ID

                $('tbody th.check-column input[type="checkbox"]:checked').each(function(i) {
                    let documentID = $(this).val();
                    let rawDocumentTitle = $(this).parent().parent().find('td.title a').attr('aria-label');
                    let documentTitle = rawDocumentTitle.substring(rawDocumentTitle.indexOf('“'), rawDocumentTitle.indexOf('”')).replace("“", "");
                    docIdsHTML += '<div data-label="' + documentTitle + '" id="' + documentID + '"><a id="' + documentID + '" class="removeBulkItem ntdelbutton" title="Remove From Bulk Edit">X</a>' + documentTitle + '</div>';
                });

                // if bulk edit box already added inside the HTML

                if($('tbody').find('tr#bulk-edit').length != 0) {

                    $('#bulk-titles').html(docIdsHTML); // only embed selected ids

                } else {

                    // append HTML first time

                    let dataAppend = '<fieldset class="inline-edit-col-left ">' +
                        '<legend class="inline-edit-legend">Bulk Edit</legend>' +
                        '<div class="inline-edit-col">' +
                            '<div id="bulk-title-div">' +
                                '<div id="bulk-titles">' + docIdsHTML + '</div>' +
                            '</div>' +
                        '</div>' +
                    '</fieldset>' + 
                    '<fieldset class="inline-edit-col-right">' +
                        '<div class="inline-edit-col">' +
                            '<div class="field-wrap full-width">' +
                                '<label class="alignleft">' +
                                    '<span class="title">Author</span>' +
                                    allAuthors
                                     +
                                '</label>' +
                            '</div>' +
                        '</div>' +
                    '</fieldset>' +
                    '<div class="submit inline-edit-save">' +
                        '<button type="button" class="button cancel cancel-bulk-media-edit alignleft">Cancel</button>' +
                        '<input type="submit" name="bulk_edit" id="bulk_edit" class="button button-primary bulk-media-update alignright" value="Update">' +
                        '<br class="clear">' +
                    '</div>' +
                    '<div class="media-loader-wrapper">' +
                        '<img src="' + backend_ajax_object.child_theme_path + '/includes/assets/images/ajax-loader.gif">' +
                    '</div>';

                    $('.table-view-list tbody').prepend('<tr id="bulk-edit" class="inline-edit-row inline-edit-row-page bulk-edit-row bulk-edit-row-page bulk-edit-attachment inline-editor"><td colspan="10" class="colspanchange bulk-edit-media">' + dataAppend + '</td></tr>');
                
                }

            }

        });

        /**
         * Bulk item removed from selection
         */

        $('body').on('click', '.removeBulkItem', function (event) {

            event.preventDefault();

            $(this).parent().remove();

        });

        /**
         * Bulk item removed from selection
         */

        $('body').on('click', '.cancel-bulk-media-edit', function (event) {

            event.preventDefault();

            $('.bulk-edit-attachment').remove();

        });

        /**
         * Bulk media update
         */

        $('body').on('click', '.bulk-media-update', function (event) {

            event.preventDefault();

            var docsIds = [];
            var selectedAuthorID = $('.media-authors-dropdown').val();

            $('#bulk-titles').children().each(function(){
                docsIds.push($(this).attr('id'));
            });

            if(docsIds) {

                $.ajax({
                    url: backend_ajax_object.ajaxurl,
                    type: 'post',
                    data: {
                      action: 'bulk_update_media_attachments',
                      ids: docsIds,
                      authorID: selectedAuthorID
                    },
                    beforeSend: function() {
                        $(".media-loader-wrapper").show();
                    },
                    success: function (response) {
                        $(".media-loader-wrapper").hide();
                        location.reload();
                    }
                });

            }

        });

	});       
})( jQuery );