<div class="modal fade" id="report_modal" tabindex="-1" role="dialog" aria-labelledby="report_modal_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="report_modal_form" action="/ajax/actions.ajax.php?source=nojs&function=report_submit" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="report_modal_title">Submit new Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body container">
                    <div class="report-item report-item-manga row">
                        <div class="col col-2"><img src="" alt="Thumb"></div>
                        <div class="col col-10">
                            <span class="ellipsis">
                                <span class="fas fa-book fa-fw " aria-hidden="true" title="Title"></span>
                                <span class="manga_title">Manga Title</span>
                            </span>
                        </div>
                    </div>
                    <div class="report-item report-item-comment row">
                        <div class="col">
                            <div class="alert alert-warning">
                                Please note, that not every report is actionable. If you don't like a particular user's posts, consider blocking (from their profile page) instead.
                            </div>
                            <label>Reported Post:</label><br />
                            <div class="alert alert-info">
                                <div class="ellipsis report-item-comment-text"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="info" class="col">Report Reason:</label>
                    </div>
                    <div class="form-group row">
                        <div class="col">
                            <select class="form-control selectpicker" id="reason_id" name="reason_id" tabindex="-90" required="required">
                                <option value="" selected>- Select A Reason -</option>
                                <?php foreach ($templateVar['report_reasons'] AS $report_reason) : ?>
                                <option data-type-id="<?= $report_reason['type_id'] ?>" value="<?= $report_reason['id'] ?>"<?= $report_reason['is_info_required'] ? ' required="required"' : '' ?>><?= $report_reason['text'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="info" class="col">Additional Info:</label>
                    </div>
                    <div class="form-group row">
                        <div class="col">
                            <textarea class="form-control" rows="5" name="info" id="info" placeholder="Please be as descriptive as possible in your report. If possible, include any recommended fixes."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="return_url" value="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>" />
                    <input type="hidden" name="item_id" value="-1" />
                    <input type="hidden" name="type_id" value="-1" />
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
