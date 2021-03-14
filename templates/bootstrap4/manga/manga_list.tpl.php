<?php

/** Template vars:
 * mangas: array of chapter
 * manga_count: number of chapters to display
 * current_page: page starting at 1
 * limit: number of entries per page
 * page: the current page name, http://mangadex.org/manga/... => 'manga'
 * user: the currently logged in user
 * title_mode
 * sort
 * search: the search array
 * uploader: (optional) if mode is user, the user object of the uploader
 */

$paging = pagination($templateVar['manga_count'], $templateVar['current_page'], $templateVar['limit'], $templateVar['sort']);

$follow_types = new Follow_Types();
$followed_manga_ids_array = $templateVar['user']->get_followed_manga_ids();

$user_manga_ratings_array = $templateVar['user']->get_manga_ratings();

$rank = ($templateVar['current_page'] - 1) * $templateVar['limit'];

if ($templateVar['show_tabs'] ?? false) {
    print parse_template('manga/partials/manga_navtabs', $templateVar);
}

if ($templateVar['page'] == 'titles' || $templateVar['page'] == 'search' || true) {
?>
<div class="row my-2">
    <div class="col-auto ml-auto">
        Sort by
        <select class="manga-sort-select form-control d-inline-block w-auto ml-1">
            <option value="2" <?= $templateVar['sort']==2 ? 'selected':'' ?>>Title &#x25B2;</option>
            <option value="3" <?= $templateVar['sort']==3 ? 'selected':'' ?>>Title &#x25BC;</option>
            <option value="0" <?= $templateVar['sort']==0 ? 'selected':'' ?>>Last updated &#x25B2;</option>
            <option value="1" <?= $templateVar['sort']==1 ? 'selected':'' ?>>Last updated &#x25BC;</option>
            <option value="4" <?= $templateVar['sort']==4 ? 'selected':'' ?>>Comments &#x25B2;</option>
            <option value="5" <?= $templateVar['sort']==5 ? 'selected':'' ?>>Comments &#x25BC;</option>
            <option value="6" <?= $templateVar['sort']==6 ? 'selected':'' ?>>Rating &#x25B2;</option>
            <option value="7" <?= $templateVar['sort']==7 ? 'selected':'' ?>>Rating &#x25BC;</option>
            <option value="8" <?= $templateVar['sort']==8 ? 'selected':'' ?>>Views &#x25B2;</option>
            <option value="9" <?= $templateVar['sort']==9 ? 'selected':'' ?>>Views &#x25BC;</option>
            <option value="10" <?= $templateVar['sort']==10 ? 'selected':'' ?>>Follows &#x25B2;</option>
            <option value="11" <?= $templateVar['sort']==11 ? 'selected':'' ?>>Follows &#x25BC;</option>
        </select>
    </div>
</div>
<?php } ?>

<!-- expanded -->
<?php if ($templateVar['title_mode'] == 1) { ?>
    <div class="border-bottom">
        <div style="width: 80px;" class="rounded my-2 mr-0 float-left"></div>
        <div class="row m-0">
            <div class="d-none d-md-block col-md-5 col-lg-7">
                <div class="row">
                    <div class="p-1 col text-truncate"><?= display_fa_icon('globe', 'Language') ?> <?= display_fa_icon('book', 'Title') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_name", "alpha", $templateVar['base_url']) ?></div>
                    <div class="p-1 d-none d-md-block col text-truncate"><?= display_fa_icon('pencil-alt', 'Author/Artist') ?></div>
                    <div class="p-1 col-auto col-md-2 text-center"></div>
                </div>
            </div>
            <div class="col-md-7 col-lg-5">
                <div class="row">
                    <div class="p-1 col"><?= display_fa_icon('comments', 'Comments') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_comments", "numeric", $templateVar['base_url']) ?></div>
                    <div class="p-1 col text-center text-primary"><?= display_fa_icon('user', 'User rating') ?></div>
                    <div class="p-1 col text-center text-primary"><?= display_fa_icon('star', 'Bayesian rating') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_bayesian", "numeric", $templateVar['base_url']) ?></div>
                    <div class="p-1 col text-center text-info"><?= display_fa_icon('eye', 'Views') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_views", "numeric", $templateVar['base_url']) ?></div>
                    <div class="p-1 col text-center text-success"><?= display_fa_icon('bookmark', 'Follows') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_follows", "numeric", $templateVar['base_url']) ?></div>
                    <div class="p-1 col text-right"><?= display_fa_icon('sync', 'Last update') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_last_updated", "numeric", $templateVar['base_url']) ?></div>
                    <?php if ($templateVar['page'] == 'mod') print "<div class='p-1 col text-right'>" . display_fa_icon('trash') . "</div>"; ?>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($templateVar['mangas'] as $manga) {
        $templateVar['parser']->parse(preg_replace('/\[spoiler\][\s\S]+?\[\/spoiler\]/', '', $manga->manga_description));
    ?>
        <div class="manga-entry border-bottom" data-id="<?= $manga->manga_id ?>"<?php if ($manga instanceof Manga) : ?> data-genre-ids="<?= implode(',', $manga->get_manga_genres()) ?>" data-demo-id="<?= $manga->manga_demo_id ?? '' ?>"<?php endif; ?>>
            <div class="w-100">
                <div class="rounded sm_md_logo col-auto p-2 float-left">
                    <a href="/title/<?= $manga->manga_id . "/" . slugify($manga->manga_name) ?>"><img
                                style="object-fit: scale-down;" class="rounded"
                                src="/images/manga/<?= $manga->manga_id ?>.thumb.jpg?<?= @filemtime(ABS_DATA_BASEPATH . "/manga/$manga->manga_id.thumb.jpg") ?>"
                                width="100%" alt="image"/></a>
                </div>
                <div>
                    <div class="row m-0 col-auto p-0">
                        <div class="col-md-5 col-lg-7 p-0">
                            <div class="row m-0">
                                <div class="p-1 col d-flex align-items-center text-truncate flex-nowrap">
                                    <?= display_lang_flag_v3($manga, 1) ?>
                                    <a title="<?= $manga->manga_name ?>"
                                       href="/title/<?= $manga->manga_id ?>/<?= slugify($manga->manga_name) ?>"
                                       class="ml-1 manga_title text-truncate"><?= $manga->manga_name ?></a>
                                    <?php if ($manga->manga_hentai) { ?>
                                        <div><span class="badge badge-danger ml-1">H</span></div><?php } ?>
                                </div>
                                <div class="p-1 d-none d-lg-block col text-truncate">
                                    <a title="<?= $manga->manga_author ?>"
                                       href="/?page=titles&author=<?= $manga->manga_author ?>"><?= $manga->manga_author ?></a>
                                </div>
                                <div class="p-1 col-auto col-md-2 text-center">
                                    <?php if (isset($templateVar['list_user_followed_manga_ids_array']) && $templateVar['list_user']->user_id != $templateVar['user']->user_id) { ?>
                                        <button title="<?= $follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_name ?>"
                                                class="disabled btn btn-xs btn-<?= $follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_class ?>"><?= display_fa_icon($follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_glyph) ?></button>
                                    <?php } ?>
                                    <?= display_follow_button($templateVar['user'], $followed_manga_ids_array, $manga->manga_id, 1) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 col-lg-5">
                            <div class="row">
                                <div class="p-1 col"><?= display_count_comments($manga->thread_posts, 'manga', $manga->manga_id, $manga->manga_name) ?></div>
                                <div class="p-1 col text-center text-primary">
                                    <?php
                                    if (isset($templateVar['list_user_manga_ratings_array'])) {
                                        if (isset($templateVar['list_user_manga_ratings_array'][$manga->manga_id]))
                                            print "<button style='width: 22px;' disabled class='btn btn-primary btn-xs' title=\"{$templateVar['list_user']->username}'s rating\">{$templateVar['list_user_manga_ratings_array'][$manga->manga_id]}</button>";
                                        else
                                            print "<button style='width: 22px;' disabled class='btn btn-primary btn-xs' title='Not yet rated by {$templateVar['list_user']->username}'>-</button>";
                                    } else {
                                        if (isset($user_manga_ratings_array[$manga->manga_id]))
                                            print display_manga_rating_button($templateVar['user']->user_id, $user_manga_ratings_array[$manga->manga_id], $manga->manga_id, 1);
                                        else
                                            print display_manga_rating_button($templateVar['user']->user_id, 0, $manga->manga_id, 1);
                                    }
                                    ?>
                                </div>
                                <div class="p-1 col text-center text-primary"><span
                                            title="<?= number_format($manga->manga_rated_users) ?> votes"><?= $manga->manga_bayesian ?></span>
                                </div>
                                <div class="p-1 col text-center text-info"><?= number_format($manga->manga_views) ?></div>
                                <div class="p-1 col text-center text-success"><?= number_format($manga->manga_follows) ?></div>
                                <div class="p-1 col text-right">
                                    <time datetime="<?= gmdate(DATETIME_FORMAT, $manga->manga_last_updated) ?>"><?= get_time_ago($manga->manga_last_updated) ?></time>
                                </div>
                                <?php if ($templateVar['page'] == 'mod') print "<div class='p-1 col text-right'><button title='Remove' class='btn btn-danger btn-sm remove_featured' id='$manga->manga_id'>" . display_fa_icon('trash') . "</button></div>"; ?>
                            </div>
                        </div>
                    </div>
                    <div class="pl-1" style="overflow: hidden; height: 125px;">
                        <?= $templateVar['parser']->getAsHtml() ?>
                    </div>
                </div>
            </div>
        </div>


    <?php } ?>

    <!-- simple -->
    <?php
} elseif ($templateVar['title_mode'] == 2) {
    ?>

    <div class="row m-0 border-bottom">
        <div class="d-none d-md-block col-md-6 col-lg-7">
            <div class="row">
                <div class="p-1 col text-truncate"><?= display_fa_icon('globe', 'Language') ?> <?= display_fa_icon('book', 'Title') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_name", "alpha", $templateVar['base_url']) ?></div>
                <div class="p-1 d-none d-lg-block col-3 text-truncate"><?= display_fa_icon('pencil-alt', 'Author/Artist') ?></div>
                <div class="p-1 col-auto col-md-2 text-center"></div>
            </div>
        </div>
        <div class="col-md-6 col-lg-5">
            <div class="row">
                <div class="p-1 col"><?= display_fa_icon('comments', 'Comments') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_comments", "numeric", $templateVar['base_url']) ?></div>
                <div class="p-1 col text-center text-primary"><?= display_fa_icon('user', 'User rating') ?></div>
                <div class="p-1 col text-center text-primary"><?= display_fa_icon('star', 'Bayesian rating') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_rating", "numeric", $templateVar['base_url']) ?></div>
                <div class="p-1 col text-center text-info"><?= display_fa_icon('eye', 'Views') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_views", "numeric", $templateVar['base_url']) ?></div>
                <div class="p-1 col text-center text-success"><?= display_fa_icon('bookmark', 'Follows') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_follows", "numeric", $templateVar['base_url']) ?></div>
                <div class="p-1 col text-right"><?= display_fa_icon('sync', 'Last update') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "manga_last_updated", "numeric", $templateVar['base_url']) ?></div>
            </div>
        </div>
    </div>

    <?php foreach ($templateVar['mangas'] as $manga) {
        ++$rank; ?>
        <div class="manga-entry row m-0 border-bottom" data-id="<?= $manga->manga_id ?>"<?php if ($manga instanceof Manga) : ?> data-genre-ids="<?= implode(',', $manga->get_manga_genres()) ?>" data-demo-id="<?= $manga->manga_demo_id ?? '' ?>"<?php endif; ?>>
            <div class="col-md-6 col-lg-7">
                <div class="row">
                    <div class="p-1 col text-truncate d-flex flex-nowrap align-items-center">
                        <?php if ($templateVar['page'] == 'titles' && in_array($templateVar['sort'], [5, 7, 9, 11])) print "<span class='badge badge-info mr-1'>$rank</span>"; ?>
                        <?= display_lang_flag_v3($manga, 1) ?>
                        <a title="<?= $manga->manga_name ?>"
                           href="/title/<?= $manga->manga_id ?>/<?= slugify($manga->manga_name) ?>"
                           class="ml-1 manga_title text-truncate"><?= $manga->manga_name ?></a>
                        <?php if ($manga->manga_hentai) { ?><span class="badge badge-danger ml-1">H</span><?php } ?>
                    </div>
                    <div class="p-1 d-none d-lg-block col-3 text-truncate"><a title="<?= $manga->manga_author ?>"
                                                                              href="/?page=titles&author=<?= $manga->manga_author ?>"><?= $manga->manga_author ?></a>
                    </div>
                    <div class="p-1 col-auto col-md-2 text-center">
                        <?php if (isset($templateVar['list_user_followed_manga_ids_array']) && $templateVar['list_user']->user_id != $templateVar['user']->user_id) { ?>
                            <button title="<?= $follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_name ?>"
                                    class="disabled btn btn-xs btn-<?= $follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_class ?>"><?= display_fa_icon($follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_glyph) ?></button>
                        <?php } ?>
                        <?= display_follow_button($templateVar['user'], $followed_manga_ids_array, $manga->manga_id, 1) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-5">
                <div class="row">
                    <div class="p-1 col"><?= display_count_comments($manga->thread_posts, 'manga', $manga->manga_id, $manga->manga_name) ?></div>
                    <div class="p-1 col text-center text-primary">
                        <?php
                        if (isset($templateVar['list_user_manga_ratings_array'])) {
                            if (isset($templateVar['list_user_manga_ratings_array'][$manga->manga_id]))
                                print "<button style='width: 22px;' disabled class='btn btn-primary btn-xs' title=\"{$templateVar['list_user']->username}'s rating\">{$templateVar['list_user_manga_ratings_array'][$manga->manga_id]}</button>";
                            else
                                print "<button style='width: 22px;' disabled class='btn btn-primary btn-xs' title='Not yet rated by {$templateVar['list_user']->username}'>-</button>";
                        } else {
                            if (isset($user_manga_ratings_array[$manga->manga_id]))
                                print display_manga_rating_button($templateVar['user']->user_id, $user_manga_ratings_array[$manga->manga_id], $manga->manga_id, 1);
                            else
                                print display_manga_rating_button($templateVar['user']->user_id, 0, $manga->manga_id, 1);
                        }
                        ?>
                    </div>
                    <div class="p-1 col text-center text-primary"><span
                                title="<?= number_format($manga->manga_rated_users) ?> votes"><?= $manga->manga_bayesian ?></span></div>
                    <div class="p-1 col text-center text-info"><?= number_format($manga->manga_views) ?></div>
                    <div class="p-1 col text-center text-success"><?= number_format($manga->manga_follows) ?></div>
                    <div class="p-1 col text-right">
                        <time datetime="<?= gmdate(DATETIME_FORMAT, $manga->manga_last_updated) ?>"><?= get_time_ago($manga->manga_last_updated) ?></time>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <!-- grid -->
    <?php
} elseif ($templateVar['title_mode'] == 3) {
    foreach ($templateVar['mangas'] as $manga) {
        ?>
        <div class="manga-entry large_logo rounded position-relative mx-1 my-2" data-id="<?= $manga->manga_id ?>"<?php if ($manga instanceof Manga) : ?> data-genre-ids="<?= implode(',', $manga->get_manga_genres()) ?>" data-demo-id="<?= $manga->manga_demo_id ?? '' ?>"<?php endif; ?>>
            <div class="hover">
                <a href="/title/<?= $manga->manga_id . "/" . slugify($manga->manga_name) ?>"><img width="100%"
                                                                                                  title="<?= $manga->manga_name ?>"
                                                                                                  class="rounded"
                                                                                                  src="/images/manga/<?= $manga->manga_id ?>.large.jpg?<?= @filemtime(ABS_DATA_BASEPATH . "/manga/$manga->manga_id.large.jpg") ?>"/></a>
            </div>
            <div class="<?= ($manga->manga_hentai) ? 'car-caption-h' : 'car-caption' ?> px-2 py-1">
                <p class="text-truncate m-0">
                    <?= display_lang_flag_v3($manga) ?>
                    <a title="<?= $manga->manga_name ?>"
                       href="/title/<?= $manga->manga_id ?>/<?= slugify($manga->manga_name) ?>"
                       class="white ml-1 manga_title text-truncate"><?= $manga->manga_name ?></a>
                </p>
                <?php if (isset($templateVar['list_user_followed_manga_ids_array']) && $templateVar['list_user']->user_id != $templateVar['user']->user_id) { ?>
                    <div class="float-left">
                    <button title="<?= $follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_name ?>"
                            class="disabled btn btn-xs btn-<?= $follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_class ?>"><?= display_fa_icon($follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_glyph) ?></button>
                    </div>
                <?php } ?>
                <div class="float-right">
                    <?= display_follow_button($templateVar['user'], $followed_manga_ids_array, $manga->manga_id, 1, 1) ?>
                </div>
            </div>

        </div>
        <?php
    }
    ?>
    <div class="clearfix mb-3"></div>

    <!-- grid -->
    <?php
} else {
    ?>
    <div class="row mt-1 mx-0">
        <?php
        foreach ($templateVar['mangas'] as $manga) {
            $templateVar['parser']->parse($manga->manga_description);
            ?>
            <div class="manga-entry col-lg-6 border-bottom pl-0 my-1" data-id="<?= $manga->manga_id ?>"<?php if ($manga instanceof Manga) : ?> data-genre-ids="<?= implode(',', $manga->get_manga_genres()) ?>" data-demo-id="<?= $manga->manga_demo_id ?? '' ?>"<?php endif; ?>>
                <div class="rounded large_logo mr-2">
                    <a href="/title/<?= $manga->manga_id . "/" . slugify($manga->manga_name) ?>"><img class="rounded"
                                                                                                      src="/images/manga/<?= $manga->manga_id ?>.large.jpg?<?= @filemtime(ABS_DATA_BASEPATH . "/manga/$manga->manga_id.large.jpg") ?>"
                                                                                                      width="100%"
                                                                                                      alt="image"/></a>
                </div>

                <div class="text-truncate mb-1 d-flex flex-nowrap align-items-center">
                    <?= display_lang_flag_v3($manga, 1) ?>
                    <a class="ml-1 manga_title text-truncate" title="<?= $manga->manga_name ?>"
                       href="/title/<?= $manga->manga_id ?>/<?= slugify($manga->manga_name) ?>"><?= $manga->manga_name ?></a>
                    <?php if ($manga->manga_hentai) { ?>
                        <div><span class="badge badge-danger ml-1">H</span></div><?php } ?>
                </div>

                <ul class="list-inline m-1">
                    <li class="list-inline-item text-primary"><?= display_fa_icon('star', 'Bayesian rating') . " " . (isset($user_manga_ratings_array[$manga->manga_id]) ? $user_manga_ratings_array[$manga->manga_id] : "<span title='You have not rated this title.'>--</span>") ?>
                        (<span title="<?= number_format($manga->manga_rated_users) ?> votes"><?= $manga->manga_bayesian ?></span>)
                    </li>
                    <li class="list-inline-item text-success"><?= display_fa_icon('bookmark', 'Follows') . " " . number_format($manga->manga_follows) ?></li>
                    <li class="list-inline-item text-info"><?= display_fa_icon('eye', 'Views') . " " . number_format($manga->manga_views) ?></li>
                    <li class="list-inline-item"><?= display_count_comments($manga->thread_posts, 'manga', $manga->manga_id, $manga->manga_name) ?></li>
                    <li class="list-inline-item float-right">
                        <?php if (isset($templateVar['list_user_followed_manga_ids_array']) && $templateVar['list_user']->user_id != $templateVar['user']->user_id) { ?>
                            <button title="<?= $follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_name ?>"
                                    class="disabled btn btn-xs btn-<?= $follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_class ?>"><?= display_fa_icon($follow_types->{$templateVar['list_user_followed_manga_ids_array'][$manga->manga_id]}->type_glyph) ?></button>
                        <?php } ?>
                        <?= display_follow_button($templateVar['user'], $followed_manga_ids_array, $manga->manga_id, 1) ?>
                    </li>
                </ul>

                <div style="height: 210px; overflow: hidden;"><?= nl2br($templateVar['parser']->getAsHtml()) ?></div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

if (!in_array($templateVar['page'], ['quick_search', 'featured']))
    print display_pagination_v2($paging, "titles", $templateVar['page'] . $templateVar['base_url']);
