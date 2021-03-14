<?php
$thread_id = $_GET['id'] ?? 1;
	
$limit = 20;
$current_page = (isset($_GET["p"]) && $_GET["p"] > 0) ? $_GET["p"] : 1;

$thread = new Forum_Posts($thread_id);

if ($thread->forum_id && validate_level($user, $thread->view_level)) {
	$posts_obj = $thread->query_read($limit, $current_page);

	// Get a list of [user_id => username] the current user has blocked. key is the userid, value is the username
    $blockedUserIds = array_map(function($e) {return $e['username'] ?? 'user';}, $user->get_blocked_user_ids());

	update_views_v2($page, $thread_id, $ip);

	$templateVars = [
        'thread' => $thread,
        'post_list' => $posts_obj,
        'post_count' => $thread->num_rows,
        'user' => $user,
        'page' => $page,
        'blocked_user_ids' => $blockedUserIds,
        'poll_items' => $thread->get_poll_items(),
        'user_vote' => $thread->get_user_vote($user->user_id),
        'total_votes' => $thread->get_poll_total_votes(),
        'breadcrumbs' => $thread->get_breadcrumb(),
        'current_page' => $current_page,
        'limit' => $limit,
        'parser' => $parser,
        'post_history_modal_html' => parse_template('partials/post_history_modal', [ 'user' => $user ])
    ];

	$page_html = parse_template('forum/thread', $templateVars);

} else {
    $page_html = parse_template('partials/alert', ['type' => 'warning mt-3', 'strong' => 'Warning', 'text' => 'This thread has either been deleted or does not exist or you don\'t have permission to view it.']);
}
