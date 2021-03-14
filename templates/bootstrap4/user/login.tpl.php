<?php if (!$templateVar['user']->user_id) : ?>
    <script src='https://www.google.com/recaptcha/api.js'></script>

    <?= display_alert('info', 'Notice', "You might experience difficulties receiving emails if you use hotmail. Email mangadexstaff@gmail.com with your username in that case.<br />Clear your MangaDex cookies <strong>from all time</strong> by clicking <strong><a href='/login?clear_cookies=1'>this link</a></strong> if you have trouble logging in.") ?>

    <?php
    if (isset($_GET['msg'])) {
        switch ($_GET['msg']) {
            case 'cookies_cleared':
                print display_alert('success', 'Notice', 'Successfully cleared all mangadex cookies.');
                break;
            case 'ipban':
                print display_alert('danger', 'Error', 'Your IP is banned. Please wait a few hours or contact a Staff member on IRC/Discord.');
                break;
            case 'wrong_credentials':
                print display_alert('danger', 'Error', 'Incorrect username or password.');
                break;
            case 'missing_2fa':
                print display_alert('info', 'Notice', 'This account has 2FA enabled. Please enter your Authentication code below the password field and login again');
                break;
            case 'failed_2fa':
                print display_alert('danger', '2FA Error', 'Could not verify 2FA Code');
                break;
        }
    }
    ?>

    <!-- login_container -->
    <div class="mx-auto form-narrow" id="login_container">
        <form method="post" id="login_form" action="/ajax/actions.ajax.php?function=login&nojs=1">
            <h1 class="text-center">Login</h1>
            <hr>
            <div class="form-group">
                <label for="login_username" class="sr-only">Username</label>
                <input autofocus tabindex="1" type="text" name="login_username" id="login_username" class="form-control" placeholder="Username" required>
            </div>

            <div class="form-group">
                <label for="login_password" class="sr-only">Password</label>
                <input tabindex="2" type="password" name="login_password" id="login_password" class="form-control" placeholder="Password" required>
            </div>

            <div id="2fa_field" class="form-group<?= isset($_GET['msg']) && ($_GET['msg'] === 'missing_2fa' || $_GET['msg'] === 'failed_2fa') ? '' : ' d-none' ?>">
                <label for="two_factor" class="sr-only">2-Factor-Authentication</label>
                <input tabindex="3" type="text" name="two_factor" id="two_factor" class="form-control" placeholder="2FA Code" autocomplete="off" data-lpignore="true">
            </div>

            <div class="form-group">
                <input tabindex="4" type="checkbox" class="" id="remember_me" name="remember_me" value="1">
                <label class="" for="remember_me">Remember me (1 year)</label>
            </div>

            <button tabindex="5" class="btn btn-lg btn-success btn-block" type="submit" id="login_button"><?= display_fa_icon('sign-in-alt') ?> Login</button>

            <a tabindex="6" href="#" class="btn btn-lg btn-warning btn-block" id="forgot_button"><?= display_fa_icon('sync') ?> Reset password</a>
			
            <a tabindex="7" href="/signup" class="btn btn-lg btn-info btn-block" id="signup_button"><?= display_fa_icon('pencil-alt') ?> Sign up</a>
        </form>
    </div>

    <!-- forgot_container -->
    <div id="forgot_container" class="mx-auto form-narrow display-none">
        <form method="post" id="reset_form">
            <h1 class="text-center">Reset Password</h1>
            <hr>

            <div class="form-group">
                <label for="reset_email" class="sr-only">Email address</label>
                <input data-toggle="popover" data-content="Enter the email address used when you registered." type="email" name="reset_email" id="reset_email" class="form-control" placeholder="Email Address" required>
            </div>

            <?php if (defined('REQUIRE_CAPTCHA') && REQUIRE_CAPTCHA) : ?>
            <div class="mb-3 g-recaptcha" data-sitekey="<?= GOOGLE_CAPTCHA_SITEKEY ?>"></div>
            <?php endif; ?>

            <button class="btn btn-lg btn-danger btn-block" type="submit" id="reset_button"><?= display_fa_icon('sync') ?> Send reset email</button>
        </form>
    </div><!-- /container -->

<?php else : ?>

    <div class="mx-auto form-narrow" id="login_container">
        <h1 class="text-center">Login</h1>
        <hr>
        <p class="text-center text-muted">You are logged in.</p>
    </div>

<?php endif; ?>
