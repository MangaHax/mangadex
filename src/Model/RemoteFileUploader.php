<?php

namespace Mangadex\Model;

use Google_Service_Drive;
use Google_Service_Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mangadex\Exception\RemoteFileUploadFailed;

class RemoteFileUploader
{

    private $context = [];

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function supports(string $fileurl): bool
    {
        $pathBits = \parse_url($fileurl);
        $hostname = $pathBits['host'];

        return \in_array($hostname, ['dropbox.com', 'www.dropbox.com', 'drive.google.com'], true) && (
            preg_match('#^https?://(?:www\.)?dropbox\.com/\w+/\w+/.+\.(zip|cbz)\??.*$#i', $fileurl)
            || preg_match('#^https?://drive\.google\.com/file/[a-z]+/[^/]+/view(?:\?[a-z=&]+)?$#i', $fileurl)
        );
    }

    public function downloadFromRemote(string $fileurl): array
    {
        $pathBits = \parse_url($fileurl);
        $hostname = $pathBits['host'];

        if (\stripos($hostname, 'dropbox.com') !== false) {
            return $this->fromDropbox($pathBits);
        } elseif (\stripos($hostname, 'drive.google.com') !== false) {
            return $this->fromGDrive($pathBits);
        }

        throw new \RuntimeException('Could not figure out which hoster this is');
    }

    private function getClient()
    {
        return new Client(
            [
            ]
        );
    }

    private function fromDropbox(array $pathInfo)
    {
        $client = $this->getClient();

        $fileurl = "$pathInfo[scheme]://$pathInfo[host]$pathInfo[path]?dl=1";

        $response = $client->request('GET', $fileurl,
            [
             'allow_redirects' => true,
             'stream' => true,
             'connect_timeout' => 3,
             'timeout' => 3,
             'read_timeout' => 10,
             'http_errors' => true,
            ]
        );
        $body = $response->getBody();
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Failed with non-successful http status code ('.$response->getStatusCode().')');
        }

        $timeout = microtime(true) + 10; // Allow a time window of xx seconds to DL the file
        $maxBytes = 1024 * 1024 * 100;
        $bytesWritten = 0;

        $fileOutPath = tempnam(sys_get_temp_dir(), 'remote_file_dl_'.($this->context['userid'] ?? 0).'_');

        $fileOut = fopen($fileOutPath, 'wb');

        while (!$body->eof() && microtime(true) < $timeout && $bytesWritten <= $maxBytes) {
            $bytesWritten += fwrite($fileOut, $body->read(1024));
        }

        $meta = $body->getMetadata();

        if ($meta['timed_out'] || ! $meta['eof']) {
            throw new RemoteFileUploadFailed('Failed to download whole file. Either a timeout was reached or the file is bigger than 100MB');
        }

        return ['filename' => 'remote.zip', 'filetmpname' => $fileOutPath, 'filesize' => $bytesWritten, 'filetype' => ''];
    }

    private function fromGDrive(array $pathInfo)
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.GOOGLE_SERVICE_ACCOUNT_PATH);
        $client = new \Google_Client(
            [

            ]
        );
        $client->addScope(Google_Service_Drive::DRIVE);
        $client->useApplicationDefaultCredentials();

        if (!preg_match('#^/file/d/([^/]+)(?:/view)?#i', $pathInfo['path'], $match) || !isset($match[1])) {
            throw new RemoteFileUploadFailed('Failed to get the fileId. Maybe the url is wrong?');
        }
        $fileId = $match[1];

        $service = new Google_Service_Drive($client);

        try {
            /** @var Response $response */
            $response = $service->files->get($fileId, ['alt' => 'media']);
        } catch (Google_Service_Exception $e) {
            if ($e->getCode() === 404) {
                throw new RemoteFileUploadFailed('File not found. Either it does not exist or the access is not set to "anyone with the link".');
            }
        } catch (\Throwable $t) {
            trigger_error('gdrive error: '.$t->getMessage(), E_USER_ERROR);
        }

        if (!$response instanceof Response || $response === null) {
            trigger_error('gdrive null', E_USER_WARNING);
            throw new RemoteFileUploadFailed('Googledrive remote uploader is broken, try again later.');
        }

        $contentType = $response->getHeader('Content-Type')[0] ?? '';
        $contentSize = $response->getHeader('Content-Length')[0] ?? PHP_INT_MAX;

        if (!\in_array($contentType, ['application/x-zip-compressed', 'application/x-zip', 'application/zip', 'image/cbz', 'application/x-cbz', 'application/cbz'], true)) {
            //var_dump($contentType);
            throw new RemoteFileUploadFailed('The file has not a zip or cbz file extension');
        }

        if ($contentSize > (1024 * 1024 * 100)) {
            throw new RemoteFileUploadFailed('Filesize is larger than 100MB');
        }

        $fileName = 'remote.zip';
        $fileOutPath = tempnam(sys_get_temp_dir(), 'remote_file_dl_'.($this->context['userid'] ?? 0).'_');
        $fileOut = fopen($fileOutPath, 'wb');

        $stream = $response->getBody();

        $timeout = microtime(true) + 10; // Allow a time window of xx seconds to DL the file
        $maxBytes = 1024 * 1024 * 100;
        $bytesWritten = 0;

        while (!$stream->eof() && microtime(true) < $timeout && $bytesWritten <= $maxBytes) {
            $bytesWritten += fwrite($fileOut, $stream->read(1024));
        }

        if (!$stream->eof()) {
            throw new RemoteFileUploadFailed('Failed to download whole file. Either a timeout was reached or the file is bigger than 100MB');
        }

        return ['filename' => $fileName, 'filetmpname' => $fileOutPath, 'filesize' => $bytesWritten, 'filetype' => ''];
    }

}
