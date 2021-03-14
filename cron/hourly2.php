<?php
/*
if (PHP_SAPI !== 'cli')
    die();
*/
require_once (__DIR__.'/../bootstrap.php');

require_once (ABSPATH . "/scripts/header.req.php");

//fetch and insert BTC data
foreach (WALLET_QR['BTC'] as $qr) {
	
	$ch = curl_init();
	// IMPORTANT: the below line is a security risk, read https://paragonie.com/blog/2017/10/certainty-automated-cacert-pem-management-for-php-software
	// in most cases, you should set it to true
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, "https://chain.api.btc.com/v3/address/$qr/tx");
	$result = curl_exec($ch);
	curl_close($ch);
	
	$obj = json_decode($result);

	foreach ($obj->data->list as $id => $tx) {
		$balance = $tx->balance_diff;
		if ($balance > 0) {
			$address = $tx->inputs[0]->prev_addresses[0];
			$hash = $tx->hash;
			
			$timestamp = $tx->block_time;
			
			$count = $sql->query_read('views_guests', " SELECT count(*) FROM mangadex_transactions_btc WHERE hash LIKE '$hash' LIMIT 1 ", 'fetchColumn', '', -1); 

			if (!$count) {
				$sql->modify('update', " INSERT IGNORE INTO mangadex_transactions_btc (timestamp, hash, sender_address, recipient_address, satoshis) VALUES (?, ?, ?, ?, ?); ", [$timestamp, $hash, $address, $qr, $balance]);
			}
		}
	}
	sleep(5); //be considerate!
}

//fetch and insert ETH data
foreach (WALLET_QR['ETH'] as $qr) {
	
	$ch = curl_init();
	// IMPORTANT: the below line is a security risk, read https://paragonie.com/blog/2017/10/certainty-automated-cacert-pem-management-for-php-software
	// in most cases, you should set it to true
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, "http://api.etherscan.io/api?module=account&action=txlist&address=$qr&startblock=0&endblock=99999999&sort=asc&apikey=V8NYD3PUSYCNJVQYMGFF45GB9V55N3JC32");
	$result = curl_exec($ch);
	curl_close($ch);
	
	$obj = json_decode($result);
	
	foreach ($obj->result as $id => $tx) {
				
		$balance = $tx->value;
		$from = $tx->from;
		$to = $tx->to;
		$hash = $tx->hash;
		
		$timestamp = $tx->timeStamp;
		
		$count = $sql->query_read('views_guests', " SELECT count(*) FROM mangadex_transactions_eth WHERE hash LIKE '$hash' LIMIT 1 ", 'fetchColumn', '', -1); 

		if (!$count && $from != strtolower($qr)) {
			$sql->modify('update', " INSERT IGNORE INTO mangadex_transactions_eth (timestamp, hash, sender_address, recipient_address, value) VALUES (?, ?, ?, ?, ?); ", [$timestamp, $hash, $from, $to, $balance]);
		}
		
	}
	sleep(1); //be considerate!
}

//update tx count
$txs = $sql->query_read('x', " SELECT * FROM `mangadex_user_paypal` WHERE `paypal` NOT LIKE '%@%'  ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($txs as $tx) {
	$count = $sql->query_read('x', " 
		select count(*) FROM mangadex_transactions_btc 
		WHERE hash in (SELECT paypal from mangadex_user_paypal where user_id = {$tx['user_id']}) OR sender_address in (SELECT paypal from mangadex_user_paypal where user_id = {$tx['user_id']}) 
		", 'fetchColumn', '', -1); 
	
	if ($count) {
		$sql->modify('x', " update mangadex_users set premium = 1 WHERE user_id = ? LIMIT 1 ", [$tx['user_id']]);
		print "updated {$tx['user_id']}\n";
	}
	//$sql->modify('x', " update mangadex_user_paypal set count = ? WHERE paypal like '?' LIMIT 1 ", [$count, $tx['paypal']]);
	
}	

foreach ($txs as $tx) {
	$count = $sql->query_read('x', " 
		select count(*) FROM mangadex_transactions_eth 
		WHERE hash in (SELECT paypal from mangadex_user_paypal where user_id = {$tx['user_id']}) OR sender_address in (SELECT paypal from mangadex_user_paypal where user_id = {$tx['user_id']}) 
		", 'fetchColumn', '', -1); 
	
	if ($count) {
		$sql->modify('x', " update mangadex_users set premium = 1 WHERE user_id = ? LIMIT 1 ", [$tx['user_id']]);
		print "updated {$tx['user_id']}\n";
	}
	
	//$sql->modify('x', " update mangadex_user_paypal set count = ? WHERE paypal like '?' LIMIT 1 ", [$count, $tx['paypal']]);
}	
/*
//update premium
$txs = $sql->query_read('x', " SELECT * FROM mangadex_user_paypal WHERE `paypal` NOT LIKE '%@%' AND count > 0 ", 'fetchAll', PDO::FETCH_ASSOC, -1);

foreach ($txs as $tx) {
	$sql->modify('x', " update mangadex_users set premium = 1 WHERE user_id = ? LIMIT 1 ", [$tx['user_id']]);
}*/