<form method="get" id="admin_ip_tracking_form" class="mt-3">
	<div class="form-group row">
		<label for="creation_ip" class="col-md-4 col-lg-3 col-xl-2 col-form-label">Search for IP:</label>
		<div class="col-md-8 col-lg-9 col-xl-10">
			<input type="text" class="form-control" id="query_ip" name="query_ip" placeholder="query_ip" value="<?= $_GET['query_ip'] ?? '' ?>">
		</div>
	</div>
	<div class="text-center">
		<button type="submit" class="btn btn-danger" id="admin_ip_tracking_button"><?= display_fa_icon('search', 'Search IP', 'fas') ?> Search</button>
	</div>
</form>
<?php if (!empty($templateVar['data'])) : ?>
<div class="container mt-5">
	<div class="row">
		<div class="col">
			<?php foreach ($templateVar['data'] as $row) : ?>
			<div class="card">
				<h5 class="card-header">IP: <?= $row['ip'] ?></h5>
				<div class="card-body">
					<pre class="code-box"><?= $row['logs'] ?></pre>
				</div>
				<div class="card-footer"><?= $row['time'] ?></div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php endif ?>
