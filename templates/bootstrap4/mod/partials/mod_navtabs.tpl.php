<?php
    $isGlobalMod = validate_level($templateVar['user'], 'gmod');
?>
<ul class="nav nav-tabs">
    <?php if ($isGlobalMod) : ?>
    <a class="nav-link <?= ($templateVar['mode'] == 'chapter_reports') ? 'active' : '' ?>" href="/mod/chapter_reports/new"><?= display_fa_icon('file', '', '', 'far') ?> Chapter reports</a>
    <li class="nav-item dropdown">
        <a class="nav-link  <?= ($templateVar['mode'] == 'chapter_reports') ? 'active' : '' ?> dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"></a>
        <div class="dropdown-menu">
            <a href="/mod/chapter_reports/new" class="dropdown-item title_mode <?= ($templateVar['mode'] == 'chapter_reports' && $templateVar['type'] == 'new') ? 'active' : '' ?>">New reports</a>
            <a href="/mod/chapter_reports/old" class="dropdown-item title_mode <?= ($templateVar['mode'] == 'chapter_reports' && $templateVar['type'] == 'old') ? 'active' : '' ?>">Old reports</a>
        </div>
    </li>
    <a class="nav-link <?= ($templateVar['mode'] == 'manga_reports') ? 'active' : '' ?>" href="/mod/manga_reports/new"><?= display_fa_icon('book') ?> Manga reports</a>
    <li class="nav-item dropdown">
        <a class="nav-link <?= ($templateVar['mode'] == 'manga_reports') ? 'active' : '' ?> dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"></a>
        <div class="dropdown-menu">
            <a href="/mod/manga_reports/new" class="dropdown-item title_mode <?= ($templateVar['mode'] == 'manga_reports' && $templateVar['type'] == 'new') ? 'active' : '' ?>">New reports</a>
            <a href="/mod/manga_reports/old" class="dropdown-item title_mode <?= ($templateVar['mode'] == 'manga_reports' && $templateVar['type'] == 'old') ? 'active' : '' ?>">Old reports</a>
        </div>
    </li>
    <?php endif; ?>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'reports') ? 'active' : '' ?>" href="/mod/reports"><?= display_fa_icon('flag', 'View Reports', 'fas') ?> Comment Reports</a>
    </li>
	<li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'upload_queue') ? 'active' : '' ?>" href="/mod/upload_queue"><?= display_fa_icon('upload', 'Upload Queue', 'fas') ?> Upload Queue</a>
    </li>
	<?php if ($isGlobalMod) : ?>
    <li class="nav-item dropdown">
        <a class="nav-link <?= ($templateVar['mode'] == 'featured') ? 'active' : '' ?> dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('tv', 'ID') ?> Featured</a>
        <div class="dropdown-menu">
            <?php
            foreach ($templateVar['manga_lists'] as $list) {
                if (isset($list->list_id))
                    print "<a class='dropdown-item " . (($templateVar['mode'] == 'featured' && $templateVar['type'] == $list->list_id) ? 'active' : '') . "' href='/mod/featured/$list->list_id'>$list->list_name</a>";
            }
            ?>
        </div>
    </li>
	<?php endif; ?>
    <li class="nav-item dropdown ml-auto">
        <a class="nav-link <?= ($templateVar['mode'] == 'user_restrictions') ? 'active' : '' ?> dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('hammer') ?> User Restrictions</a>
        <div class="dropdown-menu">
            <a href="/mod/user_restrictions/active" class="dropdown-item title_mode <?= ($templateVar['mode'] == 'user_restrictions' && $templateVar['type'] == 'active') ? 'active' : '' ?>">Active</a>
            <a href="/mod/user_restrictions/expired" class="dropdown-item title_mode <?= ($templateVar['mode'] == 'user_restrictions' && $templateVar['type'] == 'expired') ? 'active' : '' ?>">Expired</a>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'user_tracking') ? 'active' : '' ?>" href="/mod/user_tracking"><?= display_fa_icon('shoe-prints', 'User Tracking', 'fas') ?> User Tracking</a>
    </li>

</ul>