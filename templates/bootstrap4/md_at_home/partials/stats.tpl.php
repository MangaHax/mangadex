<?php if (validate_level($templateVar['user'], 'member')) :  ?>
Stats
<?php else : ?>
<div class="alert alert-info text-center"><?= display_fa_icon('info-circle') ?> Please <a href="/login">log in</a> to see the stats.</div>
<?php endif; ?>