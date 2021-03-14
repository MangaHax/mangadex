<?php
$templateVars = [
    //'mode' => $mode,
    'mode' => 'history',
    'chapter_history' => $user->get_reading_history(),
];
$page_html = parse_template('user/history', $templateVars);
