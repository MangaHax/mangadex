<form method="get" id="mod_user_tracking_form" class="mt-3">
    <div class="form-group row">
        <label for="username" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Username:</label>
        <div class="col-md-8 col-lg-9 col-xl-10">
            <input type="text" class="form-control" id="username" name="username" placeholder="username" value="<?= $_GET['username'] ?? '' ?>">
        </div>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-danger" id="mod_user_tracking_button"><?= display_fa_icon('shoe-prints', 'Track', 'fas') ?> Track</button>
    </div>
</form>
<?php if (!empty($templateVar['data'])) : ?>
    <div class="table-responsive table-ip-tracking mt-3">
        <table class="table table-striped table-hover table-sm">
            <thead>
            <tr>
                <th>User</th>
                <th>Joindate</th>
                <th>Last Activity</th>
                <th>Posts</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($templateVar['data'] as $row) : ?>
                <tr>
                    <td><?= display_user_link_v2($row) ?></td>
                    <td><?= date("Y-m-d H:i:s \U\T\C", $row['joined_timestamp']) ?></td>
                    <td><?= date("Y-m-d H:i:s \U\T\C", $row['last_seen_timestamp']) ?></td>
                    <td><a href="/user/<?=$row['user_id']?>/_/comments/"><?=$row['post_count']?></a></td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
<?php endif ?>
