<?php
$ui = $templateVar['ui_lang']->get_ui('navbar');
$unread_pms = $templateVar['user']->get_unread_threads();
$unread_notifications = $templateVar['user']->get_unread_notifications();
$friend_requests = count($templateVar['user']->get_pending_friends_user_ids());
?>

<nav class="navbar fixed-top navbar-expand-lg text-nowrap <?= in_array($templateVar['theme_id'], [1]) ? 'navbar-light bg-light' : 'navbar-dark bg-dark' ?>">
	<div class="container">
		<?php if (!isset($templateVar['user']->navigation) || $templateVar['user']->navigation == 1) : ?>
		<button class="navbar-toggler mr-auto" type="button" data-toggle="modal" data-target="#left_modal">
			<span class="navbar-toggler-icon"></span>
		</button>
		<?php else : ?>
		<button class="navbar-toggler mr-auto" type="button" data-toggle="modal" data-target="#homepage_settings_modal">
			<span class="navbar-icon"><?= display_fa_icon('cog') ?></span>
		</button>
		<?php endif; ?>

		<a class="p-0 navbar-brand" href="<?= URL ?>"><img class="mx-2" height="38px" src="/images/misc/navbar.svg?3" alt="<?= TITLE ?>" title="<?= TITLE ?>" /> <small class="d-lg-none d-xl-inline"><?= TITLE ?></small></a>

		<?php if (!isset($templateVar['user']->navigation) || $templateVar['user']->navigation == 1) : ?>
		<button class="navbar-toggler ml-auto" type="button" data-toggle="modal" data-target="#homepage_settings_modal">
			<span class="navbar-icon"><?= display_fa_icon('cog') ?></span>
		</button>
		<?php elseif ($templateVar['user']->navigation == 2) : ?>
		<button class="navbar-toggler ml-auto" type="button" data-toggle="modal" data-target="#right_modal">
			<span class="navbar-toggler-icon"></span>
		</button>
		<?php else : ?>
		<button class="navbar-toggler ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<?php endif; ?>

		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item dropdown mx-1">
					<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('book') ?> <?= $ui->manga ?></a>
					<div class="dropdown-menu">
						<a class="dropdown-item <?= display_active($_GET['page'], ['titles']) ?>" href="/titles"><?= display_fa_icon('book') ?> Titles</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['updates']) ?>" href="/updates"><?= display_fa_icon('sync') ?> Updates</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['search']) ?>" href="/search"><?= display_fa_icon('search') ?> Search</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['featured']) ?>" href="/featured"><?= display_fa_icon('tv') ?> Featured</a>
						<a class="dropdown-item" href="/manga"><?= display_fa_icon('question-circle') ?> Random</a>
						<a class="dropdown-item <?= display_active($_GET['page'], ['manga_new']) ?>" href="/manga_new"><?= display_fa_icon('plus-circle') ?> Add</a>
					</div>
				</li>

				<?php if (!$templateVar['user']->user_id) : ?>

					<li class="nav-item mx-1" id="login">
						<a class="nav-link" href="/login" title="You need to log in."><?= display_fa_icon('bookmark') ?> <?= $ui->follows ?></a>
					</li>

				<?php elseif (!$templateVar['user']->activated) : ?>

					<li class="nav-item mx-1" id="activation">
						<a class="nav-link" href="/activation" title="You need to activate your account."><?= display_fa_icon('bookmark') ?> <?= $ui->follows ?></a>
					</li>

				<?php else : ?>

					<li class="nav-item mx-1 <?= display_active($_GET['page'], ['follows', 'followed_manga', 'followed_groups', 'follows_import']) ?>" id="follows">
						<a class="nav-link" href="/follows"><?= display_fa_icon('bookmark') ?> <?= $ui->follows ?></a>
					</li>

				<?php endif; ?>

				<li class="nav-item mx-1 dropdown">
					<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('users') ?> <?= $ui->community ?></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/forums"><?= display_fa_icon('university', $ui->forums) ?> <?= $ui->forums ?></a>
						<a class="dropdown-item" href="/groups"><?= display_fa_icon('users', $ui->groups) ?> <?= $ui->groups ?></a>
						<a class="dropdown-item" href="/users"><?= display_fa_icon('user', $ui->users) ?> <?= $ui->users ?></a>
						<a class="dropdown-item" href="https://discord.gg/mangadex" target="_blank" rel="nofollow"><?= display_fa_icon('discord', 'Rules', '', 'fab') ?> Discord</a>
						<a class="dropdown-item" href="https://twitter.com/MangaDex" target="_blank" rel="nofollow"><?= display_fa_icon('twitter', 'Twitter', '', 'fab') ?> Twitter</a>
						<a class="dropdown-item" href="https://www.reddit.com/r/mangadex" target="_blank" rel="nofollow"><?= display_fa_icon('reddit', 'Reddit', '', 'fab') ?> Reddit</a>
						<a class="dropdown-item" href="https://mangadexofficial.tumblr.com/" target="_blank" rel="nofollow"><?= display_fa_icon('tumblr', 'Tumblr', '', 'fab') ?> Tumblr</a>
						<a class="dropdown-item" href="irc://irc.rizon.net/mangadex" rel="nofollow"><?= display_fa_icon('hashtag', 'IRC') ?> IRC</a>
					</div>
				</li>

				<li class="nav-item mx-1 dropdown">
					<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('info-circle') ?> <?= $ui->info ?></a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="/stats"><?= display_fa_icon('clipboard-list', 'Stats') ?> Stats</a>
						<a class="dropdown-item" href="/rules"><?= display_fa_icon('list', 'Rules') ?> Rules</a>
						<a class="dropdown-item" href="/about"><?= display_fa_icon('info', 'About') ?> About</a>
						<a class="dropdown-item" href="/changelog"><?= display_fa_icon('code', 'Change log') ?> Change log</a>
						<a class="dropdown-item" href="/affiliates"><?= display_fa_icon('handshake', 'Affiliates', '', 'far') ?> Affiliates</a>
                        <a class="dropdown-item" href="/title/30461/bocchi-sensei-teach-me-mangadex"><?= display_fa_icon('question', 'Tutorial') ?> Tutorial</a>
					</div>
				</li>
			</ul>

			<form id="quick_search_form" method="get" action="/quick_search" role="search" class="form-inline mx-1 quick-search">
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

			<ul class="navbar-nav">
				<li class="d-none d-lg-block nav-item mx-1" id="homepage_cog">
					<a class="nav-link" href="#" title="Settings" data-toggle="modal" data-target="#homepage_settings_modal"><?= display_fa_icon('cog', $ui->settings) ?></a>
				</li>

				<li class="nav-item mx-1 dropdown">
					<a href="<?= ($templateVar['user']->user_id) ? "/user/{$templateVar['user']->user_id}/{$templateVar['user']->user_slug}" : "#" ?>" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						<?= (!$templateVar['user']->user_id) ? display_fa_icon('user-times') : display_fa_icon('user')	?>
						<span class="d-lg-none d-xl-inline" style="color: #<?= $templateVar['user']->level_colour ?>"><?= $templateVar['user']->username ?></span>
						<?= ($unread_pms) ? display_fa_icon('envelope', 'Messages', 'text-danger') : '' ?>
						<?= ($unread_notifications) ? display_fa_icon('exclamation-circle', 'Notifications', 'text-info') : '' ?>
						<?= ($friend_requests) ? display_fa_icon('user-friends', 'Friend requests', 'text-success') : '' ?>
					</a>

					<div class="dropdown-menu dropdown-menu-right">
						<?php if (!$templateVar['user']->user_id) { ?>
							<a class="dropdown-item" href="/login"><?= display_fa_icon('sign-in-alt', $ui->login) ?> <?= $ui->login ?></a>
							<a class="dropdown-item" href="/signup"><?= display_fa_icon('pencil-alt', $ui->signup) ?> <?= $ui->signup ?></a>
						<?php }

						else { ?>
							<?= validate_level($templateVar['user'], 'admin') ? "<a class='dropdown-item' href='/admin'>" . display_fa_icon('user-md') . " Admin</a>" : "" ?>
							<?= validate_level($templateVar['user'], 'mod') ? "<a class='dropdown-item' href='/mod'>" . display_fa_icon('user-md') . " Moderation</a>" : "" ?>

							<?= validate_level($templateVar['user'], 'mod') ? "<div class='dropdown-divider'></div>" : "" ?>

							<a class="dropdown-item" href="/user/<?= $templateVar['user']->user_id . '/' . $templateVar['user']->user_slug ?>">
							    <?= display_fa_icon('user', $ui->profile) ?> <?= $ui->profile ?>
                            </a>
							<a class="dropdown-item" href="/history"><?= display_fa_icon('history', 'History') ?> History</a>
							<a class="dropdown-item" href="/settings"><?= display_fa_icon('cog', $ui->settings) ?> <?= $ui->settings ?></a>

							<div class="dropdown-divider"></div>

							<a class="dropdown-item" href="/list/<?= $templateVar['user']->user_id ?>"><?= display_fa_icon('list', 'My list') ?> MDList</a>
							<a class="dropdown-item" href="/social"><?= display_fa_icon('user-friends', 'social') ?> Social <?= $friend_requests ? "<span class='badge badge-success'>$friend_requests</span>" : '' ?></a>
							<a class="dropdown-item" href="/messages/notifications"><?= display_fa_icon('exclamation-circle', 'notifications') ?> Notifications <?= ($unread_notifications) ? "<span class='badge badge-info'>$unread_notifications</span>" : '' ?></a>
							<a class="dropdown-item" href="/messages/inbox"><?= display_fa_icon('envelope', $ui->inbox) ?> <?= $ui->inbox ?> <?= ($unread_pms) ? "<span class='badge badge-danger'>$unread_pms</span>" : '' ?></a>

							<div class="dropdown-divider"></div>

							<a class="dropdown-item" href="/support"><?= display_fa_icon('dollar-sign', 'support') ?> Support</a>
							<a class="dropdown-item" href="/md_at_home"><?= display_fa_icon('network-wired', 'MangaDex@Home') ?> MD@Home</a>
							<!--<a class="dropdown-item" href="/shop"><?= display_fa_icon('store', 'shop') ?> Shop</a>-->
							<?= (in_array($templateVar['user']->user_id, TL_USER_IDS) || validate_level($templateVar['user'], 'mod')) ? "<a class='dropdown-item' href='/translate'>" . display_fa_icon('globe') . " Translate</a>" : "" ?>

							<div class="dropdown-divider"></div>

							<a class="dropdown-item logout" href="#"><?= display_fa_icon('sign-out-alt', $ui->logout) ?> <?= $ui->logout ?></a>
						<?php } ?>
					</div>
				</li>

                <?php if (validate_level($templateVar['user'], 'gmod') && $templateVar['report_count']['chapter_reports'] + $templateVar['report_count']['manga_reports'] > 0) : ?>
                    <li class="d-none d-lg-block nav-item">
                        <a class="nav-link" href="/mod/<?=$templateVar['report_count']['chapter_reports'] > 0 ? 'chapter_reports' : 'manga_reports'?>/new" title="Pending Reports"><span class="badge badge-warning"><?= display_fa_icon('book') ?> <?= $templateVar['report_count']['manga_reports'] + $templateVar['report_count']['chapter_reports'] ?></span></a>
                    </li>
                <?php endif; ?>
                <?php if (validate_level($templateVar['user'], 'mod') && $templateVar['general_report_count'] > 0) : ?>
                    <li class="d-none d-lg-block nav-item">
                        <a class="nav-link" href="/mod/reports?state=0" title="Pending Reports"><span class="badge badge-primary"><?= display_fa_icon('comments') ?> <?= $templateVar['general_report_count'] ?></span></a>
					</li>
				<?php endif; ?>
				<?php if (validate_level($templateVar['user'], 'mod') && $templateVar['upload_queue_count'] > 0) : ?>
                    <li class="d-none d-lg-block nav-item">
                        <a class="nav-link" href="/mod/upload_queue" title="Upload Queue"><span class="badge badge-success"><?= display_fa_icon('upload') ?> <?= $templateVar['upload_queue_count'] ?></span></a>
                    </li>
                <?php endif; ?>
			</ul>
		</div>
	</div>
</nav>
