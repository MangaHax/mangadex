<?php
switch ($function) {
	case "import":

		$json = $_POST["json"];

		$insert = "";
		$search = '"comic_id":"';
		$string = $json;
		$bind = [];
		$found = strpos_recursive($string, $search);

		if($found) {
			foreach($found as $pos) {
				$start = $pos + 12;
				$end = strpos($json, '"', $start);
				$diff = $end - $start;
				$substr = substr($json, $start, $diff);
				$substr = prepare_numeric($substr);
				$insert .= "(?, ?, 1),";
				$bind = array_merge($bind, [$user->user_id, $substr]);

				$id = $substr;
				$memcached->delete("manga_$id");
				$memcached->delete("manga_{$id}_follows_user_id");
				$memcached->delete("user_{$user->user_id}_followed_manga_ids");
                $memcached->delete("user_{$user->user_id}_followed_manga_ids_api");
				$memcached->delete("user_{$user->user_id}_followed_manga_ids_key_pair");
                $memcached->delete("manga_{$id}_follows_user_{$user->user_id}");
			}

			$insert = rtrim($insert,",");

			$sql->modify('import', " INSERT IGNORE INTO mangadex_follow_user_manga (user_id, manga_id, follow_type) VALUES $insert ", $bind);

			$details = 1;

		}
		else {
			$details = "Something's wrong with your JSON.";
			print display_alert('danger', 'Failed', $details); // fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case 'increment_volume':
		$id = prepare_numeric($_GET['id']);

		$manga = new Manga($id);

		if (!$user->user_id)
			$error .= display_alert('danger', 'Failed', 'Your session has timed out. Please log in again.'); //success

		if (!$manga->manga_id)
			$error .= display_alert('danger', 'Failed', "This title does not exist.");

		if (!$error) {
			$sql->modify('increment_volume', ' UPDATE mangadex_follow_user_manga SET volume = FLOOR(volume) + 1 WHERE manga_id = ? AND user_id = ? LIMIT 1 ', [$manga->manga_id, $user->user_id]);

			$memcached->delete("user_{$user->user_id}_followed_manga_ids");
            $memcached->delete("user_{$user->user_id}_followed_manga_ids_api");
            $memcached->delete("manga_{$id}_follows_user_{$user->user_id}");

			$details = 1;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = (!is_numeric($details)) ? 0 : 1;

		break;

	case 'increment_chapter':
		$id = prepare_numeric($_GET['id']);

		$manga = new Manga($id);

		if (!$user->user_id)
			$error .= display_alert('danger', 'Failed', 'Your session has timed out. Please log in again.'); //success

		if (!$manga->manga_id)
			$error .= display_alert('danger', 'Failed', "This title does not exist.");

		if (!$error) {
			$sql->modify('increment_chapter', ' UPDATE mangadex_follow_user_manga SET chapter = FLOOR(chapter) + 1 WHERE manga_id = ? AND user_id = ? LIMIT 1 ', [$manga->manga_id, $user->user_id]);

			$memcached->delete("user_{$user->user_id}_followed_manga_ids");
            $memcached->delete("user_{$user->user_id}_followed_manga_ids_api");
            $memcached->delete("manga_{$id}_follows_user_{$user->user_id}");

			$details = 1;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = (!is_numeric($details)) ? 0 : 1;

		break;

	case 'edit_progress':
		$id = prepare_numeric($_GET['id']);
		$volume = remove_padding(htmlentities($_POST["volume"]));
		$chapter = remove_padding(htmlentities($_POST["chapter"]));

		$manga = new Manga($id);

		if (!$user->user_id)
			$error .= display_alert('danger', 'Failed', 'Your session has timed out. Please log in again.'); //success

		if (!$manga->manga_id)
			$error .= display_alert('danger', 'Failed', "This title does not exist.");

		if (!$error) {
			$sql->modify('edit_progress', ' UPDATE mangadex_follow_user_manga SET volume = ?, chapter = ? WHERE manga_id = ? AND user_id = ? LIMIT 1 ', [$volume, $chapter, $manga->manga_id, $user->user_id]);

			$memcached->delete("user_{$user->user_id}_followed_manga_ids");
            $memcached->delete("user_{$user->user_id}_followed_manga_ids_api");
            $memcached->delete("manga_{$id}_follows_user_{$user->user_id}");

			$details = 1;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = (!is_numeric($details)) ? 0 : 1;

		break;

	case 'manga_cover_delete':
		$id = prepare_numeric($_GET['manga_id']);
		$volume = remove_padding(htmlentities($_GET["volume"]));

		$manga = new Manga($id);

		if (!validate_level($user, 'gmod'))
			$error .= display_alert('danger', 'Failed', "You can't delete covers.");

		if (!$manga->manga_id)
			$error .= display_alert('danger', 'Failed', "This title does not exist.");

		if (!$error) {
			$old_ext = $sql->prep('old_ext', ' SELECT img FROM mangadex_manga_covers WHERE manga_id = ? AND volume = ? LIMIT 1 ', [$manga->manga_id, $volume], 'fetchColumn', '', -1);
			@unlink(ABS_DATA_BASEPATH . "/covers/{$manga->manga_id}v{$volume}.$old_ext");
			@unlink(ABS_DATA_BASEPATH . "/covers/{$manga->manga_id}v{$volume}.thumb.jpg");
			@unlink(ABS_DATA_BASEPATH . "/covers/{$manga->manga_id}v{$volume}.250.jpg");

			$sql->modify('manga_cover_delete', ' DELETE FROM mangadex_manga_covers WHERE manga_id = ? AND volume = ? LIMIT 1 ', [$manga->manga_id, $volume]);

			$memcached->delete("manga_{$id}_covers");
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = ($details) ? 0 : 1;
		break;

	case 'manga_cover_upload':
		// die('temporarily disabled');

        if (!validate_level($user, 'pr')) {
            //max of 5 attempts in 5 minutes
            $ip = _IP;
            $visit_count = $memcached->get('manga_cover_upload_' . $ip);

            if ($visit_count !== FALSE && $visit_count[0] > 5) {
                $error .= display_alert('danger', 'Failed', "Try again later.");
            }

            // Update limits
            if ($visit_count === false || time() - $visit_count[1] > 300) {
                $memcached->set('manga_cover_upload_' . $ip, [1, time()], 300);
            } else {
                $memcached->set('manga_cover_upload_' . $ip, [$visit_count[0] + 1, $visit_count[1]], 300);
            }
        }

		$id = prepare_numeric($_GET['id']);
		$volume = remove_padding(htmlentities($_POST["volume"]));
		// TODO: Standardize image upload process
		$old_file = $_FILES['file']['name'];

		$manga = new Manga($id);

		if (!validate_level($user, 'member'))
			$error .= display_alert('danger', 'Failed', "You can't upload covers.");

        if ($user->has_active_restriction(USER_RESTRICTION_EDIT_TITLES))
            $error .= display_alert('danger', 'Failed', $user->get_restriction_message(USER_RESTRICTION_EDIT_TITLES) ?? "You can't upload covers.");

		if (!validate_level($user, 'gmod') && $manga->manga_locked)
			$error .= display_alert('danger', 'Failed', "Editing has been locked to mods only.");

		if ($_FILES['file'] && $old_file)
			$error .= validate_image($_FILES['file'], 'file', 1024*1024*2); //2MB max filesize

		if (!$user->user_id)
			$error .= display_alert('danger', 'Failed', 'Your session has timed out. Please log in again.'); //success

		$old_ext = $sql->prep('old_ext', ' SELECT img FROM mangadex_manga_covers WHERE manga_id = ? AND volume = ? LIMIT 1 ', [$manga->manga_id, $volume], 'fetchColumn', '', -1);
		if ($old_ext && !validate_level($user, 'pr'))
			$error .= display_alert('danger', 'Failed', 'Only staff can replace covers.');

		if (!$error) {
			if ($old_file) {
				$arr = explode('.', $_FILES['file']['name']);
				$ext = strtolower(end($arr));

				if ($old_ext)
					@unlink(ABS_DATA_BASEPATH . "/covers/{$manga->manga_id}v{$volume}.$old_ext");

				move_uploaded_file($_FILES["file"]["tmp_name"], ABS_DATA_BASEPATH . "/covers/{$manga->manga_id}v{$volume}.$ext");

				generate_thumbnail(ABS_DATA_BASEPATH . "/covers/{$manga->manga_id}v{$volume}.$ext", 250);

				$sql->modify('manga_cover_upload', ' 
					INSERT INTO mangadex_manga_covers (manga_id, volume, img, user_id) VALUES (?, ?, ?, ?) 
					ON DUPLICATE KEY UPDATE img = ?, user_id = ?
				', [$manga->manga_id, $volume, $ext, $user->user_id, $ext, $user->user_id]);
			}

			$memcached->delete("manga_{$id}_covers");

			$details = $id;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = ($details) ? 0 : 1;
		break;

	case "manga_delete":
		$id = prepare_numeric($_GET['id']);

		$manga = new Manga($id);

		if (count(get_object_vars($manga))) {
			if (validate_level($user, 'gmod')) {
				$sql->modify('manga_delete', " DELETE FROM mangadex_mangas WHERE manga_id = ? LIMIT 1 ", [$id]);
				$sql->modify('manga_delete', " DELETE FROM mangadex_manga_alt_names WHERE manga_id = ? ", [$id]);
				$sql->modify('manga_delete', " DELETE FROM mangadex_manga_genres WHERE manga_id = ? ", [$id]);
				$sql->modify('manga_history_delete', " DELETE FROM mangadex_manga_history WHERE manga_id = ? ", [$id]);

				@unlink(ABS_DATA_BASEPATH . "/manga/$manga->manga_id.$manga->manga_image");
				@unlink(ABS_DATA_BASEPATH . "/manga/$manga->manga_id.thumb.jpg");

				$memcached->delete("manga_$id");

				$details = $id;
				print display_alert('success', 'Success', "Manga #$id has been deleted."); // success
			}
			else {
				$details = "You can't delete Manga #$id.";
				print display_alert('danger', 'Failed', $details); // fail
			}
		}
		else {
			$details = "Manga #$id does not exist.";
			print display_alert('danger', 'Failed', $details); // fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "manga_edit":
		$id = prepare_numeric($_GET['id']);

		$manga = new Manga($id);

		$history_action = 'edit';
		$history_changes = [];

		if (count(get_object_vars($manga))) {
			$manga_name = htmlentities($_POST["manga_name"]);
			$manga_alt_names = htmlentities(trim($_POST["manga_alt_names"]));
			$manga_author = htmlentities($_POST["manga_author"]);
			$manga_artist = htmlentities($_POST["manga_artist"]);
			$manga_last_chapter = htmlentities($_POST["manga_last_chapter"]);
			$manga_last_volume = empty($_POST["manga_last_volume"]) ? null : prepare_numeric($_POST["manga_last_volume"]);
			$manga_lang_id = prepare_numeric($_POST["manga_lang_id"]);
			$manga_status_id = prepare_numeric($_POST["manga_status_id"]);
			$manga_demo_id = prepare_numeric($_POST["manga_demo_id"]);
			$manga_hentai = isset($_POST["manga_hentai"]) ? 1 : 0;
			$manga_description = htmlentities($_POST["manga_description"]);
			$manga_mod_notes = isset($_POST["manga_mod_notes"]) ? htmlentities($_POST["manga_mod_notes"]) : '';
			$old_file = $_FILES['file']['name'];
			$old_alt_names = $manga->get_manga_alt_names();
			$old_related_manga = $manga->get_related_manga();
			$old_ext_links = json_decode($manga->manga_links, 1);

			if ($_FILES["file"] && $old_file)
				$error .= validate_image($_FILES["file"]);

			if (!validate_level($user, 'contributor'))
				$error .= display_alert('danger', 'Failed', "You can't edit this title.");

			if ($user->has_active_restriction(USER_RESTRICTION_EDIT_TITLES))
			    $error .= display_alert('danger', 'Failed', $user->get_restriction_message(USER_RESTRICTION_EDIT_TITLES) ?? "You can't edit this title.");

			if (!validate_level($user, 'gmod') && $manga->manga_locked)
				$error .= display_alert('danger', 'Failed', "Editing has been locked to mods only.");
			elseif ($manga->manga_locked)
                $history_action = 'edit_locked';

			if ($manga_last_volume > 255)
			    $error .= display_alert('danger', 'Failed', 'Manga last volume number is too large');

			if (mb_strlen($manga_last_chapter) > 8)
                $error .= display_alert('danger', 'Failed', 'Manga last chapter number is too large');

			if (!$error) {
				//manga_links
				if (!empty($_POST['link_type'])) {
					foreach ($_POST['link_type'] as $key => $link_type) {
						if (!empty($_POST['link_id'][$key]))
							$array[$link_type] = str_replace(['javascript:'], '', htmlentities($_POST['link_id'][$key]));
					}
					$manga_links = json_encode($array);
				}
				else
					$manga_links = NULL;

                // Track changes
                if ($manga->manga_name != $manga_name) $history_changes[] = "Name: [$manga->manga_name] -> [$manga_name]";
                if ($manga->manga_author != $manga_author) $history_changes[] = "Author: [$manga->manga_author] -> [$manga_author]";
                if ($manga->manga_artist != $manga_artist) $history_changes[] = "Artist: [$manga->manga_artist] -> [$manga_artist]";
                if ($manga->manga_lang_id != $manga_lang_id) $history_changes[] = "Language changed: [$manga->manga_lang_id] -> [$manga_lang_id]";
                if ($manga->manga_last_chapter != $manga_last_chapter) $history_changes[] = "Last Chapter-Id changed: [$manga->manga_last_chapter] -> [$manga_last_chapter]";
                if ($manga->manga_last_volume != $manga_last_volume) $history_changes[] = "Last Volume-Id changed: [$manga->manga_last_volume] -> [$manga_last_volume]";
                if ($manga->manga_status_id != $manga_status_id) $history_changes[] = "Status-Id changed: [$manga->manga_status_id] -> [$manga_status_id]";
                if ($manga->manga_demo_id != $manga_demo_id) $history_changes[] = "Demographic-Id changed: [$manga->manga_demo_id] -> [$manga_demo_id]";
                if ($manga->manga_hentai != $manga_hentai) $history_changes[] = "Hentai Status changed: [".($manga->manga_hentai ? 1 : 0)."] -> [".($manga_hentai ? 1 : 0)."]";
                if ($manga->manga_description != $manga_description) $history_changes[] = "Description changed. (Not tracked)";

				$sql->modify('manga_edit', " 
				UPDATE mangadex_mangas SET manga_name = ?, manga_author = ?, manga_artist = ?, manga_lang_id = ?, manga_status_id = ?, manga_hentai = ?, manga_demo_id = ?, manga_description = ?, manga_links = ?, manga_last_volume = ?, manga_last_chapter = ?, manga_mod_notes = ? WHERE manga_id = ? LIMIT 1 
				", [$manga_name, $manga_author, $manga_artist, $manga_lang_id, $manga_status_id, $manga_hentai, $manga_demo_id, $manga_description, $manga_links, $manga_last_volume, $manga_last_chapter, $manga_mod_notes, $id]);

				//manga genres
				$sql->modify('manga_edit', " DELETE FROM mangadex_manga_genres WHERE manga_id = ? ", [$id]);

                $_POST["manga_genres"] = $_POST["manga_genres"] ?? []; // Fixes case where all genres are removed
				if (is_array($_POST["manga_genres"])) {

                    // Track genre history
                    $old_genres = $manga->get_manga_genres();
                    $_POST['manga_genres'] = array_map(function ($e) {return (int)$e;}, $_POST['manga_genres']); // Convert genre ids to int
                    $genres_removed = array_diff($old_genres, $_POST["manga_genres"] ?? []);
                    $genres_added = array_diff($_POST["manga_genres"] ?? [], $old_genres);

                    $tags = new Tags();
                    $genre_inserts = [];
                    foreach ($_POST["manga_genres"] as $genre_id) {
						$genre_id = prepare_numeric($genre_id);
						if ($tags->getTagById($genre_id)) {
						    $genre_inserts[] = $genre_id;
                        }
                    }
                    if (!empty($genre_inserts)) {
                        $values = implode(", ", array_fill(0, count($genre_inserts), "($id, ?)"));
                        $sql->modify('manga_edit', " INSERT IGNORE INTO mangadex_manga_genres (manga_id, genre_id) VALUES $values ", $genre_inserts);
                    }

                    if (!empty($genres_added))
					    $history_changes[] = 'Genres Added: ['.implode(', ', $genres_added).']';
					if (!empty($genres_removed))
					    $history_changes[] = 'Genres Removed: ['.implode(', ', $genres_removed).']';
				}

				//manga alt names
                $manga_alt_names = explode("\r\n", $manga_alt_names);

                if ($old_alt_names != $manga_alt_names) {
                    $alt_removed = array_diff($old_alt_names, $manga_alt_names);
                    $alt_added = array_diff($manga_alt_names, $old_alt_names);
                    if (!empty($alt_added))
                        $history_changes[] = 'Alt titles Added: ['.implode(', ', $alt_added).']';
                    if (!empty($alt_removed))
                        $history_changes[] = 'Alt titles Removed: ['.implode(', ', $alt_removed).']';
                }

				$sql->modify('manga_edit', " DELETE FROM mangadex_manga_alt_names WHERE manga_id = ? ", [$id]);

				if (!empty($manga_alt_names)) {
					foreach (array_filter($manga_alt_names) as $alt_name) {
						$sql->modify('manga_edit', " INSERT IGNORE INTO mangadex_manga_alt_names (manga_id, alt_name) VALUES (?, ?) ", [$id, $alt_name]);
					}
				}

				//manga relations
				$sql->modify('manga_edit', " DELETE FROM mangadex_manga_relations WHERE manga_id = ? ", [$id]);
				$sql->modify('manga_edit', " DELETE FROM mangadex_manga_relations WHERE related_manga_id = ? ", [$id]);

				if (!empty($_POST["relation_type"])) {
					$relation_types = new Relation_Types();
					foreach ($_POST["relation_type"] as $key => $relation_type) {
						if ($id != $_POST["related_manga_id"][$key]) {
							$other_id = $_POST["related_manga_id"][$key];
							$memcached->delete("manga_{$other_id}_related_manga");
							$sql->modify('manga_edit', " INSERT IGNORE INTO mangadex_manga_relations (manga_id, relation_id, related_manga_id) VALUES (?, ?, ?) ", [$id, $relation_type, $other_id]);
							$sql->modify('manga_edit', " INSERT IGNORE INTO mangadex_manga_relations (manga_id, relation_id, related_manga_id) VALUES (?, ?, ?) ", [$other_id, $relation_types->{$relation_type}->pair_id, $id]);
						}
					}
				}

				if ($old_file) {
					$arr = explode(".", $_FILES["file"]["name"]);
					$ext = strtolower(end($arr));

					if ($manga->manga_image)
						@unlink(ABS_DATA_BASEPATH . "/manga/$manga->manga_id.$manga->manga_image");

					move_uploaded_file($_FILES["file"]["tmp_name"], ABS_DATA_BASEPATH . "/manga/$manga->manga_id.$ext");

					$sql->modify('manga_edit', " UPDATE mangadex_mangas SET manga_image = ? WHERE manga_id = ? LIMIT 1 ", [$ext, $manga->manga_id]);

					generate_thumbnail(ABS_DATA_BASEPATH . "/manga/$manga->manga_id.$ext", 1);

					//send to image server
					/*
					$url = "https://s1.mangadex.org/upload.images.php";
					$headers = [];

					$data = ['file' => base64_encode(file_get_contents(ABS_DATA_BASEPATH . "/manga/$manga->manga_id.$ext")), 'filename' => "$manga->manga_id.$ext" ];
					$return_msg = httpPost($url, $data, $headers);

					$data = ['file' => base64_encode(file_get_contents(ABS_DATA_BASEPATH . "/manga/$manga->manga_id.thumb.jpg")), 'filename' => "$manga->manga_id.thumb.jpg" ];
					$return_msg = httpPost($url, $data, $headers);
					*/

					$history_changes[] = "Thumbnail changed.";
				}

				$memcached->delete("manga_$id");
				$memcached->delete("manga_{$id}_alt_names");
				$memcached->delete("manga_{$id}_genres");
				$memcached->delete("manga_{$id}_related_manga");
                $memcached->delete("user_{$user->user_id}_followed_manga_ids_api");

				// Insert changes entry
				if (!empty($history_changes)) {
					$sql->modify('manga_history', 'INSERT INTO mangadex_manga_history (`user_id`, `manga_id`, `timestamp`, `action`, `changes`) VALUES (?,?,?,?,?)',
						[$user->user_id ?? 1, $manga->manga_id, time(), $history_action, json_encode($history_changes)]);
				}

				$details = $id;
			}
			else {
				$details = $error;
				print $error; //returns "" or a message
			}
		}
		else {
			$details = "Manga #$id does not exist.";
			print display_alert('danger', 'Failed', $details); // fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "manga_add":
		$manga_name = htmlentities($_POST["manga_name"]);

		if (validate_level($user, 'member')) {
            if ($user->has_active_restriction(USER_RESTRICTION_EDIT_TITLES))
                $error .= display_alert('danger', 'Failed', $user->get_restriction_message(USER_RESTRICTION_EDIT_TITLES) ?? "You can't add titles.");
			else if ($_FILES["file"])
				$error .= validate_image($_FILES["file"]);
			else
				$error .= display_alert('danger', 'Failed', "Missing image."); //missing image
		}
		else {
			if (!$user->user_id)
				$error .= display_alert('danger', 'Failed', "Your session has timed out. Please log in again."); //timed_out
			else
				$error .= display_alert('danger', 'Failed', "You can't upload."); //banned
		}

		//if no errors, then upload
		if (!$error) {
			$manga_alt_names = htmlentities($_POST["manga_alt_names"]);
			$manga_author = htmlentities($_POST["manga_author"]);
			$manga_artist = htmlentities($_POST["manga_artist"]);
			$manga_lang_id = prepare_numeric($_POST["manga_lang_id"]);
			$manga_status_id = prepare_numeric($_POST["manga_status_id"]);
			$manga_hentai = isset($_POST["manga_hentai"]) ? 1 : 0;
			$manga_demo_id = prepare_numeric($_POST["manga_demo_id"]);
			$manga_description = htmlentities($_POST["manga_description"]);

			$arr = explode(".", $_FILES["file"]["name"]);
			$ext = strtolower(end($arr));

			$manga_id = $sql->modify('manga_add', " 
			INSERT INTO mangadex_mangas (manga_id, manga_name, manga_author, manga_artist, manga_lang_id, manga_status_id, manga_hentai, manga_demo_id, manga_description, manga_image, manga_rating, manga_rated_users, manga_views, manga_follows, manga_comments, manga_locked, manga_links, manga_last_uploaded, thread_id, manga_last_chapter, manga_mod_notes) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, 0, 0, 0, NULL, 0, 0, 0, '') 
			", [$manga_name, $manga_author, $manga_artist, $manga_lang_id, $manga_status_id, $manga_hentai, $manga_demo_id, $manga_description, $ext]);

			if (!empty($_POST["manga_genres"])) {
				$tags = new Tags();
				$genre_inserts = [];
				foreach ($_POST["manga_genres"] as $genre_id) {
					$genre_id = prepare_numeric($genre_id);
					if ($tags->getTagById($genre_id)) {
						$genre_inserts[] = $genre_id;
					}
				}
				if (!empty($genre_inserts)) {
					$values = implode(", ", array_fill(0, sizeof($genre_inserts), "($manga_id, ?)"));
					$sql->modify('manga_add', " INSERT IGNORE INTO mangadex_manga_genres (manga_id, genre_id) VALUES $values ", $genre_inserts);
				}
			}

			if (!empty($manga_alt_names)) {
				$arr = explode("\r\n", $manga_alt_names);
				foreach (array_filter($arr) as $alt_name) {
					$sql->modify('manga_add', " INSERT IGNORE INTO mangadex_manga_alt_names (manga_id, alt_name) VALUES (?, ?) ", [$manga_id, $alt_name]);
				}
			}

			move_uploaded_file($_FILES["file"]["tmp_name"], ABS_DATA_BASEPATH . "/manga/$manga_id.$ext");

			generate_thumbnail(ABS_DATA_BASEPATH . "/manga/$manga_id.$ext", 1);

			//send to image server
			/*
			$url = "https://s1.mangadex.org/upload.images.php";
			$headers = [];

			$data = ['file' => base64_encode(file_get_contents(ABS_DATA_BASEPATH . "/manga/$manga_id.$ext")), 'filename' => "$manga_id.$ext" ];
			$return_msg = httpPost($url, $data, $headers);

			$data = ['file' => base64_encode(file_get_contents(ABS_DATA_BASEPATH . "/manga/$manga_id.thumb.jpg")), 'filename' => "$manga_id.thumb.jpg" ];
			$return_msg = httpPost($url, $data, $headers);
			*/

			// Add create manga to history
			$sql->modify('manga_history', 'INSERT INTO mangadex_manga_history (`user_id`, `manga_id`, `timestamp`, `action`, `changes`) VALUES (?,?,?,?,?)',
				[$user->user_id ?? 1, $manga_id, time(), 'manga_create', json_encode(['Created manga entry.'])]);
		}

		print $error;

		$result = ($error) ? 0 : 1;
		break;

	case "manga_follow":
		$id = prepare_numeric($_GET['id']);
		$type_id = prepare_numeric($_GET["type"]);

		if (validate_level($user, 'member')) {
			$sql->modify('manga_follow', " INSERT INTO mangadex_follow_user_manga (user_id, manga_id, follow_type) VALUES (?, ?, ?) 
			ON DUPLICATE KEY UPDATE follow_type = ? ", [$user->user_id, $id, $type_id, $type_id]);

			$sql->modify('manga_follow', " UPDATE mangadex_mangas SET manga_follows = 
				(SELECT count(*) FROM mangadex_follow_user_manga WHERE manga_id = ?) 
				WHERE manga_id = ? LIMIT 1 ", [$id, $id]);

			if (in_array($type_id, [2, 6])) {
				$search["manga_id"] = $id; //manga_id
				$chapters = new Chapters($search);
				$chapters_obj = $chapters->query_read("chapter_id ASC", 2000, 1);

				if (count($chapters_obj) > 0) {
					foreach ($chapters_obj as $chapter) {
						$ch_array[] = $chapter['chapter_id'];
					}
					$in = prepare_in($ch_array);
					$sql->modify('manga_follow', " DELETE FROM mangadex_chapter_views WHERE user_id = ? AND chapter_id IN ($in) ", array_merge([$user->user_id], $ch_array));
				}
			}

			$memcached->delete("manga_$id");
			$memcached->delete("manga_{$id}_follows_user_id");
			$memcached->delete("user_{$user->user_id}_followed_manga_ids");
            $memcached->delete("user_{$user->user_id}_followed_manga_ids_api");
			$memcached->delete("user_{$user->user_id}_followed_manga_ids_key_pair");
            $memcached->delete("manga_{$id}_follows_user_{$user->user_id}");

			$details = $id;
		}
		else {
			$details = "You have timed out. Please log in again.";
			print display_alert('danger', 'Failed', $details); // fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "manga_unfollow":
		$id = prepare_numeric($_GET['id']);
		$type_id = prepare_numeric($_GET["type"]);

		if (validate_level($user, 'member')) {
			$sql->modify('manga_unfollow', " DELETE FROM mangadex_follow_user_manga WHERE user_id = ? AND manga_id = ? LIMIT 1 ", [$user->user_id, $id]);

			$sql->modify('manga_unfollow', " UPDATE mangadex_mangas SET manga_follows = 
				(SELECT count(*) FROM mangadex_follow_user_manga WHERE manga_id = ?) 
				WHERE manga_id = ? LIMIT 1 ", [$id, $id]);

			$search["manga_id"] = $id; //manga_id
			$chapters = new Chapters($search);
			$chapters_obj = $chapters->query_read("chapter_id ASC", 2000, 1);

			if (count($chapters_obj) > 0) {
				foreach ($chapters_obj as $chapter) {
					$ch_array[] = $chapter['chapter_id'];
				}
				$in = prepare_in($ch_array);
				$sql->modify('manga_unfollow', " DELETE FROM mangadex_chapter_views WHERE user_id = ? AND chapter_id IN ($in) ", array_merge([$user->user_id], $ch_array));
			}

			$memcached->delete("manga_$id");
			$memcached->delete("manga_{$id}_follows_user_id");
			$memcached->delete("user_{$user->user_id}_followed_manga_ids");
            $memcached->delete("user_{$user->user_id}_followed_manga_ids_api");
			$memcached->delete("user_{$user->user_id}_followed_manga_ids_key_pair");
            $memcached->delete("manga_{$id}_follows_user_{$user->user_id}");

			$details = $id;
		}
		else {
			$details = "You have timed out. Please log in again.";
			print display_alert('danger', 'Failed', $details); // fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "manga_rating":
		$id = prepare_numeric($_GET['id']);
		$rating = prepare_numeric($_GET["rating"]);

		if ($rating < 0)
			$rating = 0;
		elseif ($rating > 10)
			$rating = 10;
		else
			$rating = round($rating, 0);

		if ($user->user_id) {
		    if($rating == 0){
                $sql->modify('manga_rating', " 
			DELETE FROM mangadex_manga_ratings WHERE manga_id = ? AND user_id = ? LIMIT 1 
			", [$id, $user->user_id]);
            }
		    else{
                $sql->modify('manga_rating', " 
			INSERT INTO mangadex_manga_ratings (manga_id, user_id, rating) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = ? 
			", [$id, $user->user_id, $rating, $rating]);
            }

			$memcached->delete("manga_{$id}_user_ratings");
			$memcached->delete("manga_{$id}_user_rating_$user->user_id");
            $memcached->delete("user_{$user->user_id}_followed_manga_ids_api");
			$memcached->delete("user_{$user->user_id}_manga_ratings");

			$manga = new Manga($id);

			$ratings_array = $manga->get_user_ratings();
			$average_rating = array_sum($ratings_array) / (count($ratings_array) ?: 1);

			$site_average_rating = $sql->query_read('site_average', ' SELECT AVG(rating) FROM mangadex_manga_ratings ', 'fetchColumn', '', 3600);
			$no_ratings_per_title = $sql->query_read('ratings_per_title', ' SELECT COUNT(*) AS Rows FROM mangadex_manga_ratings GROUP BY manga_id ', 'fetchAll', PDO::FETCH_COLUMN, 3600);
			$average_no_ratings_per_title = array_sum($no_ratings_per_title) / count($no_ratings_per_title);

			$bayesian = bayesian_average($ratings_array, $average_no_ratings_per_title, $average_rating, $site_average_rating);
			
            if(is_nan($average_rating)){
                $average_rating = 0;
            }
			if(is_nan($bayesian)){
                $bayesian = 0;
            }

			$sql->modify('manga_rating', " UPDATE mangadex_mangas SET manga_bayesian = ?, manga_rating = ?, manga_rated_users = (SELECT count(*) FROM mangadex_manga_ratings WHERE manga_id = ?) WHERE manga_id = ? ", [$bayesian, $average_rating, $id, $id]);

			$memcached->delete("manga_$id");

			$details = $id;
			print display_alert('success', 'Success', "You have rated Manga #$id."); //success
		}
		else {
			$details = "Your session has timed out. Please log in again.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "manga_lock":
		$id = prepare_numeric($_GET['id']);

		if (validate_level($user, 'gmod')) {
			$sql->modify('manga_lock', " UPDATE mangadex_mangas SET manga_locked = 1 WHERE manga_id = ? ", [$id]);

			$memcached->delete("manga_$id");

			// Add history entry
            $sql->modify('manga_history', "INSERT INTO mangadex_manga_history (`user_id`, `manga_id`, `timestamp`, `action`) VALUES (?,?,?,?)", [$user->user_id, $id, time(), 'lock']);

			$details = $id;
		}
		else {
			$details = "You can't lock manga.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "manga_unlock":
		$id = prepare_numeric($_GET['id']);

		if (validate_level($user, 'gmod')) {
			$sql->modify('manga_unlock', " UPDATE mangadex_mangas SET manga_locked = 0 WHERE manga_id = ? ", [$id]);

			$memcached->delete("manga_$id");

            // Add history entry
            $sql->modify('manga_history', "INSERT INTO mangadex_manga_history (`user_id`, `manga_id`, `timestamp`, `action`) VALUES (?,?,?,?)", [$user->user_id, $id, time(), 'unlock']);

            $details = $id;
		}
		else {
			$details = "You can't unlock manga.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "manga_report":
		$id = prepare_numeric($_GET['id']);
		$report_text = htmlentities($_POST["report_text"]);

		$report_restriction = $user->has_active_restriction(USER_RESTRICTION_CREATE_REPORT) && !validate_level($user, 'mod');

		if (validate_level($user, 'member') && !$report_restriction) {
			if (!$report_text) {
				$details = "Please give more information.";
				print display_alert('danger', 'Failed', $details); //fail
			}
			else {
				$sql->modify('manga_report', " INSERT INTO mangadex_reports_manga (report_id, report_manga_id, report_timestamp, report_info, report_user_id, report_mod_user_id, report_conclusion) 
					VALUES (NULL, ?, UNIX_TIMESTAMP(), ?, ?, 0, 0) ", [$id, $report_text, $user->user_id]);

                $memcached->delete('mod_report_count');

				$details = $id;

                post_on_discord(DISCORD_WEBHOOK_REPORT, [
                    'username' => $user->username,
                    'embeds' => [
                        [
                            'title' => 'Title Report',
                            'url' => URL . 'mod/manga_reports/new',
                            'footer' => [
                                'text' => $report_text
                            ]
                        ]
                    ]
                ]);
			}
		}
		elseif ($report_restriction) {
		    $details = $user->get_restriction_message(USER_RESTRICTION_CREATE_REPORT) ?? "You can't report Manga $id.";
		    print display_alert('danger', 'Failed', $details);
        }
		else {
			$details = "You can't report Manga $id.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "manga_report_accept":
		$id = prepare_numeric($_GET['id']);

		if (validate_level($user, 'gmod')) {
			$sql->modify('manga_report_accept', " UPDATE mangadex_reports_manga SET report_conclusion = 1, report_mod_user_id = ? WHERE report_id = ? LIMIT 1 ", [$user->user_id, $id]);

			print display_alert('success', 'Success', "Report #$id accepted.");  //success

            $memcached->delete('mod_report_count');

			$details = $id;
			$result = 1;
		}
		break;

	case "manga_report_reject":
		$id = prepare_numeric($_GET['id']);

		if (validate_level($user, 'gmod')) {
			$sql->modify('manga_report_reject', " UPDATE mangadex_reports_manga SET report_conclusion = 2, report_mod_user_id = ? WHERE report_id = ? LIMIT 1 ", [$user->user_id, $id]);

			print display_alert('success', 'Success', "Report #$id rejected.");  //success

            $memcached->delete('mod_report_count');

			$details = $id;
			$result = 1;
		}
		break;

	case "admin_edit_manga":
		$id = prepare_numeric($_GET['id']);
		$old_id = prepare_numeric($_POST["old_id"]);

		if (validate_level($user, 'gmod')) {
			$sql->modify('admin_edit_manga', " UPDATE mangadex_chapters SET manga_id = ? WHERE manga_id = ? ", [$old_id, $id]);
			$sql->modify('admin_edit_manga', " UPDATE IGNORE mangadex_follow_user_manga SET manga_id = ? WHERE manga_id = ? ", [$old_id, $id]);
			$sql->modify('admin_edit_manga', " UPDATE IGNORE mangadex_manga_ratings SET manga_id = ? WHERE manga_id = ? ", [$old_id, $id]);
			$sql->modify('admin_edit_manga', " UPDATE IGNORE mangadex_manga_history SET manga_id = ? WHERE manga_id = ? ", [$old_id, $id]);
			$sql->modify('admin_edit_manga', " UPDATE mangadex_forum_posts SET thread_id = (SELECT thread_id FROM mangadex_mangas WHERE manga_id = ?) WHERE thread_id = (SELECT thread_id FROM mangadex_mangas WHERE manga_id = ?) ", [$old_id, $id]);

			$sql->modify('admin_edit_manga', " DELETE FROM mangadex_mangas WHERE manga_id = ? LIMIT 1 ", [$id]);

			$memcached->delete("manga_$id");
			$memcached->delete("manga_$old_id");

			$details = $id;
			print display_alert('success', 'Success', "Manga #$id has been edited.");  //success
		}
		else {
			$details = "You can't edit manga.";
			print display_alert('danger', 'Failed', $details); //fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

	case "remove_featured":
		$list_id = prepare_numeric($_GET["list_id"]);
		$manga_id = prepare_numeric($_GET["manga_id"]);

		if (validate_level($user, 'gmod')) {
			$sql->modify('remove_featured', " DELETE FROM mangadex_manga_featured WHERE list_id = ? AND manga_id = ? LIMIT 1 ", [$list_id, $manga_id]);

			$memcached->delete("manga_list_$list_id");
		}
		print display_alert('success', 'Success', "Title removed.");  //success

		$details = $manga_id;
		$result = 1;
		break;

	case "add_featured":
		$list_id = prepare_numeric($_GET['id']);
		$manga_id = prepare_numeric($_POST["manga_id"]);

		if (validate_level($user, 'gmod')) {
			$sql->modify('add_featured', " INSERT IGNORE INTO mangadex_manga_featured (list_id, manga_id) VALUES (?, ?) ", [$list_id, $manga_id]);

			$memcached->delete("manga_list_$list_id");
		}

		$details = $manga_id;
		$result = 1;
		break;
}
