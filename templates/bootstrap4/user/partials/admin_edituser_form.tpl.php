<?php
$user_groups_array = $templateVar['uploader']->get_groups();
$is_admin = validate_level($templateVar['user'], 'admin');
$is_mod = validate_level($templateVar['user'], 'mod');
?>
<form class="my-3" method="post" id="admin_edit_user_form">
    <div class="form-group row">
        <label for="username" class="col-md-4 col-form-label">Username:</label>
        <div class="col-md-8">
            <input type="text" class="form-control" id="username" name="username" <?= $is_admin ? '' : ' disabled' ?> value="<?= $templateVar['uploader']->username ?>" />
        </div>
    </div>
    <?php if ($is_admin) : ?>
    <div class="form-group row">
        <label for="new_pass" class="col-md-4 col-form-label">New password:</label>
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" id="new_pass" name="new_pass" placeholder="Fill in to reset" />
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" id="generate_pass_button"><?= display_fa_icon('question') ?> Generate password</button>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="activation_key" class="col-md-4 col-form-label">Activation key:</label>
        <div class="col-md-8">
            <input type="text" class="form-control" id="activation_key" name="activation_key" value="<?= $templateVar['uploader']->activation_key ?>" />
        </div>
    </div>
    <div class="form-group row">
        <label for="level_id" class="col-md-4 col-form-label">User level:</label>
        <div class="col-md-8">
            <select class="form-control selectpicker" id="level" name="level_id">
                <?php
                $levels = new User_levels ();

                foreach ($levels as $level_id  => $level) {
                    $selected = ($templateVar['uploader']->level_id == $level_id) ? "selected" : "";
                    print "<option value='$level_id' $selected>$level->level_name</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="email" class="col-md-4 col-form-label">Email address:</label>
        <div class="col-md-8">
            <input type="email" class="form-control" id="email" name="email" value="<?= $templateVar['uploader']->email ?>" />
        </div>
    </div>
    <div class="form-group row">
        <label for="creation_ip" class="col-md-4 col-form-label">Signup IP:</label>
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" id="creation_ip" value="<?= $templateVar['uploader']->creation_ip ?>" disabled="disabled" />
                <div class="input-group-append">
                    <a href="/admin/ip_unban/ip_ban?ip=<?= $templateVar['uploader']->creation_ip ?>" class="btn btn-danger"><?= display_fa_icon('gavel') ?> Ban IP</a>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="last_ip" class="col-md-4 col-form-label">Last Used IP:</label>
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" id="last_ip" value="<?= $templateVar['uploader']->last_ip ?>" disabled="disabled" />
                <div class="input-group-append">
                    <a href="/admin/ip_unban/ip_ban?ip=<?= $templateVar['uploader']->last_ip ?>" class="btn btn-danger"><?= display_fa_icon('gavel') ?> Ban IP</a>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="is_2fa" class="col-md-4 col-form-label">2FA Status:</label>
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" id="is_2fa" value="<?= $templateVar['uploader']->twoFa ? 'enabled' : 'disabled' ?>" disabled="disabled" />
                <?php if ($templateVar['uploader']->twoFa) : ?>
                <div class="input-group-append">
                    <button id="remove_2fa_btn" data-user-id="<?= $templateVar['uploader']->user_id ?>" type="button" class="btn btn-danger"><?= display_fa_icon('ban') ?> Remove 2FA</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="is_2fa" class="col-md-4 col-form-label">Session Status:</label>
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" value="<?= $templateVar['uploader']->session_count ?? '???' ?>" disabled="disabled" />
                <div class="input-group-append">
                    <button id="clear_sessions_btn" data-user-id="<?= $templateVar['uploader']->user_id ?>" type="button" class="btn btn-danger"><?= display_fa_icon('ban') ?> Clear Remember-me Sessions</button>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="spamscore" class="col-md-4 col-form-label">Spam Score:</label>
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" value="<?= $templateVar['spamscore'] ?? '-1' ?>" disabled="disabled" />
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="avatar" class="col-md-4 col-form-label">Avatar:</label>
        <div class="col-md-8">
            <input type="text" class="form-control" id="avatar" name="avatar" value="<?= $templateVar['uploader']->avatar ?>" />
        </div>
    </div>
    <div class="form-group row">
        <label for="language" class="col-md-4 col-form-label">Language:</label>
        <div class="col-md-8">
            <select class="form-control selectpicker" id="lang_id" name="lang_id" data-size="10">
                <?= display_languages_select([$templateVar['uploader']->language]) ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="cat_id" class="col-md-4 col-form-label">Default upload language:</label>
        <div class="col-md-8">
            <select class="form-control selectpicker" id="upload_lang_id" name="upload_lang_id" data-size="10">
                <?= display_languages_select([$templateVar['uploader']->upload_lang_id]) ?>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="group_id" class="col-md-4 col-form-label">Default upload as:</label>
        <div class="col-md-8">
            <select class="form-control selectpicker" id="upload_group_id" name="upload_group_id">
                <option value="0">Individual</option>
                <?php
                foreach ($user_groups_array as $group_id => $group_name) {
                    $selected = ($group_id == $templateVar['uploader']->upload_group_id) ? "selected" : "";
                    print "<option $selected value='$group_id'>$group_name</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <?php else : ?>
    <input type="hidden" name="avatar" value="<?=$templateVar['uploader']->avatar?>" />
    <?php endif; ?>
    <div class="form-group row">
        <label for="website" class="col-md-4 col-form-label">Website:</label>
        <div class="col-md-8">
            <input type="text" class="form-control" id="website" name="website" value="<?= $templateVar['uploader']->user_website ?>" />
        </div>
    </div>
    <div class="form-group row">
        <label for="user_bio" class="col-md-4 col-form-label">Bio:</label>
        <div class="col-md-8">
            <textarea type="text" class="form-control" id="user_bio" name="user_bio"><?= $templateVar['uploader']->user_bio ?></textarea>
        </div>
    </div>
    <div class="form-group row">
        <label for="reset_avatar" class="col-md-4 col-form-label">Reset Avatar:</label>
        <div class="col-md-8">
            <div class="custom-control custom-checkbox form-check">
                <input type="checkbox" class="custom-control-input" id="reset_avatar" name="reset_avatar" value="0">
                <label class="custom-control-label" for="reset_avatar">&nbsp;</label>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="reset_list_banner" class="col-md-4 col-form-label">Reset MDList banner:</label>
        <div class="col-md-8">
            <div class="custom-control custom-checkbox form-check">
                <input type="checkbox" class="custom-control-input" id="reset_list_banner" name="reset_list_banner" value="0">
                <label class="custom-control-label" for="reset_list_banner">&nbsp;</label>
            </div>
        </div>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-secondary" id="admin_edit_user_button"><?= display_fa_icon('edit') ?> Save</button>
    </div>
</form>