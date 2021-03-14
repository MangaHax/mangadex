<?php
$x = [];
$y = [];
foreach ($templateVar['stats'] as $value) {
	$x[] = $value['date'];
	$y[] = $value['users'];
}
?>

<canvas id="registrations_graph" data-x='<?= json_encode($x) ?>' data-y="<?= json_encode($y) ?>"></canvas>

<?php
[$total_hits, $tachi_hits] = $templateVar['memcached']->get("chapter_hits") ?: [0, 0];
?>
<div>
  Total chapter API hits: <?= $total_hits ?>
</div>
<div>
  Tachiyomi hits: <?= $tachi_hits ?> (<?= round($tachi_hits / $total_hits * 100) ?>%)
</div>
