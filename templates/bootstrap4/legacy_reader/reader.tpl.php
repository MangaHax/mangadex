<?php if ($templateVar['user']->reader_mode) { ?>
    <div class="row display-none toggle my-3">
        <div class="col-xs-2">
            <button <?= ($templateVar['prev_id']) ? "" : "disabled" ?> class="btn btn-sm btn-secondary prev_chapter_alt" title="Go to previous chapter"><?= display_fa_icon('angle-double-left', 'Last chapter') ?></button>
        </div>

        <div class="col-xs-2">
            <button <?= ($templateVar['user']->reader_mode) ? "disabled" : "" ?> class="btn btn-sm btn-secondary prev_page_alt" title="Go to previous page"><?= display_fa_icon('angle-left', 'Last chapter') ?></button>
        </div>

        <div class="col-xs-4 text-center">
            <button class="maximise btn btn-sm btn-secondary" title="Display navbar"><?= display_fa_icon('window-maximize', 'Maximise') ?></button>
            <button title="Comment" class="btn btn-sm btn-success comment_button"><?= display_fa_icon('comments', 'far', '', 'fa-fw') ?></button>
        </div>

        <div class="col-xs-2 text-right">
            <button <?= ($templateVar['user']->reader_mode) ? "disabled" : "" ?> class="btn btn-sm btn-secondary next_page_alt" title="Go to next page"><?= display_fa_icon('angle-right', 'Last chapter') ?></button>
        </div>

        <div class="col-xs-2 text-right">
            <button <?= ($templateVar['next_id']) ? "" : "disabled" ?> class="btn btn-sm btn-secondary next_chapter_alt" title="Go to next chapter"><?= display_fa_icon('angle-double-right', 'Next chapter') ?></button>
        </div>
    </div>
<?php } ?>

<div class="images <?= ($templateVar['user']->image_fit == 1) ? 'horizontal-window-fit' : (($templateVar['user']->image_fit == 2 && !$templateVar['user']->reader_mode) ? 'vertical-window-fit' : '') ?>">

    <?php

    switch ($templateVar['user']->reader_mode) {
        case 0:
            ?>

            <img id="current_page" class="reader <?= ($templateVar['user']->image_fit == 2) ? 'max-height' : 'max-width' ?>" src="<?= $templateVar['server'] ?><?= $templateVar['chapter']->chapter_hash ?>/<?= $templateVar['page_array'][$templateVar['page']] ?? '' ?>" alt="image" data-page="<?= $templateVar['page'] ?>" />

            <?php
            break;

        case 2: //long-strip
            foreach ($templateVar['page_array'] as $key => $x) {
                if (!$templateVar['chapter']->server && $templateVar['chapter']->chapter_id == 256885 && in_array($key, [1])) {
                    ?>
                    <img class="long-strip <?= ($templateVar['user']->reader_click) ? "click" : "" ?>" src="/img.php?x=/data/<?= "{$templateVar['chapter']->chapter_hash}/$x" ?>" alt="image <?= $key ?>" />
                    <?php
                }

                else {
                    ?>
                    <img class="long-strip <?= ($templateVar['user']->reader_click) ? "click" : "" ?>" src="<?= "{$templateVar['server']}{$templateVar['chapter']->chapter_hash}/$x" ?>" alt="image <?= $key ?>" />
                    <?php
                }
            }

            break;

        case 3: //webtoon
            foreach ($templateVar['page_array'] as $key => $x) {
                if (!$templateVar['chapter']->server && $templateVar['chapter']->chapter_id == 256885 && in_array($key, [1])) {
                    ?>
                    <img class="webtoon <?= ($templateVar['user']->reader_click) ? "click" : "" ?>" src="/img.php?x=/data/<?= "{$templateVar['chapter']->chapter_hash}/$x" ?>" alt="image <?= $key ?>" />
                    <?php
                }

                else {
                    ?>

                    <img class="webtoon <?= ($templateVar['user']->reader_click) ? "click" : "" ?>" src="<?= "{$templateVar['server']}{$templateVar['chapter']->chapter_hash}/$x" ?>" alt="image <?= $key ?>" />

                    <?php
                }
            }

            break;

        default:
            ?>
            <img id="current_page" class="reader <?= ($templateVar['user']->image_fit == 2) ? 'max-height' : 'max-width' ?>" src="<?= $templateVar['server'] ?><?= $templateVar['chapter']->chapter_hash ?>/<?= $templateVar['page_array'][$templateVar['page']] ?>" alt="image" data-page="<?= $templateVar['page'] ?>" />
            <?php
            break;
    }
    ?>
</div>

<div class="<?= ($templateVar['user']->reader_mode) ? "" : "display-none toggle" ?>">
    <div class="my-3 row">
        <div class="col-sm-2">
            <button <?= ($templateVar['prev_id']) ? "" : "disabled" ?> class="btn btn-sm btn-secondary prev_chapter_alt" title="Go to previous chapter"><?= display_fa_icon('angle-double-left', 'Last chapter') ?></button>
        </div>

        <div class="col-sm-2">
            <button <?= ($templateVar['user']->reader_mode) ? "disabled" : "" ?> class="btn btn-sm btn-secondary prev_page_alt" title="Go to previous page"><?= display_fa_icon('angle-left', 'Last chapter') ?></button>
        </div>

        <div class="col-sm-4 text-center">
            <button class="btn btn-sm btn-secondary maximise" title="Display navbar"><?= display_fa_icon('window-maximize', 'Maximise') ?></button>
            <button title="Comment" class="btn btn-sm btn-success comment_button"><?= display_fa_icon('comments', '', '', 'far') ?></button>
        </div>

        <div class="col-sm-2 text-right">
            <button <?= ($templateVar['user']->reader_mode) ? "disabled" : "" ?> class="btn btn-sm btn-secondary next_page_alt" title="Go to next page"><?= display_fa_icon('angle-right', 'Last chapter') ?></button>
        </div>

        <div class="col-sm-2 text-right">
            <button <?= ($templateVar['next_id']) ? "" : "disabled" ?> class="btn btn-sm btn-secondary next_chapter_alt" title="Go to next chapter"><?= display_fa_icon('angle-double-right', 'Next chapter') ?></button>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="report_chapter_modal" tabindex="-1" role="dialog" aria-labelledby="report_chapter_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="report_chapter_label"><?= display_fa_icon('cog')?> Report chapter</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="chapter_report_form" method="post">
                    <div class="form-group row">
                        <label for="lang_id" class="col-md-3 col-form-label">Reason</label>
                        <div class="col-md-9">
                            <select required title="Select a reason" class="form-control selectpicker" id="type_id" name="type_id">
                                <?php
                                $chapter_reasons = array_filter($templateVar['report_reasons'], function($reason) { return REPORT_TYPES[$reason['type_id']] === 'Chapter'; });
                                foreach ($chapter_reasons as $reason): ?>
                                  <option value="<?= $reason['id'] ?>"><?= $reason['text'] ?><?= $reason['is_info_required'] ? ' *' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="chapter_name" class="col-md-3 col-form-label">Explanation</label>
                        <div class="col-md-9">
                            <textarea class="form-control" id="info" name="info" placeholder="Optional" ></textarea>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-warning" id="chapter_report_button"><?= display_fa_icon("pencil-alt") ?> Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="legacy_reader_settings_modal" tabindex="-1" role="dialog" aria-labelledby="legacy_reader_settings_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="legacy_reader_settings_label"><?= display_fa_icon('cog')?> Legacy reader settings</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" id="reader_settings_form">
                    <div class="form-group row">
                        <label for="reader" class="col-md-3 col-form-label">Reader:</label>
                        <div class="col-md-9">
                            <select class="form-control selectpicker" id="reader" name="reader">
                                <option <?= (!$templateVar['user']->reader ? "selected" : "") ?> value="0">Default</option>
                                <option <?= ($templateVar['user']->reader ? "selected" : "") ?> value="1">Legacy</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="img_server" class="col-md-3 col-form-label">Image server:</label>
                        <div class="col-md-9">
                            <select class="form-control selectpicker" id="img_server" name="img_server">
                                <option <?= (!$templateVar['user']->img_server ? 'selected' : '') ?> value="0">Automatic</option>
                                <option <?= ($templateVar['user']->img_server == 2 ? 'selected' : '') ?> value="2">North America</option>
                                <option <?= ($templateVar['user']->img_server == 3 ? 'selected' : '') ?> value="3">Europe</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="reader_mode" class="col-md-3 col-form-label">Reader mode:</label>
                        <div class="col-md-9">
                            <select class="form-control selectpicker" id="reader_mode" name="reader_mode">
                                <option <?= (!$templateVar['user']->reader_mode ? "selected" : "") ?> value="0">Normal</option>
                                <option <?= ($templateVar['user']->reader_mode == 2 ? "selected" : "") ?> value="2">Long strip (Load all images)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="reader_click" class="col-md-3 col-form-label">Reader click:</label>
                        <div class="col-md-9">
                            <select class="form-control selectpicker" id="reader_click" name="reader_click">
                                <option <?= ($templateVar['user']->reader_click ? "selected" : "") ?> value="1">Enabled (Click on image to go to next page or chapter)</option>
                                <option <?= (!$templateVar['user']->reader_click ? "selected" : "") ?> value="0">Disabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="image_fit" class="col-md-3 col-form-label">Image fit:</label>
                        <div class="col-md-9">
                            <select class="form-control selectpicker" id="image_fit" name="image_fit">
                                <option <?= ($templateVar['user']->image_fit ? "selected" : "") ?> value="0">Fit image width to container</option>
                                <option <?= ($templateVar['user']->image_fit == 1 ? "selected" : "") ?> value="1">Fit image width to window (if possible)</option>
                                <option <?= ($templateVar['user']->image_fit == 2 ? "selected" : "") ?> value="2">Fit image height to window (if possible)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="swipe_direction" class="col-md-3 col-form-label">Swipe direction:</label>
                        <div class="col-md-9">
                            <select class="form-control selectpicker" id="swipe_direction" name="swipe_direction">
                                <option <?= ($templateVar['user']->swipe_direction ? "selected" : "") ?> value="1">Normal (Swipe left for next page, right for last page)</option>
                                <option <?= (!$templateVar['user']->swipe_direction ? "selected" : "") ?> value="0">Reversed (Swipe right for next page, left for last page)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="swipe_sensitivity" class="col-md-3 col-form-label">Swipe sensitivity:</label>
                        <div class="col-md-9">
                            <select class="form-control selectpicker" id="swipe_sensitivity" name="swipe_sensitivity">
                                <option <?= (($templateVar['user']->swipe_sensitivity - 25) / 25 == 5 ? "selected" : "") ?> value="5">Very high</option>
                                <option <?= (($templateVar['user']->swipe_sensitivity - 25) / 25 == 4 ? "selected" : "") ?> value="4">High</option>
                                <option <?= (($templateVar['user']->swipe_sensitivity - 25) / 25 == 3 ? "selected" : "") ?> value="3">Normal</option>
                                <option <?= (($templateVar['user']->swipe_sensitivity - 25) / 25 == 2 ? "selected" : "") ?> value="2">Low</option>
                                <option <?= (($templateVar['user']->swipe_sensitivity - 25) / 25 == 1 ? "selected" : "") ?> value="1">Very low</option>
                                <option <?= (($templateVar['user']->swipe_sensitivity - 25) / 25 == 0 ? "selected" : "") ?> value="0">Off</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success" id="reader_settings_button"><?= display_fa_icon('pencil-alt') ?> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>