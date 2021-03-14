<?php if (!$templateVar['user']->user_id) : ?>

<script src='https://www.google.com/recaptcha/api.js'></script>

<!-- forgot_container -->
<div class="mx-auto form-narrow" id="forgot_container">
	<form method="post" id="reset_form">
		<h1 class="text-center">Reset Password</h1>
		<hr>

		<div class="form-group">
			<label for="reset_code" class="sr-only">Reset code</label>
			<input data-toggle="popover" data-content="Enter the reset code you received in your email." type="text" name="reset_code" id="reset_code" class="form-control" placeholder="reset_code" required value="<?= $_GET["code"] ?? '' ?>">
		</div>

        <div class="form-group">
            <label for="reg_pass1" class="sr-only">New Password</label>
            <input data-toggle="popover" data-content="Minimum length: 8 characters." type="password" name="reg_pass1" id="reg_pass1" class="form-control" placeholder="New Password" required>
        </div>

        <div class="form-group">
            <label for="reg_pass2" class="sr-only">Confirm New Password</label>
            <input data-toggle="popover" data-content="Type your password again." type="password" name="reg_pass2" id="reg_pass2" class="form-control" placeholder="New Password (again)" required>
        </div>

        <?php if (defined('REQUIRE_CAPTCHA') && REQUIRE_CAPTCHA) : ?>
		<div class="g-recaptcha" data-sitekey="<?= GOOGLE_CAPTCHA_SITEKEY ?>"></div>
        <?php endif ?>

		<button class="btn btn-lg btn-danger btn-block" type="submit" id="reset_button"><?= display_fa_icon('sync') ?> Reset Password</button>
	</form>
</div><!-- /container -->

<?php else : ?>

<div class="mx-auto form-narrow" id="login_container">
	<h1 class="text-center">Login</h1>
	<hr>
	<p class="text-center">You are logged in.</p>
</div>

<?php endif; ?>
