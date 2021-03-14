<?php
switch ($function) {
	case "start_empty_thread":
		$thread_type  = is_numeric($_POST["type"]) ? (int) $_POST["type"] : 3;
		// TODO: Change hardcoded forum ids
		$forum_id = ($thread_type === 1) ? 11      : (($thread_type === 2) ? 14      : 12);
		$type     = ($thread_type === 1) ? 'manga' : (($thread_type === 2) ? 'group' : 'chapter');
		$type_id  = prepare_numeric($_POST["type_id"]);
		$has_thread = false;
		if (!$user->user_id)
			$error .= display_alert("danger", "Failed", "Your session has timed out. Please log in again."); //success

        if (!validate_level($user, 'mod') && $user->has_active_restriction(USER_RESTRICTION_POST_COMMENT)) {
            $details = $user->get_restriction_message(USER_RESTRICTION_POST_COMMENT) ?? "Thread creation failed!";
            $error .= display_alert("danger", "Failed", $details);
        }

		// Check existence of thread.
		if (!$error) {
			// These values are probably in cache since the user is on a page for them. So lets just load that
			if ($thread_type === 1)
				$type_instance = new Manga($type_id);
			elseif ($thread_type === 2)
				$type_instance = new Group($type_id);
			else
				$type_instance = new Chapter($type_id);
			if ($type_instance) {
				if ($type_instance->thread_id) {
					$has_thread = true;
					$details = $type_instance->thread_id;
					//$error = display_alert("danger", "Failed", ucfirst($type) . " has a thread (#{$type_instance->thread_id})!");
				}
			}
			else {
				$error = display_alert("danger", "Failed", ucfirst($type) . " does not exist!");
			}
		}

		// Attempt to create a thread if there were no errors, and one does not already exist
		if (!$error && !$has_thread) {
			$sql->beginTransaction();
			try {
				$thread_id = $sql->modify('start_empty_thread', " INSERT INTO mangadex_threads (thread_id, thread_name, forum_id, user_id, thread_posts, thread_views, thread_locked, thread_sticky, thread_deleted, last_post_user_id, last_post_timestamp, last_post_id) 
				VALUES (NULL, ?, ?, 1, 0, 0, 0, 0, 0, 0, 0, 0) ", [$type_id, $forum_id]);

				$sql->modify('start_empty_thread', " 
				UPDATE mangadex_forums 
				SET last_thread_id = ?, 
					count_threads = (SELECT count(*) FROM mangadex_threads WHERE forum_id = ?), 
					count_posts = (SELECT SUM(thread_posts) FROM mangadex_threads WHERE forum_id = ?) 
				WHERE forum_id = ? LIMIT 1 ", [$thread_id, $forum_id, $forum_id, $forum_id]);

				$sql->modify('start_empty_thread', " 
				UPDATE mangadex_{$type}s
				SET thread_id = ? 
				WHERE {$type}_id = ? AND thread_id = 0 LIMIT 1
				", [$thread_id, $type_id]);

				$details = $thread_id;
				$sql->commit();

				$memcached->delete("{$type}_$type_id");
				$memcached->delete("forum_$forum_id");
			}
			catch (\Throwable $e) {
				// We lost connection to the db or some other internal error
				$sql->rollBack();
				$error = display_alert('danger', 'Failed', 'Please refresh your browser and try again.');
			}
		}

		// Alert user if any errors occurred
		if ($error) {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = ($details) ? 0 : 1;
		break;

	case "start_thread":
		$forum_id = prepare_numeric($_GET["id"]);
		$subject = htmlentities($_POST["subject"]);
		$post_text = htmlentities($_POST["text"]);
		$poll_items = htmlentities($_POST["poll_items"]);
		$poll_days = prepare_numeric($_POST["poll_days"]);

		$threads = new Forum_Threads($forum_id);

		$poll_items_array = explode("\r\n", $poll_items); //array of poll items

        $error = "";

        if (!validate_level($user, 'mod') && $user->has_active_restriction(USER_RESTRICTION_POST_COMMENT)) {
            $details = $user->get_restriction_message(USER_RESTRICTION_POST_COMMENT) ?? "Thread creation failed!";
            $error .= display_alert("danger", "Failed", $details);
        }
        elseif (!$subject)
			$error .= display_alert("danger", "Failed", "Your subject is empty.");
		elseif (!$post_text)
			$error .= display_alert("danger", "Failed", "Your post is empty.");
		elseif (!empty($poll_items) && count($poll_items_array) == 1) //poll_items not empty and only 1 item in the poll_items
			$error .= display_alert("danger", "Failed", "You need at least 2 poll items.");
		elseif (!$user->user_id)
			$error .= display_alert("danger", "Failed", "Your session has timed out. Please log in again.");
		elseif (!validate_level($user, $threads->start_thread_level))
			$error .= display_alert("danger", "Failed", "You don't have permission to start a thread in this forum.");

        if (!$user->premium && !validate_level($user, 'pr') && !$error) {
            $MIN_FIRST_THREAD_SECONDS = 60*60*24;
            $MIN_THREAD_INTERVAL_SECONDS = 60*5;
            if ($user->joined_timestamp + $MIN_FIRST_THREAD_SECONDS > time()) {
                $error .= display_alert("danger", "Failed", "You have to wait until you can make your first thread.");
            } else {
                $last_timestamp = $sql->prep('start_thread', "
                    SELECT MIN(p.timestamp) AS op_timestamp
                    FROM mangadex_threads t
                    INNER JOIN mangadex_forum_posts p ON t.user_id = p.user_id
                    WHERE t.thread_id = p.thread_id AND p.user_id = ?
                    GROUP BY t.thread_id
                    ORDER BY op_timestamp DESC
                    LIMIT 1
                ", [$user->user_id], 'fetch', PDO::FETCH_COLUMN, -1);
                if ($last_timestamp + $MIN_THREAD_INTERVAL_SECONDS > time()) {
                    $error .= display_alert("danger", "Failed", "You have to wait until you can make your next thread.");
                }
            }
        }

		if (!$error) {
			if (!empty($poll_items))
				$poll_expire_timestamp = $timestamp + $poll_days * 60 * 60 * 24;
			else
				$poll_expire_timestamp = 0;

			$thread_id = $sql->modify('start_thread', " INSERT INTO mangadex_threads (thread_id, thread_name, forum_id, user_id, thread_posts, thread_views, thread_locked, thread_sticky, thread_deleted, last_post_user_id, last_post_timestamp, last_post_id, poll_expire_timestamp) 
				VALUES (NULL, ?, ?, ?, 1, 0, 0, 0, 0, ?, ?, 0, ?) ", [$subject, $forum_id, $user->user_id, $user->user_id, $timestamp, $poll_expire_timestamp]); // insert thread into threads table

			if (!empty($poll_items)) {
				foreach ($poll_items_array as $item) {
					$sql->modify('start_thread', " INSERT INTO mangadex_forum_poll_items (item_id, item_name, thread_id) VALUES (NULL, ?, ?) ", [$item, $thread_id]); // insert poll_items into poll_items table
				}
			}

			$sql->modify('start_thread', " 
				UPDATE mangadex_forums 
				SET last_thread_id = ?, 
					count_threads = (SELECT count(*) FROM mangadex_threads WHERE forum_id = ?), 
					count_posts = (SELECT SUM(thread_posts) FROM mangadex_threads WHERE forum_id = ?) 
				WHERE forum_id = ? LIMIT 1 ", [$thread_id, $forum_id, $forum_id, $forum_id]); //update forum metadata

			$post_id = $sql->modify('start_thread', " INSERT INTO mangadex_forum_posts (post_id, thread_id, user_id, timestamp, edit_timestamp, edit_user_id, text, deleted) 
				VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), 0, 0, ?, 0) ", [$thread_id, $user->user_id, $post_text]); //insert post into posts table

			$sql->modify('start_thread', " 
				UPDATE mangadex_threads 
				SET last_post_id = ? 
				WHERE thread_id = ? LIMIT 1 ", [$post_id, $thread_id]); //update thread metadata

			$callback = new Notify_Callback($post_id);
			$post_text = preg_replace_callback("/(@[a-zA-Z0-9_-]+)/", [$callback, 'notify'], $post_text);
			$sql->modify('start_thread', " UPDATE mangadex_forum_posts SET text = ? WHERE post_id = ? ", [$post_text, $post_id]);

			if ($forum_id == 3) { //announcement stuff
				$sql->modify('start_thread', " UPDATE mangadex_users SET read_announcement = ? ", [0]);
				$memcached->delete('top_announce');
			}

			$memcached->delete("forum_$forum_id");

			$details = $thread_id;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = ($details) ? 0 : 1;
		break;

	case "delete_threads":
		$forum_id = prepare_numeric($_GET["id"]);

		if (!validate_level($user, 'pr'))
			$error = "You can't delete threads.";
		elseif(!$_POST['thread_id'])
			$error = "No threads selected.";

		if (!$error) {
			foreach($_POST['thread_id'] as $thread_id) {
				$thread_id = prepare_numeric($thread_id);
				$sql->modify('delete_threads', " UPDATE mangadex_threads SET thread_deleted = 1 WHERE thread_id = ? LIMIT 1 ", [$thread_id]);
			}

			$memcached->delete("forum_$forum_id");
		}
		else {
			$details = $error;
			print display_alert("danger", "Failed", $error);
		}

		$result = ($details) ? 0 : 1;
		break;

	case "lock_thread":
		$thread_id = prepare_numeric($_GET["id"]);

		if (!validate_level($user, 'pr'))
			$error = "You can't lock threads.";

		if (!$error) {
			$sql->modify('lock_thread', " UPDATE mangadex_threads SET thread_locked = 1 WHERE thread_id = ? LIMIT 1 ", [$thread_id]);
			$memcached->delete("thread_$thread_id");
		}
		else {
			$details = $error;
			print display_alert("danger", "Failed", $error);
		}

		$result = ($details) ? 0 : 1;
		break;

	case "unlock_thread":
		$thread_id = prepare_numeric($_GET["id"]);

		if (!validate_level($user, 'pr'))
			$error = "You can't unlock threads.";

		if (!$error) {
			$sql->modify('unlock_thread', " UPDATE mangadex_threads SET thread_locked = 0 WHERE thread_id = ? LIMIT 1 ", [$thread_id]);
			$memcached->delete("thread_$thread_id");
		}
		else {
			$details = $error;
			print display_alert("danger", "Failed", $error);
		}

		$result = ($details) ? 0 : 1;
		break;

	case "sticky_thread":
		$thread_id = prepare_numeric($_GET["id"]);

		if (!validate_level($user, 'pr'))
			$error = "You can't sticky threads.";

		if (!$error) {
			$sql->modify('sticky_thread', " UPDATE mangadex_threads SET thread_sticky = 1 WHERE thread_id = ? LIMIT 1 ", [$thread_id]);
			$memcached->delete("thread_$thread_id");
		}
		else {
			$details = $error;
			print display_alert("danger", "Failed", $error);
		}

		$result = ($details) ? 0 : 1;
		break;

	case "unsticky_thread":
		$thread_id = prepare_numeric($_GET["id"]);

		if (!validate_level($user, 'pr'))
			$error = "You can't unsticky threads.";

		if (!$error) {
			$sql->modify('unsticky_thread', " UPDATE mangadex_threads SET thread_sticky = 0 WHERE thread_id = ? LIMIT 1 ", [$thread_id]);
			$memcached->delete("thread_$thread_id");
		}
		else {
			$details = $error;
			print display_alert("danger", "Failed", $error);
		}

		$result = ($details) ? 0 : 1;
		break;

	case "edit_thread":
		$thread_id = prepare_numeric($_GET["id"]);
		$thread_name = htmlentities($_POST["thread_name"]);

		if (!validate_level($user, 'pr'))
			$error = "You can't edit threads.";

		if (!$error) {
			$sql->modify('edit_thread', " UPDATE mangadex_threads SET thread_name = ? WHERE thread_id = ? LIMIT 1 ", [$thread_name, $thread_id]);
			$memcached->delete("thread_$thread_id");
			$details = $thread_id;
		}
		else {
			$details = $error;
			print display_alert("danger", "Failed", $error);
		}

		$result = ($details) ? 0 : 1;
		break;

	case "vote":
		$thread_id = prepare_numeric($_GET["id"]);
		$poll_item_id = prepare_numeric($_POST["poll_item_id"]);

		$sql->modify('vote', " INSERT IGNORE INTO mangadex_forum_poll_votes (thread_id, user_id, item_id) VALUES (?, ?, ?) ", [$thread_id, $user->user_id, $poll_item_id]); //insert or update vote

		$sql->modify('vote', " 
			UPDATE mangadex_forum_poll_items 
			SET vote_count = (SELECT count(*) FROM mangadex_forum_poll_votes WHERE item_id = ? AND thread_id = ?) 
			WHERE item_id = ? AND thread_id = ? LIMIT 1 
			", [$poll_item_id, $thread_id, $poll_item_id, $thread_id]); //increase new vote counter

		$memcached->delete("thread_{$thread_id}_user_{$user->user_id}_vote");
		$memcached->delete("thread_{$thread_id}_poll_total_votes");
		$memcached->delete("thread_{$thread_id}_poll_items");

		$details = $thread_id;

		break;

	case "post_reply":
		$thread_id = prepare_numeric($_GET["id"]);
		$post_text = htmlentities($_POST["text"]);

		$thread = $sql->prep('post_reply', ' SELECT thread_id, thread_locked, forum_id FROM mangadex_threads WHERE thread_id = ? LIMIT 1 ', [$thread_id], 'fetch', PDO::FETCH_OBJ, -1);

        if (!validate_level($user, 'mod') && $user->has_active_restriction(USER_RESTRICTION_POST_COMMENT)) {
            $details = $user->get_restriction_message(USER_RESTRICTION_POST_COMMENT) ?? "Post creation failed!";
            $error = $details;
        }
        else if(!$post_text)
			$error = "Your post is empty.";
		elseif (!$user->user_id)
			$error = "Your session has timed out. Please log in again.";
		elseif (!$thread->thread_id)
			$error = "The thread does not exist.";
		elseif ($thread->thread_locked && !validate_level($user, 'pr'))
			$error = "The thread is locked.";

		if (!$user->premium && !validate_level($user, 'pr') && !$error) {
            $MIN_FIRST_POST_SECONDS = 86400;
            $MIN_POST_INTERVAL_SECONDS = 60*2;
            if ($user->joined_timestamp + $MIN_FIRST_POST_SECONDS > time()) {
                $error = "You have to wait until you can make your first post.";
            } else {
                $last_timestamp = $sql->prep('post_reply', "
                    SELECT timestamp
                    FROM mangadex_forum_posts
                    WHERE user_id = ?
                    ORDER BY timestamp DESC
                    LIMIT 1
                ", [$user->user_id], 'fetch', PDO::FETCH_COLUMN, -1);
                if ($last_timestamp + $MIN_POST_INTERVAL_SECONDS > time()) {
                    $error = "You have to wait until you can make your next post.";
                }
            }
        }

        if (!$error) {
			$post_id = $sql->modify('post_reply', " INSERT INTO mangadex_forum_posts (post_id, thread_id, user_id, timestamp, edit_timestamp, edit_user_id, text, deleted) VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), 0, 0, ?, 0) ", [$thread_id, $user->user_id, $post_text]);

			$sql->modify('post_reply', " UPDATE mangadex_threads SET thread_posts = thread_posts + 1, last_post_user_id = ?, last_post_timestamp = UNIX_TIMESTAMP(), last_post_id = ? WHERE thread_id = ? LIMIT 1 ", [$user->user_id, $post_id, $thread_id]);

			$sql->modify('post_reply', " 
				UPDATE mangadex_forums 
				SET last_thread_id = ?, 
					count_threads = (SELECT count(*) FROM mangadex_threads WHERE forum_id = ?), 
					count_posts = (SELECT SUM(thread_posts) FROM mangadex_threads WHERE forum_id = ?) 
				WHERE forum_id = ? LIMIT 1 ", [$thread_id, $thread->forum_id, $thread->forum_id, $thread->forum_id]);

			$callback = new Notify_Callback($post_id);
			$post_text = preg_replace_callback("/(@[a-zA-Z0-9_-]+)/", [$callback, 'notify'], $post_text);
			$sql->modify('post_reply', " UPDATE mangadex_forum_posts SET text = ? WHERE post_id = ? ", [$post_text, $post_id]);

			switch ($thread->forum_id) {
				case 11:
					$manga_id = $sql->prep('post_reply', ' SELECT manga_id FROM mangadex_mangas WHERE thread_id = ? LIMIT 1 ', [$thread_id], 'fetchColumn', '', -1);
					$memcached->delete("manga_$manga_id");
					break;

				case 12:
					$chapter_id = $sql->prep('post_reply', ' SELECT chapter_id FROM mangadex_chapters WHERE thread_id = ? LIMIT 1 ', [$thread_id], 'fetchColumn', '', -1);
					$memcached->delete("chapter_$chapter_id");
					break;

				case 14:
					$group_id = $sql->prep('post_reply', ' SELECT group_id FROM mangadex_groups WHERE thread_id = ? LIMIT 1 ', [$thread_id], 'fetchColumn', '', -1);
					$memcached->delete("group_$group_id");
					break;

				 default:
					break;
			}



			$memcached->delete("thread_$thread_id");

			$details = $thread_id;
		}
		else {
			$details = $error;
			print display_alert("danger", "Failed", $error);
		}

		$result = ($details) ? 0 : 1;
		break;

	case "post_edit":
		$post_id = prepare_numeric($_GET["id"]);
		$post_text = htmlentities($_POST["text"]);

		$previous_post = $sql->prep('post_edit', ' SELECT * FROM mangadex_forum_posts WHERE post_id = ? LIMIT 1 ', [$post_id], 'fetch', PDO::FETCH_OBJ, -1);

        if (!validate_level($user, 'mod') && $user->has_active_restriction(USER_RESTRICTION_POST_COMMENT)) {
            $details = $user->get_restriction_message(USER_RESTRICTION_POST_COMMENT) ?? "Thread edit failed!";
            $error .= display_alert("danger", "Failed", $details);
        }
        elseif (!$post_text)
			$error .= display_alert("danger", "Failed", "Your post is empty.");
		elseif (!$user->user_id)
			$error .= display_alert("danger", "Failed", "Your session has timed out. Please log in again."); //success
		elseif (!(validate_level($user, 'pr') || $user->user_id == $previous_post->user_id))
			$error .= display_alert("danger", "Failed", "You can't edit this post.");

		if (!$error) {
		    // Store previous version in database
            $sql->modify(
                'post_edit',
                'INSERT INTO mangadex_forum_posts_history (post_id, user_id, timestamp, text) VALUES (?, ?, ?, ?)',
                [
                    $previous_post->post_id,
                    $previous_post->edit_user_id !== 0 ? $previous_post->edit_user_id : $previous_post->user_id,
                    $previous_post->edit_timestamp !== 0 ? $previous_post->edit_timestamp : $previous_post->timestamp,
                    $previous_post->text
                ]
            );

            // Update actual comment row
			$callback = new Notify_Callback($post_id);
			$post_text = preg_replace_callback("/(@[a-zA-Z0-9_-]+)/", [$callback, 'notify'], $post_text);
			$sql->modify('post_edit', " UPDATE mangadex_forum_posts SET text = ?, edit_timestamp = UNIX_TIMESTAMP(), edit_user_id = ? WHERE post_id = ? ", [$post_text, $user->user_id, $post_id]);

			$details = $post_id;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = ($details) ? 0 : 1;
		break;

	case "post_delete":
		$post_id = prepare_numeric($_GET["id"]);

		$post = $sql->prep('post_delete', ' 
			SELECT posts.user_id, posts.thread_id, threads.forum_id
			FROM mangadex_forum_posts AS posts
			LEFT JOIN mangadex_threads AS threads
				ON threads.thread_id = posts.thread_id
			WHERE posts.post_id = ? 
			LIMIT 1 
			', [$post_id], 'fetch', PDO::FETCH_OBJ, -1);

		if ($post->user_id) {
			if (validate_level($user, 'pr')) {
				$sql->modify('post_delete', " UPDATE mangadex_forum_posts SET deleted = 1 WHERE post_id = ? LIMIT 1 ", [$post_id]);

				$sql->modify('post_delete', " UPDATE mangadex_threads SET thread_posts = thread_posts - 1 WHERE thread_id = ? LIMIT 1 ", [$post->thread_id]);

				switch ($post->forum_id) {
					case 11:
						$manga_id = $sql->prep('post_delete', ' SELECT manga_id FROM mangadex_mangas WHERE thread_id = ? LIMIT 1 ', [$post->thread_id], 'fetchColumn', '', -1);
						$memcached->delete("manga_$manga_id");
						break;

					case 12:

						break;

					case 14:
						$group_id = $sql->prep('post_delete', ' SELECT group_id FROM mangadex_groups WHERE thread_id = ? LIMIT 1 ', [$post->thread_id], 'fetchColumn', '', -1);
						$memcached->delete("group_$group_id");
						break;

					 default:
						break;
				}

				$details = $post->thread_id;
				print display_alert("success", "Success", "Post #$post_id has been deleted."); // success
			}
			else {
				$details = "You can't delete Post #$post_id.";
				print display_alert("danger", "Failed", $details); // fail
			}
		}
		else {
			$details = "Post #$post_id does not exist.";
			print display_alert("danger", "Failed", $details); // fail
		}

		$result = (!is_numeric($details)) ? 0 : 1;
		break;

    case "post_history":
        $post_id = prepare_numeric($_GET["id"]);

        // Check if PR+
        if (!validate_level($user, 'pr')) {
            print display_alert("danger", "Failed", "You can't view this post history.");
            return;
        }

        // Retrieve history
        $post_history = $sql->prep(
            'post_edit',
            'SELECT
              history.*, users.username, levels.level_colour AS editor_level_colour
              FROM mangadex_forum_posts_history AS history
              LEFT JOIN mangadex_users AS users ON history.user_id = users.user_id
              LEFT JOIN mangadex_user_levels AS levels ON users.level_id = levels.level_id
              WHERE history.post_id = ? ORDER BY history.timestamp DESC',
            [$post_id],
            'fetchAll',
            PDO::FETCH_OBJ,
            -1
        );

        // Show message if no edits stored
        if (count($post_history) === 0) {
            print display_alert("info", "No History", "There are no previous versions of this post available.");
            return;
        }

        // Build
        print parse_template(
            'forum/post_history',
            [
                'user' => $user,
                'posts' => $post_history,
                'parser' => $parser,
            ]
        );
        break;

    case "post_moderate":
        $post_id = prepare_numeric($_GET["id"]);
        $set_moderate = (bool)prepare_numeric($_GET["value"]) ? 1 : 0;

        if (!validate_level($user, 'pr')) {
            $error .= display_alert("danger", "Failed", "Unauthorized action.");
        }
        elseif (!$user->user_id) {
            $error .= display_alert("danger", "Failed", "Your session has timed out. Please log in again."); //success
        }
        $post = $sql->prep('post_moderate', ' SELECT post.*,
                    (SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts 
					WHERE mangadex_forum_posts.post_id <= post.post_id 
					AND mangadex_forum_posts.thread_id = post.thread_id
					AND mangadex_forum_posts.deleted = 0) AS thread_page
                    FROM mangadex_forum_posts AS post WHERE post.post_id = ? LIMIT 1 ', [$post_id], 'fetch', PDO::FETCH_OBJ, -1);
        if (!$post) {
            $error .= display_alert("danger", "Failed", "Post not found.");
        }

        if (!$error) {
            $sql->modify('post_moderate', " UPDATE mangadex_forum_posts SET moderated = ? WHERE post_id = ? ", [$set_moderate, $post_id]);

            if ($set_moderate) {
                // send the moderated user an automated message

                $url = URL . "thread/$post->thread_id/$post->thread_page/#post_$post->post_id";
                $subject = "[Warning] Your comment has been marked as moderated";
                $message = "Hello,\n\n" .
                    "One of your comments in [url=$url]this thread[/url] has been marked as moderated because it has been found to break a rule. This means it is hidden from all users who have not enabled displaying moderated comments.\n\n" .
                    "This moderated comment will not necessarily lead to further disciplinary action but repeated offenses may do so.\n\n" .
                    "This is an automated message, do not reply. If you have questions about moderation, please contact a moderator on the staff.";

                $sender_id = 1; // Default MangaDex user; TODO: remove hardcoded value?
                $recipient_id = $post->user_id;

				$thread_id = $sql->modify('msg_send', ' INSERT INTO mangadex_pm_threads (thread_id, thread_subject, sender_id, recipient_id, thread_timestamp, sender_read, recipient_read, sender_deleted, recipient_deleted) 
				VALUES (NULL, ?, ?, ?, ?, 1, 0, 0, 0) ', [$subject, $sender_id, $recipient_id, $timestamp]);

                $sql->modify('msg_send', ' INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text) 
				VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$thread_id, $sender_id, $message]);

                $memcached->delete("user_{$recipient_id}_unread_msgs");
            }

            $details = $post_id;
        }
        else {
            $details = $error;
            print $error; //returns "" or a message
        }

        $result = ($details) ? 0 : 1;
        break;

    case "post_spoiler":
        $post_id = prepare_numeric($_GET["id"]);

        if (!validate_level($user, 'pr')) {
            $error .= display_alert("danger", "Failed", "Unauthorized action.");
        }
        elseif (!$user->user_id) {
            $error .= display_alert("danger", "Failed", "Your session has timed out. Please log in again."); //success
        }

        $post = $sql->prep('post_spoiler', ' SELECT post.*,
                    (SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts 
					WHERE mangadex_forum_posts.post_id <= post.post_id 
					AND mangadex_forum_posts.thread_id = post.thread_id
					AND mangadex_forum_posts.deleted = 0) AS thread_page
                    FROM mangadex_forum_posts AS post WHERE post.post_id = ? LIMIT 1 ', [$post_id], 'fetch', PDO::FETCH_OBJ, -1);
        if (!$post) {
            $error .= display_alert("danger", "Failed", "Post not found.");
        }

        if (!$error) {
            $updated_text = "[spoiler]$post->text[/spoiler]\n\n[code]Mod Note: Please use spoiler tags when talking about chapter-specific or future events.[/code]";

            //$sql->modify('post_spoiler', " UPDATE mangadex_forum_posts SET text = ? WHERE post_id = ? ", [$updated_text, $post_id]);

            $sql->modify(
                'post_spoiler',
                'INSERT INTO mangadex_forum_posts_history (post_id, user_id, timestamp, text) VALUES (?, ?, ?, ?)',
                [
                    $post->post_id,
                    $post->edit_user_id !== 0 ? $post->edit_user_id : $post->user_id,
                    $post->edit_timestamp !== 0 ? $post->edit_timestamp : $post->timestamp,
                    $post->text
                ]
            );
            $sql->modify('post_spoiler', " UPDATE mangadex_forum_posts SET text = ?, edit_timestamp = UNIX_TIMESTAMP(), edit_user_id = ? WHERE post_id = ? ", [$updated_text, $user->user_id, $post_id]);

            // send the user whose post was spoilered an automated message

            $url = URL . "thread/$post->thread_id/$post->thread_page/#post_$post->post_id";
            $subject = "[Warning] Your comment has been marked as a spoiler";
            $message = "Hello,\n\n" .
                "One of your comments in [url=$url]this thread[/url] has been edited because it has been found to be in violation of Rules 5.2.2 and/or 5.4.4.\n\n" .
                "This comment will not necessarily lead to further disciplinary action but repeated offenses may do so.\n\n" .
                "This is an automated message, do not reply. If you have questions about moderation, please contact a moderator on the staff.";

            $sender_id = 1; // Default MangaDex user; TODO: remove hardcoded value?
            $recipient_id = $post->user_id;

			$thread_id = $sql->modify('msg_send', ' INSERT INTO mangadex_pm_threads (thread_id, thread_subject, sender_id, recipient_id, thread_timestamp, sender_read, recipient_read, sender_deleted, recipient_deleted) 
            VALUES (NULL, ?, ?, ?, ?, 1, 0, 0, 0) ', [$subject, $sender_id, $recipient_id, $timestamp]);

            $sql->modify('msg_send', ' INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text) 
            VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$thread_id, $sender_id, $message]);

            $memcached->delete("user_{$recipient_id}_unread_msgs");

            $details = $post_id;
        }
        else {
            $details = $error;
            print $error; //returns "" or a message
        }

        $result = ($details) ? 0 : 1;
        break;
}