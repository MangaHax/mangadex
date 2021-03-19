<?php
if (!$templateVar['user']->activated) {
    ?>
    <div class="container" id="verification_container">
        <div class="row justify-content-md-center">
            <div class="col col-md-auto">
                <form method="post" id="activate_form">
                    <h1 class="text-center">Activation</h1>
                    <hr>
                    <div class="form-group">
                        <label for="activation_code" class="sr-only">Activation code</label>
                        <input type="text" name="activation_code" id="activation_code" class="form-control" placeholder="Activation code" value="<?= isset($_GET['code']) ? $_GET['code'] : '' ?>" required>
                    </div>
        
                    <button class="btn btn-lg btn-success btn-block" type="submit" id="activate_button"><?= display_fa_icon('check') ?> Activate</button>
                    <button class="btn btn-lg btn-warning btn-block" type="button" id="resend_activation_code_button"><?= display_fa_icon('sync') ?> Resend</button>
                    <a href="/change_activation_email" class="btn btn-lg btn-info btn-block" id="change_activation_email_button"><span class="fas fa-pencil-alt fa-fw " aria-hidden="true"></span> Change Email</a>
                </form>
            </div>
            <div class="col col-md-5 text-wrap mt-3">
                <h5>Activation Code Problems:</h5>
                These hosts usually don't receive it or it goes to spam:
                <ul>
                    <li>school related emails</li>
                    <li>work related emails</li>
                    <li>hotmail.com</li>
                    <li>outlook.com</li>
                    <li>o2.pl</li>
                </ul>
        
                Your email quota was hit, you can send but can't receive emails, meaning we can't help you, typically occurs with:
                <ul>
                    <li><a href="https://support.google.com/mail/answer/6374270">gmail.com</a></li>
                    <li><a href="https://support.apple.com/en-us/HT202305">icloud.com</a></li>
                </ul>
                
                You are not receiving the code because you entered the incorrect email on sign-up:
                <ul>
                    <li><?= $templateVar['user']->email ?></li>
                </ul>
                
                If you are unable to receive the verification code due to one of the problems above, try changing your email.<br /><br />
                If you are still incapable of verifying your account after that, you may contact <a href="mailto:mangadexstaff@gmail.com">mangadexstaff@gmail.com</a> for assistance,
                but include your <b>username</b> and the <b>email</b> you registered with.<br><br>
            </div>
        </div>
    </div>
    <?php
}
else
    print display_alert('success', 'Success', "Your account is activated and you have access to all of " . TITLE . "'s features.");
?>