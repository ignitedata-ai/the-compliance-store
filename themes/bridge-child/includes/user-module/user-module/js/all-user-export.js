(function ($) {
  var total_chunks = 0;
  $("body").on("submit", "#na-pt-export-all", function (e) {
    // alert("testing");
    e.preventDefault();
    // $.ajaxSetup({
    //   beforeSend: function () {
    //     $("body").append("<div class='gdpr-loading'></div>");
    //     // alert('1');
    //   },
    //   complete: function () {

    //     // alert('2');
    //   },
    // });
    jQuery.post(
      the_ajax_script.ajaxurl,
      {
        action: "pt_na_export_all_user",
        data: "none",
      },
      function (data) {
        total_chunks = data;
        // console.log("chunks" + data);
        query_call(data);
        $(".pt-ajax-res-all-user-export").append(
          '<progress id="download-progress" value="0" max="' +
            data +
            '" style="width:100%; height:30px; ">  </progress>'
        );
      }
    );
  });
  var fileurl = "";
  function query_call(data) {
    if (data > 0) {
      jQuery.post(
        the_ajax_script.ajaxurl,
        {
          action: "pt_query_call_for_all_export",
          data: { chunk: total_chunks - data },
        },
        function (url) {
          // console.log("iteration no " + url);
          fileurl = url;
          query_call(data - 1);
          // $('#download-progress').attr('value').value;
          document.getElementById("download-progress").value =
            total_chunks - data + 1;
        }
      );
    } else {
      // console.log("end");
      // console.log(fileurl);
      $(".pt-ajax-res-all-user-export").append(fileurl);
      // $(".gdpr-loading").remove();
    }
  }
})(jQuery);

// For export All user login History
(function ($) {
  var total_chunks = 0;
  $("body").on("submit", "#faulh-export-all", function (e) {
    e.preventDefault();
    var date_from = $("#date_from").val();
    var date_to = $("#date_to").val();
    var user_type = $("#user_type option:selected").val();
    var user_id = $("#user_id").val();
    var user_name = $("#username").val();
    var user_role = $("#user_role option:selected").val();
    var login_status = $("#login_status option:selected").val();
    var user_status = $("#user_status option:selected").val();
    var t_t_status = $("#t_t_status option:selected").val();
    var p_p_status = $("#p_p_status option:selected").val();
    var comp_id = $("#filter_company option:selected").val();

    jQuery.post(
      the_ajax_script.ajaxurl,
      {
        beforeSend: function () {
          $("#all-cnt").append(
            "<h3 class='strt-msg'> Process Starting ..... </h3>"
          );
          $(".faulh-action").remove();
          // alert('1');
        },
        action: "pt_export_all_user_login_history",
        data: {
          date_from: date_from,
          date_to: date_to,
          user_type: user_type,
          user_id: user_id,
          user_name: user_name,
          user_role: user_role,
          login_status: login_status,
          user_status: user_status,
          t_t_status: t_t_status,
          p_p_status: p_p_status,
          comp_id: comp_id,
        },
      },
      function (data) {
        total_chunks = data;
        query_call(data);
        $(".faulh-ajax-res-all-user-export").append(
          '<h3 id="faulh-records" style="text-align:center;"></h3></br><progress id="faulh-download-progress" value="0" max="' +
            data +
            '" style="width:100%; height:30px; ">  </progress>'
        );
      }
    );
  });
  var fileurl = "";
  function query_call(data) {
    var date_from = $("#date_from").val();
    var date_to = $("#date_to").val();
    var user_type = $("#user_type option:selected").val();
    var user_id = $("#user_id").val();
    var user_name = $("#username").val();
    var user_role = $("#user_role option:selected").val();
    var login_status = $("#login_status option:selected").val();
    var user_status = $("#user_status option:selected").val();
    var t_t_status = $("#t_t_status option:selected").val();
    var p_p_status = $("#p_p_status option:selected").val();
    var comp_id = $("#filter_company option:selected").val();
    var userEmail = $("#uemail").val();
    if (data > 0) {
      jQuery.post(
        the_ajax_script.ajaxurl,
        {
          beforeSend: function () {
            $("#all-cnt .strt-msg").remove();
            // alert('1');
          },
          action: "pt_query_call_for_all_user_login_history",
          data: {
            userEmail: userEmail,
            t_chunk: total_chunks,
            chunk: total_chunks - data,
            date_from: date_from,
            date_to: date_to,
            user_type: user_type,
            user_id: user_id,
            user_name: user_name,
            user_role: user_role,
            login_status: login_status,
            user_status: user_status,
            t_t_status: t_t_status,
            p_p_status: p_p_status,
            comp_id: comp_id,
          },
        },
        function (url) {
          fileurl = url;
          query_call(data - 1);
          test1 = total_chunks - data + 1;
          test2 = total_chunks * 1000;
          per = Math.round((test1 / test2) * 100000);
          if (per > 99) {
            per = 100;
          }

          document.getElementById("faulh-download-progress").value =
            total_chunks - data + 1;
          document.getElementById("faulh-records").innerHTML =
            // test1 * 1000 + " records are processed out of " + test2 * 1000;
            "Pleae Do Not  Close the Tab!<br><br>" +
            per +
            "% records are processed";
        }
      );
    } else {
      $(".faulh-ajax-res-all-user-export").append(fileurl);
    }
  }
})(jQuery);

jQuery(document).ready(function () {
  let input = document.querySelector("#uemail");

  input.addEventListener("change", ValidateEmail);

  function ValidateEmail() {
    var validRegex =
      /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

    if (input.value.match(validRegex)) {
      return true;
    } else {
      alert("Please enter valid email address!");
    }
  }
});
