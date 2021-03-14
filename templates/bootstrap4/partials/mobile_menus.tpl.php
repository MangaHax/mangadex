<?php
$ui = $templateVar['ui_lang']->get_ui('navbar');
$unread_pms = $templateVar['user']->get_unread_threads();
$unread_notifications = $templateVar['user']->get_unread_notifications();
$friend_requests = count($templateVar['user']->get_pending_friends_user_ids());
?>

<div id="right_swipe_area" style="position: fixed; top: 0; right: 0px; z-index: 1000; height: 100vh; width: 15px;">&nbsp;</div>
<div id="left_swipe_area" style="position: fixed; top: 0; left: 0px; z-index: 1000; height: 100vh; width: 15px;">&nbsp;</div>

<!-- Modal -->
<?php if (!isset($templateVar['user']->navigation) || $templateVar['user']->navigation == 1) : ?>
<div class="modal left fade" id="left_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<?php else : ?>
<div class="modal right fade" id="right_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<?php endif; ?>
	<div class="modal-dialog" role="document">
		<div class="modal-content border-0">
			<nav class="navbar fixed-top text-nowrap <?= in_array($templateVar['theme_id'], [1]) ? 'navbar-light bg-light' : 'navbar-dark bg-dark' ?>">
				<div class="container p-0">
					<div class="dropdown">
						<a href="<?= ($templateVar['user']->user_id) ? "/user/{$templateVar['user']->user_id}/{$templateVar['user']->user_slug}" : "#" ?>" class="dropdown-toggle no-underline" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
							<?= (!$templateVar['user']->user_id) ? display_fa_icon('user-times') : display_fa_icon('user')	?>
							<span class="d-lg-none d-xl-inline" style="color: #<?= $templateVar['user']->level_colour ?>"><?= $templateVar['user']->username ?></span>
							<?= $unread_pms ? display_fa_icon('envelope', 'Messages', 'text-danger') : '' ?>
							<?= $unread_notifications ? display_fa_icon('exclamation-circle', 'Notifications', 'text-info') : '' ?>
							<?= $friend_requests ? display_fa_icon('user-friends', 'Friend requests', 'text-success') : '' ?>
						</a>
						<div class="dropdown-menu">
							<?php if (!$templateVar['user']->user_id) : ?>
								<a class="dropdown-item" href="/login"><?= display_fa_icon('sign-in-alt') ?> <?= $ui->login ?></a>
								<a class="dropdown-item" href="/signup"><?= display_fa_icon('pencil-alt') ?> <?= $ui->signup ?></a>
							<?php else : ?>
								<?= validate_level($templateVar['user'], 'admin') ? "<a class='dropdown-item' href='/admin'>" . display_fa_icon('user-md') . " Admin</a>" : "" ?>
								<?= validate_level($templateVar['user'], 'mod') ? "<a class='dropdown-item' href='/mod'>" . display_fa_icon('user-md') . " Moderation</a>" : "" ?>

								<?= validate_level($templateVar['user'], 'mod') ? "<div class='dropdown-divider'></div>" : "" ?>

								<a class="dropdown-item" href="/user/<?= $templateVar['user']->user_id . '/' . $templateVar['user']->user_slug ?>"><?= display_fa_icon('user') ?> <?= $ui->profile ?></a>
								<a class="dropdown-item" href="/history"><?= display_fa_icon('history', 'History') ?> History</a>
								<a class="dropdown-item" href="/settings"><?= display_fa_icon('cog') ?> <?= $ui->settings ?></a>

								<div class="dropdown-divider"></div>

								<a class="dropdown-item" href="/list/<?= $templateVar['user']->user_id ?>"><?= display_fa_icon('list', 'My list') ?> MDList</a>
								<a class="dropdown-item" href="/social"><?= display_fa_icon('user-friends', 'social') ?> Social <?= $friend_requests ? "<span class='badge badge-success'>$friend_requests</span>" : '' ?></a>
								<a class="dropdown-item" href="/messages/notifications"><?= display_fa_icon('exclamation-circle', 'notifications') ?> Notifications <?= $unread_notifications ? "<span class='badge badge-info'>$unread_notifications</span>" : '' ?></a>
								<a class="dropdown-item" href="/messages/inbox"><?= display_fa_icon('envelope') ?> <?= $ui->inbox ?> <?= $unread_pms ? "<span class='badge badge-danger'>$unread_pms</span>" : '' ?></a>

								<div class="dropdown-divider"></div>

								<a class="dropdown-item" href="/support"><?= display_fa_icon('dollar-sign', 'support') ?> Support</a>
								<a class="dropdown-item" href="/md_at_home"><?= display_fa_icon('network-wired', 'MangaDex@Home') ?> MD@Home</a>
								<!--<a class="dropdown-item" href="/shop"><?= display_fa_icon('store', 'shop') ?> Shop</a>-->
								<?= (in_array($templateVar['user']->user_id, TL_USER_IDS) || validate_level($templateVar['user'], 'mod')) ? "<a class='dropdown-item' href='/translate'>" . display_fa_icon('globe') . " Translate</a>" : "" ?>

								<div class="dropdown-divider"></div>

								<a class="dropdown-item logout" href="#"><?= display_fa_icon('sign-out-alt', $ui->logout) ?> <?= $ui->logout ?></a>
							<?php endif; ?>
						</div>
					</div>

					<?php if (validate_level($templateVar['user'], 'gmod') && $templateVar['report_count']['chapter_reports'] + $templateVar['report_count']['manga_reports'] > 0) : ?>
						<a class="nav-link ml-auto px-2" href="/mod/<?=$templateVar['report_count']['chapter_reports'] > 0 ? 'chapter_reports' : 'manga_reports'?>/new" title="Pending Reports">
							<span class="badge badge-warning"><?=$templateVar['report_count']['manga_reports'] + $templateVar['report_count']['chapter_reports']?></span>
						</a>
					<?php endif; ?>

					<?php if (validate_level($templateVar['user'], 'mod') && $templateVar['general_report_count'] > 0) : ?>
						<a class="nav-link px-2" href="/mod/reports?state=0" title="Pending Reports"><span class="badge badge-primary">
							<?= $templateVar['general_report_count']?></span>
						</a>
					<?php endif; ?>

					<?php if (!$templateVar['user']->user_id) : ?>
						<button class="navbar-toggler ml-auto" type="button">
							<a class="" href="/login"><span class="navbar-icon"><?= display_fa_icon('sign-in-alt') ?></span></a>
						</button>
					<?php endif; ?>
				</div>
			</nav>

			<nav style="margin-top: 70px;" class="nav flex-column">
				<div class="dropdown nav-link">
					<a href="#" class="dropdown-toggle no-underline" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('book') ?> <?= $ui->manga ?></a>
					<div class="dropdown-menu">
						<a class="dropdown-item <?= display_active($_GET['page'], ['titles']) ?>" href="/titles"><?= display_fa_icon('book') ?> Titles</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['updates']) ?>" href="/updates"><?= display_fa_icon('sync') ?> Updates</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['search']) ?>" href="/search"><?= display_fa_icon('search') ?> Search</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['featured']) ?>" href="/featured"><?= display_fa_icon('tv') ?> Featured</a>
						<a class="dropdown-item" href="/manga"><?= display_fa_icon('question-circle') ?> Random</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['manga_new']) ?>" href="/manga_new"><?= display_fa_icon('plus-circle') ?> Add</a>
					</div>
				</div>

				<?php if (!$templateVar['user']->user_id) : ?>
					<a class="nav-link no-underline" href="/login" title="You need to log in."><?= display_fa_icon('bookmark') ?> <?= $ui->follows ?></a>
				<?php elseif (!$templateVar['user']->activated) : ?>
					<a class="nav-link no-underline" href="/activation" title="You need to activate your account."><?= display_fa_icon('bookmark') ?> <?= $ui->follows ?></a>
				<?php else : ?>
					<a class="nav-link no-underline <?= display_active($_GET['page'], ['follows']) ?>" href="/follows"><?= display_fa_icon('bookmark') ?> <?= $ui->follows ?></a>
				<?php endif; ?>

				<div class="dropdown nav-link">
					<a href="#" class="dropdown-toggle no-underline" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('users') ?> Community</a>
					<div class="dropdown-menu">
						<a class="dropdown-item <?= display_active($_GET['page'], ['forums']) ?>" href="/forums"><?= display_fa_icon('university') ?> <?= $ui->forums ?></a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['groups']) ?>" href="/groups"><?= display_fa_icon('users') ?> <?= $ui->groups ?></a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['users']) ?>" href="/users"><?= display_fa_icon('user') ?> <?= $ui->users ?></a>
						<a class="dropdown-item" href="https://discord.gg/mangadex" target="_blank" rel="nofollow"><?= display_fa_icon('discord', 'Rules', '', 'fab') ?> Discord</a>
						<a class="dropdown-item" href="https://twitter.com/MangaDex" target="_blank" rel="nofollow"><?= display_fa_icon('twitter', 'Twitter', '', 'fab') ?> Twitter</a>
						<a class="dropdown-item" href="https://www.reddit.com/r/mangadex" target="_blank" rel="nofollow"><?= display_fa_icon('reddit', 'Reddit', '', 'fab') ?> Reddit</a>
						<a class="dropdown-item" href="irc://irc.rizon.net/mangadex" rel="nofollow"><?= display_fa_icon('hashtag', 'IRC') ?> IRC</a>
					</div>
				</div>

				<div class="dropdown nav-link">
					<a href="#" class="dropdown-toggle no-underline" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('info-circle') ?> <?= $ui->info ?></a>
					<div class="dropdown-menu">
						<a class="dropdown-item <?= display_active($_GET['page'], ['stats']) ?>" href="/stats"><?= display_fa_icon('clipboard-list', 'Stats') ?> Stats</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['rules']) ?>" href="/rules"><?= display_fa_icon('list', 'Rules') ?> Rules</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['about']) ?>" href="/about"><?= display_fa_icon('info', 'About') ?> About</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['changelog']) ?>" href="/changelog"><?= display_fa_icon('code', 'Change log') ?> Change log</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['affiliates']) ?>" href="/affiliates"><?= display_fa_icon('handshake', 'Affiliates', '', 'far') ?> Affiliates</a>
                        <a class="dropdown-item <?= display_active($_GET['page'], ['tutorial']) ?>" href="/title/30461/bocchi-sensei-teach-me-mangadex"><?= display_fa_icon('question', 'Tutorial') ?> Tutorial</a>
					</div>
				</div>
			</nav>

            <form id="quick_search_form_m" method="get" action="/quick_search" role="search" class="form-inline px-3 py-2 quick-search">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <select class="form-control" id="quick_search_type" name="type">
                            <option value="all">All</option>
                            <option selected value="titles">Manga</option>
                            <option value="groups">Groups</option>
                            <option value="users">Users</option>
                        </select>
                    </div>
                    <input type="text" class="form-control" placeholder="Quick search" name="term" id="quick_search_input" required>
                    <span class="input-group-append">
						<button class="btn btn-secondary" type="submit" id="quick_search_button" name="submit"><?= display_fa_icon('search') ?></button>
					</span>
                </div>
            </form>
		</div><!-- modal-content -->
	</div><!-- modal-dialog -->
</div><!-- modal -->

<!-- Modal -->
<?php if (!isset($templateVar['user']->navigation) || $templateVar['user']->navigation == 1) : ?>
<div class="modal right fade" id="right_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2">
<?php else : ?>
<div class="modal left fade" id="left_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2">
<?php endif; ?>
	<div class="modal-dialog" role="document">
		<div class="modal-content border-0">
			<nav class="navbar fixed-top text-nowrap <?= in_array($templateVar['theme_id'], [1]) ? 'navbar-light bg-light' : 'navbar-dark bg-dark' ?>">
				<div class="container p-0">
					<a class="p-0 navbar-brand" href="<?= URL ?>"><img class="mx-2" height="38px" src="/images/misc/navbar.svg?3" alt="MangaDex" title="MangaDex" /> <small class="d-lg-none d-xl-inline">MikuDex</small></a>
				</div>
			</nav>

			<div class="modal-body">
				<img src="/images/misc/miku.jpg" width="100%" />
			</div>
		</div><!-- modal-content -->
	</div><!-- modal-dialog -->
</div><!-- modal -->