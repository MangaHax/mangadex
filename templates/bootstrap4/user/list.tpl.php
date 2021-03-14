<div class="card mb-3">
    <h6 class="card-header">
        <?= display_fa_icon('list') ?> <a href="/user/<?= $templateVar['list_user']->user_id ?>/<?= strtolower($templateVar['list_user']->username) ?>"><?= $templateVar['list_user']->username ?></a>'s MDList
    </h6>
    <?= display_list_banner($templateVar['list_user'], $templateVar['theme_id']) ?>
</div>

<!-- Nav tabs -->
<ul class="nav nav-tabs mt-3">

    <li class="dropdown d-lg-none">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">
            <?= $templateVar['list_type'] ? display_fa_icon($templateVar['follow_types']->{$templateVar['list_type']}->type_glyph) . ' ' . $templateVar['follow_types']->{$templateVar['list_type']}->type_name : display_fa_icon('book') . ' All' ?>
        </a>
        <div class="dropdown-menu">
            <a href="/list/<?= "{$templateVar['list_user']->user_id}/0" ?>" class="dropdown-item <?= !$templateVar['list_type'] ? 'active' : '' ?>"><?= display_fa_icon('book') ?> All</a>
            <?php
            foreach ($templateVar['follow_types'] as $type) {
                print "<a class='dropdown-item " . ($templateVar['list_type'] == $type->type_id ? 'active' : '') . "' href='/list/{$templateVar['list_user']->user_id}/$type->type_id'>" . display_fa_icon($type->type_glyph) . " $type->type_name</a>";
            }
            ?>
        </div>
    </li>

    <li class="nav-item" title="Show all">
        <a class="<?= !$templateVar['list_type'] ? 'active' : '' ?> nav-link d-none d-lg-block" href="/list/<?= $templateVar['list_user']->user_id ?>/0"><?= display_fa_icon('book') ?> All</a>
    </li>
    <?php
    foreach ($templateVar['follow_types'] as $type) {
        print "<li class='nav-item'><a class='" . ($templateVar['list_type'] == $type->type_id ? 'active ' : '') . "nav-link d-none d-lg-block' href='/list/{$templateVar['list_user']->user_id}/$type->type_id'>" . display_fa_icon($type->type_glyph) . " $type->type_name</a></li>";
    }
    ?>

    <li class="ml-auto dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon('filter') ?></a>
        <div class="dropdown-menu dropdown-menu-right">
            <!--a class="dropdown-item" href="#" id="filter-mutual-button">
                Mutual Manga <span class="far fa-eye fa-fw" aria-hidden="true" style="visibility: hidden"></span>
            </a-->
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#genre_filter_modal">Tag Filter</a>
        </div>
    </li>
    <li class="dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false"><?= display_fa_icon(MANGA_VIEW_MODE_ICONS[$templateVar['title_mode']]) ?></a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="#" class="dropdown-item title_mode <?= (!$templateVar['title_mode']) ? 'active' : '' ?>" id="0"><?= display_fa_icon('th-large') ?> Detailed</a>
            <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 1) ? 'active' : '' ?>" id="1"><?= display_fa_icon('th-list') ?> Expanded list</a>
            <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 2) ? 'active' : '' ?>" id="2"><?= display_fa_icon('bars') ?> Simple list</a>
            <a href="#" class="dropdown-item title_mode <?= ($templateVar['title_mode'] == 3) ? 'active' : '' ?>" id="3"><?= display_fa_icon('th') ?> Grid</a>
        </div>
    </li>
    <?php if ($templateVar['user']->user_id == $templateVar['list_user']->user_id) { ?>
        <li class="nav-item" title="Settings">
            <a class="nav-link" href="#" data-toggle="modal" data-target="#list_setting_modal"><?= display_fa_icon('cog') ?></a>
        </li>
    <?php } ?>
</ul>

<div class="filtered-amount text-right my-2 mr-3"></div>

<?= $templateVar['manga_list_html'] ?>

    <div class="modal fade" id="genre_filter_modal" tabindex="-2" role="dialog" aria-labelledby="genre_filter_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="genre_filter_label"><?= display_fa_icon('filter')?> Filter MDList by Tags</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= display_genres_checkboxes((new Grouped_Genres())->toGroupedArray(), [], true) ?>
                </div>
            </div>
        </div>
    </div>

<?php if ($templateVar['user']->user_id == $templateVar['list_user']->user_id) : ?>
    <!-- Modal -->
    <div class="modal fade" id="list_setting_modal" tabindex="-1" role="dialog" aria-labelledby="list_settings_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="list_settings_label"><?= display_fa_icon('cog')?> MDList settings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" id="list_settings_form" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label for="list_privacy" class="col-md-4 col-form-label">List privacy:</label>
                            <div class="col-md-8">
                                <select class="form-control selectpicker" id="list_privacy" name="list_privacy">
                                    <option <?= ($templateVar['user']->list_privacy == 0 ? "selected" : "") ?> value="0">Private</option>
                                    <option <?= ($templateVar['user']->list_privacy == 1 ? "selected" : "") ?> value="1">Public</option>
                                    <option <?= ($templateVar['user']->list_privacy == 2 ? "selected" : "") ?> value="2">Friends only</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="file" class="col-md-4 col-form-label">List banner:</label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Preferred width of 1400px. Max 1MB" disabled name="old_file">
                                    <span class="input-group-append">
										<span class="btn btn-secondary btn-file">
											<?= display_fa_icon('folder-open', '', '', 'far') ?> <span>Browse</span> <input type="file" name="file" id="file" accept=".<?= IMPLODE(",.", ALLOWED_IMG_EXT) ?>">
										</span>
									</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="list_banner" class="col-md-4 col-form-label">Current list banner:</label>
                            <div class="col-md-8">
                                <?= display_list_banner($templateVar['user'], $templateVar['user']->style) ?>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="reset_list_banner" class="col-md-4 col-form-label">Reset list banner:</label>
                            <div class="col-md-4 ">
                                <div class="custom-control custom-checkbox form-check">
                                    <input type="checkbox" class="custom-control-input" id="reset_list_banner" name="reset_list_banner" value="1">
                                    <label class="custom-control-label" for="reset_list_banner">&nbsp;</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-secondary" id="site_settings_button"><?= display_fa_icon('save') ?> Save</button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; // Modal ?>
