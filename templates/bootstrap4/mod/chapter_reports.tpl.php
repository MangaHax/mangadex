<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
        <tr class="border-top-0">
            <th><?= display_fa_icon('hashtag', 'ID') ?></th>
            <th><?= display_fa_icon('book', 'Manga') ?></th>
            <th><?= display_fa_icon('file', 'Chapter', '', 'far') ?></th>
            <th style="min-width: 100px;"><?= display_fa_icon('clock', 'Time', '', 'far') ?></th>
            <th><?= display_fa_icon('info-circle', 'Reason') ?></th>
            <th><?= display_fa_icon('comment', 'Info', '', 'far') ?></th>
            <th><?= display_fa_icon('user', 'Report user') ?></th>
            <?php if ($templateVar['type'] == 'new') { ?>
                <th width="130px"><?= display_fa_icon('question-circle') ?></th>
            <?php } else { ?>
                <th><?= display_fa_icon('question-circle') ?></th>
                <th><?= display_fa_icon('user-md', 'Mod') ?></th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($templateVar['reports'] as $report_id => $report) {	?>
            <tr>
                <td><?= $report_id ?></td>
                <td><?php if ($report->manga_id) { ?><a target="_blank" href="/title/<?= $report->manga_id ?>"><?= display_fa_icon('book') ?></a><?php } ?></td>
                <td><a target="_blank" href="/chapter/<?= $report->report_chapter_id ?>"><?= $report->report_chapter_id ?></a> <a target="_blank" href="/chapter/<?= $report->report_chapter_id ?>/edit"><?= display_fa_icon('pencil-alt') ?></a></td>
                <td><?= get_time_ago($report->report_timestamp) ?></td>
                <td><?= $templateVar['report_type_array'][$report->report_type] ?? '[Unknown report type]' ?></td>
                <td><?= $report->report_info ?></td>
                <td><?= display_user_link($report->report_user_id, $report->reported_name, $report->reported_level_colour) ?></td>
                <?php if ($templateVar['type'] == "new") { ?>
                    <td>
                        <button class="btn btn-warning btn-sm report_accept_all" id="<?= $report->report_chapter_id ?>"><?= display_fa_icon('check-double') ?></button>
						<button class="btn btn-success btn-sm report_accept" id="<?= $report_id ?>"><?= display_fa_icon('check') ?></button>
                        <button class="btn btn-danger btn-sm report_reject" id="<?= $report_id ?>"><?= display_fa_icon('times') ?></button>
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
