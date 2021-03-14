<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'stats') ? 'active' : '' ?>" href="/admin/stats" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('chart-line', 'Stats', 'fas') ?> Stats</a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link <?= ($templateVar['mode'] == 'visit_logs') ? 'active' : '' ?> dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('clock', '', '', 'far') ?> Visit logs</a>
        <div class="dropdown-menu">
            <a href="/admin/visit_logs/summary" class="dropdown-item <?= ($templateVar['mode'] == 'visit_logs' && $templateVar['type'] == 'summary') ? 'active' : '' ?>">Summary</a>
            <a href="/admin/visit_logs/recent" class="dropdown-item <?= ($templateVar['mode'] == 'visit_logs' && $templateVar['type'] == 'recent') ? 'active' : '' ?>">Recent</a>
        </div>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link <?= ($templateVar['mode'] == 'ip_unban') ? 'active' : '' ?> dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('gavel') ?> IP</a>
        <div class="dropdown-menu">
            <a href="/admin/ip_unban/ip_unban" class="dropdown-item <?= ($templateVar['mode'] == 'ip_unban' && $templateVar['type'] == 'ip_unban') ? 'active' : '' ?>">IP Unban</a>
            <a href="/admin/ip_unban/ip_ban" class="dropdown-item <?= ($templateVar['mode'] == 'ip_unban' && $templateVar['type'] == 'ip_ban') ? 'active' : '' ?>">IP Banlist</a>
            <a href="/admin/ip_unban/ip_failtwoban" class="dropdown-item <?= ($templateVar['mode'] == 'ip_unban' && $templateVar['type'] == 'ip_failtwoban') ? 'active' : '' ?>">Fail2Ban Logs</a>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'cache_stats') ? 'active' : '' ?>" href="/admin/cache_stats" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('memory') ?> Cache Stats</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'ip_tracking') ? 'active' : '' ?>" href="/admin/ip_tracking" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('shoe-prints', 'Track IP', 'fas') ?> IP Tracking</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'report_reasons') ? 'active' : '' ?>" href="/admin/report_reasons" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('gavel', 'Edit Report Reasons', 'fas') ?> Report Reasons</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'tempmail') ? 'active' : '' ?>" href="/admin/tempmail" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('at', 'Temp mail', 'fas') ?> Temp mail</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'database') ? 'active' : '' ?>" href="/admin/database" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('database', 'Databases', 'fas') ?> DBs</a>
    </li>
</ul>
