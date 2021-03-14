<?php
$id = $_GET['id'] ?? 1;

$ui_tl = new Language($id, "lang_id");
$ui = get_object_vars($ui_tl->get_ui("navbar"));

if (!in_array($user->user_id, TL_USER_IDS) && !validate_level($user, 'mod')) {
    die('No access.');
}

$templateVars = [
    'languages' => $languages,
    'ui' => $ui,
    'id' => $id,
];

$page_html = parse_template('user/translate', $templateVars);
