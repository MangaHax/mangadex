<ul class="mb-3 nav nav-tabs">
    <li title="Search groups" class="nav-item"><a class="nav-link active" href="/groups"><?= display_fa_icon('search', 'Search groups') ?></a></li>
    <li title="Add new group" class="nav-item ml-auto"><a class="nav-link" href="/group_new"><?= display_fa_icon('plus-circle', 'Add new group') ?></a></li>
</ul>

<div class="card mb-3">
    <h6 class="card-header"><?= display_fa_icon('search') ?> Search groups</h6>
    <div class="card-body">
        <form id="group_search_form" method="post">
            <div class="form-group row">
                <label for="group_name" class="col-md-3 col-form-label">Group name</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="group_name" name="group_name" value="<?= htmlentities($_GET["group_name"], ENT_QUOTES) ?>">
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-secondary" id="search_button"><?= display_fa_icon('search') ?> <span class="span-1280">Search groups</span></button>
            </div>
        </form>
    </div>
</div>
