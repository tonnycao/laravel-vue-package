$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$(function () {
    $(function () {
        $("#new_request #type, #schedule_request #type").change(function () {
            $("#new_request #subtype option:not(:first), #schedule_request #subtype option:not(:first)").remove();
            if ($(this).val()) {
                $.getJSON("/loading/request/subtype/" + $(this).val()).done(
                    function (data) {
                        $(data).each((k, item) => {
                                $("<option value='" + item + "'>" + item + "</option>").appendTo($("#new_request #subtype, #schedule_request #subtype"));
                            }
                        )
                    }
                )
            }
        });

        $("#new_request #do_continue").click(function () {
            let name = $("#new_request #name").val();
            let type = $("#new_request #type").val();
            let subtype = $("#new_request #subtype option:selected").val();
            let download = $("#new_request #download option:selected").val();
            let source = $('#new_request #source option:selected').val();
            let box_folder = $('#new_request #box_folder').val();
            let box_file = $('#new_request #box_file').val();
            let pattern = $('#new_request #pattern').val();
            let date_pattern = $('#new_request #date_pattern').val();

            if (name.length < 3) {
                flash.show(['Please enter some characters in the name field']);
                return
            }
            if (type.length === 0) {
                flash.show(['Please select the type first']);
                return
            }
            if (download.length === 0) {
                flash.show(['Please select whether to download file fom email']);
                return
            }
            if (subtype.length === 0) {
                flash.show(['Please select the subtype']);
                return
            }
            if (['mail', 'sftp', 'box',].indexOf(source) === -1) {
                flash.show(['Please select a source']);
                return
            }
            if (source === 'box' && box_folder === '' && box_file === '') {
                flash.show(['Please input the box folder from where files would be fetched or the box file']);
                return
            }
            if (date_pattern === '' && pattern === '') {
                flash.show(['Please input either the file naming pattern or date pattern']);
                return
            }

            $.post("/loading/request", $("#new-request").serialize(), null, 'json').done(
                function (data) {
                    if (data.ok) {
                        window.location.reload();
                    } else {
                        flash.show(data.errors);
                    }
                }
            )
        });

        $("a.viewdetail").click(function (e) {
            e.preventDefault();
            $("#job-detail").load($(this).prop("href"),
                function () {
                    $("#request_detail_modal").modal('show');
                }
            )
        });

        $('#new_request input[name="box_folder"]').parent().hide();
        if ($('#new_request input[name="box_file"]').length > -1) {
            $('#new_request input[name="box_file"]').parent().hide();
        }
        $('#new_request select[name="source"]').change(function () {
            $('#new_request').displayDownloadOptions($(this).val());
        });
    });

    $("body").on("click", ".btn-confirm-box", function (e) {
        e.preventDefault();

        var good_to_go = true;
        var post_hook = $(this).data('post_hook');
        if (post_hook && post_hook.length > 0) {
            typeof window[post_hook] === 'function' && (good_to_go = window[post_hook]());
        }

        if (!good_to_go) {
            return;
        }

        $("#action").val($(this).data('action'));

        var hide = $("#reject-box").hasClass('hide');

        if ($("#action").val() == 'approve') {
            if (!hide) {
                $("#reject-box").addClass('hide')
            }
            if ($(this).data('comment')) {
                $("#reject-box").removeClass('hide')
            }
        } else {
            if (hide) {
                $("#reject-box").removeClass('hide')
            }
        }
        //load from remote?
        var msg = $(this).data('msg');
        var form_action = $(this).attr('href');


        ['submit_ok', 'submit_fail'].forEach((i) => {
            if ($(this).data(i)) {
                $("#confirm-form").data(i, $(this).data(i));
            } else {
                $("#confirm-form").data(i, false);
            }
        });


        if (!msg.startsWith('/')) {
            $("#confirm_info").text($(this).data('msg'));
            $("#confirm-form").attr('action', form_action);
            $("#confirm_box").modal('show');
        } else {
            $("#confirm_info").load(msg, function () {
                $("#confirm-form").attr('action', form_action);
                $("#confirm_box").modal('show').find('.modal-dialog').addClass('modal-lg');

            });
        }

        var msg = $(this).data('alert');
        if (msg) {
            $("#confirm_alert").text(msg).show();
        }

    });

    $("#confirm_continue").click(function () {
        if ($("#action").val() != 'approve') {
            var comment = $("#reject-comment").val();
            if (comment.length < 10 || comment.length > 200) {
                $("#reject-box").addClass('has-error');
                return;
            }
        }

        var must_confirm = $(".agreementbox").length;
        if (must_confirm) {
            var confirmed = $(".agreementbox:checked").length;
            if (confirmed != must_confirm) {
                alert('Please make sure all the items checked');
                return;
            }
        }

        $(this).addClass('disabled');

        var ok_handler = $("#confirm-form").data('submit_ok');
        var fail_handler = $("#confirm-form").data('submit_fail');

        if (ok_handler && ok_handler.length > 0) {
            typeof window[ok_handler] === 'function' && (ok_handler = window[ok_handler]);
        }

        if (fail_handler && fail_handler.length > 0) {
            typeof window[fail_handler] === 'function' && (fail_handler = window[fail_handler]);
        }


        $.post($("#confirm-form").attr('action'), $("#confirm-form").serialize(), 'null', 'json')
            .done(function (data) {
                $("#confirm_box").modal('hide')
                if (ok_handler) {
                    ok_handler(data);
                } else if (data.refresh) {
                    if (data.inform) {
                        alert(data.inform);
                    }
                    window.location.reload();
                } else if (data.alert) {
                    alert(data.alert);
                } else {
                    window.location.replace('/hub');
                }
            })
            .fail(function (data, text, status) {
                //$("#confirm_box").modal('hide')
                if (fail_handler) {
                    ok_handler();
                } else if (status == 'Unprocessable Entity') {
                    $("#reject-box").addClass('has-error');
                } else {
                    alert("No Permission");
                }
            })
            .always(function () {
                $("#confirm_continue").removeClass('disabled');
            });
    });

    $("#reject-comment").keyup(function () {
        $("#reject-box").removeClass('has-error');
    });

    let $dt_requests = $('.dt_requests');

    function displayReport() {
        var table = $dt_requests.DataTable({
            //stateSave: true,
            "buttons": [
                {
                    extend: 'copy',
                    className: 'btn-xs btn-default',
                    text: 'Copy',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                    }
                }
            ],
            "ajax": {
                "url": "/loading/request/fetch",
                "type": "POST",
                "dataSrc": 'data'
            },
            "search": {
                "regex": true,
                "smart": false
            },
            "columns": [
                {"data": "id"},                            // [0]
                {"data": "name"},                          // [1]
                {"data": "type"},                          // [2]
                {"data": "subtype"},                       // [3]
                {"data": "download"},                      // [4]
                {"data": "source"},                        // [5]
                {"data": "pattern"},                       // [6]
                {"data": "date_pattern"},                  // [7]
                {"data": "box_folder"},                    // [8]
                {"data": "box_file"},                      // [9]
                {"data": "status", "sClass": "num"},       // [10]
                {"data": "requested_by"},                  // [11]
                {"data": "requested_at"},                  // [12]
                {"data": "scheduled_at"},                  // [13]
                {"data": "finished_at"},                   // [14]
                {"data": ""}                               // [15]
            ],
            "bLengthChange": true,
            "aaSorting": [],
            "order": [],
            "language": {
                "lengthMenu": "_MENU_"
            },
            "footerCallback": function (row, data, start, end, display) {
            },
            "columnDefs": [
                {
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                    "visible": true,
                    "searchable": true
                },
                {
                    "targets": [15],
                    "render": function (data, type, full, meta) {
                        var btn_html = '<a class="btn btn-info btn-sm" href="/loading/request/' + full.id+ '" target="_blank">' +
                            ' View Detail</a>';
                        if (full.status === 0 && full.is_admin) {
                            btn_html += ' <a data-action="approve"' +
                                '           data-msg="Are you sure to approve the request?"' +
                                '           class="btn btn-success btn-sm btn-confirm-box"' +
                                '           href="/loading/request/approve/'+ full.id + '" target="_blank">Approve</a>' +
                                '        <a data-action="reject"' +
                                '           data-msg="Are you sure to reject the request?"' +
                                '           class="btn btn-danger btn-sm btn-confirm-box"' +
                                '           href="/loading/request/approve/'+ full.id + '" target="_blank">Reject</a>';
                        }
                        return btn_html;
                    },
                    "orderable": false
                },
            ]
        });
    }

    displayReport();
});

var flash = {
    message: "",
    showing: false,
    tpl: `<div class="alert alert-danger alert-notification" role="alert"> </div>`,
    container: null,
    show(msg) {
        if (this.showing) {
            this.hide();
        }
        if (!this.container) {
            $(this.tpl).appendTo($("body"));
            this.container = $(".alert-notification");
        }
        if (!$.isArray(msg)) {
            msg = [msg];
        }
        this.container.html('');
        for (var i = 0; i < msg.length; i++) {
            $("<p>" + msg[i] + "</p>").appendTo(this.container);
        }
        this.container.fadeIn();//removeClass('hide');
        this.showing = true;
        this.fade();
    },
    hide() {
        this.showing = false;
        this.container.fadeOut();//addClass('hide');
    },
    fade() {
        setTimeout(() => {
            this.hide();
        }, 3000)
    }
};
