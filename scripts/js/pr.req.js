<?php
switch ($mode) {
	case 'email_search':
	?>
		$("#user_search_form").submit(function(event) {
			var email = encodeURIComponent($("#email").val());
			var username = encodeURIComponent($("#username").val());
			$("#search_button").html("<?= display_fa_icon('spinner', '', 'fa-pulse') ?> Searching...").attr("disabled", true);
			location.href = "/pr/email_search?username="+username+"&email="+email;
			event.preventDefault();
		});
	<?php
		break;

	case 'banners':
	default:
	?>
		$("#banner_upload_form").submit(function(evt) {
			evt.preventDefault();
			$("#banner_upload_button").html("<span class='fas fa-spinner fa-pulse' aria-hidden='true' title=''></span> Uploading...").attr("disabled", true);

			const success_msg = "<div class='alert alert-success text-center' role='alert'><strong>Success:</strong> Your banner has been uploaded.</div>";
			const error_msg = "<div class='alert alert-warning text-center' role='alert'><strong>Warning:</strong> Something went wrong with your upload.</div>";
			const form = this;
			const formdata = new FormData(form);
			$.ajax({
				url: "/ajax/actions.ajax.php?function=banner_upload",
				type: 'POST',
				data: formdata,
				cache: false,
				contentType: false,
				processData: false,
				success: function (data) {
					if (!data) {
						$("#message_container").html(success_msg).show().delay(3000).fadeOut();
						location.reload();
					}
					else {
						$("#banner_upload_button").html("<?= display_fa_icon('upload') ?> Upload").attr("disabled", false);
						$("#message_container").html(data).show().delay(5000).fadeOut();
					}
				},
				error: function(err) {
					console.error(err);
					$("#banner_upload_button").html("<?= display_fa_icon('upload') ?> Upload").attr("disabled", false);
					$("#message_container").html(error_msg).show().delay(5000).fadeOut();
				}
			});
		});

		$(".toggle_banner_edit_button, .cancel_banner_edit_button").click(function(evt) {
			evt.preventDefault();
			let id = $(this).attr("data-toggle");
			$("#banner_edit_" + id).toggle();
			$("#banner_" + id).toggle();
		});

		$(".banner_edit_form").submit(function(evt) {
			evt.preventDefault();
			
			const id = $(this).attr("data-banner-id");
			const success_msg = "<div class='alert alert-success text-center' role='alert'><strong>Success:</strong> Your banner has been edited.</div>";
			const error_msg = "<div class='alert alert-warning text-center' role='alert'><strong>Warning:</strong> Something went wrong with your edit.</div>";
			const formData = new FormData($(this)[0]);
			$("#banner_edit_button_"+id).html("<?= display_fa_icon('spinner', '', 'fa-pulse') ?>").attr("disabled", true);
			$.ajax({
				url: "/ajax/actions.ajax.php?function=banner_edit&banner_id=" + id,
				type: 'POST',
				data: formData,
				cache: false,
				contentType: false,
				processData: false,
				success: function(data) {
					if (!data) {
						$("#message_container").html(success_msg).show().delay(3000).fadeOut();
					}
					else {
						$("#message_container").html(data).show().delay(10000).fadeOut();
					}
					$("#banner_edit_button_" + id).html("<?= display_fa_icon('pencil-alt', '', 'fa-fw') ?>").attr("disabled", false);
				},
				error: function(err) {
					console.error(err);
					$("#banner_edit_button_" + id).html("<?= display_fa_icon('pencil-alt', '', 'fa-fw') ?>").attr("disabled", false);
					$("#message_container").html(error_msg).show().delay(10000).fadeOut();
				}
			});
		});
	
	<?php
		break;
}
?>