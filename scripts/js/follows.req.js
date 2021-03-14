<?= jquery_post("import", 0, "upload", "Import", "Importing", "Your JSON has been imported.", "location.href = '/follows';") ?>

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


$(".chapter_mark_unread_button").click(function(event){
	var chapter_id = $(this).attr('data-id');
	$.ajax({
		url: "/ajax/actions.ajax.php?function=chapter_mark_unread&id="+chapter_id,
		success: function() {
			$("#marker_"+chapter_id).html("<i class='fas fa-eye-slash fa-fw'></i>");
			$("#marker_"+chapter_id).removeClass("chapter_mark_unread_button");
			$("#marker_"+chapter_id).addClass("grey").addClass("chapter_mark_unread_button");
		},
		cache: false,
		contentType: false,
		processData: false
	});	

	event.preventDefault();
});

$(".chapter_mark_read_button").click(function(event){
	var chapter_id = $(this).attr('data-id');
	$.ajax({
		url: "/ajax/actions.ajax.php?function=chapter_mark_read&id="+chapter_id,
		success: function() {
			$("#marker_"+chapter_id).html("<i class='fas fa-eye fa-fw'></i>");
			$("#marker_"+chapter_id).removeClass("grey").removeClass("chapter_mark_read_button");
			$("#marker_"+chapter_id).addClass("chapter_mark_unread_button");
		},
		cache: false,
		contentType: false,
		processData: false
	});	

	event.preventDefault();
});

$(".toggle_mass_edit_button, .cancel_mass_edit_button").click(function(event){
	id = $(this).attr("id"); 
	$("#toggle_mass_edit_"+id).toggle();
	$("#chapter_"+id).toggle();
	event.preventDefault();
});	

$(".mass_edit_form").submit(function(event) {
	
	id = $(this).attr("id"); 
	
	var formData = new FormData($(this)[0]);
	
	$("#mass_edit_button_"+id).html("<?= display_fa_icon("spinner", "", "fa-pulse") ?>").attr("disabled", true);
	
	$.ajax({
		url: "/ajax/actions.ajax.php?function=chapter_edit&id="+id,
		type: 'POST',
		data: formData,
		success: function(data) {
			$("#mass_edit_button_"+id).html("<?= display_fa_icon("check", "", "fa-fw") ?>").attr("disabled", false);
		},
		cache: false,
		contentType: false,
		processData: false
	});
	
	event.preventDefault();
});	

$(".mass_edit_delete_button").click(function(event){
	id = $(this).attr("id");
	$(this).html("<?= display_fa_icon("spinner", "", "fa-pulse") ?>").attr("disabled", true);
	$.ajax({
		url: "/ajax/actions.ajax.php?function=chapter_delete&id="+id,
		success: function(data) {
			$("#toggle_mass_edit_"+id).remove();
		},
		cache: false,
		contentType: false,
		processData: false
	});	
	event.preventDefault();
});