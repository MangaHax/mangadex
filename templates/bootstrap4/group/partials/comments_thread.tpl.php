<?php
/** Template vars:
 * user: the currently logged in user
 * group: the group object
 * page: the current page name, http://mangadex.org/manga/... => 'manga'
 * parser: the bbcode parser object
 */

?>

<p class="mt-3 text-center"><?= display_post_comment_v3($templateVar['user'], $templateVar['group'], 2, $templateVar['group']->group_id) ?></p>

<?php if ($templateVar['group']->thread_id) { ?>
    <div id="post_reply" class="card my-3">
        <h6 class="card-header">Post comment</h6>
        <div class="card-body">
            <form method="post" id="post_reply_form">
                <?= display_bbcode_textarea() ?>
                <div class="text-center">
                    <button type="submit" class="btn btn-secondary" id="post_reply_button"><?= display_fa_icon('comment', 'Comment', '', 'far') ?> Comment</button>
                </div>
            </form>
        </div>
    </div>
<?php } ?>

<?php if ($templateVar['group']->thread_posts) : ?>

    <table class="table table-striped table-md">
        <?php
        foreach ($templateVar['group']->get_comments() as $comment_id => $comment) {

            // Block post if this posts author userid is in the block list and if the user is not a staff member. (staff sees all posts)
            if (isset($templateVar['blockedUserIds'][$comment->user_id]) && !validate_level($templateVar['user'], 'pr')) {
                print display_post_blocked($comment, $templateVar['user']);
            } else {
                print display_post_v2($comment, $templateVar['parser']->parse($comment->text)->getAsHtml(), $templateVar['user'], $templateVar['page']);
                print display_edit_post_v2($comment, $templateVar['user']);
            }
        }
        ?>
    </table>

<?php endif ?>