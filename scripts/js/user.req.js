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

	<?php
	print jquery_get("friend_add", $id, '', "Add friend", "Adding", "You have sent a friend request to $uploader->username.", "location.reload();");
	print jquery_get("friend_accept", $id, '', "Accept request", "Accepting", "You have accepted the friend request from $uploader->username.", "location.reload();");
	print jquery_get("friend_remove", $id, '', "Remove friend", "Removing", "You have removed $uploader->username as a friend.", "location.reload();");

	print jquery_get("user_block", $id, '', "Block user", "Blocking", "You have blocked $uploader->username.", "location.reload();");
	print jquery_get("user_unblock", $id, '', "Unblock user", "Unblocking", "You have unblocked $uploader->username.", "location.reload();");

?>

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


    $('#notes-button').click(function(event) {
        $('#notes-modal').modal();
    });

    $('#notes-save').click(function(event) {
        var note = $('#note-field').val();

        $.ajax({
            url: '/ajax/actions.ajax.php?function=set_user_note',
            type: 'POST',
            data: {
                user_id: <?= $uploader->user_id ?>,
                note: note
            },
            success: function(data) {
                if (data !== "") {
                    $('#message_container').html(`<div class="alert alert-danger">${data}</div>`).show().delay(5000).fadeOut();
                    return;
                }

                $('#notes-modal').modal('hide');
                $('#message_container').html(`<div class="alert alert-success">Success: User note has been ${note !== '' ? 'set' : 'removed'}</div>`).show().delay(5000).fadeOut();
            },
            error: function() {
                $('#message_container').html(`<div class="alert alert-danger">Error setting the user note. Sorry about that :/</div>`).show().delay(5000).fadeOut();
            },
            cache: false
        });
    });

<?php } ?>

<?php if (validate_level($user, 'pr')) { ?>

	$(".post_edit_button, .cancel_post_edit_button").click(function(){
		var id = $(this).data("postId");
		$("#post_"+id).toggle();
		$("#post_edit_"+id).toggle();
	});

	$(".post_edit_form").submit(function(event) {
		var id = $(this).attr("id");

		var formData = new FormData($(this)[0]);

		$("#post_edit_button_"+id).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?> Saving...").attr("disabled", true);

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

	$(".toggle_mass_edit_button, .cancel_mass_edit_button").click(function(event){
		id = $(this).attr("id");
		$("#toggle_mass_edit_"+id).toggle();
		$("#chapter_"+id).toggle();
		event.preventDefault();
	});

	$(".mass_edit_form").submit(function(event) {

		id = $(this).attr("id");

		var formData = new FormData($(this)[0]);

		$("#mass_edit_button_"+id).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);

		$.ajax({
			url: "/ajax/actions.ajax.php?function=chapter_edit&id="+id,
			type: 'POST',
			data: formData,
			success: function(data) {
				$("#mass_edit_button_"+id).html("<?= display_fa_icon("check") ?>").attr("disabled", false);
			},
			cache: false,
			contentType: false,
			processData: false
		});

		event.preventDefault();
	});

	$(".mass_edit_delete_button").click(function(event){
		id = $(this).attr("id");
		$(this).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);
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

	<?php
	print jquery_get("unban_user", $id, '', "Unban", "Unbanning", "You have unbanned $uploader->username.", "location.reload();");
	print jquery_get("ban_user", $id, '', "Ban", "Banning", "You have banned $uploader->username.", "location.reload();");
} ?>

<?php if (validate_level($user, 'mod')) { ?>
<?= jquery_get("override", $id, '', "Override", "Overriding", "Done.", "location.reload();"); ?>

$(".undelete_button").click(function(event){
	id = $(this).attr("data-id");
	$(this).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);
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

$('#user_restriction_form').submit(function(event) {

	var formData = new FormData($(this)[0]);

	var success_msg = "<div class='alert alert-success text-center' role='alert'><strong>Success:</strong> Restriction has been applied.</div>";

	$('#user_restriction_button').html("<span class='fas fa-spinner fa-fw fa-pulse' aria-hidden='true' ></span> Saving...").attr('disabled', true);

	$.ajax({
		url: '/ajax/actions.ajax.php?function=mod_user_restriction',
		type: 'POST',
		data: formData,
		cache: false,
		headers: {'cache-control': 'no-cache'},
		contentType: false,
		processData: false,
		async: true,
		success: function (data) {
			if (!data) {
				$('#message_container').html(success_msg).show().delay(3000).fadeOut();
				location.reload();
			}
			else {
				$('#message_container').html(data).show().delay(3000).fadeOut();
			}
		}
	});

	event.preventDefault();
});

$('button.remove-restriction').click(function (event) {
	var id = $(event.currentTarget).attr('data-id');

	$.ajax({
		url: '/ajax/actions.ajax.php?function=mod_lift_user_restriction',
		type: 'POST',
		data: {
			'restriction_id': id,
			'target_user_id': $('div.user-restriction').attr('data-id')
		},
		cache: false,
		async: true,
		success: function (data) {
			if (data) {
                $('#message_container').html(data).show().delay(3000).fadeOut();
			}
            $(event.currentTarget).closest("tr").remove();
		}
	});

});

$('button#btn-show-past-restrictions').click(function (event) {
	$('table.past-user-restrictions').toggle();
});

$('#expiration_permanent').click(function (event) {
	var val = $('#expiration_permanent').is(":checked");
    $('#expiration_reltime').prop('disabled', val);
    $('#expiration_relstep').prop('disabled', val);
});

<?= print jquery_post("admin_edit_user", $id, '', "Save", "Saving", "User $id has been edited.", "location.reload();") ?>


<?php } ?>

<?php if (validate_level($user, 'admin')) { ?>
	function randomString(length) {
		var text = '';
		var possible = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789";
		for(i = 0; i < length; i++) {
			text += possible.charAt(Math.floor(Math.random() * possible.length));
		}
		return text;
	}

	$("#generate_pass_button").click(function(event){
		document.getElementById("new_pass").value = randomString(16);
		event.preventDefault();
	});

	/** 2fa logic **/
	$('#remove_2fa_btn').click(function (e) {
		if (!confirm("Are you sure you want to remove 2FA from this account?"))
			return;

		var formData = new FormData();
		formData.append('confirm', 1);
		formData.append('user_id', $(e.currentTarget).attr('data-user-id'));

		$(e.currentTarget).html('<span class="fas fa-spinner fa-fw fa-pulse" aria-hidden="true"></span>').attr('disabled', 'disabled');

		$.ajax({
			url: '/ajax/actions.ajax.php?function=2fa_remove',
			type: 'POST',
			data: formData,
			cache: false,
			headers: {'cache-control': 'no-cache'},
			contentType: false,
			processData: false,
			async: true,
			success: function (data) {
				location.reload();
			},
			error: function (err) {
				console.error(err);
			}
		});
	});

	/** Session handling **/

	$('#clear_sessions_btn').click(function (e) {
		if (!confirm("Are you sure you want to clear your sessions? You will no longer be automatically logged in on each affected device."))
			return;

		let user_id = $(e.currentTarget).attr('data-user-id');
		if (user_id === '') return;

		var formData = new FormData();
		formData.append('user_id', user_id);

		$(e.currentTarget).attr('disabled', 'disabled');

		$.ajax({
			url: '/ajax/actions.ajax.php?function=clear_sessions',
			type: 'POST',
			data: formData,
			cache: false,
			headers: {'cache-control': 'no-cache'},
			contentType: false,
			processData: false,
			async: true,
			success: function (data) {
				data = JSON.parse(data);

				if (data.status === 'success') {
					$('#message_container').html("<div class=\"alert alert-success text-center\" role=\"alert\"><strong>Result:</strong> "+data.message+"</div>").show().delay(3000).fadeOut();
				}
			},
			error: function (err) {
				console.error(err);
			}
		});
	});

<?php } ?>