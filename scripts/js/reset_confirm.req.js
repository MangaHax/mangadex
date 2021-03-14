<?php if (!$user->user_id) { ?>

$("#reset_form").submit(function(event) {
	//validate input

	var success_msg = "<div class='alert alert-success text-center' role='alert'><strong>Success:</strong> Your password has been reset.</div>";

	var formData = new FormData($(this)[0]);
	
	$("#reset_button").html("<?= display_fa_icon("spinner", "", "fa-pulse") ?> Sending password...").attr("disabled", true);
	
	$.ajax({
		url: "/ajax/actions.ajax.php?function=reset",
		type: 'POST',
		data: formData,
		success: function(data) {
			if (!data) {
				$("#message_container").html(success_msg).show().delay(1500).fadeOut();
				$("#reset_button").html("<?= display_fa_icon("sync") ?> Reset Password").attr("disabled", false);
				location.href = "/login";
			}
			else {
				$("#message_container").html(data).show().delay(1500).fadeOut();
				$("#reset_button").html("<?= display_fa_icon("sync") ?> Reset Password").attr("disabled", false);
			}
		},
		cache: false,
		contentType: false,
		processData: false
	});

	event.preventDefault();
});

<?php } ?>