<?php
if (isset($_GET['id']))
	$manga_id = $_GET['id'];
else {
	if ($lang_id_filter_string) {
		$in = prepare_in($lang_id_filter_array);
		$random_manga_array = $sql->prep("random_manga_list_$lang_id_filter_string", "
			SELECT DISTINCT chapters.manga_id
			FROM mangadex_chapters AS chapters
			LEFT JOIN mangadex_mangas AS mangas
				ON mangas.manga_id = chapters.manga_id
			WHERE mangas.manga_hentai = 0 
				AND chapters.chapter_deleted = 0
				AND chapters.lang_id IN ($in)
			", $lang_id_filter_array , 'fetchAll', PDO::FETCH_COLUMN, 86400);
	}
	else {
		$random_manga_array = $sql->query_read("random_manga_list", "
			SELECT DISTINCT chapters.manga_id
			FROM mangadex_chapters AS chapters
			LEFT JOIN mangadex_mangas AS mangas
				ON mangas.manga_id = chapters.manga_id
			WHERE mangas.manga_hentai = 0
				AND chapters.chapter_deleted = 0
			", 'fetchAll', PDO::FETCH_COLUMN, 86400);
	}
	$manga_id = $random_manga_array[array_rand($random_manga_array)];
}

$id = prepare_numeric($manga_id);

$mode = $_GET['mode'] ?? 'chapters';

$manga = new Manga($id);

$relation_types = new Relation_Types(); // This is needed, otherwise it breaks manga.req.js

if (!isset($manga->manga_id)) {
    $page_html = parse_template('partials/alert', ['type' => 'danger', 'strong' => 'Warning', 'text' => "Manga #$id does not exist."]);
}
elseif (in_array($manga->manga_id, RESTRICTED_MANGA_IDS) && !validate_level($user, 'contributor') && $user->get_chapters_read_count() < MINIMUM_CHAPTERS_READ_FOR_RESTRICTED_MANGA) {
	$page_html = parse_template('partials/alert', ['type' => 'danger', 'strong' => 'Warning', 'text' => "Manga #$id is not available. Contact staff on discord for more information."]);
}
else {

    update_views_v2($page, $manga->manga_id, $ip);

    $missing_chapters = $manga->get_missing_chapters($user->display_lang_id);

    $manga_tab_html = "";
    $post_history_modal_html = "";

    switch ($mode) {
        case 'chapters':

            $search['manga_id'] = $manga->manga_id;
			
			$blocked_groups = $user->get_blocked_groups();
			if ($blocked_groups)
				$search['blocked_groups'] = array_keys($blocked_groups);
			
            //multi_lang
            if ($user->user_id && $user->default_lang_ids)
                $search["multi_lang_id"] = $user->default_lang_ids;
            elseif (!$user->user_id && $filter_langs_cookie)
                $search["multi_lang_id"] = urldecode($filter_langs_cookie);

            if ($hentai_toggle == 0)
                $search['manga_hentai'] = 0;
            elseif ($hentai_toggle == 2)
                $search['manga_hentai'] = 1;

			if (!isset($user->show_unavailable) || !$user->show_unavailable)
				$search['available'] = 1;

            $order = "(CASE volume WHEN '' THEN 1 END) DESC, abs(volume) DESC, abs(chapter) DESC, group_id ASC";

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
                'manga' => $manga,
            ];

            if ($chapters->num_rows < 1) {
                $manga_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are no chapters in your selected language(s).']);
            } else {
                $manga_tab_html = parse_template('chapter/chapters', $templateVars);
            }

            break;

        case 'mod_chapters':
            if (validate_level($user, 'gmod')) {

                $search['manga_id'] = $manga->manga_id;

                $order = "(CASE volume WHEN '' THEN 1 END) DESC, abs(volume) DESC, abs(chapter) DESC, group_id ASC";

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
                    'manga' => $manga,
                    'show_only_deleted' => 0
                ];

                if ($chapters->num_rows < 1) {
                    $manga_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are no chapters in your selected language(s).']);
                } else {
                    $manga_tab_html = parse_template('chapter/chapters_modedit', $templateVars);
                }

            }
            break;

        case 'deleted':
            if (validate_level($user, 'pr')) {

                $search['manga_id'] = $manga->manga_id;

                $search['chapter_deleted'] = 1;

                $order = "(CASE volume WHEN '' THEN 1 END) DESC, abs(volume) DESC, abs(chapter) DESC, group_id ASC";

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
                    'manga' => $manga,
                    'show_only_deleted' => 1
                ];

                if ($chapters->num_rows < 1) {
                    $manga_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are no chapters in your selected language(s).']);
                } else {
                    $manga_tab_html = parse_template('chapter/chapters_modedit', $templateVars);
                }

            }
            break;

        case 'summary':

            if ($missing_chapters) {
                $manga_tab_html = "Missing chapters: $missing_chapters";
            }
            break;

        case 'covers':
			$templateVars = [
				'manga' => $manga,
				'user' => $user,
                'covers_data' => $manga->get_covers(),
            ];

            $manga_tab_html = parse_template('manga/partials/covers_tab', $templateVars);

            break;

        case 'comments':
            $manga_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => "Mark all spoilers with [spoiler] tags. Chapter spoilers may be left unmarked only in their respective chapter comment threads."]);

            // Get a list of [user_id => username] the current user has blocked. key is the userid, value is the username
            $blockedUserIds = array_map(function ($e) {
                return $e['username'] ?? 'user';
            }, $user->get_blocked_user_ids());

            $templateVars = [
                'user' => $user,
                'manga' => $manga,
                'parser' => $parser,
                'page' => $page,
                'blockedUserIds' => $blockedUserIds,
            ];

            $manga_tab_html .= parse_template('manga/partials/comments_thread', $templateVars);
            $post_history_modal_html = parse_template('partials/post_history_modal', $templateVars);

            break;

        case 'admin_history':

            if (validate_level($user, 'gmod')) {
                $history = $sql->prep('manga_history_'.$manga->manga_id, 'SELECT u.username, h.*, l.level_colour, l.level_name FROM mangadex_manga_history h, mangadex_users u, mangadex_user_levels l WHERE u.user_id = h.user_id AND u.level_id = l.level_id AND manga_id = ? ORDER BY `timestamp` DESC LIMIT 50', [$manga->manga_id], 'fetchAll', PDO::FETCH_ASSOC, -1);

                $templateVars = [
                    'history' => $history,
                ];

                $manga_tab_html = parse_template('manga/partials/admin_history', $templateVars);
            }
            break;

        case 'admin':
            if (validate_level($user, 'gmod')) {
                $manga_tab_html = parse_template('manga/partials/admin_tab');
            }
            break;
    }

    $templateVars = [
        'page' => $page,
        'mode' => $mode,
        'manga' => $manga,
        'user' => $user,
        'parser' => $parser,
        'missing_chapters' => $missing_chapters,
        'manga_tab_html' => $manga_tab_html,
        'post_history_modal_html' => $post_history_modal_html,
    ];

    $page_html = parse_template('manga/manga', $templateVars);

}