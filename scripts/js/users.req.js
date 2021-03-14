$("#user_search_form").submit(function(event) {
	var username = encodeURIComponent($("#username").val());
	$("#search_button").html("<?= display_fa_icon('spinner', '', 'fa-pulse') ?> Searching...").attr("disabled", true);
	location.href = "/users/0/1/"+username;
	event.preventDefault();
});