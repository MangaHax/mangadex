<div class="container">
    <div class="row mt-2">
        <div class="col-6">

        </div>
        <div class="col-6 text-right">
            <div class="dropdown type-filter">
                <label>Filter State: </label>
                <button data-state="<?= $templateVar['filter_state'] ?>" class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="<?= $templateVar['filter_state'] != 0 ? 'd-none ' : '' ?>report-state-icon report-state-icon-0"><i style="color:dimgray" class="fa fa-question"></i></span>
                    <span class="<?= $templateVar['filter_state'] != 1 ? 'd-none ' : '' ?>report-state-icon report-state-icon-1"><i style="color:greenyellow" class="fa fa-check"></i></span>
                    <span class="<?= $templateVar['filter_state'] != 2 ? 'd-none ' : '' ?>report-state-icon report-state-icon-2"><i style="color:orangered" class="fa fa-times"></i></span>
                </button>
                <div class="dropdown-menu">
                    <a data-setstate="0" class="dropdown-item filter-state-btn" href="#"><i style="color:dimgray" class="fa fa-question"></i> Unset</a>
                    <a data-setstate="1" class="dropdown-item filter-state-btn" href="#"><i style="color:greenyellow" class="fa fa-check"></i> Accept</a>
                    <a data-setstate="2" class="dropdown-item filter-state-btn" href="#"><i style="color:orangered" class="fa fa-times"></i> Decline</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="table-responsive mt-2">
            <table class="table table-striped table-hover table-sm">
                <thead>
                <tr class="border-top-0">
                    <th><?= display_fa_icon('hashtag', 'ID') ?></th>
                    <th><?= display_fa_icon('book', 'Item') ?></th>
                    <th><?= display_fa_icon('clock', 'Time', '', 'far') ?></th>
                    <th><?= display_fa_icon('gavel', 'Reason') ?></th>
                    <th><?= display_fa_icon('comment', 'Info', '', 'far') ?></th>
                    <th><?= display_fa_icon('user', 'Report user') ?></th>
                    <th><?= display_fa_icon('clock', 'Time', '', 'far') ?></th>
                    <th><?= display_fa_icon('user-md', 'Mod') ?></th>
                    <th><?= display_fa_icon('question-circle') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($templateVar['reports'] as $report) : ?>
                    <?php
                        $reason = $templateVar['report_reasons'][$report['reason_id']] ?? null;
                    ?>
                    <tr data-id="<?= $report['id'] ?>" data-type-id="<?= $report['type_id'] ?>">
                        <td>#<?= $report['id'] ?></td>
                        <td>
                            <?php
                                switch ($report['type_id']) {
                                    // manga
                                    case 1:
                                        printf('<a href="/title/%1$d/">%1$d</a>', $report['item_id']);
                                        break;

                                    // chapter
                                    case 2:
                                        printf('<a href="/chapter/%1$d/">%1$d</a>', $report['item_id']);
                                        break;

                                    // comment
                                    case 3:
                                        printf('<a href="/comment/%1$d/">%1$d</a>', $report['item_id']);
                                        break;
                                }
                            ?>
                        </td>
                        <td><?= get_time_ago($report['created']) ?></td>
                        <td><?= $reason ? $reason['text'].($reason['is_info_required'] ? ' *' : '') : 'Unknown' ?></td>
                        <td><?php if ($report['info']) : ?><pre class="code-box mb-0"><?= $report['info'] ?></pre><?php endif; ?></td>
                        <td><?= display_user_link($report['user_id'], $report['username'], $report['level_colour']) ?></td>
                        <td><?= isset($report['updated']) ? get_time_ago($report['updated']) : '-' ?></td>
                        <td><?= isset($report['mod_id']) ? display_user_link($report['mod_id'], $report['mod_username'], $report['mod_level_colour']) : '-' ?></td>
                        <td class="text-right">
                            <div class="dropdown">
                                <button data-state="<?= $report['state'] ?>" class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="<?= $report['state'] != 0 ? 'd-none ' : '' ?>report-state-icon report-state-icon-0"><i style="color:dimgray" class="fa fa-question"></i></span>
                                    <span class="<?= $report['state'] != 1 ? 'd-none ' : '' ?>report-state-icon report-state-icon-1"><i style="color:greenyellow" class="fa fa-check"></i></span>
                                    <span class="<?= $report['state'] != 2 ? 'd-none ' : '' ?>report-state-icon report-state-icon-2"><i style="color:orangered" class="fa fa-times"></i></span>
                                </button>
                                <div class="dropdown-menu">
                                    <a data-id="<?= $report['id'] ?>" data-setstate="0" class="dropdown-item report-setstate-btn" href="#"><i style="color:dimgray" class="fa fa-question"></i> Unset</a>
                                    <a data-id="<?= $report['id'] ?>" data-setstate="1" class="dropdown-item report-setstate-btn" href="#"><i style="color:greenyellow" class="fa fa-check"></i> Accept</a>
                                    <a data-id="<?= $report['id'] ?>" data-setstate="2" class="dropdown-item report-setstate-btn" href="#"><i style="color:orangered" class="fa fa-times"></i> Decline</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                    <tr>
                        <td colspan="9">
                            <div class="container-fluid">
                                <div class="row">
                                    <!--<div class="col-6 text-right"><button class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Back</button></div>
                                    <div class="col-6 col-md-5"><button class="btn btn-sm btn-secondary">Next <i class="fa fa-arrow-right"></i></button></div>-->
                                    <div class="col-12 text-right"><button class="btn btn-sm btn-secondary report-refresh-btn"><i class="fa fa-sync"></i></button></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>