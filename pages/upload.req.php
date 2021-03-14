<?php
$id = $_GET['manga_id'] ?? 1;
$id = prepare_numeric($id);

$manga = new Manga($id);

$groups = new Groups();
$groups_obj = $groups->query_read("group_name ASC", 20000, 1);

if (!isset($manga->manga_id)) {
    $page_html = parse_template('partials/alert', ['type' => 'danger', 'strong' => 'Warning', 'text' => 'Manga #'.$id.' does not exist.']);
} else {
    $templateVars = [
        'group_list' => $groups_obj,
        'user' => $user,
        'manga' => $manga,
    ];

    $page_html = parse_template('chapter/upload', $templateVars);
}
