<?php
$section = $_GET['section'] ?? 'info';

$approvaltime = $user->get_client_approval_time();

switch ($section) {
	case 'options':
		$templateVars = [
			'user' => $user, 
			'section' => $section,
		];

		$tab_html = parse_template('md_at_home/partials/options', $templateVars);

		break;
		
	case 'stats':
		$templateVars = [
			'user' => $user, 
			'section' => $section,
		];

		$tab_html = parse_template('md_at_home/partials/stats', $templateVars);

		break;
		
	case 'request':
		$templateVars = [
			'user' => $user, 
			'section' => $section,
		];
 
		$tab_html = parse_template('md_at_home/partials/request', $templateVars);

		break;
		
	case 'clients':
		$templateVars = [
			'user' => $user, 
			'section' => $section,
			'user_clients' => $user->get_clients(), 
			'approvaltime' => $approvaltime, 
		];
		
		if (validate_level($user, 'member')) {
			$tab_html = parse_template('md_at_home/partials/clients', $templateVars);
		}
		break;
		
	case 'admin':
		$clients = new Clients();

		$templateVars = [
			'user' => $user, 
			'section' => $section,
			'clients' => $clients, 
		];
		
		if (validate_level($user, 'admin')) {
			$tab_html = parse_template('md_at_home/partials/admin', $templateVars);
		}
		break;
		
	case 'info':
	default:
		$templateVars = [
			'user' => $user, 
			'section' => $section,
		];

		$tab_html = parse_template('md_at_home/partials/info', $templateVars);

		break;
}


$templateVars = [
	'user' => $user, 
	'section' => $section, 		
	'approvaltime' => $approvaltime, 
	'tab_html' => $tab_html,
];

$page_html = parse_template('md_at_home/md_at_home', $templateVars);