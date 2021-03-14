<?php if (validate_level($templateVar['user'], 'member')) :  ?>
<form method="post" id="request_client_form">
	<div class="form-group row">
		<label for="upload" class="col-md-6 col-lg-4 col-xl-3 col-form-label">Upload speed allocated (Mbps):</label>
		<div class="col-md-6 col-lg-8 col-xl-9">
			<input type="number" class="form-control" id="upload" name="upload" value="" placeholder="Min: 80 Mbps (NOT your max connection speed, but the speed you want to use)" required>
		</div>
	</div>
	<div class="form-group row">
		<label for="download" class="col-md-6 col-lg-4 col-xl-3 col-form-label">Download speed allocated (Mbps):</label>
		<div class="col-md-6 col-lg-8 col-xl-9">
			<input type="number" class="form-control" id="download" name="download" value="" placeholder="Min: 80 Mbps (NOT your max connection speed, but the speed you want to use)" required>
		</div>
	</div>
	<div class="form-group row">
		<label for="disk" class="col-md-6 col-lg-4 col-xl-3 col-form-label">Disk cache allocation (GB):</label>
		<div class="col-md-6 col-lg-8 col-xl-9">
			<input type="number" class="form-control" id="disk" name="disk" value="" placeholder="Min: 40 GB" required>
		</div>
	</div>
	<div class="form-group row">
		<label for="ip" class="col-md-6 col-lg-4 col-xl-3 col-form-label">IP address:</label>
		<div class="col-md-6 col-lg-8 col-xl-9">
			<input type="text" class="form-control" id="ip" name="ip" value="" placeholder="IPv4 only" required>
		</div>
	</div>			
	<div class="form-group row">
		<label for="speedtest" class="col-md-6 col-lg-4 col-xl-3 col-form-label">Speedtest link:</label>
		<div class="col-md-6 col-lg-8 col-xl-9">
			<input type="text" class="form-control" id="speedtest" name="speedtest" value="" placeholder="https://www.speedtest.net/result/xxxxxxxxxx.png" required>
		</div>
	</div>
	<div class="form-group row">
		<label for="read_rules" class="col-md-6 col-lg-4 col-xl-3 col-form-label">I've read the <a href="/md_at_home/info">rules</a>:</label>
		<div class="col-md-6 col-lg-8 col-xl-9 ">
			<div class="custom-control custom-checkbox form-check">
				<input type="checkbox" class="custom-control-input" id="read_rules" name="read_rules" value="1">
				<label class="custom-control-label" for="read_rules">&nbsp;</label>
			</div>
		</div>
	</div>
	<div class="text-center">
		<button type="submit" class="btn btn-secondary" id="request_client_button"><?= display_fa_icon('envelope') ?> Request</button>
	</div>
</form>

<?php if ($templateVar['user']->premium || $templateVar['user']->get_chapters_read_count() > MINIMUM_CHAPTERS_READ_FOR_SUPPORT) : ?>
	<h3 class="mt-4">Instructions for running a client on a sdbx.moe VPS</h3>
	<ol>
		<li>Have a look at the VPS plans <a href="https://sdbx.moe/vps" target="_blank">here</a>. The cheapest plan is sufficient for running a client, but of course, the better the VPS, the more beneficial it is to the <a href="https://mangadex.network" target="_blank"<>network</a>.</li>
		<li><a href="https://sdbx.moe/signup">Sign up</a> for an account and enter "CA, MD@H client" in the "requirements" field. You could go for FR if you want, but we urgently need more clients in North America right now.</li>
		<li>Go to the order page and subscribe to your selected VPS plan. You can choose between an automatic recurring or a manual subscription.</li>
		<li>Once your VPS has been set up, you will receive an email. Copy your IP address.</li>
		<li>Fill in the form above and request your client secret. For the speedtest link, just write "sdbx vps", as an actual speedtest is not required.</li>
		<li>After your client request has been approved, you will see your client secret in the <a href="/md_at_home/clients">My clients</a> tab.</li>
		<li>Copy this secret and add it into the "Requirements" field on the "Account" page in the sdbx.moe control panel.</li>
		<li>You will be notified once your client is up and running.</li>
		<li>Your client will run for as long as your subscription is active.</li>
	</ol>

<?php endif; ?>
	
	
<?php else : ?>
<div class="alert alert-info text-center"><?= display_fa_icon('info-circle') ?> Please <a href="/login">log in</a> to request a client.</div>
<?php endif; ?>