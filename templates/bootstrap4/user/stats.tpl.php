<!-- Nav tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'top') ? 'active' : '' ?>" href="/stats/top"><?= display_fa_icon('upload') ?> <span class="d-none d-md-inline">Recent chapters</span></a>
    </li>
	<li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'trending') ? 'active' : '' ?>" href="/stats/trending"><?= display_fa_icon('eye') ?> <span class="d-none d-md-inline">Trending chapters</span></a>
	</li>
</ul>


<!-- Tab panes -->
<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade show active" id="history">
        <?php
        switch ($templateVar['mode']) {
            case 'top':
				?>
				<div class="row">
					<div class="col-lg-4">
						<div class="card mb-3">
							<h6 title="Updates every 20 minutes" class="card-header text-center">6 hours</h6>
							<?= display_top_list($templateVar['top_chapters_6h'], '', 20) ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="card mb-3">
							<h6 title="Updates every hour" class="card-header text-center">24 hours</h6>
							<?= display_top_list($templateVar['top_chapters_24h'], '', 20) ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="card mb-3">
							<h6 title="Updates every 2 hours" class="card-header text-center">7 days</h6>
							<?= display_top_list($templateVar['top_chapters_7d'], '', 20) ?>
						</div>
					</div>
				</div>
				
				<?php
                break;
				
			case 'trending':
				?>
				<div class="row">
					<div class="col-lg-4">
						<div class="card mb-3">
							<h6 title="Updates every minute" class="card-header text-center">Past 10 mins</h6>
							<?= display_top_list($templateVar['trending_chapters_live'], '', 20) ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="card mb-3">
							<h6 title="Updates every 10 minutes" class="card-header text-center">Past hour</h6>
							<?= display_top_list($templateVar['trending_chapters_hour'], '', 20) ?>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="card mb-3">
							<h6 title="Updates every hour" class="card-header text-center">Past 24 hours</h6>
							<?= display_top_list($templateVar['trending_chapters_day'], '', 20) ?>
						</div>
					</div>
				</div>
				
				<?php
                break;
        }
        ?>
    </div>
</div>