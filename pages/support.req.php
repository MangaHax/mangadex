<?php
$transactions = $user->get_transactions();

if ($transactions) {
	$sql->modify('claim_transaction', ' UPDATE mangadex_user_transactions SET user_id = ? WHERE user_id = 0 AND email LIKE ? ', [$user->user_id, $transactions[0]['email']]);
	
	$memcached->delete("user_{$user->user_id}_transactions");
}

$wallet_no = substr($user->user_id, -1);
$wallet_no_2 = floor(substr($user->user_id, -1) / 2);

$page_html = parse_template('user/support', [
	'user' => $user, 
	'wallet_no' => $wallet_no,
	'wallet_no_2' => $wallet_no_2,
]);