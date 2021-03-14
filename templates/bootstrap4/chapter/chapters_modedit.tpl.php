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
 * show_only_deleted: (optional) shows only deleted chapters (bin)
 */

$paging = pagination($templateVar['chapter_count'], $templateVar['current_page'], $templateVar['limit']);
$show_only_deleted = $templateVar['show_only_deleted'] ?? 0;
$timestamp = $templateVar['timestamp'] ?? time();

?>
<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
        <tr class=" border-top-0">
            <?php if ($templateVar['page'] != "manga") { ?><th><?= display_fa_icon("book", "Title") ?></th><?php } ?>
            <th><?= display_fa_icon("file", "Chapter", '', 'far') ?></th>
            <th width="50px" class="text-center"><?= display_fa_icon("comments", "Comments") ?></th>
            <th class="text-center" width="30px"><?= display_fa_icon("globe", "Language") ?></th>
            <th><?= display_fa_icon("users", "Group") ?></th>
            <th><?= display_fa_icon("user", "User") ?></th>
            <th class="text-center text-info"><?= display_fa_icon("eye", "Views") ?></th>
            <th class="text-right" style="min-width: 80px;"><?= display_fa_icon("clock", "Age", '', 'far') ?></th>
            <?php if (validate_level($templateVar['user'], 'gmod') && !$show_only_deleted) { ?>
                <th class="text-center" width="30px"><?= display_fa_icon("pencil-alt", "Edit") ?></th>
            <?php } ?>
            <?php if (validate_level($templateVar['user'], 'gmod') && $show_only_deleted) { ?>
                <th class="text-center" width="110px"><?= display_fa_icon("sync", "Restore") ?></th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>

<?php
        $last_manga_id = "";

        foreach ($templateVar['chapters'] as $chapter) :
            $chapter = (object)$chapter;
?>

            <tr id="chapter_<?= $chapter->chapter_id ?>">
                <?php if ($templateVar['page'] != "manga") { ?>
                    <td><?= ($last_manga_id != $chapter->manga_id) ? display_manga_link($chapter->manga_id, $chapter->manga_name, $chapter->manga_hentai) : "" ?></td>
                <?php } ?>
                <td><a title="<?= $chapter->title ?>" href="/chapter/<?= $chapter->chapter_id ?>"><?= ($chapter->volume == "" || $chapter->volume == 0) ? "" : "Vol. $chapter->volume " ?><?= ($chapter->chapter != "") ? "Ch. $chapter->chapter - " : "" ?><?= mb_strimwidth($chapter->title, 0, 60, "...") ?></a><?= ($chapter->manga_last_chapter && $chapter->manga_last_chapter == $chapter->chapter) ? " <span class='badge badge-primary'>END</span>" : '' ?><?= $chapter->available ? '' : display_fa_icon('file-excel', 'Unavailable', 'mx-1', 'fas') ?></td>
                <td class="text-center"><?= display_count_comments($chapter->thread_posts, 'chapter', $chapter->chapter_id) ?></td>
                <td class="text-center"><?= display_lang_flag_v3($chapter) ?></td>
                <td><?= display_group_link_v2($chapter) ?></td>
                <td><?= display_user_link_v2($chapter) ?></td>
                <td class="text-center text-info"><?= number_format($chapter->chapter_views) ?></td>
                <td class="text-right <?= ($timestamp < $chapter->upload_timestamp) ? "text-warning" : ($timestamp - $chapter->upload_timestamp < 86400 ? "text-success" : "") ?>" title="<?= gmdate(DATETIME_FORMAT, $chapter->upload_timestamp) ?>"><time datetime="<?= gmdate(DATETIME_FORMAT, $chapter->upload_timestamp) ?>"><?= get_time_ago($chapter->upload_timestamp) ?></time></td>
                <?php if (validate_level($templateVar['user'], 'gmod') && !$show_only_deleted) { ?>
                    <td><button class="btn btn-xs btn-info toggle_mass_edit_button" type="button" id="<?= $chapter->chapter_id ?>"><?= display_fa_icon("pencil-alt", "Edit") ?></button></td>
                <?php } ?>
                <?php if (validate_level($templateVar['user'], 'gmod') && $show_only_deleted) { ?>
                    <td>
                        <a href="/chapter/<?= $chapter->chapter_id ?>/edit" class="btn btn-xs btn-info"><?= display_fa_icon("pencil-alt", "Edit") ?></a>
                        <button class="btn btn-xs btn-success undelete_button" type="button" data-id="<?= $chapter->chapter_id ?>"><?= display_fa_icon("sync", "Restore") ?></button>
                        <button class="btn btn-xs btn-warning unavailable_button" type="button" data-id="<?= $chapter->chapter_id ?>"><?= display_fa_icon("file-excel", "Unavailable") ?></button>
						<button class="btn btn-xs btn-danger purge_button" type="button" data-id="<?= $chapter->chapter_id ?>"><?= display_fa_icon("eraser", "Purge") ?></button>
                    </td>
                <?php } ?>
            </tr>

            <?php
            if (validate_level($templateVar['user'], 'gmod') && !$show_only_deleted) : ?>
                <tr class="display-none" id="toggle_mass_edit_<?= $chapter->chapter_id ?>">
                    <td colspan="<?= ($templateVar['page'] != "manga") ? "8" : "7" ?>">
                        <form class="form-inline mass_edit_form" method="post" id="<?= $chapter->chapter_id ?>">
                            <?= display_fa_icon("book", "Title") ?> <input style="width: 5%" type="text" class="form-control input-sm" name="manga_id" value="<?= $chapter->manga_id ?>" required>
                            Vol <input style="width: 5%" type="text" class="form-control input-sm" name="volume_number" value="<?= $chapter->volume ?>">
                            Ch <input style="width: 5%" type="text" class="form-control input-sm" name="chapter_number" value="<?= $chapter->chapter ?>">
                            Title <input style="width: 20%" type="text" class="form-control input-sm" name="chapter_name" value="<?= $chapter->title ?>">
                            <?= display_fa_icon("globe", "Language") ?> <input style="width: 5%" type="text" class="form-control input-sm" name="lang_id" value="<?= $chapter->lang_id ?>">
                            <?= display_fa_icon("users", "Group") ?> <input style="width: 5%" type="text" class="form-control input-sm" name="group_id" value="<?= $chapter->group_id ?>">
                            <input style="width: 5%" type="text" class="form-control input-sm" name="group_id_2" value="<?= $chapter->group_id_2 ?>">
                            <input style="width: 5%" type="text" class="form-control input-sm" name="group_id_3" value="<?= $chapter->group_id_3 ?>">
                            <?= display_fa_icon("user", "User") ?> <input style="width: 5%" type="text" class="form-control input-sm" name="user_id" value="<?= $chapter->user_id ?>">
							<?= display_fa_icon("file-excel", "Unavailable") ?> <input type="checkbox" class="" id="unavailable" name="unavailable" value="1" <?= $chapter->available ? '' : 'checked' ?>>
                            <button class="btn btn-sm btn-success" type="submit" id="mass_edit_button_<?= $chapter->chapter_id ?>"><?= display_fa_icon("pencil-alt", "Update") ?></button>
                            <button class="btn btn-sm btn-danger mass_edit_delete_button" type="button" id="<?= $chapter->chapter_id ?>"><?= display_fa_icon("trash", "Delete") ?></button>
                            <button class="btn btn-sm btn-warning cancel_mass_edit_button pull-right" type="button" id="<?= $chapter->chapter_id ?>"><?= display_fa_icon("times", "Cancel", "fa-fw") ?></button>
                        </form>
                    </td>
                </tr>
                <?php
            endif;
            $last_manga_id = $chapter->manga_id;
        endforeach;
        ?>

        </tbody>
    </table>
</div>

<?php

switch ($templateVar['page']) {
    case "manga":
        $string = 'title/' . $templateVar['manga']->manga_id . '/' . slugify($templateVar['manga']->manga_name) . '/' . $templateVar['mode'];
        break;

    case "group":
        $string = $templateVar['page'] . '/' . $templateVar['group']->group_id . '/' . slugify($templateVar['group']->group_name) . '/' . $templateVar['mode'];
        break;

    case "user":
        $string = $templateVar['page'] . '/' . $templateVar['uploader']->user_id . '/' . strtolower($templateVar['uploader']->username) . '/' . $templateVar['mode'];
        break;

    case "follows":
        $string = $templateVar['page'] . '/chapters/' . $templateVar['list_type'];
        break;

    default:
        $string = "";
        break;
}

print display_pagination_v2($paging, "chapters", $string);
