<?php if ($user->user_id) { ?>

<?= js_display_file_select("#upload_button") ?>

$("#upload_form").submit(function(evt) {
	var form = this;
 
	var success_msg = "<div class='alert alert-success text-center' role='alert'><strong>Success:</strong> Your chapter has been uploaded.</div>";
	var error_msg = "<div class='alert alert-warning text-center' role='alert'><strong>Warning:</strong> Something went wrong with your upload.</div>";
 
	$("#upload_button").html("<span class='fas fa-spinner fa-pulse' aria-hidden='true' title=''></span> Uploading...").attr("disabled", true);
	
	var formdata = new FormData(form);
	
	evt.preventDefault();
	$.ajax({
		url: "/ajax/actions.ajax.php?function=chapter_upload",
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
				
				form.reset();
				var restore = ['manga_id', 'group_id', 'group_id_2', 'group_id_3', 'lang_id', 'volume_number' ];
				for (var i = 0; i < restore.length; i++) {
					form.elements[restore[i]].value = formdata.get(restore[i]);
				}
				form.elements.chapter_number.value = Math.floor(parseFloat(formdata.get('chapter_number')) + 1 ) || '';
				if (form.elements.is_deleted != null)
					form.elements.is_deleted.checked = formdata.get('is_deleted');
			}
			else {
				$("#message_container").html(data).show().delay(5000).fadeOut();
			}
			$("#upload_button").html("<?= display_fa_icon("upload") ?> Upload").attr("disabled", false);
		},
 
		error: function(err) {
			console.error(err);
			$('#progressbar').parent().hide()
			$("#upload_button").html("<?= display_fa_icon("upload") ?> Upload").attr("disabled", false);
			$("#message_container").html(error_msg).show().delay(5000).fadeOut();
		}
	});
});

<?php } ?>