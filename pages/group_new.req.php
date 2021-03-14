<?php
    $next_group_id = $sql->query_read('last_group_id', ' SELECT group_id FROM mangadex_groups ORDER BY group_id DESC LIMIT 1 ', 'fetchColumn', '', -1) + 1;

    $page_html = parse_template('group/group_add');
