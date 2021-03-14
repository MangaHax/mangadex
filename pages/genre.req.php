<?php
if (isset($_GET['id'])) {
	$genre_id = prepare_numeric($_GET['id']);
	
	$genre = new Genre($genre_id);	
}
else {
	$genre = $sql->query_read("genre_random", " SELECT * FROM mangadex_genres ORDER BY RAND() LIMIT 1 ", 'fetch', PDO::FETCH_OBJ, 10);

	$genre_id = $genre->genre_id;
}

if (!isset($genre->genre_id)) {
	$page_html = parse_template('partials/alert', ['type' => 'danger', 'strong' => 'Warning', 'text' => "Genre #$genre_id does not exist."]);
}
else {	
	$search = [];

	if ($hentai_toggle == 0)
		$search['manga_hentai'] = 0;
	elseif ($hentai_toggle == 2)
		$search['manga_hentai'] = 1;

	$search['manga_genres_inc'] = $genre->genre_id; //genre_id

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
	} catch (\PDOException $e) {
		$mangas = (object)['num_rows' => 0];
		$mangas_obj = [];
	}
	
	$string = "/$genre_id/" . slugify($genre->genre_name);

	$follow_types = new Follow_Types();
	$followed_manga_ids_array = $user->get_followed_manga_ids_key_pair();

	$user_manga_ratings_array = $user->get_manga_ratings();

	$paging = pagination($mangas->num_rows, $current_page, $limit, $sort);

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
		'show_tabs' => true,
		'parser' => $parser,
		'base_url' => $string,
	];

	$titles_html = parse_template('manga/manga_list', $templateVars);

	$templateVars = [
        'page' => $page,
        'genre' => $genre,
        'user' => $user,
        'parser' => $parser,
		'titles_html' => $titles_html,
    ];
	
	$page_html = parse_template('manga/genre', $templateVars);
	
}