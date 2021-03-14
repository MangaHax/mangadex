<?php
$user_groups_array = $user->get_groups();
$user_blocked_groups = $user->get_blocked_groups();

$twoFa = new \Mangadex\TwoFactorAuth($user);

$_2fa_html = parse_template('user/partials/settings_2fa', [
    '2fa' => $twoFa->getData(),
]);

$session_html = parse_template('user/partials/settings_sessions', [
	'sessions' => $user->get_sessions(),
	'useragent' => \Mangadex\Model\Guard::getInstance()->getUseragent(),
]);

$templateVars = [
    'user' => $user,
	'user_clients' => $user->get_clients(), 
    'user_groups_array' => $user_groups_array,
    'user_blocked_groups' => $user_blocked_groups,
    'lang_id_filter_array' => $lang_id_filter_array,
    '2fa_html' => $_2fa_html,
	'session_html' => $session_html,
];

$page_html = parse_template('user/settings', $templateVars);
