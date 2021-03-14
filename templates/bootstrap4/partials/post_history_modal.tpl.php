<?php if (validate_level($templateVar['user'], 'pr')) : ?>
    <!-- Modal -->
    <div class="modal fade" id="post_history_modal" tabindex="-1" role="dialog" aria-labelledby="post_history_label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="post_history_label"><?= display_fa_icon('history') ?> Post History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    Loading...
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>