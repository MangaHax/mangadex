<form method="post" id="admin_ip_ban_form" class="mt-3">
    <div class="form-group row">
        <label for="ip" class="col-md-4 col-lg-3 col-xl-2 col-form-label">IP:</label>
        <div class="col-md-8 col-lg-9 col-xl-10">
            <input type="text" class="form-control" id="ip" name="ip" placeholder="ip" value="<?= $_GET['ip'] ?? '' ?>" required>
        </div>
    </div>
    <div class="form-group row">
        <label for="expires" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Expires (h):</label>
        <div class="col-md-8 col-lg-9 col-xl-10">
            <input type="text" class="form-control" id="expires" name="expires" placeholder="in x hours" value="24" required>
        </div>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-danger" id="admin_ip_ban_button"><?= display_fa_icon('gavel') ?> Ban</button>
    </div>
</form>
<?php if (!empty($templateVar['banlist'])) : ?>
    <div class="table-responsive table-ip-banlist">
        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th>IP</th>
                <th>Expires</th>
                <th></th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($templateVar['banlist'] as $row) : ?>
                <tr>
                    <td><?= $row['ip'] ?></td>
                    <td><?= date("Y-m-d H:i:s \U\T\C", $row['expires']) . ($row['expires'] > time() ? '' : ' <span style="color:red;font-weight:bold">(expired)</span>') ?></td>
                    <td><button role="button" class="btn btn-sm btn-danger" data-ip="<?= $row['ip'] ?>"><?= display_fa_icon('times', 'Unban', 'fas') ?></button></td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
<?php endif ?>
