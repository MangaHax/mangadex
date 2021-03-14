<?php 
$search = [];

if ($hentai_toggle == 0)
	$search['manga_hentai'] = 0;
elseif ($hentai_toggle == 2) 
	$search['manga_hentai'] = 1;

//multi_lang
if ($user->user_id && $user->default_lang_ids)
	$search['multi_lang_id'] = $user->default_lang_ids;
elseif (!$user->user_id && $filter_langs_cookie)
	$search['multi_lang_id'] = urldecode($filter_langs_cookie);

$timestamp_rounded = floor($timestamp / 120) * 120;
	
$search['upload_timestamp'] = 60 * 60 * 24 * 7;
$search['exclude_delayed'] = 1;

$search['chapter_deleted'] = 0;

$blocked_groups = $user->get_blocked_groups();
if ($blocked_groups)
	$search['blocked_groups'] = array_keys($blocked_groups);
			
$order = 'upload_timestamp DESC';
$limit = 50;
$current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1;

if (isset($user, $user->excluded_genres) && !empty($user->excluded_genres)) {
    $search['excluded_genres'] = $user->excluded_genres;
}

try {
    $chapters = new Chapters($search);
    $chapters_list = $chapters->query_read($order, 10000, 1);
} catch (\PDOException $e) {
    // TODO: This is a bit awkward, a proper rework of this section should fix this
    $chapters = (object)['num_rows' => 0];
    $chapters_list = [];
}

$templateVars = [
    'chapters' => $chapters_list,
    'order' => $order,
    'limit' => $limit,
    'current_page' => $current_page,
    'timestamp_rounded' => $timestamp_rounded,
    'user' => $user,
    'page' => $page,
];

if (empty($chapters_list)) {
    $page_html = parse_template('partials/alert', ['type' => 'info', 'strong' => 'Notice', 'text' => 'There are no more updates from the past week.']);
} else {
    $page_html = parse_template('chapter/last_updated', $templateVars);
}
