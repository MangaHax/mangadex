<div class="alert alert-info text-center" role="alert">
    This is a testing prototype for Drama CD translation support. Read more about it and give feedback over at the forums!
    <a title="Go to forum thread" href="/thread/<?= $templateVar['thread'] ?>"><span class="fas fa-external-link-alt fa-fw " aria-hidden="true" title="Forum thread"></span></a>
</div>

<div class="container">
    <div class="row border-bottom py-2 no-gutters">
        <div class="col-auto px-2" style="flex:0 0 80px;"></div>
        <div class="col">
            <div class="row no-gutters">
                <div class="col-auto" style="flex:0 0 2em;">
                    <?= display_fa_icon('globe', 'Language') ?>
                </div>
                <div class="col">
                    <?= display_fa_icon('microphone-alt', 'Drama collection') ?>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($templateVar['collections'] as $collection): ?>
        <div class="row border-bottom py-2 no-gutters">
            <div class="col-auto px-2" style="flex:0 0 80px;">
                <img src="<?= $collection['cover'] ?>" alt="Cover" class="mh-100 mw-100">
            </div>
            <div class="col">
                <div class="row no-gutters">
                    <div class="col-auto" style="flex:0 0 2em;">
                        <span class="rounded flag flag-<?= $collection['language']['code'] ?>" title="<?= $collection['language']['name'] ?>"></span>
                    </div>
                    <div class="col">
                        <a href="/drama/<?= $collection['id'] ?>/<?= slugify($collection['name']) ?>" class="strong"><?= $collection['name'] ?></a>
                        <span>(<?= gmdate(DATE_FORMAT, $collection['pub_date']) ?>)</span>
                    </div>
                    <div class="col">
                    </div>
                </div>
                <div class="row no-gutters">
                    <?= $collection['description'] ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
