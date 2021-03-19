<?php /*
<div class="card mb-3">

	$folder = read_dir('/images/banners');
	$r = rand(2, count($folder)+1);
	$f = explode('.', $folder[$r]);
	print "<a href='/title/{$f[0]}'><img width='100%' src='/images/banners/{$folder[$r]}?2' /></a>";

</div>
*/ ?>

<div class="row">
    <div class="col-lg-8">
    <?= parse_template('ads/mobile_app_ad', $templateVar['banners']) ?>
        <div class="card mb-3">
            <h6 class="card-header text-center"><?= display_fa_icon('external-link-alt') ?> <a href="/updates">Latest updates</a></h6>
            <div class="card-header p-0">
                <ul class="nav nav-pills nav-justified" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#latest_update" aria-controls="latest_update" data-toggle="tab">All</a></li>
                    <li class="nav-item"><a class="nav-link" href="#follows_update" aria-controls="follows_update" data-toggle="tab">Follows</a></li>
                </ul>
            </div>
            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="latest_update"><?= display_latest_updates($templateVar['latest_updates'], $templateVar['user']->latest_updates) ?></div>
                <div role="tabpanel" class="tab-pane" id="follows_update">
                    <?php
                    if (validate_level($templateVar['user'], 'member')) {
                        if ($templateVar['array_of_manga_ids']) {
                            print display_latest_updates($templateVar['follows_updates'], $templateVar['user']->latest_updates);
                        }
                        else
                            print display_alert('info m-2', 'Notice', "You haven't followed any manga!");
                    }
                    else
                        print display_alert('info m-2', 'Notice', "Please " . display_fa_icon('sign-in-alt') . " <a href='/login'>login</a> to see updates from your follows.");
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
		
		<!-- Supporters -->
		<?php if ($templateVar['user']->premium || $templateVar['user']->get_chapters_read_count() > MINIMUM_CHAPTERS_READ_FOR_SUPPORT) : ?>
		<div class="card mb-3 border-success">
            <h6 class="card-header text-center text-success border-success border-bottom">MangaDex@Home Update</h6>
            <div class="card-body text-center text-success">
				We are looking for more people to host a MD@H client to improve the <a href="https://mangadex.network/" target="_blank">MangaDex Network</a>! It is now possible to run a client without any knowledge of server management or running a java program. Please <a href="/md_at_home/info">check this out</a> if you're interested.
            </div>
        </div> 
		<?php elseif(!$templateVar['user']->premium && $templateVar['user']->get_chapters_read_count() > MINIMUM_CHAPTERS_READ_FOR_SUPPORT) : ?>
		<?php /*
		<div class="card mb-3">
            <h6 class="card-header text-center">We need your support!</h6>
            <div class="card-body text-center">
				Running MangaDex isn't cheap, but thanks to the generosity of our users, we have managed to cope so far without using any ads. We have recently upgraded to a new webserver, and our monthly costs have increased. Please consider <a href="/support">supporting</a> us! Every little counts.
            </div>
        </div>*/ ?>
		<?php endif; ?>

		<?php /*		
		<div class="card mb-3">
            <h6 class="card-header text-center">Advertisement</h6>

			$folder = read_dir('/images/aprilfools/side');
			$r = rand(2, count($folder)+1);
			$f = explode('.', $folder[$r]);
			print "<a href='/title/{$f[0]}'><img width='100%' src='/images/aprilfools/side/{$folder[$r]}' /></a>";

        </div>
		*/ ?>		

		<!-- Info -->
		<!--
		<div class="card mb-3 border-info">
            <h6 class="card-header text-center text-info border-info border-bottom">Announcement</h6>
            <div class="card-body text-center text-info">
		Emails stopped sending around 12AM UTC today, and was fixed at around 6AM UTC. If you made your account recently, resend the email for your activation code.
            </div>
        </div>
		-->
        <!-- Top chapters -->
        <div class="card mb-3">
            <h6 class="card-header text-center"><?= display_fa_icon('external-link-alt') ?> <a href="/stats/top">Top chapters</a></h6>
            <div class="card-header p-0">
                <ul class="nav nav-pills nav-justified" role="tablist">
                    <li title="Updates every 20 minutes" class="nav-item"><a class="nav-link active" href="#six_hours" aria-controls="six_hours" data-toggle="tab">6h</a></li>
                    <li title="Updates every hour" class="nav-item"><a class="nav-link" href="#day" aria-controls="day" data-toggle="tab">24h</a></li>
                    <li title="Updates every 2 hours" class="nav-item"><a class="nav-link" href="#week" aria-controls="week" data-toggle="tab">7d</a></li>
                </ul>
            </div>
            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="six_hours"><?= display_top_list($templateVar['top_chapters_6h']) ?></div>
                <div role="tabpanel" class="tab-pane" id="day"><?= display_top_list($templateVar['top_chapters_24h']) ?></div>
                <div role="tabpanel" class="tab-pane" id="week"><?= display_top_list($templateVar['top_chapters_7d']) ?></div>
            </div>
        </div>

        <!-- Social media row -->
        <div class="card mb-3">
            <h6 class="card-header text-center">Links</h6>
            <div class="card-body p-0">
                <div class="row ml-0 mr-0 social-media-btns">
                    <div class="col-4 col-sm-2 col-lg-4 col-xl-2"><a title="@Mangadex on Twitter" href="https://twitter.com/MangaDex" target="_blank">Twitter</a></div>
                    <div class="col-4 col-sm-2 col-lg-4 col-xl-2"><a title="/r/mangadex on Reddit" href="https://www.reddit.com/r/mangadex/" target="_blank">Reddit</a></div>
                    <div class="col-4 col-sm-2 col-lg-4 col-xl-2"><a title="Mangadex on Discord" href="https://discord.gg/mangadex" target="_blank">Discord</a></div>
                    <div class="col-4 col-sm-2 col-lg-4 col-xl-2"><a title="Mangadex Scanlation Bloghosting" href="https://mangadex.com/2018/09/17/hello-world/" target="_blank">Wordpress</a></div>
                    <div class="col-4 col-sm-2 col-lg-4 col-xl-2"><a title="mangadex on tumblr" href="https://mangadexofficial.tumblr.com/" target="_blank">Tumblr</a></div>
                </div>
            </div>
        </div>
        <!-- Latest news -->
        <div class="card mb-3 ">
            <h6 class="card-header text-center">News</h6>
            <?= display_latest_posts($templateVar['latest_news_posts']) ?>
        </div>
        <!-- Reading history -->
        <div class="card mb-3">
            <h6 class="card-header text-center"><?= display_fa_icon('external-link-alt') ?> <a href="/history">Reading history</a></h6>
            <?= display_reading_history($templateVar['user']) ?>
        </div>
        <!-- Top manga box -->
        <div class="card mb-3">
            <h6 class="card-header text-center">Top manga</h6>
            <div class="card-header p-0">
                <ul class="nav nav-pills nav-justified" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#top_follows" aria-controls="top_follows" data-toggle="tab">Follows</a></li>
                    <li class="nav-item"><a class="nav-link" href="#top_rating" aria-controls="top_rating" data-toggle="tab">Rating</a></li>
                </ul>
            </div>
            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="top_follows"><?= display_top_list($templateVar['top_follows'], 'top_follows') ?></div>
                <div role="tabpanel" class="tab-pane" id="top_rating"><?= display_top_list($templateVar['top_rating'], 'top_rating') ?></div>
            </div>
        </div>
        <!-- Latest comments -->
        <div class="card mb-3">
            <h6 class="card-header text-center">Latest posts</h6>
            <div class="card-header p-0">
                <ul class="nav nav-pills nav-justified" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#forum_posts" aria-controls="six_hours" data-toggle="tab">Forums</a></li>
                    <li class="nav-item"><a class="nav-link" href="#manga_posts" aria-controls="day" data-toggle="tab">Manga</a></li>
                </ul>
            </div>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="forum_posts"><?= display_latest_posts($templateVar['latest_forum_posts']) ?></div>
                <div role="tabpanel" class="tab-pane" id="manga_posts"><?= display_latest_comments($templateVar['latest_manga_comments'], 'manga') ?></div>
            </div>
        </div>
    </div>
</div>

<?php

if (is_array($templateVar['featured']) && !empty($templateVar['featured']))
    echo display_carousel($templateVar['featured'], 'Featured titles', 'hled_titles');

if (is_array($templateVar['new_manga']) && !empty($templateVar['new_manga']))
    echo display_carousel($templateVar['new_manga'], 'New titles', 'new_titles');

?>
