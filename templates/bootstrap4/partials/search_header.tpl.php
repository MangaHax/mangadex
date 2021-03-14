<?php
    //$genres = (new Genres())->toArray();
    $grouped_genres = new Grouped_Genres();
?>

<ul class="nav nav-tabs">
    <li class="nav-item" title="Manga titles" ><a class="nav-link" href="/titles"><?= display_fa_icon('book', 'Manga titles') ?> <span class="d-none d-sm-inline">Titles</span></a></li>
    <li class="nav-item" title="Advanced search"><a class="nav-link <?= display_active($_GET['page'], ['search']) ?>" href="/search"><?= display_fa_icon('search', 'Advanced search') ?> <span class="d-none d-sm-inline">Search</span></a></li>
    <li class="nav-item" title="Spring 2018"><a class="nav-link" href="/featured"><?= display_fa_icon('tv', 'Featured') ?> <span class="d-none d-sm-inline">Featured</span></a></li>
    <li class="nav-item" title="Add manga title"><a class="nav-link" href="/manga_new"><?= display_fa_icon('plus-circle', 'Add manga title') ?> <span class="d-none d-sm-inline">Add</span></a></li>

    <li class="nav-item dropdown ml-auto">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon(MANGA_VIEW_MODE_ICONS[$templateVar['title_mode']]) ?></a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="#" class="dropdown-item title_mode <?= (!$templateVar['title_mode']) ? 'active' : '' ?>" id="0"><?= display_fa_icon('th-large') ?> Detailed</a>
            <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 1) ? 'active' : '' ?>" id="1"><?= display_fa_icon('th-list') ?> Expanded list</a>
            <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 2) ? 'active' : '' ?>" id="2"><?= display_fa_icon('bars') ?> Simple list</a>
            <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 3) ? 'active' : '' ?>" id="3"><?= display_fa_icon('th') ?> Grid</a>
        </div>
    </li>
</ul>

<div class="card my-3">
    <h6 class="card-header"><?= display_fa_icon('search-plus') ?> Search</h6>
    <div class="card-body">
        <form id="search_titles_form" method="get" action="/search">
            <div class="form-group row">
                <label for="title" class="col-md-3 col-form-label">Manga title</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlentities($_GET['title'] ?? '', ENT_QUOTES) ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="author" class="col-md-3 col-form-label">Author</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="author" name="author" value="<?= htmlentities($_GET['author'] ?? '', ENT_QUOTES) ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="artist" class="col-md-3 col-form-label">Artist</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="artist" name="artist" value="<?= htmlentities($_GET['artist'] ?? '', ENT_QUOTES) ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="lang_id" class="col-md-3 col-form-label">Original language</label>
                <div class="col-md-9">
                    <select class="form-control" id="lang_id" name="lang_id">
                        <option <?= !isset($_GET['lang_id']) ? "selected" : "" ?> value="">All languages</option>
                        <?php
                        foreach (ORIG_LANG_ARRAY as $key => $language) {
                            $selected = ($key == ($_GET['lang_id'] ?? '')) ? "selected" : "";
                            print "<option $selected value='$key'>$language</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="demo_id" class="col-md-3 col-form-label">Demographic</label>
                <div class="col-md-9">
                    <div class="row px-3">
                        <?php
                    foreach (MANGA_DEMO as $key => $demo) {
                        $checked = empty($templateVar['demos']) || in_array($key, $templateVar['demos']) ? "checked" : "";
                        if ($key) {
                            print "
                                <div class='custom-control custom-checkbox form-check col-auto' style='min-width:8rem'>
                                    <input type='checkbox' class='custom-control-input' name='demo_id[]' id='demo_id_$key' $checked value='$key'>
                                    <label class='custom-control-label' for='demo_id_$key'>$demo</label>
                                </div>";
                        }
                    }
                    ?>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="status_id" class="col-md-3 col-form-label">Publication status</label>
                <div class="col-md-9">
                    <div class="row px-3">
                        <?php
                        foreach (STATUS_ARRAY as $key => $status) {
                            $checked = empty($templateVar['statuses']) || in_array($key, $templateVar['statuses']) ? "checked" : "";
                            if ($key) {
                                print "
                                <div class='custom-control custom-checkbox form-check col-auto' style='min-width:8rem'>
                                    <input type='checkbox' class='custom-control-input' name='status_id[]' id='status_id_$key' $checked value='$key'>
                                    <label class='custom-control-label' for='status_id_$key'>$status</label>
                                </div>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">Tag display mode</label>
                <div class="col-md-9">
                    <div class="btn-group">
                        <button type="button" class="tag-display-mode-toggle btn btn-secondary" data-value="dropdowns">Dropdowns</button>
                        <button type="button" class="tag-display-mode-toggle btn btn-secondary" data-value="checkboxes">Checkboxes</button>
                    </div>
                </div>
            </div>
            <input type="submit" value="Search" class="d-none" />
            <div class="form-group row mb-0 tag-display-mode-wrapper" data-tag-display="dropdowns">
                <label for="tags_inc" class="col-md-3 col-form-label">Include tags</label>
                <div class="col-md-9 genres-filter-wrapper">
                    <?= display_genres_dropdown($grouped_genres->toGroupedArray(), $templateVar['tags_inc'], 'tags_inc') ?>
                </div>
            </div>
            <div class="form-group row mb-0 tag-display-mode-wrapper" data-tag-display="dropdowns">
                <label for="tags_exc" class="col-md-3 col-form-label">Exclude tags</label>
                <div class="col-md-9 genres-filter-wrapper">
                    <?= display_genres_dropdown($grouped_genres->toGroupedArray(), !empty($templateVar['tags_exc']) ? $templateVar['tags_exc'] : explode(',', $templateVar['user']->excluded_genres), 'tags_exc') ?>
                </div>
            </div>
            <div class="form-group row tag-display-mode-wrapper d-none" data-tag-display="checkboxes">
                <label for="tags_both" class="col-md-3 col-form-label">Include/Exclude tags</label>
                <div class="col-md-9 genres-filter-wrapper pl-4">
                    <div class="container">
                        <?= display_genres_checkboxes($grouped_genres->toGroupedArray(), $templateVar['tags_inc'], !empty($templateVar['tags_exc']) ? $templateVar['tags_exc'] : explode(',', $templateVar['user']->excluded_genres), true, false, 'tags_both[]') ?>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">Tag inclusion mode</label>
                <div class="col-md-9">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tag_mode_inc" id="tag_mode_inc_all" value="all" <?= $templateVar['tag_mode_inc'] === 'all' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="tag_mode_inc_all">All <small>(AND)</small></label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tag_mode_inc" id="tag_mode_inc_any" value="any" <?= $templateVar['tag_mode_inc'] === 'any' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="tag_mode_inc_any">Any <small>(OR)</small></label>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">Tag exclusion mode</label>
                <div class="col-md-9">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tag_mode_exc" id="tag_mode_exc_all" value="all" <?= $templateVar['tag_mode_exc'] === 'all' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="tag_mode_exc_all">All <small>(AND)</small></label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tag_mode_exc" id="tag_mode_exc_any" value="any" <?= $templateVar['tag_mode_exc'] === 'any' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="tag_mode_exc_any">Any <small>(OR)</small></label>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-secondary" id="search_button"><?= display_fa_icon('search') ?> Search</button>
            </div>
        </form>
    </div>
</div>
<div id="listing" style="height: 40px; "></div>