<?php
if (!process_user_limit(25, 'login_', 3600, 3600)) {
	http_response_code(401);
	die('Too many logins detected from ' ._IP. '! Please try again tomorrow. If you are not a bot, contact staff to get unbanned.');
}

if (isset($_GET['clear_cookies']) && $_GET['clear_cookies'] > 0) {
    $domains = [DOMAIN, 'wwww.'.DOMAIN];
    if (DOMAIN !== 'mangadex.org') {
        $domains[] = 'mangadex.org';
        $domains[] = 'www.mangadex.org';
    }
    foreach ($domains AS $domain) {
        setcookie('mangadex_session', '', $timestamp - 3600, '/', $domain);
        setcookie('mangadex_session', '', $timestamp - 3600, '/', ".$domain");
        setcookie('mangadex_rememberme_token', '', $timestamp - 3600, '/', $domain);
        setcookie('mangadex_rememberme_token', '', $timestamp - 3600, '/', ".$domain");
        setcookie('mangadex_h_toggle', '', $timestamp - 3600, '/', $domain);
        setcookie('mangadex_h_toggle', '', $timestamp - 3600, '/', ".$domain");
        setcookie('mangadex_display_lang', '', $timestamp - 3600, '/', $domain);
        setcookie('mangadex_display_lang', '', $timestamp - 3600, '/', ".$domain");
        setcookie('mangadex_title_mode', '', $timestamp - 3600, '/', $domain);
        setcookie('mangadex_title_mode', '', $timestamp - 3600, '/', ".$domain");
    }

    redirect_url('/login?msg=cookies_cleared');
}

$page_html = parse_template('user/login', ['user' => $user]);
