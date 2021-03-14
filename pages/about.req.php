<?php

$staffUsers = new Users(['is_staff' => 1]);

$page_html = parse_template('about', ['users' => $staffUsers->query_read('level_id DESC, users.user_id ASC', 100, 1)]);
