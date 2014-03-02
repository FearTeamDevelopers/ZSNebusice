$(document).ready(function() {

    $(window).load(function() {
        $("#loader, .loader").hide();
    });
    /* GLOBAL SCRIPTS */

    $('.dt-base').dataTable({
        "aaSorting": [],
        "bJQueryUI": true,
        "iDisplayLength": 25,
        "sPaginationType": "full_numbers"
    });

    $('.dt-extended').dataTable({
        "aaSorting": [],
        "bJQueryUI": true,
        "iDisplayLength": 50,
        "sPaginationType": "full_numbers"
    });

    $(".datepicker").datepicker({
        changeMonth: true,
        changeYear: true,
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

    $(".button-edit").button({
        icons: {
            primary: "ui-icon-pencil"
        },
        text: false
    });
    $(".button-delete").button({
        icons: {
            primary: "ui-icon-trash"
        },
        text: false
    });
    $(".button-detail").button({
        icons: {
            primary: "ui-icon-search"
        },
        text: false
    });
    $(".button-comment").button({
        icons: {
            primary: "ui-icon-comment"
        },
        text: false
    });
    $(".button-person").button({
        icons: {
            primary: "ui-icon-person"
        },
        text: false
    });

    $(".uploadPhotoForm .multi-upload").click(function() {
        if ($(".uploadPhotoForm input").length < 8) {
            $(".uploadPhotoForm .multi-upload").after("<br/><br/><input type=\"file\" name=\"photo[]\" />");
        }
    });

    $("form.uploadPhotoForm").submit(function() {
        $("#loader").show();
    });

});