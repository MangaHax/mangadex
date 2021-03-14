<?php

use Mangadex\Exception\RemoteFileUploadFailed;
use Mangadex\Model\RemoteFileUploader;

switch ($function) {

    case "chapter_mark_unread":
		$ids = array_map(function ($e) {return (int)$e;}, explode(',', $_GET["id"]));
		if (count($ids) > 100)
		    $ids = array_slice($ids, 0, 100); // Only take max. 100 items
        $ids_in = prepare_in($ids);

        if (!empty($ids_in)) {
		    $sql->modify('chapter_mark_unread', "DELETE FROM mangadex_chapter_views WHERE user_id = ? AND chapter_id IN ($ids_in)", array_merge([$user->user_id], $ids));
			$memcached->delete("user_{$user->user_id}_read_chapters");
		}

		$result = 1;

		break;

    case "chapter_mark_read":
		$ids = array_map(function ($e) {return (int)$e;}, explode(',', $_GET["id"]));
		if (count($ids) > 100)
		    $ids = array_slice($ids, 0, 100); // Only take max. 100 items


		$q = "INSERT IGNORE INTO mangadex_chapter_views (user_id, chapter_id) VALUES ";
		$values = [];
		$binds = [];
		foreach ($ids AS $id) {
			$values[]= "(?, ?)";
			$binds[] = $user->user_id;
			$binds[] = $id;
		}
		$q .= implode(',', $values);

		$sql->modify('chapter_mark_read', $q, $binds);
		$memcached->delete("user_{$user->user_id}_read_chapters");

		$result = 1;

		break;

	case "chapter_purge":
		$id = prepare_numeric($_GET["id"]);

		$chapter = new Chapter($id);

		if (count(get_object_vars($chapter))) {
			if (validate_level($user, 'gmod')) {
				$sql->modify('chapter_purge', " UPDATE mangadex_chapters SET manga_id = 47 WHERE chapter_id = ? LIMIT 1 ", [$id]);

                $memcached->delete("chapter_$id");

                // Add undelete change to manga history
                $sql->modify('manga_history', 'INSERT INTO mangadex_manga_history (`user_id`, `manga_id`, `timestamp`, `action`, `changes`) VALUES (?,?,?,?,?)',
                    [$user->user_id ?? 1, $chapter->manga_id, time(), 'chapter_purge', json_encode([sprintf('Purge Chapter <a href="/chapter/%1$d">%1$d</a> <a role="button" href="/chapter/%1$d/edit" class="btn btn-xs btn-info"><span class="fas fa-pencil-alt fa-fw " aria-hidden="true" title="Edit"></span></a>', $chapter->chapter_id)])]);

                $details = $id;
			}
			else {
				$details = "You can't purge Chapter #$id.";
				print display_alert("danger", "Failed", $details); // fail
			}
		}
		else {
			$details = "Chapter #$id does not exist.";
			print display_alert("danger", "Failed", $details); // fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;
	
	case "chapter_unavailable":
		$id = prepare_numeric($_GET["id"]);

		$chapter = new Chapter($id);

		if (count(get_object_vars($chapter))) {
			if (validate_level($user, 'gmod')) {
				$sql->modify('chapter_undelete', " UPDATE mangadex_chapters SET available = 0, chapter_deleted = 0 WHERE chapter_id = ? LIMIT 1 ", [$id]);
				$sql->modify('chapter_undelete', " UPDATE mangadex_users SET user_uploads = user_uploads + 1 WHERE user_id = ? LIMIT 1 ", [$chapter->user_id]);

                $memcached->delete("chapter_$id");
				$memcached->delete("user_$chapter->user_id");

                // Add undelete change to manga history
                $sql->modify('manga_history', 'INSERT INTO mangadex_manga_history (`user_id`, `manga_id`, `timestamp`, `action`, `changes`) VALUES (?,?,?,?,?)',
                    [$user->user_id ?? 1, $chapter->manga_id, time(), 'chapter_unavailable', json_encode([sprintf('Unavailable Chapter <a href="/chapter/%1$d">%1$d</a> <a role="button" href="/chapter/%1$d/edit" class="btn btn-xs btn-info"><span class="fas fa-pencil-alt fa-fw " aria-hidden="true" title="Edit"></span></a>', $chapter->chapter_id)])]);

                $details = $id;
			}
			else {
				$details = "You can't make Chapter #$id unavailable.";
				print display_alert("danger", "Failed", $details); // fail
			}
		}
		else {
			$details = "Chapter #$id does not exist.";
			print display_alert("danger", "Failed", $details); // fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "chapter_undelete":
		$id = prepare_numeric($_GET["id"]);

		$chapter = new Chapter($id);

		if (count(get_object_vars($chapter))) {
			if (validate_level($user, 'gmod')) {
				$sql->modify('chapter_undelete', " UPDATE mangadex_chapters SET chapter_deleted = 0 WHERE chapter_id = ? LIMIT 1 ", [$id]);
				$sql->modify('chapter_undelete', " UPDATE mangadex_users SET user_uploads = user_uploads + 1 WHERE user_id = ? LIMIT 1 ", [$chapter->user_id]);

				$is_delayed = $chapter->upload_timestamp > time(); // Upload date is in the future

                if ($is_delayed) {
                    // We undeleted a chapter that is still due to be released in the future. Make sure the chapter exists in the delayed_chapters table
                    $sql->modify('chapter_delayed', 'INSERT INTO mangadex_delayed_chapters (`chapter_id`, `manga_id`, `upload_timestamp`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `upload_timestamp` = ?', [$chapter->chapter_id, $chapter->manga_id, $chapter->upload_timestamp, $chapter->upload_timestamp]);
                } else {
                    //update last_updated table by checking timestamps
                    $last_updated = $sql->prep('chapter_undelete', ' SELECT * FROM mangadex_last_updated WHERE manga_id = ? AND lang_id = ? LIMIT 1 ', [$chapter->manga_id, $chapter->lang_id], 'fetch', PDO::FETCH_OBJ, -1);
                    if ($chapter->upload_timestamp > $last_updated->upload_timestamp) {
                        $sql->modify('chapter_undelete', " INSERT INTO mangadex_last_updated (chapter_id, manga_id, volume, chapter, title, upload_timestamp, user_id, lang_id, group_id, group_id_2, group_id_3, available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
						ON DUPLICATE KEY UPDATE chapter_id = ?, volume = ?, chapter = ?, title = ?, upload_timestamp = ?, user_id = ?, group_id = ?, group_id_2 = ?, group_id_3 = ?, available = ? ",
                            [$chapter->chapter_id, $chapter->manga_id, $chapter->volume, $chapter->chapter, $chapter->title, $chapter->upload_timestamp, $chapter->user_id, $chapter->lang_id, $chapter->group_id, $chapter->group_id_2, $chapter->group_id_3, $chapter->available, $chapter->chapter_id, $chapter->volume, $chapter->chapter, $chapter->title, $chapter->upload_timestamp, $chapter->user_id, $chapter->group_id, $chapter->group_id_2, $chapter->group_id_3, $chapter->available]);
                    }
                }

                $memcached->delete("chapter_$id");
				$memcached->delete("user_$chapter->user_id");

                // Add undelete change to manga history
                $sql->modify('manga_history', 'INSERT INTO mangadex_manga_history (`user_id`, `manga_id`, `timestamp`, `action`, `changes`) VALUES (?,?,?,?,?)',
                    [$user->user_id ?? 1, $chapter->manga_id, time(), 'chapter_undelete', json_encode([sprintf('Undelete Chapter <a href="/chapter/%1$d">%1$d</a> <a role="button" href="/chapter/%1$d/edit" class="btn btn-xs btn-info"><span class="fas fa-pencil-alt fa-fw " aria-hidden="true" title="Edit"></span></a>', $chapter->chapter_id)])]);

                $details = $id;
			}
			else {
				$details = "You can't restore Chapter #$id.";
				print display_alert("danger", "Failed", $details); // fail
			}
		}
		else {
			$details = "Chapter #$id does not exist.";
			print display_alert("danger", "Failed", $details); // fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "chapter_delete":
		$id = prepare_numeric($_GET["id"]);

		$chapter = new Chapter($id);

        // Restriction check
        if (!validate_level($user, 'gmod') && $user->has_active_restriction(USER_RESTRICTION_CHAPTER_DELETE)) {
            $details = $user->get_restriction_message(USER_RESTRICTION_CHAPTER_DELETE) ?? "Chapter upload failed!";
            print display_alert("danger", "Failed", $details); // fail
        }
        else if (count(get_object_vars($chapter))) {
			$group = new Group($chapter->group_id);

			if (
			    validate_level($user, 'gmod') ||
			    (
                    $chapter->available &&
                    (
                        $chapter->user_id == $user->user_id ||
                        (
                            !$group->group_is_inactive &&
                            (
                                $group->group_leader_id == $user->user_id ||
                                in_array($user->username, $group->get_members())
                            )
                        )
                    )
                )
			) {
				$sql->modify('chapter_delete', " UPDATE mangadex_chapters SET chapter_deleted = 1 WHERE chapter_id = ? LIMIT 1 ", [$id]);
				$sql->modify('chapter_delete', " UPDATE mangadex_users SET user_uploads = user_uploads - 1 WHERE user_id = ? LIMIT 1 ", [$user->user_id]);

				//update last_updated table by deleting entry and replacing with an older entry if possible
				$sql->modify('chapter_delete', " DELETE FROM mangadex_last_updated WHERE chapter_id = ? LIMIT 1 ", [$id]);
				$last_updated = $sql->prep('chapter_delete', ' 
					SELECT * FROM mangadex_chapters WHERE manga_id = ? AND lang_id = ? AND chapter_deleted = 0 ORDER BY upload_timestamp DESC LIMIT 1 
					', [$chapter->manga_id, $chapter->lang_id], 'fetch', PDO::FETCH_OBJ, -1);

				if ($last_updated)
					$sql->modify('chapter_delete', " 
						INSERT INTO mangadex_last_updated (chapter_id, manga_id, volume, chapter, title, upload_timestamp, user_id, lang_id, group_id, group_id_2, group_id_3, available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
						ON DUPLICATE KEY UPDATE chapter_id = ?, volume = ?, chapter = ?, title = ?, upload_timestamp = ?, user_id = ?, group_id = ?, group_id_2 = ?, group_id_3 = ?, available = ?
						", [$last_updated->chapter_id, $last_updated->manga_id, $last_updated->volume, $last_updated->chapter, $last_updated->title, $last_updated->upload_timestamp, $last_updated->user_id, $last_updated->lang_id, $last_updated->group_id, $last_updated->group_id_2, $last_updated->group_id_3, $last_updated->available, $last_updated->chapter_id, $last_updated->volume, $last_updated->chapter, $last_updated->title, $last_updated->upload_timestamp, $last_updated->user_id, $last_updated->group_id, $last_updated->group_id_2, $last_updated->group_id_3, $last_updated->available]);

				// Delete this chapter from the delayed table as well
                $sql->modify('delayed_chapter_delete', 'DELETE FROM mangadex_delayed_chapters WHERE `chapter_id` = ?', [$id]);

				$memcached->delete("chapter_$id");
				$memcached->delete("user_$user->user_id");

                // Add delete change to manga history
                $sql->modify('manga_history', 'INSERT INTO mangadex_manga_history (`user_id`, `manga_id`, `timestamp`, `action`, `changes`) VALUES (?,?,?,?,?)',
                    [$user->user_id ?? 1, $chapter->manga_id, time(), 'chapter_delete', json_encode([sprintf('Delete Chapter <a href="/chapter/%1$d">%1$d</a> <a role="button" href="/chapter/%1$d/edit" class="btn btn-xs btn-info"><span class="fas fa-pencil-alt fa-fw " aria-hidden="true" title="Edit"></span></a>', $chapter->chapter_id)])]);


                $details = $id;
				print display_alert("success", "Success", "Chapter $id has been deleted."); // success
			}
			else {
				if (!$chapter->available)
					$details = "Chapter #$id is unavailable.";
				else
					$details = "You can't delete Chapter #$id.";

				print display_alert("danger", "Failed", $details); // fail
			}
		}
		else {
			$details = "Chapter #$id does not exist.";
			print display_alert("danger", "Failed", $details); // fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "chapter_edit":
		$id = prepare_numeric($_GET["id"]);

		$chapter = new Chapter($id);

		$manga_id = prepare_numeric($_POST["manga_id"]);
		$chapter_name = htmlentities($_POST["chapter_name"]);
		$volume_number = remove_padding(htmlentities($_POST["volume_number"]));
		$chapter_number = remove_padding(htmlentities($_POST["chapter_number"]));
		$group_id = prepare_numeric($_POST["group_id"]);
		$group_id_2 = prepare_numeric($_POST["group_id_2"]);
		$group_id_3 = prepare_numeric($_POST["group_id_3"]);
		$lang_id = prepare_numeric($_POST["lang_id"]);
		$old_file = $_FILES['file']['name'];

		$unavailable = isset($_POST["unavailable"]) ? 0 : 1;
		$server = $_POST["server"] ?? $chapter->server;
		$page_order = $_POST["page_order"] ?? $chapter->page_order;
        $user_id = $_POST["user_id"] ?? $chapter->user_id;

		if (isset($_POST["user_id"]) && !validate_level($user, 'mod')) {
		    $error .= display_alert('danger', 'Failed', 'You may not change the uploader user ID.');
        }

        // Verify volume number
        if (preg_match('/[^0-9\.]+/', $volume_number)) {
            $error .= display_alert('warning', 'Format error', 'The Volume Number field may only contain numbers and decimals. Please use the Chapter Name field for text input like Chapter titles.');
        }

        // Verify chapter_number
        if (preg_match('/[^0-9\.]+/', $chapter_number)) {
            $error .= display_alert('warning', 'Format error', 'The Chapter Number field may only contain numbers and decimals. Please use the Chapter Name field for text input like Chapter titles.');
        }

        // Verify chapter title
        if (stripos($chapter_name, 'ch. ') !== false || stripos($chapter_name, 'vol. ') !== false) {
            $error .= display_alert('warning', 'Format error', 'The Chapter name may not contain chapter or volume numbers. Please use the Volume / Chapter number field for that.');
        }

        // Verify there is no end tag
        if (stripos($chapter_name, htmlentities(' <end>')) !== false || stripos($chapter_name, ' [end]') !== false) {
            $error .= display_alert('warning', 'Format error', 'The Chapter name may not contain an end tag. Last chapters are set in the manga entry.');
        }

        $current_group = new Group($chapter->group_id);
        $current_members_array = $current_group->get_members();

        $current_members_array2 = [];
        if($chapter->group_id_2){
        	$current_group2 = new Group($chapter->group_id_2);
        	$current_members_array2 = $current_group2->get_members();
        }

        $current_members_array3 = [];
		if($chapter->group_id_3){
        	$current_group3 = new Group($chapter->group_id_3);
        	$current_members_array3 = $current_group3->get_members();
        }

		$target_group = new Group($group_id);
		if (!$target_group->group_is_inactive) {
            $group_members_array = $target_group->get_members();
        } else {
		    $group_members_array = [];
		    $target_group->group_leader_id = -1;
        }

		if ($group_id_2) {
			$target_group2 = new Group($group_id_2);
			if (!$target_group2->group_is_inactive) {
                $group_2_members_array = $target_group2->get_members();
            } else {
                $group_2_members_array = [];
                $target_group2->group_leader_id = -1;
            }
		}
		else {
			$target_group2 = new stdClass();
			$target_group2->group_control = 0;
			$group_2_members_array = [];
		}

		if ($group_id_3) {
            $target_group3 = new Group($group_id_3);
            if (!$target_group3->group_is_inactive) {
                $group_3_members_array = $target_group3->get_members();
            } else {
                $group_3_members_array = [];
                $target_group3->group_leader_id = -1;
            }
		}
		else {
			$target_group3 = new stdClass();
			$target_group3->group_control = 0;
			$group_3_members_array = [];
		}
		$validate_group_control = (!$target_group->group_control || $user->user_id == $target_group->group_leader_id || in_array($user->username, $group_members_array));
		$validate_group2_control = (!$target_group2->group_control || $user->user_id == $target_group2->group_leader_id || in_array($user->username, $group_2_members_array));
		$validate_group3_control = (!$target_group3->group_control || $user->user_id == $target_group3->group_leader_id || in_array($user->username, $group_3_members_array));

		$same_multi_group_validate = ($group_id != $group_id_2 && $group_id != $group_id_3 && ((!$group_id_2 && !$group_id_3) || $group_id_2 != $group_id_3));

        // Restriction check
        // User that cant delete chapters, cant edit chapters for obvious reasons
        if (!validate_level($user, 'gmod') && $user->has_active_restriction(USER_RESTRICTION_CHAPTER_DELETE)) {
            $details = $user->get_restriction_message(USER_RESTRICTION_CHAPTER_DELETE) ?? "Chapter edit failed!";
            $error .= display_alert("danger", "Failed", $details);
        }
        else if (
			$same_multi_group_validate &&
			(
				validate_level($user, 'mod') ||
				(
					(
						$user->user_id == $chapter->user_id ||
						($user->user_id && in_array($user->user_id, [$chapter->group_leader_id, $chapter->group_leader_id_2, $chapter->group_leader_id_3])) ||
						in_array($user->username, array_merge($current_members_array, $current_members_array2, $current_members_array3))
					) &&
					($validate_group_control && $validate_group2_control && $validate_group3_control) &&
					$chapter->available
				)
			)
		) {
			if ($old_file) {
				$zip = new ZipArchive;

				if ($_FILES["file"]) {
				    if (!ENABLE_UPLOAD) {
				        die('Upload is temporarily disabled.');
                    }

					$value = explode(".", $_FILES["file"]["name"]);

					$validate_extention = in_array(strtolower(end($value)), ALLOWED_CHAPTER_EXT);
					$validate_file_size = ($_FILES["file"]["size"] <= MAX_CHAPTER_FILESIZE) || validate_level($user, 'pr'); //check file size
					$validate_zip_file = true;

					// Check for zip bomb by limiting the uncompressed filesize to 200M, double whats allowed
					if (($actualSize = get_zip_originalsize($_FILES["file"]["tmp_name"])) > (2 * MAX_CHAPTER_FILESIZE)) {
						$validate_file_size = false;
					}

					if ($validate_extention && $validate_file_size && $zip->open($_FILES["file"]["tmp_name"])) {
						$chapter_hash = md5($manga_id . $timestamp);

						mkdir(ABS_DATA_BASEPATH . "/data/$chapter_hash");

						$zip->extractTo(ABS_DATA_BASEPATH . "/data/$chapter_hash/");
						$zip->close();

						$files = read_dir(ABS_DATA_BASEPATH . "/data/$chapter_hash/");

						$pages = count($files);

						if ($pages == 1 && is_dir(ABS_DATA_BASEPATH . "/data/$chapter_hash/$files[2]")) { //folder
							rename(ABS_DATA_BASEPATH . "/data/$chapter_hash/$files[2]", ABS_DATA_BASEPATH . "/data/$chapter_hash/folder"); //rename the dir

							$files = read_dir(ABS_DATA_BASEPATH . "/data/$chapter_hash/folder/");
							foreach($files as $value) {
								rename(ABS_DATA_BASEPATH . "/data/$chapter_hash/folder/$value", ABS_DATA_BASEPATH . "/data/$chapter_hash/$value"); //move them all
							}

							rmdir(ABS_DATA_BASEPATH . "/data/$chapter_hash/folder");

							$files = read_dir(ABS_DATA_BASEPATH . "/data/$chapter_hash/");
						}
						elseif ($pages > 1 && is_dir(ABS_DATA_BASEPATH . "/data/$chapter_hash/$files[3]")) {
							$error .= display_alert("danger", "Failed", "Your .zip contains multiple folders."); //can't open zip
						}

					} else {
						$validate_zip_file = false;
					}

					if ($_FILES["file"]["error"])
						$error .= display_alert("danger", "Failed", "Missing file? Code: (" . $_FILES["file"]["error"] . ").");
					elseif (!$validate_file_size)
						$error .= display_alert("danger", "Failed", "File size exceeds 100 MB."); //too big
					elseif (!$validate_extention)
						$error .= display_alert("danger", "Failed", "A .$extension file, not a .zip."); //too big
					elseif (!$validate_zip_file)
						$error .= display_alert("danger", "Failed", "There's something wrong with your .zip file."); //can't open zip

				}
			}
		}
		elseif (!$validate_group_control)
			$error .= display_alert("danger", "Failed", "Group 1 have restricted uploads to members only."); //banned
		elseif (!$validate_group2_control)
			$error .= display_alert("danger", "Failed", "Group 2 have restricted uploads to members only."); //banned
		elseif (!$validate_group3_control)
			$error .= display_alert("danger", "Failed", "Group 3 have restricted uploads to members only."); //banned
		elseif (!$same_multi_group_validate)
			$error .= display_alert("danger", "Failed", "Identical groups detected."); //banned
		elseif (!$chapter->available)
			$error .= display_alert("danger", "Failed", "This chapter is unavailable."); //unavailable
		else
			$error .= display_alert("danger", "Failed", "You can't edit Chapter #$id.");

		if (!$error) {
			$sql->modify('chapter_edit', " UPDATE mangadex_chapters SET manga_id = ?, title = ?, volume = ?, chapter = ?, group_id = ?, group_id_2 = ?, group_id_3 = ?, lang_id = ?, server = ?, page_order = ?, user_id = ?, available = ? WHERE chapter_id = ? LIMIT 1 ",
				[$manga_id, $chapter_name, $volume_number, $chapter_number, $group_id, $group_id_2, $group_id_3, $lang_id, $server, $page_order, $user_id, $unavailable, $id]);

			$sql->modify('chapter_edit', " UPDATE mangadex_last_updated SET manga_id = ?, volume = ?, chapter = ?, title = ?, upload_timestamp = ?, user_id = ?, lang_id = ?, group_id = ?, group_id_2 = ?, group_id_3 = ?, available = ? WHERE chapter_id = ? LIMIT 1 ",
				[$manga_id, $volume_number, $chapter_number, $chapter_name, $chapter->upload_timestamp, $user_id, $lang_id, $group_id, $group_id_2, $group_id_3, $unavailable, $id]);

			if ($old_file) {
				$page_order = "";

				natcasesort($files);
				$arr = array_values($files);

				foreach($arr as $key => $value) {
					$key++;
					$arr = explode(".", $value);
					$ext = strtolower(end($arr));
					if (!in_array($ext, ALLOWED_IMG_EXT))
						@unlink(ABS_DATA_BASEPATH . "/data/$chapter_hash/$value");
					else {
						$sha256 = hash_file('sha256', ABS_DATA_BASEPATH . "/data/$chapter_hash/$value");
						
						@rename(ABS_DATA_BASEPATH . "/data/$chapter_hash/$value", ABS_DATA_BASEPATH . "/data/$chapter_hash/$key-$sha256.$ext"); //rename them all numerically

						$page_order .= "$key-$sha256.$ext,";
					}
				}

				$page_order = rtrim($page_order, ",");

				if (!$chapter->server && is_dir(ABS_DATA_BASEPATH . "/data/$chapter->chapter_hash"))
					rename(ABS_DATA_BASEPATH . "/data/$chapter->chapter_hash", ABS_DATA_BASEPATH . "/delete/$chapter->chapter_hash");

				$sql->modify('chapter_edit', " UPDATE mangadex_chapters SET server = ?, chapter_hash = ?, page_order = ? WHERE chapter_id = ? LIMIT 1 ", [IMAGE_SERVER, $chapter_hash, $page_order, $id]);
			}

			$memcached->delete("chapter_$id");

			// Additionally, we need to invalidate the manga cache the api uses, otherwise changes to the chapter wont show up in the reader immediately
            $bind = [0,(string)$manga_id];
            $order = "(CASE volume WHEN '' THEN 1 END) DESC, abs(volume) DESC, abs(chapter) DESC, group_id ASC";
            $key = "chapters_query_".hash_array($bind)."_orderby_".md5($order)."_offset_0"; // See /api/index.php L:39+
            $res = $memcached->delete($key);

			$details = $id;
		}

		print $error; //returns "" or a message

		$result = ($error) ? 0 : 1;
		break;


	case "chapter_report":
		$id = prepare_numeric($_GET["id"]);
		$type = prepare_numeric($_POST["type_id"]);
		$info = htmlentities($_POST["info"]);

		$report_restriction = $user->has_active_restriction(USER_RESTRICTION_CREATE_REPORT) && !validate_level($user, 'mod');

		if (validate_level($user, 'member') && !$report_restriction) {
            $chapter_reasons = array_filter((new Report_Reasons())->toArray(), function($reason) { return REPORT_TYPES[$reason['type_id']] === 'Chapter'; });
			if (($chapter_reasons[$type]['is_info_required'] == 1 ?? false) && !$info) {
				$details = "Please give more information.";
				print display_alert("danger", "Failed", $details); //fail
			}
			else {
				$sql->modify('chapter_report', " INSERT IGNORE INTO mangadex_reports_chapters (report_id, report_chapter_id, report_timestamp, report_type, report_info, report_user_id, report_mod_user_id, report_conclusion) 
					VALUES (NULL, ?, UNIX_TIMESTAMP(), ?, ?, ?, 0, 0) ", [$id, $type, $info, $user->user_id]);

				$memcached->delete('mod_report_count');

				$details = $id;

                post_on_discord(DISCORD_WEBHOOK_REPORT, [
                    'username' => $user->username,
                    'embeds' => [
                        [
                            'title' => 'Chapter Report',
                            'description' => $chapter_reasons[$type]['text'] ?? 'Unknown report type',
                            'url' => URL . 'mod/chapter_reports/new',
                            'footer' => [
                                'text' => $info
                            ]
                        ]
                    ]
                ]);

                if (defined('DISCORD_REPORT_PING_COUNT') && defined('DISCORD_REPORT_PING_ROLE_ID') && !empty(DISCORD_REPORT_PING_COUNT)) {
                    $report_count = $sql->prep('chapter_report_count',
                        ' SELECT count(DISTINCT report_user_id) FROM mangadex_reports_chapters WHERE report_chapter_id = ? AND report_conclusion = 0 ',
                        [$id], 'fetchColumn', '', -1);
                    if ($report_count === DISCORD_REPORT_PING_COUNT) {
                        post_on_discord(DISCORD_WEBHOOK_REPORT, [
                            'username' => 'MangaDex',
                            'content' => "<@&" . DISCORD_REPORT_PING_ROLE_ID . "> Chapter $id has received $report_count unresolved reports.\n". URL . "chapter/$id",
                        ]);
                    }
                }
			}
		}
		elseif ($report_restriction) {
		    $details = $user->get_restriction_message(USER_RESTRICTION_CREATE_REPORT) ?? "You can't report Chapter $id.";
            print display_alert("danger", "Failed", $details); //fail
        }
		else {
			$details = "You can't report Chapter $id.";
			print display_alert("danger", "Failed", $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "chapter_report_accept_all":
		$id = prepare_numeric($_GET["id"]);

		if (validate_level($user, 'gmod')) {
			$sql->modify('chapter_report_accept_all', " UPDATE mangadex_reports_chapters SET report_conclusion = 1, report_mod_user_id = ? WHERE report_chapter_id = ? ", [$user->user_id, $id]);

			print display_alert("success", "Success", "All reports for chapter #$id accepted.");  //success

            $memcached->delete('mod_report_count');

			$details = $id;
			$result = 1;
		}
		break;

	case "chapter_report_accept":
		$id = prepare_numeric($_GET["id"]);

		if (validate_level($user, 'gmod')) {
			$sql->modify('chapter_report_accept', " UPDATE mangadex_reports_chapters SET report_conclusion = 1, report_mod_user_id = ? WHERE report_id = ? LIMIT 1 ", [$user->user_id, $id]);

			print display_alert("success", "Success", "Report #$id accepted.");  //success

            $memcached->delete('mod_report_count');

			$details = $id;
			$result = 1;
		}
		break;

	case "chapter_report_reject":
		$id = prepare_numeric($_GET["id"]);

		if (validate_level($user, 'gmod')) {
			$sql->modify('chapter_report_reject', " UPDATE mangadex_reports_chapters SET report_conclusion = 2, report_mod_user_id = ? WHERE report_id = ? LIMIT 1 ", [$user->user_id, $id]);

			print display_alert("success", "Success", "Report #$id rejected.");  //success

            $memcached->delete('mod_report_count');

			$details = $id;
			$result = 1;
		}
		break;

	case "upload_queue_accept":
		$id = prepare_numeric($_GET["id"]);

		$row = $sql->prep("queue_$id", " SELECT * FROM mangadex_upload_queue WHERE queue_id = ? ", [$id], 'fetch', PDO::FETCH_OBJ, -1);

		if (validate_level($user, 'mod')) {
			$sql->modify('upload_queue_accept', " UPDATE mangadex_upload_queue SET queue_conclusion = 1, queue_mod_user_id = ? WHERE queue_id = ? LIMIT 1 ", [$user->user_id, $id]);

			$sql->modify('upload_queue_accept', " UPDATE mangadex_chapters SET upload_timestamp = GREATEST(upload_timestamp, UNIX_TIMESTAMP()), available = 1 WHERE chapter_id = ? LIMIT 1 ", [$row->chapter_id]);

			$sql->modify('upload_queue_accept', " UPDATE mangadex_last_updated SET upload_timestamp = UNIX_TIMESTAMP(), available = 1 WHERE chapter_id = ? LIMIT 1 ", [$row->chapter_id]);

			$sql->modify('upload_queue_accept', " UPDATE mangadex_users SET user_uploads = user_uploads + 1 WHERE user_id = ? LIMIT 1 ", [$row->user_id]);

			print display_alert("success", "Success", "Queue #$id accepted.");  //success

            $memcached->delete("user_{$row->user_id}");
			$memcached->delete("chapter_{$row->chapter_id}");
            $memcached->delete('mod_upload_queue_count');

			$details = $id;
			$result = 1;
		}
		break;

	case "upload_queue_reject":
		$id = prepare_numeric($_GET["id"]);

		$row = $sql->prep("queue_$id", " SELECT * FROM mangadex_upload_queue WHERE queue_id = ? ", [$id], 'fetch', PDO::FETCH_OBJ, -1);

		$chapter = new Chapter($row->chapter_id);

		if (validate_level($user, 'mod')) {
			$sql->modify('upload_queue_reject', " UPDATE mangadex_upload_queue SET queue_conclusion = 2, queue_mod_user_id = ? WHERE queue_id = ? LIMIT 1 ", [$user->user_id, $id]);

			$sql->modify('upload_queue_reject', " UPDATE mangadex_chapters SET available = 1, chapter_deleted = 1 WHERE chapter_id = ? LIMIT 1 ", [$row->chapter_id]);

			//update last_updated table by deleting entry and replacing with an older entry if possible
			$sql->modify('upload_queue_reject', " DELETE FROM mangadex_last_updated WHERE chapter_id = ? LIMIT 1 ", [$row->chapter_id]);
			$last_updated = $sql->prep('upload_queue_reject', ' 
				SELECT * FROM mangadex_chapters WHERE manga_id = ? AND lang_id = ? AND chapter_deleted = 0 ORDER BY upload_timestamp DESC LIMIT 1 
				', [$chapter->manga_id, $chapter->lang_id], 'fetch', PDO::FETCH_OBJ, -1);

			if ($last_updated)
				$sql->modify('upload_queue_reject', " 
					INSERT INTO mangadex_last_updated (chapter_id, manga_id, volume, chapter, title, upload_timestamp, user_id, lang_id, group_id, group_id_2, group_id_3, available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
					ON DUPLICATE KEY UPDATE chapter_id = ?, volume = ?, chapter = ?, title = ?, upload_timestamp = ?, user_id = ?, group_id = ?, group_id_2 = ?, group_id_3 = ?, available = ?
					", [$last_updated->chapter_id, $last_updated->manga_id, $last_updated->volume, $last_updated->chapter, $last_updated->title, $last_updated->upload_timestamp, $last_updated->user_id, $last_updated->lang_id, $last_updated->group_id, $last_updated->group_id_2, $last_updated->group_id_3, $last_updated->available, $last_updated->chapter_id, $last_updated->volume, $last_updated->chapter, $last_updated->title, $last_updated->upload_timestamp, $last_updated->user_id, $last_updated->group_id, $last_updated->group_id_2, $last_updated->group_id_3, $last_updated->available]);

			// Delete this chapter from the delayed table as well
			$sql->modify('delayed_chapter_delete', 'DELETE FROM mangadex_delayed_chapters WHERE `chapter_id` = ?', [$row->id]);

			$memcached->delete("chapter_{$row->chapter_id}");
            $memcached->delete('mod_upload_queue_count');

			print display_alert("success", "Success", "Queue #$id rejected.");  //success

			$details = $id;
			$result = 1;
		}
		break;

	case "chapter_upload":
	    if (!ENABLE_UPLOAD) {
	        die('Upload is temporarily disabled.');
        }

		$zip = new ZipArchive;

		$manga_id = prepare_numeric($_POST["manga_id"]);
		$chapter_name = htmlentities($_POST["chapter_name"]);
		$volume_number = remove_padding(htmlentities($_POST["volume_number"]));
		$chapter_number = remove_padding(htmlentities($_POST["chapter_number"]));
		$group_id = prepare_numeric($_POST["group_id"]);
		$group_id_2 = !empty($_POST["group_id_2"]) ? prepare_numeric($_POST["group_id_2"]) : 0;
		$group_id_3 = !empty($_POST["group_id_3"]) ? prepare_numeric($_POST["group_id_3"]) : 0;
		$lang_id = prepare_numeric($_POST["lang_id"]);
		$external = isset($_POST["external"]) ? remove_padding(htmlentities($_POST["external"])) : '';
		$is_deleted = (bool) ( isset($_POST["is_deleted"]) && validate_level($user, 'gmod') && $_POST["is_deleted"] );
        $available = (isset($_POST["unavailable"]) && validate_level($user, 'gmod')) ? 0 : 1;
        $override_user_id = (isset($_POST["override_user_id"]) && !empty($_POST["override_user_id"]) && validate_level($user, 'gmod')) ? prepare_numeric($_POST["override_user_id"]) : false;

		// Verify volume number
        if (preg_match('/[^0-9\.]+/', $volume_number)) {
            $error .= display_alert('warning', 'Format error', 'The Volume Number field may only contain numbers and decimals. Please use the Chapter Name field for text input like Chapter titles.');
        }

		// Verify chapter_number
		if (preg_match('/[^0-9\.]+/', $chapter_number)) {
		    $error .= display_alert('warning', 'Format error', 'The Chapter Number field may only contain numbers and decimals. Please use the Chapter Name field for text input like Chapter titles.');
        }

		// Verify chapter title
        if (stripos($chapter_name, 'ch. ') !== false || stripos($chapter_name, 'vol. ') !== false) {
            $error .= display_alert('warning', 'Format error', 'The Chapter name may not contain chapter or volume numbers. Please use the Volume / Chapter number field for that.');
        }

        // Verify there is no end tag
        if (stripos($chapter_name, htmlentities(' <end>')) !== false || stripos($chapter_name, ' [end]') !== false) {
            $error .= display_alert('warning', 'Format error', 'The Chapter name may not contain an end tag. Last chapters are set in the manga entry.');
        }

		$manga = new Manga($manga_id);
		$group = new Group($group_id);
		$group_members_array = $group->get_members();

		if ($group_id_2) {
			$group2 = new Group($group_id_2);
			$group2_members_array = $group2->get_members();
		}
		else {
			$group2 = new stdClass();
			$group2->group_control = 0;
		}

		if ($group_id_3) {
			$group3 = new Group($group_id_3);
			$group3_members_array = $group3->get_members();
		}
		else {
			$group3 = new stdClass();
			$group3->group_control = 0;
		}

		$validate_group_control = (!$group->group_control || $user->user_id == $group->group_leader_id || ($group->group_control && in_array($user->username, $group_members_array)));
		$validate_group2_control = (!$group2->group_control || $user->user_id == $group2->group_leader_id || ($group2->group_control && in_array($user->username, $group2_members_array)));
		$validate_group3_control = (!$group3->group_control || $user->user_id == $group3->group_leader_id || ($group3->group_control && in_array($user->username, $group3_members_array)));

		$same_multi_group_validate = ($group_id != $group_id_2 && $group_id != $group_id_3 && ((!$group_id_2 && !$group_id_3) || $group_id_2 != $group_id_3));

		if (!validate_level($user, 'gmod') && $user->has_active_restriction(USER_RESTRICTION_CHAPTER_UPLOAD)) {
            $details = $user->get_restriction_message(USER_RESTRICTION_CHAPTER_UPLOAD) ?? "Chapter upload failed!";
            $error .= display_alert("danger", "Failed", $details); //timed_out
        }

        $remote = new RemoteFileUploader();
		$remote->setContext(['userid' => $user->user_id]);
		$isRemoteUploadError = false;
		$wasRemoteUploaded = false;
        if (empty($error) && isset($_POST['fileurl']) && !empty($_POST['fileurl']) && validate_level($user, 'gl')) {
            if (!$remote->supports($_POST['fileurl'])) {
                $error .= display_alert("danger", "Failed", 'This file url is not supported');
            } else {
                try {
                    ['filename'    => $filename,
                     'filetype'    => $filetype,
                     'filetmpname' => $filetmpname,
                     'filesize'    => $filesize
                    ] = $remote->downloadFromRemote($_POST['fileurl']);

                    $_FILES["file"]    = [
                        'name'     => $filename,
                        'type'     => $filetype,
                        'tmp_name' => $filetmpname,
                        'error'    => 0,
                        'size'     => $filesize,
                    ];
                    $wasRemoteUploaded = true;
                } catch (RemoteFileUploadFailed $e) {
                    $error .= display_alert('danger', 'Failed', $e->getMessage());
                } catch (\Throwable $e) {
                    // send to sentry
                    trigger_error($e->getMessage(), E_USER_WARNING);
                    $error .= display_alert("danger", "Failed", 'Remote file upload failed');
                }
            }
        }

        if (empty($error) && (!isset($_FILES['file']['tmp_name']) || empty($_FILES['file']['tmp_name']))) {
            $error .= display_alert("danger", "Failed", 'No upload found.');
        }

		if (empty($error) && validate_level($user, 'member') && count(get_object_vars($manga)) && (($validate_group_control && $validate_group2_control && $validate_group3_control) || validate_level($user, 'gmod')) && $same_multi_group_validate) {
			if ($external)
				$chapter_hash = md5($manga_id . $chapter_name . $volume_number . $chapter_number . $timestamp);
			elseif ($_FILES["file"]) {
				$value = explode(".", $_FILES["file"]["name"]);

				$validate_extention = in_array(strtolower(end($value)), ALLOWED_CHAPTER_EXT);
				$validate_file_size = ($_FILES["file"]["size"] <= MAX_CHAPTER_FILESIZE) || validate_level($user, 'pr'); //check file size
				$validate_zip_file = true;

				// Check for zip bomb by limiting the uncompressed filesize to 200M, double whats allowed
				if (($actualSize = get_zip_originalsize($_FILES["file"]["tmp_name"])) > (2 * MAX_CHAPTER_FILESIZE)) {
					$validate_file_size = false;
				}

				if ($validate_extention && $validate_file_size && !empty($_FILES["file"]["tmp_name"]) && $zip->open($_FILES["file"]["tmp_name"]) === true) {
					$chapter_hash = md5($manga_id . $chapter_name . $volume_number . $chapter_number . $timestamp);

					mkdir(ABS_DATA_BASEPATH . "/data/$chapter_hash");

					$zip->extractTo(ABS_DATA_BASEPATH . "/data/$chapter_hash/");
					$zip->close();
					if ($wasRemoteUploaded) {
					    @unlink($_FILES["file"]["tmp_name"]);
                    }

					$files = read_dir(ABS_DATA_BASEPATH . "/data/$chapter_hash/");

					$pages = count($files);

					if ($pages == 1 && is_dir(ABS_DATA_BASEPATH . "/data/$chapter_hash/$files[2]")) { //folder
						rename(ABS_DATA_BASEPATH . "/data/$chapter_hash/$files[2]", ABS_DATA_BASEPATH . "/data/$chapter_hash/folder"); //rename the dir

						$files = read_dir(ABS_DATA_BASEPATH . "/data/$chapter_hash/folder/");
						foreach($files as $value) {
							rename(ABS_DATA_BASEPATH . "/data/$chapter_hash/folder/$value", ABS_DATA_BASEPATH . "/data/$chapter_hash/$value"); //move them all
						}

						rmdir(ABS_DATA_BASEPATH . "/data/$chapter_hash/folder");

						$files = read_dir(ABS_DATA_BASEPATH . "/data/$chapter_hash/");
					}
					elseif ($pages > 1 && is_dir(ABS_DATA_BASEPATH . "/data/$chapter_hash/$files[3]")) {
						$error .= display_alert("danger", "Failed", "Your .zip contains multiple folders, or you have files starting with an invalid character (!)"); //can't open zip
					}
					elseif ($pages < 1) {
					    $error .= display_alert('danger', 'Failed', "Your .zip did not contain any files.");
                    }

				} else {
					$validate_zip_file = false;
				}

				if (isset($_FILES["file"]["error"]) && !empty($_FILES["file"]["error"]))
					$error .= display_alert("danger", "Failed", "Missing file? Code: (" . $_FILES["file"]["error"] . ").");
				elseif (!$validate_file_size)
					$error .= display_alert("danger", "Failed", "File size exceeds 100 MB."); //too big
				elseif (!$validate_extention)
					$error .= display_alert("danger", "Failed", "Only .zip archvies are supported."); //too big
				elseif (!$validate_zip_file)
					$error .= display_alert("danger", "Failed", "There's something wrong with your .zip file."); //can't open zip

			}
			else
				$error .= display_alert("danger", "Failed", "Missing file."); //missing image
		}
		elseif (empty($error)) {
			if (!$user->user_id)
				$error .= display_alert("danger", "Failed", "Your session has timed out. Please log in again."); //timed_out
			elseif (!$user->level_id)
				$error .= display_alert("danger", "Failed", "You're banned from uploading!"); //banned
			elseif (!$validate_group_control)
				$error .= display_alert("danger", "Failed", "Group 1 have restricted uploads to members only."); //banned
			elseif (!$validate_group2_control)
				$error .= display_alert("danger", "Failed", "Group 2 have restricted uploads to members only."); //banned
			elseif (!$validate_group3_control)
				$error .= display_alert("danger", "Failed", "Group 3 have restricted uploads to members only."); //banned
			elseif (!$same_multi_group_validate)
				$error .= display_alert("danger", "Failed", "Identical groups detected."); //banned
			elseif (!$stop)
				$error .= display_alert("danger", "Failed", "Stop uploading for a while due to image transfer."); //banned
			else
				$error .= display_alert("danger", "Failed", "Manga #$manga_id does not exist.");
		}

		//if no errors, then upload
		if (!$error) {
			if ($external) {
                $page_order = $external;
			} else {
                $page_order = "";
                natcasesort($files);
                $arr = array_values($files);
                //$letter = rand_letter(1);
                foreach($arr as $key => $value) {
                    $key++;
                    $arr = explode(".", $value);
                    $ext = strtolower(end($arr));
                    if (!in_array($ext, ALLOWED_IMG_EXT))
                        @unlink(ABS_DATA_BASEPATH . "/data/$chapter_hash/$value");
                    else {
						$sha256 = hash_file('sha256', ABS_DATA_BASEPATH . "/data/$chapter_hash/$value");
						
                        @rename(ABS_DATA_BASEPATH . "/data/$chapter_hash/$value", ABS_DATA_BASEPATH . "/data/$chapter_hash/$key-$sha256.$ext"); //rename them all numerically

                        $page_order .= "$key-$sha256.$ext,";
                    }
                }
                $page_order = rtrim($page_order, ",");
            }

			$upload_timestamp = $timestamp + $group->group_delay;

			$is_delayed = $group->group_delay > 0;

			$user_has_available_uploads = validate_level($user, 'pr') || $sql->prep('chapter_queue_check',
                ' SELECT count(chapter_id) FROM mangadex_chapters WHERE user_id = ? AND lang_id = ? AND available = 1 AND chapter_deleted = 0 LIMIT 1 ',
                [$user->user_id, $lang_id], 'fetchColumn', '', -1);

			$available = !$user_has_available_uploads ? 0 : $available;
			
			$available = (in_array($manga_id, DNU_MANGA_IDS) && $lang_id == 1) ? 0 : $available;

			$uploader = $override_user_id ?: $user->user_id;

			$chapter_id = $sql->modify('chapter_upload', " 
				INSERT INTO mangadex_chapters (chapter_id, chapter_hash, manga_id, volume, chapter, title, upload_timestamp, user_id, chapter_views, lang_id, authorised, group_id, group_id_2, group_id_3, server, page_order, chapter_deleted, thread_id, available) 
				VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0, ?, ?, ?, ?, ?, ?, 0, ?) 
				", [$chapter_hash, $manga_id, $volume_number, $chapter_number, $chapter_name, $upload_timestamp, $uploader, $lang_id, $group_id, $group_id_2, $group_id_3, IMAGE_SERVER, $page_order, $is_deleted ? 1 : 0, $available ? 1 : 0]);

			if (!$user_has_available_uploads) {
                if (defined('DISCORD_REPORT_PING_ROLE_ID')) {
                    $queued_chapter_count = $sql->prep('chapter_queue_check',
                        ' SELECT count(DISTINCT chapter_id) FROM mangadex_upload_queue WHERE user_id = ? AND queue_conclusion IS NULL ',
                        [$user->user_id], 'fetchColumn', '', -1);
                    if ($queued_chapter_count === 0) {
                        post_on_discord(DISCORD_WEBHOOK_REPORT, [
                            'username' => 'MangaDex',
                            'content' => "<@&" . DISCORD_REPORT_PING_ROLE_ID . "> Chapter $chapter_id has been held in the upload queue.\n". URL . "user/$user->user_id \n". URL . "chapter/$chapter_id",
                        ]);
                    }
                }

				$sql->modify('chapter_upload', " 
				INSERT INTO mangadex_upload_queue (queue_id, user_id, chapter_id) VALUES (NULL, ?, ?) 
				", [$user->user_id, $chapter_id]);

                $memcached->delete('mod_upload_queue_count');
			}

			if (!$is_deleted)
                $sql->modify('chapter_upload', " UPDATE mangadex_groups SET group_last_updated = ? WHERE group_id = ? LIMIT 1 ", [$timestamp, $group_id]);

			if ($is_delayed && !$is_deleted) {
			    // Add this chapter to the delayed_chapters table, so metadata can be modified at a later date
                if ($chapter_id > 0) {
                    $sql->modify('chapter_delayed', 'INSERT INTO mangadex_delayed_chapters (`chapter_id`, `manga_id`, `upload_timestamp`) VALUES (?,?,?)', [$chapter_id, $manga_id, $upload_timestamp]);
                }
            } else if (!$is_deleted) {
                $sql->modify('chapter_upload', " UPDATE mangadex_mangas SET manga_last_uploaded = ? WHERE manga_id = ? LIMIT 1 ", [$timestamp, $manga_id]);

                $sql->modify('chapter_upload', " 
				INSERT INTO mangadex_last_updated (chapter_id, manga_id, volume, chapter, title, upload_timestamp, user_id, lang_id, group_id, group_id_2, group_id_3, available) 
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				ON DUPLICATE KEY UPDATE chapter_id = ?, volume = ?, chapter = ?, title = ?, upload_timestamp = ?, user_id = ?, group_id = ?, group_id_2 = ?, group_id_3 = ?, available = ?
				", [$chapter_id, $manga_id, $volume_number, $chapter_number, $chapter_name, $upload_timestamp, $uploader, $lang_id, $group_id, $group_id_2, $group_id_3, $available ? 1 : 0, $chapter_id, $volume_number, $chapter_number, $chapter_name, $upload_timestamp, $uploader, $group_id, $group_id_2, $group_id_3, $available ? 1 : 0]);
            }

			if ($group_id_2 && !$is_deleted)
				$sql->modify('chapter_upload', " UPDATE mangadex_groups SET group_last_updated = ? WHERE group_id = ? LIMIT 1 ", [$timestamp, $group_id_2]);

			if ($group_id_3 && !$is_deleted)
				$sql->modify('chapter_upload', " UPDATE mangadex_groups SET group_last_updated = ? WHERE group_id = ? LIMIT 1 ", [$timestamp, $group_id_3]);

			if (!$is_deleted && $user->user_uploads)
			    $sql->modify('chapter_upload', " UPDATE mangadex_users SET user_uploads = user_uploads + 1 WHERE user_id = ? LIMIT 1 ", [$user->user_id]);

			$memcached->delete("user_$user->user_id");
		}

		print $error; //returns "" or a message

		$result = ($error) ? 0 : 1;
		break;
}
