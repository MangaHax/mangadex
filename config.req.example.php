<?php
define('ABSPATH', __DIR__);
define('ABS_DATA_BASEPATH', __DIR__);

define('DEBUG', false);

define('DB_USER', 'mangadex');
define('DB_PASSWORD', '');
define('DB_NAME', 'mangadex');
define('DB_HOST', 'localhost');

define('DB_READ_HOSTS', ['127.0.0.1']);
define('DB_READ_NAME', DB_NAME);
define('DB_READ_USER', DB_USER);
define('DB_READ_PASSWORD', '');

define('DISCORD_WEBHOOK_REPORT', '');
define('DISCORD_WEBHOOK_EVENT', '');
define('DISCORD_REPORT_PING_COUNT', 10);
define('DISCORD_REPORT_PING_ROLE_ID', '676110785633845288');

define('SENTRY_DSN', false);
define('SENTRY_SAMPLE_RATE', 1);
define('SENTRY_CURL_METHOD', 'async'); # [sync, async, exec]
define('SENTRY_TIMEOUT', 2);

define('DOMAIN', 'mangadex.org');
define('URL', 'https://mangadex.org/');
define('TITLE', 'MangaDex');
define('DESCRIPTION', 'Read manga online for free at MangaDex with no ads, high quality images and support scanlation groups!');
define('MEMCACHED_HOST', '127.0.0.1');

define('GOOGLE_CAPTCHA_SITEKEY', 'xxx');
define('GOOGLE_CAPTCHA_SECRET', 'xxx');

define('MD_AT_H_BACKEND_SECRET', 'SECRET');
define('MD_AT_H_BACKEND_URL', 'http://mangadex-test.net');

define('SESSION_COOKIE_NAME', 'mangadex_session');
define('SESSION_REMEMBERME_COOKIE_NAME', 'mangadex_rememberme_token');
define('SESSION_TIMEOUT', 60*60); // one hour
define('SESSION_REMEMBERME_TIMEOUT', 60*60*24*365); // one year
define('SESSION_COOKIE_DOMAIN', '.'.DOMAIN);
define('SESSION_COOKIE_PATH', '/');

define('ENABLE_UPLOAD', true);
define('ENABLE_REGISTRATION', true);
define('FADE_DURATION', 3000);
define('ENABLE_2FA', true);

define('CACHE_TIME', 120); //seconds
define('CAPTURE_CACHE_STATS', false); // Uses the Cache class decorator for Memcached to capture and expose memcache stats. Should not be used in production!!!

define('INCLUDE_JS_REDIRECT', true); // displays the javascript snippet, that redirects to mangadex.org if the url is different to prevent site-mirrors
define('DISABLE_HITCOUNTER', false); // Disables the flood check

define('REQUIRE_CAPTCHA', true); // Enables/Disables the captcha check for certain functionalities, like signup
define('GOOGLE_SERVICE_ACCOUNT_PATH', '/var/www/google_service_credentials.json'); // Store this OUTSIDE of the webroot!!!

define('MAX_CHAPTER_FILESIZE', 104857600); //100*1024*1024

define('DMS_DISPLAY_LIMIT', 25);

define('REQUIRE_LOGIN_PAGES', ['users', 'follows', 'followed_manga', 'followed_groups', 'follows_import', 'upload', 'settings', 'messages', 'message', 'send_message', 'activation', 'admin', 'mod', 'group_new', 'manga_new', 'stats', 'social']);

define('USER_RESTRICTION_CHAPTER_UPLOAD', 1);
define('USER_RESTRICTION_POST_COMMENT', 2);
define('USER_RESTRICTION_CHAPTER_DELETE', 3);
define('USER_RESTRICTION_CREATE_REPORT', 4);
define('USER_RESTRICTION_CREATE_DM', 5);
define('USER_RESTRICTION_CHANGE_BIOGRAPHY', 6);
define('USER_RESTRICTION_CHANGE_AVATAR', 7);
define('USER_RESTRICTION_EDIT_TITLES', 8);

define('THEMES', [1 => 'Light', 2 => 'Dark', 3 => 'Light-Bronze', 4 => 'Dark-Bronze', 5 => 'Light-Slate', 6 => 'Dark-Slate', 7 => 'Abyss' ]);
define('ORIG_LANG_ARRAY', [2 => 'Japanese', 1 => 'English', 3 => 'Polish', 8 => 'German', 10 => 'French', 12 => 'Vietnamese', 21 => 'Chinese', 27 => 'Indonesian', 28 => 'Korean', 29 => 'Spanish (LATAM)', 32 => 'Thai', 34 => 'Filipino']);
define('STATUS_ARRAY', [1 => 'Ongoing', 2 => 'Completed', 3 => 'Cancelled', 4 => 'Hiatus']);
define('MANGA_EXT_LINKS', ['mu' => 'MangaUpdates ID', 'mal' => 'MyAnimeList ID', 'nu' => 'NovelUpdates slug', 'raw' => 'Raw URL', 'engtl' => 'Official Eng URL', 'cdj' => 'CDJapan URL', 'amz' => 'Amazon.co.jp URL', 'ebj' => 'eBookJapan URL', 'bw' => 'Bookwalker ID', 'al' => 'AniList ID', 'kt' => 'Kitsu ID', 'ap' => 'Anime-Planet slug', 'dj' => 'Doujinshi.org ID']);
define('MANGA_DEMO', ['None', 'Shounen', 'Shoujo', 'Seinen', 'Josei']);
define('MANGA_VIEW_MODE_ICONS', ['th-large', 'th-list', 'bars', 'th']);
define('REPORT_TYPES', [0 => 'invalid', 1 => 'Manga', 2 => 'Chapter', 3 => 'Comment', 4 => 'Group', 5 => 'User']);

define('RESTRICTED_MANGA_IDS', [33691, 35013, 37514, 34291, 37070, 29885, 31106, 20104, 43476, 41743, 42731, 37071, 35251, 42244, 39585, 46603, 46790, 46856, 43140, 46858]);
define('MINIMUM_CHAPTERS_READ_FOR_RESTRICTED_MANGA', 100);

define('MINIMUM_CHAPTERS_READ_FOR_SUPPORT', 20);

define('ALLOWED_CHAPTER_EXT', ['zip', 'cbz']);
define('MAX_IMAGE_FILESIZE', 1048576);
define('ALLOWED_IMG_EXT', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_MIME_TYPES', ['image/png', 'image/jpeg', 'image/gif']);
define('IMAGE_SERVER', 0);
define('IMG_SERVER_URL', 'https://s1.mangadex.org');
define('LOCAL_SERVER_URL', 'https://cdndex.com/data/');

//$server_array = ['eu2' => 1, 'na' => 2, 'eu' => 3, 'na2' => 4, 'na3' => 5];
define('IMAGE_SERVER_INFO', [
	// European imageservers s1, s3, s6
	1 => [
		'server_code' => 'eu2',
		'continent_code' => 'eu',
		'country_code' => 'fr',
	],
	3 => [
		'server_code' => 'eu',
		'continent_code' => 'eu',
		'country_code' => 'fr',
	],
	6 => [
		'server_code' => 'eu3',
		'continent_code' => 'eu',
		'country_code' => 'de', // hetzner
	],
	// Northamerican servers s2, s5
	2 => [
		'server_code' => 'na',
		'continent_code' => 'na',
		'country_code' => 'us',
	],
	5 => [
		'server_code' => 'na3',
		'continent_code' => 'na',
		'country_code' => 'us',
	],
	// Third northamerican server, used for "rest of world"
	4 => [
		'server_code' => 'na2',
		'continent_code' => '??',
		'country_code' => '',
	],
]);
define('IMAGE_SERVER_CONTINENT_MAPPING', [
	'af' => '??', // Africa
	'an' => '??', // Antarctica
	'as' => '??', // Asia
	'eu' => 'eu', // Europe
	'na' => 'na', // Northamerica
	'oc' => '??', // Oceania
	'sa' => 'na', // Southamerica
]);

define('BBCODE', [
	'bold' => 'b',
	'italic' => 'i',
	'underline' => 'u',
	'strikethrough' => 's',
	'align-left' => 'left',
	'align-center' => 'center',
	'align-right' => 'right',
	'image' => 'img',
	'link' => 'url',
	'superscript' => 'sup',
	'subscript' => 'sub',
	'list-ul' => 'ul',
	'list-ol' => 'ol',
	'arrows-alt-h' => 'hr',
	'eye-slash' => 'spoiler',
	'code' => 'code',
	'quote-left' => 'quote'
]);
define('EMOJIS', ['😀', '😁', '😂', '🤣', '😃', '😄', '😅', '😆', '😉', '😊', '😋', '😎', '😍', '😘', '😗', '😙', '😚', '🙂', '🤗']);

define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s \U\T\C');

define('RATINGS', [10 => 'Masterpiece', 9 => 'Great', 8 => 'Very good', 7 => 'Good', 6 => 'Fine', 5 => 'Average', 4 => 'Bad', 3 => 'Very bad', 2 => 'Horrible', 1 => 'Appalling']);

define('SORT_ARRAY_USERS', ['level_id DESC, username ASC', 'username ASC', 'username DESC', 'user_uploads ASC', 'user_uploads DESC', 'user_views ASC', 'user_views DESC', 'level_id ASC', 'level_id DESC', 'level_id DESC, users.user_id ASC']);
define('SORT_ARRAY_GROUPS', ['group_follows DESC, group_last_updated DESC', 'group_name ASC', 'group_name DESC', 'group_likes ASC', 'group_likes DESC', 'group_views ASC', 'group_views DESC', 'group_follows ASC', 'group_follows DESC', 'thread_posts ASC', 'thread_posts DESC', 'group_last_updated ASC', 'group_last_updated DESC']);
define('SORT_ARRAY_MANGA', ['manga_last_updated DESC', 'manga_last_updated ASC', 'manga_name ASC', 'manga_name DESC', 'thread_posts ASC', 'thread_posts DESC', 'manga_bayesian ASC', 'manga_bayesian DESC', 'manga_views ASC', 'manga_views DESC', 'manga_follows ASC', 'manga_follows DESC']);

define('TL_USER_IDS', [1001, 8963, 15294, 1179, 6394, 3816, 15102, 1454, 414, 16364, 9268, 2471, 51914]);

define('SPAM_WORDS', ['vagina', 'v@gina', 'gg.gg', 'merky.de', 'v@g!na', 'b!tch', 'f.u.ck', 's.lu.tt', '@ss']);

define('AUTO_FORUM_IDS', [11, 12, 14]);
define('CATEGORY_FORUM_IDS', [1, 2, 4, 16]);
define('THREAD_LABELS', [':planned:' => "</a> <a class='no-underline'><span class='badge badge-success'>Planned</span>",
	':maybe:' => "</a> <a class='no-underline'><span class='badge badge-warning'>Maybe</span>",
	':rejected:' => "</a> <a class='no-underline'><span class='badge badge-danger'>Rejected</span>",
	':implemented:' => "</a> <a class='no-underline'><span class='badge badge-info'>Implemented</span>",
	':fixed:' => "</a> <a class='no-underline'><span class='badge badge-success'>Fixed</span>",
	':will fix:' => "</a> <a class='no-underline'><span class='badge badge-warning'>Will fix</span>",
	':will investigate:' => "</a> <a class='no-underline'><span class='badge badge-info'>Will investigate</span>",
	':not a bug:' => "</a> <a class='no-underline'><span class='badge badge-danger'>Not a bug</span>"]);

define('DEFAULT_AVATARS', ['default1.jpg', 'default2.jpg', 'default3.jpg', 'default4.gif', 'default5.gif']);
//define('DEFAULT_AVATARS', ['mdex-sumireko.png']);

define('WALLET_QR', [
	'BTC' => [
		'17jpfzcnKrBewKpyFeRW5tVCu9zpuNkPrh',
		'18WSPrCVbBqE3fcsxmRyX4MHf7huhdkHvG',
		'1HeKYAHQfxCGxESBHH96UW6nfk3sWGMBUS',
		'1KcTeDQHDV6crBruJb6bF8WRN4oMSsRj3a',
		'1KeZFBiKWCitALEQfJWVGbq89Z3Y7epeFE',
		'1LjyyRo6jLHKR4dzfGRLyp7pb3MJUCTvbj',
		'1MqNnXqvehvNvtQL41MZx1NpCZp2NDhvHk',
		'1MxBCG2xks9mfnZKzwUnGKNnjCbh8CtFnE',
		'1MZ2uJ2YNokSKCDbdocnzoc4sDt58T7RLt',
		'1PUgG3Z1WMuZd1TZi8QZKowHMu2MZifMG8'
	],
	'ETH' => [
		'0x0BB95fE37dc1458aAc692E0E9b44F9852B2Aa6Ec',
		'0x0DfF79f78980277963f1Ded0312f03377559de68',
		'0x47ef42463B582b78eA89d92A1f1302c7091EC944',
		'0x4b17f6451F1684931E0EF70c6A9aA020E86c37B5',
		'0xC513BA4E49E57A07c1b1146B81bCb5ce01F9B270'
	],
]);
