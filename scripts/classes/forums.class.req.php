<?php
class Forums { 
	public function __construct($parent_forum_id = 1) {
		global $sql;
		$this->sql = $sql;
		$this->parent_forum_id = prepare_numeric($parent_forum_id);
		
		$this->num_rows = $sql->prep("forums_{$parent_forum_id}_num_rows", 'SELECT count(*) FROM mangadex_forums WHERE mangadex_forums.forum_parent = ?', [$parent_forum_id], 'fetchColumn', '');
	}
	
	public function query_read() {
		$results = $this->sql->prep("forums_$this->parent_forum_id", "
			SELECT forums.*, 
				CASE forums.forum_id 
					WHEN 11 THEN (SELECT manga_name FROM mangadex_threads LEFT JOIN mangadex_mangas ON mangadex_mangas.manga_id = mangadex_threads.thread_name WHERE mangadex_threads.thread_id = forums.last_thread_id LIMIT 1)
					WHEN 12 THEN CONCAT('Chapter ', (SELECT chapter FROM mangadex_threads LEFT JOIN mangadex_chapters ON mangadex_chapters.chapter_id = mangadex_threads.thread_name WHERE mangadex_threads.thread_id = forums.last_thread_id LIMIT 1))
					WHEN 14 THEN (SELECT group_name FROM mangadex_threads LEFT JOIN mangadex_groups ON mangadex_groups.group_id = mangadex_threads.thread_name WHERE mangadex_threads.thread_id = forums.last_thread_id LIMIT 1)
					ELSE threads.thread_name
				END AS thread_name,
				threads.last_post_user_id, 
				threads.last_post_timestamp, 
				threads.last_post_id, 
				(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts 
					WHERE mangadex_forum_posts.post_id <= threads.last_post_id 
					AND mangadex_forum_posts.thread_id = forums.last_thread_id
					AND mangadex_forum_posts.deleted = 0) AS thread_page,
				users.username, 
				options.show_premium_badge,
				options.show_md_at_home_badge,
				levels.level_colour,
				forums.count_threads + COALESCE((SELECT SUM(count_threads) FROM mangadex_forums WHERE mangadex_forums.forum_parent = forums.forum_id), 0) AS total_threads,
				forums.count_posts + COALESCE((SELECT SUM(count_posts) FROM mangadex_forums WHERE mangadex_forums.forum_parent = forums.forum_id), 0) AS total_posts,
				(SELECT GROUP_CONCAT(forum_name) FROM mangadex_forums WHERE mangadex_forums.forum_parent = forums.forum_id) AS subforum_names,
				(SELECT GROUP_CONCAT(forum_id) FROM mangadex_forums WHERE mangadex_forums.forum_parent = forums.forum_id) AS subforum_ids
			FROM mangadex_forums AS forums
			LEFT JOIN mangadex_threads AS threads 
				ON forums.last_thread_id = threads.thread_id 
			LEFT JOIN mangadex_users AS users
				ON threads.last_post_user_id = users.user_id
			LEFT JOIN mangadex_user_options AS options
				ON threads.last_post_user_id = options.user_id
			LEFT JOIN mangadex_user_levels AS levels
				ON users.level_id = levels.level_id 
			WHERE forums.forum_parent = ? 
			ORDER BY forums.sort ASC 
			", [$this->parent_forum_id], 'fetchAll', PDO::FETCH_UNIQUE, 60);
		
		return get_results_as_object($results, 'forum_id');
	}
}

class Forum_Threads { 
	public function __construct($forum_id = 1) {
		global $sql;
		$this->sql = $sql;
		$this->forum_id = prepare_numeric($forum_id);
		
		$row = $sql->prep("forum_$forum_id", '
			SELECT mangadex_forums.*,
				(SELECT count(*) FROM mangadex_threads WHERE thread_deleted = 0 AND forum_id = ?) AS num_rows
			FROM mangadex_forums 
			WHERE forum_id = ? 
			LIMIT 1
			', [$forum_id, $forum_id], 'fetch', PDO::FETCH_OBJ);
		
		foreach ($row as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function query_read($limit, $current_page) {
		$limit = prepare_numeric($limit);
		$offset = prepare_numeric($limit * ($current_page - 1));
		
		switch ($this->forum_id) {
			case 11: //manga
				$results = $this->sql->prep("forum_threads_{$this->forum_id}_offset_$offset", "
					SELECT threads.*,
						mangas.manga_name,
						mangas.manga_id,
						started.username AS started_username, 
						last.username AS last_username, 
						started_options.show_premium_badge AS started_show_premium_badge, 
						started_options.show_md_at_home_badge AS started_show_md_at_home_badge, 
						last_options.show_premium_badge AS last_show_premium_badge,
						last_options.show_md_at_home_badge AS last_show_md_at_home_badge,
						started_levels.level_colour AS started_level_colour, 
						last_levels.level_colour AS last_level_colour,
						(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts 
							WHERE mangadex_forum_posts.post_id <= threads.last_post_id 
							AND mangadex_forum_posts.thread_id = threads.thread_id
							AND mangadex_forum_posts.deleted = 0) AS thread_page
					FROM mangadex_threads AS threads 
					LEFT JOIN mangadex_mangas AS mangas
						ON mangas.manga_id = threads.thread_name
					LEFT JOIN mangadex_users AS started 
						ON threads.user_id = started.user_id
					LEFT JOIN mangadex_user_options AS started_options
						ON threads.user_id = started_options.user_id
					LEFT JOIN mangadex_user_levels AS started_levels 
						ON started.level_id = started_levels.level_id
					LEFT JOIN mangadex_users AS last 
						ON threads.last_post_user_id = last.user_id
					LEFT JOIN mangadex_user_options AS last_options
						ON threads.last_post_user_id = last_options.user_id
					LEFT JOIN mangadex_user_levels AS last_levels 
						ON last.level_id = last_levels.level_id
					WHERE threads.thread_deleted = 0 AND threads.forum_id = ? 
					ORDER BY threads.thread_sticky DESC, threads.last_post_timestamp DESC 
					LIMIT $limit OFFSET $offset
					", [$this->forum_id], 'fetchAll', PDO::FETCH_UNIQUE, -1);
				break;
				
			case 12: //chapters
				$results = $this->sql->prep("forum_threads_{$this->forum_id}_offset_$offset", "
					SELECT threads.*,
						chapters.chapter_id,
						chapters.volume,
						chapters.chapter,
						chapters.title,
						mangas.manga_name,
						mangas.manga_id,
						started.username AS started_username, 
						last.username AS last_username, 
						started_options.show_premium_badge AS started_show_premium_badge, 
						started_options.show_md_at_home_badge AS started_show_md_at_home_badge, 
						last_options.show_premium_badge AS last_show_premium_badge,
						last_options.show_md_at_home_badge AS last_show_md_at_home_badge,
						started_levels.level_colour AS started_level_colour, 
						last_levels.level_colour AS last_level_colour,
						(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts 
							WHERE mangadex_forum_posts.post_id <= threads.last_post_id 
							AND mangadex_forum_posts.thread_id = threads.thread_id
							AND mangadex_forum_posts.deleted = 0) AS thread_page
					FROM mangadex_threads AS threads 
					LEFT JOIN mangadex_chapters AS chapters
						ON chapters.chapter_id = threads.thread_name
					LEFT JOIN mangadex_mangas AS mangas
						ON mangas.manga_id = chapters.manga_id
					LEFT JOIN mangadex_users AS started 
						ON threads.user_id = started.user_id
					LEFT JOIN mangadex_user_options AS started_options
						ON threads.user_id = started_options.user_id
					LEFT JOIN mangadex_user_levels AS started_levels 
						ON started.level_id = started_levels.level_id
					LEFT JOIN mangadex_users AS last 
						ON threads.last_post_user_id = last.user_id
					LEFT JOIN mangadex_user_options AS last_options
						ON threads.last_post_user_id = last_options.user_id
					LEFT JOIN mangadex_user_levels AS last_levels 
						ON last.level_id = last_levels.level_id
					WHERE threads.thread_deleted = 0 AND threads.forum_id = ? 
					ORDER BY threads.thread_sticky DESC, threads.last_post_timestamp DESC 
					LIMIT $limit OFFSET $offset
					", [$this->forum_id], 'fetchAll', PDO::FETCH_UNIQUE, -1);
				break;
				
			case 14: //manga
				$results = $this->sql->prep("forum_threads_{$this->forum_id}_offset_$offset", "
					SELECT threads.*,
						_groups.group_name,
						_groups.group_id,
						started.username AS started_username, 
						last.username AS last_username, 
						started_options.show_premium_badge AS started_show_premium_badge, 
						started_options.show_md_at_home_badge AS started_show_md_at_home_badge, 
						last_options.show_premium_badge AS last_show_premium_badge,
						last_options.show_md_at_home_badge AS last_show_md_at_home_badge,
						started_levels.level_colour AS started_level_colour, 
						last_levels.level_colour AS last_level_colour,
						(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts 
							WHERE mangadex_forum_posts.post_id <= threads.last_post_id 
							AND mangadex_forum_posts.thread_id = threads.thread_id
							AND mangadex_forum_posts.deleted = 0) AS thread_page
					FROM mangadex_threads AS threads 
					LEFT JOIN mangadex_groups AS _groups
						ON _groups.group_id = threads.thread_name
					LEFT JOIN mangadex_users AS started 
						ON threads.user_id = started.user_id
					LEFT JOIN mangadex_user_options AS started_options
						ON threads.user_id = started_options.user_id
					LEFT JOIN mangadex_user_levels AS started_levels 
						ON started.level_id = started_levels.level_id
					LEFT JOIN mangadex_users AS last 
						ON threads.last_post_user_id = last.user_id
					LEFT JOIN mangadex_user_options AS last_options
						ON threads.last_post_user_id = last_options.user_id
					LEFT JOIN mangadex_user_levels AS last_levels 
						ON last.level_id = last_levels.level_id
					WHERE threads.thread_deleted = 0 AND threads.forum_id = ? 
					ORDER BY threads.thread_sticky DESC, threads.last_post_timestamp DESC 
					LIMIT $limit OFFSET $offset
					", [$this->forum_id], 'fetchAll', PDO::FETCH_UNIQUE, -1);
				break;
						
			default: 
				$results = $this->sql->prep("forum_threads_{$this->forum_id}_offset_$offset", "
					SELECT threads.*, 
						started.username AS started_username, 
						last.username AS last_username, 
						started_options.show_premium_badge AS started_show_premium_badge, 
						started_options.show_md_at_home_badge AS started_show_md_at_home_badge, 
						last_options.show_premium_badge AS last_show_premium_badge,
						last_options.show_md_at_home_badge AS last_show_md_at_home_badge,
						started_levels.level_colour AS started_level_colour, 
						last_levels.level_colour AS last_level_colour,
						(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts 
							WHERE mangadex_forum_posts.post_id <= threads.last_post_id 
							AND mangadex_forum_posts.thread_id = threads.thread_id
							AND mangadex_forum_posts.deleted = 0) AS thread_page
					FROM mangadex_threads AS threads 
					LEFT JOIN mangadex_users AS started 
						ON threads.user_id = started.user_id
					LEFT JOIN mangadex_user_options AS started_options
						ON threads.user_id = started_options.user_id
					LEFT JOIN mangadex_user_levels AS started_levels 
						ON started.level_id = started_levels.level_id
					LEFT JOIN mangadex_users AS last 
						ON threads.last_post_user_id = last.user_id
					LEFT JOIN mangadex_user_options AS last_options
						ON threads.last_post_user_id = last_options.user_id
					LEFT JOIN mangadex_user_levels AS last_levels 
						ON last.level_id = last_levels.level_id
					WHERE threads.thread_deleted = 0 AND threads.forum_id = ? 
					ORDER BY threads.thread_sticky DESC, threads.last_post_timestamp DESC 
					LIMIT $limit OFFSET $offset
					", [$this->forum_id], 'fetchAll', PDO::FETCH_UNIQUE, -1);
				break;
		}
		
		return get_results_as_object($results, 'thread_id');
	}
	
	public function get_breadcrumb() {
		global $memcached; 
		
		$string = "<nav aria-label='breadcrumb'>
			<ol class='breadcrumb'>
				<li class='breadcrumb-item'><a href='/forums'>Home</a></li>";
			
		$forum = $memcached->get("forum_$this->forum_id"); //cached from earlier
		
		while($forum->forum_parent) {
			$forum = $this->sql->prep("forum_$forum->forum_parent", '
				SELECT mangadex_forums.*,
					(SELECT count(*) FROM mangadex_threads WHERE thread_deleted = 0 AND forum_id = ?) AS num_rows
				FROM mangadex_forums 
				WHERE forum_id = ? 
				LIMIT 1
				', [$forum->forum_parent, $forum->forum_parent], 'fetch', PDO::FETCH_OBJ);
			$forum_array[$forum->forum_id] = $forum->forum_name;
		}

		if (isset($forum_array) && !empty($forum_array)) {
            $forum_array_rev = array_reverse($forum_array, true);
            foreach ($forum_array_rev as $key => $value) {
                $string .= "<li class='breadcrumb-item'><a href='/forum/$key'>$value</a></li>";
            }
        }

		$string .= "<li class='breadcrumb-item'><a href='/forum/$this->forum_id'>$this->forum_name</a></li>
				</ol>
			</nav>";
			
		return $string;
	}	
}



class Forum_Posts {
	public function __construct($thread_id) {
		global $sql;
		$this->sql = $sql;
		$this->thread_id = prepare_numeric($thread_id);
		
		$this->forum_id = $sql->prep("thread_{$thread_id}_forum_id", 'SELECT forum_id FROM mangadex_threads WHERE thread_id = ? AND thread_deleted = 0', [$thread_id], 'fetchColumn', '');
		
		if ($this->forum_id) {
			switch ($this->forum_id) {
				case 11: //manga
					$row = $this->sql->prep("thread_$this->thread_id", '
						SELECT threads.thread_locked, threads.thread_sticky,
							mangas.manga_id,
							mangas.manga_name AS thread_name,
							forums.view_level,
							(SELECT count(*) FROM mangadex_forum_posts WHERE thread_id = ? AND deleted = 0) AS num_rows
						FROM mangadex_threads AS threads
						LEFT JOIN mangadex_mangas AS mangas
							ON mangas.manga_id = threads.thread_name 
						LEFT JOIN mangadex_forums AS forums
							ON forums.forum_id = threads.forum_id
						WHERE threads.thread_id = ? 
						LIMIT 1 
						', [$this->thread_id, $this->thread_id], 'fetch', PDO::FETCH_OBJ, 60);
					break;
					
				case 12: //chapter
					$row = $this->sql->prep("thread_$this->thread_id", "
						SELECT threads.thread_locked, threads.thread_sticky,
							mangas.manga_id,
							chapters.chapter_id,
							CONCAT('Chapter ', chapters.chapter, ' (', mangas.manga_name, ')') AS thread_name,
							forums.view_level,
							(SELECT count(*) FROM mangadex_forum_posts WHERE thread_id = ? AND deleted = 0) AS num_rows
						FROM mangadex_threads AS threads
						LEFT JOIN mangadex_chapters AS chapters
							ON chapters.chapter_id = threads.thread_name
						LEFT JOIN mangadex_mangas AS mangas
							ON mangas.manga_id = chapters.manga_id
						LEFT JOIN mangadex_forums AS forums
							ON forums.forum_id = threads.forum_id
						WHERE threads.thread_id = ? 
						LIMIT 1 
						", [$this->thread_id, $this->thread_id], 'fetch', PDO::FETCH_OBJ, 60);
					break;
					
				case 14: //groups
					$row = $this->sql->prep("thread_$this->thread_id", "
						SELECT threads.thread_locked, threads.thread_sticky,
							_groups.group_id,
							_groups.group_name AS thread_name,
							forums.view_level,
							(SELECT count(*) FROM mangadex_forum_posts WHERE thread_id = ? AND deleted = 0) AS num_rows
						FROM mangadex_threads AS threads
						LEFT JOIN mangadex_groups AS _groups
							ON _groups.group_id = threads.thread_name
						LEFT JOIN mangadex_forums AS forums
							ON forums.forum_id = threads.forum_id
						WHERE threads.thread_id = ? 
						LIMIT 1 
						", [$this->thread_id, $this->thread_id], 'fetch', PDO::FETCH_OBJ, 60);
					break;
					
				default:
					$row = $this->sql->prep("thread_$this->thread_id", "
						SELECT threads.thread_locked, threads.thread_sticky, threads.thread_name, threads.user_id AS thread_author_id, threads.poll_expire_timestamp,
							forums.view_level,
							(SELECT count(*) FROM mangadex_forum_posts WHERE thread_id = ? AND deleted = 0) AS num_rows
						FROM mangadex_threads AS threads
						LEFT JOIN mangadex_forums AS forums
							ON forums.forum_id = threads.forum_id
						WHERE threads.thread_id = ? 
						LIMIT 1 
						", [$this->thread_id, $this->thread_id], 'fetch', PDO::FETCH_OBJ);
					break;
			}
			
			foreach ($row as $key => $value) {
				$this->$key = $value;
			}
		}
	}
	
	public function query_read($limit, $current_page) {
		$limit = prepare_numeric($limit);
		$offset = prepare_numeric($limit * ($current_page - 1));
		
		$results = $this->sql->prep("forum_posts_{$this->thread_id}_offset_$offset", "
			SELECT posts.*, 
				users.username, 
				users.avatar, 
				options.show_premium_badge,
				options.show_md_at_home_badge,
				levels.level_id,
				levels.level_colour,
				levels.level_name, 
				$current_page AS thread_page,
				editor.username AS editor_username,
				editor_levels.level_colour AS editor_level_colour
			FROM mangadex_forum_posts AS posts
			LEFT JOIN mangadex_users AS users
				ON posts.user_id = users.user_id 
			LEFT JOIN mangadex_user_options AS options
				ON posts.user_id = options.user_id 
			LEFT JOIN mangadex_user_levels AS levels
				ON users.level_id = levels.level_id 
			LEFT JOIN mangadex_users AS editor 
				ON posts.edit_user_id = editor.user_id
			LEFT JOIN mangadex_user_levels AS editor_levels 
				ON editor.level_id = editor_levels.level_id
			WHERE posts.thread_id = ? AND posts.deleted = 0
			ORDER BY posts.timestamp ASC 
			LIMIT $limit OFFSET $offset 
			", [$this->thread_id], 'fetchAll', PDO::FETCH_UNIQUE, -1);
		
		return get_results_as_object($results, 'post_id');
	}
	
	public function get_poll_items() {
		return $this->sql->prep("thread_{$this->thread_id}_poll_items", " 
			SELECT * FROM mangadex_forum_poll_items WHERE thread_id = ? 
			", [$this->thread_id], 'fetchAll', PDO::FETCH_UNIQUE);
	}
	
	public function get_poll_total_votes() {
		return $this->sql->prep("thread_{$this->thread_id}_poll_total_votes", " 
			SELECT count(*) FROM mangadex_forum_poll_votes WHERE thread_id = ? 
			", [$this->thread_id], 'fetchColumn', '');
	}
	
	public function get_user_vote($user_id) {
		return $this->sql->prep("thread_{$this->thread_id}_user_{$user_id}_vote", " 
			SELECT item_id FROM mangadex_forum_poll_votes WHERE thread_id = ? AND user_id = ? LIMIT 1 
			", [$this->thread_id, $user_id], 'fetchColumn', '');
	}
	
	public function get_breadcrumb() {
		global $memcached; 
		
		$string = "<nav aria-label='breadcrumb'>
			<ol class='breadcrumb'>
				<li class='breadcrumb-item'><a href='/forums'>Home</a></li>";
		
		$forum = $memcached->get("forum_$this->forum_id"); //cached from earlier
		if ($forum) {
            while($forum->forum_parent) {
                $forum = $this->sql->prep("forum_$forum->forum_parent", '
				SELECT mangadex_forums.*,
					(SELECT count(*) FROM mangadex_threads WHERE thread_deleted = 0 AND forum_id = ?) AS num_rows
				FROM mangadex_forums 
				WHERE forum_id = ? 
				LIMIT 1
				', [$forum->forum_parent, $forum->forum_parent], 'fetch', PDO::FETCH_OBJ);
                $forum_array[$forum->forum_id] = $forum->forum_name;
            }
        }

		if (isset($forum_array)) {
            $forum_array_rev = array_reverse($forum_array, true);
            foreach ($forum_array_rev as $key => $value) {
                $string .= "<li class='breadcrumb-item'><a href='/forum/$key'>$value</a></li>";
            }
        }

		$forum = $memcached->get("forum_$this->forum_id"); //cached from earlier
        if ($forum) {
            $string .= "<li class='breadcrumb-item'><a href='/forum/$forum->forum_id'>$forum->forum_name</a></li>
				<li class='breadcrumb-item'><a href='/thread/$this->thread_id'>" . thread_label($this->thread_name) . "</a></li>";
        }
        $string .= "</ol></nav>";

		return $string;
	}
}
?>