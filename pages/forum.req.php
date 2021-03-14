<?php
$forum_id = $_GET['id'] ?? 1;
$threads = new Forum_Threads($forum_id);

if (validate_level($user, $threads->view_level)) {
	$limit = 20;
	$current_page = (isset($_GET["p"]) && $_GET["p"] > 0) ? $_GET["p"] : 1;
	$threads_obj = $threads->query_read($limit, $current_page);

    $subforums = new Forums($forum_id);
    $subforums_obj = $subforums->query_read();

    $templateVars = [
        'forum_id' => $forum_id,
        'thread_list' => $threads_obj,
        'thread_count' => $threads->num_rows,
        'threads' => $threads,
        'subforum_list' => $subforums_obj,
        'subforum_count' => $subforums->num_rows,
        'current_page' => $current_page,
        'limit' => $limit,
        'breadcrumb' => $threads->get_breadcrumb(),
        'user' => $user,
        'page' => $page,
    ];

    $page_html = parse_template('forum/forum', $templateVars);
}
else {
    $page_html = parse_template('partials/alert', ['type' => 'info', 'strong' => 'Notice', 'text' => "You don't have permission to view this forum."]);
}
?>