<div class="card mb-3">
    <h6 class="card-header"><?= display_fa_icon('upload') ?> Upload guidelines</h6>
    <div class="card-body">
        <ul class="m-0">
            <li>Do not upload:
                <ul>
                    <li>Western comics.</li>
                    <li>Scans of official releases, including raws.</li>
                    <li>Bulk chapters (e.g. Ch. 1-10 as one chapter).</li>
                    <li>Obtrusively watermarked images.</li>
                    <li>Images saved from aggregator sites, if an original source is available.</li>
                </ul>
            </li>
            <li>File limits:
                <ul>
                    <li>Archive file type must be .zip or .cbz, and file size must be less than 100MB.</li>
                    <li>Archive should use deflate compression.</li>
                    <li>No password protected archives.</li>
                    <li>The archive cannot have directories inside directories.</li>
                </ul>
            </li>
            <li>Naming conventions:
                <ul>
                    <li>Include chapter names, if they are available.</li>
                    <li>Use decimals (e.g. Ch. 1.5) for bonus chapters/omake/etc. They may have to be zeropadded.</li>
                    <li>Do not zeropad volume or chapter numbers. (01, 02, etc.)</li>
                    <li>Number volumes correctly. For chapters not (yet) released in volumes, leave the volume number empty.</li>
                    <li>If the comic uses season numbers, use the volume field for that.</li>
                    <li>For oneshots, name the chapter "Oneshot" and leave the volume and chapter numbers empty (except use volume 0 for oneshots inside their serialization entries).</li>
                </ul>
            </li>
            <li>General:
                <ul>
                    <li>Do not add, edit, change the order of, or remove any pages unless you are part of the original scanlator group.</li>
                    <li>Select "Unknown" if you do not know which group to attribute the chapter to.</li>
                    <li>Select "no group" if the original scanlator does not wish to create a group for their scanlation.</li>
                    <li>If the group does not appear on the dropdown list, add it to the database <a target="_blank" href="/group_new">here</a>.</li>
                    <li><span class="fas fa-exclamation-circle" aria-hidden="true" title=""></span> next to a group name indicates that only group members can upload to that group.</li>
                    <li>Do not evade group restrictions by any means.</li>
                </ul>
            </li>
        </ul>
    </div>
</div>

<?php if (!$templateVar['user']->user_uploads) : ?>
<?= display_alert("info", "Notice", "You have no previously uploaded chapters, therefore your uploaded chapters will be held in an upload queue for approval by the moderators."); ?>
<?php endif; ?>

<div class="card mb-3">
    <h6 class="card-header"><?= display_fa_icon('upload') ?> Upload chapter</h6>
    <div class="card-body">
		<?php if (in_array($templateVar['manga']->manga_id, DNU_MANGA_IDS)) : ?>
		<div class="alert alert-warning text-center"><strong>Warning:</strong> This title has an upload restriction. Any uploaded chapters in <strong>English</strong> will be marked as <strong>unavailable</strong>.</div>
		<?php endif; ?>
        <form id="upload_form" method="post" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="manga_id" class="col-md-3 col-form-label">Manga name</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" title="To change the manga, go to the manga page." disabled value="<?= $templateVar['manga']->manga_name ?>">
                    <input type="hidden" id="manga_id" name="manga_id" value="<?= $templateVar['manga']->manga_id ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="chapter_name" class="col-md-3 col-form-label">Chapter name</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="chapter_name" name="chapter_name" placeholder="Optional">
                </div>
            </div>
            <div class="form-group row">
                <label for="volume_number" class="col-md-3 col-form-label">Volume number</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="volume_number" name="volume_number" placeholder="Decimals allowed">
                </div>
            </div>
            <div class="form-group row">
                <label for="chapter_number" class="col-md-3 col-form-label">Chapter number</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="chapter_number" name="chapter_number" placeholder="Decimals allowed" >
                </div>
            </div>
            <div class="form-group row">
                <label for="group_id" class="col-md-3 col-form-label">Group 1</label>
                <div class="col-md-9">
                    <select data-size="10" data-live-search="true" required title="Select a group" class="form-control selectpicker" id="group_id" name="group_id">
                        <?php
                        foreach ($templateVar['group_list'] as $group) {
                            print "<option " . ($group->group_id == $templateVar['user']->upload_group_id ? "selected " : "") . "data-subtext='" . ($group->group_control ? '(Locked)' : '') . "' value='$group->group_id'>$group->group_name</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="group_id_2" class="col-md-3 col-form-label">Group 2</label>
                <div class="col-md-9">
                    <select data-size="10" data-live-search="true" title="Select a second group" class="form-control selectpicker" id="group_id_2" name="group_id_2">
                        <option data-icon='glyphicon-remove' value='0'>None</option>
                        <?php
                        foreach ($templateVar['group_list'] as $group) {
                            print "<option data-subtext='" . ($group->group_control ? '(Locked)' : '') . "' value='$group->group_id'>$group->group_name</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="group_id_3" class="col-md-3 col-form-label">Group 3</label>
                <div class="col-md-9">
                    <select data-size="10" data-live-search="true" title="Select a third group" class="form-control selectpicker" id="group_id_3" name="group_id_3">
                        <option data-icon='glyphicon-remove' value='0'>None</option>
                        <?php
                        foreach ($templateVar['group_list'] as $group) {
                            print "<option data-subtext='" . ($group->group_control ? '(Locked)' : '') . "' value='$group->group_id'>$group->group_name</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">Group delay</label>
                <div class="col-md-9">
                    <input class="form-control" readonly value="If there is a group delay, it will automatically be set." />
                </div>
            </div>
            <div class="form-group row">
                <label for="lang_id" class="col-md-3 col-form-label">Language</label>
                <div class="col-md-9">
                    <select required title="Select a language" class="form-control selectpicker" id="lang_id" name="lang_id" data-size="10">
                        <?= display_languages_select([$templateVar['user']->upload_lang_id]) ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="file" class="col-md-3 col-form-label">File</label>
                <div class="col-md-9" style="">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Zip. Max 100MB" readonly disabled>
                        <span class="input-group-append">
                            <span class="btn btn-secondary btn-file">
                                <?= display_fa_icon('folder-open', '', '', 'far') ?> <span class="span-1280">Browse</span> <input type="file" name="file" id="file" accept=".<?= IMPLODE(",.", ALLOWED_CHAPTER_EXT) ?>">
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            <?php if (validate_level($templateVar['user'], 'gl')) : ?>
            <div class="form-group row">
                <label for="fileurl" class="col-md-3 col-form-label">File url</label>
                <div class="col-md-9">
                    <div class="alert alert-info">
                      File URL upload allows the following sources:<br />
                      <ul>
                        <li>dropbox.com</li>
                        <li>drive.google.com</li>
                      </ul>
                      Pasting a link in this field overrides the direct fileupload.
                    </div>
                    <div class="input-group">
                        <input id="fileurl" name="fileurl" type="text" class="form-control" placeholder="URL to Zip. Max 100MB">
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (validate_level($templateVar['user'], 'gmod')) : ?>
              <div class="form-group row">
                <label for="unavailable" class="col-md-3 col-form-label">Uploader ID:</label>
                <div class="col-md-9">
                  <input type="number" class="form-control" id="override_user_id" name="override_user_id" min="1" placeholder="Optional, leave empty to upload as yourself">
                </div>
              </div>
              <div class="form-group row">
                <label for="external" class="col-md-3 col-form-label">External URL:</label>
                <div class="col-md-9">
                  <input type="text" class="form-control" id="external" name="external" placeholder="Optional">
                </div>
              </div>
                <div class="form-group row">
                    <label for="is_deleted" class="col-md-3 col-form-label">Upload to Bin:</label>
                    <div class="col-md-9">
                        <div class="custom-control custom-checkbox form-check">
                            <input type="checkbox" class="custom-control-input" id="is_deleted" name="is_deleted" value="1">
                            <label class="custom-control-label" for="is_deleted">&nbsp;</label>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="unavailable" class="col-md-3 col-form-label">Unavailable:</label>
                    <div class="col-md-9">
                        <div class="custom-control custom-checkbox form-check">
                            <input type="checkbox" class="custom-control-input" id="unavailable" name="unavailable" value="1">
                            <label class="custom-control-label" for="unavailable">&nbsp;</label>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="form-group row">
                <div class="col">
                    <a class="btn btn-secondary" href="/title/<?= $templateVar['manga']->manga_id ?>" role="button"><?= display_fa_icon('arrow-left') ?> <span class="span-1280">Back</span></a>
                    <button <?= ENABLE_UPLOAD ? '' : 'disabled' ?> type="submit" class="btn btn-success float-right" id="upload_button"><?= display_fa_icon('upload') ?> <span class="span-1280"><?= ENABLE_UPLOAD ? 'Upload' : 'Upload disabled due to image transfer' ?></span></button>
                </div>
            </div>
            <div class="form-group row">
                <div class="col">
                    <div class="progress" style="height: 38px; display: none;">
                        <div id="progressbar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="width: 0%;" class="progress-bar bg-info"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
