<?php
switch ($function) {
	case "group_add":
		$group_name = htmlentities($_POST["group_name"]);
		$group_lang_id = prepare_numeric($_POST["group_lang_id"]);
		$group_website = str_replace(['javascript:'], '', htmlentities($_POST["group_website"]));
		$group_irc_channel = htmlentities($_POST["group_irc_channel"]);
		$group_irc_server = htmlentities($_POST["group_irc_server"]);
		$group_discord = str_replace(['javascript:'], '', htmlentities($_POST["group_discord"]));
		$group_email = str_replace(['javascript:'], '', htmlentities($_POST["group_email"]));
		$group_description = str_replace(['javascript:'], '', htmlentities($_POST["group_description"]));
		
		//existing group
		$count_group_name = $sql->prep('group_add', ' SELECT count(*) FROM mangadex_groups WHERE group_name = ? ', [$group_name], 'fetchColumn', '', -1);

        // Make sure website has http://
        if (!empty($group_website) && stripos($group_website, 'http://') === false && stripos($group_website, 'https://') === false)
            $group_website = 'http://'.$group_website;

		if (!$count_group_name) {
			$group_discord = str_replace(['https://discord.gg/', 'https://discordapp.com/invite/'], ['', ''], $group_discord);

			$group_id = $sql->modify('group_add', " 
			INSERT INTO mangadex_groups (group_id, group_name, group_alt_name, group_leader_id, group_website, group_irc_channel, group_irc_server, group_discord, group_email, group_lang_id, group_founded, group_banner, group_likes, group_follows, group_views, group_description, group_control, group_delay, group_last_updated, thread_id) VALUES (NULL, ?, '', 1, ?, ?, ?, ?, ?, ?, '2018-01-01', '', 0, 0, 0, ?, 0, 0, 0, 0) 
			", [$group_name, $group_website, $group_irc_channel, $group_irc_server, $group_discord, $group_email, $group_lang_id, $group_description]);
			
			$details = $group_id;
		}
		else {
			$details = "Your chosen group name is not unique.";
			print display_alert("danger", "Failed", $details); //fail
		}
		
		$result = (!is_numeric($details)) ? 0 : 1;
		break;
	
	case "group_add_member":
		$group_id = prepare_numeric($_GET["id"]);
		
		$group = new Group($group_id);
		
		$add_member_user_id = prepare_numeric($_POST["add_member_user_id"]);
		$check_member = $sql->prep('group_add_member', ' SELECT count(*) FROM mangadex_users WHERE user_id = ? ', [$add_member_user_id], 'fetchColumn', '', -1);
		$check_existing_member = $sql->prep('group_add_member', ' SELECT count(*) FROM mangadex_link_user_group WHERE user_id = ? AND group_id = ? ', [$add_member_user_id, $group_id], 'fetchColumn', '', -1);
		$user_is_already_invited = $memcached->get("group_{$group_id}_invite_{$add_member_user_id}");

		// invalid user
        if(!($check_member && !$check_existing_member && $add_member_user_id > 1)){
            $details = "User does not exist or is already in your group.";
            print display_alert("danger", "Failed", $details);
        }
        // gmods can add directly
        else if(validate_level($user, 'gmod')){
            $sql->modify('group_add_member', " INSERT INTO mangadex_link_user_group (id, user_id, group_id, role) VALUES (NULL, ?, ?, 2) ", [$add_member_user_id, $group_id]);

            $memcached->delete("group_{$group_id}_invite_{$add_member_user_id}");
            $memcached->delete("group_{$group_id}_members_display");
            $memcached->delete("group_{$group_id}_members");
            $memcached->delete("group_{$group_id}");
            $memcached->delete("user_{$add_member_user_id}_groups");
            
            $details = $group_id;
            print display_alert("success", "Success", "User $add_member_user_id has been added to group $group_id."); //success
        }
        // group leaders can invite if user not already invited
        else if($group->group_leader_id == $user->user_id && !$user_is_already_invited){
            $memcached->set("group_{$group_id}_invite_{$add_member_user_id}", "pending", 1209600);

            // send the invitee an automated message
            $subject = "You have been invited to join {$group->group_name}.";
            $message = "Hello,\n\n" .
                "You have been invited to join group {$group->group_name}.
                 Please check the [url=https://mangadex.org/group/{$group->group_id}]group's[/url] page to accept or decline the invitation.
                 This invitation will expire in 2 weeks.";
            $sender_id = 1; // Default MangaDex user; TODO: remove hardcoded value?
            $recipient_id = $add_member_user_id;

            $thread_id = $sql->modify('msg_send', '
                INSERT INTO mangadex_pm_threads (thread_id, thread_subject, sender_id, recipient_id, thread_timestamp, sender_read, recipient_read, sender_deleted, recipient_deleted) 
                VALUES (NULL, ?, ?, ?, UNIX_TIMESTAMP(), 1, 0, 0, 0) ', [$subject, $sender_id, $recipient_id]);
            $sql->modify('msg_send', '
                INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text) 
				VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$thread_id, $sender_id, $message]);
            
            print display_alert("success", "Success", "User $add_member_user_id has been invited to group $group_id."); //success
        }
        else {
            $details = "You can't invite group members or the user is already invited.";
            print display_alert("danger", "Failed", $details); //fail
        }
        
		$result = (!is_numeric($details)) ? 0 : 1;
		break;
		
    case "group_accept_invite":
        $group_id = prepare_numeric($_GET["id"]);
        $user_id = $user->user_id;
        
        if($memcached->get("group_{$group_id}_invite_{$user_id}") == "pending"){
            $sql->modify('group_add_member', " INSERT INTO mangadex_link_user_group (id, user_id, group_id, role) VALUES (NULL, ?, ?, 2) ", [$user_id, $group_id]);

            $memcached->delete("group_{$group_id}_invite_{$user_id}");
            $memcached->delete("group_{$group_id}_members_display");
            $memcached->delete("group_{$group_id}_members");
            $memcached->delete("group_{$group_id}");
            $memcached->delete("user_{$user_id}_groups");

            $details = $group_id;

            // send the group leader an automated message
            $group = new Group($group_id);
            $subject = "{$user->username} has accepted your invite.";
            $message = "Hello,\n\n" .
                "{$user->username} has accepted your invite and is now a member of [url=https://mangadex.org/group/{$group_id}]{$group->group_name}[/url].";
            $sender_id = 1; // Default MangaDex user; TODO: remove hardcoded value?
            $recipient_id = $group->group_leader_id;
            
            $thread_id = $sql->modify('msg_send', '
                INSERT INTO mangadex_pm_threads (thread_id, thread_subject, sender_id, recipient_id, thread_timestamp, sender_read, recipient_read, sender_deleted, recipient_deleted) 
                VALUES (NULL, ?, ?, ?, UNIX_TIMESTAMP(), 1, 0, 0, 0) ', [$subject, $sender_id, $recipient_id]);
            $sql->modify('msg_send', '
                INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text) 
				VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$thread_id, $sender_id, $message]);
            
            print display_alert("success", "Success", "You have joined group $group->group_name."); //success
        }
        else{
            print display_alert("danger", "Failed", "You have not been invited to group {$group_id}"); //success
        }
        break;
    case "group_reject_invite":
        $group_id = prepare_numeric($_GET["id"]);
        
        if($memcached->get("group_{$group_id}_invite_{$user->user_id}") == "pending") {
            $memcached->set("group_{$group_id}_invite_{$user->user_id}", "rejected", 1209600);

            print display_alert("success", "Success", "You have rejected the invite."); //success
        }
        else{
            print display_alert("danger", "Failed", "You have not been invited to group {$group_id}"); //success
        }
        break;
		
	case "group_delete_member":
		$delete_user_id = prepare_numeric($_GET['user_id']);
		$group_id = prepare_numeric($_GET['group_id']);
		
		$group = new Group($group_id);
		
		if (validate_level($user, 'gmod') || $group->group_leader_id == $user->user_id) {
			$sql->modify('group_delete_member', " DELETE FROM mangadex_link_user_group WHERE group_id = ? AND user_id = ? AND role = 2 LIMIT 1 ", [$group_id, $delete_user_id]);
			
			$memcached->delete("group_{$group_id}_members_display");
			$memcached->delete("group_{$group_id}_members");
            $memcached->delete("group_{$group_id}");
			$memcached->delete("user_{$delete_user_id}_groups");
			
			$details = $group_id;
			print display_alert("success", "Success", "User has been deleted."); //success	
		}
		else {
			$details = "You can't delete members.";
			print display_alert("danger", "Failed", $details); //fail
		}
		
		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "group_leave":
		$group_id = prepare_numeric($_GET['group_id']);

		$group = new Group($group_id);

		if (!validate_level($user, 'member')) {
            $details = "You must be logged in!";
            print display_alert("danger", "Failed", $details); //fail

        } else if (!$group || $group->group_id < 1) {
            $details = "Could not find group!";
            print display_alert("danger", "Failed", $details); //fail

        } else if ($group->group_leader_id == $user->user_id) {
		    // Groupleader wants to leave

            $details = "A group leader can't leave their own group. Please contact staff.";
            print display_alert("info", "Notice", $details); //fail
		} else if (in_array($user->user_id, array_keys($group->get_members()))) {
		    // Group member wants to leave

            $sql->modify('group_delete_member', " DELETE FROM mangadex_link_user_group WHERE group_id = ? AND user_id = ? AND role = 2 LIMIT 1 ", [$group_id, $user->user_id]);

            $memcached->delete("group_{$group_id}_members_display");
            $memcached->delete("group_{$group_id}_members");
            $memcached->delete("group_{$group_id}");
            $memcached->delete("user_{$user->user_id}_groups");

            $details = $group_id;
            print display_alert("success", "Success", "You have left the group."); //success
        }
		else {
			$details = "You can't leave a group you don't belong to.";
			print display_alert("danger", "Failed", $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;
				
	case "group_edit":
		$id = prepare_numeric($_GET["id"]);
		
		$group = new Group($id);
		
		$group_founded = htmlentities($_POST["group_founded"]);
		$url_link = htmlentities($_POST["url_link"]);
		$irc_channel = htmlentities($_POST["irc_channel"]);
		$irc_server = htmlentities($_POST["irc_server"]);
		$discord = htmlentities($_POST["discord"]);
		$lang_id = prepare_numeric($_POST["lang_id"]);
		$group_email = htmlentities($_POST["group_email"]);
		$group_description = htmlentities($_POST["group_description"]);
		$group_control = isset($_POST["group_control"]) ? 1 : 0;
		$group_delay = prepare_numeric($_POST["group_delay"]);
		$old_file = $_FILES['file']['name'];

		// Only allow group inactive state to be changed if user is a mod
        $group_is_inactive = validate_level($user, 'gmod')
            ? ($_POST['group_is_inactive'] ?? false ? 1 : 0)
            : $group->group_is_inactive;

		if ($_FILES["file"] && $old_file)
			$error .= validate_image($_FILES["file"]);
		
		if (!(validate_level($user, 'gmod') || $group->group_leader_id == $user->user_id || in_array($user->username, $group->get_members())))
			$error .= display_alert("danger", "Failed", "You can't edit $group->group_name.");

        // Make sure website has http://
        if (!empty($url_link) && stripos($url_link, 'http://') === false && stripos($url_link, 'https://') === false)
            $url_link = 'http://'.$url_link;

		if (!$error) {
			$sql->modify('group_edit', " UPDATE mangadex_groups SET group_founded = ?, group_website = ?, group_irc_channel = ?, group_irc_server = ?, group_discord = ?, group_email = ?, group_lang_id = ?, group_description = ?, group_control = ?, group_is_inactive = ?, group_delay = ? WHERE group_id = ? LIMIT 1 ", [$group_founded, $url_link, $irc_channel, $irc_server, $discord, $group_email, $lang_id, $group_description, $group_control, $group_is_inactive, $group_delay, $group->group_id]);
			
			if ($old_file) {
				$arr = explode(".", $_FILES["file"]["name"]);
				$ext = strtolower(end($arr));
				
				if ($group->group_banner)
					@unlink(ABS_DATA_BASEPATH . "/groups/$group->group_id.$group->group_banner");
				
				move_uploaded_file($_FILES["file"]["tmp_name"], ABS_DATA_BASEPATH . "/groups/$group->group_id.$ext");
				
				$sql->modify('group_edit', " UPDATE mangadex_groups SET group_banner = ? WHERE group_id = ? LIMIT 1 ", [$ext, $group->group_id]);
			}
			
			$memcached->delete("group_$id");
			
			$details = $id;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}
		
		$result = ($details) ? 0 : 1;
		break;
	
	case "group_like":
		$id = prepare_numeric($_GET["id"]);
		
		$group = new Group($id);
		
		$array_of_user_id_ip = $group->get_likes_user_id_ip_list();
		
		if ($user->user_id && !in_array($user->user_id, $array_of_user_id_ip["user_id"])) {
			$sql->modify('group_like', " INSERT IGNORE INTO mangadex_group_likes (group_id, user_id, ip, timestamp) VALUES (?, ?, '', UNIX_TIMESTAMP()) ", [$id, $user->user_id]);
		}
		elseif (!$user->user_id && !in_array($ip, $array_of_user_id_ip["ip"])) {
			$sql->modify('group_like', " INSERT IGNORE INTO mangadex_group_likes (group_id, user_id, ip, timestamp) VALUES (?, 0, ?, UNIX_TIMESTAMP()) ", [$id, $ip]);
		}
		
		$sql->modify('group_like', " UPDATE mangadex_groups SET group_likes = (SELECT count(*) FROM mangadex_group_likes WHERE group_id = ?) WHERE group_id = ? LIMIT 1 ", [$group->group_id, $group->group_id]);
		
		$memcached->delete("group_$id");
		$memcached->delete("group_{$id}_likes_user_id_ip_list");
		
		$details = $id;
		$result = 1;
		break;

	case "group_unlike":
		$id = prepare_numeric($_GET["id"]);
		
		$group = new Group($id);
		
		$array_of_user_id_ip = $group->get_likes_user_id_ip_list();
		
		if ($user->user_id && in_array($user->user_id, $array_of_user_id_ip["user_id"])) {
			$sql->modify('group_unlike', " DELETE FROM mangadex_group_likes WHERE group_id = ? AND user_id = ? LIMIT 1 ", [$group->group_id, $user->user_id]);
		}
		elseif (!$user->user_id && in_array($ip, $array_of_user_id_ip["ip"])) {
			$sql->modify('group_unlike', " DELETE FROM mangadex_group_likes WHERE group_id = ? AND ip = ? LIMIT 1 ", [$group->group_id, $ip]);
		}
		
		$sql->modify('group_unlike', " UPDATE mangadex_groups SET group_likes = (SELECT count(*) FROM mangadex_group_likes WHERE group_id = ?) WHERE group_id = ? LIMIT 1 ", [$group->group_id, $group->group_id]);
		
		$memcached->delete("group_$id");
		$memcached->delete("group_{$id}_likes_user_id_ip_list");
		
		$details = $id;
		$result = 1;
		break;

	case "group_block":
		$group_id = prepare_numeric($_GET["id"]);
		
		$group = new Group($group_id);
		
		if (validate_level($user, 'member') && isset($group->group_id)) {
			$sql->modify('group_block', ' INSERT IGNORE INTO mangadex_user_block_group (user_id, group_id) VALUES (?, ?) ', [$user->user_id, $group_id]);

			$memcached->delete("user_{$user->user_id}_blocked_groups");
			$memcached->delete("group_{$group_id}_blocked_users");
			$details = $group_id;
		}
		else {
			$details = "You can't block this group.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = ($details) ? 0 : 1;
		break;

	case "group_unblock":
		$group_id = prepare_numeric($_GET["id"]);
		
		if (validate_level($user, 'member')) {
			$sql->modify('group_unblock', ' DELETE FROM mangadex_user_block_group WHERE (user_id = ? AND group_id = ?) LIMIT 1 ', [$user->user_id, $group_id]);

			$memcached->delete("user_{$user->user_id}_blocked_groups");
			$memcached->delete("group_{$group_id}_blocked_users");
			$details = $group_id;
		}
		else {
			$details = "You can't unblock this group.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = ($details) ? 0 : 1;
		break;
		
	case "group_follow":
		$id = prepare_numeric($_GET["id"]);
		
		if ($user->user_id) {
			$sql->modify('group_follow', " INSERT IGNORE INTO mangadex_follow_user_group (user_id, group_id) VALUES (?, ?) ", [$user->user_id, $id]);
			
			$sql->modify('group_follow', " UPDATE mangadex_groups SET group_follows = 
				(SELECT count(*) FROM mangadex_follow_user_group WHERE group_id = ?) 
				WHERE group_id = ? LIMIT 1 ", [$id, $id]);
			
			$memcached->delete("group_$id");
			$memcached->delete("group_{$id}_follows_user_id");
			
			$details = $id;
		}
		else {
			$details = "You have timed out. Please log in again.";
			print display_alert("danger", "Failed", $details); // fail
		}
		
		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "group_unfollow":
		$id = prepare_numeric($_GET["id"]);

		if ($user->user_id) {
			$sql->modify('group_unfollow', " DELETE FROM mangadex_follow_user_group WHERE user_id = ? AND group_id = ? LIMIT 1 ", [$user->user_id, $id]);
			
			$sql->modify('group_unfollow', " UPDATE mangadex_groups SET group_follows = 
				(SELECT count(*) FROM mangadex_follow_user_group WHERE group_id = ?) 
				WHERE group_id = ? LIMIT 1 ", [$id, $id]);
			
			$memcached->delete("group_$id");
			$memcached->delete("group_{$id}_follows_user_id");
			
			$details = $id;
		}
		else {
			$details = "You have timed out. Please log in again.";
			print display_alert("danger", "Failed", $details); // fail
		}
		
		$result = (!is_numeric($details)) ? 0 : 1;
		break;	
		
	case "admin_edit_group":
		$id = prepare_numeric($_GET["id"]);
		$group_name = htmlentities($_POST["group_name"]);	
		$group_alt_name = htmlentities($_POST["group_alt_name"]);	
		$group_leader_id = prepare_numeric($_POST["group_leader_id"]);	
		
		if (validate_level($user, 'gmod')) {
			$sql->modify('admin_edit_group', " UPDATE mangadex_groups SET group_name = ?, group_alt_name = ?, group_leader_id = ? WHERE group_id = ? LIMIT 1 ", [$group_name, $group_alt_name, $group_leader_id, $id]);
			
			$uploader = new User($group_leader_id, 'user_id');
			$group = new Group($id);
			
			if ($uploader->level_id < 5 && $group_leader_id > 0)
				$sql->modify('admin_edit_group', " UPDATE mangadex_users SET level_id = 5 WHERE user_id = ? LIMIT 1 ", [$group_leader_id]);
			
			if ($group_leader_id > 1) {
				$sql->modify('admin_edit_group', " DELETE FROM mangadex_link_user_group WHERE group_id = ? AND role = 3 LIMIT 1 ", [$id]);
				$sql->modify('admin_edit_group', " INSERT INTO mangadex_link_user_group (id, user_id, group_id, role) VALUES (NULL, ?, ?, 3) ", [$group_leader_id, $id]);
			}
			else {
				$sql->modify('admin_edit_group', " DELETE FROM mangadex_link_user_group WHERE group_id = ? AND role = 3 LIMIT 1 ", [$id]);
			}
			
			$memcached->delete("group_$id");
			$memcached->delete("user_{$group_leader_id}_groups");
			$memcached->delete("user_{$group->group_leader_id}_groups");

			$details = $id;
		}
		else {
			$details = "You can't edit groups.";
			print display_alert("danger", "Failed", $details); //fail	
		}
		
		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "group_delete":
		$id = prepare_numeric($_GET["id"]);
		
		if (validate_level($user, 'admin')) {
			$sql->modify('group_delete', " DELETE FROM mangadex_groups WHERE group_id = ? LIMIT 1 ", [$id]);
			
			$memcached->delete("group_$id");
			
			$details = $id;
			print display_alert("success", "Success", "Group #$id has been deleted."); // success
		}
		else {
			$details = "You can't delete Group #$id.";
			print display_alert("danger", "Failed", $details); // fail
		}
		
		$result = (!is_numeric($details)) ? 0 : 1;
		break;
}