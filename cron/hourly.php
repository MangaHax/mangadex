<?php

if (PHP_SAPI !== 'cli')
    die();

require_once (__DIR__.'/../bootstrap.php');

//require_once (__DIR__."/../config.req.php"); //must be like this

require_once (ABSPATH . "/scripts/header.req.php");

// prune remote file upload tmpfiles
$dirh = opendir(sys_get_temp_dir());
$nameFormat = 'remote_file_dl_';
while (false !== ($entry = readdir($dirh))) {
    if (strpos($entry, $nameFormat) === 0) {
        $tpath = sys_get_temp_dir().'/'.$entry;
        $ageInSeconds = time() - filectime($tpath);
        if ($ageInSeconds > (60 * 60)) {
            unlink($tpath);
        }
    }
}

//updated featured
$memcached->delete('featured');
$manga_lists = new Manga_Lists();
$array_of_featured_manga_ids = $manga_lists->get_manga_list(11);
if (!empty($array_of_featured_manga_ids)) {
    $manga_ids_in = prepare_in($array_of_featured_manga_ids);
    $featured = $sql->prep('featured', "
	SELECT chapters.manga_id, chapters.chapter_id, chapters.chapter_views, chapters.chapter, chapters.upload_timestamp, 
		mangas.manga_name, mangas.manga_image, mangas.manga_hentai, mangas.manga_bayesian,
		(SELECT count(*) FROM mangadex_follow_user_manga WHERE mangadex_follow_user_manga.manga_id = mangas.manga_id) AS count_follows
	FROM mangadex_chapters AS chapters
	LEFT JOIN mangadex_mangas AS mangas
		ON mangas.manga_id = chapters.manga_id
	WHERE mangas.manga_hentai = 0
		AND chapters.chapter_deleted = 0
		AND mangas.manga_id IN ($manga_ids_in)
	GROUP BY chapters.manga_id
	ORDER BY chapters.chapter_views DESC
	", $array_of_featured_manga_ids , 'fetchAll', PDO::FETCH_ASSOC, 3600);
}

//update new manga
$memcached->delete('new_manga');
$new_manga = $sql->query_read('new_manga', "
	SELECT mangas.manga_id, mangas.manga_name, mangas.manga_image, mangas.manga_hentai, chapters.chapter_id, chapters.chapter_views, chapters.chapter, chapters.upload_timestamp 
	FROM mangadex_mangas AS mangas
	LEFT JOIN mangadex_chapters AS chapters
		ON mangas.manga_id = chapters.manga_id
	WHERE mangas.manga_hentai = 0 AND chapters.chapter_id IS NOT NULL 
	GROUP BY mangas.manga_id
	ORDER BY mangas.manga_id DESC LIMIT 10
	", 'fetchAll', PDO::FETCH_ASSOC, 3600);

//update top follows
$memcached->delete('top_follows');
$top_follows = $sql->query_read('top_follows', "
	SELECT mangas.manga_id, mangas.manga_image, mangas.manga_name, mangas.manga_hentai, mangas.manga_bayesian,
		(SELECT count(*) FROM mangadex_manga_ratings WHERE mangadex_manga_ratings.manga_id = mangas.manga_id) AS count_pop,
		(SELECT count(*) FROM mangadex_follow_user_manga WHERE mangadex_follow_user_manga.manga_id = mangas.manga_id) AS count_follows
	FROM mangadex_mangas AS mangas 
	WHERE mangas.manga_hentai = 0 
	ORDER BY count_follows DESC LIMIT 10 
	", 'fetchAll', PDO::FETCH_ASSOC, 3600);

//update top rating
$memcached->delete('top_rating');
$top_rating = $sql->query_read('top_rating', "
	SELECT mangas.manga_id, mangas.manga_image, mangas.manga_name, mangas.manga_hentai, mangas.manga_bayesian,
		(SELECT count(*) FROM mangadex_manga_ratings WHERE mangadex_manga_ratings.manga_id = mangas.manga_id) AS count_pop,
		(SELECT count(*) FROM mangadex_follow_user_manga WHERE mangadex_follow_user_manga.manga_id = mangas.manga_id) AS count_follows
	FROM mangadex_mangas AS mangas 
	WHERE mangas.manga_hentai = 0
	ORDER BY manga_bayesian DESC LIMIT 10 
	", 'fetchAll', PDO::FETCH_ASSOC, 3600);
	
//process logs
$last_timestamp = $sql->query_read('last_timestamp', " SELECT visit_timestamp FROM mangadex_logs_visits ORDER BY visit_timestamp ASC LIMIT 1 ", 'fetchColumn', '', -1) + 3600;
for($i = $last_timestamp; $i < ($last_timestamp + 3600); $i+=3600) {
	$views_guests = $sql->query_read('views_guests', " SELECT count(*) FROM mangadex_logs_visits WHERE visit_timestamp >= ($i - 3600) AND visit_timestamp < $i AND visit_user_id = 0 ", 'fetchColumn', '', -1);
	$views_logged_in = $sql->query_read('views_logged_in', " SELECT count(*) FROM mangadex_logs_visits WHERE visit_timestamp >= ($i - 3600) AND visit_timestamp < $i AND visit_user_id > 0 ", 'fetchColumn', '', -1);

	$users_guests = $sql->query_read('users_guests', " SELECT COUNT(*)  FROM (SELECT `visit_user_id` FROM `mangadex_logs_visits` WHERE visit_timestamp >= ($i - 3600) AND visit_timestamp < $i AND visit_user_id = 0 GROUP BY `visit_ip`) AS `TABLE` ", 'fetchColumn', '', -1);
	$users_logged_in = $sql->query_read('users_logged_in', " SELECT COUNT(*)  FROM (SELECT `visit_user_id` FROM `mangadex_logs_visits` WHERE visit_timestamp >= ($i - 3600) AND visit_timestamp < $i AND visit_user_id > 0 GROUP BY `visit_user_id`) AS `TABLE` ", 'fetchColumn', '', -1);

	$sql->modify('insert', ' INSERT INTO `mangadex_logs_visits_summary` (`id`, `timestamp`, `users_guests`, `users_logged_in`, `views_guests`, `views_logged_in`) VALUES (NULL, ?, ?, ?, ?, ?) ', [$i, $users_guests, $users_logged_in, $views_guests, $views_logged_in]);
	$sql->modify('delete', ' DELETE FROM `mangadex_logs_visits` WHERE visit_timestamp >= (? - 3600) AND visit_timestamp < ? ', [$i, $i]);
	$sql->modify('delete', ' DELETE FROM `mangadex_logs_api` WHERE visit_timestamp >= (? - 3600) AND visit_timestamp < ? ', [$i, $i]);
}

// Prune old chapter_history data
$cutoff = time() - (60 * 60 * 24 * 90); // 90 days
$sql->modify('prune_manga_history', 'DELETE FROM mangadex_manga_history WHERE `timestamp` < ?', [$cutoff]);

// Prune expired ip bans
$sql->modify('prune_ip_bans', 'DELETE FROM mangadex_ip_bans WHERE expires < UNIX_TIMESTAMP()', []);

// Prune expired sessions each month on the 1st
if (date('j') == 1 && date('G') < 1) {
	$sql->modify('prune_sessions', 'DELETE FROM mangadex_sessions WHERE (created + ?) < UNIX_TIMESTAMP()', [60*60*24*365]);
}

//prune old chapter_live_views for trending data
$sql->modify('prune_trending', 'DELETE FROM `mangadex_chapter_live_views` WHERE (timestamp + ?) < UNIX_TIMESTAMP()', [60*60*25]);