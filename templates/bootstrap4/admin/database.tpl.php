<?php if (!empty($templateVar['errors'])): ?>
<div class="container">
    <?php foreach ($templateVar['errors'] AS $dsn => $error) : ?>
    <div class="row mt-2">
        <div class="col">
            <div class="alert alert-warning"><strong><?= $dsn ?></strong>: <?= $error ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <?php foreach ($templateVar['stats'] ?? [] AS $dsn => $serverStats) : ?>
            <?php if (empty($serverStats) || empty($serverStats[0] ?? [])) continue; ?>
            <thead>
            <tr class="border-top-0">
                <th colspan="2">Server: <?=$dsn?></th>
            </tr>
            <tr>
                <th>Key</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($serverStats[0] as $key => $val) : ?>
                <?php if (!in_array($key, ['Relay_Log_File', 'Relay_Log_Space', 'Last_Error', 'Last_IO_Error', 'Last_SQL_Error', 'Slave_SQL_Running_State', 'Slave_IO_State', 'Slave_IO_Running', 'Seconds_Behind_Master'])) continue; ?>
                <tr>
                    <td><?=$key?></td>
                    <td><?=$val?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        <?php endforeach; ?>
    </table>
</div>
