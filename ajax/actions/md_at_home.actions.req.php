<?php
switch ($function) {
	case 'turn_on':
		$turn_on = isset($_POST["turn_on"]) ? 1 : 0;
		
		$sql->modify('turn_on', ' UPDATE mangadex_user_options SET md_at_home = ? WHERE user_id = ? ', [$turn_on, $user->user_id]);
		
		$memcached->delete("user_$user->user_id");

		$details = $user->user_id;
		
		$result = 1;
		break;
		
	case 'request_client':
		$upload = prepare_numeric($_POST['upload']);
		$download = prepare_numeric($_POST['download']);
		$disk = prepare_numeric($_POST['disk']);
		$ip = $_POST['ip'];
		$speedtest = $_POST['speedtest'];
		$read_rules = isset($_POST["read_rules"]) ? 1 : 0;
		
		$count_ip = $sql->prep('count_ip', ' SELECT count(*) FROM mangadex_clients WHERE client_ip = ? ', [$ip], 'fetchColumn', '', -1);
		$count_clients = $sql->prep('count_clients', ' SELECT count(*) FROM mangadex_clients WHERE user_id = ? ', [$user->user_id], 'fetchColumn', '', -1);

		if (!$user->user_id)
			$error .= display_alert('danger', 'Failed', "Your session has timed out. Please log in again.");
		elseif ($upload < 40)
			$error .= display_alert('danger', 'Failed', "Your upload speed must be at least 40 Mbps.");
		elseif ($download < 40)
			$error .= display_alert('danger', 'Failed', "Your download speed must be at least 40 Mbps.");	
		elseif ($upload > 65535)
			$error .= display_alert('danger', 'Failed', "Your upload speed is too high.");
		elseif ($download > 65535)
			$error .= display_alert('danger', 'Failed', "Your download speed is too high.");	
		elseif ($disk < 40)
			$error .= display_alert('danger', 'Failed', "Your disk cache allocation must be at least 40 GB.");	
		elseif ($disk > 65535)
			$error .= display_alert('danger', 'Failed', "Your disk cache allocation is too high.");	
		elseif (!preg_match('/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip))
			$error .= display_alert('danger', 'Failed', "That doesn't look like a IPv4 address.");	
		elseif ($count_ip)
			$error .= display_alert('danger', 'Failed', "Your IP has already been used.");	
		elseif ($count_clients > 5)
			$error .= display_alert('danger', 'Failed', "You have reached the limit of clients you can request. For more clients, please PM ixlone.");	
		elseif (!$read_rules)
			$error .= display_alert('danger', 'Failed', "Go and read the rules.");	
		elseif (strlen($ip) > 15)
			$error .= display_alert('danger', 'Failed', "IPv4 addresses only.");	
			
		if (!$error) {
			$sql->modify('request_client', ' 
			INSERT INTO mangadex_clients (client_id, user_id, upload_speed, download_speed, disk_cache_size, speedtest, client_ip, client_continent, client_country) 
			VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)
			', [$user->user_id, $upload, $download, $disk, $speedtest, $ip, get_continent_code($ip), get_country_code($ip)]);
			
			$memcached->delete("user_{$user->user_id}_clients");

			$details = $user->user_id;
		}
		else {
			$details = $error;
			print $error; //returns "" or a message
		}

		$result = ($details) ? 0 : 1;
		break;
		
	case 'client_rotate':
		$client_id = prepare_numeric($_GET['client_id']);
		
		if (validate_level($user, 'admin')) {
			$client = $sql->prep('client_user_id', ' SELECT user_id, upload_speed FROM mangadex_clients WHERE client_id = ? ', [$client_id], 'fetch', PDO::FETCH_ASSOC, -1);
			
			try {
				$client_secret = $mdAtHomeClient->rotateUser($client_id);
				
				$sql->modify('client_rotate', ' UPDATE mangadex_clients SET client_secret = ? WHERE client_id = ? LIMIT 1 ', [$client_secret, $client_id]);
				
				$memcached->delete("user_{$user->user_id}_clients");
				
				// send the user an automated message

				$subject = "Your MangaDex@Home client secret has been reset.";
				$message = "Hello,\n\n" .
					"Your MangaDex@Home client secret has been reset to $client_secret";

				$sender_id = 1; // Default MangaDex user; TODO: remove hardcoded value?
				$recipient_id = $client['user_id'];

				$thread_id = $sql->modify('msg_send', ' INSERT INTO mangadex_pm_threads (thread_id, thread_subject, sender_id, recipient_id, thread_timestamp, sender_read, recipient_read, sender_deleted, recipient_deleted) 
				VALUES (NULL, ?, ?, ?, UNIX_TIMESTAMP(), 1, 0, 0, 0) ', [$subject, $sender_id, $recipient_id]);

				$sql->modify('msg_send', ' INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text) 
				VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$thread_id, $sender_id, $message]);

				$memcached->delete("user_{$recipient_id}_unread_msgs");
			
				$details = $id;
				
			} catch (Throwable $t) {
				// Send to sentry
				trigger_error($t->getMessage(), E_USER_WARNING);
				
				$details = $t->getMessage();
				print display_alert('danger', 'Failed', $details); //fail
			}
			
		}
		else {
			$details = "Error.";
			print display_alert('danger', 'Failed', $details); //fail
		}
		
		$result = (!is_numeric($details)) ? 0 : 1;
		
		break;
		
	case 'client_approve':
		$client_id = prepare_numeric($_GET['client_id']);
		
		if (validate_level($user, 'admin')) {
			$client = $sql->prep('client_user_id', ' 
				SELECT clients.user_id, clients.upload_speed, users.username, users.email
					FROM mangadex_clients AS clients
					LEFT JOIN mangadex_users AS users
						ON users.user_id = clients.user_id
					WHERE clients.client_id = ? 
					LIMIT 1
				', [$client_id], 'fetch', PDO::FETCH_ASSOC, -1);
			
			try {
				$bytes = $client['upload_speed'] / 8 * 1000000;
				$client_secret = $mdAtHomeClient->registerUser($client['user_id'], $client_id, $client['username'], $bytes);
				
				$sql->modify('client_approve', ' UPDATE mangadex_clients SET approved = 1, timestamp = UNIX_TIMESTAMP(), client_secret = ? WHERE client_id = ? LIMIT 1 ', [$client_secret, $client_id]);
				
				$memcached->delete("user_{$user->user_id}_clients");
				
				// send the user an automated message

				$subject = "Your MangaDex@Home client application has been accepted.";
				$message = "Hello,\n\n" .
					"Your MangaDex@Home client application has been accepted. Thank you for hosting a client! Please check the [url=https://mangadex.org/md_at_home/clients]My clients[/url] page for instructions on setting up the client.";

				$sender_id = 1; // Default MangaDex user; TODO: remove hardcoded value?
				$recipient_id = $client['user_id'];

				$thread_id = $sql->modify('msg_send', ' INSERT INTO mangadex_pm_threads (thread_id, thread_subject, sender_id, recipient_id, thread_timestamp, sender_read, recipient_read, sender_deleted, recipient_deleted) 
				VALUES (NULL, ?, ?, ?, UNIX_TIMESTAMP(), 1, 0, 0, 0) ', [$subject, $sender_id, $recipient_id]);

				$sql->modify('msg_send', ' INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text) 
				VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$thread_id, $sender_id, $message]);
				
				//email them
				$to = $client['email'];					
				$subject = "Your MangaDex@Home client application has been accepted - {$client['username']}";
				$body = "Hello,\n\n" .
"Your MangaDex@Home client application has been accepted. Thank you for hosting a client! Please check https://mangadex.org/md_at_home/clients for instructions on setting up the client.

Please connect your client as soon as possible. Clients that do not connect within 3 days of being approved will have re-apply for a new secret.";

				send_email($to, $subject, $body); 
	
				$memcached->delete("user_{$recipient_id}_unread_msgs");
			
				$details = $id;
				
			} catch (Throwable $t) {
				// Send to sentry
				trigger_error($t->getMessage(), E_USER_WARNING);
				
				$details = "MangaDex@Home error.";
				print display_alert('danger', 'Failed', $details); //fail
			}
			
		}
		else {
			$details = "Error.";
			print display_alert('danger', 'Failed', $details); //fail
		}
		
		$result = (!is_numeric($details)) ? 0 : 1;
		
		break;
		
	case 'client_reject':
		$client_id = prepare_numeric($_GET['client_id']);
		
		if (validate_level($user, 'admin')) {
			$sql->modify('client_reject', ' UPDATE mangadex_clients SET approved = 0 WHERE client_id = ? LIMIT 1 ', [$client_id]);
			
			$client_user_id = $sql->prep('client_user_id', ' SELECT user_id FROM mangadex_clients WHERE client_id = ? ', [$client_id], 'fetchColumn', '', -1);
			
			// send the user an automated message

			$subject = "Your MangaDex@Home client application has been rejected.";
			$message = "Hello,\n\n" .
				"Your MangaDex@Home client application has been rejected. Please PM ixlone to discuss your application.";

			$sender_id = 1; // Default MangaDex user; TODO: remove hardcoded value?
			$recipient_id = $client_user_id;

			$thread_id = $sql->modify('msg_send', ' INSERT INTO mangadex_pm_threads (thread_id, thread_subject, sender_id, recipient_id, thread_timestamp, sender_read, recipient_read, sender_deleted, recipient_deleted) 
			VALUES (NULL, ?, ?, ?, UNIX_TIMESTAMP(), 1, 0, 0, 0) ', [$subject, $sender_id, $recipient_id]);

			$sql->modify('msg_send', ' INSERT INTO mangadex_pm_msgs (msg_id, thread_id, user_id, timestamp, text) 
			VALUES (NULL, ?, ?, UNIX_TIMESTAMP(), ?) ', [$thread_id, $sender_id, $message]);

			$memcached->delete("user_{$recipient_id}_unread_msgs");
            			
			$memcached->delete("user_{$user->user_id}_clients");
			
			$details = $id;
		}
		else {
			$details = "Error.";
			print display_alert('danger', 'Failed', $details); //fail
		}
		
		$result = (!is_numeric($details)) ? 0 : 1;
		
		break;
		
	case 'edit_client':
		die();
		break;
		
	case 'delete_client':
		die();
		break;
}
