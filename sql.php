<?php
if (PHP_SAPI !== 'cli')
   die();

require_once ('/var/www/mangadex.org/bootstrap.php'); //must be like this

require_once (ABSPATH . "/scripts/header.req.php");


/*
for ($id = 0; $id < 2491159; $id++) {
    $memcached->delete("user_{$id}_friends_user_ids");
    $memcached->delete("user_{$id}_pending_friends_user_ids");
    $memcached->delete("user_{$id}_friends_user_ids");
    $memcached->delete("user_{$id}_pending_friends_user_ids");
    if ($id % 1000 === 0) echo ".";
}
die("\nend\n");
*/

/*
$result = $sql->query_read('x', " SELECT COUNT(*) AS `Rows`, `user_id` FROM `mangadex_clients` where approved = 1 GROUP BY `user_id` ORDER BY `user_id`   ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($result as $row) {
	
	$subject = "Please update your MD@H client to 1.1.5";
				$message = "Hello,\n\n" .
					"The MD@H client has been updated to 1.1.5. Please check the [url=https://mangadex.org/md_at_home/clients]My clients[/url] page for the download link and update as soon as possible. [b]Clients earlier than 1.0.0 will be paused on the network.[/b] Please ignore this message if you have already upgraded.";

				$sender_id = 1; // Default MangaDex user; TODO: remove hardcoded value?
				$recipient_id = $row['user_id'];

				$thread_id = $sql->modify('msg_send', ' INSERT INTO mangadex_pm_threads (thread_id, thread_subject, sender_id, recipient_id, thread_timestamp, sender_read, recipient_read, sender_deleted, recipient_deleted) 
				VALUES (NULL, ?, ?, ?, UNIX_TIMESTAMP(), 1, 0, 0, 0) ', [$subject, $sender_id, $recipient_id]);

				$sql->modify('msg_send', ' INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text) 
				VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$thread_id, $sender_id, $message]);

				$memcached->delete("user_{$recipient_id}_unread_msgs");
				print $row['user_id'] . ' ';
				
		$email = $sql->query_read('x', " SELECT email FROM mangadex_users where user_id = {$row['user_id']} LIMIT 1 ", 'fetchColumn', '', -1);
	$username = $sql->query_read('x', " SELECT username FROM mangadex_users where user_id = {$row['user_id']} LIMIT 1 ", 'fetchColumn', '', -1);
	$to = $email;
		
	$subject = "Please update your MD@H client to 1.1.5 - $username";
	$body = "Hello,\n\n" .
"The MD@H client has been updated to 1.1.5. Please check this page: https://mangadex.org/md_at_home/clients for the download link and update as soon as possible. Clients earlier than 1.0.0 will be paused on the network. Please ignore this message if you have already upgraded.

In addition, if you have been approved and have yet to connect, please do so as soon as possible. Clients that do not connect within 3 days of being approved will have their secret revoked and they will have reapply for a new secret. ";
	
	print $to . " " . $subject . " " . $body . "\n\n";
	send_email($to, $subject, $body); 
				
}
*/
//$subsubdomain = $mdAtHomeClient->getServerUrl($chapter->chapter_hash, explode(',', $chapter->page_order), _IP);
/*
$to = 'customerservice@chinanetdomain.org';
$subject = "MangaDex domain registration";
$body = "Dear sirs, you recently emailed one of my staff members and said that 'Kaiqian Ltd' is attempting to register the following domains: mangadex.cn, mangadex.com.cn, mangadex.net.cn, mangadex.org.cn. You wanted to know whether my company has a connection with this Chinese company. I would like to confirm that this company is NOT related to my company, and we would object to 'Kaiqian Ltd' registering those domain names. We are currently in control of mangadex.com, mangadex.org, mangadex.co.uk, mangadex.uk and mangadex.dev. In fact, if possible, we would like to register mangadex.cn, mangadex.com.cn, mangadex.net.cn and mangadex.org.cn. Kind regards, MangaDex. ";

send_email($to, $subject, $body, 4); 
		*/
/*
$mat = [
	'M1-H.png' => 0,
	'M2-H.png' => 0,
	'M3-H.png' => 0,
	'M4-H.png' => 0,
	'M5-H.png' => 0,
	'M6-H.png' => 0,
	'M7-H.png' => 0,
	'M2-V.png' => 0,
	'M3-V.png' => 0,
	'M4-V.png' => 0,
	'M5-V.png' => 0,
	'M6-V.png' => 0,
	'M7-V.png' => 0
];

$orders = $sql->query_read('x', " SELECT * FROM mangadex_orders ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($orders as $order) {
	$order_array = json_decode($order['items'], true);
	
	foreach ($order_array as $key => $value) {
		if ($value) {
			$key = str_replace('_', '.', $key); 
			$mat[$key] += $value;
		}
	}
	
	
}

print_r($mat);
*/

/*
$names = [
	'M1-H.png' => 'My Neighbour MangaDex (Horizontal)',
	'M2-H.png' => 'Anniversary (Horizontal)',
	'M3-H.png' => 'Vaporwave (Horizontal)',
	'M4-H.png' => 'Stylized White (Horizontal)',
	'M5-H.png' => 'Stylized Black (Horizontal)',
	'M6-H.png' => 'Simple Logo (Horizontal)',
	'M7-H.png' => 'Banana (Horizontal)',
	'M2-V.png' => 'Anniversary (Vertical)',
	'M3-V.png' => 'Vaporwave (Vertical)',
	'M4-V.png' => 'Stylized White (Vertical)',
	'M5-V.png' => 'Stylized Black (Vertical)',
	'M6-V.png' => 'Simple Logo (Vertical)',
	'M7-V.png' => 'Banana (Vertical)'
];

foreach ($orders as $order) {
	$email = $sql->query_read('x', " SELECT email FROM mangadex_users where user_id = {$order['user_id']} LIMIT 1 ", 'fetchColumn', '', -1);
	$username = $sql->query_read('x', " SELECT username FROM mangadex_users where user_id = {$order['user_id']} LIMIT 1 ", 'fetchColumn', '', -1);
	$to = $email;
	
	$order_array = json_decode($order['items'], true);
	$string = '';
	foreach ($order_array as $key => $value) {
		if ($value) {
			$key = str_replace('_', '.', $key); 
			$string .= $names[$key] . ": " . $value . "\n";
		}
	}
	
	$subject = "MangaDex Shop Order Confirmation: Order {$order['order_id']} - $username";
	$body = "Thank you for buying mousemat(s) from our shop. \n\n Sorry for the delay in getting these confirmation emails sent out. \n\nYour order contains the following items: \n\n$string \n\n Please confirm your name and shipping address by replying to this email.\n\n You have chosen to pay by " . PAYMENT_METHODS[$order['payment']] . ". The invoice/instructions for payment will follow in a subsequent email.\n\n Thanks for your support! \n\nHolo (mangadexstaff@gmail.com)";
	
	print $to . " " . $subject . " " . $body . "\n\n";
	send_email($to, $subject, $body); 
}

*/

/*
$txs = $sql->query_read('x', " SELECT * FROM mangadex_user_paypal ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($txs as $tx) {
	$sql->modify('x', " update mangadex_user_paypal set count = (select count(*) from mangadex_user_transactions_v2 where email like ?) WHERE paypal like ? LIMIT 1 
	", [$tx['paypal'], $tx['paypal']]);
	print $tx['user_id'];
}	
*/

/*
$txs = $sql->query_read('x', " SELECT * FROM mangadex_user_paypal WHERE count > 0 ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($txs as $tx) {
	$sql->modify('x', " update mangadex_users set premium = 1 WHERE user_id LIKE ? LIMIT 1 
	", [$tx['user_id']]);
	print $tx['user_id'] . ' ';
}
*/

/*
$txs = $sql->query_read('x', " SELECT * FROM mangadex_user_transactions_v2 ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($txs as $tx) {
	$sql->modify('x', " update mangadex_user_transactions_v2 set user_id = (SELECT user_id FROM mangadex_user_paypal WHERE paypal LIKE ? LIMIT 1) WHERE transaction_id = ? LIMIT 1 
	", [$tx['email'], $tx['transaction_id']]);
	print $tx['transaction_id'] . ' ';
}
*/
/*
$joined_timestamp = $sql->query_read('x', " SELECT joined_timestamp FROM mangadex_users WHERE joined_timestamp >= 1597622400 ", 'fetchAll', PDO::FETCH_COLUMN, -1);
	
foreach($joined_timestamp as $value) {
	$date = date('Y-m-d', $value);
	
	$sql->modify('x', " 
	INSERT INTO mangadex_stats_registrations (date, users) VALUES (?, 1) ON DUPLICATE KEY UPDATE users = users + 1; ", [$date]);
	print $date;
}
*/
/*
$user_id = 1756;

for ($i = 130000; $i < 135000; $i++) {

	$ch_ids = $sql->query('x', " SELECT chapter_id FROM mangadex_chapter_views WHERE user_id = $i ", 'fetchAll', PDO::FETCH_COLUMN, -1);
	foreach($ch_ids as $value) {
		$data = $sql->query('x', " SELECT manga_id, chapter FROM mangadex_chapters WHERE chapter_id = $value", 'fetchAll', PDO::FETCH_ASSOC, -1);
		
		if ((isset($array[$data[0]['manga_id']]) && $array[$data[0]['manga_id']] < $data[0]['chapter']) || !isset($array[$data[0]['manga_id']]))
			$array[$data[0]['manga_id']] = min((int)$data[0]['chapter'], 9999);
		
	}
	
	if (isset($array)) {
		foreach($array as $manga_id => $chapter) {
			$sql->modify('increment_chapter', " UPDATE mangadex_follow_user_manga SET chapter = ? WHERE manga_id = ? AND user_id = ? LIMIT 1 ", [$chapter, $manga_id, $i]);
		}
		unset($array);
		$memcached->delete("user_{$i}_followed_manga_ids");
		print $i . "<br>";
	}
}*/
/*
$last_timestamp = $sql->query('last_timestamp', " SELECT visit_timestamp FROM mangadex_logs_visits ORDER BY visit_timestamp ASC LIMIT 1 ", 'fetchColumn', '', -1) + 3600; 

for($i = $last_timestamp; $i < ($last_timestamp + 3600); $i+=3600) {

	$views_guests = $sql->query('views_guests', " SELECT count(*) FROM mangadex_logs_visits WHERE visit_timestamp >= ($i - 3600) AND visit_timestamp < $i AND visit_user_id = 0 ", 'fetchColumn', '', -1); 
	$views_logged_in = $sql->query('views_logged_in', " SELECT count(*) FROM mangadex_logs_visits WHERE visit_timestamp >= ($i - 3600) AND visit_timestamp < $i AND visit_user_id > 0 ", 'fetchColumn', '', -1); 

	$users_guests = $sql->query('users_guests', " SELECT COUNT(*)  FROM (SELECT `visit_user_id` FROM `mangadex_logs_visits` WHERE visit_timestamp >= ($i - 3600) AND visit_timestamp < $i AND visit_user_id = 0 GROUP BY `visit_ip`) AS `TABLE` ", 'fetchColumn', '', -1); 
	$users_logged_in = $sql->query('users_logged_in', " SELECT COUNT(*)  FROM (SELECT `visit_user_id` FROM `mangadex_logs_visits` WHERE visit_timestamp >= ($i - 3600) AND visit_timestamp < $i AND visit_user_id > 0 GROUP BY `visit_user_id`) AS `TABLE` ", 'fetchColumn', '', -1); 

	$sql->modify('insert', ' INSERT INTO `mangadex_logs_visits_summary` (`id`, `timestamp`, `users_guests`, `users_logged_in`, `views_guests`, `views_logged_in`) VALUES (NULL, ?, ?, ?, ?, ?) ', [$i, $users_guests, $users_logged_in, $views_guests, $views_logged_in]);
	
	$sql->modify('delete', ' DELETE FROM `mangadex_logs_visits` WHERE visit_timestamp >= (? - 3600) AND visit_timestamp < ? ', [$i, $i]);

}
*/

/*
$results = $db->get_results(" SELECT mangadex_users.user_id, mangadex_users.level_id FROM `mangadex_forum_posts` LEFT JOIN mangadex_users ON mangadex_users.user_id = mangadex_forum_posts.user_id WHERE `thread_id` = 5654 and level_id < 4  ");

foreach ($results as $row) {
	
	print "UPDATE mangadex_users SET level_id = 4 WHERE user_id = $row->user_id LIMIT 1; <br />";
}
*/

/*

$results = $db->get_results(" SELECT * FROM `mangadex_users` WHERE `level_id` = 2 and user_id >= 1100 and user_id < 1649 "); 
foreach ($results as $row) {
	
	$to = $row->email;
	$subject = "MangaDex: Account Activation - $username";
	$body = "Thank you for creating an account on MangaDex. \n\nUsername: $row->username \n\nActivation code: $row->activation_key \n\nPlease visit https://mangadex.org/activation/$row->activation_key to activate your account. Unactivated accounts will be purged at some point in the future.";
	
	send_email($to, $subject, $body); 
	print $row->user_id . "<br />";
	
}
*/

/*
$results = $db->get_results(" SELECT mangadex_comments_manga.*, thread_id FROM `mangadex_comments_manga` LEFT JOIN mangadex_mangas ON mangadex_mangas.manga_id = mangadex_comments_manga.manga_id"); 

foreach ($results as $ro) {
	print $ro->comment_id . " " . $ro->text . "<br />";
	
	$pdo->prepare(" INSERT IGNORE INTO mangadex_forum_posts (post_id, thread_id, user_id, timestamp, edit_timestamp, text, deleted) VALUES (NULL, ?, ?, ?, ?, ?, ?) ")
			->execute([$ro->thread_id, $ro->user_id, $ro->timestamp, $ro->edit_timestamp, $ro->text, $ro->deleted]);
	
	
	
	$db->query(" DELETE FROM mangadex_comments_manga WHERE manga_id = $ro->manga_id");
	
	$results2 = $db->get_results(" SELECT * FROM `mangadex_comments_manga` WHERE `manga_id` = $ro->manga_id "); 
	
	
	foreach ($results2 as $row) {
		
		print $row->text . "<br />";
		
		$db->query(" INSERT INTO mangadex_forum_posts (post_id, thread_id, user_id, timestamp, edit_timestamp, text, deleted) VALUES (NULL, $ro->thread_id, $row->user_id, $row->timestamp, $row->edit_timestamp, '$row->text', $row->deleted)");
	}
	
	
	
}
*/

/*

$dir    = '/var/www/mangadex.org/data/';
$files = array_diff(scandir($dir), array('..', '.'));

foreach ($files as $file) {
	
	$chapter_id = $sql->query_read('chapter_id', " SELECT chapter_id FROM mangadex_chapters WHERE chapter_hash LIKE '$file' ", 'fetchColumn', '', -1); 
	if (!$chapter_id) {
		print $file . " - $chapter_id\n";
		
		//$sql->modify('update', " UPDATE mangadex_chapters SET server = 1 WHERE chapter_id = ? LIMIT 1; ", [$chapter_id]);
		//$memcached->delete("chapter_$chapter_id");
		
		rename("/var/www/mangadex.org/data/$file", "/var/www/mangadex.org/delete/$file"); 
		
	}
}

*/

/*
$results = $db->get_results(" SELECT * FROM mangadex_import GROUP BY user_id  "); 

foreach ($results as $i => $row) {
	$insert = "";
	$search = '"comic_id":"';
	$string = $row->json;
	
	$found = strpos_recursive($string, $search);

	if($found) {
		foreach($found as $pos) {
			$start = $pos + 12;
			$end = strpos($row->json, '"', $start);
			$diff = $end - $start;
			$substr = substr($row->json, $start, $diff);
			$substr = sanitise_id($substr);
			$insert .= "($row->user_id, $substr),";
			
		}   
	} 
	
	$insert = rtrim($insert,",");
	
	$db->query( "INSERT IGNORE INTO mangadex_follow_user_manga (user_id, manga_id) VALUES $insert;" );
	$db->query( "DELETE FROM mangadex_import WHERE user_id = $row->user_id" );
}
	*/
/*
	$follows_array = json_decode($row->json);
	
	if (isJSON($row->json)) {
		print $row->user_id . " ";
			
		foreach ($follows_array as $manga) {				
			$count = $db->get_var(" SELECT count(*) FROM mangadex_follow_user_manga WHERE user_id = $row->user_id AND manga_id = $manga->comic_id ");
			if (!$count) {
				$db->query(" INSERT INTO mangadex_follow_user_manga (id, user_id, manga_id) VALUES (NULL, $row->user_id, $manga->comic_id); ");
				
				//$follows = $db->get_var(" SELECT count(*) FROM mangadex_follow_user_manga WHERE manga_id = $manga->comic_id "); 
				//$db->query(" UPDATE mangadex_mangas SET manga_follows = $follows WHERE manga_id = $id LIMIT 1; ");
			}
		}
	
		
	}
	else print "[not JSon $row->user_id]";
*/
 

/*
$url = ABSPATH . '/relational-data.json'; // path to your JSON file
$data = file_get_contents($url); // put the contents of the file into a variable
$array = json_decode($data); // decode the JSON feed

foreach ($array as $manga) { 
	$db->query(" UPDATE mangadex_mangas SET manga_image = '$manga->filename' WHERE manga_id = $manga->id LIMIT 1; ");
	
}
*/
/*
$results = $sql->query_read('x', " SELECT * FROM mangadex_users where level_id = 0 and user_id > 1900000 order by user_id desc ", 'fetchAll', PDO::FETCH_ASSOC, -1);
foreach ($results as $row) {
	$uid = $row['user_id'];
	
	$sql->modify('m', "DELETE FROM mangadex_pm_threads WHERE sender_id = ?", [$uid]);
	$sql->modify('m', "DELETE FROM mangadex_pm_msgs WHERE user_id = ?", [$uid]);
	print $uid . ' ';
	
}
*/
/*
foreach (WALLET_QR['ETH'] as $qr) {
	print "Fetching $qr\n\n";
	
	$ch = curl_init();
	// IMPORTANT: the below line is a security risk, read https://paragonie.com/blog/2017/10/certainty-automated-cacert-pem-management-for-php-software
	// in most cases, you should set it to true
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, "http://api.etherscan.io/api?module=account&action=txlist&address=$qr&startblock=0&endblock=99999999&sort=asc&apikey=V8NYD3PUSYCNJVQYMGFF45GB9V55N3JC32");
	$result = curl_exec($ch);
	curl_close($ch);
	
	print "Retrieved $qr\n\n";
	
	$obj = json_decode($result);
	
	foreach ($obj->result as $id => $tx) {
				
		$balance = $tx->value;
		$from = $tx->from;
		$to = $tx->to;
		$hash = $tx->hash;
		
		$timestamp = $tx->timeStamp;
		
		$count = $sql->query_read('views_guests', " SELECT count(*) FROM mangadex_transactions_eth WHERE hash LIKE '$hash' LIMIT 1 ", 'fetchColumn', '', -1); 
		print "$hash: $count\n\n";
		if (!$count && $from != strtolower($qr)) {
			$sql->modify('update', " INSERT IGNORE INTO mangadex_transactions_eth (timestamp, hash, sender_address, recipient_address, value) VALUES (?, ?, ?, ?, ?); ", [$timestamp, $hash, $from, $to, $balance]);
			
			print "inserted $hash\n\n";
		}
		
	}
	sleep(1);
}
*/
/*
$txs = $sql->query_read('x', " SELECT * FROM `mangadex_user_paypal` WHERE `paypal` NOT LIKE '%@%'  ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($txs as $tx) {
	$count = $sql->query_read('x', " 
		select count(*) FROM mangadex_transactions_btc 
		WHERE hash in (SELECT paypal from mangadex_user_paypal where user_id = {$tx['user_id']}) OR sender_address in (SELECT paypal from mangadex_user_paypal where user_id = {$tx['user_id']}) 
		", 'fetchColumn', '', -1); 
	
	$sql->modify('x', " update mangadex_user_paypal set count = ? WHERE paypal like ? LIMIT 1 ", [$count, $tx['paypal']]);
}	


$txs = $sql->query_read('x', " SELECT * FROM mangadex_user_paypal WHERE `paypal` NOT LIKE '%@%' AND count > 0 ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($txs as $tx) {
	$sql->modify('x', " update mangadex_users set premium = 1 WHERE user_id LIKE ? LIMIT 1 ", [$tx['user_id']])
}*/



//$result = $sql->query_read('x', " SELECT chapters.*, users.level_id, users.user_id, users.username FROM `mangadex_chapters` as chapters left join mangadex_users as users on chapters.user_id = users.user_id where users.level_id = 0 and chapters.chapter_deleted = 1 and chapters.server = 0  ", 'fetchAll', PDO::FETCH_ASSOC, -1);

//$result = $sql->query_read('x', " SELECT * FROM `mangadex_chapters` WHERE `manga_id` = 47 AND `server` = 0 AND `chapter_deleted` = 1   ", 'fetchAll', PDO::FETCH_ASSOC, -1);

$result = $sql->query_read('x', " 
SELECT * FROM `mangadex_chapters` WHERE `upload_timestamp` > 1604707200 AND upload_timestamp < 1605312000 and `group_id` != 9097 AND `server` = 0 AND `chapter_deleted` = 0 
", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($result as $row) {
	//print $row['chapter_hash'] . ' ';
	$file = $row['chapter_hash'];
	$chapter_id = $row['chapter_id'];
	$memcached->delete("chapter_$chapter_id");


                print "$file - $chapter_id\n";

                $sql->modify('update', " UPDATE mangadex_chapters SET server = 1 WHERE chapter_id = ? LIMIT 1; ", [$chapter_id]);
                //$memcached->delete("chapter_$chapter_id");

                rename("/var/www/mangadex.org/data/$file", "/var/www/mangadex.org/transferred/$file");



}

//$result = $sql->query_read('x', " SELECT chapters.*, users.level_id, users.user_id, users.username FROM `mangadex_chapters` as chapters left join mangadex_users as users on chapters.user_id = users.user_id where users.level_id = 0 and chapters.chapter_deleted = 1 and chapters.server = 0  ", 'fetchAll', PDO::FETCH_ASSOC, -1);

/*
$result = $sql->query_read('x', " SELECT * FROM `mangadex_chapters` WHERE `chapter_id` > 916000 and upload_timestamp <= UNIX_TIMESTAMP() AND chapter_deleted < 1  ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($result as $row) {
	print "INSERT INTO mangadex_last_updated (`chapter_id`, `manga_id`, `volume`, `chapter`, `title`, `upload_timestamp`, `user_id`, `lang_id`, `group_id`, `group_id_2`, `group_id_3`) 
    VALUES ({$row['chapter_id']}, {$row['manga_id']}, '{$row['volume']}', '{$row['chapter']}', '{$row['title']}', {$row['upload_timestamp']}, {$row['user_id']}, {$row['lang_id']}, {$row['group_id']}, {$row['group_id_2']}, {$row['group_id_3']}) 
    ON DUPLICATE KEY UPDATE chapter_id = {$row['chapter_id']}, volume = '{$row['volume']}',  chapter = '{$row['chapter']}',  title = '{$row['title']}',  upload_timestamp = {$row['upload_timestamp']},  user_id = {$row['user_id']},  group_id = {$row['group_id']},  group_id_2 = {$row['group_id_2']},  group_id_3 = {$row['group_id_3']};<br />";
	

}*/
/*
$count = 1;
$tx['paypal'] = '019e07a6f653d0c417b5ee77d1c44a2540d14b85738994df19b1a5edce511aad';
if ($sql->modify('x', " update mangadex_user_paypal set count = 1 WHERE paypal like '019e07a6f653d0c417b5ee77d1c44a2540d14b85738994df19b1a5edce511aad' LIMIT ? ", [1]))
	print 'TRUE';
else
	print 'FALSE';

try {
				$sql->modify('x', " update mangadex_user_paypal set count = 1 WHERE paypal like '019e07a6f653d0c417b5ee77d1c44a2540d14b85738994df19b1a5edce511aad' LIMIT ? ", [1]);
				
			} catch (\PDOException $e) {
				$error = sprintf('error code %s', $e->getCode());
				print $error;
			}
$result = $sql->query_read('x', " SELECT * FROM `mangadex_user_paypal` WHERE `paypal` like '019e07a6f653d0c417b5ee77d1c44a2540d14b85738994df19b1a5edce511aad'  ", 'fetchAll', PDO::FETCH_ASSOC, -1);
var_dump($result);
*/

/*
$stats = $mdAtHomeClient->getStatus();

var_dump($stats);

foreach ($stats as $client) {
	$client_id = (int) $client['client_id'];
	$user_id = (int) $client['user_id'];
	$subsubdomain = substr($client['url'], 8, 27);
	if (strpos($subsubdomain, '.') === 0)
		$subsubdomain = '*.' . substr($client['url'], 9, 13);
	$url = explode(':', $client['url']);
	$port = (int) $url[2];
	$client_ip = $client['ip'];
	$available = (int) $client['available'];
	$shard_count = (int) $client['shard_count'];
	$speed = ((int) $client['speed']) / 125000;
	$images_served = (int) $client['images_served'];
	$images_failed = (int) $client['images_failed'];
	$bytes_served = (int) $client['bytes_served'];
	$sql->modify('x', " update mangadex_clients set upload_speed = ?, client_ip = ?, client_subsubdomain = ?, client_port = ?, client_available = ?, shard_count = ?, images_served = ?, images_failed = ?, bytes_served = ?, update_timestamp = UNIX_TIMESTAMP() WHERE client_id = ? AND user_id = ? LIMIT 1 ", [$speed, $client_ip, $subsubdomain, $port, $available, $shard_count, $images_served, $images_failed, $bytes_served, $client_id, $user_id]);
}*/
/*
$result = $sql->query_read('x', " SELECT COUNT(*) AS `Rows`, `user_id` FROM `mangadex_sessions` GROUP BY `user_id` ORDER BY `Rows` DESC  ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($result as $row) {
	while ($row['Rows'] > 10) {
		$sql->modify('x', " delete FROM `mangadex_sessions` WHERE `user_id` = ? ORDER BY `mangadex_sessions`.`created` ASC limit 1 ", [$row['user_id']]);
		$row['Rows']--;
		print $row['Rows'] . '-' . $row['user_id'] . "\n";
	}
}*/

//var_dump(is_banned_asn('177.100.112.109'));
//var_dump(get_asn('177.100.112.109'));

