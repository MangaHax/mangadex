<?php 
if ($user->user_id) { 

print jquery_post("activate", 0, "check", "Activate", "Activating", "Your account has been activated.", "location.href = '/title/30461/bocchi-sensei-teach-me-mangadex';");
print jquery_get("resend_activation_code", 0, "sync", "Resend", "Resending", "Your activation code has been resent to <strong>$user->email</strong>.", "");

} 
?>