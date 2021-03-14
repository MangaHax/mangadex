<?php
$group_id = $_GET['id'] ?? 1;
$id = prepare_numeric($group_id);

$mode = $_GET['mode'] ?? 'chapters';

$group_delay_array = [0 => "None", 3600 => "1 hour", 7200 => "2 hours", 10800 => "3 hours", 14400 => "4 hours", 18000 => "5 hours", 
	21600 => "6 hours", 43200 => "12 hours", 86400 => "1 day", 172800 => "2 days", 259200 => "3 days", 345600 => "4 days", 432000 => "5 days",
	518400 => "6 days", 604800 => "1 week", 1209600 => "2 weeks"];
	
$group = new Group($id);

if (!isset($group->group_id)) {
    $page_html = parse_template('partials/alert', ['type' => 'danger', 'strong' => 'Warning', 'text' => "Group #$id does not exist."]);
} else {
    $group_members_array = $group->get_members();
	$blocked_user_ids_array = $group->get_blocked_users();
	$user_is_invited = $memcached->get("group_{$group->group_id}_invite_{$user->user_id}") == "pending";
	
	update_views_v2($page, $group->group_id, $ip);

	$group_tab_html = "";
	$post_history_modal_html = "";

    switch ($mode) {
        case 'chapters':

            if ($hentai_toggle == 0)
                $search['manga_hentai'] = 0;
            elseif ($hentai_toggle == 2)
                $search['manga_hentai'] = 1;
				
			if (!isset($user->show_unavailable) || !$user->show_unavailable)
				$search['available'] = 1;

            $search['manga_ids_array'] = $group->get_manga_ids();
            $search['group_id'] = $group->group_id;

            $order = 'upload_timestamp DESC';

            $limit = 100;
            $current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1;

            try {
                $chapters = new Chapters($search);
                $chapters_list = $chapters->query_read($order, $limit, $current_page);
            } catch (\PDOException $e) {
                // TODO: This is a bit awkward, a proper rework of this section should fix this
                $chapters = (object)['num_rows' => 0];
                $chapters_list = [];
            }

            $title_mode = ($user->user_id) ? $user->mangas_view : ($_COOKIE['mangadex_title_mode'] ?? 0);

            $templateVars = [
                'limit' => 100,
                'current_page' => (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1,
                'chapters' => $chapters_list,
                'chapter_count' => $chapters->num_rows,
                'user' => $user,
                'page' => $page,
                'mode' => $mode,
                'group' => $group,
                'title_mode' => $title_mode
            ];

            if ($chapters->num_rows < 1) {
                $group_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are no chapters in your selected language(s).']);
            } else {
                $group_tab_html = parse_template('chapter/chapters', $templateVars);
            }

            break;

        case 'mod_chapters':
            if (validate_level($user, 'gmod')) {

                $search['manga_ids_array'] = $group->get_manga_ids();
                $search['group_id'] = $group->group_id;

                $order = 'upload_timestamp DESC';

                $limit = 100;
                $current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1;

                try {
                    $chapters = new Chapters($search);
                    $chapters_list = $chapters->query_read($order, $limit, $current_page);
                } catch (\PDOException $e) {
                    // TODO: This is a bit awkward, a proper rework of this section should fix this
                    $chapters = (object)['num_rows' => 0];
                    $chapters_list = [];
                }

                $templateVars = [
                    'limit' => 100,
                    'current_page' => (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1,
                    'chapters' => $chapters_list,
                    'chapter_count' => $chapters->num_rows,
                    'user' => $user,
                    'page' => $page,
                    'mode' => $mode,
                    'group' => $group,
                    'show_only_deleted' => 0,
                ];

                if ($chapters->num_rows < 1) {
                    $group_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are no chapters in your selected language(s).']);
                } else {
                    $group_tab_html = parse_template('chapter/chapters_modedit', $templateVars);
                }
            }
            break;

        case 'deleted':
            if (validate_level($user, 'pr')) {

                //$search['manga_ids_array'] = $group->get_manga_ids();
                $search['group_id'] = $group->group_id;
                $search['chapter_deleted'] = 1;

                $order = 'upload_timestamp DESC';

                $limit = 100;
                $current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1;

                try {
                    $chapters = new Chapters($search);
                    $chapters_list = $chapters->query_read($order, $limit, $current_page);
                } catch (\PDOException $e) {
                    // TODO: This is a bit awkward, a proper rework of this section should fix this
                    $chapters = (object)['num_rows' => 0];
                    $chapters_list = [];
                }

                $templateVars = [
                    'limit' => 100,
                    'current_page' => (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1,
                    'chapters' => $chapters_list,
                    'chapter_count' => $chapters->num_rows,
                    'user' => $user,
                    'page' => $page,
                    'mode' => $mode,
                    'group' => $group,
                    'show_only_deleted' => 1,
                ];

                if ($chapters->num_rows < 1) {
                    $group_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are no chapters currently deleted. Good job?']);
                } else {
                    $group_tab_html = parse_template('chapter/chapters_modedit', $templateVars);
                }
            }
            break;

        case 'manga':
            $array_of_manga_ids = $group->get_manga_ids();
            $user_manga_ratings_array = $user->get_manga_ratings();

            /*/multi_lang
            if ($user->user_id && $user->default_lang_ids)
                $search['multi_lang_id'] = explode(',', $user->default_lang_ids);
            elseif (!$user->user_id && $filter_langs_cookie)
                $search['multi_lang_id'] = explode(',', urldecode($filter_langs_cookie));
            */

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
                $mangas_obj = $mangas->query_read($order, $limit, $current_page);
            }
            catch (\PDOException $e) {
                $mangas = (object)['num_rows' => 0];
                $mangas_obj = [];
            }

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
                'base_url' => $string,
                'title_mode' => $title_mode
            ];

            if (is_array($array_of_manga_ids) && count($array_of_manga_ids) > 0) {
                $group_tab_html = parse_template('manga/manga_list', $templateVars);
            } else {
                $group_tab_html = parse_template('partials/alert', ['type' => 'info', 'strong' => 'Notice', 'text' => 'No manga to display.']);
            }

            break;

        case 'comments':
            // Get a list of [user_id => username] the current user has blocked. key is the userid, value is the username
            $blockedUserIds = array_map(function ($e) {
                return $e['username'] ?? 'user';
            }, $user->get_blocked_user_ids());

            $templateVars = [
                'user' => $user,
                'group' => $group,
                'parser' => $parser,
                'page' => $page,
                'blockedUserIds' => $blockedUserIds,
            ];

            $group_tab_html = parse_template('group/partials/comments_thread', $templateVars);
            $post_history_modal_html = parse_template('partials/post_history_modal', $templateVars);
            break;

        case 'admin':
            if (validate_level($user, 'gmod')) {

                $templateVars = [
                    'group' => $group,
                ];

                $group_tab_html = parse_template('group/partials/admin_tab', $templateVars);
            }
            break;
    }

	$templateVars = [
        'group' => $group,
        'mode' => $mode,
        'user' => $user,
        'theme_id' => $theme_id,
        'parser' => $parser,
        'ip' => $ip,
        'group_tab_html' => $group_tab_html,
        'post_history_modal_html' => $post_history_modal_html,
        'group_members_array' => $group_members_array,
        'blocked_user_ids_array' => $blocked_user_ids_array,
        'title_mode' => $title_mode,
        'user_is_invited' => $user_is_invited
    ];

	$page_html = parse_template('group/group', $templateVars);
}
