<div class="card mb-3">
    <h6 class="card-header"><?= display_fa_icon('book') ?> <?= display_manga_link_v2($templateVar['manga'], '', false, false) ?> <?= display_lang_flag_v3($templateVar['manga']) ?></h6>
    <div class="card-body"><?= display_fa_icon('file', '', '', 'far') ?> <?= display_chapter_title($templateVar['chapter'], 0, false) ?></div>
</div>

<div class="edit card mb-3">
    <h6 class="card-header"><?= display_fa_icon("pencil-alt") ?> Edit chapter</h6>
    <div class="card-body">
        <form id="edit_chapter_form" method="post" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="manga_id" class="col-md-3 col-form-label">Manga name</label>
                <div class="col-md-9">
                    <input required type="number" class="form-control" id="manga_id" name="manga_id" placeholder="Required" value="<?= $templateVar['chapter']->manga_id ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="chapter_name" class="col-md-3 col-form-label">Chapter name</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="chapter_name" name="chapter_name" placeholder="Optional" value="<?= $templateVar['chapter']->title ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="volume_number" class="col-md-3 col-form-label">Volume number</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="volume_number" name="volume_number" placeholder="Numbers only" value="<?= $templateVar['chapter']->volume ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="chapter_number" class="col-md-3 col-form-label">Chapter number</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="chapter_number" name="chapter_number" placeholder="Alphanumeric" value="<?= $templateVar['chapter']->chapter ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="group_id" class="col-md-3 col-form-label">Group 1</label>
                <div class="col-md-9">
                    <input required type="number" class="form-control" id="group_id" name="group_id" placeholder="Required" value="<?= $templateVar['chapter']->group_id ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="group_id_2" class="col-md-3 col-form-label">Group 2</label>
                <div class="col-md-9">
                    <input required type="number" class="form-control" id="group_id_2" name="group_id_2" placeholder="Optional" value="<?= $templateVar['chapter']->group_id_2 ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="group_id_3" class="col-md-3 col-form-label">Group 3</label>
                <div class="col-md-9">
                    <input required type="number" class="form-control" id="group_id_3" name="group_id_3" placeholder="Optional" value="<?= $templateVar['chapter']->group_id_3 ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="lang_id" class="col-md-3 col-form-label">Language</label>
                <div class="col-md-9">
                    <select class="form-control selectpicker" id="lang_id" name="lang_id" data-size="10">
                        <?= display_languages_select([$templateVar['chapter']->lang_id]) ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="file" class="col-md-3 col-form-label">File</label>
                <div class="col-md-9">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Zip. Max 100MB" disabled name="old_file" value="">
                        <span class="input-group-btn">
                            <span class="btn btn-secondary btn-file">
                                <?= display_fa_icon("folder-open", "", "", "far") ?> <span class="span-1280">Browse</span> <input type="file" name="file" id="file" accept=".<?= IMPLODE(",.", ALLOWED_CHAPTER_EXT) ?>">
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            <?php if (validate_level($templateVar['user'], 'gmod')) : ?>
                <div class="form-group row">
                    <label for="server" class="col-md-3 col-form-label">Server</label>
                    <div class="col-md-9">
                        <input required type="number" class="form-control" id="server" name="server" placeholder="0, 1 or 2" value="<?= $templateVar['chapter']->server ?>">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="page_order" class="col-md-3 col-form-label">Page order</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="page_order" name="page_order" value="<?= $templateVar['chapter']->page_order ?>">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="unavailable" class="col-md-3 col-form-label">Unavailable</label>
                    <div class="col-md-9">
                        <div class="custom-control custom-checkbox form-check">
							<input type="checkbox" class="custom-control-input" id="unavailable" name="unavailable" value="1" <?= (isset($templateVar['chapter']->available) && $templateVar['chapter']->available) ? '' : 'checked' ?>>
							<label class="custom-control-label" for="unavailable">&nbsp;</label>
						</div>
                    </div>
                </div>
			<?php endif; ?>
			<?php if (validate_level($templateVar['user'], 'mod')) : ?>
				<div class="form-group row">
					<label for="user_id" class="col-md-3 col-form-label">User ID</label>
					<div class="col-md-9">
						<input required type="number" class="form-control" id="user_id" name="user_id" value="<?= $templateVar['chapter']->user_id ?>">
					</div>
				</div>
			<?php endif; ?>
            <div>
                <button type="submit" class="btn btn-success float-right" id="save_edit_button"><?= display_fa_icon("pencil-alt") ?> Save</button>
                <button type="button" class="btn btn-danger" id="chapter_delete_button"><?= display_fa_icon("trash") ?> Delete</button>
            </div>
            <div class="form-group row">
                <div class="col">
                    <div class="progress" style="height: 38px; display: none;">
                        <div id="progressbar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="width: 0%;" class="progress-bar progress-bar-info"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>