<?php
/** Template vars:
 * user: the currently logged in user
 * manga: the manga object
 * page: the current page name, http://mangadex.org/manga/... => 'manga'
 * parser: the bbcode parser object
*/

?>

<p class="text-center"><?= display_post_comment_v3($templateVar['user'], $templateVar['manga'], 1, $templateVar['manga']->manga_id) ?></p>

<?php if ($templateVar['manga']->thread_id) : ?>
    <div id="post_reply" class="card my-3">
        <h6 class="card-header">Post comment</h6>
        <div class="card-body">
            <form method="post" id="post_reply_form">
                <?= display_bbcode_textarea() ?>
                <div class="text-center">
                    <button type="submit" class="btn btn-secondary"
                            id="post_reply_button"><?= display_fa_icon('comment', '', '', 'far') ?> Comment
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if ($templateVar['manga']->thread_posts) : ?>

    <table class="table table-striped table-md">
        <?php
        foreach ($templateVar['manga']->get_comments() as $comment) {
            $templateVar['parser']->parse($comment->text);

            // Block post if this posts author userid is in the block list and if the user is not a staff member. (staff sees all posts)
            if (isset($templateVar['blockedUserIds'][$comment->user_id]) && !validate_level($templateVar['user'], 'pr')) {
                print display_post_blocked($comment, $templateVar['user']);
            } else {
                print display_post_v2($comment, $templateVar['parser']->getAsHtml(), $templateVar['user'], $templateVar['page']);
                print display_edit_post_v2($comment, $templateVar['user']);
            }
        }
        ?>
    </table>

    <?php if ($templateVar['manga']->thread_posts > 20) : ?>
        <div class="text-center">
            <a role="button" class="btn btn-secondary" href="/thread/<?= $templateVar['manga']->thread_id ?>"><?= display_fa_icon('university') ?>Read older comments</a>
        </div>
    <?php endif; ?>

<?php endif; ?>