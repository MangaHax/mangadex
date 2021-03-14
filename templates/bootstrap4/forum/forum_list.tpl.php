<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li><a href="/forums">Home</a></li>
    </ol>
</nav>

<?php
foreach ($templateVar['categories'] as $cat_id => $category) {
    if (($cat_id == 16 && $templateVar['hentai_toggle']) || $cat_id != 16) {
        $forums = new Forums($cat_id);
        $forums_obj = $forums->query_read();
        ?>

        <div class="card mb-3 border-bottom-0">
            <h6 class="card-header"><?= $category['forum_name'] ?></h6>

            <?php
            foreach ($forums_obj as $forum) {
                print display_forum($forum, $templateVar['user']);
            }
            ?>

        </div>
        <?php
    }
}
?>

<div class="card">
    <h6 class="card-header">
        <a title="Active within the past minute" role="button" data-toggle="collapse" data-parent="#online_users" href="#online_users" aria-expanded="true" aria-controls="online_users">Online: <?= number_format($templateVar['online_users_count']) ?> users and <?= number_format($templateVar['online_guests_count']) ?> guests</a>
    </h6>
    <div id="online_users" class="collapse" role="tabpanel" aria-labelledby="online_users">
        <div class="card-body">
            <?= ($templateVar['user']->user_id) ? $templateVar['online_users_string'] : "Not viewable by guests." ?>
        </div>
    </div>
</div>