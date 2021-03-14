$(".title_mode").click(function() {
	val = $(this).attr('id');
	$.ajax({
		url: "/ajax/actions.ajax.php?function=set_mangas_view&mode="+val,
		success: function (data) {
			$("#message_container").html(data).show().delay(3000).fadeOut();
			location.reload();
		},
		cache: false,
		contentType: false,
		processData: false
	});

	event.preventDefault();
});

<?php if (validate_level($user, 'member')) { ?>
$(".manga_rating_button").click(function(event){
	var rating = $(this).attr('id');
	var manga_id = $(this).attr('data-manga-id');
	$.ajax({
		url: "/ajax/actions.ajax.php?function=manga_rating&id="+manga_id+"&rating="+rating,
		success: function(data) {
			$("#message_container").html(data).show().delay(3000).fadeOut();
			location.reload();
		},
		cache: false,
		contentType: false,
		processData: false
	});

	event.preventDefault();
});

$(".manga_follow_button").click(function(event){
	var type = $(this).attr('id');
	var manga_id = $(this).attr('data-manga-id');
	$.ajax({
		url: "/ajax/actions.ajax.php?function=manga_follow&id="+manga_id+"&type="+type,
		success: function(data) {
			$("#message_container").html(data).show().delay(3000).fadeOut();
			location.reload();
		},
		cache: false,
		contentType: false,
		processData: false
	});

	event.preventDefault();
});

$(".manga_unfollow_button").click(function(event){
	if (confirm("Are you sure? This will remove all the 'read chapter' markers.")) {
		var type = $(this).attr('id');
		var manga_id = $(this).attr('data-manga-id');
		$.ajax({
			url: "/ajax/actions.ajax.php?function=manga_unfollow&id="+manga_id+"&type="+type,
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
<?php } ?>