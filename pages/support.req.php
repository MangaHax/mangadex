<?php
$mode = $_GET['mode'] ?? 'home';

$templateVars = [
    'mode' => $mode
];

$page_html = parse_template('support/partials/support_navtabs', $templateVars);

switch ($mode) {
    case 'home':
        $page_html .= parse_template('support/home', [
            'user' => $user
        ]);
        break;
    case 'donate':
        $wallet_no = substr($user->user_id, -1);
        $wallet_no_2 = floor(substr($user->user_id, -1) / 2);

        $page_html .= parse_template('support/donate', [
            'wallet_no' => $wallet_no,
            'wallet_no_2' => $wallet_no_2
        ]);
        break;
    case 'history':
        $transactions = $user->get_transactions();

        if ($transactions) {
            $sql->modify('claim_transaction', ' UPDATE mangadex_user_transactions SET user_id = ? WHERE user_id = 0 AND email LIKE ? ', [$user->user_id, $transactions[0]['email']]);

            $memcached->delete("user_{$user->user_id}_transactions");
        }

        $page_html .= parse_template('support/history', [
            'user' => $user
        ]);
        break;
    case 'affiliates':
        $page_html .= parse_template('support/affiliates', $templateVars);
        break;
}

