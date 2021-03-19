<?php if (count($templateVar['user_clients'])) : ?>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th class="text-center"><?= display_fa_icon('hashtag', 'Client ID') ?></th>
				<th>IP</th>
				<th class="text-center"><?= display_fa_icon('network-wired', 'Test') ?></th>
				<th class="text-center"><?= display_fa_icon('globe-asia', 'Continent') ?></th>
				<th class="text-center"><?= display_fa_icon('globe', 'Country') ?></th>
				<th class="text-center">Status</th>
				<th><?= display_fa_icon('calendar-alt', 'Time of approval') ?></th>
				<th><?= display_fa_icon('key', 'Client secret') ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$total_upload = 0;
		$total_download = 0;
		$total_disk = 0;
		?>
		<?php foreach($templateVar['user_clients'] as $client_id => $client) : ?>
			<tr class="text-<?= $client['approved'] ? 'success' : ($client['approved'] === 0 ? 'danger' : 'warning' ) ?>">
				<td>#<?= $client['client_id'] ?></td>
				<td><?= $client['client_ip'] ?></td>
				<td class="text-center"><?= $client['approved'] ? "<a target='_blank' href='{$templateVar['backend']->getClientUrl('a61fa9f7f1313194787116d1357a7784', ['N9.jpg'], $templateVar['ip'], $client['client_id'])}/data/a61fa9f7f1313194787116d1357a7784/N9.jpg' class='btn btn-sm btn-info' title='Test your client'>" . display_fa_icon('network-wired', 'Test') . "</a>" : "<button class='btn btn-sm btn-info disabled'>" . display_fa_icon('network-wired', 'Test') . "</button>"?></td>
				<td class="text-center"><?= $client['client_continent'] ?></td>
				<td class="text-center"><img src="/images/flags/<?= $client['client_country'] ?>.png" alt="<?= $client['client_country'] ?>" title="<?= $client['client_country'] ?>" /></td>
				<td class="text-center"><?= $client['approved'] ? display_fa_icon('check', 'Approved') : ($client['approved'] === 0 ? display_fa_icon('times', 'rejected') : 'Pending' ) ?></td>
				<td><?= $client['timestamp'] ? date('Y-m-d H:i:s', $client['timestamp']) . ' UTC' : '' ?></td>
				<td><code><?= $client['client_secret'] ?></code></td>
				<!--<td>0</td>
				<td>0</td>-->
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	
	<h3>Instructions: </h3>
	<?php if ($templateVar['approvaltime']) : ?>
	<ul>
		<li>Download <a href="/dl/mangadex_at_home-2.0.0.zip">the client and settings file</a>.</li>
		<pre class="bg-dark text-light p-2">md5: e077e54df77d406d973ce269a7e7febb
sha-256: ef4d139b346837b223c15032dd9f38790fe77d7b5f40320f1d82f6caf7a7bb10</pre>
		<li>settings.sample.yaml needs editing to your config, and renamed as settings.yaml</li>
		
		<li>To start the client, put .jar and settings.json in same folder and run: </li>
		<pre class="bg-dark text-light p-2">java -Dfile-level=trace -Dstdout-level=info -jar mangadex_at_home-2.0.0-all.jar</pre>
		<li>For no logging, run: </li>
		<pre class="bg-dark text-light p-2">java -Dfile-level=off -Dstdout-level=info -jar mangadex_at_home-2.0.0-all.jar</pre>
		<li>(The name of the .jar file will change in future versions, so edit accordingly.)</li>
		<li>If you need help, come on <a href="https://discord.gg/mangadex">Discord</a>.</li>
	</ul>
	<div class="alert alert-info"><strong>Test your client:</strong> Click the test button (<?= display_fa_icon('network-wired', 'Test') ?>) next to your client IP. If you can load the image, your client is live and can receive requests. If not, then you have a configuration issues, usually related to permissions or port forwarding.</div>
	<?php else : ?>
	<p>After approval, you will see the download link for the client and documentation here.</p>
	<?php endif; ?>
	
<?php else : ?>
	<div class="alert alert-info text-center"><?= display_fa_icon('info-circle') ?> You have no approved clients.</div>
<?php endif; ?>
