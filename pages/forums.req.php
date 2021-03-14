<?php
$categories = $sql->query_read('categories', 'SELECT * FROM mangadex_forums WHERE forum_parent = 0 ORDER BY sort ASC', 'fetchAll', PDO::FETCH_UNIQUE);

$online_guests = $sql->prep('online_guests', ' 
	SELECT visit_ip FROM mangadex_logs_visits where visit_timestamp > ? AND visit_user_id = 0 GROUP BY `visit_ip`
	', [$timestamp - 60], 'fetchAll', PDO::FETCH_COLUMN, 60);

$online_users = $sql->prep('online_users', ' 
	SELECT user_id, username, level_colour 
	FROM mangadex_users 
	LEFT JOIN mangadex_user_levels ON mangadex_users.level_id = mangadex_user_levels.level_id 
	WHERE last_seen_timestamp > ? ORDER BY last_seen_timestamp DESC, mangadex_users.level_id DESC
	', [$timestamp - 60], 'fetchAll', PDO::FETCH_UNIQUE, 60);

$online_users_string = "";
foreach ($online_users as $online_user_id => $online_user)
    $online_users_string .= display_user_link($online_user_id, $online_user['username'], $online_user['level_colour']) . ", ";

$online_users_string = rtrim($online_users_string, ", ");


$templateVars = [
    'categories' => $categories,
    'online_users_string' => $online_users_string,
    'online_users_count' => count($online_users),
    'online_guests_count' => count($online_guests),
    'hentai_toggle' => $hentai_toggle,
    'user' => $user,
];

$page_html = parse_template('forum/forum_list', $templateVars);
