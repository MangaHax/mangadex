<?php
	$paging = pagination($templateVar['post_count'], $templateVar['current_page'], $templateVar['limit']);
?>

<?= $templateVar['breadcrumbs'] ?>

<div class="row toggle mb-3">
    <div class="col">
        <?= display_sticky_thread($templateVar['user'], $templateVar['thread']) ?>
        <?= display_lock_thread($templateVar['user'], $templateVar['thread']) ?>
        <?= display_edit_thread($templateVar['user']) ?>
    </div>

    <div class="col">
        <?= display_pagination_forum($paging, $templateVar['page'], $templateVar['thread']->thread_id, slugify($templateVar['thread']->thread_name)); ?>
    </div>
</div>

<?php $thread_author_id = -1; ?>
<table class="table table-md edit">
    <thead>
    <tr>
        <th class="d-none d-md-table-cell"><?= display_fa_icon('user') ?> Author</th>
        <th><?php
            // TODO: Change hardcoded forum ids
            switch ($templateVar['thread']->forum_id) {
                case 11:
                    print display_fa_icon('book') . " <a href='/title/{$templateVar['thread']->manga_id}/" . slugify($templateVar['thread']->thread_name) . "'>{$templateVar['thread']->thread_name}</a>";
                    break;

                case 12:
                    print display_fa_icon('file', '', '', 'far') . " <a href='/chapter/{$templateVar['thread']->chapter_id}/'>{$templateVar['thread']->thread_name}</a>";
                    break;

                case 14:
                    print display_fa_icon('users') . " <a href='/group/{$templateVar['thread']->group_id}/" . slugify($templateVar['thread']->thread_name) . "'>{$templateVar['thread']->thread_name}</a>";
                    break;

                default:
                    print thread_label($templateVar['thread']->thread_name);
                    $thread_author_id = $templateVar['thread']->thread_author_id;
                    break;
            }
            ?></th>
    </tr>

	<?php if (isset($templateVar['thread']->poll_expire_timestamp) && $templateVar['thread']->poll_expire_timestamp) : ?>
	<tr>
		<td colspan="2" class="p-3">
			<form method="post" id="vote_form">
				<?php foreach ($templateVar['poll_items'] as $item_id => $item) :
					$percentage = $templateVar['total_votes'] > 0 ? round($item['vote_count'] / $templateVar['total_votes'] * 100, 1) : 0; ?>
					<div class="form-check">
						<input class="form-check-input" type="radio" name="poll_item_id" id="poll_item_<?= $item_id ?>" value="<?= $item_id ?>" <?= $item_id == $templateVar['user_vote'] ? 'checked' : ''?> <?= ($templateVar['user_vote'] || !$templateVar['user']->user_id) ? 'disabled' : ''?>>
						<label class="form-check-label" for="poll_item_<?= $item_id ?>"><?= $item['item_name'] ?> <?= $templateVar['user_vote'] ? "({$item['vote_count']} votes, $percentage%)" : '' ?></label>
					</div>
					<?php if ($templateVar['user_vote']) : ?>
						<div class="progress">
							<div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
				<div class="mt-3">
					<button title="<?= !$templateVar['user']->user_id ? 'Log in to vote' : ''?>" type="submit" class="btn btn-secondary" id="vote_button" <?= ($templateVar['user_vote'] || !$templateVar['user']->user_id) ? 'disabled' : ''?>><?= display_fa_icon('pencil-alt') ?> Vote</button>
				</div>
			</form>
		</td>
	</tr>
	<?php endif; ?>

    </thead>
    <tbody>
    <?php
    foreach ($templateVar['post_list'] as $post_id => $post) {

        $post->is_thread_starter = $post->user_id == $thread_author_id;
        $templateVar['parser']->parse($post->text);

        // Block post if this posts author userid is in the block list and if the user is not a staff member. (staff sees all posts)
        if (isset($templateVar['blocked_user_ids'][$post->user_id]) && !validate_level($templateVar['user'], 'pr')) {
            print display_post_blocked($post, $templateVar['user']);
        } else {
            print display_post_v2($post, $templateVar['parser']->getAsHtml(), $templateVar['user'], $templateVar['page']);
            print display_edit_post_v2($post, $templateVar['user']);
        }
    }
    ?>
    <tr id="last_post"></tr>
    </tbody>
</table>

<?php if (validate_level($templateVar['user'], 'pr')) { ?>
    <div class="edit display-none card mb-3">
        <h6 class="card-header">Post reply</h6>
        <div class="card-body">
            <form method="post" id="edit_thread_form">
                <div class="form-group row">
                    <label for="thread_name" class="col-md-3 col-form-label">Thread name:</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="thread_name" name="thread_name" value="<?= $templateVar['thread']->thread_name ?>" required>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-info" id="edit_thread_button"><?= display_fa_icon('save') ?> Save</button>
                </div>
            </form>
        </div>
    </div>
<?php } ?>

<?php if (validate_level($templateVar['user'], 'member')) { ?>

    <div id="post_reply" class="toggle display-none card mb-3">
        <h6 class="card-header">Post reply</h6>
        <div class="card-body">
            <form method="post" id="post_reply_form">
                <?= display_bbcode_textarea() ?>
                <div class="row justify-content-between">
                    <div class="col-auto order-2">
                        <button class="btn btn-secondary" title="Submit" type="submit" id="post_reply_button"><?= display_fa_icon('pencil-alt') ?> Submit</button>
                    </div>
                    <div class="col-auto order-1">
                        <button class="btn btn-secondary" title="Hide" type="button" id="back_button"><?= display_fa_icon('undo') ?> Back</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php } ?>

<div class="row toggle edit mb-3">
    <div class="col">
        <?= display_post_reply($templateVar['user'], $templateVar['thread']) ?>
    </div>

    <div class="col">
        <?= display_pagination_forum($paging, $templateVar['page'], $templateVar['thread']->thread_id, slugify($templateVar['thread']->thread_name)); ?>
    </div>
</div>

<?= $templateVar['post_history_modal_html'] ?>

<?= $templateVar['breadcrumbs'] ?>
