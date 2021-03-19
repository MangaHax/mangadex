<?php
class Users {
	public function __construct($search) {
		global $sql;

		$this->sql = $sql;
		$search_string = "";
		$pdo_bind = [];

		foreach ($search as $key => $value) {
			switch ($key) {
				case "username":
					$search_string .= "users.username LIKE ? AND ";
					$pdo_bind[] = "%$value%";
					break;

				case "email":
					$search_string .= "users.email LIKE ? AND ";
					$pdo_bind[] = "%$value%";
					break;

				case "is_staff":
					$search_string .= "users.level_id >= 10 AND ";
					break;

				default:
					$field = prepare_identifier("users.$key");
					$search_string .= "$field = ? AND ";
					$pdo_bind[] = $value;
					break;
			}
		}

		$this->num_rows = $sql->prep("users_query_" . hash_array($pdo_bind) . "_num_rows", "SELECT count(*) FROM mangadex_users AS users WHERE $search_string users.user_id > 1", $pdo_bind, 'fetchColumn', '', 60);
		$this->search_string = $search_string;
		$this->pdo_bind = $pdo_bind;
	}

	public function query_read($order, $limit, $current_page) {
		$orderby = prepare_orderby($order, SORT_ARRAY_USERS);
		$limit = prepare_numeric($limit);
		$offset = prepare_numeric($limit * ($current_page - 1));

		$results = $this->sql->prep("users_query_" . hash_array($this->pdo_bind) . "_orderby_{$orderby}_limit_{$limit}_offset_$offset", "
			SELECT users.*, lang.lang_name, lang.lang_flag, levels.level_colour, levels.level_name, options.*
			FROM mangadex_users AS users
			LEFT JOIN mangadex_user_options AS options
				ON users.user_id = options.user_id
			LEFT JOIN mangadex_languages AS lang
				ON users.language = lang.lang_id
			LEFT JOIN mangadex_user_levels AS levels
				ON users.level_id = levels.level_id
			WHERE $this->search_string users.user_id > 1
			ORDER BY $orderby
			LIMIT $limit OFFSET $offset
			", $this->pdo_bind, 'fetchAll', PDO::FETCH_UNIQUE, 60);

		return get_results_as_object($results, 'user_id');
	}
}

class User {
	public function __construct($id, $type) {
		global $sql;
		$this->sql = $sql;
		$type = prepare_identifier($type);

		$row = $sql->prep("user_$id", "
			SELECT users.*, levels.level_colour, levels.level_name, options.*, lang.lang_name, lang.lang_flag
			FROM mangadex_users AS users
			LEFT JOIN mangadex_languages AS lang
				ON users.language = lang.lang_id
			LEFT JOIN mangadex_user_levels AS levels
				ON users.level_id = levels.level_id
			LEFT JOIN mangadex_user_options AS options
				ON users.user_id = options.user_id
			WHERE users.$type = ?
			LIMIT 1;
			", [$id], 'fetch', PDO::FETCH_OBJ);

		//does user exist
		$this->exists = $row;

		if (!$this->exists)
			$row = $sql->query_read('user_0', '
				SELECT users.*, levels.level_colour, levels.level_name, options.*
				FROM mangadex_users AS users
				LEFT JOIN mangadex_user_levels AS levels
					ON users.level_id = levels.level_id
				LEFT JOIN mangadex_user_options AS options
					ON users.user_id = options.user_id
				WHERE users.user_id = 0
				LIMIT 1;
				', 'fetch', PDO::FETCH_OBJ);

		//copy $row into $this
		if ($row) {
			foreach ($row as $key => $value) {
				$this->$key = $value;
			}
			$this->user_slug = strtolower($this->username);

			$this->logo = (!$this->avatar) ? "default2.png?v=4" : "$this->user_id.$this->avatar?" . @filemtime(ABS_DATA_BASEPATH . "/avatars/$this->user_id.$this->avatar");

			$this->show_premium_badge = $this->show_premium_badge ?? 0;
			$this->data_saver = $this->data_saver ?? 0;

		}
	}

	public function get_comments($show, $page = 1) {
        $moderatedFilter = '%';
        if ($show === 'moderated') {
            $moderatedFilter = 1;
        } elseif ($show === 'unmoderated') {
            $moderatedFilter = 0;
        }

		$results = $this->sql->prep("user_{$this->user_id}_get_comments_{$page}_{$moderatedFilter}", '
			SELECT posts.*, users.username, users.avatar, user_levels.level_colour,
				editor.username AS editor_username,
				editor_levels.level_colour AS editor_level_colour,
				(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts
					WHERE mangadex_forum_posts.post_id <= posts.post_id
					AND mangadex_forum_posts.thread_id = posts.thread_id
					AND mangadex_forum_posts.deleted = 0) AS thread_page
			FROM mangadex_forum_posts AS posts
			LEFT JOIN mangadex_users AS users
				ON posts.user_id = users.user_id
			LEFT JOIN mangadex_user_levels AS user_levels
				ON users.level_id = user_levels.level_id
			LEFT JOIN mangadex_users AS editor
				ON posts.edit_user_id = editor.user_id
			LEFT JOIN mangadex_user_levels AS editor_levels
				ON editor.level_id = editor_levels.level_id
			WHERE posts.user_id = ? AND posts.deleted = 0 AND posts.moderated LIKE ?
			ORDER BY timestamp DESC
			LIMIT ?, 100
			',
            [
                $this->user_id,
                $moderatedFilter,
                ($page -1) * 100
            ], 'fetchAll', PDO::FETCH_UNIQUE, -1);

		return get_results_as_object($results, 'post_id');
	}

	public function get_comments_count($show) {
	    $moderatedFilter = '%';
	    if ($show === 'moderated') {
            $moderatedFilter = 1;
        } elseif ($show === 'unmoderated') {
            $moderatedFilter = 0;
        }

	    return $this->sql->prep(
	        "user_{$this->user_id}_get_comments_count_{$moderatedFilter}", '
                SELECT COUNT(*)
                FROM mangadex_forum_posts AS posts
                WHERE posts.user_id = ? AND posts.deleted = 0 AND posts.moderated LIKE ?',
            [
                $this->user_id,
                $moderatedFilter
            ],
            'fetch',
            PDO::FETCH_COLUMN,
            -1
	    );
    }

	public function update_total_chapters_uploaded() {
		$this->sql->modify('update_total_chapters_uploaded', '
			UPDATE mangadex_users
			SET user_uploads = (SELECT count(*) FROM mangadex_chapters WHERE user_id = ? AND chapter_deleted = 0)
			WHERE user_id = ? LIMIT 1
		', [$this->user_id, $this->user_id]);
	}

	public function get_unread_threads() {
		return $this->sql->prep("user_{$this->user_id}_unread_msgs", "
			SELECT count(*)
			FROM mangadex_pm_threads
			WHERE (sender_id = ? AND sender_read = 0) OR (recipient_id = ? AND recipient_read = 0)
			", [$this->user_id, $this->user_id], 'fetchColumn', '', 60);
	}

	public function get_unread_notifications() {
		return $this->sql->prep("user_{$this->user_id}_unread_notifications", "
			SELECT count(*)
			FROM mangadex_notifications
			WHERE mentionee_user_id = ? AND is_read = 0
			", [$this->user_id], 'fetchColumn', '', 60);
	}

	public function get_groups() {
		return $this->sql->prep("user_{$this->user_id}_groups", "
			SELECT mangadex_link_user_group.group_id, mangadex_groups.group_name
			FROM mangadex_groups
			LEFT JOIN mangadex_link_user_group ON mangadex_groups.group_id = mangadex_link_user_group.group_id
			WHERE mangadex_link_user_group.user_id = ?
			", [$this->user_id], 'fetchAll', PDO::FETCH_KEY_PAIR);
	}

	public function get_manga_ids() { //of the chapters which the user has uploaded
		return $this->sql->prep("user_{$this->user_id}_manga_ids", "
			SELECT manga_id
			FROM mangadex_chapters
			WHERE user_id = ? AND chapter_deleted = 0
			GROUP BY manga_id
			ORDER BY manga_id
			", [$this->user_id], 'fetchAll', PDO::FETCH_COLUMN, -1);
	}

	public function get_followed_group_ids() {
		return $this->sql->prep("user_{$this->user_id}_followed_group_ids", "
			SELECT group_id
			FROM mangadex_follow_user_group
			WHERE user_id = ?
			", [$this->user_id], 'fetchAll', PDO::FETCH_COLUMN, -1);
	}

	public function get_followed_manga_ids_key_pair() {
		return $this->sql->prep("user_{$this->user_id}_followed_manga_ids_key_pair", "
			SELECT manga_id, follow_type
			FROM mangadex_follow_user_manga
			WHERE user_id = ?
			", [$this->user_id], 'fetchAll', PDO::FETCH_KEY_PAIR);
	}

	public function get_followed_manga_ids() { //contains progress data
		return $this->sql->prep("user_{$this->user_id}_followed_manga_ids", "
			SELECT manga_id, follow_type, volume, chapter
			FROM mangadex_follow_user_manga
			WHERE user_id = ?
			", [$this->user_id], 'fetchAll', PDO::FETCH_UNIQUE);	//contains progress tracker (volume and chapter)
	}

	public function get_manga_userdata($manga_id) { //contains progress data, title, and rating
		$follows = $this->get_followed_manga_ids_api();
		foreach ($follows as $manga) {
			if ($manga['manga_id'] == $manga_id) {
				return $manga;
			}
		}
		return null;
    }

    public function get_followed_manga_ids_api() { //contains progress data, title, and rating for all followed manga
        return $this->sql->prep("user_{$this->user_id}_followed_manga_ids_api", "
			SELECT f.manga_id, m.manga_name AS title, m.manga_hentai, m.manga_image, f.follow_type, f.volume, f.chapter, COALESCE(r.rating, 0) as rating
			FROM mangadex_follow_user_manga f
			JOIN mangadex_mangas m
			ON m.manga_id = f.manga_id
			LEFT JOIN mangadex_manga_ratings r
			ON m.manga_id = r.manga_id AND r.user_id = ?
			WHERE f.user_id = ?
			", [$this->user_id, $this->user_id], 'fetchAll', PDO::FETCH_ASSOC);
    }

	public function get_read_chapters() {
		return $this->sql->prep("user_{$this->user_id}_read_chapters", "
			SELECT chapter_id
			FROM mangadex_chapter_views
			WHERE user_id = ?
			", [$this->user_id], 'fetchAll', PDO::FETCH_COLUMN);
	}

	public function get_reading_history($ignoreHentaiToggle = false) {
	    // Handle hentais
        $hentai_toggle = (int) max(0, min(2, $_COOKIE['mangadex_h_toggle'] ?? 0));
        if ($ignoreHentaiToggle) { $hentai_toggle = 1; }

        // Translate for sql query
        if ($hentai_toggle === 1) {
            $hentai_param = '_';
        } elseif ($hentai_toggle === 2) {
            $hentai_param = 1;
        } else {
            $hentai_param = 0;
        }

        // If $ignoreHentaiToggle is set, the request is not cached, since it will be read only once anyway
		return $this->sql->prep(
		    "user_{$this->user_id}_reading_history_ht{$hentai_toggle}" . ($ignoreHentaiToggle ? '_nocache' : ''),
            '
			SELECT history.chapter_id, history.timestamp, chapters.volume, chapters.chapter, chapters.title, chapters.manga_id, mangas.manga_name, mangas.manga_hentai, mangas.manga_image
			FROM mangadex_reading_history AS history
			LEFT JOIN mangadex_chapters AS chapters
				ON chapters.chapter_id = history.chapter_id
			LEFT JOIN mangadex_mangas AS mangas
				ON mangas.manga_id = chapters.manga_id
			WHERE history.user_id = ? AND mangas.manga_hentai LIKE ?
			ORDER BY history.timestamp DESC
			LIMIT ?',
            [
                $this->user_id,
                $hentai_param,
                $ignoreHentaiToggle ? 20 : 10
            ],
            'fetchAll',
            PDO::FETCH_ASSOC,
            $ignoreHentaiToggle ? -1 : 0
        );
	}

	/*public function get_read_chapters() {
		$string = $this->sql->prep("user_{$this->user_id}_read_chapters", "
			SELECT chapter_id
			FROM mangadex_chapter_views_v2
			WHERE user_id = ?
			LIMIT 1
			", [$this->user_id], 'fetchColumn', '', -1);

		$array = explode(',', $string);
		return $array;
	}*/

	public function get_manga_ratings() {
		return $this->sql->prep("user_{$this->user_id}_manga_ratings", "
			SELECT manga_id, rating
			FROM mangadex_manga_ratings
			WHERE user_id = ?
			", [$this->user_id], 'fetchAll', PDO::FETCH_KEY_PAIR);
	}

	public function get_friends_user_ids() {
		return $this->sql->prep("user_{$this->user_id}_friends_user_ids", "
			SELECT relations.target_user_id, relations.accepted, user.user_id, user.username, user.last_seen_timestamp, user.list_privacy, user_level.level_colour
			FROM mangadex_user_relations AS relations
			JOIN mangadex_users AS user
				ON relations.target_user_id = user.user_id
			LEFT JOIN mangadex_user_levels AS user_level
				ON user.level_id = user_level.level_id
			WHERE relations.relation_id = 1 AND relations.user_id = ?
			ORDER BY user.username ASC
			", [$this->user_id], 'fetchAll', PDO::FETCH_UNIQUE, 60*10);
	}

	public function get_pending_friends_user_ids() {
		return $this->sql->prep("user_{$this->user_id}_pending_friends_user_ids", "
			SELECT relations.user_id, user.user_id, user.username, user.last_seen_timestamp, user_level.level_colour
			FROM mangadex_user_relations AS relations
			JOIN mangadex_users AS user
				ON relations.user_id = user.user_id
			LEFT JOIN mangadex_user_levels AS user_level
				ON user.level_id = user_level.level_id
			WHERE relations.relation_id = 1 AND relations.accepted = 0 AND relations.target_user_id = ?
			ORDER BY user.username ASC
			", [$this->user_id], 'fetchAll', PDO::FETCH_UNIQUE, 60*60*24);
	}

	public function get_blocked_user_ids() {
		return $this->sql->prep("user_{$this->user_id}_blocked_user_ids", "
			SELECT relations.target_user_id, user.user_id, user.username, user_level.level_colour
			FROM mangadex_user_relations AS relations
			JOIN mangadex_users AS user
				ON relations.target_user_id = user.user_id
			LEFT JOIN mangadex_user_levels AS user_level
				ON user.level_id = user_level.level_id
			WHERE relations.relation_id = 0 AND relations.user_id = ? AND user.level_id < ?
			ORDER BY user.username ASC
			", [$this->user_id, 10 /** staff level: PR **/], 'fetchAll', PDO::FETCH_UNIQUE, 60*60*24);
	}

	public function get_active_restrictions() {
	    global $sql;

	    if (!isset($this->user_id) || $this->user_id < 1)
	        return []; // Probly a guest?

        if ($this->_active_restriction_cache !== null)
            return $this->_active_restriction_cache;

	    $res = array_reduce($sql->prep('user_restrictions_active_'.$this->user_id, '
  SELECT r.restriction_type_id, r.expiration_timestamp, t.name, t.error_msg
  FROM mangadex_user_restrictions r
  LEFT JOIN mangadex_restriction_types t
    ON t.restriction_type_id = r.restriction_type_id
  WHERE r.target_user_id = ? AND UNIX_TIMESTAMP() < r.expiration_timestamp
  ORDER BY r.expiration_timestamp ASC', [$this->user_id], 'fetchAll', PDO::FETCH_ASSOC, -1), function ($result, $item) {
            $result[$item['restriction_type_id']][] = $item;
            return $result;
        });

	    $this->_active_restriction_cache = $res;
        return $res;
    }
    private $_active_restriction_cache = null;

    public function has_active_restriction($type_id) {
	    $restrictions = $this->get_active_restrictions();
	    return (isset($restrictions[$type_id]) && !empty($restrictions[$type_id]));
    }

    public function get_restriction_message($type_id) {
        global $sql;

        if ($this->_restriction_type_cache === null)
        {
            $res = array_reduce($sql->prep('user_restriction_types', 'SELECT * FROM mangadex_restriction_types', [], 'fetchAll', PDO::FETCH_ASSOC, 3600),
            function ($result, $item) {
                $result[$item['restriction_type_id']] = ['name' => $item['name'], 'error_msg' => $item['error_msg']];
                return $result;
            });
            $this->_restriction_type_cache = $res;
        }

        return $this->_restriction_type_cache[$type_id]['error_msg'] ?? null;
    }
    private $_restriction_type_cache = null;

    public function get_sessions()
	{
		global $sql;

		$sessions = $sql->prep('user_sessions_'.$this->user_id, 'SELECT * FROM mangadex_sessions WHERE user_id = ?',
			[$this->user_id], 'fetchAll', PDO::FETCH_ASSOC, -1);

		return $sessions;
	}

	public function get_transactions() {
		return $this->sql->prep("user_{$this->user_id}_transactions", "
			SELECT *
			FROM mangadex_user_transactions
			WHERE user_id = ?
			ORDER BY date ASC
			", [$this->user_id], 'fetchAll', PDO::FETCH_ASSOC, -1);
	}

	public function get_btc_transactions() {
		return $this->sql->prep("user_{$this->user_id}_btc_transactions", "
			SELECT *
			FROM mangadex_transactions_btc
			WHERE hash in (SELECT paypal from mangadex_user_paypal where user_id = ?) OR
				sender_address in (SELECT paypal from mangadex_user_paypal where user_id = ?)
			ORDER BY id DESC
			", [$this->user_id, $this->user_id], 'fetchAll', PDO::FETCH_ASSOC, -1);
	}

	public function get_eth_transactions() {
		return $this->sql->prep("user_{$this->user_id}_eth_transactions", "
			SELECT *
			FROM mangadex_transactions_eth
			WHERE hash in (SELECT paypal from mangadex_user_paypal where user_id = ?) OR
				sender_address in (SELECT paypal from mangadex_user_paypal where user_id = ?)
			ORDER BY id DESC
			", [$this->user_id, $this->user_id], 'fetchAll', PDO::FETCH_ASSOC, -1);
	}

	public function get_paypal() {
		return $this->sql->prep("user_{$this->user_id}_paypal", "
			SELECT *
			FROM mangadex_user_paypal
			WHERE user_id = ?
			", [$this->user_id], 'fetchAll', PDO::FETCH_ASSOC, -1);
	}

	public function get_order() {
		return $this->sql->prep("user_{$this->user_id}_order", "
			SELECT *
			FROM mangadex_orders
			WHERE user_id = ?
			LIMIT 1
			", [$this->user_id], 'fetch', PDO::FETCH_ASSOC, -1);
	}

	public function get_chapters_read_count() {
		return $this->sql->prep("user_{$this->user_id}_chapters_read_count", "
			SELECT chapters_read
			FROM mangadex_user_stats
			WHERE user_id = ?
			LIMIT 1
			", [$this->user_id], 'fetchColumn', '', -1);
	}

	public function get_blocked_groups() {
		return $this->sql->prep("user_{$this->user_id}_blocked_groups", "
			SELECT block.group_id, groups.group_name
			FROM mangadex_user_block_group AS block
			LEFT JOIN mangadex_groups AS groups
				ON block.group_id = groups.group_id
			WHERE block.user_id = ?
			", [$this->user_id], 'fetchAll', PDO::FETCH_KEY_PAIR);
	}

	public function get_clients() {
		return $this->sql->prep("user_{$this->user_id}_clients", "
			SELECT *
			FROM mangadex_clients
			WHERE user_id = ?
			", [$this->user_id], 'fetchAll', PDO::FETCH_ASSOC, -1);
	}

	public function get_client_approval_time() {
		return $this->sql->prep("user_{$this->user_id}_client_approval_time", "
			SELECT timestamp
			FROM mangadex_clients
			WHERE user_id = ? AND approved = 1
			ORDER BY timestamp ASC LIMIT 1
			", [$this->user_id], 'fetchColumn', '', -1);
	}
}

class PM_Threads {
	public function __construct($user_id, $deleted) {
		global $sql;
		$this->sql = $sql;

		$this->num_rows = $sql->prep("user_{$user_id}_PMs_num_rows", "
			SELECT count(*)
			FROM mangadex_pm_threads
			WHERE (sender_id = ? AND sender_deleted = ?)
				OR (recipient_id = ? AND recipient_deleted = ?)
			", [$user_id, $deleted, $user_id, $deleted], 'fetchColumn', '', -1);

		$this->user_id = $user_id;
		$this->deleted = $deleted;
	}

	public function query_read($page = 1, $limit = 100)
	{
		$offset = ($page - 1) * $limit;
		$results = $this->sql->prep(
			"user_{$this->user_id}_PMs",
				"
			SELECT threads.*,
				sender.username AS sender_username,
				recipient.username AS recipient_username,
				sender_level.level_colour AS sender_level_colour,
				recipient_level.level_colour AS recipient_level_colour
			FROM mangadex_pm_threads AS threads
			LEFT JOIN mangadex_users AS sender
				ON threads.sender_id = sender.user_id
			LEFT JOIN mangadex_user_levels AS sender_level
				ON sender.level_id = sender_level.level_id
			LEFT JOIN mangadex_users AS recipient
				ON threads.recipient_id = recipient.user_id
			LEFT JOIN mangadex_user_levels AS recipient_level
				ON recipient.level_id = recipient_level.level_id
			WHERE (threads.sender_id = ? AND threads.sender_deleted = ?)
				OR (threads.recipient_id = ? AND threads.recipient_deleted = ?)
			ORDER BY threads.thread_timestamp DESC
			LIMIT ? OFFSET ?
			",
			[$this->user_id, $this->deleted, $this->user_id, $this->deleted, $limit, $offset],
			'fetchAll',
			PDO::FETCH_ASSOC,
			-1
		);

		//return get_results_as_object($results, 'thread_id');
        return $results;
	}
}

class PM_Thread {
	public function __construct($id) {
		global $sql;

		$row = $sql->prep("PM_$id", 'SELECT *, (SELECT COUNT(*) FROM mangadex_pm_msgs m WHERE m.thread_id = t.thread_id) AS total FROM mangadex_pm_threads t WHERE t.thread_id = ?', [$id], 'fetch', PDO::FETCH_OBJ);

		if (isset($row)) {
            //copy $row into $this
            foreach ($row as $key => $value) {
                $this->$key = $value;
            }
        }
	}
}


class PM_Msgs {
	public function __construct($id, int $offset = 0, int $limit = 25) {
		global $sql;

		$offset = (int)max(0, $offset);

		$results = $sql->prep("PM_msgs_$id", "
			SELECT msgs.*, users.username, users.avatar, levels.level_colour
			FROM mangadex_pm_msgs AS msgs
			LEFT JOIN mangadex_users AS users
				ON msgs.user_id = users.user_id
			LEFT JOIN mangadex_user_levels AS levels
				ON users.level_id = levels.level_id
			WHERE msgs.thread_id = ?
			ORDER BY msgs.timestamp DESC LIMIT ?, ?
			", [$id, $offset, $limit], 'fetchAll', PDO::FETCH_UNIQUE, -1);

		foreach ($results as $i => $row) {
			$this->{$i} = new \stdClass();
			foreach ($row as $key => $value) {
				$this->{$i}->$key = $value;
			}
			if (isset($this->{$i}->msg_id))
				$this->{$i}->post_id = $this->{$i}->msg_id; //compatibility with display_post function
			$this->{$i}->edit_timestamp = 0;
		}
	}
}

class Notifications {
	public function __construct($user_id) {
		global $sql;
		$this->sql = $sql;
		$this->user_id = $user_id;
	}

	public function query_read() {
		$results = $this->sql->prep("notifications_$this->user_id", "
			SELECT notifications.*,
				users.username, users.user_id,
				levels.level_colour,
				posts.thread_id,
				threads.thread_name,
				(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts
					WHERE mangadex_forum_posts.post_id <= notifications.post_id
					AND mangadex_forum_posts.thread_id = posts.thread_id
					AND mangadex_forum_posts.deleted = 0) AS thread_page
			FROM mangadex_notifications AS notifications
			LEFT JOIN mangadex_users AS users
				ON users.user_id = notifications.mentioner_user_id
			LEFT JOIN mangadex_user_levels AS levels
				ON levels.level_id = users.level_id
			LEFT JOIN mangadex_forum_posts AS posts
				ON posts.post_id = notifications.post_id
			LEFT JOIN mangadex_threads AS threads
				ON threads.thread_id = posts.thread_id
			WHERE mentionee_user_id = ?
			ORDER BY timestamp DESC
			LIMIT 20
			", [$this->user_id], 'fetchAll', PDO::FETCH_ASSOC);

		//return get_results_as_object($results, 'notification_id');
        return $results;
	}
}
?>
