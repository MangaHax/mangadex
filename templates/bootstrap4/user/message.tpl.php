<?php
$n = 0;
$messages_html = "";
foreach ($templateVar['messages'] as $msg_id => $msg) {
    $templateVar['parser']->parse($msg->text);
    $msg->post_id = $msg_id;

    $messages_html .= display_post_v2($msg, $templateVar['parser']->getAsHtml(), $templateVar['user'], $templateVar['page']);
    $n++;
}
?>

<!-- load more btn <?= $templateVar['thread']->total ?> -->
<?php if ($n < $templateVar['thread']->total) : ?>
    <div class="container mb-3" id="msg_more_container">
        <div class="row">
            <div class="col text-center">
                <button id="msg_more_button" type="button" class="btn btn-secondary" data-thread-id="<?= $templateVar['thread']->thread_id ?>"><?= display_fa_icon('sync', '', '', 'fas') ?> Load older Messages</button>
            </div>
        </div>
    </div>
<?php endif; ?>

<table class="table table-striped table-md ">
    <thead>
    <tr>
        <th class="text-right d-none d-md-table-cell"></th>
        <th>Subject: <?= !empty($templateVar['thread']->thread_subject) ? $templateVar['thread']->thread_subject : '(No Subject)' ?></th>
    </tr>
    </thead>
    <tbody id="msg_container">
        <?= $messages_html ?>
        <tr id="last_post"></tr>
    </tbody>
</table>

<div class="card mb-3">
    <h6 class="card-header">Reply</h6>
    <div class="card-body">
        <form id="msg_reply_form" name="msg_reply_form">
            <?= display_bbcode_textarea() ?>
            <div class="text-center">
                <button id="msg_reply_button" type="submit" class="btn btn-secondary"><?= display_fa_icon('comment', '', '', 'far') ?> Reply</button>
            </div>
        </form>
    </div>
</div>