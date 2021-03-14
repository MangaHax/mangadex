<?php

/** Template vars:
 * group: the group object
 */

$id = $templateVar['group']->group_id;

$user_id_follows_array = $templateVar['group']->get_follows_user_id();

$group_members_display = $templateVar['group']->get_members_display();

$group_delay_array = [0 => "None", 1800 => "30 mins", 3600 => "1 hour", 7200 => "2 hours", 10800 => "3 hours", 14400 => "4 hours", 18000 => "5 hours",
    21600 => "6 hours", 60*60*7 => '7 hours', 60*60*8 => '8 hours', 60*60*9 => '9 hours', 60*60*10 => '10 hours', 60*60*11 => '11 hours', 43200 => "12 hours", 86400 => "1 day", 172800 => "2 days", 259200 => "3 days", 345600 => "4 days", 432000 => "5 days",
    518400 => "6 days", 604800 => "1 week", 1209600 => "2 weeks"];

?>
<div class="card mb-3">
    <h6 class="card-header d-flex align-items-center py-2">
        <?= display_fa_icon('users') ?>
        <span class="mx-1"><?= $templateVar['group']->group_name ?></span>
        <?= display_lang_flag_v3($templateVar['group']) ?>
        <?= ($templateVar['group']->group_likes) ? "<span class='badge badge-success ml-1'>+" . number_format($templateVar['group']->group_likes) . "</span>" : '' ?>
        <?= display_rss_link($templateVar['user'], 'group_id', $id) ?>
    </h6>
    <?= display_group_banner($templateVar['group'], $templateVar['theme_id']) ?>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3 edit">
            <h6 class="card-header"><?= display_fa_icon('info-circle') ?> Group info</h6>
            <table class="table table-sm ">
				<tr>
                    <th width="150px">Group ID:</th>
                    <td><?= display_fa_icon('hashtag') ?> <?= $templateVar['group']->group_id ?></td>
                </tr>
                <tr>
                    <th>Alt name:</th>
                    <td><span><?= ($templateVar['group']->group_alt_name) ? display_fa_icon('users') . ' ' . $templateVar['group']->group_alt_name : 'None' ?></span></td>
                </tr>
                <?php if ($templateVar['group']->group_is_inactive) : ?>
                <tr>
                    <th>Status:</th>
                    <td><span title="This group is considered to be inactive (see Rule 2.4)" style="color:orangered;">Inactive</span></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Stats:</th>
                    <td>
                        <ul class="list-inline table-list-inline">
                            <li class="list-inline-item text-info"><?= display_fa_icon('eye') ?> <?= number_format($templateVar['group']->group_views) ?></li>
                            <li class="list-inline-item text-success"><?= display_fa_icon('bookmark', 'Follows') ?> <?= number_format($templateVar['group']->group_follows) ?></li>
                            <li class="list-inline-item"><?= display_fa_icon('file', 'Total chapters', '', 'far') ?> <?= number_format($templateVar['group']->count_chapters) ?></li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>Links:</th>
                    <td><?php if ($templateVar['group']->group_website) { ?>
                            <a target="_blank" href="<?= $templateVar['group']->group_website ?>"><?= display_fa_icon('external-link-square-alt', 'Website', 'fa-lg') ?></a>
                        <?php } else { ?>
                            <?= display_fa_icon('external-link-square-alt', 'Website', 'fa-lg') ?>
                        <?php }

                        if ($templateVar['group']->group_discord) { ?>
                            <a target="_blank" href="https://discord.gg/<?= $templateVar['group']->group_discord ?>"><?= display_fa_icon('discord', 'Discord', 'fa-lg', 'fab') ?></a>
                        <?php } else { ?>
                            <?= display_fa_icon('discord', 'Discord', 'fa-lg', 'fab') ?>
                        <?php }

                        if ($templateVar['group']->group_irc_channel) { ?>
                            <a target="_blank" href="<?= 'irc://' . $templateVar['group']->group_irc_server . '/' . $templateVar['group']->group_irc_channel ?>">
                                <?= display_fa_icon('hashtag', 'IRC', 'fa-lg') ?>
                            </a>
                        <?php } else { ?>
                            <?= display_fa_icon('hashtag', 'IRC', 'fa-lg') ?>
                        <?php }

                        if ($templateVar['group']->group_email) { ?>
                            <a target="_blank" href="mailto:<?= $templateVar['group']->group_email ?>"><?= display_fa_icon('envelope', 'Email', 'fa-lg') ?></a>
                        <?php } else { ?>
                            <?= display_fa_icon('envelope', 'Email', 'fa-lg') ?>
                        <?php } ?></td>
                </tr>
                <tr>
                    <th>Actions:</th>
                    <td>
                        <?= display_like_button($templateVar['user']->user_id, $templateVar['ip'], $templateVar['group']->get_likes_user_id_ip_list()) ?>
                        <?= display_follow_group_button($templateVar['user'], $user_id_follows_array) ?>
                        <?= display_block_group_button($templateVar['user'], $templateVar['blocked_user_ids_array']) ?>
                        <?= display_edit_group($templateVar['user'], $templateVar['group'], $templateVar['group_members_array']) ?>
                        <?php if (validate_level($templateVar['user'], 'member') && isset($templateVar['group_members_array'][$templateVar['user']->user_id])) : ?>
                            <button data-id="<?=$templateVar['group']->group_id?>" class='btn btn-danger' id='leave_button'><?= display_fa_icon('door-open', 'Leave', 'fas') ?> <span class='d-none d-xl-inline'>Leave</span></button>
                        <?php endif; ?>
                        <?= display_delete_group($templateVar['user']) ?>
                    </td>
                </tr>
            </table>
        </div>

        <?php if (validate_level($templateVar['user'], 'gmod') || $templateVar['group']->group_leader_id == $templateVar['user']->user_id || in_array($templateVar['user']->username, $templateVar['group_members_array'])) { //only display for relevant people ?>
            <div class="card mb-3 edit display-none">
                <h6 class="card-header"><?= display_fa_icon('info-circle') ?> Edit group info</h6>
                <form id="group_edit_form" method="post">
                    <table class="table table-sm ">
                        <tr>
                            <th width="150px">Banner:</th>
                            <td>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Preferred width of 1400px. Max 1MB" disabled name="old_file">
                                    <span class="input-group-append">
										<span class="btn btn-secondary btn-file">
											<?= display_fa_icon('folder-open', 'Browse', 'far') ?> <span class="span-1280">Browse</span> <input type="file" name="file" id="file" accept=".<?= IMPLODE(',.', ALLOWED_IMG_EXT) ?>">
										</span>
									</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Language:</th>
                            <td>
                                <select class="form-control selectpicker" id="lang_id" name="lang_id" data-size="10">
                                    <?= display_languages_select([$templateVar['group']->group_lang_id]) ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Founded:</th>
                            <td><input type="date" class="form-control" id="group_founded" name="group_founded" value="<?= $templateVar['group']->group_founded ?>" required></td>
                        </tr>
                        <tr>
                            <th>Website:</th>
                            <td><input type="text" class="form-control" id="url_link" name="url_link" value="<?= $templateVar['group']->group_website ?>" placeholder="http:// or https:// required"></td>
                        </tr>
                        <tr>
                            <th>IRC Channel:</th>
                            <td><input type="text" class="form-control" id="irc_channel" name="irc_channel" value="<?= $templateVar['group']->group_irc_channel ?>" placeholder="# not required"></td>
                        </tr>
                        <tr>
                            <th>IRC Server:</th>
                            <td><input type="text" class="form-control" id="irc_server" name="irc_server" value="<?= $templateVar['group']->group_irc_server ?>" placeholder="irc.rizon.net"></td>
                        </tr>
                        <tr>
                            <th>Discord:</th>
                            <td><input type="text" class="form-control" id="discord" name="discord" value="<?= $templateVar['group']->group_discord ?>" placeholder="No need for https://discord.gg/"></td>
                        </tr>
                        <tr>
                            <th>Contact:</th>
                            <td><input type="text" class="form-control" id="group_email" name="group_email" value="<?= $templateVar['group']->group_email ?>" placeholder="x@x.x"></td>
                        </tr>
                        <tr>
                            <th>Upload restriction:</th>
                            <td>
                                <div class="custom-control custom-checkbox form-check">
                                    <input type="checkbox" class="custom-control-input" id="group_control" name="group_control" value="1" <?= $templateVar['group']->group_control ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="group_control">&nbsp;</label>
                                </div>
                            </td>
                        </tr>
                        <?php if (validate_level($templateVar['user'], 'gmod')) : ?>
                        <tr>
                            <th style="color:orangered">Group is Inactive:</th>
                            <td>
                                <div class="custom-control custom-checkbox form-check">
                                    <input type="checkbox" class="custom-control-input" id="group_is_inactive" name="group_is_inactive" value="1" <?= $templateVar['group']->group_is_inactive ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="group_is_inactive">&nbsp;</label>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Group delay:</th>
                            <td>
                                <select class="form-control selectpicker" id="group_delay" name="group_delay" data-size="10">
                                    <?php
                                    foreach ($group_delay_array as $seconds => $time) {
                                        $selected = ($seconds == $templateVar['group']->group_delay) ? 'selected' : '';
                                        if ($seconds < 1209600 || $seconds == 1209600 && $id == 56)
                                            print "<option $selected value='$seconds'>$time</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td><textarea rows="10" type="text" class="form-control" id="group_description" name="group_description" placeholder="BBCode allowed"><?= $templateVar['group']->group_description ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Actions:</th>
                            <td>
                                <button type="submit" class="btn btn-success" id="group_edit_button"><?= display_fa_icon('pencil-alt') ?> Save</button>
                                <button class="btn btn-warning" id="cancel_edit_button"><?= display_fa_icon('times') ?> Cancel</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        <?php } ?>

    </div>

    <div class="col-md-6">
        <div class="card mb-3 edit-members">
            <h6 class="card-header"><?= display_fa_icon('user') ?> Member info</h6>
            <table class="table table-sm ">
                <?php if ($templateVar['group']->group_leader_id > 1) { ?>
                    <tr>
                        <th>Leader:</th>
                        <td><?= display_fa_icon('user') ?> <?= display_user_link($templateVar['group']->user_id, $templateVar['group']->username, $templateVar['group']->level_colour) ?></td>
                    </tr>
                    <tr>
                        <th>Members:</th>
                        <td><?= display_group_members_list($group_members_display); ?></td>
                    </tr>
                <?php } ?>

                <tr>
                    <th width="150px">Upload restriction:</th>
                    <td><?= ($templateVar['group']->group_control) ?
                            display_fa_icon('lock', 'Group members only', 'text-warning') . " <span class='badge badge-warning'>Group members only</span>" :
                            display_fa_icon('lock-open', 'No restrictions', 'text-success') . " <span class='badge badge-success'>None</span>" ?></td>
                </tr>

                <tr>
                    <th>Group delay:</th>
                    <td><?= ($templateVar['group']->group_delay ? "<span class='badge badge-warning'>" . ($group_delay_array[$templateVar['group']->group_delay] ?? ($templateVar['group']->group_delay . " Seconds")) . "</span>" : "<span class='badge badge-success'>None</span>") ?></td>
                </tr>

                <?php if (validate_level($templateVar['user'], 'gmod') || $templateVar['group']->group_leader_id == $templateVar['user']->user_id) { //only display for relevant people ?>
                    <tr>
                        <th>Actions:</th>
                        <td><span>
							<?= display_edit_group_members($templateVar['user'], $templateVar['group']) ?>
						</span></td>
                    </tr>
                <?php } ?>
                
                <?php if($templateVar["user_is_invited"]){ ?>
                    <tr>
                        <th>Invite:</th>
                        <td><span>
							<?= display_accept_group_invite() ?>
                            <?= display_reject_group_invite() ?>
						</span></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <?php if (validate_level($templateVar['user'], 'gmod') || $templateVar['group']->group_leader_id == $templateVar['user']->user_id) { //only display for relevant people ?>
            <div class="card mb-3 edit-members display-none">
                <h6 class="card-header"><?= display_fa_icon('user') ?> Edit members</h6>
                <form id="edit_group_members_form" method="post">
                    <table class="table table-sm ">
                        <tr>
                            <th>Members:</th>
                            <td><?= display_delete_group_members_list($templateVar['group_members_array']); ?></td>
                        </tr>
                        <tr>
                            <th>Add member:</th>
                            <td><input type="number" class="form-control" id="add_member_user_id" name="add_member_user_id" placeholder="user_id" required ></td>
                        </tr>
                        <tr>
                            <th>Actions:</th>
                            <td>
                                <button type="submit" class="btn btn-success" id="save_edit_members_button"><?= display_fa_icon('pencil-alt') ?> Save</button>
                                <button class="btn btn-warning" id="cancel_edit_members_button"><?= display_fa_icon('times') ?> Cancel</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        <?php } ?>

        <?php if ($templateVar['group']->group_leader_id == 1)
            print display_alert('warning', 'Notice', "The group leader may request to take over this internally generated group by posting in this <a href='/thread/240274'>thread</a>.") ?>
    </div>
</div>

<?php if ($templateVar['group']->group_description) { ?>
    <div class="card mb-3">
        <h6 class="card-header"><?= display_fa_icon('info-circle') ?> Description</h6>
        <div class="card-body">
            <?= nl2br($templateVar['parser']->parse($templateVar['group']->group_description)->getAsHtml()); ?>
        </div>
    </div>
<?php } ?>

<!-- Nav tabs -->
<ul class="edit nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'chapters') ? 'active' : '' ?>" href="/group/<?= $id ?>/<?= slugify($templateVar['group']->group_name) ?>/chapters/"><?= display_fa_icon('file', 'Chapters', '', 'far') ?> <span class="d-none d-md-inline">Chapters</span></a>
    </li>

    <?php if ($templateVar['mode'] == 'manga') { ?>
        <li class="nav-item dropdown">
            <a class="nav-link <?= ($templateVar['mode'] == 'manga') ? 'active' : '' ?> dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('book', 'Manga') ?> <span class="d-none d-md-inline">Manga</span> <span class="caret"></span></a>
            <ul class="dropdown-menu">
                <a href="#" class="dropdown-item title_mode <?= (!$templateVar['title_mode']) ? 'active' : '' ?>" id="0"><?= display_fa_icon('th-large') ?> Detailed</a>
                <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 1) ? 'active' : '' ?>" id="1"><?= display_fa_icon('th-list') ?> Expanded list</a>
                <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 2) ? 'active' : '' ?>" id="2"><?= display_fa_icon('bars') ?> Simple list</a>
                <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 3) ? 'active' : '' ?>" id="3"><?= display_fa_icon('th') ?> Grid</a>
            </ul>
        </li>
    <?php } else { ?>
        <li class="nav-item">
            <a class="nav-link" href="/group/<?= $id ?>/<?= slugify($templateVar['group']->group_name) ?>/manga/"><?= display_fa_icon('book', 'Manga') ?> <span class="d-none d-md-inline">Manga</span></a>
        </li>
    <?php } ?>

    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'comments') ? 'active' : '' ?>" href="/group/<?= $id ?>/<?= slugify($templateVar['group']->group_name) ?>/comments/"><?= display_fa_icon('comments', 'Comments', '', 'far') ?> <span class="d-none d-md-inline">Comments</span> <?= display_count_comments($templateVar['group']->thread_posts) ?></a>
    </li>

    <?php if (validate_level($templateVar['user'], 'pr')) { ?>
        <li class="nav-item ml-auto">
            <a class="nav-link text-success <?= ($templateVar['mode'] == 'deleted') ? 'active' : '' ?>" href="/group/<?= $id ?>/<?= slugify($templateVar['group']->group_name) ?>/deleted/"><?= display_fa_icon('trash', 'Bin') ?> <span class="d-none d-md-inline">Bin</span></a>
        </li>
    <?php } ?>

    <?php if (validate_level($templateVar['user'], 'gmod')) { ?>
        <li class="nav-item">
            <a class="nav-link text-success <?= ($templateVar['mode'] == 'mod_chapters') ? 'active' : '' ?>" href="/group/<?= $id ?>/<?= slugify($templateVar['group']->group_name) ?>/mod_chapters/"><?= display_fa_icon('edit') ?> <span class="d-none d-md-inline">Mod</span></a>
        </li>
		
        <li class="nav-item">
            <a class="nav-link  text-danger <?= ($templateVar['mode'] == 'admin') ? 'active' : '' ?>" href="/group/<?= $id ?>/<?= slugify($templateVar['group']->group_name) ?>/admin/"><?= display_fa_icon('user-md', 'Admin') ?> <span class="d-none d-md-inline">Admin</span></a>
        </li>
    <?php } ?>
</ul>

<!-- Tab panes -->
<div class="edit tab-content">

    <?= $templateVar['group_tab_html'] ?>

</div>

<?= $templateVar['post_history_modal_html'] ?>