<div class="alert alert-info text-center" role="alert">
    This is a testing prototype for Drama CD translation support. Read more about it and give feedback over at the forums!
    <a title="Go to forum thread" href="/thread/<?= $templateVar['thread'] ?>"><span class="fas fa-external-link-alt fa-fw " aria-hidden="true" title="Forum thread"></span></a>
</div>

<div class="card mb-3">
    <h6 class="card-header d-flex align-items-center py-2">
        <?= display_fa_icon('microphone-alt') ?>
        <span class="mx-1"><?= $templateVar['collection']['name'] ?></span>
        <span class="rounded flag flag-<?= $templateVar['collection']['language']['code'] ?>" title="<?= $templateVar['collection']['language']['name'] ?>"></span>
        <span class="ml-1"></span>
    </h6>
    <div class="card-body p-0">
        <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-5">
                <img class="rounded w-100" src="<?= $templateVar['collection']['cover'] ?>" alt="Cover" />
            </div>
            <div class="col-xl-9 col-lg-8 col-md-7">
                <div class="row m-0 py-1 px-0">
                    <div class="col-lg-3 col-xl-2 strong">Alt name(s):</div>
                    <div class="col-lg-9 col-xl-10">
                        <ul class="list-inline m-0">
                            <?php foreach ($templateVar['collection']['alt_names'] as $alt_name)
                                print "<li class='list-inline-item'>" . display_fa_icon('microphone-alt') . " $alt_name</li>"; ?>
                        </ul>
                    </div>
                </div>
                <div class="row m-0 py-1 px-0 border-top">
                    <div class="col-lg-3 col-xl-2 strong">Publication date:</div>
                    <div class="col-lg-9 col-xl-10">
                        <?= gmdate(DATE_FORMAT, $templateVar['collection']['pub_date']) ?>
                    </div>
                </div>
                <div class="row m-0 py-1 px-0 border-top">
                    <div class="col-lg-3 col-xl-2 strong">Description:</div>
                    <div class="col-lg-9 col-xl-10">
                        <?= $templateVar['collection']['description'] ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="drama-player container my-2">
    <div class="row no-gutters align-items-center mb-2">
        <div class="col-12 col-lg">
            <audio controls class="w-100"></audio>
        </div>
        <div class="col-12 col-lg-auto p-2">
            <select class="source-select form-control"></select>
        </div>
    </div>
    <div class="row no-gutters">
        <div class="col captions-display text-center"></div>
    </div>
</div>


<ul class="edit nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= ($templateVar['mode'] == 'tracks') ? 'active' : '' ?>" href="/drama/<?= $templateVar['collection']['id'] ?>/<?= slugify($templateVar['collection']['name']) ?>"><?= display_fa_icon('file', '', '', 'far') ?> <span class="d-none d-md-inline">Tracks</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link " href="/thread/<?= $templateVar['thread'] ?>"><?= display_fa_icon('comments', '', '', 'fas') ?> <span class="d-none d-md-inline">Comments</span></a>
    </li>
</ul>

<div class="tab-content">
    <?php foreach ($templateVar['tracks'] as $track): ?>
        <div class="drama-track container p-2 border-bottom" data-number="<?= $track['number'] ?>" data-title="<?= $track['title'] ?>" data-length="<?= $track['length'] ?>">
            <div class="row align-items-center no-gutters">
                <div class="col-auto mr-2">
                    <span class="track-number">#<?= $track['number'] ?></span>
                </div>
                <div class="col mr-2" style="font-size:1.1em">
                    <span class="track-title font-weight-bold"><?= $track['title_romaji'] ?></span>
                    <span class="track-title small text-muted">(<?= $track['title'] ?>)</span>
                </div>
                <div class="col-auto">
                    <span class="track-length font-weight-bold float-right"><?= $track['length'] != null ? ''.gmdate('i:s', $track['length']).'' : '<span class="small">(unavailable)</span>' ?></span>
                </div>
            </div>
            <div class="drama-track-sources d-none">
                <?php foreach ($track['sources'] as $source): ?>
                    <source src="<?= $source['filename'] ?>" data-format="<?= $source['format'] ?>" data-size="<?= $source['size'] ?>">
                <?php endforeach; ?>
            </div>
            <?php foreach ($track['captions'] as $caption): ?>
            <div class="row track-captions mx-3 mt-2 no-gutters align-items-center" data-id="<?= $caption['id'] ?>" data-captions="<?= $caption['filename'] ?>">
                <div class="col">
                    <div class="row no-gutters">
                        <div class="col-auto">
                            <?= display_fa_icon('closed-captioning', 'Captions') ?>
                            <?= display_fa_icon('compact-disc', 'Playing', 'fa-spin') ?>
                        </div>
                        <div class="col-auto mx-1">
                            <span class="rounded flag flag-<?= $caption['language']['code'] ?>" title="<?= $caption['language']['name'] ?>"></span>
                        </div>
                        <div class="col-auto col-lg">
                            <a href="#" class="drama-captions-link">
                                <?= $caption['title'] ?>
                            </a>
                        </div>
                        <div class="d-lg-none w-100"></div>
                        <div class="col-auto mx-1">
                            <?= display_fa_icon('users', 'Group') ?>
                        </div>
                        <div class="col col-lg-4">
                            <?= implode(" | ", array_map(function($group) {
                                $slug = slugify($group['name']);
                                return "<a href=\"/group/{$group['id']}/$slug\">{$group['name']}</a>";
                            }, $caption['groups'])); ?>
                        </div>
                        <div class="col-auto text-right ml-auto">
                            <?= display_fa_icon('user', 'Uploader') ?>
                            <a href="/user/<?= $caption['uploader']['id'] ?>" style="color:<?= $caption['uploader']['color'] ?>"><?= $caption['uploader']['name'] ?></a>
                        </div>
                        <div class="col-2 text-right ml-1" title="<?= gmdate(DATETIME_FORMAT, $caption['uploaded_at']) ?>">
                            <?= display_fa_icon('clock', 'Age', '', 'far') ?>
                            <?= get_time_ago($caption['uploaded_at']) ?>
                        </div>
                    </div>
                    <div class="row player-container"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>