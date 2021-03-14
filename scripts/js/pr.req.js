$("#user_search_form").submit(function(event) {
	var email = encodeURIComponent($("#email").val());
	var username = encodeURIComponent($("#username").val());
	$("#search_button").html("<?= display_fa_icon('spinner', '', 'fa-pulse') ?> Searching...").attr("disabled", true);
	location.href = "/pr/?username="+username+"&email="+email;
	event.preventDefault();
});