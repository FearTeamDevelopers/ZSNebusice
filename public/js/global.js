$(document).ready(function() {

    $(window).load(function() {
        $("#loader, .loader").hide();
    });

    /* GLOBAL SCRIPTS */

    $("#slides").slidesjs({
        width: 1100,
        height: 250,
        play: {
            auto: true,
            pauseOnHover: true,
            interval: 10000,
        }
    });

    $(".datepicker").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        firstDay: 1
    });

    $(".datepicker-registration").datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "1960:2000",
        dateFormat: "yy-mm-dd",
        firstDay: 1
    });

    $("#report-bug").click(function() {
        $("#dialog").load('/setting/reportBug').dialog({
            title: "Report Bug",
            width: "550px",
            modal: true,
            position: {my: "center", at: "top", of: window},
            buttons: {
                Cancel: function() {
                    $(this).dialog("close");
                }
            }
        });
    });

    $("button.ajax-button").click(function() {
        var href = $(this).attr("href");
        var val = $(this).val();
        $("#dialog").load(href).dialog({
            title: val,
            width: "550px",
            modal: true,
            position: {my: "center", at: "top", of: window},
            buttons: {
                Cancel: function() {
                    $(this).dialog("close");
                }
            }
        });
    });

    $("a.showReplyForm").click(function(e) {
        e.preventDefault();
        $(this).siblings(".replyForm").toggle(500);
        $(".replyForm:visible textarea.mediuminput").focus();
    });

});