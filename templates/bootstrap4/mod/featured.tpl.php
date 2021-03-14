<?= $templateVar['manga_list_html'] ?>

<div class="card mb-3">
    <h6 class="card-header">Add featured</h6>
    <div class="card-body">
        <form method="post" id="add_featured_form">
            <div class="form-group row">
                <label for="manga_id" class="col-md-3 col-form-label">Manga ID:</label>
                <div class="col-md-9">
                    <input type="number" class="form-control" id="manga_id" name="manga_id" required>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-success" id="add_featured_button"><?= display_fa_icon('plus-circle') ?> Add</button>
            </div>
        </form>
    </div>
</div>