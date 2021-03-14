<p>MangaDex is an online manga reader that caters to all languages. Mangadex is made by scanlators for scanlators and gives active groups complete control over their releases. Everyone is welcome here as long as they follow the <a href="/rules">rules</a>.</p>

<?php
$groupedUsers = [];
foreach ($templateVar['users'] as $user) {
    if (!isset($groupedUsers[$user->level_name])) {
        $groupedUsers[$user->level_name] = [];
    }
    $groupedUsers[$user->level_name][] = $user;
}
?>

<h3>Staff Members</h3>
<ul>
    <?php foreach ($groupedUsers as $group => $users) : ?>
        <li><?= $group ?>
            <ul>
                <?php foreach ($users as $user) : ?>
                    <li><?= display_user_link_v2($user) ?></li>
                <?php endforeach; ?>
            </ul>
        </li>
    <?php endforeach; ?>
</ul>

<h3>Contacting Staff</h3>
<ul>
    <li><a href="https://discord.gg/Y2YKXUP">Discord</a> is the fastest way to get ahold of any staff members.</li>
    <li>Staff members on the Discord have the roles:</li>
    <ul>
        <li>Overlord</li>
        <li>Admin</li>
        <li>Developer</li>
        <li>Moderator</li>
        <li>Public Relations</li>
        <li>Designer</li>
    </ul>
    <li>Anybody without one of those roles is not staff. They do not speak for MangaDex.</li>
    <li>Site Messaging is another alternative but expect longer wait times.</li>
</ul>