<?php

// TODO: Why is this here? where is this used?
$next_manga_id = $sql->query_read('last_manga_id', ' SELECT manga_id FROM mangadex_mangas ORDER BY manga_id DESC LIMIT 1 ', 'fetchColumn', '', -1) + 1;

$templateVars = [
    'page' => $page,
    'title_mode' => $title_mode,
];

$page_html = parse_template('manga/manga_add', $templateVars);
