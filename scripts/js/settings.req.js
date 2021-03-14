<?php 
if ($user->user_id) {
	print js_display_file_select();

	print jquery_post("change_password", 0, "save", "Save", "Saving", "Your password has been changed.", "");
	print jquery_post("reader_settings", 0, "save", "Save", "Saving", "Your reader settings have been saved.", "");
	print jquery_post("upload_settings", 0, "save", "Save", "Saving", "Your upload settings have been saved.", "");
	print jquery_post("site_settings", 0, "save", "Save", "Saving", "Your site settings have been saved.", "location.href = '/settings';");
	print jquery_post("change_profile", 0, "save", "Save", "Saving", "Your profile settings have been saved.", "location.href = '/settings';");
	
	if ($user->premium || $user->get_client_approval_time())
		print jquery_post("supporter_settings", 0, "save", "Save", "Saving", "Your supporter settings have been saved.", "");
	
} 
?>

document.querySelector('.profile-banner-show').addEventListener('click', function(evt) {
	this.parentElement.style.maxHeight = 'none'
	this.parentElement.removeChild(this)
});

/** 2fa logic **/
$('#remove_2fa_btn').click(function (e) {
	if (!confirm("Are you sure you want to remove 2FA from your account? You can re-add 2FA protection anytime."))
		return;

	var formData = new FormData();
	formData.append('confirm', 1);

	$(e.currentTarget).html('<span class="fas fa-spinner fa-fw fa-pulse" aria-hidden="true"></span>').attr('disabled', 'disabled');

	$.ajax({
		url: '/ajax/actions.ajax.php?function=2fa_remove',
		type: 'POST',
		data: formData,
		cache: false,
		headers: {'cache-control': 'no-cache'},
		contentType: false,
		processData: false,
		async: true,
		success: function (data) {
			data = JSON.parse(data);

			if (data.status === 'success') {
				location.reload();
			} else {
				$('.remove-2fa-errors').html('<div class="alert alert-danger">Failed to remove 2FA: '+data.message+'</div>');
			}
		},
		error: function (err) {
			console.error(err);
		}
	});
});

$('#enable_2fa_btn').click(function (e) {

	$(e.currentTarget).html('<span class="fas fa-spinner fa-fw fa-pulse" aria-hidden="true"></span>').attr('disabled', 'disabled');

	$.ajax({
		url: '/ajax/actions.ajax.php?function=2fa_setup',
		type: 'GET',
		cache: false,
		headers: {'cache-control': 'no-cache'},
		contentType: false,
		processData: false,
		async: true,
		success: function (data) {
			data = JSON.parse(data);

			$(e.currentTarget).remove();

			if (data.status === 'success') {
				$(e.currentTarget).attr('disabled', 'disabled');
				$('#2fa_container').removeClass('d-none');

				$('.2fa-card-qr .qr-code').attr('src', 'data:image/png;base64,'+data.data.image_data);
				$('.2fa-card-qr .card-body pre').html(data.data.code);
				$('#confirm_2fa_btn').removeAttr('disabled');
			} else {
				console.error(data.status, data.message);
			}
		},
		error: function (err) {
			console.error(err);
		}
	});

});

$('#confirm_2fa_btn').click(function (e) {

	let code = $('#qr_confirm').val();
	if (code === '') return;

	let formData = new FormData();
	formData.append('code', code);

	$(e.currentTarget).attr('disabled', 'disabled');

	$.ajax({
		url: '/ajax/actions.ajax.php?function=2fa_confirm',
		type: 'POST',
		data: formData,
		cache: false,
		headers: {'cache-control': 'no-cache'},
		contentType: false,
		processData: false,
		async: true,
		success: function (data) {
			data = JSON.parse(data);

			if (data.status === 'success') {

				$('.confirm-2fa-result').html('<pre class="code-box">MangaDex Recovery Codes:' + "\n" + data.data.recovery.join("\n") + '</pre>')
				let q = $('#qr_confirm');
				q.val('');
				q.attr('disabled', 'disabled');

				$('#finalize_2fa_btn').removeAttr('disabled');

			} else {
				$('.confirm-2fa-errors').html(data.message);
				let q = $('#qr_confirm');
				q.css('border-color', 'red');
				q.focus();
				$(e.currentTarget).removeAttr('disabled');
			}
		},
		error: function (err) {
			console.error(err);
		}
	});

});

$('#finalize_2fa_btn').click(function (e) {
	if (confirm("Make sure to save the recovery codes to a safe place. You won't be able to view them again. Click OK to complete the 2FA setup")) {
		location.href = '/settings#change_password';
		location.reload();
	}
});

/** session management logic **/

$('.session_remove_btn').click(function (e) {
	if (!confirm("Are you sure you want to remove this session? You will have to login again on this device."))
		return;

	let session_id = $(e.currentTarget).attr('data-session-id');
	if (session_id === '') return;

	var formData = new FormData();
	formData.append('session_id', session_id);

	$(e.currentTarget).html('').attr('disabled', 'disabled');

	$.ajax({
		url: '/ajax/actions.ajax.php?function=session_destroy',
		type: 'POST',
		data: formData,
		cache: false,
		headers: {'cache-control': 'no-cache'},
		contentType: false,
		processData: false,
		async: true,
		success: function (data) {
			data = JSON.parse(data);

			if (data.status === 'success') {
				$(e.currentTarget).parent().remove();
			}
		},
		error: function (err) {
			console.error(err);
		}
	});
});

$('#clear_sessions_btn').click(function (e) {
	if (!confirm("Are you sure you want to clear your sessions? You will no longer be automatically logged in on each affected device."))
		return;

	$(e.currentTarget).html('').attr('disabled', 'disabled');

	$.ajax({
		url: '/ajax/actions.ajax.php?function=clear_sessions',
		type: 'POST',
		cache: false,
		headers: {'cache-control': 'no-cache'},
		contentType: false,
		processData: false,
		async: true,
		success: function (data) {
			data = JSON.parse(data);

			if (data.status === 'success') {
				$(e.currentTarget).parent().parent().html('');
			}
		},
		error: function (err) {
			console.error(err);
		}
	});
});

$(".group_unblock_button").click(function(event){
	if (confirm("Are you sure?")) {
		var success_msg = "<?= display_alert('success', 'Success', 'You have unblocked this group.') ?>";
		
		group_id = $(this).attr("data-group-id");
		$(this).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);
		$.ajax({
			url: "/ajax/actions.ajax.php?function=group_unblock&id="+group_id,
			success: function(data) {
				$("#message_container").html(success_msg).show().delay(3000).fadeOut();
				location.href = "/settings";
			},
			cache: false,
			contentType: false,
			processData: false
		});	
	}
	event.preventDefault();
});

$("#group_block_button").click(function(event) {
	val = document.getElementById('block_group_id').value;
	if (!val) val = 0;
	
	var success_msg = "<?= display_alert('success', 'Success', 'You have blocked this group.') ?>";
	$.ajax({
		url: "/ajax/actions.ajax.php?function=group_block&id="+val,
		success: function (data) {
			if (!data) {
				$('#message_container').html(success_msg).show().delay(3000).fadeOut();
				location.href = "/settings";
			}
			else {
				$('#message_container').html(data).show().delay(3000).fadeOut();
			}
		},
		cache: false,
		contentType: false,
		processData: false
	});	

	event.preventDefault();
});