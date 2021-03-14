<?php
$list_user_id = $_GET['id'] ?? 0;
$p = $_GET['p'] ?? 1;
$list_type = $_GET['type'] ?? 0;
$_GET['s'] = $_GET['s'] ?? 2;

$list_user = new User($list_user_id, "user_id");
$friends = $list_user->get_friends_user_ids();

if ($list_user_id == $user->user_id || 
	validate_level($user, 'admin') || 
	(isset($friends[$user->user_id]) && $friends[$user->user_id]['accepted'] == 1 && $list_user->list_privacy == 2) ||
	$list_user->list_privacy == 1
	) {

    $list_user_followed_manga_ids_array = $list_user->get_followed_manga_ids_key_pair();
    $user_manga_ratings_array = $list_user->get_manga_ratings();

    if ($list_user_id != $user->user_id) {
        $list_user_manga_ratings_array = $list_user->get_manga_ratings();
    }
    else {
        // This must be null, so manga_list.tpl uses the correct rating button version
        $list_user_manga_ratings_array = null;
    }

    if (!$list_type)
        $array_of_manga_ids = array_keys($list_user_followed_manga_ids_array);
    else
        $array_of_manga_ids = array_keys($list_user_followed_manga_ids_array, $list_type);

    if (empty($array_of_manga_ids))
        $manga_list_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'Nothing here!']);
    else {

        if ($hentai_toggle == 0)
            $search['manga_hentai'] = 0;
        elseif ($hentai_toggle == 2)
            $search['manga_hentai'] = 1;

        $search['manga_ids_array'] = $array_of_manga_ids;

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
            //$mangas_obj = $mangas->query_read($order, $limit, $current_page);
            $mangas_obj = [];
            $mangas_ids = $mangas->getIds($order, $limit, $current_page);
            foreach ($mangas_ids AS $_id) {
                $mangas_obj[$_id] = new Manga($_id);
                $mangas_obj[$_id]->manga_last_updated = $mangas_obj[$_id]->manga_last_uploaded; // Fixes some inconsistency with manga_list.tpl
            }
        }
        catch (\PDOException $e) {
            $mangas = (object)['num_rows' => 0];
            $mangas_obj = [];
        }

        $templateVars = [
            'mangas' => $mangas_obj,
            'manga_ids' => $mangas_ids,
            'manga_count' => $mangas->num_rows,
            'current_page' => $current_page,
            'limit' => $limit,
            'page' => $page,
            'p' => $p,
            'user' => $user,
            'sort' => $sort,
            'title_mode' => $title_mode,
            'search' => $search,
            'base_url' => "/$list_user_id/$list_type",
            'show_tabs' => false,
            'parser' => $parser,
            'list_user' => $list_user,
            'list_user_followed_manga_ids_array' => $list_user_followed_manga_ids_array,
            'list_user_manga_ratings_array' => $list_user_manga_ratings_array,
        ];
        $manga_list_html = parse_template('manga/manga_list', $templateVars);

    }

	$follow_types = new Follow_Types();

	$templateVars = [
        'manga_list_html' => $manga_list_html,
        'list_user' => $list_user,
        'user' => $user,
        'theme_id' => $theme_id,
        'follow_types' => new Follow_Types(),
        'list_type' => $list_type,
        'title_mode' => $title_mode,

    ];

	$page_html = parse_template('user/list', $templateVars);

}
else {
    $page_html = parse_template('partials/alert', ['type' => 'info', 'strong' => 'Notice', 'text' => 'You do not have permission to view this user\'s list.']);
}
