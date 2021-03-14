<?php

$post_id = $_GET['id'] ?? 0;

$pagelimit = 20;

$res = $sql->prep('comment_permalink_'.$post_id, 'SELECT thread_id FROM mangadex_forum_posts WHERE post_id = ?', [$post_id], 'fetch', PDO::FETCH_ASSOC, 60);
if (!$res) {
    redirect_url('/404.html');
}

$thread_id = (int)$res['thread_id'];

// Count which position this post is
$res = $sql->prep('comment_permalink_pagesquery_'.$thread_id, 'SELECT COUNT(*)-1 as pos FROM `mangadex_forum_posts` WHERE thread_id = ? AND post_id <= ? AND deleted = 0', [$thread_id, $post_id], 'fetch', PDO::FETCH_ASSOC, 60);

$pos = $res['pos'];
$page = 1 + (int)floor($pos / (float)$pagelimit);

$url = sprintf('/thread/%d/%d/#post_%s', $thread_id, $page, $post_id);

//dump($url, $pos, $page, $pos / (float)$pagelimit);die();

redirect_url($url);
