<?php

$navInfo = [
    'titles' => [
        'icon' => display_fa_icon('book', 'Manga titles'),
        'label' => 'Titles',
        'title' => 'Manga titles'
    ],
    'search' => [
        'icon' => display_fa_icon('search', 'Advanced search'),
        'label' => 'Search',
        'title' => 'Advanced search'
    ],
    'featured' => [
        'icon' => display_fa_icon('tv', 'Featured'),
        'label' => 'Featured',
        'title' => 'Spring 2018'
    ],
    'manga_new' => [
        'icon' => display_fa_icon('plus-circle', 'Add manga title'),
        'label' => 'Add',
        'title' => 'Add manga title'
    ],
];

?>
<ul class="nav nav-tabs">
    <?php foreach ($navInfo AS $route => $info) : ?>
    <li class="nav-item" title="<?= $info['title'] ?>"><a class="nav-link<?= $templateVar['page'] == $route ? ' active' : '' ?>" href="<?= $templateVar['page'] == $route ? '#' : "/$route" ?>"><?= $info['icon'] ?> <span class="d-none d-md-inline"><?= $info['label'] ?></span></a></li>
    <?php endforeach; ?>

    <?php if ($templateVar['show_title_modes'] ?? true) : ?>
    <li class="nav-item dropdown ml-auto">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon(MANGA_VIEW_MODE_ICONS[$templateVar['title_mode']]) ?></a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="#" class="dropdown-item title_mode <?= (!$templateVar['title_mode']) ? 'active' : '' ?>" id="0"><?= display_fa_icon('th-large') ?> Detailed</a>
            <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 1) ? 'active' : '' ?>" id="1"><?= display_fa_icon('th-list') ?> Expanded list</a>
            <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 2) ? 'active' : '' ?>" id="2"><?= display_fa_icon('bars') ?> Simple list</a>
            <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 3) ? 'active' : '' ?>" id="3"><?= display_fa_icon('th') ?> Grid</a>
        </div>
    </li>
    <?php endif; ?>
</ul>
