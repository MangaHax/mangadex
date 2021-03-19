<?php
if (!$templateVar['user']->activated) {
    ?>
    <div class="container" id="verification_container">
        <div class="row justify-content-md-center">
            <div class="col col-md-auto">
                <form method="post" id="change_activation_email_form">
                    <div class="form-group">
                        <label for="email" class="col-form-label">New Email:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $templateVar['user']->email ?>" required>
                    </div>

                    <button class="btn btn-lg btn-success btn-block" type="submit" id="change_activation_email_button"><?= display_fa_icon('check') ?> Confirm</button>
                </form>
            </div>
            <div class="col col-md-5 text-wrap mt-3">
                Your account will be changed to the new email and a verification code will be re-sent to it. You may attempt multiple emails until you receive the code.
                After you are done, click <a href="activation">here</a> to go back to the activation page.
            </div>
        </div>
    </div>
    <?php
}
else
    print display_alert('success', 'Success', "Your account is activated and you have access to all of " . TITLE . "'s features.");
?>