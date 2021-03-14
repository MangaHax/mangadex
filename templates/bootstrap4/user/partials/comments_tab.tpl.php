<div>
    <div class="row my-2">
        <div class="col-auto ml-auto">
            Display
            <select class="comment-filter-select form-control d-inline-block w-auto ml-1" onchange="window.location = '?show='+this.value">
                <option value="all"         <?= $templateVar['show']==='all'         ? 'selected':'' ?>>All comments</option>
                <option value="moderated"   <?= $templateVar['show']==='moderated'   ? 'selected':'' ?>>Only moderated</option>
                <option value="unmoderated" <?= $templateVar['show']==='unmoderated' ? 'selected':'' ?>>Only unmoderated</option>
            </select>
        </div>
    </div>
    <table class="table table-striped table-md">
        <?php
        foreach ($templateVar['user_comments_array'] as $comment) {
            if (($templateVar['show'] === 'moderated' && !$comment->moderated) ||
                ($templateVar['show'] === 'unmoderated' && $comment->moderated)) {
                continue;
            }

            $templateVar['parser']->parse($comment->text);

            print display_post_v2($comment, $templateVar['parser']->getAsHtml(), $templateVar['user'], $templateVar['page']);
            print display_edit_post_v2($comment, $templateVar['user']);
        }
        ?>
        <tr>
            <td colspan="2">Total comments: <?= $templateVar['user_comments_count'] ?></td>
        </tr>
    </table>
</div>

<?php
$paging = pagination($templateVar['user_comments_count'], $templateVar['current_page'], $templateVar['limit']);
$string = $templateVar['page'] . "/{$templateVar['uploader']->user_id}/" . slugify($templateVar['uploader']->username) . '/comments';

print display_pagination_v2($paging, 'chapters', $string);

