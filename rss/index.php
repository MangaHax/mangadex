<?php
require_once ('../bootstrap.php');

require_once (ABSPATH . "/scripts/header.req.php");

$api_key = $_GET["code"] ?? '';

// This is the old method of fetching the user. since this doesnt use any session related stuff, we can keep it here.
$user__id = $sql->prep("api_key_$api_key", ' SELECT user_id FROM mangadex_users WHERE activation_key = ? LIMIT 1 ', [$api_key], 'fetchColumn', '', 3600);
$user = new User($user__id, 'user_id');

if (!$user->user_id) {
    http_response_code(401);
	exit('Invalid RSS key. Due to the volume of bots flooding the server with RSS requests, now you need to sign up to use RSS.');
}
else {
	header('Content-Type: application/rss+xml; charset=UTF-8');


	if (isset($_GET["follows"]))
		$search["manga_ids_array"] = array_keys($user->get_followed_manga_ids_key_pair(), 1);

	if (isset($_GET["manga_id"]) && $_GET["manga_id"] > 0) 
		$search["manga_id"] = $_GET["manga_id"]; //manga_id

	if (isset($_GET["user_id"]) && $_GET["user_id"] > 0) 
		$search["user_id"] = $_GET["user_id"]; //user_id

	if (isset($_GET["group_id"]) && $_GET["group_id"] > 0) 
		$search["group_id"] = $_GET["group_id"]; //group_id

	if ($user->default_lang_ids)
		$search["multi_lang_id"] = $user->default_lang_ids;
	
	if (!$user->show_unavailable) {
		$search["available"] = 1;
	}
	
	if (isset($_GET["h"]) && $_GET["h"] == 0)
		$search["manga_hentai"] = 0;
	elseif (isset($_GET["h"]) && $_GET["h"] == 2) 
		$search["manga_hentai"] = 1;

	$search['exclude_delayed'] = 1;
	$search["chapter_deleted"] = 0;
	
	$blocked_groups = $user->get_blocked_groups();
	if ($blocked_groups)
		$search['blocked_groups'] = array_keys($blocked_groups);

	$limit = 100;
	$current_page = 1;
	$order = "upload_timestamp DESC";

	try {
        $chapters = new Chapters($search);
        $chapters_obj = $chapters->query_read($order, $limit, $current_page);
    } catch (\PDOException $e) {
	    $chapters = (object)['num_rows' => 0];
	    $chapters_obj = [];
    }

	//visit_log_cumulative($ip, $table = "rss");
	?>
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="<?= URL ?>rss/<?= $user->activation_key ?>" rel="self" type="application/rss+xml" />
<title>MangaDex RSS</title>
<link><?= URL ?></link>
<description>The latest MangaDex releases</description>
<language>en</language>
<ttl>30</ttl>

<?php foreach ($chapters_obj as $chapter) {
    $chapter = (object) $chapter; ?>
<item>
	<title><?= str_replace(['&','<', '>'], '', html_entity_decode($chapter->manga_name)) ?> - <?= ($chapter->volume == "" || $chapter->volume == 0) ? "" : "Volume $chapter->volume, " ?><?= ($chapter->chapter != "") ? "Chapter $chapter->chapter" : "" ?><?= (empty($chapter->volume) && empty($chapter->chapter)) ? str_replace(['&','<', '>'], '', html_entity_decode($chapter->title)) : ""?></title>
	<link><?= URL ?>chapter/<?= $chapter->chapter_id ?></link>
	<mangaLink><?= URL ?>title/<?= $chapter->manga_id ?></mangaLink>
	<pubDate><?= gmdate('r', $chapter->upload_timestamp) ?></pubDate>
	<description>Group: <?= str_replace(['&','<', '>'], '', html_entity_decode($chapter->group_name)) ?><?= $chapter->group_name_2 ? " | " . str_replace(['&','<', '>'], '', html_entity_decode($chapter->group_name_2)) : "" ?><?= $chapter->group_name_3 ? " | " . str_replace(['&','<', '>'], '', html_entity_decode($chapter->group_name_3)) : "" ?> - Uploader: <?= $chapter->username ?> - Language: <?= $chapter->lang_name ?></description>
	<guid isPermaLink="true"><?= URL ?>chapter/<?= $chapter->chapter_id ?></guid>
</item>
<?php } ?>


</channel>
</rss>
<?php } ?>
