<?php
/*
if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Tachi') !== FALSE && rand(1,10) <3) {
	http_response_code(600);
	die('API currently down');
}*/

if (isset($_GET['_'])) {
	http_response_code(666);
	die();
}

use Mangadex\Model\Guard;

require_once ('../bootstrap.php');

require_once (ABSPATH . "/scripts/header.req.php");

if (!process_user_limit(1500, 'api_')) {
	$array['status'] = 'error';
	$array['message'] = 'Too many hits detected from your IP! Please try again tomorrow.';
	header('Content-Type: application/json');
	http_response_code(429);
	die(json_encode($array));
}

$guard = Guard::getInstance();
if (isset($_COOKIE[SESSION_COOKIE_NAME]) || isset($_COOKIE[SESSION_REMEMBERME_COOKIE_NAME])) {
	$guard->tryRestoreSession($_COOKIE[SESSION_COOKIE_NAME] ?? null, $_COOKIE[SESSION_REMEMBERME_COOKIE_NAME] ?? null);
	$user = $guard->hasUser() ? $guard->getUser() : $guard->getUser(0); // Fetch guest record (userid=0) if no user could be restored
} else {
	$user = $guard->getUser(0); // Fetch guest
}

/** @var $sentry Raven_Client */
if (isset($sentry) && isset($user)) {
	$sentry->user_context([
		'id' => $user->user_id,
		'username' => $user->username,
	]);
}

$type = $_GET['type'] ?? '';

switch ($type) {
	case 'manga':

		if (!isset($_GET['id'])) {
			$array['status'] = 'error';
			$array['message'] = 'No ID provided.';
			http_response_code(400);
			header('Content-Type: application/json');
			die(json_encode($array));
		}

		$manga_id = (int)prepare_numeric($_GET['id']);

		$manga = new Manga($manga_id);

		if (isset($manga->manga_id)) {
			$array['manga'] = [
				'cover_url' => "/images/manga/$manga->manga_id.$manga->manga_image?" . @filemtime(ABS_DATA_BASEPATH . "/manga/$manga->manga_id.$manga->manga_image"),
				'description' => $manga->manga_description,
				'title' => $manga->manga_name,
                'alt_names' => \array_map(function ($alt_name) { return \html_entity_decode($alt_name); }, $manga->get_manga_alt_names()),
				'artist' => $manga->manga_artist,
				'author' => $manga->manga_author,
				'status' => $manga->manga_status_id,
                'demographic' => $manga->manga_demo_id,
				'genres' => $manga->get_manga_genres(),
				'last_chapter' => $manga->manga_last_chapter,
                'last_volume' => $manga->manga_last_volume,
                'last_updated' => date('Y-m-d H:i:s', $manga->manga_last_uploaded),
				'lang_name' => $manga->lang_name,
				'lang_flag' => $manga->lang_flag,
				'hentai' => $manga->manga_hentai,
                //'follow' => $manga->get_user_follow_info($user->user_id),
				'links' => json_decode($manga->manga_links),
                'related' => $manga->get_related_manga(),
                'rating' => [
                    'bayesian' => $manga->manga_bayesian ?? 0,
                    'mean' => $manga->manga_rating ?? 0,
                    'users' => number_format(count($manga->get_user_ratings() ?? 0)),
                    //'personal' => $manga->get_user_rating($user->user_id) ?: 0,
                ],
                'views' => $manga->manga_views,
                'follows' => $manga->manga_follows,
                'comments' => $manga->thread_posts,
                'last_updated' => $manga->manga_last_uploaded,
                'covers' => \array_map(function ($cover) use ($manga_id) { return "/images/covers/{$manga_id}v{$cover['volume']}.{$cover['img']}"; }, $manga->get_covers()),
			];

			$search["chapter_deleted"] = 0;
			$search["manga_id"] = $manga_id; //manga_id
			$search["available"] = 1; //available

			$blocked_groups = $user->get_blocked_groups();
			if ($blocked_groups)
				$search['blocked_groups'] = array_keys($blocked_groups);

			$order = "(CASE volume WHEN '' THEN 1 END) DESC, abs(volume) DESC, abs(chapter) DESC, group_id ASC";

			$chapters = new Chapters($search);
			$chapters_obj = $chapters->query_read($order, 8000, 1);
			$group_list = array();

			foreach ($chapters_obj as $chapter) {
				$chapter = (object)$chapter;
				$array['chapter'][$chapter->chapter_id] = [
					'volume' => $chapter->volume,
					'chapter' => $chapter->chapter,
					'title' => html_entity_decode($chapter->title),
                    'lang_name' => $chapter->lang_name,
					'lang_code' => $chapter->lang_flag,
					'group_id' => $chapter->group_id,
					'group_name' => $chapter->group_name,
					'group_id_2' => $chapter->group_id_2,
					'group_name_2' => $chapter->group_name_2,
					'group_id_3' => $chapter->group_id_3,
					'group_name_3' => $chapter->group_name_3,
					'timestamp' => $chapter->upload_timestamp,
                    'comments' => $chapter->thread_posts,
				];

                $group_list[$chapter->group_id] = $chapter->group_name;
                if($chapter->group_id_2){
                    $group_list[$chapter->group_id_2] = $chapter->group_name_2;
                }
                if($chapter->group_id_3){
                    $group_list[$chapter->group_id_3] = $chapter->group_name_3;
                }
			}

			foreach($group_list as $group_id => $group_name){
                $array['group'][$group_id] = ['group_name' =>$group_name];
            }

			$array['status'] = 'OK';
			/*
			if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Tachiyomi') !== false) {
				$url = "https://www.google-analytics.com/collect?";
				$query = "/title/$manga->manga_id/" . slugify($manga->manga_name);
				$data = array(
					'v' => 1,
					'tid' => 'UA-112305080-1',
					//'tid' => 'UA-114714674-1',
					'cid' => md5($ip),
					't' => 'pageview',
					'dp' => urlencode($query),
					'dt' => $manga->manga_name . ' (Title) - Tachi API',
					//'an' => 'Tachiyomi',
					//'cd' => 'API',
					'z' => rand(),
					);

				httpGet($url, http_build_query_read($data));
			}*/
		}
		else {
			$array['status'] = 'Manga ID does not exist.';
			http_response_code(404);
		}

		break;

    case 'covers':

        if (!isset($_GET['id'])) {
            $array['status'] = 'error';
            $array['message'] = 'No ID provided.';
            http_response_code(400);
            header('Content-Type: application/json');
            die(json_encode($array));
        }

        $manga_id = (int)prepare_numeric($_GET['id']);

        $manga = new Manga($manga_id);

        if (isset($manga->manga_id)) {
            $array['covers'] = \array_map(function ($cover) use ($manga_id) { return "/images/covers/{$manga_id}v{$cover['volume']}.{$cover['img']}"; }, $manga->get_covers());
            $array['status'] = 'OK';
        }
        else {
            $array['status'] = 'Manga ID does not exist.';
            http_response_code(404);
        }

        break;

    case 'chapter':

	    if (isset($_GET['hash']) && preg_match('/^[a-z0-9]+$/i', $_GET['hash'])) {
            $chapter_id = $sql->prep("chapter_{$_GET['hash']}",
                " SELECT chapter_id FROM mangadex_chapters WHERE chapter_hash = ? LIMIT 1 ",
                [$_GET['hash']], 'fetchColumn', '', 86400);
            if (!$chapter_id) {
                $array['hash'] = $_GET['hash'];
                $array['status'] = 'error';
                $array['message'] = 'Chapter does not exist';
                http_response_code(404);
                header('Content-Type: application/json');
                die(json_encode($array));
            }
        } else if (!isset($_GET['id'])) {
			$array['status'] = 'error';
			$array['message'] = 'No ID provided.';
			http_response_code(400);
			header('Content-Type: application/json');
			die(json_encode($array));
		}

		$chapter_id = $chapter_id ?? prepare_numeric($_GET['id']);

		$chapter = new Chapter($chapter_id);
		$chapter = (object)$chapter;

		if (isset($chapter->chapter_id)) {

			$target_group = new Group($chapter->group_id);
			if ($target_group && isset($target_group->group_id) && $target_group->group_id > 0) {
				$group_members_array = $target_group->get_members();
			} else {
				$group_members_array = [];
			}

			if ($chapter->group_id_2) {
				$target_group2 = new Group($chapter->group_id_2);
				if ($target_group2 && isset($target_group2->group_id) && $target_group2->group_id > 0) {
					$group_members_array = array_merge($group_members_array, $target_group2->get_members());
				}
			}
			if ($chapter->group_id_3) {
				$target_group3 = new Group($chapter->group_id_3);
				if ($target_group3 && isset($target_group3->group_id) && $target_group3->group_id > 0) {
					$group_members_array = array_merge($group_members_array, $target_group3->get_members());
				}
			}

			if (!$chapter->available && !validate_level($user, 'pr')) {
				$array = [
					'id' => $chapter->chapter_id,
					'timestamp' => $chapter->upload_timestamp,
					'volume' => $chapter->volume,
					'chapter' => $chapter->chapter,
					'title' => html_entity_decode($chapter->title),
					'lang_name' => $chapter->lang_name,
					'lang_code' => $chapter->lang_flag,
					'manga_id' => $chapter->manga_id,
					'comments' => $chapter->thread_posts,
					'status' => 'unavailable',
				];
				http_response_code(300);
			}

			elseif ($chapter->chapter_deleted && !validate_level($user, 'pr')) {
				$array = [
					'id' => $chapter->chapter_id,
					'status' => 'deleted',
				];
				http_response_code(410);
			}

			elseif ($chapter->upload_timestamp < $timestamp ||
				($user->user_id == $chapter->user_id ||
					validate_level($user, 'pr') || // Retain pr ability to read delayed chapters
					($user->user_id && in_array($user->user_id, [$chapter->group_leader_id, $chapter->group_leader_id_2, $chapter->group_leader_id_3])) ||
					in_array($user->username, $group_members_array)
				)) {

				$status = 'OK';
				$manga = new Manga($chapter->manga_id);
				$long_strip = in_array(36, $manga->get_manga_genres());

				if (substr($chapter->page_order, 0, 4) === 'http') {
					$page_array = [];
					$status = 'external';
				} else {
					$arr = explode(",", $chapter->page_order);
					$page_array = array_combine(range(1, count($arr)), array_values($arr));
				}

				$server_fallback = IMG_SERVER_URL;
				$server_network = null;

                // use md@h for all images
                try {
                    $subsubdomain = $mdAtHomeClient->getServerUrl($chapter->chapter_hash, explode(',', $chapter->page_order), _IP, $user->mdh_portlimit ?? false);
                    if (!empty($subsubdomain)) {
                        $server_network = $subsubdomain;
                    }
				} catch (\Throwable $t) {
                    trigger_error($t->getMessage(), E_USER_WARNING);
				}

				$server = $server_network ?: $server_fallback;

				$data_dir = (isset($_GET['saver']) && $_GET['saver']) ? '/data-saver/' : '/data/';

				$array = [
					'id' => $chapter->chapter_id,
					'timestamp' => $chapter->upload_timestamp,
					'hash' => $chapter->chapter_hash,
					'volume' => $chapter->volume,
					'chapter' => $chapter->chapter,
					'title' => html_entity_decode($chapter->title),
					'lang_name' => $chapter->lang_name,
					'lang_code' => $chapter->lang_flag,
					'manga_id' => $chapter->manga_id,
                    'group_id' => $chapter->group_id,
                    'group_name' => $chapter->group_name,
                    'group_id_2' => $chapter->group_id_2,
                    'group_name_2' => $chapter->group_name_2,
                    'group_id_3' => $chapter->group_id_3,
                    'group_name_3' => $chapter->group_name_3,
					'comments' => $chapter->thread_posts,
					'server' => $server.$data_dir,
					'page_array' => array_values($page_array),
                    'long_strip' => $long_strip,
					'status' => $status,
				];

				if (!empty($server_network)) {
				    $array['server_fallback'] = $server_fallback.$data_dir;
                }

				$isRestricted = in_array($chapter->manga_id, RESTRICTED_MANGA_IDS) && !validate_level($user, 'contributor') && $user->get_chapters_read_count() < MINIMUM_CHAPTERS_READ_FOR_RESTRICTED_MANGA;
				$countryCode = strtoupper(get_country_code($user->last_ip));
				$isRegionBlocked = isset(REGION_BLOCKED_MANGA[$countryCode]) && in_array($manga->manga_id, REGION_BLOCKED_MANGA[$countryCode]) && !validate_level($user, 'pr');

				if ($status === 'external') {
					$array['external'] = $chapter->page_order;
				}

				elseif ($isRestricted || $isRegionBlocked) {
					$array = [
						'id' => $chapter->chapter_id,
						'status' => 'restricted',
					];
					http_response_code(451);
				}

				update_views_v2($type, $chapter->chapter_id, $ip, $user->user_id);

				$mark_read = $_GET["mark_read"] ?? true;
				if ($user->user_id && $mark_read) {
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

				$is_tachi = (strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Tachiyomi') !== false) ? 1 : 0;
				[$total_hits, $tachi_hits] = $memcached->get("chapter_hits") ?: [0, 0];
                $memcached->set("chapter_hits", [$total_hits + 1, $tachi_hits + $is_tachi]);
			}

			else {
				$array = [
					'id' => $chapter->chapter_id,
					'timestamp' => $chapter->upload_timestamp,
					'volume' => $chapter->volume,
					'chapter' => $chapter->chapter,
					'title' => html_entity_decode($chapter->title),
					'lang_name' => $chapter->lang_name,
					'lang_code' => $chapter->lang_flag,
					'manga_id' => $chapter->manga_id,
					'group_id' => $chapter->group_id,
                    'group_name' => $chapter->group_name,
                    'group_id_2' => $chapter->group_id_2,
					'group_name_2' => $chapter->group_name_2,
                    'group_id_3' => $chapter->group_id_3,
                    'group_name_3' => $chapter->group_name_3,
                    'group_website' => $chapter->group_website,
					'status' => 'delayed',
				];
				http_response_code(409);
			}
		}
		else {
			$array = [
				'id' => (int) $chapter_id,
				'status' => 'error',
				'message' => 'Chapter ID does not exist.',
			];
			http_response_code(404);
		}

		break;

	case 'manga_follows':

		if ($user && $user->user_id > 0) {

			if (isset($_GET['manga_id']) && $_GET['manga_id'] > 0) {
				$manga_id = (int)$_GET['manga_id'];

				$query = <<<SQL
SELECT
	m.manga_name AS title, f.manga_id, f.follow_type, f.volume, f.chapter
FROM
	mangadex_follow_user_manga f,
    mangadex_mangas m
WHERE
	f.user_id = ? AND f.manga_id = ?
    AND f.manga_id = m.manga_id
SQL;
				$follows = $sql->prep('api_folows_one_of_any_user', $query, [$user->user_id, $manga_id], 'fetchAll', \PDO::FETCH_ASSOC, -1);
			} else {
				$limit = 200;
				$offset = $limit * ((int) max(1, (int) min(50, $_GET['page'] ?? 1)) - 1);

				$follows = $user->get_followed_manga_ids_api();
				foreach ($follows AS &$follow) {
				    $follow['title'] = html_entity_decode($follow['title'] ?? '', null, 'UTF-8');
                }
			}

			$array = [
				'result' => $follows,
			];
		} else {
			http_response_code(401);
			$array['status'] = 'error';
			$array['message'] = 'No User available. You need to authenticate to use this endpoint.';
		}

		break;

	default:
		$array['status'] = 'error';
		$array['message'] = 'Not a valid API endpoint.';
		http_response_code(404);

		break;
}

//visit_log_api($_SERVER, $ip, $user->user_id, $user->hentai_mode);

header('Content-Type: application/json');
print json_encode($array);
