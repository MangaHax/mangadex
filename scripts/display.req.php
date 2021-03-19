<?php
function display_rss_link($user, $type = '', $id = 0) {
	if ($user->user_id) {
        $hentai_toggle = max(0, min(2, $_COOKIE['mangadex_h_toggle'] ?? 0));
        if($type == "follows"){
            return "<li class='ml-auto'><a target='_blank' href='/rss/follows/$user->activation_key?h=$hentai_toggle'>" . display_fa_icon("rss", "RSS", "fa-2x", "fas") .  '</a></li>';
        }
        else if($type == "group_mini"){
            return "<a target='_blank' href='/rss/$user->activation_key/group_id/$id?h=$hentai_toggle'>" . display_fa_icon('rss-square', 'RSS', 'fa-lg') . "</a>";
        }
        else{
            return "<a class='ml-auto' target='_blank' href='/rss/$user->activation_key" . ($type ? "/$type/$id" : '') . "?h=$hentai_toggle'><span class='fas fa-rss fa-2x'></span></a>";
        }
    }
	else
		return "<a class='ml-auto' href='/login'><span title='Due to the volume of bots spamming RSS links, this now requires an account to use.' class='fas fa-rss fa-2x'></span></a>";
}

function display_short_title($array, $white = '', $truncate = '') {
	if (!$array['volume'] && $array['chapter'] == '' && in_array($array['title'], ['Oneshot', '']))
		$title = "Oneshot";
	elseif (!$array['volume'] && $array['chapter'] == '')
		$title = $array['title'];
	else
		$title = ($array['volume'] ? "Vol. {$array['volume']} " : '') . ($array['chapter'] != '' ? "Chapter {$array['chapter']}" : '');

	return "<a class='" . ($white ? 'white ' : '') . ($truncate ? 'text-truncate' : '') . "' href='/chapter/{$array['chapter_id']}'" . ($truncate ? " style='flex: 0 1 auto;'" : '') . ">$title</a>";
}

function display_reading_history($user) {
	$return = '';

	if ($user->user_id) {
		$chapter_history = $user->get_reading_history();

		if ($chapter_history) {
			$return .= "<ul class='list-group list-group-flush'>";
			for ($i = 0; $i < min(3, count($chapter_history)); $i++) {
				$return .= "
					<li class='list-group-item px-2 py-1'>
						<div class='hover tiny_logo rounded float-left mr-2'>
						<a href='/title/{$chapter_history[$i]['manga_id']}/" . slugify($chapter_history[$i]['manga_name']) . "'>
						<img class='rounded max-width' src='" . LOCAL_SERVER_URL . "/images/manga/{$chapter_history[$i]['manga_id']}.thumb.jpg?" . @filemtime(ABS_DATA_BASEPATH . "/manga/{$chapter_history[$i]['manga_id']}.thumb.jpg") . "'>
						</a>
						</div>
						<div class='pt-0 pb-1 mb-1 border-bottom d-flex align-items-center flex-nowrap'>" . display_fa_icon('book','','mr-1 flex-shrink-0') . display_manga_link_v2($chapter_history[$i]) . "</div>
						<p class='text-truncate py-0 mb-1'>
						<span class='float-left'>" . display_fa_icon('file', '', '', 'far') . ' ' . display_short_title($chapter_history[$i]) . "</span>
						<span class='float-right'>" . display_fa_icon('clock', '', '', 'far') . ' ' . get_time_ago($chapter_history[$i]['timestamp']) . "</span>
						</p>
					</li>";
			}
			$return .= "</ul>";
		}
		else
			$return = "<p class='text-center m-0 p-3'>Go and read a chapter!</p>";
	}
	else
		$return = display_alert('info m-2', 'Notice', "Please " . display_fa_icon('sign-in-alt') . " <a href='/login'>log in</a> to view your reading history.");

	return $return;
}

function display_latest_posts($array) {
	global $parser;

	$return = "<ul class='list-group list-group-flush'>";

	if (empty($array)) {
        $return .= "<li class='list-group-item px-2 py-1'><strong>No posts to display.</strong></li>";
    } else {
		foreach ($array as $post) {
			$post['text'] = preg_replace('/\[spoiler\][\s\S]+?\[\/spoiler\]/iu', '', $post['text']);
			$post['text'] = preg_replace('/\[h1\][\s\S]+?\[\/h1\]/iu', '', $post['text']);
			$post['text'] = preg_replace('/\[h2\][\s\S]+?\[\/h2\]/iu', '', $post['text']);
			$post['text'] = preg_replace('/\[h3\][\s\S]+?\[\/h3\]/iu', '', $post['text']);
			$post['text'] = preg_replace('/\[img\][\s\S]+?\[\/img\]/iu', '(image)', $post['text']);
			$post['text'] = preg_replace('/\[quote[\s\S]+?\[\/quote\]/iu', '(quote)', $post['text']);
			$post['text'] = preg_replace('/\[ul\][\s\S]+?\[\/ul\]/iu', '', $post['text']);
			$post['text'] = preg_replace('/\[center\][\s\S]+?\[\/center\]/iu', '', $post['text']);
			$parser->parse($post['text']);
			$return .= "<li class='list-group-item px-2 py-1'>
				<div class='hover rounded float-left mr-2'><a href='/thread/{$post['thread_id']}/'><img width='40px' class='rounded' src='" . LOCAL_SERVER_URL . "/images/forums/{$post['forum_name']}.svg'></a></div>
				<p class='text-truncate pt-0 pb-1 mb-1 border-bottom'><a href='/thread/{$post['thread_id']}/{$post['thread_page']}/#post_{$post['post_id']}'>{$post['thread_name']}</a></p>
				<p class='text-truncate py-0 mb-1' title='" . gmdate(DATETIME_FORMAT, $post['timestamp']) . "'>" . $parser->getAsHtml() . "</p>
			</li>";
		}
	}

	$return .= "</ul>";

	return $return;
}

function display_latest_comments($array, $type = '') {
	global $parser;

	$return = "<ul class='list-group list-group-flush'>";

	if (empty($array)) {
        $return .= "<li class='list-group-item px-2 py-1'><strong>No comments to display.</strong></li>";
    } else {
		foreach ($array as $post) {
			$post['text'] = preg_replace('/\[spoiler\][\s\S]+?\[\/spoiler\]/iu', '', $post['text']);
			$post['text'] = preg_replace('/\[img\][\s\S]+?\[\/img\]/iu', '(image)', $post['text']);
			$post['text'] = preg_replace('/\[quote[\s\S]+?\[\/quote\]/iu', '(quote)', $post['text']);
			$post['text'] = preg_replace('/\[ul\][\s\S]+?\[\/ul\]/iu', '', $post['text']);
			$post['text'] = preg_replace('/\[center\][\s\S]+?\[\/center\]/iu', '', $post['text']);
			$parser->parse($post['text']);
			$return .= "<li class='list-group-item px-2 py-1'>
				<div class='hover tiny_logo rounded float-left mr-2'><a href='/title/{$post['manga_id']}/'><img class='rounded max-width' src='" . LOCAL_SERVER_URL . "/images/manga/{$post['manga_id']}.thumb.jpg'></a></div>
				<p class='text-truncate pt-0 pb-1 mb-1 border-bottom'><a href='/thread/{$post['thread_id']}/{$post['thread_page']}/#post_{$post['post_id']}'>{$post['manga_name']}</a></p>
				<p class='text-truncate py-0 mb-1' title='" . gmdate(DATETIME_FORMAT, $post['timestamp']) . "'>" . $parser->getAsHtml() . "</p>
			</li>";
		}
	}

	$return .= "</ul>";

	return $return;
}

function display_latest_updates($array, $mode) {
	if ($array) {
		if (!$mode) {
			$return = "<div class='row m-0'>";

			foreach ($array as $manga) {
                $has_end_tag = ($manga['manga_last_volume'] === null || ($manga['manga_last_volume'] == $manga['volume'])) && $manga['manga_last_chapter'] && $manga['manga_last_chapter'] == $manga['chapter'];
				$return .= "<div class='col-md-6 border-bottom p-2'>
					<div class='hover sm_md_logo rounded float-left mr-2'><a href='/title/{$manga['manga_id']}/" . slugify($manga['manga_name']) . "'><img class='rounded max-width' src='" . LOCAL_SERVER_URL . "/images/manga/{$manga['manga_id']}.thumb.jpg'></a></div>

					<div class='pt-0 pb-1 mb-1 border-bottom d-flex align-items-center flex-nowrap'>
						<div>" . display_fa_icon('book', '', 'mr-1') . "</div>"
            . display_manga_link_v2($manga) . "
					</div>

					<div class='py-0 mb-1 row no-gutters align-items-center flex-nowrap'>"
						. display_fa_icon('file', '', 'col-auto mr-1', 'far')
						. display_short_title($manga, '', 'truncate')
						. ($has_end_tag ? "<div class='ml-1'><span class='badge badge-primary'>END</span></div>" : '')
                        . ($manga["available"] == 0 ? display_fa_icon('file-excel', 'Unavailable', 'mx-1', 'fas') : '')
                        . "<div class='ml-1'>"  . display_lang_flag_v3($manga) . "</div>"
						. "</div>
					<div class='text-truncate py-0 mb-1'>" . display_fa_icon('users') . ' ' . display_group_link_v2($manga) . "</div>
					<div class='text-truncate py-0 mb-1'>" . display_fa_icon('clock', '', '', 'far') . ' ' . get_time_ago($manga['upload_timestamp']) . "</div>
				</div>";
			}

			$return .= "</div>";
		}
		else {
			$i = 0;
			$last_manga_id_array = [];
			foreach ($array as $chapter) {
				if (!in_array($chapter['manga_id'], $last_manga_id_array)) {
					$manga_array[$i] = [
						'manga_id' => $chapter['manga_id'],
						'manga_name' => $chapter['manga_name'],
						'manga_hentai' => $chapter['manga_hentai'],
					];
					$last_manga_id_array[] = $chapter['manga_id'];
					$i++;
				}

				$chapter_array[$chapter['manga_id']][$chapter['chapter_id']] = [
					'chapter_id' => $chapter['chapter_id'],
					'lang_name' => $chapter['lang_name'],
					'lang_flag' => $chapter['lang_flag'],
					'level_colour' => $chapter['level_colour'],
					'username' => $chapter['username'],
					'group_name' => $chapter['group_name'],
					'group_id' => $chapter['group_id'],
					'group_name_2' => $chapter['group_name_2'],
					'group_id_2' => $chapter['group_id_2'],
					'group_name_3' => $chapter['group_name_3'],
					'group_id_3' => $chapter['group_id_3'],
					'upload_timestamp' => $chapter['upload_timestamp'],
					'volume' => $chapter['volume'],
					'chapter' => $chapter['chapter'],
					'title' => $chapter['title'],
                    'manga_last_volume' => $chapter['manga_last_volume'],
                    'manga_last_chapter' => $chapter['manga_last_chapter'],
                    'available' => $chapter['available'],
				];
			}

			$return = "<div class='table-responsive'>
				<table class='table table-striped table-sm'>
					<thead>
						<tr>
							<th width='110px'></th>
							<th width='25px'></th>
							<th style='min-width: 150px;'></th>
							<th class='text-center' width='30px'>" . display_fa_icon('globe', 'Language') . "</th>
							<th style='min-width: 150px;'>" . display_fa_icon('users', 'Group') . "</th>
							<th style='min-width: 65px;' class='text-right'>" . display_fa_icon('clock', 'Uploaded', '', 'far') . "</th>
						</tr>
					</thead>
					<tbody>";

					for ($j = 0; $j < min(20, count($manga_array)); $j++) {
						$manga_id = $manga_array[$j]['manga_id'];
						$manga = $chapter_array[$manga_id];

						//$bookmark = ($user->user_id && in_array($manga_id, $followed_manga_array)) ? display_fa_icon("bookmark", "Following", "text-success") : "";
						$rowspan = (count($manga) >= 4 ? 5 : count($manga) + 1);

						$return .= "<tr>
							<td rowspan='$rowspan'><div class='medium_logo rounded'><a href='/title/$manga_id/" . slugify($manga_array[$j]['manga_name']). ">'><img class='rounded' src='" . LOCAL_SERVER_URL . "/images/manga/$manga_id.thumb.jpg' alt='Thumb' /></a></div></td>
							<td class='text-right'></td>
							<td colspan='4' height='31px' class='position-relative'><span class='ellipsis'>" . display_fa_icon('book', 'Title') . ' ' . display_manga_link_v2($manga_array[$j]) . "</span></td>
						</tr>";

						$i = 1;
						foreach ($manga as $chapter_id => $chapter) {
							if ($i < 5) {
								$i++;
								//$key = ($user->user_id) ? array_search($chapter_id, $read_chapter_array["chapter_id"]) : "";
								//$read = ($user->user_id && in_array($chapter_id, $read_chapter_array["chapter_id"])) ? display_fa_icon("eye", "fas", "Read " . get_time_ago($read_chapter_array["timestamp"][$key]), "fa-fw") : "";
                                $has_end_tag = ($chapter['manga_last_volume'] === null || ($chapter['manga_last_volume'] == $chapter['volume'])) && $chapter['manga_last_chapter'] && $chapter['manga_last_chapter'] == $chapter['chapter'];

								$return .= "<tr>
								<td class='text-right'></td>
								<td>" . display_fa_icon('file', '', '', 'far') . " <a href='/chapter/{$chapter['chapter_id']}'>" . display_short_title($chapter) . "</a>" . ($has_end_tag ? ' <span class="badge badge-primary">END</span>' : '') . ($chapter["available"] == 0 ? display_fa_icon('file-excel', 'Unavailable', 'mx-1', 'fas') : '') ."</td>
								<td class='text-center'>" . display_lang_flag_v3($chapter) . "</td>
								<td class='position-relative'><span class='ellipsis'>" . display_group_link_v2($chapter) . "</span></td>
								<td class='text-right' title='" . gmdate(DATETIME_FORMAT, $chapter['upload_timestamp']) . "'><time datetime='" . gmdate(DATETIME_FORMAT, $chapter['upload_timestamp']) . "'>" . get_time_ago($chapter['upload_timestamp']) . "</time></td>
								</tr>";
							}
						}
					}

					$return .= "</tbody>
				</table>
			</div>";

		}
	}
	else
		$return = display_alert('info m-2', 'Notice', "No updates!");

	return $return;
}

function display_top_list($array, $type = '', $count = 10) {
	$return = "<ul class='list-group list-group-flush'>";

	if (empty($array)) {
        $return .= "<li class='list-group-item px-2 py-1'><strong>No manga to display.</strong></li>";
    } else {
		$displayed = [];
		$i = 0;

		foreach ($array as $manga) {
			if (!in_array($manga['manga_id'], $displayed) && $i < $count) {
				$return .= "<li class='list-group-item px-2 py-1'>
					<div class='hover tiny_logo rounded float-left mr-2'><a href='/title/{$manga['manga_id']}/" . slugify($manga['manga_name']) . "'><img class='rounded max-width' src='" . LOCAL_SERVER_URL . "/images/manga/{$manga['manga_id']}.thumb.jpg'></a></div>
					<div class='text-truncate pt-0 pb-1 mb-1 border-bottom'>" . display_fa_icon('book', '', 'mr-1') . display_manga_link_v2($manga) . "</div>
					<p class='text-truncate py-0 mb-1'>";

					switch ($type) {
						case 'top_follows':
							$return .= "<span class='text-success float-left'>" . display_fa_icon('bookmark', 'Follows') . " " . number_format($manga['count_follows']) . "</span>
								<span class='float-right'><span class='text-primary'>" . display_fa_icon('star', 'Bayesian rating') . " {$manga['manga_bayesian']}</span> <small>" . display_fa_icon('user') . " " . number_format($manga['count_pop']) . "</small></span>";
							break;

						case 'top_rating':
							$return .= "<span class='float-left'><span class='text-primary'>" . display_fa_icon('star', 'Bayesian rating') . " {$manga['manga_bayesian']}</span> <small>" . display_fa_icon('user') . " " . number_format($manga['count_pop']) . "</small></span>
								<span class='text-success float-right'>" . display_fa_icon('bookmark', 'Follows') . " " . number_format($manga['count_follows']) . "</span>";
							break;

						default:
							$return .= "<span class='float-left'>" . display_fa_icon('file', '', '', 'far') . ' ' . display_short_title($manga) . "</span>
							<span class='float-right'>" . display_fa_icon('eye', 'Views') . " " . number_format($manga['chapter_views']) . "</span>";
							break;
					}

					$return .= "</p>
				</li>";
				$displayed[] = $manga['manga_id'];
				$i++;
			}
		}
	}

	$return .= "</ul>";

	return $return;
}

function display_group_banner($group, $style) {
	if ($group->group_banner)
		return "<img class='card-img-bottom' src='" . LOCAL_SERVER_URL . "/images/groups/$group->group_id.$group->group_banner?" . @filemtime(ABS_DATA_BASEPATH . "/groups/$group->group_id.$group->group_banner") . "' width='100%' alt='Group banner' />";
	else
		return "<img class='card-img-bottom' src='" . LOCAL_SERVER_URL . "/images/groups/default." . (in_array($style, [1,3,5]) ? 'light' : 'dark') . ".png' width='100%' alt='Default Group image' />";
}

function display_carousel_js($id, $autoplay = 'true') {
	return "
		$('#{$id}_owl_carousel').owlCarousel({
			loop: true,
			margin: 10,
			dots: false,
			lazyLoad: true,
			autoplay: $autoplay,
			autoplayHoverPause: true,
			responsive:{
				0: { items:2 },
				500: { items:3 },
				768: { items:4 },
				992: { items:5 },
				1440: { items:8 }
			}
		});
		$('#{$id}_owl_stop').click(function() {
			$('#{$id}_owl_carousel').trigger('stop.owl.autoplay');
			$('#{$id}_owl_play').show();
			$('#{$id}_owl_stop').hide();
		});
		$('#{$id}_owl_play').click(function() {
			$('#{$id}_owl_carousel').trigger('play.owl.autoplay');
			$('#{$id}_owl_stop').show();
			$('#{$id}_owl_play').hide();
		});
		$('#{$id}_owl_next').click(function() {
			$('#{$id}_owl_carousel').trigger('next.owl.carousel');
		});
		$('#{$id}_owl_prev').click(function() {
			$('#{$id}_owl_carousel').trigger('prev.owl.carousel');
		});
	";
}
function display_carousel(array $array, $name, $id) {
    $return = "
	<div class='mb-3'>
		<h3 class='d-inline'>$name</h3>
		<div class='float-right btn-group' role='group'>
			<button class='btn btn-secondary' id='{$id}_owl_prev'>" . display_fa_icon('chevron-left') . "</button>
			<button class='btn btn-secondary' id='{$id}_owl_stop'>" . display_fa_icon('pause') . "</button>
			<button class='btn btn-secondary display-none' id='{$id}_owl_play'>" . display_fa_icon('play') . "</button>
			<button class='btn btn-secondary' id='{$id}_owl_next'>" . display_fa_icon('chevron-right') . "</button>
		</div>
	</div>

	<div id='{$id}_owl_carousel' class='mb-4 owl-carousel owl-theme'>";
		foreach($array as $value) {
			$return .= "<div class='large_logo rounded'>
				<div class='hover'>
					<a href='/title/{$value['manga_id']}/" . slugify($value['manga_name']) . "'><img title='" . htmlentities($value['manga_name'], ENT_QUOTES | ENT_HTML5) . "' class='owl-lazy rounded' data-src='" . LOCAL_SERVER_URL . "/images/manga/{$value['manga_id']}.large.jpg' /></a>
				</div>
				<div class='car-caption px-2 py-1'><p class='text-truncate m-0'>" . display_manga_link_v2($value, 'white') . "</p>
				<p class='m-0'>";

			switch ($id) {
				case 'new_titles':
					$return .= "<a class='white' href='/chapter/{$value['chapter_id']}'>" . ($value['chapter'] ? "Chapter {$value['chapter']}" : 'Oneshot') . "</a><span title='" . gmdate(DATETIME_FORMAT, $value['upload_timestamp']) . "' class='float-right'><small>" . get_time_ago($value['upload_timestamp'], FALSE) . "</small></span>";
					break;

				default:
					$return .= "<span title='Follows' class='text-success'>" . display_fa_icon('bookmark', 'Follows') . number_format($value['count_follows']) . "</span>
						<span title='Rating' class='float-right'>" . display_fa_icon('star', 'Bayesian rating') . " {$value['manga_bayesian']} </span>";
					break;
			}

			$return .= "</p></div>
				</div>\n";
		}
	$return .= "</div>";

	return $return;
}

function display_chapter_title($chapter, $icon = 0, $truncate=true) {
	if (is_array($chapter))
		$chapter = (object) $chapter;

	if (!$chapter->volume && $chapter->chapter == '' && in_array($chapter->title, ['Oneshot', '']))
		$return = " <a href='/chapter/$chapter->chapter_id' class='" . ($truncate ? 'text-truncate' : '') . "'>Oneshot</a>";
	elseif ($chapter->title == '')
		$return = " <a href='/chapter/$chapter->chapter_id' class='" . ($truncate ? 'text-truncate' : '') . "'>" . ($chapter->volume ? "Vol. $chapter->volume " : '') . ($chapter->chapter != '' ? "Ch. $chapter->chapter" : '') . "</a>";
	else
		$return = " <a href='/chapter/$chapter->chapter_id' class='" . ($truncate ? 'text-truncate' : '') . "'>" . ($chapter->volume ? "Vol. $chapter->volume " : '') . ($chapter->chapter != '' ? "Ch. $chapter->chapter " : '') . " " . (!$chapter->volume && $chapter->chapter == '' ? '' : '- ') . "$chapter->title</a>";


	if ($icon)
		return display_fa_icon('file', '', '', 'far') . $return;
	else
		return $return;
}

function display_post_link($post) {
	return "<a href='/thread/$post->thread_id/$post->thread_page/#post_$post->post_id'>$post->thread_name</a>";
}

function display_languages_select($selected_lang_id_array = []) {
	global $languages;
	$return = '';

	foreach ($languages as $lang_id => $language) {
		$selected = in_array($lang_id, $selected_lang_id_array) ? 'selected' : '';
		$return .= "<option $selected value='$lang_id' data-content=\"" . display_lang_flag_v3($language) . " $language->lang_name\">$language->lang_name</option>";
	}

	return $return;
}

function display_count_comments($count, $type = '', $id = 0, $name = '') {
	if ($count) {
		if ($type)
			return "<a href='/$type/$id/" . ($name ? slugify($name) . '/' : '') . "comments'><span class='badge badge-secondary' title='$count comments'>" . display_fa_icon('comments', '', '', 'far') . " " . number_format($count) . "</span></a>";
		else
			return "<span class='badge badge-secondary'>" . number_format($count) . "</span>";
	}
}

function display_edit_manga_ext_link($type, $link_id) {
	$return = "
	<div class='input-group' style='margin-bottom: 5px;'>
		<div class='input-group-prepend'>
			<select class='form-control selectpicker z-index-auto' name='link_type[]' data-width='160px'>";
				foreach (MANGA_EXT_LINKS as $l_type => $l_name) {
					$selected = ($l_type == $type) ? "selected" : "";
					$return .= "<option $selected value='$l_type'>$l_name</option>";
				}
			$return .= "</select>
		</div>
		<input type='" . (in_array($type, ['mal', 'mu', 'al']) ? 'number' : 'text') . "' class='form-control' placeholder='Link ID' name='link_id[]' value='" . htmlspecialchars($link_id, ENT_QUOTES) . "'>
		<span class='input-group-append'>
			<button class='btn btn-danger delete_link_button'>" . display_fa_icon('times') . "</button>
		</span>
	</div>
	";

	return $return;
}

function display_manga_ext_links($links_array) {
	$links_array = (array)$links_array;
	if ($links_array) {

		$links_order = [
			'Official' => ['raw', 'engtl'],
			'Retail' => ['amz', 'bw', 'cdj', 'ebj'],
			'Information' => ['mu', 'nu', 'ap', 'al', 'kt', 'mal', 'dj']
		];
		$return = '';

		foreach ($links_order as $category => $types) {
			$tmp = [];
			foreach ($types as $_l) {
				if (!isset($links_array[$_l]))
					continue;
				$tmp[$_l] = $links_array[$_l];
			}
			$cat_array = $tmp;

			if ($cat_array) {
				$return .= "
				<div class='row m-0 py-1 px-0 border-top'>
					<div class='col-lg-3 col-xl-2 strong'>$category:</div>
					<div class='col-lg-9 col-xl-10'><ul class='list-inline mb-0' >";

				foreach ($cat_array as $type => $id) {
					switch ($type) {
						case "mal":
							$return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='https://myanimelist.net/manga/" . htmlspecialchars($id, ENT_QUOTES) . "'>MyAnimeList</a></li>";
							break;

						case "mu":
							$return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='https://www.mangaupdates.com/series.html?id=" . htmlspecialchars($id, ENT_QUOTES) . "'>MangaUpdates</a></li>";
							break;

						case "nu":
							$return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='https://www.novelupdates.com/series/" . htmlspecialchars($id, ENT_QUOTES) . "/'>NovelUpdates</a></li>";
							break;

						case "raw":
							$return .= "<li class='list-inline-item'>" . display_fa_icon('external-link-alt') . " <a rel='noopener noreferrer' target='_blank' href='" . htmlspecialchars($id, ENT_QUOTES) . "'>Raw</a></li>";
							break;

						case "engtl":
							$return .= "<li class='list-inline-item'>" . display_fa_icon('external-link-alt') . " <a rel='noopener noreferrer' target='_blank' href='" . htmlspecialchars($id, ENT_QUOTES) . "'>Official English</a></li>";
							break;

						case "cdj":
							$return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='" . htmlspecialchars($id, ENT_QUOTES) . "'>CDJapan</a></li>";
							break;

						case "amz":
							$return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='" . htmlspecialchars($id, ENT_QUOTES) . "'>Amazon.co.jp</a></li>";
							break;

                        case "ebj":
                            $return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='" . htmlspecialchars($id, ENT_QUOTES) . "'>eBookJapan</a></li>";
                            break;

                        case "bw":
                            $return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='https://bookwalker.jp/" . htmlspecialchars($id, ENT_QUOTES) . "/'>Bookwalker</a></li>";
                            break;

						case "al":
                            $return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='https://anilist.co/manga/" . htmlspecialchars($id, ENT_QUOTES) . "/'>AniList</a></li>";
                            break;

                        case 'kt':
                            $return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='https://kitsu.io/manga/" . htmlspecialchars($id, ENT_QUOTES). "'>Kitsu</a></li>";
                            break;

                        case 'ap':
                            $return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='https://www.anime-planet.com/manga/" . htmlspecialchars($id, ENT_QUOTES). "'>Anime-Planet</a></li>";
                            break;

                        case 'dj':
                            $return .= "<li class='list-inline-item'><img src='" . LOCAL_SERVER_URL . "/images/misc/$type.png' /> <a rel='noopener noreferrer' target='_blank' href='https://www.doujinshi.org/book/" . htmlspecialchars($id, ENT_QUOTES). "'>Doujinshi.org</a></li>";
                            break;

						default:
							break;
					}
				}

				$return .= "</ul></div>
				</div>";
			}
		}

		return $return;
	}
}

function display_js_posting() {
	return "
		$('.bbcode').click(function(){
			var textarea = $(this).parent().parent().next().next().children().children();
			var code = $(this).attr('title');
			var start = textarea.prop('selectionStart');
			var end = textarea.prop('selectionEnd');
			var v = textarea.val();
			var textBefore = v.substring(0, start);
			var textAfter  = v.substring(end, v.length);
			if (start === end) {
				textarea.val(textBefore + '[' + code + '][/' + code + ']' + textAfter);
			}
			else {
				var textSelected = v.substring(start, end);
				textarea.val(textBefore + '[' + code + ']' + textSelected + '[/' + code + ']' + textAfter);
			}
		});

		$('.emoji').click(function(){
			var textarea = $(this).parent().parent().next().children().children();
			var cursorPos = textarea.prop('selectionStart');
			var v = textarea.val();
			var textBefore = v.substring(0, cursorPos);
			var textAfter  = v.substring(cursorPos, v.length);
			textarea.val(textBefore + $(this).val() + textAfter);
		});
	";
}

function display_edit_manga_relation_entry($array = ['relation_id' => '', 'related_manga_id' => '', 'manga_name' => '']) {
	global $relation_types;

	$return = "
	<div class='input-group' style='margin-bottom: 5px;'>
		<div class='input-group-prepend'>
			<select class='form-control selectpicker z-index-auto' name='relation_type[]' data-width='150px'>";
				foreach ($relation_types as $relation) {
					$selected = ($relation->relation_id == $array['relation_id']) ? "selected" : "";
					$return .= "<option $selected value='$relation->relation_id'>$relation->relation_name</option>";
				}
			$return .= "</select>
		</div>
		<input type='number' class='form-control' placeholder='Related manga ID' name='related_manga_id[]' value='{$array['related_manga_id']}'>
		<span class='input-group-append'>
			<button class='btn btn-danger delete_relation_button'>" . display_fa_icon('times') . "</button>
		</span>
	</div>
	";

	return $return;
}

function display_manga_relations($manga_relations) {
	global $hentai_toggle, $relation_types;

	if ($manga_relations) {
		$return = "
		<div class='row m-0 py-1 px-0 border-top'>
			<div class='col-lg-3 col-xl-2 strong'>Related:</div>
			<div class='col-lg-9 col-xl-10'>
				<ul style='margin-bottom: 0;' class='list-unstyled'>";
				foreach ($manga_relations as $related_manga) {
					if (!$related_manga['manga_hentai'] || ($related_manga['manga_hentai'] && $hentai_toggle))
						$return .= "<li>" . display_fa_icon('book') . " " . display_manga_link($related_manga['related_manga_id'], $related_manga['manga_name'], $related_manga['manga_hentai'], 0) . " <span class='small'>(" . $relation_types->{$related_manga['relation_id']}->relation_name . ")</span></li>";
				}
			$return .= "</ul>
			</div>
		</div>";

		return $return;
	}
}

function display_bbcode_textarea($post_text = "") {
	$return = "
	<div class='form-group'>
		<div class='col-xs-12'>";
			foreach (BBCODE as $glyph => $text) {
				$return .= "<button title='$text' id='$glyph' type='button' class='btn btn-sm btn-secondary bbcode'>" . display_fa_icon($glyph) . "</button>";
			}
		$return .= "<button title='Emojis' type='button' class='btn btn-sm btn-secondary emoji-toggle'>😀</button></div>
	</div>
	<div class='form-group emojis display-none'>
		<div class='col-xs-12'>";
			foreach (EMOJIS as $text) {
				$return .= "<button type='button' class='btn btn-sm btn-secondary emoji' value='$text'>$text</button>";
			}
		$return .= "</div>
	</div>
	<div class='form-group'>
		<div class='col-xs-12'>
			<textarea rows='10' type='text' class='form-control' id='text' name='text' placeholder='BBCode allowed' required>$post_text</textarea>
		</div>
	</div>
	";

	return $return;
}

function display_forum($forum, $user) {
	if (validate_level($user, $forum->view_level)) {
		$return = "
		<div class='d-flex row m-0 py-1 border-bottom align-items-center'>
			<div class='col-auto px-2 ' >
				<a href='/forum/$forum->forum_id'><img src='" . LOCAL_SERVER_URL . "/images/forums/" . str_replace(' ', '-', $forum->forum_name) . ".svg' width='70px' ></a>
			</div>
			<div class='col p-0 text-truncate'>
				<div class='row m-2'>
					<div class='col-md-4 px-0' >
						<h5><strong><a href='/forum/$forum->forum_id'>$forum->forum_name</a></strong></h5>
						<p class='m-0 d-none d-xl-inline'>$forum->forum_description</p>
					</div>
					<div class='col-md-2 px-0 d-none d-lg-inline' class='text-center' >
						<p class='mb-2'>" . number_format($forum->total_threads) . " threads</p>
						<p class='m-0'>" . number_format($forum->total_posts) . " posts</p>
					</div>
					<div class='col-md-6 px-0' >" .
						($forum->last_post_timestamp ? "<p class='mb-2'>" . get_time_ago($forum->last_post_timestamp) . " by " . display_user_link($forum->last_post_user_id, $forum->username, $forum->level_colour, $forum->show_premium_badge, $forum->show_md_at_home_badge) . "</p>
						<p class='m-0'>in <a href='/thread/$forum->last_thread_id/$forum->thread_page/#last_post' title='$forum->thread_name'>$forum->thread_name</a></p>" : "")
					. "</div>
				</div>
			</div>
		</div>
		";

		if ($forum->subforum_ids) {
			$subforum_ids = explode(',', $forum->subforum_ids);
			$subforum_names = explode(',', $forum->subforum_names);

			$return .= "<div class='row m-0 border-bottom'><div class='col p-2'><strong>Subforums</strong>: ";

			foreach(array_combine($subforum_ids, $subforum_names) as $key => $value) {
				$return .= "<a href='/forum/$key'>$value</a>, ";
			}
			$return = rtrim($return, ', ');

			$return .= "</div></div>";
		}

		return $return;
	}
}

function display_edit_post_v2($post, $user) {
	if (validate_level($user, 'pr') || $post->user_id == $user->user_id) {
		return "
		<tr class='display-none' id='post_edit_$post->post_id'>
			<td width='120px' class='text-center d-none d-md-table-cell'>" . display_avatar($post->avatar, $post->user_id) . "</td>
			<td class='p-3'>
				<form class='post_edit_form' method='post' id='$post->post_id'>" .
					display_bbcode_textarea($post->text) .
					"<div class='row justify-content-between'>
				        <div class='col-auto order-2'>
						    <button type='submit' class='btn btn-success edit_post_button' id='post_edit_button_$post->post_id'>" . display_fa_icon('pencil-alt') . " Save</button>
                        </div>
				        <div class='col-auto order-1'>
						    <button title='Cancel' data-post-id='$post->post_id' type='button' class='btn btn-warning cancel_post_edit_button'>" . display_fa_icon('times') . " Cancel</button>
                        </div>
					</div>
				</form>
			</td>
		</tr>";
	}
}

function display_post_v2($post, $html, $user, $page = '') {
    $isModerated = isset($post->moderated) && $post->moderated;
    if ($isModerated && !$user->display_moderated) {
        return display_post_moderated($post, $user);
    }

    $posterIsStaff = isset($post->level_id) ? validate_level($post, 'pr') : false;
    $userIsStaff = validate_level($user, 'pr');
    $userIsPoster = $post->user_id === $user->user_id;
    $editorIsPoster = $post->user_id === $post->edit_user_id;
    $userNote = array_key_exists($post->user_id, $user->notes) ? $user->notes[$post->user_id]['note'] : '';

    $EDIT_GRACE_MINUTES = 5;
    $displayEditMessage = $post->edit_timestamp && (!$editorIsPoster || ($post->edit_timestamp > $post->timestamp + $EDIT_GRACE_MINUTES*60));

    if (strpos($html,'@') !== false) {
        // We have a potential mention. parse the mentions out and see if any of those are for our $user
        $html = preg_replace(';(@<a href="'.URL.'user/'.$user->user_id.'"[^<]+</a>);', '<span class="mentioned">$1</span>', $html);
    }

	$return = "
	<tr class='post' id='post_$post->post_id'>
		<td style='max-width: 200px; width: 120px' class='text-center d-none d-md-table-cell'>
		    <div style='text-align: left; line-height: 0.9rem'>
		        <span style='overflow-wrap: break-word'>" . display_user_link_v2($post, $userNote) . "</span><br />" .
            ($posterIsStaff ? "<small>{$post->level_name}</small>" : '') . "</div>" .
            display_avatar($post->avatar, $post->user_id) .
        "</td>
		<td class='pb-3'>" .
			"<div class='d-md-none d-lg-none d-xl-none mb-2'>" . display_fa_icon('user') . ' ' . display_user_link_v2($post, $userNote) . "</div>
			<span title='" . gmdate(DATETIME_FORMAT, $post->timestamp) . "' class='float-right'>" .
            (($page === 'message' && $post->user_id === $user->user_id && $post->seen) ? display_fa_icon('check') : '') .
            display_fa_icon('clock', '', '', 'far') . " " .
			($page != 'message' ? "<a class='permalink' href='/thread/$post->thread_id/$post->thread_page/#post_$post->post_id'>" . get_time_ago($post->timestamp) . "</a>" : get_time_ago($post->timestamp)) . "</span>".
            ($isModerated ? "<span class='text-warning float-right mr-1' title='This post has been marked as moderated.'>" . display_fa_icon('comment-slash') . "</span>" : '') .
			"<!--hr class='clearfix my-2'-->
			<div style='min-height: 100px;' class='postbody mb-3 mt-4'>" .
			nl2br(make_links_clickable($html)) . "</div>" .
			($displayEditMessage ? "<div class='my-3'><em>Last edited " . get_time_ago($post->edit_timestamp) . " by " . display_user_link($post->edit_user_id, $post->editor_username, $post->editor_level_colour) . ".</em></div>" : "");

			if ($user->user_id && validate_level($user, 'member') && $page != 'message') { //not in PMs
				$return .= "<div class='post-btns btn-group btn-group-sm d-flex justify-content-end'>";

				// quoting isn't implemented yet
                //$return .= "<button title='Quote' data-post-id='$post->post_id' type='button' class='btn btn-sm btn-secondary text-muted'>" . display_fa_icon('comment') . " <span class='d-none d-md-inline'>Quote</span></button>";

				if ($userIsPoster || $userIsStaff) {
				    $return .= "<button title='Edit' data-post-id='$post->post_id' type='button' class='post_edit_button btn btn-sm btn-secondary text-info'>" . display_fa_icon('pencil-alt') . " <span class='d-none d-md-inline'>Edit</span></button>";
                }
                if (!$userIsPoster) {
                    $return .= "<button title='Report' data-item-id='$post->post_id' data-type-name='comment' data-type-id='3' type='button' class='report-button btn btn-sm btn-secondary text-warning'>" . display_fa_icon('flag') . " <span class='d-none d-md-inline'>Report</span></button>";
                }
				if ($userIsStaff) {
                    $return .= "<div class='dropdown d-inline-block'>
                            <button class='dropdown-toggle btn btn-sm border-left-0 btn-secondary' style='border-top-left-radius: 0; border-bottom-left-radius: 0;' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>" . display_fa_icon('wrench') . " </button>
                            <div class='dropdown-menu dropdown-menu-right py-0 border-0 m-0'>";
                    if ($isModerated) {
                        $return .= "<button title='Unmoderate' data-post-action='post_moderate' data-post-id='$post->post_id' data-value='0' type='button' class='btn btn-sm btn-block m-0 rounded-0 btn-secondary text-warning'>" . display_fa_icon('comment-dots') . " Unmoderate</button>";
                    } else {
                        $return .= "<button title='Moderate' data-post-action='post_moderate' data-post-id='$post->post_id' data-value='1' type='button' class='btn btn-sm btn-block m-0 rounded-0 btn-secondary text-warning'>" . display_fa_icon('comment-slash') . " Moderate</button>";
                    }
                    $return .= "<button title='Spoiler' data-post-action='post_spoiler' data-post-id='$post->post_id' type='button' class='btn btn-sm btn-block m-0 rounded-0 btn-secondary text-warning'>" . display_fa_icon('eye-slash') . " Spoiler</button>";
                    if ($post->edit_timestamp !== 0) {
                        $return .= "<button title='History' data-toggle='modal' data-target='#post_history_modal' data-post-id='$post->post_id' type='button' class='btn btn-sm btn-block m-0 rounded-0 btn-secondary text-info'>" . display_fa_icon('history') . " History</button>";
                    }
                    $return .= "<button title='Delete' data-post-action='post_delete' data-post-id='$post->post_id' type='button' class='btn btn-sm btn-block m-0 rounded-0 btn-secondary text-danger'>" . display_fa_icon('trash') . " Delete</button>";
                    $return .= "</div></div>";
                }
                $return .= "</div>";
			}

		$return .= "</td>";
	$return .= "</tr>";

	return $return;
}

function display_post_blocked($post, $user) {
    $return = "
	<tr class='post post-blocked' id='post_$post->post_id'>
		<td colspan='2'>This post is hidden because you blocked " . display_fa_icon('user') . " " . display_user_link_v2($post) . "." .
        "<span title='" . gmdate(DATETIME_FORMAT, $post->timestamp) . "' class='float-right'>" . display_fa_icon('clock', '', '', 'far') . " " .
        "<a href='/thread/$post->thread_id/$post->thread_page/#post_$post->post_id'>" . get_time_ago($post->timestamp) . "</a></span>";

    $return .= "<div class='clearfix'></div>";

    $return .= "</td>";
    $return .= "</tr>";

    return $return;
}

function display_post_moderated($post, $user) {
    $return = "
	<tr class='post post-moderated' id='post_$post->post_id'>
		<td colspan='2'>
		    <span title='" . gmdate(DATETIME_FORMAT, $post->timestamp) . "' class='float-right'>" . display_fa_icon('clock', '', '', 'far') . " " .
            "<a href='/thread/$post->thread_id/$post->thread_page/#post_$post->post_id'>" . get_time_ago($post->timestamp) . "</a></span>";

    if (validate_level($user, 'mod')) {
        $return .= "<button title='Unmoderate' data-post-id='$post->post_id' data-value='0' type='button' class='post_moderate_button btn btn-sm btn-warning float-right mr-1'>" . display_fa_icon('comment') . " <span class='d-none d-md-inline'>Unmoderate</span></button>";
    }

    $return .= "<span class='text-warning mx-1' title='This post has been marked as moderated.'>" . display_fa_icon('comment-slash') . "</span> This post by <span>" . display_user_link_v2($post) . "</span> has been marked as moderated.";

    $return .= "<div class='clearfix'></div></td></tr>";

    return $return;
}

function display_manga_rating_button($user_id, $user_rating, $manga_id, $style = 0) {
	$return = "<div class='btn-group " . ($style ? 'btn-group-xs' : '') . "'>
		<button " . (!$user_id ? "disabled title='You need to log in to use this function.'" : "") . " type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>" .
			display_fa_icon('star') . " " . ($user_rating ? $user_rating : "") . " <span class='caret'></span>
		</button>
		<div class='dropdown-menu'>";
			foreach (RATINGS as $key => $value) {
				$disabled = ($user_rating == $key) ? "disabled" : "";
				$return .= "<a class='$disabled dropdown-item manga_rating_button' id='$key' data-manga-id='$manga_id' href='#'>($key) $value</a>";
			}
		$return .= "</div>
	</div>";

	return $return;
}

function display_pagination_v2($paging, $type, $page, $string = "") {
	if ($paging['last_page'] == 1)
		$return = "";
	elseif ($paging['current_page'] <= $paging['last_page']) {
		$return = "
			<p class='mt-3 text-center'>Showing " . number_format($paging['offset'] + 1) . " to " . number_format(min($paging['num_rows'], $paging['limit'] * $paging['current_page'])) . " of " . number_format($paging['num_rows']) . " $type</p>
			<nav>
				<ul style='margin: 0; cursor: pointer;' class='pagination justify-content-center'>";

		if (strpos($page, 'search') === 0) {
			$page = substr($page, 6);
			$return .= "<li class='page-item {$paging['previous_class']}'><a class='page-link' href='/search?s={$paging['sort']}&p=1$page#listing'>" . display_fa_icon('angle-double-left', 'Jump to first page') . "</a></li>";

			for ($i = 2; $i >= 1; $i--) {
				$pg = $paging['current_page'] - $i;
				if ($pg > 0)
					$return .= "<li class='page-item'><a class='page-link' href='/search?s={$paging['sort']}&p=$pg$page#listing'>$pg</a></li>";
			}

			$return .= "<li class='page-item active'><a class='page-link'>{$paging['current_page']}</a></li>";

			for ($i = 1; $i <= 2; $i++) {
				$pg = $paging['current_page'] + $i;
				if ($pg <= $paging['last_page'] && ($pg - $paging['current_page'] <= 2 || in_array($pg, [4,5])))
					$return .= "<li class='page-item'><a class='page-link' href='/search?s={$paging['sort']}&p=$pg$page#listing'>$pg</a></li>";
			}

			$return .= "<li class='page-item {$paging['next_class']}'><a class='page-link' href='/search?s={$paging['sort']}&p={$paging['last_page']}$page#listing'>" . display_fa_icon('angle-double-right', 'Jump to last page') . "</a></li>";
		}
		elseif (strpos($type, 'chapters') === 0 || strpos($page, 'updates') === 0) {
			$return .= "<li class='page-item {$paging['previous_class']}'><a class='page-link' href='/$page/1/$string'>" . display_fa_icon('angle-double-left', 'Jump to first page') . "</a></li>";

			for ($i = 2; $i >= 1; $i--) {
				$pg = $paging['current_page'] - $i;
				if ($pg > 0)
					$return .= "<li class='page-item'><a class='page-link' href='/$page/$pg/$string'>$pg</a></li>";
			}

			$return .= "<li class='page-item active'><a class='page-link'>{$paging['current_page']}</a></li>";

			for ($i = 1; $i <= 2; $i++) {
				$pg = $paging['current_page'] + $i;
				if ($pg <= $paging['last_page'] && ($pg - $paging['current_page'] <= 2 || in_array($pg, [4,5])))
					$return .= "<li class='page-item'><a class='page-link' href='/$page/$pg/$string'>$pg</a></li>";
			}

			$return .= "<li class='page-item {$paging['next_class']}'><a class='page-link' href='/$page/{$paging['last_page']}/$string'>" . display_fa_icon('angle-double-right', 'Jump to last page') . "</a></li>";
		}
		else {
			$return .= "<li class='page-item {$paging['previous_class']}'><a class='page-link' href='/$page/{$paging['sort']}/1/$string'>" . display_fa_icon('angle-double-left', 'Jump to first page') . "</a></li>";

			for ($i = 2; $i >= 1; $i--) {
				$pg = $paging['current_page'] - $i;
				if ($pg > 0)
					$return .= "<li class='page-item'><a class='page-link' href='/$page/{$paging['sort']}/$pg/$string'>$pg</a></li>";
			}

			$return .= "<li class='page-item active'><a class='page-link'>{$paging['current_page']}</a></li>";

			for ($i = 1; $i <= 2; $i++) {
				$pg = $paging['current_page'] + $i;
				if ($pg <= $paging['last_page'] && ($pg - $paging['current_page'] <= 2 || in_array($pg, [4,5])))
					$return .= "<li class='page-item'><a class='page-link' href='/$page/{$paging['sort']}/$pg/$string'>$pg</a></li>";
			}

			$return .= "<li class='page-item {$paging['next_class']}'><a class='page-link' href='/$page/{$paging['sort']}/{$paging['last_page']}/$string'>" . display_fa_icon('angle-double-right', 'Jump to last page') . "</a></li>";
		}

		$return .= "</ul>
			</nav>";
	}
	else
		$return = display_alert("warning", "Warning", "No results found.");

	return $return;
}

function display_pagination_forum($paging, $page, $id) {
	if ($paging['last_page'] == 1)
		$return = '';
	elseif ($paging['current_page'] <= $paging['last_page']) {
		$return = "
		<nav>
			<ul style='cursor: pointer;' class='my-0 justify-content-end pagination'>

				<li class='page-item {$paging['previous_class']}'><a class='page-link' href='/$page/$id/1'>" . display_fa_icon('angle-double-left', 'Jump to first page') . "</a></li>";

				for ($i = 2; $i >= 1; $i--) {
					$pg = $paging['current_page'] - $i;
					if ($pg > 0)
						$return .= "<li class='page-item'><a class='page-link' href='/$page/$id/$pg'>$pg</a></li>";
				}

				$return .= "<li class='page-item active'><a class='page-link'>{$paging['current_page']}</a></li>";

				for ($i = 1; $i <= 2; $i++) {
					$pg = $paging['current_page'] + $i;
					if ($pg <= $paging['last_page'] && ($pg - $paging['current_page'] <= 2 || in_array($pg, [4,5])))
						$return .= "<li class='page-item'><a class='page-link' href='/$page/$id/$pg'>$pg</a></li>";
				}

				$return .= "<li class='page-item {$paging['next_class']}'><a class='page-link' href='/$page/$id/{$paging['last_page']}'>" . display_fa_icon('angle-double-right', 'Jump to last page') . "</a></li>
			</ul>
		</nav>";
	}
	else
		$return = '';

	return $return;
}

function display_manga_link($manga_id, $manga_name, $manga_hentai, $trim = 1, $class = '') {
	if ($trim)
		return "<a class='$class' title='" . htmlentities($manga_name, ENT_QUOTES) . "' href='/title/$manga_id/" . slugify($manga_name) . "'>" . mb_strimwidth($manga_name, 0, 40, "...") . "</a>" . display_labels($manga_hentai);
	else
		return "<a class='$class' title='" . htmlentities($manga_name, ENT_QUOTES) . "' href='/title/$manga_id/" . slugify($manga_name) . "'>$manga_name</a>" . display_labels($manga_hentai);
}

function display_manga_link_v2($manga, $white = '', $hide_labels=false, $truncate=true) {
	if (is_array($manga))
		$manga = (object) $manga;

	return "<a class='manga_title " . ($truncate ? 'text-truncate ' : '') . ($white ? 'white' : '')
      . "' title='" . htmlentities($manga->manga_name, ENT_QUOTES)
      . "' href='/title/$manga->manga_id/" . slugify($manga->manga_name)
      . "'>$manga->manga_name</a>"
      . ( $hide_labels ? '' : display_labels($manga->manga_hentai));
}

function display_group_link_v2($group) {
	if (is_array($group)) {
		$group['group_id_2'] = $group['group_id_2'] ?? 0;
		$group['group_id_3'] = $group['group_id_3'] ?? 0;

		$group = (object) $group;
	}

	$return = "<a href='/group/$group->group_id/" . slugify($group->group_name) . "'>$group->group_name</a>";

	if (isset($group->group_id_2) && $group->group_id_2 > 0)
		$return .= " | <a href='/group/$group->group_id_2/" . slugify($group->group_name_2) . "'>$group->group_name_2</a>";

	if (isset($group->group_id_3) && $group->group_id_3 > 0)
		$return .= " | <a href='/group/$group->group_id_3/" . slugify($group->group_name_3) . "'>$group->group_name_3</a>";

	return $return;
}

function display_user_link_v2($user, $note = '') {

	if (is_array($user))
		$user = (object) $user;

	$levelClassname = str_replace(' ', '', ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $user->level_name ?? 'guest')), '_'));

	$string = "<a class='user_level_$levelClassname' style='color: #$user->level_colour; ' href='/user/$user->user_id/" . strtolower($user->username) . "'>$user->username</a>";

	if ($user->show_premium_badge ?? false)
		$string .= " <a href='/support'>" . display_fa_icon('gem', 'Supporter', '', 'far') . "</a>";

	if ($user->show_md_at_home_badge ?? false)
		$string .= " <a href='/md_at_home'>" . display_fa_icon('network-wired', 'MD@H Host', '', 'fas' . ($user->show_md_at_home_badge == 2 ? ' text-warning' : '')) . "</a>";

	if ($user->is_thread_starter ?? false)
	    $string .= " <span class='badge badge-primary'>OP</span>";
	if ($note)
	    $string .= " <span class='badge badge-secondary' style='white-space: normal;'>" . htmlspecialchars($note, ENT_QUOTES) . "</span>";

	return $string;
}

function display_user_link($user_id, $username, $level_colour, $show_badge = 0, $show_mah_badge = 0) {
	$string = $show_badge ? " <a href='/support'>" . display_fa_icon('gem', 'Supporter', '', 'far') . "</a>" : '';
	$string2 = $show_mah_badge ? " <a href='/md_at_home'>" . display_fa_icon('network-wired', 'MD@H Host', '', 'fas' . ($show_mah_badge == 2 ? ' text-warning' : '')) . "</a>" : '';
	return "<a style='color: #$level_colour; ' id='$user_id' href='/user/$user_id/" . strtolower($username) . "'>$username</a>" . $string . $string2;
}

function display_avatar($ext, $id, $limit = 1) {
	if ($ext)
		return "<img class='rounded " . ($limit ? "avatar" : "avatar-fit") . " mt-2' alt='Avatar' src='" . LOCAL_SERVER_URL . "/images/avatars/$id.$ext?" . @filemtime(ABS_DATA_BASEPATH . "/avatars/$id.$ext") . "'>";
	else {
		//$avatar = rand(0, 4);
		return "<img class='rounded " . ($limit ? "avatar" : "avatar-fit") . " mt-2' alt='Avatar' src='" . LOCAL_SERVER_URL . "/images/avatars/" . DEFAULT_AVATARS[0] . "'>";
	}
}

function display_list_banner($user, $style) {
	if ($user->list_banner)
		return "<a href='/user/$user->user_id/" . strtolower($user->username) . "'><img width='100%' alt='Banner' src='" . LOCAL_SERVER_URL . "/images/lists/$user->user_id.$user->list_banner?" . @filemtime(ABS_DATA_BASEPATH . "/lists/$user->user_id.$user->list_banner") . "'></a>";
	else
		return "<a href='/user/$user->user_id/" . strtolower($user->username) . "'><img width='100%' alt='Banner' src='" . LOCAL_SERVER_URL . "/images/lists/default." . (in_array($style, [1,3,5]) ? 'light' : 'dark') . ".png'></a>";
}

function jquery_get($name, $id, $button_glyph, $button_text, $button_text_alt, $success_msg, $after) {
	return "
	$('#{$name}_button').click(function(event) {

        var button = $(this)
		var buttonHtml = button.html()
		var successMsg = \"" . display_alert("success", "Success", $success_msg) . "\";

		button.html(\"" . display_fa_icon('spinner', '', 'fa-pulse') . " $button_text_alt...\").attr('disabled', true);

		$.ajax({
			url: '/ajax/actions.ajax.php?function=$name" . ($id ? "&id=$id" : "") . "',
			type: 'GET',
			cache: false,
			headers: {'cache-control': 'no-cache'},
			contentType: false,
			processData: false,
			async: true,
			success: function (data) {
				if (!data) {
					$('#message_container').html(successMsg).show().delay(" . FADE_DURATION . ").fadeOut();
					$after
				}
				else {
					$('#message_container').html(data).show().delay(" . FADE_DURATION . ").fadeOut();
				}
				button.html(buttonHtml).attr('disabled', false);
            },
		});

		event.preventDefault();
	});
	";
}

function jquery_post($name, $id, $button_glyph, $button_text, $button_text_alt, $success_msg, $after) {
	return "
	$('#{$name}_form').submit(function(event) {

		Array.prototype.slice.call(this.querySelectorAll('input[type=file]'))
		.filter(function(n) { return n && !n.files.length })
		.forEach(function(n) { n.parentNode.removeChild(n) });

        var button = $('#{$name}_button')
		var buttonHtml = button.html()
		var formData = new FormData(this);

		var success_msg = \"" . display_alert("success", "Success", $success_msg) . "\";

		button.html(\"" . display_fa_icon('spinner', '', 'fa-pulse') . " $button_text_alt...\").attr('disabled', true);

		$.ajax({
			url: '/ajax/actions.ajax.php?function=$name" . ($id ? "&id=$id" : "") . "',
			type: 'POST',
			data: formData,
			cache: false,
			headers: {'cache-control': 'no-cache'},
			contentType: false,
			processData: false,
			async: true,
			success: function (data) {
				if (!data) {
					$('#message_container').html(success_msg).show().delay(" . FADE_DURATION . ").fadeOut();
					$after
				}
				else {
					$('#message_container').html(data).show().delay(" . FADE_DURATION . ").fadeOut();
				}
				button.html(buttonHtml).attr('disabled', false);
            },
		});

		event.preventDefault();
	});
	";
}

function js_display_file_select($button = '') {
	return "
	$('.btn-file :file').on('fileselect', function(event, numFiles, label) {
		var input = $(this).parents('.input-group').find(':text');
		log = numFiles > 1 ? numFiles + ' files selected' : label;

		if(input.length)
			input.val(log);
		else
			if(log) alert(log);

		" . ($button ? "$('$button').focus();" : "") . "
	});";
}

function display_genres_checkboxes($grouped_genres, $selected_genres = [], $excluded_genres = [], $isTertiary = true, $useIndeterminate = false, $inputName = 'manga_genres[]') {
    if (!is_array($selected_genres)) {
        $selected_genres = explode(',', $selected_genres);
    }
    if (!is_array($excluded_genres)) {
        $excluded_genres = explode(',', $excluded_genres);
    }

    $return = '';
	foreach ($grouped_genres AS $group => $genres) {
        $return .= "<div class='row mb-2'><span class='col-12 strong border-bottom mb-1'>$group</span>";

        foreach ($genres AS $genre) {
            $id = $genre['id'];
            $name = $genre['name'];
            $classes = ($isTertiary ? 'tertiary ' : '') . ($useIndeterminate ? 'indeterminate-mark ' : '');
            $chipClasses = $group === "Content" ? "badge-warning" : "badge-secondary";
            $state = in_array($id, $excluded_genres) ? 2 : (in_array($id, $selected_genres) ? 1 : 0);
            $return .= "
			<div class='col-6 col-md-4 col-lg-3 col-xl-2'>
				<div class='custom-control custom-checkbox form-check py-0'>
					<input type='checkbox' class='custom-control-input $classes' id='checkbox-tag-$id' name='$inputName' value='$id' data-state='$state'>
					<label class='custom-control-label' for='checkbox-tag-$id'><span class='badge $chipClasses'>$name</span></label>
				</div>
			</div>";
        }
        $return .= "</div>";
    }
    return $return;
}

function display_genres_dropdown($grouped_genres, $selected_genres = [], $field_name = 'manga_genres') {
    if (!is_array($selected_genres)) {
        $selected_genres = explode(',', $selected_genres);
    }
    $return = "
            <select class='chip-input form-control' name='{$field_name}[]' data-defaults='" . implode(',', $selected_genres) . "' data-separator=',' data-select-behaviour='check' data-grouped='1'>
                <option value='' disabled selected>Select a tag</option>";

    foreach ($grouped_genres AS $group => $genres) {
        $return .= "<optgroup label='{$group}'>";
        foreach ($genres as $genre) {
            $chipClasses = $group === "Content" ? "badge-warning" : "badge-secondary";
            $return .= "<option value='{$genre['id']}' data-chip-classes='$chipClasses'>{$genre['name']}</option>";
        }
        $return .= "</optgroup>";
    }

    $return .= "</select>";

    return $return;
}

function display_genres_checkboxes_search($genres) {
	$return = "<div class='row'>";

    foreach ($genres AS $id => $name) {
			$return .= "
			<div class='col-6 col-md-4 col-lg-3 col-xl-2'>
				<div class='custom-control custom-checkbox'>
					<input type='checkbox' class='custom-control-input' id='genre_$id' name='manga_genres[]' data-state='0' data-id='$id'>
					<label class='custom-control-label' for='genre_$id'><span class='badge badge-secondary'>$name</span></label>
				</div>
			</div>";
	}
	$return .= "</div>";

	return $return;
}

function display_genres($genres, $genre_array) {
	$text = "";

	foreach ($genre_array as $genre) {
		$text .= "<span class='badge badge-secondary'><a class='genre' href='/search?genres_inc=$genre'>" . $genres[$genre] . "</a></span> ";
	}

	return $text;
}

function display_grouped_genres($grouped_genres, $genre_array) {
    $text = "";

    $genre_array_grouped = [];
    foreach ($genre_array as $id) {
        $genre_array_grouped[$grouped_genres[$id]['group']][$id] = $grouped_genres[$id]['name'];
    }
    foreach ($genre_array_grouped as $group => $genres) {
        $text .= "<div class='chip-group mb-1'><span class='small mr-2'>$group</span><div class='chip-array my-0'>";
        foreach ($genres as $id => $genre) {
            $color = $group === 'Content' ? 'badge-warning' : 'badge-secondary';
            $text .= "<a class='badge $color' href='/search?genres_inc={$id}'>{$genre}</a> ";
        }
        $text .= "</div></div>";
    }

    return $text;
}

function display_sort($page, $search, $sort, $type, $glyph, $string = '') {

	switch ($page) {
		case "users":
			$search_username = isset($search['username']) ? "/1/{$search['username']}" : "";

			switch ($type) {
				case "username":
					if ($sort == 1)
						return "<a href='/$page/2$search_username'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 2)
						return "<a href='/$page/1$search_username'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page/1$search_username'>" . display_fa_icon("sort", "Sort Asc") . "</a>";

					break;

				case "uploads":
					if ($sort == 3)
						return "<a href='/$page/4$search_username'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 4)
						return "<a href='/$page/3$search_username'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page/4$search_username'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

				case "views":
					if ($sort == 5)
						return "<a href='/$page/6$search_username'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 6)
						return "<a href='/$page/5$search_username'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page/6$search_username'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "level":
					if ($sort == 7)
						return "<a href='/$page/8$search_username'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 8)
						return "<a href='/$page/7$search_username'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page/8$search_username'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				default:
					break;
			}

		case "groups":
			$search_group_name = isset($search['group_name']) ? "/1/{$search['group_name']}" : "";

			switch ($type) {
				case "group_name":
					if ($sort == 1)
						return "<a href='/$page/2$search_group_name'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 2)
						return "<a href='/$page/1$search_group_name'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page/1$search_group_name'>" . display_fa_icon("sort", "Sort Asc") . "</a>";

					break;

				case "group_likes":
					if ($sort == 3)
						return "<a href='/$page/4$search_group_name'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 4)
						return "<a href='/$page/3$search_group_name'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page/4$search_group_name'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "group_views":
					if ($sort == 5)
						return "<a href='/$page/6$search_group_name'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 6)
						return "<a href='/$page/5$search_group_name'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page/6$search_group_name'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "group_follows":
					if ($sort == 7)
						return "<a href='/$page/8$search_group_name'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 8)
						return "<a href='/$page/7$search_group_name'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page/8$search_group_name'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "group_comments":
					if ($sort == 9)
						return "<a href='/$page/10$search_group_name'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 10)
						return "<a href='/$page/9$search_group_name'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page/10$search_group_name'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "group_last_updated":
					if ($sort == 11)
						return "<a href='/$page/12$search_group_name'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 12)
						return "<a href='/$page/11$search_group_name'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page/12$search_group_name'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				default:
					break;
			}

		case "genre":
		case "list":
		case "titles":
		case "follows":
		case "group":
		case "user":
			switch ($type) {
				case "manga_last_updated":
					if ($sort == 0)
						return "<a href='/$page$string/1'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 1)
						return "<a href='/$page$string/0'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page$string/0'>" . display_fa_icon("sort", "Sort Asc") . "</a>";

					break;

				case "manga_name":
					if ($sort == 2)
						return "<a href='/$page$string/3'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 3)
						return "<a href='/$page$string/2'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page$string/2'>" . display_fa_icon("sort", "Sort Asc") . "</a>";

					break;

				case "manga_comments":
					if ($sort == 4)
						return "<a href='/$page$string/5'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 5)
						return "<a href='/$page$string/4'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page$string/5'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "manga_rating":
					if ($sort == 6)
						return "<a href='/$page$string/7'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 7)
						return "<a href='/$page$string/6'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page$string/7'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "manga_views":
					if ($sort == 8)
						return "<a href='/$page$string/9'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 9)
						return "<a href='/$page$string/8'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page$string/9'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "manga_follows":
					if ($sort == 10)
						return "<a href='/$page$string/11'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 11)
						return "<a href='/$page$string/10'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/$page$string/11'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				default:
					break;
			}

		case "search":
			switch ($type) {
				case "manga_last_updated":
					if ($sort == 0)
						return "<a href='/search?s=1$string#listing'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 1)
						return "<a href='/search?s=0$string#listing'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/search?s=0$string#listing'>" . display_fa_icon("sort", "Sort Asc") . "</a>";

					break;

				case "manga_name":
					if ($sort == 2)
						return "<a href='/search?s=3$string#listing'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 3)
						return "<a href='/search?s=2$string#listing'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/search?s=2$string#listing'>" . display_fa_icon("sort", "Sort Asc") . "</a>";

					break;

				case "manga_comments":
					if ($sort == 4)
						return "<a href='/search?s=5$string#listing'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 5)
						return "<a href='/search?s=4$string#listing'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/search?s=5$string#listing'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "manga_rating":
					if ($sort == 6)
						return "<a href='/search?s=7$string#listing'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 7)
						return "<a href='/search?s=6$string#listing'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/search?s=7$string#listing'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "manga_views":
					if ($sort == 8)
						return "<a href='/search?s=9$string#listing'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 9)
						return "<a href='/search?s=8$string#listing'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/search?s=9$string#listing'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				case "manga_follows":
					if ($sort == 10)
						return "<a href='/search?s=11$string#listing'>" . display_fa_icon("sort-$glyph-down", "Sort Desc") . "</a>";
					elseif ($sort == 11)
						return "<a href='/search?s=10$string#listing'>" . display_fa_icon("sort-$glyph-up", "Sort Asc") . "</a>";
					else
						return "<a href='/search?s=11$string#listing'>" . display_fa_icon("sort", "Sort Desc") . "</a>";

					break;

				default:
					break;
			}
		default:
			return;
			break;
	}
}

function display_active($get, $page_array) {
	if (in_array($get, $page_array)) return "active";
}

function display_alert($type, $strong, $text) {
	return "<div class='alert alert-$type text-center' role='alert'><strong>$strong:</strong> $text</div>";
}

function display_lang_flag_v3($language, $div = 0) {
	if (is_array($language))
		$language = (object) $language;

	$d_start = $div ? '<div>' : '';
	$d_end = $div ? '</div>' : '';

	return "$d_start<span class='rounded flag flag-{$language->lang_flag}' title='$language->lang_name'></span>$d_end";
}

function display_send_message($user, $uploader) {
	if (validate_level($user, 'member')) {
		if ($user->user_id != $uploader->user_id)
			return "<a class='btn btn-secondary' role='button' href='/messages/send/$uploader->username'>" . display_fa_icon('envelope', 'Send message') . " <span class='d-none d-xl-inline'>Send message</span></a>";
	}
	else
		return "<a class='btn btn-secondary' role='button' href='/login' title='Log in'>" . display_fa_icon('envelope', 'Send message') . " <span class='d-none d-xl-inline'>Send message</span></a>";
}

function display_ban_user($user, $target_user) {
	if (!validate_level($target_user, 'admin')) {
		if (validate_level($target_user, 'validating'))
			return "<button class='btn btn-danger' id='ban_user_button'>" . display_fa_icon('ban', 'Ban') . " <span class='d-none d-md-inline'>Ban</span></button>";
		else
			return "<button class='btn btn-danger' id='unban_user_button'>" . display_fa_icon('unlock', 'Unban') . " <span class='d-none d-md-inline'>Unban</span></button>";
	}
}

function display_edit_manga($user, $manga) {
	if (validate_level($user, 'contributor') && !$user->has_active_restriction(USER_RESTRICTION_EDIT_TITLES))
		return "<button " . (($manga->manga_locked && !validate_level($user, 'gmod')) ? "disabled='disabled' title='Editing has been locked to mods only.'" : '') . "class='btn btn-info float-right' id='edit_button'>" . display_fa_icon('pencil-alt', 'Edit') . " <span class='d-none d-xl-inline'>Edit</span></button>";
}

function display_lock_manga($user, $manga) {
	if (validate_level($user, 'gmod')) {
		if ($manga->manga_locked)
			return "<button class='btn btn-warning' id='manga_unlock_button'>" . display_fa_icon('lock-open', 'Unlock') . " <span class='d-none d-xl-inline'>Unlock</span></button>";
		else
			return "<button class='btn btn-warning' id='manga_lock_button'>" . display_fa_icon('lock', 'Lock') . " <span class='d-none d-xl-inline'>Lock</span></button>";
	}
}

function display_regenerate_manga_thumb($user) {
	if (validate_level($user, 'mod')) {
		return "<button class='btn btn-info' id='manga_regenerate_thumb_button'>" . display_fa_icon('sync', 'Regenerate thumb') . " <span class='d-none d-xl-inline'>Regenerate thumb</span></button>";
	}
}

function display_delete_manga($user) {
	if (validate_level($user, 'admin'))
		return "<button class='btn btn-danger float-right' id='delete_button'>" . display_fa_icon('trash', 'Delete') . " <span class='d-none d-xl-inline'>Delete</span></button>";
}

function display_edit_group($user, $group, $group_member_array) {
	if (validate_level($user, 'gmod') || $group->group_leader_id == $user->user_id || in_array($user->username, $group_member_array))
		return "<button class='btn btn-info' id='edit_button'>" . display_fa_icon('pencil-alt', 'Edit') . " <span class='d-none d-xl-inline'>Edit</span></button>";
}

function display_delete_group($user) {
	if (validate_level($user, 'admin'))
		return "<button class='btn btn-danger float-right' id='delete_button'>" . display_fa_icon('trash', 'Delete') . " <span class='d-none d-xl-inline'>Delete</span></button>";
}

function display_edit_group_members($user, $group) {
	if (validate_level($user, 'gmod') || $group->group_leader_id == $user->user_id)
		return "<button class='btn btn-info' id='edit_members_button'>" . display_fa_icon('pencil-alt', 'Edit members') . " <span class='d-none d-xl-inline'>Edit members</span></button>";
}

function display_accept_group_invite(){
    return "<button class='btn btn-success' id='accept_group_invite_button'>" . display_fa_icon('check', 'Accept') . " <span class='d-none d-xl-inline'>Accept</span></button>";
}

function display_reject_group_invite(){
    return "<button class='btn btn-danger' id='reject_group_invite_button'>" . display_fa_icon('times', 'Reject') . " <span class='d-none d-xl-inline'>Reject</span></button>";
}

function display_delete_chapter($user, $chapter) {
	if (!$chapter->chapter_deleted)
		return "<button title='Delete' class='btn btn-danger' id='chapter_delete_button'>" . display_fa_icon('trash') . "</button>";
	elseif (validate_level($user, 'gmod'))
		return "<button title='Restore' class='btn btn-success' id='chapter_undelete_button'>" . display_fa_icon('sync') . "</button>";
}

function display_edit_thread($user) {
	if (validate_level($user, 'pr'))
		return "<button class='btn btn-info edit_thread_button'>" . display_fa_icon('pencil-alt', 'Edit thread') . " <span class='d-none d-md-inline'>Edit</span></button>";
}


function display_lock_thread($user, $thread) {
	if (validate_level($user, 'pr')) {
		if ($thread->thread_locked)
			return "<button class='btn btn-warning' id='unlock_thread_button'>" . display_fa_icon('unlock', 'Unlock thread') . " <span class='d-none d-md-inline'>Unlock</span></button>";
		else
			return "<button class='btn btn-warning' id='lock_thread_button'>" . display_fa_icon('lock', 'Lock thread') . " <span class='d-none d-md-inline'>Lock</span></button>";
	}
}

function display_sticky_thread($user, $thread) {
	if (validate_level($user, 'pr')) {
		if ($thread->thread_sticky)
			return "<button class='btn btn-success' id='unsticky_thread_button'>" . display_fa_icon('thumbtack', 'Unsticky thread') . " <span class='d-none d-md-inline'>Unsticky</span></button>";
		else
			return "<button class='btn btn-success' id='sticky_thread_button'>" . display_fa_icon('thumbtack', 'Sticky thread') . " <span class='d-none d-md-inline'>Sticky</span></button>";
	}
}

function display_delete_threads($user) {
	if (validate_level($user, 'pr'))
		return "<button type='submit' class='btn btn-danger' id='delete_threads_button'>" . display_fa_icon('trash', 'Delete thread') . " <span class='d-none d-md-inline'>Delete</span></button>";
}

function display_new_thread($user, $threads) {
	if (validate_level($user, $threads->start_thread_level))
		return "<button class='btn btn-secondary new_thread_button'>" . display_fa_icon('edit', 'New thread') . " <span class='d-none d-md-inline'>New thread</span></button>";
}

function display_post_reply($user, $thread) {
	if ((validate_level($user, 'member') && !$thread->thread_locked) || validate_level($user, 'pr'))
		return "<button class='btn btn-secondary post_reply_button'>" . display_fa_icon('edit', 'Post reply') . " Post reply</button>";
	else
		return "<button class='btn btn-secondary disabled' title='Please log in to post a reply.'>" . display_fa_icon('lock', 'Post reply') . " Post reply</button>";
}

function display_thread_labels($thread) {
	$return = "";

	if ($thread->thread_sticky)
		$return .= display_fa_icon('thumbtack', 'Sticky', 'text-info');
	if ($thread->thread_locked)
		$return .= display_fa_icon('lock', 'Locked', 'text-warning');

	return $return;
}

function display_post_comment_v3($user, $obj, $type, $type_id) {
	if (validate_level($user, 'member')) {
		if (!$obj->thread_id)
			return "<form class='text-center' id='start_empty_thread_form' method='post'>
				<input name='type' type='hidden' value='$type' />
				<input name='type_id' type='hidden' value='$type_id' />
				<button style='display: block; margin: 0 auto;' type='submit' class='btn btn-secondary' id='start_empty_thread_button'>" . display_fa_icon('comment', 'Comment', '', 'far') . " Start comment thread</button>
				</form>";
	}
	else
		return "You need to log in to comment.";
}

function display_labels($hentai) {
	if ($hentai)
		return "<span class='badge badge-danger ml-1'>H</span>";
}

function display_labels_rss($batch, $hentai, $reencode, $none) {
	$label = "";

	if (!$batch && !$hentai && !$reencode && $none) $label .= " None";
	else {
		if ($batch) $label .= " Batch";
		if ($hentai) $label .= " Hentai";
		if ($reencode) $label .= " Remake";
	}
	return $label;
}

function display_fa_icon($name, $title = '', $class = '', $set = 'fas') {
	return "<span class='$set fa-$name fa-fw $class' aria-hidden='true' " . ($title ? "title='$title'" : '') . "></span>";
}

function display_group_members_list($group_members_array) {
	$text = "<ul class='list-inline' style='margin-bottom: 0px;'>";
	foreach ($group_members_array as $user) {
		$text .= "<li class='list-inline-item'>" . display_fa_icon('user') . " " . display_user_link($user['user_id'], $user['username'], $user['level_colour']) . "</li>";
	}
	$text .= "</ul>";

	return $text;
}

function display_delete_group_members_list($group_members_array) {
	$text = "";
	foreach ($group_members_array as $user_id => $username) {
		$text .= display_fa_icon('user') . " <a href='/user/$user_id/" . strtolower($username) . "'>$username</a> <a href='#' class='group_delete_member' id='$user_id'>" . display_fa_icon("trash") . "</a>";
	}
	return $text;
}

function display_user_groups_list($user_groups_array) {
	if ($user_groups_array) {
		$text = "<ul class='list-inline' style='margin-bottom: 0px;'>";
		foreach ($user_groups_array as $group_id => $group_name) {
			$text .= "<li class='list-inline-item'>" . display_fa_icon('users') . " <a href='/group/$group_id'>$group_name</a></li>";
		}
		$text .= "</ul>";
	}
	else
		$text = "None";

	return $text;
}

function display_like_button($user_id, $ip, $array_of_user_id_ip) {
	if (($user_id && !in_array($user_id, $array_of_user_id_ip["user_id"])) || (!$user_id && !in_array($ip, $array_of_user_id_ip["ip"]))) //(user_id > 0 and user_id not in array) or (user_id = 0 and ip not in array)
		$text = "<button class='btn btn-success' id='group_like_button'>" . display_fa_icon('thumbs-up', 'Like', '', 'far') . " <span class='d-none d-xl-inline'>Like</span></button>";
	else $text = "<button class='btn btn-danger' id='group_unlike_button'>" . display_fa_icon('thumbs-down', 'Unlike', '', 'far') . " <span class='d-none d-xl-inline'>Unlike</span></button>";

	return $text;
}

function display_block_group_button($user, $array_of_user_ids) {
	if (validate_level($user, 'member')) {
		if (in_array($user->user_id, $array_of_user_ids))
			$text = "<button class='btn btn-danger' id='group_unblock_button'>" . display_fa_icon('check-circle', 'Unblock') . " <span class='d-none d-xl-inline'>Unblock</span></button>";
		else
			$text = "<button class='btn btn-warning' id='group_block_button'>" . display_fa_icon('minus-circle', 'Block') . " <span class='d-none d-xl-inline'>Block</span></button>";
	}
	else
		$text = "<button class='btn btn-warning' disabled title='You need to log in to use this function.'>" . display_fa_icon('lock', 'Block') . " <span class='d-none d-xl-inline'>Block</span></button>";

	return $text;
}

function display_follow_button($user, $array_of_manga_ids, $manga_id, $style = 0, $dropup = 0) {
    $follow_types = new Follow_Types();

    if (validate_level($user, 'member')) {
		$return = "<div class='btn-group " . ($style ? 'btn-group-xs ' : '') . ($dropup ? 'dropup ' : '') . "'>";

		if (isset($array_of_manga_ids[$manga_id]))
			$return .= "<button type='button' class='btn btn-" . $follow_types->{$array_of_manga_ids[$manga_id]['follow_type']}->type_class . " dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>" . display_fa_icon($follow_types->{$array_of_manga_ids[$manga_id]['follow_type']}->type_glyph) . " <span class='" . ($dropup ? '' : 'd-none d-xl-inline') . "'>" . $follow_types->{$array_of_manga_ids[$manga_id]['follow_type']}->type_name . "</span></button>";
		else
			$return .= "<button id='1' data-manga-id='$manga_id' type='button' class='btn btn-secondary manga_follow_button'>" . display_fa_icon('bookmark') . " <span class='" . ($dropup ? '' : 'd-none d-xl-inline') . "'>Follow</span></button>
				<button type='button' class='btn btn-secondary dropdown-toggle dropdown-toggle-split' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
					<span class='sr-only'>Toggle Dropdown</span>
				</button>";

		$return .= "<div class='dropdown-menu dropdown-menu-right'>" .
		(isset($array_of_manga_ids[$manga_id]) ? "<a class='dropdown-item manga_unfollow_button' id='$manga_id' data-manga-id='$manga_id' href='#'>" . display_fa_icon('bookmark', 'Unfollow') . " Unfollow</a>" : '');
			foreach ($follow_types as $type) {
				$disabled = (isset($array_of_manga_ids[$manga_id]) && $array_of_manga_ids[$manga_id]['follow_type'] == $type->type_id) ? "disabled" : "";
				$return .= "<a class='$disabled dropdown-item manga_follow_button' data-manga-id='$manga_id' id='$type->type_id' href='#'>" . display_fa_icon($type->type_glyph, 'Follow') . " $type->type_name</a>";
			}
		$return .= "</div>
	</div>";
	}
	else
		$return = "<button class='btn btn-secondary " . ($style ? 'btn-xs' : '') . "' disabled title='You need to log in to use this function.'>" . display_fa_icon('bookmark', 'Follow') . " <span class='" . ($dropup ? '' : 'd-none d-xl-inline') . "'>Follow</span></button>";

	return $return;
}

function display_follow_group_button($user, $array_of_user_ids) {
	if (validate_level($user, 'member')) {
		if (in_array($user->user_id, $array_of_user_ids))
			$text = "<button class='btn btn-danger' id='group_unfollow_button'>" . display_fa_icon('bookmark', 'Unfollow') . " <span class='d-none d-xl-inline'>Unfollow</span></button>";
		else
			$text = "<button class='btn btn-success' id='group_follow_button'>" . display_fa_icon('bookmark', 'Follow') . " <span class='d-none d-xl-inline'>Follow</span></button>";
	}
	else
		$text = "<button class='btn btn-success' disabled title='You need to log in to use this function.'>" . display_fa_icon('bookmark', 'Follow') . " <span class='d-none d-xl-inline'>Follow</span></button>";

	return $text;
}

function display_upload_button($user) {
	if (validate_level($user, 'member'))
		$text = "<button class='btn btn-secondary' id='upload_button'>" . display_fa_icon('upload', 'Upload') . " <span class='d-none d-xl-inline'>Upload chapter</span></button>";
	else
		$text = "<button class='btn btn-secondary' id='upload_button' disabled title='You need to log in to use this function.'>" . display_fa_icon('upload', 'Upload') . " <span class='d-none d-xl-inline'>Upload chapter</span></button>";

	return $text;
}

function display_add_friend($user, $uploader) {
	$friends = $user->get_friends_user_ids();
	$pending = $user->get_pending_friends_user_ids();

	if (is_array($uploader))
		$uploader = (object) $uploader;

	$return = '';
	if ($user->user_id != $uploader->user_id) {
		if (validate_level($user, 'member')) {
			if (isset($pending[$uploader->user_id]))
				$return .= "<button type='button' class='btn btn-success' id='friend_accept_button'>" . display_fa_icon('user-plus') . " <span class='d-none d-md-inline'>Accept request</span></button>";
			elseif (!isset($friends[$uploader->user_id]))
				$return .= "<button type='button' class='btn btn-success' id='friend_add_button'>" . display_fa_icon('user-plus') . " <span class='d-none d-md-inline'>Add friend</span></button>";
			elseif (!$friends[$uploader->user_id]['accepted'])
				$return .= "<button type='button' class='btn btn-success' disabled title='Waiting for user to accept your friend request'>" . display_fa_icon('user-clock') . " <span class='d-none d-md-inline'>Pending...</span></button>";
			else
				$return .= "<button type='button' class='btn btn-warning' id='friend_remove_button'>" . display_fa_icon('user-minus') . " <span class='d-none d-md-inline'>Remove friend</span></button>";
		}
		else
			$return .= "<a href='/login' role='button' class='btn btn-success' title='Log in'>" . display_fa_icon('user-plus') . " <span class='d-none d-md-inline'>Add friend</span></a>";
	}

	return $return;
}

function display_block_user($user, $uploader) {
	$blocked = $user->get_blocked_user_ids();

	if (is_array($uploader))
		$uploader = (object) $uploader;

	$return = '';
	// Hide the block button when the profile belongs to a staff member
	if ($user->user_id != $uploader->user_id && !validate_level($uploader, 'pr')) {
		if (validate_level($user, 'member')) {
			if (!isset($blocked[$uploader->user_id]))
				$return .= "<button type='button' class='btn btn-danger' id='user_block_button'>" . display_fa_icon('angry', '', '', 'far') . " <span class='d-none d-md-inline'>Block user</span></button>";
			else
				$return .= "<button type='button' class='btn btn-warning' id='user_unblock_button'>" . display_fa_icon('smile', '', '', 'far') . " <span class='d-none d-md-inline'>Unblock user</span></button>";
		}
		else
			$return .= "<a href='/login' role='button' class='btn btn-danger' title='Log in'>" . display_fa_icon('angry', '', '', 'far') . " <span class='d-none d-md-inline'>Block user</span></a>";
	}

	return $return;
}
?>