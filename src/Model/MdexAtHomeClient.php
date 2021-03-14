<?php

namespace Mangadex\Model;

class MdexAtHomeClient
{

    public function getServerUrl(string $chapterHash, array $chapterPages, string $ip): string
    {
        $path = '/assign';
        $payload = [
            'ip' => $ip,
            'hash' => $chapterHash,
            'images' => $chapterPages,
        ];

        $ch = $this->getCurl($path, $ip.$chapterHash.implode($chapterPages), $payload);

        $res = curl_exec($ch);
        curl_close($ch);
        if ($res === false) {
            throw new \RuntimeException('MD@H::getServerUrl curl error: '.curl_error($ch));
        }
        $dec = \json_decode($res, true);
        if (!$dec) {
            throw new \RuntimeException('MD@H::getServerUrl failed to decode: '.$res);
        }

        if (!isset($dec['server']) || empty($dec['server'])) {
            throw new \RuntimeException('MD@H::getServerUrl failed to retrieve server from data: '.$res);
        }
        return $dec['server'];
    }

    public function registerUser(string $userid, string $clientId, string $username, int $speedBytesPerSecond): string
    {
        $path = '/register';
        $payload = [
            'user_id' => (string) $userid,
            'username' => $username,
            'client_id' => (string) $clientId,
            'speed' => $speedBytesPerSecond,
        ];

        $ch = $this->getCurl($path, "{$userid}{$username}{$clientId}{$speedBytesPerSecond}", $payload);

        $res = curl_exec($ch);
        curl_close($ch);
        if ($res === false) {
            throw new \RuntimeException('MD@H::registerUser curl error: '.curl_error($ch));
        }
        $dec = \json_decode($res, true);
        if (!$dec) {
            throw new \RuntimeException('MD@H::registerUser failed to decode: '.$res);
        }

        if (!isset($dec['secret']) || empty($dec['secret'])) {
            throw new \RuntimeException('MD@H::registerUser failed to retrieve secret from data: '.$res);
        }
        return $dec['secret'];
    }

    public function rotateUser(string $clientId): string
    {
        $path = '/rotate_secret';
        $payload = [
            'client_id' => (string) $clientId,
        ];

        $ch = $this->getCurl($path, (string)$clientId, $payload);

        $res = curl_exec($ch);
        curl_close($ch);
        if ($res === false) {
            throw new \RuntimeException('MD@H::rotateUser curl error: '.curl_error($ch));
        }
        $dec = \json_decode($res, true);
        if (!$dec) {
            throw new \RuntimeException('MD@H::rotateUser failed to decode: '.$res);
        }

        if (!isset($dec['secret']) || empty($dec['secret'])) {
            throw new \RuntimeException('MD@H::rotateUser failed to retrieve secret from data: '.$res);
        }
        return $dec['secret'];
    }

    public function getStatus(): array
    {
        $path = '/status';

        $time = (new \DateTime())->format(\DateTimeInterface::RFC3339);
        $payload = [
            'time' => $time,
        ];

        $ch = $this->getCurl($path, $time, $payload);

        $res = curl_exec($ch);
        curl_close($ch);
        if ($res === false) {
            throw new \RuntimeException('MD@H::getStatus curl error: '.curl_error($ch));
        }
        $dec = \json_decode($res, true);
        if (!$dec) {
            throw new \RuntimeException('MD@H::getStatus failed to decode: '.$res);
        }

        return $dec;
    }

    private function getCurl(string $path, string $dataToSign, $payload)
    {
        $sign = hash_hmac('sha256', $dataToSign, MD_AT_H_BACKEND_SECRET);
        $payload['hmac'] = $sign;

        $ch = curl_init(MD_AT_H_BACKEND_URL.$path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);

        return $ch;
    }

}
