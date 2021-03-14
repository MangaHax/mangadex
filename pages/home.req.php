<?php

$search = [];

if ($hentai_toggle == 0)
	$search['manga_hentai'] = 0;
elseif ($hentai_toggle == 2) 
	$search['manga_hentai'] = 1;

if(!isset($user, $user->show_unavailable) || !$user->show_unavailable){
    $search["available"] = 1;
}

$array_of_manga_ids = array_keys($user->get_followed_manga_ids_key_pair(), 1);
$manga_ids_in = prepare_in($array_of_manga_ids);

$blocked_group_ids = $user->get_blocked_groups();
$blocked_group_ids_in = prepare_in(array_keys($blocked_group_ids));
$blocked_group_ids_hash = md5($blocked_group_ids_in);

if (!isset($user, $user->excluded_genres) || empty($user->excluded_genres)) {
    $user->excluded_genres = false;
}
$excluded_genres_hash = md5($user->excluded_genres);

if ($lang_id_filter_string) {
	$in = prepare_in($lang_id_filter_array);
		
	if ($array_of_manga_ids) {
		if (!$user->latest_updates)
			$follows_updates = $sql->prep("follows_updates_{$user->user_id}_h_{$hentai_toggle}_a_" . isset($search['available']), "
				SELECT chapters.manga_id, chapters.chapter_id, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, chapters.group_id, chapters.group_id_2, chapters.group_id_3, 
					mangas.manga_name, mangas.manga_image, mangas.manga_hentai, 
					mangas.manga_last_volume,
					mangas.manga_last_chapter,
					lang.lang_name,	lang.lang_flag,
					group1.group_name AS group_name, 
					group2.group_name AS group_name_2, 
					group3.group_name AS group_name_3,
					chapters.available
				FROM ( 
					SELECT MAX(chapter_id) AS max_chapter_id 
					FROM mangadex_last_updated
					LEFT JOIN mangadex_mangas
						ON mangadex_mangas.manga_id = mangadex_last_updated.manga_id
					WHERE mangadex_last_updated.upload_timestamp < UNIX_TIMESTAMP()
						AND mangadex_last_updated.lang_id IN ($in)
						AND mangadex_mangas.manga_id IN ($manga_ids_in) " . 
					($blocked_group_ids ? "AND mangadex_last_updated.group_id NOT IN ($blocked_group_ids_in) " : '') .
					(isset($search['manga_hentai']) ? "AND mangadex_mangas.manga_hentai = {$search['manga_hentai']} " : '') .
                    (isset($search['available']) ? "AND mangadex_last_updated.available = {$search['available']} " : '')
					. "GROUP BY mangadex_last_updated.manga_id
				) AS temp
				INNER JOIN mangadex_last_updated AS chapters
					ON temp.max_chapter_id = chapters.chapter_id
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
				ORDER BY chapters.upload_timestamp DESC LIMIT 42
				", array_merge($lang_id_filter_array, $array_of_manga_ids, array_keys($blocked_group_ids)) , 'fetchAll', PDO::FETCH_ASSOC, 60);
		else
			$follows_updates = $sql->prep("follows_updates_grouped_{$user->user_id}_" . (isset($search['manga_hentai']) ? $search['manga_hentai'] : '') . "_"
                                                . isset($search["available"]), "
				SELECT chapters.manga_id, chapters.chapter_id, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, chapters.group_id, chapters.group_id_2, chapters.group_id_3, 
					lang.lang_name,	lang.lang_flag,
					users.username,
					mangas.manga_name,
					mangas.manga_image,
					mangas.manga_hentai,
					mangas.manga_last_volume,
					mangas.manga_last_chapter,
					group1.group_name AS group_name,
					group2.group_name AS group_name_2,
					group3.group_name AS group_name_3,
					levels.level_colour,
					threads.thread_posts,
					chapters.available
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
				LEFT JOIN mangadex_user_levels AS levels
					ON levels.level_id = users.level_id
				LEFT JOIN mangadex_threads AS threads
					ON threads.thread_id = chapters.thread_id
				WHERE upload_timestamp > (UNIX_TIMESTAMP() - 604800) 
					AND upload_timestamp < UNIX_TIMESTAMP() 
					AND chapter_deleted = 0
					AND chapters.lang_id IN ($in) 
					AND mangas.manga_id IN ($manga_ids_in) " . 
					($blocked_group_ids ? "AND chapters.group_id NOT IN ($blocked_group_ids_in) " : '') .
					(isset($search['manga_hentai']) ? "AND mangas.manga_hentai = {$search['manga_hentai']} " : '') .
                    (isset($search['available']) ? "AND chapters.available = {$search['available']} " : '')
				. "ORDER BY upload_timestamp DESC
				LIMIT 200 OFFSET 0
				", array_merge($lang_id_filter_array, $array_of_manga_ids, array_keys($blocked_group_ids)) , 'fetchAll', PDO::FETCH_ASSOC, 60);
	} else {
	    // User doesnt follow any manga
        $follows_updates = [];
    }

	$top_chapters_7d = $sql->prep("top_chapters_7d_$lang_id_filter_string", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*24*7)
			AND mangas.manga_hentai = 0 
			AND chapters.chapter_deleted = 0
			AND chapters.lang_id IN ($in)
		ORDER BY chapters.chapter_views DESC LIMIT 30
		", $lang_id_filter_array , 'fetchAll', PDO::FETCH_ASSOC, 7200);

	$top_chapters_24h = $sql->prep("top_chapters_24h_$lang_id_filter_string", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*24)
			AND mangas.manga_hentai = 0 
			AND chapters.chapter_deleted = 0
			AND chapters.lang_id IN ($in)
		ORDER BY chapters.chapter_views DESC LIMIT 30
		", $lang_id_filter_array , 'fetchAll', PDO::FETCH_ASSOC, 3600);

	$top_chapters_6h = $sql->prep("top_chapters_6h_$lang_id_filter_string", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*6)
			AND mangas.manga_hentai = 0 
			AND chapters.chapter_deleted = 0
			AND chapters.lang_id IN ($in)
		ORDER BY chapters.chapter_views DESC LIMIT 30
		", $lang_id_filter_array , 'fetchAll', PDO::FETCH_ASSOC, 1200);
	
	if (!$user->latest_updates) 
		$latest_updates = $sql->prep("latest_updates_{$lang_id_filter_string}_{$excluded_genres_hash}_{$blocked_group_ids_hash}_h_{$hentai_toggle}_a_" . isset($search['available']), "
			SELECT chapters.manga_id, chapters.chapter_id, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, chapters.group_id, chapters.group_id_2, chapters.group_id_3, 
				mangas.manga_name, mangas.manga_image, mangas.manga_hentai, 
                mangas.manga_last_volume,
                mangas.manga_last_chapter,
				lang.lang_name,	lang.lang_flag,
				group1.group_name AS group_name, 
				group2.group_name AS group_name_2, 
				group3.group_name AS group_name_3,
				chapters.available 
			FROM ( 
				SELECT MAX(chapter_id) AS max_chapter_id 
				FROM mangadex_last_updated
				LEFT JOIN mangadex_mangas
					ON mangadex_mangas.manga_id = mangadex_last_updated.manga_id
				WHERE mangadex_last_updated.upload_timestamp < UNIX_TIMESTAMP()
					AND mangadex_last_updated.lang_id IN ($in) " .
                ($blocked_group_ids ? "AND mangadex_last_updated.group_id NOT IN ($blocked_group_ids_in) " : '') .
                ($user->excluded_genres ? 'AND mangadex_last_updated.manga_id NOT IN (SELECT manga_id FROM mangadex_manga_genres genres WHERE genres.genre_id IN ('.$user->excluded_genres.')) ' : '') .
				(isset($search['manga_hentai']) ? "AND mangadex_mangas.manga_hentai = {$search['manga_hentai']} " : '') .
                (isset($search['available']) ? "AND mangadex_last_updated.available = {$search['available']} " : '')
			. "GROUP BY mangadex_last_updated.manga_id
			) AS temp
			INNER JOIN mangadex_last_updated AS chapters
				ON temp.max_chapter_id = chapters.chapter_id
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
			ORDER BY chapters.upload_timestamp DESC LIMIT 42
			", array_merge($lang_id_filter_array, array_keys($blocked_group_ids)), 'fetchAll', PDO::FETCH_ASSOC, 60);
	else
		$latest_updates = $sql->prep("latest_updates_grouped_{$lang_id_filter_string}_{$excluded_genres_hash}_{$blocked_group_ids_hash}_h_{$hentai_toggle}_a_" . isset($search['available']), "
			SELECT chapters.manga_id, chapters.chapter_id, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, chapters.group_id, chapters.group_id_2, chapters.group_id_3, 
				lang.lang_name,	lang.lang_flag,
				users.username,
				mangas.manga_name,
				mangas.manga_image,
				mangas.manga_hentai,
				mangas.manga_last_volume,
				mangas.manga_last_chapter,
				group1.group_name AS group_name,
				group2.group_name AS group_name_2,
				group3.group_name AS group_name_3,
				levels.level_colour,
				threads.thread_posts,
				chapters.available
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
			LEFT JOIN mangadex_user_levels AS levels
				ON levels.level_id = users.level_id
			LEFT JOIN mangadex_threads AS threads
				ON threads.thread_id = chapters.thread_id
			WHERE upload_timestamp > (UNIX_TIMESTAMP() - 604800) 
				AND upload_timestamp < UNIX_TIMESTAMP() 
				AND chapter_deleted = 0
				AND chapters.lang_id IN ($in) " .
				($blocked_group_ids ? "AND chapters.group_id NOT IN ($blocked_group_ids_in) " : '') .
                ($user->excluded_genres ? 'AND chapters.manga_id NOT IN (SELECT manga_id FROM mangadex_manga_genres genres WHERE genres.genre_id IN ('.$user->excluded_genres.')) ' : '') .
				(isset($search['manga_hentai']) ? "AND mangas.manga_hentai = {$search['manga_hentai']} " : '') .
                (isset($search['available']) ? "AND chapters.available = {$search['available']} " : '')
			. "ORDER BY upload_timestamp DESC
			LIMIT 200 OFFSET 0
			", array_merge($lang_id_filter_array, array_keys($blocked_group_ids)), 'fetchAll', PDO::FETCH_ASSOC, 60);
}
else {
	if ($array_of_manga_ids) {
		if (!$user->latest_updates) 
			$follows_updates = $sql->prep("follows_updates_{$user->user_id}_" . (isset($search['manga_hentai']) ? $search['manga_hentai'] : '') . isset($search["available"]), "
				SELECT chapters.manga_id, chapters.chapter_id, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, chapters.group_id, chapters.group_id_2, chapters.group_id_3, 
					mangas.manga_name, mangas.manga_image, mangas.manga_hentai,
                    mangas.manga_last_volume,
					mangas.manga_last_chapter,
					lang.lang_name,	lang.lang_flag,
					group1.group_name AS group_name, 
					group2.group_name AS group_name_2, 
					group3.group_name AS group_name_3,
					chapters.available 
				FROM ( 
					SELECT MAX(chapter_id) AS max_chapter_id 
					FROM mangadex_last_updated
					LEFT JOIN mangadex_mangas
						ON mangadex_mangas.manga_id = mangadex_last_updated.manga_id
					WHERE mangadex_last_updated.upload_timestamp < UNIX_TIMESTAMP()
						AND mangadex_mangas.manga_id IN ($manga_ids_in) " . 
						($blocked_group_ids ? "AND mangadex_last_updated.group_id NOT IN ($blocked_group_ids_in) " : '') .
						(isset($search['manga_hentai']) ? "AND mangadex_mangas.manga_hentai = {$search['manga_hentai']} " : '') .
                        (isset($search['available']) ? "AND mangadex_last_updated.available = {$search['available']} " : '')
						. "GROUP BY mangadex_last_updated.manga_id
				) AS temp
				INNER JOIN mangadex_last_updated AS chapters
					ON temp.max_chapter_id = chapters.chapter_id
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
				ORDER BY chapters.upload_timestamp DESC LIMIT 42
				", array_merge($array_of_manga_ids, array_keys($blocked_group_ids)) , 'fetchAll', PDO::FETCH_ASSOC, 60);
		else 
			$follows_updates = $sql->prep("follows_updates_grouped_{$user->user_id}_" . (isset($search['manga_hentai']) ? $search['manga_hentai'] : '') . isset($search["available"]), "
				SELECT chapters.manga_id, chapters.chapter_id, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, chapters.group_id, chapters.group_id_2, chapters.group_id_3, 
					lang.lang_name,	lang.lang_flag,
					users.username,
					mangas.manga_name,
					mangas.manga_image,
					mangas.manga_hentai,
					mangas.manga_last_volume,
					mangas.manga_last_chapter,
					group1.group_name AS group_name,
					group2.group_name AS group_name_2,
					group3.group_name AS group_name_3,
					levels.level_colour,
					threads.thread_posts,
					chapters.available
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
				LEFT JOIN mangadex_user_levels AS levels
					ON levels.level_id = users.level_id
				LEFT JOIN mangadex_threads AS threads
					ON threads.thread_id = chapters.thread_id
				WHERE upload_timestamp > (UNIX_TIMESTAMP() - 604800) 
					AND upload_timestamp < UNIX_TIMESTAMP() 
					AND chapter_deleted = 0 
					AND mangas.manga_id IN ($manga_ids_in) " . 
					($blocked_group_ids ? "AND chapters.group_id NOT IN ($blocked_group_ids_in) " : '') .
					(isset($search['manga_hentai']) ? "AND mangas.manga_hentai = {$search['manga_hentai']} " : '') .
                    (isset($search['available']) ? "AND chapters.available = {$search['available']} " : '')
				. "ORDER BY upload_timestamp DESC
				LIMIT 200 OFFSET 0
				", array_merge($array_of_manga_ids, array_keys($blocked_group_ids)) , 'fetchAll', PDO::FETCH_ASSOC, 60);
	} else {
	    $follows_updates = [];
    }
	
	$top_chapters_7d = $sql->query_read("top_chapters_7d", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*24*7)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
		ORDER BY chapters.chapter_views DESC LIMIT 30
		", 'fetchAll', PDO::FETCH_ASSOC, 7200);

	$top_chapters_24h = $sql->query_read("top_chapters_24h", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*24)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
		ORDER BY chapters.chapter_views DESC LIMIT 30
		", 'fetchAll', PDO::FETCH_ASSOC, 3600);

	$top_chapters_6h = $sql->query_read("top_chapters_6h", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*6)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
		ORDER BY chapters.chapter_views DESC LIMIT 30
		", 'fetchAll', PDO::FETCH_ASSOC, 1200);
	
	if (!$user->latest_updates) 
		$latest_updates = $sql->prep("latest_updates_{$excluded_genres_hash}_{$blocked_group_ids_hash}_h_{$hentai_toggle}_a_" . isset($search['available']), "
			SELECT chapters.manga_id, chapters.chapter_id, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, chapters.group_id, chapters.group_id_2, chapters.group_id_3, 
				mangas.manga_name, mangas.manga_image, mangas.manga_hentai, 
                mangas.manga_last_volume,
                mangas.manga_last_chapter,
				lang.lang_name,	lang.lang_flag,
				group1.group_name AS group_name, 
				group2.group_name AS group_name_2, 
				group3.group_name AS group_name_3,
				chapters.available 
			FROM ( 
				SELECT MAX(chapter_id) AS max_chapter_id 
				FROM mangadex_last_updated
				LEFT JOIN mangadex_mangas
					ON mangadex_mangas.manga_id = mangadex_last_updated.manga_id
				WHERE mangadex_last_updated.upload_timestamp < UNIX_TIMESTAMP() " .
				($blocked_group_ids ? "AND mangadex_last_updated.group_id NOT IN ($blocked_group_ids_in) " : '') .
                ($user->excluded_genres ? 'AND mangadex_last_updated.manga_id NOT IN (SELECT manga_id FROM mangadex_manga_genres genres WHERE genres.genre_id IN ('.$user->excluded_genres.')) ' : '') .
				(isset($search['manga_hentai']) ? "AND mangadex_mangas.manga_hentai = {$search['manga_hentai']} " : '') .
                (isset($search['available']) ? "AND mangadex_last_updated.available = {$search['available']} " : '')
			. "GROUP BY mangadex_last_updated.manga_id
			) AS temp
			INNER JOIN mangadex_last_updated AS chapters
				ON temp.max_chapter_id = chapters.chapter_id
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
			ORDER BY chapters.upload_timestamp DESC LIMIT 42
			", array_keys($blocked_group_ids), 'fetchAll', PDO::FETCH_ASSOC, 60);
	else
		$latest_updates = $sql->prep("latest_updates_grouped_{$excluded_genres_hash}_{$blocked_group_ids_hash}_h_{$hentai_toggle}_a_" . isset($search['available']), "
			SELECT chapters.manga_id, chapters.chapter_id, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, chapters.group_id, chapters.group_id_2, chapters.group_id_3, 
				lang.lang_name,	lang.lang_flag,
				users.username,
				mangas.manga_name,
				mangas.manga_image,
				mangas.manga_hentai,
				mangas.manga_last_volume,
				mangas.manga_last_chapter,
				group1.group_name AS group_name,
				group2.group_name AS group_name_2,
				group3.group_name AS group_name_3,
				levels.level_colour,
				threads.thread_posts,
				chapters.available
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
			LEFT JOIN mangadex_user_levels AS levels
				ON levels.level_id = users.level_id
			LEFT JOIN mangadex_threads AS threads
				ON threads.thread_id = chapters.thread_id
			WHERE upload_timestamp > (UNIX_TIMESTAMP() - 604800) 
				AND upload_timestamp < UNIX_TIMESTAMP() 
				AND chapter_deleted = 0 " .
				($blocked_group_ids ? "AND chapters.group_id NOT IN ($blocked_group_ids_in) " : '') .
                ($user->excluded_genres ? 'AND chapters.manga_id NOT IN (SELECT manga_id FROM mangadex_manga_genres genres WHERE genres.genre_id IN ('.$user->excluded_genres.')) ' : '') .
				(isset($search['manga_hentai']) ? "AND mangas.manga_hentai = {$search['manga_hentai']} " : '') .
                (isset($search['available']) ? "AND chapters.available = {$search['available']} " : '')
			. "ORDER BY upload_timestamp DESC
			LIMIT 200 OFFSET 0
			", array_keys($blocked_group_ids), 'fetchAll', PDO::FETCH_ASSOC, 60);
}


$featured = $memcached->get('featured');
	
$new_manga = $memcached->get('new_manga');
$top_follows = $memcached->get('top_follows');
$top_rating = $memcached->get('top_rating');

$latest_manga_comments = $memcached->get('latest_manga_comments');
$latest_forum_posts = $memcached->get('latest_forum_posts');
$latest_news_posts = $memcached->get('latest_news_posts');

/*print display_carousel($latest_updates, 'Latest updates', 'latest_updates');
print display_carousel($new_manga, 'New titles', 'new_titles'); 
print display_carousel($top_follows, 'Top follows', 'top_follows'); */

$templateVars = [
    'user' => $user,
    'latest_updates' => $latest_updates,
    'array_of_manga_ids' => $array_of_manga_ids,
    'follows_updates' => $follows_updates,
    'top_chapters_6h' => $top_chapters_6h,
    'top_chapters_24h' => $top_chapters_24h,
    'top_chapters_7d' => $top_chapters_7d,
    'top_rating' => $top_rating,
    'top_follows' => $top_follows,
    'latest_forum_posts' => $latest_forum_posts,
    'latest_manga_comments' => $latest_manga_comments,
    'latest_news_posts' => $latest_news_posts,
    'featured' => $featured,
    'new_manga' => $new_manga,
];

$page_html = parse_template('home', $templateVars);
