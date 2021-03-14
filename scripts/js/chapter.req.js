<?php if ($mode != 'chapter' || $user->reader) { ?>

<?php
if ($chapter->chapter_id &&
(!$chapter->chapter_deleted || validate_level($user, 'gmod')) &&
($chapter->upload_timestamp < $timestamp ||
	($user->user_id == $chapter->user_id || validate_level($user, 'gmod') || ($chapter->group_leader_id === $user->user_id || $chapter->group_leader_id_2 === $user->user_id || $chapter->group_leader_id_3 === $user->user_id)))) { ?>

<?php if ($mode == 'chapter') { ?>

var prev_chapter_id = <?= $prev_id ?>;
var next_chapter_id = <?= $next_id ?>;
var prev_pages = <?= $prev_pages ?>;
var manga_id = <?= $chapter->manga_id ?>;
var chapter_id = <?= $chapter->chapter_id ?>;
var dataurl = '<?= $chapter->chapter_hash ?>';
var page_array = [
<?php
foreach ($page_array as $value) {
	print "'$value',";
}
?>
];
var server = '<?= $server ?>';



function preload_page(i) {
  if (i >= 0 && i < page_array.length) {
    var img = new Image();
    img.src = img_url(i);
  }
}

// URL methods for simplicity's sake to prepare for possible changes in the future
function img_url(pg) {
  return server+dataurl+"/"+page_array[pg - 1];
}
function chapter_url(id, pg) {
  if (pg == null) { return "/chapter/"+id; }
  return "/chapter/"+id+"/"+pg;
}
function manga_url(id) {
  return "/manga/"+id;
}

function try_next_chapter() {
	if (next_chapter_id) {
      location.href = chapter_url(next_chapter_id);
    } else {
      location.href = manga_url(manga_id);
    }
}

function try_prev_chapter() {
	if (prev_chapter_id) {
      location.href = chapter_url(prev_chapter_id);
    } else {
      location.href = manga_url(manga_id);
    }
}

function go_page (page) {
  if (page > page_array.length) {
    if (next_chapter_id) {
      location.href = chapter_url(next_chapter_id);
    } else {
      location.href = manga_url(manga_id);
    }
  } else if (page < 1) {
    if (prev_chapter_id) {
      location.href = chapter_url(prev_chapter_id, prev_pages);
    } else {
      location.href = manga_url(manga_id);
    }
  } else {
    load_page(page);
    window.history.pushState({ page: page }, null, chapter_url(chapter_id, page));
  }
}

function load_page (page) {
  $("#current_page").attr("src", img_url(page)).one("load", function() {
    // Bootstrap selectpicker should be updated directly
    $("#jump_page").val(page);

    $("#current_page").data("page", page);
    // If scrollTop while going through history is unwanted, this should probably be moved to go_page
   $(window).scrollTop($("#current_page").offset().top - $("#top_nav").height() - 60);
	preload_page(page+1);
  });
}

<?php
//*****************
// SINGLE PAGE MODE
//*****************
if (!$user->reader_mode) { ?>

// Immediately make sure jquery knows data-page is an int, and set the current history item
var page = parseInt($("#current_page").data("page"));
$("#current_page").data("page", page);
window.history.replaceState({ page: page }, null, chapter_url(chapter_id, page));
preload_page(page+1);

// Makes history work
window.onpopstate = function (evt) {
    if (evt.state != null) {
        load_page(evt.state.page);
    }
};

$("#jump_page").change(function(evt) {
  go_page(parseInt($(this).val()));
});

$("#current_page").click(function(evt) {
  go_page($(this).data("page") + 1);
});

var isPressed = false;
$(document).keyup(function(evt) {
	if (evt.key === isPressed) {
		isPressed = false;
	}
})
$(document).keydown(function(evt) {
  if (evt.altKey || evt.ctrlKey || evt.metaKey || evt.shiftKey || isPressed !== false) {
    return;
  }
  isPressed = evt.key;
  evt.stopPropagation();

  var tag = (evt.target || evt.srcElement).tagName
  if (!['INPUT','SELECT','TEXTAREA'].includes(tag)) {
    switch (evt.key.toLowerCase()) {
      case "arrowleft":
      case "left":
      case "a":
        return go_page($("#current_page").data("page") - 1);

      case "arrowright":
      case "right":
      case "d":
        return go_page($("#current_page").data("page") + 1);
    }
  }
});

<?php if ($user->swipe_sensitivity > 25) { ?>
$("#current_page").swipe({
	swipeRight:function(event, direction, distance, duration, fingerCount) {
		go_page($("#current_page").data("page") <?= ($user->swipe_direction) ? "-" : "+" ?> 1);
	},
	threshold:<?= $user->swipe_sensitivity ?>
});
$("#current_page").swipe({
	swipeLeft:function(event, direction, distance, duration, fingerCount) {
		go_page($("#current_page").data("page") <?= ($user->swipe_direction) ? "+" : "-" ?> 1);
	},
	threshold:<?= $user->swipe_sensitivity ?>
});
<?php } ?>

$(".prev_page_alt").click(function(){
	go_page($("#current_page").data("page") <?= ($user->swipe_direction) ? "-" : "+" ?> 1);
});
$(".next_page_alt").click(function(){
	go_page($("#current_page").data("page") <?= ($user->swipe_direction) ? "+" : "-" ?> 1);
});

<?php }

//*****************
// LONG STRIP MODES
//*****************

else { ?>

// Hide Header on on scroll down //not single pages
/*var lastScrollTop = 0;

window.addEventListener("scroll", function(){
   var st = window.pageYOffset || document.documentElement.scrollTop;
   if (st > lastScrollTop){
       $(".navbar").fadeOut();
   } else {
      $(".navbar").fadeIn();
   }
   lastScrollTop = st;
}, false);*/

$(".click").click(function(evt) {
	try_next_chapter();
});

var isPressed = false;
$(document).keyup(function(evt) {
	if (evt.key === isPressed) {
		isPressed = false;
	}
})
$(document).keydown(function(evt) {
	if (evt.altKey || evt.ctrlKey || evt.metaKey || evt.shiftKey || isPressed !== false) {
		return;
	  }
	  isPressed = evt.key;
	  evt.stopPropagation();

  var tag = (evt.target || evt.srcElement).tagName
  if (!['INPUT','SELECT','TEXTAREA'].includes(tag)) {
    switch (evt.key.toLowerCase()) {
      case "arrowleft":
      case "left":
      case "a":
      <?= ($user->swipe_direction) ? "try_prev_chapter();" : "try_next_chapter();" ?>
        break;

      case "arrowright":
      case "right":
      case "d":
       <?= ($user->swipe_direction) ? "try_next_chapter();" : "try_prev_chapter();" ?>
        break;
    }
  }
});



<?php }

//*****************
// INF SCROLL
//*****************

if ($user->reader_mode == 1) { ?>

// Hide Header on on scroll down //not single pages
var lastScrollTop = 0;

window.addEventListener("scroll", function(){
   var st = window.pageYOffset || document.documentElement.scrollTop;
   if (st > lastScrollTop){
       $(".navbar").fadeOut();
   } else {
	   if (st < (lastScrollTop - 20)) {
			$(".navbar").fadeIn();
	   }
   }
   lastScrollTop = st;
}, false);

	lazyload();

<?php } ?>




$("#jump_chapter").change(function() {
  var chapter = parseInt($(this).val());
  location.href = chapter_url(chapter);
});

$("#jump_group").change(function() {
  var chapter = parseInt($(this).val());
  location.href = chapter_url(chapter);
});

$(".prev_chapter_alt").click(function(){
	location.href = chapter_url(prev_chapter_id);
});
$(".next_chapter_alt").click(function(){
	location.href = chapter_url(next_chapter_id);
});

$("#nav_reader_controls").click(function(){
	$("#options_div").toggle();
});

$(".minimise").click(function(event){
	event.preventDefault();
	$(".navbar, .toggle").toggle();
	$(".edit, .report, .settings").hide();
	$(".images").show();
	$("body").css("padding-top", "0px");
});

$(".maximise").click(function(){
	$(".navbar, .toggle").toggle();
	$("body").css("padding-top", "70px");
	$("#report_button, #edit_button, #settings_button").attr("disabled", false);
});

<?php } ?>

$('.btn-spoiler').click(function(evt){
	evt.preventDefault();
	$(this).next(".spoiler").toggle();
});

<?php if (validate_level($user, 'member')) { ?>

$(".emoji-toggle").click(function(){
	$(this).parent().parent().next(".emojis").toggle();
});

<?= jquery_post("start_empty_thread", 12, "", "Start comment thread", "Starting", "You have started a comment thread.", "location.reload();"); ?>

<?= jquery_post("post_reply", $chapter->thread_id, "", "Comment", "Commenting", "Your comment has been submitted.", "location.reload();"); ?>

<?= display_js_posting() ?>

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

	var post_id = $(event.relatedTarget).data('post-id');
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

$(".comment_button").click(function(){
	$(this).html("<?= display_fa_icon('spinner', '', 'fa-pulse') ?>").attr("disabled", true);
	location.href = "/chapter/<?= $id ?>/comments";
});

<?= jquery_post("chapter_report", $id, "", "Submit", "Submitting", "This chapter has been reported.", "location.reload();"); ?>
<?= jquery_post("reader_settings", 0, "", "Save", "Saving", "Your reader settings have been saved.", "location.reload();"); ?>

<?php } ?>

<?php if (validate_level($user, 'gmod') || $chapter->user_id == $user->user_id ||
	($chapter->group_leader_id === $user->user_id || $chapter->group_leader_id_2 === $user->user_id || $chapter->group_leader_id_3 === $user->user_id || in_array($user->username, $group_members_array))) { ?>
$("#edit_button").click(function(){
	$(this).html("<?= display_fa_icon('spinner', '', 'fa-pulse') ?>").attr("disabled", true);
	location.href = "/chapter/<?= $id ?>/edit";
});
$("#cancel_edit_button").click(function(){
	$(this).html("<?= display_fa_icon('spinner', '', 'fa-pulse') ?>").attr("disabled", true);
	location.href = "/chapter/<?= $id ?>";
});

<?= js_display_file_select("#save_edit_button") ?>

$("#edit_chapter_form").submit(function(evt) {
	var form = this;

	var success_msg = "<div class='alert alert-success text-center' role='alert'><strong>Success:</strong> This chapter has been edited.</div>";
	var error_msg = "<div class='alert alert-warning text-center' role='alert'><strong>Warning:</strong> Something went wrong with your upload.</div>";

	$("#save_edit_button").html("<?= display_fa_icon("spinner", "", "fa-pulse") ?> Saving...").attr("disabled", true);

	var formdata = new FormData(form);

	evt.preventDefault();
	$.ajax({
		url: "/ajax/actions.ajax.php?function=chapter_edit&id=<?= $id ?>",
		type: 'POST',
		data: formdata,
		cache: false,
		contentType: false,
		processData: false,

		xhr: function() {
			var myXhr = $.ajaxSettings.xhr();
			if (myXhr.upload) {
				myXhr.upload.addEventListener('progress', function(e) {
					console.log(e)
					if (e.lengthComputable) {
						$('#progressbar').parent().show();
						$('#progressbar').width((Math.round(e.loaded/e.total*100) + '%'));
					}
				} , false);
			}
			return myXhr;
		},

		success: function (data) {
			$('#progressbar').parent().hide()
			$('#progressbar').width('0%');
			if (!data) {
				$("#message_container").html(success_msg).show().delay(3000).fadeOut();
				location.href = "/chapter/<?= $id ?>";
			}
			else {
				$("#message_container").html(data).show().delay(3000).fadeOut();
			}
			$("#save_edit_button").html("<?= display_fa_icon('pencil-alt') ?> Save").attr("disabled", false);
		},

		error: function(err) {
			console.error(err);
			$('#progressbar').parent().hide()
			$("#save_edit_button").html("<?= display_fa_icon('pencil-alt') ?> Save").attr("disabled", false);
			$("#message_container").html(error_msg).show().delay(3000).fadeOut();
		}
	});
});

<?= jquery_get("chapter_undelete", $id, "", "", "Restoring", "This chapter has been restored", "location.reload();") ?>

$("#chapter_delete_button").click(function(event){
	event.preventDefault();

    var confirm_message = "MangaDex allows you to re-upload a chapter without having to delete your original chapter - thus preserving any external links directing to your chapter. Simply follow the standard upload procedure on your intended chapter's edit page with a new .zip to replace the old version. \n\n Are you sure you want to delete this chapter?"

    if (confirm(confirm_message)) {
		$.ajax({
			url: "/ajax/actions.ajax.php?function=chapter_delete&id=<?= $id ?>",
			success: function(data) {
				if (data.indexOf('Failed') !== -1) {
                    $("#message_container").html(data).show().delay(5000).fadeOut();
				} else {
                    $("#message_container").html(data).show().delay(3000).fadeOut();
                    location.href = "/title/<?= $chapter->manga_id ?>";
				}
			},
			cache: false,
			contentType: false,
			processData: false
		});
	}
});

<?php } ?>
<?php } ?>
<?php } ?>
