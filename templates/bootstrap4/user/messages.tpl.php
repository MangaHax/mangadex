<script src='https://www.google.com/recaptcha/api.js'></script>

<ul class="nav nav-tabs">
    <li title="Inbox" class="nav-item">
        <a class="nav-link <?= display_active($templateVar['mode'], ['inbox']) ?>" href="/messages/inbox"><?= display_fa_icon('envelope', 'Inbox') ?> <span class="d-none d-md-inline">Inbox</span></a>
    </li>
    <li title="Notifications" class="nav-item">
        <a class="nav-link <?= display_active($templateVar['mode'], ['notifications']) ?>" href="/messages/notifications"><?= display_fa_icon('exclamation-circle', 'Notifications') ?> <span class="d-none d-md-inline">Notifications</span></a>
    </li>
    <li title="Send message" class="nav-item">
        <a class="nav-link <?= display_active($templateVar['mode'], ['send']) ?>" href="/messages/send"><?= display_fa_icon('pencil-alt', 'Send message') ?> <span class="d-none d-md-inline">Send message</span></a>
    </li>
    <li title="Bin" class="nav-item">
        <a class="nav-link <?= display_active($templateVar['mode'], ['bin']) ?>" href="/messages/bin"><?= display_fa_icon('trash', 'Bin') ?> <span class="d-none d-md-inline">Bin</span></a>
    </li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade show active">
        <?= $templateVar['messages_tab_html'] ?>
    </div>
</div>
