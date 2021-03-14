<form method="post" id="admin_add_tempmail_form" class="m-3">
    <div class="form-group row">
        <label for="ip" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Temp mail:</label>
        <div class="col-md-8 col-lg-9 col-xl-10">
            <input type="text" class="form-control" id="tempmail" name="tempmail" placeholder="trashmail.com" required>
        </div>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-danger" id="admin_add_tempmail_button"><?= display_fa_icon('plus') ?> Add</button>
    </div>
</form>

<div class="row m-1">
	<?php foreach ($templateVar['tempmail'] as $tempmail) : ?>
		
	<div class="col-sm-2"><?= $tempmail ?></div>

	<?php endforeach; ?>
</div>

