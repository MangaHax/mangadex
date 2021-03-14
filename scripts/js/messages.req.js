<?php 
if ($user->user_id) {
	print jquery_post("msg_del", 0, "trash", "Delete", "Deleting", "Your message(s) have been deleted.", "location.reload();");
	
	if ($mode == 'deleted') {
		?>
		
		$('#inbox').removeClass('active');
		$('#bin').addClass('active');
		
		<?php
	}
	
print display_js_posting();

print jquery_post("msg_send", 0, "pencil-alt", "Send", "Sending", "Your message has been sent.", "location.href = '/messages';");

} ?>