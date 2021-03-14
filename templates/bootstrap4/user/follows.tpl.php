<!-- Nav tabs -->
<ul class="nav nav-tabs">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle <?= ($templateVar['mode'] == 'chapters') ? 'active' : '' ?>" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('sync') ?> <span class="d-none d-md-inline">Latest updates</span></a>
        <div class="dropdown-menu">
            <a class="<?= (!$templateVar['list_type'] && $templateVar['mode'] == 'chapters') ? 'active' : '' ?> dropdown-item" href="/follows/chapters/0"><?= display_fa_icon('book') ?> All</a>
            <?php
            foreach ($templateVar['follow_types'] as $type) {
                print "<a class='" . (($templateVar['list_type'] == $type->type_id && $templateVar['mode'] == 'chapters') ? 'active ' : '') . "dropdown-item' href='/follows/chapters/$type->type_id'>" . display_fa_icon($type->type_glyph) . " $type->type_name</a>";
            }
            ?>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle <?= ($templateVar['mode'] == 'manga') ? 'active' : '' ?>" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('book') ?> <span class="d-none d-md-inline">Manga</span></a>
        <div class="dropdown-menu">
            <a class="<?= (!$templateVar['list_type'] && $templateVar['mode'] == 'manga') ? 'active' : '' ?> dropdown-item" href="/follows/manga/0"><?= display_fa_icon('book') ?> All</a>
            <?php
            foreach ($templateVar['follow_types'] as $type) {
                print "<a class='" . (($templateVar['list_type'] == $type->type_id && $templateVar['mode'] == 'manga') ? 'active ' : '') . "dropdown-item' href='/follows/manga/$type->type_id'>" . display_fa_icon($type->type_glyph) . " $type->type_name</a>";
            }
            ?>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'groups') ? 'active' : '' ?>" href="/follows/groups/"><?= display_fa_icon('users') ?> <span class="d-none d-md-inline">Groups</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'import') ? 'active' : '' ?>" href="/follows/import/"><?= display_fa_icon('upload') ?> <span class="d-none d-md-inline">Import (Batoto)</span></a>
    </li>

    <?php if ($templateVar['mode'] == 'manga') { ?>
        <li class="dropdown ml-auto">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon(MANGA_VIEW_MODE_ICONS[$templateVar['title_mode']]) ?></a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="#" class="dropdown-item title_mode <?= (!$templateVar['title_mode']) ? 'active' : '' ?>" id="0"><?= display_fa_icon('th-large') ?> Detailed</a>
                <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 1) ? 'active' : '' ?>" id="1"><?= display_fa_icon('th-list') ?> Expanded list</a>
                <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 2) ? 'active' : '' ?>" id="2"><?= display_fa_icon('bars') ?> Simple list</a>
                <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 3) ? 'active' : '' ?>" id="3"><?= display_fa_icon('th') ?> Grid</a>
            </div>
        </li>
    <?php } else { ?>
        <?= display_rss_link($templateVar['user'], 'follows', $templateVar['manga']->manga_id) ?>
    <?php } ?>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade show active" id="chapters">
        <?= $templateVar['follow_page_html'] ?>
    </div>
</div>