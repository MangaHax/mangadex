<div class="card my-3">
    <h6 class="card-header"><?= display_fa_icon('pencil-alt') ?> Send message</h6>
    <div class="card-body">
        <form id="msg_send_form" method="post" class="">
            <div class="form-group row">
                <label for="recipient" class="col-md-2 col-form-label">To</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" id="recipient" name="recipient" placeholder="Username" required value="<?= $_GET["recipient"] ?? '' ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="subject" class="col-md-2 col-form-label">Subject</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="message" class="col-md-2 col-form-label">Message</label>
                <div class="col-md-10">
                    <?= display_bbcode_textarea() ?>
                </div>
            </div>
            <div class="text-center">
				<?php if (defined('REQUIRE_CAPTCHA') && REQUIRE_CAPTCHA && !$templateVar['is_staff']) : ?>
                    <div class="row">
                        <div class="col-auto mx-auto">
                            <div class="mb-3 g-recaptcha" data-sitekey="<?= GOOGLE_CAPTCHA_SITEKEY ?>"></div>
                        </div>
                    </div>
				<?php endif; ?>
                <button id="msg_send_button" type="submit" class="btn btn-secondary"><?= display_fa_icon('pencil-alt') ?> Send</button>
            </div>
        </form>
    </div>
</div>
