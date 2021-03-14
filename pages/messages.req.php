<?php
$mode = $_GET['mode'] ?? 'inbox';

$messages_tab_html = '';

switch ($mode) {
    case 'notifications':
        $notifications = new Notifications($user->user_id);
        $notifications_obj = $notifications->query_read();

        if (count($notifications_obj) < 1) {
            $messages_tab_html = parse_template('partials/alert', ['type' => 'info', 'strong' => 'Notice', 'text' => 'No notifications.', '10px']);
        } else {
            $sql->modify('mark_read', " UPDATE mangadex_notifications SET is_read = 1 WHERE mentionee_user_id = ? ", [$user->user_id]);
            $memcached->delete("user_{$user->user_id}_unread_notifications");

            $templateVars = [
                'notifications' => $notifications_obj
            ];

            $messages_tab_html = parse_template('user/partials/messages_notifications', $templateVars);
        }

        break;

    case 'send':

        $messages_tab_html = parse_template('user/partials/messages_send', [ 'is_staff' => validate_level($user, 'pr') ]);

        break;

    case 'inbox':
    case 'bin':
    default:
        $deleted = ($mode == 'bin') ? 1 : 0;

        $threads = new PM_Threads($user->user_id, $deleted);
        $threads_obj = $threads->query_read();

        if ($threads->num_rows < 1) {
            $messages_tab_html = parse_template('partials/alert', ['type' => 'info', 'strong' => 'Notice', 'text' => 'You have no messages']);
        } else {
            $templateVars = [
                'threads' => $threads_obj,
                'user' => $user,
                'deleted' => $deleted,
            ];

            $messages_tab_html = parse_template('user/partials/messages_list', $templateVars);
        }

        break;
}

$templateVars = [
    'mode' => $mode,
    'messages_tab_html' => $messages_tab_html,
];

$page_html = parse_template('user/messages', $templateVars);
