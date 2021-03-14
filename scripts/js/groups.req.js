$("#group_search_form").submit(function(event) {

	var group_name = encodeURIComponent($("#group_name").val());

	$("#search_button").html("<?= display_fa_icon("spinner", "", "fa-pulse") ?> Searching...").attr("disabled", true);
	
	location.href = "/groups/1/1/"+group_name;

	event.preventDefault();

});