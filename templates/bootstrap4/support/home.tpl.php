<?php if (validate_level($templateVar['user'], 'member')) :  ?>

<div>
    <h4>Our infrastructure</h4>
    <ul>
        <li>1 webserver/master db/MangaDex@Home backend - $250 (Nickname: Ichika)</li>
        <li>2 API/slave db server - $250 each (Nicknames: Nino/Miku)</li>
        <li>1 slave database/image archive servers - $140</li>
        <li>1 slave database server - covered by a dev</li>
        <li>CDN for site assets/DDoS Protection - $180</li>
        <li>Reverse proxy - covered by Path</li>
        <li>CDN for chapter images - covered by <a href="https://mangadex.network" target="_blank">MangaDex Network/MD@H</a></li>
    </ul>
    <h4>About Us</h4>
    <p>MangaDex is still growing steadily, approaching 14 million users per month and increasing. We fund our servers primarily through user donations with a little extra from our affiliates. Our affiliates are generally services we utilize ourselves. MangaDex staff are all unpaid volunteers and all donations go directly towards server costs. We may run non-intrusive ads as a last resort when our other funding options fail, but for the most part we'd like to keep it this way out of our personal distate for ads.</p>
    <h4>How you can help</h4>
    <p>If you'd like to help keep our site ad-free we kindly ask that you consider <a href="/support/donate">donating crypto</a>, checking out our <a href="/support/affiliates">affiliates</a>, or by running a MangaDex@Home client for our P2P solution, the MangaDex network. If you would like more information about running a MangaDex@Home client, please visit the <a href="/md_at_home">MangaDex@Home</a> page. This option is open to everyone, even if you have absolutely no experience or knowledge of server management.</p>
</div>

<?php else : ?>
    <div>
        <h4>About Us</h4>
        <p>MangaDex is still growing steadily, approaching 14 million users per month and increasing. We fund our servers primarily through user donations with a little extra from our affiliates. Our affiliates are generally services we utilize ourselves. MangaDex staff are all unpaid volunteers and all donations go directly towards server costs. We may run non-intrusive ads as a last resort when our other funding options fail, but for the most part we'd like to keep it this way out of our personal distate for ads.</p>
        <h4>How you can help</h4>
        <p>If you'd like to help keep our site ad-free, we kindly ask that you consider <a href="/support/donate">donating crypto</a> , looking at our <a href="/support/affiliates">affiliates</a> , or by running a MangaDex@Home client for the MangaDex network. If you would like more information about running a MangaDex@Home client, please visit the <a href="/md_at_home">MangaDex@Home</a> page. This option is open to everyone, even if you have absolutely no experience or knowledge of server management.</p>
    </div>
<?php endif; ?>

