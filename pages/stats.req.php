<?php
die('Stats have been disabled until further notice.'); // disabled 11-03-2020

$mode = $_GET['mode'] ?? 'top';

if ($lang_id_filter_string) {
	$in = prepare_in($lang_id_filter_array);
	
	$trending_chapters_live = $sql->prep("trending_chapters_live_$lang_id_filter_string", "
		SELECT COUNT(*) AS chapter_views, live.chapter_id, chapters.manga_id, chapters.volume, chapters.chapter, chapters.title, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapter_live_views AS live
		LEFT JOIN mangadex_chapters AS chapters
			ON chapters.chapter_id = live.chapter_id
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
        WHERE live.timestamp > (UNIX_TIMESTAMP() - 60*10)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
			AND chapters.lang_id IN ($in)
		GROUP BY live.chapter_id 
		ORDER BY chapter_views DESC LIMIT 50
		", $lang_id_filter_array , 'fetchAll', PDO::FETCH_ASSOC, 60);

	$trending_chapters_hour = $sql->prep("trending_chapters_hour_$lang_id_filter_string", "
		SELECT COUNT(*) AS chapter_views, live.chapter_id, chapters.manga_id, chapters.volume, chapters.chapter, chapters.title, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapter_live_views AS live
		LEFT JOIN mangadex_chapters AS chapters
			ON chapters.chapter_id = live.chapter_id
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
        WHERE live.timestamp > (UNIX_TIMESTAMP() - 60*60)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
			AND chapters.lang_id IN ($in)
		GROUP BY live.chapter_id 
		ORDER BY chapter_views DESC LIMIT 50
		", $lang_id_filter_array , 'fetchAll', PDO::FETCH_ASSOC, 600);
		
	$trending_chapters_day = $sql->prep("trending_chapters_day_$lang_id_filter_string", "
		SELECT COUNT(*) AS chapter_views, live.chapter_id, chapters.manga_id, chapters.volume, chapters.chapter, chapters.title, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapter_live_views AS live
		LEFT JOIN mangadex_chapters AS chapters
			ON chapters.chapter_id = live.chapter_id
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
        WHERE live.timestamp > (UNIX_TIMESTAMP() - 60*60*24)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
			AND chapters.lang_id IN ($in)
		GROUP BY live.chapter_id 
		ORDER BY chapter_views DESC LIMIT 50
		", $lang_id_filter_array , 'fetchAll', PDO::FETCH_ASSOC, 3600);
				
	$top_chapters_7d = $sql->prep("extended_top_chapters_7d_$lang_id_filter_string", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*24*7)
			AND mangas.manga_hentai = 0 
			AND chapters.chapter_deleted = 0
			AND chapters.lang_id IN ($in)
		ORDER BY chapters.chapter_views DESC LIMIT 50
		", $lang_id_filter_array , 'fetchAll', PDO::FETCH_ASSOC, 7200);

	$top_chapters_24h = $sql->prep("extended_top_chapters_24h_$lang_id_filter_string", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*24)
			AND mangas.manga_hentai = 0 
			AND chapters.chapter_deleted = 0
			AND chapters.lang_id IN ($in)
		ORDER BY chapters.chapter_views DESC LIMIT 40
		", $lang_id_filter_array , 'fetchAll', PDO::FETCH_ASSOC, 3600);

	$top_chapters_6h = $sql->prep("extended_top_chapters_6h_$lang_id_filter_string", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*6)
			AND mangas.manga_hentai = 0 
			AND chapters.chapter_deleted = 0
			AND chapters.lang_id IN ($in)
		ORDER BY chapters.chapter_views DESC LIMIT 40
		", $lang_id_filter_array , 'fetchAll', PDO::FETCH_ASSOC, 1200);
}
else {	
	$trending_chapters_live = $sql->query_read("trending_chapters_live", "
		SELECT COUNT(*) AS chapter_views, live.chapter_id, chapters.manga_id, chapters.volume, chapters.chapter, chapters.title, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapter_live_views AS live
		LEFT JOIN mangadex_chapters AS chapters
			ON chapters.chapter_id = live.chapter_id
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
        WHERE live.timestamp > (UNIX_TIMESTAMP() - 60*10)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
		GROUP BY live.chapter_id 
		ORDER BY chapter_views DESC LIMIT 50
		", 'fetchAll', PDO::FETCH_ASSOC, 60);

	$trending_chapters_hour = $sql->query_read("trending_chapters_hour", "
		SELECT COUNT(*) AS chapter_views, live.chapter_id, chapters.manga_id, chapters.volume, chapters.chapter, chapters.title, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapter_live_views AS live
		LEFT JOIN mangadex_chapters AS chapters
			ON chapters.chapter_id = live.chapter_id
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
        WHERE live.timestamp > (UNIX_TIMESTAMP() - 60*60)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
		GROUP BY live.chapter_id 
		ORDER BY chapter_views DESC LIMIT 50
		", 'fetchAll', PDO::FETCH_ASSOC, 600);

	$trending_chapters_day = $sql->query_read("trending_chapters_day", "
		SELECT COUNT(*) AS chapter_views, live.chapter_id, chapters.manga_id, chapters.volume, chapters.chapter, chapters.title, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapter_live_views AS live
		LEFT JOIN mangadex_chapters AS chapters
			ON chapters.chapter_id = live.chapter_id
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
        WHERE live.timestamp > (UNIX_TIMESTAMP() - 60*60*24)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
		GROUP BY live.chapter_id 
		ORDER BY chapter_views DESC LIMIT 50
		", 'fetchAll', PDO::FETCH_ASSOC, 3600);
				
	$top_chapters_7d = $sql->query_read("extended_top_chapters_7d", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*24*7)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
		ORDER BY chapters.chapter_views DESC LIMIT 50
		", 'fetchAll', PDO::FETCH_ASSOC, 7200);

	$top_chapters_24h = $sql->query_read("extended_top_chapters_24h", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*24)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
		ORDER BY chapters.chapter_views DESC LIMIT 40
		", 'fetchAll', PDO::FETCH_ASSOC, 3600);

	$top_chapters_6h = $sql->query_read("extended_top_chapters_6h", "
		SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.volume, chapters.chapter, chapters.title, chapters.upload_timestamp, 
			mangas.manga_name, mangas.manga_image, mangas.manga_hentai
		FROM mangadex_chapters AS chapters
		LEFT JOIN mangadex_mangas AS mangas
			ON mangas.manga_id = chapters.manga_id
		WHERE chapters.upload_timestamp > (UNIX_TIMESTAMP() - 60*60*6)
			AND mangas.manga_hentai = 0
			AND chapters.chapter_deleted = 0
		ORDER BY chapters.chapter_views DESC LIMIT 40
		", 'fetchAll', PDO::FETCH_ASSOC, 1200);
}

$templateVars = [
    'user' => $user,
	'mode' => $mode,
    'top_chapters_6h' => $top_chapters_6h,
    'top_chapters_24h' => $top_chapters_24h,
    'top_chapters_7d' => $top_chapters_7d,
    'trending_chapters_live' => $trending_chapters_live,
    'trending_chapters_hour' => $trending_chapters_hour,
    'trending_chapters_day' => $trending_chapters_day,
];

$page_html = parse_template('user/stats', $templateVars);
