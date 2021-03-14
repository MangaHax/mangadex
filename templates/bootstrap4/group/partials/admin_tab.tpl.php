<form class="my-3 method="post" id="admin_edit_group_form">
<div class="form-group row">
    <label for="group_name" class="col-md-4 col-form-label">Group name:</label>
    <div class="col-md-8">
        <input type="text" class="form-control" id="group_name" name="group_name" value="<?= $templateVar['group']->group_name ?>" />
    </div>
</div>
<div class="form-group row">
    <label for="group_alt_name" class="col-md-4 col-form-label">Group alt name:</label>
    <div class="col-md-8">
        <input type="text" class="form-control" id="group_alt_name" name="group_alt_name" value="<?= $templateVar['group']->group_alt_name ?>" />
    </div>
</div>
<div class="form-group row">
    <label for="group_leader_id" class="col-md-4 col-form-label">Group leader ID:</label>
    <div class="col-md-8">
        <input type="text" class="form-control" id="group_leader_id" name="group_leader_id" value="<?= $templateVar['group']->group_leader_id ?>" />
    </div>
</div>
<div class="text-center">
    <button type="submit" class="btn btn-secondary" id="admin_edit_group_button"><?= display_fa_icon('edit') ?> Save</button>
</div>
</form>