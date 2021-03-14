<?php

$term = $_GET['term'] ?? '';
$escapedTerm = trim(str_replace('+', ' ', str_replace('_', '\_', str_replace('%', '\%', $term))));
$limit = 25;

///
/// Manga tab
///

$search = [];

$user_manga_ratings_array = $user->get_manga_ratings();

$search['manga_name'] = $escapedTerm; //title

if ($hentai_toggle == 0)
    $search['manga_hentai'] = 0;
elseif ($hentai_toggle == 2)
    $search['manga_hentai'] = 1;

if (isset($user, $user->excluded_genres) && !empty($user->excluded_genres)) {
    $search['manga_genres_exc'] = $user->excluded_genres;
}

$sort = (int)(isset($_GET['s']) && $_GET['s'] > 0 && $_GET['s'] < count(SORT_ARRAY_MANGA)) ? $_GET['s'] : 0;
$order = SORT_ARRAY_MANGA[$sort];
$current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1;

$limit = 25;

try {
    // TODO: This is a bit awkward, a proper rework of this section should fix this
    $mangas = new Mangas($search);
    $mangas_obj = $mangas->query_read($order, $limit, $current_page);
}
catch (\PDOException $e) {
    $mangas = (object)['num_rows' => 0];
    $mangas_obj = [];
}

/** Template vars:
 * mangas: array of chapter
 * manga_count: number of chapters to display
 * current_page: page starting at 1
 * limit: number of entries per page
 * page: the current page name, http://mangadex.org/manga/... => 'manga'
 * user: the currently logged in user,
 * parser: the bbcode parser
 * sort
 * title_mode
 * list_type: (optional) if mode is follows, the user setting that controls the list format
 * uploader: (optional) if mode is user, the user object of the uploader
 */
$templateVars = [
    'mangas' => $mangas_obj,
    'manga_count' => $mangas->num_rows,
    'current_page' => $current_page,
    'limit' => $limit,
    'page' => $page,
    'user' => $user,
    'parser' => $parser,
    'sort' => $sort,
    'search' => $search,
    'base_url' => '',
    'title_mode' => 1
];

if ($mangas->num_rows > 0) {
    $manga_search_tab_html = parse_template('manga/manga_list', $templateVars);
} else {
    $manga_search_tab_html = parse_template('partials/alert', ['type' => 'info', 'strong' => 'Notice', 'text' => 'No manga found.']);
}

///
/// Group Tab
///

$search = [];

//if ($lang_id)
//	$search['group_lang_id'] = $lang_id; //lang_id

$search['group_name'] = $escapedTerm; //group_name

$sort = (isset($_GET["s"]) && $_GET["s"] > 0 && $_GET["s"] < count(SORT_ARRAY_GROUPS)) ? $_GET["s"] : 0;
$order = 'group_follows DESC, group_last_updated DESC';
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

/*if ($page == "groups") {
    $page_html .= parse_template('group/partials/group_navtabs');
}*/

if ($groups->num_rows < 1) {
    $group_search_tab_html = parse_template('partials/alert', ['type' => 'info', 'strong' => 'Notice', 'text' => 'No groups found.']);
} else {
    $group_search_tab_html = parse_template('group/group_list', $templateVars);
}

///
/// User Tab
///

$search = [];

$search['username'] = $escapedTerm;

$sort = (isset($_GET['s']) && $_GET['s'] > 0) ? $_GET['s'] : 0;
$order = 'level_id DESC, username ASC';
$current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1;

$users = new Users($search);
$users_obj = $users->query_read($order, $limit, $current_page);

/*if ($page === 'users') {

    $templateVars = ['search' => $search];

    $page_html .= parse_template('user/partials/user_list_searchbox', $templateVars);
}*/

$templateVars = [
    'search' => $search,
    'sort' => $sort,
    'limit' => $limit,
    'current_page' => $current_page,
    'page' => $page,
    'user_list' => $users_obj,
    'user_count' => $users->num_rows,
    'show_pagination' => false,
];

if ($users->num_rows < 1) {
    $user_search_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are no users found with your search criteria.']);
} else {
    $user_search_tab_html = parse_template('user/user_list', $templateVars);
}

///
/// Quick Search
///

$templateVars = [
    'manga_search_tab_html' => $manga_search_tab_html,
    'group_search_tab_html' => $group_search_tab_html,
    'user_search_tab_html' => $user_search_tab_html,
    'term' => urlencode($term),
];

$page_html = parse_template('quick_search', $templateVars);
