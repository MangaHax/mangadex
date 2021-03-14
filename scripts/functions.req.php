<?php
/* mysql_escape_mimic($inp)
 * rand_string($length)
 * get_time_ago($string)
 * number_format_mod($number)
 * format_filesize($bytes)
 * reArrayFiles(&$file_post)
 * scrape_torrent($scraper, $external, $announce_url, $info_hash_array)
 * decode_torrent($torrent, $torrent_hash)
 * calc_total_transfer($completed, $size)
 * update_stats($db, $s, $l, $c, $id)
 */

/*************************************
 * General functions
 *************************************/
function is_banned_asn($ip) {
	$ch = curl_init();
	// IMPORTANT: the below line is a security risk, read https://paragonie.com/blog/2017/10/certainty-automated-cacert-pem-management-for-php-software
	// in most cases, you should set it to true
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, "https://api.iptoasn.com/v1/as/ip/$ip");
	$result = curl_exec($ch);
	curl_close($ch);
	
	$obj = json_decode($result);
	
	if (in_array($obj->as_number, BANNED_ASNS))
		return TRUE;
	else 
		return FALSE;
}
 
function bayesian_average($ratings_array, $average_no_ratings_per_title, $average_rating, $site_average_rating) {
    $w = count($ratings_array) / (count($ratings_array) + $average_no_ratings_per_title);
    return $w * $average_rating + (1 - $w) * $site_average_rating;
}

function get_browser_lang($server) {
    $lang_header = $server['HTTP_ACCEPT_LANGUAGE'] ?? 'en-GB';
    if (!preg_match('#[a-zA-Z-_]+#', $lang_header))
        $lang_header = 'en-GB';
    $array = explode('-', $lang_header);
    return $array[0];
}



function get_country_code($ip = null) {
	if (!isset($ip))
        $ip = _IP;
	
	global $ipService;
	
	$locationRecord = $ipService->getCountryRecord($ip);
	
	if (isset($locationRecord) && is_object($locationRecord)) {
		$countryCode = ($locationRecord->country ?? $locationRecord->registeredCountry ?? $locationRecord->representedCountry)->isoCode ?? '??';
	} else {
		$countryCode = '??';
	}
	
	return strtolower($countryCode);
}

function get_continent_code($ip = null) {
	if (!isset($ip))
        $ip = _IP;
	
	global $ipService;
	
	$locationRecord = $ipService->getCountryRecord($ip);
	
	if (isset($locationRecord) && is_object($locationRecord)) {
		$continentCode = $locationRecord->continent->code ?? '??';
	} else {
		$continentCode = '??';
	}
	
	return strtolower($continentCode);
}

/**
 * returns the server id for its code. codes are for example "eu", "eu2", "na", "na2", "na3"
 * @param $code
 * @return int the server id that matches its code, -1 if servercode was invalid
 */
function get_server_id_by_code($code) {
    $code = strtolower($code);

    foreach (IMAGE_SERVER_INFO AS $server_id => $server_info) {
        if ($server_info['server_code'] === $code) {
            return $server_id;
        }
    }

    return -1;
}

/**
 * returns the closest image server id by the users location with a random fallback. keeps the selected server id sticky
 * by hashing its with the ip.
 * @param null $continentCode
 * @param null $countryCode
 * @param null $serverContinentCode
 * @param null $selectedServerId
 * @return int always returns a valid server, even if region wasnt detected
 */

 
function get_server_id_by_geography($ip = null, &$continentCode = null, &$countryCode = null, &$serverContinentCode = null, &$selectedServerId = null) {

    if (!isset($ip))
        $ip = _IP;

    global $ipService;

	$locationRecord = $ipService->getCountryRecord($ip);
	if (isset($locationRecord) && is_object($locationRecord)) {
		$continentCode = $locationRecord->continent->code ?? '??';
		$countryCode = ($locationRecord->country ?? $locationRecord->registeredCountry ?? $locationRecord->representedCountry)->isoCode ?? '??';
	} else {
		$continentCode = '??';
		$countryCode = '??';
	}

    $serverContinentCode = IMAGE_SERVER_CONTINENT_MAPPING[strtolower($continentCode)] ?? '??';

    $possible_server_ids = [];

    // Collect all image servers that belong to this continent
    foreach (IMAGE_SERVER_INFO AS $server_id => $server_info) {
        if ($server_info['continent_code'] === strtolower($serverContinentCode))
            $possible_server_ids[] = $server_id;
    }

    // Collect the numeric last part of the ip xxx.xxx.xxx.123, so 123 is our hash for the sticky random server selection
    // if its ipv6, take the last bit and convert it from hex to int
    if (strpos(_IP, ':') !== false) {
        $tmp = explode(':', $ip);
        $ip_hash = (int)hexdec(end($tmp));
    } else {
        $tmp = explode('.', $ip);
        $ip_hash = (int)end($tmp);
    }

    if (empty($possible_server_ids)) {
        // Not sure when this happens, but select one at random or we get into a zero division error below
        $allServerIds = \array_keys(IMAGE_SERVER_INFO);
        return $allServerIds[\array_rand($allServerIds)];
    }

    $index = $ip_hash % count($possible_server_ids); // This returns a random sticky index for available server ids
    $selectedServerId = $possible_server_ids[$index] ?? 0;

    return $selectedServerId;
}

class Notify_Callback {
    private $post_id;

    function __construct($post_id) {
        $this->post_id = (int)$post_id;
    }

    public function notify($matches) {
        global $sql, $user, $timestamp, $memcached;

        $username = substr($matches[0], 1);
        $user_id = $sql->prep($matches[0], "SELECT user_id FROM mangadex_users WHERE username = ?", [$username], 'fetchColumn', '');

        if ($user_id) {
            // Check if the user $user_id has the current user on his blocklist.
            $mention_user = new User($user_id, 'user_id');
            $blockList = $mention_user->get_blocked_user_ids();

            // Add the notification only if this user doesnt have the post author on his blocklist
            if (!isset($blockList[$user->user_id])) {
                $sql->modify('add_notification', " INSERT IGNORE INTO mangadex_notifications (notification_id, post_id, mentioner_user_id, mentionee_user_id, timestamp, is_read) 
                VALUES (NULL, ?, ?, ?, ?, 0) ", [$this->post_id, $user->user_id, $user_id, $timestamp]);

                $memcached->delete("user_{$user_id}_unread_notifications");
                $memcached->delete("notifications_$user_id");
            }

            return "@[url=" . URL . "user/$user_id]{$username}[/url]";
        }
        else
            return $matches[0];
    }
}

function parse_template($templateName, $templateVar = [], $templateDir = 'bootstrap4')
{
    $absPath = ABSPATH . '/templates/' . $templateDir . '/' . str_replace(['../', './', '`', '´'], '', $templateName) . '.tpl.php';
    if (!file_exists($absPath))
        throw new Exception("Template $templateName not found!");

    ob_start();
    include ($absPath);
    $content = ob_get_clean();
    return $content;
}

function get_results_as_object($results, $id_name) {
    $obj = new \stdClass();
    foreach ($results as $i => $row) {
        $obj->{$i} = new \stdClass();
        foreach ($row as $key => $value) {
            $obj->{$i}->$key = $value;
        }
        $obj->{$i}->$id_name = $i;
    }

    return $obj;
}

function thread_label($name) {
    return str_replace(array_keys(THREAD_LABELS), THREAD_LABELS, $name);
}

function make_links_clickable($text) {
    $res = preg_replace("!(^|\s)((?:(?:f|ht)tp(?:s)?:\/\/)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;\/\/=]+)($|\s)!ui", "$1<a href='$2' target='_blank' rel='nofollow'>$2</a>$3", $text);
    return $res;
}

function hash_array($array) {
    return hash('crc32b', serialize($array));
}

function validate_level($user, $type) {
    $levels = [
        'guest' => 1,
        'validating' => 2,
        'member' => 3,
        'contributor' => 4,
        'gl' => 5,
        'pu' => 6,
        'pr' => 10,
        'mod' => 11,
        'gmod' => 12,
        'admin' => 15
    ];
    return isset($levels[$type]) && $user->level_id >= $levels[$type];
}

function get_ip_bans()
{
    global $sql;

    $banlist = $sql->prep('ip_banlist', "SELECT * FROM mangadex_ip_bans WHERE expires > UNIX_TIMESTAMP() ORDER BY expires ASC", [], 'fetchAll', PDO::FETCH_ASSOC, 3600);
    $ips = [];
    foreach ($banlist AS $row) {
        if ($row['expires'] > time())
            $ips[] = $row['ip'];
    }
    return $ips;
}

function sanitizePostText($text) {
    // Strip excessive amount of space/tabs/newline
    $text = preg_replace('#\s{5,}#', "\n", $text);
    // break up long words
    $match = [];
    if (preg_match_all("#[^\s]{64,}#", $text, $match, PREG_SET_ORDER)) {
        foreach ($match as $m) {
            $text = str_replace($m[0], implode(' ', str_split($m[0], 64)), $text);
        }
    }

    return $text;
}

function pagination($num_rows, $current_page, $limit, $sort = 0) {
    $array['num_rows'] = $num_rows;
    $array['current_page'] = $current_page;
    $array['limit'] = $limit;
    $array['sort'] = $sort;
    $array['offset'] = $limit * $current_page - $limit;
    $array['last_page'] = ceil($num_rows / $limit);

    if ($current_page == 1) {
        $array['previous_page'] = "-";
        $array['previous_class'] = "disabled";
    }
    else {
        $array['previous_page'] = $current_page - 1;
        $array['previous_class'] = "paging";
    }
    if ($current_page == $array['last_page']) {
        $array['next_page'] = "-";
        $array['next_class'] = "disabled";
    }
    else {
        $array['next_page'] = $current_page + 1;
        $array['next_class'] = "paging";
    }

    return $array;
}

function slugify($str) {
    $slugified = trim(preg_replace('/\W+/', '-', strtolower(html_entity_decode($str))), "-");
    if (empty($slugified)) {
        $slugified = '-';
    }
    return $slugified;
}

function prepare_in($array) {
    return count($array) > 0 ? str_repeat("?,", count($array) - 1) . "?" : "";
}

function prepare_int($number) {
    if (is_int($number))
        return $number;
    else
        die("Error: Possible SQL injection.");
}

function prepare_numeric($number) {
    if (is_numeric($number))
        return $number;
    else
        die("Error: Possible SQL injection.");
}

function prepare_identifier($ident) {
    return "`" . str_replace("`", "``", $ident) . "`";
}

function prepare_orderby($order, $allowed_orders) {
    $key = array_search($order, $allowed_orders); // see if we have such a name
    return $allowed_orders[$key]; //if not, first one will be set automatically. smart enuf :)
}

function read_dir($dir) {
	if ($dir[0] !== '/') {
		// make absolute if dir is relative
		$dir = rtrim(ABSPATH, '/') . '/' . $dir;
	}
    return array_diff(@scandir($dir, SCANDIR_SORT_ASCENDING), array('..', '.'));
}

function remove_padding($str) {
    if (ltrim($str, '0') == '' || substr(ltrim($str, '0'), 0, 1) == '.') {
        return $str;
    } else {
        return ltrim($str, '0');
    }
}

function strpos_recursive($haystack, $needle, $offset = 0, &$results = []) {
    $offset = strpos($haystack, $needle, $offset);
    if($offset === false) {
        return $results;
    } else {
        $results[] = $offset;
        return strpos_recursive($haystack, $needle, ($offset + 1), $results);
    }
}

function validate_image($file, $name = 'file', $max_filesize = MAX_IMAGE_FILESIZE) {
    $arr = explode(".", $file["name"]);
    $ext = strtolower(end($arr));
    $validate_extention = in_array($ext, ALLOWED_IMG_EXT);
    $validate_file_size = ($file["size"] <= $max_filesize); //check file size
    $validate_mime = in_array(mime_content_type($file["tmp_name"]), ALLOWED_MIME_TYPES);
    $get_image_size = getimagesize($file["tmp_name"]);

    if ($_FILES[$name]["error"])
        return display_alert("danger", "Failed", "Error Code ({$file['error']}).");
    elseif (!$validate_file_size)
        return display_alert("danger", "Failed", "File size exceeds 1 MB.");
    elseif (!$validate_extention)
        return display_alert("danger", "Failed", "A .$ext file, not an image.");
    elseif (!$validate_mime)
        return display_alert("danger", "Failed", "Image failed validation.");
    elseif (!$get_image_size)
        return display_alert("danger", "Failed", "Image cannot be processed.");
    else
        return "";
}

function get_ext($filename, $type) {
    $value = explode(".", $filename);
    if ($type)
        return strtolower(end($value));
    else
        return current($value);
}

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

/* TODO: Remove this
function mysql_escape_mimic($inp) {
    if(is_array($inp))
        return array_map(__METHOD__, $inp);

    if(!empty($inp) && is_string($inp)) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
    }
    return $inp;
}
*/

function sanitise_id($id) {
    if (preg_match("/^-?\d+$/", $id))
        return $id;
    else
        die("Error: Possible SQL injection.");
}

function send_email($to, $subject, $body, $site = 2) {
    $url = "https://mail.anidex.moe/";
    $data = [
        'site' => $site,
        'to' => $to,
        'subject' => $subject,
        'body' => $body
    ];
    $headers = [];

    httpPost($url, $data, $headers);
}
/*
function send_email($to, $subject, $body) {
	require_once (ABSPATH . "/scripts/phpmailer/src/PHPMailer.php");
	require_once (ABSPATH . "/scripts/phpmailer/src/SMTP.php");
	require_once (ABSPATH . "/scripts/phpmailer/src/Exception.php");

	$mail = new PHPMailer\PHPMailer\PHPMailer;
	$mail->SMTPDebug = false; //Enable SMTP debugging.
	$mail->isSMTP(); //Set PHPMailer to use SMTP.
	$mail->Host = SMTP_HOST; //Set SMTP host name
	$mail->SMTPAuth = true; //Set this to true if SMTP host requires authentication to send email
	$mail->Username = SMTP_USER; //Provide username and password
	$mail->Password = SMTP_PASSWORD;
	$mail->SMTPSecure = "tls"; //If SMTP requires TLS encryption then set it
	$mail->Port = SMTP_PORT; //Set TCP port to connect to
	$mail->From = SMTP_USER;
	$mail->FromName = TITLE; //From: sdbx.moe
	$mail->addBCC(SMTP_BCC); //bcc: holo@doki.co
	$mail->addReplyTo(SMTP_USER); //reply-to: mangadexstaff@gmail.com

	$mail->addAddress($to);
	$mail->Subject = $subject;
	$mail->Body = $body;

	$mail->send();
}
*/
function rand_string($length) {
    $chars = "abcdefghkmnpqrstuvwxyzABCDEFGHKMNPQRSTUVWXYZ23456789";
    return substr(str_shuffle($chars), 0, $length);
}

function rand_letter($length) {
    $chars = "abcdefghkmnqrstuvwxyzABCDEFGHKMNQRSTUVWXYZ";
    return substr(str_shuffle($chars), 0, $length);
}

function get_time_ago($ptime, $display_ago = TRUE, $maxAgeForNow = 1) {
    $etime = abs(time() - $ptime);

    if (!$ptime)
        return "Never";
    elseif ($etime < $maxAgeForNow)
        return "Now";

    $ago = ($ptime < time() && $display_ago) ? " <span class='d-none d-xl-inline'>ago</span>" : "";
    $in = ($ptime > time()) ? "in " : "";

    $a = array( 365 * 24 * 60 * 60  =>  'year',
        30 * 24 * 60 * 60  =>  'mo',
        24 * 60 * 60  =>  'day',
        60 * 60  =>  'hr',
        60  =>  'min',
        1  =>  'sec'
    );
    $a_plural = array( 'year'   => 'years',
        'mo'  => 'mo',
        'day'    => 'days',
        'hr'   => 'hrs',
        'min' => 'mins',
        'sec' => 'secs'
    );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $in . $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . $ago;
        }
    }

}

function number_format_mod($number) {
    if ($number >= 100)
        return number_format($number, 0);
    else
        return number_format($number, 1);
}

function format_filesize($bytes) {
    if ($bytes >= 1099511627776000)
        return number_format_mod($bytes / 1024 / 1024 / 1024 / 1024 / 1024) . " PB";
    elseif ($bytes >= 1073741824000)
        return number_format_mod($bytes / 1024 / 1024 / 1024 / 1024) . " TB";
    elseif ($bytes >= 1048576000)
        return number_format_mod($bytes / 1024 / 1024 / 1024) . " GB";
    elseif ($bytes >= 1024000)
        return number_format_mod($bytes / 1024 / 1024) . " MB";
    elseif ($bytes >= 1000)
        return number_format_mod($bytes / 1024) . " KB";
    elseif ($bytes >= 1)
        return $bytes . " B";
    else
        return "0 B";
}

function reArrayFiles(&$file_post) {
    $file_ary = [];
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}

function strpos_arr($haystack, $needle) {
    if (!is_array($needle))
        $needle = array($needle);
    foreach($needle as $what) {
        if (($pos = strpos($haystack, $what)) !== false)
            return $pos;
    }

    return false;
}

/* TODO: remove
function get_data($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
*/

/*
function download_file($url, $filepath) {
	set_time_limit(0);

	$file = fopen("$filepath", "w+");

	$curl = curl_init($url);

	// Update as of PHP 5.4 [] can be written []
	curl_setopt_array($curl, [
		CURLOPT_URL            => $url,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FILE           => $file,
		CURLOPT_TIMEOUT        => 50,
		CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)'
	]);

	curl_exec($curl);
	curl_close($curl);
}
*/

function httpPost($url, $data, $headers) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $r = curl_exec($curl);
    curl_close($curl);
    return $r;
}

function httpGet($url, $str) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url . $str);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 3);
    $r = curl_exec($curl);
    curl_close($curl);
    return $r;
}

function rrmdir($dir) { //recursive delete folder
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir."/".$object))
                    rrmdir($dir."/".$object);
                else
                    @unlink($dir."/".$object);
            }
        }
        rmdir($dir);
    }
}



/*************************************
 * MangaDex functions
 *************************************/

function remove_blocked_groups($chapters_array, $blocked_group_ids) {
	foreach ($chapters_array as $key => $chapter) {
		if (in_array($chapter['group_id'], array_keys($blocked_group_ids)))
			unset($chapters_array[$key]);
	}
	return $chapters_array;
}

function get_og_tags($page, $id) {
    global $sql, $parser;

    $id = (int)$id;

    $array['keywords'] = 'MangaDex, Manga Dex, Manga, Read Manga, Manga Online, Read Manga Free, Manga Scans, Free Manga, Read Manga Online';
    $array['image'] = URL . "images/misc/default_brand.png?1";
    $array['description'] = DESCRIPTION;
    $array['title'] = TITLE;
    $array['canonical'] = '';

    switch ($page) {
        case "main":
            $array['title'] = "Latest updates - " . TITLE;
            $array['canonical'] = "<link rel='canonical' href='" . URL . "' />";
            break;

        case "search":
            $array['title'] = "Search - " . TITLE;
            $array['canonical'] = "<link rel='canonical' href='" . URL . "search' />";
            break;

        case "chapter":
            $chapter = $sql->prep("og_chapter_$id", ' 
				SELECT mangas.manga_name, mangas.manga_id, mangas.manga_image, mangas.manga_description, chapters.volume, chapters.chapter, chapters.title 
				FROM mangadex_chapters AS chapters
				LEFT JOIN mangadex_mangas AS mangas
					ON chapters.manga_id = mangas.manga_id 
				WHERE chapters.chapter_id = ? 
				LIMIT 1', [$id], 'fetch', PDO::FETCH_OBJ, 3600);

            if ($chapter) {
                $parser->parse($chapter->manga_description);
                $array['keywords'] = "$chapter->manga_name Chapter $chapter->chapter, $chapter->manga_name Volume $chapter->volume, $chapter->manga_name, $chapter->manga_name Manga, Read $chapter->manga_name online, $chapter->manga_name online For Free, Read $chapter->manga_name chapters for free, $chapter->manga_name chapters, $chapter->manga_name scans, $chapter->manga_name mangadex";

                $array['title'] = (($chapter->volume) ? "Vol. $chapter->volume " : "" ).(($chapter->chapter) ? "Ch. $chapter->chapter " : "").((!$chapter->volume && !$chapter->chapter) ? "$chapter->title " : "" )."($chapter->manga_name) - " . TITLE;
                $array['image'] = URL . "images/manga/$chapter->manga_id.thumb.jpg";
                $array['description'] = $parser->getAsText();
                $array['canonical'] = "<link rel='canonical' href='" . URL . "chapter/$id' />";
            }
            break;

        case "genre":
            $genre = $sql->prep("og_genre_$id", ' SELECT * FROM mangadex_genres WHERE genre_id = ? LIMIT 1 ', [$id], 'fetch', PDO::FETCH_OBJ, 3600);

            if (isset($genre->genre_id)) {
                $array['title'] = "$genre->genre_name (Genre) - " . TITLE;
                $array['description'] = $genre->genre_description;
                $array['canonical'] = "<link rel='canonical' href='" . URL . "genre/$genre->genre_id/" . slugify($genre->genre_name) . "' />";
            }
            break;

        case "manga":
        case "title":
            $manga = $sql->prep("og_manga_$id", ' SELECT manga_name, manga_id, manga_description FROM mangadex_mangas WHERE manga_id = ? LIMIT 1 ', [$id], 'fetch', PDO::FETCH_OBJ, 3600);

            if (isset($manga->manga_id)) {
                $parser->parse($manga->manga_description);
                $array['keywords'] = "$manga->manga_name, $manga->manga_name Manga, Read $manga->manga_name online, $manga->manga_name online, $manga->manga_name online For Free, Read $manga->manga_name chapters for free, $manga->manga_name series, $manga->manga_name chapters, $manga->manga_name scans, $manga->manga_name mangadex";

                $array['title'] = "$manga->manga_name (Title) - " . TITLE;
                $array['image'] = URL . "images/manga/$manga->manga_id.thumb.jpg";
                $array['description'] = $parser->getAsText();
                $array['canonical'] = "<link rel='canonical' href='" . URL . "title/$manga->manga_id/" . slugify($manga->manga_name) . "' />";
            }
            break;

        case "user":
            $user = $sql->prep("og_user_$id", ' SELECT user_id, username, avatar FROM mangadex_users WHERE user_id = ? LIMIT 1 ', [$id], 'fetch', PDO::FETCH_OBJ, 3600);

            if (isset($user->user_id)) {
                $array['title'] = "$user->username (User) - " . TITLE;
                $array['image'] = ($user->avatar) ? URL . "images/avatars/$user->user_id.$user->avatar" : URL . 'images/avatars/xmas' . (($id % 2) + 1) . '.png';
                $array['canonical'] = "<link rel='canonical' href='" . URL . "user/$user->user_id/" . slugify($user->username) . "' />";
            }
            break;

        case "list":
            $user = $sql->prep("og_list_user_$id", ' SELECT user_id, username, avatar FROM mangadex_users WHERE user_id = ? LIMIT 1 ', [$id], 'fetch', PDO::FETCH_OBJ, 3600);
            $array['title'] = "$user->username's MDList - " . TITLE;
            $array['image'] = ($user->avatar) ? URL . "images/avatars/$user->user_id.$user->avatar" : URL . 'images/avatars/' . rand(1,3) . '.jpg';
            $array['canonical'] = "<link rel='canonical' href='" . URL . "list/$user->user_id/' />";
            break;

        case "group":
            $group = $sql->prep("og_group_$id", ' SELECT group_id, group_name FROM mangadex_groups WHERE group_id = ? LIMIT 1 ', [$id], 'fetch', PDO::FETCH_OBJ, 3600);

            if (isset($group->group_id)) {
                $array['title'] = "$group->group_name (Group) - " . TITLE;
                $array['canonical'] = "<link rel='canonical' href='" . URL . "group/$group->group_id/" . slugify($group->group_name) . "' />";
            } else {
                $array['title'] = 'Group not found';
            }
            break;

        case "forum":
            $forum_name = $sql->prep("og_forum_$id", ' SELECT forum_name FROM mangadex_forums WHERE forum_id = ? LIMIT 1 ', [$id], 'fetchColumn', '', 3600);
            $array['title'] = "$forum_name (Forum) - " . TITLE;
            break;

        case "thread":
            $thread_name = $sql->prep("og_thread_$id", ' SELECT thread_name FROM mangadex_threads WHERE thread_id = ? LIMIT 1 ', [$id], 'fetchColumn', '', 3600);
            $array['title'] = "$thread_name (Thread) - " . TITLE;
            break;

        case "titles":
            $array['title'] = "Manga titles - " . TITLE;
            break;

        default:
            $array['title'] = ($page) ? ucfirst(str_replace("_", " ", $page)) . " - " . TITLE : TITLE;
            break;
    }

    return $array;
}

function generate_thumbnail($file, $large) {

    if (file_exists ($file)) {
        $thumbFile = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);
        //$thumbFile = pathinfo($file, PATHINFO_FILENAME);

        $thumbFile .= '.thumb.jpg';

        // Setting the resize parameters
        list($width, $height) = getimagesize($file);

        // Some broken images may return an invalid height/width. return, so we dont run into a division-by-zero error
        if ($width < 1 || $height < 1 || $width + $height > 10000)
            return;

        $modwidth = 100;
        $modheight = $modwidth / $width * $height;

        // Creating the Canvas
        $tn= imagecreatetruecolor($modwidth, $modheight);

        $type = exif_imagetype($file);
        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = ImageCreateFromJPEG($file);
                break;

            case IMAGETYPE_PNG:
                $image = ImageCreateFromPNG($file);
                break;

            case IMAGETYPE_GIF:
                $image = ImageCreateFromGIF($file);
                break;

            default:
                exit;
                break;
        }

        if ($image) {
            // Resizing our image to fit the canvas
            @imagecopyresampled($tn, $image, 0, 0, 0, 0, $modwidth, $modheight, $width, $height);

            // Save to file
            @imagejpeg($tn, $thumbFile, 85);

            //Free memory
            @imagedestroy($tn);
        }

        if ($large) {
            $thumbFile = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);

            if ($large == 1)
                $thumbFile .= '.large.jpg';
            else
                $thumbFile .= ".$large.jpg";

            // Setting the resize parameters
            list($width, $height) = getimagesize($file);

            if ($large == 1)
                $modwidth = 150;
            else
                $modwidth = $large;

            $modheight = $modwidth / $width * $height;

            // Creating the Canvas
            $tn= imagecreatetruecolor($modwidth, $modheight);

            $type = exif_imagetype($file);
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $image = ImageCreateFromJPEG($file);
                    break;

                case IMAGETYPE_PNG:
                    $image = ImageCreateFromPNG($file);
                    break;

                case IMAGETYPE_GIF:
                    $image = ImageCreateFromGIF($file);
                    break;

                default:
                    exit;
                    break;
            }

            if ($image) {
                // Resizing our image to fit the canvas
                @imagecopyresampled($tn, $image, 0, 0, 0, 0, $modwidth, $modheight, $width, $height);

                // Save to file
                @imagejpeg($tn, $thumbFile, 85);

                //Free memory
                @imagedestroy($tn);
            }

        }
    }
}

function redirect_url($target, $httpcode=303)
{
    if ($target{0} !== '/' && !stripos($target, DOMAIN)) {
        die('Possible XSS Attack: Invalid redirection URL! Follow at your own risk: '.$target);
    }
    http_response_code($httpcode);
    header('location: '.$target);
    die();
}

/*************************************
 * Update database
 *************************************/

function update_cron_logs($type, $result) {
    global $sql;
    $sql->modify('update_cron_logs', " INSERT INTO mangadex_logs_cron (id, timestamp, type, result) VALUES (NULL, UNIX_TIMESTAMP(), ?, ?) ", [$type, $result]);
}

function visit_log_cumulative($ip, $table = "visit") {
    global $sql;
    $field = prepare_identifier("log_$table");
    $sql->modify('visit_log_cumulative', " INSERT INTO mangadex_logs (log_ip, log_visit, log_dl, log_rss, log_timestamp) VALUES (?, 0, 0, 0, UNIX_TIMESTAMP())
		ON DUPLICATE KEY UPDATE $field = $field + 1 ", [$ip]);
}

function condenseUA($ua_str) {
    $user_agents = ['AhrefsBot', 'bingbot', 'Googlebot', 'YandexBot', 'Android', 'iPad', 'iPhone', 'Macintosh', 'Windows', 'RSS', 'Linux', 'CrOS'];
    foreach ($user_agents as $ua) {
        if (strpos($ua_str, $ua) !== false)
            $string = $ua;
    }
    $string = $string ?? $ua_str;
    return $string;
}

function visit_log($server, $ip, $user_id, $hentai_toggle = 0, $table = "visits") {
    global $sql;
    $timestamp = time();
    $query_string = (isset($server['QUERY_STRING'])) ? substr(str_replace(['page=', '&id=', '&p=', '&mode=', '&type='], ['/', '/', '/', '/', '/'], $server['QUERY_STRING']), 0, 255) : "";
    $referer = (isset($server['HTTP_REFERER'])) ? substr(str_replace(['https://mangadex.org', 'https://www.mangadex.org'], ['', ''], $server['HTTP_REFERER']), 0, 255) : "";
    $user_agent = (isset($server['HTTP_USER_AGENT'])) ? substr($server['HTTP_USER_AGENT'], 0, 255) : "";

    $user_agent_string = condenseUA($user_agent);

    $sql->modify('visit_log', " INSERT INTO mangadex_logs_$table (visit_id, visit_ip, visit_user_id, visit_user_agent, visit_referrer, visit_timestamp, visit_page, visit_h_toggle) 
		VALUES (NULL, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?) ", [$ip, $user_id, $user_agent_string, $referer, $query_string, $hentai_toggle]);

    global $memcached;
    $cache = $memcached->get($ip);

    if ($cache === FALSE || $timestamp - $cache[1] > 600) {
        $memcached->set($ip, [1, $timestamp]);
    }
    else {
        $memcached->set($ip, [$cache[0] + 1, $cache[1]]);
    }
}

function visit_log_api($server, $ip, $user_id, $hentai_toggle = 0) {
    global $sql;
    $query_string = (isset($server['QUERY_STRING'])) ? substr(str_replace(['page=', '&id=', '&p=', '&mode=', '&type='], ['/', '/', '/', '/', '/'], $server['QUERY_STRING']), 0, 255) : "";
    $referer = (isset($server['HTTP_REFERER'])) ? substr(str_replace(['https://mangadex.org', 'https://mangadex.com'], ['', ''], $server['HTTP_REFERER']), 0, 255) : "";
    $user_agent = (isset($server['HTTP_USER_AGENT'])) ? substr($server['HTTP_USER_AGENT'], 0, 255) : "";

    $user_agent_string = condenseUA($user_agent);

    $sql->modify('visit_log_api', " INSERT INTO mangadex_logs_api (visit_id, visit_ip, visit_user_id, visit_user_agent, visit_referrer, visit_timestamp, visit_page, visit_h_toggle) 
		VALUES (NULL, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?) ", [$ip, $user_id, $user_agent_string, $referer, $query_string, $hentai_toggle]);
}

function update_views_v2($type, $id, $ip, $user_id = 0) {
    global $memcached, $sql;

    $name = "{$type}_{$id}_$ip";

    $cache = $memcached->get($name);

    if ($cache === FALSE) {
        $memcached->set($name, TRUE, 3600);

        $table = prepare_identifier("mangadex_{$type}s");
        $field = prepare_identifier("{$type}_views");
        $field_id = prepare_identifier("{$type}_id");
        $sql->modify('update_views_v2', " UPDATE $table SET $field = $field + 1 WHERE $field_id = ? LIMIT 1 ", [$id]);
        if ($type == "chapter") {
            $sql->modify('update_views_v2', " INSERT INTO mangadex_chapter_live_views (timestamp, chapter_id, ip) VALUES (UNIX_TIMESTAMP(), ?, ?) ", [$id, $ip]);

            if ($user_id)
                $sql->modify('update_views_v2', " UPDATE mangadex_user_stats SET chapters_read = chapters_read + 1 WHERE user_id = ? ", [$user_id]);
        }
    }
}

function process_user_limit($limit = 600, $prefix = '', $reset_seconds = 600, $expire_seconds = 86400)
{
    global $memcached;

    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot') !== FALSE) {
        // Perform a dns lookup
        try {
            $hostname = gethostbyaddr(_IP);
            if (stripos($hostname, '.googlebot.com') === false && stripos($hostname, '.google.com') === false) {
                throw new \RuntimeException(sprintf('Possible googlebot useragent faking. Please check if IP "%s" and host "%s" are legit for the useragent "%s"', _IP, $hostname, $_SERVER['HTTP_USER_AGENT']), E_USER_ERROR);
            }
        } catch (\RuntimeException $e) {
            die();
        } catch (\Throwable $e) {
            trigger_error(sprintf('Exception thrown during googlebot reverse dns lookup: %s', $e->getMessage()), E_USER_WARNING);
        }
    } else {
        // limit everyone else
        $ip = _IP;
        $visit_count = $memcached->get($prefix.$ip);

        if (!(defined('DISABLE_HITCOUNTER') && DISABLE_HITCOUNTER) && $visit_count !== FALSE && $visit_count[0] > $limit) {
            return false;
        }

        // Update limits
        if ($visit_count === false || time() - $visit_count[1] > $reset_seconds) {
            $memcached->set($prefix.$ip, [1, time()], $expire_seconds);
        } else {
            $memcached->set($prefix.$ip, [$visit_count[0] + 1, $visit_count[1]], $expire_seconds);
        }
    }

    return true;
}

function get_zip_originalsize($filename) {
	$size = 0;
	$resource = zip_open($filename);
	while ($dir_resource = zip_read($resource)) {
		$size += zip_entry_filesize($dir_resource);
	}
	zip_close($resource);

	return $size;
}

/*************************************
 * Discord webhook
 *************************************/
function post_on_discord($webhookUrl, $hookObject) {
    if ($webhookUrl) {
        // Convert to json
        $hookObject = json_encode($hookObject, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        // Prepare
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $webhookUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $hookObject,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true
        ]);

        // Post
        try {
            curl_exec($ch);
            if (curl_errno($ch)) {
                trigger_error(curl_error($ch), E_USER_WARNING);
            }
            curl_close($ch);
        } catch (\Throwable $e) {
            // just consume the error
        }
    }
}
