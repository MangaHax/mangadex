<div class="container mt-4">

    <?php if (isset($templateVar['useragent'])) : ?>
    <div class="form-group row">
        <label class="col-md-4 col-lg-3 col-xl-2 col-form-label">Browser Session:</label>
        <div class="col-md-8 col-lg-9 col-xl-10">
            <div id="browser_sessions_container" class="container mt-2">
                <div class="row">
                    <div class="p-0 col-12 col-lg-9 col-xl-6">
                        <div class="card">
                            <div class="card-header">
                                Your current Browser Session
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item" style="position:relative">
                                    <a href="/ajax/actions.ajax.php?function=logout&nojs=1" class="btn btn-sm btn-link session_remove_btn float-right" title="Logout"><i class="fa fa-times"></i></a>
                                    <i class="fas fa-2x fa-globe float-left"></i>
                                    <strong class="ml-4"><?= $templateVar['useragent']->isMobile() ? 'Mobile browser session' : 'Desktop browser session' ?></strong><br />
                                    <small class="ml-4"><i>Expires after 1 day of inactivity</i></small>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

	<div class="form-group row">
		<label class="col-md-4 col-lg-3 col-xl-2 col-form-label">Persistent Sessions:</label>
		<div class="col-md-8 col-lg-9 col-xl-10">
			<?php if (empty($templateVar['sessions'])) : ?>
				<div class="alert alert-info">You have no active Remember-me Sessions for this account.</div>
			<?php else : ?>
				<div id="sessions_container" class="container mt-2">
					<div class="row">
						<div class="p-0 col-12 col-lg-9 col-xl-6">
							<div class="card">
								<div class="card-header">
									Where you're logged in (Remember-me Sessions)
                                    <button id="clear_sessions_btn" title="Clears all Remember-me Sessions" type="button" class="btn btn-danger btn-xs float-right">Clear sessions</button>
								</div>
								<ul class="list-group list-group-flush">
								<?php foreach ($templateVar['sessions'] AS $session) :
									$regionData = json_decode($session['region_data'], 1);

									$icon = 'desktop';
									$class = 'fa';

									switch (strtolower($regionData['browser'])) {
										case 'chrome':
										case 'vivaldi':
											$icon = 'chrome';
											$class = 'fab';
											break;

										case 'firefox':
											$icon = 'firefox';
											$class = 'fab';
											break;

										case 'safari':
											$icon = 'safari';
											$class = 'fab';
											break;
									}

									switch (strtolower($regionData['os'])) {
										case 'android':
											$icon = 'android';
											$class = 'fab';
											break;

										case 'ios':
											$icon = 'apple';
											$class = 'fab';
											break;
									}
								?>
									<li class="list-group-item" style="position:relative">
										<button type="button" class="btn btn-sm btn-link session_remove_btn float-right" title="Remove this session" data-session-id="<?= $session['session_id'] ?>"><i class="fa fa-times"></i></button>
										<i class="<?= $class ?> fa-2x fa-<?= $icon ?> float-left"></i>
										<strong class="ml-4"><?= $regionData['browser'] ?> on <?= $regionData['os'] ?> (<?= $regionData['device'] ? $regionData['device'] : 'Desktop' ?>)</strong><br />
										<small class="ml-4">Near <?= $regionData['city'] ? $regionData['city'] : '&lt;Unknown City&gt;' ?>, <?= $regionData['country_name'] ? $regionData['country_name'] : '&lt;Unknown Country&gt;' ?> <i>(Created: <?= date(DATETIME_FORMAT, $session['created']) ?>)</i></small>
									</li>
								<?php endforeach; ?>
								</ul>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>