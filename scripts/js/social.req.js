<?php if (validate_level($user, 'member')) { ?>

$(".friend_accept_button").click(function(event){
	
	var success_msg = "<?= display_alert('success', 'Success', 'You have accepted the friend request.') ?>";
	
	id = $(this).attr("id");
	$(this).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);
	$.ajax({
		url: "/ajax/actions.ajax.php?function=friend_accept&id="+id,
		success: function(data) {
			$("#message_container").html(success_msg).show().delay(3000).fadeOut();
			location.reload();
		},
		cache: false,
		contentType: false,
		processData: false
	});	
	event.preventDefault();
});

$(".friend_remove_button").click(function(event){
	if (confirm("Are you sure?")) {
		var success_msg = "<?= display_alert('success', 'Success', 'You have removed this user.') ?>";
		
		id = $(this).attr("id");
		$(this).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);
		$.ajax({
			url: "/ajax/actions.ajax.php?function=friend_remove&id="+id,
			success: function(data) {
				$("#message_container").html(success_msg).show().delay(3000).fadeOut();
				location.reload();
			},
			cache: false,
			contentType: false,
			processData: false
		});	
	}
	event.preventDefault();
});

$(".user_unblock_button").click(function(event){
	
	var success_msg = "<?= display_alert('success', 'Success', 'You have unblocked this user.') ?>";
	
	id = $(this).attr("id");
	$(this).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);
	$.ajax({
		url: "/ajax/actions.ajax.php?function=user_unblock&id="+id,
		success: function(data) {
			$("#message_container").html(success_msg).show().delay(3000).fadeOut();
			location.reload();
		},
		cache: false,
		contentType: false,
		processData: false
	});	
	event.preventDefault();
});
<?php } ?>