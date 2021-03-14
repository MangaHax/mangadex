<?php 
switch ($mode) {
	case 'chapter_reports':
	?>
	$(".report_accept_all").click(function(event){
		id = $(this).attr("id"); 
		if (confirm("Are you sure? This will accept all reports for this chapter.")) {
			$.ajax({
				url: "/ajax/actions.ajax.php?function=chapter_report_accept_all&id="+id,
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
	
	$(".report_accept").click(function(event){
		id = $(this).attr("id"); 
		$.ajax({
			url: "/ajax/actions.ajax.php?function=chapter_report_accept&id="+id,
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

	$(".report_reject").click(function(event){
		id = $(this).attr("id"); 
		$.ajax({
			url: "/ajax/actions.ajax.php?function=chapter_report_reject&id="+id,
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
	
	<?php 
	break; 

	case 'upload_queue':
	?>
	$(".queue_accept").click(function(event){
		id = $(this).attr("data-id"); 
		$.ajax({
			url: "/ajax/actions.ajax.php?function=upload_queue_accept&id="+id,
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

	$(".queue_reject").click(function(event){
		id = $(this).attr("data-id"); 
		$.ajax({
			url: "/ajax/actions.ajax.php?function=upload_queue_reject&id="+id,
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
	
	<?php 
	break; 
	
	case 'manga_reports':
	?>

	$(".report_accept").click(function(event){
		id = $(this).attr("id"); 
		$.ajax({
			url: "/ajax/actions.ajax.php?function=manga_report_accept&id="+id,
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

	$(".report_reject").click(function(event){
		id = $(this).attr("id"); 
		$.ajax({
			url: "/ajax/actions.ajax.php?function=manga_report_reject&id="+id,
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
	
	<?php 
	break; 
	
	case 'featured': 
	?>

	$(".remove_featured").click(function(event){
		id = $(this).attr("id"); 
		$.ajax({
			url: "/ajax/actions.ajax.php?function=remove_featured&list_id=<?= $list_id ?>&manga_id="+id,
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

	<?= jquery_post("add_featured", $list_id, "", "", "Adding", "Manga added.", "location.reload(); ") ?>

	<?php 
	break;
}	
?>

$('.report-setstate-btn').click(function (e) {
	e.preventDefault();

	let id = $(e.currentTarget).attr('data-id');
	let state = $(e.currentTarget).attr('data-setstate');

	let formData = new FormData();
	formData.set('id', id);
	formData.set('state', state);

	$.ajax({
		url: '/ajax/actions.ajax.php?function=report_setstate',
		type: 'POST',
		data: formData,
		cache: false,
		headers: {'cache-control': 'no-cache'},
		contentType: false,
		processData: false,
		success: function (data) {
			try {
				data = JSON.parse(data);
			} catch (err){}

			if (data.status  === 'success') {
				$('tr[data-id="'+id+'"] .report-state-icon').each(function (i, t) {
					if ($(t).hasClass('report-state-icon-'+state)) {
						$(t).removeClass('d-none');
					} else {
						$(t).addClass('d-none');
					}
				});
			}
			else {
				$('#message_container').html("<div class='alert alert-danger text-center' role='alert'><strong>Error:</strong> Failed to submit report: "+(data.message || 'General error')+"</div>").show().delay(3000).fadeOut();
			}
		}
	});
});

$('.filter-state-btn').click(function (e) {
	e.preventDefault();

	let state = $(e.currentTarget).attr('data-setstate');

	$('.type-filter .report-state-icon').each(function (i, t) {
		if ($(t).hasClass('report-state-icon-'+state)) {
			$(t).removeClass('d-none');
		} else {
			$(t).addClass('d-none');
		}
	});

	if ('URLSearchParams' in window) {
		var urlParams = new URLSearchParams(window.location.search);
		urlParams.set("state", state);
		window.location.search = urlParams.toString();
	}
});

$('.report-refresh-btn').click(function (e) {
	window.location.reload();
});
