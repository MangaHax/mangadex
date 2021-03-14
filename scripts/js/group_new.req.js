<?php 
if ($user->user_id) {
	print js_display_file_select();

	print jquery_post("group_add", 0, "plus-circle", "Add group", "Adding", "This group has been added.", "location.href = '/group/$next_group_id';");
}
?>