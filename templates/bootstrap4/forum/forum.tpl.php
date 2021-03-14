<?php
$paging = pagination($templateVar['thread_count'], $templateVar['current_page'], $templateVar['limit']);
?>

<?= $templateVar['breadcrumb'] ?>

<div class="toggle">
    <?php if ($templateVar['subforum_count'] > 0) : ?>
        <div class="card mb-3">
            <h6 class="card-header">Child Boards</h6>
            <table class="table m-0">
                <?php
                foreach ($templateVar['subforum_list'] as $subforum) {
                    print display_forum($subforum, $templateVar['user']);
                }
                ?>
            </table>
        </div>
    <?php endif ?>

    <?php if (!in_array($templateVar['forum_id'], CATEGORY_FORUM_IDS)) { ?>

        <?= (!in_array($templateVar['forum_id'], AUTO_FORUM_IDS) && validate_level($templateVar['user'], 'pr')) ? "<form id='delete_threads_form'>" : '' ?>

        <div class="row mb-3" >
            <div class="col">
                <?= display_new_thread($templateVar['user'], $templateVar['threads']) ?>
            </div>

            <div class="col">
                <?= display_pagination_forum($paging, $templateVar['page'], $templateVar['forum_id'], slugify($templateVar['threads']->forum_name)); ?>
            </div>
        </div>

        <?php if ($templateVar['thread_count'] > 0) { ?>

            <div class="table-responsive">
                <table class="table table-md table-striped">
                    <thead>
                    <tr>
                        <?= (!in_array($templateVar['forum_id'], AUTO_FORUM_IDS) && validate_level($templateVar['user'], 'pr')) ? "<th width='20px'></th>" : '' ?>
                        <th><?php
                            // TODO: Change hardcoded forum ids
                            switch ($templateVar['forum_id']) {
                                case 11:
                                    print display_fa_icon('book') . ' Manga';
                                    break;

                                case 12:
                                    print display_fa_icon('file', '', '', 'far') . ' Chapter';
                                    break;

                                case 14:
                                    print display_fa_icon('users') . ' Groups';
                                    break;

                                default:
                                    print 'Thread';
                                    break;
                            }
                            ?></th>
                        <?= (!in_array($templateVar['forum_id'], AUTO_FORUM_IDS) ? '<th>' . display_fa_icon('user', 'Started by') . '</th>' : '') ?>
                        <?= ($templateVar['forum_id'] == 12) ? '<th>' . display_fa_icon('book', 'Manga') . '</th>': '' ?>
                        <th class="text-center"><?= display_fa_icon('comments', 'Posts') ?></th>
                        <th class="text-center"><?= display_fa_icon('eye', 'Views') ?></th>
                        <th class="text-right">Last post</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($templateVar['thread_list'] as $thread) { ?>
                        <tr>
                            <?= (!in_array($templateVar['forum_id'], AUTO_FORUM_IDS) && validate_level($templateVar['user'], 'pr')) ? "<td class='text-center'><div class='custom-control custom-checkbox'><input id='thread_$thread->thread_id' name='thread_id[]' type='checkbox' class='custom-control-input' value='$thread->thread_id'><label class='custom-control-label' for='thread_$thread->thread_id'>&nbsp;</label></div></td>" : '' ?>
                            <td><?= display_thread_labels($thread) ?>
                                <?php
                                switch ($templateVar['forum_id']) {
                                    case 11:
                                        print "<a href='/title/$thread->manga_id/" . slugify($thread->manga_name) . "'>" . display_fa_icon('book') . "</a> <a href='/thread/$thread->thread_id'> $thread->manga_name </a>";
                                        break;

                                    case 12:
                                        print "<a href='/chapter/$thread->chapter_id/'>" . display_fa_icon('file', '', '', 'far') . "</a> <a href='/thread/$thread->thread_id'>Chapter $thread->chapter</a>";
                                        break;

                                    case 14:
                                        print "<a href='/group/$thread->group_id/" . slugify($thread->group_name) . "'>" . display_fa_icon('users') . "</a> <a href='/thread/$thread->thread_id'> $thread->group_name </a>";
                                        break;

                                    default:
                                        print "<a href='/thread/$thread->thread_id'>" . ($thread->poll_expire_timestamp ? 'Poll: ' : '') . thread_label($thread->thread_name) . "</a>";
                                        break;
                                }
                                ?>
                            </td>
                            <?= (!in_array($templateVar['forum_id'], AUTO_FORUM_IDS) ? '<td>' . display_user_link($thread->user_id, $thread->started_username, $thread->started_level_colour, $thread->started_show_premium_badge, $thread->started_show_md_at_home_badge) . '</td>' : '') ?>
                            <?= ($templateVar['forum_id'] == 12) ? "<td><a href='/title/$thread->manga_id/" . slugify($thread->manga_name) . "'>$thread->manga_name</a></td>": '' ?>
                            <td class="text-center"><?= $thread->thread_posts ?></td>
                            <td class="text-center"><?= number_format($thread->thread_views) ?></td>
                            <td class="text-right"><?php if ($thread->last_post_timestamp) { ?><a href="/thread/<?= "$thread->thread_id/$thread->thread_page/#post_$thread->last_post_id" ?>"><?= get_time_ago($thread->last_post_timestamp) ?></a>
                                    by <?= display_user_link($thread->last_post_user_id, $thread->last_username, $thread->last_level_colour, $thread->last_show_premium_badge, $thread->last_show_md_at_home_badge) ?><?php } ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        else print "<div class='alert alert-info text-center' role='alert'>" . display_fa_icon('info-circle', 'fas') . " There are no threads in this forum.</div>";
        ?>

        <div class="row mb-3">
            <div class="col">
                <?= !in_array($templateVar['forum_id'], AUTO_FORUM_IDS) ? display_delete_threads($templateVar['user']) : '' ?>
            </div>

            <div class="col">
                <?= display_pagination_forum($paging, $templateVar['page'], $templateVar['forum_id'], slugify($templateVar['threads']->forum_name)); ?>
            </div>
        </div>

        <?= (!in_array($templateVar['forum_id'], AUTO_FORUM_IDS) && validate_level($templateVar['user'], 'pr')) ? "</form>" : "" ?>

    <?php } ?>

</div>

<?php if (validate_level($templateVar['user'], 'member')) { ?>

    <div class="toggle display-none card mb-3">
        <h6 class="card-header">Start new thread</h6>
        <div class="card-body">
            <form method="post" id="start_thread_form">
                <div class="form-group row">
                    <div class="col">
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Thread name" required>
                    </div>
                </div>
                <?= display_bbcode_textarea() ?>
				<div class="form-group row display-none poll-div">
                    <div class="col">
                        <textarea rows="5" type="text" class="form-control" id="poll_items" name="poll_items" placeholder="Enter each poll item on a new line. Select poll duration below."></textarea>
                    </div>
                </div>
				<div class="form-group row display-none poll-div">
					<div class="col">
						<select class="form-control selectpicker" id="poll_days" name="poll_days" data-size="7">
							<?php for($i = 1; $i <= 28; $i++) : ?>
							<option value="<?= $i ?>"><?= $i ?> day<?= $i > 1 ? 's' : '' ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>
                <div class="row justify-content-between">
                    <div class="col-auto order-3">
                        <button class="btn btn-secondary" title="Submit thread" type="submit" id="start_thread_button"><?= display_fa_icon('pencil-alt') ?> Submit</button>
                    </div>
                    <div class="col-auto order-2">
                        <button class="btn btn-secondary" title="Back to forum" type="button" id="poll_button"><?= display_fa_icon('poll-h') ?> Poll</button>
                    </div>
                    <div class="col-auto order-1">
                        <button class="btn btn-secondary" title="Submit poll" type="button" id="back_button"><?= display_fa_icon('undo') ?> Back</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php } ?>