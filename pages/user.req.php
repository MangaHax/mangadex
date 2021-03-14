<?php

$user_id = prepare_numeric($_GET['id'] ?? 1);

$mode = $_GET['mode'] ?? 'chapters';

$memcached->delete("user_$user_id");
$uploader = new User($user_id, "user_id");

//$uploader->update_total_chapters_uploaded(); //gets the total and updates the user table with total uploaded chapters

if (!$uploader->exists || $user_id < 1) {
    $page_html = parse_template('partials/alert', ['type' => 'danger', 'strong' => 'Warning', 'text' => "User #$user_id does not exist."]);

} else {

	update_views_v2($page, $uploader->user_id, $ip);

    $mdListButtonEnabled = $uploader->list_privacy === 1 // List set to public
        || ($uploader->list_privacy === 2 && isset($uploader->get_friends_user_ids()[$user->user_id])) // List set to friends only and the current user is a friend
        || (validate_level($user, 'admin')); // User is admin

    if (validate_level($user, 'mod')) {
        try {
            $restriction_types = array_reduce($sql->prep('restriction_types', 'SELECT * FROM mangadex_restriction_types', [], 'fetchAll', PDO::FETCH_ASSOC, 60) ?? [], function ($result, $item) {
                $result[$item['restriction_type_id']] = $item['name'];
                return $result;
            });
            // Current Restrictions
            $user_restrictions = $sql->prep('user_restrictions_active_detailed_'.$user_id, '
  SELECT r.*, t.*, u.username AS mod_username, l.level_colour AS mod_level_colour
  FROM mangadex_user_restrictions r 
  LEFT JOIN mangadex_restriction_types t
    ON t.restriction_type_id = r.restriction_type_id
  LEFT JOIN mangadex_users u
    ON r.mod_user_id = u.user_id
  LEFT JOIN mangadex_user_levels l
    ON u.level_id = l.level_id
  WHERE r.target_user_id = ? AND UNIX_TIMESTAMP() < r.expiration_timestamp
  ORDER BY r.expiration_timestamp ASC
  LIMIT 25', [$user_id], 'fetchAll', PDO::FETCH_ASSOC, 60);
            // Past restrictions
            $past_restrictions = $sql->prep('user_restrictions_inactive_detailed_'.$user_id, '
  SELECT r.*, t.*, u.username AS mod_username, l.level_colour AS mod_level_colour
  FROM mangadex_user_restrictions r 
  LEFT JOIN mangadex_restriction_types t
    ON t.restriction_type_id = r.restriction_type_id
  LEFT JOIN mangadex_users u
    ON r.mod_user_id = u.user_id
  LEFT JOIN mangadex_user_levels l
    ON u.level_id = l.level_id
  WHERE r.target_user_id = ? AND UNIX_TIMESTAMP() >= r.expiration_timestamp
  ORDER BY r.expiration_timestamp DESC
  LIMIT 25', [$user_id], 'fetchAll', PDO::FETCH_ASSOC, 3600);
        } catch (Exception $e) {
            $restriction_types = [];
            $past_restrictions = [];
            $user_restrictions = [];
        }
    } else {
        $restriction_types = [];
        $past_restrictions = [];
        $user_restrictions = [];
    }

	$user_tab_html = "";
    $post_history_modal_html = "";

	switch ($mode) {
		case 'chapters':

            $search['user_id'] = $user_id;

            if ($hentai_toggle == 0)
                $search['manga_hentai'] = 0;
            elseif ($hentai_toggle == 2)
                $search['manga_hentai'] = 1;
				
			if (!isset($user->show_unavailable) || !$user->show_unavailable)
				$search['available'] = 1;

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
                'uploader' => $uploader,
                'title_mode' => $title_mode
            ];

            if ($chapters->num_rows < 1) {
                $user_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'This user hasn\'t uploaded any chapters yet.']);
            } else {
                $user_tab_html = parse_template('chapter/chapters', $templateVars);
            }

			break;
			
		case 'mod_chapters':
			if (validate_level($user, 'gmod')) {
                $search['user_id'] = $user_id;
				
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
                    'uploader' => $uploader,
                    'title_mode' => $title_mode,
                    'show_only_deleted' => 0,
                ];

                if ($chapters->num_rows < 1) {
                    $user_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'This user hasn\'t uploaded any chapters yet.']);
                } else {
                    $user_tab_html = parse_template('chapter/chapters_modedit', $templateVars);
                }
			}
			break;
			
		case 'deleted':
			if (validate_level($user, 'pr')) {
                $search['user_id'] = $user_id;
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

                $title_mode = ($user->user_id) ? $user->mangas_view : ($_COOKIE['mangadex_title_mode'] ?? 0);

                $templateVars = [
                    'limit' => 100,
                    'current_page' => (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1,
                    'chapters' => $chapters_list,
                    'chapter_count' => $chapters->num_rows,
                    'user' => $user,
                    'page' => $page,
                    'mode' => $mode,
                    'uploader' => $uploader,
                    'title_mode' => $title_mode,
                    'show_only_deleted' => 1,
                ];

                if ($chapters->num_rows < 1) {
                    $user_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'This user hasn\'t uploaded any chapters yet.']);
                } else {
                    $user_tab_html = parse_template('chapter/chapters_modedit', $templateVars);
                }
			}
			break;
			
		case 'comments':
			if (validate_level($user, 'pr')) {
                $show = $_GET['show'] ?? 'all';
                if ($show !== 'moderated' && $show !== 'unmoderated') {
                    $show = 'all';
                }

                $current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? (int) $_GET['p'] : 1;
				$user_comments_array = $uploader->get_comments($show, $current_page);
				$user_comments_count = $uploader->get_comments_count($show);
				
				if ($user_comments_count === 0) {
                    $user_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'This user hasn\'t posted any comments.']);
                } else {
				    $templateVars = [
                        'user_comments_array' => $user_comments_array,
                        'user_comments_count' => $user_comments_count,
                        'current_page' => $current_page,
                        'limit' => 100,
                        'uploader' => $uploader,

                        'user' => $user,
                        'parser' => $parser,
                        'page' => $page,
                        'show' => $show,
                    ];

                    $user_tab_html = parse_template('user/partials/comments_tab', $templateVars);
                    $post_history_modal_html = parse_template('partials/post_history_modal', $templateVars);
				}
			}
			break;
			
		case 'manga':
			$array_of_manga_ids = $uploader->get_manga_ids();
			$user_manga_ratings_array = $user->get_manga_ratings();

			//$search['user_id'] = $uploader->user_id;
            $search['manga_ids_array'] = $array_of_manga_ids;

            if ($hentai_toggle == 0)
                $search['manga_hentai'] = 0;
            elseif ($hentai_toggle == 2)
                $search['manga_hentai'] = 1;

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
                $user_tab_html = parse_template('manga/manga_list', $templateVars);
            } else {
                $user_tab_html = parse_template('partials/alert', ['type' => 'info', 'strong' => 'Notice', 'text' => 'No manga to display.']);
            }
			
			break;
			
		case 'admin':
			if (validate_level($user, 'mod')) {

                if (validate_level($user, 'admin'))
                {
                    $twoFa = new \Mangadex\TwoFactorAuth($uploader);
                    $uploader->twoFa = $twoFa->isEnabled();
                }

                $antispam = new \Mangadex\Model\AntiSpam();
                $score = $antispam->getScore($user->username, $user->email, $user->last_ip);

			    $templateVars = [
			        'user' => $user,
                    'uploader' => $uploader,
                    'spamscore' => $score,
                ];

                $user_tab_html = parse_template('user/partials/admin_edituser_form', $templateVars);
			}
			break;
	}

    $parser->parse($uploader->user_bio);

	// Server geoip debug
	if (/*$uploader->user_id === $user->user_id ||*/ validate_level($user, 'pr')) {
		// Mod or same user, display region info
		get_server_id_by_geography($uploader->last_ip, $continentCode, $countryCode, $serverContinentCode, $serverId);
		$geoip_info = [
			'continent_code' => $continentCode,
			'country_code' => $countryCode,
			'server_continent_code' => $serverContinentCode,
			'server_id' => $serverId,
		];
	} else {
		$geoip_info = false;
	}

    $templateVars = [
        'uploader' => $uploader,
        'user' => $user,
        'mode' => $mode,
        'title_mode' => $title_mode,
        'parser' => $parser,
        'user_tab_html' => $user_tab_html,
        'post_history_modal_html' => $post_history_modal_html,
        'restriction_types' => $restriction_types,
        'past_restrictions' => $past_restrictions,
        'user_restrictions' => $user_restrictions,
        'mdListButtonEnabled' => $mdListButtonEnabled,
		'geoip_info' => $geoip_info,
    ];

	$page_html = parse_template('user/user', $templateVars);

}
