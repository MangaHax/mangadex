<?php

///
/// Search header
///

$selected_tags = $_GET['tags'] ?? $_POST['tags'] ?? '';
if ($selected_tags) {
    $selected_tags = explode(',', $selected_tags);
    $selected_tags_inc = array_filter($selected_tags, function($n) { return is_numeric($n) && $n >= 0; });
    $selected_tags_exc = array_filter($selected_tags, function($n) { return is_numeric($n) && $n < 0; });
    $selected_tags_exc = array_map(function($n) { return $n * -1; }, $selected_tags_exc);
} else {
    $selected_tags_inc = $_GET['tags_inc'] ?? $_POST['tags_inc'] ?? [];
    $selected_tags_exc = $_GET['tags_exc'] ?? $_POST['tags_exc'] ?? [];
    $selected_tags = array_merge($selected_tags_inc, array_map(function($n) { return $n * -1; }, $selected_tags_exc));
}
sort($selected_tags);

$tag_mode_inc = $_GET['tag_mode_inc'] ?? $_POST['tag_mode_inc'] ?? 'all';
$tag_mode_exc = $_GET['tag_mode_exc'] ?? $_POST['tag_mode_exc'] ?? 'any';

function getNumericArray($get, $fallback) {
    $values = $_GET[$get] ?? $_POST[$get] ?? '';
    if ($values) {
        $values = explode(',', $values);
    } else {
        $values = $_GET[$fallback] ?? $_POST[$fallback] ?? [];
    }
    return array_filter($values, function($n) { return is_numeric($n) && $n >= 0; });
}

$demos = getNumericArray('demos', 'demo_id');
$statuses = getNumericArray('statuses', 'status_id');

$templateVars = [
    'tags_inc' => $selected_tags_inc,
    'tags_exc' => $selected_tags_exc,
    'tag_mode_inc' => $tag_mode_inc,
    'tag_mode_exc' => $tag_mode_exc,
    'title_mode' => $title_mode,
    'demos' => $demos,
    'statuses' => $statuses,
    'user' => $user,
];

$page_html = parse_template('partials/search_header', $templateVars);

///
/// Manga List
///

$search = [];

if ($hentai_toggle == 0)
    $search['manga_hentai'] = 0;
elseif ($hentai_toggle == 2)
    $search['manga_hentai'] = 1;

if (isset($_GET['title']) && !empty($_GET['title']))
    $search['manga_name'] = trim(str_replace('_', '\_', str_replace('%', '\%', $_GET['title']))); //title

if (isset($_GET['author']) && !empty($_GET['author']))
    $search['manga_author'] = $_GET['author'];

if (isset($_GET['artist']) && !empty($_GET['artist']))
    $search['manga_artist'] = $_GET['artist'];

//if (isset($_GET['demo_id']) && !empty($_GET['demo_id']))
//    $search['manga_demo_id'] = $_GET['demo_id'];

if (isset($_GET['lang_id']) && !empty($_GET['lang_id']))
    $search['manga_lang_id'] = $_GET['lang_id'];

//if (isset($_GET['status_id']) && !empty($_GET['status_id']))
//    $search['manga_status_id'] = $_GET['status_id'];

$search['manga_genres_inc'] = $selected_tags_inc;
$search['manga_genres_exc'] = $selected_tags_exc;
$search['tag_mode_inc'] = $tag_mode_inc;
$search['tag_mode_exc'] = $tag_mode_exc;
$search['demos'] = $demos;
$search['statuses'] = $statuses;

if (isset($array_of_manga_ids))
    $search['manga_ids_array'] = $array_of_manga_ids;

//multi_lang
if ($user->user_id && $user->default_lang_ids)
    $search['multi_lang_id'] = explode(',', $user->default_lang_ids);
elseif (!$user->user_id && $filter_langs_cookie)
    $search['multi_lang_id'] = explode(',', urldecode($filter_langs_cookie));

$sort = (isset($_GET['s']) && $_GET['s'] > 0 && $_GET['s'] < count(SORT_ARRAY_MANGA)) ? $_GET['s'] : 0;
$order = SORT_ARRAY_MANGA[$sort];
$current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1;

if ($title_mode == 1)
    $limit = 50;
elseif ($title_mode == 2)
    $limit = 100;
elseif ($title_mode == 3)
    $limit = 104;
else
    $limit = 40;

try {
    // TODO: This is a bit awkward, a proper rework of this section should fix this
    $mangas = new Mangas($search);
    $mangas_obj = $mangas->query_read($order, $limit, $current_page);
}
catch (\PDOException $e) {
    var_dump($e); die;
    $mangas = (object)['num_rows' => 0];
    $mangas_obj = [];
}

$follow_types = new Follow_Types();
$followed_manga_ids_array = $user->get_followed_manga_ids_key_pair();

$user_manga_ratings_array = $user->get_manga_ratings();

$paging = pagination($mangas->num_rows, $current_page, $limit, $sort);

switch ($page) {
    case 'group':
        $string = "/$id/" . slugify($group->group_name) . "/$mode";
        break;

    case 'user':
        $string = "/$id/" . strtolower($uploader->username) . "/$mode";
        break;

    case 'follows':
        $string = "/manga/$list_type";
        break;

    case 'list':
        $string = "/$list_user_id/$list_type";
        break;

    case 'search':
        $data = array_filter([
            'author' => $search['manga_author'] ?? '',
            'artist' => $search['manga_artist'] ?? '',
            'demos' => implode(',', $demos),
            'lang_id' => $search['manga_lang_id'] ?? '',
            'statuses' => implode(',', $statuses),
            'tag_mode_inc' => $tag_mode_inc,
            'tag_mode_exc' => $tag_mode_exc,
            'tags' => implode(',', $selected_tags),
            'title' => $search['manga_name'] ?? '',
        ], function($n) { return $n; });
        $string = http_build_query($data);
        if ($string !== '') {
            $string = '&'.$string;
        }
        break;

    default:
        $string = '';
        break;
}

/** Template vars:
 * manga: array of chapter
 * manga_count: number of chapters to display
 * current_page: page starting at 1
 * limit: number of entries per page
 * page: the current page name, http://mangadex.org/manga/... => 'manga'
 * user: the currently logged in user
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
    'sort' => $sort,
    'title_mode' => $title_mode,
    'search' => $search,
    'base_url' => htmlspecialchars($string, ENT_QUOTES),
    'show_tabs' => false,
    'parser' => $parser,
];

if ($mangas->num_rows < 1) {
    $page_html .= parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => "There are no titles found with your search criteria. You can add the title <a href='/manga_new/" . ($search['manga_name'] ?? '' ) . "'>here</a>."]);
} else {
    $page_html .= parse_template('manga/manga_list', $templateVars);
}
