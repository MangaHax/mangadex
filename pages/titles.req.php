<?php
$search = [];

if ($hentai_toggle == 0)
	$search['manga_hentai'] = 0;
elseif ($hentai_toggle == 2) 
	$search['manga_hentai'] = 1;

if (isset($_GET['genres_inc']) && !empty($_GET['genres_inc'])) 
	$search['manga_genres_inc'] = $_GET['genres_inc']; //manga_id	
	
if (isset($_GET['genres_exc']) && !empty($_GET['genres_exc'])) 
	$search['manga_genres_exc'] = $_GET['genres_exc']; //manga_id	

if (isset($_GET['title']) && !empty($_GET['title'])) 
	$search['manga_name'] = trim(htmlentities(str_replace('%', '\%', $_GET['title']))); //title	

if (isset($_GET['author']) && !empty($_GET['author'])) 
	$search['manga_author'] = $_GET['author']; //author	

if (isset($_GET['artist']) && !empty($_GET['artist'])) 
	$search['manga_artist'] = $_GET['artist']; //author	

if (isset($_GET['demo']) && !empty($_GET['demo'])) 
	$search['manga_demo_id'] = $_GET['demo']; //author	

if (isset($_GET['source_lang']) && !empty($_GET['source_lang'])) 
	$search['manga_lang_id'] = $_GET['source_lang']; //manga_lang_id	

if (isset($array_of_manga_ids))
	$search['manga_ids_array'] = $array_of_manga_ids;

//multi_lang
if ($user->user_id && $user->default_lang_ids)
	$search['multi_lang_id'] = explode(',', $user->default_lang_ids);
elseif (!$user->user_id && $filter_langs_cookie)
	$search['multi_lang_id'] = explode(',', urldecode($filter_langs_cookie));

if (isset($user, $user->excluded_genres) && !empty($user->excluded_genres)) {
    $search['manga_genres_exc'] = $user->excluded_genres;
}

$sort = (int)(isset($_GET['s']) && $_GET['s'] > 0 && $_GET['s'] < count(SORT_ARRAY_MANGA)) ? $_GET['s'] : 0;
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
    $mangas = (object)['num_rows' => 0];
    $mangas_obj = [];
}

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
		$string = (isset($search['manga_name']) ? '&title=' . $search['manga_name'] : '')
			. (isset($search['manga_demo_id']) ? '&demo=' . $search['manga_demo_id'] : '')
			. (isset($search['manga_author']) ? '&author=' . $search['manga_author'] : '')
			. (isset($search['manga_artist']) ? '&artist=' . $search['manga_artist'] : '')
			. (isset($search['manga_lang_id']) ? '&source_lang=' . $search['manga_lang_id'] : '')
			. (isset($search['manga_genres_inc']) ? '&genres_inc=' . $search['manga_genres_inc'] : '')
			. (isset($search['manga_genres_exc']) ? '&genres_exc=' . $search['manga_genres_exc'] : '');
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
    'base_url' => $string,
    'show_tabs' => true,
    'parser' => $parser,
];

if ($mangas->num_rows < 1) {
    $page_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => "There are no titles found with your search criteria. You can add the title <a href='/manga_new/" . ($search['manga_name'] ?? '' ) . "'>here</a>."]);
} else {
    $page_html = parse_template('manga/manga_list', $templateVars);
}
