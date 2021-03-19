<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th class="text-center"><?= display_fa_icon('hashtag') ?></th>
			<th><?= display_fa_icon('user') ?></th>
			<th>IP</th>
            <th class="text-center"><?= display_fa_icon('network-wired', 'Test') ?></th>
            <th class="text-center"><?= display_fa_icon('globe-asia') ?></th>
			<th class="text-center"><?= display_fa_icon('globe') ?></th>
			<th class="text-center"><?= display_fa_icon('bolt') ?></th>
			<th class="text-center"><?= display_fa_icon('upload', 'Mbps') ?></th>
			<th class="text-center"><?= display_fa_icon('download', 'Mbps') ?></th>
			<th class="text-center"><?= display_fa_icon('hdd', 'GB', '', 'far') ?></th>
			<th><?= display_fa_icon('calendar-alt') ?></th>
			<th><?= display_fa_icon('key') ?></th>
			<th><?= display_fa_icon('check') ?><?= display_fa_icon('times') ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($templateVar['clients'] as $client_id => $client) : ?>
		<tr class="<?= $client->approved ? 'text-success' : ($client->approved === 0 ? 'text-danger' : 'text-warning' ) ?>">
			<td>#<?= $client_id ?></td>
			<td><?= display_user_link($client->user_id, $client->username, $client->level_colour) ?></td>
			<td><?= $client->client_ip ?></td>
            <td class="text-center"><?= $client->approved === 1 ? "<a target='_blank' href='{$templateVar['backend']->getClientUrl('a61fa9f7f1313194787116d1357a7784', ['N9.jpg'], $templateVar['ip'], $client_id)}/data/a61fa9f7f1313194787116d1357a7784/N9.jpg' class='btn btn-sm btn-info' title='Test your client'>" . display_fa_icon('network-wired', 'Test') . "</a>" : "<button class='btn btn-sm btn-info disabled'>" . display_fa_icon('network-wired', 'Test') . "</button>"?></td>
            <td><?= $client->client_continent ?></td>
			<td><img src="/images/flags/<?= $client->client_country ?>.png" alt="<?= $client->client_country ?>" /></td>
			<td><a href="<?= $client->speedtest ?>" target="_blank"><?= display_fa_icon('external-link-alt') ?></a></td>
			<td><?= $client->upload_speed ?></td>
			<td><?= $client->download_speed ?></td>
			<td><?= $client->disk_cache_size ?></td>
			<td><?= $client->timestamp ? date('Y-m-d H:i:s', $client->timestamp) . ' UTC' : '' ?></td>
			<td><code><?= $client->client_secret ?></code></td>
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
            <th></th>
			<th><?= $total_upload ?></th>
			<th><?= $total_download ?></th>
			<th><?= $total_disk ?></th>
			<th></th>
			<th></th>
			<th></th>
		</tr>
	</tfoot>
</table>