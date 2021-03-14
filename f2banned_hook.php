<?php

// UNCOMMENT THIS TO DISABLE
//die('');

if (php_sapi_name() !== 'cli') die();

// DB
include "config.req.php";

$dsn = 'mysql:dbname='.DB_NAME.';host='.DB_HOST;

try {
	$db = new PDO($dsn, DB_USER, DB_PASSWORD);
} catch (PDOException $e) {
	die('Connection failed: ' . $e->getMessage());
}

// Prune?

if (($argv[1] ?? false) === 'prune') {
	prune_entries($db);
}

$ip = $argv[1] ?? false;

log_ipban($ip, $db);

// ========= FUNCTIONS

function log_ipban($ip, $db) {

	$logfile = '/var/log/nginx/mangadex.org.error.log';

	if (!filter_var($ip, FILTER_VALIDATE_IP)) {
		die('invalid ip');
	}

	// LOG

	$lines = [];
	$cmd = sprintf('tail -n 500 %s | grep %s', $logfile, escapeshellarg($ip));
	$res = exec($cmd, $lines);

	if (empty($lines)) {
		die('Empty result');
	}

	$text = substr(implode("\n", $lines), 0, 65535);

	$stmt = $db->prepare('INSERT INTO mangadex_fail2ban_log (ip, logs) VALUES (?, ?)');
	$stmt->execute([$ip, $text]);

	die('OK');
}

function prune_entries($db) {
    $db->exec('DELETE FROM mangadex_fail2ban_log WHERE DATE_ADD(`time`, INTERVAL 1 DAY) < NOW()');
    die("Pruned.\n");
}

