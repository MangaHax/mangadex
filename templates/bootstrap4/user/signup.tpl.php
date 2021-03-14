<script src='https://www.google.com/recaptcha/api.js'></script>

<div class="mx-auto form-narrow" id="signup_container">
	
    <form method="post" id="signup_form" >
        <h1 class="text-center">Sign up</h1>
        <hr>

		<?php if (defined('ENABLE_REGISTRATION') && ENABLE_REGISTRATION) : ?>
		
        <div class="form-group">
            <label for="reg_username" class="sr-only">Username</label>
            <input data-toggle="popover" data-content="Alphanumeric characters only." type="text" name="reg_username" id="reg_username" class="form-control" placeholder="Username" required>
        </div>

        <div class="form-group">
            <label for="reg_pass1" class="sr-only">Password</label>
            <input data-toggle="popover" data-content="Minimum length: 8 characters." type="password" name="reg_pass1" id="reg_pass1" class="form-control" placeholder="Password" required>
        </div>

        <div class="form-group">
            <label for="reg_pass2" class="sr-only">Confirm Password</label>
            <input data-toggle="popover" data-content="Type your password again." type="password" name="reg_pass2" id="reg_pass2" class="form-control" placeholder="Password (again)" required>
        </div>

        <div class="form-group">
            <label for="reg_email1" class="sr-only">Email Address</label>
            <input data-toggle="popover" data-content="Valid email required for activation." type="email" name="reg_email1" id="reg_email1" class="form-control" placeholder="Email Address" required>
        </div>

        <div class="form-group">
            <label for="reg_email2" class="sr-only">Confirm Email Address</label>
            <input data-toggle="popover" data-content="Type your email again." type="email" name="reg_email2" id="reg_email2" class="form-control" placeholder="Email Address (again)" required>
        </div>
		
		<?= display_alert("info", "Info", "In order to prevent spam accounts, most disposable/temporary email domains are banned. Using these may get your account banned by mistake, so it is advised that you do not use these."); ?>
		
        <?php if (defined('REQUIRE_CAPTCHA') && REQUIRE_CAPTCHA) : ?>
            <div class="mb-3 g-recaptcha" data-sitekey="<?= GOOGLE_CAPTCHA_SITEKEY ?>"></div>
        <?php endif; ?>

        <button class="btn btn-lg btn-success btn-block" type="submit" id="signup_button"><?= display_fa_icon('pencil-alt') ?> Sign up</button>
		
		<?php else : ?>
		
		<p class="text-center">Currently closed, will be open very soon.</p>
		
		<button class="btn btn-lg btn-warning btn-block" disabled><?= display_fa_icon('ban') ?> Sign up</button>
	
		<?php endif; ?>
	
    </form>
	
</div>
