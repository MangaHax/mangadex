<?php
$mode = $_GET['mode'] ?? 'friends';

switch ($mode) {
    case 'blocked':
        $blocked = $user->get_blocked_user_ids();

        if ($blocked) {
            $templateVars = [
                'blocked' => $blocked,
            ];

            $social_tab_html = parse_template('user/partials/social_blocked', $templateVars);
        } else {
            $social_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Info', 'text' => 'You haven\'t blocked any users.']);
        }

        break;

    case 'friends':
    default:
        $friends = $user->get_friends_user_ids();
        $pending = $user->get_pending_friends_user_ids();

        if ($friends || $pending) {

            $templateVars = [
                'friends' => $friends,
                'pending' => $pending,
                'user' => $user,
            ];

            $social_tab_html = parse_template('user/partials/social_friends', $templateVars);
        } else {
            $social_tab_html = parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Info', 'text' => 'Oops! You have no friends...']);
        }

        break;
}

$templateVars = [
    'social_tab_html' => $social_tab_html,
    'mode' => $mode,
];

$page_html = parse_template('user/social', $templateVars);
