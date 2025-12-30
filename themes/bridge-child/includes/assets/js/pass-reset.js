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
    var password = document.getElementById('user_password');
    var password2 = document.getElementById('user_password1');
    var text = document.getElementById('strength-text');
    var match = document.getElementById('match-text');


    password.addEventListener('input', function() {
      var val = password.value;
      var val2 = password2.value;
      var result = zxcvbn(val);
     
      // Update the text indicator
      if(val !== "") {
          text.innerHTML = "Strength: " + "<strong>" + strength[result.score] + "</strong>" + "<span class='feedback'>" + result.feedback.warning + " " + result.feedback.suggestions + "</span"; 
      }
      else {
          text.innerHTML = "";
      }
    });

    password2.addEventListener('input', function() {
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

    /*Submit the password reset form via ajax*/
    $( '#passwordreset' ).submit(function(e){

        e.preventDefault();

        $('.login-error').slideUp();

        //check if password fields are empty
        if( $('#user_password').val()=='' || $('#user_password1').val()=='' ){
            $('#match-text').html("<div class='error'>Password fields can't be empty</div>").slideDown();
            return false;
        }

        if( $('#user_password').val() !== $('#user_password1').val() ){
            return false;
        }



        var formData= $(this).serialize();

        $.ajax({
            url: frontend_ajax_object.ajaxurl,
            type: 'post',
            data: {formData: formData, action:'reset_user_password' },
            beforeSend: function() {
              /*$("#loading-image").show();
              $(".profile-form .pt-dashboard-table").hide();*/
            },
        })
        .done(function(status) {
           /* $("#loading-image").hide();
            $(".profile-form .pt-dashboard-table").show();*/
            console.log(status);


            switch(status){

                case 'expiredkey' :
                case 'invalidkey' :
                $('.login-error').html('<div class="error">Sorry, the login key does not appear to be valid or is expired.</div>').slideDown();
                break;

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
    });

  });
})(jQuery);