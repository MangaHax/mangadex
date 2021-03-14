<table class="table table-striped table-hover table-sm">
    <thead>
    <tr class="border-top-0">
        <th>Timestamp</th>
        <th>User</th>
        <th>Action</th>
        <th>Changes</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($templateVar['history'] AS $history_entry) :
        $user_link = display_user_link_v2($history_entry);
        $timestamp_str = get_time_ago($history_entry['timestamp']);
        $changes = isset($history_entry['changes']) ? json_decode($history_entry['changes']) : [];
        ?>
        <tr>
            <td><?= $timestamp_str ?></td>
            <td><?= $user_link ?></td>
            <td><?= $history_entry['action'] ?></td>
            <td>
                <?php if (!empty($changes)) : ?>
                    <ul class="mb-0">
                        <?php foreach ($changes AS $change) : ?>
                            <li><?= $change ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>