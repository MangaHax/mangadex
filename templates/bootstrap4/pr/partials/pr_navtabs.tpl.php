<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'banners') ? 'active' : '' ?>" href="/pr/banners"><?= display_fa_icon('ad', 'Banners', 'fas') ?>Banners</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'email_search') ? 'active' : '' ?>" href="/pr/email_search"><?= display_fa_icon('at', 'Email Search', 'fas') ?>Email Search</a>
    </li>
</ul>