<form id="msg_del_form" method="post">
    <table class="table table-striped table-md ">
        <thead>
            <tr class="border-top-0">
                <th width="40px"></th>
                <th><?= display_fa_icon('user') ?> User</th>
                <th width="40px"></th>
                <th>Title</th>
                <th width="130px"><?= display_fa_icon('calendar-alt') ?> Last sent</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templateVar['threads'] as $thread) { ?>
                <tr <?= (($thread['recipient_id'] == $templateVar['user']->user_id && !$thread['recipient_read']) || ($thread['sender_id'] == $templateVar['user']->user_id && !$thread['sender_read'])) ? "style='font-weight: bold; '" : "" ?>>
                    <td class="text-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="msg_<?= $thread['thread_id'] ?>" name="msg_ids[]" value="<?= $thread['thread_id'] ?>">
                            <label class="custom-control-label" for="msg_<?= $thread['thread_id'] ?>">&nbsp;</label>
                        </div>
                    </td>
                    <td><?= ($thread['recipient_id'] == $templateVar['user']->user_id) ?
                            display_user_link($thread['sender_id'], $thread['sender_username'], $thread['sender_level_colour']) :
                            display_user_link($thread['recipient_id'], $thread['recipient_username'], $thread['recipient_level_colour']) ?></td>
                    <td><?= (($thread['recipient_id'] == $templateVar['user']->user_id && $thread['recipient_read']) || $thread['recipient_id'] != $templateVar['user']->user_id) ? "" : display_fa_icon('envelope', 'Unread', 'text-danger') ?></td>
                    <td><a href="/message/<?= $thread['thread_id'] ?>"><?= !empty($thread['thread_subject']) ? $thread['thread_subject'] : '(No Subject)' ?></a></td>
                    <td title="<?= date(DATETIME_FORMAT, $thread['thread_timestamp']) ?>"><?= get_time_ago($thread['thread_timestamp']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
        <?php if (!$templateVar['deleted']) { ?>
            <tfoot>
                <tr>
                    <th></th>
                    <th><button type="submit" class="btn btn-danger" id="msg_del_button"><?= display_fa_icon('trash') ?> Delete </button></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        <?php } ?>
    </table>
</form>

<?php
$paging = pagination($templateVar['thread_count'], $templateVar['current_page'], $templateVar['limit']);
$paging['sort'] = $templateVar['mode'];
echo display_pagination_v2($paging, 'threads', 'messages');
?>