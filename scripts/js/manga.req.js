<?php if (isset($manga->manga_id)) {	?>

//ratings_histogram
$("#histogram_toggle").click(function(){
	$("#histogram_div").toggle();
});

var ctx = $("#ratings_histogram");
var ratings =  JSON.parse(ctx.attr('data-json'));

var keys = []
var values = []
for (var i = 10; i >= 1; --i) {
    keys.push(i)
    values.push(0)
}
for (var i = 0; i < ratings.length; ++i) {
    values[10 - ratings[i]]++
}


Chart.defaults.global.defaultFontSize = 11;
Chart.defaults.global.legend.display = false;
var myChart = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: keys,
        datasets: [{
            data: values,
			backgroundColor: [
                'rgba(255, 99, 132, 0.5)',
                'rgba(54, 162, 235, 0.5)',
                'rgba(255, 206, 86, 0.5)',
                'rgba(75, 192, 192, 0.5)',
                'rgba(153, 102, 255, 0.5)',
				'rgba(255, 99, 132, 0.5)',
                'rgba(54, 162, 235, 0.5)',
                'rgba(255, 206, 86, 0.5)',
                'rgba(75, 192, 192, 0.5)',
                'rgba(153, 102, 255, 0.5)'
            ],
            borderWidth: 0
        }]
    },
    options: {
		title: {
            display: true,
            text: 'Distribution of Ratings'
        },
        scales: {
			xAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});

lightbox.option({
  'wrapAround': true
});

$('.btn-spoiler').click(function(evt){
	evt.preventDefault();
	$(this).next(".spoiler").toggle();
});


<?php if ((validate_level($user, 'contributor') && !$manga->manga_locked) || validate_level($user, 'gmod')) { ?>

$("#add_link_button").click(function(event){
	$("#links").append("<div style='margin-bottom: 5px;' class='input-group'><div class='input-group-btn'><select class='form-control selectpicker z-index-auto' name='link_type[]' data-width='160px'><?php foreach (MANGA_EXT_LINKS as $type => $name) { ?><option value='<?= $type ?>'><?= $name ?></option><?php } ?></select></div><input type='text' class='form-control' placeholder='ID/slug/URL' name='link_id[]'><span class='input-group-btn'><button class='btn btn-danger delete_link_button'><?= display_fa_icon('times') ?></button></span></div>");
	$('.selectpicker').selectpicker('show');
	event.preventDefault();
});

$("#links").on('click', '.delete_link_button', function(event){
	event.preventDefault();
	$(this).parent().parent().remove();
});

$("#add_relation_button").click(function(event){
	$("#relation_entries").append("<div style='margin-bottom: 5px;' class='input-group'><div class='input-group-btn'><select class='form-control selectpicker z-index-auto' name='relation_type[]' data-width='150px'><?php foreach ($relation_types as $relation) { ?><option value='<?= $relation->relation_id ?>'><?= $relation->relation_name ?></option><?php } ?></select></div><input type='number' class='form-control' placeholder='Related manga ID' name='related_manga_id[]'><span class='input-group-btn'><button class='btn btn-danger delete_relation_button'><?= display_fa_icon('times') ?></button></span></div>");
	$('.selectpicker').selectpicker('show');
	event.preventDefault();
});

$("#relation_entries").on('click', '.delete_relation_button', function(event){
	event.preventDefault();
	$(this).parent().parent().remove();
});

$("#edit_button").click(function(event){
	$(".edit").toggle();
	desc = $('#manga_description').val();
    desc = desc.replace(/<br \/>/g, '\n');
    desc = desc.replace(/•/g, '[*]');
	desc = desc.replace(/http(?:s)?:\/\/(?:www\.)?(?:bato\.to|batoto\.net)\/comic(?:\/_)?\/(?:comics\/)?[a-zA-Z0-9%\-]+-r([0-9]+)/g, 'https://mangadex.org/manga/$1');
    $('#manga_description').val(desc);
	event.preventDefault();
});
$("#cancel_edit_button").click(function(event){
	$(".edit").toggle();
	event.preventDefault();
});

<?= jquery_post("manga_edit", $id, "pencil-alt", "Save", "Saving", "This title has been edited.", "location.href = '/title/$id/" . slugify($manga->manga_name) . "';"); ?>

<?php } ?>

<?php if (validate_level($user, 'member')) { ?>

$(".emoji-toggle").click(function(){
	$(this).parent().parent().next(".emojis").toggle();
});

$("#increment_volume").click(function() {
	var title_id = $(this).attr('data-title-id');
	$.ajax({
		url: "/ajax/actions.ajax.php?function=increment_volume&id="+title_id,
		success: function() {
			location.reload();
		},
		cache: false,
		contentType: false,
		processData: false
	});
});

$("#increment_chapter").click(function() {
	var title_id = $(this).attr('data-title-id');
	$.ajax({
		url: "/ajax/actions.ajax.php?function=increment_chapter&id="+title_id,
		success: function() {
			location.reload();
		},
		cache: false,
		contentType: false,
		processData: false
	});
});

$("#edit_progress_form").submit(function(event) {
	id = $(this).attr("data-title-id");
	volume = $("#volume").val();
	chapter = $("#chapter").val();

	var formData = new FormData($(this)[0]);

	$("#edit_progress_button").html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);

	$.ajax({
		url: "/ajax/actions.ajax.php?function=edit_progress&id="+id,
		type: 'POST',
		data: formData,
		success: function(data) {
			$("#edit_progress_button").html("<?= display_fa_icon("pencil-alt") ?>").attr("disabled", false);
			$("#current_volume").html(volume);
			$("#current_chapter").html(chapter);
			$(".reading_progress").toggle();
		},
		cache: false,
		contentType: false,
		processData: false
	});

	event.preventDefault();
});

$("#edit_progress, #cancel_edit_progress").click(function() {
	$(".reading_progress").toggle();
});

<?= js_display_file_select(); ?>

<?= jquery_post("manga_cover_upload", $id, "upload", "Upload", "Uploading", "Cover uploaded.", "location.href = '/title/$id/" . slugify($manga->manga_name) . "/covers';"); ?>

<?= jquery_post('manga_report', $id, 'flag', 'Report', 'Reporting', 'Your report has been submitted.', "$('#manga_report_modal').modal('hide');"); ?>

<?= display_js_posting() ?>

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

$(".manga_rating_button").click(function(event){
	var rating = $(this).attr('id');
	var manga_id = $(this).attr('data-manga-id');
	$.ajax({
		url: "/ajax/actions.ajax.php?function=manga_rating&id="+manga_id+"&rating="+rating,
		success: function(data) {
			$("#message_container").html(data).show().delay(3000).fadeOut();
			location.href = "/title/<?= $id ?>";
		},
		cache: false,
		contentType: false,
		processData: false
	});

	event.preventDefault();
});

$("#upload_button").click(function(event){
	location.href = "/upload/"+<?= $manga->manga_id ?>;
	event.preventDefault();
});

$(".manga_follow_button").click(function(event){
	var type = $(this).attr('id');
	var manga_id = $(this).attr('data-manga-id');
	$.ajax({
		url: "/ajax/actions.ajax.php?function=manga_follow&id="+manga_id+"&type="+type,
		success: function(data) {
			$("#message_container").html(data).show().delay(3000).fadeOut();
			location.href = "/title/<?= $id ?>";
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
				location.href = "/title/<?= $id ?>";
			},
			cache: false,
			contentType: false,
			processData: false
		});
	}

	event.preventDefault();
});

<?= jquery_post("start_empty_thread", 11, '', "Start comment thread", "Starting", "You have started a comment thread.", "location.reload();"); ?>

<?= jquery_post("post_reply", $manga->thread_id, '', "Comment", "Commenting", "Your comment has been submitted.", "location.reload();"); ?>

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

	$("#mass_edit_button_"+id).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);

	$.ajax({
		url: "/ajax/actions.ajax.php?function=chapter_edit&id="+id,
		type: 'POST',
		data: formData,
		success: function(data) {
			$("#mass_edit_button_"+id).html("<?= display_fa_icon("check", '', "fa-fw") ?>").attr("disabled", false);
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

<?= jquery_get("manga_lock", $id, '', "Lock", "Locking", "You have locked this manga.", "location.reload();") ?>

<?= jquery_get("manga_unlock", $id, '', "Unlock", "Unlocking", "You have unlocked this manga.", "location.reload();") ?>

<?php } ?>

<?php if (validate_level($user, 'gmod')) { ?>
$(".manga_cover_delete").click(function(event){
	if (confirm("Are you sure?")) {
		manga_id = $(this).attr("data-manga-id");
		volume = $(this).attr("data-volume");
		$(this).html("<?= display_fa_icon("spinner", '', "fa-pulse") ?>").attr("disabled", true);
		$.ajax({
			url: "/ajax/actions.ajax.php?function=manga_cover_delete&manga_id="+manga_id+"&volume="+volume,
			success: function(data) {
				$("#volume_"+volume).remove();
			},
			cache: false,
			contentType: false,
			processData: false
		});
	}
	event.preventDefault();
});

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

<?php } ?>

<?php if (validate_level($user, 'gmod')) { ?>
$("#delete_button").click(function(event){
	if (confirm("Are you sure?")) {
		$.ajax({
			url: "/ajax/actions.ajax.php?function=manga_delete&id=<?= $id ?>",
			success: function(data) {
				$("#message_container").html(data).show().delay(1500).fadeOut();
				location.href = "/titles";
			},
			cache: false,
			contentType: false,
			processData: false
		});
	}
	event.preventDefault();
});

$("#admin_edit_manga_form").submit(function(event) {
	//validate input

	var formData = new FormData($(this)[0]);

	$("#admin_edit_manga_button").html("<?= display_fa_icon("spinner", '', "fa-pulse") ?> Saving...").attr("disabled", true);

	$.ajax({
		url: "/ajax/actions.ajax.php?function=admin_edit_manga&id=<?= $id ?>",
		type: 'POST',
		data: formData,
		success: function (data) {
			$("#admin_edit_manga_button").html("<?= display_fa_icon("edit", '', "fa-fw") ?> Save").attr("disabled", false);
			$("#message_container").html(data).show().delay(1500).fadeOut();
		},
		cache: false,
		contentType: false,
		processData: false
	});
	event.preventDefault();
});
<?php } ?>
<?php } ?>