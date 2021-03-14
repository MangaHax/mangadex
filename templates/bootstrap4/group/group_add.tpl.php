<ul class="nav nav-tabs">
    <li class="nav-item" title="Search groups"><a class="nav-link" href="/groups"><?= display_fa_icon('search', 'Search groups') ?></a></li>
    <li class="nav-item ml-auto" title="Add new group"><a class="nav-link active" href="/group_new"><?= display_fa_icon('plus-circle', 'Add new group') ?></a></li>
</ul>

<div class="card my-3">
    <h6 class="card-header"><?= display_fa_icon('plus-circle') ?> Add a group</h6>
    <div class="card-body">
        <form id="group_add_form" method="post">
            <div class="form-group row">
                <label for="name" class="col-md-3 col-form-label"><?= display_fa_icon('users') ?> Group name</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="group_name" name="group_name" placeholder="Group name" required value="<?= isset($_GET['name']) ? htmlentities($_GET['name'], ENT_QUOTES) : "" ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="group_lang_id" class="col-md-3 col-form-label"><?= display_fa_icon('globe') ?> Language</label>
                <div class="col-md-9">
                    <select required title="Select a language" class="form-control selectpicker" id="group_lang_id" name="group_lang_id" data-size="10">
                        <?= display_languages_select() ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="website" class="col-md-3 col-form-label"><?= display_fa_icon('external-link-square-alt') ?> Website</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="group_website" name="group_website" placeholder="(include http:// or https://) (Optional)">
                </div>
            </div>
            <div class="form-group row">
                <label for="irc" class="col-md-3 col-form-label"><?= display_fa_icon('hashtag') ?> IRC channel</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="group_irc_channel" name="group_irc_channel" placeholder="# not required (Optional)">
                </div>
            </div>
            <div class="form-group row">
                <label for="irc" class="col-md-3 col-form-label"><?= display_fa_icon('hashtag') ?> IRC server</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="group_irc_server" name="group_irc_server" placeholder="irc.rizon.net (Optional)">
                </div>
            </div>
            <div class="form-group row">
                <label for="irc" class="col-md-3 col-form-label"><?= display_fa_icon('discord', '', '', 'fab') ?> Discord</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="group_discord" name="group_discord" placeholder="Discord link (No need to include https://discord.gg/) (Optional)">
                </div>
            </div>
            <div class="form-group row">
                <label for="email" class="col-md-3 col-form-label"><?= display_fa_icon('envelope') ?> Email</label>
                <div class="col-md-9">
                    <input type="email" class="form-control" id="group_email" name="group_email" placeholder="Email (Optional)">
                </div>
            </div>
            <div class="form-group row">
                <label for="group_description" class="col-md-3 col-form-label">Description</label>
                <div class="col-md-9">
                    <textarea class="form-control" rows="11" id="group_description" name="group_description" placeholder="(Optional)"></textarea>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-secondary" id="group_add_button"><?= display_fa_icon('plus-circle') ?> Add new group</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <h6 class="card-header"><?= display_fa_icon('info-circle') ?> Features</h6>
    <div class="card-body">
        <ul class="m-0">
            <li>Anyone may add a group.</li>
            <li>If you are the leader of a group, you can be assigned "Group Leader" for your group on MangaDex. </li>
            <li>Group leaders may add members to their group.</li>
            <li>Group leaders are able to manage chapters attributed to their group.</li>
            <li>Group leaders will have the option of stopping non group members from uploading chapters to their group.</li>
        </ul>
    </div>
</div>