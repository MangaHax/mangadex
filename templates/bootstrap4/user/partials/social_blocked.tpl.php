<table class="table table-md table-striped">
    <thead>
    <tr class="border-top-0">
        <th><?= display_fa_icon('user') ?></th>
        <th width="200px"><?= display_fa_icon('question-circle') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($templateVar['blocked'] as $block) { ?>
        <tr>
            <td><?= display_user_link_v2($block) ?></td>
            <td><button type='button' class='user_unblock_button btn btn-warning btn-sm' id="<?= $block['user_id'] ?>"><?= display_fa_icon('smile', '', '', 'far') ?> Unblock</button></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
