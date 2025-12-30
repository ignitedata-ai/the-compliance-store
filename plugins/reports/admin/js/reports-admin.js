(function( $ ) {
	'use strict';
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 */
    $(document).ready(function () {

    	/**
		 * This function is used to send AJAX calls to generate activity report with date range
		 */
        $('#user_activity_choose_date_button').click(function (event) { //trigger upon clicking generate report
            event.preventDefault(); //prevent page from reloading
            var user_activity_start_date = $("input[name=user_activity_start_date]").val(); //get start date
            var user_activity_end_date = $("input[name=user_activity_end_date]").val(); //get end date
            //if start and end date fields have date
            if (user_activity_start_date && user_activity_end_date) {

            	//get total number of activity logs
            	var user_activity_total_count = $("input[name=user_activity_total_count]").val(); 

            	//get number of total iterations - 100 users per iteration
            	var counter = Math.round(user_activity_total_count/100);

            	var promises = [];

            	$("#ajax-loader").show(); //display loader

            	setTimeout(function() { //timeout used to display loader
					for (var i = 0; i < counter; i++) { //ajax loop

				        var request = $.ajax({ //send ajax call
		                    type: 'post',
		                    async: false, //this is used to send non-sync calls
		                    url: backend_reports_ajax_object.ajaxurl,
		                    data: {
		                        action: 'user_activity_date_range',
		                        start_date: user_activity_start_date, //send start date
		                        end_date: user_activity_end_date, //send end date
		                        loop_counter: i, //current iterations
		                        all_counter: counter, //total number of iterations - if last iteration then return file
		                    },
		                    success: function (response) {
		                        $('.user_activity_date_result').html(response); //display button to download report file
		                    }
	                	});

				        promises.push(request); //push promise

				        if (request.status !== 200) { //if call status is not sucessfull then return alert and break the loop
				        	alert('Oops something went wrong. Please try again.');
				        	$("#ajax-loader").hide();
				        	break;
				        }
					}

					$.when.apply(null, promises).done(function() {
			    		$("#ajax-loader").hide();
					});
			    }, 500);
            } else { //if start and end date fields are empty
            	alert("Please select Date Range");
            }
    	});
	});

})( jQuery );