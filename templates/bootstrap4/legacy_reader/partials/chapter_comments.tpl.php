<div class="card mb-3">
    <h6 class="card-header"><?= display_fa_icon('book') ?> <?= display_manga_link_v2($templateVar['manga'],'',false,false) ?> <?= display_lang_flag_v3($templateVar['manga']) ?></h6>
</div>

<p class="text-center"><?= display_post_comment_v3($templateVar['user'], $templateVar['chapter'], 3, $templateVar['chapter']->chapter_id) ?></p>

<?php if ($templateVar['chapter']->thread_id) : ?>
    <div class="card mb-3">
        <h6 class="card-header text-truncate"><?= display_fa_icon('file', '', '', 'far') ?> <?= display_chapter_title($templateVar['chapter']) ?></h6>
        <div class="card-body">
            <form method="post" id="post_reply_form">
                <?= display_bbcode_textarea() ?>
                <div class="row justify-content-between">
                    <div class="col-auto order-2">
                        <button type="submit" class="btn btn-secondary" id="post_reply_button"><?= display_fa_icon('comment', '', '', 'far') ?> Comment</button>
                    </div>
                    <div class="col-auto order-1">
                        <a role="button" href="/chapter/<?= $templateVar['chapter']->chapter_id ?>" title="Back to chapter" class="btn btn-secondary"><?= display_fa_icon('undo') ?> Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($templateVar['chapter']->thread_posts) : ?>
    <table class="table table-striped">
        <?php
        foreach ($templateVar['chapter']->get_comments() as $comment_id => $comment) {
            $templateVar['parser']->parse($comment->text);

            // Block post if this posts author userid is in the block list and if the user is not a staff member. (staff sees all posts)
            if (isset($templateVar['blocked_user_ids'][$comment->user_id]) && !validate_level($templateVar['user'], 'pr')) {
                print display_post_blocked($comment, $templateVar['user']);
            } else {
                print display_post_v2($comment, $templateVar['parser']->getAsHtml(), $templateVar['user'], $templateVar['page']);
                print display_edit_post_v2($comment, $templateVar['user']);
            }
        }
        ?>
    </table>

    <?php if ($templateVar['chapter']->thread_posts > 20) : ?>
        <div class="text-center"><a role="button" class="btn btn-secondary" href="/thread/<?= $templateVar['chapter']->thread_id ?>"><?= display_fa_icon('university') ?> Read older comments</a></div>
    <?php endif; ?>
<?php endif; ?>

<?= $templateVar['post_history_modal_html'] ?>