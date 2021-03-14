<?php
$pm_thread_id = $_GET['id'] ?? 1;

$thread = new PM_Thread($pm_thread_id); 

if ($thread->sender_id == $user->user_id || $thread->recipient_id == $user->user_id) {
	
	$msgs = new PM_Msgs($pm_thread_id, 0, defined('DMS_DISPLAY_LIMIT') ? DMS_DISPLAY_LIMIT : 25);
	$msgs_array = [];
	foreach ($msgs AS $msg_id => $msg) {
	    $msgs_array[$msg_id] = $msg;
    }

    // Mark messages & thread as read
    $sql->modify('mark_read', 'UPDATE mangadex_pm_msgs SET seen = 1 WHERE thread_id = ? AND user_id != ?', [$pm_thread_id, $user->user_id]);
	if ($thread->sender_id == $user->user_id) {
        $sql->modify('mark_read', " UPDATE mangadex_pm_threads SET sender_read = 1 WHERE thread_id = ? LIMIT 1 ", [$pm_thread_id]);
    } else {
        $sql->modify('mark_read', " UPDATE mangadex_pm_threads SET recipient_read = 1 WHERE thread_id = ? LIMIT 1 ", [$pm_thread_id]);
    }
	$memcached->delete("user_{$user->user_id}_unread_msgs");

	$temlateVars = [
        'thread' => $thread,
        'messages' => array_reverse($msgs_array),
        'page' => $page,
        'user' => $user,
        'parser' => $parser,
    ];

	$page_html = parse_template('user/message', $temlateVars);

}
else {
    $page_html = parse_template('partials/alert', ['type' => 'danger', 'strong' => 'Warning', 'text' => 'You are not the sender nor recipient of this thread.']);
}
