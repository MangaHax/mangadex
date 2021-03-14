<?php 
if ($user->user_id) {
	print jquery_post("request_client", 0, "envelope", "Request", "Requesting", "You have requested a client, pending approval.", "");
	print jquery_post("turn_on", 0, "envelope", "Save", "Saving", "Saved.", "");
} 
?>
<?php if (validate_level($user, 'admin')) { ?>
$(".approve_button").click(function(event){
	if (confirm("Are you sure?")) {
		var client_id = $(this).attr('data-id');
		$.ajax({
			url: "/ajax/actions.ajax.php?function=client_approve&client_id="+client_id,
			success: function(data) {
				$("#message_container").html(data).show().delay(3000).fadeOut();
				location.reload();
			},
			cache: false,
			contentType: false,
			processData: false
		});	
	}

	event.preventDefault();
});

$(".reject_button").click(function(event){
	if (confirm("Are you sure?")) {
		var client_id = $(this).attr('data-id');
		$.ajax({
			url: "/ajax/actions.ajax.php?function=client_reject&client_id="+client_id,
			success: function(data) {
				$("#message_container").html(data).show().delay(3000).fadeOut();
				location.reload();
			},
			cache: false,
			contentType: false,
			processData: false
		});	
	}

	event.preventDefault();
});

$(".rotate_button").click(function(event){
	if (confirm("Are you sure?")) {
		var client_id = $(this).attr('data-id');
		$.ajax({
			url: "/ajax/actions.ajax.php?function=client_rotate&client_id="+client_id,
			success: function(data) {
				$("#message_container").html(data).show().delay(3000).fadeOut();
				//location.reload();
			},
			cache: false,
			contentType: false,
			processData: false
		});	
	}

	event.preventDefault();
});
<?php } ?>