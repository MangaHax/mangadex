<div class="table-responsive">
    <div class="container">
        <?php if ($templateVar['cache_flushed']) : ?>
            <div class="row m-2">
                <div class="col col-12">
                    <?= display_alert('success', 'Notice', 'Memcached successfully flushed!') ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="row m-2">
            <div class="col col-12">
                <form method="POST">
                    <input type="hidden" name="flush_cache" value="1" />
                    <button type="submit" class="btn btn-success"><?= display_fa_icon('sync') ?> Flush cache</button>
                </form>
            </div>
        </div>
    </div>

    <table class="table table-striped table-hover table-sm">
        <?php foreach ($templateVar['stats'] AS $serverId => $serverStats) : ?>
            <thead>
            <tr class="border-top-0">
                <th colspan="2">Server: <?=$serverId?></th>
            </tr>
            <tr>
                <th>Key</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($serverStats as $key => $val) : ?>
                <tr>
                    <td><?=$key?></td>
                    <td><?=$val?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        <?php endforeach; ?>
    </table>
</div>
