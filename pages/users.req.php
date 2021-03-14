<?php
$search = [];

if (isset($_GET['username']) && !empty($_GET['username'])) 
	$search['username'] = trim($_GET['username']); 
else 
	$search['username'] = '';

$sort = (isset($_GET['s']) && $_GET['s'] > 0) ? $_GET['s'] : 0;
$order = SORT_ARRAY_USERS[$sort];
$limit = $limit ?? 100;
$current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1;

$users = new Users($search);
$users_obj = $users->query_read($order, $limit, $current_page);

$page_html = "";

if ($page === 'users') {

    $templateVars = ['search' => $search];

    $page_html .= parse_template('user/partials/user_list_searchbox', $templateVars);
}

$templateVars = [
    'search' => $search,
    'sort' => $sort,
    'limit' => $limit,
    'current_page' => $current_page,
    'page' => $page,
    'user_list' => $users_obj,
    'user_count' => $users->num_rows,
];

if ($users->num_rows < 1) {
    $page_html .= parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are no users found with your search criteria.']);
} else {
    $page_html .= parse_template('user/user_list', $templateVars);
}
