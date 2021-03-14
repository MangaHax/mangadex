<?php 
if (in_array($user->user_id, TL_USER_IDS) || validate_level($user, 'mod')) {
	print jquery_post("translate", $id, "save", "Save", "Saving", "Translation saved.", "");
} 
?>