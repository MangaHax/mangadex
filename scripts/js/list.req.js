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

(function () {
	function getStorageArray(item) {
		try { return (sessionStorage.getItem(item) || '' ).split(',').filter(s => s) }
		catch (e) { return [] }
	}
	function setStorageArray(item, value) {
		try { sessionStorage.setItem(item, value.join(',')) }
		catch(e) {}
	}

	function updateHiddenManga(tagsInc, tagsExc) {
		for (let entry of document.querySelectorAll('.manga-entry')) {
			const entryTags = (entry.dataset.genreIds || '').split(',').filter(s => s)
			// if entry has any excluded tag or not every included tag, hide it
			const isHidden = tagsExc.some(tag => entryTags.includes(tag)) ||
				!tagsInc.every(tag => entryTags.includes(tag))
			entry.classList.toggle('d-none', isHidden)
		}
		//const hiddenIds = Array.from(document.querySelectorAll('.manga-entry.d-none')).map(n => n.dataset.id)
		//const filteredEl = document.querySelector('.filtered-amount')
		//filteredEl.textContent = `(${hiddenIds.length} filtered)`
		//console.log('Included:', tagsInc, 'Excluded:', tagsExc, 'Hidden:', hiddenIds)
	}

	const tagsInc = getStorageArray('tags_inc')
	const tagsExc = getStorageArray('tags_exc')

	for (let checkbox of document.querySelectorAll('input[name="manga_genres[]"]')) {
		checkbox.checked = tagsInc.includes(checkbox.value)
		checkbox.indeterminate = tagsExc.includes(checkbox.value)
		checkbox.dataset.state = checkbox.checked ? 1 : checkbox.indeterminate ? 2 : 0
		checkbox.addEventListener('change', function(evt) {
			let tagsInc = getStorageArray('tags_inc')
			let tagsExc = getStorageArray('tags_exc')
			switch (this.dataset.state) {
				case "0":
					tagsInc = tagsInc.filter(t => t !== this.value)
					tagsExc = tagsExc.filter(t => t !== this.value)
					break;
				case "1":
					tagsInc.push(this.value);
					tagsExc = tagsExc.filter(t => t !== this.value)
					break;
				case "2":
					tagsInc = tagsInc.filter(t => t !== this.value)
					tagsExc.push(this.value);
					break;
			}
			setStorageArray('tags_inc', tagsInc)
			setStorageArray('tags_exc', tagsExc)
			updateHiddenManga(tagsInc, tagsExc)
		})
	}

	updateHiddenManga(tagsInc, tagsExc)

})();

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
<?php } ?>

<?php 
if ($user->user_id == $list_user_id) {
	
	print js_display_file_select();
	
	print jquery_post("list_settings", 0, "save", "Save", "Saving", "Your list settings have been saved.", "location.reload();");
	
} ?>