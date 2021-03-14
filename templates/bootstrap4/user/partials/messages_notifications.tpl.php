<table class="table table-striped table-md">
    <thead>
    <tr class="border-top-0">
        <th><?= display_fa_icon('exclamation-circle', 'Notification') ?> Notification</th>
        <th><?= display_fa_icon('clock', 'Time', '', 'far') ?> Time</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($templateVar['notifications'] as $notification) { ?>
        <tr>
            <td>You have been mentioned by <?= display_fa_icon('user', 'User') ?> <?= display_user_link_v2((object)$notification) ?> in <?= display_post_link((object)$notification) ?>.</td>
            <td><?= get_time_ago($notification['timestamp']) ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>