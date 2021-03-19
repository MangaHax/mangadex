<?php

$guard = \Mangadex\Model\Guard::getInstance();

switch ($function) {
	case 'logout':

		$guard->destroySession();

		$memcached->delete("user_$user->user_id");

		if (IS_NOJS) redirect_url('/index.php');
        print display_alert('success', 'Success', 'You have logged out.');

		$result = 1;
		break;

	case 'login':

		$username = $_POST['login_username'] ?? '';
		$password = $_POST['login_password'] ?? '';
		$twoFaCode = !isset($_POST['two_factor']) || empty(trim($_POST['two_factor'])) ? false : trim($_POST['two_factor']);
		$isRememberme = isset($_POST['remember_me']) && $_POST['remember_me'];
		
		$sql->modify('login', " 
			INSERT INTO mangadex_login_attempts (ip, timestamp) VALUES (?, UNIX_TIMESTAMP()) ON DUPLICATE KEY UPDATE count = count + 1
			", [$ip]);
			
		
		
		// Process captcha
		if (defined('REQUIRE_CAPTCHA') && REQUIRE_CAPTCHA) {

			if (isset($_POST['g-recaptcha-response']))
				$captcha = $_POST['g-recaptcha-response'];

			//validate captcha
			if (!isset($captcha)) {
				// This might happen if the google captcha was blocked or this is a bot request
				$captcha_validate = ['success' => false];
			} else if ($captcha) {
				try {
					$captcha_validate = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".GOOGLE_CAPTCHA_SECRET."&response=$captcha&remoteip=$ip"), true);
				} catch (\Exception $e) {
					// This might happen if the google api timed out.
					$captcha_validate = ['success' => false];
				}
			}
		} else {
			// Probably a dev environment or captcha is currently disabled, just return success then.
			$captcha = true;
			$captcha_validate = ['success' => true];
		}
		
		$ipbans = [];
		try {
			$ipbans = get_ip_bans();
		} catch (\Exception $e) {}

		if (in_array($ip, $ipbans)) {
			if (IS_NOJS) redirect_url('/login?msg=ipban');
			$details = 'Your IP is banned. Please wait a few hours or contact a Staff member on IRC/Discord';
			print display_alert('danger', 'Failed', $details);  // IP banned
			die();
		}

		// Username/password authentication

		$isAuthenticated = false;
		try {
			$userId = $guard->authenticate($username, $password);
			$isAuthenticated = $userId > 0;
			$user = $guard->getUser($userId);
		} catch (\RuntimeException $e) {
			if (IS_NOJS) redirect_url('/login?msg=wrong_credentials');
			$details = 'Incorrect username or password.';
			print display_alert('danger', 'Failed', $details);  //wrong password
			die();
		}

		// 2FA validation (if activated)

		$twoFa = new \Mangadex\TwoFactorAuth($user);

		$isValidated = !$twoFa->isEnabled();
		if (!$isValidated) {
			if (!$twoFaCode) {
				if (IS_NOJS) redirect_url('/login?msg=missing_2fa');
				die('missing_2fa');
			}

			$reason = 'Failed to verify logincode!';
			try {
				$isValidated = $twoFa->validateLoginCode($twoFaCode);
			} catch (\Exception $e) {
				$reason = $e->getMessage();
			}

			// Maybe its a recovery code?
			if (!$isValidated && preg_match('#[A-Z0-9]{8}#', $twoFaCode)) {
				// Validated via recovery code. Maybe send an email?
				$isValidated = $twoFa->validateRecoveryCode($twoFaCode);
			}

			if (!$isValidated) {
				if (IS_NOJS) redirect_url('/login?msg=failed_2fa');
				print display_alert('danger', 'Failed', $reason);
				die();
			}
		}

		// Create session

		if ($isAuthenticated && $isValidated) {
			$guard->createSession($userId);
			if ($isRememberme) {
				$guard->createRemembermeToken($userId);
			}
			if (IS_NOJS) redirect_url('/index.php');
		}

		$result = ($details) ? 0 : 1;

		break;

	case 'session_destroy':

		$sessionId = (int)$_POST['session_id'];

		$sql->modify('user_destroy_session', 'DELETE FROM mangadex_sessions WHERE user_id = ? AND session_id = ?',
			[$user->user_id, $sessionId]);

		$ret = ['status' => 'success', 'message' => 'Session removed'];
		print json_encode($ret);

		$result = 1;

		break;

	case 'clear_sessions':

		$userId = $user->user_id;
		if (isset($_POST['user_id']) && validate_level($user, 'admin')) {
			// Allow admins to clear sessions for any user
			$userId = (int)$_POST['user_id'];
		}

		$sql->modify('user_clear_sessions', 'DELETE FROM mangadex_sessions WHERE user_id = ?',
			[$userId]);

		$ret = ['status' => 'success', 'message' => 'Sessions cleared'];
		print json_encode($ret);

		$result = 1;

		break;

	case 'signup':
		$username = $_POST['reg_username'];
		$pass1 = $_POST['reg_pass1'];
		$pass2 = $_POST['reg_pass2'];	
		$email1 = $_POST['reg_email1'];
		$email2 = $_POST['reg_email2'];	

		$password_hash = password_hash($pass1, PASSWORD_DEFAULT);
		$token = rand_string(32);
		$activation_key = rand_string(32); 

		//pass1=pass2 and email1=email2
		$password_test = ($pass1 == $pass2 && strlen($pass1) >= 8); //return TRUE
		$email_test = ($email1 == $email2); //return TRUE
		
		//existing username / validate username
		$count_user = $sql->prep('count_user', ' SELECT count(*) FROM mangadex_users WHERE username = ? ', [$username], 'fetchColumn', '', -1);
		$username_validate = preg_match("/^[a-zA-Z0-9_-]+$/", $username);
		$username_test = (!$count_user && $username_validate); //return TRUE
		
		//strip . from gmails
		/*if (false !== stripos($email1, '@gmail.')) {
			$email1 = preg_replace_callback('/^([^\+@]+)(\+[^@]*)?@(gmail\..*)$/i', function ($match) { return str_replace('.', '', $match[1]).'@'.$match[3]; }, $email1);
		}*/
		
		//existing email
		$count_email = $sql->prep('count_email', ' SELECT count(*) FROM mangadex_users WHERE email = ? ', [$email1], 'fetchColumn', '', -1);
		
		//banned emails
		$banned_hosts = $sql->query_read('tempmail', "SELECT host FROM mangadex_tempmail ORDER BY host ASC ", 'fetchAll', PDO::FETCH_COLUMN);
		
		$email_parts = explode('@', $email1);
		$banned_email = in_array($email_parts[1], $banned_hosts);
		

		// Process captcha
		if (defined('REQUIRE_CAPTCHA') && REQUIRE_CAPTCHA) {

			if (isset($_POST['g-recaptcha-response']))
				$captcha = $_POST['g-recaptcha-response'];

			//validate captcha
			if (!isset($captcha)) {
				// This might happen if the google captcha was blocked or this is a bot request
				$captcha_validate = ['success' => false];
			} else if ($captcha) {
				try {
					$captcha_validate = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".GOOGLE_CAPTCHA_SECRET."&response=$captcha&remoteip=$ip"), true);
				} catch (\Exception $e) {
					// This might happen if the google api timed out.
					$captcha_validate = ['success' => false];
				}
			}
		} else {
			// Probably a dev environment or captcha is currently disabled, just return success then.
			$captcha = true;
			$captcha_validate = ['success' => true];
		}

        $ipbans = [];
        try {
            $ipbans = get_ip_bans();
        } catch (\Exception $e) {}
		
		$banned_asn = is_banned_asn($ip);
		
        $sign_up_test = ($username_test && $count_email == 0 && $banned_email === FALSE && $password_test && $email_test && isset($captcha_validate['success']) && $captcha_validate['success'] && !in_array($ip, $ipbans) && !$banned_asn);
		
		if ($sign_up_test) {
			
			$user_id = $sql->modify('signup', " 
				INSERT INTO mangadex_users (user_id, username, password, token, level_id, email, language, display_lang_id, default_lang_ids, style, joined_timestamp, last_seen_timestamp, avatar, creation_ip, time_offset, activation_key, activated, user_website, user_description, upload_group_id, upload_lang_id, read_announcement, user_views, user_uploads, hentai_mode, swipe_direction, swipe_sensitivity, reader_mode, reader_click, image_fit, mangas_view, user_bio, list_privacy, list_banner, latest_updates, reader, premium, img_server) 
				VALUES (NULL, ?, ?, ?, 2, ?, 1, 1, '', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '', ?, '0', ?, 0, '', '', 0, 0, 0, 0, 0, 0, 1, 100, 0, 1, 0, 0, '', 0, '', 0, 0, 0, 0) 
				", [$username, $password_hash, $token, $email1, $ip, $activation_key]);
			
			$sql->modify('signup', " INSERT INTO mangadex_user_stats (user_id) VALUES (?) ", [$user_id]);
			
			$sql->modify('signup', " INSERT INTO mangadex_user_options (user_id) VALUES (?) ", [$user_id]);
			
			$to = $email1;
			$subject = "MangaDex: Account Creation - $username";
			$body = "Thank you for creating an account on MangaDex. \n\nUsername: $username \nPassword: (your chosen password) \n\nActivation code: $activation_key \n\nPlease visit " . URL . "activation/$activation_key to activate your account. \n\n If the above link doesn't work, try logging in and entering the activation code manually here " . URL . "activation instead.";
			//$body = "Thank you for creating an account on MangaDex. \n\nUsername: $username \nPassword: (your chosen password) Due to problem with a spammer, activation codes are temporarily not being sent in this email. Please reply to this email to request an activation code. Apologies for the inconvenience!";

			send_email($to, $subject, $body); 
			
			$user = new User($token, 'token'); //logs
		}
		else {
			if ($count_user) 
				$details = 'Your username has already been used.';
			elseif (!$username_validate) 
				$details = 'Choose a valid username. Only a-z, A-Z, 0-9, _ and - allowed.'; 
			elseif ($count_email == 1) 
				$details = 'Your email address has already been used.'; 
			elseif ($banned_email !== FALSE) 
				$details = 'Temporary email services are banned to prevent spamming.'; 
			elseif ($pass1 !== $pass2) 
				$details = 'Your passwords do not match.'; 
			elseif (strlen($pass1) < 8) 
				$details = 'Your password is too short.'; 
			elseif (!$email_test) 
				$details = 'Your emails do not match.'; 
			elseif (isset($captcha_validate['success']) && !$captcha_validate['success'])
				$details = 'The captcha could not be verified. Please try again.';
			elseif (in_array($ip, $ipbans))
                $details = 'Your IP is banned. Please wait for a few hours or contact a Staffmember on IRC/Discord.';
			elseif ($banned_asn)
				$details = 'Your ASN is banned. Please contact a staff member on Discord and quote the error message.';
			
			print display_alert('danger', 'Failed', $details);
		}	
		
		$result = ($details) ? 0 : 1;
		break;
		
	case 'reset_email':	
		$email = $_POST['reset_email'];

		// Process captcha
		if (defined('REQUIRE_CAPTCHA') && REQUIRE_CAPTCHA) {

			if (isset($_POST['g-recaptcha-response']))
				$captcha = $_POST['g-recaptcha-response'];

			//validate captcha
			if (!isset($captcha)) {
				// This might happen if the google captcha was blocked or this is a bot request
				$captcha_validate = ['success' => false];
			} else if ($captcha) {
				try {
					$captcha_validate = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".GOOGLE_CAPTCHA_SECRET."&response=$captcha&remoteip=$ip"), true);
				} catch (\Exception $e) {
					// This might happen if the google api timed out.
					$captcha_validate = ['success' => false];
				}
			}
		} else {
			// Probably a dev environment or captcha is currently disabled, just return success then.
			$captcha = true;
			$captcha_validate = ['success' => true];
		}

		$user = $sql->prep('reset_email', ' SELECT user_id, username FROM mangadex_users WHERE email = ? LIMIT 1 ', [$email], 'fetch', PDO::FETCH_OBJ, -1);

		if ($user && isset($user->user_id) && $user->user_id > 1 && isset($captcha_validate['success']) && $captcha_validate['success']) {
			$activation_key = rand_string(32);
			
			$to = $email;
			$subject = "MangaDex: Reset Password Request - $user->username";
			$body = "You have requested a reset code for MangaDex. \n\nUsername: $user->username \n\nReset code: $activation_key \n\nPlease visit " . URL . "reset_confirm/$activation_key to continue with your password reset. ";
			
			send_email($to, $subject, $body); 
			
			$sql->modify('reset_email', ' UPDATE mangadex_users SET activation_key = ? WHERE user_id = ? ', [$activation_key, $user->user_id]);
				
			$memcached->delete("user_$user->user_id");
			
			//$user = new User($token, 'token'); //logs // TODO: Why is this here? why would we ever want or need this?
		}
		else {
			if (!isset($captcha_validate['success']) || !$captcha_validate['success']) {
			    $error = $captcha_validate['error-codes'] ?? 'No error code available';
                $details = "You failed the captcha.";
                if (is_string($error) || is_numeric($error))
                	$details .= " Error code: $error";
            } else {
                $details = "Incorrect email address: $email";
            }

			print display_alert('danger', 'Failed', $details);
		}
		
		$result = ($details) ? 0 : 1;
		break;

	case 'reset':	
		$reset_code = $_POST['reset_code'];

		$pass1 = $_POST['reg_pass1'];
		$pass2 = $_POST['reg_pass2'];

		$password_hash = password_hash($pass1, PASSWORD_DEFAULT);
		$token = rand_string(32);
		$activation_key = rand_string(32);

		//pass1=pass2 and email1=email2
		$password_test = ($pass1 == $pass2 && strlen($pass1) >= 8); //return TRUE

		// Process captcha
		if (defined('REQUIRE_CAPTCHA') && REQUIRE_CAPTCHA) {

			if (isset($_POST['g-recaptcha-response']))
				$captcha = $_POST['g-recaptcha-response'];

			//validate captcha
			if (!isset($captcha)) {
				// This might happen if the google captcha was blocked or this is a bot request
				$captcha_validate = ['success' => false];
			} else if ($captcha) {
				try {
					$captcha_validate = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".GOOGLE_CAPTCHA_SECRET."&response=$captcha&remoteip=$ip"), true);
				} catch (\Exception $e) {
					// This might happen if the google api timed out.
					$captcha_validate = ['success' => false];
				}
			}
		} else {
			// Probably a dev environment or captcha is currently disabled, just return success then.
			$captcha = true;
			$captcha_validate = ['success' => true];
		}

		$user = $sql->prep('reset_password', ' SELECT user_id, username, email FROM mangadex_users WHERE activation_key = ? LIMIT 1 ', [$reset_code], 'fetch', PDO::FETCH_OBJ, -1);

		$iphash = sha1(_IP);

		$floodCheck = isset($user->user_id) && !$memcached->get("pass_reset_user_$user->user_id") && !$memcached->get("pass_reset_ip_$iphash");

		if ($floodCheck && $password_test && $user && isset($user->user_id) && $user->user_id > 1 && isset($captcha_validate['success']) && $captcha_validate['success']) {
			$new_password = $pass1;
			$password_hash = password_hash($new_password, PASSWORD_DEFAULT);
			//$token = '';
			$activation_key = rand_string(32);
			
//			$to = $user->email;
//			$subject = "MangaDex: Reset Password - $user->username";
//			$body = "You have successfully reset your password for MangaDex. \n\nUsername: $user->username \nPassword: $new_password \n\nPlease change this password after you log on. ";
//
//			send_email($to, $subject, $body);
			
			$sql->modify('reset_password', ' UPDATE mangadex_users SET `password` = ?, activation_key = ? WHERE user_id = ? ', [$password_hash, $activation_key, $user->user_id]);

			$memcached->delete("user_$user->user_id");
			$memcached->set("pass_reset_user_$user->user_id", true, 60*60);
			$memcached->set("pass_reset_ip_$iphash", true, 60*60);

			//$user = new User($token, 'token'); //logs  // TODO: Why is this here? why would we ever want or need this?
		}
		else {
			if (isset($captcha_validate['success']) && !$captcha_validate['success']) {
                $details = "You failed the captcha.";
            }
			else if (!isset($user->user_id)) {
				$details = "Incorrect or expired reset code: $reset_code";
			}
			else if (!$floodCheck) {
				$details = "You can't reset your password that often. Please wait up to 1 hour before trying to reset your password again.";
			}
			else if (!$password_test) {
				$details = "The two passwords are too short (8 chars min.) or don't match.";
			}
			else {
                $details = "Incorrect reset code: $reset_code";
            }

			print display_alert('danger', 'Failed', $details);
		}
		
		$result = ($details) ? 0 : 1;
		break;
		
	case 'activate':
		$activation_code = $_POST['activation_code'];

		if ($activation_code == $user->activation_key && $user->level_id) {
            // check if user is ip banned
            $user_banned = $sql->prep('activate', '
                                                                SELECT COUNT(*) 
                                                                FROM mangadex_users u 
                                                                JOIN mangadex_ip_bans b 
                                                                ON u.creation_ip = b.ip OR u.last_ip = b.ip
                                                                WHERE user_id = ? LIMIT 1', [$user->user_id], "fetchColumn", '', -1);
            if($user_banned){
                $sql->modify('activate', ' UPDATE mangadex_users SET level_id = 0, activated = 1 WHERE user_id = ? AND activated = 0 LIMIT 1 ', [$user->user_id]);
                }
            else{
                $sql->modify('activate', ' UPDATE mangadex_users SET level_id = 3, activated = 1 WHERE user_id = ? AND activated = 0 LIMIT 1 ', [$user->user_id]);
                }
			
			$memcached->delete("user_$user->user_id");
			
			$to = $user->email;
			$subject = "MangaDex: Successful Activation - $user->username";
			$body = "You have successfully activated your account for MangaDex.";
			
			//send_email($to, $subject, $body); 
		}
		elseif (!$user->level_id) {
			$details = "You're banned.";
			print display_alert('danger', 'Failed', $details); // banned
		}
		else {
			$details = 'Incorrect activation code.';
			print display_alert('danger', 'Failed', $details); // wrong code
		}
		
		$result = ($details) ? 0 : 1;
		break;
	
	case "resend_activation_code":
		$to = $user->email;
		$subject = "MangaDex: Resend Activation Code - $user->username";
		$body = "Here's your activation code. \n\nUsername: $user->username \n\nActivation code: $user->activation_key \n\nPlease visit " . URL . "activation/$user->activation_key to activate your account. ";

		send_email($to, $subject, $body, 3); 
		
		$result = 1;
		break;
		
    case "change_activation_email":
        $email = $_POST['email'];

        if($email != $user->email){
            // check for another account with this email
            $count_email = $sql->prep('count_email', ' SELECT count(*) FROM mangadex_users WHERE email = ? ', [$email], 'fetchColumn', '', -1);

            //check for banned hosts
            $banned_hosts = $sql->query_read('tempmail', "SELECT host FROM mangadex_tempmail ORDER BY host ASC ", 'fetchAll', PDO::FETCH_COLUMN);
            $email_parts = explode('@', $email);
            $banned_email = in_array($email_parts[1], $banned_hosts);

            if($count_email || $banned_email){
                $details = 'This email cannot be used.';
                print display_alert('danger', 'Failed', $details); // wrong code
                $result = 0;
            }
            else{
                $sql->modify('change_email', ' UPDATE mangadex_users SET email = ? WHERE user_id = ? LIMIT 1 ', [$email, $user->user_id]);
                $memcached->delete("user_$user->user_id");

                $to = $email;
                $subject = "MangaDex: Resend Activation Code - $user->username";
                $body = "Here's your activation code. \n\nUsername: $user->username \n\nActivation code: $user->activation_key \n\nPlease visit " . URL . "activation/$user->activation_key to activate your account. ";

                send_email($to, $subject, $body, 3);
                
                $result = 1;
            }
        }
        
        break;

    case "2fa_setup":

        if ($user === null || $user->user_id < 2 || !validate_level($user, 'member')) {
            http_response_code(400);
            die(json_encode(['status' => 'fail', 'message' => '2FA can not be set up on the current user!']));
        }

        $twoFa = new \Mangadex\TwoFactorAuth($user);
        if ($twoFa->isEnabled()) {
            http_response_code(400);
            die(json_encode(['status' => 'fail', 'message' => '2FA Is already enabled!']));
        }

        $res = $twoFa->setUp();
        if (!$res) {
            http_response_code(400);
            die(json_encode(['status' => 'fail', 'message' => 'Failed to set up 2FA!']));
        }

        $ret = ['status' => 'success', 'message' => 'Please confirm your 2FA with a logincode', 'data' => ['image_data' => $twoFa->generateQrImageData(), 'code' => $twoFa->getUserCode()]];
        print json_encode($ret);

        $result = 1;

        break;

    case "2fa_confirm":

        if ($user === null || $user->user_id < 2 || !validate_level($user, 'member')) {
            //http_response_code(400);
            die(json_encode(['status' => 'fail', 'message' => '2FA can not be set up on the current user!']));
        }

        $twoFa = new \Mangadex\TwoFactorAuth($user, true);
        if (!$twoFa->isEnabled()) {
            //http_response_code(400);
            die(json_encode(['status' => 'fail', 'message' => '2FA Needs to be set up first!']));
        }

        if ($twoFa->getType() > 0) {
            //http_response_code(400);
            die(json_encode(['status' => 'fail', 'message' => '2FA Is already enabled!']));
        }

        $reason = 'Failed to verify logincode!';
        $validate = false;
        try {
            $validate = $twoFa->validateLoginCode($_POST['code']);
        } catch (\InvalidArgumentException $e) {
            $reason = $e->getMessage();
        }
        if (!$validate) {
            //http_response_code(400);
            die(json_encode(['status' => 'fail', 'message' => $reason]));
        }

        $twoFa->confirmSetUp();

        $codes = $twoFa->getRecoveryCodes();
        print json_encode(['status' => 'success', 'message' => 'Successfully set up 2FA!', 'data' => ['recovery' => $codes]]);

        break;

    case "2fa_login":

        break;

    case "2fa_remove":

        if (!isset($_POST['confirm'])) {
            http_response_code(400);
            die(json_encode(['status' => 'fail', 'message' => '2FA removal needs to be confirmed!']));
        }

        $twoFaUser = $user;

        // Allow admins to remove 2FA from other users
        if (isset($_POST['user_id']) && $_POST['user_id'] > 0 && validate_level($user, 'admin')) {
            $twoFaUser = new User((int)$_POST['user_id'], 'user_id');
        }

        if ($twoFaUser === null || $twoFaUser->user_id < 2 || !validate_level($twoFaUser, 'member')) {
            http_response_code(400);
            die(json_encode(['status' => 'fail', 'message' => '2FA can not be removed on the current user!']));
        }

        $twoFa = new \Mangadex\TwoFactorAuth($twoFaUser);
        if (!$twoFa->isEnabled()) {
            //http_response_code(400);
            // this is an error case but we still answer with a success to hide the fact if this account had 2fa or not
            die(json_encode(['status' => 'success', 'message' => '2FA has been disabled!']));
        }

        $twoFa->remove();

        die(json_encode(['status' => 'success', 'message' => '2FA has been disabled!']));

        break;
}
