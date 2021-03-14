<?php
$timestamp = time();
?>
<table class="table table-md table-striped">
    <thead>
    <tr class="border-top-0">
        <th width="20px"><?= display_fa_icon('clock', '', '', 'far') ?></th>
        <th><?= display_fa_icon('user') ?></th>
        <th class="text-right"><?= display_fa_icon('question-circle') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($templateVar['pending'] as $friend) { ?>
        <tr>
            <td><?= ($friend['last_seen_timestamp'] > $timestamp - 60) ? display_fa_icon('circle', 'Online', 'text-success') : display_fa_icon('circle', 'Offline', 'text-danger') ?></td>
            <td><?= display_user_link_v2($friend) ?></td>
            <td class="text-right">
                <button type="button" class="friend_accept_button btn btn-success btn-sm" id="<?= $friend['user_id'] ?>"><?= display_fa_icon('user-plus') ?> Accept</button>
                <button type="button" class="friend_remove_button btn btn-danger btn-sm" id="<?= $friend['user_id'] ?>"><?= display_fa_icon('user-minus') ?> Reject</button>
            </td>
        </tr>
    <?php } ?>

    <?php foreach ($templateVar['friends'] as $friend) {
        $mdListButtonEnabled = $friend['list_privacy'] === 1 || $friend['list_privacy'] === 2 // We already know hes a friend, so just check for the numbers
            || validate_level($templateVar['user'], 'admin'); // Admins can see private lists
        ?>
        <tr>
            <td><?= ($friend['last_seen_timestamp'] > $timestamp - 60) ? display_fa_icon('circle', 'Online', 'text-success') : display_fa_icon('circle', 'Offline', 'text-danger') ?></td>
            <td>
                <?= display_user_link_v2($friend) ?>
            </td>
            <td class="text-right">
                <a class="btn btn-sm btn-secondary" role="button" href="/messages/send/<?= $friend['username'] ?>"><span class="fas fa-envelope fa-fw " aria-hidden="true" title="Send message"></span><span class="d-none d-xl-inline"> Send message</span></a>

                <?php if ($mdListButtonEnabled) : ?>
                    <a href="/list/<?= $friend['user_id'] ?>" role="button" class="btn btn-sm btn-secondary"><img height="16px" src="/images/misc/navbar.svg?1"><span class="d-none d-xl-inline"> MDList</span></a>
                <?php else : ?>
                    <button role="button" class="btn btn-sm btn-secondary" title="This user hasn't set their list to public" disabled><img height="16px" src="/images/misc/navbar.svg?1"><span class="d-none d-xl-inline"> MDList</span></button>
                <?php endif; ?>

                <?php if ($friend['accepted']) { ?>
                    <button type='button' class='friend_remove_button btn btn-warning btn-sm' id='<?= $friend['user_id'] ?>'><?= display_fa_icon('user-minus') ?><span class='d-none d-md-inline'> Remove friend</span></button>
                <?php } else { ?>
                    <button type='button' class='btn btn-success btn-sm' disabled title='Waiting for user to accept your friend request'><?= display_fa_icon('user-clock') ?><span class='d-none d-md-inline'> Pending...</span></button>
                    <button type="button" class="friend_remove_button btn btn-danger btn-sm" id="<?= $friend['user_id'] ?>"><?= display_fa_icon('user-minus') ?> Cancel</button>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>