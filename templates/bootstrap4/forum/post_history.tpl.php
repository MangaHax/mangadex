<?php
/**
 * Created by PhpStorm.
 * User: icelord
 * Date: 24.01.19
 * Time: 17:31
 */

$last_entry = end($templateVar['posts']);
foreach($templateVar['posts'] AS $post) { ?>

    <h5><?= get_time_ago($post->timestamp) ?> by <?= display_user_link($post->user_id, $post->username, $post->editor_level_colour) ?></h5>

    <div class="post">
        <?php $templateVar['parser']->parse($post->text); ?>
        <?= nl2br(make_links_clickable($templateVar['parser']->getAsHtml())) ?>
    </div>

    <?php if ($last_entry !== $post) { ?>
        <hr>
    <?php } ?>

<?php } ?>