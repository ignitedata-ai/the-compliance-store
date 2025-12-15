/**
 * Get ultimate faq
 */
function wpsc_get_ultimate_faq(nonce) {
  wpsc_show_modal();
  var data = { action: "wpsc_get_ultimate_faq", _ajax_nonce: nonce };

  jQuery.post(supportcandy.ajax_url, data, function (res) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(res.title);
    jQuery(".wpsc-modal-body").html(res.body);
    jQuery(".wpsc-modal-footer").html(res.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Get ultimate faq insert link
 *
 * @param {INT} post_id
 */
function wpsc_ultimate_faq_insert_link(post_id, nonce) {
  var data = {
    action: "wpsc_ultimate_faq_insert_link",
    post_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    var is_tinymce =
      typeof tinyMCE != "undefined" &&
      tinyMCE.activeEditor &&
      !tinyMCE.activeEditor.isHidden();
    if (is_tinymce) {
      tinymce.activeEditor.execCommand("mceInsertContent", false, res);
    } else {
      var txt = jQuery(".wpsc_textarea");
      var caretPos = txt[0].selectionStart;
      var textAreaTxt = txt.val();
      txt.val(
        textAreaTxt.substring(0, caretPos) +
          res +
          textAreaTxt.substring(caretPos)
      );
    }
    wpsc_close_modal();
  });
}

/**
 * Ultimate faq insert text
 *
 * @param {*} post_id
 */
function wpsc_ultimate_faq_insert_text(post_id, nonce) {
  var data = {
    action: "wpsc_ultimate_faq_insert_text",
    post_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    var is_tinymce =
      typeof tinyMCE != "undefined" &&
      tinyMCE.activeEditor &&
      !tinyMCE.activeEditor.isHidden();
    if (is_tinymce) {
      tinymce.activeEditor.execCommand("mceInsertContent", false, res);
    } else {
      var txt = jQuery(".wpsc_textarea");
      var caretPos = txt[0].selectionStart;
      var textAreaTxt = txt.val();
      txt.val(
        textAreaTxt.substring(0, caretPos) +
          res +
          textAreaTxt.substring(caretPos)
      );
    }
    wpsc_close_modal();
  });
}

/**
 * Get acronix faq
 */
function wpsc_get_acronix_faq(nonce) {
  wpsc_show_modal();
  var data = { action: "wpsc_get_acronix_faq", _ajax_nonce: nonce };

  jQuery.post(supportcandy.ajax_url, data, function (res) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(res.title);
    jQuery(".wpsc-modal-body").html(res.body);
    jQuery(".wpsc-modal-footer").html(res.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Get acronix faq insert link
 *
 * @param {INT} post_id
 */
function wpsc_acronix_faq_insert_link(post_id, nonce) {
  var data = {
    action: "wpsc_acronix_faq_insert_link",
    post_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    var is_tinymce =
      typeof tinyMCE != "undefined" &&
      tinyMCE.activeEditor &&
      !tinyMCE.activeEditor.isHidden();
    if (is_tinymce) {
      tinymce.activeEditor.execCommand("mceInsertContent", false, res);
    } else {
      var txt = jQuery(".wpsc_textarea");
      var caretPos = txt[0].selectionStart;
      var textAreaTxt = txt.val();
      txt.val(
        textAreaTxt.substring(0, caretPos) +
          res +
          textAreaTxt.substring(caretPos)
      );
    }
    wpsc_close_modal();
  });
}

/**
 * Acronix faq insert text
 *
 * @param {*} post_id
 */
function wpsc_acronix_faq_insert_text(post_id, nonce) {
  var data = {
    action: "wpsc_acronix_faq_insert_text",
    post_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    var is_tinymce =
      typeof tinyMCE != "undefined" &&
      tinyMCE.activeEditor &&
      !tinyMCE.activeEditor.isHidden();
    if (is_tinymce) {
      tinymce.activeEditor.execCommand("mceInsertContent", false, res);
    } else {
      var txt = jQuery(".wpsc_textarea");
      var caretPos = txt[0].selectionStart;
      var textAreaTxt = txt.val();
      txt.val(
        textAreaTxt.substring(0, caretPos) +
          res +
          textAreaTxt.substring(caretPos)
      );
    }
    wpsc_close_modal();
  });
}

/**
 * Get easy accordion faq
 */
function wpsc_get_easy_accordion_faq(nonce) {
  wpsc_show_modal();
  var data = { action: "wpsc_get_easy_accordion_faq", _ajax_nonce: nonce };

  jQuery.post(supportcandy.ajax_url, data, function (res) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(res.title);
    jQuery(".wpsc-modal-body").html(res.body);
    jQuery(".wpsc-modal-footer").html(res.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Get easy accordion faq insert link
 *
 * @param {INT} post_id
 */
function wpsc_easy_accordion_faq_insert_link(post_id, nonce) {
  var data = {
    action: "wpsc_easy_accordion_faq_insert_link",
    post_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    var is_tinymce =
      typeof tinyMCE != "undefined" &&
      tinyMCE.activeEditor &&
      !tinyMCE.activeEditor.isHidden();
    if (is_tinymce) {
      tinymce.activeEditor.execCommand("mceInsertContent", false, res);
    } else {
      var txt = jQuery(".wpsc_textarea");
      var caretPos = txt[0].selectionStart;
      var textAreaTxt = txt.val();
      txt.val(
        textAreaTxt.substring(0, caretPos) +
          res +
          textAreaTxt.substring(caretPos)
      );
    }
    wpsc_close_modal();
  });
}

/**
 * Easy accordion faq insert text
 *
 * @param {*} post_id
 */
function wpsc_easy_accordion_faq_insert_text(post_id, nonce) {
  var data = {
    action: "wpsc_easy_accordion_faq_insert_text",
    post_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    var is_tinymce =
      typeof tinyMCE != "undefined" &&
      tinyMCE.activeEditor &&
      !tinyMCE.activeEditor.isHidden();
    if (is_tinymce) {
      tinymce.activeEditor.execCommand("mceInsertContent", false, res);
    } else {
      var txt = jQuery(".wpsc_textarea");
      var caretPos = txt[0].selectionStart;
      var textAreaTxt = txt.val();
      txt.val(
        textAreaTxt.substring(0, caretPos) +
          res +
          textAreaTxt.substring(caretPos)
      );
    }
    wpsc_close_modal();
  });
}

/**
 * Get BetterDocs faq
 */
function wpsc_get_betterdocs_faq(nonce) {
  wpsc_show_modal();
  var data = { action: "wpsc_get_betterdocs_faq", _ajax_nonce: nonce };

  jQuery.post(supportcandy.ajax_url, data, function (res) {
    // Set to modal.
    jQuery(".wpsc-modal-header").text(res.title);
    jQuery(".wpsc-modal-body").html(res.body);
    jQuery(".wpsc-modal-footer").html(res.footer);
    // Display modal.
    wpsc_show_modal_inner_container();
  });
}

/**
 * Get BetterDocs faq insert link
 *
 * @param {INT} post_id
 */
function wpsc_betterdocs_faq_insert_link(post_id, nonce) {
  var data = {
    action: "wpsc_betterdocs_faq_insert_link",
    post_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    var is_tinymce =
      typeof tinyMCE != "undefined" &&
      tinyMCE.activeEditor &&
      !tinyMCE.activeEditor.isHidden();
    if (is_tinymce) {
      tinymce.activeEditor.execCommand("mceInsertContent", false, res);
    } else {
      var txt = jQuery(".wpsc_textarea");
      var caretPos = txt[0].selectionStart;
      var textAreaTxt = txt.val();
      txt.val(
        textAreaTxt.substring(0, caretPos) +
          res +
          textAreaTxt.substring(caretPos)
      );
    }
    wpsc_close_modal();
  });
}

/**
 * BetterDocs faq insert text
 *
 * @param {*} post_id
 */
function wpsc_betterdocs_faq_insert_text(post_id, nonce) {
  var data = {
    action: "wpsc_betterdocs_faq_insert_text",
    post_id,
    _ajax_nonce: nonce,
  };
  jQuery.post(supportcandy.ajax_url, data, function (res) {
    var is_tinymce =
      typeof tinyMCE != "undefined" &&
      tinyMCE.activeEditor &&
      !tinyMCE.activeEditor.isHidden();
    if (is_tinymce) {
      tinymce.activeEditor.execCommand("mceInsertContent", false, res);
    } else {
      var txt = jQuery(".wpsc_textarea");
      var caretPos = txt[0].selectionStart;
      var textAreaTxt = txt.val();
      txt.val(
        textAreaTxt.substring(0, caretPos) +
          res +
          textAreaTxt.substring(caretPos)
      );
    }
    wpsc_close_modal();
  });
}
