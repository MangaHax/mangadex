<?php

if (!validate_level($user, 'admin')) die('No access');

$mode = $_GET['mode'] ?? 'stats';
$type = ($mode == 'visit_logs' && !isset($_GET['type'])) ? 'summary' : ($_GET['type'] ?? '');

$templateVars = [
    'mode' => $mode,
    'type' => $type
];

$page_html = parse_template('admin/partials/admin_navtabs', $templateVars);

switch ($mode) {

    case 'action_logs':

        $templateVars['logs'] = new Action_logs();
        $page_html .= parse_template('admin/action_logs', $templateVars);

        break;

    case 'cache_stats':

        $cacheFlushed = false;
        if (isset($_POST['flush_cache']) && $_POST['flush_cache']) {
            $memcached->flush();
            $cacheFlushed = true;
        }

        $templateVars['stats'] = $memcached->getStats();
        $templateVars['cache_flushed'] = $cacheFlushed;
        $page_html .= parse_template('admin/cache_stats', $templateVars);

        break;

    case 'report_reasons':

        $message = '';
        if (!empty($_POST)) {
            // HTML array forms order is fucked up beyond belief, so we have to reorder the array

            $keep_reason_ids = [];
            $update_reasons = [];
            $add_reasons = [];
            $order = 0;
            foreach ($_POST['type_id'] ?? [] AS $type_id => $reason_ids) {
                // if reason id is set, we keep this and only maybe update it. if reason_id is empty, its a new element
                foreach ($reason_ids AS $position => $reason_id) {
                    if ($reason_id) {
                        // Save the id so we can remove all reasons which ids we dont have, since we assume those have
                        // been removed from the form before submitting
                        $keep_reason_ids[] = (int)$reason_id;
                        // We have to assume the html form was submitted properly and hasnt been tampered with, then we
                        // can also assume that the other field arrays have the same structure and just grab the variables
                        // from there.
                        $update_reasons[$reason_id] = [
                            'id' => (int)$reason_id,
                            'type_id' => (int)$type_id,
                            'text' => $_POST['text'][$type_id][$position],
                            'is_info_required' => (int)(isset($_POST['is_info_required'][$type_id][$position]) && $_POST['is_info_required'][$type_id][$position]), // Checkbox set to "On"
                            'sortorder' => $order++,
                        ];
                    } else {
                        $add_reasons[] = [
                            'type_id' => (int)$type_id,
                            'text' => $_POST['text'][$type_id][$position],
                            'is_info_required' => (int)(isset($_POST['is_info_required'][$type_id][$position]) && $_POST['is_info_required'][$type_id][$position]), // Checkbox set to "On"
                            'sortorder' => $order++,
                        ];
                    }
                }
            }

            //dump($_POST, $add_reasons, $update_reasons, $keep_reason_ids);die();

            // Write changes
            try {
                if (empty($keep_reason_ids)) {
                    $sql->modify('keep_remove_reasons', 'DELETE FROM mangadex_report_reason', []); // Note that we can skip the pdobind because keep_reason_ids are guaranteed ints
                } else {
                    $sql->modify('keep_remove_reasons', 'DELETE FROM mangadex_report_reason WHERE id NOT IN (' .implode(',',$keep_reason_ids).")", []); // Note that we can skip the pdobind because keep_reason_ids are guaranteed ints
                }
                foreach ($update_reasons AS $reason_id => $reason) {
                    $sql->modify('update_reasons', 'UPDATE mangadex_report_reason SET `text` = ?, `type_id` = ?, `is_info_required` = ?, `sortorder` = ? WHERE `id` = ?', [
                        $reason['text'], $reason['type_id'], $reason['is_info_required'], $reason['sortorder'], $reason_id
                    ]);
                }
                foreach ($add_reasons AS $reason) {
                    $sql->modify('add_reasons', 'INSERT INTO mangadex_report_reason (`text`, `type_id`, `is_info_required`, `sortorder`) VALUES (?,?,?,?)', [
                        $reason['text'], $reason['type_id'], $reason['is_info_required'], $reason['sortorder']
                    ]);
                }

                $memcached->delete('report_reasons');
                $message = 'Changes applied successfully!';

                $report_reasons = (new Report_Reasons())->toArray();
            } catch (\Exception $e) {
                $message = ['class' => 'danger', 'text' => $e->getMessage()];
            }
        }

        if (!isset($report_reasons))
            $report_reasons = (new Report_Reasons())->toArray();

        $templateVars['report_reasons'] = $report_reasons;
        $templateVars['message'] = $message;
        $page_html .= parse_template('admin/report_reasons', $templateVars);

        break;

    case 'ip_unban':

        if ($type == 'ip_unban') {

            $page_html .= parse_template('admin/ip_unban', $templateVars);

        } else if ($type == 'ip_ban') {

            $templateVars['banlist'] = $sql->prep('ip_banlist', "SELECT * FROM mangadex_ip_bans ORDER BY expires DESC", [], 'fetchAll', PDO::FETCH_ASSOC, 3600);
            $page_html .= parse_template('admin/ip_ban', $templateVars);

        } else if ($type == 'ip_failtwoban') {

        	$query_ip = trim($_GET['query_ip'] ?? '');
        	if (!empty($query_ip)) {
        		$templateVars['data'] = $sql->prep('ip_f2b', 'SELECT * FROM mangadex_fail2ban_log WHERE ip = ? LIMIT 100', [$query_ip], 'fetchAll', PDO::FETCH_ASSOC, -1);
			}
        	$page_html .= parse_template('admin/ip_fail2ban', $templateVars);

		}
        break;

    case 'ip_tracking':

        $creation_ip = trim($_GET['creation_ip'] ?? '');
        $last_ip = trim($_GET['last_ip'] ?? '');
        $email_host = trim($_GET['email_host'] ?? '');
		
		if ($email_host) {
        $templateVars['data'] = $sql->prep('ip_tracking', "SELECT users.user_id, users.username, users.email, users.joined_timestamp, users.last_seen_timestamp, users.creation_ip, users.last_ip, levels.level_id, levels.level_name, levels.level_colour, (SELECT COUNT(*) FROM mangadex_forum_posts posts WHERE posts.user_id = users.user_id) AS post_count
FROM mangadex_users users
LEFT JOIN mangadex_user_levels levels ON users.level_id = levels.level_id
WHERE creation_ip IN (?,?) OR last_ip IN (?,?) OR email LIKE ? ORDER BY users.last_seen_timestamp DESC", [$creation_ip, $last_ip, $creation_ip, $last_ip, "%$email_host"], 'fetchAll', PDO::FETCH_ASSOC, -1);
		}
		else {
		$templateVars['data'] = $sql->prep('ip_tracking', "SELECT users.user_id, users.username, users.email, users.joined_timestamp, users.last_seen_timestamp, users.creation_ip, users.last_ip, levels.level_id, levels.level_name, levels.level_colour, (SELECT COUNT(*) FROM mangadex_forum_posts posts WHERE posts.user_id = users.user_id) AS post_count
FROM mangadex_users users
LEFT JOIN mangadex_user_levels levels ON users.level_id = levels.level_id
WHERE creation_ip IN (?,?) OR last_ip IN (?,?) ORDER BY users.last_seen_timestamp DESC", [$creation_ip, $last_ip, $creation_ip, $last_ip], 'fetchAll', PDO::FETCH_ASSOC, -1);
		}

        $page_html .= parse_template('admin/ip_tracking', $templateVars);

        break;

	case 'tempmail':
		$templateVars['tempmail'] = $sql->query_read('tempmail', "SELECT host FROM mangadex_tempmail ORDER BY host ASC ", 'fetchAll', PDO::FETCH_COLUMN);
		
		$page_html .= parse_template('admin/tempmail', $templateVars);

        break;	
		
    case 'visit_logs':

        if ($type === 'summary') {

            $templateVars['results'] = $sql->query_read('visit_summary', ' SELECT * FROM mangadex_logs_visits_summary ORDER BY id DESC ', 'fetchAll', PDO::FETCH_UNIQUE, -1);
            $page_html .= parse_template('admin/visit_logs_summary', $templateVars);

        } else {

            $templateVars['visit_logs'] = new Visit_logs();
            $page_html .= parse_template('admin/visit_logs', $templateVars);

        }

        break;

    case 'database':

        $stats = [];
        $errors = [];
        foreach (\array_unique(DB_READ_HOSTS ?? []) AS $dsn) {
            if (\strpos($dsn, ':') !== false) {
                [$host, $port] = explode(':', $dsn);
            } else {
                $host = $dsn;
                $port = 3306;
            }

            $res = [];
            try {
                $conn = new PDO("mysql:host=$host;port=$port;dbname=".DB_READ_NAME.";charset=utf8mb4", DB_READ_USER, DB_READ_PASSWORD,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    ]
                );
                $q = $conn->query('SHOW SLAVE STATUS');
                $res = $q->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $stats[$dsn] = null;
                $errors[$dsn] = $e->getMessage();
                continue;
            }
            $stats[$dsn] = $res;
        }

        $templateVars['stats'] = $stats;
        $templateVars['errors'] = $errors;

        $page_html .= parse_template('admin/database', $templateVars);

        break;

	case 'stats':	
    default:
	
		$templateVars['stats'] = $sql->query_read('registration_stats', "SELECT * FROM mangadex_stats_registrations ", 'fetchAll', PDO::FETCH_ASSOC, -1);
		$templateVars['memcached'] = $memcached;
		
		$page_html .= parse_template('admin/stats', $templateVars);

        break;

}
