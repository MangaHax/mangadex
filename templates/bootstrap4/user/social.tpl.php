<ul class="nav nav-tabs">
    <li class="nav-item" title="Friends" >
        <a class="nav-link <?= ($templateVar['mode'] == 'friends') ? 'active' : '' ?>" href="/social/friends"><?= display_fa_icon('user-friends') ?> Friends</a>
    </li>

    <li class="nav-item" title="Blocked">
        <a class="nav-link <?= ($templateVar['mode'] == 'blocked') ? 'active' : '' ?>" href="/social/blocked"><?= display_fa_icon('angry') ?> Blocked</a>
    </li>
</ul>

<!-- Tab panes -->
<div class="tab-content">

    <?= $templateVar['social_tab_html'] ?>

</div>