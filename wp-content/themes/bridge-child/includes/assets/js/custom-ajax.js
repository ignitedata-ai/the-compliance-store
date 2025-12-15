/**
 * Stop empty searches
 *
 * @author Thomas Scholz http://toscho.de
 * @param  $ jQuery object
 * @return bool|object
 */

 (function( $ ) {

  if($('div').hasClass('view-all-frontends-documents')) {

   $.fn.preventEmptySubmit = function( options ) {
       var settings = {
           inputselector: "#s",
           msg          : "Empty Search won’t work!"
       };

       if ( options ) {
           $.extend( settings, options );
       };

       this.submit( function() {
           var s = $( this ).find( settings.inputselector );
           if ( ! s.val() ) {
               alert( settings.msg );
               s.focus();
               return false;
           }
           return true;
       });
       return this;
   };

   $.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
        var dropdown_value = $('.category-dropdown-filter').val();
        var column_value = parseFloat( data[2] ) || 0; // use data for the categories column

        if ( dropdown_value == column_value  )
        {
            return true;
        }
        return false;
    }
  );

  }
   
    $(document).ready(function() {
        $('.content_inner').on('click', 'a.tcs-sort-cat', function(e) {
            event.preventDefault();
            var termid = $(this).data("id");
            var termorder = $(this).data("order");
            var termorderby = $(this).parents(".tcs-sort-container").find("select#orderby").val();

            var taxonomySlug = $('.taxonomy-header').attr("tax-slug");
            var cptName = $('.taxonomy-header').attr("cpt-name");

            if (termorder.toLowerCase() == 'desc') {
                termorder = 'asc';
            } else {
                termorder = 'desc';
            }
            $.ajax({
                data: {
                    action: 'get_ajax_documents',
                    termid: termid,
                    order: termorder,
                    orderby: termorderby,
                    cptName: cptName,
                    taxonomySlug: taxonomySlug,
                },
                type: 'post',
                url: frontend_ajax_object.ajaxurl,
                beforeSend: function() {
                    $("#loading-image").show();
                    $("#ajax-posts").hide();
                    $("a.list-group-item").css("pointer-events", "none");
                },
                success: function(data) {
                    $("#loading-image").hide();
                    $("a.list-group-item").css("pointer-events", "inherit");
                    $("#ajax-posts").show();
                    $('.ajax-docs').html(data);
                    $('.pdfemb-viewer').pdfEmbedder();
                    setTimeout(function() {
                        $('.panel-collapse').on('show.bs.collapse', function() {
                            $(this).siblings('.panel-heading').addClass('active');
                        });
                        $('.panel-collapse').on('hide.bs.collapse', function() {
                            $(this).siblings('.panel-heading').removeClass('active');
                        });
                    }, 1000);
                }
            });
        });
    
    $("a.list-group-item.i-am-child").click(function () {
      event.preventDefault();
      var additionalOffset = 400;
          
      $('html,body').animate({
        scrollTop: $("#dococument-parent").offset().top - additionalOffset
      }, 500);
      var termid = $(this).attr("id");

      var taxonomySlug = $('.taxonomy-header').attr("tax-slug");
      var cptName = $('.taxonomy-header').attr("cpt-name");

      $.ajax({
        data: {
          action: 'get_ajax_documents',
          termid: termid,
          cptName: cptName,
          taxonomySlug: taxonomySlug,
        },
        type: 'post',
        url: frontend_ajax_object.ajaxurl,
        beforeSend: function() {
          $("#loading-image").show();
          $("#ajax-posts").hide();
          $("a.list-group-item").css("pointer-events", "none");
        },
        success: function (data) {
        	$("#loading-image").hide();
        	$("a.list-group-item").css("pointer-events", "inherit");
        	$("#ajax-posts").show();
        	$( '.ajax-docs' ).html( data );
          $('.pdfemb-viewer').pdfEmbedder();
          setTimeout(function()   { 
            $('.panel-collapse').on('show.bs.collapse', function () {
                $(this).siblings('.panel-heading').addClass('active');
            });
            $('.panel-collapse').on('hide.bs.collapse', function () {
                $(this).siblings('.panel-heading').removeClass('active');
            });
          }, 1000);
        },
      });
    });
  	$(document).on( 'click', '.ajax-pagenavi a', function( event ) {
      
  		event.preventDefault();

      var termorder = $(".tcs-sort-container .tcs-sort-cat").data("order");
      var termorderby = $(".tcs-sort-container select#orderby").val();
      var page = this.href.substr(this.href.lastIndexOf('=') + 1);
      var termid = document.getElementById("term-id-pagnav").innerHTML;

      var taxonomySlug = $('.taxonomy-header').attr("tax-slug");
      var cptName = $('.taxonomy-header').attr("cpt-name");

  		$.ajax({
  			url: frontend_ajax_object.ajaxurl,
  			type: 'post',
  			data: {
  				action: 'documents_ajax_pagination',
          query_vars: frontend_ajax_object.query_vars,
          termid: termid,
          page: page,
          order: termorder,
          orderby: termorderby,
          cptName: cptName,
          taxonomySlug: taxonomySlug,
  			},
  			beforeSend: function() {
          $("#loading-image").show();
          $("#ajax-posts").hide();
          $("a.list-group-item").css("pointer-events", "none");
        },
        success: function (data) {
          $("#loading-image").hide();
          $("a.list-group-item").css("pointer-events", "inherit");
          $("#ajax-posts").show();
          $( '.ajax-docs' ).html( data );
          $('.pdfemb-viewer').pdfEmbedder();
          $('html, body').animate({ scrollTop: 0 }, 'slow');
          setTimeout(function()   { 
            $('.panel-collapse').on('show.bs.collapse', function () {
                $(this).siblings('.panel-heading').addClass('active');
            });
            $('.panel-collapse').on('hide.bs.collapse', function () {
                $(this).siblings('.panel-heading').removeClass('active');
            });
          }, 1000);
        },
  		})
  	});

    var multisidetabs=(function(){
      var opt,parentid,
      vars={
        listsub:'.list-sub',
        showclass:'mg-show'
      },
      events = function(){
        $(parentid).find('a').on('click',function(ev){
          ev.preventDefault();
          $(this).siblings('.item-active').removeClass('item-active');
          $(this).addClass('item-active');
          var atag = $(this), childsub = atag.next(vars.listsub);
          if(childsub && opt.multipletab == true){
            if(childsub.hasClass(vars.showclass)){
              childsub.removeClass(vars.showclass).slideUp(500);
              $(this).removeClass('item-active');
            }else{
              childsub.addClass(vars.showclass).slideDown(500);
            }
          }
          if(childsub && opt.multipletab == false){
           childsub.siblings(vars.listsub).removeClass(vars.showclass).slideUp(500);
           if(childsub.hasClass(vars.showclass)){
             childsub.removeClass(vars.showclass).slideUp(500);
             $(this).removeClass('item-active');
           }else{
             childsub.addClass(vars.showclass).slideDown(500);
           }
          }
        });
      },
      init=function(options){//initials
        if(options){
          opt = options;
          parentid = '#'+options.id;
          //test();
          events();
        }
      }        
        return {init:init};
    })();
        
    multisidetabs.init({
      "id":"mg-multisidetabs",
      "multipletab":false
    });

    $( '#mg-multisidetabs a' ).each(function() {
        if ( '' === $.trim( $( this ).text() ) ) {
            $( this ).remove();
        }
    });
    $('.panel-collapse').on('show.bs.collapse', function () {
        $(this).siblings('.panel-heading').addClass('active');
    });

    $('.panel-collapse').on('hide.bs.collapse', function () {
        $(this).siblings('.panel-heading').removeClass('active');
    });

    if($('div').hasClass('view-all-frontends-documents')) {

      $(".dropdown-toggle").removeAttr('data-toggle dropdown');

    }
            
  });
  $(document).ready(function () {
      $(document).on( 'click', '.download-doc', function( event ) {
        var id = $(this).attr("id");
        $.ajax({
          url: frontend_ajax_object.ajaxurl,
          type: 'post',
          data: {
            action: 'download_file',
            id: id,
          },
        })
      });

      $(document).on("shown.bs.collapse", ".documents-panel", function (event) {
        
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
            var id = this.id;
        }
        $.ajax({
            url: frontend_ajax_object.ajaxurl,
            type: 'post',
            data: {
              action: 'view_file',
              id: id,
            },
        })
      });
      $(document).on( 'click', '.popular-doc-btn', function( event ) {
        var start_date = $(this).attr("data-start-date");
        var end_date = $(this).attr("data-end-date");
        $.ajax({
          url: frontend_ajax_object.ajaxurl,
          type: 'post',
          data: {
            action: 'get_popular_documents',
            start_date: start_date,
            end_date: end_date,
          },
          beforeSend: function() {
            $(".loading-image").show();
            $("#popular-documents").hide();
            $(".popular-doc-btn").css("pointer-events", "none");
          },
          success: function (data) {
            $(".loading-image").hide();
            $(".popular-doc-btn").css("pointer-events", "inherit");
            $("#popular-documents").show();
            $('#popular-documents').html( data );
            $('.pdfemb-viewer').pdfEmbedder();
          },
        })
      });

      /**
       * Update URL for breadcrumb on archive template (frontend documents only)
       */

       if($('body').hasClass('tax-frontend_documents_category')) {

        var archiveTemplateHref = $('ol.fbc-items li:first-child a')[0].href;
        var archiveTemplateBreadcrumbAttr = $('ol.fbc-items li:first-child a').attr('cpt-term');

        if( archiveTemplateBreadcrumbAttr && archiveTemplateBreadcrumbAttr.length > 0 ) {
          return;
        }

        if(archiveTemplateHref) {
          $('ol.fbc-items li:first-child a').prop('href', frontend_ajax_object.site_url + '/view-all-documents/');
        }

      }

    });

    /**
     * View all documents ajax callback
     */

    if($('div').hasClass('view-all-frontends-documents')) {

      $('#view-all-documents tbody').append('<tr class="no-record-found"><td>No records found.</td></tr>');

      request_to_get_documents_from_database('');

    }

    /**
     * Function to get documents from database
     */

    function request_to_get_documents_from_database(authorSelected) {
      $('#view-all-documents').DataTable({
        language: {
          searchPlaceholder: "Search by name..."
        },
        ajax: {
          'type': 'POST',
          'url': frontend_ajax_object.ajaxurl + '?action=get_documents_from_db',
        },
        columns: [
          { data: 'document_name' },
          { data: 'author_name' },
          { data: 'categories' },
          { data: 'upload_document' },
          { data: 'post_date' },
          { data: 'delete_document' }
        ],
        columnDefs: [

            // author column
            {
              "targets": 1,
              "orderable": false
            },

            // categories column
            {
              "targets": 2,
              "orderable": false
            },

            // upload_document
            {
              "render": function (data, type, row) {
                if(data) {

                  var data = data.split('|');
                  var doc_id = data[0];
                  var doc_url = data[1];

                  return '<a class="frontend-download-doc" id="' + doc_id + '" target="_blank" href="' + doc_url + '" download>Download</a>';
                } else {
                  return '-';
                }
              },
              "targets": 3,
              "orderable": false

            },
            // delete_document
            {
              "render": function (data, type, row) {

                var deleteDoc = '';
                if(data) {
                  deleteDoc = '<a href="#" doc-id = '+ data +'><i class="fa fa-trash delete-document"></i></a>';
                } else {
                  deleteDoc = '';
                }

                return deleteDoc;
                
              },
              "targets": 5,
              "orderable": false
            }
        ],
        "processing": true,
        "serverSide": true,
      });
    }

    /**
     * Delete document
     */

    $(document).on('click', '.delete-document', function(e) {

      e.preventDefault();

      Swal.fire({
        title: 'Are you sure you want to delete this document?',
        icon: 'warning',
        showCancelButton: true,
        showCloseButton: true,
        confirmButtonColor: '#009eda',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        allowOutsideClick: false,
        cancelButtonText: 'Cancel',
      }).then((result) => {

        if (result.dismiss == 'close' || result.dismiss == 'cancel') {
            return;
        } else {

          var docID = $(this).parent().attr('doc-id');

          if(docID) {

            $.ajax({
              url: frontend_ajax_object.ajaxurl,
              type: 'post',
              data: {
                action: 'delete_document_using_id',
                document_id: docID,
              },
              beforeSend: function() {
                $(".loader-wrapper").show();
              },
              success: function (response) {

                var retrievedData = JSON.parse(response);

                if(retrievedData) {
                  $(".loader-wrapper").hide();
                  Swal.fire({
                    title: 'Document has been deleted!',
                    icon: 'success',
                  }).then((result) => {
                    if(result) {
                      window.location.href = frontend_ajax_object.site_url + '/view-all-documents?document_deleted=' + retrievedData.document_data.ID;
                    }
                  });
                }

              }
            });

          }

        }

      });
    });

    /**
     * Download document ajax callback
     * Function "download_document_log_handler" is inside the class-reports-public.php file
     */

     $(document).on( 'click', '.frontend-download-doc', function( event ) {
      var doc_id = $(this).attr("id"); // document id

      if(doc_id) {

        $.ajax({
          url: frontend_ajax_object.ajaxurl,
          type: 'post',
          data: {
            action: 'download_document_log_handler',
            id: doc_id,
          },
        });

      }

    });
// Frontend Doc 
	$('#front-end-post-form').on('click', '.button-primary', function() {
      if ($('#frontend_document_file-status a').length === 0 ) {
          if($('#file_up_err_msg').length === 0){
            $('#front-end-post-form .button-primary').before('<div id="file_up_err_msg">Please add file.</div>');
          }
      } else {
          $('#file_up_err_msg').remove();
      }
  	});

  $('#front-end-post-form').on('click', '.cmb2-upload-button', function() {
        $('#file_up_err_msg').remove();
  });	 
$(document).ready(function() {
    // Check if element with class "document-success-message" exists
    if ($('.document-success-message').length > 0) {
        // Fade out the element after 10 seconds
        setTimeout(function() {
            $('.document-success-message').fadeOut();
        }, 4000); // 10 seconds
    }
	
        $('.button-secondary').click(function(){
            if ($('.frd-er-msg').length > 0) {
                $('.add-new-document h3').remove();
            }
        });
});
})( jQuery );