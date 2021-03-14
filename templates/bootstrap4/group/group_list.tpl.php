<?php

    $paging = pagination($templateVar['group_count'], $templateVar['current_page'], $templateVar['limit'], $templateVar['sort']);

?>
<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
        <tr class="border-top-0">
            <th width="30px" class="text-center"><?= display_fa_icon('globe', 'Language') ?></th>
            <th style="min-width: 200px;"><?= display_fa_icon('users', 'Group name') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], 'group_name', 'alpha') ?></th>
            <th><?= display_fa_icon('user', 'Group leader') ?></th>
            <th style="min-width: 80px;" class="text-center"><?= display_fa_icon('calendar-alt', 'Last active') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], 'group_last_updated', 'numeric') ?></th>
            <th class="text-info text-center"><?= display_fa_icon('eye', 'Total views') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], 'group_views', 'numeric') ?></th>
            <th style="min-width: 55px;" class="text-success text-center"><?= display_fa_icon('bookmark', 'Total follows') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], "group_follows", "numeric") ?></th>
            <th style="min-width: 50px;" class="text-center"><?= display_fa_icon('comments', 'Comments') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], 'group_comments', 'numeric') ?></th>
            <th style="min-width: 55px;" class="text-center"><?= display_fa_icon('thumbs-up', 'Likes', '', 'far') . display_sort($templateVar['page'], $templateVar['search'], $templateVar['sort'], 'group_likes', 'numeric') ?></th>
            <th width="30px" class="text-center"><?= display_fa_icon('lock', 'Upload restriction') ?></th>
            <th width="30px" class="text-center"><?= display_fa_icon('external-link-alt', 'Website') ?></th>
            <th width="30px" class="text-center"><?= display_fa_icon('discord', 'Discord', '', 'fab') ?></th>
            <th width="30px" class="text-center"><?= display_fa_icon('hashtag', 'IRC') ?></th>
            <th width="30px" class="text-center"><?= display_fa_icon('rss', 'RSS') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($templateVar['groups'] as $group) { ?>
            <tr>
                <td class="text-center"><?= display_lang_flag_v3($group) ?></td>
                <td><?= display_group_link_v2($group) ?></td>
                <td><?= ($group->group_leader_id > 1) ? display_user_link($group->group_leader_id, $group->username, $group->level_colour) : "" ?></td>
                <td class="text-center"><?= get_time_ago($group->group_last_updated) ?></td>
                <td class="text-info text-center"><?= number_format($group->group_views) ?></td>
                <td class="text-success text-center"><?= number_format($group->group_follows) ?></td>
                <td class="text-center"><?= display_count_comments($group->thread_posts, 'group', $group->group_id, $group->group_name) ?></td>
                <td class="text-center"><?= ($group->group_likes) ? "<span class='badge badge-success'>+" . number_format($group->group_likes) . "</span>" : "" ?></td>
                <td class="text-center"><?= ($group->group_control) ? display_fa_icon('lock', 'Group members only', 'text-warning') : display_fa_icon('lock-open', 'No restrictions', 'text-success') ?></td>
                <td class="text-center"><?php if ($group->group_website) { ?>
                        <a target="_blank" href="<?= $group->group_website ?>"><?= display_fa_icon('external-link-square-alt', 'Website', 'fa-lg') ?></a>
                    <?php } else { ?>
                        <?= display_fa_icon('external-link-square-alt', 'Website', 'fa-lg') ?>
                    <?php } ?></td>
                <td class="text-center"><?php if ($group->group_discord) { ?>
                        <a target="_blank" href="https://discord.gg/<?= $group->group_discord ?>"><?= display_fa_icon('discord', 'Discord', 'fa-lg', 'fab') ?></a>
                    <?php } else { ?>
                        <?= display_fa_icon('discord', 'Discord', 'fa-lg', 'fab') ?>
                    <?php } ?></td>
                <td class="text-center"><?php if ($group->group_irc_channel) { ?>
                        <a target="_blank" href="<?= "irc://$group->group_irc_server/$group->group_irc_channel" ?>"><?= display_fa_icon('hashtag', 'IRC', 'fa-lg') ?></a>
                    <?php } else { ?>
                        <?= display_fa_icon('hashtag', 'IRC', 'fa-lg') ?>
                    <?php } ?></td>
                <td class="text-center"><?= display_rss_link($templateVar['user'], 'group_mini', $group->group_id) ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php
    if ($templateVar['page'] == "groups")
        print display_pagination_v2($paging, "groups", $templateVar['page'], $templateVar['search']['group_name']);
