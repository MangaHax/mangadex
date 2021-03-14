<?php
    $genres = (new Genres())->toArray();

    print parse_template('manga/partials/manga_navtabs', array_merge($templateVar, ['show_title_modes' => false]));

?>
<div class="card my-3">
    <h6 class="card-header"><?= display_fa_icon('plus-circle') ?> Add new title</h6>
    <div class="card-body">
        <form id="manga_add_form" method="post" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="manga_name" class="col-md-3 col-form-label">Name</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="manga_name" name="manga_name" placeholder="(Usually English or romanized)" required value="<?= htmlentities($_GET['name'] ?? '', ENT_QUOTES) ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="manga_alt_names" class="col-md-3 col-form-label">Alternative name(s)</label>
                <div class="col-md-9">
                    <textarea class="form-control" id="manga_alt_names" name="manga_alt_names" rows="5" placeholder="Use a new line for each entry."></textarea>
                </div>
            </div>
            <div class="form-group row">
                <label for="manga_author" class="col-md-3 col-form-label">Author</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="manga_author" name="manga_author" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="manga_artist" class="col-md-3 col-form-label">Artist</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="manga_artist" name="manga_artist" >
                </div>
            </div>
            <div class="form-group row">
                <label for="manga_lang_id" class="col-md-3 col-form-label">Original language</label>
                <div class="col-md-9">
                    <select class="form-control selectpicker" id="manga_lang_id" name="manga_lang_id">
                        <?php
                        foreach (ORIG_LANG_ARRAY as $key => $language) {
                            print "<option value='$key'>$language</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="manga_status_id" class="col-md-3 col-form-label">Pub. status</label>
                <div class="col-md-9">
                    <select class="form-control selectpicker" id="manga_status_id" name="manga_status_id">
                        <?php
                        foreach (STATUS_ARRAY as $key => $status) {
                            print "<option value='$key'>$status</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="manga_demo_id" class="col-md-3 col-form-label">Demographic</label>
                <div class="col-md-9">
                    <select class="form-control selectpicker" id="manga_demo_id" name="manga_demo_id">
                        <?php
                        foreach (MANGA_DEMO as $key => $demo) {
                            print "<option value='$key'>$demo</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="manga_genres" class="col-md-3 col-form-label">Genres</label>
                <div class="col-md-9">
                    <?= display_genres_checkboxes((new Grouped_Genres())->toGroupedArray(), [], [], false) ?>
                </div>
            </div>
            <div class="form-group row">
                <label for="manga_hentai" class="col-md-3 col-form-label"><span class="label label-danger">Hentai</span></label>
                <div class="col-md-9">
                    <div class="custom-control custom-checkbox form-check">
                        <input type="checkbox" class="custom-control-input" id="manga_hentai" name="manga_hentai" value="1" >
                        <label class="custom-control-label" for="manga_hentai">&nbsp;</label>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="manga_description" class="col-md-3 col-form-label">Description</label>
                <div class="col-md-9">
                    <textarea class="form-control" rows="11" id="manga_description" name="manga_description" placeholder="Optional"></textarea>
                </div>
            </div>
            <div class="form-group row">
                <label for="file" class="col-md-3 col-form-label">Image</label>
                <div class="col-md-9">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Minimum aspect ratio: 1:1.5, highest quality preferred. Max 1MB" disabled>
                        <span class="input-group-append">
							<span class="btn btn-secondary btn-file">
								<?= display_fa_icon('folder-open', '', '', 'far') ?> <span class="span-1280">Browse</span> <input required type="file" name="file" id="file"  accept=".<?= implode(",.", ALLOWED_IMG_EXT) ?>">
							</span>
						</span>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-secondary" id="manga_add_button"><?= display_fa_icon('plus-circle') ?> Add new title</button>
            </div>
        </form>
    </div>
</div>
