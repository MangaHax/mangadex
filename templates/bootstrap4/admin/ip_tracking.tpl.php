<form method="get" id="admin_ip_tracking_form" class="mt-3">
    <div class="form-group row">
        <label for="creation_ip" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Creation IP:</label>
        <div class="col-md-8 col-lg-9 col-xl-10">
            <input type="text" class="form-control" id="creation_ip" name="creation_ip" placeholder="creation_ip" value="<?= $_GET['creation_ip'] ?? '' ?>">
        </div>
    </div>
    <div class="form-group row">
        <label for="last_ip" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Last IP:</label>
        <div class="col-md-8 col-lg-9 col-xl-10">
            <input type="text" class="form-control" id="last_ip" name="last_ip" placeholder="last_ip" value="<?= $_GET['last_ip'] ?? '' ?>">
        </div>
    </div>
    <div class="form-group row">
        <label for="email_host" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Email host:</label>
        <div class="col-md-8 col-lg-9 col-xl-10">
            <input type="text" class="form-control" id="email_host" name="email_host" placeholder="email_host" value="<?= $_GET['email_host'] ?? '' ?>">
        </div>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-danger" id="admin_ip_tracking_button"><?= display_fa_icon('shoe-prints', 'Track', 'fas') ?> Track</button>
    </div>
</form>
<?php if (!empty($templateVar['data'])) : ?>
    <div class="table-responsive table-ip-tracking mt-3">
        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th>User</th>
                <th>Creation IP</th>
                <th>Last IP</th>
                <th>Joindate</th>
                <th>Last Activity</th>
                <th>Email</th>
                <th>Posts</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($templateVar['data'] as $row) : ?>
                <tr>
                    <td><?= display_user_link_v2($row) ?></td>
                    <td style="<?=$row['creation_ip'] === @$_GET['creation_ip'] ? 'font-weight:bold' : ''?>"><?=$row['creation_ip']?></td>
                    <td style="<?=$row['last_ip'] === @$_GET['last_ip'] ? 'font-weight:bold' : ''?>"><?=$row['last_ip']?></td>
                    <td><?= date("Y-m-d H:i:s \U\T\C", $row['joined_timestamp']) ?></td>
                    <td><?= date("Y-m-d H:i:s \U\T\C", $row['last_seen_timestamp']) ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><a href="/user/<?=$row['user_id']?>/_/comments/"><?=$row['post_count']?></a></td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
<?php endif ?>
