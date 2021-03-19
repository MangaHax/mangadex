<?php

if (PHP_SAPI !== 'cli') {
    die();
}

echo "START @ ".date("F j, Y, g:i a")."\n";

require_once (__DIR__.'/../bootstrap.php');

require_once (ABSPATH . "/scripts/header.req.php");

echo "latest_manga_comments ...\n";
$memcached->delete("latest_manga_comments");
$latest_manga_comments = $sql->prep('latest_manga_comments', "
	SELECT /*+ MAX_EXECUTION_TIME(600000) */ posts.post_id, posts.text, posts.timestamp, posts.thread_id, mangas.manga_name, mangas.manga_id,
		(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts 
			WHERE mangadex_forum_posts.post_id <= posts.post_id 
			AND mangadex_forum_posts.thread_id = posts.thread_id
			AND mangadex_forum_posts.deleted = 0) AS thread_page
	FROM mangadex_forum_posts AS posts
	LEFT JOIN mangadex_threads AS threads
		ON posts.thread_id = threads.thread_id
	LEFT JOIN mangadex_mangas AS mangas
		ON threads.thread_name = mangas.manga_id
	WHERE threads.forum_id = 11 AND threads.thread_deleted = 0
	ORDER BY timestamp DESC LIMIT 10	
	", [], 'fetchAll', PDO::FETCH_ASSOC, 600, true);

echo "latest_forum_posts ...\n";
$memcached->delete("latest_forum_posts");
$latest_forum_posts = $sql->prep('latest_forum_posts', "
	SELECT /*+ MAX_EXECUTION_TIME(600000) */ posts.post_id, posts.text, posts.timestamp, posts.thread_id, threads.thread_name, forums.forum_name,
		(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts 
			WHERE mangadex_forum_posts.post_id <= posts.post_id 
			AND mangadex_forum_posts.thread_id = posts.thread_id
			AND mangadex_forum_posts.deleted = 0) AS thread_page
	FROM mangadex_forum_posts AS posts
	LEFT JOIN mangadex_threads AS threads
		ON posts.thread_id = threads.thread_id
	LEFT JOIN mangadex_forums AS forums
		ON threads.forum_id = forums.forum_id
	WHERE threads.forum_id NOT IN (11, 12, 14, 17, 18, 20) AND threads.thread_deleted = 0
	ORDER BY timestamp DESC LIMIT 10	
	", [], 'fetchAll', PDO::FETCH_ASSOC, 600, true);

echo "latest_news_posts ...\n";
$memcached->delete("latest_news_posts");
$latest_forum_posts = $sql->prep('latest_news_posts', "
	SELECT /*+ MAX_EXECUTION_TIME(600000) */ posts.post_id, posts.text, posts.timestamp, posts.thread_id, threads.thread_name, forums.forum_name,
		(SELECT (count(*) -1) DIV 20 + 1 FROM mangadex_forum_posts 
			WHERE mangadex_forum_posts.post_id <= posts.post_id 
			AND mangadex_forum_posts.thread_id = posts.thread_id
			AND mangadex_forum_posts.deleted = 0) AS thread_page
	FROM mangadex_forum_posts AS posts
	LEFT JOIN mangadex_threads AS threads
		ON posts.thread_id = threads.thread_id
	LEFT JOIN mangadex_forums AS forums
		ON threads.forum_id = forums.forum_id
	WHERE threads.forum_id = 26 AND threads.thread_sticky = 1
	ORDER BY timestamp ASC LIMIT 1
	", [], 'fetchAll', PDO::FETCH_ASSOC, 600, true);
	
///
/// Put delayed chapters that just expired into the last_updated table
///

echo "expired_delayed_chapters ... \n";
// Collect all chapters that have been uploaded as delayed, but where the delay is expired
$expired_delayed_chapters = $sql->prep('expired_delayed_chapters', '
SELECT /*+ MAX_EXECUTION_TIME(600000) */ c.chapter_id, c.manga_id, c.volume, c.chapter, c.title, c.upload_timestamp, c.user_id, c.lang_id, c.group_id, c.group_id_2, c.group_id_3, c.available FROM mangadex_chapters c, mangadex_delayed_chapters d WHERE d.upload_timestamp < UNIX_TIMESTAMP() AND c.chapter_id = d.chapter_id
	', [], 'fetchAll', PDO::FETCH_ASSOC, -1, true);
// Only process if we found any
if (!empty($expired_delayed_chapters)) {
    // Collect all chapter ids in this array, so we can unset them as delayed after this
    $unexpire_chapter_ids = [];
    foreach ($expired_delayed_chapters AS $expired_delayed_chapter) {
        $unexpire_chapter_ids[] = (int)$expired_delayed_chapter['chapter_id'];
        // Add each chapter to the last updated table. this query should be identical with the trigger inside the mangadex_chapters table
        $sql->modify('last_updated_delayed', "
INSERT INTO mangadex_last_updated (`chapter_id`, `manga_id`, `volume`, `chapter`, `title`, `upload_timestamp`, `user_id`, `lang_id`, `group_id`, `group_id_2`, `group_id_3`, `available`)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE chapter_id = ?, volume = ?, chapter = ?, title = ?, upload_timestamp = ?, user_id = ?,
    group_id = ?, group_id_2 = ?, group_id_3 = ?, available = ?;", array_merge(array_values($expired_delayed_chapter), [
            $expired_delayed_chapter['chapter_id'],
            $expired_delayed_chapter['volume'],
            $expired_delayed_chapter['chapter'],
            $expired_delayed_chapter['title'],
            $expired_delayed_chapter['upload_timestamp'],
            $expired_delayed_chapter['user_id'],
            $expired_delayed_chapter['group_id'],
            $expired_delayed_chapter['group_id_2'],
            $expired_delayed_chapter['group_id_3'],
            $expired_delayed_chapter['available'],
        ]));
        // Update manga last updated
        $sql->modify('unexpire_delayed_chapters', " UPDATE mangadex_mangas SET manga_last_uploaded = ? WHERE manga_id = ? LIMIT 1 ", [$expired_delayed_chapter['upload_timestamp'], $expired_delayed_chapter['manga_id']]);
    }
    // Set each is_delayed field for all chapters we just transferred to zero, so we dont process them again.
    $unexpire_in = implode(',', $unexpire_chapter_ids);
    $sql->modify('unexpire_delayed_chapters', 'DELETE FROM mangadex_delayed_chapters WHERE chapter_id IN ('.$unexpire_in.')', []);
}

echo "mdAtHomeClient->getStatus(); ... ";
try {
    $stats = $mdAtHomeClient->getStatus();

    foreach ($stats as $client) {
        $client_id = (int) $client['client_id'];
        $user_id = (int) $client['user_id'];
        $subsubdomain = substr($client['url'], 8, 27);
        if (strpos($subsubdomain, '.') === 0)
            $subsubdomain = '*.' . substr($client['url'], 9, 13);
        $url = explode(':', $client['url']);
        $port = (int) $url[2];
        $client_ip = $client['ip'];
        $available = (int) $client['available'];
        $shard_count = (int) $client['shard_count'];
        $speed = ((int) $client['speed']) / 125000;
        $images_served = (int) $client['images_served'];
        $images_failed = (int) $client['images_failed'];
        $bytes_served = (int) $client['bytes_served'];
        $sql->modify('x', " update mangadex_clients set upload_speed = ?, client_ip = ?, client_subsubdomain = ?, client_port = ?, client_available = ?, shard_count = ?, images_served = ?, images_failed = ?, bytes_served = ?, update_timestamp = UNIX_TIMESTAMP() WHERE client_id = ? AND user_id = ? LIMIT 1 ", [$speed, $client_ip, $subsubdomain, $port, $available, $shard_count, $images_served, $images_failed, $bytes_served, $client_id, $user_id]);
    }

    echo "OK\n";
} catch (\Throwable $t) {
    echo "FAIL: ".$t->getMessage()."\n";
}

echo "END @ ".date("F j, Y, g:i a")."\n";
