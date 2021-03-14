<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
        <tr>
            <th scope="col">User</th>
            <th scope="col">Type (Hover for comments)</th>
            <th scope="col">Mod</th>
            <th scope="col">Expires</th>
            <!--th scope="col"></th-->
        </tr>
        </thead>
        <tbody>
        <?php foreach ($templateVar['user_restrictions'] AS $user_restriction) : ?>
            <tr>
                <td><?= display_user_link_v2($user_restriction) ?></td>
                <td><a style="cursor:pointer" title="<?= $user_restriction['comment'] ?>"><?= $templateVar['restriction_types'][$user_restriction['restriction_type_id']] ?? '???' ?></a></td>
                <td><?= display_user_link_v2((object)['level_colour' => $user_restriction['mod_level_colour'], 'user_id' => $user_restriction['mod_user_id'], 'username' => $user_restriction['mod_username']]) ?></td>
                <td><time datetime="<?= gmdate(DATETIME_FORMAT, $user_restriction['expiration_timestamp']) ?>"><?= $user_restriction['expiration_timestamp'] < 4294967295 ? get_time_ago($user_restriction['expiration_timestamp']) : '<span style="color:red">Permanent</span>' ?></time></td>
                <!--td>
                        <?php if ($templateVar['type'] == 'active') : ?>
                        <button data-id="<?= $user_restriction['restriction_id'] ?>" role="button" class="btn btn-danger btn-sm remove-restriction"><?= display_fa_icon('times', 'Lift Restriction', '', 'fas') ?></button>
                        <?php endif; ?>
                    </td-->
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="container">
    <div class="row">
        <div class="col col-6">
            <?php if ($templateVar['offset'] > 0) : ?>
                <a href="/mod/<?=$templateVar['mode']?>/<?=$templateVar['type']?>/<?=max(0, $templateVar['offset'] - $templateVar['limit'])?>" class="btn"><?=display_fa_icon('arrow-left')?> Previous</a>
            <?php else : ?>
                <a href="#" class="btn btn-link disabled"><?=display_fa_icon('arrow-left')?> Previous</a>
            <?php endif; ?>
        </div>
        <div class="col col-6">
            <?php if (count($templateVar['user_restrictions']) == $templateVar['limit']) : ?>
                <a href="/mod/<?=$templateVar['mode']?>/<?=$templateVar['type']?>/<?=$templateVar['offset'] + $templateVar['limit']?>" class="btn float-right">Next <?=display_fa_icon('arrow-right')?></a>
            <?php else : ?>
                <a href="#" class="btn btn-link disabled float-right">Next <?=display_fa_icon('arrow-right')?></a>
            <?php endif; ?>
        </div>
    </div>
</div>