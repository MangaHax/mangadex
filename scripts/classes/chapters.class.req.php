<?php
class Chapters {

	public function __construct($search) {
		global $sql;
		$this->sql = $sql;
		
		$search_string = "";
		$pdo_bind = [];

		if (!isset($search['chapter_deleted']))
		    $search['chapter_deleted'] = 0;
			
		foreach ($search as $key => $value) {
			switch ($key) {
				case 'multi_lang_id':
					$arr = explode(",", $value);
					$in = prepare_in($arr);
					$search_string .= "chapters.lang_id IN ($in) AND ";
					$pdo_bind = array_merge($pdo_bind, $arr);
					break;
					
				case 'manga_hentai':
					$search_string .= "mangas.manga_hentai = ? AND ";
					$pdo_bind[] = $value;
					break;
					
				case 'upload_timestamp':
					$search_string .= "upload_timestamp > (UNIX_TIMESTAMP() - $value) AND ";
					break;
					
				case 'exclude_delayed':
					$search_string .= "upload_timestamp < UNIX_TIMESTAMP() AND ";
					break;	

				case 'manga_id':
					$search_string .= "chapters.manga_id = ? AND ";
					$pdo_bind[] = $value;
					break;	

				case 'group_id':
					$search_string .= "(chapters.group_id = ? OR chapters.group_id_2 = ? OR chapters.group_id_3 = ?) AND ";
					$pdo_bind[] = $value;
					$pdo_bind[] = $value;
					$pdo_bind[] = $value;
					break;	

				case 'user_id':
					$search_string .= "chapters.user_id = ? AND ";
					$pdo_bind[] = $value;
					break;	

				case 'lang_id':
					$search_string .= "chapters.lang_id = ? AND ";
					$pdo_bind[] = $value;
					break;	

				case 'manga_ids_array':
                    $in = prepare_in($value);
                    $search_string .= "chapters.manga_id IN ($in) AND ";
                    $pdo_bind = array_merge($pdo_bind, $value);
					break;

                case 'excluded_genres':
                    if (!is_array($value))
                        $value = explode(',', $value);
                    $in = prepare_in($value);
                    $search_string .= "mangas.manga_id NOT IN (SELECT manga_id FROM mangadex_manga_genres genres WHERE genres.genre_id IN ($in)) AND ";
					$pdo_bind = array_merge($pdo_bind, $value);
                    break;
					
				case 'blocked_groups':
                    if (!is_array($value))
                        $value = explode(',', $value);
                    $in = prepare_in($value);
                    $search_string .= "chapters.group_id NOT IN ($in) AND ";
					$pdo_bind = array_merge($pdo_bind, $value);
                    break;
					
				default:
					$field = prepare_identifier($key);
					$search_string .= "$field = ? AND ";
					$pdo_bind[] = $value;
					break;
			}
		}

		// TODO: Is this ever used anywhere? Seems pointless.
		$this->num_rows = $sql->prep("chapters_query_" . hash_array($pdo_bind) . "_num_rows", "
			SELECT count(*) 
			FROM mangadex_chapters AS chapters
			LEFT JOIN mangadex_mangas AS mangas
				ON mangas.manga_id = chapters.manga_id 
			WHERE $search_string 1=1 
			", $pdo_bind, 'fetchColumn', '', 60);
		$this->search_string = $search_string;
		$this->pdo_bind = $pdo_bind;	
	}	
	
	public function query_read($order, $limit, $current_page) {
		$orderby = prepare_orderby($order, ["upload_timestamp DESC", "(CASE volume WHEN '' THEN 1 END) DESC, abs(volume) DESC, abs(chapter) DESC, group_id ASC"]);
		$limit = prepare_numeric($limit);
		$offset = prepare_numeric($limit * ($current_page - 1));

		$results = $this->sql->prep("chapters_query_" . hash_array($this->pdo_bind) . "_orderby_".md5($orderby)."_offset_$offset", "
			SELECT chapters.*, 
				lang.*, 
				users.username, 
				options.show_premium_badge,
				options.show_md_at_home_badge,
				mangas.manga_name, 
				mangas.manga_image, 
				mangas.manga_hentai, 
				mangas.manga_last_chapter,
				mangas.manga_last_volume,
				group1.group_name AS group_name, 
				group2.group_name AS group_name_2, 
				group3.group_name AS group_name_3, 
				group1.group_leader_id AS group_leader_id, 
				group2.group_leader_id AS group_leader_id_2, 
				group3.group_leader_id AS group_leader_id_3,
				levels.level_colour,
				threads.thread_posts
			FROM mangadex_chapters AS chapters
			LEFT JOIN mangadex_groups AS group1 
				ON group1.group_id = chapters.group_id 
			LEFT JOIN mangadex_groups AS group2 
				ON group2.group_id = chapters.group_id_2
			LEFT JOIN mangadex_groups AS group3 
				ON group3.group_id = chapters.group_id_3
			LEFT JOIN mangadex_mangas AS mangas
				ON mangas.manga_id = chapters.manga_id 
			LEFT JOIN mangadex_languages AS lang
				ON lang.lang_id = chapters.lang_id 
			LEFT JOIN mangadex_users AS users
				ON users.user_id = chapters.user_id
			LEFT JOIN mangadex_user_options AS options
				ON options.user_id = chapters.user_id
			LEFT JOIN mangadex_user_levels AS levels
				ON levels.level_id = users.level_id
			LEFT JOIN mangadex_threads AS threads
				ON threads.thread_id = chapters.thread_id
			WHERE $this->search_string 1=1 
			ORDER BY $orderby 
			LIMIT $limit OFFSET $offset
			", $this->pdo_bind, 'fetchAll', PDO::FETCH_ASSOC, 60); // using PDO::FETCH_ASSOC instead of PDO::FETCH_UNIQUE adds the chapter_id to the resulting array. FETCH_UNIQUE should never be used!
		
		//return get_results_as_object($results, 'chapter_id'); // TODO: Why would we ever want to return an array as an object?
        return $results;
	}
}

class Chapter {

    public $user_id;

    public function __construct($id) {
		global $sql, $pdo;
		$this->sql = $sql;
		$this->pdo = $pdo;
		$id = prepare_numeric($id);
		
		$row = $sql->prep("chapter_$id", "
			SELECT chapters.*, 
				lang.*, 
				mangas.manga_name, 
				mangas.manga_hentai, 
				group1.group_website, 
				group1.group_name AS group_name, 
				group2.group_name AS group_name_2, 
				group3.group_name AS group_name_3, 
				group1.group_leader_id AS group_leader_id, 
				group2.group_leader_id AS group_leader_id_2, 
				group3.group_leader_id AS group_leader_id_3,
				threads.thread_posts
			FROM mangadex_chapters AS chapters
			LEFT JOIN mangadex_mangas AS mangas
				ON mangas.manga_id = chapters.manga_id 
			LEFT JOIN mangadex_languages AS lang
				ON lang.lang_id = chapters.lang_id 
			LEFT JOIN mangadex_groups AS group1 
				ON group1.group_id = chapters.group_id 
			LEFT JOIN mangadex_groups AS group2 
				ON group2.group_id = chapters.group_id_2
			LEFT JOIN mangadex_groups AS group3 
				ON group3.group_id = chapters.group_id_3
			LEFT JOIN mangadex_threads AS threads
				ON chapters.thread_id = threads.thread_id 	
			WHERE chapters.chapter_id = ? 
			", [$id], 'fetch', PDO::FETCH_OBJ, 86400);
		
		//copy $row into $this
		if ($row) {  
			foreach ($row as $key => $value) {
				$this->$key = $value;
			}
			
			$this->chapter_comments = ($this->thread_posts) ? "<span class='badge'>$this->thread_posts</span>" : "";
		}
	}

	public function get_comments() {
		$results = $this->sql->prep("chapter_{$this->chapter_id}_comment", "
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
			LIMIT 20
			", [$this->thread_id], 'fetchAll', PDO::FETCH_UNIQUE, -1);
		
		return get_results_as_object($results, 'post_id');
	}
	
	public function get_other_chapters($group_id) {
		$results = $this->sql->prep("chapter_{$this->chapter_id}_other_chapters", "
			SELECT CONCAT(`volume`, ',', `chapter`) AS volch, chapter, chapter_id, volume, title, group_id
			FROM mangadex_chapters 
			WHERE manga_id = ? AND lang_id = ? AND chapter_deleted = 0 
			ORDER BY (CASE volume WHEN '' THEN 1 END) DESC, abs(volume) DESC, abs(chapter) DESC
			", [$this->manga_id, $this->lang_id], 'fetchAll', PDO::FETCH_GROUP, 60);
		
		foreach ($results as $volch => $row) {
			if (count($row) == 1)
				$temp[$volch] = $row[0];
			else {
				$ch = 0;
				foreach($row as $r) {
					if ($r['group_id'] == $group_id) {
						$temp[$volch] = $r;
						$ch = 1;
					}
				}
				if (!$ch)
					$temp[$volch] = $row[0];
			}
		}
		
		foreach ($temp as $volch => $row) {
			if ($row['volume'] || $row['chapter']) {
				$array["name"][$row['chapter_id']] = ($row['volume'] ? "Volume {$row['volume']} " : "") . ($row['chapter'] ? "Chapter {$row['chapter']} " : "Chapter 0"); 
			}
			else {
				$array["name"][$row['chapter_id']] = $row['title'];
			}
			
			$array["id"][] = $row['chapter_id'];
		}	
		
		foreach ($array["name"] as $key => $name) {
			$array['tea'][] = [
				'id' => $key,
				'name' => trim($name)
			];
		}
		
		
			
		return $array; //array of groups or group_ids
	}
	
	public function get_other_groups() {
		return $this->sql->prep("chapter_{$this->chapter_id}_other_groups", "
			SELECT mangadex_chapters.chapter_id, 
				mangadex_languages.lang_name, mangadex_languages.lang_flag, 
				mangadex_chapters.group_id, 
				mangadex_chapters.group_id_2, 
				mangadex_chapters.group_id_3, 
				group1.group_name AS group_name, 
				group2.group_name AS group_name_2, 
				group3.group_name AS group_name_3
			FROM mangadex_chapters 
			LEFT JOIN mangadex_groups AS group1 
				ON group1.group_id = mangadex_chapters.group_id 
			LEFT JOIN mangadex_groups AS group2 
				ON group2.group_id = mangadex_chapters.group_id_2
			LEFT JOIN mangadex_groups AS group3 
				ON group3.group_id = mangadex_chapters.group_id_3
			LEFT JOIN mangadex_languages 
				ON mangadex_languages.lang_id = mangadex_chapters.lang_id 
			WHERE mangadex_chapters.manga_id = ? 
				AND mangadex_chapters.lang_id = ? 
				AND mangadex_chapters.volume = ? 
				AND mangadex_chapters.chapter = ? 
				AND mangadex_chapters.chapter_deleted = 0 
			ORDER BY mangadex_chapters.group_id ASC
			", [$this->manga_id, $this->lang_id, $this->volume, $this->chapter], 'fetchAll', PDO::FETCH_UNIQUE, 60);
	}
	
	public function get_pages_of_prev_chapter($id) {
		$page_order = $this->sql->prep("chapter_{$this->chapter_id}_pages_of_prev_chapter_$id", "
			SELECT page_order 
			FROM mangadex_chapters 
			WHERE chapter_id = ? 
			LIMIT 1
			", [$id], 'fetchColumn', '', 60);
			
		return count(explode(",", $page_order));
	}
	
	/*public function update_chapter_views($array, $user_id) {
		if (!$array)
			$this->pdo->prepare(" INSERT IGNORE INTO mangadex_chapter_views_v2 (user_id, chapter_id) VALUES (?, ?) ")->execute([$user_id, $this->chapter_id]);
		
		elseif (!in_array($this->chapter_id, $array))
			$this->pdo->prepare(" UPDATE mangadex_chapter_views_v2 SET chapter_id = CONCAT(chapter_id, ?) WHERE user_id = ? LIMIT 1 ")->execute([",$this->chapter_id", $user_id]);
	}*/
	
	public function update_chapter_views($user_id, $array_of_user_ids) {
		if (isset($array_of_user_ids[$user_id]) && !in_array($array_of_user_ids[$user_id], [2,3,4,5])) {
			global $memcached;
			$this->sql->modify('update_chapter_views', " INSERT IGNORE INTO mangadex_chapter_views (user_id, chapter_id) VALUES (?, ?) ", [$user_id, $this->chapter_id]);
			$memcached->delete("user_{$user_id}_read_chapters");
		}
	}
	
	public function update_reading_history($user_id, $reading_history) {
		global $memcached;

		// Process history entries
        $duplicate = false;
		$titleCount = 0;
        $hentaiTitleCount = 0;
        $oldestChapterId = 0;
        $oldestHentaiChapterId = 0;
		foreach ($reading_history AS $chapter) {
            if ($chapter['chapter_id'] === $this->chapter_id) {
                $duplicate = true;
            }

		    if ($chapter['manga_hentai'] !== 1) {
		        $titleCount++;
		        $oldestChapterId = $chapter['chapter_id'];
            } else {
		        $hentaiTitleCount++;
		        $oldestHentaiChapterId = $chapter['chapter_id'];
            }
        }

        // Delete row from hentai or non-hentai?
        if (!$duplicate && $this->manga_hentai === 0 && $titleCount >= 10) {
            $this->sql->modify(
                'update_reading_history',
                'DELETE FROM mangadex_reading_history WHERE user_id = ? AND chapter_id = ?',
                [$user_id, $oldestChapterId]
            );
        } elseif (!$duplicate && $this->manga_hentai === 1 && $hentaiTitleCount >= 10) {
            $this->sql->modify(
                'update_reading_history',
                'DELETE FROM mangadex_reading_history WHERE user_id = ? AND chapter_id = ?',
                [$user_id, $oldestHentaiChapterId]
            );
        }

        // Add chapter to history
		$this->sql->modify(
		    'update_reading_history',
            'INSERT INTO mangadex_reading_history (user_id, manga_id, chapter_id, timestamp) VALUES (?, ?, ?, UNIX_TIMESTAMP()) ON DUPLICATE KEY UPDATE chapter_id = ?, timestamp = UNIX_TIMESTAMP()',
            [$user_id, $this->manga_id, $this->chapter_id, $this->chapter_id]
        );

		// Clear cache
        $memcached->delete("user_{$user_id}_reading_history_ht1");
        $memcached->delete("user_{$user_id}_reading_history_ht" . ($this->manga_hentai === 0 ? 0 : 2));
	}
}

class Chapter_reports {
	public function __construct($age, $limit = 300) {
		global $sql;
		$age_operator = ($age == "new") ? "=" : ">";
		
		$limit = prepare_numeric($limit);
		//$offset = prepare_numeric($offset);
		
		$results = $sql->query_read("chapter_reports", "
			SELECT reports.*, 
				chapters.manga_id, 
				reporter.username AS reported_name, 
				actioned.username AS actioned_name, 
				reporter_levels.level_colour AS reported_level_colour, 
				actioned_levels.level_colour AS actioned_level_colour 
			FROM mangadex_reports_chapters AS reports
			LEFT JOIN mangadex_users AS reporter 
				ON reports.report_user_id = reporter.user_id
			LEFT JOIN mangadex_user_levels AS reporter_levels 
				ON reporter.level_id = reporter_levels.level_id
			LEFT JOIN mangadex_users AS actioned 
				ON reports.report_mod_user_id = actioned.user_id
			LEFT JOIN mangadex_user_levels AS actioned_levels 
				ON actioned.level_id = actioned_levels.level_id
			LEFT JOIN mangadex_chapters AS chapters
				ON reports.report_chapter_id = chapters.chapter_id 
			WHERE reports.report_mod_user_id $age_operator 0
			ORDER BY reports.report_timestamp DESC
			LIMIT $limit 
			", 'fetchAll', PDO::FETCH_UNIQUE, -1);
			
		foreach ($results as $i => $report) {
			$this->{$i} = new \stdClass();
			foreach ($report as $key => $value) {
				$this->{$i}->$key = $value;
			}
		}
	}	
}

class Upload_queue {
	public function __construct() {
		global $sql;
		
		$results = $sql->query_read("upload_queue", "
			SELECT queue.*,
				users.username, levels.level_colour
			FROM mangadex_upload_queue AS queue 
			LEFT JOIN mangadex_users AS users
				ON queue.user_id = users.user_id
			LEFT JOIN mangadex_chapters AS chapters
				ON queue.chapter_id = chapters.chapter_id
			LEFT JOIN mangadex_user_levels AS levels
				ON users.level_id = levels.level_id
			WHERE queue.queue_conclusion IS NULL
			ORDER BY chapters.upload_timestamp DESC
			", 'fetchAll', PDO::FETCH_UNIQUE, -1);
			
		foreach ($results as $i => $queue) {
			$this->{$i} = new \stdClass();
			foreach ($queue as $key => $value) {
				$this->{$i}->$key = $value;
			}
		}
	}
}
?>