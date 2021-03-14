<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
        <tr class="border-top-0">
            <th>Date/Time</th>
            <th>Total views</th>
            <th>(Guests)</th>
            <th>(Logged in)</th>
            <th>Average (per minute)</th>
            <th>Total users</th>
            <th>(Guests)</th>
            <th>(Logged in)</th>
            <th>Average (per minute)</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($templateVar['results'] as $log) { ?>
            <tr>
                <td><?= date(DATETIME_FORMAT, $log['timestamp']) ?></td>
                <td><?= number_format($log['views_guests'] + $log['views_logged_in']) ?></td>
                <td><?= number_format($log['views_guests']) ?></td>
                <td><?= number_format($log['views_logged_in']) ?></td>
                <td><?= number_format(($log['views_guests'] + $log['views_logged_in']) / 60) ?></td>
                <td><?= number_format($log['users_guests'] + $log['users_logged_in']) ?></td>
                <td><?= number_format($log['users_guests']) ?></td>
                <td><?= number_format($log['users_logged_in']) ?></td>
                <td><?= number_format(($log['users_guests'] + $log['users_logged_in']) / 60) ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>