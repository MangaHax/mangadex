<!-- Nav tabs -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link <?= ($templateVar['section'] == 'info') ? 'active' : '' ?>" href="/md_at_home/info"><?= display_fa_icon('network-wired', 'MangaDex@Home') ?> <span class="d-none d-lg-inline">MangaDex@Home</span></a></li>
    <li class="nav-item"><a class="nav-link <?= ($templateVar['section'] == 'stats') ? 'active' : '' ?>" href="/md_at_home/stats"><?= display_fa_icon('chart-line', 'Statistics') ?> <span class="d-none d-lg-inline">Statistics</span></a></li>
    <li class="nav-item"><a class="nav-link <?= ($templateVar['section'] == 'request') ? 'active' : '' ?>" href="/md_at_home/request"><?= display_fa_icon('envelope', 'Request a client') ?> <span class="d-none d-lg-inline">Request a client</span></a></li>
	<?php if (validate_level($templateVar['user'], 'member')) :  ?>
    <li class="nav-item"><a class="nav-link <?= ($templateVar['section'] == 'clients') ? 'active' : '' ?>" href="/md_at_home/clients"><?= display_fa_icon('server', 'My clients') ?> <span class="d-none d-lg-inline">My clients</span></a></li>
	<?php endif; ?>
	<?php if (validate_level($templateVar['user'], 'admin')) :  ?>
	<li class="nav-item"><a class="nav-link <?= ($templateVar['section'] == 'admin') ? 'active' : '' ?>" href="/md_at_home/admin"><?= display_fa_icon('user-md', 'Admin') ?> <span class="d-none d-lg-inline">Admin</span></a></li>
	<?php endif; ?>
</ul>

<!-- Tab panes -->
<div class="tab-content">

	<?= $templateVar['tab_html'] ?>

</div>
