<?php
//$genres = (new Genres())->toArray();
$grouped_genres = new Grouped_Genres();

?>

<!-- Nav tabs -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" href="#site_settings" aria-controls="site_settings" data-toggle="tab"><?= display_fa_icon('home', 'Site settings') ?> <span class="d-none d-lg-inline">Site settings</span></a></li>
    <li class="nav-item"><a class="nav-link" href="#change_profile" aria-controls="change_profile" data-toggle="tab"><?= display_fa_icon('user', 'Change profile') ?> <span class="d-none d-lg-inline">Change profile</span></a></li>
    <li class="nav-item"><a class="nav-link" href="#change_password" aria-controls="change_password" data-toggle="tab"><?= display_fa_icon('key', 'Password and Security') ?> <span class="d-none d-lg-inline">Password and Security</span></a></li>
    <li class="nav-item"><a class="nav-link" href="#upload_settings" aria-controls="upload_settings" data-toggle="tab"><?= display_fa_icon('upload', 'Upload settings') ?> <span class="d-none d-lg-inline">Upload settings</span></a></li>
    <li class="nav-item"><a class="nav-link" href="#supporter_settings" aria-controls="supporter_settings" data-toggle="tab"><?= display_fa_icon('star', 'Supporter settings') ?> <span class="d-none d-lg-inline">Supporter settings</span></a></li>
    <li class="nav-item"><a class="nav-link" href="#blocks" aria-controls="blocks" data-toggle="tab"><?= display_fa_icon('minus-circle', 'Blocks') ?> <span class="d-none d-lg-inline">Group blocks</span></a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade show active" id="site_settings">
        <form method="post" id="site_settings_form" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="language" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Site theme:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="theme_id" name="theme_id">
                        <?php
                        foreach (THEMES as $key => $theme) {
                            $selected = ($templateVar['user']->style == $key) ? 'selected' : '';
                            print "<option $selected value='$key'>$theme</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="navigation" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Navigation:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="navigation" name="navigation">
                        <option <?= !$templateVar['user']->navigation ? 'selected' : '' ?> value="0">Legacy</option>
                        <option <?= ($templateVar['user']->navigation == 1) ? 'selected' : '' ?> value="1">Nav (Left)</option>
                        <option <?= ($templateVar['user']->navigation == 2) ? 'selected' : '' ?> value="2">Nav (Right)</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="latest_updates" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Latest updates:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="latest_updates" name="latest_updates">
                        <option <?= (!$templateVar['user']->latest_updates ? 'selected' : '') ?> value="0">Default</option>
                        <option <?= ($templateVar['user']->latest_updates ? 'selected' : '') ?> value="1">Grouped chapter list</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="mdh_portlimit" class="col-md-4 col-lg-3 col-xl-2 col-form-label">MD@H Port limit:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="mdh_portlimit" name="mdh_portlimit">
                        <option <?= !($templateVar['user']->mdh_portlimit ?? false) ? 'selected' : '' ?> value="0">Normal</option>
                        <option <?= ($templateVar['user']->mdh_portlimit ?? false) ? 'selected' : '' ?> value="1">SSL Port (443) only</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="default_lang_ids" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Filter chapter languages:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select multiple class="form-control selectpicker show-tick" data-actions-box="true" data-selected-text-format="count > 5" data-size="10" id="default_lang_ids" name="default_lang_ids[]" title="All langs">
                        <?= display_languages_select($templateVar['lang_id_filter_array']) ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="display_language" class="col-md-4 col-lg-3 col-xl-2 col-form-label">User interface language:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="display_lang_id" name="display_lang_id" data-size="10">
                        <?= display_languages_select([$templateVar['user']->display_lang_id]) ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="hentai_mode" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Hentai toggle:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="hentai_mode" name="hentai_mode">
                        <option <?= (!$templateVar['user']->hentai_mode ? 'selected' : '') ?> value="0">Hide toggle (in navbar cog)</option>
                        <option <?= ($templateVar['user']->hentai_mode ? 'selected' : '') ?> value="1">Show toggle (in navbar cog)</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="show_unavailable" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Unavailable chapters:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="show_unavailable" name="show_unavailable">
                        <option <?= (!$templateVar['user']->show_unavailable ? 'selected' : '') ?> value="0">Hide</option>
                        <option <?= ($templateVar['user']->show_unavailable ? 'selected' : '') ?> value="1">Show (For tracking purposes)</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="display_moderated" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Comment visibility:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="display_moderated" name="display_moderated">
                        <option <?= (!$templateVar['user']->display_moderated ? 'selected' : '') ?> value="0">Hide moderated comments</option>
                        <option <?= ($templateVar['user']->display_moderated ? 'selected' : '') ?> value="1">Show all comments</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="list_privacy" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Receiving Direct Messages:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="dm_privacy" name="dm_privacy">
                        <option <?= (($templateVar['user']->dm_privacy ?? 0) == 0 ? 'selected' : '') ?> value="0">From everyone</option>
                        <option <?= (($templateVar['user']->dm_privacy ?? 0) == 1 ? 'selected' : '') ?> value="1">From friends only (applies to new DM threads)</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="list_privacy" class="col-md-4 col-lg-3 col-xl-2 col-form-label">MDList privacy:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="list_privacy" name="list_privacy">
                        <option <?= ($templateVar['user']->list_privacy == 0 ? 'selected' : '') ?> value="0">Private</option>
                        <option <?= ($templateVar['user']->list_privacy == 1 ? 'selected' : '') ?> value="1">Public</option>
                        <option <?= ($templateVar['user']->list_privacy == 2 ? 'selected' : '') ?> value="2">Friends only</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="file" class="col-md-4 col-lg-3 col-xl-2 col-form-label">MDList banner:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Preferred width of 1400px. Max 1MB" disabled name="old_file">
                        <span class="input-group-append">
                            <span class="btn btn-secondary btn-file">
                                <?= display_fa_icon('folder-open', '', '', 'far') ?> <span>Browse</span> <input type="file" name="file" id="file" accept=".<?= IMPLODE(",.", ALLOWED_IMG_EXT) ?>">
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="list_banner" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Current MDList banner:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <div class="profile-banner-wrapper">
                        <?= display_list_banner($templateVar['user'], $templateVar['user']->style) ?>
                        <div class="profile-banner-show">Click here to show full banner size</div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label for="reset_list_banner" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Reset MDList banner:</label>
                <div class="col-md-8 col-lg-9 col-xl-10 ">
                    <div class="custom-control custom-checkbox form-check">
                        <input type="checkbox" class="custom-control-input" id="reset_list_banner" name="reset_list_banner" value="1">
                        <label class="custom-control-label" for="reset_list_banner">&nbsp;</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label for="" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Exclude tags:</label>
                <div class="col-md-8 col-lg-9 col-xl-10 excluded_genres_wrapper">
                    <div class="alert alert-info text-center">
                        Select tags to exclude from the frontpage, latest chapter updates, and chapter lists from manga and group pages.<br />
                        The search and the user's MDList will ignore this setting and use their individual filter functionality instead.
                    </div>
                    <?= display_genres_checkboxes($grouped_genres->toGroupedArray(), explode(',', $templateVar['user']->excluded_genres ?? ''), [], false, true) ?>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-secondary" id="site_settings_button"><?= display_fa_icon('save') ?> Save</button>
            </div>
        </form>
    </div>

    <div role="tabpanel" class="tab-pane fade" id="change_profile">
        <form method="post" id="change_profile_form" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="username" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Username:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <input type="text" class="form-control" id="username" name="username" value="<?= $templateVar['user']->username ?>" title="Send ixlone a message on site to change this." disabled>
                </div>
            </div>
            <div class="form-group row">
                <label for="email" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Email:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <input type="email" class="form-control" id="email" name="email" value="<?= $templateVar['user']->email ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="website" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Website:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <input type="text" class="form-control" id="website" name="website" value="<?= $templateVar['user']->user_website ?>" placeholder="http:// or https:// required (Defaults to your MangaDex profile page if not set.)">
                </div>
            </div>
            <div class="form-group row">
                <label for="language" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Profile language:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="lang_id" name="lang_id" data-size="10">
                        <?= display_languages_select([$templateVar['user']->language]) ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="user_bio" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Biography:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <textarea class="form-control" name="user_bio" id="user_bio"><?= $templateVar['user']->user_bio ?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <label for="file" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Avatar:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Leave blank if no change to image. Max 1MB" disabled name="old_file">
                        <span class="input-group-append">
                            <span class="btn btn-secondary btn-file">
                                <?= display_fa_icon('folder-open', '', '', 'far') ?> <span>Browse</span> <input type="file" name="file" id="file" accept=".<?= IMPLODE(",.", ALLOWED_IMG_EXT) ?>">
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="avatar" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Current avatar:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <?= display_avatar($templateVar['user']->avatar, $templateVar['user']->user_id) ?>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-secondary" id="change_profile_button"><?= display_fa_icon('save') ?> Save</button>
            </div>


        </form>
    </div>

    <div role="tabpanel" class="tab-pane fade" id="change_password">
        <div class="container">
            <form method="post" id="change_password_form">
                <div class="form-group row">
                    <label for="old_password" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Old password:</label>
                    <div class="col-md-8 col-lg-9 col-xl-10">
                        <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Old password" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="new_password1" class="col-md-4 col-lg-3 col-xl-2 col-form-label">New password:</label>
                    <div class="col-md-8 col-lg-9 col-xl-10">
                        <input type="password" class="form-control" id="new_password1" name="new_password1" placeholder="New password" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="new_password2" class="col-md-4 col-lg-3 col-xl-2 col-form-label">New password (again):</label>
                    <div class="col-md-8 col-lg-9 col-xl-10">
                        <input type="password" class="form-control" id="new_password2" name="new_password2" placeholder="New password (again)" required>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-danger" id="change_password_button"><?= display_fa_icon('save') ?> Save</button>
                </div>
            </form>
        </div>

        <?php if (isset($templateVar['2fa_html'])) : ?>
            <?= $templateVar['2fa_html'] ?>
        <?php endif; ?>

        <?php if (isset($templateVar['session_html'])) : ?>
            <?= $templateVar['session_html'] ?>
        <?php endif; ?>
    </div>

    <div role="tabpanel" class="tab-pane fade" id="upload_settings">
        <form method="post" id="upload_settings_form">
            <div class="form-group row">
                <label for="cat_id" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Default upload language:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select title="Select a language" class="form-control selectpicker" id="lang_id" name="lang_id" data-size="10">
                        <?= display_languages_select([$templateVar['user']->upload_lang_id]) ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="group_id" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Default upload as:</label>
                <div class="col-md-8 col-lg-9 col-xl-10">
                    <select class="form-control selectpicker" id="group_id" name="group_id">
                        <option value="0">Individual</option>
                        <?php
                        foreach ($templateVar['user_groups_array'] as $group_id => $group_name) {
                            $selected = ($group_id == $templateVar['user']->upload_group_id) ? 'selected' : '';
                            print "<option $selected value='$group_id'>$group_name</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-secondary" id="upload_settings_button"><?= display_fa_icon('save') ?> Save</button>
            </div>

        </form>
    </div>

    <div role="tabpanel" class="tab-pane fade" id="supporter_settings">
        <div class="container">
            <div class="alert alert-info text-center">
                <?php if ($templateVar['user']->premium) : ?>
                    Thank you for supporting us! As a supporter, you have access to some extra settings. More settings will be available in the near future.
                <?php else : ?>
                    Supporters have access to some extra settings. Please consider <a href="/support">supporting</a> us, as our infrastructure is very expensive!
                <?php endif; ?>
            </div>

            <form method="post" id="supporter_settings_form">
                <?= $templateVar['user']->premium ? "" : "<fieldset disabled>" ?>
                <div class="form-group row">
                    <label for="show_supporter_badge" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Show supporter badge:</label>
                    <div class="col-md-8 col-lg-9 col-xl-10">
                        <select class="form-control selectpicker" id="show_supporter_badge" name="show_supporter_badge">
                            <option <?= (!$templateVar['user']->show_premium_badge ? 'selected' : '') ?> value="0">Hide</option>
                            <option <?= ($templateVar['user']->show_premium_badge ? 'selected' : '') ?> value="1">Show</option>
                        </select>
                    </div>
                </div>
                <?= $templateVar['user']->premium ? "" : "</fieldset>" ?>

                <?= count($templateVar['user_clients']) ? "" : "<fieldset disabled>" ?>
                <div class="form-group row">
                    <label for="show_mah_badge" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Show MD@H badge:</label>
                    <div class="col-md-8 col-lg-9 col-xl-10">
                        <select class="form-control selectpicker" id="show_mah_badge" name="show_mah_badge">
                            <option <?= (!$templateVar['user']->show_md_at_home_badge ? 'selected' : '') ?> value="0">Hide</option>
                            <option <?= ($templateVar['user']->show_md_at_home_badge ? 'selected' : '') ?> value="1">Show</option>
                        </select>
                    </div>
                </div>
                <?= count($templateVar['user_clients']) ? "" : "</fieldset>" ?>

                <?= ($templateVar['user']->premium || count($templateVar['user_clients'])) ? "" : "<fieldset disabled>" ?>
                <div class="text-center">
                    <button type="submit" class="btn btn-secondary" id="supporter_settings_button"><?= display_fa_icon('save') ?> Save</button>
                </div>
                <?= $templateVar['user']->premium ? "" : "</fieldset>" ?>
            </form><br />

            <?php if ($templateVar['user']->premium >= 1 && count($templateVar['user']->notes) >= 1) : ?>
                <div class="row">
                    <label class="col-md-4 col-lg-3 col-xl-2 col-form-label">Notes</label>
                </div>

                <?php foreach ($templateVar['user']->notes as $affectedUserId => $savedNote) : ?>
                    <div class="row mt-1">
                        <a class="col-md-4 col-lg-3 col-xl-2 col-form-label" href="/user/<?= $affectedUserId ?>/<?= slugify($savedNote['username']) ?>"><?= $savedNote['username'] ?>:</a>
                        <div class="col-md-8 col-lg-9 col-xl-10 inline">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($savedNote['note'], ENT_QUOTES) ?>" disabled>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane fade" id="blocks">
        <?php if ($templateVar['user_blocked_groups']) : ?>
            <table class="table table-md table-striped">
                <thead>
                    <tr class="border-top-0">
                        <th><?= display_fa_icon('users') ?></th>
                        <th class="text-right"><?= display_fa_icon('question-circle') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($templateVar['user_blocked_groups'] as $group_id => $group_name) : ?>
                        <tr>
                            <td><a href="/group/<?= $group_id ?>/<?= slugify($group_name) ?>"><?= $group_name ?></a></td>
                            <td class="text-right"><button type="button" class="group_unblock_button btn btn-danger btn-sm" data-group-id="<?= $group_id ?>"><?= display_fa_icon('check-circle') ?> Unblock</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="alert alert-success text-center">You have not blocked any groups.</div>
        <?php endif; ?>
        <form class="mb-3" id="group_block_form" method="post">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><?= display_fa_icon('users') ?></span>
                </div>
                <input type="number" class="form-control" id="block_group_id" name="block_group_id" placeholder="group_id" required>
                <div class="input-group-append">
                    <button type="submit" class="btn btn-warning" id="group_block_button"><?= display_fa_icon('minus-circle') ?> Block</button>
                </div>
            </div>
        </form>
        <div class="alert alert-info text-center">Chapters from blocked groups will be hidden on all chapter listings.</div>
    </div>
</div>
