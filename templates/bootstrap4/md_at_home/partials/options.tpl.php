<?php if (validate_level($templateVar['user'], 'member')) :  ?>
<form method="post" id="turn_on_form">
	<div class="form-group row">
		<label for="turn_on" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Turn on MD@Home:</label>
		<div class="col-md-8 col-lg-9 col-xl-10 ">
			<div class="custom-control custom-checkbox form-check">
				<input type="checkbox" class="custom-control-input" id="turn_on" name="turn_on" value="1" <?= $templateVar['user']->md_at_home ? 'checked' : '' ?>>
				<label class="custom-control-label" for="turn_on">&nbsp;</label>
			</div>
		</div>
	</div>
	<div class="text-center">
		<button type="submit" class="btn btn-secondary" id="turn_on_button"><?= display_fa_icon('envelope') ?> Update</button>
	</div>
</form>
<?php endif; ?>