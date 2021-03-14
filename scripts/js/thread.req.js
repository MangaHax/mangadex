$('.btn-spoiler').click(function(evt){
	evt.preventDefault();
	$(this).next('.spoiler').toggle();
});

<?php if (validate_level($user, 'member')) { ?>

$(".emoji-toggle").click(function(){
	$(this).parent().parent().next(".emojis").toggle();
});

<?= display_js_posting() ?>

$(".post_reply_button").click(function(event){
	$(".toggle").toggle();
	event.preventDefault();
});

$("#back_button").click(function(event){
	$(".toggle").toggle();
	event.preventDefault();
});

$(".post_edit_button, .cancel_post_edit_button").click(function(){
	var id = $(this).data("postId");
	$("#post_"+id).toggle();
	$("#post_edit_"+id).toggle();
});

$(".post_edit_form").submit(function(event) {
	var id = $(this).attr("id");

	var formData = new FormData($(this)[0]);

	$("#post_edit_button_"+id).html("<?= display_fa_icon("spinner", "", "fa-pulse") ?> Saving...").attr("disabled", true);

	$.ajax({
		url: "/ajax/actions.ajax.php?function=post_edit&id="+id,
		type: 'POST',
		data: formData,
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

$("[data-post-action]").click(function(event){
	var action = $(this).data("postAction");
	var post_id = $(this).data("postId") || 0;
	var value = $(this).data("value") || 0;
	if (confirm("Selected action: "+action+". Are you sure?")) {
		$.ajax({
			url: "/ajax/actions.ajax.php?function="+action+"&id="+post_id+"&value="+value,
			success: function(data) {
				$("#message_container").html(data).show().delay(1500).fadeOut();
				location.reload();
			},
			cache: false,
			contentType: false,
			processData: false
		});
	}
	event.preventDefault();
});

$('#post_history_modal').on('shown.bs.modal', function (event) {
	$('#post_history_modal .modal-body').text('Loading...');

	let post_id = $(event.relatedTarget).data('post-id');
	$.ajax({
		url: '/ajax/actions.ajax.php?function=post_history&id=' + post_id,
		type: 'GET',
		success: function (data) {
			$('#post_history_modal .modal-body').html(data);
            $("#post_history_modal .modal-body .btn-spoiler").click(function(evt){
            		evt.preventDefault();
                $(this).next(".spoiler").toggle();
            });
		},
		cache: false,
		contentType: false,
		processData: false
	});
});

<?= jquery_post("vote", $thread_id, "", "", "Voting", "You have voted.", "location.reload(); "); ?>
<?= jquery_post("post_reply", $thread_id, "", "Submit", "Submitting", "Your reply has been posted.", "location.reload(); "); ?>

<?php }
if (validate_level($user, 'pr')) { ?>


$(".edit_thread_button").click(function(event){
	$(".edit").toggle();
	event.preventDefault();
});

<?= jquery_post('edit_thread', $thread_id, '', 'Save', 'Saving', 'This thread has been edited.', 'location.reload(); ') ?>
<?= jquery_get('sticky_thread', $thread_id, '', 'Sticky', 'Stickying', 'This thread has been stickied.', 'location.reload(); ') ?>
<?= jquery_get('unsticky_thread', $thread_id, '', 'Unsticky', 'Unstickying', 'This thread has been unstickied.', 'location.reload(); ') ?>
<?= jquery_get('lock_thread', $thread_id, '', 'Lock', 'Locking', 'This thread has been locked.', 'location.reload(); ') ?>
<?= jquery_get('unlock_thread', $thread_id, '', 'Unlock', 'Unlocking', 'This thread has been unlocked.', 'location.reload(); ') ?>

<?php } ?>