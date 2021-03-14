<?php
	// Abort if group not found
	if (!isset($group->group_id)) {
		return;
	}
?>

<?= jquery_get("group_unlike", $id, "", "Unlike", "Unliking", "You have unliked $group->group_name.", "location.reload();") ?>
<?= jquery_get("group_like", $id, "", "Like", "Liking", "You have liked $group->group_name.", "location.reload();") ?>

$('.btn-spoiler').click(function(evt){
	evt.preventDefault();
	$(this).next('.spoiler').toggle();
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

<?php if (validate_level($user, 'member')) { ?>
$(".emoji-toggle").click(function(){
	$(this).parent().parent().next(".emojis").toggle();
});

<?= jquery_get("group_unblock", $id, "", "Unblock", "Unblocking", "You have unblocked $group->group_name.", "location.reload();") ?>
<?= jquery_get("group_block", $id, "", "Block", "Blocking", "You have blocked $group->group_name.", "location.reload();") ?>

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

<?= display_js_posting() ?>

<?= jquery_get("group_follow", $id, "", "Follow", "Following", "You have followed this group.", "location.reload();") ?>
<?= jquery_get("group_unfollow", $id, "", "Unfollow", "Unfollowing", "You have unfollowed this group.", "location.reload();") ?>

<?= jquery_post("start_empty_thread", 14, "", "Start comment thread", "Starting", "You have started a comment thread.", "location.reload();"); ?>

<?= jquery_post("post_reply", $group->thread_id, "", "Comment", "Commenting", "Your comment has been submitted.", "location.reload();"); ?>

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

$("#leave_button").click(function(event){
	if (confirm("Are you sure you want to leave this group?")) {
        id = $(this).attr("data-id");
        $(this).html("<?= display_fa_icon("spinner", "", "fa-pulse") ?>").attr("disabled", true);
        $.ajax({
            url: "/ajax/actions.ajax.php?function=group_leave&group_id="+id,
            success: function(data) {
                $("#message_container").html(data).show().delay(3000).fadeOut();
                setTimeout(location.reload.bind(location), 3000);
            },
            cache: false,
            contentType: false,
            processData: false
        });
	}
	event.preventDefault();
});

<?php } ?>

<?php if (validate_level($user, 'gmod')) { ?>
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

<?php } ?>

<?php if (validate_level($user, 'gmod') || $group->group_leader_id == $user->user_id || in_array($user->username, $group_members_array)) { //only display for relevant people ?>
$("#edit_button, #cancel_edit_button").click(function(event){
	$(".edit").toggle();
	event.preventDefault();
});

$("#edit_members_button, #cancel_edit_members_button").click(function(event){
	$(".edit-members").toggle();
	event.preventDefault();
});

<?= js_display_file_select(); ?>

<?= jquery_post("group_edit", $id, "pencil-alt", "Save", "Saving", "This group has been edited.", "location.href = '/group/$id/" . slugify($group->group_name) . "';"); ?>

$("#edit_group_members_form").submit(function(event) {

	if (!confirm('Are you sure?')) return;

	//validate input

	var formData = new FormData($(this)[0]);

	$("#save_edit_members_button").html("<?= display_fa_icon("spinner", "", "fa-pulse") ?> Saving...").attr("disabled", true);

	$.ajax({
		url: "/ajax/actions.ajax.php?function=group_add_member&id=<?= $id ?>",
		type: 'POST',
		data: formData,
		success: function (data) {
			$("#message_container").html(data).show().delay(1500).fadeOut();
			location.href = "/group/<?= $id ?>";
		},
		cache: false,
		contentType: false,
		processData: false

	});

	event.preventDefault();
});

$(".group_delete_member").click(function(event){
	user_id = $(this).attr("id");
	$.ajax({
		url: "/ajax/actions.ajax.php?function=group_delete_member&group_id=<?= $id ?>&user_id="+user_id,
		type: 'GET',
		success: function (data) {
			$("#message_container").html(data).show().delay(1500).fadeOut();
			location.href = "/group/<?= $id ?>";
		},
		cache: false,
		contentType: false,
		processData: false

	});
	event.preventDefault();
});

<?php } ?>

<?php if($memcached->get("group_{$id}_invite_{$user->user_id}") == "pending"){ ?>
	$("#accept_group_invite_button").click(function(event){
		$.ajax({
			url: "/ajax/actions.ajax.php?function=group_accept_invite&id=<?= $id ?>",
			success: function (data) {
				$("#message_container").html(data).show().delay(1500).fadeOut();
				location.href = "/group/<?= $id ?>";
			},
			cache: false,
			contentType: false,
			processData: false

		});
		event.preventDefault();
	});

	$("#reject_group_invite_button").click(function(event){
		$.ajax({
			url: "/ajax/actions.ajax.php?function=group_reject_invite&id=<?= $id ?>",
			success: function (data) {
				$("#message_container").html(data).show().delay(1500).fadeOut();
				location.href = "/group/<?= $id ?>";
			},
			cache: false,
			contentType: false,
			processData: false

		});
		event.preventDefault();
	});
<?php } ?>

<?php if (validate_level($user, 'gmod')) { ?>
$(".undelete_button").click(function(event){
	id = $(this).attr("data-id");
	$(this).html("<?= display_fa_icon("spinner", "", "fa-pulse") ?>").attr("disabled", true);
	$.ajax({
		url: "/ajax/actions.ajax.php?function=chapter_undelete&id="+id,
		success: function(data) {
			$("#chapter_"+id).remove();
		},
		cache: false,
		contentType: false,
		processData: false
	});
	event.preventDefault();
});

$(".unavailable_button").click(function(event){
	id = $(this).attr("data-id");
	$(this).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);
	$.ajax({
		url: "/ajax/actions.ajax.php?function=chapter_unavailable&id="+id,
		success: function(data) {
			$("#chapter_"+id).remove();
		},
		cache: false,
		contentType: false,
		processData: false
	});
	event.preventDefault();
});

$(".purge_button").click(function(event){
	id = $(this).attr("data-id");
	$(this).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);
	$.ajax({
		url: "/ajax/actions.ajax.php?function=chapter_purge&id="+id,
		success: function(data) {
			$("#chapter_"+id).remove();
		},
		cache: false,
		contentType: false,
		processData: false
	});
	event.preventDefault();
});
<?php } ?>

<?php if (validate_level($user, 'gmod')) { ?>
$("#delete_button").click(function(event){
	if (confirm("Are you sure?")) {
		$.ajax({
			url: "/ajax/actions.ajax.php?function=group_delete&id=<?= $id ?>",
			success: function(data) {
				$("#message_container").html(data).show().delay(100).fadeOut();
				location.href = "/groups";
			},
			cache: false,
			contentType: false,
			processData: false
		});
	}
	event.preventDefault();
});

<?= jquery_post("admin_edit_group", $id, "edit", "Save", "Saving", "You have edited $group->group_name.", "location.href = '/group/$id/" . slugify($group->group_name ?? '') . "';") ?>

<?php } ?>