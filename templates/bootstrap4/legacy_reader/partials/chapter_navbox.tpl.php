<div class="toggle">
    <div class="card mb-3">
        <h6 class="card-header">
            <?= display_fa_icon('book') ?> <?= display_manga_link_v2($templateVar['manga'],'',false,false) ?> <?= display_lang_flag_v3($templateVar['manga']) ?>
            <span title="Hide navbar" style="cursor: pointer" class="minimise fas fa-times fa-lg float-right"></span>
        </h6>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-1">
                    <select class="form-control" id="jump_chapter" name="jump_chapter" data-size="10">
                        <?php
                        foreach ($templateVar['other_chapters']["name"] as $key => $name) {
                            $selected = ($templateVar['chapter']->chapter_id == $key) ? "selected" : "";
                            print "<option $selected value='$key'>$name</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3 mb-1">
                    <select class="form-control" id="jump_group" name="jump_group" data-size="10">
                        <?php
                        foreach ($templateVar['other_groups'] as $chapter_id => $row) {
                            $group_string = $row['group_name'] . ($row['group_name_2'] ? " | " . $row['group_name_2'] : "") . ($row['group_name_3'] ? " | " . $row['group_name_3'] : "");
                            $selected = ($chapter_id === $templateVar['chapter']->chapter_id) ? "selected" : "";
                            print "<option data-content=\"" . display_fa_icon('users', 'Group') . " $group_string " . display_lang_flag_v3($row) . "\" $selected value='$chapter_id'>$group_string</option>";
                        }
                        ?>

                    </select>

                </div>

                <?php if (!$templateVar['user']->reader_mode && $templateVar['mode'] == 'chapter') { ?>
                    <div class="col-md-2 mb-1">
                        <select class="form-control" id="jump_page" name="jump_page" data-size="10">
                            <?php
                            for ($i = 1; $i <= $templateVar['pages']; $i++) {
                                $selected = ($templateVar['page'] == $i) ? "selected" : "";
                                print "<option $selected value='$i'>Page $i</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="col-md-4 text-center mb-1">
                    <?php if (validate_level($templateVar['user'], 'member')) { ?>

                        <button title="Comment" class="btn btn-success comment_button"><?= display_fa_icon('comments', '', '', 'far') ?></button>
                        <button title="Report" class="btn btn-warning" data-toggle="modal" data-target="#report_chapter_modal"><?= display_fa_icon('flag') ?></button>
                        <button title="Reader settings" class="btn btn-secondary" data-toggle="modal" data-target="#legacy_reader_settings_modal"><?= display_fa_icon('cog') ?></button>

                    <?php } else { ?>
                        <button disabled title="Comment (Please log in to use this function)" class="btn btn-success"><?= display_fa_icon('comments', '', '', 'far') ?></button>
                        <button disabled title="Report (Please log in to use this function)" class="btn btn-warning"><?= display_fa_icon('flag') ?></button>
                        <button disabled title="Reader settings (Please log in to use this function)" class="btn btn-secondary"><?= display_fa_icon('cog') ?></button>
                    <?php } ?>


                    <?php
                    if ($templateVar['user']->user_id === $templateVar['chapter']->user_id || validate_level($templateVar['user'], 'gmod') ||
                        ($templateVar['chapter']->group_leader_id === $templateVar['user']->user_id || $templateVar['chapter']->group_leader_id_2 === $templateVar['user']->user_id || $templateVar['chapter']->group_leader_id_3 === $templateVar['user']->user_id || in_array($templateVar['user']->username, $templateVar['group_members_array']))) { ?>
                        <?php if ($templateVar['mode'] == 'edit') { ?>
                            <button title="Back" class="btn btn-info" id="cancel_edit_button"><?= display_fa_icon('undo') ?></button>
                        <?php } else { ?>
                            <button title="Edit" class="btn btn-info" id="edit_button"><?= display_fa_icon('pencil-alt') ?></button>
                        <?php } ?>
                        <?= display_delete_chapter($templateVar['user'], $templateVar['chapter']) ?>
                    <?php }  ?>


                </div>
            </div>
        </div>
    </div>
</div>