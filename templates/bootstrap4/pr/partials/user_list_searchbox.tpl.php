<div class="card mb-3">
	<h6 class="card-header"><?= display_fa_icon('search') ?> Search users</h6>
	<div class="card-body">
		<form id="user_search_form" method="post">
			<div class="form-group row">
				<label for="username" class="col-md-3 col-form-label">Username:</label>
				<div class="col-md-9">
					<input type="text" class="form-control" id="username" name="username" value="<?= htmlentities($templateVar['search']['username'], ENT_QUOTES) ?>">
				</div>
			</div>
			<div class="form-group row">
				<label for="email" class="col-md-3 col-form-label">Email:</label>
				<div class="col-md-9">
					<input type="text" class="form-control" id="email" name="email" value="<?= htmlentities($templateVar['search']['email'], ENT_QUOTES) ?>">
				</div>
			</div>
			<div class="text-center">
				<button type="submit" class="btn btn-secondary" id="search_button"><?= display_fa_icon('search') ?> <span class="span-1280">Search users</span></button>
			</div>
		</form>
	</div>
</div>