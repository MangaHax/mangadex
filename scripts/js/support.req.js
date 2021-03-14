<?php 
if ($user->user_id) {
	print jquery_post("claim_transaction", 0, "save", "Claim", "Claiming", "Transactions claimed.", "location.reload();");
} 
?>