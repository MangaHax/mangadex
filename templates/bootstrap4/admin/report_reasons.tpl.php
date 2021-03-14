<div class="container">
    <?php if (!empty($templateVar['message'])) : ?>
    <div class="row">
        <div class="col">
            <div class="alert alert-<?= $templateVar['message']['class'] ?? 'success' ?>"><?= $templateVar['message']['text'] ?? $templateVar['message'] ?></div>
        </div>
    </div>
    <?php endif ?>

    <?php $position = 1; ?>
    <form method="POST">
    <?php foreach (REPORT_TYPES AS $type_id => $name) : ?>
        <?php if ($type_id < 1) continue; ?>
        <div class="form-group row">
            <label class="col-md-4 col-lg-3 col-xl-2 col-form-label">Report Type:</label>
            <div class="col-md-8 col-lg-9 col-xl-10 col-form-label text-left">
                <strong><?= $name ?></strong>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-4 col-lg-3 col-xl-2 col-form-label">Report Reasons:</label>
            <div class="col-md-8 col-lg-9 col-xl-10">
                <div class="container item-container" data-type-id="<?= $type_id ?>">
                <?php foreach ($templateVar['report_reasons'] AS $reason) : ?>
                    <?php if ($reason['type_id'] !== $type_id) continue; ?>
                    <div data-type-id="<?= $type_id ?>" class="form-group row item-row">
                        <div class="col-12 col-lg-9">
                            <input type="hidden" name="type_id[<?= $type_id ?>][<?= $position ?>]" value="<?= $reason['id'] ?>" />
                            <input type="text" name="text[<?= $type_id ?>][<?= $position ?>]" class="form-control" value="<?= $reason['text'] ?>" />
                        </div>
                        <div class="col-8 col-lg-2 form-check">
                            <input type="checkbox" name="is_info_required[<?= $type_id ?>][<?= $position ?>]" class="form-check-input"<?= $reason['is_info_required'] ? ' checked="checked"' : '' ?>/>
                            <label class="form-check-label">Info Required</label>
                        </div>
                        <div class="col-4 col-lg-1 form-group">
                            <button data-type-id="<?= $type_id ?>" type="button" class="btn btn-sm btn-link btn-remove-item"><?= display_fa_icon('times', 'Remove', 'times') ?></button>
                        </div>
                    </div>
                    <?php $position++ ?>
                <?php endforeach; ?>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col text-right">
                            <button data-type-id="<?= $type_id ?>" class="btn btn-success btn-add-item" type="button"><?= display_fa_icon('plus', 'Add') ?> Add Report Reason</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <hr>
    <?php endforeach; ?>
        <div class="form-group row">
            <div class="col">
                <button type="submit" class="btn btn-success">Save Changes</button>
		<input type="hidden" name="do" value="report_reasons" />
            </div>
        </div>
    </form>
</div>
