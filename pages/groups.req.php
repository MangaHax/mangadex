<?php

$search = [];

//if ($lang_id) 
//	$search['group_lang_id'] = $lang_id; //lang_id

if (isset($_GET['group_name']) && !empty($_GET['group_name'])) 
	$search['group_name'] = trim(htmlentities(str_replace('%', '\%', $_GET['group_name']))); //group_name	
else {
	$search['group_name'] = '';
	$_GET['group_name'] = '';
}

if (isset($array_of_group_ids))
	$search["group_ids_array"] = $array_of_group_ids;

$sort = (isset($_GET["s"]) && $_GET["s"] > 0 && $_GET["s"] < count(SORT_ARRAY_GROUPS)) ? $_GET["s"] : 0;
$order = SORT_ARRAY_GROUPS[$sort];
$limit = $limit ?? 100;
$current_page = (isset($_GET["p"]) && $_GET["p"] > 0) ? $_GET["p"] : 1;

$groups = new Groups($search);
$groups_obj = $groups->query_read($order, $limit, $current_page);

$templateVars = [
    'groups' => $groups_obj,
    'group_count' => $groups->num_rows,
    'page' => $page,
    'current_page' => $current_page,
    'sort' => $sort,
    'order' => $order,
    'search' => $search,
    'limit' => $limit,
    'user' => $user,
];

$page_html = '';

if ($page == "groups") {
    $page_html .= parse_template('group/partials/group_navtabs');
}

if ($groups->num_rows < 1) {
    $page_html .= parse_template('partials/alert', ['type' => 'info', 'strong' => 'Notice', 'text' => 'No groups found.']);
} else {
    $page_html .= parse_template('group/group_list', $templateVars);
}


