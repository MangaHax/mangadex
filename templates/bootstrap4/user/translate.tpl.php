<ul class="nav nav-tabs mb-3">
    <?php
    foreach ($templateVar['languages'] as $l_id => $language) {
        $active = ($templateVar['id'] == $l_id) ? "active" : "";
        print "<li class='nav-item' role='presentation' id='$l_id'><a class='nav-link $active' href='/translate/$l_id'>" . display_lang_flag_v3($language) . "</a></li>";
    }
    ?>
</ul>

<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade show active">
        <form method="post" id="translate_form">
            <?php foreach ($templateVar['ui'] as $key => $value) { ?>
                <div class="form-group row">
                    <label for="<?= $key ?>" class="col-md-3 col-form-label"><?= $key ?></label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="<?= $key ?>" name="<?= $key ?>" value="<?= $value ?>">
                    </div>
                </div>
            <?php } ?>
            <div class="text-center">
                <button type="submit" class="btn btn-secondary" id="translate_button"><?= display_fa_icon('save') ?> Save</button>
            </div>


        </form>
    </div>
</div>