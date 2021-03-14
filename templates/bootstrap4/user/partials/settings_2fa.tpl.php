<div class="container mt-2">
    <div class="form-group row">
        <label class="col-md-4 col-lg-3 col-xl-2 col-form-label">2-Factor-Authentication:</label>
        <div class="col-md-8 col-lg-9 col-xl-10">
            <?php if ($templateVar['2fa'] !== null) : ?>
                <div class="alert alert-info">Two Factor Authentication is already set up. Click the button below to remove Two Factor Authentication protection from this account.</div>
                <button id="remove_2fa_btn" class="btn btn-danger" type="button">Disable 2FA</button>
                <span class="remove-2fa-errors"></span>
            <?php else : ?>
                <button id="enable_2fa_btn" class="btn btn-success" type="button">Enable 2FA</button>
                <div id="2fa_container" class="container d-none mt-2">
                    <div class="row">
                        <div class="col col-lg-12 col-xl-4 mb-lg-1">
                            <div class="card 2fa-card-qr h-100">
                                <div class="card-header">1. Set up Authenticator App</div>
                                <div class="qr-img-wrapper text-center border-bottom">
                                    <img class="qr-code">
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Use an authenticator app like Google authenticator to scan this QR Code or enter the code <pre class="code-box">%CODE%</pre> (set to time-based) to set up your authenticator for MangaDex</p>
                                </div>
                            </div>
                        </div>

                        <div class="col col-lg-12 col-xl-4 mb-lg-1">
                            <div class="card 2fa-card-code h-100">
                                <div class="card-header">2. Enter Code</div>
                                <div class="card-body">
                                    <p class="card-text">
                                        Use your authenticator app to generate a logincode and confirm it below.
                                    </p>
                                    <p class="card-text">
                                        You'll use this method to login in the future, in addition to your username and password combination.
                                    </p>
                                    <input type="text" class="form-control" id="qr_confirm">
                                    <div class="mt-1 mb-1 text-center confirm-2fa-errors"></div>
                                </div>
                                <div class="card-footer text-center">
                                    <button id="confirm_2fa_btn" class="btn btn-success" disabled="disabled">Confirm</button>
                                </div>
                            </div>
                        </div>

                        <div class="col col-lg-12 col-xl-4 mb-lg-1">
                            <div class="card 2fa-card-recover h-100">
                                <div class="card-header">3. Save Recover Codes</div>
                                <div class="card-body">
                                    <p class="card-text">
                                        If you ever loose access to your authenticator, you can use any of these codes to login once per code.
                                    </p>
                                    <p class="card-text confirm-2fa-result">
                                        <span style="font-family: monospace; color: orange; font-weight: bold">Confirm 2FA to view recovery codes</span>
                                    </p>
                                </div>
                                <div class="card-footer text-center">
                                    <button id="finalize_2fa_btn" class="btn btn-success" disabled="disabled">Complete 2FA Setup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>