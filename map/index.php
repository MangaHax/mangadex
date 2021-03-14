<?php
header('Content-Type: application/xml; charset=UTF-8');

require_once ($_SERVER["DOCUMENT_ROOT"] . "/config.req.php");

require_once (ABSPATH . "/scripts/header.req.php");

// Check if we have the sitemap already cached and its no older than 1 hour
$sitemapPath = ABSPATH . '/sitemap.xml';
if (file_exists($sitemapPath) && (time() - filemtime($sitemapPath) < 60*60)) {
    // Sitemap exists and is still valid. just print the file
    print file_get_contents($sitemapPath);
    exit();
}

$search["manga_hentai"] = 0;

try {
    // TODO: This is a bit awkward, a proper rework of this section should fix this
    $mangas = new Mangas($search);
    $mangas_obj = $mangas->query_read('manga_name ASC', 50000, 1);
} catch (\PDOException $e) {
    $mangas = (object)['num_rows' => 0];
    $mangas_obj = [];
}

try {
    // TODO: This is a bit awkward, a proper rework of this section should fix this
    $groups = new Groups();
    $groups_obj = $groups->query_read('group_name ASC', 10000, 1);
} catch (\PDOException $e) {
    $groups = (object)['num_rows' => 0];
    $groups_obj = [];
}

ob_start();

?>
<?='<?'?>xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url><loc><?= URL ?></loc></url>
<url><loc><?= URL ?>titles</loc></url>
<url><loc><?= URL ?>groups</loc></url>
<?php foreach ($mangas_obj as $manga) { ?>
	<url><loc><?= URL ?>title/<?= $manga->manga_id ?>/<?= slugify($manga->manga_name) ?></loc></url>
<?php } ?>
<?php foreach ($groups_obj as $group) { ?>
	<url><loc><?= URL ?>group/<?= $group->group_id ?>/<?= slugify($group->group_name) ?></loc></url>
<?php } ?>
</urlset>
<?php

$sitemapData = ob_get_flush();
file_put_contents($sitemapPath, $sitemapData);

?>