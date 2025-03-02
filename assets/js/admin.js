jQuery(document).ready(function ($) {
  // Initialize color picker
  $(".color-picker").wpColorPicker();

  if ($("#the-list").length) {
    $("#the-list").sortable({
      items: "tr",
      axis: "y",
      helper: function (e, ui) {
        ui.children().each(function () {
          $(this).width($(this).width());
        });
        return ui;
      },
      update: function (event, ui) {
        var order = [];
        $("#the-list tr").each(function () {
          order.push($(this).attr("id").replace("post-", ""));
        });

        $.ajax({
          url: svgLogoAdmin.ajaxurl,
          type: "POST",
          data: {
            action: "update_svg_logo_order",
            order: order,
            nonce: svgLogoAdmin.nonce,
          },
          success: function (response) {
            if (response.success) {
              console.log("Order updated");
            }
          },
        });
      },
    });
  }
});
