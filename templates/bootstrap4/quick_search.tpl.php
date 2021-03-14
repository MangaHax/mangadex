<?php

//$paging = pagination($templateVar['manga_count'], $templateVar['current_page'], $templateVar['limit'], $templateVar['sort']);

?>
<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" href="#search_manga" aria-controls="search_manga" data-toggle="tab"><?= display_fa_icon('book') ?> Search manga</a></li>
    <li class="nav-item"><a class="nav-link" href="#search_groups" aria-controls="search_groups" data-toggle="tab"><?= display_fa_icon('users') ?> Search groups</a></li>
    <li class="nav-item"><a class="nav-link" href="#search_users" aria-controls="search_users" data-toggle="tab"><?= display_fa_icon('user') ?> Search users</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade show active" id="search_manga">
        <?= $templateVar['manga_search_tab_html'] ?>
        <p class="text-center"><a class="btn btn-secondary" href="/search/?title=<?= $templateVar['term'] ?>"><?= display_fa_icon('search') ?> Refine search for manga</a></p>
    </div>

    <div role="tabpanel" class="tab-pane fade" id="search_groups">
        <?= $templateVar['group_search_tab_html'] ?>
        <?php
        //$_GET["group_name"] = $_GET['term'];
        //require_once(ABSPATH . '/pages/groups.req.php');
        ?>
        <p class="text-center"><a class="btn btn-secondary" href="/groups/0/1/<?= $templateVar['term'] ?>"><?= display_fa_icon('search') ?> Refine search for groups</a></p>
    </div>

    <div role="tabpanel" class="tab-pane fade" id="search_users">
        <?= $templateVar['user_search_tab_html'] ?>
        <?php
        //$_GET["username"] = $_GET['term'];
        //require_once(ABSPATH . '/pages/users.req.php');
        ?>
        <p class="text-center"><a class="btn btn-secondary" href="/users/0/1/<?= $templateVar['term'] ?>"><?= display_fa_icon('search') ?> Refine search for users</a></p>
    </div>
</div>