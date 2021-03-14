<?php
$paging = pagination($templateVar['user_count'], $templateVar['current_page'], $templateVar['limit'], $templateVar['sort']);
?>
<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
        <tr>
            <th width="30px" class="text-center"><?= display_fa_icon('globe', 'Language') ?></th>
            <th><?= display_fa_icon('user', 'User') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], 'username', 'alpha') ?></th>
            <th style="min-width: 130px;"><?= display_fa_icon('graduation-cap', 'Role') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], 'level', 'numeric') ?></th>
            <th class="text-center"><?= display_fa_icon('calendar-alt', 'Joined') ?></th>
            <th style="min-width: 80px;" class="text-center"><?= display_fa_icon('clock', 'Last seen', '', 'far') ?></th>
            <th class="text-center"><?= display_fa_icon('file', 'Total chapters', '', 'far') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], 'uploads', 'numeric') ?></th>
            <th class="text-info text-center" ><?= display_fa_icon('eye', 'Total views') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], 'views', 'numeric') ?></th>
            <th width="30px" class="text-center"><?= display_fa_icon('external-link-alt', 'Website') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($templateVar['user_list'] as $view_user) { ?>
            <tr>
                <td class="text-center"><?= display_lang_flag_v3($view_user) ?></td>
                <td><?= display_user_link_v2($view_user) ?></td>
                <td><?= $view_user->level_name ?></td>
                <td class="text-center"><?= date(DATE_FORMAT, $view_user->joined_timestamp) ?></td>
                <td class="text-center"><?= get_time_ago($view_user->last_seen_timestamp, true, 60) ?></td>
                <td class="text-center"><?= number_format($view_user->user_uploads) ?></td>
                <td class="text-info text-center"><?= number_format($view_user->user_views) ?></td>
                <td class="text-center"><a rel="nofollow" target="_blank" href="<?= ($view_user->user_website) ?: "/user/$view_user->user_id/" . strtolower($view_user->username) ?>"><?= display_fa_icon('external-link-square-alt', 'Website', 'fa-lg') ?></a></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?= ($templateVar['show_pagination'] ?? true) ? display_pagination_v2($paging, 'users', $templateVar['page'], $templateVar['search']['username']) : ''; ?>