<div class="card-body">
    <form class="form-inline" method="post" id="banner_upload_form">
        <?= display_fa_icon("file-image", "File") ?><input type="file" class="form-control" name="file" id="file" accept=".jpg,.jpeg,.png,.gif" required>
        <?= display_fa_icon("user", "User") ?> <input type="text" class="form-control input-sm" name="user_id" placeholder="User ID" required>
        <?= display_fa_icon("user-secret", "Anonymity") ?> <input type="checkbox" class="form-check-input" name="is_anonymous">
        <?= display_fa_icon("power-off", "Enabled Status") ?> <input type="checkbox" class="form-check-input" name="is_enabled">
        <button class="btn btn-sm btn-success" type="submit" id="banner_upload_button">
            <?= display_fa_icon("upload", "Upload") ?> Upload
        </button>
    </form>
</div>
<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
        <tr class=" border-top-0">
            <th><?= display_fa_icon("id-card", "ID", '', 'far') ?></th>
            <th><?= display_fa_icon("image", "Image", '', 'far') ?></th>
            <th><?= display_fa_icon("user-edit", "Author") ?></th>
            <th><?= display_fa_icon("user-secret", "Anonymous") ?></th>
            <th><?= display_fa_icon("power-off", "Enabled") ?></th>
            <th><?= display_fa_icon("pencil-alt", "Edit") ?></th>
        </tr>
        </thead>
        
        <tbody>
        <?php
        foreach($templateVar['banners'] as $banner){
        ?>
            <tr id="banner_<?= $banner['banner_id'] ?>">
                <td><?= $banner["banner_id"] ?></td>
                <td><img width="100%" src="/images/banners/affiliatebanner<?= $banner["banner_id"]?>.<?= $banner["ext"]?>"></td>
                <td><?= display_user_link_v2($banner) ?></td>
                <td><?= display_fa_icon($banner["is_anonymous"] ? 'user-secret' : 'user', 'User Anonymity') ?></td>
                <td style="color: <?= $banner["is_enabled"] ? 'green' : 'red' ?>"><?= display_fa_icon('power-off', 'Enabled Status') ?></td>
                <td>
                    <button class="btn btn-xs btn-info toggle_banner_edit_button" type="button" data-toggle="<?= $banner["banner_id"] ?>" id="toggle_banner_edit_button_<?= $banner["banner_id"] ?>">
                        <?= display_fa_icon("pencil-alt", "Edit") ?>
                    </button>
                </td>
            </tr>
            <tr class="display-none" id="banner_edit_<?= $banner["banner_id"] ?>">
                <td colspan="6">
                    <form class="form-inline banner_edit_form" method="post" data-banner-id=<?= $banner["banner_id"] ?> id="banner_edit_form_<?= $banner["banner_id"] ?>">
                        <?= display_fa_icon("file-image", "File") ?><input type="file" class="form-control" name="file" id="file" accept=".jpg,.jpeg,.png,.gif">
                        <?= display_fa_icon("user", "User") ?> <input type="text" class="form-control input-sm" name="user_id" value="<?= $banner["user_id"] ?>" required>
                        <?= display_fa_icon("user-secret", "Anonymity") ?> <input type="checkbox" name="is_anonymous" <?= $banner["is_anonymous"] ? 'checked' : '' ?>>
                        <?= display_fa_icon("power-off", "Enabled Status") ?> <input type="checkbox" name="is_enabled" <?= $banner["is_enabled"] ? 'checked' : '' ?>>
                        <button class="btn btn-sm btn-success banner_edit_button" type="submit" id="banner_edit_button_<?= $banner["banner_id"] ?>">
                            <?= display_fa_icon("pencil-alt", "Apply") ?>
                        </button>
                        <button class="btn btn-sm btn-warning pull-right cancel_banner_edit_button" type="button" data-toggle="<?= $banner["banner_id"] ?>" id="cancel_banner_edit_button_<?= $banner["banner_id"] ?>">
                            <?= display_fa_icon("times", "Cancel", "fa-fw") ?>
                        </button>
                    </form>
                </td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
