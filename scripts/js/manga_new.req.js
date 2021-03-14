<?php
if ($user->user_id) {
	print js_display_file_select();

	print jquery_post("manga_add", 0, "plus-circle", "Add title", "Adding...", "This title has been added.", "location.href = '/title/$next_manga_id';");
}
?>

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