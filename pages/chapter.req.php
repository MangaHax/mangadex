<?php
$id = $_GET['id'] ?? 1;
$p = $_GET['p'] ?? 1;

$mode = $_GET['mode'] ?? 'chapter';

if ($mode == 'chapter' && !$user->reader) {
    $page_html = parse_template('reader', [
        'report_reasons' => (new Report_Reasons())->toArray(),
    ]);
}
else {
    ///
    /// Legacy Reader
    ///

	$chapter = new Chapter($id);
	$manga = new Manga($chapter->manga_id);

	if ($chapter->chapter_id && (!$chapter->chapter_deleted || validate_level($user, 'gmod'))) {

		$target_group = new Group($chapter->group_id);
		$group_members_array = $target_group->get_members();

		if ($chapter->group_id_2) {
			$target_group2 = new Group($chapter->group_id_2);
			$group_members_array = array_merge($group_members_array, $target_group2->get_members());
		}
		if ($chapter->group_id_3) {
			$target_group3 = new Group($chapter->group_id_3);
			$group_members_array = array_merge($group_members_array, $target_group3->get_members());
		}


		switch ($mode) {
			case 'comments':

                // Get a list of [user_id => username] the current user has blocked. key is the userid, value is the username
                $blockedUserIds = array_map(function($e) {return $e['username'] ?? 'user';}, $user->get_blocked_user_ids());

                $templateVars = [
                    'blocked_user_ids' => $blockedUserIds,
                    'user' => $user,
                    'manga' => $manga,
                    'chapter' => $chapter,
                    'page' => $page,
                    'parser' => $parser,
                    'post_history_modal_html' => parse_template('partials/post_history_modal', [ 'user' => $user ]),
                ];

                $page_html = parse_template('legacy_reader/partials/chapter_comments', $templateVars);

				break;

			case 'edit':

				if (
					$user->user_id == $chapter->user_id ||
					validate_level($user, 'gmod') ||
					($user->user_id && in_array($user->user_id, [$chapter->group_leader_id, $chapter->group_leader_id_2, $chapter->group_leader_id_3])) ||
					in_array($user->username, $group_members_array)
					) {

				    $templateVars = [
                        'manga' => $manga,
                        'chapter' => $chapter,
                        'user' => $user,
                    ];

                    $page_html .= parse_template('legacy_reader/partials/chapter_edit', $templateVars);
                } else {
                    $page_html .= parse_template('partials/alert', ['type' => 'warning', 'strong' => 'Warning', 'text' => 'You don\'t have permission to edit this chapter.']);
                }

				break;

			case 'chapter':
			default:
				if ($chapter->available && ($chapter->upload_timestamp < $timestamp ||
				($user->user_id == $chapter->user_id ||
				validate_level($user, 'pr') || // Retain forum mod ability to read delayed chapters
				($user->user_id && in_array($user->user_id, [$chapter->group_leader_id, $chapter->group_leader_id_2, $chapter->group_leader_id_3])) ||
				in_array($user->username, $group_members_array)
				))) {

					$other_chapters = $chapter->get_other_chapters($chapter->group_id);
					$other_groups = $chapter->get_other_groups();

					$blocked_groups = $user->get_blocked_groups();
					if ($blocked_groups) {
						$other_groups = remove_blocked_groups($other_groups, $blocked_groups);
					}

					if (in_array(36, $manga->get_manga_genres()))
						$user->reader_mode = 3;

					$current_key = array_search($chapter->chapter_id, $other_chapters["id"]);
					$next_key = $current_key - 1;
					$prev_key = $current_key + 1;
					$next_id = $other_chapters["id"][$next_key] ?? 0;
					$prev_id = $other_chapters["id"][$prev_key] ?? 0;
					$prev_pages = $chapter->get_pages_of_prev_chapter($prev_id);

					$arr = explode(",", $chapter->page_order);
					$page_array = array_combine(range(1, count($arr)), array_values($arr));
					$pages = count($page_array);

					/*
					if ($chapter->server) {
						$record = $geoip->country($ip);
						$code = $record->country->isoCode;

						if ($code) {
							if (in_array(COUNTRY_CONTINENTS[$code], ['EU', 'AF', 'AS', 'AN']))
								$chapter->server = 3;
							elseif (in_array(COUNTRY_CONTINENTS[$code], ['NA', 'SA', 'OC']))
								$chapter->server = 2;
							else {
								$server_array = [2, 3];
								$chapter->server = $server_array[rand(0, 1)];
							}
						}
					}
					*/

					// when a chapter does not exist on the local webserver, it gets an id. since all imageservers share the same data, we can assign any imageserver
					// with the best location to the user.
					if ($chapter->server > 0) {
						$server_id = -1;
						// If a usersetting overwrites it, take this
						if (isset($user->img_server)) {
							// if the parameter was trash, this returns -1
							$server_id = get_server_id_by_code($user->img_server);
						}
						if ($server_id < 1) {
							// Try to select a region based server if we havent set one already
							$server_id = get_server_id_by_geography();
						}

						$chapter->server = $server_id;
					}
					
					$data = ($user->data_saver) ? 'data-saver' : 'data';

					$server = ($chapter->server) ? "https://s{$chapter->server}.mangadex.org/$data/" : LOCAL_SERVER_URL . "/$data/";

                    $templateVars = [
                        'manga' => $manga,
                        'chapter' => $chapter,
                        'user' => $user,
                        'pages' => $pages,
                        'page' => $p,
                        'other_chapters' => $other_chapters,
                        'other_groups' => $other_groups,
                        'mode' => $mode,
                        'group_members_array' => $group_members_array,
                    ];

                    $page_html .= parse_template('legacy_reader/partials/chapter_navbox', $templateVars);

					update_views_v2($page, $chapter->chapter_id, $ip, $user->user_id);

					if ($user->user_id) {
						$chapter->update_chapter_views($user->user_id, $manga->get_follows_user_id());
						$chapter->update_reading_history($user->user_id, $user->get_reading_history(true));

						$followed_manga_ids_array = $user->get_followed_manga_ids();
						if (isset($followed_manga_ids_array[$chapter->manga_id])) {
							if ((int) $followed_manga_ids_array[$chapter->manga_id]['chapter'] == (int) $chapter->chapter - 1)
								$sql->modify('increment_chapter', ' UPDATE mangadex_follow_user_manga SET chapter = ABS(chapter) + 1 WHERE manga_id = ? AND user_id = ? LIMIT 1 ', [$chapter->manga_id, $user->user_id]);

							if ((int) $followed_manga_ids_array[$chapter->manga_id]['volume'] == (int) $chapter->volume - 1)
								$sql->modify('increment_volume', ' UPDATE mangadex_follow_user_manga SET volume = ABS(volume) + 1 WHERE manga_id = ? AND user_id = ? LIMIT 1 ', [$chapter->manga_id, $user->user_id]);

							$memcached->delete("user_{$user->user_id}_followed_manga_ids");
						}
					}

					$external = (substr($chapter->page_order, 0, 4) === 'http') ? $chapter->page_order : '';

                    $templateVars = [
                        'manga' => $manga,
                        'chapter' => $chapter,
                        'user' => $user,
                        'server' => $server,
                        'page' => $p,
                        'page_array' => $page_array,
                        'next_id' => $next_id,
                        'prev_id' => $prev_id,
						'external' => $external,
                        'report_reasons' => (new Report_Reasons())->toArray(),
                    ];

					$page_html .= parse_template('legacy_reader/reader', $templateVars);

				}
				else {
				    // TODO: Change to template
				    $page_html =
					    display_alert("danger", "Warning", "Due to the group's delay policy, this chapter will be available " . get_time_ago($chapter->upload_timestamp) . ".") .
					    display_alert("info", "Notice", "You might be able to read it on the group's <a target='_blank' href='$chapter->group_website'>" . display_fa_icon('external-link-alt', 'Website') . "<strong>website</strong></a>.");
				}
				break;
		}
	}
	elseif ($chapter->chapter_deleted)
		$page_html = parse_template('partials/alert', ['type' => 'danger', 'strong' => 'Warning', 'text' => "Chapter #$id has been deleted and cannot be viewed. If this has been accidentally deleted, contact a mod to restore it."]);
	else
        $page_html = parse_template('partials/alert', ['type' => 'danger', 'strong' => 'Warning', 'text' => "Chapter #$id does not exist."]);
}
