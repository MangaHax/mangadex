<?php

namespace Mangadex\Model;

class AntiSpam
{

    private const API_URL = 'http://api.stopforumspam.org/api?json';

    public function __construct()
    {

    }

    public function getScore(string $username, string $email, string $ip): int
    {
        $data = http_build_query(
            [
                'username' => urlencode(iconv('GBK', 'UTF-8', $username)),
                'ip' => $ip,
                'email' => $email,
            ]
        );

        // init the request, set some info, send it and finally close it
        $ch = curl_init(self::API_URL);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        try {
            $result = curl_exec($ch);
        } catch (\Throwable $t) {
            trigger_error('Antispam threw exception: '.$t->getMessage(), E_USER_NOTICE);
            return -1;
        }
        if (!$result) {
            trigger_error('Antispam request failed: '.curl_error($ch), E_USER_NOTICE);
            return -1;
        }
        curl_close($ch);
        $stats = json_decode($result, true);
        if (!is_array($stats) || empty($stats)) {
            trigger_error('Antispam request could not be decoded: '.$result, E_USER_NOTICE);
            return -1;
        }

        return ($stats['username']['frequency'] ?? 0)
            + ($stats['ip']['frequency'] ?? 0)
            + ($stats['email']['frequency'] ?? 0);
    }

}
