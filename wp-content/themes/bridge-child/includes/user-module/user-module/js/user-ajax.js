(function ($) {
    'use strict';
    
        $(document).ready(function () {
            var strength = {
                    0: "Worst 😟",
                    1: "Bad ☹",
                    2: "Weak 😐",
                    3: "Good 🙂",
                    4: "Strong 😀"
            }
    
            var password = document.getElementById('passreset1');
            var password2 = document.getElementById('passreset2');
            var meter = document.getElementById('password-strength-meter');
            var text = document.getElementById('password-strength-text');
            var match = document.getElementById('password-match-text');
            var answer = document.getElementById('security_question_answer');
            var question_text = document.getElementById('question-text');
    
            if (password  !== null) {
                password.addEventListener('input', function()
                {
                    var val = password.value;
                    var val2 = password2.value;
                    var result = zxcvbn(val);
                    
                    // Update the password strength meter
                    meter.value = result.score;
                   
                    // Update the text indicator
                    if(val !== "") {
                        text.innerHTML = "Strength: " + "<strong>" + strength[result.score] + "</strong>" + "<span class='feedback'>" + result.feedback.warning + " " + result.feedback.suggestions + "</span"; 
                    }
                    else {
                        text.innerHTML = "";
                    }
    
                });
                password2.addEventListener('input', function()
                {
                    var val = password.value;
                    var val2 = password2.value;
                   
                    // Update the match password indicator indicator
                    if(val2 !== val) {
                        match.innerHTML = "Password doesn't matched"; 
                    }
                    else {
                        match.innerHTML = "";
                    }
    
                });
                answer.addEventListener('input', function()
                {
                    var answer_val = answer.value;
                   
                    if(answer_val == "") {
                        question_text.innerHTML = "Answer can't be empty"; 
                    }
                    else {
                        question_text.innerHTML = "";
                    }
    
                });
    
                $(".toggle-password").click(function() {
    
                  $(this).toggleClass("fa-eye fa-eye-slash");
                  var input = $($(this).attr("toggle"));
                  if (input.attr("type") == "password") {
                    input.attr("type", "text");
                  } else {
                    input.attr("type", "password");
                  }
                });
            }
            // Submit the password reset form via ajax
            $( '#resetpassaccount' ).submit(function(e){
    
                e.preventDefault();
    
                $('.login-error').slideUp();
    
                //check if question field is empty
                if( $('#security_question_answer').val()=='' || $('#security_question_answer').val()=='' ){
                    $('#question-text').html("<div class='error'>Answer can't be empty</div>").slideDown();
                    return false;
                }
                //check if password fields are empty
                if( $('#passreset1').val()=='' || $('#passreset2').val()=='' ){
                    $('#password-match-text').html("<div class='error'>Password fields can't be empty</div>").slideDown();
                    return false;
                }
    
                if( $('#passreset1').val() !== $('#passreset2').val() ){
                    return false;
                }
    
                var formData= $(this).serialize();
    
                $.ajax({
                    url: frontend_ajax_object.ajaxurl,
                    type: 'post',
                    data: {form_values: formData, action:'reset_user_pass' },
                    beforeSend: function() {
                      $("#loading-image").show();
                      $(".profile-form .pt-dashboard-table").hide();
                    },
                })
                .done(function(status) {
                    $("#loading-image").hide();
                    $(".profile-form .pt-dashboard-table").show();
    
                    switch(status){
    
                        // case 'expiredkey' :
                        // case 'invalidkey' :
                        // $('.login-error').html('<div class="error">Sorry, the login key does not appear to be valid or is expired.</div>').slideDown();
                        // break;
    
                        case 'mismatch' :
                        $('.login-error').html('<div class="error">The passwords do not match.</div>').slideDown();
                        break;
                        case 'wronganswer' :
                        $('.login-error').html('<div class="error">Answer is wrong.</div>').slideDown();
                        break;
    
                        case 'success' :
                        $('.login-error').html('<div class="success">Your password has been reset.</div>').slideDown();
                        setTimeout(function(){// wait for 5 secs(2)
                                   location.reload(); // then reload the page.(3)
                              }, 3000);
                        break;
    
                        default:
                        //console.log(status);
                        $('.login-error').html('<div>Something went wrong.Please try again </div>').slideDown();
                        break;
    
                    }
    
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    console.log("complete");
                });
            });
            $( '#setsecurityquestion' ).submit(function(e){
    
                e.preventDefault();
    
                $('.login-error').slideUp();
    
                //check if question field is empty
                if( $('#security_question').val()=='' || $('#security_question_answer').val()=='' ){
                    $('.error').html("Field is required").slideDown();
                    return false;
                }
    
                var formData= $(this).serialize();
    
                $.ajax({
                    url: frontend_ajax_object.ajaxurl,
                    type: 'post',
                    data: {form_values: formData, action:'set_security_question' },
                    beforeSend: function() {
                      $("#loading-image").show();
                      $(".profile-form .pt-dashboard-table").hide();
                    },
                })
                .done(function(status) {
                    $("#loading-image").hide();
                    $(".profile-form .pt-dashboard-table").show();
    
    
                    switch(status){
    
                        case 'success' :
                        $('.error').html('<div class="success">You have Set Successfully Your Security Quesion.</div>').slideDown();
                        setTimeout(function(){// wait for 5 secs(2)
                                   location.reload(); // then reload the page.(3)
                              }, 3000); 
                        
                        default:
                        //console.log(status);
                        $('.login-error').html('<div>Something went wrong.Please try again </div>').slideDown();
                        break;
    
                    }
    
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    console.log("complete");
                });
            });
            // Submit the edit-profile form via ajax
            $( '#edit-profile-form' ).submit(function(e){
    
                e.preventDefault();
    
                $('.login-error').slideUp();
    
                //check if question field is empty
                if( $('#first-name').val()=='' || $('#first-name').val()=='' ){
                    $('#first-name-error').html("<div class='error'>First Name is required</div>").slideDown();
                    return false;
                }
    
                var usernameRegex = /^[A-Za-z][A-Za-z0-9]{5,20}$/;
                var username = $('#nickname').val();
                if ( username.match(usernameRegex) == null ) {
                    $('#nickname-error').html("Enter a valid Username!").slideDown();
                    return false;
                }
    
    
                var profileData= $(this).serialize();
    
                $.ajax({
                    url: frontend_ajax_object.ajaxurl,
                    type: 'post',
                    data: {profile_form_values: profileData, action:'update_profile_frontend' },
                    beforeSend: function() {
                      $("#loading-image").show();
                      $(".profile-form #edit-profile-form").hide();
                    },
                })
                .done(function(status) {
                    var response = JSON.parse(status);
                    //console.log(status);
                    $("#loading-image").hide();
                    $(".profile-form #edit-profile-form").show();
                    $(".profile-form #first-name-error").hide();
                    if (response.response == 'success') {
                        $('.profile-updated').html('<div class="success">Your profile has been updated.</div>').slideDown();
                        $('#first-name-dashboard').html(response.first_name);
                        $('#last-name-dashboard').html(response.last_name);
                        $('#phone-dashboard').html(response.work_phone);
                        $('#company-dashboard').html(response.company);
                        var address = response.street_1 + '<br>' + response.street_2 + ' ' + response.city + ', ' + response.state + ' - ' + response.zip_code;
                        $('#address-dashboard').html(address);
                        $('#nickname-error').html("").slideDown()
                    } else if (response.response == 'username_exists') {
                        $('#nickname-error').html("Username already exists!").slideDown();
                        return false;
                    } else {
                        $('.profile-updated').html('<div class="error">Something went wrong.Please try again </div>').slideDown();
                    }
    
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    console.log("complete");
                });
            });
            // Submit the add user form via ajax
            $( '#add-user-form' ).submit(function(e){
    
                e.preventDefault();
    
                $('.login-error').slideUp();
    
                //check if email field is empty
                if( $('#add-email').val()=='' || $('#add-email').val()=='' ){
                    $('#add-email-error').html("<div class='error'>Email is required</div>").slideDown();
                    return false;
                }
                //check if first name field is empty
                if( $('#add-first-name').val()=='' || $('#add-first-name').val()=='' ){
                    $('#add-first-name-error').html("<div class='error'>First Name is required</div>").slideDown();
                    return false;
                }
    
    
    
                var profileData= $(this).serialize();
    
                $.ajax({
                    url: frontend_ajax_object.ajaxurl,
                    type: 'post',
                    data: {add_user_form_values: profileData, action:'add_user_frontend' },
                    beforeSend: function() {
                      $("#add-user-loading-image").show();
                      $(".profile-form #add-user-form").hide();
                    },
                })
                .done(function(status) {
                    console.log(status);
                    $("#add-user-loading-image").hide();
                    $(".profile-form #add-user-form").show();
                    $(".profile-form #add-first-name-error").hide();
                    $(".profile-form #add-email-error").hide();
                    if (status == 'success') {
                        $(".profile-form #add-user-form")[0].reset();
                        $('.user-added').html('<div class="success qas">User Successfully Added.</div>').slideDown();
    
                    }else if (status == 'invalidkey') {
                        $('.user-added').html('<div class="error">Invalid Login Key. Please login again.</div>').slideDown();
    
                    }else if (status == 'emailexists') {
                        $('.user-added').html('<div class="error">Email Already Exists. Please try another email.</div>').slideDown();
    
                    }
                    else {
    
                        $('.user-added').html('<div class="error">Something went wrong.Please try again </div>').slideDown();
                    }
    
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function() {
                    console.log("complete");
                });
            });
        });
    
    })(jQuery);