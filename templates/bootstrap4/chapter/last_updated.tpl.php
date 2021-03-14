<?php

$last_manga_id_array = [];
$manga_array = [];

foreach ($templateVar['chapters'] as $chapter) {
    if (!in_array($chapter['manga_id'], $last_manga_id_array)) {
        $manga_array[] = [
            'manga_id' => $chapter['manga_id'],
            'manga_name' => $chapter['manga_name'],
            'manga_hentai' => $chapter['manga_hentai'],
        ];
        $last_manga_id_array[] = $chapter['manga_id'];
    }

    $chapter_array[$chapter['manga_id']][$chapter['chapter_id']] = $chapter;
}

$num_rows = count($manga_array);

$paging = pagination($num_rows, $templateVar['current_page'], $templateVar['limit']);
//$read_chapter_array = ($templateVar['user']->user_id) ? $templateVar['user']->get_read_chapters() : array("");
?>

<div class="card mb-3">
    <h6 class="card-header"><?= display_fa_icon('sync') ?> Latest updates <?= $templateVar['user']->user_id ? display_rss_link($templateVar['user']) : '' ?></h6>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
            <tr class="border-top-0">
                <th width="110px"></th>
                <th width="25px"></th>
                <th style="min-width: 150px;"></th>
                <th class="text-center" width="30px"><?= display_fa_icon('globe', 'Language') ?></th>
                <th style="min-width: 150px;"><?= display_fa_icon('users', 'Group') ?></th>
                <th class="d-none d-lg-table-cell"><?= display_fa_icon('user', 'Uploader') ?></th>
                <th class="d-none d-lg-table-cell text-center text-info"><?= display_fa_icon('eye', 'Views') ?></th>
                <th style="min-width: 65px;" class="text-right"><?= display_fa_icon('clock', 'Uploaded', '', 'far') ?></th>
            </tr>
            </thead>
            <tbody>

            <?php




            for ($j = $paging['offset']; $j < min($num_rows, ($paging['offset'] + $templateVar['limit'])); $j++) {
                $manga_id = $manga_array[$j]['manga_id'];
                $manga = $chapter_array[$manga_id];

                //$bookmark = ($templateVar['user']->user_id && in_array($manga_id, $followed_manga_array)) ? display_fa_icon("bookmark", "Following", "text-success") : "";
                $rowspan = (count($manga) >= 4 ? 5 : count($manga) + 1);
                ?>

                <tr>
                    <td rowspan="<?= $rowspan ?>"><div class="medium_logo rounded"><a href="/title/<?= $manga_id ?>/<?= slugify($manga_array[$j]['manga_name']) ?>"><img class="rounded" src="/images/manga/<?= $manga_id ?>.thumb.jpg" alt="Thumb" /></a></div></td>
                    <td class="text-right"></td>
                    <td colspan="6" height="31px" class="position-relative"><span class="ellipsis"><?= display_fa_icon('book', 'Title') ?> <?= display_manga_link_v2($manga_array[$j]) ?></span></td>
                </tr>
                <?php
                $i = 1;
                foreach ($manga as $chapter_id => $chapter) {

                    if ($i < 5) {
                        $i++;
                        //$key = ($templateVar['user']->user_id) ? array_search($chapter_id, $read_chapter_array["chapter_id"]) : "";
                        //$read = ($templateVar['user']->user_id && in_array($chapter_id, $read_chapter_array["chapter_id"])) ? display_fa_icon("eye", "fas", "Read " . get_time_ago($read_chapter_array["timestamp"][$key]), "fa-fw") : "";
                        $has_end_tag = ($chapter['manga_last_volume'] === null || ($chapter['manga_last_volume'] == $chapter['volume'])) && $chapter['manga_last_chapter'] && $chapter['manga_last_chapter'] == $chapter['chapter'];

                        ?>
                        <tr>
                            <td class="text-right"></td>
                            <td><?= display_chapter_title($chapter, 1) ?><?= $has_end_tag ? ' <span class="badge badge-primary">END</span>' : '' ?></td>
                            <td class="text-center"><?= display_lang_flag_v3($chapter) ?></td>
                            <td class="position-relative"><span class="ellipsis"><?= display_group_link_v2($chapter) ?></span></td>
                            <td class="d-none d-lg-table-cell"><?= display_user_link_v2($chapter) ?></td>
                            <td class="d-none d-lg-table-cell text-center text-info"><?= number_format($chapter['chapter_views']) ?></td>
                            <td class="text-right" title="<?= gmdate(DATETIME_FORMAT, $chapter['upload_timestamp']) ?>"><time datetime="<?= gmdate(DATETIME_FORMAT, $chapter['upload_timestamp']) ?>"><?= get_time_ago($chapter['upload_timestamp']) ?></time></td>
                        </tr>
                        <?php
                    }
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<?= display_pagination_v2($paging, 'titles', $templateVar['page']) ?>