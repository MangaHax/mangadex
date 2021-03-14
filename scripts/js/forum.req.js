<?php if (validate_level($user, $threads->start_thread_level)) { ?>

<?= display_js_posting() ?>

$(".emoji-toggle").click(function(){
	$(this).parent().parent().next(".emojis").toggle();
});	

$(".new_thread_button, #back_button").click(function(event){
	$(".toggle").toggle();
	event.preventDefault();
});	

$("#poll_button").click(function(event){
	$(".poll-div").toggle();
	event.preventDefault();
});

<?= jquery_post('start_thread', $forum_id, 'pencil-alt', 'Submit', 'Submitting', 'Your thread has been posted.', 'location.reload();'); ?>

<?php } 

if (validate_level($user, 'pr')) { ?>

<?= jquery_post('delete_threads', $forum_id, '', '', 'Deleting', 'The threads have been deleted.', 'location.reload();'); ?>

<?php } ?>