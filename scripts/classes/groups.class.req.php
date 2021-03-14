<?php
class Groups {
	public function __construct($search = []) {
		global $sql;
		
		$this->sql = $sql;
		$search_string = '';
		$pdo_bind = [];
		
		foreach ($search as $key => $value) {
			switch ($key) {
				case 'group_name':
					$terms = explode(' ', $value);
					foreach ($terms as $term) {
						$search_string .= "(_groups.group_name LIKE ? OR _groups.group_alt_name LIKE ?) AND ";
						$pdo_bind[] = "%$term%";
						$pdo_bind[] = "%$term%";
					}
					break;

				case 'group_ids_array':
					$in = prepare_in($value);
					$search_string .= "_groups.group_id IN ($in) AND ";
					$pdo_bind = array_merge($pdo_bind, $value);
					break;
					
				default:
					$field = prepare_identifier("$key");
					$search_string .= "$field = ? AND ";
					$pdo_bind[] = $value;
					break;
			}
		}
		
		$this->num_rows = $sql->prep("groups_query_" . hash_array($pdo_bind) . "_num_rows", "SELECT count(*) FROM mangadex_groups AS _groups WHERE $search_string 1=1", $pdo_bind, 'fetchColumn', '', 60);
		$this->search_string = $search_string;
		$this->pdo_bind = $pdo_bind;
	}
	
	public function query_read($order, $limit, $current_page) {
		$orderby = prepare_orderby($order, SORT_ARRAY_GROUPS);
		$limit = prepare_numeric($limit);
		$offset = prepare_numeric($limit * ($current_page - 1));
		
		$results = $this->sql->prep("groups_query_" . hash_array($this->pdo_bind) . "_orderby_{$orderby}_offset_$offset", "
			SELECT _groups.*, 
				lang.lang_name, lang.lang_flag, 
				users.user_id, users.username, 
				levels.level_colour,
				thread_posts
			FROM mangadex_groups AS _groups
			LEFT JOIN mangadex_languages AS lang 
				ON _groups.group_lang_id = lang.lang_id
			LEFT JOIN mangadex_users AS users
				ON _groups.group_leader_id = users.user_id
			LEFT JOIN mangadex_user_levels AS levels
				ON users.level_id = levels.level_id
			LEFT JOIN mangadex_threads AS threads
				ON _groups.thread_id = threads.thread_id 
			
			WHERE $this->search_string 1=1 
			ORDER BY $orderby 
			LIMIT $limit OFFSET $offset
			", $this->pdo_bind, 'fetchAll', PDO::FETCH_UNIQUE, 60);
			
		return get_results_as_object($results, 'group_id');
	}
}

class Group { 
	public function __construct($id) {
		global $sql;
		
		$this->sql = $sql;
		$id = prepare_numeric($id);
		
		$row = $sql->prep("group_$id", "
			SELECT _groups.*, 
				lang.lang_name, lang.lang_flag, 
				users.username, users.user_id, 
				levels.level_colour, 
				threads.thread_posts,
				(SELECT count(*) FROM mangadex_chapters AS chapters 
					WHERE (chapters.group_id = ? OR chapters.group_id_2 = ? OR chapters.group_id_3 = ?) AND chapters.chapter_deleted = 0) AS count_chapters
			FROM mangadex_groups AS _groups
			LEFT JOIN mangadex_languages as lang
				ON _groups.group_lang_id = lang.lang_id 
			LEFT JOIN mangadex_users AS users
				ON _groups.group_leader_id = users.user_id 
			LEFT JOIN mangadex_user_levels AS levels
				ON users.level_id = levels.level_id 
			LEFT JOIN mangadex_threads AS threads
				ON _groups.thread_id = threads.thread_id 
			WHERE _groups.group_id = ? 
			", [$id, $id, $id, $id], 'fetch', PDO::FETCH_OBJ, 86400);
		
		//copy $row into $this
		if ($row) {
			foreach ($row as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	public function get_comments() {
		$results = $this->sql->prep("group_{$this->group_id}_get_comments", "
			SELECT posts.*, users.username, users.avatar, user_levels.level_colour, user_levels.level_id, user_levels.level_name,
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
			WHERE posts.thread_id = ? AND posts.deleted = 0 
			ORDER BY timestamp DESC 
			LIMIT 500
			", [$this->thread_id], 'fetchAll', PDO::FETCH_UNIQUE, -1);
		
		return get_results_as_object($results, 'post_id');
	}
	
	public function get_members_display() {
		return $this->sql->prep("group_{$this->group_id}_members_display", "
			SELECT users.username, levels.level_colour, link.user_id 
			FROM mangadex_link_user_group AS link 
			LEFT JOIN mangadex_users AS users
				ON users.user_id = link.user_id 
			LEFT JOIN mangadex_user_levels AS levels
				ON users.level_id = levels.level_id 
			WHERE link.group_id = ? 
				AND link.role = 2 
			", [$this->group_id], 'fetchAll', PDO::FETCH_ASSOC);
	}
	
	public function get_members() {
		$array = $this->sql->prep("group_{$this->group_id}_members", "
			SELECT link.user_id, users.username
			FROM mangadex_link_user_group AS link 
			LEFT JOIN mangadex_users AS users
				ON users.user_id = link.user_id 
			WHERE link.group_id = ? 
				AND link.role = 2 
			", [$this->group_id], 'fetchAll', PDO::FETCH_KEY_PAIR);
		
		natcasesort($array); //must be like this
		return $array;
	}
	
	public function get_manga_ids() {
		return $this->sql->prep("group_{$this->group_id}_get_manga_ids", "
			SELECT manga_id 
			FROM mangadex_chapters 
			WHERE (group_id = ? OR group_id_2 = ? OR group_id_3 = ?) AND chapter_deleted = 0 
			GROUP BY manga_id 
			ORDER BY manga_id
			", [$this->group_id, $this->group_id, $this->group_id], 'fetchAll', PDO::FETCH_COLUMN, -1);
	}	
	
	public function get_likes_user_id_ip_list() {
		$results = $this->sql->prep("group_{$this->group_id}_likes_user_id_ip_list", "
			SELECT user_id, ip 
			FROM mangadex_group_likes 
			WHERE group_id = ?
			", [$this->group_id], 'fetchAll', PDO::FETCH_ASSOC);
			
		$array['user_id'] = [];
		$array['ip'] = [];
		
		if ($results) {
			foreach ($results as $i => $row) {
				if ($row['user_id']) $array['user_id'][] = $row['user_id'];
				if ($row['ip']) $array['ip'][] = $row['ip'];
			}		
		}
		return $array; //array of members
	}
	
	public function get_follows_user_id() {
		return $this->sql->prep("group_{$this->group_id}_follows_user_id", "
			SELECT user_id 
			FROM mangadex_follow_user_group 
			WHERE group_id = ?
			", [$this->group_id], 'fetchAll', PDO::FETCH_COLUMN);
	}
	
	public function get_blocked_users() {
		return $this->sql->prep("group_{$this->group_id}_blocked_users", " 
			SELECT user_id
			FROM mangadex_user_block_group
			WHERE group_id = ?
			", [$this->group_id], 'fetchAll', PDO::FETCH_COLUMN);
	}
}
?>