<?php
switch ($function) {
	case 'order':
		$payment = prepare_numeric($_POST['payment']);

		if (array_sum($_POST) - $payment > 0) {
			$payment = prepare_numeric($_POST['payment']);
			unset($_POST['payment']);
			$order_json = json_encode($_POST);

			$sql->modify('order', ' INSERT INTO mangadex_orders (order_id, user_id, payment, items) VALUES (NULL, ?, ?, ?) ', [$user->user_id, $payment, $order_json]);

			$details = '';
		}
		else {
			$details = 'You need to order something!';
			print display_alert('danger', 'Failed', $details); //fail
		}
		$result = ($details) ? 0 : 1;
		break;

	case 'cancel_order':
		$id = prepare_numeric($_GET['id']);

		$sql->modify('order', ' DELETE FROM mangadex_orders WHERE order_id = ? AND user_id = ? LIMIT 1 ', [$id, $user->user_id]);

		$details = '';
		$result = ($details) ? 0 : 1;
		break;

	case 'claim_transaction':
		$id_string = $_POST['id_string'];

		if (validate_level($user, 'member')) {

				$sql->modify('claim_transaction', ' INSERT INTO mangadex_user_paypal (user_id, paypal) VALUES (?, ?) ', [$user->user_id, $id_string]);

				$memcached->delete("user_{$user->user_id}_transactions");


			$details = $id_string;
		}
		else {
			$details = "You can't claim transactions.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = ($details) ? 0 : 1;
		break;

	case 'friend_accept':
		$id = prepare_numeric($_GET['id']);

		if (validate_level($user, 'member') && $user->user_id != $id) {
			$sql->modify('friend_accept', ' 
			INSERT INTO mangadex_user_relations (user_id, relation_id, target_user_id, accepted) VALUES (?, 1, ?, 1)
				ON DUPLICATE KEY UPDATE accepted = 1 
			', [$user->user_id, $id]);

			$sql->modify('friend_accept', ' UPDATE mangadex_user_relations SET accepted = 1 WHERE user_id = ? AND relation_id = 1 AND target_user_id = ? LIMIT 1 ', [$id, $user->user_id]);

			$memcached->delete("user_{$user->user_id}_friends_user_ids");
			$memcached->delete("user_{$user->user_id}_pending_friends_user_ids");
			$memcached->delete("user_{$id}_friends_user_ids");
			$memcached->delete("user_{$id}_pending_friends_user_ids");

			$details = $id;
		}
		else {
			$details = "You can't accept this user as a friend.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = ($details) ? 0 : 1;
		break;

	case 'friend_add':
		$id = prepare_numeric($_GET['id']);

		if (validate_level($user, 'member') && $user->user_id != $id) {
			$sql->modify('friend_add', ' INSERT IGNORE INTO mangadex_user_relations (user_id, relation_id, target_user_id, accepted) VALUES (?, 1, ?, 0) ', [$user->user_id, $id]);

			$memcached->delete("user_{$user->user_id}_friends_user_ids");
			$memcached->delete("user_{$user->user_id}_pending_friends_user_ids");
			$memcached->delete("user_{$id}_friends_user_ids");
			$memcached->delete("user_{$id}_pending_friends_user_ids");
			$details = $id;
		}
		else {
			$details = "You can't add this user as a friend.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = ($details) ? 0 : 1;
		break;

	case 'friend_remove':
		$id = prepare_numeric($_GET['id']);

		if (validate_level($user, 'member') && $user->user_id != $id) {
			$sql->modify('friend_remove', ' DELETE FROM mangadex_user_relations WHERE (user_id = ? AND relation_id = 1 AND target_user_id = ?) OR (user_id = ? AND relation_id = 1 AND target_user_id = ?) LIMIT 2 ', [$user->user_id, $id, $id, $user->user_id]);

			$memcached->delete("user_{$user->user_id}_friends_user_ids");
			$memcached->delete("user_{$user->user_id}_pending_friends_user_ids");
			$memcached->delete("user_{$id}_friends_user_ids");
			$memcached->delete("user_{$id}_pending_friends_user_ids");
			$details = $id;
		}
		else {
			$details = "You can't remove this user as a friend.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = ($details) ? 0 : 1;
		break;

	case 'user_block':
		$id = prepare_numeric($_GET['id']);

		$target_user = new User($id, 'user_id');

		// Dont allow blocking of staff members
		if (validate_level($user, 'member') && $user->user_id != $id && !validate_level($target_user, 'pr')) {
			$sql->modify('user_block', ' DELETE FROM mangadex_user_relations WHERE (user_id = ? AND relation_id = 1 AND target_user_id = ?) OR (user_id = ? AND relation_id = 1 AND target_user_id = ?) LIMIT 2 ', [$user->user_id, $id, $id, $user->user_id]);

			$sql->modify('user_block', ' INSERT IGNORE INTO mangadex_user_relations (user_id, relation_id, target_user_id, accepted) VALUES (?, 0, ?, 1) ', [$user->user_id, $id]);

			$memcached->delete("user_{$user->user_id}_blocked_user_ids");
			$memcached->delete("user_{$user->user_id}_friends_user_ids");
			$memcached->delete("user_{$user->user_id}_pending_friends_user_ids");
			$memcached->delete("user_{$id}_friends_user_ids");
			$memcached->delete("user_{$id}_pending_friends_user_ids");

			$details = $id;
		}
		else {
			$details = "You can't block this user.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = ($details) ? 0 : 1;
		break;

	case 'user_unblock':
		$id = prepare_numeric($_GET['id']);

		if (validate_level($user, 'member') && $user->user_id != $id) {
			$sql->modify('user_unblock', ' DELETE FROM mangadex_user_relations WHERE user_id = ? AND relation_id = 0 AND target_user_id = ? LIMIT 1 ', [$user->user_id, $id]);

			$memcached->delete("user_{$user->user_id}_blocked_user_ids");
			$details = $id;
		}
		else {
			$details = "You can't unblock this user.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = ($details) ? 0 : 1;
		break;

    case 'set_user_note':
        if ($user->premium === 0) {
            die('You do not have the right permissions to do that.');
        }

        $affectedUserId = prepare_numeric($_POST['user_id']);
        $note = $_POST['note'];

        if (strlen($note) > 50) {
            die('The note is too long.');
        }

        if (!empty($note)) {
            // Check the note limit
            $existingNotes =  $sql->prep(
                'user_notes_overview',
                'SELECT affected_user_id FROM mangadex_user_notes WHERE creator_user_id = ?',
                [
                    $user->user_id
                ],
                'fetchAll',
                PDO::FETCH_COLUMN,
                -1
            );
            $allowedNotes = [0, 50, 200, 1000, 1000, 1000][$user->premium];

            if (!in_array($affectedUserId, $existingNotes, false) && count($existingNotes) >= $allowedNotes) {
                die('You have reached the maximum amount of notes');
            }

            // Insert/Update
            $sql->modify(
                'set_user_note',
                'INSERT INTO mangadex_user_notes VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE note = VALUES(note)',
                [
                    $user->user_id,
                    $affectedUserId,
                    $note
                ]
            );
        } else {
            // Delete
            $sql->modify(
                'set_user_note',
                'DELETE FROM mangadex_user_notes WHERE creator_user_id = ? AND affected_user_id = ?',
                [
                    $user->user_id,
                    $affectedUserId
                ]
            );
        }
        $memcached->delete("user_{$user->user_id}_notes");

        break;


	case 'change_password':
		$old_password = $_POST['old_password'];
		$new_password1 = $_POST['new_password1'];
		$new_password2 = $_POST['new_password2'];

		if (password_verify($old_password, $user->password)) { //verify the hash
			$password_test = ($new_password1 == $new_password2 && strlen($new_password1) >= 8); //return TRUE

			$new_hash = password_hash($new_password1, PASSWORD_DEFAULT);

			if ($password_test) {
				$sql->modify('change_password', ' UPDATE mangadex_users SET password = ? WHERE user_id = ? LIMIT 1 ', [$new_hash, $user->user_id]);

				$memcached->delete("user_$user->user_id");

				$to = $user->email;
				$subject = "MangaDex: Change Password - $user->username";
				$body = "You have successfully changed your password for MangaDex. \n\nUsername: $user->username \nPassword: (your chosen password) ";

				send_email($to, $subject, $body);
			}
			else {
				$details = 'Your new password is too short.';
				print display_alert('danger', 'Failed', $details); //too short
			}
		}
		else {
			$details = 'Incorrect password.';
			print display_alert('danger', 'Failed', $details); //wrong password
		}

		$result = ($details) ? 0 : 1;
		break;

	case 'supporter_settings':
		$show_premium_badge = !empty($_POST['show_supporter_badge']) ? 1 : 0;
		$show_mah_badge = !empty($_POST['show_mah_badge']) ? 1 : 0;
		
		if ($user->user_id) {
			if ($user->premium) {
				$sql->modify('supporter_settings', ' UPDATE mangadex_user_options SET show_premium_badge = ? WHERE user_id = ? LIMIT 1 ', [$show_premium_badge, $user->user_id]);
			}
			if (count($user->get_clients())) {
				$approvaltime = $user->get_client_approval_time();
				if ($show_mah_badge && $approvaltime < 1593561600) {
					$show_mah_badge = 2;					
				}				
				$sql->modify('supporter_settings', ' UPDATE mangadex_user_options SET show_md_at_home_badge = ? WHERE user_id = ? LIMIT 1 ', [$show_mah_badge, $user->user_id]);
			}

			$memcached->delete("user_$user->user_id");
		}
		else {
			$details = 'Your session has timed out. Please log in again.';
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = 1;
		break;

	case 'upload_settings':
		$lang_id = prepare_numeric($_POST["lang_id"]);
		$group_id = prepare_numeric($_POST["group_id"]) ?? 0;

		if ($user->user_id) {
			$sql->modify('upload_settings', ' UPDATE mangadex_users SET upload_group_id = ?, upload_lang_id = ? WHERE user_id = ? LIMIT 1 ', [$group_id, $lang_id, $user->user_id]);

			$memcached->delete("user_$user->user_id");
		}
		else {
			$details = 'Your session has timed out. Please log in again.';
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = 1;
		break;

	case 'reader_settings':
		$reader = !empty($_POST['reader']) ? 1 : 0;
		$swipe_direction = !empty($_POST['swipe_direction']) ? 1 : 0;
		$reader_click = !empty($_POST['reader_click']) ? 1 : 0;
		$post_sensitivity = prepare_numeric($_POST['swipe_sensitivity']);
		$reader_mode = prepare_numeric($_POST['reader_mode']) ?? 0;
		$image_fit = prepare_numeric($_POST['image_fit']) ?? 0;
		$img_server = prepare_numeric($_POST['img_server']);
		if ($reader_mode && $image_fit == 2)
			$image_fit = 0;

		$swipe_sensitivity = $post_sensitivity * 25 + 25;
		if ($swipe_sensitivity < 25)
			$swipe_sensitivity = 25;
		elseif ($swipe_sensitivity > 150)
			$swipe_sensitivity = 150;

		if ($user->user_id) {
			$sql->modify('reader_settings', ' 
			UPDATE mangadex_users SET reader = ?, swipe_direction = ?, swipe_sensitivity = ?, reader_mode = ?, reader_click = ?, image_fit = ?, img_server = ? WHERE user_id = ? LIMIT 1 
			', [$reader, $swipe_direction, $swipe_sensitivity, $reader_mode, $reader_click, $image_fit, $img_server, $user->user_id]);

			$memcached->delete("user_$user->user_id");
		}
		else {
			$details = 'Your session has timed out. Please log in again.';
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = 1;
		break;

	case 'change_profile':
		$lang_id = prepare_numeric($_POST['lang_id']);
		$website = str_replace(['javascript:'], '', htmlentities($_POST['website']));
		$user_bio = str_replace(['javascript:'], '', htmlentities($_POST['user_bio']));
		$old_file = $_FILES['file']['name'];

		// Make sure website has http://
        if (!empty($website) && stripos($website, 'http://') === false && stripos($website, 'https://') === false)
            $website = 'http://'.$website;

		if ($_FILES['file'] && $old_file) {
            $error .= validate_image($_FILES['file']);

            // Check for Avatar Change Restriction
            if ($user->has_active_restriction(USER_RESTRICTION_CHANGE_AVATAR)) {
                $fail_reason = $user->get_restriction_message(USER_RESTRICTION_CHANGE_AVATAR) ?? 'Avatar change failed!';
                $error .= display_alert("danger", "Failed", $fail_reason);
            }
        }

        // Check for Biography Change Restriction
        if ($user->has_active_restriction(USER_RESTRICTION_CHANGE_BIOGRAPHY)) {
            if ($user->user_bio !== $user_bio || $user->user_website !== $website) {
                $fail_reason = $user->get_restriction_message(USER_RESTRICTION_CHANGE_BIOGRAPHY) ?? 'Biography/Website change failed!';
                $error .= display_alert("danger", "Failed", $fail_reason);
            }
        }

		if (!$user->user_id)
			$error .= display_alert('danger', 'Failed', 'Your session has timed out. Please log in again.'); //success
		
		if (!validate_level($user, 'member'))
			$error .= display_alert('danger', 'Failed', 'You need to be at least a member.'); //success

		if (!$error) {
			$sql->modify('change_profile', ' UPDATE mangadex_users SET language = ?, user_website = ?, user_bio = ? WHERE user_id = ? LIMIT 1 ', [$lang_id, $website, $user_bio, $user->user_id]);

			if ($old_file) {
				$arr = explode('.', $_FILES['file']['name']);
				$ext = strtolower(end($arr));

				if ($user->avatar)
					@unlink(ABS_DATA_BASEPATH . "/avatars/$user->user_id.$user->avatar");

				move_uploaded_file($_FILES["file"]["tmp_name"], ABS_DATA_BASEPATH . "/avatars/$user->user_id.$ext");

				$sql->modify('change_profile', ' UPDATE mangadex_users SET avatar = ? WHERE user_id = ? LIMIT 1 ', [$ext, $user->user_id]);
			}

			if (strpos($user_bio, 'haruki.ga') !== FALSE) {
				$token = rand_string(32);
				$sql->modify('change_profile', ' UPDATE mangadex_users SET password = ?, token = ?, user_website = NULL, user_bio = NULL, avatar = NULL WHERE user_id = ? ', ['compromised', $token, $user->user_id]);

				$guard->destroySession();

				$memcached->delete("user_$user->user_id");

				if (IS_NOJS) redirect_url('/index.php');
			}

			$memcached->delete("user_$user->user_id");

			$details = $user->user_id;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = ($details) ? 0 : 1;
		break;

	case 'site_settings':
		$theme_id = prepare_numeric($_POST['theme_id']);
		$navigation = prepare_numeric($_POST['navigation']);
		$list_privacy = prepare_numeric($_POST['list_privacy']);
		$reader = $_POST['reader'] ?? 0;
		$data_saver = $_POST['data_saver'] ?? 0;
		$display_lang_id = prepare_numeric($_POST['display_lang_id']);
		$old_file = $_FILES['file']['name'];
		$hentai_mode = prepare_numeric($_POST["hentai_mode"]);
        $show_unavailable = prepare_numeric($_POST["show_unavailable"]);
        $display_moderated = prepare_numeric($_POST["display_moderated"]);
        $latest_updates = prepare_numeric($_POST["latest_updates"]);
		$default_lang_ids = (isset($_POST["default_lang_ids"]) && count($_POST["default_lang_ids"]) < 20) ? implode(",", $_POST["default_lang_ids"]) : "";
		$reset_list_banner = isset($_POST["reset_list_banner"]) ? 1 : 0;
		$excluded_genres = (isset($_POST['manga_genres']) ? array_map('intval', $_POST['manga_genres']) : []);
        sort($excluded_genres);

		if ($_FILES['file'] && $old_file && !$reset_list_banner)
			$error .= validate_image($_FILES['file']);

		if (!$user->user_id)
			$error .= display_alert('danger', 'Failed', "Your session has timed out. Please log in again."); //success
		
		if (!validate_level($user, 'member'))
			$error .= display_alert('danger', 'Failed', 'You need to be at least a member.'); //success

		if (!$error) {
			$sql->modify('site_settings', ' 
			UPDATE mangadex_users SET hentai_mode = ?, display_moderated = ?, latest_updates = ?, reader = ?, default_lang_ids = ?, style = ?, display_lang_id = ?, list_privacy = ?, excluded_genres = ?, navigation = ?, show_unavailable = ? WHERE user_id = ? LIMIT 1 
			', [$hentai_mode, $display_moderated, $latest_updates, (int) $reader, $default_lang_ids, $theme_id, $display_lang_id, $list_privacy, implode(',', $excluded_genres), $navigation, $show_unavailable, $user->user_id]);
			
			$sql->modify('site_settings', ' UPDATE mangadex_user_options SET data_saver = ? WHERE user_id = ? LIMIT 1 ', [(int) $data_saver, $user->user_id]);
			
			if ($old_file && !$reset_list_banner) {
				$arr = explode(".", $_FILES["file"]["name"]);
				$ext = strtolower(end($arr));

				$oldFilename = ABS_DATA_BASEPATH . "/lists/$user->user_id.$user->list_banner";
				if ($user->list_banner && file_exists($oldFilename))
					@unlink($oldFilename);

				move_uploaded_file($_FILES["file"]["tmp_name"], ABS_DATA_BASEPATH . "/lists/$user->user_id.$ext");

				$sql->modify('site_settings', ' UPDATE mangadex_users SET list_banner = ? WHERE user_id = ? LIMIT 1 ', [$ext, $user->user_id]);
			}
			elseif ($reset_list_banner) {
				if ($user->list_banner)
					@unlink(ABS_DATA_BASEPATH . "/lists/$user->user_id.$user->list_banner");

				$sql->modify('site_settings', " UPDATE mangadex_users SET list_banner = '' WHERE user_id = ? LIMIT 1 ", [$user->user_id]);
			}

			$memcached->delete("user_$user->user_id");

			$details = $user->user_id;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = ($details) ? 0 : 1;
		break;

	case 'homepage_settings':
		$theme_id = prepare_numeric($_POST['theme_id']);
		$display_lang_id = prepare_numeric($_POST['display_lang_id']);
		$default_lang_ids = (isset($_POST["default_lang_ids"]) && count($_POST["default_lang_ids"]) < 20) ? implode(",", $_POST["default_lang_ids"]) : "";
		$hentai_mode = isset($_POST['hentai_mode']) ? prepare_numeric($_POST['hentai_mode']) : 0;

		switch ($hentai_mode) {
			case 1:
				setcookie('mangadex_h_toggle', $hentai_mode, $timestamp + (86400 * 3650), '/', DOMAIN); // 86400 = 1 day
				break;

			case 2:
				setcookie('mangadex_h_toggle', $hentai_mode, $timestamp + (86400 * 3650), '/', DOMAIN); // 86400 = 1 day
				break;

			case 0:
			default:
				setcookie('mangadex_h_toggle', '', $timestamp - 3600, '/', DOMAIN);
				break;
		}

		if (!$user->user_id) {
			setcookie("mangadex_theme", $theme_id, $timestamp + 3600, "/"); // 3600 = 1 hour
			setcookie("mangadex_filter_langs", $default_lang_ids, $timestamp + 3600, "/"); // 3600 = 1 hour
			setcookie('mangadex_display_lang', $display_lang_id, $timestamp + 3600, '/', DOMAIN); // 3600 = 1 hour
		}
		else {
			$sql->modify('homepage_settings', ' UPDATE mangadex_users SET style = ?, default_lang_ids = ?, display_lang_id = ? WHERE user_id = ? LIMIT 1 ', [$theme_id, $default_lang_ids, $display_lang_id, $user->user_id]);

			$memcached->delete("user_$user->user_id");
		}

		$details = '';
		$result = 1;
		break;

	case 'list_settings':
		$list_privacy = prepare_numeric($_POST['list_privacy']);
		$old_file = $_FILES['file']['name'];
		$reset_list_banner = isset($_POST["reset_list_banner"]) ? 1 : 0;

		if ($_FILES['file'] && $old_file && !$reset_list_banner)
			$error .= validate_image($_FILES['file']);

		if (!$user->user_id)
			$error .= display_alert('danger', 'Failed', "Your session has timed out. Please log in again."); //success

		if (!$error) {
			$sql->modify('list_settings', ' UPDATE mangadex_users SET list_privacy = ? WHERE user_id = ? LIMIT 1 ', [$list_privacy, $user->user_id]);

			if ($old_file && !$reset_list_banner) {
				$arr = explode(".", $_FILES["file"]["name"]);
				$ext = strtolower(end($arr));

				if ($user->list_banner)
					@unlink(ABS_DATA_BASEPATH . "/lists/$user->user_id.$user->list_banner");

				move_uploaded_file($_FILES["file"]["tmp_name"], ABS_DATA_BASEPATH . "/lists/$user->user_id.$ext");

				$sql->modify('list_settings', ' UPDATE mangadex_users SET list_banner = ? WHERE user_id = ? LIMIT 1 ', [$ext, $user->user_id]);
			}
			elseif ($reset_list_banner) {
				if ($user->list_banner)
					@unlink(ABS_DATA_BASEPATH . "/lists/$user->user_id.$user->list_banner");

				$sql->modify('list_settings', " UPDATE mangadex_users SET list_banner = '' WHERE user_id = ? LIMIT 1 ", [$user->user_id]);
			}

			$memcached->delete("user_$user->user_id");

			$details = $user->user_id;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = ($details) ? 0 : 1;
		break;

    case 'msg_thread':

        $result = 1;
        try {

            $thread_id = (int)$_POST['id'];
            $page = max(1, (int)$_POST['page']);

            if (!validate_level($user, 'member'))
                throw new \Exception("You must be logged in.");

            $thread = new PM_Thread($thread_id);

            if (!isset($thread->thread_id) || ($thread->sender_id != $user->user_id && $thread->recipient_id != $user->user_id))
                throw new \Exception("This thread does not exist.");

            $limit = defined('DMS_DISPLAY_LIMIT') ? DMS_DISPLAY_LIMIT : 25;
            $messages = new PM_Msgs($thread->thread_id, ($page - 1) * $limit, $limit);

            $html = "";

            $n = 0;
            foreach ($messages as $msg_id => $msg) {
                $parser->parse($msg->text);
                $msg->post_id = $msg_id;

                $html = display_post_v2($msg, $parser->getAsHtml(), $user, 'message') . $html;
                $n++;
            }

            print json_encode([
                'status' => 'success',
                'code' => 200,
                'data' => $html,
                'count' => $n,
                'total' => $thread->total,
            ]);

        } catch (\Exception $e) {
            print json_encode([
                'status' => 'fail',
                'code' => 400,
                'message' => $e->getMessage(),
            ]);
            $result = 0;
        }

        break;

    case 'mod_user_restriction':

        if (!validate_level($user, 'mod')) {
            http_response_code(401);
            $result = 0;
        } else {
            $target_user_id = prepare_numeric($_POST['target_user_id']);
            $mod_user_id = prepare_numeric($_POST['mod_user_id']);
            $restriction_type_id = prepare_numeric($_POST['restriction_type_id']);
            $expiration_reltime = prepare_numeric($_POST['expiration_reltime'] ?? 0);
            $expiration_relstep = prepare_numeric($_POST['expiration_relstep'] ?? 0);
            $expiration_permanent = isset($_POST['expiration_permanent']) && $_POST['expiration_permanent'] === 'on';
            $expiration_timestamp = $expiration_permanent
                ? 4294967295 // Just add max number
                : time() + $expiration_reltime * $expiration_relstep;
            $comment = htmlentities(strip_tags(trim($_POST['comment'])));

            //var_dump($target_user_id, $mod_user_id, $restriction_type_id, $expiration_timestamp, $comment);

            $sql->modify('user_restrictions_all_'.$target_user_id, '
  INSERT INTO mangadex_user_restrictions 
    (target_user_id, restriction_type_id, mod_user_id, expiration_timestamp, comment)
  VALUES
    (?, ?, ?, ?, ?)', [$target_user_id, $restriction_type_id, $mod_user_id, $expiration_timestamp, $comment]);

            // Clear cache
            $memcached->delete('user_restrictions_active_detailed_'.$target_user_id); // Profile page table
            $memcached->delete('user_restrictions_active_'.$target_user_id); // User class method

            $result = 1;
        }

        break;

    case 'mod_lift_user_restriction':

        if (!validate_level($user, 'mod')) {
            http_response_code(401);
            $result = 0;
        } else {
            $restriction_id = prepare_numeric($_POST['restriction_id']);
            $target_user_id = prepare_numeric($_POST['target_user_id']);
            $mod_user_id = prepare_numeric($user->user_id);

            //var_dump($restriction_id, $mod_user_id);

            $sql->modify('user_restrictions_all_'.$target_user_id, '
  UPDATE mangadex_user_restrictions 
  SET
    mod_user_id = ?,
    expiration_timestamp = ?
  WHERE
    restriction_id = ?', [$mod_user_id, time(), $restriction_id]);

            // Clear cache
            $memcached->delete('user_restrictions_active_detailed_'.$target_user_id); // Profile page table
            $memcached->delete('user_restrictions_active_'.$target_user_id); // User class method

            $result = 1;
        }

        break;

    case 'mod_nuke_user_comments':

        if (!validate_level($user, 'mod')) {
            http_response_code(401);
            $result = 0;
        } else {
            $user_id = prepare_numeric($_GET["id"]);

            $posts = $sql->prep('posts_nuke_select', ' 
                SELECT posts.post_id, posts.thread_id, threads.forum_id
                FROM mangadex_forum_posts AS posts
                LEFT JOIN mangadex_threads AS threads
                    ON threads.thread_id = posts.thread_id
                WHERE posts.user_id = ? AND posts.deleted = 0
			', [$user_id], 'fetchAll', PDO::FETCH_ASSOC, -1);

            $sql->modify('posts_nuke_update', ' 
                UPDATE mangadex_forum_posts AS posts
                SET deleted = 1
                WHERE posts.user_id = ?
			', [$user_id]);

            foreach ($posts as $post) {
                $sql->modify("posts_nuke_update_thread_{$post['thread_id']}", '
                    UPDATE mangadex_threads
                    SET thread_posts = IF(thread_posts > 1, thread_posts - 1, 0)
                    WHERE thread_id = ?
                    LIMIT 1
                ', [$post['thread_id']]);
                switch ($post['forum_id']) {
                    case 11:
                        $manga_id = $sql->prep('posts_nuke_memcached_manga', ' SELECT manga_id FROM mangadex_mangas WHERE thread_id = ? LIMIT 1 ', [$post['thread_id']], 'fetchColumn', '', -1);
                        $memcached->delete("manga_$manga_id");
                        break;
                    case 14:
                        $group_id = $sql->prep('posts_nuke_memcached_group', ' SELECT group_id FROM mangadex_groups WHERE thread_id = ? LIMIT 1 ', [$post['thread_id']], 'fetchColumn', '', -1);
                        $memcached->delete("group_$group_id");
                        break;

                }
            }
            $memcached->delete("user_$user_id");
            $details = $user_id;
            print display_alert("success", "Success", "All the posts of user #$user_id have been deleted.");
            $result = 1;
        }

        break;


	case 'admin_edit_user':

	    $is_admin = validate_level($user, 'admin');
	    $is_mod = validate_level($user, 'mod');

		$id = prepare_numeric($_GET["id"]);
		if ($is_admin) {
            $level_id = prepare_numeric($_POST["level_id"]);
            $email = $_POST["email"];
            $username = $_POST["username"];
            $new_pass = $_POST["new_pass"];
            $lang_id = prepare_numeric($_POST["lang_id"]);
            $upload_lang_id = prepare_numeric($_POST["upload_lang_id"]);
            $upload_group_id = prepare_numeric($_POST["upload_group_id"]);
        }
        $avatar = $_POST["avatar"];
        $website = htmlentities($_POST['website']);
        $user_bio = htmlentities($_POST['user_bio']);
        $reset_list_banner = isset($_POST["reset_list_banner"]) ? 1 : 0;
        $reset_avatar = isset($_POST["reset_avatar"]) ? 1 : 0;

		if ($is_mod) {

            $edit_user = new User($id, 'user_id');

            if ($reset_list_banner && $edit_user->list_banner) {
                @unlink(ABS_DATA_BASEPATH . "/lists/{$edit_user->user_id}.{$edit_user->list_banner}");
                $sql->modify('admin_edit_list_banner', "UPDATE mangadex_users SET list_banner = '' WHERE user_id = ?", [$edit_user->user_id]);
            }

            if ($reset_avatar && $edit_user->avatar) {
                @unlink(ABS_DATA_BASEPATH . "/avatars/{$edit_user->user_id}.{$edit_user->avatar}");
                $avatar = '';
            }

            if ($is_admin) {
                $sql->modify('admin_edit_user', ' 
			UPDATE mangadex_users SET username = ?, level_id = ?, email = ?, language = ?, avatar = ?, upload_group_id = ?, upload_lang_id = ?, user_bio = ?, user_website = ? WHERE user_id = ? 
			', [$username, $level_id, $email, $lang_id, $avatar, $upload_group_id, $upload_lang_id, $user_bio, $website, $id]);

				if ($level_id == 0) {
					$sql->modify('admin_edit_user', "DELETE FROM mangadex_pm_threads WHERE sender_id = ?", [$id]);
					$sql->modify('admin_edit_user', "DELETE FROM mangadex_pm_msgs WHERE user_id = ?", [$id]);
					$sql->modify('admin_edit_user', "UPDATE mangadex_users SET avatar = '', user_bio = '', user_website = '' WHERE user_id = ? ", [$id]);
				}

                if ($new_pass) {
                    $password_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                    $sql->modify('admin_edit_user', ' UPDATE mangadex_users SET password = ? WHERE user_id = ? LIMIT 1 ', [$password_hash, $id]);
                }
            } else {
                $sql->modify('admin_edit_user', ' 
			UPDATE mangadex_users SET avatar = ?, user_bio = ?, user_website = ? WHERE user_id = ? 
			', [$avatar, $user_bio, $website, $id]);
            }

			$memcached->delete("user_$id");

			$details = $id;
		}
		else {
			$details = "You can't edit users.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;
}