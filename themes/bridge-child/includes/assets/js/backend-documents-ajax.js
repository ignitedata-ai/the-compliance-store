(function ($) {
    'use strict';
    $(document).ready(function () {
        $('.post-type-documents.edit-php #posts-filter #doaction').click(function (event) {
            event.preventDefault();
            let selectedVal = $("#bulk-action-selector-top").val();
            if (selectedVal == 'delete') {
                let postArr = new Array();

                $("input:checkbox[name='post[]']:checked").each(function () {
                    postArr.push($(this).val());
                });
                Swal.fire({
                    title: 'Would you also like to delete the attachments for all document posts?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    showCloseButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    allowOutsideClick: false,
                    cancelButtonText: 'No, Just Delete The Posts!',
                }).then((result) => {
                    if (result.dismiss == 'close') {
                        return;
                    } else {
                        if (result.value) {
                            $.ajax({
                                type: 'post',
                                url: backend_doc_ajax_object.ajaxurl,
                                data: {
                                    action: 'delete_document_attachments',
                                    postArr: postArr,
                                },
                                success: function (response) {
                                    $('.post-type-documents.edit-php #posts-filter').submit();
                                }
                            });
                        } else {
                            $('.post-type-documents.edit-php #posts-filter').submit();
                        }
                    }
                });
            } else if (selectedVal == 'trash' || selectedVal == 'untrash') {
                $('.post-type-documents.edit-php #posts-filter').submit();
            }
        });

        $('.post-type-documents.edit-php span.delete a.submitdelete').click(function (event) {

            event.preventDefault();

            var addressValue = $(this).attr("href");

            Swal.fire({
                title: 'Would you also like to delete the attachments for this document post?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                showCloseButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                allowOutsideClick: false,
                cancelButtonText: 'No, Just Delete The Post!',
            }).then((result) => {
                if (result.dismiss == 'close') {
                    return;
                } else {
                    if (result.value) {
                        $.ajax({
                            type: 'post',
                            url: backend_doc_ajax_object.ajaxurl,
                            data: {
                                action: 'delete_document_attachments',
                                postdelURL: addressValue,
                            },
                            success: function (response) {
                                console.log(response);
                                window.location.replace(addressValue);
                            }
                        });
                    } else {
                        window.location.replace(addressValue);
                    }
                }
            });
        });

        /**
         * Delete single frontend document from backend (permanent delete)
         */

        $('.post-type-frontend_documents.edit-php span.delete a.submitdelete').click(function (event) {

            event.preventDefault();

            var addressValue = $(this).attr("href");

            Swal.fire({
                title: 'Are you sure you want to delete this document?',
                icon: 'warning',
                showCancelButton: true,
                showCloseButton: true,
                confirmButtonColor: '#009eda',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                allowOutsideClick: false,
                cancelButtonText: 'No, Just Delete The Post!',
            }).then((result) => {
                if (result.dismiss == 'close') {
                    return;
                } else {
                    if (result.value) {
                        $.ajax({
                            type: 'post',
                            url: backend_doc_ajax_object.ajaxurl,
                            data: {
                                action: 'delete_frontend_document_attachments',
                                postdelURL: addressValue,
                            },
                            success: function (response) {
                                window.location.replace(addressValue);
                            }
                        });
                    } else {
                        window.location.replace(addressValue);
                    }
                }
            });
        });

        /**
         * Bulk remove items from trash (frontend documents) from admin panel
         */

         $('.post-type-frontend_documents.edit-php #posts-filter #doaction').click(function (event) {

            event.preventDefault();

            let selectedVal = $("#bulk-action-selector-top").val();
            if (selectedVal == 'delete') {
                
                let postArr = new Array();

                $("input:checkbox[name='post[]']:checked").each(function () {
                    postArr.push($(this).val());
                });
                Swal.fire({
                    title: 'Would you also like to delete the attachments for all document posts?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    showCloseButton: true,
                    confirmButtonColor: '#009eda',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    allowOutsideClick: false,
                    cancelButtonText: 'No, Just Delete The Posts!',
                }).then((result) => {
                    if (result.dismiss == 'close') {
                        return;
                    } else {
                        if (result.value) {
                            $.ajax({
                                type: 'post',
                                url: backend_doc_ajax_object.ajaxurl,
                                data: {
                                    action: 'delete_frontend_document_attachments',
                                    postArr: postArr,
                                },
                                success: function (response) {
                                    $('.post-type-frontend_documents.edit-php #posts-filter').submit();
                                }
                            });
                        } else {
                            $('.post-type-frontend_documents.edit-php #posts-filter').submit();
                        }
                    }
                });
            } else if (selectedVal == 'trash' || selectedVal == 'untrash') {
                $('.post-type-frontend_documents.edit-php #posts-filter').submit();
            }
        });

    });
})(jQuery);