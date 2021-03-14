<div class="card mb-3">
    <h6 class="card-header d-flex align-items-center py-2">
        <?= display_fa_icon('user') ?>
        <span class="mx-1"><?= $templateVar['uploader']->username ?></span>
		<?= ($templateVar['uploader']->premium && $templateVar['uploader']->show_premium_badge) ? "<a href='/support'>" . display_fa_icon('gem', 'Supporter', '', 'far fa-fw mr-1') . "</a>" : '' ?>
		<?= $templateVar['uploader']->show_md_at_home_badge ? "<a href='/md_at_home'>" . display_fa_icon('network-wired', 'MD@H Host', '', 'fas mr-1 fa-fw' . ($templateVar['uploader']->show_md_at_home_badge == 2 ? ' text-warning' : '')) . "</a>" : '' ?>
        <?= display_lang_flag_v3($templateVar['uploader']) ?>
        <?= display_rss_link($templateVar['user'], 'user_id', $templateVar['uploader']->user_id) ?>
    </h6>
    <div class="card-body p-0">
        <div class="row edit">
            <div class="col-xl-3 col-lg-4 col-md-5"><?= display_avatar($templateVar['uploader']->avatar, $templateVar['uploader']->user_id, 0) ?></div>
            <div class="col-xl-9 col-lg-8 col-md-7">
				<div class="row m-0 py-1 px-0">
                    <div class="col-lg-3 col-xl-2 strong">User ID:</div>
                    <div class="col-lg-9 col-xl-10"><?= display_fa_icon('hashtag') ?> <?= $templateVar['uploader']->user_id ?></div>
                </div>
                <div class="row m-0 py-1 px-0 border-top">
                    <div class="col-lg-3 col-xl-2 strong">User level:</div>
                    <div class="col-lg-9 col-xl-10"><?= display_fa_icon('graduation-cap') ?> <span style="color: #<?= $templateVar['uploader']->level_colour ?>; "><?= $templateVar['uploader']->level_name ?></span></div>
                </div>
                <div class="row m-0 py-1 px-0 border-top">
                    <div class="col-lg-3 col-xl-2 strong">Joined:</div>
                    <div class="col-lg-9 col-xl-10"><?= display_fa_icon('calendar-alt') ?> <?= date('Y-m-d', $templateVar['uploader']->joined_timestamp) ?></div>
                </div>
                <div class="row m-0 py-1 px-0 border-top">
                    <div class="col-lg-3 col-xl-2 strong">Last online:</div>
                    <div class="col-lg-9 col-xl-10"><?= display_fa_icon('clock', '', '', 'far') ?> <?= get_time_ago($templateVar['uploader']->last_seen_timestamp, true, 60) ?></div>
                </div>
                <?php if ($templateVar['uploader']->user_website) :
                    $isInternalDomain = strpos($templateVar['uploader']->user_website, URL) === 0;
                    ?>
                    <div class="row m-0 py-1 px-0 border-top">
                        <div class="col-lg-3 col-xl-2 strong">Website:</div>
                        <div class="col-lg-9 col-xl-10"><?php if (!$isInternalDomain) echo display_fa_icon('external-link-alt', '', '', 'fas').' ' ?><a href="<?= $templateVar['uploader']->user_website ?>" <?php if (!$isInternalDomain) echo 'rel="nofollow"' ?> target="<?= $isInternalDomain ? '_self' : '_blank' ?>"><?= $templateVar['uploader']->user_website ?></a></div>
                    </div>
                <?php endif; ?>
                <div class="row m-0 py-1 px-0 border-top">
                    <div class="col-lg-3 col-xl-2 strong">Group(s):</div>
                    <div class="col-lg-9 col-xl-10"><?= display_user_groups_list($templateVar['uploader']->get_groups()) ?></div>
                </div>
                <div class="row m-0 py-1 px-0 border-top">
                    <div class="col-lg-3 col-xl-2 strong">Stats:</div>
                    <div class="col-lg-9 col-xl-10">
                        <ul class="list-inline m-0">
                            <li class="list-inline-item text-info"><?= display_fa_icon('eye', 'Views') ?> <?= number_format($templateVar['uploader']->user_views) ?></li>
                            <li class="list-inline-item"><?= display_fa_icon('file', 'Chapters uploaded', '', 'far') ?> <?= number_format($templateVar['uploader']->user_uploads) ?></li>
                        </ul>
                    </div>
                </div>
                <?php if($templateVar['uploader']->user_bio) { ?>
                    <div class="row m-0 py-1 px-0 border-top">
                        <div class="col-lg-3 col-xl-2 strong">Biography:</div>
                        <div class="col-lg-9 col-xl-10"><?= nl2br($templateVar['parser']->getAsHtml()) ?></div>
                    </div>
                <?php } ?>
                <div class="row m-0 py-1 px-0 border-top">
                    <div class="col-lg-3 col-xl-2 strong">Actions:</div>
                    <div class="col-lg-9 col-xl-10">
                        <?php if ($templateVar['mdListButtonEnabled']) : ?>
                            <a href="/list/<?= $templateVar['uploader']->user_id ?>" role="button" class="btn btn-secondary"><img height="16px" src="/images/misc/navbar.svg?1"> MDList</a>
                        <?php else : ?>
                            <button role="button" class="btn btn-secondary" title="This user hasn't set their list to public" disabled><img height="16px" src="/images/misc/navbar.svg?1"> MDList</button>
                        <?php endif; ?>
                        <?= display_send_message($templateVar['user'], $templateVar['uploader']) ?>
                        <?= display_add_friend($templateVar['user'], $templateVar['uploader']) ?>
                        <?= display_block_user($templateVar['user'], $templateVar['uploader']) ?>

                        <?php if ($templateVar['user']->premium >= 1): ?>
                            <button id="notes-button" role="button" class="btn btn-secondary" title="Modify notes">
                                <?= display_fa_icon('sticky-note', 'Notes') ?> <span class='d-none d-md-inline'>Notes</span>
                            </button>

                            <div id="notes-modal" class="modal" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">User Notes</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group row">
                                                <div class="col">
                                                    <input type="text" class="form-control" name="notes" id="note-field" maxlength="50"
                                                           value="<?= array_key_exists($templateVar['uploader']->user_id, $templateVar['user']->notes) ? $templateVar['user']->notes[$templateVar['uploader']->user_id]['note'] : '' ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button id="notes-save" type="button" class="btn btn-primary">Save changes</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (validate_level($templateVar['user'], 'mod')) : ?>
                    <!-- Restriction Controls -->
                    <div class="row m-0 py-1 px-0 border-top">
                        <div class="col-lg-3 col-xl-2 strong">Moderation:</div>
                        <div class="col-lg-9 col-xl-10">
							<?php if ($templateVar['uploader']->get_chapters_read_count() < 101) :  ?>
								<button class="btn btn-info" id="override_button" type="button"><?= display_fa_icon('eye', 'Override') ?><span class='d-none d-md-inline'> Override</span></button>
							<?php endif; ?>
							
                            <?php if (!validate_level($templateVar['uploader'], 'pr')) : ?>
                                <button class="btn btn-warning" id="restrict_user_button" type="button" data-toggle="modal" data-target="#user_restriction_modal"><?= display_fa_icon('hand-paper', 'Restrict') ?><span class='d-none d-md-inline'> Add Restriction</span></button>
                                <button class="btn btn-warning" id="nuke_user_button" type="button" data-post-action="mod_nuke_user_comments" data-post-id="<?= $templateVar['uploader']->user_id ?>"><?= display_fa_icon('bomb', 'Nuke') ?><span class='d-none d-md-inline'> Nuke All Comments</span></button>
                            <?php endif; ?>
                            <?php if (!validate_level($templateVar['user'], 'admin')){ ?>
                                <a class="btn btn-danger" href="/mod/user_tracking?username=<?=$templateVar['uploader']->username?>"><?= display_fa_icon('shoe-prints', 'Track IP', 'fas') ?> Track</a>
                            <?php } ?>
                        </div>
                    </div>
                    <!-- Restriction list -->
                    <?php if (!empty($templateVar['user_restrictions']) || !empty($templateVar['past_restrictions'])) : ?>
                        <div data-id="<?= $templateVar['uploader']->user_id ?>" class="row m-0 py-1 px-0 border-top user-restriction">
                            <?php if (!empty($templateVar['user_restrictions'])) : ?>
                                <div class="col-lg-3 col-xl-2 strong">Active Restrictions:</div>
                                <div class="col-lg-9 col-xl-10">
                                    <table class="table table-sm mt-2">
                                        <thead>
                                        <tr>
                                            <th scope="col">Type (Hover for comments)</th>
                                            <th scope="col">Mod</th>
                                            <th scope="col">Expires</th>
                                            <th scope="col"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($templateVar['user_restrictions'] AS $user_restriction) : ?>
                                            <tr>
                                                <td><a style="cursor:pointer" title="<?= $user_restriction['comment'] ?>"><?= $templateVar['restriction_types'][$user_restriction['restriction_type_id']] ?? '???' ?></a></td>
                                                <td><?= display_user_link_v2((object)['level_colour' => $user_restriction['mod_level_colour'], 'user_id' => $user_restriction['mod_user_id'], 'username' => $user_restriction['mod_username']]) ?></td>
                                                <td><time datetime="<?= gmdate(DATETIME_FORMAT, $user_restriction['expiration_timestamp']) ?>"><?= $user_restriction['expiration_timestamp'] < 4294967295 ? get_time_ago($user_restriction['expiration_timestamp']) : '<span style="color:red">Permanent</span>' ?></time></td>
                                                <td><button data-id="<?= $user_restriction['restriction_id'] ?>" role="button" class="btn btn-danger btn-sm remove-restriction"><?= display_fa_icon('times', 'Lift Restriction', '', 'fas') ?></button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($templateVar['past_restrictions'])) : ?>
                                <div class="col-lg-3 col-xl-2 strong">Past Restrictions:</div>
                                <div class="col-lg-9 col-xl-10">
                                    <button class="btn btn-dark btn-sm" type="button" id="btn-show-past-restrictions">Show Past User Restrictions</button>
                                    <table class="table table-sm mt-2 past-user-restrictions" style="display:none">
                                        <thead>
                                        <tr>
                                            <th scope="col">Type (Hover for comments)</th>
                                            <th scope="col">Mod</th>
                                            <th scope="col">Expired</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($templateVar['past_restrictions'] AS $user_restriction) : ?>
                                            <tr>
                                                <td><a style="cursor:pointer" title="<?= $user_restriction['comment'] ?>"><?= $templateVar['restriction_types'][$user_restriction['restriction_type_id']] ?? '???' ?></a></td>
                                                <td><?= display_user_link_v2((object)['level_colour' => $user_restriction['mod_level_colour'], 'user_id' => $user_restriction['mod_user_id'], 'username' => $user_restriction['mod_username']]) ?></td>
                                                <td><time datetime="<?= gmdate(DATETIME_FORMAT, $user_restriction['expiration_timestamp']) ?>"><?= $user_restriction['expiration_timestamp'] < 4294967295 ? get_time_ago($user_restriction['expiration_timestamp']) : '<span style="color:red">Permanent</span>' ?></time></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if(validate_level($templateVar['user'], 'admin')) { ?>
                    <div class="row m-0 py-1 px-0 border-top">
                        <div class="col-lg-3 col-xl-2 strong">Admin:</div>
                        <div class="col-lg-9 col-xl-10">
                            <?= display_ban_user($templateVar['user'], $templateVar['uploader']) ?>
                            <a class="btn btn-danger" href="/admin/ip_tracking?creation_ip=<?=$templateVar['uploader']->creation_ip?>&last_ip=<?=$templateVar['uploader']->last_ip?>"><?= display_fa_icon('shoe-prints', 'Track IP', 'fas') ?> Track</a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Nav tabs -->
<ul class="edit nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'chapters') ? 'active' : '' ?>" href="/user/<?= $templateVar['uploader']->user_id ?>/<?= slugify($templateVar['uploader']->username) ?>/chapters/"><?= display_fa_icon('file', 'Chapters', '', 'far') ?> <span class="d-none d-md-inline">Chapters</span></a>
    </li>

    <?php if ($templateVar['mode'] == 'manga') { ?>
        <li class="nav-item dropdown">
            <a class="nav-link  <?= ($templateVar['mode'] == 'manga') ? 'active' : '' ?> dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('book', 'Manga') ?> <span class="d-none d-md-inline">Manga</span></a>
            <ul class="dropdown-menu">
                <a href="#" class="dropdown-item title_mode <?= (!$templateVar['title_mode']) ? 'active' : '' ?>" id="0"><?= display_fa_icon('th-large') ?> Detailed</a>
                <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 1) ? 'active' : '' ?>" id="1"><?= display_fa_icon('th-list') ?> Expanded list</a>
                <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 2) ? 'active' : '' ?>" id="2"><?= display_fa_icon('bars') ?> Simple list</a>
                <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 3) ? 'active' : '' ?>" id="3"><?= display_fa_icon('th') ?> Grid</a>
            </ul>
        </li>
    <?php } else { ?>
        <li class="nav-item">
            <a class="nav-link" href="/user/<?= $templateVar['uploader']->user_id ?>/<?= slugify($templateVar['uploader']->username) ?>/manga/"><?= display_fa_icon('book', 'Manga') ?> <span class="d-none d-md-inline">Manga</span></a>
        </li>
    <?php } ?>

	<li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'badges') ? 'active' : '' ?>" href="/user/<?= $templateVar['uploader']->user_id ?>/<?= slugify($templateVar['uploader']->username) ?>/badges/"><?= display_fa_icon('trophy', 'Badges', '', 'fas') ?> <span class="d-none d-md-inline">Badges</span></a>
    </li>
	
    <?php if (validate_level($templateVar['user'], 'pr')) { ?>
        <li class="nav-item ml-auto">
            <a class="nav-link text-success <?= ($templateVar['mode'] == 'comments') ? 'active' : '' ?>" href="/user/<?= $templateVar['uploader']->user_id ?>/<?= slugify($templateVar['uploader']->username) ?>/comments/"><?= display_fa_icon('comments', 'Comments') ?> <span class="d-none d-md-inline">Comments</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-success <?= ($templateVar['mode'] == 'deleted') ? 'active' : '' ?>" href="/user/<?= $templateVar['uploader']->user_id ?>/<?= slugify($templateVar['uploader']->username) ?>/deleted/"><?= display_fa_icon('trash', 'Bin') ?> <span class="d-none d-md-inline">Bin</span></a>
        </li>
    <?php } ?>

    <?php if (validate_level($templateVar['user'], 'gmod')) { ?>
        <li class="nav-item">
            <a class="nav-link text-success <?= ($templateVar['mode'] == 'mod_chapters') ? 'active' : '' ?>" href="/user/<?= $templateVar['uploader']->user_id ?>/<?= slugify($templateVar['uploader']->username) ?>/mod_chapters/"><?= display_fa_icon('edit') ?> <span class="d-none d-md-inline">Mod</span></a>
        </li>
	<?php } ?>

	<?php if (validate_level($templateVar['user'], 'mod')) { ?>
        <li class="nav-item">
            <a class="nav-link text-warning <?= ($templateVar['mode'] == 'admin') ? 'active' : '' ?>" href="/user/<?= $templateVar['uploader']->user_id ?>/<?= slugify($templateVar['uploader']->username) ?>/admin/"><?= display_fa_icon('user-md', 'Modify') ?> <span class="d-none d-md-inline">Modify</span></a>
        </li>
    <?php } ?>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <?= $templateVar['user_tab_html'] ?>
</div>

<?php if (validate_level($templateVar['user'], 'mod')) : ?>
    <!-- restriction modal -->
    <div class="modal fade" id="user_restriction_modal" tabindex="-1" role="dialog" aria-labelledby="user_restriction_modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="user_restriction_label"><span class='fas fa-hand-paper fa-fw ' aria-hidden='true' ></span> Add User Restriction</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" id="user_restriction_form">
                        <div class="form-group row">
                            <label for="target_username" class="col-lg-3 col-form-label-modal">Target User:</label>
                            <div class="col-lg-9">
                                <input class="form-control" name="target_username" id="target_username" type="text" value="<?= $templateVar['uploader']->username ?>" data-size="10" disabled />
                                <input class="form-control" name="target_user_id" id="target_user_id" type="hidden" value="<?= $templateVar['uploader']->user_id ?>" />
                                <input class="form-control" name="mod_user_id" id="mod_user_id" type="hidden" value="<?= $templateVar['user']->user_id ?>" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="restriction_type_id" class="col-lg-3 col-form-label-modal">Restriction:</label>
                            <div class="col-lg-9">
                                <select class="form-control selectpicker" id="restriction_type_id" name="restriction_type_id" data-size="10">
                                    <?php foreach ($templateVar['restriction_types'] as $restriction_type_id => $restriction_type_name) : ?>
                                        <option value="<?= $restriction_type_id ?>"><?= $restriction_type_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="expiration_reltime" class="col-lg-3 col-form-label-modal">Expiration Date/Time:</label>
                            <div class="col-lg-3">
                                <input type="number" class="form-control" id="expiration_reltime" name="expiration_reltime" value="1" />
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control selectpicker" id="expiration_relstep" name="expiration_relstep">
                                    <option value="60">Minute(s)</option>
                                    <option value="3600" selected>Hour(s)</option>
                                    <option value="86400">Day(s)</option>
                                    <option value="2592000">Month(s)</option>
                                    <option value="31536000">Year(s)</option>
                                </select>
                            </div>
                            <div class="col-lg-3 custom-control custom-checkbox form-check">
                                <span class="float-right">
                                    <input type="checkbox" class="custom-control-input" id="expiration_permanent" name="expiration_permanent">
                                    <label class="custom-control-label" for="expiration_permanent">Permanent</label>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="comment" class="col-lg-3 col-form-label-modal">Moderator Comment:</label>
                            <div class="col-lg-9">
                                <textarea maxlength="255" class="form-control" rows="4" id="comment" name="comment" data-size="10"></textarea>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-secondary" id="user_restriction_button"><span class='fas fa-save fa-fw ' aria-hidden='true' ></span> Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $templateVar['post_history_modal_html'] ?>

<?php if ($templateVar['geoip_info']) : ?>
<div class="container-fluid mt-4">
    <div class="row m-0">
        <div class="col text-center">
            <pre class="text-primary">Origin: <?= $templateVar['geoip_info']['continent_code'] ?> / <?= $templateVar['geoip_info']['country_code'] ?> - Server: <?= strtoupper($templateVar['geoip_info']['server_continent_code']) ?> #<?= $templateVar['geoip_info']['server_id'] ?></pre>
        </div>
    </div>
</div>
<?php endif; ?>
