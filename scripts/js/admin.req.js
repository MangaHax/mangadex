<?php 
switch ($mode) {
	case 'tempmail':
	
	print jquery_post("admin_add_tempmail", 0, "plus", "Add", "Adding", "Added.", "location.reload();");
	
	break;
	
	case 'ip_unban':
	
	print jquery_post("admin_ip_unban", 0, "gavel", "Unban", "Unbanning", "Unbanned.", "");
	print jquery_post("admin_ip_ban", 0, "gavel", "Ban", "Banning", "Banned.", "");

	break; 
	
	case 'stats':
	?>
	
	var ctx = $("#registrations_graph");
	var x =  JSON.parse(ctx.attr('data-x'));
	var y =  JSON.parse(ctx.attr('data-y'));

	var myLineChart = new Chart(ctx, {
		type: 'line',
		data: {
			datasets: [{
				data: y,
				pointRadius: 0,
				pointHitRadius: 5,
				label: 'User registrations over time'
			}],
			labels: x,
		},
	});

	<?php
	break;
}	
?>

$('.table-ip-banlist button.btn-danger').click(function (e) {
	var ip = $(e.currentTarget).attr('data-ip');
	if (ip != null) {
        var success_msg = "<div class='alert alert-success text-center' role='alert'><strong>Success:</strong> Unbanned.</div>";

        $(e.currentTarget).html("<span class='fas fa-spinner fa-fw fa-pulse' aria-hidden='true' ></span>").attr('disabled', true);

        var formData = new FormData();
        formData.append('ip', ip);

        $.ajax({
            url: '/ajax/actions.ajax.php?function=admin_ip_unban',
            type: 'POST',
            data: formData,
            cache: false,
            headers: {'cache-control': 'no-cache'},
            contentType: false,
            processData: false,
            async: true,
            success: function (data) {
                if (!data) {
                    $('#message_container').html(success_msg).show().delay(3000).fadeOut();

                }
                else {
                    $('#message_container').html(data).show().delay(3000).fadeOut();
                }
                $(e.currentTarget).parent().parent().remove();
            }
        });
	}
});

$('.btn-remove-item').click(function (e) {
    $(e.currentTarget).parent().parent().remove();
});

var reason_position = 256;
$('.btn-add-item').click(function (e) {
    let type_id = $(e.currentTarget).attr('data-type-id');
    let t = $('<div data-type-id="'+type_id+'" class="form-group row item-row">' +
        '    <div class="col-12 col-lg-9">' +
        '        <input type="hidden" name="type_id['+type_id+']['+reason_position+']" value="" />' +
        '        <input type="text" name="text['+type_id+']['+reason_position+']" class="form-control" value="" />' +
        '    </div>' +
        '    <div class="col-8 col-lg-2 form-check">' +
        '        <input type="checkbox" name="is_info_required['+type_id+']['+reason_position+']" class="form-check-input" />' +
        '        <label class="form-check-label">Info Required</label>' +
        '    </div>' +
        '    <div class="col-4 col-lg-1 form-group">' +
        '        <button data-type-id="'+type_id+'" type="button" class="btn btn-sm btn-link btn-remove-item"><span class="fas fa-times fa-fw times" aria-hidden="true" title="Remove"></span></button>' +
        '    </div>' +
        '</div>');
    reason_position++;
    $('.item-container[data-type-id="'+type_id+'"]').append(t);
    $('.btn-remove-item').click(function (e) {
        $(e.currentTarget).parent().parent().remove();
    });
});