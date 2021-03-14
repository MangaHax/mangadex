<?php
if (!$templateVar['user']->activated) {
    print display_alert('info', 'Notice', "Your activation code will be emailed to <strong>{$templateVar['user']->email}</strong>. If this is incorrect, or you are not receiving your code, email <strong>mangadexstaff@gmail.com</strong> with your username for assistance."); ?>

    <div class="mx-auto form-narrow">
        <form method="post" id="activate_form">
            <h1 class="text-center">Activation</h1>
            <hr>
            <div class="form-group">
                <label for="activation_code" class="sr-only">Activation code</label>
                <input type="text" name="activation_code" id="activation_code" class="form-control" placeholder="Activation code" value="<?= isset($_GET['code']) ? $_GET['code'] : '' ?>" required>
            </div>

            <button class="btn btn-lg btn-success btn-block" type="submit" id="activate_button"><?= display_fa_icon('check') ?> Activate</button>
            <button class="btn btn-lg btn-warning btn-block" type="button" id="resend_activation_code_button"><?= display_fa_icon('sync') ?> Resend</button>
        </form>
    </div>
    <?php
}
else
    print display_alert('success', 'Success', "Your account is activated and you have access to all of " . TITLE . "'s features.");
?>