<?php

if (isset($_GET['_'])) {
        http_response_code(666);
        die();
}

use Mangadex\Model\Guard;

require_once ('bootstrap.php');

require_once (ABSPATH . '/scripts/header.req.php');

if (!process_user_limit()) {
	http_response_code(401);
	die('Too many hits detected from ' ._IP. '! Please try again next week. If you are not a bot, contact staff to get unbanned.');
}

$guard = Guard::getInstance();
if (isset($_COOKIE[SESSION_COOKIE_NAME]) || isset($_COOKIE[SESSION_REMEMBERME_COOKIE_NAME])) {
	$guard->tryRestoreSession($_COOKIE[SESSION_COOKIE_NAME] ?? null, $_COOKIE[SESSION_REMEMBERME_COOKIE_NAME] ?? null);
	$user = $guard->hasUser() ? $guard->getUser() : $guard->getUser(0); // Fetch guest record (userid=0) if no user could be restored
} else {
	$user = $guard->getUser(0); // Fetch guest
}

/** @var $sentry Raven_Client */
if (isset($sentry) && isset($user)) {
    $sentry->user_context([
        'id' => $user->user_id,
        'username' => $user->username,
    ]);
}

if ($user->user_id) 
	$ui_lang = new Language($user->display_lang_id, 'lang_id');
elseif ($display_lang_cookie)
	$ui_lang = new Language($display_lang_cookie, 'lang_id');
else
	$ui_lang = new Language(get_browser_lang($_SERVER), 'lang_flag');

$languages = new Languages();

$page = in_array($_GET['page'] . ".req.php", read_dir('pages')) ? $_GET['page'] : 'home'; //redirect if $page does not exist

if (!$user->user_id && in_array($page, REQUIRE_LOGIN_PAGES))
	$page = 'login'; //pages which require login
elseif (!$user->activated && in_array($page, REQUIRE_LOGIN_PAGES))
	$page = 'activation'; //pages which require login
elseif ((!validate_level($user, 'admin') && $page === 'admin') || (!validate_level($user, 'mod') && $page === 'mod')) 
	$page = 'home';

//visit_log_cumulative($ip, $table = "visit");

//visit_log($_SERVER, $ip, $user->user_id, $user->hentai_mode); //$hentai_toggle set in header.req.php

$id = (int)($_GET["id"] ?? 0); // TODO: does this always remain an int?

$lang_id_filter_array = ($user->user_id) ? explode(',', $user->default_lang_ids) : explode(',', urldecode($filter_langs_cookie));
$lang_id_filter_string = implode(',', $lang_id_filter_array);

$title_mode_cookie = (int)($_COOKIE['mangadex_title_mode'] ?? 0);
$title_mode = ($user->user_id) ? $user->mangas_view : $title_mode_cookie;

$theme_id = ($user->user_id) ? $user->style : $theme_cookie;

$hentai_options = ["Hide <span class='badge badge-danger'>H</span>", "View All", "View <span class='badge badge-danger'>H</span> only"];

$announcement = $sql->query_read('top_announce', '
	SELECT threads.thread_name, threads.thread_id, MIN(posts.timestamp) as timestamp
	FROM mangadex_threads AS threads
	LEFT JOIN mangadex_forum_posts AS posts
		ON posts.thread_id = threads.thread_id
	WHERE threads.forum_id = 3
	GROUP BY threads.thread_id
	ORDER BY threads.thread_id DESC LIMIT 2
	', 'fetchAll', PDO::FETCH_OBJ);


// Load user's notes
if ($user->premium >= 1) {
    $notes_unparsed = $sql->prep(
        "user_{$user->user_id}_notes",
        'SELECT affected_user_id, note, username FROM mangadex_user_notes LEFT JOIN mangadex_users ON affected_user_id = user_id WHERE creator_user_id = ?',
        [
            $user->user_id
        ],
        'fetchAll',
        PDO::FETCH_ASSOC,
        600
    );

    $user->notes = [];
    foreach ($notes_unparsed as $noteArray) {
        $user->notes[$noteArray['affected_user_id']] = [
            'username' => $noteArray['username'],
            'note' => $noteArray['note']
        ];
    }
} else {
    $user->notes = [];
}

//// Sets the content for the requested page content

ob_start();
$page_html = null;
require_once(ABSPATH . "/pages/$page.req.php");
$page_html = $page_html ?? "<<< NOT IMPLEMENTED >>>"; // TODO: the alternative if page_html is left null is a fallback and should be removed eventually
ob_end_clean();

// oh god kill me
ob_start();
$page_scripts = null;
print jquery_post('homepage_settings', 0, "", "Save", "Saving", "Your MangaDex settings have been saved.", "location.reload();");
if (in_array("$page.req.js", read_dir("scripts/js"))) {
    require_once (ABSPATH . "/scripts/js/$page.req.js");
}
$page_scripts = $page_scripts ?? ob_get_contents();
ob_end_clean();

$reportCount = null;
$generalReportCount = null;
$uploadQueueCount = null;

if (validate_level($user, 'gmod')) {
	$reportCount = $sql->prep('mod_report_count', 'SELECT * FROM (SELECT COUNT(*) AS chapter_reports FROM `mangadex_reports_chapters` WHERE report_conclusion = 0) c JOIN (SELECT COUNT(*) AS manga_reports FROM `mangadex_reports_manga` WHERE report_conclusion = 0) m', [],'fetch', PDO::FETCH_ASSOC, 3600);
}
if (validate_level($user, 'mod')) {
    $generalReportCount = $sql->prep('mod_general_report_count', 'SELECT COUNT(*) AS report_count FROM mangadex_reports WHERE state < 1', [],'fetch', PDO::FETCH_ASSOC, 3600);
    $generalReportCount = $generalReportCount['report_count'] ?? 0;
	$uploadQueueCount = $sql->prep('mod_upload_queue_count', 'SELECT COUNT(*) AS queue_count FROM mangadex_upload_queue WHERE queue_conclusion IS NULL', [], 'fetch', PDO::FETCH_ASSOC, 3600);
    $uploadQueueCount = $uploadQueueCount['queue_count'] ?? 0;
}

try {
    $report_reasons = (new Report_Reasons())->toArray();
} catch (\Exception $e) {
    // consume for now
    $report_reasons = [];
}

$templateVars = [
    'og' => get_og_tags($page, $id),
    'page' => $page,
    'id' => $id,
    'user' => $user,
    'theme_id' => $theme_id,
    'announcement' => $announcement,
    'page_html' => $page_html,
    'sql' => $sql,
    'lang_id_filter_array' => $lang_id_filter_array,
    'ui_lang' => $ui_lang,
    'hentai_toggle' => $hentai_toggle,
    'hentai_options' => $hentai_options,
    'memcached' => $memcached,
    'ip' => $ip,
    'page_scripts' => $page_scripts,
    'report_count' => $reportCount,
    'general_report_count' => $generalReportCount,
    'report_reasons' => $report_reasons,
    'upload_queue_count' => $uploadQueueCount,
];
print parse_template('index', $templateVars);
