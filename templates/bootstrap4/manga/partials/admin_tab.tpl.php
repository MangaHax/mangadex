<form class="m-3" method="post" id="admin_edit_manga_form">
    <div class="form-group row">
        <label for="old_id" class="col-sm-4 col-form-label text-right">Merge this INTO:</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="old_id" name="old_id"/>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-12 text-center">
            <button type="submit" class="btn btn-secondary"
                    id="admin_edit_manga_button"><?= display_fa_icon('edit') ?> Save
            </button>
        </div>
    </div>
</form>
