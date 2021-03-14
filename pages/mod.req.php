<?php

if (!validate_level($user, 'mod')) die('No access');

//pages
$mode = $_GET['mode'] ?? 'reports';
$type = ($mode == 'chapter_reports' && !isset($_GET['type'])) ? 'new' : ($_GET['type'] ?? '');

$manga_lists = new Manga_Lists();

$templateVars = [
    'mode' => $mode,
    'type' => $type,
    'manga_lists' => $manga_lists,
	'user' => $user,
];

$page_html = parse_template('mod/partials/mod_navtabs', $templateVars);

switch ($mode) {

    case 'user_tracking':

        $username = trim($_GET['username'] ?? '');

        $templateVars['data'] = $sql->prep('user_tracking', "
SELECT alts.user_id, alts.username, alts.joined_timestamp, alts.last_seen_timestamp, levels.level_id, levels.level_name, levels.level_colour, (SELECT COUNT(*) FROM mangadex_forum_posts posts WHERE posts.user_id = alts.user_id) AS post_count
FROM mangadex_users as users
INNER JOIN mangadex_users as alts ON users.creation_ip = alts.creation_ip OR users.last_ip = alts.last_ip
LEFT JOIN mangadex_user_levels levels ON alts.level_id = levels.level_id
WHERE users.username = ? ORDER BY alts.last_seen_timestamp DESC", [$username], 'fetchAll', PDO::FETCH_ASSOC, -1);

        $page_html .= parse_template('mod/user_tracking', $templateVars);

        break;

    case 'featured':

		if (!validate_level($user, 'gmod')) die('No access');

        $list_id = $type ?? 1;
        $array_of_manga_ids = $manga_lists->get_manga_list($list_id);
        $title_mode = 1;

        if (empty($array_of_manga_ids)) {
            $manga_list_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'No manga.']);
        } else {
            $search = [
                'manga_ids_array' => $array_of_manga_ids
            ];

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
                'base_url' => '',
                'show_tabs' => false,
                'parser' => $parser,
            ];
            $manga_list_html = parse_template('manga/manga_list', $templateVars);
        }

        $templateVars['manga_list_html'] = $manga_list_html;

        $page_html .= parse_template('mod/featured', $templateVars);

        break;

    case 'reports':

        $filter_state = max(0, min(5, (int)($_GET['state'] ?? 0)));
        $perpage = max(5, min(500, (int)($_GET['perpage'] ?? 200)));
        $offset = (($_GET['p'] ?? 1) - 1) * $perpage;

        if (isset($_POST) && !empty($_POST)) {

            // Reload
            http_response_code(301);
            header('location: /mod/reports');
            die();
        }

        $report_reasons = (new Report_Reasons())->toArray();
        $reports = $sql->prep('mod_general_reports', <<<EOD
SELECT r.*,
u.username, l.level_colour,
u2.username as mod_username, l2.level_colour as mod_level_colour
FROM `mangadex_reports` r
LEFT JOIN mangadex_users u ON u.user_id = r.user_id
LEFT JOIN mangadex_user_levels l ON u.level_id = l.level_id
LEFT JOIN mangadex_users u2 ON u2.user_id = r.mod_id
LEFT JOIN mangadex_user_levels l2 ON u2.level_id = l2.level_id
LEFT JOIN mangadex_report_reason re ON r.reason_id = re.id
WHERE state = ?
ORDER BY r.updated DESC, r.created DESC
LIMIT ?,?
EOD
, [$filter_state, $offset, $perpage], 'fetchAll', PDO::FETCH_ASSOC, -1);

        $templateVars = array_merge($templateVars, [
            'perpage' => $perpage,
            'filter_state' => $filter_state,
            'offset' => $offset,
            'report_reasons' => $report_reasons,
            'reports' => $reports,
        ]);

        $page_html .= parse_template('mod/reports', $templateVars);

        break;

    case 'manga_reports':

		if (!validate_level($user, 'gmod')) die('No access');

		$reports = new Manga_reports($type);

        if (count(get_object_vars($reports))) {
            $templateVars['reports'] = $reports;
            $page_html .= parse_template('mod/manga_reports', $templateVars);
        } else {
            $page_html .= parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are currently no reports! Keep up the good work!']);
        }

        break;

    case 'user_restrictions':

		$limit = 50;
        $offset = max(0,(int)($_GET['offset'] ?? 0));

        if ($type == 'active') {
            // Current Restrictions
            $user_restrictions = $sql->prep('user_restrictions_active_detailed_all', '
  SELECT r.*, r.target_user_id AS user_id, t.*, ru.username, mu.username AS mod_username, rl.level_colour, ml.level_colour AS mod_level_colour
  FROM mangadex_user_restrictions r 
  LEFT JOIN mangadex_restriction_types t
    ON t.restriction_type_id = r.restriction_type_id
  LEFT JOIN mangadex_users mu
    ON r.mod_user_id = mu.user_id
  LEFT JOIN mangadex_user_levels ml
    ON mu.level_id = ml.level_id
  LEFT JOIN mangadex_users ru
    ON r.target_user_id = ru.user_id
  LEFT JOIN mangadex_user_levels rl
    ON ru.level_id = rl.level_id
  WHERE UNIX_TIMESTAMP() < r.expiration_timestamp
  ORDER BY r.expiration_timestamp ASC
  LIMIT ?,?', [$offset, $limit], 'fetchAll', PDO::FETCH_ASSOC, -1);


        } else {
            // Past restrictions
            $user_restrictions = $sql->prep('user_restrictions_inactive_detailed_all', '
  SELECT r.*, r.target_user_id AS user_id, t.*, ru.username, mu.username AS mod_username, rl.level_colour, ml.level_colour AS mod_level_colour
  FROM mangadex_user_restrictions r 
  LEFT JOIN mangadex_restriction_types t
    ON t.restriction_type_id = r.restriction_type_id
  LEFT JOIN mangadex_users mu
    ON r.mod_user_id = mu.user_id
  LEFT JOIN mangadex_user_levels ml
    ON mu.level_id = ml.level_id
  LEFT JOIN mangadex_users ru
    ON r.target_user_id = ru.user_id
  LEFT JOIN mangadex_user_levels rl
    ON ru.level_id = rl.level_id
  WHERE UNIX_TIMESTAMP() >= r.expiration_timestamp
  ORDER BY r.expiration_timestamp DESC
  LIMIT ?,?', [$offset, $limit], 'fetchAll', PDO::FETCH_ASSOC, -1);
        }

        $restriction_types = array_reduce($sql->prep('restriction_types', 'SELECT * FROM mangadex_restriction_types', [], 'fetchAll', PDO::FETCH_ASSOC, 60) ?? [], function ($result, $item) {
            $result[$item['restriction_type_id']] = $item['name'];
            return $result;
        });

        if (count($user_restrictions) > 0) {
            $templateVars['limit'] = $limit;
            $templateVars['offset'] = $offset;
            $templateVars['user_restrictions'] = $user_restrictions;
            $templateVars['restriction_types'] = $restriction_types;
            $page_html .= parse_template('mod/user_restrictions', $templateVars);
        } else {
            $page_html .= parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'No restrictions found.']);
        }

        break;
	
	case 'upload_queue':
		$queue = new Upload_queue();
		
		if (count(get_object_vars($queue))) {	
			$templateVars['queue'] = $queue;		
			$page_html .= parse_template('mod/upload_queue', $templateVars);
		} else {
			$page_html .= parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are currently no chapters in the upload queue! Keep up the good work!']);
		}
		
		break;
	
	case 'chapter_reports':
	default:

		if (!validate_level($user, 'gmod')) die('No access');

		$reports = new Chapter_reports($type);
        $chapter_reasons = array_filter((new Report_Reasons())->toArray(), function($reason) { return REPORT_TYPES[$reason['type_id']] === 'Chapter'; });
        $report_type_array = [];
        foreach ($chapter_reasons as $reason) {
            $report_type_array[$reason['id']] = $reason['text'];
        }

		if (count(get_object_vars($reports))) {
			$templateVars['reports'] = $reports;
			$templateVars['report_type_array'] = $report_type_array;
			$page_html .= parse_template('mod/chapter_reports', $templateVars);
		} else {
			$page_html .= parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are currently no reports! Keep up the good work!']);
		}


		break;
}
