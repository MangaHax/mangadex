<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th><?= display_fa_icon('hashtag') ?></th>
			<th><?= display_fa_icon('user') ?></th>
			<th>IP</th>
			<th><?= display_fa_icon('globe-asia') ?></th>
			<th><?= display_fa_icon('globe') ?></th>
			<th><?= display_fa_icon('bolt') ?></th>
			<th><?= display_fa_icon('upload', 'Mbps') ?></th>
			<th><?= display_fa_icon('download', 'Mbps') ?></th>
			<th><?= display_fa_icon('hdd', 'GB', '', 'far') ?></th>
			<th><?= display_fa_icon('calendar-alt') ?></th>
			<th><?= display_fa_icon('key') ?></th>
			<th>Data transferred (GB)</th>
			<th>Daily average (GB)</th>
			<th><?= display_fa_icon('check') ?><?= display_fa_icon('times') ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($templateVar['clients'] as $client_id => $client) : ?>
		<tr class="<?= $client->approved ? 'text-success' : ($client->approved === 0 ? 'text-danger' : 'text-warning' ) ?>">
			<td>#<?= $client_id ?></td>
			<td><?= display_user_link($client->user_id, $client->username, $client->level_colour) ?></td>
			<td><?= $client->client_ip ?></td>
			<td><?= $client->client_continent ?></td>
			<td><img src="/images/flags/<?= $client->client_country ?>.png" alt="<?= $client->client_country ?>" /></td>
			<td><a href="<?= $client->speedtest ?>" target="_blank"><?= display_fa_icon('external-link-alt') ?></a></td>
			<td><?= $client->upload_speed ?></td>
			<td><?= $client->download_speed ?></td>
			<td><?= $client->disk_cache_size ?></td>
			<td><?= $client->timestamp ? date('Y-m-d H:i:s', $client->timestamp) . ' UTC' : '' ?></td>
			<td><code><?= $client->client_secret ?></code></td>
			<td>0</td>
			<td>0</td>
			<td>
				<?php if ($client->approved === 0 || $client->approved === NULL) : ?>
				<button class="btn btn-success btn-sm approve_button" data-id="<?= $client_id ?>"><?= display_fa_icon('check') ?></button>
				<?php endif; ?>
				<?php if ($client->approved === 1 || $client->approved === NULL) : ?>
				<button class="btn btn-danger btn-sm reject_button" data-id="<?= $client_id ?>"><?= display_fa_icon('times') ?></button>
				<?php endif; ?>
				<?php if ($client->approved === 1) : ?>
				<button class="btn btn-warning btn-sm rotate_button" data-id="<?= $client_id ?>"><?= display_fa_icon('sync-alt') ?></button>
				<?php endif; ?>
			</td>
		</tr>
		<?php
		$total_upload += $client->upload_speed;
		$total_download += $client->download_speed;
		$total_disk += $client->disk_cache_size;
		?>
	<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<th></th>
			<th></th>
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
			<th>total</th>
			<th>avg</th>
		</tr>
	</tfoot>
</table>