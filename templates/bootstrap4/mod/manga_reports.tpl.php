<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
        <tr class="border-top-0">
            <th><?= display_fa_icon('hashtag', 'ID') ?></th>
            <th><?= display_fa_icon('book', 'Manga') ?></th>
            <th><?= display_fa_icon('clock', 'Time', '', 'far') ?></th>
            <th><?= display_fa_icon('comment', 'Info', '', 'far') ?></th>
            <th><?= display_fa_icon('user', 'Report user') ?></th>
            <?php if ($templateVar['type'] == 'new') { ?>
                <th width="110px"><?= display_fa_icon('question-circle') ?></th>
            <?php } else { ?>
                <th><?= display_fa_icon('question-circle') ?></th>
                <th><?= display_fa_icon('user-md', 'Mod') ?></th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($templateVar['reports'] as $report) {	?>
            <tr>
                <td><?= $report->report_id ?></td>
                <td><?php if ($report->report_manga_id) { ?><a target="_blank" href="/title/<?= $report->report_manga_id ?>"><?= $report->report_manga_id ?></a><?php } ?></td>
                <td><?= get_time_ago($report->report_timestamp) ?></td>
                <td><?= $report->report_info ?></td>
                <td><?= display_user_link($report->report_user_id, $report->reported_name, $report->reported_level_colour) ?></td>
                <?php if ($templateVar['type'] == "new") { ?>
                    <td>
                        <button class="btn btn-success btn-sm report_accept" id="<?= $report->report_id ?>"><?= display_fa_icon('check') ?></button>
                        <button class="btn btn-danger btn-sm report_reject" id="<?= $report->report_id ?>"><?= display_fa_icon('times') ?></button>
                    </td>
                <?php } else { ?>
                    <td><?= ($report->report_conclusion == 1) ? display_fa_icon('check', '', 'text-success') : display_fa_icon('times', '', 'text-danger') ?></td>
                    <td><?= display_user_link($report->report_mod_user_id, $report->actioned_name, $report->actioned_level_colour) ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>