$('#report_button,.report-button').click(function (e) {
    let typename = $(e.currentTarget).attr('data-type-name');
    let type_id = $(e.currentTarget).attr('data-type-id');
    let item_id = $(e.currentTarget).attr('data-item-id');
    if (!typename || !item_id || !type_id) {
        console.error("Not enough parameters specified for report button");
        return;
    }

    $('#report_modal_form .report-item').addClass('d-none');
    $('#report_modal_form .report-item-'+typename).removeClass('d-none');

    $('#report_modal_form input[name="item_id"]').val(item_id);
    $('#report_modal_form input[name="type_id"]').val(type_id);
    $('#report_modal_form textarea[name="info"]').val('');

    let sel = $('#report_modal_form select');
    sel.html('');
    sel.append('<option value="">- Select A Reason -</option>');
    for (let i=0; i<report_form_select_options.length; i++) {
        if (report_form_select_options[i].type_id !== type_id)
            continue;
        sel.append('<option value="'+report_form_select_options[i].value+'">'+report_form_select_options[i].text+'</option>');
    }
    sel.selectpicker('refresh');

    switch (typename) {
        case 'manga':

            break;
        case 'chapter':

            break;
        case 'comment':
            let pb = $('#post_'+item_id+' .postbody').html();
            let tmp = document.createElement("div");
            tmp.innerHTML = pb;
            pb = tmp.textContent || tmp.innerText || pb || "";
            if (pb.length > 64)
                pb = pb.toString().substr(0, 64)+'...';
            $('#report_modal_form .report-item-comment-text').html(pb);
            break;
        case 'group':

            break;
        case 'user':

            break;
    }

    $('#report_modal').modal();
});

$('#report_modal_form').submit(function (e) {
    e.preventDefault();

    let formData = new FormData($(this)[0]);

    $.ajax({
        url: '/ajax/actions.ajax.php?function=report_submit',
        type: 'POST',
        data: formData,
        cache: false,
        headers: {'cache-control': 'no-cache'},
        contentType: false,
        processData: false,
        success: function (data) {
            try {
                data = JSON.parse(data);
            } catch (err){}

            if (data.status  === 'success') {
                $('#message_container').html("<div class='alert alert-success text-center' role='alert'><strong>Success:</strong> Report has been submitted.</div>").show().delay(3000).fadeOut();
                $('#report_modal').modal('hide');
            }
            else {
                $('#message_container').html("<div class='alert alert-danger text-center' role='alert'><strong>Error:</strong> Failed to submit report: "+(data.message || 'General error')+"</div>").show().delay(3000).fadeOut();
            }
        }
    });
});

$('#reason_id').change(function (e) {
    let required = false;
    for (let i=0; i<report_form_select_options.length; i++) {
        if (report_form_select_options[i].value === e.currentTarget.value) {
            required = report_form_select_options[i].required;
            break;
        }
    }
    if (required) {
        $('#report_modal_form textarea').attr('required', 'required');
    } else {
        $('#report_modal_form textarea').removeAttr('required');
    }
});

var report_form_select_options = [];
$(document).ready(function () {
    $('#report_modal_form option').each(function (i, e) {
        if (!$(e).val())
            return;
        let type_id = $(e).attr('data-type-id');
        let value = $(e).val();
        let text = e.innerText || e.innerHTML;
        let required = $(e).attr('required') !== undefined;
        report_form_select_options.push({'type_id': type_id, 'value': value, 'text': text, 'required': required});
    });
});