<div class="card mb-3">
    <h6 class="card-header"><?= display_fa_icon('tag') ?> <?= $templateVar['genre']->genre_name ?></h6>
    <div class="card-body">
		<?php $templateVar['parser']->parse($templateVar['genre']->genre_description); print nl2br($templateVar['parser']->getAsHtml()); ?>
	</div>
</div>

<?= $templateVar['titles_html'] ?>