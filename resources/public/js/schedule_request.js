// schedule request

let shared = {
    deferreds: [],
    data: [],
};

function read_data(url, page = '1') {
    let d = $.Deferred();
    let gourl = url + '?page=' + page;
    $.getJSON(gourl).done(function (data) {
        shared.data = $.merge(shared.data, data.data)
        for (let i = 2; i <= data.last_page; i++) {
            gourl = url + '?page=' + i;
            shared.deferreds.push(
                $.getJSON(gourl).success(function (data) {
                    shared.data = $.merge(shared.data, data.data)
                })
            );
        }
        d.resolve();
    });

    return d.promise();
}

function actionEdit(obj) {
    let data_str = obj.getAttribute('data-edit');
    let item = JSON.parse(data_str);
    shared.data = item;

    let time = item.time.split(':');
    let hour = parseInt(time[0]);
    let min = time[1];
    let self = $("#edit_system_request");
    let subtypeOptions = '';
    $.getJSON("/loading/request/subtype/" + item.type).done(
        function (data) {
            $(data).each((k, val) => {
                subtypeOptions += "<option value='" + val + "'>" + val + "</option>";
            });
            $("#schedule-request #subtype").html(subtypeOptions);
            $(self).find('input[name="name"]').val(item.name);
            $(self).find('select[name="frequency_type"]').val(item.frequency_type);
            $(self).find('select[name="week"]').val(item.week);
            $(self).find('select[name="day"]').val(item.day);
            $(self).find('select[name="timezone"]').val(item.timezone);
            $(self).find('select[name="hour"]').val(hour);
            $(self).find('select[name="min"]').val(min);
            $(self).find('select[name="type"]').val(item.type);
            $(self).find('select[name="subtype"]').val(item.subtype);
            $(self).find('select[name="download"]').val(item.download);
            $(self).find('select[name="source"]').val(item.source);
            $(self).find('input[name="pattern"]').val(item.pattern);
            $(self).find('input[name="date_pattern"]').val(item.date_pattern);
            $(self).find('input[name="box_folder"]').val(item.box_folder);
            $(self).find('select[name="is_active"]').val(item.is_active);

            $(self).displayFrequencyOptions(item.frequency_type);
            $(self).displayDownloadOptions(item.source);
        }
    );
}

$(function () {
    let link = "/loading/index";

    read_data(link).done(function () {
        $.when.apply(null, shared.deferreds).done(function () {
            detail = shared.data;

            shared.data.sort(function (a, b) {
                return a.id > b.id ? -1 : (a.id == b.id ? 0 : 1);
            });
        })

    });

    $("#schedule_request #do_continue").click(function () {
        let name = $("#schedule_request #name").val();
        let frequency_type = $("#frequency_type option:selected").val();
        let week = $("#schedule_request #week option:selected").val();
        let day = $("#schedule_request #day option:selected").val();
        let timezone = $("#schedule_request #timezone option:selected").val();
        let hour = $("#schedule_request #hour option:selected").val();
        let min = $("#schedule_request #min option:selected").val();
        let type = $("#schedule_request #type option:selected").val();
        let subtype = $("#schedule_request #subtype option:selected").val();
        let is_active = $("#schedule_request #is_active option:selected").val();
        let download = $("#schedule_request #download option:selected").val();
        let source = $("#schedule_request #source option:selected").val();
        let pattern = $("#schedule_request #pattern").val();
        let date_pattern = $("#schedule_request #pattern").val();
        let box_folder = $("#schedule_request #box_folder").val();

        if (name.length < 3) {
            flash.show(['Please enter some characters in the name field']);
            return
        }
        if (frequency_type.length === 0) {
            flash.show(['Please select the frequency type']);
            return
        }
        if (week.length === 0 && (frequency_type === 'quarterly' || frequency_type === 'periodic')) {
            flash.show(['Please select the week']);
            return
        }
        if (day.length === 0 && (frequency_type === 'quarterly' || frequency_type === 'periodic' || frequency_type === 'weekly')) {
            flash.show(['Please select the day']);
            return
        }
        if (timezone.length === 0) {
            flash.show(['Please select the timezone']);
            return
        }
        if (hour.length === 0) {
            flash.show(['Please select the hour']);
            return
        }
        if (min.length === 0) {
            flash.show(['Please select the min']);
            return
        }
        if (is_active.length === 0) {
            flash.show(['Please select the whether schedule is active?']);
            return
        }
        if (source === 'box' && box_folder === '') {
            flash.show(['Please input the box folder from where files would be fetched']);
        }
        if (type.length === 0) {
            flash.show(['Please select the type type first']);
            return
        }
        if (download == 1) {
            if (source === '') {
                flash.show(['Please select the source first']);
                return
            }
            if ((pattern === '' || date_pattern === '') && source !== '') {
                flash.show(['Please specify either pattern or date pattern to match the file name']);
                return
            }
        }

        $.post("/loading/schedule", $("#schedule-request").serialize(), null, 'json').done(
            function (data) {
                if (data.ok) {
                    window.location.reload();
                } else {
                    flash.show(data.errors);
                }
            }
        )
    });

    $('#do_edit').click(function () {
        let link = "/loading/schedule/" + shared.data.id;
        let self = $("#edit_system_request");
        let data = {
            id: shared.data.id,
            name: $(self).find('input[name="name"]').val(),
            frequency_type: $(self).find('select[name="frequency_type"] option:selected').val(),
            week: $(self).find('select[name="week"] option:selected').val(),
            day: $(self).find('select[name="day"] option:selected').val(),
            timezone: $(self).find('select[name="timezone"] option:selected').val(),
            hour: $(self).find('select[name="hour"] option:selected').val(),
            min: $(self).find('select[name="min"] option:selected').val(),
            type: $(self).find('select[name="type"] option:selected').val(),
            subtype: $(self).find('select[name="subtype"] option:selected').val(),
            download: $(self).find('select[name="download"] option:selected').val(),
            source: $(self).find('select[name="source"] option:selected').val(),
            pattern: $(self).find('input[name="pattern"]').val(),
            date_pattern: $(self).find('input[name="date_pattern"]').val(),
            box_folder: $(self).find('input[name="box_folder"]').val(),
            is_active: $(self).find('select[name="is_active"] option:selected').val(),
        };
        let submit = validate(data);
        if (submit === true) {
            $.ajax({
                type: 'PUT',
                url: link,
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function (response, status) {
                    if (response.ok  && status === 'success') {
                        window.location.reload();
                    }
                    if (response.ok === false) {
                        $(".level .flex").html(response.errors[0]).css({color: 'red'});
                    }
                },
                error: function (xhr, status, error) {

                }
            });
        }
    });

    $('#schedule_request select[name="week"]').parent().hide();
    $('#schedule_request select[name="day"]').parent().hide();
    $('#schedule_request input[name="box_folder"]').parent().hide();

    $('#edit_system_request select[name="week"]').parent().hide();
    $('#edit_system_request select[name="day"]').parent().hide();
    $('#edit_system_request input[name="box_folder"]').parent().hide();

    $('#schedule_request select[name="frequency_type"], #edit_system_request select[name="frequency_type"]').change(function () {
        $('#schedule_request').displayFrequencyOptions($(this).val());
        $('#edit_system_request').displayFrequencyOptions($(this).val());
    });

    $('#schedule_request select[name="source"], #edit_system_request select[name="source"]').change(function () {
        $('#schedule_request').displayDownloadOptions($(this).val());
        $('#edit_system_request').displayDownloadOptions($(this).val());
    });

    function validate(data) {
        let result = true;
        let keys = Object.keys(data);
        for (let i = 0; i < keys.length; i++) {
            if (data[keys[i]] === undefined || data[keys[i]] === null) {
                result = false;
                $("." + keys[i] + "-error").removeClass('hidden');
            } else {
                $("." + keys[i] + "-error").addClass('hidden');
            }

            if (keys[i] === 'week' && result === false && data[keys[i]] === '') {
                if (['weekly', 'daily', 'weekdays', 'weekends',].indexOf(data['frequency_type']) > -1) {
                    result = true;
                    $("." + keys[i] + "-error").addClass('hidden');
                }
            }

            if (keys[i] === 'day' && result === false && data[keys[i]] === '') {
                if (['daily', 'weekdays', 'weekends',].indexOf(data['frequency_type']) > -1) {
                    result = true;
                    $("." + keys[i] + "-error").addClass('hidden');
                }
            }
        }

        return result;
    }
});

$.fn.displayFrequencyOptions = function (option)
{
    let require_week = ['quarterly', 'periodic',];
    let require_day = ['weekly',];

    if (require_day.indexOf(option) > -1) {
        $(this).find('#week').parent().hide();
        $(this).find('#day').parent().show();
    } else if (require_week.indexOf(option) > -1) {
        $(this).find('#week').parent().show();
        $(this).find('#day').parent().show();
    } else {
        $(this).find('#week').parent().hide();
        $(this).find('#day').parent().hide();
    }
};

$.fn.displayDownloadOptions = function (option) {
    switch (option) {
        case 'box':
            $(this).find('#box_folder').parent().show();
            if ($(this).find('#box_file').length > -1) {
                $(this).find('#box_file').parent().show();
            }
            break;
        default:
            $(this).find('#box_folder').parent().hide();
            if ($(this).find('#box_file').length > -1) {
                $(this).find('#box_file').parent().hide();
            }
            break;
    }
};
