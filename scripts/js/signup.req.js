<?php if (!$user->user_id) { 

print jquery_post("signup", 0, "pencil-alt", "Sign up", "Signing up", "You have signed up.", "location.href = '/login';");

?>

$("[data-toggle='popover']").popover({
	"container": "body",
	"trigger": "focus",
	"placement": "auto left"
});		

<?php } ?>