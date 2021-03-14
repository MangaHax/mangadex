<?php

/** Template vars:
 * chapters: array of chapter
 * chapter_count: number of chapters to display
 * current_page: page starting at 1
 * limit: number of entries per page
 * page: the current page name, http://mangadex.org/manga/... => 'manga'
 * user: the currently logged in user
 * list_type: (optional) if mode is follows, the user setting that controls the list format
 * manga: (optional) if mode is manga, the manga object
 * group: (optional) if mode is group, the group object
 * uploader: (optional) if mode is user, the user object of the uploader
 */

$paging = pagination($templateVar['chapter_count'], $templateVar['current_page'], $templateVar['limit']);
$read_chapters_array = $templateVar['user']->get_read_chapters();
$followed_list = $templateVar['user']->get_followed_manga_ids_key_pair();
$user_group_ids = array_keys($templateVar['user']->get_groups());
$timestamp = $templateVar['timestamp'] ?? time();
?>
<div class="chapter-container ">

    <div class="row no-gutters">
        <?php if ($templateVar['page'] != 'manga') { ?>
            <div class="col col-md-3 d-none d-md-flex no-gutters flex-nowrap align-items-center p-2 border-bottom">
                <?= display_fa_icon('book', 'Title') ?>
            </div>
            <div class="w-100 d-md-none"></div>
        <?php } ?>
        <div class="col <?= ($templateVar['page'] != 'manga') ? 'col-md-9' : '' ?>">
            <div class="chapter-row d-flex row no-gutters p-2 align-items-center border-bottom odd-row">
                <div class="col-auto text-center order-lg-1" style="flex: 0 0 2.5em;"><?= display_fa_icon('eye', 'Read') ?></div>
                <div class="col col-lg-5 row no-gutters pr-1 order-lg-2"><?= display_fa_icon('file', 'Chapter', '', 'far') ?></div>
                <div class="col text-right order-lg-3" style="flex: 0 0 3em;"><?= display_fa_icon('comments', 'Comments') ?></div>
                <div class="col-2 col-lg-1 ml-1 text-right order-lg-8"><?= display_fa_icon('clock', 'Age', '', 'far') ?></div>
                <div class="w-100 d-lg-none"></div>
                <div class="col-auto text-center order-lg-4" style="flex: 0 0 2.5em;"><?= display_fa_icon('globe', 'Language') ?></div>
                <div class="col order-lg-5"><?= display_fa_icon('users', 'Group') ?></div>
                <div class="col-auto col-lg-1 text-right mx-1 order-lg-6"><?= display_fa_icon('user', 'Uploader') ?></div>
                <div class="col-2 col-lg-1 text-right text-info order-lg-7"><?= display_fa_icon('eye', 'Views') ?></div>
            </div>
        </div>
    </div>

<?php
$last_manga_id = "";

foreach ($templateVar['chapters'] as $chapter) :
    $chapter = (object)$chapter;
    $has_end_tag = ($chapter->manga_last_volume === null || ($chapter->manga_last_volume == $chapter->volume)) && $chapter->manga_last_chapter && $chapter->manga_last_chapter == $chapter->chapter;
?>

    <div class="row no-gutters">

        <?php if ($templateVar['page'] != 'manga') { ?>
        <div class="col col-md-3 <?= ($last_manga_id == $chapter->manga_id ? 'd-none d-md-flex' : '') ?> row no-gutters flex-nowrap align-items-center p-2 font-weight-bold border-bottom">
            <?php if ($last_manga_id != $chapter->manga_id) { ?>
            <?= display_manga_link_v2($chapter) ?>
            <?php } ?>
        </div>
        <div class="w-100 d-md-none"></div>
        <?php } ?>
        <div class="col <?= ($templateVar['page'] != 'manga') ? 'col-md-9' : '' ?>">
            <div class="chapter-row d-flex row no-gutters p-2 align-items-center border-bottom odd-row"
                   data-id="<?= $chapter->chapter_id ?>"
                   data-title="<?= $chapter->title ?>"
                   data-chapter="<?= $chapter->chapter ?>"
                   data-volume="<?= $chapter->volume ?>"
                   data-comments="2"
                   data-read="false"
                   data-lang="<?= $chapter->lang_id ?>"
                   data-group="<?= $chapter->group_id ?>"
                   data-uploader="<?= $chapter->username ?>"
                   data-views="<?= $chapter->chapter_views ?>"
                   data-timestamp="<?= $chapter->upload_timestamp ?>"
                   data-manga-id="<?= $chapter->manga_id ?>">
				<div class="col-auto text-center order-lg-1" style="flex: 0 0 2.5em;">
					<?php 
					if (validate_level($templateVar['user'], 'member')) {
						if (in_array($chapter->chapter_id, $read_chapters_array) || (isset($followed_list[$chapter->manga_id]) && $followed_list[$chapter->manga_id] == 2))
							print "<span class='chapter_mark_unread_button' data-id='$chapter->chapter_id' id='marker_$chapter->chapter_id' title='Mark unread'>" . display_fa_icon('eye') . "</span>"; 
						elseif (isset($followed_list[$chapter->manga_id]) && $followed_list[$chapter->manga_id] == 1)
							print "<span class='chapter_mark_read_button grey' data-id='$chapter->chapter_id' id='marker_$chapter->chapter_id' title='Mark read'>" . display_fa_icon('eye-slash') . "</span>"; 
						else
							print "<span class='grey' title='You need to follow this title to mark it read.'>" . display_fa_icon('eye-slash') . "</span>"; 
					}
					else {
						print "<span class='grey' title='You need to follow this title to mark it read.'>" . display_fa_icon('eye-slash') . "</span>"; 
					}
					?>
                </div>
                <div class="col col-lg-5 row no-gutters align-items-center flex-nowrap text-truncate pr-1 order-lg-2">
                    <?= display_chapter_title($chapter) ?>
                    <div><?= $has_end_tag ? ' <span class="badge badge-primary mx-1">END</span>' : '' ?><?= $chapter->available ? '' : display_fa_icon('file-excel', 'Unavailable', 'mx-1', 'fas') ?></div>
                    <?= (validate_level($templateVar['user'], 'gmod') || $templateVar['user']->user_id == $chapter->user_id || in_array($chapter->group_id, $user_group_ids) || in_array($chapter->group_id_2, $user_group_ids) || in_array($chapter->group_id_3, $user_group_ids)) ? "<a role='button' href='/chapter/$chapter->chapter_id/edit' class='btn btn-xs btn-info order-lg-2 ml-auto'>" . display_fa_icon('pencil-alt', 'Edit') . "</a>" : '' ?>
                </div>
                <div class="col text-right order-lg-3" style="flex: 0 0 3em;">
                    <?= display_count_comments($chapter->thread_posts, 'chapter', $chapter->chapter_id) ?>
                </div>
                <div class="col-2 col-lg-1 ml-1 text-right text-truncate order-lg-8 <?= ($timestamp < $chapter->upload_timestamp) ? "text-warning" : ($timestamp - $chapter->upload_timestamp < 86400 ? "text-success" : "") ?>" title="<?= gmdate(DATETIME_FORMAT, $chapter->upload_timestamp) ?>">
                    <?= get_time_ago($chapter->upload_timestamp) ?>
                </div>
                <div class="w-100 d-lg-none"></div>
                <div class="chapter-list-flag col-auto text-center order-lg-4" style="flex: 0 0 2.5em;">
                    <?= display_lang_flag_v3($chapter) ?>
                </div>
                <div class="chapter-list-group col text-truncate order-lg-5">
                    <?= $chapter->available || validate_level($templateVar['user'], 'pr') ? display_group_link_v2($chapter) : '' ?>
                </div>
                <div class="chapter-list-uploader col-auto col-lg-1 text-truncate text-right mx-1 order-lg-6">
                    <?= display_user_link_v2($chapter) ?>
                </div>
                <div class="chapter-list-views col-2 col-lg-1 text-right text-info text-truncate order-lg-7">
                    <span class="d-none d-md-inline d-lg-none d-xl-inline"><?= number_format($chapter->chapter_views) ?></span>
                    <span class="d-inline d-md-none d-lg-inline d-xl-none" title="<?= number_format($chapter->chapter_views) ?>"><?= ($chapter->chapter_views >= 1000) ? floor($chapter->chapter_views / 1000) . 'k' : $chapter->chapter_views ?></span>
                </div>
            </div>
        </div>
    </div>
<?php
$last_manga_id = $chapter->manga_id;
endforeach;
?>
			
		</div>
    <?php

switch ($templateVar['page']) {
    case "manga":
        $string = "title/{$templateVar['manga']->manga_id}/" . slugify($templateVar['manga']->manga_name) . '/' . $templateVar['mode'];
    break;

    case "group":
        $string = $templateVar['page'] . "/{$templateVar['group']->group_id}/" . slugify($templateVar['group']->group_name) . '/' . $templateVar['mode'];
    break;

    case "user":
        $string = $templateVar['page'] . "/{$templateVar['uploader']->user_id}/" . strtolower($templateVar['uploader']->username) . '/' . $templateVar['mode'];
    break;

    case "follows":
        $string = $templateVar['page'] . '/chapters/' . $templateVar['list_type'];
    break;

    default:
        $string = "";
    break;
}

print display_pagination_v2($paging, "chapters", "$string");

