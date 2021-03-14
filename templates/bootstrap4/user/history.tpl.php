<!-- Nav tabs -->
<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link active" href="/history"><?= display_fa_icon('history') ?> <span class="d-none d-md-inline">History</span></a>
    </li>

</ul>


<!-- Tab panes -->
<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade show active" id="history">
        <?php
        switch ($templateVar['mode']) {
            case 'history':

                print "<p class='p-2'>Your last 10 read titles are listed below.</p>";

                foreach($templateVar['chapter_history'] as $chapter) {
                    ?>
                    <div class="large_logo rounded position-relative mx-1 my-2 ">
                        <div class="hover">
                            <a href="/title/<?= $chapter['manga_id'] . "/" . slugify($chapter['manga_name']) ?>"><img width="100%" title="<?= $chapter['manga_name'] ?>" class="rounded" src="/images/manga/<?= $chapter['manga_id'] ?>.large.jpg" /></a>
                        </div>
                        <div class="<?= ($chapter['manga_hentai']) ? 'car-caption-h' : 'car-caption' ?> px-2 py-1">
                            <p class="text-truncate m-0"><?= display_manga_link_v2($chapter, 'white', true) ?></p>
                            <p class="text-truncate m-0"><?= display_short_title($chapter, 'white') ?></p>
                        </div>
                    </div>
                    <?php
                }

                print "<div class='clearfix'></div>";

                break;
        }
        ?>
    </div>
</div>
