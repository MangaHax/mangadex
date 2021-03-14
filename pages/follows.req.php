<?php
$mode = $_GET['mode'] ?? 'chapters';
$list_type = $_GET['list_type'] ?? 1;

$follow_types = new Follow_Types();

$follow_page_html = "";

switch ($mode) {
    case 'chapters':
        $followed_manga_ids_array = $user->get_followed_manga_ids_key_pair();

        if (!$list_type)
            $array_of_manga_ids = array_keys($followed_manga_ids_array);
        else
            $array_of_manga_ids = array_keys($followed_manga_ids_array, $list_type);

        if ($array_of_manga_ids) {
            $order = "upload_timestamp DESC";

            if ($user->default_lang_ids)
                $search["multi_lang_id"] = $user->default_lang_ids;

			$blocked_groups = $user->get_blocked_groups();
			if ($blocked_groups)
				$search['blocked_groups'] = array_keys($blocked_groups);

            if ($hentai_toggle == 0)
                $search['manga_hentai'] = 0;
            elseif ($hentai_toggle == 2)
                $search['manga_hentai'] = 1;

            $search["chapter_deleted"] = 0;

            $search['exclude_delayed'] = 1;

            $search['manga_ids_array'] = $array_of_manga_ids;

			if (!isset($user->show_unavailable) || !$user->show_unavailable)
				$search['available'] = 1;

            // TODO: Probably a lot of search vars missing from chapters.req.php

            $limit = 100;
            $current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1;

            try {
                $chapters = new Chapters($search);
                $chapters_obj = $chapters->query_read($order, $limit, $current_page);
            } catch (\PDOException $e) {
                $chapters = (object)['num_rows' => 0];
                $chapters_obj = [];
            }

            $templateVars = [
                'limit' => 100,
                'current_page' => (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1,
                'chapter_count' => $chapters->num_rows,
                'chapters' => $chapters_obj,
                'user' => $user,
                'page' => $page,
                'list_type' => $list_type,
            ];

            if (isset($mod_edit) && $mod_edit) {
                $page_html = parse_template('chapter/chapters_modedit', $templateVars);
            } else {
                $page_html = parse_template('chapter/chapters', $templateVars);
            }

            $follow_page_html = $page_html;
        }
        else
            $follow_page_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'Nothing here!']);

        break;

    case 'manga':
        $followed_manga_ids_array = $user->get_followed_manga_ids_key_pair();
        $user_manga_ratings_array = $user->get_manga_ratings();

        if (!$list_type)
            $array_of_manga_ids = array_keys($followed_manga_ids_array);
        else
            $array_of_manga_ids = array_keys($followed_manga_ids_array, $list_type);

        $search = [];

        if ($hentai_toggle == 0)
            $search['manga_hentai'] = 0;
        elseif ($hentai_toggle == 2)
            $search['manga_hentai'] = 1;

        if (isset($array_of_manga_ids))
            $search['manga_ids_array'] = $array_of_manga_ids;

        //multi_lang
        if ($user->user_id && $user->default_lang_ids)
            $search['multi_lang_id'] = explode(',', $user->default_lang_ids);
        elseif (!$user->user_id && $filter_langs_cookie)
            $search['multi_lang_id'] = explode(',', urldecode($filter_langs_cookie));

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
        } catch (\PDOException $e) {
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
            'show_tabs' => false,
            'parser' => $parser,
        ];

        if (empty($array_of_manga_ids)) {
            $page_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'Nothing here!']);
        } else {
            $page_html = parse_template('manga/manga_list', $templateVars);
        }

        $follow_page_html = $page_html;

        break;

    case 'groups':

        $search = [];

        $array_of_group_ids = $user->get_followed_group_ids();

        $search["group_ids_array"] = $array_of_group_ids;

        $sort = (isset($_GET["s"]) && $_GET["s"] > 0 && $_GET["s"] < count(SORT_ARRAY_GROUPS)) ? $_GET["s"] : 0;
        $order = SORT_ARRAY_GROUPS[$sort];
        $limit = $limit ?? 100;
        $current_page = (isset($_GET["p"]) && $_GET["p"] > 0) ? $_GET["p"] : 1;

        if ($array_of_group_ids) {
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

            $follow_page_html = parse_template('group/group_list', $templateVars);
        }
        else {
            $follow_page_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'You haven\'t followed any groups!']);
        }

        break;

    case 'import':
        $follow_page_html = parse_template('user/partials/follows_import');
        break;
}

////// Follows sub-page

$templateVars = [
    'follow_page_html' => $follow_page_html,
    'mode' => $mode,
    'list_type' => $list_type,
    'follow_types' => $follow_types,
    'user' => $user,
    'title_mode' => $title_mode,
];

$page_html = parse_template('user/follows', $templateVars);
