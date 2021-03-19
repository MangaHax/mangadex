<?php
if ($user->user_id){
    print jquery_post("change_activation_email", 0, "check", "Confirm", "Confirming", "Your email has been updated.", "");
}
?>