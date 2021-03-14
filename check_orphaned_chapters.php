<?php

if (PHP_SAPI !== 'cli') {
    die();
}

require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/scripts/header.req.php');

$dirPath = $argv[1] ?? '';

if (!is_dir($dirPath)) {
    die("Dir doesnt exist\n");
}

function checkChapterHashes(array $hashes): array
{
    global $sql;
    $replace = \implode(',', \array_fill(0, count($hashes), '?'));

    $dbHashes = $sql->prep('', 'SELECT chapter_hash FROM mangadex_chapters WHERE chapter_hash IN ('.$replace.')', $hashes, 'fetchAll', PDO::FETCH_COLUMN, -1);

    return \array_diff($hashes, $dbHashes);
}

$handle = opendir($dirPath);
$dirChunk = [];
while (false !== ($entry = readdir($handle))) {
    if ($entry{0} === '.') {
        continue;
    }

    $dirChunk[] = $entry;

    if (count($dirChunk) >= 100) {
        $orphanedHashes = checkChapterHashes($dirChunk);
        $dirChunk = [];

        foreach ($orphanedHashes AS $hash) {
            echo "$hash\n";
        }
    }
}
if (count($dirChunk) > 0) {
    $orphanedHashes = checkChapterHashes($dirChunk);
    $dirChunk = [];

    foreach ($orphanedHashes AS $hash) {
        echo "$hash\n";
    }
}
