<table class="table table-striped table-hover table-sm">
	<thead>
		<tr>
			<th>#</th>
			<th>User</th>
			<th>Chapter</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($templateVar['queue'] as $queue_id => $queue) : ?>
		<tr>
			<td><?= $queue_id ?></td>
			<td><?= display_user_link($queue->user_id, $queue->username, $queue->level_colour) ?></td>
			<td><a target="_blank" href="/chapter/<?= $queue->chapter_id ?>"><?= $queue->chapter_id ?></a></td>
			<td>
				<button class="btn btn-success btn-sm queue_accept" data-id="<?= $queue_id ?>"><?= display_fa_icon('check') ?></button>
                <button class="btn btn-danger btn-sm queue_reject" data-id="<?= $queue_id ?>"><?= display_fa_icon('times') ?></button>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>