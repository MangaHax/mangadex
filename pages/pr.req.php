<?php
if (!validate_level($user, 'pr')) die('No access');

//pages
$mode = $_GET['mode'] ?? 'banners';

$templateVars = [
    'mode' => $mode,
];

$page_html = parse_template('pr/partials/pr_navtabs', $templateVars);

switch ($mode) {
    case 'email_search':
        $search = [];

        if (isset($_GET['username']) && !empty($_GET['username']))
            $search['username'] = trim($_GET['username']);
        else
            $search['username'] = '';

        if (isset($_GET['email']) && !empty($_GET['email']))
            $search['email'] = trim($_GET['email']);
        else
            $search['email'] = '';

        $sort = (isset($_GET['s']) && $_GET['s'] > 0) ? $_GET['s'] : 0;
        $order = SORT_ARRAY_USERS[$sort];
        $limit = $limit ?? 100;
        $current_page = (isset($_GET['p']) && $_GET['p'] > 0) ? $_GET['p'] : 1;

        if ($search['username'] || $search['email']) {
            $users = new Users($search);
            $users_obj = $users->query_read($order, $limit, $current_page);
        }
        else {
            $users->num_rows = 0;
            $users_obj = new stdClass();
        }
        
        $templateVars = ['search' => $search];
        $page_html .= parse_template('pr/partials/user_list_searchbox', $templateVars);
        $templateVars = [
            'search' => $search,
            'sort' => $sort,
            'limit' => $limit,
            'current_page' => $current_page,
            'page' => $page,
            'user_list' => $users_obj,
            'user_count' => $users->num_rows,
        ];

        if (!$search['username'] && !$search['email']) {
            $page_html .= parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'No search string']);
        } elseif ($users->num_rows < 1) {
            $page_html .= parse_template('partials/alert', ['type' => 'info mt-3', 'strong' => 'Notice', 'text' => 'There are no users found with your search criteria.']);
        } else {
            $page_html .= parse_template('user/user_list', $templateVars);
        }
        break;
    
    case "banners":
    default:
        $templateVars['banners'] = get_banners(false);
            
        $page_html .= parse_template('pr/banners', $templateVars);
        break;
}
