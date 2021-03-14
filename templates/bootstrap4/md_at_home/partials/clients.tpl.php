<?php if (count($templateVar['user_clients'])) : ?>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th><?= display_fa_icon('hashtag', 'Client ID') ?></th>
				<th>IP</th>
				<th class="text-center"><?= display_fa_icon('network-wired', 'Test') ?></th>
				<th class="text-center"><?= display_fa_icon('globe-asia', 'Continent') ?></th>
				<th class="text-center"><?= display_fa_icon('globe', 'Country') ?></th>
				<th class="text-center"><?= display_fa_icon('upload', 'Upload speed (Mbps)') ?></th>
				<th class="text-center"><?= display_fa_icon('download', 'Download speed (Mbps)') ?></th>
				<th class="text-center"><?= display_fa_icon('hdd', 'Disk allocation (GB)') ?></th>
				<th class="text-center">Status</th>
				<th><?= display_fa_icon('calendar-alt', 'Time of approval') ?></th>
				<th><?= display_fa_icon('key', 'Client secret') ?></th>
				<!--<th>Data transferred (GB)</th>
				<th>Daily average (GB)</th>-->
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
				<td class="text-center"><?= $client['approved'] ? "<a target='_blank' href='https://{$client['client_subsubdomain']}.mangadex.network:{$client['client_port']}/data/a61fa9f7f1313194787116d1357a7784/N9.jpg' class='btn btn-sm btn-info' title='Test your client'>" . display_fa_icon('network-wired', 'Test') . "</a>" : "<button class='btn btn-sm btn-info disabled'>" . display_fa_icon('network-wired', 'Test') . "</button>"?></td>
				<td class="text-center"><?= $client['client_continent'] ?></td>
				<td class="text-center"><img src="/images/flags/<?= $client['client_country'] ?>.png" alt="<?= $client['client_country'] ?>" title="<?= $client['client_country'] ?>" /></td>
				<td class="text-center"><?= $client['upload_speed'] ?></td>
				<td class="text-center"><?= $client['download_speed'] ?></td>
				<td class="text-center"><?= $client['disk_cache_size'] ?></td>
				<td class="text-center"><?= $client['approved'] ? display_fa_icon('check', 'Approved') : ($client['approved'] === 0 ? display_fa_icon('times', 'rejected') : 'Pending' ) ?></td>
				<td><?= $client['timestamp'] ? date('Y-m-d H:i:s', $client['timestamp']) . ' UTC' : '' ?></td>
				<td><code><?= $client['client_secret'] ?></code></td>
				<!--<td>0</td>
				<td>0</td>-->
			</tr>
			<?php
			$total_upload += $client['upload_speed'];
			$total_download += $client['download_speed'];
			$total_disk += $client['disk_cache_size'];
			?>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th><?= $total_upload ?></th>
				<th><?= $total_download ?></th>
				<th><?= $total_disk ?></th>
				<th></th>
				<th></th>
				<th></th>
				<!--<th>total</th>
				<th>avg</th>-->
			</tr>
		</tfoot>
	</table>
	
	<h3>Instructions: </h3>
	<?php if ($templateVar['approvaltime']) : ?>
	<ul>
		<li>Download the <a href="/dl/mangadex_at_home-1.2.2.zip">client</a> and included settings sample (right click and save as).</li>
		<pre class="bg-dark text-light p-2">md5: 7c0c8941544ec09f637a4e8e49204d96
sha-256: 68e26adf68268ae9781919fd5dd80a29595eabf562bbc0cda3dcbe332dd959d8</pre>
		<li>settings.json needs editing to your config.</li>
		<pre class="bg-dark text-light p-2">settings.json example:
{
  "client_secret": "iiesenpaithisisoursecret",
  "client_hostname": "0.0.0.0",                 // "0.0.0.0" is the default and binds to everything
  "client_port": 443,                           // 443 is recommended if possible, otherwise use something higher, e.g. 44300
  "client_external_port": 0,   	                //443 is recommended; This port will be send to mdah-backend.
                                                //You need to forward this to the client_port in your router - 0 uses `client_port`
  "threads": 16,
  "graceful_shutdown_wait_seconds": 60,         // Time from graceful shutdown start to force quit
                                                // This rounds down to 15-second increments
  "max_cache_size_in_mebibytes": 80000,
  "max_kilobits_per_second": 0,                 // 0 disables max brust limiting
  "max_mebibytes_per_hour": 0,                  // 0 disables hourly bandwidth limiting
  "web_settings": {                             // delete this block to disable webui
    "ui_hostname": "127.0.0.1",                 // "127.0.0.1" is the default and binds to localhost only
    "ui_port": 8080
  }
}</pre>
		<li>To start the client, put .jar and settings.json in same folder and run: </li>
		<pre class="bg-dark text-light p-2">java -Dfile-level=trace -Dstdout-level=info -jar mangadex_at_home-1.2.2-all.jar</pre>
		<li>For no logging, run: </li>
		<pre class="bg-dark text-light p-2">java -Dfile-level=off -Dstdout-level=info -jar mangadex_at_home-1.2.2-all.jar</pre>
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
