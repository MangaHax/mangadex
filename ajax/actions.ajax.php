<?php

require_once ('../bootstrap.php');

define('IS_NOJS', (isset($_GET['nojs']) && $_GET['nojs']));

if (!DEBUG && !IS_NOJS && (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) || $_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest"))
    die("Hacking attempt... Go away.");

require_once (ABSPATH . "/scripts/header.req.php");

/*
$user__id = $sql->prep("token_$token", ' SELECT user_id FROM mangadex_users WHERE token = ? LIMIT 1 ', [$token], 'fetchColumn', '', 3600);
$user = new User($user__id, 'user_id');
*/
$guard = \Mangadex\Model\Guard::getInstance();
if (isset($_COOKIE[SESSION_COOKIE_NAME]) || isset($_COOKIE[SESSION_REMEMBERME_COOKIE_NAME])) {
	$guard->tryRestoreSession($_COOKIE[SESSION_COOKIE_NAME] ?? null, $_COOKIE[SESSION_REMEMBERME_COOKIE_NAME] ?? null);
	$user = $guard->hasUser() ? $guard->getUser() : $guard->getUser(0); // Fetch guest record (userid=0) if no user could be restored
} else {
	$user = $guard->getUser(0); // Fetch guest
}


/** @var $sentry Raven_Client */
if (isset($sentry) && isset($user)) {
    $sentry->user_context([
        'id' => $user->user_id,
        'username' => $user->username,
    ]);
}

$details = '';
$error = '';

foreach($_POST as $key => $value) {
	if (is_string($value))
		$_POST[$key] = trim($value);
}

$function = $_GET['function'];

foreach (read_dir('ajax/actions') as $file) {
	require_once (ABSPATH . "/ajax/actions/$file");
} //require every file in actions

switch ($function) {



	/*
	// filter functions
	*/
	case 'hentai_toggle':
		$mode = $_GET['mode'];

		if ($mode == 1) {
			setcookie('mangadex_h_toggle', $mode, $timestamp + (86400 * 3650), '/', DOMAIN); // 86400 = 1 day
			$details = 'Everything displayed.';
		}
		elseif ($mode == 2) {
			setcookie('mangadex_h_toggle', $mode, $timestamp + (86400 * 3650), '/', DOMAIN); // 86400 = 1 day
			$details = 'Only hentai displayed.';
		}
		elseif ($mode == 0) {
			setcookie('mangadex_h_toggle', '', $timestamp - 3600, '/', DOMAIN);
			$details = 'Hentai hidden.';
		}

		print display_alert('success', 'Success', $details);

		$result = 1;
		break;

	case 'set_display_lang':
		$display_lang_id = $_GET['id'];

		if (!$user->user_id)
			setcookie('mangadex_display_lang', $display_lang_id, $timestamp + 86400, '/', DOMAIN); // 86400 = 1 day
		else {
			$sql->modify('set_display_lang', ' UPDATE mangadex_users SET display_lang_id = ? WHERE user_id = ? LIMIT 1 ', [$display_lang_id, $user->user_id]);

			$memcached->delete("user_$user->user_id");
		}

		$details = 'Display language set.';

		print display_alert('success', 'Success', $details);

		$result = 1;
		break;

	case 'set_mangas_view':
		$mode = $_GET['mode'];

		if (!$user->user_id)
			setcookie('mangadex_title_mode', $mode, $timestamp + 86400, '/', DOMAIN);  // 86400 = 1 day
		else {
			$sql->modify('set_mangas_view', ' UPDATE mangadex_users SET mangas_view = ? WHERE user_id = ? LIMIT 1 ', [$mode, $user->user_id]);

			$memcached->delete("user_$user->user_id");
		}

		$details = 'View mode set.';

		print display_alert('success', 'Success', $details);

		$result = 1;
		break;

	/*
	// user functions
	*/


	case 'ban_user':
		$id = prepare_numeric($_GET['id']);

		$target_user = new User($id, 'user_id');

		if (validate_level($user, 'admin') && !validate_level($target_user, 'admin') && validate_level($target_user, 'validating')) {
			$sql->modify('ban_user', ' UPDATE mangadex_users SET level_id = 0 WHERE user_id = ? LIMIT 1 ', [$id]);

			$memcached->delete("user_$id");

			$details = $id;
		}
		else {
			$details = "You can't ban $target_user->username.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case 'unban_user':
		$id = prepare_numeric($_GET['id']);

		$target_user = new User($id, 'user_id');

		if (validate_level($user, 'admin') && !$target_user->level_id) {
			$sql->modify('unban_user', ' UPDATE mangadex_users SET level_id = 3 WHERE user_id = ? LIMIT 1 ', [$id]);

			$memcached->delete("user_$id");

			$details = $id;
		}
		else {
			$details = "You can't unban $target_user->username.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;



	/*
	// message functions
	*/

	case 'msg_reply':
		$id = prepare_numeric($_GET['id']);

		$reply = str_replace(['javascript:'], '', htmlentities($_POST['text']));

		$thread = new PM_Thread($id);

		$recipient_user = new User($thread->recipient_id, 'user_id');
		$sender_user = new User($thread->sender_id, 'user_id');

		// in the context of a pm thread, "sender" is the op rather than necessarily whoever is currently sending the message
		// so in case the current replying user is the "recipient", flip the variables around to match the correct meaning
		if ($thread->recipient_id == $user->user_id) {
			$recipient_user = $sender_user;
			$sender_user = $user;
		}

		/*$canReceiveDms = \validate_level($user, 'pr') // Staff can always send dms
			|| ($recipient_user->dm_privacy ?? 0) < 1 // User has no dm restriction set
			|| \in_array( // sender is a friend of recipient
				$user->user_id,
				\array_map(static function ($u) {
					return $u['user_id'];
				},
				\array_filter($recipient_user->get_friends_user_ids(), static function ($u) {
					return $u['accepted'] === 1;
				})
				),
				true
			);*/

		$sender_blocked = $sender_user->get_blocked_user_ids();
		$recipient_blocked = $recipient_user->get_blocked_user_ids();

		// DM restriction if there is an active restriction and the sender isnt staff. restricted users can always message staff
		$dm_restriction = $user->has_active_restriction(USER_RESTRICTION_CREATE_DM) && !validate_level($recipient_user, 'mod');

		if (/*$canReceiveDms &&*/($user->user_id == $thread->sender_id || $user->user_id == $thread->recipient_id) && !isset($sender_blocked[$thread->recipient_id]) && !isset($recipient_blocked[$thread->sender_id]) && !$dm_restriction) {
			$sql->modify('msg_reply', ' INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text) VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$id, $user->user_id, $reply]);

			if ($thread->sender_id == $user->user_id)
				$sql->modify('msg_reply', ' UPDATE mangadex_pm_threads SET recipient_read = 0, recipient_deleted = 0, thread_timestamp = UNIX_TIMESTAMP() WHERE thread_id = ? LIMIT 1 ', [$id]);
			else
				$sql->modify('msg_reply', ' UPDATE mangadex_pm_threads SET sender_read = 0, sender_deleted = 0, thread_timestamp = UNIX_TIMESTAMP() WHERE thread_id = ? LIMIT 1 ', [$id]);

			$memcached->delete("user_{$thread->recipient_id}_unread_msgs");
			$memcached->delete("user_{$thread->sender_id}_unread_msgs");
			$memcached->delete("PM_{$thread->thread_id}");

			$details = $id;
		}
		else {
			if (isset($sender_blocked[$thread->recipient_id]))
				$details = "You can't reply to the message because they are blocked.";
			elseif (isset($recipient_blocked[$thread->sender_id]))
				$details = "You can't reply to the message because they are blocked.";
			/*elseif (!$canReceiveDms)
				$details = "You can't send messages to this user until you have accepted each other as friends.";*/
			elseif ($dm_restriction)
                $details = $user->get_restriction_message(USER_RESTRICTION_CREATE_DM) ?? "You can't reply to this dm.";
            else
				$details = "You can't reply on thread $id.";

			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case 'msg_send':
		$recipient = htmlentities($_POST['recipient']);
		$subject = htmlentities($_POST['subject']);
		$message = str_replace(['javascript:'], '', htmlentities($_POST['text']));

		// Process captcha
		if (defined('REQUIRE_CAPTCHA') && REQUIRE_CAPTCHA && !validate_level($user, 'pr')) {

			if (isset($_POST['g-recaptcha-response'])) {
				$captcha = $_POST['g-recaptcha-response'];
			}

			//validate captcha
			if (!isset($captcha)) {
				// This might happen if the google captcha was blocked or this is a bot request
				$captcha_validate = ['success' => false];
			} else if ($captcha) {
				try {
					$captcha_validate = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".GOOGLE_CAPTCHA_SECRET."&response=$captcha&remoteip=$ip"), true);
				} catch (\Exception $e) {
					// This might happen if the google api timed out.
					$captcha_validate = ['success' => false];
				}
			}
		} else {
			// Probably a dev environment or captcha is currently disabled, just return success then.
			$captcha = true;
			$captcha_validate = ['success' => true];
		}
		if (!isset($captcha_validate['success'])) {
			$captcha_validate['success'] = false;
		}

		$last_message_timestamp = $sql->prep('last_message_timestamp', ' SELECT timestamp FROM mangadex_pm_msgs WHERE user_id = ? ORDER BY timestamp DESC LIMIT 1 ', [$user->user_id], 'fetchColumn', '', -1);

		$recipient_id = $sql->prep('recipient_id', ' SELECT user_id FROM mangadex_users WHERE username = ?', [$recipient], 'fetchColumn', '', -1);
		$recipient_user = new User($recipient_id, 'user_id');

		$canReceiveDms = \validate_level($user, 'pr') // Staff can always send dms
			|| ($recipient_user->dm_privacy ?? 0) < 1 // User has no dm restriction set
			|| \in_array( // sender is a friend of recipient
				$user->user_id,
				\array_map(static function ($u) {
					return $u['user_id'];
				},
				\array_filter($recipient_user->get_friends_user_ids(), static function ($u) {
					return $u['accepted'] === 1;
				})
				),
				true
			);

		$user_blocked = $user->get_blocked_user_ids();
		$recipient_blocked = $recipient_user->get_blocked_user_ids();

        // DM restriction if there is an active restriction and the sender isnt staff. restricted users can always message staff
        $dm_restriction = $user->has_active_restriction(USER_RESTRICTION_CREATE_DM) && !validate_level($recipient_user, 'mod');
        // staff members ignore banned words and dm timeout
        $has_banned_word = !validate_level($user, "pr") && (strpos_arr($message, SPAM_WORDS) !== FALSE || strpos_arr($subject, SPAM_WORDS) !== FALSE);
        $has_dmed_recently = !validate_level($user, "pr") && ($timestamp - $last_message_timestamp < 30);

        $is_valid_recipient = $canReceiveDms && $recipient_id && $recipient_id != $user->user_id;
		$is_blocked = isset($user_blocked[$recipient_id]) || isset($recipient_blocked[$user->user_id]);

		if(!validate_level($user, 'member') || $dm_restriction){
			$details = "You can't send messages.";
		}
		else if ($has_banned_word) {
			$thread_id = $sql->modify('msg_send', ' INSERT INTO mangadex_pm_threads (thread_id, thread_subject, sender_id, recipient_id, thread_timestamp, sender_read, recipient_read, sender_deleted, recipient_deleted)
				VALUES (NULL, ?, ?, ?, UNIX_TIMESTAMP(), 1, 1, 0, 1) ', [$subject, $user->user_id, $recipient_id]);

			$sql->modify('msg_send', ' INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text)
				VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$thread_id, $user->user_id, $message]);

			$memcached->delete("user_{$recipient_id}_unread_msgs");

			$details = $thread_id;
		} else if ($has_dmed_recently) {
			$details = "Please wait before sending another message.";
		} else if (!$canReceiveDms) {
			$details = "You can't send messages to this user until you have accepted each other as friends.";
		} else if (!$is_valid_recipient) {
			$details = "$recipient is an invalid recipient.";
		} else if ($is_blocked) {
			$details = "$recipient has blocked you or you have blocked them.";
		} else if (!$captcha_validate['success']) {
			$details = 'You need to solve the captcha to send messages.';
		} else {
			$thread_id = $sql->modify('msg_send', ' INSERT INTO mangadex_pm_threads (thread_id, thread_subject, sender_id, recipient_id, thread_timestamp, sender_read, recipient_read, sender_deleted, recipient_deleted)
				VALUES (NULL, ?, ?, ?, UNIX_TIMESTAMP(), 1, 0, 0, 0) ', [$subject, $user->user_id, $recipient_id]);

			$sql->modify('msg_send', ' INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text)
				VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$thread_id, $user->user_id, $message]);

			$memcached->delete("user_{$recipient_id}_unread_msgs");

			$details = $thread_id;
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		if(!$result){
			print display_alert('danger', 'Failed', $details); //fail
		}
		break;

	case 'msg_del':
		if ($user->user_id && !empty($_POST['msg_ids']) && is_array($_POST['msg_ids'])) {
			foreach ($_POST['msg_ids'] as $id) {

				$id = prepare_numeric($id);
				$thread = new PM_Thread($id);

				if ($user->user_id == $thread->sender_id)
					$sql->modify('msg_del', ' UPDATE mangadex_pm_threads SET sender_deleted = 1 WHERE thread_id = ? LIMIT 1 ', [$id]);
				else
					$sql->modify('msg_del', ' UPDATE mangadex_pm_threads SET recipient_deleted = 1 WHERE thread_id = ? LIMIT 1 ', [$id]);

                $memcached->delete("PM_{$thread->thread_id}");
			}
		}
		else {
			if (!$user->user_id)
				$details = "Your session has timed out. Please log in again.";
			else
				$details = "No messages selected.";

			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	/*
	// mod functions
	*/



	/*
	// other functions
	*/

	case 'translate':
		$id = prepare_numeric($_GET['id']);
		$json = json_encode($_POST);

		if (in_array($user->user_id, TL_USER_IDS) || validate_level($user, 'gmod')) {
			$sql->modify('translate', ' UPDATE mangadex_languages SET navbar = ? WHERE lang_id = ? LIMIT 1 ', [$json, $id]);

			$memcached->delete("lang_$id");

			$details = $id;
		}
		else {
			$details = "Denied.";
			print display_alert('danger', 'Failed', $details); // fail
		}

		$result = ($details) ? 0 : 1;
		break;

	case "read_announcement":
		if (validate_level($user, 'member')) {
			$sql->modify('read_announcement', ' UPDATE mangadex_users SET read_announcement = 1 WHERE user_id = ? LIMIT 1 ', [$user->user_id]);

			$memcached->delete("user_$user->user_id");

			$result = 1;
		}
		break;

    case 'report_submit':
        $item_id = prepare_numeric($_POST['item_id']);
        $type_id = prepare_numeric($_POST['type_id']);
        $reason_id = prepare_numeric($_POST['reason_id']);
        $info = strip_tags($_POST['info']);

        if (!validate_level($user, 'member'))
            die(json_encode(['status' => 'fail', 'message' => 'You must be logged in to submit reports!']));

        $report_restriction = $user->has_active_restriction(USER_RESTRICTION_CREATE_REPORT) && !validate_level($user, 'mod');

        $reasons = [];
        foreach ((new Report_Reasons())->toArray() AS $row) {
            $reasons[$row['id']] = $row;
        }

        if ($report_restriction)
            die(json_encode(['status' => 'fail', 'message' => $details = $user->get_restriction_message(USER_RESTRICTION_CREATE_REPORT) ?? 'You can\'t submit reports at this time!']));

        if (!isset($reasons[$reason_id]))
            die(json_encode(['status' => 'fail', 'message' => 'You must specify a valid reason!']));

        if ($reasons[$reason_id]['is_info_required'] && empty($info))
            die(json_encode(['status' => 'fail', 'message' => 'You must add a short info text to this report reason!']));

        if ($type_id < 1 || !isset(REPORT_TYPES[$type_id]))
            die(json_encode(['status' => 'fail', 'message' => 'You must specify a valid item type!']));

        // Check if there already is a processed report for this comment
        try {
            $autoDecline = $sql->prep(
                "comment_report_check_$item_id",
                'SELECT COUNT(*) FROM mangadex_reports WHERE item_id = ? AND state != 0',
                [$item_id],
                'fetchColumn',
                null,
                -1
            ) > 0;
        } catch(\PDOException $e) {
            $autoDecline = false;
        }

        try {
            $sql->modify(
                'report_submit', '
                  INSERT INTO mangadex_reports (item_id, type_id, reason_id, created, info, user_id, state, updated, mod_id)
                  VALUES (?,?,?,?,?,?,?,?,?)',
                [
                    $item_id,
                    $type_id,
                    $reason_id,
                    time(),
                    empty($info) ? null : trim($info),
                    $user->user_id,
                    $autoDecline ? 2 : 0,
                    $autoDecline ? time() : null,
                    $autoDecline ? 1 : null
                ]
            );

            $memcached->delete('mod_report_count');
        } catch (\PDOException $e) {
            if (stripos($e->getMessage(), 'integrity constraint violation')) {
                die(json_encode(['status' => 'fail', 'message' => 'You already have a report of this type pending!']));
            }
            else {
                die(json_encode(['status' => 'fail', 'message' => 'There was an issue submitting the report. Please try again later!']));
            }
        }

        $memcached->delete('mod_general_report_count');

        echo json_encode(['status' => 'success']);

        if (!$autoDecline) {
            post_on_discord(DISCORD_WEBHOOK_REPORT, [
                'username' => $user->username,
                'embeds' => [
                    [
                        'title' => 'Comment Report',
                        'description' => $reasons[$reason_id]['text'],
                        'url' => URL . 'mod/reports',
                        'footer' => [
                            'text' => $info
                        ]
                    ]
                ]
            ]);
        }

        $result = 1;
        break;

    case 'report_setstate':

        $id = prepare_numeric($_POST['id']);
        $state = prepare_numeric($_POST['state']);

        if (!validate_level($user, 'mod'))
            die(json_encode(['status' => 'fail', 'message' => 'Only staff can change report states!']));

        try {
            $sql->modify('report_changestate', 'UPDATE mangadex_reports SET updated = ?, mod_id = ?, state = ? WHERE id = ?', [time(), $user->user_id, $state, $id]);
        } catch (\PDOException $e) {
			trigger_error($e->getMessage(), E_USER_ERROR);
            die(json_encode(['status' => 'fail', 'message' => 'There was an issue updating the request. Please try again later!']));
        }

        $memcached->delete('mod_general_report_count');

        echo json_encode(['status' => 'success']);

        $result = 1;
        break;

	case "admin_ip_unban":
		$ip_unban = $_POST['ip'];

		if (validate_level($user, 'admin')) {
		    // Check if this is an ip that is in the database
            $affectedRows = $sql->modify('ip_unban', "DELETE FROM mangadex_ip_bans WHERE ip = ?", [$ip_unban]);

			if ($affectedRows < 1 && $memcached->delete($ip_unban) === FALSE && $memcached->delete("api_$ip_unban") === FALSE && $memcached->delete("login_$ip_unban") === FALSE) {
				$details = "IP is not on the ban list.";
				print display_alert('danger', 'Failed', $details); // fail
			} else {
			    $memcached->delete('ip_banlist');
            }
		}

		$result = ($details) ? 0 : 1;
		break;

    case "admin_ip_ban":
		$ip_ban = $_POST['ip'];
		$expires = (int)$_POST['expires'];

		if (validate_level($user, 'admin')) {
			$sql->modify('ban_ip', "INSERT IGNORE INTO mangadex_ip_bans (ip, expires) VALUES (?, ?)", [$ip_ban, time()+60*60*$expires]);
            $memcached->delete('ip_banlist');
		}

		$result = ($details) ? 0 : 1;
		break;

    case "admin_add_tempmail":
		$tempmail = $_POST['tempmail'];

		if (validate_level($user, 'admin')) {
			$sql->modify('add_tempmail', "INSERT IGNORE INTO mangadex_tempmail (id, host) VALUES (NULL, ?)", [$tempmail]);
			$memcached->delete('tempmail');
		}

		$result = ($details) ? 0 : 1;
		break;

    case "admin_remove_tempmail":
		$tempmail = $_POST['tempmail'];

		if (validate_level($user, 'admin')) {
			$sql->modify('remove_tempmail', "DELETE FROM mangadex_tempmail WHERE host LIKE ? LIMIT 1 ", [$tempmail]);
		}

		$result = ($details) ? 0 : 1;
		break;

	case "override":
		$id = $_GET['id'];

		if (validate_level($user, 'mod')) {
			$sql->modify('override', "UPDATE mangadex_user_stats SET chapters_read = 101 WHERE user_id = ? LIMIT 1 ", [$id]);
		}

		$result = ($details) ? 0 : 1;
		break;
		
	case "banner_upload":
		$file = $_FILES["file"];
		$user_id = prepare_numeric($_POST["user_id"]);
		$is_anonymous = isset($_POST["is_anonymous"]) ? 1 : 0;
		$is_enabled = isset($_POST["is_enabled"]) ? 1 : 0;
		$file_extension = strtolower(end(explode(".", $file["name"])));

		if($file["error"] != UPLOAD_ERR_OK){
			$error .= display_alert('danger', 'Failed', "File upload error.");
		}
		if(!validate_level($user, 'pr')) {
			$error .= display_alert('danger', 'Failed', "You can't upload banners.");
		}
		if(!in_array($file_extension, ALLOWED_IMG_EXT)){
			$error .= display_alert('danger', 'Failed', "Illegal file extension.");
		}
		
		if(!$error){
			try {
				$banner_id = $sql->modify('banner_upload',
					"INSERT INTO mangadex_banners (user_id, is_anonymous, is_enabled, ext) VALUES (?, ?, ?, ?)",
					[$user_id, $is_anonymous, $is_enabled, $file_extension]);
				move_uploaded_file($file["tmp_name"], ABS_DATA_BASEPATH . "/banners/affiliatebanner$banner_id.$file_extension");
				$memcached->delete("banners_all");
				$memcached->delete("banners_enabled");
			}
			catch(Exception $e){
				$error .= display_alert('danger', 'Failed', "Database error.");
			}
		}
		if($error){
			$details = $error;
			print $error;
		}
		$result = $details ? 0 : 1;
		break;

	case "banner_edit":
		$file = $_FILES["file"];
		$banner_id = prepare_numeric($_GET["banner_id"]);
		$user_id = prepare_numeric($_POST["user_id"]);
		$is_anonymous = isset($_POST["is_anonymous"]) ? 1 : 0;
		$is_enabled = isset($_POST["is_enabled"]) ? 1 : 0;
		
		if($file["error"] == UPLOAD_ERR_NO_FILE){
			$file_extension = $sql->prep("banner_ext", "SELECT ext FROM mangadex_banners WHERE banner_id = ?", [$banner_id], "fetch", PDO::FETCH_ASSOC, -1)["ext"];
		}
		else if($file["error"] == UPLOAD_ERR_OK){
			$file_extension = strtolower(end(explode(".", $file["name"])));
		}
		else{
			$error .= display_alert('danger', 'Failed', "File upload error.");
		}

		if(!validate_level($user, 'pr')) {
			$error .= display_alert('danger', 'Failed', "You can't edit banners.");
		}
		if(!in_array($file_extension, ALLOWED_IMG_EXT)){
			$error .= display_alert('danger', 'Failed', "Illegal file extension.");
		}

		if(!$error){
			try {
				$sql->modify('banner_edit',
					"UPDATE mangadex_banners SET user_id = ?, is_anonymous = ?, is_enabled = ?, ext = ? WHERE banner_id = ?",
					[$user_id, $is_anonymous, $is_enabled, $file_extension, $banner_id]);
				if($file["error"] == UPLOAD_ERR_OK){
					move_uploaded_file($file["tmp_name"], ABS_DATA_BASEPATH . "/banners/affiliatebanner$banner_id.$file_extension");
				}
				$memcached->delete("banners_all");
				$memcached->delete("banners_enabled");
			}
			catch(Exception $e){
				$error .= display_alert('danger', 'Failed', "Database error.");
			}
		}
		if($error){
			$details = $error;
			print $error;
		}
		$result = $details ? 0 : 1;
		break;
}
/*
if (!in_array($function, ['manga_follow', 'manga_unfollow']))
	$sql->modify('action_log', ' INSERT INTO mangadex_logs_actions (action_id, action_name, action_user_id, action_timestamp, action_ip, action_result, action_details)
		VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?, ?, ?) ', [$function, $user->user_id, $ip, $result, strlen($details) > 128 ? substr($details, 0, 128) : $details]);
*/
?>
