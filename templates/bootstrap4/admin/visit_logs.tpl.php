<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
        <tr class="border-top-0">
            <th>User</th>
            <th>IP address</th>
            <th>Time</th>
            <th>Page</th>
            <th>Referer</th>
            <th>H</th>
            <th>User agent</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($templateVar['visit_logs'] as $log) { ?>
            <tr>
                <td><?= $log->username ?></td>
                <td><?= $log->visit_ip ?></td>
                <td><?= get_time_ago($log->visit_timestamp) ?></td>
                <td><?= $log->visit_page ?></td>
                <td><?= $log->visit_referrer ?></td>
                <td><?= $log->visit_h_toggle ?></td>
                <td><?= $log->visit_user_agent ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>