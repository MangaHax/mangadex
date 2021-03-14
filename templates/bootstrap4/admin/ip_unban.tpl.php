<form method="post" id="admin_ip_unban_form" class="mt-3">
    <div class="form-group row">
        <label for="ip" class="col-md-4 col-lg-3 col-xl-2 col-form-label">IP:</label>
        <div class="col-md-8 col-lg-9 col-xl-10">
            <input type="text" class="form-control" id="ip" name="ip" placeholder="ip" required>
        </div>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-danger" id="admin_ip_unban_button"><?= display_fa_icon('gavel') ?> Unban</button>
    </div>
</form>